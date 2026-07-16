<?php

namespace App\Http\Controllers;

use App\Actions\CancelReservationAction;
use App\Actions\CreateReservationAction;
use App\DTOs\ReservationDTO;
use App\Http\Requests\CancelReservationRequest;
use App\Http\Requests\GetSlotsRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Queries\ReservationQuery;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationQuery $reservationQuery
    ) {}

    public function slots(GetSlotsRequest $request): JsonResponse
    {
        $date = Carbon::parse($request->validated('date'));
        $locationId = (int) $request->validated('location_id');

        $slots = $this->reservationQuery->getAvailableSlots($date, $locationId);

        return response()->json([
            'date' => $date->toDateString(),
            'location_id' => $locationId,
            'available_slots' => $slots,
        ]);
    }

    public function store(
        StoreReservationRequest $request,
        CreateReservationAction $createReservationAction
    ): JsonResponse
    {
        $dto = ReservationDTO::fromRequest($request);
        $reservation = $createReservationAction->execute($dto);


        return response()->json([
            'message' => 'Rezerwacja została pomyślnie utworzona!',
            'data' => $reservation
        ], Response::HTTP_CREATED);
    }

    public function destroy(int $id, CancelReservationRequest $request, CancelReservationAction $cancelReservationAction): JsonResponse
    {
        $cancelReservationAction->execute($id, $request->validated('token'));

        return response()->json([
            'message' => 'Rezerwacja została pomyślnie anulowana.'
        ], Response::HTTP_OK);
    }
}
