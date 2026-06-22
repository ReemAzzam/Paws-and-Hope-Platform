<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // بيانات الاتصال الأساسية المشتركة لكل الفئات
            $table->string('country_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('governorate')->nullable();

            // الموقع الجغرافي الثابت للحساب (السكن / المدينة)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->enum('account_status', ['pending', 'active', 'suspended', 'rejected'])->default('pending');
            $table->boolean('two_factor_enabled')->default(false);

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['country_code', 'phone_number']);
            $table->index('governorate');
            $table->index(['latitude', 'longitude']);
            $table->index('account_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};