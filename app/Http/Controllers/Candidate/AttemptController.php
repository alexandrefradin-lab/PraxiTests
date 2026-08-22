<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAttemptInsights;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\NarrativeEngine;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Core\TestEngine\TestEngine;

class AttemptController extends Controller
{
    public function __construct(
        protected TestEngine $engine,
        protected GamificationEngine $gamification,
        protected NarrativeEngine $narrative,
        protected RewardCatalog $rewards,
    ) {}

    public function start(Request $request, Test $test)
    {
        abort_unless($test->published, 404);
        abort_unless(auth()->user()->profile?->isComplete(), 403, 'Profil incomplet');

        // Paywall particulier (config/b2c.php) : un auto-inscrit sans achat ne
        // lance que l'épreuve d'appel. Les invités d'un pro passent toujours.
        if (! \App\Support\B2c::isFreeTest($test) && \App\Support\B2c::locked($request->user())) {
            return redirect()->route('b2c.unlock')
                ->with('info', 'Cette épreuve fait partie du Rapport complet. Débloque ton parcours pour la lancer.');
        }

        // Gating « cadeau » : impossible de lancer un test scellé par un palier d'Éclats.
        if (! $this->rewards->isTestUnlocked($test->slug, $request->user())) {
            $reward = $this->rewards->rewardForTestSlug($test->slug);
            $seuil  = $reward['threshold'] ?? null;

            return redirect()->route('treasure.index')->with(
                'error',
                $seuil
                    ? \App\Support\Parcours::sealedMessage($seuil)
                    : (\App\Support\Parcours::isCorporate() ? "Ce module est encore verrouillé." : "Ce trésor est encore scellé.")
            );
        }

        // Reprendre une tentative en cours plutôt qu'en créer une nouvelle.
        $existing = TestAttempt::where('user_id', $request->user()->id)
            ->where('test_id', $test->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return redirect()->route('attempt.show', $existing);
        }

        // BUG-3 — récupérer l'invitation liée à cet utilisateur pour ce test
        $invitationId = session()->pull('pending_invitation_id');

        $attempt = $this->engine->startAttempt($request->user(), $test, $invitationId);

        // Marquer l'invitation comme démarrée si elle existe
        // Note : la colonne started_at n'existe pas dans test_invitations — on met juste le statut
        if ($invitationId) {
            \App\Models\TestInvitation::where('id', $invitationId)
                ->where('test_id', $test->id)
                ->update(['status' => 'started']);
        }

        // Praxis360 — étape préalable obligatoire : le candidat choisit d'abord
        // au moins 3 évaluateurs (les invitations partent immédiatement), puis
        // il accède à son auto-évaluation. Le garde-fou est dans show().
        if ($test->slug === 'praxis360') {
            return redirect()->route('panel360.manage', $attempt->id);
        }

        return redirect()->route('attempt.show', $attempt);
    }

    public function show(TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        // Praxis360 — interdire l'accès aux items tant que le candidat n'a pas
        // désigné au moins 3 évaluateurs. On le renvoie sur l'étape de panel.
        $attempt->loadMissing('test');
        if (($attempt->test->slug ?? null) === 'praxis360' && $attempt->status === 'in_progress') {
            $panel = \App\Models\EvaluationPanel::where('user_id', auth()->id())
                ->where('self_attempt_id', $attempt->id)
                ->first();
            $count = $panel ? $panel->invitations()->count() : 0;
            if ($count < 3) {
                return redirect()->route('panel360.manage', $attempt->id)
                    ->with('info', 'Indiquez au moins 3 évaluateurs pour démarrer votre 360°.');
            }
        }

        // A6 — Sélection explicite des colonnes pour éviter de transmettre scoring/validation
        // au frontend (données lourdes inutiles côté candidat).
        // Un lazy-loading section par section nécessiterait une refonte du composant Vue.
        $attempt->load([
            'test',
            'test.sections'           => fn ($q) => $q->select('id', 'test_id', 'title', 'description', 'order')->orderBy('order'),
            'test.sections.questions' => fn ($q) => $q->select('id', 'section_id', 'order', 'type', 'prompt', 'helper', 'options', 'meta', 'required')->orderBy('order'),
            'answers'                 => fn ($q) => $q->select('id', 'attempt_id', 'question_id', 'value'),
            'user.badges',
        ]);
        abort_unless($attempt->user !== null, 404, 'User not found');

        // Mélange déterministe des options pour les questions qui le demandent
        // (meta.shuffle) — ex. tests d'aptitude (PraxiCog). Graine stable par
        // (tentative, question) : l'ordre reste identique en cas de retour arrière,
        // mais diffère d'une tentative/candidat à l'autre → aucune clé positionnelle
        // partageable. Le scoring compare la VALEUR choisie (pas la position), donc
        // le résultat est inchangé. 100% opt-in : sans meta.shuffle, rien ne bouge.
        foreach ($attempt->test->sections as $section) {
            foreach ($section->questions as $q) {
                $meta = is_array($q->meta) ? $q->meta : [];
                if (empty($meta['shuffle']) || !is_array($q->options)) {
                    continue;
                }
                $opts    = $this->shuffleDeterministic($q->options, $attempt->id . '-' . $q->id);
                $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                foreach ($opts as $k => &$o) {
                    // Réétiqueter uniquement les options FIGURÉES (libellé = repère
                    // A/B/C/D) pour un affichage ordonné ; les options textuelles
                    // gardent leur libellé (c'est la réponse elle-même).
                    if (is_array($o) && !empty($o['figure'])) {
                        $o['label'] = $letters[$k] ?? (string) ($k + 1);
                    }
                }
                unset($o);
                $q->options = $opts;
            }
        }

        // Laisser un plugin overrider la page de passation via un filtre
        // (calqué sur 'results.inertia_page'). Ex : PraxiTempo → 'PraxiTempoPlay'.
        $allowedPlay = ['Candidate/AttemptPlay', 'PraxiTempoPlay'];
        $page = \Praxis\Core\Plugins\PluginHooks::applyFilters('attempt.inertia_page', 'Candidate/AttemptPlay', $attempt);
        if (! in_array($page, $allowedPlay, true)) {
            $page = 'Candidate/AttemptPlay';
        }

        $percent = $attempt->progressPercent();
        // Aperçu provisoire calculé sur les réponses déjà données. Quand il est
        // débloqué, il REMPLACE le teaser aléatoire (sinon on promettrait un
        // aperçu déjà disponible).
        $insight = $this->narrative->insight($attempt, $percent);

        return Inertia::render($page, [
            'attempt'    => $attempt,
            'progress'   => [
                'percent'   => $percent,
                'insight'   => $insight,
                'narrative' => $insight ? null : $this->narrative->microFeedback($attempt, $percent),
            ],
            'gamification' => $this->gamification->progressOf($attempt->user, $attempt->test),
            'narrative'    => [
                'intro'   => $this->narrative->messageFor('intro', $attempt),
                'midway'  => $this->narrative->messageFor('midway', $attempt),
                'final'   => $this->narrative->messageFor('final', $attempt),
            ],
        ]);
    }

