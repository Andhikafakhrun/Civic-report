# Civic Report — Platform Pelaporan & Akuntabilitas Warga

Platform pelaporan warga untuk 5 kategori infrastruktur perkotaan (jalan berlubang, sampah menumpuk, lampu jalan mati, saluran air tersumbat, dan pohon tumbang) yang tidak berhenti di pengumpulan data, tapi mengubahnya jadi *actionable intelligence* — klasifikasi otomatis via AI, deteksi laporan duplikat/berdekatan geospasial (*clustering*), skor prioritas dinamis ber-decay, dan ringkasan tren naratif berbasis AI — sekaligus menjaga akuntabilitas lewat transparansi publik dan bukti penanganan yang tersinkron penuh antara sisi warga dan sisi dinas.

Dibangun untuk kompetisi **Next Gen Developer** (kategori campus-level).

## Sitemap

| Halaman | URL | Akses | Keterangan |
|---|---|---|---|
| Beranda | `/` | Publik | Counter statistik, alur sistem, showcase kasus terbaru + modal Before/After |
| Lapor | `/lapor` | Publik | Formulir lapor, upload foto + preview, pin peta GPS draggable |
| Lacak Laporan | `/track` | Publik | Cek status laporan via kode 8 karakter, mini-map, riwayat & foto bukti |
| Transparansi | `/transparansi` | Publik | Statistik agregat, rata-rata durasi, grafik bulanan Chart.js, showcase bukti |
| Bukti Nyata | `/bukti-nyata` | Publik | Galeri foto before/after dengan modal detail penanganan |
| Profil | `/profil` | Publik | Informasi platform & misi Civic Report |
| Login / Daftar / Lupa Password | `/login`, `/register`, `/forgot-password`, `/reset-password/{token}` | Publik | Autentikasi staff, registrasi pending, reset password via Mailtrap |
| Dashboard Agency | `/dashboard` | Staff (approved) | Peta cluster fitBounds, filter status/prioritas, ringkasan AI, modal aksi dinas |
| Semua Laporan | `/reports` | Staff (approved) | Arsip seluruh laporan masuk individual dengan foto, status, dan koordinat |
| Manajemen Staff | `/staff` | Admin only | Persetujuan (approve) atau penolakan (reject) akun staff baru |

## Fitur Utama

**Sisi Warga**
- **5 Kategori Laporan**: Jalan Berlubang (`pothole`), Sampah Menumpuk (`trash`), Lampu Jalan Mati (`streetlight`), Saluran Air Tersumbat (`drainage`), dan Pohon Tumbang (`fallen_tree`).
- Lapor masalah lewat foto (dengan live preview & tombol hapus), deskripsi, nomor WhatsApp (format `08xxxxxxxxxx`), dan lokasi — GPS otomatis dengan pin peta Leaflet yang bisa digeser manual (*draggable*).
- Kode pelacakan unik 8 karakter yang di-generate instan tanpa perlu registrasi akun, lengkap dengan tombol salin satu-klik.
- Cek status laporan kapan saja lewat kode tersebut — status **selalu tersinkronisasi** dengan penanganan dinas, dilengkapi peta mini lokasi, riwayat kronologi penanganan, dan foto bukti penyelesaian.
- Transparan bila kategori laporan dikoreksi sistem setelah verifikasi foto AI (menampilkan kategori awal yang dipilih warga vs koreksi AI).
- Notifikasi WhatsApp otomatis ke nomor pelapor (dengan tautan langsung ke halaman lacak) setiap ada perubahan status penanganan.

**Sisi Intelligence (otomatis, di background via queue)**
- **Klasifikasi Foto & Keparahan via AI**: Menggunakan Google Gemini API (`gemini-3.6-flash`) untuk memverifikasi kategori dan menentukan tingkat keparahan (*low*, *medium*, *high*) berdasarkan bukti foto.
- **Validasi Enum Ketat**: Hasil analisis AI divalidasi terhadap daftar nilai yang sah. Nilai di luar enum otomatis dibersihkan dan tidak pernah mengotori database.
- **Clustering Geospasial Otomatis**: Laporan dengan kategori sama dalam radius 100 meter otomatis dikelompokkan ke dalam satu *cluster* penanganan menggunakan kalkulasi jarak Haversine.
- **Skor Prioritas Dinamis**: Dihitung dari bobot keparahan (*severity* 40%), volume laporan (*volume* 30%), dan kecepatan penambahan laporan baru (*velocity* 30%), dilengkapi mekanisme *decay* (penurunan skor 3%/hari setelah 7 hari pasif, batas bawah 40%).
- **Ringkasan Tren Naratif AI**: Analisis tren wilayah dihasilkan secara *asynchronous* (`GenerateSummaryJob` + polling JavaScript) agar tidak memblokir browser staff, dengan hasil di-cache selama 12 jam.

