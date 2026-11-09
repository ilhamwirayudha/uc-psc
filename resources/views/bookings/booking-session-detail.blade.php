@extends('layouts.dashboard')

@section('title', 'Detail Booking #' . $booking->id . ' - UC PSC')
@section('page-title', 'Detail Booking #' . $booking->id)

@section('content')
@php
    $client = $booking->client;
    $clientJobTitle = $client ? $client->occupation : null;
    if ($clientJobTitle && str_contains($clientJobTitle, ' - ')) {
        $parts = explode(' - ', $clientJobTitle);
        $clientJobTitle = trim($parts[0]);
    }
    $latestForm = $client ? $client->clientForms->first() : null;

    // Inisialisasi Paket Layanan
    $pkgName = $booking->service_package_name ?: ($client ? $client->counseling_type : '');
    $initJenis = str_contains(strtolower($pkgName), 'reguler') ? 'Reguler' : 'Pro';
    $initMetode = (str_contains(strtolower($pkgName), 'online') || $booking->session_type === 'online') ? 'Online' : 'Offline';
    $initKlien = str_contains(strtoupper($pkgName), 'WNA') ? 'WNA' : 'WNI';
    $initKategori = str_contains(strtolower($pkgName), 'kerja sama') ? 'Kerja Sama' : 'Umum';

    $tanggalBookingVal = $booking->tanggal_booking_dibuat ? $booking->tanggal_booking_dibuat->format('Y-m-d') : '';
    $tanggalDijadwalkanVal = $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('Y-m-d') : '';
    $startTime = $booking->session_start ? substr($booking->session_start, 0, 5) : '';
    $endTime = $booking->session_end ? substr($booking->session_end, 0, 5) : '';
    $currentStatus = $booking->status ?: 'data_masuk';
    $currentPaymentStatus = $booking->payment_workflow_status ?: 'menunggu_sesi_selesai';
    $currentVa = $booking->va_number ?: '';

    // Step awal wizard konseling (selalu mulai dari Langkah 1, kecuali baru saja menyimpan sub-card tertentu)
    $initialStep = 1;
    if (session('updated_card')) {
        $cardToStep = [
            'step-1' => 1,
            'card-paket-layanan' => 1,
            'step-2' => 2,
            'card-penugasan-pelaksana' => 2,
            'card-jadwal-sesi' => 2,
            'step-3' => 3,
            'card-status-pembayaran' => 3,
            'step-4' => 4,
            'card-status-layanan' => 4,
        ];
        if (isset($cardToStep[session('updated_card')])) {
            $initialStep = $cardToStep[session('updated_card')];
        }
    }
@endphp

