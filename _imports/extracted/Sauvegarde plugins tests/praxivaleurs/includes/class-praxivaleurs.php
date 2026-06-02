<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiValeurs {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'praxivaleurs', array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public static function activate() {
        global $wpdb;
        $table = $wpdb->prefix . 'praxivaleurs_sessions';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(200) NOT NULL,
            reponses LONGTEXT NOT NULL,
            scores LONGTEXT NOT NULL,
            top5 LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function enqueue_assets() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'praxivaleurs' ) ) {
            wp_enqueue_style( 'praxivaleurs-style', PRAXIVALEURS_URL . 'assets/css/style.css', array(), PRAXIVALEURS_VERSION );
            wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
            wp_enqueue_script( 'praxivaleurs-main', PRAXIVALEURS_URL . 'assets/js/main.js', array('chart-js'), PRAXIVALEURS_VERSION, true );
            wp_localize_script( 'praxivaleurs-main', 'praxiValeursData', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'praxivaleurs_nonce' ),
                'questions' => self::get_questions(),
                'dimensions' => self::get_dimensions(),
                'mapping'   => self::get_mapping(),
            ));
        }
    }

    public function render_shortcode() {
        ob_start();
        include PRAXIVALEURS_PATH . 'templates/page-intro.php';
        return ob_get_clean();
    }

    public static function get_questions() {
        return array(
            array('id'=>1,  'texte'=>"Indépendance dans mes choix",                    'dim'=>'autonomie'),
            array('id'=>2,  'texte'=>"Entraide et soutien aux autres",                 'dim'=>'bienveillance'),
            array('id'=>3,  'texte'=>"Stabilité et sécurité dans ma vie",              'dim'=>'securite'),
            array('id'=>4,  'texte'=>"Ambition et désir de réussir",                   'dim'=>'reussite'),
            array('id'=>5,  'texte'=>"Égalité et justice pour tous",                   'dim'=>'universalisme'),
            array('id'=>6,  'texte'=>"Plaisir et épanouissement personnel",            'dim'=>'hedonisme'),
            array('id'=>7,  'texte'=>"Leadership et capacité à influencer",            'dim'=>'pouvoir'),
            array('id'=>8,  'texte'=>"Respect des cadres et règles établis",           'dim'=>'conformite'),
            array('id'=>9,  'texte'=>"Aventure et goût du risque",                     'dim'=>'stimulation'),
            array('id'=>10, 'texte'=>"Fidélité aux traditions familiales",             'dim'=>'tradition'),
            array('id'=>11, 'texte'=>"Créativité et originalité",                      'dim'=>'autonomie'),
            array('id'=>12, 'texte'=>"Honnêteté et intégrité",                        'dim'=>'bienveillance'),
            array('id'=>13, 'texte'=>"Fiabilité et prévisibilité",                     'dim'=>'securite'),
            array('id'=>14, 'texte'=>"Performance et excellence",                      'dim'=>'reussite'),
            array('id'=>15, 'texte'=>"Être reconnu et estimé dans son domaine",        'dim'=>'pouvoir'),
            array('id'=>16, 'texte'=>"Tolérance et ouverture aux différences",         'dim'=>'universalisme'),
            array('id'=>17, 'texte'=>"Bien-être et confort de vie",                    'dim'=>'hedonisme'),
            array('id'=>18, 'texte'=>"Discipline et maîtrise de soi",                  'dim'=>'conformite'),
            array('id'=>19, 'texte'=>"Curiosité et soif d'apprendre",                  'dim'=>'autonomie'),
            array('id'=>20, 'texte'=>"Innovation et changement",                        'dim'=>'stimulation'),
            array('id'=>21, 'texte'=>"Protection de l'environnement",                  'dim'=>'universalisme'),
            array('id'=>22, 'texte'=>"Loyauté envers mes proches",                     'dim'=>'bienveillance'),
            array('id'=>23, 'texte'=>"Prudence et anticipation des risques",           'dim'=>'securite'),
            array('id'=>24, 'texte'=>"Efficacité et résultats concrets",               'dim'=>'reussite'),
            array('id'=>25, 'texte'=>"Influence et impact sur mon entourage",          'dim'=>'pouvoir'),
            array('id'=>26, 'texte'=>"Joie de vivre au quotidien",                     'dim'=>'hedonisme'),
            array('id'=>27, 'texte'=>"Discrétion et modestie dans mon rôle",           'dim'=>'tradition'),
            array('id'=>28, 'texte'=>"Variété et nouveauté dans mes activités",        'dim'=>'stimulation'),
            array('id'=>29, 'texte'=>"Générosité et don de soi",                       'dim'=>'bienveillance'),
            array('id'=>30, 'texte'=>"Liberté de penser et d'agir",                    'dim'=>'autonomie'),
            array('id'=>31, 'texte'=>"Modération et équilibre de vie",                 'dim'=>'tradition'),
            array('id'=>32, 'texte'=>"Dépasser mes limites et progresser",              'dim'=>'reussite'),
            array('id'=>33, 'texte'=>"Ordre et organisation",                          'dim'=>'conformite'),
            array('id'=>34, 'texte'=>"Prise de risque calculée",                       'dim'=>'stimulation'),
            array('id'=>35, 'texte'=>"Sécurité financière et matérielle",              'dim'=>'securite'),
            array('id'=>36, 'texte'=>"Prestige et statut social",                      'dim'=>'pouvoir'),
            array('id'=>37, 'texte'=>"Épanouissement et réalisation de soi",           'dim'=>'hedonisme'),
            array('id'=>38, 'texte'=>"Respect des engagements",                        'dim'=>'conformite'),
            array('id'=>39, 'texte'=>"Attachement à mes racines",                      'dim'=>'tradition'),
            array('id'=>40, 'texte'=>"Sens du collectif et du bien commun",            'dim'=>'universalisme'),
        );
    }

    public static function get_dimensions() {
        return array(
            'autonomie'     => array('label'=>'Autonomie',     'icon'=>'🧭', 'couleur'=>'#3B4F8C', 'court'=>'Indépendance, liberté de choisir, créativité',          'definition'=>'Besoin de penser et d\'agir de façon indépendante. Valorise la créativité, l\'exploration intellectuelle et la liberté de fixer ses propres buts.'),
            'stimulation'   => array('label'=>'Stimulation',   'icon'=>'⚡', 'couleur'=>'#E8A838', 'court'=>'Nouveauté, défis, goût du changement',                  'definition'=>'Besoin d\'excitation, de nouveauté et de challenge. Apprécie la variété, le risque maîtrisé et les environnements qui évoluent rapidement.'),
            'hedonisme'     => array('label'=>'Hédonisme',     'icon'=>'🌻', 'couleur'=>'#6B8F71', 'court'=>'Plaisir, bien-être, qualité de vie',                    'definition'=>'Recherche du plaisir et de la gratification sensuelle. Accorde de l\'importance au confort, à la joie de vivre et à l\'épanouissement au quotidien.'),
            'reussite'      => array('label'=>'Réussite',      'icon'=>'🏆', 'couleur'=>'#C0392B', 'court'=>'Performance, excellence, ambition',                     'definition'=>'Désir de démontrer sa compétence et d\'obtenir des résultats reconnus. Se dépasser, atteindre des objectifs ambitieux et être valorisé pour ses performances.'),
            'pouvoir'       => array('label'=>'Pouvoir',       'icon'=>'👑', 'couleur'=>'#8E44AD', 'court'=>'Influence, leadership, reconnaissance',                  'definition'=>'Aspiration à exercer une influence sur les autres et à détenir un statut social élevé. Valorise le prestige, le leadership et la capacité à orienter les décisions.'),
            'securite'      => array('label'=>'Sécurité',      'icon'=>'🛡️', 'couleur'=>'#2980B9', 'court'=>'Stabilité, ordre, protection',                          'definition'=>'Besoin de sécurité, d\'harmonie et de stabilité dans sa vie et ses relations. Valorise la prévisibilité, la protection et les environnements organisés et fiables.'),
            'conformite'    => array('label'=>'Conformité',    'icon'=>'⚖️', 'couleur'=>'#16A085', 'court'=>'Respect des règles, discipline, fiabilité',             'definition'=>'Volonté de respecter les normes, les règles et les attentes sociales. Valorise la discipline, la maîtrise de soi et le respect des engagements pris envers les autres.'),
            'tradition'     => array('label'=>'Tradition',     'icon'=>'🌿', 'couleur'=>'#795548', 'court'=>'Fidélité, continuité, attachement aux racines',         'definition'=>'Respect et attachement aux coutumes, à la culture et aux traditions familiales ou religieuses. Valorise la continuité, la modération et la fidélité à ses racines.'),
            'bienveillance' => array('label'=>'Bienveillance', 'icon'=>'💙', 'couleur'=>'#1ABC9C', 'court'=>'Soin des autres, honnêteté, entraide',                  'definition'=>'Préserver et renforcer le bien-être des personnes proches. Valorise l\'honnêteté, la loyauté, l\'entraide et la générosité dans les relations du quotidien.'),
            'universalisme' => array('label'=>'Universalisme', 'icon'=>'🌍', 'couleur'=>'#27AE60', 'court'=>'Justice, égalité, utilité sociale',                    'definition'=>'Compréhension, tolérance et protection du bien-être de tous et de la nature. Valorise la justice sociale, l\'égalité, la paix et la protection de l\'environnement.'),
        );
    }

    public static function get_mapping() {
        // dim => descriptions pour la restitution
        return array(
            'autonomie'     => array(
                'description'  => "Vous avez un fort besoin d'indépendance et de liberté dans vos choix. La créativité et l'exploration intellectuelle sont au cœur de votre épanouissement.",
                'implication'  => "Privilégiez les environnements de travail qui vous laissent de l'autonomie : freelance, management de projet, postes à responsabilités, entrepreneuriat.",
            ),
            'stimulation'   => array(
                'description'  => "Vous êtes porté·e par le changement, les défis et la nouveauté. La routine est votre principal ennemi professionnel.",
                'implication'  => "Orientez-vous vers des métiers en évolution rapide, des missions variées, des startups ou des contextes de transformation organisationnelle.",
            ),
            'hedonisme'     => array(
                'description'  => "Le plaisir, le bien-être et la qualité de vie comptent beaucoup pour vous. Vous cherchez à concilier épanouissement personnel et vie professionnelle.",
                'implication'  => "Veillez à l'équilibre vie pro/perso dans vos choix. Les environnements bienveillants, la flexibilité et le sens au quotidien sont des critères essentiels.",
            ),
            'reussite'      => array(
                'description'  => "La performance, l'excellence et la reconnaissance de vos compétences sont des moteurs puissants. Vous aimez vous dépasser et atteindre vos objectifs.",
                'implication'  => "Les environnements méritocratiques, les objectifs mesurables et les évolutions de carrière claires vous correspondent. Cadres, direction, conseil.",
            ),
            'pouvoir'       => array(
                'description'  => "Vous aspirez à avoir de l'influence, à être reconnu·e et à occuper une position de référence dans votre domaine.",
                'implication'  => "Les rôles de leadership, d'expertise reconnue ou de représentation publique seront sources de motivation. Veillez à distinguer pouvoir formel et influence.",
            ),
            'securite'      => array(
                'description'  => "La stabilité, la prévisibilité et la protection sont des besoins fondamentaux pour vous. Vous travaillez mieux dans un cadre clair et sécurisant.",
                'implication'  => "Privilégiez les structures stables (grandes entreprises, fonction publique) ou construisez un socle financier solide avant tout changement.",
            ),
            'conformite'    => array(
                'description'  => "Vous accordez de l'importance au respect des règles, à la discipline et à la cohérence. Vous êtes fiable et respectueux·se des engagements.",
                'implication'  => "Les environnements structurés, les métiers réglementés ou les organisations avec une forte culture interne vous conviennent.",
            ),
            'tradition'     => array(
                'description'  => "Vos racines, votre histoire et vos engagements à long terme sont des ancres importantes. Vous valorisez la continuité et la fidélité.",
                'implication'  => "Les projets à long terme, les secteurs liés à la culture, à l'artisanat ou au patrimoine peuvent résonner avec vos valeurs profondes.",
            ),
            'bienveillance' => array(
                'description'  => "Prendre soin des autres, créer du lien et agir avec honnêteté sont des priorités absolues pour vous. Vous êtes un pilier de confiance dans vos équipes.",
                'implication'  => "Les métiers d'accompagnement, de soin, d'éducation ou de coordination d'équipe vous permettront d'exprimer pleinement cette valeur.",
            ),
            'universalisme' => array(
                'description'  => "Vous portez une vision large du monde : justice, égalité, environnement, diversité. L'utilité sociale de votre travail est un critère majeur.",
                'implication'  => "Les organisations à impact (ESS, ONG, RSE, secteur public) ou les missions à forte portée sociale vous apporteront du sens et de la motivation.",
            ),
        );
    }
}
