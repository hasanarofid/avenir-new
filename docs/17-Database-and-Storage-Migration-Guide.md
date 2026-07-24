# Panduan & Aturan Migrasi Database dan Storage (Demo ⇄ Live)

Dokumen ini berisi spesifikasi, panduan teknis, *rules*, serta *tricks & tips* untuk memindahkan/menyinkronkan data **Database** dan **Storage (Cloudflare R2)** antara domain **Demo** (`https://demo.researchavenir.com/`) dan **Live** (`https://researchavenir.com/`).

---

## 1. Domain & Environment Overview

- **Demo Environment**: `https://demo.researchavenir.com/` (Bucket R2: `avenir-demo`, DB: `avenir_demo`)
- **Live Environment**: `https://researchavenir.com/` (Bucket R2: `avenir-live`, DB: `avenir_live`)

---

## 2. Aturan Utama Migrasi (Migration Rules)

1. **Keamanan Credential**:
   - **DILARANG HARDCODE PASSWORD/TOKEN/API KEY**: Seluruh credential harus dibaca dari Environment Variables (`.env`) atau GitHub Secrets (`CF_R2_ACCESS_KEY_ID`, `DB_PASSWORD`, dll). Dokumentasi dan script tidak boleh mencantumkan credential sensitif.
2. **Aturan Sanitasi Data (Live ➡️ Demo)**:
   - Ketika menyalin DB dari **Live ke Demo**, data sensitif pengguna (email asli, hash password, token API, data transaksi) **wajib disanitasi / di-masking** agar environment Demo tidak membocorkan data ril pengguna.
3. **Aturan Proteksi Data Live (Demo ➡️ Live)**:
   - **DILARANG OVERWRITE TOTAL DB LIVE**: Jangan melakukan `DROP DATABASE` atau menimpa tabel `users`/`sessions`/`personal_access_tokens` di Live dari Demo. Hanya lakukan **Insert / Update Incremental** pada data konten (seperti Berita, PDF Riset, Master Sektor, Desk Brief).
4. **Prosedur Pre-Migration & Post-Migration**:
   - **Wajib Backup Pre-Migration**: Buat database snapshot / `.sql` dump dari database tujuan *sebelum* eksekusi migrasi.
   - **Flush Cache Post-Migration**: Setelah migrasi selesai, wajib jalankan perintah flush cache di Laravel:
     ```bash
     php artisan cache:clear && php artisan config:clear && php artisan route:clear
     ```

---

## 3. Migrasi Storage (Cloudflare R2)

Penyimpanan asset (PDF riset, image berita, logo, avatar) menggunakan Cloudflare R2 yang kompatibel dengan API AWS S3. Referensi dasar R2 tercantum pada [13-Cloudflare-Storage-Specification.md](file:///home/hasan/Documents/hasanarofid/avenir/default-avenir/docs/13-Cloudflare-Storage-Specification.md).

### A. Sinkronisasi via AWS CLI

Gunakan perintah `aws s3 sync` dengan menunjuk endpoint Cloudflare R2:

```bash
# Export credential sementara (di session terminal, jangan hardcode di script)
export AWS_ACCESS_KEY_ID="$R2_ACCESS_KEY"
export AWS_SECRET_ACCESS_KEY="$R2_SECRET_KEY"
export R2_ENDPOINT="https://<ACCOUNT_ID>.r2.cloudflarestorage.com"

# 1. LIVE ➡️ DEMO (Sync asset produksi ke staging)
aws s3 sync s3://avenir-live s3://avenir-demo --endpoint-url $R2_ENDPOINT

# 2. DEMO ➡️ LIVE (Publish asset baru dari demo ke produksi)
aws s3 sync s3://avenir-demo s3://avenir-live --endpoint-url $R2_ENDPOINT
```

### B. Tips Sinkronisasi Storage
- **Simulasi Dry-Run**: Gunakan flag `--dryrun` untuk memeriksa daftar file yang akan ditransfer sebelum benar-benar mengeksekusi:
  ```bash
  aws s3 sync s3://avenir-demo s3://avenir-live --endpoint-url $R2_ENDPOINT --dryrun
  ```
- **Hati-hati Opsi `--delete`**: Opsi ini akan menghapus file di bucket tujuan jika tidak ada di bucket asal. Hindari penggunaan opsi ini kecuali memang membutuhkan sinkronisasi total yang identik.
- **Automasi via GitHub Actions**: Eksekusi sinkronisasi storage disarankan melalui GitHub Actions workflow (`workflow_dispatch`) agar tercatat dalam log audit deployment.

---

## 4. Migrasi Database (Demo ⇄ Live)

### A. DEMO ➡️ LIVE (Push Content / Riset Baru)
- **Prinsip**: Hanya memindahkan data konten tanpa mengganggu data user/transaksi di Live.
- **Tabel Target**: `research_reports`, `news_articles`, `desk_briefs`, `sector_masters`, `ownership_intelligence`.
- **Perintah Export Selective**:
  ```bash
  mysqldump -u $DB_USER -p $DB_NAME \
    research_reports news_articles desk_briefs sector_masters ownership_intelligence \
    > content_export.sql
  ```
- **Perintah Import ke Live**:
  ```bash
  mysql -u $LIVE_DB_USER -p $LIVE_DB_NAME < content_export.sql
  ```

### B. LIVE ➡️ DEMO (Refresh Staging Data)
- **Prinsip**: Mengisi staging Demo dengan data riil dari Live untuk pengujian, disusul sanitasi data sensitif.
- **Dump Full Database Live**:
  ```bash
  mysqldump -u $LIVE_DB_USER -p $LIVE_DB_NAME > live_backup.sql
  mysql -u $DEMO_DB_USER -p $DEMO_DB_NAME < live_backup.sql
  ```
- **Sanitasi Data User (Wajib)**:
  ```sql
  -- Jalankan SQL ini di DB DEMO setelah import
  UPDATE users SET 
    email = CONCAT('user_', id, '@demo-avenir.test'),
    password = '$2y$10$YourFixedDemoPasswordHashHere...';
  ```

---

## 5. Fitur Sync pada Panel Admin (Spesifikasi Masa Depan)

Untuk memudahkan Admin melakukan migrasi tanpa CLI, Admin Panel menyediakan modul `/admin/system/data-sync`:

1. **Export Package (Admin Demo)**:
   - Memilih modul data (Riset, Berita, EOD, Ownership) dan meng-generate file payload JSON + daftar asset R2.
2. **Import Package (Admin Live)**:
   - Upload file payload JSON di Admin Live, lalu sistem mengeksekusi `updateOrCreate` secara aman.
3. **Trigger Storage Sync Job**:
   - Menjalankan Laravel Queued Job untuk melakukan copy object R2 dari bucket demo ke live via AWS S3 SDK.
4. **Safety Interlock**:
   - Meminta verifikasi 2-langkah (misal memasukkan teks konfirmasi `"MIGRATE TO LIVE"`) sebelum perubahan database di Live dieksekusi.