    /**
     * Fisher-Yates déterministe seedé par une chaîne (PRNG xorshift local).
     * N'altère pas le RNG global (mt_rand) — deux appels avec la même graine
     * produisent le même ordre, garantissant la stabilité au retour arrière.
     *
     * @param  array<int,mixed>  $items
     * @return array<int,mixed>
     */
    private function shuffleDeterministic(array $items, string $seed): array
    {
        $items = array_values($items);
        $n     = count($items);
        if ($n < 2) {
            return $items;
        }

        $state = crc32($seed) & 0xFFFFFFFF;
        if ($state === 0) {
            $state = 1;
        }
        $next = function () use (&$state): int {
            $state ^= ($state << 13) & 0xFFFFFFFF;
            $state ^= ($state >> 17);
            $state ^= ($state << 5)  & 0xFFFFFFFF;
            $state &= 0xFFFFFFFF;
            return $state;
        };

        for ($i = $n - 1; $i > 0; $i--) {
            $j = $next() % ($i + 1);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }

    public function answer(Request $request, TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        // Bloquer toute réponse sur une tentative déjà terminée (audit risque #2).
        abort_if($attempt->isComplete(), 422, 'Cette tentative est déjà terminée.');

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'value'       => ['required'],
            'time_spent'  => ['nullable', 'integer'],
        ]);

        // BUG-2 — Vérifier que la question appartient bien au test de cette tentative
        $question = TestQuestion::where('id', (int) $data['question_id'])
            ->whereHas('section', fn ($q) => $q->where('test_id', $attempt->test_id))
            ->first();

        abort_unless($question !== null, 422, 'Question invalide pour cette tentative.');

        // A5 — Validation du type de la valeur selon le type de question
        // Contrat d'échelle : le front émet 1..options.max (jamais 0).
        // On borne côté serveur pour ne pas laisser passer 0/négatif/hors-plage.
        $scaleMin = (int) ($question->options['min'] ?? 1);
        $scaleMax = (int) ($question->options['max'] ?? 5);
        $valueRules = match ($question->type) {
            'scale'             => ['required', 'numeric', "between:{$scaleMin},{$scaleMax}"],
            'text'              => ['required', 'string', 'max:5000'],
            'multi', 'multiple', 'ranking'  => ['required', 'array', 'min:1'],
            default             => ['required', function ($attr, $val, $fail) {
                // Pour single/situational et tout type inconnu : refuser les tableaux
                if (is_array($val)) {
                    $fail('La valeur doit être une donnée scalaire pour ce type de question.');
                }
            }],
        };

        validator(['value' => $data['value']], ['value' => $valueRules])->validate();

        $this->engine->recordAnswer($attempt, $question->id, $data['value'], (int) ($data['time_spent'] ?? 0));

        return back();
    }

    public function complete(TestAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        try {
            $this->engine->complete($attempt);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['complete' => $e->getMessage()]);
        }

        // Sur OVH (QUEUE_CONNECTION=sync) : le job s'exécute ici même, de façon
        // synchrone. L'utilisateur attend 20-40s pendant que Claude génère la
        // synthèse. afterResponse() libère la réponse HTTP avant l'appel IA (P-07).
        GenerateAttemptInsights::dispatch($attempt->id)->afterResponse();

        $attempt->loadMissing('test');

        $redirect = redirect()->route('results.show', $attempt)
            ->with('achievement', [
                'name' => $attempt->test->name,
                'xp'   => config('gamification.xp.complete_test', 200),
            ]);

        // Easter egg « Le Doute » — révélé à la fin, jamais pendant l'épreuve :
        // signaler les révisions en direct influencerait les réponses, ce qui
        // fausserait la mesure.
        $aDoute = $attempt->answers()->where('revisions', '>=', 5)->exists();
        if ($aDoute && ! $attempt->user->hasClaimedEasterEgg('doute')) {
            $redirect->with('easter_egg', 'doute');
        }

        return $redirect;
    }

    protected function authorizeAttempt(TestAttempt $attempt): void
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
    }
}
