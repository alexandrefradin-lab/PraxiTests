<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute des colonnes de traçabilité IA sur test_results.
 *
 * RGPD / Risque métier (audit GPT) :
 *  - ai_model       : modèle exact utilisé (ex: claude-sonnet-4-6)
 *  - ai_metadata    : JSON contenant les éléments ayant conduit aux recommandations
 *                     (nombre de tests, dimensions analysées, version prompts, etc.)
 *                     NB : ne contient PAS le texte des réponses (données psycho sensibles)
 *  - ai_generated_at : timestamp de génération (distinct de created_at du résultat)
 *
 * Permet d'afficher aux utilisateurs "Pourquoi ces métiers ?" et de tracer
 * les hallucinations éventuelles en cas de plainte.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->string('ai_model')->nullable()->after('ai_driver')
                ->comment('Modèle IA exact (ex: claude-sonnet-4-6)');

            $table->json('ai_metadata')->nullable()->after('ai_model')
                ->comment('Éléments contextuels ayant alimenté les recommandations IA (sans données psycho brutes)');

            // Renommage sémantique : generated_at → ai_generated_at si la colonne n'existe pas encore
            if (!Schema::hasColumn('test_results', 'ai_generated_at')) {
                $table->timestamp('ai_generated_at')->nullable()->after('ai_metadata');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumnIfExists('ai_model');
            $table->dropColumnIfExists('ai_metadata');
            $table->dropColumnIfExists('ai_generated_at');
        });
    }
};
