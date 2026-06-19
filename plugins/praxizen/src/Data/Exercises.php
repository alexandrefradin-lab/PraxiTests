<?php

namespace Praxis\Plugins\PraxiZen\Data;

/**
 * Catalogue scientifique des 20 exercices PraxiZen.
 *
 * Bases cliniques :
 *  - Cohérence cardiaque : McCraty & Childre (2010), HeartMath Institute
 *  - Respiration 4-7-8 : Dr A. Weil (2015) — activation parasympathique
 *  - Technique STOP : Kabat-Zinn (1994), MBSR — mindfulness-based stress reduction
 *  - Scan corporel / PMR : Jacobson (1938) — relaxation musculaire progressive
 *  - Restructuration cognitive : Beck (1979), TCC — pensées automatiques
 *  - Ancrage 5-4-3-2-1 : Shapiro (2001), EMDR — grounding sensoriel
 */
class Exercises
{
    /**
     * Retourne les 20 exercices guidés.
     *
     * Chaque entrée :
     *   id                string  identifiant unique
     *   title             string  nom de l'exercice
     *   category          string  respiration | mindfulness | cognitif | corporel
     *   duration_minutes  int     durée indicative (2-5 min)
     *   difficulty        int     1=débutant 2=intermédiaire 3=avancé
     *   scientific_basis  string  référence courte
     *   instructions      array   étapes guidées (texte)
     *   scoring           array   dimension(s) ciblée(s) avec poids relatif
     */
    public static function all(): array
    {
        return [
            // ─── RESPIRATION (6 exercices) ────────────────────────────────────

            [
                'id'               => 'zen-resp-01',
                'title'            => 'Cohérence cardiaque 365',
                'category'         => 'respiration',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => 'McCraty & Childre (2010) — HeartMath Institute',
                'instructions'     => [
                    'Assieds-toi confortablement, le dos droit, les pieds à plat sur le sol.',
                    'Ferme les yeux ou fixe un point devant toi.',
                    'Inspire lentement par le nez pendant 5 secondes en sentant ton ventre se gonfler.',
                    'Expire doucement par la bouche pendant 5 secondes en laissant ton ventre se dégonfler.',
                    'Répète ce cycle pendant 5 minutes (environ 6 respirations par minute).',
                    'Si ton esprit s\'échappe, ramène doucement l\'attention sur ton souffle.',
                    'À la fin, observe les sensations dans ton corps avant de reprendre tes activités.',
                ],
                'scoring' => [
                    'regulation_emotionnelle' => 0.8,
                    'gestion_somatique'        => 0.9,
                ],
            ],

            [
                'id'               => 'zen-resp-02',
                'title'            => 'Respiration 4-7-8 (Dr Weil)',
                'category'         => 'respiration',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => 'Dr Andrew Weil (2015) — activation du nerf vague parasympathique',
                'instructions'     => [
                    'Vide complètement tes poumons par la bouche avec un son audible.',
                    'Ferme la bouche et inspire silencieusement par le nez en comptant mentalement jusqu\'à 4.',
                    'Retiens ta respiration en comptant jusqu\'à 7.',
                    'Expire complètement par la bouche avec un son sonore en comptant jusqu\'à 8.',
                    'C\'est un cycle. Répète-le 4 fois pour les deux premières semaines, puis 8 fois ensuite.',
                    'Pratique deux fois par jour pour des effets cumulatifs sur l\'anxiété.',
                ],
                'scoring' => [
                    'gestion_somatique'        => 1.0,
                    'regulation_emotionnelle'  => 0.7,
                ],
            ],

            [
                'id'               => 'zen-resp-03',
                'title'            => 'Respiration en boîte (Box Breathing)',
                'category'         => 'respiration',
                'duration_minutes' => 4,
                'difficulty'       => 1,
                'scientific_basis' => 'Méthode US Navy SEALs — régulation du système nerveux autonome',
                'instructions'     => [
                    'Expire lentement tout l\'air de tes poumons.',
                    'Inspire par le nez pendant 4 secondes en gonflant le ventre.',
                    'Retiens le souffle (poumons pleins) pendant 4 secondes.',
                    'Expire par la bouche pendant 4 secondes.',
                    'Retiens (poumons vides) pendant 4 secondes.',
                    'Répète ce cycle 4 à 8 fois.',
                    'Visualise mentalement un carré pendant la pratique pour ancrer l\'attention.',
                ],
                'scoring' => [
                    'gestion_somatique'        => 0.9,
                    'mindfulness'              => 0.6,
                ],
            ],

            [
                'id'               => 'zen-resp-04',
                'title'            => 'Respiration alternée (Nadi Shodhana)',
                'category'         => 'respiration',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Yoga Pranayama — équilibre hémisphérique cérébral (Telles et al., 1996)',
                'instructions'     => [
                    'Assieds-toi en position confortable, colonne vertébrale allongée.',
                    'Pose la main gauche sur le genou. Amène la main droite au visage.',
                    'Ferme la narine droite avec le pouce droit et inspire par la narine gauche (4 sec).',
                    'Ferme les deux narines, retiens le souffle (4 sec).',
                    'Ouvre la narine droite, expire par la droite (8 sec).',
                    'Inspire par la droite (4 sec), retiens (4 sec), expire par la gauche (8 sec).',
                    'C\'est un cycle. Répète 5 à 10 cycles.',
                ],
                'scoring' => [
                    'regulation_emotionnelle' => 0.7,
                    'mindfulness'              => 0.8,
                ],
            ],

            [
                'id'               => 'zen-resp-05',
                'title'            => 'Soupir physiologique',
                'category'         => 'respiration',
                'duration_minutes' => 2,
                'difficulty'       => 1,
                'scientific_basis' => 'Huberman Lab (2023) — double inspiration nasale + expiration lente',
                'instructions'     => [
                    'Prends une grande inspiration par le nez.',
                    'Sans expirer, ajoute une deuxième petite inspiration courte par le nez pour maximiser l\'ouverture des alvéoles.',
                    'Expire lentement et complètement par la bouche (6-8 secondes).',
                    'Répète 3 à 5 fois.',
                    'Ce mécanisme est le moyen le plus rapide pour le corps de redescendre le CO₂ et apaiser le stress aigu.',
                ],
                'scoring' => [
                    'gestion_somatique'        => 1.0,
                    'regulation_emotionnelle'  => 0.6,
                ],
            ],

            [
                'id'               => 'zen-resp-06',
                'title'            => 'Respiration apaisante 2:1',
                'category'         => 'respiration',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => 'Brown & Gerbarg (2012) — expiration longue active le SNP',
                'instructions'     => [
                    'Inspire par le nez pendant 4 secondes.',
                    'Expire par la bouche pendant 8 secondes (deux fois plus longtemps).',
                    'L\'expiration prolongée stimule le nerf vague et déclenche la réponse de relaxation.',
                    'Répète pendant 3 à 5 minutes.',
                    'Idéal avant une réunion stressante ou pour s\'endormir.',
                ],
                'scoring' => [
                    'gestion_somatique'        => 0.9,
                    'regulation_emotionnelle'  => 0.7,
                ],
            ],

            // ─── MINDFULNESS / PLEINE CONSCIENCE (5 exercices) ───────────────

            [
                'id'               => 'zen-mind-01',
                'title'            => 'Technique STOP',
                'category'         => 'mindfulness',
                'duration_minutes' => 2,
                'difficulty'       => 1,
                'scientific_basis' => 'Kabat-Zinn (1994) — MBSR, pause pleine conscience',
                'instructions'     => [
                    'S — STOP : Arrête ce que tu fais une seconde.',
                    'T — TAKE A BREATH : Prends une longue inspiration et une longue expiration consciente.',
                    'O — OBSERVE : Observe ce qui se passe en toi (pensées, émotions, sensations) sans juger.',
                    'P — PROCEED : Reprends ton activité avec cette nouvelle conscience.',
                    'Pratique 3 à 5 fois par jour, notamment aux moments de transition.',
                ],
                'scoring' => [
                    'mindfulness'              => 1.0,
                    'regulation_emotionnelle'  => 0.6,
                ],
            ],

            [
                'id'               => 'zen-mind-02',
                'title'            => 'Ancrage sensoriel 5-4-3-2-1',
                'category'         => 'mindfulness',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => 'Shapiro (2001) — EMDR grounding, activation du cortex préfrontal',
                'instructions'     => [
                    'Regarde autour de toi et nomme mentalement 5 choses que tu VOIS.',
                    'Nomme 4 choses que tu TOUCHES (la chaise, l\'air, tes vêtements, etc.).',
                    'Nomme 3 choses que tu ENTENDS (bruits proches et lointains).',
                    'Nomme 2 choses que tu SENS (odeurs présentes ou imaginées).',
                    'Nomme 1 chose que tu GOÛTES.',
                    'Cette technique interrompt la boucle anxieuse en ramenant l\'attention dans le présent sensoriel.',
                ],
                'scoring' => [
                    'mindfulness'              => 1.0,
                    'regulation_emotionnelle'  => 0.8,
                    'resilience'               => 0.4,
                ],
            ],

            [
                'id'               => 'zen-mind-03',
                'title'            => 'Observation des pensées (nuages)',
                'category'         => 'mindfulness',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'ACT — Hayes et al. (1999), défusion cognitive',
                'instructions'     => [
                    'Ferme les yeux et imagine un ciel bleu.',
                    'Chaque pensée qui surgit est un nuage qui traverse ce ciel.',
                    'Observe le nuage sans t\'y accrocher, sans le repousser.',
                    'Laisse-le passer naturellement et reviens au ciel bleu (ta conscience).',
                    'Si tu te retrouves dans le nuage (emporté par la pensée), remarque-le et reviens au ciel.',
                    'Tu n\'es pas tes pensées — tu es le ciel qui les observe.',
                ],
                'scoring' => [
                    'mindfulness'              => 1.0,
                    'cognitive_reframing'      => 0.6,
                ],
            ],

            [
                'id'               => 'zen-mind-04',
                'title'            => 'Scan de conscience corporelle',
                'category'         => 'mindfulness',
                'duration_minutes' => 4,
                'difficulty'       => 1,
                'scientific_basis' => 'Kabat-Zinn (1990) — Body Scan MBSR',
                'instructions'     => [
                    'Ferme les yeux. Porte ton attention sur le sommet de ton crâne.',
                    'Descends lentement : front, yeux, mâchoire (remarque les tensions).',
                    'Cou, épaules — est-ce que tu les soulèves ? Laisse-les tomber.',
                    'Poitrine, ventre — observe le mouvement de la respiration.',
                    'Dos, lombaires — y a-t-il une pression ?',
                    'Bras, mains, doigts — remarque la chaleur ou le fourmillement.',
                    'Jambes, pieds — sens le contact avec le sol.',
                    'Prends une grande inspiration et, en expirant, relâche tout le corps.',
                ],
                'scoring' => [
                    'mindfulness'       => 0.9,
                    'gestion_somatique' => 0.7,
                ],
            ],

            [
                'id'               => 'zen-mind-05',
                'title'            => 'Minute de pleine conscience sonore',
                'category'         => 'mindfulness',
                'duration_minutes' => 2,
                'difficulty'       => 1,
                'scientific_basis' => 'Teasdale et al. (2000) — MBCT, ancrage auditif',
                'instructions'     => [
                    'Pose tes deux pieds à plat sur le sol.',
                    'Ferme les yeux et porte toute ton attention sur les sons présents.',
                    'N\'essaie pas d\'identifier leur source — remarque simplement leurs qualités : fort/doux, proche/lointain, continu/intermittent.',
                    'Quand ton esprit dérive, reviens aux sons sans te juger.',
                    'Au bout de 60 à 90 secondes, ouvre les yeux doucement.',
                ],
                'scoring' => [
                    'mindfulness'              => 1.0,
                    'regulation_emotionnelle'  => 0.5,
                ],
            ],

            // ─── CORPOREL / SOMATIQUE (4 exercices) ──────────────────────────

            [
                'id'               => 'zen-corp-01',
                'title'            => 'Relaxation musculaire progressive (Jacobson)',
                'category'         => 'corporel',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => 'Jacobson (1938) — Progressive Muscle Relaxation',
                'instructions'     => [
                    'Allonge-toi ou assieds-toi confortablement.',
                    'Commence par les pieds : contracte fortement les orteils pendant 5 secondes.',
                    'Relâche d\'un coup et remarque la différence pendant 10 secondes.',
                    'Monte vers les mollets, puis les cuisses, le ventre, les poings, les bras, les épaules, le visage.',
                    'Pour chaque groupe : 5 secondes de contraction maximale, puis 10 secondes de relâchement total.',
                    'Prends conscience du contraste entre tension et relâchement — c\'est le signal de détente.',
                ],
                'scoring' => [
                    'gestion_somatique'        => 1.0,
                    'mindfulness'              => 0.5,
                ],
            ],

            [
                'id'               => 'zen-corp-02',
                'title'            => 'Tremblement thérapeutique TRE',
                'category'         => 'corporel',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Berceli (2005) — Trauma Releasing Exercises, neurogenic tremors',
                'instructions'     => [
                    'Allonge-toi sur le dos, genoux fléchis, pieds à plat.',
                    'Écarte légèrement les genoux puis rapproche-les — répète pour fatiguer légèrement les muscles des cuisses (30 sec).',
                    'Laisse les genoux vibrer naturellement, sans les contrôler.',
                    'Si aucune vibration ne vient, pousse légèrement sur les pieds pour déclencher le tremblement.',
                    'Laisse la vibration se propager dans le bassin, le ventre, la colonne.',
                    'Continue 5 minutes puis étire-toi doucement.',
                    'Note : ce tremblement est neurologique, non émotionnel — c\'est la réponse naturelle du corps au stress.',
                ],
                'scoring' => [
                    'gestion_somatique'        => 1.0,
                    'resilience'               => 0.5,
                ],
            ],

            [
                'id'               => 'zen-corp-03',
                'title'            => 'Étirement cervical anti-stress',
                'category'         => 'corporel',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => 'Thayer et al. (2009) — lien tonus musculaire cervical / anxiété',
                'instructions'     => [
                    'Assieds-toi droit, épaules relâchées.',
                    'Incline doucement la tête vers l\'épaule droite, oreille vers épaule (sans la lever).',
                    'Maintiens 30 secondes en respirant profondément.',
                    'Change de côté. Répète 2 fois de chaque côté.',
                    'Incline doucement la tête en avant, menton vers poitrine, 30 secondes.',
                    'Effectue 3 lents demi-cercles d\'oreille à oreille (pas en arrière).',
                    'Ces tensions cervicales sont l\'un des premiers dépôts physiques du stress chronique.',
                ],
                'scoring' => [
                    'gestion_somatique'        => 0.9,
                    'mindfulness'              => 0.4,
                ],
            ],

            [
                'id'               => 'zen-corp-04',
                'title'            => 'Posture de puissance (Power Posing)',
                'category'         => 'corporel',
                'duration_minutes' => 2,
                'difficulty'       => 1,
                'scientific_basis' => 'Carney, Cuddy & Yap (2010) — posture expansive et cortisol/testostérone',
                'instructions'     => [
                    'Lève-toi. Tiens-toi debout, pieds écartés à la largeur des épaules.',
                    'Pose les mains sur les hanches, épaules en arrière, poitrine ouverte.',
                    'Relève le menton légèrement (pas trop — rester naturel).',
                    'Maintiens cette posture pendant 2 minutes en respirant normalement.',
                    'Variante assise : mets les mains derrière la nuque, coudes écartés, penche-toi légèrement en arrière.',
                    'La posture influençe l\'état interne : un corps confiant envoie des signaux de sécurité au cerveau.',
                ],
                'scoring' => [
                    'resilience'               => 0.8,
                    'regulation_emotionnelle'  => 0.6,
                ],
            ],

            // ─── COGNITIF / RESTRUCTURATION (5 exercices) ────────────────────

            [
                'id'               => 'zen-cog-01',
                'title'            => 'Questionnement socratique (TCC)',
                'category'         => 'cognitif',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Beck (1979) — Thérapie cognitive, restructuration des pensées automatiques',
                'instructions'     => [
                    'Identifie la pensée stressante ("Je vais rater cette présentation").',
                    'Demande-toi : Quelle est la preuve QUE cette pensée est vraie ?',
                    'Demande-toi : Quelle est la preuve QU\'ELLE N\'EST PAS vraie ?',
                    'Existe-t-il une explication alternative plus équilibrée ?',
                    'Si cette pensée était vraie, serait-ce réellement si catastrophique ? Pourrais-tu y faire face ?',
                    'Quelle serait la pensée la plus utile et la plus réaliste dans cette situation ?',
                    'Note la pensée réévaluée et remarque le changement dans ton niveau de stress (0-10).',
                ],
                'scoring' => [
                    'cognitive_reframing'      => 1.0,
                    'resilience'               => 0.6,
                ],
            ],

            [
                'id'               => 'zen-cog-02',
                'title'            => 'Journal des inquiétudes (worry time)',
                'category'         => 'cognitif',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Borkovec et al. (1983) — Worry postponement therapy',
                'instructions'     => [
                    'Dès qu\'une inquiétude surgit, écris-la sur un papier ou dans ton téléphone (30 sec).',
                    'Note : "Je m\'occuperai de ça pendant mon temps d\'inquiétudes désigné."',
                    'Reprends ton activité. Si l\'inquiétude revient, dis-lui "pas maintenant, je t\'ai notée."',
                    'Une fois par jour (ex. 17h00), prends 10 minutes pour examiner ta liste.',
                    'Pour chaque item : est-il encore pertinent ? Puis-je faire quelque chose ? Si oui, je planifie. Si non, je lâche.',
                    'Cette technique réduit la rumination en compartimentant les inquiétudes dans le temps.',
                ],
                'scoring' => [
                    'cognitive_reframing'      => 0.9,
                    'regulation_emotionnelle'  => 0.8,
                ],
            ],

            [
                'id'               => 'zen-cog-03',
                'title'            => 'Recadrage par la perspective temporelle',
                'category'         => 'cognitif',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => 'Zimbardo & Boyd (1999) — Time Perspective Therapy',
                'instructions'     => [
                    'Pense à la situation stressante qui t\'occupe en ce moment.',
                    'Pose-toi la question : Est-ce que cela sera encore important dans 5 minutes ?',
                    'Est-ce que cela sera encore important dans 5 semaines ?',
                    'Est-ce que cela sera encore important dans 5 mois ?',
                    'Est-ce que cela sera encore important dans 5 ans ?',
                    'Cette technique aide à calibrer la réponse émotionnelle à la vraie importance de l\'événement.',
                    'Note à quel niveau le problème "disparaît" — c\'est là son vrai poids.',
                ],
                'scoring' => [
                    'cognitive_reframing'      => 1.0,
                    'resilience'               => 0.7,
                ],
            ],

            [
                'id'               => 'zen-cog-04',
                'title'            => 'Lettre de compassion à soi-même',
                'category'         => 'cognitif',
                'duration_minutes' => 5,
                'difficulty'       => 3,
                'scientific_basis' => 'Neff (2003) — Self-compassion scale, régulation émotionnelle via auto-compassion',
                'instructions'     => [
                    'Pense à une situation actuelle difficile qui te stresse ou te fait souffrir.',
                    'Imagine qu\'un ami traverse exactement la même situation.',
                    'Écris-lui une lettre courte (5-7 lignes) avec chaleur, bienveillance et compréhension.',
                    'Relis cette lettre et remplace "mon ami" par "moi".',
                    'Remarque comme tu te parlerais différemment si tu étais ton propre meilleur ami.',
                    'La recherche montre que l\'auto-compassion réduit le cortisol et augmente la résilience.',
                ],
                'scoring' => [
                    'cognitive_reframing'      => 0.8,
                    'resilience'               => 0.9,
                    'regulation_emotionnelle'  => 0.6,
                ],
            ],

            [
                'id'               => 'zen-cog-05',
                'title'            => 'Visualisation de la ressource',
                'category'         => 'cognitif',
                'duration_minutes' => 4,
                'difficulty'       => 2,
                'scientific_basis' => 'Bandura (1977) — Auto-efficacité ; Seligman (2011) — PERMA',
                'instructions'     => [
                    'Ferme les yeux. Rappelle-toi un moment de ta vie où tu t\'es senti(e) compétent(e), calme et en contrôle.',
                    'Revois la scène en détail : où étais-tu ? Qui était présent ? Qu\'est-ce que tu entendais ?',
                    'Remarque les sensations physiques associées à ce moment de réussite dans ton corps.',
                    'Amplifie ces sensations — imagine la chaleur, la légèreté, la confiance qui se répandent.',
                    'Crée une ancre : presse doucement le pouce et l\'index de ta main droite ensemble.',
                    'Ce geste deviendra le déclencheur de cet état ressource dans les situations stressantes.',
                ],
                'scoring' => [
                    'resilience'               => 1.0,
                    'regulation_emotionnelle'  => 0.7,
                    'cognitive_reframing'      => 0.5,
                ],
            ],
        ];
    }

