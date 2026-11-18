<div x-data="{
    bookingModalOpen: false,
    serviceType: '{{ old('kategori', request('kategori', 'konseling')) }}',
    lockCategory: null,
    jenis: '{{ old('jenis', 'individu') }}',
    clientId: '{{ old('client_id', '') }}',
    selectedClient: null,
    searchQuery: '',
    showClientSelector: false,
    today: '',
    sessionType: '{{ old('session_type', '') }}',
    participants: {{ json_encode(old('participants', [''])) }},
    clientsList: {{ json_encode($clients->values()) }},
    clientsData: {{ json_encode($clients->keyBy('id')) }},

    // Dropdown bertingkat Konseling (Default kosong sesuai permintaan user)
    counselingJenis: '{{ old('counseling_jenis', '') }}',
    counselingMetode: '{{ old('counseling_metode', '') }}',
    counselingKlien: '{{ old('counseling_klien', '') }}',
    counselingKategori: '{{ old('counseling_kategori', '') }}',

    // Katalog Psikotes Resmi & Master Perusahaan
    psikotesCatalog: @js(config('service_catalog.psikotes')),
    companiesList: @js(($companies ?? collect())->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'pic_name' => $c->pic_name, 'category' => $c->category])->values()),
    companyId: '{{ old('company_id', '') }}',
    picName: '{{ old('pic_name', '') }}',
    requiresOutgoingLetter: {{ old('requires_outgoing_letter') ? 'true' : 'false' }},
    isKilat: {{ old('is_kilat') ? 'true' : 'false' }},
    psikotesTier: '{{ old('service_tier', 'industri') }}',
    psikotesPackageKey: '{{ old('service_package_key', 'short_staf_umum') }}',

    get psikotesPackagesForTier() {
        return this.psikotesCatalog?.[this.psikotesTier]?.packages || {};
    },

    get psikotesSelectedPackage() {
        return this.psikotesPackagesForTier?.[this.psikotesPackageKey] || null;
    },

    get psikotesUnitPrice() {
        return Number(this.psikotesSelectedPackage?.price || 0);
    },

    onPsikotesTierChange() {
        const keys = Object.keys(this.psikotesPackagesForTier);
        this.psikotesPackageKey = keys[0] || '';
    },

    get isCounselingConfigured() {
        return Boolean(this.counselingJenis && this.counselingMetode && this.counselingKlien && this.counselingKategori);
    },

    get counselingPrice() {
        if (!this.counselingJenis || !this.counselingKlien || !this.counselingKategori) {
            return 0;
        }
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
        if (!this.isCounselingConfigured) {
            return '-';
        }
        return `Konseling ${this.counselingJenis} - ${this.counselingMetode} - ${this.counselingKlien} - ${this.counselingKategori}`;
    },

    formatRupiah(val) {
        if (!val || val === 0) return '-';
        return 'Rp ' + Number(val).toLocaleString('id-ID');
    },

    init() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        this.today = `${year}-${month}-${day}`;

        @if(request()->routeIs('bookings.counseling'))
            this.lockCategory = 'konseling';
            this.serviceType = 'konseling';
        @elseif(request()->routeIs('bookings.psychotest'))
            this.lockCategory = 'psikotes';
            this.serviceType = 'psikotes';
        @endif

        @if(isset($errors) && $errors->any() && old('from_modal'))
            this.bookingModalOpen = true;
            if (this.clientId && this.clientsData[this.clientId]) {
                this.selectClient(this.clientsData[this.clientId], false);
            }
        @endif
    },

    openModal(detail = null) {
        this.bookingModalOpen = true;
        this.counselingJenis = '';
        this.counselingMetode = '';
        this.counselingKlien = '';
        this.counselingKategori = '';
        this.sessionType = '';
        this.companyId = '';
        this.picName = '';
        this.requiresOutgoingLetter = false;
        this.isKilat = false;
        const initialClientId = (typeof detail === 'object' && detail !== null) ? detail.client_id : detail;
        const requestedCategory = (typeof detail === 'object' && detail !== null) ? detail.category : null;

        if (requestedCategory) {
            this.lockCategory = requestedCategory;
            this.serviceType = requestedCategory;
        } else if (this.lockCategory) {
            this.serviceType = this.lockCategory;
        }

        if (initialClientId && this.clientsData[initialClientId]) {
            this.showClientSelector = false;
            this.selectClient(this.clientsData[initialClientId], false);
        } else if (!this.clientId) {
            this.showClientSelector = true;
            this.clearClient();
        } else {
            this.showClientSelector = false;
        }
    },

    get filteredClients() {
        const query = (this.searchQuery || '').toLowerCase().trim();
        const list = Array.isArray(this.clientsList) ? this.clientsList : Object.values(this.clientsData);
        if (!query) return list;
        return list.filter(c => {
            const name = (c.name || '').toLowerCase();
            const phone = (c.phone || '').toLowerCase();
            const jenis = (c.jenis || '').toLowerCase();
            const email = (c.email || '').toLowerCase();
            return name.includes(query) || phone.includes(query) || jenis.includes(query) || email.includes(query);
        });
    },

    toggleClient(client) {
        if (this.clientId === client.id) {
            this.clearClient();
            return;
        }
        this.selectClient(client);
    },

    selectClient(client, applyDefaults = true) {
        this.selectedClient = client;
        this.clientId = client.id;
        this.showClientSelector = false;
        if (client.jenis) {
            this.jenis = (client.jenis === 'industri' || client.jenis === 'company') ? 'industri' : 'individu';
        } else {
            this.jenis = 'individu';
        }

        if (applyDefaults) {
            this.psikotesTier = this.jenis === 'industri' ? 'industri' : 'non_industri_bakat_minat';
            this.onPsikotesTierChange();
            if (client.pic_name && !this.picName) {
                this.picName = client.pic_name;
            }
            const cName = (client.company_name || client.name || '').toLowerCase();
            const matchedCompany = (this.companiesList || []).find(co => (co.name || '').toLowerCase() === cName);
            if (matchedCompany) {
                this.companyId = String(matchedCompany.id);
                if (!this.picName && matchedCompany.pic_name) {
                    this.picName = matchedCompany.pic_name;
                }
            }
        }

        if (this.lockCategory) {
            this.serviceType = this.lockCategory;
            return;
        }

        if (client.latest_form_raw_type === 'industri') {
            this.jenis = 'industri';
            this.serviceType = 'psikotes';
        } else if (client.latest_form_raw_type === 'non_industri' || client.latest_form_raw_type === 'biography_en') {
            this.serviceType = 'psikotes';
        } else if (['anak', 'dewasa', 'pra_nikah', 'pernikahan'].includes(client.latest_form_raw_type)) {
            this.serviceType = 'konseling';
        } else {
            if (this.jenis === 'industri') {
                this.serviceType = 'psikotes';
            } else if (!this.serviceType) {
                this.serviceType = 'konseling';
            }
        }
    },

    onCompanyModalChange() {
        const matched = (this.companiesList || []).find(c => String(c.id) === String(this.companyId));
        if (matched && matched.pic_name && !this.picName) {
            this.picName = matched.pic_name;
        }
    },

    clearClient() {
        this.selectedClient = null;
        this.clientId = '';
        this.searchQuery = '';
        this.jenis = 'individu';
        this.companyId = '';
        this.picName = '';
        this.requiresOutgoingLetter = false;
        this.isKilat = false;
        this.showClientSelector = true;
        this.counselingJenis = '';
        this.counselingMetode = '';
        this.counselingKlien = '';
        this.counselingKategori = '';
        this.sessionType = '';
        if (this.lockCategory) {
            this.serviceType = this.lockCategory;
        }
    },

    addParticipant() { this.participants.push('') },
    removeParticipant(i) {
        if (this.participants.length > 1) {
            this.participants.splice(i, 1);
        } else {
            this.participants = [''];
        }
    }
}"
@open-booking-modal.window="openModal($event.detail)"
@keydown.escape.window="bookingModalOpen = false"
class="relative z-[100]"
aria-labelledby="modal-booking-title" role="dialog" aria-modal="true" x-cloak>

    {{-- Backdrop Overlay (Full Viewport, Dark & Blur Persis Seperti Modal Detail Formulir) --}}
    <div x-show="bookingModalOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-[#160B29]/70 backdrop-blur-md transition-opacity"></div>

    {{-- Dialog Wrapper --}}
    <div x-show="bookingModalOpen" class="fixed inset-0 z-10 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center p-3 sm:p-6 lg:p-8 text-left">
            <div x-show="bookingModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative glass-modal rounded-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden">

                {{-- Modal Header (Skala Desain & Komponen Persis Seperti Modal Formulir) --}}
                <div class="px-7 py-5 bg-purple-deep/95 text-white flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-bold text-white tracking-tight" id="modal-booking-title"
                            x-text="lockCategory === 'konseling' ? 'Buat Booking Jadwal Konseling' : (lockCategory === 'psikotes' ? 'Buat Booking Jadwal Psikotes' : 'Buat Booking / Jadwal Sesi')">
                            Buat Booking / Jadwal Sesi
                        </h3>
                    </div>

                    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                        {{-- Tombol Tutup (X) Bulat Semi-Transparan Persis Seperti Modal Formulir --}}
                        <button type="button" @click="bookingModalOpen = false" 
                                class="w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition cursor-pointer shrink-0 text-xl font-bold"
                                title="Tutup">
                            &times;
                        </button>
                    </div>
                </div>

                {{-- Modal Body (Scrollable Konten Form - Penataan Field Vertikal Penuh) --}}
                <div class="p-6 sm:p-8 overflow-y-auto space-y-6 text-black bg-white flex-1 custom-scrollbar">

                    {{-- ERROR SUMMARY ALERT --}}
                    @if($errors->any() && (old('from_modal') || session('booking_modal_error')))
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4.5 shadow-xs mb-2">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
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

                    <form action="{{ route('bookings.store') }}" method="POST" id="form-booking-modal" class="space-y-6">
                        @csrf
                        <input type="hidden" name="from_modal" value="1">
                        <input type="hidden" name="kategori" :value="serviceType">
                        <input type="hidden" name="jenis" :value="jenis">
                        <input type="hidden" name="client_id" :value="clientId">

                        {{-- ========================================================= --}}
                        {{-- CARD: FORMULIR TERAKHIR YANG DIISI                        --}}
                        {{-- ========================================================= --}}
                        <template x-if="selectedClient">
                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                        <span>Formulir Terakhir yang Diisi</span>
                                    </h4>
                                </div>
                                <div class="p-5 sm:p-6">
                                    <div class="bg-slate-50 border border-slate-100 rounded-lg p-4 flex items-center justify-between gap-4">
                                        <template x-if="selectedClient.latest_form_type_label">
                                            <div class="flex items-center justify-between gap-4 w-full">
                                                <p class="text-sm font-medium text-black" x-text="selectedClient.latest_form_type_label"></p>
                                                <p class="text-sm font-medium text-black shrink-0" x-text="'Waktu Pengisian: ' + (selectedClient.latest_form_date || '-')"></p>
                                            </div>
                                        </template>
                                        <template x-if="!selectedClient.latest_form_type_label">
                                            <p class="text-sm text-slate-400 italic">Belum ada riwayat pengisian formulir pendaftaran online.</p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- ========================================================= --}}
                        {{-- CARD 1: INFORMASI KLIEN TERDAFTAR (PENATAAN VERTIKAL)      --}}
                        {{-- ========================================================= --}}
                        <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                            <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                    <span>Pilih Klien Terdaftar</span>
                                </h4>
                            </div>

                            <div class="p-5 sm:p-6 space-y-4">
                                {{-- 1. Penataan Informasi Klien (Grid 3x3 untuk 9 Data Pertama & Alamat Memanjang di Bawah) --}}
                                <template x-if="selectedClient">
                                    <div class="space-y-4">
                                        {{-- Grid 3x3: Dari Nama Lengkap hingga ke Pendidikan Terakhir & Agama --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                            {{-- 1. Nama Lengkap --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">Nama Lengkap</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black" x-text="selectedClient.name || '-'"></span>
                                                </div>
                                            </div>

                                            {{-- 2. Nama Perusahaan --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">Nama Perusahaan</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black" x-text="selectedClient.company_name_display || selectedClient.company_name || '-'"></span>
                                                </div>
                                            </div>

                                            {{-- 3. Pekerjaan / Jabatan Saat Ini --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">Pekerjaan / Jabatan Saat Ini</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black" x-text="selectedClient.occupation_display || selectedClient.occupation || '-'"></span>
                                                </div>
                                            </div>

                                            {{-- 4. Jenis Kelamin --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">Jenis Kelamin</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black" x-text="selectedClient.gender_display || selectedClient.gender_label || '-'"></span>
                                                </div>
                                            </div>

                                            {{-- 5. Tempat, Tanggal Lahir --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">Tempat, Tanggal Lahir</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black" x-text="selectedClient.formatted_dob || '-'"></span>
                                                </div>
                                            </div>

                                            {{-- 6. No. Telepon --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">No. Telepon</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black" x-text="selectedClient.phone || '-'"></span>
                                                </div>
                                            </div>

                                            {{-- 7. Alamat Email --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">Alamat Email</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black break-all" x-text="selectedClient.email || '-'"></span>
                                                </div>
                                            </div>

                                            {{-- 8. Pendidikan Terakhir --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">Pendidikan Terakhir</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black" x-text="selectedClient.education_display || selectedClient.education_label || selectedClient.education || '-'"></span>
                                                </div>
                                            </div>

                                            {{-- 9. Agama / Kepercayaan --}}
                                            <div>
                                                <span class="text-xs font-medium text-slate-500 block mb-1">Agama / Kepercayaan</span>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                    <span class="text-sm font-medium text-black" x-text="selectedClient.religion_display || selectedClient.religion || '-'"></span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- 10. Alamat Lengkap (Memanjang di Bawah Grid) --}}
                                        <div class="sm:col-span-2 lg:col-span-3">
                                            <span class="text-xs font-medium text-slate-500 block mb-1">Alamat</span>
                                            <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center">
                                                <span class="text-sm font-medium text-black leading-relaxed" x-text="selectedClient.address_display || selectedClient.address || '-'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- 2. Daftar Klien (Hanya Tampil Jika Belum Ada Klien Terpilih) --}}
                                <div x-show="!selectedClient" class="space-y-3 pt-2">
                                    {{-- Kolom Filter / Pencarian Klien --}}
                                    <div class="relative flex items-center">
                                        <div class="absolute left-3.5 text-[#827299] pointer-events-none">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                        </div>
                                        <input type="text"
                                            x-model="searchQuery"
                                            placeholder="Ketik untuk memfilter nama klien, no. telepon, atau perusahaan..."
                                            class="w-full pl-10 pr-10 py-2.5 border border-[#D9C2F0] rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white @error('client_id') border-red-300 @enderror">
                                        
                                        <div class="absolute right-2.5 flex items-center" x-show="searchQuery">
                                            <button type="button" @click="searchQuery = ''"
                                                class="p-1 text-[#827299] hover:text-red-500 rounded-lg hover:bg-gray-100 transition cursor-pointer" title="Reset filter">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                                    {{-- Tabel Pemilihan Klien Scrollable --}}
                                    <div class="bg-white border border-[#EDE1FA] rounded-lg shadow-xs overflow-hidden">
                                        <div class="max-h-[220px] overflow-y-auto custom-scrollbar overflow-x-auto">
                                            <table class="w-full text-left border-collapse text-xs">
                                                <thead class="sticky top-0 z-10 bg-slate-50 border-b border-[#EDE1FA] text-black select-none font-semibold">
                                                    <tr>
                                                        <th class="py-2.5 px-4">Nama Klien</th>
                                                        <th class="py-2.5 px-3">Kategori</th>
                                                        <th class="py-2.5 px-3">No. Telepon</th>
                                                        <th class="py-2.5 px-4">Email</th>
                                                        <th class="py-2.5 px-3 text-center w-16">Pilih</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-[#EDE1FA]/70">
                                                    <template x-for="c in filteredClients" :key="c.id">
                                                        <tr @click="toggleClient(c)"
                                                            :class="clientId === c.id ? 'bg-purple-50 text-purple-deep' : 'table-row-clickable hover:bg-slate-100 text-black'"
                                                            class="cursor-pointer transition duration-150 h-[44px]">
                                                            <td class="py-2 px-4 font-medium text-black truncate max-w-[190px]">
                                                                <span x-text="c.name"></span>
                                                            </td>
                                                            <td class="py-2 px-3 whitespace-nowrap text-black font-medium">
                                                                <span x-text="(c.jenis === 'industri' || c.jenis === 'company') ? 'Industri' : 'Individu'"></span>
                                                            </td>
                                                            <td class="py-2 px-3 whitespace-nowrap text-black font-medium">
                                                                <span x-text="c.phone || '-'"></span>
                                                            </td>
                                                            <td class="py-2 px-4 whitespace-nowrap text-black font-medium">
                                                                <span class="truncate block max-w-[200px]" x-text="c.email || '-'"></span>
                                                            </td>
                                                            <td class="py-2 px-3 text-center whitespace-nowrap">
                                                                <template x-if="clientId === c.id">
                                                                    <span class="w-6 h-6 mx-auto rounded-full bg-purple-deep text-white flex items-center justify-center shadow-xs">
                                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                                                    </span>
                                                                </template>
                                                                <template x-if="clientId !== c.id">
                                                                    <span class="text-xs text-purple-deep font-semibold hover:underline">Pilih</span>
                                                                </template>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                    <tr x-show="filteredClients.length === 0">
                                                        <td colspan="5" class="py-6 text-center text-xs text-gray-400 font-medium">
                                                            Tidak ada data klien yang cocok dengan kata kunci pencarian.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CONTAINER FORM DETAIL BOOKING (TAMPIL JIKA KLIEN TELAH TERPILIH) --}}
                        <div x-show="selectedClient" class="space-y-6">

                            {{-- ========================================================= --}}
                            {{-- CARD 2: PAKET LAYANAN (PENATAAN VERTIKAL)                 --}}
                            {{-- ========================================================= --}}
                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                        PAKET LAYANAN
                                    </h4>
                                </div>

                                <div class="p-5 sm:p-6 space-y-4">
                                    {{-- Subcard 1: Format / Nama Layanan --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">Layanan</label>
                                        <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="serviceType === 'psikotes' ? 'Psikotes' : 'Konseling'">Konseling</div>
                                    </div>

                                    {{-- KONDISI 1: FORMULASI DROPDOWN BERJENJANG KHUSUS KONSELING --}}
                                    <template x-if="serviceType === 'konseling'">
                                        <div class="space-y-4">
                                            {{-- Dropdown 1: Jenis (Pro / Reguler) --}}
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Jenis <span class="text-red-500">*</span></label>
                                                <select x-model="counselingJenis" :required="serviceType === 'konseling'"
                                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                    <option value="">Pilih Jenis...</option>
                                                    <option value="Pro">Pro</option>
                                                    <option value="Reguler">Reguler</option>
                                                </select>
                                            </div>

                                            {{-- Dropdown 2: Metode (Offline / Online) --}}
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Metode <span class="text-red-500">*</span></label>
                                                <select x-model="counselingMetode" :required="serviceType === 'konseling'" @change="sessionType = (counselingMetode === 'Offline' ? 'tatap_muka' : (counselingMetode === 'Online' ? 'online' : ''))"
                                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                    <option value="">Pilih Metode...</option>
                                                    <option value="Offline">Offline</option>
                                                    <option value="Online">Online</option>
                                                </select>
                                            </div>

                                            {{-- Dropdown 3: Klien (WNI / WNA) --}}
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Klien <span class="text-red-500">*</span></label>
                                                <select x-model="counselingKlien" :required="serviceType === 'konseling'"
                                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                    <option value="">Pilih Klien...</option>
                                                    <option value="WNI">WNI</option>
                                                    <option value="WNA">WNA</option>
                                                </select>
                                            </div>

                                            {{-- Dropdown 4: Kategori (Umum / Kerja Sama) --}}
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Kategori <span class="text-red-500">*</span></label>
                                                <select x-model="counselingKategori" :required="serviceType === 'konseling'"
                                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                    <option value="">Pilih Kategori...</option>
                                                    <option value="Umum">Umum</option>
                                                    <option value="Kerja Sama">Kerja Sama (Civitas UC Aktif: Mahasiswa / Karyawan)</option>
                                                </select>
                                                <p class="text-[11px] font-medium text-slate-500 mt-1">Tarif Kerja Sama konseling berlaku untuk civitas UC aktif dengan pembayaran personal H-1.</p>
                                            </div>

                                            {{-- Field Akhir: Layanan Lengkap yang Dipilih --}}
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Layanan Lengkap yang Dipilih</label>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="fullCounselingTitle"></div>
                                            </div>

                                            {{-- Tarif / Sesi --}}
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Tarif / Sesi</label>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="formatRupiah(counselingPrice)"></div>
                                            </div>

                                            {{-- Overtime --}}
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Overtime</label>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="formatRupiah(counselingOvertime)"></div>
                                            </div>

                                            <input type="hidden" name="counseling_type" :value="isCounselingConfigured ? fullCounselingTitle : ''">
                                            <input type="hidden" name="price" :value="isCounselingConfigured ? counselingPrice : ''">
                                            <input type="hidden" name="overtime_price" :value="isCounselingConfigured ? counselingOvertime : ''">
                                        </div>
                                    </template>

                                    {{-- KONDISI 2: JIKA KATEGORI PSIKOTES --}}
                                    <template x-if="serviceType === 'psikotes'">
                                        <div class="space-y-4">
                                            <div class="bg-slate-50 border border-slate-100 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                                <p class="text-xs font-medium text-slate-600">
                                                    Untuk batch multi-kandidat, multi-paket, dan penugasan 5 peran lengkap, gunakan halaman Buat Booking Psikotes.
                                                </p>
                                                <a href="{{ route('psychotest-bookings.create') }}" class="px-3.5 py-2 rounded-lg bg-purple-deep text-white text-xs font-semibold hover:bg-purple-deep/90 whitespace-nowrap text-center">
                                                    Buka Form Psikotes Batch →
                                                </a>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Klasifikasi Psikotes</label>
                                                <select name="service_tier" x-model="psikotesTier" @change="onPsikotesTierChange()"
                                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                    <option value="industri">Psikotes Industri</option>
                                                    <option value="non_industri_bakat_minat">Psikotes Non-Industri Bakat Minat Biasa</option>
                                                    <option value="non_industri_khusus">Psikotes Non-Industri Khusus</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Paket &amp; Tarif Resmi</label>
                                                <select name="service_package_key" x-model="psikotesPackageKey"
                                                    class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                    <template x-for="(pkg, pKey) in psikotesPackagesForTier" :key="pKey">
                                                        <option :value="pKey" x-text="pkg.label"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="jenis === 'industri' || jenis === 'company'">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Perusahaan</label>
                                                    <select name="company_id" x-model="companyId" @change="onCompanyModalChange()" class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] bg-white">
                                                        <option value="">-- Pilih Perusahaan --</option>
                                                        @foreach(($companies ?? collect()) as $company)
                                                            <option value="{{ $company->id }}">{{ $company->name }} ({{ $company->category === 'kerja_sama' ? 'Kerja Sama' : 'Umum' }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Nama PIC Perusahaan</label>
                                                    <input type="text" name="pic_name" x-model="picName" placeholder="Nama kontak PIC..." class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] bg-white">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Skema Penagihan</label>
                                                    <select name="billing_scheme" class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] bg-white">
                                                        <option value="v">Reguler / Invoice (v)</option>
                                                        <option value="I">Rekap Internal UCS (I)</option>
                                                        <option value="R">Rekap Perusahaan (R)</option>
                                                        <option value="-">Gratis / Deal Khusus (-)</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-500 mb-1">Pengiriman Hasil</label>
                                                    <select name="delivery_mode" class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] bg-white">
                                                        <option value="softcopy">Softcopy</option>
                                                        <option value="hardcopy">Hardcopy</option>
                                                        <option value="both">Softcopy &amp; Hardcopy</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <label class="bg-slate-50 border border-slate-100 rounded-lg p-3.5 flex items-center gap-2.5 cursor-pointer">
                                                    <input type="checkbox" name="requires_outgoing_letter" value="1" x-model="requiresOutgoingLetter" class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                                    <span class="text-sm font-medium text-black">Perlu Surat Keluar?</span>
                                                </label>
                                                <label class="bg-slate-50 border border-slate-100 rounded-lg p-3.5 flex items-center gap-2.5 cursor-pointer">
                                                    <input type="checkbox" name="is_kilat" value="1" x-model="isKilat" class="w-4 h-4 rounded border-slate-300 text-purple-deep">
                                                    <span class="text-sm font-medium text-black">Prioritas KILAT (Maks 1/Psikolog/Hari)</span>
                                                </label>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Tarif Dasar / Peserta (Sebelum PPN &amp; PPh)</label>
                                                <div class="px-3.5 py-2.5 bg-slate-50 rounded-lg border border-slate-100 min-h-[42px] flex items-center text-sm font-medium text-black" x-text="formatRupiah(psikotesUnitPrice)"></div>
                                            </div>

                                            <input type="hidden" name="counseling_type" :value="psikotesSelectedPackage?.label || ''">
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- ========================================================= --}}
                            {{-- CARD 3: PENUGASAN PELAKSANA (PENATAAN VERTIKAL)           --}}
                            {{-- ========================================================= --}}
                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                        <span x-text="serviceType === 'konseling' ? 'Penugasan Psikolog Bertugas' : 'Penugasan Tim Penguji & Skoring'"></span>
                                    </h4>
                                </div>

                                <div class="p-5 sm:p-6 space-y-4">
                                    {{-- Penugasan Psikolog (Konseling & Psikotes Non-Khusus) --}}
                                    <div x-show="serviceType === 'konseling' || (serviceType === 'psikotes' && psikotesTier !== 'non_industri_khusus')" class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">
                                                Pilih Psikolog Bertugas
                                            </label>
                                            <select name="counselor_id" :disabled="serviceType === 'psikotes' && psikotesTier === 'non_industri_khusus'"
                                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                <option value="">-- Pilih Psikolog --</option>
                                                @foreach($counselors as $counselor)
                                                <option value="{{ $counselor->id }}" {{ old('counselor_id') == $counselor->id ? 'selected' : '' }}>
                                                    {{ $counselor->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Penugasan Psikotes (Vertikal Penuh) --}}
                                    <div x-show="serviceType === 'psikotes'" class="space-y-4">
                                        <div x-show="psikotesTier !== 'non_industri_khusus'">
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Tester (Staff)</label>
                                            <select name="staff_penguji_id" :disabled="psikotesTier === 'non_industri_khusus'"
                                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                <option value="">-- Pilih Tester --</option>
                                                @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}" {{ old('staff_penguji_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Skorer Manual (Staff)</label>
                                            <select name="staff_koreksi_id"
                                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                <option value="">-- Pilih Skorer Manual --</option>
                                                @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}" {{ old('staff_koreksi_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Skorer Software (Staff)</label>
                                            <select name="staff_koreksi_software_id"
                                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                <option value="">-- Pilih Skorer Software --</option>
                                                @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}" {{ old('staff_koreksi_software_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-slate-500 mb-1">Editor Laporan (Staff)</label>
                                            <select name="staff_pelapor_id"
                                                class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                                <option value="">-- Pilih Editor --</option>
                                                @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}" {{ old('staff_pelapor_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ========================================================= --}}
                            {{-- CARD 4: JADWAL, WAKTU & LOKASI SESI (PENATAAN VERTIKAL)   --}}
                            {{-- ========================================================= --}}
                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                        <span>Jadwal, Waktu &amp; Lokasi Sesi</span>
                                    </h4>
                                </div>

                                <div class="p-5 sm:p-6 space-y-4">
                                    {{-- Field 1: Tanggal Permintaan Booking --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            Tanggal Permintaan Booking
                                        </label>
                                        <input type="date" name="tanggal_booking_dibuat" :min="today" value="{{ old('tanggal_booking_dibuat') }}"
                                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                    </div>

                                    {{-- Field 2: Tanggal Sesi Dijadwalkan --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            Tanggal Sesi Dijadwalkan
                                        </label>
                                        <input type="date" name="tanggal_dijadwalkan" :min="today" value="{{ old('tanggal_dijadwalkan') }}"
                                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                    </div>

                                    {{-- Field 3: Jam Mulai Sesi --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            Jam Mulai Sesi
                                        </label>
                                        <input type="time" name="start_time" value="{{ old('start_time') }}"
                                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                    </div>

                                    {{-- Field 4: Jam Selesai Sesi --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            Jam Selesai Sesi
                                        </label>
                                        <input type="time" name="end_time" value="{{ old('end_time') }}"
                                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                    </div>

                                    {{-- Field 5: Metode Pertemuan / Sesi --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            Metode Pertemuan / Sesi
                                        </label>
                                        <select name="session_type" x-model="sessionType"
                                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Metode Pertemuan --</option>
                                            <option value="tatap_muka">Tatap Muka (Offline di Ruang PSC)</option>
                                            <option value="online">Online (Google Meet / Zoom)</option>
                                            <option value="whatsapp">Konsultasi WhatsApp</option>
                                        </select>
                                    </div>

                                    {{-- Field 6: Ruangan / Lokasi / Link Meeting --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            Ruangan / Lokasi / Link Meeting
                                        </label>
                                        <input type="text" name="location" value="{{ old('location') }}" 
                                            :placeholder="sessionType === 'online' ? 'https://meet.google.com/xxx-xxxx-xxx' : 'Contoh: Ruang Konseling A / Lab Psikodiagnostik'"
                                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                    </div>
                                </div>
                            </div>

                            {{-- ========================================================= --}}
                            {{-- CARD 5: STATUS & CATATAN KHUSUS (PENATAAN VERTIKAL)       --}}
                            {{-- ========================================================= --}}
                            <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA]">
                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                        <span>Status Layanan &amp; Catatan Sesi</span>
                                    </h4>
                                </div>

                                <div class="p-5 sm:p-6 space-y-4">
                                    {{-- Field 1: Status Sesi Booking --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            Status Sesi Booking
                                        </label>
                                        <select name="status"
                                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black min-h-[42px] focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <template x-if="serviceType === 'konseling'">
                                                <optgroup label="Status Konseling">
                                                    <option value="data_masuk">Data Masuk</option>
                                                    <option value="waiting_list">Waiting List (Cari Konselor/Jadwal)</option>
                                                    <option value="menunggu_konfirmasi">Sudah Dijadwalkan (Belum Konfirmasi)</option>
                                                    <option value="terjadwal_gocal">Sudah Dijadwalkan &amp; Konfirmasi (GCal)</option>
                                                </optgroup>
                                            </template>
                                            <template x-if="serviceType !== 'konseling'">
                                                <optgroup label="Status Psikotes">
                                                    <option value="baru">Berjalan (Terjadwal)</option>
                                                    <option value="selesai">Selesai</option>
                                                    <option value="batal">Dibatalkan</option>
                                                </optgroup>
                                            </template>
                                        </select>
                                        <p class="text-xs text-slate-500 mt-1">Status akan menentukan alur kerja konselor dan penyiapan berkas.</p>
                                    </div>

                                    {{-- Field 2: Catatan Khusus Sesi --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">
                                            Catatan Khusus Sesi
                                        </label>
                                        <textarea name="notes" rows="3" placeholder="Informasi kebutuhan khusus klien, paket yang diambil, atau instruksi ruangan..."
                                            class="w-full px-3.5 py-2.5 border border-[#D9C2F0] rounded-lg text-sm font-medium text-black focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- ========================================================= --}}
                            {{-- CARD 6: DAFTAR PESERTA ASESMEN (KHUSUS INDUSTRI)          --}}
                            {{-- ========================================================= --}}
                            <div x-show="jenis === 'industri' || jenis === 'company'" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs overflow-hidden">
                                <div class="px-5 sm:px-6 py-4 bg-purple-deep border-b border-[#EDE1FA] flex items-center justify-between">
                                    <h4 class="font-bold text-white text-sm sm:text-base uppercase tracking-wider">
                                        <span>Daftar Karyawan / Peserta Asesmen</span>
                                    </h4>
                                    <button type="button" @click="addParticipant()" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 border border-white/25 text-white text-xs font-semibold transition cursor-pointer">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span>Tambah Peserta</span>
                                    </button>
                                </div>

                                <div class="p-5 sm:p-6 space-y-3">
                                    <template x-for="(p, i) in participants" :key="i">
                                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 sm:p-4 flex items-center gap-3">
                                            <span class="text-sm font-medium text-slate-500 w-6" x-text="(i + 1) + '.'"></span>
                                            <input type="text" :name="'participants[' + i + ']'" x-model="participants[i]"
                                                :disabled="jenis !== 'industri' && jenis !== 'company'"
                                                placeholder="Nama lengkap karyawan / kandidat..."
                                                class="flex-1 px-4 py-2 border border-[#D9C2F0] rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white font-medium text-black">
                                            <button type="button" @click="removeParticipant(i)" x-show="participants.length > 1"
                                                class="p-2 text-rose-500 hover:text-rose-700 transition rounded-lg hover:bg-rose-50 cursor-pointer" title="Hapus">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- ========================================================= --}}
                        {{-- MODAL FOOTER (TOMBOL AKSI KELUAR & SUBMIT)                --}}
                        {{-- ========================================================= --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#EDE1FA]">
                            <button type="button" @click="bookingModalOpen = false" 
                                    class="px-5 py-2.5 rounded-lg bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] text-sm shadow-xs transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2.5 rounded-lg bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold text-sm transition shadow-xs flex items-center gap-2 cursor-pointer">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan Jadwal Booking</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>