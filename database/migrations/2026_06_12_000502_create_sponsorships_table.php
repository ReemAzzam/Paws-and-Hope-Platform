<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sponsorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('animal_id')->constrained();

            $table->enum('sponsorship_level', ['food', 'medical', 'full']);
            $table->decimal('monthly_amount', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->enum('status', ['active', 'paused', 'ended'])->default('active');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sponsorships');
    }
};
