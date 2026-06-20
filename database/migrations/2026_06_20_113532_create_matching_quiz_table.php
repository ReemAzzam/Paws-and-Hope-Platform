<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('matching_quiz', function (Blueprint $table) {
            $table->id();
            $table->integer('step_id');
            $table->integer('question_order');
            $table->text('question_text');
            $table->json('options');
            $table->string('key');                    // مهم جداً للـ Frontend
            $table->text('hint')->nullable();
            $table->string('type')->default('single'); // single or multiple
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('matching_quiz');
    }
};
