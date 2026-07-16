<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Http\Requests\StoreReservationRequest;
use Carbon\Carbon;

readonly class ReservationDTO
{
    public function __construct(
        public int $locationId,
        public Carbon $startsAt,
        public string $customerName,
        public string $customerEmail,
    ) {}

    public static function fromRequest(StoreReservationRequest $request): self
    {
        return new self(
            locationId: (int) $request->validated('location_id'),
            startsAt: Carbon::parse($request->validated('starts_at')),
            customerName: (string) $request->validated('customer_name'),
            customerEmail: (string) $request->validated('customer_email'),
        );
    }
}
