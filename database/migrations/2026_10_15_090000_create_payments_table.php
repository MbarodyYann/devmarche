<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Paiement Mobile Money SIMULÉ avec séquestre (04, « payments ») : aucun argent réel, aucun service externe.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider', 20);
            $table->string('phone', 20);
            $table->unsignedInteger('amount_fcfa');
            $table->string('status', 20)->default('held');
            $table->string('reference', 12)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
