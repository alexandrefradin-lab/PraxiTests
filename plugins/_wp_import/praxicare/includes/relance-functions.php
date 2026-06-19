<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PraxiCare — Système de relance automatique
 * J+2  : Ancrage émotionnel — valider le vécu, créer le lien
 * J+8  : Projection — "dans 3 mois, deux chemins possibles"
 * J+15 : Passage à l'acte — preuve sociale douce + CTA Calendly
 */

// ── Activation / désactivation du cron ─────────────────────────────────
function praxicare_schedule_cron() {
    if ( ! wp_next_scheduled( 'praxicare_relance_cron' ) ) {
        wp_schedule_event( time(), 'daily', 'praxicare_relance_cron' );
    }
}
add_action( 'praxicare_relance_cron', 'praxicare_run_relances' );

function praxicare_unschedule_cron() {
    wp_clear_scheduled_hook( 'praxicare_relance_cron' );
}

// ── Traitement quotidien ────────────────────────────────────────────────
function praxicare_run_relances() {
    global $wpdb;
    $table = $wpdb->prefix . 'praxicare_results';

    $rows = $wpdb->get_results( "SELECT * FROM {$table} WHERE email != '' AND (relance_active IS NULL OR relance_active = 1) ORDER BY created_at DESC" );

    foreach ( $rows as $row ) {
        $created = strtotime( $row->created_at );
        $now     = time();
        $jours   = floor( ( $now - $created ) / DAY_IN_SECONDS );

        // J+2 : entre 2 et 3 jours
        if ( $jours >= 2 && $jours < 3 && empty( $row->relance_2j ) ) {
            praxicare_envoyer_relance( $row, 2 );
            $wpdb->update( $table, array( 'relance_2j' => current_time('mysql') ), array( 'id' => $row->id ) );
        }

        // J+8 : entre 8 et 9 jours
        if ( $jours >= 8 && $jours < 9 && empty( $row->relance_8j ) ) {
            praxicare_envoyer_relance( $row, 8 );
            $wpdb->update( $table, array( 'relance_8j' => current_time('mysql') ), array( 'id' => $row->id ) );
        }

        // J+15 : entre 15 et 16 jours
        if ( $jours >= 15 && $jours < 16 && empty( $row->relance_15j ) ) {
            praxicare_envoyer_relance( $row, 15 );
            $wpdb->update( $table, array( 'relance_15j' => current_time('mysql') ), array( 'id' => $row->id ) );
        }
    }
}

// ── Envoi d'une relance ─────────────────────────────────────────────────
function praxicare_envoyer_relance( $row, $jours ) {
    $site_name   = get_bloginfo( 'name' );
    $admin_email = get_option( 'praxicare_admin_email', get_option( 'admin_email' ) );
    $rdv_url     = 'https://calendly.com/alex-fradin/15min';

    // Pas de From: — laissé au plugin SMTP tiers pour conformité SPF/DKIM OVH
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $site_name . ' <' . $admin_email . '>',
    );

    $profil_data = praxicare_get_profil(
        $row->score_demandes, $row->score_latitude, $row->score_soutien,
        $row->score_ee, $row->score_dp, $row->score_ap
    );

    $niveau = $profil_data['niveau']; // vert, jaune, orange, rouge, critique

    switch ( $jours ) {
        case 2:
            $sujet = praxicare_sujet_j2( $row->prenom, $niveau );
            $body  = praxicare_body_j2( $row->prenom, $profil_data, $rdv_url, $site_name );
            break;
        case 8:
            $sujet = praxicare_sujet_j8( $row->prenom, $niveau );
            $body  = praxicare_body_j8( $row->prenom, $profil_data, $rdv_url, $site_name );
            break;
        case 15:
            $sujet = praxicare_sujet_j15( $row->prenom, $niveau );
            $body  = praxicare_body_j15( $row->prenom, $profil_data, $rdv_url, $site_name );
            break;
        default:
            return;
    }

    $sent = wp_mail( $row->email, $sujet, $body, $headers );
    if ( ! $sent ) {
        error_log( 'PraxiCare — Échec relance J+' . $jours . ' pour : ' . $row->email );
    }
}

