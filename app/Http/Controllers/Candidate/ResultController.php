<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Praxis\Core\Plugins\PluginHooks;

class ResultController extends Controller
{
    public function show(TestAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        $attempt->load('test', 'result');

        // Laisser chaque plugin overrider la page de résultats via un filtre.
        // Ex: PraxiCare enregistre 'results.inertia_page' → 'PraxiCareResult'
        $allowed = [
            'Candidate/ResultsShow', 'PraximetResult', 'PraxiCareResult',
            'PraxiEmoResult', 'PraxiMumResult', 'PraxiValeursResult',
        ];
        $page = PluginHooks::applyFilters('results.inertia_page', 'Candidate/ResultsShow', $attempt);
        if (!in_array($page, $allowed, true)) {
            $page = 'Candidate/ResultsShow';
        }

        return Inertia::render($page, [
            'attempt'    => $attempt,
            'result'     => $attempt->result,
            'ai_pending' => !$attempt->result?->ai_synthesis,
        ]);
    }

    /**
     * Endpoint JSON léger pour le polling côté Vue.
     * Appelé toutes les 5s par ResultsShow.vue quand ai_pending = true.
     */
    public function status(TestAttempt $attempt): \Illuminate\Http\JsonResponse
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        return response()->json([
            'ai_ready'     => (bool) $attempt->result?->ai_synthesis,
            'jobs_ready'   => !empty($attempt->result?->suggested_jobs),
        ]);
    }

    public function pdf(TestAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        $attempt->load('test', 'result', 'user.profile');

        $pdf = Pdf::loadView('pdf.results', ['attempt' => $attempt]);
        return $pdf->download("praxiquest-results-{$attempt->id}.pdf");
    }

    public function history()
    {
        $attempts = TestAttempt::with([
                'test:id,name,slug',
                'result:attempt_id,ai_synthesis,suggested_jobs',
            ])
            ->where('user_id', auth()->id())
            ->select(['id', 'test_id', 'status', 'started_at', 'completed_at'])
            ->latest('started_at')
            ->get();

        return Inertia::render('Candidate/History', [
            'attempts' => $attempts,
        ]);
    }
}
