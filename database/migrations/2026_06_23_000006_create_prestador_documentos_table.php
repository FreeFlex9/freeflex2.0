<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestador_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestador_id')->constrained('users')->cascadeOnDelete();
            $table->string('nome_arquivo');
            $table->string('caminho_arquivo', 500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestador_documentos');
    }
};
