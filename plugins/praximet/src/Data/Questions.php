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

    public static function typesLabels(): array
    {
        return [
            'R' => ['label' => 'Réaliste',     'color' => '#dc2626', 'desc' => "Aime travailler avec des objets, machines, outils, plantes ou animaux. Pratique, concret, manuel."],
            'I' => ['label' => 'Investigateur', 'color' => '#2563eb', 'desc' => "Aime observer, apprendre, analyser, évaluer ou résoudre des problèmes. Curieux, méthodique, intellectuel."],
            'A' => ['label' => 'Artistique',   'color' => '#db2777', 'desc' => "Aime créer, imaginer, exprimer. Sensible, créatif, indépendant, original."],
            'S' => ['label' => 'Social',       'color' => '#16a34a', 'desc' => "Aime travailler avec les autres : informer, former, soigner, soutenir. Empathique, coopératif."],
            'E' => ['label' => 'Entreprenant', 'color' => '#ea580c', 'desc' => "Aime persuader, diriger, prendre des risques. Ambitieux, énergique, sociable, leader."],
            'C' => ['label' => 'Conventionnel', 'color' => '#7c3aed', 'desc' => "Aime travailler avec des données, suivre des procédures. Rigoureux, organisé, méthodique."],
        ];
    }
}
