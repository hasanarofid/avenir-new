<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class LegacyResearchMetaSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('legacy_data/research_meta.json');

        if (!File::exists($path)) {
            $this->command->warn('File research_meta.json tidak ditemukan. Lewati.');
            return;
        }

        $data = json_decode(File::get($path), true);
        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                'research_id'        => $item['research_id'],
                'mitra_id'           => !empty($item['mitra_id']) ? $item['mitra_id'] : null,
                'title'              => $item['title'] ?? null,
                'description'        => $item['description'] ?? null,
                'ticker'             => $item['ticker'] ?? null,
                'sector'             => !empty($item['sector']) ? $item['sector'] : null,
                'type'               => $item['type'] ?? null,
                'published_at'       => !empty($item['published_at']) ? Carbon::parse($item['published_at'])->toDateString() : null,
                'author_type'        => $item['author_type'] ?? null,
                'author_id'          => !empty($item['author_id']) ? $item['author_id'] : null,
                'author_display_name' => $item['author_display_name'] ?? null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        // Gunakan upsert agar aman dijalankan ulang
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('research_metas')->upsert(
                $chunk,
                ['research_id'],
                ['title', 'ticker', 'sector', 'type', 'published_at', 'author_type', 'author_display_name', 'updated_at']
            );
        }

        $this->command->info(count($rows) . ' data research_metas berhasil di-seed dari Supabase.');
    }
}
