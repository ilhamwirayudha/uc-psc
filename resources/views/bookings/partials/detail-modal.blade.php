    {{-- ========================================================================= --}}
    {{-- POP-UP MODAL DETAIL & EDIT BOOKING LAYANAN (STYLE IDENTIK DENGAN BUAT BOOKING) --}}
    {{-- STRICT RULE: Dilarang menutup modal saat klik backdrop/overlay           --}}
    {{-- ========================================================================= --}}
    <div 
        x-cloak 
        x-show="detailModalOpen" 
        class="fixed inset-0 z-50 overflow-y-auto" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="if (detailModalOpen) { handleCloseAttempt(); }"
    >
        {{-- Background backdrop (STRICT RULE: Dilarang memasang handler penutup pada backdrop/overlay) --}}
        <div x-show="detailModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

        {{-- Modal panel wrapper --}}
        <div class="min-h-full flex items-center justify-center p-4">
            {{-- Modal Dialog Container (Ukuran container max-w-4xl, bg-purple-deep rounded-2xl shadow-2xl) --}}
            <div x-show="detailModalOpen" x-transition class="relative bg-purple-deep rounded-2xl shadow-2xl overflow-hidden max-w-4xl w-full max-h-[90vh] flex flex-col z-10 my-6">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 bg-purple-deep text-white border-b border-purple-900/40 shrink-0">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-white">Detail Booking Layanan / Jadwal Sesi</h3>
                    </div>
                    {{-- Tombol Close Eksplisit (Sesuai Aturan Modal) --}}
                    <button type="button" @click="handleCloseAttempt()" class="w-8 h-8 rounded-lg text-purple-200 hover:text-white hover:bg-white/10 flex items-center justify-center transition cursor-pointer" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Modal Scrollable Body & Form --}}
                <form :action="editForm.action_url" method="POST" class="flex-1 overflow-y-auto bg-[#FAF8FD] p-5 sm:p-6 pb-6 sm:pb-8 space-y-5 rounded-b-2xl scroll-smooth">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="client_id" :value="editForm.client_id">
                    <input type="hidden" name="kategori" :value="editForm.kategori">
                    <input type="hidden" name="counseling_type" :value="editForm.counseling_type">
                    <input type="hidden" name="tanggal_booking_dibuat" :value="editForm.tanggal_booking_dibuat">

                    {{-- ======================================================== --}}
                    {{-- CARD: BIODATA & PROFIL KLIEN (KLIEN TERDAFTAR)          --}}
                    {{-- ======================================================== --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                        <div class="px-5 py-3 bg-purple-deep border-b border-purple-900/40 flex items-center justify-between">
                            <h3 class="text-sm sm:text-base font-bold text-white">Biodata &amp; Profil Klien</h3>
                        </div>

                        {{-- Subcard Data Isian Profil Klien (Ditata Urut Atas ke Bawah, Tanpa Grid Samping, Tanpa Truncate) --}}
                        <div class="p-5 sm:p-6 space-y-3">
                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap</label>
                                <span class="text-black font-medium text-sm block break-words" x-text="selectedBooking?.client?.name || '-'"></span>
                            </div>

                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                <label class="block text-xs font-medium text-gray-500 mb-1">No. Telepon</label>
                                <span class="text-black font-medium text-sm block break-words" x-text="selectedBooking?.client?.phone || '-'"></span>
                            </div>

                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                                <span class="text-black font-medium text-sm block break-words" x-text="selectedBooking?.client?.email || '-'"></span>
                            </div>

                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                                <span class="text-black font-medium text-sm block break-words" x-text="selectedBooking?.client?.gender || '-'"></span>
                            </div>

                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Lahir</label>
                                <span class="text-black font-medium text-sm block break-words" x-text="selectedBooking?.client?.dob || '-'"></span>
                            </div>

                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Pendidikan &amp; Pekerjaan</label>
                                <span class="text-black font-medium text-sm block leading-relaxed break-words" x-text="(selectedBooking?.client?.education || '-') + ' • ' + (selectedBooking?.client?.occupation || '-')"></span>
                            </div>

                            <template x-if="selectedBooking?.client?.pic_name">
                                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">PIC Perusahaan</label>
                                    <span class="text-black font-medium text-sm block break-words" x-text="selectedBooking.client.pic_name"></span>
                                </div>
                            </template>

                            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Alamat Lengkap</label>
                                <span class="text-black font-medium text-sm block leading-relaxed break-words" x-text="selectedBooking?.client?.address || '-'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- CARD: PILIHAN LAYANAN                                    --}}
                    {{-- ======================================================== --}}
                    <div id="card-pilihan-layanan" class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                        <div class="px-5 py-3 bg-purple-deep border-b border-purple-900/40 flex items-center justify-between">
                            <h3 class="text-sm sm:text-base font-bold text-white">Pilihan Layanan</h3>
                            <div class="flex items-center gap-2">
                                <template x-if="editingCard !== 'layanan'">
                                    <button 
                                        type="button" 
                                        @click="startEdit('layanan')" 
                                        class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/20 text-white hover:bg-white/30 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                        title="Edit pilihan layanan"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        <span>Edit Data</span>
                                    </button>
                                </template>
                                <template x-if="editingCard === 'layanan'">
                                    <div class="flex items-center gap-2">
                                        <button 
                                            type="button" 
                                            @click="cancelEdit('layanan')" 
                                            class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-500 hover:bg-red-600 text-white transition cursor-pointer flex items-center gap-1 shadow-xs"
                                            title="Batalkan edit pilihan layanan"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            <span>Batal</span>
                                        </button>
                                        <button 
                                            type="submit" 
                                            x-show="hasCardChanged('layanan')"
                                            class="text-xs font-bold px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                            title="Simpan perubahan pilihan layanan"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                                            <span>Simpan</span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6 space-y-4">
                            {{-- 1. DUA KARTU PILIHAN BESAR (BERDAMPINGAN KANAN KIRI) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- KARTU 1: LAYANAN KONSELING --}}
                                <button type="button" 
                                    @click="if(editingCard === 'layanan') { switchEditCategory('konseling'); }"
                                    :class="editForm.kategori === 'konseling'
                                        ? 'border-2 border-purple-deep bg-purple-50/40 ring-1 ring-purple-deep/20 shadow-xs' 
                                        : (editingCard === 'layanan' ? 'border border-slate-200 bg-white hover:border-[#D9C2F0] hover:bg-[#FAF8FD] cursor-pointer' : 'border border-slate-200 bg-slate-50/60 opacity-60 cursor-default')"
                                    class="text-left p-4 sm:p-5 rounded-2xl transition duration-200 flex items-start gap-3.5 group relative">
                                    <div :class="editForm.kategori === 'konseling' ? 'bg-purple-deep text-white shadow-xs' : 'bg-purple-50 text-purple-deep'"
                                        class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0 pr-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h4 class="text-sm sm:text-base font-bold text-gray-900">Layanan Konseling</h4>
                                            <span x-show="editForm.kategori === 'konseling'" 
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold bg-purple-deep text-white px-2.5 py-0.5 rounded-full shrink-0">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                                <span>Dipilih</span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                            Konseling offline atau online bersama Psikolog Klinis PSC.
                                        </p>
                                    </div>
                                </button>

                                {{-- KARTU 2: LAYANAN PSIKOTES --}}
                                <button type="button" 
                                    @click="if(editingCard === 'layanan') { switchEditCategory('psikotes'); }"
                                    :class="editForm.kategori === 'psikotes'
                                        ? 'border-2 border-purple-deep bg-purple-50/40 ring-1 ring-purple-deep/20 shadow-xs' 
                                        : (editingCard === 'layanan' ? 'border border-slate-200 bg-white hover:border-[#D9C2F0] hover:bg-[#FAF8FD] cursor-pointer' : 'border border-slate-200 bg-slate-50/60 opacity-60 cursor-default')"
                                    class="text-left p-4 sm:p-5 rounded-2xl transition duration-200 flex items-start gap-3.5 group relative">
                                    <div :class="editForm.kategori === 'psikotes' ? 'bg-purple-deep text-white shadow-xs' : 'bg-purple-50 text-purple-deep'"
                                        class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 11l3 3L22 4"/>
                                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0 pr-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <h4 class="text-sm sm:text-base font-bold text-gray-900">Layanan Psikotes</h4>
                                            <span x-show="editForm.kategori === 'psikotes'" 
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold bg-purple-deep text-white px-2.5 py-0.5 rounded-full shrink-0">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                                <span>Dipilih</span>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                            Asesmen seleksi industri, pemetaan bakat minat, dan tes psikologi khusus.
                                        </p>
                                    </div>
                                </button>
                            </div>

                            {{-- 2. DROPDOWN YANG URUT KE BAWAH (Identik dengan Buat Booking Layanan) --}}
                            <div id="dropdown-paket-layanan-container" 
                                class="p-5 bg-slate-50 rounded-xl border border-slate-100 space-y-4">

                                {{-- ======================================================== --}}
                                {{-- CABANG DROPDOWN KONSELING                                --}}
                                {{-- ======================================================== --}}
                                <div x-show="editForm.kategori === 'konseling'" class="space-y-4">
                                    {{-- 1. Dropdown Jenis Layanan --}}
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Jenis Layanan <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="editForm.service_type" 
                                            :disabled="editingCard !== 'layanan'"
                                            @change="onKonselingTypeChange($event.target.value)"
                                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                            :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                            <option value="">-- Pilih Jenis Layanan --</option>
                                            <option value="konseling_reguler">Konseling Reguler</option>
                                            <option value="konseling_pro">Konseling Pro</option>
                                        </select>
                                    </div>

                                    {{-- 2. Dropdown Metode (Muncul setelah memilih Jenis Layanan) --}}
                                    <div x-show="Boolean(editForm.service_type)" x-transition>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Metode <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="editForm.service_metode" 
                                            :disabled="editingCard !== 'layanan'"
                                            @change="onKonselingMetodeChange($event.target.value)"
                                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                            :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                            <option value="">-- Pilih Metode --</option>
                                            <option value="Offline">Offline</option>
                                            <option value="Online">Online</option>
                                        </select>
                                    </div>

                                    {{-- 3. Dropdown Klien (Muncul setelah memilih Metode) --}}
                                    <div x-show="Boolean(editForm.service_type) && Boolean(editForm.service_metode)" x-transition>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Klien <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="editForm.service_klien" 
                                            :disabled="editingCard !== 'layanan'"
                                            @change="onKonselingKlienChange($event.target.value)"
                                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                            :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                            <option value="">-- Pilih Klien --</option>
                                            <option value="WNI">WNI</option>
                                            <option value="WNA">WNA</option>
                                        </select>
                                    </div>

                                    {{-- 4. Dropdown Kategori (Muncul setelah memilih Klien) --}}
                                    <div x-show="Boolean(editForm.service_type) && Boolean(editForm.service_metode) && Boolean(editForm.service_klien)" x-transition>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Kategori <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="editForm.service_kategori" 
                                            :disabled="editingCard !== 'layanan'"
                                            @change="onKonselingKategoriChange($event.target.value)"
                                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                            :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                            <option value="">-- Pilih Kategori --</option>
                                            <option value="Umum">Umum</option>
                                            <option value="Kerja Sama">Kerja Sama</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- ======================================================== --}}
                                {{-- CABANG DROPDOWN PSIKOTES                                 --}}
                                {{-- ======================================================== --}}
                                <div x-show="editForm.kategori === 'psikotes'" class="space-y-4">
                                    {{-- 1. Dropdown Jenis Layanan --}}
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Jenis Layanan <span class="text-red-500">*</span>
                                        </label>
                                        <select x-model="editForm.service_type" 
                                            :disabled="editingCard !== 'layanan'"
                                            @change="onPsikotesTypeChange($event.target.value)"
                                            class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                            :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                            <option value="">-- Pilih Jenis Layanan --</option>
                                            <option value="psikotes_industri">Psikotes Industri</option>
                                            <option value="psikotes_bakat_minat">Psikotes Non-Industri Bakat Minat</option>
                                            <option value="psikotes_khusus">Psikotes Non-Industri Khusus</option>
                                        </select>
                                    </div>

                                    {{-- 2. Dropdown Metode (Muncul setelah memilih Jenis Layanan) --}}
                                    <div x-show="Boolean(editForm.service_type)" x-transition>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Metode <span class="text-red-500">*</span>
                                        </label>
                                        {{-- Metode Psikotes Industri / Bakat Minat --}}
                                        <div x-show="editForm.service_type === 'psikotes_industri' || editForm.service_type === 'psikotes_bakat_minat'">
                                            <select x-model="editForm.service_metode" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesMetodeChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Metode --</option>
                                                <option value="Offline">Offline</option>
                                                <option value="Online">Online</option>
                                            </select>
                                        </div>
                                        {{-- Metode Psikotes Khusus --}}
                                        <div x-show="editForm.service_type === 'psikotes_khusus'">
                                            <select x-model="editForm.service_metode" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesMetodeChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Metode --</option>
                                                <option value="Offline">Offline</option>
                                                <option value="Online">Online</option>
                                                <option value="UCS MNA (Online)">UCS MNA (Online)</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- 3. Dropdown Format (Muncul setelah memilih Metode) --}}
                                    <div x-show="Boolean(editForm.service_type) && Boolean(editForm.service_metode)" x-transition>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Format <span class="text-red-500">*</span>
                                        </label>
                                        {{-- Format Psikotes Industri & Bakat Minat --}}
                                        <div x-show="editForm.service_type === 'psikotes_industri' || editForm.service_type === 'psikotes_bakat_minat'">
                                            <select x-model="editForm.service_format" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesFormatChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Format --</option>
                                                <option value="Short">Short</option>
                                                <option value="Long">Long</option>
                                            </select>
                                        </div>
                                        {{-- Format Psikotes Khusus (UCS MNA Online) --}}
                                        <div x-show="editForm.service_type === 'psikotes_khusus' && editForm.service_metode === 'UCS MNA (Online)'">
                                            <select x-model="editForm.service_format" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesFormatChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Format --</option>
                                                <option value="UCS-MED">UCS-MED</option>
                                                <option value="UCS-DEM">UCS-DEM</option>
                                                <option value="UCS - PPSY">UCS - PPSY</option>
                                            </select>
                                        </div>
                                        {{-- Format Psikotes Khusus (Offline / Online) --}}
                                        <div x-show="editForm.service_type === 'psikotes_khusus' && (editForm.service_metode === 'Offline' || editForm.service_metode === 'Online' || editForm.service_metode === 'Online & Offline')">
                                            <select x-model="editForm.service_format" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesFormatChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Format --</option>
                                                <option value="Konseling Psikotes">Konseling Psikotes</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- 4. Dropdown Level (Muncul HANYA ketika memilih Psikotes Industri, setelah Format) --}}
                                    <div x-show="editForm.service_type === 'psikotes_industri' && Boolean(editForm.service_metode) && Boolean(editForm.service_format)" x-transition>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Level <span class="text-red-500">*</span>
                                        </label>
                                        {{-- Level jika Format Short (HANYA Staf) --}}
                                        <div x-show="editForm.service_format === 'Short'">
                                            <select x-model="editForm.service_level" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesLevelChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Level --</option>
                                                <option value="Staf">Staf</option>
                                            </select>
                                        </div>
                                        {{-- Level jika Format Long (Staf, Supervisor, Manager) --}}
                                        <div x-show="editForm.service_format === 'Long'">
                                            <select x-model="editForm.service_level" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesLevelChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Level --</option>
                                                <option value="Staf">Staf</option>
                                                <option value="Supervisor">Supervisor</option>
                                                <option value="Manager">Manager</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- 5. Dropdown Kategori (Muncul setelah Format / Level) --}}
                                    <div x-show="Boolean(editForm.service_format) && (editForm.service_type !== 'psikotes_industri' || Boolean(editForm.service_level))" x-transition>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Kategori <span class="text-red-500">*</span>
                                        </label>
                                        {{-- Kategori untuk Psikotes Industri & Bakat Minat: Umum & Kerja Sama --}}
                                        <div x-show="editForm.service_type === 'psikotes_industri' || editForm.service_type === 'psikotes_bakat_minat'">
                                            <select x-model="editForm.service_kategori" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesKategoriChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Kategori --</option>
                                                <option value="Umum">Umum</option>
                                                <option value="Kerja Sama">Kerja Sama</option>
                                            </select>
                                        </div>
                                        {{-- Kategori untuk Psikotes Khusus UCS-MED: MMPI --}}
                                        <div x-show="editForm.service_type === 'psikotes_khusus' && editForm.service_format === 'UCS-MED'">
                                            <select x-model="editForm.service_kategori" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesKategoriChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Kategori --</option>
                                                <option value="MMPI">MMPI</option>
                                            </select>
                                        </div>
                                        {{-- Kategori untuk Psikotes Khusus UCS-DEM: MMPI, FCAT-FEAST --}}
                                        <div x-show="editForm.service_type === 'psikotes_khusus' && editForm.service_format === 'UCS-DEM'">
                                            <select x-model="editForm.service_kategori" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesKategoriChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Kategori --</option>
                                                <option value="MMPI">MMPI</option>
                                                <option value="FCAT-FEAST">FCAT-FEAST</option>
                                            </select>
                                        </div>
                                        {{-- Kategori untuk Psikotes Khusus UCS - PPSY: FCAT - FTPI - FEAST --}}
                                        <div x-show="editForm.service_type === 'psikotes_khusus' && (editForm.service_format === 'UCS - PPSY' || editForm.service_format === 'UCS-PPSY')">
                                            <select x-model="editForm.service_kategori" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesKategoriChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Kategori --</option>
                                                <option value="FCAT - FTPI - FEAST">FCAT - FTPI - FEAST</option>
                                            </select>
                                        </div>
                                        {{-- Kategori untuk Psikotes Khusus Konseling Psikotes: SSCT - EPPS --}}
                                        <div x-show="editForm.service_type === 'psikotes_khusus' && editForm.service_format === 'Konseling Psikotes'">
                                            <select x-model="editForm.service_kategori" 
                                                :disabled="editingCard !== 'layanan'"
                                                @change="onPsikotesKategoriChange($event.target.value)"
                                                class="w-full px-4 py-2.5 rounded-xl text-sm font-medium transition"
                                                :class="editingCard === 'layanan' ? 'bg-white border border-[#D9C2F0] focus:outline-none focus:ring-2 focus:ring-purple-deep text-black cursor-pointer' : 'bg-slate-100 border border-slate-200 text-black cursor-default'">
                                                <option value="">-- Pilih Kategori --</option>
                                                <option value="SSCT - EPPS">SSCT - EPPS</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- ======================================================== --}}
                                {{-- RINGKASAN FORMAT DATA LAYANAN HASIL PEMILIHAN            --}}
                                {{-- ======================================================== --}}
                                <div x-show="Boolean(editForm.counseling_type)" x-transition class="pt-3 border-t border-slate-200">
                                    <label class="block text-xs font-bold text-[#5B4A73] mb-1.5">
                                        Format Data Layanan (Tercatat)
                                    </label>
                                    <div class="px-4 py-2.5 bg-white border border-slate-200/90 rounded-xl text-sm text-black font-medium flex items-center gap-2 shadow-xs"
                                        :title="editForm.counseling_type">
                                        <svg class="w-4 h-4 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        <span class="break-words" x-text="editForm.counseling_type"></span>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- CARD: PENUGASAN KONSELOR / STAFF PELAKSANA               --}}
                    {{-- ======================================================== --}}
                    <div id="card-penugasan" class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                        <div class="px-5 py-3 bg-purple-deep border-b border-purple-900/40 flex items-center justify-between">
                            <h3 class="text-sm sm:text-base font-bold text-white">Penugasan Psikolog / Staff Pelaksana</h3>
                            <template x-if="editingCard !== 'penugasan'">
                                <button 
                                    type="button" 
                                    @click="startEdit('penugasan')" 
                                    class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/20 text-white hover:bg-white/30 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                    title="Edit penugasan psikolog / staff"
                                >
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    <span>Edit Data</span>
                                </button>
                            </template>
                            <template x-if="editingCard === 'penugasan'">
                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        @click="cancelEdit('penugasan')" 
                                        class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-500 hover:bg-red-600 text-white transition cursor-pointer flex items-center gap-1 shadow-xs"
                                        title="Batalkan edit penugasan"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        <span>Batal</span>
                                    </button>
                                    <button 
                                        type="submit" 
                                        x-show="hasCardChanged('penugasan')"
                                        class="text-xs font-bold px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                        title="Simpan penugasan"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                                        <span>Simpan</span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="p-5 sm:p-6 space-y-4">
                            {{-- Cabang Konseling --}}
                            <div x-show="(editForm.kategori || selectedBooking?.kategori) === 'konseling'">
                                <template x-if="editingCard !== 'penugasan'">
                                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between">
                                        <div>
                                            <span class="block text-xs font-medium text-gray-500">Psikolog Bertugas</span>
                                            <p class="text-sm font-medium text-black mt-1" x-text="selectedBooking?.counselor?.name || 'Belum Ditentukan'"></p>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="selectedBooking?.counselor?.specialization || 'Psikolog Praktik'"></p>
                                        </div>
                                        <span class="px-2.5 py-1 bg-purple-deep/10 text-purple-deep font-semibold rounded-lg text-xs" x-text="selectedBooking?.counselor ? 'Psikolog Aktif' : 'Belum Assign'"></span>
                                    </div>
                                </template>
                                <template x-if="editingCard === 'penugasan'">
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                            Pilih Psikolog Bertugas
                                        </label>
                                        <select name="counselor_id" x-model="editForm.counselor_id" 
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                            <option value="">-- Belum Ditentukan / Tentukan Nanti --</option>
                                            @foreach($counselors as $counselor)
                                            <option value="{{ $counselor->id }}">
                                                {{ $counselor->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </template>
                            </div>

                            {{-- Cabang Psikotes --}}
                            <div x-show="(editForm.kategori || selectedBooking?.kategori) === 'psikotes'">
                                <template x-if="editingCard !== 'penugasan'">
                                    <div class="space-y-3">
                                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl w-full">
                                            <span class="block text-xs font-medium text-gray-500 mb-1">Staff Penguji / Tester</span>
                                            <p class="font-medium text-black text-sm break-words" x-text="selectedBooking?.staff_penguji?.name || '-'"></p>
                                        </div>
                                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl w-full">
                                            <span class="block text-xs font-medium text-gray-500 mb-1">Staff Koreksi / Skoring</span>
                                            <p class="font-medium text-black text-sm break-words" x-text="selectedBooking?.staff_koreksi?.name || '-'"></p>
                                        </div>
                                        <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl w-full">
                                            <span class="block text-xs font-medium text-gray-500 mb-1">Staff Pelapor / Hasil</span>
                                            <p class="font-medium text-black text-sm break-words" x-text="selectedBooking?.staff_pelapor?.name || '-'"></p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="editingCard === 'penugasan'">
                                    <div class="space-y-3.5">
                                        <div>
                                            <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Staff Penguji / Tester</label>
                                            <select name="staff_penguji_id" x-model="editForm.staff_penguji_id"
                                                class="w-full px-3.5 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                                <option value="">-- Pilih Staff --</option>
                                                @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Staff Koreksi / Skoring</label>
                                            <select name="staff_koreksi_id" x-model="editForm.staff_koreksi_id"
                                                class="w-full px-3.5 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                                <option value="">-- Pilih Staff --</option>
                                                @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Staff Pelapor / Hasil</label>
                                            <select name="staff_pelapor_id" x-model="editForm.staff_pelapor_id"
                                                class="w-full px-3.5 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                                <option value="">-- Pilih Staff --</option>
                                                @foreach($staffs as $staff)
                                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </template>
                            </div>


                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- CARD: JADWAL, WAKTU & LOKASI SESI                        --}}
                    {{-- ======================================================== --}}
                    <div id="card-jadwal" class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                        <div class="px-5 py-3 bg-purple-deep border-b border-purple-900/40 flex items-center justify-between">
                            <h3 class="text-sm sm:text-base font-bold text-white">Jadwal, Waktu &amp; Lokasi Sesi</h3>
                            <template x-if="editingCard !== 'jadwal'">
                                <button 
                                    type="button" 
                                    @click="startEdit('jadwal')" 
                                    class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/20 text-white hover:bg-white/30 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                    title="Edit jadwal, waktu &amp; lokasi"
                                >
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    <span>Edit Data</span>
                                </button>
                            </template>
                            <template x-if="editingCard === 'jadwal'">
                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        @click="cancelEdit('jadwal')" 
                                        class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-500 hover:bg-red-600 text-white transition cursor-pointer flex items-center gap-1 shadow-xs"
                                        title="Batalkan edit jadwal"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        <span>Batal</span>
                                    </button>
                                    <button 
                                        type="submit" 
                                        x-show="hasCardChanged('jadwal')"
                                        class="text-xs font-bold px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                        title="Simpan jadwal"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                                        <span>Simpan</span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="p-5 sm:p-6 space-y-4">
                            {{-- Mode View: Kartu Tampilan Jadwal --}}
                            <template x-if="editingCard !== 'jadwal'">
                                <div class="space-y-3">
                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                        <span class="block text-xs font-medium text-gray-500 mb-1">Tanggal Permintaan Booking</span>
                                        <span class="text-black font-medium text-sm block" x-text="selectedBooking?.tanggal_booking_dibuat_formatted || '-'"></span>
                                        <span class="text-gray-500 font-medium text-xs block mt-0.5" x-text="'Pukul ' + (selectedBooking?.jam_dibuat || '-')"></span>
                                    </div>

                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                        <span class="block text-xs font-medium text-gray-500 mb-1">Tanggal Sesi Pelaksanaan</span>
                                        <span class="text-black font-medium text-sm block" x-text="selectedBooking?.tanggal_dijadwalkan_formatted || 'Belum Dijadwalkan'"></span>
                                    </div>

                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                        <span class="block text-xs font-medium text-gray-500 mb-1">Jam Pelaksanaan Sesi</span>
                                        <span class="text-black font-medium text-sm block" x-text="selectedBooking?.jam_sesi || 'Jam belum diatur'"></span>
                                    </div>

                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                        <span class="block text-xs font-medium text-gray-500 mb-1">Metode Pertemuan / Sesi</span>
                                        <span class="text-black font-medium text-sm block" x-text="selectedBooking?.session_type ? (selectedBooking.session_type === 'online' ? 'Online' : 'Offline') : 'Offline'"></span>
                                    </div>

                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                        <span class="block text-xs font-medium text-gray-500 mb-1">Ruangan / Lokasi / Link Meeting</span>
                                        <span class="text-black font-medium text-sm block leading-relaxed break-words" x-text="selectedBooking?.location || '-'"></span>
                                    </div>
                                </div>
                            </template>

                            {{-- Mode Edit: Form Input Jadwal (Ditata Atas ke Bawah) --}}
                            <template x-if="editingCard === 'jadwal'">
                                <div class="space-y-3.5">
                                    {{-- Tanggal Permintaan Booking (Readonly) --}}
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">
                                             Tanggal Permintaan Booking
                                        </label>
                                        <div class="relative">
                                            <input type="text" readonly :value="(selectedBooking?.tanggal_booking_dibuat_formatted || '') + ' • ' + (selectedBooking?.jam_dibuat || '')"
                                                class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-black font-medium focus:outline-none cursor-default select-none">
                                            <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tanggal Sesi Dijadwalkan --}}
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Tanggal Sesi Pelaksanaan</label>
                                        <input type="date" name="tanggal_dijadwalkan" x-model="editForm.tanggal_dijadwalkan"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                    </div>

                                    {{-- Jam Mulai --}}
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Jam Mulai Sesi</label>
                                        <input type="time" name="start_time" x-model="editForm.start_time"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                    </div>

                                    {{-- Jam Selesai --}}
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Jam Selesai Sesi</label>
                                        <input type="time" name="end_time" x-model="editForm.end_time"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                    </div>

                                    {{-- Metode Sesi --}}
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Metode Pertemuan / Sesi</label>
                                        <select name="session_type" x-model="editForm.session_type"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                            <option value="tatap_muka">Offline</option>
                                            <option value="online">Online</option>
                                            <option value="whatsapp">WhatsApp</option>
                                        </select>
                                    </div>

                                    {{-- Ruangan / Lokasi --}}
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Ruangan / Lokasi / Link Meeting</label>
                                        <input type="text" name="location" x-model="editForm.location" 
                                            :placeholder="editForm.session_type === 'online' ? 'https://meet.google.com/xxx-xxxx-xxx' : 'Contoh: Ruang Konseling A / Lab Psikodiagnostik'"
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                    </div>
                                </div>


                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- CARD: DAFTAR ANGGOTA / PESERTA (KHUSUS INDUSTRI/PSIKOTES)--}}
                    {{-- ======================================================== --}}
                    <div id="card-peserta" x-show="['industri', 'company', 'perusahaan'].includes(selectedBooking?.client?.jenis) || selectedBooking?.kategori === 'psikotes' || (editForm.participants && editForm.participants.length > 0 && editForm.participants[0] !== '')" 
                        class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                        <div class="px-5 py-3 bg-purple-deep border-b border-purple-900/40 flex items-center justify-between">
                            <h3 class="text-sm sm:text-base font-bold text-white">Daftar Karyawan / Peserta Asesmen</h3>
                            <div class="flex items-center gap-2">
                                <template x-if="editingCard !== 'peserta'">
                                    <button 
                                        type="button" 
                                        @click="startEdit('peserta')" 
                                        class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/20 text-white hover:bg-white/30 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                        title="Edit daftar peserta"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        <span>Edit Data</span>
                                    </button>
                                </template>
                                <template x-if="editingCard === 'peserta'">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="addParticipant()" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/20 text-white hover:bg-white/30 transition cursor-pointer flex items-center gap-1">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            <span>Tambah Peserta</span>
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="cancelEdit('peserta')" 
                                            class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-500 hover:bg-red-600 text-white transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                            title="Batalkan edit peserta"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            <span>Batal</span>
                                        </button>
                                        <button 
                                            type="submit" 
                                            x-show="hasCardChanged('peserta')"
                                            class="text-xs font-bold px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                            title="Simpan daftar peserta"
                                        >
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                                            <span>Simpan</span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6 space-y-3">
                            {{-- Mode View: Tampilan Peserta --}}
                            <template x-if="editingCard !== 'peserta'">
                                <div>
                                    <template x-if="selectedBooking?.participants && selectedBooking.participants.length > 0">
                                        <div class="space-y-2">
                                            <template x-for="(nama, idx) in selectedBooking.participants" :key="idx">
                                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2.5 w-full">
                                                    <span class="w-6 h-6 rounded-full bg-purple-deep/10 text-purple-deep font-bold text-xs flex items-center justify-center shrink-0" x-text="idx + 1"></span>
                                                    <span class="text-black font-medium text-sm break-words" x-text="nama"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!selectedBooking?.participants || selectedBooking.participants.length === 0">
                                        <p class="text-xs text-gray-500 italic p-3 bg-slate-50 rounded-xl border border-slate-100">Belum ada daftar nama peserta yang dicatat.</p>
                                    </template>
                                </div>
                            </template>

                            {{-- Mode Edit: Form Input Peserta --}}
                            <template x-if="editingCard === 'peserta'">
                                <div class="space-y-2.5">
                                    <template x-for="(p, i) in editForm.participants" :key="i">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-[#6B5B85] w-6 text-right" x-text="(i + 1) + '.'"></span>
                                            <input type="text" :name="'participants[' + i + ']'" x-model="editForm.participants[i]"
                                                placeholder="Nama lengkap karyawan / kandidat..."
                                                class="flex-1 px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                            <button type="button" @click="removeParticipant(i)" x-show="editForm.participants.length > 1"
                                                class="p-2 text-red-400 hover:text-red-600 transition rounded-lg hover:bg-red-50 cursor-pointer" title="Hapus peserta">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                    </template>


                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- CARD: STATUS & CATATAN BOOKING                           --}}
                    {{-- ======================================================== --}}
                    <div id="card-status" class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                        <div class="px-5 py-3 bg-purple-deep border-b border-purple-900/40 flex items-center justify-between">
                            <h3 class="text-sm sm:text-base font-bold text-white">Status &amp; Catatan Sesi Booking</h3>
                            <template x-if="editingCard !== 'status'">
                                <button 
                                    type="button" 
                                    @click="startEdit('status')" 
                                    class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-white/20 text-white hover:bg-white/30 transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                    title="Edit status &amp; catatan booking"
                                >
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    <span>Edit Data</span>
                                </button>
                            </template>
                            <template x-if="editingCard === 'status'">
                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        @click="cancelEdit('status')" 
                                        class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-500 hover:bg-red-600 text-white transition cursor-pointer flex items-center gap-1 shadow-xs"
                                        title="Batalkan edit status"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        <span>Batal</span>
                                    </button>
                                    <button 
                                        type="submit" 
                                        x-show="hasCardChanged('status')"
                                        class="text-xs font-bold px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition cursor-pointer flex items-center gap-1.5 shadow-xs"
                                        title="Simpan status & catatan"
                                    >
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                                        <span>Simpan</span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="p-5 sm:p-6 space-y-4">
                            {{-- Mode View: Status & Catatan (Ditata Atas ke Bawah) --}}
                            <template x-if="editingCard !== 'status'">
                                <div class="space-y-3">
                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                        <span class="block text-xs font-medium text-gray-500 mb-1">Status Booking</span>
                                        <span class="text-black font-medium text-sm block" x-text="selectedBooking?.status_label"></span>
                                    </div>
                                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 w-full">
                                        <span class="block text-xs font-medium text-gray-500 mb-1">Catatan Khusus Sesi</span>
                                        <p class="text-black font-medium text-sm leading-relaxed whitespace-pre-line break-words" x-text="selectedBooking?.notes || 'Tidak ada catatan tambahan untuk booking ini.'"></p>
                                    </div>
                                </div>
                            </template>

                            {{-- Mode Edit: Form Status & Catatan (Ditata Atas ke Bawah) --}}
                            <template x-if="editingCard === 'status'">
                                <div class="space-y-3.5">
                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Status Booking <span class="text-red-500">*</span></label>
                                        <select name="status" x-model="editForm.status" required
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium">
                                            <option value="baru">Baru</option>
                                            <option value="lanjutan">Lanjutan</option>
                                            <option value="selesai">Selesai</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs sm:text-[13px] font-bold text-[#5B4A73] mb-1.5">Catatan Khusus Sesi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                        <textarea name="notes" x-model="editForm.notes" rows="3" placeholder="Informasi kebutuhan khusus klien, paket yang diambil, atau instruksi ruangan..."
                                            class="w-full px-4 py-2.5 bg-white border border-[#D9C2F0] rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-deep text-black font-medium"></textarea>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- CARD: RIWAYAT SESI BOOKING LAINNYA (JIKA ADA > 1 BOOKING)--}}
                    {{-- ======================================================== --}}
                    <template x-if="selectedBooking?.client?.bookings && selectedBooking.client.bookings.length > 1">
                        <div class="bg-white rounded-2xl shadow-sm border border-[#EDE1FA] overflow-hidden">
                            <div class="px-5 py-3 bg-purple-deep border-b border-purple-900/40 flex items-center justify-between">
                                <h3 class="text-sm sm:text-base font-bold text-white">Riwayat Sesi Booking Klien Lainnya</h3>
                                <span class="text-xs text-purple-200 font-semibold" x-text="selectedBooking.client.bookings.length + ' sesi tercatat'"></span>
                            </div>
                            <div class="divide-y divide-slate-100 p-2">
                                <template x-for="b in selectedBooking.client.bookings" :key="b.id">
                                    <div class="p-3 flex items-center justify-between text-xs hover:bg-slate-50 rounded-xl transition" :class="b.id === selectedBooking.id ? 'bg-purple-50/50 font-bold' : ''">
                                        <div>
                                            <span class="font-bold text-purple-deep" x-text="'Booking #' + b.id"></span>
                                            <template x-if="b.id === selectedBooking.id">
                                                <span class="ml-1 text-[10px] font-semibold text-purple-deep">(Sesi saat ini)</span>
                                            </template>
                                            <span class="text-black font-medium ml-2" x-text="b.tanggal_dijadwalkan"></span>
                                            <template x-if="b.counselor_name">
                                                <span class="text-gray-500 ml-1" x-text="'• ' + b.counselor_name"></span>
                                            </template>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                            :class="b.status === 'selesai' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'"
                                            x-text="b.status_label"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- ======================================================== --}}
                    {{-- CARD: BERKAS & HASIL ASESMEN KLIEN (JIKA ADA)            --}}
                    {{-- ======================================================== --}}
                            </div>
                        </div>
                    </template>

                </form>

                {{-- Form Hidden untuk Delete Booking --}}
                <form id="delete-booking-form" :action="selectedBooking?.destroy_url || ('/bookings/' + (selectedBooking?.id || ''))" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
