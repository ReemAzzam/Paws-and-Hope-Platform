<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('awareness_post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('awareness_post_id')->constrained('awareness_posts')->onDelete('cascade');
            $table->timestamps();

            // منع تكرار اللايك (اليوزر يعمل لايك واحد فقط للبوست الواحد)
            $table->unique(['user_id', 'awareness_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awareness_post_likes');
    }
};
