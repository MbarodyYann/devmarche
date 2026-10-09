<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body'); // Markdown
            $table->text('body_html'); // rendu sécurisé et coloré, mis en cache à l'enregistrement
            $table->unsignedBigInteger('accepted_answer_id')->nullable();
            $table->integer('score')->default(0);
            $table->unsignedInteger('answers_count')->default(0);
            $table->timestamps();

            $table->index('created_at');
            $table->index('score');
            $table->index('answers_count');
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body'); // Markdown
            $table->text('body_html');
            $table->integer('score')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'score']);
        });

        // Clé étrangère circulaire : ajoutée une fois la table answers créée.
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('accepted_answer_id')->references('id')->on('answers')->nullOnDelete();
        });

        Schema::create('question_skill', function (Blueprint $table) {
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['question_id', 'skill_id']);
            $table->index('skill_id');
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('votable');
            $table->smallInteger('value'); // +1 ou -1
            $table->timestamps();

            $table->unique(['user_id', 'votable_type', 'votable_id']);
        });

        // Recherche plein texte en français sur le titre et le corps (index GIN).
        DB::statement("create index questions_search_index on questions using gin (to_tsvector('french', title || ' ' || body))");
    }

    public function down(): void
    {
        Schema::table('questions', fn (Blueprint $table) => $table->dropForeign(['accepted_answer_id']));
        Schema::dropIfExists('votes');
        Schema::dropIfExists('question_skill');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
    }
};
