<?php

namespace Praxis\Core\Orientation;

use App\Models\CareerPath;
use App\Models\Profile;
use App\Models\ProfilePathMatch;
use App\Models\TestAttempt;
use Illuminate\Support\Collection;

/**
 * PtpPathService — calcule les pistes métiers dynamiques (PTP) d'un profil.
 *
 * Principe : le score des tests ne bouge jamais (fit figé) ; ce qui évolue, c'est
 * l'écart de formation et donc le palier de la piste, quand la personne déclare
 * des acquis. Lot 1 (MVP) : 100 % déterministe, sans IA ni API externe.
 *
 * Voir PLAN-PISTES-DYNAMIQUES-PTP.md.
 */
class PtpPathService
{
    /**
     * Recalcule toutes les pistes pour un profil et renvoie les meilleures,
     * classées par indice d'opportunité décroissant.
     */
    public function recompute(Profile $profile): Collection
    {
        $dims           = $this->aggregateDimensions($profile);
        $formationCredit = $this->declaredFormationCredit($profile);

        $matches = collect();

        foreach (CareerPath::where('active', true)->get() as $path) {
            $fit  = $this->computeFit($dims, $path->fit_dimensions ?? []);
            $gap  = max(0, (int) $path->formation_months - $formationCredit);
            $tier = self::tierForGap($gap);
            $opp  = self::opportunityIndex($fit, $gap, $path->market_demand, $path->market_trend);

            $match = ProfilePathMatch::updateOrCreate(
                ['profile_id' => $profile->id, 'career_path_id' => $path->id],
                [
                    'fit_score'            => $fit,
                    'formation_gap_months' => $gap,
                    'tier'                 => $tier,
                    'opportunity_index'    => $opp,
                    'computed_at'          => now(),
                ]
            );

            $matches->push($match);
        }

        $count = (int) config('praxiquest.results.career_paths_count', 30);

        return $matches->sortByDesc('opportunity_index')->take($count)->values();
    }

    /** Vrai si des pistes ont déjà été calculées pour ce profil. */
    public function hasMatches(Profile $profile): bool
    {
        return ProfilePathMatch::where('profile_id', $profile->id)->exists();
    }

    /**
     * Renvoie les pistes prêtes pour l'affichage, groupées par palier et classées
     * par indice d'opportunité décroissant. Chaque entrée fusionne le match (calculé)
     * avec son CareerPath (référentiel) pour le composant front PathCard.
     *
     * @return array{accessible: array, ptp: array, horizon: array}
     */
    public function restitutionFor(Profile $profile): array
    {
        $count = (int) config('praxiquest.results.career_paths_count', 30);

        $matches = ProfilePathMatch::with('careerPath')
            ->where('profile_id', $profile->id)
            ->orderByDesc('opportunity_index')
            ->take($count)
            ->get();

        $grouped = ['accessible' => [], 'ptp' => [], 'horizon' => []];

        foreach ($matches as $m) {
            $path = $m->careerPath;
            if (!$path) {
                continue;
            }

            $grouped[$m->tier][] = [
                'id'                   => $m->id,
                'slug'                 => $path->slug,
                'title'                => $path->title,
                'family'               => $path->family,
                'fit_score'            => $m->fit_score,
                'formation_gap_months' => $m->formation_gap_months,
                'tier'                 => $m->tier,
                'opportunity_index'    => $m->opportunity_index,
                'unlocked'             => $m->unlocked,
                'market_demand'        => $path->market_demand,
                'market_trend'         => $path->market_trend,
                'salary_indicative'    => $path->salary_indicative,
            ];
        }

        return $grouped;
    }

