<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE user_block_logs MODIFY COLUMN acao ENUM(
            'bloqueio_temporario',
            'bloqueio_definitivo',
            'desbloqueio',
            'bloqueio_falta'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE user_block_logs MODIFY COLUMN acao ENUM(
            'bloqueio_temporario',
            'bloqueio_definitivo',
            'desbloqueio'
        ) NOT NULL");
    }
};
