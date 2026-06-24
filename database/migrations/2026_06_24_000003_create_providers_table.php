<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpf', 14)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->boolean('has_license')->default(false);
            $table->boolean('is_digital_license')->default(false);
            $table->string('license_number')->nullable();
            $table->string('license_front_path')->nullable();
            $table->string('license_back_path')->nullable();
            $table->string('rg_front_path')->nullable();
            $table->string('rg_back_path')->nullable();
            $table->string('mei_cnpj', 18)->nullable();
            $table->string('ccmei_path')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
