<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reprise de l'acquis : les mini-apps déjà accessibles sous l'ancien système
 * (déblocage automatique dès que le cumul d'Éclats franchissait le seuil) sont
 * inscrites en base pour que personne ne perde un accès qu'il possédait —
 * l'interface promet des trésors « révélés pour toujours ».
 *
 * cost_eclats = 0 : ces déblocages sont OFFERTS, ils ne doivent pas vider
 * rétroactivement le portefeuille (sinon un candidat à 5000 Éclats se
 * retrouverait à sec juste après la mise à jour).
 *
 * Lecture directe des tables (pas de service applicatif) : une migration doit
 * rester valable même si le code évolue par la suite.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mini_app_unlocks')
            || ! Schema::hasTable('plugins')
            || ! Schema::hasTable('gamification_progress')) {
            return;
        }

        $thresholds = $this->thresholds();

        if ($thresholds === []) {
            return;
        }

        $now = now();

        $insert = [];

        // cursor() plutôt que chunk() : chunk pagine avec LIMIT/OFFSET, ce qui
        // sur une requête agrégée (GROUP BY) rejoue l'agrégat à chaque page.
        $rows = DB::table('gamification_progress')
            ->select('user_id', DB::raw('SUM(xp_total) as total'))
            ->groupBy('user_id')
            ->cursor();

        foreach ($rows as $row) {
            foreach ($thresholds as $slug => $threshold) {
                if ((int) $row->total >= $threshold) {
                    $insert[] = [
                        'user_id'     => $row->user_id,
                        'plugin_slug' => $slug,
                        'cost_eclats' => 0,
                        'unlocked_at' => $now,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }
            }

            if (count($insert) >= 500) {
                $this->flush($insert);
                $insert = [];
            }
        }

        $this->flush($insert);
    }

    public function down(): void
    {
        if (Schema::hasTable('mini_app_unlocks')) {
            // Ne supprime que les déblocages offerts par la reprise ; les
            // achats réels du candidat sont conservés.
            DB::table('mini_app_unlocks')->where('cost_eclats', 0)->delete();
        }
    }

    /**
     * insertOrIgnore : la migration doit pouvoir être rejouée sans buter sur
     * l'unique (user_id, plugin_slug), et ne doit jamais écraser un vrai achat.
     */
    private function flush(array $rows): void
    {
        if ($rows !== []) {
            DB::table('mini_app_unlocks')->insertOrIgnore($rows);
        }
    }

    /** @return array<string,int> slug de plugin => seuil d'Éclats de l'ancien système */
    private function thresholds(): array
    {
        $out = [];

        foreach (DB::table('plugins')->where('enabled', true)->get(['slug', 'manifest']) as $plugin) {
            $manifest = is_string($plugin->manifest)
                ? json_decode($plugin->manifest, true)
                : (array) $plugin->manifest;

            $threshold = $manifest['reward']['threshold_eclats'] ?? null;

            if ($threshold !== null) {
                $out[$plugin->slug] = (int) $threshold;
            }
        }

        return $out;
    }
};
