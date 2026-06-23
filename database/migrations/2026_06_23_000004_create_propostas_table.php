<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propostas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demanda_id')->constrained('demandas')->cascadeOnDelete();
            $table->foreignId('prestador_id')->constrained('users')->cascadeOnDelete();
            $table->text('mensagem')->nullable();
            $table->decimal('valor_total', 10, 2)->nullable();
            $table->enum('status', [
                'pendente',
                'pendente_aprovacao_admin',
                'aceita',
                'rejeitada_admin',
                'rejeitada_prestador',
            ])->default('pendente');
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propostas');
    }
};
