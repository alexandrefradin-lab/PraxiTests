<?php

namespace Praxis\Core\TestEngine;

/**
 * Normaliseur universel de résultats.
 *
 * Chaque moteur de scoring (DefaultScoringEngine, Karasek/MBI, EQi, Big Five,
 * RIASEC, Schwartz, 360°, mini-apps…) stocke le JSON `scoring` dans un format
 * qui lui est propre : tantôt une map plate `dimensions` déjà sur 100, tantôt
 * des scores nichés (`scores_dim` avec `pct`), des percentiles (`norm_scores`),
 * des sous-échelles à seuils (`karasek`, `mbi`), un score global (`score_global`
 * vs `global_score`), un libellé-phare (`profile_label`, `niveau_qe`,
 * `wellness_label`, `archetype.titre`…).
 *
 * Le template PDF ne lisait que `scoring['dimensions']` : pour tous les tests
 * dont le moteur n'expose pas cette clé (Karasek/MBI, EQi, Big Five, PraxiLink…)
 * la section résultats restait vide. Ce presenter produit une structure unique
 * exploitable par n'importe quel test :
 *
 *   [
 *     'headline'   => ['label','code','phrase','score','score_max','pct','color'] | [],
 *     'dimensions' => [ ['name','pct','level','raw','max'], … ],
 *     'subscales'  => [ ['title', 'items' => [ ['name','value','max','pct','level'], … ] ], … ],
 *     'has_data'   => bool,
 *   ]
 *
 * Aucune dépendance plugin : on lit le tableau tel qu'il est persisté.
 */
class ScoringPresenter
{
    public static function from(?array $scoring): array
    {
        $s = $scoring ?? [];

        $headline   = self::headline($s);
        $dimensions = self::dimensions($s);
        $subscales  = self::subscales($s);

        return [
            'headline'   => $headline,
            'dimensions' => $dimensions,
            'subscales'  => $subscales,
            'has_data'   => $headline !== [] || $dimensions !== [] || $subscales !== [],
        ];
    }

    /* ───────────────────────── Résultat-phare ───────────────────────── */

    private static function headline(array $s): array
    {
        $label = $s['profile_label']
            ?? ($s['archetype']['titre'] ?? null)
            ?? ($s['archetype']['label'] ?? null)
            ?? $s['niveau_qe']
            ?? $s['niveau_orateur']
            ?? $s['wellness_label']
            ?? $s['niveau']
            ?? $s['stress_level']
            ?? (is_string($s['profile'] ?? null) ? $s['profile'] : null);

        // Code lisible affiché en complément (ex. RIASEC « RIE »).
        $code = is_string($s['code'] ?? null) ? $s['code'] : null;

        $phrase = $s['phrase_qe']
            ?? $s['phrase_orateur']
            ?? $s['phrase']
            ?? ($s['archetype']['description'] ?? null);

        $score    = $s['global_score'] ?? $s['score_global'] ?? null;
        $scoreMax = $s['score_max'] ?? null;

        $pct = null;
        if (is_numeric($score)) {
            if (is_numeric($scoreMax) && $scoreMax > 0) {
                $pct = (int) round($score / $scoreMax * 100);
            } elseif ($score >= 0 && $score <= 100) {
                $pct = (int) round($score);
            }
            $pct = $pct === null ? null : max(0, min(100, $pct));
        } else {
            $score = null;
        }

        $color = $s['archetype']['couleur'] ?? null;

        if ($label === null && $phrase === null && $pct === null && $code === null) {
            return [];
        }

        return [
            'label'     => $label,
            'code'      => $code,
            'phrase'    => $phrase,
            'score'     => is_numeric($score) ? (int) round($score) : null,
            'score_max' => is_numeric($scoreMax) ? (int) round($scoreMax) : null,
            'pct'       => $pct,
            'color'     => is_string($color) ? $color : null,
        ];
    }

    /* ───────────────────────── Barres de dimensions ─────────────────── */

    private static function dimensions(array $s): array
    {
        $meta = self::meta($s);
        $norm = is_array($s['norm_scores'] ?? null) ? $s['norm_scores'] : [];

        // 1) Map plate déjà normalisée 0-100 (Default, RIASEC, Schwartz, 360°, mini-apps).
        if (self::isFlatNumeric($s['dimensions'] ?? null)) {
            return self::barsFromFlat($s['dimensions'], $meta, $norm);
        }

        // 2) Scores nichés exposant un pourcentage (Big Five `scores_dim`).
        foreach (['scores_dim', 'dim_scores'] as $k) {
            if (self::isNestedWithPct($s[$k] ?? null)) {
                return self::barsFromNested($s[$k], $meta);
            }
        }

        // 3) Percentiles d'étalonnage (norm_scores), quand disponibles.
        if ($norm !== []) {
            $bars = self::barsFromNorm($norm, $meta);
            if ($bars !== []) {
                return $bars;
            }
        }

        // 4) Dernier recours : scores bruts → ratio sur le maximum observé.
        foreach (['dim_scores', 'scores_dim', 'dimensions'] as $k) {
            if (self::isFlatNumeric($s[$k] ?? null)) {
                return self::barsFromRatio($s[$k], $meta);
            }
        }

        return [];
    }

    private static function barsFromFlat(array $dims, array $meta, array $norm): array
    {
        $bars = [];
        foreach ($dims as $id => $val) {
            if (!is_numeric($val)) {
                continue;
            }
            $pct = (int) round(max(0, min(100, (float) $val)));
            $bars[] = [
                'name'  => self::labelFor($id, $meta),
                'pct'   => $pct,
                'level' => $norm[$id]['label'] ?? null,
                'raw'   => null,
                'max'   => null,
            ];
        }
        return $bars;
    }

