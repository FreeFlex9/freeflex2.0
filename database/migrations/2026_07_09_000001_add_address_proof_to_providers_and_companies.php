<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->string('address_proof_path')->nullable()->after('ccmei_path');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('address_proof_path')->nullable()->after('cnpj_card_path');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('address_proof_path');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('address_proof_path');
        });
    }
};
