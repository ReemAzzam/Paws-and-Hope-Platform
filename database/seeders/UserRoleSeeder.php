<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign keys and truncate tables to reset data safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->where('email', '!=', 'admin@animalrescue.com')->delete(); // مسح الكل عدا الأدمن الأساسي
        DB::table('regular_users')->truncate();
        DB::table('volunteers')->truncate();
        DB::table('veterinarians')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get the core admin instance created by SuperAdminSeeder
        $admin = User::where('email', 'admin@animalrescue.com')->first();

        if (!$admin) {
            $this->command->error('SuperAdmin not found! Please run SuperAdminSeeder first.');
            return;
        }

        // ===================================================================
        // 1. Volunteer 1: Beginner
        // ===================================================================
        $volunteerUser1 = User::create([
            'full_name'      => 'Ahmad Beginner Rescuer',
            'email'          => 'volunteer_beginner@platform.com',
            'password'       => Hash::make('Volunteer@1234'),
            'country_code'   => '963',
            'phone_number'   => '944444444',
            'governorate'    => 'دمشق',
            'latitude'       => 33.51500000, 
            'longitude'      => 36.28000000,
            'account_status' => 'active',
        ]);
        $volunteerUser1->assignRole('volunteer');

        DB::table('volunteers')->insert([
            'user_id'           => $volunteerUser1->id,
            'detailed_address'  => 'دمشق - القصاع - برج الروس',
            'age'               => 22,
            'vol_type'          => 'field',
            'experience_level'  => 'beginner',
            'equipment'         => json_encode(['pet_carrier']), 
            'current_latitude'  => 33.51520000,  
            'current_longitude' => 36.28050000,  
            'is_approved'       => true,
            'approved_at'       => now(),
            'approved_by'       => $admin->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ===================================================================
        // 2. Volunteer 2: Intermediate
        // ===================================================================
        $volunteerUser2 = User::create([
            'full_name'      => 'Mustafa Intermediate Rescuer',
            'email'          => 'volunteer_intermediate@platform.com',
            'password'       => Hash::make('Volunteer@1234'),
            'country_code'   => '963',
            'phone_number'   => '988888888',
            'governorate'    => 'دمشق',
            'latitude'       => 33.51800000, 
            'longitude'      => 36.28500000,
            'account_status' => 'active',
        ]);
        $volunteerUser2->assignRole('volunteer');

        DB::table('volunteers')->insert([
            'user_id'           => $volunteerUser2->id,
            'detailed_address'  => 'دمشق - باب توما - الشارع العام',
            'age'               => 27,
            'vol_type'          => 'field',
            'experience_level'  => 'intermediate',
            'equipment'         => json_encode(['first_aid_kit', 'pet_carrier']), 
            'current_latitude'  => 33.51820000, 
            'current_longitude' => 36.28550000,
            'is_approved'       => true,
            'approved_at'       => now(),
            'approved_by'       => $admin->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ===================================================================
        // 3. Volunteer 3: Advanced
        // ===================================================================
        $volunteerUser3 = User::create([
            'full_name'      => 'Khaled Advanced Rescuer',
            'email'          => 'volunteer_advanced@platform.com',
            'password'       => Hash::make('Volunteer@1234'),
            'country_code'   => '963',
            'phone_number'   => '977777777',
            'governorate'    => 'دمشق',
            'latitude'       => 33.51200000, 
            'longitude'      => 36.27200000,
            'account_status' => 'active',
        ]);
        $volunteerUser3->assignRole('volunteer');

        DB::table('volunteers')->insert([
            'user_id'           => $volunteerUser3->id,
            'detailed_address'  => 'دمشق - ساحة التحرير',
            'age'               => 32,
            'vol_type'          => 'field',
            'experience_level'  => 'advanced',
            'equipment'         => json_encode(['first_aid_kit', 'pet_net', 'heavy_gloves']), 
            'current_latitude'  => 33.51250000, 
            'current_longitude' => 36.27250000,
            'is_approved'       => true,
            'approved_at'       => now(),
            'approved_by'       => $admin->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ===================================================================
        // 4. Veterinarian User
        // ===================================================================
        $vetUser = User::create([
            'full_name'      => 'Dr. Hakeem Al-Baitari',
            'email'          => 'vet@platform.com',
            'password'       => Hash::make('Vet@1234'),
            'country_code'   => '963',
            'phone_number'   => '955555555',
            'governorate'    => 'حلب',
            'latitude'       => 36.20210000,
            'longitude'      => 37.13430000,
            'account_status' => 'active',
        ]);
        $vetUser->assignRole('veterinarian');

        DB::table('veterinarians')->insert([
            'user_id'           => $vetUser->id,
            'professional_name' => 'عيادة الشفاء البيطرية',
            'specialization'    => 'جراحة الحيوانات الأليفة',
            'clinic_location'   => 'حلب - حي الشهباء',
            'license_number'    => 'VET-2026-9982',
            'working_hours'     => '10:00 AM - 08:00 PM',
            'is_approved'       => true,
            'approved_at'       => now(),
            'approved_by'       => $admin->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ===================================================================
        // 5. Regular User
        // ===================================================================
        $regularUser = User::create([
            'full_name'      => 'Mohamad Case Reporter',
            'email'          => 'user@platform.com',
            'password'       => Hash::make('User@1234'),
            'country_code'   => '963',
            'phone_number'   => '966666666',
            'governorate'    => 'حمص',
            'latitude'       => 34.73240000,
            'longitude'      => 36.71370000,
            'account_status' => 'active',
        ]);
        $regularUser->assignRole('regular_user');

        DB::table('regular_users')->insert([
            'user_id'    => $regularUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Application roles and extended data seeded successfully!');
    }
}