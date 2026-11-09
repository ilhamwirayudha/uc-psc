@extends('layouts.dashboard')

@section('title', 'Detail Klien — ' . $client->name . ' — UC PSC')
@section('page-title', 'Detail Klien')
@section('page-subtitle', 'Informasi profil, riwayat seluruh layanan terdaftar, reservasi konseling, dan berkas asesmen')
@section('content')
@php
    $isIndustri = in_array(strtolower($client->jenis ?? ''), ['industri', 'company', 'perusahaan']);
    $allParticipants = $client->clientParticipants;
    $totalBookings = $client->bookings->count();
        $serviceCatalogJson = config('service_catalog');

    $counselingForms = $client->clientForms->filter(function($f) {
        if (in_array($f->form_type, ['dewasa', 'anak', 'pra_nikah', 'pernikahan'])) return true;
        if ($f->form_type === 'biography_en') {
            return !$f->booking || $f->booking->kategori === 'konseling' || $f->booking->kategori === 'pending';
        }
        return false;
    });
        $psychotestForms = $client->clientForms->filter(function($f) {
        if (in_array($f->form_type, ['industri', 'non_industri'])) return true;
        if ($f->form_type === 'biography_en') {
            return !$f->booking || $f->booking->kategori === 'psikotes' || $f->booking->kategori === 'pending';
        }
        return false;
    });

    $counselingFormsJson = $counselingForms->map(function($f) {
        $ans = $f->answers ?? [];
        $rawPref = $ans['preferensi_konseling'] 
            ?? $ans['preferensi_proses_konseling'] 
            ?? $ans['media_konseling'] 
            ?? $f->service_preference 
            ?? ($f->booking?->session_type ?? '');
        
        $mediaKonseling = '-';
        if (is_string($rawPref)) {
            if (stripos($rawPref, 'offline') !== false || stripos($rawPref, 'tatap_muka') !== false || stripos($rawPref, 'tatap muka') !== false) {
                $mediaKonseling = 'Offline';
            } elseif (stripos($rawPref, 'online') !== false || stripos($rawPref, 'zoom') !== false) {
                $mediaKonseling = 'Online';
            } elseif (!empty(trim($rawPref))) {
                $mediaKonseling = trim($rawPref);
            }
        }

        return array_merge($f->toArray(), [
            'ticket' => $f->ticket_number ?? '#' . $f->id,
            'form_type_label' => $f->form_type_label,
            'created_at_raw' => $f->created_at ? $f->created_at->format('Y-m-d H:i:s') : '',
            'created_at_formatted' => $f->created_at ? $f->created_at->format('d/m/Y H:i') : '-',
            'media_konseling' => $mediaKonseling,
            'booking' => $f->booking ? [
            'id' => $f->booking->id,
            'kategori' => $f->booking->kategori,
            'status' => $f->booking->status,
            'service_tier' => $f->booking->service_tier,
            'service_package_key' => $f->booking->service_package_key,
            'service_package_name' => $f->booking->service_package_name,
            'price' => $f->booking->price,
            'price_formatted' => $f->booking->price ? 'Rp ' . number_format($f->booking->price, 0, ',', '.') : '-',
            'tanggal_dijadwalkan' => $f->booking->tanggal_dijadwalkan ? $f->booking->tanggal_dijadwalkan->format('Y-m-d') : null,
            'tanggal_dijadwalkan_formatted' => $f->booking->tanggal_dijadwalkan ? $f->booking->tanggal_dijadwalkan->locale('id')->isoFormat('dddd, D MMMM Y') : null,
            'session_start' => $f->booking->session_start ? substr($f->booking->session_start, 0, 5) : '09:00',
            'session_end' => $f->booking->session_end ? substr($f->booking->session_end, 0, 5) : '10:30',
            'session_type' => $f->booking->session_type ?? 'tatap_muka',
            'location' => $f->booking->location ?? '',
            'counselor_id' => $f->booking->counselor_id,
            'counselor_name' => $f->booking->counselor?->name,
            'staff_penguji_id' => $f->booking->staff_penguji_id,
            'staff_penguji_name' => $f->booking->staffPenguji?->name,
            'notes' => $f->booking->notes ?? '',
        ] : null,
    ]);
})->values();

    $psychotestFormsJson = $psychotestForms->map(function($f) {
        return array_merge($f->toArray(), [
        'ticket' => $f->ticket_number ?? '#' . $f->id,
        'form_type_label' => $f->form_type_label,
        'created_at_raw' => $f->created_at ? $f->created_at->format('Y-m-d H:i:s') : '',
        'created_at_formatted' => $f->created_at ? $f->created_at->format('d/m/Y H:i') : '-',
        'booking' => $f->booking ? [
            'id' => $f->booking->id,
            'kategori' => $f->booking->kategori,
            'status' => $f->booking->status,
            'service_tier' => $f->booking->service_tier,
            'service_package_key' => $f->booking->service_package_key,
            'service_package_name' => $f->booking->service_package_name,
            'price' => $f->booking->price,
            'price_formatted' => $f->booking->price ? 'Rp ' . number_format($f->booking->price, 0, ',', '.') : '-',
            'tanggal_dijadwalkan' => $f->booking->tanggal_dijadwalkan ? $f->booking->tanggal_dijadwalkan->format('Y-m-d') : null,
            'tanggal_dijadwalkan_formatted' => $f->booking->tanggal_dijadwalkan ? $f->booking->tanggal_dijadwalkan->locale('id')->isoFormat('dddd, D MMMM Y') : null,
            'session_start' => $f->booking->session_start ? substr($f->booking->session_start, 0, 5) : '09:00',
            'session_end' => $f->booking->session_end ? substr($f->booking->session_end, 0, 5) : '10:30',
            'session_type' => $f->booking->session_type ?? 'tatap_muka',
            'location' => $f->booking->location ?? '',
            'counselor_id' => $f->booking->counselor_id,
            'counselor_name' => $f->booking->counselor?->name,
            'staff_penguji_id' => $f->booking->staff_penguji_id,
            'staff_penguji_name' => $f->booking->staffPenguji?->name,
            'notes' => $f->booking->notes ?? '',
        ] : null,
    ]);
})->values();

    // Data Booking Konseling
    $counselingBookings = $client->bookings->filter(function($b) {
        if ($b->kategori === 'konseling') return true;
        $formType = $b->clientForm?->form_type ?? $b->clientForms->first()?->form_type;
        if (in_array($formType, ['dewasa', 'anak', 'pra_nikah', 'pernikahan'])) return true;
        if ($formType === 'biography_en' && $b->kategori !== 'psikotes') return true;
        return false;
    });

    $counselingBookingsJson = $counselingBookings->map(function($b) {
        $form = $b->clientForm ?? $b->clientForms->first();
        
        $sessionTypeLabel = '-';
        if ($b->session_type === 'online') {
            $sessionTypeLabel = 'Online';
        } elseif ($b->session_type === 'tatap_muka' || $b->session_type === 'offline') {
            $sessionTypeLabel = 'Offline';
        } elseif (!empty($b->session_type)) {
            $sessionTypeLabel = ucfirst($b->session_type);
        }

        // Pemetaan Status Layanan Ringkas sesuai Google Sheets UC PSC
        $statusLabel = match($b->status) {
            'data_masuk' => 'Data Masuk',
            'waiting_list' => 'Waiting List',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'terjadwal_gocal' => 'Terjadwal (Gocal)',
            'reminder_h1' => 'Reminder H-1',
            'perlu_bayar_overtime' => 'Tagih Overtime',
            'perlu_sesi_lanjutan' => 'Perlu Lanjutan',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            default => ($b->status === 'lanjutan' ? 'Perlu Lanjutan' : ($b->status === 'selesai' ? 'Selesai' : ($b->tanggal_dijadwalkan ? 'Dijadwalkan' : 'Data Masuk'))),
        };

        // Pemetaan Status Pembayaran Ringkas (Opsi 1: Direct & Clean)
        $paymentLabel = match($b->payment_workflow_status) {
            'draft_ke_finance' => 'Pengajuan Nota',
            'menunggu_bayar' => 'Menunggu VA',
            'lunas' => 'Lunas',
            'lunas_overtime' => 'Lunas (Overtime)',
            default => 'Belum Ada Tagihan',
        };

        // Durasi aktual & status overtime (Aturan 1 sesi = 60 menit, > 90 menit = Overtime)
        $durationFormatted = '-';
        $overtimeLabel = 'Tidak';
        if ($b->session_start && $b->session_end) {
            $sh = (int) substr($b->session_start, 0, 2);
            $sm = (int) substr($b->session_start, 3, 2);
            $eh = (int) substr($b->session_end, 0, 2);
            $em = (int) substr($b->session_end, 3, 2);
            $dur = ($eh * 60 + $em) - ($sh * 60 + $sm);
            if ($dur > 0) {
                $h = floor($dur / 60);
                $m = $dur % 60;
                $durationFormatted = ($h > 0 ? "{$h}j " : '') . ($m > 0 ? "{$m}m" : '');
                if ($dur > 90) {
                    $overtimeLabel = 'Overtime';
                }
            }
        }

        $scheduleFormatted = '-';
        if ($b->tanggal_dijadwalkan) {
            $tgl = $b->tanggal_dijadwalkan->locale('id')->isoFormat('D MMMM Y');
            $jam = ($b->session_start && $b->session_end) 
                ? ' (' . substr($b->session_start, 0, 5) . ' - ' . substr($b->session_end, 0, 5) . ' WIB)'
                : '';
            $scheduleFormatted = $tgl . $jam;
        }

        $pkgName = $b->service_package_name ?: ($b->service_tier ? ucwords(str_replace('_', ' ', $b->service_tier)) : '-');
        $counselingJenis = str_contains(strtolower($pkgName), 'reguler') ? 'Reguler' : (str_contains(strtolower($pkgName), 'pro') ? 'Pro' : '-');
        $counselingMetode = (str_contains(strtolower($pkgName), 'online') || $b->session_type === 'online') ? 'Online' : ((str_contains(strtolower($pkgName), 'offline') || $b->session_type === 'tatap_muka' || $b->session_type === 'offline') ? 'Offline' : $sessionTypeLabel);
        $counselingKlien = str_contains(strtoupper($pkgName), 'WNA') ? 'WNA' : (str_contains(strtoupper($pkgName), 'WNI') ? 'WNI' : '-');
        $counselingKategori = str_contains(strtolower($pkgName), 'kerja sama') ? 'Kerja Sama' : (str_contains(strtolower($pkgName), 'umum') ? 'Umum' : '-');

        return [
            'id' => $b->id,
            'ticket' => '#' . $b->id,
            'form_ticket' => $form?->ticket_number,
            'client_form_id' => $form?->id,
            'layanan_name' => 'Konseling',
            'service_package_name' => $pkgName,
            'counseling_jenis' => $counselingJenis,
            'counseling_metode' => $counselingMetode,
            'counseling_klien' => $counselingKlien,
            'counseling_kategori' => $counselingKategori,
            'duration_formatted' => $durationFormatted,
            'overtime_label' => $overtimeLabel,
            'payment_label' => $paymentLabel,
            'schedule_formatted' => $scheduleFormatted,
            'tanggal_dijadwalkan_raw' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('Y-m-d') : '',
            'counselor_name' => $b->counselor?->name ?: '-',
            'media_konseling' => $counselingMetode,
            'status_label' => $statusLabel,
            'status' => $b->status,
            'detail_url' => route('bookings.show', $b->id),
            'reschedule_url' => route('bookings.edit', $b->id),
            'raw_form' => $form ? array_merge($form->toArray(), [
                'ticket' => $form->ticket_number ?? '#' . $form->id,
                'form_type_label' => $form->form_type_label,
                'booking' => [
                    'id' => $b->id,
                    'kategori' => $b->kategori,
                    'status' => $b->status,
                    'service_tier' => $b->service_tier,
                    'service_package_key' => $b->service_package_key,
                    'service_package_name' => $b->service_package_name,
                    'price' => $b->price,
                    'price_formatted' => $b->price ? 'Rp ' . number_format($b->price, 0, ',', '.') : '-',
                    'tanggal_dijadwalkan' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('Y-m-d') : null,
                    'session_start' => $b->session_start ? substr($b->session_start, 0, 5) : '09:00',
                    'session_end' => $b->session_end ? substr($b->session_end, 0, 5) : '10:30',
                    'session_type' => $b->session_type ?? 'tatap_muka',
                    'location' => $b->location ?? '',
                    'counselor_id' => $b->counselor_id,
                    'counselor_name' => $b->counselor?->name,
                    'staff_penguji_id' => $b->staff_penguji_id,
                    'staff_penguji_name' => $b->staffPenguji?->name,
                    'notes' => $b->notes ?? '',
                ]
            ]) : null,
        ];
    })->values();

    // Data Booking Psikotes
    $psychotestBookings = $client->bookings->filter(function($b) {
        if ($b->kategori === 'psikotes') return true;
        $formType = $b->clientForm?->form_type ?? $b->clientForms->first()?->form_type;
        if (in_array($formType, ['industri', 'non_industri'])) return true;
        if ($formType === 'biography_en' && $b->kategori === 'psikotes') return true;
        return false;
    });

    
    // Riwayat Seluruh Layanan Klien (Konseling & Psikotes) Terpadu
    $allServiceHistory = $client->bookings->sortByDesc('created_at')->values();
    $allServiceHistoryJson = $allServiceHistory->map(function($b) {
        $form = $b->clientForm ?? $b->clientForms->first();
        $isKonseling = $b->kategori === 'konseling';
        
        $sessionTypeLabel = '-';
        if ($b->session_type === 'online') {
            $sessionTypeLabel = 'Online';
        } elseif ($b->session_type === 'tatap_muka' || $b->session_type === 'offline') {
            $sessionTypeLabel = 'Offline';
        } elseif (!empty($b->session_type)) {
            $sessionTypeLabel = ucfirst($b->session_type);
        }

        $statusLabel = match($b->status) {
            'data_masuk' => 'Data Masuk',
            'waiting_list' => 'Waiting List',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'terjadwal_gocal' => 'Terjadwal (Gocal)',
            'reminder_h1' => 'Reminder H-1',
            'perlu_bayar_overtime' => 'Tagih Overtime',
            'perlu_sesi_lanjutan' => 'Perlu Lanjutan',
            'selesai' => 'Selesai',
            'batal' => 'Dibatalkan',
            default => ($b->status === 'lanjutan' ? 'Perlu Lanjutan' : ($b->status === 'selesai' ? 'Selesai' : ($b->tanggal_dijadwalkan ? 'Dijadwalkan' : 'Data Masuk'))),
        };

        $pkgName = $b->service_package_name ?: ($b->service_tier ? ucwords(str_replace('_', ' ', $b->service_tier)) : ($isKonseling ? 'Layanan Konseling' : 'Layanan Psikotes'));

        $bookingDateFormatted = $b->tanggal_booking_dibuat ? $b->tanggal_booking_dibuat->format('d/m/Y') : '-';
        $scheduledDateFormatted = '-';
        if ($b->tanggal_dijadwalkan) {
            $tgl = $b->tanggal_dijadwalkan->format('d/m/Y');
            $jam = ($b->session_start && $b->session_end) 
                ? ' ' . substr($b->session_start, 0, 5) . ' - ' . substr($b->session_end, 0, 5)
                : '';
            $scheduledDateFormatted = $tgl . $jam;
        }

        return [
            'id' => $b->id,
            'ticket' => '#' . $b->id,
            'kategori' => $b->kategori,
            'kategori_label' => ucfirst($b->kategori),
            'package_name' => $pkgName,
            'session_type' => $sessionTypeLabel,
            'booking_date' => $bookingDateFormatted,
            'booking_date_raw' => $b->tanggal_booking_dibuat ? $b->tanggal_booking_dibuat->format('Y-m-d H:i:s') : '',
            'scheduled_date' => $scheduledDateFormatted,
            'scheduled_date_raw' => $b->tanggal_dijadwalkan ? ($b->tanggal_dijadwalkan->format('Y-m-d') . ' ' . ($b->session_start ?: '00:00:00')) : '',
            'time_range' => ($b->session_start && $b->session_end) ? substr($b->session_start, 0, 5) . ' - ' . substr($b->session_end, 0, 5) : '-',
            'counselor_name' => $isKonseling ? ($b->counselor?->name ?? '-') : ($b->staffPenguji?->name ?? '-'),
            'status' => $b->status,
            'status_label' => $statusLabel,
            'price_raw' => (float) ($b->price ?? 0),
            'price_formatted' => $b->price ? 'Rp ' . number_format($b->price, 0, ',', '.') : '-',
            'show_url' => route('bookings.show', $b),
        ];
    });

    $psychotestBookingsJson = $psychotestBookings->map(function($b) {
        $form = $b->clientForm ?? $b->clientForms->first();
        
        $methodLabel = '-';
        if ($b->session_type === 'online') {
            $methodLabel = 'Online';
        } elseif ($b->session_type === 'tatap_muka' || $b->session_type === 'offline') {
            $methodLabel = 'Offline';
        } elseif (!empty($b->session_type)) {
            $methodLabel = ucfirst($b->session_type);
        }
        if ($b->location) {
            $methodLabel .= ' (' . $b->location . ')';
        }

        $statusLabel = 'Menunggu Penjadwalan';
        if ($b->status === 'selesai') {
            $statusLabel = 'Selesai';
        } elseif ($b->status === 'batal') {
            $statusLabel = 'Dibatalkan';
        } elseif ($b->tanggal_dijadwalkan) {
            $statusLabel = 'Dijadwalkan';
        }

        $scheduleFormatted = '-';
        if ($b->tanggal_dijadwalkan) {
            $tgl = $b->tanggal_dijadwalkan->locale('id')->isoFormat('D MMMM Y');
            $jam = ($b->session_start && $b->session_end) 
                ? ' (' . substr($b->session_start, 0, 5) . ' - ' . substr($b->session_end, 0, 5) . ' WIB)'
                : '';
            $scheduleFormatted = $tgl . $jam;
        }

        return [
            'id' => $b->id,
            'ticket' => '#' . $b->id,
            'form_ticket' => $form?->ticket_number,
            'client_form_id' => $form?->id,
            'service_package_name' => $b->service_package_name ?: ($b->service_tier ? ucwords(str_replace('_', ' ', $b->service_tier)) : '-'),
            'schedule_formatted' => $scheduleFormatted,
            'tanggal_dijadwalkan_raw' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('Y-m-d') : '',
            'staff_penguji_name' => $b->staffPenguji?->name ?: '-',
            'metode_lokasi' => $methodLabel,
            'status_label' => $statusLabel,
            'status' => $b->status,
            'detail_url' => route('bookings.show', $b->id),
            'reschedule_url' => route('bookings.edit', $b->id),
            'raw_form' => $form ? array_merge($form->toArray(), [
                'ticket' => $form->ticket_number ?? '#' . $form->id,
                'form_type_label' => $form->form_type_label,
                'booking' => [
                    'id' => $b->id,
                    'kategori' => $b->kategori,
                    'status' => $b->status,
                    'service_tier' => $b->service_tier,
                    'service_package_key' => $b->service_package_key,
                    'service_package_name' => $b->service_package_name,
                    'price' => $b->price,
                    'price_formatted' => $b->price ? 'Rp ' . number_format($b->price, 0, ',', '.') : '-',
                    'tanggal_dijadwalkan' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('Y-m-d') : null,
                    'session_start' => $b->session_start ? substr($b->session_start, 0, 5) : '09:00',
                    'session_end' => $b->session_end ? substr($b->session_end, 0, 5) : '10:30',
                    'session_type' => $b->session_type ?? 'tatap_muka',
                    'location' => $b->location ?? '',
                    'counselor_id' => $b->counselor_id,
                    'counselor_name' => $b->counselor?->name,
                    'staff_penguji_id' => $b->staff_penguji_id,
                    'staff_penguji_name' => $b->staffPenguji?->name,
                    'notes' => $b->notes ?? '',
                ]
            ]) : null,
        ];
    })->values();
    $counselingTotal = $counselingForms->count();
    $psychotestTotal = $psychotestForms->count();
    $cleanPhone = preg_replace('/[^0-9]/', '', $client->phone ?? '');
    if (str_starts_with($cleanPhone, '0')) {
        $waPhone = '62' . substr($cleanPhone, 1);
    } elseif (str_starts_with($cleanPhone, '62')) {
        $waPhone = $cleanPhone;
    } elseif (!empty($cleanPhone)) {
        $waPhone = '62' . $cleanPhone;
    } else {
        $waPhone = '';
    }
    // Inisial Klien untuk Profile Picture (misal: "M. Ilham Fadhilah" -> "MI", "Ilham" -> "IL")
    $nameClean = trim($client->name ?? '');
    $words = preg_split('/[\s,\.\-_]+/', $nameClean, -1, PREG_SPLIT_NO_EMPTY);
    if (count($words) >= 2) {
        $clientInitials = strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
    } elseif (count($words) === 1 && mb_strlen($words[0]) >= 2) {
        $clientInitials = strtoupper(mb_substr($words[0], 0, 2));
    } elseif (count($words) === 1) {
        $clientInitials = strtoupper(mb_substr($words[0], 0, 1));
    } else {
        $clientInitials = 'KL';
    }
@endphp

