<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_matching_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('adoption_application_id')->nullable()->constrained()->onDelete('set null');

            // الإجابات من الـ Quiz (مطابقة للكونترولر)
            $table->string('preferred_animal_type');
            $table->string('preferred_age');
            $table->string('preferred_size');
            $table->string('housing_type');
            $table->string('activity_level');
            $table->integer('hours_alone_daily')->nullable();
            $table->string('children_status');
            $table->string('preferred_personality')->nullable();
            $table->boolean('has_other_pets')->default(false);
            $table->boolean('long_term_commitment')->default(true);

            // نتائج المطابقة
            $table->json('matching_results')->nullable();
            $table->integer('highest_score')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_matching_preferences');
    }
};
