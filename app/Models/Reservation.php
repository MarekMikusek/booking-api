<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $token
 * @property \Carbon\Carbon $starts_at
 * @property \Carbon\Carbon $ends_at
 * @property string $customer_name
 * @property string $customer_email
 * @property \App\Enums\ReservationStatus $status
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'location_id',
        'starts_at',
        'ends_at',
        'status'
        ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => ReservationStatus::class
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Reservation $reservation) {
            $reservation->token = (string) Str::uuid();
        });
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', ReservationStatus::ACTIVE);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