// ════════════════════════════════════════════════════════════════════════
// EMAIL J+2 — Ancrage émotionnel
// Objectif : valider le vécu, créer un lien humain, pas de vente
// Technique : reflet empathique + question ouverte introspective
// ════════════════════════════════════════════════════════════════════════

function praxicare_sujet_j2( $prenom, $niveau ) {
    $sujets = array(
        'vert'     => $prenom . ', une question pour vous depuis avant-hier…',
        'jaune'    => $prenom . ', est-ce que ça vous a parlé ?',
        'orange'   => $prenom . ', ce que vous avez mis en mots méritait de l\'être',
        'rouge'    => $prenom . ', merci d\'avoir pris ce temps pour vous',
        'critique' => $prenom . ', je pense à vous',
    );
    $custom = get_option( 'praxicare_sujet_2j_' . $niveau, '' );
    return $custom ? str_replace( 'Prénom', $prenom, $custom ) : ( $sujets[ $niveau ] ?? $sujets['jaune'] );
}

function praxicare_body_j2( $prenom, $profil, $rdv_url, $site_name ) {
    $niveau = $profil['niveau'];
    $titre  = $profil['titre'];

    $intro = array(
        'vert'     => 'Avant-hier, vous avez pris une dizaine de minutes pour regarder honnêtement comment vous allez au travail. Ce n\'est pas si courant. La plupart des gens font l\'impasse sur cette question.',
        'jaune'    => 'Avant-hier, vous avez pris le temps de faire un bilan sur votre situation professionnelle. Ce genre de démarche demande un peu de courage : regarder les choses en face, même quand elles ne sont pas tout à fait comme on le voudrait.',
        'orange'   => 'Avant-hier, vous avez mis en mots quelque chose que beaucoup de gens n\'arrivent pas à nommer. Le fait d\'avoir complété ce test jusqu\'au bout, ça dit quelque chose de vous, une forme de lucidité, et peut-être aussi un besoin de changement.',
        'rouge'    => 'Avant-hier, vous avez fait quelque chose d\'important : vous avez pris le temps de regarder ce que vous vivez vraiment. Dans une période difficile, c\'est souvent la dernière chose qu\'on s\'autorise. Je voulais vous dire que ça compte.',
        'critique' => 'Avant-hier, vous avez fait quelque chose de courageux. Regarder honnêtement sa situation quand on va mal, c\'est difficile. Je voulais juste vous dire que je pense à vous.',
    );
    $custom_intro = get_option( 'praxicare_intro_2j_' . $niveau, '' );
    if ( $custom_intro ) $intro[ $niveau ] = $custom_intro;

    $question = array(
        'vert'     => 'Une petite question : en lisant votre résultat, est-ce qu\'il y avait quelque chose qui vous a surpris(e) ? Ou au contraire, quelque chose que vous saviez déjà, quelque part, sans vraiment vouloir l\'admettre ?',
        'jaune'    => 'Je me pose une question en pensant à vous : est-ce que votre résultat a reflété quelque chose que vous ressentiez déjà depuis un moment ? Ou est-ce qu\'il vous a mis en face d\'une réalité que vous n\'aviez pas encore tout à fait formulée ?',
        'orange'   => 'Est-ce que votre résultat a mis des mots sur ce que vous ressentez depuis un moment ? Ou est-ce qu\'il vous a surpris(e) ? Dans les deux cas, il y a quelque chose à explorer.',
        'rouge'    => 'Je n\'attends rien de vous. Mais si vous souhaitez mettre des mots sur ce que vous traversez, à votre rythme, sans pression, je suis là.',
        'critique' => 'Je n\'attends rien. Mais si vous souhaitez parler de ce que vous traversez, même brièvement, je suis disponible.',
    );

    $closing = array(
        'vert'     => 'Ce type de prise de recul est souvent plus utile qu\'on ne le croit. Si ça vous inspire des réflexions, je serai ravi(e) d\'en discuter avec vous.',
        'jaune'    => 'Parfois, mettre des mots sur sa situation est déjà le début d\'un changement. Si vous avez envie d\'aller un peu plus loin dans cette réflexion, n\'hésitez pas à me le faire savoir.',
        'orange'   => 'Ce que vous vivez mérite une attention réelle. Pas un article de blog ou un podcast, mais un vrai échange avec quelqu\'un qui comprend ce type de situation.',
        'rouge'    => 'Vous n\'avez pas à porter ça seul(e). Si un échange peut vous aider à y voir plus clair, même un peu, je suis là.',
        'critique' => 'Vous n\'êtes pas seul(e). Si vous souhaitez parler, même quelques minutes, je suis là.',
    );

    $cta_texte = array(
        'vert'     => 'Prendre 15 min pour en discuter →',
        'jaune'    => 'Prendre 15 min pour en discuter →',
        'orange'   => 'Prendre un moment pour en parler →',
        'rouge'    => 'Parler à Alexandre, c\'est gratuit →',
        'critique' => 'Contacter Alexandre →',
    );

    $show_cta = true;

    return praxicare_wrap_relance(
        $prenom,
        'J\'avais une question pour vous, ' . esc_html($prenom) . '.',
        '<p>' . esc_html($intro[$niveau] ?? $intro['jaune']) . '</p>'
        . '<p>' . esc_html($question[$niveau] ?? $question['jaune']) . '</p>'
        . '<p>' . esc_html($closing[$niveau] ?? $closing['jaune']) . '</p>',
        $show_cta ? $rdv_url : '',
        $show_cta ? ($cta_texte[$niveau] ?? 'Prendre rendez-vous →') : '',
        $site_name
    );
}

