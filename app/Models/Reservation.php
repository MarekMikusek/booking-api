<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
