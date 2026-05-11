<?php if ( ! defined('ABSPATH') ) exit; ?>
<div style="max-width:780px;margin:0 auto;padding:40px 20px 60px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#1E2A3A;line-height:1.7;">

  <h1 style="font-size:28px;font-weight:900;color:#E8541A;margin:0 0 6px;">Politique de Confidentialité</h1>
  <p style="font-size:14px;color:#8FA8BE;margin:0 0 40px;">PraxiMum — Test de personnalité Big Five &nbsp;·&nbsp; Dernière mise à jour : <?php echo date_i18n('j F Y'); ?></p>

  <?php
  $c1 = '#E8541A';
  function pp_pol_h2($text, $c1='#E8541A') {
    echo "<h2 style='font-size:18px;font-weight:800;color:{$c1};margin:36px 0 10px;padding-bottom:6px;border-bottom:2px solid #EEF3F8;'>{$text}</h2>";
  }
  function pp_pol_p($text) {
    echo "<p style='margin:0 0 14px;font-size:15px;'>{$text}</p>";
  }
  function pp_pol_ul($items) {
    echo "<ul style='margin:0 0 14px;padding-left:24px;'>";
    foreach ($items as $item) echo "<li style='margin-bottom:6px;font-size:15px;'>{$item}</li>";
    echo "</ul>";
  }
  ?>

  <?php pp_pol_h2('1. Responsable du traitement'); ?>
  <?php pp_pol_p('Le responsable du traitement des données collectées via PraxiMum est :'); ?>
  <div style="background:#EEF3F8;border-left:4px solid #E8541A;border-radius:0 10px 10px 0;padding:16px 20px;margin-bottom:20px;">
    <strong>Alexandre Fradin</strong> — Auto-entrepreneur<br>
    Activité : Accompagnement professionnel, bilan de compétences<br>
    Site web : praxis-accompagnement.com<br>
    Contact : <a href="mailto:contact@praxis-accompagnement.com" style="color:#E8541A;">contact@praxis-accompagnement.com</a>
  </div>

  <?php pp_pol_h2('2. Données collectées'); ?>
  <strong style="display:block;margin-bottom:6px;">Données saisies par l'utilisateur</strong>
  <?php pp_pol_ul(['Prénom', 'Adresse e-mail', 'Consentement au traitement (horodaté)', 'Source d\'acquisition (si applicable)']); ?>
  <strong style="display:block;margin-bottom:6px;">Données générées par le test</strong>
  <?php pp_pol_ul(['Réponses aux 128 questions', 'Scores sur les 5 dimensions (OCEAN) et 30 facettes', 'Archétype de personnalité calculé', 'Date et heure de soumission']); ?>
  <strong style="display:block;margin-bottom:6px;">Données techniques</strong>
  <?php pp_pol_ul(['Adresse IP (anti-abus uniquement, non conservée)', 'Statut des relances e-mail (J+3, J+8)']); ?>

  <?php pp_pol_h2('3. Finalités du traitement'); ?>
  <?php pp_pol_ul([
    'Calculer et afficher votre profil de personnalité',
    'Vous envoyer vos résultats et votre rapport par e-mail',
    'Vous proposer un accompagnement adapté à votre profil (bilan de compétences)',
    'Envoyer des e-mails de relance à J+3 et J+8',
    'Permettre le suivi des résultats via le tableau de bord administrateur',
  ]); ?>
  <?php pp_pol_p('<strong>Aucune donnée n\'est utilisée à des fins publicitaires ou revendue à des tiers.</strong>'); ?>

  <?php pp_pol_h2('4. Base légale du traitement'); ?>
  <?php pp_pol_p('<strong>Consentement (RGPD art. 6.1.a) :</strong> vous cochez explicitement la case de consentement avant de soumettre le test.'); ?>
  <?php pp_pol_p('<strong>Intérêt légitime (RGPD art. 6.1.f) :</strong> envoi des résultats demandés et relances liées à votre démarche d\'accompagnement.'); ?>

  <?php pp_pol_h2('5. Durée de conservation'); ?>
  <?php pp_pol_p('Vos données sont conservées pendant <strong>24 mois</strong> à compter de la date de soumission. Passé ce délai, elles sont automatiquement supprimées.'); ?>
  <?php pp_pol_p('Vous pouvez demander la suppression à tout moment (voir article 7).'); ?>

  <?php pp_pol_h2('6. Destinataires des données'); ?>
  <?php pp_pol_ul([
    'Alexandre Fradin (responsable du traitement) via l\'interface d\'administration',
    'Vous-même (destinataire de votre rapport de résultats)',
    'Hébergeur du site (serveur situé en Union Européenne)',
    'Service d\'envoi d\'e-mails (WordPress / SMTP)',
  ]); ?>

  <?php pp_pol_h2('7. Vos droits'); ?>
  <?php pp_pol_p('Conformément au RGPD et à la loi Informatique et Libertés, vous disposez des droits suivants :'); ?>
  <?php pp_pol_ul([
    '<strong>Droit d\'accès (art. 15)</strong> : obtenir une copie de vos données',
    '<strong>Droit de rectification (art. 16)</strong> : corriger des données inexactes',
    '<strong>Droit à l\'effacement (art. 17)</strong> : supprimer vos données — un lien est inclus dans chaque e-mail de résultats',
    '<strong>Droit à la portabilité (art. 20)</strong> : recevoir vos données dans un format structuré',
    '<strong>Droit d\'opposition (art. 21)</strong> : vous opposer aux relances e-mail',
    '<strong>Retrait du consentement</strong> : à tout moment, sans remettre en cause les traitements antérieurs',
  ]); ?>
  <?php pp_pol_p('Pour exercer ces droits : <a href="mailto:contact@praxis-accompagnement.com" style="color:#E8541A;">contact@praxis-accompagnement.com</a>'); ?>
  <?php pp_pol_p('En cas de réponse insatisfaisante, vous pouvez introduire une réclamation auprès de la <a href="https://www.cnil.fr" target="_blank" style="color:#E8541A;">CNIL</a>.'); ?>

  <?php pp_pol_h2('8. Sécurité'); ?>
  <?php pp_pol_ul([
    'Chiffrement des communications (HTTPS/TLS)',
    'Accès administration protégé par authentification WordPress',
    'Validation et assainissement de toutes les données soumises',
    'Protection anti-abus (rate limiting par IP)',
    'Tokens d\'accès aux profils générés de manière aléatoire et sécurisée',
  ]); ?>

  <?php pp_pol_h2('9. Cookies'); ?>
  <?php pp_pol_p('PraxiMum n\'utilise pas de cookies publicitaires ou de tracking tiers. Des cookies techniques de session peuvent être déposés par WordPress pour le fonctionnement du site. Ils sont strictement nécessaires et ne requièrent pas de consentement.'); ?>

  <?php pp_pol_h2('10. Données sensibles'); ?>
  <?php pp_pol_p('Le test PraxiMum est un outil de clarification professionnelle. <strong>Il ne constitue pas un diagnostic médical ou psychologique.</strong> Les résultats ne sont pas considérés comme des données sensibles au sens de l\'article 9 du RGPD.'); ?>

  <?php pp_pol_h2('11. Transferts hors UE'); ?>
  <?php pp_pol_p('Vos données sont hébergées et traitées au sein de l\'Union Européenne. Aucun transfert vers des pays tiers n\'est effectué.'); ?>

  <?php pp_pol_h2('12. Contact'); ?>
  <div style="background:#EEF3F8;border-radius:12px;padding:20px 24px;margin-top:8px;">
    <strong>Alexandre Fradin</strong> — PraxiMum<br>
    E-mail : <a href="mailto:contact@praxis-accompagnement.com" style="color:#E8541A;">contact@praxis-accompagnement.com</a><br>
    Site : <a href="https://praxis-accompagnement.com" style="color:#E8541A;">praxis-accompagnement.com</a>
  </div>

  <p style="margin-top:40px;padding-top:20px;border-top:1px solid #EEF3F8;font-size:12px;color:#8FA8BE;text-align:center;">
    PraxiMum © Alexandre Fradin &nbsp;·&nbsp; <?php echo date('Y'); ?> &nbsp;·&nbsp; Tous droits réservés
  </p>
</div>
