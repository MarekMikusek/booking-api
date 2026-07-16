<?php

use App\Enums\ReservationStatus;
use App\Models\Holiday;
use App\Models\Location;
use App\Models\Reservation;
use Illuminate\Support\Str;

$location = null;

beforeEach(function () use (&$location) {
    // Tworzymy lokalizację przed każdym testem
    $location = Location::create([
        'name' => 'Sala Testowa',
    ]);
});

it('pobiera prawidłową listę wolnych slotów w zwykły dzień roboczy', function () use (&$location) {
    // Używamy stworzonej lokalizacji
    $response = $this->getJson("/api/slots?date=2026-07-20&location_id={$location->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'available_slots' => [
                '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
                '15:00', '15:30', '16:00', '16:30'
            ]
        ]);
});

it('nie zwraca wolnych slotów w święto państwowe Spatie', function () use (&$location) {
    /** @var \Tests\TestCase $this */
    $response = $this->getJson("/api/slots?date=2026-11-11&location_id={$location->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'available_slots' => []
        ]);
});

it('nie zwraca wolnych slotów w niestandardowe święto zapisane w bazie danych', function () use (&$location) {
    /** @var \Tests\TestCase $this */

    Holiday::create([
        'date' => '2026-07-22',
        'name' => 'Urlop załogi'
    ]);

    $response = $this->getJson("/api/slots?date=2026-07-22&location_id={$location->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'available_slots' => []
        ]);
});

it('pozwala na utworzenie poprawnej rezerwacji i automatycznie generuje token UUID', function () use (&$location) {
    /** @var \Tests\TestCase $this */

    $payload = [
        'location_id' => $location->id,
        'starts_at' => '2026-07-20 09:30:00',
        'customer_name' => 'Jan Kowalski',
        'customer_email' => 'jan@kowalski.pl',
    ];

    $response = $this->postJson('/api/reservations', $payload);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'token',
                'starts_at',
            ]
        ]);

    $reservation = Reservation::latest()->first();
    expect($reservation->ends_at->format('Y-m-d H:i:s'))->toBe('2026-07-20 10:00:00');

    expect(Str::isUuid($response->json('data.token')))->toBeTrue();
});

it('nie pozwala na rezerwację tego samego slotu dwukrotnie', function ()  use (&$location) {
    /** @var \Tests\TestCase $this */
    Reservation::create([
        'location_id' => $location->id,
        'customer_name' => 'Klient 1',
        'customer_email' => 'klient1@test.pl',
        'starts_at' => '2026-07-20 09:30:00',
        'ends_at' => '2026-07-20 10:00:00',
        'status' => 'active'
    ]);

    $payload = [
        'location_id' => $location->id,
        'starts_at' => '2026-07-20 09:30:00',
        'customer_name' => 'Klient 2',
        'customer_email' => 'klient2@test.pl',
    ];

    $response = $this->postJson('/api/reservations', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['starts_at']);
});

it('pozwala na utworzenie rezerwacji w terminie, w którym inna rezerwacja została wcześniej anulowana', function ()  use (&$location) {
    $cancelledReservation = Reservation::create([
        'location_id' => $location->id,
        'customer_name' => 'Jan Kowalski',
        'customer_email' => 'jan@kowalski.pl',
        'starts_at' => '2026-07-20 10:00:00',
        'ends_at' => '2026-07-20 10:30:00',
        'status' => ReservationStatus::CANCELLED,
    ]);

    $payload = [
        'location_id' => $location->id,
        'customer_name' => 'Anna Nowak',
        'customer_email' => 'anna@nowak.pl',
        'starts_at' => '2026-07-20 10:00:00',
    ];

    /** @var \Tests\TestCase $this */
    $response = $this->postJson('/api/reservations', $payload);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'message' => 'Rezerwacja została pomyślnie utworzona!'
        ]);

    /** @var \Tests\TestCase $this */
    $this->assertDatabaseHas('reservations', [
        'customer_email' => 'anna@nowak.pl',
        'starts_at' => '2026-07-20 10:00:00',
        'status' => ReservationStatus::ACTIVE->value,
    ]);
});
