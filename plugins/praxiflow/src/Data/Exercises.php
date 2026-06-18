<?php

namespace Praxis\Plugins\PraxiFlow\Data;

/**
 * Catalogue des exercices PraxiFlow — Gestion du temps & productivité.
 *
 * Fondements scientifiques mobilisés :
 *  - Technique Pomodoro (Francesco Cirillo, 1980s)
 *  - Matrice Eisenhower (Dwight D. Eisenhower / Stephen Covey)
 *  - Time-blocking & Deep Work (Cal Newport, 2016)
 *  - Règle des 2 minutes & Revue hebdomadaire GTD (David Allen, 2001)
 *  - MIT — Most Important Tasks (Leo Babauta, Zen to Done)
 *  - Neuropsychologie de la procrastination (Pychyl & Sirois, 2016 ; Steel, 2007)
 *  - Task-batching & réduction du coût cognitif de commutation (Rubinstein et al., 2001)
 *  - Gestion de l'énergie vs gestion du temps (Loehr & Schwartz, 2003)
 *  - Chunking des grandes tâches (Miller, 1956 ; Gobet et al., 2001)
 */
class Exercises
{
    /** Retourne les 5 dimensions avec métadonnées. */
    public static function dimensions(): array
    {
        return [
            'planification' => [
                'label'       => 'Planification',
                'description' => 'Capacité à organiser, anticiper et structurer son temps.',
                'icon'        => 'calendar',
                'color'       => '#4F46E5',
                'questions'   => [0, 1, 2, 3],
            ],
            'focus' => [
                'label'       => 'Focus',
                'description' => 'Concentration soutenue et résistance aux distractions.',
                'icon'        => 'target',
                'color'       => '#0891B2',
                'questions'   => [4, 5, 6, 7],
            ],
            'gestion_priorites' => [
                'label'       => 'Gestion des priorités',
                'description' => "Identification et traitement des tâches à haute valeur ajoutée.",
                'icon'        => 'list-checks',
                'color'       => '#D97706',
                'questions'   => [8, 9, 10, 11],
            ],
            'gestion_energie' => [
                'label'       => "Gestion de l'énergie",
                'description' => "Alignement des tâches avec les niveaux d'énergie naturels.",
                'icon'        => 'zap',
                'color'       => '#16A34A',
                'questions'   => [12, 13, 14, 15],
            ],
            'lutte_procrastination' => [
                'label'       => 'Lutte contre la procrastination',
                'description' => 'Stratégies cognitives et comportementales anti-procrastination.',
                'icon'        => 'rocket',
                'color'       => '#DC2626',
                'questions'   => [16, 17, 18, 19],
            ],
        ];
    }

    /** Retourne les 20 questions Likert (idx 0-19). */
    public static function questions(): array
    {
        return [
            // Dimension : planification (idx 0-3)
            ['idx' => 0,  'dim' => 'planification',          'text' => "Je planifie mes journées à l'avance en listant les tâches à accomplir."],
            ['idx' => 1,  'dim' => 'planification',          'text' => "Je dédie du temps chaque semaine pour faire le bilan et organiser la semaine suivante."],
            ['idx' => 2,  'dim' => 'planification',          'text' => "Je bloque des créneaux dans mon agenda pour les tâches importantes avant que l'urgence s'impose."],
            ['idx' => 3,  'dim' => 'planification',          'text' => "Mes estimations de durée pour les tâches sont généralement fiables."],
            // Dimension : focus (idx 4-7)
            ['idx' => 4,  'dim' => 'focus',                  'text' => "Je parviens à travailler sans interrompre ma concentration pendant au moins 25 minutes d'affilée."],
            ['idx' => 5,  'dim' => 'focus',                  'text' => "Je coupe les notifications (téléphone, mails) lors de mes plages de travail profond."],
            ['idx' => 6,  'dim' => 'focus',                  'text' => "Je reprends facilement le fil d'une tâche complexe après une interruption."],
            ['idx' => 7,  'dim' => 'focus',                  'text' => "Je peux travailler sur une seule tâche à la fois sans me laisser distraire par d'autres idées."],
            // Dimension : gestion des priorités (idx 8-11)
            ['idx' => 8,  'dim' => 'gestion_priorites',      'text' => "Je distingue clairement les tâches urgentes des tâches importantes avant de commencer ma journée."],
            ['idx' => 9,  'dim' => 'gestion_priorites',      'text' => "Je commence ma journée par les tâches à forte valeur ajoutée plutôt que par les emails ou les urgences."],
            ['idx' => 10, 'dim' => 'gestion_priorites',      'text' => "Je sais dire non ou déléguer lorsqu'une demande ne correspond pas à mes priorités du moment."],
            ['idx' => 11, 'dim' => 'gestion_priorites',      'text' => "Je prends moins de 2 minutes pour traiter les petites tâches immédiatement plutôt que de les noter."],
            // Dimension : gestion de l'énergie (idx 12-15)
            ['idx' => 12, 'dim' => 'gestion_energie',        'text' => "Je connais mes pics d'énergie naturels dans la journée et j'y programme mes tâches les plus exigeantes."],
            ['idx' => 13, 'dim' => 'gestion_energie',        'text' => "Je prends des pauses régulières (toutes les 60-90 min) pour maintenir ma concentration."],
            ['idx' => 14, 'dim' => 'gestion_energie',        'text' => "Je n'enchaîne pas les réunions ou les tâches cognitives lourdes sans récupération entre elles."],
            ['idx' => 15, 'dim' => 'gestion_energie',        'text' => "Mon niveau d'énergie en fin de journée me permet encore d'accomplir des tâches importantes."],
            // Dimension : lutte contre la procrastination (idx 16-19)
            ['idx' => 16, 'dim' => 'lutte_procrastination',  'text' => "Je commence mes tâches difficiles sans attendre d'être « en forme » ou « inspiré(e) »."],
            ['idx' => 17, 'dim' => 'lutte_procrastination',  'text' => "Quand une tâche me semble trop grande, je la découpe en étapes concrètes de moins de 30 minutes."],
            ['idx' => 18, 'dim' => 'lutte_procrastination',  'text' => "Je reconnais mes stratégies d'évitement (réseaux sociaux, emails...) et je les interromps consciemment."],
            ['idx' => 19, 'dim' => 'lutte_procrastination',  'text' => "Je tiens mes engagements envers moi-même : si je planifie une tâche, je la fais."],
        ];
    }

