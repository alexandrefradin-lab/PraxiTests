<?php

namespace Praxis\Plugins\PraxiCog\Data;

/**
 * Données du test PraxiCog — Raisonnement & aptitude cognitive.
 *
 * ⚠ Positionnement psychométrique (validé rôle Psychologue) :
 *   Test INDICATIF d'aptitude au raisonnement. Ce n'est PAS une mesure de QI
 *   clinique et aucun item n'est repris d'un instrument protégé (WAIS, Raven,
 *   Cattell…). Tous les items sont originaux et libres de droit.
 *
 * Convention de scoring par item :
 *   'scoring' => ['dimension' => 'logique|verbal|numerique|spatial', 'correct' => <value>]
 *   La réponse est juste si answer->value === correct (voir PraxiCogScoringEngine).
 *
 * Chronométrage :
 *   'meta' => ['time_limit' => <secondes>, 'figure' => <svg?>]
 *   time_limit est lu par le runner (AttemptPlay.vue) qui affiche un compte à
 *   rebours et auto-valide en « non répondu » (value = -1) à expiration.
 *
 * Figures :
 *   Les items logiques/spatiaux portent des figures SVG générées par les
 *   helpers ci-dessous (currentColor → compatibles thème clair/sombre). Elles
 *   vivent dans meta.figure (énoncé) et option.figure (choix).
 */
class Questions
{
    // ─────────────────────────────────────────────────────────────────────
    //  Helpers SVG — génèrent des figures déterministes et vérifiables.
    // ─────────────────────────────────────────────────────────────────────

    /** Enveloppe standalone (une figure d'option). */
    private static function wrap(string $body, string $vb = '0 0 100 100'): string
    {
        return '<svg viewBox="' . $vb . '" xmlns="http://www.w3.org/2000/svg" '
             . 'fill="none" stroke="currentColor" stroke-width="4" '
             . 'stroke-linecap="round" stroke-linejoin="round">' . $body . '</svg>';
    }

    /** n points pleins alignés (1..5). */
    private static function dotsB(int $n): string
    {
        $body = '';
        $step = 100 / ($n + 1);
        for ($i = 1; $i <= $n; $i++) {
            $x = round($step * $i, 1);
            $body .= '<circle cx="' . $x . '" cy="50" r="9" fill="currentColor" stroke="none"/>';
        }
        return $body;
    }

    /** Flèche orientée. Cardinaux E/S/W/N + diagonales SE/SW/NW/NE (rotation 45°). */
    private static function arrowB(string $dir): string
    {
        $rot = [
            'E' => 0,   'SE' => 45,  'S' => 90,   'SW' => 135,
            'W' => 180, 'NW' => 225, 'N' => 270,  'NE' => 315,
        ][$dir];
        return '<g transform="rotate(' . $rot . ' 50 50)">'
             . '<line x1="18" y1="50" x2="76" y2="50"/>'
             . '<polyline points="58,30 80,50 58,70"/></g>';
    }

    /** Polygone régulier à $sides côtés, éventuellement plein. */
    private static function polyB(int $sides, bool $filled = false): string
    {
        $cx = 50; $cy = 52; $r = 38; $pts = [];
        for ($i = 0; $i < $sides; $i++) {
            $a = -M_PI / 2 + 2 * M_PI * $i / $sides;
            $pts[] = round($cx + $r * cos($a), 1) . ',' . round($cy + $r * sin($a), 1);
        }
        $fill = $filled ? 'fill="currentColor"' : '';
        return '<polygon points="' . implode(' ', $pts) . '" ' . $fill . '/>';
    }

    /** Cercle de rayon $r, éventuellement plein. */
    private static function circB(int $r, bool $filled = false): string
    {
        $fill = $filled ? 'fill="currentColor" stroke="none"' : '';
        return '<circle cx="50" cy="50" r="' . $r . '" ' . $fill . '/>';
    }

