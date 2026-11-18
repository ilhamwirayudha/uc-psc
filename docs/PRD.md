# Living PRD — UC PSC Management System

> **Status Dokumen:** Living PRD (Aktif & Terverifikasi)  
> **Tanggal Pembaruan Terakhir:** 18 November 2026  
> **Lokasi Repositori:** `uc-psc` (Laravel `^13.8`, PHP `^8.3`, Blade, Alpine.js, Tailwind CSS via Vite)  
> **Rujukan Standar UI/UX & Kode:** [AGENTS.md](../AGENTS.md)

---

## 1. Ringkasan Produk & Tujuan
**UC PSC Management System** adalah sistem informasi operasional untuk **UC Psychological Services Center (UC PSC)** yang mengelola siklus layanan psikologi secara terpadu: pendaftaran klien melalui formulir publik, manajemen klien dan arsip formulir, penjadwalan layanan **Konseling** dan **Psikotes (Industri & Non-Industri)**, pelacakan progres pengerjaan tim, manajemen beban tugas petugas, sinkronisasi **Google Calendar**, hingga administrasi nota dan pembayaran bersama **UC Finance**.

### Tujuan Utama
1. Menyatukan pencatatan operasional Konseling dan Psikotes yang sebelumnya tersebar di Google Form dan Google Sheets ke dalam satu sistem terpusat.
2. Melacak progres pengerjaan Psikotes **per kandidat** dengan aturan hak akses centang, urutan kerja berurutan (*sequential gate*), dan pemantauan SLA (*deadline* laporan Psikolog dan pengiriman ke klien).
3. Mengotomatiskan sinkronisasi penugasan tim ke modul **Kelola Penugasan** serta sinkronisasi jadwal ke **Google Calendar pribadi** masing-masing petugas.
4. Menjaga konsistensi alur penagihan dan pelunasan bersama **UC Finance**.

---

## 2. Pengguna, Role, & Matriks Hak Akses

Sistem menerapkan **3 role terautentikasi yang murni (tanpa perangkapan peran)** ditambah pengguna publik tanpa login:

| Role | Deskripsi | Ruang Lingkup Tugas |
|---|---|---|
| **Publik / Klien** *(Guest)* | Calon klien individu atau kandidat perusahaan | Mengakses `/daftar/*` untuk mengisi formulir pendaftaran dan melihat tiket konfirmasi. |
| **`admin`** | Administrator operasional UC PSC | Mengelola seluruh master data, pembuatan & pengaturan booking, penugasan petugas, *approval* laporan psikotes, proses administratif (`Surat Keluar`, `Cetak`, `Kirim`, `Resi`, `Bayar`), penagihan ke UC Finance, serta kelola akun Staff & Psikolog. |
| **`staff`** | Staf / Asisten Psikolog | Menjalankan tugas operasional Psikotes sesuai penugasan (`Tester`, `Skorer Manual`, `Skorer Software`, `Editor`) dan membantu *crosscheck* data klien/formulir. |
| **`psikolog`** | Psikolog / Konselor UC PSC | Menangani sesi **Konseling** dan bertugas sebagai **Psikolog Pengampu Psikotes** (pembuat `Draft HPP`). *Tidak merangkap peran Staff.* |

### Aturan Visibilitas Data & Aksi per Role
1. **Admin (`admin`):** Akses penuh ke seluruh modul, seluruh data klien/formulir/booking, tombol **`Approval`**, serta seluruh kolom administratif dan pembayaran.
2. **Staff (`staff`):**
   - **Daftar Klien (`/daftar-klien`) & Formulir Klien (`/formulir-klien`):** Dapat melihat seluruh data klien dan formulir masuk untuk membantu Admin melakukan *crosscheck* data formulir dengan pesan WA/Email PIC perusahaan.
   - **Layanan Psikotes & Aksi Centang Progres:** Hanya dapat mencentang tahapan tugas pada booking/kandidat di mana dirinya secara eksplisit di-assign (`Tester`, `Skorer Manual`, `Skorer Software`, atau `Editor`).
3. **Psikolog (`psikolog`):**
   - **Pembatasan Visibilitas:** Hanya dapat melihat data klien, formulir, dan booking yang **ditangani langsung oleh dirinya** (baik pada sesi Konseling maupun kandidat Psikotes yang di-assign kepadanya).
   - **Aksi Centang Progres:** Hanya dapat mencentang tahap `Psikotes` dan `Draft HPP` untuk kandidat yang di-assign kepadanya.

