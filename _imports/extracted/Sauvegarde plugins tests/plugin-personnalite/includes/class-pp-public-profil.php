<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gère l'URL publique /profil/[token]
 * Ajoute une rewrite rule au flush d'activation.
 */
class PP_Public_Profil {

    public static function init() {
        add_action( 'init',                  array( __CLASS__, 'add_rewrite' ) );
        add_filter( 'query_vars',            array( __CLASS__, 'add_query_var' ) );
        add_filter( 'document_title_parts',  array( __CLASS__, 'filter_title' ) );
        add_filter( 'pre_get_document_title',  array( __CLASS__, 'filter_title_str' ) );
        add_filter( 'the_title',               array( __CLASS__, 'filter_page_title' ), 10, 2 );
        add_action( 'wp_head',                 array( __CLASS__, 'inject_title_css' ) );
        add_filter( 'template_include',          array( __CLASS__, 'force_page_template' ), 99 );
        add_action( 'template_redirect',     array( __CLASS__, 'render' ) );
        add_action( 'wp_ajax_pp_compat',        array( __CLASS__, 'handle_compat' ) );
        add_action( 'wp_ajax_nopriv_pp_compat', array( __CLASS__, 'handle_compat' ) );
    }

    public static function add_rewrite() {
        add_rewrite_rule( '^profil/([a-f0-9]{32})/?$', 'index.php?pp_token=$matches[1]', 'top' );
        add_rewrite_rule( '^equipe/([a-f0-9]{16})/?$', 'index.php?pp_equipe=$matches[1]', 'top' );
    }

    // Force le template "page" pour les URLs /profil/ afin qu'Avada affiche le header
    public static function force_page_template( $template ) {
        if ( ! get_query_var( 'pp_token', '' ) && ! get_query_var( 'pp_equipe', '' ) ) {
            return $template;
        }
        // Chercher un template de page dans le thème
        $page_template = locate_template( array( 'page.php', 'single.php', 'index.php' ) );
        if ( $page_template ) {
            return $page_template;
        }
        return $template;
    }

    // Masque le titre H1 affiché par Avada sur les pages profil via CSS
    public static function inject_title_css() {
        if ( ! get_query_var( 'pp_token', '' ) && ! get_query_var( 'pp_equipe', '' ) && ! get_query_var( 'pp_delete_token', '' ) && ! get_query_var( 'pp_export_token', '' ) ) return;
        // Cibler uniquement la barre de titre de page, pas le header/menu
        echo '<style>
            .fusion-page-title-bar,
            .fusion-page-title-row,
            .fusion-page-title-wrapper { display:none !important; }
        </style>';
    }

    // Filtre le titre des posts/pages
    public static function filter_page_title( $title, $id = null ) {
        if ( get_query_var( 'pp_token', '' ) || get_query_var( 'pp_equipe', '' ) ) {
            return '';
        }
        return $title;
    }

    public static function filter_title( $title ) {
        if ( get_query_var( 'pp_token', '' ) ) {
            $title['title'] = 'Profil de personnalité';
            unset( $title['tagline'] );
        }
        if ( get_query_var( 'pp_equipe', '' ) ) {
            $title['title'] = 'Profil équipe';
            unset( $title['tagline'] );
        }
        return $title;
    }

    public static function filter_title_str( $title ) {
        if ( get_query_var( 'pp_token', '' ) ) {
            return 'Profil de personnalité';
        }
        if ( get_query_var( 'pp_equipe', '' ) ) {
            return 'Profil équipe';
        }
        return $title;
    }

    public static function add_query_var( $vars ) {
        $vars[] = 'pp_token';
        $vars[] = 'pp_equipe';
        return $vars;
    }

