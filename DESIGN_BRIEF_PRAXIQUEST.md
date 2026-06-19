# Design Brief Maître — PraxiQuest

## 1. Vision & Concept

PraxiQuest est un SaaS d'orientation professionnelle qui emprunte la structure narrative du RPG sans en adopter l'esthétique. La métaphore est architecturale, pas cosmétique : l'utilisateur vit une quête réelle — se découvrir — et l'interface orchestre cette expérience en trois actes distincts.

**Arc émotionnel :**
- **Acte I — Seuil** (onboarding) : tension douce, promesse non tenue, curiosité activée
- **Acte II — Flow** (tests/épreuves) : concentration maximale, interface effacée
- **Acte III — Révélation** (résultats) : cinématique, émotionnel, mémorable

**Règle des deux pièges :** jamais "gaming cheap" (pixel art, gamification lourde), jamais "corporate générique" (SaaS bleu passe-partout). Le résultat doit être dark, élégant, légèrement mystérieux.

**Tagline :** *"Deviens le personnage que tu n'avais pas encore nommé"*
**Sous-titre :** *"PraxiQuest révèle qui tu es, pour que tu choisisses où tu vas."*

---

## 2. Identité Visuelle

### Palette complète — Inspirée Assassin's Creed

> **Concept** : L'Animus d'AC = plonger dans sa mémoire génétique. PraxiQuest = plonger en soi pour trouver sa voie. La même architecture émotionnelle, le même vertige de la révélation.

| Rôle | Nom | Hex | Référence AC | Usage |
|------|-----|-----|-------------|-------|
| Fond base | Parchemin | `#F0E8D4` | Pages du Codex d'Altaïr, archives de la Fraternité | Background global |
| Fond surface | Vélin Ancien | `#E5DAC2` | Intérieurs de la Florence d'AC2 | Cards, panels |
| Fond élevé | Pierre Chaude | `#D8CEB5` | Murs de pierre de l'Italie Renaissance | Modales, dropdowns |
| Primaire | Or de la Fraternité | `#A67520` | Insigne et ornements de la Confrérie | CTA, actions principales |
| Primaire sombre | Or Brûlé | `#7D5510` | Or patiné, médailles anciennes | États hover, gradients |
| Secondaire | Cramoisi | `#7B1515` | La lame secrète, le danger, le sacrifice | Éléments secondaires, alertes douces |
| Accent | Encre Ancienne | `#1C1408` | Écriture à l'encre des manuscrits | Texte fort, titres |
| Succès | Vert Eagle Vision | `#3A6B48` | Eagle Vision — les alliés en vert | Matching métiers, validations |
| Danger | Rouge Sang | `#B03020` | Blessure critique, trahison | Erreurs, alertes critiques |
| Signal | Bleu Animus | `#0A7FA0` | Interface digitale de l'Animus | Highlights IA, éléments rares |
| Texte principal | Encre | `#2A1E08` | Manuscrits de la Fraternité | Corps de texte |
| Texte secondaire | Encre Pâlie | `#6B5A3E` | Texte ancien, effacé par le temps | Labels, métadonnées |

### Typographie

| Famille | Variable CSS | Usage |
|---------|-------------|-------|
| Space Grotesk | `--font-display` | H1, H2, H3, titres écrans |
| Inter | `--font-body` | Corps, UI, labels |
| Space Mono | `--font-data` | Scores, stats, terminal, XP |

Tailles : `--text-xs: 12px` / `--text-sm: 14px` / `--text-base: 16px` / `--text-lg: 18px` / `--text-xl: 24px` / `--text-2xl: 32px` / `--text-3xl: 48px`

### Logo

**Concept :** Compas dans l'Hexagone (navigation intérieure + structure)

**4 versions requises :**
1. `logo-dark.svg` — fond sombre, violet + blanc
2. `logo-light.svg` — fond clair, violet foncé
3. `logo-mono.svg` — monochrome, une couleur
4. `favicon.svg` — icône seule, 32×32

---

## 3. Design System

### Tokens CSS (variables globales)

