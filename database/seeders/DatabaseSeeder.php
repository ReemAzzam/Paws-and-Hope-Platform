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
            RoleSeeder::class,          // 1. تثبيت الأدوار الموحدة أولاً
            SuperAdminSeeder::class,    // 2. إنشاء حساب الآدمن الفردي والأساسي
            UserRoleSeeder::class,      // 3. توزيع المتطوعين وباقي المستخدمين والربط بالأدمن
            AnimalSeeder::class,        // 4. رفع الحيوانات التجريبية وصورها
            QuizQuestionsSeeder::class, // 5. تجهيز بنك أسئلة الكويز
            PostCategorySeeder::class,  // 6. تحميل تصنيفات منشوارات المجتمع
        ]);

        /*User::factory()->create([
            'full_name'      => 'Test User',
            'email'          => 'test@example.com',
            'account_status' => 'active',
        ]);*/
    }
}