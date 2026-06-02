<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Questions groupées par famille pour une UX fluide avec transitions :
 *
 * Famille 1 — Conscience de soi (Q1-20, idx 0-19)
 *   Dim 1  : Connaissance de soi       idx 0-4
 *   Dim 4  : Confiance en soi          idx 5-9
 *   Dim 9  : Expression des sentiments idx 10-14
 *   Dim 16 : Contrôle des impulsions   idx 15-19
 *
 * Famille 2 — Régulation émotionnelle (Q21-50, idx 20-49)
 *   Dim 2  : Gestion du stress         idx 20-24
 *   Dim 3  : Gestion de la colère      idx 25-29
 *   Dim 5  : Auto-motivation           idx 30-34
 *   Dim 6  : Optimisme                 idx 35-39
 *   Dim 7  : Résilience                idx 40-44
 *   Dim 8  : Flexibilité               idx 45-49
 *
 * Famille 3 — Relations & Communication (Q51-70, idx 50-69)
 *   Dim 10 : Assertivité               idx 50-54
 *   Dim 11 : Empathie                  idx 55-59
 *   Dim 12 : Tact                      idx 60-64
 *   Dim 13 : Gestion de la diversité   idx 65-69
 *
 * Famille 4 — Leadership émotionnel (Q71-80, idx 70-79)
 *   Dim 14 : Motiver les autres        idx 70-74
 *   Dim 15 : Gestion des conflits      idx 75-79
 */

class PE_Calculator {

    public static function get_dimensions() {
        return array(
            1  => array(
                'label'       => 'Connaissance de soi',
                'famille'     => 1,
                'questions'   => array(0,1,2,3,4),
                'description' => 'Votre capacité à identifier vos émotions au moment où elles surgissent et à comprendre ce qui les déclenche. C\'est le fondement de toute intelligence émotionnelle : sans cette conscience intérieure, il est difficile de se réguler ou d\'agir avec discernement.',
            ),
            4  => array(
                'label'       => 'Confiance en soi',
                'famille'     => 1,
                'questions'   => array(5,6,7,8,9),
                'description' => 'Votre sentiment de légitimité face aux défis, votre capacité à exprimer vos opinions et à prendre des décisions sans chercher constamment la validation des autres. Une confiance solide vous permet d\'avancer même dans l\'incertitude.',
            ),
            9  => array(
                'label'       => 'Expression des sentiments',
                'famille'     => 1,
                'questions'   => array(10,11,12,13,14),
                'description' => 'Votre aptitude à mettre des mots sur ce que vous ressentez et à le partager quand c\'est utile. Savoir exprimer ses émotions — sans les étouffer ni les dramatiser — est essentiel pour des relations authentiques et une bonne hygiène émotionnelle.',
            ),
            16 => array(
                'label'       => 'Contrôle des impulsions',
                'famille'     => 1,
                'questions'   => array(15,16,17,18,19),
                'description' => 'Votre capacité à marquer une pause avant d\'agir ou de répondre, même sous l\'effet d\'une émotion forte. Ce frein interne vous évite les décisions précipitées, les paroles regrettables et les comportements que vous auriez voulu retenir.',
            ),
            2  => array(
                'label'       => 'Gestion du stress',
                'famille'     => 2,
                'questions'   => array(20,21,22,23,24),
                'description' => 'Votre capacité à rester efficace et lucide sous pression. Cela englobe à la fois les stratégies que vous utilisez pour réduire la tension et votre aptitude à ne pas laisser le stress parasiter vos pensées au-delà de la situation qui l\'a déclenché.',
            ),
            3  => array(
                'label'       => 'Gestion de la colère',
                'famille'     => 2,
                'questions'   => array(25,26,27,28,29),
                'description' => 'Votre façon de traverser la frustration et la colère sans vous laisser submerger. Il ne s\'agit pas de réprimer cette émotion — qui est légitime — mais de choisir consciemment comment vous l\'exprimez, pour qu\'elle ne nuise ni à vos relations ni à vos décisions.',
            ),
            5  => array(
                'label'       => 'Auto-motivation',
                'famille'     => 2,
                'questions'   => array(30,31,32,33,34),
                'description' => 'Votre capacité à vous mettre en mouvement et à maintenir votre élan sans dépendre de récompenses extérieures ou d\'une supervision constante. C\'est cette flamme intérieure qui vous permet de rebondir après un échec et de rester investi dans la durée.',
            ),
            6  => array(
                'label'       => 'Optimisme',
                'famille'     => 2,
                'questions'   => array(35,36,37,38,39),
                'description' => 'Votre tendance à anticiper le possible plutôt que le pire et à voir les obstacles comme des étapes plutôt que des murs. L\'optimisme n\'est pas de la naïveté : c\'est une orientation mentale qui influence directement votre énergie, votre persévérance et la qualité de vos relations.',
            ),
            7  => array(
                'label'       => 'Résilience',
                'famille'     => 2,
                'questions'   => array(40,41,42,43,44),
                'description' => 'Votre capacité à absorber les chocs — échecs, pertes, changements brutaux — et à vous reconstruire. La résilience ne signifie pas ne pas souffrir, mais avoir les ressources pour traverser l\'adversité sans s\'y noyer et en ressortir transformé.',
            ),
            8  => array(
                'label'       => 'Flexibilité',
                'famille'     => 2,
                'questions'   => array(45,46,47,48,49),
                'description' => 'Votre aptitude à adapter vos façons de faire, de penser et de réagir face à de nouvelles situations ou informations. Dans un monde en mouvement, la flexibilité est ce qui vous permet de rester pertinent et serein face à l\'incertitude.',
            ),
            10 => array(
                'label'       => 'Assertivité',
                'famille'     => 3,
                'questions'   => array(50,51,52,53,54),
                'description' => 'Votre capacité à exprimer vos besoins, défendre vos limites et dire non — sans agressivité, mais sans vous effacer. L\'assertivité est l\'équilibre entre la passivité (se taire) et l\'agressivité (imposer) : c\'est le respect de soi en actes.',
            ),
            11 => array(
                'label'       => 'Empathie',
                'famille'     => 3,
                'questions'   => array(55,56,57,58,59),
                'description' => 'Votre capacité à percevoir et comprendre ce que ressent l\'autre — même quand il ne l\'exprime pas. L\'empathie nourrit la confiance, désamorce les tensions et vous permet d\'adapter votre communication à l\'état émotionnel de votre interlocuteur.',
            ),
            12 => array(
                'label'       => 'Tact',
                'famille'     => 3,
                'questions'   => array(60,61,62,63,64),
                'description' => 'Votre art de dire les choses au bon moment, de la bonne manière, à la bonne personne. Le tact ne consiste pas à édulcorer la vérité, mais à la formuler avec assez de sensibilité pour qu\'elle soit entendue plutôt que rejetée.',
            ),
            13 => array(
                'label'       => 'Gestion de la diversité',
                'famille'     => 3,
                'questions'   => array(65,66,67,68,69),
                'description' => 'Votre aisance à travailler avec des personnes aux valeurs, cultures ou façons de penser très différentes des vôtres. Cette compétence reflète votre capacité à remettre en question vos propres références et à voir la diversité comme une source d\'enrichissement.',
            ),
            14 => array(
                'label'       => 'Motiver les autres',
                'famille'     => 4,
                'questions'   => array(70,71,72,73,74),
                'description' => 'Votre capacité à insuffler de l\'élan autour de vous — en repérant ce qui donne du sens à chacun, en valorisant les efforts et en aidant les autres à se reconnecter à leurs ressources. C\'est l\'une des compétences les plus puissantes du leadership humain.',
            ),
            15 => array(
                'label'       => 'Gestion des conflits',
                'famille'     => 4,
                'questions'   => array(75,76,77,78,79),
                'description' => 'Votre façon d\'aborder les désaccords et les tensions interpersonnelles. Gérer un conflit ne signifie pas l\'éviter — c\'est savoir rester calme, distinguer le problème de la personne, et chercher des solutions qui respectent les deux parties.',
            ),
        );
    }

