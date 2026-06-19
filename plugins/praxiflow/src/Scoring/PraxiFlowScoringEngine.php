<?php

namespace Praxis\Plugins\PraxiFlow\Scoring;

use App\Models\TestAttempt;
use Praxis\Core\TestEngine\Contracts\ScoringEngineContract;
use Praxis\Plugins\PraxiFlow\Data\Exercises;

class PraxiFlowScoringEngine implements ScoringEngineContract
{
    public function key(): string
    {
        return 'praxiflow-scoring';
    }

    public function score(TestAttempt $attempt): array
    {
        // ── 1. Récolte des réponses indexées par idx (0-19) ─────────────────
        $byIdx = [];
        foreach ($attempt->answers()->with('question')->get() as $answer) {
            $idx = $answer->question->scoring['idx'] ?? null;
            if ($idx !== null) {
                $byIdx[(int) $idx] = max(1, min(4, (int) $answer->value));
            }
        }

        // ── 2. Calcul des scores bruts par dimension ─────────────────────────
        $dimensions = Exercises::dimensions();
        $rawScores  = [];
        foreach ($dimensions as $dimKey => $dimInfo) {
            $sum = 0;
            foreach ($dimInfo['questions'] as $qIdx) {
                $sum += $byIdx[$qIdx] ?? 1;
            }
            $rawScores[$dimKey] = $sum; // Plage : 4-16 (4 questions × Likert 1-4)
        }

        // ── 3. Normalisation 0-100 ──────────────────────────────────────────
        // min = 4 (4 questions × 1), max = 16 (4 questions × 4)
        $normScores = [];
        foreach ($rawScores as $dimKey => $raw) {
            $normScores[$dimKey] = (int) round(($raw - 4) / 12 * 100);
        }

        // ── 4. Score global (moyenne des normalisés) ─────────────────────────
        $globalScore = (int) round(array_sum($normScores) / count($normScores));

        // ── 5. Niveau de productivité global ────────────────────────────────
        [$niveau, $phrase] = $this->interpretGlobal($globalScore);

        // ── 6. Tri des dimensions : forces vs axes de développement ──────────
        arsort($normScores);
        $sortedKeys = array_keys($normScores);

        $topForces = array_slice($sortedKeys, 0, 2);
        $topDev    = array_filter(
            array_reverse($sortedKeys),
            fn($k) => $normScores[$k] < 50
        );
        $topDev = array_values(array_slice($topDev, 0, 2));

        // ── 7. Programme d'exercices recommandés par dimension ───────────────
        $exerciseProgram = [];
        foreach ($normScores as $dimKey => $normScore) {
            $exercises = Exercises::recommended($dimKey, $normScore);
            $exerciseProgram[$dimKey] = array_map(
                fn($e) => [
                    'id'               => $e['id'],
                    'title'            => $e['title'],
                    'category'         => $e['category'],
                    'duration_minutes' => $e['duration_minutes'],
                    'difficulty'       => $e['difficulty'],
                    'scientific_basis' => $e['scientific_basis'],
                    'instructions'     => $e['instructions'],
                ],
                $exercises
            );
        }

        // ── 8. Plan d'action 7 jours ─────────────────────────────────────────
        $weekPlan = $this->buildWeekPlan($normScores, $topDev);

        // ── 9. Statistique motivante ─────────────────────────────────────────
        $motivatingStat = $this->motivatingStat($globalScore);

        return [
            'engine'           => $this->key(),
            'dimensions'       => $dimensions,
            'raw_scores'       => $rawScores,
            'norm_scores'      => $normScores,
            'global_score'     => $globalScore,
            'niveau'           => $niveau,
            'phrase'           => $phrase,
            'top_forces'       => $topForces,
            'top_dev'          => $topDev,
            'exercise_program' => $exerciseProgram,
            'week_plan'        => $weekPlan,
            'motivating_stat'  => $motivatingStat,
            'computed_at'      => now()->toIso8601String(),
        ];
    }

    /**
     * Interprète le score global normalisé (0-100).
     *
     * @return array{0: string, 1: string}
     */
    protected function interpretGlobal(int $score): array
    {
        if ($score < 30) {
            return [
                'Productivité en construction',
                "Ta gestion du temps et de l'énergie est en phase de structuration. C'est la meilleure nouvelle : les gains qui t'attendent sont les plus importants. Quelques habitudes ciblées peuvent transformer radicalement ton quotidien.",
            ];
        }
        if ($score < 50) {
            return [
                'Productivité en développement',
                "Tu as de bonnes intuitions sur la gestion de ton temps, mais certaines zones fragilisent ta performance globale. Un travail ciblé sur tes 1-2 dimensions les plus faibles produira rapidement des effets visibles.",
            ];
        }
        if ($score < 70) {
            return [
                'Productivité solide',
                "Tu maîtrises bien les fondamentaux de la gestion du temps. Tes habitudes te protègent des pièges courants. Pour passer au niveau supérieur, il s'agit maintenant d'affiner tes systèmes et d'éliminer les dernières frictions.",
            ];
        }
        if ($score < 85) {
            return [
                'Haute productivité',
                "Tu fais partie des 20% qui pilotent vraiment leur temps et leur énergie. Tes systèmes sont robustes. Le levier à ce stade est la profondeur : aller plus loin dans le Deep Work et la gestion stratégique de l'énergie.",
            ];
        }
        return [
            'Productivité d\'élite',
            "Ton profil de gestion du temps est remarquable. Tu opères avec des systèmes que peu de professionnels ont construits. Le défi à ce niveau : protéger tes habitudes face aux sollicitations croissantes et continuer à performer dans la durée.",
        ];
    }

