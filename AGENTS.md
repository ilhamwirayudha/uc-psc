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
- **Warna Teks Field Terisi Wajib Hitam**: Seluruh data yang sudah terisi wajib menggunakan `text-black` (bukan ungu, hijau, atau warna lainnya).
- **Format Skoring Kuesioner**: Hanya tampilkan angka skornya saja tanpa teks kriteria dalam kurung (misal: cukup `2`, bukan `2 (Kadang-kadang)`).
- **Pemisahan Field seperti IPK**: Letakkan dalam subcard input data tersendiri di body card, bukan digabung dengan head card.
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

