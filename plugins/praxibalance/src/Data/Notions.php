<?php

namespace Praxis\Plugins\PraxiBalance\Data;

/**
 * La Balance — banque de notions.
 *
 * 32 notions x 4 formulations. La répétition espacée porte sur la NOTION,
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
                'id'          => 'p01',
                'level'       => 1,
                'theme'       => 'tri',
                'explanation' => '<b>Urgent</b> décrit une échéance : ça réclame maintenant. <b>Important</b> décrit une conséquence : ça pèse sur ce qui compte pour toi. Les deux n\'ont rien à voir — et c\'est l\'urgence qui gagne toujours, parce qu\'elle crie plus fort.',
                'variants'    => [
                    ['Une tâche urgente est, par définition, une tâche importante.', false],
                    ['« Urgent » parle d\'échéance, « important » parle de conséquence : ce sont deux mesures différentes.', true],
                    ['Beaucoup de choses urgentes n\'ont aucune conséquence réelle si on ne les fait pas.', true],
                    ['Ce qui réclame ton attention tout de suite mérite ton attention tout de suite.', false],
                ],
            ],
            [
                'id'          => 'p02',
                'level'       => 1,
                'theme'       => 'tri',
                'explanation' => 'Les tâches importantes mais <b>non urgentes</b> — préparer, apprendre, entretenir une relation, réfléchir — sont celles qui changent vraiment quelque chose. Comme rien ne les réclame, elles attendent indéfiniment. Jusqu\'à devenir urgentes, dans la douleur.',
                'variants'    => [
                    ['Ce qui est important sans être urgent finit par se faire tout seul.', false],
                    ['Les tâches importantes et non urgentes sont celles qu\'on repousse le plus facilement.', true],
                    ['Une tâche importante négligée assez longtemps finit souvent par devenir une urgence.', true],
                    ['Tant qu\'une tâche n\'a pas d\'échéance, il n\'y a pas de raison de lui réserver du temps.', false],
                ],
            ],
            [
                'id'          => 'p03',
                'level'       => 1,
                'theme'       => 'tri',
                'explanation' => 'L\'urgence d\'une demande dit surtout quelque chose de <b>celui qui la formule</b>, pas de sa valeur pour toi. « C\'est urgent » est une information sur son planning, pas sur tes priorités.',
                'variants'    => [
                    ['Quand quelqu\'un qualifie sa demande d\'urgente, cela renseigne d\'abord sur son propre planning.', true],
                    ['Si une demande est présentée comme urgente, c\'est qu\'elle l\'est objectivement.', false],
                    ['Demander « urgent pour quand, exactement ? » fait tomber une bonne partie des fausses urgences.', true],
                    ['Questionner l\'urgence d\'une demande est une forme de mauvaise volonté.', false],
                ],
            ],
            [
                'id'          => 'p04',
                'level'       => 1,
                'theme'       => 'reactif',
                'explanation' => 'Une boîte de réception est une liste de tâches <b>écrite par les autres</b>, classée par ordre d\'arrivée. C\'est le contraire d\'un ordre de priorité.',
                'variants'    => [
                    ['Traiter ses messages dans l\'ordre d\'arrivée revient à laisser les autres fixer tes priorités.', true],
                    ['La boîte de réception est un bon point de départ pour organiser sa journée.', false],
                    ['L\'ordre d\'arrivée des demandes n\'a aucun rapport avec leur importance.', true],
                    ['Répondre au fil de l\'eau est la façon la plus efficace de ne rien laisser passer.', false],
                ],
            ],
            [
                'id'          => 'p05',
                'level'       => 1,
                'theme'       => 'reactif',
                'explanation' => 'Commencer par ce qui est <b>facile</b> donne une sensation d\'avancée sans déplacer la ligne. À la fin de la journée, la liste est plus courte et le vrai sujet n\'a pas bougé.',
                'variants'    => [
                    ['Expédier d\'abord les petites tâches faciles permet de se mettre en route.', false],
                    ['Cocher beaucoup de petites lignes peut donner l\'impression d\'avancer sans rien faire avancer.', true],
                    ['La satisfaction de rayer une tâche n\'est pas proportionnelle à son importance.', true],
                    ['Une journée où l\'on a coché beaucoup de tâches est une journée productive.', false],
                ],
            ],
            [
                'id'          => 'p06',
                'level'       => 1,
                'theme'       => 'reactif',
                'explanation' => 'Décider de ses priorités <b>avant</b> d\'ouvrir ses messages, c\'est arriver avec un plan plutôt qu\'avec une page blanche que les autres vont remplir.',
                'variants'    => [
                    ['Choisir sa priorité de la journée avant de consulter ses messages change la journée entière.', true],
                    ['Il vaut mieux prendre connaissance de tout ce qui est arrivé avant de décider quoi faire.', false],
                    ['Arriver avec une intention écrite rend plus difficile de se laisser détourner.', true],
                    ['On ne peut pas fixer ses priorités tant qu\'on ne sait pas ce qu\'on nous demande.', false],
                ],
            ],
            [
                'id'          => 'p07',
                'level'       => 1,
                'theme'       => 'mit',
                'explanation' => 'Quand tout est prioritaire, <b>rien</b> ne l\'est. Une liste de dix priorités est une liste de dix choses, sans ordre — donc sans décision.',
                'variants'    => [
                    ['Avoir dix priorités revient à n\'en avoir aucune.', true],
                    ['Plus on identifie de priorités, mieux on couvre l\'ensemble de ses sujets.', false],
                    ['Une priorité qui n\'exclut rien d\'autre n\'est pas une priorité.', true],
                    ['Classer ses tâches par ordre d\'importance suffit à les traiter dans le bon ordre.', false],
                ],
            ],
            [
                'id'          => 'p08',
                'level'       => 1,
                'theme'       => 'mit',
                'explanation' => 'Une seule tâche décisive par jour, identifiée le matin et protégée : c\'est peu, et c\'est déjà plus que ce que produit une journée subie. <b>Une par jour, tenue, fait deux cents par an.</b>',
                'variants'    => [
                    ['S\'engager sur une seule tâche décisive par jour est trop peu ambitieux.', false],
                    ['Une tâche vraiment importante menée à bien chaque jour transforme une année.', true],
                    ['Protéger un créneau pour cette tâche compte autant que de l\'avoir identifiée.', true],
                    ['Il vaut mieux se fixer plusieurs objectifs quotidiens pour ne pas perdre de temps.', false],
                ],
            ],
            // ── Niveau 3 ──
            [
                'id'          => 'p09',
                'level'       => 3,
                'theme'       => 'cout',
                'explanation' => 'Ton temps est fini. Chaque « oui » est donc un <b>« non » silencieux</b> adressé à tout le reste — y compris à ce que tu n\'as pas encore identifié comme important.',
                'variants'    => [
                    ['Accepter une tâche revient à refuser implicitement tout ce qu\'on aurait pu faire à la place.', true],
                    ['Tant qu\'on arrive à tout caser, accepter une demande de plus ne coûte rien.', false],
                    ['Le vrai coût d\'une tâche, c\'est ce qu\'elle t\'empêche de faire.', true],
                    ['Une tâche courte est une tâche sans conséquence sur le reste.', false],
                ],
            ],
            [
                'id'          => 'p10',
                'level'       => 3,
                'theme'       => 'refus',
                'explanation' => 'Refuser une demande n\'est pas refuser la <b>personne</b>. Dire « je ne peux pas le faire cette semaine, et voilà pourquoi » traite l\'un sans abîmer l\'autre.',
                'variants'    => [
                    ['Décliner une demande est nécessairement mal reçu par celui qui la formule.', false],
                    ['Refuser une tâche en expliquant sur quoi tu es engagé préserve la relation.', true],
                    ['Un refus argumenté est mieux accepté qu\'un oui suivi d\'un retard.', true],
                    ['Accepter puis livrer en retard est moins coûteux pour la relation que de refuser d\'emblée.', false],
                ],
            ],
            [
                'id'          => 'p11',
                'level'       => 3,
                'theme'       => 'refus',
                'explanation' => 'Un « oui » réflexe se paie plus tard, avec intérêts. Répondre <b>« je te dis ça d\'ici ce soir »</b> laisse le temps de vérifier ce que ça déplace.',
                'variants'    => [
                    ['S\'accorder un délai avant de répondre à une demande évite les engagements impossibles.', true],
                    ['Répondre immédiatement à une sollicitation est une marque de professionnalisme.', false],
                    ['La plupart des engagements intenables ont été pris en quelques secondes.', true],
                    ['Différer sa réponse à une demande est une façon de fuir la décision.', false],
                ],
            ],
            [
                'id'          => 'p12',
                'level'       => 3,
                'theme'       => 'refus',
                'explanation' => 'Refuser tôt est un <b>service</b> : ça laisse à l\'autre le temps de trouver une solution. Refuser tard, ou ne pas livrer, le met en difficulté.',
                'variants'    => [
                    ['Mieux vaut refuser dès le départ que de se désister au dernier moment.', true],
                    ['Accepter pour ne pas décevoir sur le moment est la solution la plus prudente.', false],
                    ['Un refus annoncé tôt laisse à l\'autre le temps de s\'organiser autrement.', true],
                    ['Tant qu\'on n\'a pas dit non explicitement, on garde toutes les options ouvertes.', false],
                ],
            ],
            [
                'id'          => 'p13',
                'level'       => 3,
                'theme'       => 'delegation',
                'explanation' => 'Une tâche que quelqu\'un d\'autre peut faire <b>correctement</b> — même moins bien que toi — n\'a pas à occuper ton temps si ce temps est mieux employé ailleurs.',
                'variants'    => [
                    ['Il faut confier une tâche seulement si l\'autre la fera aussi bien que soi.', false],
                    ['« Suffisamment bien fait par quelqu\'un d\'autre » vaut mieux que « parfait mais jamais commencé ».', true],
                    ['Garder une tâche parce qu\'on est plus rapide à la faire soi-même est un mauvais calcul sur la durée.', true],
                    ['Expliquer une tâche coûte toujours plus cher que de la faire.', false],
                ],
            ],
            [
                'id'          => 'p14',
                'level'       => 3,
                'theme'       => 'delegation',
                'explanation' => 'Avant de traiter une demande, une question à trois secondes : <b>est-ce vraiment à moi de le faire ?</b> Beaucoup de tâches atterrissent sur un bureau par habitude, pas par logique.',
                'variants'    => [
                    ['Se demander si une tâche relève vraiment de soi devrait précéder le fait de s\'y mettre.', true],
                    ['Si une demande t\'est adressée, c\'est qu\'elle te revient.', false],
                    ['Beaucoup de tâches arrivent sur un bureau par habitude plutôt que par compétence.', true],
                    ['Renvoyer une demande vers la bonne personne est une façon de se défausser.', false],
                ],
            ],
            [
                'id'          => 'p15',
                'level'       => 3,
                'theme'       => 'inacheve',
                'explanation' => 'Chaque chantier ouvert consomme de l\'attention même quand tu n\'y travailles pas. <b>Finir</b> libère bien plus que commencer ne rapporte.',
                'variants'    => [
                    ['Mener trois chantiers de front les fait avancer plus vite que de les traiter l\'un après l\'autre.', false],
                    ['Un projet commencé mais non terminé continue d\'occuper de la place mentale.', true],
                    ['Terminer un dossier en cours vaut souvent mieux que d\'en ouvrir un nouveau.', true],
                    ['Tant qu\'on progresse un peu partout, le nombre de chantiers ouverts n\'a pas d\'importance.', false],
                ],
            ],
            [
                'id'          => 'p16',
                'level'       => 3,
                'theme'       => 'inacheve',
                'explanation' => 'Limiter volontairement le nombre de sujets en cours accélère l\'ensemble. Moins de chantiers ouverts, c\'est moins de reprises de contexte et des livraisons <b>plus rapprochées</b>.',
                'variants'    => [
                    ['Se fixer une limite au nombre de dossiers ouverts en même temps accélère le tout.', true],
                    ['Plus on lance de sujets en parallèle, plus on livre vite.', false],
                    ['Réduire le nombre de tâches en cours réduit le temps passé à se remettre dedans.', true],
                    ['Attendre d\'avoir fini avant de commencer autre chose fait perdre du temps.', false],
                ],
            ],
            // ── Niveau 5 ──
            [
                'id'          => 'p17',
                'level'       => 5,
                'theme'       => 'effort',
                'explanation' => 'Sur beaucoup de sujets, une <b>petite part</b> de l\'effort produit l\'essentiel du résultat. Repérer laquelle change tout ; s\'acharner sur le reste ne rapporte presque rien.',
                'variants'    => [
                    ['Sur la plupart des sujets, une petite partie du travail produit l\'essentiel du résultat.', true],
                    ['Le résultat obtenu est à peu près proportionnel au temps investi.', false],
                    ['Identifier les quelques actions qui pèsent vraiment vaut mieux que de tout traiter également.', true],
                    ['Traiter toutes ses tâches avec le même soin est la marque du sérieux.', false],
                ],
            ],
            [
                'id'          => 'p18',
                'level'       => 5,
                'theme'       => 'effort',
                'explanation' => 'Les dernières finitions coûtent souvent aussi cher que tout le reste, pour un gain que <b>personne ne remarque</b>. Le bon niveau de qualité est celui qu\'exige l\'usage, pas celui que permettrait le temps.',
                'variants'    => [
                    ['Pousser un travail au-delà de ce que l\'usage exige coûte cher pour un gain invisible.', true],
                    ['Un travail doit toujours être mené au meilleur niveau dont on est capable.', false],
                    ['Le niveau de finition devrait se décider avant de commencer, pas en cours de route.', true],
                    ['Il n\'y a pas de mal à peaufiner tant qu\'on a le temps.', false],
                ],
            ],
            [
                'id'          => 'p19',
                'level'       => 5,
                'theme'       => 'effort',
                'explanation' => 'Continuer un projet parce qu\'on y a déjà investi beaucoup, c\'est laisser le <b>passé</b> décider à la place du futur. Ce qui est dépensé est dépensé, quel que soit le choix.',
                'variants'    => [
                    ['Le temps déjà investi dans un projet est une bonne raison de le poursuivre.', false],
                    ['La seule question valable est : à partir d\'aujourd\'hui, est-ce le meilleur usage de mon temps ?', true],
                    ['Abandonner un chantier mal engagé peut être la décision la plus rentable.', true],
                    ['Renoncer après avoir beaucoup investi revient à gaspiller tout ce travail.', false],
                ],
            ],
            [
                'id'          => 'p20',
                'level'       => 5,
                'theme'       => 'estimation',
                'explanation' => 'Nous sous-estimons presque systématiquement les durées, même en connaissant ce travers, même sur des tâches déjà faites. La parade n\'est pas de mieux estimer : c\'est de regarder <b>combien de temps ça a pris la dernière fois</b>.',
                'variants'    => [
                    ['On sous-estime la durée de ses tâches même quand on sait qu\'on la sous-estime.', true],
                    ['Avec l\'expérience, on finit par estimer justement le temps que prend une tâche.', false],
                    ['Se fonder sur la durée réelle des fois précédentes est plus fiable que sa propre estimation.', true],
                    ['Une estimation faite par la personne qui va exécuter la tâche est la plus exacte.', false],
                ],
            ],
            [
                'id'          => 'p21',
                'level'       => 5,
                'theme'       => 'estimation',
                'explanation' => 'Une journée pleine à ras bord n\'a aucune marge : le premier imprévu fait tomber tout le reste. <b>Planifier moins</b> que le temps disponible n\'est pas de la paresse, c\'est de la robustesse.',
                'variants'    => [
                    ['Laisser du temps non planifié dans sa journée est une perte de productivité.', false],
                    ['Une journée planifiée à ras bord s\'effondre au premier imprévu.', true],
                    ['Prévoir de la marge permet d\'absorber l\'imprévu sans sacrifier l\'essentiel.', true],
                    ['Bien organiser sa journée, c\'est occuper chaque créneau disponible.', false],
                ],
            ],
            [
                'id'          => 'p22',
                'level'       => 5,
                'theme'       => 'estimation',
                'explanation' => 'Une tâche dont la durée est <b>inconnue</b> ne se planifie pas : elle se découpe. Le premier morceau sert à savoir de quoi il retourne.',
                'variants'    => [
                    ['Face à une tâche dont on ignore la durée, mieux vaut en découper un premier morceau.', true],
                    ['Une tâche floue doit être planifiée en bloc, quitte à y consacrer la journée.', false],
                    ['Le premier morceau d\'une tâche inconnue sert surtout à en mesurer l\'ampleur.', true],
                    ['Tant qu\'on ne sait pas combien de temps ça prendra, il vaut mieux attendre.', false],
                ],
            ],
            [
                'id'          => 'p23',
                'level'       => 5,
                'theme'       => 'revue',
                'explanation' => 'Une liste de priorités qu\'on ne relit pas devient une liste de <b>regrets</b>. Cinq minutes de revue en fin de semaine valent mieux qu\'un système parfait jamais consulté.',
                'variants'    => [
                    ['Une liste de priorités jamais relue ne sert à rien.', true],
                    ['L\'important est d\'avoir un bon système, pas de le consulter souvent.', false],
                    ['Un point court et régulier vaut mieux qu\'une organisation élaborée et abandonnée.', true],
                    ['Revoir ses priorités trop souvent empêche de s\'y tenir.', false],
                ],
            ],
            [
                'id'          => 'p24',
                'level'       => 5,
                'theme'       => 'revue',
                'explanation' => 'Les priorités <b>changent</b>, et c\'est normal. S\'y accrocher parce qu\'on les a écrites, c\'est confondre la constance avec l\'entêtement.',
                'variants'    => [
                    ['Changer de priorité quand le contexte change est un signe de lucidité.', true],
                    ['Modifier ses priorités en cours de route est un manque de rigueur.', false],
                    ['Une priorité fixée il y a un mois mérite d\'être réexaminée aujourd\'hui.', true],
                    ['Une fois ses priorités posées, il faut s\'y tenir quoi qu\'il arrive.', false],
                ],
            ],
            // ── Niveau 7 ──
            [
                'id'          => 'p25',
                'level'       => 7,
                'theme'       => 'negociation',
                'explanation' => 'Face à deux demandes incompatibles, la décision ne t\'appartient pas toujours. <b>Renvoyer l\'arbitrage</b> — « les deux ne tiennent pas, laquelle je décale ? » — est souvent la seule sortie honnête.',
                'variants'    => [
                    ['Quand deux demandes ne tiennent pas ensemble, il revient au demandeur d\'arbitrer.', true],
                    ['Accepter les deux et faire de son mieux est la réponse la plus professionnelle.', false],
                    ['Rendre visible un conflit de priorités vaut mieux que de le subir en silence.', true],
                    ['Signaler qu\'on ne peut pas tout faire revient à avouer son incompétence.', false],
                ],
            ],
            [
                'id'          => 'p26',
                'level'       => 7,
                'theme'       => 'negociation',
                'explanation' => 'Une demande a presque toujours <b>trois leviers</b> : le contenu, la date, le niveau de finition. Quand tu ne peux pas refuser, il reste souvent l\'un des trois à négocier.',
                'variants'    => [
                    ['Quand on ne peut pas refuser une tâche, on peut souvent en négocier l\'ampleur ou la date.', true],
                    ['Une demande se prend ou se refuse : il n\'y a rien entre les deux.', false],
                    ['Proposer une version allégée dans le délai vaut mieux qu\'une version complète en retard.', true],
                    ['Discuter le périmètre d\'une demande, c\'est déjà commencer à s\'en défausser.', false],
                ],
            ],
            [
                'id'          => 'p27',
                'level'       => 7,
                'theme'       => 'urgence_collective',
                'explanation' => 'Dans une équipe, une urgence non discutée se propage : chacun la relaie à son voisin. <b>Vérifier l\'échéance réelle</b> à l\'entrée arrête la chaîne.',
                'variants'    => [
                    ['Une fausse urgence transmise sans être vérifiée contamine toute une chaîne de personnes.', true],
                    ['Relayer immédiatement une demande urgente est la meilleure façon de servir l\'équipe.', false],
                    ['Demander l\'échéance réelle avant de transmettre évite de propager une urgence inventée.', true],
                    ['Il n\'appartient pas à celui qui transmet de questionner l\'urgence.', false],
                ],
            ],
            [
                'id'          => 'p28',
                'level'       => 7,
                'theme'       => 'urgence_collective',
                'explanation' => 'Une interruption « rapide » ne l\'est jamais pour celui qui la subit : elle coûte la tâche en cours <b>plus</b> la remise en route. Grouper ses demandes est une politesse concrète.',
                'variants'    => [
                    ['Grouper ses questions à un collègue plutôt que de le solliciter au fil de l\'eau lui fait gagner du temps.', true],
                    ['Une question rapide ne coûte que le temps de la poser.', false],
                    ['Interrompre quelqu\'un lui coûte sa tâche en cours en plus du temps de la réponse.', true],
                    ['Poser ses questions au fur et à mesure évite de les oublier, et c\'est le principal.', false],
                ],
            ],
            [
                'id'          => 'p29',
                'level'       => 7,
                'theme'       => 'visibilite',
                'explanation' => 'Ce que les autres ne voient pas, ils ne peuvent pas le respecter. Rendre visible <b>ce sur quoi tu es engagé</b> déplace la discussion des personnes vers les arbitrages.',
                'variants'    => [
                    ['Rendre visibles ses engagements en cours facilite la discussion sur les priorités.', true],
                    ['Il vaut mieux garder pour soi sa charge de travail, sous peine de paraître débordé.', false],
                    ['Une charge de travail invisible est une charge qui ne sera jamais arbitrée.', true],
                    ['Si personne ne demande, c\'est que la charge est acceptable.', false],
                ],
            ],
            [
                'id'          => 'p30',
                'level'       => 7,
                'theme'       => 'visibilite',
                'explanation' => 'Annoncer une difficulté <b>tôt</b> laisse des options ouvertes. L\'annoncer la veille de l\'échéance n\'en laisse aucune, et transforme un problème gérable en incident.',
                'variants'    => [
                    ['Signaler tôt qu\'une échéance est menacée laisse le temps de réagir.', true],
                    ['Mieux vaut attendre d\'être certain de ne pas y arriver avant d\'alerter.', false],
                    ['Une alerte tardive transforme un problème d\'organisation en incident.', true],
                    ['Alerter avant d\'avoir tout tenté, c\'est se décharger sur les autres.', false],
                ],
            ],
            [
                'id'          => 'p31',
                'level'       => 7,
                'theme'       => 'energie',
                'explanation' => 'Toutes les heures ne se valent pas. Placer une décision difficile sur un <b>creux d\'énergie</b>, c\'est la prendre mal — ou ne pas la prendre du tout.',
                'variants'    => [
                    ['Placer une tâche exigeante sur son meilleur créneau vaut mieux que de la placer « quand il reste du temps ».', true],
                    ['Une heure de travail en vaut une autre : seul le nombre d\'heures compte.', false],
                    ['Les décisions difficiles prises en fin de journée sont souvent moins bonnes.', true],
                    ['Il faut réserver ses meilleurs créneaux aux tâches qui traînent depuis longtemps.', false],
                ],
            ],
            [
                'id'          => 'p32',
                'level'       => 7,
                'theme'       => 'energie',
                'explanation' => 'Décider fatigue. Après une longue série d\'arbitrages, on choisit <b>par défaut</b> : le plus facile, le plus récent, ou rien. D\'où l\'intérêt de décider peu, et tôt.',
                'variants'    => [
                    ['Enchaîner les décisions dégrade la qualité des dernières.', true],
                    ['La capacité à décider reste constante tout au long de la journée.', false],
                    ['Réduire le nombre de décisions quotidiennes préserve celles qui comptent.', true],
                    ['Plus on a l\'habitude de décider, moins les décisions coûtent.', false],
                ],
            ],
        ];
    }

    /** Notions d un niveau de connaissance (1, 3, 5 ou 7). */
    public static function forLevel(int $level): array
    {
        return array_values(array_filter(
            self::all(),
            fn (array $x) => $x['level'] === $level
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