---

## 3. Master Data & Katalog Tarif

### 3.1 Master Data yang Dibutuhkan
1. **Master Data Perusahaan (`companies`)**
   - Menyimpan seluruh perusahaan klien UC PSC beserta atribut **`Kategori`: `Kerja Sama` atau `Umum`** dan informasi PIC default.
   - Kategori perusahaan menentukan tarif otomatis dari katalog harga saat membuat Booking Psikotes Industri.
2. **Master Data Hari Libur Nasional (`holidays`)**
   - Daftar tanggal libur nasional (di luar Sabtu–Minggu) untuk menghitung SLA pengiriman laporan UC PSC ke klien secara akurat berdasarkan **hari kerja**.
3. **Master Data Psikolog (`counselors` + akun login `psikolog`)**
   - Satu daftar master Psikolog yang digunakan bersama oleh layanan **Konseling** dan **Psikotes**, terhubung ke akun pengguna ber-role `psikolog` agar dapat login, memantau jadwal/kandidat, menghubungkan Google Calendar, dan mencentang progres.
4. **Master Pricelist Layanan (`config/service_catalog.php`)**
   - Seluruh harga pada katalog berikut berstatus **harga dasar sebelum pajak (PPN) dan PPh**:

#### Katalog Layanan Konseling
| Jenis | Metode | Klien | Kategori | Harga / Sesi | Tarif Overtime |
|---|---|---|---|---:|---:|
| **Pro** *(Psikolog Senior)* | Offline \| Online | WNI | Umum | Rp 525.000 | Rp 525.000 |
| **Pro** *(Psikolog Senior)* | Offline \| Online | WNI | Kerja Sama | Rp 450.000 | Rp 450.000 |
| **Pro** *(Psikolog Senior)* | Offline \| Online | WNA | Umum | Rp 775.000 | Rp 775.000 |
| **Pro** *(Psikolog Senior)* | Offline \| Online | WNA | Kerja Sama | Rp 675.000 | Rp 675.000 |
| **Reguler** | Offline \| Online | WNI | Umum | Rp 275.000 | Rp 275.000 |
| **Reguler** | Offline \| Online | WNI | Kerja Sama | Rp 225.000 | Rp 225.000 |
| **Reguler** | Offline \| Online | WNA | Umum | Rp 525.000 | Rp 525.000 |
| **Reguler** | Offline \| Online | WNA | Kerja Sama | Rp 450.000 | Rp 450.000 |

#### Katalog Layanan Psikotes
| Kategori Utama | Metode | Format | Level | Kategori Tarif | Harga Dasar |
|---|---|---|---|---|---:|
| **Psikotes Industri** | Offline & Online *(sama)* | Short | Staf | Umum / Kerja Sama | Rp 575.000 / Rp 500.000 |
| **Psikotes Industri** | Offline & Online *(sama)* | Long | Staf | Umum / Kerja Sama | Rp 700.000 / Rp 600.000 |
| **Psikotes Industri** | Offline & Online *(sama)* | Long | Supervisor | Umum / Kerja Sama | Rp 775.000 / Rp 675.000 |
| **Psikotes Industri** | Offline & Online *(sama)* | Long | Manager | Umum / Kerja Sama | Rp 850.000 / Rp 750.000 |
| **Psikotes Non-Industri Bakat Minat Biasa** | Offline & Online *(sama)* | Short | - | Umum / Kerja Sama | Rp 625.000 / Rp 550.000 |
| **Psikotes Non-Industri Bakat Minat Biasa** | Offline & Online *(sama)* | Long | - | Umum / Kerja Sama | Rp 750.000 / Rp 650.000 |
| **Psikotes Non-Industri Khusus** | UCS MNA (Online) | UCS-MED | - | MMPI | Rp 50.000 |
| **Psikotes Non-Industri Khusus** | UCS MNA (Online) | UCS-DEM | - | MMPI | Rp 50.000 |
| **Psikotes Non-Industri Khusus** | UCS MNA (Online) | UCS-DEM | - | FCAT-FEAST | Rp 200.000 |
| **Psikotes Non-Industri Khusus** | UCS MNA (Online) | UCS-PPSY | - | FCAT - FTPI - FEAST | Rp 250.000 |
| **Psikotes Non-Industri Khusus** | Online & Offline | Konseling Psikotes | - | SSCT - EPPS | Rp 75.000 |

