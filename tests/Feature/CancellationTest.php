<?php

use App\Enums\ReservationStatus;
use App\Models\Location;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->location = Location::create([
            'name' => 'Salon Główny',
        ]);
    });

it('anuluje rezerwację, jeśli podano poprawny token z dużym wyprzedzeniem', function () {
    // Tworzymy rezerwację na jutro (czyli > 2h wolnego czasu)
    $reservation = Reservation::create([
        'location_id' => $this->location->id,
        'customer_name' => 'Anna Nowak',
        'customer_email' => 'anna@nowak.pl',
        'starts_at' => Carbon::now()->addDay()->setHour(12)->setMinute(0),
        'ends_at' => Carbon::now()->addDay()->setHour(12)->setMinute(30),
        'status' => ReservationStatus::ACTIVE,
    ]);

    $token = $reservation->token;

    $response = $this->deleteJson("/api/reservations/{$reservation->id}?token={$token}");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Rezerwacja została pomyślnie anulowana.']);

    // Sprawdzamy czy w bazie status zmienił się na CANCELLED
    expect($reservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
});

it('blokuje próbę anulowania przy użyciu nieprawidłowego tokenu', function () {
    $reservation = Reservation::create([
        'location_id' => $this->location->id,
        'customer_name' => 'Anna Nowak',
        'customer_email' => 'anna@nowak.pl',
        'starts_at' => Carbon::now()->addDay(),
        'ends_at' => Carbon::now()->addDay()->addMinutes(30),
        'status' => ReservationStatus::ACTIVE,
    ]);

    $badToken = (string) Str::uuid();

    $response = $this->deleteJson("/api/reservations/{$reservation->id}?token={$badToken}");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['token']);
});

it('nie pozwala na anulowanie rezerwacji po jej rozpoczęciu', function () {
    // Rezerwacja za 1 godzinę od teraz
    $reservation = Reservation::create([
        'location_id' => $this->location->id,
        'customer_name' => 'Spóźnialski Jan',
        'customer_email' => 'jan@test.pl',
        'starts_at' => Carbon::now()->addHour(),
        'ends_at' => Carbon::now()->addHour()->addMinutes(30),
        'status' => ReservationStatus::ACTIVE,
    ]);

    $token = $reservation->token;

    $response = $this->deleteJson("/api/reservations/{$reservation->id}?token={$token}");

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['reservation']);
});
