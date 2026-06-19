/**
 * PraxiMet – Quiz RIASEC interactif
 * JavaScript Vanilla – aucune dépendance jQuery
 * v2.1.1 — persistance localStorage (sauvegarde automatique)
 */

(function () {
    'use strict';

    var STORAGE_KEY = 'praximet_quiz_v1';

    // ── État global du quiz ───────────────────────────────────────────
    var state = {
        questions:    [],
        currentIndex: 0,
        reponses:     {},
        total:        0,
    };

    // ── Sélecteurs DOM ───────────────────────────────────────────────
    var el = {
        quiz:          document.getElementById('praximet-quiz'),
        questionTexte: document.getElementById('praximet-question-texte'),
        questionNum:   document.getElementById('praximet-question-num'),
        progressBar:   document.getElementById('praximet-progress-bar'),
        progressLabel: document.getElementById('praximet-progress-label'),
        btnOui:        document.getElementById('praximet-btn-oui'),
        btnNon:        document.getElementById('praximet-btn-non'),
        btnPrev:       document.getElementById('praximet-btn-prev'),
        etapeQuiz:     document.getElementById('praximet-etape-quiz'),
        etapeForm:     document.getElementById('praximet-etape-form'),
        etapeResultat: document.getElementById('praximet-etape-resultat'),
        inputReponses: document.getElementById('praximet-input-reponses'),
    };

    // ── Persistance localStorage ──────────────────────────────────────

    function sauvegarder() {
        try {
            var data = {
                reponses:     state.reponses,
                currentIndex: state.currentIndex,
                // Sauvegarder aussi l'ordre des questions (shufflé côté PHP)
                questionsIds: state.questions.map(function(q){ return q.id; }),
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
        } catch(e) {}
    }

    function chargerSauvegarde() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return null;
            var data = JSON.parse(raw);
            // Vérifier que la sauvegarde correspond aux mêmes questions
            if (!data.questionsIds || !data.reponses) return null;
            return data;
        } catch(e) {
            return null;
        }
    }

    function effacerSauvegarde() {
        try { localStorage.removeItem(STORAGE_KEY); } catch(e) {}
    }

    // ── Initialisation ───────────────────────────────────────────────
    function init() {
        if (!el.quiz) return;

        var data = el.quiz.dataset.questions;
        if (!data) return;

        try {
            state.questions = JSON.parse(data);
            state.total     = state.questions.length;
        } catch(e) {
            return;
        }

        // Tenter de restaurer une sauvegarde
        var sauvegarde = chargerSauvegarde();
        var indexDepart = 0;

        if (sauvegarde && sauvegarde.questionsIds) {
            // Vérifier que l'ordre correspond (même session)
            var idsActuels = state.questions.map(function(q){ return q.id; }).join(',');
            var idsSauv    = sauvegarde.questionsIds.join(',');

            if (idsActuels === idsSauv && Object.keys(sauvegarde.reponses).length > 0) {
                // Restaurer les réponses et la position
                state.reponses     = sauvegarde.reponses;
                indexDepart        = Math.min(sauvegarde.currentIndex, state.total - 1);
                afficherBandeauReprise(indexDepart);
            }
        }

        afficherQuestion(indexDepart);
        bindEvents();
    }

    // ── Bandeau de reprise ────────────────────────────────────────────
    function afficherBandeauReprise(index) {
        var nb = Object.keys(state.reponses).length;
        if (nb === 0) return;

        var bandeau = document.createElement('div');
        bandeau.className = 'praximet-reprise-bandeau';
        bandeau.innerHTML =
            '<span class="praximet-reprise-texte">&#128197; Quiz repris — ' + nb + ' réponses restaurées.</span>' +
            '<button class="praximet-reprise-reset" type="button">Recommencer</button>';

        el.etapeQuiz.insertBefore(bandeau, el.etapeQuiz.firstChild);

        bandeau.querySelector('.praximet-reprise-reset').addEventListener('click', function() {
            effacerSauvegarde();
            state.reponses     = {};
            state.currentIndex = 0;
            bandeau.remove();
            afficherQuestion(0);
        });
    }

    // ── Affiche la question à l'index donné ──────────────────────────
    function afficherQuestion(index) {
        var q = state.questions[index];
        if (!q) return;

        state.currentIndex = index;

        el.questionTexte.textContent = q.texte;
        el.questionNum.textContent   = (index + 1) + ' / ' + state.total;

        var pct = Math.round((index / state.total) * 100);
        el.progressBar.style.width = pct + '%';
        el.progressBar.setAttribute('aria-valuenow', pct);
        el.progressLabel.textContent = pct + '% complété';

        el.btnPrev.style.display = index > 0 ? 'inline-flex' : 'none';

        var dejaRepondu = state.reponses[q.id];
        el.btnOui.classList.toggle('praximet-btn--actif', dejaRepondu === 1);
        el.btnNon.classList.toggle('praximet-btn--actif', dejaRepondu === 0);

        el.questionTexte.classList.remove('praximet-fade-in');
        void el.questionTexte.offsetWidth;
        el.questionTexte.classList.add('praximet-fade-in');

        // Sauvegarde à chaque changement de question
        sauvegarder();
    }

    // ── Enregistre la réponse et avance ──────────────────────────────
    function repondre(valeur) {
        var q = state.questions[state.currentIndex];
        if (!q) return;

        state.reponses[q.id] = valeur;
        sauvegarder();

        var suivant = state.currentIndex + 1;

        if (suivant < state.total) {
            afficherQuestion(suivant);
        } else {
            finaliserQuiz();
        }
    }

    // ── Finalise le quiz ──────────────────────────────────────────────
    function finaliserQuiz() {
        el.progressBar.style.width = '100%';
        el.progressBar.setAttribute('aria-valuenow', 100);
        el.progressLabel.textContent = '100% complété';

        var reponsesJson = JSON.stringify(state.reponses);
        el.inputReponses.value   = reponsesJson;
        window.praximet_reponses = reponsesJson;

        // Effacer la sauvegarde une fois le formulaire soumis
        // (la suppression réelle se fait après soumission AJAX réussie)
        el.etapeQuiz.classList.add('praximet-hidden');
        el.etapeForm.classList.remove('praximet-hidden');
        el.etapeForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ── Gestion des événements ────────────────────────────────────────
    function bindEvents() {
        el.btnOui.addEventListener('click', function() { repondre(1); });
        el.btnNon.addEventListener('click', function() { repondre(0); });
        el.btnPrev.addEventListener('click', function() {
            if (state.currentIndex > 0) afficherQuestion(state.currentIndex - 1);
        });

        // Effacer la sauvegarde après soumission réussie
        var form = document.getElementById('praximet-form');
        if (form) {
            form.addEventListener('praximet:succes', function() {
                effacerSauvegarde();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