// ════════════════════════════════════════════════════════════════════════
// EMAIL J+8 — Projection cognitive
// Objectif : créer une tension entre "continuer comme avant" et "agir"
// Technique : biais de projection temporelle, contraste émotionnel
// ════════════════════════════════════════════════════════════════════════

function praxicare_sujet_j8( $prenom, $niveau ) {
    $sujets = array(
        'vert'     => $prenom . ', dans 3 mois, où en serez-vous ?',
        'jaune'    => $prenom . ', deux chemins, un choix',
        'orange'   => $prenom . ', imaginez dans 3 mois…',
        'rouge'    => $prenom . ', dans 3 mois : la même chose, ou autre chose ?',
        'critique' => $prenom . ', une image pour vous aider à décider',
    );
    $custom = get_option( 'praxicare_sujet_8j_' . $niveau, '' );
    return $custom ? str_replace( 'Prénom', $prenom, $custom ) : ( $sujets[ $niveau ] ?? $sujets['jaune'] );
}

function praxicare_body_j8( $prenom, $profil, $rdv_url, $site_name ) {
    $niveau = $profil['niveau'];

    $scenario_a = array(
        'vert'     => 'Vous continuez à avancer comme vous le faites. Vous maintenez ce bon équilibre, vous restez attentif(ve) à vos signaux, et dans 3 mois vous êtes toujours dans cette dynamique positive. Peut-être même avec de nouvelles portes qui s\'ouvrent.',
        'jaune'    => 'Vous continuez sans rien changer. Dans 3 mois, la situation est peut-être la même. Vous tenez toujours, un peu plus fatigué(e). Un peu moins enthousiaste. Les mêmes questions reviennent, sans vraiment de réponses.',
        'orange'   => 'Vous continuez sans rien changer. Dans 3 mois, vous portez toujours le même poids. La fatigue s\'est installée un peu plus profondément. Vous avez appris à faire avec. Mais à quel prix ?',
        'rouge'    => 'Vous continuez à tenir. Dans 3 mois, vous êtes peut-être encore là. Mais à quel point ? L\'épuisement ne s\'efface pas avec le temps. Il s\'accumule.',
        'critique' => 'Rien ne change. Dans 3 mois, vous portez toujours le même poids, peut-être un peu plus lourd. Ce n\'est pas inévitable.',
    );
    $custom_intro = get_option( 'praxicare_intro_8j_' . $niveau, '' );
    if ( $custom_intro ) $scenario_a[ $niveau ] = $custom_intro;

    $scenario_b = array(
        'vert'     => 'Vous prenez le temps de consolider ce qui fonctionne. Un coaching, un bilan, un échange stratégique, quelque chose qui vous donne les outils pour aller encore plus loin. Dans 3 mois, vous avancez avec plus de clarté, plus de direction.',
        'jaune'    => 'Vous prenez le temps de faire le point vraiment. Pas seul(e) devant un résultat sur un écran, mais avec quelqu\'un qui comprend ces situations. Dans 3 mois, vous avez des réponses. Et probablement des actions concrètes en cours.',
        'orange'   => 'Vous posez les choses. Vous en parlez à quelqu\'un qui comprend ce type de situation. Dans 3 mois, quelque chose a changé, pas forcément tout, mais assez pour que vous respiriez différemment.',
        'rouge'    => 'Vous faites quelque chose. Un premier pas, même petit. Un entretien, une consultation, une décision. Dans 3 mois, vous n\'êtes plus au même endroit.',
        'critique' => 'Vous faites un pas. Un seul. Dans 3 mois, vous n\'êtes plus au même endroit. Et ce pas peut commencer maintenant, avec un simple échange.',
    );

    $pont = array(
        'vert'     => 'La différence entre ces deux chemins ? Souvent, une conversation de 15 minutes.',
        'jaune'    => 'La différence entre ces deux chemins ? Souvent moins qu\'on ne le croit.',
        'orange'   => 'La différence entre ces deux chemins ? Une décision. Pas forcément une grande décision, juste celle de ne plus attendre.',
        'rouge'    => 'La différence entre ces deux chemins ? Un premier échange. Pas un engagement. Juste une conversation.',
        'critique' => 'La différence ? Un seul pas. Et vous n\'avez pas à le faire seul(e).',
    );

    $cta_texte = array(
        'vert'     => 'Explorer le deuxième chemin →',
        'jaune'    => 'Choisir le deuxième chemin →',
        'orange'   => 'Commencer à changer quelque chose →',
        'rouge'    => 'Faire ce premier pas →',
        'critique' => 'Parler à Alexandre, 15 min gratuitement →',
    );

    return praxicare_wrap_relance(
        $prenom,
        'Imaginez deux versions de votre situation dans 3 mois.',
        '<p style="font-weight:600;color:#1E2A3A;">📍 Chemin A : vous continuez comme aujourd\'hui</p>'
        . '<p style="color:#666;">' . esc_html($scenario_a[$niveau] ?? $scenario_a['jaune']) . '</p>'
        . '<p style="font-weight:600;color:#1E2A3A;margin-top:24px;">🌱 Chemin B : vous décidez de faire quelque chose</p>'
        . '<p style="color:#444;">' . esc_html($scenario_b[$niveau] ?? $scenario_b['jaune']) . '</p>'
        . '<p style="font-style:italic;color:#555;margin-top:20px;">' . esc_html($pont[$niveau] ?? $pont['jaune']) . '</p>',
        $rdv_url,
        $cta_texte[$niveau] ?? 'Réserver un entretien →',
        $site_name
    );
}

