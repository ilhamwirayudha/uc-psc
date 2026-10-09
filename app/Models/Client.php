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

    protected static function booted(): void
    {
        static::deleting(function (Client $client) {
            $client->clientForms()->delete();
        });
    }

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

    public function getCompanyNameAttribute(): ?string
    {
        if (!$this->isIndustri()) {
            return null;
        }

        // 1. Cek pic_name (pada klien industri digunakan untuk menyimpan nama perusahaan)
        if (!empty($this->attributes['pic_name'])) {
            return trim($this->attributes['pic_name']);
        }

        // 2. Jika occupation berformat "Jabatan - Nama Perusahaan", ambil bagian perusahaan setelah " - "
        if (!empty($this->occupation) && str_contains($this->occupation, ' - ')) {
            $parts = explode(' - ', $this->occupation);
            $candidate = trim(end($parts));
            if (!empty($candidate)) {
                return $candidate;
            }
        }

        // 3. Jika occupation sendiri mengandung entitas nama perusahaan (PT, CV, dll.)
        if (!empty($this->occupation) && preg_match('/(?:PT|CV|UD|Firma|Yayasan|Corp|Ltd|Inc)\b/i', $this->occupation)) {
            return trim($this->occupation);
        }

        // 4. Cari dari data formulir industri (clientForms) jika tersedia
        if ($this->relationLoaded('clientForms') || $this->exists) {
            $industriForm = $this->relationLoaded('clientForms')
                ? $this->clientForms->firstWhere('form_type', 'industri')
                : $this->clientForms()->where('form_type', 'industri')->latest()->first();

            if ($industriForm && !empty($industriForm->answers)) {
                $answers = $industriForm->answers;
                if (!empty($answers['nama_perusahaan'])) {
                    return trim($answers['nama_perusahaan']);
                }
                if (!empty($answers['perusahaan'])) {
                    return trim($answers['perusahaan']);
                }
                if (!empty($answers['instansi_1_nama'])) {
                    return trim($answers['instansi_1_nama']);
                }
            }
        }

        // 5. Cek notes jika mencantumkan info perusahaan
        if (!empty($this->notes) && preg_match('/(?:Nama Perusahaan|Perusahaan|Instansi)\s*:\s*([^\n\r]+)/i', $this->notes, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public function getJenisLabelAttribute(): string
    {
        return in_array(strtolower($this->jenis ?? ''), ['industri', 'company', 'perusahaan'])
            ? 'Industri'
            : 'Individu';
    }

    public function isIndustri(): bool
    {
        return in_array(strtolower($this->jenis ?? ''), ['industri', 'company', 'perusahaan']);
    }

    public function isIndividu(): bool
    {
        return !$this->isIndustri();
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

    public function clientForms(): HasMany
    {
        return $this->hasMany(ClientForm::class);
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

    /**
     * Mengambil daftar seluruh catatan klien terstruktur per entri tanggal.
     * Mendukung data JSON array, teks terformat [tanggal] catatan, maupun legacy plain text.
     */
    public function getNotesListAttribute(): array
    {
        if (empty($this->notes)) {
            // Ambil catatan otomatis dari formulir pendaftaran jika ada
            $forms = $this->relationLoaded('clientForms')
                ? $this->clientForms
                : ($this->exists ? $this->clientForms()->latest()->get() : collect());

            if ($forms->isNotEmpty()) {
                $list = [];
                foreach ($forms as $form) {
                    $noteContent = $this->extractFormNoteContent($form);
                    if (!empty($noteContent)) {
                        $formDate = $form->created_at ?? ($form->consent_agreed_at ?? now());
                        $list[] = [
                            'id' => 'form_note_' . $form->id,
                            'date' => $formDate->format('Y-m-d H:i:s'),
                            'date_display' => $formDate->format('d M Y, H:i') . ' WIB',
                            'date_input' => $formDate->format('Y-m-d\TH:i'),
                            'note' => $noteContent,
                        ];
                    }
                }
                if (!empty($list)) {
                    usort($list, fn($a, $b) => strcmp($b['date'], $a['date']));
                    return $list;
                }
            }

            return [];
        }

        $raw = trim($this->notes);

        // 1. Coba decode sebagai JSON
        if (str_starts_with($raw, '[') || str_starts_with($raw, '{')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $list = [];
                foreach ($decoded as $index => $item) {
                    if (is_array($item)) {
                        $dateStr = $item['date'] ?? null;
                        try {
                            $carbonDate = $dateStr ? \Carbon\Carbon::parse($dateStr) : ($this->created_at ?? now());
                        } catch (\Exception $e) {
                            $carbonDate = $this->created_at ?? now();
                        }

                        $content = trim($item['note'] ?? ($item['content'] ?? ($item['text'] ?? '')));
                        if (!empty($content)) {
                            $list[] = [
                                'id' => (string) ($item['id'] ?? ('note_' . ($index + 1))),
                                'date' => $carbonDate->format('Y-m-d H:i:s'),
                                'date_display' => $carbonDate->format('d M Y, H:i') . ' WIB',
                                'date_input' => $carbonDate->format('Y-m-d\TH:i'),
                                'note' => $content,
                            ];
                        }
                    }
                }
                if (!empty($list)) {
                    usort($list, fn($a, $b) => strcmp($b['date'], $a['date']));
                    return $list;
                }
            }
        }

        // 2. Cek apakah ada penanda waktu bertanda kurung siku, misal: [07/10/2026 15:06] atau [07 Oct 2026, 15:06]
        $pattern = '/(?:^|\n\s*|\r\n\s*)\[([0-9]{1,2}[-\/][0-9]{1,2}[-\/][0-9]{2,4}[^\]]*|[0-9]{1,2}\s+[A-Za-z]{3,}\s+[0-9]{2,4}[^\]]*)\]\s*/u';
        $splits = preg_split($pattern, $raw, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (count($splits) > 1) {
            $list = [];
            for ($i = 0; $i < count($splits); $i += 2) {
                $datePart = $splits[$i] ?? '';
                $textPart = trim($splits[$i + 1] ?? '');

                try {
                    $cleanDatePart = trim(str_replace(['WIB', 'WITA', 'WIT'], '', $datePart));
                    $carbonDate = \Carbon\Carbon::parse($cleanDatePart);
                } catch (\Exception $e) {
                    $carbonDate = $this->created_at ?? now();
                }

                if (!empty($textPart)) {
                    $list[] = [
                        'id' => 'note_' . ($i / 2 + 1),
                        'date' => $carbonDate->format('Y-m-d H:i:s'),
                        'date_display' => $carbonDate->format('d M Y, H:i') . ' WIB',
                        'date_input' => $carbonDate->format('Y-m-d\TH:i'),
                        'note' => $textPart,
                    ];
                }
            }
            if (!empty($list)) {
                usort($list, fn($a, $b) => strcmp($b['date'], $a['date']));
                return $list;
            }
        }

        // 3. Fallback: Data tunggal konvensional
        $singleDate = $this->updated_at ?? ($this->created_at ?? now());
        return [
            [
                'id' => 'note_1',
                'date' => $singleDate->format('Y-m-d H:i:s'),
                'date_display' => $singleDate->format('d M Y, H:i') . ' WIB',
                'date_input' => $singleDate->format('Y-m-d\TH:i'),
                'note' => $raw,
            ]
        ];
    }

    /**
     * Representasi teks bersih dari seluruh catatan klien.
     */
    public function getNotesTextAttribute(): string
    {
        $list = $this->notes_list;
        if (empty($list)) {
            return '';
        }
        if (count($list) === 1 && !str_starts_with(trim($this->notes ?? ''), '[') && !str_starts_with(trim($this->notes ?? ''), '{')) {
            return $list[0]['note'];
        }
        $lines = [];
        foreach ($list as $item) {
            $lines[] = "[{$item['date_display']}]\n{$item['note']}";
        }
        return implode("\n\n", $lines);
    }

    /**
     * Mengekstrak intisari catatan/keluhan/alasan pendaftaran dari data formulir klien.
     */
    public function extractFormNoteContent($form): ?string
    {
        if (!$form) {
            return null;
        }

        $answers = $form->answers ?? [];
        $formType = $form->form_type ?? '';
        $formTitle = $form->form_type_label ?? 'Formulir Pendaftaran';

        $items = [];

        // 1. Ekstraksi spesifik per jenis formulir
        if ($formType === 'dewasa') {
            if (!empty($answers['alasan_konseling'])) {
                $items[] = "Alasan Konseling: " . trim($answers['alasan_konseling']);
            }
            if (!empty($answers['kondisi_saat_ini'])) {
                $items[] = "Kondisi Saat Ini: " . trim($answers['kondisi_saat_ini']);
            }
            if (!empty($answers['pikiran_negatif'])) {
                $items[] = "Keluhan / Pikiran Mengganggu: " . trim($answers['pikiran_negatif']);
            }
            if (!empty($answers['hal_ingin_ditingkatkan'])) {
                $items[] = "Harapan / Hal yang Ingin Ditingkatkan: " . trim($answers['hal_ingin_ditingkatkan']);
            }
        } elseif ($formType === 'anak') {
            if (!empty($answers['alasan_konseling'])) {
                $items[] = "Alasan Konseling: " . trim($answers['alasan_konseling']);
            }
            if (!empty($answers['kondisi_anak_saat_ini'])) {
                $items[] = "Kondisi Anak Saat Ini: " . trim($answers['kondisi_anak_saat_ini']);
            }
            if (!empty($answers['hal_ingin_ditingkatkan'])) {
                $items[] = "Harapan / Hal yang Ingin Ditingkatkan: " . trim($answers['hal_ingin_ditingkatkan']);
            }
        } elseif ($formType === 'pra_nikah') {
            if (!empty($answers['alasan_konseling'])) {
                $items[] = "Alasan Konseling: " . trim($answers['alasan_konseling']);
            }
            if (!empty($answers['keluhan_hubungan'])) {
                $items[] = "Dinamika / Keluhan Relasi: " . trim($answers['keluhan_hubungan']);
            }
            if (!empty($answers['hal_ingin_ditingkatkan'])) {
                $items[] = "Target Pembahasan: " . trim($answers['hal_ingin_ditingkatkan']);
            }
        } elseif ($formType === 'pernikahan') {
            if (!empty($answers['alasan_konseling'])) {
                $items[] = "Alasan Konseling: " . trim($answers['alasan_konseling']);
            }
            if (!empty($answers['keluhan_utama'])) {
                $items[] = "Keluhan Utama: " . trim($answers['keluhan_utama']);
            }
            if (!empty($answers['hal_ingin_ditingkatkan'])) {
                $items[] = "Harapan Perbaikan: " . trim($answers['hal_ingin_ditingkatkan']);
            }
        } elseif ($formType === 'industri') {
            if (!empty($answers['posisi_dituju'])) {
                $items[] = "Posisi yang Dituju: " . trim($answers['posisi_dituju']);
            }
            if (!empty($answers['tujuan_pemeriksaan'])) {
                $items[] = "Tujuan Asesmen: " . trim($answers['tujuan_pemeriksaan']);
            }
            if (!empty($answers['alasan_melamar'])) {
                $items[] = "Alasan Melamar: " . trim($answers['alasan_melamar']);
            }
            if (!empty($answers['instansi_1_alasan_berhenti'])) {
                $items[] = "Alasan Berhenti Kerja Terakhir: " . trim($answers['instansi_1_alasan_berhenti']);
            }
        } elseif ($formType === 'non_industri') {
            if (!empty($answers['cita_cita'])) {
                $items[] = "Cita-cita / Peminatan: " . trim($answers['cita_cita']);
            }
            if (!empty($answers['rencana_cita_cita'])) {
                $items[] = "Rencana Karir/Studi: " . trim($answers['rencana_cita_cita']);
            }
            if (!empty($answers['usaha_cita_cita'])) {
                $items[] = "Usaha yang Dilakukan: " . trim($answers['usaha_cita_cita']);
            }
            if (!empty($answers['tujuan_pemeriksaan'])) {
                $items[] = "Tujuan Asesmen: " . trim($answers['tujuan_pemeriksaan']);
            }
        } elseif ($formType === 'biography_en') {
            if (!empty($answers['dream_job'])) {
                $items[] = "Career Goal / Dream Job: " . trim($answers['dream_job']);
            }
            if (!empty($answers['dream_job_future'])) {
                $items[] = "Future Aspirations: " . trim($answers['dream_job_future']);
            }
            if (!empty($answers['purpose_of_assessment'])) {
                $items[] = "Purpose of Assessment: " . trim($answers['purpose_of_assessment']);
            }
        }

        // 2. Fallback generic
        if (empty($items)) {
            $candidates = [
                'alasan_konseling' => 'Alasan Konseling',
                'keluhan_utama' => 'Keluhan Utama',
                'keluhan' => 'Keluhan',
                'kondisi_saat_ini' => 'Kondisi Saat Ini',
                'tujuan_pemeriksaan' => 'Tujuan Pemeriksaan',
                'posisi_dituju' => 'Posisi Dituju',
                'dream_job' => 'Career Goal',
                'notes' => 'Catatan',
                'catatan' => 'Catatan',
            ];
            foreach ($candidates as $k => $lbl) {
                if (!empty($answers[$k]) && is_string($answers[$k])) {
                    $items[] = "{$lbl}: " . trim($answers[$k]);
                }
            }
        }

        if (empty($items)) {
            if (!empty($form->admin_notes)) {
                return "Formulir {$formTitle}:\n• " . trim($form->admin_notes);
            }
            return "Pendaftaran formulir {$formTitle} telah terisi lengkap.";
        }

        return "Formulir {$formTitle}:\n• " . implode("\n• ", $items);
    }
}
