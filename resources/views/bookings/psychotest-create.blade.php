@extends('layouts.dashboard')

@section('title', 'Buat Booking Psikotes — UC PSC')
@section('page-title', 'Buat Booking Psikotes')
@section('page-subtitle', 'Pilih perusahaan/klien, centang formulir kandidat yang masuk, atur multi-paket dan penugasan tim')

@section('content')
@php
    $formsPayload = $unbookedForms->map(function ($f) {
        return [
            'id' => $f->id,
            'form_type' => $f->form_type,
            'client_name' => $f->client_name,
            'client_phone' => $f->client_phone,
            'company_name' => $f->company_name ?? ($f->client?->company_name ?? ''),
            'target_position' => $f->answers['posisi_dituju'] ?? ($f->answers['tujuan_asesmen'] ?? ''),
            'created_at' => $f->created_at ? $f->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') : '-',
        ];
    })->values();

    $companiesPayload = $companies->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'category' => $c->category,
        'pic_name' => $c->pic_name,
    ])->values();

    $oldItems = old('items', [
        ['service_tier' => 'industri', 'service_package_key' => 'long_staf_kerja_sama', 'quantity' => 1],
    ]);
    $oldCandidatesRaw = old('candidates', []);
    $oldCandidates = collect($oldCandidatesRaw)->values()->map(function ($cand, $idx) {
        return [
            'uid' => 'old-' . $idx,
            'client_form_id' => $cand['client_form_id'] ?? '',
            'candidate_name' => $cand['candidate_name'] ?? '',
            'target_position' => $cand['target_position'] ?? '',
            'job_level' => $cand['job_level'] ?? '',
            'service_item_index' => (int) ($cand['service_item_index'] ?? 0),
            'is_kilat' => !empty($cand['is_kilat']),
            'assigned_psikolog_id' => (string) ($cand['assigned_psikolog_id'] ?? ''),
            'assigned_editor_id' => (string) ($cand['assigned_editor_id'] ?? ''),
        ];
    })->values();
    $oldSelectedFormIds = collect($oldCandidatesRaw)->pluck('client_form_id')->filter()->map(fn ($id) => (int) $id)->values();
@endphp