<script>
function clientProfileDetail() {
    return {
        activeTab: @js(in_array(request('tab'), ['profile', 'notes', 'konseling', 'psychotest', 'history', 'settings']) ? request('tab') : 'profile'),
        assignModalOpen: false,
        reassignModalOpen: false,
        unassignModalOpen: false,
        deleteModalOpen: false,
        shareFormDropdownOpen: false,
        editMode: false,
        // State & Modal Konfirmasi Perubahan Belum Disimpan (Unsaved Changes)
        showUnsavedChangesModal: false,
        pendingActionType: null, // 'tab' atau 'navigate' atau 'cancel_edit'
        pendingTarget: null, // targetTab atau targetUrl
        formData: {
            name: @js($client->name ?? ''),
            company_name: @js($client->company_name ?? ($client->pic_name ?? '')),
            occupation: @js($jobTitle ?? ($client->occupation ?? '')),
            gender: @js(in_array(strtolower($client->gender ?? ''), ['l', 'male', 'laki-laki', 'pria']) ? 'l' : (in_array(strtolower($client->gender ?? ''), ['p', 'female', 'perempuan', 'wanita']) ? 'p' : ($client->gender ?? ''))),
            birth_place: @js($client->birth_place ?? ''),
            dob: @js($client->dob ? $client->dob->format('Y-m-d') : ''),
            phone: @js($client->phone ?? ''),
            email: @js($client->email ?? ''),
            education: @js($client->education ?? ''),
            religion: @js($client->religion ?? ''),
            address: @js($client->address ?? '')
        },
        initialFormData: null,
        initFormData() {
            if (!this.initialFormData) {
                this.initialFormData = JSON.parse(JSON.stringify(this.formData));
            }
        },
        isFormDirty() {
            if (!this.editMode) return false;
            this.initFormData();
            return JSON.stringify(this.formData) !== JSON.stringify(this.initialFormData);
        },
        confirmDiscard() {
            this.showUnsavedChangesModal = false;
            if (this.initialFormData) {
                this.formData = JSON.parse(JSON.stringify(this.initialFormData));
            }
            this.editMode = false;
            if (this.pendingActionType === 'tab' && this.pendingTarget) {
                this.activeTab = this.pendingTarget;
            } else if (this.pendingActionType === 'navigate' && this.pendingTarget) {
                window.location.href = this.pendingTarget;
            }
            this.pendingActionType = null;
            this.pendingTarget = null;
        },
        handleToggleEdit() {
            if (this.editMode) {
                this.handleCancelEdit();
            } else {
                this.activeTab = 'profile';
                this.initFormData();
                this.editMode = true;
            }
        },
        handleCancelEdit() {
            if (this.isFormDirty()) {
                this.pendingActionType = 'cancel_edit';
                this.pendingTarget = null;
                this.showUnsavedChangesModal = true;
                return;
            }
            if (this.initialFormData) {
                this.formData = JSON.parse(JSON.stringify(this.initialFormData));
            }
            this.editMode = false;
        },
        handleTabClick(targetTab) {
            if (this.activeTab === targetTab) return;
            if (this.editMode && this.isFormDirty()) {
                this.pendingActionType = 'tab';
                this.pendingTarget = targetTab;
                this.showUnsavedChangesModal = true;
                return;
            }
            this.editMode = false;
            this.activeTab = targetTab;
        },
        // Modal Dossier Formulir
        showDossierModal: false,
        selectedForm: null,
        isPrintingPdf: false,

        // Modal Penetapan Jadwal & Paket Layanan
        showScheduleModal: false,
        schedulingForm: null,
        scheduleCategory: 'konseling',
        scheduleCategoryLocked: true,
        scheduleTier: 'pro',
        schedulePackageKey: 'pro_wni_umum',
        scheduleCounselorId: '',
        scheduleStaffId: '',
        scheduleDate: '',
        scheduleStart: '09:00',
        scheduleEnd: '10:30',
        scheduleSessionType: 'offline',
        scheduleLocation: 'Ruang Konseling UC PSC',
        scheduleNotes: '',
        serviceCatalog: @json($serviceCatalogJson),

        openScheduleModal(form) {
            if (!form) return;
            this.schedulingForm = form;
            const b = form.booking;

            const counselingTypes = ['dewasa', 'anak', 'pra_nikah', 'pernikahan'];
            const psychotestTypes = ['industri', 'non_industri'];

            if (counselingTypes.includes(form.form_type)) {
                this.scheduleCategory = 'konseling';
                this.scheduleCategoryLocked = true;
                this.scheduleTier = b?.service_tier || 'pro';
                this.schedulePackageKey = b?.service_package_key || 'pro_wni_umum';
            } else if (psychotestTypes.includes(form.form_type)) {
                this.scheduleCategory = 'psikotes';
                this.scheduleCategoryLocked = true;
                this.scheduleTier = b?.service_tier || (form.form_type === 'industri' ? 'industri' : 'non_industri_bakat_minat');
                this.schedulePackageKey = b?.service_package_key || (form.form_type === 'industri' ? 'short_staf_umum' : 'short_umum');
            } else if (form.form_type === 'biography_en') {
                this.scheduleCategoryLocked = false;
                this.scheduleCategory = (b?.kategori && b.kategori !== 'pending') ? b.kategori : 'konseling';
                if (this.scheduleCategory === 'konseling') {
                    this.scheduleTier = b?.service_tier || 'pro';
                    this.schedulePackageKey = b?.service_package_key || 'pro_wna_umum';
                } else {
                    this.scheduleTier = b?.service_tier || 'industri';
                    this.schedulePackageKey = b?.service_package_key || 'short_staf_umum';
                }
            }

            if (b && b.tanggal_dijadwalkan) {
                this.scheduleDate = b.tanggal_dijadwalkan;
                this.scheduleStart = b.session_start || '09:00';
                this.scheduleEnd = b.session_end || '10:30';
                this.scheduleSessionType = (b.session_type === 'online') ? 'online' : 'offline';
                this.scheduleLocation = b.location || (this.scheduleSessionType === 'online' ? 'Online Video Conference' : 'Ruang Konseling UC PSC');
                this.scheduleCounselorId = b.counselor_id || '';
                this.scheduleStaffId = b.staff_penguji_id || '';
                this.scheduleNotes = b.notes || '';
            } else {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                this.scheduleDate = tomorrow.toISOString().split('T')[0];
                this.scheduleStart = '09:00';
                this.scheduleEnd = '10:30';
                this.scheduleSessionType = 'offline';
                this.scheduleLocation = (this.scheduleCategory === 'konseling') ? 'Ruang Konseling UC PSC' : 'Ruang Asesmen UC PSC';
                this.scheduleCounselorId = '';
                this.scheduleStaffId = '';
                this.scheduleNotes = '';
            }

            this.showScheduleModal = true;
        },

        closeScheduleModal() {
            this.showScheduleModal = false;
            this.schedulingForm = null;
        },

        onCategoryChange() {
            if (this.scheduleCategory === 'konseling') {
                this.scheduleTier = 'pro';
                this.schedulePackageKey = (this.schedulingForm?.form_type === 'biography_en') ? 'pro_wna_umum' : 'pro_wni_umum';
                this.scheduleLocation = 'Ruang Konseling UC PSC';
            } else {
                this.scheduleTier = 'industri';
                this.schedulePackageKey = 'short_staf_umum';
                this.scheduleLocation = 'Ruang Asesmen UC PSC';
            }
        },

        onTierChange() {
            if (this.scheduleCategory === 'konseling') {
                const isWna = (this.schedulingForm?.form_type === 'biography_en');
                this.schedulePackageKey = this.scheduleTier + (isWna ? '_wna_umum' : '_wni_umum');
            } else {
                if (this.scheduleTier === 'industri') {
                    this.schedulePackageKey = 'short_staf_umum';
                } else if (this.scheduleTier === 'non_industri_bakat_minat') {
                    this.schedulePackageKey = 'short_umum';
                } else if (this.scheduleTier === 'non_industri_khusus') {
                    this.schedulePackageKey = 'ucs_med_mmpi';
                }
            }
        },

        get calculatedPriceInfo() {
            const cat = this.serviceCatalog;
            if (!cat) return { price_formatted: '-', overtime_formatted: '-', label: '-' };

            if (this.scheduleCategory === 'konseling') {
                const parts = (this.schedulePackageKey || '').split('_');
                const tier = this.scheduleTier;
                const klien = parts[1] || 'wni';
                let pkgType = parts[2] || 'umum';
                if (parts[3]) pkgType += '_' + parts[3];

                const info = cat.konseling?.tiers?.[tier]?.[klien]?.[pkgType];
                if (info) {
                    return {
                        price_formatted: 'Rp ' + Number(info.price).toLocaleString('id-ID'),
                        overtime_formatted: 'Rp ' + Number(info.overtime).toLocaleString('id-ID'),
                        label: info.label
                    };
                }
            } else {
                const tier = this.scheduleTier;
                const pkg = cat.psikotes?.[tier]?.packages?.[this.schedulePackageKey];
                if (pkg) {
                    return {
                        price_formatted: 'Rp ' + Number(pkg.price).toLocaleString('id-ID'),
                        overtime_formatted: '-',
                        label: pkg.label
                    };
                }
            }
            return { price_formatted: '-', overtime_formatted: '-', label: '-' };
        },

        // Rekam Jejak Formulir Masuk Sorting (Client-side interactive)
        counselingFormsData: @json($counselingFormsJson),
        counselingSortCol: null,
        counselingSortDir: 'asc',

        psychotestFormsData: @json($psychotestFormsJson),
        psychotestSortCol: null,
        psychotestSortDir: 'asc',

        toggleCounselingSort(column) {
            if (this.counselingSortCol !== column) {
                this.counselingSortCol = column;
                this.counselingSortDir = 'asc';
            } else if (this.counselingSortDir === 'asc') {
                this.counselingSortDir = 'desc';
            } else {
                this.counselingSortCol = null;
                this.counselingSortDir = 'asc';
            }
        },

        get sortedCounselingForms() {
            if (!this.counselingSortCol) {
                return this.counselingFormsData;
            }
            return [...this.counselingFormsData].sort((a, b) => {
                let col = this.counselingSortCol;
                let valA, valB;
                if (col === 'created_at') {
                    valA = (a.created_at_raw || a.created_at || '');
                    valB = (b.created_at_raw || b.created_at || '');
                } else if (col === 'ticket') {
                    valA = (a.ticket || a.ticket_number || a.id || '').toString().toLowerCase();
                    valB = (b.ticket || b.ticket_number || b.id || '').toString().toLowerCase();
                } else if (col === 'form_type') {
                    valA = (a.form_type_label || a.form_type || '').toString().toLowerCase();
                    valB = (b.form_type_label || b.form_type || '').toString().toLowerCase();
                } else {
                    valA = (a[col] || '').toString().toLowerCase();
                    valB = (b[col] || '').toString().toLowerCase();
                }
                if (valA < valB) return this.counselingSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return this.counselingSortDir === 'asc' ? 1 : -1;
                return 0;
            });
        },

        togglePsychotestSort(column) {
            if (this.psychotestSortCol !== column) {
                this.psychotestSortCol = column;
                this.psychotestSortDir = 'asc';
            } else if (this.psychotestSortDir === 'asc') {
                this.psychotestSortDir = 'desc';
            } else {
                this.psychotestSortCol = null;
                this.psychotestSortDir = 'asc';
            }
        },

        get sortedPsychotestForms() {
            if (!this.psychotestSortCol) {
                return this.psychotestFormsData;
            }
            return [...this.psychotestFormsData].sort((a, b) => {
                let col = this.psychotestSortCol;
                let valA, valB;
                if (col === 'created_at') {
                    valA = (a.created_at_raw || a.created_at || '');
                    valB = (b.created_at_raw || b.created_at || '');
                } else if (col === 'ticket') {
                    valA = (a.ticket || a.ticket_number || a.id || '').toString().toLowerCase();
                    valB = (b.ticket || b.ticket_number || b.id || '').toString().toLowerCase();
                } else if (col === 'form_type') {
                    valA = (a.form_type_label || a.form_type || '').toString().toLowerCase();
                    valB = (b.form_type_label || b.form_type || '').toString().toLowerCase();
                } else {
                    valA = (a[col] || '').toString().toLowerCase();
                    valB = (b[col] || '').toString().toLowerCase();
                }
                if (valA < valB) return this.psychotestSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return this.psychotestSortDir === 'asc' ? 1 : -1;
                return 0;
            });
        },

        // Rekam Jejak Booking Layanan Sorting (Client-side interactive)
        counselingBookingsData: @json($counselingBookingsJson),
        counselingBookingSortCol: null,
        counselingBookingSortDir: 'asc',

        psychotestBookingsData: @json($psychotestBookingsJson),
        psychotestBookingSortCol: null,
        psychotestBookingSortDir: 'asc',

        toggleCounselingBookingSort(column) {
            if (this.counselingBookingSortCol !== column) {
                this.counselingBookingSortCol = column;
                this.counselingBookingSortDir = 'asc';
            } else if (this.counselingBookingSortDir === 'asc') {
                this.counselingBookingSortDir = 'desc';
            } else {
                this.counselingBookingSortCol = null;
                this.counselingBookingSortDir = 'asc';
            }
        },

        get sortedCounselingBookings() {
            if (!this.counselingBookingSortCol) {
                return this.counselingBookingsData;
            }
            return [...this.counselingBookingsData].sort((a, b) => {
                let col = this.counselingBookingSortCol;
                let valA, valB;
                if (col === 'schedule') {
                    valA = a.tanggal_dijadwalkan_raw || '';
                    valB = b.tanggal_dijadwalkan_raw || '';
                } else if (col === 'id') {
                    valA = Number(a.id) || 0;
                    valB = Number(b.id) || 0;
                } else {
                    valA = (a[col] || '').toString().toLowerCase();
                    valB = (b[col] || '').toString().toLowerCase();
                }
                if (valA < valB) return this.counselingBookingSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return this.counselingBookingSortDir === 'asc' ? 1 : -1;
                return 0;
            });
        },

        togglePsychotestBookingSort(column) {
            if (this.psychotestBookingSortCol !== column) {
                this.psychotestBookingSortCol = column;
                this.psychotestBookingSortDir = 'asc';
            } else if (this.psychotestBookingSortDir === 'asc') {
                this.psychotestBookingSortDir = 'desc';
            } else {
                this.psychotestBookingSortCol = null;
                this.psychotestBookingSortDir = 'asc';
            }
        },

        get sortedPsychotestBookings() {
            if (!this.psychotestBookingSortCol) {
                return this.psychotestBookingsData;
            }
            return [...this.psychotestBookingsData].sort((a, b) => {
                let col = this.psychotestBookingSortCol;
                let valA, valB;
                if (col === 'schedule') {
                    valA = a.tanggal_dijadwalkan_raw || '';
                    valB = b.tanggal_dijadwalkan_raw || '';
                } else if (col === 'id') {
                    valA = Number(a.id) || 0;
                    valB = Number(b.id) || 0;
                } else {
                    valA = (a[col] || '').toString().toLowerCase();
                    valB = (b[col] || '').toString().toLowerCase();
                }
                if (valA < valB) return this.psychotestBookingSortDir === 'asc' ? -1 : 1;
                if (valA > valB) return this.psychotestBookingSortDir === 'asc' ? 1 : -1;
                return 0;
            });
        },

        formatDateTimeRaw(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr.replace(' ', 'T'));
            if (isNaN(d.getTime())) return dateStr;
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const seconds = String(d.getSeconds()).padStart(2, '0');
            return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
        },
                // State Edit Form Dossier Modal
        editingFormCard: null,
        editFormAnswers: {},
        isSavingFormCard: false,

        startEditFormCard(cardKey) {
            this.editingFormCard = cardKey;
            this.editFormAnswers = JSON.parse(JSON.stringify(this.selectedForm?.answers || {}));
            
            // Format tanggal agar sesuai <input type="date"> (YYYY-MM-DD)
            ['tanggal_lahir', 'tanggal_lahir_pasangan', 'anak_tanggal_lahir', 'ayah_tanggal_lahir', 'ibu_tanggal_lahir', 'tanggal_rencana_pernikahan'].forEach(key => {
                if (this.editFormAnswers[key] && typeof this.editFormAnswers[key] === 'string' && this.editFormAnswers[key].includes('/')) {
                    const parts = this.editFormAnswers[key].split('/');
                    if (parts.length === 3) {
                        this.editFormAnswers[key] = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                    }
                }
            });

            // Sinkronkan dari biodata klien aktif jika field di formulir masih kosong
            if (!this.editFormAnswers.nama_lengkap && this.formData.name) {
                this.editFormAnswers.nama_lengkap = this.formData.name;
            }
            if (!this.editFormAnswers.tempat_lahir && this.formData.birth_place) {
                this.editFormAnswers.tempat_lahir = this.formData.birth_place;
            }
            if (!this.editFormAnswers.tanggal_lahir && this.formData.dob) {
                this.editFormAnswers.tanggal_lahir = this.formData.dob;
            }
            if (!this.editFormAnswers.jenis_kelamin && this.formData.gender) {
                this.editFormAnswers.jenis_kelamin = (this.formData.gender === 'l' || this.formData.gender === 'male' || this.formData.gender === 'pria' || this.formData.gender === 'laki-laki') ? 'Laki-Laki' : 'Perempuan';
            }
            if (!this.editFormAnswers.phone && !this.editFormAnswers.no_telepon && this.formData.phone) {
                this.editFormAnswers.phone = this.formData.phone;
                this.editFormAnswers.no_telepon = this.formData.phone;
            }
            if (!this.editFormAnswers.email && this.formData.email) {
                this.editFormAnswers.email = this.formData.email;
            }
            if (!this.editFormAnswers.agama && this.formData.religion) {
                this.editFormAnswers.agama = this.formData.religion;
            }
            if (!this.editFormAnswers.pendidikan_terakhir && this.formData.education) {
                this.editFormAnswers.pendidikan_terakhir = this.formData.education;
            }
            if (!this.editFormAnswers.pekerjaan && !this.editFormAnswers.pekerjaan_saat_ini && this.formData.occupation) {
                this.editFormAnswers.pekerjaan = this.formData.occupation;
                this.editFormAnswers.pekerjaan_saat_ini = this.formData.occupation;
            }
            if (!this.editFormAnswers.alamat && this.formData.address) {
                this.editFormAnswers.alamat = this.formData.address;
            }
        },

        cancelEditFormCard() {
            this.editingFormCard = null;
            this.editFormAnswers = {};
        },

        async saveFormCard(cardKey) {
            if (!this.selectedForm || this.isSavingFormCard) return;
            this.isSavingFormCard = true;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                const response = await fetch(`/client-forms/${this.selectedForm.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        answers: this.editFormAnswers
                    })
                });

                const res = await response.json();
                if (res.success) {
                    this.selectedForm = res.data;
                    
                    // 1. Sinkronkan ke master data Client di halaman detail (tab Biodata Klien)
                    if (res.client) {
                        this.formData.name = res.client.name ?? this.formData.name;
                        this.formData.gender = (res.client.gender === 'l' || res.client.gender === 'male' || res.client.gender === 'laki-laki' || res.client.gender === 'pria') ? 'l' : (res.client.gender ? 'p' : this.formData.gender);
                        this.formData.birth_place = res.client.birth_place ?? this.formData.birth_place;
                        this.formData.dob = res.client.dob ? (typeof res.client.dob === 'string' ? res.client.dob.substring(0, 10) : res.client.dob) : this.formData.dob;
                        this.formData.phone = res.client.phone ?? this.formData.phone;
                        this.formData.email = res.client.email ?? this.formData.email;
                        this.formData.education = res.client.education ?? this.formData.education;
                        this.formData.religion = res.client.religion ?? this.formData.religion;
                        this.formData.occupation = res.client.occupation ?? this.formData.occupation;
                        this.formData.address = res.client.address ?? this.formData.address;
                        this.initialFormData = JSON.parse(JSON.stringify(this.formData));

                        // 2. Sinkronkan ke daftar formulir klien lain di memory (konseling & psikotes)
                        if (Array.isArray(res.client.client_forms)) {
                            res.client.client_forms.forEach(cf => {
                                if (Array.isArray(this.counselingForms)) {
                                    const idx = this.counselingForms.findIndex(f => f.id === cf.id);
                                    if (idx !== -1) this.counselingForms[idx] = cf;
                                }
                                if (Array.isArray(this.psychotestForms)) {
                                    const idx = this.psychotestForms.findIndex(f => f.id === cf.id);
                                    if (idx !== -1) this.psychotestForms[idx] = cf;
                                }
                            });
                        }
                    }

                    // Perbarui formulir aktif di daftar lokal
                    if (Array.isArray(this.counselingForms)) {
                        const idx = this.counselingForms.findIndex(f => f.id === res.data.id);
                        if (idx !== -1) this.counselingForms[idx] = res.data;
                    }
                    if (Array.isArray(this.psychotestForms)) {
                        const idx = this.psychotestForms.findIndex(f => f.id === res.data.id);
                        if (idx !== -1) this.psychotestForms[idx] = res.data;
                    }

                    this.editingFormCard = null;
                    this.editFormAnswers = {};
                    if (window.showToast) {
                        window.showToast('Data formulir berhasil disimpan dan disinkronkan.', 'success');
                    }
                } else {
                    alert(res.message || 'Gagal menyimpan perubahan formulir.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan perubahan.');
            } finally {
                this.isSavingFormCard = false;
            }
        },

        openDossier(form) {
            this.editingFormCard = null;
            this.editFormAnswers = {};

            this.selectedForm = form;
            this.showDossierModal = true;
        },
        getFormTitle(form) {
            if (!form) return 'Detail Formulir';
            if (form.form_type_label) return form.form_type_label;
            const map = {
                'anak': 'Konseling Anak',
                'dewasa': 'Konseling Dewasa',
                'pra_nikah': 'Konseling Pra-Nikah',
                'pernikahan': 'Konseling Pernikahan',
                'industri': 'Layanan Industri',
                'non_industri': 'Layanan Non-Industri',
                'biography_en': 'Biography Form (English)'
            };
            return map[form.form_type] || (form.form_type ? form.form_type.charAt(0).toUpperCase() + form.form_type.slice(1).replace(/_/g, ' ') : 'Detail Formulir');
        },
        formatBirthPlaceDate(place, date, fallback) {
            let p = (place || '').trim();
            let d = (date || '').trim();

            if (fallback && !p && !d) {
                let fb = (fallback || '').trim();
                if (!fb) return '-';
                if (fb.includes(',')) {
                    let parts = fb.split(',');
                    p = parts[0].trim();
                    d = parts.slice(1).join(',').trim();
                } else {
                    return fb;
                }
            }

            let formattedDate = '';
            if (d) {
                let dateObj = new Date(d.replace(' ', 'T'));
                if (!isNaN(dateObj.getTime())) {
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    formattedDate = `${dateObj.getDate()} ${months[dateObj.getMonth()]} ${dateObj.getFullYear()}`;
                } else {
                    formattedDate = d;
                }
            }

            if (p && formattedDate) {
                return `${p}, ${formattedDate}`;
            } else if (p) {
                return p;
            } else if (formattedDate) {
                return formattedDate;
            }
            return '-';
        },
        formatFormSubmissionDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr.replace(' ', 'T'));
            if (isNaN(d.getTime())) return dateStr;
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const day = String(d.getDate()).padStart(2, '0');
            const month = months[d.getMonth()];
            const year = d.getFullYear();
            return `${day} ${month} ${year}`;
        },
        formatFormSubmissionTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr.replace(' ', 'T'));
            if (isNaN(d.getTime())) return '';
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const seconds = String(d.getSeconds()).padStart(2, '0');
            return `${hours}:${minutes}:${seconds} WIB`;
        },
        printPdf(formOrId) {
            const formId = typeof formOrId === 'object' && formOrId !== null ? formOrId.id : (formOrId || (this.selectedForm ? this.selectedForm.id : null));
            if (!formId) return;
            this.isPrintingPdf = true;
            const formObj = typeof formOrId === 'object' && formOrId !== null ? formOrId : this.selectedForm;
            const targetFilename = (formObj && formObj.pdf_filename) ? formObj.pdf_filename : null;
            const existingFrame = document.getElementById('pdf-print-iframe');
            if (existingFrame) {
                existingFrame.remove();
            }
            const frame = document.createElement('iframe');
            frame.id = 'pdf-print-iframe';
            frame.style.position = 'fixed';
            frame.style.right = '0';
            frame.style.bottom = '0';
            frame.style.width = '0';
            frame.style.height = '0';
            frame.style.border = '0';
            frame.style.visibility = 'hidden';
            document.body.appendChild(frame);
            frame.onload = () => {
                setTimeout(() => {
                    const originalTitle = document.title;
                    try {
                        const finalTitle = (frame.contentDocument && frame.contentDocument.title) 
                            ? frame.contentDocument.title 
                            : (targetFilename || originalTitle);
                        document.title = finalTitle;
                        if (frame.contentDocument) {
                            frame.contentDocument.title = finalTitle;
                        }
                        frame.contentWindow.focus();
                        frame.contentWindow.print();
                        const restoreTitle = () => {
                            document.title = originalTitle;
                            window.removeEventListener('afterprint', restoreTitle);
                            if (frame.contentWindow) {
                                frame.contentWindow.removeEventListener('afterprint', restoreTitle);
                            }
                        };
                        window.addEventListener('afterprint', restoreTitle);
                        if (frame.contentWindow) {
                            frame.contentWindow.addEventListener('afterprint', restoreTitle);
                        }
                        setTimeout(restoreTitle, 3500);
                    } catch (err) {
                        console.error('Gagal mencetak dokumen:', err);
                        document.title = originalTitle;
                    } finally {
                        this.isPrintingPdf = false;
                    }
                }, 350);
            };
            frame.src = `/client-forms/${formId}/pdf`;
        },
        init() {
            window.clientProfileDetailComponent = this;
            const validTabs = ['profile', 'notes', 'konseling', 'psychotest', 'history', 'settings'];
            const urlParams = new URLSearchParams(window.location.search);
            const urlTab = urlParams.get('tab');
            try {
                Object.keys(sessionStorage).forEach(key => {
                    if (key.startsWith('client_active_tab_')) {
                        sessionStorage.removeItem(key);
                    }
                });
            } catch (e) {}
            if (urlTab && validTabs.includes(urlTab)) {
                this.activeTab = urlTab;
            } else {
                this.activeTab = 'profile';
            }
            this.$watch('activeTab', (tab) => {
                if (validTabs.includes(tab)) {
                    const url = new URL(window.location.href);
                    if (tab === 'profile') {
                        url.searchParams.delete('tab');
                    } else {
                        url.searchParams.set('tab', tab);
                    }
                    window.history.replaceState({}, '', url);
                }
            });
        }
    };
}
</script>

<div id="client-profile-detail-root" x-data="clientProfileDetail()" class="space-y-4">

    {{-- ========================================================================= --}}
    
    {{-- ========================================================================= --}}
    {{-- CARD 1: PROFILE HEADER BANNER (ELEGANT PURPLE CARD DENGAN KONTRAS HARMONIS) --}}
    {{-- ========================================================================= --}}
    <div class="bg-purple-deep rounded-2xl border border-purple-900 shadow-xs overflow-hidden text-white relative">
        {{-- Background Soft Accent Tint --}}
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="px-5 py-4 sm:px-6 sm:py-4.5 flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-5 relative z-10">
            {{-- Sisi Kiri: Avatar Inisial Klien & Informasi Identitas Utama --}}
            <div class="flex items-center gap-4 sm:gap-5">
                {{-- Profile Picture Inisial Klien --}}
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-b from-[#4A247A] to-[#2D124D] border border-[#D9C2F0]/40 ring-2 ring-black/10 flex items-center justify-center shadow-sm text-white font-bold text-lg sm:text-xl tracking-wider select-none shrink-0">
                    {{ $clientInitials }}
                </div>

                {{-- Detail Nama & ID Klien Saja --}}
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight leading-tight">
                        {{ $client->name ?? '-' }}
                    </h2>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-lg bg-white/15 text-white border border-white/25 backdrop-blur-xs shadow-2xs">
                        #CLN-{{ str_pad($client->id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
            </div>

            {{-- Sisi Kanan: Action Buttons Glossy Glass Transparan --}}
            <div class="flex items-center gap-2.5 shrink-0 self-start md:self-center">
                @if(!empty($waPhone))
                <a href="https://wa.me/{{ $waPhone }}" target="_blank"
                   class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white backdrop-blur-md transition-all flex items-center justify-center shadow-xs cursor-pointer group"
                   title="Hubungi Klien via WhatsApp">
                    <svg class="w-4 h-4 fill-current text-white group-hover:scale-110 transition-transform" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                    </svg>
                </a>
                @endif
                {{-- Tombol Edit Profil: Ikon Putih, Tanpa Teks, Glossy Glass Transparan --}}
                <button type="button" @click="handleToggleEdit()"
                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl transition-all flex items-center justify-center shadow-xs cursor-pointer group"
                        :class="editMode ? 'bg-white text-purple-deep shadow-sm' : 'bg-white/15 hover:bg-white/25 border border-white/25 text-white backdrop-blur-md'"
                        :title="editMode ? 'Tutup Mode Edit' : 'Edit Biodata Klien'">
                    <template x-if="!editMode">
                        <svg class="w-4 h-4 text-white group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </template>
                    <template x-if="editMode">
                        <svg class="w-4 h-4 text-purple-deep group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </template>
                </button>
            </div>
        </div>
    </div>


    {{-- ========================================================================= --}}
    {{-- CARD 2: TABS WORKSPACE CONTAINER (BIODATA, CATATAN, KONSELING, PSIKOTES, RIWAYAT, PENGATURAN) --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
        {{-- Unified Tabs Navigation Header (Pertinggi & Font Lebih Tebal & Jelas) --}}
        <div class="px-4 sm:px-6 bg-purple-deep border-b border-purple-900/30 flex items-center gap-4 sm:gap-6 overflow-x-auto overflow-y-hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden select-none">
            {{-- Tab 1: Biodata Klien --}}
            <button type="button" @click="handleTabClick('profile')"
                    class="py-3 sm:py-3.5 text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer border-b-2 whitespace-nowrap"
                    :class="activeTab === 'profile'
                        ? 'border-white text-white font-bold'
                        : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-semibold'">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Biodata Klien</span>
            </button>

            {{-- Tab 2: Catatan Klien --}}
            <button type="button" @click="handleTabClick('notes')"
                    class="py-3 sm:py-3.5 text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer border-b-2 whitespace-nowrap"
                    :class="activeTab === 'notes'
                        ? 'border-white text-white font-bold'
                        : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-semibold'">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>Catatan Klien</span>
            </button>

            {{-- Tab 3: Konseling --}}
            <button type="button" @click="handleTabClick('konseling')"
                    class="py-3 sm:py-3.5 text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer border-b-2 whitespace-nowrap"
                    :class="activeTab === 'konseling'
                        ? 'border-white text-white font-bold'
                        : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-semibold'">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>Konseling</span>
            </button>

            {{-- Tab 4: Psikotes --}}
            <button type="button" @click="handleTabClick('psychotest')"
                    class="py-3 sm:py-3.5 text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer border-b-2 whitespace-nowrap"
                    :class="activeTab === 'psychotest'
                        ? 'border-white text-white font-bold'
                        : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-semibold'">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"></path>
                    <rect x="9" y="3" width="6" height="4" rx="1"></rect>
                    <path d="m9 14 2 2 4-4"></path>
                </svg>
                <span>Psikotes</span>
            </button>

            {{-- Tab 5: Riwayat Layanan (Fitur Baru) --}}
            <button type="button" @click="handleTabClick('history')"
                    class="py-3 sm:py-3.5 text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer border-b-2 whitespace-nowrap"
                    :class="activeTab === 'history'
                        ? 'border-white text-white font-bold'
                        : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-semibold'">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                    <path d="M12 7v5l4 2"></path>
                </svg>
                <span>Riwayat Layanan</span>
            </button>

            {{-- Tab 6: Pengaturan --}}
            <button type="button" @click="handleTabClick('settings')"
                    class="py-3 sm:py-3.5 text-xs sm:text-sm transition-all flex items-center gap-2 cursor-pointer border-b-2 whitespace-nowrap"
                    :class="activeTab === 'settings'
                        ? 'border-white text-white font-bold'
                        : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-semibold'">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span>Pengaturan</span>
            </button>
        </div>

        {{-- Content Container --}}
        <div class="p-4 sm:p-5 lg:p-6 bg-white">


            {{-- ========================================================================= --}}
            {{-- TAB 1: BIODATA KLIEN (FULL WIDTH CONTAINER)                               --}}
            {{-- ========================================================================= --}}
            <div x-show="activeTab === 'profile'" x-cloak class="space-y-6">
                @include('clients.partials.address-script')

                {{-- ---- VIEW MODE BIODATA ---- --}}
                <div x-show="!editMode" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- 1. Nama Lengkap --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                {{ $client->name ?? '-' }}
                            </div>
                        </div>

                        {{-- 2. Nama Perusahaan --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Nama Perusahaan</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                {{ $client->company_name ?? ($client->pic_name ?? '-') }}
                            </div>
                        </div>

                        {{-- 3. Pekerjaan / Jabatan Saat Ini --}}
                        @php
                            $jobTitle = $client->occupation;
                            if ($jobTitle && str_contains($jobTitle, ' - ')) {
                                $parts = explode(' - ', $jobTitle);
                                $jobTitle = trim($parts[0]);
                            }
                        @endphp
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan / Jabatan Saat Ini</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                {{ $jobTitle ?: '-' }}
                            </div>
                        </div>

                        {{-- 4. Jenis Kelamin --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                {{ $client->gender_label ?: '-' }}
                            </div>
                        </div>

                        {{-- 5 & 6. Tempat, Tanggal Lahir (Bersebelahan dalam satu kolom selaras dengan Edit Mode) --}}
                        <div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 mb-1">Tempat Lahir</label>
                                    <div class="px-3 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                        {{ $client->birth_place ?? '-' }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 mb-1">Tanggal Lahir</label>
                                    <div class="px-2.5 sm:px-3 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                        {{ $client->dob ? $client->dob->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 7. No. Telepon / WhatsApp --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon / WhatsApp</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                {{ $client->phone ?? '-' }}
                            </div>
                        </div>

                        {{-- 8. Alamat Email --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black break-all">
                                {{ $client->email ?? '-' }}
                            </div>
                        </div>

                        {{-- 9. Pendidikan Terakhir --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                {{ $client->education_label ?? $client->education ?? '-' }}
                            </div>
                        </div>

                        {{-- 10. Agama / Kepercayaan --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Agama / Kepercayaan</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                {{ $client->religion ?? '-' }}
                            </div>
                        </div>

                        {{-- 11. Alamat Domisili Lengkap --}}
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Alamat</label>
                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black leading-relaxed">
                                {{ $client->address ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ---- EDIT MODE BIODATA ---- --}}
                <div x-show="editMode" x-cloak class="space-y-4">
                    <form id="client-edit-biodata-form" data-no-unsaved-guard @submit="window.isSubmittingForm = true" action="{{ route('clients.update', $client) }}" method="POST"
                          class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            {{-- 1. Nama Lengkap (Wajib) --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="formData.name" required placeholder="Nama lengkap klien..."
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                            </div>

                            {{-- 2. Nama Perusahaan --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Nama Perusahaan</label>
                                <input type="text" name="company_name" x-model="formData.company_name" placeholder="Nama perusahaan atau instansi..."
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                            </div>

                            {{-- 3. Pekerjaan / Jabatan Saat Ini --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan / Jabatan Saat Ini</label>
                                <input type="text" name="occupation" x-model="formData.occupation" placeholder="Jabatan atau profesi..."
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                            </div>

                            {{-- 4. Jenis Kelamin --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin</label>
                                <select name="gender" x-model="formData.gender"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    <option value="">Pilih Jenis Kelamin...</option>
                                    <option value="l">Laki-laki</option>
                                    <option value="p">Perempuan</option>
                                </select>
                            </div>

                            {{-- 5 & 6. Tempat, Tanggal Lahir (Bersebelahan dalam satu kolom selaras dengan View Mode) --}}
                            <div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat Lahir</label>
                                        <input type="text" name="birth_place" x-model="formData.birth_place" placeholder="Kota kelahiran..."
                                               class="w-full px-3 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tanggal Lahir</label>
                                        <input type="date" name="dob" x-model="formData.dob"
                                               class="w-full px-2.5 sm:px-3 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    </div>
                                </div>
                            </div>

                            {{-- 7. No. Telepon / WhatsApp --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon / WhatsApp</label>
                                <input type="text" name="phone" x-model="formData.phone" placeholder="Contoh: 08123456789"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                            </div>

                            {{-- 8. Alamat Email --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email</label>
                                <input type="email" name="email" x-model="formData.email" placeholder="Contoh: klien@email.com"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                            </div>

                            {{-- 9. Pendidikan Terakhir --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir</label>
                                <select name="education" x-model="formData.education"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    <option value="">Pilih Pendidikan...</option>
                                    <option value="SD" {{ $client->education === 'SD' ? 'selected' : '' }}>SD</option>
                                    <option value="SMP" {{ $client->education === 'SMP' ? 'selected' : '' }}>SMP</option>
                                    <option value="SMA" {{ in_array($client->education, ['SMA', 'SMK']) ? 'selected' : '' }}>SMA / SMK</option>
                                    <option value="D3" {{ $client->education === 'D3' ? 'selected' : '' }}>Diploma (D3)</option>
                                    <option value="S1" {{ $client->education === 'S1' ? 'selected' : '' }}>Sarjana (S1)</option>
                                    <option value="S2" {{ $client->education === 'S2' ? 'selected' : '' }}>Magister (S2)</option>
                                    <option value="S3" {{ $client->education === 'S3' ? 'selected' : '' }}>Doktor (S3)</option>
                                    <option value="Lainnya" {{ !in_array($client->education, ['SD', 'SMP', 'SMA', 'SMK', 'D3', 'S1', 'S2', 'S3']) && !empty($client->education) ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>

                            {{-- 10. Agama / Kepercayaan --}}
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Agama / Kepercayaan</label>
                                <select name="religion" x-model="formData.religion"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    <option value="">Pilih Agama...</option>
                                    <option value="Islam" {{ strcasecmp($client->religion ?? '', 'Islam') === 0 ? 'selected' : '' }}>Islam</option>
                                    <option value="Kristen" {{ strcasecmp($client->religion ?? '', 'Kristen') === 0 || strcasecmp($client->religion ?? '', 'Kristen Protestan') === 0 ? 'selected' : '' }}>Kristen</option>
                                    <option value="Katolik" {{ strcasecmp($client->religion ?? '', 'Katolik') === 0 ? 'selected' : '' }}>Katolik</option>
                                    <option value="Hindu" {{ strcasecmp($client->religion ?? '', 'Hindu') === 0 ? 'selected' : '' }}>Hindu</option>
                                    <option value="Buddha" {{ strcasecmp($client->religion ?? '', 'Buddha') === 0 ? 'selected' : '' }}>Buddha</option>
                                    <option value="Khonghucu" {{ in_array(strtolower($client->religion ?? ''), ['khonghucu', 'konghucu']) ? 'selected' : '' }}>Khonghucu</option>
                                    <option value="Lainnya" {{ strcasecmp($client->religion ?? '', 'Lainnya') === 0 ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>

                            {{-- 11. Alamat Lengkap --}}
                            <div class="sm:col-span-2 lg:col-span-3">
                                <label class="block text-xs font-medium text-slate-500 mb-1">Alamat</label>
                                <textarea name="address" x-model="formData.address" rows="2" placeholder="Alamat domisili saat ini..."
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white"></textarea>
                            </div>
                        </div>

                        {{-- Form Action Buttons Sesuai Aturan Button Design System --}}
                        <div class="pt-2 flex items-center justify-end gap-2.5">
                            <button type="button" @click="handleCancelEdit()"
                                    class="px-5 py-2.5 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs sm:text-sm transition cursor-pointer shadow-xs">
                                Batal
                            </button>
                            <button type="submit" @click="window.isSubmittingForm = true"
                                    class="px-5 py-2.5 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs sm:text-sm transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                <span>Simpan Biodata</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

<div x-show="activeTab === 'notes'" x-cloak class="space-y-6" x-data="{ 
        showAddNoteModal: false,
        showAddUnsavedModal: false,
        addNoteText: '',
        showEditNoteModal: false,
        showEditUnsavedModal: false,
        editNoteId: '',
        editNoteDate: '',
        editNoteText: '',
        initialEditNoteText: '',
        editActionUrl: '',
        noteSortCol: null,
        noteSortDir: 'desc',
        rawNotes: {{ json_encode($client->notes_list) }},
        toggleNoteSort(column) {
            if (this.noteSortCol !== column) {
                this.noteSortCol = column;
                this.noteSortDir = 'desc';
            } else if (this.noteSortDir === 'desc') {
                this.noteSortDir = 'asc';
            } else {
                this.noteSortCol = null;
                this.noteSortDir = 'desc';
            }
        },
        get sortedNotes() {
            const list = [...(this.rawNotes || [])];
            if (!this.noteSortCol) {
                return list;
            }
            return list.sort((a, b) => {
                if (this.noteSortCol === 'date') {
                    const timeA = new Date(a.date.replace(' ', 'T')).getTime() || 0;
                    const timeB = new Date(b.date.replace(' ', 'T')).getTime() || 0;
                    return this.noteSortDir === 'asc' ? timeA - timeB : timeB - timeA;
                }
                if (this.noteSortCol === 'note') {
                    const noteA = (a.note || '').toLowerCase();
                    const noteB = (b.note || '').toLowerCase();
                    if (noteA < noteB) return this.noteSortDir === 'asc' ? -1 : 1;
                    if (noteA > noteB) return this.noteSortDir === 'asc' ? 1 : -1;
                    return 0;
                }
                return 0;
            });
        },
        formatDateTimeDisplay(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr.replace(' ', 'T'));
            if (isNaN(d.getTime())) return dateStr;
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            const h = String(d.getHours()).padStart(2, '0');
            const m = String(d.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} ${h}:${m}`;
        },
        formatDateDisplay(dateStr) {
            return this.formatDateTimeDisplay(dateStr);
        },
        formatTimeDisplay(dateStr) {
            return '';
        },
        openAddModal() {
            this.addNoteText = '';
            this.showAddNoteModal = true;
            this.showAddUnsavedModal = false;
        },
        isAddDirty() {
            return (this.addNoteText || '').trim() !== '';
        },
        handleCloseAddModal() {
            if (this.isAddDirty()) {
                this.showAddUnsavedModal = true;
            } else {
                this.forceCloseAddModal();
            }
        },
        forceCloseAddModal() {
            this.showAddUnsavedModal = false;
            this.showAddNoteModal = false;
            this.addNoteText = '';
        },
        openEditModal(item) {
            this.editNoteId = item.id;
            this.editNoteDate = item.date_input;
            this.editNoteText = item.note;
            this.initialEditNoteText = item.note;
            this.editActionUrl = `/clients/{{ $client->id }}/notes/${item.id}`;
            this.showEditNoteModal = true;
            this.showEditUnsavedModal = false;
        },
        isEditDirty() {
            return (this.editNoteText || '').trim() !== (this.initialEditNoteText || '').trim();
        },
        handleCloseEditModal() {
            if (this.isEditDirty()) {
                this.showEditUnsavedModal = true;
            } else {
                this.forceCloseEditModal();
            }
        },
        forceCloseEditModal() {
            this.showEditUnsavedModal = false;
            this.showEditNoteModal = false;
            this.editNoteText = '';
            this.initialEditNoteText = '';
        }
    }">

        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white text-base">Catatan Klien</h3>
                </div>
                <button type="button" @click="openAddModal()"
                        class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Catatan</span>
                </button>
            </div>

            {{-- TABEL PENYAJIAN CATATAN (Dibatasi 6 baris data teratas, selebihnya scroll vertikal) --}}

            @if(!empty($client->notes_list) && count($client->notes_list) > 0)
            <div class="overflow-y-auto max-h-[460px] custom-scrollbar">
                <table class="w-full table-fixed text-left text-xs font-medium">
                    <thead class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA] text-xs select-none sticky top-0 z-10 shadow-xs">
                        <tr>
                            <th class="py-3 px-4 font-semibold text-black w-48 shrink-0 whitespace-nowrap bg-slate-50">
                                <button type="button" 
                                        @click="toggleNoteSort('date')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>Tanggal & Waktu</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="noteSortCol === 'date' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="noteSortCol === 'date' && noteSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="noteSortCol === 'date' && noteSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="noteSortCol !== 'date'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>
                            <th class="py-3 px-4 font-semibold text-black bg-slate-50">
                                <button type="button" 
                                        @click="toggleNoteSort('note')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>Isi Catatan</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="noteSortCol === 'note' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="noteSortCol === 'note' && noteSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="noteSortCol === 'note' && noteSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="noteSortCol !== 'note'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>
                            <th class="py-3.5 px-6 text-center font-semibold text-black w-24 shrink-0 whitespace-nowrap bg-slate-50">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-xs">
                        <template x-for="(item, idx) in sortedNotes" :key="item.id">
                            <tr class="transition-colors duration-150 group">
                                {{-- Kolom Kiri: Tanggal & Waktu (Seragam text-xs font-medium text-black) --}}
                                <td class="py-3.5 px-6 text-black align-top whitespace-nowrap text-xs font-medium w-48 shrink-0" x-text="formatDateTimeDisplay(item.date)"></td>

                                {{-- Kolom Kanan: Catatan (Seragam text-xs font-medium text-black) --}}
                                <td class="py-3.5 px-6 text-black align-top text-xs leading-relaxed whitespace-pre-line font-medium break-words" x-text="item.note"></td>

                                {{-- Kolom Aksi --}}
                                <td class="py-3.5 px-6 text-center align-top whitespace-nowrap w-24 shrink-0">
                                    <div class="flex items-center justify-center gap-1">
                                        {{-- Tombol Edit --}}
                                        <button type="button" 
                                                @click="openEditModal(item)"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-purple-deep hover:bg-purple-50 transition cursor-pointer"
                                                title="Edit Catatan">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <form :action="`/clients/{{ $client->id }}/notes/${item.id}`" method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                                    title="Hapus Catatan">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            @else
            <div class="py-12 text-center text-slate-400 space-y-3">
                <svg class="w-12 h-12 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <p class="text-sm font-medium text-black">Belum ada catatan hasil inputan formulir untuk klien ini.</p>
                <button type="button" @click="openAddModal()"
                        class="px-4 py-2 rounded-xl bg-purple-deep text-white text-xs font-semibold hover:opacity-90 transition inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Catatan Pertama</span>
                </button>
            </div>
            @endif
        </div>

        {{-- ============================================================== --}}

        {{-- MODAL TAMBAH CATATAN BARU                                      --}}

        {{-- ============================================================== --}}

        <template x-teleport="body">
            <div x-show="showAddNoteModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
                <div x-show="showAddNoteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-[#160B29]/70 backdrop-blur-md transition-opacity"></div>
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showAddNoteModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden text-left relative z-10">
                        {{-- Header Modal --}}
                        <div class="px-6 py-4.5 bg-purple-deep text-white flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-white tracking-tight">Tambah Catatan Formulir Klien</h3>
                            </div>
                            <button type="button" @click="handleCloseAddModal()" 
                                    class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-lg font-bold"
                                    title="Tutup">
                                &times;
                            </button>
                        </div>

                        {{-- Form Body --}}
                        <form action="{{ route('clients.notes.store', $client) }}" method="POST" class="p-6 space-y-4" @submit="window.isSubmittingForm = true">
                            @csrf

                            <div>
                                <textarea name="note" rows="6" x-model="addNoteText" required
                                          class="w-full p-4 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white resize-y leading-relaxed"
                                          placeholder="Tuliskan catatan keluhan, alasan konseling, tujuan asesmen, atau keterangan penting hasil isian formulir klien..."></textarea>
                            </div>

                            {{-- Footer Modal --}}
                            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                                <button type="button" @click="handleCloseAddModal()"
                                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-5 py-2.5 rounded-xl bg-purple-deep text-white text-xs font-semibold hover:opacity-95 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                    <span>Simpan Catatan Formulir</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- ============================================================== --}}
        {{-- MODAL KONFIRMASI: PERUBAHAN TAMBAH CATATAN BELUM DISIMPAN       --}}
        {{-- ============================================================== --}}
        <template x-teleport="body">
            <div x-cloak x-show="showAddUnsavedModal" class="fixed inset-0 z-[110] overflow-y-auto" role="dialog" aria-modal="true">
                <div x-show="showAddUnsavedModal" 
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md transition-opacity"></div>
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showAddUnsavedModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-[2rem] bg-white/80 backdrop-blur-2xl text-center shadow-[0_30px_70px_rgba(74,35,128,0.18),inset_0_1px_2px_rgba(255,255,255,0.95)] transition-all sm:my-8 sm:w-full sm:max-w-md border border-white/80 p-7 sm:p-9">
                        
                        <div class="absolute top-0 inset-x-10 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none"></div>

                        <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center mb-5 backdrop-blur-md shadow-xs relative z-10">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        </div>

                        <h3 class="text-xl font-extrabold text-[#260E45] tracking-tight mb-2 relative z-10">
                            Tinggalkan Catatan?
                        </h3>
                        <p class="text-xs sm:text-sm text-[#6B5B85] leading-relaxed max-w-xs mx-auto mb-7 relative z-10">
                            Catatan baru yang telah Anda ketik belum disimpan. Jika Anda keluar sekarang, catatan ini akan dibuang.
                        </p>

                        <div class="grid grid-cols-2 gap-3.5 pt-1 relative z-10">
                            <button 
                                type="button" 
                                @click="showAddUnsavedModal = false"
                                class="w-full py-3 rounded-2xl text-xs sm:text-sm font-bold text-purple-deep hover:text-[#260E45] bg-white/70 hover:bg-white border border-[#D9C2F0]/80 hover:border-purple-300 shadow-xs hover:shadow-sm active:scale-[0.98] transition-all duration-200 cursor-pointer flex items-center justify-center"
                            >
                                Lanjut Mengisi
                            </button>
                            <button 
                                type="button"
                                @click="forceCloseAddModal()" 
                                class="w-full py-3 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 shadow-[0_8px_20px_rgba(245,158,11,0.3),inset_0_1px_1px_rgba(255,255,255,0.4)] hover:shadow-[0_10px_25px_rgba(245,158,11,0.45)] active:scale-[0.98] cursor-pointer flex items-center justify-center border border-white/20 relative overflow-hidden"
                            >
                                <span class="relative z-10">Ya, Buang</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- ============================================================== --}}

        {{-- MODAL EDIT CATATAN                                             --}}

        {{-- ============================================================== --}}

        <template x-teleport="body">
            <div x-show="showEditNoteModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
                <div x-show="showEditNoteModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-[#160B29]/70 backdrop-blur-md transition-opacity"></div>
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <div x-show="showEditNoteModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden text-left relative z-10">
                        {{-- Header Modal --}}
                        <div class="px-6 py-4.5 bg-purple-deep text-white flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-white tracking-tight">Edit Catatan Formulir Klien</h3>
                                <p class="text-xs font-semibold text-purple-200">Perbarui tanggal atau isi catatan formulir klien</p>
                            </div>
                            <button type="button" @click="handleCloseEditModal()" 
                                    class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-lg font-bold"
                                    title="Tutup">
                                &times;
                            </button>
                        </div>

                        {{-- Form Body --}}
                        <form :action="editActionUrl" method="POST" id="client-edit-note-form" class="p-6 space-y-4" @submit="window.isSubmittingForm = true">
                            @csrf
                            @method('PUT')

                            <div>
                                <textarea name="note" rows="6" x-model="editNoteText"
                                          class="w-full p-4 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white resize-y leading-relaxed"
                                          placeholder="Tuliskan catatan keluhan, alasan konseling, tujuan asesmen, atau keterangan penting hasil isian formulir klien..."></textarea>
                            </div>

                            {{-- Footer Modal --}}
                            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                                <button type="button" @click="handleCloseEditModal()"
                                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-5 py-2.5 rounded-xl bg-purple-deep text-white text-xs font-semibold hover:opacity-95 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                    <span>Perbarui Catatan Formulir</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
        {{-- ============================================================== --}}
        {{-- MODAL KONFIRMASI: PERUBAHAN EDIT CATATAN BELUM DISIMPAN         --}}
        {{-- ============================================================== --}}
        <template x-teleport="body">
            <div x-cloak x-show="showEditUnsavedModal" class="fixed inset-0 z-[110] overflow-y-auto" role="dialog" aria-modal="true">
            <div x-show="showEditUnsavedModal" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md transition-opacity"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div x-show="showEditUnsavedModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-[2rem] bg-white/80 backdrop-blur-2xl text-center shadow-[0_30px_70px_rgba(74,35,128,0.18),inset_0_1px_2px_rgba(255,255,255,0.95)] transition-all sm:my-8 sm:w-full sm:max-w-md border border-white/80 p-7 sm:p-9">
                    
                    <div class="absolute top-0 inset-x-10 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none"></div>

                    <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center mb-5 backdrop-blur-md shadow-xs relative z-10">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>

                    <h3 class="text-xl font-extrabold text-[#260E45] tracking-tight mb-2 relative z-10">
                        Tinggalkan Perubahan?
                    </h3>
                    <p class="text-xs sm:text-sm text-[#6B5B85] leading-relaxed max-w-xs mx-auto mb-7 relative z-10">
                        Perubahan catatan yang telah Anda ketik belum disimpan. Jika Anda keluar sekarang, perubahan ini akan dibuang.
                    </p>

                    <div class="grid grid-cols-2 gap-3.5 pt-1 relative z-10">
                        <button 
                            type="button" 
                            @click="showEditUnsavedModal = false"
                            class="w-full py-3 rounded-2xl text-xs sm:text-sm font-bold text-purple-deep hover:text-[#260E45] bg-white/70 hover:bg-white border border-[#D9C2F0]/80 hover:border-purple-300 shadow-xs hover:shadow-sm active:scale-[0.98] transition-all duration-200 cursor-pointer flex items-center justify-center"
                        >
                            Lanjut Mengisi
                        </button>
                        <button 
                            type="button"
                            @click="forceCloseEditModal()" 
                            class="w-full py-3 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 shadow-[0_8px_20px_rgba(245,158,11,0.3),inset_0_1px_1px_rgba(255,255,255,0.4)] hover:shadow-[0_10px_25px_rgba(245,158,11,0.45)] active:scale-[0.98] cursor-pointer flex items-center justify-center border border-white/20 relative overflow-hidden"
                        >
                            <span class="relative z-10">Ya, Buang</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </template>
    </div>

    {{-- ========================================================================= --}}

    {{-- TAB 3: KONSELING (Formulir Intake + Jadwal Booking)                       --}}

    {{-- ========================================================================= --}}

    <div x-show="activeTab === 'konseling'" x-cloak class="space-y-6">

        {{-- Bagian A: Tabel Formulir Pendaftaran Konseling --}}

        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white text-base">Rekam Jejak Formulir Masuk</h3>
                </div>
                <a href="{{ route('clients.create', ['client_id' => $client->id, 'jenis' => 'individu', 'form_type' => 'dewasa']) }}"
                   class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Buat Layanan Baru</span>
                </a>
            </div>

            @if($counselingForms->isEmpty())
            <div class="py-10 text-center text-slate-400 space-y-2">
                <svg class="w-10 h-10 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <p class="text-sm font-medium text-black">Belum ada formulir pendaftaran konseling untuk klien ini.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm font-medium">
                    <thead class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA] text-xs select-none">
                        <tr>
                            {{-- Filter Kolom ID --}}
                            <th class="py-3 px-4 font-semibold text-black">
                                <button type="button" @click="toggleCounselingSort('ticket')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>ID</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="counselingSortCol === 'ticket' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="counselingSortCol === 'ticket' && counselingSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="counselingSortCol === 'ticket' && counselingSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="counselingSortCol !== 'ticket'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>

                            {{-- Filter Kolom Jenis Formulir --}}
                            <th class="py-3 px-4 font-semibold text-black">
                                <button type="button" @click="toggleCounselingSort('form_type')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>Jenis Formulir</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="counselingSortCol === 'form_type' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="counselingSortCol === 'form_type' && counselingSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="counselingSortCol === 'form_type' && counselingSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="counselingSortCol !== 'form_type'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>

                            {{-- Filter Kolom Waktu Formulir Masuk --}}
                            <th class="py-3 px-4 font-semibold text-black">
                                <button type="button" @click="toggleCounselingSort('created_at')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>Waktu Formulir Masuk</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="counselingSortCol === 'created_at' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="counselingSortCol === 'created_at' && counselingSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="counselingSortCol === 'created_at' && counselingSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="counselingSortCol !== 'created_at'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>

                            {{-- Filter Kolom Media Konseling --}}
                            <th class="py-3 px-4 font-semibold text-black">
                                <button type="button" @click="toggleCounselingSort('media_konseling')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>Media Konseling</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="counselingSortCol === 'media_konseling' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="counselingSortCol === 'media_konseling' && counselingSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="counselingSortCol === 'media_konseling' && counselingSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="counselingSortCol !== 'media_konseling'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-xs">
                        <template x-for="form in sortedCounselingForms" :key="form.id">
                            <tr @click="openDossier(form)" class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" title="Klik untuk melihat detail formulir">
                                <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="form.ticket"></td>
                                <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="form.form_type_label"></td>
                                <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="form.created_at_formatted"></td>
                                <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="form.media_konseling || '-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Bagian B: Tabel Data Booking Layanan Konseling --}}
        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white text-base">Rekam Jejak Booking Layanan</h3>
                </div>
                <button type="button"
                        @click="$dispatch('open-booking-modal', { client_id: {{ $client->id }}, category: 'konseling' })"
                        class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Booking Layanan</span>
                </button>
            </div>

            <template x-if="sortedCounselingBookings.length === 0">
                <div class="py-10 text-center text-slate-400 space-y-2">
                    <svg class="w-10 h-10 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <p class="text-sm font-medium text-black">Belum ada data booking layanan konseling untuk klien ini.</p>
                </div>
            </template>
            <template x-if="sortedCounselingBookings.length > 0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm font-medium">
                        <thead class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA] text-xs select-none">
                            <tr>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('id')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>ID</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'id' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'id' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'id' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'id'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('layanan_name')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Layanan</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'layanan_name' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'layanan_name' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'layanan_name' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'layanan_name'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('counseling_jenis')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Jenis</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'counseling_jenis' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'counseling_jenis' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'counseling_jenis' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'counseling_jenis'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('counseling_metode')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Metode</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'counseling_metode' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'counseling_metode' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'counseling_metode' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'counseling_metode'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('counseling_klien')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Klien</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'counseling_klien' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'counseling_klien' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'counseling_klien' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'counseling_klien'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('counseling_kategori')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Kategori</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'counseling_kategori' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'counseling_kategori' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'counseling_kategori' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'counseling_kategori'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('schedule')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Jadwal Konseling</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'schedule' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'schedule' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'schedule' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'schedule'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('counselor_name')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Psikolog</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'counselor_name' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'counselor_name' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'counselor_name' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'counselor_name'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('duration_formatted')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Durasi</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'duration_formatted' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'duration_formatted' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'duration_formatted' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'duration_formatted'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('overtime_label')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Overtime</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'overtime_label' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'overtime_label' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'overtime_label' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'overtime_label'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('payment_label')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Pembayaran</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'payment_label' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'payment_label' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'payment_label' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'payment_label'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="toggleCounselingBookingSort('status_label')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Status Layanan</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="counselingBookingSortCol === 'status_label' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="counselingBookingSortCol === 'status_label' && counselingBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol === 'status_label' && counselingBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="counselingBookingSortCol !== 'status_label'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-xs">
                            <template x-for="booking in sortedCounselingBookings" :key="booking.id">
                                <tr @click="window.location.href = booking.detail_url" class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" title="Klik untuk melihat detail sesi booking">
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.ticket"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.layanan_name"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.counseling_jenis"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.counseling_metode"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.counseling_klien"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.counseling_kategori"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.schedule_formatted"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.counselor_name"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.duration_formatted"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.overtime_label"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.payment_label"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.status_label"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>

    {{-- ========================================================================= --}}

    {{-- TAB 4: PSIKOTES (Formulir Masuk + Laporan Hasil Psikotes)                 --}}

    {{-- ========================================================================= --}}

    <div x-show="activeTab === 'psychotest'" x-cloak class="space-y-6">

        {{-- Bagian A: Tabel Formulir Pendaftaran / Asesmen Psikotes Masuk --}}

        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white text-base">Rekam Jejak Formulir Masuk</h3>
                </div>
                <a href="{{ route('clients.create', ['client_id' => $client->id, 'jenis' => $isIndustri ? 'industri' : 'individu', 'form_type' => $isIndustri ? 'industri' : 'non_industri']) }}"
                   class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Buat Layanan Baru</span>
                </a>
            </div>

            @if($psychotestForms->isEmpty())
            <div class="py-10 text-center text-slate-400 space-y-2">
                <svg class="w-10 h-10 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <p class="text-sm font-medium text-black">Belum ada formulir asesmen atau psikotes untuk klien ini.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm font-medium">
                    <thead class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA] text-xs select-none">
                        <tr>
                            {{-- Filter Kolom ID --}}
                            <th class="py-3 px-4 font-semibold text-black">
                                <button type="button" @click="togglePsychotestSort('ticket')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>ID</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="psychotestSortCol === 'ticket' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="psychotestSortCol === 'ticket' && psychotestSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="psychotestSortCol === 'ticket' && psychotestSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="psychotestSortCol !== 'ticket'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>

                            {{-- Filter Kolom Jenis Layanan --}}
                            <th class="py-3 px-4 font-semibold text-black">
                                <button type="button" @click="togglePsychotestSort('form_type')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>Jenis Layanan</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="psychotestSortCol === 'form_type' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="psychotestSortCol === 'form_type' && psychotestSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="psychotestSortCol === 'form_type' && psychotestSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="psychotestSortCol !== 'form_type'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>

                            {{-- Filter Kolom Waktu Pendaftaran Masuk --}}
                            <th class="py-3 px-4 font-semibold text-black">
                                <button type="button" @click="togglePsychotestSort('created_at')"
                                        class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                    <span>Waktu Pendaftaran Masuk</span>
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                          :class="psychotestSortCol === 'created_at' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                        <template x-if="psychotestSortCol === 'created_at' && psychotestSortDir === 'asc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                        </template>
                                        <template x-if="psychotestSortCol === 'created_at' && psychotestSortDir === 'desc'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                        </template>
                                        <template x-if="psychotestSortCol !== 'created_at'">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                        </template>
                                    </span>
                                </button>
                            </th>

                            <th class="py-3 px-4 font-semibold text-black">Status Layanan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-xs">
                        <template x-for="form in sortedPsychotestForms" :key="form.id">
                            <tr @click="openDossier(form)" class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" title="Klik untuk melihat detail formulir">
                                <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="form.ticket"></td>
                                <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="form.form_type_label"></td>
                                <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="(form.booking && form.booking.tanggal_dijadwalkan) ? ('Terjadwal: ' + form.booking.tanggal_dijadwalkan_formatted) : 'Menunggu Penjadwalan'"></td>
                                <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="form.created_at_formatted"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Bagian B: Tabel Data Booking Layanan Psikotes --}}
        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white text-base">Rekam Jejak Booking Layanan</h3>
                </div>
                <button type="button"
                        @click="$dispatch('open-booking-modal', { client_id: {{ $client->id }}, category: 'psikotes' })"
                        class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Booking Layanan</span>
                </button>
            </div>

            <template x-if="sortedPsychotestBookings.length === 0">
                <div class="py-10 text-center text-slate-400 space-y-2">
                    <svg class="w-10 h-10 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <p class="text-sm font-medium text-black">Belum ada data booking layanan psikotes untuk klien ini.</p>
                </div>
            </template>
            <template x-if="sortedPsychotestBookings.length > 0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm font-medium">
                        <thead class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA] text-xs select-none">
                            <tr>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="togglePsychotestBookingSort('id')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>ID</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="psychotestBookingSortCol === 'id' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="psychotestBookingSortCol === 'id' && psychotestBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol === 'id' && psychotestBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol !== 'id'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="togglePsychotestBookingSort('service_package_name')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Paket Layanan</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="psychotestBookingSortCol === 'service_package_name' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="psychotestBookingSortCol === 'service_package_name' && psychotestBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol === 'service_package_name' && psychotestBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol !== 'service_package_name'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="togglePsychotestBookingSort('schedule')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Jadwal Psikotes</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="psychotestBookingSortCol === 'schedule' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="psychotestBookingSortCol === 'schedule' && psychotestBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol === 'schedule' && psychotestBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol !== 'schedule'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="togglePsychotestBookingSort('staff_penguji_name')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Staff Penguji</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="psychotestBookingSortCol === 'staff_penguji_name' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="psychotestBookingSortCol === 'staff_penguji_name' && psychotestBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol === 'staff_penguji_name' && psychotestBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol !== 'staff_penguji_name'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="togglePsychotestBookingSort('metode_lokasi')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Metode / Lokasi</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="psychotestBookingSortCol === 'metode_lokasi' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="psychotestBookingSortCol === 'metode_lokasi' && psychotestBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol === 'metode_lokasi' && psychotestBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol !== 'metode_lokasi'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                                <th class="py-3 px-4 font-semibold text-black">
                                    <button type="button" @click="togglePsychotestBookingSort('status_label')"
                                            class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                        <span>Status Layanan</span>
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                              :class="psychotestBookingSortCol === 'status_label' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                            <template x-if="psychotestBookingSortCol === 'status_label' && psychotestBookingSortDir === 'asc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol === 'status_label' && psychotestBookingSortDir === 'desc'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                            </template>
                                            <template x-if="psychotestBookingSortCol !== 'status_label'">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                            </template>
                                        </span>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-xs">
                            <template x-for="booking in sortedPsychotestBookings" :key="booking.id">
                                <tr @click="window.location.href = booking.detail_url" class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" title="Klik untuk melihat detail sesi booking">
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.ticket"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.service_package_name"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.schedule_formatted"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.staff_penguji_name"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.metode_lokasi"></td>
                                    <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="booking.status_label"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </div>
{{-- ========================================================================= --}}

    {{-- TAB 5: PENGATURAN KLIEN & AKSI                                            --}}

    {{-- ========================================================================= --}}

    
            {{-- ========================================================================= --}}
            {{-- TAB 5: RIWAYAT LAYANAN (FITUR BARU)                                       --}}
            {{-- ========================================================================= --}}
            <div x-show="activeTab === 'history'" x-cloak class="space-y-6"
                 x-data="{
                     serviceHistory: {{ json_encode($allServiceHistoryJson ?? []) }},
                     filterKategori: 'semua',
                     historySortCol: null,
                     historySortDir: 'asc',
                     toggleHistorySort(column) {
                         if (this.historySortCol !== column) {
                             this.historySortCol = column;
                             this.historySortDir = 'asc';
                         } else if (this.historySortDir === 'asc') {
                             this.historySortDir = 'desc';
                         } else {
                             this.historySortCol = null;
                             this.historySortDir = 'asc';
                         }
                     },
                     get filteredHistory() {
                         return (this.serviceHistory || []).filter(i => this.filterKategori === 'semua' || i.kategori === this.filterKategori);
                     },
                     get sortedHistory() {
                         const list = [...this.filteredHistory];
                         if (!this.historySortCol) {
                             return list;
                         }
                         return list.sort((a, b) => {
                             let valA, valB;
                             switch (this.historySortCol) {
                                 case 'kategori_label':
                                     valA = (a.kategori_label || '').toLowerCase();
                                     valB = (b.kategori_label || '').toLowerCase();
                                     break;
                                 case 'package_name':
                                     valA = (a.package_name || '').toLowerCase();
                                     valB = (b.package_name || '').toLowerCase();
                                     break;
                                 case 'session_type':
                                     valA = (a.session_type || '').toLowerCase();
                                     valB = (b.session_type || '').toLowerCase();
                                     break;
                                 case 'counselor_name':
                                     valA = (a.counselor_name || '').toLowerCase();
                                     valB = (b.counselor_name || '').toLowerCase();
                                     break;
                                 case 'booking_date':
                                     valA = a.booking_date_raw || '';
                                     valB = b.booking_date_raw || '';
                                     break;
                                 case 'scheduled_date':
                                     valA = a.scheduled_date_raw || '';
                                     valB = b.scheduled_date_raw || '';
                                     break;
                                 case 'status_label':
                                     valA = (a.status_label || '').toLowerCase();
                                     valB = (b.status_label || '').toLowerCase();
                                     break;
                                 case 'price':
                                     valA = Number(a.price_raw) || 0;
                                     valB = Number(b.price_raw) || 0;
                                     return this.historySortDir === 'asc' ? valA - valB : valB - valA;
                                 default:
                                     return 0;
                             }
                             if (valA < valB) return this.historySortDir === 'asc' ? -1 : 1;
                             if (valA > valB) return this.historySortDir === 'asc' ? 1 : -1;
                             return 0;
                         });
                     }
                 }">
                <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                    <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-white text-base">Riwayat Layanan Klien</h3>
                            <p class="text-xs font-medium text-purple-200 mt-0.5">Daftar rekam jejak transaksi seluruh layanan (Konseling & Psikotes) yang pernah terdaftar.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <select x-model="filterKategori" class="px-3.5 py-1.5 rounded-xl bg-white text-black text-xs font-medium border border-transparent focus:outline-none cursor-pointer shadow-xs">
                                <option value="semua">Semua Layanan</option>
                                <option value="konseling">Konseling</option>
                                <option value="psikotes">Psikotes</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm font-medium">
                            <thead class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA] text-xs select-none">
                                <tr>
                                    {{-- Kolom Layanan --}}
                                    <th class="py-3 px-4 font-semibold text-black">
                                        <button type="button" @click="toggleHistorySort('kategori_label')"
                                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                            <span>Layanan</span>
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                                  :class="historySortCol === 'kategori_label' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                                <template x-if="historySortCol === 'kategori_label' && historySortDir === 'asc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                </template>
                                                <template x-if="historySortCol === 'kategori_label' && historySortDir === 'desc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                </template>
                                                <template x-if="historySortCol !== 'kategori_label'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>

                                    {{-- Kolom Paket / Sesi --}}
                                    <th class="py-3 px-4 font-semibold text-black">
                                        <button type="button" @click="toggleHistorySort('package_name')"
                                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                            <span>Paket / Sesi</span>
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                                  :class="historySortCol === 'package_name' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                                <template x-if="historySortCol === 'package_name' && historySortDir === 'asc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                </template>
                                                <template x-if="historySortCol === 'package_name' && historySortDir === 'desc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                </template>
                                                <template x-if="historySortCol !== 'package_name'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>

                                    {{-- Kolom Metode --}}
                                    <th class="py-3 px-4 font-semibold text-black">
                                        <button type="button" @click="toggleHistorySort('session_type')"
                                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                            <span>Metode</span>
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                                  :class="historySortCol === 'session_type' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                                <template x-if="historySortCol === 'session_type' && historySortDir === 'asc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                </template>
                                                <template x-if="historySortCol === 'session_type' && historySortDir === 'desc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                </template>
                                                <template x-if="historySortCol !== 'session_type'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>

                                    {{-- Kolom Konselor / Penguji --}}
                                    <th class="py-3 px-4 font-semibold text-black">
                                        <button type="button" @click="toggleHistorySort('counselor_name')"
                                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                            <span>Konselor / Penguji</span>
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                                  :class="historySortCol === 'counselor_name' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                                <template x-if="historySortCol === 'counselor_name' && historySortDir === 'asc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                </template>
                                                <template x-if="historySortCol === 'counselor_name' && historySortDir === 'desc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                </template>
                                                <template x-if="historySortCol !== 'counselor_name'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>

                                    {{-- Kolom Tgl Booking --}}
                                    <th class="py-3 px-4 font-semibold text-black">
                                        <button type="button" @click="toggleHistorySort('booking_date')"
                                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                            <span>Tgl Booking</span>
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                                  :class="historySortCol === 'booking_date' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                                <template x-if="historySortCol === 'booking_date' && historySortDir === 'asc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                </template>
                                                <template x-if="historySortCol === 'booking_date' && historySortDir === 'desc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                </template>
                                                <template x-if="historySortCol !== 'booking_date'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>

                                    {{-- Kolom Jadwal Sesi --}}
                                    <th class="py-3 px-4 font-semibold text-black">
                                        <button type="button" @click="toggleHistorySort('scheduled_date')"
                                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                            <span>Jadwal Sesi</span>
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                                  :class="historySortCol === 'scheduled_date' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                                <template x-if="historySortCol === 'scheduled_date' && historySortDir === 'asc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                </template>
                                                <template x-if="historySortCol === 'scheduled_date' && historySortDir === 'desc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                </template>
                                                <template x-if="historySortCol !== 'scheduled_date'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>

                                    {{-- Kolom Status --}}
                                    <th class="py-3 px-4 font-semibold text-black">
                                        <button type="button" @click="toggleHistorySort('status_label')"
                                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                            <span>Status Layanan</span>
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                                  :class="historySortCol === 'status_label' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                                <template x-if="historySortCol === 'status_label' && historySortDir === 'asc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                </template>
                                                <template x-if="historySortCol === 'status_label' && historySortDir === 'desc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                </template>
                                                <template x-if="historySortCol !== 'status_label'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>

                                    {{-- Kolom Biaya / Tarif --}}
                                    <th class="py-3 px-4 font-semibold text-black">
                                        <button type="button" @click="toggleHistorySort('price')"
                                                class="inline-flex items-center gap-1.5 hover:text-purple-deep group transition cursor-pointer select-none">
                                            <span>Biaya / Tarif</span>
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md transition"
                                                  :class="historySortCol === 'price' ? 'bg-purple-100 text-purple-deep font-bold' : 'text-slate-400 group-hover:text-purple-deep'">
                                                <template x-if="historySortCol === 'price' && historySortDir === 'asc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                                </template>
                                                <template x-if="historySortCol === 'price' && historySortDir === 'desc'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                                </template>
                                                <template x-if="historySortCol !== 'price'">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 15l5 5 5-5M7 9l5-5 5 5"/></svg>
                                                </template>
                                            </span>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-xs">
                                <template x-for="item in sortedHistory" :key="item.id">
                                    <tr @click="window.location.href = item.show_url" class="table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer" title="Klik untuk melihat detail sesi booking">
                                        <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="item.kategori_label"></td>
                                        <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="item.package_name"></td>
                                        <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="item.session_type"></td>
                                        <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="item.counselor_name"></td>
                                        <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="item.booking_date"></td>
                                        <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="item.scheduled_date"></td>
                                        <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="item.status_label"></td>
                                        <td class="py-3 px-4 font-medium text-black text-xs whitespace-nowrap" x-text="item.price_formatted"></td>
                                    </tr>
                                </template>
                                <tr x-show="sortedHistory.length === 0">
                                    <td colspan="8" class="py-8 text-center text-xs font-medium text-slate-400">
                                        Belum ada riwayat layanan terdaftar untuk kategori ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

