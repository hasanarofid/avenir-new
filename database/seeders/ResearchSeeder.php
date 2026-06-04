<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('seeders/research_data.json'));
        $data = json_decode($json, true);
        
        foreach ($data as $item) {
            \App\Models\Research::create([
                'title' => $item['title'],
                'subtitle' => $item['subtitle'],
                'ticker' => $item['ticker'],
                'sector' => $item['sector'],
                'revenue' => $item['revenue'],
                'patmi' => $item['patmi'],
                'sales' => $item['sales'],
                'price' => $item['price'],
                'tags' => $item['tags'],
                'date' => $item['date'],
            ]);
        }
    }
}
