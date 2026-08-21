<?php

namespace Praxis\Plugins\PraxiBalance\Data;

/**
 * La Balance — cartes de déblocage.
 *
 * Servies entre deux swipes quand le candidat cale : une question ouverte
 * pour les niveaux de connaissance (indexée sur le thème des notions ratées),
 * un recentrage bref pour les séries chronométrées. Aucune bonne réponse,
 * aucun score : on casse la boucle d échec, on ne la sanctionne pas.
 *
 * Fichier généré depuis le prototype : ne pas éditer à la main.
 */
class Prompts
{
    /** Questions puissantes, indexées par thème de notion. */
    public static function powerQuestions(): array
    {
        return [
            'tri' => [
                'question' => 'Qu\'est-ce que tu as fait hier qui était urgent, mais qui ne comptait pas vraiment ?',
                'relance'  => 'Si tu trouves plusieurs réponses, tu tiens déjà le créneau que tu cherchais.',
            ],
            'reactif' => [
                'question' => 'Ce matin, qui a décidé de ta première heure : toi, ou ta boîte de réception ?',
                'relance'  => 'La réponse dit à peu près tout du reste de ta journée.',
            ],
            'mit' => [
                'question' => 'Si tu ne pouvais accomplir qu\'une seule chose demain, laquelle rendrait la journée réussie ?',
                'relance'  => 'Celle-là. Bloque-lui un créneau maintenant, avant que la journée ne se remplisse.',
            ],
            'cout' => [
                'question' => 'La dernière fois que tu as dit oui trop vite, qu\'est-ce que ça t\'a coûté ?',
                'relance'  => 'Ce que tu n\'as pas fait à la place, personne ne l\'a vu. Toi si.',
            ],
            'refus' => [
                'question' => 'À quelle demande aurais-tu dû dire non cette semaine ?',
                'relance'  => 'Qu\'est-ce qui t\'en a empêché : le manque de temps pour réfléchir, ou la peur de décevoir ?',
            ],
            'delegation' => [
                'question' => 'Quelle tâche fais-tu par habitude, alors qu\'elle n\'est plus vraiment de ton ressort ?',
                'relance'  => 'Depuis combien de temps ? Et qui pourrait la reprendre correctement ?',
            ],
            'inacheve' => [
                'question' => 'Combien de chantiers as-tu ouverts en ce moment ? Compte-les vraiment.',
                'relance'  => 'Lequel gagnerais-tu le plus à terminer cette semaine, plutôt qu\'à faire avancer un peu ?',
            ],
            'effort' => [
                'question' => 'Sur ta tâche en cours, quelle part du travail produit l\'essentiel du résultat ?',
                'relance'  => 'Et combien de temps passes-tu sur le reste ?',
            ],
            'estimation' => [
                'question' => 'Ta dernière tâche importante : combien de temps pensais-tu y passer, combien y as-tu passé ?',
                'relance'  => 'L\'écart n\'est pas un accident. C\'est ton coefficient personnel — applique-le à la prochaine.',
            ],
            'revue' => [
                'question' => 'Quand as-tu relu tes priorités pour la dernière fois ?',
                'relance'  => 'Si tu dois chercher la date, c\'est qu\'elles pilotent moins ta semaine que tu ne le crois.',
            ],
            'negociation' => [
                'question' => 'Sur ta dernière surcharge, as-tu demandé à ton interlocuteur ce qu\'il fallait décaler ?',
                'relance'  => 'S\'il ne sait pas que les deux ne tiennent pas, il ne peut pas t\'aider à choisir.',
            ],
            'urgence_collective' => [
                'question' => 'La dernière urgence que tu as transmise, en avais-tu vérifié l\'échéance réelle ?',
                'relance'  => 'Une chaîne de personnes s\'arrête à la première qui pose la question.',
            ],
            'visibilite' => [
                'question' => 'Ton entourage sait-il sur quoi tu es engagé cette semaine ?',
                'relance'  => 'S\'il ne le sait pas, chaque nouvelle demande lui semble raisonnable — et elle l\'est, de son point de vue.',
            ],
            'energie' => [
                'question' => 'À quel moment de la journée prends-tu tes décisions les plus lourdes ?',
                'relance'  => 'Est-ce parce que c\'est le meilleur moment, ou parce que c\'est le créneau qui restait ?',
            ],
        ];
    }

    /** Recentrages servis pendant les séries chronométrées. */
    public static function resets(): array
    {
        return [
            [
                'question' => 'Tu tries par réflexe, ou tu lis la conséquence ?',
                'relance'  => 'Reprends : pour chaque carte, demande-toi ce qui arrive si ce n\'est pas fait.',
            ],
            [
                'question' => 'L\'urgence attire l\'œil. L\'importance, non.',
                'relance'  => 'Ralentis d\'un cran : c\'est la conséquence qu\'on cherche, pas l\'échéance.',
            ],
            [
                'question' => 'La vitesse n\'est pas l\'objectif ici.',
                'relance'  => 'Vise le tri juste. Relâche les épaules et reprends la série.',
            ],
        ];
    }
}
