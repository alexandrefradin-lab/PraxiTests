<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function praxicare_ajax_save() {
    check_ajax_referer( 'praxicare_nonce', 'nonce' );

    $prenom       = sanitize_text_field( wp_unslash( $_POST['prenom'] ?? '' ) );
    $email        = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $has_superior = isset( $_POST['has_superior'] ) && $_POST['has_superior'] === '1';

    if ( empty( $email ) || ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'Email invalide.' ) );
    }

    $raw      = isset( $_POST['reponses'] ) ? (array) $_POST['reponses'] : array();
    $reponses = array_map( 'absint', $raw );

    // ── SCORING KARASEK ──────────────────────────────────────────────

    $score_demandes = 0;
    for ( $i = 1; $i <= 9; $i++ ) {
        $val = isset( $reponses[ 'D' . $i ] ) ? $reponses[ 'D' . $i ] : 1;
        $val = max(1, min(4, $val));
        $score_demandes += ( $i === 4 ) ? (5 - $val) : $val;
    }

    $score_latitude = 0;
    for ( $i = 1; $i <= 9; $i++ ) {
        $val = isset( $reponses[ 'L' . $i ] ) ? $reponses[ 'L' . $i ] : 1;
        $score_latitude += max(1, min(4, $val));
    }

    $score_soutien = 0;
    $soutien_start = $has_superior ? 1 : 5;
    for ( $i = $soutien_start; $i <= 8; $i++ ) {
        $val = isset( $reponses[ 'S' . $i ] ) ? $reponses[ 'S' . $i ] : 1;
        $score_soutien += max(1, min(4, $val));
    }
    $seuil_soutien = $has_superior ? 21 : 10;

    // ── SCORING MBI ──────────────────────────────────────────────────

    $score_ee = 0;
    for ( $i = 1; $i <= 9; $i++ ) {
        $val = isset( $reponses[ 'EE' . $i ] ) ? $reponses[ 'EE' . $i ] : 0;
        $score_ee += max(0, min(3, $val));
    }

    $score_dp = 0;
    for ( $i = 1; $i <= 5; $i++ ) {
        $val = isset( $reponses[ 'DP' . $i ] ) ? $reponses[ 'DP' . $i ] : 0;
        $score_dp += max(0, min(3, $val));
    }

    $score_ap = 0;
    for ( $i = 1; $i <= 8; $i++ ) {
        $val = isset( $reponses[ 'AP' . $i ] ) ? $reponses[ 'AP' . $i ] : 0;
        $score_ap += (3 - max(0, min(3, $val)));
    }

    // ── PROFIL ───────────────────────────────────────────────────────
    $profil = praxicare_get_profil( $score_demandes, $score_latitude, $score_soutien, $score_ee, $score_dp, $score_ap, $seuil_soutien );

    // ── SAUVEGARDE BDD ───────────────────────────────────────────────
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'praxicare_results',
        array(
            'prenom'         => $prenom,
            'email'          => $email,
            'score_demandes' => $score_demandes,
            'score_latitude' => $score_latitude,
            'score_soutien'  => $score_soutien,
            'score_ee'       => $score_ee,
            'score_dp'       => $score_dp,
            'score_ap'       => $score_ap,
            'profil'         => $profil['id'],
        ),
        array( '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s' )
    );

    // ── ENVOI EMAIL ──────────────────────────────────────────────────
    praxicare_send_email( $prenom, $email, $profil, array(
        'demandes' => $score_demandes,
        'latitude' => $score_latitude,
        'soutien'  => $score_soutien,
        'ee'       => $score_ee,
        'dp'       => $score_dp,
        'ap'       => $score_ap,
    ));

    wp_send_json_success( array(
        'profil' => $profil,
        'scores' => array(
            'demandes' => $score_demandes,
            'latitude' => $score_latitude,
            'soutien'  => $score_soutien,
            'ee'       => $score_ee,
            'dp'       => $score_dp,
            'ap'       => $score_ap,
        ),
    ));
}

