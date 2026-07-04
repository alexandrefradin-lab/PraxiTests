<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAttemptInsights;
use App\Models\TestAttempt;
use App\Models\TestResult;
use Inertia\Inertia;

/**
 * Gestion admin des synthèses IA en échec.
 *
 * Audit — gestion d'échec IA : permet de lister les tentatives dont la
 * génération a échoué (ai_failed = true) et de les relancer manuellement
 * sans passer par la console OVH.
 */
class InsightsRetryController extends Controller
{
    /**
     * Liste des tentatives avec synthèse IA en échec (ai_failed = true).
     * L'erreur IA (ai_error) est affichée pour diagnostiquer sans ouvrir les logs.
     */
    public function index()
    {
        $failed = TestResult::where('ai_failed', true)
            ->with([
                'attempt:id,user_id,test_id,completed_at',
                'attempt.user:id,name,email',
                'attempt.test:id,name,slug',
            ])
            ->latest()
            ->paginate(30)
            ->through(fn (TestResult $r) => [
                'id'           => $r->id,
                'attempt_id'   => $r->attempt_id,
                'user'         => $r->attempt?->user?->name,
                'email'        => $r->attempt?->user?->email,
                'test'         => $r->attempt?->test?->name,
                'completed_at' => $r->attempt?->completed_at?->format('d/m/Y H:i'),
                'ai_error'     => $r->ai_error,
            ]);

        return Inertia::render('Admin/FailedInsights', [
            'results' => $failed,
        ]);
    }

    /**
     * Relance en masse : toutes les synthèses en échec repartent en file.
     * Chaque relance est un job séparé — un nouvel échec isolé ne bloque pas le lot.
     */
    public function retryAll()
    {
        $ids = TestResult::where('ai_failed', true)->pluck('attempt_id')->filter();

        abort_if($ids->isEmpty(), 422, 'Aucune synthèse en échec à relancer.');

        TestResult::whereIn('attempt_id', $ids)->update([
            'ai_synthesis' => null,
            'ai_failed'    => false,
            'ai_error'     => null,
            'generated_at' => null,
        ]);

        foreach ($ids as $attemptId) {
            GenerateAttemptInsights::dispatch($attemptId);
        }

        \App\Models\AuditLog::record('insights.retried_all', null, ['count' => $ids->count()]);

        return back()->with('success', "Relance lancée pour {$ids->count()} synthèse(s).");
    }

    /**
     * Relance la génération IA pour une tentative échouée.
     * Réinitialise le flag ai_failed et remet ai_synthesis à null
     * pour que le polling candidat reprenne.
     */
    public function retry(TestAttempt $attempt)
    {
        abort_unless($attempt->result !== null, 404, 'Pas de résultat pour cette tentative.');
        abort_unless($attempt->result->ai_failed, 422, 'La synthèse n\'est pas en état d\'échec.');

        // Réinitialiser pour relancer le polling côté candidat
        $attempt->result->update([
            'ai_synthesis' => null,
            'ai_failed'    => false,
            'ai_error'     => null,
            'generated_at' => null,
        ]);

        GenerateAttemptInsights::dispatch($attempt->id);

        \App\Models\AuditLog::record('insights.retried', $attempt, []);

        return back()->with('success', "Génération IA relancée pour la tentative #{$attempt->id}.");
    }
}