<div x-show="activeTab === 'settings'" x-cloak class="space-y-5">

        {{-- 1. Fitur Hubungi Klien via WhatsApp --}}
        <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-semibold text-black">Hubungi Klien via WhatsApp</h4>

                @if(!empty($waPhone))
                    <p class="text-xs font-medium text-gray-500 mt-0.5">
                        Nomor terdaftar: <span class="text-black font-semibold">{{ $client->phone }}</span>
                    </p>
                @else
                    <p class="text-xs font-medium text-amber-600 mt-0.5">
                        No. telepon belum tercatat di biodata klien.
                    </p>
                @endif
            </div>
            <div>

                @if(!empty($waPhone))
                    <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode('Halo ' . $client->name . ', saya dari Unit Konsultasi Psikologi (UC PSC).') }}"
                       target="_blank" rel="noopener noreferrer"
                       class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                        </svg>
                        <span>Hubungi WhatsApp</span>
                    </a>
                @else
                    <button type="button" @click="editMode = true"
                            class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-purple-50 text-purple-deep font-semibold text-xs transition">
                        Lengkapi Nomor HP
                    </button>
                @endif
            </div>
        </div>

        {{-- 2. Hapus Akun Klien (Sederhana & Rapi) --}}

        @if(auth()->user()?->isAdmin())
        <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-semibold text-black">Hapus Akun Klien</h4>
                <p class="text-xs font-medium text-gray-500 mt-0.5">Hapus data klien ini beserta seluruh formulir dan reservasinya secara permanen dari sistem.</p>
            </div>
            <button type="button" @click="deleteModalOpen = true"
                    class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-xs sm:text-sm transition shadow-xs flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                <span>Hapus Data Klien</span>
            </button>
        </div>
        @endif
    </div>
            </div>
        </div>

    {{-- ========================================================================= --}}
    {{-- DOSSIER MODAL DETAIL JAWABAN FORMULIR                                     --}}
    {{-- ========================================================================= --}}
    <template x-teleport="body">

        <div x-show="showDossierModal" x-cloak 
             class="fixed inset-0 z-[100] overflow-y-auto"
             role="dialog" aria-modal="true">

            {{-- Backdrop Overlay (Full Viewport, Dark & Blur tanpa terpotong) --}}

            <div x-show="showDossierModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-[#160B29]/70 backdrop-blur-md transition-opacity"></div>

            {{-- Dialog Wrapper (Tanpa outline/border yang keluar) --}}
            <div class="min-h-full flex items-center justify-center p-3 sm:p-6 lg:p-8 text-left">

                <div x-show="showDossierModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative bg-white rounded-3xl w-full max-w-5xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">

                    {{-- Modal Header (Nama Formulir yang Diisi) --}}
                    <div class="px-7 py-5 bg-purple-deep text-white flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-xl sm:text-2xl font-bold text-white tracking-tight" x-text="getFormTitle(selectedForm)"></h3>
                        </div>
                        <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">

                            {{-- Tombol Download Laporan PDF (Cetak Langsung Tanpa Buka Tab Baru) --}}
                            <button type="button" 
                                    @click="printPdf(selectedForm)"
                                    :disabled="isPrintingPdf || !selectedForm"
                                    class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs sm:text-sm font-semibold transition cursor-pointer shadow-xs backdrop-blur-xs select-none disabled:opacity-60 disabled:cursor-not-allowed"
                                    title="Download / Cetak Laporan PDF">
                                <template x-if="!isPrintingPdf">
                                    <svg class="w-4 h-4 text-white shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                </template>
                                <template x-if="isPrintingPdf">
                                    <svg class="w-4 h-4 text-white animate-spin shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="isPrintingPdf ? 'Menyiapkan...' : 'Download PDF'"></span>
                            </button>

                            {{-- Tombol Tutup (X) --}}
                            <button type="button" @click="showDossierModal = false; cancelEditFormCard()" 
                                    class="w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-xl font-bold"
                                    title="Tutup">
                                &times;
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body (Scrollable Answers) --}}
                    <div class="p-6 sm:p-8 overflow-y-auto space-y-6 text-black bg-white">
                        <template x-if="selectedForm">
                            <div class="space-y-6">



                                {{-- ========================================================= --}}

                                {{-- VIEW DOSSIER: FORMULIR RIWAYAT HIDUP - INDUSTRI           --}}
                                {{-- ========================================================= --}}
                                <template x-if="selectedForm.form_type === 'industri'">
                                    <div class="space-y-6">

                                        {{-- LEMBAR PERNYATAAN KEJUJURAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lembar Pernyataan Kejujuran</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pernyataan</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex flex-col justify-center">
                                                            <div class="flex items-center gap-2 mt-1">
                                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                            <span class="text-black text-sm font-medium" x-text="selectedForm.answers.consent_agree || 'Setuju'"></span>
                                                        </div>
                                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">"Semua keterangan yang saya berikan dalam Formulir Riwayat Hidup ini, saya buat dengan jujur dan sungguh-sungguh."</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Waktu Pengisian Form</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between gap-2 text-sm font-medium text-black">
                                                            <div x-text="formatFormSubmissionDate(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                            <div x-text="formatFormSubmissionTime(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- DATA DIRI & POSISI PEKERJAAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Diri & Posisi Pekerjaan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_datadiri'" 
                                                            @click="startEditFormCard('industri_datadiri')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_datadiri'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_datadiri')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap & Gelar</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_lengkap || selectedForm.client_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_lengkap" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Posisi yang Dituju</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.posisi_dituju || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.posisi_dituju" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan_saat_ini || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan_saat_ini" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <template x-if="selectedForm.answers.nama_perusahaan">
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Nama Perusahaan</label>
                                                            <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_perusahaan"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_perusahaan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                        </div>
                                                    </template>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kelamin || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <select x-model="editFormAnswers.jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat, Tanggal Lahir</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="formatBirthPlaceDate(selectedForm.answers.tempat_lahir, selectedForm.answers.tanggal_lahir, selectedForm.answers.tempat_tanggal_lahir)"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers.tempat_lahir" placeholder="Kota Kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="date" x-model="editFormAnswers.tanggal_lahir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Urutan Kelahiran</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.urutan_kelahiran || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.urutan_kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone || selectedForm.client_phone || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <textarea x-model="editFormAnswers.email" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama / Kepercayaan</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <select x-model="editFormAnswers.agama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_bangsa || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_bangsa" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Status Perkawinan</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.status_perkawinan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.status_perkawinan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_terakhir || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Asal Kota</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.asal_kota || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.asal_kota" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Tinggal Sekarang</label>
                                                        <div x-show="editingFormCard !== 'industri_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.alamat_sekarang || selectedForm.answers.alamat || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_datadiri'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat_sekarang" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            <div x-show="editingFormCard === 'industri_datadiri'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_datadiri')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- KUESIONER STRES (PSS-10) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Kuesioner Stres (PSS-10)</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_pss10'" 
                                                            @click="startEditFormCard('industri_pss10')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_pss10'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_pss10')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Keterangan Skala Nilai (0 - 4)</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        <span class="text-black text-sm font-medium block ">0 = Tidak Pernah | 1 = Hampir Tidak Pernah | 2 = Kadang-kadang | 3 = Cukup Sering | 4 = Sangat Sering</span>
                                                    </div>
                                                </div>

                                                @php
                                                $pssLabels = [
                                                    1 => '1. Kecewa karena sesuatu terjadi tak terduga',
                                                    2 => '2. Tidak mampu mengendalikan hal penting dalam hidup',
                                                    3 => '3. Merasa gelisah dan stres',
                                                    4 => '4. Percaya diri selesaikan masalah pribadi',
                                                    5 => '5. Segala sesuatu berjalan sesuai keinginan',
                                                    6 => '6. Tidak bisa atasi hal yang harus dilakukan',
                                                    7 => '7. Mampu kendalikan hal menjengkelkan',
                                                    8 => '8. Merasa mampu mengendalikan permasalahan',
                                                    9 => '9. Marah karena hal di luar kendali',
                                                    10 => '10. Tidak mampu selesaikan masalah menumpuk',
                                                ];
                                                @endphp

                                                @foreach($pssLabels as $pssNum => $pssText)
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">{{ $pssText }}</label>
                                                    <div x-show="editingFormCard !== 'industri_pss10'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        <span class="text-black text-sm font-medium block "
                                                          x-text="selectedForm.answers.pss_{{ $pssNum }} !== undefined && selectedForm.answers.pss_{{ $pssNum }} !== null && selectedForm.answers.pss_{{ $pssNum }} !== '' ? selectedForm.answers.pss_{{ $pssNum }} : '-'"></span>
                                                    </div>
                                                        <div x-show="editingFormCard === 'industri_pss10'" x-cloak>
                                                            <select x-model="editFormAnswers['pss_{{ $pssNum }}']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                            <option value="">-- Pilih Skor (0 - 4) --</option>
                                                            <option value="0">0 - Tidak Pernah</option>
                                                            <option value="1">1 - Hampir Tidak Pernah</option>
                                                            <option value="2">2 - Kadang-Kadang</option>
                                                            <option value="3">3 - Cukup Sering</option>
                                                            <option value="4">4 - Sangat Sering</option>
                                                        </select>
                                                        </div>
                                                </div>
                                                @endforeach
                                            <div x-show="editingFormCard === 'industri_pss10'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_pss10')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- SUSUNAN ANGGOTA KELUARGA --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Susunan Anggota Keluarga</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_keluarga'" 
                                                            @click="startEditFormCard('industri_keluarga')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_keluarga'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_keluarga')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-6">

                                                {{-- Profil Ayah --}}
                                                <div class="space-y-4">
                                                    <span class="text-sm font-bold text-slate-700 uppercase tracking-wider block">Profil Ayah</span>
                                                    <div class="space-y-4">
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Nama Ayah</label>
                                                            <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ayah_nama || '-'"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ayah_nama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Usia Ayah</label>
                                                            <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ayah_usia ? selectedForm.answers.ayah_usia + ' tahun' : '-'"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ayah_usia" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Ayah</label>
                                                            <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ayah_pendidikan || '-'"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers.ayah_pendidikan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Ayah</label>
                                                            <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ayah_pekerjaan || '-'"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ayah_pekerjaan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Profil Ibu --}}
                                                <div class="space-y-4 pt-4 border-t border-[#F3EAFB]">
                                                    <span class="text-sm font-bold text-slate-700 uppercase tracking-wider block">Profil Ibu</span>
                                                    <div class="space-y-4">
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Nama Ibu</label>
                                                            <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ibu_nama || '-'"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ibu_nama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Usia Ibu</label>
                                                            <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ibu_usia ? selectedForm.answers.ibu_usia + ' tahun' : '-'"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ibu_usia" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Ibu</label>
                                                            <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ibu_pendidikan || '-'"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers.ibu_pendidikan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Ibu</label>
                                                            <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ibu_pekerjaan || '-'"></span>
                                                            </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ibu_pekerjaan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Profil Pasangan (jika ada) --}}
                                                <template x-if="selectedForm.answers.pasangan_nama">
                                                    <div class="space-y-4 pt-4 border-t border-[#F3EAFB]">
                                                        <span class="text-sm font-bold text-slate-700 uppercase tracking-wider block">Profil Pasangan</span>
                                                        <div class="space-y-4">
                                                            <div>
                                                                <label class="block text-xs font-medium text-slate-500 mb-1">Nama Pasangan</label>
                                                                <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                    <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pasangan_nama || '-'"></span>
                                                                </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pasangan_nama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-medium text-slate-500 mb-1">Usia Pasangan</label>
                                                                <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                    <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pasangan_usia ? selectedForm.answers.pasangan_usia + ' tahun' : '-'"></span>
                                                                </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pasangan_usia" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Pasangan</label>
                                                                <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                    <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pasangan_pendidikan || '-'"></span>
                                                                </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Pasangan</label>
                                                                <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                                    <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pasangan_pekerjaan || '-'"></span>
                                                                </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pasangan_pekerjaan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Saudara Kandung --}}
                                                <template x-if="selectedForm.answers.saudara_1_nama">
                                                    <div class="space-y-4 pt-4 border-t border-[#F3EAFB]">
                                                        <span class="text-sm font-bold text-slate-700 uppercase tracking-wider block">Saudara Kandung</span>
                                                        <div class="space-y-4">
                                                            @foreach(range(1, 5) as $sdrNum)
                                                            <template x-if="selectedForm.answers.saudara_{{ $sdrNum }}_nama">
                                                                <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                                                                    <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Nama Saudara {{ $sdrNum }}</label>
    <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.saudara_{{ $sdrNum }}_nama"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['saudara_{{ $sdrNum }}_nama']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                                    <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Usia & Jenis Kelamin</label>
    <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="(selectedForm.answers.saudara_{{ $sdrNum }}_usia ? selectedForm.answers.saudara_{{ $sdrNum }}_usia + ' tahun' : '-') + ' | ' + (selectedForm.answers.saudara_{{ $sdrNum }}_jenis_kelamin || '-')"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers.jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
