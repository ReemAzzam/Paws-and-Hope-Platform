<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsorship_payments', function (Blueprint $table) {
            $table->id();
            // ربط الدفعة بعقد الكفالة الأساسي
            $table->foreignId('sponsorship_id')->constrained('sponsorships')->onDelete('cascade');
            
            $table->decimal('amount', 10, 2); // المبلغ المدفوع في هذه الدفعة
            $table->string('payment_method'); // الهرم، الفؤاد، سيرياتيل كاش...
            $table->string('transaction_number')->unique(); // رقم الحوالة (MTCN) فريد لمنع التلاعب
            $table->string('receipt_image_url'); // رابط صورة الإيصال المرفوعة
            
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null'); // الآدمين الذي وافق عليها
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable(); // في حال الرفض
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsorship_payments');
    }
};