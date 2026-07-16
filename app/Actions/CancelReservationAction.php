<?php

namespace App\Actions;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Validation\ValidationException;

class CancelReservationAction
{
    public function execute(int $id, string $token): void
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->token !== $token) {
            throw ValidationException::withMessages(['token' => 'Nieprawidłowy token autoryzacyjny dla tej rezerwacji.']);
        }

        if ($reservation->starts_at->isBefore(now()->addHours(2))) {
            throw ValidationException::withMessages(['reservation' => 'Rezerwację można anulować najpóźniej 2 godziny przed jej rozpoczęciem.']);
        }

        $reservation->update([
            'status' => ReservationStatus::CANCELLED
        ]);
    }
}
