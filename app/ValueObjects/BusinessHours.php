<?php

namespace App\ValueObjects;

use App\Models\Location;
use Carbon\Carbon;

class BusinessHours
{
    public function __construct(
        private readonly Carbon $date,
        private readonly Location $location
    )
    {}

    public function isOpen(): bool
    {
        return !$this->date->isSunday();
    }

    public function getOpeningTime(): ?Carbon
    {
        if(!$this->isOpen()){
            return null;
        }

        $timeString = $this->date->isSaturday()
            ? $this->location->saturday_start
            : $this->location->weekday_start;

        return $this->setTimeFromConfig($this->date, $timeString);
    }

    public function getClosingTime(): ?Carbon
    {
        if (!$this->isOpen()) {
            return null;
        }

        $timeString = $this->date->isSaturday()
            ? $this->location->saturday_end
            : $this->location->weekday_end;

        return $this->setTimeFromConfig($this->date, $timeString);
    }

    public function containsSlot(Carbon $startsAt, Carbon $endsAt): bool
    {
        $opening = $this->getOpeningTime();
        $closing = $this->getClosingTime();

        if (!$opening || !$closing) {
            return false;
        }

        return $startsAt->greaterThanOrEqualTo($opening)
            && $endsAt->lessThanOrEqualTo($closing);
    }

    public function getPossibleSlots(): array
    {
        if (!$this->isOpen()) {
            return [];
        }

        $slots = [];
        $currentTime = $this->getOpeningTime();
        $endTime = $this->getClosingTime();
        $duration = config('booking.slot_duration_minutes', 30);

        while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slots[] = $currentTime->format('H:i');
            $currentTime->addMinutes($duration);
        }

        return $slots;
    }

    private function setTimeFromConfig(Carbon $date, string $timeString): Carbon
    {
        $normalizedTime = str_replace('.', ':', $timeString);

        return $date->copy()->setTimeFromTimeString($normalizedTime);
    }
}