    public static function get_familles() {
        return array(
            1 => array( 'label' => 'Conscience de soi',         'emoji' => '🧠' ),
            2 => array( 'label' => 'Régulation émotionnelle',   'emoji' => '⚡' ),
            3 => array( 'label' => 'Relations & Communication', 'emoji' => '🤝' ),
            4 => array( 'label' => 'Leadership émotionnel',     'emoji' => '🎯' ),
        );
    }

    public static function compute( array $answers ) {
        $dims       = self::get_dimensions();
        $dim_scores = array();

        foreach ( $dims as $dim_id => $dim ) {
            $sum = 0;
            foreach ( $dim['questions'] as $q_idx ) {
                $val  = isset( $answers[ $q_idx ] ) ? absint( $answers[ $q_idx ] ) : 1;
                $val  = max( 1, min( 4, $val ) );
                $sum += $val;
            }
            $dim_scores[ $dim_id ] = $sum;
        }

        $score_global = array_sum( $dim_scores );

        arsort( $dim_scores );
        $sorted_ids = array_keys( $dim_scores );

        // Top 3 forces : les 3 dimensions avec les scores les plus élevés
        $top_forces = array_slice( $sorted_ids, 0, 3 );

        // Axes de progression : uniquement les dimensions réellement faibles (≤ 12/20)
        // Si tout le monde est excellent, on ne fabrique pas de faux "axes de développement"
        $top_dev = array();
        foreach ( array_reverse( $sorted_ids ) as $dim_id ) {
            if ( count( $top_dev ) >= 3 ) break;
            if ( $dim_scores[ $dim_id ] <= 12 ) {
                $top_dev[] = $dim_id;
            }
        }

        list( $niveau, $phrase ) = self::interpret_global( $score_global );

        return array(
            'dim_scores'   => $dim_scores,
            'score_global' => $score_global,
            'top_forces'   => $top_forces,
            'top_dev'      => $top_dev,
            'niveau_qe'    => $niveau,
            'phrase_qe'    => $phrase,
        );
    }

    private static function interpret_global( $score ) {
        // Plage réelle : 80 (tout "Jamais") → 320 (tout "Toujours")
        // Seuils calés sur les points médians entre chaque niveau de réponse :
        //   Jamais(80) ↔ Rarement(160) : médiane = 120
        //   Rarement(160) ↔ Souvent(240) : médiane = 200
        //   Souvent(240) ↔ Toujours(320) : médiane = 280
        if ( $score <= 120 ) {
            return array( 'QE Faible',     'Votre intelligence émotionnelle est en construction. C\'est une excellente base pour commencer un travail sur vous.' );
        }
        if ( $score <= 200 ) {
            return array( 'QE Modéré',     'Vous disposez de vraies ressources émotionnelles. Quelques zones méritent d\'être renforcées pour libérer votre plein potentiel.' );
        }
        if ( $score <= 280 ) {
            return array( 'QE Élevé',      'Votre intelligence émotionnelle est un vrai atout. Vous gérez bien vos émotions et savez créer des relations de qualité.' );
        }
        return array( 'QE Très élevé',     'Vous faites partie des profils à haute intelligence émotionnelle. Votre capacité à comprendre et réguler vos émotions est remarquable.' );
    }

    public static function interpret_dim( $score ) {
        if ( $score <= 8  ) return 'Zone de développement prioritaire';
        if ( $score <= 12 ) return 'Compétence en construction';
        if ( $score <= 16 ) return 'Compétence développée';
        return 'Point fort';
    }

    /**
     * Retourne des recommandations concrètes pour une dimension donnée en fonction du score.
     *
     * @param int $dim_id  Identifiant de la dimension (1–16).
     * @param int $score   Score brut (5–20).
     * @return array { niveau: string, actions: string[] }
     */
    public static function get_recommendations( $dim_id, $score ) {
        $data = self::get_recommendations_data();
        $recs = $data[ $dim_id ] ?? null;
        if ( ! $recs ) return array( 'niveau' => '', 'actions' => array() );

        if ( $score <= 8 ) {
            $niveau  = 'Zone de développement prioritaire';
            $actions = $recs['prioritaire'];
        } elseif ( $score <= 12 ) {
            $niveau  = 'Compétence en construction';
            $actions = $recs['construction'];
        } elseif ( $score <= 16 ) {
            $niveau  = 'Compétence développée';
            $actions = $recs['developpee'];
        } else {
            $niveau  = 'Point fort';
            $actions = $recs['forte'];
        }

        return array( 'niveau' => $niveau, 'actions' => $actions );
    }

