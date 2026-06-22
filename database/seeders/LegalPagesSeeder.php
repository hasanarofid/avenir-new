<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'meta_description' => 'Kebijakan privasi Avenir Research',
                'content' => '<h2>Privacy Policy</h2><p>Masukkan kebijakan privasi Anda di sini.</p>'
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'meta_description' => 'Syarat dan Ketentuan layanan Avenir Research',
                'content' => '<h2>Terms of Service</h2><p>Masukkan syarat dan ketentuan Anda di sini.</p>'
            ],
            [
                'title' => 'Disclaimer',
                'slug' => 'disclaimer',
                'meta_description' => 'Penafian (Disclaimer) Avenir Research',
                'content' => '<h2>Disclaimer</h2><p>Masukkan penafian (disclaimer) Anda di sini.</p>'
            ]
        ];

        foreach ($pages as $p) {
            $page = Page::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'title' => $p['title'],
                    'meta_description' => $p['meta_description'],
                    'is_active' => true
                ]
            );

            Section::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'key' => 'content'
                ],
                [
                    'title' => 'Konten Halaman',
                    'order' => 1,
                    'content' => ['body' => $p['content']],
                    'is_active' => true
                ]
            );
        }
    }
}
