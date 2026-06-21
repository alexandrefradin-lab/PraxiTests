<?php

namespace Praxis\Plugins\PraxiLink\Data;

/**
 * Items d'auto-évaluation de la communication assertive (PraxiLink).
 *
 * 20 affirmations (4 par dimension), échelle 1-5 :
 *   1 = Pas du tout — 5 = Tout à fait.
 *
 * Chaque item porte sa `dimension` de scoring. Les 5 clés de dimensions
 * correspondent exactement à PraxiLinkScoringEngine::dimensions() et aux
 * libellés attendus côté front (PraxiLinkResult.vue).
 */
class Questions
{
    /**
     * @return array<string, array{label: string, questions: array<int, array{key: string, texte: string}>}>
     */
    public static function sections(): array
    {
        return [
            'ecoute_active' => [
                'label'     => 'Écoute active',
                'questions' => [
                    ['key' => 'EA1', 'texte' => "Quand quelqu'un me parle, je reformule ce que j'ai compris avant de répondre."],
                    ['key' => 'EA2', 'texte' => "Je laisse mon interlocuteur terminer sans l'interrompre, même quand je ne suis pas d'accord."],
                    ['key' => 'EA3', 'texte' => "Je pose des questions ouvertes pour mieux comprendre le point de vue de l'autre."],
                    ['key' => 'EA4', 'texte' => "Je prête attention au langage non verbal (ton, posture, silences) autant qu'aux mots."],
                ],
            ],
            'expression_assertive' => [
                'label'     => 'Expression assertive',
                'questions' => [
                    ['key' => 'EX1', 'texte' => "J'exprime clairement mes besoins et mes limites, sans agressivité ni effacement."],
                    ['key' => 'EX2', 'texte' => "Je sais dire « non » à une demande quand c'est nécessaire, sans me sentir coupable."],
                    ['key' => 'EX3', 'texte' => "Je formule mes désaccords en parlant de mon ressenti (« je ») plutôt qu'en accusant l'autre."],
                    ['key' => 'EX4', 'texte' => "Je demande ce dont j'ai besoin de façon directe, plutôt que d'espérer qu'on le devine."],
                ],
            ],
            'gestion_conflits' => [
                'label'     => 'Gestion des conflits',
                'questions' => [
                    ['key' => 'GC1', 'texte' => "Face à un désaccord, je cherche une solution qui convienne aux deux parties."],
                    ['key' => 'GC2', 'texte' => "Je reste calme et factuel(le) quand une discussion devient tendue."],
                    ['key' => 'GC3', 'texte' => "Je sépare le problème de la personne : je critique la situation, pas mon interlocuteur."],
                    ['key' => 'GC4', 'texte' => "Après une tension, je prends l'initiative de renouer le dialogue."],
                ],
            ],
            'empathie_relationnelle' => [
                'label'     => 'Empathie relationnelle',
                'questions' => [
                    ['key' => 'EM1', 'texte' => "Je perçois facilement l'état émotionnel de la personne en face de moi."],
                    ['key' => 'EM2', 'texte' => "J'adapte ma manière de communiquer selon mon interlocuteur et le contexte."],
                    ['key' => 'EM3', 'texte' => "Je reconnais et valide les émotions de l'autre avant de proposer des solutions."],
                    ['key' => 'EM4', 'texte' => "Je me mets à la place de l'autre pour comprendre ses réactions."],
                ],
            ],
            'feedback_constructif' => [
                'label'     => 'Feedback constructif',
                'questions' => [
                    ['key' => 'FB1', 'texte' => "Quand je donne un retour critique, je m'appuie sur des faits précis plutôt que sur des jugements."],
                    ['key' => 'FB2', 'texte' => "Je sais recevoir une critique sans me braquer et en tirer quelque chose d'utile."],
                    ['key' => 'FB3', 'texte' => "J'exprime aussi des retours positifs, pas seulement ce qui ne va pas."],
                    ['key' => 'FB4', 'texte' => "Je choisis le bon moment et un cadre approprié pour formuler mes feedbacks."],
                ],
            ],
        ];
    }
}