    private static function get_recommendations_data() {
        return array(
            // ── Dim 1 : Connaissance de soi ─────────────────────────────────────
            1 => array(
                'prioritaire'  => array(
                    'Tenez un journal émotionnel quotidien : notez chaque soir 1 émotion ressentie dans la journée, son déclencheur et son intensité.',
                    'Pratiquez la pause consciente : quand vous sentez une réaction monter, demandez-vous "Qu\'est-ce que je ressens exactement ?" avant d\'agir.',
                    'Dressez la liste de vos 5 "déclencheurs" émotionnels récurrents et partagez-la avec un proche de confiance pour validation.',
                ),
                'construction' => array(
                    'Intégrez 5 minutes de pleine conscience matinale pour observer votre état intérieur avant de démarrer la journée.',
                    'Enrichissez votre vocabulaire émotionnel : au lieu de "je suis mal", précisez (frustré, déçu, anxieux...) — la précision accélère la régulation.',
                    'Observez le lien entre vos humeurs et vos décisions sur une semaine et notez les patterns.',
                ),
                'developpee'   => array(
                    'Approfondissez la compréhension de l\'origine de vos émotions — explorez quelles croyances ou expériences passées les alimentent.',
                    'Utilisez cette conscience pour anticiper vos réactions dans les situations à fort enjeu (avant une réunion difficile, un entretien).',
                ),
                'forte'        => array(
                    'Mobilisez cette conscience pour devenir un miroir bienveillant : aidez vos proches à nommer ce qu\'ils ressentent et à mieux se comprendre.',
                ),
            ),
            // ── Dim 2 : Gestion du stress ────────────────────────────────────────
            2 => array(
                'prioritaire'  => array(
                    'Identifiez vos 3 principaux stresseurs et créez un plan d\'action simple pour chacun (réduire, déléguer, accepter).',
                    'Intégrez une technique de décompression rapide (respiration 4-7-8 ou cohérence cardiaque 5 min) à utiliser dès que la pression monte.',
                    'Instituez des "zones sans stress" dans votre journée : 15 minutes le matin et en soirée sans notifications ni sollicitations.',
                ),
                'construction' => array(
                    'Construisez une routine de récupération hebdomadaire (sport, nature, activité créative) — la résilience se construit hors crise.',
                    'Pratiquez le recadrage cognitif : listez 3 angles positifs ou apprentissages dans toute situation stressante.',
                    'Apprenez à distinguer le stress "utile" (stimulant) du stress "toxique" (paralysant) pour y répondre différemment.',
                ),
                'developpee'   => array(
                    'Transmettez vos stratégies anti-stress à votre entourage : expliquer une technique la renforce pour vous-même.',
                    'Expérimentez des niveaux de pression volontairement élevés (défi sportif, prise de parole) pour tester et étendre vos limites.',
                ),
                'forte'        => array(
                    'Capitalisez sur votre robustesse : devenez la "ressource calme" dans les situations de crise collective, vos proches ont besoin de ce modèle.',
                ),
            ),
            // ── Dim 3 : Gestion de la colère ─────────────────────────────────────
            3 => array(
                'prioritaire'  => array(
                    'Mettez en place le protocole STOP : quand la colère monte, Soufflez, Taisez-vous, Observez la sensation dans votre corps, Posez-vous.',
                    'Tenez un journal de vos épisodes de colère : contexte, déclencheur, intensité, réaction — les patterns deviennent visibles en 3 semaines.',
                    'Créez des "sorties de secours" préparées à l\'avance : une phrase courte et neutre pour vous extraire d\'une situation sans exploser ("J\'ai besoin de 5 minutes pour réfléchir").',
                ),
                'construction' => array(
                    'Pratiquez l\'expression assertive de la frustration avant qu\'elle devienne colère : "Je me sens frustré quand..." plutôt que "Tu fais toujours...".',
                    'Intégrez un délai de 24h avant de répondre aux messages qui vous irritent — relisez-vous après ce délai.',
                    'Explorez les besoins insatisfaits derrière votre colère : elle cache presque toujours un besoin de respect, de reconnaissance ou de contrôle.',
                ),
                'developpee'   => array(
                    'Utilisez la colère canalisée comme énergie de changement : identifiez un problème récurrent et transformez cette frustration en action concrète.',
                    'Renforcez votre capacité à récupérer après un pic — visez à revenir à l\'équilibre en moins d\'une heure.',
                ),
                'forte'        => array(
                    'Partagez votre méthode avec les personnes de votre entourage qui luttent avec la colère — c\'est la marque d\'une vraie maîtrise.',
                ),
            ),
            // ── Dim 4 : Confiance en soi ──────────────────────────────────────────
            4 => array(
                'prioritaire'  => array(
                    'Tenez un "journal de victoires" : notez chaque soir 1 réussite de la journée, aussi petite soit-elle — la confiance se construit sur l\'accumulation.',
                    'Identifiez votre "critique intérieur" et donnez-lui un nom. Quand il parle, répondez-lui comme à un ami pessimiste : avec scepticisme bienveillant.',
                    'Relevez un défi hors zone de confort chaque semaine, calibré pour être difficile mais réalisable — la confiance naît de l\'action, pas de la réflexion.',
                ),
                'construction' => array(
                    'Pratiquez la posture de pouvoir : avant une situation stressante, adoptez une posture expansive 2 minutes (recherche Amy Cuddy).',
                    'Listez 10 compétences que vous possédez réellement — relisez cette liste avant les situations où vous doutez de vous.',
                    'Osez donner votre avis dans 3 contextes où vous vous seriez habituellement tu(e) cette semaine.',
                ),
                'developpee'   => array(
                    'Travaillez sur la confiance situationnelle : dans quel(s) contexte(s) spécifiques le doute persiste-t-il ? Ciblez-les avec des actions précises.',
                    'Mentorer quelqu\'un de moins expérimenté — enseigner renforce profondément la légitimité.',
                ),
                'forte'        => array(
                    'Utilisez votre confiance pour créer un espace safe autour de vous : vos collègues et proches osent-ils prendre des risques grâce à votre soutien ?',
                ),
            ),
            // ── Dim 5 : Auto-motivation ───────────────────────────────────────────
            5 => array(
                'prioritaire'  => array(
                    'Reconnectez-vous à votre "pourquoi" : écrivez en 5 lignes pourquoi votre projet ou rôle actuel a du sens pour vous — relisez-le chaque matin.',
                    'Découpez vos objectifs en microtâches de 25 minutes (Pomodoro) — la progression visible est le meilleur carburant motivationnel.',
                    'Supprimez les 2-3 principales sources de friction dans votre environnement de travail (notifications, désordre, interruptions) — elles vampirisent votre élan.',
                ),
                'construction' => array(
                    'Créez un rituel de démarrage journalier (même heure, même séquence) — l\'ancrage conditionné réduit la résistance au démarrage.',
                    'Célébrez les étapes intermédiaires, pas seulement les résultats finaux — le cerveau a besoin de dopamine régulière.',
                    'Cultivez des relations avec des personnes dont l\'énergie et la motivation vous tirent vers le haut.',
                ),
                'developpee'   => array(
                    'Explorez ce qui vous donne de l\'élan dans les tâches routinières — l\'art de trouver du sens partout est une compétence à développer.',
                    'Testez des défis légèrement au-delà de vos capacités actuelles — le flow (Csikszentmihalyi) naît à la frontière défi/compétence.',
                ),
                'forte'        => array(
                    'Votre moteur interne est puissant — canalisez-le : assurez-vous que votre énergie est bien alignée sur vos priorités profondes et pas seulement sur l\'urgence.',
                ),
            ),
            // ── Dim 6 : Optimisme ─────────────────────────────────────────────────
            6 => array(
                'prioritaire'  => array(
                    'Pratiquez le "3 bonnes choses" avant de dormir : notez 3 événements positifs de la journée et leur cause — en 3 semaines, cela recâble le biais négatif.',
                    'Questionnez votre pessimisme : quand vous anticipez le pire, demandez "Quelle est la probabilité réelle ? Que ferais-je si cela arrivait ?" — souvent, c\'est gérable.',
                    'Limitez votre exposition aux informations négatives (actualités, réseaux sociaux) à des plages définies et restreintes.',
                ),
                'construction' => array(
                    'Cultivez la formulation solution : remplacez "c\'est impossible" par "comment est-ce que je pourrais..." — le cerveau suit la question.',
                    'Entourez-vous de personnes dont la vision positive est réaliste et constructive — l\'optimisme est contagieux.',
                    'Tenez un "journal des possibles" : à chaque obstacle, notez 3 voies de passage.',
                ),
                'developpee'   => array(
                    'Affinez la distinction entre optimisme réaliste et déni : gardez un regard lucide sur les risques tout en restant orienté solution.',
                    'Transmettez votre vision positive dans vos communications — reformulez les défis collectifs en opportunités.',
                ),
                'forte'        => array(
                    'Votre optimisme est une ressource collective précieuse — utilisez-le consciemment pour redonner de l\'élan aux équipes et proches en période difficile.',
                ),
            ),
            // ── Dim 7 : Résilience ────────────────────────────────────────────────
            7 => array(
                'prioritaire'  => array(
                    'Identifiez vos "ressources ressourçantes" (activités, personnes, lieux) et planifiez-les activement — ne les laissez pas au hasard.',
                    'Pratiquez l\'acceptation radicale de ce que vous ne pouvez pas contrôler : listez vos préoccupations en deux colonnes (maîtrisable / non maîtrisable) et agissez uniquement sur la première.',
                    'Cherchez un soutien professionnel ou personnel pour traverser l\'épreuve actuelle — demander de l\'aide est le premier acte de résilience.',
                ),
                'construction' => array(
                    'Développez votre "boîte à outils" de récupération : que faites-vous concrètement après un coup dur ? Formalisez-le pour l\'avoir sous la main.',
                    'Pratiquez la recherche de sens : quelle leçon, quelle force, quel changement cette épreuve vous a-t-elle apporté ?',
                    'Renforcez votre réseau de soutien — la résilience est rarement solitaire.',
                ),
                'developpee'   => array(
                    'Approfondissez la post-traumatic growth : après les difficultés passées, quelles ressources nouvelles avez-vous développées ? Nommez-les.',
                    'Renforcez votre tolérance à l\'ambiguïté — les personnes résilientes restent fonctionnelles même sans certitude.',
                ),
                'forte'        => array(
                    'Vous traversez les tempêtes avec une solidité rare — partagez vos méthodes et soyez un ancre pour les personnes en crise autour de vous.',
                ),
            ),
            // ── Dim 8 : Flexibilité ───────────────────────────────────────────────
            8 => array(
                'prioritaire'  => array(
                    'Pratiquez délibérément un changement de routine chaque semaine (chemin, horaire, méthode) — l\'inconfort du petit changement prépare au grand.',
                    'Identifiez les 2-3 croyances rigides qui vous bloquent face au changement ("ça a toujours marché comme ça", "ce n\'est pas mon rôle") et questionnez-les.',
                    'Exposez-vous à des points de vue radicalement différents des vôtres (podcast, livre, conversation) sans chercher à les réfuter — juste à comprendre.',
                ),
                'construction' => array(
                    'Entraînez-vous au "oui, et..." : dans vos conversations, avant de réfuter une idée, trouvez ce qui est valable en elle.',
                    'Documentez les situations où vous avez changé d\'avis et où ça s\'est bien terminé — cela neutralise la peur du changement.',
                    'Pratiquez la résolution créative de problèmes : pour tout obstacle, générez 10 solutions sans filtrer, puis choisissez.',
                ),
                'developpee'   => array(
                    'Cultivez l\'aisance dans l\'ambiguïté : prenez des décisions incomplètes avec 70% des informations — la flexibilité inclut la tolérance à l\'incertitude.',
                    'Devenez un facilitateur de changement dans votre entourage — expliquez, rassurez, embarquez.',
                ),
                'forte'        => array(
                    'Votre adaptabilité est un avantage concurrentiel — utilisez-la pour initier des changements que les autres n\'osent pas encore.',
                ),
            ),
            // ── Dim 9 : Expression des sentiments ────────────────────────────────
            9 => array(
                'prioritaire'  => array(
                    'Commencez par les émotions positives : exprimez gratitude, joie, ou appréciation à 1 personne par jour — c\'est le chemin le plus safe vers l\'expression.',
                    'Pratiquez les messages en "je" : "Je me sens débordé" plutôt que "Tu m\'oppresses" — cela ouvre la communication au lieu de la fermer.',
                    'Créez un espace de confiance avec 1 personne à qui vous pouvez vous exprimer sans filtre — ce lien est une soupape essentielle.',
                ),
                'construction' => array(
                    'Distinguez l\'expression utile (qui sert la relation ou la situation) et l\'expression cathartique (qui vous soulage) — les deux ont leur place, mais pas au même moment.',
                    'Exprimez vos émotions difficiles en différé : écrivez-les d\'abord, puis choisissez ce que vous partagez et à qui.',
                    'Expérimentez des canaux alternatifs (écriture, art, sport) pour les émotions que vous ne savez pas encore mettre en mots.',
                ),
                'developpee'   => array(
                    'Affinez le timing de votre expression : le "bon moment" pour partager une émotion difficile est aussi important que le "comment".',
                    'Pratiquez l\'expression de la vulnérabilité avec des personnes de confiance — elle crée un lien profond quand elle est bien calibrée.',
                ),
                'forte'        => array(
                    'Votre authenticité émotionnelle est une force relationnelle rare — modélisez cette capacité et créez un climat où les autres osent s\'exprimer aussi.',
                ),
            ),
            // ── Dim 10 : Assertivité ──────────────────────────────────────────────
            10 => array(
                'prioritaire'  => array(
                    'Commencez petit : dites "non" à une demande non urgente cette semaine, sans vous justifier longuement — observez ce qui se passe réellement.',
                    'Pratiquez le disque rayé : répétez calmement votre position sans hausser le ton ni vous laisser entraîner dans la négociation ("Je comprends, et ma réponse reste non").',
                    'Identifiez les situations où vous vous effacez systématiquement et préparez à l\'avance une réponse assertive pour la prochaine occurrence.',
                ),
                'construction' => array(
                    'Apprenez à demander ce dont vous avez besoin directement, sans détour ni manipulation — la demande claire est souvent suffisante.',
                    'Distinguez vos besoins de vos envies, vos droits de vos préférences — l\'assertivité repose sur cette clarté intérieure.',
                    'Pratiquez avec des enjeux faibles (restaurant, commerçant) pour construire le muscle avant les situations importantes.',
                ),
                'developpee'   => array(
                    'Affinez votre assertivité dans les situations de pouvoir asymétrique (hiérarchie, expert, groupe) — c\'est là que la compétence est la plus précieuse.',
                    'Vérifiez que votre assertivité reste chaleureuse : la fermeté sans bienveillance peut être perçue comme de l\'agressivité.',
                ),
                'forte'        => array(
                    'Modelez l\'assertivité pour votre entourage : créer un espace où chacun ose s\'exprimer directement est l\'un des plus beaux cadeaux relationnels.',
                ),
            ),
            // ── Dim 11 : Empathie ─────────────────────────────────────────────────
            11 => array(
                'prioritaire'  => array(
                    'Pratiquez l\'écoute sans interrompre : dans votre prochaine conversation difficile, laissez l\'autre finir entièrement avant de répondre.',
                    'Posez la question "Comment tu vis ça ?" et écoutez sans chercher à résoudre — parfois, être entendu suffit.',
                    'Exercez-vous à identifier les émotions des autres dans vos conversations (films, séries) — commencer par observer avant d\'interagir.',
                ),
                'construction' => array(
                    'Pratiquez le "grand pas de côté" : face à quelqu\'un dont vous ne comprenez pas la réaction, cherchez activement son point de vue avant de répondre.',
                    'Distinguez empathie cognitive (comprendre) et affective (ressentir) — les deux sont nécessaires, mais la première est plus facilement cultivable.',
                    'Cherchez à comprendre les personnes qui vous irritent le plus — c\'est là que l\'empathie est la plus transformatrice.',
                ),
                'developpee'   => array(
                    'Affinez votre empathie en protégeant votre espace émotionnel — évitez l\'empathie excessive (fusion) qui vous épuise et vous rend moins efficace.',
                    'Utilisez votre empathie dans les situations de conflit pour désamorcer avant que les positions ne se figent.',
                ),
                'forte'        => array(
                    'Votre sensibilité aux autres est une ressource précieuse — canalisez-la dans des rôles de médiation, accompagnement ou management humain.',
                ),
            ),
            // ── Dim 12 : Tact ─────────────────────────────────────────────────────
            12 => array(
                'prioritaire'  => array(
                    'Avant de donner un feedback difficile, posez-vous 3 questions : est-ce le bon moment ? le bon lieu ? la bonne formulation ?',
                    'Pratiquez la structure "Situation - Comportement - Impact" pour tout feedback : les faits d\'abord, l\'interprétation ensuite.',
                    'Apprenez à lire l\'état émotionnel de votre interlocuteur avant d\'aborder un sujet sensible — une personne en stress ne peut pas recevoir de feedback.',
                ),
                'construction' => array(
                    'Développez votre radar contextuel : le tact, c\'est savoir adapter ce qui est vrai à ce qui est recevable ici et maintenant.',
                    'Entraînez-vous à reformuler vos messages directs avec une ouverture empathique ("Je vois que c\'est important pour toi, et...").',
                    'Observez les personnes que vous trouvez diplomatiques : qu\'est-ce qu\'elles font exactement ? Comment introduisent-elles les sujets délicats ?',
                ),
                'developpee'   => array(
                    'Affinez votre tact dans les situations de grande différence culturelle ou générationnelle — les codes varient fortement.',
                    'Travaillez sur le "tact sous pression" : il est plus facile d\'être tactique quand on a le temps — entraînez-vous à la douceur en situation urgente.',
                ),
                'forte'        => array(
                    'Votre sens des nuances est un atout stratégique — utilisez-le pour faciliter les conversations difficiles que les autres évitent.',
                ),
            ),
            // ── Dim 13 : Gestion de la diversité ─────────────────────────────────
            13 => array(
                'prioritaire'  => array(
                    'Exposez-vous délibérément à des cultures, générations ou milieux sociaux différents des vôtres — un repas, un podcast, une conversation.',
                    'Identifiez 3 biais que vous suspectez avoir (âge, milieu, style de travail) et cherchez activement des contre-exemples.',
                    'Dans vos prochaines interactions avec des personnes "différentes", adoptez la posture du curieux : "Qu\'est-ce que j\'apprends ici ?"',
                ),
                'construction' => array(
                    'Pratiquez la suspension du jugement : quand quelqu\'un agit différemment de vous, attendez 30 secondes avant d\'interpréter — souvent, le contexte manque.',
                    'Recherchez activement des avis divergents dans vos prises de décision — la diversité cognitive produit de meilleures décisions.',
                    'Lisez un livre ou regardez un documentaire sur une culture ou réalité sociale que vous connaissez peu.',
                ),
                'developpee'   => array(
                    'Devenez un facilitateur d\'inclusion : comment créez-vous des espaces où chacun peut contribuer pleinement, quelle que soit sa différence ?',
                    'Questionnez les systèmes et habitudes autour de vous qui excluent involontairement certains profils.',
                ),
                'forte'        => array(
                    'Votre ouverture à la diversité est un avantage réel — incarnez-la dans vos choix de collaboration et dans la composition des groupes que vous animez.',
                ),
            ),
            // ── Dim 14 : Motiver les autres ───────────────────────────────────────
            14 => array(
                'prioritaire'  => array(
                    'Commencez par observer ce qui donne de l\'élan à chaque personne de votre entourage — les motivateurs varient radicalement d\'une personne à l\'autre.',
                    'Pratiquez la reconnaissance sincère et spécifique : "J\'ai beaucoup apprécié la façon dont tu as géré X ce matin" — évitez les compliments génériques.',
                    'Demandez à vos collaborateurs/proches : "Qu\'est-ce qui te donne de l\'élan dans ce que tu fais ?" — la question elle-même motive.',
                ),
                'construction' => array(
                    'Développez votre capacité à connecter les tâches au sens : "En faisant ça, tu contribues à...".',
                    'Pratiquez le soutien dans l\'échec : comment vous comportez-vous quand quelqu\'un échoue ? C\'est là que votre impact motivationnel est le plus fort.',
                    'Apprenez à adapter votre style (directif, coaching, soutien) en fonction de la maturité et de la confiance de votre interlocuteur.',
                ),
                'developpee'   => array(
                    'Construisez des rituels collectifs qui entretiennent l\'élan (rétrospectives positives, célébrations d\'étapes).',
                    'Cultivez votre propre énergie — on ne peut donner que ce qu\'on a : votre motivation est contagieuse quand elle est authentique.',
                ),
                'forte'        => array(
                    'Vous avez un réel pouvoir d\'inspiration — formalisez votre approche et transmettez-la aux managers et leaders de votre entourage.',
                ),
            ),
            // ── Dim 15 : Gestion des conflits ─────────────────────────────────────
            15 => array(
                'prioritaire'  => array(
                    'Pratiquez l\'approche DESC pour aborder un conflit : Décrivez les faits, Exprimez votre ressenti, Spécifiez ce que vous attendez, Conséquences positives si changement.',
                    'Apprenez à distinguer le problème de la personne — attaquer le comportement, pas l\'individu, ouvre la porte à la résolution.',
                    'Préparez mentalement votre prochaine conversation difficile : quel est mon objectif ? Quel est le besoin de l\'autre ? Quelle solution nous conviendrait à tous deux ?',
                ),
                'construction' => array(
                    'Pratiquez l\'écoute active dans les conflits : avant de défendre votre position, reformulez celle de l\'autre pour vous assurer de l\'avoir comprise.',
                    'Développez votre tolérance à l\'inconfort de la confrontation — éviter les conflits les amplifie ; les aborder tôt les résout.',
                    'Cherchez l\'intérêt commun derrière les positions antagonistes — il existe presque toujours.',
                ),
                'developpee'   => array(
                    'Affinez votre rôle de médiateur : comment faciliter la résolution de conflits dans lesquels vous n\'êtes pas directement impliqué ?',
                    'Travaillez sur la réconciliation post-conflit — gérer l\'après est aussi important que gérer le pendant.',
                ),
                'forte'        => array(
                    'Votre capacité à gérer les conflits est rare et précieuse — proposez-vous comme médiateur ou facilitateur dans les situations tendues de votre entourage.',
                ),
            ),
            // ── Dim 16 : Contrôle des impulsions ─────────────────────────────────
            16 => array(
                'prioritaire'  => array(
                    'Installez une règle personnelle de délai : pour toute décision ou réaction sous émotion, attendez 10 minutes minimum — souvent, l\'envie d\'agir impulsif disparaît.',
                    'Identifiez vos "zones gâchette" : contextes où vous perdez le contrôle (faim, fatigue, sentiment de mépris). Anticipez-les avec des protocoles précis.',
                    'Pratiquez la pleine conscience quotidienne (5 min) — elle renforce le cortex préfrontal, siège du contrôle des impulsions.',
                ),
                'construction' => array(
                    'Entraînez-vous à la gratification différée : choisissez délibérément la récompense à long terme sur la satisfaction immédiate dans des situations à faible enjeu.',
                    'Développez une "phrase d\'ancrage" personnelle à vous répéter quand vous sentez l\'impulsivité monter ("Respire. Choisir. Agir.").',
                    'Analysez vos décisions impulsives passées : quel signal physique précédait l\'action ? Apprenez à le reconnaître avant d\'en être submergé.',
                ),
                'developpee'   => array(
                    'Affinez votre contrôle dans les situations de fatigue et de stress intense — c\'est là que l\'impulsivité reprend le dessus même pour les meilleurs.',
                    'Utilisez ce contrôle pour planifier : prenez vos grandes décisions en état de repos et de lucidité, jamais dans l\'urgence.',
                ),
                'forte'        => array(
                    'Votre maîtrise de vous-même est un socle de confiance pour votre entourage — les autres savent qu\'ils peuvent compter sur votre stabilité.',
                ),
            ),
        );
    }

