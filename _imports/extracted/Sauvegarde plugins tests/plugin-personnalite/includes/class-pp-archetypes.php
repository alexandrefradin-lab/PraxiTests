<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Système d'archétypes OCEAN — 16 profils mémorables.
 *
 * Logique : les 3 dimensions avec le score T le plus éloigné de 50
 * (en valeur absolue) définissent l'archétype dominant.
 * Chaque archétype a : nom, tagline, emoji, description courte,
 * couleur principale, couleur secondaire, stat de rareté.
 */
class PP_Archetypes {

    /**
     * Retourne l'archétype correspondant aux scores de dimensions.
     *
     * @param  array $scores_dim  [ 'O' => ['T'=>..,'pct'=>..], ... ]
     * @return array  archetype data
     */
    public static function detecter( $scores_dim ) {
        // Profil binaire : H = score T >= 50 (haut), L = bas
        $O = ( $scores_dim['O']['T'] ?? 50 ) >= 50 ? 'H' : 'L';
        $C = ( $scores_dim['C']['T'] ?? 50 ) >= 50 ? 'H' : 'L';
        $E = ( $scores_dim['E']['T'] ?? 50 ) >= 50 ? 'H' : 'L';
        $A = ( $scores_dim['A']['T'] ?? 50 ) >= 50 ? 'H' : 'L';
        $N = ( $scores_dim['N']['T'] ?? 50 ) >= 50 ? 'H' : 'L';
        $key = "{$O}{$C}{$E}{$A}{$N}";

        $map = self::get_map();
        // Match exact, sinon fallback sur la combinaison la plus proche
        if ( isset( $map[$key] ) ) return $map[$key];

        // Fallback : distance de Hamming minimale
        $best = null; $best_dist = 99;
        foreach ( $map as $k => $arch ) {
            $dist = 0;
            for ( $i = 0; $i < 5; $i++ ) {
                if ( $k[$i] !== $key[$i] ) $dist++;
            }
            if ( $dist < $best_dist ) { $best_dist = $dist; $best = $arch; }
        }
        return $best ?? $map['HLHLH'];
    }

