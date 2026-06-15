<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\News;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $newsItems = [
            [
                'title' => 'IHSG Menguat ke 7.275, Investor Asing Net Buy Rp1,02 Triliun',
                'category' => 'Market',
                'source' => 'AVENIR Research',
                'sentiment' => 'Positif',
                'excerpt' => 'Penguatan didorong oleh sektor perbankan dan komoditas. Sentimen positif global menopang aliran dana asing ke pasar domestik.',
                'cover_image' => 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?auto=format&fit=crop&q=80',
                'published_at' => now()->subMinutes(9),
                'tickers' => ['IHSG', 'BBRI', 'TLKM', 'ASII']
            ],
            [
                'title' => 'BI Tahan Suku Bunga di 6,25%, Rupiah Menguat ke 16.255',
                'category' => 'Macro',
                'source' => 'Bloomberg Technoz',
                'sentiment' => 'Positif',
                'excerpt' => 'Keputusan sejalan dengan ekspektasi pasar. BI tetap waspada terhadap inflasi dan risiko eksternal.',
                'cover_image' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80',
                'published_at' => now()->subMinutes(25),
                'tickers' => ['BI', 'USDIDR', 'Inflasi', 'Suku Bunga']
            ],
            [
                'title' => 'Harga Minyak Turun di Tengah Data Inventori AS yang Meningkat',
                'category' => 'Commodity',
                'source' => 'Reuters',
                'sentiment' => 'Negatif',
                'excerpt' => 'Stok minyak mentah AS naik 3,6 juta barel, melebihi ekspektasi analis.',
                'cover_image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&q=80',
                'published_at' => now()->subMinutes(45),
                'tickers' => ['Oil', 'Brent', 'WTI', 'Energy']
            ]
        ];

        foreach ($newsItems as $item) {
            $tickers = $item['tickers'];
            unset($item['tickers']);
            $item['slug'] = Str::slug($item['title']);
            
            $news = News::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
            
            // Note: If you want to attach tickers, ensure you have the logic to find or create Tickers first.
            // For simplicity in seeding, we might skip the many-to-many relationship in this seeder 
            // unless we also seed the Tickers first. The tickers table is already seeded by DummyDataSeeder.
        }
    }
}
