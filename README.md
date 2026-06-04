# Avenir Research - CMS & Platform Dashboard

Platform CMS dan Panel Administrasi berbasis **Laravel 11**, **Vue 3 (Inertia.js)**, dan **Tailwind CSS v4** yang dioptimalkan dengan estetika premium gelap bertema hijau emerald (`#121614` / `#090b0a`). Didesain khusus untuk platform publikasi riset investasi, analisis pasar saham, dan portal premium Avenir Research.

---

## 👨‍💻 Owner & Creator
Platform ini dideploy dan dikembangkan oleh:
- **Owner**: [Avenir Research](https://www.researchavenir.com/)
- **Developer / Integrator**: [@hasanarofid.site](https://hasanarofid.site)

---

## 🔄 Alur & Proses Bisnis Utama

Avenir Research berfungsi sebagai platform publikasi riset eksklusif serta portal analisis pasar keuangan. Berikut adalah proses bisnis inti yang didukung oleh sistem:

### 1. Pengelolaan Publikasi & Metadata Riset (*Research Metadata*)
* Admin mengunggah dan memperbarui metadata laporan riset melalui Panel Admin.
* Setiap riset menyimpan parameter khusus seperti:
  * **Kode Saham / Emiten** (Ticker/Tags).
  * **Status Akses** (Free / Premium Paywall).
  * **Target Price** & Rentang Pembelian (*Buy/Sell Range*).
  * **Tautan Laporan** (Google Drive / File PDF).
  * **Tipe Laporan** (Analisis Teknikal, Fundamental, Makro, dst).

### 2. Pembatasan Akses Pengguna (*Access Control & Paywall*)
* Pengguna umum (*Guest*) hanya dapat melihat pratinjau landing page, artikel berita (*Posts*), dan daftar judul riset yang tersedia.
* Riset bertanda **Premium/Paid** dikunci di balik paywall. Hanya pengguna terautentikasi dengan status langganan aktif yang dapat mengakses link unduhan laporan lengkap.
* Integrasi pengajuan pembayaran (*Payment Submissions*) untuk mencatat pendaftaran anggota baru ke platform riset.

### 3. Interaksi & Metrik Engagement Anggota
* Anggota terdaftar dapat:
  * Membaca dan mengirimkan komentar (*Comments*) pada riset yang dibuka.
  * Menyukai (*Like*) riset tertentu untuk disimpan dalam daftar favorit.
  * Menerima notifikasi (*Notifications*) personal dari platform.
* Sistem melacak jumlah pembaca secara real-time (*Research Views*) untuk analisis statistik performa riset paling populer.

### 4. Pemasaran & Konfigurasi Halaman Dinamis
* **Pages & Section Editor**: Admin dapat mengubah visual konten landing page (Headline Hero, Item Fitur Keunggulan, Testimoni Klien) secara langsung melalui UI admin tanpa menyentuh kode.
* **Global Settings**: Integrasi nomor WhatsApp CS, tautan aplikasi Android Google Play Store, serta logo Avenir secara dinamis di sidebar dan halaman login.

---

## 💾 Impor Data dari Supabase (Legacy Migration)

Sistem ini memiliki perintah otomatis untuk mengimpor dan menormalkan data historis yang diekspor dari database Supabase Avenir Research versi lama:

```bash
php artisan avenir:import-legacy-data
```

**Proses yang berjalan di balik layar:**
1. Membaca file ekskresi CSV dari folder `public/csv-supabase/`.
2. Membersihkan format ISO Timestamp PostgreSQL menjadi MySQL-compatible datetime.
3. Melakukan import data secara relasional ke tabel:
   * `legacy_profiles` (Akun pengguna lama).
   * `research_metas` (Laporan riset).
   * `research_likes` & `research_views` (Statistik interaksi historis).
   * `comments` (Komentar lama).
   * `notifications` & `trial_email_history`.
   * `payment_submissions` (Histori pembayaran).
4. Penanganan *Orphaned Row* otomatis dengan mengamankan batasan kunci asing (*foreign key constraints*) selama raw ingestion.

---

## 🚀 Panduan Instalasi & Jalankan Lokal

### 1. Clone & Setup Environment
Salin file konfigurasi `.env.example` menjadi `.env` lalu sesuaikan kredensial database Anda (MySQL / MariaDB).

### 2. Pasang Dependensi
```bash
# Dependensi PHP
composer install

# Dependensi Node.js
npm install
```

### 3. Migrasi & Seed Database Awal
```bash
php artisan migrate:fresh --seed
```

### 4. Impor Data Historis Supabase
```bash
php artisan avenir:import-legacy-data
```

### 5. Buat Tautan Storage
```bash
php artisan storage:link
```

### 6. Kompilasi Aset Frontend & Jalankan Server
```bash
# Terminal 1: Compile & Watch Assets (Vite)
npm run dev

# Terminal 2: PHP Local Server
php artisan serve
```

---

## 🔑 Kredensial Akses Default (Development)
* **Admin**: `admin@cms.com` / `password`
* **Editor**: `editor@cms.com` / `password`
* **Client / Subscriber**: `client@cms.com` / `password`
