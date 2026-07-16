<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $name
 * @property string $weekday_start
 * @property string $weekday_end
 * @property string $saturday_start
 * @property string $saturday_end
 */
class Location extends Model
{
    protected $fillable = [
        'name',
        'weekday_start',
        'weekday_end',
        'saturday_start',
        'saturday_end',
    ];

    protected static function booted(): void
    {
        static::saved(fn (Location $location) => Cache::forget("location:{$location->id}"));
        static::deleted(fn (Location $location) => Cache::forget("location:{$location->id}"));
    }

    public static function findCached(int $id): self
    {
        return Cache::remember("location:{$id}", now()->addDay(), function () use ($id) {
            return self::findOrFail($id);
        });
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
