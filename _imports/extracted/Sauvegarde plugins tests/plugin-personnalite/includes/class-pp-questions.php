<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PraxiMum — version 128 questions
 *
 * Structure :
 *   5 dimensions × 4q  = 20q  (2 directs + 2 inversés)
 *   30 facettes  × 4q  = 120q (2 directs + 2 inversés)
 *   Désirabilité sociale       8q
 *   ─────────────────────────────
 *   Total                    148q
 *
 * Note : les 20q dimensions et les 120q facettes se recoupent
 * intentionnellement — les dimensions sont un sous-ensemble des facettes.
 * Pour éviter la redondance, on utilise 4q par facette UNIQUEMENT,
 * et le score dimension est agrégé depuis les scores facettes.
 * Total réel soumis à l'utilisateur : 120q facettes + 8q DS = 128q.
 *
 * Ordre de présentation : entrelacé par blocs de 8 (dimensions mélangées).
 * Échelle Likert : 1 (Fortement en désaccord) → 5 (Fortement d'accord).
 */
class PP_Questions {

    public static function get_facettes_map() {
        return array(
            'O1_FAN' => array('label'=>'Fantaisie',              'dim'=>'O','desc'=>"Tendance à imaginer et à enrichir la réalité par des scénarios inventés."),
            'O2_EST' => array('label'=>'Esthétique',             'dim'=>'O','desc'=>"Sensibilité à la beauté dans l'art, la nature et les objets du quotidien."),
            'O3_SEN' => array('label'=>'Sentiments',             'dim'=>'O','desc'=>"Profondeur et variété de la vie émotionnelle intérieure."),
            'O4_ACT' => array('label'=>'Actions',                'dim'=>'O','desc'=>"Goût pour la nouveauté dans les comportements et choix de vie."),
            'O5_IDE' => array('label'=>'Idées',                  'dim'=>'O','desc'=>"Curiosité intellectuelle et plaisir de la réflexion abstraite."),
            'O6_VAL' => array('label'=>'Valeurs',                'dim'=>'O','desc'=>"Ouverture à remettre en question normes et croyances établies."),
            'C1_COM' => array('label'=>'Compétence',             'dim'=>'C','desc'=>"Sentiment d'être capable et efficace face aux défis."),
            'C2_ORD' => array('label'=>'Ordre',                  'dim'=>'C','desc'=>"Besoin de structure et d'organisation de l'environnement."),
            'C3_DEV' => array('label'=>'Sens du devoir',         'dim'=>'C','desc'=>"Respect des obligations morales et des engagements pris."),
            'C4_REA' => array('label'=>'Recherche de réussite',  'dim'=>'C','desc'=>"Motivation à exceller et à atteindre des objectifs ambitieux."),
            'C5_DIS' => array('label'=>'Autodiscipline',         'dim'=>'C','desc'=>"Capacité à persévérer malgré l'ennui ou le manque de motivation."),
            'C6_DEL' => array('label'=>'Délibération',           'dim'=>'C','desc'=>"Tendance à réfléchir soigneusement avant d'agir."),
            'E1_CHA' => array('label'=>'Chaleur',                'dim'=>'E','desc'=>"Affection et intérêt sincère pour les autres."),
            'E2_GRE' => array('label'=>'Grégarité',              'dim'=>'E','desc'=>"Goût pour la compagnie et les situations sociales animées."),
            'E3_ASS' => array('label'=>'Assertivité',            'dim'=>'E','desc'=>"Tendance à prendre le leadership et à s'affirmer."),
            'E4_ACT' => array('label'=>'Activité',               'dim'=>'E','desc'=>"Niveau d'énergie et de mouvement dans la vie quotidienne."),
            'E5_STI' => array('label'=>'Recherche de sensations','dim'=>'E','desc'=>"Attrait pour les stimulations fortes et les risques."),
            'E6_EMO' => array('label'=>'Émotions positives',     'dim'=>'E','desc'=>"Fréquence et intensité des émotions positives."),
            'A1_CON' => array('label'=>'Confiance',              'dim'=>'A','desc'=>"Disposition à croire en la bonne foi des autres."),
            'A2_DRO' => array('label'=>'Droiture',               'dim'=>'A','desc'=>"Authenticité et refus de la tromperie."),
            'A3_ALT' => array('label'=>'Altruisme',              'dim'=>'A','desc'=>"Plaisir à aider les autres sans contrepartie."),
            'A4_COM' => array('label'=>'Compliance',             'dim'=>'A','desc'=>"Tendance à céder et à éviter les confrontations."),
            'A5_MOD' => array('label'=>'Modestie',               'dim'=>'A','desc'=>"Humilité et discrétion sur ses accomplissements."),
            'A6_SEN' => array('label'=>'Sensibilité',            'dim'=>'A','desc'=>"Empathie et préoccupation pour la souffrance des autres."),
            'N1_ANX' => array('label'=>'Anxiété',                'dim'=>'N','desc'=>"Tendance à s'inquiéter et à ressentir une tension chronique."),
            'N2_HOS' => array('label'=>'Hostilité',              'dim'=>'N','desc'=>"Irritabilité et tendance à la colère."),
            'N3_DEP' => array('label'=>'Dépression',             'dim'=>'N','desc'=>"Tendance aux humeurs basses et au découragement."),
            'N4_CON' => array('label'=>'Conscience de soi',      'dim'=>'N','desc'=>"Sensibilité au regard des autres et peur du jugement."),
            'N5_IMP' => array('label'=>'Impulsivité',            'dim'=>'N','desc'=>"Difficulté à résister aux envies immédiates."),
            'N6_VUL' => array('label'=>'Vulnérabilité',          'dim'=>'N','desc'=>"Tendance à se sentir dépassé(e) sous l'effet du stress."),
        );
    }

    public static function get_normes() {
        return array(
            // Score brut sur 16 (4 items × 4) — recalibrées depuis normes 1-5
            'O1_FAN' => array('mean'=>9.8,'sd'=>2.7),
            'O2_EST' => array('mean'=>10.4,'sd'=>2.6),
            'O3_SEN' => array('mean'=>10.9,'sd'=>2.4),
            'O4_ACT' => array('mean'=>9.5,'sd'=>2.6),
            'O5_IDE' => array('mean'=>10.1,'sd'=>2.9),
            'O6_VAL' => array('mean'=>9.1,'sd'=>2.7),
            'C1_COM' => array('mean'=>11.4,'sd'=>2.4),
            'C2_ORD' => array('mean'=>9.8,'sd'=>2.8),
            'C3_DEV' => array('mean'=>11.6,'sd'=>2.3),
            'C4_REA' => array('mean'=>10.7,'sd'=>2.5),
            'C5_DIS' => array('mean'=>10.2,'sd'=>2.7),
            'C6_DEL' => array('mean'=>9.7,'sd'=>2.6),
            'E1_CHA' => array('mean'=>11.0,'sd'=>2.4),
            'E2_GRE' => array('mean'=>9.4,'sd'=>2.8),
            'E3_ASS' => array('mean'=>9.6,'sd'=>2.7),
            'E4_ACT' => array('mean'=>10.1,'sd'=>2.6),
            'E5_STI' => array('mean'=>8.2,'sd'=>2.8),
            'E6_EMO' => array('mean'=>10.7,'sd'=>2.5),
            'A1_CON' => array('mean'=>10.6,'sd'=>2.5),
            'A2_DRO' => array('mean'=>11.4,'sd'=>2.3),
            'A3_ALT' => array('mean'=>11.7,'sd'=>2.2),
            'A4_COM' => array('mean'=>9.9,'sd'=>2.6),
            'A5_MOD' => array('mean'=>10.0,'sd'=>2.7),
            'A6_SEN' => array('mean'=>11.0,'sd'=>2.4),
            'N1_ANX' => array('mean'=>8.8,'sd'=>3.0),
            'N2_HOS' => array('mean'=>7.4,'sd'=>2.7),
            'N3_DEP' => array('mean'=>7.8,'sd'=>2.9),
            'N4_CON' => array('mean'=>8.6,'sd'=>2.9),
            'N5_IMP' => array('mean'=>9.4,'sd'=>2.7),
            'N6_VUL' => array('mean'=>7.5,'sd'=>2.8),
        );
    }

    public static function get_all() {
        return array_merge( self::get_ocean(), self::get_ds() );
    }

    // =========================================================
    // 120 questions OCEAN — 4 par facette (2 directs + 2 inv.)
    // Sélection des items les plus discriminants de la banque 248q
    // =========================================================
    public static function get_ocean() {
        return array(

        // ── O1 Fantaisie ──────────────────────────────────────
        array('id'=>1,  'dim'=>'O','facette'=>'O1_FAN','inv'=>false,'texte'=>"Mon imagination produit souvent des images, des scènes ou des histoires spontanées."),
        array('id'=>2,  'dim'=>'O','facette'=>'O1_FAN','inv'=>false,'texte'=>"Les univers fictifs me semblent aussi réels et enrichissants que le monde ordinaire."),
        array('id'=>3,  'dim'=>'O','facette'=>'O1_FAN','inv'=>true, 'texte'=>"Je consacre rarement du temps à imaginer des choses qui n'existent pas."),
        array('id'=>4,  'dim'=>'O','facette'=>'O1_FAN','inv'=>true, 'texte'=>"Je préfère observer le monde tel qu'il est plutôt que d'imaginer ce qu'il pourrait être."),

        // ── O2 Esthétique ─────────────────────────────────────
        array('id'=>5,  'dim'=>'O','facette'=>'O2_EST','inv'=>false,'texte'=>"Une belle mélodie, un tableau ou un coucher de soleil peuvent me toucher profondément."),
        array('id'=>6,  'dim'=>'O','facette'=>'O2_EST','inv'=>false,'texte'=>"La qualité visuelle ou sonore d'un environnement influence beaucoup mon humeur."),
        array('id'=>7,  'dim'=>'O','facette'=>'O2_EST','inv'=>true, 'texte'=>"L'esthétique d'un lieu ou d'un objet ne compte guère pour moi — seule la fonctionnalité importe."),
        array('id'=>8,  'dim'=>'O','facette'=>'O2_EST','inv'=>true, 'texte'=>"La musique, la peinture ou la poésie me laissent généralement de marbre."),

        // ── O3 Sentiments ─────────────────────────────────────
        array('id'=>9,  'dim'=>'O','facette'=>'O3_SEN','inv'=>false,'texte'=>"Je prends le temps d'identifier et de nommer précisément ce que je ressens."),
        array('id'=>10, 'dim'=>'O','facette'=>'O3_SEN','inv'=>false,'texte'=>"Je considère ma vie émotionnelle comme une source de connaissance de moi-même."),
        array('id'=>11, 'dim'=>'O','facette'=>'O3_SEN','inv'=>true, 'texte'=>"Je fais rarement attention à mes états émotionnels du moment."),
        array('id'=>12, 'dim'=>'O','facette'=>'O3_SEN','inv'=>true, 'texte'=>"Les émotions me semblent des réactions irrationnelles qu'il vaut mieux mettre de côté."),

        // ── O4 Actions ────────────────────────────────────────
        array('id'=>13, 'dim'=>'O','facette'=>'O4_ACT','inv'=>false,'texte'=>"J'essaie régulièrement de nouvelles activités ou de nouveaux loisirs."),
        array('id'=>14, 'dim'=>'O','facette'=>'O4_ACT','inv'=>false,'texte'=>"Quand je voyage, je cherche des expériences insolites plutôt que les circuits classiques."),
        array('id'=>15, 'dim'=>'O','facette'=>'O4_ACT','inv'=>true, 'texte'=>"Je me sens plus à l'aise dans des activités que je connais bien que dans des expériences inconnues."),
        array('id'=>16, 'dim'=>'O','facette'=>'O4_ACT','inv'=>true, 'texte'=>"Changer mes habitudes me demande un effort et ne me procure guère de plaisir."),

        // ── O5 Idées ──────────────────────────────────────────
        array('id'=>17, 'dim'=>'O','facette'=>'O5_IDE','inv'=>false,'texte'=>"Une idée stimulante peut m'occuper l'esprit pendant des jours."),
        array('id'=>18, 'dim'=>'O','facette'=>'O5_IDE','inv'=>false,'texte'=>"J'apprécie les débats intellectuels même sur des sujets sans application pratique immédiate."),
        array('id'=>19, 'dim'=>'O','facette'=>'O5_IDE','inv'=>true, 'texte'=>"Je préfère nettement les informations pratiques et concrètes aux théories abstraites."),
        array('id'=>20, 'dim'=>'O','facette'=>'O5_IDE','inv'=>true, 'texte'=>"Les débats philosophiques me semblent stériles et sans intérêt réel."),

        // ── O6 Valeurs ────────────────────────────────────────
        array('id'=>21, 'dim'=>'O','facette'=>'O6_VAL','inv'=>false,'texte'=>"Je remets régulièrement en question certaines de mes croyances ou valeurs."),
        array('id'=>22, 'dim'=>'O','facette'=>'O6_VAL','inv'=>false,'texte'=>"Je suis prêt(e) à réviser une conviction profonde si les faits le justifient."),
        array('id'=>23, 'dim'=>'O','facette'=>'O6_VAL','inv'=>true, 'texte'=>"Je pense que les valeurs transmises par ma famille ou ma culture sont les bonnes et méritent d'être préservées."),
        array('id'=>24, 'dim'=>'O','facette'=>'O6_VAL','inv'=>true, 'texte'=>"Je me sens mal à l'aise face aux personnes qui défient les traditions et les conventions établies."),

        // ── C1 Compétence ─────────────────────────────────────
        array('id'=>25, 'dim'=>'C','facette'=>'C1_COM','inv'=>false,'texte'=>"En général, je suis convaincu(e) de pouvoir mener à bien ce que j'entreprends."),
        array('id'=>26, 'dim'=>'C','facette'=>'C1_COM','inv'=>false,'texte'=>"Face à un problème difficile, je fais confiance à ma capacité à trouver une solution."),
        array('id'=>27, 'dim'=>'C','facette'=>'C1_COM','inv'=>true, 'texte'=>"Je doute souvent de ma capacité à faire face aux défis importants."),
        array('id'=>28, 'dim'=>'C','facette'=>'C1_COM','inv'=>true, 'texte'=>"Il m'arrive fréquemment de penser que les autres feraient mieux que moi à ma place."),

        // ── C2 Ordre ──────────────────────────────────────────
        array('id'=>29, 'dim'=>'C','facette'=>'C2_ORD','inv'=>false,'texte'=>"Je veille à ce que mes affaires, fichiers et documents soient toujours bien organisés."),
        array('id'=>30, 'dim'=>'C','facette'=>'C2_ORD','inv'=>false,'texte'=>"Un environnement bien ordonné me permet de penser et de travailler plus efficacement."),
        array('id'=>31, 'dim'=>'C','facette'=>'C2_ORD','inv'=>true, 'texte'=>"Je peux travailler sans problème dans un environnement désordonné."),
        array('id'=>32, 'dim'=>'C','facette'=>'C2_ORD','inv'=>true, 'texte'=>"Mon espace de travail ou de vie est souvent en désordre sans que cela me dérange."),

        // ── C3 Sens du devoir ─────────────────────────────────
        array('id'=>33, 'dim'=>'C','facette'=>'C3_DEV','inv'=>false,'texte'=>"Je respecte mes engagements même quand cela m'est difficile ou peu pratique."),
        array('id'=>34, 'dim'=>'C','facette'=>'C3_DEV','inv'=>false,'texte'=>"Je me sens mal à l'aise lorsque je dois manquer à un engagement que j'ai pris."),
        array('id'=>35, 'dim'=>'C','facette'=>'C3_DEV','inv'=>true, 'texte'=>"Il m'arrive de contourner une règle si je pense que le résultat final en vaut la peine."),
        array('id'=>36, 'dim'=>'C','facette'=>'C3_DEV','inv'=>true, 'texte'=>"Je ne me sens pas toujours obligé(e) de respecter des engagements pris à la légère."),

        // ── C4 Recherche de réussite ──────────────────────────
        array('id'=>37, 'dim'=>'C','facette'=>'C4_REA','inv'=>false,'texte'=>"Je me fixe régulièrement des objectifs ambitieux que je travaille à atteindre."),
        array('id'=>38, 'dim'=>'C','facette'=>'C4_REA','inv'=>false,'texte'=>"Je consacre beaucoup d'énergie à progresser et à développer mes compétences."),
        array('id'=>39, 'dim'=>'C','facette'=>'C4_REA','inv'=>true, 'texte'=>"Je me contente facilement de résultats ordinaires sans chercher à exceller."),
        array('id'=>40, 'dim'=>'C','facette'=>'C4_REA','inv'=>true, 'texte'=>"L'ambition et la réussite ne sont pas des valeurs importantes pour moi."),

        // ── C5 Autodiscipline ─────────────────────────────────
        array('id'=>41, 'dim'=>'C','facette'=>'C5_DIS','inv'=>false,'texte'=>"Je me mets au travail même quand je n'en ai pas envie."),
        array('id'=>42, 'dim'=>'C','facette'=>'C5_DIS','inv'=>false,'texte'=>"Je résiste aux distractions et reste concentré(e) sur ma priorité du moment."),
        array('id'=>43, 'dim'=>'C','facette'=>'C5_DIS','inv'=>true, 'texte'=>"Je remets facilement les tâches peu agréables à plus tard."),
        array('id'=>44, 'dim'=>'C','facette'=>'C5_DIS','inv'=>true, 'texte'=>"Je me laisse souvent distraire avant d'avoir terminé ce que j'avais commencé."),

        // ── C6 Délibération ───────────────────────────────────
        array('id'=>45, 'dim'=>'C','facette'=>'C6_DEL','inv'=>false,'texte'=>"Je prends le temps de peser soigneusement les options avant toute décision importante."),
        array('id'=>46, 'dim'=>'C','facette'=>'C6_DEL','inv'=>false,'texte'=>"Avant d'agir, je réfléchis aux conséquences possibles à court et à long terme."),
        array('id'=>47, 'dim'=>'C','facette'=>'C6_DEL','inv'=>true, 'texte'=>"J'agis souvent sur une impulsion sans m'attarder à analyser la situation."),
        array('id'=>48, 'dim'=>'C','facette'=>'C6_DEL','inv'=>true, 'texte'=>"Je prends fréquemment des décisions rapides que je regrette ensuite."),

        // ── E1 Chaleur ────────────────────────────────────────
        array('id'=>49, 'dim'=>'E','facette'=>'E1_CHA','inv'=>false,'texte'=>"Je montre facilement mon affection aux personnes qui me sont proches."),
        array('id'=>50, 'dim'=>'E','facette'=>'E1_CHA','inv'=>false,'texte'=>"Je m'intéresse sincèrement à la vie et aux expériences des gens que je rencontre."),
        array('id'=>51, 'dim'=>'E','facette'=>'E1_CHA','inv'=>true, 'texte'=>"Je garde naturellement une certaine réserve émotionnelle vis-à-vis des autres."),
        array('id'=>52, 'dim'=>'E','facette'=>'E1_CHA','inv'=>true, 'texte'=>"On me dit parfois que je suis distant(e) ou difficile à cerner."),

        // ── E2 Grégarité ──────────────────────────────────────
        array('id'=>53, 'dim'=>'E','facette'=>'E2_GRE','inv'=>false,'texte'=>"J'aime les soirées animées avec beaucoup de monde et d'interactions."),
        array('id'=>54, 'dim'=>'E','facette'=>'E2_GRE','inv'=>false,'texte'=>"Une foule animée ou un groupe nombreux me stimule plutôt qu'il ne m'épuise."),
        array('id'=>55, 'dim'=>'E','facette'=>'E2_GRE','inv'=>true, 'texte'=>"Je préfère passer la soirée avec une ou deux personnes proches plutôt qu'en grand groupe."),
        array('id'=>56, 'dim'=>'E','facette'=>'E2_GRE','inv'=>true, 'texte'=>"Après une longue journée sociale, j'ai absolument besoin de solitude pour me ressourcer."),

        // ── E3 Assertivité ────────────────────────────────────
        array('id'=>57, 'dim'=>'E','facette'=>'E3_ASS','inv'=>false,'texte'=>"Dans la plupart des groupes, je prends naturellement un rôle de meneur."),
        array('id'=>58, 'dim'=>'E','facette'=>'E3_ASS','inv'=>false,'texte'=>"Je n'hésite pas à exprimer clairement mon opinion, même si elle est impopulaire."),
        array('id'=>59, 'dim'=>'E','facette'=>'E3_ASS','inv'=>true, 'texte'=>"Je laisse volontiers les autres mener les discussions ou prendre les décisions."),
        array('id'=>60, 'dim'=>'E','facette'=>'E3_ASS','inv'=>true, 'texte'=>"Je me sens mal à l'aise lorsqu'on me demande de diriger un groupe."),

        // ── E4 Activité ───────────────────────────────────────
        array('id'=>61, 'dim'=>'E','facette'=>'E4_ACT','inv'=>false,'texte'=>"Mon quotidien est généralement bien rempli et je suis souvent en mouvement."),
        array('id'=>62, 'dim'=>'E','facette'=>'E4_ACT','inv'=>false,'texte'=>"Un rythme de vie soutenu me stimule plutôt qu'il ne me fatigue."),
        array('id'=>63, 'dim'=>'E','facette'=>'E4_ACT','inv'=>true, 'texte'=>"Je mène un rythme de vie assez calme et ne cherche pas à le remplir davantage."),
        array('id'=>64, 'dim'=>'E','facette'=>'E4_ACT','inv'=>true, 'texte'=>"Un agenda chargé m'angoisse plus qu'il ne me motive."),

        // ── E5 Recherche de sensations ────────────────────────
        array('id'=>65, 'dim'=>'E','facette'=>'E5_STI','inv'=>false,'texte'=>"Les activités à sensations fortes me plaisent vraiment."),
        array('id'=>66, 'dim'=>'E','facette'=>'E5_STI','inv'=>false,'texte'=>"Je suis attiré(e) par les situations qui comportent une part de risque ou d'imprévu."),
        array('id'=>67, 'dim'=>'E','facette'=>'E5_STI','inv'=>true, 'texte'=>"Je préfère la sécurité et la stabilité aux situations risquées ou très stimulantes."),
        array('id'=>68, 'dim'=>'E','facette'=>'E5_STI','inv'=>true, 'texte'=>"Les activités à risque ne m'attirent pas — je les trouve stressantes plus qu'excitantes."),

        // ── E6 Émotions positives ─────────────────────────────
        array('id'=>69, 'dim'=>'E','facette'=>'E6_EMO','inv'=>false,'texte'=>"Je ris facilement et je trouve souvent des raisons d'être joyeux(se)."),
        array('id'=>70, 'dim'=>'E','facette'=>'E6_EMO','inv'=>false,'texte'=>"Je suis souvent de bonne humeur, même sans raison particulière."),
        array('id'=>71, 'dim'=>'E','facette'=>'E6_EMO','inv'=>true, 'texte'=>"Je suis rarement exubérant(e) ou particulièrement enthousiaste."),
        array('id'=>72, 'dim'=>'E','facette'=>'E6_EMO','inv'=>true, 'texte'=>"Mon humeur de fond est plutôt sérieuse ou neutre que joyeuse."),

        // ── A1 Confiance ──────────────────────────────────────
        array('id'=>73, 'dim'=>'A','facette'=>'A1_CON','inv'=>false,'texte'=>"Je suppose naturellement que les gens ont de bonnes intentions."),
        array('id'=>74, 'dim'=>'A','facette'=>'A1_CON','inv'=>false,'texte'=>"Je pense que la majorité des gens sont fondamentalement honnêtes."),
        array('id'=>75, 'dim'=>'A','facette'=>'A1_CON','inv'=>true, 'texte'=>"Je me méfie souvent des inconnus jusqu'à ce qu'ils aient prouvé leur bonne foi."),
        array('id'=>76, 'dim'=>'A','facette'=>'A1_CON','inv'=>true, 'texte'=>"Je pense que beaucoup de gens cherchent avant tout à servir leurs propres intérêts."),

        // ── A2 Droiture ───────────────────────────────────────
        array('id'=>77, 'dim'=>'A','facette'=>'A2_DRO','inv'=>false,'texte'=>"Mes intentions sont claires et transparentes — je ne cache rien pour manipuler les autres."),
        array('id'=>78, 'dim'=>'A','facette'=>'A2_DRO','inv'=>false,'texte'=>"Je dis ce que je pense, même quand la vérité est difficile à entendre."),
        array('id'=>79, 'dim'=>'A','facette'=>'A2_DRO','inv'=>true, 'texte'=>"Il m'arrive de dissimuler certaines informations pour protéger mes intérêts."),
        array('id'=>80, 'dim'=>'A','facette'=>'A2_DRO','inv'=>true, 'texte'=>"Je suis capable d'adapter ma présentation des faits pour orienter l'opinion des autres."),

        // ── A3 Altruisme ──────────────────────────────────────
        array('id'=>81, 'dim'=>'A','facette'=>'A3_ALT','inv'=>false,'texte'=>"J'éprouve un réel plaisir à aider les autres, même si cela me coûte temps et énergie."),
        array('id'=>82, 'dim'=>'A','facette'=>'A3_ALT','inv'=>false,'texte'=>"Le bien-être des personnes qui m'entourent compte autant pour moi que le mien."),
        array('id'=>83, 'dim'=>'A','facette'=>'A3_ALT','inv'=>true, 'texte'=>"Je pense d'abord à mes propres besoins avant de me soucier de ceux des autres."),
        array('id'=>84, 'dim'=>'A','facette'=>'A3_ALT','inv'=>true, 'texte'=>"Je n'éprouve pas de satisfaction particulière à rendre service."),

        // ── A4 Compliance ─────────────────────────────────────
        array('id'=>85, 'dim'=>'A','facette'=>'A4_COM','inv'=>false,'texte'=>"Je préfère céder dans un désaccord plutôt que de créer un conflit inutile."),
        array('id'=>86, 'dim'=>'A','facette'=>'A4_COM','inv'=>false,'texte'=>"Je cherche un terrain d'entente même quand je suis convaincu(e) d'avoir raison."),
        array('id'=>87, 'dim'=>'A','facette'=>'A4_COM','inv'=>true, 'texte'=>"Je défends fermement ma position même face à une forte pression du groupe."),
        array('id'=>88, 'dim'=>'A','facette'=>'A4_COM','inv'=>true, 'texte'=>"Je n'hésite pas à entrer en confrontation directe quand je pense avoir raison."),

        // ── A5 Modestie ───────────────────────────────────────
        array('id'=>89, 'dim'=>'A','facette'=>'A5_MOD','inv'=>false,'texte'=>"Je n'aime pas me mettre en avant ou souligner mes propres accomplissements."),
        array('id'=>90, 'dim'=>'A','facette'=>'A5_MOD','inv'=>false,'texte'=>"Je me considère comme égal(e) aux autres, sans sentiment de supériorité."),
        array('id'=>91, 'dim'=>'A','facette'=>'A5_MOD','inv'=>true, 'texte'=>"Je pense mériter davantage de reconnaissance que ce que je reçois."),
        array('id'=>92, 'dim'=>'A','facette'=>'A5_MOD','inv'=>true, 'texte'=>"J'ai une haute opinion de moi-même et je n'en fais pas mystère."),

        // ── A6 Sensibilité ────────────────────────────────────
        array('id'=>93, 'dim'=>'A','facette'=>'A6_SEN','inv'=>false,'texte'=>"La détresse des autres me touche sincèrement et me donne envie d'agir."),
        array('id'=>94, 'dim'=>'A','facette'=>'A6_SEN','inv'=>false,'texte'=>"Je perçois intuitivement quand quelqu'un souffre, même s'il ne le dit pas."),
        array('id'=>95, 'dim'=>'A','facette'=>'A6_SEN','inv'=>true, 'texte'=>"La souffrance des autres ne m'affecte pas particulièrement."),
        array('id'=>96, 'dim'=>'A','facette'=>'A6_SEN','inv'=>true, 'texte'=>"Je pense que chacun doit régler ses problèmes par lui-même sans chercher de la pitié."),

        // ── N1 Anxiété ────────────────────────────────────────
        array('id'=>97,  'dim'=>'N','facette'=>'N1_ANX','inv'=>false,'texte'=>"Je me fais facilement du souci, même pour des choses sans grande importance."),
        array('id'=>98,  'dim'=>'N','facette'=>'N1_ANX','inv'=>false,'texte'=>"L'incertitude sur l'avenir me génère une anxiété difficile à contrôler."),
        array('id'=>99,  'dim'=>'N','facette'=>'N1_ANX','inv'=>true, 'texte'=>"Je fais face à l'incertitude avec sérénité — elle ne m'angoisse pas."),
        array('id'=>100, 'dim'=>'N','facette'=>'N1_ANX','inv'=>true, 'texte'=>"Je suis rarement préoccupé(e) par ce qui pourrait mal tourner."),

        // ── N2 Hostilité ──────────────────────────────────────
        array('id'=>101, 'dim'=>'N','facette'=>'N2_HOS','inv'=>false,'texte'=>"Je m'énerve assez vite quand les choses ne se passent pas comme je l'avais prévu."),
        array('id'=>102, 'dim'=>'N','facette'=>'N2_HOS','inv'=>false,'texte'=>"J'ai du mal à masquer ma frustration quand les autres ne font pas ce qu'ils ont promis."),
        array('id'=>103, 'dim'=>'N','facette'=>'N2_HOS','inv'=>true, 'texte'=>"Je garde mon calme même face à des comportements qui pourraient me contrarier."),
        array('id'=>104, 'dim'=>'N','facette'=>'N2_HOS','inv'=>true, 'texte'=>"Je suis patient(e) et peu irritable, même dans les situations difficiles."),

        // ── N3 Dépression ─────────────────────────────────────
        array('id'=>105, 'dim'=>'N','facette'=>'N3_DEP','inv'=>false,'texte'=>"Il m'arrive de traverser des périodes de profond découragement sans raison évidente."),
        array('id'=>106, 'dim'=>'N','facette'=>'N3_DEP','inv'=>false,'texte'=>"Je me sens parfois inutile ou convaincu(e) de ne pas valoir grand-chose."),
        array('id'=>107, 'dim'=>'N','facette'=>'N3_DEP','inv'=>true, 'texte'=>"Je me sens rarement déprimé(e) ou profondément découragé(e)."),
        array('id'=>108, 'dim'=>'N','facette'=>'N3_DEP','inv'=>true, 'texte'=>"Même dans les moments difficiles, je conserve un sentiment de bien-être et d'espoir."),

        // ── N4 Conscience de soi ──────────────────────────────
        array('id'=>109, 'dim'=>'N','facette'=>'N4_CON','inv'=>false,'texte'=>"Je suis très sensible à ce que les autres pensent ou disent de moi."),
        array('id'=>110, 'dim'=>'N','facette'=>'N4_CON','inv'=>false,'texte'=>"Je redoute d'être perçu(e) négativement par mon entourage."),
        array('id'=>111, 'dim'=>'N','facette'=>'N4_CON','inv'=>true, 'texte'=>"L'opinion des autres sur moi m'affecte peu."),
        array('id'=>112, 'dim'=>'N','facette'=>'N4_CON','inv'=>true, 'texte'=>"Je me comporte de la même façon, que les autres me regardent ou non."),

        // ── N5 Impulsivité ────────────────────────────────────
        array('id'=>113, 'dim'=>'N','facette'=>'N5_IMP','inv'=>false,'texte'=>"J'ai du mal à résister aux envies du moment, même quand elles vont contre mes intérêts."),
        array('id'=>114, 'dim'=>'N','facette'=>'N5_IMP','inv'=>false,'texte'=>"Sous l'effet du stress, je retombe facilement dans de mauvaises habitudes."),
        array('id'=>115, 'dim'=>'N','facette'=>'N5_IMP','inv'=>true, 'texte'=>"Je contrôle bien mes envies et mes impulsions, même sous pression."),
        array('id'=>116, 'dim'=>'N','facette'=>'N5_IMP','inv'=>true, 'texte'=>"Je suis capable de différer une satisfaction immédiate pour un bénéfice à long terme."),

        // ── N6 Vulnérabilité ──────────────────────────────────
        array('id'=>117, 'dim'=>'N','facette'=>'N6_VUL','inv'=>false,'texte'=>"Quand la pression monte, je perds rapidement mes moyens."),
        array('id'=>118, 'dim'=>'N','facette'=>'N6_VUL','inv'=>false,'texte'=>"Face à un stress intense, je me sens incapable de prendre des décisions claires."),
        array('id'=>119, 'dim'=>'N','facette'=>'N6_VUL','inv'=>true, 'texte'=>"Je reste lucide et efficace même dans les situations très stressantes."),
        array('id'=>120, 'dim'=>'N','facette'=>'N6_VUL','inv'=>true, 'texte'=>"Les crises me mobilisent plutôt qu'elles ne me paralysent."),
        );
    }

    // ── 8 questions Désirabilité Sociale (ids 121–128) ────────
    public static function get_ds() {
        return array(
            array('id'=>121,'dim'=>'DS','inv'=>false,'texte'=>"Je n'ai jamais dit quelque chose de faux pour obtenir ce que je voulais."),
            array('id'=>122,'dim'=>'DS','inv'=>false,'texte'=>"Je suis toujours courtois(e) et respectueux(se), même envers les personnes qui m'irritent."),
            array('id'=>123,'dim'=>'DS','inv'=>true, 'texte'=>"Il m'arrive de critiquer les gens en leur absence."),
            array('id'=>124,'dim'=>'DS','inv'=>false,'texte'=>"Je ne ressens jamais d'envie ou de jalousie envers les autres."),
            array('id'=>125,'dim'=>'DS','inv'=>true, 'texte'=>"Il m'est arrivé de vouloir abandonner une responsabilité que j'avais acceptée."),
            array('id'=>126,'dim'=>'DS','inv'=>false,'texte'=>"Je n'ai jamais laissé transparaître de la colère de façon inappropriée."),
            array('id'=>127,'dim'=>'DS','inv'=>false,'texte'=>"Je n'ai jamais profité d'une situation au détriment de quelqu'un d'autre."),
            array('id'=>128,'dim'=>'DS','inv'=>false,'texte'=>"Mes comportements sont toujours parfaitement en accord avec mes valeurs déclarées."),
        );
    }

    // 8 questions par page → 16 étapes (au lieu de 31)
    public static function get_steps( $per_page = 8 ) {
        return array_chunk( self::get_all(), $per_page );
    }
}
