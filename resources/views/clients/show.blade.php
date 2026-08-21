@extends('layouts.dashboard')

@section('title', 'Detail Klien — ' . $client->name . ' — UC PSC')
@section('page-title', $client->name)

@section('content')
@php
    $isKonseling = ($client->service_type ?? 'konseling') === 'konseling';
    $isGroup = ($client->jenis ?? 'individual') === 'group';
    $isCompany = !$isKonseling && ($client->jenis ?? 'individual') === 'company';
    $isPsikotesInd = !$isKonseling && !$isCompany;
    $isKonselingInd = $isKonseling && !$isGroup;
    $isKonselingGroup = $isKonseling && $isGroup;

    $latestRecord = $client->counselingRecords->sortBy('scheduled_at')->first();
    $upcomingRecord = $client->counselingRecords->where('status', 'scheduled')->where('scheduled_at', '>=', now())->sortBy('scheduled_at')->first();
    $activePair = $client->pairings->where('status', 'active')->first();
    
    // Kumpulkan semua peserta dari booking jika group atau company
    $allParticipants = $client->bookings->flatMap->participants;
@endphp

<div class="space-y-6">

    {{-- Breadcrumb & Back Link / Quick Action --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ route('clients.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-purple-deep hover:text-purple-deep/80 transition">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Kembali ke Daftar Klien</span>
        </a>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('bookings.create', ['client_id' => $client->id]) }}" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-orange text-white text-xs font-bold rounded-xl hover:bg-orange/90 transition shadow-sm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Buat Jadwal Booking</span>
            </a>
            <a href="{{ route('clients.edit', $client) }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-[#D9C2F0] text-[#5B4A73] text-xs font-semibold rounded-xl hover:bg-gray-50 transition">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span>Edit Identitas</span>
            </a>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TOP BANNER JADWAL / NOTIFIKASI KHUSUS (BERDASARKAN LAYANAN)                --}}
    {{-- ========================================================================= --}}

    {{-- 1. Banner Jadwal Konseling Mendatang --}}
    @if($isKonseling && $upcomingRecord)
    <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 flex items-start gap-3.5 shadow-xs">
        <div class="w-10 h-10 rounded-xl bg-indigo-500 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-bold text-indigo-950 mb-0.5">Jadwal Sesi Konseling Mendatang</h3>
            <p class="text-xs text-indigo-800 leading-relaxed">
                {{ $isKonselingGroup ? 'Kelompok / Peserta' : 'Klien' }} dijadwalkan sesi konseling pada <strong class="font-semibold text-purple-deep">{{ $upcomingRecord->scheduled_at->format('d M Y, H:i') }} WIB</strong> 
                bersama konselor <strong class="font-semibold text-purple-deep">{{ $upcomingRecord->counselor->name ?? '-' }}</strong>.
                Metode/Lokasi: <strong class="font-semibold text-purple-deep">{{ $upcomingRecord->type === 'online' ? 'Online (' . ($upcomingRecord->location ?? 'Link Meet') . ')' : ($upcomingRecord->location ?? 'Tatap Muka di Ruang Konseling') }}</strong>.
            </p>
        </div>
    </div>
    @endif

    {{-- 2. Banner Konseling Berkelompok --}}
    @if($isKonselingGroup)
    <div class="bg-purple-deep/5 border border-[#EDE1FA] rounded-2xl p-4 flex items-start justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-deep text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-purple-deep">Konseling Berkelompok (Group / Pasangan / Keluarga)</h3>
                <p class="text-xs text-[#6B5B85] mt-0.5">
                    Paket: <strong class="text-[#2A2035]">{{ $client->counseling_type ?? 'Konseling Kelompok' }}</strong>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-deep/10 text-purple-deep">
                {{ $allParticipants->count() }} Anggota Terdaftar
            </span>
        </div>
    </div>
    @endif

    {{-- 3. Banner Jadwal Psikotes Individu Mendatang --}}
    @if($isPsikotesInd && $upcomingRecord)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3.5 shadow-xs">
        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-bold text-amber-950 mb-0.5">Jadwal Pelaksanaan Tes Psikologi</h3>
            <p class="text-xs text-amber-800 leading-relaxed">
                Pelaksanaan tes <strong class="font-semibold text-purple-deep">{{ $client->counseling_type ?? 'Psikotes' }}</strong> dijadwalkan pada 
                <strong class="font-semibold text-purple-deep">{{ $upcomingRecord->scheduled_at->format('d M Y, H:i') }} WIB</strong> 
                s/d <strong class="font-semibold text-purple-deep">{{ $upcomingRecord->end_time ? $upcomingRecord->end_time->format('H:i') . ' WIB' : 'Selesai' }}</strong>.
                Metode/Lokasi: <strong class="font-semibold text-purple-deep">{{ $upcomingRecord->type === 'online' ? 'Online (' . ($upcomingRecord->location ?? 'Platform Tes') . ')' : ($upcomingRecord->location ?? 'Tatap Muka di Lokasi / Kampus') }}</strong>.
            </p>
        </div>
    </div>
    @endif

    {{-- 4. Banner Ringkasan Perusahaan / Corporate --}}
    @if($isCompany)
    <div class="bg-purple-deep/5 border border-[#EDE1FA] rounded-2xl p-4 flex items-start justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-deep text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-purple-deep">Asesmen Psikotes Perusahaan / Kolektif</h3>
                <p class="text-xs text-[#6B5B85] mt-0.5">
                    Paket: <strong class="text-[#2A2035]">{{ $client->counseling_type ?? 'Asesmen Rekrutmen & Seleksi Karyawan' }}</strong>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-deep/10 text-purple-deep">
                {{ $allParticipants->count() }} Peserta Karyawan Terdaftar
            </span>
        </div>
    </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- GRID UTAMA: KOLOM KIRI (PROFIL) & KOLOM KANAN (OPERASIONAL & DETAIL)       --}}
    {{-- ========================================================================= --}}
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- ===================================================================== --}}
        {{-- KOLOM KIRI: PROFIL KLIEN (SESUAI KONSELING / PSIKOTES IND / COMPANY)   --}}
        {{-- ===================================================================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 relative" 
            x-data="{ 
                editMode: false, 
                serviceType: '{{ $client->service_type ?? 'konseling' }}',
                jenis: '{{ $client->jenis ?? 'individual' }}'
            }">
            
            {{-- Tombol Toggle Edit --}}
            <button @click="editMode = !editMode" class="absolute top-6 right-6 p-1.5 rounded-lg text-[#827299] hover:text-purple-deep hover:bg-purple-deep/10 transition cursor-pointer" title="Edit Data Klien">
                <svg x-show="!editMode" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <svg x-cloak x-show="editMode" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>

            <form action="{{ route('clients.update', $client) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                {{-- Header Profil --}}
                <div class="flex items-center gap-3.5 pb-4 border-b border-[#EDE1FA] pr-8">
                    @if($isCompany)
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center font-bold text-xl flex-shrink-0 border border-indigo-100 shadow-xs">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    @elseif($isKonselingGroup)
                    <div class="w-14 h-14 rounded-2xl bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-xl flex-shrink-0 border border-[#D9C2F0] shadow-xs">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    @else
                    <div class="w-14 h-14 rounded-2xl grad-purple text-white flex items-center justify-center font-bold text-xl flex-shrink-0 shadow-xs">
                        {{ substr($client->name, 0, 1) }}
                    </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <h2 x-show="!editMode" class="text-base font-bold text-purple-deep truncate">{{ $client->name }}</h2>
                        <input x-cloak x-show="editMode" type="text" name="name" value="{{ $client->name }}" 
                            class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-sm font-bold text-purple-deep focus:outline-none focus:ring-1 focus:ring-purple-deep mb-1.5" required>

                        {{-- Badges Layanan & Status --}}
                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                            @if($isKonselingInd)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-deep/10 text-purple-deep">
                                Konseling Individu
                            </span>
                            @elseif($isKonselingGroup)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-deep/10 text-purple-deep">
                                Konseling Berkelompok
                            </span>
                            @elseif($isCompany)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">
                                Psikotes Perusahaan
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                Psikotes Individu
                            </span>
                            @endif

                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $client->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 
                                  ($client->status === 'ongoing' ? 'bg-blue-50 text-blue-700' : 
                                  ($client->status === 'assigned' ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-700')) }}">
                                {{ str_replace('_', ' ', $client->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Detail Fields: Kondisional per Layanan --}}
                <div class="space-y-3.5 text-xs text-[#5B4A73]">

                    {{-- Paket Layanan / Detail --}}
                    <div class="bg-[#F7F5FB] p-3 rounded-xl border border-[#EDE1FA]">
                        <p class="text-[11px] font-semibold text-[#827299] mb-0.5">
                            {{ $isKonseling ? 'Jenis Konseling' : ($isCompany ? 'Keperluan Asesmen Korporat' : 'Paket Tes Psikologi') }}
                        </p>
                        <p x-show="!editMode" class="font-bold text-purple-deep text-sm">{{ $client->counseling_type ?? '-' }}</p>
                        <input x-cloak x-show="editMode" type="text" name="counseling_type" value="{{ $client->counseling_type }}" 
                            class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep bg-white mt-1">
                    </div>

                    {{-- Khusus Perusahaan / Kelompok: PIC Info --}}
                    @if($isCompany || $isKonselingGroup)
                    <div>
                        <p class="text-[11px] font-semibold text-[#827299] mb-1">
                            {{ $isCompany ? 'Kontak Kantor & PIC Perusahaan' : 'Kontak Utama & PIC Kelompok' }}
                        </p>
                        <div class="bg-[#FAF8FD] p-3 rounded-xl border border-[#EDE1FA] space-y-1.5">
                            <p class="text-xs font-semibold text-[#2A2035] flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <span>{{ $client->phone ?? '-' }}</span>
                            </p>
                            <p class="text-xs text-[#6B5B85] flex items-center gap-1.5">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                <span>{{ $client->email ?? '-' }}</span>
                            </p>
                        </div>
                    </div>
                    @else
                    {{-- Khusus Individu: Kontak & Biodata --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-semibold text-[#827299] mb-1">Telepon / WhatsApp</p>
                            <p x-show="!editMode" class="font-bold text-[#2A2035]">{{ $client->phone ?? '-' }}</p>
                            <input x-cloak x-show="editMode" type="text" name="phone" value="{{ $client->phone }}" 
                                class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep">
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-[#827299] mb-1">Email</p>
                            <p x-show="!editMode" class="font-bold text-[#2A2035] truncate">{{ $client->email ?? '-' }}</p>
                            <input x-cloak x-show="editMode" type="email" name="email" value="{{ $client->email }}" 
                                class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-semibold text-[#827299] mb-1">Jenis Kelamin</p>
                            <p x-show="!editMode" class="font-bold text-[#2A2035]">{{ $client->gender_label }}</p>
                            <select x-cloak x-show="editMode" name="gender" class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep">
                                <option value="">Pilih...</option>
                                <option value="l" {{ $client->gender === 'l' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="p" {{ $client->gender === 'p' ? 'selected' : '' }}>Perempuan</option>
                                <option value="non_binary" {{ $client->gender === 'non_binary' ? 'selected' : '' }}>Non-biner</option>
                                <option value="transgender" {{ $client->gender === 'transgender' ? 'selected' : '' }}>Transgender</option>
                                <option value="prefer_not_to_say" {{ $client->gender === 'prefer_not_to_say' ? 'selected' : '' }}>Tidak Disebutkan</option>
                                <option value="other" {{ $client->gender === 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-[#827299] mb-1">Tanggal Lahir</p>
                            <p x-show="!editMode" class="font-bold text-[#2A2035]">
                                {{ $client->dob ? $client->dob->format('d M Y') : '-' }}
                                @if($client->dob)
                                <span class="text-[10px] font-normal text-[#827299]">({{ $client->dob->age }} thn)</span>
                                @endif
                            </p>
                            <input x-cloak x-show="editMode" type="date" name="dob" value="{{ $client->dob ? $client->dob->format('Y-m-d') : '' }}" 
                                class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep">
                        </div>
                    </div>
                    @endif

                    {{-- Sumber & Status --}}
                    <div class="grid grid-cols-2 gap-3 pt-1 border-t border-[#EDE1FA]">
                        <div>
                            <p class="text-[11px] font-semibold text-[#827299] mb-1">Sumber Rujukan</p>
                            <p x-show="!editMode" class="font-bold text-[#2A2035]">{{ ucfirst(str_replace('_', ' ', $client->source)) }}</p>
                            <select x-cloak x-show="editMode" name="source" class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep">
                                <option value="whatsapp" {{ $client->source === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="walk_in" {{ $client->source === 'walk_in' ? 'selected' : '' }}>Walk In</option>
                                <option value="referral" {{ $client->source === 'referral' ? 'selected' : '' }}>Referral</option>
                                <option value="other" {{ $client->source === 'other' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-[#827299] mb-1">Status Klien</p>
                            <p x-show="!editMode" class="font-bold text-[#2A2035]">{{ ucfirst(str_replace('_', ' ', $client->status)) }}</p>
                            <select x-cloak x-show="editMode" name="status" class="w-full px-3 py-1.5 border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep">
                                <option value="unassigned" {{ $client->status === 'unassigned' ? 'selected' : '' }}>Belum Di-assign</option>
                                <option value="assigned" {{ $client->status === 'assigned' ? 'selected' : '' }}>Telah Di-assign</option>
                                <option value="ongoing" {{ $client->status === 'ongoing' ? 'selected' : '' }}>Sedang Pelaksanaan</option>
                                <option value="unpaid" {{ $client->status === 'unpaid' ? 'selected' : '' }}>Belum Membayar</option>
                                <option value="paid" {{ $client->status === 'paid' ? 'selected' : '' }}>Lunas</option>
                                <option value="needs_followup" {{ $client->status === 'needs_followup' ? 'selected' : '' }}>Butuh Sesi Lanjutan</option>
                                <option value="completed" {{ $client->status === 'completed' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>

                    {{-- Catatan Tambahan --}}
                    <div class="pt-1 border-t border-[#EDE1FA]">
                        <p class="text-[11px] font-semibold text-[#827299] mb-1">
                            {{ $isCompany ? 'Catatan Asesmen Korporat' : ($isKonselingGroup ? 'Catatan Konseling Kelompok' : 'Catatan Klien') }}
                        </p>
                        <p x-show="!editMode" class="text-xs text-[#2A2035] whitespace-pre-line leading-relaxed bg-[#FAF8FD] p-2.5 rounded-lg border border-[#EDE1FA]">
                            {{ $client->notes ?: 'Tidak ada catatan tambahan.' }}
                        </p>
                        <textarea x-cloak x-show="editMode" name="notes" rows="3" 
                            class="w-full px-3 py-2 border border-[#D9C2F0] rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-purple-deep">{{ $client->notes }}</textarea>
                    </div>

                    {{-- Metadata Pembuat --}}
                    <div class="pt-2 border-t border-[#EDE1FA] text-[11px] text-[#827299] space-y-1">
                        <p>Dicatat oleh: <strong class="text-[#2A2035]">{{ $client->creator->name ?? '-' }}</strong></p>
                        <p>Terdaftar sejak: <strong class="text-[#2A2035]">{{ $client->created_at->format('d M Y, H:i') }}</strong></p>
                    </div>
                </div>

                {{-- Action Buttons Edit Mode --}}
                <div x-cloak x-show="editMode" class="pt-3 border-t border-[#EDE1FA] flex items-center justify-end gap-2">
                    <button type="button" @click="editMode = false" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-[#6B5B85] border border-[#D9C2F0] hover:bg-[#F7F5FB] transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-emerald-600 text-white px-4 py-1.5 rounded-xl text-xs font-semibold hover:bg-emerald-700 transition shadow-xs">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- ===================================================================== --}}
        {{-- KOLOM KANAN: CARD OPERASIONAL KONDISIONAL BERDASARKAN LAYANAN         --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ================================================================= --}}
            {{-- VARIAN A: OPERASIONAL LAYANAN KONSELING BERKELOMPOK (GROUP)       --}}
            {{-- ================================================================= --}}
            @if($isKonselingGroup)

            {{-- 1. Card Daftar Anggota / Peserta Konseling Kelompok --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-purple-deep">Daftar Anggota / Peserta Konseling Kelompok</h3>
                        <p class="text-xs text-[#827299]">Anggota yang terdaftar dalam sesi konseling kelompok ini</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-deep/10 text-purple-deep">
                        {{ $allParticipants->count() }} Anggota
                    </span>
                </div>

                <div class="divide-y divide-[#F3EAFB] max-h-72 overflow-y-auto custom-scrollbar">
                    @forelse($allParticipants as $participant)
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                        <div class="flex items-center gap-3">
                            <span class="w-6 text-center text-xs font-bold text-purple-deep">{{ $loop->iteration }}.</span>
                            <div class="w-8 h-8 rounded-full bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ substr($participant->nama_peserta, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#2A2035]">{{ $participant->nama_peserta }}</p>
                                <p class="text-[10px] text-[#827299]">Kelompok: {{ $client->name }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                            Peserta Aktif
                        </span>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-[#827299] text-xs">
                        Belum ada data anggota kelompok yang tercatat.
                    </div>
                    @endforelse
                </div>
            </div>

            @endif

            {{-- ================================================================= --}}
            {{-- VARIAN B: OPERASIONAL LAYANAN KONSELING (INDIVIDU & GROUP)        --}}
            {{-- ================================================================= --}}
            @if($isKonseling)
            
            {{-- Card Pairing Konselor --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-purple-deep">Konselor yang Dipasangkan (*Pairing*)</h3>
                        <p class="text-xs text-[#827299]">Konselor penanggung jawab sesi konseling</p>
                    </div>
                    @if($activePair)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">Aktif</span>
                    @endif
                </div>
                <div class="p-6">
                    @if($activePair && $activePair->counselor)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[#FAF8FD] p-4 rounded-xl border border-[#EDE1FA]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl grad-purple text-white flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0">
                                {{ substr($activePair->counselor->name, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-purple-deep">{{ $activePair->counselor->name }}</h4>
                                <p class="text-xs text-[#6B5B85]">{{ $activePair->counselor->specialization ?? 'Psikolog Klinis / Konselor' }}</p>
                                <p class="text-[11px] text-[#827299] mt-0.5">Dipasangkan oleh {{ $activePair->assigner->name ?? 'Admin' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('counselors.index') }}" class="text-xs font-semibold text-orange hover:underline">Kelola Konselor →</a>
                    </div>
                    @else
                    <div class="text-center py-6">
                        <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-purple-deep/10 flex items-center justify-center text-purple-deep">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-[#2A2035]">Belum ada konselor yang dipasangkan</p>
                        <p class="text-xs text-[#827299] mt-0.5">Edit data klien atau buat booking untuk menugaskan konselor.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Card Riwayat Sesi Konseling --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-purple-deep">Riwayat Sesi Konseling</h3>
                        <p class="text-xs text-[#827299]">Daftar jadwal dan pelaksanaan sesi konseling</p>
                    </div>
                    <a href="{{ route('counseling-records.create') }}" class="text-xs font-semibold text-purple-deep hover:underline">
                        + Tambah Sesi
                    </a>
                </div>
                <div class="divide-y divide-[#F3EAFB] max-h-72 overflow-y-auto custom-scrollbar">
                    @forelse($client->counselingRecords->sortByDesc('scheduled_at') as $record)
                    <div class="px-6 py-3.5 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-[11px] font-bold {{ $record->type === 'whatsapp' ? 'bg-emerald-500' : ($record->type === 'online' ? 'bg-blue-500' : 'bg-purple-deep') }} flex-shrink-0 shadow-xs">
                                {{ $record->type === 'whatsapp' ? 'WA' : ($record->type === 'online' ? 'ON' : 'TM') }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-[#2A2035] truncate">{{ $record->counselor->name ?? 'Konselor Umum' }}</p>
                                <p class="text-[11px] text-[#827299]">
                                    {{ $record->scheduled_at ? $record->scheduled_at->format('d M Y, H:i') : '-' }} 
                                    · <span class="capitalize">{{ str_replace('_', ' ', $record->type) }}</span>
                                    @if($record->location) · {{ $record->location }} @endif
                                </p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $record->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 
                              ($record->status === 'scheduled' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                            {{ $record->status === 'completed' ? 'Selesai' : ($record->status === 'scheduled' ? 'Terjadwal' : 'Batal') }}
                        </span>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-[#827299] text-xs">Belum ada catatan sesi konseling.</div>
                    @endforelse
                </div>
            </div>

            @endif

            {{-- ================================================================= --}}
            {{-- VARIAN C: OPERASIONAL LAYANAN PSIKOTES PERORANGAN (INDIVIDU)      --}}
            {{-- ================================================================= --}}
            @if($isPsikotesInd)

            {{-- 1. Card Jadwal Pelaksanaan Tes Psikologi --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA]">
                    <h3 class="font-bold text-purple-deep">Jadwal Pelaksanaan Tes Psikologi</h3>
                    <p class="text-xs text-[#827299]">Informasi waktu dan lokasi asesmen untuk peserta individu</p>
                </div>
                <div class="p-6">
                    @if($latestRecord)
                    <div class="grid sm:grid-cols-3 gap-3">
                        <div class="bg-[#FAF8FD] p-3.5 rounded-xl border border-[#EDE1FA]">
                            <p class="text-[11px] font-semibold text-[#827299] mb-0.5">Waktu Pelaksanaan</p>
                            <p class="text-xs font-bold text-purple-deep">
                                {{ $latestRecord->scheduled_at ? $latestRecord->scheduled_at->format('d M Y, H:i') : '-' }} WIB
                            </p>
                            <p class="text-[10px] text-[#827299]">s/d {{ $latestRecord->end_time ? $latestRecord->end_time->format('H:i') . ' WIB' : 'Selesai' }}</p>
                        </div>
                        <div class="bg-[#FAF8FD] p-3.5 rounded-xl border border-[#EDE1FA]">
                            <p class="text-[11px] font-semibold text-[#827299] mb-0.5">Metode Pelaksanaan</p>
                            <span class="inline-block px-2.5 py-0.5 rounded-md text-xs font-bold {{ $latestRecord->type === 'online' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $latestRecord->type === 'online' ? 'Online (Daring)' : 'Offline (Tatap Muka)' }}
                            </span>
                        </div>
                        <div class="bg-[#FAF8FD] p-3.5 rounded-xl border border-[#EDE1FA]">
                            <p class="text-[11px] font-semibold text-[#827299] mb-0.5">Lokasi / Ruangan</p>
                            <p class="text-xs font-bold text-[#2A2035] truncate">{{ $latestRecord->location ?? 'Lab Psikodiagnostik' }}</p>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-6 text-xs text-[#827299]">
                        Jadwal tes belum diatur. Anda dapat mengatur jadwal melalui tombol edit profil klien.
                    </div>
                    @endif
                </div>
            </div>

            {{-- 2. Card Hasil & Laporan Tes Psikologi (Dokumen Asesmen) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-purple-deep">Laporan Hasil Tes Psikologi (*Psychological Report*)</h3>
                        <p class="text-xs text-[#827299]">File laporan psikotes, interpretasi hasil, dan rekomendasi</p>
                    </div>
                    <a href="{{ route('test-results.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-deep text-white hover:opacity-90 transition shadow-xs">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Upload Hasil Tes</span>
                    </a>
                </div>
                <div class="divide-y divide-[#F3EAFB]">
                    @forelse($client->testResults as $test)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange/15 text-orange flex items-center justify-center flex-shrink-0 shadow-xs">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#2A2035]">{{ $test->test_name }}</p>
                                <p class="text-[11px] text-[#827299]">
                                    Diuji pada {{ $test->tested_at ? $test->tested_at->format('d M Y') : '-' }} 
                                    · Penguji: {{ $test->administrator->name ?? 'Staff Penguji' }}
                                </p>
                            </div>
                        </div>
                        @if($test->file_path)
                        <a href="{{ asset('storage/' . $test->file_path) }}" target="_blank" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-deep/10 text-purple-deep hover:bg-purple-deep hover:text-white transition">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span>Unduh Laporan</span>
                        </a>
                        @endif
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-[#827299] text-xs">
                        <p class="font-semibold text-[#2A2035] mb-0.5">Belum ada dokumen hasil tes yang diupload</p>
                        <p>Klik tombol "+ Upload Hasil Tes" di atas untuk menambahkan laporan psikotes.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            @endif

            {{-- ================================================================= --}}
            {{-- VARIAN D: OPERASIONAL LAYANAN PSIKOTES PERUSAHAAN (KORPORAT)      --}}
            {{-- ================================================================= --}}
            @if($isCompany)

            {{-- 1. Card Daftar Karyawan / Peserta Asesmen Korporat --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-purple-deep">Daftar Karyawan / Peserta Asesmen (*Candidate List*)</h3>
                        <p class="text-xs text-[#827299]">Kandidat karyawan terdaftar dalam proyek asesmen korporat ini</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-deep/10 text-purple-deep">
                        {{ $allParticipants->count() }} Karyawan
                    </span>
                </div>

                <div class="divide-y divide-[#F3EAFB] max-h-80 overflow-y-auto custom-scrollbar">
                    @forelse($allParticipants as $participant)
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                        <div class="flex items-center gap-3">
                            <span class="w-6 text-center text-xs font-bold text-purple-deep">{{ $loop->iteration }}.</span>
                            <div class="w-8 h-8 rounded-full bg-purple-deep/10 text-purple-deep flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ substr($participant->nama_peserta, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#2A2035]">{{ $participant->nama_peserta }}</p>
                                <p class="text-[10px] text-[#827299]">Batch: {{ $client->name }} · {{ $client->counseling_type }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                            Terdaftar
                        </span>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-[#827299] text-xs">
                        Belum ada peserta karyawan yang didaftarkan.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- 2. Card Dokumen & Hasil Asesmen Kolektif Perusahaan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-purple-deep">Rekapitulasi & Laporan Asesmen Korporat</h3>
                        <p class="text-xs text-[#827299]">Dokumen rekap nilai, psikogram, dan rekomendasi hasil seleksi</p>
                    </div>
                    <a href="{{ route('test-results.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-deep text-white hover:opacity-90 transition shadow-xs">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Upload Rekap Asesmen</span>
                    </a>
                </div>
                <div class="divide-y divide-[#F3EAFB]">
                    @forelse($client->testResults as $test)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange/15 text-orange flex items-center justify-center flex-shrink-0 shadow-xs">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#2A2035]">{{ $test->test_name }}</p>
                                <p class="text-[11px] text-[#827299]">
                                    Diunggah {{ $test->tested_at ? $test->tested_at->format('d M Y') : '-' }} 
                                    · Penguji: {{ $test->administrator->name ?? 'Staff PIC' }}
                                </p>
                            </div>
                        </div>
                        @if($test->file_path)
                        <a href="{{ asset('storage/' . $test->file_path) }}" target="_blank" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-deep/10 text-purple-deep hover:bg-purple-deep hover:text-white transition">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            <span>Unduh Rekap Laporan</span>
                        </a>
                        @endif
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center text-[#827299] text-xs">
                        Belum ada dokumen laporan asesmen kolektif yang diunggah.
                    </div>
                    @endforelse
                </div>
            </div>

            @endif

            {{-- ================================================================= --}}
            {{-- CARD BERSAMA 1: RIWAYAT PENUGASAN STAFF (UNTUK SEMUA LAYANAN)     --}}
            {{-- ================================================================= --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-purple-deep">Penugasan Staff Pengelola</h3>
                        <p class="text-xs text-[#827299]">Staff internal penanggung jawab operasional klien</p>
                    </div>
                    @if($client->assignedStaff)
                    <a href="{{ route('staff-management.show', $client->assignedStaff) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-deep/10 text-purple-deep hover:bg-purple-deep/20 transition">
                        <span>Staff: {{ $client->assignedStaff->name }}</span>
                    </a>
                    @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">Belum Ditugaskan</span>
                    @endif
                </div>

                <div class="divide-y divide-[#F3EAFB] max-h-60 overflow-y-auto custom-scrollbar">
                    @forelse($client->staffAssignmentHistory->sortByDesc('assigned_at') as $assignment)
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-[#FAF8FD] transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xs font-bold flex-shrink-0">
                                {{ substr($assignment->staff->name ?? 'S', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#2A2035]">{{ $assignment->staff->name ?? '-' }}</p>
                                <p class="text-[10px] text-[#827299]">
                                    Ditugaskan {{ $assignment->assigned_at ? $assignment->assigned_at->format('d M Y, H:i') : '-' }} 
                                    @if($assignment->assigner) · oleh {{ $assignment->assigner->name }} @endif
                                </p>
                            </div>
                        </div>
                        @if($assignment->ended_at)
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-500">Berakhir</span>
                        @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                        @endif
                    </div>
                    @empty
                    <div class="px-6 py-6 text-center text-[#827299] text-xs">Belum ada riwayat penugasan staff.</div>
                    @endforelse
                </div>
            </div>

            {{-- ================================================================= --}}
            {{-- CARD BERSAMA 2: DAFTAR BOOKING / TRANSAKSI LAYANAN                --}}
            {{-- ================================================================= --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-purple-deep">Daftar Booking Terdaftar</h3>
                        <p class="text-xs text-[#827299]">Riwayat reservasi dan status administrasi layanan</p>
                    </div>
                    <a href="{{ route('bookings.create', ['client_id' => $client->id]) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-deep text-white hover:opacity-90 transition shadow-xs">
                        + Booking Baru
                    </a>
                </div>

                <div class="divide-y divide-[#F3EAFB]">
                    @forelse($client->bookings->sortByDesc('created_at') as $booking)
                    <div class="px-6 py-3.5 hover:bg-[#FAF8FD] transition">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $booking->kategori === 'konseling' ? 'bg-purple-deep/10 text-purple-deep' : 'bg-orange/10 text-orange' }}">
                                    {{ $booking->kategori }}
                                </span>
                                <span class="text-xs font-bold text-[#2A2035]">Booking #{{ $booking->id }}</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $booking->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 
                                  ($booking->status === 'baru' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ str_replace('_', ' ', $booking->status) }}
                            </span>
                        </div>
                        <p class="text-[11px] text-[#827299]">
                            Dibuat: {{ $booking->tanggal_booking_dibuat ? $booking->tanggal_booking_dibuat->format('d M Y') : '-' }}
                            @if($booking->tanggal_dijadwalkan) · Pelaksanaan: {{ $booking->tanggal_dijadwalkan->format('d M Y') }} @endif
                            @if($booking->counselor) · Konselor: {{ $booking->counselor->name }} @endif
                        </p>
                    </div>
                    @empty
                    <div class="px-6 py-6 text-center text-[#827299] text-xs">Belum ada catatan booking.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
