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

### 5. Penyajian Data Tabel & Pengurutan (Strict Rule)
- **Dilarang Font Bold pada Teks di Dalam Tabel (Strict Rule)**: Dilarang keras menggunakan bobot font tebal (`font-bold`, `font-semibold`, `font-extrabold`, tag `<b>`, tag `<strong>`) untuk seluruh teks data yang berada di dalam baris/sel tabel (`<td>`). Seluruh teks isian tabel wajib konsisten menggunakan bobot netral `font-medium` (atau `font-normal`).
- **Data Tabel Wajib Pure Teks Tanpa Styling**: Seluruh data isian di dalam kolom tabel di website ini **WAJIB murni teks biasa (pure text)** tanpa styling visual apapun.
- **Dilarang Keras**: Menggunakan badge, pill, chip, background warna (`bg-purple-50`, `bg-slate-100`, dll.), border box, atau rounded container pada nilai data di dalam sel tabel.
- **Format Standar**: Nilai data disajikan langsung sebagai teks netral (`text-black text-xs font-medium whitespace-nowrap`).
- **Default Filter / Indikator Sort Wajib Non-Aktif**: Saat halaman tabel pertama kali dimuat, seluruh header kolom wajib menampilkan ikon netral (`↕`) tanpa background aktif atau panah pengurutan aktif. Pengurutan baru aktif ketika pengguna mengeklik salah satu header kolom.
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

