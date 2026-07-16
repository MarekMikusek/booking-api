<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
{
   public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'uuid'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'token' => $this->query('token'),
        ]);
    }
}
