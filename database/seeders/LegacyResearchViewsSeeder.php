<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LegacyResearchViewsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('legacy_data/research_views.json');

        if (!File::exists($path)) {
            $this->command->warn('File research_views.json tidak ditemukan. Lewati.');
            return;
        }

        $this->command->info('Memuat research_views.json (~5.900 baris)...');
        $data = json_decode(File::get($path), true);

        // Validasi FK - gunakan flip() agar lookup O(1)
        $validUserIds     = DB::table('users')->pluck('id')->flip()->toArray();
        $validResearchIds = DB::table('research_metas')->pluck('research_id')->flip()->toArray();

        $rows    = [];
        $skipped = 0;
        $total   = count($data);

        foreach ($data as $item) {
            $userId     = $item['user_id'] ?? null;
            $researchId = $item['research_id'] ?? null; // nama di JSON

            // Validasi FK user_id → users
            if (!isset($validUserIds[$userId])) {
                $skipped++;
                continue;
            }

            // Validasi FK research_meta_id → research_metas (tidak semua research_id ada di metas)
            if (!isset($validResearchIds[$researchId])) {
                $skipped++;
                continue;
            }

            $rows[] = [
                // id di tabel adalah UUID, bukan integer — generate UUID baru
                'id'               => Str::uuid()->toString(),
                'user_id'          => $userId,
                'research_meta_id' => $researchId, // kolom di tabel = research_meta_id
                'viewed_at'        => !empty($item['viewed_at'])  ? Carbon::parse($item['viewed_at'])->toDateTimeString()  : now(),
                'created_at'       => !empty($item['created_at']) ? Carbon::parse($item['created_at'])->toDateTimeString() : now(),
                'updated_at'       => !empty($item['created_at']) ? Carbon::parse($item['created_at'])->toDateTimeString() : now(),
            ];
        }

        $count = count($rows);
        $this->command->info("Memasukkan {$count} data research_views ke database (dilewati: {$skipped})...");

        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        // Chunk 500 untuk efisiensi insert massal
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('research_views')->insert($chunk);
            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Selesai. Total: {$total} | Di-seed: {$count} | Dilewati: {$skipped}");
    }
}
