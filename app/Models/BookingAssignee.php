<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingAssignee extends Model
{
    public const ROLE_LABELS = [
        'psikolog' => 'Psikolog',
        'tester' => 'Tester',
        'skorer_manual' => 'Skorer Manual',
        'skorer_software' => 'Skorer Software',
        'editor' => 'Editor',
    ];

    protected $fillable = [
        'booking_id',
        'role_type',
        'user_id',
        'counselor_id',
        'batch_completed_at',
        'completed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'batch_completed_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Counselor::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role_type] ?? ucfirst($this->role_type);
    }

    public function getAssigneeNameAttribute(): string
    {
        if ($this->role_type === 'psikolog') {
            return $this->counselor?->name ?? $this->user?->name ?? '-';
        }

        return $this->user?->name ?? '-';
    }
}