    /**
     * Retourne les 20 exercices guidés, associés à leurs dimensions.
     * Chaque exercice : id, title, category, duration_minutes, difficulty (1-3),
     *                   scientific_basis, instructions (étapes), scoring (dimension cible).
     */
    public static function exercises(): array
    {
        return [

            // ═══════════════════════════════════════════════════════════
            //  PLANIFICATION
            // ═══════════════════════════════════════════════════════════
            [
                'id'               => 'pomodoro-decouverte',
                'title'            => "Découverte Pomodoro — ton premier cycle 25/5",
                'category'         => 'planification',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => "La technique Pomodoro (Francesco Cirillo, 1980s) exploite les cycles ultradiens du cerveau (90 min) en découpant le travail en intervalles de 25 minutes suivis de 5 minutes de repos. Des études en neuroimagerie (Dehaene, 2014) montrent que ces micro-pauses consolident les apprentissages et restaurent la capacité attentionnelle du cortex préfrontal.",
                'instructions'     => [
                    "Choisissez UNE seule tâche à accomplir. Écrivez-la sur une feuille : « Je vais faire : ___. »",
                    "Réglez un minuteur sur 25 minutes. Pas d'application multitâche — juste votre tâche et le minuteur.",
                    "Travaillez jusqu'à la sonnerie. Si une pensée parasite surgit, notez-la en marge sans agir dessus.",
                    "À la sonnerie, cochez une barre sur votre feuille. Levez-vous, stirez-vous 5 minutes.",
                    "Après 4 Pomodoros, accordez-vous une pause longue de 20-30 minutes. Notez ce que vous avez produit.",
                    "Réflexion : combien de tâches avez-vous réussi à terminer ? Qu'est-ce qui vous a interrompu ?",
                ],
                'scoring'          => ['dimension' => 'planification', 'weight' => 1.0],
            ],

            [
                'id'               => 'planning-mit-matinal',
                'title'            => "Planning MIT — les 3 tâches qui comptent vraiment",
                'category'         => 'planification',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => "La méthode MIT (Most Important Tasks, Leo Babauta / Zen to Done) s'appuie sur la recherche en psychologie de l'accomplissement (Locke & Latham, 1990) : les objectifs spécifiques et limités en nombre augmentent significativement la performance. Limiter à 3 tâches prioritaires évite la surcharge décisionnelle (Schwartz, The Paradox of Choice, 2004).",
                'instructions'     => [
                    "Prenez une feuille vierge et écrivez la date d'aujourd'hui.",
                    "Demandez-vous : « Si je ne pouvais accomplir QUE 3 choses aujourd'hui, lesquelles auraient le plus d'impact ? »",
                    "Écrivez ces 3 MIT en haut de la feuille, dans l'ordre d'importance décroissante.",
                    "Sous chaque MIT, notez la PREMIÈRE action concrète (< 15 min) à réaliser pour la démarrer.",
                    "Le lendemain matin, avant d'ouvrir vos emails, attaquez votre MIT n°1 pendant au moins 25 minutes.",
                    "Bilan du soir : combien de MIT avez-vous accomplies ? Qu'est-ce qui vous en a empêché ?",
                ],
                'scoring'          => ['dimension' => 'planification', 'weight' => 1.2],
            ],

            [
                'id'               => 'time-blocking-semaine',
                'title'            => "Time-blocking — construis ton agenda idéal",
                'category'         => 'planification',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => "Le time-blocking (Cal Newport, Deep Work, 2016) consiste à affecter chaque heure de la journée à une tâche spécifique. Une étude de l'Université de Californie (Mark et al., 2008) montre qu'il faut en moyenne 23 minutes pour retrouver sa concentration après une interruption. Planifier par blocs réduit les transitions et protège le travail cognitif profond.",
                'instructions'     => [
                    "Ouvrez votre agenda (papier ou numérique) pour la semaine prochaine.",
                    "Identifiez vos 3-5 créneaux de haute énergie dans la journée (souvent 9h-12h pour les chronotypes matinaux).",
                    "Bloquez ces créneaux en premier pour vos tâches à forte concentration (production, analyse, création).",
                    "Ensuite seulement, positionnez les réunions, emails et tâches routinières dans les créneaux restants.",
                    "Ajoutez 20% de marge sur chaque bloc estimé (« buffer de réalité »).",
                    "Respectez le planning une journée entière. Le soir, notez les écarts et leur cause.",
                ],
                'scoring'          => ['dimension' => 'planification', 'weight' => 1.3],
            ],

            [
                'id'               => 'revue-hebdomadaire-gtd',
                'title'            => "Revue hebdomadaire GTD — vide ton cerveau",
                'category'         => 'planification',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => "La revue hebdomadaire est le pivot du système GTD (Getting Things Done, David Allen, 2001). Elle repose sur la théorie de la charge cognitive (Sweller, 1988) : le cerveau dépense de l'énergie à maintenir les tâches non terminées en mémoire de travail (effet Zeigarnik, 1927). Externaliser et traiter régulièrement sa liste libère le cortex préfrontal pour la réflexion créative.",
                'instructions'     => [
                    "Réservez 30 minutes chaque vendredi soir ou dimanche matin. Même créneau chaque semaine.",
                    "COLLECTER : videz tous vos « inboxes » (emails, notes, post-its, messageries) dans une liste unique.",
                    "TRAITER : pour chaque item, décidez : Supprimer / Déléguer / Faire maintenant (< 2 min) / Planifier.",
                    "PLANIFIER : positionnez dans votre agenda les tâches qui ont une date ou une durée.",
                    "REVOIR : regardez vos projets en cours. Y a-t-il une prochaine action définie pour chacun ?",
                    "CLORE : votre liste est vide ou planifiée. Notez 3 victoires de la semaine avant de fermer.",
                ],
                'scoring'          => ['dimension' => 'planification', 'weight' => 1.2],
            ],

            // ═══════════════════════════════════════════════════════════
            //  FOCUS
            // ═══════════════════════════════════════════════════════════
            [
                'id'               => 'deep-work-session',
                'title'            => "Session Deep Work — 50 minutes sans interruption",
                'category'         => 'focus',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => "Le « deep work » (Cal Newport, 2016) désigne les activités cognitives effectuées en état de concentration totale, sans distraction. La recherche en neurosciences montre que le réseau du mode par défaut (DMN) et le réseau attentionnel (DAN) sont mutuellement inhibiteurs : toute distraction déclenche le DMN et coûte entre 10 et 23 minutes de refocalisation (Gloria Mark, UC Irvine).",
                'instructions'     => [
                    "Avant de commencer : notez sur papier la tâche exacte que vous allez accomplir et le livrable attendu.",
                    "Fermez tous les onglets non nécessaires. Mettez votre téléphone en mode avion ou dans une autre pièce.",
                    "Réglez un minuteur sur 50 minutes. Posez-le hors de votre champ de vision.",
                    "Si une pensée intrusive arrive, notez-la en 5 mots sur un Post-it sans développer. Reprenez la tâche.",
                    "À la sonnerie, évaluez : 0 à 10, quel était votre niveau de concentration ? Qu'est-ce qui vous a distrait ?",
                    "Notez vos distracteurs récurrents. La semaine prochaine, pré-anticipez-les avant chaque session.",
                ],
                'scoring'          => ['dimension' => 'focus', 'weight' => 1.3],
            ],

            [
                'id'               => 'detox-notifications',
                'title'            => "Détox notifications — audit & protocole digital",
                'category'         => 'focus',
                'duration_minutes' => 4,
                'difficulty'       => 1,
                'scientific_basis' => "Chaque notification déclenche une micro-libération de dopamine (Schultz, 1997) qui interrompt le réseau attentionnel dorsal. Une étude de l'Université de Floride (Stothart et al., 2015) montre que la seule présence d'un smartphone visible réduit les capacités cognitives disponibles, même éteint. La désactivation des notifications augmente la productivité de 26% (Iqbal & Bailey, 2010).",
                'instructions'     => [
                    "Ouvrez les paramètres de votre téléphone. Comptez le nombre d'applications ayant accès aux notifications.",
                    "Pour chaque app, posez-vous : « Cette notification m'apporte-t-elle une valeur réelle dans la minute ? »",
                    "Désactivez toutes les notifications non-essentielles (réseaux sociaux, news, jeux, promotions).",
                    "Sur votre ordinateur : désactivez les popups email. Définissez 2-3 créneaux fixes de consultation.",
                    "Mettez en place une règle : entre 9h et 12h, téléphone retourné et silencieux.",
                    "Après 3 jours : notez l'évolution de votre concentration et de votre sentiment de contrôle.",
                ],
                'scoring'          => ['dimension' => 'focus', 'weight' => 1.1],
            ],

            [
                'id'               => 'single-tasking',
                'title'            => "Single-tasking — l'art de finir ce qu'on commence",
                'category'         => 'focus',
                'duration_minutes' => 4,
                'difficulty'       => 2,
                'scientific_basis' => "Le multitâche cognitif est un mythe neurologique : le cerveau ne traite pas deux tâches complexes simultanément, il bascule rapidement entre elles (task-switching). Rubinstein, Meyer & Evans (2001, APA) ont mesuré que ces bascules coûtent jusqu'à 40% de productivité cognitive et multiplient les erreurs. Le single-tasking délibéré est l'antidote neurologique prouvé.",
                'instructions'     => [
                    "Choisissez une tâche de travail profond (rédaction, analyse, création). Durée estimée : 30-45 min.",
                    "Fermez TOUTES les applications non liées à cette tâche. Pas d'exception.",
                    "Écrivez sur une fiche : « En ce moment, je travaille sur : ___ ». Posez-la devant vous.",
                    "Si une tâche parallèle vous vient à l'esprit, notez-la dans votre liste sans changer de tâche.",
                    "Terminez la tâche ou arrivez à un point de livraison partiel avant d'ouvrir quoi que ce soit d'autre.",
                    "Bilan : notez la qualité produite vs. vos sessions multitâches habituelles. La différence est-elle perceptible ?",
                ],
                'scoring'          => ['dimension' => 'focus', 'weight' => 1.2],
            ],

            [
                'id'               => 'batching-taches',
                'title'            => "Batching de tâches similaires — grouper pour économiser",
                'category'         => 'focus',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => "Le batching consiste à regrouper les tâches similaires en un seul bloc temporel. Des recherches en sciences cognitives (Rubinstein et al., 2001) montrent que chaque changement de type de tâche génère un coût de commutation (switching cost) de 15 à 20% du temps effectif. Regrouper les emails, les appels ou les tâches administratives réduit ce coût et libère des blocs de concentration continue.",
                'instructions'     => [
                    "Listez tous les types de tâches récurrentes de votre semaine (emails, appels, administratif, création, réunions).",
                    "Identifiez lesquelles peuvent être groupées (ex : tous les emails en 2 créneaux, tous les appels l'après-midi).",
                    "Dans votre agenda, créez des blocs « batch » : ex. Lundi 14h-15h = tous les emails urgents de la semaine.",
                    "Respectez la règle : ne répondez aux emails QUE dans vos créneaux batch, pas en continu.",
                    "Après une semaine, mesurez : combien de fois avez-vous consulté vos emails hors des créneaux prévus ?",
                    "Ajustez les créneaux batch selon votre expérience. L'objectif : ≤ 3 consultations email par jour.",
                ],
                'scoring'          => ['dimension' => 'focus', 'weight' => 1.1],
            ],

            // ═══════════════════════════════════════════════════════════
            //  GESTION DES PRIORITÉS
            // ═══════════════════════════════════════════════════════════
            [
                'id'               => 'matrice-eisenhower',
                'title'            => "Matrice Eisenhower — trier l'urgent de l'important",
                'category'         => 'gestion_priorites',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => "La matrice Eisenhower (popularisée par Stephen Covey dans Les 7 Habitudes) divise les tâches selon deux axes : urgence (pression temporelle) et importance (valeur ajoutée réelle). Des recherches en comportement organisationnel (Covey, Macan et al.) montrent que 70-80% du temps des travailleurs est absorbé par le quadrant « urgent/non-important » au détriment des activités à fort impact (important/non-urgent).",
                'instructions'     => [
                    "Dessinez un grand carré divisé en 4 quadrants : Q1 (Urgent + Important), Q2 (Important + Non urgent), Q3 (Urgent + Non important), Q4 (Non urgent + Non important).",
                    "Listez toutes vos tâches de la semaine sur des Post-its ou fiches.",
                    "Placez chaque tâche dans son quadrant en vous demandant : est-ce vraiment important pour mes objectifs ? Est-ce vraiment urgent aujourd'hui ?",
                    "Q1 = Faites immédiatement. Q2 = Planifiez (c'est là que vivent les vrais leviers). Q3 = Déléguez. Q4 = Éliminez.",
                    "Comptez le nombre de tâches dans chaque quadrant. Combien sont en Q2 (la zone de la proactivité) ?",
                    "Objectif : chaque semaine, planifiez au moins 2 blocs dédiés exclusivement à vos tâches Q2.",
                ],
                'scoring'          => ['dimension' => 'gestion_priorites', 'weight' => 1.3],
            ],

            [
                'id'               => 'regle-deux-minutes',
                'title'            => "Règle des 2 minutes — éliminer les micro-tâches",
                'category'         => 'gestion_priorites',
                'duration_minutes' => 2,
                'difficulty'       => 1,
                'scientific_basis' => "Extraite du système GTD (David Allen, 2001), la règle des 2 minutes repose sur la théorie de la charge cognitive : stocker une petite tâche dans sa liste de suivi coûte davantage en énergie mentale (rappel, révision, anxiété) que de l'exécuter immédiatement. Les neurosciences confirment que les tâches non terminées activent le cortex préfrontal de manière persistante (effet Zeigarnik).",
                'instructions'     => [
                    "Ouvrez votre liste de tâches ou votre boîte email.",
                    "Pour chaque item, posez-vous une seule question : « Est-ce que ça prend moins de 2 minutes ? »",
                    "Si oui → faites-le maintenant, immédiatement, sans le noter dans une liste.",
                    "Si non → planifiez-le (date + durée estimée) ou déléguez-le.",
                    "Appliquez cette règle en traitant votre inbox entière en une seule session.",
                    "Constatez la réduction de votre liste : combien d'items ont été traités en < 2 minutes ?",
                ],
                'scoring'          => ['dimension' => 'gestion_priorites', 'weight' => 1.0],
            ],

            [
                'id'               => 'analyse-80-20',
                'title'            => "Analyse 80/20 — trouvez vos tâches à effet de levier",
                'category'         => 'gestion_priorites',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => "Le principe de Pareto (Vilfredo Pareto, 1896) appliqué à la productivité stipule que 20% des actions produisent 80% des résultats. Des études en management (Koch, 2011 ; Koch, The 80/20 Principle) confirment que la plupart des professionnels passent l'essentiel de leur temps sur des tâches à faible impact. Identifier et protéger ces 20% transforme la productivité sans augmenter les heures travaillées.",
                'instructions'     => [
                    "Listez toutes vos activités professionnelles sur une semaine type (soyez exhaustif).",
                    "Pour chaque activité, estimez son impact réel sur vos objectifs : faible / moyen / fort.",
                    "Identifiez vos 3-5 activités à impact fort : ce sont vos 20% Pareto.",
                    "Maintenant comptez : quel pourcentage de votre temps leur accordez-vous réellement ?",
                    "Identifiez 2-3 activités à faible impact que vous pourriez déléguer, automatiser ou supprimer.",
                    "Action concrète : libérez 1 heure par jour en éliminant une activité faible impact. Réallouez-la à votre 20% Pareto.",
                ],
                'scoring'          => ['dimension' => 'gestion_priorites', 'weight' => 1.2],
            ],

            [
                'id'               => 'protocole-delegation',
                'title'            => "Protocole de délégation — dire non avec méthode",
                'category'         => 'gestion_priorites',
                'duration_minutes' => 4,
                'difficulty'       => 2,
                'scientific_basis' => "L'incapacité à déléguer et à dire non génère une surcharge cognitive (cognitive overload) qui dégrade les performances du cortex préfrontal (Arnsten, 2009). Des recherches en psychologie organisationnelle montrent que les personnes qui fixent des limites claires ont une productivité 31% supérieure et un niveau de stress significativement inférieur (Harvard Business Review, 2012).",
                'instructions'     => [
                    "Listez 5 tâches récurrentes que vous faites mais qui pourraient être faites par quelqu'un d'autre.",
                    "Pour chacune, identifiez qui pourrait la faire (collègue, outil automatisé, prestataire).",
                    "Choisissez une demande que vous allez refuser ou rediriger cette semaine. Préparez votre formulation : « Je ne suis pas la bonne personne pour ça, mais [X] pourrait vous aider / voici quand je pourrai le faire. »",
                    "Entraînez-vous à dire cette phrase à voix haute 3 fois.",
                    "Appliquez-la dans une situation réelle cette semaine.",
                    "Bilan : comment vous êtes-vous senti après avoir dit non ? La relation a-t-elle été préservée ?",
                ],
                'scoring'          => ['dimension' => 'gestion_priorites', 'weight' => 1.1],
            ],

            // ═══════════════════════════════════════════════════════════
            //  GESTION DE L'ÉNERGIE
            // ═══════════════════════════════════════════════════════════
            [
                'id'               => 'audit-energie-chronotype',
                'title'            => "Audit énergie & chronotype — trouve ton pic cognitif",
                'category'         => 'gestion_energie',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => "La gestion de l'énergie plutôt que du temps est le principe central de Loehr & Schwartz (The Power of Full Engagement, 2003). Les cycles ultradiens (90 min éveillé / 20 min récupération) et le chronotype individuel (matinal vs vespéral, déterminé génétiquement à 47% selon Till Roenneberg) dictent les moments optimaux pour le travail cognitif intense. Aligner tâches et énergie augmente la productivité de 20-25%.",
                'instructions'     => [
                    "Pendant 3 jours consécutifs, toutes les heures, notez votre niveau d'énergie de 1 (épuisé) à 5 (en forme optimale).",
                    "À la fin des 3 jours, tracez votre courbe d'énergie moyenne. Identifiez votre pic (souvent entre 9h-12h ou 15h-17h).",
                    "Identifiez également votre creux (souvent après le déjeuner, 13h-15h).",
                    "Réorganisez votre semaine : tâches cognitives complexes → pic d'énergie. Tâches routinières → creux.",
                    "Planifiez 2 pauses de récupération de 15 min dans votre journée (à la fin des blocs ultradiens).",
                    "Après une semaine : notez si votre production a changé qualitativement sur les créneaux de pic.",
                ],
                'scoring'          => ['dimension' => 'gestion_energie', 'weight' => 1.3],
            ],

            [
                'id'               => 'pauses-strategiques',
                'title'            => "Pauses stratégiques — récupérer pour performer",
                'category'         => 'gestion_energie',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => "Des recherches en neurosciences (Immordino-Yang et al., 2012) montrent que les pauses activent le réseau du mode par défaut (DMN), essentiel à la consolidation mémorielle, la créativité et la résolution de problèmes. Une étude de DeskTime (2014) sur les employés les plus productifs révèle qu'ils travaillent 52 minutes puis se reposent 17 minutes. La fatigue décisionnelle (Baumeister, 2008) s'accumule dès 90 minutes de travail continu.",
                'instructions'     => [
                    "Réglez une alarme toutes les 50-60 minutes pendant votre journée de travail.",
                    "À chaque alarme, arrêtez COMPLÈTEMENT votre tâche. Levez-vous physiquement.",
                    "Pendant 10-15 minutes : marchez (même dans votre bureau), regardez au loin, hydratez-vous, respirez.",
                    "L'interdiction absolue : consulter emails, réseaux sociaux, ou continuer à travailler pendant la pause.",
                    "Après 3 jours : évaluez votre niveau d'énergie à 17h comparé à votre habitude sans pause.",
                    "Bonus : notez si des idées créatives émergent pendant ces pauses (le DMN travaille pour vous).",
                ],
                'scoring'          => ['dimension' => 'gestion_energie', 'weight' => 1.1],
            ],

            [
                'id'               => 'rituel-cloture-journee',
                'title'            => "Rituel de clôture — fermer la journée proprement",
                'category'         => 'gestion_energie',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => "Le rituel de clôture journalière (shutdown ritual, Cal Newport) exploite l'effet Zeigarnik inversé : noter explicitement où en sont les tâches non terminées permet au cerveau de « lâcher prise » et de ne pas les ruminer la nuit. Des études sur la qualité du sommeil (Scullin et al., 2018) montrent que rédiger ses tâches du lendemain avant le coucher réduit le temps d'endormissement de 9 minutes en moyenne.",
                'instructions'     => [
                    "Chaque soir, avant 18h30, allouez 10 minutes à votre rituel de clôture.",
                    "Étape 1 — Bilan : notez vos 3 accomplissements de la journée, même les petits.",
                    "Étape 2 — Report : listez les tâches non terminées et où vous en êtes (une phrase suffit).",
                    "Étape 3 — Préparer demain : notez vos 3 MIT pour le lendemain pendant que votre journée est encore fraîche.",
                    "Étape 4 — Phrase de clôture : dites mentalement ou écrivez « Shutdown complete. » C'est un signal neural de coupure.",
                    "Mesurez votre qualité de récupération sur 7 jours : les ruminations du soir diminuent-elles ?",
                ],
                'scoring'          => ['dimension' => 'gestion_energie', 'weight' => 1.2],
            ],

            [
                'id'               => 'recharge-physique',
                'title'            => "Recharge physique — le carburant de la productivité",
                'category'         => 'gestion_energie',
                'duration_minutes' => 4,
                'difficulty'       => 2,
                'scientific_basis' => "Loehr & Schwartz (2003) démontrent que la performance humaine dépend de 4 niveaux d'énergie : physique, émotionnel, mental et spirituel. La base physique (sommeil, mouvement, nutrition) conditionne les trois autres. Des méta-analyses (Lambourne & Tomporowski, 2010) montrent qu'une marche de 20 minutes augmente les fonctions exécutives du cortex préfrontal de 10-15% pendant les 2 heures suivantes.",
                'instructions'     => [
                    "Faites un audit de vos 4 piliers physiques cette semaine : Sommeil (heures réelles ?), Activité physique (nombre de sessions ?), Nutrition (qualité des repas de travail ?), Hydratation (litres d'eau par jour ?).",
                    "Identifiez votre pilier le plus déficient.",
                    "Choisissez UNE action concrète pour l'améliorer cette semaine (ex : 7h30 de sommeil minimum, 20 min de marche à midi).",
                    "Planifiez cette action dans votre agenda comme une réunion inamovible.",
                    "Après 5 jours, évaluez l'impact sur votre concentration et votre niveau d'énergie.",
                    "Notez : quelle corrélation observez-vous entre votre qualité de sommeil et votre productivité du lendemain ?",
                ],
                'scoring'          => ['dimension' => 'gestion_energie', 'weight' => 1.0],
            ],

            // ═══════════════════════════════════════════════════════════
            //  LUTTE CONTRE LA PROCRASTINATION
            // ═══════════════════════════════════════════════════════════
            [
                'id'               => 'diagnostic-procrastination',
                'title'            => "Diagnostic procrastination — comprendre son évitement",
                'category'         => 'procrastination',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => "La procrastination est aujourd'hui définie comme un problème de régulation émotionnelle plutôt que de gestion du temps (Pychyl & Sirois, 2016). L'évitement est déclenché par des émotions négatives associées à une tâche (peur de l'échec, anxiété de performance, ennui). La récompense dopaminergique immédiate de l'évitement (réseaux sociaux, etc.) surpasse dans l'instant la récompense différée de l'accomplissement (Tuckman, 2010).",
                'instructions'     => [
                    "Identifiez la tâche que vous procrastinez le plus en ce moment. Nommez-la précisément.",
                    "Pour cette tâche, répondez honnêtement : Quelle émotion est-ce qu'elle déclenche ? (ennui, anxiété, peur du jugement, perfectionnisme, sentiment d'incompétence ?)",
                    "Identifiez votre stratégie d'évitement préférée (emails, réseaux sociaux, tâches secondaires, « je n'ai pas le bon outil »).",
                    "Maintenant, demandez-vous : quel est le VRAI obstacle à démarrer ? (pas la version logique — la version émotionnelle).",
                    "Écrivez une phrase d'auto-compassion : « Il est normal que cette tâche soit difficile parce que ___ ».",
                    "Définissez la micro-action d'entrée (2 minutes max) pour commencer cette tâche maintenant.",
                ],
                'scoring'          => ['dimension' => 'lutte_procrastination', 'weight' => 1.2],
            ],

            [
                'id'               => 'chunking-micro-etapes',
                'title'            => "Chunking — découper l'éléphant en bouchées",
                'category'         => 'procrastination',
                'duration_minutes' => 4,
                'difficulty'       => 1,
                'scientific_basis' => "Le chunking (Miller, 1956 ; Gobet et al., 2001) désigne la décomposition d'une grande tâche en unités cognitives gérables. Appliqué à la procrastination, il contourne le mécanisme d'évitement : une tâche perçue comme trop grande active l'amygdale (réponse de stress), tandis qu'une micro-tâche de 5-10 minutes reste sous le seuil d'activation du stress. La règle des « deux minutes » (GTD) est la forme extrême du chunking.",
                'instructions'     => [
                    "Choisissez votre tâche procrastinée (grande, floue ou intimidante).",
                    "Découpez-la en sous-tâches de MAXIMUM 30 minutes chacune. Soyez ultra-spécifique et concret.",
                    "Pour chaque sous-tâche, définissez le livrable exact : qu'est-ce qui sera produit/décidé à la fin ?",
                    "Numérotez vos sous-tâches dans l'ordre logique. Planifiez la première dans votre agenda aujourd'hui.",
                    "La règle d'or : ne commencez jamais par « faire [grand projet] » — toujours par « écrire les 3 premières lignes de » ou « envoyer l'email de cadrage à ». »",
                    "Après avoir terminé la première sous-tâche, célébrez (check mental, barre sur le papier). L'élan est lancé.",
                ],
                'scoring'          => ['dimension' => 'lutte_procrastination', 'weight' => 1.2],
            ],

            [
                'id'               => 'technique-5-secondes',
                'title'            => "La règle des 5 secondes — lancer avant le veto mental",
                'category'         => 'procrastination',
                'duration_minutes' => 2,
                'difficulty'       => 1,
                'scientific_basis' => "La règle des 5 secondes (Mel Robbins, 2017) est un outil comportemental qui exploite la fenêtre de décision pré-motrice. Des recherches en neurosciences (Libet, 1985) montrent que le cerveau prend une décision motrice 300 ms avant la conscience. En comptant à rebours 5-4-3-2-1 et en agissant immédiatement, on interrompt la boucle de rumination et d'évitement avant que le cortex préfrontal ne construise une justification pour ne pas commencer.",
                'instructions'     => [
                    "Pensez à une tâche que vous devez faire mais que vous évitez depuis au moins 2 jours.",
                    "Lisez la règle : quand vous direz « 1 », vous allez vous lever et commencer. Sans négociation.",
                    "Comptez à voix haute : 5 — 4 — 3 — 2 — 1.",
                    "Levez-vous et faites la première action physique liée à la tâche (ouvrir le document, sortir le dossier, écrire le titre).",
                    "Une fois lancé, continuez pendant au moins 5 minutes. L'inertie du démarrage est souvent le seul obstacle.",
                    "Pratiquez cette technique pendant 7 jours sur toutes les tâches que vous repoussez. Notez vos observations.",
                ],
                'scoring'          => ['dimension' => 'lutte_procrastination', 'weight' => 1.1],
            ],

            [
                'id'               => 'recompenses-dopaminergiques',
                'title'            => "Récompenses dopaminergiques — hacker sa motivation",
                'category'         => 'procrastination',
                'duration_minutes' => 3,
                'difficulty'       => 2,
                'scientific_basis' => "La procrastination est en partie un problème de temporalité de la récompense : le cerveau limbique valorise les récompenses immédiates (dopamine instantanée) au détriment des récompenses différées (accomplissement futur). Des neuroscientifiques (Schultz, 1997 ; Berridge, 2007) montrent qu'anticiper explicitement une récompense active le circuit de récompense dopaminergique AVANT l'action, augmentant la motivation de démarrage.",
                'instructions'     => [
                    "Choisissez une tâche difficile que vous devez accomplir cette semaine.",
                    "Définissez une récompense concrète et désirée que vous vous accorderez UNIQUEMENT après avoir terminé (café préféré, série, sortie, achat plaisir).",
                    "Écrivez le contrat avec vous-même : « Quand j'aurai terminé ___, je m'offrirai ___ ».",
                    "Visualisez pendant 30 secondes le plaisir de la récompense AVANT de commencer. Activez la dopamine anticipatoire.",
                    "Ne prenez PAS la récompense avant d'avoir terminé. L'engagement doit être tenu.",
                    "Réflexion : avez-vous ressenti plus de motivation à démarrer ? Quel type de récompense fonctionne le mieux pour vous ?",
                ],
                'scoring'          => ['dimension' => 'lutte_procrastination', 'weight' => 1.1],
            ],

        ];
    }

    /**
     * Retourne les exercices recommandés pour une dimension et un score normalisé donné.
     * score : 0-100. En dessous de 50 → priorité haute.
     *
     * @param  string  $dimension  Clé de dimension (ex: 'focus')
     * @param  int     $normScore  Score normalisé 0-100
     * @return array<int, array>   Liste des exercices triés par pertinence
     */
    public static function recommended(string $dimension, int $normScore): array
    {
        $all = self::exercises();
        $filtered = array_filter($all, fn($e) => $e['scoring']['dimension'] === $dimension);
        usort($filtered, fn($a, $b) => $b['scoring']['weight'] <=> $a['scoring']['weight']);

        // Si score très faible (<= 33) → exercices faciles en premier pour l'ancrage
        if ($normScore <= 33) {
            usort($filtered, function ($a, $b) {
                if ($a['difficulty'] !== $b['difficulty']) return $a['difficulty'] <=> $b['difficulty'];
                return $b['scoring']['weight'] <=> $a['scoring']['weight'];
            });
        }

        return array_values($filtered);
    }
}
