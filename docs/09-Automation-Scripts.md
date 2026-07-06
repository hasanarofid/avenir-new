# Automation & Scripting Guidelines

Selama proses pengembangan sistem ini, kita telah membangun beberapa modul utilitas dan skrip otomatisasi (terutama menggunakan Python) untuk mempercepat migrasi, perbaikan CSS, dan ekstraksi logika dari dokumen statis ke komponen dinamis Vue.

## 1. Lokasi Script
Semua file skrip eksperimental, testing, dan otomatisasi yang BUKAN bagian langsung dari *source code* Laravel/Vue **HARUS** disimpan di dalam direktori `prd-testing/scripts/`. Hal ini bertujuan agar *root* direktori proyek tetap bersih.

Struktur penyimpanan:
- `prd-testing/scripts/python/`: Untuk skrip Python (parsing, refactoring, code generation).
- `prd-testing/scripts/php/`: Untuk skrip PHP murni (pengujian DB mandiri, simulasi fitur).
- `prd-testing/scripts/js/`: Untuk skrip utilitas Node.js.

## 2. Pemanfaatan Kembali (Reusability)
Jika di masa depan Anda dihadapkan pada tugas yang serupa, **DIWAJIBKAN** untuk memeriksa folder `prd-testing/scripts/python/` terlebih dahulu sebelum membuat skrip otomatisasi baru dari nol.

### Daftar Script Python yang Sudah Ada:
1. **HTML to Vue Conversion (`create_vue.py`, `create_vue_vhtml.py`)**: 
   - Digunakan untuk mengubah file HTML mockup/PRD murni menjadi struktur komponen Vue. Berguna ketika klien memberikan desain mentah yang panjang.
2. **CSS Fixer & Namespacing (`fix_css.py`, `fix_css_2.py`, `fix_vue_css.py`)**: 
   - Digunakan untuk membungkus (namespace) *rule* CSS global dengan class spesifik (misalnya `.app`) untuk mencegah kebocoran CSS (*bleeding*) dari desain statis ke antarmuka Laravel utama.
3. **Data Extraction & Logic Splitting (`split_data_logic.py`, `parse_html.py`, `parse_html_clean.py`)**: 
   - Digunakan untuk mengekstrak data *dummy* berukuran besar (megabytes) dari file Javascript/HTML statis menjadi file JSON terpisah (`public/data/`). Membantu mencegah Vue/Vite kehabisan memori atau nge-hang saat *compile*.
4. **Import Path Fixer (`fix_imports.py`)**:
   - Skrip utilitas untuk melakukan pencarian dan penggantian *path relative* dalam banyak file Vue secara massal.

## 3. Aturan Pembuatan Script Baru
- Skrip yang dibuat untuk tugas temporer (seperti *scraping* data atau konversi struktur kode secara massal) tidak boleh diletakkan di luar `prd-testing/scripts`.
- Selalu berikan nama file yang deskriptif dan buatlah agar kode skrip tersebut generik supaya bisa digunakan kembali (*reusable*).
