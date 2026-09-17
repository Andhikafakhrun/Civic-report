# Civic Report — Platform Pelaporan & Akuntabilitas Warga Berbasis AI

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap" />
  <img src="https://img.shields.io/badge/Google_Gemini-3.6_Flash-4285F4?style=for-the-badge&logo=google&logoColor=white" alt="Google Gemini" />
  <img src="https://img.shields.io/badge/Fonnte-WhatsApp_API-25D366?style=for-the-badge&logo=whatsapp&logoColor=white" alt="Fonnte" />
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License MIT" />
</p>

---

## 📌 Tentang Civic Report

**Civic Report** adalah platform modern pelaporan masalah infrastruktur perkotaan bagi warga. Platform ini tidak sekadar menampung laporan lalu membiarkannya menumpuk, melainkan mengubah setiap aduan menjadi ***actionable intelligence*** secara otomatis:

1. **AI Image Verification**: Memvalidasi kesesuaian kategori laporan dan menilai tingkat keparahan (*severity*) menggunakan Google Gemini API.
2. **Geospatial Clustering**: Mengelompokkan laporan-laporan berdekatan dalam radius 100 meter ke dalam satu klaster penanganan menggunakan algoritma Haversine.
3. **Dynamic Priority Scoring**: Menghitung bobot prioritas penanganan dinamis dengan algoritma penuaan data (*time-decay*).
4. **AI Narrative Trend Summary**: Menghasilkan ringkasan tren wilayah berbasis AI di latar belakang (*asynchronous queue*).
5. **Real-time Accountability & Transparency**: Sinkronisasi status penanganan dua arah, notifikasi WhatsApp ke warga pelapor, kewajiban upload bukti penyelesaian, serta galeri publik *Before vs After*.

> Proyek ini dikembangkan untuk kompetisi **Next Gen Developer** (Kategori Campus-Level).

---

