(function($) {
    'use strict';

    var currentStep = 0;
    var totalSteps  = parseInt($('.pp-step').last().data('step')) + 1;
    var allAnswers  = {};

    // -----------------------------------------------
    // Mise à jour de la barre de progression
    // -----------------------------------------------
    function updateProgress(step) {
        var pct = Math.round((step / totalSteps) * 100);
        $('#pp-progress-fill').css('width', pct + '%');
        $('#pp-current-step').text(step + 1);
    }

    // -----------------------------------------------
    // Afficher une étape
    // -----------------------------------------------
    function showStep(step) {
        $('.pp-step').hide();
        $('[data-step="' + step + '"]').show();
        currentStep = step;
        updateProgress(step);
        $('html, body').animate({ scrollTop: $('#pp-wrapper').offset().top - 60 }, 300);
    }

    // -----------------------------------------------
    // Valider une étape : toutes les questions répondues
    // -----------------------------------------------
    function validateStep(step) {
        var $step = $('[data-step="' + step + '"]');
        var valid = true;

        $step.find('.pp-question').each(function() {
            var $q = $(this);
            var qid = $q.attr('id').replace('pp-q-', '');
            var answered = $q.find('input[type="radio"]:checked').length > 0;

            if (!answered) {
                $q.addClass('pp-unanswered');
                valid = false;
            } else {
                $q.removeClass('pp-unanswered');
                allAnswers[qid] = $q.find('input[type="radio"]:checked').val();
            }
        });

        if (!valid) {
            $('html, body').animate({
                scrollTop: $step.find('.pp-unanswered').first().offset().top - 80
            }, 300);
        }

        return valid;
    }

    // -----------------------------------------------
    // Collect answers in real time
    // -----------------------------------------------
    $(document).on('change', '.pp-question input[type="radio"]', function() {
        var $q = $(this).closest('.pp-question');
        $q.removeClass('pp-unanswered');
        var qid = $q.attr('id').replace('pp-q-', '');
        allAnswers[qid] = $(this).val();
    });

    // -----------------------------------------------
    // Bouton Suivant
    // -----------------------------------------------
    $(document).on('click', '.pp-next', function() {
        var step = parseInt($(this).data('step'));

        if (!validateStep(step)) return;

        showStep(step + 1);
    });

    // -----------------------------------------------
    // Bouton Précédent
    // -----------------------------------------------
    $(document).on('click', '.pp-prev', function() {
        var step = parseInt($(this).data('step'));
        showStep(step - 1);
    });

    // -----------------------------------------------
    // Soumission finale
    // -----------------------------------------------
    $('#pp-submit').on('click', function() {
        var prenom       = $.trim($('#pp-prenom').val());
        var email        = $.trim($('#pp-email').val());
        var consentement = $('#pp-consentement').is(':checked') ? 1 : 0;

        // Validation côté client
        if (!prenom) {
            showError('Veuillez renseigner votre prénom.');
            return;
        }
        if (!isValidEmail(email)) {
            showError('Veuillez renseigner une adresse e-mail valide.');
            return;
        }
        if (!consentement) {
            showError('Vous devez accepter la politique de confidentialité pour continuer.');
            return;
        }

        hideError();

        // Construire les données
        var formData = {
            action:       'pp_submit',
            nonce:        PP_AJAX.nonce,
            prenom:       prenom,
            email:        email,
            consentement: consentement,
            source:       window.location.href,
            reponses:     allAnswers,
        };

        // Afficher le loader
        $('#pp-form-container').hide();
        $('#pp-loader').show();

        $.post(PP_AJAX.url, formData, function(response) {
            $('#pp-loader').hide();

            if (response.success) {
                displayResults(response.data);
            } else {
                $('#pp-form-container').show();
                showError(response.data.message || 'Une erreur est survenue. Veuillez réessayer.');
            }
        }).fail(function() {
            $('#pp-loader').hide();
            $('#pp-form-container').show();
            showError('Erreur de connexion. Vérifiez votre connexion internet et réessayez.');
        });
    });

    // -----------------------------------------------
    // Affichage des résultats
    // -----------------------------------------------
    function displayResults(data) {
        $('#pp-results-title').text('Bravo ' + data.prenom + ' ! Voici votre profil de personnalité');
        $('#pp-rdv-link').attr('href', data.rdv_url);

        var $container = $('#pp-scores-container');
        $container.empty();

        var colors = {
            score_O:  '#7c3aed',
            score_C:  '#2563eb',
            score_E:  '#E8541A',
            score_A:  '#10b981',
            score_N:  '#ef4444',
            score_DS: '#9ca3af',
        };

        $.each(data.profil, function(key, p) {
            if (key === 'score_DS') return; // Ne pas afficher DS à l'utilisateur
            var color = colors[key] || '#E8541A';
            var card = $('<div class="pp-score-card">');
            card.html(
                '<div class="pp-score-header">' +
                    '<span class="pp-score-label">' + escHtml(p.label) + '</span>' +
                    '<span class="pp-score-pct" style="color:' + color + ';">' + p.score + '%</span>' +
                '</div>' +
                '<div class="pp-bar-bg">' +
                    '<div class="pp-bar-fill" style="background:' + color + ';width:0%;"></div>' +
                '</div>' +
                '<p class="pp-score-texte">' + escHtml(p.texte) + '</p>'
            );
            $container.append(card);
        });

        $('#pp-results').show();

        // Animer les barres
        setTimeout(function() {
            $.each(data.profil, function(key, p) {
                if (key === 'score_DS') return;
                var $card = $container.find('.pp-score-card').filter(function() {
                    return $(this).find('.pp-score-label').text() === p.label;
                });
                $card.find('.pp-bar-fill').css('width', p.score + '%');
            });
        }, 100);

        $('html, body').animate({ scrollTop: $('#pp-results').offset().top - 60 }, 500);
    }

    // -----------------------------------------------
    // Helpers
    // -----------------------------------------------
    function showError(msg) {
        $('#pp-error').text(msg).show();
        $('html, body').animate({ scrollTop: $('#pp-error').offset().top - 80 }, 300);
    }
    function hideError() { $('#pp-error').hide(); }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function escHtml(str) {
        return $('<div>').text(str).html();
    }

    // Init
    updateProgress(0);

})(jQuery);

