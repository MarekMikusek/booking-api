<?php

namespace App\Actions;

use App\DTOs\HolidayDTO;
use App\Models\Holiday;

class CreateHolidayAction
{
    public function execute(HolidayDTO $data): Holiday
    {
        return Holiday::create([
            'date' => $data->date,
            'name' => $data->name,
        ]);
    }
}
