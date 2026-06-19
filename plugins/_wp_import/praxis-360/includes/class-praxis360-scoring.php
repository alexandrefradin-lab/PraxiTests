<?php
/**
 * Calcul des scores Praxis 360 : moyennes par dimension et par catégorie,
 * gaps d'auto-perception, anonymat (seuil >= 3 pour pairs/collaborateurs/clients).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Praxis360_Scoring {

    /** Seuil d'anonymat pour les catégories agrégées. */
    const ANON_THRESHOLD = 3;

    /** Catégories agrégées soumises au seuil d'anonymat. */
    public static function aggregated_relations() {
        return array( 'peer', 'report', 'client' );
    }

    /**
     * Calcule le rapport complet d'une campagne.
     *
     * @return array {
     *   dimensions: [dim_key => label],
     *   scores: [relation => [dim_key => float|null]],
     *   counts: [relation => int],
     *   others: [dim_key => float|null],     // moyenne tous évaluateurs (hors self)
     *   self:   [dim_key => float|null],
     *   gaps:   [dim_key => float|null],     // others - self
     *   strengths: [...], improvements: [...], blindspots: [...],
     * }
     */
    public static function compute( $campaign_id ) {
        $dimensions  = Praxis360_Items::dimensions();
        $respondents = Praxis360_DB::get_respondents( $campaign_id );

        // Regrouper les réponses par relation et par item.
        // accum[relation][item_key] = array of values
        $accum  = array();
        $counts = array(); // nb de répondants TERMINÉS par relation

        foreach ( $respondents as $resp ) {
            if ( 'completed' !== $resp->status ) {
                continue;
            }
            $rel = $resp->relation;
            if ( ! isset( $counts[ $rel ] ) ) {
                $counts[ $rel ] = 0;
            }
            $counts[ $rel ]++;

            $responses = Praxis360_DB::get_responses( $resp->id );
            foreach ( $responses as $row ) {
                if ( null === $row->value || '' === $row->value ) {
                    continue; // "Non observé" exclu.
                }
                $accum[ $rel ][ $row->item_key ][] = (int) $row->value;
            }
        }

        // Moyenne par dimension et par relation.
        $scores = array();
        foreach ( array_keys( Praxis360_Items::relations() ) as $rel ) {
            foreach ( $dimensions as $dim_key => $dim ) {
                $vals = array();
                foreach ( $dim['items'] as $item_key => $phr ) {
                    if ( ! empty( $accum[ $rel ][ $item_key ] ) ) {
                        $vals = array_merge( $vals, $accum[ $rel ][ $item_key ] );
                    }
                }
                $scores[ $rel ][ $dim_key ] = ! empty( $vals ) ? round( array_sum( $vals ) / count( $vals ), 2 ) : null;
            }
        }

        // Moyenne "ensemble des autres" (hors self), par dimension.
        $others = array();
        $self   = array();
        foreach ( $dimensions as $dim_key => $dim ) {
            $all_vals = array();
            foreach ( $accum as $rel => $items ) {
                if ( 'self' === $rel ) {
                    continue;
                }
                foreach ( $dim['items'] as $item_key => $phr ) {
                    if ( ! empty( $items[ $item_key ] ) ) {
                        $all_vals = array_merge( $all_vals, $items[ $item_key ] );
                    }
                }
            }
            $others[ $dim_key ] = ! empty( $all_vals ) ? round( array_sum( $all_vals ) / count( $all_vals ), 2 ) : null;
            $self[ $dim_key ]   = isset( $scores['self'][ $dim_key ] ) ? $scores['self'][ $dim_key ] : null;
        }

        // Gaps (others - self).
        $gaps = array();
        foreach ( $dimensions as $dim_key => $dim ) {
            if ( null !== $others[ $dim_key ] && null !== $self[ $dim_key ] ) {
                $gaps[ $dim_key ] = round( $others[ $dim_key ] - $self[ $dim_key ], 2 );
            } else {
                $gaps[ $dim_key ] = null;
            }
        }

        // Filtrer les catégories affichables :
        // - self : toujours conservée (gérée à part dans le template) ;
        // - catégories agrégées (pairs/collaborateurs/clients) : seuil d'anonymat ;
        // - manager : conservée dès 1 répondant.
        $aggregated = self::aggregated_relations();
        foreach ( array_keys( Praxis360_Items::relations() ) as $rel ) {
            if ( 'self' === $rel ) {
                continue;
            }
            $n = isset( $counts[ $rel ] ) ? $counts[ $rel ] : 0;
            $min = in_array( $rel, $aggregated, true ) ? self::ANON_THRESHOLD : 1;
            if ( $n < $min ) {
                unset( $scores[ $rel ] );
            }
        }

        // Forces / axes de progrès (selon le regard des autres).
        $ranked = array();
        foreach ( $others as $dim_key => $val ) {
            if ( null !== $val ) {
                $ranked[ $dim_key ] = $val;
            }
        }
        arsort( $ranked );
        $strengths    = array_slice( array_keys( $ranked ), 0, 3 );
        asort( $ranked );
        $improvements = array_slice( array_keys( $ranked ), 0, 3 );

        // Angles morts : gaps positifs les plus élevés (les autres notent mieux que soi).
        $gap_rank = array();
        foreach ( $gaps as $dim_key => $val ) {
            if ( null !== $val ) {
                $gap_rank[ $dim_key ] = $val;
            }
        }
        arsort( $gap_rank );
        $blindspots = array();
        foreach ( $gap_rank as $dim_key => $val ) {
            if ( $val >= 0.5 ) {
                $blindspots[] = $dim_key;
            }
        }
        $blindspots = array_slice( $blindspots, 0, 3 );

        $dim_labels = array();
        foreach ( $dimensions as $dim_key => $dim ) {
            $dim_labels[ $dim_key ] = $dim['label'];
        }

        return array(
            'dimensions'   => $dim_labels,
            'scores'       => $scores,
            'counts'       => $counts,
            'others'       => $others,
            'self'         => $self,
            'gaps'         => $gaps,
            'strengths'    => $strengths,
            'improvements' => $improvements,
            'blindspots'   => $blindspots,
        );
    }
}