/* ══════════════════════════════════════════════
   PARTAGE CARTE — LinkedIn / Instagram / Download
   Utilise html2canvas (chargé conditionnellement)
   ══════════════════════════════════════════════ */

// html2canvas est chargé via wp_enqueue_script — disponible globalement
function ppLoadHtml2Canvas(cb) {
  // html2canvas est une dépendance WP déclarée — toujours disponible
  if (typeof html2canvas === 'function') { cb(); return; }
  // Fallback sécurisé si WP a échoué à charger (hébergeur avec CSP strict)
  console.warn('html2canvas non disponible — téléchargement direct impossible.');
  ppToast('⚠️ Capture indisponible. Téléchargez via le bouton ci-dessous.');
}

// Capture la carte en canvas → blob PNG
function ppCaptureCarte(w, h, cb) {
  var el = document.getElementById('pp-carte-result');
  if (!el) return;
  ppLoadHtml2Canvas(function() {
    var scale = w / el.offsetWidth;
    html2canvas(el, {
      scale: scale,
      useCORS: true,
      backgroundColor: null,
      logging: false,
    }).then(function(canvas) {
      canvas.toBlob(function(blob) { cb(blob, canvas); }, 'image/png');
    });
  });
}

// Toast helper
function ppToast(msg) {
  var t = document.getElementById('pp-toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'pp-toast'; t.className = 'pp-toast';
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(function(){ t.classList.remove('show'); }, 2800);
}

// ── LinkedIn : partage du lien profil public (aperçu OG automatique) ──
function ppCopierLienCarte() {
  var profilUrl = (window.ppProfileData && window.ppProfileData.profil_url)
    ? window.ppProfileData.profil_url
    : window.location.href.split('?')[0].split('#')[0];

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(profilUrl).then(function() {
      ppToast('✅ Lien copié ! Collez-le sur LinkedIn, WhatsApp ou par email.');
    }).catch(function() {
      ppFallbackCopy(profilUrl);
    });
  } else {
    ppFallbackCopy(profilUrl);
  }
}

function ppFallbackCopy(text) {
  var el = document.createElement('textarea');
  el.value = text;
  el.style.position = 'fixed';
  el.style.opacity = '0';
  document.body.appendChild(el);
  el.select();
  try {
    document.execCommand('copy');
    ppToast('✅ Lien copié ! Collez-le sur LinkedIn, WhatsApp ou par email.');
  } catch(e) {
    ppToast('⚠️ Copiez ce lien : ' + text);
  }
  document.body.removeChild(el);
}


function ppDownloadCarte() {
  ppToast('⏳ Génération…');
  ppCaptureCarte(1200, 627, function(blob, canvas) {
    ppFallbackDownload(canvas, 'profil');
  });
}

function ppFallbackDownload(canvas, suffix) {
  var a = document.createElement('a');
  a.download = 'mon-profil-' + suffix + '.png';
  a.href = canvas.toDataURL('image/png');
  a.click();
  ppToast('✅ Image téléchargée !');
}

// ── Animation des barres au chargement des résultats ──
function ppAnimateBars() {
  var fills = document.querySelectorAll('.pp-bar-fill');
  fills.forEach(function(el) {
    var w = el.style.width;
    el.style.width = '0';
    setTimeout(function(){ el.style.width = w; }, 120);
  });
  // Donuts couleurs
  var donuts = document.querySelectorAll('.pp-dim-donut');
  donuts.forEach(function(el) {
    var pct = parseInt(el.dataset.pct || 50);
    var hue = Math.round(pct * 1.2); // 0–120 rouge→vert
    el.style.background = 'hsl(' + hue + ',70%,45%)';
  });
}

// Déclencher quand les résultats apparaissent
document.addEventListener('pp:results_shown', ppAnimateBars);
