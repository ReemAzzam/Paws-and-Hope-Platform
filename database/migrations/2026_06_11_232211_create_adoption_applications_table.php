<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('adoption_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('animal_id')->constrained();

            $table->text('application_details');
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_trial'])->default('pending');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adoption_applications');
    }
};
