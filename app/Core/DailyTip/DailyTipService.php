<?php

namespace Praxis\Core\DailyTip;

use App\Models\DailyTipEngagement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Praxis\Core\Gamification\GamificationEngine;
use Praxis\Core\Library\ExerciseLibrary;

/**
 * Cœur du « Tip du jour » des mini-apps de la Salle du Trésor.
 *
 * Principe : chaque app expose une bibliothèque de tips sérieux (sourcés) via
 * ExerciseLibrary. Chaque jour, ce service sélectionne UN tip — différent
 * chaque jour, et qui parcourt toute la bibliothèque avant de se répéter.
 *
 * La sélection est :
 *   - déterministe (même tip toute la journée, stable d'un chargement à l'autre) ;
 *   - propre à chaque utilisateur (point de départ de la rotation décalé par un
 *     hash user×app, pour que deux personnes n'aient pas le même tip le même jour) ;
 *   - exhaustive (permutation : on voit tous les tips avant d'en revoir un).
 *
 * S'y greffe une mécanique d'assiduité (streak) récompensée en Éclats, et un
 * point d'extension de personnalisation (profil / résultats de tests).
 */
class DailyTipService
{
    /** Éclats accordés la première fois qu'un tip est appliqué dans la journée. */
    public const ECLATS_PER_APPLY = 15;

    /** Bonus de régularité aux paliers de série. */
    public const STREAK_MILESTONES = [7 => 30, 30 => 100, 100 => 300];

    public function __construct(
        protected ExerciseLibrary $library,
        protected GamificationEngine $gamification,
    ) {}

    /**
     * Le tip du jour pour cet utilisateur et cette app, enrichi de son contexte
     * (numéro du jour, pertinence selon le profil). Null si l'app n'a pas de tips.
     *
     * @return array<string,mixed>|null
     */
    public function todayFor(User $user, string $plugin, ?Carbon $date = null): ?array
    {
        $tips = $this->library->tips($plugin);
        if ($tips === []) {
            return null;
        }

        $date    = $date ?? $this->today();
        $order   = $this->rotationFor($user, $plugin, count($tips));
        $epochDay = (int) floor($date->copy()->startOfDay()->timestamp / 86400);
        $tip     = $tips[$order[$epochDay % count($tips)]];

        // Personnalisation : marque le tip comme « choisi pour toi » si ses tags
        // recoupent les axes du profil de la personne.
        $profileTags = $this->profileTags($user);
        $matched     = array_values(array_intersect($tip['tags'] ?? [], $profileTags));

        return array_merge($tip, [
            'plugin'        => $plugin,
            'date'          => $date->toDateString(),
            'day_number'    => ($epochDay % count($tips)) + 1,
            'library_size'  => count($tips),
            'personalized'  => $matched !== [],
            'matched_tags'  => $matched,
        ]);
    }

    /**
     * État d'assiduité affichable (série active, record, appliqué aujourd'hui ?).
     *
     * @return array<string,mixed>
     */
    public function engagementFor(User $user, string $plugin, ?Carbon $date = null): array
    {
        $date = $date ?? $this->today();
        $row  = $this->row($user, $plugin);

        $today     = $date->toDateString();
        $yesterday = $date->copy()->subDay()->toDateString();

        $lastApplied  = $row?->last_applied_on?->toDateString();
        $appliedToday = $lastApplied === $today;

        // Une série n'est « vivante » que si elle a été nourrie hier ou aujourd'hui.
        $activeStreak = in_array($lastApplied, [$today, $yesterday], true)
            ? (int) ($row->current_streak ?? 0)
            : 0;

        return [
            'streak'         => $activeStreak,
            'longest_streak' => (int) ($row->longest_streak ?? 0),
            'total_applied'  => (int) ($row->total_applied ?? 0),
            'applied_today'  => $appliedToday,
        ];
    }

