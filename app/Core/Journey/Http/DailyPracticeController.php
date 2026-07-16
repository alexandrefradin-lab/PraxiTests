<?php

namespace Praxis\Core\Journey\Http;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Gamification\RewardCatalog;
use Praxis\Core\Journey\DailyJourneyService;

/**
 * Contrôleur mutualisé des mini-apps « parcours quotidien » adossées à leurs
 * propres tables (PraxiLead, PraxiVision, PraxiZenith, PraxiMiroir…).
 *
 * La mécanique commune — gating Éclats (trésor), liste des jours, page du
 * jour, complétion + octroi d'Éclats une seule fois — vit ici. Chaque plugin
 * ne fournit que son identité (slug, modèles, pages Inertia) et, au besoin,
 * surcharge les points de variation (libellés, clés de props, champs).
 *
 * Contrat STRICT : les pages Inertia reçoivent exactement les mêmes props
 * qu'avant la mutualisation (mêmes clés, mêmes formes) — aucun changement de
 * comportement observable.
 */
abstract class DailyPracticeController extends Controller
{
    public function __construct(
        protected DailyJourneyService $journeys,
        protected GamificationEngine $gamification,
        protected RewardCatalog $rewards,
    ) {}

    // ─── Identité du parcours (à fournir par chaque plugin) ─────────────────

    /** Slug du plugin ('praxivision'…) — sert aux routes, au gating, au reason XP. */
    abstract protected function slug(): string;

    /** Classe du modèle « contenu du jour » (scopes active() + ordered() requis). */
    abstract protected function itemModel(): string;

    /** Classe du modèle de progression (scope forUser() requis). */
    abstract protected function progressModel(): string;

    /** Page Inertia du tableau de bord ('PraxiVisionIndex'…). */
    abstract protected function indexPage(): string;

    /** Page Inertia du jour ('PraxiVisionPractice'…). */
    abstract protected function showPage(): string;

    /** Éclats octroyés à la première complétion d'un jour. */
    abstract protected function eclatsPerItem(): int;

    // ─── Points de variation (surchargeables) ────────────────────────────────

    /** Clé de la liste envoyée à la page index ('practices' ou 'exercises'). */
    protected function itemsProp(): string
    {
        return 'practices';
    }

    /** Clé de l'objet envoyé à la page jour ('practice' ou 'exercise'). */
    protected function itemProp(): string
    {
        return 'practice';
    }

    /** Clé du nombre d'Éclats envoyé à la page jour. */
    protected function eclatsProp(): string
    {
        return 'eclatsPerPractice';
    }

    /** Raison passée au moteur de gamification. */
    protected function xpReason(): string
    {
        return $this->slug() . '.practice_done';
    }

    /** Message 403 quand le jour n'est pas encore débloqué. */
    protected function lockedMessage(int $daysLeft): string
    {
        return 'Cette pratique se débloquera dans ' . $daysLeft . ' jour(s).';
    }

    /** Message flash à la première complétion. */
    protected function completedMessage(): string
    {
        return 'Pratique intégrée ! +' . $this->eclatsPerItem() . ' ' . \App\Support\Parcours::xpName() . '.';
    }

    /** Message flash quand on met simplement à jour le ressenti. */
    protected function updatedMessage(): string
    {
        return 'Ressenti mis à jour.';
    }

    /** Ligne résumée d'un jour pour la page index (avant les clés d'état). */
    protected function itemSummary(Model $item): array
    {
        return [
            'day'          => $item->day_index,
            'theme'        => $item->theme,
            'title'        => $item->title,
            'summary'      => $item->summary,
            'duration_min' => $item->duration_min,
            'icon'         => $item->icon,
        ];
    }

    /** Détail complet d'un jour pour la page jour. */
    protected function itemPayload(Model $item): array
    {
        return [
            'day'             => $item->day_index,
            'theme'           => $item->theme,
            'title'           => $item->title,
            'summary'         => $item->summary,
            'body'            => $item->body,
            'micro_challenge' => $item->micro_challenge,
            'duration_min'    => $item->duration_min,
            'icon'            => $item->icon,
        ];
    }

    /** État de progression envoyé à la page jour. */
    protected function statePayload(?Model $pr): array
    {
        return [
            'completed'  => $pr?->completed_at !== null,
            'felt_score' => $pr?->felt_score,
            'notes'      => $pr?->notes,
        ];
    }