function praxicare_get_profil( $demandes, $latitude, $soutien, $ee, $dp, $ap, $seuil_soutien = 21 ) {

    // ── Karasek ──────────────────────────────────────────────────────
    $job_strain = ( $demandes >= 22 && $latitude <= 21 );
    $iso_strain = ( $job_strain && $soutien <= $seuil_soutien );
    $passif     = ( $demandes < 22 && $latitude <= 21 );
    $actif      = ( $demandes >= 22 && $latitude > 21 );
    $detendu    = ( $demandes < 22 && $latitude > 21 );

    // ── MBI — 3 niveaux de burnout ───────────────────────────────────
    $ee_eleve = $ee >= 19;
    $dp_eleve = $dp >= 10;
    $ap_eleve = $ap >= 17;

    $nb_dims_elevees = (int)$ee_eleve + (int)$dp_eleve + (int)$ap_eleve;

    $burnout_severe = ( $nb_dims_elevees === 3 );
    $burnout_modere = ( $nb_dims_elevees === 2 );
    $burnout_leger  = ( $nb_dims_elevees === 1 );
    $pas_burnout    = ( $nb_dims_elevees === 0 );

    // ── Cascade profils — du plus critique au moins critique ─────────

    // Niveau CRITIQUE
    if ( $iso_strain && $burnout_severe )
        return praxicare_profil_data( 'urgence' );

    // Niveau ROUGE
    if ( $job_strain && $burnout_severe )
        return praxicare_profil_data( 'souffrance_averee' );

    if ( $iso_strain && $burnout_modere )
        return praxicare_profil_data( 'alarme' );

    // Niveau ORANGE
    if ( $job_strain && $burnout_modere )
        return praxicare_profil_data( 'souffrance_installee' );

    if ( $iso_strain && $burnout_leger )
        return praxicare_profil_data( 'tension_isolee' );

    if ( ( $actif || $detendu || $passif ) && $burnout_severe )
        return praxicare_profil_data( 'epuisement_interne' );

    if ( ( $actif || $detendu || $passif ) && $burnout_modere )
        return praxicare_profil_data( 'fragilite' );

    if ( $job_strain && $burnout_leger )
        return praxicare_profil_data( 'risque_cumule' );

    // Niveau JAUNE
    if ( ( $actif || $detendu || $passif ) && $burnout_leger )
        return praxicare_profil_data( 'vigilance' );

    if ( $job_strain && $pas_burnout )
        return praxicare_profil_data( 'risque_situationnel' );

    if ( $passif && $pas_burnout )
        return praxicare_profil_data( 'sous_stimulation' );

    // Niveau VERT
    if ( $actif && $pas_burnout )
        return praxicare_profil_data( 'engagement_sain' );

    return praxicare_profil_data( 'bien_etre' );
}