    /**
     * Retourne le score de fiabilité (désirabilité sociale inversée).
     * Bas score = réponses trop parfaites = biais probable.
     * Haut score = admission de faiblesses humaines normales = fiable.
     *
     * Les 6 items (indices 80-85) décrivent des comportements que
     * presque tout le monde reconnaît avoir PARFOIS.
     * "Jamais" (1) sur ces items = score de fiabilité faible.
     *
     * @param  array $answers  Tableau indexé 0-85.
     * @return int  Score brut 6-24. Seuils : ≤12 = biais fort, ≤18 = biais modéré.
     */
    public static function compute_desirabilite( array $answers ) {
        $sum = 0;
        for ( $i = 80; $i <= 85; $i++ ) {
            $val  = isset( $answers[ $i ] ) ? absint( $answers[ $i ] ) : 1;
            $sum += max( 1, min( 4, $val ) );
        }
        return $sum; // range 6-24
    }

    /**
     * Interprète le score de fiabilité.
     * @param  int $score  Valeur retournée par compute_desirabilite().
     * @return array { niveau: string, alerte: bool, message: string }
     */
    public static function interpret_desirabilite( $score ) {
        if ( $score <= 12 ) {
            return array(
                'niveau'  => 'Biais fort',
                'alerte'  => true,
                'message' => 'Vos réponses semblent orientées vers une image très positive de vous-même. Les scores ci-dessus reflètent peut-être davantage ce que vous souhaiteriez être que ce que vous vivez au quotidien. Un regard plus nuancé pourrait révéler des pistes de développement précieuses.',
            );
        }
        if ( $score <= 18 ) {
            return array(
                'niveau'  => 'Biais modéré',
                'alerte'  => false,
                'message' => '',
            );
        }
        return array(
            'niveau'  => 'Fiable',
            'alerte'  => false,
            'message' => '',
        );
    }

