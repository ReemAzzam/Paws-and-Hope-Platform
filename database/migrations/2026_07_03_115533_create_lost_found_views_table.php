<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('lost_found_views', function (Blueprint $table) {

    $table->id();

    $table->foreignId('lost_found_id')
          ->constrained('lost_found')->constrained()
          ->cascadeOnDelete();

    $table->foreignId('user_id')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();

    $table->string('ip_address')->nullable();

    $table->timestamps();

    $table->unique(['lost_found_id', 'user_id']);
    $table->unique(['lost_found_id', 'ip_address']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_found_views');
    }
};
