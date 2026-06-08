<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage settings',
            'manage pages',
            'manage posts',
            'manage users',
            'create articles',
            'edit articles',
            'publish articles',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign existing permissions
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo(Permission::all());

        $editorRole = Role::findOrCreate('editor');
        $editorRole->givePermissionTo(['manage pages', 'manage posts', 'publish articles']);

        $clientRole = Role::findOrCreate('client');
        $clientRole->givePermissionTo(['create articles', 'edit articles']);
    }
}
