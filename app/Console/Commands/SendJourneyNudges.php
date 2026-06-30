<?php

namespace App\Console\Commands;

use App\Mail\JourneyNudgeMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * Détecte chaque soir les utilisateurs qui n'ont pas accompli
 * leur action du jour dans une mini-app à parcours journalier,
 * et leur envoie un email de relance pointant vers le questionnaire
 * sur les croyances bloquantes.
 *
 * Plugins supportés : praxilead, praxizenith, praxivision
 * (extensible : ajouter une entrée dans PLUGINS)
 *
 * Lancé automatiquement à 20h via le scheduler Laravel (routes/console.php).
 */
class SendJourneyNudges extends Command
{
    protected $signature   = 'journey:nudge {--dry-run : Affiche les destinataires sans envoyer}';
    protected $description = 'Envoie les emails de relance aux utilisateurs sans action du jour';

    /**
     * Descripteurs de chaque plugin à surveiller.
     * progress_table  — table de suivi des complétions
     * journey_table   — table de parcours (started_on)
     * user_fk         — nom de la colonne user_id dans journey_table
     * day_column      — colonne "numéro du jour" dans progress_table
     * completed_col   — colonne non-nulle si l'action est faite
     * label           — libellé humain du parcours
     * action_route    — route nommée show(day) pour l'action
     * title_source    — closure(day): string → titre de l'action du jour
     */
    private const DEDICATED_PLUGINS = [
        'praxilead' => [
            'journey_table'   => 'mgmt_journeys',
            'progress_table'  => 'mgmt_practice_progress',
            'day_column'      => 'day_index',
            'completed_col'   => 'completed_at',
            'label'           => 'Le Cap — 60 jours de management',
            'action_route'    => 'praxilead.show',
            'practices_class' => \Praxis\Plugins\PraxiLead\Data\Practices::class,
        ],
        'praxizenith' => [
            'journey_table'   => 'focus_journeys',
            'progress_table'  => 'focus_exercise_progress',
            'day_column'      => 'day_index',
            'completed_col'   => 'completed_at',
            'label'           => 'Le Sanctuaire de l\'Attention',
            'action_route'    => 'praxizenith.show',
            'practices_class' => \Praxis\Plugins\PraxiZenith\Data\Exercises::class,
        ],
        'praxivision' => [
            'journey_table'   => 'vision_journeys',
            'progress_table'  => 'vision_practice_progress',
            'day_column'      => 'day_index',
            'completed_col'   => 'completed_at',
            'label'           => 'L\'Éveilleur — 60 jours de leadership intégral',
            'action_route'    => 'praxivision.show',
            'practices_class' => \Praxis\Plugins\PraxiVision\Data\Practices::class,
        ],
    ];

    /**
     * Plugins utilisant la table partagée journey_progress (plugin_slug discriminant).
     * Ces plugins n'ont pas de table de parcours dédiée : "actif" = au moins 1 complétion.
     * Le CTA pointe vers l'index du plugin (liste des exercices du jour).
     */
    private const SHARED_PLUGINS = [
        'praxiself'  => ['label' => 'La Forge du Soi',       'route' => 'praxiself.index'],
        'praxilink'  => ['label' => "L'Art des Liens",        'route' => 'praxilink.index'],
        'praxiflow'  => ['label' => 'Le Maître du Temps',     'route' => 'praxiflow.index'],
        'praxizen'   => ['label' => 'Le Refuge Intérieur',    'route' => 'praxizen.index'],
        'praxispeak' => ['label' => 'La Voix du Héros',       'route' => 'praxispeak.index'],
    ];

    public function handle(): int
    {
        $today   = Carbon::today()->toDateString();
        $dryRun  = $this->option('dry-run');
        $total   = 0;

        // ── Plugins avec tables dédiées (praxilead, praxizenith, praxivision) ─
        foreach (self::DEDICATED_PLUGINS as $plugin => $cfg) {
            if (! Schema::hasTable($cfg['journey_table'])
                || ! Schema::hasTable($cfg['progress_table'])) {
                $this->line("  [skip] {$plugin} — tables manquantes");
                continue;
            }

            $total += $this->processPlugin($plugin, $cfg, $today, $dryRun);
        }

        // ── Plugins sur table partagée journey_progress ──────────────────────
        if (Schema::hasTable('journey_progress')) {
            foreach (self::SHARED_PLUGINS as $plugin => $cfg) {
                $total += $this->processSharedPlugin($plugin, $cfg, $today, $dryRun);
            }
        }

        $this->info("✓ Nudge journey : {$total} email(s) " . ($dryRun ? 'simulé(s)' : 'envoyé(s)'));
        return self::SUCCESS;
    }