// ════════════════════════════════════════════════════════════════════════
// EMAIL J+15 — Passage à l'acte
// Objectif : lever les dernières résistances, déclencher l'action
// Technique : preuve sociale implicite + rareté douce + CTA sans pression
// ════════════════════════════════════════════════════════════════════════

function praxicare_sujet_j15( $prenom, $niveau ) {
    $sujets = array(
        'vert'     => $prenom . ', une dernière chose avant de vous laisser tranquille',
        'jaune'    => $prenom . ', ce que font les gens qui changent vraiment',
        'orange'   => $prenom . ', il y a 15 jours vous avez mis des mots dessus',
        'rouge'    => $prenom . ', 15 jours. Est-ce que quelque chose a changé ?',
        'critique' => $prenom . ', je voulais juste prendre de vos nouvelles',
    );
    $custom = get_option( 'praxicare_sujet_15j_' . $niveau, '' );
    return $custom ? str_replace( 'Prénom', $prenom, $custom ) : ( $sujets[ $niveau ] ?? $sujets['jaune'] );
}

function praxicare_body_j15( $prenom, $profil, $rdv_url, $site_name ) {
    $niveau = $profil['niveau'];
    $titre  = $profil['titre'];

    $intro = array(
        'vert'     => 'Il y a 15 jours, vous avez pris le temps d\'évaluer votre situation professionnelle. Votre résultat était positif, et c\'est une vraie bonne nouvelle. Mais voici quelque chose que j\'observe souvent : les gens qui vont bien au travail et qui décident d\'investir dans leur développement à ce moment-là, plutôt qu\'en période de crise, avancent beaucoup plus vite et beaucoup plus sereinement.',
        'jaune'    => 'Il y a 15 jours, vous avez mis des mots sur quelque chose. Et j\'imagine que depuis, cette réflexion a un peu évolué, peut-être sans que vous y pensiez explicitement. C\'est souvent comme ça que ça fonctionne : une question posée continue à travailler en arrière-plan.',
        'orange'   => 'Il y a 15 jours, vous avez nommé quelque chose d\'important. Depuis, est-ce que quelque chose a bougé, dans votre tête ou dans votre situation ? Ou est-ce que c\'est toujours le même poids, les mêmes questionnements ?',
        'rouge'    => 'Il y a 15 jours, vous avez eu le courage de regarder en face ce que vous viviez. Je voulais prendre de vos nouvelles. Pas pour vous faire une proposition commerciale, juste pour savoir si quelque chose a changé depuis.',
        'critique' => 'Il y a 15 jours, vous avez fait quelque chose de courageux. Je pense encore à vous. Pas pour vous vendre quoi que ce soit, juste pour vous dire que je suis là si vous souhaitez parler.',
    );
    $custom_intro = get_option( 'praxicare_intro_15j_' . $niveau, '' );
    if ( $custom_intro ) $intro[ $niveau ] = $custom_intro;

    $preuve = array(
        'vert'     => 'Les personnes qui tirent le plus de valeur d\'un accompagnement sont souvent celles qui arrivent dans une bonne dynamique, pas en mode "urgence", mais avec l\'envie de construire quelque chose. Si c\'est votre cas, c\'est exactement le bon moment.',
        'jaune'    => 'Ce que j\'observe chez les personnes qui changent vraiment quelque chose à leur situation : elles ne le font pas dans un grand élan d\'enthousiasme. Elles le font dans un moment ordinaire, où elles décident juste de ne plus remettre à demain.',
        'orange'   => 'Les personnes qui sortent de ce type de situation ne trouvent généralement pas la solution seules, pas parce qu\'elles manquent de ressources, mais parce que quand on est dans le brouillard, on a besoin de quelqu\'un à l\'extérieur pour voir ce qu\'on ne voit plus.',
        'rouge'    => 'Ce que j\'ai appris en accompagnant des dizaines de personnes dans des situations similaires : attendre que ça aille mieux "tout seul" fonctionne rarement. Ce qui fonctionne, c\'est un petit pas. Un seul. Même imparfait.',
        'critique' => 'Vous n\'avez rien à prouver, rien à justifier. Si vous souhaitez juste parler quelques minutes, c\'est possible. Sans engagement, sans pression.',
    );

    $cta_context = array(
        'vert'     => 'Si vous êtes curieux(se) d\'explorer comment un coaching ou un bilan pourrait s\'intégrer dans votre trajectoire, je vous propose un échange gratuit de 15 minutes, sans engagement.',
        'jaune'    => 'Si vous souhaitez aller un peu plus loin dans cette réflexion, pas seul(e), mais avec quelqu\'un qui connaît bien ces situations, je suis disponible pour un échange gratuit de 15 minutes.',
        'orange'   => 'Si vous êtes prêt(e) à mettre des mots sur ce que vous vivez avec quelqu\'un qui peut vraiment vous aider à y voir plus clair, je vous propose un premier échange : 15 minutes, gratuit, sans engagement.',
        'rouge'    => 'Si vous souhaitez parler, vraiment, d\'une situation qui dure depuis trop longtemps, je suis disponible. 15 minutes, sans engagement, sans pression.',
        'critique' => 'Si vous souhaitez me parler, même brièvement, je suis là. 15 minutes. Gratuit. Sans aucune pression.',
    );

    $cta_texte = array(
        'vert'     => 'Réserver mon entretien gratuit →',
        'jaune'    => 'Prendre 15 min pour en discuter →',
        'orange'   => 'Parler à Alexandre gratuitement →',
        'rouge'    => 'Prendre rendez-vous avec Alexandre →',
        'critique' => 'Contacter Alexandre →',
    );

    $closing = array(
        'vert'     => 'Quoi qu\'il en soit, je vous souhaite de continuer à bien avancer.',
        'jaune'    => 'Quoi qu\'il en soit, je vous souhaite du bien.',
        'orange'   => 'Prenez soin de vous. C\'est la priorité.',
        'rouge'    => 'Prenez soin de vous. Vraiment.',
        'critique' => 'Prenez soin de vous.',
    );

    return praxicare_wrap_relance(
        $prenom,
        'Je reprends contact une dernière fois.',
        '<p>' . esc_html($intro[$niveau] ?? $intro['jaune']) . '</p>'
        . '<p>' . esc_html($preuve[$niveau] ?? $preuve['jaune']) . '</p>'
        . '<p>' . esc_html($cta_context[$niveau] ?? $cta_context['jaune']) . '</p>'
        . '<p style="font-style:italic;color:#888;font-size:13px;">' . esc_html($closing[$niveau] ?? $closing['jaune']) . '</p>',
        $rdv_url,
        $cta_texte[$niveau] ?? 'Réserver un entretien gratuit →',
        $site_name
    );
}

