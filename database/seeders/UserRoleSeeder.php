<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // تصفير الجداول لمنع تكرار البيانات أو حدوث تضارب في المفاتيح الأجنبية
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('regular_users')->truncate();
        DB::table('volunteers')->truncate();
        DB::table('veterinarians')->truncate();
        DB::table('roles')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. إنشاء الأدوار داخل الحزمة مع تحديد جارد الـ API
        $superAdminRole = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'api']);
        $volunteerRole  = Role::create(['name' => 'Volunteer', 'guard_name' => 'api']);
        $vetRole        = Role::create(['name' => 'Veterinarian', 'guard_name' => 'api']);

        // ===================================================================
        // 2. إنشاء حساب الـ SuperAdmin وتعيين الدور له
        // ===================================================================
        $admin = User::create([
            'full_name'      => 'المدير العام للمنصة',
            'email'          => 'admin@platform.com',
            'password'       => Hash::make('Admin@1234'),
            'country_code'   => '963',
            'phone_number'   => '933333333',
            'governorate'    => 'دمشق',
            'latitude'       => 33.51380000,
            'longitude'      => 36.27650000,
            'account_status' => 'active',
        ]);
        $admin->assignRole($superAdminRole);


        // ===================================================================
        // 3. متطوع 1: مبتدئ (Beginner) - قريب جغرافيًا (يستقبل الحالات الـ Normal فقط)
        // ===================================================================
        $volunteerUser1 = User::create([
            'full_name'      => 'أحمد المنقذ المبتدئ',
            'email'          => 'volunteer_beginner@platform.com',
            'password'       => Hash::make('Volunteer@1234'),
            'country_code'   => '963',
            'phone_number'   => '944444444',
            'governorate'    => 'دمشق',
            'latitude'       => 33.51500000, 
            'longitude'      => 36.28000000,
            'account_status' => 'active',
        ]);
        $volunteerUser1->assignRole($volunteerRole);

        DB::table('volunteers')->insert([
            'user_id'           => $volunteerUser1->id,
            'detailed_address'  => 'دمشق - القصاع - برج الروس',
            'age'               => 22,
            'vol_type'          => 'field',
            'experience_level'  => 'beginner', // 👈 مبتدئ
            'equipment'         => json_encode(['pet_carrier']), 
            'current_latitude'  => 33.51520000,  // قريب جداً من مركز البلاغ التجريبي
            'current_longitude' => 36.28050000,  
            'is_approved'       => true,
            'approved_at'       => now(),
            'approved_by'       => $admin->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);


        // ===================================================================
        // 4. متطوع 2: متوسط (Intermediate) - قريب جغرافيًا (يستقبل الـ Normal والـ Urgent)
        // ===================================================================
        $volunteerUser2 = User::create([
            'full_name'      => 'مصطفى المنقذ المتوسط',
            'email'          => 'volunteer_intermediate@platform.com',
            'password'       => Hash::make('Volunteer@1234'),
            'country_code'   => '963',
            'phone_number'   => '988888888',
            'governorate'    => 'دمشق',
            'latitude'       => 33.51800000, 
            'longitude'      => 36.28500000,
            'account_status' => 'active',
        ]);
        $volunteerUser2->assignRole($volunteerRole);

        DB::table('volunteers')->insert([
            'user_id'           => $volunteerUser2->id,
            'detailed_address'  => 'دمشق - باب توما - الشارع العام',
            'age'               => 27,
            'vol_type'          => 'field',
            'experience_level'  => 'intermediate', // 👈 متوسط
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
        // 5. متطوع 3: متقدم (Advanced) - قريب جغرافيًا (يستقبل جميع الحالات بما فيها Critical)
        // ===================================================================
        $volunteerUser3 = User::create([
            'full_name'      => 'خالد المنقذ المتقدم',
            'email'          => 'volunteer_advanced@platform.com',
            'password'       => Hash::make('Volunteer@1234'),
            'country_code'   => '963',
            'phone_number'   => '977777777',
            'governorate'    => 'دمشق',
            'latitude'       => 33.51200000, 
            'longitude'      => 36.27200000,
            'account_status' => 'active',
        ]);
        $volunteerUser3->assignRole($volunteerRole);

        DB::table('volunteers')->insert([
            'user_id'           => $volunteerUser3->id,
            'detailed_address'  => 'دمشق - ساحة التحرير',
            'age'               => 32,
            'vol_type'          => 'field',
            'experience_level'  => 'advanced', // 👈 متقدم محترف الحالات الحرجة
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
        // 6. إنشاء حساب الطبيب البيطري وتعيين الدور له + تفاصيله الممتدة
        // ===================================================================
        $vetUser = User::create([
            'full_name'      => 'الدكتور حكيم البيطري',
            'email'          => 'vet@platform.com',
            'password'       => Hash::make('Vet@1234'),
            'country_code'   => '963',
            'phone_number'   => '955555555',
            'governorate'    => 'حلب',
            'latitude'       => 36.20210000,
            'longitude'      => 37.13430000,
            'account_status' => 'active',
        ]);
        $vetUser->assignRole($vetRole);

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
        // 7. إنشاء حساب المستخدم العادي (Regular User) + تفاصيله الممتدة
        // ===================================================================
        $regularUser = User::create([
            'full_name'      => 'محمد مبلّغ الحالات',
            'email'          => 'user@platform.com',
            'password'       => Hash::make('User@1234'),
            'country_code'   => '963',
            'phone_number'   => '966666666',
            'governorate'    => 'حمص',
            'latitude'       => 34.73240000,
            'longitude'      => 36.71370000,
            'account_status' => 'active',
        ]);

        DB::table('regular_users')->insert([
            'user_id'      => $regularUser->id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }
}