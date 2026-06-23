<?php

namespace Praxis\Plugins\PraxiZenith\Data;

/**
 * Catalogue des 60 exercices du Sanctuaire de l'Attention.
 *
 * Public : toute personne qui veut réapprendre à se concentrer (généraliste).
 * Un exercice par jour, concret, applicable en moins de 15 minutes, suivi d'un
 * micro-défi à appliquer le jour même.
 *
 * Parti pris : des exercices étayés par la recherche sur l'attention
 * (réseaux attentionnels — Posner & Petersen ; coût de la bascule de tâches —
 * Monsell, Rubinstein & Meyer ; entraînement de l'attention & pleine conscience
 * — Tang & Posner ; travail profond — Newport ; intentions d'implémentation —
 * Gollwitzer ; « surfer l'impulsion » — Marlatt ; restauration de l'attention
 * par la nature — Kaplan ; rythmes ultradiens — Kleitman). Chaque exercice
 * privilégie un geste observable plutôt que la théorie. Aucun ne remplace un
 * avis médical en cas de trouble de l'attention.
 *
 * Les 60 jours sont regroupés en 8 blocs thématiques progressifs :
 *   J1-8   Comprendre ton attention (diagnostic)
 *   J9-16  Aménager le sanctuaire (environnement)
 *   J17-24 Le muscle de l'attention (entraînement de base)
 *   J25-32 Une chose à la fois (single-tasking)
 *   J33-40 Tenir la durée (attention prolongée)
 *   J41-48 Dompter la distraction
 *   J49-56 Les profondeurs (deep work)
 *   J57-60 Durer (régénérer & intégrer)
 */
