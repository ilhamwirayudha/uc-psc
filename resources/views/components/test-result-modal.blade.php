<div x-data="{
    testResultModalOpen: false,
    confirmDiscardModalOpen: false,
    clientId: @js(old('client_id', request('client_id', ''))),
    bookingId: @js(old('booking_id', request('booking_id', ''))),
    method: @js(old('method', 'offline')),
    clientSearch: '',
    clientDropdownOpen: false,
    testName: @js(old('test_name', '')),
    testedAt: @js(old('tested_at', '')) || new Date().toISOString().split('T')[0],
    resultSummary: @js(old('result_summary', '')),
    fileName: '',
    clientsData: {{ json_encode($clients->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'jenis' => $c->jenis])) }},
    bookingsData: {{ json_encode(($psikotesBookings ?? collect())->map(fn($b) => ['id' => $b->id, 'client_id' => $b->client_id, 'tanggal' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('Y-m-d') : null, 'tanggal_label' => $b->tanggal_dijadwalkan ? $b->tanggal_dijadwalkan->format('d M Y') : 'Tanpa tanggal'])) }},

    init() {
        @if($errors->any() && old('from_test_result_modal'))
            this.testResultModalOpen = true;
        @endif

        if (this.clientId) {
            const found = this.clientsData.find(c => c.id == this.clientId);
            if (found) {
                this.clientSearch = found.name;
            }
        }
    },

    openModal(detail = null) {
        this.testResultModalOpen = true;
        if (typeof detail === 'object' && detail !== null) {
            if (detail.client_id) {
                this.clientId = detail.client_id;
                const found = this.clientsData.find(c => c.id == detail.client_id);
                if (found) this.clientSearch = found.name;
            }
            if (detail.booking_id) {
                this.bookingId = detail.booking_id;
            }
            if (detail.test_name) {
                this.testName = detail.test_name;
            }
            if (detail.tested_at) {
                this.testedAt = detail.tested_at;
            }
            if (detail.method) {
                this.method = detail.method;
            }
        } else if (detail) {
            this.clientId = detail;
            const found = this.clientsData.find(c => c.id == detail);
            if (found) {
                this.clientSearch = found.name;
            }
        } else if (!this.clientId) {
            this.clientSearch = '';
        }
        this.clientDropdownOpen = false;
    },

    get filteredClients() {
        const query = (this.clientSearch || '').toLowerCase().trim();
        if (!query) return this.clientsData;
        return this.clientsData.filter(c => {
            const name = (c.name || '').toLowerCase();
            const phone = (c.phone || '').toLowerCase();
            const jenis = (c.jenis || '').toLowerCase();
            return name.includes(query) || phone.includes(query) || jenis.includes(query);
        });
    },

    get clientBookings() {
        if (!this.clientId) return [];
        return this.bookingsData.filter(b => b.client_id == this.clientId);
    },

    selectClient(c) {
        this.clientId = c.id;
        this.clientSearch = c.name;
        this.clientDropdownOpen = false;
        
        // Auto-select latest booking jika ada
        const availableBookings = this.bookingsData.filter(b => b.client_id == c.id);
        if (availableBookings.length > 0 && !this.bookingId) {
            this.bookingId = availableBookings[0].id;
            if (availableBookings[0].tanggal && !this.testedAt) {
                this.testedAt = availableBookings[0].tanggal;
            }
        }
    },

    clearClient() {
        this.clientId = '';
        this.clientSearch = '';
        this.bookingId = '';
        this.clientDropdownOpen = false;
    },

    handleFileChange(e) {
        if (e.target.files && e.target.files[0]) {
            this.fileName = e.target.files[0].name;
        } else {
            this.fileName = '';
        }
    },

    isDirty() {
        if (this.clientId) return true;
        if (this.bookingId) return true;
        if (this.testName && this.testName.trim().length > 0) return true;
        if (this.resultSummary && this.resultSummary.trim().length > 0) return true;
        if (this.fileName) return true;
        return false;
    },

    requestClose() {
        if (this.isDirty()) {
            this.confirmDiscardModalOpen = true;
        } else {
            this.forceClose();
        }
    },

    cancelDiscard() {
        this.confirmDiscardModalOpen = false;
    },

    executeDiscard() {
        this.confirmDiscardModalOpen = false;
        this.forceClose();
    },

    forceClose() {
        this.testResultModalOpen = false;
        this.resetForm();
    },

    resetForm() {
        this.clientId = '';
        this.bookingId = '';
        this.clientSearch = '';
        this.method = 'offline';
        this.clientDropdownOpen = false;
        this.testName = '';
        this.testedAt = new Date().toISOString().split('T')[0];
        this.resultSummary = '';
        this.fileName = '';
        if (this.$refs.fileInput) {
            this.$refs.fileInput.value = '';
        }
    }
}"
@open-test-result-modal.window="openModal($event.detail)"
@keydown.escape.window="if (confirmDiscardModalOpen) { cancelDiscard(); } else if (testResultModalOpen) { requestClose(); }"
class="relative z-50"
aria-labelledby="test-result-modal-title" role="dialog" aria-modal="true" x-cloak>

    {{-- Background backdrop --}}
    <div x-show="testResultModalOpen" x-transition.opacity class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"></div>
    
    {{-- Modal panel --}}
    <div x-show="testResultModalOpen" x-transition class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0" @click.self="requestClose()">
            <div class="relative transform overflow-hidden rounded-2xl bg-[#F7F5FB] text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-2xl">
                
                <!-- Header -->
                <div class="bg-purple-deep px-6 py-4 rounded-t-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold leading-6 text-white" id="test-result-modal-title">Kasus Asesmen & Hasil Psikotes</h3>
                            <p class="text-[11px] text-white/70">Registrasi pelaksanaan dan berkas dokumen hasil tes klien</p>
                        </div>
                    </div>
                    <button type="button" @click="requestClose()" class="text-white hover:text-gray-200 transition cursor-pointer" title="Tutup formulir">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="px-6 pt-5 pb-6 max-h-[85vh] overflow-y-auto">
                    
                    {{-- Error Summary Alert --}}
                    @if($errors->any() && old('from_test_result_modal'))
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 shadow-xs mb-5">
                        <div class="flex items-start gap-3">
                            <div class="text-red-500 mt-0.5 shrink-0">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-red-800 mb-1">Mohon periksa data berikut:</h4>
                                <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('test-results.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="from_test_result_modal" value="1">

                        {{-- FIELD 1: PILIH KLIEN (SEARCHABLE DROPDOWN) --}}
                        <div>
                            <label for="modal_client_id" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                Klien <span class="text-red-500">*</span>
                            </label>
                            <input type="hidden" id="modal_client_id" name="client_id" :value="clientId" required>

                            <div class="relative" @click.outside="clientDropdownOpen = false">
                                <div class="relative flex items-center">
                                    <div class="absolute left-3.5 text-[#827299] pointer-events-none">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </div>
                                    <input type="text"
                                        x-model="clientSearch"
                                        @focus="clientDropdownOpen = true"
                                        @input="clientDropdownOpen = true; if (!clientSearch) { clientId = ''; }"
                                        placeholder="Ketik nama klien atau pilih dari daftar..."
                                        class="w-full pl-10 pr-20 py-2.5 border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep bg-white text-[#2A2035] @error('client_id') border-red-300 @enderror">
                                    
                                    <div class="absolute right-2.5 flex items-center gap-1">
                                        <button type="button" x-show="clientSearch" @click="clearClient()"
                                            class="p-1 text-[#827299] hover:text-red-500 rounded-lg hover:bg-gray-100 transition cursor-pointer" title="Hapus pilihan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                        <button type="button" @click="clientDropdownOpen = !clientDropdownOpen"
                                            class="p-1 text-[#827299] hover:text-purple-deep rounded-lg transition cursor-pointer">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                :class="clientDropdownOpen ? 'rotate-180' : ''" class="transition-transform duration-200"><polyline points="6 9 12 15 18 9"/></svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Dropdown Hasil Pencarian --}}
                                <div x-show="clientDropdownOpen"
                                    class="absolute z-50 left-0 right-0 mt-1 bg-white border border-[#EDE1FA] rounded-xl shadow-xl max-h-52 overflow-y-auto divide-y divide-gray-50">
                                    <template x-for="c in filteredClients" :key="c.id">
                                        <div @click="selectClient(c)"
                                            class="px-4 py-2.5 text-xs sm:text-sm cursor-pointer flex items-center justify-between hover:bg-[#F7F5FB] transition"
                                            :class="{ 'bg-purple-deep/5 text-purple-deep font-semibold': clientId == c.id, 'text-[#2A2035]': clientId != c.id }">
                                            <div class="flex items-center gap-2">
                                                <span x-text="c.name"></span>
                                                <template x-if="c.jenis">
                                                    <span class="text-[10px] px-2 py-0.5 rounded-full capitalize"
                                                        :class="{
                                                            'bg-purple-100 text-purple-700': c.jenis === 'individual',
                                                            'bg-blue-100 text-blue-700': c.jenis === 'group',
                                                            'bg-emerald-100 text-emerald-700': c.jenis === 'company'
                                                        }"
                                                        x-text="c.jenis"></span>
                                                </template>
                                            </div>
                                            <span class="text-xs text-[#827299]" x-text="c.phone || ''"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredClients.length === 0" class="px-4 py-3 text-xs text-[#827299] text-center">
                                        Klien tidak ditemukan
                                    </div>
                                </div>
                            </div>
                            @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- FIELD 2: KAITKAN DENGAN BOOKING PSIKOTES (JIKA ADA) --}}
                        <div x-show="clientId && clientBookings.length > 0">
                            <label for="modal_booking_id" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                Tiket Booking Terkait (Opsional)
                            </label>
                            <select id="modal_booking_id" name="booking_id" x-model="bookingId"
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035]">
                                <option value="">-- Buat Kasus Baru (Tanpa Tiket Booking) --</option>
                                <template x-for="b in clientBookings" :key="b.id">
                                    <option :value="b.id" x-text="'Booking #' + b.id + ' — Tanggal ' + b.tanggal_label"></option>
                                </template>
                            </select>
                            <span class="text-[10px] text-[#827299] mt-0.5 block">Mengaitkan kasus dengan booking akan menyelaraskan penugasan tim staff.</span>
                        </div>

                        {{-- FIELD 3: NAMA TES & METODE PELAKSANAAN --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label for="modal_test_name" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                    Nama Tes <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="modal_test_name" name="test_name" x-model="testName" required
                                    placeholder="contoh: MMPI-2, WISC-IV, DISC, Minat Bakat"
                                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition @error('test_name') border-red-300 @enderror">
                                @error('test_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                    Metode <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-1.5 p-1 bg-white border border-[#D9C2F0] rounded-xl">
                                    <label class="text-center py-1.5 rounded-lg text-xs font-bold cursor-pointer transition"
                                        :class="method === 'offline' ? 'bg-purple-deep text-white shadow-2xs' : 'text-[#6B5B85] hover:text-purple-deep'">
                                        <input type="radio" name="method" value="offline" x-model="method" class="hidden">
                                        Offline
                                    </label>
                                    <label class="text-center py-1.5 rounded-lg text-xs font-bold cursor-pointer transition"
                                        :class="method === 'online' ? 'bg-purple-deep text-white shadow-2xs' : 'text-[#6B5B85] hover:text-purple-deep'">
                                        <input type="radio" name="method" value="online" x-model="method" class="hidden">
                                        Online
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- FIELD 4: TANGGAL TES & TARGET HASIL --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="modal_tested_at" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                    Tanggal Pelaksanaan Tes
                                </label>
                                <input type="date" id="modal_tested_at" name="tested_at" x-model="testedAt"
                                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                            </div>

                            <div>
                                <label for="modal_result_due_date" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                    Target Hasil (Opsional)
                                </label>
                                <input type="date" id="modal_result_due_date" name="result_due_date"
                                    placeholder="Otomatis 7 hari kerja jika kosong"
                                    class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] transition">
                                <span class="text-[10px] text-[#827299] mt-0.5 block">Default otomatis dihitung 7 hari kerja (Senin-Jumat).</span>
                            </div>
                        </div>

                        {{-- FIELD 5: FILE HASIL / LEMBAR DOKUMEN --}}
                        <div>
                            <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                Berkas Dokumen Hasil / Lembar Tes (Opsional)
                            </label>
                            <div class="relative border-2 border-dashed border-[#D9C2F0] hover:border-purple-deep/60 rounded-2xl p-4 text-center bg-white transition cursor-pointer group"
                                @click="$refs.fileInput.click()">
                                <input type="file" id="modal_file" name="file" x-ref="fileInput"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls" class="hidden"
                                    @change="handleFileChange($event)">

                                <div class="flex flex-col items-center justify-center gap-1.5 pointer-events-none">
                                    <template x-if="!fileName">
                                        <div class="flex flex-col items-center">
                                            <div class="w-9 h-9 rounded-xl bg-purple-deep/10 text-purple-deep flex items-center justify-center mb-1 group-hover:scale-105 transition-transform">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            </div>
                                            <span class="text-xs sm:text-sm font-semibold text-purple-deep group-hover:underline">Klik untuk memilih berkas dokumen</span>
                                            <span class="text-[11px] text-[#827299] mt-0.5">Maks. 10MB (PDF, DOC, DOCX, JPG, PNG, XLSX)</span>
                                        </div>
                                    </template>
                                    
                                    <template x-if="fileName">
                                        <div class="flex items-center gap-2 text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-200">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span class="text-xs font-bold truncate max-w-xs" x-text="fileName"></span>
                                            <span class="text-[10px] text-emerald-600">(klik untuk mengganti)</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- FIELD 6: RINGKASAN HASIL --}}
                        <div>
                            <label for="modal_result_summary" class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                Ringkasan Hasil / Catatan Awal
                            </label>
                            <textarea id="modal_result_summary" name="result_summary" rows="2" x-model="resultSummary"
                                placeholder="Ringkasan singkat, catatan pelaksanaan, atau arahan awal asesmen..."
                                class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-[#2A2035] resize-none transition"></textarea>
                            @error('result_summary') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#EDE1FA]">
                            <button type="button" @click="requestClose()"
                                class="px-5 py-2.5 text-center text-xs sm:text-sm font-semibold text-[#6B5B85] border border-[#D9C2F0] rounded-xl hover:bg-[#F7F5FB] transition cursor-pointer">
                                Batal
                            </button>

                            <button type="submit"
                                class="px-6 py-2.5 bg-purple-deep text-white text-xs sm:text-sm font-bold rounded-xl hover:bg-purple-deep/90 transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan Kasus Psikotes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL KONFIRMASI BATALKAN PENGISIAN DATA (PELINDUNG DATA INPUT)          --}}
    {{-- ========================================================================= --}}
    <div x-cloak x-show="confirmDiscardModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @click.self="cancelDiscard()">
        {{-- Backdrop --}}
        <div x-show="confirmDiscardModalOpen" 
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#1F0E38]/60 backdrop-blur-sm" 
            @click.stop="cancelDiscard()"></div>

        {{-- Dialog Box --}}
        <div x-show="confirmDiscardModalOpen" 
            @click.stop
            x-transition:enter="ease-out duration-200" 
            x-transition:enter-start="opacity-0 scale-95" 
            x-transition:enter-end="opacity-100 scale-100" 
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-3xl shadow-2xl border border-[#EDE1FA] max-w-sm w-full p-6 sm:p-7 z-10 space-y-4 text-center overflow-hidden">
            
            {{-- Specular Highlight Line --}}
            <div class="absolute top-0 inset-x-8 h-[1.5px] bg-gradient-to-r from-transparent via-amber-400 to-transparent pointer-events-none"></div>

            {{-- Warning Icon Badge --}}
            <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center shadow-xs">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            
            <div class="space-y-2">
                <h3 class="text-lg font-extrabold text-[#260E45] tracking-tight">Tinggalkan Pengisian Data?</h3>
                <p class="text-xs sm:text-sm text-[#6B5B85] leading-relaxed">
                    Data kasus psikotes yang telah Anda masukkan belum disimpan. Jika Anda keluar sekarang, seluruh draf pengisian formulir akan hilang.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" @click.stop="cancelDiscard()" 
                    class="w-full py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-purple-deep hover:text-[#260E45] bg-[#F7F5FB] hover:bg-[#EDE1FA] border border-[#D9C2F0] transition cursor-pointer">
                    Lanjut Mengisi
                </button>
                <button type="button" @click.stop="executeDiscard()" 
                    class="w-full py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 transition shadow-xs cursor-pointer">
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </div>
</div>
