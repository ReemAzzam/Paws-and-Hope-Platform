<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create unified application roles with api guard
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'regular_user', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'veterinarian', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'volunteer', 'guard_name' => 'api']);

        $this->command->info('✅ System roles seeded successfully!');
    }
}