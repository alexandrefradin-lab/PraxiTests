<?php if ( ! defined( 'ABSPATH' ) ) exit;
$_c1 = sanitize_hex_color( get_option('pp_color_primary',   '#E8541A') ) ?: '#E8541A';
$_c2 = sanitize_hex_color( get_option('pp_color_secondary', '#1E2A3A') ) ?: '#1E2A3A';
?>
<style>
/* ── PraxiMum — styles critiques inline ── */
#pp-test-container *{box-sizing:border-box;}
#pp-test-container{max-width:700px;margin:0 auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
#pp-test-container .pp-progress-bar-wrap{height:6px;background:#dde8f0;border-radius:999px;overflow:hidden;margin-bottom:6px;}
#pp-test-container .pp-progress-bar{height:100%;background:<?php echo esc_attr($_c1); ?>;border-radius:999px;transition:width .5s ease;}
#pp-test-container .pp-step{padding:10px 0;}
#pp-test-container .pp-step-title{font-size:22px;font-weight:800;color:<?php echo esc_attr($_c2); ?>;margin-bottom:12px;}
#pp-test-container .pp-label{display:block;font-size:14px;font-weight:600;color:<?php echo esc_attr($_c2); ?>;margin-bottom:14px;}
#pp-test-container .pp-input{display:block;width:100%;padding:11px 14px;border:1.5px solid #dde8f0;border-radius:10px;font-family:inherit;font-size:14px;color:#1E2A3A;margin-top:5px;background:#fff;}
#pp-test-container .pp-input:focus{outline:none;border-color:<?php echo esc_attr($_c1); ?>;}
#pp-test-container .pp-checkbox{display:flex;gap:8px;align-items:flex-start;font-size:13px;color:#8FA8BE;margin-bottom:16px;cursor:pointer;}
#pp-test-container .pp-btn{display:inline-block;padding:12px 28px;border-radius:999px;border:none;cursor:pointer;font-family:inherit;font-size:14px;font-weight:700;}
#pp-test-container .pp-btn-next{background:<?php echo esc_attr($_c1); ?>;color:#fff;box-shadow:0 4px 14px rgba(232,84,26,.3);}
#pp-test-container .pp-btn-outline{background:#fff;border:2px solid <?php echo esc_attr($_c1); ?>;color:<?php echo esc_attr($_c1); ?>;}
#pp-test-container .pp-question{margin-bottom:28px;}
#pp-test-container .pp-q-text{font-size:15px;font-weight:600;color:<?php echo esc_attr($_c2); ?>;margin-bottom:12px;line-height:1.5;}
#pp-test-container .pp-q-num{color:<?php echo esc_attr($_c1); ?>;font-weight:800;}
#pp-test-container .pp-likert{display:flex;flex-wrap:wrap;gap:8px;}
#pp-test-container .pp-likert-opt{display:flex;align-items:center;gap:8px;padding:9px 16px;border:2px solid #dde8f0;border-radius:999px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;color:#2E4A6A;transition:border-color .15s,background .15s,color .15s;}
#pp-test-container .pp-likert-opt:hover{border-color:<?php echo esc_attr($_c1); ?>;color:<?php echo esc_attr($_c1); ?>;}
#pp-test-container .pp-likert-sel{border-color:<?php echo esc_attr($_c1); ?> !important;background:<?php echo esc_attr($_c1); ?> !important;color:#fff !important;}
#pp-test-container .pp-likert-opt input[type=radio]{display:none;}
#pp-test-container .pp-nav{display:flex;gap:12px;margin-top:24px;justify-content:space-between;}
#pp-test-container .pp-loader{display:flex;flex-direction:column;align-items:center;padding:40px;gap:16px;}
#pp-test-container .pp-spinner{width:40px;height:40px;border:3px solid #EEF3F8;border-top-color:<?php echo esc_attr($_c1); ?>;border-radius:50%;animation:pp-spin .7s linear infinite;}
@keyframes pp-spin{to{transform:rotate(360deg);}}
#pp-test-container .pp-q-missing{animation:pp-shake .4s ease;}
@keyframes pp-shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
#pp-test-container .pp-reprise-banner{background:#EEF3F8;border-left:4px solid <?php echo esc_attr($_c1); ?>;border-radius:12px;padding:14px 18px;margin-bottom:20px;}
#pp-test-container .pp-reprise-inner{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
#pp-test-container .pp-reprise-text{display:flex;flex-direction:column;gap:3px;font-size:13px;color:#1E2A3A;}
#pp-test-container .pp-reprise-actions{display:flex;gap:8px;}
</style>
<div id="pp-test-container" class="pp-container">

  <!-- Barre de progression -->
  <div class="pp-progress-bar-wrap">
    <div class="pp-progress-bar" id="pp-progress" style="width:0%"></div>
  </div>
  <div class="pp-progress-label" id="pp-progress-label">Étape 1 / 16</div>

  <!-- Formulaire multi-étapes -->
  <form id="pp-form">
    <?php wp_nonce_field( 'pp_nonce', 'pp_nonce_field' ); ?>

    <!-- Étape 0 : infos -->
    <div class="pp-step" data-step="0">
      <h2 class="pp-step-title">Bienvenue 👋</h2>
      <p class="pp-step-intro"><?php echo wp_kses_post( get_option('pp_texte_intro', "Ceci est un outil de clarification, il ne remplace pas un accompagnement humain et ne constitue pas un diagnostic. Il est composé de 128 questions couvrant 5 grandes dimensions et 30 facettes. Comptez environ 12 minutes.") ); ?></p>

      <!-- Bloc point de départ -->
      <?php
        $c1 = sanitize_hex_color(get_option('pp_color_primary','#E8541A')) ?: '#E8541A';
        $c2 = sanitize_hex_color(get_option('pp_color_secondary','#1E2A3A')) ?: '#1E2A3A';
      ?>
      <div class="pp-depart-box" style="display:flex;gap:16px;align-items:flex-start;background:#EEF3F8;border:1px solid #C8D9E8;border-left:4px solid <?php echo esc_attr($c1); ?>;border-radius:12px;padding:20px;margin:18px 0 22px;">
        <div style="font-size:28px;flex-shrink:0;margin-top:2px;">🧭</div>
        <div style="flex:1;">
          <p style="font-size:15px;font-weight:700;color:<?php echo esc_attr($c1); ?>;margin:0 0 6px;">Ce test est un point de départ.</p>
          <p style="font-size:14px;color:#1E2A3A;margin:0 0 14px;line-height:1.6;">Pour aller plus loin — mieux vous connaître, clarifier votre orientation professionnelle et passer à l'action — le <strong>bilan de compétences</strong> est fait pour vous.</p>
          <a href="<?php echo esc_url( get_option('pp_rdv_url', home_url('/contact')) ); ?>"
             style="display:inline-block;background:<?php echo esc_attr($c1); ?>;color:#fff;font-size:14px;font-weight:700;padding:11px 22px;border-radius:999px;text-decoration:none;box-shadow:0 2px 10px rgba(0,0,0,.15);transition:opacity .2s;"
             target="_blank" rel="noopener"
             onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            <?php echo esc_html( get_option('pp_texte_rdv_cta', 'Découvrir le bilan de compétences →') ); ?>
          </a>
        </div>
      </div>

      <label class="pp-label">Prénom <input type="text" name="prenom" class="pp-input" placeholder="Votre prénom" required></label>
      <label class="pp-label">Email  <input type="email" name="email" class="pp-input" placeholder="votre@email.com" required></label>
      <label class="pp-checkbox">
        <input type="checkbox" name="consentement" value="1" required>
        J'accepte que mes données soient traitées conformément à la <a href="<?php echo esc_url( get_option('pp_politique_url', home_url('/politique-confidentialite')) ); ?>" target="_blank">politique de confidentialité</a>.
      </label>
      <button type="button" class="pp-btn pp-btn-next" onclick="ppStartTest()" style="margin-top:20px;">Commencer le test →</button>
    </div>

    <!-- Étapes questions (injectées dynamiquement) -->
    <div id="pp-questions-container"></div>

    <!-- Navigation -->
    <div class="pp-nav" id="pp-nav" style="display:none">
      <button type="button" class="pp-btn pp-btn-outline" id="pp-btn-prev" onclick="ppPrev()">← Précédent</button>
      <button type="button" class="pp-btn pp-btn-next"   id="pp-btn-next" onclick="ppNext()">Suivant →</button>
    </div>
  </form>

  <!-- Résultats -->
  <div id="pp-results" style="display:none">
    <div class="pp-result-block" id="pp-result-block"></div>
  </div>

  <!-- Loader -->
  <div id="pp-loader" style="display:none" class="pp-loader">
    <div class="pp-spinner"></div>
    <p>Analyse de votre profil en cours…</p>
  </div>
