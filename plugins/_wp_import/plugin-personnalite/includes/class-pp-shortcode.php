<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Shortcode {

    /** Durée de vie du nonce de soumission (30 min). */
    const SUBMIT_TRANSIENT_PREFIX = 'pp_submit_lock_';
    const SUBMIT_LOCK_TTL         = 300; // 5 min — protection double-soumission

    public static function init() {
        add_shortcode( 'test_personnalite',      array( __CLASS__, 'render' ) );
        add_shortcode( 'test_personnalite_solo', array( __CLASS__, 'render_solo' ) );
        add_shortcode( 'pp_profil',              array( __CLASS__, 'render_profil' ) );
        add_shortcode( 'pp_politique',           array( __CLASS__, 'render_politique' ) );
        add_action( 'wp_ajax_pp_submit',        array( __CLASS__, 'handle_submit' ) );
        add_action( 'wp_ajax_nopriv_pp_submit', array( __CLASS__, 'handle_submit' ) );
    }

    public static function render( $atts ) {
        ob_start();
        include PP_PLUGIN_DIR . 'templates/form.php';
        return ob_get_clean();
    }

    /** Shortcode [pp_profil token="xxx"] */
    public static function render_profil( $atts ) {
        $atts  = shortcode_atts( array( 'token' => '' ), $atts );
        $token = sanitize_text_field( $atts['token'] );
        if ( ! $token ) return '<p>Token manquant.</p>';

        $row = PP_DB::get_by_token( $token );
        if ( ! $row ) return '<p>Profil introuvable.</p>';

        $scores_data = json_decode( $row->scores, true ) ?: array();
        $arch_data   = json_decode( $row->archetype_data, true ) ?: array();
        // Toujours recharger depuis PP_Archetypes pour avoir les textes à jour
        if ( ! empty($scores_data['scores_dim']) ) {
            $arch_fresh = PP_Archetypes::detecter( $scores_data['scores_dim'] );
            // Écraser uniquement les champs texte (description, tagline) — garder couleurs/nom
            foreach ( array('description','tagline','traits','rarete') as $field ) {
                if ( ! empty($arch_fresh[$field]) ) {
                    $arch_data[$field] = $arch_fresh[$field];
                }
            }
            if ( empty($arch_data) ) $arch_data = $arch_fresh;
        }
        $profil     = PP_Calculator::profil( $scores_data );
        $carte_html = PP_Archetypes::render_carte( $row->prenom, $arch_data, $scores_data['scores_dim'] ?? array() );
        $rdv_url    = get_option( 'pp_rdv_url', home_url('/contact') );
        $site_name  = get_bloginfo('name');
        ob_start();
        include PP_PLUGIN_DIR . 'templates/public-profil.php';
        return ob_get_clean();
    }

    public static function handle_submit() {
        // ── 1. Vérification nonce ────────────────────────────────────────────
        if ( ! check_ajax_referer( 'pp_nonce', 'nonce', false ) ) {
            PP_Logger::warning('submit', 'Nonce invalide', array('ip'=>self::get_ip()));
            wp_send_json_error( array( 'message' => 'Session expirée. Rechargez la page et réessayez.' ), 403 );
        }

        // ── 2. Sanitisation entrées ──────────────────────────────────────────
        $prenom       = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
        $email        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        $consentement = intval( $_POST['consentement'] ?? 0 );

        if ( strlen($prenom) < 1 || strlen($prenom) > 100 ) {
            wp_send_json_error( array( 'message' => 'Prénom invalide (1–100 caractères).' ), 422 );
        }
        if ( ! is_email($email) ) {
            wp_send_json_error( array( 'message' => 'Adresse email invalide.' ), 422 );
        }
        if ( ! $consentement ) {
            wp_send_json_error( array( 'message' => 'Vous devez accepter la politique de confidentialité.' ), 422 );
        }

        // ── 3. Protection double-soumission (transient par email+IP) ────────
        $lock_key = self::SUBMIT_TRANSIENT_PREFIX . md5($email . self::get_ip());
        if ( get_transient($lock_key) ) {
            PP_Logger::warning('submit', 'Double soumission bloquée', array('email'=>$email,'ip'=>self::get_ip()));
            wp_send_json_error( array(
                'message' => 'Votre test a déjà été soumis. Vérifiez votre boîte mail ou attendez quelques minutes.'
            ), 429 );
        }
        set_transient($lock_key, 1, self::SUBMIT_LOCK_TTL);

        // ── 4. Validation réponses ───────────────────────────────────────────
        $raw_reponses = $_POST['reponses'] ?? array();
        if ( ! is_array($raw_reponses) ) {
            wp_send_json_error( array( 'message' => 'Format de données invalide.' ), 422 );
        }

        $reponses = array();
        $invalides = array();
        foreach ( $raw_reponses as $qid => $val ) {
            $qid_i = intval($qid);
            $val_i = intval($val);
            if ( $qid_i < 1 || $qid_i > 128 ) { $invalides[] = $qid_i; continue; }
            if ( $val_i < 1 || $val_i > 5   ) { $invalides[] = $qid_i; continue; }
            $reponses[$qid_i] = $val_i;
        }
        if ( $invalides ) {
            PP_Logger::warning('submit', 'Réponses invalides', array('ids'=>array_slice($invalides,0,10)));
            wp_send_json_error( array( 'message' => 'Réponses invalides détectées. Veuillez réessayer.' ), 422 );
        }
        if ( count($reponses) < 120 ) {
            $missing = 128 - count($reponses);
            wp_send_json_error( array(
                'message' => "Veuillez répondre à toutes les questions ({$missing} réponse(s) manquante(s))."
            ), 422 );
        }

        // ── 5. Calcul scores (protégé contre les exceptions) ─────────────────
        try {
            $scores    = PP_Calculator::calculer( $reponses );
            $profil    = PP_Calculator::profil( $scores );
            $archetype = PP_Archetypes::detecter( $scores['scores_dim'] );
        } catch ( Throwable $e ) {
            PP_Logger::critical('submit', 'Erreur calcul scores : ' . $e->getMessage(),
                array('email'=>$email,'trace'=>substr($e->getTraceAsString(),0,500)));
            delete_transient($lock_key); // Libérer le lock pour retry
            wp_send_json_error( array( 'message' => 'Erreur lors du calcul. Veuillez réessayer.' ), 500 );
        }

        // ── 6. Insertion BDD (avec retry) ────────────────────────────────────
        $source = sanitize_text_field( wp_unslash( $_POST['source'] ?? '' ) );
        if ( ! $source ) $source = wp_get_referer() ?: 'direct';

        $insert      = null;
        $last_db_err = '';
        for ( $try = 1; $try <= 3; $try++ ) {
            try {
                $insert = PP_DB::insert( array(
                    'prenom'       => $prenom,
                    'email'        => $email,
                    'reponses'     => $reponses,
                    'scores'       => $scores,
                    'archetype'    => $archetype,
                    'consentement' => $consentement,
                    'source'       => $source,
                ) );
                if ( $insert && ! empty($insert['id']) ) break;
            } catch ( Throwable $e ) {
                $last_db_err = $e->getMessage();
                PP_Logger::error('submit', "Tentative BDD #{$try} échouée : " . $last_db_err,
                    array('email'=>$email,'prenom'=>$prenom));
                if ( $try < 3 ) usleep(200000); // 200ms entre retries
            }
        }

        if ( ! $insert || empty($insert['id']) ) {
            PP_Logger::critical('submit', 'Impossible d\'insérer en BDD après 3 tentatives',
                array('email'=>$email,'prenom'=>$prenom,'db_error'=>$last_db_err));
            delete_transient($lock_key);
            wp_send_json_error( array(
                'message' => 'Erreur d\'enregistrement. Vos réponses sont sauvegardées localement — réessayez dans quelques minutes.'
            ), 500 );
        }

        $token     = $insert['token'];
        $result_id = $insert['id'];
        PP_Logger::info('submit', 'Test soumis', array('id'=>$result_id,'email'=>$email,'archetype'=>$archetype['nom']??''));

        // ── 7. Mise à jour statut batch ──────────────────────────────────────
        $invite_tk = sanitize_text_field( wp_unslash( $_POST['pp_invite_tk'] ?? '' ) );
        if ( $invite_tk && preg_match('/^[a-f0-9]{32}$/', $invite_tk) && class_exists('PP_Batch') ) {
            global $wpdb;
            $updated = $wpdb->update(
                $wpdb->prefix . PP_Batch::TABLE,
                array('statut'=>'complete','date_complete'=>current_time('mysql'),'resultat_id'=>$result_id),
                array('token'=>$invite_tk)
            );
            if ($updated === false) {
                PP_Logger::warning('submit', 'Échec mise à jour statut batch', array('invite_tk'=>$invite_tk));
            }
        }

        // ── 8. Envoi email (non bloquant — échec loggé mais pas fatal) ───────
        try {
            $sent = PP_Mailer::envoyer_resultats( $prenom, $email, $profil, $archetype, $token );
            if ( ! $sent ) {
                PP_Logger::warning('submit', 'wp_mail a retourné false', array('email'=>$email,'token'=>$token));
            }
        } catch ( Throwable $e ) {
            PP_Logger::error('submit', 'Exception envoi email : ' . $e->getMessage(),
                array('email'=>$email,'token'=>$token));
            // On continue — le test est enregistré même si l'email échoue
        }

        // ── 9. Préparation réponse ───────────────────────────────────────────
        $profil_url = $token ? home_url('/profil/' . $token) : get_option('pp_rdv_url', home_url('/contact'));
        $merci_url  = get_option('pp_merci_url', '');
        $carte_html = PP_Archetypes::render_carte($prenom, $archetype, $scores['scores_dim']);

        $facettes_raw = PP_Questions::get_facettes_map();
        $facettes_map = array_map(fn($v) => array('label'=>$v['label'],'dim'=>$v['dim'],'desc'=>$v['desc']),
                                  $facettes_raw);

        $tracking = array(
            'ga_event'      => sanitize_text_field(get_option('pp_ga_event',      'test_personnalite_complete')),
            'gtm_event'     => sanitize_text_field(get_option('pp_gtm_event',     'pp_test_complete')),
            'meta_pixel_id' => sanitize_text_field(get_option('pp_meta_pixel_id', '')),
            'meta_event'    => sanitize_text_field(get_option('pp_meta_event',    'Lead')),
        );

        wp_send_json_success( array(
            'profil'         => $profil,
            'archetype'      => array(
                'nom'         => $archetype['nom']         ?? '',
                'tagline'     => $archetype['tagline']     ?? '',
                'emoji'       => $archetype['emoji']       ?? '',
                'description' => $archetype['description'] ?? '',
                'rarete'      => $archetype['rarete']      ?? '',
                'couleur1'    => $archetype['couleur1']    ?? '#4F46E5',
                'couleur2'    => $archetype['couleur2']    ?? '#7C3AED',
                'traits'      => $archetype['traits']      ?? array(),
            ),
            'scores_dim'     => $scores['scores_dim'],
            'scores_facette' => $scores['scores_facette'],
            'facettes_map'   => $facettes_map,
            'carte_html'     => $carte_html,
            'prenom'         => $prenom,
            'profil_url'     => $profil_url,
            'merci_url'      => $merci_url,
            'rdv_url'        => get_option('pp_rdv_url', home_url('/contact')),
            'site_name'      => get_option('pp_site_name_override','') ?: get_bloginfo('name'),
            'tracking'       => $tracking,
        ));
    }

    /** IP cliente — compatible reverse proxy / Cloudflare. */
    private static function get_ip() {
        $keys = array('HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR');
        foreach ($keys as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = trim(explode(',', $_SERVER[$k])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    /** Shortcode [pp_politique] — affiche la politique de confidentialité */
    public static function render_politique( $atts ) {
        ob_start();
        include PP_PLUGIN_DIR . 'templates/politique.php';
        return ob_get_clean();
    }

    /** Shortcode [test_personnalite_solo] — mode une question par page */
    public static function render_solo( $atts ) {
        ob_start();
        include PP_PLUGIN_DIR . 'templates/form-solo.php';
        return ob_get_clean();
    }
}
