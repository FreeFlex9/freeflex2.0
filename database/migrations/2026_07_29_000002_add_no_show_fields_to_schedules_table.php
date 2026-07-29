<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->enum('no_show_status', ['none', 'pending_justification', 'justified', 'unjustified'])
                ->default('none')->after('status');
            $table->timestamp('no_show_detected_at')->nullable();
            $table->text('no_show_justification')->nullable();
            $table->timestamp('no_show_justification_submitted_at')->nullable();
            $table->timestamp('no_show_justification_deadline')->nullable();
            $table->timestamp('no_show_reviewed_at')->nullable();
            $table->foreignId('no_show_reviewed_by_admin_id')->nullable()
                ->constrained('admins')->nullOnDelete();
            $table->boolean('no_show_penalty_applied')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('no_show_reviewed_by_admin_id');
            $table->dropColumn([
                'no_show_status', 'no_show_detected_at', 'no_show_justification',
                'no_show_justification_submitted_at', 'no_show_justification_deadline',
                'no_show_reviewed_at', 'no_show_penalty_applied',
            ]);
        });
    }
};