    /**
     * Construit un plan d'action sur 7 jours en fonction des dimensions prioritaires.
     */
    protected function buildWeekPlan(array $normScores, array $topDev): array
    {
        $allExercises = Exercises::exercises();
        $byId = [];
        foreach ($allExercises as $e) {
            $byId[$e['id']] = $e;
        }

        // Sélectionne les exercices pour les dimensions les plus faibles
        $priorityDims = array_slice($topDev, 0, 2);
        if (empty($priorityDims)) {
            // Toutes les dimensions sont bonnes → travailler les 2 premières (amélioration continue)
            arsort($normScores);
            $priorityDims = array_slice(array_keys($normScores), -2);
        }

        $plans = [
            'jour_1' => [
                'titre'        => 'Jour 1 — Diagnostic & Premier pas',
                'description'  => "Commence par comprendre tes schémas et effectuer ton premier exercice fondateur.",
                'exercice_ids' => $this->pickExercisesForDay($priorityDims, $normScores, 1),
            ],
            'jour_2' => [
                'titre'        => 'Jour 2 — Structurer ta planification',
                'description'  => "Mets en place tes MIT et ton time-blocking pour la semaine.",
                'exercice_ids' => ['planning-mit-matinal', 'time-blocking-semaine'],
            ],
            'jour_3' => [
                'titre'        => 'Jour 3 — Protéger ton focus',
                'description'  => "Applique le protocole Deep Work et la détox notifications.",
                'exercice_ids' => ['deep-work-session', 'detox-notifications'],
            ],
            'jour_4' => [
                'titre'        => 'Jour 4 — Prioriser avec méthode',
                'description'  => "Construis ta matrice Eisenhower et applique la règle des 2 minutes.",
                'exercice_ids' => ['matrice-eisenhower', 'regle-deux-minutes'],
            ],
            'jour_5' => [
                'titre'        => 'Jour 5 — Gérer ton énergie',
                'description'  => "Audit de ton chronotype et mise en place des pauses stratégiques.",
                'exercice_ids' => ['audit-energie-chronotype', 'pauses-strategiques'],
            ],
            'jour_6' => [
                'titre'        => 'Jour 6 — Vaincre la procrastination',
                'description'  => "Applique le chunking et la règle des 5 secondes sur ta tâche repoussée.",
                'exercice_ids' => ['diagnostic-procrastination', 'chunking-micro-etapes'],
            ],
            'jour_7' => [
                'titre'        => 'Jour 7 — Revue & ancrage des habitudes',
                'description'  => "Effectue ta première revue hebdomadaire GTD et ton rituel de clôture.",
                'exercice_ids' => ['revue-hebdomadaire-gtd', 'rituel-cloture-journee'],
            ],
        ];

        // Enrichit avec les titres réels
        foreach ($plans as &$day) {
            $day['exercices'] = array_values(array_filter(
                array_map(fn($id) => isset($byId[$id])
                    ? ['id' => $id, 'title' => $byId[$id]['title'], 'duration_minutes' => $byId[$id]['duration_minutes']]
                    : null,
                    $day['exercice_ids']
                )
            ));
        }

        return $plans;
    }

    /**
     * Sélectionne 2 exercices appropriés pour une journée donnée en fonction des dimensions prioritaires.
     *
     * @param  array<string>  $priorityDims
     * @param  array<string, int>  $normScores
     * @param  int  $day
     * @return array<string>
     */
    protected function pickExercisesForDay(array $priorityDims, array $normScores, int $day): array
    {
        $result = [];
        foreach ($priorityDims as $dim) {
            $exercises = Exercises::recommended($dim, $normScores[$dim] ?? 50);
            if (!empty($exercises)) {
                $result[] = $exercises[0]['id'];
            }
        }
        return array_slice($result, 0, 2);
    }

    /**
     * Retourne une statistique motivante adaptée au niveau de score.
     */
    protected function motivatingStat(int $globalScore): string
    {
        if ($globalScore < 40) {
            return "Les recherches montrent que 20 minutes de planification quotidienne économisent en moyenne 2 heures de travail désorganisé. Ton premier exercice vaut 6x son temps investi.";
        }
        if ($globalScore < 60) {
            return "Les top performers planifient 60 à 70% de leur temps en avance, laissant 30-40% pour l'imprévu. Structurer ton agenda 3 jours à l'avance multiplie ton exécution par 1,5.";
        }
        if ($globalScore < 75) {
            return "Cal Newport estime que 4 heures de Deep Work par jour suffisent pour rivaliser avec des professionnels travaillant 10 heures en mode réactif. La qualité du focus surpasse la quantité.";
        }
        return "Les études sur les athlètes cognitifs de haut niveau montrent qu'ils récupèrent aussi délibérément qu'ils travaillent. Ta prochaine marge de progression est dans la qualité de ta récupération, pas dans plus d'heures.";
    }
}
