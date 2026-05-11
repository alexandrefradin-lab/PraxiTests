<?php
/**
 * PraxiMet – Générateur de rapport PDF détaillé
 * Rapport complet : code RIASEC, radar, scores par type et sous-domaine,
 * descriptions, métiers, environnement de travail idéal
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiMet_PDF_Generator {

    public static function init() {
        add_action( 'wp_ajax_nopriv_praximet_export_pdf',   [ __CLASS__, 'handle_export_candidat' ] );
        add_action( 'wp_ajax_praximet_export_pdf',          [ __CLASS__, 'handle_export_candidat' ] );
        add_action( 'admin_post_praximet_admin_export_pdf', [ __CLASS__, 'handle_export_admin' ] );
    }

    // ── Export candidat ───────────────────────────────────────────────

    public static function handle_export_candidat() {
        if ( ! check_ajax_referer( 'praximet_submit', 'praximet_nonce', false ) ) {
            wp_send_json_error([ 'message' => 'Requête invalide.' ]);
        }
        $lead_id = isset( $_POST['lead_id'] ) ? (int) $_POST['lead_id'] : 0;
        $lead    = self::get_lead( $lead_id );
        if ( ! $lead ) wp_send_json_error([ 'message' => 'Lead introuvable.' ]);

        $html = self::generer_rapport( $lead );
        wp_send_json_success([ 'html' => base64_encode( $html ) ]);
    }

    // ── Export admin ──────────────────────────────────────────────────

    public static function handle_export_admin() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Accès refusé.' );
        check_admin_referer( 'praximet_admin_export_pdf' );

        $lead_id = isset( $_POST['lead_id'] ) ? (int) $_POST['lead_id'] : 0;
        $lead    = self::get_lead( $lead_id );
        if ( ! $lead ) wp_die( 'Lead introuvable.' );

        $html     = self::generer_rapport( $lead );
        $filename = 'rapport-praximet-' . sanitize_title( $lead['prenom'] . '-' . $lead['nom'] ) . '-' . date('Y-m-d') . '.html';

        header( 'Content-Type: text/html; charset=UTF-8' );
        header( 'Content-Disposition: inline; filename="' . $filename . '"' );
        echo $html;
        exit;
    }

    // ── Helpers publics ───────────────────────────────────────────────

    public static function generer_html_public( array $lead, string $code, array $scores, array $resultat ) {
        return self::generer_rapport( $lead );
    }

    private static function get_lead( int $id ) {
        if ( ! $id ) return null;
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}praximet_leads WHERE id = %d", $id ),
            ARRAY_A
        );
    }

    // ── Génération du rapport complet ─────────────────────────────────

    private static function generer_rapport( array $lead ) {
        require_once PRAXIMET_PATH . 'includes/class-riasec-engine.php';
        require_once PRAXIMET_PATH . 'data/questions-riasec.php';

        $prenom   = esc_html( $lead['prenom'] );
        $nom      = esc_html( $lead['nom'] );
        $email    = esc_html( $lead['email'] );
        $date     = date_i18n( 'd/m/Y', strtotime( $lead['created_at'] ) );
        $site     = esc_html( get_bloginfo('name') );
        $code     = $lead['code_riasec'];

        $scores = [
            'R' => (int) $lead['score_r'],
            'I' => (int) $lead['score_i'],
            'A' => (int) $lead['score_a'],
            'S' => (int) $lead['score_s'],
            'E' => (int) $lead['score_e'],
            'C' => (int) $lead['score_c'],
        ];

        $resultat = PraxiMet_Riasec_Engine::get_resultat_complet( $code );
        $libelles = ['R'=>'Réaliste','I'=>'Investigateur','A'=>'Artistique','S'=>'Social','E'=>'Entrepreneur','C'=>'Conventionnel'];

        // Scores par sous-domaine (reconstitués depuis les questions)
        $questions = praximet_get_questions();
        $scores_sd = [];
        foreach ( $questions as $q ) {
            $sd  = $q['sous_domaine'] ?? '';
            $type = $q['type'];
            $field = 'score_' . strtolower($type);
            if ( ! isset( $scores_sd[$sd] ) ) {
                $scores_sd[$sd] = ['type' => $type, 'total' => 0, 'max' => 0];
            }
            $scores_sd[$sd]['max']++;
        }
        // Distribuer les scores par sous-domaine proportionnellement
        $sd_counts = [];
        foreach ( $questions as $q ) {
            $sd = $q['sous_domaine'] ?? '';
            if ( ! isset($sd_counts[$sd]) ) $sd_counts[$sd] = 0;
            $sd_counts[$sd]++;
        }

        ob_start();
        include PRAXIMET_PATH . 'templates/rapport-pdf.php';
        return ob_get_clean();
    }

    // ── SVG Radar ─────────────────────────────────────────────────────

    public static function svg_radar( array $scores, string $code, int $size = 220 ) {
        $axes = ['R','I','A','S','E','C'];
        $labs = ['R'=>'Réaliste','I'=>'Investigateur','A'=>'Artistique','S'=>'Social','E'=>'Entrepreneur','C'=>'Conventionnel'];
        $max  = 14;
        $cx   = $size / 2;
        $cy   = $size / 2;
        $r    = $size * 0.35;
        $n    = 6;

        $pt = function($idx, $val) use ($max, $r, $cx, $cy, $n) {
            $a = (M_PI * 2 * $idx / $n) - M_PI / 2;
            $d = ($val / $max) * $r;
            return ['x' => round($cx + $d * cos($a), 2), 'y' => round($cy + $d * sin($a), 2)];
        };

        $vb = $size;
        $out = '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$vb.' '.$vb.'" xmlns="http://www.w3.org/2000/svg">';
        $out .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.($r+$size*0.07).'" fill="#eaf1fb"/>';

        // Grilles
        for ($niv = 1; $niv <= 7; $niv++) {
            $pts = [];
            foreach ($axes as $ai => $_) { $p = $pt($ai, $niv * 2); $pts[] = $p['x'].','.$p['y']; }
            $stroke = $niv === 7 ? '#2d5a8e' : '#c8d8ec';
            $sw     = $niv === 7 ? '1.2' : '0.5';
            $out .= '<polygon points="'.implode(' ',$pts).'" fill="none" stroke="'.$stroke.'" stroke-width="'.$sw.'" opacity="0.7"/>';
        }

        // Axes
        foreach ($axes as $ai => $_) {
            $p = $pt($ai, $max);
            $out .= '<line x1="'.$cx.'" y1="'.$cy.'" x2="'.$p['x'].'" y2="'.$p['y'].'" stroke="#c8d8ec" stroke-width="0.7" opacity="0.8"/>';
        }

        // Zone score
        $spts = [];
        foreach ($axes as $ai => $l) { $p = $pt($ai, $scores[$l] ?? 0); $spts[] = $p['x'].','.$p['y']; }
        $out .= '<polygon points="'.implode(' ',$spts).'" fill="#1e3a5f" fill-opacity="0.20" stroke="#1e3a5f" stroke-width="1.8" stroke-linejoin="round"/>';

        // Points
        foreach ($axes as $ai => $l) {
            $val = $scores[$l] ?? 0;
            $p   = $pt($ai, $val);
            $dom = strpos($code, $l) !== false;
            if ($dom) $out .= '<circle cx="'.$p['x'].'" cy="'.$p['y'].'" r="'.($r*0.09).'" fill="#1e3a5f" opacity="0.12"/>';
            $out .= '<circle cx="'.$p['x'].'" cy="'.$p['y'].'" r="'.($dom ? $r*0.05 : $r*0.035).'" fill="'.($dom?'#1e3a5f':'#fff').'" stroke="#1e3a5f" stroke-width="'.($dom?'0':'1.2').'"/>';
        }

        // Labels
        foreach ($axes as $ai => $l) {
            $dom = strpos($code, $l) !== false;
            $pL  = $pt($ai, $max * 1.22);
            $fw  = $dom ? '800' : '600';
            $col = $dom ? '#1e3a5f' : '#64748b';
            $fs  = round($size * 0.058);
            $out .= '<text x="'.$pL['x'].'" y="'.$pL['y'].'" text-anchor="middle" dominant-baseline="central" font-size="'.$fs.'" font-weight="'.$fw.'" fill="'.$col.'" font-family="Arial,sans-serif">'.esc_html($l).'</text>';

            $pLib = $pt($ai, $max * 1.42);
            $col2 = $dom ? '#2d5a8e' : '#94a3b8';
            $fs2  = round($size * 0.034);
            $out .= '<text x="'.$pLib['x'].'" y="'.$pLib['y'].'" text-anchor="middle" dominant-baseline="central" font-size="'.$fs2.'" font-weight="'.($dom?'600':'400').'" fill="'.$col2.'" font-family="Arial,sans-serif">'.esc_html($labs[$l]).'</text>';
        }

        $out .= '</svg>';
        return $out;
    }
}
