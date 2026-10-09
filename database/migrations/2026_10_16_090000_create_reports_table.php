<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Signalements : un par membre et par contenu (mission, question, réponse).
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('reportable');
            $table->string('reason', 20);
            $table->timestamps();

            $table->unique(['reporter_id', 'reportable_type', 'reportable_id']);
        });

        // Contenu masqué par la modération : invisible du public.
        foreach (['missions', 'questions', 'answers'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->timestamp('hidden_at')->nullable()->index());
        }
    }

    public function down(): void
    {
        foreach (['missions', 'questions', 'answers'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('hidden_at'));
        }

        Schema::dropIfExists('reports');
    }
};
