# UC PSC Management System - Agent Guidelines & Project Rules

## Aturan Khusus UI/UX & Interaksi

### 1. Modal & Pop-up Handling (Strict Rule)
- **TIDAK BOLEH** menutup modal saat kursor/pengguna mengeklik area luar popup (backdrop, overlay, atau outside click).
- Setiap pembuatan maupun modifikasi modal/popup di website ini:
  - **Dilarang** memasang handler penutup pada backdrop/overlay (misal: `@click="modal = false"` pada backdrop).
  - **Dilarang** memasang `@click.away` atau `@click.outside` pada dialog box/container modal.
  - **Dilarang** memasang `@click.self="close()"` pada dialog wrapper.
  - Modal **HANYA BISA** ditutup melalui aksi eksplisit pengguna:
    1. Tombol silang / Close `X` di bagian header modal.
    2. Tombol `Batal` / `Tutup` di bagian footer modal.
    3. Setelah proses submit form berhasil selesai.
- Rujukan aturan lengkap tersedia pada [modal-behavior.md](file:///.agents/rules/modal-behavior.md).

### 2. Card Styling & Presentasi Data Isian (Strict Rule)
- **Tanpa Urutan Nomor pada Head Card**: Dilarang menggunakan prefix angka (seperti `1. `, `2. `) pada judul head card di seluruh card presentasi data saat ini dan kedepannya.
- **Warna Card Input Data Wajib Abu-abu**: Dilarang menggunakan warna card selain abu-abu netral (`bg-slate-50 border border-slate-100`) untuk semua kotak input data. Tidak boleh ada card hijau, merah/rose, amber, atau ungu.
- **Dilarang Font Bold pada Field Data & Label (Strict Rule)**: Dilarang keras menggunakan bobot font tebal (`font-bold`, `font-extrabold`, `font-semibold`) untuk seluruh nilai field data isian, estimasi biaya/tarif, maupun label input formulir. Seluruh teks nilai isian data wajib konsisten menggunakan `text-black text-sm font-medium`. Seluruh label field wajib menggunakan `text-xs font-medium text-slate-500`.
- **Warna Teks Field Terisi Wajib Hitam**: Seluruh data yang sudah terisi wajib menggunakan `text-black` (bukan ungu, hijau, atau warna lainnya).
- **Format Skoring Kuesioner**: Hanya tampilkan angka skornya saja tanpa teks kriteria dalam kurung (misal: cukup `2`, bukan `2 (Kadang-kadang)`).
- **Pemisahan Field seperti IPK**: Letakkan dalam subcard input data tersendiri di body card, bukan digabung dengan head card.
- **Warna Scrollbar Wajib Abu-abu Netral (Strict Design Rule)**: Dilarang menggunakan warna scrollbar selain abu-abu netral (`#CBD5E1` / `#94A3B8` hover, atau kelas `custom-scrollbar`). Tidak boleh ada scrollbar berwarna ungu, lavender, oranye, atau warna aksen lainnya di seluruh website ini saat ini maupun untuk pembuatan fitur kedepannya (*current and future creation*).
- **Desain Field Isian Data: Mode View (Read-Only) vs Mode Edit (Editable) (Strict Rule)**:
  - **Judul/Label di Luar & di Atas**: Label field wajib diletakkan di luar dan di atas box (`text-xs font-medium text-slate-500 mb-1 block`). **Dilarang keras** meletakkan label di dalam kotak abu-abu pada mode view.
  - **Mode View (Read-Only)**: Kotak field menggunakan background abu-abu netral (`px-3.5 py-2.5 bg-slate-50 rounded-xl border border-slate-100 min-h-[42px] flex items-center`) dengan teks `text-sm font-medium text-black`.
  - **Mode Edit (Editable)**: Kotak field yang dapat diedit **wajib berubah warna menjadi putih** (`bg-white`) dengan border outline abu-abu netral (`w-full px-3.5 py-2.5 rounded-xl border border-[#D9C2F0] focus:ring-2 focus:ring-purple-deep text-sm font-medium text-black min-h-[42px] shadow-xs`).
  - **Pemisahan Kolom Tempat & Tanggal Lahir (Zero-Layout-Shift)**: Wajib dipisah menjadi sub-kolom mandiri (`grid grid-cols-2 gap-2`) pada mode view dan edit agar simetris tanpa pergeseran layout.
  - Tinggi, padding, dan struktur grid mode view dan edit wajib selaras agar tata letak tidak bergeser saat berganti mode. Aturan ini mengikat untuk seluruh implementasi saat ini dan masa depan (*current and future creation*).
- Rujukan aturan lengkap tersedia pada [card-styling-guidelines.md](file:///.agents/rules/card-styling-guidelines.md).

### 3. Sinkronisasi Google Form Asli & Standar Pembuatan Dummy Data (Strict Rule)
- **Wajib Mengacu pada Google Form Asli**: Untuk sekarang dan seluruh kebutuhan kedepannya (**current and future events**), setiap kali diminta membuat input data dummy, isian data **WAJIB** disesuaikan secara presisi 1-to-1 dengan Google Form resmi:
  1. **Biography Form**: `biography_en` (123 pertanyaan)
  2. **Formulir Riwayat Hidup - Industri**: `industri` (190 pertanyaan)
  3. **Formulir Riwayat Hidup - Non-Industri**: `non_industri` (122 pertanyaan)
  4. **Formulir Riwayat Hidup Konseling - Anak**: `anak` (58 pertanyaan)
  5. **Formulir Riwayat Hidup Konseling - Dewasa**: `dewasa` (36 pertanyaan)
  6. **Formulir Riwayat Hidup Konseling - Pra Nikah**: `pra_nikah` (49 pertanyaan)
  7. **Formulir Riwayat Hidup Konseling - Pernikahan**: `pernikahan` (63 pertanyaan)
- **Kelengkapan Isian Dummy**: Dilarang membuat data dummy yang hanya mengisi sebagian kecil field jika form aslinya memiliki field-field detail (seperti keluarga, susunan saudara, riwayat pendidikan lengkap, pekerjaan, kondisi psikologis, relasi, dll.).
- **Kesesuaian Opsi Jawaban**: Opsi radio/select/skala wajib mengikuti pilihan opsi yang ada pada Google Form aslinya.
- Rujukan pemetaan dan katalog pertanyaan lengkap tersedia pada [form-specifications-and-dummy-data.md](file:///.agents/rules/form-specifications-and-dummy-data.md).

### 4. Efisiensi Kerja & Eksekusi Cepat (Strict Workflow Rule)
- **Targeted Action First**: Untuk instruksi perubahan UI/fitur spesifik, langsung tuju file komponen/view terkait tanpa melakukan audit Git global atau reflog yang tidak diminta.
- **Handling Windows CRLF & Blade Syntax**: Hati-hati terhadap karakter baris baru di Windows dan sintaks Blade `{{ }}` di PowerShell agar tidak memicu error parsing.
- **Larangan Full-Suite Test Tanpa Izin**: Jangan menjalankan `php artisan test` skala penuh di tengah perubahan UI kecil yang bisa menyebabkan delay/background timeout.
- Rujukan aturan lengkap tersedia pada [workflow-performance.md](file:///.agents/rules/workflow-performance.md).

### 5. Penyajian Data Tabel & Siklus Pengurutan 3-Mode (Strict Rule)
- **Dilarang Font Bold pada Teks di Dalam Tabel (Strict Rule)**: Dilarang keras menggunakan bobot font tebal (`font-bold`, `font-semibold`, `font-extrabold`, tag `<b>`, tag `<strong>`) untuk seluruh teks data yang berada di dalam baris/sel tabel (`<td>`). Seluruh teks isian tabel wajib konsisten menggunakan bobot netral `font-medium` (atau `font-normal`).
- **Data Tabel Wajib Pure Teks Tanpa Styling**: Seluruh data isian di dalam kolom tabel di website ini **WAJIB murni teks biasa (pure text)** tanpa styling visual apapun.
- **Dilarang Keras**: Menggunakan badge, pill, chip, background warna (`bg-purple-50`, `bg-slate-100`, dll.), border box, atau rounded container pada nilai data di dalam sel tabel.
- **Format Standar**: Nilai data disajikan langsung sebagai teks netral (`text-black text-xs font-medium whitespace-nowrap`).
- **Wajib Siklus 3-Mode Sorting (Dilarang Toggle Biner 2-Arah)**:
  - **State Awal (Default)**: Saat halaman pertama kali dimuat, indikator sort wajib **non-aktif / netral (`sortCol = null`)** dengan ikon netral abu-abu (`↕`). Dilarang langsung aktif dengan background ungu atau panah satu arah.
  - **Siklus Klik**: Klik 1 = Arah 1 (aktif `bg-purple-100 text-purple-deep`), Klik 2 = Arah 2 (berbalik arah), Klik 3 = **Kembali Netral / Reset (`sortCol = null`)**.
- **Efek Hover Baris Tabel Clickable**: Baris tabel yang dapat diklik (*clickable row*) **wajib menggelap saat kursor hover melewatinya** (`table-row-clickable hover:bg-slate-100 active:bg-slate-200 transition-colors duration-150 cursor-pointer`) untuk memberi indikasi interaktif seperti tombol.
- Rujukan aturan lengkap tersedia pada [table-data-styling.md](file:///.agents/rules/table-data-styling.md).

### 6. Standar Format Tanggal & Waktu (Strict Rule)
- **Kolom Tabel**: Seluruh penulisan tanggal dan waktu di dalam kolom tabel wajib menggunakan format numerik ringkas `29/10/2026 12:10` (format `d/m/Y H:i` tanpa detik dan tanpa `WIB`).
- **Desain Card & Field**: Seluruh penulisan tanggal dan waktu di dalam card, subcard, modal, atau field data wajib menggunakan format Bahasa Indonesia baku tanpa imbuhan WIB: `29 Oktober 2026, 12:10` (format `D MMMM Y, HH:mm`).
- Rujukan aturan lengkap tersedia pada [datetime-formatting.md](file:///.agents/rules/datetime-formatting.md).

### 7. Standarisasi Desain Tombol (Strict Rule)
- **Pilar 1: Simpan / Submit Form (Ungu Deep)**: Seluruh tombol submit form, simpan perubahan, atau simpan draf **WAJIB** menggunakan `bg-purple-deep hover:bg-purple-deep/90 text-white font-semibold shadow-xs` lengkap dengan ikon SVG disket/save. **Dilarang keras** menggunakan warna hijau emerald, biru, atau oranye untuk tombol simpan.
- **Pilar 2: Tambah Data Baru (Oranye Brand UC)**: Tombol pemicu tambah data baru di header/action bar wajib menggunakan `bg-orange hover:bg-orange/90 text-white font-semibold shadow-xs` dengan ikon plus SVG.
- **Pilar 3: Batal / Cancel (Background Putih & Teks Ungu)**: Seluruh tombol pembatalan berlabel teks `Batal` atau `Cancel` **WAJIB** menggunakan **background putih dan teks ungu** (`bg-white hover:bg-purple-50 text-purple-deep font-semibold border border-[#D9C2F0] shadow-xs`).
- **Pilar 4: Delete / Hapus (Background Merah Solid & Teks Putih)**: Seluruh tombol penghapusan destruktif atau hapus permanen berlabel `Hapus`, `Delete`, atau konfirmasi hapus **WAJIB** menggunakan **background merah dan teks putih** (`bg-red-500 hover:bg-red-600 text-white font-semibold shadow-xs`) lengkap dengan ikon tempat sampah / trash SVG.
- **Pilar 5: Secondary / Navigasi (Putih Border Ungu)**: Tombol kembali atau filter pasif menggunakan `bg-white border border-[#D9C2F0] text-black hover:text-purple-deep`.
- Rujukan aturan lengkap tersedia pada [button-design-system.md](file:///.agents/rules/button-design-system.md) dan [cancel-button-styling.md](file:///.agents/rules/cancel-button-styling.md).

### 8. Propagasi Perubahan Desain Global & Pembuatan Komponen Masa Depan (Strict Rule)
- **Propagasi Menyeluruh (Global Propagation)**: Setiap kali ada permintaan perubahan atau penyempurnaan desain pada suatu komponen/elemen UI (seperti field isian, card, tombol, modal, tabel, label, atau icon), perubahan tersebut **WAJIB langsung diterapkan ke seluruh komponen serupa** di seluruh halaman dan view proyek tanpa terkecuali.
- **Standar Desain Komponen Baru (Future Creation)**: Setiap pembuatan view, modal, card, atau elemen UI baru di masa mendatang **WAJIB secara otomatis mengadopsi prinsip dan pola desain terbaru** yang telah disepakati dan dibakukan. Dilarang menggunakan pola desain lama yang sudah digantikan.

### 9. Proteksi Perubahan Belum Disimpan / Unsaved Changes Guard & Integritas DOM (Strict Rule)
- **Dilarang Menutup Langsung Jika Ada Perubahan Karakter**:
  - Setiap kali ada data yang ditambah, dikurangi, atau diubah (meskipun hanya 1 karakter), penutupan form melalui tombol Close `X`, tombol `Batal`, maupun perpindahan tab/navigasi **DILARANG KERAS langsung menutup**.
  - Sistem **WAJIB memunculkan modal konfirmasi Unsaved Changes** untuk mencegah pengguna kehilangan data isian secara tidak sengaja.
  - Form hanya boleh ditutup jika pengguna secara eksplisit menekan opsi konfirmasi "Ya, Tinggalkan".
- **Pelacakan Dirty State Otomatis**:
  - Seluruh form mode edit dan modal edit diwajibkan memiliki mekanisme `isFormDirty()` dengan pemantauan (*watcher*) state secara real-time.
- **Integritas Pohon DOM & Scope Alpine.js (Strict Structural Rule)**:
  - Pada berkas Blade monolitik/besar (>500 baris) yang menggunakan root container Alpine (`x-data="clientProfileDetail()"` atau sejenisnya), **dilarang keras** meninggalkan tag penutup `</div>` berlebih di tengah file. Tag ekstra akan menutup container Alpine secara prematur, menyebabkan modal konfirmasi terlempar keluar dari scope dan membuat tombol `X` serta `Batal` mati total.
  - Seluruh modal wajib berada di dalam scope root Alpine (atau dibungkus `<template x-teleport="body">` di dalam scope root) dan container root Alpine **wajib ditutup tepat di akhir file sebelum `@endsection`** dengan keseimbangan tag div `0 unclosed / 0 extra`.
  - Kontrak metode navigasi inti seperti `handleTabClick(targetTab)` dan `handleCancelEdit()` **dilarang dihapus atau di-bypass**.
- **Standar untuk Pengembangan Selanjutnya (*Future Changes & Creation*)**:
  - Seluruh penambahan form, mode edit, modal pengeditan, atau komponen pengisian data baru di masa depan **WAJIB secara otomatis mengimplementasikan handling proteksi unsaved changes ini tanpa perlu diminta ulang atau ditest manual**.
- Rujukan aturan lengkap dan catatan post-mortem tersedia pada [unsaved-changes-guard.md](file:///.agents/rules/unsaved-changes-guard.md).

### 10. Sinkronisasi Biodata Klien & Standar Modal Formulir (Dossier) (Strict Rule)
- **Auto-Sync Biodata ke Berkas Formulir**:
  - Setiap kali data master klien diperbarui dan disimpan pada tab Biodata Klien (`/clients/{id}`), backend **WAJIB otomatis menyinkronkan (auto-sync)** data perubahan (nama, tanggal/tempat lahir, gender, telepon, email, agama, pendidikan, alamat) ke seluruh arsip berkas formulir klien di tabel `client_forms.answers` melalui method `Client::syncToClientForms()`.
  - Staf cukup melakukan perbaikan 1 kali di tab Biodata Klien untuk menjamin konsistensi data di seluruh sistem.
- **Presisi Modal Formulir 1-to-1 Per Jenis Layanan**:
  - Tampilan modal berkas formulir (`client-profile-detail.blade.php` dan `client-forms-summary-results.blade.php`) **WAJIB** presisi 1-to-1 sesuai template formulir aslinya (`dewasa`, `pra_nikah`, `pernikahan`, `anak`, `non_industri`, `biography_en`, `industri`).
  - **Dilarang Keras** menampilkan section atau field formulir lain yang tidak relevan (seperti pertanyaan psikologis konseling dewasa di modal pra-nikah dengan nilai `-`).
  - **Judul Head Card Modal**: Wajib menggunakan nama jenis formulir/layanan (misal: "Konseling Pra-Nikah"), bukan nama klien.
  - **Waktu Pengisian Form**: Diletakkan di subcard Lembar Persetujuan pada sisi kanan dengan teks hitam pekat (`text-black text-base font-medium`).
- Rujukan aturan lengkap tersedia pada [client-form-sync-and-dossier.md](file:///.agents/rules/client-form-sync-and-dossier.md).
