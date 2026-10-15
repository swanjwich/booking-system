<?php

namespace App\Support;

use Carbon\CarbonImmutable;

final readonly class TimeSlot
{
    public function __construct(
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
        public CarbonImmutable $blockedUntil,
    ) {}

    public static function for(CarbonImmutable $start, int $duration, int $buffer = 0): self
    {
        return new self(
            $start,
            $start->addMinutes($duration),
            $start->addMinutes($duration + $buffer),
        );
    }

    public function overlaps(CarbonImmutable $otherStart, CarbonImmutable $otherEnd): bool
    {
        return $this->startsAt < $otherEnd && $this->blockedUntil > $otherStart;
    }

    public function label(string $timezone): string
    {
        return $this->startsAt->setTimezone($timezone)->format('g:i A');
    }
}
