<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCandidate extends Model
{
    protected $fillable = [
        'booking_id',
        'client_id',
        'client_form_id',
        'booking_service_item_id',
        'candidate_name',
        'target_position',
        'job_level',
        'is_kilat',
        'attendance_status',
        'assigned_psikolog_id',
        'assigned_editor_id',
        'psychologist_deadline_date',
        'client_deadline_date',
        'step_psikotes_at',
        'step_psikotes_by',
        'step_skoring_at',
        'step_skoring_by',
        'step_surat_keluar_at',
        'step_surat_keluar_by',
        'step_draft_hpp_at',
        'step_draft_hpp_by',
        'step_editing_at',
        'step_editing_by',
        'step_cetak_at',
        'step_cetak_by',
        'step_approval_at',
        'step_approval_by',
        'step_kirim_at',
        'step_kirim_by',
        'softcopy_sent_at',
        'softcopy_sent_by',
        'kilat_sent_at',
        'kilat_sent_by',
        'resi_date',
        'resi_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_kilat' => 'boolean',
            'psychologist_deadline_date' => 'date',
            'client_deadline_date' => 'date',
            'step_psikotes_at' => 'datetime',
            'step_skoring_at' => 'datetime',
            'step_surat_keluar_at' => 'datetime',
            'step_draft_hpp_at' => 'datetime',
            'step_editing_at' => 'datetime',
            'step_cetak_at' => 'datetime',
            'step_approval_at' => 'datetime',
            'step_kirim_at' => 'datetime',
            'softcopy_sent_at' => 'datetime',
            'kilat_sent_at' => 'datetime',
            'resi_date' => 'date',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientForm(): BelongsTo
    {
        return $this->belongsTo(ClientForm::class);
    }

    public function serviceItem(): BelongsTo
    {
        return $this->belongsTo(BookingServiceItem::class, 'booking_service_item_id');
    }

    public function assignedPsikolog(): BelongsTo
    {
        return $this->belongsTo(Counselor::class, 'assigned_psikolog_id');
    }

    public function assignedEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_editor_id');
    }

    public function isAbsent(): bool
    {
        return $this->attendance_status === 'absen';
    }

    public function isSpecialPackage(): bool
    {
        if ($this->relationLoaded('serviceItem') ? $this->serviceItem : $this->serviceItem()->first()) {
            $item = $this->relationLoaded('serviceItem') ? $this->serviceItem : $this->serviceItem()->first();
            return $item->service_tier === 'non_industri_khusus';
        }

        return $this->booking?->isSpecialNonIndustri() ?? false;
    }

    public function isPsychologistReportOverdue(): bool
    {
        if ($this->isAbsent() || !$this->psychologist_deadline_date || $this->step_draft_hpp_at || ($this->is_kilat && $this->kilat_sent_at)) {
            return false;
        }

        return now()->startOfDay()->gt($this->psychologist_deadline_date->startOfDay());
    }

    public function isCompletedForDelivery(): bool
    {
        if ($this->isAbsent()) {
            return true;
        }

        if ($this->is_kilat && $this->kilat_sent_at) {
            return true;
        }

        return !is_null($this->step_kirim_at);
    }
}
