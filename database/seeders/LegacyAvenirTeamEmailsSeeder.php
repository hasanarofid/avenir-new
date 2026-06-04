<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class LegacyAvenirTeamEmailsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('legacy_data/avenir_team_emails.json');

        if (!File::exists($path)) {
            $this->command->warn('File avenir_team_emails.json tidak ditemukan. Lewati.');
            return;
        }

        $data = json_decode(File::get($path), true);
        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                'email'    => $item['email'],
                'note'     => $item['note'] ?? null,
                'added_at' => !empty($item['added_at']) ? Carbon::parse($item['added_at'])->toDateTimeString() : now(),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('avenir_team_emails')->upsert(
                $chunk,
                ['email'],
                ['note', 'added_at']
            );
        }

        $this->command->info(count($rows) . ' data avenir_team_emails berhasil di-seed dari Supabase.');
    }
}
