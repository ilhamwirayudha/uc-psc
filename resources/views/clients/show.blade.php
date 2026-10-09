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
    $counselingForms = $client->clientForms->filter(fn($f) => in_array($f->form_type, ['dewasa', 'anak', 'pra_nikah', 'pernikahan']));
    $psychotestForms = $client->clientForms->filter(fn($f) => in_array($f->form_type, ['industri', 'non_industri', 'biography_en']));
    $counselingTotal = $counselingForms->count() + $client->bookings->where('kategori', 'konseling')->count();
    $psychotestTotal = $psychotestForms->count() + $totalTests;
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
    isPrintingPdf: false,
    openDossier(form) {
        this.selectedForm = form;
        this.showDossierModal = true;
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
                    // Gunakan judul yang telah dihitung server atau targetFilename
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
        const validTabs = ['profile', 'notes', 'konseling', 'psychotest', 'settings'];
        const urlParams = new URLSearchParams(window.location.search);
        const urlTab = urlParams.get('tab');

        // Bersihkan seluruh cache session tab klien agar selalu default ke Biodata Klien
        try {
            Object.keys(sessionStorage).forEach(key => {
                if (key.startsWith('client_active_tab_')) {
                    sessionStorage.removeItem(key);
                }
            });
        } catch (e) {}

        // Selalu default ke tab 'profile' (Biodata Klien) kecuali URL memiliki parameter ?tab= eksplisit
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
}" class="space-y-6">

    {{-- ========================================================================= --}}
    {{-- KARTU TABS UTAMA TERINTEGRASI                                             --}}
    {{-- ========================================================================= --}}
    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
        {{-- HEADER NAVIGASI TAB (Minimalist Underline di Bar Deep Purple ala Linear / GitHub) --}}
        <div class="bg-purple-deep border-b border-purple-900/40 px-4 sm:px-6 flex items-center justify-between gap-3 overflow-x-auto overflow-y-hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden select-none">
            <div class="flex items-center gap-6 sm:gap-8 -mb-px">
                {{-- Tab 1: Biodata Klien --}}
                <button type="button" @click="activeTab = 'profile'; editMode = false"
                        class="py-4 text-sm font-semibold transition-all flex items-center gap-2 cursor-pointer border-b-[3px] whitespace-nowrap"
                        :class="activeTab === 'profile'
                            ? 'border-white text-white font-bold'
                            : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-medium'">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Biodata Klien</span>
                </button>

                {{-- Tab 2: Catatan Klien --}}
                <button type="button" @click="activeTab = 'notes'"
                        class="py-4 text-sm font-semibold transition-all flex items-center gap-2 cursor-pointer border-b-[3px] whitespace-nowrap"
                        :class="activeTab === 'notes'
                            ? 'border-white text-white font-bold'
                            : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-medium'">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    <span>Catatan Klien</span>
                </button>

                {{-- Tab 3: Konseling --}}
                <button type="button" @click="activeTab = 'konseling'"
                        class="py-4 text-sm font-semibold transition-all flex items-center gap-2 cursor-pointer border-b-[3px] whitespace-nowrap"
                        :class="activeTab === 'konseling'
                            ? 'border-white text-white font-bold'
                            : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-medium'">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Konseling</span>
                </button>

                {{-- Tab 4: Psikotes --}}
                <button type="button" @click="activeTab = 'psychotest'"
                        class="py-4 text-sm font-semibold transition-all flex items-center gap-2 cursor-pointer border-b-[3px] whitespace-nowrap"
                        :class="activeTab === 'psychotest'
                            ? 'border-white text-white font-bold'
                            : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-medium'">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    <span>Psikotes</span>
                </button>

                {{-- Tab 5: Pengaturan --}}
                <button type="button" @click="activeTab = 'settings'"
                        class="py-4 text-sm font-semibold transition-all flex items-center gap-2 cursor-pointer border-b-[3px] whitespace-nowrap"
                        :class="activeTab === 'settings'
                            ? 'border-white text-white font-bold'
                            : 'border-transparent text-purple-200/80 hover:text-white hover:border-purple-300/40 font-medium'">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Pengaturan</span>
                </button>
            </div>
        </div>

        {{-- WADAH KONTEN TAB --}}
        <div class="p-5 sm:p-6 bg-white">
            {{-- ========================================================================= --}}
            {{-- TAB 1: BIODATA KLIEN                                                     --}}
            {{-- ========================================================================= --}}
            <div x-show="activeTab === 'profile'" x-cloak class="space-y-5">
                @include('clients.partials.address-script')


                {{-- ---- VIEW MODE ---- --}}
                <div x-show="!editMode" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                    <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-white text-base">Informasi Biodata Klien</h3>
                            <p class="text-xs font-medium text-purple-200">Data identitas pribadi, kontak, dan riwayat tempat tinggal</p>
                        </div>
                        <button type="button" @click="editMode = true"
                                class="px-4 py-2.5 rounded-xl bg-white border border-[#EDE1FA] text-purple-deep hover:bg-purple-50 font-semibold text-xs sm:text-sm transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit Biodata</span>
                        </button>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            {{-- 1. Nama Lengkap (Wajib) --}}
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-gray-500 block font-medium text-xs">Nama Lengkap:</span>
                                <span class="text-black text-sm font-medium block mt-0.5">{{ $client->name }}</span>
                            </div>

                            @if($isIndustri)
                                {{-- 2. Perusahaan Pengirim (Wajib Industri) --}}
                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <span class="text-gray-500 block font-medium text-xs">Perusahaan Pengirim:</span>
                                    <span class="text-black text-sm font-medium block mt-0.5">{{ $client->company_name ?? ($client->pic_name ?? '-') }}</span>
                                </div>

                                {{-- 3. Pekerjaan / Jabatan Saat Ini (Wajib Industri) --}}
                                @php
                                    $jobTitle = $client->occupation;
                                    if ($jobTitle && str_contains($jobTitle, ' - ')) {
                                        $parts = explode(' - ', $jobTitle);
                                        $jobTitle = trim($parts[0]);
                                    }
                                @endphp
                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <span class="text-gray-500 block font-medium text-xs">Pekerjaan / Jabatan Saat Ini:</span>
                                    <span class="text-black text-sm font-medium block mt-0.5">{{ $jobTitle ?: '-' }}</span>
                                </div>
                            @endif

                            {{-- Jenis Kelamin (Wajib) --}}
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-gray-500 block font-medium text-xs">Jenis Kelamin:</span>
                                <span class="text-black text-sm font-medium block mt-0.5">{{ $client->gender_label }}</span>
                            </div>

                            {{-- Tempat / Tanggal Lahir (Wajib) --}}
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-gray-500 block font-medium text-xs">Tempat / Tanggal Lahir:</span>
                                <span class="text-black text-sm font-medium block mt-0.5">
                                    {{ $client->birth_place ?? '-' }}{{ $client->dob ? ', ' . $client->dob->format('d M Y') : '' }}
                                </span>
                            </div>

                            {{-- Nomor Telepon / WhatsApp (Wajib) --}}
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-gray-500 block font-medium text-xs">Nomor Telepon / WhatsApp:</span>
                                <span class="text-black text-sm font-medium block mt-0.5">{{ $client->phone ?? '-' }}</span>
                            </div>

                            {{-- Alamat Email (Wajib) --}}
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-gray-500 block font-medium text-xs">Alamat Email:</span>
                                <span class="text-black text-sm font-medium block mt-0.5">{{ $client->email ?? '-' }}</span>
                            </div>

                            {{-- Pendidikan Terakhir (Wajib) --}}
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                <span class="text-gray-500 block font-medium text-xs">Pendidikan Terakhir:</span>
                                <span class="text-black text-sm font-medium block mt-0.5">{{ $client->education_label ?? $client->education ?? '-' }}</span>
                            </div>

                            @if(!$isIndustri)
                                {{-- Agama / Kepercayaan (Wajib untuk Non-Industri) --}}
                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <span class="text-gray-500 block font-medium text-xs">Agama / Kepercayaan:</span>
                                    <span class="text-black text-sm font-medium block mt-0.5">{{ $client->religion ?? '-' }}</span>
                                </div>

                                {{-- Alamat Lengkap Tempat Tinggal (Wajib untuk Non-Industri) --}}
                                <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                    <span class="text-gray-500 block font-medium text-xs">Alamat Lengkap Tempat Tinggal:</span>
                                    <span class="text-black text-sm font-medium block mt-0.5">{{ $client->address ?? '-' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ---- EDIT MODE ---- --}}
                <div x-show="editMode" x-cloak>
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                        <form action="{{ route('clients.update', $client) }}" method="POST"
                              x-data="{
                                  name: @js($client->name ?? ''),
                                  dob: @js($client->dob ? $client->dob->format('Y-m-d') : ''),
                                  gender: @js($client->gender ?? ''),
                                  religion: @js($client->religion ?? ''),
                                  education: @js($client->education ?? ''),
                                  occupation: @js($client->occupation ?? ''),
                                  pic_name: @js($client->pic_name ?? ''),
                                  phone: @js($client->phone ?? ''),
                                  email: @js($client->email ?? ''),
                                  address: @js($client->address ?? ''),
                                  capitalizeInput(e) {
                                      const el = e.target;
                                      el.value = el.value.replace(/[0-9]/g, '');
                                      el.value = el.value.replace(/\b\w/g, c => c.toUpperCase());
                                      if (this.hasOwnProperty(el.getAttribute('name'))) this[el.getAttribute('name')] = el.value;
                                  }
                              }">
                            @csrf @method('PUT')

                            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-white text-base">Edit Biodata Klien</h3>
                                    <p class="text-xs font-medium text-purple-200">Ubah data identitas klien secara langsung</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="editMode = false"
                                            class="px-4 py-2 rounded-xl border border-white/30 text-white font-semibold text-sm hover:bg-white/10 transition cursor-pointer">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="px-4 py-2 rounded-xl bg-white text-purple-deep hover:bg-purple-50 font-semibold text-sm transition shadow-xs cursor-pointer">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">
                                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {{-- Nama --}}
                                    <div class="lg:col-span-3">
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                                        <input type="text" name="name" x-model="name" required @input="capitalizeInput($event)"
                                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    </div>

                                    {{-- Tanggal Lahir --}}
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Lahir <span class="text-red-400">*</span></label>
                                        <input type="date" name="dob" x-model="dob" required
                                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    </div>

                                    {{-- Jenis Kelamin --}}
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Jenis Kelamin <span class="text-red-400">*</span></label>
                                        <select name="gender" x-model="gender" required
                                                class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                            <option value="">-- Pilih --</option>
                                            <option value="l" {{ $client->gender === 'l' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="p" {{ $client->gender === 'p' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>

                                    {{-- Pendidikan --}}
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Pendidikan Terakhir <span class="text-red-400">*</span></label>
                                        <select name="education" x-model="education" required
                                                class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
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

                                    {{-- Phone --}}
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Nomor Telepon / WhatsApp <span class="text-red-400">*</span></label>
                                        <input type="text" name="phone" x-model="phone" required
                                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    </div>

                                    {{-- Email --}}
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Alamat Email <span class="text-red-400">*</span></label>
                                        <input type="email" name="email" x-model="email" required
                                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                    </div>

                                    @if($isIndustri)
                                        {{-- Nama Perusahaan / Instansi Pengirim --}}
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Perusahaan Pengirim <span class="text-red-400">*</span></label>
                                            <input type="text" name="pic_name" x-model="pic_name" required
                                                   class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                        </div>

                                        {{-- Pekerjaan / Jabatan --}}
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Pekerjaan / Jabatan Saat Ini <span class="text-red-400">*</span></label>
                                            <input type="text" name="occupation" x-model="occupation" required
                                                   class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                                        </div>
                                    @else
                                        {{-- Agama (Wajib Non-Industri) --}}
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Agama <span class="text-red-400">*</span></label>
                                            <select name="religion" x-model="religion" required
                                                    class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
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

                                        {{-- Alamat (Wajib Non-Industri) --}}
                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Alamat Lengkap Tempat Tinggal <span class="text-red-400">*</span></label>
                                            <textarea name="address" x-model="address" rows="2" required
                                                      class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white resize-none"></textarea>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: CATATAN KLIEN (Notes)                                              --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'notes'" x-cloak class="space-y-6" x-data="{ 
        showAddNoteModal: false,
        showEditNoteModal: false,
        editNoteId: '',
        editNoteDate: '',
        editNoteText: '',
        editActionUrl: '',
        openEditModal(item) {
            this.editNoteId = item.id;
            this.editNoteDate = item.date_input;
            this.editNoteText = item.note;
            this.editActionUrl = `/clients/{{ $client->id }}/notes/${item.id}`;
            this.showEditNoteModal = true;
        }
    }">
        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white text-base">Catatan Klien & Administrasi</h3>
                    <p class="text-xs font-medium text-purple-200">Riwayat catatan internal staf, instruksi operasional, dan perkembangan klien terstruktur per tanggal</p>
                </div>
                <button type="button" @click="showAddNoteModal = true"
                        class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Tambah Catatan</span>
                </button>
            </div>

            {{-- TABEL PENYAJIAN CATATAN --}}
            @if(!empty($client->notes_list) && count($client->notes_list) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm font-medium">
                    <thead class="bg-slate-50 text-black font-semibold border-b border-[#EDE1FA] text-xs select-none">
                        <tr>
                            <th class="py-3.5 px-6 font-semibold text-black w-60 whitespace-nowrap">Tanggal & Waktu</th>
                            <th class="py-3.5 px-6 font-semibold text-black">Isi Catatan</th>
                            <th class="py-3.5 px-6 text-center font-semibold text-black w-24 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-sm">
                        @foreach($client->notes_list as $item)
                        <tr class="hover:bg-[#FDFBFF] transition group">
                            {{-- Kolom Kiri: Tanggal --}}
                            <td class="py-4 px-6 text-black align-top whitespace-nowrap">
                                <div class="font-semibold text-black text-sm">
                                    {{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-500 font-medium mt-0.5">
                                    Pukul {{ \Carbon\Carbon::parse($item['date'])->format('H:i') }} WIB
                                </div>
                            </td>

                            {{-- Kolom Kanan: Catatan --}}
                            <td class="py-4 px-6 text-black align-top text-sm leading-relaxed whitespace-pre-line font-medium">
                                {{ $item['note'] }}
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="py-4 px-6 text-center align-top whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Tombol Edit --}}
                                    <button type="button" 
                                            @click="openEditModal({{ json_encode($item) }})"
                                            class="p-1.5 rounded-lg text-slate-500 hover:text-purple-deep hover:bg-purple-50 transition cursor-pointer"
                                            title="Edit Catatan">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('clients.notes.destroy', [$client, $item['id']]) }}" method="POST" 
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
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="py-12 text-center text-slate-400 space-y-3">
                <svg class="w-12 h-12 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <p class="text-sm font-medium text-black">Belum ada riwayat catatan untuk klien ini.</p>
                <button type="button" @click="showAddNoteModal = true"
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
        <div x-show="showAddNoteModal" x-cloak 
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate-scale-up">
                {{-- Header Modal --}}
                <div class="px-6 py-4.5 bg-purple-deep text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white tracking-tight">Tambah Catatan Klien</h3>
                        <p class="text-xs font-semibold text-purple-200">Tambahkan catatan baru dengan tanggal yang dapat disesuaikan</p>
                    </div>
                    <button type="button" @click="showAddNoteModal = false" 
                            class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-lg font-bold"
                            title="Tutup">
                        &times;
                    </button>
                </div>

                {{-- Form Body --}}
                <form action="{{ route('clients.notes.store', $client) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-black mb-1.5">Tanggal & Waktu Catatan:</label>
                        <input type="datetime-local" name="date" value="{{ now()->format('Y-m-d\TH:i') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                        <span class="text-2xs text-gray-500 font-medium mt-1 block">Anda dapat memilih tanggal lampau untuk mendokumentasikan riwayat sebelum-sebelumnya.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black mb-1.5">Isi Catatan Administrasi / Operasional:</label>
                        <textarea name="note" rows="5" required
                                  class="w-full p-4 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white resize-y leading-relaxed"
                                  placeholder="Tuliskan isi catatan, riwayat sesi, instruksi operasional, atau perkembangan klien..."></textarea>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddNoteModal = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-purple-deep text-white text-xs font-semibold hover:opacity-95 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Simpan Catatan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL EDIT CATATAN                                             --}}
        {{-- ============================================================== --}}
        <div x-show="showEditNoteModal" x-cloak 
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate-scale-up">
                {{-- Header Modal --}}
                <div class="px-6 py-4.5 bg-purple-deep text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-white tracking-tight">Edit Catatan Klien</h3>
                        <p class="text-xs font-semibold text-purple-200">Perbarui tanggal atau isi catatan</p>
                    </div>
                    <button type="button" @click="showEditNoteModal = false" 
                            class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-lg font-bold"
                            title="Tutup">
                        &times;
                    </button>
                </div>

                {{-- Form Body --}}
                <form :action="editActionUrl" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-black mb-1.5">Tanggal & Waktu Catatan:</label>
                        <input type="datetime-local" name="date" x-model="editNoteDate" required
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black mb-1.5">Isi Catatan Administrasi / Operasional:</label>
                        <textarea name="note" rows="5" x-model="editNoteText" required
                                  class="w-full p-4 rounded-xl border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black bg-white resize-y leading-relaxed"></textarea>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="showEditNoteModal = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-purple-deep text-white text-xs font-semibold hover:opacity-95 transition shadow-xs cursor-pointer flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Perbarui Catatan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 3: KONSELING (Formulir Intake + Jadwal Booking)                       --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'konseling'" x-cloak class="space-y-6">
        {{-- Bagian A: Tabel Formulir Pendaftaran Konseling --}}
        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                <h3 class="font-semibold text-white text-base">Rekam Jejak Formulir Masuk</h3>
                <p class="text-xs font-medium text-purple-200">Daftar kuesioner dan formulir pendaftaran konseling yang dikirimkan oleh klien</p>
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
                            <th class="py-3 px-4 font-semibold text-black">ID</th>
                            <th class="py-3 px-4 font-semibold text-black">Jenis Layanan</th>
                            <th class="py-3 px-4 font-semibold text-black">Waktu Pendaftaran Masuk</th>
                            <th class="py-3 px-4 text-center font-semibold text-black">Status</th>
                            <th class="py-3 px-4 text-center font-semibold text-black">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-sm">
                        @foreach($counselingForms->sortByDesc('created_at') as $form)
                        @php
                            $formTypeLabel = $form->form_type_label;
                        @endphp
                        <tr class="hover:bg-[#FDFBFF] transition">
                            <td class="py-3 px-4 font-medium text-black text-sm whitespace-nowrap">
                                {{ $form->ticket_number ?? '#' . $form->id }}
                            </td>
                            <td class="py-3 px-4 font-medium text-black text-sm whitespace-nowrap">
                                {{ $formTypeLabel }}
                            </td>
                            <td class="py-3 px-4 font-medium text-black text-sm whitespace-nowrap">
                                {{ $form->created_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-3 px-4 text-center font-medium text-black text-sm capitalize whitespace-nowrap">
                                {{ $form->status ?? 'Baru' }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <button type="button" @click="openDossier({{ json_encode($form) }})"
                                        class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-purple-50 text-purple-deep border border-[#EDE1FA] font-medium text-sm transition inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span>Lihat Detail</span>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Bagian B: Jadwal Booking --}}
        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white text-base">Daftar Sesi Booking & Konseling</h3>
                    <p class="text-xs font-medium text-purple-200">Riwayat sesi konseling dan tes psikologi yang terjadwal</p>
                </div>
                <button type="button" @click="$dispatch('open-booking-modal', { client_id: '{{ $client->id }}' })"
                        class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Buat Booking Baru</span>
                </button>
            </div>
            @if($client->bookings->isEmpty())
            <div class="py-10 text-center text-slate-400 space-y-2">
                <svg class="w-10 h-10 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p class="text-sm font-medium text-black">Belum ada sesi booking untuk klien ini.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm font-medium">
                    <thead class="bg-gray-50 text-black font-semibold border-b border-[#EDE1FA] text-xs select-none">
                        <tr>
                            <th class="py-3 px-4 font-semibold text-black">No. Booking</th>
                            <th class="py-3 px-4 font-semibold text-black">Layanan</th>
                            <th class="py-3 px-4 font-semibold text-black">Jadwal Sesi</th>
                            <th class="py-3 px-4 font-semibold text-black">Konselor</th>
                            <th class="py-3 px-4 text-center font-semibold text-black">Status</th>
                            <th class="py-3 px-4 text-center font-semibold text-black">Pembayaran</th>
                            <th class="py-3 px-4 text-center font-semibold text-black">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-sm">
                        @foreach($client->bookings->sortByDesc('booking_date') as $b)
                        <tr class="hover:bg-[#FDFBFF] transition">
                            <td class="py-3 px-4 font-medium text-black text-sm whitespace-nowrap">{{ $b->booking_number ?? '#' . $b->id }}</td>
                            <td class="py-3 px-4 font-medium text-black text-sm">{{ $b->kategori }}</td>
                            <td class="py-3 px-4">
                                <span class="font-medium text-black block text-sm">{{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}</span>
                                <span class="text-xs text-gray-500 font-medium">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }} WIB</span>
                            </td>
                            <td class="py-3 px-4 font-medium text-black text-sm">{{ $b->counselor->name ?? 'Belum Ditugaskan' }}</td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
                                    {{ $b->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($b->status === 'batal' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $b->status_label ?? $b->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $b->paymentTransaction?->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $b->paymentTransaction?->status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('bookings.show', $b) }}" class="text-purple-deep font-medium hover:text-orange transition text-sm">Detail &rarr;</a>
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
    {{-- TAB 4: PSIKOTES (Formulir Masuk + Laporan Hasil Psikotes)                 --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'psychotest'" x-cloak class="space-y-6">
        {{-- Bagian A: Tabel Formulir Pendaftaran / Asesmen Psikotes Masuk --}}
        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                <h3 class="font-semibold text-white text-base">Rekam Jejak Formulir Masuk</h3>
                <p class="text-xs font-medium text-purple-200">Daftar kuesioner dan formulir asesmen psikologi yang dikirimkan oleh klien</p>
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
                            <th class="py-3 px-4 font-semibold text-black">ID</th>
                            <th class="py-3 px-4 font-semibold text-black">Jenis Layanan</th>
                            <th class="py-3 px-4 font-semibold text-black">Waktu Pendaftaran Masuk</th>
                            <th class="py-3 px-4 text-center font-semibold text-black">Status</th>
                            <th class="py-3 px-4 text-center font-semibold text-black">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F3EAFB] text-black font-medium text-sm">
                        @foreach($psychotestForms->sortByDesc('created_at') as $form)
                        @php
                            $formTypeLabel = $form->form_type_label;
                        @endphp
                        <tr class="hover:bg-[#FDFBFF] transition">
                            <td class="py-3 px-4 font-medium text-black text-sm whitespace-nowrap">
                                {{ $form->ticket_number ?? '#' . $form->id }}
                            </td>
                            <td class="py-3 px-4 font-medium text-black text-sm whitespace-nowrap">
                                {{ $formTypeLabel }}
                            </td>
                            <td class="py-3 px-4 font-medium text-black text-sm whitespace-nowrap">
                                {{ $form->created_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-3 px-4 text-center font-medium text-black text-sm capitalize whitespace-nowrap">
                                {{ $form->status ?? 'Baru' }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <button type="button" @click="openDossier({{ json_encode($form) }})"
                                        class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-purple-50 text-purple-deep border border-[#EDE1FA] font-medium text-sm transition inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span>Lihat Detail</span>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Bagian B: Laporan Hasil Psikotes --}}
        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-white text-base">Laporan Hasil Psikotes (Psychological Report)</h3>
                    <p class="text-xs font-medium text-purple-200">Dokumen evaluasi psikotes, rekomendasi, dan status pengiriman ke klien</p>
                </div>
                <button type="button" @click="$dispatch('open-test-result-modal', { client_id: {{ $client->id }} })"
                        class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Upload Hasil Psikotes</span>
                </button>
            </div>

            @if($client->testResults->isEmpty())
            <div class="py-12 text-center text-slate-400 space-y-2">
                <svg class="w-12 h-12 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                <p class="text-sm font-medium text-black">Belum ada dokumen hasil psikotes untuk klien ini.</p>
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
                            <h4 class="text-base font-semibold text-black">{{ $test->report_title ?? 'Laporan Asesmen Psikotes' }}</h4>
                            <p class="text-xs font-medium text-gray-500">Tanggal Tes: {{ $test->test_date ? \Carbon\Carbon::parse($test->test_date)->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('test-results.show', $test) }}" class="px-3.5 py-2 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
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
    {{-- TAB 5: PENGATURAN KLIEN & AKSI                                            --}}
    {{-- ========================================================================= --}}
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
                        Nomor WhatsApp belum tercatat di biodata klien.
                    </p>
                @endif
            </div>

            <div>
                @if(!empty($waPhone))
                    <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode('Halo ' . $client->name . ', saya dari Unit Konsultasi Psikologi (UC PSC).') }}"
                       target="_blank" rel="noopener noreferrer"
                       class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <span>Hubungi via WhatsApp</span>
                    </a>
                @else
                    <button type="button" @click="activeTab = 'profile'; editMode = true"
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
                <span>Hapus Klien</span>
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
                    
                    {{-- Modal Header (Hanya Nama dan Jenis Layanan) --}}
                    <div class="px-7 py-5 bg-purple-deep text-white flex items-center justify-between shrink-0">
                        <div class="space-y-1">
                            <h3 class="text-xl sm:text-2xl font-bold text-white tracking-tight" x-text="selectedForm ? (selectedForm.client_name || selectedForm.answers?.nama_lengkap || 'Detail Formulir') : ''"></h3>
                            <p class="text-sm sm:text-base font-semibold text-purple-200" x-text="selectedForm ? (selectedForm.form_type_label || (selectedForm.form_type === 'dewasa' ? 'Konseling Dewasa' : selectedForm.form_type)) : ''"></p>
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
                            <button type="button" @click="showDossierModal = false" 
                                    class="w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-xl font-bold"
                                    title="Tutup">
                                &times;
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body (Scrollable Answers) --}}
                    <div class="p-6 sm:p-8 overflow-y-auto space-y-6 text-black bg-white">
                        <template x-if="selectedForm && selectedForm.answers">
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
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Pernyataan:</span>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                            <span class="text-black text-sm font-semibold" x-text="selectedForm.answers.consent_agree || 'Setuju'"></span>
                                                        </div>
                                                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">"Semua keterangan yang saya berikan dalam Formulir Riwayat Hidup ini, saya buat dengan jujur dan sungguh-sungguh."</p>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col justify-center">
                                                        <span class="text-gray-500 block font-medium text-xs">Waktu Pengisian:</span>
                                                        <span class="text-black text-sm font-semibold block mt-1" x-text="selectedForm.created_at ? (selectedForm.created_at.slice(0, 19).replace('T', ' ') + ' WIB') : '-'"></span>
                                                        <span class="text-gray-400 text-xs mt-0.5" x-text="'Nomor Tiket: ' + (selectedForm.ticket_number || '-')"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- DATA DIRI & POSISI PEKERJAAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Diri & Posisi Pekerjaan</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Nama Lengkap & Gelar:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.nama_lengkap || selectedForm.client_name || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Posisi yang Dituju:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.posisi_dituju || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Pekerjaan Saat Ini:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.pekerjaan_saat_ini || '-'"></span>
                                                    </div>
                                                    <template x-if="selectedForm.answers.nama_perusahaan">
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                            <span class="text-gray-500 block font-medium text-xs">Nama Perusahaan:</span>
                                                            <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.nama_perusahaan"></span>
                                                        </div>
                                                    </template>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Jenis Kelamin:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.jenis_kelamin || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Tempat, Tanggal Lahir:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.tempat_tanggal_lahir || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Urutan Kelahiran:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.urutan_kelahiran || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Nomor WhatsApp / HP:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.phone || selectedForm.client_phone || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Alamat Email:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.email || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Agama / Kepercayaan:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.agama || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Suku Bangsa:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.suku_bangsa || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Status Perkawinan:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.status_perkawinan || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Pendidikan Terakhir:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.pendidikan_terakhir || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Asal Kota:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.asal_kota || '-'"></span>
                                                    </div>
                                                    <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Alamat Tinggal Sekarang:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.alamat_sekarang || selectedForm.answers.alamat || '-'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- KUESIONER STRES PSS-10 --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Kuesioner Stres (PSS-10)</span>
                                                </h4>
                                                <span class="text-xs font-semibold px-3 py-1 bg-white/20 text-white rounded-full">Skala 0 - 4</span>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-3">
                                                <p class="text-xs font-medium text-gray-500 mb-2">0 = Tidak Pernah | 1 = Hampir Tidak Pernah | 2 = Kadang-kadang | 3 = Cukup Sering | 4 = Sangat Sering</p>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm font-medium">
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
                                                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between gap-3">
                                                        <span class="text-xs text-black font-medium leading-snug">{{ $pssText }}</span>
                                                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-200 text-black font-bold text-xs shrink-0"
                                                              x-text="selectedForm.answers.pss_{{ $pssNum }} !== undefined && selectedForm.answers.pss_{{ $pssNum }} !== null && selectedForm.answers.pss_{{ $pssNum }} !== '' ? selectedForm.answers.pss_{{ $pssNum }} : '-'"></span>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        {{-- SUSUNAN ANGGOTA KELUARGA --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Susunan Anggota Keluarga</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-5">
                                                {{-- Ayah & Ibu --}}
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">Profil Ayah</span>
                                                        <div class="text-sm font-medium space-y-1 text-black">
                                                            <p><span class="text-gray-500 text-xs">Nama:</span> <strong class="text-black" x-text="selectedForm.answers.ayah_nama || '-'"></strong></p>
                                                            <p><span class="text-gray-500 text-xs">Usia:</span> <span class="text-black" x-text="selectedForm.answers.ayah_usia ? selectedForm.answers.ayah_usia + ' tahun' : '-'"></span></p>
                                                            <p><span class="text-gray-500 text-xs">Pendidikan:</span> <span class="text-black" x-text="selectedForm.answers.ayah_pendidikan || '-'"></span></p>
                                                            <p><span class="text-gray-500 text-xs">Pekerjaan:</span> <span class="text-black" x-text="selectedForm.answers.ayah_pekerjaan || '-'"></span></p>
                                                        </div>
                                                    </div>
                                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">Profil Ibu</span>
                                                        <div class="text-sm font-medium space-y-1 text-black">
                                                            <p><span class="text-gray-500 text-xs">Nama:</span> <strong class="text-black" x-text="selectedForm.answers.ibu_nama || '-'"></strong></p>
                                                            <p><span class="text-gray-500 text-xs">Usia:</span> <span class="text-black" x-text="selectedForm.answers.ibu_usia ? selectedForm.answers.ibu_usia + ' tahun' : '-'"></span></p>
                                                            <p><span class="text-gray-500 text-xs">Pendidikan:</span> <span class="text-black" x-text="selectedForm.answers.ibu_pendidikan || '-'"></span></p>
                                                            <p><span class="text-gray-500 text-xs">Pekerjaan:</span> <span class="text-black" x-text="selectedForm.answers.ibu_pekerjaan || '-'"></span></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Pasangan (jika ada) --}}
                                                <template x-if="selectedForm.answers.pasangan_nama">
                                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">Profil Pasangan</span>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm font-medium text-black">
                                                            <div><span class="text-gray-500 text-xs block">Nama:</span> <strong class="text-black" x-text="selectedForm.answers.pasangan_nama"></strong></div>
                                                            <div><span class="text-gray-500 text-xs block">Usia:</span> <span class="text-black" x-text="selectedForm.answers.pasangan_usia ? selectedForm.answers.pasangan_usia + ' tahun' : '-'"></span></div>
                                                            <div><span class="text-gray-500 text-xs block">Pendidikan:</span> <span class="text-black" x-text="selectedForm.answers.pasangan_pendidikan || '-'"></span></div>
                                                            <div><span class="text-gray-500 text-xs block">Pekerjaan:</span> <span class="text-black" x-text="selectedForm.answers.pasangan_pekerjaan || '-'"></span></div>
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Saudara Kandung --}}
                                                <template x-if="selectedForm.answers.saudara_1_nama">
                                                    <div class="space-y-2.5">
                                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">Saudara Kandung</span>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                                            @foreach(range(1, 5) as $sdrNum)
                                                            <template x-if="selectedForm.answers.saudara_{{ $sdrNum }}_nama">
                                                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1">
                                                                    <div class="font-bold text-black text-sm" x-text="'Saudara {{ $sdrNum }}: ' + selectedForm.answers.saudara_{{ $sdrNum }}_nama"></div>
                                                                    <p class="text-black"><span class="text-gray-500">Usia:</span> <span x-text="(selectedForm.answers.saudara_{{ $sdrNum }}_usia || '-') + ' th'"></span> | <span class="text-gray-500">Gender:</span> <span x-text="selectedForm.answers.saudara_{{ $sdrNum }}_jenis_kelamin || '-'"></span></p>
                                                                    <p class="text-black"><span class="text-gray-500">Pendidikan:</span> <span x-text="selectedForm.answers.saudara_{{ $sdrNum }}_pendidikan || '-'"></span></p>
                                                                    <p class="text-black"><span class="text-gray-500">Pekerjaan:</span> <span x-text="selectedForm.answers.saudara_{{ $sdrNum }}_pekerjaan || '-'"></span></p>
                                                                </div>
                                                            </template>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </template>

                                                {{-- Anak --}}
                                                <template x-if="selectedForm.answers.anak_1_nama">
                                                    <div class="space-y-2.5">
                                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">Anak</span>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                                            @foreach(range(1, 5) as $ankNum)
                                                            <template x-if="selectedForm.answers.anak_{{ $ankNum }}_nama">
                                                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1">
                                                                    <div class="font-bold text-black text-sm" x-text="'Anak {{ $ankNum }}: ' + selectedForm.answers.anak_{{ $ankNum }}_nama"></div>
                                                                    <p class="text-black"><span class="text-gray-500">Usia:</span> <span x-text="(selectedForm.answers.anak_{{ $ankNum }}_usia || '-') + ' th'"></span> | <span class="text-gray-500">Gender:</span> <span x-text="selectedForm.answers.anak_{{ $ankNum }}_jenis_kelamin || '-'"></span></p>
                                                                    <p class="text-black"><span class="text-gray-500">Pendidikan:</span> <span x-text="selectedForm.answers.anak_{{ $ankNum }}_pendidikan || '-'"></span></p>
                                                                    <p class="text-black"><span class="text-gray-500">Pekerjaan:</span> <span x-text="selectedForm.answers.anak_{{ $ankNum }}_pekerjaan || '-'"></span></p>
                                                                </div>
                                                            </template>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- RIWAYAT PENDIDIKAN FORMAL --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Riwayat Pendidikan Formal</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                {{-- Card Khusus IPK Terakhir --}}
                                                <template x-if="selectedForm.answers.ipk_terakhir">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 sm:max-w-xs">
                                                        <span class="text-gray-500 block font-medium text-xs">IPK Terakhir:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.ipk_terakhir"></span>
                                                    </div>
                                                </template>

                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                                    @foreach(range(1, 5) as $eduNum)
                                                    <template x-if="selectedForm.answers.nama_sekolah_{{ $eduNum }}">
                                                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                                                            <div class="flex items-center justify-between">
                                                                <h5 class="text-sm font-bold text-black" x-text="selectedForm.answers.nama_sekolah_{{ $eduNum }}"></h5>
                                                                <span class="text-xs text-black font-semibold" x-text="(selectedForm.answers.tahun_masuk_sekolah_{{ $eduNum }} || '-') + ' - ' + (selectedForm.answers.tahun_keluar_sekolah_{{ $eduNum }} || '-')"></span>
                                                            </div>
                                                            <p class="text-xs"><span class="text-gray-500">Jurusan/Keterangan:</span> <span class="text-black font-medium" x-text="selectedForm.answers.keterangan_sekolah_{{ $eduNum }} || '-'"></span></p>
                                                            <p class="text-xs"><span class="text-gray-500">Kota:</span> <span class="text-black font-medium" x-text="selectedForm.answers.kota_sekolah_{{ $eduNum }} || '-'"></span></p>
                                                        </div>
                                                    </template>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        {{-- PENDIDIKAN NON FORMAL / KURSUS --}}
                                        <template x-if="selectedForm.answers.jenis_kursus_1 || selectedForm.answers.jenis_kursus_2 || selectedForm.answers.jenis_kursus_3">
                                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                        <span>Pendidikan Non Formal / Kursus</span>
                                                    </h4>
                                                </div>
                                                <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                                    @foreach(range(1, 3) as $krsNum)
                                                    <template x-if="selectedForm.answers.jenis_kursus_{{ $krsNum }}">
                                                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1 text-xs">
                                                            <h5 class="text-sm font-bold text-black" x-text="selectedForm.answers.jenis_kursus_{{ $krsNum }}"></h5>
                                                            <p><span class="text-gray-500">Tempat:</span> <span class="text-black font-medium" x-text="selectedForm.answers.tempat_kursus_{{ $krsNum }} || '-'"></span></p>
                                                            <p><span class="text-gray-500">Durasi:</span> <span class="text-black font-medium" x-text="selectedForm.answers.lama_kursus_{{ $krsNum }} || '-'"></span></p>
                                                        </div>
                                                    </template>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </template>

                                        {{-- RIWAYAT PEKERJAAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Riwayat Pekerjaan</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                {{-- Ekspektasi Gaji & Tunjangan --}}
                                                <template x-if="selectedForm.answers.ekspektasi_gaji_tunjangan">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Ekspektasi Gaji & Tunjangan:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.ekspektasi_gaji_tunjangan"></span>
                                                    </div>
                                                </template>

                                                {{-- List Perusahaan --}}
                                                <div class="space-y-3.5">
                                                    @foreach(range(1, 5) as $jobNum)
                                                    <template x-if="selectedForm.answers.instansi_{{ $jobNum }}_nama">
                                                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 pb-2 border-b border-slate-200">
                                                                <div>
                                                                    <h5 class="text-sm font-bold text-black" x-text="selectedForm.answers.instansi_{{ $jobNum }}_nama"></h5>
                                                                    <p class="text-xs font-semibold text-black" x-text="selectedForm.answers.instansi_{{ $jobNum }}_jabatan || '-'"></p>
                                                                </div>
                                                                <span class="text-xs font-medium text-black" x-text="(selectedForm.answers.instansi_{{ $jobNum }}_tahun_masuk || '-') + ' - ' + (selectedForm.answers.instansi_{{ $jobNum }}_tahun_keluar || '-')"></span>
                                                            </div>
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                                                <div><span class="text-gray-500">Gaji Terakhir:</span> <strong class="text-black font-semibold" x-text="selectedForm.answers.instansi_{{ $jobNum }}_gaji || '-'"></strong></div>
                                                                <div><span class="text-gray-500">Alasan Berhenti:</span> <span class="text-black font-medium" x-text="selectedForm.answers.instansi_{{ $jobNum }}_alasan_berhenti || '-'"></span></div>
                                                            </div>
                                                            <template x-if="selectedForm.answers.instansi_{{ $jobNum }}_tugas">
                                                                <div class="pt-1.5 border-t border-slate-200">
                                                                    <span class="text-2xs font-bold text-gray-500 uppercase tracking-wider block">Uraian Tugas / Tanggung Jawab:</span>
                                                                    <p class="text-xs text-black leading-relaxed mt-0.5 whitespace-pre-wrap" x-text="selectedForm.answers.instansi_{{ $jobNum }}_tugas"></p>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        {{-- PENGALAMAN ORGANISASI & PRESTASI --}}
                                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                            {{-- Organisasi --}}
                                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                        <span>Pengalaman Organisasi</span>
                                                    </h4>
                                                </div>
                                                <div class="p-5 sm:p-6 space-y-3">
                                                    @foreach(range(1, 3) as $orgNum)
                                                    <template x-if="selectedForm.answers.organisasi_{{ $orgNum }}_nama">
                                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-0.5">
                                                            <div class="font-bold text-black text-sm" x-text="selectedForm.answers.organisasi_{{ $orgNum }}_nama"></div>
                                                            <p class="text-black font-semibold" x-text="selectedForm.answers.organisasi_{{ $orgNum }}_jabatan || '-'"></p>
                                                            <p class="text-gray-500"><span class="text-gray-500">Periode:</span> <span class="text-black font-medium" x-text="selectedForm.answers.organisasi_{{ $orgNum }}_lama || '-'"></span></p>
                                                        </div>
                                                    </template>
                                                    @endforeach
                                                    <template x-if="!selectedForm.answers.organisasi_1_nama && !selectedForm.answers.organisasi_2_nama">
                                                        <p class="text-xs text-gray-400 italic">Tidak ada pengalaman organisasi tercatat.</p>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Prestasi --}}
                                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                        <span>Prestasi yang Diraih</span>
                                                    </h4>
                                                </div>
                                                <div class="p-5 sm:p-6">
                                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <p class="text-black text-sm font-medium leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.prestasi || 'Tidak ada prestasi tercatat.'"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- DESKRIPSI DIRI & KEPRIBADIAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Deskripsi Diri & Riwayat Kesehatan</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6 space-y-4">
                                                {{-- Riwayat Medis / Psikologis --}}
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Riwayat Trauma:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.riwayat_trauma || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Penyakit / Rawat Inap:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.riwayat_penyakit_opname || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Konsultasi Psikolog:</span>
                                                        <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.konsultasi_psikolog || '-'"></span>
                                                    </div>
                                                </div>

                                                {{-- Kelebihan & Kelemahan --}}
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">3 Kelebihan Diri:</span>
                                                        <ul class="text-xs font-medium text-black space-y-1.5 list-disc list-inside">
                                                            <li x-text="selectedForm.answers.kelebihan_1 || '-'"></li>
                                                            <li x-text="selectedForm.answers.kelebihan_2 || '-'"></li>
                                                            <li x-text="selectedForm.answers.kelebihan_3 || '-'"></li>
                                                        </ul>
                                                    </div>
                                                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">3 Kelemahan Diri:</span>
                                                        <ul class="text-xs font-medium text-black space-y-1.5 list-disc list-inside">
                                                            <li x-text="selectedForm.answers.kelemahan_1 || '-'"></li>
                                                            <li x-text="selectedForm.answers.kelemahan_2 || '-'"></li>
                                                            <li x-text="selectedForm.answers.kelemahan_3 || '-'"></li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                {{-- Hobi --}}
                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                    <span class="text-gray-500 block font-medium text-xs">Hobi / Kegemaran:</span>
                                                    <span class="text-black text-sm font-semibold block mt-0.5" x-text="selectedForm.answers.hobi || '-'"></span>
                                                </div>

                                                {{-- Deskripsi Diri Bebas --}}
                                                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5">
                                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block">Deskripsi Diri Bebas:</span>
                                                    <p class="text-black text-sm font-medium leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.deskripsi_diri_bebas || '-'"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- ========================================================= --}}
                                {{-- VIEW DOSSIER: FORMULIR KONSELING (ANAK/DEWASA/PRA/NIKAH)  --}}
                                {{-- ========================================================= --}}
                                <template x-if="selectedForm.form_type !== 'industri'">
                                    <div class="space-y-6">

                                        {{-- KARTU: LEMBAR PERSETUJUAN (INFORMED CONSENT) --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Lembar Persetujuan (Informed Consent)</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Status Persetujuan:</span>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="20 6 9 17 4 12"></polyline>
                                                            </svg>
                                                            <span class="text-black text-sm font-medium" x-text="selectedForm.consent_agreed ? 'Setuju' : (selectedForm.answers.consent_agree || 'Setuju')"></span>
                                                        </div>
                                                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</p>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex flex-col justify-center">
                                                        <span class="text-gray-500 block font-medium text-xs">Waktu Pengisian Form:</span>
                                                        <span class="text-black text-sm font-medium block mt-1" x-text="selectedForm.created_at ? (selectedForm.created_at.slice(0, 19).replace('T', ' ') + ' WIB') : (selectedForm.consent_agreed_at ? (selectedForm.consent_agreed_at.slice(0, 19).replace('T', ' ') + ' WIB') : '-')"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- KARTU: DATA IDENTITAS & DIRI --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Identitas & Diri</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Nama Lengkap:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.client_name || selectedForm.answers.nama_lengkap || selectedForm.answers.full_name || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Jenis Kelamin:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.jenis_kelamin || selectedForm.answers.gender || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Tempat, Tanggal Lahir:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.birth_place_date || selectedForm.answers.tempat_tanggal_lahir || ((selectedForm.answers.tempat_lahir || '') + (selectedForm.answers.tempat_lahir && selectedForm.answers.tanggal_lahir ? ', ' : '') + (selectedForm.answers.tanggal_lahir || '-'))"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Urutan Kelahiran:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.urutan_kelahiran || selectedForm.answers.birth_order || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Nomor Telepon / WhatsApp:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.client_phone || selectedForm.answers.phone || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Alamat Email:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.email || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Agama / Kepercayaan:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.agama || selectedForm.answers.religion || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Suku Bangsa:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.suku_bangsa || selectedForm.answers.ethnicity || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Pendidikan Terakhir:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.pendidikan_terakhir || selectedForm.answers.last_education || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Pekerjaan Saat Ini:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.pekerjaan || selectedForm.answers.pekerjaan_saat_ini || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Hobi / Minat Kegemaran:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.hobi || '-'"></span>
                                                    </div>
                                                    <template x-if="selectedForm.answers.status_pernikahan_ortu">
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                            <span class="text-gray-500 block font-medium text-xs">Status Pernikahan Orang Tua:</span>
                                                            <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.status_pernikahan_ortu"></span>
                                                        </div>
                                                    </template>
                                                    <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Alamat Lengkap Tempat Tinggal:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.alamat || selectedForm.answers.alamat_sekarang || selectedForm.answers.current_address || '-'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- KARTU: ALASAN KONSELING & KELUHAN --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Alasan Konseling & Keluhan Utama</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                    <span class="text-gray-500 block font-medium text-xs">Alasan Ingin Melakukan Konseling:</span>
                                                    <p class="text-black text-sm font-medium block mt-1 leading-relaxed whitespace-pre-wrap" x-text="selectedForm.answers.alasan_konseling || selectedForm.answers.keterangan || selectedForm.answers.keluhan_utama || '-'"></p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- KARTU: DATA KONDISI PSIKOLOGIS --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden"
                                             x-show="selectedForm.answers.kondisi_saat_ini !== undefined || selectedForm.answers.pikiran_negatif !== undefined || selectedForm.answers.hal_ingin_ditingkatkan !== undefined || selectedForm.answers.karakter_kepribadian !== undefined || selectedForm.answers.jam_tidur !== undefined || selectedForm.answers.kondisi_anak_saat_ini !== undefined">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Data Kondisi Psikologis</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Gambaran Kondisi Saat Ini terhadap Permasalahan yang Dirasakan:</span>
                                                        <p class="text-black text-sm font-medium block mt-1 leading-relaxed" x-text="selectedForm.answers.kondisi_saat_ini || selectedForm.answers.kondisi_anak_saat_ini || '-'"></p>
                                                    </div>
                                                    <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Pikiran-Pikiran Negatif yang Sering Muncul Akhir-Akhir Ini:</span>
                                                        <p class="text-black text-sm font-medium block mt-1 leading-relaxed" x-text="selectedForm.answers.pikiran_negatif || '-'"></p>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Hal-Hal Dalam Hidup yang Ingin Ditingkatkan:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.hal_ingin_ditingkatkan || selectedForm.answers.hal_ditingkatkan || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Karakter Kepribadian:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.karakter_kepribadian || selectedForm.answers.karakter_anak || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Ketakutan / Phobia yang Dimiliki:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.ketakutan_phobia || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Rata-Rata Jam Tidur per Malam:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.jam_tidur || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Riwayat Hidup yang Menimbulkan Trauma:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.riwayat_trauma || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Riwayat Kesehatan:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.riwayat_kesehatan || '-'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- KARTU: GAMBARAN RELASI INTERPERSONAL --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden"
                                             x-show="selectedForm.answers.relasi_ayah !== undefined || selectedForm.answers.relasi_ibu !== undefined || selectedForm.answers.relasi_saudara !== undefined || selectedForm.answers.relasi_pasangan !== undefined || selectedForm.answers.relasi_anak !== undefined">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Gambaran Relasi Interpersonal</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Relasi dengan Ayah:</span>
                                                        <p class="text-black text-sm font-medium block mt-1 leading-relaxed" x-text="selectedForm.answers.relasi_ayah || '-'"></p>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Relasi dengan Ibu:</span>
                                                        <p class="text-black text-sm font-medium block mt-1 leading-relaxed" x-text="selectedForm.answers.relasi_ibu || '-'"></p>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Relasi dengan Saudara Kandung:</span>
                                                        <p class="text-black text-sm font-medium block mt-1 leading-relaxed" x-text="selectedForm.answers.relasi_saudara || '-'"></p>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Relasi dengan Pasangan:</span>
                                                        <p class="text-black text-sm font-medium block mt-1 leading-relaxed" x-text="selectedForm.answers.relasi_pasangan || '-'"></p>
                                                    </div>
                                                    <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Relasi dengan Anak:</span>
                                                        <p class="text-black text-sm font-medium block mt-1 leading-relaxed" x-text="selectedForm.answers.relasi_anak || '-'"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- KARTU: DATA PASANGAN & PERNIKAHAN (Khusus Form Pra-Nikah & Pernikahan) --}}
                                        <template x-if="selectedForm.answers.nama_pasangan">
                                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                        <span>Data Pasangan & Riwayat Hubungan</span>
                                                    </h4>
                                                </div>
                                                <div class="p-5 sm:p-6 space-y-4">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Nama Pasangan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.nama_pasangan || '-'"></span></div>
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Jenis Kelamin:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.jenis_kelamin_pasangan || '-'"></span></div>
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">TTL Pasangan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="`${selectedForm.answers.tempat_lahir_pasangan || ''}${selectedForm.answers.tempat_lahir_pasangan && selectedForm.answers.tanggal_lahir_pasangan ? ', ' : ''}${selectedForm.answers.tanggal_lahir_pasangan || '-'}`"></span></div>
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">No. HP Pasangan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.phone_pasangan || '-'"></span></div>
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Pekerjaan Pasangan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.pekerjaan_pasangan || '-'"></span></div>
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Agama Pasangan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.agama_pasangan || '-'"></span></div>
                                                        <template x-if="selectedForm.answers.tanggal_rencana_pernikahan">
                                                            <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                                <span class="text-gray-500 block font-medium text-xs">Rencana Pernikahan:</span>
                                                                <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.tanggal_rencana_pernikahan"></span>
                                                            </div>
                                                        </template>
                                                        <template x-if="selectedForm.answers.tempat_tanggal_pernikahan">
                                                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Tempat & Tanggal Pernikahan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.tempat_tanggal_pernikahan"></span></div>
                                                        </template>
                                                        <template x-if="selectedForm.answers.lama_pernikahan">
                                                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Lama Pernikahan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.lama_pernikahan"></span></div>
                                                        </template>
                                                        <template x-if="selectedForm.answers.jumlah_anak">
                                                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Jumlah Anak:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.jumlah_anak"></span></div>
                                                        </template>
                                                    </div>
                                                    <template x-if="selectedForm.answers.harapan_pernikahan">
                                                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                            <span class="text-gray-500 block font-medium text-xs">Harapan dari Pernikahan:</span>
                                                            <p class="text-black text-sm font-medium block mt-1 leading-relaxed" x-text="selectedForm.answers.harapan_pernikahan"></p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- KARTU: DATA ORANG TUA (Khusus Form Anak) --}}
                                        <template x-if="selectedForm.answers.nama_ayah || selectedForm.answers.nama_ibu">
                                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                        <span>Data Profil Orang Tua & Keluarga</span>
                                                    </h4>
                                                </div>
                                                <div class="p-5 sm:p-6 space-y-4">
                                                    <template x-if="selectedForm.answers.nama_ayah">
                                                        <div class="space-y-3">
                                                            <span class="text-xs font-bold text-black uppercase tracking-wider block">Profil Ayah</span>
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Nama Ayah:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.nama_ayah || '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Usia:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.usia_ayah ? selectedForm.answers.usia_ayah + ' tahun' : '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Pekerjaan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.pekerjaan_ayah || '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Pendidikan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.pendidikan_ayah || '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">No. HP Ayah:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.phone_ayah || '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Agama:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.agama_ayah || '-'"></span></div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-if="selectedForm.answers.nama_ibu">
                                                        <div class="space-y-3 pt-3 border-t border-[#F3EAFB]">
                                                            <span class="text-xs font-bold text-black uppercase tracking-wider block">Profil Ibu</span>
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Nama Ibu:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.nama_ibu || '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Usia:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.usia_ibu ? selectedForm.answers.usia_ibu + ' tahun' : '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Pekerjaan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.pekerjaan_ibu || '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Pendidikan:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.pendidikan_ibu || '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">No. HP Ibu:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.phone_ibu || '-'"></span></div>
                                                                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100"><span class="text-gray-500 block font-medium text-xs">Agama:</span> <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.agama_ibu || '-'"></span></div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- KARTU: INFORMASI TAMBAHAN & PREFERENSI --}}
                                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                                    <span>Informasi Tambahan & Preferensi</span>
                                                </h4>
                                            </div>
                                            <div class="p-5 sm:p-6">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Pernah Konseling Sebelumnya?:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.pernah_konseling || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Nama Konselor Lama:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.nama_konselor || selectedForm.answers.nama_konselor_lama || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Kontak Darurat:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.kontak_darurat || '-'"></span>
                                                    </div>
                                                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Sumber Mengetahui UC PSC:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.sumber_info || '-'"></span>
                                                    </div>
                                                    <div class="sm:col-span-2 lg:col-span-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                                                        <span class="text-gray-500 block font-medium text-xs">Preferensi Proses Konseling:</span>
                                                        <span class="text-black text-sm font-medium block mt-0.5" x-text="selectedForm.answers.preferensi_konseling || selectedForm.service_preference || '-'"></span>
                                                    </div>
                                                </div>
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
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-purple-deep text-white shadow-xs">Simpan</button>
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
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-purple-deep text-white shadow-xs">Simpan</button>
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

</div>
@endsection
