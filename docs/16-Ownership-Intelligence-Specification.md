# Spesifikasi Fitur: Ownership Intelligence

Dokumen ini menjelaskan arsitektur, alur data, referensi file, dan aturan pengembangan untuk fitur **Avenir Ownership Intelligence** — sistem analisis kepemilikan saham berbasis data KSEI.

---

## 1. Referensi Dokumen

| Dokumen | Lokasi |
|---|---|
| PRD (versi final) | `prd-testing/ownership/new/PRD-Avenir-Ownership-Intelligence-2.pdf` |
| Mockup HTML | `prd-testing/ownership/new/Avenir-OwnershipIntelligence-6-31.html` |
| File Mapping Excel (contoh) | `prd-testing/ownership/new/Ownership_Mapping-3.xlsx` |
| Contoh File KSEI Bulanan | `prd-testing/ownership/new/peng-06-00015-*.xlsx` |

> [!NOTE]
> Selalu baca PRD terbaru (`-2.pdf`) jika ada konflik interpretasi dengan versi sebelumnya.

---

## 2. Arsitektur & Alur Data

### Jenis Data Input (dari Panel Admin)
Admin mengunggah **4 file Excel sekaligus** di halaman `/admin/desk-brief/ownership`:

| Kode Variabel | Jenis File | Keterangan |
|---|---|---|
| `file_daily_5pct` | KSEI Harian ≥5% | `peng-...-lima-persen.xlsx` — Pergerakan harian pemegang saham ≥5% |
| `file_monthly_1pct` | KSEI Bulanan Komposisi 1% | `peng-...-satu-persen.xlsx` |
| `file_monthly_classification` | KSEI Bulanan Klasifikasi | `peng-...-klasifikasi.xlsx` |
| `file_monthly_type` | KSEI Bulanan Tipe Domestik/Asing | `peng-...-tipe.xlsx` |

### Alur Pemrosesan (Pipeline)

```
Admin Upload 4x Excel
    → OwnershipController::upload()
    → File disimpan di storage/app/ownership_excel/
    → ownership_snapshots row dibuat (file_path = null)
    → ProcessOwnershipCsvJob::dispatch() → (Queue)

ProcessOwnershipCsvJob
    → Panggil scripts/build_ownership_excel.py via shell_exec
    → Output: JSON (ownership_data_<id>.json)
    → Simpan di storage/app/public/ownership_data/
    → Update ownership_snapshots.file_path

ExtractKseiOwnershipJob (dipanggil dari dalam ProcessOwnershipCsvJob)
    → Parse 4 file Excel
    → Isi tabel: ownership_entities, ownership_edges, ownership_changes, ownership_audits
```

> [!IMPORTANT]
> Script Python yang **WAJIB ADA** di `scripts/build_ownership_excel.py` adalah inti dari pipeline ini. **Jangan** hapus atau pindahkan file ini.

---

## 3. Tabel Database yang Digunakan

| Tabel | Fungsi |
|---|---|
| `ownership_snapshots` | Metadata setiap sesi upload (tanggal, path JSON) |
| `ownership_entities` | Entitas kepemilikan (emiten, individu, grup) |
| `ownership_edges` | Relasi/jaringan kepemilikan antar entitas |
| `ownership_changes` | Perubahan kepemilikan antar dua snapshot |
| `ownership_audits` | Audit detil per emiten per snapshot |

> [!WARNING]
> Sebelum membuat query atau migration pada tabel-tabel di atas, baca terlebih dahulu `docs/04-Database-Architecture.md`. Jangan berasumsi struktur kolom tanpa memeriksa migration yang ada.

---

## 4. Referensi File Backend

| File | Path | Fungsi |
|---|---|---|
| Controller Admin | `app/Http/Controllers/Admin/OwnershipController.php` | Upload, delete, get data |
| Controller Admin (UBO) | `app/Http/Controllers/Admin/OwnershipManualInputController.php` | Input manual UBO/grup |
| Job Utama | `app/Jobs/ProcessOwnershipCsvJob.php` | Orkestrasi: panggil Python & simpan JSON |
| Job Ekstraksi | `app/Jobs/ExtractKseiOwnershipJob.php` | Parse Excel → isi tabel DB |
| Script Python | `scripts/build_ownership_excel.py` | Kalkulasi ownership, output JSON |

---

## 5. Referensi File Frontend

| File/Folder | Path | Fungsi |
|---|---|---|
| Halaman FE (real data) | `resources/js/Pages/OwnershipIntelligence/Index.vue` | Route `/desk-brief/ownership-intelligence` |
| Halaman FE (mockup) | `resources/js/Pages/OwnershipIntelligence/IndexMockup.vue` | Route `/desk-brief/ownership-intelligence-mockup` |
| Komponen-komponen | `resources/js/Pages/OwnershipIntelligence/Components/` | Sidebar, Pane, KpiGrid, dll. |
| Composable data | `resources/js/Composables/useOwnershipLogic.js` | Fetch & state management data dari API |
| CSS Khusus | `public/css/ownership-intelligence.css` | Styling halaman ini (di-load dinamis saat `onMounted`) |

---

## 6. Referensi Route

| Method | Route | Controller | Nama Route |
|---|---|---|---|
| GET | `/desk-brief/ownership-intelligence` | `DeskBriefController::ownership` | `desk-brief.ownership` |
| GET | `/desk-brief/ownership-intelligence-mockup` | `DeskBriefController::ownershipMockup` | `desk-brief.ownership-mockup` |
| GET | `/admin/desk-brief/ownership` | `Admin\OwnershipController::index` | `desk-brief.ownership.index` |
| GET | `/admin/desk-brief/ownership/data` | `Admin\OwnershipController::getOwnershipData` | `desk-brief.ownership.data` |
| POST | `/admin/desk-brief/ownership/upload` | `Admin\OwnershipController::upload` | `desk-brief.ownership.upload` |
| DELETE | `/admin/desk-brief/ownership/{id}` | `Admin\OwnershipController::destroy` | `desk-brief.ownership.destroy` |

---

## 7. Aturan Pengembangan

1. **Jangan ubah struktur output JSON** dari `scripts/build_ownership_excel.py` tanpa menyesuaikan `useOwnershipLogic.js` dan semua komponen yang mengkonsumsi data tersebut.
2. **Jangan hapus atau rename** file Python `scripts/build_ownership_excel.py` — file ini dipanggil langsung oleh `ProcessOwnershipCsvJob`.
3. **CSS diload secara dinamis** melalui `onMounted` di `Index.vue`. Semua styling yang bersifat global untuk halaman ini harus diletakkan di `public/css/ownership-intelligence.css`, bukan di komponen Vue secara langsung.
4. **Vanilla JS masih digunakan** untuk `switchTab` dan `switchMode` (lihat `Index.vue` baris 28-39). Jangan ganti logika tersebut ke Vue reactive state tanpa refactoring penuh pada semua komponen yang bergantung padanya.
5. **Mockup (`IndexMockup.vue`)** adalah versi statis dengan data dummy. Gunakan ini untuk prototyping UI. **Jangan** hapus route mockup ini karena digunakan sebagai referensi pengembangan.