    private function processPlugin(string $plugin, array $cfg, string $today, bool $dryRun): int
    {
        // Récupère tous les parcours actifs en excluant les désabonnés marketing (RGPD, MET-m1).
        // Le filtre est appliqué dès la requête pour éviter de charger des utilisateurs inutilement.
        $journeys = DB::table($cfg['journey_table'])
            ->select("{$cfg['journey_table']}.user_id", "{$cfg['journey_table']}.started_on")
            ->whereNotExists(function ($q) use ($cfg) {
                $q->select(DB::raw(1))
                  ->from('profiles')
                  ->whereColumn('profiles.user_id', "{$cfg['journey_table']}.user_id")
                  ->whereNotNull('profiles.marketing_unsubscribed_at');
            })
            ->get();

        $sent = 0;

        foreach ($journeys as $journey) {
            $startedOn = Carbon::parse($journey->started_on);
            $currentDay = max(1, min(60, $startedOn->startOfDay()->diffInDays(Carbon::today(), false) + 1));

            // A-t-il déjà fait l'action du jour ?
            $done = DB::table($cfg['progress_table'])
                ->where('user_id', $journey->user_id)
                ->where($cfg['day_column'], $currentDay)
                ->whereNotNull($cfg['completed_col'])
                ->exists();

            if ($done) {
                continue;
            }

            // Déjà relancé aujourd'hui pour ce plugin ?
            $alreadyNudged = DB::table('journey_nudge_logs')
                ->where('user_id', $journey->user_id)
                ->where('plugin', $plugin)
                ->where('nudged_on', $today)
                ->exists();

            if ($alreadyNudged) {
                continue;
            }

            $user = User::find($journey->user_id);
            if (! $user || ! $user->email) {
                continue;
            }

            // Garde-fou secondaire (filtre primaire déjà appliqué en requête, MET-m1).
            if ($user->profile && $user->profile->marketing_unsubscribed_at !== null) {
                continue;
            }

            // Titre de l'action du jour
            $actionTitle = $this->resolveTitle($cfg, $currentDay);

            // Streak : jours consécutifs complétés dans la progress_table dédiée
            $streak = $this->streakFromTable(
                $cfg['progress_table'],
                $cfg['day_column'],
                $cfg['completed_col'],
                $journey->user_id,
                $currentDay
            );

            if ($dryRun) {
                $this->line("  [dry-run] {$plugin} J{$currentDay} streak={$streak} → {$user->email} — {$actionTitle}");
            } else {
                Mail::to($user->email)->queue(new JourneyNudgeMail(
                    user:        $user,
                    plugin:      $plugin,
                    day:         $currentDay,
                    actionTitle: $actionTitle,
                    pluginLabel: $cfg['label'],
                    actionRoute: $cfg['action_route'],
                    routeHasDay: true,
                    streak:      $streak,
                ));

                // Log anti-doublon
                DB::table('journey_nudge_logs')->insert([
                    'user_id'    => $user->id,
                    'plugin'     => $plugin,
                    'day'        => $currentDay,
                    'nudged_on'  => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $sent++;
        }

        return $sent;
    }

    /**
     * Relance pour les plugins utilisant la table partagée journey_progress.
     * "Actif" = a complété au moins 1 exercice pour ce plugin.
     * "Pas fait aujourd'hui" = aucune complétion avec completed_at >= aujourd'hui 00:00.
     */
    private function processSharedPlugin(string $plugin, array $cfg, string $today, bool $dryRun): int
    {
        $todayStart = Carbon::today();

        // Utilisateurs ayant au moins 1 complétion pour ce plugin (= parcours démarré)
        $userIds = DB::table('journey_progress')
            ->where('plugin_slug', $plugin)
            ->whereNotNull('completed_at')
            ->pluck('user_id')
            ->unique();

        $sent = 0;

        foreach ($userIds as $userId) {
            // A-t-il fait quelque chose AUJOURD'HUI ?
            $doneToday = DB::table('journey_progress')
                ->where('user_id', $userId)
                ->where('plugin_slug', $plugin)
                ->whereNotNull('completed_at')
                ->where('completed_at', '>=', $todayStart)
                ->exists();

            if ($doneToday) {
                continue;
            }

            // Déjà relancé aujourd'hui ?
            $alreadyNudged = DB::table('journey_nudge_logs')
                ->where('user_id', $userId)
                ->where('plugin', $plugin)
                ->where('nudged_on', $today)
                ->exists();

            if ($alreadyNudged) {
                continue;
            }

            $user = User::find($userId);
            if (! $user || ! $user->email) {
                continue;
            }

            // Filtre désabonnement marketing (RGPD)
            if ($user->profile && $user->profile->marketing_unsubscribed_at !== null) {
                continue;
            }

            // Jour actuel du parcours (= dernier jour complété + 1)
            $currentDay = DB::table('journey_progress')
                ->where('user_id', $userId)
                ->where('plugin_slug', $plugin)
                ->whereNotNull('completed_at')
                ->max('day') ?? 0;
            $currentDay = min(60, $currentDay + 1);

            $actionTitle = "Exercice du jour {$currentDay}";

            // Streak via la table journey_progress partagée
            $streak = DB::table('journey_progress')
                ->where('user_id', $userId)
                ->where('plugin_slug', $plugin)
                ->whereNotNull('completed_at')
                ->orderByDesc('completed_at')
                ->pluck('completed_at')
                ->pipe(fn ($dates) => $this->computeStreakFromDates($dates));

            if ($dryRun) {
                $this->line("  [dry-run] {$plugin} J{$currentDay} streak={$streak} → {$user->email}");
            } else {
                Mail::to($user->email)->queue(new JourneyNudgeMail(
                    user:         $user,
                    plugin:       $plugin,
                    day:          $currentDay,
                    actionTitle:  $actionTitle,
                    pluginLabel:  $cfg['label'],
                    actionRoute:  $cfg['route'],
                    routeHasDay:  false,
                    streak:       $streak,
                ));

                DB::table('journey_nudge_logs')->insert([
                    'user_id'    => $user->id,
                    'plugin'     => $plugin,
                    'day'        => $currentDay,
                    'nudged_on'  => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $sent++;
        }

        return $sent;
    }

    /**
     * Calcule le streak depuis une progress table dédiée (praxilead, zenith, vision).
     * Compte les jours consécutifs complétés en remontant depuis currentDay-1.
     */
    private function streakFromTable(
        string $table,
        string $dayCol,
        string $completedCol,
        int    $userId,
        int    $currentDay
    ): int {
        if ($currentDay <= 1) {
            return 0;
        }

        $completedDays = DB::table($table)
            ->where('user_id', $userId)
            ->whereNotNull($completedCol)
            ->orderByDesc($dayCol)
            ->pluck($dayCol)
            ->map(fn ($d) => (int) $d)
            ->toArray();

        $streak  = 0;
        $expected = $currentDay - 1;

        foreach ($completedDays as $day) {
            if ($day === $expected) {
                $streak++;
                $expected--;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calcule le streak depuis une collection de timestamps (journey_progress partagée).
     * Deux complétions sont "consécutives" si elles sont à ≤ 1 jour d'intervalle.
     */
    private function computeStreakFromDates(\Illuminate\Support\Collection $dates): int
    {
        if ($dates->isEmpty()) {
            return 0;
        }

        $today    = Carbon::today();
        $lastDate = Carbon::parse($dates->first())->startOfDay();

        // Si la dernière complétion date d'avant-hier → streak rompu
        if ($lastDate->diffInDays($today) > 1) {
            return 0;
        }

        $streak  = 1;
        $current = $lastDate;

        foreach ($dates->skip(1) as $ts) {
            $date = Carbon::parse($ts)->startOfDay();
            if ($current->diffInDays($date) <= 1) {
                $streak++;
                $current = $date;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Retrouve le titre de l'item au jour $day depuis la classe Data du plugin.
     */
    private function resolveTitle(array $cfg, int $day): string
    {
        try {
            $class = $cfg['practices_class'];
            $all   = $class::all();
            $item  = collect($all)->firstWhere('day', $day)
                  ?? collect($all)->get($day - 1);

            return $item?->title ?? "Action du jour {$day}";
        } catch (\Throwable) {
            return "Action du jour {$day}";
        }
    }
}
