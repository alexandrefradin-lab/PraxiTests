<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="praxicare-app">

<!-- ══════════════════════════════════════════
     ÉCRAN INTRO
══════════════════════════════════════════ -->
<div id="pc-intro" class="pc-screen pc-active">
  <div class="pc-intro-wrap">
    <p class="pc-titre-hero">Mesurez votre niveau de souffrance au travail avec PraxiCare</p>
    <p class="pc-bienvenue">Bienvenue 👋</p>
    <p class="pc-sous-titre">Ceci est un outil d'aide à la prise de conscience, il ne remplace pas un accompagnement humain et ne constitue pas un diagnostic médical. Il est composé de 48 questions couvrant 6 dimensions. Comptez environ 10 minutes.</p>
    <div class="pc-bloc-info">
      <span class="pc-bloc-info-icon">🧭</span>
      <div>
        <p class="pc-bloc-info-titre">Ce test est un point de départ.</p>
        <p class="pc-bloc-info-texte">Pour aller plus loin, comprendre les sources de votre mal-être, retrouver du sens et passer à l'action : un <strong>accompagnement personnalisé</strong> est fait pour vous.</p>
      </div>
    </div>
    <div class="pc-badges">
      <span class="pc-badge">⏱️ 10 minutes</span>
      <span class="pc-badge">🔒 Confidentiel</span>
      <span class="pc-badge">🎯 Résultats immédiats</span>
      <span class="pc-badge">🆓 Gratuit</span>
    </div>
    <div class="pc-form-intro">
      <label class="pc-label" for="pc-prenom-input">Prénom</label>
      <input type="text" id="pc-prenom-input" class="pc-input" placeholder="Votre prénom" autocomplete="given-name">
      <button id="pc-btn-start" class="pc-btn-cta">Commencer le test →</button>
      <p class="pc-social-proof">Rejoignez les +3 200 personnes qui ont déjà mesuré leur bien-être au travail.</p>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     ÉCRAN FILTRE SUPÉRIEUR
══════════════════════════════════════════ -->
<div id="pc-filtre-superieur" class="pc-screen">
  <div class="pc-question-wrap">
    <div class="pc-question-card">
      <p class="pc-question-texte">Avez-vous un supérieur hiérarchique ?</p>
      <div class="pc-choix">
        <button id="pc-filtre-oui" class="pc-choix-btn">Oui</button>
        <button id="pc-filtre-non" class="pc-choix-btn">Non</button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     ÉCRAN QUESTIONS
══════════════════════════════════════════ -->
<div id="pc-questions" class="pc-screen">
  <div class="pc-progress-bar-wrap">
    <div class="pc-progress-top">
      <span id="pc-section-label">Charge de travail</span>
      <span id="pc-progress-pct" class="pc-pct">0%</span>
    </div>
    <div class="pc-progress-track">
      <div id="pc-progress-fill" class="pc-progress-fill" style="width:0%"></div>
    </div>
    <div class="pc-progress-nav">
      <button id="pc-btn-retour" class="pc-btn-retour">← Retour</button>
      <span id="pc-compteur" class="pc-compteur">1 / 48</span>
    </div>
  </div>
  <div class="pc-question-wrap">
    <div class="pc-question-card">
      <p id="pc-question-texte" class="pc-question-texte"></p>
      <div id="pc-choix" class="pc-choix"></div>
    </div>
  </div>
  <div id="pc-transition" class="pc-transition pc-hidden">
    <p id="pc-transition-texte" class="pc-transition-texte"></p>
    <button id="pc-btn-continuer" class="pc-btn-cta">Continuer →</button>
  </div>
</div>

<!-- ══════════════════════════════════════════
     ÉCRAN GATE EMAIL (avant résultats)
