<?php

namespace App\Livewire;

use App\Actions\BookAppointment;
use App\Exceptions\SlotUnavailableException;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Services\AvailabilityCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BookingWizard extends Component
{
    public ?int $serviceId = null;
    public ?int $staffId = null;
    public string $date = '';
    public ?string $slot = null;
    public string $note = '';
    public ?string $warning = null;

    public function mount(): void
    {
        $this->date = now(config('booking.timezone'))->toDateString();
    }

    #[Computed]
    public function services(): Collection
    {
        return Service::active()->orderBy('name')->get();
    }

    #[Computed]
    public function service(): ?Service
    {
        return $this->serviceId ? Service::find($this->serviceId) : null;
    }

    #[Computed]
    public function staffOptions(): Collection
    {
        return $this->service
            ? $this->service->staffProfiles()->with('user')->where('is_bookable', true)->get()
            : collect();
    }

    #[Computed]
    public function staff(): ?StaffProfile
    {
        return $this->staffId
            ? StaffProfile::with(['user', 'workingHours'])->find($this->staffId)
            : null;
    }

    #[Computed]
    public function slots(): Collection
    {
        if (! $this->staff || ! $this->service) {
            return collect();
        }

        return app(AvailabilityCalculator::class)->slotsFor(
            $this->staff,
            $this->service,
            CarbonImmutable::parse($this->date, config('booking.timezone')),
        );
    }

    public function chooseService(int $id): void
    {
        $this->serviceId = $id;
        $this->staffId = null;
        $this->slot = null;
        $this->warning = null;
    }

    public function chooseStaff(int $id): void
    {
        $this->staffId = $id;
        $this->slot = null;
        $this->warning = null;
    }

    public function updatedDate(): void
    {
        $this->slot = null;
        $this->warning = null;
    }

    public function book(BookAppointment $action)
    {
        $this->validate([
            'serviceId' => ['required', 'integer'],
            'staffId' => ['required', 'integer'],
            'slot' => ['required', 'string'],
        ]);

        try {
            $appointment = $action->handle(
                auth()->user(),
                $this->staff,
                $this->service,
                CarbonImmutable::parse($this->slot),
                $this->note ?: null,
            );
        } catch (SlotUnavailableException $e) {
            $this->slot = null;
            unset($this->slots);
            $this->warning = $e->getMessage().' Please choose another time.';

            return null;
        }

        return $this->redirectRoute('bookings.show', $appointment, navigate: true);
    }

    public function render()
    {
        return view('livewire.booking-wizard');
    }
}
