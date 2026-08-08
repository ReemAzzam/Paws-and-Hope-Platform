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
            $table->foreignId('sponsorship_id')->constrained('sponsorships')->onDelete('cascade');
            
            $table->decimal('amount', 10, 2);
            $table->enum('currency', ['SYP', 'USD'])->default('SYP');
            $table->enum('payment_method', [
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
            $table->string('transaction_number')->unique(); 
            $table->string('receipt_image_url');
            
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null'); // الآدمين الذي وافق عليها
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsorship_payments');
    }
};