    /**
     * Grille n×n de points ; les cases listées ($cells = [[row,col],…]) sont
     * pleines. Sert aux items spatiaux (miroir / rotation).
     */
    private static function gridB(array $cells, int $n = 3): string
    {
        $body = '';
        $gap  = 100 / $n;
        $rad  = $gap * 0.30;
        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                $x = round($gap * ($c + 0.5), 1);
                $y = round($gap * ($r + 0.5), 1);
                $on = false;
                foreach ($cells as $cell) {
                    if ($cell[0] === $r && $cell[1] === $c) { $on = true; break; }
                }
                if ($on) {
                    $body .= '<rect x="' . round($x - $rad, 1) . '" y="' . round($y - $rad, 1)
                           . '" width="' . round(2 * $rad, 1) . '" height="' . round(2 * $rad, 1)
                           . '" rx="3" fill="currentColor" stroke="none"/>';
                } else {
                    $body .= '<circle cx="' . $x . '" cy="' . $y . '" r="2.5" '
                           . 'fill="currentColor" opacity="0.25" stroke="none"/>';
                }
            }
        }
        $body .= '<rect x="3" y="3" width="94" height="94" rx="6" opacity="0.35"/>';
        return $body;
    }

    /**
     * Bande horizontale : place les figures ($bodies) côte à côte puis une
     * case « ? ». Utilisé pour les énoncés de séries.
     */
    private static function strip(array $bodies): string
    {
        $cells = $bodies;
        $cells[] = '__Q__';
        $n  = count($cells);
        $cw = 100; $gap = 22;
        $tw = $n * $cw + ($n - 1) * $gap;
        $x  = 0; $inner = '';
        foreach ($cells as $b) {
            if ($b === '__Q__') {
                $inner .= '<svg x="' . $x . '" y="0" width="100" height="100" viewBox="0 0 100 100">'
                        . '<rect x="6" y="6" width="88" height="88" rx="8" stroke-dasharray="7 7" opacity="0.6"/>'
                        . '<text x="50" y="68" text-anchor="middle" font-size="54" '
                        . 'fill="currentColor" stroke="none" font-family="Georgia, serif">?</text></svg>';
            } else {
                $inner .= '<svg x="' . $x . '" y="0" width="100" height="100" viewBox="0 0 100 100">'
                        . $b . '</svg>';
            }
            $x += $cw + $gap;
        }
        return '<svg viewBox="0 0 ' . $tw . ' 100" xmlns="http://www.w3.org/2000/svg" '
             . 'fill="none" stroke="currentColor" stroke-width="4" '
             . 'stroke-linecap="round" stroke-linejoin="round">' . $inner . '</svg>';
    }

    /** Fabrique une option figurée (label lettre + figure SVG). */
    private static function figOpt(int $value, string $letter, string $body): array
    {
        return ['value' => $value, 'label' => $letter, 'figure' => self::wrap($body)];
    }

    // ─────────────────────────────────────────────────────────────────────
    //  Items
    // ─────────────────────────────────────────────────────────────────────

    public static function all(): array
    {
        // Les *Extra() partagent le même 'section' que leur domaine : le seeder
        // regroupe par section, donc les items difficiles se rangent à la suite
        // des items de base dans la même section (progression facile → difficile).
        return array_merge(
            self::logique(),
            self::verbal(),
            self::numerique(),
            self::spatial(),
            self::logiqueExtra(),
            self::verbalExtra(),
            self::numeriqueExtra(),
            self::spatialExtra(),
        );
    }

    // ── LOGIQUE — séries & matrices de figures (chrono 55 s) ──────────────
    private static function logique(): array
    {
        $sec = 'Raisonnement logique';
        $t   = 55;

        return [
            // L1 — nombre de points croissant 1,2,3 → 4. Correct en slot 2.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle figure complète logiquement la série ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::dotsB(1), self::dotsB(2), self::dotsB(3)])],
                'options' => [
                    self::figOpt(1, 'A', self::dotsB(3)),
                    self::figOpt(2, 'B', self::dotsB(4)),
                    self::figOpt(3, 'C', self::dotsB(5)),
                    self::figOpt(4, 'D', self::dotsB(2)),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 2],
            ],

            // L2 — flèche qui tourne d'1/4 de tour horaire : E,S,W → N. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'La flèche pivote d\'un quart de tour à chaque étape. Quelle est la suivante ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::arrowB('E'), self::arrowB('S'), self::arrowB('W')])],
                'options' => [
                    self::figOpt(1, 'A', self::arrowB('N')),
                    self::figOpt(2, 'B', self::arrowB('E')),
                    self::figOpt(3, 'C', self::arrowB('S')),
                    self::figOpt(4, 'D', self::arrowB('W')),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 1],
            ],

            // L3 — nombre de côtés +1 : triangle, carré, pentagone → hexagone. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Le nombre de côtés augmente à chaque étape. Quelle figure vient ensuite ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::polyB(3), self::polyB(4), self::polyB(5)])],
                'options' => [
                    self::figOpt(1, 'A', self::polyB(5)),
                    self::figOpt(2, 'B', self::polyB(4)),
                    self::figOpt(3, 'C', self::polyB(6)),
                    self::figOpt(4, 'D', self::polyB(7)),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 3],
            ],

            // L4 — taille décroissante r=40,30,20 → 10. Correct slot 4.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Les cercles rétrécissent régulièrement. Lequel complète la série ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::circB(40), self::circB(30), self::circB(20)])],
                'options' => [
                    self::figOpt(1, 'A', self::circB(20)),
                    self::figOpt(2, 'B', self::circB(30)),
                    self::figOpt(3, 'C', self::circB(25)),
                    self::figOpt(4, 'D', self::circB(10)),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 4],
            ],

            // L5 — intrus : 3 triangles + 1 carré. Correct = le carré, slot 3.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Trois de ces figures partagent une même propriété. Quelle est l\'intruse ?',
                'meta'    => ['time_limit' => $t],
                'options' => [
                    self::figOpt(1, 'A', self::polyB(3)),
                    self::figOpt(2, 'B', self::polyB(3)),
                    self::figOpt(3, 'C', self::polyB(4)),
                    self::figOpt(4, 'D', self::polyB(3)),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 3],
            ],

            // L6 — double règle : côtés +1 ET remplissage alterné.
            //      triangle vide, carré plein, pentagone vide → hexagone PLEIN. Correct slot 2.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Deux règles se combinent dans cette série. Quelle figure la complète ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::polyB(3, false), self::polyB(4, true), self::polyB(5, false)])],
                'options' => [
                    self::figOpt(1, 'A', self::polyB(6, false)),
                    self::figOpt(2, 'B', self::polyB(6, true)),
                    self::figOpt(3, 'C', self::polyB(5, true)),
                    self::figOpt(4, 'D', self::polyB(7, true)),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 2],
            ],
        ];
    }

    // ── VERBAL — analogies, intrus, synonymes (chrono 40 s) ───────────────
    private static function verbal(): array
    {
        $sec = 'Raisonnement verbal';
        $t   = 40;

        return [
            // V1 — analogie de lieu. Correct slot 2.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'MÉDECIN est à HÔPITAL ce que PROFESSEUR est à… ?',
                'options' => [
                    ['value' => 1, 'label' => 'Élève'],
                    ['value' => 2, 'label' => 'École'],
                    ['value' => 3, 'label' => 'Diplôme'],
                    ['value' => 4, 'label' => 'Cartable'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 2],
            ],
            // V2 — intrus (catégorie). Chêne = arbre parmi des fleurs. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel mot n\'appartient pas à la même catégorie que les autres ?',
                'options' => [
                    ['value' => 1, 'label' => 'Rose'],
                    ['value' => 2, 'label' => 'Tulipe'],
                    ['value' => 3, 'label' => 'Chêne'],
                    ['value' => 4, 'label' => 'Marguerite'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 3],
            ],
            // V3 — analogie d'action. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'OISEAU est à VOLER ce que POISSON est à… ?',
                'options' => [
                    ['value' => 1, 'label' => 'Nager'],
                    ['value' => 2, 'label' => 'Eau'],
                    ['value' => 3, 'label' => 'Nageoire'],
                    ['value' => 4, 'label' => 'Écaille'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 1],
            ],
            // V4 — synonyme. « éphémère » ≈ passager. Correct slot 4.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel mot est le plus proche du sens de « ÉPHÉMÈRE » ?',
                'options' => [
                    ['value' => 1, 'label' => 'Éternel'],
                    ['value' => 2, 'label' => 'Solide'],
                    ['value' => 3, 'label' => 'Rare'],
                    ['value' => 4, 'label' => 'Passager'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 4],
            ],
            // V5 — intrus (valence). Furieux ≠ émotions positives. Correct slot 2.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel mot ne va pas avec les autres ?',
                'options' => [
                    ['value' => 1, 'label' => 'Ravi'],
                    ['value' => 2, 'label' => 'Furieux'],
                    ['value' => 3, 'label' => 'Joyeux'],
                    ['value' => 4, 'label' => 'Content'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 2],
            ],
            // V6 — analogie d'opposition. JOUR ↔ NUIT. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'CHAUD est à FROID ce que JOUR est à… ?',
                'options' => [
                    ['value' => 1, 'label' => 'Soleil'],
                    ['value' => 2, 'label' => 'Matin'],
                    ['value' => 3, 'label' => 'Nuit'],
                    ['value' => 4, 'label' => 'Lumière'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 3],
            ],
        ];
    }

    // ── NUMÉRIQUE — séries de nombres & problèmes (chrono 45 s) ────────────
    private static function numerique(): array
    {
        $sec = 'Raisonnement numérique';
        $t   = 45;

        return [
            // N1 — ×2 : 2,4,8,16 → 32. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 2, 4, 8, 16, … ?',
                'options' => [
                    ['value' => 1, 'label' => '20'],
                    ['value' => 2, 'label' => '24'],
                    ['value' => 3, 'label' => '32'],
                    ['value' => 4, 'label' => '64'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 3],
            ],
            // N2 — +3 : 3,6,9,12 → 15. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 3, 6, 9, 12, … ?',
                'options' => [
                    ['value' => 1, 'label' => '15'],
                    ['value' => 2, 'label' => '14'],
                    ['value' => 3, 'label' => '16'],
                    ['value' => 4, 'label' => '18'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 1],
            ],
            // N3 — carrés parfaits : 1,4,9,16 → 25. Correct slot 4.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 1, 4, 9, 16, … ?',
                'options' => [
                    ['value' => 1, 'label' => '20'],
                    ['value' => 2, 'label' => '24'],
                    ['value' => 3, 'label' => '36'],
                    ['value' => 4, 'label' => '25'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 4],
            ],
            // N4 — Fibonacci : 1,1,2,3,5,8 → 13. Correct slot 2.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 1, 1, 2, 3, 5, 8, … ?',
                'options' => [
                    ['value' => 1, 'label' => '11'],
                    ['value' => 2, 'label' => '13'],
                    ['value' => 3, 'label' => '15'],
                    ['value' => 4, 'label' => '16'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 2],
            ],
            // N5 — différences croissantes (+4,+6,+8,+10,+12) : 2,6,12,20,30 → 42. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 2, 6, 12, 20, 30, … ?',
                'options' => [
                    ['value' => 1, 'label' => '36'],
                    ['value' => 2, 'label' => '40'],
                    ['value' => 3, 'label' => '42'],
                    ['value' => 4, 'label' => '44'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 3],
            ],
            // N6 — ÷3 : 81,27,9 → 3. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 81, 27, 9, … ?',
                'options' => [
                    ['value' => 1, 'label' => '3'],
                    ['value' => 2, 'label' => '6'],
                    ['value' => 3, 'label' => '1'],
                    ['value' => 4, 'label' => '0'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 1],
            ],
        ];
    }

    // ── SPATIAL — miroir & rotation sur grille 3×3 (chrono 60 s) ──────────
    //  Réponses dérivées des transformations : miroir vertical c→2-c,
    //  miroir horizontal r→2-r, rotation 90° horaire (r,c)→(c,2-r).
    private static function spatial(): array
    {
        $sec = 'Raisonnement spatial';
        $t   = 60;

        return [
            // S1 — miroir vertical. Réf {(0,0),(0,1),(1,0)} → {(0,2),(0,1),(1,2)}. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille est l\'image miroir de la figure ci-dessus (symétrie par un axe VERTICAL) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [0, 1], [1, 0]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 0], [0, 1], [1, 0]])),           // identique
                    self::figOpt(2, 'B', self::gridB([[2, 0], [2, 1], [1, 0]])),           // miroir horizontal
                    self::figOpt(3, 'C', self::gridB([[0, 2], [0, 1], [1, 2]])),           // ✓ miroir vertical
                    self::figOpt(4, 'D', self::gridB([[2, 2], [2, 1], [1, 2]])),           // rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 3],
            ],

            // S2 — rotation 90° horaire. Réf {(0,0),(0,1),(0,2),(1,0)} →
            //      {(0,2),(1,2),(2,2),(0,1)}. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille correspond à la figure ci-dessus après un quart de tour vers la DROITE (90° horaire) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [0, 1], [0, 2], [1, 0]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 2], [1, 2], [2, 2], [0, 1]])),   // ✓ 90° horaire
                    self::figOpt(2, 'B', self::gridB([[0, 0], [0, 1], [0, 2], [1, 0]])),   // identique
                    self::figOpt(3, 'C', self::gridB([[2, 0], [1, 0], [0, 0], [2, 1]])),   // 90° anti-horaire
                    self::figOpt(4, 'D', self::gridB([[2, 0], [2, 1], [2, 2], [1, 2]])),   // rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 1],
            ],

            // S3 — miroir horizontal. Réf {(0,0),(0,1),(1,1)} → {(2,0),(2,1),(1,1)}. Correct slot 4.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille est l\'image miroir de la figure ci-dessus (symétrie par un axe HORIZONTAL) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [0, 1], [1, 1]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 0], [0, 1], [1, 1]])),           // identique
                    self::figOpt(2, 'B', self::gridB([[0, 2], [0, 1], [1, 1]])),           // miroir vertical
                    self::figOpt(3, 'C', self::gridB([[2, 2], [2, 1], [1, 1]])),           // rotation 180
                    self::figOpt(4, 'D', self::gridB([[2, 0], [2, 1], [1, 1]])),           // ✓ miroir horizontal
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 4],
            ],

            // S4 — rotation 90° horaire. Réf {(0,1),(1,1),(2,1),(2,0)} →
            //      {(1,2),(1,1),(1,0),(0,0)}. Correct slot 2.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille correspond à la figure ci-dessus après un quart de tour vers la DROITE (90° horaire) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 1], [1, 1], [2, 1], [2, 0]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 1], [1, 1], [2, 1], [2, 0]])),   // identique
                    self::figOpt(2, 'B', self::gridB([[1, 0], [1, 1], [1, 2], [0, 0]])),   // ✓ 90° horaire
                    self::figOpt(3, 'C', self::gridB([[1, 0], [1, 1], [1, 2], [2, 2]])),   // 90° anti-horaire
                    self::figOpt(4, 'D', self::gridB([[0, 1], [1, 1], [2, 1], [0, 2]])),   // rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 2],
            ],

            // S5 — miroir vertical. Réf {(0,0),(1,0),(2,0),(2,1)} →
            //      {(0,2),(1,2),(2,2),(2,1)}. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille est l\'image miroir de la figure ci-dessus (symétrie par un axe VERTICAL) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [1, 0], [2, 0], [2, 1]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 2], [1, 2], [2, 2], [2, 1]])),   // ✓ miroir vertical
                    self::figOpt(2, 'B', self::gridB([[0, 0], [1, 0], [2, 0], [2, 1]])),   // identique
                    self::figOpt(3, 'C', self::gridB([[2, 0], [1, 0], [0, 0], [0, 1]])),   // miroir horizontal
                    self::figOpt(4, 'D', self::gridB([[2, 2], [1, 2], [0, 2], [0, 1]])),   // rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 1],
            ],

            // S6 — rotation 90° horaire. Réf {(0,0),(1,1),(2,2),(0,2)} →
            //      {(0,2),(1,1),(2,0),(2,2)}. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille correspond à la figure ci-dessus après un quart de tour vers la DROITE (90° horaire) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [1, 1], [2, 2], [0, 2]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 0], [1, 1], [2, 2], [0, 2]])),   // identique
                    self::figOpt(2, 'B', self::gridB([[2, 0], [1, 1], [0, 2], [0, 0]])),   // 90° anti-horaire
                    self::figOpt(3, 'C', self::gridB([[0, 2], [1, 1], [2, 0], [2, 2]])),   // ✓ 90° horaire
                    self::figOpt(4, 'D', self::gridB([[2, 2], [1, 1], [0, 0], [2, 0]])),   // rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 3],
            ],
        ];
    }

    // ── LOGIQUE (suite) — items plus difficiles (chrono 55 s) ─────────────
    private static function logiqueExtra(): array
    {
        $sec = 'Raisonnement logique';
        $t   = 55;

        return [
            // L7 — rotation ANTI-horaire : N, W, S → E. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'La flèche pivote toujours dans le même sens. Quelle est la suivante ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::arrowB('N'), self::arrowB('W'), self::arrowB('S')])],
                'options' => [
                    self::figOpt(1, 'A', self::arrowB('E')),
                    self::figOpt(2, 'B', self::arrowB('N')),
                    self::figOpt(3, 'C', self::arrowB('S')),
                    self::figOpt(4, 'D', self::arrowB('W')),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 1],
            ],

            // L8 — côtés en dents de scie (+2,-1,+2 → -1) : 3,5,4,6 → pentagone(5). Correct slot 2.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Le nombre de côtés suit une règle alternée. Quelle figure vient ensuite ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::polyB(3), self::polyB(5), self::polyB(4), self::polyB(6)])],
                'options' => [
                    self::figOpt(1, 'A', self::polyB(7)),
                    self::figOpt(2, 'B', self::polyB(5)),
                    self::figOpt(3, 'C', self::polyB(6)),
                    self::figOpt(4, 'D', self::polyB(4)),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 2],
            ],

            // L9 — points en dents de scie (+2,-1,+2 → -1) : 1,3,2,4 → 3. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Le nombre de points suit une règle alternée. Quelle figure complète la série ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::dotsB(1), self::dotsB(3), self::dotsB(2), self::dotsB(4)])],
                'options' => [
                    self::figOpt(1, 'A', self::dotsB(5)),
                    self::figOpt(2, 'B', self::dotsB(4)),
                    self::figOpt(3, 'C', self::dotsB(3)),
                    self::figOpt(4, 'D', self::dotsB(2)),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 3],
            ],

            // L10 — rotation à 45° horaire : E, SE, S → SW. Correct slot 4.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'La flèche tourne d\'un huitième de tour à chaque étape. Laquelle vient ensuite ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::strip([self::arrowB('E'), self::arrowB('SE'), self::arrowB('S')])],
                'options' => [
                    self::figOpt(1, 'A', self::arrowB('W')),
                    self::figOpt(2, 'B', self::arrowB('S')),
                    self::figOpt(3, 'C', self::arrowB('NE')),
                    self::figOpt(4, 'D', self::arrowB('SW')),
                ],
                'scoring' => ['dimension' => 'logique', 'correct' => 4],
            ],
        ];
    }

    // ── VERBAL (suite) — items plus difficiles (chrono 40 s) ──────────────
    private static function verbalExtra(): array
    {
        $sec = 'Raisonnement verbal';
        $t   = 40;

        return [
            // V7 — analogie partie/tout. Correct slot 2.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'DOIGT est à MAIN ce que PÉTALE est à… ?',
                'options' => [
                    ['value' => 1, 'label' => 'Tige'],
                    ['value' => 2, 'label' => 'Fleur'],
                    ['value' => 3, 'label' => 'Feuille'],
                    ['value' => 4, 'label' => 'Racine'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 2],
            ],
            // V8 — analogie outil/objet. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'MARTEAU est à CLOU ce que TOURNEVIS est à… ?',
                'options' => [
                    ['value' => 1, 'label' => 'Vis'],
                    ['value' => 2, 'label' => 'Bois'],
                    ['value' => 3, 'label' => 'Planche'],
                    ['value' => 4, 'label' => 'Métal'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 1],
            ],
            // V9 — intrus (unité de volume parmi des longueurs). Correct slot 2.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel mot n\'appartient pas à la même catégorie que les autres ?',
                'options' => [
                    ['value' => 1, 'label' => 'Kilomètre'],
                    ['value' => 2, 'label' => 'Litre'],
                    ['value' => 3, 'label' => 'Mètre'],
                    ['value' => 4, 'label' => 'Centimètre'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 2],
            ],
            // V10 — analogie de degré (intensification). tiède<brûlant, humide<trempé. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'TIÈDE est à BRÛLANT ce que HUMIDE est à… ?',
                'options' => [
                    ['value' => 1, 'label' => 'Sec'],
                    ['value' => 2, 'label' => 'Mouillé'],
                    ['value' => 3, 'label' => 'Trempé'],
                    ['value' => 4, 'label' => 'Pluie'],
                ],
                'scoring' => ['dimension' => 'verbal', 'correct' => 3],
            ],
        ];
    }

    // ── NUMÉRIQUE (suite) — items plus difficiles (chrono 45 s) ───────────
    private static function numeriqueExtra(): array
    {
        $sec = 'Raisonnement numérique';
        $t   = 45;

        return [
            // N7 — deux pas alternés (+1,+2) : 1,2,4,5,7,8 → 10. Correct slot 2.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 1, 2, 4, 5, 7, 8, … ?',
                'options' => [
                    ['value' => 1, 'label' => '9'],
                    ['value' => 2, 'label' => '10'],
                    ['value' => 3, 'label' => '11'],
                    ['value' => 4, 'label' => '14'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 2],
            ],
            // N8 — ×2+1 : 3,7,15,31 → 63. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 3, 7, 15, 31, … ?',
                'options' => [
                    ['value' => 1, 'label' => '47'],
                    ['value' => 2, 'label' => '62'],
                    ['value' => 3, 'label' => '63'],
                    ['value' => 4, 'label' => '127'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 3],
            ],
            // N9 — nombres premiers : 2,3,5,7,11 → 13. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 2, 3, 5, 7, 11, … ?',
                'options' => [
                    ['value' => 1, 'label' => '13'],
                    ['value' => 2, 'label' => '12'],
                    ['value' => 3, 'label' => '14'],
                    ['value' => 4, 'label' => '15'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 1],
            ],
            // N10 — factorielle (×2,×3,×4,×5) : 1,2,6,24 → 120. Correct slot 4.
            [
                'section' => $sec, 'type' => 'single', 'meta' => ['time_limit' => $t],
                'prompt'  => 'Quel nombre complète la suite : 1, 2, 6, 24, … ?',
                'options' => [
                    ['value' => 1, 'label' => '48'],
                    ['value' => 2, 'label' => '60'],
                    ['value' => 3, 'label' => '96'],
                    ['value' => 4, 'label' => '120'],
                ],
                'scoring' => ['dimension' => 'numerique', 'correct' => 4],
            ],
        ];
    }

    // ── SPATIAL (suite) — items plus difficiles (chrono 60 s) ─────────────
    private static function spatialExtra(): array
    {
        $sec = 'Raisonnement spatial';
        $t   = 60;

        return [
            // S7 — DEMI-tour (180°). Réf {(0,0),(0,1),(1,0)} → {(2,2),(2,1),(1,2)}. Correct slot 4.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille correspond à la figure ci-dessus après un DEMI-tour (180°) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [0, 1], [1, 0]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 0], [0, 1], [1, 0]])),           // identique
                    self::figOpt(2, 'B', self::gridB([[0, 2], [0, 1], [1, 2]])),           // miroir vertical
                    self::figOpt(3, 'C', self::gridB([[2, 0], [2, 1], [1, 0]])),           // miroir horizontal
                    self::figOpt(4, 'D', self::gridB([[2, 2], [2, 1], [1, 2]])),           // ✓ rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 4],
            ],

            // S8 — rotation 90° horaire. Réf {(0,0),(0,1),(1,0),(2,0)} →
            //      {(0,0),(0,1),(0,2),(1,2)}. Correct slot 2.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille correspond à la figure ci-dessus après un quart de tour vers la DROITE (90° horaire) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [0, 1], [1, 0], [2, 0]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 0], [0, 1], [1, 0], [2, 0]])),   // identique
                    self::figOpt(2, 'B', self::gridB([[0, 0], [0, 1], [0, 2], [1, 2]])),   // ✓ 90° horaire
                    self::figOpt(3, 'C', self::gridB([[2, 0], [1, 0], [2, 1], [2, 2]])),   // 90° anti-horaire
                    self::figOpt(4, 'D', self::gridB([[2, 2], [2, 1], [1, 2], [0, 2]])),   // rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 2],
            ],

            // S9 — miroir vertical. Réf {(0,0),(1,0),(1,1),(1,2)} →
            //      {(0,2),(1,0),(1,1),(1,2)}. Correct slot 3.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille est l\'image miroir de la figure ci-dessus (symétrie par un axe VERTICAL) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [1, 0], [1, 1], [1, 2]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[0, 0], [1, 0], [1, 1], [1, 2]])),   // identique
                    self::figOpt(2, 'B', self::gridB([[2, 0], [1, 0], [1, 1], [1, 2]])),   // miroir horizontal
                    self::figOpt(3, 'C', self::gridB([[0, 2], [1, 0], [1, 1], [1, 2]])),   // ✓ miroir vertical
                    self::figOpt(4, 'D', self::gridB([[2, 2], [1, 2], [1, 1], [1, 0]])),   // rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 3],
            ],

            // S10 — miroir horizontal. Réf {(0,0),(0,1),(0,2),(1,2)} →
            //      {(2,0),(2,1),(2,2),(1,2)}. Correct slot 1.
            [
                'section' => $sec, 'type' => 'single',
                'prompt'  => 'Quelle grille est l\'image miroir de la figure ci-dessus (symétrie par un axe HORIZONTAL) ?',
                'meta'    => ['time_limit' => $t, 'figure' => self::wrap(self::gridB([[0, 0], [0, 1], [0, 2], [1, 2]]))],
                'options' => [
                    self::figOpt(1, 'A', self::gridB([[2, 0], [2, 1], [2, 2], [1, 2]])),   // ✓ miroir horizontal
                    self::figOpt(2, 'B', self::gridB([[0, 0], [0, 1], [0, 2], [1, 2]])),   // identique
                    self::figOpt(3, 'C', self::gridB([[0, 0], [0, 1], [0, 2], [1, 0]])),   // miroir vertical
                    self::figOpt(4, 'D', self::gridB([[2, 0], [2, 1], [2, 2], [1, 0]])),   // rotation 180
                ],
                'scoring' => ['dimension' => 'spatial', 'correct' => 1],
            ],
        ];
    }

    /**
     * Métadonnées des dimensions — libellés, descriptions, couleurs.
     */
    public static function dimensions(): array
    {
        return [
            'logique' => [
                'label'       => 'Raisonnement logique',
                'description' => 'Aptitude à repérer des règles et des régularités dans des suites de figures.',
                'color'       => '#A67520',
            ],
            'verbal' => [
                'label'       => 'Raisonnement verbal',
                'description' => 'Aptitude à manipuler le sens des mots : analogies, catégories, relations.',
                'color'       => '#1C6E8C',
            ],
            'numerique' => [
                'label'       => 'Raisonnement numérique',
                'description' => 'Aptitude à identifier des logiques de progression dans des suites de nombres.',
                'color'       => '#7B1515',
            ],
            'spatial' => [
                'label'       => 'Raisonnement spatial',
                'description' => 'Aptitude à se représenter mentalement rotations et symétries de figures.',
                'color'       => '#3A6B48',
            ],
        ];
    }
}
