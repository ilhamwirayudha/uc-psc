@extends('layouts.dashboard')

@section('title', 'Hasil Respon Formulir Klien — UC PSC')
@section('page-title', 'Hasil Respon Formulir Klien')
@section('page-subtitle', 'Lembar kerja spreadsheet hasil pengisian formulir pendaftaran mandiri')

@section('content')
<div class="space-y-6" x-data="{
    copiedKey: null,
    selectedForm: null,
    showDossierModal: false,

    copyToClipboard(url, key) {
        navigator.clipboard.writeText(url);
        this.copiedKey = key;
        setTimeout(() => this.copiedKey = null, 2500);
    },

    openDossier(form) {
        this.selectedForm = form;
        this.showDossierModal = true;
    },

    switchCategory(catKey) {
        window.location.href = '{{ route('client-forms.results') }}?category=' + catKey;
    }
}">


    {{-- ============================================================== --}}
    {{-- SPREADSHEET RESPON MASUK (GOOGLE SHEETS STYLE)                 --}}
    {{-- ============================================================== --}}
    <div class="bg-white rounded-3xl border border-[#EDE1FA] shadow-xs overflow-hidden" 
         x-data="{
             exportToCSV() {
                 let table = document.getElementById('sheetsResponseTable');
                 if (!table) return;
                 let rows = table.querySelectorAll('tr');
                 let csv = [];
                 for (let i = 0; i < rows.length; i++) {
                     let row = [], cols = rows[i].querySelectorAll('th, td');
                     for (let j = 0; j < cols.length; j++) {
                         if (cols[j].classList.contains('no-export')) continue;
                         let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/(\s\s+)/gm, ' ');
                         data = data.replace(/\"/g, '\"\"');
                         row.push('\"' + data.trim() + '\"');
                     }
                     csv.push(row.join(','));
                 }
                 let csvFile = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                 let downloadLink = document.createElement('a');
                 downloadLink.download = 'Respon_{{ $activeKey }}_' + new Date().toISOString().slice(0, 10) + '.csv';
                 downloadLink.href = window.URL.createObjectURL(csvFile);
                 downloadLink.style.display = 'none';
                 document.body.appendChild(downloadLink);
                 downloadLink.click();
                 document.body.removeChild(downloadLink);
             }
         }">

        {{-- Google Sheets Toolbar Header --}}
        <div class="px-5 sm:px-6 py-4 bg-[#FAF9FD] border-b border-[#EDE1FA] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-xs flex-shrink-0">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
                </div>
                
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">
                        {{ $activeCategory['title'] }} (Responses)
                    </h2>
                </div>
            </div>

            {{-- Action Tools: Search & Export --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <form method="GET" action="{{ route('client-forms.results') }}" class="flex items-center gap-1.5">
                    <input type="hidden" name="category" value="{{ $activeKey }}">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari di spreadsheet..." 
                           class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:border-purple-600 focus:ring-2 focus:ring-purple-600/20 w-44 sm:w-56 bg-white">
                </form>

                <button type="button" @click="exportToCSV()" 
                        class="px-4 py-2 rounded-xl bg-white hover:bg-purple-50 text-purple-deep border border-[#EDE1FA] font-bold text-xs transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
                        title="Unduh seluruh respon dalam format CSV">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Download CSV</span>
                </button>
            </div>
        </div>

        {{-- Spreadsheet Grid View --}}
        @if($submissions->isEmpty())
            <div class="py-16 text-center bg-white">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 3v18"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Belum Ada Baris Respon untuk {{ $activeCategory['short_title'] }}</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Saat ada calon klien yang mengisi dan mengirimkan link {{ $activeCategory['short_title'] }}, baris data akan langsung muncul di spreadsheet ini secara otomatis.</p>
            </div>
        @else
            <div class="overflow-x-auto max-h-[580px] border-b border-slate-200 relative">
                <table id="sheetsResponseTable" class="min-w-full text-left border-collapse text-[11px] font-sans">
                    <thead class="sticky top-0 z-20 bg-[#F1F3F4] text-slate-700 uppercase tracking-wider font-bold shadow-xs select-none">
                        <tr class="divide-x divide-slate-300 border-b-2 border-slate-300">
                            <th class="py-2.5 px-3 bg-[#E8EAED] text-center w-12 font-mono text-slate-500 no-export">#</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px] text-slate-800">Timestamp</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[90px] text-center">Informed Consent</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px] text-slate-800 font-bold">Nama Lengkap</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[100px]">Jenis Kelamin</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">TTL</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Urutan Kelahiran</th>


                            @if($activeKey === 'anak')
                                {{-- Kolom Khusus Anak (58 Pertanyaan GForm Anak) --}}
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px]">Status Nikah Ortu</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[220px]">Alamat Tinggal</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No. HP</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Agama</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[100px]">Suku</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Pendidikan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Alasan Konseling</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Kondisi Saat Ini</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Hal Ingin Ditingkatkan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Karakter Anak</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Prestasi</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Ketakutan / Fobia</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Riwayat Trauma</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Riwayat Kesehatan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px] bg-blue-50/70 text-blue-900 font-extrabold">Nama Ayah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[80px]">Usia Ayah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Pekerjaan Ayah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No HP Ayah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px] bg-pink-50/70 text-pink-900 font-extrabold">Nama Ibu</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[80px]">Usia Ibu</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Pekerjaan Ibu</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No HP Ibu</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Relasi dgn Ayah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Relasi dgn Ibu</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Relasi dgn Saudara</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Relasi dgn Teman</th>
                            @elseif($activeKey === 'dewasa')
                                {{-- Kolom Khusus Dewasa --}}
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[220px]">Alamat Sekarang</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No. HP</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Agama</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[100px]">Suku</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Pendidikan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Pekerjaan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Hobi</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Alasan Konseling</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Kondisi Saat Ini</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Pikiran Negatif</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Hal Ingin Ditingkatkan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Karakter Kepribadian</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Ketakutan / Fobia</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[100px]">Jam Tidur</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Riwayat Trauma</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Riwayat Kesehatan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Relasi dgn Ayah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Relasi dgn Ibu</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Relasi dgn Saudara</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Relasi dgn Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Relasi dgn Anak</th>
                            @elseif($activeKey === 'pra_nikah')
                                {{-- Kolom Khusus Pra-Nikah --}}
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Alamat Klien</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No. HP</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Agama</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Pekerjaan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px] bg-rose-50/70 text-rose-900 font-extrabold">Nama Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">TTL Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">HP Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Pekerjaan Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px] text-purple-900 font-bold">Rencana Pernikahan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Lama Kenal</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Lama Pacaran</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Alasan Konseling</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Harapan Pernikahan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Hal Ingin Ditingkatkan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Keluhan Hubungan</th>
                            @elseif($activeKey === 'pernikahan')
                                {{-- Kolom Khusus Pernikahan --}}
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Alamat Klien</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No. HP</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Agama</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Pekerjaan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px] bg-rose-50/70 text-rose-900 font-extrabold">Nama Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">TTL Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">HP Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Pekerjaan Pasangan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Tgl/Tempat Nikah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Lama Menikah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[90px] text-center">Jml Anak</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Alasan Konseling</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Keluhan Utama Hubungan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Kemungkinan Perbaikan</th>
                            @elseif($activeKey === 'biography_en')
                                {{-- Kolom Khusus Biography Form (English) --}}
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[220px]">Current Address</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Phone</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Email</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Religion</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Ethnicity</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">Education</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Father's Name</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Mother's Name</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Siblings Info</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">School / University</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Strengths</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Weaknesses</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Dream Job</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Self Description</th>
                            @elseif($activeKey === 'non_industri')
                                {{-- Kolom Khusus Non-Industri --}}
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Alamat</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Asal Kota</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No. HP</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Agama</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Pendidikan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Nama Ayah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Nama Ibu</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Data Saudara</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Riwayat Sekolah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Organisasi & Prestasi</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Kelebihan & Kelemahan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[150px]">Cita-Cita</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Deskripsi Diri</th>
                            @elseif($activeKey === 'industri')
                                {{-- Kolom Khusus Industri --}}
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Alamat</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Asal Kota</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No. HP</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[110px]">Status Nikah</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px]">Pendidikan & IPK</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px] bg-purple-50/70 text-purple-900 font-extrabold">Posisi Dituju</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[180px]">Pekerjaan Saat Ini</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px] bg-amber-50/70 text-amber-900 font-extrabold text-center">Skor PSS (Stres)</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[220px]">Pengalaman Kerja</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px]">Ekspektasi Gaji</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[200px]">Kelebihan & Kelemahan</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Deskripsi Diri</th>
                            @else
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[220px]">Alamat</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[120px]">No. HP</th>
                                <th class="py-2.5 px-3 whitespace-nowrap min-w-[250px]">Keterangan</th>
                            @endif

                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[100px]">Pernah Konseling</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[160px]">Kontak Darurat</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[130px]">Sumber Info</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[140px] text-purple-900 font-extrabold">Preferensi Sesi</th>
                            <th class="py-2.5 px-3 whitespace-nowrap min-w-[90px] text-center no-export sticky right-0 bg-[#F1F3F4] z-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-800">
                        @foreach($submissions as $index => $sub)
                        @php
                            $ans = $sub->answers ?? [];
                        @endphp
                        <tr class="divide-x divide-slate-200 hover:bg-slate-50 transition font-mono text-[11px] group">
                            {{-- Row Number --}}
                            <td class="py-2 px-2.5 bg-[#F8F9FA] text-center font-bold text-slate-400 select-none no-export">
                                {{ $submissions->firstItem() + $index }}
                            </td>

                            {{-- Timestamp --}}
                            <td class="py-2 px-3 whitespace-nowrap text-slate-600">
                                {{ $sub->created_at->format('Y-m-d H:i:s') }}
                            </td>

                            {{-- Consent --}}
                            <td class="py-2 px-3 whitespace-nowrap text-center text-slate-700 font-semibold">
                                {{ $sub->consent_agreed ? 'Setuju' : 'Tidak' }}
                            </td>

                            {{-- Nama Klien --}}
                            <td class="py-2 px-3 whitespace-nowrap font-sans font-bold text-slate-900">
                                {{ $sub->client_name }}
                            </td>

                            {{-- Gender --}}
                            <td class="py-2 px-3 whitespace-nowrap font-sans">
                                {{ $ans['jenis_kelamin'] ?? $ans['gender'] ?? '-' }}
                            </td>

                            {{-- TTL --}}
                            <td class="py-2 px-3 whitespace-nowrap font-sans">
                                {{ $ans['birth_place_date'] ?? $ans['tempat_tanggal_lahir'] ?? (($ans['tempat_lahir'] ?? '') . ($ans['tempat_lahir'] && $ans['tanggal_lahir'] ? ', ' : '') . ($ans['tanggal_lahir'] ?? '-')) }}
                            </td>

                            {{-- Urutan Kelahiran --}}
                            <td class="py-2 px-3 whitespace-nowrap font-sans">
                                {{ $ans['urutan_kelahiran'] ?? $ans['birth_order'] ?? '-' }}
                            </td>

                            @if($activeKey === 'anak')
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['status_pernikahan_ortu'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[220px]" title="{{ $ans['alamat'] ?? '-' }}">{{ $ans['alamat'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sub->client_phone }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['agama'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['suku_bangsa'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pendidikan_terakhir'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]" title="{{ $ans['alasan_konseling'] ?? '-' }}">{{ $ans['alasan_konseling'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]" title="{{ $ans['kondisi_anak_saat_ini'] ?? '-' }}">{{ $ans['kondisi_anak_saat_ini'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]" title="{{ $ans['hal_ingin_ditingkatkan'] ?? '-' }}">{{ $ans['hal_ingin_ditingkatkan'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['karakter_anak'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['prestasi_anak'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['ketakutan_phobia'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['riwayat_trauma'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['riwayat_kesehatan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans font-bold text-slate-800 bg-blue-50/20">{{ $ans['nama_ayah'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap text-center">{{ $ans['usia_ayah'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pekerjaan_ayah'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $ans['phone_ayah'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans font-bold text-slate-800 bg-pink-50/20">{{ $ans['nama_ibu'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap text-center">{{ $ans['usia_ibu'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pekerjaan_ibu'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $ans['phone_ibu'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[180px]">{{ $ans['relasi_ayah'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[180px]">{{ $ans['relasi_ibu'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[160px]">{{ $ans['relasi_saudara'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[160px]">{{ $ans['relasi_teman'] ?? '-' }}</td>
                            @elseif($activeKey === 'dewasa')
                                <td class="py-2 px-3 font-sans truncate max-w-[220px]" title="{{ $ans['alamat'] ?? '-' }}">{{ $ans['alamat'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sub->client_phone }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['agama'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['suku_bangsa'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pendidikan_terakhir'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pekerjaan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['hobi'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]" title="{{ $ans['alasan_konseling'] ?? '-' }}">{{ $ans['alasan_konseling'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]">{{ $ans['kondisi_saat_ini'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['pikiran_negatif'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['hal_ingin_ditingkatkan'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['karakter_kepribadian'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['ketakutan_phobia'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap text-center">{{ $ans['jam_tidur'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['riwayat_trauma'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[150px]">{{ $ans['riwayat_kesehatan'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[180px]">{{ $ans['relasi_ayah'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[180px]">{{ $ans['relasi_ibu'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[160px]">{{ $ans['relasi_saudara'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[160px]">{{ $ans['relasi_pasangan'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[160px]">{{ $ans['relasi_anak'] ?? '-' }}</td>
                            @elseif($activeKey === 'pra_nikah')
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['alamat'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sub->client_phone }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['agama'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pekerjaan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans font-bold text-slate-800 bg-rose-50/20">{{ $ans['nama_pasangan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ ($ans['tempat_lahir_pasangan'] ?? '') . ', ' . ($ans['tanggal_lahir_pasangan'] ?? '') }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $ans['phone_pasangan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pekerjaan_pasangan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans font-bold text-purple-deep">{{ $ans['tanggal_rencana_pernikahan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans text-center">{{ $ans['lama_berkenalan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans text-center">{{ $ans['lama_berpacaran'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]" title="{{ $ans['alasan_konseling'] ?? '-' }}">{{ $ans['alasan_konseling'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]" title="{{ $ans['harapan_pernikahan'] ?? '-' }}">{{ $ans['harapan_pernikahan'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['hal_ingin_ditingkatkan'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['keluhan_hubungan'] ?? '-' }}</td>
                            @elseif($activeKey === 'pernikahan')
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['alamat'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sub->client_phone }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['agama'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pekerjaan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans font-bold text-slate-800 bg-rose-50/20">{{ $ans['nama_pasangan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ ($ans['tempat_lahir_pasangan'] ?? '') . ', ' . ($ans['tanggal_lahir_pasangan'] ?? '') }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $ans['phone_pasangan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pekerjaan_pasangan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['tempat_tanggal_pernikahan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans text-center">{{ $ans['lama_pernikahan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans text-center">{{ $ans['jumlah_anak'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]" title="{{ $ans['alasan_konseling'] ?? '-' }}">{{ $ans['alasan_konseling'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]" title="{{ $ans['keluhan_utama'] ?? '-' }}">{{ $ans['keluhan_utama'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['kemungkinan_perbaikan'] ?? '-' }}</td>
                            @elseif($activeKey === 'biography_en')
                                <td class="py-2 px-3 font-sans truncate max-w-[220px]">{{ $ans['current_address'] ?? $ans['alamat'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sub->client_phone }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $ans['email'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['religion'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['ethnicity'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['last_education'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['father_name'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['mother_name'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[180px]">{{ $ans['siblings_info'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['school_history'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['strengths'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['weaknesses'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['dream_job'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]">{{ $ans['self_description'] ?? '-' }}</td>
                            @elseif($activeKey === 'non_industri')
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['alamat_sekarang'] ?? $ans['alamat'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['asal_kota'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sub->client_phone }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['agama'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['pendidikan_terakhir'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['nama_ayah'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['nama_ibu'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[180px]">{{ $ans['data_saudara'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[180px]">{{ $ans['riwayat_sekolah'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ ($ans['riwayat_organisasi'] ?? '-') . ' | ' . ($ans['prestasi'] ?? '-') }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ ($ans['kelebihan_diri'] ?? '-') . ' | ' . ($ans['kelemahan_diri'] ?? '-') }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['cita_cita'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]">{{ $ans['deskripsi_diri_bebas'] ?? '-' }}</td>
                            @elseif($activeKey === 'industri')
                                @php
                                    $pssTotal = 0;
                                    for ($p = 1; $p <= 10; $p++) {
                                        $pssTotal += (int)($ans['pss_' . $p] ?? 0);
                                    }
                                @endphp
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ $ans['alamat_sekarang'] ?? $ans['alamat'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['asal_kota'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sub->client_phone }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['status_perkawinan'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ ($ans['pendidikan_terakhir'] ?? '-') . ' (IPK: ' . ($ans['ipk_terakhir'] ?? '-') . ')' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans font-bold text-purple-deep bg-purple-50/20">{{ $ans['posisi_dituju'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[180px]">{{ $ans['pekerjaan_saat_ini'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap text-center font-bold {{ $pssTotal >= 27 ? 'text-rose-600 bg-rose-50/30' : ($pssTotal >= 14 ? 'text-amber-600 bg-amber-50/30' : 'text-slate-700 bg-slate-50/50') }}">
                                    {{ $pssTotal }} / 40
                                </td>
                                <td class="py-2 px-3 font-sans truncate max-w-[220px]">{{ ($ans['pengalaman_kerja_1'] ?? '-') }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['ekspektasi_gaji'] ?? '-' }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[200px]">{{ ($ans['kelebihan_diri'] ?? '-') . ' | ' . ($ans['kelemahan_diri'] ?? '-') }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]">{{ $ans['deskripsi_diri_bebas'] ?? '-' }}</td>
                            @else
                                <td class="py-2 px-3 font-sans truncate max-w-[220px]">{{ $ans['alamat'] ?? '-' }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sub->client_phone }}</td>
                                <td class="py-2 px-3 font-sans truncate max-w-[250px]">{{ $ans['alasan_konseling'] ?? $ans['keterangan'] ?? '-' }}</td>
                            @endif

                            {{-- Common Columns --}}
                            <td class="py-2 px-3 whitespace-nowrap font-sans text-center">{{ $ans['pernah_konseling'] ?? '-' }}</td>
                            <td class="py-2 px-3 font-sans truncate max-w-[160px]" title="{{ $ans['kontak_darurat'] ?? '-' }}">{{ $ans['kontak_darurat'] ?? '-' }}</td>
                            <td class="py-2 px-3 whitespace-nowrap font-sans">{{ $ans['sumber_info'] ?? '-' }}</td>
                            <td class="py-2 px-3 whitespace-nowrap font-sans font-bold text-purple-deep">{{ $ans['preferensi_konseling'] ?? $sub->service_preference }}</td>

                            {{-- Action Sticky Column --}}
                            <td class="py-2 px-2.5 whitespace-nowrap text-center no-export sticky right-0 bg-white group-hover:bg-[#f2faf5] shadow-xs">
                                <button type="button" 
                                        @click="openDossier({{ json_encode($sub) }})"
                                        class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-deep hover:bg-purple-deep hover:text-white transition font-bold text-[10px] shadow-2xs cursor-pointer"
                                        title="Buka Lembar Dossier Lengkap">
                                    Detail
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination --}}
            <div class="px-5 py-3.5 bg-[#F8F9FA] border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan <strong>{{ $submissions->count() }}</strong> dari <strong>{{ $submissions->total() }}</strong> total baris respon</span>
                <div>
                    {{ $submissions->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- ============================================================== --}}
    {{-- DOSSIER MODAL DETAIL JAWABAN FORMULIR                         --}}
    {{-- ============================================================== --}}
    <div x-show="showDossierModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showDossierModal = false" 
             class="bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-scale-up">
            
            {{-- Modal Header --}}
            <div class="p-6 bg-gradient-to-r from-purple-deep to-[#4A2F85] text-white flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-white/70 uppercase" x-text="selectedForm && selectedForm.created_at ? 'Waktu Pengisian: ' + selectedForm.created_at.slice(0, 19).replace('T', ' ') : ''"></span>
                    <h3 class="text-xl font-extrabold" x-text="selectedForm ? selectedForm.client_name : ''"></h3>
                    <p class="text-xs text-white/80" x-text="selectedForm ? selectedForm.form_type_label : ''"></p>
                </div>
                <button type="button" @click="showDossierModal = false" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
                    &times;
                </button>
            </div>

            {{-- Modal Body (Answers List) --}}
            <div class="p-6 overflow-y-auto space-y-6 text-sm text-slate-800">
                <template x-if="selectedForm && selectedForm.answers">
                    <div class="space-y-6">
                        {{-- 1. Ringkasan Identitas Klien --}}
                        <div class="bg-purple-50/60 rounded-2xl p-4 border border-purple-100">
                            <h4 class="font-bold text-purple-deep text-xs uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>1. Data Diri Klien</span>
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                                <div><span class="text-slate-400 block font-medium">Nama Lengkap:</span> <strong class="text-slate-900" x-text="selectedForm.client_name || selectedForm.answers.nama_lengkap || selectedForm.answers.full_name || '-'"></strong></div>
                                <div><span class="text-slate-400 block font-medium">Jenis Kelamin:</span> <span class="font-semibold text-slate-800" x-text="selectedForm.answers.jenis_kelamin || selectedForm.answers.gender || '-'"></span></div>
                                <div><span class="text-slate-400 block font-medium">TTL:</span> <span class="font-semibold text-slate-800" x-text="selectedForm.answers.birth_place_date || selectedForm.answers.tempat_tanggal_lahir || `${selectedForm.answers.tempat_lahir || ''}${selectedForm.answers.tempat_lahir && selectedForm.answers.tanggal_lahir ? ', ' : ''}${selectedForm.answers.tanggal_lahir || '-'}`"></span></div>
                                <div><span class="text-slate-400 block font-medium">Urutan Kelahiran:</span> <span class="text-slate-800" x-text="selectedForm.answers.urutan_kelahiran || selectedForm.answers.birth_order || '-'"></span></div>
                                <div><span class="text-slate-400 block font-medium">No. Telp / HP:</span> <strong class="text-purple-deep" x-text="selectedForm.client_phone || selectedForm.answers.phone || '-'"></strong></div>
                                <div><span class="text-slate-400 block font-medium">Email:</span> <span class="text-slate-800" x-text="selectedForm.answers.email || '-'"></span></div>
                                <div><span class="text-slate-400 block font-medium">Agama / Religion:</span> <span class="text-slate-800" x-text="selectedForm.answers.agama || selectedForm.answers.religion || '-'"></span></div>
                                <div><span class="text-slate-400 block font-medium">Suku / Ethnicity:</span> <span class="text-slate-800" x-text="selectedForm.answers.suku_bangsa || selectedForm.answers.ethnicity || '-'"></span></div>
                                <div><span class="text-slate-400 block font-medium">Pendidikan Terakhir:</span> <span class="text-slate-800" x-text="selectedForm.answers.pendidikan_terakhir || selectedForm.answers.last_education || '-'"></span></div>
                                <template x-if="selectedForm.answers.status_pernikahan_ortu">
                                    <div><span class="text-slate-400 block font-medium">Status Nikah Ortu:</span> <span class="text-slate-800" x-text="selectedForm.answers.status_pernikahan_ortu"></span></div>
                                </template>
                                <template x-if="selectedForm.answers.status_perkawinan">
                                    <div><span class="text-slate-400 block font-medium">Status Perkawinan:</span> <span class="text-slate-800" x-text="selectedForm.answers.status_perkawinan"></span></div>
                                </template>
                                <template x-if="selectedForm.answers.asal_kota">
                                    <div><span class="text-slate-400 block font-medium">Asal Kota:</span> <span class="text-slate-800" x-text="selectedForm.answers.asal_kota"></span></div>
                                </template>
                                <div class="col-span-2 sm:col-span-3"><span class="text-slate-400 block font-medium">Alamat:</span> <strong class="text-slate-800 font-medium" x-text="selectedForm.answers.alamat || selectedForm.answers.alamat_sekarang || selectedForm.answers.current_address || '-'"></strong></div>
                            </div>
                        </div>

                        {{-- 2. Data Pasangan (Khusus Pra-Nikah & Pernikahan) --}}
                        <template x-if="selectedForm.answers.nama_pasangan">
                            <div class="bg-rose-50/50 rounded-2xl p-4 border border-rose-100 space-y-3">
                                <h4 class="font-bold text-rose-900 text-xs uppercase tracking-wider flex items-center gap-1.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                    <span>2. Data Pasangan & Riwayat Hubungan</span>
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                                    <div><span class="text-slate-400 block">Nama Pasangan:</span> <strong class="text-rose-900" x-text="selectedForm.answers.nama_pasangan || '-'"></strong></div>
                                    <div><span class="text-slate-400 block">Jenis Kelamin:</span> <span x-text="selectedForm.answers.jenis_kelamin_pasangan || '-'"></span></div>
                                    <div><span class="text-slate-400 block">TTL Pasangan:</span> <span x-text="`${selectedForm.answers.tempat_lahir_pasangan || ''}${selectedForm.answers.tempat_lahir_pasangan && selectedForm.answers.tanggal_lahir_pasangan ? ', ' : ''}${selectedForm.answers.tanggal_lahir_pasangan || '-'}`"></span></div>
                                    <div><span class="text-slate-400 block">HP Pasangan:</span> <strong class="text-slate-900" x-text="selectedForm.answers.phone_pasangan || '-'"></strong></div>
                                    <div><span class="text-slate-400 block">Pekerjaan:</span> <span x-text="selectedForm.answers.pekerjaan_pasangan || '-'"></span></div>
                                    <div><span class="text-slate-400 block">Agama:</span> <span x-text="selectedForm.answers.agama_pasangan || '-'"></span></div>
                                    <template x-if="selectedForm.answers.tanggal_rencana_pernikahan">
                                        <div class="col-span-2 sm:col-span-3 bg-white p-2.5 rounded-xl border border-rose-200">
                                            <span class="text-rose-800 font-bold block mb-0.5">Rencana Pernikahan:</span>
                                            <p class="text-slate-800" x-text="selectedForm.answers.tanggal_rencana_pernikahan"></p>
                                        </div>
                                    </template>
                                    <template x-if="selectedForm.answers.tempat_tanggal_pernikahan">
                                        <div><span class="text-slate-400 block">Tgl & Tempat Nikah:</span> <span x-text="selectedForm.answers.tempat_tanggal_pernikahan"></span></div>
                                    </template>
                                    <template x-if="selectedForm.answers.lama_pernikahan">
                                        <div><span class="text-slate-400 block">Lama Pernikahan:</span> <strong x-text="selectedForm.answers.lama_pernikahan"></strong></div>
                                    </template>
                                    <template x-if="selectedForm.answers.jumlah_anak">
                                        <div><span class="text-slate-400 block">Jumlah Anak:</span> <strong x-text="selectedForm.answers.jumlah_anak"></strong></div>
                                    </template>
                                    <template x-if="selectedForm.answers.lama_berkenalan">
                                        <div><span class="text-slate-400 block">Lama Berkenalan:</span> <span x-text="selectedForm.answers.lama_berkenalan"></span></div>
                                    </template>
                                    <template x-if="selectedForm.answers.lama_berpacaran">
                                        <div><span class="text-slate-400 block">Lama Berpacaran:</span> <span x-text="selectedForm.answers.lama_berpacaran"></span></div>
                                    </template>
                                </div>

                                <template x-if="selectedForm.answers.harapan_pernikahan || selectedForm.answers.keluhan_hubungan || selectedForm.answers.keluhan_utama">
                                    <div class="space-y-2 pt-2 border-t border-rose-100 text-xs">
                                        <template x-if="selectedForm.answers.harapan_pernikahan">
                                            <div>
                                                <span class="text-rose-800 font-semibold block">Harapan dari Pernikahan:</span>
                                                <p class="text-slate-800 font-medium" x-text="selectedForm.answers.harapan_pernikahan"></p>
                                            </div>
                                        </template>
                                        <template x-if="selectedForm.answers.keluhan_hubungan || selectedForm.answers.keluhan_utama">
                                            <div>
                                                <span class="text-rose-800 font-semibold block">Keluhan / Masalah Hubungan:</span>
                                                <p class="text-slate-800 font-medium" x-text="selectedForm.answers.keluhan_hubungan || selectedForm.answers.keluhan_utama"></p>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- 3. Data Industri & Stres PSS (Khusus Formulir Industri) --}}
                        <template x-if="selectedForm.form_type === 'industri' || selectedForm.answers.posisi_dituju">
                            <div class="bg-amber-50/50 rounded-2xl p-4 border border-amber-200 space-y-3 text-xs">
                                <h4 class="font-bold text-amber-900 text-xs uppercase tracking-wider flex items-center gap-1.5">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    <span>Posisi Dilamar & Pengalaman Industri</span>
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <div class="col-span-2 sm:col-span-1 bg-white p-2.5 rounded-xl border border-amber-200">
                                        <span class="text-slate-400 block font-medium">Posisi yang Dituju:</span>
                                        <strong class="text-purple-deep text-sm" x-text="selectedForm.answers.posisi_dituju || '-'"></strong>
                                    </div>
                                    <div class="bg-white p-2.5 rounded-xl border border-amber-200">
                                        <span class="text-slate-400 block font-medium">Pekerjaan Saat Ini:</span>
                                        <strong class="text-slate-800" x-text="selectedForm.answers.pekerjaan_saat_ini || '-'"></strong>
                                    </div>
                                    <div class="bg-white p-2.5 rounded-xl border border-amber-200">
                                        <span class="text-slate-400 block font-medium">Ekspektasi Gaji:</span>
                                        <strong class="text-purple-deep" x-text="selectedForm.answers.ekspektasi_gaji || '-'"></strong>
                                    </div>
                                    <div class="col-span-2 sm:col-span-3">
                                        <span class="text-slate-400 block font-medium">Pengalaman Kerja:</span>
                                        <p class="text-slate-800 font-medium" x-text="`${selectedForm.answers.pengalaman_kerja_1 || ''} ${selectedForm.answers.pengalaman_kerja_2 ? ' | ' + selectedForm.answers.pengalaman_kerja_2 : ''}`"></p>
                                    </div>
                                </div>

                                {{-- PSS Stress Score Summary --}}
                                <template x-if="selectedForm.answers.pss_1 !== undefined">
                                    <div class="mt-3 pt-3 border-t border-amber-200">
                                        <h5 class="font-bold text-slate-800 mb-2">Hasil Kuesioner Stres (PSS 10 Butir):</h5>
                                        <div class="grid grid-cols-5 sm:grid-cols-10 gap-1 text-center font-mono">
                                            <template x-for="n in 10" :key="n">
                                                <div class="p-1.5 rounded-lg border bg-white">
                                                    <span class="text-[9px] text-slate-400 block" x-text="'B' + n"></span>
                                                    <strong class="text-xs text-slate-800" x-text="selectedForm.answers['pss_' + n] || '0'"></strong>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- 4. Profil Orang Tua & Keluarga --}}
                        <template x-if="selectedForm.answers.nama_ayah || selectedForm.answers.nama_ibu || selectedForm.answers.father_name || selectedForm.answers.mother_name">
                            <div class="space-y-2">
                                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Data Orang Tua & Keluarga</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="border rounded-2xl p-4 bg-slate-50/50 space-y-1.5 text-xs">
                                        <h5 class="font-bold text-blue-900 uppercase mb-2 flex items-center gap-1.5">👨 Profil Ayah</h5>
                                        <p><span class="text-slate-400">Nama:</span> <strong x-text="selectedForm.answers.nama_ayah || selectedForm.answers.father_name || '-'"></strong></p>
                                        <template x-if="selectedForm.answers.usia_ayah || selectedForm.answers.father_age">
                                            <p><span class="text-slate-400">Usia:</span> <span x-text="selectedForm.answers.usia_ayah || selectedForm.answers.father_age || '-'"></span></p>
                                        </template>
                                        <template x-if="selectedForm.answers.pendidikan_ayah || selectedForm.answers.father_education">
                                            <p><span class="text-slate-400">Pendidikan:</span> <span x-text="selectedForm.answers.pendidikan_ayah || selectedForm.answers.father_education || '-'"></span></p>
                                        </template>
                                        <template x-if="selectedForm.answers.pekerjaan_ayah || selectedForm.answers.father_occupation">
                                            <p><span class="text-slate-400">Pekerjaan:</span> <span x-text="selectedForm.answers.pekerjaan_ayah || selectedForm.answers.father_occupation || '-'"></span></p>
                                        </template>
                                        <template x-if="selectedForm.answers.phone_ayah">
                                            <p><span class="text-slate-400">HP:</span> <strong x-text="selectedForm.answers.phone_ayah"></strong></p>
                                        </template>
                                    </div>

                                    <div class="border rounded-2xl p-4 bg-slate-50/50 space-y-1.5 text-xs">
                                        <h5 class="font-bold text-pink-900 uppercase mb-2 flex items-center gap-1.5">👩 Profil Ibu</h5>
                                        <p><span class="text-slate-400">Nama:</span> <strong x-text="selectedForm.answers.nama_ibu || selectedForm.answers.mother_name || '-'"></strong></p>
                                        <template x-if="selectedForm.answers.usia_ibu || selectedForm.answers.mother_age">
                                            <p><span class="text-slate-400">Usia:</span> <span x-text="selectedForm.answers.usia_ibu || selectedForm.answers.mother_age || '-'"></span></p>
                                        </template>
                                        <template x-if="selectedForm.answers.pendidikan_ibu || selectedForm.answers.mother_education">
                                            <p><span class="text-slate-400">Pendidikan:</span> <span x-text="selectedForm.answers.pendidikan_ibu || selectedForm.answers.mother_education || '-'"></span></p>
                                        </template>
                                        <template x-if="selectedForm.answers.pekerjaan_ibu || selectedForm.answers.mother_occupation">
                                            <p><span class="text-slate-400">Pekerjaan:</span> <span x-text="selectedForm.answers.pekerjaan_ibu || selectedForm.answers.mother_occupation || '-'"></span></p>
                                        </template>
                                        <template x-if="selectedForm.answers.phone_ibu">
                                            <p><span class="text-slate-400">HP:</span> <strong x-text="selectedForm.answers.phone_ibu"></strong></p>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="selectedForm.answers.data_saudara || selectedForm.answers.siblings_info">
                                    <div class="p-3 rounded-xl border bg-slate-50 text-xs">
                                        <span class="text-slate-400 font-semibold block">Informasi Saudara:</span>
                                        <p class="font-medium text-slate-800" x-text="selectedForm.answers.data_saudara || selectedForm.answers.siblings_info"></p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- 5. Kondisi & Riwayat Psikologis --}}
                        <div class="space-y-3">
                            <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Kondisi & Riwayat Psikologis</h4>
                            
                            <template x-if="selectedForm.answers.alasan_konseling">
                                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50">
                                    <span class="text-slate-500 text-xs block mb-1 font-semibold">Alasan Membutuhkan Konseling:</span>
                                    <p class="text-sm font-medium text-slate-900" x-text="selectedForm.answers.alasan_konseling"></p>
                                </div>
                            </template>

                            <template x-if="selectedForm.answers.kondisi_anak_saat_ini || selectedForm.answers.kondisi_saat_ini">
                                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50">
                                    <span class="text-slate-500 text-xs block mb-1 font-semibold">Gambaran Kondisi Saat Ini:</span>
                                    <p class="text-sm font-medium text-slate-900" x-text="selectedForm.answers.kondisi_anak_saat_ini || selectedForm.answers.kondisi_saat_ini"></p>
                                </div>
                            </template>

                            <template x-if="selectedForm.answers.hal_ingin_ditingkatkan">
                                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50">
                                    <span class="text-slate-500 text-xs block mb-1 font-semibold">Hal yang Ingin Ditingkatkan:</span>
                                    <p class="text-sm font-medium text-slate-900" x-text="selectedForm.answers.hal_ingin_ditingkatkan"></p>
                                </div>
                            </template>

                            <template x-if="selectedForm.answers.self_description || selectedForm.answers.deskripsi_diri_bebas">
                                <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50">
                                    <span class="text-slate-500 text-xs block mb-1 font-semibold">Deskripsi Diri:</span>
                                    <p class="text-sm font-medium text-slate-900" x-text="selectedForm.answers.self_description || selectedForm.answers.deskripsi_diri_bebas"></p>
                                </div>
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <template x-if="selectedForm.answers.karakter_anak || selectedForm.answers.karakter_kepribadian || selectedForm.answers.kelebihan_diri || selectedForm.answers.strengths">
                                    <div class="p-3 rounded-xl border bg-slate-50/70">
                                        <span class="text-slate-400 block font-semibold">Karakter / Kelebihan Diri:</span>
                                        <p class="font-medium text-slate-800" x-text="selectedForm.answers.karakter_anak || selectedForm.answers.karakter_kepribadian || selectedForm.answers.kelebihan_diri || selectedForm.answers.strengths || '-'"></p>
                                    </div>
                                </template>
                                <template x-if="selectedForm.answers.kelemahan_diri || selectedForm.answers.weaknesses || selectedForm.answers.pikiran_negatif">
                                    <div class="p-3 rounded-xl border bg-slate-50/70">
                                        <span class="text-slate-400 block font-semibold">Kelemahan / Pikiran Negatif:</span>
                                        <p class="font-medium text-slate-800" x-text="selectedForm.answers.kelemahan_diri || selectedForm.answers.weaknesses || selectedForm.answers.pikiran_negatif || '-'"></p>
                                    </div>
                                </template>
                                <template x-if="selectedForm.answers.ketakutan_phobia">
                                    <div class="p-3 rounded-xl border bg-slate-50/70">
                                        <span class="text-slate-400 block font-semibold">Ketakutan / Phobia:</span>
                                        <p class="font-medium text-slate-800" x-text="selectedForm.answers.ketakutan_phobia || '-'"></p>
                                    </div>
                                </template>
                                <template x-if="selectedForm.answers.riwayat_trauma || selectedForm.answers.trauma_history">
                                    <div class="p-3 rounded-xl border bg-slate-50/70">
                                        <span class="text-slate-400 block font-semibold">Riwayat Trauma:</span>
                                        <p class="font-medium text-slate-800" x-text="selectedForm.answers.riwayat_trauma || selectedForm.answers.trauma_history || '-'"></p>
                                    </div>
                                </template>
                                <template x-if="selectedForm.answers.riwayat_kesehatan || selectedForm.answers.hospitalization_history">
                                    <div class="col-span-1 sm:col-span-2 p-3 rounded-xl border bg-slate-50/70">
                                        <span class="text-slate-400 block font-semibold">Riwayat Kesehatan / Medis:</span>
                                        <p class="font-medium text-slate-800" x-text="selectedForm.answers.riwayat_kesehatan || selectedForm.answers.hospitalization_history || '-'"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- 6. Lain-Lain & Preferensi Layanan --}}
                        <div class="space-y-2">
                            <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Lain-Lain & Preferensi Layanan</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-xl border bg-slate-50">
                                    <span class="text-slate-400 block font-semibold">Pernah Konseling:</span>
                                    <p class="font-medium text-slate-800" x-text="`${selectedForm.answers.pernah_konseling || selectedForm.answers.psychologist_consultation || '-'} ${selectedForm.answers.nama_konselor_lama ? '(' + selectedForm.answers.nama_konselor_lama + ')' : ''}`"></p>
                                </div>
                                <div class="p-3 rounded-xl border bg-slate-50">
                                    <span class="text-slate-400 block font-semibold">Kontak Darurat:</span>
                                    <strong class="text-slate-900" x-text="selectedForm.answers.kontak_darurat || selectedForm.answers.emergency_contact || '-'"></strong>
                                </div>
                                <div class="p-3 rounded-xl border bg-slate-50">
                                    <span class="text-slate-400 block font-semibold">Sumber Info UC PSC:</span>
                                    <span class="font-medium text-slate-800" x-text="selectedForm.answers.sumber_info || selectedForm.answers.info_source || '-'"></span>
                                </div>
                                <div class="p-3 rounded-xl border bg-purple-50/50 border-purple-100">
                                    <span class="text-purple-deep block font-semibold">Preferensi Proses Konseling:</span>
                                    <strong class="text-purple-deep" x-text="selectedForm.answers.preferensi_konseling || selectedForm.answers.preferred_counseling || selectedForm.service_preference"></strong>
                                </div>
                            </div>
                        </div>

                        {{-- 7. DOSSIER LENGKAP SEMUA JAWABAN (INSPECTOR) --}}
                        <div class="pt-4 border-t border-slate-200">
                            <details class="group cursor-pointer">
                                <summary class="flex items-center justify-between text-xs font-bold text-slate-700 hover:text-purple-deep py-2 select-none">
                                    <span class="flex items-center gap-2">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                        <span>Inspeksi Seluruh Data Jawaban Klien (Lengkap)</span>
                                    </span>
                                    <span class="text-slate-400 group-open:rotate-180 transition-transform">&darr;</span>
                                </summary>
                                <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-200 overflow-x-auto text-[11px] font-mono space-y-1.5 max-h-60 overflow-y-auto">
                                    <template x-for="(val, key) in selectedForm.answers" :key="key">
                                        <div class="flex items-start gap-2 py-1 border-b border-slate-200/60 last:border-b-0">
                                            <span class="w-44 text-slate-500 font-semibold flex-shrink-0" x-text="key + ':'"></span>
                                            <span class="text-slate-900 break-words flex-1" x-text="val !== null && val !== '' ? val : '-'"></span>
                                        </div>
                                    </template>
                                </div>
                            </details>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Modal Footer --}}
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <button type="button" @click="showDossierModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Tutup
                </button>
                <div class="flex items-center gap-2">
                    <template x-if="selectedForm && selectedForm.client_id">
                        <a :href="`/clients/${selectedForm.client_id}`" class="px-4 py-2 rounded-xl bg-purple-deep text-white text-xs font-bold hover:bg-[#4A2F85] transition">
                            Buka di Profil Klien &rarr;
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
