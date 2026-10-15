<?php

namespace App\Services;

use App\Enums\DayOfWeek;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\TimeOff;
use App\Models\WorkingHour;
use App\Support\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AvailabilityCalculator
{
    public function __construct(
        private readonly int $intervalMinutes,
        private readonly string $timezone,
        private readonly int $minHoursNotice,
    ) {}

    /**
     * @return Collection<int, TimeSlot>
     */
    public function slotsFor(StaffProfile $staff, Service $service, CarbonImmutable $date): Collection
    {
        if (! $staff->is_bookable) {
            return collect();
        }

        $windows = $this->workingWindows($staff, $date);

        if ($windows->isEmpty()) {
            return collect();
        }

        $duration = $service->duration_minutes;
        $buffer = $service->buffer_minutes;

        $dayStart = $windows->first()['start'];
        $dayEnd = $windows->last()['end']->addMinutes($buffer);

        $busy = $this->busyPeriods($staff, $dayStart, $dayEnd);
        $earliest = CarbonImmutable::now()->addHours($this->minHoursNotice);

        return $windows
            ->flatMap(fn (array $w) => $this->candidatesIn($w['start'], $w['end'], $duration, $buffer))
            ->filter(fn (TimeSlot $slot) => $slot->startsAt >= $earliest)
            ->filter(fn (TimeSlot $slot) => $this->isFree($slot, $busy))
            ->values();
    }

    private function workingWindows(StaffProfile $staff, CarbonImmutable $date): Collection
    {
        $local = $date->setTimezone($this->timezone);
        $weekday = DayOfWeek::from($local->dayOfWeek);

        return $staff->workingHours
            ->filter(fn (WorkingHour $h) => $h->day_of_week === $weekday)
            ->sortBy('start_time')
            ->map(fn (WorkingHour $h) => [
                'start' => $this->atLocalTime($local, $h->start_time),
                'end' => $this->atLocalTime($local, $h->end_time),
            ])
            ->values();
    }

    private function atLocalTime(CarbonImmutable $date, string $time): CarbonImmutable
    {
        return CarbonImmutable::parse(
            $date->format('Y-m-d').' '.$time,
            $this->timezone
        )->utc();
    }

    private function candidatesIn(
        CarbonImmutable $windowStart,
        CarbonImmutable $windowEnd,
        int $duration,
        int $buffer,
    ): Collection {
        $slots = collect();
        $cursor = $windowStart;

        while ($cursor->addMinutes($duration) <= $windowEnd) {
            $slots->push(TimeSlot::for($cursor, $duration, $buffer));
            $cursor = $cursor->addMinutes($this->intervalMinutes);
        }

        return $slots;
    }

    private function busyPeriods(StaffProfile $staff, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $timeOffs = $staff->timeOffs()
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->get()
            ->map(fn (TimeOff $t) => ['start' => $t->starts_at, 'end' => $t->ends_at]);

        $appointments = Appointment::query()
            ->forStaff($staff)
            ->blocking()
            ->overlapping($from, $to)
            ->get()
            ->map(fn (Appointment $a) => ['start' => $a->starts_at, 'end' => $a->blocked_until]);

        return $timeOffs->concat($appointments);
    }

    private function isFree(TimeSlot $slot, Collection $busy): bool
    {
        return $busy->every(
            fn (array $period) => ! $slot->overlaps($period['start'], $period['end'])
        );
    }
}
