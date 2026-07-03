<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('animal_medical_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');

            $table->string('condition');           // اسم المرض أو الحالة (Ear Mites)
            $table->text('treatment')->nullable(); // العلاج
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();  // إذا انتهى العلاج
            $table->text('notes')->nullable();     // ملاحظات إضافية

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('animal_medical_conditions');
    }
};
