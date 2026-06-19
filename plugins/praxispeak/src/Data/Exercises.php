<?php

namespace Praxis\Plugins\PraxiSpeak\Data;

class Exercises
{
    /**
     * Cinq dimensions de la prise de parole en public.
     * Base scientifique : modèles de Wolpe (désensibilisation), Bandura (auto-efficacité),
     * Cuddy (power posing), Goleman (régulation émotionnelle), Carnegie (structure).
     */
    public static function dimensions(): array
    {
        return [
            'gestion_du_trac' => [
                'label'       => 'Gestion du trac',
                'description' => 'Régulation de l'anxiété de performance avant et pendant la prise de parole.',
                'icon'        => 'heart-pulse',
                'color'       => '#6366F1',
            ],
            'preparation_mentale' => [
                'label'       => 'Préparation mentale',
                'description' => 'Structuration cognitive et visualisation avant d'intervenir.',
                'icon'        => 'brain',
                'color'       => '#8B5CF6',
            ],
            'presence_physique' => [
                'label'       => 'Présence physique',
                'description' => 'Langage non-verbal, posture, regard et occupation de l'espace.',
                'icon'        => 'person-standing',
                'color'       => '#EC4899',
            ],
            'structure_du_discours' => [
                'label'       => 'Structure du discours',
                'description' => 'Organisation et clarté du message transmis à l'auditoire.',
                'icon'        => 'list-ordered',
                'color'       => '#F59E0B',
            ],
            'impact_vocal' => [
                'label'       => 'Impact vocal',
                'description' => 'Projection, rythme, articulation et variations de la voix.',
                'icon'        => 'mic',
                'color'       => '#10B981',
            ],
        ];
    }

