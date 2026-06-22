<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('regular_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            $table->string('country_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('governorate')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('regular_users');
    }
};
