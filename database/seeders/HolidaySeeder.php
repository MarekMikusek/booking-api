<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        Holiday::updateOrCreate(
            ['date' => '2026-12-24'],
            ['name' => 'Wigilia - przerwa świąteczna']
        );

        Holiday::updateOrCreate(
            ['date' => '2026-05-02'],
            ['name' => 'Weekend majowy - wolne firmy']
        );
    }
}
