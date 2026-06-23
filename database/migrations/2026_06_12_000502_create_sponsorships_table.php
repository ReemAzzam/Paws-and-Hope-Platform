<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sponsorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
            
            // المبلغ الشهري الثابت للكفالة الكاملة
            $table->decimal('monthly_amount', 10, 2); 
            // حالة الكفالة (انتظار، نشطة، ملغاة، موقوفة مؤقتاً)
            $table->enum('status', ['pending', 'active', 'cancelled', 'paused'])->default('pending'); 
            
            $table->date('start_date')->nullable();
            $table->date('next_payment_due')->nullable(); // لمتابعة شرط الـ 45 يوماً للتخلف عن الدفع
            $table->text('notes')->nullable();
            $table->timestamps();
        });
            }

    public function down()
    {
        Schema::dropIfExists('sponsorships');
    }
};
