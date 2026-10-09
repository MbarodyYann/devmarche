<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recherche plein texte en français sur le titre et la description (index GIN), comme pour les questions.
        DB::statement("create index missions_search_index on missions using gin (to_tsvector('french', title || ' ' || description))");
    }

    public function down(): void
    {
        DB::statement('drop index if exists missions_search_index');
    }
};