```css
:root {
  /* Backgrounds — Parchemin et pierre chaude */
  --bg-base: #F0E8D4;
  --bg-surface: #E5DAC2;
  --bg-elevated: #D8CEB5;

  /* Colors — Palette Assassin's Creed (thème clair) */
  --color-primary: #A67520;       /* Or de la Fraternité */
  --color-primary-dark: #7D5510;  /* Or brûlé */
  --color-secondary: #7B1515;     /* Cramoisi / lame secrète */
  --color-accent: #1C1408;        /* Encre ancienne */
  --color-success: #3A6B48;       /* Vert Eagle Vision */
  --color-danger: #B03020;        /* Rouge sang */
  --color-signal: #0A7FA0;        /* Bleu Animus */

  /* Text — Encre et manuscrit */
  --text-primary: #2A1E08;
  --text-secondary: #6B5A3E;

  /* Fonts */
  --font-display: 'Space Grotesk', sans-serif;
  --font-body: 'Inter', sans-serif;
  --font-data: 'Space Mono', monospace;

  /* Effects — Ombres chaudes, pas de lueurs (thème clair) */
  --shadow-primary: 0 4px 20px rgba(166, 117, 32, 0.25);
  --shadow-card: 0 2px 12px rgba(42, 30, 8, 0.1);
  --shadow-elevated: 0 8px 32px rgba(42, 30, 8, 0.15);
  --glass-bg: rgba(240, 232, 212, 0.85);
  --glass-blur: blur(14px);
  --glass-border: rgba(166, 117, 32, 0.25);  /* bordure or subtile */

  /* Radius */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;

  /* Spacing scale : 4px base */
  --space-1: 4px; --space-2: 8px; --space-3: 12px;
  --space-4: 16px; --space-6: 24px; --space-8: 32px;
  --space-12: 48px; --space-16: 64px;
}
```

### Composants clés

**Boutons** — 4 variants × 3 tailles (sm/md/lg)
- `btn-primary` : gradient `#C9912B → #A67520`, glow or au hover (`--glow-primary`), `transition: filter 200ms`
- `btn-secondary` : bordure `--color-primary` (or), bg transparent, glow subtle
- `btn-ghost` : texte seul, underline au hover
- `btn-danger` : bg `--color-danger`, pour actions destructives

**Cards tests (Armurerie)**
- Illustration géométrique 80×80px (SVG inline)
- Titre (Space Grotesk 18px), durée + difficulté (Space Mono 12px gris)
- Tag "Recommandé par l'IA" (badge bleu Animus)
- Hover : `transform: translateY(-4px)` + `filter: var(--glow-primary)` (aura or)
- `transition: transform 250ms ease, filter 250ms ease`

**Cards métiers (Voies Possibles)**
- Barre de matching : gradient vert Eagle Vision → or Fraternité, largeur = % de correspondance
- Icône métier avec glow or (`--glow-primary`)
- Hover : expansion légère + révélation de 2 lignes de description

**XP Bar**
- Style RPG, fond `--bg-elevated`, remplissage gradient `primary → secondary`
- Shimmer animé : pseudo-element blanc, `animation: shimmer 2s infinite`
- Transition remplissage : `width 1.2s cubic-bezier(0.34, 1.56, 0.64, 1)` (spring)

**Badges (Emblèmes)**
- Locked : icône grise, `opacity: 0.4`, bordure pointillée
- Unlocked : or Fraternité avec pulse `animation: pulse-gold 2s ease infinite`, bordure `--color-primary`
- Nouveau : ring-pulse visible 5 secondes puis s'estompe

**Toast récompense**
- Slide-in depuis le bas, `animation: slideUp 400ms ease-out`
- Icône avec ring-pulse, texte "Emblème débloqué : [Nom]"
- Auto-dismiss 4 secondes

**Navigation**
- Header : glassmorphism (`backdrop-filter: var(--glass-blur)`, `background: var(--glass-bg)`)
- Sidebar : section labels en Space Mono 11px uppercase gris, items avec glow au active

---

## 4. Architecture & Parcours

### Arborescence

```
/ (Landing)
├── /a-propos
├── /fonctionnalites
├── /tarifs
├── /blog
└── /contact

/auth/
├── /inscription
├── /connexion
└── /mot-de-passe-oublie

/app/ (espace utilisateur)
├── /dashboard          → "La Salle des Quêtes"
├── /profil             → "L'Identité du Héros"
├── /epreuves           → "L'Armurerie" (tests disponibles)
├── /epreuves/:id       → test en cours
├── /grimoire           → "Le Grimoire" (résultats)
├── /emblemes           → badges
└── /forge              → paramètres

/admin/
├── /utilisateurs
├── /convocations       → invitations groupées
├── /rapports
└── /plugins
```

### Parcours principal "La Quête" — 9 étapes

