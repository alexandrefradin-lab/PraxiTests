<?php

namespace Praxis\Plugins\PraxiLead\Data;

/**
 * Catalogue des 60 pratiques de management de proximité.
 *
 * Public : manager d'équipe directe (généraliste). Une pratique par jour,
 * concrète, applicable en moins de 15 minutes, suivie d'un micro-défi à
 * appliquer le jour même.
 *
 * Parti pris : des pratiques étayées par la recherche en management et en
 * psychologie du travail (sécurité psychologique — Edmondson ; feedback &
 * objectifs — Locke & Latham ; motivation autodéterminée — Deci & Ryan ;
 * reconnaissance ; entretiens 1:1 ; délégation situationnelle — Hersey &
 * Blanchard ; conversations difficiles — Stone, Patterson). Chaque pratique
 * privilégie le comportement observable plutôt que la théorie.
 *
 * Les 60 jours sont regroupés en 8 blocs thématiques progressifs :
 *   J1-8   Poser le cadre
 *   J9-16  Écouter & l'entretien individuel
 *   J17-24 Le feedback
 *   J25-32 Déléguer & responsabiliser
 *   J33-40 Motiver & reconnaître
 *   J41-48 Développer & faire grandir
 *   J49-56 Tensions, conflits & décisions
 *   J57-60 Sens, vision & durer
 */
