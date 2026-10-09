<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $clientForm->pdf_filename }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ucpsc.png') }}">

    <style>
        /* ==========================================================================
           MICROSOFT WORD DOCUMENT STYLE (Clean, Table-free, Non-web aesthetic)
           ========================================================================== */
        @page {
            size: A4 portrait;
            margin: 2.54cm 2.54cm 2.54cm 2.54cm; /* Standard 1 inch MS Word Margin */
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000000;
            background-color: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Halaman Kertas Word (A4 Sheet Format) */
        .word-page {
            max-width: 21cm;
            min-height: 29.7cm;
            margin: 0 auto;
            background: #ffffff;
            padding: 2.54cm 2.54cm 2.54cm 2.54cm; /* 1 inch Word margins */
        }

        /* Kop Surat Resmi Word Style */
        .kop-surat {
            border-bottom: 2px solid #000000;
            padding-bottom: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .kop-surat-logo img {
            height: 65px;
            width: auto;
        }
        .kop-surat-text {
            text-align: center;
            flex: 1;
        }
        .kop-title-main {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-title-sub {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-address {
            font-size: 9.5pt;
            color: #222222;
            margin-top: 2px;
        }
        .kop-contact {
            font-size: 9pt;
            color: #444444;
        }

        /* Judul Dokumen Word */
        .doc-title-block {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #222222;
            margin-top: 2px;
        }
        .doc-meta {
            font-size: 9.5pt;
            color: #555555;
            margin-top: 4px;
        }

        /* Section Headings Word Style */
        .section-header {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 18px;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #777777;
            page-break-after: avoid;
            break-after: avoid;
        }
        .sub-header {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 4px;
            text-decoration: underline;
            page-break-after: avoid;
            break-after: avoid;
        }

        /* Format Baris Data (Label : Value) Tanpa Tabel */
        .field-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 4px;
            line-height: 1.35;
        }
        .field-label {
            width: 215px;
            flex-shrink: 0;
            color: #111111;
        }
        .field-colon {
            width: 14px;
            flex-shrink: 0;
            text-align: center;
            color: #111111;
        }
        .field-value {
            flex: 1;
            color: #000000;
            font-weight: 500;
        }

        /* Format Paragraf / Isian Panjang Word Style */
        .text-paragraph {
            margin-top: 4px;
            margin-bottom: 10px;
            text-align: justify;
            line-height: 1.4;
        }
        .text-paragraph-label {
            font-weight: bold;
            margin-bottom: 2px;
            display: block;
        }

        /* List Word Style */
        .list-numbered {
            margin-left: 20px;
            margin-bottom: 8px;
        }
        .list-numbered li {
            margin-bottom: 3px;
            line-height: 1.35;
        }

        /* Item Blok Berulang (misal: pekerjaan, sekolah, kursus) */
        .item-block {
            margin-bottom: 8px;
            padding-left: 12px;
            border-left: 2px solid #cccccc;
        }
        .item-block-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

        /* Blok Tanda Tangan Word */
        .signature-section {
            margin-top: 35px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .signature-grid {
            display: flex;
            justify-content: space-between;
            text-align: center;
            font-size: 10.5pt;
        }
        .signature-col {
            width: 45%;
        }
        .signature-space {
            height: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-note {
            font-size: 8.5pt;
            color: #555555;
            margin-top: 2px;
        }

        /* Footer Halaman Word */
        .doc-page-footer {
            margin-top: 30px;
            padding-top: 8px;
            border-top: 1px solid #cccccc;
            display: flex;
            justify-content: space-between;
            font-size: 8.5pt;
            color: #666666;
        }

        /* Aturan Khusus Cetak (@media print) */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .word-page {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .page-break-inside-avoid {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    {{-- Kertas Dokumen Word --}}
    <div class="word-page">

        {{-- KOP SURAT RESMI --}}
        <div class="kop-surat">
            <div class="kop-surat-logo">
                <img src="{{ asset('images/logo-ucpsc.png') }}" alt="Logo UC PSC">
            </div>
            <div class="kop-surat-text">
                <div class="kop-title-main">UNIVERSITAS CIPUTRA SURABAYA</div>
                <div class="kop-title-sub">PSYCHOLOGICAL SERVICE CENTER (UC PSC)</div>
                <div class="kop-address">CitraLand CBD Boulevard, Made, Sambikerep, Surabaya 60219</div>
                <div class="kop-contact">Telp/WA: +62 812-3456-7890 | Email: psc@ciputra.ac.id | Website: uc.ac.id/psc</div>
            </div>
        </div>

        {{-- JUDUL LAPORAN --}}
        <div class="doc-title-block">
            <div class="doc-title">BERKAS FORMULIR PENDAFTARAN & RIWAYAT HIDUP</div>
            <div class="doc-subtitle">{{ $clientForm->form_type_label }}</div>
            <div class="doc-meta">
                Nomor Berkas: {{ $clientForm->ticket_number ?? 'DRAFT' }} &nbsp;|&nbsp; 
                Waktu Pengisian: {{ $clientForm->created_at ? $clientForm->created_at->format('d F Y, H:i') . ' WIB' : '-' }}
            </div>
        </div>

        @php
            $ans = $clientForm->answers ?? [];
            $ft = $clientForm->form_type;
        @endphp

        {{-- ========================================================================= --}}
        {{-- SECTION 1: LEMBAR PERNYATAAN / PERSETUJUAN                                --}}
        {{-- ========================================================================= --}}
        <div class="section-header">
            {{ $ft === 'industri' ? 'LEMBAR PERNYATAAN KEJUJURAN' : 'LEMBAR PERSETUJUAN (INFORMED CONSENT)' }}
        </div>

        <div class="field-row">
            <span class="field-label">Status Persetujuan</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['consent_agree'] ?? ($clientForm->consent_agreed ? 'Setuju' : 'Setuju') }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Waktu Validasi Persetujuan</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $clientForm->created_at ? $clientForm->created_at->format('d F Y, H:i:s') . ' WIB' : '-' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Keterangan Pernyataan</span>
            <span class="field-colon">:</span>
            <span class="field-value">
                {{ $ft === 'industri' 
                    ? '"Semua keterangan yang saya berikan dalam Formulir Riwayat Hidup ini, saya buat dengan jujur dan sungguh-sungguh."' 
                    : 'Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.' }}
            </span>
        </div>


        {{-- ========================================================================= --}}
        {{-- SECTION 2: DATA IDENTITAS & INFORMASI DIRI                                --}}
        {{-- ========================================================================= --}}
        <div class="section-header">
            {{ $ft === 'industri' ? 'DATA DIRI & POSISI PEKERJAAN' : 'DATA IDENTITAS & DIRI KLIEN' }}
        </div>

        <div class="field-row">
            <span class="field-label">Nama Lengkap & Gelar</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['nama_lengkap'] ?? $clientForm->client_name ?? $clientForm->client?->name ?? '-' }}</span>
        </div>
        @if(!empty($ans['posisi_dituju']))
        <div class="field-row">
            <span class="field-label">Posisi yang Dituju</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['posisi_dituju'] }}</span>
        </div>
        @endif
        <div class="field-row">
            <span class="field-label">Jenis Kelamin</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['jenis_kelamin'] ?? $ans['gender'] ?? '-' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Tempat, Tanggal Lahir</span>
            <span class="field-colon">:</span>
            <span class="field-value">
                {{ $ans['birth_place_date'] ?? $ans['tempat_tanggal_lahir'] ?? (($ans['tempat_lahir'] ?? '') . (!empty($ans['tempat_lahir']) && !empty($ans['tanggal_lahir']) ? ', ' : '') . ($ans['tanggal_lahir'] ?? '-')) }}
            </span>
        </div>
        @if(!empty($ans['urutan_kelahiran']) || !empty($ans['birth_order']))
        <div class="field-row">
            <span class="field-label">Urutan Kelahiran</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['urutan_kelahiran'] ?? $ans['birth_order'] }}</span>
        </div>
        @endif
        <div class="field-row">
            <span class="field-label">Nomor Telepon / WhatsApp</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $clientForm->client_phone ?? $ans['phone'] ?? $ans['telepon'] ?? '-' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Alamat Email</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['email'] ?? $clientForm->client?->email ?? '-' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Agama / Kepercayaan</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['agama'] ?? $ans['religion'] ?? '-' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Suku Bangsa</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['suku_bangsa'] ?? $ans['ethnicity'] ?? '-' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Pendidikan Terakhir</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['pendidikan_terakhir'] ?? $ans['last_education'] ?? '-' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Pekerjaan Saat Ini</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['pekerjaan'] ?? $ans['pekerjaan_saat_ini'] ?? '-' }}</span>
        </div>
        @if(!empty($ans['hobi']))
        <div class="field-row">
            <span class="field-label">Hobi / Minat Kegemaran</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['hobi'] }}</span>
        </div>
        @endif
        <div class="field-row">
            <span class="field-label">Alamat Tempat Tinggal</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ $ans['alamat'] ?? $ans['alamat_sekarang'] ?? $ans['current_address'] ?? '-' }}</span>
        </div>


        {{-- ========================================================================= --}}
        {{-- FORM TYPE SPESIFIK: INDUSTRI                                              --}}
        {{-- ========================================================================= --}}
        @if($ft === 'industri')

            {{-- Kuesioner Stres PSS-10 --}}
            <div class="section-header">KUESIONER TINGKAT STRES (PSS-10)</div>
            <p style="font-size: 9.5pt; color: #444; margin-bottom: 6px;">
                Skala penilaian: 0 = Tidak Pernah, 1 = Hampir Tidak Pernah, 2 = Kadang-kadang, 3 = Cukup Sering, 4 = Sangat Sering.
            </p>
            @php
            $pssQuestions = [
                1 => 'Kecewa karena sesuatu terjadi secara tidak terduga',
                2 => 'Merasa tidak mampu mengendalikan hal-hal penting dalam hidup',
                3 => 'Merasa gelisah, cemas, dan stres',
                4 => 'Yakin dan percaya diri terhadap kemampuan menyelesaikan masalah pribadi',
                5 => 'Merasa segala sesuatu berjalan sesuai dengan keinginan',
                6 => 'Merasa tidak mampu mengatasi hal-hal yang harus dikerjakan',
                7 => 'Mampu mengendalikan hal-hal yang menjengkelkan dalam hidup',
                8 => 'Merasa mampu mengendalikan dan mengatasi berbagai permasalahan',
                9 => 'Merasa marah karena hal-hal yang terjadi berada di luar kendali',
                10 => 'Merasa kesulitan menumpuk begitu tinggi hingga tidak mampu mengatasinya',
            ];
            @endphp
            <ol class="list-numbered">
                @foreach($pssQuestions as $pssIdx => $pssText)
                <li>
                    <span>{{ $pssText }}</span> &nbsp;—&nbsp; <strong>Skor: {{ $ans["pss_{$pssIdx}"] ?? '-' }}</strong>
                </li>
                @endforeach
            </ol>

            {{-- Susunan Anggota Keluarga --}}
            <div class="section-header">SUSUNAN ANGGOTA KELUARGA</div>
            
            <div class="sub-header">Data Orang Tua</div>
            <div class="field-row">
                <span class="field-label">Nama Ayah</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['ayah_nama'] ?? '-' }} {{ !empty($ans['ayah_usia']) ? '(' . $ans['ayah_usia'] . ' tahun)' : '' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Pendidikan & Pekerjaan Ayah</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['ayah_pendidikan'] ?? '-' }} / {{ $ans['ayah_pekerjaan'] ?? '-' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Nama Ibu</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['ibu_nama'] ?? '-' }} {{ !empty($ans['ibu_usia']) ? '(' . $ans['ibu_usia'] . ' tahun)' : '' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Pendidikan & Pekerjaan Ibu</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['ibu_pendidikan'] ?? '-' }} / {{ $ans['ibu_pekerjaan'] ?? '-' }}</span>
            </div>

            {{-- Riwayat Pendidikan Formal --}}
            <div class="section-header">RIWAYAT PENDIDIKAN FORMAL</div>
            @if(!empty($ans['ipk_terakhir']))
            <div class="field-row">
                <span class="field-label">Indeks Prestasi Kumulatif (IPK)</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['ipk_terakhir'] }}</span>
            </div>
            @endif
            @php $hasEdu = false; @endphp
            @foreach(range(1, 5) as $eduIdx)
                @if(!empty($ans["nama_sekolah_{$eduIdx}"]))
                    @php $hasEdu = true; @endphp
                    <div class="item-block">
                        <div class="item-block-title">{{ $ans["nama_sekolah_{$eduIdx}"] }} ({{ $ans["tahun_masuk_sekolah_{$eduIdx}"] ?? '-' }} - {{ $ans["tahun_keluar_sekolah_{$eduIdx}"] ?? '-' }})</div>
                        <div class="field-row">
                            <span class="field-label">Jurusan / Keterangan</span>
                            <span class="field-colon">:</span>
                            <span class="field-value">{{ $ans["keterangan_sekolah_{$eduIdx}"] ?? '-' }}</span>
                        </div>
                        <div class="field-row">
                            <span class="field-label">Kota Tempat Sekolah</span>
                            <span class="field-colon">:</span>
                            <span class="field-value">{{ $ans["kota_sekolah_{$eduIdx}"] ?? '-' }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
            @if(!$hasEdu)
                <p style="color: #666; font-style: italic;">Tidak ada catatan riwayat pendidikan formal tambahan.</p>
            @endif

            {{-- Pendidikan Non Formal / Kursus --}}
            @if(!empty($ans['jenis_kursus_1']) || !empty($ans['jenis_kursus_2']) || !empty($ans['jenis_kursus_3']))
            <div class="section-header">PENDIDIKAN NON FORMAL / KURSUS / PELATIHAN</div>
            @foreach(range(1, 3) as $krsIdx)
                @if(!empty($ans["jenis_kursus_{$krsIdx}"]))
                <div class="item-block">
                    <div class="item-block-title">{{ $ans["jenis_kursus_{$krsIdx}"] }}</div>
                    <div class="field-row">
                        <span class="field-label">Tempat Penyelenggara</span>
                        <span class="field-colon">:</span>
                        <span class="field-value">{{ $ans["tempat_kursus_{$krsIdx}"] ?? '-' }}</span>
                    </div>
                    <div class="field-row">
                        <span class="field-label">Lama Pelatihan / Durasi</span>
                        <span class="field-colon">:</span>
                        <span class="field-value">{{ $ans["lama_kursus_{$krsIdx}"] ?? '-' }}</span>
                    </div>
                </div>
                @endif
            @endforeach
            @endif

            {{-- Riwayat Pekerjaan --}}
            <div class="section-header">RIWAYAT PEKERJAAN & PENGALAMAN KERJA</div>
            @if(!empty($ans['ekspektasi_gaji_tunjangan']))
            <div class="field-row" style="margin-bottom: 8px;">
                <span class="field-label">Ekspektasi Gaji & Tunjangan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['ekspektasi_gaji_tunjangan'] }}</span>
            </div>
            @endif
            @php $hasJob = false; @endphp
            @foreach(range(1, 5) as $jobIdx)
                @if(!empty($ans["instansi_{$jobIdx}_nama"]))
                    @php $hasJob = true; @endphp
                    <div class="item-block">
                        <div class="item-block-title">{{ $ans["instansi_{$jobIdx}_nama"] }} ({{ $ans["instansi_{$jobIdx}_tahun_masuk"] ?? '-' }} - {{ $ans["instansi_{$jobIdx}_tahun_keluar"] ?? '-' }})</div>
                        <div class="field-row">
                            <span class="field-label">Jabatan Terakhir</span>
                            <span class="field-colon">:</span>
                            <span class="field-value">{{ $ans["instansi_{$jobIdx}_jabatan"] ?? '-' }}</span>
                        </div>
                        <div class="field-row">
                            <span class="field-label">Gaji Terakhir</span>
                            <span class="field-colon">:</span>
                            <span class="field-value">{{ $ans["instansi_{$jobIdx}_gaji"] ?? '-' }}</span>
                        </div>
                        <div class="field-row">
                            <span class="field-label">Alasan Berhenti</span>
                            <span class="field-colon">:</span>
                            <span class="field-value">{{ $ans["instansi_{$jobIdx}_alasan_berhenti"] ?? '-' }}</span>
                        </div>
                        @if(!empty($ans["instansi_{$jobIdx}_tugas"]))
                        <div class="field-row">
                            <span class="field-label">Uraian Tugas Pokok</span>
                            <span class="field-colon">:</span>
                            <span class="field-value">{{ $ans["instansi_{$jobIdx}_tugas"] }}</span>
                        </div>
                        @endif
                    </div>
                @endif
            @endforeach
            @if(!$hasJob)
                <p style="color: #666; font-style: italic;">Belum ada catatan riwayat pekerjaan sebelumnya.</p>
            @endif

            {{-- Pengalaman Organisasi & Prestasi --}}
            @if(!empty($ans['organisasi_1_nama']) || !empty($ans['organisasi_2_nama']) || !empty($ans['prestasi']))
            <div class="section-header">PENGALAMAN ORGANISASI & PRESTASI</div>
            @foreach(range(1, 3) as $orgIdx)
                @if(!empty($ans["organisasi_{$orgIdx}_nama"]))
                <div class="field-row">
                    <span class="field-label">{{ $ans["organisasi_{$orgIdx}_nama"] }}</span>
                    <span class="field-colon">:</span>
                    <span class="field-value">Jabatan: {{ $ans["organisasi_{$orgIdx}_jabatan"] ?? '-' }} (Periode: {{ $ans["organisasi_{$orgIdx}_lama"] ?? '-' }})</span>
                </div>
                @endif
            @endforeach
            @if(!empty($ans['prestasi']))
            <div class="text-paragraph" style="margin-top: 6px;">
                <span class="text-paragraph-label">Prestasi yang Pernah Diraih:</span>
                <p>{{ $ans['prestasi'] }}</p>
            </div>
            @endif
            @endif

            {{-- Riwayat Kesehatan & Trauma --}}
            <div class="section-header">DESKRIPSI DIRI & RIWAYAT KESEHATAN</div>
            <div class="field-row">
                <span class="field-label">Riwayat Peristiwa Trauma</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['riwayat_trauma'] ?? '-' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Riwayat Penyakit Fisik Berat</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['riwayat_penyakit_berat'] ?? '-' }}</span>
            </div>

        {{-- ========================================================================= --}}
        {{-- FORM TYPE NON-INDUSTRI (DEWASA, ANAK, PRA NIKAH, PERNIKAHAN, BIOGRAPHY)   --}}
        {{-- ========================================================================= --}}
        @else

            {{-- Alasan Konseling & Keluhan --}}
            <div class="section-header">ALASAN KONSELING & KELUHAN UTAMA</div>
            <div class="text-paragraph">
                <span class="text-paragraph-label">Keluhan atau Alasan Melakukan Konseling:</span>
                <p>{{ $ans['alasan_konseling'] ?? $ans['keluhan_utama'] ?? $ans['keterangan'] ?? '-' }}</p>
            </div>

            {{-- Kondisi Psikologis --}}
            @if(!empty($ans['kondisi_saat_ini']) || !empty($ans['pikiran_negatif']) || !empty($ans['hal_ingin_ditingkatkan']) || !empty($ans['karakter_kepribadian']) || !empty($ans['ketakutan_phobia']))
            <div class="section-header">DATA KONDISI PSIKOLOGIS</div>
            @if(!empty($ans['kondisi_saat_ini']))
            <div class="text-paragraph">
                <span class="text-paragraph-label">Gambaran Kondisi Saat Ini terhadap Permasalahan:</span>
                <p>{{ $ans['kondisi_saat_ini'] }}</p>
            </div>
            @endif
            @if(!empty($ans['pikiran_negatif']))
            <div class="text-paragraph">
                <span class="text-paragraph-label">Pikiran Negatif yang Sering Muncul:</span>
                <p>{{ $ans['pikiran_negatif'] }}</p>
            </div>
            @endif
            @if(!empty($ans['hal_ingin_ditingkatkan']))
            <div class="field-row">
                <span class="field-label">Hal yang Ingin Ditingkatkan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['hal_ingin_ditingkatkan'] }}</span>
            </div>
            @endif
            @if(!empty($ans['karakter_kepribadian']))
            <div class="field-row">
                <span class="field-label">Karakter Kepribadian</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['karakter_kepribadian'] }}</span>
            </div>
            @endif
            @if(!empty($ans['ketakutan_phobia']))
            <div class="field-row">
                <span class="field-label">Ketakutan / Phobia Tertentu</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['ketakutan_phobia'] }}</span>
            </div>
            @endif
            @if(!empty($ans['riwayat_kesehatan']))
            <div class="field-row">
                <span class="field-label">Riwayat Kesehatan / Medis</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['riwayat_kesehatan'] }}</span>
            </div>
            @endif
            @endif

            {{-- Relasi Interpersonal --}}
            @if(!empty($ans['relasi_ayah']) || !empty($ans['relasi_ibu']) || !empty($ans['relasi_saudara']) || !empty($ans['relasi_pasangan']) || !empty($ans['relasi_anak']))
            <div class="section-header">GAMBARAN RELASI INTERPERSONAL</div>
            @if(!empty($ans['relasi_ayah']))
            <div class="field-row">
                <span class="field-label">Hubungan dengan Ayah</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['relasi_ayah'] }}</span>
            </div>
            @endif
            @if(!empty($ans['relasi_ibu']))
            <div class="field-row">
                <span class="field-label">Hubungan dengan Ibu</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['relasi_ibu'] }}</span>
            </div>
            @endif
            @if(!empty($ans['relasi_saudara']))
            <div class="field-row">
                <span class="field-label">Hubungan dengan Saudara</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['relasi_saudara'] }}</span>
            </div>
            @endif
            @if(!empty($ans['relasi_pasangan']))
            <div class="field-row">
                <span class="field-label">Hubungan dengan Pasangan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['relasi_pasangan'] }}</span>
            </div>
            @endif
            @if(!empty($ans['relasi_anak']))
            <div class="field-row">
                <span class="field-label">Hubungan dengan Anak</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['relasi_anak'] }}</span>
            </div>
            @endif
            @endif

            {{-- Khusus Pasangan / Pra-Nikah / Pernikahan --}}
            @if(!empty($ans['nama_pasangan']))
            <div class="section-header">DATA PASANGAN & RIWAYAT PERNIKAHAN</div>
            <div class="field-row">
                <span class="field-label">Nama Pasangan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['nama_pasangan'] }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Jenis Kelamin Pasangan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['jenis_kelamin_pasangan'] ?? '-' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">No. Telepon / HP Pasangan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['phone_pasangan'] ?? '-' }}</span>
            </div>
            @if(!empty($ans['pekerjaan_pasangan']))
            <div class="field-row">
                <span class="field-label">Pekerjaan Pasangan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['pekerjaan_pasangan'] }}</span>
            </div>
            @endif
            @if(!empty($ans['tanggal_rencana_pernikahan']))
            <div class="field-row">
                <span class="field-label">Rencana Pernikahan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['tanggal_rencana_pernikahan'] }}</span>
            </div>
            @endif
            @if(!empty($ans['lama_pernikahan']))
            <div class="field-row">
                <span class="field-label">Lama Usia Pernikahan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['lama_pernikahan'] }}</span>
            </div>
            @endif
            @if(!empty($ans['jumlah_anak']))
            <div class="field-row">
                <span class="field-label">Jumlah Anak</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['jumlah_anak'] }}</span>
            </div>
            @endif
            @if(!empty($ans['harapan_pernikahan']))
            <div class="text-paragraph">
                <span class="text-paragraph-label">Harapan dari Hubungan Pernikahan:</span>
                <p>{{ $ans['harapan_pernikahan'] }}</p>
            </div>
            @endif
            @endif

            {{-- Khusus Form Anak: Profil Orang Tua --}}
            @if(!empty($ans['nama_ayah']) || !empty($ans['nama_ibu']))
            <div class="section-header">DATA PROFIL ORANG TUA (KLIEN ANAK)</div>
            @if(!empty($ans['nama_ayah']))
            <div class="sub-header">Data Ayah</div>
            <div class="field-row">
                <span class="field-label">Nama Lengkap Ayah</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['nama_ayah'] }} {{ !empty($ans['usia_ayah']) ? '(' . $ans['usia_ayah'] . ' tahun)' : '' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Pekerjaan & Pendidikan Ayah</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['pekerjaan_ayah'] ?? '-' }} / {{ $ans['pendidikan_ayah'] ?? '-' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Nomor Telepon Ayah</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['phone_ayah'] ?? '-' }}</span>
            </div>
            @endif
            @if(!empty($ans['nama_ibu']))
            <div class="sub-header">Data Ibu</div>
            <div class="field-row">
                <span class="field-label">Nama Lengkap Ibu</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['nama_ibu'] }} {{ !empty($ans['usia_ibu']) ? '(' . $ans['usia_ibu'] . ' tahun)' : '' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Pekerjaan & Pendidikan Ibu</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['pekerjaan_ibu'] ?? '-' }} / {{ $ans['pendidikan_ibu'] ?? '-' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Nomor Telepon Ibu</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['phone_ibu'] ?? '-' }}</span>
            </div>
            @endif
            @endif

            {{-- Informasi Tambahan & Preferensi --}}
            <div class="section-header">INFORMASI TAMBAHAN & PREFERENSI LAYANAN</div>
            <div class="field-row">
                <span class="field-label">Pernah Konseling Sebelumnya</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['pernah_konseling'] ?? '-' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Nama Konselor Terdahulu</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['nama_konselor'] ?? $ans['nama_konselor_lama'] ?? '-' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label">Kontak Darurat</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $ans['kontak_darurat'] ?? '-' }}</span>
            </div>

        @endif

        {{-- ========================================================================= --}}
        {{-- TANDA TANGAN & PENGESAHAN DOKUMEN (MICROSOFT WORD STYLE)                  --}}
        {{-- ========================================================================= --}}
        <div class="signature-section">
            <div class="signature-grid">
                <div class="signature-col">
                    <p>Pendaftar / Klien,</p>
                    <div class="signature-space"></div>
                    <p class="signature-name">{{ $clientForm->client_name ?? $clientForm->client?->name ?? 'Klien' }}</p>
                    <p class="signature-note">(Tervalidasi Digital pada Sistem)</p>
                </div>
                <div class="signature-col">
                    <p>Surabaya, {{ now()->format('d F Y') }}<br>Pusat Layanan Psikologi (UC PSC),</p>
                    <div class="signature-space"></div>
                    <p class="signature-name">Petugas Administrasi Layanan</p>
                    <p class="signature-note">Universitas Ciputra Surabaya</p>
                </div>
            </div>
        </div>

        {{-- FOOTER DOKUMEN --}}
        <div class="doc-page-footer">
            <span>Sistem Informasi Pusat Layanan Psikologi (UC PSC)</span>
            <span>Nomor Dokumen: {{ $clientForm->ticket_number ?? 'DRAFT' }}</span>
        </div>

    </div>

    {{-- Script untuk auto trigger dialog print jika dibuka langsung --}}
    <script>
        window.addEventListener('load', function() {
            if (window.self === window.top) {
                setTimeout(function() {
                    window.print();
                }, 300);
            }
        });
    </script>
</body>
</html>