## 📑 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Arsitektur Sistem](#-arsitektur-sistem)
- [Tech Stack](#-tech-stack)
- [Sitemap & Hak Akses](#-sitemap--hak-akses)
- [Prasyarat Sistem](#-prasyarat-sistem)
- [Panduan Instalasi Langkah-demi-Langkah](#-panduan-instalasi-langkah-demi-langkah)
- [Konfigurasi API Pihak Ketiga (.env)](#-konfigurasi-api-pihak-ketiga-env)
- [Inisialisasi Akun Admin Pertama](#-inisialisasi-akun-admin-pertama)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Data Demo (Opsional)](#-data-demo-opsional)
- [Pengujian / Testing (PHPUnit)](#-pengujian--testing-phpunit)
- [Struktur Direktori](#-struktur-direktori)
- [Troubleshooting / FAQ](#-troubleshooting--faq)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

### 👥 Sisi Warga (Publik)
- **5 Kategori Infrastruktur**: 
  - `pothole` (Jalan Berlubang)
  - `trash` (Sampah Menumpuk)
  - `streetlight` (Lampu Jalan Mati)
  - `drainage` (Saluran Air Tersumbat)
  - `fallen_tree` (Pohon Tumbang)
- **Pelaporan Praktis & Akurat**: Form upload foto bukti (lengkap dengan preview & tombol reset), deskripsi singkat, nomor WhatsApp aktif, dan penentuan lokasi via GPS atau pin peta Leaflet yang dapat digeser manual (*draggable pin*).
- **Lacak Tanpa Login**: Warga langsung mendapatkan **Kode Pelacakan 8 Karakter** unik tanpa perlu registrasi akun, lengkap dengan tombol salin instan.
- **Transparansi Koreksi AI**: Menampilkan kategori awal pilihan warga vs verifikasi AI jika sistem mendeteksi ketidaksesuaian kategori foto.
- **Notifikasi WhatsApp Otomatis**: Setiap pembaruan status oleh dinas langsung dikirimkan ke nomor WhatsApp warga lengkap dengan tautan pelacakan langsung.

### 🧠 Sisi Intelligence (Background Queue Processing)
- **Verifikasi AI & Klasifikasi Keparahan**: Memanfaatkan Google Gemini API (`gemini-3.6-flash`) untuk menganalisis foto lapangan dan menentukan tingkat keparahan (`low`, `medium`, `high`).
- **Validasi Enum Ketat**: Output AI divalidasi ketat terhadap enum sistem. Nilai yang tidak valid otomatis dibersihkan agar integritas database tetap terjamin.
- **Pengelompokan Otomatis (Geospatial Clustering)**: Aduan dengan kategori yang sama dalam radius 100 meter otomatis dihimpun dalam satu kelompok (*cluster*) penanganan.
- **Skor Prioritas Berbasis Waktu (*Decay*)**:
  - Bobot: *Severity* (40%), *Volume* Laporan (30%), *Velocity* Penambahan Aduan (30%).
  - Mekanisme *decay*: Penurunan skor 3%/hari setelah 7 hari tanpa laporan baru (batas aman lantai skor 40%).
- **Ringkasan Tren Wilayah AI**: Dihasilkan di latar belakang (`GenerateSummaryJob`) melalui polling asynchronous sehingga tidak memblokir UI petugas, dengan hasil analisis di-cache selama 12 jam.

### 🏢 Sisi Dinas / Petugas (Agency Dashboard)
- **Sistem Autentikasi Bertingkat**: Registrasi publik staff otomatis menghasilkan status `pending`. Akun hanya bisa mengakses dashboard setelah disetujui (*approved*) oleh `admin`.
- **Dashboard Geospasial Komprehensif**:
  - Peta Leaflet dengan fitur *fitBounds* otomatis memusatkan tampilan ke semua klaster aktif.
  - Penanda peta (*marker*) berkode warna sesuai tingkat urgensi.
  - Kartu metrik KPI: Total Klaster, Prioritas Tinggi (hanya klaster aktif), Sedang Ditangani, dan Selesai.
- **Pembaruan Berantai (*Cascade Update*)**: Memperbarui status klaster otomatis mengalir ke seluruh laporan individual di dalamnya.
- **Kewajiban Bukti Foto Lapangan**: Petugas wajib mengunggah foto bukti pengerjaan saat menandai status klaster menjadi "Resolved".

### 🏛️ Sisi Transparansi Publik
- **Statistik Terbuka**: Rata-rata durasi penyelesaian kasus, persentase efektivitas, rincian per kategori, serta grafik bulanan laporan masuk vs selesai (Chart.js).
- **Galeri Bukti Nyata (*Before vs After*)**: Komparasi visual kondisi sebelum dan sesudah perbaikan beserta catatan petugas.
- **Integritas Bukti**: Hanya kasus yang saat ini berstatus *resolved* yang ditampilkan. Jika kasus dibuka kembali, bukti lama disembunyikan sampai tuntas kembali.

---

## 🏗️ Arsitektur Sistem

```text
┌─────────────────────────────────────────────────────────────┐
│                      SISI WARGA (PUBLIC)                    │
│  Form Lapor (/lapor)  ──►  GPS / Draggable Pin Leaflet      │
└──────────────────────────────┬──────────────────────────────┘
                               │ Submit Laporan (HTTP POST)
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                   BACKEND LARAVEL CONTROLLER                │
│  • Simpan Report ke Database (Status Awal: 'reported')      │
│  • Buat Tracking Code 8 Karakter Unik                       │
│  • Berikan Respon Cepat ke Warga                            │
│  • Dispatch 'ClassifyReportJob' ke Database Queue           │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Asynchronous Worker)
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKGROUND QUEUE WORKER                  │
│  1. Klasifikasi Foto & Severity via Google Gemini API       │
│  2. Validasi Enum Ketat                                     │
│  3. Clustering Spasial Haversine (Radius 100m)              │
│  4. Kalkulasi Skor Prioritas Dinamis + Decay                │
└──────────────────────────────┬──────────────────────────────┘
                               │
                               ▼
┌─────────────────────────────────────────────────────────────┐
│                   DASHBOARD DINAS / AGENCY                  │
│  • Peta FitBounds Interaktif Leaflet + Marker Prioritas     │
│  • Manajemen Status Klaster (Investigating/In Progress)     │
│  • Penyelesaian Kasus ('Resolved') + Wajib Upload Bukti     │
└──────────────────────────────┬──────────────────────────────┘
                               │
            ┌──────────────────┴──────────────────┐
            │ Cascade Update Status               │
            ▼                                     ▼
┌───────────────────────────┐         ┌───────────────────────────┐
│     NOTIFIKASI WARGA      │         │   AKUNTABILITAS PUBLIK    │
│  • WhatsApp via Fonnte    │         │  • Halaman /track Realtime│
│  • Link Langsung /track   │         │  • Galeri Before/After    │
│                           │         │  • Statistik Transparansi │
└───────────────────────────┘         └───────────────────────────┘
```

---

## 🛠️ Tech Stack

| Komponen | Teknologi | Keterangan |
|---|---|---|
| **Backend** | Laravel 13, PHP 8.3 | Framework PHP modern & robust |
| **Database** | MySQL 8.0 / MariaDB | Penyimpanan relasional & spasial |
| **Frontend** | Blade, Bootstrap 5.3 CDN | Cepat, responsif, tanpa Node.js build step |
| **Styling** | Custom Utility CSS (`public/css/app.css`) | Tema *data-forecast flat utilitarian* |
| **Peta Interaktif** | Leaflet.js + OpenStreetMap | Draggable pin, auto fitBounds, mini-maps |
| **Visualisasi Data**| Chart.js | Grafik statistik bulanan |
| **Kecerdasan Buatan**| Google Gemini API (`gemini-3.6-flash`) | Klasifikasi visual, severity, dan tren naratif |
| **WhatsApp Gateway**| Fonnte API | Pengiriman notifikasi otomatis |
| **Email Service** | Mailtrap Sandbox (SDK) | Simulasi reset password akun staf |
| **Antrean (Queue)** | Laravel Database Queue | Eksekusi background jobs (AI & WhatsApp) |
| **Testing** | PHPUnit 12 | 36 automated unit & feature tests |

---

## 🗺️ Sitemap & Hak Akses

| Halaman | URL | Akses | Deskripsi |
|---|---|---|---|
| **Beranda** | `/` | Publik | Counter statistik, alur sistem, showcase kasus terbaru + modal Before/After |
| **Lapor Masalah** | `/lapor` | Publik | Form aduan, live preview foto, pin GPS interaktif |
| **Lacak Aduan** | `/track` | Publik | Cek status via kode 8 karakter, riwayat & foto bukti |
| **Transparansi** | `/transparansi` | Publik | Statistik performa, grafik perbandingan bulanan |
| **Bukti Nyata** | `/bukti-nyata` | Publik | Galeri Before/After kasus terselesaikan |
| **Profil Platform** | `/profil` | Publik | Informasi tentang platform dan misi Civic Report |
| **Autentikasi** | `/login`, `/register`, `/forgot-password` | Publik | Portal masuk, pendaftaran staf, dan pemulihan akun |
| **Dashboard Dinas**| `/dashboard` | Staff (*Approved*) | Peta sebaran klaster, filter prioritas, modal tindakan |
| **Semua Laporan** | `/reports` | Staff (*Approved*) | Arsip tabel seluruh aduan warga secara mendetail |
| **Manajemen Staf** | `/staff` | Admin Only | Halaman persetujuan (*approval/reject*) pendaftar staf |

---

## 💻 Prasyarat Sistem

Sebelum memulai instalasi, pastikan lingkungan lokal Anda memenuhi spesifikasi berikut:

- **PHP** >= 8.2 atau 8.3
  - Ekstensi wajib: `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `openssl`
- **Composer** (versi 2.x)
- **Database**: MySQL 8.x / MariaDB (bisa melalui Laragon, XAMPP, Docker, atau MySQL Server langsung)
- **Git**
- Koneksi Internet aktif (untuk CDN Bootstrap/Leaflet dan pemanggilan API Gemini/Fonnte/Mailtrap)

---

## 🚀 Panduan Instalasi Langkah-demi-Langkah

Ikuti langkah-langkah berikut secara berurutan:

### 1. Clone Repositori
```bash
git clone https://github.com/Andhikafakhrun/Civic-report.git
cd Civic-report
```

### 2. Install Dependensi PHP
```bash
composer install
```

### 3. Duplikasi File Konfigurasi Environment
```bash
# Untuk Linux / macOS:
cp .env.example .env

# Untuk Windows (Command Prompt):
copy .env.example .env

# Untuk Windows (PowerShell):
cp .env.example .env
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Buat Database MySQL & Konfigurasikan `.env`
Buka phpMyAdmin, DBeaver, HeidiSQL, atau terminal MySQL, lalu buat database baru:
```sql
CREATE DATABASE civic_report CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Buka file `.env` di text editor Anda, lalu sesuaikan koneksi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=civic_report
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Jalankan Migrasi Database
```bash
php artisan migrate
```

### 7. Buat Symbolic Link Storage (Sangat Penting!)
> [!IMPORTANT]
> Langkah ini wajib dijalankan agar foto aduan warga dan foto bukti penanganan dinas dapat ditampilkan di browser.
```bash
php artisan storage:link
```

---

## 🔑 Konfigurasi API Pihak Ketiga (.env)

Agar seluruh fitur cerdas (AI, WhatsApp, dan Reset Password) bekerja optimal, lengkapi bagian berikut di file `.env`:

```env
# Antrean (Wajib 'database' agar asynchronous worker bekerja)
QUEUE_CONNECTION=database

# 1. Google Gemini API (Untuk klasifikasi foto & ringkasan tren)
# Dapatkan gratis di: https://aistudio.google.com/app/api-keys
GEMINI_API_KEY=AIzaSyxxxxxxxxxxxxxxxxxxxxxxx

# 2. Fonnte WhatsApp API (Untuk notifikasi otomatis ke warga)
# Dapatkan gratis di: https://fonnte.com
FONNTE_API_TOKEN=xxxxxxxxxxxxxxxxxxxx

# 3. Mailtrap Sandbox (Untuk simulasi kirim email lupa password staf)
# Daftar gratis di: https://mailtrap.io (pilih Sandbox Inbox)
MAIL_MAILER=mailtrap-sdk
MAILTRAP_HOST=sandbox.api.mailtrap.io
MAILTRAP_API_KEY=xxxxxxxxxxxxxxxxxxxx
MAILTRAP_INBOX_ID=xxxxxxx
MAIL_FROM_ADDRESS=noreply@civicreport.test
MAIL_FROM_NAME="${APP_NAME}"
```

> [!NOTE]
> Jika `GEMINI_API_KEY` atau `FONNTE_API_TOKEN` belum diisi, aplikasi tetap dapat dijalankan untuk pengujian antarmuka, namun fitur klasifikasi AI dan pengiriman WhatsApp akan dilewati (*graceful fallback*).

---

## 🛡️ Inisialisasi Akun Admin Pertama

Sistem menerapkan keamanan ketat: formulir registrasi publik di `/register` hanya membuat akun staf dengan status `pending`. Tidak ada endpoint publik untuk mendaftar sebagai `admin`.

Buat akun Admin pertama kali melalui perintah **Laravel Tinker**:

### Cara Cepat (One-Liner):
```bash
php artisan tinker --execute="App\Models\User::create(['name' => 'Super Admin', 'email' => 'admin@civicreport.test', 'password' => 'admin123', 'role' => 'admin', 'status' => 'approved']);"
```

### Atau Melalui Sesi Interaktif:
```bash
php artisan tinker
```
Lalu masukkan kode:
```php
App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@civicreport.test',
    'password' => 'admin123',
    'role' => 'admin',
    'status' => 'approved',
]);
exit;
```

**Kredensial Default:**
- **URL Login**: `http://127.0.0.1:8000/login`
- **Email**: `admin@civicreport.test`
- **Password**: `admin123`

Setelah masuk sebagai admin, Anda dapat membuka halaman `/staff` untuk menyetujui (*approve*) pendaftaran akun staf lainnya.

---

## ⚡ Menjalankan Aplikasi

Aplikasi membutuhkan **2 terminal** yang berjalan bersamaan:

### Terminal 1: Web Server
```bash
php artisan serve
```
Akses aplikasi melalui browser di: **`http://127.0.0.1:8000`**

### Terminal 2: Queue Worker (Wajib)
> [!IMPORTANT]
> Klasifikasi foto oleh AI, pengelompokan laporan, notifikasi WhatsApp, dan penyusunan narasi tren dijalankan di background queue. Queue worker harus selalu menyala:
```bash
php artisan queue:work
```

---

## 🧪 Data Demo (Opsional)

Jika Anda ingin mengisi data simulasi klaster dan aduan (misalnya untuk demo atau presentasi):

1. Masuk ke halaman publik `/lapor`, isi dan kirimkan minimal **1 laporan asli dengan foto**.
2. Jalankan seeder di terminal:
   ```bash
   php artisan db:seed --class=ReportSeeder
   ```
*Catatan: Seeder akan menggunakan kembali sampel foto yang sudah ada untuk membuat beberapa skenario klaster.*

---

## 🧪 Pengujian / Testing (PHPUnit)

Test suite dijalankan pada database pengujian terpisah (`civic_report_test`) sehingga **tidak akan merusak** data operasional Anda.

### 1. Buat Database Khusus Testing Sekali Saja:
```sql
CREATE DATABASE civic_report_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Jalankan Seluruh Test Suite (36 Tests):
```bash
php artisan test
```

### 3. Menjalankan Test Tertentu:
```bash
# Menguji sinkronisasi status cluster dengan pelacakan warga
php artisan test --filter=StatusSyncTest

# Menguji integritas galeri Bukti Nyata
php artisan test --filter=TransparencyIntegrityTest

# Menguji scoring prioritas dan decay
php artisan test --filter=PriorityScoringServiceTest
```

### Cakupan Pengujian:
- ✅ `PriorityScoringServiceTest`: Algoritma prioritas dan fungsi waktu *decay*.
- ✅ `AiEnumValidationTest`: Memastikan output AI di luar enum valid tidak masuk ke database.
- ✅ `AiServiceConfigTest`: Menjamin konfigurasi network timeout & retry tetap terpasang.
- ✅ `ReportSubmissionTest`: Alur dan validasi pengiriman laporan warga.
- ✅ `ClusteringServiceTest`: Ketepatan pengelompokan koordinat spasial Haversine (radius 100m).
- ✅ `DashboardAccessTest`: Proteksi middleware `auth` dan `approved` pada rute staf.
- ✅ `PasswordResetTest`: Alur lupa password dan mitigasi *user enumeration*.
- ✅ `PageRenderTest`: Memastikan integritas komponen peta dan formulir Blade.
- ✅ `StatusSyncTest`: Sinkronisasi status berantai (*cascade*) dari dashboard ke pelacak warga.
- ✅ `TransparencyIntegrityTest`: Menjaga hanya kasus yang berstatus *resolved* yang tampil di galeri publik.
- ✅ `SummaryPollingTest`: Pengujian async polling pembuatan ringkasan tren AI.

---

## 📂 Struktur Direktori

```text
civic-report/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php            # Login, register, forgot/reset password
│   │   │   ├── DashboardController.php       # Dashboard staf, peta & modal penanganan
│   │   │   ├── HomeController.php            # Landing page publik & showcase kasus
│   │   │   ├── ReportController.php          # Form aduan & pelacakan kode 8 karakter
│   │   │   ├── StaffManagementController.php # Approval akun staf baru (Admin Only)
│   │   │   └── TransparencyController.php    # Halaman transparansi & galeri bukti nyata
│   │   └── Middleware/
│   │       ├── EnsureUserIsAdmin.php         # Proteksi hak akses admin
│   │       └── EnsureUserIsApproved.php      # Proteksi staf berstatus approved
│   ├── Jobs/
│   │   ├── ClassifyReportJob.php             # Queue: Klasifikasi AI, klasterisasi, scoring
│   │   ├── GenerateSummaryJob.php            # Queue: Generate narasi tren wilayah AI
│   │   └── NotifyStatusChangeJob.php         # Queue: Pengiriman pesan WhatsApp
│   ├── Models/
│   │   ├── Report.php                        # Model laporan aduan warga
│   │   ├── ReportCluster.php                 # Model klaster penanganan geospasial
│   │   ├── StatusLog.php                     # Catatan riwayat status & foto bukti
│   │   └── User.php                          # Model pengguna (Warga, Staff, Admin)
│   └── Services/
│       ├── AiClassificationService.php      # Integrasi Google Gemini API & validasi enum
│       ├── ClusteringService.php             # Algoritma Haversine clustering 100m
│       ├── FonnteService.php                 # Integrasi WhatsApp Gateway Fonnte
│       ├── PriorityScoringService.php        # Algoritma skor urgensi & waktu decay
│       └── TrendSummaryService.php           # Generator prompt tren wilayah ke Gemini
├── config/                                   # Konfigurasi aplikasi & third-party services
├── database/
│   ├── migrations/                           # Skema tabel database
│   └── seeders/ReportSeeder.php              # Seeder data demonstrasi kasus
├── public/
│   ├── css/app.css                           # Desain token sistem (flat utilitarian)
│   └── js/                                   # Asset skrip pendukung
├── resources/views/                          # Template antarmuka Blade
│   ├── auth/                                 # Halaman login, register, & reset password
│   ├── dashboard/                            # Tampilan dashboard staf, peta & modal
│   ├── layouts/app.blade.php                 # Master layout (Navbar responsif & footer)
│   ├── reports/                              # Form lapor, index laporan, halaman lacak
│   ├── staff/                                # Antarmuka approval staf (admin)
│   └── transparency/                         # Statistik publik & galeri Before/After
└── tests/                                    # Automated unit & feature tests (PHPUnit)
```

---

## ❓ Troubleshooting / FAQ

### 1. Foto laporan atau bukti penanganan tidak muncul di browser?
**Penyebab**: Folder `storage` belum di-link ke folder `public`.  
**Solusi**: Jalankan perintah:
```bash
php artisan storage:link
```

### 2. Laporan masuk, tapi kategori tidak diverifikasi AI dan WhatsApp tidak terkirim?
**Penyebab**: Antrean background worker belum dijalankan, sehingga pekerjaan (*Jobs*) menumpuk di tabel `jobs`.  
**Solusi**: Buka terminal baru dan jalankan:
```bash
php artisan queue:work
```
Pastikan juga nilai `GEMINI_API_KEY` dan `FONNTE_API_TOKEN` di `.env` sudah diisi dengan benar.

### 3. Akun staf baru tidak bisa membuka halaman `/dashboard`?
**Penyebab**: Akun yang baru didaftarkan berstatus `pending` untuk mencegah akses tidak sah.  
**Solusi**: Masuk sebagai `admin`, kunjungi menu **Manajemen Staf** di `/staff`, lalu klik tombol **Approve** pada nama pengguna tersebut.

### 4. Muncul error cURL / SSL saat memanggil Gemini API di Windows?
**Penyebab**: Konfigurasi sertifikat CA (*Certificate Authority*) pada PHP Windows belum terpasang.  
**Solusi**: Unduh file `cacert.pem` dari [curl.se/ca](https://curl.se/docs/caextract.html), simpan di direktori PHP Anda, lalu tambahkan di `php.ini`:
```ini
curl.cainfo = "C:\path\ke\cacert.pem"
openssl.cafile = "C:\path\ke\cacert.pem"
```
Simpan lalu restart web server Anda.

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi terbuka [MIT License](LICENSE). Anda bebas menggunakan, memodifikasi, dan mendistribusikan kode ini untuk kepentingan akademis, kompetisi, maupun pengembangan lebih lanjut.

---

<p align="center">
  Dibuat dengan dedikasi untuk transparansi infrastruktur perkotaan yang lebih baik. 🏙️✨
</p>