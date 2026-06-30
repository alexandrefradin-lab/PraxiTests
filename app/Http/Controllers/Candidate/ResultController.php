<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\BuildsBrandedPdf;
use App\Models\JourneyProgress;
use App\Models\TestAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Praxis\Core\Plugins\PluginHooks;

class ResultController extends Controller
{
    use BuildsBrandedPdf;

    public function show(TestAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        $attempt->load('test', 'result');

        // Laisser chaque plugin overrider la page de résultats via un filtre.
        // Ex: PraxiCare enregistre 'results.inertia_page' → 'PraxiCareResult'
        // La whitelist est extensible : les plugins s'enregistrent via 'results.allowed_pages' (ARC-m3).
        $defaultAllowed = [
            'Candidate/ResultsShow',
            'PraximetResult', 'PraxiCareResult', 'PraxiEmoResult', 'PraxiMumResult', 'PraxiValeursResult',
            'Praxis360Result', 'PraxiFocusResult', 'PraxiSensResult',
            // Mini-apps
            'PraxiZenResult', 'PraxiSelfResult', 'PraxiSpeakResult', 'PraxiFlowResult', 'PraxiLinkResult',
        ];
        $pluginPages = PluginHooks::applyFilters('results.allowed_pages', []);
        $allowed = array_merge($defaultAllowed, is_array($pluginPages) ? $pluginPages : []);
        $page = PluginHooks::applyFilters('results.inertia_page', 'Candidate/ResultsShow', $attempt);
        if (!in_array($page, $allowed, true)) {
            $page = 'Candidate/ResultsShow';
        }

        // Injecter les props journey pour les mini-apps
        $miniAppSlugs = ['praxizen', 'praxiself', 'praxispeak', 'praxiflow', 'praxilink'];
        $testSlug     = $attempt->test->plugin_slug ?? $attempt->test->slug ?? '';

        $journeyProps = [];
        if (in_array($testSlug, $miniAppSlugs, true)) {
            $userId = auth()->id();

            $journeyClass = match($testSlug) {
                'praxizen'   => \Praxis\Plugins\PraxiZen\Data\Journey::class,
                'praxiself'  => \Praxis\Plugins\PraxiSelf\Data\Journey::class,
                'praxispeak' => \Praxis\Plugins\PraxiSpeak\Data\Journey::class,
                'praxiflow'  => \Praxis\Plugins\PraxiFlow\Data\Journey::class,
                'praxilink'  => \Praxis\Plugins\PraxiLink\Data\Journey::class,
                default      => null,
            };

            $currentDay    = JourneyProgress::currentDay($userId, $testSlug);
            $streak        = JourneyProgress::streakFor($userId, $testSlug);
            $completedArr  = JourneyProgress::completedDays($userId, $testSlug);
            $completedList = array_keys(array_filter($completedArr)); // [1, 3, 5, …]
            $rate          = JourneyProgress::completionRate($userId, $testSlug);
            $todayEntry    = ($journeyClass && class_exists($journeyClass)) ? $journeyClass::day($currentDay) : null;
            $allDays       = ($journeyClass && class_exists($journeyClass)) ? $journeyClass::days() : [];

            // Props selon ce que chaque page Vue consomme réellement
            // (basé sur les defineProps de chaque page)
            $journeyProps = match($testSlug) {
                'praxizen' => [
                    'journeyDays'     => $allDays,        // Journey::days() — les 60 entrées
                    'journeyProgress' => $completedList,  // jours complétés [1,3,5,…]
                    'currentDay'      => $currentDay,
                    'currentStreak'   => $streak,
                ],
                'praxiself' => [
                    'journeyDays'      => $completedList,
                    'journeyStreak'    => $streak,
                    'journeyToday'     => $todayEntry,
                    'journeyPhase'     => $this->journeyPhase($currentDay),
                    'journeyPhaseMeta' => $this->journeyPhaseMeta($currentDay),
                ],
                // PraxiSpeak et PraxiFlow lisent depuis result.journey — pas de props séparées
                'praxispeak', 'praxiflow' => [],
                'praxilink' => [
                    'journeyCurrentDay'     => $currentDay,
                    'journeyStreak'         => $streak,
                    'journeyCompletion'     => $rate,
                    'journeyCompletedCount' => count($completedList),
                    'journeyCompletedDays'  => $completedArr, // map { 1: true, 3: true, … }
                    'todayJourney'          => $todayEntry,
                    // Exercice du jour : choisi sur la dimension la plus faible du profil.
                    'exerciseOfTheDay'      => $this->praxilinkExerciseOfTheDay($attempt, $currentDay),
                ],
                default => [],
            };
        }

        // Feedback 360° — injecter l'agrégat des regards si un panel existe.
        $panel360 = null;
        if ($testSlug === 'praxis360') {
            $panel = \App\Models\EvaluationPanel::where('user_id', auth()->id())
                ->where('self_attempt_id', $attempt->id)
                ->first();
            $panel360 = [
                'manage_url' => route('panel360.manage', $attempt->id),
                'started'    => (bool) $panel,
                'aggregate'  => $panel ? (new \Praxis\Plugins\Praxis360\Support\PanelAggregator($panel))->build() : null,
            ];
        }

        return Inertia::render($page, array_merge([
            'attempt'    => $attempt,
            'result'     => $attempt->result,
            // ai_pending = true uniquement si la synthèse est absente ET que l'IA n'a pas échoué
            // (si ai_failed = true, le fallback est déjà écrit, pas la peine de poller)
            'ai_pending' => !$attempt->result?->ai_synthesis && !$attempt->result?->ai_failed,
            'ai_failed'  => (bool) $attempt->result?->ai_failed,
            'panel360'   => $panel360,
        ], $journeyProps));
    }

