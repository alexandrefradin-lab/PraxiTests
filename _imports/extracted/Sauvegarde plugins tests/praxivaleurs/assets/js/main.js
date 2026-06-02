/**
 * PraxiValeurs — main.js v3.0
 * Parcours : 40 questions Likert → 20 comparaisons forcées → formulaire → résultats
 * Scoring : 60% Likert + 40% tournoi de comparaisons
 */

(function () {
    'use strict';

    const QUESTIONS  = praxiValeursData.questions;
    const DIMENSIONS = praxiValeursData.dimensions;
    const MAPPING    = praxiValeursData.mapping;
    const AJAX_URL   = praxiValeursData.ajax_url;
    const NONCE      = praxiValeursData.nonce;

    // ─── État global ───────────────────────────────────────────────────────────
    let currentQ      = 0;
    let isAdvancing   = false;
    const reponses    = {};  // { question_id: valeur }

    // Comparaisons forcées
    let comparePairs  = [];  // [ [dimA, dimB], ... ] — 20 paires aléatoires
    let compareIndex  = 0;
    let compareScores = {};  // { dim_key: victoires (0-N) }
    let isComparing   = false;

    // ─── DOM ───────────────────────────────────────────────────────────────────
    const screenIntro    = document.getElementById('pv-screen-intro');
    const screenQuestions= document.getElementById('pv-screen-questions');
    const screenCompare  = document.getElementById('pv-screen-compare');
    const screenForm     = document.getElementById('pv-screen-form');
    const screenResults  = document.getElementById('pv-screen-results');
    const loader         = document.getElementById('pv-loader');

    const btnStart       = document.getElementById('pv-btn-start');
    const btnBack        = document.getElementById('pv-btn-back');
    const btnBackCompare = document.getElementById('pv-btn-back-compare');
    const btnSubmit      = document.getElementById('pv-btn-submit');

    const questionText   = document.getElementById('pv-question-text');
    const counter        = document.getElementById('pv-counter');
    const progressFill   = document.getElementById('pv-progress-fill');
    const progressPct    = document.getElementById('pv-progress-pct');
    const sectionLabel   = document.getElementById('pv-section-label');
    const likertBtns     = document.querySelectorAll('.pv-likert-btn');

    const compareCounter = document.getElementById('pv-compare-counter');
    const compareFill    = document.getElementById('pv-compare-fill');
    const comparePct     = document.getElementById('pv-compare-pct');
    const compareIntro   = document.getElementById('pv-compare-intro');
    const choiceA        = document.getElementById('pv-choice-a');
    const choiceB        = document.getElementById('pv-choice-b');
    const iconA          = document.getElementById('pv-icon-a');
    const iconB          = document.getElementById('pv-icon-b');
    const labelA         = document.getElementById('pv-label-a');
    const labelB         = document.getElementById('pv-label-b');

    const inputPrenom    = document.getElementById('pv-prenom');
    const inputEmail     = document.getElementById('pv-email');
    const formError      = document.getElementById('pv-form-error');
    const resultsPrenom  = document.getElementById('pv-results-prenom');
    const top5Cards      = document.getElementById('pv-top5-cards');

    // ─── Navigation ────────────────────────────────────────────────────────────
    function showScreen(screen) {
        [screenIntro, screenQuestions, screenCompare, screenForm, screenResults].forEach(s => {
            s.classList.remove('active');
        });
        screen.classList.add('active');
    }

    // ─── PARTIE 1 : QUESTIONS LIKERT ──────────────────────────────────────────

    function loadQuestion(index) {
        const q     = QUESTIONS[index];
        const total = QUESTIONS.length;
        const pct   = Math.round((index + 1) / total * 100);

        questionText.textContent      = q.texte;
        if (counter)      counter.textContent      = (index + 1) + ' / ' + total;
        if (progressFill) progressFill.style.width = pct + '%';
        if (progressPct)  progressPct.textContent  = pct + '%';

        const dimData = DIMENSIONS[q.dim];
        if (sectionLabel) sectionLabel.textContent = dimData ? dimData.icon + ' ' + dimData.label : 'Mes valeurs';

        if (btnBack) btnBack.style.visibility = index > 0 ? 'visible' : 'hidden';

        likertBtns.forEach(btn => {
            btn.classList.remove('selected');
            if (reponses[q.id] && parseInt(btn.dataset.value, 10) === reponses[q.id]) {
                btn.classList.add('selected');
            }
        });
    }

    function handleLikertClick(e) {
        if (isAdvancing) return;
        const btn   = e.currentTarget;
        const value = parseInt(btn.dataset.value, 10);
        const q     = QUESTIONS[currentQ];

        likertBtns.forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        reponses[q.id] = value;

        isAdvancing = true;
        setTimeout(() => {
            isAdvancing = false;
            currentQ++;
            if (currentQ < QUESTIONS.length) {
                loadQuestion(currentQ);
            } else {
                // Toutes les questions Likert répondues → lancer les comparaisons
                startComparisons();
            }
        }, 280);
    }

    // ─── PARTIE 2 : COMPARAISONS FORCÉES ──────────────────────────────────────

    function generatePairs(n) {
        // Générer toutes les paires possibles entre les 10 dimensions
        const dims  = Object.keys(DIMENSIONS);
        const allPairs = [];
        for (let i = 0; i < dims.length; i++) {
            for (let j = i + 1; j < dims.length; j++) {
                allPairs.push([dims[i], dims[j]]);
            }
        }
        // Mélanger (Fisher-Yates) et prendre les n premières
        for (let i = allPairs.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [allPairs[i], allPairs[j]] = [allPairs[j], allPairs[i]];
        }
        return allPairs.slice(0, n);
    }

    function startComparisons() {
        // Initialiser les scores de tournoi
        compareScores = {};
        Object.keys(DIMENSIONS).forEach(k => { compareScores[k] = 0; });

        comparePairs = generatePairs(20);
        compareIndex = 0;

        showScreen(screenCompare);
        loadComparison(0);
    }

    function loadComparison(index) {
        const total = comparePairs.length;
        const pct   = Math.round(index / total * 100);
        const pair  = comparePairs[index];

        if (compareCounter) compareCounter.textContent = (index + 1) + ' / ' + total;
        if (compareFill)    compareFill.style.width    = pct + '%';
        if (comparePct)     comparePct.textContent     = pct + '%';

        // Masquer l'intro après la 1ère comparaison
        if (compareIntro) compareIntro.style.display = index === 0 ? 'block' : 'none';

        // Bouton retour : visible dès la 2ème comparaison
        if (btnBackCompare) btnBackCompare.style.visibility = index > 0 ? 'visible' : 'hidden';

        const dimA = DIMENSIONS[pair[0]];
        const dimB = DIMENSIONS[pair[1]];

        // Reset état visuel
        [choiceA, choiceB].forEach(btn => {
            btn.classList.remove('selected', 'loser');
            btn.disabled = false;
        });

        if (iconA)  iconA.textContent  = dimA.icon;
        if (labelA) labelA.textContent = dimA.label;
        if (iconB)  iconB.textContent  = dimB.icon;
        if (labelB) labelB.textContent = dimB.label;

        // Descriptions courtes
        const descA = document.getElementById('pv-desc-a');
        const descB = document.getElementById('pv-desc-b');
        if (descA) descA.textContent = dimA.court || '';
        if (descB) descB.textContent = dimB.court || '';

        // Infobulles
        const tooltipA = document.getElementById('pv-tooltip-a');
        const tooltipB = document.getElementById('pv-tooltip-b');
        if (tooltipA) tooltipA.textContent = dimA.definition || '';
        if (tooltipB) tooltipB.textContent = dimB.definition || '';

        isComparing = false;
    }

    // Comparaisons — paires + vainqueurs stockés au moment du clic
    const comparaisonResults = []; // [ {a, b, winner}, ... ]

    function handleCompareClick(winner, loser) {
        if (isComparing) return;
        isComparing = true;

        winner.classList.add('selected');
        loser.classList.add('loser');
        winner.disabled = true;
        loser.disabled  = true;

        const pair    = comparePairs[compareIndex];
        const winKey  = winner === choiceA ? pair[0] : pair[1];
        const loseKey = winner === choiceA ? pair[1] : pair[0];

        // Enregistrer victoire et résultat de la paire
        compareScores[winKey]++;
        comparaisonResults[compareIndex] = { a: pair[0], b: pair[1], winner: winKey };

        setTimeout(() => {
            compareIndex++;
            if (compareIndex < comparePairs.length) {
                loadComparison(compareIndex);
            } else {
                showScreen(screenForm);
            }
        }, 320);
    }

    // ─── SCORING FINAL PONDÉRÉ ─────────────────────────────────────────────────
    // 60% score Likert normalisé + 40% score tournoi normalisé

    function computeFinalScores() {
        const dims = Object.keys(DIMENSIONS);

        // Score Likert par dimension (moyenne des items, normalisée 0-100)
        const likertRaw = {};
        dims.forEach(k => { likertRaw[k] = 0; });
        const counts = {};
        dims.forEach(k => { counts[k] = 0; });

        QUESTIONS.forEach(q => {
            if (reponses[q.id]) {
                likertRaw[q.dim] = (likertRaw[q.dim] || 0) + reponses[q.id];
                counts[q.dim]    = (counts[q.dim]    || 0) + 1;
            }
        });

        const likertNorm = {};
        dims.forEach(k => {
            likertNorm[k] = counts[k] > 0 ? Math.round((likertRaw[k] / counts[k] / 6) * 100) : 0;
        });

        // Score tournoi normalisé 0-100 (max victoires possible = 20 si tout gagné)
        const maxVictoires = Math.max(...Object.values(compareScores), 1);
        const tournoi = {};
        dims.forEach(k => {
            tournoi[k] = Math.round((compareScores[k] / maxVictoires) * 100);
        });

        // Score final pondéré : 60% Likert + 40% tournoi
        const final = {};
        dims.forEach(k => {
            final[k] = Math.round(likertNorm[k] * 0.60 + tournoi[k] * 0.40);
        });

        return { final, likertNorm, tournoi };
    }

    // ─── SOUMISSION FORMULAIRE ─────────────────────────────────────────────────

    function validateForm() {
        const prenom = inputPrenom.value.trim();
        const email  = inputEmail.value.trim();
        if (!prenom) return 'Veuillez entrer votre prénom.';
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return 'Veuillez entrer un email valide.';
        const nbRepondues = QUESTIONS.filter(q => reponses[q.id] !== undefined).length;
        if (nbRepondues < QUESTIONS.length) return 'Toutes les questions doivent être répondues (' + nbRepondues + '/40).';
        return null;
    }

    function submitForm() {
        const errMsg = validateForm();
        if (errMsg) {
            formError.textContent    = errMsg;
            formError.style.display  = 'block';
            return;
        }
        formError.style.display = 'none';

        const { final, likertNorm, tournoi } = computeFinalScores();

        const fd = new FormData();
        fd.append('action', 'praxivaleurs_submit');
        fd.append('nonce',  NONCE);
        fd.append('prenom', inputPrenom.value.trim());
        fd.append('email',  inputEmail.value.trim());

        // Réponses Likert
        for (const [id, val] of Object.entries(reponses)) {
            fd.append('reponses[' + id + ']', val);
        }

        // Comparaisons — résultats stockés au moment de chaque clic
        comparaisonResults.forEach((res, i) => {
            fd.append('comparaisons[' + i + '][a]',      res.a);
            fd.append('comparaisons[' + i + '][b]',      res.b);
            fd.append('comparaisons[' + i + '][winner]', res.winner);
        });

        // Scores finaux pré-calculés
        for (const [k, v] of Object.entries(final)) {
            fd.append('scores_finaux[' + k + ']', v);
        }

        loader.style.display = 'flex';
        btnSubmit.disabled   = true;

        fetch(AJAX_URL, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                loader.style.display = 'none';
                btnSubmit.disabled   = false;
                if (data.success) {
                    afficherResultats(data.data);
                } else {
                    formError.textContent   = data.data && data.data.message ? data.data.message : 'Une erreur est survenue.';
                    formError.style.display = 'block';
                }
            })
            .catch(() => {
                loader.style.display = 'none';
                btnSubmit.disabled   = false;
                formError.textContent   = 'Erreur réseau. Veuillez réessayer.';
                formError.style.display = 'block';
            });
    }

    // ─── AFFICHAGE RÉSULTATS ───────────────────────────────────────────────────

    function afficherResultats(data) {
        const { prenom, mapping, dimensions, scores, top5 } = data;

        resultsPrenom.textContent = prenom;

        top5Cards.innerHTML = '';
        let rang = 1;
        for (const [dimKey, score] of Object.entries(top5)) {
            const dim  = dimensions[dimKey];
            const map  = mapping[dimKey];
            const pct  = score; // score déjà en % (0-100)
            const card = document.createElement('div');
            card.className = 'pv-value-card';
            card.style.animationDelay = (rang * 0.08) + 's';
            card.innerHTML =
                '<div class="pv-value-card-header">' +
                    '<div class="pv-value-icon">' + dim.icon + '</div>' +
                    '<div>' +
                        '<div class="pv-value-rank">Valeur #' + rang + '</div>' +
                        '<span class="pv-value-label" style="color:' + dim.couleur + '">' + dim.label + '</span>' +
                        '<span class="pv-value-pct">' + score + '%</span>' +
                    '</div>' +
                '</div>' +
                '<p class="pv-value-desc">' + map.description + '</p>' +
                '<p class="pv-value-impl"><strong>💼 Pour votre projet professionnel :</strong> ' + map.implication + '</p>';
            top5Cards.appendChild(card);
            rang++;
        }

        showScreen(screenResults);

        setTimeout(() => { buildRadar(scores, dimensions); }, 100);
    }

    function buildRadar(scores, dimensions) {
        const canvas = document.getElementById('pv-radar-chart');
        if (!canvas || !window.Chart) return;

        const labels = Object.values(dimensions).map(d => d.icon + ' ' + d.label);
        const values = Object.keys(dimensions).map(k => scores[k] || 0);
        const colors = Object.values(dimensions).map(d => d.couleur);

        new Chart(canvas, {
            type: 'radar',
            data: {
                labels,
                datasets: [{
                    label: 'Mes valeurs',
                    data: values,
                    backgroundColor: 'rgba(232,73,29,0.12)',
                    borderColor: '#E8491D',
                    borderWidth: 2,
                    pointBackgroundColor: colors,
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                }]
            },
            options: {
                scales: {
                    r: {
                        min: 0, max: 100,
                        ticks: { stepSize: 25, font: { size: 10 }, color: '#999', backdropColor: 'transparent' },
                        grid: { color: 'rgba(0,0,0,0.08)' },
                        angleLines: { color: 'rgba(0,0,0,0.08)' },
                        pointLabels: { font: { size: 11, weight: '600' }, color: '#1B2A4A' }
                    }
                },
                plugins: { legend: { display: false } },
                animation: { duration: 800, easing: 'easeOutQuart' },
            }
        });
    }

    // ─── ÉVÉNEMENTS ────────────────────────────────────────────────────────────

    if (btnStart) {
        btnStart.addEventListener('click', () => {
            showScreen(screenQuestions);
            loadQuestion(0);
        });
    }

    if (btnBack) {
        btnBack.addEventListener('click', () => {
            if (isAdvancing) return;
            if (currentQ > 0) { currentQ--; loadQuestion(currentQ); }
        });
    }

    if (btnBackCompare) {
        btnBackCompare.addEventListener('click', () => {
            if (isComparing) return;
            if (compareIndex > 0) {
                compareIndex--;
                // Annuler la victoire enregistrée pour cette comparaison
                const res = comparaisonResults[compareIndex];
                if (res) compareScores[res.winner] = Math.max(0, compareScores[res.winner] - 1);
                comparaisonResults.splice(compareIndex, 1);
                loadComparison(compareIndex);
            }
        });
    }

    likertBtns.forEach(btn => { btn.addEventListener('click', handleLikertClick); });

    if (choiceA) choiceA.addEventListener('click', () => { handleCompareClick(choiceA, choiceB); });
    if (choiceB) choiceB.addEventListener('click', () => { handleCompareClick(choiceB, choiceA); });

    if (btnSubmit) btnSubmit.addEventListener('click', submitForm);
    [inputPrenom, inputEmail].forEach(input => {
        if (input) input.addEventListener('keydown', e => { if (e.key === 'Enter') submitForm(); });
    });

})();