    public static function render() {
        // Vue équipe
        $equipe_token = get_query_var( 'pp_equipe', '' );
        if ( ! empty( $equipe_token ) ) {
            $equipe_token = preg_replace('/[^a-f0-9]/', '', sanitize_text_field($equipe_token));
            if ( strlen($equipe_token) !== 16 ) { status_header(404); wp_die('Token invalide.'); }
            self::render_equipe( $equipe_token );
            return;
        }

        $token = sanitize_text_field( get_query_var( 'pp_token', '' ) );
        if ( empty($token) ) return;
        if ( ! preg_match('/^[a-f0-9]{32}$/', $token) ) {
            if ( class_exists('PP_Logger') ) PP_Logger::warning('profil','Token malformé',array('token'=>substr($token,0,16)));
            status_header(404); wp_die('Profil introuvable.');
        }

        $row = PP_DB::get_by_token( $token );
        if ( ! $row ) {
            wp_die( 'Profil introuvable ou lien expiré.', 'Profil non trouvé', array('response'=>404) );
        }

        // Téléchargement PDF
        if ( isset($_GET['pp_pdf']) ) {
            PP_PDF::handle_download( $token );
            exit;
        }

        $scores_data = json_decode( $row->scores, true ) ?: array();
        $arch_data   = json_decode( $row->archetype_data, true ) ?: array();

        // Toujours recharger les textes depuis PP_Archetypes pour avoir les versions à jour
        if ( ! empty( $scores_data['scores_dim'] ) ) {
            $arch_fresh = PP_Archetypes::detecter( $scores_data['scores_dim'] );
            foreach ( array('description','tagline','traits','rarete') as $field ) {
                if ( ! empty( $arch_fresh[$field] ) ) {
                    $arch_data[$field] = $arch_fresh[$field];
                }
            }
            if ( empty( $arch_data ) ) $arch_data = $arch_fresh;
        }

        $profil    = PP_Calculator::profil( $scores_data );
        $carte_html = PP_Archetypes::render_carte( $row->prenom, $arch_data, $scores_data['scores_dim'] ?? array() );
        $rdv_url   = get_option( 'pp_rdv_url', home_url('/contact') );
        $site_name = get_bloginfo('name');

        // Rendu complet avec le thème WP
        add_action( 'wp_head', function() use ( $row, $arch_data, $site_name ) {
            $t_emoji  = esc_html( $arch_data['emoji'] ?? '' );
            $t_nom    = esc_attr( $arch_data['nom'] ?? 'Profil de personnalité' );
            $t_prenom = esc_attr( $row->prenom );
            $t_desc   = esc_attr( 'Découvrez le profil de personnalité de ' . $row->prenom . ' — Archétype : ' . ($arch_data['nom'] ?? '') . '. ' . ($arch_data['tagline'] ?? '') );
            $t_url    = esc_url( home_url( '/profil/' . $row->token ) );
            $t_site   = esc_html( $site_name );
            // Image OG : logo du plugin ou logo du site
            $t_logo   = esc_url( get_option('pp_logo_url', '') ?: get_site_icon_url(1200) ?: PP_PLUGIN_URL . 'assets/img/og-default.png' );
            echo "<title>{$t_emoji} {$t_nom} — Profil de {$t_prenom} | {$t_site}</title>\n";
            echo "<meta name='description' content='{$t_desc}'>\n";
            echo "<meta property='og:title' content='{$t_emoji} {$t_nom} — {$t_prenom} | {$t_site}'>\n";
            echo "<meta property='og:description' content='{$t_desc}'>\n";
            echo "<meta property='og:url' content='{$t_url}'>\n";
            echo "<meta property='og:type' content='article'>\n";
            echo "<meta property='og:site_name' content='" . esc_attr($t_site) . "'>\n";
            echo "<meta property='og:image' content='{$t_logo}'>\n";
            echo "<meta property='og:image:width' content='1200'>\n";
            echo "<meta property='og:image:height' content='627'>\n";
            echo "<meta name='twitter:card' content='summary_large_image'>\n";
            echo "<meta name='twitter:title' content='{$t_emoji} {$t_nom} — {$t_prenom}'>\n";
            echo "<meta name='twitter:description' content='{$t_desc}'>\n";
            echo "<meta name='twitter:image' content='{$t_logo}'>\n";
            wp_enqueue_style( 'pp-style', PP_PLUGIN_URL . 'assets/css/front.css', array(), PP_VERSION );
            wp_enqueue_script( 'html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), '1.4.1', true );
            wp_enqueue_script( 'pp-script', PP_PLUGIN_URL . 'assets/js/front.js', array('jquery','html2canvas'), PP_VERSION, true );
        });

        // Template full-page
        get_header();
        include PP_PLUGIN_DIR . 'templates/public-profil.php';
        get_footer();
        exit;
    }

    /** AJAX : calcul de compatibilité entre deux tokens */
    public static function handle_compat() {
        if ( ! check_ajax_referer( 'pp_nonce', 'nonce', false ) ) {
            wp_send_json_error( array('message'=>'Erreur de sécurité.') ); wp_die();
        }
        $token_a = preg_replace('/[^a-f0-9]/', '', strtolower( sanitize_text_field( $_POST['token_a'] ?? '' ) ));
        $token_b = preg_replace('/[^a-f0-9]/', '', strtolower( sanitize_text_field( $_POST['token_b'] ?? '' ) ));

        if ( ! $token_a || ! $token_b ) {
            wp_send_json_error( array('message'=>'Tokens manquants.') ); wp_die();
        }

        $row_a = PP_DB::get_by_token( $token_a );
        $row_b = PP_DB::get_by_token( $token_b );

        if ( ! $row_a ) {
            wp_send_json_error( array('message'=>'Profil A introuvable.') ); wp_die();
        }
        if ( ! $row_b ) {
            wp_send_json_error( array('message'=>'Profil B introuvable.') ); wp_die();
        }

        $sc_a = json_decode( $row_a->scores, true )['scores_dim'] ?? array();
        $sc_b = json_decode( $row_b->scores, true )['scores_dim'] ?? array();

        $result = self::calculer_compatibilite( $row_a, $row_b, $sc_a, $sc_b );
        wp_send_json_success( $result );
        wp_die();
    }

    /**
     * Calcule un score de compatibilité OCEAN entre deux profils.
     * Score de 0 à 100 basé sur la complémentarité et la proximité.
     */
    public static function render_equipe( $campagne_token ) {
        $campagne_token = preg_replace('/[^a-f0-9]/', '', sanitize_text_field($campagne_token));
        $profils = PP_Batch::get_team_profils( $campagne_token );

        if ( $profils === null ) {
            wp_die( 'Équipe introuvable.', 'Non trouvé', array('response'=>404) );
        }

        // Trouver le nom de la campagne
        $campagne_nom = '';
        global $wpdb;
        $rows = $wpdb->get_col( "SELECT DISTINCT campagne FROM {$wpdb->prefix}pp_batch" );
        foreach ( $rows as $cn ) {
            if ( PP_Batch::get_campagne_token($cn) === $campagne_token ) {
                $campagne_nom = $cn; break;
            }
        }

        add_action('wp_head', function() use ($campagne_nom) {
            echo "<title>Vue équipe — " . esc_html($campagne_nom) . "</title>\n";
            wp_enqueue_style('pp-style', PP_PLUGIN_URL.'assets/css/front.css', array(), PP_VERSION);
        });

        get_header();
        include PP_PLUGIN_DIR . 'templates/equipe.php';
        get_footer();
        exit;
    }

    public static function calculer_compatibilite( $row_a, $row_b, $sc_a, $sc_b ) {
        $dims   = array( 'O', 'C', 'E', 'A', 'N' );
        $labels = array(
            'O' => 'Ouverture',
            'C' => 'Conscience',
            'E' => 'Extraversion',
            'A' => 'Agréabilité',
            'N' => 'Stabilité',
        );

        $pa = $row_a->prenom;
        $pb = $row_b->prenom;

        $details     = array();
        $score_total = 0;
        $poids_total = 0;

        $poids = array( 'O' => 1.0, 'C' => 1.5, 'E' => 0.8, 'A' => 1.5, 'N' => 1.2 );

        foreach ( $dims as $d ) {
            $a    = $sc_a[$d]['pct'] ?? 50;
            $b    = $sc_b[$d]['pct'] ?? 50;
            $diff = abs( $a - $b );
            $w    = $poids[$d];

            switch ( $d ) {
                case 'O':
                    $score_brut = max( 20, 100 - $diff * 1.0 );
                    if ( $diff <= 15 )     { $picto = '✅'; $phrase = "$pa et $pb partagent un niveau d'ouverture similaire — curiosité et créativité alignées."; }
                    elseif ( $diff <= 35 ) { $picto = '⚡'; $phrase = "Légère différence de curiosité intellectuelle entre $pa et $pb — enrichissant si bien géré."; }
                    else                  { $picto = '🔄'; $phrase = "Écart important : " . ($a > $b ? $pa : $pb) . " cherche plus à explorer et innover — risque de friction créative."; }
                    break;

                case 'C':
                    $score_brut = max( 10, 100 - $diff * 1.2 );
                    if ( $diff <= 15 )     { $picto = '✅'; $phrase = "Même niveau de rigueur et d'organisation — vous travaillez au même rythme."; }
                    elseif ( $diff <= 30 ) { $picto = '⚡'; $phrase = "Différence de méthode de travail : l'un est plus structuré que l'autre — nécessite des ajustements."; }
                    else                  { $picto = '🔄'; $phrase = "Écart fort : " . ($a > $b ? $pa : $pb) . " est bien plus organisé(e) — frustrations possibles sur les délais et la rigueur."; }
                    break;

                case 'E':
                    $score_brut = max( 30, 100 - max( 0, $diff - 30 ) * 1.0 );
                    if ( $diff <= 20 )     { $picto = '✅'; $phrase = "Niveau d'énergie sociale similaire — vous êtes à l'aise dans les mêmes environnements."; }
                    elseif ( $diff <= 45 ) { $picto = '⚡'; $phrase = "Bonne complémentarité : " . ($a > $b ? $pa : $pb) . " apporte l'élan social, l'autre la profondeur."; }
                    else                  { $picto = '🔄'; $phrase = "Très différents sur l'énergie sociale — l'un peut trouver l'autre épuisant ou trop effacé."; }
                    break;

                case 'A':
                    $score_brut = max( 10, 100 - $diff * 1.1 );
                    if ( $diff <= 15 )     { $picto = '✅'; $phrase = "Même approche relationnelle — confiance et bienveillance partagées."; }
                    elseif ( $diff <= 30 ) { $picto = '⚡'; $phrase = "Légère différence de style relationnel — l'un est plus direct, l'autre plus conciliant."; }
                    else                  { $picto = '🔄'; $phrase = "Écart notable : " . ($a > $b ? $pa : $pb) . " est bien plus coopératif(ve) — risque de malentendus sur les intentions."; }
                    break;

                case 'N':
                    $avg_n      = ( $a + $b ) / 2;
                    $score_brut = max( 10, 100 - $diff * 0.8 - max( 0, $avg_n - 40 ) * 0.5 );
                    if ( $diff <= 15 && $avg_n <= 45 ) { $picto = '✅'; $phrase = "Bonne stabilité émotionnelle des deux côtés — relationnel serein."; }
                    elseif ( $avg_n > 60 )             { $picto = '🔄'; $phrase = "Niveau de stress élevé des deux côtés — risque d'amplification mutuelle des tensions."; }
                    elseif ( $diff > 30 )              { $picto = '🔄'; $phrase = "Écart émotionnel : " . ($a > $b ? $pb : $pa) . " devra souvent rassurer l'autre — énergie à prévoir."; }
                    else                               { $picto = '⚡'; $phrase = "Légère différence de gestion du stress — surmontable avec une bonne communication."; }
                    break;

                default:
                    $score_brut = max( 0, 100 - $diff );
                    $picto = $diff <= 20 ? '✅' : ( $diff <= 40 ? '⚡' : '🔄' );
                    $phrase = '';
            }

            $score_pondere = $score_brut * $w;
            $details[$d] = array(
                'label'   => $labels[$d],
                'score_a' => $a,
                'score_b' => $b,
                'compat'  => round( $score_brut ),
                'picto'   => $picto,
                'phrase'  => $phrase,
            );
            $score_total += $score_pondere;
            $poids_total += $w;
        }

        $score_global = round( $score_total / $poids_total );

        if ( $score_global >= 78 ) {
            $niveau = 'Excellente synergie';
            $desc   = 'Vos profils sont très alignés. Vous partagez les mêmes valeurs de travail et vous fonctionnez naturellement bien ensemble.';
            $emoji  = '🌟';
        } elseif ( $score_global >= 62 ) {
            $niveau = 'Bonne compatibilité';
            $desc   = 'Vous avez de solides points communs. Quelques ajustements suffiront pour tirer le meilleur de votre collaboration.';
            $emoji  = '✅';
        } elseif ( $score_global >= 45 ) {
            $niveau = 'Compatibilité modérée';
            $desc   = 'Vos différences sont réelles. Elles peuvent être une richesse si vous prenez le temps de les comprendre et de les nommer.';
            $emoji  = '⚡';
        } else {
            $niveau = 'Profils contrastés';
            $desc   = 'Vos modes de fonctionnement sont très différents. La collaboration est possible mais demandera un effort conscient des deux côtés.';
            $emoji  = '🔄';
        }

        return array(
            'score'      => $score_global,
            'niveau'     => $niveau,
            'description'=> $desc,
            'emoji'      => $emoji,
            'details'    => $details,
            'prenom_a'   => $row_a->prenom,
            'prenom_b'   => $row_b->prenom,
            'arch_a'     => $row_a->archetype_nom,
            'arch_b'     => $row_b->archetype_nom,
        );
    }
}
