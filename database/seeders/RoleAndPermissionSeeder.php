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
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo(Permission::all());

        $editorRole = Role::findOrCreate('editor', 'web');
        $editorRole->givePermissionTo(['manage pages', 'manage posts', 'publish articles']);

        $clientRole = Role::findOrCreate('client', 'web');
        $clientRole->givePermissionTo(['create articles', 'edit articles']);
        
        // Role untuk Mitra Analis
        $mitraRole = Role::findOrCreate('mitra', 'web');
        $mitraRole->givePermissionTo(['create articles', 'edit articles']);
        
        // Role untuk Subscriber (Pengguna Berbayar)
        Role::findOrCreate('subscriber', 'web');
        
        // Role untuk User Biasa
        Role::findOrCreate('user', 'web');
    }
}
