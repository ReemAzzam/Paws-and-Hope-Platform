<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('animal_photos', function (Blueprint $table) {
            $table->id();
            
            // الطريقة الأفضل والأسرع للربط في لارافل
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            
            $table->string('photo_url');
            $table->boolean('is_main')->default(false);
            $table->integer('order_number')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('animal_photos');
    }
};