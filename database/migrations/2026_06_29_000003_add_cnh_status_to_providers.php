<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->string('cnh_status', 20)->nullable()->after('has_license');
            $table->text('cnh_rejection_reason')->nullable()->after('cnh_status');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['cnh_status', 'cnh_rejection_reason']);
        });
    }
};