    private static function barsFromNested(array $dims, array $meta): array
    {
        $bars = [];
        foreach ($dims as $id => $d) {
            if (!is_array($d) || !isset($d['pct'])) {
                continue;
            }
            $bars[] = [
                'name'  => $d['label'] ?? self::labelFor($id, $meta),
                'pct'   => (int) round(max(0, min(100, (float) $d['pct']))),
                'level' => $d['niveau'] ?? $d['label_niveau'] ?? null,
                'raw'   => $d['brut'] ?? null,
                'max'   => null,
            ];
        }
        return $bars;
    }

    private static function barsFromNorm(array $norm, array $meta): array
    {
        $bars = [];
        foreach ($norm as $id => $d) {
            $pct = is_array($d) ? ($d['percentile'] ?? null) : (is_numeric($d) ? $d : null);
            if (!is_numeric($pct)) {
                continue;
            }
            $bars[] = [
                'name'  => self::labelFor($id, $meta),
                'pct'   => (int) round(max(0, min(100, (float) $pct))),
                'level' => is_array($d) ? ($d['label'] ?? null) : null,
                'raw'   => is_array($d) ? ($d['score'] ?? null) : null,
                'max'   => null,
            ];
        }
        return $bars;
    }

    private static function barsFromRatio(array $dims, array $meta): array
    {
        $max = 0.0;
        foreach ($dims as $v) {
            if (is_numeric($v)) {
                $max = max($max, (float) $v);
            }
        }
        if ($max <= 0) {
            return [];
        }
        $bars = [];
        foreach ($dims as $id => $v) {
            if (!is_numeric($v)) {
                continue;
            }
            $bars[] = [
                'name'  => self::labelFor($id, $meta),
                'pct'   => (int) round((float) $v / $max * 100),
                'level' => null,
                'raw'   => (float) $v,
                'max'   => (int) round($max),
            ];
        }
        return $bars;
    }

    /* ───────────────────────── Sous-échelles ────────────────────────── */

    private static function subscales(array $s): array
    {
        $groups = [];

        // Modèle de Karasek (PraxiCare).
        if (is_array($s['karasek'] ?? null)) {
            $k = $s['karasek'];
            $items = [];
            foreach ([
                ['Demandes psychologiques', 'demandes', 'demandes_max'],
                ['Latitude décisionnelle',  'latitude', 'latitude_max'],
                ['Soutien social',          'soutien',  'soutien_max'],
            ] as [$lbl, $vKey, $mKey]) {
                if (isset($k[$vKey])) {
                    $items[] = self::ratioItem($lbl, $k[$vKey], $k[$mKey] ?? null);
                }
            }
            if ($items) {
                $groups[] = ['title' => 'Modèle de Karasek', 'items' => $items];
            }
        }

        // Burnout MBI (PraxiCare) — avec niveau de sévérité.
        if (is_array($s['mbi'] ?? null)) {
            $m = $s['mbi'];
            $items = [];
            foreach ([
                ['Épuisement émotionnel',      'ee', 'ee_max', 'ee_severite'],
                ['Dépersonnalisation',         'dp', 'dp_max', 'dp_severite'],
                ['Accomplissement personnel',  'ap', 'ap_max', 'ap_severite'],
            ] as [$lbl, $vKey, $mKey, $sevKey]) {
                if (isset($m[$vKey])) {
                    $it = self::ratioItem($lbl, $m[$vKey], $m[$mKey] ?? null);
                    $it['level'] = $m[$sevKey] ?? null;
                    $items[] = $it;
                }
            }
            if ($items) {
                $groups[] = ['title' => 'Burnout — MBI', 'items' => $items];
            }
        }

        return $groups;
    }

    private static function ratioItem(string $name, mixed $value, mixed $max): array
    {
        $v   = is_numeric($value) ? (float) $value : null;
        $mx  = is_numeric($max) && $max > 0 ? (float) $max : null;
        $pct = ($v !== null && $mx !== null) ? (int) round(max(0, min(100, $v / $mx * 100))) : null;

        return [
            'name'  => $name,
            'value' => $v !== null ? (int) round($v) : null,
            'max'   => $mx !== null ? (int) round($mx) : null,
            'pct'   => $pct,
            'level' => null,
        ];
    }

    /* ───────────────────────── Helpers ──────────────────────────────── */

    private static function meta(array $s): array
    {
        foreach (['meta', 'meta_dimensions', 'dimensions_meta', 'types_meta', 'meta_facettes'] as $k) {
            if (is_array($s[$k] ?? null) && $s[$k] !== []) {
                return $s[$k];
            }
        }
        return [];
    }

    /** Libellé lisible d'une dimension à partir des métadonnées du test. */
    private static function labelFor(int|string $id, array $meta): string
    {
        $entry = $meta[$id] ?? null;
        if (is_array($entry)) {
            $label = $entry['label'] ?? $entry['name'] ?? $entry['titre'] ?? null;
            if (is_string($label) && $label !== '') {
                return $label;
            }
        } elseif (is_string($entry) && $entry !== '') {
            return $entry;
        }
        return ucfirst(str_replace(['_', '-'], ' ', (string) $id));
    }

    private static function isFlatNumeric(mixed $a): bool
    {
        if (!is_array($a) || $a === []) {
            return false;
        }
        foreach ($a as $v) {
            if (!is_numeric($v)) {
                return false;
            }
        }
        return true;
    }

    private static function isNestedWithPct(mixed $a): bool
    {
        if (!is_array($a) || $a === []) {
            return false;
        }
        $first = reset($a);
        return is_array($first) && isset($first['pct']);
    }
}
