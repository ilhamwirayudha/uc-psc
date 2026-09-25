<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{

    protected $fillable = [
        'name',
        'pic_name',
        'jenis',
        'phone',
        'email',
        'gender',
        'birth_place',
        'dob',
        'religion',
        'occupation',
        'education',
        'marital_status',
        'country',
        'province',
        'city',
        'address',
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

    public function getPicNameAttribute(): ?string
    {
        if (!empty($this->attributes['pic_name'])) {
            return $this->attributes['pic_name'];
        }

        if (!empty($this->notes) && preg_match('/(?:PIC \/ Perwakilan Kelompok|PIC Perusahaan|Nama PIC|PIC)\s*:\s*([^\n\r]+)/i', $this->notes, $matches)) {
            return trim($matches[1]);
        }

        if ($this->relationLoaded('clientParticipants') && $this->clientParticipants->isNotEmpty()) {
            return $this->clientParticipants->first()->nama_peserta;
        }

        return null;
    }

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

    public function clientParticipants(): HasMany
    {
        return $this->hasMany(ClientParticipant::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
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

    public function getEducationLabelAttribute(): string
    {
        return match($this->education) {
            'sd' => 'SD / Sederajat',
            'smp' => 'SMP / Sederajat',
            'sma_smk' => 'SMA / SMK / Sederajat',
            'd3' => 'Diploma / D3',
            's1' => 'Sarjana / S1 / D4',
            's2' => 'Magister / S2',
            's3' => 'Doktor / S3',
            'lainnya' => 'Lainnya',
            default => $this->education ?: '-',
        };
    }

    public function getMaritalStatusLabelAttribute(): string
    {
        return match($this->marital_status) {
            'belum_menikah' => 'Belum Menikah',
            'menikah' => 'Menikah',
            'cerai_hidup' => 'Cerai Hidup',
            'cerai_mati' => 'Cerai Mati',
            default => $this->marital_status ?: '-',
        };
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

        // Ekstrak 5 digit kode pos jika terdapat dalam raw address
        $postalCode = null;
        if (preg_match('/(?:kode\s*pos\s*[:.-]?\s*)?(\b\d{5}\b)/i', $rawAddress, $matches)) {
            $postalCode = $matches[1];
            // Bersihkan kode pos dari teks jalan/kelurahan/kecamatan agar tidak berulang
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

        // Letakkan kode pos di paling akhir setelah negara
        if (!empty($postalCode)) {
            $parts[] = $postalCode;
        }

        return !empty($parts) ? implode(', ', $parts) : '-';
    }
}
