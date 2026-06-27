<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lost_found_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lost_found_id')->constrained('lost_found')->onDelete('cascade');
            $table->string('photo_url');
            $table->boolean('is_main')->default(false);
            $table->integer('order_number')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lost_found_photos');
    }
};
