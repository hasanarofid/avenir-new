<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class LegacyNotificationsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('legacy_data/notifications.json');

        if (!File::exists($path)) {
            $this->command->warn('File notifications.json tidak ditemukan. Lewati.');
            return;
        }

        $data = json_decode(File::get($path), true);
        $rows = [];

        foreach ($data as $item) {
            $rows[] = [
                'id'                => (int) $item['id'],
                'title'             => $item['title'] ?? null,
                'url'               => $item['url'] ?? null,
                'category'          => $item['category'] ?? null,
                'is_new'            => filter_var($item['is_new'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'published_at'      => !empty($item['published_at'])  ? Carbon::parse($item['published_at'])->toDateTimeString()  : null,
                'email_sent_at'     => !empty($item['email_sent_at']) ? Carbon::parse($item['email_sent_at'])->toDateTimeString() : null,
                'email_sent_count'  => (int) ($item['email_sent_count'] ?? 0),
                'created_at'        => !empty($item['created_at']) ? Carbon::parse($item['created_at'])->toDateTimeString() : now(),
                'updated_at'        => !empty($item['updated_at']) ? Carbon::parse($item['updated_at'])->toDateTimeString() : now(),
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('notifications')->upsert(
                $chunk,
                ['id'],
                ['title', 'url', 'category', 'is_new', 'email_sent_at', 'email_sent_count', 'updated_at']
            );
        }

        $this->command->info(count($rows) . ' data notifications berhasil di-seed dari Supabase.');
    }
}
