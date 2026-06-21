(function () {
    'use strict';

    var D = window.PraxiSensData || {};
    var questions = D.questions || [];
    var options = D.options || [];
    var dimensions = D.dimensions || {};

    var answers = {};
    var index = 0;

    var el = {};
    function $(id) { return document.getElementById(id); }

    document.addEventListener('DOMContentLoaded', function () {
        el.app = $('praxisens-app');
        if (!el.app) { return; }
        el.progress = $('px-progress');
        el.bar = $('px-bar');
        el.counter = $('px-counter');
        el.section = $('px-section');
        el.intro = $('px-intro');
        el.questions = $('px-questions');
        el.collect = $('px-collect');
        el.results = $('px-results');
        el.error = $('px-error');

        $('px-start').addEventListener('click', startTest);
        $('px-submit').addEventListener('click', submit);
    });

    function show(node) {
        [el.intro, el.questions, el.collect, el.results].forEach(function (n) {
            if (n) { n.hidden = (n !== node); }
        });
    }

    function startTest() {
        index = 0;
        el.progress.hidden = false;
        show(el.questions);
        renderQuestion();
    }

    function renderQuestion() {
        var q = questions[index];
        if (!q) { return; }
        var dim = dimensions[q.dim] || {};

        var pct = Math.round((index / questions.length) * 100);
        el.bar.style.width = pct + '%';
        el.counter.textContent = (index + 1) + ' / ' + questions.length;
        el.section.textContent = dim.full || 'Hypersensibilité';

        var html = '<div class="px-qtext">' + escapeHtml(q.text) + '</div><div class="px-options">';
        options.forEach(function (opt) {
            var sel = (answers[q.id] === opt.value) ? ' is-selected' : '';
            html += '<button type="button" class="px-option' + sel + '" data-value="' + opt.value + '">'
                + '<span class="px-dot"></span><span>' + escapeHtml(opt.label) + '</span></button>';
        });
        html += '</div>';
        html += '<button type="button" class="px-back" id="px-back"' + (index === 0 ? ' hidden' : '') + '>&larr; Précédent</button>';
        el.questions.innerHTML = html;

        var btns = el.questions.querySelectorAll('.px-option');
        Array.prototype.forEach.call(btns, function (b) {
            b.addEventListener('click', function () {
                answers[q.id] = parseInt(b.getAttribute('data-value'), 10);
                Array.prototype.forEach.call(btns, function (x) { x.classList.remove('is-selected'); });
                b.classList.add('is-selected');
                setTimeout(next, 280);
            });
        });
        var back = $('px-back');
        if (back) { back.addEventListener('click', prev); }
    }

    function next() {
        if (index < questions.length - 1) {
            index++;
            renderQuestion();
        } else {
            el.bar.style.width = '100%';
            show(el.collect);
        }
    }

    function prev() {
        if (index > 0) {
            index--;
            renderQuestion();
        }
    }

    function submit() {
        var firstName = ($('px-firstname').value || '').trim();
        var email = ($('px-email').value || '').trim();
        el.error.hidden = true;

        if (!validEmail(email)) {
            el.error.textContent = 'Merci d\'indiquer une adresse e-mail valide.';
            el.error.hidden = false;
            return;
        }
        if (Object.keys(answers).length < questions.length) {
            el.error.textContent = 'Certaines questions sont sans réponse.';
            el.error.hidden = false;
            return;
        }

        var btn = $('px-submit');
        btn.disabled = true;
        btn.innerHTML = '<span class="px-spinner"></span>Calcul en cours…';

        var body = new URLSearchParams();
        body.append('action', 'praxisens_submit');
        body.append('nonce', D.nonce);
        body.append('first_name', firstName);
        body.append('email', email);
        body.append('answers', JSON.stringify(answers));

        fetch(D.ajaxUrl, { method: 'POST', body: body })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.success) {
                    renderResults(res.data.scores, firstName);
                } else {
                    fail(btn, (res && res.data && res.data.message) || 'Une erreur est survenue.');
                }
            })
            .catch(function () { fail(btn, 'Connexion impossible. Réessayez.'); });
    }

    function fail(btn, msg) {
        btn.disabled = false;
        btn.textContent = 'Voir mon résultat';
        el.error.textContent = msg;
        el.error.hidden = false;
    }

    function renderResults(s, firstName) {
        el.progress.hidden = true;
        var hi = firstName ? (escapeHtml(firstName) + ', ') : '';

        var html = '<h2>' + hi + 'voici votre profil</h2>';
        html += '<div class="px-score-hero">'
            + '<div class="px-score-num">' + s.global + '%</div>'
            + '<div class="px-score-label">' + escapeHtml(s.profile) + '</div></div>';
        html += '<p class="px-band">' + bandText(s.global) + '</p>';

        ['EOE', 'AES', 'LST'].forEach(function (k) {
            var dim = dimensions[k] || {};
            html += '<div class="px-dim">'
                + '<div class="px-dim-head"><span>' + escapeHtml(dim.label || k) + '</span><span>' + s[k] + '%</span></div>'
                + '<div class="px-dim-track"><div class="px-dim-fill" data-w="' + s[k] + '" style="background:' + (dim.color || '#7c3aed') + '"></div></div>'
                + '<div class="px-dim-desc">' + escapeHtml(dim.desc || '') + '</div></div>';
        });

        html += '<div class="px-cta"><p>Votre résultat vous a été envoyé par e-mail. '
            + 'Pour transformer cette sensibilité en force au quotidien, découvrez l\'accompagnement Praxis.</p></div>';

        el.results.innerHTML = html;
        show(el.results);

        setTimeout(function () {
            var fills = el.results.querySelectorAll('.px-dim-fill');
            Array.prototype.forEach.call(fills, function (f) {
                f.style.width = f.getAttribute('data-w') + '%';
            });
        }, 60);
    }

    // Paliers alignes sur le PHP : faible <40, moderee 40-59, elevee 60-77, haute >=78.
    function bandText(pct) {
        if (pct >= 78) {
            return 'Votre profil correspond à une <strong>haute sensibilité marquée</strong>. Vous traitez l\'information en profondeur et percevez finement votre environnement, une richesse qui demande de protéger vos temps de récupération.';
        }
        if (pct >= 60) {
            return 'Une <strong>sensibilité élevée</strong> ressort de vos réponses. Vous êtes nettement réceptif(ve) aux ambiances et aux subtilités, tout en gardant des moments où les stimulations ne vous débordent pas.';
        }
        if (pct >= 40) {
            return 'Votre profil indique une <strong>sensibilité modérée</strong>, équilibrée. Vous percevez les nuances de votre environnement sans en être facilement submergé(e), ni filtre systématique, ni saturation fréquente.';
        }
        return 'Votre profil indique une <strong>sensibilité plutôt faible</strong>. Vous filtrez naturellement les stimulations et restez à l\'aise dans des environnements intenses.';
    }

    function validEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
})();
