<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Concerns\BuildsBrandedPdf;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateGlobalGrimoire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Praxis\Core\AI\Services\GlobalGrimoireService;

/**
 * Le Grimoire global — relecture transversale de tous les tests du candidat.
 * Page /grimoire. Couche de conclusion par-dessus les résultats par test.
 */
class GrimoireController extends Controller
{
    use BuildsBrandedPdf;

    public function show(GlobalGrimoireService $service): Response
    {
        $user     = auth()->user();
        $attempts = $service->completedAttempts($user);

        // Aucun test complété → page "vide" incitative (la Vue gère l'état).
        if ($attempts->isEmpty()) {
            return Inertia::render('Candidate/Grimoire', [
                'grimoire'    => null,
                'tests'       => [],
                'ai_pending'  => false,
                'is_empty'    => true,
            ]);
        }

        $grimoire  = $user->grimoire();
        $signature = $service->signature($attempts);

        // Grimoire absent ou périmé (un test ajouté/refait) → on (re)lance la génération.
        $needsGeneration = $grimoire->status !== 'ready'
            || $grimoire->tests_signature !== $signature;

        if ($needsGeneration && $grimoire->status !== 'failed') {
            if ($grimoire->status === 'ready') {
                // périmé : on repasse en pending le temps de la régénération
                $grimoire->update(['status' => 'pending']);
            }
            GenerateGlobalGrimoire::dispatch($user->id)->afterResponse();
        }

        $pending = $grimoire->fresh()->status === 'pending';

        return Inertia::render('Candidate/Grimoire', [
            'grimoire' => [
                'synthesis'      => $grimoire->synthesis,
                'voies'          => $grimoire->voies ?? [],
                'tests_included' => $grimoire->tests_included ?? [],
                'status'         => $grimoire->status,
                'generated_at'   => $grimoire->generated_at?->toIso8601String(),
                'disclaimer'     => $grimoire->aiDisclaimer(),
            ],
            'tests' => $attempts->map(fn ($a) => [
                'attempt_id' => $a->id,
                'name'       => $a->test?->name,
            ])->values(),
            'ai_pending' => $pending,
            'is_empty'   => false,
        ]);
    }

    /** Polling léger pour la page (équivalent de results.status). */
    public function status(): JsonResponse
    {
        $grimoire = auth()->user()->profileGrimoire;

        return response()->json([
            'ready'  => $grimoire?->status === 'ready',
            'failed' => $grimoire?->status === 'failed',
        ]);
    }

    /** Export PDF de la relecture globale. */
    public function pdf(GlobalGrimoireService $service)
    {
        $user     = auth()->user();
        $grimoire = $user->profileGrimoire;

        abort_unless($grimoire && $grimoire->isReady(), 404, "Ton Grimoire n'est pas encore prêt.");

        $opts = $this->pdfOptions();

        $pdf = Pdf::loadView('pdf.grimoire', [
            'user'     => $user->load('profile'),
            'grimoire' => $grimoire,
            'brand'    => $opts['brand'],
            'org'      => $opts['org'],
        ])->setPaper(
            config('praxiquest.pdf.paper', 'a4'),
            config('praxiquest.pdf.orientation', 'portrait'),
        );

        $slug = \Illuminate\Support\Str::slug($opts['brand']['name'] ?? 'praxiquest');
        return $pdf->download("{$slug}-grimoire-{$user->id}.pdf");
    }

    /** Bouton "Régénérer" — force une nouvelle relecture. */
    public function refresh(): RedirectResponse
    {
        $user     = auth()->user();
        $grimoire = $user->grimoire();

        // Garde-fou anti-spam : 1 régénération manuelle / minute.
        if ($grimoire->generated_at && $grimoire->generated_at->gt(now()->subMinute())) {
            return back()->with('info', 'Ton Grimoire vient d\'être mis à jour. Réessaie dans un instant.');
        }

        $grimoire->update(['status' => 'pending']);
        GenerateGlobalGrimoire::dispatch($user->id, force: true)->afterResponse();

        return back()->with('success', 'Ton Grimoire est en cours de relecture…');
    }
}
