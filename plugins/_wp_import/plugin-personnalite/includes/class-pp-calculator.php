<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Calculateur Big Five.
 *
 * Scores bruts  : somme items pondérés (1–5), range 8–40 par facette, 48–240 par dim.
 * Scores T      : moyenne 50, écart-type 10, normés sur population adulte générale.
 *   T = 50 + 10 × (brut - moyenne_norm) / sd_norm
 *   Clippé à [20, 80] pour rester interprétable.
 * Scores %      : (T - 20) / 60 × 100  → utilisés pour l'affichage des barres.
 * Interprétation des plages T :
 *   T < 35  → Très bas    (< percentile 7)
 *   35–44   → Bas         (≈ percentile 7–30)
 *   45–55   → Moyen       (≈ percentile 30–70)
 *   56–65   → Élevé       (≈ percentile 70–93)
 *   T > 65  → Très élevé  (> percentile 93)
 */
class PP_Calculator {

    /** Plages de scores T et leurs étiquettes. */
    public static function get_plages() {
        return array(
            array( 'min' => 0,  'max' => 34, 'label' => 'Très bas',    'pct_label' => '0–20 %',   'niveau' => 1 ),
            array( 'min' => 35, 'max' => 44, 'label' => 'Bas',         'pct_label' => '21–40 %',  'niveau' => 2 ),
            array( 'min' => 45, 'max' => 55, 'label' => 'Moyen',       'pct_label' => '41–60 %',  'niveau' => 3 ),
            array( 'min' => 56, 'max' => 65, 'label' => 'Élevé',       'pct_label' => '61–80 %',  'niveau' => 4 ),
            array( 'min' => 66, 'max' => 99, 'label' => 'Très élevé',  'pct_label' => '81–100 %', 'niveau' => 5 ),
        );
    }

    /** Renvoie le niveau (1–5) correspondant à un score T. */
    public static function niveau_t( $t ) {
        foreach ( self::get_plages() as $p ) {
            if ( $t >= $p['min'] && $t <= $p['max'] ) return $p['niveau'];
        }
        return 3;
    }

    /** Renvoie l'étiquette textuelle d'un score T. */
    public static function label_t( $t ) {
        foreach ( self::get_plages() as $p ) {
            if ( $t >= $p['min'] && $t <= $p['max'] ) return $p['label'];
        }
        return 'Moyen';
    }

    /** Convertit un score T en pourcentage d'affichage (0–100). */
    public static function t_to_pct( $t ) {
        return (int) round( max( 0, min( 100, ( $t - 20 ) / 60 * 100 ) ) );
    }

    /**
     * Calcul principal.
     * @param  array $reponses [ question_id => 1–5 ]
     * @return array {
     *   scores_dim    : [ 'O' => [...], 'C' => [...], ... ]  (brut, T, pct, niveau, label)
     *   scores_facette: [ 'O1_FAN' => [...], ... ]
     *   score_DS      : [ brut, T, pct, niveau, label ]
     * }
     */
    public static function calculer( $reponses ) {
        $questions    = PP_Questions::get_all();
        $facettes_map = PP_Questions::get_facettes_map();
        $normes       = PP_Questions::get_normes(); // Les normes sont définies dans PP_Questions

        // Accumulateurs
        $dims = array();
        foreach ( array('O','C','E','A','N','DS') as $d ) {
            $dims[$d] = array( 'brut' => 0, 'n' => 0 );
        }
        $facs = array();
        foreach ( array_keys( $facettes_map ) as $fk ) {
            $facs[$fk] = array( 'brut' => 0, 'n' => 0 );
        }

        foreach ( $questions as $q ) {
            $id = $q['id'];
            if ( ! isset( $reponses[$id] ) ) continue;
            $val = intval( $reponses[$id] );
            if ( $val < 1 || $val > 4 ) continue;

            $score = $q['inv'] ? ( 5 - $val ) : $val;
            $dims[ $q['dim'] ]['brut'] += $score;
            $dims[ $q['dim'] ]['n']    += 1;

            if ( isset( $q['facette'] ) ) {
                $facs[ $q['facette'] ]['brut'] += $score;
                $facs[ $q['facette'] ]['n']    += 1;
            }
        }

        // ── Scores facettes ──────────────────────────────────────
        $scores_facette = array();
        foreach ( $facs as $fk => $data ) {
            if ( $data['n'] === 0 ) {
                $scores_facette[$fk] = self::empty_score();
                continue;
            }
            $brut = $data['brut'];
            $norm = $normes[$fk] ?? array( 'mean' => 24.0, 'sd' => 5.5 );
            $T    = self::compute_T( $brut, $norm['mean'], $norm['sd'] );
            $scores_facette[$fk] = array(
                'brut'   => $brut,
                'T'      => $T,
                'pct'    => self::t_to_pct( $T ),
                'niveau' => self::niveau_t( $T ),
                'label'  => self::label_t( $T ),
            );
        }

        // ── Scores dimensions (agrégés depuis les facettes) ──────
        $scores_dim = array();
        $dim_facettes = array();
        foreach ( $facettes_map as $fk => $info ) {
            $dim_facettes[ $info['dim'] ][] = $fk;
        }

        foreach ( array('O','C','E','A','N') as $dim ) {
            $brut_total = $dims[$dim]['brut'];
            // Moyenne T des facettes pour la dimension
            $t_vals = array();
            if ( isset( $dim_facettes[$dim] ) ) {
                foreach ( $dim_facettes[$dim] as $fk ) {
                    if ( isset( $scores_facette[$fk] ) ) {
                        $t_vals[] = $scores_facette[$fk]['T'];
                    }
                }
            }
            $T = count($t_vals) > 0
                ? (int) round( array_sum($t_vals) / count($t_vals) )
                : 50;
            $T = max( 20, min( 80, $T ) );

            $scores_dim[$dim] = array(
                'brut'   => $brut_total,
                'T'      => $T,
                'pct'    => self::t_to_pct( $T ),
                'niveau' => self::niveau_t( $T ),
                'label'  => self::label_t( $T ),
            );
        }

        // ── Désirabilité Sociale (% simple, pas de normes T) ────
        $ds = $dims['DS'];
        $ds_pct = $ds['n'] > 0
            ? (int) round( ( ( $ds['brut'] - $ds['n'] ) / ( 4 * $ds['n'] ) ) * 100 )
            : 0;
        $score_DS = array(
            'brut'   => $ds['brut'],
            'T'      => null,
            'pct'    => $ds_pct,
            'niveau' => self::niveau_pct( $ds_pct ),
            'label'  => self::label_t( 20 + (int)round( $ds_pct * 0.6 ) ),
        );

        return array(
            'scores_dim'     => $scores_dim,
            'scores_facette' => $scores_facette,
            'score_DS'       => $score_DS,
        );
    }

