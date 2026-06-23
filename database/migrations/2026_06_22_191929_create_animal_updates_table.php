<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animal_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            $table->string('title');                  
            $table->text('content');               
            $table->string('media_url')->nullable();  
            $table->enum('type', ['health', 'media', 'general'])->default('general');
            
            $table->timestamps();                 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_updates');
    }
};