    /**
     * 16 archétypes couvrant les combinaisons OCEAN les plus distinctives.
     * Format clé : O C E A N  (H=haut, L=bas)
     */
    public static function get_map() {
        return array(

            // ─── O haut ─────────────────────────────────────────
            'HHHHL' => array(
                'nom'        => 'Le Catalyseur',
                'tagline'    => 'Vous transformez les idées en mouvements collectifs.',
                'emoji'      => '⚡',
                'description'=> 'Ouvert, organisé, sociable et bienveillant — vous réunissez en vous des qualités rarement combinées. Vous avez la rare capacité de fédérer des équipes autour de visions ambitieuses et de les conduire à la réussite. Votre stabilité émotionnelle vous rend redoutablement efficace même sous pression. Vous savez alterner entre la pensée stratégique et l\'action concrète, et votre présence inspire confiance. Les autres vous suivent parce que vous incarnez ce que vous demandez.',
                'rarete'     => '3',
                'couleur1'   => '#E8541A',
                'couleur2'   => '#1E2A3A',
                'traits'     => array('Vision', 'Leadership', 'Organisation', 'Bienveillance'),
            ),
            'HHLHL' => array(
                'nom'        => 'Le Stratège Visionnaire',
                'tagline'    => 'Vous voyez loin et construisez en détail.',
                'emoji'      => '🔭',
                'description'=> 'Vous combinez une imagination puissante, une organisation rigoureuse et une bienveillance naturelle. Vous êtes à votre mieux quand vous pouvez concevoir de grandes idées ET les exécuter avec méthode. Entouré(e) d\'une équipe que vous inspirez, vous transformez les visions en réalisations concrètes. Votre capacité à structurer le chaos créatif est l\'un de vos atouts les plus précieux. Rares sont ceux qui savent à la fois imaginer et livrer — vous en faites partie.',
                'rarete'     => '5',
                'couleur1'   => '#1E2A3A',
                'couleur2'   => '#E8541A',
                'traits'     => array('Vision', 'Rigueur', 'Empathie', 'Indépendance'),
            ),
            'HLHHL' => array(
                'nom'        => 'L\'Explorateur Passionné',
                'tagline'    => 'Vous trouvez l\'extraordinaire là où les autres voient l\'ordinaire.',
                'emoji'      => '🌍',
                'description'=> 'Curieux, sociable et bienveillant, vous collectionnez les expériences et les connexions humaines avec une avidité joyeuse. Vous avancez par enthousiasme, pas par obligation — ce qui rend votre engagement authentique et durable. Votre énergie est contagieuse : les gens veulent naturellement être dans votre sillage. Vous êtes un pont entre les personnes et les idées, créant de la valeur par votre seule présence. Votre défi est de canaliser cette richesse pour qu\'elle se concrétise.',
                'rarete'     => '7',
                'couleur1'   => '#2E4A6A',
                'couleur2'   => '#8FA8BE',
                'traits'     => array('Curiosité', 'Enthousiasme', 'Connexion', 'Aventure'),
            ),
            'HLLHL' => array(
                'nom'        => 'Le Penseur Indépendant',
                'tagline'    => 'Votre cerveau ne s\'arrête jamais — et c\'est votre super-pouvoir.',
                'emoji'      => '🧠',
                'description'=> 'Intellectuellement insatiable et émotionnellement stable, vous préférez la profondeur à la largeur dans tous les domaines. Vous travaillez mieux seul(e) ou en petit groupe de confiance, loin des agitations inutiles. Vos analyses sont d\'une précision redoutable — vous voyez ce que les autres ne voient pas. Votre indépendance d\'esprit est une force qui vous protège des effets de mode. Là où les autres s\'arrêtent, vous continuez à creuser.',
                'rarete'     => '8',
                'couleur1'   => '#E8541A',
                'couleur2'   => '#2E4A6A',
                'traits'     => array('Analyse', 'Indépendance', 'Profondeur', 'Clarté'),
            ),
            'HHHLL' => array(
                'nom'        => 'L\'Architecte Créatif',
                'tagline'    => 'Vous bâtissez des mondes que les autres habitent.',
                'emoji'      => '🏛️',
                'description'=> 'Vous combinez créativité, rigueur et aisance sociale dans une proposition particulièrement rare sur le marché professionnel. Vos projets sont à la fois originaux dans leur conception et solides dans leur exécution. Vous savez convaincre en amont et livrer en aval — ce double talent fait de vous un profil très recherché. Vous vous épanouissez dans les environnements où l\'on valorise l\'initiative et où les idées peuvent devenir réalité. Votre ambition est un moteur, pas un défaut.',
                'rarete'     => '4',
                'couleur1'   => '#1E2A3A',
                'couleur2'   => '#C4430F',
                'traits'     => array('Créativité', 'Structure', 'Influence', 'Ambition'),
            ),
            'HLHLL' => array(
                'nom'        => 'Le Visionnaire Rebelle',
                'tagline'    => 'Vous imaginez ce qui n\'existe pas encore — puis vous le créez.',
                'emoji'      => '🚀',
                'description'=> 'Ouvert à l\'extrême, sociable et peu contraint par les conventions, vous êtes le moteur de l\'innovation dans vos environnements. Votre force est de voir avant tout le monde — les tendances, les opportunités, les connexions inattendues. Vous stimulez la pensée des autres rien qu\'en parlant. Votre défi est de trouver des partenaires qui ancrent vos idées dans la réalité et les transforment en projets concrets. Seul votre enthousiasme peut parfois vous disperser : choisissez vos batailles.',
                'rarete'     => '6',
                'couleur1'   => '#E8541A',
                'couleur2'   => '#1E2A3A',
                'traits'     => array('Innovation', 'Audace', 'Vision', 'Spontanéité'),
            ),

            // ─── O bas ──────────────────────────────────────────
            'LHHLH' => array(
                'nom'        => 'Le Gardien Fiable',
                'tagline'    => 'Là où vous êtes, les choses fonctionnent.',
                'emoji'      => '🛡️',
                'description'=> 'Organisé, sociable et bienveillant, vous êtes le pilier silencieux sur lequel les équipes s\'appuient dans les moments difficiles. Vous ne cherchez pas les feux des projecteurs — vous préférez que les choses fonctionnent. Votre fiabilité est un atout stratégique de premier ordre, souvent sous-estimé mais toujours remarqué. Là où vous êtes, l\'environnement devient plus stable, plus humain, plus efficace. Votre loyauté inspire en retour une loyauté profonde chez ceux qui vous entourent.',
                'rarete'     => '9',
                'couleur1'   => '#8FA8BE',
                'couleur2'   => '#2E4A6A',
                'traits'     => array('Fiabilité', 'Soin', 'Structure', 'Loyauté'),
            ),
            'LHLLH' => array(
                'nom'        => 'L\'Expert Silencieux',
                'tagline'    => 'Votre travail parle avant vous — et il parle fort.',
                'emoji'      => '⚙️',
                'description'=> 'Rigoureux, discret et profondément fiable, vous produisez un travail d\'une qualité rare avec une constance qui force le respect. Vous n\'avez pas besoin de briller en public — vos résultats se chargent de votre réputation sur le long terme. Votre précision et votre sens du détail vous permettent d\'atteindre une excellence que peu peuvent revendiquer. Vous fonctionnez mieux dans des environnements stables où la profondeur est valorisée. Votre discrétion est une force, pas une limite.',
                'rarete'     => '11',
                'couleur1'   => '#1E2A3A',
                'couleur2'   => '#E8541A',
                'traits'     => array('Précision', 'Discrétion', 'Excellence', 'Constance'),
            ),
            'LHHLL' => array(
                'nom'        => 'Le Manager de Terrain',
                'tagline'    => 'Vous faites tourner la machine avec une précision d\'horloger.',
                'emoji'      => '🎯',
                'description'=> 'Organisé, sociable et stable, vous êtes naturellement fait(e) pour coordonner, livrer et fédérer autour d\'un objectif commun. Pas de grands discours — des résultats. Votre sens du concret vous permet de traduire les visions en plans d\'action immédiatement opérationnels. Votre fiabilité en font un leader opérationnel de premier plan, capable de tenir les délais sans sacrifier les relations humaines. Vous êtes la colonne vertébrale des projets ambitieux.',
                'rarete'     => '10',
                'couleur1'   => '#2E4A6A',
                'couleur2'   => '#E8541A',
                'traits'     => array('Exécution', 'Coordination', 'Fiabilité', 'Pragmatisme'),
            ),
            'LLLHH' => array(
                'nom'        => 'Le Empathique Sensible',
                'tagline'    => 'Vous ressentez ce que les autres ne voient même pas.',
                'emoji'      => '💙',
                'description'=> 'Profondément tourné(e) vers les autres, vous êtes une présence apaisante et un soutien inestimable pour ceux qui vous entourent. Vous percevez les émotions avec une finesse que peu possèdent, ce qui fait de vous un(e) confident(e) naturel(le). Votre richesse émotionnelle est un don précieux — apprenez à la protéger pour qu\'elle reste une force et non une source d\'épuisement. Vous flourissez dans les environnements qui valorisent l\'écoute et la profondeur relationnelle. Prendre soin de vous est la condition de prendre soin des autres.',
                'rarete'     => '12',
                'couleur1'   => '#8FA8BE',
                'couleur2'   => '#C4430F',
                'traits'     => array('Empathie', 'Écoute', 'Soin', 'Profondeur'),
            ),
            'LLLLH' => array(
                'nom'        => 'L\'Âme Sensible',
                'tagline'    => 'Votre intensité est votre plus grande richesse.',
                'emoji'      => '🌊',
                'description'=> 'Vous vivez les choses avec une intensité rare qui colore chacune de vos expériences d\'une profondeur particulière. Peu structuré(e) et peu assertif(ve), vous trouvez votre force non dans le bruit du monde, mais dans votre vie intérieure profonde et féconde. Votre imagination produit des œuvres ou des idées qui touchent les gens là où ça compte. Le défi est de construire autour de vous les structures et les alliés qui vous permettent de déployer ce potentiel immense. Votre sensibilité est votre matière première.',
                'rarete'     => '14',
                'couleur1'   => '#C4430F',
                'couleur2'   => '#1E2A3A',
                'traits'     => array('Profondeur', 'Sensibilité', 'Authenticité', 'Résilience'),
            ),
            'LLHLL' => array(
                'nom'        => 'Le Diplomate Naturel',
                'tagline'    => 'Vous créez le lien là où les autres créent la friction.',
                'emoji'      => '🤝',
                'description'=> 'Sociable, bienveillant et stable, vous désamorcez les conflits avec une aisance remarquable qui tient presque du don naturel. Votre intelligence relationnelle est votre atout maître dans tous les environnements à forte dimension humaine. Vous savez trouver le bon mot, le bon moment, la bonne distance avec chacun. Votre stabilité émotionnelle vous permet d\'être présent(e) pour les autres sans vous laisser déstabiliser. Dans un monde où les tensions sont fréquentes, vous êtes une ressource rare.',
                'rarete'     => '8',
                'couleur1'   => '#8FA8BE',
                'couleur2'   => '#C4430F',
                'traits'     => array('Médiation', 'Écoute', 'Harmonie', 'Stabilité'),
            ),
            'LHLHH' => array(
                'nom'        => 'Le Protecteur Passionné',
                'tagline'    => 'Vous vous battez pour ceux qui n\'ont pas la voix pour le faire.',
                'emoji'      => '🦁',
                'description'=> 'Assertif(ve), bienveillant(e) et émotionnellement intense, vous portez les causes qui vous tiennent à cœur avec une énergie et une conviction redoutables. Vous êtes un(e) défenseur(se) naturel(le) — non par calcul, mais par profonde conviction que les autres méritent d\'être soutenus. Votre capacité à allier force de caractère et chaleur humaine est rare et précieuse. Vous mobilisez les gens non par l\'autorité mais par la sincérité de votre engagement. Votre intensité est votre marque de fabrique.',
                'rarete'     => '7',
                'couleur1'   => '#C4430F',
                'couleur2'   => '#8FA8BE',
                'traits'     => array('Courage', 'Engagement', 'Chaleur', 'Intensité'),
            ),
            'HLLLH' => array(
                'nom'        => 'Le Créatif Sensible',
                'tagline'    => 'Vous créez des œuvres que les autres ressentent dans leurs tripes.',
                'emoji'      => '🎨',
                'description'=> 'Ouvert, peu contraint et émotionnellement intense, vous êtes un(e) créateur(trice) dans l\'âme au sens le plus profond du terme. Votre imagination fertile et votre sensibilité aiguisée produisent des œuvres, des idées ou des visions qui touchent les gens là où les mots ordinaires n\'arrivent pas. Vous fonctionnez par inspiration plutôt que par planification — ce qui donne à votre travail une authenticité que rien ne peut imiter. Votre défi est de trouver les conditions qui vous permettent de créer sans vous épuiser. Votre intensité est votre plus grande richesse.',
                'rarete'     => '9',
                'couleur1'   => '#1E2A3A',
                'couleur2'   => '#C4430F',
                'traits'     => array('Créativité', 'Sensibilité', 'Originalité', 'Authenticité'),
            ),
            'HLHLH' => array(
                'nom'        => 'Le Pionnier Empathique',
                'tagline'    => 'Vous ouvrez des chemins que les autres n\'osaient pas emprunter.',
                'emoji'      => '🧭',
                'description'=> 'Ouvert, bienveillant et émotionnellement intense, vous innovez avec le cœur autant qu\'avec l\'intellect. Vous imaginez un monde meilleur — plus juste, plus humain, plus créatif — et vous vous investissez pour le créer avec une constance remarquable. Votre empathie profonde inspire ceux qui vous entourent et crée autour de vous un mouvement naturel. Vous êtes à votre mieux quand vous pouvez combiner votre vision avec l\'action concrète. Votre sensibilité n\'est pas une faiblesse — c\'est le moteur de votre impact.',
                'rarete'     => '6',
                'couleur1'   => '#2E4A6A',
                'couleur2'   => '#1E2A3A',
                'traits'     => array('Innovation', 'Empathie', 'Vision', 'Engagement'),
            ),
            'LHHHH' => array(
                'nom'        => 'Le Leader Humain',
                'tagline'    => 'Vous dirigez avec la tête et guidez avec le cœur.',
                'emoji'      => '👑',
                'description'=> 'Organisé, sociable, bienveillant et émotionnellement présent, vous incarnez un leadership à visage humain dans ce qu\'il a de plus authentique. Votre force rare est d\'exiger des résultats ambitieux sans jamais perdre de vue les personnes qui les produisent. Vous créez des environnements où les gens donnent le meilleur d\'eux-mêmes parce qu\'ils se sentent vus et respectés. Votre intelligence émotionnelle complète votre rigueur pour former un profil de leader complet. Dans un monde qui a besoin de sens autant que de performance, vous êtes une réponse.',
                'rarete'     => '4',
                'couleur1'   => '#C4430F',
                'couleur2'   => '#8FA8BE',
                'traits'     => array('Leadership', 'Bienveillance', 'Structure', 'Présence'),
            ),
        );
    }

