<div x-data="{
    clientModalOpen: false,
    confirmDiscardModalOpen: false,
    jenis: '{{ old('jenis', 'individu') }}',
    form_type: '{{ old('form_type', 'dewasa') }}',

    init() {
        @if($errors->any() && old('from_client_modal'))
            this.clientModalOpen = true;
            this.jenis = '{{ old('jenis', 'individu') }}';
            this.form_type = '{{ old('form_type', 'dewasa') }}';
        @endif
    },

    setJenis(newJenis) {
        this.jenis = newJenis;
        if (newJenis === 'industri') {
            this.form_type = 'industri';
        } else if (this.form_type === 'industri') {
            this.form_type = 'dewasa';
        }
    },

    setFormType(type) {
        this.form_type = type;
        if (type === 'industri') {
            this.jenis = 'industri';
        } else {
            this.jenis = 'individu';
        }
    },

    openModal(initialJenis = null, initialFormType = null) {
        this.clientModalOpen = true;
        if (initialJenis) {
            this.setJenis(initialJenis);
        }
        if (initialFormType) {
            this.setFormType(initialFormType);
        }
    },

    requestClose() {
        this.clientModalOpen = false;
    },

    forceClose() {
        this.clientModalOpen = false;
    }
}"
@open-client-modal.window="openModal($event.detail?.jenis, $event.detail?.form_type)"
@keydown.escape.window="if (confirmDiscardModalOpen) { confirmDiscardModalOpen = false; } else if (clientModalOpen) { requestClose(); }"
class="relative z-50"
aria-labelledby="client-modal-title" role="dialog" aria-modal="true" x-cloak>

    {{-- Background backdrop --}}
    <div x-show="clientModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>
    
    {{-- Modal panel --}}
    <div x-show="clientModalOpen" x-transition class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-3 text-center sm:items-center sm:p-4" @click.self="requestClose()">
            <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-6 w-full max-w-5xl border border-purple-100">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-deep via-[#4A2F85] to-purple-light px-6 py-4.5 rounded-t-3xl flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-white/15 flex items-center justify-center text-white text-lg shadow-2xs">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg sm:text-xl font-extrabold leading-6 text-white" id="client-modal-title">Tambah Klien Baru</h3>
                            <p class="text-xs text-purple-200 mt-0.5">Pendaftaran klien berdasarkan jenis formulir layanan UC Psychological Service Center</p>
                        </div>
                    </div>
                    <button type="button" @click="requestClose()" class="text-white/80 hover:text-white p-2 rounded-xl hover:bg-white/10 transition cursor-pointer" title="Tutup formulir">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="px-5 sm:px-8 pt-6 pb-8 max-h-[86vh] overflow-y-auto bg-white">
                    
                    {{-- Error Summary Alert --}}
                    @if($errors->any() && old('from_client_modal'))
                    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4.5 shadow-xs mb-6">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-rose-800 mb-1">Mohon lengkapi isian formulir berikut:</h4>
                                <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Main Form --}}
                    <form action="{{ route('clients.store') }}" method="POST" data-unsaved-guard class="space-y-6">
                        @csrf
                        <input type="hidden" name="from_client_modal" value="1">
                        <input type="hidden" name="jenis" :value="jenis">
                        <input type="hidden" name="form_type" :value="form_type">

                        {{-- SELEKTOR UTAMA: TIPE KLIEN & PILIHAN FORMULIR LAYANAN --}}
                        <div class="bg-white rounded-3xl shadow-xs border border-[#EDE1FA] p-6 space-y-5">
                            
                            {{-- Baris 1: Toggle Individu vs Industri --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#827299] mb-2.5">
                                    1. Pilih Tipe Pemohon / Klien
                                </label>
                                <div class="grid grid-cols-2 gap-3 p-1.5 bg-[#F7F5FB] rounded-2xl border border-[#EDE1FA]">
                                    {{-- Opsi 1: Individu --}}
                                    <button type="button"
                                        @click="setJenis('individu')"
                                        :class="jenis === 'individu' ? 'bg-white text-purple-deep shadow-xs border border-[#D9C2F0]' : 'text-[#6B5B85] hover:text-purple-deep'"
                                        class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl text-sm sm:text-base font-bold cursor-pointer transition">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <span>Individu</span>
                                    </button>

                                    {{-- Opsi 2: Industri --}}
                                    <button type="button"
                                        @click="setJenis('industri')"
                                        :class="jenis === 'industri' ? 'bg-white text-purple-deep shadow-xs border border-[#D9C2F0]' : 'text-[#6B5B85] hover:text-purple-deep'"
                                        class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl text-sm sm:text-base font-bold cursor-pointer transition">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                        <span>Industri</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Baris 2: Pemilihan Formulir Spesifik Layanan --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#827299] mb-2.5">
                                    2. Pilih Formulir Layanan Klien
                                </label>

                                {{-- Jika Tipe INDIVIDU: Tampilkan 6 Formulir Individu --}}
                                <div x-show="jenis === 'individu'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    
                                    {{-- 1. Dewasa --}}
                                    <button type="button"
                                        @click="setFormType('dewasa')"
                                        :class="form_type === 'dewasa' ? 'border-2 border-purple-deep bg-[#F3EAFB] shadow-xs' : 'border border-[#EDE1FA] bg-[#FAF9FD] hover:bg-white hover:border-purple-300'"
                                        class="p-4 rounded-2xl text-left cursor-pointer transition flex flex-col justify-between space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-2xl">👤</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                                :class="form_type === 'dewasa' ? 'bg-purple-deep text-white' : 'bg-purple-100 text-purple-800'">
                                                Aktif
                                            </span>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Konseling - Dewasa</h5>
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">Evaluasi psikologis, trauma, dan relasi klien perorangan dewasa.</p>
                                        </div>
                                    </button>

                                    {{-- 2. Anak --}}
                                    <button type="button"
                                        @click="setFormType('anak')"
                                        :class="form_type === 'anak' ? 'border-2 border-amber-500 bg-amber-50/70 shadow-xs' : 'border border-[#EDE1FA] bg-[#FAF9FD] hover:bg-white hover:border-amber-300'"
                                        class="p-4 rounded-2xl text-left cursor-pointer transition flex flex-col justify-between space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-2xl">🧒</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                                :class="form_type === 'anak' ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-800'">
                                                Aktif
                                            </span>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Konseling - Anak</h5>
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">Tumbuh kembang anak, relasi keluarga, dan data orang tua/wali.</p>
                                        </div>
                                    </button>

                                    {{-- 3. Pra Nikah --}}
                                    <button type="button"
                                        @click="setFormType('pra_nikah')"
                                        :class="form_type === 'pra_nikah' ? 'border-2 border-rose-500 bg-rose-50/70 shadow-xs' : 'border border-[#EDE1FA] bg-[#FAF9FD] hover:bg-white hover:border-rose-300'"
                                        class="p-4 rounded-2xl text-left cursor-pointer transition flex flex-col justify-between space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-2xl">💍</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                                :class="form_type === 'pra_nikah' ? 'bg-rose-600 text-white' : 'bg-rose-100 text-rose-800'">
                                                Aktif
                                            </span>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Konseling - Pra Nikah</h5>
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">Kesiapan mental, ekspektasi bersama, dan data calon pasangan.</p>
                                        </div>
                                    </button>

                                    {{-- 4. Pernikahan --}}
                                    <button type="button"
                                        @click="setFormType('pernikahan')"
                                        :class="form_type === 'pernikahan' ? 'border-2 border-red-500 bg-red-50/70 shadow-xs' : 'border border-[#EDE1FA] bg-[#FAF9FD] hover:bg-white hover:border-red-300'"
                                        class="p-4 rounded-2xl text-left cursor-pointer transition flex flex-col justify-between space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-2xl">❤️</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                                :class="form_type === 'pernikahan' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-800'">
                                                Aktif
                                            </span>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Konseling - Pernikahan</h5>
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">Dinamika relasi suami istri, resolusi konflik, dan keharmonisan.</p>
                                        </div>
                                    </button>

                                    {{-- 5. Non-Industri --}}
                                    <button type="button"
                                        @click="setFormType('non_industri')"
                                        :class="form_type === 'non_industri' ? 'border-2 border-teal-600 bg-teal-50/70 shadow-xs' : 'border border-[#EDE1FA] bg-[#FAF9FD] hover:bg-white hover:border-teal-300'"
                                        class="p-4 rounded-2xl text-left cursor-pointer transition flex flex-col justify-between space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-2xl">🎓</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                                :class="form_type === 'non_industri' ? 'bg-teal-700 text-white' : 'bg-teal-100 text-teal-800'">
                                                Aktif
                                            </span>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Riwayat Hidup - Non-Industri</h5>
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">Asesmen penelusuran minat bakat sekolah, komunitas & pendidikan.</p>
                                        </div>
                                    </button>

                                    {{-- 6. Biography Form (English) --}}
                                    <button type="button"
                                        @click="setFormType('biography_en')"
                                        :class="form_type === 'biography_en' ? 'border-2 border-indigo-600 bg-indigo-50/70 shadow-xs' : 'border border-[#EDE1FA] bg-[#FAF9FD] hover:bg-white hover:border-indigo-300'"
                                        class="p-4 rounded-2xl text-left cursor-pointer transition flex flex-col justify-between space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-2xl">🌐</span>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                                :class="form_type === 'biography_en' ? 'bg-indigo-700 text-white' : 'bg-indigo-100 text-indigo-800'">
                                                Aktif
                                            </span>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-900 leading-snug">Biography Form (English)</h5>
                                            <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">Intake form for international clients and expatriates.</p>
                                        </div>
                                    </button>

                                </div>

                                {{-- Jika Tipe INDUSTRI: Tampilkan Formulir Industri --}}
                                <div x-show="jenis === 'industri'" class="max-w-md">
                                    <div class="p-4 rounded-2xl border-2 border-purple-deep bg-[#F3EAFB] shadow-xs flex items-start gap-3.5">
                                        <div class="w-10 h-10 rounded-xl bg-purple-deep text-white flex items-center justify-center text-xl flex-shrink-0">
                                            🏢
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h5 class="text-sm font-bold text-slate-900">Formulir Riwayat Hidup - Industri</h5>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-deep text-white">Terpilih</span>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-1">Formulir seleksi, rekrutmen perusahaan, kuesioner stres PSS-10, dan riwayat pekerjaan.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CONTAINER KARTU ISIAN LENGKAP MASING-MASING FORM --}}
                        <div class="pt-2">
                            {{-- 1. Formulir Dewasa --}}
                            <div x-show="form_type === 'dewasa'" x-cloak>
                                @include('components.client-forms.dewasa-fields')
                            </div>

                            {{-- 2. Formulir Anak --}}
                            <div x-show="form_type === 'anak'" x-cloak>
                                @include('components.client-forms.anak-fields')
                            </div>

                            {{-- 3. Formulir Pra-Nikah --}}
                            <div x-show="form_type === 'pra_nikah'" x-cloak>
                                @include('components.client-forms.pra-nikah-fields')
                            </div>

                            {{-- 4. Formulir Pernikahan --}}
                            <div x-show="form_type === 'pernikahan'" x-cloak>
                                @include('components.client-forms.pernikahan-fields')
                            </div>

                            {{-- 5. Formulir Non-Industri --}}
                            <div x-show="form_type === 'non_industri'" x-cloak>
                                @include('components.client-forms.non-industri-fields')
                            </div>

                            {{-- 6. Biography Form (English) --}}
                            <div x-show="form_type === 'biography_en'" x-cloak>
                                @include('components.client-forms.biography-en-fields')
                            </div>

                            {{-- 7. Formulir Industri --}}
                            <div x-show="form_type === 'industri'" x-cloak>
                                @include('components.client-forms.industri-fields')
                            </div>
                        </div>

                        {{-- STICKY ACTION FOOTER --}}
                        <div class="sticky -bottom-4 bg-white rounded-2xl border border-purple-100 p-4 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3 z-20">
                            <div class="text-xs text-slate-500 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-purple-deep animate-pulse"></span>
                                <span>Tanda bintang merah (<strong class="text-red-500">*</strong>) wajib diisi sesuai form asli.</span>
                            </div>

                            <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                                <button type="button"
                                    @click="requestClose()"
                                    class="w-1/2 sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-semibold transition cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="w-1/2 sm:w-auto px-6 py-2.5 bg-gradient-to-r from-orange to-[#ff7d1a] hover:from-orange/90 hover:to-[#ff7d1a]/90 text-white text-sm font-bold rounded-xl shadow-md shadow-orange/20 transition flex items-center justify-center gap-2 cursor-pointer">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                        <polyline points="17 21 17 13 7 13 7 21"/>
                                        <polyline points="7 3 7 8 15 8"/>
                                    </svg>
                                    <span>Simpan Data Klien</span>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