class Practices
{
    public static function all(): array
    {
        return [
            // ===================================================================
            // BLOC 1 — POSER LE CADRE (J1-8)
            // ===================================================================
            [
                'day' => 1,
                'theme' => 'Poser le cadre',
                'title' => "Clarifie ce que « bien faire » veut dire",
                'summary' => "La première cause de démotivation, c'est le flou sur ce qui est attendu.",
                'micro_challenge' => "Choisis un·e membre de l'équipe et écris en une phrase ce que tu attends concrètement de son rôle cette semaine. Vérifie avec elle/lui que vous êtes d'accord.",
                'duration_min' => 10,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

On croit souvent que les attentes sont « évidentes ». Elles ne le sont presque jamais. Le flou sur ce qui est attendu est l'une des premières sources de stress et de désengagement au travail (études Gallup sur l'engagement : « savoir ce qu'on attend de moi » est le tout premier besoin des salariés).

Un attendu clair, c'est un comportement ou un résultat **observable** : pas « sois plus rigoureux » mais « les comptes-rendus sont envoyés le vendredi avant midi ».

## Comment

1. Pour une personne, distingue **la mission** (à quoi sert son poste) et **les attentes de la semaine** (1 à 3 résultats concrets).
2. Formule-les de façon observable : qui, quoi, pour quand.
3. Demande-lui de reformuler avec ses mots. L'écart entre ce que tu as dit et ce qu'elle a compris est ta vraie zone de travail.
MD,
            ],
            [
                'day' => 2,
                'theme' => 'Poser le cadre',
                'title' => "Écris les règles du jeu de l'équipe",
                'summary' => "Une équipe sans règles explicites en invente d'implicites — souvent injustes.",
                'micro_challenge' => "Note 3 règles de fonctionnement non écrites de ton équipe (réunions, délais, disponibilité). Demande-toi : sont-elles claires pour tout le monde, ou seulement dans ta tête ?",
                'duration_min' => 10,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

Toute équipe a des règles. La question n'est pas *s'il y en a*, mais *si elles sont explicites*. Quand elles restent implicites, chacun applique les siennes, et les malentendus se transforment en tensions (« il répond aux mails le week-end, donc je dois aussi ? »).

## Comment

1. Liste les zones qui créent des frictions : horaires, délais de réponse, prise de parole en réunion, droit à l'erreur, urgences.
2. Pour chacune, écris **une règle simple** que tu peux tenir et faire tenir.
3. Tu n'as pas besoin de tout fixer seul : les meilleures règles d'équipe se co-construisent. Mais c'est à toi de garantir qu'elles existent.
MD,
            ],
            [
                'day' => 3,
                'theme' => 'Poser le cadre',
                'title' => "Donne le « pourquoi » avant le « quoi »",
                'summary' => "Une consigne sans raison est exécutée a minima ; avec sa raison, elle est portée.",
                'micro_challenge' => "Pour la prochaine demande que tu fais aujourd'hui, ajoute une phrase : « C'est important parce que… ». Observe la différence dans l'engagement.",
                'duration_min' => 5,
                'icon' => 'lightbulb',
                'body' => <<<MD
## Pourquoi

Le cerveau humain coopère mieux quand il comprend le sens d'une action. Une consigne sans raison déclenche l'exécution minimale ; une consigne avec sa raison déclenche l'**intelligence d'adaptation** : la personne peut ajuster si le contexte change, parce qu'elle a compris l'intention.

## Comment

1. Avant de demander une tâche, formule-toi à toi-même : *à quoi ça sert, pour qui, quel problème ça résout ?*
2. Dis-le en une phrase. « Je te demande X parce que le client Y attend Z. »
3. Méfie-toi du « parce que je te le demande » : c'est un aveu que tu n'as pas pris le temps de comprendre toi-même le pourquoi.
MD,
            ],
            [
                'day' => 4,
                'theme' => 'Poser le cadre',
                'title' => "Sois prévisible avant d'être charismatique",
                'summary' => "La confiance ne naît pas de grands gestes, mais de la constance des petits.",
                'micro_challenge' => "Identifie une chose sur laquelle ton équipe ne peut pas encore compter à 100 % (tes horaires de dispo, tes réponses, ton humeur). Choisis-en une et rends-la fiable cette semaine.",
                'duration_min' => 10,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

On imagine le bon manager comme une figure inspirante. En réalité, ce qui sécurise une équipe au quotidien, c'est la **prévisibilité** : savoir comment tu vas réagir, quand tu es disponible, ce qui te met en alerte. Un manager imprévisible oblige chacun à dépenser de l'énergie à « deviner » son état — au détriment du travail.

## Comment

1. Repère tes incohérences : un jour tu valides, le lendemain tu reproches la même chose.
2. Choisis quelques principes que tu tiendras quoi qu'il arrive (ex. « je préviens toujours si un délai change »).
3. La constance émotionnelle compte autant : ton équipe lit ton humeur comme une météo. Annonce-la plutôt que de la faire subir.
MD,
            ],
            [
                'day' => 5,
                'theme' => 'Poser le cadre',
                'title' => "Distingue l'urgent, l'important et le bruit",
                'summary' => "Si tout est prioritaire, rien ne l'est — et ton équipe s'épuise sur le bruit.",
                'micro_challenge' => "Liste les 5 sollicitations que tu vas transmettre à l'équipe aujourd'hui. Classe-les : vraie priorité, peut attendre, à filtrer. Ne transmets que les vraies.",
                'duration_min' => 10,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

Le manager est un **filtre**, pas un tuyau. Une grande partie des sollicitations qui te parviennent ne devraient jamais atteindre l'équipe telles quelles : elles sont urgentes pour quelqu'un d'autre, pas importantes pour ta mission. Transmettre sans filtrer, c'est faire porter à ton équipe ton propre stress.

## Comment

1. Pour chaque demande entrante, pose-toi : *est-ce important (ça sert nos objectifs) ou seulement urgent (ça presse quelqu'un) ?*
2. Protège l'équipe du bruit : regroupe, reporte, ou absorbe les sollicitations de faible valeur.
3. Quand tu transmets une priorité, dis explicitement ce qui passe **après**. Une priorité sans arbitrage n'est qu'un vœu.
MD,
            ],
            [
                'day' => 6,
                'theme' => 'Poser le cadre',
                'title' => "Crée la sécurité psychologique",
                'summary' => "Les meilleures équipes ne sont pas les plus douées, mais celles où l'on ose parler.",
                'micro_challenge' => "Aujourd'hui, admets une de tes erreurs devant l'équipe, simplement. C'est le signal le plus fort que l'erreur est discutable ici.",
                'duration_min' => 10,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Le projet Aristote de Google a cherché ce qui distingue les équipes performantes. Le premier facteur n'est ni le talent ni les moyens : c'est la **sécurité psychologique** (Amy Edmondson) — la conviction qu'on peut prendre un risque interpersonnel (poser une question, signaler une erreur, exprimer un doute) sans être humilié.

## Comment

1. Réagis aux erreurs par la curiosité (« qu'est-ce qui s'est passé ? ») plutôt que par le blâme. Le blâme apprend à cacher, pas à corriger.
2. Montre ta faillibilité : un manager qui n'admet jamais d'erreur installe une équipe qui n'en signale jamais.
3. Remercie celui qui signale un problème, même si c'est inconfortable. Tu récompenses le signal, pas le problème.
MD,
            ],
            [
                'day' => 7,
                'theme' => 'Poser le cadre',
                'title' => "Apprends à dire « je ne sais pas »",
                'summary' => "L'autorité ne vient pas de tout savoir, mais d'être fiable sur ce qu'on dit.",
                'micro_challenge' => "La prochaine fois qu'on te pose une question dont tu n'as pas la réponse, dis : « Je ne sais pas, je me renseigne et je reviens vers toi avant [date]. » Puis tiens-le.",
                'duration_min' => 5,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

Beaucoup de jeunes managers croient devoir tout savoir. C'est un piège : inventer une réponse détruit la confiance bien plus vite qu'un « je ne sais pas ». Ce qui fonde ton autorité, ce n'est pas l'omniscience, c'est la **fiabilité de ta parole**.

## Comment

1. Sépare deux choses : ne pas savoir (normal) et ne pas s'engager à trouver (problématique).
2. Formule : « Je ne sais pas + je m'engage à + une échéance. »
3. Reviens, même si la réponse est « je n'ai pas encore ». Une boucle non fermée coûte cher en crédibilité.
MD,
            ],
            [
                'day' => 8,
                'theme' => 'Poser le cadre',
                'title' => "Fais un point de départ honnête",
                'summary' => "On ne peut pas piloter ce qu'on n'a pas regardé en face.",
                'micro_challenge' => "Écris pour toi 3 forces et 2 fragilités réelles de ton équipe aujourd'hui. Sans juger : juste constater. Ce sera ta ligne de base.",
                'duration_min' => 15,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Avant d'améliorer quoi que ce soit, il faut un **point zéro lucide**. Les managers se trompent souvent dans deux directions : trop sévères (ils ne voient que ce qui ne va pas) ou trop indulgents (ils évitent les sujets qui fâchent). Un diagnostic honnête, c'est les deux à la fois.

## Comment

1. Regarde des faits, pas des impressions : délais tenus, climat, compétences clés, dépendances à une seule personne.
2. Note ce qui marche (à protéger) et ce qui est fragile (à renforcer).
3. Garde cette photo. Dans 60 jours, tu la reliras — c'est ta mesure du chemin parcouru.
MD,
            ],
            // ===================================================================
            // BLOC 2 — ÉCOUTER & L'ENTRETIEN INDIVIDUEL (J9-16)
            // ===================================================================
            [
                'day' => 9,
                'theme' => "Écouter & le 1:1",
                'title' => "Instaure le rendez-vous individuel régulier",
                'summary' => "Le 1:1 est le seul rituel qui prévient 80 % des problèmes au lieu de les subir.",
                'micro_challenge' => "Pose dès aujourd'hui un créneau récurrent (30 min) avec chaque membre de l'équipe. Hebdo ou bi-mensuel. Mets-le au calendrier maintenant.",
                'duration_min' => 10,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

L'entretien individuel régulier (1:1) est le rituel le plus rentable du management. Il transforme la relation : au lieu de découvrir les problèmes quand ils explosent, tu les vois venir. C'est **le temps de la personne**, pas le tien — donc pas un point d'avancement projet déguisé.

## Comment

1. Fréquence : toutes les 1 à 2 semaines, 30 minutes, protégé (on ne l'annule pas, on le déplace).
2. C'est elle/lui qui remplit l'ordre du jour en priorité. Tu écoutes plus que tu ne parles.
3. Trois questions universelles : *Comment ça va vraiment ? Qu'est-ce qui te freine ? De quoi as-tu besoin de moi ?*
MD,
            ],
            [
                'day' => 10,
                'theme' => "Écouter & le 1:1",
                'title' => "Écoute pour comprendre, pas pour répondre",
                'summary' => "La plupart des gens écoutent en préparant déjà leur réponse.",
                'micro_challenge' => "Dans une conversation aujourd'hui, avant de répondre, reformule ce que l'autre vient de dire : « Si je comprends bien… ». Une seule fois suffit pour sentir l'effet.",
                'duration_min' => 5,
                'icon' => 'ear',
                'body' => <<<MD
## Pourquoi

L'écoute active n'est pas une posture gentille : c'est un outil de précision. Tant que tu n'as pas vérifié que tu as compris, tu pilotes à l'aveugle. Reformuler ralentit l'échange de quelques secondes et évite des heures de malentendu.

## Comment

1. Quand quelqu'un t'explique un problème, résiste à l'envie de résoudre immédiatement.
2. Reformule : « Donc ce qui te bloque, c'est… c'est ça ? » Laisse corriger.
3. Pose une question de plus avant de conclure. La première version d'un problème est rarement la vraie.
MD,
            ],
            [
                'day' => 11,
                'theme' => "Écouter & le 1:1",
                'title' => "Pose des questions ouvertes",
                'summary' => "Une bonne question fait réfléchir l'autre ; une mauvaise lui fait deviner ta réponse.",
                'micro_challenge' => "Bannis aujourd'hui le « Tu as compris ? » (qui n'appelle que « oui »). Remplace-le par « Qu'est-ce que tu vas faire en premier ? ».",
                'duration_min' => 5,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

Les questions fermées (oui/non) confirment ce que tu crois déjà savoir. Les questions ouvertes (qui, quoi, comment, qu'est-ce qui) ouvrent l'information réelle. « Tu as compris ? » obtient un « oui » réflexe ; « Qu'est-ce que tu vas faire ? » révèle ce qui a vraiment été compris.

## Comment

1. Commence tes questions par *quoi / comment / qu'est-ce qui*. Évite *pourquoi* qui met sur la défensive.
2. Pose une question, puis tais-toi. Le silence fait souvent émerger l'essentiel.
3. Une question à la fois : empiler les questions, c'est reprendre la parole sans laisser répondre.
MD,
            ],
            [
                'day' => 12,
                'theme' => "Écouter & le 1:1",
                'title' => "Accueille le silence",
                'summary' => "Combler chaque silence, c'est priver l'autre de l'espace pour penser.",
                'micro_challenge' => "Après ta prochaine question importante, compte mentalement jusqu'à 5 avant de reprendre la parole. Observe ce qui émerge.",
                'duration_min' => 5,
                'icon' => 'ear',
                'body' => <<<MD
## Pourquoi

Le silence est inconfortable pour le manager, qui se sent responsable de « faire avancer ». Mais 3 à 5 secondes de silence après une question donnent à l'autre le temps de formuler une vraie réponse, au lieu d'un réflexe. Les meilleures idées arrivent souvent après le premier silence.

## Comment

1. Pose ta question, puis laisse délibérément un blanc.
2. Ne réponds pas à ta propre question. Si le silence dure, dis simplement : « Prends ton temps. »
3. En réunion, le silence après « Des questions ? » dure rarement assez. Compte jusqu'à 7.
MD,
            ],
            [
                'day' => 13,
                'theme' => "Écouter & le 1:1",
                'title' => "Repère l'état réel, pas l'état déclaré",
                'summary' => "« Ça va » est la réponse par défaut, pas une information.",
                'micro_challenge' => "Avec une personne aujourd'hui, va au-delà du premier « ça va » : « Ça va vraiment, ou ça va “ça va” ? ». Avec le sourire.",
                'duration_min' => 5,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

« Ça va ? » — « Ça va. » Cet échange n'apporte aucune information : c'est un rituel social. Or une grande partie du travail de manager consiste à percevoir les signaux faibles **avant** qu'ils deviennent des départs, des arrêts ou des conflits.

## Comment

1. Observe les écarts à la normale : quelqu'un d'habituellement bavard qui se tait, un travail soigné qui se relâche.
2. Nomme l'observation sans interpréter : « J'ai remarqué que tu es plus en retrait cette semaine, je me trompe ? »
3. Si la personne ne veut pas parler, respecte-le, mais laisse la porte ouverte : « Quand tu veux, je suis là. »
MD,
            ],
            [
                'day' => 14,
                'theme' => "Écouter & le 1:1",
                'title' => "Ne vole pas le problème",
                'summary' => "Reprendre chaque problème, c'est former une équipe qui ne sait plus en résoudre.",
                'micro_challenge' => "Quand quelqu'un t'apporte un problème aujourd'hui, réponds par : « Toi, tu ferais quoi ? » avant de donner ta solution.",
                'duration_min' => 5,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Quand un collaborateur arrive avec un problème, le réflexe du manager est de le résoudre. Sauf que chaque problème que tu reprends est une compétence que la personne ne développe pas — et une dépendance de plus à toi. C'est le « management par singe sur l'épaule » : tu repars avec tous les singes.

## Comment

1. Réoriente : « Quelles options tu vois ? » ou « Toi, tu ferais quoi ? »
2. Si la personne a une piste correcte, valide-la plutôt que d'imposer la tienne.
3. Garde la résolution directe pour les vraies urgences ou les sujets qui te reviennent légitimement. Le reste, fais-le grandir.
MD,
            ],
            [
                'day' => 15,
                'theme' => "Écouter & le 1:1",
                'title' => "Demande du feedback sur toi",
                'summary' => "Un manager qui ne reçoit jamais de feedback gouverne dans le noir.",
                'micro_challenge' => "Demande à une personne de confiance : « Qu'est-ce que je pourrais faire pour te faciliter le travail ? » Écoute sans te justifier.",
                'duration_min' => 10,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

Plus tu montes en responsabilité, moins on te dit la vérité — par prudence. Résultat : tu peux accumuler des angles morts sans le savoir. Demander activement du feedback est le seul antidote. Et c'est aussi un puissant signal de sécurité psychologique : si le chef accepte la critique, chacun ose.

## Comment

1. Pose une question précise et facile à répondre : « Qu'est-ce que je devrais faire plus ? moins ? »
2. Accueille sans te défendre. Dis « merci », note, et reviens plus tard si besoin.
3. Agis sur **un** point. Rien ne tue le feedback comme le sentiment qu'il ne sert à rien.
MD,
            ],
            [
                'day' => 16,
                'theme' => "Écouter & le 1:1",
                'title' => "Tiens tes engagements de 1:1",
                'summary' => "Chaque promesse oubliée en entretien coûte une dose de confiance.",
                'micro_challenge' => "Reprends tes derniers entretiens : as-tu une action promise non tenue ? Ferme-la aujourd'hui, ou explique honnêtement pourquoi elle attend.",
                'duration_min' => 10,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Le 1:1 génère des micro-engagements : « je vais regarder », « je te mets en lien avec X », « je remonte ce point ». Chacun semble mineur. Mais une accumulation d'engagements oubliés envoie un message dévastateur : *ce que tu me dis ne compte pas vraiment.*

## Comment

1. Note tes engagements pendant l'entretien, pas après (tu oublieras).
2. Tiens un suivi simple : une ligne par personne, ce que tu lui dois.
3. Si tu ne peux pas tenir, dis-le explicitement et tôt. Un engagement renégocié vaut mille fois mieux qu'un engagement évaporé.
MD,
            ],
            // ===================================================================
            // BLOC 3 — LE FEEDBACK (J17-24)
            // ===================================================================
            [
                'day' => 17,
                'theme' => 'Le feedback',
                'title' => "Sépare les faits de l'interprétation",
                'summary' => "« Tu es négligent » est un jugement ; « il manque 3 chiffres » est un fait.",
                'micro_challenge' => "Avant ton prochain retour, écris d'abord le fait observable, puis ton interprétation. Ne partage que le fait, et demande à l'autre son interprétation.",
                'duration_min' => 10,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

La plupart des feedbacks ratés mélangent un **fait** (observable, indiscutable) et une **interprétation** (ce que j'en conclus). « Tu ne respectes pas les délais » est une interprétation ; « le rapport est arrivé mardi alors qu'on avait dit lundi » est un fait. On peut discuter une interprétation, pas un fait — donc partir du fait désamorce la défensive.

## Comment

1. Décris ce qu'une caméra aurait filmé : un comportement, une donnée, une date.
2. Sépare clairement : « Voici ce que j'observe [fait]. Voici ce que je crains [interprétation]. »
3. Vérifie ton interprétation auprès de l'autre. Elle est souvent fausse, et c'est tant mieux à savoir.
MD,
            ],
            [
                'day' => 18,
                'theme' => 'Le feedback',
                'title' => "Donne du feedback positif précis",
                'summary' => "« Bon travail » ne sert à rien : la personne ne sait pas quoi reproduire.",
                'micro_challenge' => "Repère aujourd'hui un comportement précis que tu veux voir se répéter, et dis-le : « Ce que tu as fait là, [précis], a eu [tel effet]. Continue. »",
                'duration_min' => 5,
                'icon' => 'gift',
                'body' => <<<MD
## Pourquoi

Le feedback positif est le plus négligé et le plus rentable. Mais « bravo, bon boulot » est inutile : trop vague pour être reproduit. Un renforcement efficace nomme **le comportement précis** et **son effet**. C'est ainsi qu'on installe durablement les bons réflexes.

## Comment

1. Comportement : qu'a fait exactement la personne ?
2. Effet : qu'est-ce que ça a produit (pour le client, l'équipe, toi) ?
3. Donne-le tôt et en privé ou en public selon la personne. Certains adorent la reconnaissance publique, d'autres la fuient — connais les tiens.
MD,
            ],
            [
                'day' => 19,
                'theme' => 'Le feedback',
                'title' => "Recadre sans humilier",
                'summary' => "Le but d'un recadrage n'est pas de te soulager, mais de changer un comportement.",
                'micro_challenge' => "Prépare un recadrage en attente avec la trame : fait + impact + attente. En privé, jamais à chaud, jamais devant les autres.",
                'duration_min' => 10,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Un recadrage réussi corrige un comportement sans abîmer la personne ni la relation. L'erreur classique : décharger sa frustration. Le test est simple — *est-ce que je veux changer quelque chose, ou est-ce que je veux me défouler ?* Seul le premier objectif est légitime.

## Comment

1. En privé, toujours. Critiquer en public humilie et dresse l'équipe contre toi.
2. Trame : **fait** observable + **impact** concret + **attente** claire pour la suite.
3. Sépare l'acte de l'identité : « ce rapport est en retard » (acte), pas « tu es quelqu'un de pas fiable » (identité). On change un acte, pas une identité.
MD,
            ],
            [
                'day' => 20,
                'theme' => 'Le feedback',
                'title' => "Donne le feedback tôt et petit",
                'summary' => "Le feedback différé s'accumule et explose ; le feedback fréquent reste léger.",
                'micro_challenge' => "Y a-t-il un petit truc que tu laisses traîner « parce que ce n'est pas si grave » ? Dis-le aujourd'hui, calmement, tant qu'il est encore petit.",
                'duration_min' => 5,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

Reporter un feedback ne le fait pas disparaître : il s'accumule. Et le jour où ça déborde, on reproche d'un coup trois mois de petites choses — injuste et incompréhensible pour l'autre. Le feedback fréquent et léger évite l'entretien lourd et redouté.

## Comment

1. Vise la proximité dans le temps : un retour à chaud (mais calme) vaut mieux qu'un bilan trimestriel.
2. Petit sujet = petite conversation. Ne transforme pas une remarque mineure en convocation solennelle.
3. Si tu ressens « ce n'est pas le moment », demande-toi si ce ne serait pas plutôt « ce n'est pas confortable ». Ce sont deux choses différentes.
MD,
            ],
            [
                'day' => 21,
                'theme' => 'Le feedback',
                'title' => "Oublie le sandwich, vise la clarté",
                'summary' => "Emballer une critique entre deux compliments brouille le message.",
                'micro_challenge' => "Pour ton prochain retour difficile, énonce directement le sujet en une phrase claire et bienveillante, sans l'enrober. Observe que c'est plus respectueux, pas moins.",
                'duration_min' => 5,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

La technique du « sandwich » (compliment – critique – compliment) part d'une bonne intention mais brouille tout : la personne ne sait plus si on la félicite ou on la corrige, et finit par se méfier de tout compliment. La clarté bienveillante respecte davantage l'autre que l'enrobage.

## Comment

1. Annonce le sujet franchement : « Je veux te parler de X. »
2. Sépare dans le temps reconnaissance et recadrage : les deux sont essentiels, mais pas dans la même phrase.
3. Bienveillance ≠ flou. On peut être à la fois direct sur le fond et chaleureux sur la forme.
MD,
            ],
            [
                'day' => 22,
                'theme' => 'Le feedback',
                'title' => "Termine par une demande claire",
                'summary' => "Un feedback sans attente explicite laisse l'autre deviner ce qu'il doit changer.",
                'micro_challenge' => "Après ton prochain retour, conclus toujours par : « Concrètement, ce que je te propose pour la suite, c'est… » et vérifie l'accord.",
                'duration_min' => 5,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

Beaucoup de feedbacks décrivent un problème mais oublient l'essentiel : *qu'est-ce que j'attends maintenant ?* Sans demande explicite, la personne sort en sachant qu'elle a déçu, mais pas comment faire mieux. C'est anxiogène et inefficace.

## Comment

1. Conclus toujours par une attente concrète et atteignable.
2. Formule-la au futur et au positif : « la prochaine fois, fais X » plutôt que « arrête de faire Y ».
3. Vérifie l'accord : « Est-ce que ça te paraît faisable ? » Un engagement vaut mieux qu'une consigne subie.
MD,
            ],
            [
                'day' => 23,
                'theme' => 'Le feedback',
                'title' => "Reçois mal une critique… puis reviens",
                'summary' => "Personne ne réagit parfaitement à chaud. Ce qui compte, c'est le retour.",
                'micro_challenge' => "Repense à une critique que tu as mal prise récemment. Reviens vers la personne : « J'ai réfléchi à ce que tu m'as dit, tu avais raison sur… »",
                'duration_min' => 10,
                'icon' => 'heart',
                'body' => <<<MD
## Pourquoi

Recevoir une critique déclenche une réaction défensive automatique — c'est biologique, pas un défaut de caractère. L'erreur n'est pas de mal réagir sur le moment ; c'est de ne jamais y revenir. Revenir après coup transforme un raté en preuve de maturité.

## Comment

1. Sur le moment, gagne du temps : « Merci, laisse-moi y réfléchir. »
2. Reviens dans les 48 h, à froid, avec ce que tu retiens.
3. Modélise ce comportement devant l'équipe : tu leur apprends qu'on a le droit de digérer un feedback avant d'y répondre.
MD,
            ],
            [
                'day' => 24,
                'theme' => 'Le feedback',
                'title' => "Célèbre les progrès, pas que les résultats",
                'summary' => "Attendre la réussite finale pour reconnaître, c'est laisser l'effort sans carburant.",
                'micro_challenge' => "Repère quelqu'un qui progresse sur quelque chose de difficile, même sans avoir encore réussi. Reconnais le progrès aujourd'hui.",
                'duration_min' => 5,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Si tu ne reconnais que les résultats finaux, tu ignores 90 % du chemin — là où se trouve l'effort. Or c'est l'effort et le progrès qu'on veut renforcer, surtout sur les tâches difficiles. Reconnaître le progrès entretient la motivation quand la ligne d'arrivée est encore loin.

## Comment

1. Repère les progrès relatifs : « Tu gères ce client beaucoup plus sereinement qu'il y a deux mois. »
2. Valorise la prise de risque et l'apprentissage, pas seulement la victoire.
3. Attention au piège inverse : reconnaître le progrès ne veut pas dire baisser le niveau d'exigence. On encourage **et** on garde le cap.
MD,
            ],
            // ===================================================================
            // BLOC 4 — DÉLÉGUER & RESPONSABILISER (J25-32)
            // ===================================================================
            [
                'day' => 25,
                'theme' => 'Déléguer',
                'title' => "Délègue le résultat, pas la méthode",
                'summary' => "Dicter chaque étape, ce n'est pas déléguer, c'est télécommander.",
                'micro_challenge' => "Pour une tâche que tu confies aujourd'hui, décris le résultat attendu et les contraintes — puis laisse la personne choisir comment s'y prendre.",
                'duration_min' => 10,
                'icon' => 'handshake',
                'body' => <<<MD
## Pourquoi

Déléguer la méthode (« fais-le exactement comme moi ») produit des exécutants et te garde indispensable. Déléguer le résultat (« voici le but et les limites, à toi de jouer ») produit de l'autonomie et te libère. La personne s'approprie la tâche d'autant mieux qu'elle a choisi son chemin.

## Comment

1. Définis clairement : le résultat attendu, l'échéance, les contraintes non négociables, les ressources.
2. Laisse la méthode ouverte. Une autre façon de faire que la tienne n'est pas forcément moins bonne.
3. Résiste à corriger une approche simplement parce qu'elle n'est pas la tienne. Interviens sur le résultat, pas sur le style.
MD,
            ],
            [
                'day' => 26,
                'theme' => 'Déléguer',
                'title' => "Adapte ton soutien au niveau d'autonomie",
                'summary' => "Le même niveau d'encadrement pour tous est forcément inadapté pour la plupart.",
                'micro_challenge' => "Pour une personne, situe-la sur une tâche précise : débutante (a besoin de cadre) ou autonome (a besoin de marge) ? Ajuste ton soutien en conséquence.",
                'duration_min' => 10,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

Le management situationnel (Hersey & Blanchard) rappelle une évidence souvent oubliée : le bon niveau d'encadrement dépend de la **compétence** et de la **motivation** de la personne *sur cette tâche précise*. Encadrer un expert l'étouffe ; lâcher un débutant l'angoisse. Le même style pour tous est donc inadapté pour presque tous.

## Comment

1. Pour chaque tâche, évalue : la personne sait-elle faire ? est-elle motivée/confiante ?
2. Débutant motivé → cadre et instructions. Compétent mais hésitant → soutien et encouragement. Autonome → délègue et fais confiance.
3. Une même personne peut être autonome sur une tâche et débutante sur une autre. Raisonne par tâche, pas par étiquette.
MD,
            ],
            [
                'day' => 27,
                'theme' => 'Déléguer',
                'title' => "Accepte le droit à l'erreur",
                'summary' => "Une délégation sans droit à l'erreur n'est qu'une mise à l'épreuve déguisée.",
                'micro_challenge' => "Identifie une tâche déléguée où une erreur serait réparable. Dis explicitement : « Si tu te trompes, on corrigera ensemble, c'est permis. »",
                'duration_min' => 5,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Déléguer, c'est accepter que ce soit fait différemment, et parfois moins bien au début. Si tu reprends à la moindre imperfection, la personne comprend qu'elle n'a pas vraiment la main — et arrête de prendre des initiatives. Le droit à l'erreur (sur ce qui est réparable) est le prix de l'autonomie.

## Comment

1. Distingue les erreurs réparables (la majorité — on apprend) des erreurs critiques (irréversibles, coûteuses — on sécurise davantage).
2. Sur le réparable, laisse faire et debriefe après, sans drame.
3. Quand une erreur arrive, demande « qu'est-ce qu'on en apprend ? » avant « qui est responsable ? ».
MD,
            ],
            [
                'day' => 28,
                'theme' => 'Déléguer',
                'title' => "Ne reprends pas le travail délégué",
                'summary' => "Refaire le travail d'un autre, c'est lui apprendre à ne plus le faire.",
                'micro_challenge' => "Repère une tâche que tu as tendance à « reprendre en main ». Aujourd'hui, laisse-la à son responsable, même imparfaite, et coache plutôt que de refaire.",
                'duration_min' => 5,
                'icon' => 'handshake',
                'body' => <<<MD
## Pourquoi

Le « je vais le faire moi-même, ce sera plus vite fait » est le piège mortel du manager. À court terme, oui. À moyen terme : la personne ne progresse pas, tu te surcharges, et tu deviens le goulot d'étranglement de ton équipe. Reprendre un travail délégué annule la délégation.

## Comment

1. Si le résultat est « suffisamment bon », accepte-le. Le parfait selon toi n'est pas toujours le nécessaire.
2. Si c'est insuffisant, renvoie avec un feedback précis plutôt que de corriger toi-même.
3. Investis le temps gagné dans le coaching : 30 minutes pour faire monter quelqu'un valent mieux que 30 minutes à faire à sa place.
MD,
            ],
            [
                'day' => 29,
                'theme' => 'Déléguer',
                'title' => "Fixe des points de contrôle, pas du flicage",
                'summary' => "Déléguer sans aucun suivi, c'est abandonner ; suivre en continu, c'est surveiller.",
                'micro_challenge' => "Pour une tâche déléguée, convenez ENSEMBLE d'un ou deux jalons de point d'étape, à l'avance. Entre les jalons, tu ne demandes rien.",
                'duration_min' => 10,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

Entre le manager qui abandonne (« débrouille-toi ») et celui qui surveille en continu (« où ça en est ? » dix fois par jour), il y a le suivi par jalons. Des points de contrôle convenus à l'avance rassurent les deux parties sans transformer la délégation en flicage.

## Comment

1. Au moment de déléguer, fixez ensemble les jalons : quand, sur quoi, sous quelle forme.
2. Entre deux jalons, fais confiance et tiens-toi disponible — sans relancer.
3. Adapte la fréquence au risque et à l'autonomie : plus c'est risqué ou nouveau, plus les jalons sont rapprochés.
MD,
            ],
            [
                'day' => 30,
                'theme' => 'Déléguer',
                'title' => "Délègue aussi ce que tu aimes faire",
                'summary' => "On garde souvent les tâches agréables — et on prive l'équipe de ce qui motive.",
                'micro_challenge' => "Liste 3 tâches que tu gardes « parce que tu les aimes ». Y en a-t-il une qui ferait grandir et plaisir à quelqu'un de l'équipe ? Confie-la.",
                'duration_min' => 10,
                'icon' => 'gift',
                'body' => <<<MD
## Pourquoi

Les managers délèguent volontiers les corvées et gardent les missions valorisantes : la présentation au comité, le projet visible, le client prestigieux. Mais ces tâches sont justement celles qui font grandir et qui motivent. Garder tout ce qui brille, c'est se rendre indispensable au détriment de l'équipe.

## Comment

1. Repère ce que tu gardes par plaisir ou par ego plus que par nécessité.
2. Identifie qui, dans l'équipe, gagnerait en visibilité ou en compétence à le reprendre.
3. Accompagne le passage de témoin : ce n'est pas se déposséder, c'est offrir une marche à gravir.
MD,
            ],
            [
                'day' => 31,
                'theme' => 'Déléguer',
                'title' => "Clarifie qui décide quoi",
                'summary' => "Le flou sur le pouvoir de décision est une fabrique de conflits et de lenteur.",
                'micro_challenge' => "Pour un dossier en cours, précise à voix haute : « Sur ça, tu décides seul. Sur ça, tu me consultes. Sur ça, c'est moi qui tranche. »",
                'duration_min' => 10,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Beaucoup de tensions ne viennent pas du *quoi* mais du *qui décide*. Quand le périmètre de décision est flou, soit les gens n'osent rien (et tout remonte à toi), soit ils décident à ta place (et tu le découvres trop tard). Clarifier les niveaux de délégation supprime cette zone grise.

## Comment

1. Pour un sujet, distingue trois niveaux : *décide seul et informe* / *propose et on valide ensemble* / *me laisse décider*.
2. Dis-le explicitement, à l'avance, par type de décision (budget, planning, relation client…).
3. En cas de doute, mieux vaut une règle imparfaite mais connue qu'une zone floue. On l'ajuste ensuite.
MD,
            ],
            [
                'day' => 32,
                'theme' => 'Déléguer',
                'title' => "Couvre publiquement, corrige en privé",
                'summary' => "Un manager qui lâche son équipe sous pression la perd définitivement.",
                'micro_challenge' => "Si une critique vise le travail de ton équipe aujourd'hui, assume-la devant les autres (« c'est sous ma responsabilité »), et traite le fond en interne.",
                'duration_min' => 5,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Sous pression (un client mécontent, un dirigeant énervé), la tentation est de se désolidariser : « ce n'est pas moi, c'est untel ». C'est l'erreur qui détruit le plus vite la loyauté d'une équipe. Le manager porte la responsabilité collective vers l'extérieur, et traite les responsabilités individuelles à l'intérieur.

## Comment

1. À l'extérieur : « C'est notre travail, je le prends. » Tu es le paratonnerre, pas le délateur.
2. À l'intérieur : tu peux être exigeant, recadrer, corriger — sans témoin.
3. Cette règle n'excuse pas tout : couvrir une faute grave répétée serait de la complaisance. Mais protéger des erreurs ordinaires, c'est ton rôle.
MD,
            ],
            // ===================================================================
            // BLOC 5 — MOTIVER & RECONNAÎTRE (J33-40)
            // ===================================================================
            [
                'day' => 33,
                'theme' => 'Motiver',
                'title' => "Nourris l'autonomie, la maîtrise, le sens",
                'summary' => "La motivation durable ne s'achète pas : elle se cultive sur trois leviers.",
                'micro_challenge' => "Pour une personne, demande-toi lequel des trois leviers (autonomie, montée en compétence, sens) lui manque le plus en ce moment. Agis sur celui-là cette semaine.",
                'duration_min' => 10,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

La recherche sur la motivation (Deci & Ryan, théorie de l'autodétermination) montre que la motivation *durable* repose sur trois besoins : l'**autonomie** (avoir une marge de manœuvre), la **compétence** (progresser, réussir) et le **lien/sens** (compter, servir à quelque chose). Les primes motivent à court terme ; ces trois leviers motivent en profondeur.

## Comment

1. Autonomie : laisse des choix, même petits, sur le comment.
2. Compétence : confie des défis à la bonne hauteur — ni écrasants, ni ennuyeux.
3. Sens : relie régulièrement le travail à son effet réel (le client soulagé, le collègue aidé).
MD,
            ],
            [
                'day' => 34,
                'theme' => 'Motiver',
                'title' => "Reconnais avant qu'on te le réclame",
                'summary' => "La reconnaissance attendue trop longtemps se transforme en rancœur.",
                'micro_challenge' => "Envoie aujourd'hui un message de reconnaissance sincère et précis à quelqu'un qui ne s'y attend pas. Sans rien demander en retour.",
                'duration_min' => 5,
                'icon' => 'gift',
                'body' => <<<MD
## Pourquoi

Le manque de reconnaissance est l'une des premières causes de départ et de désengagement. Et la reconnaissance ne coûte rien — ni budget, ni temps. Pourtant on la rationne, par pudeur ou par oubli. Une reconnaissance spontanée, non sollicitée, a un effet bien supérieur à celle qu'on finit par arracher.

## Comment

1. Sois précis et sincère : « Ton intervention hier a débloqué la situation, merci. »
2. Varie les formes : un mot, un message, une mention en réunion, un vrai « merci » en face.
3. Reconnais aussi l'invisible : la personne qui désamorce les tensions, celle qui aide les autres discrètement.
MD,
            ],
            [
                'day' => 35,
                'theme' => 'Motiver',
                'title' => "Personnalise ce qui motive chacun",
                'summary' => "Ce qui te motive n'est pas ce qui motive les autres.",
                'micro_challenge' => "Demande à une personne, simplement : « Qu'est-ce qui te donne de l'énergie dans ton travail, et qu'est-ce qui t'en prend ? » Écoute, ne suppose pas.",
                'duration_min' => 10,
                'icon' => 'heart',
                'body' => <<<MD
## Pourquoi

L'erreur de projection : croire que les autres veulent ce que je veux. Certains carburent à l'autonomie, d'autres à la sécurité ; certains veulent de la visibilité, d'autres la fuient ; certains rêvent d'évolution, d'autres d'équilibre. Motiver, c'est d'abord **connaître** les ressorts de chacun.

## Comment

1. Pose la question directement, plutôt que de deviner : « Qu'est-ce qui te motive vraiment ? »
2. Observe ce vers quoi la personne va naturellement quand elle a le choix.
3. Adapte tes leviers : proposer une promotion à quelqu'un qui cherche de la stabilité peut être vécu comme une menace, pas une récompense.
MD,
            ],
            [
                'day' => 36,
                'theme' => 'Motiver',
                'title' => "Protège le temps de concentration",
                'summary' => "On ne motive pas une équipe qu'on interrompt toutes les dix minutes.",
                'micro_challenge' => "Identifie une source d'interruption que tu provoques (messages non urgents, réunions à rallonge). Réduis-la aujourd'hui.",
                'duration_min' => 10,
                'icon' => 'clock',
                'body' => <<<MD
## Pourquoi

La motivation profonde naît souvent de l'état de **flow** : être absorbé par une tâche à sa hauteur. Or les interruptions permanentes (notifications, réunions, sollicitations « vite fait ») détruisent le flow et l'épanouissement. Un manager qui protège le temps de concentration de son équipe agit directement sur sa motivation et sa performance.

## Comment

1. Regroupe tes sollicitations au lieu de les égrener au fil de l'eau.
2. Distingue l'urgent du « ça peut attendre la réunion d'équipe ».
3. Crée des plages protégées (pas de réunion, pas de message non urgent) et respecte-les toi-même.
MD,
            ],
            [
                'day' => 37,
                'theme' => 'Motiver',
                'title' => "Transforme les victoires en rituels",
                'summary' => "Une réussite non célébrée s'efface ; une réussite partagée soude l'équipe.",
                'micro_challenge' => "Repère une réussite récente de l'équipe, même modeste. Prends 2 minutes en réunion pour la nommer et remercier ceux qui y ont contribué.",
                'duration_min' => 5,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

Les équipes passent d'un dossier à l'autre sans jamais marquer les réussites. Résultat : le sentiment d'avancer manque, même quand on avance. Marquer les victoires, même petites, crée de l'élan, de la fierté collective et de la mémoire d'équipe.

## Comment

1. Rends-le rituel : un tour de table des « petites victoires » en réunion, par exemple.
2. Attribue le mérite nommément. Le « bravo l'équipe » général dilue ; nommer renforce.
3. Célèbre l'effort sur les échecs aussi : « on n'a pas gagné ce dossier, mais la façon dont on s'est battu est exactement ce qu'il fallait. »
MD,
            ],
            [
                'day' => 38,
                'theme' => 'Motiver',
                'title' => "Évite l'injustice perçue",
                'summary' => "Rien ne démotive plus vite que le sentiment d'un traitement inéquitable.",
                'micro_challenge' => "Demande-toi : y a-t-il dans l'équipe un déséquilibre visible (charge, avantages, attention) que je n'ai pas traité ? Nomme-le, ou corrige-le.",
                'duration_min' => 10,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Le sentiment d'iniquité est l'un des poisons les plus puissants pour une équipe (théorie de l'équité d'Adams). Ce n'est pas tant le niveau absolu de récompense qui compte que la **comparaison** : « pourquoi lui et pas moi ? ». Un favoritisme, même involontaire, se voit et se paie cher.

## Comment

1. Sois conscient de tes affinités : on accorde naturellement plus d'attention à ceux qui nous ressemblent.
2. Veille à l'équité de la charge, des opportunités intéressantes, du temps que tu accordes.
3. Équité ≠ uniformité : traiter justement, ce n'est pas traiter pareil, c'est traiter selon des critères explicables. Si tu ne peux pas expliquer une différence, c'est sans doute un favoritisme.
MD,
            ],
            [
                'day' => 39,
                'theme' => 'Motiver',
                'title' => "Donne de la visibilité au travail de l'équipe",
                'summary' => "Le travail invisible finit par démotiver ceux qui le portent.",
                'micro_challenge' => "Trouve une occasion cette semaine de rendre visible, vers le haut ou vers les autres équipes, une contribution de ton équipe — en citant les noms.",
                'duration_min' => 10,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

Une grande partie du rôle de manager est de faire **rayonner** le travail de son équipe au-delà de ses murs. Ceux qui produisent dans l'ombre, sans que leur contribution remonte, finissent par se démotiver ou partir là où on les verra. Relayer le mérite vers le haut, c'est l'un des plus beaux services que tu rends.

## Comment

1. Quand tu présentes un résultat à ta hiérarchie, cite qui l'a réalisé.
2. Crée des occasions pour ton équipe d'être vue : présentations, démonstrations, mentions.
3. Ne t'approprie jamais le mérite d'un autre. C'est tentant et c'est la trahison qui ne se pardonne pas.
MD,
            ],
            [
                'day' => 40,
                'theme' => 'Motiver',
                'title' => "Repère et préviens l'épuisement",
                'summary' => "Le meilleur élément qui s'éteint est plus coûteux que dix erreurs.",
                'micro_challenge' => "Repère la personne la plus chargée de ton équipe. Demande-lui ce qu'elle pourrait lâcher ou reporter, et aide-la à le faire.",
                'duration_min' => 10,
                'icon' => 'heart',
                'body' => <<<MD
## Pourquoi

Les profils les plus engagés sont les plus exposés au burn-out : ils en font toujours plus, jusqu'à craquer. Un manager attentif détecte les signaux faibles (fatigue qui dure, cynisme nouveau, repli, baisse de qualité chez quelqu'un de fiable) et agit avant la rupture, qui coûte humainement et opérationnellement très cher.

## Comment

1. Surveille les écarts durables au comportement habituel, surtout chez les surengagés.
2. Donne la permission de ralentir : beaucoup n'osent pas, par peur de décevoir.
3. Si les signaux sont sérieux, ne joue pas au thérapeute : oriente vers les ressources adaptées (médecine du travail, RH, soutien psychologique). Ton rôle est de repérer et d'aiguiller, pas de soigner.
MD,
            ],
            // ===================================================================
            // BLOC 6 — DÉVELOPPER & FAIRE GRANDIR (J41-48)
            // ===================================================================
            [
                'day' => 41,
                'theme' => 'Faire grandir',
                'title' => "Coache avec des questions, pas des réponses",
                'summary' => "Donner la solution dépanne aujourd'hui ; faire trouver développe pour toujours.",
                'micro_challenge' => "Dans une situation de coaching aujourd'hui, remplace « Voilà ce qu'il faut faire » par « Quelles options vois-tu ? Laquelle te semble la meilleure ? ».",
                'duration_min' => 10,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Le manager-coach ne transmet pas son savoir : il aide l'autre à construire le sien. Une réponse donnée résout un problème ; une bonne question développe une capacité à résoudre tous les problèmes du même type. C'est plus lent au début, infiniment plus rentable ensuite.

## Comment

1. Inspire-toi de la trame GROW : **G**oal (quel objectif ?), **R**eality (où en es-tu ?), **O**ptions (que peux-tu faire ?), **W**ill (que vas-tu faire ?).
2. Pose des questions sincères, pas des questions piégées dont tu attends « la » réponse.
3. Garde le conseil direct pour les urgences ou quand l'enjeu d'apprentissage est faible. Le coaching demande du temps : choisis tes moments.
MD,
            ],
            [
                'day' => 42,
                'theme' => 'Faire grandir',
                'title' => "Confie des missions qui étirent",
                'summary' => "On grandit dans la zone juste au-delà de ce qu'on sait déjà faire.",
                'micro_challenge' => "Identifie pour une personne une mission légèrement au-dessus de son niveau actuel. Propose-la, avec ton soutien. C'est un cadeau, pas un test.",
                'duration_min' => 10,
                'icon' => 'rocket',
                'body' => <<<MD
## Pourquoi

On apprend le mieux dans la « zone proximale de développement » (Vygotski) : ni trop facile (ennui), ni trop dur (découragement), mais juste au-dessus de ce qu'on maîtrise, avec du soutien. Les missions étirantes (stretch assignments) sont le premier moteur de développement — bien plus que les formations.

## Comment

1. Repère une compétence à développer chez la personne, et une mission réelle qui l'exige.
2. Présente-la comme une opportunité de confiance, avec un filet : « Je te confie X, je suis là si besoin. »
3. Calibre la hauteur : trop bas, c'est de l'occupation ; trop haut sans soutien, c'est un piège. Reste disponible sans faire à la place.
MD,
            ],
            [
                'day' => 43,
                'theme' => 'Faire grandir',
                'title' => "Parle carrière, même quand tu n'as rien à offrir",
                'summary' => "Ne pas parler d'avenir, c'est laisser chacun l'imaginer ailleurs.",
                'micro_challenge' => "Avec une personne, ouvre la conversation : « Dans un an ou deux, vers quoi aimerais-tu aller ? » Écoute, même si tu n'as pas de poste à proposer.",
                'duration_min' => 10,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Les managers évitent souvent de parler évolution par peur de créer des attentes qu'ils ne pourront pas satisfaire. Erreur : ne pas en parler ne supprime pas les attentes, ça les pousse à se réaliser ailleurs. Comprendre les aspirations de chacun te permet d'aligner — autant que possible — leur travail avec leur trajectoire.

## Comment

1. Sépare l'écoute des aspirations (toujours possible) de la promesse de poste (rarement entre tes mains).
2. Cherche les développements possibles *dans* le rôle actuel : nouvelles responsabilités, expertises, visibilité.
3. Sois honnête sur ce que tu peux et ne peux pas garantir. Une attente claire vaut mieux qu'un espoir entretenu.
MD,
            ],
            [
                'day' => 44,
                'theme' => 'Faire grandir',
                'title' => "Fais des erreurs un matériau d'apprentissage",
                'summary' => "Une erreur analysée sans blâme vaut une formation ; punie, elle vaut un secret.",
                'micro_challenge' => "Pour un raté récent, organise un mini-débrief « sans coupable » : qu'est-ce qui a conduit là ? qu'est-ce qu'on change pour la prochaine fois ?",
                'duration_min' => 10,
                'icon' => 'lightbulb',
                'body' => <<<MD
## Pourquoi

Les organisations qui apprennent le plus vite sont celles qui analysent leurs erreurs sans chercher de coupable (post-mortem sans blâme, comme dans l'aéronautique). Le réflexe « à qui la faute ? » apprend à cacher les erreurs ; le réflexe « qu'est-ce qu'on en apprend ? » apprend à les prévenir.

## Comment

1. Concentre le débrief sur le **système** (process, info manquante, charge) avant les personnes.
2. Pose trois questions : que s'est-il passé ? pourquoi (sans juger) ? qu'est-ce qu'on change ?
3. Termine par une action concrète. Un débrief sans décision n'est qu'une séance de regrets.
MD,
            ],
            [
                'day' => 45,
                'theme' => 'Faire grandir',
                'title' => "Crée des binômes et du partage de savoir",
                'summary' => "Le savoir concentré sur une personne est une fragilité ; partagé, c'est une force.",
                'micro_challenge' => "Repère une compétence détenue par une seule personne dans l'équipe. Organise un moment où elle la transmet à quelqu'un d'autre.",
                'duration_min' => 10,
                'icon' => 'users',
                'body' => <<<MD
## Pourquoi

Quand une compétence critique repose sur une seule personne, l'équipe est fragile (que se passe-t-il si elle part ou tombe malade ?) et la personne est prisonnière de son expertise. Organiser le partage de savoir réduit le risque, fait grandir d'autres, et valorise l'expert dans un rôle de transmetteur.

## Comment

1. Cartographie les savoirs « mono-détenus » : qui est le seul à savoir faire quoi ?
2. Mets en place des binômes, des démonstrations, de la documentation partagée.
3. Valorise la transmission comme une contribution à part entière, pas comme une corvée en plus.
MD,
            ],
            [
                'day' => 46,
                'theme' => 'Faire grandir',
                'title' => "Encourage l'initiative, même imparfaite",
                'summary' => "Une équipe qui attend tes ordres est une équipe que tu as dressée à attendre.",
                'micro_challenge' => "Quand quelqu'un prend une initiative aujourd'hui (même perfectible), valorise d'abord la prise d'initiative avant de commenter le contenu.",
                'duration_min' => 5,
                'icon' => 'rocket',
                'body' => <<<MD
## Pourquoi

Si chaque initiative est accueillie par une critique du résultat, les gens cessent d'en prendre — c'est moins risqué d'attendre les consignes. Or une équipe autonome est précisément une équipe qui ose agir sans tout demander. Tu obtiens le comportement que tu renforces.

## Comment

1. Découple toujours deux messages : « Bravo d'avoir pris l'initiative » (à renforcer) et « voici comment l'améliorer » (le contenu).
2. Mets le premier en avant, surtout au début. La perfection viendra avec la pratique.
3. Quand tu dois corriger, garde la porte ouverte : « Continue à proposer, c'est exactement ce que j'attends. »
MD,
            ],
            [
                'day' => 47,
                'theme' => 'Faire grandir',
                'title' => "Prépare quelqu'un à te remplacer",
                'summary' => "Un manager irremplaçable est un manager qui bloque tout le monde, à commencer par lui.",
                'micro_challenge' => "Identifie une de tes responsabilités que personne ne saurait reprendre demain. Commence à former quelqu'un dessus cette semaine.",
                'duration_min' => 10,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Paradoxe du management : plus tu te rends remplaçable, plus tu deviens promouvable. Une équipe qui ne tourne pas sans toi te cloue à ton poste et s'effondre dès que tu t'absentes. Préparer sa propre relève, c'est sécuriser l'équipe **et** ouvrir ton propre avenir.

## Comment

1. Identifie les points de dépendance critiques à ta personne.
2. Délègue progressivement, en formant et en documentant.
3. Teste : pars en congés sans être joignable. Ce qui casse en ton absence te montre exactement ce qu'il reste à transmettre.
MD,
            ],
            [
                'day' => 48,
                'theme' => 'Faire grandir',
                'title' => "Adapte ton style à la personne",
                'summary' => "Manager tout le monde de la même façon, c'est bien manager personne.",
                'micro_challenge' => "Choisis deux personnes très différentes. Pour chacune, note une chose que tu devrais faire différemment dans ta manière de les manager.",
                'duration_min' => 10,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

L'équité n'est pas l'uniformité. Certains ont besoin de cadre, d'autres de liberté ; certains de fréquence de contact, d'autres de distance ; certains de défis, d'autres de stabilité. Le bon manager ajuste sa posture à la personne et à la situation, sans pour autant renoncer à des principes communs.

## Comment

1. Observe ce qui « marche » avec chacun : quand donne-t-il le meilleur ?
2. Ajuste ce qui est ajustable (fréquence, autonomie, type de soutien) en gardant constant ce qui ne l'est pas (règles, exigences, respect).
3. Explique cette différenciation si elle interroge : « je n'adapte pas mes exigences, j'adapte ma façon de vous accompagner. »
MD,
            ],
            // ===================================================================
            // BLOC 7 — TENSIONS, CONFLITS & DÉCISIONS (J49-56)
            // ===================================================================
            [
                'day' => 49,
                'theme' => 'Tensions & décisions',
                'title' => "N'enterre pas les conflits, traite-les tôt",
                'summary' => "Un conflit ignoré ne s'éteint pas : il s'infecte.",
                'micro_challenge' => "Y a-t-il une tension dans l'équipe que tu fais semblant de ne pas voir ? Décide aujourd'hui d'en parler à l'une des parties cette semaine.",
                'duration_min' => 10,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

L'évitement est la réaction la plus naturelle face au conflit — et la plus coûteuse. Un désaccord non traité se transforme en rancune, en clans, en climat pourri. Plus on attend, plus c'est dur à dénouer. Aborder tôt, quand l'enjeu émotionnel est encore faible, est infiniment plus simple.

## Comment

1. Distingue le désaccord sain (utile, à encourager) du conflit qui s'envenime (relationnel, à traiter).
2. Ouvre la conversation factuellement : « J'ai senti une tension entre vous, j'aimerais comprendre. »
3. Ne prends pas parti trop vite : écoute chaque version avant de te forger une opinion.
MD,
            ],
            [
                'day' => 50,
                'theme' => 'Tensions & décisions',
                'title' => "Prépare tes conversations difficiles",
                'summary' => "Improviser une conversation difficile, c'est presque toujours la rater.",
                'micro_challenge' => "Pour une conversation difficile en attente, écris en 3 lignes : le fait, ce que tu ressens, ce que tu veux obtenir. Puis lance-la.",
                'duration_min' => 10,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

Les conversations difficiles (recadrage lourd, désaccord, sujet personnel) ratent surtout par manque de préparation : on part dans l'émotion, on attaque ou on tourne autour. Quelques minutes de préparation (cf. *Conversations difficiles*, Stone & Heen) changent tout.

## Comment

1. Clarifie ton objectif réel : qu'est-ce que je veux *obtenir*, et pour la relation ?
2. Prépare ton ouverture factuelle, et anticipe les réactions possibles.
3. Choisis le bon moment et le bon lieu : jamais à chaud, jamais en public, jamais en coup de vent.
MD,
            ],
            [
                'day' => 51,
                'theme' => 'Tensions & décisions',
                'title' => "Vise l'intérêt, pas la position",
                'summary' => "Deux positions opposées cachent souvent des intérêts conciliables.",
                'micro_challenge' => "Dans un désaccord en cours, demande à chaque partie : « Qu'est-ce qui est vraiment important pour toi là-dedans ? » Cherche l'intérêt derrière la position.",
                'duration_min' => 10,
                'icon' => 'handshake',
                'body' => <<<MD
## Pourquoi

La négociation raisonnée (Fisher & Ury, Harvard) distingue la **position** (« je veux X ») de l'**intérêt** (« parce que j'ai besoin de Y »). Deux positions peuvent être incompatibles alors que les intérêts sous-jacents se concilient. Creuser les intérêts ouvre des solutions invisibles au niveau des positions.

## Comment

1. Derrière chaque exigence, cherche le besoin : sécurité, reconnaissance, charge, équité ?
2. Reformule les intérêts des deux côtés à voix haute, pour montrer qu'ils sont entendus.
3. Cherche des options qui satisfont les intérêts essentiels de chacun, plutôt qu'un compromis qui frustre tout le monde.
MD,
            ],
            [
                'day' => 52,
                'theme' => 'Tensions & décisions',
                'title' => "Décide, même dans l'incertitude",
                'summary' => "Ne pas décider est aussi une décision — souvent la pire.",
                'micro_challenge' => "Repère une décision que tu repousses faute d'avoir « toutes les infos ». Fixe-toi une date butoir pour trancher, même imparfaitement.",
                'duration_min' => 10,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Attendre la certitude parfaite, c'est condamner l'équipe à l'attente et à l'anxiété. La plupart des décisions managériales se prennent avec 70 % de l'information : au-delà, le coût du délai dépasse le gain de précision. L'indécision chronique d'un manager paralyse toute une équipe.

## Comment

1. Classe la décision : réversible (décide vite, ajuste après) ou irréversible (prends plus de temps).
2. Fixe une échéance de décision et tiens-la.
3. Une fois décidé, assume et communique clairement — y compris l'incertitude restante. Revenir sans cesse sur ses décisions vaut presque aussi cher que ne pas décider.
MD,
            ],
            [
                'day' => 53,
                'theme' => 'Tensions & décisions',
                'title' => "Explique les décisions que tu n'as pas prises",
                'summary' => "Une décision venue d'en haut, mal expliquée, c'est toi qu'on tient pour responsable.",
                'micro_challenge' => "Pour une décision imposée par le haut, explique à l'équipe le contexte et le pourquoi — sans la critiquer ni la subir, en l'assumant comme relais.",
                'duration_min' => 10,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

Le manager de proximité est souvent le relais de décisions qu'il n'a pas prises. Deux pièges : les subir en se plaignant (« moi non plus je ne suis pas d'accord, mais bon… »), ce qui te décrédibilise et démobilise ; ou les imposer sèchement, ce qui crée de la résistance. La voie juste : expliquer honnêtement.

## Comment

1. Comprends d'abord le pourquoi de la décision (quitte à le demander à ta hiérarchie).
2. Explique le contexte sans te désolidariser ni faire semblant d'être l'auteur.
3. Tu peux reconnaître les difficultés (« je sais que ça complique vos plannings ») tout en portant la décision. Loyauté ascendante **et** honnêteté descendante.
MD,
            ],
            [
                'day' => 54,
                'theme' => 'Tensions & décisions',
                'title' => "Gère le collaborateur difficile par les faits",
                'summary' => "Face à un comportement problématique, l'émotion divise, les faits cadrent.",
                'micro_challenge' => "Pour une situation tendue avec une personne, documente 2-3 faits précis (dates, comportements observables). Tu en auras besoin pour une conversation cadrée.",
                'duration_min' => 10,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

Avec un collaborateur au comportement difficile (agressivité, démotivation affichée, non-respect des règles), le manager bascule souvent dans le ressenti (« il est pénible ») — indéfendable et contre-productif. S'appuyer sur des faits précis permet une conversation cadrée, équitable, et si besoin un dossier solide.

## Comment

1. Documente factuellement : quoi, quand, quel impact. Pas d'adjectifs, des faits.
2. Aborde le comportement, pas la personnalité : « tu as coupé la parole à trois reprises hier » plutôt que « tu es irrespectueux ».
3. Si le comportement persiste malgré des échanges clairs, formalise et appuie-toi sur les RH. Tu n'es pas seul, et certains sujets dépassent le management de proximité.
MD,
            ],
            [
                'day' => 55,
                'theme' => 'Tensions & décisions',
                'title' => "Maîtrise tes propres émotions d'abord",
                'summary' => "Un manager qui réagit à chaud transmet son stress à toute l'équipe.",
                'micro_challenge' => "La prochaine fois que tu sens la colère ou l'agacement monter, applique la règle des 6 secondes : respire, et ne réponds rien avant. Reporte si besoin.",
                'duration_min' => 5,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Les émotions du manager sont contagieuses (contagion émotionnelle) : ton stress devient celui de l'équipe, ton calme aussi. Réagir à chaud — un mail cinglant, un éclat en réunion — fait des dégâts durables pour un soulagement de quelques secondes. La régulation émotionnelle est une compétence managériale centrale.

## Comment

1. Apprends à reconnaître tes signaux de montée émotionnelle (mâchoire, ton qui monte, envie de répondre vite).
2. Crée un délai : « j'y reviens », une respiration, une nuit. Rien d'urgent ne se règle mieux à chaud.
3. Si tu as débordé, répare : « j'ai réagi trop vivement tout à l'heure, je m'en excuse. » Ça grandit, ça n'abaisse pas.
MD,
            ],
            [
                'day' => 56,
                'theme' => 'Tensions & décisions',
                'title' => "Sache dire non et arbitrer",
                'summary' => "Dire oui à tout, c'est trahir ses priorités et épuiser son équipe.",
                'micro_challenge' => "Identifie une demande à laquelle tu devrais dire non (ou « pas maintenant »). Formule un refus clair et argumenté, sans culpabilité.",
                'duration_min' => 5,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Un manager qui dit oui à toutes les sollicitations (de sa hiérarchie, des autres services, de son équipe) disperse ses ressources et finit par décevoir tout le monde. Savoir dire non — ou « oui, mais alors quoi en moins ? » — protège les priorités et la santé de l'équipe.

## Comment

1. Un refus n'est pas un rejet de la personne : sépare le non à la demande du respect dû à l'interlocuteur.
2. Explique le critère : « Je dis non parce que ça nous détournerait de X, qui est prioritaire. »
3. Propose une alternative quand c'est possible : un délai, une version réduite, une autre ressource. Le « non » constructif ouvre une porte ailleurs.
MD,
            ],
            // ===================================================================
            // BLOC 8 — SENS, VISION & DURER (J57-60)
            // ===================================================================
            [
                'day' => 57,
                'theme' => 'Sens & durer',
                'title' => "Relie le quotidien à une direction",
                'summary' => "Sans cap visible, le travail quotidien devient une suite de tâches absurdes.",
                'micro_challenge' => "En réunion ou en 1:1 cette semaine, relie explicitement une tâche du quotidien à l'objectif plus large qu'elle sert.",
                'duration_min' => 10,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Les gens supportent beaucoup d'efforts s'ils savent à quoi ils servent. Le rôle du manager est de maintenir vivant le lien entre les tâches du jour et une direction qui a du sens. C'est la fameuse parabole des tailleurs de pierre : l'un « taille un caillou », l'autre « bâtit une cathédrale ». Même geste, sens opposé.

## Comment

1. Reformule régulièrement le « pourquoi global » : ce vers quoi l'équipe avance, et pourquoi ça compte.
2. Relie les tâches concrètes à ce cap : « ce que tu fais là, ça sert à… ».
3. Répète : le sens s'érode avec la routine. Ce qui était clair il y a six mois doit être ravivé.
MD,
            ],
            [
                'day' => 58,
                'theme' => 'Sens & durer',
                'title' => "Sois cohérent entre tes mots et tes actes",
                'summary' => "L'équipe ne croit pas ce que tu dis, elle croit ce que tu fais.",
                'micro_challenge' => "Repère un écart entre une valeur que tu prônes et un de tes comportements. Corrige le comportement, ou cesse de prôner la valeur.",
                'duration_min' => 10,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

L'exemplarité n'est pas une option : ton équipe te regarde et calibre son comportement sur le tien, pas sur tes discours. Prôner l'équilibre vie pro/perso en envoyant des mails à 23 h, exiger la ponctualité en arrivant en retard : chaque incohérence détruit ta crédibilité plus sûrement qu'une faute avouée.

## Comment

1. Repère tes écarts entre le dire et le faire — demande même à un proche de te les signaler.
2. Choisis : aligne ton comportement, ou abandonne le discours. Le pire est de maintenir les deux.
3. Quand tu déroges (ça arrive), nomme-le : « je sais, j'envoie ce mail tard, ne vous sentez pas obligés de répondre ce soir. »
MD,
            ],
            [
                'day' => 59,
                'theme' => 'Sens & durer',
                'title' => "Prends soin de toi pour tenir",
                'summary' => "Un manager épuisé ne protège plus personne — surtout pas son équipe.",
                'micro_challenge' => "Identifie une chose que tu négliges pour toi (pause, sommeil, soutien, déconnexion). Protège-la cette semaine comme tu protégerais celle d'un collaborateur.",
                'duration_min' => 10,
                'icon' => 'heart',
                'body' => <<<MD
## Pourquoi

Le manager de proximité absorbe les pressions du haut et les difficultés du bas : c'est l'un des postes les plus exposés à l'usure. Or un manager épuisé devient irritable, indécis, et cesse de faire tampon. Prendre soin de soi n'est pas un luxe égoïste : c'est une condition pour tenir ton rôle dans la durée.

## Comment

1. Applique-toi tes propres conseils : pauses, charge soutenable, droit à la déconnexion.
2. Trouve un espace pour souffler et prendre du recul : pair, mentor, réseau de managers. Manager est un métier solitaire — ne le sois pas.
3. Repère tes propres signaux d'usure et agis tôt, comme tu le ferais pour un membre de ton équipe.
MD,
            ],
            [
                'day' => 60,
                'theme' => 'Sens & durer',
                'title' => "Fais ton bilan et choisis la suite",
                'summary' => "60 jours de pratiques ne valent que par ce que tu décides d'en garder.",
                'micro_challenge' => "Relis ta photo de départ (jour 8). Choisis 3 pratiques de ce parcours qui ont eu le plus d'effet, et fais-en tes réflexes permanents.",
                'duration_min' => 15,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

Un parcours sans bilan se dissout dans le quotidien. Ce dernier jour ne clôt pas le travail : il en fixe les acquis. Le but n'est pas d'avoir « tout appliqué », mais d'avoir transformé quelques pratiques en réflexes durables — c'est largement suffisant pour changer ta manière de manager.

## Comment

1. Reprends la photo de départ (jour 8) : qu'est-ce qui a bougé dans ton équipe et chez toi ?
2. Choisis 3 pratiques qui ont eu le plus d'impact. Concentre-toi dessus : trois habitudes solides valent mieux que soixante intentions.
3. Recommence le parcours dans six mois si tu veux : avec l'expérience accumulée, les mêmes pratiques résonneront différemment. Le management est un métier qu'on n'a jamais fini d'apprendre.
MD,
            ],
        ];
    }
}
