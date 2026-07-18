# Spesifikasi Fitur Upload EOD Stock Summary

Dokumen ini berisi spesifikasi dan aturan terkait fitur upload data harian EOD (End of Day) Stock Summary dari file Excel.

## 1. Tujuan dan Fungsi Utama
- **Fungsi**: Memasukkan data EOD saham harian (sekitar 900+ emiten) dari file Excel (misal: `Stock Summary-20260717.xlsx`) ke dalam database secara langsung melalui halaman Admin.
- **Tujuan Penggunaan Data**:
  1. **Grafik Historis Emiten**: Data akan digunakan untuk membuat grafik pergerakan harga historis daily untuk masing-masing emiten di halaman depan (frontend). Grafik ini menggunakan data internal kita, bukan mengambil dari API eksternal (Sectors API).
  2. **Historical Regime Score**: Data akan digunakan sebagai dasar perhitungan "historical regime score".
  3. **News (Berita)**: Data juga akan digunakan untuk memperkaya konten di halaman berita (news).

## 2. Kebutuhan UI / Halaman Admin
- **Halaman Upload**: Sediakan form sederhana untuk upload file Excel EOD.
- **Tabel Riwayat Upload**: Halaman yang menampilkan riwayat/history file Excel yang telah di-upload, bukan menampilkan seluruh 900+ baris data secara langsung.
- **Master Sector**: Master terkait sektor sudah ada dan dapat dikelola di `/admin/master-stock`. Proses data EOD jika memerlukan relasi ke sektor harus mengacu pada master data ini.

## 3. Aturan Pengembangan (Backend & Database)
- **Performa (Big Data)**: Proses import file Excel yang berisi ~900 baris data harus di-handle dengan efisien. Disarankan menggunakan teknik chunking, queue/job background, atau bulk insert (misal menggunakan package `maatwebsite/excel`) agar tidak timeout.
- **Struktur Database**: Pastikan tabel yang menyimpan data EOD terindeks dengan baik, terutama pada kolom `ticker/kode_saham` dan `tanggal`, karena data ini akan sering di-query untuk grafik time-series.
- **Relasi**: Pertimbangkan relasi yang baik dengan tabel master emiten dan tabel sektor (sesuai data dari `/admin/master-stock`).

## 4. Aturan Pengembangan (Frontend)
- **Charting**: Gunakan library grafik yang sudah ada/disepakati dalam project untuk menampilkan grafik daily secara mandiri dari API internal kita.
- **Efisiensi**: Endpoint API untuk mengambil data grafik historis harus di-optimize dan di-cache bila memungkinkan agar load halaman cepat.
