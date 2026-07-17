<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GenerateReservations extends Command
{
    protected $signature = 'reservations:generate
                            {count=10000 : Liczba rezerwacji do wygenerowania}
                            {--chunk=500 : Wielkość paczki zapisu (bulk insert)}';

    protected $description = 'Szybkie generowanie dużej ilości danych do tabeli reservations z zachowaniem integralności bazy danych';

    public function handle(): int
    {
        $count = (int) $this->argument('count');
        $chunkSize = (int) $this->option('chunk');

        $this->info("Rozpoczynam przygotowania do wygenerowania {$count} rezerwacji...");

        $locationIds = DB::table('locations')->pluck('id')->toArray();

        if (empty($locationIds)) {
            $this->error('Brak rekordów w tabeli locations! Najpierw dodaj lokalizacje.');
            return self::FAILURE;
        }

        $this->info("Znaleziono lokalizacje. Generowanie danych...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $data = [];
        $now = now();

        $lastStartsAtPerLocation = [];
        foreach ($locationIds as $locId) {
            $lastStartsAtPerLocation[$locId] = Carbon::now()->startOfHour();
        }

        for ($i = 1; $i <= $count; $i++) {
            $locationId = $locationIds[array_rand($locationIds)];

            $lastStartsAtPerLocation[$locationId] = $lastStartsAtPerLocation[$locationId]->addHours(rand(1, 4));

            $startsAt = $lastStartsAtPerLocation[$locationId]->copy();
            $endsAt = $startsAt->copy()->addHours(rand(1, 3));

            $status = rand(1, 10) > 2 ? 'active' : 'cancelled';

            $data[] = [
                'location_id'    => $locationId,
                'customer_name'  => 'Klient_' . Str::random(6),
                'customer_email' => 'klient_' . Str::random(6) . '@example.com',
                'starts_at'      => $startsAt->toDateTimeString(),
                'ends_at'        => $endsAt->toDateTimeString(),
                'status'         => $status,
                'token'          => (string) Str::uuid(),
                'created_at'     => $now->toDateTimeString(),
                'updated_at'     => $now->toDateTimeString(),
            ];

            if (count($data) >= $chunkSize) {
                DB::table('reservations')->insert($data);
                $bar->advance(count($data));
                $data = [];
            }
        }

        if (count($data) > 0) {
            DB::table('reservations')->insert($data);
            $bar->advance(count($data));
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Sukces! Pomyślnie wygenerowano {$count} rezerwacji.");

        return self::SUCCESS;
    }
}
