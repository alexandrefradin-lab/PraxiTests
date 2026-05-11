/* ═══════════════════════════════════════════════════════════
   PraxiCare v1.3.5 — main.js
   UX Standard Cali : 1 question/écran, auto-avancement 280ms
   Gate email obligatoire avant résultats
═══════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  // ── Questions ───────────────────────────────────────────────────────
  const SECTIONS = [
    {
      id: 'demandes', label: 'Charge de travail', transition: null,
      questions: [
        { key: 'D1', texte: 'Mon travail demande de travailler très vite.' },
        { key: 'D2', texte: 'Mon travail demande de travailler intensément.' },
        { key: 'D3', texte: 'On me demande d\'effectuer une quantité de travail excessive.' },
        { key: 'D4', texte: 'Je dispose du temps nécessaire pour exécuter correctement mon travail.' },
        { key: 'D5', texte: 'Je reçois des ordres contradictoires de la part des autres.' },
        { key: 'D6', texte: 'Mon travail nécessite de longues périodes de concentration intense.' },
        { key: 'D7', texte: 'Mon travail est souvent interrompu avant que je l\'aie terminé.' },
        { key: 'D8', texte: 'Je subis une pression constante dans mon travail.' },
        { key: 'D9', texte: 'Attendre le travail de collègues ou d\'autres départements ralentit souvent mon propre travail.' },
      ],
      choix: [
        { label: 'Pas du tout d\'accord', val: 1 },
        { label: 'Pas d\'accord',         val: 2 },
        { label: 'D\'accord',             val: 3 },
        { label: 'Tout à fait d\'accord', val: 4 },
      ],
    },
    {
      id: 'latitude', label: 'Autonomie', transition: null,
      questions: [
        { key: 'L1', texte: 'Mon travail me permet de prendre des décisions de façon autonome.' },
        { key: 'L2', texte: 'J\'ai la possibilité d\'influencer le déroulement de mon travail.' },
        { key: 'L3', texte: 'Mon travail me permet de développer mes compétences professionnelles.' },
        { key: 'L4', texte: 'Mon travail implique d\'apprendre des choses nouvelles.' },
        { key: 'L5', texte: 'Mon travail me permet d\'utiliser mes compétences et savoir-faire.' },
        { key: 'L6', texte: 'Dans mon travail, j\'ai la possibilité de faire des choses variées.' },
        { key: 'L7', texte: 'J\'ai mon mot à dire sur ce qui se passe dans mon service.' },
        { key: 'L8', texte: 'J\'ai la possibilité de développer mes habiletés personnelles.' },
        { key: 'L9', texte: 'Au travail, j\'ai la possibilité de faire preuve de créativité.' },
      ],
      choix: [
        { label: 'Pas du tout d\'accord', val: 1 },
        { label: 'Pas d\'accord',         val: 2 },
        { label: 'D\'accord',             val: 3 },
        { label: 'Tout à fait d\'accord', val: 4 },
      ],
    },
    {
      id: 'soutien', label: 'Soutien', transition: null,
      questions: [
        { key: 'S1', texte: 'Mon supérieur se soucie du bien-être des personnes qu\'il dirige.' },
        { key: 'S2', texte: 'Mon supérieur prête attention à ce que je dis.' },
        { key: 'S3', texte: 'Mon supérieur m\'aide à mener mes tâches à bien.' },
        { key: 'S4', texte: 'Mon supérieur réussit facilement à faire travailler les gens ensemble.' },
        { key: 'S5', texte: 'Les collègues avec qui je travaille sont des gens professionnellement compétents.' },
        { key: 'S6', texte: 'Les collègues avec qui je travaille me manifestent de l\'intérêt.' },
        { key: 'S7', texte: 'Les collègues avec qui je travaille sont amicaux.' },
        { key: 'S8', texte: 'Les collègues avec qui je travaille m\'aident à mener mes tâches à bien.' },
      ],
      choix: [
        { label: 'Pas du tout d\'accord', val: 1 },
        { label: 'Pas d\'accord',         val: 2 },
        { label: 'D\'accord',             val: 3 },
        { label: 'Tout à fait d\'accord', val: 4 },
      ],
    },
    {
      id: 'ee', label: 'Épuisement',
      transition: 'Vous êtes à mi-parcours 👏 La première partie portait sur votre environnement de travail. Parlons maintenant de ce que vous ressentez, vous.',
      questions: [
        { key: 'EE1', texte: 'Je me sens émotionnellement épuisé(e) par mon travail.' },
        { key: 'EE2', texte: 'Je me sens à bout en fin de journée de travail.' },
        { key: 'EE3', texte: 'Je me sens fatigué(e) quand je me lève le matin et que j\'ai à faire face à une nouvelle journée de travail.' },
        { key: 'EE4', texte: 'Travailler avec des gens toute la journée me demande un effort important.' },
        { key: 'EE5', texte: 'Je me sens usé(e) par mon travail.' },
        { key: 'EE6', texte: 'Je me sens frustré(e) par mon travail.' },
        { key: 'EE7', texte: 'Je pense que je travaille trop dur dans mon travail.' },
        { key: 'EE8', texte: 'Travailler directement avec des gens me stresse trop.' },
        { key: 'EE9', texte: 'Je me sens au bout du rouleau à cause de mon travail.' },
      ],
      choix: [
        { label: 'Jamais',   val: 0 },
        { label: 'Parfois',  val: 1 },
        { label: 'Souvent',  val: 2 },
        { label: 'Toujours', val: 3 },
      ],
    },
    {
      id: 'dp', label: 'Détachement', transition: null,
      questions: [
        { key: 'DP1', texte: 'Il m\'arrive de traiter certains collègues ou clients de manière froide et impersonnelle.' },
        { key: 'DP2', texte: 'Je suis devenu(e) indifférent(e) aux gens depuis que j\'ai ce travail.' },
        { key: 'DP3', texte: 'Je remarque que je deviens plus insensible aux gens depuis que j\'ai ce travail.' },
        { key: 'DP4', texte: 'Je crains que ce travail ne m\'endurcisse émotionnellement.' },
        { key: 'DP5', texte: 'Je ne me soucie plus vraiment de ce qui arrive aux personnes avec qui je travaille.' },
      ],
      choix: [
        { label: 'Jamais',   val: 0 },
        { label: 'Parfois',  val: 1 },
        { label: 'Souvent',  val: 2 },
        { label: 'Toujours', val: 3 },
      ],
    },
    {
      id: 'ap', label: 'Accomplissement', transition: null,
      questions: [
        { key: 'AP1', texte: 'Je parviens facilement à créer une atmosphère détendue avec les personnes avec qui je travaille.' },
        { key: 'AP2', texte: 'Le contact avec les gens dans mon travail me redonne de l\'énergie.' },
        { key: 'AP3', texte: 'J\'ai accompli des choses utiles et qui comptent dans mon travail.' },
        { key: 'AP4', texte: 'Dans mon travail, je traite les problèmes avec calme.' },
        { key: 'AP5', texte: 'Je sens que j\'influence positivement la vie des autres à travers mon travail.' },
        { key: 'AP6', texte: 'Je crée facilement une atmosphère détendue dans mon travail.' },
        { key: 'AP7', texte: 'Je me sens plein(e) d\'énergie dans mon travail.' },
        { key: 'AP8', texte: 'Dans mon travail, j\'ai le sentiment de m\'en sortir bien.' },
      ],
      choix: [
        { label: 'Jamais',   val: 0 },
        { label: 'Parfois',  val: 1 },
        { label: 'Souvent',  val: 2 },
        { label: 'Toujours', val: 3 },
      ],
    },
  ];

  // Clés S1–S4 à skipper si pas de supérieur
  const SKIP_NO_SUPERIOR = new Set(['S1','S2','S3','S4']);

  // ── État global ──────────────────────────────────────────────────────
  const state = {
    prenom:      '',
    email:       '',
    hasSuperior: true,
    reponses:    {},
    sectionIdx:  0,
    questionIdx: 0,
    globalIdx:   0,
    scores:      null,
    profil:      null,
  };

  function getTotalQuestions() {
    return state.hasSuperior ? 48 : 44;
  }

  // ── DOM ──────────────────────────────────────────────────────────────
  const $ = id => document.getElementById(id);

  const elIntro           = $('pc-intro');
  const elFiltreSuperieur = $('pc-filtre-superieur');
  const elQuestions       = $('pc-questions');
  const elGateEmail       = $('pc-gate-email');
  const elResults         = $('pc-results');

  // ── Helpers ──────────────────────────────────────────────────────────
  function showScreen(el) {
    [elIntro, elFiltreSuperieur, elQuestions, elGateEmail, elResults]
      .forEach(s => s.classList.remove('pc-active'));
    el.classList.add('pc-active');
    window.scrollTo(0, 0);
  }

  function updateProgress() {
    const total = getTotalQuestions();
    const pct   = Math.round((state.globalIdx / total) * 100);
    const sect  = SECTIONS[state.sectionIdx];
    $('pc-section-label').textContent = sect ? sect.label : '';
    $('pc-progress-pct').textContent  = pct + '%';
    $('pc-progress-fill').style.width = pct + '%';
    $('pc-compteur').textContent      = state.globalIdx + ' / ' + total;
  }

  // ── Rendu question ───────────────────────────────────────────────────
  function renderQuestion() {
    const sect = SECTIONS[state.sectionIdx];
    if (!sect) { showGate(); return; }

    const q = sect.questions[state.questionIdx];
    if (!q) { nextSection(); return; }

    // Skipper S1–S4 si pas de supérieur
    if (!state.hasSuperior && SKIP_NO_SUPERIOR.has(q.key)) {
      state.questionIdx++;
      renderQuestion();
      return;
    }

    $('pc-transition').classList.add('pc-hidden');
    $('pc-question-texte').textContent = q.texte;

    const elChoix = $('pc-choix');
    elChoix.innerHTML = '';
    sect.choix.forEach(c => {
      const btn = document.createElement('button');
      btn.className   = 'pc-choix-btn';
      btn.textContent = c.label;
      btn.dataset.val = c.val;
      if (state.reponses[q.key] !== undefined && state.reponses[q.key] == c.val) {
        btn.classList.add('selected');
      }
      btn.addEventListener('click', () => onChoix(btn, q.key, c.val));
      elChoix.appendChild(btn);
    });

    updateProgress();
  }

  function onChoix(btn, key, val) {
    $('pc-choix').querySelectorAll('.pc-choix-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    state.reponses[key] = val;
    setTimeout(() => {
      state.questionIdx++;
      state.globalIdx++;
      renderQuestion();
    }, 280);
  }

  function nextSection() {
    const nextIdx  = state.sectionIdx + 1;
    if (nextIdx >= SECTIONS.length) { showGate(); return; }

    const nextSect = SECTIONS[nextIdx];
    if (nextSect.transition) {
      // Masquer la carte, afficher le bloc transition
      document.querySelector('.pc-question-card').style.display = 'none';
      $('pc-transition-texte').textContent = nextSect.transition;
      $('pc-transition').classList.remove('pc-hidden');
      $('pc-btn-continuer').onclick = () => {
        state.sectionIdx  = nextIdx;
        state.questionIdx = 0;
        $('pc-transition').classList.add('pc-hidden');
        document.querySelector('.pc-question-card').style.display = '';
        renderQuestion();
      };
    } else {
      state.sectionIdx  = nextIdx;
      state.questionIdx = 0;
      renderQuestion();
    }
  }

  // ── Bouton Retour ────────────────────────────────────────────────────
  $('pc-btn-retour').addEventListener('click', () => {
    if (state.questionIdx > 0) {
      state.questionIdx--;
      state.globalIdx = Math.max(0, state.globalIdx - 1);
      // Reculer en sautant S1-S4 si pas de supérieur
      if (!state.hasSuperior) {
        const sect = SECTIONS[state.sectionIdx];
        while (state.questionIdx > 0 && sect && SKIP_NO_SUPERIOR.has(sect.questions[state.questionIdx]?.key)) {
          state.questionIdx--;
          state.globalIdx = Math.max(0, state.globalIdx - 1);
        }
      }
    } else if (state.sectionIdx > 0) {
      state.sectionIdx--;
      state.questionIdx = SECTIONS[state.sectionIdx].questions.length - 1;
      state.globalIdx   = Math.max(0, state.globalIdx - 1);
      if (!state.hasSuperior) {
        const sect = SECTIONS[state.sectionIdx];
        while (state.questionIdx > 0 && sect && SKIP_NO_SUPERIOR.has(sect.questions[state.questionIdx]?.key)) {
          state.questionIdx--;
          state.globalIdx = Math.max(0, state.globalIdx - 1);
        }
      }
    } else {
      showScreen(elFiltreSuperieur);
      return;
    }
    renderQuestion();
  });

  // ── Démarrer le test ─────────────────────────────────────────────────
  $('pc-btn-start').addEventListener('click', () => {
    const prenom = $('pc-prenom-input').value.trim();
    if (!prenom) {
      $('pc-prenom-input').focus();
      $('pc-prenom-input').style.borderColor = '#E8521A';
      return;
    }
    state.prenom = prenom;
    showScreen(elFiltreSuperieur);
  });

  // ── Filtre supérieur hiérarchique ────────────────────────────────────
  function startQuestions(hasSuperior) {
    state.hasSuperior = hasSuperior;
    state.sectionIdx  = 0;
    state.questionIdx = 0;
    state.globalIdx   = 0;
    state.reponses    = {};
    showScreen(elQuestions);
    renderQuestion();
  }

  $('pc-filtre-oui').addEventListener('click', () => {
    $('pc-filtre-oui').classList.add('selected');
    setTimeout(() => startQuestions(true), 280);
  });

  $('pc-filtre-non').addEventListener('click', () => {
    $('pc-filtre-non').classList.add('selected');
    setTimeout(() => startQuestions(false), 280);
  });

  // ── Gate email avant résultats ───────────────────────────────────────
  function showGate() {
    $('pc-gate-prenom').textContent = state.prenom;
    showScreen(elGateEmail);
  }

  $('pc-btn-gate-send').addEventListener('click', () => {
    const email  = $('pc-gate-email-input').value.trim();
    const rgpd   = $('pc-gate-rgpd').checked;
    const errEl  = $('pc-gate-error');

    if (!email || !rgpd || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      errEl.classList.remove('pc-hidden');
      return;
    }
    errEl.classList.add('pc-hidden');
    state.email = email;

    // Désactiver le bouton pour éviter double-clic
    const gateBtn = $('pc-btn-gate-send');
    gateBtn.disabled = true;
    gateBtn.textContent = 'Chargement\u2026';

    // Envoyer en arrière-plan (AJAX) — sans bloquer l'affichage
    const formData = new FormData();
    formData.append('action',       'praxicare_save');
    formData.append('nonce',        praxicare_ajax.nonce);
    formData.append('prenom',       state.prenom);
    formData.append('email',        email);
    formData.append('has_superior', state.hasSuperior ? '1' : '0');
    Object.entries(state.reponses).forEach(([k, v]) => {
      formData.append('reponses[' + k + ']', v);
    });
    fetch(praxicare_ajax.url, { method: 'POST', body: formData }).catch(() => {});

    // Afficher les résultats — protégé contre crash Chart.js
    try {
      showResults();
    } catch (e) {
      console.error('PraxiCare — erreur showResults:', e);
      showScreen(elResults);
      $('pc-prenom-display').textContent = state.prenom;
      if ($('pc-email-sent-to')) $('pc-email-sent-to').textContent = state.email;
    }
  });

  // ── Affichage résultats ──────────────────────────────────────────────
  function showResults() {
    showScreen(elResults);
    $('pc-prenom-display').textContent = state.prenom;

    const scores = calcScores();
    state.scores = scores;

    const profil = getProfil(scores);
    state.profil = profil;

    renderKarasek(scores);
    renderMBI(scores);
    renderAnalyse(scores);

    const profilBlock = $('pc-profil-block');
    profilBlock.className = 'pc-profil-block niveau-' + profil.niveau;
    $('pc-profil-emoji').textContent = profil.emoji;
    $('pc-profil-titre').textContent = profil.titre;
    $('pc-profil-texte').textContent = profil.texte;

    if (profil.niveau === 'critique' || profil.niveau === 'rouge') {
      $('pc-urgence-block').classList.remove('pc-hidden');
    }

    const list = $('pc-preco-list');
    list.innerHTML = '';
    const precos = buildPreconisations(profil, scores, state.hasSuperior);
    precos.forEach(p => {
      const li = document.createElement('li');
      if (typeof p === 'object' && p.lien) {
        const a = document.createElement('a');
        a.href        = p.lien;
        a.target      = '_blank';
        a.rel         = 'noopener';
        a.textContent = p.texte;
        a.style.color = '#E8541A';
        a.style.fontWeight = '600';
        li.appendChild(a);
      } else {
        li.textContent = p;
      }
      list.appendChild(li);
    });

    // Afficher l'email de confirmation
    if ($('pc-email-sent-to')) {
      $('pc-email-sent-to').textContent = state.email;
    }
  }



  // ── Préconisations dynamiques ───────────────────────────────────────
  function buildPreconisations(profil, scores, hasSuperior) {
    var rdv = "https://calendly.com/alex-fradin/15min";
    var niveau = profil.niveau;
    var precos = [];

    if (scores.demandes >= 28) {
      precos.push("Votre charge est très élevée : priorisez radicalement. Listez ce qui est vraiment non-négociable cette semaine et déléguez ou repoussez le reste sans culpabilité.");
    } else if (scores.demandes >= 22) {
      precos.push("Parlez de votre charge à votre manager ou à un référent RH, pas pour vous plaindre, mais pour identifier ce qui peut être allégé ou redistribué.");
    }

    if (scores.latitude <= 15) {
      precos.push("Votre marge de manoeuvre est très réduite. Cherchez, même à petite échelle, un espace où vous pouvez décider : l'organisation de votre journée, une méthode de travail. Reprendre une parcelle de contrôle change réellement le vécu.");
    } else if (scores.latitude <= 21) {
      precos.push("Identifiez 1 ou 2 domaines dans votre travail où vous pouvez proposer de nouvelles façons de faire. Même une petite initiative approuvée renforce le sentiment d'autonomie.");
    }

    var seuilSoutienBas = hasSuperior ? 16 : 8;
    var seuilSoutien    = hasSuperior ? 21 : 10;
    if (scores.soutien <= seuilSoutienBas) {
      precos.push("Vous êtes dans une situation d'isolement professionnel réel. Trouvez au moins une personne, en dehors du travail si nécessaire, à qui parler. La médecine du travail est aussi une ressource confidentielle et gratuite.");
      if (hasSuperior) {
        precos.push("La relation avec votre hiérarchie est très tendue. Si un entretien direct n'est pas possible, envisagez de passer par les RH ou un représentant du personnel.");
      }
    } else if (scores.soutien <= seuilSoutien) {
      precos.push("Le soutien autour de vous est limité, renforcez activement vos liens avec 1 ou 2 collègues de confiance. Ces relations informelles sont souvent le meilleur amortisseur du stress quotidien.");
    }

    if (scores.ee >= 22) {
      precos.push("Votre épuisement émotionnel est très élevé, consultez votre médecin traitant rapidement. Parlez-lui explicitement de votre situation professionnelle. Un arrêt de travail peut être nécessaire.");
      precos.push("Ne prenez aucune grande décision professionnelle dans cet état. Attendez d'aller mieux avant de vous positionner.");
    } else if (scores.ee >= 19) {
      precos.push("Votre fatigue émotionnelle dépasse le seuil critique. Consultez votre médecin traitant et évoquez votre situation au travail, il peut vous orienter et, si nécessaire, vous prescrire un arrêt.");
    } else if (scores.ee >= 11) {
      precos.push("Repérez à quel moment vous vous sentez le plus à plat, et protégez ces plages. Évitez de planifier des réunions ou des tâches exigeantes sur ces créneaux.");
    }

    if (scores.dp >= 10) {
      precos.push("Vous avez développé une distance protectrice envers les autres au travail. Essayez de maintenir au moins quelques interactions authentiques avec vos collègues, même courtes.");
    }

    if (scores.ap >= 17) {
      precos.push("Le sentiment de ne plus être efficace est souvent lié au contexte, pas à vos compétences réelles. Notez chaque jour 1 chose concrète accomplie, aussi petite soit-elle, cela aide à reconstruire le sens.");
    } else if (scores.ap >= 10) {
      precos.push("Identifiez ce qui vous donnait de la satisfaction il y a 6 ou 12 mois, et comparez avec aujourd'hui. Cette mise en perspective aide à cibler ce qui a vraiment changé.");
    }

    if (niveau === "critique" || niveau === "rouge") {
      precos.push("Si vous traversez une détresse intense, appelez le 3114 (numéro national de prévention, gratuit, 24h/24). Des professionnels sont là pour vous écouter.");
    }

    if (niveau === "vert") {
      precos.push("Continuez à surveiller vos signaux : sommeil, irritabilité, plaisir au travail. Ce sont les premiers indicateurs avant qu'une situation bascule.");
    }

    if (niveau === "jaune" && scores.demandes < 22 && scores.latitude <= 21) {
      precos.push("Un bilan de compétences peut vous aider à clarifier ce que vous voulez vraiment, et à reprendre la main sur votre trajectoire professionnelle.");
    }

    var rdvTextes = {
      vert:     "Envie de consolider cette dynamique ? Réservez un entretien gratuit avec Alexandre",
      jaune:    "Un échange de 15 minutes peut vous aider à y voir plus clair, entretien gratuit avec Alexandre",
      orange:   "Un accompagnement personnalisé peut vraiment changer les choses, entretien gratuit avec Alexandre",
      rouge:    "Vous n'avez pas à porter ça seul(e), parlez-en à Alexandre, entretien confidentiel et gratuit",
      critique: "Dès que vous vous sentez prêt(e), Alexandre est disponible, entretien gratuit et confidentiel",
    };
    precos.push({ texte: rdvTextes[niveau] || rdvTextes["jaune"], lien: rdv });

    return precos;
  }

  // ── Analyse détaillée par dimension ─────────────────────────────────
  function renderAnalyse(scores) {
    var container = document.getElementById("pc-analyse-list");
    if (!container) return;

    var hasSup = state.hasSuperior;

    var dims = [
      {
        titre: "Charge de travail",
        score: scores.demandes, max: 36,
        couleur: scores.demandes >= 28 ? "#C62828" : scores.demandes >= 22 ? "#E65100" : scores.demandes >= 15 ? "#2E7D32" : "#2E7D32",
        badge:   scores.demandes >= 28 ? "Très élevée" : scores.demandes >= 22 ? "Élevée" : scores.demandes >= 15 ? "Modérée" : "Faible",
        texte: (function() {
          var s = scores.demandes;
          if (s >= 28) return "Votre charge de travail est très élevée (" + s + "/36). Vous travaillez vite, intensément, sous pression constante, avec peu de temps pour souffler. Ce niveau de sollicitation prolongé use les ressources physiques et mentales.";
          if (s >= 22) return "Votre charge de travail est au-dessus du seuil critique (" + s + "/36). Vous ressentez une pression régulière, des demandes qui s'accumulent, parfois des interruptions ou des délais difficiles à tenir.";
          if (s >= 15) return "Votre charge de travail est dans une zone équilibrée (" + s + "/36). Vous avez une activité soutenue sans être débordé(e). C'est un bon indicateur, à condition que ce niveau reste stable.";
          return "Votre charge de travail est faible (" + s + "/36). Peu de pression, peu d'intensité. Si ce n'est pas choisi, cela peut générer un sentiment d'inutilité.";
        })(),
      },
      {
        titre: "Autonomie et latitude décisionnelle",
        score: scores.latitude, max: 36,
        couleur: scores.latitude <= 15 ? "#C62828" : scores.latitude <= 21 ? "#E65100" : "#2E7D32",
        badge:   scores.latitude <= 15 ? "Très faible" : scores.latitude <= 21 ? "Faible" : scores.latitude <= 28 ? "Bonne" : "Très bonne",
        texte: (function() {
          var s = scores.latitude;
          if (s <= 15) return "Votre autonomie est très faible (" + s + "/36). Vous avez très peu de marge pour décider, organiser ou influencer votre travail. Ce niveau de contrôle minimal génère un sentiment d'impuissance qui s'installe progressivement.";
          if (s <= 21) return "Votre latitude décisionnelle est en dessous du seuil (" + s + "/36). Vous avez peu de liberté pour organiser votre travail à votre façon. Ce manque d'autonomie amplifie les effets d'une charge élevée.";
          if (s <= 28) return "Vous disposez d'une bonne autonomie (" + s + "/36). Vous pouvez influencer votre travail, prendre des initiatives, développer vos compétences. C'est un facteur de protection important contre l'épuisement.";
          return "Votre niveau d'autonomie est très élevé (" + s + "/36). Vous avez une grande liberté dans l'organisation et les décisions, un atout réel.";
        })(),
      },
      {
        titre: "Soutien social" + (hasSup ? "" : " (collègues uniquement)"),
        score: scores.soutien, max: hasSup ? 32 : 16,
        couleur: (function() {
          var seuil = hasSup ? 21 : 10; var bas = hasSup ? 16 : 8;
          return scores.soutien <= bas ? "#C62828" : scores.soutien <= seuil ? "#E65100" : "#2E7D32";
        })(),
        badge: (function() {
          var seuil = hasSup ? 21 : 10; var bas = hasSup ? 16 : 8;
          return scores.soutien <= bas ? "Très faible" : scores.soutien <= seuil ? "Faible" : scores.soutien <= (hasSup ? 27 : 13) ? "Bon" : "Très bon";
        })(),
        texte: (function() {
          var s = scores.soutien; var seuil = hasSup ? 21 : 10; var bas = hasSup ? 16 : 8; var max = hasSup ? 32 : 16;
          if (s <= bas) return "Votre niveau de soutien est très faible (" + s + "/" + max + "). " + (hasSup ? "Ni votre hiérarchie ni vos collègues ne constituent un appui solide." : "Vos collègues ne constituent pas un appui solide.") + " L'isolement dans un contexte de pression est l'un des facteurs les plus aggravants.";
          if (s <= seuil) return "Votre soutien social est en dessous du seuil (" + s + "/" + max + "). " + (hasSup ? "Les relations avec la hiérarchie ou les collègues ne sont pas suffisamment soutenantes." : "Le soutien entre collègues reste limité.") + " L'absence de relais humain finit par peser.";
          return "Vous bénéficiez d'un bon soutien social (" + s + "/" + max + "). " + (hasSup ? "Votre manager et/ou vos collègues constituent un appui réel." : "Vos collègues constituent un appui réel.") + " C'est un facteur de protection majeur.";
        })(),
      },
      {
        titre: "Épuisement émotionnel",
        score: scores.ee, max: 27,
        couleur: scores.ee >= 19 ? "#C62828" : scores.ee >= 11 ? "#E65100" : "#2E7D32",
        badge:   scores.ee >= 19 ? "Élevé" : scores.ee >= 11 ? "Modéré" : "Faible",
        texte: (function() {
          var s = scores.ee;
          if (s >= 22) return "Votre épuisement émotionnel est très élevé (" + s + "/27). Vous vous sentez à bout physiquement et émotionnellement. Ce niveau d'usure ne se rattrape pas avec un week-end. Il signale un besoin de récupération profond et durable.";
          if (s >= 19) return "Votre épuisement émotionnel dépasse le seuil critique (" + s + "/27). Vous ressentez une fatigue persistante que le repos habituel ne suffit plus à effacer. Votre réserve émotionnelle est sérieusement entamée.";
          if (s >= 11) return "Votre épuisement émotionnel est modéré (" + s + "/27). Vous ressentez de la fatigue, des moments où vous êtes à plat, sans que ce soit constant. C'est une zone de vigilance à ne pas ignorer.";
          return "Votre niveau d'épuisement émotionnel est faible (" + s + "/27). Vous ne montrez pas de signe d'usure significative sur le plan émotionnel.";
        })(),
      },
      {
        titre: "Détachement affectif",
        score: scores.dp, max: 15,
        couleur: scores.dp >= 10 ? "#C62828" : scores.dp >= 5 ? "#E65100" : "#2E7D32",
        badge:   scores.dp >= 10 ? "Élevé" : scores.dp >= 5 ? "Modéré" : "Faible",
        texte: (function() {
          var s = scores.dp;
          if (s >= 10) return "Votre détachement affectif est élevé (" + s + "/15). Vous avez développé une distance notable envers les personnes avec qui vous travaillez. C'est souvent une protection inconsciente contre la surcharge émotionnelle, mais elle peut renforcer l'isolement.";
          if (s >= 5) return "Votre détachement affectif est modéré (" + s + "/15). Vous remarquez parfois une certaine froideur dans vos interactions. C'est une zone à surveiller, surtout si elle s'accompagne d'épuisement.";
          return "Votre niveau de détachement affectif est faible (" + s + "/15). Vous restez impliqué(e) émotionnellement dans vos relations professionnelles. C'est un signe de bon ancrage relationnel.";
        })(),
      },
      {
        titre: "Sentiment d'accomplissement",
        score: scores.ap, max: 24,
        couleur: scores.ap >= 17 ? "#C62828" : scores.ap >= 10 ? "#E65100" : "#2E7D32",
        badge:   scores.ap >= 17 ? "Manque élevé" : scores.ap >= 10 ? "Manque modéré" : "Bon",
        texte: (function() {
          var s = scores.ap;
          if (s >= 17) return "Votre sentiment d'accomplissement est très altéré (" + s + "/24 de manque). Vous avez du mal à percevoir votre utilité et à vous sentir efficace. Ce n'est pas un manque de compétences, c'est souvent le signe que le contexte ne vous permet plus d'exprimer ce dont vous êtes capable.";
          if (s >= 10) return "Votre sentiment d'accomplissement est partiellement altéré (" + s + "/24 de manque). Vous avez des moments de doute sur votre efficacité ou l'utilité de votre travail. Ce n'est pas constant, mais c'est un signal qui mérite attention.";
          return "Votre sentiment d'accomplissement est préservé (" + s + "/24 de manque). Vous avez globalement le sentiment de faire un travail utile et d'être efficace. C'est un facteur de résilience important.";
        })(),
      },
    ];

    container.innerHTML = "";
    dims.forEach(function(d) {
      var pct = Math.round((d.score / d.max) * 100);
      var col = d.couleur;
      var item = document.createElement("div");
      item.className = "pc-analyse-item";
      item.innerHTML =
        "<div class='pc-analyse-header'>" +
          "<span class='pc-analyse-dim'>" + d.titre + "</span>" +
          "<span class='pc-analyse-badge' style='color:" + col + ";border-color:" + col + "40;background:" + col + "10;'>" + d.badge + "</span>" +
        "</div>" +
        "<div class='pc-analyse-bar-wrap'>" +
          "<div class='pc-analyse-bar' style='width:" + pct + "%;background:" + col + ";'></div>" +
          "<span class='pc-analyse-score' style='color:" + col + ";'>" + d.score + " / " + d.max + "</span>" +
        "</div>" +
        "<p class='pc-analyse-texte'>" + d.texte + "</p>";
      container.appendChild(item);
    });
  }

  // ── Calcul scores ────────────────────────────────────────────────────
  function calcScores() {
    let demandes = 0, latitude = 0, soutien = 0, ee = 0, dp = 0, ap = 0;

    for (let i = 1; i <= 9; i++) {
      const val = parseInt(state.reponses['D' + i] || 1, 10);
      demandes += (i === 4) ? (5 - val) : val;
    }
    for (let i = 1; i <= 9; i++) latitude += parseInt(state.reponses['L' + i] || 1, 10);

    const soutienStart = state.hasSuperior ? 1 : 5;
    for (let i = soutienStart; i <= 8; i++) soutien += parseInt(state.reponses['S' + i] || 1, 10);

    for (let i = 1; i <= 9; i++) ee += parseInt(state.reponses['EE' + i] || 0, 10);
    for (let i = 1; i <= 5; i++) dp += parseInt(state.reponses['DP' + i] || 0, 10);
    for (let i = 1; i <= 8; i++) ap += (3 - parseInt(state.reponses['AP' + i] || 3, 10));

    return { demandes, latitude, soutien, ee, dp, ap };
  }

  // ── Profils ──────────────────────────────────────────────────────────
  function getProfil(s) {
    const seuilSoutien = state.hasSuperior ? 21 : 10;
    const jobStrain  = s.demandes >= 22 && s.latitude <= 21;
    const isoStrain  = jobStrain && s.soutien <= seuilSoutien;
    const passif     = s.demandes <  22 && s.latitude <= 21;
    const actif      = s.demandes >= 22 && s.latitude >  21;
    const detendu    = s.demandes <  22 && s.latitude >  21;

    const eeEleve    = s.ee >= 19;
    const dpEleve    = s.dp >= 10;
    const apEleve    = s.ap >= 17;
    const nbDims     = (eeEleve ? 1 : 0) + (dpEleve ? 1 : 0) + (apEleve ? 1 : 0);

    const burnoutS   = nbDims === 3;
    const burnoutM   = nbDims === 2;
    const burnoutL   = nbDims === 1;
    const pasBurnout = nbDims === 0;

    if (isoStrain && burnoutS)                          return PROFILS.urgence;
    if (jobStrain && burnoutS)                          return PROFILS.souffrance_averee;
    if (isoStrain && burnoutM)                          return PROFILS.alarme;
    if (jobStrain && burnoutM)                          return PROFILS.souffrance_installee;
    if (isoStrain && burnoutL)                          return PROFILS.tension_isolee;
    if ((actif || detendu || passif) && burnoutS)       return PROFILS.epuisement_interne;
    if ((actif || detendu || passif) && burnoutM)       return PROFILS.fragilite;
    if (jobStrain && burnoutL)                          return PROFILS.risque_cumule;
    if ((actif || detendu || passif) && burnoutL)       return PROFILS.vigilance;
    if (jobStrain && pasBurnout)                        return PROFILS.risque_situationnel;
    if (passif && pasBurnout)                           return PROFILS.sous_stimulation;
    if (actif && pasBurnout)                            return PROFILS.engagement_sain;
    return PROFILS.bien_etre;
  }

  const RDV         = { texte: '📅 Réservez un entretien gratuit avec Alexandre', lien: 'https://calendly.com/alex-fradin/15min' };
  const RDV_BILAN   = { texte: '📅 Un bilan de compétences peut vous aider à y voir plus clair, entretien gratuit', lien: 'https://calendly.com/alex-fradin/15min' };
  const RDV_COACHING = { texte: '📅 Un coaching peut vous aider à reprendre la main, entretien gratuit', lien: 'https://calendly.com/alex-fradin/15min' };
  const RDV_ACCOMP  = { texte: '📅 Un accompagnement peut vraiment changer les choses, entretien gratuit', lien: 'https://calendly.com/alex-fradin/15min' };
  const RDV_SORTIR  = { texte: '📅 Un coaching ou bilan peut vous aider à trouver une sortie, entretien gratuit', lien: 'https://calendly.com/alex-fradin/15min' };
  const RDV_CONFID  = { texte: '📅 Parlez-en à Alexandre, entretien confidentiel et gratuit', lien: 'https://calendly.com/alex-fradin/15min' };
  const RDV_PRET    = { texte: '📅 Alexandre peut vous accompagner dès que vous vous sentez prêt(e), entretien gratuit', lien: 'https://calendly.com/alex-fradin/15min' };

  const PROFILS = {
    bien_etre:            { niveau:'vert',     emoji:'✅', titre:'Votre situation professionnelle est saine',               texte:'Votre charge de travail est raisonnable, vous avez de la marge pour décider, et vous ne montrez pas de signe d\'épuisement. Une bonne position, mais qui mérite d\'être entretenue, car les contextes professionnels peuvent évoluer vite.', preconisations:['Identifiez ce qui rend votre travail satisfaisant, et protégez-le','Continuez à développer vos compétences et vos relations professionnelles','Restez attentif(ve) aux changements de contexte : une surcharge passagère peut vite faire basculer la situation', RDV] },
    engagement_sain:      { niveau:'vert',     emoji:'✅', titre:'Vous êtes engagé(e) et en bonne forme professionnelle',   texte:'Votre travail est exigeant. Vraie charge, vrai rythme, mais vous disposez d\'assez d\'autonomie pour y faire face, et votre état intérieur suit. C\'est le profil de quelqu\'un qui avance bien, à condition de ne pas négliger sa récupération.', preconisations:['Vos efforts sont réels. Assurez-vous que votre repos l\'est aussi. Le cerveau a besoin de vraies coupures','Apprenez à repérer vos premiers signaux d\'alerte : irritabilité, sommeil perturbé, perte de plaisir','Vérifiez régulièrement que votre charge reste compatible avec votre énergie réelle', RDV_COACHING] },
    sous_stimulation:     { niveau:'jaune',    emoji:'🟡', titre:'Votre travail ne vous sollicite pas assez',              texte:'Vous n\'êtes pas débordé(e), c\'est plutôt le contraire. Peu de défis, peu d\'autonomie, peu de sentiment de progresser. Ce type de situation use autrement : ennui, sentiment d\'inutilité, démotivation progressive. Ce n\'est pas anodin.', preconisations:['Mettez des mots sur ce qui vous manque : de la reconnaissance, des responsabilités, plus de liberté ? C\'est le point de départ pour agir','Parlez-en à votre manager, pas pour vous plaindre, mais pour proposer : un projet transversal, une nouvelle mission','Si votre poste ne peut pas évoluer, faites le point sur vos compétences et vos aspirations', RDV_BILAN] },
    risque_situationnel:  { niveau:'jaune',    emoji:'🟡', titre:'Votre environnement de travail est sous pression',       texte:'Votre charge est élevée et vous avez peu de marge pour organiser votre travail à votre façon. Pour l\'instant vous tenez le coup, pas de signe d\'épuisement. Mais ce type de situation finit par user si rien ne change. Agir maintenant, c\'est éviter que ça s\'aggrave.', preconisations:['Ce n\'est pas vous le problème, c\'est votre environnement. Gardez ça en tête pour ne pas trop vous en vouloir','Identifiez 1 ou 2 choses sur lesquelles vous avez vraiment la main, et concentrez vos efforts là-dessus','Parlez de votre charge à votre manager ou à un référent RH, sans attendre que ça déborde', RDV_COACHING] },
    vigilance:            { niveau:'jaune',    emoji:'🟡', titre:'Vous montrez un premier signe d\'usure',                 texte:'Votre contexte de travail n\'est pas particulièrement problématique, mais quelque chose commence à peser. Une fatigue, un peu de distance avec les autres, ou un sentiment de moins bien vous en sortir. Ce n\'est pas encore grave, mais c\'est un signal à prendre au sérieux maintenant.', preconisations:['Repérez à quel moment vous vous sentez le plus à plat, et protégez ces plages','L\'usure vient souvent de petites choses qui s\'accumulent. Identifiez ce qui vous pèse le plus en ce moment','Parlez-en à quelqu\'un de confiance : un ami, un proche, un médecin. Mettre des mots sur ce qu\'on ressent aide vraiment', RDV] },
    risque_cumule:        { niveau:'orange',   emoji:'🟠', titre:'Pression au travail et premiers signes d\'usure',        texte:'Votre charge est forte, vous avez peu d\'autonomie, et vous commencez à en ressentir les effets : fatigue qui s\'installe, distance, sentiment de moins bien vous en sortir. Les deux s\'additionnent. Ce n\'est pas encore une crise, mais le risque d\'aggravation est réel si rien ne change.', preconisations:['Ne vous dites pas "c\'est normal". C\'est peut-être courant dans votre secteur, mais ce n\'est pas normal','Essayez de réduire ce qui n\'est pas urgent dans votre agenda, même si tout semble prioritaire','Parlez-en à votre manager, votre médecin ou un référent RH. Ne portez pas ça seul(e)', RDV_ACCOMP] },
    tension_isolee:       { niveau:'orange',   emoji:'🟠', titre:'Vous êtes sous pression et peu soutenu(e)',              texte:'Votre charge est élevée, vous avez peu d\'autonomie, vous vous sentez peu soutenu(e), ni par la hiérarchie, ni par les collègues. Et en plus, quelque chose commence à se faire sentir intérieurement. L\'isolement dans ce genre de situation aggrave tout.', preconisations:['Le manque de soutien est souvent ce qui fait basculer une situation difficile en souffrance. Il est urgent de ne pas rester seul(e) avec ça','Identifiez une personne à qui vous pouvez vraiment parler de ce que vous vivez, dans ou hors du travail','La médecine du travail est confidentielle et gratuite, c\'est un interlocuteur légitime pour ce type de situation', RDV_CONFID] },
    fragilite:            { niveau:'orange',   emoji:'🟠', titre:'Vous êtes en train de vous épuiser',                    texte:'Votre contexte n\'est pas forcément le principal problème, mais vous portez quelque chose de lourd intérieurement. La fatigue est là, peut-être aussi un sentiment de distance, ou l\'impression de ne plus vraiment vous en sortir. Ce n\'est pas une faiblesse. C\'est votre corps et votre tête qui vous disent que ça suffit.', preconisations:['Prenez ce signal au sérieux. Ce n\'est pas le moment de "pousser encore un peu". C\'est le moment d\'agir','Parlez-en à votre médecin traitant. Vous n\'avez pas besoin d\'être "au fond" pour consulter','Réduisez les sollicitations non indispensables, même si tout vous semble urgent', RDV_ACCOMP] },
    epuisement_interne:   { niveau:'orange',   emoji:'🟠', titre:'Vous êtes profondément épuisé(e)',                      texte:'Les trois dimensions de l\'épuisement professionnel sont élevées chez vous : à bout physiquement et émotionnellement, distance avec les gens autour de vous, perte du sentiment d\'être efficace. C\'est sérieux, même si votre environnement n\'est pas hostile. Quelque chose s\'est consumé, et ça ne se répare pas seul.', preconisations:['Ce que vous vivez a un nom : c\'est un épuisement professionnel. Reconnaître ça n\'est pas dramatiser. C\'est voir les choses en face','Consultez votre médecin traitant rapidement. Un arrêt de travail peut permettre à votre corps de récupérer','Ne prenez pas de grande décision professionnelle dans cet état. Attendez d\'aller mieux', RDV_ACCOMP] },
    souffrance_installee: { niveau:'orange',   emoji:'🟠', titre:'Votre travail vous pèse et ça commence à se voir',      texte:'Votre charge est forte, vous avez peu de latitude, et en plus vous commencez à en ressentir les effets sur deux dimensions au moins. L\'environnement ET l\'état intérieur sont en zone de risque. Cette combinaison ne se résout pas d\'elle-même.', preconisations:['C\'est le moment d\'en parler : à votre médecin, au médecin du travail, ou à un proche. Pas dans 3 mois, maintenant','La médecine du travail est là pour vous, pas pour votre employeur. Un entretien est confidentiel et gratuit','Demandez un entretien RH pour évoquer un aménagement de poste. Ce n\'est pas une faiblesse, c\'est de la gestion', RDV_SORTIR] },
    alarme:               { niveau:'rouge',    emoji:'🔴', titre:'Votre situation est sérieuse, il faut agir',           texte:'Vous cumulez une pression forte, un manque de soutien, et des signes d\'épuisement sur plusieurs dimensions. Continuer sans rien changer risque d\'aggraver votre état de santé. Vous méritez du soutien, et il en existe.', preconisations:['Ne faites pas semblant que ça va. Parlez-en à quelqu\'un aujourd\'hui : un proche, votre médecin, un collègue de confiance','Envisagez sérieusement un arrêt de travail. Ce n\'est pas abandonner, c\'est protéger votre santé','Si vous ressentez une détresse intense, appelez le 3114, gratuit, confidentiel, disponible 24h/24', RDV_CONFID] },
    souffrance_averee:    { niveau:'rouge',    emoji:'🔴', titre:'Vous souffrez au travail, votre santé est en jeu',     texte:'Votre charge est très élevée, votre autonomie très faible, et les trois dimensions de l\'épuisement sont atteintes. Vous êtes à bout physiquement et émotionnellement. Cette situation ne se résoudra pas d\'elle-même. Vous avez besoin d\'aide, et vous avez le droit d\'en demander.', preconisations:['Consultez votre médecin traitant en urgence et parlez-lui de ce que vous vivez. Un arrêt de travail peut être nécessaire pour protéger votre santé','Contactez le médecin du travail pour un entretien confidentiel, il peut agir auprès de votre employeur sans vous exposer','Si vous traversez une détresse intense, appelez le 3114, des professionnels sont là, gratuitement, 24h/24', RDV_CONFID] },
    urgence:              { niveau:'critique', emoji:'🚨', titre:'Votre situation est critique, vous n\'êtes pas seul(e)',texte:'Vous cumulez tout : charge extrême, très peu d\'autonomie, peu de soutien, et les trois dimensions de l\'épuisement au rouge. Ce que vous vivez est sérieux. Votre santé passe avant tout le reste : votre poste, vos obligations, ce que les autres pensent. Des personnes formées peuvent vous aider maintenant.', preconisations:['Appelez le 3114 si vous ressentez une détresse intense, gratuit, confidentiel, 24h/24','Consultez votre médecin aujourd\'hui ou demain. Demandez un arrêt de travail immédiat, votre corps en a besoin','Ne prenez aucune grande décision seul(e) dans cet état : ni démissionner, ni signer quoi que ce soit', RDV_PRET] },
  };

  // ── Graphique Karasek ────────────────────────────────────────────────
  function renderKarasek(scores) {
    const ctx = $('pc-chart-karasek').getContext('2d');
    // x = demandes (0→100), y = latitude (0→100)
    // Axe Y Chart.js : 0 en bas, 100 en haut → latitude haute = haut du graphique ✓
    const x = Math.round(((scores.demandes - 9) / 27) * 100);
    const y = Math.round(((scores.latitude  - 9) / 27) * 100);

    // Quadrant actif de l'utilisateur
    const jobStrain = scores.demandes >= 22 && scores.latitude <= 21;
    const isoStrain = jobStrain && scores.soutien <= (state.hasSuperior ? 21 : 10);
    const actif     = scores.demandes >= 22 && scores.latitude > 21;
    const detendu   = scores.demandes < 22  && scores.latitude > 21;
    const passif    = scores.demandes < 22  && scores.latitude <= 21;

    const quadrantActif = isoStrain ? 'iso' : jobStrain ? 'strain' : actif ? 'actif' : detendu ? 'detendu' : 'passif';

    // Quadrants : [gauche-haut, droite-haut, gauche-bas, droite-bas]
    // = [passif, détendu, travail sous tension, actif]
    const quadrantsMeta = [
      { key:'passif',  label:'Travail passif',  sous:'Peu de charge, peu d\'autonomie', col:'rgba(249,168,37,.10)', border:'rgba(249,168,37,.3)'  },
      { key:'detendu', label:'Travail détendu', sous:'Peu de charge, bonne autonomie',  col:'rgba(46,125,50,.08)',  border:'rgba(46,125,50,.25)'   },
      { key:'strain',  label:'Travail sous tension ⚠️',   sous:'Forte charge, peu d\'autonomie', col:'rgba(198,40,40,.10)',  border:'rgba(198,40,40,.3)'   },
      { key:'actif',   label:'Travail actif',   sous:'Forte charge, bonne autonomie',  col:'rgba(232,82,26,.08)',  border:'rgba(232,82,26,.2)'   },
    ];

    // Unregister previous plugin instance to avoid stale closure bug
    try { Chart.unregister(Chart.registry.plugins.get('karasek-quadrants')); } catch(e) {}

    Chart.register({
      id: 'karasek-quadrants',
      beforeDraw(chart) {
        const { ctx: c, chartArea: { left, top, width, height } } = chart;
        // Échelle X et Y : min=-5, max=105, amplitude=110
        // Conversion valeur (0-100) → pixel : left + ((val+5)/110)*width
        const toPixX = v => left + ((v + 5) / 110) * width;
        const toPixY = v => top  + ((105 - v) / 110) * height;  // Y inversé : 0 en bas
        // Seuil demandes=22 → x_score=(22-9)/27*100=48.15
        // Seuil latitude=21 → y_score=(21-9)/27*100=44.44
        const xThresh = (22 - 9) / 27 * 100;  // 48.15
        const yThresh = (21 - 9) / 27 * 100;  // 44.44
        const mx = toPixX(xThresh);
        const my = toPixY(yThresh);
        c.save();

        // Fonds des quadrants
        // Chart.js : Y=0 en bas, Y=100 en haut → top du canvas = latitude haute
        // Haut-gauche  = demandes faibles  + latitude haute = Travail détendu
        // Haut-droite  = demandes élevées  + latitude haute = Travail actif
        // Bas-gauche   = demandes faibles  + latitude faible = Travail passif
        // Bas-droite   = demandes élevées  + latitude faible = Travail sous tension
        const quads = [
          { meta: quadrantsMeta[1], rx: left, ry: top,  rw: mx - left,       rh: my - top        },  // détendu : gauche-haut
          { meta: quadrantsMeta[3], rx: mx,   ry: top,  rw: left+width - mx, rh: my - top        },  // actif   : droite-haut
          { meta: quadrantsMeta[0], rx: left, ry: my,   rw: mx - left,       rh: top+height - my },  // passif  : gauche-bas
          { meta: quadrantsMeta[2], rx: mx,   ry: my,   rw: left+width - mx, rh: top+height - my },  // strain  : droite-bas
        ];

        quads.forEach(({ meta, rx, ry, rw, rh }) => {
          const isActif = meta.key === quadrantActif || (meta.key === 'strain' && quadrantActif === 'iso');
          c.fillStyle = isActif ? meta.col.replace(/[\d.]+\)$/, '0.18)') : meta.col;
          c.fillRect(rx, ry, rw, rh);
          if (isActif) {
            c.strokeStyle = meta.border;
            c.lineWidth = 1.5;
            c.strokeRect(rx + 1, ry + 1, rw - 2, rh - 2);
          }
        });

        // Lignes de seuil aux positions réelles
        c.strokeStyle = 'rgba(0,0,0,.12)';
        c.lineWidth = 1;
        c.setLineDash([4, 4]);
        c.beginPath(); c.moveTo(mx, top); c.lineTo(mx, top + height); c.stroke();
        c.beginPath(); c.moveTo(left, my); c.lineTo(left + width, my); c.stroke();
        c.setLineDash([]);

        // Labels des quadrants
        const labelStyle = (key) => {
          const isActif = key === quadrantActif || (key === 'strain' && quadrantActif === 'iso');
          return { alpha: isActif ? 1 : 0.55, weight: isActif ? '700' : '500' };
        };

        const drawQuadLabel = (key, label, sous, lx, ly) => {
          const { alpha, weight } = labelStyle(key);
          c.globalAlpha = alpha;
          c.fillStyle = '#1E2A3A';
          c.font = weight + ' 11px -apple-system, sans-serif';
          c.textAlign = 'left';
          c.fillText(label, lx, ly);
          c.fillStyle = '#6B7280';
          c.font = '10px -apple-system, sans-serif';
          sous.split('\n').forEach((line, i) => c.fillText(line, lx, ly + 14 + i * 13));
          c.globalAlpha = 1;
        };

        const pad = 8;
        // Haut-gauche = détendu, haut-droite = actif, bas-gauche = passif, bas-droite = strain
        drawQuadLabel('detendu', 'Travail détendu',       "Peu de charge, bonne autonomie",   left + pad, top + 14);
        drawQuadLabel('actif',   'Travail actif',         "Forte charge, bonne autonomie",    mx + pad,   top + 14);
        drawQuadLabel('passif',  'Travail passif',        'Peu de charge, peu d\'autonomie',  left + pad, my + 14);
        drawQuadLabel('strain',  'Travail sous tension',  'Forte charge, peu d\'autonomie',   mx + pad,   my + 14);

        // Annotations des axes
        c.globalAlpha = 0.6;
        c.fillStyle = '#6B7280';
        c.font = '10px -apple-system, sans-serif';
        c.textAlign = 'left';  c.fillText('← Demandes faibles',  left + 4,     top + height - 6);
        c.textAlign = 'right'; c.fillText('Demandes élevées →',  left + width, top + height - 6);
        c.globalAlpha = 1;

        c.restore();
      },
    });

    new Chart(ctx, {
      type: 'scatter',
      data: {
        datasets: [{
          label: 'Vous',
          data: [{ x, y }],
          backgroundColor: '#E8521A',
          borderColor: '#fff',
          borderWidth: 2,
          pointRadius: 11,
          pointHoverRadius: 14,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: () => {
                const q = { passif:'Travail passif', detendu:'Travail détendu', strain:'Travail sous tension', iso:'Travail sous tension + isolement', actif:'Travail actif' };
                return ' Votre zone : ' + q[quadrantActif];
              },
              afterLabel: () => ' Demandes : ' + scores.demandes + '/36  |  Latitude : ' + scores.latitude + '/36',
            },
          },
        },
        scales: {
          x: {
            min: -5, max: 105,
            title: { display: true, text: 'Demandes psychologiques', color: '#6B7280', font: { size: 11 } },
            ticks: { display: false },
            grid:  { display: false },
          },
          y: {
            min: -5, max: 105,
            title: { display: true, text: 'Latitude décisionnelle (autonomie)', color: '#6B7280', font: { size: 11 } },
            ticks: { display: false },
            grid:  { display: false },
          },
        },
      },
    });
  }

  // ── Graphique MBI ────────────────────────────────────────────────────
  function renderMBI(scores) {
    // Seuils basés sur les scores bruts (pas en %)
    const dims = [
      {
        id:    'ee',
        label: 'Épuisement émotionnel',
        desc:  "Sentiment d'être vidé(e) par le travail",
        score: scores.ee,
        max:   27,
        seuil_mod: 10,
        seuil_eleve: 18,
      },
      {
        id:    'dp',
        label: 'Détachement affectif',
        desc:  "Distance émotionnelle envers les autres",
        score: scores.dp,
        max:   15,
        seuil_mod: 4,
        seuil_eleve: 9,
      },
      {
        id:    'ap',
        label: "Manque d'accomplissement",
        desc:  "Sentiment de ne plus être efficace",
        score: scores.ap,
        max:   24,
        seuil_mod: 9,
        seuil_eleve: 16,
      },
    ];

    const couleur = (s, sm, se) => s <= sm ? '#2E7D32' : s <= se ? '#E65100' : '#C62828';
    const bgCoul  = (s, sm, se) => s <= sm ? 'rgba(46,125,50,.07)' : s <= se ? 'rgba(230,81,0,.07)' : 'rgba(198,40,40,.07)';
    const bordCoul= (s, sm, se) => s <= sm ? 'rgba(46,125,50,.25)' : s <= se ? 'rgba(230,81,0,.25)' : 'rgba(198,40,40,.25)';
    const statut  = (s, sm, se) => s <= sm ? 'Faible' : s <= se ? 'Modéré' : 'Élevé';

    const container = document.getElementById('pc-mbi-cards');
    if (!container) return;

    container.innerHTML = '';
    dims.forEach(d => {
      const pct = Math.round((d.score / d.max) * 100);
      const col  = couleur(d.score, d.seuil_mod, d.seuil_eleve);
      const bg   = bgCoul(d.score, d.seuil_mod, d.seuil_eleve);
      const bord = bordCoul(d.score, d.seuil_mod, d.seuil_eleve);
      const st   = statut(d.score, d.seuil_mod, d.seuil_eleve);

      const card = document.createElement('div');
      card.style.cssText = 'background:' + bg + ';border:0.5px solid ' + bord + ';border-radius:12px;padding:16px;';
      card.innerHTML =
        '<p style="font-size:13px;font-weight:500;color:#1E2A3A;margin:0 0 4px;line-height:1.4;">' + d.label + '</p>' +
        '<p style="font-size:11px;color:#6B7280;margin:0 0 14px;line-height:1.4;">' + d.desc + '</p>' +
        '<div style="background:rgba(0,0,0,.08);border-radius:999px;height:6px;margin-bottom:10px;overflow:hidden;">' +
          '<div style="background:' + col + ';width:' + pct + '%;height:6px;border-radius:999px;"></div>' +
        '</div>' +
        '<div style="display:flex;justify-content:space-between;align-items:center;">' +
          '<span style="font-size:20px;font-weight:500;color:' + col + ';">' + d.score + '<span style="font-size:12px;color:#6B7280;font-weight:400;"> / ' + d.max + '</span></span>' +
          '<span style="font-size:12px;font-weight:500;color:' + col + ';background:' + bg.replace('.07)', '.15)') + ';border:0.5px solid ' + bord + ';padding:3px 10px;border-radius:999px;">' + st + '</span>' +
        '</div>';
      container.appendChild(card);
    });
  }

})();
