<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->boolean('is_pcd')->default(false)->after('bio');
            $table->string('pcd_type')->nullable()->after('is_pcd');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['is_pcd', 'pcd_type']);
        });
    }
};