    public static function get_questions_json() {
        return array(
            // ── FAMILLE 1 — Conscience de soi (Q1-20) ──────────────
            // Dim 1 : Connaissance de soi
            array( 'dim' => 1,  'f' => 1, 'text' => "J'identifie mes émotions au moment même où je les ressens." ),
            array( 'dim' => 1,  'f' => 1, 'text' => "Je comprends ce qui déclenche mes réactions émotionnelles." ),
            array( 'dim' => 1,  'f' => 1, 'text' => "Je perçois l'impact de mon humeur sur mes comportements et mes décisions." ),
            array( 'dim' => 1,  'f' => 1, 'text' => "J'ai une vision claire de mes forces et de mes limites personnelles." ),
            array( 'dim' => 1,  'f' => 1, 'text' => "Je reconnais les signaux physiques qui accompagnent mes émotions (tension, accélération cardiaque...)." ),
            // Dim 4 : Confiance en soi
            array( 'dim' => 4,  'f' => 1, 'text' => "Je crois en ma capacité à réussir les défis que j'affronte." ),
            array( 'dim' => 4,  'f' => 1, 'text' => "J'exprime mes opinions même lorsqu'elles diffèrent de celles des autres." ),
            array( 'dim' => 4,  'f' => 1, 'text' => "Je prends mes décisions sans avoir constamment besoin de validation extérieure." ),
            array( 'dim' => 4,  'f' => 1, 'text' => "Je fais confiance à mon jugement dans les situations difficiles." ),
            array( 'dim' => 4,  'f' => 1, 'text' => "Je relève de nouveaux défis sans que l'incertitude me paralyse." ),
            // Dim 9 : Expression des sentiments
            array( 'dim' => 9,  'f' => 1, 'text' => "Je prends l'initiative de partager ce que je ressens quand c'est utile à la relation." ),
            array( 'dim' => 9,  'f' => 1, 'text' => "Je trouve les mots justes pour décrire ce que je ressens intérieurement." ),
            array( 'dim' => 9,  'f' => 1, 'text' => "J'exprime naturellement mes émotions positives (joie, gratitude, affection)." ),
            array( 'dim' => 9,  'f' => 1, 'text' => "Je n'accumule pas mes émotions négatives en les gardant pour moi." ),
            array( 'dim' => 9,  'f' => 1, 'text' => "Je partage mes ressentis sans les dramatiser ni les minimiser." ),
            // Dim 16 : Contrôle des impulsions
            array( 'dim' => 16, 'f' => 1, 'text' => "Je prends le temps de réfléchir avant d'agir, même sous l'effet d'une émotion forte." ),
            array( 'dim' => 16, 'f' => 1, 'text' => "Je diffère une satisfaction immédiate au profit d'un bénéfice futur plus important." ),
            array( 'dim' => 16, 'f' => 1, 'text' => "Je ne prends pas de décisions importantes quand je suis sous l'emprise d'une émotion intense." ),
            array( 'dim' => 16, 'f' => 1, 'text' => "Avant de réagir à chaud, je marque une pause pour choisir ma réponse." ),
            array( 'dim' => 16, 'f' => 1, 'text' => "Je termine ce que j'ai commencé sans me laisser distraire par des envies du moment." ),
            // ── FAMILLE 2 — Régulation émotionnelle (Q21-50) ───────
            // Dim 2 : Gestion du stress
            array( 'dim' => 2,  'f' => 2, 'text' => "Je reste calme et efficace même sous forte pression." ),
            array( 'dim' => 2,  'f' => 2, 'text' => "Je dispose de stratégies concrètes pour réduire mon niveau de stress." ),
            array( 'dim' => 2,  'f' => 2, 'text' => "J'arrive à prendre du recul même quand la pression est forte." ),
            array( 'dim' => 2,  'f' => 2, 'text' => "Je ne laisse pas le stress envahir mes pensées sur une longue durée." ),
            array( 'dim' => 2,  'f' => 2, 'text' => "Je gère les situations d'urgence sans me laisser déborder émotionnellement." ),
            // Dim 3 : Gestion de la colère
            array( 'dim' => 3,  'f' => 2, 'text' => "Quand je me sens en colère, je choisis consciemment comment réagir plutôt que d'exploser." ),
            array( 'dim' => 3,  'f' => 2, 'text' => "Je prends le temps de me calmer avant de répondre dans une situation conflictuelle." ),
            array( 'dim' => 3,  'f' => 2, 'text' => "Quand je suis en colère, j'exprime ce que je ressens sans agressivité." ),
            array( 'dim' => 3,  'f' => 2, 'text' => "Je ne laisse pas la colère guider mes décisions." ),
            array( 'dim' => 3,  'f' => 2, 'text' => "Après un accès de colère, je reviens rapidement à un état serein." ),
            // Dim 5 : Auto-motivation
            array( 'dim' => 5,  'f' => 2, 'text' => "Je me fixe des objectifs ambitieux et je m'y tiens sans supervision." ),
            array( 'dim' => 5,  'f' => 2, 'text' => "Je me remotive rapidement après un échec ou une déception." ),
            array( 'dim' => 5,  'f' => 2, 'text' => "Je trouve du sens dans ce que je fais, même dans les tâches routinières." ),
            array( 'dim' => 5,  'f' => 2, 'text' => "Je m'investis pleinement sans avoir besoin de récompense externe." ),
            array( 'dim' => 5,  'f' => 2, 'text' => "Je maintiens mon énergie et mon enthousiasme sur la durée." ),
            // Dim 6 : Optimisme
            array( 'dim' => 6,  'f' => 2, 'text' => "Je m'attends généralement à ce que les choses se passent bien." ),
            array( 'dim' => 6,  'f' => 2, 'text' => "Face à un obstacle, je cherche d'abord les solutions plutôt que les problèmes." ),
            array( 'dim' => 6,  'f' => 2, 'text' => "Je considère les difficultés comme des opportunités d'apprentissage." ),
            array( 'dim' => 6,  'f' => 2, 'text' => "Je maintiens une vision positive de l'avenir même dans les moments difficiles." ),
            array( 'dim' => 6,  'f' => 2, 'text' => "Je tends à voir ce qui est possible plutôt que ce qui fait obstacle." ),
            // Dim 7 : Résilience
            array( 'dim' => 7,  'f' => 2, 'text' => "Je me relève rapidement après un échec ou une épreuve difficile." ),
            array( 'dim' => 7,  'f' => 2, 'text' => "Les situations adverses me renforcent plutôt qu'elles ne me découragent." ),
            array( 'dim' => 7,  'f' => 2, 'text' => "Je garde mon équilibre émotionnel lors de changements importants dans ma vie." ),
            array( 'dim' => 7,  'f' => 2, 'text' => "J'accepte ce que je ne peux pas contrôler et m'adapte en conséquence." ),
            array( 'dim' => 7,  'f' => 2, 'text' => "Je suis capable de repartir sur de nouvelles bases après une perte significative." ),
            // Dim 8 : Flexibilité
            array( 'dim' => 8,  'f' => 2, 'text' => "Je m'adapte facilement aux nouvelles règles, méthodes ou environnements." ),
            array( 'dim' => 8,  'f' => 2, 'text' => "Je change de point de vue quand on me présente des éléments convaincants." ),
            array( 'dim' => 8,  'f' => 2, 'text' => "Je gère bien l'incertitude et l'ambiguïté sans anxiété excessive." ),
            array( 'dim' => 8,  'f' => 2, 'text' => "Je ne m'accroche pas à mes habitudes quand le changement est nécessaire." ),
            array( 'dim' => 8,  'f' => 2, 'text' => "J'accueille les nouvelles idées avec ouverture, même si elles remettent en question ma façon de faire." ),
            // ── FAMILLE 3 — Relations & Communication (Q51-70) ─────
            // Dim 10 : Assertivité
            array( 'dim' => 10, 'f' => 3, 'text' => "Je sais dire non sans me sentir coupable." ),
            array( 'dim' => 10, 'f' => 3, 'text' => "J'exprime mes besoins et mes attentes clairement et directement." ),
            array( 'dim' => 10, 'f' => 3, 'text' => "Je défends mes opinions et mes droits sans agressivité." ),
            array( 'dim' => 10, 'f' => 3, 'text' => "Je donne un feedback direct sur ce qui ne me convient pas, sans détour ni agressivité." ),
            array( 'dim' => 10, 'f' => 3, 'text' => "Je ne laisse pas les autres empiéter sur mes limites personnelles ou professionnelles." ),
            // Dim 11 : Empathie
            array( 'dim' => 11, 'f' => 3, 'text' => "Je perçois facilement les émotions de quelqu'un, même s'il ne les exprime pas verbalement." ),
            array( 'dim' => 11, 'f' => 3, 'text' => "Je m'intéresse sincèrement à ce que vivent les autres." ),
            array( 'dim' => 11, 'f' => 3, 'text' => "Je me mets à la place de personnes dont les valeurs sont très différentes des miennes." ),
            array( 'dim' => 11, 'f' => 3, 'text' => "Je remarque quand quelqu'un ne va pas, même sans qu'il me le signale." ),
            array( 'dim' => 11, 'f' => 3, 'text' => "Je prends en compte les émotions des autres dans mes décisions." ),
            // Dim 12 : Tact
            array( 'dim' => 12, 'f' => 3, 'text' => "Je choisis le bon moment et la bonne manière pour aborder un sujet sensible." ),
            array( 'dim' => 12, 'f' => 3, 'text' => "J'adapte mon langage et mon ton à mon interlocuteur et au contexte." ),
            array( 'dim' => 12, 'f' => 3, 'text' => "Je suis capable de dire des vérités difficiles sans blesser inutilement." ),
            array( 'dim' => 12, 'f' => 3, 'text' => "Je tiens compte de l'état émotionnel de l'autre avant de lui faire un retour." ),
            array( 'dim' => 12, 'f' => 3, 'text' => "Je crée un climat de confiance avant d'aborder des sujets délicats." ),
            // Dim 13 : Gestion de la diversité
            array( 'dim' => 13, 'f' => 3, 'text' => "Je suis à l'aise pour collaborer avec des personnes d'horizons très différents du mien." ),
            array( 'dim' => 13, 'f' => 3, 'text' => "Je considère la diversité des points de vue comme une richesse dans un groupe." ),
            array( 'dim' => 13, 'f' => 3, 'text' => "Je suis curieux(se) de comprendre des références culturelles ou sociales différentes des miennes." ),
            array( 'dim' => 13, 'f' => 3, 'text' => "Je traite chaque personne avec le même respect, quelle que soit son appartenance." ),
            array( 'dim' => 13, 'f' => 3, 'text' => "Je fais des efforts pour comprendre les valeurs de ceux qui m'entourent." ),
            // ── FAMILLE 4 — Leadership émotionnel (Q71-80) ─────────
            // Dim 14 : Motiver les autres
            array( 'dim' => 14, 'f' => 4, 'text' => "Je prends le temps d'observer ce qui donne de l'élan à chaque personne autour de moi." ),
            array( 'dim' => 14, 'f' => 4, 'text' => "Je suis capable d'insuffler de l'enthousiasme à un groupe qui manque d'énergie." ),
            array( 'dim' => 14, 'f' => 4, 'text' => "Je valorise les efforts et les réussites des personnes autour de moi." ),
            array( 'dim' => 14, 'f' => 4, 'text' => "Je formule des encouragements sincères et adaptés à la situation." ),
            array( 'dim' => 14, 'f' => 4, 'text' => "J'aide les autres à trouver du sens dans ce qu'ils font, même dans les moments difficiles." ),
            // Dim 15 : Gestion des conflits
            array( 'dim' => 15, 'f' => 4, 'text' => "Je fais face aux conflits plutôt que de les éviter." ),
            array( 'dim' => 15, 'f' => 4, 'text' => "Je recherche des solutions qui satisfont les deux parties plutôt que d'imposer ma vision." ),
            array( 'dim' => 15, 'f' => 4, 'text' => "Je reste calme et constructif(ve) dans les situations de tension interpersonnelle." ),
            array( 'dim' => 15, 'f' => 4, 'text' => "Je m'en prends au problème, pas à la personne." ),
            array( 'dim' => 15, 'f' => 4, 'text' => "Je désamorce une situation conflictuelle avant qu'elle ne s'aggrave." ),

            // ── FAMILLE 5 — Désirabilité sociale (Q81-86, idx 80-85) ──
            // Items Marlowe-Crowne inversés : "Jamais" sur ces items = biais fort.
            // Comportements humains normaux que tout le monde reconnaît avoir parfois.
            // NE PAS MODIFIER L'ORDRE — les indices 80-85 sont codés en dur dans compute_desirabilite().
            array( 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de mentir légèrement pour éviter de blesser quelqu'un." ),
            array( 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive d'être moins patient(e) avec les autres que je ne le voudrais." ),
            array( 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de ressentir de l'envie face à la réussite d'un autre." ),
            array( 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de procrastiner, même quand je sais que je ne le devrais pas." ),
            array( 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de penser du mal de quelqu'un sans oser le lui dire." ),
            array( 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de me sentir irrité(e) par des comportements ou des personnes, même sans raison valable." ),
        );
    }
}
