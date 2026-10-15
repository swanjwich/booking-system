<?php

namespace App\Observers;

use App\Models\Appointment;
use Illuminate\Support\Str;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function creating(Appointment $appointment): void
    {
        if (blank($appointment->reference)) {
            do {
                $reference = 'BK-' . Str::upper(Str::random(6));
            } while (Appointment::where('reference', $reference)->exists());

            $appointment->reference = $reference;
        }
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        //
    }
}
