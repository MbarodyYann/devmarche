<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            // Rendu Markdown sécurisé de la description, mis en cache à l'enregistrement.
            $table->text('description_html')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('missions', fn (Blueprint $table) => $table->dropColumn('description_html'));
    }
};
