<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF9FD]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Formulir Riwayat Hidup Konseling - Dewasa — UC PSC</title>
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

        /* Typography Sizing */
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
        <div class="absolute top-96 -left-20 w-80 h-80 bg-orange-100/25 rounded-full blur-3xl"></div>
    </div>

    {{-- Main Container --}}
    <main class="flex-1 py-6 sm:py-8 px-4" x-data="{
        page: 1,
        totalPages: 5,
        errors: {},

        // Safety Draft State
        draftSaved: false,
        hasRestoredDraft: false,
        formSubmitted: false,
        draftTimer: null,
        lastSavedTime: '',

        // Form Fields
        consent_agree: '{{ old('consent_agree', '') }}',
        nama_lengkap: '{{ old('nama_lengkap', '') }}',
        jenis_kelamin: '{{ old('jenis_kelamin', '') }}',
        tempat_lahir: '{{ old('tempat_lahir', '') }}',
        tanggal_lahir: '{{ old('tanggal_lahir', '') }}',
        urutan_kelahiran: '{{ old('urutan_kelahiran', '') }}',
        alamat: '{{ old('alamat', '') }}',
        phone: '{{ old('phone', '') }}',
        agama: '{{ old('agama', '') }}',
        suku_bangsa: '{{ old('suku_bangsa', '') }}',
        pendidikan_terakhir: '{{ old('pendidikan_terakhir', '') }}',
        pekerjaan: '{{ old('pekerjaan', '') }}',
        hobi: '{{ old('hobi', '') }}',
        alasan_konseling: '{{ old('alasan_konseling', '') }}',

        kondisi_saat_ini: '{{ old('kondisi_saat_ini', '') }}',
        pikiran_negatif: '{{ old('pikiran_negatif', '') }}',
        hal_ingin_ditingkatkan: '{{ old('hal_ingin_ditingkatkan', '') }}',
        karakter_kepribadian: '{{ old('karakter_kepribadian', '') }}',
        ketakutan_phobia: '{{ old('ketakutan_phobia', '') }}',
        jam_tidur: '{{ old('jam_tidur', '') }}',
        riwayat_trauma: '{{ old('riwayat_trauma', '') }}',
        riwayat_kesehatan: '{{ old('riwayat_kesehatan', '') }}',

        relasi_ayah: '{{ old('relasi_ayah', '') }}',
        relasi_ibu: '{{ old('relasi_ibu', '') }}',
        relasi_saudara: '{{ old('relasi_saudara', '') }}',
        relasi_pasangan: '{{ old('relasi_pasangan', '') }}',
        relasi_anak: '{{ old('relasi_anak', '') }}',

        pernah_konseling: '{{ old('pernah_konseling', '') }}',
        nama_konselor: '{{ old('nama_konselor', '') }}',
        kontak_darurat: '{{ old('kontak_darurat', '') }}',
        sumber_info: '{{ old('sumber_info', '') }}',
        preferensi_konseling: '{{ old('preferensi_konseling', '') }}',

        init() {
            @if(!$errors->any())
                this.restoreDraft();
            @endif
        },

        saveDraft() {
            if (this.formSubmitted) return;
            const formEl = document.getElementById('dewasaUCPSCForm');
            if (!formEl) return;

            const draft = {};
            const formData = new FormData(formEl);
            for (const [key, value] of formData.entries()) {
                if (key === '_token') continue;
                draft[key] = value;
            }

            const fieldKeys = [
                'consent_agree', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
                'urutan_kelahiran', 'alamat', 'phone', 'agama', 'suku_bangsa', 'pendidikan_terakhir',
                'pekerjaan', 'hobi', 'alasan_konseling', 'kondisi_saat_ini', 'pikiran_negatif',
                'hal_ingin_ditingkatkan', 'karakter_kepribadian', 'ketakutan_phobia', 'jam_tidur',
                'riwayat_trauma', 'riwayat_kesehatan', 'relasi_ayah', 'relasi_ibu', 'relasi_saudara',
                'relasi_pasangan', 'relasi_anak', 'pernah_konseling', 'nama_konselor', 'kontak_darurat',
                'sumber_info', 'preferensi_konseling'
            ];

            fieldKeys.forEach(k => {
                if (this[k] !== undefined && this[k] !== null && this[k] !== '') {
                    draft[k] = this[k];
                }
            });

            draft['_saved_page'] = this.page;
            draft['_saved_at'] = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            try {
                localStorage.setItem('ucpsc_draft_dewasa', JSON.stringify(draft));
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
                const raw = localStorage.getItem('ucpsc_draft_dewasa');
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
                    const formEl = document.getElementById('dewasaUCPSCForm');
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

        resetDraft() {
            if (confirm('Apakah Anda yakin ingin mengosongkan draf dan mengulang pengisian formulir dari awal?')) {
                try {
                    localStorage.removeItem('ucpsc_draft_dewasa');
                } catch(e) {}
                window.location.reload();
            }
        },

        clearDraftOnSubmit() {
            this.formSubmitted = true;
            try {
                localStorage.removeItem('ucpsc_draft_dewasa');
            } catch(e) {}
        },

        validateCurrentPage() {
            this.errors = {};

            if (this.page === 1) {
                if (!this.consent_agree || this.consent_agree !== 'Setuju') {
                    this.errors['consent_agree'] = 'Anda wajib memilih opsi \'Setuju\' pada Lembar Persetujuan untuk dapat melanjutkan.';
                }
            } else if (this.page === 2) {
                if (!this.nama_lengkap.trim()) this.errors['nama_lengkap'] = 'Pertanyaan ini wajib diisi';
                if (!this.jenis_kelamin) this.errors['jenis_kelamin'] = 'Pertanyaan ini wajib diisi';
                if (!this.tempat_lahir.trim()) this.errors['tempat_lahir'] = 'Tempat lahir wajib diisi';
                if (!this.tanggal_lahir) this.errors['tanggal_lahir'] = 'Tanggal lahir wajib diisi';
                if (!this.urutan_kelahiran.trim()) this.errors['urutan_kelahiran'] = 'Pertanyaan ini wajib diisi';
                if (!this.alamat.trim()) this.errors['alamat'] = 'Pertanyaan ini wajib diisi';
                if (!this.phone.trim()) this.errors['phone'] = 'Pertanyaan ini wajib diisi';
                if (!this.agama) this.errors['agama'] = 'Pertanyaan ini wajib diisi';
                if (!this.suku_bangsa.trim()) this.errors['suku_bangsa'] = 'Pertanyaan ini wajib diisi';
                if (!this.pendidikan_terakhir) this.errors['pendidikan_terakhir'] = 'Pertanyaan ini wajib diisi';
                if (!this.hobi.trim()) this.errors['hobi'] = 'Pertanyaan ini wajib diisi';
                if (!this.alasan_konseling.trim()) this.errors['alasan_konseling'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 3) {
                if (!this.kondisi_saat_ini.trim()) this.errors['kondisi_saat_ini'] = 'Pertanyaan ini wajib diisi';
                if (!this.pikiran_negatif.trim()) this.errors['pikiran_negatif'] = 'Pertanyaan ini wajib diisi';
                if (!this.hal_ingin_ditingkatkan.trim()) this.errors['hal_ingin_ditingkatkan'] = 'Pertanyaan ini wajib diisi';
                if (!this.karakter_kepribadian.trim()) this.errors['karakter_kepribadian'] = 'Pertanyaan ini wajib diisi';
                if (!this.jam_tidur.trim()) this.errors['jam_tidur'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 4) {
                if (!this.relasi_ayah.trim()) this.errors['relasi_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.relasi_ibu.trim()) this.errors['relasi_ibu'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 5) {
                if (!this.pernah_konseling) this.errors['pernah_konseling'] = 'Pertanyaan ini wajib diisi';
                if (!this.kontak_darurat.trim()) this.errors['kontak_darurat'] = 'Pertanyaan ini wajib diisi';
                if (!this.sumber_info) this.errors['sumber_info'] = 'Pertanyaan ini wajib diisi';
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
            <form action="{{ route('public.client-form.dewasa.store') }}" 
                  method="POST" 
                  id="dewasaUCPSCForm" 
                  @input.debounce.400ms="saveDraft()" 
                  @change="saveDraft()"
                  @submit="if(!validateCurrentPage()){ $event.preventDefault(); } else { clearDraftOnSubmit(); }">
                @csrf

                {{-- ============================================================== --}}
                {{-- HALAMAN 1 DARI 5: INFORMED CONSENT                             --}}
                {{-- ============================================================== --}}
                <div x-show="page === 1" class="space-y-4">
                    {{-- Kartu Judul Banner Utama (Gradien Khas UC PSC) --}}
                    <div class="bg-gradient-to-r from-purple-deep via-[#4A2F85] to-purple-light rounded-2xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-44 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="relative z-10 space-y-2">
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Formulir Riwayat Hidup Konseling - Dewasa</h1>
                            <p class="text-white/90 text-sm sm:text-base leading-relaxed max-w-2xl">
                                Formulir Riwayat Hidup ini digunakan untuk memberikan informasi lengkap tentang diri Anda. Segala data yang Anda berikan dalam Formulir Riwayat Hidup ini akan terjamin kerahasiaannya dan hanya akan digunakan untuk keperluan konseling semata.
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
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Lembar Persetujuan Layanan Konseling Universitas Ciputra PSC</p>
                        </div>

                        <div class="text-sm sm:text-base text-slate-700 leading-relaxed space-y-4 max-h-[380px] overflow-y-auto pr-3 custom-scrollbar border border-slate-100 rounded-xl p-4 sm:p-5 bg-slate-50/50">
                            <div>
                                <h4 class="font-bold text-purple-deep text-base sm:text-lg">Layanan Konseling</h4>
                                <p class="mt-1">Layanan konseling bertujuan untuk membantu Anda mengatasi permasalahan yang sedang dihadapi, memahami diri sendiri, dan mencapai tujuan yang diinginkan dalam kehidupan pribadi, profesional, atau sosial. Namun, hasil dari konseling dapat bervariasi, tergantung pada partisipasi aktif Anda.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">1. Tujuan Konseling</h4>
                                <p class="mt-1">Layanan konseling bertujuan untuk membantu Anda mengatasi permasalahan yang sedang dihadapi, memahami diri sendiri, dan mencapai tujuan yang diinginkan dalam kehidupan pribadi, profesional, atau sosial. Namun, hasil dari konseling dapat bervariasi, tergantung pada partisipasi aktif Anda.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">2. Proses Konseling</h4>
                                <ul class="list-disc list-inside space-y-1.5 mt-1 text-slate-600">
                                    <li>Konseling dilakukan dalam sesi-sesi terjadwal yang difasilitasi oleh konselor yang telah berlisensi atau terlatih.</li>
                                    <li>Jumlah sesi dan durasi konseling akan disesuaikan dengan kebutuhan Anda.</li>
                                    <li>Anda dapat mengakhiri proses konseling kapan saja dengan menginformasikan kepada konselor.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">3. Kerahasiaan</h4>
                                <p class="mt-1 text-slate-600">Informasi yang Anda berikan selama proses konseling akan dijaga kerahasiaannya sesuai dengan kode etik psikologi dan peraturan hukum yang berlaku. Kerahasiaan dapat dibuka dalam situasi tertentu, seperti:</p>
                                <ul class="list-disc list-inside pl-3 space-y-1 mt-1 text-slate-600">
                                    <li>Adanya ancaman serius terhadap keselamatan Anda atau orang lain.</li>
                                    <li>Permintaan resmi dari pengadilan.</li>
                                    <li>Persetujuan tertulis dari Anda untuk berbagi informasi tertentu.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">4. Hak dan Kewajiban Klien</h4>
                                <p class="font-semibold text-slate-800 mt-1.5">Hak Anda:</p>
                                <ul class="list-disc list-inside pl-3 text-slate-600 space-y-1">
                                    <li>Mendapatkan layanan yang aman dan profesional.</li>
                                    <li>Bertanya atau meminta penjelasan lebih lanjut mengenai proses konseling.</li>
                                    <li>Menghentikan konseling kapan saja jika merasa tidak nyaman.</li>
                                </ul>
                                <p class="font-semibold text-slate-800 mt-2.5">Kewajiban Anda:</p>
                                <ul class="list-disc list-inside pl-3 text-slate-600 space-y-1">
                                    <li>Memberikan informasi yang jujur dan relevan untuk membantu proses konseling.</li>
                                    <li>Menghormati waktu sesi yang telah dijadwalkan.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">5. Risiko dan Batasan Konseling</h4>
                                <ul class="list-disc list-inside space-y-1 mt-1 text-slate-600">
                                    <li>Konseling bukanlah solusi instan untuk semua masalah.</li>
                                    <li>Prosesnya mungkin menimbulkan ketidaknyamanan emosional saat membahas masalah yang sensitif.</li>
                                    <li>Tidak ada jaminan bahwa setiap tujuan yang diinginkan akan tercapai sepenuhnya.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">6. Biaya Layanan</h4>
                                <p class="mt-1 text-slate-600">Biaya layanan konseling telah dijelaskan sebelumnya dan Anda setuju untuk memenuhi kewajiban pembayaran sesuai perjanjian.</p>
                            </div>

                            <div>
                                <h4 class="font-bold text-slate-900 text-base sm:text-lg">7. Hubungan Profesional</h4>
                                <ul class="list-disc list-inside space-y-1 mt-1 text-slate-600">
                                    <li>Hubungan antara Anda dan konselor bersifat profesional, bukan hubungan pribadi.</li>
                                    <li>Interaksi Anda dengan konselor terbatas pada konteks konseling.</li>
                                </ul>
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
                            <li>Telah membaca, memahami, dan menerima poin-poin yang tertera di atas.</li>
                            <li>Menyetujui untuk berpartisipasi dalam layanan konseling dengan sadar dan sukarela.</li>
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
                {{-- HALAMAN 2 DARI 5: DATA DIRI                                    --}}
                {{-- ============================================================== --}}
                <div x-show="page === 2" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA DIRI</h2>
                        <p class="text-sm text-slate-500 mt-1">Silakan lengkapi informasi identitas Anda secara urut di bawah ini.</p>
                    </div>

                    {{-- 1. Nama Lengkap --}}
                    <div id="card_nama_lengkap" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['nama_lengkap'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Lengkap (Beserta Gelar Jika Ada) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" x-model="nama_lengkap" @input="delete errors['nama_lengkap']"
                               placeholder="Nama lengkap beserta gelar" 
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
                                <span class="block text-xs text-slate-500 mb-1">Tempat Lahir</span>
                                <input type="text" name="tempat_lahir" x-model="tempat_lahir" @input="delete errors['tempat_lahir']"
                                       placeholder="Kota tempat lahir" 
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                                <template x-if="errors['tempat_lahir']">
                                    <p class="text-xs text-rose-600 font-medium mt-1" x-text="errors['tempat_lahir']"></p>
                                </template>
                            </div>
                            <div>
                                <span class="block text-xs text-slate-500 mb-1">Tanggal Lahir</span>
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
                        <p class="text-xs text-slate-500">Contoh format: Anak ke-2 dari 3 bersaudara</p>
                        <input type="text" name="urutan_kelahiran" x-model="urutan_kelahiran" @input="delete errors['urutan_kelahiran']"
                               placeholder="Jawaban Anda" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['urutan_kelahiran']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['urutan_kelahiran']"></p>
                        </template>
                    </div>

                    {{-- 5. Alamat Sekarang --}}
                    <div id="card_alamat" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['alamat'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Alamat Sekarang <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat" x-model="alamat" @input="delete errors['alamat']" rows="3"
                                  placeholder="Alamat tempat tinggal lengkap saat ini" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['alamat']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['alamat']"></p>
                        </template>
                    </div>

                    {{-- 6. No. Telp/HP --}}
                    <div id="card_phone" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['phone'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            No. Telp/HP (WhatsApp) <span class="text-rose-500">*</span>
                        </label>
                        <input type="tel" name="phone" x-model="phone" @input="delete errors['phone']"
                               placeholder="Contoh: 081234567890" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['phone']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['phone']"></p>
                        </template>
                    </div>

                    {{-- 7. Agama/Kepercayaan --}}
                    <div id="card_agama" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['agama'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Agama/Kepercayaan <span class="text-rose-500">*</span>
                        </label>
                        <select name="agama" x-model="agama" @change="delete errors['agama']"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            <option value="">Pilih</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen Protestan">Kristen Protestan</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <template x-if="errors['agama']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['agama']"></p>
                        </template>
                    </div>

                    {{-- 8. Suku Bangsa --}}
                    <div id="card_suku_bangsa" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['suku_bangsa'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Suku Bangsa <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="suku_bangsa" x-model="suku_bangsa" @input="delete errors['suku_bangsa']"
                               placeholder="Jawaban Anda (misal: Jawa, Tionghoa, Batak...)" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['suku_bangsa']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['suku_bangsa']"></p>
                        </template>
                    </div>

                    {{-- 9. Pendidikan Terakhir --}}
                    <div id="card_pendidikan_terakhir" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['pendidikan_terakhir'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pendidikan Terakhir <span class="text-rose-500">*</span>
                        </label>
                        <select name="pendidikan_terakhir" x-model="pendidikan_terakhir" @change="delete errors['pendidikan_terakhir']"
                                class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                            <option value="">Pilih</option>
                            <option value="SMA / SMK">SMA / SMK</option>
                            <option value="Diploma / D3">Diploma / D3</option>
                            <option value="Sarjana / S1">Sarjana / S1</option>
                            <option value="Magister / S2">Magister / S2</option>
                            <option value="Doktor / S3">Doktor / S3</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <template x-if="errors['pendidikan_terakhir']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pendidikan_terakhir']"></p>
                        </template>
                    </div>

                    {{-- 10. Pekerjaan Saat Ini --}}
                    <div id="card_pekerjaan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition border-[#EDE1FA]">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pekerjaan Saat Ini <span class="text-xs font-normal text-slate-500">(Opsional)</span>
                        </label>
                        <input type="text" name="pekerjaan" x-model="pekerjaan" @input="delete errors['pekerjaan']"
                               placeholder="Jawaban Anda" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['pekerjaan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pekerjaan']"></p>
                        </template>
                    </div>

                    {{-- 11. Hobi --}}
                    <div id="card_hobi" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['hobi'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Hobi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="hobi" x-model="hobi" @input="delete errors['hobi']"
                               placeholder="Jawaban Anda" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['hobi']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['hobi']"></p>
                        </template>
                    </div>

                    {{-- 12. Alasan Anda Ingin Melakukan Konseling --}}
                    <div id="card_alasan_konseling" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['alasan_konseling'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Alasan Anda Ingin Melakukan Konseling <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alasan_konseling" x-model="alasan_konseling" @input="delete errors['alasan_konseling']" rows="4"
                                  placeholder="Jawaban Anda" 
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
                {{-- HALAMAN 3 DARI 5: DATA KONDISI PSIKOLOGIS                      --}}
                {{-- ============================================================== --}}
                <div x-show="page === 3" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA KONDISI PSIKOLOGIS</h2>
                        <p class="text-sm text-slate-500 mt-1">Informasi mengenai situasi emosi dan psikologis yang sedang Anda rasakan.</p>
                    </div>

                    {{-- 1. Gambaran Kondisi Anda Saat Ini terhadap Permasalahan --}}
                    <div id="card_kondisi_saat_ini" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['kondisi_saat_ini'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambaran Kondisi Anda Saat Ini terhadap Permasalahan yang Anda Rasakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="kondisi_saat_ini" x-model="kondisi_saat_ini" @input="delete errors['kondisi_saat_ini']" rows="4"
                                  placeholder="Jawaban Anda" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['kondisi_saat_ini']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['kondisi_saat_ini']"></p>
                        </template>
                    </div>

                    {{-- 2. Pikiran-Pikiran Negatif yang Sering Muncul Akhir-Akhir Ini --}}
                    <div id="card_pikiran_negatif" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['pikiran_negatif'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pikiran-Pikiran Negatif yang Sering Muncul Akhir-Akhir Ini <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="pikiran_negatif" x-model="pikiran_negatif" @input="delete errors['pikiran_negatif']" rows="4"
                                  placeholder="Jawaban Anda" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['pikiran_negatif']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pikiran_negatif']"></p>
                        </template>
                    </div>

                    {{-- 3. Hal-Hal Dalam Hidup Anda yang Ingin Anda Tingkatkan --}}
                    <div id="card_hal_ingin_ditingkatkan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['hal_ingin_ditingkatkan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Hal-Hal Dalam Hidup Anda yang Ingin Anda Tingkatkan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="hal_ingin_ditingkatkan" x-model="hal_ingin_ditingkatkan" @input="delete errors['hal_ingin_ditingkatkan']" rows="3"
                                  placeholder="Jawaban Anda" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['hal_ingin_ditingkatkan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['hal_ingin_ditingkatkan']"></p>
                        </template>
                    </div>

                    {{-- 4. Karakter Kepribadian --}}
                    <div id="card_karakter_kepribadian" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['karakter_kepribadian'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Karakter Kepribadian <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="karakter_kepribadian" x-model="karakter_kepribadian" @input="delete errors['karakter_kepribadian']" rows="3"
                                  placeholder="Jawaban Anda" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['karakter_kepribadian']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['karakter_kepribadian']"></p>
                        </template>
                    </div>

                    {{-- 5. Ketakutan/Phobia yang Dimiliki --}}
                    <div id="card_ketakutan_phobia" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Ketakutan/Phobia yang Dimiliki
                        </label>
                        <input type="text" name="ketakutan_phobia" x-model="ketakutan_phobia"
                               placeholder="Jawaban Anda (opsional)" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                    </div>

                    {{-- 6. Rata-Rata Jam Tidur per Malam --}}
                    <div id="card_jam_tidur" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['jam_tidur'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Rata-Rata Jam Tidur per Malam <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="jam_tidur" x-model="jam_tidur" @input="delete errors['jam_tidur']"
                               placeholder="Contoh: 6 jam" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['jam_tidur']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['jam_tidur']"></p>
                        </template>
                    </div>

                    {{-- 7. Riwayat Hidup yang Menimbulkan Trauma --}}
                    <div id="card_riwayat_trauma" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Riwayat Hidup yang Menimbulkan Trauma
                        </label>
                        <textarea name="riwayat_trauma" x-model="riwayat_trauma" rows="3"
                                  placeholder="Jawaban Anda (opsional)" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- 8. Riwayat Kesehatan --}}
                    <div id="card_riwayat_kesehatan" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Riwayat Kesehatan
                        </label>
                        <textarea name="riwayat_kesehatan" x-model="riwayat_kesehatan" rows="3"
                                  placeholder="Jawaban Anda (opsional)" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
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
                {{-- HALAMAN 4 DARI 5: GAMBARAN RELASI                              --}}
                {{-- ============================================================== --}}
                <div x-show="page === 4" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">GAMBARAN RELASI</h2>
                        <p class="text-sm text-slate-500 mt-1">Ceritakan kualitas dan dinamika hubungan Anda dengan anggota keluarga dan orang terdekat.</p>
                    </div>

                    {{-- 1. Gambarkan Relasi Anda dengan Ayah Anda --}}
                    <div id="card_relasi_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['relasi_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anda dengan Ayah Anda <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="relasi_ayah" x-model="relasi_ayah" @input="delete errors['relasi_ayah']" rows="3"
                                  placeholder="Jawaban Anda" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['relasi_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['relasi_ayah']"></p>
                        </template>
                    </div>

                    {{-- 2. Gambarkan Relasi Anda dengan Ibu Anda --}}
                    <div id="card_relasi_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['relasi_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anda dengan Ibu Anda <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="relasi_ibu" x-model="relasi_ibu" @input="delete errors['relasi_ibu']" rows="3"
                                  placeholder="Jawaban Anda" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['relasi_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['relasi_ibu']"></p>
                        </template>
                    </div>

                    {{-- 3. Gambarkan Relasi Anda dengan Saudara Kandung Anda --}}
                    <div id="card_relasi_saudara" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anda dengan Saudara Kandung Anda
                        </label>
                        <textarea name="relasi_saudara" x-model="relasi_saudara" rows="3"
                                  placeholder="Jawaban Anda (bisa ditulis '-' jika anak tunggal)" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- 4. Gambarkan Relasi Anda dengan Pasangan Anda --}}
                    <div id="card_relasi_pasangan" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anda dengan Pasangan Anda
                        </label>
                        <textarea name="relasi_pasangan" x-model="relasi_pasangan" rows="3"
                                  placeholder="Jawaban Anda (opsional jika belum memiliki pasangan)" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- 5. Gambarkan Relasi Anda dengan Anak Anda --}}
                    <div id="card_relasi_anak" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anda dengan Anak Anda
                        </label>
                        <textarea name="relasi_anak" x-model="relasi_anak" rows="3"
                                  placeholder="Jawaban Anda (opsional jika belum memiliki anak)" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
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
                {{-- HALAMAN 5 DARI 5: LAIN-LAIN & PREFERENSI LAYANAN               --}}
                {{-- ============================================================== --}}
                <div x-show="page === 5" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">LAIN-LAIN</h2>
                        <p class="text-sm text-slate-500 mt-1">Informasi pendukung dan metode pelaksanaan sesi konseling.</p>
                    </div>

                    {{-- 1. Apakah Anda Pernah Mengikuti Sesi Konseling Sebelumnya? --}}
                    <div id="card_pernah_konseling" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['pernah_konseling'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Apakah Anda Pernah Mengikuti Sesi Konseling Sebelumnya? <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="pernah_konseling === 'Ya' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="pernah_konseling" value="Ya" x-model="pernah_konseling" @change="delete errors['pernah_konseling']" class="sr-only">
                                <span>Ya</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="pernah_konseling === 'Tidak' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="pernah_konseling" value="Tidak" x-model="pernah_konseling" @change="delete errors['pernah_konseling']" class="sr-only">
                                <span>Tidak</span>
                            </label>
                        </div>
                        <template x-if="errors['pernah_konseling']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pernah_konseling']"></p>
                        </template>
                    </div>

                    {{-- 2. Nama Konselor yang Menangani --}}
                    <div id="card_nama_konselor" x-show="pernah_konseling === 'Ya'" x-cloak class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Konselor yang Menangani
                        </label>
                        <input type="text" name="nama_konselor" x-model="nama_konselor"
                               placeholder="Nama konselor atau lembaga sebelumnya" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                    </div>

                    {{-- 3. Kontak Darurat yang Bisa Kami Hubungi Terkait dengan Kondisi Anda --}}
                    <div id="card_kontak_darurat" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['kontak_darurat'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Kontak Darurat yang Bisa Kami Hubungi Terkait dengan Kondisi Anda <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500">Tuliskan nama, hubungan (misal: Orang Tua / Saudara / Pasangan), serta nomor telepon aktif.</p>
                        <input type="text" name="kontak_darurat" x-model="kontak_darurat" @input="delete errors['kontak_darurat']"
                               placeholder="Contoh: Siti Rahma (Kakak) - 081234567890" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['kontak_darurat']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['kontak_darurat']"></p>
                        </template>
                    </div>

                    {{-- 4. Bagaimana Anda Mengetahui Tentang UCPSC? --}}
                    <div id="card_sumber_info" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['sumber_info'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Bagaimana Anda Mengetahui Tentang UCPSC? <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-2.5">
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3"
                                   :class="sumber_info === 'Referensi dari Kerabat' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="sumber_info" value="Referensi dari Kerabat" x-model="sumber_info" @change="delete errors['sumber_info']" class="w-4 h-4 text-purple-deep focus:ring-purple-deep">
                                <span class="text-sm">Referensi dari Kerabat</span>
                            </label>
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3"
                                   :class="sumber_info === 'Media Sosial' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="sumber_info" value="Media Sosial" x-model="sumber_info" @change="delete errors['sumber_info']" class="w-4 h-4 text-purple-deep focus:ring-purple-deep">
                                <span class="text-sm">Media Sosial</span>
                            </label>
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3"
                                   :class="sumber_info === 'Website' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="sumber_info" value="Website" x-model="sumber_info" @change="delete errors['sumber_info']" class="w-4 h-4 text-purple-deep focus:ring-purple-deep">
                                <span class="text-sm">Website</span>
                            </label>
                        </div>
                        <template x-if="errors['sumber_info']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['sumber_info']"></p>
                        </template>
                    </div>

                    {{-- 5. Preferensi Proses Konseling --}}
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
