<?php

use App\Enums\ReservationStatus;
use App\Models\Location;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Str;

it('anuluje rezerwację, jeśli podano poprawny token z dużym wyprzedzeniem', function () {
    /** @var \Tests\TestCase $this */

    $location = Location::create(['name' => 'Salon Główny']);
    $reservation = Reservation::create([
        'location_id' => $location->id,
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

    expect($reservation->fresh()->status)->toBe(ReservationStatus::CANCELLED);
});

it('blokuje próbę anulowania przy użyciu nieprawidłowego tokenu', function () {
    /** @var \Tests\TestCase $this */

    $location = Location::create(['name' => 'Salon Główny']);
    $reservation = Reservation::create([
        'location_id' => $location->id,
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
    /** @var \Tests\TestCase $this */
    $location = Location::create(['name' => 'Salon Główny']);

    $reservation = Reservation::create([
        'location_id' => $location->id,
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
