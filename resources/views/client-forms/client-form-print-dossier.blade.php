@php
    $formatBirthPlaceDate = function($place, $date, $fallback = null) {
        $p = trim((string)($place ?? ''));
        $d = trim((string)($date ?? ''));
        if (empty($p) && empty($d) && !empty($fallback)) {
            $fb = trim((string)$fallback);
            if (str_contains($fb, ',')) {
                $parts = explode(',', $fb, 2);
                $p = trim($parts[0]);
                $d = trim($parts[1]);
            } else {
                return $fb;
            }
        }

        $formattedDate = '';
        if (!empty($d)) {
            try {
                $carbon = \Carbon\Carbon::parse($d);
                $formattedDate = $carbon->locale('id')->isoFormat('D MMMM Y');
            } catch (\Exception $e) {
                $formattedDate = $d;
            }
        }

        if (!empty($p) && !empty($formattedDate)) {
            return "{$p}, {$formattedDate}";
        } elseif (!empty($p)) {
            return $p;
        } elseif (!empty($formattedDate)) {
            return $formattedDate;
        }
        return '-';
    };
@endphp
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
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #111111;
            margin-top: 3px;
        }
        .kop-meta {
            font-size: 9pt;
            color: #555555;
            margin-top: 5px;
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
            word-break: break-word;
        }
        /* Sub-field menjorok ke dalam (misal anggota keluarga) */
        .field-row-sub {
            display: flex;
            align-items: flex-start;
            margin-bottom: 4px;
            line-height: 1.35;
            padding-left: 16px;
        }
        .field-row-sub .field-label {
            width: 199px;
            color: #333333;
        }
        /* Format Paragraf / Isian Panjang Word Style */
        .text-paragraph {
            margin-top: 4px;
            margin-bottom: 10px;
            text-align: justify;
            line-height: 1.4;
        }
        .text-paragraph-label {
            font-weight: normal;
            color: #111111;
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

        {{-- KOP SURAT & JUDUL BERKAS --}}
        <div class="kop-surat">
            <div class="kop-surat-logo">
                <img src="{{ asset('images/logo-ucpsc.png') }}" alt="Logo UC PSC">
            </div>
            <div class="kop-surat-text">
                <div class="kop-title-main">BERKAS FORMULIR PENDAFTARAN & RIWAYAT HIDUP</div>
                <div class="kop-title-sub">{{ $clientForm->form_type_label }}</div>
                <div class="kop-meta">
                    Nomor Berkas: {{ $clientForm->ticket_number ?? 'DRAFT' }} &nbsp;|&nbsp; 
                    Waktu Pengisian: {{ $clientForm->created_at ? $clientForm->created_at->format('d F Y, H:i') . ' WIB' : '-' }}
                </div>
            </div>
        </div>
@php
$ans = $clientForm->answers ?? [];
$ft = $clientForm->form_type;
@endphp

{{-- ========================================================================= --}}

{{-- FORMULIR 1: KONSELING DEWASA                                              --}}

{{-- ========================================================================= --}}

@if($ft === 'dewasa')

{{-- 1. LEMBAR PERSETUJUAN --}}
<div class="section-header">LEMBAR PERSETUJUAN (INFORMED CONSENT)</div>
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
<span class="field-value">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</span>
</div>

{{-- 2. DATA IDENTITAS & DIRI --}}
<div class="section-header">DATA IDENTITAS & DIRI KLIEN</div>
<div class="field-row">
<span class="field-label">Nama Lengkap & Gelar</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_lengkap'] ?? $clientForm->client_name ?? $clientForm->client?->name ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jenis_kelamin'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Tempat, Tanggal Lahir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate($ans['tempat_lahir'] ?? null, $ans['tanggal_lahir'] ?? null, $ans['tempat_tanggal_lahir'] ?? null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon</span>
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
<span class="field-value">{{ $ans['agama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Suku Bangsa</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['suku_bangsa'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_terakhir'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Hobi / Minat Kegemaran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['hobi'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat'] ?? '-' }}</span>
</div>

{{-- 3. ALASAN KONSELING & KELUHAN UTAMA --}}
<div class="section-header">ALASAN KONSELING & KELUHAN UTAMA</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Alasan Ingin Melakukan Konseling / Keluhan:</span>
<p>{{ !empty($ans['alasan_konseling']) ? $ans['alasan_konseling'] : '-' }}</p>
</div>

{{-- 4. DATA KONDISI PSIKOLOGIS --}}
<div class="section-header">DATA KONDISI PSIKOLOGIS</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Gambaran Kondisi Saat Ini terhadap Permasalahan yang Dirasakan:</span>
<p>{{ !empty($ans['kondisi_saat_ini']) ? $ans['kondisi_saat_ini'] : '-' }}</p>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Pikiran-Pikiran Negatif yang Sering Muncul Akhir-Akhir Ini:</span>
<p>{{ !empty($ans['pikiran_negatif']) ? $ans['pikiran_negatif'] : '-' }}</p>
</div>
<div class="field-row">
<span class="field-label">Hal-Hal yang Ingin Ditingkatkan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['hal_ingin_ditingkatkan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Karakter Kepribadian</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['karakter_kepribadian'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Rata-Rata Jam Tidur per Malam</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jam_tidur'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Ketakutan / Phobia Tertentu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['ketakutan_phobia'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Trauma Masa Lalu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_trauma'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Kesehatan Medis</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_kesehatan'] ?? '-' }}</span>
</div>

{{-- 5. GAMBARAN RELASI INTERPERSONAL --}}
<div class="section-header">GAMBARAN RELASI INTERPERSONAL</div>
<div class="field-row">
<span class="field-label">Relasi dengan Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Relasi dengan Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Relasi dengan Saudara Kandung</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_saudara'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Relasi dengan Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Relasi dengan Anak</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_anak'] ?? '-' }}</span>
</div>

{{-- 6. INFORMASI TAMBAHAN & PREFERENSI --}}
<div class="section-header">INFORMASI TAMBAHAN & PREFERENSI LAYANAN</div>
<div class="field-row">
<span class="field-label">Pernah Konseling Sebelumnya?</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernah_konseling'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Nama Konselor Lama</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_konselor'] ?? $ans['nama_konselor_lama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kontak Darurat</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kontak_darurat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Sumber Mengetahui UC PSC</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['sumber_info'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Preferensi Proses Konseling</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['preferensi_konseling'] ?? '-' }}</span>
</div>

{{-- ========================================================================= --}}

{{-- FORMULIR 2: KONSELING ANAK                                                --}}

{{-- ========================================================================= --}}
@elseif($ft === 'anak')

{{-- 1. LEMBAR PERSETUJUAN ORANG TUA / WALI --}}
<div class="section-header">LEMBAR PERSETUJUAN ORANG TUA / WALI</div>
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
<span class="field-value">Orang tua / wali menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling anak di UC PSC.</span>
</div>

{{-- 2. DATA DIRI ANAK --}}
<div class="section-header">DATA DIRI ANAK</div>
<div class="field-row">
<span class="field-label">Nama Lengkap Anak</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_lengkap'] ?? $clientForm->client_name ?? $clientForm->client?->name ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin Anak</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jenis_kelamin'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Tempat, Tanggal Lahir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate($ans['tempat_lahir'] ?? null, $ans['tanggal_lahir'] ?? null, $ans['tempat_tanggal_lahir'] ?? null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Tempat Tinggal Anak</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon</span>
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
<span class="field-value">{{ $ans['agama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Suku Bangsa</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['suku_bangsa'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir / Kelas</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_terakhir'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Status Pernikahan Orang Tua</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['status_pernikahan_ortu'] ?? '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Alasan Anak Membutuhkan Konseling:</span>
<p>{{ !empty($ans['alasan_konseling']) ? $ans['alasan_konseling'] : '-' }}</p>
</div>

{{-- 3. DATA KONDISI PSIKOLOGIS ANAK --}}
<div class="section-header">DATA KONDISI PSIKOLOGIS ANAK</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Gambaran Kondisi Anak Saat Ini:</span>
<p>{{ !empty($ans['kondisi_anak_saat_ini']) ? $ans['kondisi_anak_saat_ini'] : ($ans['kondisi_saat_ini'] ?? '-') }}</p>
</div>
<div class="field-row">
<span class="field-label">Hal yang Ingin Ditingkatkan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['hal_ingin_ditingkatkan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Karakter Kepribadian Anak</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['karakter_anak'] ?? $ans['karakter_kepribadian'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Prestasi / Minat Bakat Anak</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['prestasi_anak'] ?? $ans['prestasi'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Ketakutan / Phobia Anak</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['ketakutan_phobia'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Trauma Masa Lalu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_trauma'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Kesehatan Medis</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_kesehatan'] ?? '-' }}</span>
</div>

{{-- 4. GAMBARAN RELASI ANAK --}}
<div class="section-header">GAMBARAN RELASI ANAK</div>
<div class="field-row">
<span class="field-label">Relasi Anak dengan Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Relasi Anak dengan Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Relasi dengan Saudara</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_saudara'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Relasi dengan Teman Sebaya</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['relasi_teman'] ?? '-' }}</span>
</div>

{{-- 5. DATA ORANG TUA (AYAH) --}}
<div class="section-header">DATA ORANG TUA (AYAH)</div>
<div class="field-row">
<span class="field-label">Nama Lengkap Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Usia Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ !empty($ans['usia_ayah']) ? $ans['usia_ayah'] . ' tahun' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Agama Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['agama_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon / HP Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['phone_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pernikahan Ayah Ke-</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernikahan_ayah_ke'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jumlah Anak Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jumlah_anak_ayah'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Status Pernikahan Ayah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['status_nikah_ayah'] ?? '-' }}</span>
</div>

{{-- 6. DATA ORANG TUA (IBU) --}}
<div class="section-header">DATA ORANG TUA (IBU)</div>
<div class="field-row">
<span class="field-label">Nama Lengkap Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Usia Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ !empty($ans['usia_ibu']) ? $ans['usia_ibu'] . ' tahun' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Agama Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['agama_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon / HP Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['phone_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pernikahan Ibu Ke-</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernikahan_ibu_ke'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jumlah Anak Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jumlah_anak_ibu'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Status Pernikahan Ibu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['status_nikah_ibu'] ?? '-' }}</span>
</div>

{{-- 7. LAIN-LAIN & PREFERENSI --}}
<div class="section-header">LAIN-LAIN & PREFERENSI LAYANAN</div>
<div class="field-row">
<span class="field-label">Anak Pernah Konseling Sebelumnya?</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernah_konseling'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Nama Konselor Lama</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_konselor_lama'] ?? $ans['nama_konselor'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kontak Darurat</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kontak_darurat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Sumber Mengetahui UC PSC</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['sumber_info'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Preferensi Proses Konseling</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['preferensi_konseling'] ?? '-' }}</span>
</div>

{{-- ========================================================================= --}}

{{-- FORMULIR 3: KONSELING PRA NIKAH                                           --}}

{{-- ========================================================================= --}}
@elseif($ft === 'pra_nikah')

{{-- 1. LEMBAR PERSETUJUAN --}}
<div class="section-header">LEMBAR PERSETUJUAN (INFORMED CONSENT)</div>
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
<span class="field-value">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</span>
</div>

{{-- 2. DATA DIRI PENDAFTAR --}}
<div class="section-header">DATA DIRI ANDA (PENDAFTAR)</div>
<div class="field-row">
<span class="field-label">Nama Lengkap & Gelar</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_lengkap'] ?? $clientForm->client_name ?? $clientForm->client?->name ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jenis_kelamin'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Tempat, Tanggal Lahir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate($ans['tempat_lahir'] ?? null, $ans['tanggal_lahir'] ?? null, $ans['tempat_tanggal_lahir'] ?? null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Tempat Tinggal Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon</span>
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
<span class="field-value">{{ $ans['agama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Suku Bangsa</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['suku_bangsa'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_terakhir'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Rencana Pernikahan Ini Ke-</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernikahan_ke'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jumlah Anak Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jumlah_anak'] ?? '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Alasan Mengikuti Konseling Pra Nikah:</span>
<p>{{ !empty($ans['alasan_konseling']) ? $ans['alasan_konseling'] : '-' }}</p>
</div>

{{-- 3. DATA DIRI CALON PASANGAN --}}
<div class="section-header">DATA DIRI CALON PASANGAN</div>
<div class="field-row">
<span class="field-label">Nama Calon Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin Calon Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jenis_kelamin_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Tempat, Tanggal Lahir Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate($ans['tempat_lahir_pasangan'] ?? null, $ans['tanggal_lahir_pasangan'] ?? null, null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Tempat Tinggal Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['phone_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Email Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['email_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Agama Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['agama_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Suku Bangsa Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['suku_bangsa_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_terakhir_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Bagi Pasangan, Pernikahan Ke-</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernikahan_pasangan_ke'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jumlah Anak Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jumlah_anak_pasangan'] ?? '-' }}</span>
</div>

{{-- 4. DATA RENCANA PERNIKAHAN & KONDISI HUBUNGAN --}}
<div class="section-header">DATA RENCANA PERNIKAHAN & KONDISI HUBUNGAN</div>
<div class="field-row">
<span class="field-label">Tanggal Rencana Pernikahan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['tanggal_rencana_pernikahan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Lama Waktu Berkenalan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['lama_berkenalan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Lama Waktu Berpacaran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['lama_berpacaran'] ?? '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Harapan terhadap Pernikahan:</span>
<p>{{ !empty($ans['harapan_pernikahan']) ? $ans['harapan_pernikahan'] : '-' }}</p>
</div>
<div class="field-row">
<span class="field-label">Hal yang Ingin Ditingkatkan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['hal_ingin_ditingkatkan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kelebihan Peran bagi Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kelebihan_peran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kekurangan Peran bagi Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kekurangan_peran'] ?? '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Keluhan atau Konflik dalam Hubungan yang Sering Muncul:</span>
<p>{{ !empty($ans['keluhan_hubungan']) ? $ans['keluhan_hubungan'] : '-' }}</p>
</div>
<div class="field-row">
<span class="field-label">Riwayat Trauma Masa Lalu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_trauma'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Penyakit Medis</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_kesehatan'] ?? '-' }}</span>
</div>

{{-- 5. LAIN-LAIN & PREFERENSI --}}
<div class="section-header">LAIN-LAIN & PREFERENSI LAYANAN</div>
<div class="field-row">
<span class="field-label">Pernah Konseling Sebelumnya?</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernah_konseling'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Nama Konselor Lama</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_konselor'] ?? $ans['nama_konselor_lama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kontak Darurat</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kontak_darurat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Sumber Mengetahui UC PSC</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['sumber_info'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Preferensi Proses Konseling</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['preferensi_konseling'] ?? '-' }}</span>
</div>

{{-- ========================================================================= --}}

{{-- FORMULIR 4: KONSELING PERNIKAHAN                                          --}}

{{-- ========================================================================= --}}
@elseif($ft === 'pernikahan')

{{-- 1. LEMBAR PERSETUJUAN --}}
<div class="section-header">LEMBAR PERSETUJUAN (INFORMED CONSENT)</div>
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
<span class="field-value">Klien menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan layanan konseling UC PSC.</span>
</div>

{{-- 2. DATA DIRI PENDAFTAR --}}
<div class="section-header">DATA DIRI ANDA (PENDAFTAR)</div>
<div class="field-row">
<span class="field-label">Nama Lengkap & Gelar</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_lengkap'] ?? $clientForm->client_name ?? $clientForm->client?->name ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jenis_kelamin'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Tempat, Tanggal Lahir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate($ans['tempat_lahir'] ?? null, $ans['tanggal_lahir'] ?? null, $ans['tempat_tanggal_lahir'] ?? null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Tempat Tinggal Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon</span>
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
<span class="field-value">{{ $ans['agama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Suku Bangsa</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['suku_bangsa'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_terakhir'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pernikahan Ini Adalah Ke-</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernikahan_ke'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jumlah Anak yang Dimiliki</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jumlah_anak'] ?? '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Alasan Mengikuti Konseling Pernikahan:</span>
<p>{{ !empty($ans['alasan_konseling']) ? $ans['alasan_konseling'] : '-' }}</p>
</div>

{{-- 3. DATA DIRI PASANGAN --}}
<div class="section-header">DATA DIRI PASANGAN (SUAMI/ISTRI)</div>
<div class="field-row">
<span class="field-label">Nama Lengkap Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jenis_kelamin_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Tempat, Tanggal Lahir Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate($ans['tempat_lahir_pasangan'] ?? null, $ans['tanggal_lahir_pasangan'] ?? null, null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Tempat Tinggal Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['phone_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Email Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['email_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Agama Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['agama_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Suku Bangsa Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['suku_bangsa_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_terakhir_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan_pasangan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Bagi Pasangan, Pernikahan Ini Ke-</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernikahan_pasangan_ke'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jumlah Anak yang Dimiliki Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jumlah_anak_pasangan'] ?? '-' }}</span>
</div>

{{-- 4. DATA PERNIKAHAN & DINAMIKA HUBUNGAN --}}
<div class="section-header">DATA PERNIKAHAN & DINAMIKA HUBUNGAN</div>
<div class="field-row">
<span class="field-label">Tempat & Tanggal Pernikahan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['tempat_tanggal_pernikahan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Usia Pernikahan Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['lama_pernikahan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Lama Berkenalan Sebelum Nikah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['lama_berkenalan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Lama Waktu Berpacaran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['lama_berpacaran'] ?? '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Keluhan Utama / Masalah Pernikahan:</span>
<p>{{ !empty($ans['keluhan_utama']) ? $ans['keluhan_utama'] : '-' }}</p>
</div>
<div class="field-row">
<span class="field-label">Kemungkinan untuk Diperbaiki</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kemungkinan_perbaikan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Hal yang Ingin Ditingkatkan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['hal_ingin_ditingkatkan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kelebihan Peran bagi Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kelebihan_peran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kekurangan Peran bagi Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kekurangan_peran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Trauma Masa Lalu</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_trauma'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Penyakit Medis</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_kesehatan'] ?? '-' }}</span>
</div>

{{-- 5. DATA ANAK --}}
<div class="section-header">DATA ANAK (1 S.D. 3)</div>
@php $hasAnak = false; @endphp
@foreach(range(1, 3) as $ankIdx)

@if(!empty($ans["anak_{$ankIdx}_nama"]))
@php $hasAnak = true; @endphp
<div class="item-block">
<div class="item-block-title">Anak Ke-{{ $ankIdx }}: {{ $ans["anak_{$ankIdx}_nama"] }}</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin / Usia</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["anak_{$ankIdx}_jenis_kelamin"] ?? '-' }} / {{ !empty($ans["anak_{$ankIdx}_usia"]) ? $ans["anak_{$ankIdx}_usia"] . ' tahun' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["anak_{$ankIdx}_pendidikan"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasAnak)
<p style="color: #666; font-style: italic;">Tidak ada catatan data anak.</p>
@endif

{{-- 6. LAIN-LAIN & PREFERENSI --}}
<div class="section-header">LAIN-LAIN & PREFERENSI LAYANAN</div>
<div class="field-row">
<span class="field-label">Pernah Konseling Pernikahan Sebelumnya?</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pernah_konseling'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Nama Konselor Lama</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_konselor'] ?? $ans['nama_konselor_lama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kontak Darurat</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['kontak_darurat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Sumber Mengetahui UC PSC</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['sumber_info'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Preferensi Proses Konseling</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['preferensi_konseling'] ?? '-' }}</span>
</div>

{{-- ========================================================================= --}}

{{-- FORMULIR 5: INDUSTRI                                                      --}}

{{-- ========================================================================= --}}
@elseif($ft === 'industri')

{{-- 1. PERNYATAAN KEJUJURAN --}}
<div class="section-header">LEMBAR PERNYATAAN KEJUJURAN</div>
<div class="field-row">
<span class="field-label">Status Persetujuan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['consent_agree'] ?? 'Setuju' }}</span>
</div>
<div class="field-row">
<span class="field-label">Waktu Validasi</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $clientForm->created_at ? $clientForm->created_at->format('d F Y, H:i:s') . ' WIB' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Keterangan Pernyataan</span>
<span class="field-colon">:</span>
<span class="field-value">"Semua keterangan yang saya berikan dalam Formulir Riwayat Hidup ini, saya buat dengan jujur dan sungguh-sungguh."</span>
</div>

{{-- 2. DATA DIRI & POSISI PEKERJAAN --}}
<div class="section-header">DATA DIRI & POSISI PEKERJAAN</div>
<div class="field-row">
<span class="field-label">Nama Lengkap & Gelar</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_lengkap'] ?? $clientForm->client_name ?? $clientForm->client?->name ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Posisi yang Dituju</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['posisi_dituju'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jenis_kelamin'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Tempat, Tanggal Lahir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate($ans['tempat_lahir'] ?? null, $ans['tanggal_lahir'] ?? null, $ans['tempat_tanggal_lahir'] ?? null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon</span>
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
<span class="field-value">{{ $ans['agama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Suku Bangsa</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['suku_bangsa'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_terakhir'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Tempat Tinggal</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat'] ?? '-' }}</span>
</div>

{{-- 3. KUESIONER STRES PSS-10 --}}
<div class="section-header">KUESIONER TINGKAT STRES (PSS-10)</div>
<p style="font-size: 9pt; color: #444; margin-bottom: 6px;">Skala penilaian: 0 = Tidak Pernah, 1 = Hampir Tidak Pernah, 2 = Kadang-kadang, 3 = Cukup Sering, 4 = Sangat Sering.</p>
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
<span>{{ $pssText }}</span> &nbsp;—&nbsp; <strong>Skor: {{ isset($ans["pss_{$pssIdx}"]) && $ans["pss_{$pssIdx}"] !== '' ? $ans["pss_{$pssIdx}"] : '-' }}</strong>
</li>
@endforeach
</ol>

{{-- 4. SUSUNAN ANGGOTA KELUARGA --}}
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

@if(!empty($ans['pasangan_nama']))
<div class="sub-header">Data Pasangan</div>
<div class="field-row">
<span class="field-label">Nama Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pasangan_nama'] }} {{ !empty($ans['pasangan_usia']) ? '(' . $ans['pasangan_usia'] . ' tahun)' : '' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan & Pekerjaan Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pasangan_pendidikan'] ?? '-' }} / {{ $ans['pasangan_pekerjaan'] ?? '-' }}</span>
</div>
@endif
<div class="sub-header">Data Saudara Kandung</div>
@php $hasSdr = false; @endphp
@foreach(range(1, 5) as $sdrIdx)

@if(!empty($ans["saudara_{$sdrIdx}_nama"]))
@php $hasSdr = true; @endphp
<div class="item-block">
<div class="item-block-title">Saudara {{ $sdrIdx }}: {{ $ans["saudara_{$sdrIdx}_nama"] }}</div>
<div class="field-row">
<span class="field-label">Gender / Usia</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["saudara_{$sdrIdx}_jenis_kelamin"] ?? '-' }} / {{ !empty($ans["saudara_{$sdrIdx}_usia"]) ? $ans["saudara_{$sdrIdx}_usia"] . ' tahun' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan / Pekerjaan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["saudara_{$sdrIdx}_pendidikan"] ?? '-' }} / {{ $ans["saudara_{$sdrIdx}_pekerjaan"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasSdr)
<p style="color: #666; font-style: italic; margin-bottom: 6px;">Tidak ada catatan saudara kandung.</p>
@endif

@if(!empty($ans['anak_1_nama']))
<div class="sub-header">Data Anak</div>

@foreach(range(1, 5) as $ankIdx)

@if(!empty($ans["anak_{$ankIdx}_nama"]))
<div class="item-block">
<div class="item-block-title">Anak {{ $ankIdx }}: {{ $ans["anak_{$ankIdx}_nama"] }}</div>
<div class="field-row">
<span class="field-label">Gender / Usia</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["anak_{$ankIdx}_jenis_kelamin"] ?? '-' }} / {{ !empty($ans["anak_{$ankIdx}_usia"]) ? $ans["anak_{$ankIdx}_usia"] . ' tahun' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan / Pekerjaan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["anak_{$ankIdx}_pendidikan"] ?? '-' }} / {{ $ans["anak_{$ankIdx}_pekerjaan"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach
@endif

{{-- 5. RIWAYAT PENDIDIKAN FORMAL --}}
<div class="section-header">RIWAYAT PENDIDIKAN FORMAL</div>
<div class="field-row">
<span class="field-label">IPK Terakhir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['ipk_terakhir'] ?? '-' }}</span>
</div>
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
<span class="field-label">Kota Sekolah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["kota_sekolah_{$eduIdx}"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasEdu)
<p style="color: #666; font-style: italic;">Tidak ada catatan riwayat pendidikan formal tambahan.</p>
@endif

{{-- 6. PENDIDIKAN NON FORMAL --}}
<div class="section-header">PENDIDIKAN NON FORMAL / KURSUS / PELATIHAN</div>
@php $hasKrs = false; @endphp
@foreach(range(1, 3) as $krsIdx)

@if(!empty($ans["jenis_kursus_{$krsIdx}"]))
@php $hasKrs = true; @endphp
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

@if(!$hasKrs)
<p style="color: #666; font-style: italic;">Tidak ada catatan pendidikan non-formal.</p>
@endif

{{-- 7. RIWAYAT PEKERJAAN --}}
<div class="section-header">RIWAYAT PEKERJAAN & PENGALAMAN KERJA</div>
<div class="field-row">
<span class="field-label">Ekspektasi Gaji & Tunjangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['ekspektasi_gaji_tunjangan'] ?? '-' }}</span>
</div>
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

{{-- 8. PENGALAMAN ORGANISASI --}}
<div class="section-header">PENGALAMAN ORGANISASI</div>
@php $hasOrg = false; @endphp
@foreach(range(1, 3) as $orgIdx)

@if(!empty($ans["organisasi_{$orgIdx}_nama"]))
@php $hasOrg = true; @endphp
<div class="field-row">
<span class="field-label">{{ $ans["organisasi_{$orgIdx}_nama"] }}</span>
<span class="field-colon">:</span>
<span class="field-value">Jabatan: {{ $ans["organisasi_{$orgIdx}_jabatan"] ?? '-' }} (Periode: {{ $ans["organisasi_{$orgIdx}_lama"] ?? '-' }})</span>
</div>
@endif
@endforeach

@if(!$hasOrg)
<p style="color: #666; font-style: italic;">Tidak ada pengalaman organisasi tercatat.</p>
@endif

{{-- 9. PRESTASI --}}
<div class="section-header">PRESTASI YANG PERNAH DIRAIH</div>
<div class="text-paragraph">
<p>{{ !empty($ans['prestasi']) ? $ans['prestasi'] : 'Tidak ada prestasi tercatat.' }}</p>
</div>

{{-- 10. DESKRIPSI DIRI & RIWAYAT KESEHATAN --}}
<div class="section-header">DESKRIPSI DIRI & RIWAYAT KESEHATAN</div>
<div class="field-row">
<span class="field-label">Riwayat Peristiwa Trauma</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_trauma'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Penyakit Opname / Rawat Inap</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_penyakit_opname'] ?? $ans['riwayat_penyakit_berat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pernah Konsultasi Psikolog</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['konsultasi_psikolog'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">3 Kelebihan Diri</span>
<span class="field-colon">:</span>
<span class="field-value">{{ implode(', ', array_filter([$ans['kelebihan_1'] ?? null, $ans['kelebihan_2'] ?? null, $ans['kelebihan_3'] ?? null])) ?: '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">3 Kelemahan Diri</span>
<span class="field-colon">:</span>
<span class="field-value">{{ implode(', ', array_filter([$ans['kelemahan_1'] ?? null, $ans['kelemahan_2'] ?? null, $ans['kelemahan_3'] ?? null])) ?: '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Hobi / Minat Kegemaran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['hobi'] ?? '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Deskripsi Diri Bebas:</span>
<p>{{ !empty($ans['deskripsi_diri_bebas']) ? $ans['deskripsi_diri_bebas'] : '-' }}</p>
</div>

{{-- ========================================================================= --}}

{{-- FORMULIR 6: NON INDUSTRI                                                  --}}

{{-- ========================================================================= --}}
@elseif($ft === 'non_industri')

{{-- 1. PERNYATAAN KEJUJURAN --}}
<div class="section-header">LEMBAR PERNYATAAN KEJUJURAN</div>
<div class="field-row">
<span class="field-label">Status Persetujuan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['consent_agree'] ?? 'Setuju' }}</span>
</div>
<div class="field-row">
<span class="field-label">Waktu Validasi</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $clientForm->created_at ? $clientForm->created_at->format('d F Y, H:i:s') . ' WIB' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Keterangan Pernyataan</span>
<span class="field-colon">:</span>
<span class="field-value">"Semua keterangan yang saya berikan dalam Formulir Riwayat Hidup ini, saya buat dengan jujur dan sungguh-sungguh."</span>
</div>

{{-- 2. DATA DIRI --}}
<div class="section-header">DATA DIRI</div>
<div class="field-row">
<span class="field-label">Nama Lengkap & Gelar</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['nama_lengkap'] ?? $clientForm->client_name ?? $clientForm->client?->name ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Jenis Kelamin</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['jenis_kelamin'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Tempat, Tanggal Lahir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate($ans['tempat_lahir'] ?? null, $ans['tanggal_lahir'] ?? null, $ans['tempat_tanggal_lahir'] ?? null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Urutan Kelahiran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['urutan_kelahiran'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Alamat Tempat Tinggal</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['alamat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon</span>
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
<span class="field-value">{{ $ans['agama'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Suku Bangsa</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['suku_bangsa'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan Terakhir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pendidikan_terakhir'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pekerjaan Saat Ini</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pekerjaan'] ?? '-' }}</span>
</div>

{{-- 3. DATA KELUARGA --}}
<div class="section-header">DATA KELUARGA</div>
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

@if(!empty($ans['pasangan_nama']))
<div class="sub-header">Data Pasangan</div>
<div class="field-row">
<span class="field-label">Nama Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pasangan_nama'] }} {{ !empty($ans['pasangan_usia']) ? '(' . $ans['pasangan_usia'] . ' tahun)' : '' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan & Pekerjaan Pasangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['pasangan_pendidikan'] ?? '-' }} / {{ $ans['pasangan_pekerjaan'] ?? '-' }}</span>
</div>
@endif
<div class="sub-header">Data Saudara Kandung</div>
@php $hasSdrNon = false; @endphp
@foreach(range(1, 5) as $sdrIdx)

@if(!empty($ans["saudara_{$sdrIdx}_nama"]))
@php $hasSdrNon = true; @endphp
<div class="item-block">
<div class="item-block-title">Saudara {{ $sdrIdx }}: {{ $ans["saudara_{$sdrIdx}_nama"] }}</div>
<div class="field-row">
<span class="field-label">Gender / Usia</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["saudara_{$sdrIdx}_jenis_kelamin"] ?? '-' }} / {{ !empty($ans["saudara_{$sdrIdx}_usia"]) ? $ans["saudara_{$sdrIdx}_usia"] . ' tahun' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan / Pekerjaan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["saudara_{$sdrIdx}_pendidikan"] ?? '-' }} / {{ $ans["saudara_{$sdrIdx}_pekerjaan"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasSdrNon)
<p style="color: #666; font-style: italic; margin-bottom: 6px;">Tidak ada catatan saudara kandung.</p>
@endif

@if(!empty($ans['anak_1_nama']))
<div class="sub-header">Data Anak</div>

@foreach(range(1, 5) as $ankIdx)

@if(!empty($ans["anak_{$ankIdx}_nama"]))
<div class="item-block">
<div class="item-block-title">Anak {{ $ankIdx }}: {{ $ans["anak_{$ankIdx}_nama"] }}</div>
<div class="field-row">
<span class="field-label">Gender / Usia</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["anak_{$ankIdx}_jenis_kelamin"] ?? '-' }} / {{ !empty($ans["anak_{$ankIdx}_usia"]) ? $ans["anak_{$ankIdx}_usia"] . ' tahun' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pendidikan / Pekerjaan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["anak_{$ankIdx}_pendidikan"] ?? '-' }} / {{ $ans["anak_{$ankIdx}_pekerjaan"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach
@endif

{{-- 4. RIWAYAT PENDIDIKAN FORMAL --}}
<div class="section-header">RIWAYAT PENDIDIKAN FORMAL</div>
<div class="field-row">
<span class="field-label">IPK Terakhir</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['ipk_terakhir'] ?? '-' }}</span>
</div>
@php $hasEduNon = false; @endphp
@foreach(range(1, 5) as $eduIdx)

@if(!empty($ans["nama_sekolah_{$eduIdx}"]))
@php $hasEduNon = true; @endphp
<div class="item-block">
<div class="item-block-title">{{ $ans["nama_sekolah_{$eduIdx}"] }} ({{ $ans["tahun_masuk_sekolah_{$eduIdx}"] ?? '-' }} - {{ $ans["tahun_keluar_sekolah_{$eduIdx}"] ?? '-' }})</div>
<div class="field-row">
<span class="field-label">Jurusan / Keterangan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["keterangan_sekolah_{$eduIdx}"] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Kota Sekolah</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["kota_sekolah_{$eduIdx}"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasEduNon)
<p style="color: #666; font-style: italic;">Tidak ada catatan riwayat pendidikan formal tambahan.</p>
@endif

{{-- 5. PENDIDIKAN NON FORMAL --}}
<div class="section-header">PENDIDIKAN NON FORMAL / KURSUS</div>
@php $hasKrsNon = false; @endphp
@foreach(range(1, 3) as $krsIdx)

@if(!empty($ans["jenis_kursus_{$krsIdx}"]))
@php $hasKrsNon = true; @endphp
<div class="item-block">
<div class="item-block-title">{{ $ans["jenis_kursus_{$krsIdx}"] }}</div>
<div class="field-row">
<span class="field-label">Tempat Penyelenggara</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["tempat_kursus_{$krsIdx}"] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Lama Pelatihan</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["lama_kursus_{$krsIdx}"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasKrsNon)
<p style="color: #666; font-style: italic;">Tidak ada catatan pendidikan non-formal.</p>
@endif

{{-- 6. PENGALAMAN ORGANISASI --}}
<div class="section-header">PENGALAMAN ORGANISASI</div>
@php $hasOrgNon = false; @endphp
@foreach(range(1, 3) as $orgIdx)

@if(!empty($ans["organisasi_{$orgIdx}_nama"]))
@php $hasOrgNon = true; @endphp
<div class="field-row">
<span class="field-label">{{ $ans["organisasi_{$orgIdx}_nama"] }}</span>
<span class="field-colon">:</span>
<span class="field-value">Jabatan: {{ $ans["organisasi_{$orgIdx}_jabatan"] ?? '-' }} (Periode: {{ $ans["organisasi_{$orgIdx}_lama"] ?? '-' }})</span>
</div>
@endif
@endforeach

@if(!$hasOrgNon)
<p style="color: #666; font-style: italic;">Tidak ada pengalaman organisasi tercatat.</p>
@endif

{{-- 7. PRESTASI --}}
<div class="section-header">PRESTASI YANG PERNAH DIRAIH</div>
<div class="text-paragraph">
<p>{{ !empty($ans['prestasi']) ? $ans['prestasi'] : 'Tidak ada prestasi tercatat.' }}</p>
</div>

{{-- 8. DESKRIPSI DIRI & MINAT --}}
<div class="section-header">DESKRIPSI DIRI & MINAT</div>
<div class="field-row">
<span class="field-label">Riwayat Peristiwa Trauma</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_trauma'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Riwayat Penyakit Opname / Rawat Inap</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['riwayat_penyakit_opname'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Pernah Konsultasi Psikolog</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['konsultasi_psikolog'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">3 Kelebihan Diri</span>
<span class="field-colon">:</span>
<span class="field-value">{{ implode(', ', array_filter([$ans['kelebihan_1'] ?? null, $ans['kelebihan_2'] ?? null, $ans['kelebihan_3'] ?? null])) ?: '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">3 Kelemahan Diri</span>
<span class="field-colon">:</span>
<span class="field-value">{{ implode(', ', array_filter([$ans['kelemahan_1'] ?? null, $ans['kelemahan_2'] ?? null, $ans['kelemahan_3'] ?? null])) ?: '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Hobi / Minat Kegemaran</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['hobi'] ?? '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Deskripsi Diri Bebas:</span>
<p>{{ !empty($ans['deskripsi_diri_bebas']) ? $ans['deskripsi_diri_bebas'] : '-' }}</p>
</div>

{{-- ========================================================================= --}}

{{-- FORMULIR 7: BIOGRAPHY FORM (ENGLISH)                                      --}}

{{-- ========================================================================= --}}
@elseif($ft === 'biography_en')

{{-- 1. DECLARATION OF AUTHENTICITY --}}
<div class="section-header">DECLARATION OF AUTHENTICITY</div>
<div class="field-row">
<span class="field-label">Declaration Status</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['consent_agree'] ?? 'Agreed' }}</span>
</div>
<div class="field-row">
<span class="field-label">Submission Timestamp</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $clientForm->created_at ? $clientForm->created_at->format('d F Y, H:i:s') . ' WIB' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Statement</span>
<span class="field-colon">:</span>
<span class="field-value">"I declare that all information provided in this Biography Form is true, accurate, and filled with full honesty."</span>
</div>

{{-- 2. PERSONAL INFORMATION --}}
<div class="section-header">PERSONAL INFORMATION</div>
<div class="field-row">
<span class="field-label">Full Name</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['full_name'] ?? $clientForm->client_name ?? $clientForm->client?->name ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Gender</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['gender'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Place and Date of Birth</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $formatBirthPlaceDate(null, null, $ans['birth_place_date'] ?? null) }}</span>
</div>
<div class="field-row">
<span class="field-label">Order of Birth</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['birth_order'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">City of Origin</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['city_of_origin'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Current Address</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['current_address'] ?? $ans['alamat'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">No. Telepon</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $clientForm->client_phone ?? $ans['phone'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Active Email Address</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['email'] ?? $clientForm->client?->email ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Religion / Belief</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['religion'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Ethnicity</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['ethnicity'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Highest Education Level</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['last_education'] ?? '-' }}</span>
</div>

{{-- 3. FAMILY INFORMATION --}}
<div class="section-header">FAMILY INFORMATION</div>
<div class="sub-header">Father's Profile</div>
<div class="field-row">
<span class="field-label">Father's Full Name</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['father_name'] ?? '-' }} {{ !empty($ans['father_age']) ? '(' . $ans['father_age'] . ' years old)' : '' }}</span>
</div>
<div class="field-row">
<span class="field-label">Education & Occupation</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['father_education'] ?? '-' }} / {{ $ans['father_occupation'] ?? '-' }}</span>
</div>
<div class="sub-header">Mother's Profile</div>
<div class="field-row">
<span class="field-label">Mother's Full Name</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['mother_name'] ?? '-' }} {{ !empty($ans['mother_age']) ? '(' . $ans['mother_age'] . ' years old)' : '' }}</span>
</div>
<div class="field-row">
<span class="field-label">Education & Occupation</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['mother_education'] ?? '-' }} / {{ $ans['mother_occupation'] ?? '-' }}</span>
</div>
<div class="sub-header">Siblings Information</div>
@php $hasSib = false; @endphp
@foreach(range(1, 5) as $sibIdx)

@if(!empty($ans["sibling_{$sibIdx}_name"]))
@php $hasSib = true; @endphp
<div class="item-block">
<div class="item-block-title">Sibling {{ $sibIdx }}: {{ $ans["sibling_{$sibIdx}_name"] }}</div>
<div class="field-row">
<span class="field-label">Gender / Age</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["sibling_{$sibIdx}_gender"] ?? '-' }} / {{ !empty($ans["sibling_{$sibIdx}_age"]) ? $ans["sibling_{$sibIdx}_age"] . ' years' : '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Education / Occupation</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["sibling_{$sibIdx}_education"] ?? '-' }} / {{ $ans["sibling_{$sibIdx}_occupation"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasSib)
<p style="color: #666; font-style: italic; margin-bottom: 6px;">No siblings recorded.</p>
@endif

{{-- 4. FORMAL EDUCATION HISTORY --}}
<div class="section-header">FORMAL EDUCATION HISTORY</div>
@php $hasEduEn = false; @endphp
@foreach(range(1, 4) as $eduIdx)

@if(!empty($ans["school_{$eduIdx}_name"]))
@php $hasEduEn = true; @endphp
<div class="item-block">
<div class="item-block-title">{{ $ans["school_{$eduIdx}_name"] }} ({{ $ans["school_{$eduIdx}_year_entry"] ?? '-' }} - {{ $ans["school_{$eduIdx}_year_graduation"] ?? '-' }})</div>
<div class="field-row">
<span class="field-label">City</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["school_{$eduIdx}_city"] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Major / Information</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["school_{$eduIdx}_info"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasEduEn)
<p style="color: #666; font-style: italic;">No formal education entries recorded.</p>
@endif
<div class="field-row" style="margin-top: 6px;">
<span class="field-label">Failed Grade Reason</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['failed_grade_reason'] ?? '-' }}</span>
</div>

{{-- 5. NON-FORMAL EDUCATION --}}
<div class="section-header">NON-FORMAL EDUCATION</div>
@php $hasNonEn = false; @endphp
@foreach(range(1, 3) as $nonIdx)

@if(!empty($ans["non_formal_{$nonIdx}_type"]))
@php $hasNonEn = true; @endphp
<div class="item-block">
<div class="item-block-title">{{ $ans["non_formal_{$nonIdx}_type"] }}</div>
<div class="field-row">
<span class="field-label">Institution / Organizer</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["non_formal_{$nonIdx}_organizer"] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Duration</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans["non_formal_{$nonIdx}_duration"] ?? '-' }}</span>
</div>
</div>
@endif
@endforeach

@if(!$hasNonEn)
<p style="color: #666; font-style: italic;">No non-formal education recorded.</p>
@endif

{{-- 6. ORGANIZATIONAL EXPERIENCE --}}
<div class="section-header">ORGANIZATIONAL EXPERIENCE</div>
@php $hasOrgEn = false; @endphp
@foreach(range(1, 3) as $orgIdx)

@if(!empty($ans["org_{$orgIdx}_name"]))
@php $hasOrgEn = true; @endphp
<div class="field-row">
<span class="field-label">{{ $ans["org_{$orgIdx}_name"] }}</span>
<span class="field-colon">:</span>
<span class="field-value">Position: {{ $ans["org_{$orgIdx}_position"] ?? '-' }} (Period: {{ $ans["org_{$orgIdx}_period"] ?? '-' }})</span>
</div>
@endif
@endforeach

@if(!$hasOrgEn)
<p style="color: #666; font-style: italic;">No organizational experience recorded.</p>
@endif

{{-- 7. ACHIEVEMENTS --}}
<div class="section-header">ACHIEVEMENTS</div>
<div class="text-paragraph">
<p>{{ !empty($ans['achievements']) ? $ans['achievements'] : 'No achievements recorded.' }}</p>
</div>

{{-- 8. SELF DESCRIPTION & CHARACTERISTICS --}}
<div class="section-header">SELF DESCRIPTION & CHARACTERISTICS</div>
<div class="field-row">
<span class="field-label">3 Strengths</span>
<span class="field-colon">:</span>
<span class="field-value">{{ implode(', ', array_filter([$ans['strength_1'] ?? null, $ans['strength_2'] ?? null, $ans['strength_3'] ?? null])) ?: '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">3 Weaknesses</span>
<span class="field-colon">:</span>
<span class="field-value">{{ implode(', ', array_filter([$ans['weakness_1'] ?? null, $ans['weakness_2'] ?? null, $ans['weakness_3'] ?? null])) ?: '-' }}</span>
</div>
<div class="text-paragraph">
<span class="text-paragraph-label">Life Highlights / History:</span>
<p>{{ !empty($ans['life_highlights']) ? $ans['life_highlights'] : '-' }}</p>
</div>

{{-- 9. OTHER INFORMATION --}}
<div class="section-header">OTHER INFORMATION</div>
<div class="field-row">
<span class="field-label">Hobbies / Interests</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['hobbies'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Serious Illness / Hospitalization</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['serious_illness'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Psychological Help History</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['psychological_consultation'] ?? '-' }}</span>
</div>
<div class="field-row">
<span class="field-label">Traumatic Experience</span>
<span class="field-colon">:</span>
<span class="field-value">{{ $ans['traumatic_experience'] ?? '-' }}</span>
</div>
@endif
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