| # | Étape | Écran | Déclencheur | Objectif UX |
|---|-------|-------|-------------|-------------|
| 1 | Landing | `/` | Arrivée | Hero fort + Zeigarnik (progress bar partielle visible) |
| 2 | Inscription | `/auth/inscription` | CTA "Commencer" | Social auth + email, friction minimale |
| 3 | Onboarding | 3 micro-étapes | Post-inscription | Statut pro + ancienneté + CV (optionnel V1) |
| 4 | Sélection épreuve | `/epreuves` | Dashboard vide | Cards avec temps + niveau + tag IA |
| 5 | Passage épreuve | `/epreuves/:id` | Clic "Commencer" | Mode concentration : header réduit, fond sombre, XP visible |
| 6 | Fin d'épreuve | Écran transition | Dernière question | XP gagné + badge si applicable + "L'oracle analyse..." |
| 7 | Résultats | `/grimoire` | Fin d'analyse | Synthèse IA narrative + 15 Voies Possibles en stagger |
| 8 | Exploration métiers | Fiche expandable | Clic sur une Voie | Détail métier + liens formations + save/favoris |
| 9 | Re-engagement | Notification | J+1 inactif | "Il te reste X épreuves pour compléter ton profil" |

**Risques UX et parades :**
- Synthèse IA générique → personnalisation forte, réutiliser les termes exacts du CV uploadé
- Abandon à l'upload CV → optionnel en V1, fortement encouragé mais jamais bloquant
- Surcharge gamification → XP et badges discrets, jamais bloquants sur le parcours de lecture

---

## 5. Voix de Marque & Glossaire

**Ton :** Poétique sans être obscur. Direct sans être froid. Bienveillant sans être infantilisant. Épique sans être grandiloquent. **Tutoiement systématique.**

### Glossaire officiel

| Terme générique | Terme PraxiQuest |
|----------------|-----------------|
| Test | Épreuve |
| Résultats | Révélation |
| Tableau de bord | Salle des Quêtes |
| Profil | Identité du Héros |
| Score | Éclat |
| Progression | Niveau |
| Badge | Emblème |
| Connexion | Entrer dans la Quête |
| Déconnexion | Quitter la Quête |
| Invitations | Convocations |
| Rapport IA | Grimoire de Synthèse |
| Métiers suggérés | Voies Possibles |
| Paramètres | Forge des Réglages |
| Disponible | En attente de toi |

### Microcopy clé

| Contexte | Texte |
|----------|-------|
| Bienvenue | *"Ta quête commence ici. Ce que tu vas découvrir, aucun test ne te l'a jamais montré de cette façon."* |
| Début d'une épreuve | *"Réponds sans te censurer. Il n'y a pas de bonnes réponses — il y a les tiennes."* |
| Chargement IA | *"L'oracle analyse ton parcours... quelques instants."* |
| Badge débloqué | *"Emblème débloqué : [Nom]. Tu viens de révéler une facette de toi que peu de gens connaissent."* |
| Timeout session | *"Tu t'es éloigné·e un moment. Ta quête t'attend là où tu l'as laissée."* |
| Dashboard vide | *"Ta salle des quêtes est encore silencieuse. La première épreuve changera ça."* |
| Upload CV | *"Partage ton parcours pour que l'oracle parle en connaissance de cause."* |
| Erreur générique | *"Quelque chose a résisté. Essaie à nouveau — les quêtes ne s'arrêtent pas sur une embûche."* |

---

## 6. Motion Design

### Stack technique

| Outil | Usage |
|-------|-------|
| Vue `<Transition>` + CSS | 80% des animations courantes |
| GSAP | Séquences temporelles complexes, stagger |
| Canvas 2D natif | Particules (pas de WebGL) |
| tsParticles | Effets ambient légers |

### Cinq principes

1. Chaque transition raconte quelque chose — pas d'animation décorative sans sens
2. La progression est toujours visible — XP bar, étapes, position dans la quête
3. La récompense s'anime — jamais silencieuse
4. Mobile first — `prefers-reduced-motion` respecté sur toutes les animations
5. Jamais d'animation sur le chemin critique de lecture (texte, CTAs)

### Transitions standards

