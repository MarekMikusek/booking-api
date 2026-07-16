<?php

use App\Models\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Dynamicznie wykrywa klucz używany przez model do zapisu w Cache
 */
function getCacheKeyForLocation(int $id): string
{
    $possibleKeys = [
        "location.{$id}",
        "location:{$id}",
        "location_{$id}",
        "locations.{$id}",
        "locations:{$id}",
        "locations_{$id}",
        "location-{$id}",
    ];

    return collect($possibleKeys)->first(fn($key) => Cache::has($key)) ?? "location.{$id}";
}

it('zapisuje lokalizację w cache przy pierwszym pobraniu i nie odpytuje bazy za drugim razem', function () {
    $location = Location::create([
        'name' => 'Salon Cache',
    ]);

    $id = $location->id;

    // 1. Pierwsze pobranie: powinno wbić dane do cache
    $cachedLocation = Location::findCached($id);
    expect($cachedLocation->name)->toBe('Salon Cache');

    // Znajdujemy poprawny klucz cache dynamicznie
    $cacheKey = getCacheKeyForLocation($id);
    expect(Cache::has($cacheKey))->toBeTrue();

    // 2. Symulujemy usunięcie rekordu z bazy "pod spodem" bezpośrednio przez SQL
    DB::table('locations')->where('id', $id)->delete();

    // 3. Drugie pobranie: powinno pójść z cache (baza jest pusta, ale dane nadal są dostępne)
    $cachedAgain = Location::findCached($id);
    expect($cachedAgain)->not->toBeNull();
    expect($cachedAgain->name)->toBe('Salon Cache');
});

it('unieważnia i czyści cache po edycji modelu Location', function () {
    $location = Location::create([
        'name' => 'Salon Przed Zmianą',
    ]);

    $id = $location->id;

    // Wrzucamy do cache
    Location::findCached($id);

    // Znajdujemy poprawny klucz cache dynamicznie
    $cacheKey = getCacheKeyForLocation($id);
    expect(Cache::has($cacheKey))->toBeTrue();

    // Edytujemy nazwę lokalizacji (powinno wywołać event 'saved' i wyczyścić cache)
    $location->update(['name' => 'Salon Po Zmianie']);

    // Sprawdzamy czy cache został wyczyszczony (busting)
    expect(Cache::has($cacheKey))->toBeFalse();
});
