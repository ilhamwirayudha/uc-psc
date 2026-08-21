<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'jenis',
        'phone',
        'email',
        'gender',
        'dob',
        'source',
        'service_type',
        'counseling_type',
        'status',
        'notes',
        'created_by',
        'assigned_staff_id',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function staffAssignmentHistory(): HasMany
    {
        return $this->hasMany(ClientStaffAssignment::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function counselingRecords(): HasMany
    {
        return $this->hasMany(CounselingRecord::class);
    }

    public function pairings(): HasMany
    {
        return $this->hasMany(Pairing::class);
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'l' => 'Laki-laki',
            'p' => 'Perempuan',
            'non_binary' => 'Non-biner',
            'transgender' => 'Transgender',
            'prefer_not_to_say' => 'Memilih Tidak Menyebutkan',
            'other' => 'Lainnya',
            default => '-',
        };
    }
}
