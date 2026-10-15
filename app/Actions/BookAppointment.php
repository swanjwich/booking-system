<?php

namespace App\Actions;

use App\Enums\AppointmentStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\AvailabilityCalculator;
use App\Support\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BookAppointment
{
    public function __construct(
        private readonly AvailabilityCalculator $availability,
    ) {}

    public function handle(
        User $customer,
        StaffProfile $staff,
        Service $service,
        CarbonImmutable $startsAt,
        ?string $note = null,
    ): Appointment {
        return DB::transaction(function () use ($customer, $staff, $service, $startsAt, $note) {
            StaffProfile::whereKey($staff->getKey())->lockForUpdate()->firstOrFail();

            if (! $this->stillAvailable($staff, $service, $startsAt)) {
                throw new SlotUnavailableException();
            }

            $slot = TimeSlot::for($startsAt, $service->duration_minutes, $service->buffer_minutes);

            return Appointment::create([
                'customer_id' => $customer->getKey(),
                'staff_profile_id' => $staff->getKey(),
                'service_id' => $service->getKey(),
                'starts_at' => $slot->startsAt,
                'ends_at' => $slot->endsAt,
                'blocked_until' => $slot->blockedUntil,
                'active_slot_at' => $slot->startsAt,
                'status' => AppointmentStatus::Pending,
                'price_amount' => $service->price_amount,
                'duration_minutes' => $service->duration_minutes,
                'buffer_minutes' => $service->buffer_minutes,
                'customer_note' => $note,
            ]);
        });
    }

    private function stillAvailable(
        StaffProfile $staff,
        Service $service,
        CarbonImmutable $startsAt,
    ): bool {
        $staff->load('workingHours');

        return $this->availability
            ->slotsFor($staff, $service, $startsAt)
            ->contains(fn (TimeSlot $slot) => $slot->startsAt->equalTo($startsAt));
    }
}
