<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetSlotsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'location_id' => $this->input('location_id', 1),
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ];
    }
}
