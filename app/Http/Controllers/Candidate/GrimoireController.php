<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Concerns\BuildsBrandedPdf;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateGlobalGrimoire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;
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

        $grimoire  = $user->getOrCreateGrimoire();
        $signature = $service->signature($attempts);

        // Garde-fou « synthèse OK mais aucune voie » : un Grimoire marqué ready dont
        // les voies sont vides (clé JSON ratée, appel voies échoué…) restait figé sans
        // jamais se régénérer. On le relance, borné à 2 tentatives via le compteur
        // ai_metadata->voies_attempts (géré par le service, remis à 0 dès qu'il y a des
        // voies). Anti-boucle indispensable avec QUEUE_CONNECTION=sync où chaque visite
        // rejouerait l'IA de façon synchrone.
        $voiesEmpty      = empty($grimoire->voies);
        $voiesAttempts   = (int) ($grimoire->ai_metadata['voies_attempts'] ?? 0);
        $needsVoiesRetry = $grimoire->status === 'ready' && $voiesEmpty && $voiesAttempts < 2;

        // Grimoire absent ou périmé (un test ajouté/refait) → on (re)lance la génération.
        $needsGeneration = $grimoire->status !== 'ready'
            || $grimoire->tests_signature !== $signature
            || $needsVoiesRetry;

        // Détecte un Grimoire bloqué sur "pending" (job tué par OVH avant completion) :
        // le statut ne passe jamais à ready/failed, le polling tourne à vide indéfiniment.
        // Seuil ramené à 3 min (l'appel IA le plus long dépasse rarement 2 min sur OVH).
        $stuckPending = $grimoire->status === 'pending'
            && $grimoire->updated_at
            && $grimoire->updated_at->lt(now()->subMinutes(3));

        // Cooldown anti-boucle : si writeFallback a tracé un échec récent (< 3 min),
        // on ne redispatch pas le job — évite les boucles infinies quand la clé API
        // est invalide (chaque visite relancerait un appel IA voué à échouer).
        $lastFailedAt  = isset($grimoire->ai_metadata['last_failed_at'])
            ? \Carbon\Carbon::parse($grimoire->ai_metadata['last_failed_at'])
            : null;
        $recentlyFailed = $lastFailedAt && $lastFailedAt->gt(now()->subMinutes(3));

        if ($stuckPending) {
            \Illuminate\Support\Facades\Cache::forget(
                'laravel_unique_job:' . GenerateGlobalGrimoire::class . ':grimoire_user_' . $user->id
            );
        }

        if ($needsGeneration && ($grimoire->status !== 'failed' || $stuckPending) && !$recentlyFailed) {
            if ($grimoire->status === 'ready') {
                // périmé : on repasse en pending le temps de la régénération
                $grimoire->update(['status' => 'pending']);
            }
            if ($stuckPending) {
                // Bloqué depuis trop longtemps → reset + relance forcée
                $grimoire->update(['status' => 'pending']);
            }
            // force=true si le candidat a cliqué "Régénérer" (tests_signature vidée par refresh())
            // ou si le job est bloqué depuis trop longtemps (stuckPending).
            // Sans force, le job pourrait sauter silencieusement si un verrou résiduel
            // ou une vérification d'idempotence l'empêche de tourner.
            $forceRegen = ($grimoire->fresh()->tests_signature === '') || $stuckPending;
            GenerateGlobalGrimoire::dispatch($user->id, force: $forceRegen)->afterResponse();
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
                'attempt_id'   => $a->id,
                'name'         => $a->test?->name,
                'summary'      => $this->testSummary($a->result?->ai_synthesis),
                'completed_at' => $a->completed_at?->toIso8601String(),
                'results_url'  => route('results.show', $a->id),
                'pdf_url'      => $a->result?->ai_synthesis ? route('results.pdf', $a->id) : null,
            ])->values(),
            'ai_pending' => $pending,
            'is_empty'   => false,
        ]);
    }

    /**
     * Résumé court d'un test pour la liste du Grimoire : premier paragraphe
     * de la synthèse IA, tronqué proprement. Null si la synthèse n'est pas prête.
     */
    private function testSummary(?string $synthesis): ?string
    {
        $synthesis = trim((string) $synthesis);
        if ($synthesis === '') {
            return null;
        }

        // Premier VRAI paragraphe de prose : on ignore les titres Markdown
        // (« # Synthèse de profil — … »), les règles horizontales et les lignes
        // vides, sinon le résumé de chaque test affiche juste le titre commun.
        $first = collect(preg_split('/\n+/', $synthesis))
            ->map(fn ($p) => trim($p))
            ->first(function ($p) {
                if ($p === '') return false;
                if (preg_match('/^#{1,6}\s/', $p)) return false;   // titre Markdown
                if (preg_match('/^[-*_=]{3,}$/', $p)) return false; // règle horizontale
                return true;
            }) ?? $synthesis;

        // Nettoyage du balisage Markdown inline pour un teaser en texte brut.
        $first = preg_replace('/^#{1,6}\s+/', '', $first);          // titre résiduel
        $first = preg_replace('/(\*\*|__|\*|_|`)/', '', $first);    // gras / italique / code
        $first = trim(preg_replace('/\s+/', ' ', $first));

        return \Illuminate\Support\Str::limit($first, 280);
    }

    /** Polling léger pour la page (équivalent de results.status). */
    public function status(): JsonResponse
    {
        $grimoire = auth()->user()->profileGrimoire;

        // Détecte un Grimoire bloqué sur "pending" depuis > 6 min :
        // le front recharge alors la page pour déclencher un nouveau dispatch.
        $stuck = $grimoire
            && $grimoire->status === 'pending'
            && $grimoire->updated_at
            && $grimoire->updated_at->lt(now()->subMinutes(6));

        return response()->json([
            'ready'  => $grimoire?->status === 'ready',
            'failed' => $grimoire?->status === 'failed',
            'stuck'  => $stuck,
        ]);
    }

    /** Export PDF de la relecture globale. */
    /** Export PDF de la relecture globale. */
    public function pdf(GlobalGrimoireService $service)
    {
        $user     = auth()->user();
        $grimoire = $user->profileGrimoire;

        abort_unless($grimoire && $grimoire->isReady(), 404, "Ton Grimoire n'est pas encore prêt.");

        $opts = $this->pdfOptions();

        $slug = \Illuminate\Support\Str::slug($opts['brand']['name'] ?? 'praxiquest');

        return $this->downloadBrandedPdf('pdf.grimoire', [
            'user'     => $user->load('profile'),
            'grimoire' => $grimoire,
            'brand'    => $opts['brand'],
            'org'      => $opts['org'],
        ], "{$slug}-grimoire-{$user->id}.pdf");
    }

    /** Bouton "Régénérer" — force une nouvelle relecture. */
    public function refresh(): RedirectResponse
    {
        $user = auth()->user();

        // Garde-fou anti-spam : 1 régénération manuelle / 10 minutes (TECH-11).
        $key = 'grimoire-regen:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['regen' => "Vous pouvez régénérer dans {$seconds} secondes. (Limite : 1 fois par 10 minutes)"]);
        }
        RateLimiter::hit($key, 600);

        $grimoire = $user->getOrCreateGrimoire();

        // Verrou atomique (cf. audit M-3 / E-4) : empêche deux clics simultanés —
        // ou un statut déjà "pending" avec generated_at null — de lancer plusieurs
        // jobs IA payants en parallèle. La déduplication réelle du travail est
        // ensuite assurée par ShouldBeUnique côté job.
        $lock = \Illuminate\Support\Facades\Cache::lock("grimoire_refresh_user_{$user->id}", 30);
        if (! $lock->get()) {
            return back()->with('info', 'Ton Grimoire est déjà en cours de relecture…');
        }

        try {
            // Purge l'éventuel verrou ShouldBeUnique résiduel d'un job précédent tué
            // par max_execution_time (OVH sync+afterResponse). Sans ça, le dispatch
            // suivant est silencieusement ignoré et le Grimoire reste figé sur "pending"
            // indéfiniment (le polling front ne voit jamais ready/failed).
            \Illuminate\Support\Facades\Cache::forget(
                'laravel_unique_job:' . GenerateGlobalGrimoire::class . ':grimoire_user_' . $user->id
            );

            // On met uniquement en "pending" sans dispatcher ici.
            // Sur OVH (QUEUE_CONNECTION=sync sans fastcgi_finish_request), un dispatch
            // afterResponse() dans ce POST bloque la réponse tant que l'IA tourne (~1-2 min).
            // Inertia reçoit alors le redirect APRÈS que le job est terminé → show() voit
            // status='ready' → ai_pending=false → l'écran pending ne s'affiche jamais.
            // Solution : show() dispatch déjà le job dans son propre afterResponse quand il
            // voit needsGeneration=true. La valeur $pending est calculée AVANT afterResponse,
            // donc ai_pending=true est retourné immédiatement au client → pending screen OK.
            $grimoire->update(['status' => 'pending', 'tests_signature' => '']);
        } finally {
            $lock->release();
        }

        return back()->with('success', 'Ton Grimoire est en cours de relecture…');
    }

    /**
     * Déblocage déclaratif d'une piste : la personne indique viser/avoir la
     * formation associée. Lot 1 = simple déclaration (trace), sans toucher au
     * score des tests. La validation par module interne (qui réduira réellement
     * l'écart de formation) viendra en Lot 3.
     */
    public function declarePiste(ProfilePathMatch $pathMatch): RedirectResponse
    {
        // Autorisation : la piste doit appartenir au profil du candidat connecté.
        abort_unless($pathMatch->profile?->user_id === auth()->id(), 403);

        $pathMatch->update(['unlocked' => true]);

        return back()->with('success', 'Formation déclarée pour cette piste.');
    }
}
