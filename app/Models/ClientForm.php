<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientForm extends Model
{
    protected $fillable = [
        'ticket_number',
        'form_type',
        'client_id',
        'booking_id',
        'client_name',
        'client_phone',
        'consent_agreed',
        'consent_agreed_at',
        'service_preference',
        'status',
        'answers',
        'admin_notes',
        'ip_address',
    ];

    protected $casts = [
        'consent_agreed' => 'boolean',
        'consent_agreed_at' => 'datetime',
        'answers' => 'array',
    ];

    protected $appends = [
        'form_type_label',
        'pdf_filename',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function getPdfFilenameAttribute(): string
    {
        $clientName = trim($this->client_name ?? $this->client?->name ?? ($this->answers['nama_lengkap'] ?? ''));
        if (empty($clientName)) {
            $clientName = $this->answers['nama_lengkap'] 
                ?? $this->answers['fullname'] 
                ?? $this->answers['full_name'] 
                ?? $this->answers['nama'] 
                ?? ($this->client?->name ?? 'Pasien');
        }

        $serviceLabel = $this->form_type_label ?? 'Layanan Konseling';

        $dateSource = $this->created_at ?? $this->consent_agreed_at ?? now();
        // Format tanggal dan jam: dd-mm-yyyy HH.ii.ss (contoh: 07-10-2026 15.06.55)
        // Catatan: Karakter '/' dan ':' dilarang oleh OS Windows/Mac/Linux pada nama file, sehingga digunakan '-' dan '.'
        $dateFormatted = $dateSource->format('d-m-Y H.i.s');

        // Bersihkan karakter yang dilarang pada penamaan file Windows: / \ : * ? " < > |
        $cleanName = trim(str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $clientName));
        $cleanService = trim(str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $serviceLabel));

        if (empty($cleanName)) {
            $cleanName = 'Pasien';
        }
        if (empty($cleanService)) {
            $cleanService = 'Layanan Konseling';
        }

        return "{$cleanName} - {$cleanService} - {$dateFormatted}";
    }

    public function getFormTypeLabelAttribute(): string
    {
        return match($this->form_type) {
            'anak' => 'Konseling Anak',
            'dewasa' => 'Konseling Dewasa',
            'pra_nikah' => 'Konseling Pra-Nikah',
            'pernikahan' => 'Konseling Pernikahan',
            'industri' => 'Layanan Industri',
            'non_industri' => 'Layanan Non-Industri',
            'biography_en' => 'Biography Form (English)',
            default => ucfirst(str_replace('_', ' ', $this->form_type)),
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'baru' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Baru Masuk'],
            'diproses' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Sedang Diproses'],
            'selesai' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Selesai Terjadwal'],
            'dibatalkan' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Dibatalkan'],
            default => ['bg' => 'bg-gray-50 text-gray-700 border-gray-200', 'label' => ucfirst($this->status)],
        };
    }

    public static function generateTicketNumber(string $formType): string
    {
        $prefix = match($formType) {
            'anak' => 'ANK',
            'dewasa' => 'DWS',
            'pra_nikah' => 'PRN',
            'pernikahan' => 'NIK',
            'industri' => 'IND',
            'non_industri' => 'NIN',
            'biography_en' => 'BIO',
            default => 'PSC',
        };

        $dateStr = now()->format('ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        return "PSC-{$prefix}-{$dateStr}-{$random}";
    }
}
