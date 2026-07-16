<?php

namespace App\DTOs;

use App\Http\Requests\StoreHolidayRequest;
use Carbon\Carbon;

readonly class HolidayDTO
{
    public function __construct(
        public Carbon $date,
        public ?string $name,
    ) {}

    public static function fromRequest(StoreHolidayRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            Carbon::parse($validated['date']),
            $validated['name'] ?? null
        );
    }
}
