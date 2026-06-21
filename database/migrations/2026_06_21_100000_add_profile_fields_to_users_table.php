<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf', 11)->nullable()->unique()->after('role');
            $table->string('cnpj', 14)->nullable()->unique()->after('cpf');
            $table->string('nome_fantasia')->nullable()->after('cnpj');
            $table->string('telefone', 15)->nullable()->after('nome_fantasia');
            $table->string('cep', 8)->nullable()->after('telefone');
            $table->string('logradouro')->nullable()->after('cep');
            $table->string('numero', 20)->nullable()->after('logradouro');
            $table->string('complemento', 100)->nullable()->after('numero');
            $table->string('bairro', 100)->nullable()->after('complemento');
            $table->string('cidade', 100)->nullable()->after('bairro');
            $table->string('estado', 2)->nullable()->after('cidade');
            $table->boolean('is_mei')->default(false)->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cpf', 'cnpj', 'nome_fantasia', 'telefone',
                'cep', 'logradouro', 'numero', 'complemento',
                'bairro', 'cidade', 'estado', 'is_mei',
            ]);
        });
    }
};
