<?php

namespace Praxis\Plugins\PraxiZen\Data;

/**
 * Bibliothèque de « tips du jour » — Le Refuge Intérieur (gestion du stress).
 *
 * Chaque tip : un principe de fond sérieux + une micro-action applicable le
 * jour même. Niveau de preuve :
 *   solide      → méta-analyses / essais contrôlés randomisés convergents
 *   prometteur  → résultats encourageants, à confirmer
 *   emergent    → piste théorique ou données préliminaires
 *
 * Le DailyTipService en sélectionne un par jour, par rotation exhaustive.
 */
class Tips
{
    public static function all(): array
    {
        return [
            [
                'id'       => 'zen-soupir-physiologique',
                'title'    => 'Le soupir physiologique : le frein d\'urgence du stress',
                'theme'    => 'Respiration',
                'evidence' => 'solide',
                'insight'  => "Deux inspirations nasales successives puis une longue expiration buccale réinflatent les alvéoles et activent le système parasympathique. C'est la voie la plus rapide pour faire redescendre la tension physiologique en quelques cycles.",
                'action'   => "Avant ta prochaine réunion sous tension, fais 3 soupirs physiologiques : inspire par le nez, re-inspire un petit coup, puis expire lentement par la bouche.",
                'source'   => 'Balban et al., 2023, Cell Reports Medicine (ECR, n=111)',
                'tags'     => ['stress', 'respiration', 'anxiete'],
            ],
            [
                'id'       => 'zen-coherence-cardiaque',
                'title'    => 'Respirer à 6 cycles/minute synchronise cœur et cerveau',
                'theme'    => 'Respiration',
                'evidence' => 'prometteur',
                'insight'  => "Respirer environ 5 secondes à l'inspiration, 5 à l'expiration (≈6 respirations/minute) augmente la variabilité de la fréquence cardiaque, un marqueur de bonne régulation du stress et de récupération.",
                'action'   => "Aujourd'hui, programme 5 minutes de respiration 5-5 (inspire 5 s, expire 5 s). Idéalement le matin, avant la première sollicitation.",
                'source'   => 'Lehrer & Gevirtz, 2014, Frontiers in Psychology (revue HRV biofeedback)',
                'tags'     => ['stress', 'respiration', 'recuperation'],
            ],
            [
                'id'       => 'zen-etiquetage-emotion',
                'title'    => 'Nommer une émotion réduit son intensité',
                'theme'    => 'Recadrage cognitif',
                'evidence' => 'solide',
                'insight'  => "Mettre des mots sur ce qu'on ressent (« affect labeling ») diminue l'activité de l'amygdale et l'intensité ressentie. Nommer, ce n'est pas ruminer : c'est passer de la submersion à l'observation.",
                'action'   => "Quand une tension monte aujourd'hui, complète mentalement : « Là, je ressens… » avec un mot précis (agacement, inquiétude, déception). Une phrase, pas plus.",
                'source'   => 'Lieberman et al., 2007, Psychological Science',
                'tags'     => ['stress', 'emotions', 'pleine-conscience'],
            ],
            [
                'id'       => 'zen-defusion',
                'title'    => 'Tes pensées ne sont pas des faits',
                'theme'    => 'Recadrage cognitif',
                'evidence' => 'prometteur',
                'insight'  => "La défusion cognitive (ACT) consiste à prendre du recul sur une pensée anxieuse en la traitant comme un événement mental, pas comme une vérité. « Je vais échouer » devient « je remarque que j'ai la pensée que je vais échouer ».",
                'action'   => "Repère une pensée stressante récurrente et reformule-la : « Je remarque que j'ai la pensée que… ». Observe si la charge baisse d'un cran.",
                'source'   => 'Hayes et al., ACT ; méta-analyses A-Tjak 2015',
                'tags'     => ['stress', 'rumination', 'cognitif'],
            ],
            [
                'id'       => 'zen-nature-20min',
                'title'    => '20 minutes dehors font chuter le cortisol',
                'theme'    => 'Récupération',
                'evidence' => 'prometteur',
                'insight'  => "Passer un moment dans un environnement naturel réduit mesurablement le cortisol salivaire. L'effet par minute est maximal autour de 20-30 minutes : pas besoin d'une grande randonnée.",
                'action'   => "Cale aujourd'hui une pause de 20 minutes dehors, sans téléphone — un parc, des arbres, suffisent. Marche lentement.",
                'source'   => 'Hunter et al., 2019, Frontiers in Psychology',
                'tags'     => ['stress', 'recuperation', 'nature'],
            ],
            [
                'id'       => 'zen-exercice-anxiete',
                'title'    => "L'activité physique vaut un traitement de fond léger",
                'theme'    => 'Récupération',
                'evidence' => 'solide',
                'insight'  => "L'exercice régulier réduit significativement l'anxiété et les symptômes dépressifs. Même une marche rapide compte : l'effet est dose-dépendant mais commence bas.",
                'action'   => "Ajoute 15 minutes de marche rapide à ta journée. Si possible, juste après le moment le plus stressant prévu.",
                'source'   => 'Singh et al., 2023, British Journal of Sports Medicine (méta-revue)',
                'tags'     => ['stress', 'exercice', 'humeur'],
            ],
            [
                'id'       => 'zen-scommencer-difficulte',
                'title'    => 'Le scan corporel coupe le pilote automatique',
                'theme'    => 'Pleine conscience',
                'evidence' => 'prometteur',
                'insight'  => "Balayer mentalement son corps de la tête aux pieds ramène l'attention au présent et désamorce la spirale anticipatoire du stress. C'est un ancrage sensoriel, pas une relaxation à obtenir de force.",
                'action'   => "Fais un scan corporel de 3 minutes : porte ton attention successivement sur le front, les épaules, le ventre, les mains, les pieds. Constate sans corriger.",
                'source'   => 'MBSR, Kabat-Zinn ; méta-analyse Khoury 2015',
                'tags'     => ['stress', 'pleine-conscience', 'ancrage'],
            ],
            [
                'id'       => 'zen-sommeil-regularite',
                'title'    => "L'heure de lever régulière vaut mieux que l'heure de coucher",
                'theme'    => 'Sommeil',
                'evidence' => 'solide',
                'insight'  => "La régularité du sommeil prédit mieux la santé et l'humeur que la durée seule. Le levier le plus stable est une heure de réveil constante, même le week-end : elle réancre l'horloge biologique.",
                'action'   => "Fixe une heure de lever unique pour les 7 prochains jours, week-end compris. Note comment tu te sens à J+3.",
                'source'   => 'Windred et al., 2023, Sleep (régularité > durée)',
                'tags'     => ['stress', 'sommeil', 'recuperation'],
            ],
            [
                'id'       => 'zen-lumiere-matin',
                'title'    => 'La lumière du matin règle ton stress du soir',
                'theme'    => 'Sommeil',
                'evidence' => 'prometteur',
                'insight'  => "Une exposition à la lumière vive le matin avance l'horloge circadienne, améliore l'éveil diurne et la qualité du sommeil — donc la résilience au stress le lendemain.",
                'action'   => "Dans l'heure qui suit ton réveil, expose-toi 10 minutes à la lumière extérieure (même par temps gris, c'est plus lumineux qu'en intérieur).",
                'source'   => 'Blume et al., 2019, Somnologie (revue lumière & circadien)',
                'tags'     => ['sommeil', 'energie', 'circadien'],
            ],
            [
                'id'       => 'zen-rumination-fenetre',
                'title'    => 'Donne un rendez-vous à tes ruminations',
                'theme'    => 'Recadrage cognitif',
                'evidence' => 'prometteur',
                'insight'  => "Le « worry time » : reporter volontairement les inquiétudes à un créneau fixe (ex. 18 h, 15 min) réduit leur emprise dans la journée. Le cerveau accepte de lâcher car il sait qu'un moment est prévu.",
                'action'   => "Choisis un créneau « inquiétudes » de 15 min aujourd'hui. Quand une rumination surgit avant, note-la et renvoie-la au créneau.",
                'source'   => 'Borkovec et al., 1983 ; TCC de l\'anxiété généralisée',
                'tags'     => ['stress', 'rumination', 'cognitif'],
            ],
            [
                'id'       => 'zen-auto-compassion',
                'title'    => 'Se parler comme à un ami amortit le stress',
                'theme'    => 'Recadrage cognitif',
                'evidence' => 'solide',
                'insight'  => "L'auto-compassion (se traiter avec bienveillance dans l'échec plutôt qu'avec dureté) est liée à moins d'anxiété et plus de résilience. Ce n'est pas de la complaisance : c'est une base plus stable pour agir.",
                'action'   => "Face à une contrariété aujourd'hui, demande-toi : « Qu'est-ce que je dirais à un ami dans cette situation ? » Puis dis-le-toi.",
                'source'   => 'Neff ; méta-analyse Ferrari et al., 2019 (g≈0,75)',
                'tags'     => ['stress', 'auto-compassion', 'resilience'],
            ],
            [
                'id'       => 'zen-mono-tache',
                'title'    => "Le multitâche fabrique du stress, pas de la vitesse",
                'theme'    => 'Pleine conscience',
                'evidence' => 'prometteur',
                'insight'  => "Jongler entre tâches augmente la charge cognitive et le sentiment de débordement sans gain réel de productivité. L'esprit divisé est aussi un esprit moins serein.",
                'action'   => "Choisis une plage de 25 minutes en mono-tâche aujourd'hui : un seul onglet, téléphone hors de vue. Observe le niveau de tension à la fin.",
                'source'   => 'Ophir, Nass & Wagner, 2009, PNAS',
                'tags'     => ['stress', 'attention', 'focus'],
            ],
            [
                'id'       => 'zen-stress-mindset',
                'title'    => 'Voir le stress comme un allié change ses effets',
                'theme'    => 'Recadrage cognitif',
                'evidence' => 'prometteur',
                'insight'  => "Réinterpréter les signes physiques du stress (cœur qui bat, mains moites) comme une mobilisation utile de l'énergie, plutôt qu'un danger, améliore la performance et la réponse cardiovasculaire.",
                'action'   => "Avant un moment à enjeu, dis-toi : « Mon corps se prépare à être performant. » Recadre l'accélération comme du carburant.",
                'source'   => 'Jamieson et al., 2012, Journal of Experimental Psychology',
                'tags'     => ['stress', 'performance', 'mindset'],
            ],
            [
                'id'       => 'zen-relachement-musculaire',
                'title'    => 'Relâcher le corps relâche le mental',
                'theme'    => 'Récupération',
                'evidence' => 'solide',
                'insight'  => "La relaxation musculaire progressive (contracter puis relâcher chaque groupe musculaire) réduit l'anxiété en agissant sur la boucle corps→cerveau. Le relâchement physique précède souvent l'apaisement mental.",
                'action'   => "Fais 5 minutes : contracte 5 s puis relâche 10 s, en remontant des pieds aux épaules. Note le contraste tension/détente.",
                'source'   => 'Jacobson ; méta-analyses sur la PMR et l\'anxiété',
                'tags'     => ['stress', 'detente', 'corps'],
            ],
            [
                'id'       => 'zen-gratitude-soir',
                'title'    => 'Trois bonnes choses, chaque soir',
                'theme'    => 'Pleine conscience',
                'evidence' => 'solide',
                'insight'  => "Noter chaque soir trois choses qui se sont bien passées réoriente l'attention, biaisée vers le négatif, et améliore l'humeur sur la durée. La variété compte : des choses nouvelles chaque jour.",
                'action'   => "Ce soir, note 3 choses positives du jour et, pour une, ton rôle dedans. Demain, vise-en des différentes.",
                'source'   => 'Seligman et al., 2005 ; méta-analyse gratitude 2023',
                'tags'     => ['stress', 'humeur', 'gratitude'],
            ],
            [
                'id'       => 'zen-pause-deconnexion',
                'title'    => "Les micro-pauses rechargent l'attention",
                'theme'    => 'Récupération',
                'evidence' => 'prometteur',
                'insight'  => "De courtes pauses régulières, surtout actives ou en mouvement, restaurent la vigilance et réduisent l'accumulation de fatigue mentale mieux qu'une longue pause unique en fin de journée.",
                'action'   => "Programme une micro-pause de 5 min toutes les 60-90 min aujourd'hui : lève-toi, regarde au loin, bouge. Sans écran.",
                'source'   => 'Albulescu et al., 2022, PLOS ONE (méta-analyse micro-pauses)',
                'tags'     => ['stress', 'recuperation', 'energie'],
            ],
        ];
    }
}
