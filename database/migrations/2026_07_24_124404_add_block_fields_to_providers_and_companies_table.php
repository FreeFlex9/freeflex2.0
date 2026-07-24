<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['providers', 'companies'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamp('blocked_at')->nullable()->after('active');
                $table->timestamp('blocked_until')->nullable()->after('blocked_at');
                $table->text('block_reason')->nullable()->after('blocked_until');
                $table->foreignId('blocked_by_admin_id')->nullable()->after('block_reason')
                    ->constrained('admins')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['providers', 'companies'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('blocked_by_admin_id');
                $table->dropColumn(['blocked_at', 'blocked_until', 'block_reason']);
            });
        }
    }
};
