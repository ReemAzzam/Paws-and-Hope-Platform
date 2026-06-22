<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lost_found', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->enum('post_type', ['lost', 'found']);
            $table->enum('animal_type', ['dog', 'cat', 'bird', 'other']);
            $table->text('description');
            $table->string('location_description');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('status', ['open', 'resolved', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lost_found');
    }
};
