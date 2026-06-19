/* Praxis 360 — Application de passation (JavaScript vanilla). */
(function () {
    'use strict';

    if (typeof PRAXIS360 === 'undefined') { return; }

    var P = PRAXIS360;
    var questions = P.questions || [];
    var openQs = P.openQs || [];
    var scale = P.scale || {};
    var total = questions.length;

    var answers = {};   // item_key -> value (1..5 ou 'na')
    var openAns = {};   // question_key -> texte
    var idx = 0;        // index courant dans les questions fermées
    var openIdx = -1;   // -1 = pas encore dans les ouvertes
    var locked = false; // évite double-clic pendant l'auto-avancement

    var intro = document.getElementById('p360-intro');
    var screen = document.getElementById('p360-screen');
    var area = document.getElementById('p360-question-area');
    var backBtn = document.getElementById('p360-back');
    var fill = document.getElementById('p360-fill');
    var sectionEl = document.getElementById('p360-section');
    var counterEl = document.getElementById('p360-counter');
    var startBtn = document.getElementById('p360-start');

    if (startBtn) {
        startBtn.addEventListener('click', function () {
            if (intro) { intro.parentNode.style.display = 'none'; }
            if (screen) { screen.style.display = 'block'; }
            renderClosed();
        });
    }

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            if (locked) { return; }
            if (openIdx >= 0) {
                if (openIdx === 0) { openIdx = -1; idx = total - 1; renderClosed(); }
                else { openIdx--; renderOpen(); }
            } else if (idx > 0) {
                idx--; renderClosed();
            }
        });
    }

    function setProgress(section, current, max) {
        sectionEl.textContent = section;
        counterEl.textContent = current + ' / ' + max;
        var pct = max > 0 ? Math.round((current / max) * 100) : 0;
        fill.style.width = pct + '%';
    }

    function renderClosed() {
        locked = false;
        var q = questions[idx];
        setProgress(q.dimension, idx + 1, total);
        backBtn.disabled = (idx === 0);

        var html = '<div class="p360-question">';
        html += '<div class="p360-question-text">' + escapeHtml(q.text) + '</div>';
        html += '<div class="p360-options">';
        var keys = Object.keys(scale);
        for (var i = 0; i < keys.length; i++) {
            var val = keys[i];
            var sel = (answers[q.key] === val) ? ' is-selected' : '';
            html += '<button type="button" class="p360-opt' + sel + '" data-val="' + escapeAttr(val) + '">' + escapeHtml(scale[val]) + '</button>';
        }
        html += '</div>';
        var naSel = (answers[q.key] === 'na') ? ' style="font-weight:700"' : '';
        html += '<button type="button" class="p360-na"' + naSel + ' data-val="na">' + escapeHtml(P.strings.na) + '</button>';
        html += '</div>';
        area.innerHTML = html;

        var opts = area.querySelectorAll('.p360-opt, .p360-na');
        for (var j = 0; j < opts.length; j++) {
            opts[j].addEventListener('click', onAnswer);
        }
    }

    function onAnswer(e) {
        if (locked) { return; }
        var btn = e.currentTarget;
        var val = btn.getAttribute('data-val');
        var q = questions[idx];
        answers[q.key] = val;

        // Feedback visuel.
        var all = area.querySelectorAll('.p360-opt');
        for (var i = 0; i < all.length; i++) { all[i].classList.remove('is-selected'); }
        if (btn.classList.contains('p360-opt')) { btn.classList.add('is-selected'); }

        locked = true;
        saveAnswer(q.key, val);

        setTimeout(function () {
            locked = false;
            if (idx < total - 1) { idx++; renderClosed(); }
            else { openIdx = 0; renderOpen(); }
        }, P.autoAdvance || 280);
    }

    function renderOpen() {
        locked = false;
        if (!openQs.length) { submitAll(); return; }
        var q = openQs[openIdx];
        setProgress('Questions ouvertes', openIdx + 1, openQs.length);
        backBtn.disabled = false;

        var existing = openAns[q.key] || '';
        var html = '<div class="p360-question">';
        html += '<div class="p360-question-text">' + escapeHtml(q.text) + '</div>';
        html += '<textarea class="p360-input p360-textarea" id="p360-open">' + escapeHtml(existing) + '</textarea>';
        html += '<p style="margin-top:18px;">';
        html += '<button type="button" class="p360-btn" id="p360-open-continue">' + escapeHtml(P.strings.continue) + '</button> ';
        html += '<button type="button" class="p360-btn-ghost" id="p360-open-pass">' + escapeHtml(P.strings.pass) + '</button>';
        html += '</p></div>';
        area.innerHTML = html;

        document.getElementById('p360-open-continue').addEventListener('click', function () {
            var txt = document.getElementById('p360-open').value || '';
            openAns[q.key] = txt;
            saveOpen(q.key, txt);
            nextOpen();
        });
        document.getElementById('p360-open-pass').addEventListener('click', function () {
            openAns[q.key] = '';
            nextOpen();
        });
    }

    function nextOpen() {
        if (openIdx < openQs.length - 1) { openIdx++; renderOpen(); }
        else { submitAll(); }
    }

    function submitAll() {
        setProgress('Terminé', total, total);
        backBtn.disabled = true;
        area.innerHTML = '<div class="p360-question" style="text-align:center;">'
            + '<div class="p360-question-text">' + escapeHtml(P.strings.thanks) + ' 🙏</div>'
            + '<p>Vos réponses ont bien été enregistrées. Vous pouvez fermer cette page.</p>'
            + '<p id="p360-submit-state" style="color:var(--praxis-text-soft);font-size:14px;">Enregistrement…</p>'
            + '</div>';
        post('p360_submit', {}, function () {
            var s = document.getElementById('p360-submit-state');
            if (s) { s.textContent = 'Merci, c\'est terminé.'; }
        });
    }

    // --- AJAX ---------------------------------------------------------------
    function saveAnswer(key, val) {
        post('p360_save_answer', { item_key: key, value: val }, null);
    }
    function saveOpen(key, txt) {
        post('p360_save_open', { question_key: key, text: txt }, null);
    }

    function post(action, data, cb) {
        var body = 'action=' + encodeURIComponent(action)
            + '&nonce=' + encodeURIComponent(P.nonce)
            + '&token=' + encodeURIComponent(P.token);
        for (var k in data) {
            if (data.hasOwnProperty(k)) {
                body += '&' + encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
            }
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', P.ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && cb) { cb(xhr.status === 200); }
        };
        xhr.send(body);
    }

    // --- Utils --------------------------------------------------------------
    function escapeHtml(s) {
        s = '' + s;
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    function escapeAttr(s) { return escapeHtml(s); }
})();
