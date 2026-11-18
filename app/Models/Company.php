<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'category',
        'pic_name',
        'pic_phone',
        'pic_email',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category === 'kerja_sama' ? 'Kerja Sama' : 'Umum';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
