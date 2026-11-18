<?php

namespace App\Models;

use App\Services\PublicMediaStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Counselor extends Model
{

    public const SPECIALIZATIONS = [
        'Psikolog Klinis Dewasa',
        'Psikolog Anak & Remaja',
        'Psikolog Klinis Umum',
        'Psikolog Pendidikan',
        'Psikolog Industri & Organisasi (PIO)',
        'Psikolog Sosial & Komunitas',
        'Konselor Keluarga & Pernikahan',
        'Konselor Trauma & Krisis',
        'Konselor Adiksi & Rehabilitasi',
        'Konselor Perkembangan & Remaja',
        'Konselor Umum',
    ];

    protected $fillable = [
        'name',
        'photo',
        'phone',
        'email',
        'country',
        'province',
        'city',
        'address',
        'specialization',
        'sipp_number',
        'str_number',
        'status',
        'notes',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        return PublicMediaStorage::url($this->photo);
    }

    public function getFullAddressAttribute(): string
    {
        $rawAddress = trim($this->address ?? '');
        $city = trim($this->city ?? '');
        $province = trim($this->province ?? '');
        $country = trim($this->country ?? '');

        if (empty($rawAddress) && empty($city) && empty($province) && empty($country)) {
            return '-';
        }

        $postalCode = null;
        if (preg_match('/(?:kode\s*pos\s*[:.-]?\s*)?(\b\d{5}\b)/i', $rawAddress, $matches)) {
            $postalCode = $matches[1];
            $cleanedRaw = preg_replace('/,?\s*(?:kode\s*pos\s*[:.-]?\s*)?\b' . $postalCode . '\b/i', '', $rawAddress);
            $rawAddress = trim(preg_replace('/\s*,\s*/', ', ', $cleanedRaw), " ,\t\n\r\0\x0B");
        }

        $parts = [];
        if (!empty($rawAddress)) {
            $parts[] = rtrim($rawAddress, ',');
        }

        if (!empty($city)) {
            if (empty($rawAddress) || !preg_match('/\b' . preg_quote($city, '/') . '\b/i', $rawAddress)) {
                $parts[] = $city;
            }
        }

        if (!empty($province)) {
            if (empty($rawAddress) || !preg_match('/\b' . preg_quote($province, '/') . '\b/i', $rawAddress)) {
                $parts[] = $province;
            }
        }

        if (!empty($country)) {
            if (empty($rawAddress) || !preg_match('/\b' . preg_quote($country, '/') . '\b/i', $rawAddress)) {
                $parts[] = $country;
            }
        }

        if (!empty($postalCode)) {
            $parts[] = $postalCode;
        }

        return !empty($parts) ? implode(', ', $parts) : '-';
    }


    public function pairings(): HasMany
    {
        return $this->hasMany(Pairing::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function counselingBookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function assignedCandidates(): HasMany
    {
        return $this->hasMany(BookingCandidate::class, 'assigned_psikolog_id');
    }

    public function bookingAssignees(): HasMany
    {
        return $this->hasMany(BookingAssignee::class);
    }

    /**
     * Hitung jumlah tugas Konseling & Psikotes yang masih aktif pada konselor/psikolog ini.
     */
    public function countActiveTasks(): int
    {
        $counselingCount = $this->counselingBookings()
            ->where('kategori', 'konseling')
            ->whereNotIn('status', ['selesai', 'batal'])
            ->count();

        $psychotestCount = $this->assignedCandidates()
            ->where('attendance_status', '!=', 'absen')
            ->whereNull('step_draft_hpp_at')
            ->where(function ($q) {
                $q->where('is_kilat', false)->orWhereNull('kilat_sent_at');
            })
            ->whereHas('booking', fn ($q) => $q->where('kategori', 'psikotes')->where('status', '!=', 'batal'))
            ->count();

        return $counselingCount + $psychotestCount;
    }
}
