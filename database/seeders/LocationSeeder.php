<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'siedziba firmy',
                'weekday_start' => '09:00',
                'weekday_end' => '17:00',
                'saturday_start' => '10:00',
                'saturday_end' => '14:20',
            ]
        );
    }
}
