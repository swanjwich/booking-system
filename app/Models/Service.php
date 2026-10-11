<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'buffer_minutes',
        'price_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'buffer_minutes' => 'integer',
            'price_amount' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function staffProfiles(): BelongsToMany
    {
        return $this->belongsToMany(StaffProfile::class)
            ->withPivot(['price_override', 'duration_override'])
            ->withTimestamps();
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            $service->slug = Str::slug($service->name);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected function price(): Attribute
    {
        return Attribute::get(
            fn(): string => Number::currency($this->price_amount / 100, 'PHP')
        );
    }

    protected function totalMinutes(): Attribute
    {
        return Attribute::get(
            fn(): int => $this->duration_minutes + $this->buffer_minutes
        );
    }
}