---

## 4. Formulir Pendaftaran Publik & Manajemen Data Klien

### 4.1 7 Jenis Formulir Publik (`/daftar/*`)
| Route | Kode (`form_type`) | Prefix Tiket | Kategori Layanan | Perilaku Saat Submit |
|---|---|---|---|---|
| `/daftar/anak` | `anak` | `ANK` | Konseling | Simpan `Client`, `ClientForm`, & auto-init `Booking` konseling |
| `/daftar/dewasa` | `dewasa` | `DWS` | Konseling | Simpan `Client`, `ClientForm`, & auto-init `Booking` konseling |
| `/daftar/pra-nikah` | `pra_nikah` | `PRN` | Konseling | Simpan `Client`, `ClientForm`, & auto-init `Booking` konseling |
| `/daftar/pernikahan` | `pernikahan` | `NIK` | Konseling | Simpan `Client`, `ClientForm`, & auto-init `Booking` konseling |
| `/daftar/biography-en` | `biography_en` | `BIO` | Fleksibel WNA (`pending`) | Simpan `Client`, `ClientForm`, & auto-init `Booking` (`pending`) |
| `/daftar/non-industri` | `non_industri` | `NIN` | Psikotes Non-Industri | **Hanya simpan `Client` & `ClientForm` berstatus `"Belum Dibooking"`** *(tidak auto-create Booking)* |
| `/daftar/industri` | `industri` | `IND` | Psikotes Industri | **Hanya simpan `Client` & `ClientForm` berstatus `"Belum Dibooking"`** *(tidak auto-create Booking)* |

- Nomor tiket unik: `PSC-{PREFIX}-{YYMMDD}-{4_HEX}`.
- Pembaruan biodata pada master `Client` otomatis tersinkronisasi ke `client_forms.answers` melalui `Client::syncToClientForms()`.
- Penutupan form/modal yang sedang diubah wajib dilindungi konfirmasi *unsaved changes* sesuai [AGENTS.md](../AGENTS.md).

---

## 5. Proses Bisnis & Kebutuhan Fungsional — Layanan Konseling

### 5.1 Alur Layanan & Status Sesi
Status sesi Konseling mengikuti 8 tahapan resmi + pembatalan:
1. `Data Masuk` (`data_masuk`)
2. `Waiting List` — mencari konselor dan/atau jadwal (`waiting_list`)
3. `Sudah Dijadwalkan; Belum Konfirmasi Klien` (`menunggu_konfirmasi`)
4. `Sudah Dijadwalkan & Konfirmasi → Masuk Google Calendar` (`terjadwal_gocal`)
5. `Tagih Pembayaran H-1 & Reminder` (`reminder_h1`)
6. `Perlu Bayar Overtime` (`perlu_bayar_overtime`)
7. `Memerlukan Sesi Lanjutan` (`perlu_sesi_lanjutan`)
8. `Tidak Perlu Sesi Lanjutan / Selesai` (`selesai`)
9. `Dibatalkan` (`batal`)

- **Sumber Pembayaran:** Sementara hanya `Pribadi`.
- **Sesi Lanjutan (*Follow-up*):** Pembuatan booking lanjutan terhubung ke sesi sebelumnya melalui `follow_up_of_booking_id`.

### 5.2 Aturan Durasi & *Overtime*
- Durasi standar per sesi = **60 menit**, dengan masa toleransi hingga **90 menit**.
- Ketika `Jam Masuk` (`session_start`) dan `Jam Keluar` (`session_end`) dicatat:
  - Total durasi ≤ 90 menit → **Tidak Overtime**.
  - Total durasi **> 90 menit** → **Overtime**, dan sistem memicu alur pengajuan nota baru ke UC Finance untuk biaya overtime sesuai tarif paket.

### 5.3 Kebijakan *Reschedule* Konseling
- *Reschedule* diajukan **≥ 24 jam** sebelum jam mulai sesi → pembayaran H-1 yang sudah lunas **tetap berlaku** dan menempel pada jadwal pengganti.
- *Reschedule* diajukan **< 24 jam** sebelum jam mulai sesi atau **saat sesi sedang berlangsung** → pembayaran sebelumnya dinyatakan **hangus**, dan klien dikenakan **biaya ulang** (pengajuan Invoice + VA baru ke UC Finance).

