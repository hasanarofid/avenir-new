<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Disclosure;
use App\Models\KIBrief;
use App\Models\Ticker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DmasSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure DMAS ticker exists
        $dmas = Ticker::updateOrCreate(
            ['symbol' => 'DMAS'],
            [
                'company_name' => 'Puradelta Lestari Tbk',
                'sector' => 'Property & Real Estate',
                'description' => 'Pengembang kawasan industri Kota Deltamas, Cikarang dengan luas lahan lebih dari 3.000 hektar.',
                'current_price' => 160.00,
                'target_price' => 200.00,
                'recommendation' => 'bullish',
            ]
        );

        // --- Articles (Berita & Riset Terkait) ---
        $articles = [
            [
                'title' => 'DMAS: Q1-2026: Revenue Naik 25%, Pre-sales Kota Deltamas Tembus Rp 1,2 Triliun',
                'slug' => 'dmas-q1-2026-pre-sales-1-2-triliun',
                'category' => 'Equity · Property · Earnings',
                'badge' => 'Q1-2026 · DMAS',
                'excerpt' => 'Puradelta Lestari (DMAS) merilis laporan keuangan Q1-2026 dengan pencapaian pre-sales Kota Deltamas mencapai Rp 1,2 triliun, naik 25% YoY, didorong oleh permintaan lahan industri dari tenant manufaktur dan logistik.',
                'cover_image' => '/images/articles/paradoks-pdb-rupiah-2026.svg',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-05-20',
                'is_paid' => false,
                'status' => 'published',
                'position_disclosure' => 'Penulis dan/atau afiliasi tidak memiliki posisi saham DMAS pada saat tulisan ini diterbitkan.',
            ],
            [
                'title' => 'Kota Deltamas: Kawasan Industri Premium dengan Tenant Global',
                'slug' => 'kota-deltamas-kawasan-industri-premium',
                'category' => 'Equity · Property · Industrial Estate',
                'badge' => 'Industrial · DMAS',
                'excerpt' => 'Kota Deltamas yang dikembangkan oleh DMAS menjadi pilihan utama tenant global seperti Toyota, Samsung, dan DHL karena infrastruktur yang lengkap dan lokasi strategis di Cikarang.',
                'cover_image' => '/images/articles/photonics-cpo-2026.svg',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-05-15',
                'is_paid' => true,
                'status' => 'published',
                'position_disclosure' => 'Penulis dan/atau afiliasi memiliki posisi panjang saham DMAS.',
            ],
        ];

        foreach ($articles as $articleData) {
            $article = Article::updateOrCreate(
                ['slug' => $articleData['slug']],
                $articleData
            );
            // Attach article to DMAS ticker
            $dmas->articles()->syncWithoutDetaching([$article->id]);
        }

        // --- Disclosures & KI Briefs ---
        $disclosures = [
            [
                'title' => 'Pengumuman Pencapaian Pre-sales Q1-2026',
                'category' => 'Pengumuman Keuangan',
                'date' => '2026-05-10',
                'source_url' => 'https://www.idx.co.id',
                'raw_text' => 'PT Puradelta Lestari Tbk (DMAS) hari ini mengumumkan pencapaian pre-sales triwulan pertama tahun 2026 sebesar Rp 1,2 triliun, mengalami peningkatan 25% dibandingkan periode yang sama tahun sebelumnya.',
                'ki_brief' => [
                    'summary' => 'DMAS berhasil mencatatkan pre-sales Q1-2026 sebesar Rp 1,2 triliun (+25% YoY), didorong oleh kuatnya permintaan lahan industri di Kota Deltamas, terutama dari sektor manufaktur dan logistik.',
                    'key_numbers' => 'Pre-sales: Rp 1.2T (+25% YoY), Penjualan lahan: 50 hektar, Average selling price: Rp 24 juta/hektar',
                    'impact' => 'Pencapaian ini mengkonfirmasi guidance manajemen tahun 2026 dan memperkuat posisi DMAS sebagai pemain utama kawasan industri di Jabodetabek.',
                    'risks' => 'Risiko utama adalah keterlambatan pengembangan infrastruktur dan penurunan permintaan akibat kondisi makro ekonomi.',
                ],
            ],
            [
                'title' => 'Pengumuman Penandatanganan Kontrak dengan Tenant Baru',
                'category' => 'Pengumuman Korporasi',
                'date' => '2026-04-28',
                'source_url' => 'https://www.idx.co.id',
                'raw_text' => 'PT Puradelta Lestari Tbk dengan bangga mengumumkan penandatanganan kontrak jual beli lahan dengan sebuah perusahaan logistik multinasional untuk lahan seluas 15 hektar di kawasan Kota Deltamas.',
                'ki_brief' => [
                    'summary' => 'DMAS menandatangani kontrak penjualan lahan 15 hektar dengan tenant logistik global, menambah pipeline pre-sales untuk tahun 2026.',
                    'key_numbers' => 'Luas lahan: 15 hektar, Nilai kontrak: Rp 360 miliar, Jangka waktu pembayaran: 12 bulan',
                    'impact' => 'Kontrak ini memberikan visibilitas pendapatan yang jelas untuk tahun 2026 dan 2027.',
                    'risks' => 'Risiko keterlambatan pembayaran dan keterlambatan serah terima lahan.',
                ],
            ],
        ];

        foreach ($disclosures as $disclosureData) {
            $kiBriefData = $disclosureData['ki_brief'];
            unset($disclosureData['ki_brief']);
            $disclosureData['ticker_id'] = $dmas->id;

            $disclosure = Disclosure::updateOrCreate(
                ['title' => $disclosureData['title'], 'date' => $disclosureData['date']],
                $disclosureData
            );

            KIBrief::updateOrCreate(
                ['disclosure_id' => $disclosure->id],
                $kiBriefData
            );
        }
    }
}
