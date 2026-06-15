<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('seeders/research_data.json'));
        $data = json_decode($json, true);
        
        foreach ($data as $item) {
            $ticker = $item['ticker'] ?? null;
            // Generate slug dari ticker, fallback ke title jika tidak ada ticker
            if ($ticker) {
                $parts = explode(':', $ticker);
                $slug  = strtolower(trim(end($parts)));
                $slug  = preg_replace('/[^a-z0-9]/', '', $slug);
            } else {
                // Buat slug dari title: lowercase, strip "PT"/"Tbk"/spasi → dash
                $slug = \Illuminate\Support\Str::slug($item['title']);
                // Bersihkan kata generik: pt-, -tbk
                $slug = preg_replace('/^pt-/', '', $slug);
                $slug = preg_replace('/-tbk$/', '', $slug);
                $slug = preg_replace('/-+/', '-', trim($slug, '-'));
            }

            $user = \App\Models\User::first();

            $updateData = [
                'title'          => $item['title'],
                'subtitle'       => $item['subtitle'],
                'ticker'         => $ticker,
                'sector'         => $item['sector'],
                'revenue'        => $item['revenue'],
                'patmi'          => $item['patmi'],
                'sales'          => $item['sales'],
                'price'          => $item['price'],
                'tags'           => $item['tags'],
                'date'           => $item['date'],
                'recommendation' => 'BUY', // Dummy
                'target_price'   => $item['price'] ?? 'Rp 10.000',
                'upside'         => '+10%',
                'report_type'    => 'Equity Research',
                'is_premium'     => (bool)rand(0, 1),
                'pdf_path'       => '/dummy.pdf', // dummy pdf
                'content'        => 'Ini adalah ringkasan tesis investasi (summary) untuk laporan riset ' . $item['title'] . '. Fundamental perusahaan solid dengan prospek pertumbuhan di masa depan.',
                'author_id'      => $user ? $user->id : null,
            ];

            if (!empty($item['image'])) {
                $updateData['image'] = $item['image'];
            }

            \App\Models\Research::updateOrCreate(
                ['slug' => $slug],
                $updateData
            );
        }
    }
}