</div>
                                                                    <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan</label>
    <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.saudara_{{ $sdrNum }}_pendidikan || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers['saudara_{{ $sdrNum }}_pendidikan']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
</div>
                                                                    <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan</label>
    <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.saudara_{{ $sdrNum }}_pekerjaan || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['saudara_{{ $sdrNum }}_pekerjaan']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                                </div>
                                                            </template>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Anak --}}
                                                <template x-if="selectedForm.answers.anak_1_nama">
                                                    <div class="space-y-4 pt-4 border-t border-[#F3EAFB]">
                                                        <span class="text-sm font-bold text-slate-700 uppercase tracking-wider block">Anak</span>
                                                        <div class="space-y-4">
                                                            @foreach(range(1, 5) as $ankNum)
                                                            <template x-if="selectedForm.answers.anak_{{ $ankNum }}_nama">
                                                                <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                                                                    <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Nama Anak {{ $ankNum }}</label>
    <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.anak_{{ $ankNum }}_nama"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['anak_{{ $ankNum }}_nama']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                                    <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Usia & Jenis Kelamin</label>
    <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="(selectedForm.answers.anak_{{ $ankNum }}_usia ? selectedForm.answers.anak_{{ $ankNum }}_usia + ' tahun' : '-') + ' | ' + (selectedForm.answers.anak_{{ $ankNum }}_jenis_kelamin || '-')"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers.anak_jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
