# Spesifikasi Penyimpanan Cloudflare R2

Dokumen ini menjelaskan arsitektur, konfigurasi, dan mekanisme sinkronisasi data terkait penggunaan **Cloudflare R2** sebagai *storage system* di aplikasi Avenir.

## 1. Arsitektur Storage

Kita menggunakan Cloudflare R2 sebagai alternatif dari AWS S3. R2 kompatibel penuh dengan S3 API, sehingga kita bisa menggunakan driver `s3` bawaan dari Laravel (`league/flysystem-aws-s3-v3`) dengan penyesuaian pada `endpoint`.

**Keuntungan R2:**
- Biaya *egress* (keluar data) gratis atau jauh lebih murah.
- Kompatibel dengan alat-alat standar industri (seperti AWS CLI).

## 2. Konfigurasi Environment (ENV)

Di masing-masing *environment* (Demo / Live), konfigurasikan file `.env` sebagai berikut:

```env
# File: .env

FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=your_cloudflare_r2_access_key
AWS_SECRET_ACCESS_KEY=your_cloudflare_r2_secret_key
AWS_DEFAULT_REGION=auto
AWS_BUCKET=avenir-demo # Gunakan 'avenir-live' di server production
AWS_USE_PATH_STYLE_ENDPOINT=false
AWS_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
```

> [!NOTE]
> Pastikan paket `league/flysystem-aws-s3-v3` sudah terinstal di proyek Laravel (jika belum, jalankan `composer require league/flysystem-aws-s3-v3`).

## 3. Aturan Penggunaan dalam Kode

Untuk semua unggahan, hindari menggunakan nama *disk* lokal. Selalu panggil secara generik menggunakan `Storage::disk('s3')` atau cukup menggunakan default disk.

**Contoh Menyimpan File:**
```php
$path = $request->file('avatar')->store('avatars', 's3');
```

**Contoh Menampilkan File:**
Gunakan fitur Temporary URL atau langsung URL jika bucket diset ke *public-read* (R2 custom domain):
```php
$url = Storage::disk('s3')->url($path);
```

## 4. Mekanisme Migrasi dan Sinkronisasi Antar Environment

Kita menyediakan *script* otomatis (GitHub Actions) untuk menyalin data *storage* dari bucket **live** ke bucket **demo**, atau sebaliknya.

Script tersebut menggunakan **AWS CLI** dengan menimpa konfigurasi *endpoint* ke URL Cloudflare R2.

### Menjalankan Sinkronisasi Secara Manual

Jika Anda butuh memindahkan data menggunakan Terminal secara manual, Anda bisa menggunakan perintah berikut:

```bash
# Export credential sementara
export AWS_ACCESS_KEY_ID="<TOKEN_ANDA>"
export AWS_SECRET_ACCESS_KEY="<SECRET_ANDA>"

# Sinkronisasi dari Demo ke Live
aws s3 sync s3://avenir-demo s3://avenir-live \
  --endpoint-url https://<ACCOUNT_ID>.r2.cloudflarestorage.com

# Sinkronisasi dari Live ke Demo (biasanya berguna saat setup Demo awal)
aws s3 sync s3://avenir-live s3://avenir-demo \
  --endpoint-url https://<ACCOUNT_ID>.r2.cloudflarestorage.com
```

### GitHub Actions (CI/CD)

Workflow otomatis disiapkan di `.github/workflows/sync-storage.yml`. 
Workflow ini dapat dijalankan secara manual (*workflow_dispatch*) untuk menyalin asset jika diperlukan. Pastikan rahasia (Secrets) berikut sudah ditambahkan di setelan repositori GitHub Anda:

- `CF_R2_ACCOUNT_ID`
- `CF_R2_ACCESS_KEY_ID`
- `CF_R2_SECRET_ACCESS_KEY`

> [!WARNING]
> Proses *sync* dengan atribut `--delete` dapat menghapus file di *destination bucket* jika tidak ada di *source bucket*. Harap hati-hati mengatur flag tambahan.