function praxicare_profil_data( $id ) {
    $rdv = 'https://calendly.com/alex-fradin/15min';

    $profils = array(

        // ── VERT ─────────────────────────────────────────────────────

        'bien_etre' => array(
            'id' => 'bien_etre', 'niveau' => 'vert', 'emoji' => '✅',
            'titre' => 'Votre situation professionnelle est saine',
            'texte' => 'Votre charge de travail est raisonnable, vous avez de la marge pour prendre des décisions, et vous ne montrez pas de signe d\'épuisement. C\'est une bonne position, mais elle mérite d\'être entretenue activement, car les contextes professionnels peuvent évoluer vite.',
            'preconisations' => array(
                'Prenez le temps d\'identifier ce qui rend votre travail satisfaisant, et protégez-le',
                'Continuez à développer vos compétences et à entretenir vos relations professionnelles',
                'Restez attentif(ve) aux changements dans votre environnement : une restructuration, un nouveau manager, ou une surcharge passagère peuvent vite faire basculer la situation',
                array( 'texte' => '📅 Envie de capitaliser sur cette période favorable ? Réservez un entretien gratuit avec Alexandre', 'lien' => $rdv ),
            ),
        ),

        'engagement_sain' => array(
            'id' => 'engagement_sain', 'niveau' => 'vert', 'emoji' => '✅',
            'titre' => 'Vous êtes engagé(e) et en bonne forme professionnelle',
            'texte' => 'Votre travail est exigeant. Vous avez une vraie charge, mais vous disposez d\'assez d\'autonomie pour y faire face. Et votre état intérieur suit : pas de signe d\'épuisement. C\'est le profil de quelqu\'un qui avance bien, à condition de ne pas négliger sa récupération.',
            'preconisations' => array(
                'Vos efforts sont réels. Assurez-vous que votre repos l\'est aussi. Le cerveau a besoin de vraies coupures',
                'Apprenez à repérer vos premiers signaux d\'alerte (irritabilité, sommeil perturbé, perte de plaisir) avant qu\'ils s\'installent',
                'Vérifiez régulièrement que votre charge reste compatible avec votre énergie réelle, pas seulement avec vos objectifs',
                array( 'texte' => '📅 Un coaching peut vous aider à maintenir cette dynamique sur le long terme, entretien gratuit avec Alexandre', 'lien' => $rdv ),
            ),
        ),

        // ── JAUNE ─────────────────────────────────────────────────────

        'sous_stimulation' => array(
            'id' => 'sous_stimulation', 'niveau' => 'jaune', 'emoji' => '🟡',
            'titre' => 'Votre travail ne vous sollicite pas assez',
            'texte' => 'Vous n\'êtes pas débordé(e), c\'est plutôt le contraire. Peu de défis, peu de marge pour décider par vous-même, peu de sentiment d\'apprendre ou de progresser. Ce type de situation use autrement : elle génère de l\'ennui, un sentiment d\'inutilité, et parfois une vraie démotivation. Ce n\'est pas anodin.',
            'preconisations' => array(
                'Mettez des mots sur ce qui vous manque : est-ce de la reconnaissance, des responsabilités, plus de liberté, un travail plus varié ? C\'est le point de départ pour agir',
                'Parlez-en à votre manager, pas pour vous plaindre, mais pour proposer : un projet transversal, une montée en compétences, une mission différente',
                'Si votre poste actuel ne peut pas évoluer, il peut être utile de faire le point sur vos compétences et vos aspirations pour la suite',
                array( 'texte' => '📅 Un bilan de compétences peut vous aider à y voir plus clair, entretien gratuit avec Alexandre', 'lien' => $rdv ),
            ),
        ),

        'risque_situationnel' => array(
            'id' => 'risque_situationnel', 'niveau' => 'jaune', 'emoji' => '🟡',
            'titre' => 'Votre environnement de travail est sous pression',
            'texte' => 'Votre charge est élevée et vous avez peu de marge pour organiser votre travail à votre façon. Pour l\'instant, vous tenez le coup, votre état intérieur ne montre ne montre pas encore de signe d\'épuisement. Mais ce type de situation finit par user si rien ne change. Agir maintenant, c\'est éviter que ça s\'aggrave.',
            'preconisations' => array(
                'Ce n\'est pas vous le problème, c\'est votre environnement. Gardez ça en tête pour ne pas trop vous en vouloir',
                'Identifiez 1 ou 2 choses concrètes sur lesquelles vous avez réellement la main, et concentrez vos efforts là-dessus',
                'Parlez de votre charge à votre manager ou à un référent RH, sans attendre que ça déborde',
                array( 'texte' => '📅 Un coaching peut vous aider à reprendre la main sur la situation, entretien gratuit avec Alexandre', 'lien' => $rdv ),
            ),
        ),

        'vigilance' => array(
            'id' => 'vigilance', 'niveau' => 'jaune', 'emoji' => '🟡',
            'titre' => 'Vous montrez un premier signe d\'usure',
            'texte' => 'Votre contexte de travail n\'est pas particulièrement problématique, mais quelque chose commence à peser. Vous ressentez de la fatigue, un peu de distance avec vos collègues, ou un sentiment de moins bien vous en sortir. Ce n\'est pas encore grave, mais c\'est un signal à prendre au sérieux maintenant, avant que ça s\'installe.',
            'preconisations' => array(
                'Repérez à quel moment de la journée ou de la semaine vous vous sentez le plus à plat, et protégez ces plages',
                'L\'usure vient souvent de petites choses qui s\'accumulent. Essayez d\'identifier ce qui vous pèse le plus en ce moment',
                'Parlez-en à quelqu\'un de confiance : un ami, un proche, un médecin. Mettre des mots sur ce qu\'on ressent aide vraiment',
                array( 'texte' => '📅 Un entretien gratuit avec Alexandre peut vous aider à faire le point', 'lien' => $rdv ),
            ),
        ),

        // ── ORANGE ───────────────────────────────────────────────────

        'risque_cumule' => array(
            'id' => 'risque_cumule', 'niveau' => 'orange', 'emoji' => '🟠',
            'titre' => 'Pression au travail et premiers signes d\'usure',
            'texte' => 'Votre charge est forte, vous avez peu d\'autonomie, et vous commencez à ressentir les premiers effets : une fatigue qui s\'installe, un peu de distance, ou l\'impression de moins bien vous en sortir. Les deux s\'additionnent. Ce n\'est pas encore une situation de crise, mais le risque d\'aggravation est réel si rien ne change.',
            'preconisations' => array(
                'Ne minimisez pas ce que vous ressentez en vous disant "c\'est normal". C\'est peut-être courant, mais ce n\'est pas normal',
                'Essayez de réduire ce qui n\'est pas urgent dans votre agenda, même si tout semble prioritaire',
                'Parlez de votre charge à votre manager, votre médecin traitant, ou un référent RH. Ne portez pas ça seul(e)',
                array( 'texte' => '📅 Un accompagnement peut vous aider à sortir de cette spirale, entretien gratuit avec Alexandre', 'lien' => $rdv ),
            ),
        ),

        'tension_isolee' => array(
            'id' => 'tension_isolee', 'niveau' => 'orange', 'emoji' => '🟠',
            'titre' => 'Vous êtes sous pression et peu soutenu(e)',
            'texte' => 'Votre charge est élevée, vous avez peu d\'autonomie, et vous vous sentez peu soutenu(e), ni par votre hiérarchie, ni par vos collègues. En plus, quelque chose commence à se faire sentir intérieurement : de la fatigue, de l\'irritabilité, ou une distance qui s\'installe. L\'isolement dans ce genre de situation aggrave tout.',
            'preconisations' => array(
                'Le manque de soutien est souvent ce qui fait basculer une situation difficile en situation de souffrance. Il est urgent de ne pas rester seul(e) avec ça',
                'Identifiez une personne, dans ou hors du travail, à qui vous pouvez parler vraiment de ce que vous vivez',
                'Si vous n\'avez personne en interne, votre médecin traitant ou la médecine du travail sont des interlocuteurs légitimes et confidentiels',
                array( 'texte' => '📅 Parlez-en à Alexandre, entretien confidentiel et gratuit', 'lien' => $rdv ),
            ),
        ),

        'fragilite' => array(
            'id' => 'fragilite', 'niveau' => 'orange', 'emoji' => '🟠',
            'titre' => 'Vous êtes en train de vous épuiser',
            'texte' => 'Votre contexte de travail n\'est pas forcément le principal problème, mais vous portez quelque chose de lourd intérieurement. La fatigue est là, peut-être aussi un sentiment de distance par rapport à vos collègues ou à votre travail, ou l\'impression de ne plus vraiment vous en sortir. Ce n\'est pas une faiblesse. C\'est votre corps et votre tête qui vous disent que ça suffit.',
            'preconisations' => array(
                'Prenez ce signal au sérieux. Ce n\'est pas le moment de "pousser encore un peu". C\'est le moment d\'agir',
                'Parlez-en à votre médecin traitant. Vous n\'avez pas besoin d\'être "au fond du gouffre" pour consulter',
                'Réduisez les sollicitations non indispensables, même si vous avez l\'impression que tout est urgent',
                array( 'texte' => '📅 Un accompagnement personnalisé peut vraiment changer les choses, entretien gratuit avec Alexandre', 'lien' => $rdv ),
            ),
        ),

        'epuisement_interne' => array(
            'id' => 'epuisement_interne', 'niveau' => 'orange', 'emoji' => '🟠',
            'titre' => 'Vous êtes profondément épuisé(e)',
            'texte' => 'Les trois dimensions de l\'épuisement professionnel sont élevées chez vous : vous vous sentez à bout physiquement et émotionnellement, vous avez pris de la distance avec les gens autour de vous, et vous avez perdu le sentiment d\'être efficace dans votre travail. C\'est sérieux, même si votre environnement de travail n\'est pas nécessairement hostile. Quelque chose s\'est consumé, et ça ne se répare pas seul.',
            'preconisations' => array(
                'Ce que vous vivez a un nom : c\'est un épuisement professionnel (burnout). Reconnaître ça n\'est pas dramatiser. C\'est voir les choses en face',
                'Consultez votre médecin traitant rapidement. Un arrêt de travail peut être la bonne décision pour permettre à votre corps de récupérer',
                'Ne prenez pas de grande décision professionnelle dans cet état. Attendez d\'aller mieux avant de vous positionner sur quoi que ce soit',
                array( 'texte' => '📅 Un accompagnement peut vous aider à traverser cette période et à reconstruire, entretien gratuit avec Alexandre', 'lien' => $rdv ),
            ),
        ),

        'souffrance_installee' => array(
            'id' => 'souffrance_installee', 'niveau' => 'orange', 'emoji' => '🟠',
            'titre' => 'Votre travail vous pèse et ça commence à se voir',
            'texte' => 'Votre charge est forte, vous avez peu de latitude pour décider, et en plus vous commencez à ressentir les effets : fatigue, distance, sentiment de moins bien vous en sortir, sur deux dimensions au moins. Les deux faces du problème (l\'environnement et l\'état intérieur) sont maintenant présentes. Cette combinaison ne se résout pas d\'elle-même.',
            'preconisations' => array(
                'C\'est le moment d\'en parler : à votre médecin, au médecin du travail, ou à un proche. Pas dans 3 mois, maintenant',
                'La médecine du travail est là pour vous, pas pour votre employeur. Un entretien est confidentiel et gratuit',
                'Pensez à aménager votre charge : demandez un entretien RH pour évoquer la situation. Ce n\'est pas une faiblesse, c\'est de la gestion',
                array( 'texte' => '📅 Un coaching ou bilan peut vous aider à trouver une sortie, entretien gratuit avec Alexandre', 'lien' => $rdv ),
            ),
        ),

        // ── ROUGE ─────────────────────────────────────────────────────

        'alarme' => array(
            'id' => 'alarme', 'niveau' => 'rouge', 'emoji' => '🔴',
            'titre' => 'Votre situation est sérieuse, il faut agir',
            'texte' => 'Vous cumulez une pression professionnelle forte, un manque de soutien, et des signes d\'épuisement sur plusieurs dimensions. C\'est une combinaison qui use profondément et rapidement. Continuer sans rien changer risque d\'aggraver votre état de santé. Vous méritez du soutien, et il en existe.',
            'preconisations' => array(
                'Ne faites pas semblant que ça va. Parlez-en à quelqu\'un aujourd\'hui : un proche, votre médecin, un collègue de confiance',
                'Envisagez sérieusement un arrêt de travail. Votre médecin peut en juger avec vous. Ce n\'est pas abandonner, c\'est protéger votre santé',
                'Si vous ressentez une détresse intense, appelez le 3114. C\'est le numéro national de prévention, gratuit, disponible 24h/24',
                array( 'texte' => '📅 Parlez-en à Alexandre, entretien confidentiel et gratuit', 'lien' => $rdv ),
            ),
        ),

        'souffrance_averee' => array(
            'id' => 'souffrance_averee', 'niveau' => 'rouge', 'emoji' => '🔴',
            'titre' => 'Vous souffrez au travail, votre santé est en jeu',
            'texte' => 'Votre charge est très élevée, votre autonomie très faible, et les trois dimensions de l\'épuisement professionnel sont atteintes. Vous êtes à bout physiquement et émotionnellement, vous avez décroché des autres, et vous ne vous sentez plus efficace. Cette situation ne se résoudra pas d\'elle-même. Vous avez besoin d\'aide, et vous avez le droit d\'en demander.',
            'preconisations' => array(
                'Consultez votre médecin traitant en urgence. Parlez-lui de ce que vous vivez au travail. Un arrêt de travail peut être nécessaire pour protéger votre santé',
                'Contactez le médecin du travail pour un entretien confidentiel, il peut agir auprès de votre employeur sans vous exposer',
                'Si vous traversez une détresse intense, appelez le 3114. Des professionnels sont là pour vous écouter, gratuitement, 24h/24',
                array( 'texte' => '📅 Vous n\'êtes pas seul(e), parlez-en à Alexandre, entretien gratuit et confidentiel', 'lien' => $rdv ),
            ),
        ),

        // ── CRITIQUE ──────────────────────────────────────────────────

        'urgence' => array(
            'id' => 'urgence', 'niveau' => 'critique', 'emoji' => '🚨',
            'titre' => 'Votre situation est critique, vous n\'êtes pas seul(e)',
            'texte' => 'Vous cumulez tout : une charge extrême, quasiment aucune autonomie, peu ou pas de soutien autour de vous, et les trois dimensions de l\'épuisement professionnel sont au rouge. Ce que vous vivez est sérieux. Votre santé passe avant tout le reste : avant votre poste, avant vos obligations, avant ce que les autres pourraient penser. Des personnes formées peuvent vous aider maintenant.',
            'preconisations' => array(
                'Appelez le 3114 maintenant si vous ressentez une détresse intense. C\'est le numéro national de prévention du suicide, gratuit, confidentiel, disponible 24h/24',
                'Consultez votre médecin aujourd\'hui ou demain. Demandez un arrêt de travail immédiat, votre corps en a besoin',
                'Ne prenez aucune grande décision seul(e) dans cet état : ni démissionner, ni signer quoi que ce soit, ni rompre un engagement',
                array( 'texte' => '📅 Alexandre peut vous accompagner dès que vous vous sentez prêt(e), entretien gratuit et confidentiel', 'lien' => $rdv ),
            ),
        ),

    );

    return isset( $profils[ $id ] ) ? $profils[ $id ] : $profils['bien_etre'];
}