**Sisi Dinas/Agency**
- **Sistem Peran & Persetujuan Akun**: Registrasi mandiri staff menghasilkan akun berstatus `pending`. Hanya akun yang disetujui `admin` yang dapat mengakses dashboard (ditegakkan via middleware `approved`).
- **Reset Password Mandiri**: Alur lupa password via email Mailtrap Sandbox dengan proteksi anti-*user enumeration*.
- **Dashboard Operasional Lengkap**:
  - Kartu KPI: Total Cluster, Prioritas Tinggi (hanya menghitung cluster aktif), Sedang Ditangani, dan Kasus Selesai.
  - Peta Leaflet interaktif: Otomatis memusatkan tampilan (*fitBounds*) ke seluruh cluster aktif dengan penanda berkode warna sesuai prioritas/keparahan.
  - Tabel prioritas: Terurut berdasarkan skor urgensi tertinggi untuk respon cepat.
  - Modal aksi penanganan terpadu: Klik marker peta atau tombol detail pada tabel untuk melihat seluruh foto warga di cluster tersebut, peta mini, riwayat status, dan form pembaruan status.
- **Sinkronisasi Berantai (*Cascade Update*)**: Pembaruan status pada tingkat cluster otomatis mengalir ke seluruh laporan individual di dalamnya — menjamin konsistensi data antara pandangan staff dan halaman pelacakan warga.
- **Kewajiban Bukti Foto**: Wajib mengunggah foto bukti lapangan saat mengubah status menjadi "Resolved".

**Transparansi Publik**
- Beranda publik dilengkapi counter statistik waktu nyata dan galeri interaktif "Baru Selesai Ditangani" dengan modal Before/After.
- Statistik agregat di halaman `/transparansi`: persentase penyelesaian, rata-rata hari penanganan, perincian kategori, serta grafik bulanan Chart.js (laporan masuk vs terselesaikan).
- Galeri "Bukti Nyata" (`/bukti-nyata`): Menampilkan perbandingan Before/After berdampingan, catatan petugas, dan modal rincian kasus lengkap.
- Integritas data: Hanya cluster yang saat ini berstatus *resolved* yang ditampilkan di galeri publik. Jika suatu kasus dibuka kembali, bukti lama otomatis disembunyikan sampai diselesaikan ulang.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3
- **Database**: MySQL 8 + Eloquent ORM
- **Frontend**: Blade + Bootstrap 5.3 CDN (tanpa Node.js build step / Vite untuk kemudahan portabilitas & deployment; token desain khusus di `public/css/app.css` dengan gaya *data-forecast flat utilitarian*)
- **Peta Interaktif**: Leaflet.js + OpenStreetMap (draggable pin di form lapor, fitBounds otomatis & marker-to-modal di dashboard, serta mini-map di modal & lacak)
- **Visualisasi Data**: Chart.js (grafik perbandingan bulanan laporan masuk vs selesai)
- **Kecerdasan Buatan (AI)**: Google Gemini API (`gemini-3.6-flash`) — klasifikasi foto, penentuan severity, validasi enum, dan ringkasan tren wilayah dengan timeout & retry terkonfigurasi
- **Notifikasi WhatsApp**: Fonnte API
- **Pengiriman Email**: Mailtrap (Sandbox API) — reset password akun staff
- **Antrean (*Queue*)**: Laravel Queue (driver database) — memproses tugas AI, notifikasi pesan, dan generate ringkasan tren di background
- **Pengujian (*Testing*)**: PHPUnit (36 unit & feature tests)
- **Lingkungan Lokal**: Laragon / PHP built-in server

## Arsitektur Singkat

