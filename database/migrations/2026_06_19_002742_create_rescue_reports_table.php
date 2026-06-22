<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('rescue_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('animal_id')->nullable()->constrained('animals')->nullOnDelete();
            $table->foreignId('assigned_volunteer_id')->nullable()->constrained('volunteers')->nullOnDelete();

            $table->enum('animal_type', ['dog', 'cat', 'bird', 'other']);
            $table->text('approximate_condition');
            $table->string('image_url')->nullable();

            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            $table->enum('severity_level', ['simple', 'intermediate', 'critical']);
            $table->enum('current_status', [
                'reported', 'dispatched', 'on_site', 'in_clinic', 'resolved'
            ])->default('reported');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rescue_reports');
    }
};
