<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = file_get_contents(database_path('seeders/research_data.json'));
        $data = json_decode($json, true);
        
        foreach ($data as $item) {
            \App\Models\Property::create([
                'title' => $item['title'],
                'ticker' => $item['ticker'],
                'sector' => $item['sector'],
                'location' => $item['subtitle'],
                'price' => $item['price'] ?? '10000',
            ]);
        }
    }
}