    /**
     * Endpoint JSON léger pour le polling côté Vue.
     * Appelé toutes les 5s par ResultsShow.vue quand ai_pending = true.
     */
    public function status(TestAttempt $attempt): \Illuminate\Http\JsonResponse
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        return response()->json([
            'ai_ready'   => (bool) $attempt->result?->ai_synthesis,
            'jobs_ready' => !empty($attempt->result?->suggested_jobs),
        ]);
    }

    public function pdf(TestAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        $attempt->load('test', 'result', 'user.profile');

        $opts = $this->pdfOptions();

        $slug = \Illuminate\Support\Str::slug($opts['brand']['name'] ?? 'praxiquest');

        return $this->downloadBrandedPdf('pdf.results', [
            'attempt'  => $attempt,
            'brand'    => $opts['brand'],
            'org'      => $opts['org'],
            'sections' => $opts['sections'],
            'statuses' => config('praxiquest.profile.statuses', []),
        ], "{$slug}-synthese-{$attempt->id}.pdf");
    }

    public function history()
    {
        $attempts = TestAttempt::with([
                'test:id,name,slug,description,plugin_id',
                'test.plugin:id,type',
                'result:attempt_id,ai_synthesis,suggested_jobs',
            ])
            ->where('user_id', auth()->id())
            ->select(['id', 'test_id', 'status', 'started_at', 'completed_at'])
            ->latest('started_at')
            ->get()
            // Exclure les mini-apps
            ->filter(fn ($a) => $a->test?->plugin?->type !== 'mini-app')
            // Une seule entrée par test : la plus récente
            ->groupBy('test_id')
            ->map(fn ($group) => $group->first())
            ->values()
            ->map(fn ($a) => [
                'id'               => $a->id,
                'status'           => $a->status,
                'started_at'       => $a->started_at,
                'completed_at'     => $a->completed_at,
                'test_name'        => $a->test?->name ?? 'Épreuve',
                'test_description' => $a->test?->description,
                'result_id'        => $a->result ? $a->id : null,
            ]);

        return Inertia::render('Candidate/History', [
            'attempts' => $attempts,
        ]);
    }

    // ─── Helpers phase PraxiSelf ──────────────────────────────────────────────

    private function journeyPhase(int $day): string
    {
        return match(true) {
            $day <= 15 => 'decouverte',
            $day <= 30 => 'installation',
            $day <= 45 => 'renforcement',
            default    => 'maitrise',
        };
    }

    private function journeyPhaseMeta(int $day): array
    {
        $phases = [
            'decouverte'   => ['label' => 'Découverte',   'days' => '1-15',  'emoji' => '🌱'],
            'installation' => ['label' => 'Installation', 'days' => '16-30', 'emoji' => '🌿'],
            'renforcement' => ['label' => 'Renforcement', 'days' => '31-45', 'emoji' => '🌳'],
            'maitrise'     => ['label' => 'Maîtrise',     'days' => '46-60', 'emoji' => '✨'],
        ];
        return $phases[$this->journeyPhase($day)];
    }

    // ─── Exercice du jour PraxiLink ───────────────────────────────────────────

    /**
     * Sélectionne l'exercice « scénario du jour » pour PraxiLink.
     *
     * Lu directement depuis la classe Data du plugin (comme Journey) : aucune
     * dépendance à une table. On cible en priorité la dimension la plus faible
     * du profil (axe de progrès), puis on fait tourner les exercices de cette
     * dimension selon le jour de parcours pour varier d'un jour à l'autre.
     *
     * @return array<string, mixed>|null
     */
    private function praxilinkExerciseOfTheDay(TestAttempt $attempt, int $day): ?array
    {
        $exercisesClass = \Praxis\Plugins\PraxiLink\Data\Exercises::class;
        if (! class_exists($exercisesClass)) {
            return null;
        }

        $all = $exercisesClass::all();
        if (empty($all)) {
            return null;
        }

        // Dimension la plus faible d'après le scoring stocké (axe de progrès).
        $norm = $attempt->result?->scoring['norm_scores'] ?? [];
        $weakest = null;
        if (is_array($norm) && $norm !== []) {
            asort($norm);
            $weakest = array_key_first($norm);
        }

        // Exercices ciblant cette dimension, sinon repli sur l'ensemble.
        $pool = $weakest !== null
            ? array_values(array_filter(
                $all,
                fn ($ex) => ($ex['scoring']['dimension'] ?? null) === $weakest
            ))
            : [];
        if ($pool === []) {
            $pool = array_values($all);
        }

        // Rotation déterministe par jour de parcours.
        $index = (max(1, $day) - 1) % count($pool);

        return $pool[$index];
    }
}