══════════════════════════════════════════ -->
<div id="pc-gate-email" class="pc-screen">
  <div class="pc-intro-wrap">
    <p class="pc-titre-hero">Vos résultats sont prêts, <span id="pc-gate-prenom"></span> 🎉</p>
    <p class="pc-sous-titre">Renseignez votre email pour accéder à votre diagnostic personnalisé et le recevoir dans votre boîte mail.</p>
    <div class="pc-form-intro">
      <label class="pc-label" for="pc-gate-email-input">Email</label>
      <input type="email" id="pc-gate-email-input" class="pc-input" placeholder="votre@email.com" autocomplete="email">
      <label class="pc-rgpd-label">
        <input type="checkbox" id="pc-gate-rgpd">
        J'accepte que mes données soient traitées conformément à la <a href="#" class="pc-link-orange">politique de confidentialité</a>.
      </label>
      <button id="pc-btn-gate-send" class="pc-btn-cta">Voir mes résultats →</button>
      <p id="pc-gate-error" class="pc-hidden" style="color:#E8541A;font-size:13px;text-align:center;margin-top:8px;">Merci de renseigner un email valide et d'accepter la politique de confidentialité.</p>
      <p class="pc-rgpd-note">Vos données ne sont jamais revendues. Vous pouvez demander leur suppression à tout moment.</p>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     ÉCRAN RÉSULTATS
══════════════════════════════════════════ -->
<div id="pc-results" class="pc-screen">
  <div class="pc-results-wrap">

    <h2 class="pc-results-titre">Vos résultats, <span id="pc-prenom-display"></span></h2>

    <p class="pc-disclaimer-results">
      Ces résultats sont indicatifs et ne constituent pas un avis médical. Ils ne remplacent en aucun cas une consultation avec un professionnel de santé.
    </p>

    <div class="pc-graphs-row">
      <div class="pc-graph-block">
        <p class="pc-graph-titre">Modèle de Karasek</p>
        <div class="pc-graph-container">
          <canvas id="pc-chart-karasek"></canvas>
          <div class="pc-karasek-labels">
            <span class="pc-kl pc-kl-tl">Travail<br>détendu</span>
            <span class="pc-kl pc-kl-tr">Travail<br>actif</span>
            <span class="pc-kl pc-kl-bl">Travail<br>passif</span>
            <span class="pc-kl pc-kl-br">Travail sous<br>tension ⚠️</span>
          </div>
        </div>
      </div>
      <div class="pc-graph-block">
        <p class="pc-graph-titre">Inventaire MBI Praxis</p>
        <div id="pc-mbi-cards" class="pc-mbi-cards"></div>
      </div>
    </div>

    <div class="pc-analyse-block">
      <p class="pc-analyse-titre">🔍 Analyse détaillée par dimension</p>
      <div id="pc-analyse-list" class="pc-analyse-list"></div>
    </div>

    <div id="pc-profil-block" class="pc-profil-block">
      <p id="pc-profil-emoji" class="pc-profil-emoji"></p>
      <h3 id="pc-profil-titre" class="pc-profil-titre"></h3>
      <p id="pc-profil-texte" class="pc-profil-texte"></p>
    </div>

    <div id="pc-urgence-block" class="pc-urgence-block pc-hidden">
      <p class="pc-urgence-titre">💙 Si vous traversez une période difficile, vous n'êtes pas seul(e).</p>
      <p class="pc-urgence-sous">Des professionnels sont disponibles pour vous écouter, gratuitement et en toute confidentialité, 24h/24, au <strong>3114</strong>.</p>
    </div>

    <div class="pc-preco-block">
      <p class="pc-preco-titre">📋 Vos préconisations personnalisées</p>
      <ul id="pc-preco-list" class="pc-preco-list"></ul>
    </div>

    <div class="pc-email-success-block">
      <p>✅ Votre rapport a été envoyé à <strong id="pc-email-sent-to"></strong>. Vérifiez votre boîte mail.</p>
    </div>

  </div>
</div>

</div><!-- #praxicare-app -->
