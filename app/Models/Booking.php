<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{

    protected $fillable = [
        'client_id',
        'kategori',
        'tanggal_booking_dibuat',
        'tanggal_dijadwalkan',
        'status',
        'staff_penguji_id',
        'staff_koreksi_id',
        'staff_pelapor_id',
        'counselor_id',
        'follow_up_of_booking_id',
        'payment_transaction_id',
        'notes',
        // Realisasi sesi
        'session_start',
        'session_end',
        'session_duration_standard',
        'overtime_minutes',
        'session_notes',
        'session_type',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_booking_dibuat' => 'date',
            'tanggal_dijadwalkan' => 'date',
        ];
    }

    // --- Computed Attributes untuk Realisasi Sesi ---

    /**
     * Apakah sesi sudah direalisasikan (jam masuk & keluar sudah diinput).
     */
    public function getIsRealizedAttribute(): bool
    {
        return !is_null($this->session_start) && !is_null($this->session_end);
    }

    /**
     * Total durasi sesi dalam menit (berdasarkan session_start & session_end).
     */
    public function getSessionDurationMinutesAttribute(): ?int
    {
        if (!$this->is_realized) return null;
        [$sh, $sm] = explode(':', substr($this->session_start, 0, 5));
        [$eh, $em] = explode(':', substr($this->session_end, 0, 5));
        $startMin = (int)$sh * 60 + (int)$sm;
        $endMin   = (int)$eh * 60 + (int)$em;
        return max(0, $endMin - $startMin);
    }

    /**
     * Apakah sesi ini overtime.
     */
    public function getIsOvertimeAttribute(): bool
    {
        $duration = $this->session_duration_minutes;
        if ($duration === null) return false;
        return $duration > $this->session_duration_standard;
    }

    /**
     * Label durasi sesi untuk tampilan (misal: "1 jam 40 menit").
     */
    public function getSessionDurationLabelAttribute(): ?string
    {
        $total = $this->session_duration_minutes;
        if ($total === null) return null;
        $hours   = intdiv($total, 60);
        $minutes = $total % 60;
        $parts   = [];
        if ($hours > 0) $parts[] = $hours . ' jam';
        if ($minutes > 0) $parts[] = $minutes . ' menit';
        return implode(' ', $parts) ?: '0 menit';
    }

    /**
     * Menit overtime (durasi - standar).
     */
    public function getOvertimeMinutesComputedAttribute(): int
    {
        $total = $this->session_duration_minutes;
        if ($total === null) return 0;
        return max(0, $total - $this->session_duration_standard);
    }

    // --- Relasi ke model lain ---

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function staffPenguji(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_penguji_id');
    }

    public function staffKoreksi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_koreksi_id');
    }

    public function staffPelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_pelapor_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Counselor::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    // --- Self-referencing relasi (follow-up chain) ---

    public function followUpOf(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'follow_up_of_booking_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(Booking::class, 'follow_up_of_booking_id');
    }

    // --- Relasi ke peserta ---

    public function participants(): HasMany
    {
        return $this->hasMany(BookingParticipant::class);
    }

    // --- Relasi ke hasil psikotes / assessment ---

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    public function testResult(): HasOne
    {
        return $this->hasOne(TestResult::class)->latestOfMany();
    }
}