```
Warga submit laporan (/lapor, dengan peta pin draggable)
        │
        ▼
Simpan ke DB (status: reported) ── respons instan ke warga
        │
        ▼
ClassifyReportJob (queue, background)
   ├─ Klasifikasi AI + validasi enum (kategori + severity), timeout & retry
   ├─ Clustering geospasial (radius 100m)
   └─ Hitung skor prioritas (dengan decay)
        │
        ▼
Dashboard Agency (peta fitBounds + marker-to-modal, grafik, tabel ringkas + modal detail)
        │
   Staff ubah status ──► cascade ke semua reports dalam cluster
        │                  │
        │                  ▼
        │          NotifyStatusChangeJob (queue) ──► WhatsApp ke warga (+ link /track)
        ▼
/track menampilkan status, peta, riwayat, & foto bukti — selalu sinkron dengan dashboard
        │
        ▼
Transparansi Publik & Bukti Nyata (before/after, hanya cluster yang saat ini resolved)

Staff klik "Generate Ringkasan AI" ──► GenerateSummaryJob (queue) ──► polling JS ──► auto-reload saat siap
```

## Setup Lokal

### Prasyarat
- PHP 8.2+, Composer
- MySQL (lewat Laragon/XAMPP/dsb)
- API key Gemini dari [Google AI Studio](https://aistudio.google.com/app/api-keys)
- API token Fonnte dari [fonnte.com](https://fonnte.com)
- Akun Mailtrap dari [mailtrap.io](https://mailtrap.io) (gunakan Sandbox, bukan Transactional)

### Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Konfigurasi `.env`:
```
DB_DATABASE=civic_report
DB_USERNAME=root
DB_PASSWORD=

GEMINI_API_KEY=your_gemini_key_here
FONNTE_API_TOKEN=your_fonnte_token_here

MAIL_MAILER=mailtrap-sdk
MAILTRAP_HOST=sandbox.api.mailtrap.io
MAILTRAP_API_KEY=your_mailtrap_token_here
MAILTRAP_INBOX_ID=your_sandbox_inbox_id
MAIL_FROM_ADDRESS=noreply@civicreport.test
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database
```

```bash
php artisan migrate
php artisan storage:link
```

### Setup akun admin pertama

Registrasi lewat `/register` selalu menghasilkan akun `staff` berstatus `pending` — tidak ada jalur publik untuk membuat admin. Admin pertama harus dibuat manual lewat Tinker:

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@civicreport.test',
    'password' => Illuminate\Support\Facades\Hash::make('password_anda'),
    'role' => 'admin',
    'status' => 'approved',
]);
```

Setelah itu, login sebagai admin di `/login`, lalu buka `/staff` untuk menyetujui pendaftaran staff lainnya.

### Menjalankan aplikasi

Butuh **2 terminal berjalan bersamaan**:

```bash
# Terminal 1 — web server
php artisan serve

# Terminal 2 — queue worker (wajib untuk AI classification, notifikasi WhatsApp, dan ringkasan AI)
php artisan queue:work
```

Akses di `http://127.0.0.1:8000`

### Data demo (opsional, untuk keperluan presentasi)

```bash
php artisan db:seed --class=ReportSeeder
```

Catatan: seeder butuh minimal 1 laporan asli (dengan foto) sudah ada di database sebelum dijalankan, karena foto laporan demo memakai ulang foto yang sudah pernah diupload.

## Testing

Test dijalankan di atas database **terpisah** (`civic_report_test`), tidak menyentuh data development/production.

Setup sekali saja:
1. Buat database baru bernama `civic_report_test` (collation `utf8mb4_unicode_ci`)
2. Konfigurasi koneksi test sudah diatur di `phpunit.xml`

Jalankan semua test:
```bash
php artisan test
```

Jalankan test tertentu saja:
```bash
php artisan test --filter=StatusSyncTest
```

Cakupan test saat ini (36 test):
- `PriorityScoringServiceTest` — perhitungan skor prioritas & mekanisme decay
- `AiEnumValidationTest` — memastikan hasil AI di luar nilai valid tidak masuk ke database
- `AiServiceConfigTest` — memastikan konfigurasi timeout & retry tetap ada di service AI (mengunci regresi ketahanan jaringan)
- `ReportSubmissionTest` — validasi & alur submit laporan
- `ClusteringServiceTest` — pengelompokan laporan berdasarkan jarak & kategori
- `DashboardAccessTest` — proteksi akses halaman staff (auth + status approved) & KPI prioritas tinggi exclude resolved
- `PasswordResetTest` — alur lupa password & reset, termasuk proteksi user enumeration
- `PageRenderTest` — memastikan elemen kritis (form fields, div peta) tidak hilang saat edit Blade
- `StatusSyncTest` — memastikan update status cluster tercermin di halaman lacak warga
- `TransparencyIntegrityTest` — memastikan galeri Bukti Nyata tidak menampilkan data yang belum selesai, termasuk skenario cluster yang dibuka kembali
- `SummaryPollingTest` — memastikan generate ringkasan AI berjalan asynchronous, bukan memblokir request

## Struktur Folder Penting

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── HomeController.php         # Beranda
│   │   ├── ReportController.php       # Lapor, lacak laporan
│   │   ├── DashboardController.php    # Dashboard staff
│   │   ├── TransparencyController.php # Transparansi & Bukti Nyata
│   │   ├── StaffManagementController.php # Approval staff (admin only)
│   │   └── AuthController.php
│   └── Middleware/
│       ├── EnsureUserIsAdmin.php      # Proteksi route khusus admin
│       └── EnsureUserIsApproved.php   # Proteksi route khusus staff approved
├── Jobs/
│   ├── ClassifyReportJob.php          # AI classification + clustering + scoring
│   ├── NotifyStatusChangeJob.php      # Notifikasi WhatsApp
│   └── GenerateSummaryJob.php         # Ringkasan tren AI di background
├── Models/                # Report, ReportCluster, StatusLog, User
└── Services/
    ├── AiClassificationService.php   # Klasifikasi foto via Gemini + validasi enum + timeout/retry
    ├── ClusteringService.php          # Clustering geospasial
    ├── PriorityScoringService.php     # Perhitungan skor prioritas + decay
    ├── TrendSummaryService.php        # Ringkasan tren via Gemini + timeout/retry
    └── FonnteService.php              # Kirim WhatsApp

database/
├── migrations/
└── seeders/ReportSeeder.php

resources/views/
├── layouts/app.blade.php          # Navbar (active state) + footer
├── home.blade.php                 # Beranda (counter live + showcase bukti selesai)
├── profile.blade.php              # Profil & informasi platform
├── reports/       # create (form lapor + preview + peta pin), index (arsip), track (peta + riwayat)
├── dashboard/      # index (peta fitBounds + grafik + tabel + modal detail)
├── transparency/
│   ├── index.blade.php            # Statistik agregat & grafik Chart.js
│   ├── proof.blade.php            # Galeri bukti nyata (before/after)
│   └── _case-modal.blade.php      # Reusable modal rincian kasus before/after
├── staff/          # index (approval & list staff)
└── auth/           # login, register, forgot-password, reset-password

tests/
├── Unit/
│   ├── PriorityScoringServiceTest.php
│   ├── AiEnumValidationTest.php
│   └── AiServiceConfigTest.php
└── Feature/
    ├── ReportSubmissionTest.php
    ├── ClusteringServiceTest.php
    ├── DashboardAccessTest.php
    ├── PasswordResetTest.php
    ├── PageRenderTest.php
    ├── StatusSyncTest.php
    ├── TransparencyIntegrityTest.php
    └── SummaryPollingTest.php
```

## Catatan Pengembangan

- **5 Kategori Infrastruktur**: Sistem mendukung `pothole` (Jalan Berlubang), `trash` (Sampah Menumpuk), `streetlight` (Lampu Jalan Mati), `drainage` (Saluran Air Tersumbat), dan `fallen_tree` (Pohon Tumbang) dengan warna dan label terpusat di `Report::categories()`.
- **Radius Clustering**: 100 meter (`ClusteringService::RADIUS_METERS`) dikelompokkan berdasarkan kategori yang sama.
- **Bobot Skor Prioritas**: Severity 40%, Volume 30%, Velocity 30%; mekanisme decay mulai aktif setelah 7 hari tanpa laporan baru, turun 3%/hari dengan nilai lantai minimum 40% dari skor asli (`PriorityScoringService`).
- **Modal Rincian Sebelum/Sesudah**: Komponen `_case-modal.blade.php` digunakan bersama secara konsisten di beranda, halaman transparansi, dan galeri bukti nyata untuk menampilkan foto sebelum, foto bukti penyelesaian, catatan staf penangan, dan thumbnail laporan terkait di cluster tersebut.
- **Rate Limiting (Throttle)**: 5 submit laporan/menit, 10 percobaan lacak/menit, 5 login/menit, 3 registrasi/menit, 3 lupa-password/menit, 5 generate ringkasan AI/menit — semua dibatasi per alamat IP.
- **Proteksi Akses Terlindungi**: Semua route dashboard (`/dashboard`, `/reports`) memerlukan login staff **dan** status `approved`, ditegakkan langsung di level middleware `EnsureUserIsApproved`.
- **Kewajiban Bukti Penyelesaian**: Foto bukti wajib diupload saat status cluster diubah menjadi "Resolved".
- **Pembaruan Berantai (*Cascade Update*)**: Update status cluster otomatis mengalir ke semua laporan (`reports`) di dalamnya — memastikan halaman pelacakan warga dan dashboard staf selalu tersinkronisasi 100%.
- **Validasi Nomor WhatsApp**: Format nomor Indonesia divalidasi (`08xxxxxxxxxx`) dan otomatis dikonversi ke standar internasional (`628xx`) sebelum dikirimkan ke gateway Fonnte.
- **Validasi Enum AI Ketat**: Hasil klasifikasi Gemini API divalidasi ketat terhadap daftar nilai kategori dan tingkat keparahan yang diizinkan; data di luar enum otomatis dibersihkan dan tidak disimpan mentah ke database.
- **Transparansi Koreksi AI**: Kategori asli pilihan warga disimpan terpisah (`original_category`) — jika AI mengoreksi kategori berdasarkan bukti foto, penjelasannya ditampilkan transparan baik di halaman pelacakan warga maupun di dashboard dinas.
- **Ketahanan Jaringan AI**: Panggilan API Gemini (klasifikasi & ringkasan tren) memiliki konfigurasi `timeout()` dan `retry()` eksplisit untuk menangani fluktuasi koneksi internet.
- **Pemrosesan AI Asynchronous**: Generate Ringkasan AI dijalankan di latar belakang lewat `GenerateSummaryJob` dan dipantau oleh antarmuka via polling JavaScript tiap 3 detik (maksimum 60 detik) — tidak memblokir browser staf, dengan hasil analisis di-cache selama 12 jam.
- **KPI Prioritas Aktif**: Metrik "Prioritas Tinggi" pada dashboard secara cerdas mengecualikan cluster yang sudah berstatus `resolved`, meski skor matematisnya masih tinggi.
- **Integritas Galeri Bukti Nyata**: Galeri Bukti Nyata (`/bukti-nyata` & `/transparansi`) hanya menampilkan `StatusLog` dari cluster yang **saat ini** berstatus `resolved` — apabila kasus dibuka kembali (status berubah), bukti lama otomatis disembunyikan sampai kasus benar-benar selesai kembali.
- **Keamanan Akun**: Form lupa password menampilkan pesan sukses yang seragam baik untuk email yang terdaftar maupun tidak terdaftar (mencegah *user enumeration*).
- **Email Testing**: Menggunakan Mailtrap Sandbox — email simulasi aman tanpa terkirim ke alamat email nyata pengguna.
- **Desain & Gaya Visual**: Token CSS mandiri di `public/css/app.css` — border-radius minimal (2px), tanpa efek shadow/gradient berlebih, badge outline, font sistem, serta tipografi tabular/mono pada angka statistik dan koordinat.

## Roadmap Lanjutan (di luar scope kompetisi)

- Ekspor rekapitulasi data & laporan berkala ke format PDF / Excel untuk pelaporan dinas
- *Reverse geocoding* otomatis (konversi koordinat GPS ke nama jalan dan kelurahan)
- Penugasan regu teknis operasional & SLA penanganan per dinas/bidang terkait
- Integrasi Progressive Web App (PWA) / aplikasi mobile warga untuk akses kamera instan di lapangan
#   C i v i c - r e p o r t  
 