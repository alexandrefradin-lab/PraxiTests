<?php if ( ! defined('ABSPATH') ) exit;
$saved = isset($_GET['saved']);
$consultant_email  = get_option('praxivaleurs_consultant_email', get_option('admin_email'));
$send_user_email   = get_option('praxivaleurs_send_user_email', 1);
$send_notif_email  = get_option('praxivaleurs_send_notif_email', 1);
$from_name         = get_option('praxivaleurs_from_name', 'Praxis Accompagnement');
$from_email        = get_option('praxivaleurs_from_email', get_option('admin_email'));
?>
<div class="pv-admin-wrap">

    <div class="pv-admin-header">
        <div class="pv-admin-header-left">
            <div class="pv-admin-logo">
                <span class="pv-admin-logo-icon">◆</span>
                <div>
                    <div class="pv-admin-logo-name">PraxiValeurs</div>
                    <div class="pv-admin-logo-sub">Réglages</div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($saved): ?>
    <div class="pv-admin-notice pv-admin-notice--success">✅ Réglages enregistrés avec succès.</div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('praxivaleurs_settings'); ?>
        <input type="hidden" name="action" value="praxivaleurs_save_settings">

        <!-- Emails -->
        <div class="pv-admin-card pv-admin-card--full pv-settings-card">
            <div class="pv-admin-card-title">📧 Configuration email</div>
            <div class="pv-settings-grid">

                <div class="pv-settings-field">
                    <label class="pv-settings-label">Email du consultant (notifications)</label>
                    <input type="email" name="consultant_email" value="<?php echo esc_attr($consultant_email); ?>" class="pv-settings-input" placeholder="consultant@praxis.fr">
                    <p class="pv-settings-hint">Reçoit une notification à chaque nouveau profil complété.</p>
                </div>

                <div class="pv-settings-field">
                    <label class="pv-settings-label">Nom expéditeur</label>
                    <input type="text" name="from_name" value="<?php echo esc_attr($from_name); ?>" class="pv-settings-input" placeholder="Praxis Accompagnement">
                </div>

                <div class="pv-settings-field">
                    <label class="pv-settings-label">Email expéditeur</label>
                    <input type="email" name="from_email" value="<?php echo esc_attr($from_email); ?>" class="pv-settings-input" placeholder="noreply@praxis.fr">
                    <p class="pv-settings-hint">Doit correspondre à un email autorisé sur votre serveur OVH.</p>
                </div>

                <div class="pv-settings-field pv-settings-field--full">
                    <label class="pv-settings-label">Options d'envoi</label>
                    <div class="pv-toggle-group">
                        <label class="pv-toggle">
                            <input type="checkbox" name="send_user_email" value="1" <?php checked($send_user_email, 1); ?>>
                            <span class="pv-toggle-track"></span>
                            <span class="pv-toggle-label">Envoyer l'email de résultats au bénéficiaire</span>
                        </label>
                        <label class="pv-toggle">
                            <input type="checkbox" name="send_notif_email" value="1" <?php checked($send_notif_email, 1); ?>>
                            <span class="pv-toggle-track"></span>
                            <span class="pv-toggle-label">Envoyer une notification au consultant</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <!-- Intégration -->
        <div class="pv-admin-card pv-admin-card--full pv-settings-card">
            <div class="pv-admin-card-title">🔗 Intégration</div>
            <div class="pv-settings-grid">
                <div class="pv-settings-field pv-settings-field--full">
                    <label class="pv-settings-label">Shortcode</label>
                    <div class="pv-code-block">[praxivaleurs]</div>
                    <p class="pv-settings-hint">Collez ce shortcode dans n'importe quelle page ou article WordPress pour afficher le test.</p>
                </div>
                <div class="pv-settings-field pv-settings-field--full">
                    <label class="pv-settings-label">SMTP OVH (via plugin WP Mail SMTP)</label>
                    <div class="pv-settings-info">
                        <div>Hôte : <strong>ssl0.ovh.net</strong></div>
                        <div>Port : <strong>465</strong></div>
                        <div>Chiffrement : <strong>SSL</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Version -->
        <div class="pv-admin-card pv-admin-card--full pv-settings-card">
            <div class="pv-admin-card-title">ℹ️ Informations</div>
            <div class="pv-settings-info">
                <div>Plugin : <strong>PraxiValeurs</strong></div>
                <div>Version : <strong><?php echo PRAXIVALEURS_VERSION; ?></strong></div>
                <div>Framework : <strong>Schwartz (10 valeurs, 40 items)</strong></div>
                <div>Auteur : <strong>Praxis Accompagnement</strong></div>
            </div>
        </div>

        <div class="pv-settings-submit">
            <button type="submit" class="pv-admin-btn pv-admin-btn--lg">Enregistrer les réglages</button>
        </div>

    </form>
</div>