| Transition | Durée | Easing | Détails |
|-----------|-------|--------|---------|
| Entre pages | 600ms | `cubic-bezier(0.4, 0, 0.2, 1)` | slide + fade + scale(0.98→1) |
| Entre questions | 300ms | `ease-out` | slide horizontal (comme pages d'un livre) |
| Fin test → Résultats | 1200ms | Séquence GSAP | Voir storyboard |
| Hover cards | 250ms | `ease` | translateY(-4px) + filter glow |
| Toast récompense | 400ms | `ease-out` | slideUp depuis le bas |
| XP bar fill | 1200ms | `cubic-bezier(0.34, 1.56, 0.64, 1)` | Spring effect |

### Storyboard résultats (moment signature)

```
0ms      → Fond noir pur (transition depuis écran de fin)
400ms    → Particules lumineuses convergent vers le centre (Canvas 2D)
800ms    → Logo PraxiQuest pulse doucement (scale 1→1.05→1, 400ms)
1200ms   → "Ton Grimoire se révèle" — fade in (opacity 0→1, 300ms)
1800ms   → Synthèse IA — typewriter 40ms/caractère, ligne par ligne
3200ms   → "Tes 15 Voies Possibles" — title slide in
3300ms   → Cards métiers en stagger : 80ms entre chaque, translateY(20px)→0 + fade
           (15 cards × 80ms = 1200ms total pour compléter)
5600ms   → CTA principal pulse doucement (ring-pulse 1.5s ease infinite)
```

**Règle performance critique :** Ne jamais animer `box-shadow` directement. Toujours utiliser `filter: drop-shadow()` ou un pseudo-element `::after` avec `opacity`. Gain ×10 sur mobile.

---

## 7. Neuromarketing — Leviers psychologiques

> Rapport produit par l'expert neuromarketing. Chaque recommandation est directement implémentable.

### Diagnostic des leviers existants

| Levier | Mécanisme | Score | Pourquoi ça fonctionne |
|--------|-----------|-------|----------------------|
| Effet Zeigarnik | Barre partielle dès la landing | 8/10 | Le cerveau ne peut pas "fermer" une tâche incomplète — tension cognitive constante |
| Gamification XP | Gratification immédiate post-épreuve | 6/10 | Dopamine à chaque épreuve terminée — mais manque de charge identitaire |
| Narration RPG | Vocabulaire quête, héros, oracle | 7/10 | Active le système narratif du cerveau — l'utilisateur se projette dans un récit |
| Anticipation IA | "L'oracle analyse..." | 7/10 | Suspend le jugement, crée un espace de curiosité avant la révélation |

La gamification est à 6/10 non parce qu'elle est mauvaise, mais parce que les points seuls ne suffisent pas : il faut des emblèmes qui **racontent quelque chose sur l'identité de l'utilisateur**.

### Leviers manquants — 5 priorités

**1. Ancrage identitaire précoce** *(Dès l'inscription — impact fort)*
À l'étape d'inscription, proposer le choix d'un "titre de quête" parmi 3 options :
- *L'Architecte* — je construis, je structure
- *L'Explorateur* — je cherche, je découvre
- *Le Passeur* — j'accompagne, je transmets

Aucun impact fonctionnel, mais l'utilisateur s'approprie une identité dès la première minute. Mécanisme : **théorie de l'auto-affirmation** (Steele, 1988) — une identité choisie génère un engagement comportemental durable. Le titre apparaît ensuite dans tous les contextes (badges, emails, dashboard).

**2. Réduction de friction CV — fallback 3 champs** *(Onboarding — impact fort)*
Remplacer l'upload CV obligatoire par : upload optionnel + fallback alternatif si refus :
- Secteur d'activité actuel
- Compétence dont tu es le plus fier·e
- Ce que tu ferais si tu recommençais

Mécanisme : **principe de réciprocité** (Cialdini) — l'utilisateur qui donne quelque chose de soi reçoit en retour une synthèse perçue comme plus personnelle. Le fallback réduit l'abandon sans sacrifier la personnalisation.

**3. Pic de suspension avant la révélation** *(Écran de transition post-épreuve — impact fort)*
Les 5,6s de cinématique ne doivent pas être un écran de chargement déguisé. C'est un **moment de suspension émotionnelle consciente**. Ajouter une phrase variable selon le profil pendant l'analyse :
- *"Quelque chose d'inattendu se dessine dans ton parcours."*
- *"L'oracle voit une cohérence que tu n'as peut-être pas encore nommée."*

Mécanisme : **anticipatory pleasure** (Berns, 2001) — l'anticipation d'une récompense active plus de dopamine que la récompense elle-même.

**4. Aucun CTA commercial pendant 90 secondes après la révélation** *(Page résultats — impact fort)*
La révélation des 15 Voies Possibles est le moment émotionnellement le plus intense. Toute interruption commerciale à cet instant détruit l'expérience. Le premier CTA actionnable (prise de RDV, formation, partage) ne doit apparaître qu'après un temps de digestion.

Mécanisme : **pic-fin** (Kahneman) — la mémoire d'une expérience est dictée par son pic émotionnel et sa fin. Polluer le pic avec du commercial effondre la mémorabilité positive.

**5. Carte d'explorateur partageable** *(Post-résultats — impact moyen/fort sur acquisition)*
Générer automatiquement une image de partage personnalisée : titre de quête, niveau atteint, 3 mots-clés extraits de la synthèse IA, design parchemin/AC. Partage en un clic sur LinkedIn/WhatsApp.

Mécanisme : **identité sociale** (Tajfel) — partager une "révélation sur soi" est un acte d'affirmation identitaire naturel. C'est le levier de viralité organique le plus puissant sur ce type de produit.

### Arc émotionnel optimal

| Étape | Émotion cible | Levier | Élément concret |
|-------|--------------|--------|----------------|
| Landing | Curiosité intriguée | Zeigarnik + mystère | Barre partielle + titre énigmatique |
| Inscription | Appartenance | Ancrage identitaire | Choix du titre de quête |
| Onboarding CV | Confiance + réciprocité | Fallback 3 champs | "Plus tu partages, plus l'oracle voit juste" |
| Sélection épreuve | Désir d'explorer | Curiosity gap | Tag "Ce que cette épreuve révèle : [mystère]" |
| Pendant l'épreuve | Flow total | Réduction des distractions | Header réduit, fond sombre, aucune sortie visible |
| Fin d'épreuve | Soulagement + anticipation | Pic de suspension | Phrase variable + "L'oracle analyse..." |
| Révélation résultats | Émerveillement + reconnaissance | Cinématique + personnalisation | 5,6s + termes du CV réutilisés |
| Exploration métiers | Désir d'action | Preuve sociale + réciprocité | "X personnes avec ton profil ont choisi cette voie" |
| Re-engagement J+1 | Aversion à la perte | Loss aversion | Email avec temps investi dynamique |

### Microcopy reformulé — Avant/Après

| Contexte | Avant | Après | Mécanisme |
|----------|-------|-------|-----------|
| Début d'épreuve | *"Réponds sans te censurer. Il n'y a pas de bonnes réponses — il y a les tiennes."* | *"Cette épreuve ne te note pas. Elle révèle ce que tu sais déjà de toi-même sans l'avoir encore mis en mots."* | Recadrage : de "test" à "révélation" — réduit l'anxiété d'évaluation |
| Bienvenue | *"Ta quête commence ici."* | *"Tu viens de faire quelque chose que la plupart des gens remettent à demain."* | Validation comportementale — ancre positivement l'acte d'inscription |
| Chargement IA | *"L'oracle analyse ton parcours... quelques instants."* | *"Quelque chose d'inattendu se dessine. L'oracle en a pour quelques instants."* | Anticipatory pleasure — promet une surprise, crée du désir |
| Dashboard vide | *"Ta salle des quêtes est encore silencieuse."* | *"Tu as déjà fait le plus dur : tu es là. La première épreuve prend 8 minutes."* | Réduction de la friction par ancrage temporel précis |
| Email relance J+1 | Objet : *"Ta quête t'attend"* | Objet : *"Tu as investi [X min] — il manque une chose"* + Preview : *"La synthèse ne peut pas se générer sans cette dernière étape."* | Loss aversion + biais de dotation + proximité du but |

---

## 8. Checklist de Démarrage

Les 10 premières actions pour lancer le développement, dans l'ordre (intègre les priorités neuromarketing) :

1. **Setup Vite + Vue 3 + TypeScript + Tailwind** — configurer `tailwind.config.js` avec les tokens (couleurs custom, fonts, espacement)
2. **Injecter les variables CSS** dans `src/assets/design-tokens.css`, importer dans `main.ts`
3. **Installer les fonts** — Space Grotesk, Inter, Space Mono via Google Fonts ou self-hosted (performance)
4. **Créer les 4 fichiers logo SVG** selon le brief logo, les placer dans `src/assets/logo/`
5. **Implémenter le layout de base** — `AppLayout.vue` avec header glassmorphism + sidebar + slot `<router-view>`
6. **Créer les composants atomiques** dans cet ordre : `Button.vue` (4 variants), `Card.vue`, `Badge.vue`, `XPBar.vue`
7. **Configurer Vue Router** avec les routes front + auth + app + admin, guards d'authentification
8. **Wirer les transitions globales** — `<router-view v-slot="{ Component }"><transition name="page">` dans `App.vue`
9. **Page Landing** — hero section avec tagline, sous-titre, CTA, progress bar Zeigarnik partielle visible
10. **Page Salle des Quêtes (Dashboard)** — état vide avec microcopy officiel, puis état peuplé avec cards épreuves

---

*Document produit par l'équipe design PraxiQuest. Version 1.0 — Juin 2026.*
*Maintenir ce brief à jour à chaque itération majeure.*
