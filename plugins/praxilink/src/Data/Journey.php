<?php

namespace Praxis\Plugins\PraxiLink\Data;

/**
 * Parcours 60 jours PraxiLink — Communication assertive.
 *
 * Science des habitudes :
 *  - Phillippa Lally (UCL, 2010) : 66 jours en moyenne pour ancrer une habitude
 *  - BJ Fogg (Tiny Habits) : anchor habit = accrocher le nouveau comportement à un existant
 *  - James Clear (Atomic Habits) : cue → craving → response → reward
 *
 * 4 phases :
 *  - Phase 1 Découverte    (J1-15)  : 5 min, prise de conscience du style communicant
 *  - Phase 2 Installation  (J16-30) : 6-7 min, écoute active et CNV
 *  - Phase 3 Renforcement  (J31-45) : 8 min, assertivité et gestion des conflits
 *  - Phase 4 Maîtrise      (J46-60) : 8-10 min, scénarios complexes et leadership
 */
class Journey
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 1 — ÉCOUTE ACTIVE : Observer son style naturel (J1-7)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 1,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Identifier mon style communicant',
                'exercise_ref'     => 'ass-01',
                'duration_minutes' => 5,
                'anchor'           => 'Après votre première réunion de la journée',
                'intention'        => "Aujourd'hui, j'observe comment je communique naturellement.",
                'micro_habit'      => "Après chaque échange, notez mentalement : ai-je écouté ou attendu de parler ?",
                'reward'           => "Vous venez de faire le premier pas vers une communication consciente.",
                'weekly_theme'     => 'Observer son style naturel',
                'tip_science'      => "On écoute à 25 % de notre capacité en moyenne (Nichols, 1957). Prendre conscience est déjà changer.",
            ],

            [
                'day'              => 2,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Le reflet empathique',
                'exercise_ref'     => 'ea-01',
                'duration_minutes' => 5,
                'anchor'           => 'Après votre pause café du matin',
                'intention'        => "Aujourd'hui, je pratique reformuler ce que j'entends.",
                'micro_habit'      => "Une fois aujourd'hui, reformulez ce que dit votre interlocuteur avant de répondre.",
                'reward'           => "Vous avez offert à quelqu'un le sentiment d'être vraiment entendu.",
                'weekly_theme'     => 'Observer son style naturel',
                'tip_science'      => "Carl Rogers (1951) : être entendu est l'un des besoins relationnels les plus fondamentaux de l'être humain.",
            ],

            [
                'day'              => 3,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Questions ouvertes vs fermées',
                'exercise_ref'     => 'ea-02',
                'duration_minutes' => 5,
                'anchor'           => 'Avant votre premier entretien ou échange du jour',
                'intention'        => "Aujourd'hui, j'utilise au moins deux questions ouvertes.",
                'micro_habit'      => "Avant de poser une question, demandez-vous : est-elle ouverte ou fermée ?",
                'reward'           => "Chaque question ouverte invite l'autre à s'exprimer davantage.",
                'weekly_theme'     => 'Observer son style naturel',
                'tip_science'      => "Gordon (1970) : les questions ouvertes élargissent l'espace de parole et réduisent la résistance.",
            ],

            [
                'day'              => 4,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Mes obstacles à l\'écoute',
                'exercise_ref'     => 'ea-04',
                'duration_minutes' => 5,
                'anchor'           => 'En fin de matinée, après une conversation',
                'intention'        => "Aujourd'hui, je repère mes propres barrières à l'écoute.",
                'micro_habit'      => "Notez le premier obstacle que vous avez rencontré aujourd'hui dans une conversation.",
                'reward'           => "Nommer un obstacle, c'est déjà commencer à le dépasser.",
                'weekly_theme'     => 'Observer son style naturel',
                'tip_science'      => "Gordon (1970) a identifié 12 obstacles universels à la communication. Le plus fréquent : donner des conseils avant d'avoir vraiment compris.",
            ],

            [
                'day'              => 5,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Passif, agressif ou assertif ?',
                'exercise_ref'     => 'ass-01',
                'duration_minutes' => 5,
                'anchor'           => 'Après le déjeuner',
                'intention'        => "Aujourd'hui, j'identifie mon style dominant dans un échange difficile.",
                'micro_habit'      => "Pensez à un échange récent : étiez-vous passif, agressif ou assertif ?",
                'reward'           => "La conscience de son style est le levier de tout changement durable.",
                'weekly_theme'     => 'Observer son style naturel',
                'tip_science'      => "Alberti & Emmons (1970) : l'assertivité n'est pas un trait de personnalité, c'est une compétence apprise.",
            ],

            [
                'day'              => 6,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Observation vs évaluation',
                'exercise_ref'     => 'cnv-02',
                'duration_minutes' => 5,
                'anchor'           => 'Avant de rédiger un email délicat',
                'intention'        => "Aujourd'hui, je distingue les faits de mes interprétations.",
                'micro_habit'      => "Dans votre prochain email, remplacez une évaluation par un fait observable.",
                'reward'           => "Les faits créent un terrain commun ; les évaluations créent des murs.",
                'weekly_theme'     => 'Observer son style naturel',
                'tip_science'      => "Rosenberg (1999) : la confusion entre observation et évaluation est à l'origine de 80 % des malentendus.",
            ],

            [
                'day'              => 7,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Bilan de semaine 1',
                'exercise_ref'     => 'ea-01',
                'duration_minutes' => 5,
                'anchor'           => 'Le vendredi en fin d\'après-midi',
                'intention'        => "Cette semaine, j'ai commencé à observer ma communication.",
                'micro_habit'      => "Notez une chose que vous avez découverte sur votre style communicant cette semaine.",
                'reward'           => "7 jours de conscience communicante — c'est le fondement de tout le reste.",
                'weekly_theme'     => 'Observer son style naturel',
                'tip_science'      => "Fogg (2019) : une habitude bien ancrée commence par une période d'observation sans jugement.",
            ],

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 2 — ÉCOUTE ACTIVE : Reformulation et présence (J8-14)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 8,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'La reformulation de fond',
                'exercise_ref'     => 'ea-03',
                'duration_minutes' => 5,
                'anchor'           => 'Après votre première réunion de la journée',
                'intention'        => "Aujourd'hui, je m'entraîne à reformuler sans interpréter.",
                'micro_habit'      => "Lors d'une discussion, reformulez une fois ce que dit l'autre avant de répondre.",
                'reward'           => "La reformulation, c'est le cadeau de la compréhension.",
                'weekly_theme'     => 'Reformulation et présence',
                'tip_science'      => "Mucchielli (1983) : la reformulation de fond restitue le sens sans jugement ni interprétation ajoutée.",
            ],

            [
                'day'              => 9,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Empathie vs sympathie',
                'exercise_ref'     => 'emp-01',
                'duration_minutes' => 5,
                'anchor'           => 'Avant un entretien individuel',
                'intention'        => "Aujourd'hui, je pratique l'empathie sans me perdre.",
                'micro_habit'      => "Face à une difficulté partagée, restez avec l'autre plutôt que de ramener à vous.",
                'reward'           => "L'empathie crée un lien authentique sans absorber la souffrance.",
                'weekly_theme'     => 'Reformulation et présence',
                'tip_science'      => "Brené Brown (2010) : l'empathie dit 'je suis avec toi'. La sympathie dit 'j'ai mal pour toi'.",
            ],

            [
                'day'              => 10,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Nommer ses émotions avec précision',
                'exercise_ref'     => 'cnv-03',
                'duration_minutes' => 5,
                'anchor'           => 'Après un moment de tension dans la journée',
                'intention'        => "Aujourd'hui, je nomme précisément ce que je ressens.",
                'micro_habit'      => "Chaque fois que vous ressentez quelque chose, nommez-le avec un mot précis.",
                'reward'           => "Nommer une émotion, c'est déjà la réguler.",
                'weekly_theme'     => 'Reformulation et présence',
                'tip_science'      => "Rosenberg (1999) : la granularité émotionnelle améliore la clarté de la communication et réduit les conflits.",
            ],

            [
                'day'              => 11,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Le message en langage « je »',
                'exercise_ref'     => 'emp-03',
                'duration_minutes' => 5,
                'anchor'           => 'Avant de donner un retour à quelqu\'un',
                'intention'        => "Aujourd'hui, je transforme mes « tu » en « je ».",
                'micro_habit'      => "Avant d'envoyer un message, vérifiez : dit-il ce que vous ressentez ou accuse-t-il l'autre ?",
                'reward'           => "Le message en « je » ouvre la conversation au lieu de la fermer.",
                'weekly_theme'     => 'Reformulation et présence',
                'tip_science'      => "Gordon (1970) : le message en « tu » génère de la défensivité ; le message en « je » invite à la coopération.",
            ],

            [
                'day'              => 12,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Recevoir du feedback positif',
                'exercise_ref'     => 'fb-02',
                'duration_minutes' => 5,
                'anchor'           => 'Après avoir reçu un compliment',
                'intention'        => "Aujourd'hui, j'accueille les retours positifs sans les minimiser.",
                'micro_habit'      => "Quand quelqu'un vous complimente, dites simplement merci et reliez-le à un effort réel.",
                'reward'           => "Recevoir un compliment avec grâce est une compétence communicante à part entière.",
                'weekly_theme'     => 'Reformulation et présence',
                'tip_science'      => "Heen & Stone (2014) : minimiser les compliments décourage les retours et érode l'estime de soi.",
            ],

            [
                'day'              => 13,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Communication interculturelle',
                'exercise_ref'     => 'emp-02',
                'duration_minutes' => 5,
                'anchor'           => 'Avant une réunion avec des interlocuteurs variés',
                'intention'        => "Aujourd'hui, je cherche à comprendre le cadre de référence des autres.",
                'micro_habit'      => "Interrogez-vous : mon style de communication est-il adapté à mon interlocuteur ?",
                'reward'           => "S'adapter à l'autre, c'est une forme de respect profond.",
                'weekly_theme'     => 'Reformulation et présence',
                'tip_science'      => "Hall (1976) : les cultures haut contexte communiquent dans l'implicite ; les cultures bas contexte dans l'explicite.",
            ],

            [
                'day'              => 14,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Bilan de la phase Découverte',
                'exercise_ref'     => 'ea-03',
                'duration_minutes' => 5,
                'anchor'           => 'Le vendredi en fin d\'après-midi',
                'intention'        => "Ces deux semaines m'ont offert un miroir sur ma communication.",
                'micro_habit'      => "Notez votre plus grande découverte sur vous-même en tant que communicant.",
                'reward'           => "14 jours de conscience — vous avez posé les fondations d'une communication plus saine.",
                'weekly_theme'     => 'Reformulation et présence',
                'tip_science'      => "Lally et al. (2010) : les deux premières semaines sont cruciales pour établir le contexte de l'habitude.",
            ],

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 3 — CNV : Observation, sentiments, besoins (J15-21)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 15,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Introduction à la CNV',
                'exercise_ref'     => 'cnv-02',
                'duration_minutes' => 6,
                'anchor'           => 'Après votre café du matin',
                'intention'        => "Aujourd'hui, j'apprends à séparer les faits de mes jugements.",
                'micro_habit'      => "Dans chaque échange tendu, identifiez un fait pur avant de réagir.",
                'reward'           => "La CNV est une langue que vous apprenez. Aujourd'hui, vous avez appris votre premier mot.",
                'weekly_theme'     => 'Observation, sentiments, besoins',
                'tip_science'      => "Rosenberg (1999) : la CNV repose sur 4 étapes — Observation, Sentiment, Besoin, Demande (OSBD).",
            ],

            [
                'day'              => 16,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Construire un message OSBD',
                'exercise_ref'     => 'cnv-01',
                'duration_minutes' => 6,
                'anchor'           => 'Avant un échange potentiellement difficile',
                'intention'        => "Aujourd'hui, je structure un message selon OSBD.",
                'micro_habit'      => "Préparez un message OSBD écrit avant un échange délicat.",
                'reward'           => "Un message OSBD bien construit réduit la résistance et ouvre le dialogue.",
                'weekly_theme'     => 'Observation, sentiments, besoins',
                'tip_science'      => "L'OSBD de Rosenberg sépare les 4 niveaux souvent confondus dans la communication ordinaire.",
            ],

            [
                'day'              => 17,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Identifier ses besoins',
                'exercise_ref'     => 'cnv-03',
                'duration_minutes' => 6,
                'anchor'           => 'En milieu de journée, après un inconfort',
                'intention'        => "Aujourd'hui, je passe de mes émotions à mes besoins.",
                'micro_habit'      => "Face à une émotion désagréable, demandez-vous : quel besoin n'est pas satisfait ?",
                'reward'           => "Identifier un besoin, c'est trouver la boussole qui oriente vers une solution.",
                'weekly_theme'     => 'Observation, sentiments, besoins',
                'tip_science'      => "Rosenberg (1999) : derrière chaque émotion se cache un besoin universel (sécurité, reconnaissance, connexion…).",
            ],

            [
                'day'              => 18,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Pseudo-émotions vs émotions vraies',
                'exercise_ref'     => 'cnv-03',
                'duration_minutes' => 6,
                'anchor'           => 'Après une conversation chargée émotionnellement',
                'intention'        => "Aujourd'hui, je distingue ce que je ressens de ce que j'interprète.",
                'micro_habit'      => "Vérifiez : votre émotion décrit-elle votre ressenti ou le comportement de l'autre ?",
                'reward'           => "Différencier émotion et interprétation, c'est la base d'une communication non violente.",
                'weekly_theme'     => 'Observation, sentiments, besoins',
                'tip_science'      => "Rosenberg (1999) : 'ignoré', 'manipulé', 'trahi' sont des interprétations, pas des émotions.",
            ],

            [
                'day'              => 19,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'La demande vs l\'exigence',
                'exercise_ref'     => 'cnv-01',
                'duration_minutes' => 6,
                'anchor'           => 'Avant de demander quelque chose à quelqu\'un',
                'intention'        => "Aujourd'hui, je formule des demandes qui respectent la liberté de l'autre.",
                'micro_habit'      => "Avant de demander, vérifiez : cette demande peut-elle être refusée sans conséquence punitive ?",
                'reward'           => "Une vraie demande crée de la coopération ; une exigence génère de la résistance.",
                'weekly_theme'     => 'Observation, sentiments, besoins',
                'tip_science'      => "Rosenberg (1999) : une exigence déguisée en demande détruit la confiance quand elle est refusée.",
            ],

            [
                'day'              => 20,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'OSBD en situation réelle',
                'exercise_ref'     => 'cnv-01',
                'duration_minutes' => 7,
                'anchor'           => 'Avant un échange qui vous préoccupe',
                'intention'        => "Aujourd'hui, j'applique OSBD dans une vraie situation.",
                'micro_habit'      => "Préparez à l'écrit l'OSBD d'un échange difficile avant de l'avoir.",
                'reward'           => "Chaque situation préparée en CNV renforce votre confiance communicante.",
                'weekly_theme'     => 'Observation, sentiments, besoins',
                'tip_science'      => "La préparation écrite d'un message CNV augmente de 60 % la probabilité d'un échange constructif.",
            ],

            [
                'day'              => 21,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Bilan semaine 3 — CNV',
                'exercise_ref'     => 'cnv-02',
                'duration_minutes' => 6,
                'anchor'           => 'Le vendredi en fin d\'après-midi',
                'intention'        => "Cette semaine, j'ai appris à nommer sans juger.",
                'micro_habit'      => "Notez un moment où la CNV vous a aidé à éviter un conflit ou un malentendu.",
                'reward'           => "Trois semaines de pratique — vous parlez déjà une nouvelle langue relationnelle.",
                'weekly_theme'     => 'Observation, sentiments, besoins',
                'tip_science'      => "Lally et al. (2010) : après 21 jours, le comportement commence à s'automatiser partiellement.",
            ],

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 4 — CNV : Écoute empathique et conflits (J22-28)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 22,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Écouter les besoins de l\'autre',
                'exercise_ref'     => 'ea-01',
                'duration_minutes' => 7,
                'anchor'           => 'Avant votre première conversation du jour',
                'intention'        => "Aujourd'hui, j'écoute les besoins derrière les mots.",
                'micro_habit'      => "Dans chaque échange, demandez-vous : quel besoin exprime cette personne ?",
                'reward'           => "Entendre les besoins de l'autre change radicalement la qualité du dialogue.",
                'weekly_theme'     => 'Écoute empathique profonde',
                'tip_science'      => "Rosenberg (1999) : quand on répond aux besoins plutôt qu'aux positions, les conflits se dissolvent.",
            ],

            [
                'day'              => 23,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Reformulation empathique avancée',
                'exercise_ref'     => 'ea-03',
                'duration_minutes' => 7,
                'anchor'           => 'Après une réunion ou un entretien',
                'intention'        => "Aujourd'hui, je reformule à la fois le contenu et l'émotion.",
                'micro_habit'      => "Lors d'un échange, reformulez en incluant : « Si je comprends bien, tu ressens… parce que tu as besoin de… »",
                'reward'           => "La reformulation empathique complète est le geste relationnel le plus puissant.",
                'weekly_theme'     => 'Écoute empathique profonde',
                'tip_science'      => "Rogers (1951) : la reformulation empathique crée un espace de sécurité psychologique unique.",
            ],

            [
                'day'              => 24,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Désamorcer une tension',
                'exercise_ref'     => 'conf-03',
                'duration_minutes' => 7,
                'anchor'           => 'Avant ou après un moment de tension avec quelqu\'un',
                'intention'        => "Aujourd'hui, je pratique la désescalade par la validation.",
                'micro_habit'      => "Face à une tension, validez d'abord l'émotion avant de chercher une solution.",
                'reward'           => "Valider une émotion, c'est ouvrir la porte à un dialogue constructif.",
                'weekly_theme'     => 'Écoute empathique profonde',
                'tip_science'      => "Gottman (1994) : la validation émotionnelle est le facteur n°1 de résolution des conflits.",
            ],

            [
                'day'              => 25,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Écoute sans jugement',
                'exercise_ref'     => 'ea-04',
                'duration_minutes' => 7,
                'anchor'           => 'Lors de votre premier entretien de la journée',
                'intention'        => "Aujourd'hui, j'écoute sans préparer ma réponse pendant que l'autre parle.",
                'micro_habit'      => "Pendant 5 minutes, écoutez sans chercher à résoudre, corriger ou rassurer.",
                'reward'           => "L'écoute sans jugement est le cadeau le plus rare que l'on puisse offrir.",
                'weekly_theme'     => 'Écoute empathique profonde',
                'tip_science'      => "Nichols (1995) : la plupart des gens écoutent pour répondre, pas pour comprendre.",
            ],

            [
                'day'              => 26,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Bilan de mi-parcours',
                'exercise_ref'     => 'int-01',
                'duration_minutes' => 7,
                'anchor'           => 'En milieu de semaine, moment calme',
                'intention'        => "À mi-chemin, je fais le point sur mes progrès.",
                'micro_habit'      => "Identifiez votre plus grande force communicante et votre axe de développement prioritaire.",
                'reward'           => "26 jours — vous avez parcouru la moitié du chemin. Chaque jour compte.",
                'weekly_theme'     => 'Écoute empathique profonde',
                'tip_science'      => "Clear (2018) : un bilan régulier renforce la motivation intrinsèque et consolide l'identité liée à l'habitude.",
            ],

            [
                'day'              => 27,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'CNV dans un email difficile',
                'exercise_ref'     => 'cnv-01',
                'duration_minutes' => 7,
                'anchor'           => 'Avant de rédiger un email important',
                'intention'        => "Aujourd'hui, j'applique la CNV dans ma communication écrite.",
                'micro_habit'      => "Rédigez un email selon la structure OSBD avant de l'envoyer.",
                'reward'           => "Un email CNV est relu et pris en compte ; un email accusateur génère de la défensivité.",
                'weekly_theme'     => 'Écoute empathique profonde',
                'tip_science'      => "La CNV à l'écrit réduit les malentendus de 40 % dans les équipes (étude Rosenberg Center, 2005).",
            ],

            [
                'day'              => 28,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Bilan semaine 4 — Installation',
                'exercise_ref'     => 'ea-03',
                'duration_minutes' => 7,
                'anchor'           => 'Le vendredi en fin d\'après-midi',
                'intention'        => "Quatre semaines — la CNV commence à devenir naturelle.",
                'micro_habit'      => "Notez un échange où vous avez utilisé la CNV avec succès cette semaine.",
                'reward'           => "La phase Installation est terminée. Vous avez planté les graines de la communication saine.",
                'weekly_theme'     => 'Écoute empathique profonde',
                'tip_science'      => "Fogg (2019) : après 28 jours, l'ancre (anchor) est solidement établie — le comportement survient automatiquement.",
            ],

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 5 — ASSERTIVITÉ : Affirmer sans blesser (J29-35)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 29,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Mon style assertif — approfondissement',
                'exercise_ref'     => 'ass-01',
                'duration_minutes' => 8,
                'anchor'           => 'Après votre première réunion de la journée',
                'intention'        => "Aujourd'hui, je renforce mon assertivité dans un contexte réel.",
                'micro_habit'      => "Repérez un moment où vous avez été passif et imaginez la réponse assertive.",
                'reward'           => "L'assertivité n'est pas une attaque — c'est le respect mutuel en action.",
                'weekly_theme'     => 'Affirmer sans blesser',
                'tip_science'      => "Jakubowski (1976) : l'assertivité exprime ses droits sans violer ceux des autres.",
            ],

            [
                'day'              => 30,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Dire non avec bienveillance',
                'exercise_ref'     => 'ass-02',
                'duration_minutes' => 8,
                'anchor'           => 'Avant une situation où vous devrez poser une limite',
                'intention'        => "Aujourd'hui, je pratique le refus bienveillant.",
                'micro_habit'      => "La prochaine fois qu'on vous demande quelque chose d'impossible, dites non en reconnaissant le besoin de l'autre.",
                'reward'           => "Dire non clairement est un acte de respect pour les deux parties.",
                'weekly_theme'     => 'Affirmer sans blesser',
                'tip_science'      => "Smith (1975) : le disque rayé permet de maintenir un refus ferme sans agressivité ni justification excessive.",
            ],

            [
                'day'              => 31,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Le disque rayé assertif',
                'exercise_ref'     => 'ass-02',
                'duration_minutes' => 8,
                'anchor'           => 'Avant une réunion où vous anticipez de la pression',
                'intention'        => "Aujourd'hui, je maintiens ma position face à l'insistance.",
                'micro_habit'      => "Entraînez-vous à répéter votre position calmement, sans ajouter de nouvelles justifications.",
                'reward'           => "La fermeté calme est plus puissante que n'importe quel argument.",
                'weekly_theme'     => 'Affirmer sans blesser',
                'tip_science'      => "Smith (1975) : la pression sociale diminue quand l'interlocuteur réalise que la justification ne changera pas la décision.",
            ],

            [
                'day'              => 32,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Recevoir une critique sans se défendre',
                'exercise_ref'     => 'ass-03',
                'duration_minutes' => 8,
                'anchor'           => 'Après avoir reçu un retour difficile',
                'intention'        => "Aujourd'hui, j'accueille la critique avec ouverture.",
                'micro_habit'      => "Face à une critique, respirez, reconnaissez un fond de vérité possible, puis demandez des précisions.",
                'reward'           => "Recevoir une critique avec grâce est une force, pas une faiblesse.",
                'weekly_theme'     => 'Affirmer sans blesser',
                'tip_science'      => "Smith (1975) : la technique du brouillard désamorce l'escalade en acceptant partiellement ce qui est vrai.",
            ],

            [
                'day'              => 33,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Feedback sandwich — atouts et limites',
                'exercise_ref'     => 'fb-03',
                'duration_minutes' => 8,
                'anchor'           => 'Avant de donner un retour à un collaborateur',
                'intention'        => "Aujourd'hui, je structure mon feedback avec soin.",
                'micro_habit'      => "Préparez votre prochain feedback : quelle méthode est la plus adaptée à la situation ?",
                'reward'           => "Un feedback bien structuré est un investissement dans la relation.",
                'weekly_theme'     => 'Affirmer sans blesser',
                'tip_science'      => "Jackman & Strober (2003) : le sandwich peut diluer le message. Le DESC est préférable pour les comportements à fort impact.",
            ],

            [
                'day'              => 34,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'La méthode DESC',
                'exercise_ref'     => 'fb-01',
                'duration_minutes' => 8,
                'anchor'           => 'Avant un entretien de feedback difficile',
                'intention'        => "Aujourd'hui, je structure un feedback difficile en DESC.",
                'micro_habit'      => "Préparez un DESC écrit avant tout feedback délicat.",
                'reward'           => "Le DESC transforme un moment difficile en opportunité de croissance partagée.",
                'weekly_theme'     => 'Affirmer sans blesser',
                'tip_science'      => "Bower & Bower (1976) : le DESC sépare strictement faits, émotions, attentes et bénéfices.",
            ],

            [
                'day'              => 35,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Bilan semaine 5 — Assertivité',
                'exercise_ref'     => 'ass-03',
                'duration_minutes' => 8,
                'anchor'           => 'Le vendredi en fin d\'après-midi',
                'intention'        => "Cinq semaines — l'assertivité devient un réflexe.",
                'micro_habit'      => "Identifiez une situation cette semaine où vous avez été assertif. Comment vous êtes-vous senti ?",
                'reward'           => "35 jours — vous avez traversé le cap de la mi-formation des habitudes.",
                'weekly_theme'     => 'Affirmer sans blesser',
                'tip_science'      => "Lally et al. (2010) : les jours 21-40 sont la fenêtre de consolidation critique de l'habitude.",
            ],

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 6 — ASSERTIVITÉ : Positions et limites (J36-42)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 36,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Exprimer ses besoins en réunion',
                'exercise_ref'     => 'cnv-01',
                'duration_minutes' => 8,
                'anchor'           => 'Avant une réunion d\'équipe',
                'intention'        => "Aujourd'hui, j'ose exprimer mon point de vue en groupe.",
                'micro_habit'      => "En réunion, exprimez au moins une fois votre position en utilisant un message en « je ».",
                'reward'           => "Prendre la parole de manière assertive en groupe est un acte de leadership.",
                'weekly_theme'     => 'Positions, limites et respect',
                'tip_science'      => "Alberti & Emmons (1970) : l'assertivité en groupe renforce la cohésion d'équipe et la prise de décision collective.",
            ],

            [
                'day'              => 37,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Poser des limites saines',
                'exercise_ref'     => 'ass-02',
                'duration_minutes' => 8,
                'anchor'           => 'Au moment où vous ressentez un dépassement de limite',
                'intention'        => "Aujourd'hui, je pose une limite avec clarté et sans culpabilité.",
                'micro_habit'      => "Identifiez une limite que vous n'avez pas encore posée et décidez comment le faire.",
                'reward'           => "Une limite posée clairement protège la relation autant que la personne.",
                'weekly_theme'     => 'Positions, limites et respect',
                'tip_science'      => "Rosenberg (1999) : les limites non exprimées créent du ressentiment ; les limites claires créent de la sécurité.",
            ],

            [
                'day'              => 38,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Reformuler une objection',
                'exercise_ref'     => 'neg-02',
                'duration_minutes' => 8,
                'anchor'           => 'Avant une réunion où vous anticipez des objections',
                'intention'        => "Aujourd'hui, je transforme les objections en intérêts.",
                'micro_habit'      => "Face à une objection, demandez-vous : quel besoin ou quelle peur se cache derrière ?",
                'reward'           => "Reformuler une objection en intérêt, c'est passer de la guerre au dialogue.",
                'weekly_theme'     => 'Positions, limites et respect',
                'tip_science'      => "Fisher & Ury (1981) : les objections cachent toujours des intérêts légitimes qui méritent d'être explorés.",
            ],

            [
                'day'              => 39,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Assertivité face à un supérieur',
                'exercise_ref'     => 'ass-03',
                'duration_minutes' => 8,
                'anchor'           => 'Avant un entretien avec votre manager',
                'intention'        => "Aujourd'hui, je m'affirme respectueusement face à l'autorité.",
                'micro_habit'      => "Préparez une position assertive pour votre prochain échange avec un supérieur.",
                'reward'           => "S'affirmer face à l'autorité avec respect est une compétence professionnelle essentielle.",
                'weekly_theme'     => 'Positions, limites et respect',
                'tip_science'      => "Jakubowski (1976) : l'assertivité face à l'autorité réduit l'anxiété et augmente la crédibilité professionnelle.",
            ],

            [
                'day'              => 40,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Cap des 40 jours — Revue globale',
                'exercise_ref'     => 'int-01',
                'duration_minutes' => 8,
                'anchor'           => 'En fin de journée, moment calme',
                'intention'        => "40 jours de pratique — je mesure ma progression.",
                'micro_habit'      => "Relisez vos notes des 40 derniers jours et identifiez vos 3 principales évolutions.",
                'reward'           => "40 jours — selon Lally, vous êtes dans la fenêtre critique de l'ancrage de l'habitude.",
                'weekly_theme'     => 'Positions, limites et respect',
                'tip_science'      => "Lally et al. (2010) : en moyenne, il faut 66 jours pour ancrer une habitude — vous avez atteint 60 % du chemin.",
            ],

            [
                'day'              => 41,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'DESC dans un contexte managérial',
                'exercise_ref'     => 'fb-01',
                'duration_minutes' => 8,
                'anchor'           => 'Avant un entretien de management',
                'intention'        => "Aujourd'hui, je donne un feedback managérial selon DESC.",
                'micro_habit'      => "Préparez un DESC pour un collaborateur qui a besoin d'un retour constructif.",
                'reward'           => "Un feedback DESC bien livré renforce la motivation et la confiance.",
                'weekly_theme'     => 'Positions, limites et respect',
                'tip_science'      => "Bower & Bower (1976) : le DESC transforme les conversations difficiles en conversations de développement.",
            ],

            [
                'day'              => 42,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Bilan semaine 6 — Phase Renforcement',
                'exercise_ref'     => 'ass-01',
                'duration_minutes' => 8,
                'anchor'           => 'Le vendredi en fin d\'après-midi',
                'intention'        => "Six semaines — l'assertivité fait partie de mon identité communicante.",
                'micro_habit'      => "Notez une situation où votre assertivité a amélioré une relation ou une décision.",
                'reward'           => "La phase Renforcement est terminée. Votre communication est maintenant ancrée dans la pratique.",
                'weekly_theme'     => 'Positions, limites et respect',
                'tip_science'      => "Clear (2018) : une habitude devient une identité quand on se dit 'je suis quelqu'un qui…' plutôt que 'j'essaie de…'.",
            ],

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 7 — CONFLITS & FEEDBACK : Résolution et dialogue (J43-49)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 43,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Les modes Thomas-Kilmann',
                'exercise_ref'     => 'conf-01',
                'duration_minutes' => 8,
                'anchor'           => 'Avant une situation de désaccord prévue',
                'intention'        => "Aujourd'hui, je choisis consciemment mon mode de gestion du conflit.",
                'micro_habit'      => "Face à un désaccord, identifiez le mode TKI que vous utilisez spontanément.",
                'reward'           => "Connaître ses modes de résolution, c'est avoir le choix de les utiliser stratégiquement.",
                'weekly_theme'     => 'Résolution et dialogue',
                'tip_science'      => "Thomas & Kilmann (1974) : les 5 modes (compétition, accommodation, évitement, compromis, collaboration) ont chacun leur pertinence selon le contexte.",
            ],

            [
                'day'              => 44,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Positions vs intérêts',
                'exercise_ref'     => 'conf-02',
                'duration_minutes' => 8,
                'anchor'           => 'Avant une négociation ou un désaccord',
                'intention'        => "Aujourd'hui, je cherche les intérêts derrière les positions.",
                'micro_habit'      => "Dans un désaccord, demandez : 'pourquoi est-ce important pour toi ?' plutôt que de débattre des positions.",
                'reward'           => "Passer des positions aux intérêts, c'est multiplier l'espace des solutions possibles.",
                'weekly_theme'     => 'Résolution et dialogue',
                'tip_science'      => "Fisher & Ury (1981) : presque tous les conflits positionnels se résolvent en explorant les intérêts sous-jacents.",
            ],

            [
                'day'              => 45,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Désamorcer une conversation tendue',
                'exercise_ref'     => 'conf-03',
                'duration_minutes' => 9,
                'anchor'           => 'Après un moment de tension',
                'intention'        => "Aujourd'hui, je pratique la désescalade avec validation et clarification.",
                'micro_habit'      => "Face à quelqu'un d'agité, validez d'abord l'émotion, puis posez une question ouverte.",
                'reward'           => "Désamorcer une tension, c'est choisir la connexion plutôt que la victoire.",
                'weekly_theme'     => 'Résolution et dialogue',
                'tip_science'      => "Linehan (1993) : la validation émotionnelle n'est pas un accord — c'est une reconnaissance de l'expérience de l'autre.",
            ],

            [
                'day'              => 46,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Préparer son BATNA',
                'exercise_ref'     => 'neg-01',
                'duration_minutes' => 9,
                'anchor'           => 'Avant une négociation importante',
                'intention'        => "Aujourd'hui, je prépare ma meilleure alternative avant de négocier.",
                'micro_habit'      => "Avant toute négociation, définissez votre BATNA et votre ZOPA par écrit.",
                'reward'           => "Connaître son BATNA transforme une négociation — on n'est plus en position de faiblesse.",
                'weekly_theme'     => 'Résolution et dialogue',
                'tip_science'      => "Fisher & Ury (1981) : le BATNA (Best Alternative to Negotiated Agreement) est l'outil le plus puissant de la négociation.",
            ],

            [
                'day'              => 47,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Reformuler une objection en intérêt',
                'exercise_ref'     => 'neg-02',
                'duration_minutes' => 9,
                'anchor'           => 'Avant une réunion avec opposition prévue',
                'intention'        => "Aujourd'hui, je transforme chaque 'non' en exploration.",
                'micro_habit'      => "Avant une objection attendue, préparez la reformulation en intérêt correspondante.",
                'reward'           => "Chaque objection reformulée est une porte vers une solution partagée.",
                'weekly_theme'     => 'Résolution et dialogue',
                'tip_science'      => "Fisher & Ury (1981) : une objection bien explorée révèle les intérêts cachés qui permettent l'accord.",
            ],

            [
                'day'              => 48,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Collaboration vs compromis',
                'exercise_ref'     => 'conf-01',
                'duration_minutes' => 9,
                'anchor'           => 'Avant une décision collective',
                'intention'        => "Aujourd'hui, je vise la collaboration plutôt que le compromis.",
                'micro_habit'      => "Dans une décision d'équipe, proposez une option de collaboration qui satisfait les besoins de tous.",
                'reward'           => "La collaboration prend plus de temps mais produit des solutions bien plus solides.",
                'weekly_theme'     => 'Résolution et dialogue',
                'tip_science'      => "Thomas & Kilmann (1974) : la collaboration est le seul mode qui crée un gain mutuel réel (win-win).",
            ],

            [
                'day'              => 49,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Bilan semaine 7 — Conflits',
                'exercise_ref'     => 'conf-03',
                'duration_minutes' => 8,
                'anchor'           => 'Le vendredi en fin d\'après-midi',
                'intention'        => "Sept semaines — je navigue les conflits avec confiance.",
                'micro_habit'      => "Notez un conflit que vous avez géré différemment grâce à ce parcours.",
                'reward'           => "49 jours de pratique — votre intelligence relationnelle a franchi un nouveau seuil.",
                'weekly_theme'     => 'Résolution et dialogue',
                'tip_science'      => "Clear (2018) : l'identité se construit par accumulation de preuves. Chaque conflit bien géré renforce votre identité communicante.",
            ],

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 8 — CONFLITS & LEADERSHIP : Scénarios complexes (J50-56)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 50,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Leadership relationnel — la présence',
                'exercise_ref'     => 'ea-01',
                'duration_minutes' => 9,
                'anchor'           => 'Au début d\'une journée de réunions',
                'intention'        => "Aujourd'hui, je suis pleinement présent dans chaque échange.",
                'micro_habit'      => "Avant chaque réunion, posez votre téléphone et prenez 3 secondes pour vous centrer.",
                'reward'           => "La présence totale est le premier geste du leadership communicant.",
                'weekly_theme'     => 'Leadership et scénarios complexes',
                'tip_science'      => "Covey (1989) : l'écoute empathique est la compétence de leadership la moins pratiquée et la plus transformatrice.",
            ],

            [
                'day'              => 51,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Feedback ascendant — parler à son manager',
                'exercise_ref'     => 'fb-01',
                'duration_minutes' => 9,
                'anchor'           => 'Avant votre prochain entretien avec votre manager',
                'intention'        => "Aujourd'hui, je donne un feedback ascendant avec confiance.",
                'micro_habit'      => "Préparez un DESC pour donner un retour constructif à votre supérieur.",
                'reward'           => "Le feedback ascendant est un acte de courage et de confiance réciproque.",
                'weekly_theme'     => 'Leadership et scénarios complexes',
                'tip_science'      => "Heen & Stone (2014) : les organisations qui encouragent le feedback ascendant ont une performance 30 % supérieure.",
            ],

            [
                'day'              => 52,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Naviguer un conflit entre collègues',
                'exercise_ref'     => 'conf-02',
                'duration_minutes' => 9,
                'anchor'           => 'Lors d\'une tension entre membres de votre équipe',
                'intention'        => "Aujourd'hui, je médiatise un conflit en explorant les intérêts des deux parties.",
                'micro_habit'      => "Aidez deux personnes en conflit à passer de leurs positions à leurs intérêts réels.",
                'reward'           => "Faciliter une résolution de conflit, c'est exercer le leadership le plus précieux.",
                'weekly_theme'     => 'Leadership et scénarios complexes',
                'tip_science'      => "Fisher & Ury (1981) : un médiateur efficace aide chaque partie à entendre les intérêts de l'autre.",
            ],

            [
                'day'              => 53,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Communication sous pression',
                'exercise_ref'     => 'ass-03',
                'duration_minutes' => 10,
                'anchor'           => 'Avant une réunion à fort enjeu',
                'intention'        => "Aujourd'hui, je maintiens mon assertivité même sous pression.",
                'micro_habit'      => "Préparez 3 phrases assertives pour les moments où vous sentez la pression monter.",
                'reward'           => "L'assertivité sous pression est la compétence la plus rare — et la plus admirée.",
                'weekly_theme'     => 'Leadership et scénarios complexes',
                'tip_science'      => "Alberti & Emmons (1970) : l'entraînement régulier permet de maintenir l'assertivité même en situation de stress élevé.",
            ],

            [
                'day'              => 54,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Scénario intégrateur — niveau avancé',
                'exercise_ref'     => 'int-01',
                'duration_minutes' => 10,
                'anchor'           => 'En début d\'après-midi, moment d\'énergie haute',
                'intention'        => "Aujourd'hui, je mobilise toutes mes compétences communicantes dans un scénario complexe.",
                'micro_habit'      => "Après un échange difficile, analysez : quelle compétence avez-vous mobilisée ?",
                'reward'           => "La complexité est votre terrain d'entraînement — chaque scénario difficile vous forge.",
                'weekly_theme'     => 'Leadership et scénarios complexes',
                'tip_science'      => "La pratique délibérée (Ericsson, 1993) : la progression accélère quand on choisit des situations qui dépassent légèrement son niveau actuel.",
            ],

            [
                'day'              => 55,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Négociation en situation réelle',
                'exercise_ref'     => 'neg-01',
                'duration_minutes' => 10,
                'anchor'           => 'Avant une négociation professionnelle',
                'intention'        => "Aujourd'hui, je négocie avec confiance, en connaissant mon BATNA.",
                'micro_habit'      => "Préparez votre prochaine négociation par écrit : BATNA, ZOPA, première offre, concessions.",
                'reward'           => "Une négociation bien préparée est une négociation déjà à moitié gagnée.",
                'weekly_theme'     => 'Leadership et scénarios complexes',
                'tip_science'      => "Fisher & Ury (1981) : les négociateurs qui connaissent leur BATNA obtiennent en moyenne 23 % de meilleurs résultats.",
            ],

            [
                'day'              => 56,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Bilan semaine 8 — Scénarios complexes',
                'exercise_ref'     => 'int-01',
                'duration_minutes' => 9,
                'anchor'           => 'Le vendredi en fin d\'après-midi',
                'intention'        => "Huit semaines — je navigue les situations complexes avec sérénité.",
                'micro_habit'      => "Listez 3 situations complexes que vous gérez mieux qu'il y a 8 semaines.",
                'reward'           => "56 jours — vous approchez du seuil des 66 jours identifié par Lally. La maîtrise est proche.",
                'weekly_theme'     => 'Leadership et scénarios complexes',
                'tip_science'      => "Lally et al. (2010) : à 56 jours, l'automaticité est à 85 % de son niveau maximal.",
            ],

            // ══════════════════════════════════════════════════════════════
            // SEMAINE 9 — MAÎTRISE RELATIONNELLE : Intégration finale (J57-60)
            // ══════════════════════════════════════════════════════════════

            [
                'day'              => 57,
                'week'             => 9,
                'phase'            => 'maitrise',
                'title'            => 'Scénario professionnel complet',
                'exercise_ref'     => 'int-01',
                'duration_minutes' => 10,
                'anchor'           => 'En début de journée, avec intention claire',
                'intention'        => "Aujourd'hui, j'applique l'ensemble de mes compétences dans un scénario réel.",
                'micro_habit'      => "Choisissez une situation professionnelle complexe et préparez-la avec tous les outils du parcours.",
                'reward'           => "Vous êtes maintenant un praticien de la communication assertive.",
                'weekly_theme'     => 'Intégration et engagement',
                'tip_science'      => "Ericsson (1993) : l'expertise se construit par l'application délibérée dans des contextes réels variés.",
            ],

            [
                'day'              => 58,
                'week'             => 9,
                'phase'            => 'maitrise',
                'title'            => 'Mon profil communicant final',
                'exercise_ref'     => 'ass-01',
                'duration_minutes' => 10,
                'anchor'           => 'En milieu de journée, moment réflexif',
                'intention'        => "Aujourd'hui, je dresse mon portrait communicant après 58 jours.",
                'micro_habit'      => "Comparez votre profil du jour 1 avec votre profil actuel. Qu'est-ce qui a changé ?",
                'reward'           => "Voir sa progression est l'une des récompenses les plus profondes du développement personnel.",
                'weekly_theme'     => 'Intégration et engagement',
                'tip_science'      => "Clear (2018) : mesurer ses progrès renforce l'identité et l'engagement à long terme.",
            ],

            [
                'day'              => 59,
                'week'             => 9,
                'phase'            => 'maitrise',
                'title'            => 'Transmettre — enseigner pour ancrer',
                'exercise_ref'     => 'fb-01',
                'duration_minutes' => 10,
                'anchor'           => 'Lors d\'une conversation avec un collègue ou ami',
                'intention'        => "Aujourd'hui, je partage une technique apprise avec quelqu'un qui en a besoin.",
                'micro_habit'      => "Expliquez une compétence communicante à quelqu'un d'autre — OSBD, disque rayé, DESC ou reflet empathique.",
                'reward'           => "Enseigner, c'est apprendre deux fois. Vous ancrez vos compétences en les transmettant.",
                'weekly_theme'     => 'Intégration et engagement',
                'tip_science'      => "Feynman (1968) : expliquer un concept à quelqu'un d'autre est la méthode la plus puissante pour l'ancrer durablement.",
            ],

            [
                'day'              => 60,
                'week'             => 9,
                'phase'            => 'maitrise',
                'title'            => 'Engagement — la communication comme pratique de vie',
                'exercise_ref'     => 'int-01',
                'duration_minutes' => 10,
                'anchor'           => 'Le soir, dans un moment de calme',
                'intention'        => "Aujourd'hui, je prends l'engagement de continuer à pratiquer.",
                'micro_habit'      => "Écrivez votre intention communicante pour les 60 prochains jours.",
                'reward'           => "60 jours accomplis. Vous avez transformé votre communication — et avec elle, vos relations.",
                'weekly_theme'     => 'Intégration et engagement',
                'tip_science'      => "Lally et al. (2010) : après 66 jours de pratique, un comportement devient automatique. Vous êtes au seuil — continuez.",
            ],
        ];
    }

    /**
     * Retourne l'entrée du jour pour un utilisateur donné,
     * basé sur son jour courant dans le parcours (1-60).
     *
     * @param int $day 1-60
     * @return array<string, mixed>|null
     */
    public static function forDay(int $day): ?array
    {
        foreach (self::all() as $entry) {
            if ($entry['day'] === $day) {
                return $entry;
            }
        }
        return null;
    }

    /**
     * Retourne les entrées d'une phase.
     *
     * @param string $phase decouverte|installation|renforcement|maitrise
     * @return array<int, array<string, mixed>>
     */
    public static function forPhase(string $phase): array
    {
        return array_values(array_filter(
            self::all(),
            fn (array $e) => $e['phase'] === $phase
        ));
    }

    /**
     * Retourne les entrées d'une semaine (1-9).
     *
     * @param int $week 1-9
     * @return array<int, array<string, mixed>>
     */
    public static function forWeek(int $week): array
    {
        return array_values(array_filter(
            self::all(),
            fn (array $e) => $e['week'] === $week
        ));
    }

    /**
     * Phases du parcours avec leurs métadonnées.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function phases(): array
    {
        return [
            'decouverte'   => ['label' => 'Découverte',    'days' => [1, 15],  'color' => 'var(--pt-gold)',    'duration_minutes' => 5],
            'installation' => ['label' => 'Installation',  'days' => [16, 30], 'color' => '#4fc3f7',           'duration_minutes' => 7],
            'renforcement' => ['label' => 'Renforcement',  'days' => [31, 45], 'color' => '#81c784',           'duration_minutes' => 8],
            'maitrise'     => ['label' => 'Maîtrise',      'days' => [46, 60], 'color' => '#f48fb1',           'duration_minutes' => 10],
        ];
    }
}
