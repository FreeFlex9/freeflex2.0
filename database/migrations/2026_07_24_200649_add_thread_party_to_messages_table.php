<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->enum('thread_party_type', ['provider', 'company'])->nullable()->after('sender_type');
            $table->unsignedBigInteger('thread_party_id')->nullable()->after('thread_party_type');

            $table->index(['demand_id', 'thread_party_type', 'thread_party_id']);
        });

        DB::statement("UPDATE messages SET thread_party_type = sender_type, thread_party_id = sender_id WHERE sender_type IN ('provider', 'company')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['demand_id', 'thread_party_type', 'thread_party_id']);
            $table->dropColumn(['thread_party_type', 'thread_party_id']);
        });
    }
};