    /**
     * Enregistre que l'utilisateur a appliqué le tip du jour. Idempotent : une
     * seule comptabilisation (et un seul gain d'Éclats) par jour.
     *
     * @return array<string,mixed> état mis à jour + éclats gagnés
     */
    public function markApplied(User $user, string $plugin, string $tipId, ?Carbon $date = null): array
    {
        $date  = $date ?? $this->today();
        $today = $date->toDateString();

        if (! $this->tableReady()) {
            return ['engagement' => $this->engagementFor($user, $plugin, $date), 'eclats_gained' => 0];
        }

        $row = $this->row($user, $plugin) ?? new DailyTipEngagement([
            'user_id'     => $user->id,
            'plugin_slug' => $plugin,
        ]);

        // Déjà appliqué aujourd'hui → no-op (pas de double récompense).
        if ($row->last_applied_on?->toDateString() === $today) {
            return [
                'engagement'    => $this->engagementFor($user, $plugin, $date),
                'eclats_gained' => 0,
            ];
        }

        $yesterday   = $date->copy()->subDay()->toDateString();
        $continues   = $row->last_applied_on?->toDateString() === $yesterday;
        $newStreak   = $continues ? ((int) $row->current_streak + 1) : 1;

        $row->current_streak  = $newStreak;
        $row->longest_streak  = max((int) $row->longest_streak, $newStreak);
        $row->total_applied   = (int) $row->total_applied + 1;
        $row->last_applied_on = $today;
        $row->last_engaged_on = $today;
        $row->last_tip_id     = $tipId;
        $row->save();

        // Récompense : gain de base + éventuel bonus de palier de série.
        $eclats = self::ECLATS_PER_APPLY + (self::STREAK_MILESTONES[$newStreak] ?? 0);
        $this->gamification->awardXp(
            $user,
            $eclats,
            "Tip du jour appliqué — {$plugin} (série {$newStreak})",
        );

        return [
            'engagement'    => $this->engagementFor($user, $plugin, $date),
            'eclats_gained' => $eclats,
        ];
    }

    /** Mémorise que le tip du jour a été vu (sans récompense). */
    public function markSeen(User $user, string $plugin, string $tipId, ?Carbon $date = null): void
    {
        if (! $this->tableReady()) {
            return;
        }

        $date = $date ?? $this->today();

        DailyTipEngagement::updateOrCreate(
            ['user_id' => $user->id, 'plugin_slug' => $plugin],
            ['last_engaged_on' => $date->toDateString(), 'last_tip_id' => $tipId],
        );
    }

    /**
     * Permutation déterministe des indices [0..n-1], propre à user×app.
     * Mélange de Fisher-Yates seedé → stable et reproductible.
     *
     * @return array<int,int>
     */
    protected function rotationFor(User $user, string $plugin, int $n): array
    {
        $order = range(0, $n - 1);
        $seed  = crc32($user->id . '|' . $plugin);

        // PRNG déterministe (xorshift) pour ne pas dépendre de mt_srand global.
        $state = $seed ?: 1;
        $rand  = function (int $max) use (&$state): int {
            $state ^= ($state << 13) & 0xFFFFFFFF;
            $state ^= ($state >> 17);
            $state ^= ($state << 5) & 0xFFFFFFFF;
            $state &= 0xFFFFFFFF;
            return $max > 0 ? $state % $max : 0;
        };

        for ($i = $n - 1; $i > 0; $i--) {
            $j = $rand($i + 1);
            [$order[$i], $order[$j]] = [$order[$j], $order[$i]];
        }

        return $order;
    }

    /**
     * Axes du profil servant à personnaliser le choix (point d'extension).
     *
     * Best-effort et tolérant : si le profil n'expose pas de tags, on renvoie
     * un tableau vide (rotation pure, aucun effet de bord). À enrichir plus tard
     * avec les résultats de tests / l'Oracle.
     *
     * @return array<int,string>
     */
    protected function profileTags(User $user): array
    {
        $tags = [];

        // Tolérant aux schémas : on ne lit que ce qui existe.
        $focus = data_get($user, 'profile.focus_tags');
        if (is_array($focus)) {
            $tags = $focus;
        } elseif (is_string($focus) && $focus !== '') {
            $tags = array_map('trim', explode(',', $focus));
        }

        return array_values(array_filter(array_map('strval', $tags)));
    }

    protected function row(User $user, string $plugin): ?DailyTipEngagement
    {
        if (! $this->tableReady()) {
            return null;
        }

        return DailyTipEngagement::query()
            ->where('user_id', $user->id)
            ->where('plugin_slug', $plugin)
            ->first();
    }

    protected function tableReady(): bool
    {
        return Schema::hasTable('daily_tip_engagements');
    }

    protected function today(): Carbon
    {
        return Carbon::now(config('app.timezone', 'Europe/Paris'));
    }
}
