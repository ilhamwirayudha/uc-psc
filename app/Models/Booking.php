<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

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
    ];

    protected function casts(): array
    {
        return [
            'tanggal_booking_dibuat' => 'date',
            'tanggal_dijadwalkan' => 'date',
        ];
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
}