<div x-data="{
    // Current Active Tab / Step (1: Klien & Paket, 2: Penjadwalan, 3: Pembayaran & Biaya, 4: Status Sesi & Catatan)
    activeStep: {{ $initialStep }},
    stepsCount: 4,

    // Edit modes per card / section
    editPaket: false,
    editKonselor: false,
    editJadwal: false,
    editPembayaran: false,
    editStatus: false,
    discardModalOpen: false,

    get isAnyEditing() {
        return this.editPaket || this.editKonselor || this.editJadwal || this.editPembayaran || this.editStatus;
    },

    attemptClose() {
        if (this.isAnyEditing) {
            this.discardModalOpen = true;
            return;
        }
        this.closeDirectly();
    },

    closeDirectly() {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '{{ $client ? route('clients.show', $client) : route('bookings.index') }}';
        }
    },

    setStep(stepNum) {
        this.activeStep = stepNum;
        const hiddenCardInput = document.getElementById('submitted_card_input');
        if (hiddenCardInput) {
            hiddenCardInput.value = 'step-' + stepNum;
        }
    },

    prepareSubmit(stepName) {
        const hiddenCardInput = document.getElementById('submitted_card_input');
        if (hiddenCardInput) {
            hiddenCardInput.value = stepName;
        }
    },

    serviceType: '{{ $booking->kategori }}',
    jenis: '{{ in_array($client->jenis ?? '', ['industri', 'company', 'perusahaan']) ? 'industri' : 'individu' }}',

    // Dropdown bertingkat Konseling
    counselingJenis: '{{ $initJenis }}',
    counselingMetode: '{{ $initMetode }}',
    counselingKlien: '{{ $initKlien }}',
    counselingKategori: '{{ $initKategori }}',

    sessionType: '{{ $sessionType }}',
    counselorIdVal: '{{ old('counselor_id', $booking->counselor_id ?? '') }}',
    tanggalDijadwalkanVal: '{{ old('tanggal_dijadwalkan', $tanggalDijadwalkanVal) }}',
    startTimeVal: '{{ $startTime }}',
    endTimeVal: '{{ $endTime }}',
    statusVal: '{{ old('status', $booking->status ?: 'data_masuk') }}',
    paymentStatusVal: '{{ $currentPaymentStatus }}',
    vaNumberVal: '{{ $currentVa }}',

    // Helper teks label status booking
    getStatusLabel(statusKey) {
        const labels = {
            'data_masuk': 'Data Masuk',
            'waiting_list': 'Waiting List (Cari Psikolog/Jadwal)',
            'menunggu_konfirmasi': 'Terjadwal (Belum Konfirmasi)',
            'terjadwal_gocal': 'Terjadwal & Konfirmasi (GCal)',
            'reminder_h1': 'Reminder & Tagihan H-1',
            'perlu_bayar_overtime': 'Sesi Selesai (Perlu Overtime)',
            'perlu_sesi_lanjutan': 'Sesi Selesai (Perlu Lanjutan)',
            'selesai': 'Sesi Selesai (Tuntas)',
            'batal': 'Dibatalkan'
        };
        return labels[statusKey] || statusKey || 'Data Masuk';
    },

    // Deteksi tingkat milestone progres aktual (1 sampai 4)
    get highestCompletedStep() {
        if (this.statusVal === 'selesai' || this.paymentStatusVal === 'lunas' || this.paymentStatusVal === 'lunas_overtime') {
            return 4;
        }
        if (this.calculatedDurationMinutes > 0 || this.statusVal === 'perlu_bayar_overtime' || this.paymentStatusVal === 'menunggu_bayar' || this.paymentStatusVal === 'draft_ke_finance') {
            return 3;
        }
        if ((this.counselorIdVal && String(this.counselorIdVal).trim() !== '') || (this.tanggalDijadwalkanVal && String(this.tanggalDijadwalkanVal).trim() !== '')) {
            return 2;
        }
        return 1;
    },

    // Sinkronisasi cerdas status dengan field psikolog, jadwal, dan durasi
    syncStatusWithSchedule() {
        if (this.serviceType !== 'konseling') return;

        const hasCounselor = !!(this.counselorIdVal && String(this.counselorIdVal).trim() !== '');
        const hasDate = !!(this.tanggalDijadwalkanVal && String(this.tanggalDijadwalkanVal).trim() !== '');

        if (!hasCounselor && !hasDate) {
            this.statusVal = 'data_masuk';
        } else if ((hasCounselor && !hasDate) || (!hasCounselor && hasDate)) {
            this.statusVal = 'waiting_list';
        } else if (hasCounselor && hasDate) {
            if (this.statusVal === 'data_masuk' || this.statusVal === 'waiting_list') {
                this.statusVal = 'menunggu_konfirmasi';
            }
        }
    },

    // Sinkronisasi status saat durasi/jam selesai diisi atau overtime terdeteksi
    syncStatusWithDuration() {
        if (this.serviceType !== 'konseling') return;
        if (this.isOvertime) {
            if (this.paymentStatusVal !== 'lunas_overtime' && this.statusVal !== 'batal') {
                this.statusVal = 'perlu_bayar_overtime';
            }
        } else {
            if (this.statusVal === 'perlu_bayar_overtime') {
                this.statusVal = 'selesai';
            }
        }
    },

    // Sinkronisasi status saat status pembayaran berubah
    onPaymentStatusChange() {
        if (this.serviceType !== 'konseling') return;
        if (this.paymentStatusVal === 'lunas_overtime' && this.statusVal === 'perlu_bayar_overtime') {
            this.statusVal = 'selesai';
        }
    },

    get calculatedDurationMinutes() {
        if (!this.startTimeVal || !this.endTimeVal) return 0;
        let partsS = this.startTimeVal.split(':');
        let partsE = this.endTimeVal.split(':');
        if (partsS.length < 2 || partsE.length < 2) return 0;
        let total = ((parseInt(partsE[0], 10) * 60) + parseInt(partsE[1], 10)) - ((parseInt(partsS[0], 10) * 60) + parseInt(partsS[1], 10));
        return total > 0 ? total : 0;
    },

    get durationFormatted() {
        let mins = this.calculatedDurationMinutes;
        if (mins === 0) return '-';
        let h = Math.floor(mins / 60);
        let m = mins % 60;
        if (h > 0 && m > 0) return `${h} Jam ${m} Menit`;
        if (h > 0) return `${h} Jam`;
        return `${m} Menit`;
    },

    get isOvertime() {
        return this.calculatedDurationMinutes > 90;
    },

    get overtimeAmount() {
        return this.isOvertime ? this.counselingOvertime : 0;
    },

    get totalBillAmount() {
        return this.counselingPrice + this.overtimeAmount;
    },

    get counselingPrice() {
        if (this.counselingJenis === 'Pro') {
            if (this.counselingKlien === 'WNI') {
                return this.counselingKategori === 'Kerja Sama' ? 450000 : 525000;
            } else {
                return this.counselingKategori === 'Kerja Sama' ? 675000 : 775000;
            }
        } else {
            if (this.counselingKlien === 'WNI') {
                return this.counselingKategori === 'Kerja Sama' ? 225000 : 275000;
            } else {
                return this.counselingKategori === 'Kerja Sama' ? 450000 : 525000;
            }
        }
    },
    get counselingOvertime() {
        return this.counselingPrice;
    },
    get fullCounselingTitle() {
        return `Konseling ${this.counselingJenis} - ${this.counselingMetode} - ${this.counselingKlien} - ${this.counselingKategori}`;
    },
    formatRupiah(val) {
        return 'Rp ' + Number(val).toLocaleString('id-ID');
    }
}" class="relative">

    {{-- Backdrop Overlay (TIDAK menutup modal saat backdrop diklik sesuai aturan modal-behavior) --}}
    <div class="fixed inset-0 z-40 bg-[#160B29]/75 backdrop-blur-md transition-opacity"></div>

    {{-- Dialog Wrapper (Centered Modal Container - Landscape Lebar Proporsional Opsi 1) --}}
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center p-3 sm:p-5 lg:p-6 text-left">
            <div class="relative bg-purple-deep rounded-3xl w-full max-w-6xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">

                {{-- ========================================================= --}}
                {{-- 1. MODAL HEADER & PROGRESS STEPPER BAR LANDSCAPE          --}}
                {{-- ========================================================= --}}
                <div class="px-6 sm:px-8 pt-5 pb-4 bg-purple-deep text-white shrink-0 shadow-xs border-b border-purple-800/40 rounded-t-3xl">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg sm:text-xl font-bold text-white tracking-tight" id="modal-booking-detail-title">
                                Detail Booking {{ $booking->kategori === 'konseling' ? 'Jadwal Konseling' : 'Jadwal Psikotes' }}
                            </h3>
                        </div>

                        {{-- Quick Info Badge & Tombol Close X --}}
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/10 border border-white/15 text-xs text-purple-100 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span x-text="getStatusLabel(statusVal)"></span>
                            </span>

                            <button type="button" 
                                    @click="attemptClose()"
                                    class="w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-xl font-bold leading-none"
                                    title="Tutup Modal (Esc / Klik X)">
                                &times;
                            </button>
                        </div>
                    </div>

                    {{-- Horizontal Progress Bar with Dots and Line (Sesuai Referensi Opsi 1) --}}
                    <div class="mt-4 pt-3 border-t border-white/15">
                        <div class="flex items-center justify-between max-w-3xl mx-auto px-4 sm:px-8">
                            
                            {{-- Step 1 Dot --}}
                            <div class="flex flex-col items-center cursor-pointer group z-10" @click="setStep(1)">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-200 border-2"
                                     :class="{
                                         'bg-emerald-500 border-white text-white shadow-lg ring-4 ring-emerald-300/40 scale-105 font-extrabold': activeStep === 1,
                                         'bg-emerald-600 border-emerald-400 text-white': activeStep > 1,
                                         'bg-white/20 border-white/40 text-purple-200 group-hover:bg-white/30': activeStep < 1
                                     }">
                                    <template x-if="activeStep > 1">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    </template>
                                    <template x-if="activeStep <= 1">
                                        <span>1</span>
                                    </template>
                                </div>
                                <span class="text-xs font-semibold mt-1.5 transition whitespace-nowrap"
                                      :class="activeStep === 1 ? 'text-white font-bold' : (activeStep > 1 ? 'text-emerald-300 font-medium' : 'text-purple-200 group-hover:text-white')">
                                    Klien & Paket
                                </span>
                            </div>

                            {{-- Segment Connector 1-2 --}}
                            <div class="flex-1 h-1 mx-2 sm:mx-4 -mt-5 rounded-full transition-all duration-300 z-0"
                                 :class="activeStep > 1 ? 'bg-emerald-400' : 'bg-white/20'"></div>

                            {{-- Step 2 Dot --}}
                            <div class="flex flex-col items-center cursor-pointer group z-10" @click="setStep(2)">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-200 border-2"
                                     :class="{
                                         'bg-emerald-500 border-white text-white shadow-lg ring-4 ring-emerald-300/40 scale-105 font-extrabold': activeStep === 2,
                                         'bg-emerald-600 border-emerald-400 text-white': activeStep > 2,
                                         'bg-white/20 border-white/40 text-purple-200 group-hover:bg-white/30': activeStep < 2
                                     }">
                                    <template x-if="activeStep > 2">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    </template>
                                    <template x-if="activeStep <= 2">
                                        <span>2</span>
                                    </template>
                                </div>
                                <span class="text-xs font-semibold mt-1.5 transition whitespace-nowrap"
                                      :class="activeStep === 2 ? 'text-white font-bold' : (activeStep > 2 ? 'text-emerald-300 font-medium' : 'text-purple-200 group-hover:text-white')">
                                    Penjadwalan Sesi
                                </span>
                            </div>

                            {{-- Segment Connector 2-3 --}}
                            <div class="flex-1 h-1 mx-2 sm:mx-4 -mt-5 rounded-full transition-all duration-300 z-0"
                                 :class="activeStep > 2 ? 'bg-emerald-400' : 'bg-white/20'"></div>

                            {{-- Step 3 Dot --}}
                            <div class="flex flex-col items-center cursor-pointer group z-10" @click="setStep(3)">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-200 border-2"
                                     :class="{
                                         'bg-emerald-500 border-white text-white shadow-lg ring-4 ring-emerald-300/40 scale-105 font-extrabold': activeStep === 3,
                                         'bg-emerald-600 border-emerald-400 text-white': activeStep > 3,
                                         'bg-white/20 border-white/40 text-purple-200 group-hover:bg-white/30': activeStep < 3
                                     }">
                                    <template x-if="activeStep > 3">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    </template>
                                    <template x-if="activeStep <= 3">
                                        <span>3</span>
                                    </template>
                                </div>
                                <span class="text-xs font-semibold mt-1.5 transition whitespace-nowrap"
                                      :class="activeStep === 3 ? 'text-white font-bold' : (activeStep > 3 ? 'text-emerald-300 font-medium' : 'text-purple-200 group-hover:text-white')">
                                    Tagihan & Finansial
                                </span>
                            </div>

                            {{-- Segment Connector 3-4 --}}
                            <div class="flex-1 h-1 mx-2 sm:mx-4 -mt-5 rounded-full transition-all duration-300 z-0"
                                 :class="activeStep > 3 ? 'bg-emerald-400' : 'bg-white/20'"></div>

                            {{-- Step 4 Dot --}}
                            <div class="flex flex-col items-center cursor-pointer group z-10" @click="setStep(4)">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-200 border-2"
                                     :class="{
                                         'bg-emerald-500 border-white text-white shadow-lg ring-4 ring-emerald-300/40 scale-105 font-extrabold': activeStep === 4,
                                         'bg-emerald-600 border-emerald-400 text-white': highestCompletedStep >= 4,
                                         'bg-white/20 border-white/40 text-purple-200 group-hover:bg-white/30': activeStep < 4
                                     }">
                                    <template x-if="highestCompletedStep >= 4 && activeStep !== 4">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    </template>
                                    <template x-if="highestCompletedStep < 4 || activeStep === 4">
                                        <span>4</span>
                                    </template>
                                </div>
                                <span class="text-xs font-semibold mt-1.5 transition whitespace-nowrap"
                                      :class="activeStep === 4 ? 'text-white font-bold' : 'text-purple-200 group-hover:text-white'">
                                    Status & Catatan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- 2. MODAL BODY (LANDSCAPE COMPACT VIEWPORT)                --}}
                {{-- ========================================================= --}}
                <div class="p-5 sm:p-6 lg:p-7 text-black bg-[#FAF9FD] flex-1 overflow-y-auto custom-scrollbar">

                    {{-- Global Alert Success / Error (Tunggal & Bersih) --}}
                    @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-sm font-medium flex items-center justify-between gap-2.5 shadow-xs mb-4">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" type="button" class="text-emerald-700 hover:text-emerald-900 p-1 rounded-lg hover:bg-emerald-100 transition cursor-pointer shrink-0" title="Tutup">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 shadow-xs mb-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-red-800 mb-1">Mohon perbaiki isian berikut:</h4>
                                <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('bookings.update', $booking) }}" method="POST" id="form-booking-detail" class="h-full">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="client_id" value="{{ $booking->client_id }}">
                        <input type="hidden" name="kategori" value="{{ $booking->kategori }}">
                        <input type="hidden" name="status" :value="statusVal">
                        <input type="hidden" name="submitted_card" id="submitted_card_input" value="step-{{ $initialStep }}">

                        {{-- ========================================================= --}}
                        {{-- STEP 1: INFORMASI KLIEN & PAKET LAYANAN                   --}}
                        {{-- ========================================================= --}}
                        <div x-show="activeStep === 1" x-cloak class="space-y-4">

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                                
                                {{-- Kolom Kiri (7/12): Biodata Klien & Riwayat Form --}}
                                <div class="lg:col-span-7 space-y-4">
                                    {{-- Formulir Terakhir --}}
                                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                        <div class="px-5 py-5 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                            <h4 class="font-bold text-white text-xs sm:text-sm uppercase tracking-wider">
                                                Formulir Terakhir yang Diisi
                                            </h4>
                                        </div>
                                        <div class="p-4 bg-white">
                                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                @if($latestForm)
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block">Formulir Terakhir:</span>
                                                    <span class="text-sm font-medium text-black block mt-0.5">{{ $latestForm->form_type_label }}</span>
                                                </div>
                                                <div class="sm:text-right">
                                                    <span class="text-xs font-medium text-slate-500 block">Waktu Pengisian:</span>
                                                    <span class="text-sm font-medium text-black block mt-0.5">{{ $latestForm->created_at ? $latestForm->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') : '-' }}</span>
                                                </div>
                                                @else
                                                <span class="text-sm font-medium text-slate-400 italic">Belum ada riwayat pengisian formulir pendaftaran online.</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Informasi Klien Terdaftar (10 Field Sesuai Biodata Klien) --}}
                                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                        <div class="px-5 py-5 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                            <h4 class="font-bold text-white text-xs sm:text-sm uppercase tracking-wider">
                                                Informasi Klien Terdaftar
                                            </h4>
                                        </div>
                                        <div class="p-4 bg-white">
                                            @if($client)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                {{-- 1. Nama Lengkap --}}
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">Nama Lengkap</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black">{{ $client->name ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                {{-- 2. Nama Perusahaan --}}
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">Nama Perusahaan</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black">{{ $client->company_name ?? ($client->pic_name ?? '-') }}</span>
                                                    </div>
                                                </div>

                                                {{-- 3. Pekerjaan / Jabatan Saat Ini --}}
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">Pekerjaan / Jabatan Saat Ini</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black">{{ $clientJobTitle ?: '-' }}</span>
                                                    </div>
                                                </div>

                                                {{-- 4. Jenis Kelamin --}}
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">Jenis Kelamin</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black">{{ $client->gender_label ?: '-' }}</span>
                                                    </div>
                                                </div>

                                                {{-- 5 & 6. Tempat, Tanggal Lahir --}}
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

                                                {{-- 6. No. Telepon --}}
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">No. Telepon</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black">{{ $client->phone ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                {{-- 7. Alamat Email --}}
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">Alamat Email</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black break-all">{{ $client->email ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                {{-- 8. Pendidikan Terakhir --}}
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">Pendidikan Terakhir</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black">{{ $client->education_label ?? $client->education ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                {{-- 9. Agama / Kepercayaan --}}
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">Agama / Kepercayaan</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black">{{ $client->religion ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                {{-- 10. Alamat Lengkap --}}
                                                <div class="sm:col-span-2 lg:col-span-3">
                                                    <span class="text-xs font-medium text-slate-500 block mb-1">Alamat</span>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center">
                                                        <span class="text-sm font-medium text-black leading-relaxed">{{ $client->address ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @else
                                            <p class="text-xs text-slate-400 italic py-3 text-center">Data klien tidak ditemukan.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan (5/12): Paket Layanan & Tarif --}}
                                <div class="lg:col-span-5 space-y-4">
                                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                        <div class="px-5 py-5 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                            <h4 class="font-bold text-white text-xs sm:text-sm uppercase tracking-wider">
                                                Paket Layanan
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <button type="button" x-show="!editPaket" @click="editPaket = true"
                                                        class="inline-flex items-center justify-center p-2 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white transition cursor-pointer"
                                                        title="Ubah Paket Layanan">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                </button>
                                                <div x-show="editPaket" x-cloak class="flex items-center gap-2">
                                                    <button type="button" @click="editPaket = false"
                                                            class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white hover:bg-purple-50 text-purple-deep transition cursor-pointer flex items-center gap-1 shadow-xs"
                                                            title="Batalkan perubahan">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                        <span>Batal</span>
                                                    </button>
                                                    <button type="submit" form="form-booking-detail" @click="prepareSubmit('step-1')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-deep hover:bg-purple-900 border border-white/40 text-white text-xs font-semibold transition cursor-pointer shadow-xs">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                                        <span>Simpan</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 space-y-3">
                                            {{-- View Mode Paket --}}
                                            <div x-show="!editPaket" class="space-y-2.5">
                                                {{-- 4 Field Rincian: Jenis, Metode, Klien, Kategori --}}
                                                <div class="space-y-2.5">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Jenis</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="counselingJenis"></div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Metode</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="counselingMetode"></div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Klien</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="counselingKlien"></div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-500 mb-1">Kategori</label>
                                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="counselingKategori"></div>
                                                    </div>
                                                </div>

                                                {{-- Card Paket Terpilih --}}
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Paket Terpilih</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="fullCounselingTitle"></div>
                                                </div>

                                                {{-- Card Total Harga per Sesi --}}
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Harga per Sesi</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center justify-between text-sm font-medium text-black">
                                                        <span x-text="formatRupiah(counselingPrice)"></span>
                                                        <span class="text-xs font-medium text-slate-400">*sebelum PPN & PPh</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Edit Mode Paket --}}
                                            <div x-show="editPaket" x-cloak class="space-y-2.5">
                                                <div class="space-y-2.5">
                                                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                        <label class="block text-xs font-medium text-slate-500">Jenis</label>
                                                        <select x-model="counselingJenis" class="w-full px-3 py-2 border border-transparent rounded-lg text-xs bg-white font-medium text-black focus:outline-none focus:ring-0">
                                                            <option value="Reguler">Reguler</option>
                                                            <option value="Pro">Pro</option>
                                                        </select>
                                                    </div>
                                                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                        <label class="block text-xs font-medium text-slate-500">Metode</label>
                                                        <select x-model="counselingMetode" class="w-full px-3 py-2 border border-transparent rounded-lg text-xs bg-white font-medium text-black focus:outline-none focus:ring-0">
                                                            <option value="Offline">Offline</option>
                                                            <option value="Online">Online</option>
                                                        </select>
                                                    </div>
                                                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                        <label class="block text-xs font-medium text-slate-500">Klien</label>
                                                        <select x-model="counselingKlien" class="w-full px-3 py-2 border border-transparent rounded-lg text-xs bg-white font-medium text-black focus:outline-none focus:ring-0">
                                                            <option value="WNI">WNI</option>
                                                            <option value="WNA">WNA</option>
                                                        </select>
                                                    </div>
                                                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                        <label class="block text-xs font-medium text-slate-500">Kategori</label>
                                                        <select x-model="counselingKategori" class="w-full px-3 py-2 border border-transparent rounded-lg text-xs bg-white font-medium text-black focus:outline-none focus:ring-0">
                                                            <option value="Umum">Umum</option>
                                                            <option value="Kerja Sama">Kerja Sama</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Card Paket Terpilih (Sinkron dengan View) --}}
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 space-y-1">
                                                    <span class="text-xs font-medium text-slate-500 block">Paket Terpilih:</span>
                                                    <span class="text-sm font-medium text-black block mt-0.5" x-text="fullCounselingTitle"></span>
                                                </div>

                                                {{-- Card Live Estimasi Harga per Sesi (Sinkron dengan View) --}}
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 space-y-1">
                                                    <span class="text-xs font-medium text-slate-500 block">Harga per Sesi:</span>
                                                    <div class="flex items-baseline gap-2 mt-0.5">
                                                        <span class="text-sm font-medium text-black" x-text="formatRupiah(counselingPrice)"></span>
                                                        <span class="text-xs font-medium text-slate-400">*sebelum PPN & PPh</span>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="counseling_type" :value="fullCounselingTitle">
                                                <input type="hidden" name="price" :value="counselingPrice">
                                                <input type="hidden" name="overtime_price" :value="counselingOvertime">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ========================================================= --}}
                        {{-- STEP 2: PENJADWALAN, PSIKOLOG & LOKASI SESI               --}}
                        {{-- ========================================================= --}}
                        <div x-show="activeStep === 2" x-cloak class="space-y-4">

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                                
                                {{-- Kolom Kiri (5/12): Penugasan Psikolog --}}
                                <div class="lg:col-span-5 space-y-4">
                                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                        <div class="px-5 py-5 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                            <h4 class="font-bold text-white text-xs sm:text-sm uppercase tracking-wider">
                                                {{ $booking->kategori === 'konseling' ? 'Penugasan Psikolog' : 'Tim Penguji' }}
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <button type="button" x-show="!editKonselor" @click="editKonselor = true"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold transition cursor-pointer">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    <span>Ubah</span>
                                                </button>
                                                <div x-show="editKonselor" x-cloak class="flex items-center gap-2">
                                                    <button type="button" @click="editKonselor = false"
                                                            class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white hover:bg-purple-50 text-purple-deep transition cursor-pointer flex items-center gap-1 shadow-xs"
                                                            title="Batalkan perubahan">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                        <span>Batal</span>
                                                    </button>
                                                    <button type="submit" form="form-booking-detail" @click="prepareSubmit('step-2')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-deep hover:bg-purple-900 border border-white/40 text-white text-xs font-semibold transition cursor-pointer shadow-xs">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                                        <span>Simpan</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 space-y-3">
                                            @if($booking->kategori === 'konseling')
                                            <div x-show="!editKonselor">
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Psikolog Bertugas</label>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                    {{ $booking->counselor ? $booking->counselor->name : 'Belum Ditugaskan' }}
                                                </div>
                                            </div>

                                            <div x-show="editKonselor" x-cloak class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                                                <label class="block text-xs font-medium text-slate-500">Pilih Psikolog Bertugas:</label>
                                                <select name="counselor_id" x-model="counselorIdVal" @change="syncStatusWithSchedule()"
                                                    class="w-full px-3 py-2 border border-transparent rounded-xl text-sm focus:outline-none focus:ring-0 bg-white font-medium text-black">
                                                    <option value="">-- Belum Ditugaskan --</option>
                                                    @foreach($counselors as $counselor)
                                                    <option value="{{ $counselor->id }}" {{ old('counselor_id', $booking->counselor_id) == $counselor->id ? 'selected' : '' }}>
                                                        {{ $counselor->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <p class="text-[11px] text-slate-500 italic">*Status sesi otomatis tersinkronisasi saat psikolog dipilih.</p>
                                            </div>
                                            @else
                                            <div class="space-y-2.5">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Staff Penguji</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        {{ $booking->staffPenguji->name ?? '-' }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Staff Koreksi</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        {{ $booking->staffKoreksi->name ?? '-' }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Staff Pelapor</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        {{ $booking->staffPelapor->name ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan (7/12): Jadwal, Waktu, Durasi & Lokasi --}}
                                <div class="lg:col-span-7 space-y-4">
                                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                        <div class="px-5 py-5 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                            <h4 class="font-bold text-white text-xs sm:text-sm uppercase tracking-wider">
                                                Jadwal, Waktu & Lokasi Pertemuan
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <button type="button" x-show="!editJadwal" @click="editJadwal = true"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold transition cursor-pointer">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    <span>Ubah</span>
                                                </button>
                                                <div x-show="editJadwal" x-cloak class="flex items-center gap-2">
                                                    <button type="button" @click="editJadwal = false"
                                                            class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white hover:bg-purple-50 text-purple-deep transition cursor-pointer flex items-center gap-1 shadow-xs"
                                                            title="Batalkan perubahan">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                        <span>Batal</span>
                                                    </button>
                                                    <button type="submit" form="form-booking-detail" @click="prepareSubmit('step-2')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-deep hover:bg-purple-900 border border-white/40 text-white text-xs font-semibold transition cursor-pointer shadow-xs">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                                        <span>Simpan</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 space-y-3">
                                            {{-- View Mode Jadwal --}}
                                            <div x-show="!editJadwal" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Tanggal Permintaan Booking</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        {{ $booking->tanggal_booking_dibuat ? $booking->tanggal_booking_dibuat->locale('id')->isoFormat('D MMMM Y') : '-' }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Tanggal Sesi Dijadwalkan</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        {{ $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->locale('id')->isoFormat('D MMMM Y') : 'Belum Dijadwalkan' }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Jam Mulai Sesi</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="startTimeVal ? startTimeVal + ' WIB' : '-' "></div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Jam Selesai Sesi</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="endTimeVal ? endTimeVal + ' WIB' : '-' "></div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Durasi Sesi Riil</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="durationFormatted"></div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Status Overtime (>90 mnt)</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="calculatedDurationMinutes === 0 ? '-' : (isOvertime ? 'Overtime' : 'Tidak')"></div>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Metode & Ruangan</label>
                                                    <div class="px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black">
                                                        {{ match($sessionType) {
                                                            'online' => 'Online (Google Meet / Zoom)',
                                                            'whatsapp' => 'Konsultasi WhatsApp',
                                                            default => 'Tatap Muka (Offline di Ruang PSC)',
                                                        } }}
                                                        {{ $location ? ' - ' . $location : '' }}
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Edit Mode Jadwal --}}
                                            <div x-show="editJadwal" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                    <label class="block text-xs font-medium text-slate-500">Tanggal Booking Dibuat</label>
                                                    <input type="date" name="tanggal_booking_dibuat" value="{{ old('tanggal_booking_dibuat', $tanggalBookingVal) }}"
                                                        class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs bg-white font-medium text-black">
                                                </div>
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                    <label class="block text-xs font-medium text-slate-500">Tanggal Sesi Dijadwalkan</label>
                                                    <input type="date" name="tanggal_dijadwalkan" x-model="tanggalDijadwalkanVal" @change="syncStatusWithSchedule()" @input="syncStatusWithSchedule()"
                                                        class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs bg-white font-medium text-black">
                                                </div>
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                    <label class="block text-xs font-medium text-slate-500">Jam Mulai Sesi</label>
                                                    <input type="time" name="start_time" x-model="startTimeVal"
                                                        class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs bg-white font-medium text-black">
                                                </div>
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                    <label class="block text-xs font-medium text-slate-500">Jam Selesai Sesi</label>
                                                    <input type="time" name="end_time" x-model="endTimeVal" @change="syncStatusWithDuration()" @input="syncStatusWithDuration()"
                                                        class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs bg-white font-medium text-black">
                                                </div>
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                    <label class="block text-xs font-medium text-slate-500">Metode Sesi</label>
                                                    <select name="session_type" x-model="sessionType"
                                                        class="w-full px-3 py-1.5 border border-transparent rounded-lg text-xs bg-white font-medium text-black focus:outline-none focus:ring-0">
                                                        <option value="tatap_muka">Tatap Muka (Offline di Ruang PSC)</option>
                                                        <option value="online">Online (Google Meet / Zoom)</option>
                                                        <option value="whatsapp">Konsultasi WhatsApp</option>
                                                    </select>
                                                </div>
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1">
                                                    <label class="block text-xs font-medium text-slate-500">Ruangan / Link Meet</label>
                                                    <input type="text" name="location" value="{{ old('location', $location) }}"
                                                        :placeholder="sessionType === 'online' ? 'https://meet.google.com/...' : 'Ruang Konseling A'"
                                                        class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs bg-white font-medium text-black">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ========================================================= --}}
                        {{-- STEP 3: TAGIHAN, OVERTIME & STATUS PEMBAYARAN FINANCE     --}}
                        {{-- ========================================================= --}}
                        <div x-show="activeStep === 3" x-cloak class="space-y-4">

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                                
                                {{-- Kolom Kiri (6/12): Rincian Biaya Pokok & Overtime --}}
                                <div class="lg:col-span-6 space-y-4">
                                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                        <div class="px-5 py-5 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                            <h4 class="font-bold text-white text-xs sm:text-sm uppercase tracking-wider">
                                                Kalkulasi Biaya Sesi & Overtime
                                            </h4>
                                        </div>
                                        <div class="p-4 space-y-3">
                                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 flex items-center justify-between">
                                                <span class="text-xs font-medium text-slate-500">Tarif Pokok (1 Sesi / 60 Menit):</span>
                                                <span class="text-sm font-medium text-black" x-text="formatRupiah(counselingPrice)"></span>
                                            </div>

                                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 flex items-center justify-between">
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block">Biaya Overtime:</span>
                                                    <span class="text-[11px] text-slate-400" x-text="isOvertime ? 'Terhitung (Sesi > 90 mnt)' : 'Tidak Ada Overtime'"></span>
                                                </div>
                                                <span class="text-sm font-medium text-black" x-text="formatRupiah(overtimeAmount)"></span>
                                            </div>

                                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 flex items-center justify-between">
                                                <div>
                                                    <span class="text-xs font-medium text-slate-500 block">Total Tagihan Sesi:</span>
                                                    <span class="text-[11px] text-slate-400 italic">*Belum termasuk PPh dan PPN</span>
                                                </div>
                                                <span class="text-sm font-medium text-black" x-text="formatRupiah(totalBillAmount)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom Kanan (6/12): Alur Nota & Pembayaran UC Finance --}}
                                <div class="lg:col-span-6 space-y-4">
                                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                        <div class="px-5 py-5 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                            <h4 class="font-bold text-white text-xs sm:text-sm uppercase tracking-wider">
                                                Status Pembayaran UC Finance
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <button type="button" x-show="!editPembayaran" @click="editPembayaran = true"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold transition cursor-pointer">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    <span>Ubah</span>
                                                </button>
                                                <div x-show="editPembayaran" x-cloak class="flex items-center gap-2">
                                                    <button type="button" @click="editPembayaran = false"
                                                            class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white hover:bg-purple-50 text-purple-deep transition cursor-pointer flex items-center gap-1 shadow-xs"
                                                            title="Batalkan perubahan">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                        <span>Batal</span>
                                                    </button>
                                                    <button type="submit" form="form-booking-detail" @click="prepareSubmit('step-3')"
                                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-deep hover:bg-purple-900 border border-white/40 text-white text-xs font-semibold transition cursor-pointer shadow-xs">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                                        <span>Simpan</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 space-y-3">
                                            {{-- View Mode Pembayaran --}}
                                            <div x-show="!editPembayaran" class="space-y-3">
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 space-y-1">
                                                    <span class="text-xs font-medium text-slate-500 block">Status Pembayaran Saat Ini:</span>
                                                    <span class="text-sm font-medium text-black block mt-0.5">
                                                        {{ match($booking->payment_workflow_status) {
                                                            'draft_ke_finance' => 'Pengajuan Nota (Finance)',
                                                            'menunggu_bayar' => 'Menunggu Pembayaran (VA)',
                                                            'lunas' => 'Lunas',
                                                            'lunas_overtime' => 'Lunas (Overtime)',
                                                            default => 'Belum Ada Tagihan',
                                                        } }}
                                                    </span>
                                                </div>

                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 space-y-1">
                                                    <span class="text-xs font-medium text-slate-500 block">Nomor Virtual Account / Invoice Finance:</span>
                                                    <span class="text-sm font-medium text-black block mt-0.5">{{ $booking->va_number ?: '-' }}</span>
                                                </div>
                                            </div>

                                            {{-- Edit Mode Pembayaran --}}
                                            <div x-show="editPembayaran" x-cloak class="space-y-3">
                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1.5">
                                                    <label class="block text-xs font-medium text-slate-500">Pilih Status Pembayaran</label>
                                                    <select name="payment_workflow_status" x-model="paymentStatusVal" @change="onPaymentStatusChange()"
                                                        class="w-full px-3 py-2 border border-transparent rounded-xl text-xs bg-white font-medium text-black focus:outline-none focus:ring-0">
                                                        <option value="menunggu_sesi_selesai">Belum Ada Tagihan</option>
                                                        <option value="draft_ke_finance">Pengajuan Nota (Finance)</option>
                                                        <option value="menunggu_bayar">Menunggu Pembayaran (VA)</option>
                                                        <option value="lunas">Lunas</option>
                                                        <option value="lunas_overtime">Lunas (Overtime)</option>
                                                    </select>
                                                </div>

                                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 space-y-1.5">
                                                    <label class="block text-xs font-medium text-slate-500">Nomor VA / Referensi Invoice</label>
                                                    <input type="text" name="va_number" x-model="vaNumberVal" placeholder="Contoh: 988001234567"
                                                        class="w-full px-3 py-2 border border-[#D9C2F0] rounded-xl text-xs bg-white font-medium text-black">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ========================================================= --}}
                        {{-- STEP 4: STATUS LAYANAN & CATATAN KHUSUS SESI              --}}
                        {{-- ========================================================= --}}
                        <div x-show="activeStep === 4" x-cloak class="space-y-4">

                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                <div class="px-5 py-5 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                    <h4 class="font-bold text-white text-xs sm:text-sm uppercase tracking-wider">
                                        Status Layanan & Catatan Sesi
                                    </h4>
                                    <div class="flex items-center gap-2">
                                        <button type="button" x-show="!editStatus" @click="editStatus = true"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold transition cursor-pointer">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            <span>Ubah</span>
                                        </button>
                                        <div x-show="editStatus" x-cloak class="flex items-center gap-2">
                                            <button type="button" @click="editStatus = false"
                                                    class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white hover:bg-purple-50 text-purple-deep transition cursor-pointer flex items-center gap-1 shadow-xs"
                                                    title="Batalkan perubahan">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                <span>Batal</span>
                                            </button>
                                            <button type="submit" form="form-booking-detail" @click="prepareSubmit('step-4')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-purple-deep hover:bg-purple-900 border border-white/40 text-white text-xs font-semibold transition cursor-pointer shadow-xs">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                                <span>Simpan</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-5 space-y-4">
                                    {{-- Field Status Sesi Booking --}}
                                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                                        <label class="block text-xs font-medium text-slate-500">Status Sesi Booking (Bisa Diatur Otomatis / Manual):</label>
                                        <div x-show="!editStatus">
                                            <p class="text-sm font-medium text-black mt-0.5" x-text="getStatusLabel(statusVal)"></p>
                                        </div>
                                        <div x-show="editStatus" x-cloak>
                                            <select x-model="statusVal"
                                                class="w-full px-3.5 py-2 border border-transparent rounded-xl text-sm focus:outline-none focus:ring-0 bg-white font-medium text-black">
                                                <option value="data_masuk" {{ old('status', $booking->status) === 'data_masuk' ? 'selected' : '' }}>Data Masuk</option>
                                                <option value="waiting_list" {{ old('status', $booking->status) === 'waiting_list' ? 'selected' : '' }}>Waiting List (Cari Psikolog/Jadwal)</option>
                                                <option value="menunggu_konfirmasi" {{ old('status', $booking->status) === 'menunggu_konfirmasi' ? 'selected' : '' }}>Terjadwal (Belum Konfirmasi)</option>
                                                <option value="terjadwal_gocal" {{ old('status', $booking->status) === 'terjadwal_gocal' ? 'selected' : '' }}>Terjadwal & Konfirmasi (GCal)</option>
                                                <option value="reminder_h1" {{ old('status', $booking->status) === 'reminder_h1' ? 'selected' : '' }}>Reminder & Tagihan H-1</option>
                                                <option value="perlu_bayar_overtime" {{ old('status', $booking->status) === 'perlu_bayar_overtime' ? 'selected' : '' }}>Sesi Selesai (Perlu Overtime)</option>
                                                <option value="perlu_sesi_lanjutan" {{ old('status', $booking->status) === 'perlu_sesi_lanjutan' ? 'selected' : '' }}>Sesi Selesai (Perlu Lanjutan)</option>
                                                <option value="selesai" {{ old('status', $booking->status) === 'selesai' ? 'selected' : '' }}>Sesi Selesai (Tuntas)</option>
                                                <option value="batal" {{ old('status', $booking->status) === 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Field Catatan Khusus Sesi --}}
                                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                                        <label class="block text-xs font-medium text-slate-500">Catatan Khusus Sesi / Instruksi Ruangan:</label>
                                        <div x-show="!editStatus">
                                            <p class="text-sm font-medium text-black mt-0.5 whitespace-pre-line leading-relaxed">{{ $booking->notes ?: '-' }}</p>
                                        </div>
                                        <div x-show="editStatus" x-cloak>
                                            <textarea name="notes" rows="4" placeholder="Informasi kebutuhan khusus klien, paket yang diambil, atau instruksi ruangan..."
                                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white font-medium text-black">{{ old('notes', $booking->notes) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- ========================================================= --}}
                {{-- 3. MODAL FOOTER DENGAN STEPPER NAVIGATION                 --}}
                {{-- ========================================================= --}}
                <div class="px-6 sm:px-8 py-4 bg-white border-t border-[#EDE1FA] flex items-center justify-between shrink-0 rounded-b-3xl">
                    <button type="button" 
                            x-show="activeStep > 1" 
                            @click="setStep(activeStep - 1)" 
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#D9C2F0] text-purple-deep hover:bg-purple-50 text-sm font-semibold transition cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        <span>Sebelumnya</span>
                    </button>
                    <div x-show="activeStep === 1"></div>

                    <div class="flex items-center gap-3">
                        <button type="button" 
                                x-show="activeStep < stepsCount" 
                                @click="setStep(activeStep + 1)" 
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-deep hover:bg-purple-800 text-white text-sm font-bold transition shadow-xs cursor-pointer">
                            <span>Langkah Berikutnya</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>

                        <button type="button" 
                                x-show="activeStep === stepsCount" 
                                @click="attemptClose()" 
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-deep hover:bg-purple-800 text-white text-sm font-bold transition shadow-xs cursor-pointer">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Selesai & Tutup</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SUB-MODAL: KONFIRMASI BUANG PERUBAHAN MODE EDIT AKTIF                       --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="discardModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        {{-- Backdrop (Tidak menutup modal saat luar diklik) --}}
        <div x-show="discardModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-150" 
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0" 
            class="fixed inset-0 bg-black/60 backdrop-blur-xs"></div>
        
        {{-- Dialog Container --}}
        <div x-show="discardModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95" 
            x-transition:enter-end="opacity-100 scale-100" 
            x-transition:leave="ease-in duration-150" 
            x-transition:leave-start="opacity-100 scale-100" 
            x-transition:leave-end="opacity-0 scale-95" 
            class="relative bg-white rounded-3xl shadow-2xl border border-[#EDE1FA] max-w-md w-full p-7 z-10 space-y-5"
            @click.stop>
            
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center mx-auto shadow-xs">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-purple-deep">Mode Edit Masih Aktif</h3>
                <p class="text-sm text-[#6B5B85] leading-relaxed">
                    Anda sedang mengubah data booking ini. Jika Anda keluar sekarang, perubahan yang belum disimpan akan hilang.
                </p>
            </div>

            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" @click="discardModalOpen = false" class="flex-1 px-5 py-3 rounded-xl text-sm font-semibold text-purple-deep border border-[#D9C2F0] hover:bg-[#FAF8FD] transition cursor-pointer">
                    Lanjut Mengedit
                </button>
                <button type="button" @click="closeDirectly()" class="flex-1 px-5 py-3 rounded-xl text-sm font-bold bg-amber-600 text-white hover:bg-amber-700 transition shadow-xs cursor-pointer">
                    Ya, Keluar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
