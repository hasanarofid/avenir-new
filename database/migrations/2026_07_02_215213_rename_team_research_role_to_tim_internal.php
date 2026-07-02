<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = \Spatie\Permission\Models\Role::where('name', 'team_research')->first();
        if ($role) {
            $role->name = 'tim_internal';
            $role->save();
            $role->givePermissionTo(['create articles', 'edit articles', 'publish articles']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $role = \Spatie\Permission\Models\Role::where('name', 'tim_internal')->first();
        if ($role) {
            $role->name = 'team_research';
            $role->save();
            $role->revokePermissionTo(['create articles', 'edit articles', 'publish articles']);
        }
    }
};
