<?php

namespace Praxis\Plugins\PraxiLink\Data;

class Exercises
{
    /**
     * Retourne les 20 exercices du module PraxiLink — Communication assertive.
     *
     * Dimensions couvertes :
     *   ecoute_active, expression_assertive, gestion_conflits,
     *   empathie_relationnelle, feedback_constructif
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [

            // ──────────────────────────────────────────────────────────────
            // ÉCOUTE ACTIVE
            // ──────────────────────────────────────────────────────────────
            [
                'id'              => 'ea-01',
                'title'           => 'Le reflet empathique',
                'category'        => 'ecoute',
                'duration_minutes' => 3,
                'difficulty'      => 1,
                'scientific_basis' => 'Écoute active — Carl Rogers (1951). Le reflet consiste à reformuler le contenu émotionnel de l\'interlocuteur pour lui signifier qu\'il est entendu.',
                'scoring'         => ['dimension' => 'ecoute_active', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Votre collègue Sophie vous dit : « Je suis vraiment épuisée en ce moment. Ce projet me prend tout mon temps et je n\'ai même plus de soirées pour moi. J\'ai l\'impression que personne ne s\'en rend compte. »',
                    'question'  => 'Parmi les réponses suivantes, laquelle constitue le meilleur reflet empathique ?',
                    'type'      => 'choix_multiple',
                    'options'   => [
                        'A' => 'Tu devrais apprendre à mieux gérer ton temps.',
                        'B' => 'Si je comprends bien, tu te sens débordée et invisible, et ça t\'épuise vraiment.',
                        'C' => 'Moi aussi j\'ai des projets lourds, c\'est normal dans ce métier.',
                        'D' => 'Il faudrait en parler à ton manager.',
                    ],
                    'correct'   => 'B',
                    'feedback'  => 'La réponse B reformule à la fois la situation (débordée) et l\'émotion (invisible, épuisée), sans juger ni conseiller prématurément. C\'est l\'essence du reflet empathique rogérien.',
                ],
            ],

            [
                'id'              => 'ea-02',
                'title'           => 'Questions ouvertes vs questions fermées',
                'category'        => 'ecoute',
                'duration_minutes' => 2,
                'difficulty'      => 1,
                'scientific_basis' => 'Écoute active — distinction questions ouvertes/fermées (Rogers, 1951 ; Gordon, 1970). Les questions ouvertes élargissent l\'espace de parole.',
                'scoring'         => ['dimension' => 'ecoute_active', 'weight' => 0.8],
                'instructions'    => [
                    'scenario'  => 'Lors d\'un entretien avec un collaborateur en difficulté sur sa mission, vous souhaitez comprendre sa situation en profondeur.',
                    'question'  => 'Classez ces questions de la plus ouverte à la plus fermée :',
                    'type'      => 'classement',
                    'options'   => [
                        '1' => 'Est-ce que tu as compris les objectifs ?',
                        '2' => 'Qu\'est-ce qui te semble le plus difficile dans cette mission ?',
                        '3' => 'Comment vis-tu cette période sur le projet ?',
                        '4' => 'Qu\'est-ce que « réussir » signifierait pour toi ici ?',
                    ],
                    'correct_order' => [4, 3, 2, 1],
                    'feedback'  => 'La question 4 invite à définir sa propre vision du succès (très ouverte). La question 1 appelle un oui/non (fermée). Plus une question laisse de liberté dans la réponse, plus elle favorise l\'écoute active.',
                ],
            ],

            [
                'id'              => 'ea-03',
                'title'           => 'Reformulation de fond',
                'category'        => 'ecoute',
                'duration_minutes' => 3,
                'difficulty'      => 2,
                'scientific_basis' => 'Technique de reformulation (Mucchielli, 1983). La reformulation de fond restitue le sens global du message sans l\'interpréter.',
                'scoring'         => ['dimension' => 'ecoute_active', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Lors d\'une réunion, Marc dit : « On nous demande de livrer ce module en deux semaines alors qu\'on n\'a pas encore les specs complètes, que deux personnes de l\'équipe sont en congé, et qu\'on vient de détecter un bug critique sur l\'ancienne version. Je veux bien faire des efforts mais là c\'est vraiment impossible. »',
                    'question'  => 'Rédigez une reformulation de fond en une ou deux phrases. Votre reformulation doit restituer : (1) la situation factuelle, (2) la position de Marc, (3) sans jugement.',
                    'type'      => 'redaction_libre',
                    'indicateurs' => [
                        'Mentionne les contraintes concrètes (délai, specs, congés, bug)',
                        'Restitue la position de Marc (efforts acceptés, mais délai irréaliste)',
                        'Absence de jugement ou de conseil',
                        'Utilise des mots du type « si je comprends bien », « ce que tu soulèves » ou équivalent',
                    ],
                    'exemple_reponse' => 'Si je comprends bien, Marc, tu es face à un cumul de contraintes — délai serré, specs incomplètes, équipe réduite et bug critique — qui rend le délai demandé objectivement irréaliste à tes yeux, même avec de la bonne volonté.',
                    'feedback'  => 'Une bonne reformulation résume sans déformer ni minimiser. Elle ne propose pas de solution et ne juge pas la résistance.',
                ],
            ],

            [
                'id'              => 'ea-04',
                'title'           => 'Détecter les signaux d\'écoute parasites',
                'category'        => 'ecoute',
                'duration_minutes' => 2,
                'difficulty'      => 2,
                'scientific_basis' => 'Barrières à l\'écoute (Gordon, 1970 — les 12 obstacles à la communication). Identifier les comportements qui coupent la parole ou invalident l\'interlocuteur.',
                'scoring'         => ['dimension' => 'ecoute_active', 'weight' => 0.9],
                'instructions'    => [
                    'scenario'  => 'Observez cet échange entre Léa (manager) et Thomas (développeur) :\n\nThomas : « J\'ai du mal avec la nouvelle procédure de déploiement, elle me prend trois fois plus de temps. »\nLéa : « Oui mais tout le monde l\'utilise sans problème. Tu as regardé la doc ? Et puis c\'est important d\'être autonome sur ces sujets. »',
                    'question'  => 'Identifiez au moins 3 obstacles à l\'écoute présents dans la réponse de Léa.',
                    'type'      => 'cases_a_cocher',
                    'options'   => [
                        'A' => 'Comparaison invalidante (« tout le monde »)',
                        'B' => 'Question rhétorique (« Tu as regardé la doc ? »)',
                        'C' => 'Moralisation (« il est important d\'être autonome »)',
                        'D' => 'Reflet empathique',
                        'E' => 'Minimisation du problème',
                        'F' => 'Jugement sur la compétence',
                    ],
                    'correct'   => ['A', 'B', 'C', 'E'],
                    'feedback'  => 'Léa commet 4 obstacles : comparaison (A), question rhétorique implicitement accusatoire (B), moralisation (C) et minimisation du problème (E). Aucun reflet ni exploration de la difficulté réelle de Thomas.',
                ],
            ],

            // ──────────────────────────────────────────────────────────────
            // CNV — COMMUNICATION NON-VIOLENTE
            // ──────────────────────────────────────────────────────────────
            [
                'id'              => 'cnv-01',
                'title'           => 'Construire un message OSBD',
                'category'        => 'cnv',
                'duration_minutes' => 4,
                'difficulty'      => 2,
                'scientific_basis' => 'Communication Non-Violente — Marshall Rosenberg (1999). Le modèle OSBD : Observation factuelle, Sentiment, Besoin, Demande concrète.',
                'scoring'         => ['dimension' => 'expression_assertive', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Situation : Votre collègue arrive systématiquement 15 minutes en retard aux réunions d\'équipe hebdomadaires (8h30). La semaine dernière, vous avez dû répéter l\'ordre du jour trois fois. Vous souhaitez lui en parler.',
                    'question'  => 'Rédigez un message en 4 étapes OSBD :\n\nO — Observation (fait neutre, vérifiable, sans jugement)\nS — Sentiment (émotion que vous ressentez)\nB — Besoin (besoin non satisfait derrière ce sentiment)\nD — Demande (action concrète, réalisable, exprimée positivement)',
                    'type'      => 'redaction_structuree',
                    'champs'    => ['Observation', 'Sentiment', 'Besoin', 'Demande'],
                    'indicateurs' => [
                        'O : fait observable, sans interprétation (« tu arrives après 8h30 » et non « tu es irrespectueux »)',
                        'S : émotion en « je » (frustré, découragé, préoccupé…)',
                        'B : besoin universel (efficacité, respect, coordination…)',
                        'D : demande précise et réalisable (« pourrais-tu… »)',
                    ],
                    'exemple_reponse' => "O : Lors des quatre dernières réunions du lundi, tu es arrivé entre 10 et 20 minutes après le début.\nS : Je me sens frustré et démotivé.\nB : J'ai besoin d'efficacité et que chacun puisse contribuer dès le départ.\nD : Serait-il possible que tu arrives à 8h30 ou que tu me préviennes la veille si tu as un empêchement ?",
                    'feedback'  => 'La puissance de l\'OSBD est dans la séparation stricte des faits et des émotions, et dans la demande non punitive. Une demande doit toujours être refusable pour rester une demande (et non une exigence).',
                ],
            ],

            [
                'id'              => 'cnv-02',
                'title'           => 'Distinguer observation et évaluation',
                'category'        => 'cnv',
                'duration_minutes' => 2,
                'difficulty'      => 1,
                'scientific_basis' => 'CNV — Rosenberg (1999). La première étape OSBD consiste à séparer l\'observation neutre (vérifiable par n\'importe qui) de l\'évaluation ou de l\'interprétation.',
                'scoring'         => ['dimension' => 'expression_assertive', 'weight' => 0.8],
                'instructions'    => [
                    'scenario'  => 'Vous recevez les phrases suivantes. Certaines sont des observations pures, d\'autres sont des évaluations déguisées.',
                    'question'  => 'Pour chaque phrase, indiquez O (Observation) ou E (Évaluation) :',
                    'type'      => 'classification_binaire',
                    'items'     => [
                        ['phrase' => 'Tu as interrompu Camille trois fois pendant sa présentation.', 'correct' => 'O'],
                        ['phrase' => 'Tu es irrespectueux envers tes collègues.', 'correct' => 'E'],
                        ['phrase' => 'Ce rapport a été remis deux jours après la deadline convenue.', 'correct' => 'O'],
                        ['phrase' => 'Tu ne t\'impliques jamais dans les projets collectifs.', 'correct' => 'E'],
                        ['phrase' => 'Lors de la réunion du 10 juin, tu n\'as pas pris la parole.', 'correct' => 'O'],
                        ['phrase' => 'Tu es toujours dans la lune en réunion.', 'correct' => 'E'],
                    ],
                    'feedback'  => 'Les évaluations contiennent souvent des mots comme « toujours », « jamais », « irrespectueux », des jugements de caractère. L\'observation se limite à ce qui est perceptible par les sens à un moment précis.',
                ],
            ],

            [
                'id'              => 'cnv-03',
                'title'           => 'Nommer ses émotions avec précision',
                'category'        => 'cnv',
                'duration_minutes' => 3,
                'difficulty'      => 2,
                'scientific_basis' => 'CNV — vocabulaire émotionnel (Rosenberg, 1999). La granularité émotionnelle améliore la qualité de la communication et réduit les malentendus.',
                'scoring'         => ['dimension' => 'empathie_relationnelle', 'weight' => 0.9],
                'instructions'    => [
                    'scenario'  => 'Votre manager vient de modifier l\'architecture de votre projet sans vous consulter. Vous vous sentez… « mal ».',
                    'question'  => 'Le mot « mal » est trop vague. Parmi les émotions suivantes, lesquelles pourraient mieux décrire votre état intérieur ? Sélectionnez toutes celles qui s\'appliquent, puis justifiez votre premier choix.',
                    'type'      => 'selection_et_justification',
                    'options'   => [
                        'Frustré(e)', 'Déçu(e)', 'Ignoré(e) (pseudo-émotion)', 'Anxieux(se)',
                        'En colère', 'Découragé(e)', 'Blessé(e)', 'Impuissant(e)',
                    ],
                    'note_pedagogique' => 'Attention : « ignoré(e) » est une pseudo-émotion car elle contient une interprétation du comportement de l\'autre. Une vraie émotion se ressent dans le corps.',
                    'feedback'  => 'La précision émotionnelle (frustré vs découragé vs blessé) oriente la conversation vers le besoin réel. Confondre pseudo-émotions (interprétations déguisées) et émotions réelles génère des malentendus.',
                ],
            ],

            // ──────────────────────────────────────────────────────────────
            // ASSERTIVITÉ
            // ──────────────────────────────────────────────────────────────
            [
                'id'              => 'ass-01',
                'title'           => 'Identifier son style de communication',
                'category'        => 'assertivite',
                'duration_minutes' => 3,
                'difficulty'      => 1,
                'scientific_basis' => 'Triangle Passivité-Agressivité-Assertivité (Alberti & Emmons, 1970 ; Jakubowski, 1976). L\'assertivité est le style qui exprime ses besoins sans violer ceux d\'autrui.',
                'scoring'         => ['dimension' => 'expression_assertive', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Votre collègue vous demande, pour la troisième fois ce mois-ci, de finir son rapport à sa place parce qu\'il est « débordé ». Vous avez vous-même beaucoup de travail.',
                    'question'  => 'Associez chaque réponse à son style de communication (Passif / Agressif / Assertif) :',
                    'type'      => 'association',
                    'options'   => [
                        'A' => '« Bon, d\'accord, je vais essayer de trouver du temps… » (en soupirant)',
                        'B' => '« Encore toi ! Tu pourrais apprendre à gérer ton temps, non ? »',
                        'C' => '« Je comprends que tu sois sous pression. Pour ma part, j\'ai aussi un planning chargé. Je ne peux pas prendre ton rapport cette fois, mais on peut chercher ensemble une autre solution. »',
                        'D' => '« Je n\'ai pas que ça à faire. » (et tu raccroches)',
                    ],
                    'correct'   => ['A' => 'Passif', 'B' => 'Agressif', 'C' => 'Assertif', 'D' => 'Agressif passif'],
                    'feedback'  => 'L\'assertivité (C) reconnaît le besoin de l\'autre et affirme le sien sans culpabilité ni attaque. La réponse passive (A) génère du ressentiment. Les réponses agressives (B, D) détériorent la relation.',
                ],
            ],

            [
                'id'              => 'ass-02',
                'title'           => 'Le disque rayé assertif',
                'category'        => 'assertivite',
                'duration_minutes' => 3,
                'difficulty'      => 2,
                'scientific_basis' => 'Technique du « disque rayé » (Smith, 1975 — When I Say No, I Feel Guilty). Répéter calmement sa position sans se justifier permet de résister à la pression sociale.',
                'scoring'         => ['dimension' => 'expression_assertive', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Vous avez décidé de ne pas participer au projet bénévole proposé par votre direction. Votre manager insiste :\n\nM : « Tout le monde y participe, tu seras le seul absent. »\nM : « C\'est important pour la cohésion d\'équipe. »\nM : « Je pensais que tu tenais à l\'entreprise. »',
                    'question'  => 'Pour chaque relance du manager, rédigez une réponse utilisant la technique du disque rayé : reconnaître ce qui est dit, puis réaffirmer votre position calmement.',
                    'type'      => 'redaction_libre',
                    'indicateurs' => [
                        'Absence de justification excessive',
                        'Réaffirmation de la position sans attaque',
                        'Ton calme et non défensif',
                        'Reconnaissance brève du point de l\'autre',
                    ],
                    'exemple_reponse' => "Relance 1 : « Je comprends, et ma décision reste de ne pas y participer cette fois.\"\nRelance 2 : « Je sais que c\'est important pour toi, et je ne pourrai pas m\'y joindre.\"\nRelance 3 : « Je tiens à l\'entreprise, et je ne participerai pas à ce projet. »",
                    'feedback'  => 'Le disque rayé ne cherche pas à convaincre : il maintient un « non » ferme sans agressivité. L\'absence de justification empêche l\'autre de démontrer que votre raison est « insuffisante ».',
                ],
            ],

            [
                'id'              => 'ass-03',
                'title'           => 'Recevoir une critique sans se défendre',
                'category'        => 'assertivite',
                'duration_minutes' => 3,
                'difficulty'      => 3,
                'scientific_basis' => 'Gestion assertive de la critique — technique du « brouillard » et de l\'acquiescement négatif (Smith, 1975). Accepter partiellement une critique vraie désamorce l\'escalade.',
                'scoring'         => ['dimension' => 'expression_assertive', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Votre directrice vous dit devant l\'équipe : « Franchement, ton compte-rendu de la semaine dernière était vraiment bâclé. On ne comprend rien à tes conclusions. »',
                    'question'  => 'Rédigez une réponse assertive qui :\n1. Accueille la critique sans vous effondrer ni contre-attaquer\n2. Reconnaît ce qui est potentiellement vrai\n3. Demande des précisions si nécessaire\n4. Exprime votre ressenti ou intention sans vous excuser à l\'excès',
                    'type'      => 'redaction_libre',
                    'indicateurs' => [
                        'Pas d\'attaque retour',
                        'Pas d\'humilité excessive ou d\'excuse répétée',
                        'Reconnaissance d\'un fond de vérité possible',
                        'Demande de précision constructive',
                    ],
                    'exemple_reponse' => '« Tu as peut-être raison sur le manque de clarté — je veux m\'améliorer sur ce point. Pourrais-tu me préciser quelles conclusions te semblaient confuses ? Je préfère comprendre pour que le prochain soit plus utile. »',
                    'feedback'  => 'Cette réponse évite l\'escalade sans se dévaloriser. Elle transforme la critique en occasion d\'apprentissage. L\'assertivité face à la critique, c\'est garder son estime de soi intact tout en restant ouvert.',
                ],
            ],

            // ──────────────────────────────────────────────────────────────
            // GESTION DES CONFLITS
            // ──────────────────────────────────────────────────────────────
            [
                'id'              => 'conf-01',
                'title'           => 'Les modes Thomas-Kilmann',
                'category'        => 'conflit',
                'duration_minutes' => 4,
                'difficulty'      => 2,
                'scientific_basis' => 'Modèle Thomas-Kilmann (1974). Cinq modes de gestion des conflits selon deux axes : assertivité (satisfaire ses besoins) et coopération (satisfaire les besoins de l\'autre).',
                'scoring'         => ['dimension' => 'gestion_conflits', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Deux équipes (développement et marketing) s\'opposent sur le délai de lancement d\'un produit. Le marketing veut lancer en 3 semaines pour une opportunité de marché. Le développement estime avoir besoin de 6 semaines pour un produit stable.',
                    'question'  => 'Pour chaque mode de résolution ci-dessous, décrivez en une phrase ce que l\'équipe dirait/ferait, puis évaluez si ce mode est adapté à cette situation.',
                    'type'      => 'analyse_modale',
                    'modes'     => ['Compétition', 'Accommodation', 'Évitement', 'Compromis', 'Collaboration'],
                    'correct_mode' => 'Collaboration',
                    'exemple_collaboration' => 'Les deux équipes analysent ensemble les features minimales pour un lancement à 4 semaines, avec un plan de stabilisation post-lancement partagé.',
                    'feedback'  => 'La collaboration vise un gain mutuel (win-win) en explorant les intérêts réels derrière les positions. Elle prend plus de temps mais préserve la relation et produit des solutions plus robustes que le simple compromis (chacun perd un peu).',
                ],
            ],

            [
                'id'              => 'conf-02',
                'title'           => 'Positions vs intérêts (Fisher & Ury)',
                'category'        => 'conflit',
                'duration_minutes' => 4,
                'difficulty'      => 3,
                'scientific_basis' => 'Négociation raisonnée — Fisher, Ury & Patton (Getting to Yes, 1981). Distinguer les positions affichées des intérêts réels permet de trouver des solutions créatives.',
                'scoring'         => ['dimension' => 'gestion_conflits', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Lucas (chef de projet) exige que les réunions de suivi aient lieu chaque lundi matin à 8h. Clara (designer) insiste pour qu\'elles aient lieu le jeudi après-midi. Chacun refuse de céder.',
                    'question'  => 'Complétez le tableau suivant :',
                    'type'      => 'tableau_interets',
                    'colonnes'  => ['Acteur', 'Position (ce qu\'il dit vouloir)', 'Intérêt probable (pourquoi)'],
                    'lignes'    => [
                        ['Lucas', 'Lundi 8h', '???'],
                        ['Clara', 'Jeudi après-midi', '???'],
                    ],
                    'interet_lucas_exemple' => 'Synchroniser l\'équipe en début de semaine pour piloter les priorités hebdomadaires.',
                    'interet_clara_exemple' => 'Avoir le temps de finaliser les livrables du mardi-mercredi avant de les présenter.',
                    'solution_possible' => 'Réunion le mercredi matin : Clara a deux jours pour livrer, Lucas synchronise à mi-semaine.',
                    'feedback'  => 'Quand on passe des positions (lundi vs jeudi) aux intérêts (coordination vs préparation), l\'espace de solutions s\'élargit considérablement. La plupart des conflits positionnels se résolvent par l\'exploration des intérêts sous-jacents.',
                ],
            ],

            [
                'id'              => 'conf-03',
                'title'           => 'Désamorcer une conversation tendue',
                'category'        => 'conflit',
                'duration_minutes' => 4,
                'difficulty'      => 3,
                'scientific_basis' => 'Validation émotionnelle et désamorçage (Linehan, 1993 — Dialectical Behavior Therapy ; Gottman, 1994). Valider ne signifie pas être d\'accord : cela signifie reconnaître que l\'émotion est compréhensible.',
                'scoring'         => ['dimension' => 'gestion_conflits', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'En réunion, Antoine explose soudainement : « C\'est toujours pareil ! On nous présente des décisions toutes faites et on est censés faire semblant d\'adhérer. Je suis fatigué d\'être traité comme un pion ! »',
                    'question'  => 'Vous êtes le manager. Rédigez une réponse en trois temps :\n1. Validation émotionnelle (reconnaître l\'émotion sans la juger)\n2. Clarification (explorer ce qui se cache derrière)\n3. Proposition de suite constructive',
                    'type'      => 'redaction_structuree',
                    'champs'    => ['Validation', 'Clarification', 'Proposition'],
                    'indicateurs' => [
                        'Validation : nomme l\'émotion, ne la minimise pas, ne la nie pas',
                        'Clarification : question ouverte sur la situation concrète',
                        'Proposition : invite à un espace de dialogue, ne promet pas l\'impossible',
                    ],
                    'exemple_reponse' => "Validation : « Antoine, je t\'entends — tu exprimes une vraie frustration face au sentiment de ne pas être consulté. »\nClarification : « Peux-tu me dire sur quelle décision récente tu as eu ce sentiment le plus fortement ? »\nProposition : « Je veux qu\'on prenne le temps, toi et moi, de revoir notre façon de partager les décisions. Peut-on trouver 30 minutes cette semaine ? »",
                    'feedback'  => 'Commencer par la validation désarme la tension. Si vous sautez directement à la solution ou à la défense, Antoine se sentira encore plus ignoré. La validation n\'est pas une capitulation — c\'est l\'ouverture d\'un vrai dialogue.',
                ],
            ],

            // ──────────────────────────────────────────────────────────────
            // FEEDBACK CONSTRUCTIF
            // ──────────────────────────────────────────────────────────────
            [
                'id'              => 'fb-01',
                'title'           => 'La méthode DESC',
                'category'        => 'feedback',
                'duration_minutes' => 4,
                'difficulty'      => 2,
                'scientific_basis' => 'Méthode DESC — Bower & Bower (Asserting Yourself, 1976). Describe, Express, Specify, Consequences : structurer un feedback difficile de façon non agressive.',
                'scoring'         => ['dimension' => 'feedback_constructif', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Vous êtes manager. Inès, votre développeuse senior, répond aux questions des clients directement sans passer par le process de ticket, ce qui crée des incohérences dans le suivi et du travail en double pour le support.',
                    'question'  => 'Rédigez un feedback DESC complet à Inès.',
                    'type'      => 'redaction_structuree',
                    'champs'    => [
                        'D — Describe (faits observables)',
                        'E — Express (impact sur vous / l\'équipe / ressenti)',
                        'S — Specify (comportement attendu)',
                        'C — Consequences (bénéfices du changement)',
                    ],
                    'indicateurs' => [
                        'D : faits spécifiques, sans jugement de personne',
                        'E : « je » ou « l\'équipe », pas « tu fais toujours »',
                        'S : demande précise et réalisable',
                        'C : conséquences positives du changement (pas menace)',
                    ],
                    'exemple_reponse' => "D : « Au cours des deux dernières semaines, j\'ai constaté que tu as répondu directement à 4 demandes clients par email sans créer de ticket dans Jira. »\nE : « Cela crée des doublons et le support perd du temps à reconstituer le contexte des échanges. »\nS : « Je voudrais que chaque demande client passe par un ticket, même si tu réponds en parallèle. »\nC : « Ainsi, toute l\'équipe a de la visibilité et tu seras reconnue pour ton aide sans alourdir le travail de suivi. »",
                    'feedback'  => 'Le DESC est puissant car il sépare strictement les faits, les émotions, les attentes et les bénéfices. Évitez de finir par une menace (conséquences négatives) — le changement volontaire est toujours plus durable.',
                ],
            ],

            [
                'id'              => 'fb-02',
                'title'           => 'Recevoir du feedback positif',
                'category'        => 'feedback',
                'duration_minutes' => 2,
                'difficulty'      => 1,
                'scientific_basis' => 'Réception du feedback positif (Heen & Stone, Thanks for the Feedback, 2014). Beaucoup de personnes ont du mal à recevoir les compliments — ils les minimisent ou les dévient, ce qui décourage les donneurs de feedback.',
                'scoring'         => ['dimension' => 'feedback_constructif', 'weight' => 0.7],
                'instructions'    => [
                    'scenario'  => 'Votre directeur vous dit en réunion : « Ta présentation d\'hier était excellente. Tu as su convaincre un client très exigeant, c\'est vraiment du bon travail. »',
                    'question'  => 'Laquelle de ces réponses illustre une réception saine du feedback positif ?',
                    'type'      => 'choix_multiple',
                    'options'   => [
                        'A' => '« Oh, c\'est rien, j\'ai eu de la chance. »',
                        'B' => '« Merci — j\'avais préparé cette présentation avec soin et je suis content(e) qu\'elle ait eu l\'effet voulu. »',
                        'C' => '« Oui, et pourtant personne ne m\'avait aidé ! »',
                        'D' => '« Tu trouves vraiment ? Je ne suis pas sûr(e) d\'avoir été si bon(ne)… »',
                    ],
                    'correct'   => 'B',
                    'feedback'  => 'La réponse B accueille le feedback avec gratitude et relie le résultat à un effort réel. Les autres réponses minimisent (A, D) ou détournent vers une revendication (C). Bien recevoir un compliment encourage les retours futurs et renforce l\'estime de soi.',
                ],
            ],

            [
                'id'              => 'fb-03',
                'title'           => 'Feedback sandwich : pourquoi l\'éviter (et le modèle SBI)',
                'category'        => 'feedback',
                'duration_minutes' => 3,
                'difficulty'      => 2,
                'scientific_basis' => 'La méthode « sandwich » (positif-négatif-positif) est aujourd\'hui considérée comme l\'un des formats les MOINS efficaces : elle dilue le message critique et peut entamer la confiance (Heen & Stone, 2014). Mieux étayé : le modèle SBI — Situation, Behavior, Impact (Center for Creative Leadership) — qui reste factuel et réduit la défensivité. Plus le comportement est décrit précisément, plus le destinataire agit dessus.',
                'scoring'         => ['dimension' => 'feedback_constructif', 'weight' => 0.9],
                'instructions'    => [
                    'scenario'  => 'Vous devez donner un feedback à Raphaël dont les emails clients sont très bien écrits mais qui oublie systématiquement de mettre l\'équipe en copie.',
                    'question'  => 'Étape 1 : rédigez un feedback « sandwich » et identifiez son risque principal ici. Étape 2 : réécrivez le même feedback au format SBI (Situation → Comportement observé → Impact concret).',
                    'type'      => 'redaction_et_analyse',
                    'champs'    => ['Sandwich : positif / problème / positif', 'Risque du sandwich dans ce cas', 'SBI — Situation (quand, où)', 'SBI — Comportement observable (faits)', 'SBI — Impact (conséquence concrète)'],
                    'risque_attendu' => 'Raphaël pourrait retenir surtout les parties positives et ne pas percevoir la gravité de l\'oubli de mise en copie.',
                    'feedback'  => 'Le sandwich enrobe le message au point de le diluer : le destinataire retient les compliments et minimise le problème. Le format SBI est plus efficace car il reste factuel et précis (Situation, Comportement, Impact) sans juger la personne — ce qui réduit la défensivité et augmente la probabilité d\'un changement réel.',
                ],
            ],

            // ──────────────────────────────────────────────────────────────
            // EMPATHIE RELATIONNELLE
            // ──────────────────────────────────────────────────────────────
            [
                'id'              => 'emp-01',
                'title'           => 'Empathie vs sympathie vs projection',
                'category'        => 'cnv',
                'duration_minutes' => 3,
                'difficulty'      => 2,
                'scientific_basis' => 'Empathie — Brené Brown (2010) ; Carl Rogers (1951). L\'empathie consiste à se mettre dans le cadre de référence de l\'autre sans se perdre soi-même, ni compatir (sympathie) ni projeter ses propres émotions.',
                'scoring'         => ['dimension' => 'empathie_relationnelle', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Votre collègue Hugo vous confie : « Je viens d\'apprendre que ma candidature interne a été refusée. Je ne comprends pas — j\'ai tout donné pour ce poste. »',
                    'question'  => 'Associez chaque réponse à son type (Empathie / Sympathie / Projection) et justifiez.',
                    'type'      => 'association_justifiee',
                    'options'   => [
                        'A' => '« Oh non, c\'est horrible ! Tu dois être effondré, moi ça m\'aurait brisé. »',
                        'B' => '« Ce que tu traverses doit être vraiment douloureux. Être investis à fond et se voir refusé, c\'est difficile à accepter. »',
                        'C' => '« Moi aussi j\'ai eu une déception comme ça l\'an dernier — c\'est dur mais on s\'en remet. »',
                    ],
                    'correct'   => [
                        'A' => 'Sympathie/Projection — s\'approprie la souffrance de l\'autre et projette ses propres réactions',
                        'B' => 'Empathie — se place dans le vécu de Hugo sans se perdre',
                        'C' => 'Sympathie — ramène l\'expérience à soi, décentre le focus',
                    ],
                    'feedback'  => 'L\'empathie dit « je suis avec toi dans cet espace ». La sympathie dit « j\'ai mal pour toi » (décentrage). La projection dit « voilà ce que je ressentirais à ta place ». Seule l\'empathie maintient Hugo au centre.',
                ],
            ],

            [
                'id'              => 'emp-02',
                'title'           => 'Communication interculturelle : adapter son style',
                'category'        => 'ecoute',
                'duration_minutes' => 4,
                'difficulty'      => 3,
                'scientific_basis' => 'Communication interculturelle — Hofstede (1980), Hall (1976 — contexte haut/bas). Les cultures dites « haut contexte » (Japon, Corée, Maghreb) communiquent davantage par l\'implicite. Les cultures « bas contexte » (USA, Allemagne, Pays nordiques) privilégient l\'explicite.',
                'scoring'         => ['dimension' => 'empathie_relationnelle', 'weight' => 0.9],
                'instructions'    => [
                    'scenario'  => 'Vous managez une équipe internationale. Lors d\'une réunion, vous proposez une nouvelle procédure. Yuki (Japonaise) répond : « C\'est une approche très intéressante. » Daniel (Allemand) dit : « Je vois plusieurs problèmes de faisabilité — on ne peut pas adopter ça sans plus d\'analyse. »',
                    'question'  => 'Que signifient probablement ces deux réactions dans leur contexte culturel respectif ? Comment adapteriez-vous votre suivi avec chacun ?',
                    'type'      => 'analyse_et_plan',
                    'indicateurs' => [
                        'Yuki : culture haut contexte — « intéressant » peut signifier des réserves non exprimées directement ; suivi en tête-à-tête recommandé',
                        'Daniel : culture bas contexte — critique directe, non hostile, factuelle ; répondre point par point est valorisé',
                        'Adapter le canal (tête-à-tête vs groupe)',
                        'Ne pas imposer un style de communication unique',
                    ],
                    'feedback'  => 'Il n\'existe pas de communication « neutre » : tout style est culturellement situé. L\'empathie interculturelle commence par reconnaître que le silence ou l\'indirect n\'est pas un refus et que la franchise directe n\'est pas de l\'agressivité.',
                ],
            ],

            [
                'id'              => 'emp-03',
                'title'           => 'Le message en langage « je »',
                'category'        => 'cnv',
                'duration_minutes' => 3,
                'difficulty'      => 1,
                'scientific_basis' => 'Messages en « je » vs messages en « tu » — Gordon (1970 — Parent Effectiveness Training). Le message en « je » décrit l\'impact sur soi ; le message en « tu » accuse et génère de la défensivité.',
                'scoring'         => ['dimension' => 'expression_assertive', 'weight' => 0.8],
                'instructions'    => [
                    'scenario'  => 'Vous avez besoin de silence pour vous concentrer et votre open-space est très bruyant.',
                    'question'  => 'Transformez chacun de ces messages en « tu » en message en « je » :',
                    'type'      => 'transformation',
                    'items'     => [
                        'Tu parles trop fort, c\'est insupportable.',
                        'Tu n\'as aucun respect pour la concentration des autres.',
                        'Tu fais toujours ça en plein milieu de mes tâches importantes.',
                    ],
                    'exemples_reponses' => [
                        'Quand les conversations sont fortes, j\'ai du mal à me concentrer et je ressens de la tension.',
                        'J\'ai besoin de calme pour ce type de tâche — puis-je te demander de continuer cette conversation à voix plus basse ou dans la salle de réunion ?',
                        'Quand des échanges surviennent au moment où je suis en pleine concentration, cela m\'affecte vraiment — est-ce qu\'on peut trouver un arrangement ?',
                    ],
                    'feedback'  => 'Le message en « je » décrit un fait + une émotion + un besoin. Il ne pose pas de jugement sur l\'intention de l\'autre. Résultat : moins de défensivité, plus de coopération.',
                ],
            ],

            // ──────────────────────────────────────────────────────────────
            // NÉGOCIATION WIN-WIN
            // ──────────────────────────────────────────────────────────────
            [
                'id'              => 'neg-01',
                'title'           => 'Négociation win-win : préparer son BATNA',
                'category'        => 'conflit',
                'duration_minutes' => 5,
                'difficulty'      => 3,
                'scientific_basis' => 'BATNA — Best Alternative to a Negotiated Agreement (Fisher & Ury, 1981). Connaître sa meilleure alternative renforce sa position sans qu\'on ait besoin de menacer.',
                'scoring'         => ['dimension' => 'gestion_conflits', 'weight' => 1.0],
                'instructions'    => [
                    'scenario'  => 'Vous négociez avec un fournisseur (Agence Alpha) le renouvellement de votre contrat de design. Agence Alpha propose 15 000 € pour la prestation. Votre budget est de 10 000 €. Vous avez reçu un devis de l\'Agence Beta pour 11 500 € et une proposition d\'une freelance pour 9 000 € (moins d\'expérience).',
                    'question'  => 'Avant d\'entrer en négociation, définissez :\n1. Votre BATNA (meilleure alternative si l\'accord échoue)\n2. Votre ZOPA (Zone Of Possible Agreement) — fourchette acceptable\n3. Votre première offre et sa justification\n4. Deux concessions que vous pouvez faire vs deux lignes rouges',
                    'type'      => 'plan_negociation',
                    'exemple_reponse' => "BATNA : accepter le devis d\'Agence Beta à 11 500 € (compétente, budget légèrement dépassé mais acceptable).\nZOPA : entre 10 000 € et 11 500 €.\nPremière offre : 9 500 € en justifiant par le volume et la fidélité client.\nConcessions possibles : paiement en avance, témoignage client valorisant.\nLignes rouges : dépasser 11 500 € ou réduire le périmètre de livrables.",
                    'feedback'  => 'Connaître son BATNA transforme la négociation : vous n\'êtes plus en position de faiblesse car vous savez exactement quel accord vaut mieux que pas d\'accord. La ZOPA définit l\'espace réel de négociation.',
                ],
            ],

            [
                'id'              => 'neg-02',
                'title'           => 'Reformuler une objection en intérêt',
                'category'        => 'conflit',
                'duration_minutes' => 3,
                'difficulty'      => 2,
                'scientific_basis' => 'Reformulation des objections (Fisher & Ury, 1981 ; Rosenberg, 1999). Une objection cache souvent un besoin ou une peur légitime. La reformuler en intérêt ouvre la négociation.',
                'scoring'         => ['dimension' => 'gestion_conflits', 'weight' => 0.9],
                'instructions'    => [
                    'scenario'  => 'Lors d\'une réunion projet, votre cliente dit : « Je ne veux pas du tout de méthodologie agile — dans mon expérience, ça crée du chaos. »',
                    'question'  => 'Reformulez cette objection en intérêt, puis proposez une réponse qui explore cet intérêt.',
                    'type'      => 'reformulation_et_reponse',
                    'indicateurs' => [
                        'Reformulation : identifie le besoin ou la peur derrière l\'objection (ex : besoin de visibilité, de prévisibilité, de contrôle)',
                        'Réponse : explore l\'intérêt sans défendre la méthode aveuglément',
                        'Ton : curiosité, pas défensivité',
                    ],
                    'exemple_reponse' => "Reformulation de l\'intérêt : votre cliente a besoin de clarté, de jalons prévisibles et de sentir qu\'elle a le contrôle du périmètre.\nRéponse : « Je comprends — vous avez besoin de visibilité et de prévisibilité sur l\'avancement. Si je vous montrais comment on peut garder un planning structuré tout en conservant de la flexibilité, est-ce que ça répondrait à votre préoccupation ? »",
                    'feedback'  => 'Défendre une méthode face à une objection frontale génère une guerre de positions. Passer par l\'intérêt réel (besoin de contrôle) permet de proposer une solution qui satisfait les deux parties.',
                ],
            ],

            // ──────────────────────────────────────────────────────────────
            // EXERCICE INTÉGRATEUR
            // ──────────────────────────────────────────────────────────────
            [
                'id'              => 'int-01',
                'title'           => 'Situation complète : toutes compétences mobilisées',
                'category'        => 'assertivite',
                'duration_minutes' => 5,
                'difficulty'      => 3,
                'scientific_basis' => 'Exercice intégrateur mobilisant : CNV (Rosenberg), écoute active (Rogers), gestion des conflits (Thomas-Kilmann), feedback DESC (Bower), assertivité (Alberti & Emmons).',
                'scoring'         => [
                    'dimensions' => [
                        'ecoute_active'        => 0.2,
                        'expression_assertive' => 0.2,
                        'gestion_conflits'     => 0.2,
                        'empathie_relationnelle' => 0.2,
                        'feedback_constructif' => 0.2,
                    ],
                ],
                'instructions'    => [
                    'scenario'  => 'Contexte : vous êtes chef de projet. Depuis deux semaines, Julien (développeur senior) arrive en réunion avec son téléphone constamment à la main, répond aux messages pendant les discussions et coupe la parole à ses collègues lorsqu\'il veut intervenir. Hier, cette attitude a froissé Amina, qui est partie de la réunion visiblement contrariée. Vous devez parler à Julien.',
                    'question'  => 'Rédigez la conversation complète que vous auriez avec Julien. Votre texte doit inclure :\n\n1. Une ouverture bienveillante (pas d\'accusation directe)\n2. Un reflet empathique si Julien exprime une émotion\n3. Un message OSBD sur les comportements observés\n4. Une validation si Julien contre-argue\n5. Une demande précise et une conséquence positive\n6. Une clôture qui préserve la relation',
                    'type'      => 'redaction_libre_integratrice',
                    'indicateurs' => [
                        'Ouverture non accusatoire',
                        'Présence d\'un reflet empathique si Julien réagit',
                        'OSBD identifiable',
                        'Validation d\'une contre-argumentation',
                        'Demande précise et conséquence positive',
                        'Clôture relationnelle',
                    ],
                    'feedback'  => 'Cet exercice teste votre capacité à maintenir un cap assertif tout en restant empathique. La difficulté est d\'éviter de glisser vers la passivité (abandonner le message) ou vers l\'agressivité (perdre de vue la relation) face à la résistance de Julien.',
                ],
            ],
        ];
    }

    /**
     * Retourne les exercices filtrés par catégorie.
     *
     * @param string $category ecoute|cnv|conflit|feedback|assertivite
     * @return array<int, array<string, mixed>>
     */
    public static function byCategory(string $category): array
    {
        return array_filter(
            self::all(),
            fn (array $ex) => $ex['category'] === $category
        );
    }

    /**
     * Retourne les exercices filtrés par dimension de scoring.
     *
     * @param string $dimension
     * @return array<int, array<string, mixed>>
     */
    public static function byDimension(string $dimension): array
    {
        return array_filter(self::all(), function (array $ex) use ($dimension): bool {
            if (isset($ex['scoring']['dimension'])) {
                return $ex['scoring']['dimension'] === $dimension;
            }
            if (isset($ex['scoring']['dimensions'])) {
                return array_key_exists($dimension, $ex['scoring']['dimensions']);
            }
            return false;
        });
    }

    /**
     * Retourne un exercice du jour pseudo-aléatoire basé sur la date.
     *
     * @return array<string, mixed>
     */
    public static function exerciseOfTheDay(): array
    {
        $all = self::all();
        $index = (int) date('z') % count($all);
        return $all[$index];
    }
}
