<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('adoption_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');

            $table->text('reason_for_adoption');
            $table->boolean('has_other_pets')->default(false);
            $table->text('other_pets_info')->nullable();
            $table->enum('housing_type', ['house', 'apartment', 'villa']);
            $table->boolean('has_garden')->default(false);
            $table->integer('family_members_count');
            $table->boolean('children_under_10')->default(false);
            $table->text('work_schedule');
            $table->text('experience_with_animals');
            $table->boolean('commitment_declaration')->default(false);
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');

            $table->enum('status', ['pending', 'approved', 'rejected', 'in_trial'])
                  ->default('pending');

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adoption_applications');
    }
};