</div>
                                                                    <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan</label>
    <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.anak_{{ $ankNum }}_pendidikan || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers['anak_{{ $ankNum }}_pendidikan']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
</div>
                                                                    <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan</label>
    <div x-show="editingFormCard !== 'industri_keluarga'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.anak_{{ $ankNum }}_pekerjaan || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['anak_{{ $ankNum }}_pekerjaan']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                                </div>
                                                            </template>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </template>

                                            <div x-show="editingFormCard === 'industri_keluarga'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_keluarga')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- RIWAYAT PENDIDIKAN FORMAL --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Riwayat Pendidikan Formal</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_pendidikan'" 
                                                            @click="startEditFormCard('industri_pendidikan')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_pendidikan'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_pendidikan')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">

                                                {{-- Card Khusus IPK Terakhir --}}
                                                <template x-if="selectedForm.answers.ipk_terakhir">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">IPK Terakhir</label>
                                                        <div x-show="editingFormCard !== 'industri_pendidikan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ipk_terakhir"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_pendidikan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ipk_terakhir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                </template>

                                                @php
                                                $eduLabels = [
                                                    1 => 'Sekolah Dasar (SD)',
                                                    2 => 'Sekolah Menengah Pertama (SMP)',
                                                    3 => 'Sekolah Menengah Atas / Kejuruan (SMA/SMK)',
                                                    4 => 'Akademi / Diploma (D3/D4)',
                                                    5 => 'Universitas / Sarjana / Pascasarjana (S1/S2/S3)',
                                                ];
                                                @endphp

                                                @foreach(range(1, 5) as $eduNum)
                                                <template x-if="selectedForm.answers.nama_sekolah_{{ $eduNum }}">
                                                    <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Jenjang / Tingkat</label>
    <div class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block ">{{ $eduLabels[$eduNum] ?? "Jenjang $eduNum" }}</span>
    </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Nama Sekolah / Institusi</label>
    <div x-show="editingFormCard !== 'industri_pendidikan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_sekolah_{{ $eduNum }}"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pendidikan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['nama_sekolah_{{ $eduNum }}']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Periode Pendidikan</label>
    <div x-show="editingFormCard !== 'industri_pendidikan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="(selectedForm.answers.tahun_masuk_sekolah_{{ $eduNum }} || '-') + ' - ' + (selectedForm.answers.tahun_keluar_sekolah_{{ $eduNum }} || '-')"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pendidikan'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers[\'tahun_masuk_sekolah_{{ $eduNum }}\']" placeholder="Tahun Masuk" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="text" x-model="editFormAnswers[\'tahun_keluar_sekolah_{{ $eduNum }}\']" placeholder="Tahun Lulus" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Jurusan / Program Studi</label>
    <div x-show="editingFormCard !== 'industri_pendidikan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.keterangan_sekolah_{{ $eduNum }} || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pendidikan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['keterangan_sekolah_{{ $eduNum }}']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Kota</label>
    <div x-show="editingFormCard !== 'industri_pendidikan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.kota_sekolah_{{ $eduNum }} || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pendidikan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['kota_sekolah_{{ $eduNum }}']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                    </div>
                                                </template>
                                                @endforeach

                                                <template x-if="!selectedForm.answers.nama_sekolah_1 && !selectedForm.answers.nama_sekolah_2 && !selectedForm.answers.nama_sekolah_3 && !selectedForm.answers.nama_sekolah_4 && !selectedForm.answers.nama_sekolah_5">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Formal</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block ">-</span>
                                                        </div>
                                                    </div>
                                                </template>
                                            <div x-show="editingFormCard === 'industri_pendidikan'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_pendidikan')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- PENDIDIKAN NON FORMAL / KURSUS --}}
                                        <template x-if="selectedForm.answers.jenis_kursus_1 || selectedForm.answers.jenis_kursus_2 || selectedForm.answers.jenis_kursus_3">
                                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Pendidikan Non Formal / Kursus</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_nonformal'" 
                                                            @click="startEditFormCard('industri_nonformal')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_nonformal'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_nonformal')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                                <div class="p-5 sm:p-6 space-y-4">
                                                    @foreach(range(1, 3) as $krsNum)
                                                    <template x-if="selectedForm.answers.jenis_kursus_{{ $krsNum }}">
                                                        <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                                                            <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kursus / Pelatihan {{ $krsNum }}</label>
    <div x-show="editingFormCard !== 'industri_nonformal'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kursus_{{ $krsNum }}"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_nonformal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['jenis_kursus_{{ $krsNum }}']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                            <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Tempat Kursus</label>
    <div x-show="editingFormCard !== 'industri_nonformal'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.tempat_kursus_{{ $krsNum }} || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_nonformal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['tempat_kursus_{{ $krsNum }}']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                            <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Lama / Durasi Kursus</label>
    <div x-show="editingFormCard !== 'industri_nonformal'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.lama_kursus_{{ $krsNum }} || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_nonformal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['lama_kursus_{{ $krsNum }}']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                        </div>
                                                    </template>
                                                    @endforeach
                                                <div x-show="editingFormCard === 'industri_nonformal'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_nonformal')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                            </div>
                                        </template>

                                        {{-- RIWAYAT PEKERJAAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Riwayat Pekerjaan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_pekerjaan'" 
                                                            @click="startEditFormCard('industri_pekerjaan')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_pekerjaan'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_pekerjaan')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                {{-- Ekspektasi Gaji & Tunjangan --}}
                                                <template x-if="selectedForm.answers.ekspektasi_gaji_tunjangan">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Ekspektasi Gaji & Tunjangan</label>
                                                        <div x-show="editingFormCard !== 'industri_pekerjaan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ekspektasi_gaji_tunjangan"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'industri_pekerjaan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ekspektasi_gaji_tunjangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                </template>

                                                @foreach(range(1, 5) as $jobNum)
                                                <template x-if="selectedForm.answers.instansi_{{ $jobNum }}_nama">
                                                    <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Nama Perusahaan / Instansi</label>
    <div x-show="editingFormCard !== 'industri_pekerjaan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.instansi_{{ $jobNum }}_nama"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pekerjaan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['instansi_{{ $jobNum }}_nama']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Jabatan / Posisi</label>
    <div x-show="editingFormCard !== 'industri_pekerjaan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.instansi_{{ $jobNum }}_jabatan || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pekerjaan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['instansi_{{ $jobNum }}_jabatan']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Periode Masa Kerja</label>
    <div x-show="editingFormCard !== 'industri_pekerjaan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="(selectedForm.answers.instansi_{{ $jobNum }}_tahun_masuk || '-') + ' - ' + (selectedForm.answers.instansi_{{ $jobNum }}_tahun_keluar || '-')"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pekerjaan'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers['instansi_{{ $jobNum }}_tahun_masuk']" placeholder="Tahun Masuk" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="text" x-model="editFormAnswers['instansi_{{ $jobNum }}_tahun_keluar']" placeholder="Tahun Selesai" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Gaji Terakhir</label>
    <div x-show="editingFormCard !== 'industri_pekerjaan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.instansi_{{ $jobNum }}_gaji || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pekerjaan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['instansi_{{ $jobNum }}_gaji']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Alasan Berhenti</label>
    <div x-show="editingFormCard !== 'industri_pekerjaan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.instansi_{{ $jobNum }}_alasan_berhenti || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pekerjaan'" x-cloak>
                                                            <textarea x-model="editFormAnswers['instansi_{{ $jobNum }}_alasan_berhenti']" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
</div>
                                                        <template x-if="selectedForm.answers.instansi_{{ $jobNum }}_tugas">
                                                            <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Uraian Tugas / Tanggung Jawab</label>
    <div x-show="editingFormCard !== 'industri_pekerjaan'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] text-sm font-medium text-black leading-relaxed">
        <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.instansi_{{ $jobNum }}_tugas"></p>
    </div>
                                                        <div x-show="editingFormCard === 'industri_pekerjaan'" x-cloak>
                                                            <textarea x-model="editFormAnswers['instansi_{{ $jobNum }}_tugas']" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
</div>
                                                        </template>
                                                    </div>
                                                </template>
                                                @endforeach

                                                <template x-if="!selectedForm.answers.instansi_1_nama && !selectedForm.answers.instansi_2_nama && !selectedForm.answers.instansi_3_nama && !selectedForm.answers.instansi_4_nama && !selectedForm.answers.instansi_5_nama">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Pekerjaan</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block ">-</span>
                                                        </div>
                                                    </div>
                                                </template>
                                            <div x-show="editingFormCard === 'industri_pekerjaan'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_pekerjaan')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- PENGALAMAN ORGANISASI --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Pengalaman Organisasi</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_organisasi'" 
                                                            @click="startEditFormCard('industri_organisasi')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_organisasi'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_organisasi')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                @foreach(range(1, 3) as $orgNum)
                                                <template x-if="selectedForm.answers.organisasi_{{ $orgNum }}_nama">
                                                    <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Nama Organisasi {{ $orgNum }}</label>
    <div x-show="editingFormCard !== 'industri_organisasi'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.organisasi_{{ $orgNum }}_nama"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_organisasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['organisasi_{{ $orgNum }}_nama']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Jabatan</label>
    <div x-show="editingFormCard !== 'industri_organisasi'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.organisasi_{{ $orgNum }}_jabatan || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_organisasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['organisasi_{{ $orgNum }}_jabatan']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                        <div>
    <label class="block text-xs font-medium text-slate-500 mb-1">Periode / Lama Aktif</label>
    <div x-show="editingFormCard !== 'industri_organisasi'" class="px-3.5 py-2.5 bg-white rounded-xl border border-slate-200 min-h-[42px] flex items-center text-sm font-medium text-black">
        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.organisasi_{{ $orgNum }}_lama || '-'"></span>
    </div>
                                                        <div x-show="editingFormCard === 'industri_organisasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers['organisasi_{{ $orgNum }}_lama']" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
</div>
                                                    </div>
                                                </template>
                                                @endforeach
                                                <template x-if="!selectedForm.answers.organisasi_1_nama && !selectedForm.answers.organisasi_2_nama && !selectedForm.answers.organisasi_3_nama">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pengalaman Organisasi</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block ">-</span>
                                                        </div>
                                                    </div>
                                                </template>
                                            <div x-show="editingFormCard === 'industri_organisasi'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_organisasi')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- PRESTASI YANG DIRAIH --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Prestasi yang Diraih</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_prestasi'" 
                                                            @click="startEditFormCard('industri_prestasi')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_prestasi'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_prestasi')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Prestasi yang Pernah Diraih</label>
                                                    <div x-show="editingFormCard !== 'industri_prestasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                        <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.prestasi || '-'"></p>
                                                    </div>
                                                        <div x-show="editingFormCard === 'industri_prestasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.prestasi" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                </div>
                                            <div x-show="editingFormCard === 'industri_prestasi'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_prestasi')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- DESKRIPSI DIRI & RIWAYAT KESEHATAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Deskripsi Diri & Riwayat Kesehatan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'industri_deskripsi'" 
                                                            @click="startEditFormCard('industri_deskripsi')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'industri_deskripsi'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('industri_deskripsi')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Trauma</label>
                                                    <div x-show="editingFormCard !== 'industri_deskripsi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.riwayat_trauma || '-'"></span>
                                                    </div>
                                                        <div x-show="editingFormCard === 'industri_deskripsi'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_trauma" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Penyakit / Rawat Inap</label>
                                                    <div x-show="editingFormCard !== 'industri_deskripsi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.riwayat_penyakit_opname || '-'"></span>
                                                    </div>
                                                        <div x-show="editingFormCard === 'industri_deskripsi'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_penyakit_opname" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Konsultasi Psikolog</label>
                                                    <div x-show="editingFormCard !== 'industri_deskripsi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.konsultasi_psikolog || '-'"></span>
                                                    </div>
                                                        <div x-show="editingFormCard === 'industri_deskripsi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.konsultasi_psikolog" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Kelebihan Diri (Kekuatan Utama)</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                        <div class="text-black text-sm font-medium block  space-y-1">
                                                        <p x-text="'1. ' + (selectedForm.answers.kelebihan_1 || '-')"></p>
                                                        <p x-text="'2. ' + (selectedForm.answers.kelebihan_2 || '-')"></p>
                                                        <p x-text="'3. ' + (selectedForm.answers.kelebihan_3 || '-')"></p>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Kelemahan Diri (Area Pengembangan)</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                        <div class="text-black text-sm font-medium block  space-y-1">
                                                        <p x-text="'1. ' + (selectedForm.answers.kelemahan_1 || '-')"></p>
                                                        <p x-text="'2. ' + (selectedForm.answers.kelemahan_2 || '-')"></p>
                                                        <p x-text="'3. ' + (selectedForm.answers.kelemahan_3 || '-')"></p>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Hobi / Kegemaran</label>
                                                    <div x-show="editingFormCard !== 'industri_deskripsi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.hobi || '-'"></span>
                                                    </div>
                                                        <div x-show="editingFormCard === 'industri_deskripsi'" x-cloak>
                                                            <textarea x-model="editFormAnswers.hobi" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Deskripsi Diri Bebas</label>
                                                    <div x-show="editingFormCard !== 'industri_deskripsi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                        <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.deskripsi_diri_bebas || '-'"></p>
                                                    </div>
                                                        <div x-show="editingFormCard === 'industri_deskripsi'" x-cloak>
                                                            <textarea x-model="editFormAnswers.deskripsi_diri_bebas" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                </div>
                                            <div x-show="editingFormCard === 'industri_deskripsi'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('industri_deskripsi')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
{{-- VIEW DOSSIER: FORMULIR KONSELING (ANAK/DEWASA/PRA/NIKAH)  --}}

                                {{-- ========================================================= --}}
                                                                {{-- VIEW DOSSIER: FORMULIR KONSELING PRA NIKAH --}}
                                <template x-if="selectedForm.form_type === 'pra_nikah'">
                                    <div class="space-y-6">
                                        {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lembar Persetujuan (Informed Consent)</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Persetujuan</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex flex-col justify-center">
                                                        <div class="flex items-center gap-2 mt-1">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        <span class="text-black text-sm font-medium" x-text="selectedForm.consent_agreed ? 'Setuju' : (selectedForm.answers.consent_agree || 'Setuju')"></span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Waktu Pengisian Form</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between gap-2 text-sm font-medium text-black">
                                                        <div x-text="formatFormSubmissionDate(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                        <div x-text="formatFormSubmissionTime(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA DIRI ANDA (PENDAFTAR) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Diri Anda (Pendaftar)</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'pra_nikah_pendaftar'" 
                                                            @click="startEditFormCard('pra_nikah_pendaftar')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'pra_nikah_pendaftar'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('pra_nikah_pendaftar')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_lengkap || selectedForm.client_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_lengkap" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kelamin || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <select x-model="editFormAnswers.jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat, Tanggal Lahir</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="formatBirthPlaceDate(selectedForm.answers.tempat_lahir, selectedForm.answers.tanggal_lahir, selectedForm.answers.tempat_tanggal_lahir) || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers.tempat_lahir" placeholder="Kota Kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="date" x-model="editFormAnswers.tanggal_lahir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Urutan Kelahiran</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.urutan_kelahiran || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.urutan_kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Tempat Tinggal Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alamat || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon / WhatsApp</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone || selectedForm.client_phone || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email Aktif</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <textarea x-model="editFormAnswers.email" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama / Kepercayaan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <select x-model="editFormAnswers.agama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_bangsa || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_bangsa" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_terakhir || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Rencana Pernikahan Ini Adalah Pernikahan Ke-</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernikahan_ke || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pernikahan_ke" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jumlah Anak yang Dimiliki Saat Ini (Jika ada)</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jumlah_anak || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.jumlah_anak" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alasan Mengikuti Konseling Pra Nikah</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alasan_konseling || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alasan_konseling" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'pra_nikah_pendaftar'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('pra_nikah_pendaftar')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA DIRI CALON PASANGAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Diri Calon Pasangan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'pra_nikah_pasangan'" 
                                                            @click="startEditFormCard('pra_nikah_pasangan')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'pra_nikah_pasangan'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('pra_nikah_pasangan')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap Calon Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin Calon Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kelamin_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <select x-model="editFormAnswers.jenis_kelamin_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat, Tanggal Lahir Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="formatBirthPlaceDate(selectedForm.answers.tempat_lahir_pasangan, selectedForm.answers.tanggal_lahir_pasangan, selectedForm.answers.tempat_tanggal_lahir_pasangan) || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers.tempat_lahir_pasangan" placeholder="Kota Kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="date" x-model="editFormAnswers.tanggal_lahir_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Urutan Kelahiran Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.urutan_kelahiran_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.urutan_kelahiran_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Tempat Tinggal Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alamat_pasangan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat_pasangan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <textarea x-model="editFormAnswers.email_pasangan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <select x-model="editFormAnswers.agama_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_bangsa_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_bangsa_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_terakhir_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Bagi Pasangan, Pernikahan Ini Adalah Ke-</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernikahan_pasangan_ke || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pernikahan_pasangan_ke" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jumlah Anak yang Dimiliki Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jumlah_anak_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.jumlah_anak_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'pra_nikah_pasangan'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('pra_nikah_pasangan')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA RENCANA PERNIKAHAN & KONDISI HUBUNGAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Rencana Pernikahan & Kondisi Hubungan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'pra_nikah_rencana'" 
                                                            @click="startEditFormCard('pra_nikah_rencana')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'pra_nikah_rencana'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('pra_nikah_rencana')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tanggal Rencana Pernikahan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.tanggal_rencana_pernikahan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <input type="date" x-model="editFormAnswers.tanggal_rencana_pernikahan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Lama Waktu Berkenalan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.lama_berkenalan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.lama_berkenalan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Lama Waktu Berpacaran</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.lama_berpacaran || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.lama_berpacaran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Harapan terhadap Pernikahan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.harapan_pernikahan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <textarea x-model="editFormAnswers.harapan_pernikahan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Hal yang Ingin Ditingkatkan dalam Hubungan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.hal_ingin_ditingkatkan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.hal_ingin_ditingkatkan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kelebihan Peran Anda bagi Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.kelebihan_peran || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <textarea x-model="editFormAnswers.kelebihan_peran" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kekurangan Peran Anda bagi Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.kekurangan_peran || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <textarea x-model="editFormAnswers.kekurangan_peran" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Keluhan atau Konflik dalam Hubungan yang Sering Muncul</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.keluhan_hubungan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <textarea x-model="editFormAnswers.keluhan_hubungan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Trauma Masa Lalu</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_trauma || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_trauma" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Penyakit Medis</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_rencana'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_kesehatan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_kesehatan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'pra_nikah_rencana'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('pra_nikah_rencana')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: LAIN-LAIN & PREFERENSI LAYANAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lain-Lain & Preferensi Layanan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'pra_nikah_lain'" 
                                                            @click="startEditFormCard('pra_nikah_lain')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'pra_nikah_lain'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('pra_nikah_lain')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pernah Konseling Sebelumnya?</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernah_konseling || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_lain'" x-cloak>
                                                            <select x-model="editFormAnswers.pernah_konseling" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="Tidak, pertama kali">Tidak, pertama kali</option>
    <option value="Ya, pernah sebelumnya">Ya, pernah sebelumnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Konselor Lama</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_konselor || selectedForm.answers.nama_konselor_lama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_konselor" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kontak Darurat (Nama, Relasi, No. Telepon)</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.kontak_darurat || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.kontak_darurat" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Sumber Informasi Mengenai UC PSC</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.sumber_info || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.sumber_info" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Preferensi Proses Konseling</label>
                                                        <div x-show="editingFormCard !== 'pra_nikah_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.preferensi_konseling || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pra_nikah_lain'" x-cloak>
                                                            <select x-model="editFormAnswers.preferensi_konseling" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="Offline (Tatap Muka di UC PSC)">Offline (Tatap Muka di UC PSC)</option>
    <option value="Online (Melalui Zoom Meeting)">Online (Melalui Zoom Meeting)</option>
</select>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'pra_nikah_lain'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('pra_nikah_lain')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- VIEW DOSSIER: FORMULIR KONSELING DEWASA --}}
                                <template x-if="selectedForm.form_type === 'dewasa'">
                                    <div class="space-y-6">
                                        {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lembar Persetujuan (Informed Consent)</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Persetujuan</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex flex-col justify-center">
                                                        <div class="flex items-center gap-2 mt-1">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        <span class="text-black text-sm font-medium" x-text="selectedForm.consent_agreed ? 'Setuju' : (selectedForm.answers.consent_agree || 'Setuju')"></span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Waktu Pengisian Form</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between gap-2 text-sm font-medium text-black">
                                                        <div x-text="formatFormSubmissionDate(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                        <div x-text="formatFormSubmissionTime(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA IDENTITAS & DIRI KLIEN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Identitas & Diri Klien</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'dewasa_identitas'" 
                                                            @click="startEditFormCard('dewasa_identitas')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'dewasa_identitas'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('dewasa_identitas')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_lengkap || selectedForm.client_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_lengkap" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kelamin || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <select x-model="editFormAnswers.jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat, Tanggal Lahir</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="formatBirthPlaceDate(selectedForm.answers.tempat_lahir, selectedForm.answers.tanggal_lahir, selectedForm.answers.tempat_tanggal_lahir) || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers.tempat_lahir" placeholder="Kota Kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="date" x-model="editFormAnswers.tanggal_lahir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Urutan Kelahiran</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.urutan_kelahiran || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.urutan_kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Tempat Tinggal Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alamat || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon / WhatsApp</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone || selectedForm.client_phone || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <textarea x-model="editFormAnswers.email" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama / Kepercayaan</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <select x-model="editFormAnswers.agama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_bangsa || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_bangsa" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_terakhir || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Hobi / Minat Kegemaran</label>
                                                        <div x-show="editingFormCard !== 'dewasa_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.hobi || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.hobi" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'dewasa_identitas'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('dewasa_identitas')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: ALASAN KONSELING & KELUHAN UTAMA --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Alasan Konseling & Keluhan Utama</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'dewasa_keluhan'" 
                                                            @click="startEditFormCard('dewasa_keluhan')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'dewasa_keluhan'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('dewasa_keluhan')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alasan Ingin Melakukan Konseling</label>
                                                        <div x-show="editingFormCard !== 'dewasa_keluhan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alasan_konseling || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_keluhan'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alasan_konseling" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'dewasa_keluhan'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('dewasa_keluhan')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA KONDISI PSIKOLOGIS --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Kondisi Psikologis</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'dewasa_psikologis'" 
                                                            @click="startEditFormCard('dewasa_psikologis')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'dewasa_psikologis'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('dewasa_psikologis')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Gambaran Kondisi Saat Ini terhadap Permasalahan yang Dirasakan</label>
                                                        <div x-show="editingFormCard !== 'dewasa_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.kondisi_saat_ini || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.kondisi_saat_ini" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pikiran-Pikiran Negatif yang Sering Muncul Akhir-Akhir Ini</label>
                                                        <div x-show="editingFormCard !== 'dewasa_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.pikiran_negatif || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.pikiran_negatif" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Hal-Hal Dalam Hidup yang Ingin Ditingkatkan</label>
                                                        <div x-show="editingFormCard !== 'dewasa_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.hal_ingin_ditingkatkan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.hal_ingin_ditingkatkan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Karakter Kepribadian</label>
                                                        <div x-show="editingFormCard !== 'dewasa_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.karakter_kepribadian || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.karakter_kepribadian" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Ketakutan / Phobia yang Dimiliki</label>
                                                        <div x-show="editingFormCard !== 'dewasa_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.ketakutan_phobia || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.ketakutan_phobia" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Rata-Rata Jam Tidur per Malam</label>
                                                        <div x-show="editingFormCard !== 'dewasa_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jam_tidur || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.jam_tidur" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Hidup yang Menimbulkan Trauma</label>
                                                        <div x-show="editingFormCard !== 'dewasa_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_trauma || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_trauma" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Kesehatan</label>
                                                        <div x-show="editingFormCard !== 'dewasa_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_kesehatan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_kesehatan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'dewasa_psikologis'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('dewasa_psikologis')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: GAMBARAN RELASI INTERPERSONAL --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Gambaran Relasi Interpersonal</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'dewasa_relasi'" 
                                                            @click="startEditFormCard('dewasa_relasi')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'dewasa_relasi'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('dewasa_relasi')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi dengan Ayah</label>
                                                        <div x-show="editingFormCard !== 'dewasa_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_ayah || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi dengan Ibu</label>
                                                        <div x-show="editingFormCard !== 'dewasa_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_ibu || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi dengan Saudara</label>
                                                        <div x-show="editingFormCard !== 'dewasa_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_saudara || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_saudara" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi dengan Pasangan (Jika ada)</label>
                                                        <div x-show="editingFormCard !== 'dewasa_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_pasangan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi dengan Anak (Jika ada)</label>
                                                        <div x-show="editingFormCard !== 'dewasa_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_anak || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_anak" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'dewasa_relasi'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('dewasa_relasi')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: LAIN-LAIN & PREFERENSI LAYANAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lain-Lain & Preferensi Layanan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'dewasa_lain'" 
                                                            @click="startEditFormCard('dewasa_lain')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'dewasa_lain'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('dewasa_lain')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pernah Konseling Sebelumnya?</label>
                                                        <div x-show="editingFormCard !== 'dewasa_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernah_konseling || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_lain'" x-cloak>
                                                            <select x-model="editFormAnswers.pernah_konseling" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="Tidak, pertama kali">Tidak, pertama kali</option>
    <option value="Ya, pernah sebelumnya">Ya, pernah sebelumnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Konselor Lama</label>
                                                        <div x-show="editingFormCard !== 'dewasa_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_konselor || selectedForm.answers.nama_konselor_lama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_konselor" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kontak Darurat</label>
                                                        <div x-show="editingFormCard !== 'dewasa_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.kontak_darurat || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.kontak_darurat" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Sumber Mengetahui UC PSC</label>
                                                        <div x-show="editingFormCard !== 'dewasa_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.sumber_info || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.sumber_info" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Preferensi Proses Konseling</label>
                                                        <div x-show="editingFormCard !== 'dewasa_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.preferensi_konseling || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'dewasa_lain'" x-cloak>
                                                            <select x-model="editFormAnswers.preferensi_konseling" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="Offline (Tatap Muka di UC PSC)">Offline (Tatap Muka di UC PSC)</option>
    <option value="Online (Melalui Zoom Meeting)">Online (Melalui Zoom Meeting)</option>
</select>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'dewasa_lain'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('dewasa_lain')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- VIEW DOSSIER: FORMULIR KONSELING PERNIKAHAN --}}
                                <template x-if="selectedForm.form_type === 'pernikahan'">
                                    <div class="space-y-6">
                                        {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lembar Persetujuan (Informed Consent)</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Persetujuan</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex flex-col justify-center">
                                                        <div class="flex items-center gap-2 mt-1">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        <span class="text-black text-sm font-medium" x-text="selectedForm.consent_agreed ? 'Setuju' : (selectedForm.answers.consent_agree || 'Setuju')"></span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Waktu Pengisian Form</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between gap-2 text-sm font-medium text-black">
                                                        <div x-text="formatFormSubmissionDate(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                        <div x-text="formatFormSubmissionTime(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA DIRI ANDA (PENDAFTAR) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Diri Anda (Pendaftar)</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'pernikahan_pendaftar'" 
                                                            @click="startEditFormCard('pernikahan_pendaftar')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'pernikahan_pendaftar'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('pernikahan_pendaftar')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_lengkap || selectedForm.client_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_lengkap" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kelamin || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <select x-model="editFormAnswers.jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat, Tanggal Lahir</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="formatBirthPlaceDate(selectedForm.answers.tempat_lahir, selectedForm.answers.tanggal_lahir, selectedForm.answers.tempat_tanggal_lahir) || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers.tempat_lahir" placeholder="Kota Kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="date" x-model="editFormAnswers.tanggal_lahir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Urutan Kelahiran</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.urutan_kelahiran || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.urutan_kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Tempat Tinggal Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alamat || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon / WhatsApp</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone || selectedForm.client_phone || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <textarea x-model="editFormAnswers.email" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama / Kepercayaan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <select x-model="editFormAnswers.agama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_bangsa || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_bangsa" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_terakhir || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pernikahan Ini Adalah Ke-</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernikahan_ke || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pernikahan_ke" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jumlah Anak yang Dimiliki</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jumlah_anak || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.jumlah_anak" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alasan Mengikuti Konseling Pernikahan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pendaftar'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alasan_konseling || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alasan_konseling" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'pernikahan_pendaftar'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('pernikahan_pendaftar')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA DIRI PASANGAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Diri Pasangan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'pernikahan_pasangan'" 
                                                            @click="startEditFormCard('pernikahan_pasangan')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'pernikahan_pasangan'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('pernikahan_pasangan')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kelamin_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <select x-model="editFormAnswers.jenis_kelamin_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat, Tanggal Lahir Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="formatBirthPlaceDate(selectedForm.answers.tempat_lahir_pasangan, selectedForm.answers.tanggal_lahir_pasangan, selectedForm.answers.tempat_tanggal_lahir_pasangan) || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers.tempat_lahir_pasangan" placeholder="Kota Kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="date" x-model="editFormAnswers.tanggal_lahir_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Urutan Kelahiran Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.urutan_kelahiran_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.urutan_kelahiran_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Tempat Tinggal Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alamat_pasangan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat_pasangan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <textarea x-model="editFormAnswers.email_pasangan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <select x-model="editFormAnswers.agama_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_bangsa_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_bangsa_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_terakhir_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Pasangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan_pasangan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan_pasangan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Bagi Pasangan, Pernikahan Ini Adalah Ke-</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_pasangan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernikahan_pasangan_ke || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pernikahan_pasangan_ke" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'pernikahan_pasangan'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('pernikahan_pasangan')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA PERNIKAHAN & RIWAYAT HUBUNGAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Pernikahan & Riwayat Hubungan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'pernikahan_riwayat'" 
                                                            @click="startEditFormCard('pernikahan_riwayat')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'pernikahan_riwayat'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('pernikahan_riwayat')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat & Tanggal Pernikahan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_riwayat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.tempat_tanggal_pernikahan || selectedForm.answers.tanggal_pernikahan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak>
                                                            <input type="date" x-model="editFormAnswers.tempat_tanggal_pernikahan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Lama Pernikahan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_riwayat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.lama_pernikahan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.lama_pernikahan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jumlah Anak & Keterangan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_riwayat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jumlah_anak || selectedForm.answers.daftar_anak || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.jumlah_anak" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Harapan dari Konseling Pernikahan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_riwayat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.harapan_pernikahan || selectedForm.answers.harapan_konseling || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak>
                                                            <textarea x-model="editFormAnswers.harapan_pernikahan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Dinamika Hubungan & Keluhan Utama</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_riwayat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.keluhan_hubungan || selectedForm.answers.keluhan_utama || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak>
                                                            <textarea x-model="editFormAnswers.keluhan_hubungan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Upaya Penyelesaian yang Pernah Dilakukan</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_riwayat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.upaya_penyelesaian || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.upaya_penyelesaian" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Trauma Masa Lalu</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_riwayat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_trauma || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_trauma" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Penyakit Medis</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_riwayat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_kesehatan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_kesehatan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'pernikahan_riwayat'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('pernikahan_riwayat')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: LAIN-LAIN & PREFERENSI LAYANAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lain-Lain & Preferensi Layanan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'pernikahan_lain'" 
                                                            @click="startEditFormCard('pernikahan_lain')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'pernikahan_lain'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('pernikahan_lain')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pernah Konseling Sebelumnya?</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernah_konseling || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_lain'" x-cloak>
                                                            <select x-model="editFormAnswers.pernah_konseling" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="Tidak, pertama kali">Tidak, pertama kali</option>
    <option value="Ya, pernah sebelumnya">Ya, pernah sebelumnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Konselor Lama</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_konselor || selectedForm.answers.nama_konselor_lama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_konselor" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kontak Darurat</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.kontak_darurat || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.kontak_darurat" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Sumber Mengetahui UC PSC</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.sumber_info || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.sumber_info" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Preferensi Proses Konseling</label>
                                                        <div x-show="editingFormCard !== 'pernikahan_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.preferensi_konseling || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'pernikahan_lain'" x-cloak>
                                                            <select x-model="editFormAnswers.preferensi_konseling" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="Offline (Tatap Muka di UC PSC)">Offline (Tatap Muka di UC PSC)</option>
    <option value="Online (Melalui Zoom Meeting)">Online (Melalui Zoom Meeting)</option>
</select>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'pernikahan_lain'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('pernikahan_lain')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- VIEW DOSSIER: FORMULIR KONSELING ANAK --}}
                                <template x-if="selectedForm.form_type === 'anak'">
                                    <div class="space-y-6">
                                        {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lembar Persetujuan (Informed Consent)</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Persetujuan</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex flex-col justify-center">
                                                        <div class="flex items-center gap-2 mt-1">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        <span class="text-black text-sm font-medium" x-text="selectedForm.consent_agreed ? 'Setuju' : (selectedForm.answers.consent_agree || 'Setuju')"></span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Waktu Pengisian Form</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between gap-2 text-sm font-medium text-black">
                                                        <div x-text="formatFormSubmissionDate(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                        <div x-text="formatFormSubmissionTime(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA DIRI ANAK --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Diri Anak</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'anak_datadiri'" 
                                                            @click="startEditFormCard('anak_datadiri')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'anak_datadiri'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('anak_datadiri')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap Anak</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_lengkap || selectedForm.client_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_lengkap" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin Anak</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kelamin || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <select x-model="editFormAnswers.anak_jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat, Tanggal Lahir</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="formatBirthPlaceDate(selectedForm.answers.tempat_lahir, selectedForm.answers.tanggal_lahir, selectedForm.answers.tempat_tanggal_lahir) || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers.tempat_lahir" placeholder="Kota Kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="date" x-model="editFormAnswers.tanggal_lahir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Urutan Kelahiran</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.urutan_kelahiran || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.urutan_kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Tempat Tinggal Anak</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alamat || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon / WhatsApp</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone || selectedForm.client_phone || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <textarea x-model="editFormAnswers.email" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama / Kepercayaan</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <select x-model="editFormAnswers.agama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_bangsa || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_bangsa" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan / Kelas Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_terakhir || selectedForm.answers.sekolah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Hobi / Minat Kegemaran Anak</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.hobi || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.hobi" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Status Pernikahan Orang Tua</label>
                                                        <div x-show="editingFormCard !== 'anak_datadiri'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.status_pernikahan_ortu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_datadiri'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.status_pernikahan_ortu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'anak_datadiri'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('anak_datadiri')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA PROFIL ORANG TUA & KELUARGA --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Profil Orang Tua & Keluarga</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'anak_orangtua'" 
                                                            @click="startEditFormCard('anak_orangtua')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'anak_orangtua'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('anak_orangtua')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Ayah</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Usia Ayah</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.usia_ayah ? selectedForm.answers.usia_ayah + ' tahun' : '-' || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.usia_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama Ayah</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <select x-model="editFormAnswers.agama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa Ayah</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir Ayah</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Ayah</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon Ayah</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Ibu</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Usia Ibu</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.usia_ibu ? selectedForm.answers.usia_ibu + ' tahun' : '-' || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.usia_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama Ibu</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <select x-model="editFormAnswers.agama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa Ibu</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir Ibu</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Ibu</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon Ibu</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Orang Tua</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alamat_ortu || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat_ortu" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Susunan Saudara Kandung</label>
                                                        <div x-show="editingFormCard !== 'anak_orangtua'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.susunan_saudara || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_orangtua'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.susunan_saudara" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'anak_orangtua'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('anak_orangtua')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: ALASAN KONSELING & KELUHAN UTAMA --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Alasan Konseling & Keluhan Utama</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'anak_keluhan'" 
                                                            @click="startEditFormCard('anak_keluhan')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'anak_keluhan'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('anak_keluhan')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Keluhan Utama / Alasan Membawa Anak Konseling</label>
                                                        <div x-show="editingFormCard !== 'anak_keluhan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alasan_konseling || selectedForm.answers.keluhan_utama || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_keluhan'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alasan_konseling" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Sejak Kapan Keluhan Ini Muncul (Onset)</label>
                                                        <div x-show="editingFormCard !== 'anak_keluhan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.onset_keluhan || selectedForm.answers.sejak_kapan_muncul || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_keluhan'" x-cloak>
                                                            <textarea x-model="editFormAnswers.onset_keluhan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Upaya yang Pernah Dilakukan Orang Tua</label>
                                                        <div x-show="editingFormCard !== 'anak_keluhan'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.upaya_dilakukan || selectedForm.answers.upaya_orang_tua || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_keluhan'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.upaya_dilakukan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'anak_keluhan'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('anak_keluhan')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: KONDISI PSIKOLOGIS & PERKEMBANGAN ANAK --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Kondisi Psikologis & Perkembangan Anak</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'anak_psikologis'" 
                                                            @click="startEditFormCard('anak_psikologis')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'anak_psikologis'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('anak_psikologis')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Gambaran Kondisi Anak Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'anak_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.kondisi_saat_ini || selectedForm.answers.kondisi_anak || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.kondisi_saat_ini" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Karakter & Ciri Kepribadian Anak</label>
                                                        <div x-show="editingFormCard !== 'anak_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.karakter_kepribadian || selectedForm.answers.karakter_anak || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.karakter_kepribadian" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Ketakutan / Fobia yang Dimiliki Anak</label>
                                                        <div x-show="editingFormCard !== 'anak_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.ketakutan_phobia || selectedForm.answers.ketakutan_anak || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.ketakutan_phobia" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Kehamilan & Kelahiran</label>
                                                        <div x-show="editingFormCard !== 'anak_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_kelahiran || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_kelahiran" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Tumbuh Kembang</label>
                                                        <div x-show="editingFormCard !== 'anak_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_tumbuh_kembang || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_tumbuh_kembang" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Hidup yang Menimbulkan Trauma pada Anak</label>
                                                        <div x-show="editingFormCard !== 'anak_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_trauma || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_trauma" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Riwayat Penyakit Medis Anak</label>
                                                        <div x-show="editingFormCard !== 'anak_psikologis'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.riwayat_kesehatan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_psikologis'" x-cloak>
                                                            <textarea x-model="editFormAnswers.riwayat_kesehatan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'anak_psikologis'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('anak_psikologis')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: GAMBARAN RELASI ANAK --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Gambaran Relasi Anak</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'anak_relasi'" 
                                                            @click="startEditFormCard('anak_relasi')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'anak_relasi'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('anak_relasi')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi Anak dengan Ayah</label>
                                                        <div x-show="editingFormCard !== 'anak_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_ayah || selectedForm.answers.relasi_dengan_ayah || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi Anak dengan Ibu</label>
                                                        <div x-show="editingFormCard !== 'anak_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_ibu || selectedForm.answers.relasi_dengan_ibu || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi Anak dengan Saudara</label>
                                                        <div x-show="editingFormCard !== 'anak_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_saudara || selectedForm.answers.relasi_dengan_saudara || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_saudara" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Relasi Anak dengan Teman Sebaya / Sekolah</label>
                                                        <div x-show="editingFormCard !== 'anak_relasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.relasi_teman || selectedForm.answers.relasi_dengan_teman || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_relasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.relasi_teman" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'anak_relasi'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('anak_relasi')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: LAIN-LAIN & PREFERENSI LAYANAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lain-Lain & Preferensi Layanan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'anak_lain'" 
                                                            @click="startEditFormCard('anak_lain')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'anak_lain'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('anak_lain')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pernah Konseling Sebelumnya?</label>
                                                        <div x-show="editingFormCard !== 'anak_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernah_konseling || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_lain'" x-cloak>
                                                            <select x-model="editFormAnswers.pernah_konseling" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="Tidak, pertama kali">Tidak, pertama kali</option>
    <option value="Ya, pernah sebelumnya">Ya, pernah sebelumnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kontak Darurat (Nama, Relasi, No. Telepon)</label>
                                                        <div x-show="editingFormCard !== 'anak_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.kontak_darurat || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.kontak_darurat" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Sumber Mengetahui UC PSC</label>
                                                        <div x-show="editingFormCard !== 'anak_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.sumber_info || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_lain'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.sumber_info" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Preferensi Proses Konseling</label>
                                                        <div x-show="editingFormCard !== 'anak_lain'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.preferensi_konseling || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'anak_lain'" x-cloak>
                                                            <select x-model="editFormAnswers.preferensi_konseling" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="Offline (Tatap Muka di UC PSC)">Offline (Tatap Muka di UC PSC)</option>
    <option value="Online (Melalui Zoom Meeting)">Online (Melalui Zoom Meeting)</option>
</select>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'anak_lain'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('anak_lain')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- VIEW DOSSIER: FORMULIR NON-INDUSTRI --}}
                                <template x-if="selectedForm.form_type === 'non_industri'">
                                    <div class="space-y-6">
                                        {{-- KARTU: LEMBAR PERNYATAAN KEJUJURAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lembar Pernyataan Kejujuran</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Persetujuan</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex flex-col justify-center">
                                                        <div class="flex items-center gap-2 mt-1">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        <span class="text-black text-sm font-medium" x-text="selectedForm.consent_agreed ? 'Setuju' : (selectedForm.answers.consent_agree || 'Setuju')"></span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">"Semua keterangan yang saya berikan dalam Formulir Riwayat Hidup ini, saya buat dengan jujur dan sungguh-sungguh."</p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Waktu Pengisian Form</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between gap-2 text-sm font-medium text-black">
                                                        <div x-text="formatFormSubmissionDate(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                        <div x-text="formatFormSubmissionTime(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA IDENTITAS & DIRI KLIEN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Identitas & Diri Klien</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'non_industri_identitas'" 
                                                            @click="startEditFormCard('non_industri_identitas')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'non_industri_identitas'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('non_industri_identitas')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_lengkap || selectedForm.client_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_lengkap" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis Kelamin</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.jenis_kelamin || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <select x-model="editFormAnswers.jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Jenis Kelamin --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tempat, Tanggal Lahir</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="formatBirthPlaceDate(selectedForm.answers.tempat_lahir, selectedForm.answers.tanggal_lahir, selectedForm.answers.tempat_tanggal_lahir) || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <div class="grid grid-cols-2 gap-2">
    <input type="text" x-model="editFormAnswers.tempat_lahir" placeholder="Kota Kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <input type="date" x-model="editFormAnswers.tanggal_lahir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
</div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Urutan Kelahiran</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.urutan_kelahiran || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.urutan_kelahiran" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Tempat Tinggal Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alamat || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <textarea x-model="editFormAnswers.alamat" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">No. Telepon / WhatsApp</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone || selectedForm.client_phone || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Alamat Email</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <textarea x-model="editFormAnswers.email" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Agama / Kepercayaan</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.agama || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <select x-model="editFormAnswers.agama" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Agama --</option>
    <option value="Islam">Islam</option>
    <option value="Kristen">Kristen</option>
    <option value="Katolik">Katolik</option>
    <option value="Hindu">Hindu</option>
    <option value="Buddha">Buddha</option>
    <option value="Konghucu">Konghucu</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Suku Bangsa</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.suku_bangsa || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.suku_bangsa" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Terakhir</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_terakhir || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_terakhir" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Saat Ini</label>
                                                        <div x-show="editingFormCard !== 'non_industri_identitas'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'non_industri_identitas'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('non_industri_identitas')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: DATA KELUARGA & ORANG TUA --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Keluarga & Orang Tua</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'non_industri_keluarga'" 
                                                            @click="startEditFormCard('non_industri_keluarga')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'non_industri_keluarga'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('non_industri_keluarga')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Ayah</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Usia Ayah</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.usia_ayah ? selectedForm.answers.usia_ayah + ' tahun' : '-' || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.usia_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Ayah</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Ayah</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan_ayah || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan_ayah" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Nama Ibu</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.nama_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.nama_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Usia Ibu</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.usia_ibu ? selectedForm.answers.usia_ibu + ' tahun' : '-' || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.usia_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pendidikan Ibu</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pendidikan_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <select x-model="editFormAnswers.pendidikan_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
    <option value="">-- Pilih Pendidikan --</option>
    <option value="TK">TK</option>
    <option value="SD">SD</option>
    <option value="SMP">SMP</option>
    <option value="SMA / SMK">SMA / SMK</option>
    <option value="Diploma (D3/D4)">Diploma (D3/D4)</option>
    <option value="Sarjana (S1)">Sarjana (S1)</option>
    <option value="Magister (S2)">Magister (S2)</option>
    <option value="Doktor (S3)">Doktor (S3)</option>
    <option value="Lainnya">Lainnya</option>
</select>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pekerjaan Ibu</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pekerjaan_ibu || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pekerjaan_ibu" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Susunan Saudara Kandung</label>
                                                        <div x-show="editingFormCard !== 'non_industri_keluarga'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.susunan_saudara || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.susunan_saudara" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'non_industri_keluarga'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('non_industri_keluarga')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: MINAT, BAKAT, CITA-CITA & TUJUAN ASESMEN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Minat, Bakat, Cita-Cita & Tujuan Asesmen</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'non_industri_minat'" 
                                                            @click="startEditFormCard('non_industri_minat')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'non_industri_minat'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('non_industri_minat')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Cita-Cita / Peminatan yang Diinginkan</label>
                                                        <div x-show="editingFormCard !== 'non_industri_minat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.cita_cita || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_minat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.cita_cita" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Rencana Karir / Studi</label>
                                                        <div x-show="editingFormCard !== 'non_industri_minat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.rencana_cita_cita || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_minat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.rencana_cita_cita" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Usaha yang Telah Dilakukan</label>
                                                        <div x-show="editingFormCard !== 'non_industri_minat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.usaha_cita_cita || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_minat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.usaha_cita_cita" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tujuan Pemeriksaan Psikologis</label>
                                                        <div x-show="editingFormCard !== 'non_industri_minat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.tujuan_pemeriksaan || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_minat'" x-cloak>
                                                            <textarea x-model="editFormAnswers.tujuan_pemeriksaan" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Mata Pelajaran / Kuliah yang Disukai</label>
                                                        <div x-show="editingFormCard !== 'non_industri_minat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.mata_pelajaran_disukai || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_minat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.mata_pelajaran_disukai" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Mata Pelajaran / Kuliah yang Kurang Disukai</label>
                                                        <div x-show="editingFormCard !== 'non_industri_minat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.mata_pelajaran_tidak_disukai || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_minat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.mata_pelajaran_tidak_disukai" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kegiatan di Waktu Luang / Hobi</label>
                                                        <div x-show="editingFormCard !== 'non_industri_minat'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.kegiatan_luang || selectedForm.answers.hobi || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_minat'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.kegiatan_luang" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'non_industri_minat'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('non_industri_minat')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: INFORMASI TAMBAHAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Informasi Tambahan</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'non_industri_informasi'" 
                                                            @click="startEditFormCard('non_industri_informasi')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'non_industri_informasi'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('non_industri_informasi')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Pernah Mengikuti Psikotes Sebelumnya?</label>
                                                        <div x-show="editingFormCard !== 'non_industri_informasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.pernah_psikotes || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_informasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.pernah_psikotes" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kapan Psikotes Terdahulu Dilakukan</label>
                                                        <div x-show="editingFormCard !== 'non_industri_informasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.waktu_psikotes || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_informasi'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.waktu_psikotes" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Tujuan Pemeriksaan Psikotes Terdahulu</label>
                                                        <div x-show="editingFormCard !== 'non_industri_informasi'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.tujuan_psikotes_lama || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'non_industri_informasi'" x-cloak>
                                                            <textarea x-model="editFormAnswers.tujuan_psikotes_lama" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white"></textarea>
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'non_industri_informasi'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('non_industri_informasi')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- VIEW DOSSIER: BIOGRAPHY FORM (ENGLISH) --}}
                                <template x-if="selectedForm.form_type === 'biography_en'">
                                    <div class="space-y-6">
                                        {{-- KARTU: DECLARATION OF AUTHENTICITY --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Declaration of Authenticity</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Declaration Status</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        <div class="flex items-center gap-2 ">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        <span class="text-black text-sm font-medium" x-text="selectedForm.consent_agreed ? 'Agreed' : (selectedForm.answers.consent_agree || 'Agreed')"></span>
                                                    </div>
                                                    <p class="text-sm text-slate-500  leading-relaxed">"I declare that all information provided in this Biography Form is true, accurate, and filled with full honesty."</p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Submission Timestamp</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        <div class=" flex items-center justify-between gap-2">
                                                        <div class="text-black text-sm font-medium" x-text="formatFormSubmissionDate(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                        <div class="text-black text-sm font-medium" x-text="formatFormSubmissionTime(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                    </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: PERSONAL INFORMATION --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Personal Information</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'biography_en_personal'" 
                                                            @click="startEditFormCard('biography_en_personal')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'biography_en_personal'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('biography_en_personal')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Full Name</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.full_name || selectedForm.client_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.full_name" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Gender</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.gender || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.gender" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Place & Date of Birth</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.birth_place_date || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.birth_place_date" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Order of Birth</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.birth_order || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.birth_order" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">City of Origin</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.city_of_origin || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.city_of_origin" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Current Address</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.current_address || selectedForm.answers.alamat || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.current_address" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Phone / WhatsApp</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.phone || selectedForm.client_phone || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Active Email Address</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.email || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.email" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Religion / Belief</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.religion || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.religion" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Ethnicity</label>
                                                        <div x-show="editingFormCard !== 'biography_en_personal'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.ethnicity || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_personal'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.ethnicity" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'biography_en_personal'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('biography_en_personal')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: FAMILY BACKGROUND --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Family Background</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'biography_en_family'" 
                                                            @click="startEditFormCard('biography_en_family')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'biography_en_family'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('biography_en_family')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Father Name</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.father_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.father_name" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Father Age</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.father_age ? selectedForm.answers.father_age + ' years old' : '-' || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.father_age" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Father Education</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.father_education || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.father_education" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Father Occupation</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.father_occupation || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.father_occupation" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Mother Name</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.mother_name || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.mother_name" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Mother Age</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.mother_age ? selectedForm.answers.mother_age + ' years old' : '-' || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.mother_age" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Mother Education</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.mother_education || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.mother_education" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Mother Occupation</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.mother_occupation || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.mother_occupation" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Siblings</label>
                                                        <div x-show="editingFormCard !== 'biography_en_family'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.siblings || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_family'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.siblings" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'biography_en_family'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('biography_en_family')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: CAREER ASPIRATIONS & ASSESSMENT PURPOSE --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Career Aspirations & Assessment Purpose</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'biography_en_career'" 
                                                            @click="startEditFormCard('biography_en_career')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'biography_en_career'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('biography_en_career')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Career Goal / Dream Job</label>
                                                        <div x-show="editingFormCard !== 'biography_en_career'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.dream_job || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_career'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.dream_job" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Future Aspirations</label>
                                                        <div x-show="editingFormCard !== 'biography_en_career'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.dream_job_future || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_career'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.dream_job_future" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Purpose of Psychological Assessment</label>
                                                        <div x-show="editingFormCard !== 'biography_en_career'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.purpose_of_assessment || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_career'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.purpose_of_assessment" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Hobbies & Leisure Activities</label>
                                                        <div x-show="editingFormCard !== 'biography_en_career'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.hobbies || selectedForm.answers.leisure_activities || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_career'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.hobbies" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'biography_en_career'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('biography_en_career')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- KARTU: ADDITIONAL INFORMATION --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Additional Information</span>
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <button type="button" 
                                                            x-show="editingFormCard !== 'biography_en_additional'" 
                                                            @click="startEditFormCard('biography_en_additional')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold backdrop-blur-md transition shadow-xs cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>Ubah</span>
                                                    </button>
                                                    <template x-if="editingFormCard === 'biography_en_additional'">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button" 
                                                                    @click="cancelEditFormCard()"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep text-xs font-semibold border border-[#D9C2F0] transition shadow-xs cursor-pointer">
                                                                Batal
                                                            </button>
                                                            <button type="button" 
                                                                    @click="saveFormCard('biography_en_additional')"
                                                                    :disabled="isSavingFormCard"
                                                                    class="px-3 py-1.5 rounded-lg bg-[#4A2380] hover:bg-[#3D1D69] text-white text-xs font-semibold border border-purple-300/40 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                                <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan'"></span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Prior Psychological Test Experience</label>
                                                        <div x-show="editingFormCard !== 'biography_en_additional'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.prior_test || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_additional'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.prior_test" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">When the Prior Test was Administered</label>
                                                        <div x-show="editingFormCard !== 'biography_en_additional'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                            <span class="text-black text-sm font-medium block " x-text="selectedForm.answers.prior_test_date || '-'"></span>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_additional'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.prior_test_date" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Medical / Health History</label>
                                                        <div x-show="editingFormCard !== 'biography_en_additional'" class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.health_history || '-'"></p>
                                                        </div>
                                                        <div x-show="editingFormCard === 'biography_en_additional'" x-cloak>
                                                            <input type="text" x-model="editFormAnswers.health_history" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs bg-white">
                                                        </div>
                                                    </div>
                                            <div x-show="editingFormCard === 'biography_en_additional'" x-cloak class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                                                    <button type="button" 
                                                            @click="cancelEditFormCard()"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-xs transition cursor-pointer shadow-xs">
                                                        Batal
                                                    </button>
                                                    <button type="button" 
                                                            @click="saveFormCard('biography_en_additional')"
                                                            :disabled="isSavingFormCard"
                                                            class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-xs transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                                                        <span x-text="isSavingFormCard ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- VIEW DOSSIER: FALLBACK GENERIC --}}
                                <template x-if="!['industri', 'pra_nikah', 'dewasa', 'pernikahan', 'anak', 'non_industri', 'biography_en'].includes(selectedForm.form_type)">
                                    <div class="space-y-6">
                                        {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lembar Persetujuan (Informed Consent)</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Persetujuan</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex flex-col justify-center">
                                                        <div class="flex items-center gap-2 mt-1">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                        <span class="text-black text-sm font-medium" x-text="selectedForm.consent_agreed ? 'Setuju' : (selectedForm.answers.consent_agree || 'Setuju')"></span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Waktu Pengisian Form</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between gap-2 text-sm font-medium text-black">
                                                        <div x-text="formatFormSubmissionDate(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                        <div x-text="formatFormSubmissionTime(selectedForm.created_at || selectedForm.consent_agreed_at)"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Formulir</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                <template x-for="(val, key) in selectedForm.answers" :key="key">
                                                    <div x-show="typeof val === 'string' && val.trim() !== '' && key !== 'consent_agree'">
                                                        <label class="block text-xs font-medium text-slate-500 mb-1" x-text="key.replace(/_/g, ' ').toUpperCase() + ':'"></label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] text-sm font-medium text-black leading-relaxed">
                                                            <p class="text-black text-sm font-medium block  leading-relaxed whitespace-pre-wrap" x-text="val"></p>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ========================================================================= --}}

    {{-- MODAL PENUGASAN STAF                                                      --}}

    {{-- ========================================================================= --}}

    @if(auth()->user()->isAdmin())

    {{-- 1. Modal Tugaskan Staff Baru --}}
    <div x-cloak x-show="assignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div x-show="assignModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-xs"></div>

        <div x-show="assignModalOpen" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-semibold text-black">Tugaskan Staff Pengelola</h3>
            <form action="{{ route('assignments.assign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Pilih Staff <span class="text-red-500">*</span></label>
                    <select name="staff_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-xs font-medium text-black focus:ring-2 focus:ring-purple-deep bg-white">
                        <option value="">-- Pilih Staff --</option>
                        @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 border border-slate-200">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-purple-deep text-white shadow-xs flex items-center gap-1.5"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg><span>Simpan</span></button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. Modal Alihkan Staff --}}
    <div x-cloak x-show="reassignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div x-show="reassignModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-xs"></div>

        <div x-show="reassignModalOpen" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-semibold text-black">Alihkan Staff Pengelola</h3>
            <form action="{{ route('assignments.reassign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Pilih Staff Pengganti <span class="text-red-500">*</span></label>
                    <select name="staff_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-xs font-medium text-black focus:ring-2 focus:ring-purple-deep bg-white">
                        <option value="">-- Pilih Staff Baru --</option>
                        @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}" {{ $client->assigned_staff_id === $staff->id ? 'disabled' : '' }}>
                            {{ $staff->name }} ({{ $staff->email }}) {{ $client->assigned_staff_id === $staff->id ? '(Saat ini)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-between gap-2 pt-2">
                    <button type="button" @click="reassignModalOpen = false; unassignModalOpen = true" class="text-xs font-semibold text-red-600 hover:underline">Lepaskan Staff</button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="reassignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 border border-slate-200">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-purple-deep text-white shadow-xs flex items-center gap-1.5"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg><span>Simpan</span></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. Modal Lepaskan Staff --}}
    <div x-cloak x-show="unassignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div x-show="unassignModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-xs"></div>

        <div x-show="unassignModalOpen" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-semibold text-red-600">Lepaskan Penugasan Staff</h3>
            <p class="text-xs font-medium text-gray-500">Klien <strong class="text-black font-semibold">{{ $client->name }}</strong> akan dikembalikan ke status belum ditugaskan.</p>
            <form action="{{ route('assignments.unassign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="unassignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 border border-slate-200">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-red-600 text-white shadow-xs">Ya, Lepaskan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. Modal Hapus Klien --}}
    <div x-cloak x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div x-show="deleteModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-xs"></div>

        <div x-show="deleteModalOpen" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-semibold text-red-600">Hapus Data Klien?</h3>
            <p class="text-xs font-medium text-gray-500 leading-relaxed">
                Apakah Anda yakin ingin menghapus data klien <strong class="text-black font-semibold">{{ $client->name }}</strong> beserta seluruh riwayat formulir dan reservasi terkait? Tindakan ini permanen.
            </p>
            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="flex items-center justify-end gap-2 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-500 border border-slate-200">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-red-600 text-white shadow-xs">Ya, Hapus Klien</button>
            </form>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}

    {{-- ===== MODAL KONFIRMASI: PERUBAHAN BELUM DISIMPAN (FROSTED GLASSMORPHISM) ===== --}}
    <template x-teleport="body">
    <div x-cloak x-show="showUnsavedChangesModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-unsaved-title" role="dialog" aria-modal="true">

        {{-- Backdrop with Blur (Tanpa @click penutup sesuai aturan modal) --}}

        <div x-show="showUnsavedChangesModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md transition-opacity"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center">

            <div x-show="showUnsavedChangesModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-[2rem] bg-white/80 backdrop-blur-2xl text-center shadow-[0_30px_70px_rgba(74,35,128,0.18),inset_0_1px_2px_rgba(255,255,255,0.95)] transition-all sm:my-8 sm:w-full sm:max-w-md border border-white/80 p-7 sm:p-9">

                {{-- Specular Top Highlight Edge --}}
                <div class="absolute top-0 inset-x-10 h-[1.5px] bg-gradient-to-r from-transparent via-white to-transparent pointer-events-none"></div>

                {{-- Centered Top Icon Badge --}}
                <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center mb-5 backdrop-blur-md shadow-xs relative z-10">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>

                {{-- Title & Description --}}
                <h3 class="text-xl font-extrabold text-[#260E45] tracking-tight mb-2 relative z-10" id="modal-unsaved-title">
                    Tinggalkan Halaman?
                </h3>
                <p class="text-xs sm:text-sm text-[#6B5B85] leading-relaxed max-w-xs mx-auto mb-7 relative z-10">
                    Data formulir yang telah Anda isi belum disimpan. Jika Anda keluar sekarang, draf pengisian akan hilang.
                </p>

                {{-- Symmetrical Action Buttons (Grid 2 Equal Columns - Identik dengan Dashboard Guard) --}}
                <div class="grid grid-cols-2 gap-3.5 pt-1 relative z-10">
                    <button 
                        type="button" 
                        @click="showUnsavedChangesModal = false"
                        class="w-full py-3 rounded-2xl text-xs sm:text-sm font-bold text-purple-deep hover:text-[#260E45] bg-white/70 hover:bg-white border border-[#D9C2F0]/80 hover:border-purple-300 shadow-xs hover:shadow-sm active:scale-[0.98] transition-all duration-200 cursor-pointer flex items-center justify-center"
                    >
                        Lanjut Mengisi
                    </button>
                    <button 
                        type="button"
                        @click="confirmDiscard()" 
                        class="w-full py-3 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-white rounded-2xl text-xs sm:text-sm font-bold transition-all duration-200 shadow-[0_8px_20px_rgba(245,158,11,0.3),inset_0_1px_1px_rgba(255,255,255,0.4)] hover:shadow-[0_10px_25px_rgba(245,158,11,0.45)] active:scale-[0.98] cursor-pointer flex items-center justify-center border border-white/20 relative overflow-hidden"
                    >
                        <span class="relative z-10">Ya, Tinggalkan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>
    <script>
    // Expose dirty checker to global unsaved-guard
    window.isClientProfileDirty = function() {
        const rootEl = document.getElementById('client-profile-detail-root') || document.querySelector('[x-data]');
        if (window.clientProfileDetailComponent) {
            return window.clientProfileDetailComponent.isFormDirty();
        }
        if (rootEl && window.Alpine) {
            const data = Alpine.$data(rootEl);
            if (data && data.editMode && typeof data.isFormDirty === 'function') {
                return data.isFormDirty();
            }
        }
        if (window.clientProfileInitialData && window.clientProfileCurrentData) {
            return JSON.stringify(window.clientProfileInitialData) !== JSON.stringify(window.clientProfileCurrentData);
        }
        return false;
    };
    // Intercept navigation to other pages if form is dirty
    document.addEventListener('click', function(e) {
        if (window.isSubmittingForm) return;
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.getAttribute('target') === '_blank') return;
        if (window.isClientProfileDirty()) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const comp = window.clientProfileDetailComponent;
            if (comp) {
                comp.pendingActionType = 'navigate';
                comp.pendingTarget = href;
                comp.showUnsavedChangesModal = true;
            } else {
                window.dispatchEvent(new CustomEvent('open-unsaved-modal', {
                    detail: { url: href, isBrowserBack: false }
                }));
            }
        }
    }, true);
    // Browser level reload/tab close protection
    window.addEventListener('beforeunload', function(e) {
        if (window.isSubmittingForm) return;
        if (typeof window.isClientProfileDirty === 'function' && window.isClientProfileDirty()) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
</script>

    {{-- ========================================================================= --}}
    {{-- MODAL PENETAPAN JADWAL & PAKET LAYANAN                                    --}}
    {{-- Aturan Strict: Tidak menutup pada backdrop click, hanya Batal atau X       --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="showScheduleModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-schedule-title" role="dialog" aria-modal="true">
        {{-- Backdrop tanpa @click --}}
        <div x-show="showScheduleModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-[#160B29]/70 backdrop-blur-md transition-opacity"></div>

        <div class="min-h-full flex items-center justify-center p-3 sm:p-6 lg:p-8 text-left">
            <div x-show="showScheduleModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-3xl w-full max-w-2xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">

                {{-- Header Modal --}}
                <div class="px-6 py-5 bg-purple-deep text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-white tracking-tight" id="modal-schedule-title">
                            Penetapan Jadwal & Paket Layanan
                        </h3>
                        <p class="text-xs font-medium text-purple-200 mt-0.5" x-text="schedulingForm ? ('Formulir: ' + (schedulingForm.ticket || '#' + schedulingForm.id) + ' - ' + (schedulingForm.client_name || schedulingForm.form_type_label)) : ''"></p>
                    </div>
                    <button type="button" @click="closeScheduleModal()"
                            class="w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-xl font-bold"
                            title="Tutup">
                        &times;
                    </button>
                </div>

                {{-- Body Modal Form --}}
                <form :action="'/client-forms/' + (schedulingForm ? schedulingForm.id : '') + '/schedule'" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="p-6 sm:p-7 overflow-y-auto space-y-5 text-black">

                        {{-- 1. Kategori Layanan --}}
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                            <label class="block text-xs font-semibold text-gray-700">Kategori Layanan <span class="text-red-500">*</span></label>
                            <template x-if="scheduleCategoryLocked">
                                <div>
                                    <input type="hidden" name="kategori" :value="scheduleCategory">
                                    <div class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-black uppercase tracking-wider flex items-center justify-between">
                                        <span x-text="scheduleCategory === 'konseling' ? 'Layanan Konseling' : 'Layanan Asesmen Psikotes'"></span>
                                        <span class="text-xs text-gray-400 font-normal">Terkunci sesuai jenis formulir</span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!scheduleCategoryLocked">
                                <div>
                                    <select name="kategori" x-model="scheduleCategory" @change="onCategoryChange()"
                                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-xs font-semibold text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                        <option value="konseling">Layanan Konseling (WNA)</option>
                                        <option value="psikotes">Layanan Asesmen Psikotes (WNA / Asesmen)</option>
                                    </select>
                                    <p class="text-[11px] text-gray-500 mt-1">Formulir Biography (English) dapat diarahkan ke Konseling atau Asesmen Psikotes.</p>
                                </div>
                            </template>
                        </div>

                        {{-- 2. Spesifikasi Tier & Paket Layanan Sesuai Katalog --}}
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                            <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Spesifikasi Paket & Tarif Resmi</h5>

                            {{-- Option bila Kategori Konseling --}}
                            <template x-if="scheduleCategory === 'konseling'">
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Tier Konseling <span class="text-red-500">*</span></label>
                                            <select name="service_tier" x-model="scheduleTier" @change="onTierChange()"
                                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                                <option value="pro">Pro (Psikolog Senior)</option>
                                                <option value="reguler">Reguler</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Paket Konseling & Tarif <span class="text-red-500">*</span></label>
                                            <select name="service_package_key" x-model="schedulePackageKey"
                                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                                <template x-if="scheduleTier === 'pro'">
                                                    <optgroup label="Pro (Psikolog Senior)">
                                                        <option value="pro_wni_umum">WNI - Umum (Rp 525.000 / sesi)</option>
                                                        <option value="pro_wni_kerja_sama">WNI - Kerja Sama (Rp 450.000 / sesi)</option>
                                                        <option value="pro_wna_umum">WNA - Umum (Rp 775.000 / sesi)</option>
                                                        <option value="pro_wna_kerja_sama">WNA - Kerja Sama (Rp 675.000 / sesi)</option>
                                                    </optgroup>
                                                </template>
                                                <template x-if="scheduleTier === 'reguler'">
                                                    <optgroup label="Reguler">
                                                        <option value="reguler_wni_umum">WNI - Umum (Rp 275.000 / sesi)</option>
                                                        <option value="reguler_wni_kerja_sama">WNI - Kerja Sama (Rp 225.000 / sesi)</option>
                                                        <option value="reguler_wna_umum">WNA - Umum (Rp 525.000 / sesi)</option>
                                                        <option value="reguler_wna_kerja_sama">WNA - Kerja Sama (Rp 450.000 / sesi)</option>
                                                    </optgroup>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Option bila Kategori Psikotes --}}
                            <template x-if="scheduleCategory === 'psikotes'">
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Klasifikasi Psikotes <span class="text-red-500">*</span></label>
                                            <select name="service_tier" x-model="scheduleTier" @change="onTierChange()"
                                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                                <option value="industri">Psikotes Industri</option>
                                                <option value="non_industri_bakat_minat">Non-Industri (Bakat Minat Biasa)</option>
                                                <option value="non_industri_khusus">Non-Industri Khusus</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Format, Level & Kategori <span class="text-red-500">*</span></label>
                                            <select name="service_package_key" x-model="schedulePackageKey"
                                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                                <template x-if="scheduleTier === 'industri'">
                                                    <optgroup label="Psikotes Industri">
                                                        <option value="short_staf_umum">Short - Staf - Umum (Rp 575.000)</option>
                                                        <option value="short_staf_kerja_sama">Short - Staf - Kerja Sama (Rp 500.000)</option>
                                                        <option value="long_staf_umum">Long - Staf - Umum (Rp 700.000)</option>
                                                        <option value="long_staf_kerja_sama">Long - Staf - Kerja Sama (Rp 600.000)</option>
                                                        <option value="long_supervisor_umum">Long - Supervisor - Umum (Rp 775.000)</option>
                                                        <option value="long_supervisor_kerja_sama">Long - Supervisor - Kerja Sama (Rp 675.000)</option>
                                                        <option value="long_manager_umum">Long - Manager - Umum (Rp 850.000)</option>
                                                        <option value="long_manager_kerja_sama">Long - Manager - Kerja Sama (Rp 750.000)</option>
                                                    </optgroup>
                                                </template>
                                                <template x-if="scheduleTier === 'non_industri_bakat_minat'">
                                                    <optgroup label="Bakat Minat Biasa">
                                                        <option value="short_umum">Short - Umum (Rp 625.000)</option>
                                                        <option value="short_kerja_sama">Short - Kerja Sama (Rp 550.000)</option>
                                                        <option value="long_umum">Long - Umum (Rp 750.000)</option>
                                                        <option value="long_kerja_sama">Long - Kerja Sama (Rp 650.000)</option>
                                                    </optgroup>
                                                </template>
                                                <template x-if="scheduleTier === 'non_industri_khusus'">
                                                    <optgroup label="Non-Industri Khusus">
                                                        <option value="ucs_med_mmpi">UCS-MED - MMPI (Rp 50.000)</option>
                                                        <option value="ucs_dem_mmpi">UCS-DEM - MMPI (Rp 50.000)</option>
                                                        <option value="ucs_dem_fcat_feast">UCS-DEM - FCAT-FEAST (Rp 200.000)</option>
                                                        <option value="ucs_ppsy_fcat_ftpi_feast">UCS-PPSY - FCAT-FTPI-FEAST (Rp 250.000)</option>
                                                        <option value="konseling_ssct_epps">Konseling Psikotes - SSCT-EPPS (Rp 75.000)</option>
                                                    </optgroup>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Summary Tarif Terpilih --}}
                            <div class="p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                <span class="text-gray-500 font-medium">Tarif Resmi Sesi:</span>
                                <span class="text-black font-bold text-sm" x-text="calculatedPriceInfo.price_formatted"></span>
                            </div>
                        </div>

                        {{-- 3. Praktisi (Konselor atau Staff Penguji) --}}
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                            <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Penugasan Psikolog / Penguji</h5>
                            <template x-if="scheduleCategory === 'konseling' || schedulePackageKey === 'konseling_ssct_epps'">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Psikolog Praktik <span class="text-red-500">*</span></label>
                                    <select name="counselor_id" x-model="scheduleCounselorId" required
                                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                        <option value="">-- Pilih Psikolog --</option>
                                        @foreach($counselors as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </template>
                            <template x-if="scheduleCategory === 'psikotes' && schedulePackageKey !== 'konseling_ssct_epps'">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Staff Penguji <span class="text-red-500">*</span></label>
                                    <select name="staff_penguji_id" x-model="scheduleStaffId" required
                                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                        <option value="">-- Pilih Staff Penguji --</option>
                                        @foreach($staffMembers as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->email }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </template>
                        </div>

                        {{-- 4. Jadwal & Waktu Sesi --}}
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                            <h5 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Waktu & Tempat Pelaksanaan</h5>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Sesi <span class="text-red-500">*</span></label>
                                    <input type="date" name="tanggal_dijadwalkan" x-model="scheduleDate" required
                                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                                    <input type="time" name="session_start" x-model="scheduleStart" required
                                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                                    <input type="time" name="session_end" x-model="scheduleEnd" required
                                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Metode Sesi <span class="text-red-500">*</span></label>
                                    <select name="session_type" x-model="scheduleSessionType"
                                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                        <option value="offline">Offline (Tatap Muka di UC PSC)</option>
                                        <option value="online">Online (Video Call / Google Meet / Zoom)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi / Link Pertemuan</label>
                                    <input type="text" name="location" x-model="scheduleLocation" placeholder="Contoh: Ruang Konseling 1 atau Link Zoom"
                                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep">
                                </div>
                            </div>
                        </div>

                        {{-- 5. Catatan Khusus --}}
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5">
                            <label class="block text-xs font-semibold text-gray-700">Catatan Pelaporan & Briefing</label>
                            <textarea name="notes" x-model="scheduleNotes" rows="2" placeholder="Catatan hasil konfirmasi antara staf dan konselor/penguji..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-black bg-white focus:ring-2 focus:ring-purple-deep"></textarea>
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" @click="closeScheduleModal()"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-gray-600 hover:bg-slate-100 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-purple-deep hover:bg-purple-900 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Simpan Jadwal & Layanan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection