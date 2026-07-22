# Standar Operasional Prosedur: Aturan Eksekusi File Uji Coba (Scratch / Test Files)

Dokumen ini mengatur tata letak dan eksekusi file uji coba (seperti script debug PHP, script ekstraksi Python, build tools manual Node.js, maupun file output text/CSV) pada sistem Avenir.

## Latar Belakang

Sering kali pengembang perlu membuat *script* cepat (biasanya dinamai `test_something.php` atau `extract_data.py`) untuk menguji coba koneksi database, menguji regex, melakukan parsing Excel, atau sekadar menyimpan hasil *dump/diff* sementara.

Menempatkan file-file tersebut di *root directory* (folder utama) proyek sangat **DILARANG** karena:
1. Membuat struktur proyek Laravel menjadi sangat kotor (*cluttered*).
2. Membingungkan rekan satu tim mana yang termasuk *core* aplikasi dan mana yang hanya file eksperimen sementara.

## Aturan Penempatan

Semua file non-core wajib ditempatkan di dalam folder khusus:
- **`scratch/`** -> Gunakan folder ini untuk bereksperimen dengan file-file uji coba satu kali (*one-off scripts*).
- **`scripts/`** (atau **`prd-testing/scripts/`**) -> Gunakan folder ini untuk alat otomasi/utilitas Python atau PHP yang sifatnya **digunakan berulang oleh sistem/tim** (misal `query_breadth.php`, `update_excel.py`, `build_ownership_data.py`).

### Jenis File yang Terlarang Berada di Root:
1. `test_*.php`, `test_*.py`
2. `check_*.php`, `debug_*.php`
3. `query_*.php`, `import_*.php`, `update_*.php`, `patch_*.php`
4. File output seperti `*.csv`, `*.json` (yang bukan konfigurasi), dan `*.txt`
5. Script utilitas ekstraksi/modifikasi seperti `extract*.py/js/cjs`, `build_*.cjs`, `fix_*.py`

## Aturan Eksekusi

Saat Anda membuat *script* dan meletakkannya di dalam folder `scratch/`, Anda tetap bisa menjalankan *script* tersebut dengan memanggilnya dari *root* menggunakan Terminal.

**Contoh yang Benar (Posisi di Root Terminal):**
```bash
php scratch/test_flow.php
python3 scratch/test_parser.py
node scratch/extract.cjs
```

Jika *script* Anda memerlukan pembacaan/penulisan file tambahan (seperti menghasilkan file output `.csv`), arahkan *path output* script tersebut agar juga tersimpan di dalam folder `scratch/` (contoh: `file_put_contents(__DIR__ . '/scratch/output.csv', $data)`).

> [!WARNING]
> Jangan pernah memindahkan *core files* Laravel seperti `artisan`, `composer.json`, `package.json`, `.env`, atau konfigurasi Vite dan Tailwind. File tersebut adalah tulang punggung berjalannya framework.
