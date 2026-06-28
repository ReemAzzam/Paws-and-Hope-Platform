<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('awareness_posts', function (Blueprint $table) {
            $table->id();
            // ربط المنشور بالطبيب البيطري (عند حذف الطبيب يتم حذف منشوراته آلياً)
            $table->foreignId('veterinarian_id')->constrained('veterinarians')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('image_url')->nullable(); // الصورة التوضيحية للمنشور
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('awareness_posts');
    }
};