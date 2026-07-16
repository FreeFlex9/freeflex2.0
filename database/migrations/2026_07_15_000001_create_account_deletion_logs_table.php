<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['prestador', 'empresa']);
            $table->unsignedBigInteger('usuario_id');
            $table->string('nome');
            $table->string('email');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('deleted_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletion_logs');
    }
};
