<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 12, 2);
            // إضافة حقل العملة (USD أو SYP)
            $table->enum('currency', ['SYP', 'USD'])->default('SYP'); 
            // توسيع خيارات البوابات لتشمل الحوالات الخارجية والدفع اليدوي
            $table->enum('gateway_type', [
                'al_haram', 
                'al_fouad', 
                'syriatel_cash', 
                'mtn_cash', 
                'western_union', 
                'paypal', 
                'gofundme', 
                'hand_delivery',
                'external'
            ]); 
            $table->string('transaction_number')->nullable()->unique(); // إمكانية القبول كـ nullable في حال التسليم اليدوي
            $table->string('receipt_image_path')->nullable(); 
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable(); 
            $table->boolean('is_anonymous')->default(false); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};