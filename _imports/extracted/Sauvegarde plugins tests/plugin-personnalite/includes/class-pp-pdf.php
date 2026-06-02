<?php
/**
 * PP_PDF — Stub de compatibilité
 *
 * Le PDF est désormais entièrement généré côté client via jsPDF (assets/js/pp-pdf-client.js).
 * Cette classe est conservée pour la compatibilité ascendante mais ne fait plus rien
 * de lourd : plus de Python, plus de shell_exec, plus d'admin_notices.
 *
 * L'URL /profil/[token]?pp_pdf=1 redirige désormais vers la page profil avec un
 * paramètre JS qui déclenche automatiquement le téléchargement côté client.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_PDF {

    /**
     * Anciennement : génération serveur (Python / ReportLab).
     * Désormais : no-op — le PDF est produit par pp-pdf-client.js.
     *
     * @deprecated Conservé pour ne pas casser du code tiers éventuel.
     * @return false toujours
     */
    public static function generer( $row ) {
        return false;
    }

    /**
     * Redirige ?pp_pdf=1 vers la page profil en mode auto-download JS.
     * Le template public-profil.php détecte window.location.hash === '#download-pdf'
     * et appelle ppGeneratePDFPublic() automatiquement.
     */
    public static function handle_download( $token ) {
        $token = preg_replace( '/[^a-f0-9]/', '', sanitize_text_field( $token ) );
        if ( strlen( $token ) !== 32 ) {
            wp_die( esc_html__( 'Lien invalide.', 'plugin-personnalite' ), '', array( 'response' => 400 ) );
        }
        $url = home_url( '/profil/' . $token . '/#download-pdf' );
        wp_safe_redirect( $url, 302 );
        exit;
    }

    /** @deprecated Plus utilisé — Python n'est plus nécessaire. */
    public static function find_python() { return false; }

    /** @deprecated Plus utilisé — Python n'est plus nécessaire. */
    public static function check_reportlab() { return false; }
}
