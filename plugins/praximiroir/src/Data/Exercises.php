<?php

namespace Praxis\Plugins\PraxiMiroir\Data;

/**
 * Catalogue des 30 exercices d'introspection de PraxiMiroir.
 *
 * Public : toute personne en questionnement professionnel (salarié, demandeur
 * d'emploi, entrepreneur) souhaitant clarifier son identité avant d'agir.
 *
 * Parti pris : ancré sur la recherche en psychologie positive (Seligman),
 * l'approche narrative (White & Epston), l'ikigaï, la théorie de l'auto-
 * détermination (Deci & Ryan) et la psychologie des valeurs (Schwartz).
 * Chaque exercice comporte un ancrage théorique court, un mode d'emploi
 * pratique, et un prompt de réflexion libre à rédiger dans l'espace prévu.
 *
 * Les 30 jours sont organisés en 8 blocs progressifs :
 *   J1-4   L'Inventaire      — photographier le présent sans jugement
 *   J5-8   Les Valeurs       — ce qui compte vraiment
 *   J9-12  Les Forces        — ce que tu fais naturellement bien
 *   J13-16 Les Angles morts  — ce que tu ne vois pas encore de toi
 *   J17-20 Le Narratif       — l'histoire qui donne du sens
 *   J21-24 La Confiance      — reprendre la main sur ta légitimité
 *   J25-28 L'Identité future — qui veux-tu devenir ?
 *   J29-30 La Forge          — intégrer et sceller
 */
