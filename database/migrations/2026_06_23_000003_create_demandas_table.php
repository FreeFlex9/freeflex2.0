<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->restrictOnDelete();
            $table->date('data');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->string('local')->nullable();
            $table->text('descricao')->nullable();
            $table->unsignedSmallInteger('quantidade_necessaria')->default(1);
            $table->unsignedSmallInteger('quantidade_confirmada')->default(0);
            $table->decimal('valor_total', 10, 2)->nullable();
            $table->enum('status', [
                'aberta',
                'pendente_aprovacao_admin',
                'pendente_aceite_prestador',
                'parcialmente_agendada',
                'agendada',
                'concluida',
                'cancelada',
                'rejeitada_admin',
                'rejeitada_prestador',
            ])->default('aberta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandas');
    }
};
