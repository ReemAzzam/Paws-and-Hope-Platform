<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescue_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rescue_report_id')->constrained('rescue_reports')->onDelete('cascade');
            $table->foreignId('volunteer_id')->constrained('volunteers')->onDelete('cascade');
            $table->foreignId('veterinarian_id')->nullable()->constrained('veterinarians')->onDelete('set null');

            $table->text('question')->comment('سؤال المتطوع أو توصيف الحالة الطارئة');
            $table->text('medical_advice')->nullable()->comment('توجيهات الطبيب الإسعافية الفورية');

            $table->enum('status', ['pending', 'answered'])->default('pending');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescue_consultations');
    }
};