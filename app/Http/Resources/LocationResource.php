<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weekday_start' => $this->weekday_start,
            'weekday_end' => $this->weekday_end,
            'saturday_start' => $this->saturday_start,
            'saturday_end' => $this->saturday_end,
        ];
    }
}
