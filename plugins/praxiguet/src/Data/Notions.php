<?php

namespace Praxis\Plugins\PraxiGuet\Data;

/**
 * La Tour de Guet — banque de notions.
 *
 * 24 notions x 4 formulations. La répétition espacée porte sur la NOTION,
 * jamais sur la phrase : à chaque réapparition, la notion revient sous une
 * formulation différente. Les formulations d une même notion n ont PAS toutes
 * la même réponse (tournure directe = vrai, tournure inversée = faux) — sans
 * quoi l apprenant mémorise l étiquette au lieu du concept.
 *
 * Fichier généré depuis le prototype : ne pas éditer à la main.
 */
class Notions
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return [
            // ── Niveau 1 ──
            [
                'id'          => 'n01',
                'level'       => 1,
                'theme'       => 'multitache',
                'explanation' => 'Le cerveau ne parallélise pas deux tâches à forte demande : il bascule de l\'une à l\'autre (<b>task-switching</b>). Chaque bascule a un coût, invisible mais cumulatif.',
                'variants'    => [
                    ['Le cerveau humain sait traiter deux tâches cognitives exigeantes en parallèle.', false],
                    ['Faire deux choses exigeantes « en même temps », c\'est en réalité alterner très vite entre elles.', true],
                    ['Le multitâche est une compétence qui s\'entraîne : avec de la pratique, on finit par vraiment paralléliser.', false],
                    ['Suivre une réunion tout en rédigeant un mail dégrade les deux à la fois.', true],
                ],
            ],
            [
                'id'          => 'n02',
                'level'       => 1,
                'theme'       => 'multitache',
                'explanation' => 'Il faut recharger le contexte mental : ce qu\'on faisait, où on en était, ce qu\'on visait. Le coût réel d\'une interruption dépasse largement sa durée.',
                'variants'    => [
                    ['Après une interruption, revenir pleinement dans une tâche complexe prend souvent plusieurs minutes.', true],
                    ['Une interruption de trente secondes coûte trente secondes.', false],
                    ['Dix micro-interruptions coûtent plus cher que leur durée cumulée.', true],
                    ['Reprendre une tâche interrompue est immédiat dès qu\'on se remet devant son écran.', false],
                ],
            ],
            [
                'id'          => 'n03',
                'level'       => 1,
                'theme'       => 'multitache',
                'explanation' => 'Une sollicitation visible n\'a pas besoin d\'être suivie pour coûter : <b>y résister mobilise déjà des ressources</b>. Le coût n\'est pas dans l\'onglet, il est dans la décision répétée de ne pas y aller.',
                'variants'    => [
                    ['Garder quinze onglets ouverts est un signe de flexibilité, sans coût attentionnel.', false],
                    ['Chaque onglet ouvert est une invitation permanente à basculer.', true],
                    ['Fermer les fenêtres inutiles avant de commencer allège la charge mentale.', true],
                    ['Tant qu\'on ne clique pas dessus, un onglet ouvert est neutre.', false],
                ],
            ],
            [
                'id'          => 'n04',
                'level'       => 1,
                'theme'       => 'vagabondage',
                'explanation' => 'Le vagabondage mental est un <b>mode par défaut</b> du cerveau, utile à la consolidation et à la créativité. L\'objectif n\'est pas de le supprimer, mais de raccourcir le délai entre « je décroche » et « je reviens ».',
                'variants'    => [
                    ['L\'esprit qui vagabonde est un défaut à éliminer complètement.', false],
                    ['Remarquer qu\'on a décroché, puis revenir : c\'est le cœur de l\'entraînement attentionnel.', true],
                    ['Un esprit sain reste focalisé en continu pendant une heure, sans jamais dériver.', false],
                    ['Le vagabondage mental participe à la consolidation des souvenirs et à la créativité.', true],
                ],
            ],
            [
                'id'          => 'n05',
                'level'       => 1,
                'theme'       => 'vagabondage',
                'explanation' => 'Une tâche sous-dimensionnée laisse des ressources libres, qui partent ailleurs. <b>Trop facile distrait autant que trop dur.</b>',
                'variants'    => [
                    ['Plus une tâche est difficile et engageante, moins l\'esprit a tendance à vagabonder.', true],
                    ['C\'est sur les tâches faciles et répétitives que l\'esprit part le plus loin.', true],
                    ['Une tâche très simple protège naturellement de la distraction.', false],
                    ['L\'ennui est un facteur de distraction aussi puissant que le bruit.', true],
                ],
            ],
            [
                'id'          => 'n06',
                'level'       => 1,
                'theme'       => 'sommeil',
                'explanation' => 'La capacité à <b>juger de son propre état</b> chute plus vite que la performance elle-même : on se croit opérationnel alors qu\'on ne l\'est plus.',
                'variants'    => [
                    ['Le manque de sommeil dégrade l\'attention avant même qu\'on s\'en aperçoive.', true],
                    ['En dette de sommeil, on sait précisément à quel point on est diminué.', false],
                    ['Une nuit trop courte se compense par un supplément de volonté.', false],
                    ['L\'auto-évaluation de sa vigilance devient peu fiable en privation de sommeil.', true],
                ],
            ],
            [
                'id'          => 'n07',
                'level'       => 1,
                'theme'       => 'mesure',
                'explanation' => 'Le transfert vers des tâches réelles reste faible : on progresse surtout <b>sur l\'exercice entraîné</b>. Ce module t\'apprend des mécanismes et des habitudes ; il ne « muscle » pas ton attention comme un biceps.',
                'variants'    => [
                    ['Les programmes de « brain training » transfèrent largement leurs gains à la vie quotidienne.', false],
                    ['On progresse surtout sur l\'exercice qu\'on entraîne, assez peu au-delà.', true],
                    ['S\'entraîner à repérer des formes à l\'écran améliore la concentration au bureau.', false],
                    ['Comprendre les mécanismes de son attention est plus rentable que de muscler un exercice isolé.', true],
                ],
            ],
            [
                'id'          => 'n08',
                'level'       => 1,
                'theme'       => 'mesure',
                'explanation' => 'L\'attention est un <b>état</b>, pas une identité. Ce qui la fait varier est en grande partie sous ton contrôle : sommeil, environnement, découpage des tâches.',
                'variants'    => [
                    ['La capacité de concentration est un trait fixe : on l\'a ou on ne l\'a pas.', false],
                    ['Ta concentration varie selon l\'heure, le sommeil, l\'enjeu et l\'environnement.', true],
                    ['Se définir comme « quelqu\'un de distrait » est une description utile et exacte.', false],
                    ['Une même personne peut être très concentrée le matin et incapable de tenir dix minutes le soir.', true],
                ],
            ],
            // ── Niveau 3 ──
            [
                'id'          => 'n09',
                'level'       => 3,
                'theme'       => 'ecran',
                'explanation' => 'Résister à l\'envie de le consulter consomme déjà de l\'attention. Le rendre <b>invisible</b> supprime la décision, au lieu de te la faire répéter toute la journée.',
                'variants'    => [
                    ['La seule présence visible d\'un smartphone, même éteint, peut réduire les ressources mentales disponibles.', true],
                    ['Le mettre en silencieux sur le bureau suffit à annuler son effet.', false],
                    ['Le ranger hors du champ de vision est plus efficace que de le retourner écran contre table.', true],
                    ['Un téléphone visible n\'a d\'effet que lorsqu\'il sonne.', false],
                ],
            ],
            [
                'id'          => 'n10',
                'level'       => 3,
                'theme'       => 'ecran',
                'explanation' => 'Lire sans traiter <b>ouvre des boucles</b> : le cerveau garde la tâche en suspens. Traiter ses messages par blocs referme ces boucles au lieu de les multiplier.',
                'variants'    => [
                    ['Commencer sa session par un coup d\'œil aux mails « met en condition ».', false],
                    ['Un message lu mais non traité continue de tirer sur l\'attention pendant la tâche suivante.', true],
                    ['Consulter ses mails à heures fixes coûte moins cher que de les surveiller en continu.', true],
                    ['Ouvrir sa boîte mail au réveil est un échauffement neutre.', false],
                ],
            ],
            [
                'id'          => 'n11',
                'level'       => 3,
                'theme'       => 'ecran',
                'explanation' => 'L\'orientation vers un signal saillant est <b>réflexe</b> : elle précède la décision d\'y répondre. Supprimer le signal est plus fiable que lutter contre lui à chaque occurrence.',
                'variants'    => [
                    ['Une notification ignorée ne coûte rien, puisqu\'on n\'y répond pas.', false],
                    ['Couper les notifications est plus efficace que d\'apprendre à les ignorer.', true],
                    ['L\'attention est détournée par le signal avant même qu\'on décide d\'y répondre.', true],
                    ['La discipline personnelle protège mieux qu\'un simple réglage technique.', false],
                ],
            ],
            [
                'id'          => 'n12',
                'level'       => 3,
                'theme'       => 'bruit',
                'explanation' => 'Ce n\'est pas le volume qui distrait, c\'est <b>l\'information</b>. Les paroles entrent en concurrence directe avec le traitement du texte.',
                'variants'    => [
                    ['Le silence total est la condition optimale pour tout le monde.', false],
                    ['Ce qui distrait le plus, c\'est le son porteur de sens et imprévisible — une conversation.', true],
                    ['Un bruit de fond constant et sans information gêne davantage qu\'une conversation à côté.', false],
                    ['Écouter de la musique avec paroles pendant une lecture améliore la compréhension.', false],
                ],
            ],
            [
                'id'          => 'n13',
                'level'       => 3,
                'theme'       => 'bruit',
                'explanation' => 'Plus la tâche mobilise le <b>langage</b>, plus le son verbal coûte cher. Sur une tâche mécanique, un fond sonore peut au contraire aider à tenir la durée.',
                'variants'    => [
                    ['Une musique instrumentale familière gêne moins qu\'une nouveauté chantée.', true],
                    ['Le bon fond sonore dépend de la tâche : lire n\'est pas trier des fichiers.', true],
                    ['Il existe un type de musique optimal pour toutes les tâches.', false],
                    ['Sur une tâche de lecture ou de rédaction, le silence et l\'instrumental sont les paris les plus sûrs.', true],
                ],
            ],
            [
                'id'          => 'n14',
                'level'       => 3,
                'theme'       => 'organisation',
                'explanation' => 'La mémoire de travail est étroite. Ce qui est posé sur le papier n\'a plus à être maintenu mentalement — et chaque hésitation est une porte ouverte à la distraction.',
                'variants'    => [
                    ['Écrire ce qu\'on va faire juste avant de commencer réduit la charge mentale.', true],
                    ['Garder son plan en tête plutôt que de l\'écrire fait gagner du temps.', false],
                    ['Noter sa prochaine action supprime l\'hésitation « je fais quoi maintenant ? ».', true],
                    ['Une liste écrite ne sert qu\'à la mémoire, pas à l\'attention.', false],
                ],
            ],
            [
                'id'          => 'n15',
                'level'       => 3,
                'theme'       => 'organisation',
                'explanation' => 'Vingt-cinq minutes est une <b>convention pratique</b>, pas une constante biologique. Le principe utile : un bloc engagé, une pause franche, et on recommence.',
                'variants'    => [
                    ['La durée de 25 minutes du Pomodoro est un optimum démontré scientifiquement.', false],
                    ['Ce qui compte est l\'alternance effort / pause, pas la durée exacte du bloc.', true],
                    ['La bonne durée de bloc dépend de la tâche et de ta forme du jour.', true],
                    ['Modifier la durée de ses blocs, c\'est mal appliquer la méthode.', false],
                ],
            ],
            [
                'id'          => 'n16',
                'level'       => 3,
                'theme'       => 'organisation',
                'explanation' => 'Une pause récupère si elle <b>change de registre</b>. Passer d\'un écran à un autre écran maintient exactement le même type de charge.',
                'variants'    => [
                    ['Bouger quelques minutes entre deux blocs de travail aide à restaurer l\'attention.', true],
                    ['Une pause passée à faire défiler un fil d\'actualité repose autant qu\'une marche.', false],
                    ['Une pause sur écran prolonge la sollicitation au lieu de la relâcher.', true],
                    ['Seule la durée de la pause compte, pas ce qu\'on y fait.', false],
                ],
            ],
            // ── Niveau 5 ──
            [
                'id'          => 'n17',
                'level'       => 5,
                'theme'       => 'energie',
                'explanation' => 'Elle bloque le <b>signal</b> de fatigue, elle n\'ajoute pas de capacité. Sur un cerveau reposé le gain est marginal ; sur le sommeil, le coût est bien réel.',
                'variants'    => [
                    ['La caféine crée de la concentration même chez quelqu\'un de parfaitement reposé.', false],
                    ['La caféine restaure surtout une vigilance déjà dégradée.', true],
                    ['Prise en fin de journée, elle dégrade le sommeil et donc l\'attention du lendemain.', true],
                    ['Augmenter les doses augmente proportionnellement la concentration.', false],
                ],
            ],
            [
                'id'          => 'n18',
                'level'       => 5,
                'theme'       => 'energie',
                'explanation' => 'La vigilance suit un <b>rythme</b>. Aligner les tâches difficiles sur tes pics coûte infiniment moins d\'effort que de lutter à contretemps.',
                'variants'    => [
                    ['Ta capacité d\'attention est à peu près constante du matin au soir.', false],
                    ['Placer la tâche la plus exigeante sur ton meilleur créneau est un levier majeur.', true],
                    ['Le meilleur créneau de la journée est le même pour tout le monde.', false],
                    ['Le creux de début d\'après-midi est un phénomène courant, pas un manque de volonté.', true],
                ],
            ],
            [
                'id'          => 'n19',
                'level'       => 5,
                'theme'       => 'energie',
                'explanation' => 'L\'attention soutenue <b>s\'érode</b>, et cette érosion se perçoit mal de l\'intérieur. D\'où l\'intérêt de découper, plutôt que de s\'en remettre à son ressenti.',
                'variants'    => [
                    ['Sur une tâche de surveillance longue, les erreurs augmentent avec le temps passé.', true],
                    ['Quand on est motivé, la performance reste stable pendant des heures.', false],
                    ['Découper une longue tâche en blocs limite la chute de performance.', true],
                    ['La baisse de vigilance se ressent nettement au moment où elle survient.', false],
                ],
            ],
            [
                'id'          => 'n20',
                'level'       => 5,
                'theme'       => 'emotion',
                'explanation' => 'L\'ennui <b>informe</b> : tâche trop plate, ou réserves épuisées. Ajuster la difficulté ou prendre une vraie pause traite la cause ; forcer ne traite que le symptôme.',
                'variants'    => [
                    ['L\'ennui pendant une tâche est un signal utile plutôt qu\'un défaut de volonté.', true],
                    ['S\'ennuyer sur une tâche signifie qu\'on manque de discipline.', false],
                    ['L\'ennui signale souvent un décalage entre la difficulté et tes ressources du moment.', true],
                    ['Face à l\'ennui, la seule réponse valable est de forcer davantage.', false],
                ],
            ],
            [
                'id'          => 'n21',
                'level'       => 5,
                'theme'       => 'emotion',
                'explanation' => 'L\'inquiétude tourne dans <b>la même mémoire de travail</b> que la tâche. La décharger — par écrit, ou en la traitant — rend de la place immédiatement.',
                'variants'    => [
                    ['Se répéter « je n\'y arriverai jamais » occupe des ressources dont la tâche a besoin.', true],
                    ['Les ruminations sont sans effet sur la performance tant qu\'on reste devant sa tâche.', false],
                    ['Poser par écrit ce qui inquiète avant de commencer peut libérer de l\'attention.', true],
                    ['Le stress améliore toujours la concentration.', false],
                ],
            ],
            [
                'id'          => 'n22',
                'level'       => 5,
                'theme'       => 'emotion',
                'explanation' => 'Un niveau d\'activation trop élevé se traduit par du zapping. Le faire redescendre avant de commencer coûte une minute et en fait gagner beaucoup.',
                'variants'    => [
                    ['Respirer lentement une minute avant de démarrer facilite l\'entrée en tâche.', true],
                    ['L\'agitation physiologique pousse à chercher une stimulation ailleurs.', true],
                    ['Prendre le temps de se calmer avant de commencer est du temps perdu.', false],
                    ['Allonger l\'expiration aide à faire redescendre l\'activation.', true],
                ],
            ],
            [
                'id'          => 'n23',
                'level'       => 5,
                'theme'       => 'demarrage',
                'explanation' => 'L\'anticipation de l\'effort est plus douloureuse que l\'effort lui-même. Rendre l\'entrée <b>minuscule</b> contourne la résistance — et la motivation vient souvent après le premier pas, pas avant.',
                'variants'    => [
                    ['Se fixer une durée très courte pour démarrer une tâche redoutée est une stratégie inefficace.', false],
                    ['S\'engager sur cinq minutes seulement désamorce souvent la résistance.', true],
                    ['C\'est le démarrage qui coûte le plus cher, pas la poursuite.', true],
                    ['Mieux vaut attendre d\'être motivé pour commencer.', false],
                ],
            ],
            [
                'id'          => 'n24',
                'level'       => 5,
                'theme'       => 'demarrage',
                'explanation' => 'Le flou t\'oblige à décider au moment où l\'énergie manque. Une <b>prochaine action précise</b> supprime purement et simplement cette décision.',
                'variants'    => [
                    ['« Travailler sur le dossier » est une intention aussi actionnable que « rédiger le paragraphe 2 ».', false],
                    ['Plus la prochaine action est précise, moins il reste de place pour l\'hésitation.', true],
                    ['Une tâche formulée de façon floue est une invitation à la procrastination.', true],
                    ['Définir précisément par où commencer relève du perfectionnisme inutile.', false],
                ],
            ],
        ];
    }

    /** Notions d un niveau de connaissance (1, 3 ou 5). */
    public static function forLevel(int $level): array
    {
        return array_values(array_filter(
            self::all(),
            fn (array $n) => $n['level'] === $level
        ));
    }

    /** Notion par identifiant, ou null si inconnue. */
    public static function find(string $id): ?array
    {
        foreach (self::all() as $notion) {
            if ($notion['id'] === $id) {
                return $notion;
            }
        }

        return null;
    }

    public static function count(): int
    {
        return count(self::all());
    }
}
