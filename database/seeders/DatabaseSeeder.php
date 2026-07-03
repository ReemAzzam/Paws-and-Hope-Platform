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
            UserRoleSeeder::class,  // 3. توزيع المتطوعين وباقي المستخدمين والربط بالأدمن
            AnimalSeeder::class,        // 4. رفع الحيوانات التجريبية وصورها
            VaccinationSeeder::class,
            BehavioralAttributeSeeder::class,
            AnimalMedicalConditionSeeder::class, // 5. رفع الحالات الطبية التجريبية
            QuizQuestionsSeeder::class, // 5. تجهيز بنك أسئلة الكويز
            PostCategorySeeder::class,  // 6. تحميل تصنيفات منشوارات المجتمع
            LostFoundSeeder::class,      // 7. رفع منشورات Lost & Found التجريبية

        ]);

        /*User::factory()->create([
            'full_name'      => 'Test User',
            'email'          => 'test@example.com',
            'account_status' => 'active',
        ]);*/
    }
}
