<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Paradoks PDB 5,61% dan Pelemahan Rupiah: Kualitas Pertumbuhan, DSR/Revenue, dan Repricing Risiko Fiskal',
                'slug' => 'paradoks-pdb-rupiah-2026',
                'category' => 'Macro & Markets · Fiscal Policy · PDB · Rupiah · DSR Revenue',
                'badge' => 'Macro · PDB · Fiscal',
                'excerpt' => 'PDB Indonesia Q1-2026 tumbuh 5,61% YoY — tertinggi sejak Q3-2022, tetapi rupiah tetap melemah ke rekor Rp17.668/USD. Avenir Research membedah paradoks ini: dorongan belanja pemerintah +21,8%, oil shock importir energi, MSCI deletion 18 saham, dan kerangka DSR/Revenue dari M. Chatib Basri yang menyoroti risiko fiskal.',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-05-18',
                'is_paid' => false,
            ],
            [
                'title' => 'Pelemahan Rupiah 2026: Oil Shock, Dollar Premium, dan Diskon Kredibilitas Kebijakan',
                'slug' => 'pelemahan-rupiah-2026',
                'category' => 'Macro & Markets · FX Strategy · Rupiah · Bank Indonesia · Fiscal Credibility',
                'badge' => 'Macro · FX · Rupiah',
                'excerpt' => 'Selasa 12 Mei 2026, rupiah menembus Rp17.512/USD — rekor terlemah sepanjang sejarah. Cadangan devisa turun ke US$146,2 miliar (terendah sejak Juli 2024). Fitch outlook negatif, Moody\'s outlook negatif, MSCI mendepak 18 saham Indonesia. PMI manufaktur April turun ke 49,1 — kontraksi pertama setelah 8 bulan ekspansi.',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-05-18',
                'is_paid' => true,
            ],
            [
                'title' => 'Sejarah Reli Sandisk: Dari Spin-off Western Digital ke AI Storage Proxy',
                'slug' => 'sandisk-ai-storage-2026',
                'category' => 'Equity Strategy · AI Storage · NAND · Memory Supercycle · Spin-off',
                'badge' => 'Sandisk · AI Storage · NAND',
                'excerpt' => 'Sandisk (SNDK) naik >1.500% dalam satu tahun — bukan bubble kosong, tetapi triple rerating langka: business model, earnings, dan multiple. Spin-off Feb 2025 dari Western Digital di $36, akhir 2025 ke $237,38 (+559%). Q3 FY2026: Revenue $5,95B vs konsensus $4,70B, EPS $23,41 vs $14,54.',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-05-18',
                'is_paid' => true,
            ],
            [
                'title' => 'Setelah Photonics & CPO: AI Power Infrastructure sebagai Bottleneck Investasi Berikutnya',
                'slug' => 'ai-power-infrastructure-2026',
                'category' => 'AI Infrastructure · Power · Data Center · Grid Equipment · Nuclear Renaissance',
                'badge' => 'AI · Power · Data Center',
                'excerpt' => 'Pasar bertanya: setelah optical components dan silicon photonics, kemana modal AI mengalir? Jawabannya semakin jelas — sumber listrik, jaringan tegangan tinggi, transformator, sistem pendingin, dan reaktor nuklir. Eaton melaporkan 228 GW backlog data center (~12 tahun supply), Vertiv backlog $15B (+80,8% YoY) co-develop NVIDIA Rubin.',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-05-10',
                'is_paid' => true,
            ],
            [
                'title' => 'Mengapa Saham Bernama Coherent Naik 367% dalam 12 Bulan: Bedah Tema Photonics dan Pertanyaan yang Belum Banyak Ditanya',
                'slug' => 'photonics-cpo-2026',
                'category' => 'AI Infrastructure · Photonics · Co-Packaged Optics · Silicon Photonics · Optical Networking',
                'badge' => 'Photonics · CPO · AI',
                'excerpt' => 'NVIDIA gelontorkan USD 4 miliar ke Coherent & Lumentum Maret 2026; Marvell akuisisi Celestial AI USD 3,25 miliar; saham AAOI naik +441% YTD, LITE +166%, COHR +97%, lalu masuk S&P 500. 14 bab bedah lengkap: bottleneck AI interconnect, evolusi pluggable → CPO → optical I/O.',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-05-05',
                'is_paid' => true,
            ],
            [
                'title' => 'ETF Bernama DRAM yang Mengalahkan Bitcoin: Memory Supercycle, AI Bottleneck, dan Bahaya Crowded Trade',
                'slug' => 'etf-dram-2026',
                'category' => 'Market Structure · ETF Analysis · HBM · Memory · AI Infrastructure',
                'badge' => 'ETF · DRAM · Memory',
                'excerpt' => 'Roundhill Memory ETF (DRAM) mengumpulkan USD 6,5 miliar dalam 36 hari — memecahkan rekor BlackRock Bitcoin ETF. Saham +100% sejak launch April 2026. 14 bab mengulas HBM supercycle, SK Hynix 72% OP margin (melampaui NVIDIA), Goldman shortage terparah 15 tahun.',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-04-28',
                'is_paid' => false,
            ],
            [
                'title' => 'AI Bubble Risk dan Kapital Cycle: Bedah Probabilitas Repricing 2026-2028',
                'slug' => 'ai-bubble-risk-2026',
                'category' => 'Thematic Paper · AI · Capex Cycle · Semiconductor · Power',
                'badge' => 'AI · Bubble · Risk',
                'excerpt' => 'Hyperscaler global berkomitmen belanja USD 650–725 miliar di 2026 untuk infrastruktur AI — mendekati 0,8% PDB AS. NVIDIA market cap USD 4,83 triliun. Tetapi Jeff Bezos, David Solomon (Goldman), Sam Altman, Jamie Dimon, dan Ray Dalio sudah mengirim sinyal hati-hati.',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-04-20',
                'is_paid' => false,
            ],
            [
                'title' => 'Industri Ethanol Indonesia 2026: Anatomi Proses, Pemain Utama (MOLI, SRSN), dan "Big Bet" E5/E10 Mandate',
                'slug' => 'industri-ethanol-indonesia',
                'category' => 'Sector Deep Dive · Bioethanol · Energy Transition',
                'badge' => 'Ethanol · MOLI · SRSN',
                'excerpt' => 'Bedah komprehensif industri ethanol Indonesia menjelang mandat E5 2026. 16 bab mencakup proses produksi 3D (molasses → fermentasi → distillasi → molecular sieve), profil 13 produsen domestik dengan deep dive MOLI (kapasitas 80.000 KL terbesar) dan SRSN (Indo Acidatama Karanganyar).',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-04-10',
                'is_paid' => false,
            ],
            [
                'title' => 'Private Credit Bubble Risk: Shadow Banking, Stale Valuation, dan Risiko Repricing di Balik "Stable Return"',
                'slug' => 'private-credit-bubble-risk',
                'category' => 'Financial Stability · Private Credit · Global Markets',
                'badge' => 'Private Credit · Risk',
                'excerpt' => 'Bedah komprehensif risiko pasar private credit global US$1,5–3,5 triliun. 18 bab mencakup anatomi 5 mesin risiko (floating-rate, PIK income 8% BDC, stale valuation, bank/insurer interconnection, retailization), maturity wall 2026–2027.',
                'author' => 'Tim Avenir Research',
                'published_at' => '2026-04-02',
                'is_paid' => true,
            ],
        ];

        foreach ($articles as $article) {
            Article::updateOrCreate(
                ['slug' => $article['slug']],
                $article
            );
        }
    }
}
