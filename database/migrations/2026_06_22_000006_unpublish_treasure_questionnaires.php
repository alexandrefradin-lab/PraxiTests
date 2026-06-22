<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La Salle du Trésor ne contient plus aucun test : les 5 mini-apps deviennent
 * des bibliothèques d'exercices de développement personnel. On dépublie donc
 * les questionnaires que leurs seeders avaient créés, pour qu'ils
 * disparaissent de L'Armurerie et ne soient plus la porte d'entrée des apps.
 */
return new class extends Migration
{
    /** Slugs des tests adossés aux 5 mini-apps de la Salle du Trésor. */
    private array $slugs = [
        'praxispeak',
        'praxiself-affirmation',
        'praxilink-assertivite',
        'praxizen-stress',
        'praxiflow-productivite',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('tests')) {
            return;
        }

        DB::table('tests')
            ->whereIn('slug', $this->slugs)
            ->update(['published' => false]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('tests')) {
            return;
        }

        DB::table('tests')
            ->whereIn('slug', $this->slugs)
            ->update(['published' => true]);
    }
};