    /**
     * Calcule les pistes pour UNE passation spécifique (affichage sous la page de résultats).
     *
     * Différences vs recompute() (grimoire global) :
     * — Dimensions issues de CE test uniquement (pas d'agrégat cross-tests).
     * — Filtre strict formation_months ≤ 12 (paliers accessible + ptp seulement).
     * — Boost contextuel profil : secteur / rôle / quête / extrait CV → affinité famille.
     * — Limite 15 pistes, classées par indice d'opportunité décroissant.
     * — Résultat éphémère : pas d'écriture en base (≠ ProfilePathMatch globaux).
     *
     * @return array<int, array<string, mixed>>
     */
    public function computeForAttempt(TestAttempt $attempt, ?Profile $profile): array
    {
        $scoring = $attempt->result?->scoring ?? [];

        // Dimensions de ce test. Repli sur norm_scores si dimensions absentes.
        $dims = $scoring['dimensions'] ?? [];
        if (empty($dims) && !empty($scoring['norm_scores'])) {
            $dims = $scoring['norm_scores'];
        }

        $formationCredit = $profile ? $this->declaredFormationCredit($profile) : 0;
        $contextBoosts   = $profile ? $this->contextBoosts($profile) : [];
        $count           = (int) config('praxiquest.results.career_paths_per_test', 15);

        $results = [];

        foreach (CareerPath::where('active', true)->get() as $path) {
            // Filtre strict : on n'affiche que ce qui est atteignable en ≤ 1 an.
            if ((int) $path->formation_months > 12) {
                continue;
            }

            $fit  = $this->computeFit($dims, $path->fit_dimensions ?? []);
            // Boost contextuel plafonné à +10 par famille.
            $fit  = min(100, $fit + ($contextBoosts[$path->family] ?? 0));
            $gap  = max(0, (int) $path->formation_months - $formationCredit);
            $tier = self::tierForGap($gap);
            $opp  = self::opportunityIndex($fit, $gap, $path->market_demand, $path->market_trend);

            $results[] = [
                'slug'                 => $path->slug,
                'title'                => $path->title,
                'family'               => $path->family,
                'fit_score'            => $fit,
                'formation_gap_months' => $gap,
                'tier'                 => $tier,
                'opportunity_index'    => $opp,
                'market_demand'        => $path->market_demand,
                'market_trend'         => $path->market_trend,
                'salary_indicative'    => $path->salary_indicative,
                'unlocked'             => false, // éphémère, pas de déclaration ici
            ];
        }

        usort($results, static fn ($a, $b) => $b['opportunity_index'] <=> $a['opportunity_index']);

        return array_slice($results, 0, $count);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Fonctions pures (testables sans base de données)
    // ─────────────────────────────────────────────────────────────────────────

    /** Palier de restitution selon l'écart de formation restant (en mois). */
    public static function tierForGap(int $gapMonths): string
    {
        if ($gapMonths <= 0) {
            return 'accessible';      // 0 formation
        }
        if ($gapMonths <= 12) {
            return 'ptp';             // ≤ 1 an — finançable, cœur de l'offre
        }
        return 'horizon';            // > 1 an — ambition hors PTP
    }

    /**
     * Finançabilité 0–100 : 100 si accessible, décroissance linéaire jusqu'à la
     * borne PTP (12 mois), 0 au-delà.
     */
    public static function financability(int $gapMonths): int
    {
        if ($gapMonths <= 0) {
            return 100;
        }
        if ($gapMonths > 12) {
            return 0;
        }
        return (int) round(100 - ($gapMonths / 12) * 85);  // 12 mois → ~15
    }

    /** Score de marché 0–100 = niveau de demande ± inflexion de tendance. */
    public static function marketScore(string $demand, string $trend): int
    {
        $d = ['faible' => 30, 'moyen' => 65, 'fort' => 100][$demand] ?? 50;
        $t = ['declin' => -15, 'stable' => 0, 'croissance' => 15][$trend] ?? 0;

        return max(0, min(100, $d + $t));
    }

    /**
     * Indice d'opportunité 0–100 : pondère fit (tests), finançabilité (PTP) et marché.
     * Une bonne piste coche les 3 : ça me correspond, c'est finançable, ça recrute.
     */
    public static function opportunityIndex(int $fit, int $gapMonths, string $demand, string $trend): int
    {
        $score = 0.45 * $fit
            + 0.30 * self::financability($gapMonths)
            + 0.25 * self::marketScore($demand, $trend);

        return (int) round(max(0, min(100, $score)));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Privé
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Agrège les dimensions de scoring de tous les tests complétés du candidat,
     * en moyennant par clé de dimension (0–100).
     */
    protected function aggregateDimensions(Profile $profile): array
    {
        $user = $profile->user;
        if (!$user) {
            return [];
        }

        $sums = [];
        $counts = [];

        $attempts = $user->attempts()
            ->where('status', 'completed')
            ->with('result')
            ->get();

        foreach ($attempts as $attempt) {
            $scoring    = $attempt->result?->scoring ?? [];
            $dimensions = $scoring['dimensions'] ?? [];

            foreach ($dimensions as $key => $val) {
                if (!is_numeric($val)) {
                    continue;
                }
                $sums[$key]   = ($sums[$key] ?? 0) + (float) $val;
                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }
        }

        $avg = [];
        foreach ($sums as $key => $sum) {
            $avg[$key] = $sum / max(1, $counts[$key]);
        }

        return $avg;
    }

    /**
     * Fit 0–100 : moyenne des dimensions ciblées par la piste. À défaut de
     * dimensions ciblées (ou si le candidat n'a pas encore les bons tests),
     * on retombe sur la moyenne globale, ou une valeur neutre (50).
     */
    protected function computeFit(array $dims, array $fitDimensions): int
    {
        if (empty($dims)) {
            return 50;  // pas encore de tests → neutre
        }

        $globalAvg = (int) round(array_sum($dims) / count($dims));

        if (empty($fitDimensions)) {
            return $globalAvg;
        }

        $vals = [];
        foreach ($fitDimensions as $key) {
            if (isset($dims[$key]) && is_numeric($dims[$key])) {
                $vals[] = (float) $dims[$key];
            }
        }

        if (empty($vals)) {
            return $globalAvg;
        }

        return (int) round(array_sum($vals) / count($vals));
    }

    /**
     * Crédit de formation déclaré par la personne (mécanique de déblocage Lot 1 =
     * déclaratif). Stocké dans profiles.metadata->formation_credit_months ; en
     * Lot 3 ce crédit proviendra d'une validation par module interne.
     */
    protected function declaredFormationCredit(Profile $profile): int
    {
        $meta = $profile->metadata ?? [];

        return (int) ($meta['formation_credit_months'] ?? 0);
    }

    /**
     * Boosts contextuels par famille de métiers, calculés depuis le profil (CV + quête).
     *
     * Heuristique niveau 1 : correspondance mots-clés dans rôle / secteur / problématique
     * / extrait CV (tronqué à 2 000 chars pour la perf). Chaque mot-clé trouvé ajoute +5,
     * plafond à +10 par famille. Aucune écriture en base, calcul pur.
     *
     * @return array<string, int>  Ex. : ['Numérique' => 10, 'Santé' => 5]
     */
    protected function contextBoosts(Profile $profile): array
    {
        $boosts = [];

        $ctx = mb_strtolower(implode(' ', array_filter([
            $profile->current_role                       ?? '',
            $profile->industry                           ?? '',
            $profile->problematique                      ?? '',
            mb_substr($profile->cv_extracted_text ?? '', 0, 2000),
        ])));

        if ($ctx === '') {
            return $boosts;
        }

        // Famille → mots-clés déclencheurs (correspondances partielles suffisent)
        $affinities = [
            'Numérique'           => ['digital', 'numérique', 'informatique', 'web', 'développ', 'data', 'tech', 'logiciel', 'code', 'système'],
            'Communication'       => ['communication', 'médias', 'rédact', 'journalis', 'content', 'marketing', 'publicité'],
            'Santé'               => ['santé', 'médecin', 'infirmier', 'soignant', 'médical', 'clinique', 'hôpital', 'paramédical'],
            'Social'              => ['social', 'éducatif', 'insertion', 'accompagnement', 'association', 'médiateur'],
            'Formation'           => ['formation', 'enseignement', 'pédagogie', 'formateur', 'éducation', 'tuteur'],
            'Ressources humaines' => ['ressources humaines', 'recrutement', 'paie', 'talent', 'people', ' rh '],
            'Gestion'             => ['comptable', 'gestion', 'finance', 'contrôle de gestion', 'audit', 'trésorerie'],
            'Commerce'            => ['vente', 'commercial', 'business', 'immobilier', 'négoci'],
            'Bâtiment'            => ['bâtiment', 'travaux', 'construction', 'chantier', 'électricien', 'plombier'],
            'Artisanat'           => ['artisan', 'menuisier', 'boulanger', 'artisanat', 'manuel'],
            'Restauration'        => ['restauration', 'cuisine', 'cuisinier', 'hôtellerie', 'chef', 'traiteur'],
            'Transport'           => ['transport', 'logistique', 'conducteur', 'chauffeur', 'livraison'],
            'Industrie'           => ['industrie', 'production', 'maintenance', 'technicien', 'mécanique'],
            'Accompagnement'      => ['coaching', 'accompagnement', 'bilan', 'orientation', 'conseil'],
            'Design'              => ['design', 'graphiste', 'illustration', 'créatif', 'visuel'],
            'Administration'      => ['administratif', 'secrétariat', 'accueil', 'assistant administratif'],
            'Management'          => ['management', 'manager', 'encadrement', 'responsable équipe', 'chef de projet'],
            'Relation client'     => ['relation client', 'support client', 'service client', 'téléconseiller'],
        ];

        foreach ($affinities as $family => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($ctx, $kw)) {
                    $score += 5;
                    if ($score >= 10) {
                        break;
                    }
                }
            }
            if ($score > 0) {
                $boosts[$family] = $score;
            }
        }

        return $boosts;
    }
}
