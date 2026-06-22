<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            
            $table->string('country_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('detailed_address')->nullable();
            $table->integer('age')->nullable();

            // Volunteer Type
            $table->enum('vol_type', ['field', 'photography', 'transportation', 'other'])->nullable();

            // Experience
            $table->enum('experience_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');

            // Equipment (JSON)
            $table->json('equipment')->nullable();

            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();

            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['current_latitude', 'current_longitude']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('volunteers');
    }
};