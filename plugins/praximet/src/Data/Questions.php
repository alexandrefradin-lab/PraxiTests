<?php

namespace Praxis\Plugins\PraxiMet\Data;

class Questions
{
    /** Charge les 84 questions RIASEC depuis le JSON extrait du plugin WP source. */
    public static function all(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = json_decode(file_get_contents(__DIR__ . '/questions.json'), true) ?: [];
        }
        return $cache;
    }

    /**
     * Libellés et descriptions des 6 types — alignés EXACTEMENT sur le plugin WP source
     * (includes/class-riasec-engine.php :: get_description_type()).
     * La clé 'color' fait partie du scaffolding PraxiQuest et n'existe pas côté WP.
     */
    public static function typesLabels(): array
    {
        return [
            'R' => ['label' => 'Réaliste',      'color' => '#dc2626', 'desc' => "Vous êtes concret, manuel et pragmatique. Vous aimez travailler avec vos mains, les outils et la technique. Vous préférez les environnements physiques et concrets aux environnements de bureau."],
            'I' => ['label' => 'Investigateur', 'color' => '#2563eb', 'desc' => "Vous êtes analytique, curieux et rigoureux. Vous aimez comprendre, rechercher et résoudre des problèmes complexes. Vous êtes motivé par la logique et la découverte."],
            'A' => ['label' => 'Artistique',    'color' => '#db2777', 'desc' => "Vous êtes créatif, expressif et indépendant. Vous avez besoin de liberté et d'originalité dans votre travail. Vous vous épanouissez dans des environnements qui valorisent l'imagination et l'originalité."],
            'S' => ['label' => 'Social',        'color' => '#16a34a', 'desc' => "Vous êtes empathique, communicant et orienté vers les autres. Vous aimez aider, enseigner et accompagner. Vous trouvez du sens dans le fait d'avoir un impact direct et positif sur les personnes."],
            'E' => ['label' => 'Entrepreneur',  'color' => '#ea580c', 'desc' => "Vous êtes ambitieux, persuasif et leader. Vous aimez convaincre, décider et relever des défis. Vous vous épanouissez dans des environnements dynamiques, compétitifs et ambitieux."],
            'C' => ['label' => 'Conventionnel', 'color' => '#7c3aed', 'desc' => "Vous êtes organisé, rigoureux et méthodique. Vous aimez les structures claires, les données et les procédures. Vous vous épanouissez dans des environnements stables et bien définis."],
        ];
    }
}
