<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('smtp_host')->default('');
            $table->unsignedSmallInteger('smtp_port')->default(587);
            $table->string('smtp_secure', 10)->default('tls');
            $table->string('smtp_username')->default('');
            $table->string('smtp_password', 512)->default('');
            $table->string('email_from')->default('');
            $table->string('email_from_name')->default('FreeFlex Notificações');
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        DB::table('smtp_settings')->insert(['id' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
