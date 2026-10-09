<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idées reprises de Fiverr (02, lot 3 bis) : langues parlées, délai proposé, dernière activité.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->jsonb('languages')->nullable(); // codes de config/languages.php : ["fr", "wo"]
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedSmallInteger('delivery_days')->nullable(); // 1 à 180 jours
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', fn (Blueprint $table) => $table->dropColumn('languages'));
        Schema::table('applications', fn (Blueprint $table) => $table->dropColumn('delivery_days'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('last_seen_at'));
    }
};
