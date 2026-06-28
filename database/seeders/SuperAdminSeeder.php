<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@animalrescue.com'],
            [
                'full_name'          => 'Super Administrator',
                'password'           => Hash::make('password123'),
                'country_code'       => '963',
                'phone_number'       => '938337719',
                'governorate'        => 'دمشق',
                'account_status'     => 'active',
                'email_verified_at'  => now(),
                'two_factor_enabled' => true,
            ]
        );

        // Assign unified role to the super admin
        $superAdmin->assignRole('super_admin');

        $this->command->info('✅ Super Admin established successfully!');
    }
}