    /**
     * Retourne les 5 dimensions évaluées avec leurs métadonnées.
     */
    public static function dimensions(): array
    {
        return [
            'regulation_emotionnelle' => [
                'label'       => 'Régulation émotionnelle',
                'color'       => '#7C3AED',
                'icon'        => '🧘',
                'description' => 'Capacité à reconnaître, comprendre et moduler ses émotions face au stress.',
                'low_advice'  => 'Pratique quotidiennement la cohérence cardiaque et la respiration 4-7-8 pour renforcer ton système nerveux parasympathique.',
            ],
            'resilience' => [
                'label'       => 'Résilience',
                'color'       => '#059669',
                'icon'        => '🌱',
                'description' => 'Capacité à rebondir et maintenir l\'équilibre face aux défis professionnels.',
                'low_advice'  => 'Explore la visualisation de ressource et la lettre de compassion pour construire une base psychologique solide.',
            ],
            'gestion_somatique' => [
                'label'       => 'Gestion somatique',
                'color'       => '#DC2626',
                'icon'        => '💪',
                'description' => 'Capacité à détecter et relâcher les tensions physiques liées au stress.',
                'low_advice'  => 'La relaxation musculaire progressive de Jacobson et les exercices de respiration sont tes outils prioritaires.',
            ],
            'mindfulness' => [
                'label'       => 'Pleine conscience',
                'color'       => '#0284C7',
                'icon'        => '🌊',
                'description' => 'Capacité à rester ancré(e) dans le moment présent sans être débordé(e) par les pensées.',
                'low_advice'  => 'Commence par 2 minutes d\'ancrage 5-4-3-2-1 plusieurs fois par jour pour entraîner le muscle attentionnel.',
            ],
            'cognitive_reframing' => [
                'label'       => 'Recadrage cognitif',
                'color'       => '#EA580C',
                'icon'        => '💡',
                'description' => 'Capacité à remettre en question les pensées catastrophiques et adopter une perspective plus équilibrée.',
                'low_advice'  => 'Le questionnement socratique (TCC) et la perspective temporelle t\'aideront à neutraliser les pensées automatiques.',
            ],
        ];
    }

