<?php
namespace App\Actions;

use App\DTOs\ReservationDTO;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Queries\ReservationQuery;
use App\Repositories\ReservationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReservationAction
{
    public function __construct(
        private readonly ReservationQuery $reservationQuery,
        private readonly ReservationRepository $reservationRepository
    ) {}

    public function execute(ReservationDTO $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $startsAt   = Carbon::parse($data->startsAt);
            $locationId = (int) $data->locationId;
            $date       = $startsAt->copy()->startOfDay();

            $availableSlots = $this->reservationQuery->getAvailableSlots($date, $locationId, lock: true);

            $requestedSlotTime = $startsAt->format('H:i');

            if (! in_array($requestedSlotTime, $availableSlots)) {
                throw ValidationException::withMessages([
                    'starts_at' => ['Wybrany termin jest już zajęty lub nieczynny.'],
                ]);
            }

            return $this->reservationRepository->create([
                'location_id' => $locationId,
                'customer_name' => $data->customerName,
                'customer_email' => $data->customerEmail,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes(config('booking.slot_duration_minutes', 30)),
                'status' => ReservationStatus::ACTIVE,
            ]);
        });
    }
}
