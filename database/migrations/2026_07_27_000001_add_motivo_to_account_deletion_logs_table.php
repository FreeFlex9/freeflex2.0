<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('account_deletion_logs', function (Blueprint $table) {
            $table->string('motivo')->nullable()->after('admin_id');
            $table->text('motivo_detalhes')->nullable()->after('motivo');
        });
    }

    public function down(): void
    {
        Schema::table('account_deletion_logs', function (Blueprint $table) {
            $table->dropColumn(['motivo', 'motivo_detalhes']);
        });
    }
};
