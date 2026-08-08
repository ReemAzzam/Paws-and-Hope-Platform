<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->where('email', '!=', 'admin@animalrescue.com')->delete();
        DB::table('regular_users')->truncate();
        DB::table('volunteers')->truncate();
        DB::table('veterinarians')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $regularUserRole = Role::findOrCreate('regular_user', 'api');
        $superAdminRole  = Role::findOrCreate('SuperAdmin', 'api');
        $volunteerRole   = Role::findOrCreate('volunteer', 'api');
        $vetRole         = Role::findOrCreate('veterinarian', 'api');

        // ===================================================================
        // SuperAdmin
        // ===================================================================
        $admin = User::updateOrCreate(
            ['email' => 'admin@platform.com'],
            [
                'full_name'          => 'المدير العام للمنصة',
                'password'           => Hash::make('Admin@1234'),
                'photo'              => $this->storeUserPhoto('admin.avif', 'admin.avif'),
                'country_code'       => 'SY',
                'phone_number'       => '933333333',
                'governorate'        => 'دمشق',
                'latitude'           => 33.51380000,
                'longitude'          => 36.27650000,
                'account_status'     => 'active',
                'email_verified_at'  => now(),
                'two_factor_enabled' => true
            ]
        );
        $admin->assignRole($superAdminRole);

        // ===================================================================
        // Volunteer 1
        // ===================================================================
        $volunteerUser1 = User::create([
            'full_name'          => 'Ahmad Beginner Rescuer',
            'email'              => 'volunteer_beginner@platform.com',
            'password'           => Hash::make('Volunteer@1234'),
            'photo'              => $this->storeUserPhoto('volunteer1.avif', 'volunteer1.avif'),
            'country_code'       => 'SY',
            'phone_number'       => '944444444',
            'governorate'        => 'دمشق',
            'latitude'           => 33.51500000,
            'longitude'          => 36.28000000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true
        ]);
        $volunteerUser1->assignRole($volunteerRole);

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
        // Volunteer 2
        // ===================================================================
        $volunteerUser2 = User::create([
            'full_name'          => 'Mustafa Intermediate Rescuer',
            'email'              => 'volunteer_intermediate@platform.com',
            'password'           => Hash::make('Volunteer@1234'),
            'photo'              => $this->storeUserPhoto('volunteer2.avif', 'volunteer2.avif'),
            'country_code'       => 'SY',
            'phone_number'       => '988888888',
            'governorate'        => 'دمشق',
            'latitude'           => 33.51800000,
            'longitude'          => 36.28500000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true
        ]);
        $volunteerUser2->assignRole($volunteerRole);

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
        // Volunteer 3
        // ===================================================================
        $volunteerUser3 = User::create([
            'full_name'          => 'Khaled Advanced Rescuer',
            'email'              => 'volunteer_advanced@platform.com',
            'password'           => Hash::make('Volunteer@1234'),
            'photo'              => $this->storeUserPhoto('volunteer3.avif', 'volunteer3.avif'),
            'country_code'       => 'SY',
            'phone_number'       => '977777777',
            'governorate'        => 'دمشق',
            'latitude'           => 33.51200000,
            'longitude'          => 36.27200000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true
        ]);
        $volunteerUser3->assignRole($volunteerRole);

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
        // Veterinarian
        // ===================================================================
        $vetUser = User::create([
            'full_name'          => 'Dr. Hakeem Al-Baitari',
            'email'              => 'vet@platform.com',
            'password'           => Hash::make('Vet@1234'),
            'photo'              => $this->storeUserPhoto('vet1.avif', 'vet1.avif'),
            'country_code'       => 'SY',
            'phone_number'       => '955555555',
            'governorate'        => 'حلب',
            'latitude'           => 36.20210000,
            'longitude'          => 37.13430000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true,
        ]);
        $vetUser->assignRole($vetRole);

        DB::table('veterinarians')->insert([
            'user_id'           => $vetUser->id,
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
        // Regular Users
        // ===================================================================
        $regularUser = User::create([
            'full_name'          => 'Mohamad Case Reporter',
            'email'              => 'user@platform.com',
            'password'           => Hash::make('User@1234'),
            'photo'              => $this->storeUserPhoto('user1.avif', 'user1.avif'),
            'country_code'       => 'SY',
            'phone_number'       => '966666666',
            'governorate'        => 'حمص',
            'latitude'           => 34.73240000,
            'longitude'          => 36.71370000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true,
        ]);
        $regularUser->assignRole($regularUserRole);

        DB::table('regular_users')->insert([
            'user_id'    => $regularUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularUser = User::create([
            'full_name'          => 'Lenar',
            'email'              => 'Lili@platform.com',
            'password'           => Hash::make('User@1234'),
            'photo'              => $this->storeUserPhoto('user2.avif', 'user2.avif'),
            'country_code'       => '+49',
            'phone_number'       => '15758083978',
            'governorate'        => 'حمص',
            'latitude'           => 34.73240000,
            'longitude'          => 36.71370000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true,
        ]);
        $regularUser->assignRole($regularUserRole);

        DB::table('regular_users')->insert([
            'user_id'    => $regularUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularUser = User::create([
            'full_name'          => 'Zain',
            'email'              => 'Zain@platform.com',
            'password'           => Hash::make('User@1234'),
            'photo'              => $this->storeUserPhoto('user3.avif', 'user3.avif'),
            'country_code'       => 'DE',
            'phone_number'       => '15754083978',
            'governorate'        => 'دمشق',
            'latitude'           => 34.73240000,
            'longitude'          => 36.71370000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true,
        ]);
        $regularUser->assignRole($regularUserRole);

        DB::table('regular_users')->insert([
            'user_id'    => $regularUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularUser = User::create([
            'full_name'          => 'Louna',
            'email'              => 'Lounaaa@platform.com',
            'password'           => Hash::make('User@1234'),
            'photo'              => $this->storeUserPhoto('user4.avif', 'user4.avif'),
            'country_code'       => 'AE',
            'phone_number'       => '5677803978',
            'governorate'        => 'الامارات',
            'latitude'           => 34.73240000,
            'longitude'          => 36.71370000,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true,
        ]);
        $regularUser->assignRole($regularUserRole);

        DB::table('regular_users')->insert([
            'user_id'    => $regularUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Application roles and integrated data seeded successfully!');
    }

    /**
     * تخزين صورة المستخدم بنفس أسلوب Lost & Found
     * يرجع مسار نسبي مثل: users/admin.avif
     */
    private function storeUserPhoto(string $sourceFileName, string $targetFileName): ?string
    {
        $sourcePath = database_path('seeders/assets/users/' . $sourceFileName);

        if (!File::exists($sourcePath)) {
            $this->command?->warn("Photo not found: {$sourceFileName}");
            return null;
        }

        $targetPath = 'users/' . $targetFileName;

        Storage::disk('public')->put(
            $targetPath,
            File::get($sourcePath)
        );

        return $targetPath;
    }
}
