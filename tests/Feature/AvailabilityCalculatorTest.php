<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\AvailabilityCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $day;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-11-03 08:00', 'Asia/Manila'));
        $this->day = CarbonImmutable::parse('2026-11-04 00:00', 'Asia/Manila');
    }

    private function calculator(): AvailabilityCalculator
    {
        return app(AvailabilityCalculator::class);
    }

    private function staff(string $start = '09:00', string $end = '17:00'): StaffProfile
    {
        $user = User::factory()->create(['role' => UserRole::Staff]);
        $profile = $user->staffProfile()->create(['title' => 'Stylist', 'is_bookable' => true]);

        foreach (range(0, 6) as $weekday) {
            $profile->workingHours()->create([
                'day_of_week' => $weekday,
                'start_time' => $start,
                'end_time' => $end,
            ]);
        }

        return $profile;
    }

    private function service(int $duration = 30, int $buffer = 0): Service
    {
        return Service::create([
            'name' => 'Service ' . uniqid(),
            'duration_minutes' => $duration,
            'buffer_minutes' => $buffer,
            'price_amount' => 35000,
            'is_active' => true,
        ]);
    }

    private function book(
        StaffProfile $staff,
        Service $service,
        string $localTime,
        int $minutes = 30,
        int $buffer = 0,
    ): void {
        $start = CarbonImmutable::parse('2026-11-04 ' . $localTime, 'Asia/Manila')->utc();

        $staff->appointments()->create([
            'reference' => strtoupper(uniqid()),
            'customer_id' => User::factory()->create()->id,
            'service_id' => $service->id,
            'starts_at' => $start,
            'ends_at' => $start->addMinutes($minutes),
            'blocked_until' => $start->addMinutes($minutes + $buffer),
            'active_slot_at' => $start,
            'status' => AppointmentStatus::Confirmed,
            'price_amount' => 35000,
            'duration_minutes' => $minutes,
            'buffer_minutes' => $buffer,
        ]);
    }

    private function times(StaffProfile $staff, Service $service): array
    {
        return $this->calculator()
            ->slotsFor($staff, $service, $this->day)
            ->map(fn($slot) => $slot->label('Asia/Manila'))
            ->all();
    }

    public function test_it_generates_slots_across_the_working_day(): void
    {
        $times = $this->times($this->staff(), $this->service());

        $this->assertSame('9:00 AM', $times[0]);
        $this->assertSame('4:30 PM', end($times));
        $this->assertCount(31, $times);
    }

    public function test_it_will_not_offer_a_slot_that_runs_past_closing(): void
    {
        $times = $this->times($this->staff('09:00', '10:00'), $this->service(45));

        $this->assertSame(['9:00 AM', '9:15 AM'], $times);
    }

    public function test_it_returns_nothing_when_the_staff_member_does_not_work(): void
    {
        $user = User::factory()->create(['role' => UserRole::Staff]);
        $staff = $user->staffProfile()->create(['title' => 'Stylist', 'is_bookable' => true]);

        $this->assertEmpty($this->times($staff, $this->service()));
    }

    public function test_it_removes_slots_taken_by_an_appointment(): void
    {
        $staff = $this->staff();
        $service = $this->service();

        $this->book($staff, $service, '10:00');

        $times = $this->times($staff, $service);

        $this->assertNotContains('9:45 AM', $times);
        $this->assertNotContains('10:00 AM', $times);
        $this->assertNotContains('10:15 AM', $times);
    }

    public function test_it_allows_a_slot_starting_exactly_when_another_ends(): void
    {
        $staff = $this->staff();
        $service = $this->service();

        $this->book($staff, $service, '10:00');

        $this->assertContains('10:30 AM', $this->times($staff, $service));
        $this->assertContains('9:30 AM', $this->times($staff, $service));
    }

    public function test_a_cancelled_appointment_frees_its_slot(): void
    {
        $staff = $this->staff();
        $service = $this->service();

        $this->book($staff, $service, '10:00');

        $staff->appointments()->first()->update([
            'status' => AppointmentStatus::Cancelled,
            'active_slot_at' => null,
        ]);

        $this->assertContains('10:00 AM', $this->times($staff, $service));
    }

    public function test_it_removes_slots_covered_by_time_off(): void
    {
        $staff = $this->staff();

        $staff->timeOffs()->create([
            'starts_at' => CarbonImmutable::parse('2026-11-04 12:00', 'Asia/Manila')->utc(),
            'ends_at' => CarbonImmutable::parse('2026-11-04 13:00', 'Asia/Manila')->utc(),
            'reason' => 'Lunch',
        ]);

        $times = $this->times($staff, $this->service());

        $this->assertNotContains('12:00 PM', $times);
        $this->assertNotContains('12:45 PM', $times);
        $this->assertContains('1:00 PM', $times);
    }

    public function test_the_buffer_blocks_the_slot_after_an_appointment(): void
    {
        $staff = $this->staff();
        $service = $this->service(duration: 30, buffer: 15);

        // Booked 10:00. Service ends 10:30, but the chair isn't free until 10:45.
        $this->book($staff, $service, '10:00', minutes: 30, buffer: 15);

        $times = $this->times($staff, $service);

        $this->assertNotContains('10:30 AM', $times);
        $this->assertContains('10:45 AM', $times);
    }

    public function test_it_hides_slots_inside_the_notice_window(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-04 09:30', 'Asia/Manila'));

        $times = $this->times($this->staff(), $this->service());

        $this->assertNotContains('11:00 AM', $times);
        $this->assertContains('11:30 AM', $times);
    }
}