---

## 6. Proses Bisnis & Kebutuhan Fungsional — Layanan Psikotes (Industri & Non-Industri)

### 6.1 Pembuatan Booking Psikotes
1. **Inisiasi:**
   - **Industri:** PIC perusahaan menghubungi UC PSC via WA/Email dengan daftar kandidat + jabatan. UC PSC membalas link `/daftar/industri`. Setiap kandidat mengisi formulirnya sendiri-sendiri sehingga masuk sebagai `ClientForm` berstatus **"Belum Dibooking"**.
   - **Non-Industri:** Klien individu mengisi `/daftar/non-industri` (atau diinput Admin) menjadi `ClientForm` *"Belum Dibooking"*.
2. **Form Buat Booking Psikotes oleh Admin:**
   - **Industri:** Admin memilih **Nama Perusahaan** dari dropdown **Master Perusahaan** (kategori `Kerja Sama` / `Umum`), menginput/mengonfirmasi nama **PIC perusahaan**, lalu **mencentang nama-nama kandidat** dari daftar formulir yang masuk sesuai list PIC menjadi **1 Booking**.
   - **Non-Industri:** Admin memilih/mencentang calon klien individu dari daftar formulir `non_industri` yang belum dibooking.
   - **Konfigurasi Booking:**
     - **Multi-Paket / Multi-Level dalam 1 Booking:** Mendukung kombinasi lebih dari 1 paket & level jabatan beserta jumlah pesertanya (contoh: `Industri Long Online Staf (1) + Supervisor (5)`).
     - **Penanda `KILAT`:** Toggle/checkbox `KILAT` dapat diaktifkan **per kandidat**.
     - **Skema Penagihan (`Invoice`):** `Reguler (v)`, `Rekap Internal UCS (I)`, `Rekap Perusahaan Tertentu (R)`, atau `Gratis / Deal Khusus (-)`.
     - **Toggle `Perlu Surat Keluar?`:** `Ya` atau `Tidak`.
     - **Metode Pengiriman Hasil:** `Softcopy`, `Hardcopy`, atau `Keduanya`.

### 6.2 Penugasan Tim Pelaksana (*Multi-Select*) & Pengecualian Paket Khusus
Admin menentukan penugasan tim untuk 5 peran, seluruhnya mendukung **pemilihan 1 atau lebih petugas (*multi-select*)**:

| Peran Penugasan | Tipe Penugasan | Keterangan |
|---|---|---|
| **Psikolog** | Multi-select **per kandidat** (menghasilkan kuota, misal `Sasa (2) Sisi (2) Tata (2)`) | Mengampu tes psikotes & membuat `Draft HPP`. **Validasi:** Maksimal **1 peserta `KILAT`** per Psikolog di tanggal pelaksanaan yang sama. |
| **Editor** | Multi-select **per kandidat** (menghasilkan kuota, misal `Ester (3) Cece (2)`) | Melakukan editing laporan hasil psikotes (HPP). |
| **Tester** | Multi-select **kolektif di level batch** | Staff yang membantu pelaksanaan tes psikotes di hari H. |
| **Skorer Manual** | Multi-select **kolektif di level batch** | Staff yang mengerjakan skoring manual. |
| **Skorer Software** | Multi-select **kolektif di level batch** | Staff yang mengerjakan skoring menggunakan software psikotes. |

- **Pengecualian Paket `Psikotes Non-Industri Khusus`:** Tidak membutuhkan penugasan **Psikolog** dan **Tester**. Tahap `Psikotes` dicentang oleh Admin atau Staff yang bertugas di job tersebut, sedangkan tahap `Draft HPP` dikerjakan dan dicentang oleh **Admin**.

### 6.3 Pelacakan Progres Per-Kandidat & *Sequential Gate*
Pelacakan dilakukan **per kandidat** dengan urutan centang yang **harus berurutan (*sequential blocking*)**, dan hanya bisa dicentang oleh **Admin** atau **petugas yang di-assign** pada tugas terkait:

1. **`Psikotes` + Status Kehadiran (`Hadir` / `Absen`)** — dicentang oleh Psikolog/Tester yang di-assign (atau Admin/Staff di paket *Non-Industri Khusus*).
   - *Aturan Peserta Absen:* Gugur dari kuota beban deadline Psikolog, **tetapi tetap ditagih penuh** ke klien/perusahaan.
