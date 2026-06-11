

AVENIR Research - AI Research Generator GuideConfidential working brief
## AVENIR
Panduan Prompt dan Brief
AI Avenir Research
Blueprint prompt, struktur output, quality control, dan UI brief untuk fitur AI
Research Generator berbasis dokumen unggahan.
## Versi1.0 - Draft Produk
## Tanggal11 Juni 2026
## Tujuan
Menjadikan fitur generate riset dari dokumen lebih dalam, terstruktur, dan
siap masuk workflow editorial.
Catatan utama: AI tidak boleh langsung membuat rating dan target price tanpa
dasar data yang cukup. Output harus membedakan fakta dokumen, interpretasi
analis, dan asumsi tambahan.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 2
## Ringkasan Eksekutif
Fitur AI Avenir Research sebaiknya tidak diposisikan sebagai sekadar ringkasan PDF. Produk yang benar
adalah junior analyst workflow: dokumen diunggah, fakta diekstrak, dampak dianalisis, valuasi diuji, lalu
draft riset masuk proses review sebelum dipublikasikan.
Dokumen ini berisi brief untuk programmer, prompt untuk model AI, struktur JSON output, serta standar
tampilan hasil draft agar fitur terlihat seperti research platform, bukan caption generator.
Prinsip produk
- Data first: semua angka, tanggal, pihak terkait, dan nilai transaksi harus berasal dari dokumen atau
data pendukung yang jelas.
- No unsupported TP: target price hanya boleh dibuat jika ada dasar valuasi yang memadai.
- Separate facts vs opinion: fakta dokumen, interpretasi analis, dan asumsi model harus dipisahkan.
- Editorial gate: draft AI bukan langsung publish. Harus ada review manusia atau quality check otomatis.
- UI harus mendukung pembacaan panjang dengan tab, bukan satu card panjang yang overflow di
mobile.
Daftar isi
- Arsitektur workflow AI Research Generator
- Tipe dokumen dan input data
- Struktur draft riset yang wajib muncul
- Prompt 1 - Fact extraction
- Prompt 2 - Equity research generator
- Prompt 3 - Quality control & hallucination check
- JSON output untuk frontend dan database
- UI/UX brief halaman hasil draft AI
- Guardrails rating, target price, dan disclaimer
- Acceptance criteria untuk programmer

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 3
- Arsitektur Workflow AI Research Generator
Fitur generate riset harus dibangun sebagai pipeline multi-tahap. Tujuannya adalah mengurangi
hallucination, meningkatkan kedalaman analisis, dan membuat output mudah dipakai oleh frontend.
## Upload Dokumen
-> Text extraction / OCR jika perlu
## -> Fact Extraction
## -> Impact Analysis
## -> Valuation Logic
## -> Research Draft Generation
## -> Quality Control
## -> Editorial Review
## -> Publish / Save Draft
Tahapan kerja
TahapFungsiOutput
- UploadUser mengunggah PDF, Word, annual report,
public expose, KI, prospektus, atau laporan
keuangan.
File + metadata dokumen.
- ExtractAI mengambil fakta eksplisit dari dokumen
tanpa opini.
Ticker, tanggal, nilai transaksi, pihak
terkait, angka keuangan, risiko, data gaps.
- AnalyzeAI menilai dampak bisnis, finansial, valuasi,
risiko, dan katalis.
Business impact, financial impact, scenario
analysis.
- DraftAI menyusun draft riset profesional sesuai
template Avenir.
Draft riset lengkap per section.
- QCAI mengecek klaim yang tidak didukung data
dan mengukur confidence.
Quality score, hallucination flag, missing
data.
- ReviewEditor/analis menyetujui, revisi, atau menolak
draft.
Publish-ready report.
Catatan implementasi: jangan satu endpoint langsung generate semua. Minimal pisahkan endpoint extract,
generate, dan validate agar debugging mudah dan biaya model lebih terkontrol.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 4
- Tipe Dokumen dan Input Data
AI perlu mengetahui jenis dokumen karena cara membaca keterbukaan informasi berbeda dengan
laporan keuangan atau public expose. Satu prompt generik akan menghasilkan output yang dangkal.
Jenis dokumenFokus ekstraksiOutput utama
Keterbukaan InformasiEvent, pihak terkait, nilai transaksi, jadwal,
dampak potensial.
Event overview, red flags, catalysts,
data gaps.
Laporan KeuanganRevenue, laba, aset, liabilitas, ekuitas,
margin, rasio.
Financial analysis, trend, valuation
snapshot.
Annual ReportModel bisnis, strategi, risiko, segmen, tata
kelola, kinerja historis.
Company profile, thesis, long-term
risks.
Public ExposeGuidance, strategi, proyek baru, outlook
manajemen.
Catalyst map, management tone,
scenario analysis.
## Prospektus / Rights
## Issue
Use of proceeds, dilusi, valuasi, pemegang
saham, risiko.
Dilution analysis, funding impact, red
flags.
Berita KorporasiEvent singkat, sentimen, implikasi ke saham.KI brief / quick insight.
Input tambahan yang sebaiknya disediakan ke model
- company_profile: nama emiten, sektor, industri, papan pencatatan, market cap, jumlah saham
beredar.
- market_data: harga terakhir, volume, perubahan harga, 52-week range jika tersedia.
- financials: revenue, net profit, equity, EPS, BVPS, PER, PBV, DER, ROE, ROA, NPM.
- price_history: data harga untuk menghitung upside/downside, momentum, dan risiko volatilitas.
- peer_data: comparable company untuk valuasi relatif.
- user_mode: quick brief, deep research, disclosure analysis, atau valuation report.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 5
- Struktur Draft Riset yang Wajib Dihasilkan
Output AI harus jauh lebih lengkap dari tampilan saat ini yang hanya berisi rating, target price, key risks,
conclusion, dan executive summary. Struktur minimal harus seperti equity research draft.
SectionIsi wajibCatatan produk
Research SnapshotRating, target price, current price, upside, risk level,
conviction, time horizon, confidence.
Tampil sebagai card paling atas.
## Executive
## Summary
3-5 poin inti dari dokumen dan implikasi ke emiten.Bullet pendek, high signal.
Event OverviewApa event-nya, pihak terkait, nilai transaksi, tanggal,
alasan penting.
Harus factual.
Business ImpactDampak ke model bisnis, ekspansi, competitive
position, kontrak, pelanggan.
Kualitatif jika angka belum ada.
## Financial Impact
Revenue, EBITDA, laba, aset, utang, ekuitas, cash
flow, EPS, BVPS.
Wajib tulis data gaps bila angka
tidak tersedia.
Valuation ViewMetode valuasi, asumsi, TP basis, sensitivity, peer
comparison.
Jangan buat TP tanpa dasar.
Scenario AnalysisBull case, base case, bear case.Membantu user memahami range
outcome.
Key CatalystsTender offer, akuisisi, kontrak, RUPS, dividen,
regulasi, laporan keuangan.
Urutkan berdasarkan materialitas.
Key RisksRisiko spesifik berdasarkan dokumen dan konteks
bisnis.
Hindari risiko generik.
Red FlagsAfiliasi, dilusi, utang, konflik kepentingan, disclosure
terbatas.
Wajib untuk KI dan aksi korporasi.
Data GapsData yang belum tersedia untuk kesimpulan final.Mencegah AI sok tahu.
ConclusionKesimpulan profesional dan status rekomendasi.Ringkas, tidak pompoman.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 6
## 4. Prompt 1 - Fact Extraction
Prompt ini dijalankan pertama. Tugasnya hanya mengambil fakta eksplisit dari dokumen, bukan
membuat opini atau rating.
Anda adalah analis ekstraksi dokumen untuk AVENIR Research.
Tugas Anda adalah mengekstrak fakta penting dari dokumen yang diunggah user.
Jangan membuat analisis, opini, kesimpulan investasi, rating, atau target price.
Ambil hanya informasi yang eksplisit tersedia dalam dokumen.
Jika informasi tidak tersedia, tulis: "Tidak tersedia dalam dokumen".
Output wajib:
- Identitas dokumen
- kode saham
- nama emiten
- jenis dokumen
- tanggal dokumen
- sumber dokumen
- periode laporan jika ada
- Fakta utama
- ringkasan fakta dalam 5-10 bullet
- pihak yang terlibat
- nilai transaksi jika ada
- harga transaksi / tender offer jika ada
- tanggal efektif / jadwal penting
- tujuan transaksi atau event
- Angka penting
- revenue
- laba bersih
- aset
- liabilitas
- ekuitas
## - EPS
## - BVPS
- rasio keuangan
- nilai transaksi terhadap ekuitas atau aset jika tersedia
- Risiko yang disebutkan dokumen
- risiko eksplisit dari dokumen
- risiko eksekusi
- risiko regulasi
- risiko pendanaan
- risiko transaksi afiliasi
- Data gaps
Sebutkan data yang belum tersedia atau belum cukup untuk menilai dampak keuangan.
- Evidence map
Untuk setiap fakta penting, cantumkan halaman, bagian, atau kutipan ringkas jika
tersedia.
Developer note: hasil prompt ini disimpan sebagai extraction_result. Hasil ini menjadi input untuk prompt
riset berikutnya. Jangan langsung expose ke user sebagai final research.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 7
## 5. Prompt 2 - Equity Research Generator
Prompt kedua menghasilkan draft riset berdasarkan extraction_result dan data tambahan dari database
Avenir. Prompt ini harus tegas soal keterbatasan data.
Anda adalah Equity Research Analyst untuk AVENIR Research.
Tugas Anda menyusun draft riset saham berbasis:
- hasil ekstraksi dokumen
- profil emiten
- data pasar
- data keuangan
- data pembanding jika tersedia
Gunakan gaya profesional, objektif, dan berbasis data. Jangan membuat klaim yang
tidak didukung oleh dokumen atau data tambahan. Pisahkan fakta, interpretasi,
dan asumsi.
Aturan penting:
- Jangan memberikan target price jika data valuasi tidak cukup.
- Jika target price dibuat, jelaskan metode, asumsi, dan keterbatasannya.
- Jika data tidak tersedia, tulis "data belum tersedia".
- Hindari bahasa promosi, pom-pom, atau ajakan beli/jual.
- Rating harus dapat dijelaskan oleh tesis, valuasi, dan risiko.
Format output:
- Judul riset
## 2. Metadata
- Rating & valuation snapshot
- Executive summary
- Event overview
- Business impact analysis
- Financial impact analysis
- Valuation view
- Scenario analysis: bull, base, bear
- Key catalysts
- Key risks
- Red flags
- Data gaps
- Analyst conclusion
- Source-based evidence
## 16. Disclaimer
Template instruksi rating
Gunakan rating berikut:
BUY: upside menarik dan katalis relatif kuat, dengan risiko dapat diterima.
HOLD: risk-reward seimbang atau data belum cukup kuat untuk BUY/SELL.
SELL: downside dominan, risiko fundamental tinggi, atau valuasi tidak mendukung.
NEUTRAL: informasi bersifat administratif atau dampak investasi belum jelas.
Jika dokumen hanya bersifat rutin administratif, default rating harus NEUTRAL,
kecuali ada data lain yang kuat untuk mendukung rating berbeda.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 8
## 6. Prompt 3 - Quality Control & Hallucination
## Check
Prompt ketiga memvalidasi output sebelum ditampilkan ke user. Ini wajib karena AI sering terlihat
percaya diri meskipun dasar datanya tipis.
Anda adalah editor quality control AVENIR Research.
Tugas Anda menilai draft riset AI sebelum dipublikasikan.
Periksa apakah setiap klaim penting didukung oleh dokumen atau data tambahan.
## Output:
- Quality score: 0-100
- Model confidence: Low / Medium / High
- Unsupported claims
- Overconfident statements
- Missing evidence
- Valuation weakness
- Rating justification check
- Target price check
- Required revision
- Publish recommendation: Approve / Revise / Reject
## Aturan:
- Flag semua angka yang tidak punya sumber.
- Flag target price yang tidak menjelaskan metode.
- Flag rating BUY/SELL jika hanya berdasarkan sentimen tanpa data.
- Flag kalimat yang terdengar seperti rekomendasi beli/jual langsung.
QC threshold
ScoreStatusAksi
85-100High qualityBoleh masuk editorial review.
70-84Usable with revisionTampilkan warning dan minta review analis.
50-69Weak draftJangan publish. Perlu data tambahan.
0-49RejectRegenerate atau upload dokumen tambahan.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 9
- JSON Output untuk Frontend dan Database
Agar UI rapi dan mudah dikembangkan, output jangan hanya disimpan sebagai satu teks panjang.
Simpan sebagai structured JSON agar setiap section bisa ditampilkan dalam tab yang berbeda.
## {
"ticker": "KETR",
"company_name": "PT Ketrosden Triasmitra Tbk",
"sector": "Infrastructure",
"document_type": "Keterbukaan Informasi",
## "analysis_date": "2026-06-11",
"rating": "HOLD",
"target_price": null,
"target_price_note": "Target price belum ditentukan karena data valuasi belum
memadai.",
"current_price": null,
"upside_downside": null,
"risk_level": "Medium",
"conviction_level": "Medium",
"time_horizon": "6-12 bulan",
## "model_confidence": 78,
## "quality_score": 82,
"title": "KETR: Tender Offer Berpotensi Menjadi Katalis, Dampak Fundamental Masih
## Perlu Verifikasi",
## "executive_summary": [
"Perseroan berpotensi mendapat sentimen dari rencana tender offer.",
"Harga tender offer dapat menjadi referensi valuasi jangka pendek.",
"Dampak ke laba dan arus kas belum dapat dihitung dari dokumen."
## ],
"event_overview": "Dokumen membahas potensi pengambilalihan dan tender offer.",
"business_impact": "Dampak bisnis berpotensi positif jika pengendali baru memperkuat
proyek.",
"financial_impact": "Belum dapat dihitung secara kuantitatif karena proyeksi tidak
tersedia.",
## "valuation_view": {
"method": "Tender Offer Reference",
"assumptions": ["Tender offer terlaksana sesuai rencana"],
"limitations": ["Belum tersedia fairness opinion lengkap"]
## },
## "scenario_analysis": {
"bull_case": "Tender offer dan proyek strategis berjalan lancar.",
"base_case": "Sentimen positif terbatas sampai data keuangan lanjutan tersedia.",
"bear_case": "Tender offer tertunda atau proyek tidak terealisasi."
## },
"key_catalysts": ["Pelaksanaan tender offer", "Update disclosure lanjutan"],
"key_risks": ["Ketidakpastian jadwal tender offer", "Risiko eksekusi proyek"],
"red_flags": ["Disclosure dampak keuangan masih terbatas"],
"data_gaps": ["Proyeksi laba", "Dampak EPS", "Fairness opinion"],
"analyst_conclusion": "Prospek cenderung positif secara sentimen, tetapi fundamental
masih perlu verifikasi.",
"disclaimer": "Riset ini bersifat edukatif dan informatif, bukan rekomendasi beli
atau jual saham."
## }

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 10
- UI/UX Brief Halaman Hasil Draft AI
Masalah UI pada screenshot: hasil draft ditumpuk dalam satu card vertikal sehingga teks panjang
terpotong dan tidak nyaman dibaca. Halaman hasil AI harus dibuat seperti dashboard riset dengan tab.
Struktur tampilan atas
KomponenIsi
HeaderTicker, nama emiten, jenis dokumen, tanggal analisis, model, status QC.
Snapshot cardsRating, target price, upside/downside, risk level, conviction, confidence.
Action buttonsSave draft, regenerate, export PDF, send to editorial, publish.
Warning bannerTampil jika data tidak cukup, target price null, atau QC score rendah.
Tab utama
- Overview: executive summary, event overview, analyst conclusion.
- Valuation: valuation method, target price basis, peer comparison, scenario analysis.
- Financial Impact: revenue, margin, cash flow, balance sheet impact, data gaps.
- Risks: key risks, red flags, what to watch next.
- Sources: uploaded document, evidence map, extracted facts, missing data.
- Editorial: QC score, unsupported claims, revision notes, publish status.
Mobile UX
Di mobile, snapshot cards dapat dibuat horizontal scroll. Tab harus sticky di bawah header agar user
mudah pindah dari overview ke valuation atau risks. Jangan menampilkan paragraf panjang tanpa
expand/collapse.
Mobile layout:
Header compact
Snapshot cards horizontal scroll
Sticky tabs: Overview | Valuation | Financial | Risks | Sources
Section cards with expand/collapse
Bottom action bar: Save Draft | Export | Send Review

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 11
- Guardrails Rating, Target Price, dan
## Disclaimer
Bagian ini penting secara reputasi. AI yang terlalu mudah memberi BUY dan target price membuat
platform terlihat tidak profesional. Rating dan TP harus punya standar jelas.
Target price policy
- TP boleh dibuat jika tersedia minimal satu basis valuasi: PER, PBV, EV/EBITDA, DCF, SOTP, tender offer
reference, atau peer comparison.
- TP tidak boleh dibuat hanya dari sentimen positif, berita, atau ringkasan manajemen.
- Jika memakai tender offer reference, tulis bahwa TP bersifat event-driven dan belum tentu
mencerminkan nilai fundamental penuh.
- Jika data proyeksi tidak tersedia, gunakan HOLD/NEUTRAL dan tulis 'target price belum ditentukan'.
- Semua asumsi TP wajib muncul di valuation_view.assumptions dan limitations.
Rating policy
RatingKriteria minimum
BUYUpside jelas, katalis kuat, risiko dapat diterima, dan data valuasi memadai.
HOLDRisk-reward seimbang, data belum cukup, atau katalis masih menunggu realisasi.
SELLDownside dominan, risiko fundamental tinggi, atau valuasi tidak mendukung.
NEUTRALDokumen rutin, dampak belum jelas, atau informasi belum cukup untuk tesis investasi.
Disclaimer standar
Riset ini disusun untuk tujuan edukasi dan informasi. Konten ini bukan
rekomendasi beli atau jual saham tertentu. Investor wajib melakukan analisis
mandiri dan mempertimbangkan profil risiko masing-masing. Kinerja masa lalu
tidak menjamin kinerja masa depan.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 12
- Acceptance Criteria untuk Programmer
Checklist ini dipakai untuk menilai apakah fitur AI Research Generator sudah layak masuk tahap demo
internal.
- User bisa upload dokumen dan memilih mode analisis: quick brief, deep research, valuation, atau
disclosure analysis.
- Sistem menjalankan fact extraction terlebih dahulu sebelum generate riset.
- Output tersimpan dalam format JSON terstruktur, bukan satu text blob.
- UI menampilkan snapshot, tab, dan section cards tanpa teks terpotong di mobile.
- Target price tidak muncul jika data valuasi tidak memadai.
- Semua data gaps tampil jelas di hasil draft.
- QC score dan model confidence tampil di dashboard hasil.
- User bisa regenerate per section, bukan harus regenerate seluruh draft.
- User bisa export draft ke PDF atau simpan ke dashboard editorial.
- Ada empty state, loading state, error state, dan warning jika dokumen gagal dibaca.
Recommended database tables
ai_research_jobs
- id
- user_id
- ticker
- document_url
- document_type
- status
- model_used
- created_at
- updated_at
ai_extractions
- job_id
- extraction_json
- evidence_map_json
- data_gaps_json
ai_research_drafts
- job_id
- draft_json
- rating
- target_price
- quality_score
- confidence_level
- editorial_status
ai_qc_reports
- job_id
- unsupported_claims
- revision_notes
- publish_recommendation
Roadmap singkat
PhaseFitur
MVPUpload dokumen, extract facts, generate deep draft, tampilkan tab overview/risks/sources.
Phase 2Valuation engine sederhana, QC report, export PDF, editorial dashboard.
Phase 3Auto-connect ke emiten hub, disclosure radar, katalog riset, dan contributor workflow.

AVENIRPanduan Prompt dan Brief AI Research
Avenir Research - Draft untuk pengembangan produkPage 13
PhaseFitur
Phase 4Track record contributor, comment, community research, dan revenue share analytics.
Kesimpulan: fitur AI Avenir harus diposisikan sebagai research workflow, bukan text summarizer. Nilai
produk muncul dari struktur data, guardrails, evidence map, dan editorial layer.