    /**
     * Retourne les 20 questions Likert 4 niveaux pour l'évaluation initiale.
     * 4 questions par dimension (total : 20).
     */
    public static function questions(): array
    {
        return [
            // Régulation émotionnelle
            [
                'id'        => 'q-re-01',
                'prompt'    => 'Quand je suis sous pression, je réussis à garder le contrôle de mes réactions émotionnelles.',
                'scoring'   => ['dimension' => 'regulation_emotionnelle', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-re-02',
                'prompt'    => 'Mes émotions négatives au travail (frustration, anxiété) disparaissent rapidement sans que j\'y pense trop.',
                'scoring'   => ['dimension' => 'regulation_emotionnelle', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-re-03',
                'prompt'    => 'Je me laisse souvent envahir par mes émotions au point de ne plus pouvoir travailler efficacement.',
                'scoring'   => ['dimension' => 'regulation_emotionnelle', 'reversed' => true, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-re-04',
                'prompt'    => 'Je peux me calmer délibérément lorsque je sens le stress monter.',
                'scoring'   => ['dimension' => 'regulation_emotionnelle', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            // Résilience
            [
                'id'        => 'q-res-01',
                'prompt'    => 'Après un échec ou une déception professionnelle, je retrouve mon énergie assez rapidement.',
                'scoring'   => ['dimension' => 'resilience', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-res-02',
                'prompt'    => 'Je perçois les périodes difficiles comme des occasions d\'apprendre quelque chose sur moi.',
                'scoring'   => ['dimension' => 'resilience', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-res-03',
                'prompt'    => 'Quand la pression est forte, j\'ai tendance à baisser les bras ou à me replier sur moi-même.',
                'scoring'   => ['dimension' => 'resilience', 'reversed' => true, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-res-04',
                'prompt'    => 'Je garde espoir et une vision positive même face aux obstacles professionnels.',
                'scoring'   => ['dimension' => 'resilience', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            // Gestion somatique
            [
                'id'        => 'q-gs-01',
                'prompt'    => 'Je remarque rapidement quand le stress se manifeste dans mon corps (tensions, maux de tête, fatigue).',
                'scoring'   => ['dimension' => 'gestion_somatique', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-gs-02',
                'prompt'    => 'Je peux relâcher les tensions musculaires volontairement lorsque j\'y prête attention.',
                'scoring'   => ['dimension' => 'gestion_somatique', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-gs-03',
                'prompt'    => 'Les symptômes physiques du stress (mâchoire serrée, dos tendu, sommeil perturbé) m\'affectent régulièrement.',
                'scoring'   => ['dimension' => 'gestion_somatique', 'reversed' => true, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-gs-04',
                'prompt'    => 'Je prends soin de mon corps de façon régulière (respiration, mouvement, pauses) pour prévenir le stress.',
                'scoring'   => ['dimension' => 'gestion_somatique', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            // Mindfulness
            [
                'id'        => 'q-mf-01',
                'prompt'    => 'Je suis capable de rester concentré(e) sur ce que je fais sans que mon esprit parte dans tous les sens.',
                'scoring'   => ['dimension' => 'mindfulness', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-mf-02',
                'prompt'    => 'J\'arrive à observer mes pensées stressantes sans m\'y perdre, comme si je les regardais de loin.',
                'scoring'   => ['dimension' => 'mindfulness', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-mf-03',
                'prompt'    => 'Je me retrouve souvent à ruminer le passé ou à m\'inquiéter du futur au lieu de vivre le moment présent.',
                'scoring'   => ['dimension' => 'mindfulness', 'reversed' => true, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-mf-04',
                'prompt'    => 'Je prends régulièrement quelques instants dans ma journée pour simplement observer ce qui se passe en moi.',
                'scoring'   => ['dimension' => 'mindfulness', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            // Recadrage cognitif
            [
                'id'        => 'q-cr-01',
                'prompt'    => 'Face à un problème stressant, je cherche naturellement à voir les choses sous plusieurs angles.',
                'scoring'   => ['dimension' => 'cognitive_reframing', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-cr-02',
                'prompt'    => 'Je peux remettre en question mes pensées catastrophiques et trouver une perspective plus réaliste.',
                'scoring'   => ['dimension' => 'cognitive_reframing', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-cr-03',
                'prompt'    => 'Quand quelque chose se passe mal, j\'ai tendance à dramatiser et à voir le pire scénario possible.',
                'scoring'   => ['dimension' => 'cognitive_reframing', 'reversed' => true, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
            [
                'id'        => 'q-cr-04',
                'prompt'    => 'Je trouve généralement des solutions créatives ou des ressources insoupçonnées quand je suis sous pression.',
                'scoring'   => ['dimension' => 'cognitive_reframing', 'reversed' => false, 'weight' => 1],
                'options'   => self::likert4Options(),
            ],
        ];
    }

    private static function likert4Options(): array
    {
        return [
            ['value' => 1, 'label' => 'Jamais'],
            ['value' => 2, 'label' => 'Rarement'],
            ['value' => 3, 'label' => 'Souvent'],
            ['value' => 4, 'label' => 'Toujours'],
        ];
    }
}
