<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demands', function (Blueprint $table) {
            $table->enum('dress_code', ['uniforme', 'social', 'casual', 'esportiva', 'epi', 'outro'])
                ->nullable()->after('has_meal');
            $table->string('dress_code_other')->nullable()->after('dress_code');
        });
    }

    public function down(): void
    {
        Schema::table('demands', function (Blueprint $table) {
            $table->dropColumn(['dress_code', 'dress_code_other']);
        });
    }
};
