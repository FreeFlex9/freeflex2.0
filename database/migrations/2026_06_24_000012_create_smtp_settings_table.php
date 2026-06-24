<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('smtp_host')->default('');
            $table->unsignedSmallInteger('smtp_port')->default(587);
            $table->enum('smtp_secure', ['tls', 'ssl'])->default('tls');
            $table->string('smtp_username')->default('');
            $table->string('smtp_password')->default('');
            $table->string('from_email')->default('');
            $table->string('from_name')->default('FreeFlex');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