<div x-data="{
    mode: '{{ old('mode', 'industri') }}',
    companyId: '{{ old('company_id', '') }}',
    picName: '{{ old('pic_name', '') }}',
    tanggalDijadwalkan: '{{ old('tanggal_dijadwalkan', now()->toDateString()) }}',
    sessionStart: '{{ old('session_start', '09:00') }}',
    sessionEnd: '{{ old('session_end', '12:00') }}',
    sessionType: '{{ old('session_type', 'online') }}',
    location: '{{ old('location', '') }}',
    billingScheme: '{{ old('billing_scheme', 'v') }}',
    requiresOutgoingLetter: {{ old('requires_outgoing_letter') ? 'true' : 'false' }},
    deliveryMode: '{{ old('delivery_mode', 'softcopy') }}',
    notes: @js(old('notes', '')),

    companies: @js($companiesPayload),
    unbookedForms: @js($formsPayload),
    catalog: @js($catalog),
    counselors: @js($counselors->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values()),
    staffs: @js($staffs->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()),

    formSearch: '',
    selectedFormIds: @js($oldSelectedFormIds),
    manualCandidates: [],
    items: @js(array_values($oldItems)),

    // Kolektif batch roles (multi-select)
    testerIds: @js(array_map('strval', (array) old('tester_ids', []))),
    skorerManualIds: @js(array_map('strval', (array) old('skorer_manual_ids', []))),
    skorerSoftwareIds: @js(array_map('strval', (array) old('skorer_software_ids', []))),

    // State modal unsaved
    unsavedModalOpen: false,
    pendingCancelUrl: '{{ route('bookings.psychotest') }}',

    get selectedCompany() {
        return this.companies.find(c => String(c.id) === String(this.companyId)) || null;
    },

    get companyCategory() {
        return this.selectedCompany ? this.selectedCompany.category : 'umum';
    },

    onCompanyChange() {
        if (this.selectedCompany && !this.picName) {
            this.picName = this.selectedCompany.pic_name || '';
        }
        if (this.mode === 'industri' && this.selectedCompany) {
            const targetSuffix = this.companyCategory === 'kerja_sama' ? '_kerja_sama' : '_umum';
            const altSuffix = this.companyCategory === 'kerja_sama' ? '_umum' : '_kerja_sama';
            this.items.forEach(it => {
                if (it.service_tier === 'industri' && it.service_package_key && it.service_package_key.endsWith(altSuffix)) {
                    const candidateKey = it.service_package_key.slice(0, -altSuffix.length) + targetSuffix;
                    if (this.packagesForTier('industri')[candidateKey]) {
                        it.service_package_key = candidateKey;
                    }
                }
            });
        }
    },

    onModeChange() {
        this.selectedFormIds = [];
        this.syncCandidatesFromForms();
        const defaultTier = this.mode === 'industri' ? 'industri' : 'non_industri_bakat_minat';
        const defaultKey = this.mode === 'industri'
            ? (this.companyCategory === 'kerja_sama' ? 'long_staf_kerja_sama' : 'long_staf_umum')
            : 'short_umum';
        this.items = [
            { service_tier: defaultTier, service_package_key: defaultKey, quantity: Math.max(1, this.candidateRows.length) }
        ];
        this.candidateRows.forEach(r => { r.service_item_index = 0; });
        this.syncItemQuantities();
    },

    syncItemQuantities() {
        if (this.candidateRows.length === 0) return;
        const counts = {};
        this.candidateRows.forEach(c => {
            const idx = Number(c.service_item_index || 0);
            counts[idx] = (counts[idx] || 0) + 1;
        });
        this.items.forEach((it, idx) => {
            it.quantity = counts[idx] || 0;
        });
    },

    get filteredForms() {
        const q = this.formSearch.toLowerCase().trim();
        return this.unbookedForms.filter(f => {
            if (this.mode === 'industri' && f.form_type !== 'industri') return false;
            if (this.mode === 'non_industri' && f.form_type !== 'non_industri') return false;
            if (!q) return true;
            return (f.client_name || '').toLowerCase().includes(q)
                || (f.company_name || '').toLowerCase().includes(q)
                || (f.target_position || '').toLowerCase().includes(q);
        });
    },

    candidateRows: @js($oldCandidates),

    syncCandidatesFromForms() {
        // Pertahankan kandidat yang sudah ada & tambahkan form baru yang dicentang
        const keepManual = this.candidateRows.filter(r => !r.client_form_id);
        const byFormId = {};
        this.candidateRows.filter(r => r.client_form_id).forEach(r => { byFormId[r.client_form_id] = r; });

        const nextRows = [];
        this.selectedFormIds.forEach(fid => {
            if (byFormId[fid]) {
                nextRows.push(byFormId[fid]);
            } else {
                const form = this.unbookedForms.find(u => String(u.id) === String(fid));
                if (form) {
                    nextRows.push({
                        uid: 'form-' + form.id,
                        client_form_id: form.id,
                        candidate_name: form.client_name,
                        target_position: form.target_position || '',
                        job_level: '',
                        service_item_index: 0,
                        is_kilat: false,
                        assigned_psikolog_id: '',
                        assigned_editor_id: '',
                    });
                }
            }
        });
        this.candidateRows = [...nextRows, ...keepManual];
        this.syncItemQuantities();
    },

    toggleFormCheck(formId) {
        const idx = this.selectedFormIds.indexOf(formId);
        if (idx === -1) {
            this.selectedFormIds.push(formId);
        } else {
            this.selectedFormIds.splice(idx, 1);
        }
        this.syncCandidatesFromForms();
    },

    addManualCandidate() {
        this.candidateRows.push({
            uid: 'manual-' + Date.now() + '-' + Math.random().toString(36).slice(2, 6),
            client_form_id: '',
            candidate_name: '',
            target_position: '',
            job_level: '',
            service_item_index: 0,
            is_kilat: false,
            assigned_psikolog_id: '',
            assigned_editor_id: '',
        });
        this.syncItemQuantities();
    },

    removeCandidate(index) {
        const row = this.candidateRows[index];
        if (row && row.client_form_id) {
            this.selectedFormIds = this.selectedFormIds.filter(id => String(id) !== String(row.client_form_id));
        }
        this.candidateRows.splice(index, 1);
        this.syncItemQuantities();
    },

    defaultPackageForTier(tier) {
        const pkgs = Object.keys(this.catalog[tier]?.packages || {});
        if (pkgs.length === 0) return '';
        if (tier === 'industri' || tier === 'non_industri_bakat_minat') {
            const suffix = (this.mode === 'industri' && this.companyCategory === 'kerja_sama') ? '_kerja_sama' : '_umum';
            const match = pkgs.find(k => k.endsWith(suffix));
            if (match) return match;
        }
        return pkgs[0];
    },

    addItem() {
        const defaultTier = this.mode === 'industri' ? 'industri' : 'non_industri_bakat_minat';
        this.items.push({
            service_tier: defaultTier,
            service_package_key: this.defaultPackageForTier(defaultTier),
            quantity: 0,
        });
        this.syncItemQuantities();
    },

    removeItem(idx) {
        if (this.items.length > 1) {
            this.items.splice(idx, 1);
            this.candidateRows.forEach(r => {
                if (Number(r.service_item_index || 0) >= this.items.length) {
                    r.service_item_index = 0;
                }
            });
            this.syncItemQuantities();
        }
    },

    packagesForTier(tier) {
        return this.catalog[tier]?.packages || {};
    },

    itemUnitPrice(item) {
        return Number(this.catalog[item.service_tier]?.packages?.[item.service_package_key]?.price || 0);
    },

    get totalBasePrice() {
        return this.items.reduce((sum, it) => sum + (this.itemUnitPrice(it) * Number(it.quantity || 0)), 0);
    },

    get isAllSpecialTier() {
        return this.items.length > 0 && this.items.every(it => it.service_tier === 'non_industri_khusus');
    },

    get psychologistQuotaSummary() {
        const counts = {};
        this.candidateRows.forEach(c => {
            if (c.assigned_psikolog_id) {
                counts[c.assigned_psikolog_id] = (counts[c.assigned_psikolog_id] || 0) + 1;
            }
        });
        const parts = Object.entries(counts).map(([id, cnt]) => {
            const p = this.counselors.find(x => String(x.id) === String(id));
            return `${p ? p.name : 'Psikolog'} (${cnt})`;
        });
        return parts.join(', ') || '-';
    },

    get editorQuotaSummary() {
        const counts = {};
        this.candidateRows.forEach(c => {
            if (c.assigned_editor_id) {
                counts[c.assigned_editor_id] = (counts[c.assigned_editor_id] || 0) + 1;
            }
        });
        const parts = Object.entries(counts).map(([id, cnt]) => {
            const s = this.staffs.find(x => String(x.id) === String(id));
            return `${s ? s.name : 'Editor'} (${cnt})`;
        });
        return parts.join(', ') || '-';
    },

    get kilatConflictWarning() {
        const kilatCounts = {};
        for (const c of this.candidateRows) {
            if (c.is_kilat && c.assigned_psikolog_id) {
                kilatCounts[c.assigned_psikolog_id] = (kilatCounts[c.assigned_psikolog_id] || 0) + 1;
                if (kilatCounts[c.assigned_psikolog_id] > 1) {
                    const p = this.counselors.find(x => String(x.id) === String(c.assigned_psikolog_id));
                    return `${p ? p.name : 'Psikolog'} tidak boleh memegang lebih dari 1 kandidat KILAT pada hari yang sama.`;
                }
            }
        }
        return '';
    },

    formatRupiah(val) {
        return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
    },

    attemptCancel() {
        const isDirty = this.candidateRows.length > 0
            || Boolean(this.companyId)
            || Boolean(this.picName)
            || Boolean(this.location)
            || Boolean(this.notes)
            || this.requiresOutgoingLetter
            || this.items.length > 1
            || this.testerIds.length > 0
            || this.skorerManualIds.length > 0
            || this.skorerSoftwareIds.length > 0;
        if (isDirty) {
            this.unsavedModalOpen = true;
            return;
        }
        window.location.href = this.pendingCancelUrl;
    }
}" class="space-y-6">

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 space-y-1">
            <p class="text-xs font-semibold text-red-700">Mohon perbaiki kesalahan berikut sebelum menyimpan booking:</p>
            <ul class="list-disc list-inside text-xs font-medium text-red-700 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('psychotest-bookings.store') }}" class="space-y-6">
        @csrf

        {{-- Card 1: Klien / Perusahaan & Pemilihan Kandidat --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Klien &amp; Pemilihan Kandidat Formulir</h3>
                <span class="text-xs text-purple-100 font-medium" x-text="candidateRows.length + ' Kandidat Terpilih'"></span>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Jenis Layanan Psikotes *</label>
                        <select name="mode" x-model="mode" @change="onModeChange()"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            <option value="industri">Psikotes Industri (Perusahaan)</option>
                            <option value="non_industri">Psikotes Non-Industri (Individu / Khusus)</option>
                        </select>
                    </div>

                    <div x-show="mode === 'industri'">
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Perusahaan *</label>
                        <select name="company_id" x-model="companyId" @change="onCompanyChange()" :required="mode === 'industri'"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            <option value="">-- Pilih Perusahaan --</option>
                            <template x-for="comp in companies" :key="comp.id">
                                <option :value="comp.id" x-text="comp.name + ' (' + (comp.category === 'kerja_sama' ? 'Kerja Sama' : 'Umum') + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Nama PIC (Jika Perusahaan / Kelompok)</label>
                        <input type="text" name="pic_name" x-model="picName" placeholder="Contoh: Ibu Nana (HRD)"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                </div>

                {{-- Daftar Formulir Belum Dibooking --}}
                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/70 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-black">Centang Calon Klien yang Sudah Mengisi Formulir</p>
                            <p class="text-xs font-medium text-slate-500">Menampilkan formulir berstatus Belum Dibooking</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="formSearch" placeholder="Filter nama kandidat / perusahaan..."
                                class="px-3.5 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                            <button type="button" @click="addManualCandidate()" class="px-3.5 py-2 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-xs font-semibold shadow-xs cursor-pointer whitespace-nowrap">
                                + Tambah Manual
                            </button>
                        </div>
                    </div>

                    <div class="max-h-56 overflow-y-auto custom-scrollbar divide-y divide-slate-200 bg-white rounded-lg border border-slate-200">
                        <template x-for="f in filteredForms" :key="f.id">
                            <label class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer">
                                <div class="flex items-center gap-3 min-w-0">
                                    <input type="checkbox" :checked="selectedFormIds.includes(f.id)" @change="toggleFormCheck(f.id)"
                                        class="w-4 h-4 rounded border-slate-300 text-purple-deep focus:ring-purple-deep">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-black truncate" x-text="f.client_name"></p>
                                        <p class="text-[11px] font-medium text-slate-500 truncate"
                                           x-text="(f.company_name ? f.company_name + ' · ' : '') + (f.target_position || 'Posisi belum diisi') + ' · ' + f.created_at"></p>
                                    </div>
                                </div>
                                <span class="text-xs font-medium text-slate-500 whitespace-nowrap" x-text="f.client_phone"></span>
                            </label>
                        </template>
                        <div x-show="filteredForms.length === 0" class="px-4 py-6 text-center text-xs font-medium text-slate-400">
                            Tidak ada formulir belum dibooking yang cocok. Gunakan tombol "+ Tambah Manual" bila perlu.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Multi-Paket & Konfigurasi Layanan --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Paket Layanan, Jadwal, &amp; Ketentuan Pengiriman</h3>
                <span class="text-xs text-purple-100 font-medium" x-text="'Estimasi Harga Dasar (Sebelum PPN/PPh): ' + formatRupiah(totalBasePrice)"></span>
            </div>

            <div class="p-6 space-y-5">
                {{-- Multi-paket items --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-medium text-slate-500 block">Daftar Paket / Level Layanan *</label>
                        <button type="button" @click="addItem()" class="px-3.5 py-1.5 rounded-lg bg-white border border-[#D9C2F0] text-black hover:text-purple-deep text-xs font-semibold cursor-pointer">
                            + Tambah Paket / Level
                        </button>
                    </div>

                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-3.5 bg-slate-50 border border-slate-100 rounded-xl items-end">
                            <div class="md:col-span-3">
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Tier</label>
                                <select :name="`items[${idx}][service_tier]`" x-model="item.service_tier"
                                    @change="item.service_package_key = defaultPackageForTier(item.service_tier)"
                                    class="w-full px-3 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black min-h-[42px]">
                                    <option value="industri">Psikotes Industri</option>
                                    <option value="non_industri_bakat_minat">Non-Industri Bakat Minat</option>
                                    <option value="non_industri_khusus">Non-Industri Khusus</option>
                                </select>
                            </div>
                            <div class="md:col-span-5">
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Paket &amp; Tarif</label>
                                <select :name="`items[${idx}][service_package_key]`" x-model="item.service_package_key"
                                    class="w-full px-3 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black min-h-[42px]">
                                    <template x-for="(pkg, pKey) in packagesForTier(item.service_tier)" :key="pKey">
                                        <option :value="pKey" x-text="pkg.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-xs font-medium text-slate-500 mb-1 block">Jumlah Peserta</label>
                                <input type="number" min="0" :name="`items[${idx}][quantity]`" x-model="item.quantity"
                                    class="w-full px-3 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black min-h-[42px]">
                            </div>
                            <div class="md:col-span-2 flex items-center justify-between gap-2">
                                <div class="px-3 py-2 bg-white rounded-lg border border-slate-200 text-xs font-medium text-black flex-1 min-h-[42px] flex items-center"
                                     x-text="formatRupiah(itemUnitPrice(item) * (item.quantity || 0))"></div>
                                <button type="button" x-show="items.length > 1" @click="removeItem(idx)" class="px-2.5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Jadwal & Konfigurasi Pengiriman / Penagihan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Tanggal Pelaksanaan *</label>
                        <input type="date" name="tanggal_dijadwalkan" x-model="tanggalDijadwalkan" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Jam Mulai - Selesai</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="time" name="session_start" x-model="sessionStart" class="w-full px-2.5 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black min-h-[42px]">
                            <input type="time" name="session_end" x-model="sessionEnd" class="w-full px-2.5 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black min-h-[42px]">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Metode Pelaksanaan *</label>
                        <select name="session_type" x-model="sessionType" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            <option value="online">Online</option>
                            <option value="tatap_muka">Offline (Tatap Muka)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Lokasi / Tautan</label>
                        <input type="text" name="location" x-model="location" placeholder="Ruang UC PSC / Link Zoom"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Skema Penagihan (Invoice) *</label>
                        <select name="billing_scheme" x-model="billingScheme" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            <option value="v">Reguler / Per-Order (v) — Wajib lunas sebelum kirim</option>
                            <option value="I">Rekap Internal UCS (I) — Tagih akhir bulan</option>
                            <option value="R">Rekap Perusahaan (R) — Tagih akhir bulan</option>
                            <option value="-">Gratis / Deal Khusus (-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-500 mb-1 block">Metode Pengiriman Hasil *</label>
                        <select name="delivery_mode" x-model="deliveryMode" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black min-h-[42px]">
                            <option value="softcopy">Softcopy (Tanpa Cetak &amp; Resi)</option>
                            <option value="hardcopy">Hardcopy (Wajib Cetak &amp; Resi)</option>
                            <option value="both">Keduanya (Softcopy &amp; Hardcopy)</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="w-full px-3.5 py-2.5 rounded-lg bg-slate-50 border border-slate-100 min-h-[42px] flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="requires_outgoing_letter" value="1" x-model="requiresOutgoingLetter"
                                class="w-4 h-4 rounded border-slate-300 text-purple-deep focus:ring-purple-deep">
                            <span class="text-sm font-medium text-black">Perlu Surat Keluar?</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-500 mb-1 block">Catatan Booking</label>
                    <textarea name="notes" x-model="notes" rows="2" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D9C2F0] bg-white text-sm font-medium text-black" placeholder="Catatan tambahan dari PIC perusahaan / instruksi khusus..."></textarea>
                </div>
            </div>
        </div>

        {{-- Card 3: Penugasan Tim Multi-Select & Pengaturan Per-Kandidat --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-purple-deep/95 text-white flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Penugasan Tim 5 Peran &amp; Prioritas KILAT</h3>
                <span class="text-xs text-purple-100 font-medium" x-text="'Psikolog: ' + psychologistQuotaSummary + ' | Editor: ' + editorQuotaSummary"></span>
            </div>

            <div class="p-6 space-y-6">
                {{-- Multi-Select Kolektif Batch Roles --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2" x-show="!isAllSpecialTier">
                        <label class="text-xs font-medium text-slate-500 block">Tester (Staff — Multi-Select)</label>
                        <div class="max-h-36 overflow-y-auto custom-scrollbar space-y-1.5">
                            <template x-for="st in staffs" :key="'t-' + st.id">
                                <label class="flex items-center gap-2 text-xs font-medium text-black cursor-pointer">
                                    <input type="checkbox" name="tester_ids[]" :value="st.id" x-model="testerIds" class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                    <span x-text="st.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                        <label class="text-xs font-medium text-slate-500 block">Skorer Manual (Staff — Multi-Select)</label>
                        <div class="max-h-36 overflow-y-auto custom-scrollbar space-y-1.5">
                            <template x-for="st in staffs" :key="'sm-' + st.id">
                                <label class="flex items-center gap-2 text-xs font-medium text-black cursor-pointer">
                                    <input type="checkbox" name="skorer_manual_ids[]" :value="st.id" x-model="skorerManualIds" class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                    <span x-text="st.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 space-y-2">
                        <label class="text-xs font-medium text-slate-500 block">Skorer Software (Staff — Multi-Select)</label>
                        <div class="max-h-36 overflow-y-auto custom-scrollbar space-y-1.5">
                            <template x-for="st in staffs" :key="'ss-' + st.id">
                                <label class="flex items-center gap-2 text-xs font-medium text-black cursor-pointer">
                                    <input type="checkbox" name="skorer_software_ids[]" :value="st.id" x-model="skorerSoftwareIds" class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                    <span x-text="st.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <div x-show="isAllSpecialTier" class="p-3.5 rounded-lg bg-slate-50 border border-slate-100 text-xs font-medium text-black">
                    Paket Non-Industri Khusus tidak memerlukan penugasan Psikolog dan Tester (tahap Psikotes &amp; Draft HPP dikelola oleh Admin/Staff bertugas).
                </div>

                <div x-show="kilatConflictWarning" x-cloak class="p-3.5 rounded-lg bg-red-50 border border-red-200 text-xs font-medium text-red-700" x-text="kilatConflictWarning"></div>

                {{-- Tabel Kandidat: Pembagian Psikolog, Editor, Paket, & Flag KILAT --}}
                <div class="overflow-x-auto custom-scrollbar border border-slate-200 rounded-xl">
                    <table class="w-full text-sm font-medium">
                        <thead>
                            <tr class="bg-slate-50 text-black font-semibold border-b border-slate-200">
                                <th class="px-4 py-3 text-left font-semibold">Nama Kandidat *</th>
                                <th class="px-4 py-3 text-left font-semibold">Jabatan</th>
                                <th class="px-4 py-3 text-left font-semibold">Paket Item</th>
                                <th class="px-4 py-3 text-left font-semibold" x-show="!isAllSpecialTier">Psikolog</th>
                                <th class="px-4 py-3 text-left font-semibold">Editor</th>
                                <th class="px-4 py-3 text-center font-semibold">KILAT</th>
                                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="(cand, cIdx) in candidateRows" :key="cand.uid">
                                <tr>
                                    <td class="px-4 py-3">
                                        <input type="hidden" :name="`candidates[${cIdx}][client_form_id]`" :value="cand.client_form_id">
                                        <input type="text" :name="`candidates[${cIdx}][candidate_name]`" x-model="cand.candidate_name" required
                                            class="w-full min-w-[160px] px-3 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" :name="`candidates[${cIdx}][target_position]`" x-model="cand.target_position" placeholder="Staf / SPV / ..."
                                            class="w-full min-w-[130px] px-3 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                                    </td>
                                    <td class="px-4 py-3">
                                        <select :name="`candidates[${cIdx}][service_item_index]`" x-model="cand.service_item_index" @change="syncItemQuantities()"
                                            class="w-full min-w-[150px] px-3 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                                            <template x-for="(it, iIdx) in items" :key="iIdx">
                                                <option :value="iIdx" x-text="`Paket #${iIdx + 1}: ${it.service_package_key}`"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3" x-show="!isAllSpecialTier">
                                        <select :name="`candidates[${cIdx}][assigned_psikolog_id]`" x-model="cand.assigned_psikolog_id"
                                            class="w-full min-w-[150px] px-3 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                                            <option value="">-- Pilih Psikolog --</option>
                                            <template x-for="p in counselors" :key="p.id">
                                                <option :value="p.id" x-text="p.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <select :name="`candidates[${cIdx}][assigned_editor_id]`" x-model="cand.assigned_editor_id"
                                            class="w-full min-w-[150px] px-3 py-2 rounded-lg border border-[#D9C2F0] bg-white text-xs font-medium text-black">
                                            <option value="">-- Pilih Editor --</option>
                                            <template x-for="s in staffs" :key="s.id">
                                                <option :value="s.id" x-text="s.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" :name="`candidates[${cIdx}][is_kilat]`" value="1" x-model="cand.is_kilat"
                                            class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" @click="removeCandidate(cIdx)" class="px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-medium cursor-pointer">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="candidateRows.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-xs font-medium text-slate-400">
                                    Belum ada kandidat dipilih. Centang formulir masuk di atas atau klik "+ Tambah Manual".
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="attemptCancel()" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="candidateRows.length === 0 || Boolean(kilatConflictWarning)"
                        class="px-5 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 disabled:opacity-50 text-white font-semibold text-sm shadow-xs cursor-pointer inline-flex items-center gap-2">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Simpan Booking Psikotes</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Modal Unsaved Changes --}}
    <div x-show="unsavedModalOpen" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#1F0E38]/50 backdrop-blur-md"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-[0_30px_70px_rgba(74,35,128,0.2)] overflow-hidden">
                <div class="px-6 py-4 bg-purple-deep flex items-center justify-between text-white">
                    <h3 class="text-base font-semibold">Perubahan Belum Disimpan</h3>
                    <button type="button" @click="unsavedModalOpen = false" class="p-1 rounded-lg text-white/80 hover:text-white hover:bg-white/15 cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                        <p class="text-sm font-medium text-black">Anda memiliki perubahan yang belum disimpan. Jika meninggalkan halaman ini, isian booking akan hilang.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="unsavedModalOpen = false" class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs cursor-pointer">Batal</button>
                    <a :href="pendingCancelUrl" class="px-5 py-2.5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold text-sm shadow-xs">Ya, Tinggalkan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
