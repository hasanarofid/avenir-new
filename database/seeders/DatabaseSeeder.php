<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // === Sistem & Konfigurasi Dasar ===
            RoleAndPermissionSeeder::class,
            SettingSeeder::class,
            PageAndSectionSeeder::class,

            // === Konten Statis (tidak bergantung pada users) ===
            PropertySeeder::class,
            ResearchSeeder::class,
            ArticleSeeder::class,

            // === Migrasi Data Legacy Supabase (researchavenir.com) ===
            // 1. Users harus pertama karena semua tabel legacy FK ke users
            LegacyDataMigrationSeeder::class,

            // 2. Research metas harus sebelum comments/likes/views
            LegacyResearchMetaSeeder::class,

            // 3. Data turunan (FK ke users + research_metas)
            LegacyCommentsSeeder::class,
            LegacyResearchLikesSeeder::class,
            LegacyResearchViewsSeeder::class,

            // 4. Data independen
            LegacyPaymentSubmissionsSeeder::class,
            LegacyNotificationsSeeder::class,
            LegacyAvenirTeamEmailsSeeder::class,
            LegacyPoolConfigSeeder::class,

            // === Data Dummy untuk Development ===
            DummyDataSeeder::class,
            
            // === Seeder Emiten Demo ===
            DmasSeeder::class,
        ]);
    }
}
