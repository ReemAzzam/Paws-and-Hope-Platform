<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->enum('type', ['dog', 'cat', 'bird', 'rabbit', 'other']);
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->integer('age')->nullable();
            $table->string('size')->nullable();
            $table->decimal('weight', 5, 2)->nullable();

            $table->text('description')->nullable();
            $table->text('story')->nullable();

            $table->enum('health_status', ['healthy', 'injured', 'critical', 'recovering'])
                  ->default('healthy');

            $table->enum('availability_status', [
                'available', 'pending', 'adopted', 'sponsored', 'under_treatment'
            ])->default('available');

            $table->boolean('is_vaccinated')->default(false);
            $table->boolean('is_neutered')->default(false);
            $table->boolean('is_urgent')->default(false);

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->foreignId('vet_id')->nullable()->constrained('veterinarians')->nullOnDelete();

            // تم تعديله مؤقتاً بدون constrained
            $table->unsignedBigInteger('rescue_report_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('animals');
    }
};
