<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticker;
use App\Models\Article;
use App\Models\Disclosure;
use Illuminate\Support\Str;

class BBRITickerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyProfile = [
            'industry' => 'Banks',
            'board' => 'Utama',
            'listingDate' => '10 November 2003',
            'website' => 'bri.co.id',
            'business' => 'Perbankan yang mencakup kegiatan usaha secara konvensional dan/atau berdasarkan prinsip syariah.',
            'marketCap' => 'Rp 672,45 T',
            'outstandingShares' => '149,05 Miliar',
            'address' => 'Jl. Jenderal Sudirman Kav. 44-46 Jakarta 10210, Indonesia',
            'phone' => '(021) 575 8965',
            'email' => 'corporate_secretary@bri.co.id',
            'tags' => ['SOE', 'Banking', 'UMKM', 'Bluechip']
        ];

        $financialHighlights = [
            [ 'title' => 'Net Interest Income', 'value' => 'Rp 112,7 T', 'change' => '+8,45%', 'type' => 'up', 'icon' => 'Wallet' ],
            [ 'title' => 'Net Profit', 'value' => 'Rp 15,0 T', 'change' => '+10,32%', 'type' => 'up', 'icon' => 'TrendingUp' ],
            [ 'title' => 'Total Assets', 'value' => 'Rp 1.915 T', 'change' => '+8,12%', 'type' => 'up', 'icon' => 'Building2' ],
            [ 'title' => 'Loan', 'value' => 'Rp 1.345 T', 'change' => '+9,27%', 'type' => 'up', 'icon' => 'CreditCard' ],
            [ 'title' => 'Deposit', 'value' => 'Rp 1.417 T', 'change' => '+2,88%', 'type' => 'up', 'icon' => 'PiggyBank' ],
            [ 'title' => 'NIM', 'value' => '7,61%', 'change' => '-0,15%', 'type' => 'down', 'icon' => 'Percent' ],
            [ 'title' => 'NPL Gross', 'value' => '2,87%', 'change' => '-0,21%', 'type' => 'down', 'icon' => 'AlertTriangle' ],
            [ 'title' => 'ROE', 'value' => '19,82%', 'change' => '+1,22%', 'type' => 'up', 'icon' => 'Activity' ],
            [ 'title' => 'CAR', 'value' => '27,45%', 'change' => '+1,33%', 'type' => 'up', 'icon' => 'Shield' ],
        ];

        $financialRatios = [
            [ 'name' => 'PER', 'value' => '12,45x', 'period' => '2025', 'change' => '+0,23%' ],
            [ 'name' => 'PBV', 'value' => '2,08x', 'period' => '2025', 'change' => '+5,12%' ],
            [ 'name' => 'ROA', 'value' => '2,89%', 'period' => '2025', 'change' => '+0,18%' ],
            [ 'name' => 'ROE', 'value' => '19,82%', 'period' => '2025', 'change' => '+1,22%' ],
            [ 'name' => 'NIM', 'value' => '7,61%', 'period' => '2025', 'change' => '-0,15%' ],
            [ 'name' => 'NPL Gross', 'value' => '2,87%', 'period' => '2025', 'change' => '-0,21%' ],
            [ 'name' => 'LCR', 'value' => '88,21%', 'period' => '2025', 'change' => '+1,45%' ],
            [ 'name' => 'CAR', 'value' => '27,45%', 'period' => '2025', 'change' => '+1,33%' ],
        ];

        $mainRisks = [
            'Kenaikan NPL dan penurunan kualitas kredit',
            'Tekanan margin bunga (NIM)',
            'Perlambatan kredit UMKM',
            'Perubahan suku bunga',
            'Risiko regulasi perbankan'
        ];

        $ticker = Ticker::updateOrCreate(
            ['symbol' => 'BBRI'],
            [
                'company_name' => 'Bank Rakyat Indonesia (Persero) Tbk.',
                'sector' => 'Financials',
                'description' => 'BBRI merupakan bank BUMN terbesar di Indonesia dengan fokus utama pada segmen mikro dan UMKM. Kekuatan utama perseroan berasal dari jaringan luas, basis nasabah besar, dan profitabilitas yang stabil.',
                'current_price' => 4500,
                'target_price' => 6000,
                'recommendation' => 'BUY',
                'company_profile' => $companyProfile,
                'financial_highlights' => $financialHighlights,
                'financial_ratios' => $financialRatios,
                'main_risks' => $mainRisks,
            ]
        );

        // Seed some news if not exists
        if ($ticker->articles()->count() == 0) {
            $article1 = Article::create([
                'title' => 'BBRI: Mikro Masih Jadi Mesin Laba',
                'slug' => Str::slug('BBRI: Mikro Masih Jadi Mesin Laba'),
                'content' => 'Lorem ipsum',
                'excerpt' => 'Bank Rakyat Indonesia tetap optimis segmen mikro akan terus menjadi penggerak utama pertumbuhan laba perseroan pada 2026.',
                'cover_image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&q=80&w=200&h=200',
                'status' => 'published',
                'badge' => 'Riset',
                'published_at' => '2026-06-11 10:00:00'
            ]);

            $article2 = Article::create([
                'title' => 'BBRI Catat Pertumbuhan Kredit 9,2% di Q1 2026',
                'slug' => Str::slug('BBRI Catat Pertumbuhan Kredit 9,2% di Q1 2026'),
                'content' => 'Lorem ipsum',
                'excerpt' => 'Pertumbuhan kredit tercatat kuat dan kualitas aset tetap terjaga di tengah fluktuasi ekonomi global.',
                'cover_image' => 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e?auto=format&fit=crop&q=80&w=200&h=200',
                'status' => 'published',
                'badge' => 'Berita',
                'published_at' => '2026-06-10 14:00:00'
            ]);
            
            $ticker->articles()->attach([$article1->id, $article2->id]);
        }

        // Seed some disclosures if not exists
        if ($ticker->disclosures()->count() == 0) {
            Disclosure::create([
                'ticker_id' => $ticker->id,
                'title' => 'Laporan Bulanan Registrasi Pemegang Efek',
                'category' => 'Laporan Bulanan',
                'date' => '2026-06-11',
                'source_url' => 'https://idx.co.id'
            ]);

            Disclosure::create([
                'ticker_id' => $ticker->id,
                'title' => 'Perubahan Pengurus Perseroan',
                'category' => 'Perubahan Pengurus',
                'date' => '2026-06-10',
                'source_url' => 'https://idx.co.id'
            ]);

            Disclosure::create([
                'ticker_id' => $ticker->id,
                'title' => 'Risalah RUPS Tahunan',
                'category' => 'RUPS',
                'date' => '2026-06-02',
                'source_url' => 'https://idx.co.id'
            ]);
        }
    }
}
