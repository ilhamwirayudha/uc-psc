<div x-data="{
    bookingModalOpen: false,
    serviceType: '{{ old('kategori', request('kategori', 'konseling')) }}',
    jenis: '{{ old('jenis', 'individual') }}',
    clientId: '{{ old('client_id', '') }}',
    selectedClient: null,
    searchQuery: '',
    dropdownOpen: false,
    today: '',
    sessionType: '{{ old('session_type', '') }}',
    participants: {{ json_encode(old('participants', [''])) }},
    clientsData: {{ json_encode($clients->keyBy('id')) }},

    init() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        this.today = `${year}-${month}-${day}`;

        @if(isset($errors) && $errors->any() && old('from_modal'))
            this.bookingModalOpen = true;
            if (this.clientId && this.clientsData[this.clientId]) {
                this.selectClient(this.clientsData[this.clientId], false);
            }
        @endif
    },

    openModal(initialClientId = null) {
        this.bookingModalOpen = true;
        if (initialClientId && this.clientsData[initialClientId]) {
            this.selectClient(this.clientsData[initialClientId], false);
        } else if (!this.clientId) {
            this.clearClient();
        }
    },

    get filteredClients() {
        const query = (this.searchQuery || '').toLowerCase().trim();
        const clientsList = Object.values(this.clientsData);
        if (!query) return clientsList;
        return clientsList.filter(c => {
            const name = (c.name || '').toLowerCase();
            const phone = (c.phone || '').toLowerCase();
            const jenis = (c.jenis || '').toLowerCase();
            const email = (c.email || '').toLowerCase();
            return name.includes(query) || phone.includes(query) || jenis.includes(query) || email.includes(query);
        });
    },

    selectClient(client, shouldOpen = false) {
        this.selectedClient = client;
        this.clientId = client.id;
        this.searchQuery = client.name;
        this.dropdownOpen = shouldOpen;
        if (client.jenis) {
            this.jenis = client.jenis;
        }
        if (client.jenis === 'group') {
            this.serviceType = 'konseling';
        } else if (client.jenis === 'company') {
            this.serviceType = 'psikotes';
        } else {
            if (!this.serviceType) {
                this.serviceType = 'konseling';
            }
        }
    },

    clearClient() {
        this.selectedClient = null;
        this.clientId = '';
        this.searchQuery = '';
        this.jenis = 'individual';
        this.dropdownOpen = false;
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
@open-booking-modal.window="openModal($event.detail?.client_id)"
@keydown.escape.window="bookingModalOpen = false"
class="relative z-50"
aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>

    {{-- Background backdrop --}}
    <div x-show="bookingModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
    
    {{-- Modal panel --}}
    <div x-show="bookingModalOpen" x-transition class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div @click.outside="bookingModalOpen = false" class="relative transform overflow-hidden rounded-2xl bg-[#F7F5FB] text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl">
                
                <!-- Header -->
                <div class="bg-purple-deep px-6 py-4 rounded-t-2xl flex items-center justify-between shadow-sm">
                    <h3 class="text-lg font-bold leading-6 text-white" id="modal-title">Buat Booking / Jadwal Sesi</h3>
                    <button type="button" @click="bookingModalOpen = false" class="text-white hover:text-gray-200 transition cursor-pointer">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="px-6 pt-5 pb-6 max-h-[85vh] min-h-[460px] sm:min-h-[500px] overflow-y-auto">
                    
                    {{-- ERROR SUMMARY ALERT --}}
                    @if($errors->any() && session('booking_modal_error'))
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4.5 shadow-xs mb-6">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-red-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-red-800 mb-1">Mohon lengkapi atau perbaiki isian berikut:</h4>
                                <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('bookings.store') }}" method="POST" class="flex flex-col justify-between min-h-[400px] sm:min-h-[440px]">
                        <div class="space-y-6">
                            @csrf
                            <!-- Penanda bahwa form dikirim dari modal, untuk trigger error modal -->
                            <input type="hidden" name="from_modal" value="1">
                            
                            <input type="hidden" name="kategori" :value="serviceType">
                            <input type="hidden" name="jenis" :value="jenis">

                            {{-- CARD 1: PILIH KLIEN TERDAFTAR (SEARCHABLE DROPDOWN) --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                            <div class="pb-3 border-b border-[#EDE1FA]">
                                <h3 class="text-base font-bold text-purple-deep">Pilih Klien Terdaftar</h3>
                            </div>

                            <div class="space-y-4">
                                {{-- Searchable Input & Dropdown Klien --}}
                                <div class="relative" @click.outside="dropdownOpen = false">
                                    <input type="hidden" name="client_id" :value="clientId" required>

                                    <div class="relative flex items-center">
                                        <div class="absolute left-3.5 text-[#827299] pointer-events-none">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                        </div>
                                        <input type="text"
                                            x-model="searchQuery"
                                            @focus="dropdownOpen = true"
                                            @input="dropdownOpen = true; if (!searchQuery) { clientId = ''; selectedClient = null; }"
                                            placeholder="Ketik untuk mencari nama klien, nomor WhatsApp, atau perusahaan..."
                                            class="w-full pl-10 pr-20 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white @error('client_id') border-red-300 @enderror">
                                        
                                        <div class="absolute right-2.5 flex items-center gap-1">
                                            <button type="button" x-show="searchQuery" @click="clearClient()"
                                                class="p-1 text-[#827299] hover:text-red-500 rounded-lg hover:bg-gray-100 transition cursor-pointer" title="Hapus pilihan">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                            <button type="button" @click="dropdownOpen = !dropdownOpen"
                                                class="p-1 text-[#827299] hover:text-purple-deep rounded-lg transition cursor-pointer">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    :class="dropdownOpen ? 'rotate-180' : ''" class="transition-transform duration-200"><polyline points="6 9 12 15 18 9"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                                    {{-- Dropdown Hasil Pencarian --}}
                                    <div x-show="dropdownOpen"
                                        class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-[#EDE1FA] rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-gray-50">
                                        <template x-for="c in filteredClients" :key="c.id">
                                            <div @click="selectClient(c, false)"
                                                :class="clientId === c.id ? 'bg-purple-deep/10 font-bold' : 'hover:bg-[#FAF8FD]'"
                                                class="px-4 py-2.5 text-xs cursor-pointer flex items-center justify-between gap-3 transition">
                                                <div class="space-y-0.5 min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="font-bold text-[#2A2035] truncate" x-text="c.name"></span>
                                                        <span class="text-[11px] font-normal text-[#827299]"
                                                            x-text="'(' + (c.jenis === 'company' ? 'Perusahaan' : (c.jenis === 'group' ? 'Kelompok' : 'Individu')) + ')'"></span>
                                                    </div>
                                                    <p class="text-[#827299] truncate">
                                                        WA: <span class="text-[#5B4A73]" x-text="c.phone || '-'"></span> &bull; 
                                                        Email: <span class="text-[#5B4A73]" x-text="c.email || '-'"></span>
                                                    </p>
                                                </div>
                                                <div x-show="clientId === c.id" class="text-purple-deep flex-shrink-0">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="filteredClients.length === 0" class="p-4 text-center text-xs text-[#827299]">
                                            Tidak ada data klien yang cocok dengan kata kunci pencarian.
                                        </div>
                                    </div>
                                </div>

                                {{-- Detail Box Klien Terpilih --}}
                                <div x-show="selectedClient" class="p-3.5 bg-[#FAF8FD] rounded-xl border border-[#EDE1FA] flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-[#2A2035]" x-text="selectedClient ? selectedClient.name : ''"></p>
                                        <p class="text-[#827299]">
                                            WhatsApp: <span class="font-semibold text-[#5B4A73]" x-text="selectedClient ? selectedClient.phone : ''"></span> &bull; 
                                            Email: <span class="font-semibold text-[#5B4A73]" x-text="selectedClient ? selectedClient.email : ''"></span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-[#6B5B85]"
                                            x-text="selectedClient ? (selectedClient.jenis === 'company' ? 'Perusahaan / Instansi' : (selectedClient.jenis === 'group' ? 'Kelompok / Keluarga' : 'Perorangan (Individu)')) : ''"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- WRAPPER CONTAINER FORM DETAIL BOOKING (HANYA MUNCUL JIKA KLIEN TELAH DIPILIH) --}}
                        <div x-show="selectedClient" class="space-y-6">

                            {{-- CARD 2: PILIHAN LAYANAN SESUAI TIPE KLIEN --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                                <div class="pb-3 border-b border-[#EDE1FA]">
                                    <h3 class="text-base font-bold text-purple-deep"
                                        x-text="jenis === 'company' ? 'Pilihan Layanan Perusahaan' : (jenis === 'group' ? 'Pilihan Layanan Kelompok' : 'Pilihan Layanan Individu')"></h3>
                                </div>

                                {{-- 1. SWITCHER OPSI UNTUK KLIEN INDIVIDU --}}
                                <div x-show="jenis === 'individual'">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        {{-- Opsi: Konseling Individu --}}
                                        <button type="button"
                                            @click="serviceType = 'konseling'"
                                            :class="serviceType === 'konseling' 
                                                ? 'border-purple-deep bg-purple-deep/5 ring-1.5 ring-purple-deep/30 text-purple-deep shadow-xs opacity-100' 
                                                : 'border-[#EDE1FA] bg-[#FAF8FD] text-[#827299] opacity-45 hover:opacity-80 hover:border-[#D9C2F0] hover:bg-white'"
                                            class="flex items-center gap-3.5 px-4 py-3 rounded-xl border text-left cursor-pointer group transition-all">
                                            <div :class="serviceType === 'konseling' ? 'bg-purple-deep text-white shadow-xs' : 'bg-purple-deep/10 text-purple-deep'"
                                                class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-all">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <span class="font-bold text-sm" :class="serviceType === 'konseling' ? 'text-purple-deep' : 'text-[#5B4A73]'">Konseling Individu</span>
                                                <p class="text-xs text-[#827299]">Sesi konseling 1-on-1 bersama psikolog</p>
                                            </div>
                                        </button>

                                        {{-- Opsi: Psikotes Individu --}}
                                        <button type="button"
                                            @click="serviceType = 'psikotes'"
                                            :class="serviceType === 'psikotes' 
                                                ? 'border-purple-deep bg-purple-deep/5 ring-1.5 ring-purple-deep/30 text-purple-deep shadow-xs opacity-100' 
                                                : 'border-[#EDE1FA] bg-[#FAF8FD] text-[#827299] opacity-45 hover:opacity-80 hover:border-[#D9C2F0] hover:bg-white'"
                                            class="flex items-center gap-3.5 px-4 py-3 rounded-xl border text-left cursor-pointer group transition-all">
                                            <div :class="serviceType === 'psikotes' ? 'bg-purple-deep text-white shadow-xs' : 'bg-purple-deep/10 text-purple-deep'"
                                                class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-all">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <span class="font-bold text-sm" :class="serviceType === 'psikotes' ? 'text-purple-deep' : 'text-[#5B4A73]'">Psikotes Individu</span>
                                                <p class="text-xs text-[#827299]">Asesmen psikologi perorangan</p>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                {{-- 2. SWITCHER OPSI UNTUK KLIEN KELOMPOK --}}
                                <div x-show="jenis === 'group'">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        {{-- Opsi: Konseling Kelompok --}}
                                        <button type="button"
                                            @click="serviceType = 'konseling'"
                                            :class="serviceType === 'konseling' 
                                                ? 'border-purple-deep bg-purple-deep/5 ring-1.5 ring-purple-deep/30 text-purple-deep shadow-xs opacity-100' 
                                                : 'border-[#EDE1FA] bg-[#FAF8FD] text-[#827299] opacity-45 hover:opacity-80 hover:border-[#D9C2F0] hover:bg-white'"
                                            class="flex items-center gap-3.5 px-4 py-3 rounded-xl border text-left cursor-pointer group transition-all">
                                            <div :class="serviceType === 'konseling' ? 'bg-purple-deep text-white shadow-xs' : 'bg-purple-deep/10 text-purple-deep'"
                                                class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-all">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <span class="font-bold text-sm" :class="serviceType === 'konseling' ? 'text-purple-deep' : 'text-[#5B4A73]'">Konseling Kelompok</span>
                                                <p class="text-xs text-[#827299]">Konseling bersama pasangan/keluarga</p>
                                            </div>
                                        </button>

                                        {{-- Opsi: Psikotes Kelompok --}}
                                        <button type="button"
                                            @click="serviceType = 'psikotes'"
                                            :class="serviceType === 'psikotes' 
                                                ? 'border-purple-deep bg-purple-deep/5 ring-1.5 ring-purple-deep/30 text-purple-deep shadow-xs opacity-100' 
                                                : 'border-[#EDE1FA] bg-[#FAF8FD] text-[#827299] opacity-45 hover:opacity-80 hover:border-[#D9C2F0] hover:bg-white'"
                                            class="flex items-center gap-3.5 px-4 py-3 rounded-xl border text-left cursor-pointer group transition-all">
                                            <div :class="serviceType === 'psikotes' ? 'bg-purple-deep text-white shadow-xs' : 'bg-purple-deep/10 text-purple-deep'"
                                                class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-all">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <span class="font-bold text-sm" :class="serviceType === 'psikotes' ? 'text-purple-deep' : 'text-[#5B4A73]'">Psikotes Kelompok</span>
                                                <p class="text-xs text-[#827299]">Asesmen psikologi untuk keluarga</p>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                {{-- 3. SWITCHER OPSI UNTUK KLIEN PERUSAHAAN --}}
                                <div x-show="jenis === 'company'">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        {{-- Opsi: Konseling Kelompok / Korporat --}}
                                        <button type="button"
                                            @click="serviceType = 'konseling'"
                                            :class="serviceType === 'konseling' 
                                                ? 'border-purple-deep bg-purple-deep/5 ring-1.5 ring-purple-deep/30 text-purple-deep shadow-xs opacity-100' 
                                                : 'border-[#EDE1FA] bg-[#FAF8FD] text-[#827299] opacity-45 hover:opacity-80 hover:border-[#D9C2F0] hover:bg-white'"
                                            class="flex items-center gap-3.5 px-4 py-3 rounded-xl border text-left cursor-pointer group transition-all">
                                            <div :class="serviceType === 'konseling' ? 'bg-purple-deep text-white shadow-xs' : 'bg-purple-deep/10 text-purple-deep'"
                                                class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-all">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <span class="font-bold text-sm" :class="serviceType === 'konseling' ? 'text-purple-deep' : 'text-[#5B4A73]'">Konseling Kelompok / Karyawan</span>
                                                <p class="text-xs text-[#827299]">Program konsultasi tim instansi</p>
                                            </div>
                                        </button>

                                        {{-- Opsi: Psikotes Massal Perusahaan --}}
                                        <button type="button"
                                            @click="serviceType = 'psikotes'"
                                            :class="serviceType === 'psikotes' 
                                                ? 'border-purple-deep bg-purple-deep/5 ring-1.5 ring-purple-deep/30 text-purple-deep shadow-xs opacity-100' 
                                                : 'border-[#EDE1FA] bg-[#FAF8FD] text-[#827299] opacity-45 hover:opacity-80 hover:border-[#D9C2F0] hover:bg-white'"
                                            class="flex items-center gap-3.5 px-4 py-3 rounded-xl border text-left cursor-pointer group transition-all">
                                            <div :class="serviceType === 'psikotes' ? 'bg-purple-deep text-white shadow-xs' : 'bg-purple-deep/10 text-purple-deep'"
                                                class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-all">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <span class="font-bold text-sm" :class="serviceType === 'psikotes' ? 'text-purple-deep' : 'text-[#5B4A73]'">Psikotes Massal / Perusahaan</span>
                                                <p class="text-xs text-[#827299]">Asesmen rekrutmen & seleksi</p>
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                {{-- DROPDOWN PAKET LAYANAN SPESIFIK --}}
                                <div class="pt-2">
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Pilih Paket Layanan Spesifik <span class="text-red-500">*</span></label>
                                    
                                    {{-- Paket Konseling Individu --}}
                                    <div x-show="serviceType === 'konseling' && jenis === 'individual'">
                                        <select name="counseling_type" :disabled="serviceType !== 'konseling' || jenis !== 'individual'"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Paket Layanan Spesifik --</option>
                                            <option value="Konseling Individu (Dewasa / Umum)">Konseling Individu (Dewasa / Umum)</option>
                                            <option value="Konseling Mahasiswa / Remaja">Konseling Mahasiswa / Remaja</option>
                                            <option value="Konseling Anak & Tumbuh Kembang">Konseling Anak & Tumbuh Kembang</option>
                                            <option value="Konseling Karir & Masalah Pekerjaan">Konseling Karir & Masalah Pekerjaan</option>
                                        </select>
                                    </div>

                                    {{-- Paket Konseling Kelompok / Keluarga / Perusahaan --}}
                                    <div x-show="serviceType === 'konseling' && (jenis === 'group' || jenis === 'company')">
                                        <select name="counseling_type" :disabled="serviceType !== 'konseling' || (jenis !== 'group' && jenis !== 'company')"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Paket Layanan Spesifik --</option>
                                            <option value="Konseling Pasangan & Pra-Nikah">Konseling Pasangan & Pra-Nikah</option>
                                            <option value="Konseling Keluarga & Hubungan Orang Tua-Anak">Konseling Keluarga & Hubungan Orang Tua-Anak</option>
                                            <option value="Konseling Kelompok Terarah (Support Group)">Konseling Kelompok Terarah (Support Group)</option>
                                            <option value="Konseling Tim & Kesehatan Mental Karyawan">Konseling Tim & Kesehatan Mental Karyawan</option>
                                        </select>
                                    </div>

                                    {{-- Paket Psikotes Individu --}}
                                    <div x-show="serviceType === 'psikotes' && jenis === 'individual'">
                                        <select name="counseling_type" :disabled="serviceType !== 'psikotes' || jenis !== 'individual'"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Paket Layanan Spesifik --</option>
                                            <option value="Tes IQ & Intelegensi (Kognitif)">Tes IQ & Intelegensi (Kognitif)</option>
                                            <option value="Tes Kepribadian (Personality Profile)">Tes Kepribadian (Personality Profile)</option>
                                            <option value="Tes Minat & Bakat (Career Guidance)">Tes Minat & Bakat (Career Guidance)</option>
                                            <option value="Tes Kesiapan Masuk Sekolah (TK / SD)">Tes Kesiapan Masuk Sekolah (TK / SD)</option>
                                            <option value="Pemeriksaan Psikologis Klinis Komprehensif">Pemeriksaan Psikologis Klinis Komprehensif</option>
                                        </select>
                                    </div>

                                    {{-- Paket Psikotes Kelompok / Perusahaan --}}
                                    <div x-show="serviceType === 'psikotes' && (jenis === 'group' || jenis === 'company')">
                                        <select name="counseling_type" :disabled="serviceType !== 'psikotes' || (jenis !== 'group' && jenis !== 'company')"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Paket Layanan Spesifik --</option>
                                            <option value="Asesmen Rekrutmen & Seleksi Karyawan">Asesmen Rekrutmen & Seleksi Karyawan</option>
                                            <option value="Asesmen Promosi Jabatan & Leadership">Asesmen Promosi Jabatan & Leadership</option>
                                            <option value="Pemetaan Potensi & Kompetensi SDM">Pemetaan Potensi & Kompetensi SDM</option>
                                            <option value="Asesmen Psikotes Kelompok / Komunitas">Asesmen Psikotes Kelompok / Komunitas</option>
                                            <option value="Asesmen Massal Calon Karyawan (Batch)">Asesmen Massal Calon Karyawan (Batch)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 3: PENUGASAN PELAKSANA (KONSELOR / STAFF) --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                                <div class="pb-3 border-b border-[#EDE1FA]">
                                    <h3 class="text-base font-bold text-purple-deep">Penugasan Konselor / Staff Pelaksana</h3>
                                </div>

                                {{-- Konseling: Konselor --}}
                                <div x-show="serviceType === 'konseling'" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Pilih Psikolog / Konselor Bertugas <span class="text-red-500">*</span></label>
                                        <select name="counselor_id" :required="selectedClient && serviceType === 'konseling'"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Konselor --</option>
                                            @foreach($counselors as $counselor)
                                            <option value="{{ $counselor->id }}" {{ old('counselor_id') == $counselor->id ? 'selected' : '' }}>
                                                {{ $counselor->name }} ({{ $counselor->specialization ?? 'Psikolog Klinis' }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Psikotes: Staff Penguji, Koreksi, Pelapor --}}
                                <div x-show="serviceType === 'psikotes'" class="grid sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Staff Penguji / Tester</label>
                                        <select name="staff_penguji_id"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Staff --</option>
                                            @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}" {{ old('staff_penguji_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Staff Koreksi / Skoring</label>
                                        <select name="staff_koreksi_id"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Staff --</option>
                                            @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}" {{ old('staff_koreksi_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Staff Pelapor / Pembuat Hasil</label>
                                        <select name="staff_pelapor_id"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Staff --</option>
                                            @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}" {{ old('staff_pelapor_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 4: JADWAL, WAKTU & METODE SESI --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-5">
                                <div class="pb-3 border-b border-[#EDE1FA]">
                                    <h3 class="text-base font-bold text-purple-deep">Jadwal, Waktu & Lokasi Sesi</h3>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    {{-- Tanggal Permintaan Booking --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Tanggal Permintaan Booking <span class="text-red-500">*</span></label>
                                        <input type="date" name="tanggal_booking_dibuat" :min="today" value="{{ old('tanggal_booking_dibuat') }}" :required="selectedClient != null"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Tanggal Sesi Dijadwalkan --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Tanggal Sesi Dijadwalkan <span class="text-red-500">*</span></label>
                                        <input type="date" name="tanggal_dijadwalkan" :min="today" value="{{ old('tanggal_dijadwalkan') }}" :required="selectedClient != null"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Jam Mulai & Selesai --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Jam Mulai Sesi</label>
                                        <input type="time" name="start_time" value="{{ old('start_time') }}"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Jam Selesai Sesi</label>
                                        <input type="time" name="end_time" value="{{ old('end_time') }}"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>

                                    {{-- Metode Sesi --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Metode Pertemuan / Sesi</label>
                                        <select name="session_type" x-model="sessionType"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Metode Pertemuan --</option>
                                            <option value="tatap_muka">Tatap Muka (Offline di Ruang PSC)</option>
                                            <option value="online">Online (Google Meet / Zoom)</option>
                                            <option value="whatsapp">Konsultasi WhatsApp</option>
                                        </select>
                                    </div>

                                    {{-- Ruangan / Lokasi --}}
                                    <div>
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Ruangan / Lokasi / Link Meeting</label>
                                        <input type="text" name="location" value="{{ old('location') }}" 
                                            :placeholder="sessionType === 'online' ? 'https://meet.google.com/xxx-xxxx-xxx' : 'Contoh: Ruang Konseling A / Lab Psikodiagnostik'"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 5: DAFTAR ANGGOTA / PESERTA (GROUP / COMPANY) --}}
                            <div x-show="jenis === 'company' || jenis === 'group'" class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA] flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-bold text-purple-deep"
                                            x-text="jenis === 'company' ? 'Daftar Karyawan / Peserta Asesmen' : 'Daftar Anggota / Peserta Kelompok'"></h3>
                                        <p class="text-xs text-[#827299]">Masukkan nama masing-masing peserta atau kandidat</p>
                                    </div>
                                    <button type="button" @click="addParticipant()" class="text-xs font-bold text-purple-deep hover:underline cursor-pointer flex items-center gap-1">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <span>Tambah Peserta</span>
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(p, i) in participants" :key="i">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-[#6B5B85] w-6" x-text="(i + 1) + '.'"></span>
                                            <input type="text" :name="'participants[' + i + ']'" x-model="participants[i]"
                                                :disabled="jenis !== 'company' && jenis !== 'group'"
                                                :placeholder="jenis === 'company' ? 'Nama lengkap karyawan / kandidat...' : 'Nama lengkap anggota keluarga / pasangan...'"
                                                class="flex-1 px-4 py-2 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">
                                            <button type="button" @click="removeParticipant(i)" x-show="participants.length > 1"
                                                class="p-2 text-red-400 hover:text-red-600 transition rounded-lg hover:bg-red-50 cursor-pointer">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- CARD 6: STATUS & CATATAN BOOKING --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] p-6 space-y-4">
                                <div class="pb-3 border-b border-[#EDE1FA]">
                                    <h3 class="text-base font-bold text-purple-deep">Status & Catatan Sesi Booking</h3>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Status Booking <span class="text-red-500">*</span></label>
                                        <select name="status" :required="selectedClient != null"
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white">
                                            <option value="">-- Pilih Status Booking --</option>
                                            <option value="baru" {{ old('status') === 'baru' ? 'selected' : '' }}>Baru (Terjadwal)</option>
                                            <option value="lanjutan" {{ old('status') === 'lanjutan' ? 'selected' : '' }}>Lanjutan (Follow-up)</option>
                                            <option value="selesai" {{ old('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">Catatan Khusus Sesi <span class="text-[#827299] font-normal">(Opsional)</span></label>
                                        <textarea name="notes" rows="3" placeholder="Informasi kebutuhan khusus klien, paket yang diambil, atau instruksi ruangan..."
                                            class="w-full px-4 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex items-center justify-end gap-3 pt-6">
                            <button type="button" @click="bookingModalOpen = false" class="px-6 py-2.5 text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] rounded-xl hover:bg-[#F7F5FB] transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-6 py-2.5 bg-purple-deep text-white text-sm font-semibold rounded-xl hover:bg-purple-deep/90 transition shadow-sm cursor-pointer flex items-center gap-2">
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