    /** Règles de validation du formulaire de complétion. */
    protected function validationRules(): array
    {
        return [
            'felt_score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** Reporte les champs validés sur la ligne de progression. */
    protected function applyProgressData(Model $pr, array $data): void
    {
        $pr->felt_score = $data['felt_score'] ?? $pr->felt_score;
        $pr->notes      = $data['notes'] ?? $pr->notes;
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();

        // Gating Éclats : la mini-app est un trésor (palier défini dans RewardCatalog).
        if (! $this->rewards->isRouteUnlocked($this->slug() . '.index', $user)) {
            $reward = $this->rewards->rewardForRoute($this->slug() . '.index');
            $seuil  = $reward['threshold'] ?? null;

            return redirect()->route('treasure.index')->with(
                'error',
                $seuil
                    ? \App\Support\Parcours::sealedMessage($seuil)
                    : (\App\Support\Parcours::isCorporate() ? "Ce module est encore verrouillé." : "Ce trésor est encore scellé.")
            );
        }

        $journey = $this->journeys->journeyFor($user);
        $current = $this->journeys->currentDay($journey);

        $progressModel = $this->progressModel();
        $progress      = $progressModel::forUser($user->id)
            ->get()
            ->keyBy('day_index');

        $itemModel = $this->itemModel();
        $items     = $itemModel::active()->ordered()->get()->map(function ($item) use ($journey, $current, $progress) {
            $pr = $progress->get($item->day_index);

            return $this->itemSummary($item) + [
                'unlocked'  => $this->journeys->isUnlocked($journey, $item->day_index),
                'completed' => $pr?->completed_at !== null,
                'is_today'  => $item->day_index === $current,
                'days_left' => $this->journeys->daysUntilUnlock($journey, $item->day_index),
            ];
        });

        $completed = $progress->whereNotNull('completed_at')->count();

        return Inertia::render($this->indexPage(), [
            'appDescription'   => $this->rewards->descriptionFor($this->slug()),
            $this->itemsProp() => $items,
            'currentDay'       => $current,
            'totalDays'        => $this->journeys->totalDays(),
            'completed'        => $completed,
            'streak'           => $this->journeys->streakFor($user),
        ]);
    }

    public function show(Request $request, int $day)
    {
        $user      = $request->user();
        $journey   = $this->journeys->journeyFor($user);
        $itemModel = $this->itemModel();
        $item      = $itemModel::active()->where('day_index', $day)->firstOrFail();

        abort_unless(
            $this->journeys->isUnlocked($journey, $day),
            403,
            $this->lockedMessage($this->journeys->daysUntilUnlock($journey, $day))
        );

        $progressModel = $this->progressModel();
        $pr            = $progressModel::forUser($user->id)
            ->where('day_index', $day)
            ->first();

        return Inertia::render($this->showPage(), [
            $this->itemProp() => $this->itemPayload($item),
            'state'           => $this->statePayload($pr),
            'nav'             => [
                'prev' => $day > 1 ? $day - 1 : null,
                'next' => ($day < $this->journeys->totalDays() && $this->journeys->isUnlocked($journey, $day + 1))
                    ? $day + 1
                    : null,
            ],
            $this->eclatsProp() => $this->eclatsPerItem(),
        ]);
    }

    public function complete(Request $request, int $day)
    {
        $user      = $request->user();
        $journey   = $this->journeys->journeyFor($user);
        $itemModel = $this->itemModel();
        $item      = $itemModel::active()->where('day_index', $day)->firstOrFail();

        abort_unless($this->journeys->isUnlocked($journey, $day), 403);

        $data = $request->validate($this->validationRules());

        $progressModel = $this->progressModel();
        $pr            = $progressModel::firstOrNew([
            'user_id'   => $user->id,
            'day_index' => $day,
        ]);

        $firstTime = $pr->completed_at === null;

        $pr->completed_at = $pr->completed_at ?? now();
        $this->applyProgressData($pr, $data);

        // Octroi d'Éclats une seule fois par jour complété.
        if ($firstTime && ! $pr->eclats_awarded) {
            $this->gamification->awardXp(
                $user,
                $this->eclatsPerItem(),
                $this->xpReason(),
                null,
                ['day' => $day, 'title' => $item->title],
                false,
            );
            $pr->eclats_awarded = true;
        }

        $pr->save();

        return back()->with(
            'success',
            $firstTime ? $this->completedMessage() : $this->updatedMessage()
        );
    }
}