    /**
     * 20 exercices courts (2-5 min) fondés sur la recherche en psychologie cognitive
     * et les techniques éprouvées de coaching en prise de parole.
     */
    public static function exercises(): array
    {
        return [

            // ── GESTION DU TRAC ─────────────────────────────────────────────────────────

            [
                'id'              => 'trac_01',
                'title'           => 'Respiration 4-7-8 anti-trac',
                'category'        => 'gestion_du_trac',
                'duration_minutes'=> 3,
                'difficulty'      => 1,
                'scientific_basis'=> 'Respiration diaphragmatique (Benson, 1975) — active le système nerveux parasympathique et réduit le cortisol. La phase expiratoire prolongée (8 temps) maximise l'effet vagal.',
                'instructions'    => [
                    'Assois-toi droit, les deux pieds à plat sur le sol.',
                    'Pose une main sur ton ventre, l'autre sur ta poitrine.',
                    'Inspire lentement par le nez en comptant jusqu'à 4 — sens ton ventre se gonfler (pas ta poitrine).',
                    'Bloque ta respiration en comptant jusqu'à 7.',
                    'Expire entièrement par la bouche, lèvres légèrement entrouvertes, en comptant jusqu'à 8.',
                    'Répète ce cycle 4 fois.',
                    'À la fin, remarque la chaleur dans tes mains et le relâchement dans tes épaules.',
                ],
                'scoring'         => ['dimension' => 'gestion_du_trac', 'points' => 10],
            ],

            [
                'id'              => 'trac_02',
                'title'           => 'Désensibilisation par étapes — l'échelle d'exposition',
                'category'        => 'gestion_du_trac',
                'duration_minutes'=> 5,
                'difficulty'      => 2,
                'scientific_basis'=> 'Désensibilisation systématique (Wolpe, 1958) — exposition graduelle couplée à la relaxation réduit la réponse anxieuse conditionnée. Efficacité démontrée sur l'anxiété de performance sociale (Hofmann & Smits, 2008).',
                'instructions'    => [
                    'Sur une feuille, liste 5 situations de prise de parole du moins stressant au plus stressant (ex. 1 = parler à un ami / 5 = présenter en réunion plénière).',
                    'Ferme les yeux et imagine la situation n°1 avec le plus de détails possible : l'endroit, les gens, les sons.',
                    'Reste dans cette image jusqu'à ce que ton anxiété descende en dessous de 3/10.',
                    'Passe à la situation suivante seulement quand tu es calme sur la précédente.',
                    'Si tu bloque sur une étape, reviens à la respiration 4-7-8 avant de réessayer.',
                    'Objectif session : progresser d'au moins une marche sur ton échelle.',
                ],
                'scoring'         => ['dimension' => 'gestion_du_trac', 'points' => 15],
            ],

            [
                'id'              => 'trac_03',
                'title'           => 'Recadrage cognitif du trac',
                'category'        => 'gestion_du_trac',
                'duration_minutes'=> 4,
                'difficulty'      => 2,
                'scientific_basis'=> 'Restructuration cognitive (Beck, 1979) — identifier et remplacer les pensées automatiques négatives réduit l'anxiété anticipatoire. Brooks (2014, Harvard) montre que requalifier l'excitation en « je suis excité(e) » améliore la performance.',
                'instructions'    => [
                    'Prends une feuille et trace deux colonnes : « Pensée automatique » et « Recadrage ».',
                    'Note 3 pensées catastrophistes que tu as avant de parler en public (ex. « Je vais me planter », « Tout le monde va me juger »).',
                    'Pour chacune, pose-toi : « Quelle est la probabilité réelle ? » et « Et si ça se passait bien, à quoi ressemblerait la scène ? »',
                    'Remplace chaque pensée par une formulation active : « Je suis bien préparé(e) », « Mon message a de la valeur ».',
                    'Lis tes recadrages à voix haute, debout, avec conviction.',
                    'Ferme les yeux 30 secondes et laisse la nouvelle version s'imprimer.',
                ],
                'scoring'         => ['dimension' => 'gestion_du_trac', 'points' => 15],
            ],

            [
                'id'              => 'trac_04',
                'title'           => 'Ancre de ressource PNL',
                'category'        => 'gestion_du_trac',
                'duration_minutes'=> 5,
                'difficulty'      => 2,
                'scientific_basis'=> 'Ancrage neuro-linguistique (Bandler & Grinder, 1979) — un stimulus conditionné (geste) associé à un état interne intense permet de le réactiver à la demande. Mécanisme proche du conditionnement classique pavlovien appliqué aux ressources positives.',
                'instructions'    => [
                    'Ferme les yeux. Rappelle-toi un moment où tu t'es senti(e) vraiment confiant(e) et à ta place.',
                    'Revivez ce souvenir intensément : les images, les sons, les sensations physiques dans ton corps.',
                    'Au pic de l'état (7/10 minimum), pince fermement le pouce et l'index de ta main dominante pendant 5 secondes.',
                    'Relâche, aère-toi l'esprit en pensant à autre chose 30 secondes.',
                    'Répète 3 fois pour solidifier l'ancre.',
                    'Test : pince ton ancre et observe si l'état de confiance revient spontanément.',
                    'Active cette ancre juste avant chaque prise de parole.',
                ],
                'scoring'         => ['dimension' => 'gestion_du_trac', 'points' => 15],
            ],

            // ── PRÉPARATION MENTALE ──────────────────────────────────────────────────────

            [
                'id'              => 'mental_01',
                'title'           => 'Visualisation positive de l'intervention',
                'category'        => 'preparation_mentale',
                'duration_minutes'=> 5,
                'difficulty'      => 2,
                'scientific_basis'=> 'Répétition mentale (Driskell et al., 1994) — la visualisation d'une performance réussie active les mêmes circuits neuronaux que l'exécution réelle (neurones miroirs). Méta-analyse : +12% de performance vs groupe contrôle.',
                'instructions'    => [
                    'Installe-toi confortablement, ferme les yeux, respire profondément trois fois.',
                    'Imagine-toi la veille de ton intervention : tu es calme, bien préparé(e), confiant(e).',
                    'Visualise le lieu : les chaises, la lumière, le pupitre ou la salle.',
                    'Vois-toi entrer, t'installer, sourire au public.',
                    'Entends ta voix claire et posée commencer. Ressens l'attention positive de l'auditoire.',
                    'Filme mentalement toute l'intervention comme un succès : les acquiescements, les sourires.',
                    'Vois-toi conclure avec impact, entends les applaudissements ou les remerciements.',
                    'Ouvre les yeux et note sur papier 3 mots qui décrivent cet orateur(trice) que tu viens de voir.',
                ],
                'scoring'         => ['dimension' => 'preparation_mentale', 'points' => 15],
            ],

            [
                'id'              => 'mental_02',
                'title'           => 'Méthode PREP — structurer son message en 3 min',
                'category'        => 'preparation_mentale',
                'duration_minutes'=> 5,
                'difficulty'      => 1,
                'scientific_basis'=> 'Structure argumentative PREP (Point, Reason, Example, Point) — issue de la rhétorique classique et popularisée dans les formations de leadership. Réduit la charge cognitive de l'orateur et améliore la mémorisation du message par l'auditoire (Mayer, 2009).',
                'instructions'    => [
                    'Choisis un sujet sur lequel tu devras bientôt t'exprimer (une idée, un projet, une position).',
                    'Prends une feuille et trace 4 cases : P — R — E — P.',
                    'P (Point) : note ton message central en une phrase. Maximum 20 mots.',
                    'R (Reason) : note la raison principale qui soutient ton point. Sois précis(e).',
                    'E (Example) : note un exemple concret, une histoire, un chiffre qui illustre ta raison.',
                    'P (Point) : reformule ton message initial légèrement différemment pour l'ancrer.',
                    'Entraîne-toi à réciter cette structure à voix haute en 90 secondes chrono.',
                    'Évalue : le message est-il clair pour quelqu'un qui n'y connaît rien ?',
                ],
                'scoring'         => ['dimension' => 'preparation_mentale', 'points' => 10],
            ],

            [
                'id'              => 'mental_03',
                'title'           => 'Carte mentale de préparation express',
                'category'        => 'preparation_mentale',
                'duration_minutes'=> 5,
                'difficulty'      => 1,
                'scientific_basis'=> 'Mind mapping (Buzan, 1974) — la représentation visuelle arborescente exploite la mémoire spatiale et associative. Réduit la surcharge cognitive et facilite la récupération en mémoire pendant l'intervention (Farrand et al., 2002).',
                'instructions'    => [
                    'Prends une feuille A4 en orientation paysage.',
                    'Au centre, écris ton sujet en 3 mots maximum et encercle-le.',
                    'Trace 3 branches principales : « Contexte », « Cœur du message », « Action attendue ».',
                    'Pour chaque branche, ajoute 2-3 sous-branches avec des mots-clés seulement (pas de phrases).',
                    'Utilise des couleurs différentes par branche si possible.',
                    'Regarde ta carte 30 secondes, ferme les yeux et essaie de la restituer mentalement.',
                    'Ta carte mentale doit pouvoir tenir sur un Post-it lors de ton intervention.',
                ],
                'scoring'         => ['dimension' => 'preparation_mentale', 'points' => 10],
            ],

            [
                'id'              => 'mental_04',
                'title'           => 'Rituel de centrage pré-intervention',
                'category'        => 'preparation_mentale',
                'duration_minutes'=> 3,
                'difficulty'      => 1,
                'scientific_basis'=> 'Routines pré-performance (Cotterill, 2010) — les rituels réduisent l'anxiété en créant un sentiment de contrôle et en automatisant la transition vers un état de focus. Largement utilisés en psychologie du sport.',
                'instructions'    => [
                    'Conçois TON rituel personnel en 5 étapes max (tu peux adapter cet exemple).',
                    '1. Hydrate-toi : bois un verre d'eau fraîche 5 minutes avant.',
                    '2. Posture : tiens-toi debout, les pieds à la largeur des épaules, 60 secondes.',
                    '3. Intention : dis à voix basse une phrase d'intention (ex. « Je suis ici pour apporter de la valeur »).',
                    '4. Respiration : 3 respirations profondes diaphragmatiques.',
                    '5. Sourire : souris 10 secondes — déclenche la libération d'endorphines.',
                    'Pratique ce rituel maintenant, puis utilise-le systématiquement avant chaque prise de parole.',
                ],
                'scoring'         => ['dimension' => 'preparation_mentale', 'points' => 10],
            ],

            [
                'id'              => 'mental_05',
                'title'           => 'Analyse du public — empathie stratégique',
                'category'        => 'preparation_mentale',
                'duration_minutes'=> 4,
                'difficulty'      => 2,
                'scientific_basis'=> 'Théorie de l'audience (Aristotle, Rhétorique) revisitée par la psychologie sociale — adapter son message au cadre de référence de l'auditoire augmente la persuasion et réduit les objections (Cialdini, 2001).',
                'instructions'    => [
                    'Pense à ton prochain auditoire. Prends 2 minutes pour répondre par écrit :',
                    '— Qui sont-ils ? (rôle, niveau d'expertise, relation avec moi)',
                    '— Qu'attendent-ils de cette intervention ?',
                    '— Quelle est leur préoccupation principale en ce moment ?',
                    '— Qu'est-ce qui pourrait les bloquer dans l'adhésion à mon message ?',
                    'Ajuste maintenant ton message PREP : est-il aligné avec leurs attentes ?',
                    'Identifie 1 formulation à changer pour mieux résonner avec leur réalité.',
                ],
                'scoring'         => ['dimension' => 'preparation_mentale', 'points' => 15],
            ],

            // ── PRÉSENCE PHYSIQUE ────────────────────────────────────────────────────────

            [
                'id'              => 'corps_01',
                'title'           => 'Posture de puissance — Power Pose 2 minutes',
                'category'        => 'presence_physique',
                'duration_minutes'=> 3,
                'difficulty'      => 1,
                'scientific_basis'=> 'Power posing (Cuddy et al., 2010) — les postures d'expansion augmentent le sentiment de confiance en soi et modulent les hormones de dominance. Même si l'effet hormonal est débattu, l'impact comportemental et perçu est répliqué (Ranehill et al., 2015).',
                'instructions'    => [
                    'Lève-toi et trouve un espace privé (toilettes, couloir, bureau vide).',
                    'Adopte la posture Wonder Woman / Superman : pieds à la largeur des épaules, mains sur les hanches, menton légèrement relevé, regard droit devant.',
                    'Tiens cette posture pendant exactement 2 minutes.',
                    'Respire profondément et régulièrement pendant ces 2 minutes.',
                    'Observe les sensations dans ton corps : chaleur, élargissement de la poitrine, stabilité des jambes.',
                    'Variation : bras levés en V (posture de victoire) pendant 60 secondes supplémentaires.',
                    'Pratique ceci dans les 5 minutes qui précèdent toute prise de parole importante.',
                ],
                'scoring'         => ['dimension' => 'presence_physique', 'points' => 10],
            ],

            [
                'id'              => 'corps_02',
                'title'           => 'Ancrage au sol et équilibre postural',
                'category'        => 'presence_physique',
                'duration_minutes'=> 3,
                'difficulty'      => 1,
                'scientific_basis'=> 'Embodied cognition (Lakoff & Johnson, 1999) — la stabilité posturale physique se traduit en sentiment de stabilité psychologique. Un orateur ancré transmet inconsciemment autorité et sécurité à son auditoire.',
                'instructions'    => [
                    'Debout, pieds parallèles, largeur des épaules. Ressens le contact de tes pieds avec le sol.',
                    'Imagine des racines qui partent de tes plantes de pieds et plongent dans le sol.',
                    'Porte ton poids légèrement sur la plante du pied (pas les talons).',
                    'Genoux légèrement déverrouillés — jamais raides.',
                    'Bascule ton bassin légèrement vers l'avant pour redresser le bas du dos.',
                    'Roule tes épaules vers l'arrière et vers le bas — ouvre la poitrine.',
                    'Allonge le sommet du crâne vers le plafond comme si un fil te tirait vers le haut.',
                    'Reste dans cette posture 60 secondes. Remarque : tu occupes l'espace sans effort.',
                ],
                'scoring'         => ['dimension' => 'presence_physique', 'points' => 10],
            ],

            [
                'id'              => 'corps_03',
                'title'           => 'Regard panoramique — connecter avec le public',
                'category'        => 'presence_physique',
                'duration_minutes'=> 4,
                'difficulty'      => 2,
                'scientific_basis'=> 'Contact visuel en communication (Argyle & Cook, 1976) — le regard est le principal canal de création de lien et de crédibilité. Un regard fuyant ou fixe réduit la confiance accordée à l'orateur.',
                'instructions'    => [
                    'Assieds-toi face à un miroir ou utilise des objets posés devant toi pour simuler un public.',
                    'Divise mentalement ton espace visuel en 3 zones : gauche, centre, droite.',
                    'Commence par la zone gauche : regarde un point précis 3-5 secondes (comme une personne réelle).',
                    'Puis centre : déplace ton regard naturellement, sans mouvement mécanique de tête.',
                    'Puis droite. Puis reviens au centre.',
                    'Le regard doit « donner » quelque chose — entraîne-toi à transmettre de la chaleur, pas juste à regarder.',
                    'Pratique avec un texte : dis une phrase, puis déplace le regard. Une idée = une zone.',
                    'Évite : regarder vos notes plus de 1 seconde, le plafond, et les pieds.',
                ],
                'scoring'         => ['dimension' => 'presence_physique', 'points' => 15],
            ],

            [
                'id'              => 'corps_04',
                'title'           => 'Gestes ouverts et gestes d'autorité',
                'category'        => 'presence_physique',
                'duration_minutes'=> 4,
                'difficulty'      => 2,
                'scientific_basis'=> 'Communication non-verbale (Mehrabian, 1971 revisé) — les gestes amplificateurs augmentent la mémorisation du message de 60% vs discours seul (McNeill, 1992). Les gestes fermés (bras croisés, mains cachées) signalent défensivité ou doute.',
                'instructions'    => [
                    'Lève-toi et parle d'un sujet quelconque pendant 2 minutes.',
                    'Règle 1 — Mains visibles : garde les mains au-dessus de la ceinture, paumes visibles.',
                    'Règle 2 — Geste d'énumération : pour lister 3 éléments, lève 3 doigts l'un après l'autre.',
                    'Règle 3 — Geste d'ouverture : pour inviter l'adhésion, ouvre les paumes vers le haut.',
                    'Règle 4 — Geste de tranchant : pour marquer une idée forte, tranche l'air d'une main verticale.',
                    'Règle 5 — Stop aux gestes parasites : pas de stylo à tripoter, pas de jeu avec bijoux ou vêtements.',
                    'Enregistre-toi 30 secondes et observe : tes gestes amplifient-ils ou contredisent-ils tes mots ?',
                ],
                'scoring'         => ['dimension' => 'presence_physique', 'points' => 15],
            ],

            // ── STRUCTURE DU DISCOURS ────────────────────────────────────────────────────

            [
                'id'              => 'structure_01',
                'title'           => 'L'ouverture en 3 étapes — Accrocher en 30 secondes',
                'category'        => 'structure_du_discours',
                'duration_minutes'=> 4,
                'difficulty'      => 2,
                'scientific_basis'=> 'Effet de primauté (Murdock, 1962) — les premières secondes d'une intervention déterminent l'attention et la crédibilité perçue pour la totalité de la présentation. TED Talks : 97% des ouvertures utilisent une des 4 techniques d'accroche.',
                'instructions'    => [
                    'Choisis une technique d'accroche parmi : question rhétorique, statistique surprenante, histoire courte, citation.',
                    'Rédige ton ouverture en 3 étapes : 1) Accroche (1 phrase) → 2) Enjeu (pourquoi c'est important) → 3) Annonce du plan (3 mots max par partie).',
                    'Exemple : « 70% des gens préfèrent la mort à la prise de parole en public. [accroche] Ce chiffre dit quelque chose de notre rapport à la vulnérabilité. [enjeu] Ce soir, nous allons voir : pourquoi, comment, et quand c'est possible. [plan] »',
                    'Entraîne-toi à dire ton ouverture en 30 secondes chrono, debout, sans notes.',
                    'Critère de réussite : quelqu'un qui t'écoute doit avoir envie d'entendre la suite.',
                ],
                'scoring'         => ['dimension' => 'structure_du_discours', 'points' => 15],
            ],

            [
                'id'              => 'structure_02',
                'title'           => 'La conclusion mémorable — Clore avec impact',
                'category'        => 'structure_du_discours',
                'duration_minutes'=> 3,
                'difficulty'      => 2,
                'scientific_basis'=> 'Effet de récence (Murdock, 1962) — la conclusion est le dernier souvenir de l'auditoire. Une conclusion forte avec appel à l'action augmente le passage à l'acte de 40% vs clôture sans structure (Petty & Cacioppo, 1986).',
                'instructions'    => [
                    'Rédige ta conclusion en 3 temps : 1) Résumé en 1 phrase → 2) Message fort à retenir → 3) Appel à l'action ou question ouverte.',
                    'Exemple : « Nous avons vu que la prise de parole se travaille comme un muscle. [résumé] Le trac n'est pas votre ennemi, c'est votre énergie. [message fort] Quelle est la prochaine occasion où vous allez prendre la parole ? [appel à l'action] »',
                    'Ta conclusion doit durer 30 à 60 secondes maximum.',
                    'Ne jamais dire : « voilà, c'est tout », « j'ai fini », « merci de m'avoir écouté » comme seule conclusion.',
                    'Entraîne-toi à la dire debout, en regardant le public imaginaire, sans notes.',
                ],
                'scoring'         => ['dimension' => 'structure_du_discours', 'points' => 15],
            ],

            [
                'id'              => 'structure_03',
                'title'           => 'Storytelling — Structurer une histoire en 90 secondes',
                'category'        => 'structure_du_discours',
                'duration_minutes'=> 5,
                'difficulty'      => 3,
                'scientific_basis'=> 'Narrative transportation theory (Green & Brock, 2000) — une histoire bien structurée génère de l'empathie, réduit la résistance au message et améliore la mémorisation de 22x par rapport à des faits seuls (Zak, 2014).',
                'instructions'    => [
                    'Choisis une expérience personnelle courte (professionnelle ou personnelle).',
                    'Structure-la en 5 étapes : 1) Contexte (quand, où, qui) → 2) Tension (quel problème ?) → 3) Action (qu'as-tu fait ?) → 4) Résolution (qu'est-il arrivé ?) → 5) Leçon (qu'est-ce que cela apprend ?).',
                    'Écris l'histoire en moins de 100 mots.',
                    'Entraîne-toi à la raconter en 90 secondes, debout.',
                    'Utilise le présent de narration pour plus d'impact (« Je suis dans la salle, il fait chaud, j'ouvre la bouche... »).',
                    'Critère : quelqu'un qui t'écoute doit pouvoir dire en 1 phrase ce que cette histoire lui a appris.',
                ],
                'scoring'         => ['dimension' => 'structure_du_discours', 'points' => 20],
            ],

            [
                'id'              => 'structure_04',
                'title'           => 'Gérer les questions difficiles — La technique du miroir',
                'category'        => 'structure_du_discours',
                'duration_minutes'=> 4,
                'difficulty'      => 3,
                'scientific_basis'=> 'Communication de crise (Ury, 1993 — Getting Past No) — reformuler la question désamorce l'hostilité, gagne du temps de réflexion et repositionne l'orateur en maîtrise. Utilisé en médiation, négociation et politique.',
                'instructions'    => [
                    'Identifie 3 questions difficiles ou déstabilisantes que tu pourrais recevoir après une intervention.',
                    'Pour chaque question, applique la séquence miroir :',
                    '1. Répète ou reformule la question (« Si je comprends bien, vous me demandez... »)',
                    '2. Valide l'intention (« C'est une question importante »)',
                    '3. Réoriente vers ton message central (« Ce qui est clé ici, c'est... »)',
                    '4. Conclus avec un retour à l'action.',
                    'Entraîne-toi à répondre à chaque question difficile en moins de 45 secondes.',
                    'Rappel : silence de 2 secondes avant de répondre = marque de réflexion, pas de faiblesse.',
                ],
                'scoring'         => ['dimension' => 'structure_du_discours', 'points' => 20],
            ],

            // ── IMPACT VOCAL ─────────────────────────────────────────────────────────────

            [
                'id'              => 'voix_01',
                'title'           => 'Projection vocale — Parler depuis le ventre',
                'category'        => 'impact_vocal',
                'duration_minutes'=> 4,
                'difficulty'      => 2,
                'scientific_basis'=> 'Phoniatrie et pédagogie vocale (Linklater, 1992) — la voix projetée depuis la résonance thoracique/abdominale est perçue comme plus autoritaire, plus chaleureuse et plus crédible qu'une voix portée par la gorge. Réduit également le risque de fatigue vocale.',
                'instructions'    => [
                    'Pose une main sur ton sternum et une sur ton ventre.',
                    'Inspire profondément par le nez, sens le ventre se soulever.',
                    'Expire en faisant « MMMM » — sens la vibration dans ta main sur le sternum.',
                    'Dis maintenant « Hm-hm » comme pour acquiescer — c'est ta zone de résonance naturelle.',
                    'Depuis cette zone, prononce : « Bonjour à tous » en imaginant envoyer ta voix au fond de la salle.',
                    'Augmente progressivement le volume en maintenant la résonance au sternum (pas dans la gorge).',
                    'Test de projection : dis une phrase à voix basse et à voix haute — la qualité doit rester la même.',
                ],
                'scoring'         => ['dimension' => 'impact_vocal', 'points' => 15],
            ],

            [
                'id'              => 'voix_02',
                'title'           => 'Diction — Exercices d'articulation et de précision',
                'category'        => 'impact_vocal',
                'duration_minutes'=> 4,
                'difficulty'      => 1,
                'scientific_basis'=> 'Phonétique articulatoire — une diction précise réduit la charge cognitive de l'auditoire (il n'a pas à « compléter » les sons manquants) et augmente la perception de compétence de l'orateur (Nygaard & Pisoni, 1998).',
                'instructions'    => [
                    'Exagère l'articulation pendant tout l'exercice — mâchoire ouverte, lèvres actives, langue précise.',
                    'Répète 3 fois à rythme croissant : « Un chasseur sachant chasser sait chasser sans son chien ».',
                    'Répète 3 fois : « Trois tortues trottaient sur un trottoir très étroit ».',
                    'Répète 3 fois : « Didon dîna dit-on du dos d'un dodu dindon ».',
                    'Maintenant lis un texte de 5 lignes en articulant chaque consonne finale.',
                    'Astuce : mettre un crayon (non taillé) en travers de la bouche force l'articulation — entraîne-toi 1 min avec, puis sans.',
                ],
                'scoring'         => ['dimension' => 'impact_vocal', 'points' => 10],
            ],

            [
                'id'              => 'voix_03',
                'title'           => 'Rythme et silences — L'art de la pause',
                'category'        => 'impact_vocal',
                'duration_minutes'=> 5,
                'difficulty'      => 3,
                'scientific_basis'=> 'Prosodie et persuasion (Mehrabian, 1971) — le rythme variable et les silences stratégiques maintiennent l'attention et signalent les moments importants. Les orateurs les plus persuasifs font des pauses 3x plus fréquentes que la moyenne (Leathers, 1997).',
                'instructions'    => [
                    'Choisis un paragraphe de 5 lignes sur un sujet que tu maîtrises.',
                    'Étape 1 — Repère les mots-clés : souligne les 5-7 mots les plus importants.',
                    'Étape 2 — Marque les pauses : mets un trait (/) là où tu veux t'arrêter 1 seconde, et un double trait (//) pour 2-3 secondes.',
                    'Les pauses vont AVANT les mots importants, pas après.',
                    'Lis le texte une première fois sans pauses (trop vite, monotone).',
                    'Lis-le une deuxième fois en respectant tes marques — le silence doit te sembler « trop long ».',
                    'Enregistre les deux versions et écoute la différence : laquelle est plus percutante ?',
                ],
                'scoring'         => ['dimension' => 'impact_vocal', 'points' => 20],
            ],

            [
                'id'              => 'voix_04',
                'title'           => 'Variations tonales — Sortir de la voix monotone',
                'category'        => 'impact_vocal',
                'duration_minutes'=> 4,
                'difficulty'      => 2,
                'scientific_basis'=> 'Paralangage et engagement (Burgoon et al., 1996) — une voix monotone réduit la rétention d'information de 40%. Les variations d'intonation (montante/descendante) guident l'attention et signalent l'importance des informations (Baddeley, 2000).',
                'instructions'    => [
                    'Dis la phrase « Nous allons parler d'un sujet important » de 5 façons différentes :',
                    '1. Avec enthousiasme (voix qui monte)',
                    '2. Avec gravité (voix basse et lente)',
                    '3. Avec complicité (comme un secret)',
                    '4. Avec autorité (ferme, posé, sans montée finale)',
                    '5. Avec curiosité (légère montée sur le dernier mot)',
                    'Enregistre-toi. Écoute : entends-tu bien 5 couleurs différentes ?',
                    'Exercice final : lis 3 phrases de ton prochain discours en utilisant au moins 3 couleurs vocales différentes.',
                ],
                'scoring'         => ['dimension' => 'impact_vocal', 'points' => 15],
            ],

            [
                'id'              => 'voix_05',
                'title'           => 'Échauffement vocal complet — 3 minutes avant de parler',
                'category'        => 'impact_vocal',
                'duration_minutes'=> 3,
                'difficulty'      => 1,
                'scientific_basis'=> 'Physiologie vocale — les cordes vocales sont des muscles. Un échauffement progressif réduit le risque de voix qui « déraille », améliore la qualité sonore dès les premières secondes et réduit la fatigue vocale sur la durée (Titze, 2006).',
                'instructions'    => [
                    '1. Bâillement exagéré × 3 — ouvre grand la bouche, soupire en expirant.',
                    '2. Lèvres en « pétarade » (brr) pendant 10 secondes en faisant varier la hauteur.',
                    '3. Langue dehors : tire la langue et dis « la-la-la » × 10.',
                    '4. Humming (fredonner bouche fermée) une mélodie simple, 20 secondes.',
                    '5. Sirène vocale : monte la voix du grave à l'aigu et redescends × 3.',
                    '6. Prononce les voyelles A-E-I-O-U en exagérant chaque forme buccale × 2.',
                    '7. Dis ta première phrase d'introduction à voix haute, avec intention. Ready.',
                ],
                'scoring'         => ['dimension' => 'impact_vocal', 'points' => 10],
            ],
        ];
    }

    /**
     * Citations inspirantes d'orateurs emblématiques — utilisées dans la page résultats.
     */
    public static function quotes(): array
    {
        return [
            ['quote' => 'Il faut que la parole soit un acte. Pas un bruit.', 'author' => 'Jean Jaurès'],
            ['quote' => 'Parler est un besoin, écouter est un art.', 'author' => 'Johann Wolfgang von Goethe'],
            ['quote' => 'Le courage n'est pas l'absence de peur, c'est la capacité de la surmonter.', 'author' => 'Nelson Mandela'],
            ['quote' => 'Il faut toujours viser la lune, car même en cas d'échec, on atterrit dans les étoiles.', 'author' => 'Oscar Wilde'],
            ['quote' => 'La communication est le problème de l'existence.', 'author' => 'Albert Camus'],
            ['quote' => 'Begin with the end in mind.', 'author' => 'Stephen R. Covey'],
            ['quote' => 'Soyez le changement que vous voulez voir dans le monde.', 'author' => 'Mahatma Gandhi'],
            ['quote' => 'Les mots sont, bien sûr, la drogue la plus puissante utilisée par l'humanité.', 'author' => 'Rudyard Kipling'],
            ['quote' => 'Si vous n'avez pas de conviction, vous ne pouvez convaincre personne.', 'author' => 'Winston Churchill'],
            ['quote' => 'Le secret d'un bon discours, c'est d'avoir quelque chose à dire.', 'author' => 'Gustave Flaubert'],
        ];
    }
}
