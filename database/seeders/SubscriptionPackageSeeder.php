<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\SubscriptionPackage::updateOrCreate(
            ['id' => '1bulan'],
            [
                'name' => 'Bulanan',
                'price' => 149000,
                'period_text' => '/ bulan',
                'per_month_text' => null,
                'save_text' => 'Fleksibel · Bisa berhenti kapan saja',
                'duration_days' => 30,
                'badge' => null,
                'special_bg' => false,
                'image_path' => '/images/subscription/plan-bulanan.png',
                'discount_percent' => 40,
                'discount_end_at' => now()->addDays(7), // Discount valid for 7 days
                'is_active' => true,
            ]
        );

        \App\Models\SubscriptionPackage::updateOrCreate(
            ['id' => '3bulan'],
            [
                'name' => '3 Bulan',
                'price' => 399000,
                'period_text' => '/ 3 bulan',
                'per_month_text' => '≈ Rp 133.000 / bulan',
                'save_text' => 'Hemat Rp 48.000 vs bulanan',
                'duration_days' => 90,
                'badge' => 'Populer',
                'special_bg' => false,
                'image_path' => '/images/subscription/plan-3bulan.png',
                'discount_percent' => 0,
                'discount_end_at' => null,
                'is_active' => true,
            ]
        );

        \App\Models\SubscriptionPackage::updateOrCreate(
            ['id' => '6bulan'],
            [
                'name' => '6 Bulan',
                'price' => 729000,
                'period_text' => '/ 6 bulan',
                'per_month_text' => '≈ Rp 121.500 / bulan',
                'save_text' => 'Hemat Rp 165.000 · Diskon 18%',
                'duration_days' => 180,
                'badge' => 'Nilai Terbaik',
                'special_bg' => true,
                'image_path' => '/images/subscription/plan-6bulan.png',
                'discount_percent' => 0,
                'discount_end_at' => null,
                'is_active' => true,
            ]
        );

        \App\Models\SubscriptionPackage::updateOrCreate(
            ['id' => '12bulan'],
            [
                'name' => '1 Tahun',
                'price' => 1289000,
                'period_text' => '/ tahun',
                'per_month_text' => '≈ Rp 107.500 / bulan',
                'save_text' => 'Hemat Rp 499.000 · Diskon 28%',
                'duration_days' => 365,
                'badge' => 'Paling Hemat',
                'special_bg' => false,
                'image_path' => '/images/subscription/plan-12bulan.png',
                'discount_percent' => 0,
                'discount_end_at' => null,
                'is_active' => true,
            ]
        );
    }
}
