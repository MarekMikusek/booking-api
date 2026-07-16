<?php

namespace App\Repositories;

use App\Models\Location;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationRepository
{
public function getActiveReservationsForDate(Carbon $date, Location $location, bool $lock = false): array
    {
        $query = Reservation::query()
            ->where('location_id', $location->id)
            ->active()
            ->whereBetween('starts_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()]);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->pluck('starts_at')
            ->map(fn($dt) => $dt->format('H:i'))
            ->toArray();
    }

    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }
}
