<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class LegacyPaymentSubmissionsSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('legacy_data/payment_submissions.json');

        if (!File::exists($path)) {
            $this->command->warn('File payment_submissions.json tidak ditemukan. Lewati.');
            return;
        }

        $data = json_decode(File::get($path), true);

        $validUserIds = DB::table('users')->pluck('id')->flip()->toArray();

        $rows    = [];
        $skipped = 0;

        foreach ($data as $item) {
            $userId = $item['user_id'] ?? null;

            if (!isset($validUserIds[$userId])) {
                $this->command->warn("  [SKIP] Payment #{$item['id']}: user_id '{$userId}' tidak ada di users.");
                $skipped++;
                continue;
            }

            // verified_by juga harus ada di users (atau null)
            $verifiedBy = !empty($item['verified_by']) && isset($validUserIds[$item['verified_by']])
                ? $item['verified_by']
                : null;

            $rows[] = [
                // Supabase menyimpan id sebagai integer string, kita generate UUID baru
                // agar sesuai dengan kolom uuid primary key di migrasi
                'id'           => \Illuminate\Support\Str::uuid()->toString(),
                'user_id'      => $userId,
                'paket'        => $item['paket'] ?? null,
                'durasi_hari'  => (int) ($item['durasi_hari'] ?? 0),
                'nominal'      => (int) ($item['nominal'] ?? 0),
                'bukti_url'    => !empty($item['bukti_url'])   ? $item['bukti_url']   : null,
                'bukti_path'   => !empty($item['bukti_path'])  ? $item['bukti_path']  : null,
                'status'       => $item['status'] ?? 'pending',
                'submitted_at' => !empty($item['submitted_at']) ? Carbon::parse($item['submitted_at'])->toDateTimeString() : null,
                'verified_at'  => !empty($item['verified_at'])  ? Carbon::parse($item['verified_at'])->toDateTimeString()  : null,
                'verified_by'  => $verifiedBy,
                'admin_notes'  => !empty($item['admin_notes'])  ? $item['admin_notes']  : null,
                'user_email'   => $item['user_email'] ?? null,
                'user_nama'    => $item['user_nama']  ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (!empty($rows)) {
            DB::table('payment_submissions')->insert($rows);
        }

        $this->command->info(count($rows) . ' data payment_submissions berhasil di-seed dari Supabase. (Dilewati: ' . $skipped . ')');
    }
}
