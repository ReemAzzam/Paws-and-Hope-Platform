<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animal_updates', function (Blueprint $table) {
            $table->id();
            
            // ربط التحديث بالحيوان مع الحذف التلقائي عند حذف الحيوان
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            
            // جعل العنوان اختياري لأن الطبيب قد يكتفي بكتابة الملاحظة الطبية مباشرة
            $table->string('title')->nullable();                  
            $table->text('content');               
            $table->string('media_url')->nullable();  
            $table->enum('type', ['health', 'media', 'general'])->default('general');
            
            $table->timestamps();                 

            // تحسين الأداء: إضافة فهرس مركب للاستعلام بسرعة عن تايم لاين حيوان معين مرتباً زمنياً
            $table->index(['animal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_updates');
    }
};