class Exercises
{
    public static function all(): array
    {
        return [

            // ===================================================================
            // BLOC 1 — L'INVENTAIRE (J1-4)
            // ===================================================================
            [
                'day'          => 1,
                'bloc'         => "L'Inventaire",
                'title'        => "La photographie du présent",
                'summary'      => "Décrire sa situation professionnelle actuelle sans la juger, c'est déjà la voir autrement.",
                'duration_min' => 15,
                'icon'         => 'camera',
                'prompt'       => "Décris ta situation professionnelle actuelle en 10 lignes, comme si tu la racontais à un ami qui te découvrirait. Faits, ressentis, questions ouvertes — sans te censurer.",
                'body'         => <<<MD
## Pourquoi

Avant d'explorer qui tu veux devenir, il faut observer avec précision où tu en es. Pas pour te juger, mais pour avoir un point de départ honnête. La plupart des personnes en questionnement professionnel passent directement aux solutions sans jamais poser clairement le tableau de leur situation.

Cette photographie initiale deviendra une référence : dans 30 jours, tu pourras mesurer le chemin parcouru.

## Comment

1. **Prends 5 minutes** pour noter ce que tu fais au quotidien dans ton travail (ou ce que tu cherches si tu es en transition).
2. **Ajoute ce que tu ressens** : ce qui te pèse, ce qui t'anime, ce qui te laisse indifférent.
3. **Pose 2 ou 3 questions** que tu te poses en ce moment sur ta vie pro — sans chercher à y répondre.

Il n'y a pas de bonne réponse. L'objectif est d'écrire, pas de trouver.
MD,
            ],

            [
                'day'          => 2,
                'bloc'         => "L'Inventaire",
                'title'        => "Les pics — tes moments de fierté",
                'summary'      => "Tes moments de plus grande satisfaction au travail contiennent la carte de ce qui te correspond.",
                'duration_min' => 15,
                'icon'         => 'mountain',
                'prompt'       => "Liste 5 moments dans ta vie professionnelle (ou de formation) où tu étais vraiment fier·e ou profondément satisfait·e. Pour chacun : que faisais-tu ? Avec qui ? Qu'est-ce qui te rendait fier·e exactement ?",
                'body'         => <<<MD
## Pourquoi

La psychologie positive (Seligman) montre que nos moments de *flow* — d'engagement total et sans effort — révèlent nos forces caractérielles. Ces pics ne mentent pas : ils pointent vers ce qui nous correspond vraiment, indépendamment des attentes des autres.

Ces 5 moments sont une mine d'or. On y trouve des thèmes récurrents, des compétences mobilisées, des contextes favorables. Ce sera la matière première des prochains jours.

## Comment

1. Ferme les yeux 2 minutes et laisse remonter des souvenirs professionnels positifs. Pas forcément spectaculaires — parfois c'est une réunion, une journée, un projet.
2. Note-les dans l'ordre où ils viennent, sans filtrer.
3. Pour chacun, réponds aux 3 questions du prompt. Plus tu es précis·e, plus l'exercice sera utile.
MD,
            ],

            [
                'day'          => 3,
                'bloc'         => "L'Inventaire",
                'title'        => "Ce qui m'anime, ce qui me draine",
                'summary'      => "L'énergie ne ment jamais. Cartographier ce qui te recharge et ce qui t'épuise révèle ton profil d'énergie.",
                'duration_min' => 15,
                'icon'         => 'bolt',
                'prompt'       => "Fais deux colonnes : « Ce qui m'anime » et « Ce qui me draine ». Liste au moins 5 éléments dans chaque colonne, tirés de ton expérience pro réelle. Cherche ensuite ce que les éléments de chaque colonne ont en commun.",
                'body'         => <<<MD
## Pourquoi

Le niveau d'énergie est un signal fiable de compatibilité. Pas de la performance : tu peux être performant·e sur quelque chose qui t'épuise. Mais durablement, ce qui nous draine nous use, et ce qui nous anime nous donne envie de progresser.

Cette cartographie t'aide à distinguer les compétences que tu *as* des compétences que tu *aimes utiliser* — une distinction fondamentale pour l'orientation.

## Comment

1. **Anime :** pense aux tâches, interactions, contextes, types de problèmes qui te font perdre la notion du temps ou dont tu ressors ragaillardi·e.
2. **Draine :** pense à ce que tu repousses, ce dont tu ressors vidé·e, ce qui te pèse au quotidien.
3. Une fois les deux listes faites, **cherche le fil** : y a-t-il un pattern ? (ex : « tout ce qui implique de la répétition me draine », « tout ce qui implique de convaincre m'anime »)
MD,
            ],

            [
                'day'          => 4,
                'bloc'         => "L'Inventaire",
                'title'        => "Qui suis-je dans le regard des autres ?",
                'summary'      => "Ce que les autres voient de nous révèle souvent des forces que nous avons naturalisées — et donc invisibles à nos propres yeux.",
                'duration_min' => 15,
                'icon'         => 'users',
                'prompt'       => "Si tu demandais à 3 personnes qui te connaissent bien (pro ou perso) de dire en 3 mots ce que tu apportes, que diraient-elles ? Note les mots que tu penses qu'elles choisiraient. Puis demande-toi : est-ce que tu reconnais ces qualités en toi ? Lesquelles tu as du mal à accepter ?",
                'body'         => <<<MD
## Pourquoi

Le « blind spot positif » est réel : nous sous-estimons souvent nos propres forces parce qu'elles nous viennent facilement — donc nous ne les valorisons pas. Le regard des autres est un miroir précieux, surtout pour ce que nous faisons naturellement, sans même y penser.

Cet exercice prépare le bloc « Forces » qui arrive dans quelques jours. Il commence aussi à interroger ton rapport à la légitimité : est-ce que tu t'autorises à reconnaître ce que tu apportes ?

## Comment

1. Choisis 3 personnes : un·e collègue, un·e manager ou client·e, une personne de ton entourage proche.
2. Imagine ce que chacune dirait. Écris 3 mots par personne.
3. Repère les mots qui reviennent, et ceux qui t'étonnent le plus.

*Optionnel mais puissant* : envoie réellement le message à ces personnes. La réponse t'apprendra quelque chose.
MD,
            ],

            // ===================================================================
            // BLOC 2 — LES VALEURS (J5-8)
            // ===================================================================
            [
                'day'          => 5,
                'bloc'         => "Les Valeurs",
                'title'        => "Les lignes rouges — ce que je ne ferais jamais",
                'summary'      => "Nos valeurs se révèlent mieux dans ce que nous refusons que dans ce que nous déclarons.",
                'duration_min' => 15,
                'icon'         => 'shield',
                'prompt'       => "Liste 5 choses que tu refuserais de faire dans un cadre professionnel, même sous pression. Pour chacune, demande-toi : quelle valeur est en jeu ? Qu'est-ce que ce refus dit de ce qui est important pour toi ?",
                'body'         => <<<MD
## Pourquoi

Quand on demande à quelqu'un ses valeurs, il répond souvent ce qu'il *pense* qu'il devrait valoriser (honnêteté, famille, courage…). Ce n'est pas faux — mais ce n'est pas toujours juste.

Les lignes rouges, elles, sont honnêtes. Ce qu'on ne ferait *jamais*, ce qu'on a refusé même quand c'était coûteux — ça, c'est une valeur réelle, pas déclarée.

## Comment

1. Pense à des situations concrètes : des propositions que tu aurais refusées, des comportements que tu n'aurais pas tolérés, des compromis que tu n'aurais pas faits.
2. Formule chaque ligne rouge en action : « Je ne ferais jamais... »
3. Cherche le nom de la valeur derrière : intégrité, loyauté, autonomie, justice, respect, bienveillance, ambition, créativité…
MD,
            ],

            [
                'day'          => 6,
                'bloc'         => "Les Valeurs",
                'title'        => "Les décisions dont je suis fier·e",
                'summary'      => "Tes meilleures décisions professionnelles ont un fil commun. Ce fil, c'est tes valeurs en action.",
                'duration_min' => 15,
                'icon'         => 'check-circle',
                'prompt'       => "Rappelle-toi 3 décisions professionnelles dont tu es fier·e — pas forcément les plus grandes, mais celles qui te semblent *juste*. Pour chacune : qu'est-ce que tu as choisi ? Qu'est-ce que tu aurais pu choisir à la place ? Qu'est-ce qui a guidé ta décision ?",
                'body'         => <<<MD
## Pourquoi

La recherche en psychologie des valeurs (Schwartz) montre que nos valeurs ne guident pas nos *déclarations* mais nos *arbitrages*. Ce sont les moments où on a choisi A plutôt que B — surtout quand les deux étaient légitimes — qui révèlent ce qui compte vraiment.

## Comment

1. Pense à des décisions à n'importe quelle échelle : changer de poste, refuser une mission, prendre la parole dans une réunion, ne pas prendre la parole, choisir un collaborateur plutôt qu'un autre…
2. Pour chaque décision, pose-toi la question : *qu'est-ce que j'aurais pu faire à la place, et pourquoi je ne l'ai pas fait ?*
3. Ce « pourquoi » contient tes valeurs.
MD,
            ],

            [
                'day'          => 7,
                'bloc'         => "Les Valeurs",
                'title'        => "Valeurs en actes — les nommer et les définir",
                'summary'      => "Une valeur sans définition comportementale reste floue. Aujourd'hui, tu ancres les tiennes dans le concret.",
                'duration_min' => 20,
                'icon'         => 'anchor',
                'prompt'       => "À partir de J5 et J6, identifie tes 3 valeurs professionnelles les plus importantes. Pour chacune : écris une phrase qui explique ce que cette valeur signifie *pour toi* (pas en général), et donne un exemple concret de moment où tu l'as honorée.",
                'body'         => <<<MD
## Pourquoi

« J'aime le travail bien fait » ou « la liberté est importante pour moi » — ces formules sont trop vagues pour guider quoi que ce soit. Une valeur opérationnelle ressemble plutôt à : *« La rigueur, pour moi, c'est rendre un travail complet même quand personne ne vérifie »*.

Cette précision te permettra d'évaluer des environnements de travail, des offres d'emploi, des projets — et de savoir si ça *résonne* ou pas.

## Comment

1. Reprends les lignes rouges (J5) et les décisions (J6). Extrais les 3 valeurs qui reviennent le plus fortement.
2. Pour chaque valeur, écris *ta propre définition* en une phrase. Évite les généralités.
3. Donne un exemple comportemental réel : « J'ai honoré cette valeur quand j'ai… »
MD,
            ],

            [
                'day'          => 8,
                'bloc'         => "Les Valeurs",
                'title'        => "La boussole — ta valeur cardinale",
                'summary'      => "Quand deux valeurs entrent en conflit, laquelle l'emporte ? Cette valeur-là est ta boussole.",
                'duration_min' => 15,
                'icon'         => 'compass',
                'prompt'       => "Parmi tes 3 valeurs (J7), laquelle primer sur les deux autres quand elles entrent en tension ? Décris une situation passée ou hypothétique où cette valeur l'a emporté — ou aurait dû l'emporter. En une phrase : ta boussole professionnelle, c'est…",
                'body'         => <<<MD
## Pourquoi

Avoir des valeurs est une chose. Savoir les hiérarchiser en situation de conflit, c'est ce qui donne de la cohérence à une trajectoire professionnelle. Sans boussole, on oscille.

Cette valeur cardinale devient un outil pratique : quand tu dois évaluer une opportunité, une mission, un environnement de travail — tu peux te demander *« cette valeur est-elle honorée ici ? »*.

## Comment

1. Imagine un conflit entre deux de tes valeurs (ex : la sécurité vs la liberté, la loyauté vs l'intégrité).
2. Dans lequel de ces conflits aurais-tu le plus de mal à « perdre » ? Celle que tu défendrais en dernier, c'est la cardinale.
3. Complète la phrase : *« Ma boussole professionnelle, c'est ___, parce que… »*
MD,
            ],

            // ===================================================================
            // BLOC 3 — LES FORCES (J9-12)
            // ===================================================================
            [
                'day'          => 9,
                'bloc'         => "Les Forces",
                'title'        => "Ce qui me vient naturellement",
                'summary'      => "Tes véritables forces sont celles que tu fais facilement — et que tu as donc cessé de remarquer.",
                'duration_min' => 15,
                'icon'         => 'seedling',
                'prompt'       => "Liste 5 choses que tu fais facilement au travail, presque sans effort — et que d'autres semblent trouver difficiles. Puis demande-toi : est-ce que je reconnais ça comme une force, ou est-ce que je le minimise en me disant 'tout le monde peut faire ça' ?",
                'body'         => <<<MD
## Pourquoi

Martin Seligman, fondateur de la psychologie positive, a montré que nos *forces caractérielles* sont celles qui nous viennent naturellement, qui nous énergisent et qui produisent des résultats. Le problème : elles sont si fluides pour nous qu'on oublie qu'elles sont rares.

« Tout le monde peut faire ça » est presque toujours faux. C'est juste que *toi* tu le fais sans y penser.

## Comment

1. Pense aux tâches, aux interactions, aux types de problèmes où tu es régulièrement sollicité·e par les autres.
2. Pense aussi à ce que tu apprends vite, ce que tu corriges chez les autres sans qu'ils ne te le demandent.
3. Pour chaque élément listé, pose-toi honnêtement : est-ce que j'aurais tendance à dire « oui mais c'est pas si difficile » ? Si oui, c'est probablement une vraie force.
MD,
            ],

            [
                'day'          => 10,
                'bloc'         => "Les Forces",
                'title'        => "Les retours qu'on me fait toujours",
                'summary'      => "Les feedbacks récurrents sont le miroir le plus fidèle de tes forces réelles.",
                'duration_min' => 15,
                'icon'         => 'message',
                'prompt'       => "Quels compliments ou retours positifs reviennent régulièrement dans ta vie pro ? (même si tu n'y crois pas entièrement) Note-les. Puis : que disent-ils de tes forces ? Y a-t-il un pattern — un type de contribution que les autres semblent toujours remarquer chez toi ?",
                'body'         => <<<MD
## Pourquoi

Les retours récurrents ne mentent pas. Si plusieurs personnes, dans des contextes différents, te disent la même chose — c'est que cette chose est réelle et visible. Le fait que tu n'y croies pas entièrement relève souvent du syndrome de l'imposteur, pas de la réalité.

## Comment

1. Remonte les évaluations, les entretiens annuels, les mails de remerciement, les commentaires informels que tu as reçus.
2. Note les formulations qui reviennent — même vaguement similaires.
3. Cherche le fil : est-ce qu'on te félicite pour la précision ? Pour la créativité ? Pour ta capacité à mettre les gens à l'aise ? Pour la vitesse ? Pour la fiabilité ?

Ce fil, c'est une force réelle — et probablement une valeur ajoutée transférable dans d'autres contextes.
MD,
            ],

            [
                'day'          => 11,
                'bloc'         => "Les Forces",
                'title'        => "La force cachée",
                'summary'      => "Il y a presque toujours une compétence que tu possèdes et que tu n'as pas encore nommée comme une force.",
                'duration_min' => 15,
                'icon'         => 'gem',
                'prompt'       => "Pense à quelque chose que tu fais bien et que tu aurais tendance à qualifier de 'pas vraiment une compétence professionnelle'. Pourquoi l'as-tu mis de côté ? Dans quel contexte professionnel cette capacité pourrait-elle être précieuse ? Donne-lui un nom.",
                'body'         => <<<MD
## Pourquoi

Beaucoup de compétences sont sous-valorisées parce qu'elles ne correspondent pas aux intitulés de postes classiques, qu'elles ont été acquises hors du travail, ou qu'elles semblent trop « douces » (soft skills).

Or ce sont souvent ces compétences-là qui font la différence dans un poste — et qui sont les plus difficiles à recruter.

## Comment

1. Pense à ce que tu fais bien *en dehors* du travail : organiser, écouter, transmettre, créer, réparer, convaincre, structurer, analyser, imaginer…
2. Demande-toi dans quel contexte professionnel cette capacité serait une valeur ajoutée évidente.
3. Donne-lui un nom professionnel : pas « j'écoute bien les gens » mais « écoute active et détection des besoins implicites ».
MD,
            ],

            [
                'day'          => 12,
                'bloc'         => "Les Forces",
                'title'        => "Ma contribution unique",
                'summary'      => "La combinaison de tes forces est unique. Aujourd'hui tu la formules.",
                'duration_min' => 20,
                'icon'         => 'fingerprint',
                'prompt'       => "À partir de J9, J10 et J11 : qu'est-ce que tu apportes qu'on ne trouve pas facilement ailleurs ? Formule-le en une ou deux phrases, comme si tu devais l'expliquer à un recruteur ou un client. Commence par : « Ce que j'apporte, c'est… »",
                'body'         => <<<MD
## Pourquoi

Ce n'est pas une compétence isolée qui te rend précieux·se — c'est la *combinaison* de tes forces, de ta personnalité et de ton expérience. Cette combinaison est unique, même si ses composantes semblent banales prises séparément.

Formuler ta contribution unique est l'un des exercices les plus difficiles — et les plus utiles — de ce parcours. C'est la base de ton positionnement professionnel.

## Comment

1. Relis J9, J10 et J11.
2. Cherche la combinaison : si tu devais choisir 2 ou 3 forces qui, ensemble, créent quelque chose de distinct, lesquelles choisirais-tu ?
3. Formule une phrase d'une ou deux lignes, concrète, sans jargon, qui décrit ce que tu rends possible pour les autres.

*Note : cette formulation va évoluer. C'est normal. L'important est d'avoir une première version.*
MD,
            ],

            // ===================================================================
            // BLOC 4 — LES ANGLES MORTS (J13-16)
            // ===================================================================
            [
                'day'          => 13,
                'bloc'         => "Les Angles morts",
                'title'        => "Ce que j'évite",
                'summary'      => "Ce qu'on évite révèle autant sur soi que ce qu'on cherche.",
                'duration_min' => 15,
                'icon'         => 'eye-off',
                'prompt'       => "Liste 3 à 5 situations, types de tâches ou d'interactions que tu repousses régulièrement ou que tu cherches à éviter dans ton travail. Pour chacun : qu'est-ce que tu te dis pour justifier cet évitement ? Qu'est-ce que tu risques vraiment en les affrontant ?",
                'body'         => <<<MD
## Pourquoi

L'évitement est l'un des mécanismes les plus courants en psychologie. Il fonctionne à court terme — il réduit l'inconfort — mais à long terme il rétrécit notre champ d'action et renforce nos zones d'ombre.

Repérer ce qu'on évite n'est pas pour se forcer à le faire. C'est pour comprendre ce que ça dit de nos peurs, de nos croyances, de notre zone de confort — et décider en conscience si cette limite nous protège vraiment ou si elle nous emprisonne.

## Comment

1. Pense aux emails qu'on laisse en attente, aux conversations qu'on reporte, aux types de missions qu'on décline, aux situations qu'on contourne.
2. Pour chacun, note la justification habituelle (« je n'ai pas le temps », « c'est pas mon rôle »…)
3. Pose-toi : si la justification était levée, qu'est-ce qui resterait comme inconfort ?
MD,
            ],

            [
                'day'          => 14,
                'bloc'         => "Les Angles morts",
                'title'        => "Le feedback qui m'a piqué",
                'summary'      => "Le feedback qui nous a le plus agacé·e est souvent celui qui contenait le plus de vérité.",
                'duration_min' => 15,
                'icon'         => 'needle',
                'prompt'       => "Rappelle-toi un feedback négatif ou une critique que tu as reçu·e et qui t'a irrité·e, blessé·e ou que tu as eu envie de rejeter. Qu'est-ce qu'il disait ? Aujourd'hui, avec du recul, quelle part de vérité y avait-il ? Qu'est-ce que ça dit de toi ?",
                'body'         => <<<MD
## Pourquoi

La réaction émotionnelle forte face à un feedback — irritation, rejet, blessure — est souvent un signal que quelque chose a touché un point sensible réel. Ce n'est pas toujours le cas, mais c'est suffisamment fréquent pour mériter qu'on s'y arrête.

Carl Jung appelait ça l'ombre : les parties de nous-mêmes que nous n'avons pas encore intégrées. Ce que nous rejetons chez les autres ou dans les retours qu'on nous fait pointe souvent vers quelque chose que nous ne voulons pas voir.

## Comment

1. Identifie un feedback spécifique — pas une impression générale, mais une phrase ou un commentaire précis.
2. Rappelle-toi ta réaction émotionnelle du moment.
3. Pose-toi la question honnêtement : si ce feedback contenait 20 % de vérité, quel serait ce 20 % ?

*Cet exercice demande du courage. Il ne s'agit pas de valider toute critique — certaines sont injustes. Il s'agit de ne pas rejeter en bloc ce qui est inconfortable.*
MD,
            ],

            [
                'day'          => 15,
                'bloc'         => "Les Angles morts",
                'title'        => "Mon saboteur principal",
                'summary'      => "Nous avons tous une croyance limitante récurrente. La nommer, c'est déjà commencer à s'en défaire.",
                'duration_min' => 15,
                'icon'         => 'ghost',
                'prompt'       => "Quelle pensée sur toi-même revient le plus souvent pour te freiner dans ta vie pro ? (« Je ne suis pas assez... », « Les autres sont meilleurs que moi en... », « Je n'ai pas le profil pour... »). Note-la précisément. Puis : d'où vient-elle ? Quelles preuves as-tu que c'est *vrai* ? Quelles preuves as-tu que c'est *faux* ?",
                'body'         => <<<MD
## Pourquoi

Les croyances limitantes ne sont pas des vérités — ce sont des hypothèses que nous avons construites à partir d'expériences passées et que nous avons finies par confondre avec la réalité. La bonne nouvelle : une croyance construite peut être déconstruite.

La première étape est de la rendre visible. Une croyance qu'on n'a jamais formulée explicitement est d'autant plus puissante qu'elle opère dans l'ombre.

## Comment

1. Pense aux moments où tu t'es bridé·e, où tu n'as pas postulé, où tu n'as pas pris la parole — quelle phrase intérieure accompagnait ces moments ?
2. Formule-la clairement, sans l'adoucir.
3. Fais ensuite le bilan des preuves : qu'est-ce qui, concrètement, prouve que c'est vrai ? Et qu'est-ce qui prouve que c'est faux ou partiel ?
MD,
            ],

            [
                'day'          => 16,
                'bloc'         => "Les Angles morts",
                'title'        => "La peur derrière la peur",
                'summary'      => "La vraie peur est rarement celle qu'on exprime. Descendre d'un niveau, c'est trouver quelque chose d'utile.",
                'duration_min' => 20,
                'icon'         => 'layers',
                'prompt'       => "Reprends ton saboteur principal (J15). Maintenant pose-toi : si cette croyance était vraie, qu'est-ce qui se passerait ? Et si ça arrivait, qu'est-ce qui se passerait ensuite ? Continue 3 fois. Qu'est-ce que tu découvres au fond ?",
                'body'         => <<<MD
## Pourquoi

La technique de la « flèche descendante » (Burns) consiste à suivre une peur jusqu'à sa racine en posant la question « et alors ? » à chaque étape. On découvre souvent que la vraie peur est plus profonde — et plus gérable — que celle qu'on croyait avoir.

Exemple : « Je n'ai pas le bon profil » → *si c'était vrai ?* → « Je ne serais pas recruté » → *et alors ?* → « Je resterais dans ma situation actuelle » → *et alors ?* → « Je ne progresserais jamais » → *et ce qui fait si peur là-dedans ?* → « Ne jamais être reconnu à ma juste valeur. »

Là, on tient quelque chose de réel à travailler.

## Comment

1. Reprends ta croyance limitante de J15.
2. Pose la question « et si c'était vrai, qu'est-ce qui se passerait ? » au moins 3 fois.
3. À chaque niveau, remarque comment ta réponse évolue. La réponse du dernier niveau est souvent la peur fondamentale.
MD,
            ],

            // ===================================================================
            // BLOC 5 — LE NARRATIF (J17-20)
            // ===================================================================
            [
                'day'          => 17,
                'bloc'         => "Le Narratif",
                'title'        => "Ma ligne du temps — les pivots",
                'summary'      => "Ton parcours n'est pas une liste de postes. C'est une histoire avec des tournants.",
                'duration_min' => 20,
                'icon'         => 'timeline',
                'prompt'       => "Dessine (ou liste) les 5 à 7 moments pivots de ta vie professionnelle : les moments où quelque chose a changé — une décision, une rencontre, un événement, une prise de conscience. Pour chaque pivot : qu'est-ce qui a changé ? Qu'as-tu appris ou gagné ?",
                'body'         => <<<MD
## Pourquoi

La psychologie narrative (White & Epston) montre que nous sommes des êtres qui construisent du sens à travers les histoires. La façon dont tu racontes ton parcours dit autant sur toi que le parcours lui-même.

Identifier les pivots permet de ne plus voir son CV comme une liste de postes mais comme une trajectoire — avec une logique, des apprentissages, une direction possible.

## Comment

1. Prends une feuille et trace une ligne horizontale. Place-y les étapes clés de ton parcours pro (y compris les formations, les périodes de transition).
2. Identifie les moments où quelque chose a *changé* : pas nécessairement les promotions ou succès — parfois c'est un échec, une démission, une rencontre.
3. Pour chaque pivot, note en une ligne : **ce qui s'est passé** et **ce que ça t'a apporté** (même si c'était douloureux au moment).
MD,
            ],

            [
                'day'          => 18,
                'bloc'         => "Le Narratif",
                'title'        => "Le fil rouge",
                'summary'      => "Il y a un thème qui relie tous tes postes, projets et pivots. Aujourd'hui tu le trouves.",
                'duration_min' => 15,
                'icon'         => 'thread',
                'prompt'       => "Regarde ta ligne du temps (J17). Qu'est-ce qui relie ces différentes expériences — pas en termes de secteur ou de titre, mais en termes de ce que tu faisais, de ce qui t'animait, de la valeur que tu créais ? Formule ce fil rouge en une ou deux phrases.",
                'body'         => <<<MD
## Pourquoi

Même les parcours qui semblent « en zigzag » ont un fil rouge — une constante dans ce qui animait la personne, dans le type de problèmes qu'elle résolvait, dans la façon dont elle contribuait. Ce fil rouge est souvent invisible à celui qui le vit, et évident pour un observateur extérieur.

Le trouver permet de raconter son parcours avec cohérence — y compris quand il est atypique.

## Comment

1. Relis J17. Cherche ce qui est *constant* malgré les changements de contexte.
2. Pose-toi : quel type de problème ai-je résolu encore et encore ? Quel type de personne ou d'organisation ai-je servi ? Qu'est-ce que j'ai toujours voulu créer ou améliorer ?
3. Formule le fil rouge sans te contraindre à un secteur ou un titre.

*Exemple : « Quel que soit le poste, j'ai toujours aidé des équipes à mieux communiquer dans des situations complexes. »*
MD,
            ],

            [
                'day'          => 19,
                'bloc'         => "Le Narratif",
                'title'        => "Mon histoire en 3 phrases",
                'summary'      => "Savoir se raconter en quelques phrases est l'une des compétences les plus rares — et les plus puissantes.",
                'duration_min' => 20,
                'icon'         => 'book-open',
                'prompt'       => "Écris un pitch de ton identité professionnelle en 3 phrases : (1) Qui tu es et d'où tu viens (le contexte), (2) Ce que tu fais ou apportes (le fil rouge + la contribution unique), (3) Ce vers quoi tu te diriges (la direction). Pas un CV. Une histoire.",
                'body'         => <<<MD
## Pourquoi

La question « parle-moi de toi » est redoutée parce qu'on n'a pas de réponse préparée qui soit à la fois vraie et cohérente. Pourtant, c'est la porte d'entrée de toute relation professionnelle.

Ce pitch n'est pas un slogan marketing. C'est une histoire vraie, courte, qui donne envie d'en savoir plus.

## Comment

**Phrase 1 — Le contexte :** D'où tu viens, quel est ton background, sans liste exhaustive. Une phrase qui situe.

**Phrase 2 — La contribution :** Ce que tu fais vraiment (ton fil rouge + ta contribution unique de J12). Concret, sans jargon.

**Phrase 3 — La direction :** Vers quoi tu te tournes, ce qui t'intéresse maintenant. Sans être trop précis si tu ne le sais pas encore — l'honnêteté sur ta recherche est plus convaincante que la fausse certitude.

*Ce pitch va évoluer avec J20. Garde cette première version.*
MD,
            ],

            [
                'day'          => 20,
                'bloc'         => "Le Narratif",
                'title'        => "Le prochain chapitre",
                'summary'      => "Ton histoire a une direction. Aujourd'hui tu lui donnes une forme.",
                'duration_min' => 15,
                'icon'         => 'book-plus',
                'prompt'       => "Si ton parcours était un roman, comment s'appellerait le prochain chapitre ? Décris-le en 5 à 10 lignes : ce qui se passe dans ce chapitre, ce que le personnage (toi) cherche, ce qu'il doit traverser pour y arriver.",
                'body'         => <<<MD
## Pourquoi

La métaphore narrative aide à projeter sans se sentir engagé à l'irréversible. Quand on écrit un « chapitre », on se donne la permission d'imaginer sans que ce soit définitif — ce qui libère des projections plus authentiques que si on posait la question directement « qu'est-ce que tu veux faire de ta vie ? »

## Comment

1. Donne un titre à ce prochain chapitre. Laisse venir ce qui vient — il ne faut pas que ce soit parfait.
2. Décris en prose libre ce qui se passe dans ce chapitre : quel type de contexte, quelles personnes, quel type de problèmes, quel sentiment dominant.
3. Note aussi ce que le personnage doit surmonter pour écrire ce chapitre.

*Cette projection sera utile dès le bloc suivant — et jusqu'à la fin du parcours.*
MD,
            ],

            // ===================================================================
            // BLOC 6 — LA CONFIANCE (J21-24)
            // ===================================================================
            [
                'day'          => 21,
                'bloc'         => "La Confiance",
                'title'        => "La liste des preuves",
                'summary'      => "La confiance en soi se construit sur des preuves réelles — pas sur des affirmations.",
                'duration_min' => 15,
                'icon'         => 'list-check',
                'prompt'       => "Écris 10 choses concrètes que tu as réussies dans ta vie pro — à n'importe quelle échelle. Pas des qualités (« je suis organisé·e »), des faits (« j'ai géré seul·e X », « j'ai appris Y en Z semaines », « j'ai résolu le problème de... »). Dix, même si tu dois chercher.",
                'body'         => <<<MD
## Pourquoi

Le syndrome de l'imposteur se nourrit du vide. Quand on ne recense pas ses réussites, le cerveau opère un biais de négativité naturel : il retient mieux les échecs et oublie les succès. Construire une liste de preuves concrètes contre-balance ce biais.

Cette liste n'est pas pour se vanter. C'est une base de données objective à laquelle revenir quand la confiance flanche.

## Comment

1. Rappelle-toi de projets menés, de problèmes résolus, de compétences acquises, de situations difficiles surmontées.
2. Formule chaque élément comme un fait observable : pas « j'ai bien géré le projet X » mais « j'ai coordonné 4 prestataires pour livrer le projet X dans les délais malgré une réduction de budget de 20 % ».
3. Si tu bloques à 10, cherche dans ta vie de formation, tes engagements associatifs, tes projets personnels.
MD,
            ],

            [
                'day'          => 22,
                'bloc'         => "La Confiance",
                'title'        => "L'imposteur prend la parole",
                'summary'      => "La meilleure façon de démanteler une voix intérieure sabotrice est de la rendre visible — et de lui répondre.",
                'duration_min' => 20,
                'icon'         => 'mask',
                'prompt'       => "Laisse ton imposteur intérieur s'exprimer : écris ce qu'il dirait pour justifier que tu n'es pas légitime dans ta prochaine étape. Puis réponds-lui point par point, en t'appuyant sur tes preuves (J21), tes forces (J9-12) et tes valeurs (J5-8).",
                'body'         => <<<MD
## Pourquoi

Pauline Clance et Suzanne Imes, qui ont décrit le syndrome de l'imposteur en 1978, ont montré que ce phénomène touche en particulier les personnes les plus compétentes — parce qu'elles sont assez conscientes d'elles-mêmes pour voir leurs limites.

La stratégie la plus efficace pour le désamorcer n'est pas de nier la voix — c'est de lui répondre avec des faits.

## Comment

1. **Colonne gauche :** Laisse l'imposteur s'exprimer librement. Qu'est-ce qu'il dit pour te freiner ? Quels arguments donne-t-il ?
2. **Colonne droite :** Réponds à chaque argument avec des éléments concrets issus des jours précédents.
3. À la fin, relis les deux colonnes. Laquelle tient mieux la comparaison ?
MD,
            ],

            [
                'day'          => 23,
                'bloc'         => "La Confiance",
                'title'        => "La permission",
                'summary'      => "Beaucoup de choses que nous n'osons pas faire attendent simplement qu'on se les autorise.",
                'duration_min' => 15,
                'icon'         => 'key',
                'prompt'       => "De quoi as-tu besoin de te donner la permission dans ta vie professionnelle ? Complète la phrase : « Je me donne la permission de... » autant de fois que nécessaire. Puis : qu'est-ce qui t'a empêché·e de te donner cette permission jusqu'ici ?",
                'body'         => <<<MD
## Pourquoi

Beaucoup de transitions professionnelles sont bloquées non par des obstacles externes mais par une absence de permission intérieure. On attend d'être « assez bon·ne », d'avoir la « bonne » expérience, l'accord de quelqu'un, le bon moment — qui ne vient jamais.

Se donner la permission, c'est reconnaître que personne d'autre ne peut le faire à ta place.

## Comment

1. Complète librement : « Je me donne la permission de postuler à… », « Je me donne la permission de dire que je veux… », « Je me donne la permission de ne plus… »
2. Pour chaque permission, demande-toi : qu'attendais-tu pour te l'accorder ? Qui ou quoi devait venir en premier ?
3. Choisis la permission la plus importante pour toi en ce moment. Comment peux-tu l'honorer cette semaine ?
MD,
            ],

            [
                'day'          => 24,
                'bloc'         => "La Confiance",
                'title'        => "La lettre du moi futur",
                'summary'      => "Ton moi dans 3 ans a traversé ce que tu redoutes aujourd'hui. Il a quelque chose à te dire.",
                'duration_min' => 20,
                'icon'         => 'mail',
                'prompt'       => "Écris une lettre à ton moi d'aujourd'hui, de la part de ton moi dans 3 ans — celui qui a réussi sa transition ou sa transformation professionnelle. Que dit-il sur les peurs que tu as maintenant ? Quels conseils donne-t-il ? Qu'est-ce qu'il veut que tu saches ?",
                'body'         => <<<MD
## Pourquoi

La technique de la « lettre du moi futur » est utilisée en thérapie cognitive et en coaching pour créer une distanciation saine avec ses propres peurs. Le moi futur a le recul que le moi présent n'a pas encore.

Cette lettre active aussi la projection positive : elle te demande d'imaginer que tu as réussi, et de décrire comment depuis l'intérieur. C'est un exercice puissant de visualisation constructive.

## Comment

1. Installe-toi confortablement. Ferme les yeux quelques instants et imagine-toi dans 3 ans : tu as traversé cette période, tu es arrivé·e quelque part qui a du sens pour toi.
2. Écris la lettre à la première personne du singulier, au présent ou au passé récent (ton moi futur parle de maintenant comme d'un « avant »).
3. Laisse venir ce qui vient. Il n'y a pas de bonne ou mauvaise lettre.
MD,
            ],

            // ===================================================================
            // BLOC 7 — L'IDENTITÉ FUTURE (J25-28)
            // ===================================================================
            [
                'day'          => 25,
                'bloc'         => "L'Identité future",
                'title'        => "La journée idéale",
                'summary'      => "Décrire une journée de travail idéale révèle des préférences que les intitulés de poste ne capturent pas.",
                'duration_min' => 20,
                'icon'         => 'sun',
                'prompt'       => "Décris une journée de travail parfaite, de bout en bout : à quelle heure tu commences, où tu es, avec qui, sur quoi tu travailles, comment tu te sens à 18h. Sois précis·e et honnête — pas une journée fantasmée, mais une journée *vraiment* bonne selon tes standards.",
                'body'         => <<<MD
## Pourquoi

La journée idéale est un outil concret pour identifier des critères souvent oubliés dans la recherche d'emploi : le rythme, le niveau d'autonomie, la nature des interactions, le type de problèmes, le contexte physique. Ces critères sont aussi importants que la mission officielle.

Beaucoup de personnes choisissent un métier sans penser à ces dimensions — et se retrouvent inadaptées à l'environnement même quand le métier leur plaît.

## Comment

1. Décris la journée comme un récit, heure par heure si possible.
2. Sois précis·e sur : le lieu, les personnes présentes (ou l'absence de personnes), la nature des tâches, le niveau de structure vs liberté, le type de résultats.
3. À la fin, identifie les 5 critères non-négociables que cette journée révèle.
MD,
            ],

            [
                'day'          => 26,
                'bloc'         => "L'Identité future",
                'title'        => "Les 3 métiers qui m'intriguent",
                'summary'      => "Sans filtrer par faisabilité, quels métiers t'attirent ? L'attraction révèle quelque chose.",
                'duration_min' => 15,
                'icon'         => 'search',
                'prompt'       => "Liste 3 métiers, rôles ou types d'activité qui t'intriguent ou t'attirent — même si tu te dis 'c'est impossible pour moi', 'c'est pas réaliste', ou 'je n'ai pas le profil'. Pour chacun : qu'est-ce qui t'attire dedans exactement ? Qu'est-ce que ce métier permettrait que tu fais peu aujourd'hui ?",
                'body'         => <<<MD
## Pourquoi

L'attraction pour un métier ou un rôle est rarement aléatoire. Elle pointe vers des besoins non satisfaits, des forces sous-utilisées, des valeurs en attente d'expression. Même si le métier lui-même n'est pas accessible, ce qu'il représente est une information utile.

La question n'est pas « puis-je faire ça ? » mais « qu'est-ce que ça dit de moi que j'en aie envie ? »

## Comment

1. Autorise-toi à lister n'importe quoi, sans filtre de faisabilité.
2. Pour chaque métier, décompose l'attraction : est-ce la liberté ? La création ? L'impact direct ? Le prestige ? L'apprentissage permanent ? Le contact humain ?
3. Ces éléments décomposés sont plus utiles que le métier lui-même.
MD,
            ],

            [
                'day'          => 27,
                'bloc'         => "L'Identité future",
                'title'        => "Ce que je veux qu'on dise de moi",
                'summary'      => "L'épitaphe professionnelle est l'un des exercices les plus révélateurs sur ce qui compte vraiment.",
                'duration_min' => 15,
                'icon'         => 'star',
                'prompt'       => "Dans 20 ans, lors de ton départ à la retraite ou d'une remise de prix, qu'aimerais-tu qu'on dise de toi ? Pas ce que tu penses qu'on dira — ce que tu *voudrais* qu'on dise. En quoi ce que tu fais aujourd'hui te rapproche-t-il ou t'éloigne-t-il de ça ?",
                'body'         => <<<MD
## Pourquoi

Stephen Covey, dans *Les 7 habitudes des gens très efficaces*, propose d'écrire son propre éloge funèbre comme boussole de vie. L'idée : en imaginant ce qu'on veut laisser comme trace, on révèle ce qui compte vraiment — au-delà des objectifs à court terme.

L'épitaphe professionnelle est une version moins radicale mais tout aussi efficace.

## Comment

1. Écris 3 à 5 phrases que tu aimerais entendre dans cet éloge — sur ta façon de travailler, ce que tu as apporté, comment tu as traité les gens.
2. Puis évalue honnêtement : est-ce que ma trajectoire actuelle me rapproche de ça ?
3. Si non : qu'est-ce qui devrait changer ?
MD,
            ],

            [
                'day'          => 28,
                'bloc'         => "L'Identité future",
                'title'        => "L'identité que j'assume",
                'summary'      => "L'identité professionnelle se construit aussi en la déclarant — pas seulement en l'attendant.",
                'duration_min' => 15,
                'icon'         => 'badge',
                'prompt'       => "Complète la phrase : « Je suis quelqu'un qui... » 5 fois, en lien avec ta vie professionnelle — sans attendre d'avoir prouvé, sans attendre d'être « assez ». Ces 5 phrases décrivent l'identité que tu assumes dès aujourd'hui.",
                'body'         => <<<MD
## Pourquoi

L'identité ne précède pas l'action — elle se construit avec elle. Mais elle se construit aussi par la déclaration : se définir comme « quelqu'un qui… » avant d'avoir toutes les preuves, c'est ce que font les personnes qui se transforment.

James Clear, dans *Atomic Habits*, montre que le changement durable commence par l'identité : « Je suis quelqu'un qui fait X » produit des comportements différents de « J'essaie de faire X ».

## Comment

1. Complète 5 fois : « Je suis quelqu'un qui… »
2. Formule chaque phrase au présent, comme si c'était déjà vrai (même si tu es en chemin).
3. Relis les 5 phrases. Laquelle te fait le plus peur ? Laquelle te donne le plus d'élan ?
MD,
            ],

            // ===================================================================
            // BLOC 8 — LA FORGE (J29-30)
            // ===================================================================
            [
                'day'          => 29,
                'bloc'         => "La Forge",
                'title'        => "Le portrait complet",
                'summary'      => "Tout ce que tu as découvert mérite d'être rassemblé en un portrait cohérent.",
                'duration_min' => 25,
                'icon'         => 'layout',
                'prompt'       => "En t'appuyant sur les 28 exercices précédents, rédige ton portrait professionnel complet : (1) Tes 3 valeurs cardinales et ce qu'elles signifient pour toi. (2) Tes 3 forces principales et ta contribution unique. (3) Ton principal angle mort et comment tu le travailles. (4) Ton narratif : le fil rouge + le prochain chapitre. (5) L'identité que tu assumes.",
                'body'         => <<<MD
## Pourquoi

Ce portrait est le cœur de ce que la Forge de l'Identité t'a permis de construire. Il n'est pas définitif — il évoluera avec toi. Mais c'est ta meilleure approximation actuelle de qui tu es professionnellement, et c'est une base solide pour agir.

Ce document peut servir de :
- **Guide d'orientation** quand tu évalues des opportunités
- **Base de pitch** pour des entretiens ou des rencontres professionnelles
- **Boussole** quand tu doutes ou que tu te perds dans les injonctions extérieures

## Comment

1. Reprends les exercises clés : J7-8 (valeurs), J9-12 (forces), J13-16 (angles morts), J18-20 (narratif), J28 (identité).
2. Écris chaque section sans te relire — laisse venir. Tu pourras affiner.
3. Cherche la cohérence entre les sections : est-ce que les valeurs s'accordent avec les forces ? Est-ce que le prochain chapitre honore la boussole ?
MD,
            ],

            [
                'day'          => 30,
                'bloc'         => "La Forge",
                'title'        => "La lettre à moi-même",
                'summary'      => "Ce que tu as découvert dans ce parcours mérite d'être ancré par une lettre que tu te liras dans 6 mois.",
                'duration_min' => 20,
                'icon'         => 'heart',
                'prompt'       => "Écris une lettre à toi-même, à lire dans 6 mois. Dis-toi ce que tu as découvert pendant ce parcours, ce que tu ne veux pas oublier, ce que tu veux honorer dans les prochains mois. Sois honnête, bienveillant·e, et précis·e.",
                'body'         => <<<MD
## Pourquoi

La clôture d'un parcours d'introspection est aussi importante que le travail lui-même. Sans rituel de fermeture, les insights s'évaporent, les résolutions s'effacent, et on retourne au point de départ.

Cette lettre est un ancrage. Elle te parlera dans 6 mois de ce que tu pensais, ressentais et voulais aujourd'hui. Elle te permettra de mesurer le chemin parcouru — ou de te rappeler ce à quoi tu avais décidé de te tenir.

## Comment

1. Commence par ce que tu savais avant ce parcours — et ce que tu sais maintenant.
2. Écris ce que tu veux ne pas oublier : une idée, une permission, une valeur, une force que tu avais sous-estimée.
3. Termine par ce que tu veux faire dans les 6 prochains mois — une intention concrète, pas une liste de résolutions.

*Garde cette lettre quelque part où tu pourras la relire. Dans PraxiMiroir, elle reste accessible dans ton journal.*
MD,
            ],

        ];
    }
}
