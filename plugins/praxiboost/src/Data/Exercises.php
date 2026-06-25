<?php

namespace Praxis\Plugins\PraxiBoost\Data;

/**
 * Catalogue des exercices de développement personnel.
 *
 * Chaque exercice se débloque lorsqu'un palier d'Éclats (XP cumulés) est atteint.
 * Les paliers sont volontairement progressifs : le premier (100) tombe dès les
 * premières interactions pour créer un effet d'accroche, les suivants
 * récompensent l'engagement dans la durée.
 *
 * Principe directeur (refonte 2026-06-21) : ne retenir que des protocoles dont
 * l'efficacité est étayée par des méta-analyses ou des essais contrôlés
 * randomisés (ECR), avec un mécanisme d'action clair et une durée réaliste.
 * Chaque exercice se termine par une « clause si-alors » (intention
 * d'implémentation, Gollwitzer & Sheeran 2006, d=0,65) pour transformer
 * l'intention en action.
 *
 * Fondements : psychologie positive (Seligman ; méta-analyse gratitude 2023),
 * neurophysiologie respiratoire (Balban et al. 2023, Cell Reports Medicine),
 * auto-compassion (Neff ; Ferrari et al. 2019, g=0,75) et auto-distanciation
 * (Kross), TCC (Beck), ACT / valeurs (Hayes, Harris), contraste mental WOOP
 * (Oettingen) et intentions d'implémentation (Gollwitzer).
 */
