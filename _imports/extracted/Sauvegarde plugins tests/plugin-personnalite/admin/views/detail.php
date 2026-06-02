<?php if ( ! defined('ABSPATH') ) exit;
$profil = PP_Calculator::profil( array(
    'score_O'  => $row->score_O,
    'score_C'  => $row->score_C,
    'score_E'  => $row->score_E,
    'score_A'  => $row->score_A,
    'score_N'  => $row->score_N,
    'score_DS' => $row->score_DS,
));
?>
<div class="wrap pp-admin">
    <h1>
        <a href="<?php echo admin_url('admin.php?page=pp-resultats'); ?>" class="button">&larr; Retour</a>
        Détail — <?php echo esc_html($row->prenom); ?> (<?php echo esc_html($row->email); ?>)
    </h1>

    <div class="pp-detail-meta">
        <p><strong>Date :</strong> <?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($row->date_soumis))); ?></p>
        <p><strong>Consentement RGPD :</strong> <?php echo $row->consentement ? 'Oui ✅' : 'Non ❌'; ?></p>
        <p><strong>Source :</strong> <?php echo esc_html($row->source ?: '—'); ?></p>
        <p><strong>Relance 3j :</strong> <?php echo $row->relance_3j ? 'Envoyée ✅' : 'En attente'; ?>
           &nbsp;|&nbsp; <strong>Relance 8j :</strong> <?php echo $row->relance_8j ? 'Envoyée ✅' : 'En attente'; ?></p>
    </div>

    <h2>Profil</h2>
    <div class="pp-profil-grid">
    <?php foreach ( $profil as $key => $p ) :
        $color = $p['score'] >= 65 ? '#16a34a' : ( $p['score'] >= 40 ? '#2563eb' : '#dc2626' );
    ?>
        <div class="pp-profil-card">
            <div class="pp-profil-header">
                <span class="pp-profil-label"><?php echo esc_html($p['label']); ?></span>
                <span class="pp-profil-score" style="color:<?php echo $color; ?>"><?php echo $p['score']; ?>%</span>
            </div>
            <div class="pp-bar-bg">
                <div class="pp-bar-fill" style="width:<?php echo $p['score']; ?>%;background:<?php echo $color; ?>;"></div>
            </div>
            <p class="pp-profil-texte"><?php echo esc_html($p['texte']); ?></p>
        </div>
    <?php endforeach; ?>
    </div>
</div>
