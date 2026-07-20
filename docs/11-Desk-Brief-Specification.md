# 11 - Spesifikasi & Aturan Modul Desk Brief

Dokumen ini berisi spesifikasi ketat dan aturan pengembangan untuk modul **Desk Brief** (baik *frontend* di `/desk-brief` maupun *backend/admin* di `/admin/desk-brief`). Aturan ini dirumuskan berdasarkan *Product Requirements Document* (PRD v1.0), desain UI/UX, dan kesepakatan spesifik dengan klien.

---

## 1. Lingkup Komponen (10 Panel Layout)
Berdasarkan arahan klien ("Design nya mah tetap kaya gini"), kita **MEMPERTAHANKAN** seluruh layout 10 panel dari desain HTML eksisting.

Fokus pengembangan adalah meng-upgrade/mengimplementasikan fitur-fitur dari PRD yang belum ada di versi `Index.vue` saat ini, meliputi:
1. **Stock Heatmap** (Upgrade menjadi visualisasi D3 treemap `treemapBinary` full-width).
2. **Panel 4: Regime & Flow Charts** (Tambahkan fitur *dropdown selector* untuk berpindah antara `Regime Score`, `Foreign Momentum v2`, dan `Market Stress`).
3. **Panel 8: Catalyst Calendar** (Tambahkan modal kalender bulanan dan integrasikan Trading Economics API).
4. **Panel 9: Risk Monitor** (Siapkan hook FRED API).
5. **Panel 10: Analyst Takeaway** (Siapkan hook LLM Claude).

Komponen statis lainnya seperti *Key Drivers*, *Cross-Asset*, dan *Smart Money Lens* tetap dipertahankan sesuai desain.

---

## 2. Prinsip Integritas Data
1. **Kejujuran di Atas Estetika:** Angka yang ditampilkan harus berasal dari sumber valid (Database/API). Jika data bersifat proxy atau estimasi, wajib diberi label (misalnya *badge "est"*).
2. **Data Engine Resmi:** Dilarang mengkalkulasi ulang data yang sudah dihitung oleh engine (seperti Regime Score) di level front-end. Selalu gunakan nilai resmi dari tabel database. (Contoh: Skor regime **harus** dari tabel `Skor Harian`).
3. **Pendekatan Snapshot:** Untuk saat ini, sebagian data bersifat *snapshot historis*. Hook API dipersiapkan untuk update data live di masa depan (fallback ke data statis harus berjalan normal saat offline/belum ada data live).

---

## 3. Aturan Pengembangan Frontend (`/desk-brief`)
- **Ekstraksi CSS & Aset:** Ekstrak kelas-kelas CSS, variabel *custom property* (seperti `--bg`, `--green`, `--red`), dan *script* D3.js dari template HTML **hanya untuk komponen yang dikerjakan**. Jangan mencemari CSS global proyek (Vue/Tailwind) dengan *style* yang tidak dipakai.
- **Isolasi Panel:** Setiap panel (misal Panel 8 Kalender) harus dibangun sebagai komponen mandiri (misal `DeskBriefCalendar.vue`) untuk mempermudah pemeliharaan dan menghindari ukuran file komponen yang terlalu bengkak.

---

## 4. Spesifikasi Backend & Admin Panel (`/admin/desk-brief`)
Backend akan menangani sisi *data-live* dan konfigurasi admin.
- **Konfigurasi API:** Harus ada antarmuka (atau di level `.env`) untuk menyimpan key API eksternal secara aman (misal `FRED_API_KEY`, `ANTHROPIC_API_KEY`, dan `TRADING_ECONOMICS_API_KEY`). **Dilarang keras mengekspos API key di front-end.**
- **Proxy Backend:** Buat proxy endpoint di Node.js/Express (atau via endpoint Laravel Inertia) yang menghubungkan front-end dengan layanan seperti FRED API (untuk Risk Monitor), Claude (untuk Analyst Takeaway), dan Trading Economics API (untuk Catalyst Calendar). Front-end hanya melakukan request ke endpoint internal kita.
- **Manajemen Fallback:** 
  - Untuk kalender (Panel 8), sistem akan terintegrasi langsung dengan API **Trading Economics** (sesuai arahan klien). Pastikan proxy backend meng-handle request ini dengan API key terkait.
  - Untuk Analyst Takeaway (Panel 10), pastikan sistem otomatis kembali ke output *rule-based* jika LLM gagal atau API belum dikonfigurasi.

---

**PENTING**: Jika dalam pengerjaan Anda menemukan ada bagian desain/komponen yang tidak ada di 7 daftar wajib pada Poin 1, **TINGGALKAN**. Jaga agar implementasi tetap ramping dan sesuai dengan apa yang dibayarkan dan disetujui klien.
