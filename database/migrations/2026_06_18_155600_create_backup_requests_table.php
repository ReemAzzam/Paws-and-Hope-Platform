<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('backup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rescue_report_id')->constrained('rescue_reports')->onDelete('cascade');
            $table->foreignId('volunteer_id')->constrained('volunteers')->onDelete('cascade');
            
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            $table->enum('urgency_level', ['low', 'medium', 'high', 'critical'])->default('high');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'responded', 'cancelled'])->default('pending');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('backup_requests');
    }
};