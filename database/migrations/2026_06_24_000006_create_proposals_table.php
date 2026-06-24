<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->enum('status', [
                'pending',
                'pending_admin_approval',
                'accepted',
                'rejected',
                'rejected_admin',
            ])->default('pending');
            $table->timestamps();
            $table->unique(['demand_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
