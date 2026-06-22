<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // مسح الكاش
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الأدوار
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'regular_user']);
        Role::firstOrCreate(['name' => 'veterinarian']);
        Role::firstOrCreate(['name' => 'volunteer']);

        $this->command->info('✅ Roles seeded successfully!');
    }
}
