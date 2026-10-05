<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF9FD]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Formulir Riwayat Hidup Konseling - Anak — UC PSC</title>
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
            font-size: 100% !important; /* Kembalikan ukuran root ke 100% (16px) agar teks form tidak mengecil di HP */
        }
        body { 
            font-family: 'Plus Jakarta Sans', 'Montserrat', sans-serif; 
            background-color: #FAF9FD;
            font-size: 16px;
        }
        [x-cloak] { display: none !important; }

        /* Typography Sizing for Online Form (Mobile & Desktop) */
        /* Judul Pertanyaan */
        label.block.font-bold,
        label.font-bold {
            font-size: 0.9375rem !important; /* ~15px di HP */
            line-height: 1.4 !important;
        }
        @media (min-width: 640px) {
            label.block.font-bold,
            label.font-bold {
                font-size: 1rem !important; /* 16px di Desktop */
            }
        }

        /* Deskripsi / Petunjuk Pertanyaan */
        p.text-xs.text-slate-500,
        span.text-xs.text-slate-500,
        .text-xs.text-slate-500,
        p.text-slate-500 {
            font-size: 0.8125rem !important; /* 13px */
            line-height: 1.45 !important;
        }

        /* Form Inputs (Text, Date, Textarea, Select) */
        input[type="text"],
        input[type="date"],
        input[type="tel"],
        input[type="email"],
        input[type="number"],
        textarea,
        select {
            font-size: 0.9rem !important; /* ~14.4px */
            line-height: 1.45 !important;
        }

        /* Opsi Pilihan (Radio & Checkbox Card) */
        label.cursor-pointer,
        label.cursor-pointer span,
        label.cursor-pointer div {
            font-size: 0.875rem !important; /* 14px */
        }

        /* Tombol Navigasi */
        button[type="submit"],
        button[type="button"] {
            font-size: 0.875rem !important;
        }

        /* Pesan Error Validasi */
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
        status_pernikahan_ortu: '{{ old('status_pernikahan_ortu', '') }}',
        alasan_konseling: '{{ old('alasan_konseling', '') }}',

        kondisi_anak_saat_ini: '{{ old('kondisi_anak_saat_ini', '') }}',
        hal_ingin_ditingkatkan: '{{ old('hal_ingin_ditingkatkan', '') }}',
        karakter_anak: '{{ old('karakter_anak', '') }}',
        prestasi_anak: '{{ old('prestasi_anak', '') }}',
        ketakutan_phobia: '{{ old('ketakutan_phobia', '') }}',
        riwayat_trauma: '{{ old('riwayat_trauma', '') }}',
        riwayat_kesehatan: '{{ old('riwayat_kesehatan', '') }}',

        relasi_ayah: '{{ old('relasi_ayah', '') }}',
        relasi_ibu: '{{ old('relasi_ibu', '') }}',
        relasi_saudara: '{{ old('relasi_saudara', '') }}',
        relasi_teman: '{{ old('relasi_teman', '') }}',

        nama_ayah: '{{ old('nama_ayah', '') }}',
        jenis_kelamin_ayah: '{{ old('jenis_kelamin_ayah', 'Laki-Laki') }}',
        usia_ayah: '{{ old('usia_ayah', '') }}',
        pendidikan_ayah: '{{ old('pendidikan_ayah', '') }}',
        pekerjaan_ayah: '{{ old('pekerjaan_ayah', '') }}',
        agama_ayah: '{{ old('agama_ayah', '') }}',
        urutan_kelahiran_ayah: '{{ old('urutan_kelahiran_ayah', '') }}',
        alamat_ayah: '{{ old('alamat_ayah', '') }}',
        phone_ayah: '{{ old('phone_ayah', '') }}',
        pernikahan_ayah_ke: '{{ old('pernikahan_ayah_ke', '1') }}',
        jumlah_anak_ayah: '{{ old('jumlah_anak_ayah', '') }}',
        status_nikah_ayah: '{{ old('status_nikah_ayah', '') }}',

        nama_ibu: '{{ old('nama_ibu', '') }}',
        jenis_kelamin_ibu: '{{ old('jenis_kelamin_ibu', 'Perempuan') }}',
        usia_ibu: '{{ old('usia_ibu', '') }}',
        pendidikan_ibu: '{{ old('pendidikan_ibu', '') }}',
        pekerjaan_ibu: '{{ old('pekerjaan_ibu', '') }}',
        agama_ibu: '{{ old('agama_ibu', '') }}',
        urutan_kelahiran_ibu: '{{ old('urutan_kelahiran_ibu', '') }}',
        alamat_ibu: '{{ old('alamat_ibu', '') }}',
        phone_ibu: '{{ old('phone_ibu', '') }}',
        pernikahan_ibu_ke: '{{ old('pernikahan_ibu_ke', '1') }}',
        jumlah_anak_ibu: '{{ old('jumlah_anak_ibu', '') }}',
        status_nikah_ibu: '{{ old('status_nikah_ibu', '') }}',

        pernah_konseling: '{{ old('pernah_konseling', 'Tidak') }}',
        kontak_darurat: '{{ old('kontak_darurat', '') }}',
        sumber_info: '{{ old('sumber_info', '') }}',
        preferensi_konseling: '{{ old('preferensi_konseling', '') }}',

        init() {
            this.restoreDraft();

            // Auto-save ketika pengguna berpindah tab, membuka WA, layar mati, atau meminimalkan browser
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden' && !this.formSubmitted) {
                    this.saveDraft();
                }
            });

            // Auto-save sebelum browser direfresh atau ditutup
            window.addEventListener('beforeunload', () => {
                if (!this.formSubmitted) {
                    this.saveDraft();
                }
            });
        },

        saveDraft() {
            if (this.formSubmitted) return;
            const formEl = document.getElementById('anakUCPSCForm');
            if (!formEl) return;

            const draft = {};
            const formData = new FormData(formEl);
            for (const [key, value] of formData.entries()) {
                if (key === '_token') continue;
                draft[key] = value;
            }

            // Simpan seluruh state Alpine secara eksplisit
            const fieldKeys = [
                'consent_agree', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
                'urutan_kelahiran', 'alamat', 'phone', 'agama', 'suku_bangsa', 'pendidikan_terakhir',
                'status_pernikahan_ortu', 'alasan_konseling', 'kondisi_anak_saat_ini', 'hal_ingin_ditingkatkan',
                'karakter_anak', 'prestasi_anak', 'ketakutan_phobia', 'riwayat_trauma', 'riwayat_kesehatan',
                'relasi_ayah', 'relasi_ibu', 'relasi_saudara', 'relasi_teman', 'nama_ayah', 'jenis_kelamin_ayah',
                'usia_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'agama_ayah', 'urutan_kelahiran_ayah',
                'alamat_ayah', 'phone_ayah', 'pernikahan_ayah_ke', 'jumlah_anak_ayah', 'status_nikah_ayah',
                'nama_ibu', 'jenis_kelamin_ibu', 'usia_ibu', 'pendidikan_ibu', 'pekerjaan_ibu',
                'agama_ibu', 'urutan_kelahiran_ibu', 'alamat_ibu', 'phone_ibu', 'pernikahan_ibu_ke',
                'jumlah_anak_ibu', 'status_nikah_ibu', 'pernah_konseling', 'kontak_darurat',
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
                localStorage.setItem('ucpsc_draft_anak', JSON.stringify(draft));
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
                const raw = localStorage.getItem('ucpsc_draft_anak');
                if (!raw) return;
                const draft = JSON.parse(raw);
                if (!draft || typeof draft !== 'object') return;

                const keys = Object.keys(draft).filter(k => !k.startsWith('_'));
                if (keys.length === 0) return;

                // Pulihkan data ke state Alpine
                keys.forEach(key => {
                    if (this.hasOwnProperty(key)) {
                        if (draft[key] !== undefined && draft[key] !== null) {
                            this[key] = draft[key];
                        }
                    }
                });

                // Sinkronkan ke elemen DOM yang tidak menggunakan x-model
                this.$nextTick(() => {
                    const formEl = document.getElementById('anakUCPSCForm');
                    if (formEl) {
                        keys.forEach(key => {
                            const field = formEl.elements[key];
                            if (field && !(field instanceof RadioNodeList) && field.type !== 'radio') {
                                field.value = draft[key];
                            }
                        });
                    }
                });

                // Kembalikan ke halaman langkah terakhir yang sedang diisi
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
                    localStorage.removeItem('ucpsc_draft_anak');
                } catch(e) {}
                window.location.reload();
            }
        },

        clearDraftOnSubmit() {
            this.formSubmitted = true;
            try {
                localStorage.removeItem('ucpsc_draft_anak');
            } catch(e) {}
        },

        copyAlamatToAyah() {
            if (this.alamat) {
                this.alamat_ayah = this.alamat;
                delete this.errors['alamat_ayah'];
                this.saveDraft();
            }
        },

        copyAlamatToIbu() {
            if (this.alamat) {
                this.alamat_ibu = this.alamat;
                delete this.errors['alamat_ibu'];
                this.saveDraft();
            }
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
                if (!this.tempat_lahir.trim()) this.errors['tempat_lahir'] = 'Pertanyaan ini wajib diisi';
                if (!this.tanggal_lahir) this.errors['tanggal_lahir'] = 'Pertanyaan ini wajib diisi';
                if (!this.urutan_kelahiran.trim()) this.errors['urutan_kelahiran'] = 'Pertanyaan ini wajib diisi';
                if (!this.alamat.trim()) this.errors['alamat'] = 'Pertanyaan ini wajib diisi';
                if (!this.phone.trim()) this.errors['phone'] = 'Pertanyaan ini wajib diisi';
                if (!this.agama.trim()) this.errors['agama'] = 'Pertanyaan ini wajib diisi';
                if (!this.suku_bangsa.trim()) this.errors['suku_bangsa'] = 'Pertanyaan ini wajib diisi';
                if (!this.pendidikan_terakhir.trim()) this.errors['pendidikan_terakhir'] = 'Pertanyaan ini wajib diisi';
                if (!this.status_pernikahan_ortu) this.errors['status_pernikahan_ortu'] = 'Pertanyaan ini wajib diisi';
                if (!this.alasan_konseling.trim()) this.errors['alasan_konseling'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 3) {
                if (!this.kondisi_anak_saat_ini.trim()) this.errors['kondisi_anak_saat_ini'] = 'Pertanyaan ini wajib diisi';
                if (!this.hal_ingin_ditingkatkan.trim()) this.errors['hal_ingin_ditingkatkan'] = 'Pertanyaan ini wajib diisi';
                if (!this.karakter_anak.trim()) this.errors['karakter_anak'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 4) {
                if (!this.relasi_ayah.trim()) this.errors['relasi_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.relasi_ibu.trim()) this.errors['relasi_ibu'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 5) {
                if (!this.nama_ayah.trim()) this.errors['nama_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.jenis_kelamin_ayah) this.errors['jenis_kelamin_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.usia_ayah.trim()) this.errors['usia_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.pendidikan_ayah.trim()) this.errors['pendidikan_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.pekerjaan_ayah.trim()) this.errors['pekerjaan_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.agama_ayah.trim()) this.errors['agama_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.urutan_kelahiran_ayah.trim()) this.errors['urutan_kelahiran_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.alamat_ayah.trim()) this.errors['alamat_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.phone_ayah.trim()) this.errors['phone_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.pernikahan_ayah_ke.trim()) this.errors['pernikahan_ayah_ke'] = 'Pertanyaan ini wajib diisi';
                if (!this.jumlah_anak_ayah.trim()) this.errors['jumlah_anak_ayah'] = 'Pertanyaan ini wajib diisi';
                if (!this.status_nikah_ayah) this.errors['status_nikah_ayah'] = 'Pertanyaan ini wajib diisi';

                if (!this.nama_ibu.trim()) this.errors['nama_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.jenis_kelamin_ibu) this.errors['jenis_kelamin_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.usia_ibu.trim()) this.errors['usia_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.pendidikan_ibu.trim()) this.errors['pendidikan_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.pekerjaan_ibu.trim()) this.errors['pekerjaan_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.agama_ibu.trim()) this.errors['agama_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.urutan_kelahiran_ibu.trim()) this.errors['urutan_kelahiran_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.alamat_ibu.trim()) this.errors['alamat_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.phone_ibu.trim()) this.errors['phone_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.pernikahan_ibu_ke.trim()) this.errors['pernikahan_ibu_ke'] = 'Pertanyaan ini wajib diisi';
                if (!this.jumlah_anak_ibu.trim()) this.errors['jumlah_anak_ibu'] = 'Pertanyaan ini wajib diisi';
                if (!this.status_nikah_ibu) this.errors['status_nikah_ibu'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 6) {
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
            <form action="{{ route('public.client-form.anak.store') }}" 
                  method="POST" 
                  id="anakUCPSCForm" 
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

                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Formulir Riwayat Hidup Konseling - Anak</h1>
                            <p class="text-white/90 text-sm sm:text-base leading-relaxed max-w-2xl">
                                Formulir Riwayat Hidup ini digunakan untuk memberikan informasi lengkap tentang diri anak Anda. Segala data yang Anda berikan dalam Formulir Riwayat Hidup ini akan terjamin kerahasiaannya dan hanya akan digunakan untuk keperluan konseling semata.
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
                        <button type="button" @click="nextPage()" class="px-7 py-3.5 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-base shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 2 DARI 6: DATA DIRI ANAK                               --}}
                {{-- ============================================================== --}}
                <div x-show="page === 2" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA DIRI ANAK</h2>
                        <p class="text-sm text-slate-500 mt-1">Silakan lengkapi informasi identitas anak secara urut di bawah ini.</p>
                    </div>

                    {{-- 1. Nama Lengkap --}}
                    <div id="card_nama_lengkap" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['nama_lengkap'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" x-model="nama_lengkap" @input="delete errors['nama_lengkap']"
                               placeholder="Nama lengkap anak" 
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
                        <p class="text-xs text-slate-500">Anak ke _____ dari _____ bersaudara</p>
                        <input type="text" name="urutan_kelahiran" x-model="urutan_kelahiran" @input="delete errors['urutan_kelahiran']"
                               placeholder="Contoh: Anak ke 1 dari 2 bersaudara" 
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
                        <p class="text-xs text-slate-500">Alamat domisili tempat tinggal anak saat ini</p>
                        <textarea name="alamat" x-model="alamat" rows="2" @input="delete errors['alamat']"
                                  placeholder="Tuliskan alamat lengkap..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['alamat']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['alamat']"></p>
                        </template>
                    </div>

                    {{-- 6. No. Telp/HP --}}
                    <div id="card_phone" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['phone'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            No. Telp/HP <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500">Nomor HP anak atau kontak utama WhatsApp</p>
                        <input type="text" name="phone" x-model="phone" @input="delete errors['phone']"
                               placeholder="08xxxxxxxxxx" 
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
                        <input type="text" name="agama" x-model="agama" @input="delete errors['agama']"
                               placeholder="Contoh: Kristen, Islam, Katolik, dll." 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
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
                               placeholder="Contoh: Jawa, Tionghoa, dll." 
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
                        <input type="text" name="pendidikan_terakhir" x-model="pendidikan_terakhir" @input="delete errors['pendidikan_terakhir']"
                               placeholder="Contoh: SD Kelas 4 / TK B" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['pendidikan_terakhir']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pendidikan_terakhir']"></p>
                        </template>
                    </div>

                    {{-- 10. Status Pernikahan Orang Tua --}}
                    <div id="card_status_pernikahan_ortu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['status_pernikahan_ortu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Status Pernikahan Orang Tua <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="status_pernikahan_ortu === 'Menikah' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="status_pernikahan_ortu" value="Menikah" x-model="status_pernikahan_ortu" @change="delete errors['status_pernikahan_ortu']" class="sr-only">
                                <span>Menikah</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="status_pernikahan_ortu === 'Bercerai' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="status_pernikahan_ortu" value="Bercerai" x-model="status_pernikahan_ortu" @change="delete errors['status_pernikahan_ortu']" class="sr-only">
                                <span>Bercerai</span>
                            </label>
                        </div>
                        <template x-if="errors['status_pernikahan_ortu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['status_pernikahan_ortu']"></p>
                        </template>
                    </div>

                    {{-- 11. Alasan Anak Anda Membutuhkan Konseling --}}
                    <div id="card_alasan_konseling" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['alasan_konseling'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Alasan Anak Anda Membutuhkan Konseling <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alasan_konseling" x-model="alasan_konseling" rows="3" @input="delete errors['alasan_konseling']"
                                  placeholder="Jelaskan alasan atau keluhan utama..." 
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
                        <button type="button" @click="nextPage()" class="px-7 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 3 DARI 6: DATA KONDISI PSIKOLOGIS ANAK                 --}}
                {{-- ============================================================== --}}
                <div x-show="page === 3" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA KONDISI PSIKOLOGIS ANAK</h2>
                        <p class="text-sm text-slate-500 mt-1">Bantu psikolog memahami kepribadian dan kondisi psikologis anak.</p>
                    </div>

                    {{-- 12. Gambaran Kondisi Anak Saat Ini --}}
                    <div id="card_kondisi_anak_saat_ini" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['kondisi_anak_saat_ini'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambaran Kondisi Anak Anda Saat Ini terhadap Permasalahan yang Anak Anda Alami <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="kondisi_anak_saat_ini" x-model="kondisi_anak_saat_ini" rows="3" @input="delete errors['kondisi_anak_saat_ini']"
                                  placeholder="Ceritakan perubahan sikap, perilaku, atau keluhan anak..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['kondisi_anak_saat_ini']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['kondisi_anak_saat_ini']"></p>
                        </template>
                    </div>

                    {{-- 13. Hal-Hal Dalam Hidup yang Ingin Ditingkatkan --}}
                    <div id="card_hal_ingin_ditingkatkan" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['hal_ingin_ditingkatkan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Hal-Hal Dalam Hidup Anak Anda yang Ingin Anda Tingkatkan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="hal_ingin_ditingkatkan" x-model="hal_ingin_ditingkatkan" rows="2" @input="delete errors['hal_ingin_ditingkatkan']"
                                  placeholder="Contoh: Fokus belajar, pengelolaan emosi, kepercayaan diri..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                        <template x-if="errors['hal_ingin_ditingkatkan']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['hal_ingin_ditingkatkan']"></p>
                        </template>
                    </div>

                    {{-- 14. Karakter Kepribadian Anak --}}
                    <div id="card_karakter_anak" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['karakter_anak'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Karakter Kepribadian Anak <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="karakter_anak" x-model="karakter_anak" @input="delete errors['karakter_anak']"
                               placeholder="Contoh: Pemalu, ceria, aktif, keras kepala..." 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['karakter_anak']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['karakter_anak']"></p>
                        </template>
                    </div>

                    {{-- 15. Prestasi Anak --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Prestasi Anak
                        </label>
                        <input type="text" name="prestasi_anak" x-model="prestasi_anak"
                               placeholder="Prestasi akademik maupun non-akademik (bisa dikosongkan jika tidak ada)" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                    </div>

                    {{-- 16. Ketakutan/Phobia yang Dimiliki Anak --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Ketakutan/Phobia yang Dimiliki Anak
                        </label>
                        <input type="text" name="ketakutan_phobia" x-model="ketakutan_phobia"
                               placeholder="Contoh: Takut gelap, suara petir/keras..." 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                    </div>

                    {{-- 17. Riwayat Hidup Anak yang Menimbulkan Trauma --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Riwayat Hidup Anak yang Menimbulkan Trauma
                        </label>
                        <textarea name="riwayat_trauma" x-model="riwayat_trauma" rows="2" placeholder="Peristiwa masa lalu yang membekas (contoh: pernah dibully, kecelakaan, perpisahan...)" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- 18. Riwayat Kesehatan Anak --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Riwayat Kesehatan Anak
                        </label>
                        <textarea name="riwayat_kesehatan" x-model="riwayat_kesehatan" rows="2" placeholder="Riwayat penyakit kronis, alergi obat, kejang/step, atau riwayat rawat inap..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="button" @click="nextPage()" class="px-7 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 4 DARI 6: GAMBARAN RELASI ANAK                         --}}
                {{-- ============================================================== --}}
                <div x-show="page === 4" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">GAMBARAN RELASI ANAK</h2>
                        <p class="text-sm text-slate-500 mt-1">Hubungan dan interaksi anak dengan anggota keluarga dan lingkungan sekitar.</p>
                    </div>

                    {{-- 19. Gambarkan Relasi Anak Anda dengan Ayah --}}
                    <div id="card_relasi_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['relasi_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anak Anda dengan Ayah <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="relasi_ayah" x-model="relasi_ayah" rows="2" @input="delete errors['relasi_ayah']"
                                  placeholder="Bagaimana pola komunikasi, kedekatan, dan interaksi anak dengan ayah..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">{{ old('relasi_ayah') }}</textarea>
                        <template x-if="errors['relasi_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['relasi_ayah']"></p>
                        </template>
                    </div>

                    {{-- 20. Gambarkan Relasi Anak Anda dengan Ibu --}}
                    <div id="card_relasi_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['relasi_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anak Anda dengan Ibu <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="relasi_ibu" x-model="relasi_ibu" rows="2" @input="delete errors['relasi_ibu']"
                                  placeholder="Bagaimana pola komunikasi, kedekatan, dan interaksi anak dengan ibu..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">{{ old('relasi_ibu') }}</textarea>
                        <template x-if="errors['relasi_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['relasi_ibu']"></p>
                        </template>
                    </div>

                    {{-- 21. Gambarkan Relasi Anak Anda dengan Saudara Kandung --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anak Anda dengan Saudara Kandung
                        </label>
                        <textarea name="relasi_saudara" x-model="relasi_saudara" rows="2" placeholder="Hubungan dengan kakak/adik (bisa dikosongkan jika anak tunggal)..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- 22. Gambarkan Relasi Anak Anda dengan Teman-Teman --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Gambarkan Relasi Anak Anda dengan Teman-Teman
                        </label>
                        <textarea name="relasi_teman" x-model="relasi_teman" rows="2" placeholder="Bagaimana anak berbaur dan berteman di sekolah maupun lingkungan rumah..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="button" @click="nextPage()" class="px-7 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 5 DARI 6: DATA ORANG TUA                               --}}
                {{-- ============================================================== --}}
                <div x-show="page === 5" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">DATA ORANG TUA</h2>
                        <p class="text-sm text-slate-500 mt-1">Data profil ayah dan ibu dari anak.</p>
                    </div>

                    {{-- SUB-SECTION: PROFIL AYAH --}}
                    <div class="pt-2">
                        <div class="bg-purple-50 text-purple-deep border border-purple-100 px-4 py-2.5 rounded-xl font-extrabold text-xs uppercase tracking-wider flex items-center justify-between">
                            <span class="flex items-center gap-2">👨 Profil Ayah</span>
                            <button type="button" @click="copyAlamatToAyah()" class="text-[11px] font-semibold text-purple-deep underline hover:text-[#3D1D66] cursor-pointer">
                                Salin alamat dari anak
                            </button>
                        </div>
                    </div>

                    {{-- 23. Nama Ayah --}}
                    <div id="card_nama_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['nama_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Ayah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_ayah" x-model="nama_ayah" @input="delete errors['nama_ayah']"
                               placeholder="Nama lengkap ayah" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['nama_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['nama_ayah']"></p>
                        </template>
                    </div>

                    {{-- 24. Jenis Kelamin Ayah --}}
                    <div id="card_jenis_kelamin_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['jenis_kelamin_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Jenis Kelamin Ayah <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin_ayah === 'Laki-Laki' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="jenis_kelamin_ayah" value="Laki-Laki" x-model="jenis_kelamin_ayah" @change="delete errors['jenis_kelamin_ayah']" class="sr-only">
                                <span>Laki-Laki</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin_ayah === 'Perempuan' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="jenis_kelamin_ayah" value="Perempuan" x-model="jenis_kelamin_ayah" @change="delete errors['jenis_kelamin_ayah']" class="sr-only">
                                <span>Perempuan</span>
                            </label>
                        </div>
                        <template x-if="errors['jenis_kelamin_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['jenis_kelamin_ayah']"></p>
                        </template>
                    </div>

                    {{-- 25. Usia Ayah --}}
                    <div id="card_usia_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['usia_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Usia Ayah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="usia_ayah" x-model="usia_ayah" @input="delete errors['usia_ayah']"
                               placeholder="Contoh: 42 Tahun" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['usia_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['usia_ayah']"></p>
                        </template>
                    </div>

                    {{-- 26. Pendidikan Terakhir Ayah --}}
                    <div id="card_pendidikan_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['pendidikan_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pendidikan Terakhir Ayah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="pendidikan_ayah" x-model="pendidikan_ayah" @input="delete errors['pendidikan_ayah']"
                               placeholder="Contoh: S1 Teknik / SMA" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['pendidikan_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pendidikan_ayah']"></p>
                        </template>
                    </div>

                    {{-- 27. Pekerjaan Ayah --}}
                    <div id="card_pekerjaan_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['pekerjaan_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pekerjaan Ayah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="pekerjaan_ayah" x-model="pekerjaan_ayah" @input="delete errors['pekerjaan_ayah']"
                               placeholder="Contoh: Wiraswasta / Karyawan Swasta" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['pekerjaan_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pekerjaan_ayah']"></p>
                        </template>
                    </div>

                    {{-- 28. Agama/Kepercayaan Ayah --}}
                    <div id="card_agama_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['agama_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Agama/Kepercayaan Ayah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="agama_ayah" x-model="agama_ayah" @input="delete errors['agama_ayah']"
                               placeholder="Agama/kepercayaan ayah" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['agama_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['agama_ayah']"></p>
                        </template>
                    </div>

                    {{-- 29. Urutan Kelahiran Ayah --}}
                    <div id="card_urutan_kelahiran_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['urutan_kelahiran_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Urutan Kelahiran Ayah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="urutan_kelahiran_ayah" x-model="urutan_kelahiran_ayah" @input="delete errors['urutan_kelahiran_ayah']"
                               placeholder="Contoh: Anak ke 2 dari 3 bersaudara" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['urutan_kelahiran_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['urutan_kelahiran_ayah']"></p>
                        </template>
                    </div>

                    {{-- 30. Alamat Ayah --}}
                    <div id="card_alamat_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['alamat_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm sm:text-base font-bold text-slate-900">
                                Alamat Ayah <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" @click="copyAlamatToAyah()" class="text-xs text-purple-deep font-semibold hover:underline">
                                Salin Alamat Anak
                            </button>
                        </div>
                        <input type="text" name="alamat_ayah" x-model="alamat_ayah" @input="delete errors['alamat_ayah']"
                               placeholder="Alamat tinggal ayah" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['alamat_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['alamat_ayah']"></p>
                        </template>
                    </div>

                    {{-- 31. No. Telp/HP Ayah --}}
                    <div id="card_phone_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['phone_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            No. Telp/HP Ayah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="phone_ayah" x-model="phone_ayah" @input="delete errors['phone_ayah']"
                               placeholder="08xxxxxxxxxx" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['phone_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['phone_ayah']"></p>
                        </template>
                    </div>

                    {{-- 32. Pernikahan Ayah ke- --}}
                    <div id="card_pernikahan_ayah_ke" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['pernikahan_ayah_ke'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pernikahan Ayah ke- <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="pernikahan_ayah_ke" x-model="pernikahan_ayah_ke" @input="delete errors['pernikahan_ayah_ke']"
                               placeholder="Contoh: 1" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['pernikahan_ayah_ke']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pernikahan_ayah_ke']"></p>
                        </template>
                    </div>

                    {{-- 33. Jumlah Anak Ayah --}}
                    <div id="card_jumlah_anak_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['jumlah_anak_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Jumlah Anak Ayah <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="jumlah_anak_ayah" x-model="jumlah_anak_ayah" @input="delete errors['jumlah_anak_ayah']"
                               placeholder="Contoh: 2" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['jumlah_anak_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['jumlah_anak_ayah']"></p>
                        </template>
                    </div>

                    {{-- 34. Status Pernikahan Ayah dengan Ibu --}}
                    <div id="card_status_nikah_ayah" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['status_nikah_ayah'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Status Pernikahan Ayah dengan Ibu <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="status_nikah_ayah === 'Menikah' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="status_nikah_ayah" value="Menikah" x-model="status_nikah_ayah" @change="delete errors['status_nikah_ayah']" class="sr-only">
                                <span>Menikah</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="status_nikah_ayah === 'Bercerai' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="status_nikah_ayah" value="Bercerai" x-model="status_nikah_ayah" @change="delete errors['status_nikah_ayah']" class="sr-only">
                                <span>Bercerai</span>
                            </label>
                        </div>
                        <template x-if="errors['status_nikah_ayah']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['status_nikah_ayah']"></p>
                        </template>
                    </div>

                    {{-- SUB-SECTION: PROFIL IBU --}}
                    <div class="pt-4">
                        <div class="bg-purple-50 text-purple-deep border border-purple-100 px-4 py-2.5 rounded-xl font-extrabold text-xs uppercase tracking-wider flex items-center justify-between">
                            <span class="flex items-center gap-2">👩 Profil Ibu</span>
                            <button type="button" @click="copyAlamatToIbu()" class="text-[11px] font-semibold text-purple-deep underline hover:text-[#3D1D66] cursor-pointer">
                                Salin alamat dari anak
                            </button>
                        </div>
                    </div>

                    {{-- 35. Nama Ibu --}}
                    <div id="card_nama_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['nama_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_ibu" x-model="nama_ibu" @input="delete errors['nama_ibu']"
                               placeholder="Nama lengkap ibu" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['nama_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['nama_ibu']"></p>
                        </template>
                    </div>

                    {{-- 36. Jenis Kelamin Ibu --}}
                    <div id="card_jenis_kelamin_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['jenis_kelamin_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Jenis Kelamin Ibu <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin_ibu === 'Laki-Laki' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="jenis_kelamin_ibu" value="Laki-Laki" x-model="jenis_kelamin_ibu" @change="delete errors['jenis_kelamin_ibu']" class="sr-only">
                                <span>Laki-Laki</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin_ibu === 'Perempuan' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="jenis_kelamin_ibu" value="Perempuan" x-model="jenis_kelamin_ibu" @change="delete errors['jenis_kelamin_ibu']" class="sr-only">
                                <span>Perempuan</span>
                            </label>
                        </div>
                        <template x-if="errors['jenis_kelamin_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['jenis_kelamin_ibu']"></p>
                        </template>
                    </div>

                    {{-- 37. Usia Ibu --}}
                    <div id="card_usia_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['usia_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Usia Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="usia_ibu" x-model="usia_ibu" @input="delete errors['usia_ibu']"
                               placeholder="Contoh: 39 Tahun" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['usia_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['usia_ibu']"></p>
                        </template>
                    </div>

                    {{-- 38. Pendidikan Terakhir Ibu --}}
                    <div id="card_pendidikan_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['pendidikan_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pendidikan Terakhir Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="pendidikan_ibu" x-model="pendidikan_ibu" @input="delete errors['pendidikan_ibu']"
                               placeholder="Contoh: S1 / SMA" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['pendidikan_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pendidikan_ibu']"></p>
                        </template>
                    </div>

                    {{-- 39. Pekerjaan Ibu --}}
                    <div id="card_pekerjaan_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['pekerjaan_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pekerjaan Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="pekerjaan_ibu" x-model="pekerjaan_ibu" @input="delete errors['pekerjaan_ibu']"
                               placeholder="Contoh: Ibu Rumah Tangga / Guru / Karyawan" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['pekerjaan_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pekerjaan_ibu']"></p>
                        </template>
                    </div>

                    {{-- 40. Agama/Kepercayaan Ibu --}}
                    <div id="card_agama_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['agama_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Agama/Kepercayaan Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="agama_ibu" x-model="agama_ibu" @input="delete errors['agama_ibu']"
                               placeholder="Agama/kepercayaan ibu" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['agama_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['agama_ibu']"></p>
                        </template>
                    </div>

                    {{-- 41. Urutan Kelahiran Ibu --}}
                    <div id="card_urutan_kelahiran_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['urutan_kelahiran_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Urutan Kelahiran Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="urutan_kelahiran_ibu" x-model="urutan_kelahiran_ibu" @input="delete errors['urutan_kelahiran_ibu']"
                               placeholder="Contoh: Anak ke 1 dari 2 bersaudara" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['urutan_kelahiran_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['urutan_kelahiran_ibu']"></p>
                        </template>
                    </div>

                    {{-- 42. Alamat Ibu --}}
                    <div id="card_alamat_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['alamat_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm sm:text-base font-bold text-slate-900">
                                Alamat Ibu <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" @click="copyAlamatToIbu()" class="text-xs text-purple-deep font-semibold hover:underline">
                                Salin Alamat Anak
                            </button>
                        </div>
                        <input type="text" name="alamat_ibu" x-model="alamat_ibu" @input="delete errors['alamat_ibu']"
                               placeholder="Alamat tinggal ibu" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['alamat_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['alamat_ibu']"></p>
                        </template>
                    </div>

                    {{-- 43. No. Telp/HP Ibu --}}
                    <div id="card_phone_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['phone_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            No. Telp/HP Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="phone_ibu" x-model="phone_ibu" @input="delete errors['phone_ibu']"
                               placeholder="08xxxxxxxxxx" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['phone_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['phone_ibu']"></p>
                        </template>
                    </div>

                    {{-- 44. Pernikahan Ibu ke- --}}
                    <div id="card_pernikahan_ibu_ke" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['pernikahan_ibu_ke'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Pernikahan Ibu ke- <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="pernikahan_ibu_ke" x-model="pernikahan_ibu_ke" @input="delete errors['pernikahan_ibu_ke']"
                               placeholder="Contoh: 1" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['pernikahan_ibu_ke']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pernikahan_ibu_ke']"></p>
                        </template>
                    </div>

                    {{-- 45. Jumlah Anak Ibu --}}
                    <div id="card_jumlah_anak_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['jumlah_anak_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Jumlah Anak Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="jumlah_anak_ibu" x-model="jumlah_anak_ibu" @input="delete errors['jumlah_anak_ibu']"
                               placeholder="Contoh: 2" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['jumlah_anak_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['jumlah_anak_ibu']"></p>
                        </template>
                    </div>

                    {{-- 46. Status Pernikahan Ibu dengan Ayah --}}
                    <div id="card_status_nikah_ibu" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['status_nikah_ibu'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Status Pernikahan Ibu dengan Ayah <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="status_nikah_ibu === 'Menikah' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="status_nikah_ibu" value="Menikah" x-model="status_nikah_ibu" @change="delete errors['status_nikah_ibu']" class="sr-only">
                                <span>Menikah</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="status_nikah_ibu === 'Bercerai' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="status_nikah_ibu" value="Bercerai" x-model="status_nikah_ibu" @change="delete errors['status_nikah_ibu']" class="sr-only">
                                <span>Bercerai</span>
                            </label>
                        </div>
                        <template x-if="errors['status_nikah_ibu']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['status_nikah_ibu']"></p>
                        </template>
                    </div>

                    {{-- Navigation Bottom Bar --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-purple-50 text-purple-deep font-semibold text-sm transition">
                            Kembali
                        </button>
                        <button type="button" @click="nextPage()" class="px-7 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-sm shadow-md shadow-purple-deep/20 transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 6 DARI 6: LAIN-LAIN                                    --}}
                {{-- ============================================================== --}}
                <div x-show="page === 6" x-cloak class="space-y-4">
                    {{-- Section Header Card --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">LAIN-LAIN</h2>
                        <p class="text-sm text-slate-500 mt-1">Tahap akhir riwayat konseling dan preferensi format layanan.</p>
                    </div>

                    {{-- 47. Pernah Konseling --}}
                    <div id="card_pernah_konseling" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['pernah_konseling'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Apakah Anak Anda Pernah Mengikuti Sesi Konseling Sebelumnya? <span class="text-rose-500">*</span>
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
                                <span>Tidak Pernah</span>
                            </label>
                        </div>
                        <template x-if="errors['pernah_konseling']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['pernah_konseling']"></p>
                        </template>
                    </div>

                    {{-- 48. Nama Konselor yang Menangani --}}
                    <div x-show="pernah_konseling === 'Ya'" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 sm:p-6 space-y-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Nama Konselor yang Menangani
                        </label>
                        <input type="text" name="nama_konselor_lama" value="{{ old('nama_konselor_lama') }}"
                               placeholder="Tuliskan nama konselor atau lembaga psikologi sebelumnya" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                    </div>

                    {{-- 49. Kontak Darurat --}}
                    <div id="card_kontak_darurat" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-2 transition"
                         :class="errors['kontak_darurat'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Kontak Darurat yang Bisa Kami Hubungi Terkait dengan Kondisi Anak Anda <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500">Tuliskan pula relasinya dengan Anak Anda</p>
                        <input type="text" name="kontak_darurat" x-model="kontak_darurat" @input="delete errors['kontak_darurat']"
                               placeholder="Contoh: Budi Santoso (Paman) - 08123456789" 
                               class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['kontak_darurat']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['kontak_darurat']"></p>
                        </template>
                    </div>

                    {{-- 50. Bagaimana Anda Mengetahui Tentang UCPSC? --}}
                    <div id="card_sumber_info" class="bg-white rounded-2xl border shadow-xs p-5 sm:p-6 space-y-3 transition"
                         :class="errors['sumber_info'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            Bagaimana Anda Mengetahui Tentang UCPSC? <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer font-semibold text-xs sm:text-sm flex items-center gap-2.5"
                                   :class="sumber_info === 'Referensi dari Kerabat' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="sumber_info" value="Referensi dari Kerabat" x-model="sumber_info" @change="delete errors['sumber_info']" class="text-purple-deep focus:ring-purple-deep">
                                <span>Referensi dari Kerabat</span>
                            </label>
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer font-semibold text-xs sm:text-sm flex items-center gap-2.5"
                                   :class="sumber_info === 'Media Sosial' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="sumber_info" value="Media Sosial" x-model="sumber_info" @change="delete errors['sumber_info']" class="text-purple-deep focus:ring-purple-deep">
                                <span>Media Sosial</span>
                            </label>
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer font-semibold text-xs sm:text-sm flex items-center gap-2.5"
                                   :class="sumber_info === 'Website' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-semibold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD] hover:border-purple-light/40'">
                                <input type="radio" name="sumber_info" value="Website" x-model="sumber_info" @change="delete errors['sumber_info']" class="text-purple-deep focus:ring-purple-deep">
                                <span>Website</span>
                            </label>
                        </div>
                        <template x-if="errors['sumber_info']">
                            <p class="text-xs text-rose-600 font-medium" x-text="errors['sumber_info']"></p>
                        </template>
                    </div>

                    {{-- 51. Preferensi Proses Konseling --}}
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
