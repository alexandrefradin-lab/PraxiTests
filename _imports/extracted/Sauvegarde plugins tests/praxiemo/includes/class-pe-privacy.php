<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gestion de la page de politique de confidentialité PraxiEmo.
 *
 * - Crée automatiquement une page WordPress complète (RGPD) à l'activation.
 * - Stocke l'ID dans l'option `pemo_privacy_page_id`.
 * - Expose get_url() pour les liens internes.
 * - Le contenu est généré en HTML brut pour être directement lisible
 *   sans dépendre du shortcode.
 */
class PE_Privacy {

    /** Slug de la page créée. */
    const PAGE_SLUG = 'praxiemo-politique-confidentialite';

    /** Option WordPress qui stocke l'ID de la page. */
    const OPTION_KEY = 'pemo_privacy_page_id';

    // ── API publique ───────────────────────────────────────────────────────────

    /**
     * Crée ou recrée la page de confidentialité.
     * Appelée à l'activation du plugin et via le bouton admin.
     *
     * @return int|WP_Error  ID de la page créée ou mise à jour.
     */
    public static function create_or_update_page() {
        $content = self::get_page_content();

        // Vérifier si une page existe déjà (par slug ou option)
        $existing_id = (int) get_option( self::OPTION_KEY, 0 );
        if ( $existing_id && get_post_status( $existing_id ) !== false ) {
            // Mettre à jour le contenu
            $result = wp_update_post( array(
                'ID'           => $existing_id,
                'post_content' => $content,
                'post_status'  => 'publish',
            ), true );
            return $result;
        }

        // Chercher par slug au cas où l'option aurait été perdue
        $existing = get_page_by_path( self::PAGE_SLUG );
        if ( $existing ) {
            update_option( self::OPTION_KEY, $existing->ID );
            wp_update_post( array(
                'ID'           => $existing->ID,
                'post_content' => $content,
                'post_status'  => 'publish',
            ) );
            return $existing->ID;
        }

        // Créer la page
        $page_id = wp_insert_post( array(
            'post_title'   => 'Politique de confidentialité — Test IE',
            'post_name'    => self::PAGE_SLUG,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => get_current_user_id() ?: 1,
        ), true );

        if ( ! is_wp_error( $page_id ) ) {
            update_option( self::OPTION_KEY, $page_id );
        }

        return $page_id;
    }

    /**
     * Retourne l'URL de la page de confidentialité générée par le plugin.
     * Retourne '' si la page n'existe pas ou a été supprimée.
     *
     * @return string
     */
    public static function get_url() {
        $page_id = (int) get_option( self::OPTION_KEY, 0 );
        if ( ! $page_id ) return '';

        $status = get_post_status( $page_id );
        if ( ! $status || $status === 'trash' ) return '';

        return (string) get_permalink( $page_id );
    }

    /**
     * Supprime l'option (pas la page elle-même) — appelé à la désinstallation.
     */
    public static function cleanup() {
        delete_option( self::OPTION_KEY );
    }

    // ── Contenu de la page ─────────────────────────────────────────────────────

