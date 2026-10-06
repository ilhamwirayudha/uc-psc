<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF9FD]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Formulir Riwayat Hidup Konseling - Pernikahan — UC PSC</title>
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
        <div class="absolute top-96 -left-20 w-80 h-80 bg-red-100/20 rounded-full blur-3xl"></div>
    </div>

    {{-- Main Container (Flow Pengisian Runtut Ala GForm dengan Tema Desain UC PSC) --}}
    <main class="flex-1 py-6 sm:py-8 px-4" x-data="{
        page: 1,
        totalPages: 6,
        errors: {},

        // Safety Draft State
        draftSaved: false,
        hasRestoredDraft: false,
        formSubmitted: false,
        draftTimer: null,
        lastSavedTime: '',

        // Form Fields
        consent_agree: '{{ old('consent_agree', '') }}',
        
        // Halaman 2: Data Diri Anda
        nama_lengkap: '{{ old('nama_lengkap', '') }}',
        jenis_kelamin: '{{ old('jenis_kelamin', '') }}',
        tempat_lahir: '{{ old('tempat_lahir', '') }}',
        tanggal_lahir: '{{ old('tanggal_lahir', '') }}',
        urutan_kelahiran: '{{ old('urutan_kelahiran', '') }}',
        alamat: '{{ old('alamat', '') }}',
        phone: '{{ old('phone', '') }}',
        email: '{{ old('email', '') }}',
        agama: '{{ old('agama', '') }}',
        suku_bangsa: '{{ old('suku_bangsa', '') }}',
        pendidikan_terakhir: '{{ old('pendidikan_terakhir', '') }}',
        pekerjaan: '{{ old('pekerjaan', '') }}',
        pernikahan_ke: '{{ old('pernikahan_ke', '1') }}',
        jumlah_anak: '{{ old('jumlah_anak', '0') }}',
        alasan_konseling: '{{ old('alasan_konseling', '') }}',

        // Halaman 3: Data Pasangan
        nama_pasangan: '{{ old('nama_pasangan', '') }}',
        jenis_kelamin_pasangan: '{{ old('jenis_kelamin_pasangan', '') }}',
        tempat_lahir_pasangan: '{{ old('tempat_lahir_pasangan', '') }}',
        tanggal_lahir_pasangan: '{{ old('tanggal_lahir_pasangan', '') }}',
        urutan_kelahiran_pasangan: '{{ old('urutan_kelahiran_pasangan', '') }}',
        alamat_pasangan: '{{ old('alamat_pasangan', '') }}',
        phone_pasangan: '{{ old('phone_pasangan', '') }}',
        email_pasangan: '{{ old('email_pasangan', '') }}',
        agama_pasangan: '{{ old('agama_pasangan', '') }}',
        suku_bangsa_pasangan: '{{ old('suku_bangsa_pasangan', '') }}',
        pendidikan_terakhir_pasangan: '{{ old('pendidikan_terakhir_pasangan', '') }}',
        pekerjaan_pasangan: '{{ old('pekerjaan_pasangan', '') }}',
        pernikahan_pasangan_ke: '{{ old('pernikahan_pasangan_ke', '1') }}',
        jumlah_anak_pasangan: '{{ old('jumlah_anak_pasangan', '0') }}',

        // Halaman 4: Data Pernikahan
        tempat_tanggal_pernikahan: '{{ old('tempat_tanggal_pernikahan', '') }}',
        lama_pernikahan: '{{ old('lama_pernikahan', '') }}',
        lama_berkenalan: '{{ old('lama_berkenalan', '') }}',
        lama_berpacaran: '{{ old('lama_berpacaran', '') }}',

        // Halaman 5: Data Kondisi Pernikahan
        keluhan_utama: '{{ old('keluhan_utama', '') }}',
        kemungkinan_perbaikan: '{{ old('kemungkinan_perbaikan', '') }}',
        hal_ingin_ditingkatkan: '{{ old('hal_ingin_ditingkatkan', '') }}',
        kelebihan_peran: '{{ old('kelebihan_peran', '') }}',
        kekurangan_peran: '{{ old('kekurangan_peran', '') }}',
        riwayat_trauma: '{{ old('riwayat_trauma', '') }}',
        riwayat_kesehatan: '{{ old('riwayat_kesehatan', '') }}',

        // Halaman 6: Lain-Lain & Preferensi
        pernah_konseling: '{{ old('pernah_konseling', '') }}',
        nama_konselor: '{{ old('nama_konselor', '') }}',
        kontak_darurat: '{{ old('kontak_darurat', '') }}',
        sumber_info: '{{ old('sumber_info', '') }}',
        preferensi_konseling: '{{ old('preferensi_konseling', '') }}',

        init() {
            @if(!session('error') && !$errors->any())
                this.restoreDraft();
            @endif
        },

        saveDraft() {
            if (this.formSubmitted) return;
            const formEl = document.getElementById('pernikahanUCPSCForm');
            if (!formEl) return;

            const draft = {};
            const formData = new FormData(formEl);
            for (const [key, value] of formData.entries()) {
                if (key === '_token') continue;
                draft[key] = value;
            }

            const fieldKeys = [
                'consent_agree', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
                'urutan_kelahiran', 'alamat', 'phone', 'email', 'agama', 'suku_bangsa',
                'pendidikan_terakhir', 'pekerjaan', 'pernikahan_ke', 'jumlah_anak', 'alasan_konseling',
                'nama_pasangan', 'jenis_kelamin_pasangan', 'tempat_lahir_pasangan', 'tanggal_lahir_pasangan',
                'urutan_kelahiran_pasangan', 'alamat_pasangan', 'phone_pasangan', 'email_pasangan',
                'agama_pasangan', 'suku_bangsa_pasangan', 'pendidikan_terakhir_pasangan',
                'pekerjaan_pasangan', 'pernikahan_pasangan_ke', 'jumlah_anak_pasangan',
                'tempat_tanggal_pernikahan', 'lama_pernikahan', 'lama_berkenalan', 'lama_berpacaran',
                'keluhan_utama', 'kemungkinan_perbaikan', 'hal_ingin_ditingkatkan', 'kelebihan_peran',
                'kekurangan_peran', 'riwayat_trauma', 'riwayat_kesehatan', 'pernah_konseling',
                'nama_konselor', 'kontak_darurat', 'sumber_info', 'preferensi_konseling'
            ];

            fieldKeys.forEach(k => {
                if (this[k] !== undefined && this[k] !== null && this[k] !== '') {
                    draft[k] = this[k];
                }
            });

            draft['_saved_page'] = this.page;
            draft['_saved_at'] = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            try {
                localStorage.setItem('ucpsc_draft_pernikahan', JSON.stringify(draft));
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
                const raw = localStorage.getItem('ucpsc_draft_pernikahan');
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
                    const formEl = document.getElementById('pernikahanUCPSCForm');
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
                localStorage.removeItem('ucpsc_draft_pernikahan');
            } catch (e) {}
        },

        validateCurrentPage() {
            this.errors = {};

            if (this.page === 1) {
                if (!this.consent_agree) {
                    this.errors['consent_agree'] = 'Anda wajib menyetujui Lembar Persetujuan untuk melanjutkan.';
                } else if (this.consent_agree === 'Tidak Setuju') {
                    this.errors['consent_agree'] = 'Anda harus menyetujui Lembar Persetujuan untuk dapat menggunakan layanan konseling.';
                }
            } else if (this.page === 2) {
                if (!this.nama_lengkap.trim()) this.errors['nama_lengkap'] = 'Pertanyaan ini wajib diisi';
                if (!this.jenis_kelamin) this.errors['jenis_kelamin'] = 'Pertanyaan ini wajib diisi';
                if (!this.tempat_lahir.trim()) this.errors['tempat_lahir'] = 'Pertanyaan ini wajib diisi';
                if (!this.tanggal_lahir) this.errors['tanggal_lahir'] = 'Pertanyaan ini wajib diisi';
                if (!this.urutan_kelahiran.trim()) this.errors['urutan_kelahiran'] = 'Pertanyaan ini wajib diisi';
                if (!this.alamat.trim()) this.errors['alamat'] = 'Pertanyaan ini wajib diisi';
                if (!this.phone.trim()) this.errors['phone'] = 'Pertanyaan ini wajib diisi';
                if (!this.email.trim()) this.errors['email'] = 'Alamat email aktif wajib diisi';
                if (!this.agama.trim()) this.errors['agama'] = 'Pertanyaan ini wajib diisi';
                if (!this.suku_bangsa.trim()) this.errors['suku_bangsa'] = 'Pertanyaan ini wajib diisi';
                if (!this.pendidikan_terakhir.trim()) this.errors['pendidikan_terakhir'] = 'Pertanyaan ini wajib diisi';
                if (!this.pekerjaan.trim()) this.errors['pekerjaan'] = 'Pertanyaan ini wajib diisi';
                if (!this.pernikahan_ke.trim()) this.errors['pernikahan_ke'] = 'Pertanyaan ini wajib diisi';
                if (!this.alasan_konseling.trim()) this.errors['alasan_konseling'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 3) {
                if (!this.nama_pasangan.trim()) this.errors['nama_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.jenis_kelamin_pasangan) this.errors['jenis_kelamin_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.tempat_lahir_pasangan.trim()) this.errors['tempat_lahir_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.tanggal_lahir_pasangan) this.errors['tanggal_lahir_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.urutan_kelahiran_pasangan.trim()) this.errors['urutan_kelahiran_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.alamat_pasangan.trim()) this.errors['alamat_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.phone_pasangan.trim()) this.errors['phone_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.email_pasangan.trim()) this.errors['email_pasangan'] = 'Alamat email pasangan wajib diisi';
                if (!this.agama_pasangan.trim()) this.errors['agama_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.suku_bangsa_pasangan.trim()) this.errors['suku_bangsa_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.pendidikan_terakhir_pasangan.trim()) this.errors['pendidikan_terakhir_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.pekerjaan_pasangan.trim()) this.errors['pekerjaan_pasangan'] = 'Pertanyaan ini wajib diisi';
                if (!this.pernikahan_pasangan_ke.trim()) this.errors['pernikahan_pasangan_ke'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 4) {
                if (!this.tempat_tanggal_pernikahan.trim()) this.errors['tempat_tanggal_pernikahan'] = 'Pertanyaan ini wajib diisi';
                if (!this.lama_pernikahan.trim()) this.errors['lama_pernikahan'] = 'Pertanyaan ini wajib diisi';
                if (!this.lama_berkenalan.trim()) this.errors['lama_berkenalan'] = 'Pertanyaan ini wajib diisi';
                if (!this.lama_berpacaran.trim()) this.errors['lama_berpacaran'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 5) {
                if (!this.keluhan_utama.trim()) this.errors['keluhan_utama'] = 'Pertanyaan ini wajib diisi';
                if (!this.kemungkinan_perbaikan.trim()) this.errors['kemungkinan_perbaikan'] = 'Pertanyaan ini wajib diisi';
                if (!this.hal_ingin_ditingkatkan.trim()) this.errors['hal_ingin_ditingkatkan'] = 'Pertanyaan ini wajib diisi';
                if (!this.kelebihan_peran.trim()) this.errors['kelebihan_peran'] = 'Pertanyaan ini wajib diisi';
                if (!this.kekurangan_peran.trim()) this.errors['kekurangan_peran'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 6) {
                if (!this.pernah_konseling) this.errors['pernah_konseling'] = 'Pertanyaan ini wajib diisi';
                if (!this.kontak_darurat.trim()) this.errors['kontak_darurat'] = 'Pertanyaan ini wajib diisi';
                if (!this.sumber_info.trim()) this.errors['sumber_info'] = 'Pertanyaan ini wajib diisi';
                if (!this.preferensi_konseling) this.errors['preferensi_konseling'] = 'Pertanyaan ini wajib diisi';
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
            <form action="{{ route('public.client-form.pernikahan.store') }}" 
                  method="POST" 
                  id="pernikahanUCPSCForm" 
                  @input.debounce.400ms="saveDraft()" 
                  @change="saveDraft()"
                  @submit="if(!validateCurrentPage()){ $event.preventDefault(); } else { clearDraftOnSubmit(); }">
                @csrf

                {{-- ============================================================== --}}
                {{-- HALAMAN 1 DARI 6: INFORMED CONSENT                             --}}
                {{-- ============================================================== --}}
                <div x-show="page === 1" class="space-y-4">
                    {{-- Kartu Judul Banner Utama (Gradien Khas UC PSC) --}}
                    <div class="bg-gradient-to-r from-purple-deep via-[#4A2F85] to-purple-light rounded-2xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="relative z-10 space-y-2">
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Formulir Riwayat Hidup Konseling - Pernikahan</h1>
                            <p class="text-white/90 text-sm sm:text-base leading-relaxed max-w-2xl">
                                Formulir ini bertujuan untuk memahami dinamika relasi suami istri, latar belakang keluarga, riwayat perkawinan, serta persoalan hubungan demi menemukan solusi bersama yang konstruktif. Segala data terjamin kerahasiaannya.
                            </p>
                            <div class="pt-3 border-t border-white/20 text-xs sm:text-sm text-rose-200 font-medium">
                                * Menunjukkan pertanyaan yang wajib diisi
                            </div>
                        </div>
                    </div>

                    {{-- Kartu Teks Lembar Persetujuan --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-4">
                        <div class="pb-3 border-b border-slate-100">
                            <h2 class="text-lg sm:text-xl font-bold text-slate-900">INFORMED CONSENT</h2>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Lembar Persetujuan Layanan Konseling Pernikahan Universitas Ciputra PSC</p>
                        </div>

                        <div class="text-sm sm:text-base text-slate-700 leading-relaxed space-y-4 max-h-[380px] overflow-y-auto pr-3 custom-scrollbar border border-slate-100 rounded-xl p-4 sm:p-5 bg-slate-50/50">
                            <div>
                                <h4 class="font-bold text-purple-deep text-base sm:text-lg">Layanan Konseling Pernikahan</h4>
                                <p class="mt-1">Konseling pernikahan bertujuan untuk memfasilitasi komunikasi yang sehat, membantu penyelesaian konflik rumah tangga, dan memulihkan kehangatan hubungan suami istri. Konselor bertindak secara netral dan profesional.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">1. Prinsip Kerahasiaan & Keamanan</h4>
                                <p class="mt-1">Seluruh pembicaraan dan data yang tertulis dalam formulir ini dijaga kerahasiaannya dengan standar Kode Etik Psikologi Indonesia dan tidak dibagikan kepada pihak luar tanpa izin kedua belah pihak.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">2. Netralitas Konselor</h4>
                                <p class="mt-1">Konselor tidak berpihak kepada salah satu pasangan (suami maupun istri), melainkan berfokus pada keberlangsungan dan kesehatan ikatan pernikahan Anda berdua.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">3. Keterbukaan Pasangan</h4>
                                <p class="mt-1">Keterbukaan, kesediaan mendengarkan, serta komitmen kedua belah pihak memegang peranan krusial dalam pemulihan hubungan keluarga.</p>
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
                            <li>Telah membaca, memahami, dan menyetujui lembar persetujuan konseling di atas.</li>
                            <li>Bersedia memberikan data dan keterangan yang jujur demi proses konseling yang efektif.</li>
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
                {{-- HALAMAN 2 DARI 6: DATA DIRI ANDA                               --}}
                {{-- ============================================================== --}}
                <div x-show="page === 2" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA DIRI ANDA</h2>
                        <p class="text-sm text-slate-500 mt-1">Silakan lengkapi informasi identitas diri Anda di bawah ini.</p>
                    </div>

                    {{-- 1. Nama Lengkap --}}
                    <div id="card_nama_lengkap" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['nama_lengkap'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Lengkap Anda <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" x-model="nama_lengkap" @input="delete errors['nama_lengkap']"
                               placeholder="Nama lengkap sesuai identitas" 
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

                    {{-- 3. Tempat, Tanggal Lahir --}}
                    <div id="card_tempat_lahir" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['tempat_lahir'] || errors['tanggal_lahir'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Tempat, Tanggal Lahir <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Kota / Tempat Lahir:</span>
                                <input type="text" name="tempat_lahir" x-model="tempat_lahir" @input="delete errors['tempat_lahir']"
                                       placeholder="Contoh: Surabaya" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['tempat_lahir']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['tempat_lahir']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Tanggal Lahir:</span>
                                <input type="date" name="tanggal_lahir" x-model="tanggal_lahir" @change="delete errors['tanggal_lahir']"
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['tanggal_lahir']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['tanggal_lahir']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Urutan Kelahiran --}}
                    <div id="card_urutan_kelahiran" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['urutan_kelahiran'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Urutan Kelahiran <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="urutan_kelahiran" x-model="urutan_kelahiran" @input="delete errors['urutan_kelahiran']"
                               placeholder="Contoh: Anak ke-1 dari 3 bersaudara" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['urutan_kelahiran']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['urutan_kelahiran']"></p>
                        </template>
                    </div>

                    {{-- 5. Alamat Tempat Tinggal --}}
                    <div id="card_alamat" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['alamat'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Alamat Tempat Tinggal Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat" x-model="alamat" @input="delete errors['alamat']" rows="2"
                                  placeholder="Alamat domisili lengkap beserta kota" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['alamat']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['alamat']"></p>
                        </template>
                    </div>

                    {{-- 6. No. Telp / HP & Email --}}
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
                                <span class="text-xs text-slate-500 block mb-1">Alamat Email: <span class="text-rose-500">*</span></span>
                                <input type="email" name="email" x-model="email" @input="delete errors['email']"
                                       placeholder="nama@email.com" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['email']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['email']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 7. Agama & Suku Bangsa --}}
                    <div id="card_agama" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['agama'] || errors['suku_bangsa'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Agama & Suku Bangsa <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Agama / Keyakinan:</span>
                                <input type="text" name="agama" x-model="agama" @input="delete errors['agama']"
                                       placeholder="Contoh: Kristen, Islam, Katolik, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['agama']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['agama']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Suku Bangsa:</span>
                                <input type="text" name="suku_bangsa" x-model="suku_bangsa" @input="delete errors['suku_bangsa']"
                                       placeholder="Contoh: Jawa, Tionghoa, Batak, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['suku_bangsa']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['suku_bangsa']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 8. Pendidikan Terakhir & Pekerjaan --}}
                    <div id="card_pendidikan_terakhir" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['pendidikan_terakhir'] || errors['pekerjaan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pendidikan Terakhir & Pekerjaan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pendidikan Terakhir:</span>
                                <input type="text" name="pendidikan_terakhir" x-model="pendidikan_terakhir" @input="delete errors['pendidikan_terakhir']"
                                       placeholder="Contoh: S1 Manajemen, SMA, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['pendidikan_terakhir']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['pendidikan_terakhir']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pekerjaan Saat Ini:</span>
                                <input type="text" name="pekerjaan" x-model="pekerjaan" @input="delete errors['pekerjaan']"
                                       placeholder="Contoh: Karyawan Swasta, Profesional, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['pekerjaan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['pekerjaan']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 9. Pernikahan Ke- & Jumlah Anak --}}
                    <div id="card_pernikahan_ke" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['pernikahan_ke'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Riwayat Pernikahan Anda <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pernikahan Anda yang ke: <span class="text-rose-500">*</span></span>
                                <input type="text" name="pernikahan_ke" x-model="pernikahan_ke" @input="delete errors['pernikahan_ke']"
                                       placeholder="Contoh: 1" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['pernikahan_ke']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['pernikahan_ke']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Jumlah Anak Anda Saat Ini (opsional):</span>
                                <input type="text" name="jumlah_anak" x-model="jumlah_anak"
                                       placeholder="Contoh: 2" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            </div>
                        </div>
                    </div>

                    {{-- 10. Alasan Mengikuti Konseling --}}
                    <div id="card_alasan_konseling" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['alasan_konseling'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Alasan Mengikuti Konseling Pernikahan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alasan_konseling" x-model="alasan_konseling" @input="delete errors['alasan_konseling']" rows="3"
                                  placeholder="Ceritakan latar belakang utama mengapa Anda dan pasangan membutuhkan konseling saat ini..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['alasan_konseling']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['alasan_konseling']"></p>
                        </template>
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
                {{-- HALAMAN 3 DARI 6: DATA DIRI PASANGAN                           --}}
                {{-- ============================================================== --}}
                <div x-show="page === 3" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA DIRI PASANGAN (SUAMI / ISTRI)</h2>
                        <p class="text-sm text-slate-500 mt-1">Silakan lengkapi informasi identitas pasangan Anda di bawah ini.</p>
                    </div>

                    {{-- 1. Nama Pasangan --}}
                    <div id="card_nama_pasangan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['nama_pasangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Lengkap Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_pasangan" x-model="nama_pasangan" @input="delete errors['nama_pasangan']"
                               placeholder="Nama lengkap pasangan Anda" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['nama_pasangan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['nama_pasangan']"></p>
                        </template>
                    </div>

                    {{-- 2. Jenis Kelamin Pasangan --}}
                    <div id="card_jenis_kelamin_pasangan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['jenis_kelamin_pasangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Jenis Kelamin Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin_pasangan === 'Laki-Laki' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="jenis_kelamin_pasangan" value="Laki-Laki" x-model="jenis_kelamin_pasangan" @change="delete errors['jenis_kelamin_pasangan']" class="sr-only">
                                <span>Laki-Laki</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin_pasangan === 'Perempuan' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="jenis_kelamin_pasangan" value="Perempuan" x-model="jenis_kelamin_pasangan" @change="delete errors['jenis_kelamin_pasangan']" class="sr-only">
                                <span>Perempuan</span>
                            </label>
                        </div>
                        <template x-if="errors['jenis_kelamin_pasangan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['jenis_kelamin_pasangan']"></p>
                        </template>
                    </div>

                    {{-- 3. Tempat, Tanggal Lahir Pasangan --}}
                    <div id="card_tempat_lahir_pasangan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['tempat_lahir_pasangan'] || errors['tanggal_lahir_pasangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Tempat, Tanggal Lahir Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Kota / Tempat Lahir:</span>
                                <input type="text" name="tempat_lahir_pasangan" x-model="tempat_lahir_pasangan" @input="delete errors['tempat_lahir_pasangan']"
                                       placeholder="Contoh: Surabaya" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['tempat_lahir_pasangan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['tempat_lahir_pasangan']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Tanggal Lahir:</span>
                                <input type="date" name="tanggal_lahir_pasangan" x-model="tanggal_lahir_pasangan" @change="delete errors['tanggal_lahir_pasangan']"
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['tanggal_lahir_pasangan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['tanggal_lahir_pasangan']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Urutan Kelahiran Pasangan --}}
                    <div id="card_urutan_kelahiran_pasangan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['urutan_kelahiran_pasangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Urutan Kelahiran Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="urutan_kelahiran_pasangan" x-model="urutan_kelahiran_pasangan" @input="delete errors['urutan_kelahiran_pasangan']"
                               placeholder="Contoh: Anak ke-2 dari 4 bersaudara" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['urutan_kelahiran_pasangan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['urutan_kelahiran_pasangan']"></p>
                        </template>
                    </div>

                    {{-- 5. Alamat Tempat Tinggal Pasangan --}}
                    <div id="card_alamat_pasangan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['alamat_pasangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Alamat Tempat Tinggal Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat_pasangan" x-model="alamat_pasangan" @input="delete errors['alamat_pasangan']" rows="2"
                                  placeholder="Alamat domisili pasangan jika terpisah, atau tulis sama dengan Anda" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['alamat_pasangan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['alamat_pasangan']"></p>
                        </template>
                    </div>

                    {{-- 6. No. Telp / HP Pasangan --}}
                    <div id="card_phone_pasangan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['phone_pasangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Kontak Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Nomor WhatsApp / HP Aktif: <span class="text-rose-500">*</span></span>
                                <input type="tel" name="phone_pasangan" x-model="phone_pasangan" @input="delete errors['phone_pasangan']"
                                       placeholder="Contoh: 081234567890" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['phone_pasangan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['phone_pasangan']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Alamat Email: <span class="text-rose-500">*</span></span>
                                <input type="email" name="email_pasangan" x-model="email_pasangan" @input="delete errors['email_pasangan']"
                                       placeholder="email@pasangan.com" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['email_pasangan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['email_pasangan']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 7. Agama & Suku Pasangan --}}
                    <div id="card_agama_pasangan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['agama_pasangan'] || errors['suku_bangsa_pasangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Agama & Suku Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Agama / Keyakinan:</span>
                                <input type="text" name="agama_pasangan" x-model="agama_pasangan" @input="delete errors['agama_pasangan']"
                                       placeholder="Contoh: Kristen, Islam, Katolik, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['agama_pasangan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['agama_pasangan']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Suku Bangsa:</span>
                                <input type="text" name="suku_bangsa_pasangan" x-model="suku_bangsa_pasangan" @input="delete errors['suku_bangsa_pasangan']"
                                       placeholder="Contoh: Jawa, Tionghoa, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['suku_bangsa_pasangan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['suku_bangsa_pasangan']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 8. Pendidikan & Pekerjaan Pasangan --}}
                    <div id="card_pendidikan_terakhir_pasangan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['pendidikan_terakhir_pasangan'] || errors['pekerjaan_pasangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pendidikan & Pekerjaan Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pendidikan Terakhir:</span>
                                <input type="text" name="pendidikan_terakhir_pasangan" x-model="pendidikan_terakhir_pasangan" @input="delete errors['pendidikan_terakhir_pasangan']"
                                       placeholder="Contoh: S1 Akuntansi, SMA, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['pendidikan_terakhir_pasangan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['pendidikan_terakhir_pasangan']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pekerjaan:</span>
                                <input type="text" name="pekerjaan_pasangan" x-model="pekerjaan_pasangan" @input="delete errors['pekerjaan_pasangan']"
                                       placeholder="Contoh: Pegawai BUMN, Wirausaha, dll" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['pekerjaan_pasangan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['pekerjaan_pasangan']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 9. Riwayat Pernikahan Pasangan --}}
                    <div id="card_pernikahan_pasangan_ke" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['pernikahan_pasangan_ke'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Riwayat Pernikahan Pasangan <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Pernikahan Pasangan yang ke: <span class="text-rose-500">*</span></span>
                                <input type="text" name="pernikahan_pasangan_ke" x-model="pernikahan_pasangan_ke" @input="delete errors['pernikahan_pasangan_ke']"
                                       placeholder="Contoh: 1" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['pernikahan_pasangan_ke']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['pernikahan_pasangan_ke']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Jumlah Anak Pasangan (opsional):</span>
                                <input type="text" name="jumlah_anak_pasangan" x-model="jumlah_anak_pasangan"
                                       placeholder="Contoh: 2" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
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
                {{-- HALAMAN 4 DARI 6: DATA PERNIKAHAN & DINAMIKA                   --}}
                {{-- ============================================================== --}}
                <div x-show="page === 4" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA PERNIKAHAN & RIWAYAT HUBUNGAN</h2>
                        <p class="text-sm text-slate-500 mt-1">Informasi tanggal pernikahan, masa perkenalan, dan usia pernikahan Anda saat ini.</p>
                    </div>

                    {{-- 1. Tempat & Tanggal Pernikahan --}}
                    <div id="card_tempat_tanggal_pernikahan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['tempat_tanggal_pernikahan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Tempat & Tanggal Pernikahan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="tempat_tanggal_pernikahan" x-model="tempat_tanggal_pernikahan" @input="delete errors['tempat_tanggal_pernikahan']"
                               placeholder="Contoh: Surabaya, 10 Oktober 2018" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['tempat_tanggal_pernikahan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['tempat_tanggal_pernikahan']"></p>
                        </template>
                    </div>

                    {{-- 2. Lama Usia Pernikahan --}}
                    <div id="card_lama_pernikahan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['lama_pernikahan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Lama Usia Pernikahan Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="lama_pernikahan" x-model="lama_pernikahan" @input="delete errors['lama_pernikahan']"
                               placeholder="Contoh: 5 Tahun 6 Bulan" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['lama_pernikahan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['lama_pernikahan']"></p>
                        </template>
                    </div>

                    {{-- 3. Durasi Sebelum Menikah --}}
                    <div id="card_lama_berkenalan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['lama_berkenalan'] || errors['lama_berpacaran'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Durasi Sebelum Menikah <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Lama Waktu Berkenalan:</span>
                                <input type="text" name="lama_berkenalan" x-model="lama_berkenalan" @input="delete errors['lama_berkenalan']"
                                       placeholder="Contoh: 3 Tahun" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['lama_berkenalan']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['lama_berkenalan']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Lama Masa Pacaran / Menjajaki:</span>
                                <input type="text" name="lama_berpacaran" x-model="lama_berpacaran" @input="delete errors['lama_berpacaran']"
                                       placeholder="Contoh: 2 Tahun" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['lama_berpacaran']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['lama_berpacaran']"></p>
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
                {{-- HALAMAN 5 DARI 6: DATA KONDISI PERNIKAHAN                      --}}
                {{-- ============================================================== --}}
                <div x-show="page === 5" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">KONDISI & PERSOALAN PERNIKAHAN</h2>
                        <p class="text-sm text-slate-500 mt-1">Uraian mengenai dinamika konflik, keluhan hubungan, dan harapan pemulihan.</p>
                    </div>

                    {{-- 1. Keluhan Utama --}}
                    <div id="card_keluhan_utama" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['keluhan_utama'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Keluhan Utama dalam Pernikahan Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="keluhan_utama" x-model="keluhan_utama" @input="delete errors['keluhan_utama']" rows="3"
                                  placeholder="Ceritakan konflik atau tantangan relasi apa yang paling berat dihadapi saat ini..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['keluhan_utama']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['keluhan_utama']"></p>
                        </template>
                    </div>

                    {{-- 2. Kemungkinan Perbaikan Hubungan --}}
                    <div id="card_kemungkinan_perbaikan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['kemungkinan_perbaikan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Upaya yang Pernah Dilakukan untuk Memperbaiki Hubungan <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500">Jelaskan upaya atau langkah yang pernah dicoba oleh Anda atau pasangan sebelumnya.</p>
                        <textarea name="kemungkinan_perbaikan" x-model="kemungkinan_perbaikan" @input="delete errors['kemungkinan_perbaikan']" rows="2"
                                  placeholder="Contoh: Pernah berdiskusi keluarga, membaca buku pernikahan, mencoba mediasi..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['kemungkinan_perbaikan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['kemungkinan_perbaikan']"></p>
                        </template>
                    </div>

                    {{-- 3. Hal-hal yang Ingin Ditingkatkan --}}
                    <div id="card_hal_ingin_ditingkatkan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['hal_ingin_ditingkatkan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Hal-Hal yang Ingin Ditingkatkan / Dipulihkan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="hal_ingin_ditingkatkan" x-model="hal_ingin_ditingkatkan" @input="delete errors['hal_ingin_ditingkatkan']" rows="3"
                                  placeholder="Contoh: Kepercayaan, keintiman emosional, komunikasi tanpa emosi berlebih, pola asuh anak bersama..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['hal_ingin_ditingkatkan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['hal_ingin_ditingkatkan']"></p>
                        </template>
                    </div>

                    {{-- 4. Kelebihan & Kekurangan Peran --}}
                    <div id="card_kelebihan_peran" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['kelebihan_peran'] || errors['kekurangan_peran'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Refleksi Peran Anda sebagai Suami / Istri <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-3">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Kelebihan / Kontribusi Positif Anda dalam Rumah Tangga:</span>
                                <textarea name="kelebihan_peran" x-model="kelebihan_peran" @input="delete errors['kelebihan_peran']" rows="2"
                                          placeholder="Kelebihan atau hal positif yang Anda berikan bagi pasangan dan keluarga..." 
                                          class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                                <template x-if="errors['kelebihan_peran']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['kelebihan_peran']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Kelemahan / Hal yang Menurut Anda Perlu Diperbaiki dari Diri Anda Sendiri:</span>
                                <textarea name="kekurangan_peran" x-model="kekurangan_peran" @input="delete errors['kekurangan_peran']" rows="2"
                                          placeholder="Sikap, cara komunikasi, atau reaksi Anda yang mungkin memicu ketegangan..." 
                                          class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                                <template x-if="errors['kekurangan_peran']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['kekurangan_peran']"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Riwayat Khusus --}}
                    <div id="card_riwayat_trauma" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-3">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Riwayat Khusus (Trauma / Kesehatan)
                        </label>
                        <p class="text-xs text-slate-500">Opsional. Informasi berharga untuk memahami konteks emosional Anda.</p>
                        <div class="space-y-3">
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Riwayat Peristiwa Traumatis (Jika Ada):</span>
                                <textarea name="riwayat_trauma" x-model="riwayat_trauma" rows="2"
                                          placeholder="Pengalaman berat di masa lalu atau dalam pernikahan..." 
                                          class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block mb-1">Riwayat Kesehatan Medis Penting (Jika Ada):</span>
                                <textarea name="riwayat_kesehatan" x-model="riwayat_kesehatan" rows="2"
                                          placeholder="Kondisi fisik, keluhan penyakit kronis, atau perawatan saat ini..." 
                                          class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
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
                {{-- HALAMAN 6 DARI 6: LAIN-LAIN & PREFERENSI                       --}}
                {{-- ============================================================== --}}
                <div x-show="page === 6" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">LAIN-LAIN & PREFERENSI LAYANAN</h2>
                        <p class="text-sm text-slate-500 mt-1">Langkah terakhir untuk menentukan preferensi sesi dan kontak darurat keluarga.</p>
                    </div>

                    {{-- 1. Pernah Konseling Sebelumnya --}}
                    <div id="card_pernah_konseling" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['pernah_konseling'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Apakah Pernah Mengikuti Konseling Pernikahan Sebelumnya? <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="pernah_konseling === 'Ya' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="pernah_konseling" value="Ya" x-model="pernah_konseling" @change="delete errors['pernah_konseling']" class="sr-only">
                                <span>Ya, Pernah</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="pernah_konseling === 'Tidak' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="pernah_konseling" value="Tidak" x-model="pernah_konseling" @change="delete errors['pernah_konseling']" class="sr-only">
                                <span>Belum Pernah</span>
                            </label>
                        </div>
                        <template x-if="errors['pernah_konseling']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pernah_konseling']"></p>
                        </template>

                        <div x-show="pernah_konseling === 'Ya'" class="pt-2">
                            <span class="text-xs text-slate-500 block mb-1">Jika pernah, sebutkan nama konselor / lembaga sebelumnya (opsional):</span>
                            <input type="text" name="nama_konselor" x-model="nama_konselor"
                                   placeholder="Contoh: Bapak Hendra, M.Psi" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        </div>
                    </div>

                    {{-- 2. Kontak Darurat --}}
                    <div id="card_kontak_darurat" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['kontak_darurat'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Kontak Darurat (Emergency Contact) <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500">Keluarga atau kerabat terpercaya di luar pasangan yang dapat dihubungi saat situasi darurat.</p>
                        <input type="text" name="kontak_darurat" x-model="kontak_darurat" @input="delete errors['kontak_darurat']"
                               placeholder="Nama, Hubungan Relasi, dan Nomor HP (Contoh: Hendro - Adik Kandung - 08123456789)" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['kontak_darurat']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['kontak_darurat']"></p>
                        </template>
                    </div>

                    {{-- 3. Sumber Informasi --}}
                    <div id="card_sumber_info" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['sumber_info'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Dari Mana Anda Mengetahui Layanan UC PSC? <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="sumber_info" x-model="sumber_info" @input="delete errors['sumber_info']"
                               placeholder="Contoh: Media Sosial, Rekan Kerja, Website, dll" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['sumber_info']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['sumber_info']"></p>
                        </template>
                    </div>

                    {{-- 4. Preferensi Proses Konseling --}}
                    <div id="card_preferensi_konseling" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['preferensi_konseling'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Preferensi Proses Konseling <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <label class="p-4 rounded-xl border-2 transition cursor-pointer flex items-start gap-3 hover:bg-slate-50"
                                   :class="preferensi_konseling === 'Online melalui Zoom' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="preferensi_konseling" value="Online melalui Zoom" x-model="preferensi_konseling" @change="delete errors['preferensi_konseling']" class="mt-1 text-purple-deep focus:ring-purple-deep">
                                <div>
                                    <p class="font-bold text-sm">Online melalui Zoom</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Sesi tatap maya interaktif melalui Zoom Meeting.</p>
                                </div>
                            </label>
                            <label class="p-4 rounded-xl border-2 transition cursor-pointer flex items-start gap-3 hover:bg-slate-50"
                                   :class="preferensi_konseling === 'Offline di UCPSC' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="preferensi_konseling" value="Offline di UCPSC" x-model="preferensi_konseling" @change="delete errors['preferensi_konseling']" class="mt-1 text-purple-deep focus:ring-purple-deep">
                                <div>
                                    <p class="font-bold text-sm">Offline di UCPSC</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Tatap muka langsung di Ruang Konseling UC PSC Kampus Universitas Ciputra Surabaya.</p>
                                </div>
                            </label>
                        </div>
                        <template x-if="errors['preferensi_konseling']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['preferensi_konseling']"></p>
                        </template>
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
