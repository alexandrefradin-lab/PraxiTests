/* ============================================================
   PraxiEmo — main.js  |  Design calqué PraxiMum / PraxiCare
   JS vanilla — animations slide, pills, barre progression
   ============================================================ */
(function () {
  'use strict';

  /* ── State ──────────────────────────────────────────────── */
  var state = {
    prenom       : '',
    email        : '',
    token        : '',
    qIndex       : -1,
    answers      : {},
    lastFamille  : 0,
    submitting   : false,
  };

  /* ── Data injectée par WordPress ────────────────────────── */
  var DATA        = window.PEMO_DATA || {};
  var QUESTIONS   = DATA.questions   || [];
  var TRANSITIONS = DATA.transitions || {};

  var LIKERT = [
    { val: 1, label: 'Jamais'   },
    { val: 2, label: 'Rarement' },
    { val: 3, label: 'Souvent'  },
    { val: 4, label: 'Toujours' },
  ];

  /* ── Refs DOM ───────────────────────────────────────────── */
  function $ (id) { return document.getElementById(id); }

  /* ── Init ───────────────────────────────────────────────── */
  function init() {
    var btnStart = $('pemo-form-submit');
    if (btnStart) btnStart.addEventListener('click', handleStart);

    var btnBack = $('pemo-btn-back');
    if (btnBack) btnBack.addEventListener('click', handleBack);
  }

  /* ── Écrans ─────────────────────────────────────────────── */
  function showScreen(name) {
    ['intro','question','transition','loading','results'].forEach(function (s) {
      var el = $('pemo-screen-' + s);
      if (el) el.classList.add('pemo-hidden');
    });
    var t = $('pemo-screen-' + name);
    if (t) t.classList.remove('pemo-hidden');

    var bar = $('pemo-progress-wrap');
    if (bar) {
      if (name === 'question') bar.classList.remove('pemo-hidden');
      else bar.classList.add('pemo-hidden');
    }

    var app = $('pemo-app');
    if (app) window.scrollTo({ top: app.getBoundingClientRect().top + window.pageYOffset - 20, behavior: 'smooth' });
  }

  /* ── Formulaire intro ───────────────────────────────────── */
  function handleStart(e) {
    e.preventDefault();
    var prenom  = ($('pemo-prenom').value || '').trim();
    var email   = ($('pemo-email').value  || '').trim();
    var consent = $('pemo-consent') && $('pemo-consent').checked;

    if (!prenom || !email || !isEmail(email)) {
      showError('Veuillez entrer votre prénom et une adresse email valide.'); return;
    }
    if (!consent) {
      showError('Veuillez accepter la politique de confidentialité.'); return;
    }
    hideError();

    var btn = $('pemo-form-submit');
    btn.disabled    = true;
    btn.textContent = 'Démarrage…';

    ajaxPost('pemo_start', { prenom: prenom, email: email }, function (data) {
      state.prenom      = data.prenom;
      state.email       = email;
      state.token       = data.token;
      state.qIndex      = 0;
      state.answers     = {};
      state.lastFamille = 0;
      showScreen('question');
      showQuestion(0, false);
    }, function (msg) {
      btn.disabled    = false;
      btn.textContent = 'Commencer le test →';
      showError(msg || 'Une erreur est survenue. Veuillez réessayer.');
    });
  }

  function showError(msg) {
    var el = $('pemo-form-error');
    if (el) { el.textContent = msg; el.classList.add('visible'); }
  }
  function hideError() {
    var el = $('pemo-form-error');
    if (el) el.classList.remove('visible');
  }

  /* ── Affichage d'une question ───────────────────────────── */
  function showQuestion(idx, isBack) {
    if (idx >= QUESTIONS.length) { submitAnswers(); return; }

    var q     = QUESTIONS[idx];
    var famId = q.f;

    // Transition de famille (en avançant uniquement)
    if (!isBack && famId !== state.lastFamille && state.lastFamille !== 0 && TRANSITIONS[famId]) {
      state.qIndex = idx;
      showTransitionFamille(famId, idx);
      return;
    }

    state.qIndex      = idx;
    state.lastFamille = famId;

    // Compteur
    var counter = $('pemo-q-counter');
    if (counter) counter.textContent = (idx + 1) + ' / ' + QUESTIONS.length;

    // Bouton retour
    var btnBack = $('pemo-btn-back');
    if (btnBack) btnBack.style.visibility = idx === 0 ? 'hidden' : 'visible';

    // Barre de progression
    var pct  = Math.round((idx / QUESTIONS.length) * 100);
    var fill = $('pemo-progress-fill');
    if (fill) fill.style.width = pct + '%';

    var famLabel = $('pemo-progress-famille');
    if (famLabel && DATA.familles && DATA.familles[famId]) {
      var fam = DATA.familles[famId];
      famLabel.textContent = (fam.emoji || '') + ' ' + (fam.label || '');
    }
    var pctEl = $('pemo-progress-pct');
    if (pctEl) pctEl.textContent = pct + '%';

    // Question
    var qText = $('pemo-q-text');
    if (qText) qText.textContent = q.text;

    // Choix
    var choicesEl = $('pemo-choices');
    if (!choicesEl) return;
    choicesEl.innerHTML = '';
    var saved = state.answers[idx];

    LIKERT.forEach(function (opt) {
      var btn     = document.createElement('button');
      btn.type    = 'button';
      btn.className = 'pemo-choice-btn' + (saved === opt.val ? ' selected' : '');
      btn.textContent = opt.label;
      (function (capturedIdx, capturedVal) {
        btn.addEventListener('click', function (ev) {
          onAnswer(capturedIdx, capturedVal, ev.currentTarget);
        });
      })(idx, opt.val);
      choicesEl.appendChild(btn);
    });

    // Animation carte
    var card = $('pemo-solo-card');
    if (card) {
      card.classList.remove('pemo-in','pemo-in-back','pemo-out','pemo-out-back');
      void card.offsetWidth;
      card.classList.add(isBack ? 'pemo-in-back' : 'pemo-in');
    }
  }

  /* ── Réponse ────────────────────────────────────────────── */
  function onAnswer(qIdx, value, clickedBtn) {
    state.answers[qIdx] = value;

    // Feedback visuel
    document.querySelectorAll('.pemo-choice-btn').forEach(function (b) {
      b.classList.remove('selected');
    });
    if (clickedBtn) clickedBtn.classList.add('selected');

    // Slide-out puis question suivante
    setTimeout(function () {
      var card = $('pemo-solo-card');
      if (card) {
        card.classList.add('pemo-out');
        setTimeout(function () {
          card.classList.remove('pemo-out');
          showQuestion(qIdx + 1, false);
        }, 200);
      } else {
        showQuestion(qIdx + 1, false);
      }
    }, 280);
  }

  /* ── Navigation retour ──────────────────────────────────── */
  function handleBack() {
    if (state.qIndex <= 0) return;
    var card = $('pemo-solo-card');
    if (card) {
      card.classList.add('pemo-out-back');
      setTimeout(function () {
        card.classList.remove('pemo-out-back');
        goBack();
      }, 200);
    } else {
      goBack();
    }
  }

  function goBack() {
    var prevIdx = state.qIndex - 1;
    state.qIndex      = prevIdx;
    state.lastFamille = prevIdx > 0 ? QUESTIONS[prevIdx - 1].f : 0;
    showQuestion(prevIdx, true);
  }

  /* ── Transitions de famille ─────────────────────────────── */
  function showTransitionFamille(famId, nextIdx) {
    var trans = TRANSITIONS[famId];
    if (!trans) { showQuestion(nextIdx, false); return; }

    var parts = (trans.label || '').split(' ');
    var emoji = parts[0] || '✨';
    var title = parts.slice(1).join(' ');

    var eEl = $('pemo-trans-emoji'); if (eEl) eEl.textContent = emoji;
    var tEl = $('pemo-trans-title'); if (tEl) tEl.textContent = title;
    var pEl = $('pemo-trans-text');  if (pEl) pEl.textContent  = trans.text;

    state.lastFamille = famId;
    showScreen('transition');

    var btnContinue = $('pemo-trans-continue');
    if (btnContinue) {
      // Remplacer l'événement à chaque fois
      var newBtn = btnContinue.cloneNode(true);
      btnContinue.parentNode.replaceChild(newBtn, btnContinue);
      newBtn.addEventListener('click', function () {
        showScreen('question');
        showQuestion(nextIdx, false);
      });
    }
  }

  /* ── Soumission ─────────────────────────────────────────── */
  function submitAnswers() {
    if (state.submitting) return;
    state.submitting = true;
    showScreen('loading');

    var answersObj = {};
    for (var i = 0; i < QUESTIONS.length; i++) {
      answersObj[i] = state.answers[i] || 1; // inclut les 6 questions de désirabilité (idx 80-85)
    }

    ajaxPost('pemo_submit', { token: state.token, answers: answersObj }, function (data) {
      state.submitting = false;
      renderResults(data);
      showScreen('results');
    }, function (msg) {
      state.submitting = false;
      alert('Une erreur est survenue lors de l\'envoi. Veuillez réessayer.\n' + (msg || ''));
      showScreen('question');
      showQuestion(state.qIndex, false);
    });
  }

  /* ── Rendu des résultats ────────────────────────────────── */
  function renderResults(data) {
    var container = $('pemo-results-container');
    if (!container) return;

    var html = '';

    // Warning désirabilité sociale (si biais détecté)
    if (data.desirabilite_alerte && data.desirabilite_msg) {
      html += '<div class="pemo-desirabilite-alert">'
            + '<span class="pemo-desir-icon">🔍</span>'
            + '<div>'
            + '<p class="pemo-desir-title">Note sur vos réponses</p>'
            + '<p class="pemo-desir-text">' + esc(data.desirabilite_msg) + '</p>'
            + '</div>'
            + '</div>';
    }

    // Hero QE
    html += '<div class="pemo-results-hero">';
    html += '<p class="pemo-qe-label">Votre Quotient Émotionnel</p>';
    html += '<div class="pemo-qe-score">' + esc(data.score_global) + '<small> / ' + esc(data.score_max || 320) + '</small></div>';
    html += '<div class="pemo-qe-niveau">✦ ' + esc(data.niveau_qe) + '</div>';
    html += '<p class="pemo-qe-phrase">' + esc(data.phrase_qe) + '</p>';
    html += '</div>';

    // Dimensions groupées par famille
    var familleMap = {};
    (data.dims || []).forEach(function (d) {
      var fid = String(d.famille_id);
      if (!familleMap[fid]) familleMap[fid] = { label: d.emoji + ' ' + d.famille, dims: [] };
      familleMap[fid].dims.push(d);
    });

    var famDesc = {
      '1': 'Comprendre ses propres émotions, forces et limites.',
      '2': 'Gérer ses réactions, son élan et son équilibre intérieur.',
      '3': 'Qualité de la présence aux autres et des échanges.',
      '4': 'Capacité à influencer positivement et à résoudre les tensions.',
    };

    html += '<div class="pemo-result-section"><p class="pemo-section-title">📊 Vos 16 dimensions</p>';
    Object.keys(familleMap).forEach(function (fid) {
      var fam   = familleMap[fid];
      var color = famColor(parseInt(fid, 10));
      var desc  = famDesc[fid] || '';
      var parts = fam.label.split(' ');
      var emoji = parts[0];
      var name  = parts.slice(1).join(' ');

      html += '<div class="pemo-famille-block" style="--fam-color:' + color + ';">';

      // En-tête domaine
      html += '<div class="pemo-famille-header">'
            + '<span class="pemo-famille-emoji">' + emoji + '</span>'
            + '<div class="pemo-famille-meta">'
            + '<span class="pemo-famille-name">' + esc(name) + '</span>'
            + (desc ? '<span class="pemo-famille-desc">' + esc(desc) + '</span>' : '')
            + '</div>'
            + '</div>';

      // Dimensions
      html += '<div class="pemo-famille-dims">';
      fam.dims.forEach(function (d) {
        html += '<div class="pemo-dim-row">'
          + '<div class="pemo-dim-header">'
          + '<span class="pemo-dim-label">' + esc(d.label) + '</span>'
          + '<span class="pemo-dim-score" style="color:' + color + ';">' + esc(d.score) + '/20'
          + ' <span style="font-size:11px;font-weight:500;color:#94A3B8;margin-left:3px;">— ' + esc(d.interpretation) + '</span></span>'
          + '</div>'
          + '<div class="pemo-dim-track" style="margin-bottom:6px;">'
          + '<div class="pemo-dim-fill pemo-fill-f' + d.famille_id + '" data-pct="' + d.pct + '" style="width:0%;"></div>'
          + '</div>'
          + (d.description ? '<p style="font-size:13px;color:#475569;line-height:1.6;margin:0 0 14px;padding-left:2px;">' + esc(d.description) + '</p>' : '')
          + '</div>';
      });
      html += '</div>'; // .pemo-famille-dims
      html += '</div>'; // .pemo-famille-block
    });
    html += '</div>';

    // Top forces / développement
    html += '<div class="pemo-result-section"><div class="pemo-highlights">';
    html += '<div class="pemo-highlight-card forces"><h4>🏆 Points forts</h4><ul>';
    (data.top_forces || []).forEach(function (l) { html += '<li>' + esc(l) + '</li>'; });
    html += '</ul></div>';
    html += '<div class="pemo-highlight-card dev"><h4>🎯 Axes de progression</h4><ul>';
    if ((data.top_dev || []).length > 0) {
      (data.top_dev || []).forEach(function (l) { html += '<li>' + esc(l) + '</li>'; });
    } else {
      html += '<li style="color:#15803D;font-style:italic;">Toutes vos dimensions sont bien développées — bravo !</li>';
    }
    html += '</ul></div></div></div>';

    // ── Historique (évolution) ───────────────────────────────
    if (data.history && data.history.dims) {
      var h = data.history;
      var diffColor = h.global_diff > 0 ? '#15803D' : (h.global_diff < 0 ? '#B91C1C' : '#64748B');
      var diffSign  = h.global_diff > 0 ? '+' : '';
      html += '<div class="pemo-result-section">';
      html += '<p class="pemo-section-title">📈 Votre évolution depuis le ' + esc(h.date_prev) + '</p>';
      html += '<div class="pemo-history-hero">';
      html += '<span class="pemo-hist-prev">' + esc(h.global_prev) + '/320</span>';
      html += '<span class="pemo-hist-arrow">→</span>';
      html += '<span class="pemo-hist-curr">' + esc(data.score_global) + '/320</span>';
      html += '<span class="pemo-hist-diff" style="color:' + diffColor + ';">' + h.global_arrow + ' ' + diffSign + esc(h.global_diff) + ' pts</span>';
      html += '</div>';

      // Tableau mini des évolutions par dimension
      html += '<div class="pemo-hist-grid">';
      Object.keys(h.dims).forEach(function (dimId) {
        var d = h.dims[dimId];
        var dc = d.diff > 0 ? '#15803D' : (d.diff < 0 ? '#B91C1C' : '#94A3B8');
        var ds = d.diff > 0 ? '+' : '';
        html += '<div class="pemo-hist-row">'
          + '<span class="pemo-hist-lbl">' + esc(d.label) + '</span>'
          + '<span class="pemo-hist-val">' + esc(d.prev) + ' → ' + esc(d.curr) + '</span>'
          + '<span class="pemo-hist-chg" style="color:' + dc + ';">' + d.arrow + ' ' + ds + d.diff + '</span>'
          + '</div>';
      });
      html += '</div>';
      html += '</div>';
    }

    // ── Recommandations par dimension ────────────────────────
    if ((data.dims || []).some(function(d){ return d.reco_actions && d.reco_actions.length; })) {
      html += '<div class="pemo-result-section">';
      html += '<p class="pemo-section-title">💡 Recommandations personnalisées</p>';

      var recoByFam = {};
      (data.dims || []).forEach(function(d) {
        var fid = String(d.famille_id);
        if (!recoByFam[fid]) recoByFam[fid] = { label: d.emoji + ' ' + d.famille, dims: [] };
        recoByFam[fid].dims.push(d);
      });

      Object.keys(recoByFam).forEach(function(fid) {
        var fam = recoByFam[fid];
        html += '<p style="font-size:11px;font-weight:700;color:#8FA8BE;text-transform:uppercase;letter-spacing:.08em;margin:20px 0 8px;">' + esc(fam.label) + '</p>';
        fam.dims.forEach(function(d) {
          if (!d.reco_actions || !d.reco_actions.length) return;
          html += '<div class="pemo-reco-card">';
          html += '<div class="pemo-reco-dim">'
                + '<span class="pemo-reco-dim-name">' + esc(d.label) + '</span>'
                + '<span class="pemo-reco-dim-badge">' + esc(d.reco_niveau) + '</span>'
                + '</div>';
          html += '<ul class="pemo-reco-actions">';
          d.reco_actions.forEach(function(a, i) {
            html += '<li><span class="reco-num">' + (i + 1) + '</span>' + esc(a) + '</li>';
          });
          html += '</ul></div>';
        });
      });
      html += '</div>';
    }

    // CTA
    var rdvUrl = data.rdv_url || '#';
    html += '<p class="pemo-results-note">💡 Ce profil est un <strong>miroir d\'exploration</strong>, pas un diagnostic clinique. Les scores reflètent vos réponses à un instant T — ils sont un point de départ pour la réflexion, pas une vérité définitive.</p>';
    html += '<div class="pemo-cta-block">';
    html += '<p class="pemo-cta-eyebrow">Et maintenant ?</p>';
    html += '<p class="pemo-cta-title">Transformez ce profil en levier de développement concret</p>';
    html += '<p class="pemo-cta-text">Un entretien de débriefing avec Alexandre vous permettra de comprendre ce que révèlent réellement vos scores — et comment les transformer en changements durables.</p>';
    html += '<a href="' + escAttr(rdvUrl) + '" style="display:block;text-align:center;background:#E8541A;color:#fff;padding:14px;border-radius:999px;text-decoration:none;font-size:14px;font-weight:700;box-shadow:0 4px 16px rgba(232,84,26,.3);margin-bottom:12px;" target="_blank" rel="noopener">✦ Réserver mon entretien gratuit</a>';
    if (data.pdf_url) {
      html += '<a href="' + escAttr(data.pdf_url) + '" target="_blank" rel="noopener" class="pemo-pdf-btn"'
            + ' style="display:block;text-align:center;color:#ffffff !important;background:rgba(255,255,255,.10);'
            + 'border:1.5px solid rgba(255,255,255,.30);padding:12px 20px;border-radius:999px;'
            + 'text-decoration:none;font-size:13px;font-weight:700;margin-bottom:14px;">'
            + '📄 Télécharger mon rapport PDF</a>';
    }
    if (data.email_sent !== false) {
      html += '<p class="pemo-email-confirm">📩 Rapport envoyé à ' + esc(state.email) + ' — pensez à vérifier vos spams.</p>';
    } else {
      html += '<p class="pemo-email-confirm" style="color:#FCA5A5;">Configurez votre SMTP dans Praxis IE → Réglages pour activer les emails.</p>';
    }
    html += '</div>';

    container.innerHTML = html;

    // Animer les barres
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        container.querySelectorAll('.pemo-dim-fill[data-pct]').forEach(function (el) {
          el.style.width = (el.getAttribute('data-pct') || '0') + '%';
        });
      });
    });
  }

  /* ── Utilitaires ─────────────────────────────────────────── */
  function famColor(fid) {
    var colors = { '1': '#E8541A', '2': '#F59E0B', '3': '#3B82F6', '4': '#16A34A' };
    return colors[String(fid)] || '#E8541A';
  }

  function esc(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str != null ? String(str) : ''));
    return d.innerHTML;
  }

  function escAttr(str) {
    return String(str || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function isEmail(e) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }

  function ajaxPost(action, params, onSuccess, onError) {
    var fd = new FormData();
    fd.append('action', action);
    fd.append('nonce', DATA.nonce || '');
    Object.keys(params).forEach(function (key) {
      var val = params[key];
      if (typeof val === 'object' && val !== null) {
        Object.keys(val).forEach(function (k) { fd.append(key + '[' + k + ']', val[k]); });
      } else {
        fd.append(key, val);
      }
    });
    var xhr = new XMLHttpRequest();
    xhr.open('POST', DATA.ajax_url || '/wp-admin/admin-ajax.php', true);
    xhr.onload = function () {
      try {
        var r = JSON.parse(xhr.responseText);
        r.success ? onSuccess(r.data) : onError(r.data && r.data.message ? r.data.message : 'Erreur serveur.');
      } catch (e) { onError('Réponse invalide du serveur.'); }
    };
    xhr.onerror = function () { onError('Erreur réseau — vérifiez votre connexion.'); };
    xhr.send(fd);
  }

  /* ── Démarrage ───────────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
