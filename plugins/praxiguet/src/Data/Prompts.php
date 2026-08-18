<?php

namespace Praxis\Plugins\PraxiGuet\Data;

/**
 * Cartes de déblocage de La Tour de Guet.
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
            'multitache' => [
                'question' => 'Quand as-tu terminé une tâche pour la dernière fois sans en ouvrir une autre entre-temps ?',
                'relance'  => 'Repense à ce moment précis : qu\'est-ce qui l\'avait rendu possible ? C\'est cette condition-là qu\'il faut recréer, pas la volonté.',
            ],
            'vagabondage' => [
                'question' => 'Quand ton esprit s\'échappe, où va-t-il exactement ?',
                'relance'  => 'Vers un souci, une envie, ou quelque chose de plus intéressant ? Les trois ne se traitent pas de la même façon.',
            ],
            'sommeil' => [
                'question' => 'Et si ton sommeil était la condition de ton travail, plutôt que sa variable d\'ajustement ?',
                'relance'  => 'Regarde ta semaine : qu\'est-ce que tu déplacerais dès ce soir ?',
            ],
            'mesure' => [
                'question' => 'Sur quoi juges-tu ta concentration : sur ce que tu as produit, ou sur ce que tu as ressenti ?',
                'relance'  => 'Les deux se contredisent souvent. Une seule des deux est vérifiable.',
            ],
            'ecran' => [
                'question' => 'Si ton téléphone était dans une autre pièce pendant les 90 prochaines minutes, qu\'arriverait-il vraiment de grave ?',
                'relance'  => 'Nomme-le précisément. Le plus souvent, la réponse honnête est « rien ».',
            ],
            'bruit' => [
                'question' => 'Ton environnement de travail, tu l\'as choisi — ou tu l\'as subi ?',
                'relance'  => 'Qu\'est-ce que tu pourrais y changer aujourd\'hui, en moins de deux minutes ?',
            ],
            'organisation' => [
                'question' => 'Quelle est la seule tâche qui, terminée aujourd\'hui, rendrait le reste secondaire ?',
                'relance'  => 'Si tu hésites entre trois, c\'est que la journée n\'est pas encore décidée.',
            ],
            'energie' => [
                'question' => 'À quelle heure es-tu le plus lucide ? Et qu\'est-ce que tu y fais, en ce moment ?',
                'relance'  => 'L\'écart entre ces deux réponses est probablement ton plus gros gisement.',
            ],
            'emotion' => [
                'question' => 'Ce qui te bloque sur cette tâche : sa difficulté, ou ce qu\'elle te fait ressentir ?',
                'relance'  => 'On traite rarement la deuxième, alors que c\'est presque toujours elle.',
            ],
            'demarrage' => [
                'question' => 'Quelle est la plus petite action possible pour entrer dans la tâche que tu repousses ?',
                'relance'  => 'Si elle prend plus de deux minutes, elle est encore trop grosse.',
            ],
        ];
    }

    /** Recentrages servis pendant les séries chronométrées. */
    public static function resets(): array
    {
        return [
            [
                'question' => 'Tu réponds avant d\'avoir vu, ou tu vois avant de répondre ?',
                'relance'  => 'Trois respirations. Expire plus longtemps que tu n\'inspires. Puis repars.',
            ],
            [
                'question' => 'La vitesse n\'est pas l\'objectif.',
                'relance'  => 'Vise le geste juste : la vitesse suit toute seule. Relâche les épaules et reprends.',
            ],
            [
                'question' => 'Ton réflexe a pris le dessus sur la règle.',
                'relance'  => 'Relis la consigne une fois, à voix basse. Puis reprends la série.',
            ],
        ];
    }
}
