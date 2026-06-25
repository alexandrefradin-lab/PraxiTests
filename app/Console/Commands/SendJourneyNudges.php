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
    private const PLUGINS = [
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

    public function handle(): int
    {
        $today   = Carbon::today()->toDateString();
        $dryRun  = $this->option('dry-run');
        $total   = 0;

        foreach (self::PLUGINS as $plugin => $cfg) {
            // Vérifie que les tables existent (plugin peut ne pas être installé)
            if (! Schema::hasTable($cfg['journey_table'])
                || ! Schema::hasTable($cfg['progress_table'])) {
                $this->line("  [skip] {$plugin} — tables manquantes");
                continue;
            }

            $total += $this->processPlugin($plugin, $cfg, $today, $dryRun);
        }

        $this->info("✓ Nudge journey : {$total} email(s) " . ($dryRun ? 'simulé(s)' : 'envoyé(s)'));
        return self::SUCCESS;
    }

    private function processPlugin(string $plugin, array $cfg, string $today, bool $dryRun): int
    {
        // Récupère tous les parcours actifs
        $journeys = DB::table($cfg['journey_table'])
            ->select('user_id', 'started_on')
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

            // Ne pas envoyer aux utilisateurs désabonnés des emails marketing (TECH-03).
            if ($user->profile && $user->profile->marketing_unsubscribed_at !== null) {
                continue;
            }

            // Titre de l'action du jour
            $actionTitle = $this->resolveTitle($cfg, $currentDay);

            if ($dryRun) {
                $this->line("  [dry-run] {$plugin} J{$currentDay} → {$user->email} — {$actionTitle}");
            } else {
                Mail::to($user->email)->queue(new JourneyNudgeMail(
                    user:        $user,
                    plugin:      $plugin,
                    day:         $currentDay,
                    actionTitle: $actionTitle,
                    pluginLabel: $cfg['label'],
                    actionRoute: $cfg['action_route'],
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
