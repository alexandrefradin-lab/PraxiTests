<?php

namespace Praxis\Plugins\PraxiVision\Data;

/**
 * Catalogue des 60 pratiques de leadership intégral.
 *
 * 8 blocs progressifs :
 *   J1-7    Se connaître
 *   J8-15   Présence & énergie
 *   J16-22  Vision & sens
 *   J23-30  Influence & conviction
 *   J31-38  L'équipe comme système
 *   J39-46  Décider sous incertitude
 *   J47-54  Transformer l'organisation
 *   J55-60  Durer & transmettre
 *
 * Sources : Goleman (intelligence émotionnelle), Scharmer (Théorie U),
 * Heifetz (leadership adaptatif), Edmondson (sécurité psychologique),
 * Kahneman (biais cognitifs), Kotter (conduite du changement),
 * Greenleaf (servant leadership), Dweck (mentalité de croissance).
 */
class Practices
{
    public static function all(): array
    {
        return [

            // ===================================================================
            // BLOC 1 — SE CONNAÎTRE (J1-7)
            // ===================================================================

            [
                'day' => 1,
                'theme' => 'Se connaître',
                'title' => "Cartographie tes valeurs fondamentales",
                'summary' => "Un leader sans ancrage dans ses valeurs se laisse ballotter par les événements.",
                'micro_challenge' => "Liste 10 valeurs qui comptent pour toi, puis réduis à 3 en te demandant : « Laquelle refuserais-je de trahir même sous pression ? » Note-les et affiche-les là où tu travailles.",
                'duration_min' => 15,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Les leaders les plus efficaces ne se définissent pas par leur poste, mais par leurs valeurs. Ces valeurs servent d'étoile polaire dans les décisions difficiles — là où les règles et les procédures ne suffisent plus (Kouzes & Posner, *The Leadership Challenge*).

Connaître ses valeurs, ce n'est pas un exercice de développement personnel accessoire : c'est la condition d'une cohérence dans la durée. Sans ça, ton leadership oscillera selon les contextes et perdra la confiance de ceux que tu guides.

## Comment

1. Écris rapidement 10 valeurs qui comptent pour toi (ne filtre pas : liste d'abord, trie ensuite).
2. Regroupe les doublons sémantiques (liberté ≈ autonomie, honnêteté ≈ intégrité).
3. Pour chaque valeur, demande-toi : « Ai-je déjà payé un prix pour cette valeur ? » Les vraies valeurs ont une histoire.
4. Réduis à 3. Ces 3 sont ton noyau dur.

## Ce que cela change

Avoir ses valeurs explicites permet de :
- Décider plus vite sous pression (tu sais ce qui n'est pas négociable)
- Recruiter et déléguer avec cohérence
- Inspirer par l'exemple plutôt que par l'autorité
MD,
            ],

            [
                'day' => 2,
                'theme' => 'Se connaître',
                'title' => "Repère tes zones d'énergie et tes puits",
                'summary' => "Tout le monde a des activités qui donnent de l'élan — et d'autres qui drainent. Les connaître, c'est gérer ton carburant de leader.",
                'micro_challenge' => "Trace un tableau de ta semaine passée. Pour chaque activité majeure, note +1 si elle t'a donné de l'énergie, -1 si elle t'a drainé. Que vois-tu ? Quelle proportion de ton temps est dans le + ?",
                'duration_min' => 10,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

Le leadership est une activité à haute dépense énergétique. Les leaders qui durent ne sont pas ceux qui ont le plus d'endurance — ce sont ceux qui savent où ils puisent leur énergie et qui organisent leur travail en conséquence (Loehr & Schwartz, *The Power of Full Engagement*).

## Comment

1. Liste les 8 à 10 activités principales de ta semaine type.
2. Pour chacune, note ton niveau d'énergie **après** l'avoir faite (pas pendant — on peut être dans le flux pendant une activité épuisante).
3. Identifie tes 2 « sources » (activités qui rechargent) et tes 2 « puits » (activités qui drainent).
4. Pose-toi la question : puis-je déléguer l'un des puits ? Peut-je augmenter le temps dans l'une des sources ?

## Le piège à éviter

Confondre urgence et énergie. Une urgence peut être énergisante (rush de cortisol) tout en épuisant les réserves à long terme. Ce qui compte, c'est l'énergie **nette** sur plusieurs jours.
MD,
            ],

            [
                'day' => 3,
                'theme' => 'Se connaître',
                'title' => "Identifie tes croyances limitantes de leader",
                'summary' => "Les croyances que tu as sur toi-même dessinent silencieusement les contours de ce que tu oses faire.",
                'micro_challenge' => "Complète cette phrase 5 fois : « Un leader comme moi ne peut pas… » Puis demande-toi pour chacune : est-ce un fait observable ou une conviction non vérifiée ?",
                'duration_min' => 12,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Carol Dweck a montré que la croyance en sa propre capacité à évoluer — la *mentalité de croissance* — est l'un des meilleurs prédicteurs de réussite à long terme. Les croyances limitantes ne sont pas des vérités : ce sont des hypothèses figées qui s'auto-confirment si on ne les examine pas.

## Comment

1. Complète 5 fois : « Un leader comme moi ne peut pas… »
2. Pour chaque phrase, demande-toi :
   - Sur quoi cette croyance est-elle fondée ? (Un événement passé ? Un feedback mal digéré ?)
   - Quelle est la **preuve contraire** ? (Quand ai-je fait l'opposé ?)
   - Quelle croyance alternative me servirait mieux ?
3. Reformule chaque croyance limitante en intention apprenante : « Je n'ai pas encore appris à… »

## Ce que cela ouvre

Repérer une croyance limitante ne la fait pas disparaître immédiatement, mais elle perd son pouvoir implicite. Ce qui était une règle devient un choix conscient.
MD,
            ],

            [
                'day' => 4,
                'theme' => 'Se connaître',
                'title' => "Reconnais tes déclencheurs émotionnels",
                'summary' => "Quand tu réagis au lieu de répondre, c'est un déclencheur qui pilote à ta place.",
                'micro_challenge' => "Pense à la dernière fois où tu as réagi de façon disproportionnée (agacement, fermeture, sur-réaction). Qu'est-ce qui s'est passé juste avant ? Quel besoin non satisfait était derrière ?",
                'duration_min' => 10,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

L'intelligence émotionnelle — et en particulier la conscience de soi — est le prédicteur le plus fiable de l'efficacité du leader selon Goleman. Mais la conscience de soi ne signifie pas « contrôler ses émotions » : elle signifie les **reconnaître avant qu'elles prennent le volant**.

## Comment

1. Rappelle-toi un moment récent où ta réaction t'a surpris toi-même.
2. Identifie l'événement déclencheur (ce que quelqu'un a dit ou fait).
3. Cherche le besoin derrière l'émotion : les déclencheurs pointent presque toujours vers un besoin fondamental non satisfait (reconnaissance, équité, contrôle, appartenance…).
4. Note : « Quand X se produit, je ressens Y, parce que j'ai besoin de Z. »

## L'enjeu

Un leader qui ne connaît pas ses déclencheurs les exporte sur son équipe. Les connaître, c'est choisir comment répondre plutôt que subir ses propres réactions.
MD,
            ],

            [
                'day' => 5,
                'theme' => 'Se connaître',
                'title' => "Examine ton style d'influence naturel",
                'summary' => "Tu influences constamment — même quand tu ne le cherches pas. Connaître ton style, c'est pouvoir l'ajuster.",
                'micro_challenge' => "Demande à 3 personnes qui te connaissent bien de compléter cette phrase par mail ou à l'oral : « Quand tu veux convaincre, tu… » Compare leurs réponses à ta propre perception.",
                'duration_min' => 15,
                'icon' => 'users',
                'body' => <<<MD
## Pourquoi

L'influence est le cœur du leadership — pas l'autorité. Mais chaque leader a un style d'influence dominant qui devient un réflexe : argumenter logiquement, s'appuyer sur les émotions, utiliser le réseau, inspirer par l'exemple, créer la pression… Chaque style est efficace dans certains contextes et contre-productif dans d'autres.

## Les styles principaux

- **Rationnel** : faits, données, logique. Efficace avec des experts ; moins avec ceux qui cherchent du sens.
- **Émotionnel** : vision, récit, connexion. Mobilise ; peut sembler manipulateur si mal dosé.
- **Socio-politique** : coalitions, alliés, timing. Puissant ; peut être perçu comme calcul.
- **Exemplaire** : montrer plutôt que dire. Inspirant ; peut créer une dépendance au modèle.

## Comment l'utiliser

Ton style dominant est une force — et une limite. Les leaders les plus influents savent alterner selon l'interlocuteur et l'enjeu, plutôt que d'appliquer le même levier à tous.
MD,
            ],

            [
                'day' => 6,
                'theme' => 'Se connaître',
                'title' => "Écris ton manifeste personnel de leadership",
                'summary' => "Ce que tu crois sur le leadership détermine comment tu le pratiques. Rendre cela explicite est un acte de clarté.",
                'micro_challenge' => "En 10 minutes, complète ces 3 phrases : « Je crois qu'un leader doit… » / « Je refuse de… » / « Je veux que les personnes que je guide ressentent… » Ne te censure pas : c'est un document pour toi.",
                'duration_min' => 15,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

Un manifeste personnel n'est pas un exercice de marketing interne. C'est une clarification pour toi-même : qu'est-ce que je veux incarner comme leader ? Sans cela, ton style de leadership est la somme des habitudes héritées, des managers que tu as eus, des modèles que tu as imités — pas nécessairement ceux que tu as choisis.

## Structure proposée

1. **Ce en quoi je crois** : 3 à 5 convictions sur ce que le leadership devrait être.
2. **Ce que je refuse** : les comportements que tu t'interdis, même sous pression.
3. **L'impact que je veux avoir** : comment les personnes que tu guides doivent se sentir à long terme.

## Usage

Relis ce manifeste quand tu te trouves à un carrefour difficile. C'est un instrument de navigation, pas un contrat gravé dans le marbre : tu peux l'affiner à mesure que tu évolues.
MD,
            ],

            [
                'day' => 7,
                'theme' => 'Se connaître',
                'title' => "Cartographie tes angles morts",
                'summary' => "Ce que tu ne vois pas sur toi-même est ce qui freine le plus souvent ton leadership.",
                'micro_challenge' => "Demande à un pair ou un manager de confiance : « Quelle est la chose que je pourrais faire différemment qui aurait le plus grand impact sur mon efficacité ? » Écoute sans défendre, sans expliquer.",
                'duration_min' => 20,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

La fenêtre de Johari distingue ce que tu sais de toi de ce que les autres voient. Le quadrant « angles morts » — ce que les autres voient mais que tu ne vois pas — est la zone de croissance la plus riche et la plus inconfortable.

## Comment solliciter un feedback utile

1. Choisis quelqu'un qui te fait confiance **et** qui te dira quelque chose d'inconfortable.
2. Pose une question précise plutôt que générale : « Quelle habitude de communication gagnerais-je à changer ? »
3. Note sans commenter. L'envie de te justifier est un signe que tu touches quelque chose de vrai.
4. Remercie sans minimiser (« c'est pas si grave ») ni exagérer (« tu as tellement raison, je suis nul »).

## Le vrai courage de leadership

Ce n'est pas de prendre des décisions difficiles. C'est d'accepter un miroir honnête — et d'agir sur ce qu'on y voit.
MD,
            ],

            // ===================================================================
            // BLOC 2 — PRÉSENCE & ÉNERGIE (J8-15)
            // ===================================================================

            [
                'day' => 8,
                'theme' => 'Présence & énergie',
                'title' => "Pratique la présence totale en réunion",
                'summary' => "Être physiquement présent et mentalement ailleurs est l'une des façons les plus visibles de manquer de respect à son équipe.",
                'micro_challenge' => "Lors de ta prochaine réunion, pose ton téléphone face cachée, ferme les autres onglets, et pour chaque intervention, reformule ce que tu as compris avant de répondre.",
                'duration_min' => 10,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

La présence — la qualité d'attention que tu apportes à un moment donné — est l'une des ressources les plus rares et les plus valorisées par les équipes. Des recherches en neurosciences montrent que l'esprit humain est « vagabond » (mind wandering) près de 47 % du temps (Killingsworth & Gilbert, 2010). Chez un leader, cela se voit.

## Ce que la présence change

- Elle signale à l'autre qu'il compte.
- Elle améliore la qualité de la décision (tu entends vraiment les nuances).
- Elle crée un climat de sécurité psychologique : les gens osent dire des choses difficiles quand ils sentent qu'ils sont vraiment entendus.

## Technique simple

Avant d'entrer dans une réunion, prends 60 secondes pour noter : **Qu'est-ce que je veux apporter à cette réunion ?** Cette question ramène l'intention au présent.
MD,
            ],

            [
                'day' => 9,
                'theme' => 'Présence & énergie',
                'title' => "Gère ton énergie, pas seulement ton temps",
                'summary' => "Le temps est une ressource fixe. L'énergie, elle, peut être renouvelée — si tu sais comment.",
                'micro_challenge' => "Identifie ton pic d'énergie cognitif dans la journée (matin ? fin de matinée ?). Bloque ce créneau pour une tâche de leadership exigeante (conversation difficile, décision stratégique). Repousse les tâches administratives en dehors.",
                'duration_min' => 10,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

Gérer son agenda en unités de temps sans tenir compte de l'énergie, c'est comme conduire en ne regardant que le compteur de vitesse — en ignorant le niveau de carburant. Loehr & Schwartz ont montré que les performers de haut niveau optimisent leurs cycles d'effort et de récupération, pas juste leur temps de travail.

## Les 4 dimensions de l'énergie

1. **Physique** : sommeil, mouvement, alimentation.
2. **Émotionnelle** : qualité des relations, ressenti au travail.
3. **Mentale** : focus, complexité cognitive supportée.
4. **Spirituelle** (au sens large) : alignement avec ses valeurs, sens du travail.

## Action immédiate

Identifie la tâche de leadership qui exige le plus de toi et programme-la systématiquement à ton pic d'énergie. Déplace une seule réunion administrative pour libérer ce créneau cette semaine.
MD,
            ],

            [
                'day' => 10,
                'theme' => 'Présence & énergie',
                'title' => "Maîtrise ta présence physique",
                'summary' => "Avant même d'avoir dit un mot, ton corps a déjà communiqué un message. Le leadership commence dans la posture.",
                'micro_challenge' => "Filme-toi 2 minutes en train de parler (téléphone posé sur un bureau). Regarde sans son d'abord : quelle énergie ton corps transmet-il ? Réajuste un seul élément (regard, posture, gestes) pour la journée.",
                'duration_min' => 12,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Albert Mehrabian a popularisé l'idée que 55 % du message passe par le non-verbal. Si le chiffre est discutable hors de son contexte, le principe reste solide : la présence physique d'un leader **amplifie ou contredit** ses mots. Un leader qui dit « je vous fais confiance » en croisant les bras et en regardant l'heure envoie un message contradictoire.

## Les signaux à calibrer

- **Regard** : le contact visuel distribué dans le groupe (pas fixé sur une seule personne, ni fuyant).
- **Posture** : ancré, sans rigidité — ni effondrement ni sur-tension.
- **Voix** : le rythme ralenti par rapport à l'état de stress naturel.
- **Espace** : ni envahissant ni absent — à portée, disponible.

## L'objectif

Non pas performer une présence, mais **s'ancrer** pour être réellement là. Le corps suit l'intention intérieure.
MD,
            ],

            [
                'day' => 11,
                'theme' => 'Présence & énergie',
                'title' => "Pratique l'écoute de niveau 3",
                'summary' => "Il existe plusieurs niveaux d'écoute. La plupart des gens s'arrêtent au niveau 1. Le leadership commence au niveau 3.",
                'micro_challenge' => "Dans ta prochaine conversation à fort enjeu, pratique la règle du 70/30 : l'autre parle 70 % du temps, toi 30 %. Compte mentalement le nombre de fois où tu as envie de couper la parole — et ne le fais pas.",
                'duration_min' => 10,
                'icon' => 'ear',
                'body' => <<<MD
## Les 3 niveaux d'écoute (Co-Active Coaching)

- **Niveau 1** : tu écoutes en filtrant par rapport à toi — tu penses déjà à ce que tu vas répondre.
- **Niveau 2** : tu écoutes vraiment l'autre — les mots, le ton, l'émotion derrière les mots.
- **Niveau 3** : tu écoutes le champ — ce qui est dit, ce qui ne l'est pas, la dynamique du moment.

## Pourquoi c'est un acte de leadership

L'écoute profonde signale à l'autre qu'il est en sécurité pour dire quelque chose d'important. Elle révèle des informations que tu n'aurais jamais obtenues en parlant. Et elle crée une relation de confiance que aucun discours ne peut créer aussi vite.

## Technique d'ancrage

Avant de répondre, résume ce que tu as entendu : « Ce que je comprends, c'est… » Ça t'oblige à vraiment écouter pour pouvoir résumer.
MD,
            ],

            [
                'day' => 12,
                'theme' => 'Présence & énergie',
                'title' => "Installe un rituel de récupération active",
                'summary' => "La récupération n'est pas l'absence d'effort : c'est un acte intentionnel. Les leaders qui durent le savent.",
                'micro_challenge' => "Conçois un rituel de fin de journée de 5 minutes qui marque la transition entre le rôle de leader et le reste de ta vie. Ce peut être une marche, noter 3 choses accomplies, ou un geste symbolique. Teste-le ce soir.",
                'duration_min' => 10,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Les neurosciences du sport ont montré que c'est pendant la récupération — pas pendant l'effort — que la performance se consolide. Le même principe s'applique au leadership cognitif et émotionnel. Sans récupération intentionnelle, les réserves s'épuisent progressivement, souvent sans signal d'alarme visible jusqu'au burnout.

## Types de récupération

1. **Physique** : sommeil, mouvement, respiration.
2. **Sociale** : interactions non liées au travail, liens chaleureux.
3. **Cognitive** : activités sans objectif de performance (marche, lecture de fiction, créativité).
4. **Émotionnelle** : nommer et lâcher les émotions de la journée plutôt que les ruminer.

## Le rituel de coupure

La transition délibérée entre le « mode leader » et le reste de la vie n'est pas un luxe : c'est une maintenance. Sans elle, le cerveau reste en mode vigilance et l'énergie disponible pour le lendemain diminue.
MD,
            ],

            [
                'day' => 13,
                'theme' => 'Présence & énergie',
                'title' => "Identifie tes parasites cognitifs",
                'summary' => "Certaines pensées récurrentes consomment ton énergie mentale en arrière-plan, comme des applications ouvertes sur un téléphone.",
                'micro_challenge' => "Pendant 5 minutes, note tout ce qui tourne en arrière-plan dans ta tête (conflits non résolus, décisions en suspens, conversations évitées). Classe chaque item : action (je peux agir), acceptation (je dois lâcher), ou attente (ça se résoudra sans moi).",
                'duration_min' => 12,
                'icon' => 'lightbulb',
                'body' => <<<MD
## Pourquoi

David Allen (Getting Things Done) a identifié un phénomène clé : les « boucles ouvertes » — les problèmes non résolus, décisions en suspens, engagements non tenus — mobilisent une part de l'attention même quand on ne les pense pas consciemment. Elles drainent le RAM cognitif.

## Comment les traiter

- **Item actionnable** : prends une décision ou programme une action concrète, puis sort-le de la tête.
- **Item à accepter** : c'est hors de ton contrôle. Formuler explicitement « je ne peux pas changer ça » libère de l'énergie.
- **Item en attente** : note-le dans un système externe (carnet, app). Le cerveau peut alors « lâcher » sans craindre de l'oublier.

## L'effet immédiat

Vider sa tête une fois par semaine (ou par jour pour les leaders à haute charge) libère de l'espace pour la créativité, la présence et les décisions de qualité.
MD,
            ],

            [
                'day' => 14,
                'theme' => 'Présence & énergie',
                'title' => "Développe ta résilience émotionnelle face à la critique",
                'summary' => "Recevoir une critique sans s'effondrer ni se fermer est une compétence — pas un trait de caractère inné.",
                'micro_challenge' => "Rappelle-toi une critique récente qui t'a touché. Sépare : (a) le fait observable, (b) l'interprétation de l'autre, (c) ton émotion, (d) ce qui est vrai dans le fond. Qu'est-ce que tu peux utiliser ?",
                'duration_min' => 12,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Les leaders sont constamment évalués — formellement et informellement. Ceux qui ne peuvent pas recevoir de feedback négatif sans réagir défensivement envoient un signal clair à leur équipe : « Ne me dites pas ce qui ne va pas. » Résultat : le leader opère dans une bulle de confirmation.

## Le modèle SBI (Situation-Behavior-Impact)

Pour déconstruire une critique :
1. **Situation** : dans quel contexte la critique porte-t-elle ?
2. **Comportement** : quel comportement précis est-il visé ? (Pas ta personnalité — un acte.)
3. **Impact** : quel effet a eu ce comportement, selon l'autre ?

## La question clé

Après avoir reçu une critique difficile, demande-toi : « Y a-t-il 10 % de vrai là-dedans que je peux utiliser ? » Ce cadrage ouvre sans imposer de capitulation.
MD,
            ],

            [
                'day' => 15,
                'theme' => 'Présence & énergie',
                'title' => "Tiens ta présence sous pression",
                'summary' => "N'importe qui peut être un bon leader quand tout va bien. La vraie compétence, c'est de rester ancré quand ça tourne mal.",
                'micro_challenge' => "La prochaine fois que tu sens la pression monter (réunion tendue, mauvaise nouvelle, conflit), applique le 4-7-8 : inspire 4 secondes, retiens 7, expire 8. Fais-le une fois avant de répondre. Observe ce qui change.",
                'duration_min' => 10,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Sous pression, l'amygdale prend le relais et réduit l'accès au cortex préfrontal — la zone de la pensée nuancée, de l'empathie et de la décision rationnelle. Ce « détournement amygdalien » (Goleman) est involontaire, mais ses effets sur le leadership sont concrets : ton ton change, tes mots perdent en nuance, tu fermes les options.

## L'ancrage physiologique

La respiration est le levier le plus rapide pour réguler le système nerveux autonome. Ralentir l'expiration active le nerf vague et déclenche la réponse parasympathique (calme). Cela ne prend que 20 à 30 secondes.

## Ce que tu projettes

Un leader qui reste calme sous pression ne transmet pas que de la sérénité : il transmet la croyance implicite que **la situation est gérable**. C'est l'un des signaux les plus puissants du leadership en crise.
MD,
            ],

            // ===================================================================
            // BLOC 3 — VISION & SENS (J16-22)
            // ===================================================================

            [
                'day' => 16,
                'theme' => 'Vision & sens',
                'title' => "Formule ta vision en une image mémorable",
                'summary' => "Une vision abstraite ne mobilise personne. Une image concrète s'ancre dans les esprits et guide les décisions.",
                'micro_challenge' => "En une phrase, décris ce à quoi ressemblera le succès de ton équipe ou projet dans 3 ans — comme si tu décrivais une scène à quelqu'un qui la voit. Pas d'objectifs chiffrés : une image vivante.",
                'duration_min' => 15,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Martin Luther King n'a pas dit « I have a plan ». Les visions qui mobilisent sont celles qui permettent à l'autre de se **voir** dans un futur différent. Le cerveau traite les images concrètes beaucoup plus efficacement que les objectifs abstraits (dual coding theory, Paivio).

## Ce qui fait une vision mémorable

1. **Concrète** : on peut la visualiser, pas seulement la comprendre.
2. **Désirable** : elle suscite une envie, pas seulement une obligation.
3. **Partageable** : elle tient en une phrase que quelqu'un peut répéter à quelqu'un d'autre.
4. **Ambitieuse mais crédible** : elle étire sans décourager.

## Exercice de formulation

Complète : « Dans 3 ans, quand des gens parleront de notre équipe, ils diront : "Ces gens-là, ils ont réussi à…" » La réponse est souvent le début de ta vision.
MD,
            ],

            [
                'day' => 17,
                'theme' => 'Vision & sens',
                'title' => "Relie chaque tâche au sens plus grand",
                'summary' => "Les gens ne se mobilisent pas pour des tâches. Ils se mobilisent pour des causes. Le rôle du leader est de faire le lien.",
                'micro_challenge' => "Choisit une tâche récurrente que ton équipe trouve rébarbative. Écris la chaîne de sens : cette tâche → permet quoi → pour qui → avec quel impact ultime. Partage cette chaîne en réunion.",
                'duration_min' => 10,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Adam Grant a montré dans ses recherches à Wharton que les téléopérateurs d'un centre d'appels travaillant pour une université donnaient 142 % plus de temps à leurs appels et collectaient 171 % plus d'argent après avoir rencontré un bénéficiaire réel de leurs efforts. Le sens n'est pas un bonus motivationnel : c'est un carburant.

## La chaîne de sens

Toute tâche peut être reliée à un impact plus grand. Le leader qui sait faire cette connexion de façon authentique — pas comme une manipulation mais comme une réalité — change la qualité de l'engagement.

**Exemple** :
Remplir les rapports hebdomadaires → permet à la direction de voir les tendances → ce qui évite des décisions mal informées → ce qui protège les emplois et les projets de l'équipe.

## Le mot clé : authentique

La connexion au sens ne fonctionne que si tu y crois toi-même. Si tu ne vois pas le sens, commence par te poser la question honnêtement.
MD,
            ],

            [
                'day' => 18,
                'theme' => 'Vision & sens',
                'title' => "Diagnostique le gap entre vision et réalité",
                'summary' => "La tension entre où tu veux aller et où tu en es est le moteur du changement — à condition de la voir clairement.",
                'micro_challenge' => "Note sur 10 l'alignement actuel de ton équipe avec ta vision (1 = très loin, 10 = pleinement réalisée). Puis identifie le seul facteur qui, s'il changeait, ferait passer ce score de +2 points.",
                'duration_min' => 12,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

Peter Senge a introduit le concept de « tension créatrice » : l'écart entre la vision (où on veut aller) et la réalité actuelle (où on est) génère une énergie qui tire vers l'avant — à condition de ne pas réduire la tension en abaissant la vision plutôt qu'en élevant la réalité.

## Les deux façons de réduire la tension

1. **Élever la réalité** vers la vision → croissance, changement, leadership.
2. **Abaisser la vision** vers la réalité → confort, stagnation, résignation.

Les leaders qui choisissent la première option maintiennent une tension productive. Ceux qui choisissent la seconde sans s'en rendre compte perdent progressivement leur boussole.

## Comment utiliser ce diagnostic

Une fois le gap identifié, ne te noie pas dans la liste des facteurs. Le **facteur levier** — celui qui a le plus d'effet sur les autres — mérite toute ton attention cette semaine.
MD,
            ],

            [
                'day' => 19,
                'theme' => 'Vision & sens',
                'title' => "Parle à l'imagination, pas seulement à la raison",
                'summary' => "Les arguments convaincent. Les histoires engagent. Un leader qui ne sait que présenter des données mobilise peu.",
                'micro_challenge' => "Pense à une décision ou un projet à venir. Construis un récit en 3 temps : situation actuelle (le problème vécu), moment de bascule (ce qui rend le changement possible), futur possible (à quoi ressemble le succès). Teste-le à l'oral devant un miroir ou un proche.",
                'duration_min' => 15,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

Le cerveau humain est câblé pour les histoires. Les données activent les zones du langage et du traitement logique. Les récits activent également le cortex sensoriel, moteur et émotionnel — comme si on vivait l'histoire (Uri Hasson, Princeton). Une histoire bien construite ne fait pas que convaincre : elle fait **ressentir**.

## La structure narrative minimale

1. **Situation** : le contexte et le problème (ce que les gens reconnaissent).
2. **Tension** : ce qui rend le statu quo intenable ou une opportunité imminente.
3. **Résolution possible** : la direction que tu proposes, incarnée dans une image concrète.

## Ce que cela n'est pas

Storytelling ne signifie pas inventer ou manipuler. Les meilleures histoires de leadership sont vraies — elles extraient du sens d'une expérience réelle et le rendent transmissible.
MD,
            ],

            [
                'day' => 20,
                'theme' => 'Vision & sens',
                'title' => "Transforme un obstacle en frontière créatrice",
                'summary' => "Les contraintes que tu subis peuvent devenir des forces si tu changes ta relation à elles.",
                'micro_challenge' => "Identifie une contrainte majeure que tu ne peux pas changer (budget, délai, décision externe). Pose-toi la question : si cette contrainte était permanente, quelle approche créative cela ouvrirait-il que je n'aurais jamais considérée ?",
                'duration_min' => 12,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

La recherche en créativité (Catrinel Haught-Tromp, Patricia Stokes) montre que les contraintes, loin de bloquer l'innovation, la stimulent souvent. Elles forcent à chercher des solutions hors des schémas habituels. Les meilleurs leaders ne passent pas leur temps à se battre contre les frontières — ils les utilisent.

## Le recadrage

Au lieu de « je n'ai pas les ressources pour faire X », demande : « Avec exactement ce que j'ai, quelle est la version la plus puissante de ce que je peux créer ? »

Ce n'est pas de la résignation — c'est de l'ingéniosité stratégique.

## Application

Liste 3 contraintes actuelles. Pour chacune, identifie une solution que tu n'aurais pas envisagée sans cette contrainte. La contrainte devient un moteur de créativité plutôt qu'un frein à la vision.
MD,
            ],

            [
                'day' => 21,
                'theme' => 'Vision & sens',
                'title' => "Construis un récit de transformation",
                'summary' => "Le changement que tu portes a besoin d'un récit — sinon, d'autres récits (résistance, peur, rumeur) rempliront le vide.",
                'micro_challenge' => "Pour un changement en cours dans ton équipe ou ton organisation, écris le récit en 4 phrases : d'où on vient, pourquoi on ne peut pas rester là, où on va, ce que chacun a à y gagner. Teste-le à haute voix.",
                'duration_min' => 15,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

John Kotter a identifié que l'une des premières causes d'échec du changement est l'absence d'une vision communiquée avec suffisamment de clarté et de répétition. Les gens ne résistent pas au changement : ils résistent à la perte de sens, à l'incertitude, à l'absence de perspective claire sur leur place dans le nouveau monde.

## Les 4 éléments du récit de transformation

1. **D'où on vient** : ancrer dans une réalité partagée et reconnue.
2. **Pourquoi on ne peut pas rester là** : la tension (interne ou externe) qui rend le statu quo intenable.
3. **Où on va** : la direction, concrète et imageable.
4. **Ce que chacun y gagne** : WIIFM (What's In It For Me) — la question que tout le monde se pose et que peu de leaders répondent explicitement.

## Répétition

Un bon récit de transformation doit être dit 7 fois avant d'être entendu une fois. Ce n'est pas de la manipulation : c'est la réalité de l'attention humaine.
MD,
            ],

            [
                'day' => 22,
                'theme' => 'Vision & sens',
                'title' => "Teste ta vision sur le terrain",
                'summary' => "Une vision qui n'a pas été confrontée à la réalité de ceux qu'elle concerne reste une vision solitaire.",
                'micro_challenge' => "Partage ta vision (ou un aspect de ta direction) avec 2 personnes de ton équipe en leur demandant : « Qu'est-ce que ça suscite en vous ? Qu'est-ce qui vous attire ? Qu'est-ce qui vous inquiète ? » Écoute sans défendre.",
                'duration_min' => 20,
                'icon' => 'users',
                'body' => <<<MD
## Pourquoi

Une vision qui reste dans la tête du leader n'est pas encore une vision — c'est un projet personnel. Une vision partagée émerge quand elle a résonné, été débattue, parfois contestée, et finalement appropriée par ceux qui vont la vivre.

## Ce que tu cherches à tester

- Clarté : l'autre comprend-il ce que j'ai voulu dire ?
- Désirabilité : quelque chose dans cette vision l'attire-t-il ?
- Inquiétudes légitimes : quelles résistances ou questions soulève-t-elle ?

## Ce que tu n'es pas en train de faire

Tu ne cherches pas la validation. Tu cherches à affiner. Les meilleures visions émergent d'un dialogue réel entre l'intention du leader et la réalité vécue par l'équipe.
MD,
            ],

            // ===================================================================
            // BLOC 4 — INFLUENCE & CONVICTION (J23-30)
            // ===================================================================

            [
                'day' => 23,
                'theme' => 'Influence & conviction',
                'title' => "Cartographie tes parties prenantes",
                'summary' => "Influencer sans carte, c'est naviguer sans boussole. Savoir qui compte, ce qu'il veut et ce qu'il craint est la base de toute stratégie d'influence.",
                'micro_challenge' => "Dresse la liste des 6 à 8 personnes les plus importantes pour ton projet ou ton rôle. Pour chacune, note : son enjeu principal, son niveau de soutien actuel (allié, neutre, résistant), et une chose que tu pourrais faire pour mieux l'engager.",
                'duration_min' => 15,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Le leadership sans autorité hiérarchique — et souvent même avec — repose sur la capacité à mobiliser des acteurs aux intérêts divergents. Une cartographie des parties prenantes n'est pas un exercice politique : c'est une compréhension réaliste de l'écosystème dans lequel tu opères.

## Les 4 dimensions à cartographier

1. **Pouvoir** : quel est son niveau d'influence sur les décisions qui te concernent ?
2. **Intérêt** : quel est son niveau d'intérêt pour ton projet ?
3. **Position actuelle** : allié actif, soutien passif, neutre, sceptique, opposant ?
4. **Levier d'engagement** : qu'est-ce qui pourrait faire bouger sa position ?

## L'erreur courante

Ne cartographier que les partisans évidents. Les opposants et les neutres sont souvent ceux sur lesquels un investissement relationnel rapporte le plus.
MD,
            ],

            [
                'day' => 24,
                'theme' => 'Influence & conviction',
                'title' => "Adapte ton message à chaque interlocuteur",
                'summary' => "Ce n'est pas le message qui change — c'est la porte d'entrée. Chaque interlocuteur a la sienne.",
                'micro_challenge' => "Prends un projet ou une idée que tu dois défendre. Formule-le en 2 versions différentes : une pour quelqu'un orienté résultats/chiffres, une pour quelqu'un orienté personnes/impact humain. Note ce qui change.",
                'duration_min' => 12,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

Les gens ne prennent pas de décisions de la même façon. Certains ont besoin de données et de logique. D'autres ont besoin de vision et d'impact. D'autres encore ont besoin de sécurité et de processus. Présenter le même message de la même façon à tout le monde, c'est parier que tous partagent ton propre mode de traitement — ce qui est rarement le cas.

## Les 4 profils d'écoute

- **Analytique** : données, preuves, rigueur, risques calculés.
- **Visionnaire** : possibilités, futur, impact transformateur.
- **Pragmatique** : faisabilité, ressources, plan concret.
- **Relationnel** : impact sur les personnes, valeurs, appartenance.

## L'adaptation n'est pas de la manipulation

Adapter son message, c'est traduire — pas trahir. Le fond reste le même ; l'emballage parle à ce qui compte vraiment pour l'autre.
MD,
            ],

            [
                'day' => 25,
                'theme' => 'Influence & conviction',
                'title' => "Construis ta crédibilité avant d'en avoir besoin",
                'summary' => "La crédibilité est une réserve. Elle se constitue dans les moments calmes et se dépense dans les moments de crise.",
                'micro_challenge' => "Identifie un domaine où ta crédibilité est faible mais où tu as une ambition d'influence. Définis une action concrète cette semaine pour la renforcer : partager une expertise, tenir un engagement oublié, reconnaître une erreur passée.",
                'duration_min' => 10,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Robert Cialdini a identifié l'autorité comme l'un des 6 principes d'influence — mais cette autorité n'est pas liée au titre : elle est liée à la crédibilité perçue. Et cette crédibilité se construit sur deux axes : **l'expertise** (tu sais de quoi tu parles) et **la confiance** (tu fais ce que tu dis).

## Les 3 piliers de la crédibilité

1. **Compétence** : les gens croient-ils que tu sais ?
2. **Intégrité** : les gens croient-ils que tu es honnête ?
3. **Bienveillance** : les gens croient-ils que tu agis dans leur intérêt ?

La crédibilité est la somme de ces trois perceptions — et il suffit d'en fragiliser une pour que les autres s'effondrent.

## Reconstruction

Si ta crédibilité a été entamée, la route de retour est simple mais lente : tenir ses engagements petits avant les grands, reconnaître l'erreur passée sans sur-justification, et laisser le temps faire le reste.
MD,
            ],

            [
                'day' => 26,
                'theme' => 'Influence & conviction',
                'title' => "Maîtrise l'art du silence stratégique",
                'summary' => "Les leaders les plus influents ne remplissent pas le silence — ils le laissent travailler.",
                'micro_challenge' => "Dans ta prochaine négociation ou conversation de décision, après avoir posé ta proposition ou ta question, compte jusqu'à 7 dans ta tête avant de parler. Observe ce qui se passe dans le silence.",
                'duration_min' => 10,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

L'inconfort face au silence est universel. La plupart des gens le remplissent instinctivement — souvent en faisant des concessions, en ajoutant des justifications non sollicitées, ou en réduisant leur propre demande. Le leader qui sait tenir le silence donne à l'autre l'espace de penser, et à lui-même l'avantage de la réflexion.

## Les fonctions du silence

- **Après une question** : il force l'autre à aller chercher une réponse plus profonde.
- **Après une proposition** : il laisse la proposition exister sans la défendre immédiatement.
- **Après une émotion forte** : il marque que tu as entendu, sans minimiser ni sur-réagir.

## Ce que le silence n'est pas

Une arme ou une tactique froide. C'est d'abord une discipline intérieure : résister à l'envie de remplir le vide pour conforter son propre inconfort.
MD,
            ],

            [
                'day' => 27,
                'theme' => 'Influence & conviction',
                'title' => "Gère le désaccord comme un allié",
                'summary' => "Un désaccord bien géré renforce la relation et améliore la décision. Mal géré, il crée une résistance silencieuse.",
                'micro_challenge' => "La prochaine fois qu'on t'exprime un désaccord, essaie la séquence : accuser réception (« je comprends que tu vois ça différemment »), explorer (« qu'est-ce qui te préoccupe le plus ? »), puis seulement répondre sur le fond.",
                'duration_min' => 12,
                'icon' => 'handshake',
                'body' => <<<MD
## Pourquoi

Amy Edmondson a montré que les équipes à haute sécurité psychologique — où les gens osent exprimer le désaccord — font moins d'erreurs à long terme et sont plus innovantes. Le désaccord n'est pas un signe de dysfonctionnement : c'est un signal de santé organisationnelle, si le leader sait le recevoir et l'utiliser.

## La séquence en 3 temps

1. **Accuser réception** : montrer que tu as entendu, sans valider ni invalider encore. « Je t'entends. »
2. **Explorer** : comprendre le fond du désaccord avant de chercher à le résoudre. « Qu'est-ce qui te préoccupe le plus dans cette approche ? »
3. **Répondre sur le fond** : une fois le désaccord compris, décider — en expliquant ton raisonnement, même si la décision ne change pas.

## Ce qui ne fonctionne pas

Chercher à « gagner » le désaccord. On peut avoir raison et perdre la relation. On peut changer d'avis et gagner la confiance.
MD,
            ],

            [
                'day' => 28,
                'theme' => 'Influence & conviction',
                'title' => "Utilise la preuve sociale avec discernement",
                'summary' => "Les gens s'orientent naturellement vers ce que font les autres. Le leader qui sait le montrer sans le manipuler amplifie son influence.",
                'micro_challenge' => "Pour un comportement ou une pratique que tu veux encourager dans ton équipe, trouve un exemple concret d'un pair ou d'une équipe similaire qui l'a adopté avec succès. Partage-le de façon factuelle, sans pression.",
                'duration_min' => 10,
                'icon' => 'users',
                'body' => <<<MD
## Pourquoi

Cialdini a documenté comment la preuve sociale — le fait de voir que d'autres font ou pensent quelque chose — est l'un des leviers d'influence les plus puissants. Ce principe s'applique en leadership : montrer qu'une pratique fonctionne « chez des gens comme nous » réduit la résistance au changement bien mieux que l'argument rationnel seul.

## Applications concrètes

- « D'autres équipes dans notre secteur ont essayé cette approche et voici ce qu'elles ont observé… »
- Partager des témoignages internes (un collègue qui a adopté la pratique et en parle positivement).
- Mettre en avant les early adopters dans l'équipe pour créer un effet d'entraînement.

## La limite éthique

La preuve sociale devient manipulation quand elle est fabriquée ou exagérée. Elle reste un outil d'influence légitime quand elle est factuelle et transparente sur sa source.
MD,
            ],

            [
                'day' => 29,
                'theme' => 'Influence & conviction',
                'title' => "Crée de l'urgence sans créer de la panique",
                'summary' => "L'urgence mobilise. La panique paralyse. La différence tient à un seul mot : le contrôle perçu.",
                'micro_challenge' => "Pour un enjeu que tu portes actuellement, formule le message d'urgence en 2 versions : une qui génère de la peur (« si on ne fait rien… »), une qui génère de l'élan (« l'opportunité de… »). Quelle version envoie le signal que la situation est gérable ?",
                'duration_min' => 12,
                'icon' => 'rocket',
                'body' => <<<MD
## Pourquoi

Kotter place la création d'un sentiment d'urgence en tête de sa séquence de conduite du changement. Mais l'urgence mal dosée génère de l'anxiété, et l'anxiété réduit la capacité à penser de façon créative et à prendre des risques calculés. L'objectif n'est pas de paniquer les gens : c'est de les sortir de l'inertie.

## La distinction clé

- **Urgence anxiogène** : met en avant la menace, sans issue claire → paralysie ou fuite.
- **Urgence mobilisatrice** : met en avant l'opportunité limitée dans le temps, avec une direction → action.

## La formule

« Voilà ce qui se passe [réalité]. Voilà pourquoi agir maintenant nous donne un avantage [opportunité temporelle]. Et voilà le premier pas concret qu'on peut faire ensemble [contrôle]. »
MD,
            ],

            [
                'day' => 30,
                'theme' => 'Influence & conviction',
                'title' => "Réfléchis avant de persuader",
                'summary' => "Les leaders les plus influents savent quand ne pas chercher à convaincre — et quand écouter change plus de choses que n'importe quel argument.",
                'micro_challenge' => "Avant ta prochaine tentative d'influence, pose-toi 3 questions : (1) Est-ce que je cherche à influencer ou à avoir raison ? (2) Mon interlocuteur a-t-il les informations et l'espace pour décider librement ? (3) Qu'est-ce que je suis prêt à changer si ses arguments sont bons ?",
                'duration_min' => 10,
                'icon' => 'lightbulb',
                'body' => <<<MD
## Pourquoi

L'influence éthique repose sur la réciprocité : tu cherches à changer l'autre, mais tu restes ouvert à être changé. Un leader qui cherche à toujours convaincre sans jamais être convaincu ne fait pas de l'influence — il fait de la pression. Et la pression crée de la compliance, pas de l'engagement.

## Le test de l'influence intègre

Demande-toi : est-ce que je présente l'information de façon à permettre à l'autre de prendre une décision éclairée, ou de façon à ce qu'il ne puisse que dire oui ?

## Ce que ça change à long terme

Les leaders qui ont la réputation d'être persuasifs ET ouverts créent une culture où les gens apportent leurs meilleures idées — parce qu'ils savent qu'elles seront entendues, pas juste tolérées.
MD,
            ],

            // ===================================================================
            // BLOC 5 — L'ÉQUIPE COMME SYSTÈME (J31-38)
            // ===================================================================

            [
                'day' => 31,
                'theme' => "L'équipe comme système",
                'title' => "Évalue la sécurité psychologique de ton équipe",
                'summary' => "Une équipe où les gens n'osent pas dire ce qu'ils pensent vraiment est une équipe qui fonctionne à mi-régime.",
                'micro_challenge' => "Pose cette question anonymement à ton équipe (mail ou post-it) : « Sur une échelle de 1 à 5, à quel point te sens-tu à l'aise pour exprimer une idée inhabituelles ou signaler un problème dans notre équipe ? » Regarde la distribution, pas la moyenne.",
                'duration_min' => 15,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Amy Edmondson (Harvard) a étudié les meilleures équipes médicales et a découvert quelque chose contre-intuitif : les équipes les plus performantes *déclaraient* plus d'erreurs — non pas parce qu'elles en faisaient plus, mais parce qu'elles se sentaient suffisamment en sécurité pour les signaler. La sécurité psychologique est le premier prédicteur de la performance d'équipe selon le Projet Aristote de Google.

## Ce qui crée (ou détruit) la sécurité psychologique

**Crée** : les leaders qui admettent leur propre incertitude, qui répondent aux signaux précoces sans punir le messager, qui montrent de la curiosité face aux erreurs.

**Détruit** : les réactions défensives aux mauvaises nouvelles, les sarcasmes, le silence après une idée risquée, la survalorisation du consensus.

## Le premier pas

La sécurité psychologique ne se décrète pas. Elle se construit par des micro-signaux répétés. Commence par les tiens : comment réagis-tu quand quelqu'un apporte une mauvaise nouvelle ?
MD,
            ],

            [
                'day' => 32,
                'theme' => "L'équipe comme système",
                'title' => "Cultive la diversité cognitive, pas juste démographique",
                'summary' => "La vraie valeur de la diversité n'est pas symbolique — elle est cognitive : des façons de penser différentes qui permettent de voir ce qu'une seule perspective rate.",
                'micro_challenge' => "Pour ta prochaine réunion de décision, identifie délibérément 2 personnes dont le cadre de référence est le plus éloigné du tien. Invite-les à contribuer en premier — avant que la dynamique de groupe impose une direction.",
                'duration_min' => 12,
                'icon' => 'users',
                'body' => <<<MD
## Pourquoi

Scott Page (The Diversity Bonus) a montré mathématiquement que des groupes composés de personnes aux modèles mentaux différents résolvent des problèmes complexes mieux que des groupes d'experts homogènes. La diversité cognitive n'est pas une valeur morale seulement : c'est un avantage stratégique.

## Les pièges

- **L'homophilie** : on recrute naturellement des gens qui pensent comme nous.
- **L'effet de halo** : on valorise les idées qui ressemblent aux nôtres.
- **La pensée de groupe** : la pression vers le consensus efface les voix dissonantes.

## Ce que le leader peut faire

Créer des espaces où les perspectives minoritaires sont entendues *avant* que la majorité se soit exprimée. La séquence compte : une fois qu'une direction émerge, il est très difficile de la contester même si elle est erronée.
MD,
            ],

            [
                'day' => 33,
                'theme' => "L'équipe comme système",
                'title' => "Crée des rituels de cohésion intentionnels",
                'summary' => "Les équipes soudées ne le sont pas par hasard. Il y a derrière des rituels — des moments réguliers qui créent un sentiment d'appartenance.",
                'micro_challenge' => "Identifie un rituel d'équipe existant (réunion, déjeuner, moment informel). Demande-toi : ce rituel crée-t-il vraiment du lien, ou est-il devenu une obligation ? Si la réponse est la seconde, pense à le réinventer ou à en créer un nouveau.",
                'duration_min' => 10,
                'icon' => 'heart',
                'body' => <<<MD
## Pourquoi

Les rituels ont deux fonctions : ils marquent le temps (début de semaine, fin de projet, célébration) et ils créent de l'appartenance en signalant « nous sommes un groupe avec des pratiques communes ». Les équipes qui ont des rituels significatifs montrent une plus grande cohésion et une meilleure résilience en période de stress.

## Caractéristiques d'un bon rituel d'équipe

1. **Régulier** : prévisible, ancré dans le calendrier.
2. **Participatif** : chacun peut y contribuer, pas seulement le leader.
3. **Signifiant** : il marque quelque chose qui compte (un succès, un défi, une transition).
4. **Léger** : ne demande pas une préparation lourde.

## Exemples

- Tour de table du lundi : chacun partage sa priorité et son énergie du moment (2 min par personne).
- Célébration des victoires petites et grandes, pas seulement des grandes.
- Post-mortem de fin de projet : non pour chercher les coupables, mais pour extraire les apprentissages.
MD,
            ],

            [
                'day' => 34,
                'theme' => "L'équipe comme système",
                'title' => "Transforme la tension en énergie créatrice",
                'summary' => "Le conflit n'est pas un problème à éteindre. C'est souvent le signe que quelque chose d'important est en jeu.",
                'micro_challenge' => "Identifie une tension actuelle dans ton équipe (désaccord, friction entre deux personnes, frustration non dite). Demande-toi : qu'est-ce que cette tension révèle sur ce qui compte pour les personnes impliquées ? Quelle question utile pourrait-elle ouvrir ?",
                'duration_min' => 12,
                'icon' => 'flame',
                'body' => <<<MD
## Pourquoi

Ronald Heifetz distingue les problèmes techniques (qui ont des solutions connues) des défis adaptatifs (qui nécessitent de changer des croyances, des habitudes ou des valeurs). Les conflits d'équipe sont souvent des défis adaptatifs déguisés en problèmes techniques. Les résoudre superficiellement ne fait que les reporter.

## La distinction clé

- **Conflit de surface** : « On n'est pas d'accord sur le processus. »
- **Conflit profond** : « Nos valeurs sur ce qui compte dans le travail divergent. »

Résoudre le premier sans adresser le second crée une paix temporaire.

## Comment utiliser la tension

Poser la question : « Qu'est-ce que ce désaccord révèle sur ce que chacun de vous valorise vraiment ? » permet souvent de passer d'une conversation de position à une conversation d'intérêts — beaucoup plus productive.
MD,
            ],

            [
                'day' => 35,
                'theme' => "L'équipe comme système",
                'title' => "Délègue l'autorité, pas seulement les tâches",
                'summary' => "Déléguer une tâche, c'est soulager ton agenda. Déléguer de l'autorité, c'est développer quelqu'un.",
                'micro_challenge' => "Identifie une décision que tu prends régulièrement et que l'un de tes collaborateurs pourrait prendre lui-même. Délègue-lui cette décision pour les 30 prochains jours — sans veto. Note ce qui se passe.",
                'duration_min' => 10,
                'icon' => 'handshake',
                'body' => <<<MD
## Pourquoi

Hersey & Blanchard ont modélisé la délégation situationnelle : le niveau d'autonomie accordé doit correspondre au niveau de compétence et d'engagement de la personne. Mais même les leaders qui connaissent ce modèle ont tendance à sous-déléguer — par habitude, par peur de l'erreur, ou parce qu'ils font les choses plus vite eux-mêmes.

## La vraie délégation

Déléguer une tâche, c'est confier l'exécution. Déléguer l'autorité, c'est confier le pouvoir de décider comment l'exécuter. La seconde est plus exigeante pour le leader (lâcher le contrôle) et beaucoup plus développante pour le collaborateur.

## Le contrat de délégation

Sois clair sur : l'objectif (ce qui doit être atteint), les contraintes (ce qui ne peut pas être changé), le niveau d'autonomie (décide seul / consulte-moi avant / tiens-moi informé), et les ressources disponibles.
MD,
            ],

            [
                'day' => 36,
                'theme' => "L'équipe comme système",
                'title' => "Amplifie les points forts collectifs",
                'summary' => "La performance collective ne vient pas de corriger tous les points faibles — elle vient d'aligner les points forts de chacun sur les besoins du groupe.",
                'micro_challenge' => "Pour chaque membre de ton équipe, note en une phrase sa contribution unique — ce qu'il apporte que personne d'autre n'apporte exactement de la même façon. Partage-le directement avec chaque personne.",
                'duration_min' => 15,
                'icon' => 'rocket',
                'body' => <<<MD
## Pourquoi

Les recherches de Gallup sur 1,7 million d'employés montrent que les personnes qui utilisent leurs forces au travail tous les jours sont 6 fois plus susceptibles d'être engagées. Mais dans la plupart des équipes, les forces individuelles ne sont jamais nommées explicitement — elles sont utilisées implicitement au mieux, ignorées au pire.

## Comment identifier les points forts collectifs

1. Demande à chaque membre ce qui leur vient naturellement et facilement — ce qu'ils font bien sans y penser.
2. Observe qui est sollicité spontanément par les autres pour quoi.
3. Croise avec les besoins actuels de l'équipe : y a-t-il des forces sous-utilisées ?

## L'effet de nomination

Nommer la contribution unique de quelqu'un — explicitement, devant les autres ou en face à face — crée un lien fort entre identité et engagement. « Tu es la personne qui dans notre équipe sait faire X » change la façon dont quelqu'un se perçoit dans le groupe.
MD,
            ],

            [
                'day' => 37,
                'theme' => "L'équipe comme système",
                'title' => "Installe une culture du feedback mutuel",
                'summary' => "Une équipe où le feedback ne va que du leader vers les membres est une équipe à sens unique — et qui perd la moitié de son intelligence collective.",
                'micro_challenge' => "Lance un rituel de rétroaction courte à la fin de ton prochain atelier ou réunion de travail : 3 minutes, chacun dit (en 1 phrase) quelque chose qui a bien fonctionné ET quelque chose à améliorer pour la prochaine fois.",
                'duration_min' => 12,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

Le feedback multidirectionnel — entre membres, pas seulement du manager — est l'un des marqueurs des équipes apprenantes. Il développe la capacité de chacun à observer les processus collectifs, à nommer ce qui ne va pas sans attendre l'autorisation du leader, et à améliorer en continu.

## Comment l'installer sans que ça soit artificiel

1. Commencer par des formats courts et non menaçants (pas « critique ton collègue »).
2. Le leader donne le ton en acceptant lui-même le feedback sans défensive.
3. Répéter le rituel : la première fois est toujours maladroite, la dixième devient naturelle.

## Ce que ça change dans la durée

Une équipe qui pratique le feedback mutuel régulièrement développe une tolérance à l'inconfort constructif. Elle peut aborder des conversations difficiles — avec les clients, avec la direction — que d'autres équipes évitent.
MD,
            ],

            [
                'day' => 38,
                'theme' => "L'équipe comme système",
                'title' => "Pilote par la confiance, pas par le contrôle",
                'summary' => "Contrôler crée de la compliance. Faire confiance crée de l'engagement. La différence de performance entre les deux est massive.",
                'micro_challenge' => "Identifie un moment récent où tu as sur-contrôlé (vérifié quelque chose que tu aurais pu laisser faire, repris une tâche déléguée). Qu'est-ce qui t'a poussé à le faire ? La peur de l'erreur ? Le manque de confiance ? Le besoin de maîtrise ?",
                'duration_min' => 10,
                'icon' => 'handshake',
                'body' => <<<MD
## Pourquoi

La théorie de l'autodétermination (Deci & Ryan) identifie l'autonomie comme l'un des trois besoins fondamentaux qui alimentent la motivation intrinsèque. Un environnement de sur-contrôle détruit cette autonomie — et avec elle, l'engagement, la créativité et l'initiative.

## Le paradoxe du contrôle

Plus tu contrôles, moins les gens prennent d'initiative. Moins ils prennent d'initiative, plus tu sens que tu dois contrôler. C'est un cercle vicieux. La sortie passe par un acte de foi : faire confiance avant d'avoir la garantie que ça fonctionnera.

## Construire une confiance fondée

La confiance aveugle n'est pas une vertu managériale — c'est de la négligence. La confiance fondée repose sur : des attentes claires, des compétences vérifiées, et des mécanismes de vérification légère (un point hebdomadaire, pas un micro-management quotidien).
MD,
            ],

            // ===================================================================
            // BLOC 6 — DÉCIDER SOUS INCERTITUDE (J39-46)
            // ===================================================================

            [
                'day' => 39,
                'theme' => 'Décider sous incertitude',
                'title' => "Distingue les décisions réversibles des irréversibles",
                'summary' => "Toutes les décisions ne méritent pas le même niveau d'analyse. Confondre les deux, c'est être trop lent sur les petites et trop rapide sur les grandes.",
                'micro_challenge' => "Liste 5 décisions que tu dois prendre dans les 2 prochaines semaines. Pour chacune, note : réversible (on peut revenir en arrière à faible coût) ou irréversible (coût de retour élevé). Ajuste ta vitesse de décision en conséquence.",
                'duration_min' => 10,
                'icon' => 'scale',
                'body' => <<<MD
## Pourquoi

Jeff Bezos a théorisé cette distinction : les décisions de type 1 (irréversibles) nécessitent une réflexion approfondie. Les décisions de type 2 (réversibles) doivent être prises rapidement, par de petits groupes, quitte à se tromper et corriger. La plupart des organisations appliquent le processus type 1 à tout — ce qui crée de la lenteur sans améliorer la qualité.

## Application pratique

- **Réversible** : décide vite, délègue, expérimente. L'erreur est peu coûteuse.
- **Irréversible** : ralentis, consulte, cartographie les risques. L'erreur est coûteuse.

## L'erreur la plus coûteuse

Traiter une décision irréversible (recrutement clé, partenariat, pivot stratégique) comme si elle était réversible. Prendre le temps de se poser la question en amont évite des erreurs difficiles à corriger.
MD,
            ],

            [
                'day' => 40,
                'theme' => 'Décider sous incertitude',
                'title' => "Pense en systèmes, pas en silos",
                'summary' => "Chaque décision a des effets secondaires. Le leader qui ne les voit pas crée des problèmes qu'il ne comprend pas.",
                'micro_challenge' => "Prends une décision récente que tu as prise. Dessine ses conséquences de second ordre : qu'est-ce que cette décision change pour les acteurs adjacents ? Qu'est-ce qu'elle rend plus difficile, même si elle résout le problème immédiat ?",
                'duration_min' => 15,
                'icon' => 'map',
                'body' => <<<MD
## Pourquoi

Peter Senge a montré que la plupart des problèmes complexes en organisation sont des problèmes de systèmes mal compris. Une décision qui optimise localement peut dégrader le système global. Par exemple : accélérer la production sans consulter le service qualité crée des problèmes en aval ; réduire les coûts d'un département sans regarder ses interdépendances crée des inefficacités ailleurs.

## La pensée systémique en pratique

Avant de valider une décision, demande-toi :
1. Qui d'autre est affecté par cette décision sans être dans la pièce ?
2. Quels effets retardés cette décision pourrait-elle avoir dans 3 mois ? 1 an ?
3. Est-ce que je traite un symptôme ou une cause racine ?

## L'outil minimal

Dessine 3 cercles concentriques : au centre, l'effet immédiat de ta décision. Au milieu, les effets de second ordre. À l'extérieur, les effets de troisième ordre (rares, mais importants pour les grandes décisions).
MD,
            ],

            [
                'day' => 41,
                'theme' => 'Décider sous incertitude',
                'title' => "Embrasse l'ambiguïté comme donnée permanente",
                'summary' => "L'incertitude n'est pas un problème à résoudre avant d'agir. C'est le milieu dans lequel le leader doit naviguer.",
                'micro_challenge' => "Identifie une situation où tu attends d'avoir plus d'informations avant de décider. Demande-toi honnêtement : attendre va-t-il réduire l'incertitude significativement, ou est-ce une façon d'éviter le risque d'une mauvaise décision ?",
                'duration_min' => 10,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

Le monde VUCA (Volatile, Incertain, Complexe, Ambigu) n'est pas une phase temporaire : c'est la condition normale de tout leader opérant dans un environnement dynamique. La recherche de certitude avant d'agir est un réflexe humain compréhensible mais souvent contre-productif : elle ralentit, crée de la dépendance à l'information, et souvent n'apporte pas la clarté espérée.

## La tolérance à l'ambiguïté

C'est une compétence qui se développe. Elle repose sur :
- La confiance dans sa capacité à s'adapter en cours de route.
- La distinction entre « je ne sais pas encore tout » et « je sais assez pour avancer ».
- L'expérience répétée de décider sans certitude et de corriger si nécessaire.

## L'alternative à l'attente

Au lieu d'attendre la clarté : définir les critères de décision à l'avance (« si X, alors Y »), lancer un test limité, ou choisir l'option qui conserve le plus d'options ouvertes.
MD,
            ],

            [
                'day' => 42,
                'theme' => 'Décider sous incertitude',
                'title' => "Utilise le pré-mortem pour anticiper les échecs",
                'summary' => "Imaginer l'échec avant qu'il arrive est l'un des outils de décision les plus puissants — et les moins utilisés.",
                'micro_challenge' => "Pour un projet ou une décision importante, anime un pré-mortem de 10 minutes avec ton équipe : « Imaginez qu'on soit dans 6 mois et que ce projet ait échoué. Qu'est-ce qui s'est passé ? » Listez les causes sans filtre.",
                'duration_min' => 15,
                'icon' => 'lightbulb',
                'body' => <<<MD
## Pourquoi

Gary Klein a développé le pré-mortem comme réponse à un biais cognitif bien documenté : le biais d'optimisme. Nous surévaluons systématiquement nos chances de succès et sous-estimons les obstacles. Le pré-mortem contourne ce biais en donnant la permission explicite d'imaginer l'échec — ce qui fait remonter des informations que personne n'aurait osé partager autrement.

## Comment l'animer

1. Annoncer : « Faisons comme si ce projet avait échoué dans 6 mois. Qu'est-ce qui s'est passé ? »
2. Laisser chacun écrire individuellement (3 min) sans influence de groupe.
3. Partager et lister les causes identifiées.
4. Pour chaque cause probable, identifier une action préventive.

## Ce que ça n'est pas

Un exercice de pessimisme. C'est un outil de robustesse : identifier les risques réels pour mieux les anticiper. Les projets qui traversent un pré-mortem sérieux sont souvent mieux préparés que ceux qui n'y ont pas été soumis.
MD,
            ],

            [
                'day' => 43,
                'theme' => 'Décider sous incertitude',
                'title' => "Cultive ta tolérance à l'incomplétude",
                'summary' => "Les perfectionnistes font de bons techniciens et de mauvais leaders. Le leadership exige d'agir avec des informations incomplètes.",
                'micro_challenge' => "Identifie une chose que tu retardes parce qu'elle n'est « pas encore prête ». Demande-toi : à quel niveau de complétude serait-elle assez bonne pour avancer ? Lance-la à ce niveau, avec un plan d'amélioration continue.",
                'duration_min' => 10,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Le perfectionnisme est souvent une forme d'évitement du jugement : si je ne montre rien, je ne peux pas être critiqué. Mais dans un contexte de leadership, le coût du délai est souvent supérieur au coût de l'imperfection. Les leaders efficaces ont intégré que 80 % bien fait maintenant vaut souvent mieux que 100 % parfait trop tard.

## Le critère de « assez bon »

Définir à l'avance ce que signifie « assez bon pour avancer » est la clé. Non pas comme abandon de standards, mais comme choix conscient de priorité entre vitesse et perfection selon l'enjeu.

## La distinction essentielle

Certaines choses méritent le perfectionnisme (une décision irréversible, une communication publique majeure). Beaucoup d'autres ne le méritent pas. Distinguer les deux est une compétence de leader.
MD,
            ],

            [
                'day' => 44,
                'theme' => 'Décider sous incertitude',
                'title' => "Génère des options avant de choisir",
                'summary' => "La qualité d'une décision dépend de la qualité des options considérées. Trop souvent, on choisit entre seulement deux alternatives.",
                'micro_challenge' => "Pour une décision en cours, force-toi à identifier 4 options différentes avant d'en éliminer. Ajoute toujours l'option « ne rien faire » et l'option « déléguée à quelqu'un d'autre ». Quelle nouvelle option ce processus t'a-t-il révélée ?",
                'duration_min' => 12,
                'icon' => 'lightbulb',
                'body' => <<<MD
## Pourquoi

Chip et Dan Heath (*Decisive*) ont montré que la principale cause de mauvaises décisions est le cadrage binaire : « dois-je faire A ou B ? » En réalité, toute décision peut générer plus d'options si on se donne la permission de chercher. L'élargissement délibéré du champ des possibles améliore la qualité des décisions finales.

## Les 4 questions pour élargir les options

1. Que ferais-je si l'option A et l'option B n'existaient pas ?
2. Y a-t-il quelqu'un qui a résolu un problème similaire de façon différente ?
3. Quelle est l'option que je n'ose pas envisager parce qu'elle me dérange ?
4. Comment un observateur extérieur, intelligent mais ignorant de nos contraintes, verrait-il ce problème ?

## L'option cachée

Souvent, l'option la plus créative est celle qu'on n'a pas encore formulée parce qu'elle exige de remettre en question une hypothèse de base. Identifier cette hypothèse est souvent le travail le plus utile.
MD,
            ],

            [
                'day' => 45,
                'theme' => 'Décider sous incertitude',
                'title' => "Apprends de tes décisions passées",
                'summary' => "Les leaders qui ne réfléchissent pas à leurs décisions passées condamnent leur équipe à répéter les mêmes erreurs.",
                'micro_challenge' => "Choisit une décision prise dans les 6 derniers mois qui n'a pas donné les résultats attendus. Sans chercher les responsables, pose-toi : quelles hypothèses j'avais ? Laquelle était fausse ? Qu'est-ce que je ferais différemment ?",
                'duration_min' => 15,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

La boucle d'apprentissage (Argyris, Schön) distingue le simple bouclage (corriger l'erreur) du double bouclage (remettre en question les hypothèses qui ont conduit à l'erreur). La plupart des organisations pratiquent le premier ; les organisations apprenantes pratiquent le second.

## Le journal de décision

Tenir un journal de décision — noter avant de décider les hypothèses, les alternatives écartées et les critères utilisés — permet une revue honnête ultérieure. Sans cela, la mémoire réécrit le passé pour le rendre cohérent avec le présent.

## Ce que ça n'est pas

Une séance d'auto-flagellation ou de chasse aux coupables. C'est une extraction d'apprentissage : qu'est-ce que cette décision m'a appris sur ma façon de décider dans ce type de situation ?
MD,
            ],

            [
                'day' => 46,
                'theme' => 'Décider sous incertitude',
                'title' => "Maintiens ta direction sous pression externe",
                'summary' => "La vraie épreuve du leadership n'est pas de décider quand tout est calme — c'est de rester cap quand tout pousse à changer de direction.",
                'micro_challenge' => "Identifie une décision ou une direction sur laquelle tu subis de la pression pour changer de cap. Sépare : (a) les arguments de fond qui méritent d'être reconsidérés, (b) la pression sociale qui ne mérite pas de changer la direction. Décide en conséquence.",
                'duration_min' => 12,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

Heifetz parle de la capacité à « tenir la chaleur » — à maintenir la tension productive d'un défi adaptatif sans la réduire prématurément sous la pression des parties prenantes. Les leaders qui cèdent à la pression sans raisonnement de fond ne font pas preuve de flexibilité : ils font preuve d'instabilité.

## La distinction entre adaptation et capitulation

- **Adaptation** : changer de direction parce que de nouvelles informations ou de nouveaux arguments le justifient.
- **Capitulation** : changer de direction parce que la pression est forte, même si les arguments restent les mêmes.

La première est une compétence. La seconde est un manque de cap.

## L'ancrage dans les valeurs

Quand la pression est forte, revenir à la question : « Ma décision est-elle cohérente avec mes valeurs et ma vision ? » Si oui, tenir. Si non, reconsidérer — mais pas à cause de la pression, à cause du fond.
MD,
            ],

            // ===================================================================
            // BLOC 7 — TRANSFORMER L'ORGANISATION (J47-54)
            // ===================================================================

            [
                'day' => 47,
                'theme' => "Transformer l'organisation",
                'title' => "Lis la culture comme un système vivant",
                'summary' => "La culture n'est pas ce qui est écrit sur les murs — c'est ce qui se passe dans les angles quand personne ne regarde.",
                'micro_challenge' => "Observe une réunion ou un moment informel d'équipe avec le regard d'un anthropologue : quels comportements sont valorisés sans le dire ? Quels sujets sont évités ? Qui parle après qui ? Qu'est-ce que ça révèle sur la culture réelle ?",
                'duration_min' => 15,
                'icon' => 'eye',
                'body' => <<<MD
## Pourquoi

Edgar Schein a modélisé la culture organisationnelle en 3 niveaux : les artefacts visibles (les symboles, l'espace, les rituels), les valeurs déclarées (ce qu'on dit valoriser), et les hypothèses fondamentales implicites (ce qu'on croit vraiment et qui guide les comportements). Le niveau 3 est invisible mais dominant — et c'est là que le changement réel se joue.

## Les révélateurs de la culture réelle

- Qui est promu ? (Ce qu'on valorise vraiment.)
- Comment réagit-on aux erreurs ? (La tolérance réelle au risque.)
- De quoi rit-on ? (Ce qui est normalisé.)
- Qui peut contredire qui ? (La hiérarchie invisible du pouvoir.)

## Ce que le leader peut faire

Tu ne peux pas changer la culture par décret. Tu peux la modeler en changeant tes comportements — parce que les comportements du leader sont scrutés et imités plus que n'importe quel autre signal.
MD,
            ],

            [
                'day' => 48,
                'theme' => "Transformer l'organisation",
                'title' => "Identifie les gardiens et les catalyseurs du changement",
                'summary' => "Dans tout système humain, certaines personnes accélèrent le changement et d'autres le ralentissent. Les connaître est la première étape pour les engager.",
                'micro_challenge' => "Pour un changement en cours dans ton périmètre, liste les 5 à 6 personnes les plus influentes dans sa trajectoire. Pour chacune : est-elle catalyseur (elle tire le changement), gardienne (elle le ralentit mais pour des raisons légitimes), ou résistante (elle s'y oppose activement) ?",
                'duration_min' => 12,
                'icon' => 'users',
                'body' => <<<MD
## Pourquoi

Everett Rogers a modélisé la diffusion de l'innovation : les early adopters (2-5 % du groupe) sont les leviers du changement. Convaincre la majorité précoce ne passe pas par convaincre tout le monde simultanément — ça passe par activer les bons relais au bon moment.

## Les 3 types d'acteurs à identifier

1. **Catalyseurs** : ils veulent que ça change, ils ont de l'énergie et de l'influence. → Leur donner une visibilité et de l'espace pour agir.
2. **Gardiens légitimes** : ils ralentissent parce qu'ils protègent quelque chose de réel (qualité, continuité). → Les écouter et intégrer leurs préoccupations dans le design du changement.
3. **Résistants actifs** : ils s'opposent pour des raisons de perte de pouvoir ou de valeurs incompatibles. → Engager s'il y a une marge de dialogue, marginaliser si ce n'est pas possible.

## L'erreur classique

Investir l'essentiel de son énergie à convaincre les résistants — plutôt qu'à amplifier les catalyseurs.
MD,
            ],

            [
                'day' => 49,
                'theme' => "Transformer l'organisation",
                'title' => "Commence par des victoires rapides visibles",
                'summary' => "Un changement sans résultats visibles rapidement perd son élan. Les premières victoires ne prouvent pas que tout ira bien — elles prouvent que quelque chose est possible.",
                'micro_challenge' => "Pour un changement que tu portes, identifie une victoire rapide que tu peux montrer dans les 2 prochaines semaines : quelque chose de petit, de visible, et qui démontre que la direction est bonne. Planifie-la.",
                'duration_min' => 10,
                'icon' => 'rocket',
                'body' => <<<MD
## Pourquoi

Kotter place les « short-term wins » (victoires à court terme) au cœur de sa séquence de changement. Sans elles, la résistance se réorganise, les partisans se découragent, et les sceptiques trouvent des munitions. Avec elles, l'élan se crée et les douteux rejoignent le mouvement.

## Caractéristiques d'une bonne victoire rapide

1. **Visible** : les gens peuvent la voir ou en entendre parler facilement.
2. **Indiscutable** : difficile d'attribuer le résultat à une cause autre que le changement.
3. **Liée au changement** : elle démontre quelque chose sur la direction, pas un succès sans rapport.

## Ce que ça n'est pas

Une communication cosmétique. Une vraie victoire rapide est un résultat réel, même modeste. La différence est perçue immédiatement par les équipes.
MD,
            ],

            [
                'day' => 50,
                'theme' => "Transformer l'organisation",
                'title' => "Crée des coalitions d'acteurs-clés",
                'summary' => "Aucun leader ne conduit seul un changement significatif. Il construit une coalition suffisamment diverse et influente pour le porter.",
                'micro_challenge' => "Pour un changement important, identifie 3 à 4 personnes qui ne sont pas dans ton équipe directe mais qui ont de l'influence sur sa trajectoire. Organise une conversation individuelle avec chacune cette semaine pour comprendre leurs enjeux et chercher leur soutien.",
                'duration_min' => 15,
                'icon' => 'handshake',
                'body' => <<<MD
## Pourquoi

Kotter identifie la constitution d'une « guiding coalition » comme la deuxième étape indispensable du changement. Cette coalition doit avoir trois propriétés : suffisamment de pouvoir pour bloquer les résistances, suffisamment de crédibilité pour être entendue, et suffisamment de diversité pour représenter l'ensemble du système.

## Comment construire une coalition

1. Identifier les acteurs à la croisée de l'influence et de l'enjeu.
2. Les approcher individuellement avant de les réunir (les conversations de groupe sans préparation individuelle produisent peu).
3. Chercher leur intérêt sincère dans le changement — pas juste leur accord de surface.
4. Leur donner un rôle réel, pas honorifique.

## La limite du leadership solitaire

Les changements portés par un seul leader, même charismatique, s'essoufflent quand ce leader part ou perd de l'énergie. Une coalition répartit le leadership et le rend plus résilient.
MD,
            ],

            [
                'day' => 51,
                'theme' => "Transformer l'organisation",
                'title' => "Gère la résistance sans l'écraser",
                'summary' => "La résistance est rarement irrationnelle. Elle pointe souvent vers quelque chose de réel que le changement menace.",
                'micro_challenge' => "Pense à la personne la plus résistante à un changement que tu portes. Prends 15 minutes pour essayer de comprendre son point de vue honnêtement : que risque-t-elle de perdre ? Quelle préoccupation légitime exprime-t-elle même si mal formulée ?",
                'duration_min' => 12,
                'icon' => 'shield',
                'body' => <<<MD
## Pourquoi

Les personnes qui résistent à un changement ne sont pas irrationnelles — elles défendent quelque chose qui a de la valeur pour elles : une façon de travailler qui fonctionnait, une relation de pouvoir qu'elles vont perdre, une crainte de ne pas être compétentes dans le nouveau monde. Écraser cette résistance sans l'écouter, c'est perdre des informations précieuses sur les risques réels du changement.

## Les 4 sources de résistance

1. **Perte de compétence** : « je ne sais pas faire dans le nouveau monde. »
2. **Perte de sens** : « je ne comprends pas pourquoi on change ce qui fonctionne. »
3. **Perte de relation** : « les liens que j'ai construits vont être détruits. »
4. **Perte de pouvoir** : « ma position va s'affaiblir dans le nouveau système. »

## La bonne réponse

Chaque source de résistance appelle une réponse différente. La formation pour la compétence. Le récit pour le sens. Le soin pour les relations. La négociation pour le pouvoir.
MD,
            ],

            [
                'day' => 52,
                'theme' => "Transformer l'organisation",
                'title' => "Communique le changement par vagues",
                'summary' => "Annoncer une fois ne suffit pas. Les gens entendent, oublient, doutent, et ont besoin d'entendre à nouveau — avec de nouvelles preuves.",
                'micro_challenge' => "Pour un changement que tu communiques, planifie 3 moments de communication différents sur les 4 prochaines semaines : une annonce, une démonstration de résultats intermédiaires, et une célébration d'un premier succès. Programme-les maintenant.",
                'duration_min' => 10,
                'icon' => 'message',
                'body' => <<<MD
## Pourquoi

Kotter estime que les leaders sous-communiquent les changements par un facteur de 10. Les gens doivent entendre un message 7 à 10 fois avant de l'intégrer vraiment — et chaque répétition doit apporter quelque chose de nouveau (un résultat, un témoignage, une précision) pour ne pas être perçue comme du bruit.

## La règle des 3 vagues

1. **L'annonce** : le pourquoi, le quoi, le cap. Ce n'est pas encore la conviction — c'est la direction.
2. **Les preuves en cours** : des signaux que la direction est bonne (premiers résultats, témoignages).
3. **La célébration** : marquer un jalon de façon visible pour consolider l'élan.

## Ce que les leaders oublient souvent

La communication du changement n'est pas une conférence de presse — c'est une conversation continue. Les questions, les doutes, les rumeurs qui circulent informellement sont des signaux sur ce qui n'a pas encore été entendu ou compris.
MD,
            ],

            [
                'day' => 53,
                'theme' => "Transformer l'organisation",
                'title' => "Préserve ce qui donne du sens pendant le changement",
                'summary' => "Tout changer à la fois détruit les repères. Les leaders qui savent ce qui mérite de rester créent une continuité qui rend le changement supportable.",
                'micro_challenge' => "Dans un changement en cours, identifie 2 ou 3 éléments de la situation actuelle qui méritent d'être préservés parce qu'ils ont une valeur réelle (une pratique, une relation, un rituel). Communique explicitement que ces éléments ne changent pas.",
                'duration_min' => 10,
                'icon' => 'anchor',
                'body' => <<<MD
## Pourquoi

William Bridges a montré que les gens ne résistent pas aux changements en eux-mêmes — ils résistent aux pertes que ces changements impliquent. Reconnaître et honorer ce qui est perdu (ou préservé) dans un changement est une condition de son acceptation durable.

## La distinction changement / transition

Le changement est externe (nouvelle organisation, nouveau système). La transition est interne (le processus psychologique par lequel les gens lâchent l'ancien et adoptent le nouveau). Le changement peut être rapide ; la transition prend du temps.

## Ce que le leader peut faire concrètement

- Nommer explicitement ce qui est préservé, pas seulement ce qui change.
- Organiser un moment de reconnaissance de ce qu'on laisse derrière (un projet, une façon de fonctionner).
- Ne pas minimiser les pertes réelles : les gens qui sentent que leurs pertes sont reconnues s'adaptent plus vite.
MD,
            ],

            [
                'day' => 54,
                'theme' => "Transformer l'organisation",
                'title' => "Institutionnalise les nouveaux comportements",
                'summary' => "Un changement qui n'est pas ancré dans des structures et des pratiques quotidiennes régresse dès que l'attention se détourne.",
                'micro_challenge' => "Pour un comportement nouveau que tu veux installer dans ton équipe, identifie comment le rendre « par défaut » : un rituel, un outil, un processus qui fait que le nouveau comportement est plus facile que l'ancien. Mets-le en place cette semaine.",
                'duration_min' => 12,
                'icon' => 'target',
                'body' => <<<MD
## Pourquoi

Kotter identifie l'ancrage du changement dans la culture comme la dernière étape — et celle qui est le plus souvent bâclée. Les comportements nouveaux régressent vers l'ancien par défaut si les structures (processus, systèmes d'évaluation, rituels) ne les soutiennent pas.

## Le principe du design comportemental

Richard Thaler et Cass Sunstein ont montré que les comportements sont fortement influencés par l'architecture des choix : ce qui est le plus facile, le plus visible, le plus attendu. Changer le comportement par défaut est souvent plus efficace que de persuader.

## Application pratique

- Intégrer le nouveau comportement dans une réunion récurrente (ex : commencer chaque réunion par un apprentissage).
- Le rendre mesurable et visible (tableau de bord, rituel de célébration).
- L'inscrire dans les critères d'évaluation et de promotion : ce qui est mesuré et récompensé se fait.
MD,
            ],

            // ===================================================================
            // BLOC 8 — DURER & TRANSMETTRE (J55-60)
            // ===================================================================

            [
                'day' => 55,
                'theme' => 'Durer & transmettre',
                'title' => "Cultive l'humilité apprenante",
                'summary' => "Les leaders qui arrêtent d'apprendre commencent à décliner — même s'ils continuent à diriger.",
                'micro_challenge' => "Identifie un domaine où tu as arrêté d'apprendre parce que tu penses « déjà savoir ». Définis une action concrète pour y réintroduire de la curiosité : un livre, une conversation avec un expert, une immersion terrain.",
                'duration_min' => 10,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Carol Dweck a montré que la mentalité de croissance — croire que les capacités se développent par l'effort — est corrélée avec de meilleures performances à long terme. Mais la mentalité de croissance chez les leaders a un ennemi particulier : le succès. Plus on réussit, plus on a tendance à croire que ce qu'on sait suffit.

## Le paradoxe de l'expertise

L'expertise approfondit la connaissance dans un domaine — mais elle peut aussi créer des angles morts en dehors. Les meilleurs leaders maintiennent délibérément une posture de débutant (« shoshin » en zen) dans les domaines où ils ne sont pas experts.

## Le signe d'alarme

Si tu te retrouves à souvent dire « oui, je connais » avant que l'autre ait fini sa phrase, c'est le signal que la curiosité a été remplacée par la confirmation. La curiosité s'entretient — elle ne se maintient pas seule.
MD,
            ],

            [
                'day' => 56,
                'theme' => 'Durer & transmettre',
                'title' => "Développe activement les leaders de demain",
                'summary' => "Le test ultime d'un leader n'est pas ce qu'il accomplit lui-même, mais ce qu'il a rendu possible chez les autres.",
                'micro_challenge' => "Identifie une personne dans ton équipe qui a un potentiel de leadership non encore exprimé. Définit une opportunité concrète que tu peux lui offrir cette semaine pour qu'elle l'exerce : un projet, une prise de parole, une délégation de responsabilité réelle.",
                'duration_min' => 15,
                'icon' => 'users',
                'body' => <<<MD
## Pourquoi

Warren Bennis a écrit : « Le leadership ultime, c'est de créer un environnement dans lequel les gens développent leur plein potentiel. » Les organisations dont les leaders développent d'autres leaders ont une résilience que celles dont les leaders développent des suiveurs n'ont pas.

## La différence entre former et développer

- **Former** : transmettre des compétences techniques définies.
- **Développer** : créer des conditions dans lesquelles quelqu'un découvre ses propres capacités et élargit ses limites.

Le développement passe par l'exposition à des défis réels, le soutien actif, et le feedback régulier — pas seulement par des formations.

## La question du lâcher-prise

Développer un futur leader implique souvent de lui céder de l'espace — parfois de l'espace où tu étais toi-même. C'est inconfortable, et c'est exactement pourquoi peu de leaders le font vraiment.
MD,
            ],

            [
                'day' => 57,
                'theme' => 'Durer & transmettre',
                'title' => "Construis ta succession de façon consciente",
                'summary' => "Un leader qui n'a pas pensé à sa succession rend son organisation fragile. Un leader qui la prépare activement lui donne de la résilience.",
                'micro_challenge' => "Si tu devais quitter ton rôle demain, qui serait prêt à 70 % à le reprendre ? Si personne ne vient à l'esprit immédiatement, c'est un signal. Définis une action pour préparer au moins une personne dans les 6 prochains mois.",
                'duration_min' => 12,
                'icon' => 'compass',
                'body' => <<<MD
## Pourquoi

La plupart des leaders pensent à leur succession le plus tard possible — souvent parce que la préparer implique d'accepter sa propre finitude dans le rôle. Mais les organisations dont les leaders préparent activement leur succession montrent une meilleure performance à long terme et une plus grande stabilité lors des transitions.

## La préparation de succession n'est pas un aveu de départ

C'est une responsabilité de leadership. Elle signifie : « Je construis quelque chose qui dure au-delà de moi. »

## Comment préparer concrètement

1. Identifier 1 à 2 successeurs potentiels.
2. Les exposer progressivement à tes responsabilités (réunions stratégiques, représentation externe, décisions complexes).
3. Leur donner un feedback régulier sur leur développement spécifiquement pour ce rôle.
4. Documenter les décisions clés et les connaissances implicites de ton poste.
MD,
            ],

            [
                'day' => 58,
                'theme' => 'Durer & transmettre',
                'title' => "Crée du sens dans la durée",
                'summary' => "Ce qui donne envie de continuer quand c'est difficile, ce n'est pas la motivation — c'est le sens.",
                'micro_challenge' => "Complète cette phrase : « Dans 10 ans, je veux que les personnes avec qui j'ai travaillé disent que j'ai contribué à… » Si la réponse ne te semble pas assez grande, reformule. Si elle te semble juste, affiche-la quelque part.",
                'duration_min' => 12,
                'icon' => 'heart',
                'body' => <<<MD
## Pourquoi

Viktor Frankl a montré que l'être humain peut traverser n'importe quelle épreuve s'il y trouve du sens. Pour un leader, le sens dans la durée est ce qui permet de maintenir l'engagement au-delà du premier enthousiasme, des résistances, des échecs et des compromis inévitables.

## Les deux sources de sens

1. **Le sens transcendant** : contribuer à quelque chose de plus grand que soi (une mission, une génération future, un impact sociétal).
2. **Le sens relationnel** : la valeur créée pour les personnes spécifiques que l'on guide.

Les deux sont nécessaires. Le sens transcendant donne la direction ; le sens relationnel donne le carburant quotidien.

## La question du renouvellement

Le sens se renouvelle — il ne reste pas stable. Les leaders qui durent revisitent régulièrement la question du sens, surtout après les grandes transitions.
MD,
            ],

            [
                'day' => 59,
                'theme' => 'Durer & transmettre',
                'title' => "Laisse une empreinte, pas une trace",
                'summary' => "Une trace est ce qu'on a fait. Une empreinte est ce qu'on a changé dans les gens et les systèmes — ce qui reste après qu'on soit parti.",
                'micro_challenge' => "Liste 3 personnes dont tu as influencé le développement au cours de ta carrière. Qu'est-ce que tu leur as transmis qui durera au-delà de votre relation professionnelle ? Qu'est-ce que tu aurais voulu leur transmettre de plus ?",
                'duration_min' => 15,
                'icon' => 'seedling',
                'body' => <<<MD
## Pourquoi

Robert Greenleaf a défini le servant leader comme quelqu'un dont la priorité est de servir les autres — et dont la question centrale est : « Ceux qui me suivent grandissent-ils en tant que personnes ? Deviennent-ils plus libres, plus capables, plus autonomes ? »

## La différence empreinte / trace

- **Trace** : les projets réalisés, les chiffres atteints, les structures construites. Disparaissent souvent avec le leader.
- **Empreinte** : les leaders formés, les façons de penser transmises, les cultures amorcées. Durent après le départ.

## Ce qui détermine l'empreinte

L'empreinte ne se décide pas dans les grandes décisions — elle se construit dans les milliers de petites interactions quotidiennes : comment tu réponds à une erreur, comment tu accueilles une idée, comment tu parles des absents.
MD,
            ],

            [
                'day' => 60,
                'theme' => 'Durer & transmettre',
                'title' => "Écris ta lettre à ton successeur",
                'summary' => "Ce que tu aurais voulu savoir quand tu as commencé. Ce à quoi tu t'accrocherais dans les moments difficiles. Ce que tu transmettrais si tu ne pouvais dire qu'une chose.",
                'micro_challenge' => "Écris une lettre à la personne qui te succédera dans ton rôle actuel — ou à la version de toi-même qui commençait ce parcours. Sans filtre. Ce que tu as vraiment appris sur le leadership. Ce qui compte vraiment. Ce que tu ferais différemment.",
                'duration_min' => 20,
                'icon' => 'book',
                'body' => <<<MD
## Pourquoi

La lettre à son successeur est l'un des exercices les plus révélateurs du leadership. Elle force à distiller 60 jours (et peut-être des années) d'expérience en ce qui compte vraiment — en laissant de côté les techniques, les frameworks et les modèles pour revenir à l'essentiel humain du leadership.

## Ce qu'on écrit rarement — et qui compte le plus

- Les erreurs qu'on a faites et ce qu'elles ont appris.
- Ce qu'on aurait voulu entendre dans les moments de doute.
- Les choses qui ont l'air importantes mais qui ne le sont pas.
- Les choses qui ont l'air banales mais qui font toute la différence.

## Pour toi aussi

Cette lettre n'est pas seulement pour ton successeur. Elle est pour toi — une synthèse de qui tu es devenu comme leader à travers ce parcours. Garde-la. Relis-la dans un an. Mesure le chemin parcouru.

---

*Tu as terminé 60 jours de leadership intégral. Le vrai travail commence maintenant.*
MD,
            ],

        ];
    }
}
