<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->timestamp('application_blocked_until')->nullable()->after('blocked_by_admin_id');
            $table->text('application_block_reason')->nullable()->after('application_blocked_until');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['application_blocked_until', 'application_block_reason']);
        });
    }
};