    private static function get_page_content() {
        $site_name    = get_option( 'pemo_site_name', get_bloginfo( 'name' ) );
        $admin_email  = get_option( 'pemo_admin_email', get_option( 'admin_email' ) );
        $site_url     = home_url();
        $date_maj     = date_i18n( 'd/m/Y' );

        ob_start();
        ?>
<div class="pemo-privacy-page">

<p><em>Dernière mise à jour : <?php echo esc_html( $date_maj ); ?></em></p>

<h2>1. Qui sommes-nous ?</h2>
<p>Ce test d'Intelligence Émotionnelle est proposé par <strong><?php echo esc_html( $site_name ); ?></strong>, dont le site est accessible à l'adresse <a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_url( $site_url ); ?></a>.</p>
<p>Le responsable du traitement des données personnelles collectées via ce test est :<br>
<strong>Alexandre Fradin — Praxis Accompagnement</strong><br>
Contact : <a href="mailto:<?php echo esc_attr( $admin_email ); ?>"><?php echo esc_html( $admin_email ); ?></a></p>

<h2>2. Quelles données collectons-nous ?</h2>
<p>Lors de la passation du test, nous collectons les informations suivantes :</p>
<ul>
  <li><strong>Prénom</strong> — pour personnaliser votre rapport de résultats.</li>
  <li><strong>Adresse email</strong> — pour vous envoyer votre rapport et, si vous l'acceptez, des contenus de suivi.</li>
  <li><strong>Adresse IP</strong> — à des fins de sécurité et de lutte contre les abus (détection de soumissions multiples frauduleuses).</li>
  <li><strong>Vos réponses au questionnaire</strong> — les réponses aux 86 questions du test (80 questions d'intelligence émotionnelle + 6 questions de calibration), nécessaires au calcul de votre profil.</li>
  <li><strong>Date et heure de passation</strong> — pour horodater votre résultat et permettre un suivi dans le temps.</li>
</ul>
<p>Nous ne collectons aucune donnée sensible au sens de l'article 9 du RGPD (origine ethnique, opinions politiques, données de santé, etc.).</p>

<h2>3. Pourquoi collectons-nous ces données ?</h2>
<p>Vos données sont traitées pour les finalités suivantes :</p>
<ul>
  <li><strong>Calcul et affichage de votre profil d'intelligence émotionnelle</strong> — finalité principale du test.</li>
  <li><strong>Envoi de votre rapport par email</strong> — afin que vous puissiez conserver et consulter vos résultats.</li>
  <li><strong>Suivi de votre progression dans le temps</strong> — si vous repassez le test ultérieurement, vos scores précédents sont comparés à vos nouveaux résultats.</li>
  <li><strong>Relances pédagogiques</strong> — si vous avez démarré le test sans le terminer, un email de rappel peut vous être envoyé à J+3 et J+8 (désactivable dans vos préférences ou sur demande).</li>
  <li><strong>Amélioration du test</strong> — les résultats agrégés et anonymisés nous permettent d'affiner la calibration du questionnaire.</li>
</ul>

<h2>4. Base légale du traitement</h2>
<p>Le traitement de vos données repose sur votre <strong>consentement explicite</strong> (article 6.1.a du RGPD), que vous donnez en cochant la case correspondante avant de commencer le test.</p>
<p>Vous pouvez retirer ce consentement à tout moment en nous contactant à l'adresse indiquée à l'article 1. Le retrait du consentement ne remet pas en cause la licéité du traitement effectué avant ce retrait.</p>

<h2>5. Durée de conservation</h2>
<p>Vos données sont conservées pendant une durée maximale de <strong>24 mois</strong> à compter de la date de votre dernière passation du test. Passé ce délai, elles sont supprimées automatiquement de notre base de données.</p>
<p>Vous pouvez demander la suppression de vos données à tout moment, sans avoir à attendre l'expiration de ce délai (voir article 7).</p>

<h2>6. Avec qui partageons-nous vos données ?</h2>
<p>Vos données ne sont <strong>pas vendues, ni cédées</strong> à des tiers à des fins commerciales.</p>
<p>Elles peuvent être transmises aux prestataires techniques suivants, dans le strict cadre de leur mission :</p>
<ul>
  <li><strong>Hébergeur du site</strong> (OVH SAS, France) — pour le stockage des données sur des serveurs situés en France / dans l'Union européenne.</li>
  <li><strong>Service d'envoi d'emails</strong> — pour l'acheminement de votre rapport et des relances éventuelles, via le serveur SMTP de l'hébergeur.</li>
</ul>
<p>Aucun transfert de données hors de l'Union européenne n'est effectué.</p>

<h2>7. Vos droits</h2>
<p>Conformément au RGPD (articles 15 à 22), vous disposez des droits suivants :</p>
<ul>
  <li><strong>Droit d'accès</strong> — vous pouvez demander une copie des données que nous détenons vous concernant.</li>
  <li><strong>Droit de rectification</strong> — vous pouvez demander la correction de données inexactes ou incomplètes.</li>
  <li><strong>Droit à l'effacement</strong> ("droit à l'oubli") — vous pouvez demander la suppression de toutes vos données.</li>
  <li><strong>Droit à la portabilité</strong> — vous pouvez demander à recevoir vos données dans un format structuré et lisible par machine.</li>
  <li><strong>Droit d'opposition</strong> — vous pouvez vous opposer au traitement de vos données pour les finalités de communication et de suivi.</li>
  <li><strong>Droit de retrait du consentement</strong> — à tout moment, sans que cela affecte la licéité du traitement antérieur.</li>
</ul>
<p>Pour exercer l'un de ces droits, adressez votre demande à : <a href="mailto:<?php echo esc_attr( $admin_email ); ?>"><?php echo esc_html( $admin_email ); ?></a></p>
<p>Nous nous engageons à vous répondre dans un délai d'un mois. Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une réclamation auprès de la <strong>CNIL</strong> (Commission Nationale de l'Informatique et des Libertés) : <a href="https://www.cnil.fr/fr/plaintes" target="_blank" rel="noopener noreferrer">www.cnil.fr/fr/plaintes</a>.</p>

<h2>8. Sécurité des données</h2>
<p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données contre tout accès non autorisé, perte ou divulgation : connexion sécurisée (HTTPS), accès restreint à la base de données, mots de passe chiffrés, journaux d'accès.</p>

<h2>9. Cookies</h2>
<p>Le test d'Intelligence Émotionnelle n'utilise <strong>aucun cookie tiers</strong> à des fins de traçage ou de publicité. Une session technique temporaire peut être utilisée le temps de la passation du test uniquement.</p>

<h2>10. Modifications de cette politique</h2>
<p>Cette politique de confidentialité peut être mise à jour pour refléter des évolutions légales ou des changements dans notre fonctionnement. La date de dernière mise à jour figure en haut de cette page. En continuant à utiliser le test après une modification, vous acceptez la version mise à jour.</p>

<h2>11. Contact</h2>
<p>Pour toute question relative à cette politique ou à vos données personnelles :<br>
<strong><?php echo esc_html( $site_name ); ?></strong><br>
Email : <a href="mailto:<?php echo esc_attr( $admin_email ); ?>"><?php echo esc_html( $admin_email ); ?></a><br>
Site : <a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_url( $site_url ); ?></a></p>

</div>
        <?php
        return trim( ob_get_clean() );
    }
}
