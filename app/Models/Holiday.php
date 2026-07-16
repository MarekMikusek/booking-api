<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Holidays\Holidays as SpatieHolidays;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public static function isHoliday(Carbon $date): bool
    {
        $country = config('booking.holiday_country', 'pl');

        if (SpatieHolidays::for(country: $country)->isHoliday($date->toDateString())) {
            return true;
        }

        return self::whereDate('date', $date->toDateString())->exists();
    }
}
