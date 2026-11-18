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
        'company_id',
        'pic_name',
        'client_form_id',
        'kategori',
        'service_tier',
        'service_package_key',
        'service_package_name',
        'price',
        'final_price',
        'overtime_price',
        'billing_scheme',
        'requires_outgoing_letter',
        'delivery_mode',
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
        // Realisasi sesi & kalkulasi overtime
        'session_start',
        'session_end',
        'session_duration_standard',
        'overtime_minutes',
        'actual_duration_minutes',
        'is_overtime',
        'payment_workflow_status',
        'va_number',
        'finance_invoice_number',
        'payment_proof_notes',
        'payment_proof_path',
        'paid_at',
        'overtime_payment_status',
        'overtime_va_number',
        'overtime_final_price',
        'is_payment_forfeited',
        'forfeited_amount',
        'forfeited_reason',
        'rescheduled_at',
        'gcal_sync_status',
        'last_gcal_synced_at',
        'session_notes',
        'session_type',
        'location',
    ];

    /**
     * Buat data booking awal otomatis ketika formulir layanan klien disubmit.
     * Detail penugasan konselor/staff, jadwal, waktu, lokasi, status, dan catatan
     * dibiarkan kosong/default agar dapat diisi menyusul oleh admin/user.
     */
    public static function createInitialForForm(Client $client, string $formType, ?string $servicePreference = null, ?int $clientFormId = null): self
    {
        $isKonseling = in_array($formType, ['dewasa', 'anak', 'pra_nikah', 'pernikahan']);
        $isPsikotes = in_array($formType, ['industri', 'non_industri']);
        $isBiography = ($formType === 'biography_en');

        $kategori = null;
        $serviceTier = null;

        if ($isKonseling) {
            $kategori = 'konseling';
        } elseif ($isPsikotes) {
            $kategori = 'psikotes';
            $serviceTier = ($formType === 'industri' ? 'industri' : 'non_industri_bakat_minat');
        } elseif ($isBiography) {
            // Biography Form (English) fleksibel untuk WNA (Konseling WNA atau Psikotes)
            $kategori = 'pending';
        }

        $sessionType = null;
        if ($isKonseling && !empty($servicePreference)) {
            $sessionType = str_contains(strtolower($servicePreference), 'online') ? 'online' : 'tatap_muka';
        }

        $booking = self::create([
            'client_id' => $client->id,
            'client_form_id' => $clientFormId,
            'kategori' => $kategori,
            'service_tier' => $serviceTier,
            'tanggal_booking_dibuat' => now()->toDateString(),
            'tanggal_dijadwalkan' => null,
            'session_start' => null,
            'session_end' => null,
            'session_duration_standard' => 60,
            'overtime_minutes' => 0,
            'session_notes' => null,
            'session_type' => $sessionType,
            'location' => null,
            'status' => 'belum_assign',
            'staff_penguji_id' => null,
            'staff_koreksi_id' => null,
            'staff_pelapor_id' => null,
            'counselor_id' => null,
            'notes' => null,
        ]);

        if ($formType === 'industri' && !empty($client->name)) {
            BookingParticipant::create([
                'booking_id' => $booking->id,
                'nama_peserta' => $client->name,
            ]);
            ClientParticipant::firstOrCreate([
                'client_id' => $client->id,
                'nama_peserta' => $client->name,
            ]);
        }

        return $booking;
    }

    protected function casts(): array
    {
        return [
            'tanggal_booking_dibuat' => 'date',
            'tanggal_dijadwalkan' => 'date',
            'requires_outgoing_letter' => 'boolean',
            'is_overtime' => 'boolean',
            'is_payment_forfeited' => 'boolean',
            'price' => 'decimal:2',
            'final_price' => 'decimal:2',
            'overtime_price' => 'decimal:2',
            'overtime_final_price' => 'decimal:2',
            'forfeited_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'rescheduled_at' => 'datetime',
            'last_gcal_synced_at' => 'datetime',
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

    // --- Relasi ke formulir klien & workflow psikotes ---

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function serviceItems(): HasMany
    {
        return $this->hasMany(BookingServiceItem::class);
    }

    public function assignees(): HasMany
    {
        return $this->hasMany(BookingAssignee::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(BookingCandidate::class);
    }

    public function googleCalendarEvents(): HasMany
    {
        return $this->hasMany(GoogleCalendarEvent::class);
    }

    public function clientForms(): HasMany
    {
        return $this->hasMany(ClientForm::class);
    }

    public function clientForm(): BelongsTo
    {
        return $this->belongsTo(ClientForm::class, 'client_form_id');
    }

    // --- Helper Bisnis UC PSC ---

    public function isPaid(): bool
    {
        return in_array($this->payment_workflow_status, ['lunas', 'lunas_overtime'], true)
            || !is_null($this->paid_at);
    }

    public function allowsDeliveryBeforePayment(): bool
    {
        return in_array($this->billing_scheme, ['I', 'R', '-'], true);
    }

    public function isSpecialNonIndustri(): bool
    {
        return $this->service_tier === 'non_industri_khusus';
    }

    public function getBillingSchemeLabelAttribute(): string
    {
        return match ($this->billing_scheme) {
            'I' => 'Rekap Internal UCS (I)',
            'R' => 'Rekap Perusahaan (R)',
            '-' => 'Gratis / Deal Khusus (-)',
            default => 'Reguler / Invoice (v)',
        };
    }

    public function getDeliveryModeLabelAttribute(): string
    {
        return match ($this->delivery_mode) {
            'hardcopy' => 'Hardcopy',
            'both' => 'Softcopy & Hardcopy',
            default => 'Softcopy',
        };
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->final_price ?? $this->price ?? 0);
    }

    /**
     * Format ringkasan petugas per peran (misal: "Sasa (2) Sisi (2)").
     */
    public function formatPsychologistQuotaSummary(): string
    {
        $candidates = $this->relationLoaded('candidates') ? $this->candidates : $this->candidates()->with('assignedPsikolog')->get();
        if ($candidates->isEmpty()) {
            $assignees = $this->relationLoaded('assignees')
                ? $this->assignees->where('role_type', 'psikolog')
                : $this->assignees()->where('role_type', 'psikolog')->with('counselor')->get();
            return $assignees->map(fn ($a) => $a->counselor?->name)->filter()->implode(', ') ?: '-';
        }

        $grouped = $candidates->whereNotNull('assigned_psikolog_id')->groupBy('assigned_psikolog_id');
        if ($grouped->isEmpty()) {
            return '-';
        }

        return $grouped->map(function ($items) {
            $name = $items->first()->assignedPsikolog?->name ?? 'Psikolog';
            return "{$name} ({$items->count()})";
        })->implode(', ');
    }

    public function formatAssigneeSummary(string $roleType): string
    {
        if ($roleType === 'psikolog') {
            return $this->formatPsychologistQuotaSummary();
        }

        if ($roleType === 'editor') {
            $candidates = $this->relationLoaded('candidates') ? $this->candidates : $this->candidates()->with('assignedEditor')->get();
            $grouped = $candidates->whereNotNull('assigned_editor_id')->groupBy('assigned_editor_id');
            if ($grouped->isNotEmpty()) {
                return $grouped->map(function ($items) {
                    $name = $items->first()->assignedEditor?->name ?? 'Editor';
                    return "{$name} ({$items->count()})";
                })->implode(', ');
            }
        }

        $assignees = $this->relationLoaded('assignees')
            ? $this->assignees->where('role_type', $roleType)
            : $this->assignees()->where('role_type', $roleType)->with('user')->get();

        return $assignees->map(fn ($a) => $a->user?->name)->filter()->implode(', ') ?: '-';
    }

    public function syncOverallCompletionStatus(): void
    {
        if ($this->kategori !== 'psikotes') {
            return;
        }

        $candidates = $this->candidates()->get();
        if ($candidates->isEmpty()) {
            return;
        }

        $nonKilat = $candidates->where('is_kilat', false);
        $targetSet = $nonKilat->isNotEmpty() ? $nonKilat : $candidates;
        $allDelivered = $targetSet->every(fn (BookingCandidate $c) => $c->isCompletedForDelivery());

        if ($allDelivered && $this->status !== 'selesai') {
            $this->update(['status' => 'selesai']);
        } elseif (!$allDelivered && $this->status === 'selesai') {
            $this->update(['status' => 'baru']);
        }
    }
}

