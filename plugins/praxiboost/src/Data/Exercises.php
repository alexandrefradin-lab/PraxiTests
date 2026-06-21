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
 * Fondements : psychologie positive (Seligman), cohérence cardiaque,
 * thérapies cognitivo-comportementales (Beck), ACT / clarification des valeurs
 * (Hayes, Schwartz) et intentions d'implémentation (Gollwitzer).
 */
class Exercises
{
    public static function all(): array
    {
        return [
            [
                'slug'             => 'journal-3-reussites',
                'title'            => 'Le journal des 3 réussites',
                'category'         => 'Confiance en soi',
                'summary'          => "Reprogramme ton attention vers tes réussites pour nourrir l'estime de soi.",
                'duration_min'     => 5,
                'icon'             => 'sparkles',
                'threshold_eclats' => 100,
                'sort_order'       => 1,
                'body'             => <<<MD
## Pourquoi cet exercice

Notre cerveau a un *biais de négativité* : il retient plus facilement les échecs que les réussites. Cet exercice, issu de la psychologie positive (Seligman), entraîne ton attention à repérer ce qui fonctionne — un levier direct sur la confiance en soi.

## Comment faire (5 min)

1. Chaque soir, note **trois choses qui se sont bien passées** dans ta journée, même minuscules (un appel réussi, un repas agréable, une difficulté évitée).
2. Pour chacune, écris **une phrase** sur *ton rôle* dans cette réussite : qu'as-tu fait, décidé ou osé ?
3. Relis tes notes une fois par semaine.

## Pour aller plus loin

Au bout de 7 jours, repère un fil conducteur : quelles forces reviennent souvent ? Ce sont des appuis sur lesquels construire.
MD,
            ],
            [
                'slug'             => 'ancrage-coherence-cardiaque',
                'title'            => 'Respiration & ancrage avant un enjeu',
                'category'         => 'Gestion du stress',
                'summary'          => "Une routine de 3 minutes pour calmer le système nerveux avant un moment important.",
                'duration_min'     => 4,
                'icon'             => 'heart-pulse',
                'threshold_eclats' => 300,
                'sort_order'       => 2,
                'body'             => <<<MD
## Pourquoi cet exercice

Avant un entretien, une prise de parole ou une décision difficile, le stress accélère le rythme cardiaque et brouille la réflexion. La **cohérence cardiaque** régule le système nerveux autonome en quelques minutes.

## Comment faire (3-4 min)

1. Assieds-toi, dos droit, pieds au sol.
2. Respire selon le rythme **5-5** : inspire 5 secondes par le nez, expire 5 secondes par la bouche.
3. Maintiens ce rythme **3 minutes** (environ 18 cycles).
4. Pendant l'expiration, imagine que tu déposes la tension dans le sol.

## Ancrage

Juste après, pose une main sur le sternum et formule une phrase courte et vraie : *« Je suis prêt·e, j'ai ce qu'il faut. »* Associe-la à ce calme : tu pourras la réactiver le moment venu.
MD,
            ],
            [
                'slug'             => 'recadrer-croyance-limitante',
                'title'            => 'Reformuler une croyance limitante',
                'category'         => 'Restructuration cognitive',
                'summary'          => "Identifie une pensée qui te freine et transforme-la en pensée alternative réaliste.",
                'duration_min'     => 8,
                'icon'             => 'brain',
                'threshold_eclats' => 700,
                'sort_order'       => 3,
                'body'             => <<<MD
## Pourquoi cet exercice

Les **croyances limitantes** (« je ne suis pas légitime », « je vais échouer ») agissent comme des prophéties auto-réalisatrices. La restructuration cognitive (Beck) consiste à les examiner plutôt qu'à les croire sur parole.

## Comment faire (8 min)

1. **Repère** la pensée. Écris-la mot pour mot.
2. **Cherche les preuves** : quels faits la soutiennent ? Quels faits la contredisent ?
3. **Mesure l'utilité** : cette pensée t'aide-t-elle à agir, ou te paralyse-t-elle ?
4. **Reformule** une version plus juste et nuancée. Exemple : « Je ne suis pas légitime » → « Je débute sur ce sujet, et je peux apprendre comme les autres l'ont fait. »
5. Relis la nouvelle formulation à voix haute.

## Astuce

Garde la version reformulée à portée (téléphone, carnet). Plus tu la relis, plus elle devient ton réflexe par défaut.
MD,
            ],
            [
                'slug'             => 'clarifier-valeurs-coeur',
                'title'            => 'Clarifier ses valeurs cœur',
                'category'         => 'Sens & valeurs',
                'summary'          => "Identifie les 3 valeurs qui comptent vraiment pour guider tes choix.",
                'duration_min'     => 10,
                'icon'             => 'compass',
                'threshold_eclats' => 1500,
                'sort_order'       => 4,
                'body'             => <<<MD
## Pourquoi cet exercice

Les décisions alignées sur nos **valeurs profondes** procurent plus de satisfaction durable que celles dictées par la pression extérieure (ACT, Hayes ; théorie des valeurs, Schwartz). Encore faut-il les avoir clarifiées.

## Comment faire (10 min)

1. **Souviens-toi** d'un moment où tu t'es senti·e pleinement toi-même, fier·e. Décris-le en 3 lignes.
2. Dans ce souvenir, **quelles valeurs étaient honorées ?** (ex. liberté, transmission, justice, créativité, sécurité, lien…)
3. Liste **8 valeurs** qui te parlent, puis réduis à **3 valeurs cœur**.
4. Pour chacune, écris **une action concrète** que tu pourrais poser cette semaine pour l'incarner davantage.

## Pour aller plus loin

À chaque décision importante, demande-toi : *« Quelle option respecte le mieux mes 3 valeurs cœur ? »*
MD,
            ],
            [
                'slug'             => 'plan-action-30-jours',
                'title'            => "Plan d'action 30 jours aligné sur ton profil",
                'category'         => 'Passage à l\'action',
                'summary'          => "Transforme tes résultats en un plan concret et réaliste sur 30 jours.",
                'duration_min'     => 12,
                'icon'             => 'rocket',
                'threshold_eclats' => 3000,
                'sort_order'       => 5,
                'body'             => <<<MD
## Pourquoi cet exercice

Une intention vague (« je devrais m'y mettre ») se concrétise rarement. Les **intentions d'implémentation** (Gollwitzer) — « quand X, alors je fais Y » — multiplient le passage à l'action.

## Comment faire (12 min)

1. **Un objectif** : choisis un seul objectif aligné sur ce que tes tests ont révélé. Formule-le concrètement et de façon mesurable.
2. **Trois jalons** : découpe-le en 3 étapes réparties sur 30 jours.
3. **Déclencheurs** : pour chaque étape, écris une règle *« Quand [situation précise], alors je [action] »* (ex. « Quand j'ouvre mon ordinateur le matin, alors je travaille 25 min sur l'étape 1 »).
4. **Obstacles** : anticipe 1 obstacle probable et ta parade.
5. **Preuve** : décide comment tu sauras, au jour 30, que c'est réussi.

## Suivi

Note ton avancement chaque semaine. Reviens ajuster le plan — un plan vivant vaut mieux qu'un plan parfait.
MD,
            ],
        ];
    }
}
