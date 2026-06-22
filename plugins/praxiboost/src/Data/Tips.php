<?php

namespace Praxis\Plugins\PraxiBoost\Data;

/**
 * Bibliothèque de « tips du jour » — L'Étincelle (développement personnel).
 *
 * Convention identique aux autres mini-apps (voir PraxiZen\Data\Tips) :
 *   solide / prometteur / emergent pour le niveau de preuve, et un couple
 *   insight (le fond) + action (la micro-action du jour).
 */
class Tips
{
    public static function all(): array
    {
        return [
            [
                'id'       => 'boost-ancrage-habitude',
                'title'    => 'Accroche une nouvelle habitude à une habitude existante',
                'theme'    => 'Habitudes',
                'evidence' => 'prometteur',
                'insight'  => "Le « habit stacking » utilise un comportement déjà automatique comme déclencheur : « Après [habitude existante], je fais [nouvelle habitude]. » Le contexte existant fait le travail de rappel que la motivation seule ne fait pas.",
                'action'   => "Formule une accroche pour une habitude visée : « Après avoir [versé mon café du matin], je [note ma priorité du jour]. »",
                'source'   => 'Clear, Atomic Habits ; Fogg, Tiny Habits',
                'tags'     => ['habitudes', 'action', 'routine'],
            ],
            [
                'id'       => 'boost-reduire-friction',
                'title'    => "Rends la bonne habitude évidente et facile",
                'theme'    => 'Habitudes',
                'evidence' => 'prometteur',
                'insight'  => "On agit moins par volonté que par facilité. Réduire de quelques secondes la friction d'un bon comportement (préparer ses affaires de sport la veille) et augmenter celle d'un mauvais (déconnecter une appli) change durablement la conduite.",
                'action'   => "Choisis une bonne habitude et supprime un obstacle physique dès aujourd'hui : prépare l'environnement à l'avance.",
                'source'   => 'Architecture du choix ; Thaler & Sunstein, Nudge',
                'tags'     => ['habitudes', 'environnement', 'action'],
            ],
            [
                'id'       => 'boost-woop',
                'title'    => 'Rêver ne suffit pas : contraste avec l\'obstacle',
                'theme'    => 'Motivation',
                'evidence' => 'solide',
                'insight'  => "La méthode WOOP (Souhait, Résultat, Obstacle, Plan) montre que visualiser le succès ne suffit pas — ça peut même démobiliser. Confronter le souhait à l'obstacle interne réel, puis prévoir un plan « si… alors », transforme l'envie en action.",
                'action'   => "Fais un WOOP : souhait du jour, bénéfice, principal obstacle interne, puis « si [obstacle], alors je [réaction prévue] ».",
                'source'   => 'Oettingen, 2014, Rethinking Positive Thinking (contraste mental)',
                'tags'     => ['motivation', 'objectifs', 'action'],
            ],
            [
                'id'       => 'boost-si-alors',
                'title'    => 'Le plan « si… alors » bat la bonne résolution',
                'theme'    => 'Action',
                'evidence' => 'solide',
                'insight'  => "Préciser à l'avance dans quelle situation on agira (« si c'est lundi 18 h, alors je vais courir ») rend l'action quasi automatique. C'est l'un des leviers comportementaux les mieux validés pour franchir le fossé intention-action.",
                'action'   => "Prends un objectif que tu repousses et écris une clause si-alors précise. Place-la là où tu la reverras.",
                'source'   => 'Gollwitzer & Sheeran, 2006 (intentions d\'implémentation)',
                'tags'     => ['action', 'objectifs', 'habitudes'],
            ],
            [
                'id'       => 'boost-valeurs-boussole',
                'title'    => 'Tes valeurs sont une boussole, pas une destination',
                'theme'    => 'Valeurs',
                'evidence' => 'prometteur',
                'insight'  => "En ACT, les valeurs (être honnête, créatif, présent) ne sont pas des buts à cocher mais des directions qui orientent l'action au quotidien. Agir selon ses valeurs, même à petits pas, nourrit un sens plus stable que la course aux résultats.",
                'action'   => "Identifie une valeur importante pour toi et pose aujourd'hui un acte concret qui l'incarne, même minuscule.",
                'source'   => 'Hayes, ACT ; Harris, Le Piège du bonheur',
                'tags'     => ['valeurs', 'sens', 'action'],
            ],
            [
                'id'       => 'boost-gratitude',
                'title'    => 'La gratitude est un muscle attentionnel',
                'theme'    => 'Confiance',
                'evidence' => 'solide',
                'insight'  => "Noter régulièrement ce pour quoi on est reconnaissant améliore l'humeur et réduit l'anxiété, en rééquilibrant l'attention biaisée vers le manque. L'effet tient si l'on varie : des choses nouvelles plutôt qu'une liste figée.",
                'action'   => "Note 3 choses pour lesquelles tu es reconnaissant aujourd'hui, dont une que tu n'avais jamais notée.",
                'source'   => 'Méta-analyse gratitude 2023 ; Emmons & McCullough, 2003',
                'tags'     => ['confiance', 'humeur', 'gratitude'],
            ],
            [
                'id'       => 'boost-2min-demarrage',
                'title'    => 'Pour vaincre l\'inertie, n\'engage que 2 minutes',
                'theme'    => 'Action',
                'evidence' => 'emergent',
                'insight'  => "La résistance est maximale avant de commencer, pas pendant. Réduire l'engagement initial à 2 minutes (« j'ouvre juste le document ») contourne l'inhibition ; l'élan fait souvent le reste.",
                'action'   => "Prends la tâche que tu évites et engage-toi à n'en faire que 2 minutes, maintenant. Tu t'arrêtes après si tu veux.",
                'source'   => 'Fogg, Tiny Habits ; principe d\'amorçage comportemental',
                'tags'     => ['action', 'procrastination', 'habitudes'],
            ],
            [
                'id'       => 'boost-progres-principle',
                'title'    => 'Le moteur de motivation le plus puissant : avancer',
                'theme'    => 'Motivation',
                'evidence' => 'prometteur',
                'insight'  => "Sur le travail qui a du sens, rien ne soutient autant la motivation au quotidien que le sentiment de progresser, même par petits pas. Rendre le progrès visible (le noter) alimente l'énergie du lendemain.",
                'action'   => "Ce soir, écris une seule chose qui a avancé aujourd'hui, si petite soit-elle. Constate l'effet sur ton moral.",
                'source'   => 'Amabile & Kramer, 2011, The Progress Principle',
                'tags'     => ['motivation', 'progres', 'sens'],
            ],
            [
                'id'       => 'boost-identite',
                'title'    => 'Vise l\'identité, pas seulement le résultat',
                'theme'    => 'Habitudes',
                'evidence' => 'emergent',
                'insight'  => "Une habitude tient mieux quand elle découle d'une identité (« je suis quelqu'un qui bouge ») que d'un objectif chiffré. Chaque petite action est alors un vote pour la personne qu'on devient.",
                'action'   => "Reformule un objectif en identité : non « courir 3x/semaine » mais « je deviens quelqu'un de sportif ». Agis une fois en conséquence.",
                'source'   => 'Clear, Atomic Habits (identity-based habits)',
                'tags'     => ['habitudes', 'identite', 'motivation'],
            ],
            [
                'id'       => 'boost-auto-compassion-rebond',
                'title'    => 'La bienveillance envers soi fait rebondir plus vite',
                'theme'    => 'Confiance',
                'evidence' => 'solide',
                'insight'  => "Après un échec, se traiter avec auto-compassion (plutôt qu'avec dureté) est associé à plus de motivation à se rattraper et moins de procrastination. La sévérité paralyse ; la bienveillance remet en mouvement.",
                'action'   => "Face à un faux pas aujourd'hui, écris-toi une phrase que tu dirais à un ami dans la même situation.",
                'source'   => 'Neff ; Breines & Chen, 2012 (self-compassion & motivation)',
                'tags'     => ['confiance', 'auto-compassion', 'resilience'],
            ],
            [
                'id'       => 'boost-objectif-precis',
                'title'    => 'Un objectif précis bat un objectif flou',
                'theme'    => 'Motivation',
                'evidence' => 'solide',
                'insight'  => "« Faire de mon mieux » produit moins que des objectifs spécifiques et exigeants. La précision oriente l'effort, rend le progrès mesurable et entretient l'engagement. Le flou, lui, dilue la motivation.",
                'action'   => "Transforme une intention vague en cible concrète et datée pour aujourd'hui.",
                'source'   => 'Locke & Latham, 2002 (fixation d\'objectifs)',
                'tags'     => ['motivation', 'objectifs', 'action'],
            ],
            [
                'id'       => 'boost-tentation-bundling',
                'title'    => 'Associe le pénible à un plaisir',
                'theme'    => 'Habitudes',
                'evidence' => 'prometteur',
                'insight'  => "Le « temptation bundling » consiste à n'autoriser un plaisir (podcast, série) qu'en faisant une tâche utile mais rebutante (sport, ménage). Le plaisir tire la corvée, et l'habitude s'installe sans bras de fer avec la volonté.",
                'action'   => "Choisis un plaisir que tu réserveras uniquement à un moment d'une tâche que tu repousses. Teste l'association aujourd'hui.",
                'source'   => 'Milkman, Minson & Volpp, 2014, Management Science',
                'tags'     => ['habitudes', 'motivation', 'action'],
            ],
            [
                'id'       => 'boost-pre-engagement',
                'title'    => 'Engage-toi à l\'avance pour court-circuiter la tentation',
                'theme'    => 'Action',
                'evidence' => 'prometteur',
                'insight'  => "Les dispositifs de pré-engagement (annoncer publiquement, prendre rendez-vous, supprimer l'option de facilité) lient nos mains de demain quand notre volonté sera plus faible. On décide une fois, à froid, plutôt que chaque jour, à chaud.",
                'action'   => "Pour un objectif important, prends aujourd'hui un engagement difficile à défaire : un rendez-vous fixé, une annonce à quelqu'un.",
                'source'   => 'Ariely ; économie comportementale (commitment devices)',
                'tags'     => ['action', 'volonte', 'objectifs'],
            ],
            [
                'id'       => 'boost-pratique-deliberee',
                'title'    => 'Répéter ne suffit pas : vise tes points faibles',
                'theme'    => 'Motivation',
                'evidence' => 'prometteur',
                'insight'  => "Le progrès vient de la pratique délibérée : travailler spécifiquement ce qu'on ne maîtrise pas encore, à la limite de sa zone de confort, avec du feedback. Refaire ce qu'on sait déjà entretient, mais ne fait pas progresser.",
                'action'   => "Identifie un point faible précis dans une compétence qui compte pour toi, et entraîne-le 15 minutes aujourd'hui.",
                'source'   => 'Ericsson, 1993 (deliberate practice)',
                'tags'     => ['motivation', 'apprentissage', 'progres'],
            ],
            [
                'id'       => 'boost-savourer',
                'title'    => 'Savourer démultiplie les bons moments',
                'theme'    => 'Confiance',
                'evidence' => 'prometteur',
                'insight'  => "Prendre quelques secondes pour vivre pleinement un moment agréable (et non passer à la suite aussitôt) renforce l'humeur positive et la satisfaction de vie. Le bonheur tient autant à l'attention qu'aux événements.",
                'action'   => "Aujourd'hui, choisis un moment agréable et arrête-toi 10 secondes pour le savourer consciemment, sans rien faire d'autre.",
                'source'   => 'Bryant & Veroff, 2007 (savoring) ; psychologie positive',
                'tags'     => ['confiance', 'humeur', 'present'],
            ],
            [
                'id'       => 'boost-revue-valeurs',
                'title'    => 'Trente secondes de sens avant une journée dure',
                'theme'    => 'Valeurs',
                'evidence' => 'prometteur',
                'insight'  => "Se rappeler brièvement pourquoi une tâche compte (à quelle valeur ou quel objectif elle se rattache) augmente la persévérance face à l'effort. Le sens est un carburant plus durable que la discipline brute.",
                'action'   => "Avant ta tâche la plus exigeante aujourd'hui, écris une phrase : « Je fais ça parce que… ».",
                'source'   => 'Affirmation des valeurs (Cohen & Sherman, 2014)',
                'tags'     => ['valeurs', 'sens', 'motivation'],
            ],
        ];
    }
}
