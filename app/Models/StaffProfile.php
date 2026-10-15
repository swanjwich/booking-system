<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'bio', 'photo_path', 'is_bookable'];

    protected function casts(): array
    {
        return ['is_bookable' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->withPivot(['price_override', 'duration_override'])
            ->withTimestamps();
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }

    public function timeOffs(): HasMany
    {
        return $this->hasMany(TimeOff::class);
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn(): string => $this->user->name);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
