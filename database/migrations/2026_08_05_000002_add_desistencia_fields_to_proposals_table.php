<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('status');
            $table->timestamp('withdrawn_at')->nullable()->after('accepted_at');
        });

        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM(
            'pending',
            'pending_company_accept',
            'pending_admin_approval',
            'accepted',
            'rejected',
            'rejected_admin',
            'rejected_provider',
            'withdrawn_by_provider'
        ) DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM(
            'pending',
            'pending_company_accept',
            'pending_admin_approval',
            'accepted',
            'rejected',
            'rejected_admin',
            'rejected_provider'
        ) DEFAULT 'pending'");

        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['accepted_at', 'withdrawn_at']);
        });
    }
};
