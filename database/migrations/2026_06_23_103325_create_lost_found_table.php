<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lost_found', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->enum('post_type', ['lost', 'found']);
            $table->enum('animal_type', ['dog', 'cat', 'bird', 'rabbit', 'other']);

            // الحقول الجديدة
            $table->string('name')->nullable();
            $table->string('breed')->nullable();
            $table->enum('gender', ['male', 'female', 'unknown'])->nullable();
            $table->enum('size', ['small', 'medium', 'large'])->nullable();
            $table->string('age')->nullable();
            $table->string('color')->nullable();

            $table->text('description');
            $table->text('location_description');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);

            $table->string('contact_phone')->nullable();
            $table->string('image_url')->nullable();

            $table->text('distinctive_marks')->nullable();
            $table->string('collar_tags')->nullable();
            $table->boolean('microchipped')->default(false);
            $table->boolean('neutered')->default(false);
            $table->string('temperament')->nullable();

            $table->integer('views')->default(0);
            $table->enum('status', ['open', 'resolved', 'closed'])->default('open');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lost_found');
    }
};
