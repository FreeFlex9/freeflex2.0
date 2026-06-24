<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('score')->unsigned()->comment('1-5');
            $table->text('comment')->nullable();
            $table->enum('rated_by', ['company', 'provider']);
            $table->timestamps();
            $table->unique(['demand_id', 'provider_id', 'rated_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
