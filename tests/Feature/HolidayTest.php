<?php

use App\Models\Holiday;

it('pozwala na dodanie nowego święta z podaną nazwą', function () {
    $payload = [
        'date' => '2026-12-25',
        'name' => 'Boże Narodzenie',
    ];

    $response = $this->postJson('/api/holiday', $payload);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Dzień wolny został pomyślnie dodany.']);

    $this->assertDatabaseHas('holidays', $payload);
});

it('pozwala na dodanie święta bez podanej nazwy', function () {
    $payload = [
        'date' => '2026-11-01',
    ];

    $response = $this->postJson('/api/holiday', $payload);

    $response->assertStatus(201);
    $this->assertDatabaseHas('holidays', $payload);
});

it('nie pozwala na dodanie święta z datą, która już istnieje w bazie', function () {
    Holiday::create([
        'date' => '2026-01-01',
        'name' => 'Nowy Rok'
    ]);

    $payload = [
        'date' => '2026-01-01',
        'name' => 'Inna nazwa dla tej samej daty'
    ];

    $response = $this->postJson('/api/holiday', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date']);
});

it('zwraca błąd walidacji dla nieprawidłowego formatu daty', function () {
    $payload = [
        'date' => 'nie-jest-data',
        'name' => 'Błędne święto'
    ];

    $response = $this->postJson('/api/holiday', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['date']);
});
