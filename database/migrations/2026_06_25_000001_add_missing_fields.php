<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Endereço + coords nas empresas
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('state');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Endereço + coords + horário nos prestadores
        Schema::table('providers', function (Blueprint $table) {
            $table->string('zip_code', 9)->nullable()->after('bio');
            $table->string('street')->nullable()->after('zip_code');
            $table->string('number', 20)->nullable()->after('street');
            $table->string('complement')->nullable()->after('number');
            $table->string('neighborhood')->nullable()->after('complement');
            $table->string('city')->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('state');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->time('start_hour')->nullable()->default('08:00')->after('longitude');
            $table->time('end_hour')->nullable()->default('18:00')->after('start_hour');
        });

        // Coords + valor total + statuses adicionais nas demandas
        Schema::table('demands', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('state');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('total_value', 10, 2)->nullable()->after('longitude');
        });

        // Alterar enum de status das demandas
        DB::statement("ALTER TABLE demands MODIFY COLUMN status ENUM(
            'open',
            'partially_scheduled',
            'scheduled',
            'in_progress',
            'completed',
            'cancelled',
            'pending_admin_approval'
        ) DEFAULT 'pending_admin_approval'");

        // Alterar enum de status das propostas
        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM(
            'pending',
            'pending_company_accept',
            'pending_admin_approval',
            'accepted',
            'rejected',
            'rejected_admin',
            'rejected_provider'
        ) DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('companies', fn ($t) => $t->dropColumn(['latitude', 'longitude']));
        Schema::table('providers', fn ($t) => $t->dropColumn(['zip_code','street','number','complement','neighborhood','city','state','latitude','longitude','start_hour','end_hour']));
        Schema::table('demands',   fn ($t) => $t->dropColumn(['latitude', 'longitude', 'total_value']));
    }
};