2. **`Skoring`** — baru dianggap selesai apabila **seluruh `Skorer Manual` dan `Skorer Software` yang di-assign** di batch tersebut sudah mencentang konfirmasi selesai.
3. **`Surat Keluar`** *(Kondisional)* — wajib dicentang **Admin** bila toggle `Perlu Surat Keluar? = Ya`; otomatis dilewati bila `Tidak`.
4. **`Draft HPP`** — dicentang oleh **Psikolog** pengampu kandidat tersebut (atau **Admin** pada paket *Non-Industri Khusus*).
5. **`Editing`** — dicentang oleh **Editor** yang di-assign ke kandidat tersebut.
6. **`Cetak`** *(Kondisional)* — wajib dicentang **Admin** bila metode pengiriman adalah `Hardcopy` atau `Keduanya`; otomatis dilewati bila `Softcopy`.
7. **`Approval`** — **mutlak hanya Admin** yang berhak mencentang.
8. **`Kirim`** *(+ `Tanggal Resi` & `No Resi` bila `Hardcopy` / `Keduanya`)* — dicentang oleh **Admin**.
   - **Gerbang Pembayaran (*Payment Gate*):**
     - Jika Skema Penagihan = **`Reguler (v)`**: tombol `Kirim` **terkunci** sampai status **`Bayar` = Lunas**.
     - Jika Skema Penagihan = **`Rekap Internal (I)`** atau **`Rekap Perusahaan (R)`**: **diperbolehkan `Kirim` mendahului `Bayar`** (dengan konfirmasi Admin).
     - Jika Skema Penagihan = **`Gratis / Deal Khusus (-)`**: tidak memerlukan invoice/bayar, langsung boleh `Kirim`.

### 6.4 Jalur Cepat Klien `KILAT` (Opsi A)
- Kandidat yang ditandai `KILAT` tampil otomatis di **Panel "Prioritas KILAT"** pada Dashboard dan halaman Layanan Psikotes beserta *countdown* deadline **H+1**.
- Pada kartu kandidat `KILAT`, tersedia **1 tombol cepat *"Tandai Kilat Terkirim"*** (1 klik, otomatis mencatat timestamp pengiriman) tanpa harus melewati urutan checklist panjang.
- Status **Booking Induk** tetap **Berjalan** hingga seluruh kandidat **Non-Kilat** selesai dan dikirimkan semua.

### 6.5 Aturan SLA / *Deadline* Laporan Psikotes
1. **Deadline Psikolog → UC PSC:**
   - Dihitung **1 klien hadir = 1 hari kalender** *(tidak mempedulikan hari libur/weekend; Sabtu tetap dihitung 1 hari)*.
   - Khusus klien bertanda **`KILAT`**, deadline wajib **H+1** (esok harinya).
   - Apabila melewati batas waktu dan laporan belum disetor, sistem menandai status **Terlambat (Perlu Ditagihkan ke Psikolog)**.
2. **Deadline UC PSC → Klien / PIC:**
   - Laporan **`KILAT`**: maksimal **1 hari kerja** setelah tanggal psikotes.
   - Laporan **`NON-KILAT`**: maksimal **5 hari kerja** setelah tanggal psikotes.
   - Perhitungan hari kerja **mengecualikan Sabtu–Minggu dan Hari Libur Nasional**.

---

## 7. Proses Bisnis Pembayaran & Nota (UC Finance)

Alur pengajuan nota dan pembayaran bekerja secara manual-tercatat (*email-driven*) antara UC PSC, UC Finance, dan Klien:

```
Booking + Pricelist (Tanpa PPN/PPh)
   ──► Email Pengajuan Nota ke UC Finance
          ──► Balasan Email UC Finance: Invoice + VA + Nominal Final (termasuk PPN/PPh / Harga Khusus)
                 ──► Admin Input Nominal Final & VA di Sistem, Kirim ke Klien
                        ──► Klien Bayar & Kirim Bukti Transfer
                               ──► UC PSC Teruskan Bukti ke UC Finance ──► Admin Tandai Lunas ("Bayar")
```

