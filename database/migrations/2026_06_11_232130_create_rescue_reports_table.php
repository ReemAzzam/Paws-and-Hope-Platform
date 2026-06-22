<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rescue_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('volunteer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('location_address')->nullable();
            $table->enum('severity_level', ['normal', 'urgent', 'critical'])->default('normal');
            $table->string('animal_type');
            $table->enum('health_status', ['bleeding', 'fracture', 'poisoning', 'other']);
            $table->text('description')->nullable();
            $table->enum('status', ['reported', 'dispatched', 'on_site', 'in_clinic', 'resolved'])->default('reported');
            $table->timestamps();
        });

        Schema::create('rescue_report_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rescue_report_id')->constrained('rescue_reports')->onDelete('cascade');
            $table->string('image_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescue_report_images');
        Schema::dropIfExists('rescue_reports');
    }
};
