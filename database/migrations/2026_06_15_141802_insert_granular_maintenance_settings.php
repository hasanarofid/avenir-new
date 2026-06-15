<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $keys = [
            'maint_home',
            'maint_katalog',
            'maint_artikel',
            'maint_news',
            'maint_emiten',
            'maint_ki_brief',
            'maint_disclosure',
            'maint_tentang',
            'maint_mitra',
            'maint_langganan'
        ];

        foreach ($keys as $key) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => '0', 'type' => 'boolean', 'created_at' => now(), 'updated_at' => now()]
            );
        }
        
        // Remove the global maintenance_mode from previous migration
        DB::table('settings')->where('key', 'maintenance_mode')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'maint_home',
            'maint_katalog',
            'maint_artikel',
            'maint_news',
            'maint_emiten',
            'maint_ki_brief',
            'maint_disclosure',
            'maint_tentang',
            'maint_mitra',
            'maint_langganan'
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
        
        // Restore global maintenance_mode
        DB::table('settings')->updateOrInsert(
            ['key' => 'maintenance_mode'],
            ['value' => '0', 'type' => 'boolean', 'created_at' => now(), 'updated_at' => now()]
        );
    }
};
