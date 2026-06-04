<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class LegacyCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('legacy_data/comments.json');

        if (!File::exists($path)) {
            $this->command->warn('File comments.json tidak ditemukan. Lewati.');
            return;
        }

        $data = json_decode(File::get($path), true);

        // Ambil research_id yang valid di research_metas
        $validResearchIds = DB::table('research_metas')->pluck('research_id')->toArray();
        // Ambil user_id yang valid di users
        $validUserIds = DB::table('users')->pluck('id')->toArray();

        $rows    = [];
        $skipped = 0;

        foreach ($data as $item) {
            $researchId = $item['research_id'] ?? null;
            $userId     = $item['user_id'] ?? null;

            // Skip jika FK tidak ada
            if (!in_array($researchId, $validResearchIds)) {
                $this->command->warn("  [SKIP] Comment {$item['id']}: research_id '{$researchId}' tidak ada di research_metas.");
                $skipped++;
                continue;
            }

            if (!in_array($userId, $validUserIds)) {
                $this->command->warn("  [SKIP] Comment {$item['id']}: user_id '{$userId}' tidak ada di users.");
                $skipped++;
                continue;
            }

            $rows[] = [
                'id'               => $item['id'],
                'research_meta_id' => $researchId,
                'user_id'          => $userId,
                'parent_id'        => !empty($item['parent_id']) ? $item['parent_id'] : null,
                'body'             => $item['body'] ?? null,
                'like_count'       => (int) ($item['like_count'] ?? 0),
                'is_deleted'       => filter_var($item['is_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'created_at'       => !empty($item['created_at']) ? Carbon::parse($item['created_at'])->toDateTimeString() : now(),
                'updated_at'       => !empty($item['updated_at']) ? Carbon::parse($item['updated_at'])->toDateTimeString() : now(),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('comments')->upsert(
                $chunk,
                ['id'],
                ['body', 'like_count', 'is_deleted', 'updated_at']
            );
        }

        $this->command->info(count($rows) . ' data comments berhasil di-seed dari Supabase. (Dilewati: ' . $skipped . ')');
    }
}
