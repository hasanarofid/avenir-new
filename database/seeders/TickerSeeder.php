<?php

namespace Database\Seeders;

use App\Models\Ticker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TickerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFile = __DIR__ . '/saham.json';
        
        if (!File::exists($jsonFile)) {
            $this->command->error("File saham.json tidak ditemukan!");
            return;
        }

        $sahamData = json_decode(File::get($jsonFile), true);

        $this->command->info('Memulai proses seeding ' . count($sahamData) . ' emiten...');

        foreach ($sahamData as $saham) {
            $companyProfile = [
                'tanggal_pencatatan' => $saham['Tanggal_Pencatatan'] ?? null,
                'saham_beredar' => $saham['Saham'] ?? null,
                'papan_pencatatan' => $saham['Papan'] ?? null,
            ];

            Ticker::updateOrCreate(
                ['symbol' => $saham['Kode']],
                [
                    'company_name' => $saham['Nama'],
                    'company_profile' => $companyProfile,
                ]
            );
        }

        $this->command->info('Proses seeding selesai!');
    }
}