</div>

<script>
(function(){
  var steps      = [];
  var totalSteps = 0;
  var currentStep = -1; // -1 = étape infos
  var answers    = {};
  var ajaxUrl    = '<?php echo admin_url("admin-ajax.php"); ?>';
  var nonce      = '<?php echo wp_create_nonce("pp_nonce"); ?>';
  var STORAGE_KEY = 'pp_progression_v1';

  // ── Likert ────────────────────────────────────────────────
  var LIKERT = [
    {val:1, label:'Pas moi'},
    {val:2, label:'Un peu moi'},
    {val:3, label:'Assez moi'},
    {val:4, label:'Tout à fait moi'},
  ];

  var allQuestions = <?php echo wp_json_encode( PP_Questions::get_all() ); ?>;
  var perPage = 8;

  function chunkSteps(arr, size) {
    var chunks = [];
    for (var i = 0; i < arr.length; i += size) chunks.push(arr.slice(i, i+size));
    return chunks;
  }
  steps      = chunkSteps(allQuestions, perPage);
  totalSteps = steps.length;

  // ══════════════════════════════════════════════════════════
  // SAUVEGARDE / RESTAURATION localStorage
  // ══════════════════════════════════════════════════════════

  function sauvegarder() {
    try {
      var data = {
        prenom:      document.querySelector('[name="prenom"]').value.trim(),
        email:       document.querySelector('[name="email"]').value.trim(),
        consentement:document.querySelector('[name="consentement"]').checked,
        step:        currentStep,
        answers:     answers,
        ts:          Date.now(),
      };
      localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch(e) { /* localStorage indisponible — mode privé ou quota dépassé */ }
  }

  function chargerSauvegarde() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return null;
      var data = JSON.parse(raw);
      // Expiration : 7 jours
      if (!data.ts || Date.now() - data.ts > 7 * 24 * 3600 * 1000) {
        localStorage.removeItem(STORAGE_KEY);
        return null;
      }
      return data;
    } catch(e) { return null; }
  }

  function effacerSauvegarde() {
    try { localStorage.removeItem(STORAGE_KEY); } catch(e) {}
  }

  function afficherBanniereReprise(data) {
    var nb      = Object.keys(data.answers).length;
    var pct     = Math.round(nb / allQuestions.filter(function(q){ return q.dim !== 'DS'; }).length * 100);
    var etape   = data.step + 1;
    var banner  = document.createElement('div');
    banner.id   = 'pp-reprise-banner';
    banner.className = 'pp-reprise-banner';
    banner.innerHTML =
      '<div class="pp-reprise-inner">'
      + '<div class="pp-reprise-text">'
      + '  <strong>🔖 Progression sauvegardée détectée</strong>'
      + '  <span>Étape ' + etape + ' / ' + totalSteps + ' — ' + pct + '% complété</span>'
      + '</div>'
      + '<div class="pp-reprise-actions">'
      + '  <button class="pp-btn pp-btn-next pp-btn-sm" id="pp-btn-reprendre">Reprendre →</button>'
      + '  <button class="pp-btn pp-btn-outline pp-btn-sm" id="pp-btn-recommencer">Recommencer</button>'
      + '</div>'
      + '</div>';
    var container = document.getElementById('pp-test-container');
    container.insertBefore(banner, container.firstChild);

    document.getElementById('pp-btn-reprendre').addEventListener('click', function() {
      banner.remove();
      // Restaurer les champs infos
      document.querySelector('[name="prenom"]').value = data.prenom || '';
      document.querySelector('[name="email"]').value  = data.email  || '';
      if (data.consentement) document.querySelector('[name="consentement"]').checked = true;
      // Restaurer les réponses
      answers = data.answers || {};
      // Reprendre à l'étape sauvegardée
      document.querySelector('[data-step="0"]').style.display = 'none';
      document.getElementById('pp-nav').style.display = 'flex';
      currentStep = data.step || 0;
      renderStep(currentStep);
      updateProgress();
      showMotivation('👋 Bienvenue ! Vous reprenez où vous en étiez.');
    });

    document.getElementById('pp-btn-recommencer').addEventListener('click', function() {
      effacerSauvegarde();
      banner.remove();
      showMotivation('✅ Progression effacée — nouveau départ !');
    });
  }

  // ── Init : vérifier sauvegarde au chargement ──────────────
  (function() {
    var saved = chargerSauvegarde();
    if (saved && saved.step >= 0 && Object.keys(saved.answers || {}).length > 0) {
      afficherBanniereReprise(saved);
    }
  })();

  // ══════════════════════════════════════════════════════════
  // NAVIGATION
  // ══════════════════════════════════════════════════════════

  window.ppStartTest = function() {
    var prenom  = document.querySelector('[name="prenom"]').value.trim();
    var email   = document.querySelector('[name="email"]').value.trim();
    var consent = document.querySelector('[name="consentement"]').checked;
    if (!prenom || !email) { alert('Prénom et email sont obligatoires.'); return; }
    if (!consent)          { alert('Veuillez accepter la politique de confidentialité.'); return; }
    document.querySelector('[data-step="0"]').style.display = 'none';
    document.getElementById('pp-nav').style.display = 'flex';
    currentStep = 0;
    renderStep(0);
    updateProgress();
    sauvegarder();
  };

  function renderStep(idx) {
    var container = document.getElementById('pp-questions-container');
    var qs = steps[idx];
    var html = '<div class="pp-step pp-step-q" id="pp-step-q-' + idx + '">';
    qs.forEach(function(q, qi) {
      html += '<div class="pp-question" id="q-wrap-' + q.id + '">';
      html += '<p class="pp-q-text"><span class="pp-q-num">' + (idx * perPage + qi + 1) + '.</span> ' + escHtml(q.texte) + '</p>';
      html += '<div class="pp-likert">';
      LIKERT.forEach(function(opt) {
        var checked = answers[q.id] === opt.val ? 'checked' : '';
        html += '<label class="pp-likert-opt ' + (checked ? 'pp-likert-sel' : '') + '">'
          + '<input type="radio" name="q' + q.id + '" value="' + opt.val + '" ' + checked
          + ' onchange="ppAnswer(' + q.id + ',' + opt.val + ',this)">'
          + '<span class="pp-likert-dot"></span>'
          + '<span class="pp-likert-label">' + escHtml(opt.label) + '</span>'
          + '</label>';
      });
      html += '</div></div>';
    });
    html += '</div>';
    container.innerHTML = html;
    container.scrollIntoView({behavior:'smooth', block:'start'});
    document.getElementById('pp-btn-prev').style.visibility = idx === 0 ? 'hidden' : 'visible';
    document.getElementById('pp-btn-next').textContent = idx === totalSteps - 1 ? 'Voir mes résultats 🎯' : 'Suivant →';
  }

  window.ppAnswer = function(qid, val, input) {
    answers[qid] = val;
    var wrap = document.getElementById('q-wrap-' + qid);
    wrap.querySelectorAll('.pp-likert-opt').forEach(function(l){ l.classList.remove('pp-likert-sel'); });
    input.closest('.pp-likert-opt').classList.add('pp-likert-sel');
    // Sauvegarde silencieuse à chaque réponse
    sauvegarder();
  };

  window.ppNext = function() {
    var qs = steps[currentStep];
    // Ignorer les questions DS (optionnelles)
    var missing = qs.filter(function(q){ return q.dim !== 'DS' && !answers[q.id]; });
    if (missing.length) {
      missing.forEach(function(q){
        var w = document.getElementById('q-wrap-' + q.id);
        w.classList.add('pp-q-missing');
        setTimeout(function(){ w.classList.remove('pp-q-missing'); }, 1800);
      });
      document.getElementById('q-wrap-' + missing[0].id).scrollIntoView({behavior:'smooth', block:'center'});
      return;
    }
    if (currentStep < totalSteps - 1) {
      currentStep++;
      renderStep(currentStep);
      updateProgress();
      sauvegarder();
    } else {
      submitTest();
    }
  };

  window.ppPrev = function() {
    if (currentStep > 0) {
      currentStep--;
      renderStep(currentStep);
      updateProgress();
      sauvegarder();
    }
  };

  function updateProgress() {
    var pct = Math.round((currentStep / totalSteps) * 100);
    document.getElementById('pp-progress').style.width = pct + '%';
    document.getElementById('pp-progress-label').textContent = 'Étape ' + (currentStep + 1) + ' / ' + totalSteps;
    var msgs = {
      4:  '🔥 Super début ! Continuez comme ça.',
      8:  '✅ Vous êtes à mi-chemin — excellent !',
      12: '🚀 Presque terminé, encore un effort !',
      15: '🎯 Dernière étape — votre profil vous attend !',
    };
    if (msgs[currentStep]) showMotivation(msgs[currentStep]);
  }

  function showMotivation(msg) {
    var el = document.getElementById('pp-motivation');
    if (!el) {
      el = document.createElement('div');
      el.id = 'pp-motivation';
      el.className = 'pp-motivation-toast';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.classList.add('show');
    setTimeout(function(){ el.classList.remove('show'); }, 2800);
  }

  function submitTest() {
    document.getElementById('pp-questions-container').style.display = 'none';
    document.getElementById('pp-nav').style.display = 'none';
    document.getElementById('pp-loader').style.display = 'flex';

    var formData = new FormData();
    formData.append('action', 'pp_submit');
    formData.append('nonce', nonce);
    formData.append('prenom', document.querySelector('[name="prenom"]').value.trim());
    formData.append('email',  document.querySelector('[name="email"]').value.trim());
    formData.append('consentement', document.querySelector('[name="consentement"]').checked ? 1 : 0);
    Object.keys(answers).forEach(function(k){ formData.append('reponses['+k+']', answers[k]); });

    // Transmettre le token d'invitation batch si présent
    var urlParams = new URLSearchParams(window.location.search);
    var inviteTk  = urlParams.get('pp_invite_tk') || '';
    if (inviteTk) formData.append('pp_invite_tk', inviteTk);

    // Pré-remplissage email/prénom depuis le lien batch
    var ppEmail  = urlParams.get('pp_email');
    var ppPrenom = urlParams.get('pp_prenom');
    var ppSource = urlParams.get('pp_source');
    if (ppSource) formData.append('source', ppSource);

    fetch(ajaxUrl, { method:'POST', body:formData })
      .then(function(r){ return r.json(); })
      .then(function(data){
        document.getElementById('pp-loader').style.display = 'none';
        if (data.success) {
          effacerSauvegarde(); // ✅ Test terminé — on nettoie

          // ── Tracking ──────────────────────────────────────
          var tr = data.data.tracking || {};

          // Google Tag Manager / GA4 via dataLayer
          if (tr.gtm_event || tr.ga_event) {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
              event:          tr.gtm_event || tr.ga_event,
              event_category: 'test_personnalite',
              archetype:      (data.data.archetype || {}).nom || '',
              prenom:         data.data.prenom || '',
            });
          }

          // GA4 direct (gtag)
          if (tr.ga_event && window.gtag) {
            window.gtag('event', tr.ga_event, {
              event_category: 'test_personnalite',
              archetype: (data.data.archetype || {}).nom || '',
            });
          }

          // Meta Pixel
          if (tr.meta_pixel_id && tr.meta_event) {
            if (!window.fbq) {
              (function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
              n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
              n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
              t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)})(window,
              document,'script','https://connect.facebook.net/en_US/fbevents.js');
              window.fbq('init', tr.meta_pixel_id);
            }
            window.fbq('track', tr.meta_event);
          }
          // ── Fin tracking ──────────────────────────────────

          // Redirection page merci (si configurée)
          if (data.data.merci_url) {
            renderResults(data.data); // Afficher d'abord les résultats
            setTimeout(function() {
              window.location.href = data.data.merci_url;
            }, 4000); // Laisser 4s pour voir l'archétype avant redirect
          } else {
            renderResults(data.data);
          }
        } else {
          alert(data.data.message || 'Une erreur est survenue.');
          document.getElementById('pp-questions-container').style.display = '';
          document.getElementById('pp-nav').style.display = 'flex';
        }
      })
      .catch(function(){
        alert('Erreur réseau. Votre progression est sauvegardée — vous pouvez réessayer.');
        document.getElementById('pp-loader').style.display = 'none';
        document.getElementById('pp-questions-container').style.display = '';
        document.getElementById('pp-nav').style.display = 'flex';
      });
  }

  function renderResults(d) {
    var arch = d.archetype;
    var dims = d.scores_dim;
    var rdv  = d.rdv_url;
    var c1   = arch.couleur1 || 'var(--pp-c1,#E8541A)';
    var c2   = arch.couleur2 || 'var(--pp-c2,#1E2A3A)';

    var dimCfg = {
      O:{icon:'🔭',label:'Ouverture',    color:'#E8541A'},
      C:{icon:'🗂️',label:'Conscience',   color:'#1E2A3A'},
      E:{icon:'💬',label:'Extraversion', color:'#C4430F'},
      A:{icon:'🤝',label:'Agréabilité',  color:'#2E4A6A'},
      N:{icon:'🌊',label:'Stabilité',    color:'#8FA8BE'},
    };

    var bars = '';
    Object.keys(dimCfg).forEach(function(k){
      var cfg = dimCfg[k]; var pct = dims[k] ? dims[k].pct : 50; var lbl = dims[k] ? dims[k].label : '';
      bars += '<div style="margin-bottom:14px;">'
        + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">'
        + '<span style="font-size:14px;font-weight:700;color:#1e293b;">' + cfg.icon + ' ' + cfg.label + '</span>'
        + '<span style="font-size:14px;font-weight:800;color:' + cfg.color + ';">' + pct + '% <span style="font-size:11px;font-weight:500;color:#94a3b8;">— ' + lbl + '</span></span>'
        + '</div>'
        + '<div style="background:#e2e8f0;border-radius:999px;height:10px;overflow:hidden;">'
        + '<div class="pp-bar-anim" style="background:' + cfg.color + ';height:10px;width:0%;border-radius:999px;transition:width 1s cubic-bezier(.4,0,.2,1);" data-target="' + pct + '"></div>'
        + '</div></div>';
    });

    var traitsHtml = '';
    if (arch.traits && arch.traits.length) {
      traitsHtml = '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">';
      arch.traits.forEach(function(tr){
        traitsHtml += '<span style="background:linear-gradient(135deg,'+c1+','+c2+');color:#fff;padding:5px 14px;border-radius:999px;font-size:13px;font-weight:600;">'+escHtml(tr)+'</span>';
      });
      traitsHtml += '</div>';
    }

    var html = ''
      + '<div style="background:linear-gradient(135deg,'+c1+','+c2+');border-radius:20px;padding:40px 32px;text-align:center;color:#fff;margin-bottom:24px;">'
      + '<div style="font-size:72px;line-height:1;margin-bottom:12px;filter:drop-shadow(0 4px 20px rgba(0,0,0,.2))">'+escHtml(arch.emoji)+'</div>'
      + '<p style="margin:0 0 4px;font-size:12px;text-transform:uppercase;letter-spacing:.12em;opacity:.75;font-weight:600;">Votre archétype</p>'
      + '<h2 style="font-size:32px;font-weight:900;margin:0 0 8px;letter-spacing:-.02em">'+escHtml(arch.nom)+'</h2>'
      + '<p style="font-size:16px;font-style:italic;margin:0 0 14px;opacity:.9;">'+escHtml(arch.tagline)+'</p>'
      + '<div style="display:inline-block;background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.3);border-radius:999px;padding:6px 20px;font-size:13px;font-weight:700;">✨ '+arch.rarete+'% des personnes</div>'
      + '</div>'
      + '<div style="background:#f8fafc;border-left:4px solid '+c1+';border-radius:0 12px 12px 0;padding:16px 20px;margin-bottom:24px;">'
      + '<p style="margin:0;color:#334155;font-size:15px;line-height:1.75;">'+escHtml(arch.description)+'</p>'
      + '</div>'
      + traitsHtml
      + '<div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:24px;margin-bottom:24px;">'
      + '<h3 style="margin:0 0 16px;font-size:15px;font-weight:800;color:#1e293b;">📊 Vos 5 dimensions</h3>'
      + bars + '</div>'
      + d.carte_html
      + '<div style="text-align:center;margin:24px 0;">'
      + '<button onclick="ppGeneratePDFFromResults()" style="background:linear-gradient(135deg,'+c1+','+c2+');color:#fff;border:none;cursor:pointer;padding:13px 28px;border-radius:999px;font-size:14px;font-weight:700;font-family:inherit;box-shadow:0 4px 14px rgba(232,84,26,.3);">⬇ Télécharger mon rapport PDF</button>'
      + '</div>'
      + '<div style="background:linear-gradient(135deg,#EEF3F8,#E4ECF4);border:1px solid #C8D9E8;border-left:4px solid '+c1+';border-radius:14px;padding:24px 28px;margin:8px 0 32px;">'      + '<p style="font-size:12px;font-weight:700;color:'+c1+';text-transform:uppercase;letter-spacing:.08em;margin:0 0 8px;">🎯 Et maintenant ?</p>'      + '<p style="font-size:17px;font-weight:800;color:#1E2A3A;margin:0 0 10px;line-height:1.4;">Votre profil révèle votre potentiel. Le bilan de compétences vous aide à le transformer en projet concret.</p>'      + '<p style="font-size:14px;color:#2E4A6A;margin:0 0 20px;line-height:1.6;">Clarifiez vos forces, vos motivations profondes et construisez un projet professionnel aligné avec qui vous êtes vraiment.</p>'      + '<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">'      + '<a href="'+escHtml(rdv)+'" style="display:inline-block;background:'+c1+';color:#fff;padding:14px 32px;border-radius:999px;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 4px 14px rgba(232,84,26,.3);">✦ Démarrer mon bilan de compétences</a>'      + '<a href="'+escHtml(d.profil_url||rdv)+'" style="display:inline-block;padding:14px 22px;border:2px solid '+c1+';color:'+c1+';border-radius:999px;text-decoration:none;font-size:14px;font-weight:600;">🔗 Voir mon profil</a>'      + '</div>'      + '</div>';

    var block = document.getElementById('pp-result-block');
    block.innerHTML = html;
    document.getElementById('pp-results').style.display = 'block';
    var wrap = document.getElementById('pp-test-container').querySelector('.pp-progress-bar-wrap');
    if (wrap) wrap.style.display = 'none';
    var lbl = document.getElementById('pp-progress-label');
    if (lbl) lbl.style.display = 'none';

    requestAnimationFrame(function(){
      requestAnimationFrame(function(){
        block.querySelectorAll('.pp-bar-anim').forEach(function(el){
          el.style.width = el.getAttribute('data-target') + '%';
        });
      });
    });

    block.scrollIntoView({behavior:'smooth', block:'start'});

    window.PP_CURRENT_PROFILE = {
      prenom: d.prenom, archetype: d.archetype,
      scores_dim: d.scores_dim, scores_facette: d.scores_facette || {},
      facettes_map: d.facettes_map || {}, rdv_url: d.rdv_url,
      profil_url: d.profil_url, site_name: d.site_name || document.title,
      date: new Date().toLocaleDateString('fr-FR'),
    };
  }

  function ppGeneratePDFFromResults() {
    if (window.PP_CURRENT_PROFILE && window.ppGeneratePDF) {
      ppGeneratePDF(window.PP_CURRENT_PROFILE);
    } else {
      ppToast('⚠️ Données de profil non disponibles. Rechargez la page.');
    }
  }

  function escHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
  }
})();
</script>
