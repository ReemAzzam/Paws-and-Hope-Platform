<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('regular_users')->truncate();
        DB::table('volunteers')->truncate();
        DB::table('veterinarians')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $regularRole   = Role::findOrCreate('regular_user', 'api');
        $superAdminRole = Role::findOrCreate('SuperAdmin', 'api');
        $volunteerRole = Role::findOrCreate('volunteer', 'api');
        $vetRole       = Role::findOrCreate('veterinarian', 'api');

        /*
        |--------------------------------------------------------------------------
        | 1) Super Admin
        |--------------------------------------------------------------------------
        */
        $admin = User::create([
            'full_name'          => 'Platform Super Admin',
            'email'              => 'admin@platform.com',
            'password'           => Hash::make('Admin@1234'),
            'photo'              => $this->storePhoto('admin.avif', 'admin.avif'),
            'country_code'       => 'SY',
            'phone_number'       => '933000000',
            'governorate'        => 'Damascus',
            'latitude'           => 33.51380000,
            'longitude'          => 36.27650000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true,
        ]);
        $admin->assignRole($superAdminRole);

        /*
        |--------------------------------------------------------------------------
        | 2) 30 Veterinarians (all in Syria)
        |--------------------------------------------------------------------------
        */
        $vetNames = [
            'Dr. Ahmad Al Shami', 'Dr. Lina Haddad', 'Dr. Omar Al Khatib', 'Dr. Nour Saleh', 'Dr. Rania Khalil',
            'Dr. Samer Mustafa', 'Dr. Maya Ibrahim', 'Dr. Tarek Youssef', 'Dr. Salma Nasser', 'Dr. Basel Hamoud',
            'Dr. Dima Farhat', 'Dr. Khaled Darwish', 'Dr. Reem Abbas', 'Dr. Fadi Ahmad', 'Dr. Hala Mustafa',
            'Dr. Yazan Ali', 'Dr. Sara Hamdan', 'Dr. Wissam Hassan', 'Dr. Jana Mahmoud', 'Dr. Hadi Nour',
            'Dr. Farah Jad', 'Dr. Imad Raed', 'Dr. Ghada Nour', 'Dr. Raed Sami', 'Dr. Mona Hatem',
            'Dr. Hatem Ali', 'Dr. Ruba Zain', 'Dr. Nader Imad', 'Dr. Salma Tamer', 'Dr. Karim Bassam',
        ];

        $syCities = [
            ['Damascus', 33.5138, 36.2765, 'Damascus - Mezzeh'],
            ['Damascus', 33.5201, 36.2802, 'Damascus - Abu Rummaneh'],
            ['Aleppo', 36.2021, 37.1343, 'Aleppo - Al Shahba'],
            ['Aleppo', 36.2100, 37.1500, 'Aleppo - Al Aziziyah'],
            ['Homs', 34.7324, 36.7137, 'Homs - Inshaat'],
            ['Latakia', 35.5317, 35.7913, 'Latakia - Corniche'],
            ['Tartus', 34.8950, 35.8867, 'Tartus - City Center'],
            ['Rural Damascus', 33.4500, 36.3000, 'Jaramana'],
            ['Hama', 35.1318, 36.7578, 'Hama - City Center'],
            ['Sweida', 32.7081, 36.5695, 'Sweida - Main Street'],
        ];

        $specializations = [
            'Small Animal Surgery', 'Canine Medicine', 'Feline Medicine', 'Emergency Veterinary Care',
            'Internal Medicine', 'Orthopedic Surgery', 'Avian and Exotic Pets', 'Preventive Veterinary Medicine',
            'Dermatology', 'Anesthesia and Critical Care',
        ];

        foreach ($vetNames as $i => $name) {
            $n = $i + 1;
            $city = $syCities[$i % count($syCities)];
            $approved = $n <= 20; // 20 active, 10 pending

            $user = User::create([
                'full_name'          => $name,
                'email'              => "vet{$n}@platform.com",
                'password'           => Hash::make('Vet@1234'),
                'photo'              => $this->storePhoto("vet{$n}.avif", "vet{$n}.avif"),
                'country_code'       => 'SY',
                'phone_number'       => '95555' . str_pad((string)$n, 4, '0', STR_PAD_LEFT),
                'governorate'        => $city[0],
                'latitude'           => $city[1],
                'longitude'          => $city[2],
                'account_status'     => $approved ? 'active' : 'pending',
                'email_verified_at'  => $approved ? now() : null,
                'two_factor_enabled' => $approved,
            ]);
            $user->assignRole($vetRole);

            DB::table('veterinarians')->insert([
                'user_id'          => $user->id,
                'specialization'   => $specializations[$i % count($specializations)],
                'clinic_location'  => $city[3],
                'license_number'   => 'VET-2026-' . str_pad((string)$n, 4, '0', STR_PAD_LEFT),
                'working_hours'    => '09:00 AM - 05:00 PM',
                'experience_years' => 3 + ($n % 15),
                'about'            => 'Dedicated veterinarian providing professional animal healthcare services in Syria.',
                'bio'              => 'Experienced in clinical diagnosis, treatment planning, and post-rescue care.',
                'is_approved'      => $approved,
                'approved_at'      => $approved ? now() : null,
                'approved_by'      => $approved ? $admin->id : null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3) 40 Volunteers (Damascus + Rural Damascus only)
        |--------------------------------------------------------------------------
        */
        $volNames = [
            'Ahmad Rescuer', 'Mustafa Field Helper', 'Khaled Advanced Rescuer', 'Omar Transport Volunteer',
            'Samer Rescue Member', 'Yousef Pet Helper', 'Bilal Field Volunteer', 'Hussein Animal Helper',
            'Maher Rescue Team', 'Tamer Volunteer', 'Rami Transport Helper', 'Firas Field Member',
            'Wael Advanced Helper', 'Nabil Animal Volunteer', 'Laith Rescuer', 'Hadi Pet Volunteer',
            'Ziad Photographer', 'Karim Camera Volunteer', 'Adel Field Helper', 'Marwan Rescue Volunteer',
            'Shadi Volunteer', 'Ayman Transport Member', 'Rashid Animal Helper', 'Anas Rescuer',
            'Bashar Advanced Volunteer', 'Nour Pending Volunteer', 'Hassan Pending Helper', 'Fadi Pending Rescuer',
            'Sultan Pending Member', 'Yazan Pending Volunteer', 'Tareq Pending Helper', 'Iyad Pending Rescuer',
            'Majd Pending Volunteer', 'Kinan Photographer', 'Rami Pending Helper', 'Nasser Pending Member',
            'Ali Pending Volunteer', 'Mazen Transport Helper', 'Hamza Field Volunteer', 'Saeed Rescue Helper',
        ];

        $damascusAreas = [
            ['Damascus', 33.5138, 36.2765, 'Damascus - Qassaa'],
            ['Damascus', 33.5200, 36.2810, 'Damascus - Mezzeh'],
            ['Damascus', 33.5160, 36.2900, 'Damascus - Shaalan'],
            ['Damascus', 33.5100, 36.2780, 'Damascus - Kafar Souseh'],
            ['Damascus', 33.5220, 36.2880, 'Damascus - Abu Rummaneh'],
            ['Damascus', 33.5300, 36.2950, 'Damascus - Rukn Al Din'],
            ['Damascus', 33.5050, 36.2800, 'Damascus - Midan'],
            ['Rural Damascus', 33.4500, 36.3000, 'Jaramana - Main Road'],
            ['Rural Damascus', 33.4800, 36.3200, 'Qudsaya'],
            ['Rural Damascus', 33.5100, 36.3500, 'Harasta'],
        ];

        $volTypes = ['field', 'photography', 'transportation', 'other'];
        $expLevels = ['beginner', 'intermediate', 'advanced'];
        $equipmentSets = [
            ['pet_carrier'],
            ['first_aid_kit', 'pet_carrier'],
            ['first_aid_kit', 'pet_net', 'heavy_gloves'],
            ['transport_vehicle', 'pet_carrier'],
            ['camera', 'first_aid_kit'],
        ];

        foreach ($volNames as $i => $name) {
            $n = $i + 1;
            $area = $damascusAreas[$i % count($damascusAreas)];
            $approved = $n <= 25; // 25 active, 15 pending

            $user = User::create([
                'full_name'          => $name,
                'email'              => "volunteer{$n}@platform.com",
                'password'           => Hash::make('Volunteer@1234'),
                'photo'              => $this->storePhoto("vol{$n}.avif", "vol{$n}.avif"),
                'country_code'       => 'SY',
                'phone_number'       => '94444' . str_pad((string)$n, 4, '0', STR_PAD_LEFT),
                'governorate'        => $area[0],
                'latitude'           => $area[1],
                'longitude'          => $area[2],
                'account_status'     => $approved ? 'active' : 'pending',
                'email_verified_at'  => $approved ? now() : null,
                'two_factor_enabled' => $approved,
            ]);
            $user->assignRole($volunteerRole);

            DB::table('volunteers')->insert([
                'user_id'           => $user->id,
                'detailed_address'  => $area[3],
                'age'               => 20 + ($n % 20),
                'vol_type'          => $volTypes[$i % count($volTypes)],
                'experience_level'  => $expLevels[$i % count($expLevels)],
                'equipment'         => json_encode($equipmentSets[$i % count($equipmentSets)]),
                'current_latitude'  => $area[1],
                'current_longitude' => $area[2],
                'is_approved'       => $approved,
                'approved_at'       => $approved ? now() : null,
                'approved_by'       => $approved ? $admin->id : null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4) 25 Regular Users
        |--------------------------------------------------------------------------
        */
        $regularUsers = [
            ['Mohamad Reporter', 'user1@platform.com', 'SY', 'Damascus', 33.5138, 36.2765],
            ['Lina Karim', 'user2@platform.com', 'SY', 'Aleppo', 36.2021, 37.1343],
            ['Zain Al Din', 'user3@platform.com', 'DE', 'Berlin', 52.5200, 13.4050],
            ['Louna Hassan', 'user4@platform.com', 'AE', 'Dubai', 25.2048, 55.2708],
            ['Rama Saleh', 'user5@platform.com', 'SY', 'Homs', 34.7324, 36.7137],
            ['Kareem Nader', 'user6@platform.com', 'SY', 'Latakia', 35.5317, 35.7913],
            ['Maya Youssef', 'user7@platform.com', 'SY', 'Tartus', 34.8950, 35.8867],
            ['Tala Fadi', 'user8@platform.com', 'JO', 'Amman', 31.9539, 35.9106],
            ['Omar Bassam', 'user9@platform.com', 'SY', 'Damascus', 33.5200, 36.2800],
            ['Sally Nour', 'user10@platform.com', 'SY', 'Hama', 35.1318, 36.7578],
            ['Nour Hasan', 'user11@platform.com', 'SY', 'Damascus', 33.5140, 36.2770],
            ['Hiba Ali', 'user12@platform.com', 'SY', 'Aleppo', 36.2030, 37.1350],
            ['Rami Saleh', 'user13@platform.com', 'NL', 'Amsterdam', 52.3676, 4.9041],
            ['Dina Kareem', 'user14@platform.com', 'SY', 'Homs', 34.7330, 36.7140],
            ['Sami Nader', 'user15@platform.com', 'SY', 'Latakia', 35.5320, 35.7920],
            ['Lina Fadi', 'user16@platform.com', 'SY', 'Tartus', 34.8960, 35.8870],
            ['Basel Omar', 'user17@platform.com', 'SY', 'Damascus', 33.5180, 36.2820],
            ['Rana Youssef', 'user18@platform.com', 'AE', 'Abu Dhabi', 24.4539, 54.3773],
            ['Tarek Mahmoud', 'user19@platform.com', 'SY', 'Aleppo', 36.2060, 37.1370],
            ['Jana Waleed', 'user20@platform.com', 'SY', 'Damascus', 33.5190, 36.2830],
            ['Fadi Nabil', 'user21@platform.com', 'SY', 'Homs', 34.7350, 36.7160],
            ['Maya Samir', 'user22@platform.com', 'DE', 'Munich', 48.1351, 11.5820],
            ['Ahmad Ziad', 'user23@platform.com', 'SY', 'Latakia', 35.5340, 35.7940],
            ['Sara Hani', 'user24@platform.com', 'SY', 'Damascus', 33.5210, 36.2850],
            ['Walid Karim', 'user25@platform.com', 'SY', 'Rural Damascus', 33.4500, 36.3000],
        ];

        foreach ($regularUsers as $i => $u) {
            $n = $i + 1;

            $user = User::create([
                'full_name'          => $u[0],
                'email'              => $u[1],
                'password'           => Hash::make('User@1234'),
                'photo'              => $this->storePhoto("user{$n}.avif", "user{$n}.avif"),
                'country_code'       => $u[2],
                'phone_number'       => '96666' . str_pad((string)$n, 4, '0', STR_PAD_LEFT),
                'governorate'        => $u[3],
                'latitude'           => $u[4],
                'longitude'          => $u[5],
                'account_status'     => 'active',
                'email_verified_at'  => now(),
                'two_factor_enabled' => true,
            ]);
            $user->assignRole($regularRole);

            DB::table('regular_users')->insert([
                'user_id'      => $user->id,
                'country_code' => $u[2],
                'phone_number' => $user->phone_number,
                'governorate'  => $u[3],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $this->command->info('✅ Seeded: 1 admin + 30 vets + 40 volunteers + 25 regular users');
    }

    private function storePhoto(string $sourceFileName, string $targetFileName): ?string
    {
        $sourcePath = database_path('seeders/assets/users/' . $sourceFileName);

        if (!File::exists($sourcePath)) {
            $this->command?->warn("Photo not found: {$sourceFileName}");
            return null;
        }

        $targetPath = 'users/' . $targetFileName;
        Storage::disk('public')->put($targetPath, File::get($sourcePath));

        return $targetPath;
    }
}