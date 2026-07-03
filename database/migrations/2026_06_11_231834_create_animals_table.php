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
        $table->enum('gender', ['male', 'female', 'unknown'])->default('unknown');
        $table->integer('age')->nullable();
        $table->enum('size', ['small', 'medium', 'large'])->nullable();
        $table->decimal('weight', 5, 2)->nullable();

        $table->text('description')->nullable();
        $table->text('story')->nullable();

        $table->enum('health_status', ['healthy', 'sick', 'injured', 'critical', 'recovering'])->default('healthy');
        $table->boolean('is_vaccinated')->default(false);
        $table->boolean('is_neutered')->default(false);

        $table->enum('availability_status', ['available', 'pending', 'adopted', 'sponsored', 'under_treatment'])->default('under_treatment');
        $table->boolean('is_urgent')->default(false);

        $table->decimal('latitude', 10, 8)->nullable();
        $table->decimal('longitude', 11, 8)->nullable();

        $table->unsignedBigInteger('vet_id')->nullable();
        $table->unsignedBigInteger('rescue_report_id')->nullable();

        $table->timestamps();
        $table->softDeletes();
    });

    Schema::table('animals', function (Blueprint $table) {
        if (Schema::hasTable('veterinarians')) {
            $table->foreign('vet_id')->references('id')->on('veterinarians')->nullOnDelete();
        }
        if (Schema::hasTable('rescue_reports')) {
            $table->foreign('rescue_report_id')->references('id')->on('rescue_reports')->nullOnDelete();
        }
    });
}

    public function down()
    {
        Schema::dropIfExists('animals');
    }
};