// ── Template HTML commun ────────────────────────────────────────────────
function praxicare_wrap_relance( $prenom, $accroche, $contenu, $rdv_url, $cta_texte, $site_name ) {
    $cta_block = '';
    if ( $rdv_url && $cta_texte ) {
        $cta_block = '
      <tr><td style="padding:24px 40px;">
        <div style="text-align:center;">
          <a href="' . esc_url($rdv_url) . '" style="display:inline-block;background:#E8521A;color:#FFFFFF;font-weight:700;font-size:15px;padding:14px 32px;border-radius:50px;text-decoration:none;">' . esc_html($cta_texte) . '</a>
          <p style="margin:12px 0 0;font-size:12px;color:#999;">Entretien de 15 min · Gratuit · Sans engagement</p>
        </div>
      </td></tr>';
    }

    return '<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#F5F7FA;font-family:Inter,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F5F7FA;padding:32px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border-radius:12px;overflow:hidden;max-width:600px;width:100%;">

  <tr><td style="background:#1E2D5A;padding:24px 40px;">
    <p style="margin:0;color:#FFFFFF;font-size:18px;font-weight:700;">Alexandre — Praxis Accompagnement</p>
    <p style="margin:4px 0 0;color:#A8B8D8;font-size:12px;">Coach certifié · Bilan de compétences · Accompagnement professionnel</p>
  </td></tr>

  <tr><td style="padding:32px 40px 0;">
    <p style="margin:0;font-size:16px;color:#333;">Bonjour <strong>' . esc_html($prenom) . '</strong>,</p>
    <p style="margin:16px 0 0;font-size:15px;color:#1E2A3A;font-weight:600;line-height:1.5;">' . esc_html($accroche) . '</p>
  </td></tr>

  <tr><td style="padding:16px 40px 0;font-size:15px;color:#444;line-height:1.8;">
    ' . $contenu . '
  </td></tr>

  ' . $cta_block . '

  <tr><td style="background:#F5F7FA;padding:20px 40px;text-align:center;border-top:1px solid #E8EEF4;">
    <p style="margin:0;font-size:12px;color:#999;">Praxis Accompagnement · <a href="https://www.praxis-accompagnement.com" style="color:#E8541A;">praxis-accompagnement.com</a></p>
    <p style="margin:6px 0 0;font-size:11px;color:#bbb;">Vous recevez cet email car vous avez complété le test PraxiCare. Pour ne plus recevoir ces messages, répondez à cet email avec la mention "désinscription".</p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
}
