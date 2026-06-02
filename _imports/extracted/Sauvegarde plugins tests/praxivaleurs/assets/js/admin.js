/**
 * PraxiValeurs Admin v2.7 — admin.js
 * Correction : fonctions déclarées avant utilisation (pas de hoisting avec =)
 */
(function () {
    'use strict';

    if (typeof pvAdmin === 'undefined') {
        console.error('PraxiValeurs: pvAdmin non défini.');
        return;
    }

    const AJAX  = pvAdmin.ajax_url;
    const NONCE = pvAdmin.nonce;
    const DIMS  = pvAdmin.dimensions;
    const SETS  = pvAdmin.settings;

    let currentPage     = 1;
    let searchTimer     = null;
    let modalRadarChart = null;

    /* ════════════════════════════════════════════
       UTILITAIRES — déclarés en premier (hoistés)
    ════════════════════════════════════════════ */
    function ajax(action, data, onOk, onErr) {
        const fd = new FormData();
        fd.append('action', action);
        fd.append('nonce', NONCE);
        Object.keys(data).forEach(k => fd.append(k, data[k]));
        fetch(AJAX, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                console.log('PraxiValeurs [' + action + ']:', res);
                if (res.success) { onOk && onOk(res.data); }
                else { console.warn('AJAX erreur:', res.data); onErr && onErr(res.data); }
            })
            .catch(err => { console.error('Fetch erreur:', err); onErr && onErr(); });
    }

    function setText(id, v) {
        const el = document.getElementById(id);
        if (el) el.textContent = v;
    }

    function getVal(id) {
        const el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ════════════════════════════════════════════
       PARTICIPANTS
    ════════════════════════════════════════════ */
    function loadSessions(page) {
        currentPage = page;
        const searchInput = document.getElementById('pva-search');
        const search = searchInput ? searchInput.value.trim() : '';
        const tbody  = document.getElementById('pva-table-body');
        if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="pva-table-empty">Chargement…</td></tr>';

        ajax('pv_admin_get_sessions', { search: search, page: page }, function(data) {
            renderTable(data.sessions || []);
            renderPagination(data.pages || 1, data.page || 1);
            const c = document.getElementById('pva-result-count');
            if (c) c.textContent = (data.total || 0) + ' résultat' + (data.total > 1 ? 's' : '');
        }, function(errData) {
            const tbody = document.getElementById('pva-table-body');
            const msg = errData && errData.message ? errData.message : 'Erreur inconnue';
            if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="pva-table-empty" style="color:#DC2626">Erreur : ' + esc(msg) + '</td></tr>';
        });
    }

    function renderTable(sessions) {
        const tbody = document.getElementById('pva-table-body');
        if (!tbody) return;
        if (!sessions.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="pva-table-empty">Aucun participant pour le moment.</td></tr>';
            return;
        }

        tbody.innerHTML = sessions.map(function(s) {
            const chips = (s.top5_chips || []).slice(0, 3).map(function(v) {
                return '<span class="pva-chip">' + esc(v) + '</span>';
            }).join('');

            return '<tr>' +
                '<td style="color:#9CA3AF;font-size:12px;font-weight:600">#' + s.id + '</td>' +
                '<td><strong>' + esc(s.prenom) + '</strong></td>' +
                '<td style="color:#6B7280;font-size:13px">' + esc(s.email) + '</td>' +
                '<td>' + chips + '</td>' +
                '<td style="color:#9CA3AF;font-size:12px;white-space:nowrap">' + esc(s.created_at) + '</td>' +
                '<td>' +
                    '<button class="pva-act-btn" data-id="' + s.id + '" data-prenom="' + esc(s.prenom) + '" ' +
                        'data-scores=\'' + JSON.stringify(s.scores || {}) + '\' ' +
                        'data-keys=\'' + JSON.stringify(s.top5_keys || []) + '\'>' +
                        '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> Voir' +
                    '</button>' +
                    '<button class="pva-act-btn pva-act-btn--del" data-del="' + s.id + '">' +
                        '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg> Suppr.' +
                    '</button>' +
                '</td>' +
            '</tr>';
        }).join('');

        tbody.querySelectorAll('[data-id]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                try {
                    pvShowDetail(btn.dataset.id, btn.dataset.prenom, JSON.parse(btn.dataset.scores), JSON.parse(btn.dataset.keys));
                } catch(e) { console.error('Modale erreur:', e); }
            });
        });

        tbody.querySelectorAll('[data-del]').forEach(function(btn) {
            btn.addEventListener('click', function() { pvDeleteSession(btn.dataset.del); });
        });
    }

    function renderPagination(pages, current) {
        const el = document.getElementById('pva-pagination');
        if (!el) return;
        if (pages <= 1) { el.innerHTML = ''; return; }
        let html = '';
        if (current > 1) html += '<button class="pva-page-btn" data-p="' + (current-1) + '">← Préc.</button>';
        for (let i = 1; i <= pages; i++) {
            if (i === 1 || i === pages || Math.abs(i-current) <= 2)
                html += '<button class="pva-page-btn' + (i===current?' active':'') + '" data-p="' + i + '">' + i + '</button>';
            else if (Math.abs(i-current) === 3)
                html += '<span style="padding:7px 4px;color:#9CA3AF">…</span>';
        }
        if (current < pages) html += '<button class="pva-page-btn" data-p="' + (current+1) + '">Suiv. →</button>';
        el.innerHTML = html;
        el.querySelectorAll('[data-p]').forEach(function(btn) {
            btn.addEventListener('click', function() { loadSessions(parseInt(btn.dataset.p)); });
        });
    }

    /* ════════════════════════════════════════════
       STATS
    ════════════════════════════════════════════ */
    function loadStats() {
        ajax('pv_admin_get_stats', {}, function(data) {
            setText('kpi-total',       data.total);
            setText('kpi-month',       data.this_month);
            setText('kpi-week',        data.this_week);
            setText('pva-total-badge', data.total + ' participants');
            // Utiliser top_dim si disponible, sinon fallback sur freq
            var topKey = data.top_dim || Object.keys(data.freq || {})[0];
            setText('kpi-top-dim', topKey && DIMS[topKey] ? DIMS[topKey].icon + ' ' + DIMS[topKey].label : '—');
            buildEvolutionChart(data.evolution || []);
            buildFreqChart(data.freq || {});
        });
    }

    function buildEvolutionChart(evolution) {
        const ctx = document.getElementById('chart-evolution');
        if (!ctx || !window.Chart) return;
        const map = {};
        evolution.forEach(function(r) { map[r.jour] = parseInt(r.nb); });
        const labels = [], values = [];
        for (let i = 29; i >= 0; i--) {
            const d = new Date(); d.setDate(d.getDate() - i);
            const key = d.toISOString().slice(0, 10);
            labels.push(key.slice(5));
            values.push(map[key] || 0);
        }
        new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [{ data: values, borderColor: '#E8491D', backgroundColor: 'rgba(232,73,29,0.07)', borderWidth: 2, pointBackgroundColor: '#E8491D', pointRadius: 3, fill: true, tension: 0.4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 10 } }, y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } } } }
        });
    }

    function buildFreqChart(freq) {
        const ctx = document.getElementById('chart-freq');
        if (!ctx || !window.Chart) return;
        const labels = [], values = [], colors = [];
        // Trier par valeur décroissante
        const sorted = Object.keys(freq).sort(function(a,b){ return freq[b]-freq[a]; });
        sorted.forEach(function(k) {
            if (!DIMS[k]) return;
            labels.push(DIMS[k].icon + ' ' + DIMS[k].label);
            values.push(freq[k]);
            colors.push(DIMS[k].couleur || '#E8491D');
        });
        new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: colors.map(function(c){ return c+'20'; }), borderColor: colors, borderWidth: 2, borderRadius: 5 }] },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function(ctx) { return ' ' + ctx.raw + '% des participants'; } } }
                },
                scales: {
                    x: { beginAtZero: true, max: 100, ticks: { stepSize: 25, font: { size: 10 }, callback: function(v){ return v+'%'; } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    y: { ticks: { font: { size: 11 } }, grid: { display: false } }
                }
            }
        });
    }

    /* ════════════════════════════════════════════
       MODALE
    ════════════════════════════════════════════ */
    var currentSessionId   = null;
    var currentSessionData = null;

    function pvShowDetail(id, prenom, scores, top5Keys) {
        currentSessionId   = id;
        currentSessionData = { id: id, prenom: prenom, scores: scores, top5Keys: top5Keys };
        const modal = document.getElementById('pva-modal');
        if (!modal) return;
        modal.style.display = 'flex';
        setText('pva-modal-title', 'Profil de ' + prenom);

        // Reset feedback
        const fb = document.getElementById('pva-resend-feedback');
        if (fb) fb.textContent = '';

        const listEl = document.getElementById('pva-modal-top5');
        if (listEl) {
            listEl.innerHTML = top5Keys.map(function(k, i) {
                const dim = DIMS[k];
                if (!dim) return '';
                const pct = Math.min(100, scores[k] || 0);
                return '<div class="pva-modal-row">' +
                    '<span class="pva-modal-rank">#' + (i+1) + '</span>' +
                    '<span class="pva-modal-name">' + esc(dim.icon + ' ' + dim.label) + '</span>' +
                    '<span class="pva-modal-pct">' + pct + '%</span>' +
                '</div>';
            }).join('');
        }

        if (modalRadarChart) { modalRadarChart.destroy(); modalRadarChart = null; }

        // Délai pour que la modale soit rendue et le canvas ait des dimensions
        setTimeout(function() {
            var canvas = document.getElementById('pva-modal-radar');
            if (!canvas || !window.Chart) return;

            // Forcer les dimensions du canvas
            canvas.width  = canvas.offsetWidth  || 300;
            canvas.height = canvas.offsetHeight || 300;

            var labels = [], values = [], colors = [];
            Object.keys(DIMS).forEach(function(k) {
                labels.push(DIMS[k].label);
                values.push(Math.min(100, Math.round(scores[k] || 0)));
                colors.push(DIMS[k].couleur || '#E8491D');
            });

            modalRadarChart = new Chart(canvas, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: 'rgba(232,73,29,0.12)',
                        borderColor: '#E8491D',
                        borderWidth: 2,
                        pointBackgroundColor: colors,
                        pointBorderColor: '#fff',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    }]
                },
                options: {
                    aspectRatio: 1,
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            ticks: { stepSize: 25, font: { size: 9 }, backdropColor: 'transparent', color: '#9CA3AF' },
                            grid: { color: 'rgba(0,0,0,0.08)' },
                            angleLines: { color: 'rgba(0,0,0,0.08)' },
                            pointLabels: { font: { size: 10, weight: '600' }, color: '#1B2A4A' }
                        }
                    },
                    plugins: { legend: { display: false } },
                    animation: { duration: 500 }
                }
            });
        }, 50);
    }

    function closeModal() {
        const m = document.getElementById('pva-modal');
        if (m) m.style.display = 'none';
        if (modalRadarChart) { modalRadarChart.destroy(); modalRadarChart = null; }
    }

    /* ════════════════════════════════════════════
       SUPPRESSION
    ════════════════════════════════════════════ */
    function pvDeleteSession(id) {
        if (!confirm('Supprimer ce participant ? Action irréversible.')) return;
        ajax('pv_admin_delete_session', { id: id }, function() {
            loadSessions(currentPage);
            loadStats();
        });
    }

    /* ════════════════════════════════════════════
       RÉGLAGES
    ════════════════════════════════════════════ */
    function prefillSettings() {
        var map = { 'set-email': SETS.consultant_email, 'set-smtp-host': SETS.smtp_host, 'set-smtp-user': SETS.smtp_user };
        Object.keys(map).forEach(function(id) {
            var el = document.getElementById(id);
            if (el && map[id]) el.value = map[id];
        });
    }

    function showFeedback(type, msg) {
        var el = document.getElementById('pva-settings-feedback');
        if (!el) return;
        el.className = 'pva-feedback ' + type;
        el.textContent = msg;
        el.style.display = 'block';
        setTimeout(function() { el.style.display = 'none'; }, 4000);
    }

    /* ════════════════════════════════════════════
       GÉNÉRATION PDF (impression navigateur)
    ════════════════════════════════════════════ */
    function generatePDF(data) {
        var mapping = pvAdmin.mapping || {};
        var prenom  = data.prenom;
        var scores  = data.scores;
        var top5    = data.top5Keys;

        // Construire les lignes du Top 5 avec descriptions
        var top5Html = top5.map(function(k, i) {
            var dim  = DIMS[k] || {};
            var map  = mapping[k] || {};
            var pct  = Math.min(100, Math.round(scores[k] || 0));
            var bar  = '<div style="height:8px;background:#F3F4F6;border-radius:4px;margin:8px 0 6px"><div style="height:8px;background:#E8491D;border-radius:4px;width:' + pct + '%"></div></div>';
            return '<div style="margin-bottom:20px;padding:16px 20px;border-left:4px solid #E8491D;background:#FFF8F6;border-radius:0 8px 8px 0;">' +
                '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">' +
                    '<div><span style="font-size:11px;font-weight:700;color:#E8491D;text-transform:uppercase;letter-spacing:1px">Valeur #' + (i+1) + '</span><br>' +
                    '<strong style="font-size:17px;color:#1B2A4A">' + (dim.icon||'') + ' ' + (dim.label||k) + '</strong></div>' +
                    '<span style="font-size:18px;font-weight:800;color:#E8491D">' + pct + '%</span>' +
                '</div>' +
                bar +
                (map.description ? '<p style="font-size:13px;color:#444;line-height:1.6;margin:8px 0 4px">' + map.description + '</p>' : '') +
                (map.implication ? '<p style="font-size:12px;color:#666;background:#fff;padding:8px 12px;border-radius:6px;border-left:3px solid #E8491D;margin:0"><strong>💼 Implication professionnelle :</strong> ' + map.implication + '</p>' : '') +
            '</div>';
        }).join('');

        // Toutes les dimensions triées
        var allDimsHtml = Object.keys(scores).sort(function(a,b){ return scores[b]-scores[a]; }).map(function(k) {
            var dim = DIMS[k] || {};
            var pct = Math.min(100, Math.round(scores[k] || 0));
            var isTop = top5.indexOf(k) !== -1;
            return '<tr>' +
                '<td style="padding:8px 12px;font-size:13px;font-weight:' + (isTop?'700':'400') + ';color:' + (isTop?'#E8491D':'#374151') + '">' + (dim.icon||'') + ' ' + (dim.label||k) + (isTop?' ★':'') + '</td>' +
                '<td style="padding:8px 12px;width:60%;vertical-align:middle">' +
                    '<div style="background:#F3F4F6;border-radius:4px;height:10px"><div style="background:' + (isTop?'#E8491D':'#9CA3AF') + ';width:' + pct + '%;height:10px;border-radius:4px"></div></div>' +
                '</td>' +
                '<td style="padding:8px 12px;font-size:13px;font-weight:700;color:' + (isTop?'#E8491D':'#6B7280') + ';text-align:right">' + pct + '%</td>' +
            '</tr>';
        }).join('');

        var win = window.open('', '_blank');
        win.document.write('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">');
        win.document.write('<title>Profil PraxiValeurs — ' + prenom + '</title>');
        win.document.write('<style>');
        win.document.write('body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;margin:0;padding:0;color:#1B2A4A;background:#fff}');
        win.document.write('.page{max-width:800px;margin:0 auto;padding:40px 40px}');
        win.document.write('h1{font-size:26px;margin:0}h2{font-size:16px;font-weight:700;color:#1B2A4A;border-bottom:2px solid #E8491D;padding-bottom:8px;margin:32px 0 16px}');
        win.document.write('table{width:100%;border-collapse:collapse}');
        win.document.write('@media print{.no-print{display:none}.page{padding:20px}@page{margin:1.5cm}}');
        win.document.write('</style></head><body>');
        win.document.write('<div class="page">');

        // Header
        win.document.write('<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;padding-bottom:20px;border-bottom:3px solid #1B2A4A">');
        win.document.write('<div><div style="font-size:11px;font-weight:700;color:#E8491D;text-transform:uppercase;letter-spacing:2px;margin-bottom:4px">Praxis Accompagnement · Bilan de compétences</div>');
        win.document.write('<h1>Profil de valeurs de <span style="color:#E8491D">' + esc(prenom) + '</span></h1></div>');
        win.document.write('<div style="text-align:right;font-size:12px;color:#9CA3AF">Généré le ' + new Date().toLocaleDateString('fr-FR') + '<br>PraxiValeurs v2.x</div></div>');

        // Top 5
        win.document.write('<h2>⭐ Vos 5 valeurs dominantes</h2>');
        win.document.write(top5Html);

        // Tableau complet
        win.document.write('<h2>📊 Résultats détaillés — toutes les dimensions</h2>');
        win.document.write('<table><thead><tr>');
        win.document.write('<th style="padding:8px 12px;text-align:left;font-size:11px;text-transform:uppercase;color:#6B7280;border-bottom:1px solid #E5E7EB">Valeur</th>');
        win.document.write('<th style="padding:8px 12px;font-size:11px;text-transform:uppercase;color:#6B7280;border-bottom:1px solid #E5E7EB">Score</th>');
        win.document.write('<th style="padding:8px 12px;text-align:right;font-size:11px;text-transform:uppercase;color:#6B7280;border-bottom:1px solid #E5E7EB">%</th>');
        win.document.write('</tr></thead><tbody>');
        win.document.write(allDimsHtml);
        win.document.write('</tbody></table>');

        // Footer
        win.document.write('<div style="margin-top:40px;padding-top:16px;border-top:1px solid #E5E7EB;font-size:11px;color:#9CA3AF;text-align:center">');
        win.document.write('Praxis Accompagnement · praxis-accompagnement.fr · Ce profil est confidentiel et destiné au bilan de compétences.');
        win.document.write('</div>');

        // Bouton imprimer
        win.document.write('<div class="no-print" style="text-align:center;margin-top:32px">');
        win.document.write('<button onclick="window.print()" style="background:#E8491D;color:#fff;border:none;padding:12px 32px;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer">🖨️ Imprimer / Enregistrer en PDF</button>');
        win.document.write('</div>');

        win.document.write('</div></body></html>');
        win.document.close();
    }

    /* ════════════════════════════════════════════
       INITIALISATION — après déclaration de toutes les fonctions
    ════════════════════════════════════════════ */

    // Onglets
    document.querySelectorAll('.pva-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.pva-tab').forEach(function(t) { t.classList.remove('active'); });
            document.querySelectorAll('.pva-panel').forEach(function(p) { p.classList.remove('active'); });
            tab.classList.add('active');
            var panel = document.getElementById('pva-tab-' + tab.dataset.tab);
            if (panel) panel.classList.add('active');
            if (tab.dataset.tab === 'participants') loadSessions(1);
        });
    });

    // Recherche
    var searchInput = document.getElementById('pva-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() { loadSessions(1); }, 350);
        });
    }

    // Bouton renvoyer email
    var resendBtn = document.getElementById('pva-modal-resend');
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            if (!currentSessionId) return;
            resendBtn.disabled = true;
            resendBtn.textContent = 'Envoi en cours…';
            ajax('pv_admin_resend_email', { session_id: currentSessionId }, function(data) {
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg> Renvoyer les résultats par email';
                var fb = document.getElementById('pva-resend-feedback');
                if (fb) { fb.textContent = '✓ ' + (data.message || 'Email envoyé !'); fb.style.color = '#10B981'; }
            }, function() {
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg> Renvoyer les résultats par email';
                var fb = document.getElementById('pva-resend-feedback');
                if (fb) { fb.textContent = '✗ Erreur d\'envoi'; fb.style.color = '#DC2626'; }
            });
        });
    }

    // Bouton PDF
    var pdfBtn = document.getElementById('pva-modal-pdf');
    if (pdfBtn) {
        pdfBtn.addEventListener('click', function() {
            if (!currentSessionData) return;
            generatePDF(currentSessionData);
        });
    }

    // Modale fermeture
    var closeBtn = document.getElementById('pva-modal-close');
    var overlay  = document.querySelector('.pva-modal-overlay');
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (overlay)  overlay.addEventListener('click', closeModal);

    // Réglages sauvegarde
    var saveBtn = document.getElementById('pva-save-settings');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            ajax('pv_admin_save_settings', {
                consultant_email: getVal('set-email'),
                smtp_host:        getVal('set-smtp-host'),
                smtp_user:        getVal('set-smtp-user'),
                smtp_pass:        getVal('set-smtp-pass'),
            },
            function() { showFeedback('success', '✓ Réglages enregistrés.'); },
            function() { showFeedback('error',   '✗ Erreur lors de l\'enregistrement.'); });
        });
    }

    // Chargement initial
    loadStats();
    loadSessions(1);
    prefillSettings();

})();
