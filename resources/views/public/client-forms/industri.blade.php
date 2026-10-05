<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF9FD]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Formulir Riwayat Hidup Asesmen Industri — UC PSC</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ucpsc.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html { 
            font-size: 100% !important;
        }
        body { 
            font-family: 'Plus Jakarta Sans', 'Montserrat', sans-serif; 
            background-color: #FAF9FD;
            font-size: 16px;
        }
        [x-cloak] { display: none !important; }

        /* Typography Sizing for Online Form */
        label.block.font-bold,
        label.font-bold {
            font-size: 0.9375rem !important;
            line-height: 1.4 !important;
        }
        @media (min-width: 640px) {
            label.block.font-bold,
            label.font-bold {
                font-size: 1rem !important;
            }
        }

        p.text-xs.text-slate-500,
        span.text-xs.text-slate-500,
        .text-xs.text-slate-500,
        p.text-slate-500 {
            font-size: 0.8125rem !important;
            line-height: 1.45 !important;
        }

        input[type="text"],
        input[type="date"],
        input[type="tel"],
        input[type="email"],
        input[type="number"],
        textarea,
        select {
            font-size: 0.9rem !important;
            line-height: 1.45 !important;
        }

        label.cursor-pointer,
        label.cursor-pointer span,
        label.cursor-pointer div {
            font-size: 0.875rem !important;
        }

        button[type="submit"],
        button[type="button"] {
            font-size: 0.875rem !important;
        }

        p.text-rose-600 {
            font-size: 0.775rem !important;
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased selection:bg-purple-deep selection:text-white pb-16 relative">

    {{-- Ambient UC PSC Brand Background Atmosphere --}}
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[450px] bg-gradient-to-b from-[#F3EAFB] via-[#FAF9FD]/80 to-transparent rounded-full blur-3xl opacity-90"></div>
        <div class="absolute top-48 -right-24 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl"></div>
        <div class="absolute top-96 -left-20 w-80 h-80 bg-blue-100/25 rounded-full blur-3xl"></div>
    </div>

    {{-- Main Container --}}
    <main class="flex-1 py-6 sm:py-8 px-4" x-data="{
        page: 1,
        totalPages: 7,
        errors: {},

        // Safety Draft State
        draftSaved: false,
        hasRestoredDraft: false,
        formSubmitted: false,
        draftTimer: null,
        lastSavedTime: '',

        // Form Fields
        consent_agree: '{{ old('consent_agree', '') }}',
        
        // Halaman 2: Data Diri & Posisi
        nama_lengkap: '{{ old('nama_lengkap', '') }}',
        jenis_kelamin: '{{ old('jenis_kelamin', '') }}',
        tempat_tanggal_lahir: '{{ old('tempat_tanggal_lahir', '') }}',
        urutan_kelahiran: '{{ old('urutan_kelahiran', '') }}',
        alamat_sekarang: '{{ old('alamat_sekarang', '') }}',
        asal_kota: '{{ old('asal_kota', '') }}',
        phone: '{{ old('phone', '') }}',
        email: '{{ old('email', '') }}',
        agama: '{{ old('agama', '') }}',
        suku_bangsa: '{{ old('suku_bangsa', '') }}',
        status_perkawinan: '{{ old('status_perkawinan', '') }}',
        pendidikan_terakhir: '{{ old('pendidikan_terakhir', '') }}',
        pekerjaan_saat_ini: '{{ old('pekerjaan_saat_ini', '') }}',
        posisi_dituju: '{{ old('posisi_dituju', '') }}',

        // Halaman 3: PSS-10
        pss_1: '{{ old('pss_1', '') }}',
        pss_2: '{{ old('pss_2', '') }}',
        pss_3: '{{ old('pss_3', '') }}',
        pss_4: '{{ old('pss_4', '') }}',
        pss_5: '{{ old('pss_5', '') }}',
        pss_6: '{{ old('pss_6', '') }}',
        pss_7: '{{ old('pss_7', '') }}',
        pss_8: '{{ old('pss_8', '') }}',
        pss_9: '{{ old('pss_9', '') }}',
        pss_10: '{{ old('pss_10', '') }}',

        // Halaman 4: Data Keluarga
        ayah_nama: '{{ old('ayah_nama', '') }}',
        ayah_usia: '{{ old('ayah_usia', '') }}',
        ayah_pendidikan: '{{ old('ayah_pendidikan', '') }}',
        ayah_pekerjaan: '{{ old('ayah_pekerjaan', '') }}',
        ibu_nama: '{{ old('ibu_nama', '') }}',
        ibu_usia: '{{ old('ibu_usia', '') }}',
        ibu_pendidikan: '{{ old('ibu_pendidikan', '') }}',
        ibu_pekerjaan: '{{ old('ibu_pekerjaan', '') }}',
        data_keluarga_inti: '{{ old('data_keluarga_inti', '') }}',

        // Halaman 5: Pendidikan
        pendidikan_formal: '{{ old('pendidikan_formal', '') }}',
        kursus_pelatihan: '{{ old('kursus_pelatihan', '') }}',
        keahlian_bahasa: '{{ old('keahlian_bahasa', '') }}',

        // Halaman 6: Pekerjaan & Karir
        pengalaman_kerja: '{{ old('pengalaman_kerja', '') }}',
        prestasi_kerja: '{{ old('prestasi_kerja', '') }}',
        gaya_kepemimpinan: '{{ old('gaya_kepemimpinan', '') }}',

        // Halaman 7: Deskripsi Diri & Motivasi
        alasan_melamar: '{{ old('alasan_melamar', '') }}',
        kelebihan_kerja: '{{ old('kelebihan_kerja', '') }}',
        kekurangan_kerja: '{{ old('kekurangan_kerja', '') }}',
        harapan_gaji_fasilitas: '{{ old('harapan_gaji_fasilitas', '') }}',
        kontak_darurat: '{{ old('kontak_darurat', '') }}',

        init() {
            @if(!session('error') && !$errors->any())
                this.restoreDraft();
            @endif
        },

        saveDraft() {
            if (this.formSubmitted) return;
            const formEl = document.getElementById('industriUCPSCForm');
            if (!formEl) return;

            const draft = {};
            const formData = new FormData(formEl);
            for (const [key, value] of formData.entries()) {
                if (key === '_token') continue;
                draft[key] = value;
            }

            const fieldKeys = [
                'consent_agree', 'nama_lengkap', 'jenis_kelamin', 'tempat_tanggal_lahir',
                'urutan_kelahiran', 'alamat_sekarang', 'asal_kota', 'phone', 'email', 'agama',
                'suku_bangsa', 'status_perkawinan', 'pendidikan_terakhir', 'pekerjaan_saat_ini',
                'posisi_dituju', 'pss_1', 'pss_2', 'pss_3', 'pss_4', 'pss_5', 'pss_6', 'pss_7',
                'pss_8', 'pss_9', 'pss_10', 'ayah_nama', 'ayah_usia', 'ayah_pendidikan',
                'ayah_pekerjaan', 'ibu_nama', 'ibu_usia', 'ibu_pendidikan', 'ibu_pekerjaan',
                'data_keluarga_inti', 'pendidikan_formal', 'kursus_pelatihan', 'keahlian_bahasa',
                'pengalaman_kerja', 'prestasi_kerja', 'gaya_kepemimpinan', 'alasan_melamar',
                'kelebihan_kerja', 'kekurangan_kerja', 'harapan_gaji_fasilitas', 'kontak_darurat'
            ];

            fieldKeys.forEach(k => {
                if (this[k] !== undefined && this[k] !== null && this[k] !== '') {
                    draft[k] = this[k];
                }
            });

            draft['_saved_page'] = this.page;
            draft['_saved_at'] = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            try {
                localStorage.setItem('ucpsc_draft_industri', JSON.stringify(draft));
                this.draftSaved = true;
                this.hasRestoredDraft = true;
                this.lastSavedTime = draft['_saved_at'];
                clearTimeout(this.draftTimer);
                this.draftTimer = setTimeout(() => {
                    this.draftSaved = false;
                }, 3000);
            } catch (err) {
                console.warn('Gagal menyimpan draf:', err);
            }
        },

        restoreDraft() {
            try {
                const raw = localStorage.getItem('ucpsc_draft_industri');
                if (!raw) return;
                const draft = JSON.parse(raw);
                if (!draft || typeof draft !== 'object') return;

                const keys = Object.keys(draft).filter(k => !k.startsWith('_'));
                if (keys.length === 0) return;

                keys.forEach(key => {
                    if (this.hasOwnProperty(key)) {
                        if (draft[key] !== undefined && draft[key] !== null) {
                            this[key] = draft[key];
                        }
                    }
                });

                this.$nextTick(() => {
                    const formEl = document.getElementById('industriUCPSCForm');
                    if (formEl) {
                        keys.forEach(key => {
                            const field = formEl.elements[key];
                            if (field && !(field instanceof RadioNodeList) && field.type !== 'radio') {
                                field.value = draft[key];
                            }
                        });
                    }
                });

                if (draft._saved_page && draft._saved_page >= 1 && draft._saved_page <= this.totalPages) {
                    this.page = draft._saved_page;
                }

                this.hasRestoredDraft = true;
                this.lastSavedTime = draft._saved_at || '';
            } catch (err) {
                console.warn('Gagal memulihkan draf:', err);
            }
        },

        clearDraftOnSubmit() {
            this.formSubmitted = true;
            try {
                localStorage.removeItem('ucpsc_draft_industri');
            } catch (e) {}
        },

        validateCurrentPage() {
            this.errors = {};

            if (this.page === 1) {
                if (!this.consent_agree) {
                    this.errors['consent_agree'] = 'Anda wajib menyetujui Lembar Pernyataan Kejujuran untuk melanjutkan.';
                } else if (this.consent_agree === 'Tidak Setuju') {
                    this.errors['consent_agree'] = 'Anda harus menyetujui pernyataan untuk dapat melanjutkan pengisian asesmen.';
                }
            } else if (this.page === 2) {
                if (!this.nama_lengkap.trim()) this.errors['nama_lengkap'] = 'Pertanyaan ini wajib diisi';
                if (!this.jenis_kelamin) this.errors['jenis_kelamin'] = 'Pertanyaan ini wajib diisi';
                if (!this.tempat_tanggal_lahir.trim()) this.errors['tempat_tanggal_lahir'] = 'Pertanyaan ini wajib diisi';
                if (!this.urutan_kelahiran.trim()) this.errors['urutan_kelahiran'] = 'Pertanyaan ini wajib diisi';
                if (!this.alamat_sekarang.trim()) this.errors['alamat_sekarang'] = 'Pertanyaan ini wajib diisi';
                if (!this.asal_kota.trim()) this.errors['asal_kota'] = 'Pertanyaan ini wajib diisi';
                if (!this.phone.trim()) this.errors['phone'] = 'Pertanyaan ini wajib diisi';
                if (!this.agama.trim()) this.errors['agama'] = 'Pertanyaan ini wajib diisi';
                if (!this.suku_bangsa.trim()) this.errors['suku_bangsa'] = 'Pertanyaan ini wajib diisi';
                if (!this.status_perkawinan.trim()) this.errors['status_perkawinan'] = 'Pertanyaan ini wajib diisi';
                if (!this.pendidikan_terakhir.trim()) this.errors['pendidikan_terakhir'] = 'Pertanyaan ini wajib diisi';
                if (!this.pekerjaan_saat_ini.trim()) this.errors['pekerjaan_saat_ini'] = 'Pertanyaan ini wajib diisi';
                if (!this.posisi_dituju.trim()) this.errors['posisi_dituju'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 3) {
                // PSS-10
            } else if (this.page === 4) {
                // Data Keluarga
            } else if (this.page === 5) {
                // Pendidikan
            } else if (this.page === 6) {
                // Karir
            } else if (this.page === 7) {
                // Deskripsi Diri
            }

            if (Object.keys(this.errors).length > 0) {
                let firstKey = Object.keys(this.errors)[0];
                let el = document.getElementById('card_' + firstKey);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
            return true;
        },

        nextPage() {
            if (this.validateCurrentPage()) {
                this.page++;
                this.saveDraft();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        prevPage() {
            if (this.page > 1) {
                this.page--;
                this.saveDraft();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    }">
        <div class="max-w-3xl mx-auto space-y-4">

            {{-- FORM START --}}
            <form action="{{ route('public.client-form.industri.store') }}" 
                  method="POST" 
                  id="industriUCPSCForm" 
                  @input.debounce.400ms="saveDraft()" 
                  @change="saveDraft()"
                  @submit="if(!validateCurrentPage()){ $event.preventDefault(); } else { clearDraftOnSubmit(); }">
                @csrf

                {{-- ============================================================== --}}
                {{-- HALAMAN 1 DARI 7: LEMBAR PERNYATAAN KEJUJURAN                  --}}
                {{-- ============================================================== --}}
                <div x-show="page === 1" class="space-y-4">
                    {{-- Kartu Judul Banner Utama (Gradien Khas UC PSC) --}}
                    <div class="bg-gradient-to-r from-purple-deep via-[#4A2F85] to-purple-light rounded-2xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="relative z-10 space-y-2">
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Formulir Riwayat Hidup - Industri & Organisasi</h1>
                            <p class="text-white/90 text-sm sm:text-base leading-relaxed max-w-2xl">
                                Formulir Riwayat Hidup ini digunakan untuk asesmen psikologis rekrutmen, seleksi karyawan, asesmen promosi jabatan, dan pemetaan kompetensi profesional. Segala data yang diberikan terjamin kerahasiaannya.
                            </p>
                            <div class="pt-3 border-t border-white/20 text-xs sm:text-sm text-rose-200 font-medium">
                                * Menunjukkan pertanyaan yang wajib diisi
                            </div>
                        </div>
                    </div>

                    {{-- Kartu Teks Pernyataan --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
                        <div class="pb-3 border-b border-slate-100">
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900">PERNYATAAN KEJUJURAN & PERSETUJUAN ASESMEN</h2>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Asesmen Psikologi Industri & Organisasi Universitas Ciputra PSC</p>
                        </div>

                        <div class="text-sm sm:text-base text-slate-700 leading-relaxed space-y-4 max-h-[380px] overflow-y-auto pr-3 custom-scrollbar border border-slate-100 rounded-xl p-4 sm:p-5 bg-slate-50/50">
                            <div>
                                <h4 class="font-bold text-purple-deep text-base sm:text-lg">Tujuan Asesmen</h4>
                                <p class="mt-1">Asesmen psikologi ini diselenggarakan untuk memetakan potensi kerja, stabilitas emosi, gaya kepemimpinan, dan kecocokan profil kompetensi Anda dengan posisi yang dituju.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">1. Kerahasiaan Data & Hasil Asesmen</h4>
                                <p class="mt-1">Seluruh data yang tertulis dalam formulir ini dijaga kerahasiaannya dan hanya disampaikan kepada pihak pengambil keputusan / organisasi pemberi kerja sesuai mandat asesmen.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">2. Integritas Pengisian</h4>
                                <p class="mt-1">Saya menyatakan bahwa seluruh informasi dan jawaban yang saya berikan adalah benar adanya, jujur, serta mewakili kondisi diri saya yang sebenarnya.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Kartu Pertanyaan Persetujuan --}}
                    <div id="card_consent_agree" class="bg-white rounded-2xl border shadow-xs p-6 space-y-3 transition-colors"
                         :class="errors['consent_agree'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-base sm:text-lg font-bold text-slate-900 leading-snug">
                            Dengan menyetujui formulir ini, Anda menyatakan: <span class="text-rose-500">*</span>
                        </label>
                        <ul class="text-sm sm:text-base text-slate-600 leading-relaxed space-y-1.5 list-disc list-inside pl-1">
                            <li>Seluruh informasi yang diisikan adalah benar dan dapat dipertanggungjawabkan.</li>
                            <li>Menyetujui keikutsertaan dalam proses asesmen psikologi industri di UC PSC.</li>
                        </ul>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3 font-semibold text-base"
                                   :class="consent_agree === 'Setuju' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="consent_agree" value="Setuju" x-model="consent_agree" @change="delete errors['consent_agree']"
                                       class="w-4 h-4 text-purple-deep focus:ring-purple-deep">
                                <span>Setuju</span>
                            </label>
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3 font-semibold text-base"
                                   :class="consent_agree === 'Tidak Setuju' ? 'border-rose-400 bg-rose-50/70 text-rose-700' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="consent_agree" value="Tidak Setuju" x-model="consent_agree" @change="delete errors['consent_agree']"
                                       class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                                <span>Tidak Setuju</span>
                            </label>
                        </div>

                        <template x-if="errors['consent_agree']">
                            <p class="text-xs sm:text-sm text-rose-600 font-medium pt-1 flex items-center gap-1.5" x-text="errors['consent_agree']"></p>
                        </template>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <div></div>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 2 DARI 7: DATA DIRI & POSISI                           --}}
                {{-- ============================================================== --}}
                <div x-show="page === 2" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA DIRI & POSISI PEKERJAAN</h2>
                        <p class="text-sm text-slate-500 mt-1">Identitas pribadi dan posisi kerja yang Anda tuju.</p>
                    </div>

                    {{-- 1. Nama Lengkap --}}
                    <div id="card_nama_lengkap" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['nama_lengkap'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Lengkap Beserta Gelar <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" x-model="nama_lengkap" @input="delete errors['nama_lengkap']"
                               placeholder="Nama lengkap sesuai KTP" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['nama_lengkap']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['nama_lengkap']"></p>
                        </template>
                    </div>

                    {{-- 2. Jenis Kelamin --}}
                    <div id="card_jenis_kelamin" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['jenis_kelamin'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin === 'Laki-Laki' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="jenis_kelamin" value="Laki-Laki" x-model="jenis_kelamin" @change="delete errors['jenis_kelamin']" class="sr-only">
                                <span>Laki-Laki</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin === 'Perempuan' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="jenis_kelamin" value="Perempuan" x-model="jenis_kelamin" @change="delete errors['jenis_kelamin']" class="sr-only">
                                <span>Perempuan</span>
                            </label>
                        </div>
                        <template x-if="errors['jenis_kelamin']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['jenis_kelamin']"></p>
                        </template>
                    </div>

                    {{-- 3. Posisi yang Dituju & Pekerjaan Saat Ini --}}
                    <div id="card_posisi_dituju" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['posisi_dituju'] || errors['pekerjaan_saat_ini'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Posisi Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Posisi Jabatan yang Dituju / Dilamar: <span class="text-rose-500">*</span></span>
                                <input type="text" name="posisi_dituju" x-model="posisi_dituju" @input="delete errors['posisi_dituju']"
                                       placeholder="Contoh: Manager Operasional" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['posisi_dituju']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['posisi_dituju']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pekerjaan / Jabatan Saat Ini: <span class="text-rose-500">*</span></span>
                                <input type="text" name="pekerjaan_saat_ini" x-model="pekerjaan_saat_ini" @input="delete errors['pekerjaan_saat_ini']"
                                       placeholder="Contoh: Supervisor Logistik" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['pekerjaan_saat_ini']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['pekerjaan_saat_ini']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Tempat, Tanggal Lahir --}}
                    <div id="card_tempat_tanggal_lahir" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['tempat_tanggal_lahir'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Tempat & Tanggal Lahir <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="tempat_tanggal_lahir" x-model="tempat_tanggal_lahir" @input="delete errors['tempat_tanggal_lahir']"
                               placeholder="Contoh: Surabaya, 12 Agustus 1995" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['tempat_tanggal_lahir']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['tempat_tanggal_lahir']"></p>
                        </template>
                    </div>

                    {{-- 5. Urutan Kelahiran & Status Perkawinan --}}
                    <div id="card_urutan_kelahiran" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['urutan_kelahiran'] || errors['status_perkawinan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Keluarga & Status Perkawinan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Urutan Kelahiran:</span>
                                <input type="text" name="urutan_kelahiran" x-model="urutan_kelahiran" @input="delete errors['urutan_kelahiran']"
                                       placeholder="Contoh: Anak ke-1 dari 3 bersaudara" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['urutan_kelahiran']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['urutan_kelahiran']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Status Perkawinan:</span>
                                <input type="text" name="status_perkawinan" x-model="status_perkawinan" @input="delete errors['status_perkawinan']"
                                       placeholder="Contoh: Belum Menikah / Menikah" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['status_perkawinan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['status_perkawinan']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 6. Alamat Tempat Tinggal & Asal Kota --}}
                    <div id="card_alamat_sekarang" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['alamat_sekarang'] || errors['asal_kota'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Alamat Tinggal Sekarang & Asal Kota <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-3">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Alamat Tinggal Saat Ini:</span>
                                <textarea name="alamat_sekarang" x-model="alamat_sekarang" @input="delete errors['alamat_sekarang']" rows="2"
                                          placeholder="Alamat domisili lengkap beserta kota" 
                                          class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                                <template x-if="errors['alamat_sekarang']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['alamat_sekarang']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Asal Kota / Daerah:</span>
                                <input type="text" name="asal_kota" x-model="asal_kota" @input="delete errors['asal_kota']"
                                       placeholder="Contoh: Surabaya" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['asal_kota']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['asal_kota']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 7. No. Telp / HP & Email --}}
                    <div id="card_phone" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['phone'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Kontak Anda <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Nomor WhatsApp / HP Aktif: <span class="text-rose-500">*</span></span>
                                <input type="tel" name="phone" x-model="phone" @input="delete errors['phone']"
                                       placeholder="Contoh: 081234567890" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['phone']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['phone']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Alamat Email:</span>
                                <input type="email" name="email" x-model="email"
                                       placeholder="nama@email.com" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- 8. Agama, Suku & Pendidikan --}}
                    <div id="card_agama" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['agama'] || errors['suku_bangsa'] || errors['pendidikan_terakhir'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Latar Belakang & Pendidikan Terakhir <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Agama:</span>
                                <input type="text" name="agama" x-model="agama" @input="delete errors['agama']"
                                       placeholder="Contoh: Kristen, Islam, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['agama']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['agama']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Suku Bangsa:</span>
                                <input type="text" name="suku_bangsa" x-model="suku_bangsa" @input="delete errors['suku_bangsa']"
                                       placeholder="Contoh: Jawa, Tionghoa, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['suku_bangsa']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['suku_bangsa']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pendidikan Terakhir:</span>
                                <input type="text" name="pendidikan_terakhir" x-model="pendidikan_terakhir" @input="delete errors['pendidikan_terakhir']"
                                       placeholder="Contoh: S1 Teknik Industri" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['pendidikan_terakhir']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['pendidikan_terakhir']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 3 DARI 7: KUESIONER PSS-10                             --}}
                {{-- ============================================================== --}}
                <div x-show="page === 3" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">KUESIONER TINGKAT STRES KERJA (PSS-10)</h2>
                        <p class="text-sm text-slate-500 mt-1">Jawablah pertanyaan berikut berdasarkan perasaan dan pikiran Anda selama 1 bulan terakhir.</p>
                        <div class="mt-3 p-3 bg-purple-50/70 border border-purple-100 rounded-xl text-xs text-purple-900 flex flex-wrap gap-x-4 gap-y-1">
                            <span><strong>0</strong> = Tidak Pernah</span>
                            <span><strong>1</strong> = Hampir Tidak Pernah</span>
                            <span><strong>2</strong> = Kadang-Kadang</span>
                            <span><strong>3</strong> = Cukup Sering</span>
                            <span><strong>4</strong> = Sangat Sering</span>
                        </div>
                    </div>

                    @php
                        $pssQuestions = [
                            1 => 'Dalam sebulan terakhir, seberapa sering Anda merasa kesal karena sesuatu yang terjadi secara tidak terduga?',
                            2 => 'Dalam sebulan terakhir, seberapa sering Anda merasa tidak mampu mengendalikan hal-hal penting dalam hidup Anda?',
                            3 => 'Dalam sebulan terakhir, seberapa sering Anda merasa gugup dan stres?',
                            4 => 'Dalam sebulan terakhir, seberapa sering Anda merasa yakin dengan kemampuan Anda dalam menangani masalah pribadi?',
                            5 => 'Dalam sebulan terakhir, seberapa sering Anda merasa segala sesuatunya berjalan sesuai keinginan Anda?',
                            6 => 'Dalam sebulan terakhir, seberapa sering Anda merasa tidak dapat mengatasi semua hal yang harus Anda lakukan?',
                            7 => 'Dalam sebulan terakhir, seberapa sering Anda mampu mengendalikan kejengkelan dalam hidup Anda?',
                            8 => 'Dalam sebulan terakhir, seberapa sering Anda merasa mampu menguasai keadaan?',
                            9 => 'Dalam sebulan terakhir, seberapa sering Anda marah karena hal-hal yang terjadi di luar kendali Anda?',
                            10 => 'Dalam sebulan terakhir, seberapa sering Anda merasa kesulitan yang menumpuk begitu tinggi sehingga Anda tidak dapat mengatasinya?',
                        ];
                    @endphp

                    @foreach($pssQuestions as $idx => $question)
                        <div id="card_pss_{{ $idx }}" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                            <label class="block text-sm sm:text-base font-bold text-slate-900 leading-snug">
                                {{ $idx }}. {{ $question }}
                            </label>
                            <div class="grid grid-cols-5 gap-2 pt-1">
                                @for($val = 0; $val <= 4; $val++)
                                    <label class="p-2.5 rounded-xl border-2 transition cursor-pointer font-bold text-sm flex flex-col items-center justify-center gap-1 text-center"
                                           :class="pss_{{ $idx }} === '{{ $val }}' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                        <input type="radio" name="pss_{{ $idx }}" value="{{ $val }}" x-model="pss_{{ $idx }}" class="sr-only">
                                        <span class="text-base">{{ $val }}</span>
                                    </label>
                                @endfor
                            </div>
                        </div>
                    @endforeach

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 4 DARI 7: DATA KELUARGA                                --}}
                {{-- ============================================================== --}}
                <div x-show="page === 4" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA KELUARGA</h2>
                        <p class="text-sm text-slate-500 mt-1">Informasi orang tua dan anggota keluarga inti Anda.</p>
                    </div>

                    {{-- Ayah --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <h3 class="font-bold text-sm sm:text-base text-slate-900">Profil Ayah</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Nama Ayah:</span>
                                <input type="text" name="ayah_nama" x-model="ayah_nama" placeholder="Nama ayah" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Usia:</span>
                                <input type="text" name="ayah_usia" x-model="ayah_usia" placeholder="Contoh: 56 tahun" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pendidikan Terakhir:</span>
                                <input type="text" name="ayah_pendidikan" x-model="ayah_pendidikan" placeholder="Contoh: S1" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pekerjaan:</span>
                                <input type="text" name="ayah_pekerjaan" x-model="ayah_pekerjaan" placeholder="Contoh: Pensiunan / Wiraswasta" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Ibu --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <h3 class="font-bold text-sm sm:text-base text-slate-900">Profil Ibu</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Nama Ibu:</span>
                                <input type="text" name="ibu_nama" x-model="ibu_nama" placeholder="Nama ibu" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Usia:</span>
                                <input type="text" name="ibu_usia" x-model="ibu_usia" placeholder="Contoh: 52 tahun" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pendidikan Terakhir:</span>
                                <input type="text" name="ibu_pendidikan" x-model="ibu_pendidikan" placeholder="Contoh: S1" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pekerjaan:</span>
                                <input type="text" name="ibu_pekerjaan" x-model="ibu_pekerjaan" placeholder="Contoh: Ibu Rumah Tangga" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- Data Keluarga Inti (Pasangan / Anak / Saudara) --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Data Keluarga Inti</label>
                        <p class="text-xs text-slate-500">Tuliskan pasangan/anak (jika sudah berkeluarga) atau saudara kandung Anda.</p>
                        <textarea name="data_keluarga_inti" x-model="data_keluarga_inti" rows="3" placeholder="Contoh: Istri: Siti (30 th, Guru), Anak: Rizky (3 th)" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 5 DARI 7: PENDIDIKAN & PELATIHAN                       --}}
                {{-- ============================================================== --}}
                <div x-show="page === 5" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">RIWAYAT PENDIDIKAN & KEAHLIAN</h2>
                        <p class="text-sm text-slate-500 mt-1">Latar belakang pendidikan akademis serta sertifikasi profesional.</p>
                    </div>

                    {{-- Pendidikan Formal --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Riwayat Pendidikan Formal</label>
                        <textarea name="pendidikan_formal" x-model="pendidikan_formal" rows="3" placeholder="Universitas / Sekolah, Jurusan, IPK, Tahun Lulus..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Kursus & Keahlian Bahasa --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Pelatihan / Sertifikasi Profesional</label>
                            <textarea name="kursus_pelatihan" x-model="kursus_pelatihan" rows="2" placeholder="Sertifikasi keahlian, pelatihan industri, dll..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Penguasaan Bahasa & Keahlian Khusus</label>
                            <input type="text" name="keahlian_bahasa" x-model="keahlian_bahasa" placeholder="Contoh: Bahasa Inggris (TOEFL 580), SAP, Python" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        </div>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 6 DARI 7: PEKERJAAN & KARIR                            --}}
                {{-- ============================================================== --}}
                <div x-show="page === 6" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">RIWAYAT PEKERJAAN & KARIR</h2>
                        <p class="text-sm text-slate-500 mt-1">Pengalaman profesional, pencapaian kerja, serta gaya kepemimpinan Anda.</p>
                    </div>

                    {{-- Pengalaman Kerja --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Riwayat Pengalaman Kerja</label>
                        <p class="text-xs text-slate-500">Nama perusahaan, jabatan, periode kerja, dan tanggung jawab utama.</p>
                        <textarea name="pengalaman_kerja" x-model="pengalaman_kerja" rows="3" placeholder="Contoh: PT ABC (2021-2024, Assistant Manager, memimpin tim 10 orang)..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Prestasi Kerja & Kepemimpinan --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Prestasi Kerja Terbaik yang Pernah Dicapai</label>
                            <textarea name="prestasi_kerja" x-model="prestasi_kerja" rows="2" placeholder="Inisiatif, perbaikan efisiensi, atau capaian target penting..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Gaya Kepemimpinan & Kerjasama Tim</label>
                            <textarea name="gaya_kepemimpinan" x-model="gaya_kepemimpinan" rows="2" placeholder="Bagaimana Anda memimpin tim atau berkoordinasi dengan rekan kerja..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 7 DARI 7: DESKRIPSI DIRI & MOTIVASI                    --}}
                {{-- ============================================================== --}}
                <div x-show="page === 7" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DESKRIPSI DIRI & MOTIVASI KARIR</h2>
                        <p class="text-sm text-slate-500 mt-1">Langkah terakhir: motivasi melamar, evaluasi diri, dan kontak darurat.</p>
                    </div>

                    {{-- Motivasi & Alasan Melamar --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Alasan Melamar / Menempuh Asesmen Posisi Ini</label>
                        <textarea name="alasan_melamar" x-model="alasan_melamar" rows="3" placeholder="Mengapa Anda tertarik dengan posisi ini dan apa kontribusi yang ingin Anda berikan..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Kelebihan & Kekurangan Kerja --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Kelebihan Diri dalam Bekerja</label>
                            <textarea name="kelebihan_kerja" x-model="kelebihan_kerja" rows="2" placeholder="Kekuatan profesional dan integritas kerja Anda..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Area Pengembangan / Kekurangan Kerja yang Perlu Ditingkatkan</label>
                            <textarea name="kekurangan_kerja" x-model="kekurangan_kerja" rows="2" placeholder="Hal yang menjadi tantangan dan sedang Anda perbaiki..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        </div>
                    </div>

                    {{-- Harapan & Kontak Darurat --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Harapan Gaji & Fasilitas Kerja (Opsional)</label>
                            <input type="text" name="harapan_gaji_fasilitas" x-model="harapan_gaji_fasilitas" placeholder="Ekspektasi kompensasi dan fasilitas jika ada" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">Kontak Darurat (Emergency Contact)</label>
                            <input type="text" name="kontak_darurat" x-model="kontak_darurat" placeholder="Nama, Hubungan, dan Nomor HP (Contoh: Maya - Istri - 08123456789)" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        </div>
                    </div>

                    {{-- Navigation Bottom Bar (Final Submit) --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="submit" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Kirim Formulir Pendaftaran</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </div>
                </div>
            </form>

            {{-- Footer Branding Resmi --}}
            <footer class="pt-6 text-center text-xs text-slate-500 space-y-1">
                <p>Formulir Pendaftaran Resmi Universitas Ciputra Psychological Service Center (UC PSC).</p>
                <p>&copy; {{ date('Y') }} Universitas Ciputra Surabaya. Seluruh Hak Cipta Dilindungi.</p>
            </footer>

        </div>
    </main>

</body>
</html>
