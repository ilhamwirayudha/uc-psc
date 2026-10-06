<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FAF9FD]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Formulir Riwayat Hidup - Industri — UC PSC</title>
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
        html { font-size: 100% !important; }
        body { 
            font-family: 'Plus Jakarta Sans', 'Montserrat', sans-serif; 
            background-color: #FAF9FD;
            font-size: 16px;
        }
        [x-cloak] { display: none !important; }
        
        label.block.font-bold, label.font-bold {
            font-size: 0.9375rem !important;
            line-height: 1.4 !important;
        }
        @media (min-width: 640px) {
            label.block.font-bold, label.font-bold { font-size: 1rem !important; }
        }
        input[type="text"], input[type="date"], input[type="tel"], input[type="email"], input[type="number"], textarea, select {
            font-size: 0.9rem !important;
            line-height: 1.45 !important;
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased selection:bg-purple-deep selection:text-white pb-16 relative">

    {{-- Ambient UC PSC Brand Atmosphere --}}
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[450px] bg-gradient-to-b from-[#F3EAFB] via-[#FAF9FD]/80 to-transparent rounded-full blur-3xl opacity-90"></div>
        <div class="absolute top-48 -right-24 w-96 h-96 bg-purple-200/20 rounded-full blur-3xl"></div>
        <div class="absolute top-96 -left-20 w-80 h-80 bg-blue-100/25 rounded-full blur-3xl"></div>
    </div>

    {{-- Main Container --}}
    <main class="flex-1 py-6 sm:py-8 px-4" x-data="{
        page: 1,
        totalPages: 10,
        errors: {},
        draftSaved: false,
        hasRestoredDraft: false,
        formSubmitted: false,
        draftTimer: null,
        lastSavedTime: '',

        // Halaman 1: Pernyataan Kejujuran
        consent_agree: @js(old('consent_agree', '')),

        // Halaman 2: Data Diri
        nama_lengkap: @js(old('nama_lengkap', '')),
        jenis_kelamin: @js(old('jenis_kelamin', '')),
        tempat_tanggal_lahir: @js(old('tempat_tanggal_lahir', '')),
        urutan_kelahiran: @js(old('urutan_kelahiran', '')),
        alamat_sekarang: @js(old('alamat_sekarang', '')),
        asal_kota: @js(old('asal_kota', '')),
        phone: @js(old('phone', '')),
        agama: @js(old('agama', '')),
        suku_bangsa: @js(old('suku_bangsa', '')),
        status_perkawinan: @js(old('status_perkawinan', '')),
        pendidikan_terakhir: @js(old('pendidikan_terakhir', '')),
        pekerjaan_saat_ini: @js(old('pekerjaan_saat_ini', '')),
        posisi_dituju: @js(old('posisi_dituju', '')),

        // Halaman 3: PSS-10
        pss_1: @js(old('pss_1', '')),
        pss_2: @js(old('pss_2', '')),
        pss_3: @js(old('pss_3', '')),
        pss_4: @js(old('pss_4', '')),
        pss_5: @js(old('pss_5', '')),
        pss_6: @js(old('pss_6', '')),
        pss_7: @js(old('pss_7', '')),
        pss_8: @js(old('pss_8', '')),
        pss_9: @js(old('pss_9', '')),
        pss_10: @js(old('pss_10', '')),

        // Halaman 4: Data Keluarga
        ayah_nama: @js(old('ayah_nama', '')),
        ayah_jenis_kelamin: @js(old('ayah_jenis_kelamin', 'Laki-Laki')),
        ayah_usia: @js(old('ayah_usia', '')),
        ayah_pendidikan: @js(old('ayah_pendidikan', '')),
        ayah_pekerjaan: @js(old('ayah_pekerjaan', '')),

        ibu_nama: @js(old('ibu_nama', '')),
        ibu_jenis_kelamin: @js(old('ibu_jenis_kelamin', 'Perempuan')),
        ibu_usia: @js(old('ibu_usia', '')),
        ibu_pendidikan: @js(old('ibu_pendidikan', '')),
        ibu_pekerjaan: @js(old('ibu_pekerjaan', '')),

        pasangan_nama: @js(old('pasangan_nama', '')),
        pasangan_jenis_kelamin: @js(old('pasangan_jenis_kelamin', '')),
        pasangan_usia: @js(old('pasangan_usia', '')),
        pasangan_pendidikan: @js(old('pasangan_pendidikan', '')),
        pasangan_pekerjaan: @js(old('pasangan_pekerjaan', '')),

        saudara_1_nama: @js(old('saudara_1_nama', '')),
        saudara_1_jenis_kelamin: @js(old('saudara_1_jenis_kelamin', '')),
        saudara_1_usia: @js(old('saudara_1_usia', '')),
        saudara_1_pendidikan: @js(old('saudara_1_pendidikan', '')),
        saudara_1_pekerjaan: @js(old('saudara_1_pekerjaan', '')),

        saudara_2_nama: @js(old('saudara_2_nama', '')),
        saudara_2_jenis_kelamin: @js(old('saudara_2_jenis_kelamin', '')),
        saudara_2_usia: @js(old('saudara_2_usia', '')),
        saudara_2_pendidikan: @js(old('saudara_2_pendidikan', '')),
        saudara_2_pekerjaan: @js(old('saudara_2_pekerjaan', '')),

        saudara_3_nama: @js(old('saudara_3_nama', '')),
        saudara_3_jenis_kelamin: @js(old('saudara_3_jenis_kelamin', '')),
        saudara_3_usia: @js(old('saudara_3_usia', '')),
        saudara_3_pendidikan: @js(old('saudara_3_pendidikan', '')),
        saudara_3_pekerjaan: @js(old('saudara_3_pekerjaan', '')),

        saudara_4_nama: @js(old('saudara_4_nama', '')),
        saudara_4_jenis_kelamin: @js(old('saudara_4_jenis_kelamin', '')),
        saudara_4_usia: @js(old('saudara_4_usia', '')),
        saudara_4_pendidikan: @js(old('saudara_4_pendidikan', '')),
        saudara_4_pekerjaan: @js(old('saudara_4_pekerjaan', '')),

        saudara_5_nama: @js(old('saudara_5_nama', '')),
        saudara_5_jenis_kelamin: @js(old('saudara_5_jenis_kelamin', '')),
        saudara_5_usia: @js(old('saudara_5_usia', '')),
        saudara_5_pendidikan: @js(old('saudara_5_pendidikan', '')),
        saudara_5_pekerjaan: @js(old('saudara_5_pekerjaan', '')),

        anak_1_nama: @js(old('anak_1_nama', '')),
        anak_1_jenis_kelamin: @js(old('anak_1_jenis_kelamin', '')),
        anak_1_usia: @js(old('anak_1_usia', '')),
        anak_1_pendidikan: @js(old('anak_1_pendidikan', '')),
        anak_1_pekerjaan: @js(old('anak_1_pekerjaan', '')),

        anak_2_nama: @js(old('anak_2_nama', '')),
        anak_2_jenis_kelamin: @js(old('anak_2_jenis_kelamin', '')),
        anak_2_usia: @js(old('anak_2_usia', '')),
        anak_2_pendidikan: @js(old('anak_2_pendidikan', '')),
        anak_2_pekerjaan: @js(old('anak_2_pekerjaan', '')),

        anak_3_nama: @js(old('anak_3_nama', '')),
        anak_3_jenis_kelamin: @js(old('anak_3_jenis_kelamin', '')),
        anak_3_usia: @js(old('anak_3_usia', '')),
        anak_3_pendidikan: @js(old('anak_3_pendidikan', '')),
        anak_3_pekerjaan: @js(old('anak_3_pekerjaan', '')),

        anak_4_nama: @js(old('anak_4_nama', '')),
        anak_4_jenis_kelamin: @js(old('anak_4_jenis_kelamin', '')),
        anak_4_usia: @js(old('anak_4_usia', '')),
        anak_4_pendidikan: @js(old('anak_4_pendidikan', '')),
        anak_4_pekerjaan: @js(old('anak_4_pekerjaan', '')),

        anak_5_nama: @js(old('anak_5_nama', '')),
        anak_5_jenis_kelamin: @js(old('anak_5_jenis_kelamin', '')),
        anak_5_usia: @js(old('anak_5_usia', '')),
        anak_5_pendidikan: @js(old('anak_5_pendidikan', '')),
        anak_5_pekerjaan: @js(old('anak_5_pekerjaan', '')),

        // Halaman 5: Riwayat Pendidikan Formal
        ipk_terakhir: @js(old('ipk_terakhir', '')),
        nama_sekolah_1: @js(old('nama_sekolah_1', '')),
        kota_sekolah_1: @js(old('kota_sekolah_1', '')),
        tahun_masuk_sekolah_1: @js(old('tahun_masuk_sekolah_1', '')),
        tahun_keluar_sekolah_1: @js(old('tahun_keluar_sekolah_1', '')),
        keterangan_sekolah_1: @js(old('keterangan_sekolah_1', '')),

        nama_sekolah_2: @js(old('nama_sekolah_2', '')),
        kota_sekolah_2: @js(old('kota_sekolah_2', '')),
        tahun_masuk_sekolah_2: @js(old('tahun_masuk_sekolah_2', '')),
        tahun_keluar_sekolah_2: @js(old('tahun_keluar_sekolah_2', '')),
        keterangan_sekolah_2: @js(old('keterangan_sekolah_2', '')),

        nama_sekolah_3: @js(old('nama_sekolah_3', '')),
        kota_sekolah_3: @js(old('kota_sekolah_3', '')),
        tahun_masuk_sekolah_3: @js(old('tahun_masuk_sekolah_3', '')),
        tahun_keluar_sekolah_3: @js(old('tahun_keluar_sekolah_3', '')),
        keterangan_sekolah_3: @js(old('keterangan_sekolah_3', '')),

        nama_sekolah_4: @js(old('nama_sekolah_4', '')),
        kota_sekolah_4: @js(old('kota_sekolah_4', '')),
        tahun_masuk_sekolah_4: @js(old('tahun_masuk_sekolah_4', '')),
        tahun_keluar_sekolah_4: @js(old('tahun_keluar_sekolah_4', '')),
        keterangan_sekolah_4: @js(old('keterangan_sekolah_4', '')),

        nama_sekolah_5: @js(old('nama_sekolah_5', '')),
        kota_sekolah_5: @js(old('kota_sekolah_5', '')),
        tahun_masuk_sekolah_5: @js(old('tahun_masuk_sekolah_5', '')),
        tahun_keluar_sekolah_5: @js(old('tahun_keluar_sekolah_5', '')),
        keterangan_sekolah_5: @js(old('keterangan_sekolah_5', '')),

        // Halaman 6: Pendidikan Non Formal
        jenis_kursus_1: @js(old('jenis_kursus_1', '')),
        tempat_kursus_1: @js(old('tempat_kursus_1', '')),
        lama_kursus_1: @js(old('lama_kursus_1', '')),

        jenis_kursus_2: @js(old('jenis_kursus_2', '')),
        tempat_kursus_2: @js(old('tempat_kursus_2', '')),
        lama_kursus_2: @js(old('lama_kursus_2', '')),

        jenis_kursus_3: @js(old('jenis_kursus_3', '')),
        tempat_kursus_3: @js(old('tempat_kursus_3', '')),
        lama_kursus_3: @js(old('lama_kursus_3', '')),

        // Halaman 7: Riwayat Pekerjaan
        instansi_1_nama: @js(old('instansi_1_nama', '')),
        instansi_1_jabatan: @js(old('instansi_1_jabatan', '')),
        instansi_1_tahun_masuk: @js(old('instansi_1_tahun_masuk', '')),
        instansi_1_tahun_keluar: @js(old('instansi_1_tahun_keluar', '')),
        instansi_1_tugas: @js(old('instansi_1_tugas', '')),
        instansi_1_gaji: @js(old('instansi_1_gaji', '')),
        instansi_1_alasan_berhenti: @js(old('instansi_1_alasan_berhenti', '')),

        instansi_2_nama: @js(old('instansi_2_nama', '')),
        instansi_2_jabatan: @js(old('instansi_2_jabatan', '')),
        instansi_2_tahun_masuk: @js(old('instansi_2_tahun_masuk', '')),
        instansi_2_tahun_keluar: @js(old('instansi_2_tahun_keluar', '')),
        instansi_2_tugas: @js(old('instansi_2_tugas', '')),
        instansi_2_gaji: @js(old('instansi_2_gaji', '')),
        instansi_2_alasan_berhenti: @js(old('instansi_2_alasan_berhenti', '')),

        instansi_3_nama: @js(old('instansi_3_nama', '')),
        instansi_3_jabatan: @js(old('instansi_3_jabatan', '')),
        instansi_3_tahun_masuk: @js(old('instansi_3_tahun_masuk', '')),
        instansi_3_tahun_keluar: @js(old('instansi_3_tahun_keluar', '')),
        instansi_3_tugas: @js(old('instansi_3_tugas', '')),
        instansi_3_gaji: @js(old('instansi_3_gaji', '')),
        instansi_3_alasan_berhenti: @js(old('instansi_3_alasan_berhenti', '')),

        instansi_4_nama: @js(old('instansi_4_nama', '')),
        instansi_4_jabatan: @js(old('instansi_4_jabatan', '')),
        instansi_4_tahun_masuk: @js(old('instansi_4_tahun_masuk', '')),
        instansi_4_tahun_keluar: @js(old('instansi_4_tahun_keluar', '')),
        instansi_4_tugas: @js(old('instansi_4_tugas', '')),
        instansi_4_gaji: @js(old('instansi_4_gaji', '')),
        instansi_4_alasan_berhenti: @js(old('instansi_4_alasan_berhenti', '')),

        instansi_5_nama: @js(old('instansi_5_nama', '')),
        instansi_5_jabatan: @js(old('instansi_5_jabatan', '')),
        instansi_5_tahun_masuk: @js(old('instansi_5_tahun_masuk', '')),
        instansi_5_tahun_keluar: @js(old('instansi_5_tahun_keluar', '')),
        instansi_5_tugas: @js(old('instansi_5_tugas', '')),
        instansi_5_gaji: @js(old('instansi_5_gaji', '')),
        instansi_5_alasan_berhenti: @js(old('instansi_5_alasan_berhenti', '')),

        ekspektasi_gaji_tunjangan: @js(old('ekspektasi_gaji_tunjangan', '')),

        // Halaman 8: Pengalaman Organisasi
        organisasi_1_nama: @js(old('organisasi_1_nama', '')),
        organisasi_1_jabatan: @js(old('organisasi_1_jabatan', '')),
        organisasi_1_lama: @js(old('organisasi_1_lama', '')),

        organisasi_2_nama: @js(old('organisasi_2_nama', '')),
        organisasi_2_jabatan: @js(old('organisasi_2_jabatan', '')),
        organisasi_2_lama: @js(old('organisasi_2_lama', '')),

        organisasi_3_nama: @js(old('organisasi_3_nama', '')),
        organisasi_3_jabatan: @js(old('organisasi_3_jabatan', '')),
        organisasi_3_lama: @js(old('organisasi_3_lama', '')),

        // Halaman 9: Prestasi
        prestasi: @js(old('prestasi', '')),

        // Halaman 10: Deskripsi Diri
        riwayat_trauma: @js(old('riwayat_trauma', '')),
        riwayat_penyakit_opname: @js(old('riwayat_penyakit_opname', '')),
        konsultasi_psikolog: @js(old('konsultasi_psikolog', '')),
        kelebihan_1: @js(old('kelebihan_1', '')),
        kelebihan_2: @js(old('kelebihan_2', '')),
        kelebihan_3: @js(old('kelebihan_3', '')),
        kelemahan_1: @js(old('kelemahan_1', '')),
        kelemahan_2: @js(old('kelemahan_2', '')),
        kelemahan_3: @js(old('kelemahan_3', '')),
        hobi: @js(old('hobi', '')),
        deskripsi_diri_bebas: @js(old('deskripsi_diri_bebas', '')),

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

            draft['_saved_page'] = this.page;
            draft['_saved_at'] = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            try {
                localStorage.setItem('ucpsc_draft_industri_gform_v2', JSON.stringify(draft));
                this.draftSaved = true;
                this.hasRestoredDraft = true;
                this.lastSavedTime = draft['_saved_at'];
                clearTimeout(this.draftTimer);
                this.draftTimer = setTimeout(() => { this.draftSaved = false; }, 3000);
            } catch (err) {}
        },

        restoreDraft() {
            try {
                const raw = localStorage.getItem('ucpsc_draft_industri_gform_v2');
                if (!raw) return;
                const draft = JSON.parse(raw);
                if (!draft || typeof draft !== 'object') return;

                const keys = Object.keys(draft).filter(k => !k.startsWith('_'));
                keys.forEach(key => {
                    if (this.hasOwnProperty(key)) {
                        this[key] = draft[key];
                    }
                });

                if (draft._saved_page && draft._saved_page >= 1 && draft._saved_page <= this.totalPages) {
                    this.page = draft._saved_page;
                }
                this.hasRestoredDraft = true;
                this.lastSavedTime = draft._saved_at || '';
            } catch (err) {}
        },

        clearDraftOnSubmit() {
            this.formSubmitted = true;
            try { localStorage.removeItem('ucpsc_draft_industri_gform_v2'); } catch (e) {}
        },

        validateCurrentPage() {
            this.errors = {};

            if (this.page === 1) {
                if (!this.consent_agree) {
                    this.errors['consent_agree'] = 'Pertanyaan ini wajib diisi';
                } else if (this.consent_agree === 'Tidak Setuju') {
                    this.errors['consent_agree'] = 'Anda harus menyetujui pernyataan untuk melanjutkan pengisian riwayat hidup.';
                }
            } else if (this.page === 2) {
                if (!this.nama_lengkap.trim()) this.errors['nama_lengkap'] = 'Pertanyaan ini wajib diisi';
                if (!this.jenis_kelamin) this.errors['jenis_kelamin'] = 'Pertanyaan ini wajib diisi';
                if (!this.tempat_tanggal_lahir.trim()) this.errors['tempat_tanggal_lahir'] = 'Pertanyaan ini wajib diisi';
                if (!this.urutan_kelahiran.trim()) this.errors['urutan_kelahiran'] = 'Pertanyaan ini wajib diisi';
                if (!this.asal_kota.trim()) this.errors['asal_kota'] = 'Pertanyaan ini wajib diisi';
                if (!this.phone.trim()) this.errors['phone'] = 'Pertanyaan ini wajib diisi';
                if (!this.suku_bangsa.trim()) this.errors['suku_bangsa'] = 'Pertanyaan ini wajib diisi';
                if (!this.status_perkawinan) this.errors['status_perkawinan'] = 'Pertanyaan ini wajib diisi';
                if (!this.pendidikan_terakhir) this.errors['pendidikan_terakhir'] = 'Pertanyaan ini wajib diisi';
                if (!this.posisi_dituju.trim()) this.errors['posisi_dituju'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 3) {
                for (let i = 1; i <= 10; i++) {
                    if (this['pss_' + i] === '' || this['pss_' + i] === null || this['pss_' + i] === undefined) {
                        this.errors['pss_' + i] = 'Pertanyaan ini wajib diisi';
                    }
                }
            } else if (this.page === 5) {
                if (!this.nama_sekolah_1.trim()) this.errors['nama_sekolah_1'] = 'Pertanyaan ini wajib diisi';
                if (!this.kota_sekolah_1.trim()) this.errors['kota_sekolah_1'] = 'Pertanyaan ini wajib diisi';
                if (!this.tahun_masuk_sekolah_1.trim()) this.errors['tahun_masuk_sekolah_1'] = 'Pertanyaan ini wajib diisi';
                if (!this.tahun_keluar_sekolah_1.trim()) this.errors['tahun_keluar_sekolah_1'] = 'Pertanyaan ini wajib diisi';
                if (!this.keterangan_sekolah_1.trim()) this.errors['keterangan_sekolah_1'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 7) {
                if (!this.ekspektasi_gaji_tunjangan.trim()) this.errors['ekspektasi_gaji_tunjangan'] = 'Pertanyaan ini wajib diisi';
            } else if (this.page === 10) {
                if (!this.kelebihan_1.trim()) this.errors['kelebihan_1'] = 'Pertanyaan ini wajib diisi';
                if (!this.kelebihan_2.trim()) this.errors['kelebihan_2'] = 'Pertanyaan ini wajib diisi';
                if (!this.kelebihan_3.trim()) this.errors['kelebihan_3'] = 'Pertanyaan ini wajib diisi';
                if (!this.kelemahan_1.trim()) this.errors['kelemahan_1'] = 'Pertanyaan ini wajib diisi';
                if (!this.kelemahan_2.trim()) this.errors['kelemahan_2'] = 'Pertanyaan ini wajib diisi';
                if (!this.kelemahan_3.trim()) this.errors['kelemahan_3'] = 'Pertanyaan ini wajib diisi';
                if (!this.hobi.trim()) this.errors['hobi'] = 'Pertanyaan ini wajib diisi';
                if (!this.deskripsi_diri_bebas.trim()) this.errors['deskripsi_diri_bebas'] = 'Pertanyaan ini wajib diisi';
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

            {{-- Progress Header Bar --}}
            <div class="bg-white rounded-2xl p-4 border border-[#EDE1FA] shadow-xs flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-deep">Formulir Riwayat Hidup - Industri</span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs font-semibold text-slate-600" x-text="'Halaman ' + page + ' dari ' + totalPages"></span>
                </div>
                <div class="flex items-center gap-3">
                    <span x-show="draftSaved" x-transition class="text-xs font-medium text-emerald-600 flex items-center gap-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Draf tersimpan</span>
                    </span>
                    <div class="w-24 sm:w-36 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                        <div class="bg-purple-deep h-full rounded-full transition-all duration-300" :style="'width: ' + ((page / totalPages) * 100) + '%'"></div>
                    </div>
                </div>
            </div>

            {{-- FORM START --}}
            <form action="{{ route('public.client-form.industri.store') }}" 
                  method="POST" 
                  id="industriUCPSCForm" 
                  @input.debounce.400ms="saveDraft()" 
                  @change="saveDraft()"
                  @submit="if(!validateCurrentPage()){ $event.preventDefault(); } else { clearDraftOnSubmit(); }">
                @csrf

                {{-- ============================================================== --}}
                {{-- HALAMAN 1 DARI 10: PERNYATAAN KEJUJURAN                        --}}
                {{-- ============================================================== --}}
                <div x-show="page === 1" class="space-y-4">
                    <div class="bg-gradient-to-r from-purple-deep via-[#4A2F85] to-purple-light rounded-2xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden">
                        <div class="relative z-10 space-y-2">
                            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Formulir Riwayat Hidup - Industri</h1>
                            <p class="text-white/90 text-sm sm:text-base leading-relaxed">
                                Formulir ini digunakan untuk asesmen psikologis seleksi, rekrutmen, dan pemetaan kompetensi kerja UC Psychological Service Center.
                            </p>
                            <div class="pt-3 border-t border-white/20 text-xs sm:text-sm text-rose-200 font-medium">
                                * Menunjukkan pertanyaan yang wajib diisi
                            </div>
                        </div>
                    </div>

                    <div id="card_consent_agree" class="bg-white rounded-2xl border shadow-xs p-6 space-y-4 transition"
                         :class="errors['consent_agree'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm sm:text-base font-bold text-slate-900 leading-snug">
                            Semua keterangan yang saya berikan dalam Formulir Riwayat Hidup ini, saya buat dengan jujur dan sungguh-sungguh. <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-2.5 pt-1">
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3 font-semibold text-sm"
                                   :class="consent_agree === 'Setuju' ? 'border-2 border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="consent_agree" value="Setuju" x-model="consent_agree" @change="delete errors['consent_agree']" class="w-4 h-4 text-purple-deep">
                                <span>Setuju</span>
                            </label>
                            <label class="p-3.5 rounded-xl border-2 transition cursor-pointer flex items-center gap-3 font-semibold text-sm"
                                   :class="consent_agree === 'Tidak Setuju' ? 'border-rose-400 bg-rose-50/70 text-rose-700' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="consent_agree" value="Tidak Setuju" x-model="consent_agree" @change="delete errors['consent_agree']" class="w-4 h-4 text-rose-600">
                                <span>Tidak Setuju</span>
                            </label>
                        </div>
                        <template x-if="errors['consent_agree']">
                            <p class="text-xs text-rose-600 font-medium pt-1" x-text="errors['consent_agree']"></p>
                        </template>
                    </div>

                    <div class="flex items-center justify-end pt-3">
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 2 DARI 10: DATA DIRI                                   --}}
                {{-- ============================================================== --}}
                <div x-show="page === 2" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Data Diri</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Identitas diri peserta asesmen secara lengkap.</p>
                    </div>

                    {{-- 1. Nama Lengkap --}}
                    <div id="card_nama_lengkap" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['nama_lengkap'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Nama Lengkap (Beserta Gelar Jika Ada) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" x-model="nama_lengkap" @input="delete errors['nama_lengkap']" placeholder="Jawaban Anda" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['nama_lengkap']"><p class="text-xs text-rose-600 font-medium" x-text="errors['nama_lengkap']"></p></template>
                    </div>

                    {{-- 2. Jenis Kelamin --}}
                    <div id="card_jenis_kelamin" class="bg-white rounded-2xl border shadow-xs p-5 space-y-3 transition" :class="errors['jenis_kelamin'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin === 'Laki-Laki' ? 'border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="jenis_kelamin" value="Laki-Laki" x-model="jenis_kelamin" @change="delete errors['jenis_kelamin']" class="w-4 h-4 text-purple-deep">
                                <span>Laki-Laki</span>
                            </label>
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="jenis_kelamin === 'Perempuan' ? 'border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="jenis_kelamin" value="Perempuan" x-model="jenis_kelamin" @change="delete errors['jenis_kelamin']" class="w-4 h-4 text-purple-deep">
                                <span>Perempuan</span>
                            </label>
                        </div>
                        <template x-if="errors['jenis_kelamin']"><p class="text-xs text-rose-600 font-medium" x-text="errors['jenis_kelamin']"></p></template>
                    </div>

                    {{-- 3. Tempat, Tanggal Lahir --}}
                    <div id="card_tempat_tanggal_lahir" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['tempat_tanggal_lahir'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Tempat, Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <input type="text" name="tempat_tanggal_lahir" x-model="tempat_tanggal_lahir" @input="delete errors['tempat_tanggal_lahir']" placeholder="Contoh: Surabaya, 15 Januari 1998" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['tempat_tanggal_lahir']"><p class="text-xs text-rose-600 font-medium" x-text="errors['tempat_tanggal_lahir']"></p></template>
                    </div>

                    {{-- 4. Urutan Kelahiran --}}
                    <div id="card_urutan_kelahiran" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['urutan_kelahiran'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Urutan Kelahiran <span class="text-rose-500">*</span></label>
                        <p class="text-xs text-slate-500">Anak ke _____ dari _____ bersaudara</p>
                        <input type="text" name="urutan_kelahiran" x-model="urutan_kelahiran" @input="delete errors['urutan_kelahiran']" placeholder="Contoh: Anak ke 2 dari 3 bersaudara" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['urutan_kelahiran']"><p class="text-xs text-rose-600 font-medium" x-text="errors['urutan_kelahiran']"></p></template>
                    </div>

                    {{-- 5. Alamat Sekarang --}}
                    <div id="card_alamat_sekarang" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-2 transition">
                        <label class="block text-sm font-bold text-slate-900">Alamat Sekarang <span class="text-xs font-normal text-slate-400">(Opsional)</span></label>
                        <textarea name="alamat_sekarang" rows="3" x-model="alamat_sekarang" placeholder="Alamat domisili tempat tinggal saat ini..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- 6. Asal Kota --}}
                    <div id="card_asal_kota" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['asal_kota'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Asal Kota <span class="text-rose-500">*</span></label>
                        <input type="text" name="asal_kota" x-model="asal_kota" @input="delete errors['asal_kota']" placeholder="Contoh: Surabaya" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['asal_kota']"><p class="text-xs text-rose-600 font-medium" x-text="errors['asal_kota']"></p></template>
                    </div>

                    {{-- 7. No. Telp/HP --}}
                    <div id="card_phone" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['phone'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">No. Telp/HP <span class="text-rose-500">*</span></label>
                        <input type="tel" name="phone" x-model="phone" @input="delete errors['phone']" placeholder="Contoh: 081234567890" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['phone']"><p class="text-xs text-rose-600 font-medium" x-text="errors['phone']"></p></template>
                    </div>

                    {{-- 8. Agama/Kepercayaan --}}
                    <div id="card_agama" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-2 transition">
                        <label class="block text-sm font-bold text-slate-900">Agama/Kepercayaan <span class="text-xs font-normal text-slate-400">(Opsional)</span></label>
                        <input type="text" name="agama" x-model="agama" placeholder="Contoh: Islam / Kristen / Katolik / Hindu / Buddha / Khonghucu" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                    </div>

                    {{-- 9. Suku Bangsa --}}
                    <div id="card_suku_bangsa" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['suku_bangsa'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Suku Bangsa <span class="text-rose-500">*</span></label>
                        <input type="text" name="suku_bangsa" x-model="suku_bangsa" @input="delete errors['suku_bangsa']" placeholder="Contoh: Jawa / Tionghoa / Batak / Sunda / Madura" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['suku_bangsa']"><p class="text-xs text-rose-600 font-medium" x-text="errors['suku_bangsa']"></p></template>
                    </div>

                    {{-- 10. Status Perkawinan --}}
                    <div id="card_status_perkawinan" class="bg-white rounded-2xl border shadow-xs p-5 space-y-3 transition" :class="errors['status_perkawinan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Status Perkawinan <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach(['Belum Menikah', 'Menikah', 'Bercerai', 'Pasangan Meninggal'] as $st)
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center gap-3"
                                   :class="status_perkawinan === '{{ $st }}' ? 'border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="status_perkawinan" value="{{ $st }}" x-model="status_perkawinan" @change="delete errors['status_perkawinan']" class="w-4 h-4 text-purple-deep">
                                <span>{{ $st }}</span>
                            </label>
                            @endforeach
                        </div>
                        <template x-if="errors['status_perkawinan']"><p class="text-xs text-rose-600 font-medium" x-text="errors['status_perkawinan']"></p></template>
                    </div>

                    {{-- 11. Pendidikan Terakhir --}}
                    <div id="card_pendidikan_terakhir" class="bg-white rounded-2xl border shadow-xs p-5 space-y-3 transition" :class="errors['pendidikan_terakhir'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Pendidikan Terakhir <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            @foreach(['SMA', 'SMK', 'D3', 'D4', 'S1', 'S2', 'S3'] as $pend)
                            <label class="p-3 rounded-xl border-2 transition cursor-pointer font-semibold text-sm flex items-center justify-center gap-2"
                                   :class="pendidikan_terakhir === '{{ $pend }}' ? 'border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="pendidikan_terakhir" value="{{ $pend }}" x-model="pendidikan_terakhir" @change="delete errors['pendidikan_terakhir']" class="w-4 h-4 text-purple-deep">
                                <span>{{ $pend }}</span>
                            </label>
                            @endforeach
                        </div>
                        <template x-if="errors['pendidikan_terakhir']"><p class="text-xs text-rose-600 font-medium" x-text="errors['pendidikan_terakhir']"></p></template>
                    </div>

                    {{-- 12. Pekerjaan Saat Ini --}}
                    <div id="card_pekerjaan_saat_ini" class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-2">
                        <label class="block text-sm font-bold text-slate-900">Pekerjaan Saat Ini <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="pekerjaan_saat_ini" x-model="pekerjaan_saat_ini" placeholder="Jawaban Anda" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                    </div>

                    {{-- 13. Posisi yang Dituju --}}
                    <div id="card_posisi_dituju" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['posisi_dituju'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Posisi yang Dituju <span class="text-rose-500">*</span></label>
                        <input type="text" name="posisi_dituju" x-model="posisi_dituju" @input="delete errors['posisi_dituju']" placeholder="Contoh: Management Trainee / Staff Finance" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['posisi_dituju']"><p class="text-xs text-rose-600 font-medium" x-text="errors['posisi_dituju']"></p></template>
                    </div>

                    {{-- Navigasi --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 3 DARI 10: KUESIONER STRES (PSS-10)                   --}}
                {{-- ============================================================== --}}
                <div x-show="page === 3" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6 space-y-2">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Kuesioner Stres (PSS-10)</h2>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Kuesioner ini bertujuan untuk mengetahui bagaimana Anda merasakan dan menghadapi situasi selama proses pencarian kerja dalam sebulan terakhir. Tidak ada jawaban benar atau salah. Harap menjawab dengan jujur sesuai apa yang Anda alami.
                        </p>
                        <div class="p-3 bg-purple-50 rounded-xl border border-purple-100 text-xs text-purple-900 mt-2 space-y-1">
                            <p class="font-bold">Skala jawaban:</p>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-1.5 pt-1">
                                <span><strong>0</strong> = Tidak Pernah</span>
                                <span><strong>1</strong> = Hampir Tidak Pernah</span>
                                <span><strong>2</strong> = Kadang-kadang</span>
                                <span><strong>3</strong> = Cukup Sering</span>
                                <span><strong>4</strong> = Sangat Sering</span>
                            </div>
                        </div>
                    </div>

                    @php
                    $pssQuestions = [
                        1 => '1. Dalam sebulan terakhir, seberapa sering Anda merasa kecewa karena sesuatu yang terjadi secara tidak terduga?',
                        2 => '2. Dalam sebulan terakhir, seberapa sering Anda merasa tidak mampu mengendalikan hal-hal penting dalam hidup Anda?',
                        3 => '3. Dalam sebulan terakhir, seberapa sering Anda merasa gelisah dan stres?',
                        4 => '4. Dalam sebulan terakhir, seberapa sering Anda merasa percaya diri dengan kemampuan Anda untuk menyelesaikan masalah pribadi Anda?',
                        5 => '5. Dalam sebulan terakhir, seberapa sering Anda merasa segala sesuatu berjalan sesuai keinginan Anda?',
                        6 => '6. Dalam sebulan terakhir, seberapa sering Anda mengetahui bahwa Anda tidak bisa mengatasi hal-hal yang harus Anda lakukan?',
                        7 => '7. Dalam sebulan terakhir, seberapa sering Anda mampu mengendalikan hal-hal yang menjengkelkan dalam hidup Anda?',
                        8 => '8. Dalam sebulan terakhir, seberapa sering Anda merasa mampu mengendalikan permasalahan Anda?',
                        9 => '9. Dalam sebulan terakhir, seberapa sering Anda marah karena hal-hal yang terjadi di luar kendali Anda?',
                        10 => '10. Dalam sebulan terakhir, seberapa sering Anda merasa tidak mampu menyelesaikan permasalahan permasalahan yang menumpuk dalam hidup Anda?',
                    ];
                    @endphp

                    @foreach($pssQuestions as $num => $qText)
                    <div id="card_pss_{{ $num }}" class="bg-white rounded-2xl border shadow-xs p-5 space-y-3 transition" :class="errors['pss_{{ $num }}'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900 leading-snug">{{ $qText }} <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-5 gap-2 pt-1 text-center">
                            @foreach([0, 1, 2, 3, 4] as $val)
                            <label class="py-3 px-1 rounded-xl border-2 transition cursor-pointer flex flex-col items-center justify-center gap-1"
                                   :class="pss_{{ $num }} == '{{ $val }}' ? 'border-purple-deep bg-[#F3EAFB] text-purple-deep ring-2 ring-purple-deep/15 font-bold' : 'border-[#EDE1FA] text-slate-700 hover:bg-[#FAF9FD]'">
                                <input type="radio" name="pss_{{ $num }}" value="{{ $val }}" x-model="pss_{{ $num }}" @change="delete errors['pss_{{ $num }}']" class="w-4 h-4 text-purple-deep">
                                <span class="text-sm font-bold">{{ $val }}</span>
                            </label>
                            @endforeach
                        </div>
                        <template x-if="errors['pss_{{ $num }}']"><p class="text-xs text-rose-600 font-medium" x-text="errors['pss_{{ $num }}']"></p></template>
                    </div>
                    @endforeach

                    {{-- Navigasi --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 4 DARI 10: DATA KELUARGA                               --}}
                {{-- ============================================================== --}}
                <div x-show="page === 4" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Data Keluarga</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Informasi latar belakang keluarga (orang tua, pasangan, saudara, dan anak).</p>
                    </div>

                    {{-- 1. Ayah --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-4">
                        <h3 class="font-bold text-purple-deep text-base border-b border-purple-100 pb-2">Data Ayah</h3>
                        <div class="grid sm:grid-cols-2 gap-3.5">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Nama Ayah</label><input type="text" name="ayah_nama" x-model="ayah_nama" placeholder="Nama ayah" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin Ayah</label>
                                <select name="ayah_jenis_kelamin" x-model="ayah_jenis_kelamin" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm bg-white">
                                    <option value="Laki-Laki">Laki-Laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Usia Ayah</label><input type="text" name="ayah_usia" x-model="ayah_usia" placeholder="Contoh: 58 Tahun" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan Ayah</label><input type="text" name="ayah_pendidikan" x-model="ayah_pendidikan" placeholder="Contoh: S1" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-700 mb-1">Pekerjaan Ayah</label><input type="text" name="ayah_pekerjaan" x-model="ayah_pekerjaan" placeholder="Contoh: Pensiunan PNS / Wiraswasta" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                        </div>
                    </div>

                    {{-- 2. Ibu --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-4">
                        <h3 class="font-bold text-purple-deep text-base border-b border-purple-100 pb-2">Data Ibu</h3>
                        <div class="grid sm:grid-cols-2 gap-3.5">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Nama Ibu</label><input type="text" name="ibu_nama" x-model="ibu_nama" placeholder="Nama ibu" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin Ibu</label>
                                <select name="ibu_jenis_kelamin" x-model="ibu_jenis_kelamin" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm bg-white">
                                    <option value="Perempuan">Perempuan</option>
                                    <option value="Laki-Laki">Laki-Laki</option>
                                </select>
                            </div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Usia Ibu</label><input type="text" name="ibu_usia" x-model="ibu_usia" placeholder="Contoh: 54 Tahun" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan Ibu</label><input type="text" name="ibu_pendidikan" x-model="ibu_pendidikan" placeholder="Contoh: SMA" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-700 mb-1">Pekerjaan Ibu</label><input type="text" name="ibu_pekerjaan" x-model="ibu_pekerjaan" placeholder="Contoh: Ibu Rumah Tangga / Guru" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                        </div>
                    </div>

                    {{-- 3. Suami / Istri --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-4">
                        <h3 class="font-bold text-purple-deep text-base border-b border-purple-100 pb-2">Data Suami / Istri <span class="text-xs text-slate-400 font-normal">(Jika sudah menikah)</span></h3>
                        <div class="grid sm:grid-cols-2 gap-3.5">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Nama Suami/Istri</label><input type="text" name="pasangan_nama" x-model="pasangan_nama" placeholder="Nama pasangan" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                                <select name="pasangan_jenis_kelamin" x-model="pasangan_jenis_kelamin" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-Laki">Laki-Laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Usia</label><input type="text" name="pasangan_usia" x-model="pasangan_usia" placeholder="Usia pasangan" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan</label><input type="text" name="pasangan_pendidikan" x-model="pasangan_pendidikan" placeholder="Pendidikan pasangan" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-700 mb-1">Pekerjaan</label><input type="text" name="pasangan_pekerjaan" x-model="pasangan_pekerjaan" placeholder="Pekerjaan pasangan" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                        </div>
                    </div>

                    {{-- 4. Saudara Kandung (1 s.d. 5) --}}
                    @for($s = 1; $s <= 5; $s++)
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-3">
                        <h4 class="font-bold text-purple-deep text-sm">Data Saudara {{ $s }} <span class="text-xs text-slate-400 font-normal">(Opsional)</span></h4>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Nama Saudara {{ $s }}</label><input type="text" name="saudara_{{ $s }}_nama" x-model="saudara_{{ $s }}_nama" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                                <select name="saudara_{{ $s }}_jenis_kelamin" x-model="saudara_{{ $s }}_jenis_kelamin" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-Laki">Laki-Laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Usia</label><input type="text" name="saudara_{{ $s }}_usia" x-model="saudara_{{ $s }}_usia" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan</label><input type="text" name="saudara_{{ $s }}_pendidikan" x-model="saudara_{{ $s }}_pendidikan" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-700 mb-1">Pekerjaan</label><input type="text" name="saudara_{{ $s }}_pekerjaan" x-model="saudara_{{ $s }}_pekerjaan" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                        </div>
                    </div>
                    @endfor

                    {{-- 5. Anak (1 s.d. 5) --}}
                    @for($a = 1; $a <= 5; $a++)
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-3">
                        <h4 class="font-bold text-purple-deep text-sm">Data Anak {{ $a }} <span class="text-xs text-slate-400 font-normal">(Opsional)</span></h4>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Nama Anak {{ $a }}</label><input type="text" name="anak_{{ $a }}_nama" x-model="anak_{{ $a }}_nama" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                                <select name="anak_{{ $a }}_jenis_kelamin" x-model="anak_{{ $a }}_jenis_kelamin" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm bg-white">
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-Laki">Laki-Laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Usia</label><input type="text" name="anak_{{ $a }}_usia" x-model="anak_{{ $a }}_usia" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Pendidikan</label><input type="text" name="anak_{{ $a }}_pendidikan" x-model="anak_{{ $a }}_pendidikan" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div class="sm:col-span-2"><label class="block text-xs font-bold text-slate-700 mb-1">Pekerjaan</label><input type="text" name="anak_{{ $a }}_pekerjaan" x-model="anak_{{ $a }}_pekerjaan" class="w-full px-3 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                        </div>
                    </div>
                    @endfor

                    {{-- Navigasi --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 5 DARI 10: RIWAYAT PENDIDIKAN FORMAL                   --}}
                {{-- ============================================================== --}}
                <div x-show="page === 5" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Riwayat Pendidikan Formal</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Tuliskan dari yang paling akhir.</p>
                    </div>

                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-2">
                        <label class="block text-sm font-bold text-slate-900">IPK Terakhir (Jika Ada)</label>
                        <input type="text" name="ipk_terakhir" x-model="ipk_terakhir" placeholder="Contoh: 3.75" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] text-sm">
                    </div>

                    {{-- Sekolah 1 (Wajib) --}}
                    <div class="bg-white rounded-2xl border border-purple-200 shadow-xs p-5 space-y-3 bg-purple-50/15">
                        <div class="border-b border-purple-100 pb-2 flex items-center justify-between">
                            <h3 class="font-bold text-purple-deep text-base">Sekolah / Universitas 1 (Pendidikan Terakhir) <span class="text-rose-500">*</span></h3>
                        </div>
                        <div class="space-y-3">
                            <div id="card_nama_sekolah_1">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sekolah 1 <span class="text-rose-500">*</span></label>
                                <input type="text" name="nama_sekolah_1" x-model="nama_sekolah_1" @input="delete errors['nama_sekolah_1']" placeholder="Nama sekolah / universitas" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                                <template x-if="errors['nama_sekolah_1']"><p class="text-xs text-rose-600 font-medium" x-text="errors['nama_sekolah_1']"></p></template>
                            </div>
                            <div id="card_kota_sekolah_1">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kota Sekolah 1 <span class="text-rose-500">*</span></label>
                                <input type="text" name="kota_sekolah_1" x-model="kota_sekolah_1" @input="delete errors['kota_sekolah_1']" placeholder="Kota" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                                <template x-if="errors['kota_sekolah_1']"><p class="text-xs text-rose-600 font-medium" x-text="errors['kota_sekolah_1']"></p></template>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div id="card_tahun_masuk_sekolah_1">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Masuk Sekolah 1 <span class="text-rose-500">*</span></label>
                                    <input type="text" name="tahun_masuk_sekolah_1" x-model="tahun_masuk_sekolah_1" @input="delete errors['tahun_masuk_sekolah_1']" placeholder="Tahun masuk" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                                    <template x-if="errors['tahun_masuk_sekolah_1']"><p class="text-xs text-rose-600 font-medium" x-text="errors['tahun_masuk_sekolah_1']"></p></template>
                                </div>
                                <div id="card_tahun_keluar_sekolah_1">
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Keluar Sekolah 1 <span class="text-rose-500">*</span></label>
                                    <input type="text" name="tahun_keluar_sekolah_1" x-model="tahun_keluar_sekolah_1" @input="delete errors['tahun_keluar_sekolah_1']" placeholder="Tahun lulus" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                                    <template x-if="errors['tahun_keluar_sekolah_1']"><p class="text-xs text-rose-600 font-medium" x-text="errors['tahun_keluar_sekolah_1']"></p></template>
                                </div>
                            </div>
                            <div id="card_keterangan_sekolah_1">
                                <label class="block text-xs font-bold text-slate-700 mb-0.5">Keterangan Sekolah 1 <span class="text-rose-500">*</span></label>
                                <p class="text-[11px] text-slate-500 mb-1">Tuliskan jurusan jika ada</p>
                                <input type="text" name="keterangan_sekolah_1" x-model="keterangan_sekolah_1" @input="delete errors['keterangan_sekolah_1']" placeholder="Contoh: S1 Manajemen / IPA / Teknik Informatika" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                                <template x-if="errors['keterangan_sekolah_1']"><p class="text-xs text-rose-600 font-medium" x-text="errors['keterangan_sekolah_1']"></p></template>
                            </div>
                        </div>
                    </div>

                    {{-- Sekolah 2 s.d. 5 (Opsional) --}}
                    @for($sk = 2; $sk <= 5; $sk++)
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-3">
                        <h4 class="font-bold text-purple-deep text-sm">Sekolah / Universitas {{ $sk }} <span class="text-xs text-slate-400 font-normal">(Opsional)</span></h4>
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Nama Sekolah {{ $sk }}</label><input type="text" name="nama_sekolah_{{ $sk }}" x-model="nama_sekolah_{{ $sk }}" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Kota Sekolah {{ $sk }}</label><input type="text" name="kota_sekolah_{{ $sk }}" x-model="kota_sekolah_{{ $sk }}" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div class="grid grid-cols-2 gap-3">
                                <div><label class="block text-xs font-bold text-slate-700 mb-1">Tahun Masuk</label><input type="text" name="tahun_masuk_sekolah_{{ $sk }}" x-model="tahun_masuk_sekolah_{{ $sk }}" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1">Tahun Keluar</label><input type="text" name="tahun_keluar_sekolah_{{ $sk }}" x-model="tahun_keluar_sekolah_{{ $sk }}" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-0.5">Keterangan Sekolah {{ $sk }}</label>
                                <p class="text-[11px] text-slate-500 mb-1">Tuliskan jurusan jika ada</p>
                                <input type="text" name="keterangan_sekolah_{{ $sk }}" x-model="keterangan_sekolah_{{ $sk }}" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                            </div>
                        </div>
                    </div>
                    @endfor

                    {{-- Navigasi --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 6 DARI 10: PENDIDIKAN NON FORMAL                       --}}
                {{-- ============================================================== --}}
                <div x-show="page === 6" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Pendidikan Non Formal</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Tuliskan dari yang paling akhir.</p>
                    </div>

                    @for($c = 1; $c <= 3; $c++)
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-3">
                        <h3 class="font-bold text-purple-deep text-base">Kursus / Pelatihan {{ $c }} <span class="text-xs text-slate-400 font-normal">(Opsional)</span></h3>
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kursus {{ $c }}</label><input type="text" name="jenis_kursus_{{ $c }}" x-model="jenis_kursus_{{ $c }}" placeholder="Contoh: Digital Marketing / Brevet Pajak" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Tempat Kursus {{ $c }}</label><input type="text" name="tempat_kursus_{{ $c }}" x-model="tempat_kursus_{{ $c }}" placeholder="Lembaga / Kota" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Lama Kursus {{ $c }}</label><input type="text" name="lama_kursus_{{ $c }}" x-model="lama_kursus_{{ $c }}" placeholder="Contoh: 3 Bulan (Januari - Maret 2024)" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                        </div>
                    </div>
                    @endfor

                    {{-- Navigasi --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 7 DARI 10: RIWAYAT PEKERJAAN                           --}}
                {{-- ============================================================== --}}
                <div x-show="page === 7" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Riwayat Pekerjaan</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Tuliskan dari yang paling akhir. Bagi fresh graduated, bisa menuliskan pengalaman magang.</p>
                    </div>

                    @for($w = 1; $w <= 5; $w++)
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-3">
                        <h3 class="font-bold text-purple-deep text-base">Instansi / Perusahaan {{ $w }} <span class="text-xs text-slate-400 font-normal">(Opsional)</span></h3>
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Nama Instansi {{ $w }}</label><input type="text" name="instansi_{{ $w }}_nama" x-model="instansi_{{ $w }}_nama" placeholder="Nama perusahaan / instansi" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Jabatan Instansi {{ $w }}</label><input type="text" name="instansi_{{ $w }}_jabatan" x-model="instansi_{{ $w }}_jabatan" placeholder="Jabatan terakhir" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div class="grid grid-cols-2 gap-3">
                                <div><label class="block text-xs font-bold text-slate-700 mb-1">Tahun Masuk</label><input type="text" name="instansi_{{ $w }}_tahun_masuk" x-model="instansi_{{ $w }}_tahun_masuk" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1">Tahun Keluar</label><input type="text" name="instansi_{{ $w }}_tahun_keluar" x-model="instansi_{{ $w }}_tahun_keluar" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            </div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Ringkasan Tugas Instansi {{ $w }}</label><textarea name="instansi_{{ $w }}_tugas" rows="2" x-model="instansi_{{ $w }}_tugas" placeholder="Deskripsikan tanggung jawab utama Anda..." class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></textarea></div>
                            <div class="grid sm:grid-cols-2 gap-3">
                                <div><label class="block text-xs font-bold text-slate-700 mb-1">Gaji dan Tunjangan</label><input type="text" name="instansi_{{ $w }}_gaji" x-model="instansi_{{ $w }}_gaji" placeholder="Contoh: Rp 5.000.000" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                                <div><label class="block text-xs font-bold text-slate-700 mb-1">Alasan Berhenti</label><input type="text" name="instansi_{{ $w }}_alasan_berhenti" x-model="instansi_{{ $w }}_alasan_berhenti" placeholder="Alasan keluar / selesai" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            </div>
                        </div>
                    </div>
                    @endfor

                    {{-- Ekspektasi Gaji (Wajib) --}}
                    <div id="card_ekspektasi_gaji_tunjangan" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['ekspektasi_gaji_tunjangan'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Ekspektasi Gaji dan Tunjangan pada Perusahaan Ini <span class="text-rose-500">*</span></label>
                        <input type="text" name="ekspektasi_gaji_tunjangan" x-model="ekspektasi_gaji_tunjangan" @input="delete errors['ekspektasi_gaji_tunjangan']" placeholder="Contoh: Rp 7.000.000 - Rp 9.000.000 beserta BPJS dan tunjangan transport" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white">
                        <template x-if="errors['ekspektasi_gaji_tunjangan']"><p class="text-xs text-rose-600 font-medium" x-text="errors['ekspektasi_gaji_tunjangan']"></p></template>
                    </div>

                    {{-- Navigasi --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 8 DARI 10: PENGALAMAN ORGANISASI                       --}}
                {{-- ============================================================== --}}
                <div x-show="page === 8" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Pengalaman Organisasi</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Tuliskan dari yang paling akhir.</p>
                    </div>

                    @for($o = 1; $o <= 3; $o++)
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-3">
                        <h3 class="font-bold text-purple-deep text-base">Organisasi {{ $o }} <span class="text-xs text-slate-400 font-normal">(Opsional)</span></h3>
                        <div class="space-y-3">
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Nama Organisasi {{ $o }}</label><input type="text" name="organisasi_{{ $o }}_nama" x-model="organisasi_{{ $o }}_nama" placeholder="Nama organisasi" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Posisi/Jabatan Organisasi {{ $o }}</label><input type="text" name="organisasi_{{ $o }}_jabatan" x-model="organisasi_{{ $o }}_jabatan" placeholder="Jabatan dalam organisasi" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                            <div><label class="block text-xs font-bold text-slate-700 mb-1">Lama Berorganisasi di Organisasi {{ $o }}</label><input type="text" name="organisasi_{{ $o }}_lama" x-model="organisasi_{{ $o }}_lama" placeholder="Contoh: 1 Tahun (2022 - 2023)" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm"></div>
                        </div>
                    </div>
                    @endfor

                    {{-- Navigasi --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 9 DARI 10: PRESTASI                                    --}}
                {{-- ============================================================== --}}
                <div x-show="page === 9" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Prestasi</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Pencapaian akademik maupun non-akademik.</p>
                    </div>

                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-2">
                        <label class="block text-sm font-bold text-slate-900 leading-snug">
                            Jika Anda pernah meraih prestasi, tuliskan prestasi apa saja yang pernah Anda raih. Prestasi di sini, boleh yang sifatnya akademik maupun non-akademik. <span class="text-xs text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea name="prestasi" rows="4" x-model="prestasi" placeholder="Tuliskan daftar prestasi Anda..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] focus:border-purple-deep focus:ring-4 focus:ring-purple-deep/10 text-sm outline-none transition bg-[#FAF9FD]/40 focus:bg-white"></textarea>
                    </div>

                    {{-- Navigasi --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="button" @click="nextPage()" class="px-5 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-semibold text-sm shadow-md transition flex items-center gap-2">
                            <span>Berikutnya</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- HALAMAN 10 DARI 10: DESKRIPSI DIRI                             --}}
                {{-- ============================================================== --}}
                <div x-show="page === 10" x-cloak class="space-y-4">
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-6">
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Deskripsi Diri</h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Refleksi diri, kekuatan, area pengembangan, dan riwayat kesehatan.</p>
                    </div>

                    {{-- 1. Trauma --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-2">
                        <label class="block text-sm font-bold text-slate-900 leading-snug">
                            Tuliskan riwayat hidup yang sifatnya menimbulkan trauma (jika ada). <span class="text-xs text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea name="riwayat_trauma" rows="2" x-model="riwayat_trauma" placeholder="Jawaban Anda" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] text-sm"></textarea>
                    </div>

                    {{-- 2. Penyakit Opname --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-2">
                        <label class="block text-sm font-bold text-slate-900 leading-snug">
                            Tuliskan riwayat penyakit yang sifatnya sampai membutuhkan opname (jika ada). <span class="text-xs text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea name="riwayat_penyakit_opname" rows="2" x-model="riwayat_penyakit_opname" placeholder="Jawaban Anda" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] text-sm"></textarea>
                    </div>

                    {{-- 3. Konsultasi Psikolog --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-2">
                        <label class="block text-sm font-bold text-slate-900 leading-snug">
                            Apakah Anda pernah konsultasi dengan psikolog sebelumnya? Jika ya, tuliskan jenis dan tujuan dari konsultasi tersebut. <span class="text-xs text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <textarea name="konsultasi_psikolog" rows="2" x-model="konsultasi_psikolog" placeholder="Jawaban Anda" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] text-sm"></textarea>
                    </div>

                    {{-- 4. Kelebihan Diri 1, 2, 3 --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-3">
                        <h3 class="font-bold text-slate-900 text-sm">Kelebihan Diri <span class="text-rose-500">*</span></h3>
                        <div id="card_kelebihan_1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelebihan Diri 1 <span class="text-rose-500">*</span></label>
                            <input type="text" name="kelebihan_1" x-model="kelebihan_1" @input="delete errors['kelebihan_1']" placeholder="Kelebihan 1" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                            <template x-if="errors['kelebihan_1']"><p class="text-xs text-rose-600 font-medium" x-text="errors['kelebihan_1']"></p></template>
                        </div>
                        <div id="card_kelebihan_2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelebihan Diri 2 <span class="text-rose-500">*</span></label>
                            <input type="text" name="kelebihan_2" x-model="kelebihan_2" @input="delete errors['kelebihan_2']" placeholder="Kelebihan 2" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                            <template x-if="errors['kelebihan_2']"><p class="text-xs text-rose-600 font-medium" x-text="errors['kelebihan_2']"></p></template>
                        </div>
                        <div id="card_kelebihan_3">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelebihan Diri 3 <span class="text-rose-500">*</span></label>
                            <input type="text" name="kelebihan_3" x-model="kelebihan_3" @input="delete errors['kelebihan_3']" placeholder="Kelebihan 3" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                            <template x-if="errors['kelebihan_3']"><p class="text-xs text-rose-600 font-medium" x-text="errors['kelebihan_3']"></p></template>
                        </div>
                    </div>

                    {{-- 5. Kelemahan Diri 1, 2, 3 --}}
                    <div class="bg-white rounded-2xl border border-[#EDE1FA] shadow-xs p-5 space-y-3">
                        <h3 class="font-bold text-slate-900 text-sm">Kelemahan Diri <span class="text-rose-500">*</span></h3>
                        <div id="card_kelemahan_1">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelemahan Diri 1 <span class="text-rose-500">*</span></label>
                            <input type="text" name="kelemahan_1" x-model="kelemahan_1" @input="delete errors['kelemahan_1']" placeholder="Kelemahan 1" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                            <template x-if="errors['kelemahan_1']"><p class="text-xs text-rose-600 font-medium" x-text="errors['kelemahan_1']"></p></template>
                        </div>
                        <div id="card_kelemahan_2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelemahan Diri 2 <span class="text-rose-500">*</span></label>
                            <input type="text" name="kelemahan_2" x-model="kelemahan_2" @input="delete errors['kelemahan_2']" placeholder="Kelemahan 2" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                            <template x-if="errors['kelemahan_2']"><p class="text-xs text-rose-600 font-medium" x-text="errors['kelemahan_2']"></p></template>
                        </div>
                        <div id="card_kelemahan_3">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelemahan Diri 3 <span class="text-rose-500">*</span></label>
                            <input type="text" name="kelemahan_3" x-model="kelemahan_3" @input="delete errors['kelemahan_3']" placeholder="Kelemahan 3" class="w-full px-3.5 py-2 rounded-xl border border-[#E4D2F5] text-sm">
                            <template x-if="errors['kelemahan_3']"><p class="text-xs text-rose-600 font-medium" x-text="errors['kelemahan_3']"></p></template>
                        </div>
                    </div>

                    {{-- 6. Hobi --}}
                    <div id="card_hobi" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['hobi'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Hobi <span class="text-rose-500">*</span></label>
                        <input type="text" name="hobi" x-model="hobi" @input="delete errors['hobi']" placeholder="Contoh: Membaca, Bulu Tangkis, Menulis" class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] text-sm">
                        <template x-if="errors['hobi']"><p class="text-xs text-rose-600 font-medium" x-text="errors['hobi']"></p></template>
                    </div>

                    {{-- 7. Deskripsi Diri Bebas --}}
                    <div id="card_deskripsi_diri_bebas" class="bg-white rounded-2xl border shadow-xs p-5 space-y-2 transition" :class="errors['deskripsi_diri_bebas'] ? 'border-rose-400 bg-rose-50/20' : 'border-[#EDE1FA]'">
                        <label class="block text-sm font-bold text-slate-900">Deskripsikan diri Anda secara bebas dalam 1-2 paragraf. <span class="text-rose-500">*</span></label>
                        <textarea name="deskripsi_diri_bebas" rows="4" x-model="deskripsi_diri_bebas" @input="delete errors['deskripsi_diri_bebas']" placeholder="Tuliskan gambaran umum kepribadian, nilai hidup, dan cara Anda memandang pekerjaan..." class="w-full px-4 py-2.5 rounded-xl border border-[#E4D2F5] text-sm"></textarea>
                        <template x-if="errors['deskripsi_diri_bebas']"><p class="text-xs text-rose-600 font-medium" x-text="errors['deskripsi_diri_bebas']"></p></template>
                    </div>

                    {{-- Navigasi Terakhir / Submit --}}
                    <div class="flex items-center justify-between pt-3">
                        <button type="button" @click="prevPage()" class="px-5 py-3 rounded-xl border border-[#EDE1FA] bg-white hover:bg-slate-50 text-slate-700 font-semibold text-sm transition">Kembali</button>
                        <button type="submit" class="px-6 py-3 rounded-xl bg-purple-deep hover:bg-[#3D1D66] text-white font-bold text-sm shadow-md transition flex items-center gap-2 cursor-pointer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Kirim Formulir Pendaftaran</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="text-center py-6 text-xs text-slate-400 border-t border-[#EDE1FA]/60 bg-white/50 backdrop-blur-xs">
        <p>&copy; {{ date('Y') }} Universitas Ciputra Psychological Service Center (UC PSC). Hak cipta dilindungi undang-undang.</p>
    </footer>

</body>
</html>
