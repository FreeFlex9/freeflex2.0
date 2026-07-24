<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_block_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['prestador', 'empresa']);
            $table->unsignedBigInteger('usuario_id');
            $table->string('nome');
            $table->string('email');
            $table->enum('acao', ['bloqueio_temporario', 'bloqueio_definitivo', 'desbloqueio']);
            $table->text('motivo')->nullable();
            $table->timestamp('blocked_until')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_block_logs');
    }
};
