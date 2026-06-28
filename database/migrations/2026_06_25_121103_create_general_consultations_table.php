<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_consultations', function (Blueprint $table) {
            $table->id();
            // ربط الاستشارة بالمستخدم العادي الذي سأل
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // ربط اختياري بطبيب محدد (لو ترك فارغاً null يعني السؤال موجه لأي طبيب متاح)
            $table->foreignId('veterinarian_id')->nullable()->constrained('veterinarians')->onDelete('set null');

            $table->text('question'); // نص السؤال
            $table->text('answer')->nullable(); // جواب الطبيب البيطري

            $table->enum('status', ['pending', 'answered'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_consultations');
    }
};