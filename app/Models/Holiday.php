<?php

namespace App\Models;

use App\Services\PsychotestSlaService;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Holiday $holiday): void {
            app(PsychotestSlaService::class)->recalculateDeadlinesForDates([
                $holiday->getOriginal('date'),
                $holiday->date,
            ]);
        });

        static::deleted(function (Holiday $holiday): void {
            app(PsychotestSlaService::class)->recalculateDeadlinesForDates([
                $holiday->date,
            ]);
        });
    }
}
