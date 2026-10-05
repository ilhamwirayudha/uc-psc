@extends('layouts.dashboard')

@section('title', 'Detail Klien — ' . $client->name . ' — UC PSC')
@section('page-title', 'Detail Klien')
@section('page-subtitle', 'Informasi profil, riwayat seluruh layanan terdaftar, reservasi konseling, dan berkas asesmen')

@section('content')
@php
    $isIndustri = in_array(strtolower($client->jenis ?? ''), ['industri', 'company', 'perusahaan']);
    $allParticipants = $client->clientParticipants;
    $totalBookings = $client->bookings->count();
    $totalTests = $client->testResults->count();
    $totalForms = $client->clientForms->count();
    $cleanPhone = preg_replace('/[^0-9]/', '', $client->phone ?? '');
    if (str_starts_with($cleanPhone, '0')) {
        $waPhone = '62' . substr($cleanPhone, 1);
    } else {
        $waPhone = $cleanPhone;
    }
@endphp

<div x-data="{ 
    activeTab: @js(request('tab', 'profile')),
    assignModalOpen: false,
    reassignModalOpen: false,
    unassignModalOpen: false,
    deleteModalOpen: false,
    shareFormDropdownOpen: false,
    editMode: false,

    // Modal Dossier Formulir
    showDossierModal: false,
    selectedForm: null,
    openDossier(form) {
        this.selectedForm = form;
        this.showDossierModal = true;
    }
}" class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- 1. PROFILE CARD (SEDERHANA)                                               --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl px-6 py-4 border border-[#EDE1FA] shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        {{-- Identity --}}
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $client->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider {{ $isIndustri ? 'bg-amber-100 text-amber-800' : 'bg-purple-100 text-purple-800' }}">
                    {{ $client->jenis_label }}
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1.5 text-xs text-slate-600">
                @if($client->phone)
                <a href="https://wa.me/{{ $waPhone }}" target="_blank" class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold hover:underline">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    {{ $client->phone }}
                </a>
                @endif
                @if($client->email)
                <span class="inline-flex items-center gap-1 text-slate-500">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    {{ $client->email }}
                </span>
                @endif
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            @if(auth()->user()->isAdmin())
            <button type="button" @click="deleteModalOpen = true"
                    class="px-4 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Hapus Klien
            </button>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- NAVIGASI TAB UTAMA                                                        --}}
    {{-- ========================================================================= --}}
    <div class="border-b border-[#EDE1FA] flex items-center gap-1 overflow-x-auto select-none">
        {{-- Tab 1: Biodata Klien --}}
        <button type="button" @click="activeTab = 'profile'; editMode = false"
                class="px-5 py-3 rounded-t-2xl text-xs font-bold transition flex items-center gap-2 cursor-pointer border-t-2 border-l border-r whitespace-nowrap"
                :class="activeTab === 'profile'
                    ? 'bg-white border-t-purple-deep border-l-[#EDE1FA] border-r-[#EDE1FA] text-purple-deep shadow-2xs'
                    : 'border-transparent text-slate-500 hover:text-purple-deep hover:bg-purple-50/50'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Biodata Klien</span>
        </button>

        {{-- Tab 2: Konseling --}}
        <button type="button" @click="activeTab = 'konseling'"
                class="px-5 py-3 rounded-t-2xl text-xs font-bold transition flex items-center gap-2 cursor-pointer border-t-2 border-l border-r whitespace-nowrap"
                :class="activeTab === 'konseling'
                    ? 'bg-white border-t-purple-deep border-l-[#EDE1FA] border-r-[#EDE1FA] text-purple-deep shadow-2xs'
                    : 'border-transparent text-slate-500 hover:text-purple-deep hover:bg-purple-50/50'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>Konseling</span>
            @if($totalForms + $totalBookings > 0)
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500"
                  :class="activeTab === 'konseling' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-500'">
                {{ $totalForms + $totalBookings }}
            </span>
            @endif
        </button>

        {{-- Tab 3: Psikotes --}}
        <button type="button" @click="activeTab = 'psychotest'"
                class="px-5 py-3 rounded-t-2xl text-xs font-bold transition flex items-center gap-2 cursor-pointer border-t-2 border-l border-r whitespace-nowrap"
                :class="activeTab === 'psychotest'
                    ? 'bg-white border-t-purple-deep border-l-[#EDE1FA] border-r-[#EDE1FA] text-purple-deep shadow-2xs'
                    : 'border-transparent text-slate-500 hover:text-purple-deep hover:bg-purple-50/50'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            <span>Psikotes</span>
            @if($totalTests > 0)
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="activeTab === 'psychotest' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-500'">
                {{ $totalTests }}
            </span>
            @endif
        </button>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: BIODATA KLIEN (dengan inline edit)                                 --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'profile'" x-cloak class="space-y-4">
        @include('clients.partials.address-script')

        {{-- ---- VIEW MODE ---- --}}
        <div x-show="!editMode">
            <div class="bg-white rounded-2xl border border-[#EDE1FA] p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                    <div>
                        <h3 class="font-bold text-purple-deep text-sm">Biodata & Informasi Identitas Klien</h3>
                        <p class="text-xs text-slate-500">Data induk demografi klien yang terintegrasi secara otomatis</p>
                    </div>
                    <button type="button" @click="editMode = true"
                            class="px-4 py-2 rounded-xl bg-white border border-[#EDE1FA] text-purple-deep hover:bg-purple-50 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Biodata
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Nama Lengkap:</span>
                        <strong class="text-slate-900 text-sm font-bold">{{ $client->name }}</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Jenis Kelamin:</span>
                        <strong class="text-slate-800 text-sm">{{ $client->gender_label }}</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Tempat / Tanggal Lahir:</span>
                        <strong class="text-slate-800 text-sm">
                            {{ $client->birth_place ?? '-' }}{{ $client->dob ? ', ' . $client->dob->format('d M Y') : '' }}
                        </strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Nomor Telepon / WhatsApp:</span>
                        <strong class="text-purple-deep text-sm font-mono">{{ $client->phone ?? '-' }}</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Alamat Email:</span>
                        <strong class="text-slate-800 text-sm">{{ $client->email ?? '-' }}</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Agama / Kepercayaan:</span>
                        <strong class="text-slate-800 text-sm">{{ $client->religion ?? '-' }}</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Pendidikan Terakhir:</span>
                        <strong class="text-slate-800 text-sm">{{ $client->education_label ?? $client->education ?? '-' }}</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Pekerjaan:</span>
                        <strong class="text-slate-800 text-sm">{{ $client->occupation ?? '-' }}</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Status Pernikahan:</span>
                        <strong class="text-slate-800 text-sm">{{ $client->marital_status ?? '-' }}</strong>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-slate-400 block font-semibold text-xs">Alamat Lengkap Tempat Tinggal:</span>
                        <strong class="text-slate-900 text-sm">{{ $client->address ?? '-' }}</strong>
                    </div>
                    @if(!empty($client->notes))
                    <div class="sm:col-span-2 lg:col-span-3 p-4 bg-purple-50/50 rounded-2xl border border-purple-100">
                        <span class="text-purple-deep block font-bold text-xs mb-1">Catatan Administrasi:</span>
                        <p class="text-slate-800 whitespace-pre-line text-sm">{{ $client->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ---- EDIT MODE ---- --}}
        <div x-show="editMode" x-cloak>
            <form action="{{ route('clients.update', $client) }}" method="POST"
                  x-data="{
                      name: @js($client->name ?? ''),
                      dob: @js($client->dob ? $client->dob->format('Y-m-d') : ''),
                      gender: @js($client->gender ?? ''),
                      religion: @js($client->religion ?? ''),
                      marital_status: @js($client->marital_status ?? ''),
                      education: @js($client->education ?? ''),
                      occupation: @js($client->occupation ?? ''),
                      phone: @js($client->phone ?? ''),
                      email: @js($client->email ?? ''),
                      address: @js($client->address ?? ''),
                      capitalizeInput(e) {
                          const el = e.target;
                          el.value = el.value.replace(/[0-9]/g, '');
                          el.value = el.value.replace(/\b\w/g, c => c.toUpperCase());
                          if (this.hasOwnProperty(el.getAttribute('name'))) this[el.getAttribute('name')] = el.value;
                      }
                  }"
                  class="bg-white rounded-2xl border border-[#EDE1FA] p-6 shadow-xs space-y-5">
                @csrf @method('PUT')

                <div class="flex items-center justify-between border-b border-[#EDE1FA] pb-3">
                    <div>
                        <h3 class="font-bold text-purple-deep text-sm">Edit Biodata Klien</h3>
                        <p class="text-xs text-slate-500">Ubah data identitas klien secara langsung</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editMode = false"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-slate-500 font-semibold text-xs hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-xs transition shadow-xs cursor-pointer">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- Nama --}}
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                        <input type="text" name="name" x-model="name" required @input="capitalizeInput($event)"
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white">
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Tanggal Lahir</label>
                        <input type="date" name="dob" x-model="dob"
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white">
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Jenis Kelamin</label>
                        <select name="gender" x-model="gender"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white text-[#5B4A73]">
                            <option value="">-- Pilih --</option>
                            <option value="l" {{ $client->gender === 'l' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="p" {{ $client->gender === 'p' ? 'selected' : '' }}>Perempuan</option>
                            <option value="non_binary" {{ $client->gender === 'non_binary' ? 'selected' : '' }}>Non-biner</option>
                            <option value="prefer_not_to_say" {{ $client->gender === 'prefer_not_to_say' ? 'selected' : '' }}>Tidak Disebutkan</option>
                            <option value="other" {{ $client->gender === 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    {{-- Agama --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Agama</label>
                        <select name="religion" x-model="religion"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white text-[#5B4A73]">
                            <option value="">-- Pilih --</option>
                            <option value="Islam" {{ $client->religion === 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen" {{ $client->religion === 'Kristen' ? 'selected' : '' }}>Kristen Protestan</option>
                            <option value="Katolik" {{ $client->religion === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ $client->religion === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ $client->religion === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Khonghucu" {{ $client->religion === 'Khonghucu' ? 'selected' : '' }}>Khonghucu</option>
                            <option value="Lainnya" {{ $client->religion === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    {{-- Status Perkawinan --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Status Perkawinan</label>
                        <select name="marital_status" x-model="marital_status"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white text-[#5B4A73]">
                            <option value="">-- Pilih --</option>
                            <option value="belum_menikah" {{ $client->marital_status === 'belum_menikah' ? 'selected' : '' }}>Belum Menikah</option>
                            <option value="menikah" {{ $client->marital_status === 'menikah' ? 'selected' : '' }}>Menikah</option>
                            <option value="cerai_hidup" {{ $client->marital_status === 'cerai_hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                            <option value="cerai_mati" {{ $client->marital_status === 'cerai_mati' ? 'selected' : '' }}>Cerai Mati</option>
                        </select>
                    </div>

                    {{-- Pendidikan --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pendidikan Terakhir</label>
                        <select name="education" x-model="education"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white text-[#5B4A73]">
                            <option value="">-- Pilih --</option>
                            <option value="sd" {{ $client->education === 'sd' ? 'selected' : '' }}>SD / Sederajat</option>
                            <option value="smp" {{ $client->education === 'smp' ? 'selected' : '' }}>SMP / Sederajat</option>
                            <option value="sma_smk" {{ $client->education === 'sma_smk' ? 'selected' : '' }}>SMA / SMK</option>
                            <option value="d3" {{ $client->education === 'd3' ? 'selected' : '' }}>Diploma / D3</option>
                            <option value="s1" {{ $client->education === 's1' ? 'selected' : '' }}>Sarjana / S1</option>
                            <option value="s2" {{ $client->education === 's2' ? 'selected' : '' }}>Magister / S2</option>
                            <option value="s3" {{ $client->education === 's3' ? 'selected' : '' }}>Doktor / S3</option>
                            <option value="lainnya" {{ $client->education === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    {{-- Pekerjaan --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pekerjaan</label>
                        <input type="text" name="occupation" x-model="occupation"
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white">
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Nomor Telepon / WhatsApp <span class="text-red-400">*</span></label>
                        <input type="text" name="phone" x-model="phone"
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Alamat Email</label>
                        <input type="email" name="email" x-model="email"
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white">
                    </div>

                    {{-- Alamat --}}
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Alamat Lengkap</label>
                        <textarea name="address" x-model="address" rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm bg-white resize-none"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: KONSELING (Formulir Intake + Jadwal Booking)                       --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'konseling'" x-cloak class="space-y-6">

        {{-- Bagian A: Formulir Pendaftaran --}}
        @if($client->clientForms->isEmpty())
        <div class="bg-white rounded-2xl p-10 border border-[#EDE1FA] text-center space-y-3 shadow-xs">
            <div class="w-14 h-14 rounded-2xl bg-purple-50 text-purple-deep mx-auto flex items-center justify-center">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Belum Ada Formulir Layanan Masuk</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Klien belum mengirimkan formulir pendaftaran. Formulir akan muncul di sini secara otomatis.</p>
        </div>
        @else
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-purple-deep">Rekam Jejak Pendaftaran Layanan</h3>
                    <p class="text-xs text-slate-500">Formulir pendaftaran yang pernah dikirimkan oleh klien ini</p>
                </div>
                <span class="text-xs font-semibold text-slate-500 bg-white px-3 py-1.5 rounded-xl border border-[#EDE1FA]">
                    Total: <strong>{{ $totalForms }} Pendaftaran</strong>
                </span>
            </div>
            <div class="space-y-4">
                @foreach($client->clientForms->sortByDesc('created_at') as $form)
                @php
                    $ans = $form->answers ?? [];
                    $formTypeLabel = $form->form_type_label;
                    $statusBg = match($form->status) {
                        'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'diproses' => 'bg-amber-100 text-amber-800 border-amber-200',
                        default => 'bg-purple-100 text-purple-800 border-purple-200',
                    };
                @endphp
                <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs hover:shadow-md transition-shadow duration-200 overflow-hidden">
                    <div class="px-5 py-3.5 bg-[#FAF9FD] border-b border-[#EDE1FA] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-purple-deep text-white flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0">
                                @if($form->form_type === 'anak') 🧒
                                @elseif($form->form_type === 'pra_nikah') 💍
                                @elseif($form->form_type === 'pernikahan') ❤️
                                @elseif($form->form_type === 'industri') 🏢
                                @elseif($form->form_type === 'biography_en') 🌐
                                @else 👤
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-extrabold text-slate-900">{{ $formTypeLabel }}</h4>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $statusBg }}">{{ ucfirst($form->status ?? 'Baru Masuk') }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Waktu Masuk: <strong>{{ $form->created_at->format('d M Y, H:i') }} WIB</strong>
                                    <span class="text-slate-400">({{ $form->created_at->diffForHumans() }})</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="openDossier({{ json_encode($form) }})"
                                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-purple-50 text-purple-deep border border-[#EDE1FA] font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                Lihat Kuesioner
                            </button>
                            <button type="button" @click="$dispatch('open-booking-modal', { client_id: '{{ $client->id }}', service_type: '{{ $formTypeLabel }}' })"
                                    class="px-3 py-1.5 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-xs transition flex items-center gap-1.5 cursor-pointer">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Jadwalkan Sesi
                            </button>
                        </div>
                    </div>
                    @if(!empty($ans['alasan_konseling']) || !empty($ans['keterangan']) || !empty($ans['tujuan_asesmen']))
                    <div class="px-5 py-4">
                        <div class="bg-purple-50/60 rounded-xl p-3.5 border border-purple-100/80">
                            <span class="text-xs font-bold text-purple-deep uppercase tracking-wider block mb-1">Alasan Konseling / Kebutuhan Layanan:</span>
                            <p class="text-sm text-slate-800 font-medium leading-relaxed italic">"{{ $ans['alasan_konseling'] ?? $ans['keterangan'] ?? $ans['tujuan_asesmen'] }}"</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Bagian B: Jadwal Booking --}}
        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-purple-deep text-sm">Daftar Sesi Booking & Konseling</h3>
                    <p class="text-xs text-slate-500">Riwayat sesi konseling dan tes psikologi yang terjadwal</p>
                </div>
                <button type="button" @click="$dispatch('open-booking-modal', { client_id: '{{ $client->id }}' })"
                        class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Buat Booking Baru</span>
                </button>
            </div>
            @if($client->bookings->isEmpty())
            <div class="py-10 text-center text-slate-400 space-y-2">
                <svg class="w-10 h-10 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p class="text-xs font-semibold text-slate-500">Belum ada sesi booking untuk klien ini.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#F8F9FA] text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">No. Booking</th>
                            <th class="py-3 px-4">Layanan</th>
                            <th class="py-3 px-4">Jadwal Sesi</th>
                            <th class="py-3 px-4">Konselor</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Pembayaran</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($client->bookings->sortByDesc('booking_date') as $b)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-purple-deep">{{ $b->booking_number ?? '#' . $b->id }}</td>
                            <td class="py-3 px-4 font-bold">{{ $b->kategori }}</td>
                            <td class="py-3 px-4">
                                <span class="font-semibold block">{{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}</span>
                                <span class="text-slate-400">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }} WIB</span>
                            </td>
                            <td class="py-3 px-4">{{ $b->counselor->name ?? 'Belum Ditugaskan' }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold capitalize
                                    {{ $b->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($b->status === 'batal' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $b->status_label ?? $b->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $b->paymentTransaction?->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $b->paymentTransaction?->status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('bookings.show', $b) }}" class="text-purple-deep font-bold hover:underline">Detail &rarr;</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>


    {{-- ========================================================================= --}}
    {{-- TAB 3: PSIKOTES (LAPORAN ASESMEN)                                         --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'psychotest'" x-cloak class="space-y-6">
        <div class="bg-white rounded-3xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-purple-deep text-sm">Laporan Hasil Psikotes (*Psychological Report*)</h3>
                    <p class="text-xs text-slate-500">Dokumen evaluasi psikotes, rekomendasi, dan status pengiriman ke klien</p>
                </div>
                <button type="button" @click="$dispatch('open-test-result-modal', { client_id: {{ $client->id }} })"
                        class="px-4 py-2 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-xs transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Upload Hasil Psikotes</span>
                </button>
            </div>

            @if($client->testResults->isEmpty())
            <div class="py-12 text-center text-slate-400 space-y-2">
                <svg class="w-12 h-12 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                <p class="text-xs font-semibold text-slate-500">Belum ada dokumen hasil psikotes untuk klien ini.</p>
            </div>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($client->testResults->sortByDesc('created_at') as $test)
                <div class="p-5 hover:bg-slate-50 transition flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold shadow-xs flex-shrink-0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">{{ $test->report_title ?? 'Laporan Asesmen Psikotes' }}</h4>
                            <p class="text-xs text-slate-500">Tanggal Tes: {{ $test->test_date ? \Carbon\Carbon::parse($test->test_date)->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('test-results.show', $test) }}" class="px-3.5 py-1.5 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-bold text-xs transition">
                            Lihat Berkas &rarr;
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>



    {{-- ========================================================================= --}}
    {{-- DOSSIER MODAL DETAIL JAWABAN FORMULIR                                     --}}
    {{-- ========================================================================= --}}
    <div x-show="showDossierModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showDossierModal = false" 
             class="bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-scale-up">
            
            {{-- Modal Header --}}
            <div class="p-6 bg-gradient-to-r from-purple-deep to-[#4A2F85] text-white flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-white/70 uppercase" x-text="selectedForm && selectedForm.created_at ? 'Waktu Pengisian: ' + selectedForm.created_at.slice(0, 19).replace('T', ' ') : ''"></span>
                    <h3 class="text-xl font-extrabold" x-text="selectedForm ? selectedForm.client_name : ''"></h3>
                    <p class="text-xs text-white/80" x-text="selectedForm ? selectedForm.form_type_label : ''"></p>
                </div>
                <button type="button" @click="showDossierModal = false" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition cursor-pointer">
                    &times;
                </button>
            </div>

            {{-- Modal Body (Answers List) --}}
            <div class="p-6 overflow-y-auto space-y-6 text-sm text-slate-800">
                <template x-if="selectedForm && selectedForm.answers">
                    <div class="space-y-6">
                        {{-- 1. Data Diri --}}
                        <div class="bg-purple-50/60 rounded-2xl p-4 border border-purple-100">
                            <h4 class="font-bold text-purple-deep text-xs uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>1. Data Diri Klien</span>
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                                <div><span class="text-slate-400 block font-medium">Nama Lengkap:</span> <strong class="text-slate-900" x-text="selectedForm.client_name || selectedForm.answers.nama_lengkap || selectedForm.answers.full_name || '-'"></strong></div>
                                <div><span class="text-slate-400 block font-medium">Jenis Kelamin:</span> <span class="font-semibold text-slate-800" x-text="selectedForm.answers.jenis_kelamin || selectedForm.answers.gender || '-'"></span></div>
                                <div><span class="text-slate-400 block font-medium">TTL:</span> <span class="font-semibold text-slate-800" x-text="selectedForm.answers.birth_place_date || selectedForm.answers.tempat_tanggal_lahir || `${selectedForm.answers.tempat_lahir || ''}${selectedForm.answers.tempat_lahir && selectedForm.answers.tanggal_lahir ? ', ' : ''}${selectedForm.answers.tanggal_lahir || '-'}`"></span></div>
                                <div><span class="text-slate-400 block font-medium">No. WhatsApp:</span> <strong class="text-purple-deep" x-text="selectedForm.client_phone || selectedForm.answers.phone || '-'"></strong></div>
                                <div><span class="text-slate-400 block font-medium">Pendidikan Terakhir:</span> <span class="text-slate-800" x-text="selectedForm.answers.pendidikan_terakhir || selectedForm.answers.last_education || '-'"></span></div>
                                <div><span class="text-slate-400 block font-medium">Pekerjaan:</span> <span class="text-slate-800" x-text="selectedForm.answers.pekerjaan || selectedForm.answers.pekerjaan_saat_ini || '-'"></span></div>
                                <div class="col-span-2 sm:col-span-3"><span class="text-slate-400 block font-medium">Alamat:</span> <strong class="text-slate-800 font-medium" x-text="selectedForm.answers.alamat || selectedForm.answers.alamat_sekarang || selectedForm.answers.current_address || '-'"></strong></div>
                            </div>
                        </div>

                        {{-- 2. Alasan & Keluhan --}}
                        <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-100">
                            <h4 class="font-bold text-amber-900 text-xs uppercase tracking-wider mb-2">2. Alasan Konseling & Keluhan</h4>
                            <p class="text-xs text-slate-800 leading-relaxed font-medium" x-text="selectedForm.answers.alasan_konseling || selectedForm.answers.keterangan || '-'"></p>
                        </div>

                        {{-- 3. Seluruh Jawaban Kuesioner (Raw Key-Value Explorer) --}}
                        <div class="pt-3 border-t border-slate-200">
                            <details class="group cursor-pointer">
                                <summary class="flex items-center justify-between text-xs font-bold text-slate-700 hover:text-purple-deep py-2 select-none">
                                    <span>Inspeksi Seluruh Data Jawaban Lengkap</span>
                                    <span class="text-slate-400 group-open:rotate-180 transition-transform">&darr;</span>
                                </summary>
                                <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-200 overflow-x-auto text-[11px] font-mono space-y-1.5 max-h-60 overflow-y-auto">
                                    <template x-for="(val, key) in selectedForm.answers" :key="key">
                                        <div class="flex items-start gap-2 py-1 border-b border-slate-200/60 last:border-b-0">
                                            <span class="w-44 text-slate-500 font-semibold flex-shrink-0" x-text="key + ':'"></span>
                                            <span class="text-slate-900 break-words flex-1" x-text="val !== null && val !== '' ? val : '-'"></span>
                                        </div>
                                    </template>
                                </div>
                            </details>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Modal Footer --}}
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <button type="button" @click="showDossierModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL PENUGASAN STAF                                                      --}}
    {{-- ========================================================================= --}}
    @if(auth()->user()->isAdmin())
    {{-- 1. Modal Tugaskan Staff Baru --}}
    <div x-cloak x-show="assignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="assignModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="assignModalOpen = false"></div>
        <div x-show="assignModalOpen" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-bold text-purple-deep">Tugaskan Staff Pengelola</h3>
            <form action="{{ route('assignments.assign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pilih Staff <span class="text-red-500">*</span></label>
                    <select name="staff_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-xs focus:ring-2 focus:ring-purple-deep bg-white">
                        <option value="">-- Pilih Staff --</option>
                        @foreach($staffMembers as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="assignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 border border-slate-200">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-deep text-white shadow-xs">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 2. Modal Alihkan Staff --}}
    <div x-cloak x-show="reassignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="reassignModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="reassignModalOpen = false"></div>
        <div x-show="reassignModalOpen" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-bold text-purple-deep">Alihkan Staff Pengelola</h3>
            <form action="{{ route('assignments.reassign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#5B4A73] mb-1.5">Pilih Staff Pengganti <span class="text-red-500">*</span></label>
                    <select name="staff_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] text-xs focus:ring-2 focus:ring-purple-deep bg-white">
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
                        <button type="button" @click="reassignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 border border-slate-200">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-purple-deep text-white shadow-xs">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. Modal Lepaskan Staff --}}
    <div x-cloak x-show="unassignModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="unassignModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="unassignModalOpen = false"></div>
        <div x-show="unassignModalOpen" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-bold text-red-600">Lepaskan Penugasan Staff</h3>
            <p class="text-xs text-slate-600">Klien <strong>{{ $client->name }}</strong> akan dikembalikan ke status belum ditugaskan.</p>
            <form action="{{ route('assignments.unassign', $client) }}" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="unassignModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 border border-slate-200">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 text-white shadow-xs">Ya, Lepaskan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. Modal Hapus Klien --}}
    <div x-cloak x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="deleteModalOpen" class="fixed inset-0 bg-black/40 backdrop-blur-xs" @click="deleteModalOpen = false"></div>
        <div x-show="deleteModalOpen" class="relative bg-white rounded-2xl shadow-xl border border-[#EDE1FA] max-w-md w-full p-6 z-10 space-y-4">
            <h3 class="text-base font-bold text-red-600">Hapus Data Klien?</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Apakah Anda yakin ingin menghapus data klien <strong>{{ $client->name }}</strong> beserta seluruh riwayat formulir dan reservasi terkait? Tindakan ini permanen.
            </p>
            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="flex items-center justify-end gap-2 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 border border-slate-200">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 text-white shadow-xs">Ya, Hapus Klien</button>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
