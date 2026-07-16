<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->json('document_validation')->nullable()->after('address_proof_path');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->json('document_validation')->nullable()->after('address_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('document_validation');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('document_validation');
        });
    }
};
