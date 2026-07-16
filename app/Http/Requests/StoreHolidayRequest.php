<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date|unique:holidays,date',
            'name' => 'nullable|string|max:255',
        ];
    }
}
