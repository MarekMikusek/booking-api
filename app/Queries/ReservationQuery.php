<?php

namespace App\Queries;

use App\Models\Holiday;
use App\Models\Location;
use App\Repositories\ReservationRepository;
use App\ValueObjects\BusinessHours;
use Carbon\Carbon;

class ReservationQuery
{
    public function __construct(private readonly ReservationRepository $reservationRepository) {}

    public function getAvailableSlots(Carbon $date, int $locationId, bool $lock = false): array
    {
        if (Holiday::isHoliday($date)) {
            return [];
        }

        $location = Location::findCached($locationId);
        $businessHours = new BusinessHours($date, $location);
        $allPossibleSlots = $businessHours->getPossibleSlots();

        $bookedSlots = $this->reservationRepository->getActiveReservationsForDate($date, $location, $lock);

        return array_values(array_diff($allPossibleSlots, $bookedSlots));
    }

}
