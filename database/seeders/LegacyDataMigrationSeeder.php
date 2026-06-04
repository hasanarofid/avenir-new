<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LegacyDataMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLegacyUsers();
    }

    private function seedLegacyUsers(): void
    {
        $jsonPath = database_path('legacy_data/profiles.json');
        $emailsPath = database_path('legacy_data/trial_email_history.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->warn("File data profil lama tidak ditemukan. Lewati.");
            return;
        }

        $profiles = json_decode(File::get($jsonPath), true);
        
        // Map emails
        $emailMap = [];
        if (File::exists($emailsPath)) {
            $emailHistory = json_decode(File::get($emailsPath), true);
            foreach ($emailHistory as $eh) {
                if (isset($eh['current_user_id']) && isset($eh['email_lower'])) {
                    $emailMap[$eh['current_user_id']] = $eh['email_lower'];
                }
            }
        }
        
        $usersData = [];
        $usedEmails = [];

        foreach ($profiles as $profile) {
            $id = $profile['id'];
            // Fallback to a dummy email if not found in trial_email_history to satisfy unique constraint
            $email = $emailMap[$id] ?? "legacy_user_{$id}@researchavenir.com";

            // Prevent Unique Constraint Violation for duplicate emails in trial history
            if (in_array($email, $usedEmails)) {
                $email = "duplicate_" . uniqid() . "_" . $email;
            }
            $usedEmails[] = $email;

            $createdAt = isset($profile['created_at']) ? \Illuminate\Support\Carbon::parse($profile['created_at'])->toDateTimeString() : now();
            $updatedAt = isset($profile['updated_at']) ? \Illuminate\Support\Carbon::parse($profile['updated_at'])->toDateTimeString() : now();

            $usersData[] = [
                'id'          => $id,
                'name'        => trim(($profile['nama_depan'] ?? '') . ' ' . ($profile['nama_belakang'] ?? '')) ?: 'User Migrasi',
                'email'       => $email,
                'password'    => null,
                'is_migrated' => true,
                'created_at'  => $createdAt,
                'updated_at'  => $updatedAt,
            ];
        }

        foreach (array_chunk($usersData, 100) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $this->command->info(count($usersData) . ' Data profil legacy sukses tertanam ke tabel Users.');
    }
}