    /**
     * Génère le HTML de la carte de résultat partageable.
     */
    public static function render_carte( $prenom, $archetype, $scores_dim ) {
        $nom      = esc_html( $archetype['nom'] );
        $tagline  = esc_html( $archetype['tagline'] );
        $emoji    = $archetype['emoji'];
        $rarete   = intval( $archetype['rarete'] );
        $c1       = self::sanitize_hex_color( $archetype['couleur1'] ?? '#E8541A' );
        $c2       = self::sanitize_hex_color( $archetype['couleur2'] ?? '#1E2A3A' );
        $traits   = array_map( 'esc_html', $archetype['traits'] );
        $prenom_h = esc_html( $prenom );

        // Barres OCEAN — tout en styles inline
        $dims = array(
            'O' => array( 'label' => 'Ouverture',    'icon' => '🔭' ),
            'C' => array( 'label' => 'Conscience',   'icon' => '🗂️' ),
            'E' => array( 'label' => 'Extraversion', 'icon' => '💬' ),
            'A' => array( 'label' => 'Agréabilité',  'icon' => '🤝' ),
            'N' => array( 'label' => 'Stabilité',    'icon' => '🌊' ),
        );

        $bars_html = '';
        foreach ( $dims as $key => $info ) {
            $pct = intval( $scores_dim[$key]['pct'] ?? 50 );
            $bars_html .= '<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">'
                . '<span style="font-size:13px;color:rgba(255,255,255,.85);width:120px;flex-shrink:0;">' . $info['icon'] . ' ' . esc_html($info['label']) . '</span>'
                . '<div style="flex:1;background:rgba(255,255,255,.2);border-radius:999px;height:6px;">'
                . '<div style="background:#fff;height:6px;border-radius:999px;width:' . $pct . '%;"></div>'
                . '</div>'
                . '<span style="font-size:12px;color:#fff;font-weight:700;width:34px;text-align:right;">' . $pct . '%</span>'
                . '</div>';
        }

        $traits_html = '';
        foreach ( $traits as $t ) {
            $traits_html .= '<span style="background:rgba(255,255,255,.18);color:#fff;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid rgba(255,255,255,.3);">' . $t . '</span>';
        }

        $html  = '<div id="pp-carte-result" style="border-radius:20px;overflow:hidden;margin-bottom:0;">';
        $html .= '<div style="background:linear-gradient(135deg,' . $c1 . ' 0%,' . $c2 . ' 100%);padding:32px 28px;text-align:center;">';

        // Emoji
        $html .= '<div style="font-size:56px;line-height:1;margin-bottom:10px;">' . $emoji . '</div>';

        // Label
        $html .= '<p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.7);font-weight:600;">VOTRE ARCHÉTYPE</p>';

        // Prénom
        $html .= '<p style="margin:0 0 2px;font-size:13px;color:rgba(255,255,255,.75);">Le profil de ' . $prenom_h . '</p>';

        // Nom archétype
        $html .= '<h3 style="margin:0 0 6px;font-size:26px;font-weight:900;color:#fff;letter-spacing:-.02em;">' . $nom . '</h3>';

        // Tagline
        $html .= '<p style="margin:0 0 16px;font-size:14px;font-style:italic;color:rgba(255,255,255,.85);">' . $tagline . '</p>';

        // Traits
        if ( $traits_html ) {
            $html .= '<div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;margin-bottom:20px;">' . $traits_html . '</div>';
        }

        // Barres
        $html .= '<div style="text-align:left;margin-bottom:16px;">' . $bars_html . '</div>';

        // Rareté
        $html .= '<div style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:6px 16px;display:inline-block;font-size:12px;font-weight:700;color:#fff;">';
        $html .= '✨ Profil présent chez seulement <strong>' . $rarete . '%</strong> des personnes';
        $html .= '</div>';

        $html .= '</div>'; // fin carte

        // Boutons
        $html .= '</div>'; // fin pp-carte-result
        $html .= '<div style="display:flex;gap:10px;flex-wrap:wrap;padding:14px 0 4px;justify-content:center;">';
        $html .= '<button onclick="ppCopierLienCarte()" style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:999px;background:' . $c1 . ';color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:600;font-family:inherit;">';
        $html .= '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
        $html .= 'Copier le lien pour le partager</button>';
        $html .= '<button onclick="ppDownloadCarte()" style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:999px;background:transparent;color:' . $c1 . ';border:2px solid ' . $c1 . ';cursor:pointer;font-size:13px;font-weight:600;font-family:inherit;">⬇ Télécharger la carte</button>';
        $html .= '</div>'; // fin boutons

        return $html;
    }


    /**
     * Valide et assainit une couleur hexadécimale pour injection CSS sécurisée.
     * Retourne le fallback si la valeur ne correspond pas au format #RRGGBB ou #RGB.
     */
    public static function sanitize_color( $color, $fallback = '#E8541A' ) {
        return self::sanitize_hex_color( $color, $fallback );
    }

    public static function sanitize_hex_color( $color, $fallback = '#E8541A' ) {
        if ( preg_match( '/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $color ) ) {
            return $color;
        }
        return $fallback;
    }
}
