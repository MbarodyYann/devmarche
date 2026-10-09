<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Avis du client sur le freelance, une fois par mission terminée (04, « reviews »).
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('communication');
            $table->unsignedTinyInteger('quality');
            $table->unsignedTinyInteger('timeliness');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['reviewee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