class Exercises
{
    public static function all(): array
    {
        return [
            [
                'slug'             => 'journal-3-reussites',
                'title'            => 'Les 3 réussites du jour',
                'category'         => 'Confiance en soi',
                'summary'          => "Reprogramme ton attention vers ce qui fonctionne pour nourrir l'estime de soi.",
                'duration_min'     => 5,
                'icon'             => 'sparkles',
                'threshold_eclats' => 300,
                'sort_order'       => 1,
                'body'             => <<<MD
## Pourquoi cet exercice

Notre cerveau a un *biais de négativité* : il retient plus facilement les échecs que les réussites. Repérer chaque jour ce qui s'est bien passé entraîne l'attention à voir aussi le positif — un levier direct sur la confiance.

**Niveau de preuve : 🟢 solide.** Les interventions de gratitude type « trois bonnes choses » améliorent le bien-être et réduisent les symptômes dépressifs et anxieux (méta-analyse 2023, 25 ECR, Hedges' g ≈ 0,22). Point clé confirmé : la **variété** compte — noter des choses *nouvelles* chaque jour évite l'accoutumance.

## Comment faire (5 min)

1. Chaque soir, note **trois choses qui se sont bien passées** dans ta journée, même minuscules — et **différentes** de la veille.
2. Pour chacune, écris **une phrase** sur *ton rôle* dans cette réussite : qu'as-tu fait, décidé ou osé ?
3. Relis tes notes une fois par semaine pour repérer un fil conducteur : quelles forces reviennent souvent ?

## Ta clause si-alors

*« Quand je pose ma tête sur l'oreiller, alors je note mes 3 réussites du jour. »*
MD,
            ],
            [
                'slug'             => 'soupir-physiologique',
                'title'            => 'Le soupir physiologique — calmer le système nerveux en 5 min',
                'category'         => 'Gestion du stress',
                'summary'          => "La technique respiratoire la mieux étayée pour faire redescendre la tension avant un enjeu.",
                'duration_min'     => 5,
                'icon'             => 'heart-pulse',
                'threshold_eclats' => 750,
                'sort_order'       => 2,
                'body'             => <<<MD
## Pourquoi cet exercice

Avant un entretien, une prise de parole ou une décision difficile, le stress accélère le rythme cardiaque et brouille la réflexion. Le **soupir physiologique** — deux inspirations nasales suivies d'une longue expiration — réinflate les alvéoles pulmonaires et active le système nerveux parasympathique.

**Niveau de preuve : 🟢 solide.** Dans un ECR de Stanford (Balban et al., 2023, *Cell Reports Medicine*, n=111), 5 minutes par jour de respiration centrée sur l'expiration ont amélioré l'humeur **davantage que la méditation de pleine conscience** et réduit la fréquence respiratoire (affect positif +1,91 vs +1,22 sur 28 jours).

## Comment faire (5 min)

1. Assieds-toi, dos droit, épaules relâchées.
2. **Inspire par le nez**, puis, sans expirer, **inspire encore un petit coup** par le nez (double inspiration).
3. **Expire lentement et complètement par la bouche**, plus longtemps que l'inspiration.
4. Recommence ce cycle pendant **5 minutes**.
5. Remarque le ralentissement progressif de ton rythme cardiaque.

## Ta clause si-alors

*« Quand je sens le stress monter avant un moment important, alors je fais 5 soupirs physiologiques avant d'agir. »*
MD,
            ],
            [
                'slug'             => 'auto-distanciation-compassion',
                'title'            => "Se parler comme à un ami (auto-distanciation)",
                'category'         => 'Confiance en soi',
                'summary'          => "Prends du recul sur tes émotions difficiles en te parlant à la 3ᵉ personne, avec bienveillance.",
                'duration_min'     => 6,
                'icon'             => 'message-heart',
                'threshold_eclats' => 1350,
                'sort_order'       => 3,
                'body'             => <<<MD
## Pourquoi cet exercice

Quand une émotion difficile nous submerge, on « tourne en rond » dedans. Deux leviers complémentaires aident à en sortir : se parler à soi-même **à la troisième personne** (auto-distanciation) et s'adresser à soi avec la **bienveillance** qu'on aurait pour un ami (auto-compassion).

**Niveau de preuve : 🟢 solide.** L'auto-distanciation régule l'émotion **sans épuiser les ressources cognitives** (preuves combinées ERP et IRMf, Kross, Moser et al.). L'auto-compassion réduit le stress, la dépression et l'autocritique avec des effets moyens (Ferrari et al., 2019, méta-analyse de 27 ECR : g=0,75 pour l'auto-compassion, 0,66 pour la dépression).

## Comment faire (6 min)

1. Repère une situation qui te pèse en ce moment.
2. Décris-la **en t'appelant par ton prénom**, à la 3ᵉ personne : *« [Ton prénom] se sent dépassé·e parce que… »*
3. Pose-toi la question : *« Qu'est-ce que je dirais à un ami qui vit exactement ça ? »* Écris cette réponse.
4. Relis-la à voix haute, en t'adressant à toi avec le même ton qu'à cet ami.

## Ta clause si-alors

*« Quand je me surprends à me critiquer durement, alors je me parle à la 3ᵉ personne comme à un ami. »*
MD,
            ],
            [
                'slug'             => 'recadrer-croyance-limitante',
                'title'            => 'Reformuler une croyance limitante',
                'category'         => 'Restructuration cognitive',
                'summary'          => "Identifie une pensée qui te freine et transforme-la en pensée alternative réaliste.",
                'duration_min'     => 8,
                'icon'             => 'brain',
                'threshold_eclats' => 2100,
                'sort_order'       => 4,
                'body'             => <<<MD
## Pourquoi cet exercice

Les **croyances limitantes** (« je ne suis pas légitime », « je vais échouer ») agissent comme des prophéties auto-réalisatrices. La restructuration cognitive consiste à les examiner plutôt qu'à les croire sur parole.

**Niveau de preuve : 🟢 solide.** La restructuration cognitive est l'un des ingrédients actifs les mieux établis des thérapies cognitivo-comportementales (Beck, 1979), efficaces sur l'anxiété et la dépression dans de nombreuses méta-analyses.

## Comment faire (8 min)

1. **Repère** la pensée. Écris-la mot pour mot.
2. **Cherche les preuves** : quels faits la soutiennent ? Quels faits la contredisent ?
3. **Mesure l'utilité** : cette pensée t'aide-t-elle à agir, ou te paralyse-t-elle ?
4. **Reformule** une version plus juste. Exemple : « Je ne suis pas légitime » → « Je débute sur ce sujet, et je peux apprendre comme les autres l'ont fait. »
5. Relis la nouvelle formulation à voix haute.

## Ta clause si-alors

*« Quand une pensée du type "je n'en suis pas capable" surgit, alors je l'écris et je cherche un fait qui la contredit. »*
MD,
            ],
            [
                'slug'             => 'clarifier-valeurs-coeur',
                'title'            => 'Clarifier ses valeurs cœur',
                'category'         => 'Sens & valeurs',
                'summary'          => "Identifie les 3 valeurs qui comptent vraiment pour guider tes choix.",
                'duration_min'     => 10,
                'icon'             => 'compass',
                'threshold_eclats' => 3300,
                'sort_order'       => 5,
                'body'             => <<<MD
## Pourquoi cet exercice

Les décisions alignées sur nos **valeurs profondes** procurent une satisfaction plus durable que celles dictées par la pression extérieure. Encore faut-il les avoir clarifiées.

**Niveau de preuve : 🟢 solide.** La clarification des valeurs comme boussole de l'action est un processus central de la thérapie d'acceptation et d'engagement (ACT, Hayes ; Harris, 2009), dont l'efficacité est soutenue par de nombreux ECR sur le bien-être et l'engagement comportemental.

## Comment faire (10 min)

1. **Souviens-toi** d'un moment où tu t'es senti·e pleinement toi-même, fier·e. Décris-le en 3 lignes.
2. Dans ce souvenir, **quelles valeurs étaient honorées ?** (liberté, transmission, justice, créativité, sécurité, lien…)
3. Liste **8 valeurs** qui te parlent, puis réduis à **3 valeurs cœur**.
4. Pour chacune, écris **une action concrète** que tu pourrais poser cette semaine pour l'incarner.

## Ta clause si-alors

*« Quand je dois prendre une décision importante, alors je me demande quelle option respecte le mieux mes 3 valeurs cœur. »*
MD,
            ],
            [
                'slug'             => 'woop-souhait-plan',
                'title'            => "WOOP : du souhait au plan d'action",
                'category'         => 'Passage à l\'action',
                'summary'          => "Transforme une envie vague en plan concret en anticipant les obstacles (méthode WOOP).",
                'duration_min'     => 10,
                'icon'             => 'target',
                'threshold_eclats' => 5100,
                'sort_order'       => 6,
                'body'             => <<<MD
## Pourquoi cet exercice

Rêver d'un objectif sans anticiper ce qui nous en empêche mène rarement à l'action. La méthode **WOOP** (Wish, Outcome, Obstacle, Plan) crée la tension motivante nécessaire en confrontant le souhait à l'obstacle interne — c'est le *contraste mental* couplé à un plan si-alors.

**Niveau de preuve : 🟢 solide.** Le contraste mental avec intentions d'implémentation (MCII / WOOP, Oettingen) améliore l'atteinte des objectifs dans plusieurs ECR et une méta-analyse, sur des domaines variés (santé, études, comportement).

## Comment faire (10 min)

1. **W — Souhait** : choisis un objectif important et réalisable pour les prochaines semaines.
2. **O — Résultat** : imagine vivement le **meilleur résultat** si tu y arrives. Que ressens-tu ?
3. **O — Obstacle** : identifie l'**obstacle intérieur** principal (une habitude, une peur, une émotion). Imagine-le concrètement.
4. **P — Plan** : écris une clause *« Quand [obstacle survient], alors je [action pour le surmonter]. »*

## Ta clause si-alors

C'est le « P » de WOOP lui-même : garde ta phrase *« Quand… alors… »* visible (téléphone, carnet).
MD,
            ],
            [
                'slug'             => 'plan-action-30-jours',
                'title'            => "Plan d'action 30 jours aligné sur ton profil",
                'category'         => 'Passage à l\'action',
                'summary'          => "Transforme tes résultats en un plan concret et réaliste sur 30 jours.",
                'duration_min'     => 12,
                'icon'             => 'rocket',
                'threshold_eclats' => 9000,
                'sort_order'       => 7,
                'body'             => <<<MD
## Pourquoi cet exercice

Une intention vague (« je devrais m'y mettre ») se concrétise rarement. Les **intentions d'implémentation** — « quand X, alors je fais Y » — multiplient le passage à l'action.

**Niveau de preuve : 🟢 solide.** Méta-analyse de référence : Gollwitzer & Sheeran (2006), **d=0,65** sur 94 tests et plus de 8 000 participants — un effet moyen-à-fort sur l'atteinte des objectifs ; mise à jour 2024 sur 642 tests confirmant la robustesse.

## Comment faire (12 min)

1. **Un objectif** : choisis un seul objectif aligné sur ce que tes tests ont révélé. Formule-le concrètement et de façon mesurable.
2. **Trois jalons** : découpe-le en 3 étapes réparties sur 30 jours.
3. **Déclencheurs** : pour chaque étape, écris une règle *« Quand [situation précise], alors je [action] »* (ex. « Quand j'ouvre mon ordinateur le matin, alors je travaille 25 min sur l'étape 1 »).
4. **Obstacles** : anticipe 1 obstacle probable et ta parade.
5. **Preuve** : décide comment tu sauras, au jour 30, que c'est réussi.

## Suivi

Note ton avancement chaque semaine. Un plan vivant, ajusté, vaut mieux qu'un plan parfait jamais suivi.
MD,
            ],
        ];
    }
}
