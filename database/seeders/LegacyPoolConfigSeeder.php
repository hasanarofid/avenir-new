<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class LegacyPoolConfigSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('legacy_data/pool_config.json');

        if (!File::exists($path)) {
            $this->command->warn('File pool_config.json tidak ditemukan. Lewati.');
            return;
        }

        $data = json_decode(File::get($path), true);
        $rows = [];

        foreach ($data as $item) {
            // Ambil updated_by jika valid UUID dan ada di tabel users
            $updatedBy = null;
            if (!empty($item['updated_by'])) {
                $exists = DB::table('users')->where('id', $item['updated_by'])->exists();
                if ($exists) {
                    $updatedBy = $item['updated_by'];
                }
            }

            $rows[] = [
                'period_year'      => (int) ($item['period_year'] ?? date('Y')),
                'period_month'     => (int) ($item['period_month'] ?? date('n')),
                'pool_budget_idr'  => (float) ($item['pool_budget_idr'] ?? 0),
                'notes'            => $item['notes'] ?? null,
                'updated_by'       => $updatedBy,
                'updated_at'       => !empty($item['updated_at']) ? Carbon::parse($item['updated_at'])->toDateTimeString() : now(),
            ];
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('pool_config')->upsert(
                $chunk,
                ['period_year', 'period_month'],
                ['pool_budget_idr', 'notes', 'updated_by', 'updated_at']
            );
        }

        $this->command->info(count($rows) . ' data pool_config berhasil di-seed dari Supabase.');
    }
}
