<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'My Premium CMS',
                'type' => 'text',
            ],
            [
                'key' => 'site_description',
                'value' => 'Sebuah platform CMS serbaguna berbasis Laravel 11 dan Vue 3 yang sangat fleksibel dan super cepat.',
                'type' => 'textarea',
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
            ],
            [
                'key' => 'whatsapp_number',
                'value' => '6281234567890',
                'type' => 'text',
            ],
            [
                'key' => 'playstore_link',
                'value' => 'https://play.google.com/store/apps/details?id=com.mycms.app',
                'type' => 'url',
            ],
            [
                'key' => 'bank_account_info',
                'value' => 'Marta Fikri 3370748356 bank BCA',
                'type' => 'textarea',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