class Exercises
{
    public static function all(): array
    {
        return [
            // ===================================================================
            // BLOC 1 — COMPRENDRE TON ATTENTION (J1-8)
            // ===================================================================
            [
                'day' => 1,
                'theme' => 'Comprendre ton attention',
                'title' => "Mesure ta ligne de base",
                'summary' => "Avant d'entraîner ton attention, il faut savoir où elle en est aujourd'hui.",
                'micro_challenge' => "Choisis une tâche simple (lire, écrire) et lance un chrono. Travaille jusqu'à la première vraie distraction, puis arrête le chrono. Note le temps : c'est ta ligne de base. Aucun jugement.",
                'duration_min' => 10,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

On ne peut pas améliorer ce qu'on ne mesure pas. La plupart des gens surestiment énormément leur capacité à rester concentrés : ils pensent tenir 45 minutes, alors que la première distraction arrive souvent en 3 à 10 minutes. Connaître ta vraie durée d'attention soutenue, sans te mentir, est le point de départ de tout le parcours.

Cette mesure n'est pas une note. C'est une photo de départ. Elle ne dit rien de ta valeur, seulement de ton entraînement actuel — comme un coureur qui chronomètre son premier kilomètre.

## Comment

1. Prends une tâche qui demande un minimum d'attention (lire un texte, rédiger, réviser).
2. Démarre un chronomètre et travaille normalement.
3. À la **première vraie rupture** (tu attrapes ton téléphone, tu changes d'onglet, ton esprit part ailleurs et tu le suis), arrête le chrono.
4. Note le chiffre quelque part. Tu le compareras dans 60 jours.
MD,
            ],
            [
                'day' => 2,
                'theme' => 'Comprendre ton attention',
                'title' => "Cartographie tes voleurs d'attention",
                'summary' => "On ne combat bien que ce qu'on a nommé. Repère qui te vole ton attention.",
                'micro_challenge' => "Garde une feuille à côté de toi toute la journée. À chaque fois que tu décroches, note en deux mots la cause (notif, pensée, ennui, faim…). Le soir, compte. Le coupable n°1 te saute aux yeux.",
                'duration_min' => 10,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Les distractions semblent venir de partout, mais en réalité 2 ou 3 sources causent la majorité de tes ruptures d'attention. Tant qu'elles restent floues (« je suis juste distrait »), tu ne peux rien faire. Dès qu'elles sont nommées et comptées, elles deviennent des cibles concrètes.

On distingue les distractions **externes** (notifications, bruit, collègue) et **internes** (une pensée, une inquiétude, l'ennui, une sensation de faim). Les deux comptent.

## Comment

1. Pose une feuille — papier, pas écran — à portée de main.
2. Chaque fois que ton attention décroche, écris la cause en deux mots. Ne cherche pas à résister, juste à noter.
3. Le soir, regroupe et compte. Tu obtiens ta carte personnelle des voleurs d'attention.
4. Entoure le n°1 : c'est lui que les prochains jours vont t'apprendre à neutraliser.
MD,
            ],
            [
                'day' => 3,
                'theme' => 'Comprendre ton attention',
                'title' => "Attention focalisée ou diffuse ?",
                'summary' => "Ton cerveau a deux modes. Savoir lequel tu utilises change tout.",
                'micro_challenge' => "Aujourd'hui, repère un moment en mode focalisé (tu serres une tâche précise) et un moment en mode diffus (douche, marche, l'esprit vagabonde). Nomme-les à voix basse quand ils arrivent.",
                'duration_min' => 8,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

Le cerveau alterne entre deux régimes d'attention. Le mode **focalisé** : tu diriges volontairement ton attention sur une chose précise (résoudre, lire, écrire). Le mode **diffus** : ton attention se relâche et erre librement (sous la douche, en marchant) — c'est là que naissent les idées et les connexions inattendues.

Apprendre à se concentrer, ce n'est pas rester focalisé en permanence : c'est savoir **choisir le bon mode au bon moment**, et ne pas confondre la rêverie utile avec la distraction subie.

## Comment

1. Quand tu attaques une tâche qui exige de la précision, dis-toi : « mode focalisé ».
2. Quand tu fais une pause sans écran (marche, vaisselle), laisse l'esprit vagabonder : « mode diffus », c'est permis et utile.
3. La distraction, c'est quand le mode diffus s'invite pendant que tu voulais être focalisé. C'est cette frontière-là que tu apprends à tenir.
MD,
            ],
            [
                'day' => 4,
                'theme' => 'Comprendre ton attention',
                'title' => "Le mythe du multitâche",
                'summary' => "Tu ne fais jamais deux choses à la fois : tu bascules, et ça coûte cher.",
                'micro_challenge' => "Fais l'expérience : écris « concentration » en comptant 1-2-3… sous chaque lettre, mais en alternant une lettre / un chiffre. Chronomètre. Puis fais les deux séparément. Compare. La bascule t'a coûté du temps.",
                'duration_min' => 8,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Le « multitâche » n'existe pas pour les tâches qui demandent de la pensée. Ce que fait le cerveau, c'est **basculer** très vite d'une tâche à l'autre. Et chaque bascule a un coût : il faut recharger le contexte, retrouver où on en était. La recherche (Rubinstein, Meyer & Evans) montre que ces coûts de bascule peuvent gonfler le temps total de 20 à 40 %.

Pire : après une interruption, il faut en moyenne plusieurs minutes pour revenir au niveau d'attention d'avant. Croire qu'on gagne du temps en jonglant est l'illusion la plus coûteuse de la concentration.

## Comment

1. Fais l'expérience d'alternance ci-dessus, chrono en main. Le ressenti vaut tous les discours.
2. Aujourd'hui, repère une situation où tu jongles (mails + réunion, chat + rédaction).
3. Pose-toi la question : et si je faisais l'un *puis* l'autre ? Tu y reviendras dans le bloc single-tasking.
MD,
            ],
            [
                'day' => 5,
                'theme' => 'Comprendre ton attention',
                'title' => "Trouve ton pic d'attention",
                'summary' => "Ton attention n'est pas constante dans la journée. Repère ton heure de pointe.",
                'micro_challenge' => "Note ton niveau d'attention (de 1 à 5) à trois moments aujourd'hui : matin, début d'après-midi, fin de journée. Demain, tu sauras quand placer ce qui compte vraiment.",
                'duration_min' => 5,
                'icon' => 'sun',
                'body' => <<<MD
## Pourquoi

Personne n'est aussi affûté à 16 h qu'à son meilleur moment de la journée. Chacun a un **chronotype** : certains sont au sommet le matin, d'autres en fin de journée. Gaspiller son pic d'attention sur des mails et garder les tâches exigeantes pour le creux de l'après-midi, c'est ramer à contre-courant.

Repérer ta fenêtre de haute attention te permet d'y placer ce qui compte — et de réserver les tâches mécaniques pour les creux.

## Comment

1. Pendant un ou deux jours, note ton niveau d'attention à 3 moments (matin / après-midi / soir), de 1 à 5.
2. Repère ta **fenêtre haute** : 1 à 3 heures où tu es le plus net.
3. Règle simple : protège cette fenêtre pour ta tâche la plus importante. N'y mets jamais tes mails.
MD,
            ],
            [
                'day' => 6,
                'theme' => 'Comprendre ton attention',
                'title' => "Le rythme des 90 minutes",
                'summary' => "Ton attention fonctionne par vagues. Travaille avec elles, pas contre.",
                'micro_challenge' => "Aujourd'hui, après environ 90 minutes de travail, prends une vraie pause de 10-15 minutes (sans écran). Observe comme la reprise est plus nette qu'après avoir forcé.",
                'duration_min' => 5,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

Le corps suit des cycles d'environ 90 minutes, jour et nuit — les **rythmes ultradiens** décrits par Kleitman. Après ~90 minutes d'effort mental soutenu, l'attention chute naturellement : on bâille, on relit la même phrase, on se met à grignoter ou à scroller.

Ce n'est pas un manque de volonté, c'est de la biologie. Forcer au-delà donne du travail de mauvaise qualité. Respecter la vague — effort puis récupération — permet de soutenir l'attention sur toute une journée.

## Comment

1. Pense ta journée en blocs d'environ 90 minutes, pas en heures continues.
2. Au bout d'un bloc, fais une pause **réelle** de 10-15 minutes : bouge, regarde au loin, hydrate-toi, sans écran.
3. La pause n'est pas du temps perdu : c'est ce qui recharge la batterie pour le bloc suivant.
MD,
            ],
            [
                'day' => 7,
                'theme' => 'Comprendre ton attention',
                'title' => "L'attention est une ressource limitée",
                'summary' => "Chaque décision, chaque résistance puise dans le même réservoir.",
                'micro_challenge' => "Repère une décision triviale que tu prends chaque jour (quoi porter, quoi manger, par où commencer). Automatise-la pour demain (décide ce soir). Garde ton énergie mentale pour ce qui compte.",
                'duration_min' => 8,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

L'attention et la maîtrise de soi puisent dans une réserve d'énergie mentale qui s'épuise au fil de la journée. Chaque petite décision (« je réponds maintenant ou plus tard ? »), chaque distraction à laquelle tu résistes, prélève un peu de carburant. C'est pourquoi on tient mieux le matin et qu'on craque le soir.

La conséquence est libératrice : se concentrer n'est pas qu'une affaire de volonté, c'est aussi une affaire de **gestion d'énergie**. On protège son attention en réduisant le nombre de décisions inutiles et de tentations à repousser.

## Comment

1. Repère les micro-décisions répétitives qui grignotent ton énergie sans rien apporter.
2. Automatise-en une : décide-la à l'avance, crée une routine, ou supprime le choix.
3. Plus tu réduis le bruit décisionnel, plus il te reste d'attention pour l'essentiel.
MD,
            ],
            [
                'day' => 8,
                'theme' => 'Comprendre ton attention',
                'title' => "Définis ta concentration",
                'summary' => "« Mieux se concentrer » est trop vague pour réussir. Rends-le concret.",
                'micro_challenge' => "Complète cette phrase et garde-la sous les yeux : « Dans 60 jours, je veux pouvoir ___ pendant ___ minutes sans ___. » Exemple : lire 40 min sans toucher mon téléphone.",
                'duration_min' => 10,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

Un objectif flou ne mobilise pas. « Je veux être plus concentré » ne dit ni quoi, ni combien, ni comment on saura que c'est gagné. Un objectif **observable** — une durée, une tâche, une condition — donne une direction claire et un moyen de mesurer les progrès.

Tu as maintenant une ligne de base (J1) et une carte de tes voleurs d'attention (J2). Tu peux transformer ça en cible nette.

## Comment

1. Choisis **une** activité où la concentration compte le plus pour toi (travail, étude, lecture, création).
2. Fixe une cible mesurable : « X minutes d'attention soutenue sur cette tâche, sans Y ».
3. Écris-la et garde-la visible. À mi-parcours (J30) et à la fin (J60), tu reviendras la mesurer.
MD,
            ],

            // ===================================================================
            // BLOC 2 — AMÉNAGER LE SANCTUAIRE (J9-16)
            // ===================================================================
            [
                'day' => 9,
                'theme' => 'Aménager le sanctuaire',
                'title' => "Crée un lieu dédié",
                'summary' => "Ton cerveau associe les lieux à des comportements. Donne-lui un lieu pour se concentrer.",
                'micro_challenge' => "Désigne un endroit précis (un coin de bureau, une chaise, une table) qui ne servira qu'à te concentrer aujourd'hui. Quand tu t'y assieds, c'est le signal : ici, on se concentre.",
                'duration_min' => 10,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Le cerveau apprend par association. Si tu travailles, manges, scrolles et regardes des vidéos au même endroit, ce lieu envoie des signaux contradictoires. Un espace **réservé** à la concentration devient, à force, un déclencheur : t'y installer suffit à amorcer le bon état mental.

Pas besoin d'un bureau parfait. Un coin stable, toujours le même, suffit à créer l'ancrage.

## Comment

1. Choisis un endroit que tu peux dédier, même petit, même partagé dans le temps.
2. Décide une règle : ici, on ne fait *que* se concentrer (pas de repas, pas de scroll).
3. Quand tu t'y installes, marque le coup mentalement : « j'entre dans le sanctuaire ».
4. Plus tu respectes la règle, plus l'association se renforce.
MD,
            ],
            [
                'day' => 10,
                'theme' => 'Aménager le sanctuaire',
                'title' => "Dégage le champ visuel",
                'summary' => "Ce que tu vois, ton cerveau le traite. Un bureau encombré encombre l'attention.",
                'micro_challenge' => "Avant ta prochaine session, enlève de ton champ de vision tout ce qui n'a rien à voir avec la tâche : objets, papiers, tasses, post-its d'autres sujets. Surface nue = esprit dégagé.",
                'duration_min' => 8,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Chaque objet visible est une porte ouverte vers une autre pensée : ce courrier non traité, ce livre commencé, cette facture. Même sans y penser consciemment, ton cerveau les enregistre et y consacre une fraction d'attention. Un champ visuel chargé fragmente la concentration.

Réduire le visuel, c'est réduire le nombre de « tâches fantômes » qui réclament ton attention en arrière-plan.

## Comment

1. Avant de commencer, balaie ton plan de travail du regard.
2. Retire tout ce qui n'est pas lié à la tâche du moment — hors de vue, pas juste sur le côté.
3. Ne garde que ce dont tu as besoin maintenant.
4. À l'écran aussi : ferme les fenêtres et onglets sans rapport. Le désordre numérique fatigue autant.
MD,
            ],
            [
                'day' => 11,
                'theme' => 'Aménager le sanctuaire',
                'title' => "Mets le téléphone hors d'atteinte",
                'summary' => "La simple présence du téléphone, éteint, réduit déjà ta capacité d'attention.",
                'micro_challenge' => "Pour ta prochaine session, mets ton téléphone dans une autre pièce (ou un tiroir fermé, loin). Pas en mode silencieux sur le bureau : hors d'atteinte. Observe la différence.",
                'duration_min' => 5,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Une étude marquante (Ward et al., « Brain Drain ») a montré que la seule présence du smartphone sur le bureau — écran éteint, retourné — suffit à réduire les capacités d'attention et de mémoire de travail. Une partie de ton cerveau reste mobilisée à *ne pas* le regarder, et ça consomme la ressource même dont tu as besoin.

La distance physique est l'un des leviers les plus puissants et les plus simples de tout le parcours. Hors de vue, hors d'atteinte, hors d'esprit.

## Comment

1. Avant une session de concentration, éloigne **physiquement** le téléphone : autre pièce, sac, tiroir.
2. La friction est le but : devoir te lever pour le prendre suffit à casser l'automatisme.
3. Si tu attends un appel important, mets seulement ce contact en sonnerie et garde l'appareil loin.
MD,
            ],
            [
                'day' => 12,
                'theme' => 'Aménager le sanctuaire',
                'title' => "Coupe les notifications",
                'summary' => "Chaque notification est une invitation à perdre le fil. Ferme la porte.",
                'micro_challenge' => "Désactive aujourd'hui les notifications de 3 applications qui t'interrompent le plus (hors urgences réelles). Garde-les coupées et juge à la fin de la journée si le monde s'est écroulé.",
                'duration_min' => 10,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Une notification ne prend pas « juste une seconde ». Elle capture l'attention, déclenche une bascule, et il faut ensuite du temps pour revenir. Multipliées par des dizaines par jour, les notifications hachent l'attention en confettis. Le pire : même ignorées, elles créent une vigilance de fond.

Tu n'as pas à être joignable en temps réel par chaque application. Reprendre la main sur les notifications, c'est décider *toi* quand tu regardes, au lieu de te laisser convoquer.

## Comment

1. Ouvre les réglages de notifications de ton téléphone et de ton ordinateur.
2. Coupe tout ce qui n'est pas une vraie urgence humaine : réseaux, jeux, promos, mails non critiques.
3. Garde idéalement seulement les appels et messages de tes proches.
4. Passe du « push » (ça vient à toi) au « pull » (tu vas voir quand tu décides).
MD,
            ],
            [
                'day' => 13,
                'theme' => 'Aménager le sanctuaire',
                'title' => "Un seul onglet, un seul écran",
                'summary' => "Le désordre numérique est un champ de mines pour l'attention. Déminе-le.",
                'micro_challenge' => "Pour ta prochaine session : ferme tous les onglets sauf celui dont tu as besoin, et mets ton appli en plein écran. Un écran = une tâche. Rien d'autre de visible.",
                'duration_min' => 8,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

Vingt onglets ouverts, c'est vingt tentations à un clic. Chaque onglet visible est une tâche en attente qui murmure « regarde-moi ». Le plein écran, lui, supprime la barre des tâches, l'horloge, les autres fenêtres : il ne reste que toi et la tâche.

Réduire les chemins de fuite est plus efficace que de résister à chacun. On ne lutte pas contre une distraction qu'on a rendue invisible.

## Comment

1. Avant de démarrer, ferme (ou mets de côté dans une liste) tous les onglets sans rapport.
2. Garde seulement ce dont tu as besoin maintenant.
3. Passe l'application en plein écran pour masquer le reste de l'interface.
4. Si tu dois ouvrir un nouvel onglet « juste pour vérifier », note plutôt la chose sur ta feuille et continue.
MD,
            ],
            [
                'day' => 14,
                'theme' => 'Aménager le sanctuaire',
                'title' => "Maîtrise le son",
                'summary' => "Le bon environnement sonore protège l'attention. Le mauvais la sabote.",
                'micro_challenge' => "Teste aujourd'hui un environnement sonore pour te concentrer : silence, bruit blanc/de pluie, ou musique sans paroles. Une seule condition par session. Note celle qui te garde le plus dans le fil.",
                'duration_min' => 8,
                'icon' => 'ear',
                'body' => <<<MD
## Pourquoi

Le bruit imprévisible — une conversation, une notification sonore, un open space — est l'un des pires ennemis de l'attention, parce que le cerveau ne peut pas s'empêcher de traiter la parole. À l'inverse, un fond sonore régulier et sans langage (bruit blanc, pluie, musique instrumentale) peut masquer les interruptions et stabiliser l'attention.

La musique avec paroles, elle, entre en compétition avec les tâches verbales (lire, écrire) : elle aide rarement à ce moment-là.

## Comment

1. Identifie ta principale gêne sonore (collègues, rue, foyer).
2. Teste une parade : casque, bruit blanc, playlist instrumentale, ou simplement un lieu plus calme.
3. Évite les paroles si ta tâche est verbale.
4. Garde une seule recette qui marche : tu n'as pas à réinventer ton ambiance chaque jour.
MD,
            ],
            [
                'day' => 15,
                'theme' => 'Aménager le sanctuaire',
                'title' => "Prépare la veille",
                'summary' => "La concentration de demain se gagne ce soir, en décidant par où tu commences.",
                'micro_challenge' => "Ce soir, écris en une ligne la première tâche précise de demain et pose tout ce qu'il faut pour la commencer. Demain, tu démarres sans réfléchir — donc sans résister.",
                'duration_min' => 8,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Le moment le plus vulnérable d'une session, c'est le démarrage : tant que tu n'as pas décidé *quoi* faire exactement, le cerveau cherche une échappatoire — et la trouve (un coup d'œil aux mails, au téléphone). Décider la veille supprime ce vide. Tu arrives, et le chemin est déjà tracé.

Préparer son environnement à l'avance réduit aussi le nombre de décisions à prendre dans le moment, là où ta volonté est la plus fragile (cf. J7).

## Comment

1. En fin de journée, choisis **la** première tâche du lendemain — précise, pas « avancer le projet ».
2. Mets en place ce qu'il faut : document ouvert, page marquée, outils prêts.
3. Écris la phrase de départ : « Demain, je commence par… ».
4. Le matin, tu n'as plus qu'à exécuter, sans laisser de place à l'hésitation.
MD,
            ],
            [
                'day' => 16,
                'theme' => 'Aménager le sanctuaire',
                'title' => "Crée un rituel de démarrage",
                'summary' => "Un même geste répété avant de te concentrer devient l'interrupteur de l'attention.",
                'micro_challenge' => "Invente un mini-rituel de 60 secondes à faire avant chaque session (ex. : ranger le bureau, un verre d'eau, 3 respirations, écrire la tâche). Fais-le aujourd'hui. Répété, il deviendra ton signal.",
                'duration_min' => 8,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Attendre d'« avoir envie » de se concentrer est une stratégie perdante : l'envie vient rarement à la commande. Un **rituel** court contourne le problème : en répétant toujours la même séquence avant de travailler, tu crées un automatisme qui amorce l'état de concentration sans négocier avec ta motivation.

C'est le même principe que l'athlète qui a sa routine avant de jouer : le geste précède et déclenche l'état mental.

## Comment

1. Compose une séquence simple de 3-4 gestes, toujours la même (ranger, eau, respirer, écrire la tâche).
2. Garde-la courte : moins de 2 minutes, sinon tu finiras par la sauter.
3. Exécute-la avant chaque session, même les jours où tu n'en ressens pas le besoin.
4. Au fil des jours, le rituel devient l'interrupteur : tu le fais, et l'attention s'allume.
MD,
            ],

            // ===================================================================
            // BLOC 3 — LE MUSCLE DE L'ATTENTION (J17-24)
            // ===================================================================
            [
                'day' => 17,
                'theme' => "Le muscle de l'attention",
                'title' => "Trois minutes sur le souffle",
                'summary' => "L'exercice fondateur : poser l'attention sur la respiration, et l'y ramener.",
                'micro_challenge' => "Assieds-toi, règle un minuteur sur 3 minutes, et porte ton attention sur les sensations de ta respiration. Quand l'esprit part, reviens au souffle. C'est tout. Fais-le une fois aujourd'hui.",
                'duration_min' => 5,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

La respiration est l'objet d'attention le plus pratique qui soit : toujours disponible, toujours dans le présent. S'entraîner à y poser son attention, c'est faire des répétitions pour le muscle attentionnel — exactement comme on soulève une charge pour un muscle physique. La recherche sur l'entraînement de l'attention (Tang & Posner) montre que quelques minutes par jour suffisent à produire des effets mesurables en quelques semaines.

L'objectif n'est pas de « faire le vide ». C'est de remarquer quand tu es parti, et de revenir.

## Comment

1. Assieds-toi confortablement, dos droit mais sans raideur.
2. Règle un minuteur sur 3 minutes.
3. Porte ton attention sur les sensations physiques du souffle (narines, poitrine, ventre).
4. Dès que tu remarques que l'esprit a vagabondé, reviens doucement au souffle, sans te juger.
MD,
            ],
            [
                'day' => 18,
                'theme' => "Le muscle de l'attention",
                'title' => "Le balayage du corps",
                'summary' => "Promener son attention dans le corps muscle le contrôle attentionnel volontaire.",
                'micro_challenge' => "En 5 minutes, déplace lentement ton attention des pieds jusqu'à la tête, en t'arrêtant sur chaque zone pour sentir ce qui s'y passe. Ton attention devient un projecteur que tu diriges.",
                'duration_min' => 7,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Le balayage corporel (body scan) entraîne une compétence clé : **déplacer volontairement** le foyer de l'attention et le maintenir là où on l'a posé. C'est précisément ce qu'on fait quand on reste concentré sur une tâche : on choisit où va le projecteur, et on l'y tient.

En prime, c'est un puissant moyen de revenir dans le présent quand l'esprit est dispersé ou agité.

## Comment

1. Allonge-toi ou assieds-toi, ferme les yeux si tu le souhaites.
2. Porte ton attention sur tes pieds : que ressens-tu ? Chaleur, contact, picotements, rien ?
3. Remonte lentement, zone par zone : jambes, bassin, ventre, dos, mains, bras, épaules, visage.
4. Si l'esprit s'échappe, note-le et reviens à la dernière zone. La précision compte plus que la vitesse.
MD,
            ],
            [
                'day' => 19,
                'theme' => "Le muscle de l'attention",
                'title' => "Revenir, c'est l'exercice",
                'summary' => "Chaque retour après une distraction est une répétition. Ce n'est pas un échec, c'est la séance.",
                'micro_challenge' => "Pendant 5 minutes d'attention au souffle, compte mentalement chaque fois que tu te surprends à être parti et que tu reviens. Vise beaucoup de retours : chaque retour est un point gagné, pas un point perdu.",
                'duration_min' => 6,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

La plupart des gens abandonnent l'entraînement de l'attention parce qu'ils croient « ne pas y arriver » : leur esprit part sans cesse. C'est un contresens total. Le moment où tu remarques que tu es parti et où tu reviens **est** l'exercice — comme la flexion est l'exercice pour le biceps. Un esprit qui vagabonde n'est pas un problème : c'est le matériel d'entraînement.

Reformuler ainsi change tout : tu ne peux plus « rater » la séance, tu peux seulement faire plus ou moins de répétitions.

## Comment

1. Lance 5 minutes d'attention au souffle.
2. Chaque fois que tu te surprends ailleurs, dis intérieurement « retour » et reviens.
3. Au lieu de t'agacer, félicite-toi : tu viens de faire une répétition.
4. À la fin, le nombre de retours n'est pas une honte — c'est ton volume d'entraînement du jour.
MD,
            ],
            [
                'day' => 20,
                'theme' => "Le muscle de l'attention",
                'title' => "Fixer un point",
                'summary' => "Concentrer le regard sur un seul point rassemble une attention dispersée.",
                'micro_challenge' => "Choisis un petit objet (une bougie, un point sur le mur, ton stylo). Pose ton regard et ton attention dessus pendant 3 minutes, sans le quitter. Quand l'esprit fuit, reviens à l'objet.",
                'duration_min' => 5,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

La concentration sur un point unique est l'une des plus anciennes pratiques d'entraînement de l'attention. En réduisant le champ à un seul objet, tu donnes à l'esprit une cible claire et tu rends évidente la moindre dérive. C'est de l'attention focalisée à l'état pur — la même qu'on mobilise pour serrer une tâche exigeante.

L'œil aide l'esprit : là où se pose le regard, l'attention tend à suivre.

## Comment

1. Place un objet simple à hauteur de regard, à un mètre environ.
2. Pose les yeux dessus et observe-le en détail : forme, couleur, texture, ombres.
3. Quand ton attention s'échappe, ramène-la sur l'objet, encore et encore.
4. Cligne normalement, ne force pas le regard : c'est l'attention qu'on entraîne, pas les yeux.
MD,
            ],
            [
                'day' => 21,
                'theme' => "Le muscle de l'attention",
                'title' => "Compter les respirations",
                'summary' => "Donner un fil à suivre à l'esprit l'empêche de se disperser.",
                'micro_challenge' => "Compte tes respirations de 1 à 10, puis recommence à 1. Si tu te perds ou dépasses 10, c'est que tu étais parti : reviens à 1, sans drame. Tiens 5 minutes.",
                'duration_min' => 6,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Compter donne à l'attention une structure : un repère pour savoir, en temps réel, si tu es encore là. Le moment où tu réalises que tu as compté jusqu'à 17 (ou que tu as oublié où tu en étais) est une information précieuse : ton attention avait décroché sans que tu le saches. L'exercice rend visible l'invisible.

C'est aussi un excellent baromètre de progrès : avec l'entraînement, tu te perdras moins souvent.

## Comment

1. Inspire, expire : « un ». Inspire, expire : « deux ». Jusqu'à dix.
2. Arrivé à dix, repars à un. Ne va jamais au-delà de dix (sinon le pilote automatique reprend).
3. Si tu perds le compte ou dépasses, reviens simplement à un.
4. Le but n'est pas d'atteindre dix parfaitement, mais de rester présent à chaque chiffre.
MD,
            ],
            [
                'day' => 22,
                'theme' => "Le muscle de l'attention",
                'title' => "Écouter un seul son",
                'summary' => "Accrocher l'attention à un son entraîne l'écoute profonde et la présence.",
                'micro_challenge' => "Trouve un son continu ou des sons ambiants. Pendant 3 minutes, écoute-les vraiment : leurs détails, leurs variations, les silences. Quand tu pars dans tes pensées, reviens au son.",
                'duration_min' => 5,
                'icon' => 'ear',
                'body' => <<<MD
## Pourquoi

L'audition est une porte d'entrée idéale vers le présent : un son n'existe que maintenant. S'entraîner à écouter pleinement — sans commenter, sans nommer, juste percevoir — développe une attention réceptive, ouverte, qui complète l'attention focalisée du regard. C'est aussi un antidote immédiat à la rumination, car on ne peut pas écouter vraiment et ressasser en même temps.

## Comment

1. Choisis une source sonore : une musique, le bruit de la pièce, les sons de dehors.
2. Pendant quelques minutes, écoute comme si tu devais en faire le portrait : timbre, hauteur, rythme, silences.
3. Évite de mettre des mots ou des jugements (« joli », « agaçant ») : reste dans la perception brute.
4. Dès que l'esprit commente ou s'éloigne, reviens au son lui-même.
MD,
            ],
            [
                'day' => 23,
                'theme' => "Le muscle de l'attention",
                'title' => "Marcher en pleine présence",
                'summary' => "On peut entraîner l'attention en mouvement, sans s'asseoir ni fermer les yeux.",
                'micro_challenge' => "Marche pendant 5 minutes (dehors ou chez toi) en portant toute ton attention sur les sensations de tes pieds qui se posent. Pas de téléphone, pas d'objectif de destination : juste marcher et sentir.",
                'duration_min' => 7,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

L'attention n'a pas besoin d'immobilité. La marche attentive ancre l'esprit dans des sensations concrètes et rythmées (le contact du pied, le balancement, le souffle), ce qui en fait un entraînement accessible même quand rester assis te paraît impossible. C'est aussi un pont vers le quotidien : tu marches chaque jour, donc tu peux t'entraîner chaque jour.

## Comment

1. Marche à un rythme normal ou légèrement ralenti, sans destination précise.
2. Porte ton attention sur la plante des pieds : le contact, le déroulé, le transfert de poids.
3. Élargis ensuite, si tu veux, aux jambes, au souffle, à l'air sur ta peau.
4. Laisse le téléphone de côté. Quand l'esprit part, reviens aux pieds.
MD,
            ],
            [
                'day' => 24,
                'theme' => "Le muscle de l'attention",
                'title' => "Une tâche banale, pleinement",
                'summary' => "La concentration s'entraîne aussi dans la vaisselle, le café, la douche.",
                'micro_challenge' => "Choisis une tâche routinière aujourd'hui (vaisselle, douche, préparer un repas) et fais-la avec 100 % de ton attention sur les gestes et les sensations. Pas de musique, pas de podcast, pas de pensées de la suite.",
                'duration_min' => 8,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

On passe une grande partie de la journée en pilote automatique, l'esprit ailleurs pendant que le corps agit. Transformer une tâche banale en exercice d'attention, c'est multiplier les occasions de t'entraîner — sans ajouter une minute à ton agenda. Et c'est apprendre une chose précieuse : **être tout entier à ce qu'on fait**, qui est l'essence même de la concentration.

## Comment

1. Choisis une activité simple et répétitive que tu fais d'habitude « sans y penser ».
2. Fais-la en y mettant toute ton attention : les sensations, les gestes, les détails.
3. Coupe les distractions parallèles (pas de musique ni de podcast pour cette fois).
4. Quand tu remarques que l'esprit est parti vers « après », ramène-le au geste présent.
MD,
            ],

            // ===================================================================
            // BLOC 4 — UNE CHOSE À LA FOIS (J25-32)
            // ===================================================================
            [
                'day' => 25,
                'theme' => 'Une chose à la fois',
                'title' => "Ta première session minutée",
                'summary' => "Un bloc de temps court et défini transforme l'intention en concentration réelle.",
                'micro_challenge' => "Choisis une tâche, règle un minuteur sur 25 minutes, et travaille uniquement dessus jusqu'à la sonnerie. Si une distraction surgit, note-la et reviens. Une seule session aujourd'hui.",
                'duration_min' => 25,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

Le travail minuté (popularisé sous le nom de Pomodoro) marche pour une raison simple : un temps **court et délimité** rend l'effort tenable. Se dire « je travaille tout l'après-midi » est écrasant ; « je travaille 25 minutes » est faisable. La sonnerie de fin enlève l'angoisse : tu sais qu'une pause arrive.

C'est aussi un contrat clair avec toi-même : pendant ce bloc, *une seule* chose existe.

## Comment

1. Choisis une tâche précise et règle un minuteur sur 25 minutes.
2. Travaille uniquement dessus. Pas de mails, pas d'onglets, pas de téléphone.
3. Si une distraction ou une idée surgit, note-la sur une feuille et reviens immédiatement.
4. À la sonnerie, arrête-toi et prends 5 minutes de vraie pause. Tu as tenu un bloc entier : c'est la victoire.
MD,
            ],
            [
                'day' => 26,
                'theme' => 'Une chose à la fois',
                'title' => "Une seule tâche par bloc",
                'summary' => "Le secret du single-tasking : décider à l'avance ce que tu ne feras pas.",
                'micro_challenge' => "Avant ta session, écris en haut d'une feuille la SEULE tâche autorisée pendant ce bloc. Tout le reste est interdit jusqu'à la sonnerie, même les « petites choses rapides ».",
                'duration_min' => 25,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

La tentation, pendant une session, n'est pas seulement la distraction externe : c'est de glisser vers une *autre* tâche utile (« je vais juste répondre à ce mail vite fait »). Chaque glissement est une bascule, avec son coût (cf. J4). Décider à l'avance la seule tâche autorisée crée une frontière nette : tout le reste attend.

« Une chose à la fois » n'est pas un slogan moral, c'est la condition technique d'une attention pleine.

## Comment

1. Avant le bloc, écris la tâche unique en haut d'une feuille.
2. Tout ce qui surgit d'autre — même légitime — va dans une liste « plus tard », pas dans l'action.
3. Si tu termines avant la sonnerie, continue d'approfondir la même tâche plutôt que d'en ouvrir une autre.
4. La règle est binaire : pendant ce bloc, c'est cette tâche, ou rien.
MD,
            ],
            [
                'day' => 27,
                'theme' => 'Une chose à la fois',
                'title' => "La chose la plus importante",
                'summary' => "Concentre-toi sur ce qui compte, pas seulement sur ce qui est urgent ou facile.",
                'micro_challenge' => "Ce matin (ou maintenant), demande-toi : « Si je ne fais qu'UNE chose de vraie valeur aujourd'hui, laquelle ? » Écris-la, et donne-lui ton premier bloc de concentration, avant tout le reste.",
                'duration_min' => 10,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

Se concentrer n'a de sens que si c'est sur la bonne chose. Or l'attention est facilement happée par l'urgent et le facile (mails, petites tâches), au détriment de l'important. Identifier chaque jour ta **tâche la plus importante** et la traiter en premier garantit qu'au moins une chose de valeur sera faite, même si la journée déraille ensuite.

C'est l'application directe de ton pic d'attention (J5) : la meilleure énergie pour la tâche qui compte le plus.

## Comment

1. Avant de te noyer dans la liste, pose la question : « Quelle est la tâche qui aurait le plus de valeur si elle était faite aujourd'hui ? »
2. Écris-la. Une seule.
3. Réserve-lui ton premier bloc de concentration de la journée, idéalement dans ta fenêtre haute.
4. Le reste vient après. L'important d'abord, l'urgent ensuite.
MD,
            ],
            [
                'day' => 28,
                'theme' => 'Une chose à la fois',
                'title' => "Vide les boucles ouvertes",
                'summary' => "Les tâches non notées tournent en fond et grignotent ton attention.",
                'micro_challenge' => "Prends 10 minutes et vide ta tête sur papier : tout ce que tu as « à faire », « à ne pas oublier », « à régler ». Une fois écrites, ces boucles cessent de tourner en arrière-plan. Ton esprit se libère pour se concentrer.",
                'duration_min' => 10,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

Tout ce que tu cherches à retenir « dans ta tête » consomme de l'attention en continu : c'est l'effet Zeigarnik, cette tendance du cerveau à garder actives les tâches inachevées. Plus tu as de boucles ouvertes non capturées, plus le bruit de fond est fort, et moins il te reste d'attention pour la tâche présente.

Externaliser sur une liste fiable (le principe central de la méthode GTD de David Allen) éteint ce bruit : l'esprit accepte de lâcher ce qu'il sait noté ailleurs.

## Comment

1. Prends une feuille ou une note, et écris **tout** ce qui te trotte en tête : tâches, idées, choses à ne pas oublier.
2. Ne tri pas, ne hiérarchise pas pour l'instant : vide, simplement.
3. Range cette liste dans un endroit unique et fiable que tu reverras.
4. Refais ce vidage chaque fois que ta tête est encombrée. Un esprit déchargé se concentre mieux.
MD,
            ],
            [
                'day' => 29,
                'theme' => 'Une chose à la fois',
                'title' => "Regroupe les tâches semblables",
                'summary' => "Faire d'un coup les tâches de même nature évite les bascules coûteuses.",
                'micro_challenge' => "Repère des petites tâches éparses de même type (mails, appels, paperasse). Au lieu de les traiter à mesure qu'elles arrivent, regroupe-les en un seul bloc dédié aujourd'hui.",
                'duration_min' => 10,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Passer d'un mail à un document à un appel puis à nouveau à un mail, c'est enchaîner les bascules — et payer le coût de rechargement à chaque fois (cf. J4). Regrouper les tâches de même nature (le *batching*) supprime ces transitions : l'esprit reste dans un seul mode, un seul contexte, et va beaucoup plus vite.

C'est particulièrement vrai pour les tâches « réactives » comme les mails et messages, qui sinon fragmentent toute la journée.

## Comment

1. Repère les catégories de petites tâches qui reviennent : mails, appels, administratif, courses.
2. Au lieu de les traiter en continu, fixe **un créneau** dédié à chaque catégorie.
3. Hors de ce créneau, laisse-les s'accumuler sans culpabilité : elles attendront leur bloc.
4. En particulier, traite tes mails en 2-3 sessions groupées plutôt qu'en flux permanent.
MD,
            ],
            [
                'day' => 30,
                'theme' => 'Une chose à la fois',
                'title' => "L'art de démarrer",
                'summary' => "La résistance est au début. Rends le premier pas ridiculement petit.",
                'micro_challenge' => "Pour la tâche que tu repousses, engage-toi seulement à la commencer 5 minutes — avec le droit d'arrêter ensuite. Le plus souvent, tu continueras. Démarre maintenant.",
                'duration_min' => 8,
                'icon' => 'rocket',
                'body' => <<<MD
## Pourquoi

La concentration ne bute presque jamais sur la tâche elle-même, mais sur son **démarrage**. La résistance est maximale avant de commencer et fond une fois lancé. L'astuce consiste à rendre le premier pas si petit qu'il devient impossible à refuser : non pas « écrire le rapport » mais « ouvrir le document et écrire une phrase ».

Te donner la permission d'arrêter après 5 minutes lève le frein. Et neuf fois sur dix, l'élan pris fait que tu continues.

## Comment

1. Réduis la tâche à un premier geste minuscule et concret (« ouvrir », « écrire une ligne », « lire un paragraphe »).
2. Engage-toi seulement sur 5 minutes, avec le droit explicite de t'arrêter après.
3. Démarre. Observe : la plupart du temps, tu es déjà dedans et tu continues.
4. Les jours sans élan, refaire ce micro-engagement vaut mieux qu'attendre la motivation.
MD,
            ],
            [
                'day' => 31,
                'theme' => 'Une chose à la fois',
                'title' => "Surfer l'envie de vérifier",
                'summary' => "L'impulsion de checker monte, culmine, puis redescend si tu ne la nourris pas.",
                'micro_challenge' => "À la prochaine envie d'attraper ton téléphone pendant une session, ne cède pas tout de suite : observe l'impulsion comme une vague, sans bouger, pendant 30 secondes. Elle retombe. Reste sur ta tâche.",
                'duration_min' => 8,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

L'envie de vérifier son téléphone, ses mails ou ses messages fonctionne comme une vague : elle monte, atteint un pic, puis redescend d'elle-même si on ne lui obéit pas. Les psychologues appellent cela « surfer l'impulsion » (urge surfing, Marlatt). Le problème, c'est qu'on cède d'habitude au pic, ce qui renforce l'habitude. Observer l'envie sans agir l'affaiblit, fois après fois.

Tu n'as pas à lutter contre l'envie : juste à la laisser passer sans la nourrir.

## Comment

1. Quand l'impulsion de checker surgit, ne la combats pas et n'y obéis pas : remarque-la.
2. Observe-la dans le corps (où ça tire, ça démange) comme une vague qui monte.
3. Respire et attends : 20 à 40 secondes suffisent souvent pour qu'elle redescende.
4. Reviens à ta tâche. Chaque vague surfée affaiblit l'automatisme.
MD,
            ],
            [
                'day' => 32,
                'theme' => 'Une chose à la fois',
                'title' => "La vraie pause",
                'summary' => "Scroller n'est pas se reposer. L'attention ne se régénère qu'en se détachant.",
                'micro_challenge' => "À ta prochaine pause, ne prends pas ton téléphone. Lève-toi, regarde au loin, marche, étire-toi ou respire pendant 5 minutes. Compare ton niveau d'attention à la reprise avec une pause-écran habituelle.",
                'duration_min' => 7,
                'icon' => 'sun',
                'body' => <<<MD
## Pourquoi

On croit se reposer en scrollant entre deux tâches, mais l'écran sollicite exactement les mêmes circuits attentionnels que le travail : on en sort aussi fatigué, parfois plus. La vraie récupération attentionnelle passe par le **détachement** : regarder au loin, bouger, être dans le corps, laisser l'esprit en mode diffus (cf. J3).

Une bonne pause n'est pas une récompense ; c'est ce qui rend possible le bloc suivant.

## Comment

1. À la fin d'un bloc, laisse le téléphone et l'écran de côté.
2. Lève-toi et change de posture : marche, étire-toi, va à la fenêtre.
3. Repose les yeux en regardant loin (la fatigue visuelle nourrit la fatigue d'attention).
4. 5 à 10 minutes suffisent. Tu reviens plus net qu'avec une pause-scroll.
MD,
            ],

            // ===================================================================
            // BLOC 5 — TENIR LA DURÉE (J33-40)
            // ===================================================================
            [
                'day' => 33,
                'theme' => 'Tenir la durée',
                'title' => "Allonge le bloc",
                'summary' => "Comme un muscle, l'attention se renforce en augmentant progressivement la charge.",
                'micro_challenge' => "Aujourd'hui, vise un bloc de 45 minutes au lieu de 25, sur une seule tâche. Si 45 c'est trop, fais 35. L'idée : pousser un peu au-delà de ta zone de confort, sans casser.",
                'duration_min' => 45,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

Au début, des blocs de 25 minutes installent l'habitude. Mais pour développer l'endurance attentionnelle, il faut augmenter la charge, comme on ajoute du poids à la barre. Allonger progressivement les sessions (35, 45, 50 minutes) entraîne l'attention à rester soutenue plus longtemps, ce qui ouvre l'accès aux tâches profondes qui demandent du temps pour « rentrer dedans ».

La clé est la progressivité : pousser un peu, pas trop. Trop d'un coup, et tu casses ; pas assez, et tu ne progresses pas.

## Comment

1. Pars de la durée que tu tiens déjà confortablement.
2. Ajoute 10-15 minutes : si tu tiens 25, vise 35-45.
3. Tiens-toi à la même tâche pendant tout le bloc, sans bascule.
4. Si tu sens une vraie chute (pas une simple envie de fuir), arrête : tu retenteras demain. On construit, on ne se punit pas.
MD,
            ],
            [
                'day' => 34,
                'theme' => 'Tenir la durée',
                'title' => "Le plan si… alors",
                'summary' => "Décider à l'avance ta réaction aux distractions double tes chances de tenir.",
                'micro_challenge' => "Écris une règle « si… alors » pour ta distraction n°1. Exemple : « Si l'envie d'ouvrir Instagram vient, alors je note la pensée et je respire trois fois. » Applique-la aujourd'hui à chaque déclenchement.",
                'duration_min' => 8,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Les recherches de Peter Gollwitzer sur les **intentions d'implémentation** sont sans appel : décider à l'avance « si la situation X se produit, alors je fais Y » augmente fortement le passage à l'action, parce que la réponse devient quasi automatique. Au lieu de devoir te décider dans l'instant — quand ta volonté est faible et l'impulsion forte — tu as déjà programmé ta réaction.

Applique-le à tes voleurs d'attention identifiés au J2 : chaque déclencheur reçoit une parade prête à l'emploi.

## Comment

1. Reprends ta distraction n°1.
2. Formule une règle précise : « Si [déclencheur], alors [action de remplacement] ».
3. Choisis une action simple et compatible avec rester concentré (noter, respirer, boire une gorgée d'eau).
4. Répète-la mentalement quelques fois pour l'ancrer, puis applique-la dès qu'elle se présente.
MD,
            ],
            [
                'day' => 35,
                'theme' => 'Tenir la durée',
                'title' => "Le carnet à portée de main",
                'summary' => "Noter une pensée intrusive permet de la lâcher sans la perdre.",
                'micro_challenge' => "Garde une feuille à côté de toi pendant ta session. Chaque idée, tâche ou inquiétude qui surgit, tu l'écris en deux mots et tu reviens. Tu la traiteras plus tard ; pour l'instant, tu restes.",
                'duration_min' => 8,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

Pendant une session, l'esprit produit constamment des « bonnes idées » et des rappels (« il faut que je pense à… »). Si tu les suis, tu décroches ; si tu essaies de les retenir, elles tournent en boucle et parasitent ton attention. La solution est un carnet de capture à portée de main : tu écris, donc tu peux lâcher, donc tu reviens.

C'est l'application en temps réel du vidage des boucles ouvertes (J28) : une soupape qui protège le fil de la session.

## Comment

1. Pose une feuille ou un carnet à côté de toi avant de commencer (pas une appli, pour éviter l'écran).
2. À chaque pensée parasite, note-la en quelques mots — assez pour la retrouver — puis reviens immédiatement.
3. Ne traite rien pendant la session : tu ranges, tu ne fais pas.
4. À la fin du bloc, regarde ta liste et décide quoi en faire.
MD,
            ],
            [
                'day' => 36,
                'theme' => 'Tenir la durée',
                'title' => "Rester avec l'ennui",
                'summary' => "On fuit la plupart des tâches non par difficulté, mais par inconfort. Apprends à le traverser.",
                'micro_challenge' => "Pendant ta session, quand tu sens monter l'ennui ou l'envie de fuir, ne bouge pas : reste 60 secondes de plus avec l'inconfort, en l'observant. Constate qu'il passe, et que tu peux continuer.",
                'duration_min' => 8,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

La plupart des distractions ne viennent pas de l'extérieur : elles sont une fuite d'un **inconfort intérieur** — ennui, frustration, doute, anxiété de mal faire. Attraper le téléphone est un soulagement immédiat. Si tu apprends à rester quelques instants avec l'inconfort sans le fuir, tu coupes le réflexe à la racine et tu découvres qu'il est tout à fait supportable, et passager.

Tolérer l'inconfort est peut-être la compétence centrale de la concentration durable.

## Comment

1. Quand l'envie de fuir monte, identifie l'émotion derrière : ennui ? frustration ? peur de l'échec ?
2. Nomme-la intérieurement (« voilà de l'ennui ») : nommer apaise déjà.
3. Reste avec elle 30 à 60 secondes sans agir, en respirant.
4. Observe qu'elle change et reflue. Puis reprends la tâche, là où tu l'avais laissée.
MD,
            ],
            [
                'day' => 37,
                'theme' => 'Tenir la durée',
                'title' => "Découpe en micro-objectifs",
                'summary' => "Une longue tâche écrase l'attention. Des étapes visibles la portent.",
                'micro_challenge' => "Prends une tâche longue et découpe-la en 3 à 5 mini-étapes concrètes. Coche-les au fur et à mesure. Chaque coche entretient l'élan et l'attention.",
                'duration_min' => 10,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Face à une tâche vaste et vague, l'attention se dilue : on ne sait pas par où prendre, on ne voit pas la fin, alors on fuit. Découper en micro-objectifs clairs et atteignables redonne une direction et, à chaque étape franchie, un petit shot de progression qui nourrit la motivation et soutient l'attention.

Le cerveau aime terminer des choses. Donne-lui beaucoup de petites fins plutôt qu'une seule, lointaine et intimidante.

## Comment

1. Prends la tâche qui te paraît trop grosse.
2. Découpe-la en 3 à 5 étapes concrètes, chacune réalisable en un bloc.
3. Écris-les en liste, dans l'ordre.
4. Avance étape par étape, en cochant. Concentre-toi uniquement sur l'étape en cours, pas sur tout le sommet.
MD,
            ],
            [
                'day' => 38,
                'theme' => 'Tenir la durée',
                'title' => "Trouver le flow",
                'summary' => "L'attention totale survient quand le défi égale ta compétence. Ajuste le curseur.",
                'micro_challenge' => "Évalue ta tâche du jour : trop facile (tu t'ennuies) ou trop dure (tu stresses) ? Ajuste-la pour qu'elle soit un peu au-dessus de ton niveau actuel — le point où l'attention s'absorbe d'elle-même.",
                'duration_min' => 10,
                'icon' => 'rocket',
                'body' => <<<MD
## Pourquoi

Le « flow » décrit par Csikszentmihalyi — cet état où l'on est si absorbé qu'on oublie le temps — n'arrive pas par hasard. Il apparaît dans une zone précise : quand le **défi de la tâche** correspond à peu près à ton **niveau de compétence**. Trop facile, tu t'ennuies et tu décroches ; trop dur, tu t'angoisses et tu fuis. Juste au-dessus de ton niveau, l'attention se verrouille naturellement.

Tu peux donc, en partie, fabriquer les conditions de la concentration en réglant la difficulté.

## Comment

1. Repère ton ressenti sur la tâche : ennui (trop facile) ou anxiété (trop dur) ?
2. Si c'est trop facile, ajoute une contrainte : une limite de temps, un standard plus élevé.
3. Si c'est trop dur, réduis : prends une sous-partie, baisse l'exigence du premier jet.
4. Vise le « légèrement au-dessus » : assez pour mobiliser, pas assez pour effrayer.
MD,
            ],
            [
                'day' => 39,
                'theme' => 'Tenir la durée',
                'title' => "Reprendre après une coupure",
                'summary' => "Les interruptions sont inévitables. Ce qui compte, c'est ta vitesse de retour.",
                'micro_challenge' => "Avant de quitter une tâche (pause, interruption), laisse-toi un indice de reprise : une note « je reprends à… », une phrase inachevée. Au retour, tu replonges en secondes au lieu de minutes.",
                'duration_min' => 8,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Après une interruption, retrouver le fil prend du temps — souvent plusieurs minutes pour revenir au niveau d'attention d'avant. Tu ne peux pas supprimer toutes les coupures, mais tu peux réduire drastiquement leur coût en te laissant un point de reprise clair. Reprendre devient alors instantané au lieu de coûteux.

L'astuce des écrivains : s'arrêter au milieu d'une phrase qu'on sait finir, pour redémarrer sans effort.

## Comment

1. Avant toute pause ou interruption prévisible, pose un repère : note « je reprends à… », ou laisse un geste évident à faire.
2. Pour une tâche d'écriture, arrête-toi volontairement au milieu d'une idée que tu sais terminer.
3. Au retour, commence par relire ton repère plutôt que tout reprendre de zéro.
4. Protège aussi les premières minutes du retour : c'est là que l'attention est la plus fragile.
MD,
            ],
            [
                'day' => 40,
                'theme' => 'Tenir la durée',
                'title' => "Deux blocs profonds",
                'summary' => "Quelques heures de concentration vraie valent mieux qu'une journée dispersée.",
                'micro_challenge' => "Planifie aujourd'hui DEUX blocs profonds (45-90 min chacun), à tes meilleurs moments. Entre les deux, tâches légères et repos. Vise la qualité de ces deux blocs, pas le remplissage de la journée.",
                'duration_min' => 10,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

L'attention de haute qualité est une ressource limitée : la recherche sur l'expertise (Ericsson) suggère que même les meilleurs ne soutiennent que quelques heures de pratique vraiment concentrée par jour. Vouloir être focalisé du matin au soir est irréaliste et conduit à l'épuisement. En revanche, garantir **deux blocs profonds** par jour, bien placés et bien protégés, suffit à produire l'essentiel de la valeur.

Le reste de la journée peut accueillir les tâches légères, sans culpabilité.

## Comment

1. Repère tes deux meilleures fenêtres d'attention de la journée (cf. J5).
2. Réserve-y deux blocs profonds, sur tes tâches qui comptent le plus.
3. Protège-les comme des rendez-vous : pas de réunions, pas de mails dedans.
4. Entre et autour, place le reste (mails, admin, échanges) et de vraies pauses.
MD,
            ],

            // ===================================================================
            // BLOC 6 — DOMPTER LA DISTRACTION (J41-48)
            // ===================================================================
            [
                'day' => 41,
                'theme' => 'Dompter la distraction',
                'title' => "Externe ou interne ?",
                'summary' => "On ne traite pas une notification comme une rumination. Distingue les deux.",
                'micro_challenge' => "Aujourd'hui, à chaque distraction, classe-la d'un mot : « externe » (vient du dehors) ou « interne » (vient de ta tête). Le soir, regarde laquelle domine : c'est elle qu'il faut traiter en priorité.",
                'duration_min' => 8,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Toutes les distractions ne se combattent pas de la même façon. Les distractions **externes** (notifications, bruit, sollicitations) se traitent par l'environnement : on les bloque, on les éloigne (blocs 2). Les distractions **internes** (pensées, inquiétudes, ennui, impulsions) se traitent par l'esprit : on les note, on les observe, on les laisse passer (blocs 3 et 5). Confondre les deux fait perdre du temps.

Beaucoup de gens blindent leur environnement puis se découvrent encore distraits : c'est que l'ennemi principal était à l'intérieur.

## Comment

1. Reprends une journée de travail et observe tes ruptures d'attention.
2. Pour chacune, demande-toi : est-ce venu du dehors (externe) ou de ma tête (interne) ?
3. Compte. Si l'externe domine, renforce l'environnement (notifs, téléphone, lieu).
4. Si l'interne domine, le vrai chantier est l'entraînement de l'attention et la tolérance à l'inconfort.
MD,
            ],
            [
                'day' => 42,
                'theme' => 'Dompter la distraction',
                'title' => "L'émotion sous la distraction",
                'summary' => "Derrière le geste de fuir, il y a presque toujours une émotion à entendre.",
                'micro_challenge' => "La prochaine fois que tu fuis vers une distraction, arrête-toi une seconde et demande : « Qu'est-ce que je ressens là, tout de suite ? » Nomme l'émotion. Souvent, la nommer suffit à desserrer son emprise.",
                'duration_min' => 8,
                'icon' => 'heart',
                'body' => <<<MD
## Pourquoi

La distraction est rarement le vrai problème : c'est une **stratégie d'évitement** d'un état désagréable. On ne scrolle pas parce qu'on aime scroller, mais pour échapper à l'ennui, au doute, à la fatigue, à l'angoisse de mal faire. Tant qu'on ne voit pas l'émotion en dessous, on lutte contre le symptôme. Dès qu'on l'identifie, on peut répondre au vrai besoin.

Nommer une émotion réduit son intensité — un effet bien documenté en neurosciences affectives (« affect labeling », Lieberman).

## Comment

1. Au moment de fuir vers une distraction, marque une pause d'une seconde.
2. Demande-toi : « Qu'est-ce que je ressens, là, maintenant ? »
3. Mets un mot dessus : ennui, frustration, peur, fatigue, agitation.
4. Réponds au vrai besoin si tu peux (pause si fatigue, découpage si la tâche fait peur) plutôt que de scroller.
MD,
            ],
            [
                'day' => 43,
                'theme' => 'Dompter la distraction',
                'title' => "Reformuler l'ennui",
                'summary' => "L'ennui n'est pas un signal d'alarme. C'est juste une sensation, et elle passe.",
                'micro_challenge' => "Aujourd'hui, autorise-toi un court moment d'ennui volontaire : 3 minutes sans rien faire, sans téléphone, à attendre. Observe que l'ennui est inconfortable mais inoffensif. Tu apprends à ne plus le fuir.",
                'duration_min' => 6,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Nous sommes devenus intolérants à l'ennui : à la moindre seconde de vide (file d'attente, ascenseur, micro-pause), la main cherche le téléphone. Or cette intolérance est précisément ce qui ruine la concentration : dès qu'une tâche devient un peu ennuyeuse, on fuit. Réapprendre à supporter l'ennui — même à l'accueillir — restaure la capacité à rester avec une tâche qui n'est pas constamment stimulante.

L'ennui est aussi le terreau de la pensée diffuse et de la créativité (cf. J3).

## Comment

1. Offre-toi de petits moments d'ennui volontaire dans la journée : attendre sans écran.
2. Observe la sensation sans la fuir : où la sens-tu ? Est-elle vraiment insupportable ?
3. Laisse l'esprit vagabonder plutôt que de le remplir.
4. À force, l'ennui perd son pouvoir de te faire décrocher.
MD,
            ],
            [
                'day' => 44,
                'theme' => 'Dompter la distraction',
                'title' => "Comprendre la boucle du scroll",
                'summary' => "Les applications sont conçues pour capter ton attention. Connais le mécanisme.",
                'micro_challenge' => "Observe-toi en train de scroller aujourd'hui (sans culpabilité). Repère le déclencheur (quel inconfort ?), l'action (scroll), la récompense (nouveauté). Voir la boucle, c'est déjà commencer à en sortir.",
                'duration_min' => 8,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

Le scroll infini, les notifications, les « j'aime » exploitent un ressort ancien du cerveau : la **récompense variable** (parfois c'est intéressant, parfois non), le mécanisme le plus addictif qui soit — celui des machines à sous. La dopamine ne signale pas le plaisir mais l'**anticipation** : c'est l'attente de la prochaine nouveauté qui te tient, plus que la nouveauté elle-même. Comprendre que ta distraction est *ingénierie*, pas faiblesse personnelle, change le rapport de force.

Tu n'es pas faible : tu fais face à des produits optimisés par des milliers d'ingénieurs pour capter ton attention.

## Comment

1. Observe une session de scroll comme un anthropologue, sans te juger.
2. Identifie la boucle : **déclencheur** (un inconfort, un creux) → **action** (ouvrir, scroller) → **récompense** (nouveauté imprévisible).
3. Repère surtout le déclencheur : c'est le maillon sur lequel tu as le plus de prise.
4. Demain, tu agiras sur la boucle. Aujourd'hui, il suffit de la voir clairement.
MD,
            ],
            [
                'day' => 45,
                'theme' => 'Dompter la distraction',
                'title' => "Une matinée à basse stimulation",
                'summary' => "Commencer la journée sans shoot de nouveauté réhabitue le cerveau au calme.",
                'micro_challenge' => "Demain matin (ou ce matin), tiens la première heure sans téléphone, réseaux ni actualités. Commence par quelque chose de calme. Observe comme ton attention est plus stable ensuite.",
                'duration_min' => 10,
                'icon' => 'sun',
                'body' => <<<MD
## Pourquoi

Quand on commence la journée par une avalanche de stimulations rapides (notifications, fil d'actu, vidéos courtes), on règle d'emblée le cerveau sur un rythme de nouveauté permanente — après quoi une tâche lente et profonde paraît insupportablement terne. À l'inverse, préserver une matinée à **basse stimulation** maintient le système attentionnel dans un régime calme, où la concentration est plus facile à tenir le reste de la journée.

Ce n'est pas une détox spectaculaire : juste ne pas saturer le réservoir de dopamine avant même d'avoir commencé.

## Comment

1. Choisis une durée de protection le matin : 30 minutes, 1 heure.
2. Pendant ce temps, pas de téléphone, pas de réseaux, pas d'actualités.
3. Remplis-le de calme : eau, marche, respiration, lecture sur papier, ou directement ta tâche importante.
4. Compare ta stabilité d'attention les jours où tu protèges la matinée et les jours où tu la sautes.
MD,
            ],
            [
                'day' => 46,
                'theme' => 'Dompter la distraction',
                'title' => "La règle des dix minutes",
                'summary' => "Tu n'as pas à dire non à l'impulsion. Juste : pas tout de suite.",
                'micro_challenge' => "À la prochaine envie de céder à une distraction, dis-toi : « Dans dix minutes, si je veux toujours. » Continue ta tâche. Le plus souvent, l'envie sera passée — et tu auras gagné du temps de concentration.",
                'duration_min' => 6,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

Résister frontalement à une envie est épuisant et souvent perdant. La **règle des dix minutes** contourne le combat : tu ne t'interdis rien, tu *diffères*. « Pas non, mais pas maintenant. » Comme l'impulsion est une vague qui retombe (cf. J31), dix minutes suffisent presque toujours pour qu'elle disparaisse d'elle-même. Et si l'envie revient vraiment après dix minutes, tu peux choisir en conscience plutôt que par réflexe.

Différer transforme une réaction automatique en décision.

## Comment

1. Quand l'envie de te distraire surgit, ne dis pas « non » : dis « dans dix minutes ».
2. Reviens à ta tâche et laisse le minuteur mental tourner.
3. Après dix minutes, observe : le plus souvent, l'envie a fondu.
4. Si elle persiste vraiment, accorde-toi une pause choisie — pas une fuite subie.
MD,
            ],
            [
                'day' => 47,
                'theme' => 'Dompter la distraction',
                'title' => "Friction d'un côté, fluidité de l'autre",
                'summary' => "Rends la distraction pénible et la concentration facile. L'environnement décide à ta place.",
                'micro_challenge' => "Ajoute une friction à ta distraction n°1 (déconnexion, appli supprimée de l'écran d'accueil, appareil dans une autre pièce) ET enlève une friction à ta tâche (tout prêt, document ouvert). Fais les deux aujourd'hui.",
                'duration_min' => 10,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

La volonté est une ressource peu fiable ; l'environnement, lui, agit en continu, sans effort. Le principe est double : **augmenter la friction** vers la distraction (la rendre plus pénible à atteindre) et **réduire la friction** vers la tâche (la rendre plus facile à commencer). Quelques secondes d'obstacle suffisent à désamorcer un automatisme ; quelques secondes gagnées suffisent à enclencher l'action.

Tu ne te bats plus contre toi-même à chaque instant : tu as réglé le décor une fois pour qu'il pousse dans le bon sens.

## Comment

1. Pour ta distraction principale, ajoute des obstacles : déconnexion, suppression du raccourci, appareil éloigné, bloqueur de sites.
2. Pour ta tâche importante, supprime les obstacles : prépare tout, garde le document ouvert et visible.
3. Vise l'asymétrie : la distraction à plusieurs gestes, la tâche à zéro geste.
4. Réajuste dès qu'une distraction redevient trop facile d'accès.
MD,
            ],
            [
                'day' => 48,
                'theme' => 'Dompter la distraction',
                'title' => "Le parking à pensées",
                'summary' => "Les inquiétudes récurrentes ont besoin d'un lieu et d'un horaire. Donne-leur les deux.",
                'micro_challenge' => "Crée un « parking » : une page où tu gares les pensées qui reviennent (inquiétudes, idées, choses à régler). Quand l'une surgit pendant une session, gare-la et fixe-toi un moment précis pour t'en occuper.",
                'duration_min' => 8,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

Certaines pensées ne se contentent pas d'être notées : elles reviennent, parce qu'elles portent un enjeu réel (une décision, une inquiétude). Leur dire « pas maintenant » ne suffit pas si on ne leur donne jamais de « maintenant ». Le **parking à pensées**, couplé à un rendez-vous dédié (par exemple un « temps des soucis » ou une revue quotidienne), rassure l'esprit : il sait que la chose sera traitée, donc il accepte de la lâcher pendant la session.

C'est la différence entre étouffer une pensée (elle revient plus fort) et la programmer (elle se calme).

## Comment

1. Ouvre une page unique dédiée aux pensées récurrentes : le « parking ».
2. Quand l'une surgit pendant que tu te concentres, gare-la là en une ligne, puis reviens.
3. Fixe un rendez-vous régulier (10 minutes en fin de journée) pour relire le parking et décider.
4. Honore ce rendez-vous : c'est lui qui rend crédible le « plus tard » et apaise l'esprit.
MD,
            ],

            // ===================================================================
            // BLOC 7 — LES PROFONDEURS (J49-56)
            // ===================================================================
            [
                'day' => 49,
                'theme' => 'Les profondeurs',
                'title' => "Réserve tes blocs profonds",
                'summary' => "Le travail profond n'arrive pas dans les trous de l'agenda. Il se planifie.",
                'micro_challenge' => "Ouvre ton agenda et bloque concrètement, pour demain, un créneau de travail profond (60-90 min) comme un rendez-vous avec toi-même. Donne-lui un nom et défends-le.",
                'duration_min' => 10,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

Le travail profond — la concentration sans distraction sur une tâche exigeante — ne se produit presque jamais « quand on aura le temps », parce qu'on n'a jamais le temps : les trous se remplissent de réactif. La seule parade fiable, montre Cal Newport, est le **time-blocking** : inscrire le travail profond dans l'agenda comme un rendez-vous ferme, à l'avance, et le traiter avec le même sérieux qu'une réunion importante.

Ce qui n'est pas planifié n'a pas lieu. Ce qui est planifié et protégé, oui.

## Comment

1. Repère, pour demain, une fenêtre d'au moins 60-90 minutes dans ta meilleure plage d'attention.
2. Bloque-la dans ton agenda avec un titre clair (« Profond : [tâche] »).
3. Traite ce bloc comme un rendez-vous non négociable : tu ne le déplaces pas pour du réactif.
4. Préviens, si besoin, ton entourage que tu es indisponible sur ce créneau.
MD,
            ],
            [
                'day' => 50,
                'theme' => 'Les profondeurs',
                'title' => "Ton rituel de travail profond",
                'summary' => "Un lieu, une durée, des règles : un cadre fixe libère l'attention maximale.",
                'micro_challenge' => "Définis ton rituel de deep work : OÙ (le lieu), COMBIEN DE TEMPS (la durée), et 3 RÈGLES (ex. : pas de téléphone, un seul onglet, pas de mails). Écris-le et applique-le à ton prochain bloc.",
                'duration_min' => 10,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Improviser chaque session coûte de l'énergie décisionnelle (cf. J7). Un **rituel de travail profond** stable — toujours le même lieu, la même durée cible, les mêmes règles — supprime ces micro-décisions et signale au cerveau qu'on entre en mode intense. Plus le cadre est défini et répété, moins il faut de volonté pour s'y mettre : le rituel porte l'effort à ta place.

C'est l'aboutissement du rituel de démarrage (J16), poussé jusqu'au travail le plus exigeant.

## Comment

1. Fixe le **lieu** où tu feras ton travail profond (idéalement ton sanctuaire du J9).
2. Fixe la **durée** cible d'un bloc (60, 75, 90 minutes).
3. Fixe **3 règles** simples et non négociables (téléphone hors de portée, un seul onglet, aucun mail).
4. Note ce rituel et applique-le tel quel à chaque bloc, sans renégocier à chaque fois.
MD,
            ],
            [
                'day' => 51,
                'theme' => 'Les profondeurs',
                'title' => "Le rythme quotidien",
                'summary' => "La régularité bat l'intensité. Un peu de profond chaque jour vaut mieux que beaucoup une fois.",
                'micro_challenge' => "Engage-toi sur un bloc profond à heure fixe, chaque jour ouvré. Aujourd'hui, fais-le à l'heure choisie. La constance crée l'habitude ; l'habitude rend la concentration automatique.",
                'duration_min' => 10,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

La concentration profonde se renforce comme une habitude : par la **répétition régulière**, pas par les coups d'éclat. Un bloc profond chaque jour, à heure à peu près fixe, finit par devenir un automatisme — le cerveau « sait » qu'à cette heure-là, on travaille en profondeur, et y entre sans résistance. À l'inverse, des sessions intenses mais erratiques ne créent jamais cette habitude et restent coûteuses à chaque fois.

C'est le rythme « du moine » : un créneau sacré, tous les jours, qui finit par couler de source.

## Comment

1. Choisis une heure réaliste, tenable la plupart des jours ouvrés.
2. Place-y ton bloc profond quotidien, même court les jours chargés.
3. Vise la régularité avant la durée : mieux vaut 45 minutes tous les jours que 3 heures une fois par semaine.
4. Protège la chaîne : ne pas casser la série compte plus que la performance d'un jour donné.
MD,
            ],
            [
                'day' => 52,
                'theme' => 'Les profondeurs',
                'title' => "Protège ton attention des autres",
                'summary' => "Apprendre à se concentrer, c'est aussi apprendre à dire « pas maintenant ».",
                'micro_challenge' => "Pour ton prochain bloc profond, rends-toi explicitement indisponible : statut occupé, porte fermée, message d'absence. Préviens avant plutôt que de t'excuser après. Défends ton créneau une fois aujourd'hui.",
                'duration_min' => 8,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Toutes les distractions ne viennent pas des écrans : les sollicitations humaines (« t'as deux minutes ? », messages, réunions impromptues) fragmentent au moins autant l'attention. Or beaucoup de gens protègent leur environnement numérique mais n'osent pas protéger leur temps face aux autres, par peur de mal faire. Poser des limites claires et prévenues à l'avance n'est pas de l'égoïsme : c'est la condition pour produire un travail de qualité — qui sert aussi les autres.

Indisponible une heure, pleinement disponible ensuite, vaut mieux que disponible en continu mais jamais vraiment là.

## Comment

1. Avant un bloc profond, signale ton indisponibilité : statut, casque visible, porte, message clair.
2. Annonce-le **à l'avance** et avec une échéance (« je suis concentré jusqu'à 11 h, je reviens vers toi après »).
3. Tiens la limite : si on t'interrompt pour du non-urgent, propose un autre moment.
4. Compense par une vraie disponibilité hors de tes blocs, pour que les limites soient acceptées.
MD,
            ],
            [
                'day' => 53,
                'theme' => 'Les profondeurs',
                'title' => "La solitude productive",
                'summary' => "Pour penser en profondeur, il faut parfois se couper de tout intrant extérieur.",
                'micro_challenge' => "Aujourd'hui, accorde-toi 20 minutes seul avec ta pensée et un seul sujet — sans entrée extérieure (ni écran, ni musique, ni livre). Juste réfléchir, ou écrire à la main. Laisse ton esprit travailler le problème.",
                'duration_min' => 12,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

La pensée profonde a besoin de moments **sans intrant** : ni notification, ni podcast, ni musique, ni même lecture. Newport parle de « privation de solitude » pour décrire l'état moderne où l'on n'est jamais seul avec ses pensées plus de quelques minutes. Or c'est dans ces espaces que se font les vraies connexions, les décisions mûries, les idées originales. S'entraîner à rester seul avec un problème, sans rien pour combler le vide, est une forme avancée de concentration.

## Comment

1. Choisis un sujet ou un problème qui mérite réflexion.
2. Isole-toi 15-20 minutes sans aucune entrée extérieure : pas d'écran, pas de son, pas de lecture.
3. Réfléchis activement, ou écris à la main pour suivre ta pensée.
4. Accepte les premières minutes d'inconfort : c'est le sevrage de la stimulation. La pensée vient après.
MD,
            ],
            [
                'day' => 54,
                'theme' => 'Les profondeurs',
                'title' => "La lecture profonde",
                'summary' => "Lire lentement et activement réentraîne une attention que le survol numérique a érodée.",
                'micro_challenge' => "Lis un texte exigeant pendant 20 minutes, sur papier si possible, stylo en main. Souligne, annote, reformule. Quand tu décroches, reviens à la dernière phrase comprise. Lecture lente, attention profonde.",
                'duration_min' => 20,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

À force de survoler des écrans — diagonale, zapping d'un lien à l'autre — beaucoup ont perdu l'habitude de la **lecture profonde** : suivre une pensée longue, complexe, sans décrocher. Or cette lecture est un entraînement attentionnel exceptionnel : elle exige de tenir le fil sur la durée, de relire, de relier. La restaurer, c'est muscler directement la concentration tout en nourrissant la pensée.

Le papier aide : moins de distractions, un rapport plus lent et plus engagé au texte.

## Comment

1. Choisis un texte qui demande un effort (pas un fil d'actualité) : un chapitre, un article de fond.
2. Lis sur papier si tu peux, stylo en main.
3. Lis lentement, souligne, annote en marge, reformule les idées avec tes mots.
4. Quand l'attention décroche, reviens à la dernière phrase vraiment comprise plutôt que de continuer en surface.
MD,
            ],
            [
                'day' => 55,
                'theme' => 'Les profondeurs',
                'title' => "Créer sans s'interrompre",
                'summary' => "Produire et corriger en même temps fragmente l'attention. Sépare les deux temps.",
                'micro_challenge' => "Pour une tâche de création (écrire, concevoir, coder), fais un premier jet SANS rien corriger ni revérifier : avance jusqu'au bout, même imparfait. La correction viendra dans un second temps, séparé.",
                'duration_min' => 15,
                'icon' => 'rocket',
                'body' => <<<MD
## Pourquoi

Créer et corriger sollicitent deux modes opposés : l'un produit et avance, l'autre juge et revient en arrière. Les mêler — écrire une phrase puis la réécrire trois fois avant de continuer — fragmente l'attention, casse l'élan et déclenche le perfectionnisme paralysant. Séparer les deux temps (d'abord produire d'un trait, ensuite corriger) protège le flux créatif et permet une concentration bien plus profonde sur chaque phase.

C'est la règle du premier jet : avancer d'abord, parfaire ensuite.

## Comment

1. Pour la phase de création, fixe une règle : **interdiction de corriger** tant que le premier jet n'est pas fini.
2. Avance jusqu'au bout, en acceptant l'imparfait et les trous (note-les d'un signe et continue).
3. Fais ensuite une pause, puis ouvre une phase distincte de révision, avec l'autre état d'esprit.
4. Ne repasse jamais en mode correction au milieu de la création : c'est ce mélange qui te fragmente.
MD,
            ],
            [
                'day' => 56,
                'theme' => 'Les profondeurs',
                'title' => "Mesure ta profondeur",
                'summary' => "Ce qui se mesure s'améliore. Compte tes vraies heures de concentration.",
                'micro_challenge' => "À partir d'aujourd'hui, comptabilise tes heures de travail vraiment profond (concentré, sans distraction). Note le total du jour. Le simple fait de compter te poussera à en faire un peu plus.",
                'duration_min' => 8,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

On surestime le temps qu'on passe réellement concentré : entre les bascules, les coups d'œil au téléphone et les demi-attentions, une « journée de travail » contient souvent peu d'heures vraiment profondes. Tenir un compteur honnête de ses heures de concentration profonde a deux effets : il révèle la réalité (souvent modeste au début), et il crée une saine émulation — on cherche naturellement à faire grimper le chiffre.

Ce que tu mesures, tu l'améliores. Un simple décompte vaut mieux qu'un grand système.

## Comment

1. Définis ce qui compte comme « profond » : concentré, sur une tâche exigeante, sans distraction.
2. Chaque jour, note le total d'heures (ou de blocs) de travail réellement profond.
3. Ne triche pas : seule l'attention pleine compte, pas le temps passé assis.
4. Vise une progression douce et durable du total hebdomadaire, pas un record ponctuel.
MD,
            ],

            // ===================================================================
            // BLOC 8 — DURER (J57-60)
            // ===================================================================
            [
                'day' => 57,
                'theme' => 'Durer',
                'title' => "Le repos qui restaure l'attention",
                'summary' => "La nature et le calme rechargent l'attention mieux que n'importe quel écran.",
                'micro_challenge' => "Aujourd'hui, accorde-toi 15-20 minutes dehors, dans un cadre naturel si possible (parc, arbres, ciel), sans téléphone. Laisse simplement ton regard et ton esprit se poser. C'est de la recharge, pas du temps perdu.",
                'duration_min' => 15,
                'icon' => 'sun',
                'body' => <<<MD
## Pourquoi

L'attention dirigée — celle qu'on mobilise pour se concentrer — se fatigue et a besoin d'être restaurée. La théorie de la restauration de l'attention (Kaplan) montre que les environnements naturels la rechargent particulièrement bien : la nature capte l'attention en douceur, sans l'épuiser, ce qui laisse au système de concentration le temps de récupérer. Une marche dans un parc améliore mesurablement la concentration ensuite — bien plus qu'une pause en ville ou sur écran.

Se reposer n'est pas l'opposé de se concentrer : c'est ce qui le rend possible jour après jour.

## Comment

1. Sors dans un cadre aussi naturel que possible : parc, jardin, bord d'eau, ou simplement le ciel et des arbres.
2. Laisse le téléphone de côté.
3. Marche tranquillement ou assieds-toi, et laisse ton attention se poser sans effort sur ce qui t'entoure.
4. Intègre ce type de pause régulièrement, surtout les jours de forte charge mentale.
MD,
            ],
            [
                'day' => 58,
                'theme' => 'Durer',
                'title' => "Le sommeil, socle de l'attention",
                'summary' => "Aucune technique ne compense un cerveau privé de sommeil. Protège tes nuits.",
                'micro_challenge' => "Ce soir, fixe une heure de coucher et protège la dernière heure avant : pas d'écran, lumière douce, quelque chose de calme. Une bonne nuit est ton premier exercice de concentration de demain.",
                'duration_min' => 10,
                'icon' => 'heart',
                'body' => <<<MD
## Pourquoi

Le manque de sommeil détruit l'attention : après une nuit trop courte, la capacité de concentration, la mémoire de travail et le contrôle des impulsions chutent fortement — et l'on ne s'en rend même pas compte, car le sommeil insuffisant altère aussi le jugement sur soi. Aucune technique de ce parcours ne peut compenser un cerveau chroniquement privé de repos. Le sommeil est le socle, pas l'accessoire.

Soigner ses nuits est sans doute le levier d'attention le plus puissant — et le plus négligé.

## Comment

1. Fixe une heure de coucher régulière et tiens-la, même le week-end autant que possible.
2. Protège la dernière heure : pas d'écran (la lumière bleue retarde l'endormissement), lumière douce, activité calme.
3. Garde la chambre fraîche, sombre et sans téléphone à portée.
4. Traite ton sommeil comme une partie de ton entraînement à la concentration, pas comme une variable d'ajustement.
MD,
            ],
            [
                'day' => 59,
                'theme' => 'Durer',
                'title' => "Le bilan de ton attention",
                'summary' => "Une revue régulière transforme l'effort en progrès durable.",
                'micro_challenge' => "Fais le point en 10 minutes : qu'est-ce qui a le plus aidé ta concentration ces 8 dernières semaines ? Qu'est-ce qui te distrait encore le plus ? Choisis UNE chose à garder et UNE à améliorer la semaine prochaine.",
                'duration_min' => 10,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Sans regard en arrière, on répète les mêmes schémas sans le voir. Une revue régulière — hebdomadaire, idéalement — permet de repérer ce qui marche pour toi (et de le renforcer) et ce qui te piège encore (et de l'ajuster). C'est la boucle d'apprentissage qui transforme une série d'exercices en une compétence stable. Ta concentration n'est pas figée : elle se pilote, à condition de l'observer.

Tu as parcouru huit semaines. Il est temps d'en tirer **ta** synthèse, pas une recette générale.

## Comment

1. Reprends ton objectif du J8 et ta ligne de base du J1 : où en es-tu ?
2. Liste ce qui a le plus aidé (environnement ? entraînement ? rituels ? limites ?).
3. Liste ce qui te distrait encore le plus aujourd'hui.
4. Choisis une force à consolider et un point à travailler pour la semaine qui vient. Refais ce bilan chaque semaine.
MD,
            ],
            [
                'day' => 60,
                'theme' => 'Durer',
                'title' => "Ton protocole personnel",
                'summary' => "Soixante jours d'exercices se condensent en quelques règles qui sont les tiennes.",
                'micro_challenge' => "Écris ton protocole de concentration en une demi-page : tes 3-5 pratiques essentielles (celles qui marchent vraiment pour toi). Affiche-le. C'est ton mode d'emploi pour les mois à venir.",
                'duration_min' => 15,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

La concentration n'est pas un état qu'on atteint une fois pour toutes : c'est une pratique qui se cultive. Au terme de ces soixante jours, tu n'as pas besoin de tout garder — tu as besoin de **ton** noyau : les trois à cinq pratiques qui, pour toi, font la plus grande différence. Les condenser en un protocole clair et visible te donne un mode d'emploi durable, à rouvrir dès que l'attention se dérègle.

Le parcours se termine ; ta pratique, elle, commence vraiment. Reviens dans le Sanctuaire quand tu veux : les exercices restent là.

## Comment

1. Relis tes notes et tes ressentis : quels exercices t'ont le plus aidé ?
2. Sélectionne 3 à 5 pratiques essentielles — pas plus, pour que ce soit tenable.
3. Écris-les en un protocole court et concret (un lieu, un rituel, des règles, une cadence).
4. Affiche-le là où tu travailles, et reviens-y dès que tu sens ta concentration se déliter. Tu sais désormais comment la reconstruire.
MD,
            ],
        ];
    }
}
