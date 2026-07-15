<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->boolean('had_recent_surgery')->default(false)->after('message');
            $table->text('surgery_description')->nullable()->after('had_recent_surgery');
            $table->boolean('health_consent')->default(false)->after('surgery_description');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['had_recent_surgery', 'surgery_description', 'health_consent']);
        });
    }
};
