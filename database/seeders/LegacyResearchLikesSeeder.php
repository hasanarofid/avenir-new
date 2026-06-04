<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class LegacyResearchLikesSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('legacy_data/research_likes.json');

        if (!File::exists($path)) {
            $this->command->warn('File research_likes.json tidak ditemukan. Lewati.');
            return;
        }

        $data = json_decode(File::get($path), true);

        // Ambil ID valid - kolom primary di research_metas adalah 'research_id' (string)
        $validResearchIds = DB::table('research_metas')->pluck('research_id')->flip()->toArray();
        $validUserIds     = DB::table('users')->pluck('id')->flip()->toArray();

        $rows      = [];
        $skipped   = 0;
        $seenPairs = []; // cegah duplikat unique(user_id, research_meta_id)

        foreach ($data as $item) {
            $researchId = $item['research_id'] ?? null; // kolom di JSON
            $userId     = $item['user_id'] ?? null;

            // Validasi FK user_id → users
            if (!isset($validUserIds[$userId])) {
                $skipped++;
                continue;
            }

            // Validasi FK research_meta_id → research_metas
            if (!isset($validResearchIds[$researchId])) {
                $skipped++;
                continue;
            }

            // Cegah duplikat unique constraint (user_id, research_meta_id)
            $pairKey = $userId . '|' . $researchId;
            if (isset($seenPairs[$pairKey])) {
                $skipped++;
                continue;
            }
            $seenPairs[$pairKey] = true;

            $rows[] = [
                'id'               => $item['id'],   // UUID dari Supabase
                'user_id'          => $userId,
                'research_meta_id' => $researchId,   // nama kolom di tabel = research_meta_id
                'created_at'       => !empty($item['created_at']) ? Carbon::parse($item['created_at'])->toDateTimeString() : now(),
                'updated_at'       => !empty($item['created_at']) ? Carbon::parse($item['created_at'])->toDateTimeString() : now(),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('research_likes')->upsert(
                $chunk,
                ['id'],
                ['research_meta_id', 'user_id']
            );
        }

        $this->command->info(count($rows) . ' data research_likes berhasil di-seed dari Supabase. (Dilewati: ' . $skipped . ')');
    }
}
