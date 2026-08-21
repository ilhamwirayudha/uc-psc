<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counselor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'specialization',
        'sipp_number',
        'str_number',
        'status',
        'notes',
    ];

    public function counselingRecords(): HasMany
    {
        return $this->hasMany(CounselingRecord::class);
    }

    public function pairings(): HasMany
    {
        return $this->hasMany(Pairing::class);
    }
}
