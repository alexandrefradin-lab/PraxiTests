<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="pemo-app">

  <!-- Barre de progression (visible uniquement sur l'écran question) -->
  <div id="pemo-progress-wrap" class="pemo-hidden">
    <div id="pemo-progress-meta">
      <span id="pemo-progress-famille"></span>
      <span id="pemo-progress-pct">0%</span>
    </div>
    <div id="pemo-progress-track">
      <div id="pemo-progress-fill"></div>
    </div>
  </div>

  <!-- ── ÉCRAN INTRO ─────────────────────────────────────── -->
  <div id="pemo-screen-intro">

    <div class="pemo-intro-card">

      <!-- Header visuel sombre -->
      <div class="pemo-intro-header">
        <div class="pemo-intro-header-badge">Praxis Accompagnement</div>
        <h1 class="pemo-intro-title">Intelligence<br>Émotionnelle</h1>
        <p class="pemo-intro-subtitle">Évaluez votre profil à travers 16 dimensions clés et découvrez vos forces émotionnelles.</p>
        <div class="pemo-intro-stats">
          <div class="pemo-stat">
            <span class="pemo-stat-num">16</span>
            <span class="pemo-stat-label">dimensions</span>
          </div>
          <div class="pemo-stat-sep"></div>
          <div class="pemo-stat">
            <span class="pemo-stat-num">80</span>
            <span class="pemo-stat-label">questions</span>
          </div>
          <div class="pemo-stat-sep"></div>
          <div class="pemo-stat">
            <span class="pemo-stat-num">20<span style="font-size:.55em;opacity:.55;font-weight:700"> min</span></span>
            <span class="pemo-stat-label">environ</span>
          </div>
        </div>
      </div>

      <!-- Zone formulaire -->
      <div class="pemo-intro-form">

        <div class="pemo-intro-disclaimer">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span>Ce test est un <strong>miroir d'exploration</strong>, pas un diagnostic clinique. Il vous donne un profil utile pour amorcer une réflexion ou un accompagnement — pas une vérité définitive sur qui vous êtes.</span>
        </div>

        <label class="pemo-label">
          Prénom
          <input type="text" id="pemo-prenom" class="pemo-input" placeholder="Votre prénom" autocomplete="given-name">
        </label>

        <label class="pemo-label">
          Email
          <input type="email" id="pemo-email" class="pemo-input" placeholder="votre@email.com" autocomplete="email">
        </label>

        <label class="pemo-checkbox">
          <input type="checkbox" id="pemo-consent">
          <span>J'accepte que mes données soient traitées conformément à la <a href="<?php echo PE_Shortcode::get_privacy_url(); ?>" target="_blank" rel="noopener noreferrer">politique de confidentialité</a>.</span>
        </label>

        <div id="pemo-form-error" class="pemo-error"></div>

        <button id="pemo-form-submit" class="pemo-btn-cta">Commencer le test →</button>

        <div class="pemo-badges">
          <span class="pemo-badge">🔒 Confidentiel</span>
          <span class="pemo-badge">📩 Résultats par email</span>
          <span class="pemo-badge">✓ Gratuit</span>
        </div>

      </div>
    </div>

  </div><!-- /screen-intro -->

  <!-- ── ÉCRAN QUESTION ──────────────────────────────────── -->
  <div id="pemo-screen-question" class="pemo-hidden">
    <div class="pemo-q-header">
      <button type="button" class="pemo-btn-back" id="pemo-btn-back">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Retour
      </button>
      <span class="pemo-q-counter" id="pemo-q-counter">1 / 80</span>
    </div>
    <div class="pemo-solo-card" id="pemo-solo-card">
      <p class="pemo-q-text" id="pemo-q-text"></p>
      <div class="pemo-choices" id="pemo-choices"></div>
    </div>
    <p class="pemo-q-hint">Soyez spontané(e) — il n'y a pas de bonne ou mauvaise réponse.</p>
  </div><!-- /screen-question -->

  <!-- ── ÉCRAN TRANSITION ────────────────────────────────── -->
  <div id="pemo-screen-transition" class="pemo-hidden">
    <div class="pemo-trans-inner">
      <div id="pemo-trans-emoji" class="pemo-trans-emoji">✨</div>
      <div id="pemo-trans-title" class="pemo-trans-title"></div>
      <p id="pemo-trans-text" class="pemo-trans-text"></p>
      <button id="pemo-trans-continue" class="pemo-btn-cta" style="max-width:240px;margin:0 auto;display:block;position:relative;z-index:1;">Continuer →</button>
    </div>
  </div><!-- /screen-transition -->

  <!-- ── ÉCRAN CHARGEMENT ────────────────────────────────── -->
  <div id="pemo-screen-loading" class="pemo-hidden">
    <div class="pemo-loading-inner">
      <div class="pemo-spinner-wrap">
        <div class="pemo-spinner"></div>
        <span class="pemo-spinner-icon">🧠</span>
      </div>
      <p class="pemo-loading-title">Analyse en cours…</p>
      <p class="pemo-loading-sub">Calcul de vos 16 dimensions</p>
      <div class="pemo-loading-dots">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div><!-- /screen-loading -->

  <!-- ── ÉCRAN RÉSULTATS ─────────────────────────────────── -->
  <div id="pemo-screen-results" class="pemo-hidden">
    <div id="pemo-results-container"></div>
  </div><!-- /screen-results -->

</div><!-- #pemo-app -->
