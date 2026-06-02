<?php
/**
 * PraxiMet – Moteur de calcul RIASEC
 * Calcule les scores et le code dominant à 3 lettres
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PraxiMet_Riasec_Engine {

    /**
     * Calcule les scores RIASEC à partir des réponses du formulaire.
     *
     * @param array $reponses  Ex: ['R1' => 1, 'R2' => 0, 'I1' => 1, ...]
     * @return array           Scores et code dominant
     */
    public static function calculer_scores( array $reponses ) {

        $scores = [
            'R' => 0,
            'I' => 0,
            'A' => 0,
            'S' => 0,
            'E' => 0,
            'C' => 0,
        ];

        // Récupère les questions pour connaître le type de chaque ID
        require_once PRAXIMET_PATH . 'data/questions-riasec.php';
        $questions = praximet_get_questions();

        // Indexe les questions par ID pour accès rapide
        $index = [];
        foreach ( $questions as $q ) {
            $index[ $q['id'] ] = $q['type'];
        }

        // Additionne les scores par type
        // Note : on normalise en majuscules car sanitize_key() force les minuscules
        $index_lower = [];
        foreach ( $index as $key => $type ) {
            $index_lower[ strtolower( $key ) ] = $type;
        }

        foreach ( $reponses as $id => $valeur ) {
            // Nettoie l'ID et normalise en minuscules pour la comparaison
            $id_clean = preg_replace( '/[^a-zA-Z0-9]/', '', (string) $id );
            $id_lower = strtolower( $id_clean );
            $valeur   = (int) $valeur; // 0 ou 1 uniquement

            if ( isset( $index_lower[ $id_lower ] ) && in_array( $valeur, [0, 1], true ) ) {
                $scores[ $index_lower[ $id_lower ] ] += $valeur;
            }
        }

        // Calcule le code dominant (3 lettres avec scores les plus élevés)
        $code = self::calculer_code( $scores );

        return [
            'scores' => $scores,
            'code'   => $code,
        ];
    }

    /**
     * Détermine le code RIASEC à 3 lettres dominant.
     * En cas d'égalité, respecte l'ordre RIASEC standard.
     *
     * @param array $scores   Ex: ['R'=>3,'I'=>5,'A'=>2,'S'=>6,'E'=>4,'C'=>1]
     * @return string         Ex: "SAE"
     */
    private static function calculer_code( array $scores ) {

        // Ordre de priorité en cas d'égalité (standard Holland)
        $ordre = ['R', 'I', 'A', 'S', 'E', 'C'];

        // Trie en gardant l'ordre stable (arsort ne garantit pas la stabilité)
        $tries = [];
        foreach ( $ordre as $lettre ) {
            $tries[ $lettre ] = $scores[ $lettre ];
        }

        // Tri décroissant stable
        arsort( $tries );

        // Prend les 3 premières lettres
        $top3 = array_slice( array_keys( $tries ), 0, 3 );

        return implode( '', $top3 );
    }

    /**
     * Calcule les scores par sous-domaine.
     *
     * @param array $reponses  Ex: ['R1' => 1, 'R2' => 0, ...]
     * @return array           Scores par sous-domaine
     */
    public static function calculer_scores_sous_domaines( array $reponses ) {
        require_once PRAXIMET_PATH . 'data/questions-riasec.php';
        $questions = praximet_get_questions();

        $descriptions = [
            'Activités manuelles et techniques' => 'Goût pour le travail concret avec les mains, les outils et les techniques.',
            'Activités extérieures'              => 'Attrait pour les environnements physiques, le terrain et le contact avec la nature.',
            'Curiosité intellectuelle et apprentissage' => 'Plaisir à apprendre, questionner et comprendre le monde en profondeur.',
            'Sciences et technologie'            => 'Intérêt pour les démarches scientifiques, l\'expérimentation et les nouvelles technologies.',
            'Sens esthétique et expression'      => 'Sensibilité à la beauté, l\'art et besoin d\'exprimer ses émotions.',
            'Créativité et conception'           => 'Goût pour créer, imaginer et concevoir des idées ou objets originaux.',
            'Dévouement aux autres'              => 'Plaisir à aider, soutenir et accompagner les personnes dans leurs difficultés.',
            'Relations personnelles'             => 'Goût pour les échanges humains, les liens durables et le travail en équipe.',
            'Leadership'                         => 'Capacité à diriger, décider et entraîner les autres vers un objectif commun.',
            'Entrepreneur'                       => 'Attrait pour les défis, la prise de risque et la création de projets ambitieux.',
            'Méthodique'                         => 'Goût pour l\'organisation, la planification et les procédures structurées.',
            'Données et nombres'                 => 'Aisance avec les chiffres, les données et les analyses quantitatives.',
        ];

        $scores_sd = [];
        foreach ( $questions as $q ) {
            $sd  = $q['sous_domaine'] ?? '';
            $val = isset( $reponses[ $q['id'] ] ) ? (int) $reponses[ $q['id'] ] : 0;
            if ( ! isset( $scores_sd[ $sd ] ) ) {
                $scores_sd[ $sd ] = [
                    'score'       => 0,
                    'total'       => 0,
                    'type'        => $q['type'],
                    'description' => $descriptions[ $sd ] ?? '',
                ];
            }
            $scores_sd[ $sd ]['score'] += $val;
            $scores_sd[ $sd ]['total'] += 1;
        }
        return $scores_sd;
    }

    /**
     * Retourne la description textuelle d'un type RIASEC.
     *
     * @param string $lettre  Ex: 'S'
     * @return array          ['label' => '...', 'description' => '...']
     */
    public static function get_description_type( string $lettre ) {

        $descriptions = [
            'R' => [
                'label'       => 'Réaliste',
                'description' => 'Vous êtes concret, manuel et pragmatique. Vous aimez travailler avec vos mains, les outils et la technique. Vous préférez les environnements physiques et concrets aux environnements de bureau.',
            ],
            'I' => [
                'label'       => 'Investigateur',
                'description' => 'Vous êtes analytique, curieux et rigoureux. Vous aimez comprendre, rechercher et résoudre des problèmes complexes. Vous êtes motivé par la logique et la découverte.',
            ],
            'A' => [
                'label'       => 'Artistique',
                'description' => 'Vous êtes créatif, expressif et indépendant. Vous avez besoin de liberté et d\'originalité dans votre travail. Vous vous épanouissez dans des environnements qui valorisent l\'imagination et l\'originalité.',
            ],
            'S' => [
                'label'       => 'Social',
                'description' => 'Vous êtes empathique, communicant et orienté vers les autres. Vous aimez aider, enseigner et accompagner. Vous trouvez du sens dans le fait d\'avoir un impact direct et positif sur les personnes.',
            ],
            'E' => [
                'label'       => 'Entrepreneur',
                'description' => 'Vous êtes ambitieux, persuasif et leader. Vous aimez convaincre, décider et relever des défis. Vous vous épanouissez dans des environnements dynamiques, compétitifs et ambitieux.',
            ],
            'C' => [
                'label'       => 'Conventionnel',
                'description' => 'Vous êtes organisé, rigoureux et méthodique. Vous aimez les structures claires, les données et les procédures. Vous vous épanouissez dans des environnements stables et bien définis.',
            ],
        ];

        return $descriptions[ $lettre ] ?? [
            'label'       => 'Inconnu',
            'description' => '',
        ];
    }

    /**
     * Construit le résultat complet à afficher au candidat.
     *
     * @param string $code  Ex: "SAE"
     * @return array
     */
    public static function get_resultat_complet( string $code ) {

        $lettres = str_split( $code );
        $profil  = [];

        foreach ( $lettres as $lettre ) {
            $profil[] = self::get_description_type( $lettre );
        }

        return [
            'code'   => $code,
            'profil' => $profil,
        ];
    }
}