- **Input Harga Final Finance:** Sistem wajib menyimpan **Harga Dasar Pricelist** secara terpisah dari **Nominal Final UC Finance** (karena Finance menentukan PPN/PPh maupun tarif khusus klien tertentu).
- **Waktu Penagihan:**
  - **Konseling:** Penagihan dilakukan **H-1** sebelum sesi; jika terjadi *overtime* atau *reschedule* hangus, diajukan nota/VA baru ke UC Finance.
  - **Psikotes:** Fleksibel saat invoice turun atau menjelang hasil selesai; untuk skema **`Reguler (v)`** wajib lunas sebelum hasil dikirim.
- **Rekap Akhir Bulan (`I` & `R`):** Sistem menyediakan rekap daftar layanan berkode `I` (Internal UCS) dan `R` (Rekap Perusahaan Tertentu) per periode bulanan untuk keperluan penagihan akhir bulan.

---

## 8. Integrasi Google Calendar (`GCal` — OAuth Per-User)

- **Lingkup:** Berlaku otomatis untuk jadwal **Layanan Konseling** dan **Layanan Psikotes**.
- **Mekanisme Autentikasi (OAuth Per-User):** Setiap `admin`, `staff`, dan `psikolog` menghubungkan akun Google pribadi masing-masing pada halaman **Profil Saya** (`/profil-saya`).
- **Sinkronisasi Event:**
  - **Konseling:** Ketika status memasuki *Terjadwal & Konfirmasi (`terjadwal_gocal`)*, event otomatis dibuat ke kalender pribadi **Admin** dan **Psikolog** yang bertugas.
  - **Psikotes:** Ketika jadwal psikotes ditetapkan, event otomatis dibuat ke kalender pribadi **Admin**, **Psikolog**, dan **Staff (`Tester`, `Skorer`, `Editor`)** yang di-assign.
  - Perubahan jadwal (*reschedule*) atau pembatalan otomatis memperbarui/menghapus event di Google Calendar terkait.
- **Fallback Non-Blocking:** Jika ada petugas yang belum menghubungkan OAuth Google-nya, penyimpanan jadwal di aplikasi **tetap berhasil**, dan sistem menampilkan indikator **"GCal belum sinkron"** beserta tombol **"Sync ke GCal"** setelah akun Google terhubung.

---

## 9. Kelola Penugasan (`/kelola-penugasan`) & Dashboard (`/dashboard`)

### 9.1 Kelola Penugasan (*Workload & Task Tracker*)
- Konsep penugasan lama `1 Klien ↔ 1 Staff` (`assigned_staff_id`) **dihapus dan digantikan sepenuhnya**.
- Halaman `/kelola-penugasan` otomatis tersinkron secara *real-time* dari data Booking Psikotes dan Konseling, menampilkan:
  1. **Ringkasan Beban Tugas per Petugas** (tab `Psikolog` dan tab `Staff`): jumlah tugas aktif, tugas `KILAT`, serta status SLA/keterlambatan.
  2. **Daftar Tugas Rinci per Petugas:** klien/perusahaan, tanggal pelaksanaan, peran tugas, dan progres checklist.
  3. **Aksi *Reassign* Tugas:** Admin dapat memindahkan tugas seorang Psikolog/Staff ke petugas lain langsung dari halaman ini; sistem otomatis memperbarui Booking Psikotes/Konseling, hak centang, dan event Google Calendar.

### 9.2 Dashboard Operasional (`/dashboard`)
Menampilkan ringkasan prioritas harian sesuai role:
1. **Panel Prioritas KILAT** (kandidat kilat aktif + *countdown* H+1 + tombol cepat kirim).
2. **Pemantauan SLA Laporan Psikolog** (daftar laporan mendekati deadline / terlambat ditagihkan).
3. **Antrean Formulir Psikotes** (`ClientForm` *"Belum Dibooking"*).
4. **Pengingat Tagihan H-1 & Overtime Konseling**.
5. **Status Sinkronisasi Google Calendar** (jadwal yang masih *"GCal belum sinkron"*).

---

## 10. Status Implementasi & Roadmap Pengembangan (*Gap Analysis*)

