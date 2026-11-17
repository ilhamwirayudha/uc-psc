<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingServiceItem extends Model
{
    protected $fillable = [
        'booking_id',
        'service_tier',
        'service_package_key',
        'service_package_name',
        'quantity',
        'unit_price',
        'subtotal_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal_price' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(BookingCandidate::class);
    }
}
