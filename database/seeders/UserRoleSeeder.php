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
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Clean old seeded data
        |--------------------------------------------------------------------------
        */

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('regular_users')->truncate();
        DB::table('volunteers')->truncate();
        DB::table('veterinarians')->truncate();

        // Keep the admin account if it already exists.
        DB::table('users')
            ->where('email', '!=', 'admin@platform.com')
            ->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $regularUserRole = Role::findOrCreate('regular_user', 'api');
        $superAdminRole  = Role::findOrCreate('SuperAdmin', 'api');
        $volunteerRole   = Role::findOrCreate('volunteer', 'api');
        $vetRole         = Role::findOrCreate('veterinarian', 'api');

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        $admin = User::updateOrCreate(
            ['email' => 'admin@platform.com'],
            [
                'full_name'          => 'المدير العام للمنصة',
                'password'           => Hash::make('Admin@1234'),
                'photo'              => $this->storeUserPhoto('admin.avif', 'admin.avif'),
                'email'              => 'admin@platform.com',
                'country_code'       => 'SY',
                'phone_number'       => '933333333',
                'governorate'        => 'دمشق',
                'latitude'           => 33.51380000,
                'longitude'          => 36.27650000,
                'account_status'     => 'active',
                'email_verified_at'  => now(),
                'two_factor_enabled' => true,
            ]
        );

        $admin->syncRoles([$superAdminRole]);

        /*
        |--------------------------------------------------------------------------
        | 20 Veterinarians
        |--------------------------------------------------------------------------
        */

        // 1 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Hakeem Al-Baitari',
            'vet@platform.com',
            '955555555',
            'حلب',
            36.20210000,
            37.13430000,
            'جراحة الحيوانات الأليفة',
            'حلب - حي الشهباء',
            'VET-2026-9982',
            '10:00 AM - 08:00 PM',
            12,
            'vet1.avif',
            true
        );

        // 2 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Ahmad Al-Shami',
            'vet2@platform.com',
            '955555556',
            'دمشق',
            33.51380000,
            36.27650000,
            'طب وجراحة الكلاب',
            'دمشق - المزة',
            'VET-2026-0002',
            '09:00 AM - 05:00 PM',
            10,
            'vet1.avif',
            true
        );

        // 3 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Lina Haddad',
            'vet3@platform.com',
            '955555557',
            'دمشق',
            33.52010000,
            36.28020000,
            'طب القطط',
            'دمشق - أبو رمانة',
            'VET-2026-0003',
            '10:00 AM - 06:00 PM',
            8,
            'vet1.avif',
            true
        );

        // 4 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Omar Al-Khatib',
            'vet4@platform.com',
            '955555558',
            'ريف دمشق',
            33.45000000,
            36.30000000,
            'الطوارئ البيطرية',
            'ريف دمشق - جرمانا',
            'VET-2026-0004',
            '08:00 AM - 04:00 PM',
            14,
            'vet1.avif',
            true
        );

        // 5 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Nour Al-Din Saleh',
            'vet5@platform.com',
            '955555559',
            'حمص',
            34.73240000,
            36.71370000,
            'الأمراض الباطنية للحيوانات',
            'حمص - الإنشاءات',
            'VET-2026-0005',
            '09:00 AM - 05:00 PM',
            11,
            'vet1.avif',
            true
        );

        // 6 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Rania Khalil',
            'vet6@platform.com',
            '955555560',
            'اللاذقية',
            35.53170000,
            35.79130000,
            'طب الحيوانات الصغيرة',
            'اللاذقية - مشروع الصليبة',
            'VET-2026-0006',
            '10:00 AM - 07:00 PM',
            7,
            'vet1.avif',
            true
        );

        // 7 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Samer Mustafa',
            'vet7@platform.com',
            '955555561',
            'طرطوس',
            34.89500000,
            35.88670000,
            'جراحة العظام البيطرية',
            'طرطوس - الكورنيش',
            'VET-2026-0007',
            '09:00 AM - 06:00 PM',
            13,
            'vet1.avif',
            true
        );

        // 8 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Maya Ibrahim',
            'vet8@platform.com',
            '955555562',
            'حلب',
            36.20000000,
            37.14000000,
            'طب الطيور والحيوانات الغريبة',
            'حلب - العزيزية',
            'VET-2026-0008',
            '10:00 AM - 08:00 PM',
            9,
            'vet1.avif',
            true
        );

        // 9 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Tarek Youssef',
            'vet9@platform.com',
            '955555563',
            'دمشق',
            33.51050000,
            36.29010000,
            'الطب البيطري الوقائي',
            'دمشق - الشعلان',
            'VET-2026-0009',
            '08:00 AM - 04:00 PM',
            15,
            'vet1.avif',
            true
        );

        // 10 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Salma Nasser',
            'vet10@platform.com',
            '955555564',
            'حمص',
            34.73000000,
            36.71000000,
            'التخدير والعناية المركزة',
            'حمص - باب السباع',
            'VET-2026-0010',
            '09:00 AM - 05:00 PM',
            12,
            'vet1.avif',
            true
        );

        // 11 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Basel Hamoud',
            'vet11@platform.com',
            '955555565',
            'دمشق',
            33.51800000,
            36.28500000,
            'طب وجراحة الحيوانات الأليفة',
            'دمشق - باب توما',
            'VET-2026-0011',
            '10:00 AM - 06:00 PM',
            6,
            'vet1.avif',
            true
        );

        // 12 - Active
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Dima Farhat',
            'vet12@platform.com',
            '955555566',
            'حلب',
            36.21000000,
            37.15000000,
            'الأمراض الجلدية البيطرية',
            'حلب - السليمانية',
            'VET-2026-0012',
            '09:00 AM - 07:00 PM',
            10,
            'vet1.avif',
            true
        );

        // 13 - Pending
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Khaled Darwish',
            'vet13@platform.com',
            '955555567',
            'دمشق',
            33.52500000,
            36.29000000,
            'جراحة الحيوانات',
            'دمشق - الميدان',
            'VET-2026-0013',
            '10:00 AM - 06:00 PM',
            5,
            'vet1.avif',
            false
        );

        // 14 - Pending
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Reem Abbas',
            'vet14@platform.com',
            '955555568',
            'حمص',
            34.73500000,
            36.72000000,
            'طب القطط والكلاب',
            'حمص - الوعر',
            'VET-2026-0014',
            '09:00 AM - 05:00 PM',
            4,
            'vet1.avif',
            false
        );

        // 15 - Pending
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Fadi Ahmad',
            'vet15@platform.com',
            '955555569',
            'اللاذقية',
            35.53000000,
            35.79000000,
            'طب الحيوانات الصغيرة',
            'اللاذقية - الرمل الجنوبي',
            'VET-2026-0015',
            '10:00 AM - 06:00 PM',
            3,
            'vet1.avif',
            false
        );

        // 16 - Pending
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Hala Mustafa',
            'vet16@platform.com',
            '955555570',
            'طرطوس',
            34.90000000,
            35.89000000,
            'الطب البيطري العام',
            'طرطوس - المدينة',
            'VET-2026-0016',
            '09:00 AM - 05:00 PM',
            4,
            'vet1.avif',
            false
        );

        // 17 - Pending
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Yazan Ali',
            'vet17@platform.com',
            '955555571',
            'حلب',
            36.20500000,
            37.13000000,
            'جراحة الطوارئ',
            'حلب - الحمدانية',
            'VET-2026-0017',
            '10:00 AM - 08:00 PM',
            5,
            'vet1.avif',
            false
        );

        // 18 - Pending
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Sara Hamdan',
            'vet18@platform.com',
            '955555572',
            'دمشق',
            33.51500000,
            36.27500000,
            'التغذية البيطرية',
            'دمشق - المالكي',
            'VET-2026-0018',
            '09:00 AM - 05:00 PM',
            2,
            'vet1.avif',
            false
        );

        // 19 - Pending
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Wissam Hassan',
            'vet19@platform.com',
            '955555573',
            'حمص',
            34.72800000,
            36.71500000,
            'الأمراض المعدية',
            'حمص - الدبلان',
            'VET-2026-0019',
            '10:00 AM - 06:00 PM',
            3,
            'vet1.avif',
            false
        );

        // 20 - Pending
        $this->createVeterinarian(
            $vetRole,
            $admin,
            'Dr. Jana Mahmoud',
            'vet20@platform.com',
            '955555574',
            'حلب',
            36.21500000,
            37.14500000,
            'طب وجراحة الحيوانات الأليفة',
            'حلب - الأشرفية',
            'VET-2026-0020',
            '10:00 AM - 07:00 PM',
            2,
            'vet1.avif',
            false
        );

        /*
        |--------------------------------------------------------------------------
        | 40 Volunteers
        |--------------------------------------------------------------------------
        */

        // 1
        $this->createVolunteer(
            $volunteerRole,
            $admin,
            'Ahmad Beginner Rescuer',
            'volunteer_beginner@platform.com',
            '944444444',
            'دمشق',
            33.51500000,
            36.28000000,
            'دمشق - القصاع - برج الروس',
            22,
            'field',
            'beginner',
            ['pet_carrier'],
            'volunteer1.avif',
            true
        );

        // 2
        $this->createVolunteer(
            $volunteerRole,
            $admin,
            'Mustafa Intermediate Rescuer',
            'volunteer_intermediate@platform.com',
            '988888888',
            'دمشق',
            33.51800000,
            36.28500000,
            'دمشق - باب توما - الشارع العام',
            27,
            'field',
            'intermediate',
            ['first_aid_kit', 'pet_carrier'],
            'volunteer2.avif',
            true
        );

        // 3
        $this->createVolunteer(
            $volunteerRole,
            $admin,
            'Khaled Advanced Rescuer',
            'volunteer_advanced@platform.com',
            '977777777',
            'دمشق',
            33.51200000,
            36.27200000,
            'دمشق - ساحة التحرير',
            32,
            'field',
            'advanced',
            ['first_aid_kit', 'pet_net', 'heavy_gloves'],
            'volunteer3.avif',
            true
        );

        // 4
        $this->createVolunteer($volunteerRole, $admin, 'Omar Field Rescuer', 'volunteer4@platform.com', '944444445', 'دمشق', 33.52000000, 36.28100000, 'دمشق - المزة', 24, 'field', 'beginner', ['pet_carrier'], 'volunteer1.avif', true);

        // 5
        $this->createVolunteer($volunteerRole, $admin, 'Samer Transport Volunteer', 'volunteer5@platform.com', '944444446', 'دمشق', 33.51600000, 36.29000000, 'دمشق - الشعلان', 29, 'transportation', 'intermediate', ['transport_vehicle', 'pet_carrier'], 'volunteer2.avif', true);

        // 6
        $this->createVolunteer($volunteerRole, $admin, 'Yousef Rescue Volunteer', 'volunteer6@platform.com', '944444447', 'دمشق', 33.51000000, 36.27800000, 'دمشق - كفرسوسة', 26, 'field', 'intermediate', ['first_aid_kit'], 'volunteer3.avif', true);

        // 7
        $this->createVolunteer($volunteerRole, $admin, 'Bilal Pet Rescuer', 'volunteer7@platform.com', '944444448', 'دمشق', 33.52200000, 36.28800000, 'دمشق - أبو رمانة', 31, 'field', 'advanced', ['first_aid_kit', 'pet_net'], 'volunteer1.avif', true);

        // 8
        $this->createVolunteer($volunteerRole, $admin, 'Hussein Volunteer', 'volunteer8@platform.com', '944444449', 'حمص', 34.73200000, 36.71400000, 'حمص - الإنشاءات', 25, 'field', 'beginner', ['pet_carrier'], 'volunteer2.avif', true);

        // 9
        $this->createVolunteer($volunteerRole, $admin, 'Maher Animal Helper', 'volunteer9@platform.com', '944444450', 'حمص', 34.73500000, 36.71800000, 'حمص - الوعر', 34, 'field', 'advanced', ['first_aid_kit', 'heavy_gloves'], 'volunteer3.avif', true);

        // 10
        $this->createVolunteer($volunteerRole, $admin, 'Tamer Rescue Team', 'volunteer10@platform.com', '944444451', 'حلب', 36.20500000, 37.14000000, 'حلب - العزيزية', 28, 'field', 'intermediate', ['pet_net'], 'volunteer1.avif', true);

        // 11
        $this->createVolunteer($volunteerRole, $admin, 'Rami Transport Helper', 'volunteer11@platform.com', '944444452', 'حلب', 36.21000000, 37.14500000, 'حلب - السليمانية', 30, 'transportation', 'intermediate', ['transport_vehicle'], 'volunteer2.avif', true);

        // 12
        $this->createVolunteer($volunteerRole, $admin, 'Firas Field Volunteer', 'volunteer12@platform.com', '944444453', 'اللاذقية', 35.53000000, 35.79000000, 'اللاذقية - الرمل', 23, 'field', 'beginner', ['pet_carrier'], 'volunteer3.avif', true);

        // 13
        $this->createVolunteer($volunteerRole, $admin, 'Wael Advanced Rescuer', 'volunteer13@platform.com', '944444454', 'اللاذقية', 35.53500000, 35.79500000, 'اللاذقية - مشروع الصليبة', 36, 'field', 'advanced', ['first_aid_kit', 'pet_net', 'heavy_gloves'], 'volunteer1.avif', true);

        // 14
        $this->createVolunteer($volunteerRole, $admin, 'Nabil Animal Volunteer', 'volunteer14@platform.com', '944444455', 'طرطوس', 34.89500000, 35.88500000, 'طرطوس - الكورنيش', 27, 'field', 'intermediate', ['first_aid_kit'], 'volunteer2.avif', true);

        // 15
        $this->createVolunteer($volunteerRole, $admin, 'Laith Rescuer', 'volunteer15@platform.com', '944444456', 'طرطوس', 34.90000000, 35.89000000, 'طرطوس - المدينة', 33, 'field', 'advanced', ['first_aid_kit', 'pet_net'], 'volunteer3.avif', true);

        // 16
        $this->createVolunteer($volunteerRole, $admin, 'Hadi Pet Helper', 'volunteer16@platform.com', '944444457', 'دمشق', 33.53000000, 36.29500000, 'دمشق - ركن الدين', 21, 'field', 'beginner', ['pet_carrier'], 'volunteer1.avif', true);

        // 17
        $this->createVolunteer($volunteerRole, $admin, 'Ziad Volunteer', 'volunteer17@platform.com', '944444458', 'دمشق', 33.52500000, 36.30000000, 'دمشق - القصاع', 26, 'photography', 'intermediate', ['camera'], 'volunteer2.avif', true);

        // 18
        $this->createVolunteer($volunteerRole, $admin, 'Karim Photographer', 'volunteer18@platform.com', '944444459', 'دمشق', 33.51800000, 36.30000000, 'دمشق - باب توما', 29, 'photography', 'advanced', ['camera', 'first_aid_kit'], 'volunteer3.avif', true);

        // 19
        $this->createVolunteer($volunteerRole, $admin, 'Adel Field Helper', 'volunteer19@platform.com', '944444460', 'دمشق', 33.50500000, 36.28000000, 'دمشق - الميدان', 35, 'field', 'advanced', ['heavy_gloves', 'pet_net'], 'volunteer1.avif', true);

        // 20
        $this->createVolunteer($volunteerRole, $admin, 'Marwan Rescue Volunteer', 'volunteer20@platform.com', '944444461', 'حمص', 34.74000000, 36.70000000, 'حمص - باب السباع', 24, 'field', 'beginner', ['pet_carrier'], 'volunteer2.avif', true);

        // 21
        $this->createVolunteer($volunteerRole, $admin, 'Shadi Volunteer', 'volunteer21@platform.com', '944444462', 'حمص', 34.72500000, 36.70500000, 'حمص - الدبلان', 31, 'field', 'intermediate', ['first_aid_kit'], 'volunteer3.avif', true);

        // 22
        $this->createVolunteer($volunteerRole, $admin, 'Ayman Transport Volunteer', 'volunteer22@platform.com', '944444463', 'حلب', 36.21500000, 37.15000000, 'حلب - الحمدانية', 37, 'transportation', 'advanced', ['transport_vehicle', 'pet_carrier'], 'volunteer1.avif', true);

        // 23
        $this->createVolunteer($volunteerRole, $admin, 'Rashid Animal Helper', 'volunteer23@platform.com', '944444464', 'حلب', 36.19500000, 37.12500000, 'حلب - الأشرفية', 28, 'field', 'intermediate', ['first_aid_kit', 'pet_net'], 'volunteer2.avif', true);

        // 24
        $this->createVolunteer($volunteerRole, $admin, 'Anas Rescuer', 'volunteer24@platform.com', '944444465', 'اللاذقية', 35.52500000, 35.78500000, 'اللاذقية - الصليبة', 25, 'field', 'beginner', ['pet_carrier'], 'volunteer3.avif', true);

        // 25
        $this->createVolunteer($volunteerRole, $admin, 'Bashar Advanced Volunteer', 'volunteer25@platform.com', '944444466', 'طرطوس', 34.90500000, 35.89500000, 'طرطوس - الكورنيش', 38, 'field', 'advanced', ['first_aid_kit', 'pet_net', 'heavy_gloves'], 'volunteer1.avif', true);

        // 26 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Nour Pending Volunteer', 'volunteer26@platform.com', '944444467', 'دمشق', 33.51400000, 36.27900000, 'دمشق - المزة', 22, 'field', 'beginner', ['pet_carrier'], 'volunteer2.avif', false);

        // 27 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Hassan Pending Volunteer', 'volunteer27@platform.com', '944444468', 'دمشق', 33.51900000, 36.28300000, 'دمشق - باب توما', 27, 'field', 'intermediate', ['first_aid_kit'], 'volunteer3.avif', false);

        // 28 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Fadi Pending Volunteer', 'volunteer28@platform.com', '944444469', 'دمشق', 33.51000000, 36.27500000, 'دمشق - كفرسوسة', 30, 'field', 'advanced', ['pet_net'], 'volunteer1.avif', false);

        // 29 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Sultan Pending Volunteer', 'volunteer29@platform.com', '944444470', 'حمص', 34.73300000, 36.71600000, 'حمص - الإنشاءات', 23, 'field', 'beginner', ['pet_carrier'], 'volunteer2.avif', false);

        // 30 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Yazan Pending Volunteer', 'volunteer30@platform.com', '944444471', 'حمص', 34.73600000, 36.72000000, 'حمص - الوعر', 32, 'transportation', 'intermediate', ['transport_vehicle'], 'volunteer3.avif', false);

        // 31 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Tareq Pending Volunteer', 'volunteer31@platform.com', '944444472', 'حلب', 36.20200000, 37.13800000, 'حلب - العزيزية', 26, 'field', 'intermediate', ['first_aid_kit'], 'volunteer1.avif', false);

        // 32 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Iyad Pending Volunteer', 'volunteer32@platform.com', '944444473', 'حلب', 36.20800000, 37.14200000, 'حلب - السليمانية', 34, 'field', 'advanced', ['heavy_gloves'], 'volunteer2.avif', false);

        // 33 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Majd Pending Volunteer', 'volunteer33@platform.com', '944444474', 'اللاذقية', 35.52800000, 35.78800000, 'اللاذقية - الرمل', 21, 'field', 'beginner', ['pet_carrier'], 'volunteer3.avif', false);

        // 34 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Kinan Pending Volunteer', 'volunteer34@platform.com', '944444475', 'اللاذقية', 35.53200000, 35.79200000, 'اللاذقية - الصليبة', 29, 'photography', 'intermediate', ['camera'], 'volunteer1.avif', false);

        // 35 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Rami Pending Volunteer', 'volunteer35@platform.com', '944444476', 'طرطوس', 34.89800000, 35.88800000, 'طرطوس - المدينة', 25, 'field', 'beginner', ['pet_carrier'], 'volunteer2.avif', false);

        // 36 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Nasser Pending Volunteer', 'volunteer36@platform.com', '944444477', 'طرطوس', 34.90200000, 35.89200000, 'طرطوس - الكورنيش', 36, 'field', 'advanced', ['first_aid_kit', 'pet_net'], 'volunteer3.avif', false);

        // 37 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Ali Pending Volunteer', 'volunteer37@platform.com', '944444478', 'دمشق', 33.52800000, 36.29000000, 'دمشق - ركن الدين', 24, 'field', 'intermediate', ['first_aid_kit'], 'volunteer1.avif', false);

        // 38 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Mazen Pending Volunteer', 'volunteer38@platform.com', '944444479', 'دمشق', 33.52400000, 36.29400000, 'دمشق - القصاع', 28, 'transportation', 'intermediate', ['transport_vehicle'], 'volunteer2.avif', false);

        // 39 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Hamza Pending Volunteer', 'volunteer39@platform.com', '944444480', 'حلب', 36.21800000, 37.14800000, 'حلب - الحمدانية', 33, 'field', 'advanced', ['heavy_gloves', 'pet_net'], 'volunteer3.avif', false);

        // 40 - Pending
        $this->createVolunteer($volunteerRole, $admin, 'Saeed Pending Volunteer', 'volunteer40@platform.com', '944444481', 'حمص', 34.72800000, 36.71200000, 'حمص - الدبلان', 20, 'field', 'beginner', ['pet_carrier'], 'volunteer1.avif', false);

        /*
        |--------------------------------------------------------------------------
        | 10 Regular Users
        |--------------------------------------------------------------------------
        */

        // 1
        $this->createRegularUser(
            $regularUserRole,
            'Mohamad Case Reporter',
            'user@platform.com',
            '966666666',
            'حمص',
            34.73240000,
            36.71370000,
            'SY',
            'user1.avif'
        );

        // 2
        $this->createRegularUser(
            $regularUserRole,
            'Lenar',
            'lili@platform.com',
            '15758083978',
            'حمص',
            34.73240000,
            36.71370000,
            'DE',
            'user2.avif'
        );

        // 3
        $this->createRegularUser(
            $regularUserRole,
            'Zain',
            'zain@platform.com',
            '15754083978',
            'دمشق',
            33.51380000,
            36.27650000,
            'DE',
            'user3.avif'
        );

        // 4
        $this->createRegularUser(
            $regularUserRole,
            'Louna',
            'lounaaa@platform.com',
            '5677803978',
            'الامارات',
            25.20480000,
            55.27080000,
            'AE',
            'user4.avif'
        );

        // 5
        $this->createRegularUser(
            $regularUserRole,
            'Rama',
            'rama@platform.com',
            '966666667',
            'دمشق',
            33.52000000,
            36.28000000,
            'SY',
            'user1.avif'
        );

        // 6
        $this->createRegularUser(
            $regularUserRole,
            'Kareem',
            'kareem@platform.com',
            '966666668',
            'حلب',
            36.20200000,
            37.13400000,
            'SY',
            'user2.avif'
        );

        // 7
        $this->createRegularUser(
            $regularUserRole,
            'Maya',
            'maya@platform.com',
            '966666669',
            'اللاذقية',
            35.53170000,
            35.79130000,
            'SY',
            'user3.avif'
        );

        // 8
        $this->createRegularUser(
            $regularUserRole,
            'Tala',
            'tala@platform.com',
            '966666670',
            'طرطوس',
            34.89500000,
            35.88670000,
            'SY',
            'user4.avif'
        );

        // 9
        $this->createRegularUser(
            $regularUserRole,
            'Omar',
            'omar@platform.com',
            '966666671',
            'حمص',
            34.73000000,
            36.71000000,
            'SY',
            'user1.avif'
        );

        // 10
        $this->createRegularUser(
            $regularUserRole,
            'Sally',
            'sally@platform.com',
            '966666672',
            'دمشق',
            33.51500000,
            36.28500000,
            'SY',
            'user2.avif'
        );

        $this->command->info('✅ Seeded 1 admin + 20 veterinarians + 40 volunteers + 10 regular users successfully!');
    }

    /*
    |--------------------------------------------------------------------------
    | Veterinarian Helper
    |--------------------------------------------------------------------------
    */

    private function createVeterinarian(
        $role,
        User $admin,
        string $name,
        string $email,
        string $phone,
        string $governorate,
        float $latitude,
        float $longitude,
        string $specialization,
        string $clinicLocation,
        string $licenseNumber,
        string $workingHours,
        int $experienceYears,
        string $photo,
        bool $approved
    ): void {
        $user = User::create([
            'full_name'          => $name,
            'email'              => $email,
            'password'           => Hash::make('Vet@1234'),
            'photo'              => $this->storeUserPhoto($photo, $photo),
            'country_code'       => 'SY',
            'phone_number'       => $phone,
            'governorate'        => $governorate,
            'latitude'           => $latitude,
            'longitude'          => $longitude,
            'account_status'     => $approved ? 'active' : 'pending',
            'email_verified_at'  => $approved ? now() : null,
            'two_factor_enabled' => $approved,
        ]);

        $user->assignRole($role);

        DB::table('veterinarians')->insert([
            'user_id'          => $user->id,
            'specialization'   => $specialization,
            'clinic_location'  => $clinicLocation,
            'license_number'   => $licenseNumber,
            'working_hours'    => $workingHours,
            'experience_years' => $experienceYears,
            'about'            => null,
            'bio'              => null,
            'is_approved'      => $approved,
            'approved_at'      => $approved ? now() : null,
            'approved_by'      => $approved ? $admin->id : null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Volunteer Helper
    |--------------------------------------------------------------------------
    */

    private function createVolunteer(
        $role,
        User $admin,
        string $name,
        string $email,
        string $phone,
        string $governorate,
        float $latitude,
        float $longitude,
        string $address,
        int $age,
        string $volType,
        string $experienceLevel,
        array $equipment,
        string $photo,
        bool $approved
    ): void {
        $user = User::create([
            'full_name'          => $name,
            'email'              => $email,
            'password'           => Hash::make('Volunteer@1234'),
            'photo'              => $this->storeUserPhoto($photo, $photo),
            'country_code'       => 'SY',
            'phone_number'       => $phone,
            'governorate'        => $governorate,
            'latitude'           => $latitude,
            'longitude'          => $longitude,
            'account_status'     => $approved ? 'active' : 'pending',
            'email_verified_at'  => $approved ? now() : null,
            'two_factor_enabled' => $approved,
        ]);

        $user->assignRole($role);

        DB::table('volunteers')->insert([
            'user_id'           => $user->id,
            'detailed_address'  => $address,
            'age'               => $age,
            'vol_type'          => $volType,
            'experience_level'  => $experienceLevel,
            'equipment'         => json_encode($equipment),
            'current_latitude'  => $latitude,
            'current_longitude' => $longitude,
            'is_approved'       => $approved,
            'approved_at'       => $approved ? now() : null,
            'approved_by'       => $approved ? $admin->id : null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Regular User Helper
    |--------------------------------------------------------------------------
    */

    private function createRegularUser(
        $role,
        string $name,
        string $email,
        string $phone,
        string $governorate,
        float $latitude,
        float $longitude,
        string $countryCode,
        string $photo
    ): void {
        $user = User::create([
            'full_name'          => $name,
            'email'              => $email,
            'password'           => Hash::make('User@1234'),
            'photo'              => $this->storeUserPhoto($photo, $photo),
            'country_code'       => $countryCode,
            'phone_number'       => $phone,
            'governorate'        => $governorate,
            'latitude'           => $latitude,
            'longitude'          => $longitude,
            'account_status'     => 'active',
            'email_verified_at'  => now(),
            'two_factor_enabled' => true,
        ]);

        $user->assignRole($role);

        DB::table('regular_users')->insert([
            'user_id'       => $user->id,
            'country_code'  => $countryCode,
            'phone_number'  => $phone,
            'governorate'   => $governorate,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store User Photo
    |--------------------------------------------------------------------------
    */

    private function storeUserPhoto(
        string $sourceFileName,
        string $targetFileName
    ): ?string {
        $sourcePath = database_path(
            'seeders/assets/users/' . $sourceFileName
        );

        if (!File::exists($sourcePath)) {
            $this->command?->warn(
                "Photo not found: {$sourceFileName}"
            );

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

