<?php

namespace Praxis\Plugins\PraxiCog\Support;

use App\Models\TestAttempt;

/**
 * Construit le prompt de synthèse IA spécifique à PraxiCog.
 *
 * Branché via le filtre `ai.synthesis.messages` (voir PluginServiceProvider) :
 * remplace le persona générique « orientation / personnalité » du cœur par un
 * persona « aptitude au raisonnement » avec des garde-fous stricts (jamais de
 * QI, jamais de trait figé, langage de performance sur CE test).
 *
 * N'impacte que les tentatives PraxiCog — le filtre laisse les autres tests
 * intacts.
 */
class AptitudeSynthesisPrompt
{
    /**
     * @return array<int,array{role:string,content:string}>
     */
    public static function messages(TestAttempt $attempt): array
    {
        $attempt->loadMissing('result');
        $scoring = $attempt->result?->scoring ?? [];

        $norm     = is_array($scoring['norm_scores'] ?? null) ? $scoring['norm_scores'] : [];
        $metaDims = is_array($scoring['meta'] ?? null) ? $scoring['meta'] : [];

        // Niveau qualitatif par domaine (jamais de chiffres côté candidat).
        $order    = ['logique', 'verbal', 'numerique', 'spatial'];
        $domaines = [];
        foreach ($order as $key) {
            $label  = $metaDims[$key]['label'] ?? ucfirst($key);
            $niveau = $norm[$key]['label'] ?? 'Niveau indicatif non déterminé';
            $domaines[$label] = $niveau;
        }

        $context = [
            'test'                  => 'Aptitude au raisonnement (logique, verbal, numérique, spatial)',
            'niveau_global'         => $norm['global']['label'] ?? null,
            'niveaux_par_domaine'   => $domaines,
            'vitesse_de_traitement' => $scoring['speed']['label'] ?? null,
        ];

        $system = <<<TXT
Tu es un psychologue du travail qui explique, avec pédagogie et prudence, les résultats d'un test d'APTITUDE AU RAISONNEMENT (domaines : logique, verbal, numérique, spatial).

CADRE IMPÉRATIF (non négociable) :
- Ce test est INDICATIF. Ce n'est PAS une mesure de QI, PAS un diagnostic, PAS un trait figé de la personne.
- N'emploie JAMAIS les mots « QI », « quotient intellectuel », « intelligence », « surdoué », « déficient », « haut potentiel ».
- Formule TOUT en performance SUR CE TEST, à ce moment précis : « sur cette épreuve, votre raisonnement X s'est montré… », JAMAIS « vous êtes quelqu'un de… » ni « votre intelligence… ».
- Rappelle, sans dramatiser, que le résultat dépend du stress, de la fatigue, de la vitesse et de l'habitude de ce type d'exercices : il peut évoluer avec l'entraînement.
- Présente les domaines les moins réussis comme des AXES DE PROGRÈS concrets, jamais comme des déficits ou un manque de valeur.

STYLE : vouvoiement, chaleureux, clair, non anxiogène, sans jargon, phrases courtes. 3 paragraphes.
INTERDITS : aucun chiffre ni percentile (utilise les niveaux qualitatifs fournis) ; aucun conseil métier ni plan d'action (cela relève du Grimoire) ; n'invente aucun score.
TXT;

        $userMsg = "Voici les niveaux indicatifs obtenus par la personne sur ce test d'aptitude :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nRédige une synthèse de 200 à 350 mots, en 3 paragraphes :\n"
            . "1) les domaines de raisonnement où la personne s'est le mieux débrouillée sur ce test, "
            . "et ce que cela veut dire concrètement (types de problèmes qu'elle traite avec aisance) ;\n"
            . "2) les domaines qui offrent le plus de marge de progression, présentés comme des axes "
            . "de travail encourageants (et rappel que ça se muscle) ;\n"
            . "3) une lecture d'ensemble du profil de raisonnement, prudente et non figée, "
            . "en rappelant que c'est une photographie d'un moment, pas un verdict sur la personne.\n"
            . "Respecte scrupuleusement le cadre : aucun mot interdit (QI, intelligence…), "
            . "aucun chiffre, langage de performance sur ce test et non de trait de caractère.";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $userMsg],
        ];
    }
}
