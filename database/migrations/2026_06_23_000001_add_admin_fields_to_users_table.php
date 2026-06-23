<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alterar o enum role para incluir 'admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('prestador','empresa','admin') NOT NULL DEFAULT 'prestador'");

        Schema::table('users', function (Blueprint $table) {
            // Campos de aprovação (empresa e prestador)
            $table->enum('status_aprovacao', ['pendente', 'aprovado', 'rejeitado'])->default('pendente')->after('is_mei');
            $table->text('motivo_rejeicao')->nullable()->after('status_aprovacao');

            // Campos extras de prestador
            $table->boolean('possui_cnh')->default(false)->after('motivo_rejeicao');
            $table->boolean('cnh_digital')->default(false)->after('possui_cnh');
            $table->string('numero_cnh', 20)->nullable()->after('cnh_digital');
            $table->string('cnpj_mei', 14)->nullable()->after('numero_cnh');

            // Caminhos de documentos
            $table->string('cartao_cnpj_path', 500)->nullable()->after('cnpj_mei');
            $table->string('cnh_frente_path', 500)->nullable()->after('cartao_cnpj_path');
            $table->string('cnh_verso_path', 500)->nullable()->after('cnh_frente_path');
            $table->string('rg_frente_path', 500)->nullable()->after('cnh_verso_path');
            $table->string('rg_verso_path', 500)->nullable()->after('rg_frente_path');
            $table->string('ccmei_path', 500)->nullable()->after('rg_verso_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status_aprovacao', 'motivo_rejeicao',
                'possui_cnh', 'cnh_digital', 'numero_cnh', 'cnpj_mei',
                'cartao_cnpj_path', 'cnh_frente_path', 'cnh_verso_path',
                'rg_frente_path', 'rg_verso_path', 'ccmei_path',
            ]);
        });
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('prestador','empresa') NOT NULL DEFAULT 'prestador'");
    }
};
