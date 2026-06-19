<?php
/**
 * Écran d'accueil de la passation.
 * Variables disponibles : $is_self (bool), $campaign (obj), $resp (obj).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$subject = esc_html( $campaign->subject_name );
?>
<div class="p360-app">
    <div id="p360-intro" class="p360-card p360-intro">
        <?php if ( $is_self ) : ?>
            <h1>Votre auto-évaluation 360°</h1>
            <p>Avant de découvrir le regard des autres, posez le vôtre. Répondez spontanément&nbsp;: c'est l'écart entre votre perception et celle des autres qui sera le plus éclairant.</p>
        <?php else : ?>
            <h1>Aidez <?php echo $subject; ?> à mieux se connaître</h1>
            <p><?php echo $subject; ?> a souhaité recueillir des regards sincères sur ses compétences relationnelles, et votre avis compte.</p>
            <p>Vos réponses sont <strong>confidentielles</strong>&nbsp;: elles seront regroupées avec celles des autres participants, jamais montrées individuellement.</p>
        <?php endif; ?>
        <span class="p360-meta">⏱ Environ 10 minutes</span>
        <p>
            <button id="p360-start" class="p360-btn">
                <?php echo esc_html( $is_self ? 'Commencer' : "Commencer l'évaluation" ); ?>
            </button>
        </p>
        <p style="font-size:13px;color:var(--praxis-text-soft);">
            Pour chaque comportement, indiquez à quelle fréquence vous l'observez. Si vous ne pouvez pas l'observer, choisissez «&nbsp;Non observé&nbsp;».
        </p>
    </div>
</div>
