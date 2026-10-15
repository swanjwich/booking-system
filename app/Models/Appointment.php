<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Observers\AppointmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(AppointmentObserver::class)]
class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'staff_profile_id',
        'service_id',
        'starts_at',
        'ends_at',
        'blocked_until',
        'active_slot_at',
        'status',
        'price_amount',
        'duration_minutes',
        'buffer_minutes',
        'customer_note',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'blocked_until' => 'immutable_datetime',
            'active_slot_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'status' => AppointmentStatus::class,
            'price_amount' => 'integer',
            'duration_minutes' => 'integer',
            'buffer_minutes' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeBlocking(Builder $query): void
    {
        $query->whereIn('status', AppointmentStatus::blocking());
    }

    public function scopeForStaff(Builder $query, StaffProfile|int $staff): void
    {
        $query->where('staff_profile_id', $staff instanceof StaffProfile ? $staff->id : $staff);
    }

    public function scopeOverlapping(Builder $query, $start, $end): void
    {
        $query->where('starts_at', '<', $end)
              ->where('blocked_until', '>', $start);
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }
}
