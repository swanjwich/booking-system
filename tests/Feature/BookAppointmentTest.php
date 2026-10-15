<?php

namespace Tests\Feature;

use App\Actions\BookAppointment;
use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private StaffProfile $staff;
    private Service $service;
    private User $customer;
    private CarbonImmutable $tenAm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(CarbonImmutable::parse('2026-11-03 08:00', 'Asia/Manila'));

        $staffUser = User::factory()->create(['role' => UserRole::Staff]);
        $this->staff = $staffUser->staffProfile()->create([
            'title' => 'Stylist',
            'is_bookable' => true,
        ]);

        foreach (range(0, 6) as $weekday) {
            $this->staff->workingHours()->create([
                'day_of_week' => $weekday,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ]);
        }

        $this->service = Service::create([
            'name' => 'Haircut',
            'duration_minutes' => 30,
            'buffer_minutes' => 15,
            'price_amount' => 35000,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create(['role' => UserRole::Customer]);
        $this->tenAm = CarbonImmutable::parse('2026-11-04 10:00', 'Asia/Manila')->utc();
    }

    private function book(?CarbonImmutable $at = null): Appointment
    {
        return app(BookAppointment::class)->handle(
            $this->customer,
            $this->staff,
            $this->service,
            $at ?? $this->tenAm,
        );
    }

    public function test_it_books_an_appointment(): void
    {
        $appointment = $this->book();

        $this->assertSame(AppointmentStatus::Pending, $appointment->status);
        $this->assertTrue($appointment->starts_at->equalTo($this->tenAm));
        $this->assertStringStartsWith('BK-', $appointment->reference);
    }

    public function test_it_snapshots_price_duration_and_buffer(): void
    {
        $appointment = $this->book();

        $this->service->update(['price_amount' => 99900, 'duration_minutes' => 90]);

        $this->assertSame(35000, $appointment->fresh()->price_amount);
        $this->assertSame(30, $appointment->fresh()->duration_minutes);
        $this->assertSame(15, $appointment->fresh()->buffer_minutes);
    }

    public function test_the_end_time_excludes_the_buffer_but_the_block_includes_it(): void
    {
        $appointment = $this->book();

        $this->assertTrue($appointment->ends_at->equalTo($this->tenAm->addMinutes(30)));
        $this->assertTrue($appointment->blocked_until->equalTo($this->tenAm->addMinutes(45)));
    }

    public function test_it_refuses_a_slot_that_is_already_taken(): void
    {
        $this->book();

        $this->expectException(SlotUnavailableException::class);

        $this->book();
    }

    public function test_it_refuses_a_slot_overlapping_the_buffer(): void
    {
        $this->book();

        $this->expectException(SlotUnavailableException::class);

        $this->book($this->tenAm->addMinutes(30));
    }

    public function test_it_refuses_a_time_outside_working_hours(): void
    {
        $this->expectException(SlotUnavailableException::class);

        $this->book(CarbonImmutable::parse('2026-11-04 19:00', 'Asia/Manila')->utc());
    }

    public function test_the_database_rejects_a_duplicate_active_slot(): void
    {
        $first = $this->book();

        $this->expectException(\Illuminate\Database\QueryException::class);

        Appointment::create([
            ...$first->only([
                'customer_id', 'staff_profile_id', 'service_id',
                'starts_at', 'ends_at', 'blocked_until', 'active_slot_at',
                'price_amount', 'duration_minutes', 'buffer_minutes',
            ]),
            'status' => AppointmentStatus::Pending,
        ]);
    }

    public function test_a_failed_booking_leaves_nothing_behind(): void
    {
        $this->book();

        try {
            $this->book();
        } catch (SlotUnavailableException) {
            // expected
        }

        $this->assertSame(1, Appointment::count());
    }
}
