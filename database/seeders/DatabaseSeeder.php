<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserRoleSeeder::class,
            AnimalSeeder::class,
        ]);

        /*User::factory()->create([
            'full_name'      => 'Test User',
            'email'          => 'test@example.com',
            'account_status' => 'active',
        ]);*/
    }
}