    /**
     * Profil textuel simplifié (rétro-compatibilité shortcode / mail).
     */
    public static function profil( $scores_raw ) {
        // Accepte aussi bien l'ancien format (score_O => int) que le nouveau
        $get = function( $key ) use ( $scores_raw ) {
            if ( isset( $scores_raw['scores_dim'] ) ) {
                $dim = strtoupper( str_replace( 'score_', '', $key ) );
                if ( $dim === 'DS' ) return $scores_raw['score_DS']['pct'] ?? 0;
                return $scores_raw['scores_dim'][$dim]['pct'] ?? 0;
            }
            return $scores_raw[$key] ?? 0;
        };

        $map = array(
            'score_O'  => array( 'label' => "Ouverture à l'expérience",   'seuil' => 50 ),
            'score_C'  => array( 'label' => 'Conscience & Organisation',   'seuil' => 50 ),
            'score_E'  => array( 'label' => 'Extraversion',                'seuil' => 50 ),
            'score_A'  => array( 'label' => 'Agréabilité',                 'seuil' => 50 ),
            'score_N'  => array( 'label' => 'Stabilité émotionnelle',      'seuil' => 50 ),
            'score_DS' => array( 'label' => 'Désirabilité sociale',        'seuil' => 50 ),
        );
        $profils = array();
        $textes = array(
            'score_O'  => array( 'haut' => "Vous êtes curieux(se), créatif(ve) et avide de nouvelles expériences.",     'bas' => "Vous préférez la stabilité et les approches éprouvées." ),
            'score_C'  => array( 'haut' => "Vous êtes organisé(e), fiable et orienté(e) vers les objectifs.",           'bas' => "Vous fonctionnez mieux avec de la flexibilité et de la spontanéité." ),
            'score_E'  => array( 'haut' => "Vous êtes sociable, dynamique et à l'aise dans les groupes.",              'bas' => "Vous êtes introverti(e) et rechargez vos batteries dans la solitude." ),
            'score_A'  => array( 'haut' => "Vous êtes empathique, coopératif(ve) et tourné(e) vers les autres.",        'bas' => "Vous êtes direct(e) et défendez vos positions avec fermeté." ),
            'score_N'  => array( 'haut' => "Vous ressentez les émotions avec intensité — la régulation est un axe clé.", 'bas' => "Vous êtes stable et résistant(e) au stress." ),
            'score_DS' => array( 'haut' => "Attention : certaines réponses semblent idéalisées.",                       'bas' => "Vos réponses semblent authentiques et spontanées." ),
        );
        foreach ( $map as $key => $info ) {
            $pct  = $get( $key );
            $haut = $pct >= $info['seuil'];
            $profils[$key] = array(
                'label' => $info['label'],
                'score' => $pct,
                'texte' => $haut ? $textes[$key]['haut'] : $textes[$key]['bas'],
            );
        }
        return $profils;
    }

    /**
     * Retourne les scores facettes regroupés par dimension.
     */
    public static function profil_facettes( $resultats ) {
        if ( ! isset( $resultats['scores_facette'] ) ) return array();
        $facettes_map = PP_Questions::get_facettes_map();
        $result = array();
        foreach ( $resultats['scores_facette'] as $fk => $data ) {
            $dim = $facettes_map[$fk]['dim'];
            $result[$dim][$fk] = array_merge( $data, array(
                'label' => $facettes_map[$fk]['label'],
                'desc'  => $facettes_map[$fk]['desc'],
            ));
        }
        return $result;
    }

    // ── Helpers privés ────────────────────────────────────────

    private static function compute_T( $brut, $mean, $sd ) {
        if ( $sd <= 0 ) return 50;
        $T = 50 + 10 * ( ( $brut - $mean ) / $sd );
        // Légère amplification pour profils plus contrastés
        $T = 50 + ( $T - 50 ) * 1.15;
        return (int) round( max( 20, min( 80, $T ) ) );
    }

    private static function empty_score() {
        return array( 'brut' => 0, 'T' => 50, 'pct' => 50, 'niveau' => 3, 'label' => 'Non calculé' );
    }

    private static function niveau_pct( $pct ) {
        if ( $pct <= 20 ) return 1;
        if ( $pct <= 40 ) return 2;
        if ( $pct <= 60 ) return 3;
        if ( $pct <= 80 ) return 4;
        return 5;
    }
}
