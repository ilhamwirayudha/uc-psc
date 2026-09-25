@extends('layouts.dashboard')

@section('title', 'Detail Klien — ' . $client->name . ' — UC PSC')
@section('page-title', 'Detail Klien')
@section('page-subtitle', 'Informasi lengkap profil, riwayat layanan, sesi, dokumen asesmen, dan penugasan staff')
@section('back-url', route('clients.index'))

@include('clients.partials.address-script')

@section('content')
@php
    $isGroup = ($client->jenis ?? 'individual') === 'group';
    $isCompany = ($client->jenis ?? 'individual') === 'company';
    $isIndividual = !$isGroup && !$isCompany;
    $isIndonesia = empty($client->country) || strtolower(trim($client->country)) === 'indonesia';

    $activePair = $client->pairings->where('status', 'active')->first();
    
    // Peserta tetap dari klien kelompok/perusahaan (disimpan saat registrasi klien)
    $allParticipants = $client->clientParticipants;
    $bookedCategories = $client->bookings->pluck('kategori')->filter()->unique()->values();

    // Hitung total booking dan transaksi
    $totalBookings = $client->bookings->count();
    $totalTests = $client->testResults->count();
@endphp

<div x-data="{ 
    activeTab: @js(request('tab', 'overview')),
    assignModalOpen: false,
    reassignModalOpen: false,
    unassignModalOpen: false,
    deleteModalOpen: false,
    editClientModalOpen: false,
    selectedStaffId: '{{ $client->assigned_staff_id ?? '' }}',

    confirmDiscardModalOpen: false,
    confirmDiscardSectionName: '',
    confirmDiscardTarget: null,

    askDiscardConfirmation(sectionName, onConfirmCallback) {
        this.confirmDiscardSectionName = sectionName;
        this.confirmDiscardTarget = onConfirmCallback;
        this.confirmDiscardModalOpen = true;
    },
    executeDiscard() {
        if (typeof this.confirmDiscardTarget === 'function') {
            this.confirmDiscardTarget();
        }
        this.confirmDiscardModalOpen = false;
        this.confirmDiscardTarget = null;
        this.confirmDiscardSectionName = '';
    },
    cancelDiscard() {
        this.confirmDiscardModalOpen = false;
        this.confirmDiscardTarget = null;
        this.confirmDiscardSectionName = '';
    },

    country: @js(old('country', $client->country ?? 'Indonesia')),
    province: @js(old('province', $client->province ?? '')),
    city: @js(old('city', $client->city ?? '')),

    get countryList() {
        return [...(window.addressData?.countries || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
    },
    get provinceList() {
        return [...(window.addressData?.provinces || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
    },
    get cityList() {
        if (!this.province || !window.addressData?.citiesByProvince || !window.addressData.citiesByProvince[this.province]) {
            return [];
        }
        return [...window.addressData.citiesByProvince[this.province]].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
    },
    selectCountry(val) {
        this.country = val;
        if (val !== 'Indonesia') {
            this.province = '';
            this.city = '';
        }
    },
    selectProvince(val) {
        this.province = val;
        this.city = '';
    }
}" class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- TOP CONTEXTUAL NOTIFICATION BANNERS                                       --}}
    {{-- ========================================================================= --}}





    {{-- ========================================================================= --}}
    {{-- MAIN 2-COLUMN LAYOUT: INFORMASI KLIEN (KIRI) & KONTEN OPERASIONAL (KANAN)  --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ===================================================================== --}}
        {{-- KOLOM KIRI: FORM & DATA INFORMASI KLIEN (SINGLE UNIFIED CONTAINER)     --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-4 xl:col-span-4">
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-5 sm:p-6 space-y-5">
                
                {{-- HEADER PROFILE KLIEN (HIRARKI ATAS KE BAWAH) --}}
                <div class="flex flex-col items-center text-center space-y-3 pt-1">
                    {{-- 1. Profile Picture / Avatar Paling Atas (Lingkaran Seperti Akun Admin) --}}
                    @if($isCompany)
                    <div class="w-20 h-20 rounded-full grad-purple text-white flex items-center justify-center font-bold shadow-sm ring-4 ring-[#F7F5FB]">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    @elseif($isGroup)
                    <div class="w-20 h-20 rounded-full grad-purple text-white flex items-center justify-center font-bold shadow-sm ring-4 ring-[#F7F5FB]">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    @else
                    <div class="w-20 h-20 rounded-full grad-purple text-white flex items-center justify-center font-bold text-3xl shadow-sm ring-4 ring-[#F7F5FB]">
                        {{ substr($client->name, 0, 1) }}
                    </div>
                    @endif

                    {{-- Hirarki Informasi: 1. Nama Perusahaan, 2. ID, 3. Tipe Klien --}}
                    <div class="space-y-1.5 w-full px-2">
                        {{-- 1. Nama Perusahaan --}}
                        <div class="flex items-center justify-center max-w-full">
                            <h1 class="text-base sm:text-lg font-bold text-purple-deep text-center leading-snug break-words">{{ $client->name }}</h1>
                        </div>

                        {{-- 2. ID Klien --}}
                        <div class="text-xs font-bold text-[#5B4A73]">
                            #{{ $client->id }}
                        </div>

                        {{-- 3. Tipe Klien --}}
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#F7F5FB] text-[#5B4A73] border border-[#EDE1FA]">
                                {{ $client->jenis === 'company' ? 'Perusahaan' : ($client->jenis === 'group' ? 'Kelompok' : 'Individu') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-[#EDE1FA]"></div>
                
                @if($isCompany || $isGroup)
                {{-- 1. NAMA PERWAKILAN (PIC) --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Nama Perwakilan (PIC)</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 text-xs">
                        <p class="text-black font-semibold">{{ $client->pic_name ?? '-' }}</p>
                    </div>
                </div>

                {{-- 2. DAFTAR PESERTA --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Daftar Peserta ({{ $allParticipants->count() }})</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 space-y-2 text-xs">
                        @forelse($allParticipants as $idx => $participant)
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-[#827299] w-4 shrink-0">{{ $idx + 1 }}.</span>
                            <span class="text-black font-semibold">{{ $participant->nama_peserta }}</span>
                        </div>
                        @empty
                        <div class="text-xs text-[#827299] text-center py-1">Belum ada data peserta terdaftar.</div>
                        @endforelse
                    </div>
                </div>

                {{-- 3. KONTAK & KOMUNIKASI PIC --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span>Kontak & Komunikasi</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 space-y-2.5 text-xs">
                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">No. Telepon / WhatsApp</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->phone ?? '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Email</p>
                            <p class="text-black font-semibold mt-0.5 break-all">{{ $client->email ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- 4. ALAMAT PERUSAHAAN / ALAMAT KELOMPOK --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>{{ $isCompany ? 'Alamat Perusahaan' : 'Alamat' }}</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 space-y-2.5 text-xs">
                        @if($isIndonesia)
                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Kota / Kabupaten</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->city ?: '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Provinsi</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->province ?: '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Negara</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->country ?: 'Indonesia' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Alamat Lengkap</p>
                            <p class="text-black font-semibold mt-0.5 leading-relaxed whitespace-pre-line">{{ $client->full_address }}</p>
                        </div>
                        @else
                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Negara</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->country ?: '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Alamat Lengkap</p>
                            <p class="text-black font-semibold mt-0.5 leading-relaxed whitespace-pre-line">{{ $client->full_address }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- 5. CATATAN KHUSUS --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <span>Catatan Khusus</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 text-xs text-black font-semibold leading-relaxed">
                        <p class="whitespace-pre-line">{{ trim($client->notes) ?: 'Tidak ada catatan khusus.' }}</p>
                    </div>
                </div>

                {{-- 6. STAFF PENGELOLA (PIC) & JEJAK AUDIT --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Staff Pengelola (PIC) & Audit</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 flex items-center justify-between gap-3">
                        @if($client->assignedStaff)
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ substr($client->assignedStaff->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-purple-deep truncate">{{ $client->assignedStaff->name }}</p>
                                <p class="text-[11px] text-[#827299] truncate">{{ $client->assignedStaff->email }}</p>
                            </div>
                        </div>
                        @if(auth()->user()->role === 'admin')
                        <button type="button" @click="reassignModalOpen = true" class="text-xs font-bold text-purple-deep hover:underline flex-shrink-0 cursor-pointer">
                            Alihkan
                        </button>
                        @endif
                        @else
                        <div class="text-xs text-[#827299]">Belum ada staff ditugaskan.</div>
                        @if(auth()->user()->role === 'admin')
                        <button type="button" @click="assignModalOpen = true" class="px-2.5 py-1 rounded-lg bg-purple-deep text-white text-xs font-bold hover:opacity-90 transition flex-shrink-0 cursor-pointer">
                            Tugaskan
                        </button>
                        @endif
                        @endif
                    </div>

                    {{-- Jejak Audit --}}
                    <div class="pt-3 border-t border-[#EDE1FA] text-[11px] text-black space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-[#64748B]">Dicatat oleh:</span>
                            <span class="text-black font-medium">{{ $client->creator->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-[#64748B]">Pendaftaran:</span>
                            <span class="text-black font-medium">{{ $client->created_at ? $client->created_at->format('d M Y, H:i') : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-[#64748B]">Perubahan Terakhir:</span>
                            <span class="text-black font-medium">{{ $client->updated_at ? $client->updated_at->format('d M Y, H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>

                @else
                {{-- INDIVIDU --}}
                {{-- 1. BIODATA LENGKAP --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Biodata Lengkap</span>
                        </h3>
                    </div>

                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Tanggal Lahir</p>
                            <p class="text-black font-semibold mt-0.5">
                                {{ $client->dob ? $client->dob->format('d M Y') : '-' }}
                                @if($client->dob)
                                <span class="text-[10px] font-normal text-[#827299]">({{ $client->dob->age }} Thn)</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Jenis Kelamin</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->gender_label }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Pendidikan Terakhir</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->education_label }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Status Perkawinan</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->marital_status_label }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Agama</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->religion ?: '-' }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Pekerjaan</p>
                            <p class="text-black font-semibold mt-0.5 truncate" title="{{ $client->occupation }}">{{ $client->occupation ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- 2. KONTAK & KOMUNIKASI --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span>Kontak & Komunikasi</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 space-y-2.5 text-xs">
                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">No. Telepon / WhatsApp</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->phone ?? '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Email</p>
                            <p class="text-black font-semibold mt-0.5 break-all">{{ $client->email ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- 3. ALAMAT --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>Alamat</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 space-y-2.5 text-xs">
                        @if($isIndonesia)
                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Kota / Kabupaten</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->city ?: '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Provinsi</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->province ?: '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Negara</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->country ?: 'Indonesia' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Alamat Lengkap</p>
                            <p class="text-black font-semibold mt-0.5 leading-relaxed whitespace-pre-line">{{ $client->full_address }}</p>
                        </div>
                        @else
                        <div>
                            <p class="text-[11px] font-semibold text-[#64748B]">Negara</p>
                            <p class="text-black font-semibold mt-0.5">{{ $client->country ?: '-' }}</p>
                        </div>
                        <div class="pt-2 border-t border-gray-200/60">
                            <p class="text-[11px] font-semibold text-[#64748B]">Alamat Lengkap</p>
                            <p class="text-black font-semibold mt-0.5 leading-relaxed whitespace-pre-line">{{ $client->full_address }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- 4. CATATAN KHUSUS --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <span>Catatan Khusus</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 text-xs text-black font-semibold leading-relaxed">
                        <p class="whitespace-pre-line">{{ trim($client->notes) ?: 'Tidak ada catatan khusus.' }}</p>
                    </div>
                </div>

                {{-- 5. STAFF PENGELOLA (PIC) & JEJAK AUDIT --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-purple-deep flex items-center gap-2">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Staff Pengelola (PIC) & Audit</span>
                        </h3>
                    </div>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200/80 flex items-center justify-between gap-3">
                        @if($client->assignedStaff)
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ substr($client->assignedStaff->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-purple-deep truncate">{{ $client->assignedStaff->name }}</p>
                                <p class="text-[11px] text-[#827299] truncate">{{ $client->assignedStaff->email }}</p>
                            </div>
                        </div>
                        @if(auth()->user()->role === 'admin')
                        <button type="button" @click="reassignModalOpen = true" class="text-xs font-bold text-purple-deep hover:underline flex-shrink-0 cursor-pointer">
                            Alihkan
                        </button>
                        @endif
                        @else
                        <div class="text-xs text-[#827299]">Belum ada staff ditugaskan.</div>
                        @if(auth()->user()->role === 'admin')
                        <button type="button" @click="assignModalOpen = true" class="px-2.5 py-1 rounded-lg bg-purple-deep text-white text-xs font-bold hover:opacity-90 transition flex-shrink-0 cursor-pointer">
                            Tugaskan
                        </button>
                        @endif
                        @endif
                    </div>

                    {{-- Jejak Audit --}}
                    <div class="pt-3 border-t border-[#EDE1FA] text-[11px] text-black space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-[#64748B]">Dicatat oleh:</span>
                            <span class="text-black font-medium">{{ $client->creator->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-[#64748B]">Pendaftaran:</span>
                            <span class="text-black font-medium">{{ $client->created_at ? $client->created_at->format('d M Y, H:i') : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-[#64748B]">Perubahan Terakhir:</span>
                            <span class="text-black font-medium">{{ $client->updated_at ? $client->updated_at->format('d M Y, H:i') : '-' }}</span>
                        </div>
                    </div>
                </div>
                @endif

                {{-- AKSI CONTAINER KLIEN: EDIT DATA (KIRI) & HAPUS KLIEN (KANAN) --}}
                <div class="pt-2 border-t border-[#EDE1FA] flex items-center gap-2">
                    <button type="button" @click="editClientModalOpen = true" 
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-bold text-purple-deep bg-white hover:bg-purple-deep/10 rounded-xl border border-[#D9C2F0] transition cursor-pointer text-center">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <span>Edit Data</span>
                    </button>

                    @if(auth()->user()->role === 'admin')
                    <button type="button" @click="deleteModalOpen = true" 
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold text-red-600 hover:text-red-700 hover:bg-red-50 rounded-xl border border-red-200 transition cursor-pointer text-center">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        <span>Hapus Klien</span>
                    </button>
                    @endif
                </div>

            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- KOLOM KANAN: FORM & KONTEN OPERASIONAL LAINNYA                        --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-8 xl:col-span-8 space-y-6">

            {{-- Folder-Style Tabbed Navigation Bar --}}
            <div class="relative flex items-center gap-2 border-b border-[#D9C2F0]"
                 x-data="{
                     canScrollLeft: false,
                     canScrollRight: true,
                     checkScroll() {
                         const el = this.$refs.tabScroll;
                         if (!el) return;
                         this.canScrollLeft = el.scrollLeft > 2;
                         this.canScrollRight = (el.scrollWidth - el.clientWidth - el.scrollLeft) > 2;
                     },
                     slideLeft() {
                         this.$refs.tabScroll.scrollBy({ left: -220, behavior: 'smooth' });
                         setTimeout(() => this.checkScroll(), 300);
                     },
                     slideRight() {
                         this.$refs.tabScroll.scrollBy({ left: 220, behavior: 'smooth' });
                         setTimeout(() => this.checkScroll(), 300);
                     },
                     selectTab(tab, el) {
                         this.activeTab = tab;
                         if (el) {
                             el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                         }
                         setTimeout(() => this.checkScroll(), 350);
                     }
                 }"
                 x-init="
                     const update = () => checkScroll();
                     $nextTick(update);
                     setTimeout(update, 100);
                     setTimeout(update, 300);
                     setTimeout(update, 600);
                     window.addEventListener('resize', update);
                 ">

                {{-- Tombol Slide Kiri (Prev) --}}
                <button type="button" 
                        @click="slideLeft()" 
                        :disabled="!canScrollLeft"
                        :class="canScrollLeft ? 'opacity-100 hover:bg-purple-deep hover:text-white text-purple-deep cursor-pointer border-[#D9C2F0] shadow-xs' : 'opacity-25 cursor-not-allowed text-[#827299] border-[#EDE1FA]'"
                        class="w-7 h-7 -mb-1 rounded-lg bg-white border flex items-center justify-center flex-shrink-0 transition"
                        title="Geser Pilihan ke Kiri">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                </button>

                {{-- Container Pilihan Tab (Folder Style) --}}
                <div x-ref="tabScroll" 
                     @scroll.passive="checkScroll()" 
                     class="flex-1 flex items-end gap-2 overflow-x-auto no-scrollbar scroll-smooth -mb-px px-1">
                    
                    {{-- 1. Ikhtisar & Progres --}}
                    <button type="button" @click="selectTab('overview', $el)" 
                        class="px-5 py-3 rounded-t-xl text-xs transition flex items-center gap-2 whitespace-nowrap cursor-pointer flex-shrink-0"
                        :class="activeTab === 'overview' 
                            ? 'bg-white border-t border-l border-r border-[#D9C2F0] border-b-transparent text-[#2A2035] font-bold shadow-xs' 
                            : 'text-purple-deep hover:text-purple-deep hover:bg-purple-50/50 font-semibold border-t border-l border-r border-transparent'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Ikhtisar & Progres</span>
                    </button>



                    {{-- 3. Hasil Psikotes --}}
                    <button type="button" @click="selectTab('psychotest', $el)" 
                        class="px-5 py-3 rounded-t-xl text-xs transition flex items-center gap-2 whitespace-nowrap cursor-pointer flex-shrink-0"
                        :class="activeTab === 'psychotest' 
                            ? 'bg-white border-t border-l border-r border-[#D9C2F0] border-b-transparent text-[#2A2035] font-bold shadow-xs' 
                            : 'text-purple-deep hover:text-purple-deep hover:bg-purple-50/50 font-semibold border-t border-l border-r border-transparent'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Hasil Psikotes</span>
                        @if($totalTests > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'psychotest' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-gray-100 text-[#827299]'">{{ $totalTests }}</span>
                        @endif
                    </button>

                    {{-- 4. Daftar Booking --}}
                    <button type="button" @click="selectTab('bookings', $el)" 
                        class="px-5 py-3 rounded-t-xl text-xs transition flex items-center gap-2 whitespace-nowrap cursor-pointer flex-shrink-0"
                        :class="activeTab === 'bookings' 
                            ? 'bg-white border-t border-l border-r border-[#D9C2F0] border-b-transparent text-[#2A2035] font-bold shadow-xs' 
                            : 'text-purple-deep hover:text-purple-deep hover:bg-purple-50/50 font-semibold border-t border-l border-r border-transparent'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>Daftar Booking</span>
                        @if($totalBookings > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'bookings' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-gray-100 text-[#827299]'">{{ $totalBookings }}</span>
                        @endif
                    </button>

                    {{-- 5. Peserta (Group/Company) --}}
                    @if($isCompany || $isGroup || $allParticipants->count() > 0)
                    <button type="button" @click="selectTab('participants', $el)" 
                        class="px-5 py-3 rounded-t-xl text-xs transition flex items-center gap-2 whitespace-nowrap cursor-pointer flex-shrink-0"
                        :class="activeTab === 'participants' 
                            ? 'bg-white border-t border-l border-r border-[#D9C2F0] border-b-transparent text-[#2A2035] font-bold shadow-xs' 
                            : 'text-purple-deep hover:text-purple-deep hover:bg-purple-50/50 font-semibold border-t border-l border-r border-transparent'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Peserta ({{ $allParticipants->count() }})</span>
                    </button>
                    @endif

                    {{-- 6. Riwayat Staff --}}
                    <button type="button" @click="selectTab('assignments', $el)" 
                        class="px-5 py-3 rounded-t-xl text-xs transition flex items-center gap-2 whitespace-nowrap cursor-pointer flex-shrink-0"
                        :class="activeTab === 'assignments' 
                            ? 'bg-white border-t border-l border-r border-[#D9C2F0] border-b-transparent text-[#2A2035] font-bold shadow-xs' 
                            : 'text-purple-deep hover:text-purple-deep hover:bg-purple-50/50 font-semibold border-t border-l border-r border-transparent'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        <span>Riwayat Staff</span>
                        @if($client->staffAssignmentHistory->count() > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeTab === 'assignments' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-gray-100 text-[#827299]'">{{ $client->staffAssignmentHistory->count() }}</span>
                        @endif
                    </button>
                </div>

                {{-- Tombol Slide Kanan (Next) --}}
                <button type="button" 
                        @click="slideRight()" 
                        :disabled="!canScrollRight"
                        :class="canScrollRight ? 'opacity-100 hover:bg-purple-deep hover:text-white text-purple-deep cursor-pointer border-[#D9C2F0] shadow-xs' : 'opacity-25 cursor-not-allowed text-[#827299] border-[#EDE1FA]'"
                        class="w-7 h-7 -mb-1 rounded-lg bg-white border flex items-center justify-center flex-shrink-0 transition"
                        title="Geser Pilihan ke Kanan">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>

            {{-- ================================================================= --}}
            {{-- TAB 1: IKHTISAR & PROGRES (OVERVIEW)                              --}}
            {{-- ================================================================= --}}
            <div x-show="activeTab === 'overview'" x-cloak class="space-y-6">

                @if($totalBookings === 0)
                {{-- Banner Klien Baru Terdaftar (Belum Booking) --}}
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl p-8 border border-[#EDE1FA] text-center space-y-4 shadow-xs">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-deep text-white flex items-center justify-center shadow-md">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="max-w-md mx-auto space-y-1.5">
                        <h3 class="text-base font-bold text-purple-deep">Klien Baru Terdaftar — Belum Memiliki Layanan</h3>
                        <p class="text-xs text-[#6B5B85] leading-relaxed">
                            Data identitas dan demografi klien telah tersimpan di sistem. Layanan (Konseling atau Tes Psikologi) hanya terikat ketika Anda membuat tiket booking untuk klien ini.
                        </p>
                    </div>
                    <div>
                        <button type="button" @click="$dispatch('open-booking-modal', { client_id: '{{ $client->id }}' })" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-white border border-[#D9C2F0] text-purple-deep hover:bg-purple-deep/10 transition shadow-xs cursor-pointer">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Daftarkan Layanan / Buat Booking</span>
                        </button>
                    </div>
                </div>
                @else
                {{-- Metric Counters Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl p-5 border border-[#EDE1FA] shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[#827299]">Total Reservasi</p>
                            <p class="text-2xl font-bold text-purple-deep mt-1">{{ $totalBookings }}</p>
                            <p class="text-[11px] text-[#827299] mt-0.5">Booking Terdaftar</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-purple-deep/10 text-purple-deep flex items-center justify-center">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                    </div>



                    <div class="bg-white rounded-2xl p-5 border border-[#EDE1FA] shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-[#827299]">Hasil Psikotes</p>
                            <p class="text-2xl font-bold text-purple-deep mt-1">{{ $totalTests }}</p>
                            <p class="text-[11px] text-[#827299] mt-0.5">Dokumen Laporan</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Card Konselor yang Dipasangkan (*Active Pairing*) --}}
                @if($activePair)
                <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-purple-deep">Konselor yang Dipasangkan (*Pairing*)</h3>
                            <p class="text-xs text-[#827299]">Konselor penanggung jawab utama bimbingan konseling</p>
                        </div>
                        @if($activePair)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">Aktif</span>
                        @endif
                    </div>
                    <div class="p-6">
                        @if($activePair && $activePair->counselor)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#FAF8FD] p-4 rounded-xl border border-[#EDE1FA]">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-xl grad-purple text-white flex items-center justify-center font-bold text-lg shadow-xs flex-shrink-0">
                                    {{ substr($activePair->counselor->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-purple-deep">{{ $activePair->counselor->name }}</h4>
                                    <p class="text-xs text-[#6B5B85]">{{ $activePair->counselor->specialization ?? 'Psikolog Klinis / Konselor' }}</p>
                                    <p class="text-[11px] text-[#827299] mt-0.5">
                                        Dipasangkan oleh {{ $activePair->assigner->name ?? 'Admin' }}
                                        @if($activePair->created_at) · {{ $activePair->created_at->format('d M Y') }} @endif
                                    </p>
                                </div>
                            </div>

                        </div>
                        @else
                        <div class="text-center py-6">
                            <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-[#2A2035]">Belum ada konselor yang dipasangkan</p>
                            <p class="text-xs text-[#827299] mt-0.5">Buat booking sesi konseling untuk menugaskan konselor.</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif



            </div>

            {{-- ================================================================= --}}
            {{-- TAB 3: HASIL PSIKOTES (PSYCHOLOGICAL TESTS)                       --}}
            {{-- ================================================================= --}}
            <div x-show="activeTab === 'psychotest'" x-cloak class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-purple-deep">Laporan Hasil Psikotes (*Psychological Report*)</h3>
                            <p class="text-xs text-[#827299]">Dokumen hasil psikotes, interpretasi, dan rekomendasi psikolog</p>
                        </div>
                        <button type="button" @click="$dispatch('open-test-result-modal', { client_id: {{ $client->id }} })" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-xs cursor-pointer">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Upload Hasil Psikotes</span>
                        </button>
                    </div>

                    <div class="divide-y divide-[#F3EAFB]">
                        @forelse($client->testResults->sortByDesc('created_at') as $test)
                        <div class="p-5 hover:bg-[#FAF8FD] transition space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-orange/15 text-orange flex items-center justify-center flex-shrink-0 shadow-xs">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('test-results.show', $test) }}" class="text-sm font-bold text-[#2A2035] hover:text-purple-deep hover:underline">
                                                {{ $test->test_name }}
                                            </a>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $test->status_badge_class }}">
                                                {{ $test->status_label }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $test->method === 'online' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }}">
                                                {{ $test->method_label }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-[#827299] mt-0.5">
                                            Pelaksanaan: <strong class="text-[#2A2035]">{{ $test->tested_at ? $test->tested_at->format('d M Y') : '-' }}</strong>
                                            · Dicatat oleh: {{ $test->administrator->name ?? 'Staff' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 self-start sm:self-auto">
                                    <a href="{{ route('test-results.show', $test) }}" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-xs">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <span>Detail</span>
                                    </a>

                                    @if($test->file_path)
                                    <a href="{{ route('test-results.download', $test) }}" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-orange/15 text-orange hover:bg-orange hover:text-white transition">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        <span>Unduh Laporan</span>
                                    </a>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-[#F3EAFB] text-[11px]">
                                <div>
                                    <span class="text-[#827299] block">Reviewer / Koreksi:</span>
                                    <strong class="text-[#2A2035]">{{ $test->staff_koreksi->name ?? $test->staff_pelapor->name ?? 'Belum diassign' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[#827299] block">Staff Pelapor:</span>
                                    <strong class="text-[#2A2035]">{{ $test->staff_pelapor->name ?? 'Belum diassign' }}</strong>
                                </div>
                                <div>
                                    <span class="text-[#827299] block">Target Hasil:</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] border {{ $test->sla_badge['class'] }}">
                                        {{ $test->sla_badge['label'] }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[#827299] block">Status Penyerahan:</span>
                                    @if($test->delivered_at)
                                    <strong class="text-emerald-700">Terkirim ({{ $test->delivery_method_label }})</strong>
                                    @elseif($test->status === \App\Models\TestResult::STATUS_RESULT_READY)
                                    <strong class="text-amber-700">Siap Dikirim</strong>
                                    @else
                                    <strong class="text-[#827299]">Dalam Proses</strong>
                                    @endif
                                </div>
                            </div>

                            @if($test->result_summary)
                            <p class="text-[11px] text-[#6B5B85] bg-[#F7F5FB] p-2.5 rounded-xl border border-[#EDE1FA] leading-relaxed">
                                {{ $test->result_summary }}
                            </p>
                            @endif
                        </div>
                        @empty
                        <div class="px-6 py-10 text-center text-[#827299] text-xs">
                            <p class="font-semibold text-[#2A2035] mb-1">Belum ada dokumen hasil psikotes yang terdaftar</p>
                            <p>Klik tombol "+ Upload Hasil Psikotes" di atas untuk menambahkan laporan psikotes.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ================================================================= --}}
            {{-- TAB 4: DAFTAR BOOKING & TRANSAKSI                                 --}}
            {{-- ================================================================= --}}
            <div x-show="activeTab === 'bookings'" x-cloak class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-purple-deep">Riwayat Reservasi & Booking</h3>
                            <p class="text-xs text-[#827299]">Daftar tiket booking dan administrasi transaksi klien</p>
                        </div>
                        <a href="{{ route('bookings.create', ['client_id' => $client->id]) }}" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-xs">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Buat Booking</span>
                        </a>
                    </div>

                    <div class="divide-y divide-[#F3EAFB]">
                        @forelse($client->bookings->sortByDesc('created_at') as $booking)
                        <div class="p-5 hover:bg-[#FAF8FD] transition space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase
                                        {{ $booking->kategori === 'konseling' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-orange/10 text-orange' }}">
                                        {{ $booking->kategori }}
                                    </span>
                                    <a href="{{ route('bookings.show', $booking) }}" class="text-xs font-bold text-purple-deep hover:underline">
                                        Booking #{{ $booking->id }}
                                    </a>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $booking->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 
                                          ($booking->status === 'baru' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ str_replace('_', ' ', $booking->status) }}
                                    </span>
                                    <a href="{{ route('bookings.show', $booking) }}" class="p-1.5 text-[#827299] hover:text-purple-deep hover:bg-purple-deep/10 rounded-lg transition" title="Detail Booking">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-2 text-[11px] text-[#6B5B85] pt-1">
                                <div>
                                    <p>Tanggal Dibuat: <strong class="text-[#2A2035]">{{ $booking->tanggal_booking_dibuat ? $booking->tanggal_booking_dibuat->format('d M Y') : '-' }}</strong></p>
                                    <p>Tanggal Pelaksanaan: <strong class="text-[#2A2035]">{{ $booking->tanggal_dijadwalkan ? $booking->tanggal_dijadwalkan->format('d M Y') : '-' }}</strong></p>
                                </div>
                                <div>
                                    @if($booking->counselor)
                                    <p>Konselor: <strong class="text-purple-deep">{{ $booking->counselor->name }}</strong></p>
                                    @elseif($booking->staffPenguji)
                                    <p>Staff Penguji: <strong class="text-purple-deep">{{ $booking->staffPenguji->name }}</strong></p>
                                    @endif

                                    @if($booking->paymentTransaction)
                                    <p>Pembayaran: <strong class="text-[#2A2035]">Rp {{ number_format($booking->paymentTransaction->jumlah, 0, ',', '.') }}</strong>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold {{ $booking->paymentTransaction->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ ucfirst($booking->paymentTransaction->status) }}
                                        </span>
                                    </p>
                                    @endif
                                </div>
                            </div>

                            @if($booking->followUpOf)
                            <p class="text-[10px] text-purple-deep font-semibold">
                                ↳ Follow-up dari: <a href="{{ route('bookings.show', $booking->followUpOf) }}" class="underline">Booking #{{ $booking->follow_up_of_booking_id }}</a>
                            </p>
                            @endif
                        </div>
                        @empty
                        <div class="px-6 py-10 text-center text-[#827299] text-xs">
                            <p class="font-semibold text-[#2A2035] mb-1">Belum ada riwayat booking</p>
                            <p>Klik tombol "+ Buat Booking" di atas untuk membuat reservasi layanan baru.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ================================================================= --}}
            {{-- TAB 5: PESERTA / ANGGOTA (KHUSUS GROUP & COMPANY)                 --}}
            {{-- ================================================================= --}}
            @if($isCompany || $isGroup || $allParticipants->count() > 0)
            <div x-show="activeTab === 'participants'" x-cloak class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-purple-deep">{{ $isCompany ? 'Daftar Peserta / Anggota Asesmen' : 'Daftar Anggota / Peserta Terdaftar' }}</h3>
                            <p class="text-xs text-[#827299]">{{ $isCompany ? 'Kandidat karyawan terdaftar dalam asesmen korporat ini' : 'Daftar peserta yang terdata dalam kelompok / bimbingan' }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-deep/10 text-purple-deep">
                            {{ $allParticipants->count() }} Orang
                        </span>
                    </div>

                    <div class="divide-y divide-[#F3EAFB] max-h-96 overflow-y-auto custom-scrollbar">
                        @forelse($allParticipants as $participant)
                        <div class="px-6 py-3 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                            <div class="flex items-center gap-3">
                                <span class="w-6 text-center text-xs font-bold text-purple-deep">{{ $loop->iteration }}.</span>
                                <div class="w-8 h-8 rounded-full bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ substr($participant->nama_peserta, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-[#2A2035]">{{ $participant->nama_peserta }}</p>
                                    <p class="text-[10px] text-[#827299]">Grup/Instansi: {{ $client->name }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                Terdaftar
                            </span>
                        </div>
                        @empty
                        <div class="px-6 py-10 text-center text-[#827299] text-xs">
                            Belum ada nama peserta yang terdaftar pada booking klien ini.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            {{-- ================================================================= --}}
            {{-- TAB 6: RIWAYAT PENUGASAN STAFF                                    --}}
            {{-- ================================================================= --}}
            <div x-show="activeTab === 'assignments'" x-cloak class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-purple-deep">Riwayat Penugasan Staff Pengelola</h3>
                            <p class="text-xs text-[#827299]">Log pencatatan penugasan staff penanggung jawab klien</p>
                        </div>
                        @if(auth()->user()->role === 'admin')
                        <button type="button" @click="assignModalOpen = true" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-xs cursor-pointer">
                            + Tugaskan Staff
                        </button>
                        @endif
                    </div>

                    <div class="divide-y divide-[#F3EAFB]">
                        @forelse($client->staffAssignmentHistory->sortByDesc('assigned_at') as $assignment)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                            <div class="flex items-center gap-3.5">
                                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ substr($assignment->staff->name ?? 'S', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-[#2A2035]">{{ $assignment->staff->name ?? '-' }}</p>
                                    <p class="text-[11px] text-[#827299]">
                                        Ditugaskan: {{ $assignment->assigned_at ? $assignment->assigned_at->format('d M Y, H:i') : '-' }}
                                        @if($assignment->assigner) · oleh {{ $assignment->assigner->name }} @endif
                                    </p>
                                    @if($assignment->notes)
                                    <p class="text-[10px] text-[#6B5B85] mt-0.5 italic">"{{ $assignment->notes }}"</p>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @if($assignment->ended_at)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500">
                                    Selesai ({{ $assignment->ended_at->format('d M Y') }})
                                </span>
                                @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    Penugasan Aktif
                                </span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="px-6 py-10 text-center text-[#827299] text-xs">
                            Belum ada riwayat penugasan staff untuk klien ini.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODALS: PENUGASAN STAFF & DELETE CONFIRMATION                             --}}
    {{-- ========================================================================= --}}

    {{-- 1. Modal Tugaskan Staff Baru --}}
    @if(auth()->user()->role === 'admin')
    <div x-cloak x-show="assignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="assignModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="assignModalOpen = false"></div>
        <div x-show="assignModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#EDE1FA]">
                <h3 class="text-base font-bold text-purple-deep">Tugaskan Staff Pengelola</h3>
                <button type="button" @click="assignModalOpen = false" class="text-[#827299] hover:text-purple-deep cursor-pointer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form action="{{ route('assignments.assign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pilih Staff Penanggung Jawab <span class="text-red-500">*</span></label>
                    <select name="staff_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white text-[#2A2035]">
                        <option value="">-- Pilih Staff --</option>
                        @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Catatan Penugasan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan atau instruksi penugasan untuk staff..." class="w-full px-3.5 py-2 rounded-xl border border-[#D9C2F0] text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#FAF8FD] cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-xs cursor-pointer">Simpan Penugasan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. Modal Ganti / Alihkan Staff --}}
    <div x-cloak x-show="reassignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="reassignModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="reassignModalOpen = false"></div>
        <div x-show="reassignModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#EDE1FA]">
                <h3 class="text-base font-bold text-purple-deep">Alihkan Staff Pengelola</h3>
                <button type="button" @click="reassignModalOpen = false" class="text-[#827299] hover:text-purple-deep cursor-pointer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form action="{{ route('assignments.reassign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div class="bg-[#FAF8FD] p-3 rounded-xl border border-[#EDE1FA] text-xs">
                    <p class="text-[#827299]">Staff Saat Ini:</p>
                    <p class="font-bold text-purple-deep">{{ $client->assignedStaff->name ?? 'Belum Ditugaskan' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pilih Staff Pengganti <span class="text-red-500">*</span></label>
                    <select name="staff_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white text-[#2A2035]">
                        <option value="">-- Pilih Staff Baru --</option>
                        @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}" {{ $client->assigned_staff_id === $staff->id ? 'disabled' : '' }}>
                            {{ $staff->name }} ({{ $staff->email }}) {{ $client->assigned_staff_id === $staff->id ? '(Saat ini)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Alasan Pengalihan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Alasan pengalihan staff..." class="w-full px-3.5 py-2 rounded-xl border border-[#D9C2F0] text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep"></textarea>
                </div>

                <div class="flex items-center justify-between gap-2 pt-2">
                    <button type="button" @click="reassignModalOpen = false; unassignModalOpen = true" class="text-xs font-semibold text-red-600 hover:underline cursor-pointer">
                        Lepaskan Staff
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="reassignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#FAF8FD] cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-xs cursor-pointer">Simpan Pengalihan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. Modal Lepaskan Staff --}}
    <div x-cloak x-show="unassignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="unassignModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="unassignModalOpen = false"></div>
        <div x-show="unassignModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-bold text-red-600">Lepaskan Penugasan Staff</h3>
            <p class="text-xs text-[#6B5B85]">Klien <strong>{{ $client->name }}</strong> akan dikembalikan ke status belum ditugaskan (unassigned).</p>

            <form action="{{ route('assignments.unassign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Catatan Pelepasan (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan pelepasan penugasan..." class="w-full px-3.5 py-2 rounded-xl border border-[#D9C2F0] text-xs focus:outline-none focus:ring-2 focus:ring-purple-deep"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="unassignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#FAF8FD] cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">Ya, Lepaskan Staff</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. Modal Konfirmasi Hapus Klien --}}
    <div x-cloak x-show="deleteModalOpen" @keydown.escape.window="deleteModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="deleteModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="deleteModalOpen = false"></div>
        <div x-show="deleteModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </div>
            
            <div class="text-center space-y-1.5">
                <h3 class="text-base font-bold text-red-600">Hapus Data Klien?</h3>
                <p class="text-xs text-[#6B5B85] leading-relaxed">
                    Apakah Anda yakin ingin menghapus data klien <strong>{{ $client->name }}</strong> (ID: #{{ $client->id }})? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.
                </p>
            </div>

            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="flex items-center justify-center gap-2 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#FAF8FD] cursor-pointer">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 text-white hover:bg-red-700 transition shadow-xs cursor-pointer">Ya, Hapus Klien</button>
            </form>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL POPUP EDIT DATA KLIEN (VERSI KOMPAK RELEVAN SESUAI TIPE)            --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="editClientModalOpen" 
         x-data="{
              name: @js(old('name', $client->name ?? '')),
              pic_name: @js(old('pic_name', $client->pic_name ?? '')),
              participants: @js($allParticipants->pluck('nama_peserta')->values()->toArray() ?: ['', '']),
              dob: @js(old('dob', $client->dob ? $client->dob->format('Y-m-d') : '')),
              gender: @js(old('gender', $client->gender ?? '')),
              religion: @js(old('religion', $client->religion ?? '')),
              marital_status: @js(old('marital_status', $client->marital_status ?? '')),
              education: @js(old('education', $client->education ?? '')),
              occupation: @js(old('occupation', $client->occupation ?? '')),
              phone: @js(old('phone', $client->phone ?? '')),
              email: @js(old('email', $client->email ?? '')),
              country: @js(old('country', $client->country ?? 'Indonesia')),
              province: @js(old('province', $client->province ?? '')),
              city: @js(old('city', $client->city ?? '')),
              address: @js(old('address', $client->address ?? '')),
              notes: @js(old('notes', $client->notes ?? '')),

              initialName: @js(old('name', $client->name ?? '')),
              initialPicName: @js(old('pic_name', $client->pic_name ?? '')),
              initialParticipants: @js($allParticipants->pluck('nama_peserta')->values()->toArray() ?: ['', '']),
              initialDob: @js(old('dob', $client->dob ? $client->dob->format('Y-m-d') : '')),
              initialGender: @js(old('gender', $client->gender ?? '')),
              initialReligion: @js(old('religion', $client->religion ?? '')),
              initialMaritalStatus: @js(old('marital_status', $client->marital_status ?? '')),
              initialEducation: @js(old('education', $client->education ?? '')),
              initialOccupation: @js(old('occupation', $client->occupation ?? '')),
              initialPhone: @js(old('phone', $client->phone ?? '')),
              initialEmail: @js(old('email', $client->email ?? '')),
              initialCountry: @js(old('country', $client->country ?? 'Indonesia')),
              initialProvince: @js(old('province', $client->province ?? '')),
              initialCity: @js(old('city', $client->city ?? '')),
              initialAddress: @js(old('address', $client->address ?? '')),
              initialNotes: @js(old('notes', $client->notes ?? '')),

              countryOpen: false,
              countrySearch: '',
              provinceOpen: false,
              provinceSearch: '',
              cityOpen: false,
              citySearch: '',

              get isDirty() {
                  if (this.name !== this.initialName) return true;
                  if (this.pic_name !== this.initialPicName) return true;
                  if (this.dob !== this.initialDob) return true;
                  if (this.gender !== this.initialGender) return true;
                  if (this.religion !== this.initialReligion) return true;
                  if (this.marital_status !== this.initialMaritalStatus) return true;
                  if (this.education !== this.initialEducation) return true;
                  if (this.occupation !== this.initialOccupation) return true;
                  if (this.phone !== this.initialPhone) return true;
                  if (this.email !== this.initialEmail) return true;
                  if (this.country !== this.initialCountry) return true;
                  if (this.province !== this.initialProvince) return true;
                  if (this.city !== this.initialCity) return true;
                  if (this.address !== this.initialAddress) return true;
                  if (this.notes !== this.initialNotes) return true;
                  if (JSON.stringify(this.participants) !== JSON.stringify(this.initialParticipants)) return true;
                  return false;
              },

              resetForm() {
                  this.name = this.initialName;
                  this.pic_name = this.initialPicName;
                  this.participants = JSON.parse(JSON.stringify(this.initialParticipants));
                  this.dob = this.initialDob;
                  this.gender = this.initialGender;
                  this.religion = this.initialReligion;
                  this.marital_status = this.initialMaritalStatus;
                  this.education = this.initialEducation;
                  this.occupation = this.initialOccupation;
                  this.phone = this.initialPhone;
                  this.email = this.initialEmail;
                  this.country = this.initialCountry;
                  this.province = this.initialProvince;
                  this.city = this.initialCity;
                  this.address = this.initialAddress;
                  this.notes = this.initialNotes;
                  this.countryOpen = false;
                  this.provinceOpen = false;
                  this.cityOpen = false;
              },

              requestClose() {
                  if (this.isDirty) {
                      this.askDiscardConfirmation('Data Klien', () => {
                          this.resetForm();
                          this.editClientModalOpen = false;
                      });
                  } else {
                      this.editClientModalOpen = false;
                  }
              },

              get modalCountryList() {
                  const list = [...(window.addressData?.countries || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
                  if (!this.countrySearch) return list;
                  return list.filter(c => c.toLowerCase().includes(this.countrySearch.toLowerCase()));
              },
              get modalProvinceList() {
                  const list = [...(window.addressData?.provinces || [])].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
                  if (!this.provinceSearch) return list;
                  return list.filter(p => p.toLowerCase().includes(this.provinceSearch.toLowerCase()));
              },
              get modalCityList() {
                  if (!this.province || !window.addressData?.citiesByProvince || !window.addressData.citiesByProvince[this.province]) {
                      return [];
                  }
                  const list = [...window.addressData.citiesByProvince[this.province]].sort((a, b) => a.localeCompare('id', { sensitivity: 'base' }));
                  if (!this.citySearch) return list;
                  return list.filter(c => c.toLowerCase().includes(this.citySearch.toLowerCase()));
              },

              openCountry() {
                  this.countryOpen = true;
                  this.countrySearch = '';
                  this.provinceOpen = false;
                  this.cityOpen = false;
                  this.$nextTick(() => { this.$refs.modalCountrySearch && this.$refs.modalCountrySearch.focus(); });
              },
              selectCountry(val) {
                  this.country = val;
                  this.countryOpen = false;
                  this.countrySearch = '';
                  if (val !== 'Indonesia') {
                      this.province = '';
                      this.provinceSearch = '';
                      this.provinceOpen = false;
                      this.city = '';
                      this.citySearch = '';
                      this.cityOpen = false;
                  }
              },
              openProvince() {
                  if (this.country !== 'Indonesia') return;
                  this.provinceOpen = true;
                  this.provinceSearch = '';
                  this.countryOpen = false;
                  this.cityOpen = false;
                  this.$nextTick(() => { this.$refs.modalProvinceSearch && this.$refs.modalProvinceSearch.focus(); });
              },
              selectProvince(val) {
                  this.province = val;
                  this.provinceOpen = false;
                  this.provinceSearch = '';
                  this.city = '';
                  this.citySearch = '';
              },
              openCity() {
                  if (this.country !== 'Indonesia' || !this.province) return;
                  this.cityOpen = true;
                  this.citySearch = '';
                  this.countryOpen = false;
                  this.provinceOpen = false;
                  this.$nextTick(() => { this.$refs.modalCitySearch && this.$refs.modalCitySearch.focus(); });
              },
              selectCity(val) {
                  this.city = val;
                  this.cityOpen = false;
                  this.citySearch = '';
              },

              addParticipant() {
                  this.participants.push('');
              },
              removeParticipant(index) {
                  if (this.participants.length > 2) {
                      this.participants.splice(index, 1);
                  }
              },

              capitalizeInput(e) {
                  const el = e.target;
                  const start = el.selectionStart;
                  const end = el.selectionEnd;
                  const prevLen = el.value.length;
                  el.value = el.value.replace(/[0-9]/g, '');
                  const diff = prevLen - el.value.length;
                  el.value = el.value.replace(/\b\w/g, c => c.toUpperCase());
                  const modelName = el.getAttribute('name');
                  if (modelName && this.hasOwnProperty(modelName)) {
                      this[modelName] = el.value;
                  }
                  el.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
              }
         }"
         @keydown.escape.window="if (editClientModalOpen && !confirmDiscardModalOpen) requestClose()" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
        <div x-show="editClientModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="requestClose()"></div>
        
        <div x-show="editClientModalOpen" 
             x-transition:enter="ease-out duration-200" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100" 
             class="relative bg-[#FAF8FD] rounded-3xl shadow-2xl border border-[#EDE1FA] max-w-3xl w-full p-5 sm:p-7 z-10 max-h-[92vh] overflow-y-auto custom-scrollbar space-y-4">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3.5 border-b border-[#EDE1FA]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-deep text-white flex items-center justify-center font-bold shadow-xs">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-purple-deep">Edit Data Klien</h3>
                        <p class="text-xs text-[#827299]">
                            {{ $isCompany ? 'Perbarui data instansi, kontak PIC, peserta, dan alamat' : ($isGroup ? 'Perbarui data kelompok, kontak PIC, anggota, dan alamat' : 'Perbarui data identitas, kontak, dan alamat klien') }}
                        </p>
                    </div>
                </div>
                <button type="button" @click="requestClose()" class="text-[#827299] hover:text-purple-deep p-2 rounded-xl hover:bg-white transition cursor-pointer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form action="{{ route('clients.update', $client) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- CARD 1: IDENTITAS, DEMOGRAFI & KONTAK --}}
                <div class="bg-white rounded-2xl shadow-xs border border-[#EDE1FA] p-5 space-y-4">
                    <div class="pb-2.5 border-b border-[#EDE1FA]">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-purple-deep">Data Identitas & Kontak</h3>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        {{-- Nama Klien / Perusahaan / Kelompok --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                                {{ $isCompany ? 'Nama Perusahaan / Instansi' : ($isGroup ? 'Nama Kelompok / Keluarga' : 'Nama Lengkap Klien') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" x-model="name" required
                                @input="capitalizeInput($event)"
                                @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                placeholder="{{ $isCompany ? 'Contoh: PT Ciputra Mitra Tbk' : ($isGroup ? 'Contoh: Pasangan Anton & Siti / Keluarga Bpk. Hendra' : 'Contoh: Ahmad Fauzi Pratama') }}"
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                        </div>

                        @if($isCompany || $isGroup)
                        {{-- Nama PIC --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                                {{ $isGroup ? 'Nama PIC / Perwakilan Kelompok' : 'Nama PIC / Kontak Perwakilan Instansi' }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="pic_name" x-model="pic_name" required
                                @input="capitalizeInput($event)"
                                @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                placeholder="{{ $isGroup ? 'Contoh: Bpk. Hendra (Kepala Keluarga)' : 'Contoh: Ibu Maria (HR Manager)' }}"
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                        </div>

                        {{-- Kontak PIC (No. Telepon & Email) --}}
                        <div>
                            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp PIC <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" x-model="phone" required maxlength="13"
                                @input="phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = phone"
                                placeholder="Contoh: 081234567890 (10-13 digit)"
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Alamat Email PIC <span class="text-red-500">*</span></label>
                            <input type="email" name="email" x-model="email" required
                                @input="email = $el.value.toLowerCase(); $el.value = email"
                                placeholder="{{ $isCompany ? 'pic@instansi.com' : 'pic@email.com' }}"
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                        </div>

                        {{-- DAFTAR PESERTA / ANGGOTA (DI BAWAH KONTAK PIC) --}}
                        <div class="sm:col-span-2 p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                            <div class="flex items-center justify-between pb-1.5 border-b border-gray-200">
                                <label class="block text-sm font-bold text-purple-deep">
                                    {{ $isCompany ? 'Daftar Karyawan / Peserta Instansi' : 'Daftar Anggota / Peserta Kelompok' }} <span class="text-red-500">*</span>
                                </label>
                                <span class="text-xs text-[#827299]">Minimal 2 orang</span>
                            </div>

                            <div class="space-y-2.5">
                                <template x-for="(p, index) in participants" :key="index">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-6 text-sm text-center font-bold text-[#6B5B85]" x-text="(index + 1) + '.'"></span>
                                        <input type="text" name="participants[]" x-model="participants[index]" required 
                                            @input="participants[index] = $event.target.value.replace(/[0-9]/g, '').replace(/\b\w/g, c => c.toUpperCase())"
                                            @keydown="if ($event.key >= '0' && $event.key <= '9') $event.preventDefault()"
                                            placeholder="{{ $isCompany ? 'Nama lengkap karyawan / kandidat...' : 'Nama lengkap anggota keluarga / pasangan...' }}" 
                                            class="flex-1 px-4 py-2.5 bg-white rounded-xl border border-gray-300 focus:border-[#D9C2F0] text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                        <button type="button" @click="removeParticipant(index)" :disabled="participants.length <= 2" 
                                            :class="participants.length <= 2 ? 'opacity-30 cursor-not-allowed text-gray-400' : 'text-red-500 hover:text-red-700 cursor-pointer'" 
                                            class="p-2 rounded-lg transition" title="Hapus peserta">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div class="flex justify-center pt-1.5">
                                <button type="button" @click="addParticipant()" 
                                    class="flex items-center gap-2 px-3.5 py-2 border border-dashed border-gray-300 rounded-lg text-sm font-semibold text-purple-deep hover:border-purple-deep hover:bg-purple-deep/5 transition cursor-pointer">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>Tambah Peserta</span>
                                </button>
                            </div>
                        </div>
                        @else
                        {{-- KHUSUS INDIVIDU: LAYOUT 2 KOLOM --}}
                        <div class="sm:col-span-2 grid sm:grid-cols-2 gap-4">
                            {{-- KOLOM KIRI --}}
                            <div class="space-y-4">
                                {{-- 1. Tanggal Lahir --}}
                                <div>
                                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                                    <input type="date" name="dob" x-model="dob" required 
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                </div>

                                {{-- 2. Jenis Kelamin --}}
                                <div>
                                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <select name="gender" x-model="gender" required 
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="l">Laki-laki</option>
                                        <option value="p">Perempuan</option>
                                        <option value="non_binary">Non-binary</option>
                                        <option value="transgender">Transgender</option>
                                        <option value="prefer_not_to_say">Memilih tidak menyebutkan</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>

                                {{-- 3. Agama --}}
                                <div>
                                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Agama <span class="text-red-500">*</span></label>
                                    <select name="religion" x-model="religion" required 
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                        <option value="">-- Pilih Agama --</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen Protestan</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Khonghucu">Khonghucu</option>
                                        <option value="Penghayat Kepercayaan">Penghayat Kepercayaan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>

                                {{-- 4. Status Perkawinan --}}
                                <div>
                                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Status Perkawinan <span class="text-red-500">*</span></label>
                                    <select name="marital_status" x-model="marital_status" required 
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                        <option value="">-- Pilih Status Perkawinan --</option>
                                        <option value="belum_menikah">Belum Menikah (Lajang)</option>
                                        <option value="menikah">Menikah</option>
                                        <option value="cerai_hidup">Cerai Hidup</option>
                                        <option value="cerai_mati">Cerai Mati</option>
                                    </select>
                                </div>
                            </div>

                            {{-- KOLOM KANAN --}}
                            <div class="space-y-4">
                                {{-- 1. Pendidikan Terakhir --}}
                                <div>
                                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                                    <select name="education" x-model="education" required 
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                        <option value="">-- Pilih Pendidikan Terakhir --</option>
                                        <option value="sd">SD / Sederajat</option>
                                        <option value="smp">SMP / Sederajat</option>
                                        <option value="sma_smk">SMA / SMK / Sederajat</option>
                                        <option value="d3">Diploma / D3</option>
                                        <option value="s1">Sarjana / S1 / D4</option>
                                        <option value="s2">Magister / S2</option>
                                        <option value="s3">Doktor / S3</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>

                                {{-- 2. Pekerjaan --}}
                                <div>
                                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Pekerjaan</label>
                                    <select name="occupation" x-model="occupation" 
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                        <option value="">-- Pilih Pekerjaan --</option>
                                        <option value="Belum Bekerja">Belum Bekerja</option>
                                        <option value="Pelajar">Pelajar</option>
                                        <option value="Mahasiswa">Mahasiswa</option>
                                        <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)">Penyedia Jasa (Guru/Dokter/Lawyer/Peneliti/Lainnya)</option>
                                        <option value="Freelance">Freelance</option>
                                        <option value="Karyawan Swasta">Karyawan Swasta</option>
                                        <option value="Pegawai Negeri Sipil">Pegawai Negeri Sipil</option>
                                        <option value="BUMN">BUMN</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>

                                {{-- 3. No. Telepon / WhatsApp --}}
                                <div>
                                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">No. Telepon / WhatsApp <span class="text-red-500">*</span></label>
                                    <input type="text" name="phone" x-model="phone" required maxlength="13"
                                        @input="phone = $el.value.replace(/[^0-9]/g, '').slice(0, 13); $el.value = phone"
                                        placeholder="Contoh: 081234567890 (10-13 digit)"
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                </div>

                                {{-- 4. Email --}}
                                <div>
                                    <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Email <span class="text-red-500">*</span></label>
                                    <input type="email" name="email" x-model="email" required
                                        @input="email = $el.value.toLowerCase(); $el.value = email"
                                        placeholder="klien@email.com"
                                        class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- CARD 2: DATA ALAMAT --}}
                <div class="bg-white rounded-2xl shadow-xs border border-[#EDE1FA] p-5 space-y-4">
                    <div class="pb-2.5 border-b border-[#EDE1FA]">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-purple-deep">{{ $isCompany ? 'Alamat Perusahaan' : 'Alamat' }}</h3>
                    </div>

                    <div class="grid sm:grid-cols-3 gap-4">
                        {{-- 1. NEGARA --}}
                        <div class="relative" @click.outside="countryOpen = false">
                            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Negara <span class="text-red-500">*</span></label>
                            <input type="hidden" name="country" :value="country" required>
                            
                            <button type="button" @click="countryOpen ? (countryOpen = false) : openCountry()" 
                                class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition bg-white border border-[#D9C2F0] hover:border-[#B59BD6] focus:outline-none focus:ring-2 focus:ring-purple-deep cursor-pointer text-[#2A2035]">
                                <span x-text="country || '-- Pilih Negara --'"></span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': countryOpen }">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>

                            <div x-cloak x-show="countryOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                    <input type="text" x-ref="modalCountrySearch" x-model="countrySearch" placeholder="Cari negara..." class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                </div>
                                <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                    <template x-for="c in modalCountryList" :key="c">
                                        <div @click="selectCountry(c)" class="px-4 py-2.5 text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': country === c, 'text-[#2A2035]': country !== c }">
                                            <span x-text="c"></span>
                                            <svg x-show="country === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- 2. PROVINSI --}}
                        <div class="relative" @click.outside="provinceOpen = false">
                            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                                Provinsi 
                                <span x-show="country === 'Indonesia'" class="text-red-500">*</span>
                                <span x-show="country !== 'Indonesia'" class="text-xs font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                            </label>
                            <input type="hidden" name="province" :value="country === 'Indonesia' ? province : ''">

                            <button type="button" :disabled="country !== 'Indonesia'" @click="country === 'Indonesia' && (provinceOpen ? (provinceOpen = false) : openProvince())" 
                                class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                                :class="country === 'Indonesia' ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                                <span x-text="country === 'Indonesia' ? (province || '-- Pilih Provinsi --') : '-'"></span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': provinceOpen }">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>

                            <div x-cloak x-show="country === 'Indonesia' && provinceOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                    <input type="text" x-ref="modalProvinceSearch" x-model="provinceSearch" placeholder="Cari provinsi..." class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                </div>
                                <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                    <template x-for="p in modalProvinceList" :key="p">
                                        <div @click="selectProvince(p)" class="px-4 py-2.5 text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': province === p, 'text-[#2A2035]': province !== p }">
                                            <span x-text="p"></span>
                                            <svg x-show="province === p" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- 3. KOTA / KABUPATEN --}}
                        <div class="relative" @click.outside="cityOpen = false">
                            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">
                                Kota / Kabupaten
                                <span x-show="country === 'Indonesia'" class="text-red-500">*</span>
                                <span x-show="country !== 'Indonesia'" class="text-xs font-normal text-[#9D8EB0]">(Khusus Indonesia)</span>
                            </label>
                            <input type="hidden" name="city" :value="country === 'Indonesia' ? city : ''">

                            <button type="button" :disabled="country !== 'Indonesia' || !province" @click="(country === 'Indonesia' && province) && (cityOpen ? (cityOpen = false) : openCity())" 
                                class="w-full px-4 py-2.5 rounded-xl text-sm flex items-center justify-between text-left transition"
                                :class="(country === 'Indonesia' && province) ? 'bg-white border border-[#D9C2F0] hover:border-[#B59BD6] cursor-pointer text-[#2A2035] focus:outline-none focus:ring-2 focus:ring-purple-deep' : 'bg-[#F7F4FB] border border-[#E8DEF2] text-[#A093B3] cursor-not-allowed opacity-75'">
                                <span x-text="country === 'Indonesia' ? (province ? (city || '-- Pilih Kota / Kabupaten --') : '-') : '-'"></span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#827299] transition-transform duration-200" :class="{ 'rotate-180': cityOpen }">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>

                            <div x-cloak x-show="country === 'Indonesia' && province && cityOpen" class="absolute z-50 mt-1.5 w-full bg-white rounded-xl shadow-xl border border-[#EDE1FA] overflow-hidden">
                                <div class="p-2.5 border-b border-[#EDE1FA] bg-[#FAF8FD]">
                                    <input type="text" x-ref="modalCitySearch" x-model="citySearch" :placeholder="province ? 'Cari kota di ' + province + '...' : 'Cari kota / kabupaten...'" class="w-full px-3 py-1.5 bg-white border border-[#D9C2F0] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-deep text-[#2A2035]">
                                </div>
                                <div class="max-h-48 overflow-y-auto divide-y divide-[#FAF8FD] py-1 custom-scrollbar">
                                    <template x-for="c in modalCityList" :key="c">
                                        <div @click="selectCity(c)" class="px-4 py-2.5 text-sm cursor-pointer flex items-center justify-between transition hover:bg-[#FAF8FD]" :class="{ 'bg-[#F3EBFC] text-purple-deep font-bold': city === c, 'text-[#2A2035]': city !== c }">
                                            <span x-text="c"></span>
                                            <svg x-show="city === c" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-purple-deep"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Alamat Lengkap Textarea --}}
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-bold text-[#5B4A73] mb-1.5">Alamat Lengkap <span class="text-red-500">*</span> <span class="text-[#827299] font-normal">(Jalan, Nomor, RT/RW, Kelurahan, Kecamatan, Kode Pos)</span></label>
                            <textarea name="address" rows="2" required x-model="address" placeholder="Contoh: Jl. Soekarno Hatta No. 112, RT 002/RW 005, Kel. Jatimulyo, Kec. Lowokwaru" class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition"></textarea>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: CATATAN & KETERANGAN TAMBAHAN --}}
                <div class="bg-white rounded-2xl shadow-xs border border-[#EDE1FA] p-5 space-y-3">
                    <div class="pb-2.5 border-b border-[#EDE1FA]">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-purple-deep">Catatan & Keterangan Tambahan (Opsional)</h3>
                    </div>

                    <div>
                        <textarea name="notes" x-model="notes" rows="3" placeholder="Contoh: Latar belakang pengajuan konsultasi atau kebutuhan khusus lainnya..." class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] leading-relaxed transition"></textarea>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="requestClose()" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-white transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-bold bg-purple-deep text-white hover:opacity-90 transition shadow-sm flex items-center gap-2 cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL KONFIRMASI BATALKAN PERUBAHAN (CUSTOM POPUP DIALOG)                --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="confirmDiscardModalOpen" @keydown.escape.window="if (confirmDiscardModalOpen) cancelDiscard()" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div x-show="confirmDiscardModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-black/50 backdrop-blur-xs" @click="cancelDiscard()"></div>
        <div x-show="confirmDiscardModalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="relative bg-white rounded-2xl shadow-2xl border border-[#EDE1FA] max-w-sm w-full p-6 z-10 space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <circle cx="12" cy="17" r="0.9" fill="currentColor" stroke="none"/>
                </svg>
            </div>
            
            <div class="text-center space-y-1.5">
                <h3 class="text-base font-bold text-gray-900">Batalkan Perubahan?</h3>
                <p class="text-xs text-[#6B5B85] leading-relaxed">
                    Perubahan pada <strong class="text-purple-deep" x-text="confirmDiscardSectionName || 'formulir'"></strong> belum disimpan.<br>Apakah Anda yakin ingin membatalkan dan keluar?
                </p>
            </div>

            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button" @click="cancelDiscard()" class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#FAF8FD] transition cursor-pointer">
                    Lanjut Edit
                </button>
                <button type="button" @click="executeDiscard()" class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-600 text-white hover:bg-amber-700 transition shadow-xs cursor-pointer">
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
