<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Concerns\BuildsBrandedPdf;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateGlobalGrimoire;
use App\Models\ProfilePathMatch;
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

        // Génération progressive : la synthèse est sauvegardée AVANT les voies
        // (voies_phase = pending → done). Tant que la phase n'est pas "done", des
        // voies vides sont NORMALES (elles arrivent) — on ne relance donc rien.
        $voiesPhase = $grimoire->ai_metadata['voies_phase'] ?? null;
        $voiesEmpty = empty($grimoire->voies);

        // Garde-fou « synthèse OK mais aucune voie » : un Grimoire dont la phase voies
        // est TERMINÉE (done) mais vide (clé JSON ratée, appel voies échoué…) restait
        // figé. On le relance, borné à 2 tentatives via ai_metadata->voies_attempts
        // (remis à 0 dès qu'il y a des voies). Anti-boucle indispensable en sync.
        $voiesAttempts   = (int) ($grimoire->ai_metadata['voies_attempts'] ?? 0);
        $needsVoiesRetry = $grimoire->status === 'ready' && $voiesPhase === 'done'
            && $voiesEmpty && $voiesAttempts < 2;

        // Étape 2 (voies) interrompue : la synthèse est là (status ready) mais la
        // phase voies est restée "pending" et le job a été tué (OVH max_execution_time)
        // il y a plus de 3 min → on relance pour terminer les voies (synthèse réutilisée).
        $stuckVoies = $grimoire->status === 'ready'
            && $voiesPhase === 'pending'
            && $voiesEmpty
            && $grimoire->updated_at
            && $grimoire->updated_at->lt(now()->subMinutes(3));

        // Grimoire absent ou périmé (un test ajouté/refait) → on (re)lance la génération.
        $needsGeneration = $grimoire->status !== 'ready'
            || $grimoire->tests_signature !== $signature
            || $needsVoiesRetry
            || $stuckVoies;

        // Détecte un Grimoire bloqué sur "pending" (job tué ou pas encore démarré) :
        // Avec QUEUE_CONNECTION=database + cron toutes les minutes, le job peut prendre
        // jusqu'à 1 min pour démarrer + ~2 min pour tourner = 3 min max normal.
        // Seuil à 7 min pour éviter les faux positifs (relances inutiles).
        $stuckPending = $grimoire->status === 'pending'
            && $grimoire->updated_at
            && $grimoire->updated_at->lt(now()->subMinutes(7));

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
            // Libère aussi le rate limiter "Régénérer" : si le job était bloqué,
            // l'utilisateur doit pouvoir relancer manuellement sans attendre 10 min.
            RateLimiter::clear('grimoire-regen:' . $user->id);
        }

        if ($needsGeneration && ($grimoire->status !== 'failed' || $stuckPending) && !$recentlyFailed) {
            // On marque l'état "régénération en cours" de façon DÉTECTABLE par le front :
            // voies vidées + voies_phase=pending. Sans ça, un Grimoire encore "ready" avec
            // son ancien contenu n'aurait déclenché ni écran d'attente ni polling, et la
            // nouvelle version ne serait jamais apparue toute seule. La synthèse, elle, est
            // conservée à l'écran le temps que la nouvelle se calcule (moins brutal).
            if ($grimoire->status === 'ready' || $stuckPending) {
                $grimoire->update([
                    'status'      => 'pending',
                    'voies'       => [],
                    'ai_metadata' => array_merge($grimoire->ai_metadata ?? [], ['voies_phase' => 'pending']),
                ]);
            }
            // force=true si le candidat a cliqué "Régénérer" (tests_signature vidée par refresh()),
            // si le job est bloqué (stuckPending/stuckVoies) ou s'il faut relancer les voies
            // (needsVoiesRetry) : ces cas ont status=ready + signature à jour, donc sans force
            // le job sauterait via sa vérification d'idempotence.
            $forceRegen = ($grimoire->fresh()->tests_signature === '')
                || $stuckPending || $stuckVoies || $needsVoiesRetry;
            GenerateGlobalGrimoire::dispatch($user->id, force: $forceRegen)->afterResponse();
        }

        $grimoire = $grimoire->fresh();

        // Écran d'attente PLEINE PAGE : seulement tant qu'il n'y a AUCUNE synthèse à
        // montrer (toute première génération). Dès que la synthèse existe, on affiche
        // la page et les voies se chargent à part (voies_pending).
        $aiPending = $grimoire->status === 'pending' && empty($grimoire->synthesis);

        // Voies en cours : la synthèse est affichée mais les pistes se génèrent encore.
        $voiesPhaseNow = $grimoire->ai_metadata['voies_phase'] ?? null;
        $voiesPending  = !empty($grimoire->synthesis)
            && empty($grimoire->voies)
            && ($voiesPhaseNow === 'pending' || $grimoire->status === 'pending');

        return Inertia::render('Candidate/Grimoire', [
            'grimoire' => [
                'synthesis'      => $grimoire->synthesis,
                'voies'          => $grimoire->voies ?? [],
                'tests_included' => $grimoire->tests_included ?? [],
                'status'         => $grimoire->status,
                'generated_at'   => $grimoire->generated_at?->toIso8601String(),
                'disclaimer'          => $grimoire->aiDisclaimer(),
                'requested_voies_count' => (int) ($grimoire->ai_metadata['requested_voies_count'] ?? 30),
            ],
            'tests' => $attempts->map(fn ($a) => [
                'attempt_id'   => $a->id,
                'name'         => $a->test?->name,
                'summary'      => $this->testSummary($a->result?->ai_synthesis),
                'completed_at' => $a->completed_at?->toIso8601String(),
                'results_url'  => route('results.show', $a->id),
                'pdf_url'      => $a->result?->ai_synthesis ? route('results.pdf', $a->id) : null,
            ])->values(),
            'ai_pending'   => $aiPending,
            'voies_pending' => $voiesPending,
            'is_empty'     => false,
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

        $voiesPhase    = $grimoire?->ai_metadata['voies_phase'] ?? null;
        $syntheseReady = !empty($grimoire?->synthesis);
        $voiesReady    = $voiesPhase === 'done' || !empty($grimoire?->voies);

        // "Bloqué" : soit l'écran pleine page traîne (status pending > 8 min sans
        // synthèse), soit l'étape voies est restée "pending" trop longtemps (> 8 min).
        $stuckSynth = $grimoire
            && $grimoire->status === 'pending'
            && empty($grimoire->synthesis)
            && $grimoire->updated_at
            && $grimoire->updated_at->lt(now()->subMinutes(8));

        $stuckVoies = $grimoire
            && $syntheseReady
            && !$voiesReady
            && $voiesPhase === 'pending'
            && $grimoire->updated_at
            && $grimoire->updated_at->lt(now()->subMinutes(8));

        return response()->json([
            // 'ready' = tout est prêt (synthèse + voies) — compat avec l'ancien front.
            'ready'          => $grimoire?->status === 'ready' && $voiesReady,
            'synthese_ready' => $syntheseReady,
            'voies_ready'    => $voiesReady,
            'failed'         => $grimoire?->status === 'failed',
            'stuck'          => (bool) ($stuckSynth || $stuckVoies),
        ]);
    }

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
    public function refresh(\Illuminate\Http\Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Garde-fou anti-spam : 1 régénération manuelle / 3 minutes (TECH-11).
        // 3 min (au lieu de 10) car l'ajustement du nombre de pistes est un usage
        // légitime fréquent, et chaque régénération est désormais peu coûteuse (Haiku).
        $key = 'grimoire-regen:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = (int) ceil($seconds / 60);
            return back()->withErrors(['regen' => "Tu pourras régénérer dans {$minutes} min ({$seconds} s). Limite : 1 fois par 3 minutes."]);
        }
        RateLimiter::hit($key, 180);

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
            $requestedCount = (int) $request->input('count', 30);
            $requestedCount = max(1, min(50, $requestedCount));

            $meta = $grimoire->ai_metadata ?? [];
            $meta['requested_voies_count'] = $requestedCount;
            // Régénération détectable par le front (loader pistes + polling) : voies vidées
            // + phase pending. La synthèse reste affichée le temps du recalcul.
            $meta['voies_phase'] = 'pending';

            $grimoire->update([
                'status'         => 'pending',
                'tests_signature' => '',
                'voies'          => [],
                'ai_metadata'    => $meta,
            ]);
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
