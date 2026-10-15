<?php

namespace App\Providers;

use App\Services\AvailabilityCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AvailabilityCalculator::class, fn() => new AvailabilityCalculator(
            intervalMinutes: config('booking.slot_interval'),
            timezone: config('booking.timezone'),
            minHoursNotice: config('booking.min_hours_notice'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Date::use(CarbonImmutable::class);
    }
}