### 10.1 Fitur yang Sudah Ada di Codebase Saat Ini
- Autentikasi & manajemen akun `admin` / `staff` ([AuthController.php](../app/Http/Controllers/AuthController.php), [StaffManagementController.php](../app/Http/Controllers/StaffManagementController.php), [AdminManagementController.php](../app/Http/Controllers/AdminManagementController.php)).
- Profil Saya & log aktivitas `UserActivityLog` ([ProfileController.php](../app/Http/Controllers/ProfileController.php)).
- 7 formulir pendaftaran publik `/daftar/*` + tiket ([PublicClientFormController.php](../app/Http/Controllers/PublicClientFormController.php)).
- Manajemen formulir klien, tampilan 1-to-1, cetak PDF, & jadwalkan dari form ([ClientFormManagementController.php](../app/Http/Controllers/ClientFormManagementController.php)).
- Master klien, catatan klien, peserta klien, & `Client::syncToClientForms()` ([ClientController.php](../app/Http/Controllers/ClientController.php), [Client.php](../app/Models/Client.php)).
- Direktori master `Counselor` ([CounselorController.php](../app/Http/Controllers/CounselorController.php)).
- Katalog harga dasar ([service_catalog.php](../config/service_catalog.php)).
- Layanan Konseling: daftar booking, wizard 4 langkah detail booking, 8+1 status konseling, kalkulasi durasi & aturan overtime > 90 menit, status pembayaran UC Finance & VA, serta *follow-up* ([BookingController.php](../app/Http/Controllers/BookingController.php), [booking-session-detail.blade.php](../resources/views/bookings/booking-session-detail.blade.php)).
- Kalender Operasional internal di website ([OperationalCalendarController.php](../app/Http/Controllers/OperationalCalendarController.php)).

### 10.2 Status Implementasi Roadmap (`P1`–`P3`)

Seluruh modul `P1` hingga `P3` telah diimplementasikan ke dalam codebase pada pembaruan **18 November 2026**:

| Prioritas | Modul / Fitur | Status & Catatan Implementasi |
|---|---|---|
| **P1** | **Role `psikolog` & Hak Akses Baru** | **Selesai** — Role `psikolog` terhubung ke `counselors`, visibilitas Opsi 2 pada `ClientPolicy`/`BookingPolicy`/`ClientController`/`ClientFormManagementController`, dan `assigned_staff_id` telah dihapus. |
| **P1** | **Master Perusahaan & Libur Nasional** | **Selesai** — Tabel & CRUD `companies` (`Kerja Sama`/`Umum`) dan `holidays` tersedia di menu Administrasi. |
| **P1** | **Perilaku Submit Form Psikotes** | **Selesai** — `/daftar/industri` & `/daftar/non-industri` menyimpan `ClientForm` berstatus `belum_dibooking` tanpa auto-create `Booking`. |
| **P1** | **Modul Psikotes Multi-Kandidat & Checklist** | **Selesai** — Form pembuatan batch, multi-paket, multi-select 5 peran, anti-bentrok KILAT, pengecualian *Non-Industri Khusus*, sequential gate per kandidat, kehadiran/absen, toggle `Surat Keluar`, mode kirim, dan *Payment Gate*. |
| **P1** | **Jalur Cepat `KILAT` & Hitung SLA** | **Selesai** — Panel Prioritas KILAT + tombol 1-klik `Tandai Kilat Terkirim` + `PsychotestSlaService` (Psikolog & Klien). |
| **P2** | **Penyempurnaan Pembayaran & Konseling** | **Selesai** — Field `final_price`, invoice & bukti transfer Finance, nota overtime terpisah, Rekap Akhir Bulan (`I`/`R`), dan aturan reschedule konseling hangus (< 24 jam / saat sesi). |
| **P2** | **Google Calendar API (OAuth Per-User)** | **Siap Pakai (Menunggu Kredensial `.env`)** — Arsitektur OAuth, token terenkripsi per user, `GoogleCalendarService`, UI `/profil-saya`, indikator `GCal belum sinkron`, dan tombol `Sync ke GCal` selesai; pengujian *live* menunggu `GOOGLE_CLIENT_ID` & `GOOGLE_CLIENT_SECRET` di `.env`. |
| **P2** | **Kelola Penugasan Sinkron** | **Selesai** — `/kelola-penugasan` menjadi *Workload & Task Tracker* otomatis untuk Psikolog & Staff lengkap dengan fitur *reassign*. |
| **P3** | **Dashboard Operasional** | **Selesai** — `/dashboard` menampilkan 5 panel prioritas operasional sesuai role. |
