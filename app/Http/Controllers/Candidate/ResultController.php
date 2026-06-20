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
        $allowed = [
            'Candidate/ResultsShow',
            'PraximetResult', 'PraxiCareResult', 'PraxiEmoResult', 'PraxiMumResult', 'PraxiValeursResult',
            // Mini-apps
            'PraxiZenResult', 'PraxiSelfResult', 'PraxiSpeakResult', 'PraxiFlowResult', 'PraxiLinkResult',
        ];
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
                ],
                default => [],
            };
        }

        return Inertia::render($page, array_merge([
            'attempt'    => $attempt,
            'result'     => $attempt->result,
            'ai_pending' => !$attempt->result?->ai_synthesis,
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

        $pdf = Pdf::loadView('pdf.results', [
            'attempt'  => $attempt,
            'brand'    => $opts['brand'],
            'org'      => $opts['org'],
            'sections' => $opts['sections'],
            'statuses' => config('praxiquest.profile.statuses', []),
        ])->setPaper(
            config('praxiquest.pdf.paper', 'a4'),
            config('praxiquest.pdf.orientation', 'portrait'),
        );

        $slug = \Illuminate\Support\Str::slug($opts['brand']['name'] ?? 'praxiquest');
        return $pdf->download("{$slug}-synthese-{$attempt->id}.pdf");
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
}
