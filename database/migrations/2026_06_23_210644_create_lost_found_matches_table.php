<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lost_found_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lost_post_id')->constrained('lost_found')->onDelete('cascade');
            $table->foreignId('found_post_id')->constrained('lost_found')->onDelete('cascade');

            $table->integer('match_score')->unsigned();
            $table->json('match_reasons')->nullable();

            $table->enum('status', ['pending', 'contacted', 'resolved', 'dismissed'])
                  ->default('pending');

            $table->timestamp('notified_at')->nullable();

            $table->timestamps();

            $table->unique(['lost_post_id', 'found_post_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lost_found_matches');
    }
};
