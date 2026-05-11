<?php

namespace Praxis\Plugins\PraxiCare\Data;

class Questions
{
    public static function sections(): array
    {
        return [
            'demandes' => [
                'label'       => 'Charge de travail',
                'description' => "Karasek — Demandes psychologiques",
                'scale'       => 'likert4',
                'questions'   => [
                    ['key' => 'D1', 'texte' => 'Mon travail demande de travailler très vite.'],
                    ['key' => 'D2', 'texte' => 'Mon travail demande de travailler intensément.'],
                    ['key' => 'D3', 'texte' => "On me demande d'effectuer une quantité de travail excessive."],
                    ['key' => 'D4', 'texte' => 'Je dispose du temps nécessaire pour exécuter correctement mon travail.', 'inverse' => true],
                    ['key' => 'D5', 'texte' => 'Je reçois des ordres contradictoires de la part des autres.'],
                    ['key' => 'D6', 'texte' => 'Mon travail nécessite de longues périodes de concentration intense.'],
                    ['key' => 'D7', 'texte' => "Mon travail est souvent interrompu avant que je l'aie terminé."],
                    ['key' => 'D8', 'texte' => 'Je subis une pression constante dans mon travail.'],
                    ['key' => 'D9', 'texte' => "Attendre le travail de collègues ou d'autres départements ralentit souvent mon propre travail."],
                ],
            ],
            'latitude' => [
                'label'       => 'Autonomie',
                'description' => "Karasek — Latitude décisionnelle",
                'scale'       => 'likert4',
                'questions'   => [
                    ['key' => 'L1', 'texte' => 'Mon travail me permet de prendre des décisions de façon autonome.'],
                    ['key' => 'L2', 'texte' => "J'ai la possibilité d'influencer le déroulement de mon travail."],
                    ['key' => 'L3', 'texte' => 'Mon travail me permet de développer mes compétences professionnelles.'],
                    ['key' => 'L4', 'texte' => "Mon travail implique d'apprendre des choses nouvelles."],
                    ['key' => 'L5', 'texte' => "Mon travail me permet d'utiliser mes compétences et savoir-faire."],
                    ['key' => 'L6', 'texte' => "Dans mon travail, j'ai la possibilité de faire des choses variées."],
                    ['key' => 'L7', 'texte' => "J'ai mon mot à dire sur ce qui se passe dans mon service."],
                    ['key' => 'L8', 'texte' => "J'ai la possibilité de développer mes habiletés personnelles."],
                    ['key' => 'L9', 'texte' => "Au travail, j'ai la possibilité de faire preuve de créativité."],
                ],
            ],
            'soutien' => [
                'label'       => 'Soutien social',
                'description' => "Karasek — Soutien hiérarchique et collègues",
                'scale'       => 'likert4',
                'questions'   => [
                    ['key' => 'S1', 'texte' => "Mon supérieur se soucie du bien-être des personnes qu'il dirige.", 'optional' => 'has_superior'],
                    ['key' => 'S2', 'texte' => 'Mon supérieur prête attention à ce que je dis.', 'optional' => 'has_superior'],
                    ['key' => 'S3', 'texte' => "Mon supérieur m'aide à mener mes tâches à bien.", 'optional' => 'has_superior'],
                    ['key' => 'S4', 'texte' => 'Mon supérieur réussit facilement à faire travailler les gens ensemble.', 'optional' => 'has_superior'],
                    ['key' => 'S5', 'texte' => 'Les collègues avec qui je travaille sont des gens professionnellement compétents.'],
                    ['key' => 'S6', 'texte' => "Les collègues avec qui je travaille me manifestent de l'intérêt."],
                    ['key' => 'S7', 'texte' => 'Les collègues avec qui je travaille sont amicaux.'],
                    ['key' => 'S8', 'texte' => "Les collègues avec qui je travaille m'aident à mener mes tâches à bien."],
                ],
            ],
            'ee' => [
                'label'       => 'Épuisement émotionnel',
                'description' => "MBI — Emotional Exhaustion",
                'scale'       => 'mbi4',
                'questions'   => [
                    ['key' => 'EE1', 'texte' => 'Je me sens émotionnellement épuisé(e) par mon travail.'],
                    ['key' => 'EE2', 'texte' => 'Je me sens à bout en fin de journée de travail.'],
                    ['key' => 'EE3', 'texte' => "Je me sens fatigué(e) quand je me lève le matin et que j'ai à faire face à une nouvelle journée de travail."],
                    ['key' => 'EE4', 'texte' => 'Travailler avec des gens toute la journée me demande un effort important.'],
                    ['key' => 'EE5', 'texte' => 'Je me sens usé(e) par mon travail.'],
                    ['key' => 'EE6', 'texte' => 'Je me sens frustré(e) par mon travail.'],
                    ['key' => 'EE7', 'texte' => 'Je pense que je travaille trop dur dans mon travail.'],
                    ['key' => 'EE8', 'texte' => 'Travailler directement avec des gens me stresse trop.'],
                    ['key' => 'EE9', 'texte' => 'Je me sens au bout du rouleau à cause de mon travail.'],
                ],
            ],
            'dp' => [
                'label'       => 'Dépersonnalisation',
                'description' => "MBI — Depersonalization",
                'scale'       => 'mbi4',
                'questions'   => [
                    ['key' => 'DP1', 'texte' => "Il m'arrive de traiter certains collègues ou clients de manière froide et impersonnelle."],
                    ['key' => 'DP2', 'texte' => "Je suis devenu(e) indifférent(e) aux gens depuis que j'ai ce travail."],
                    ['key' => 'DP3', 'texte' => "Je remarque que je deviens plus insensible aux gens depuis que j'ai ce travail."],
                    ['key' => 'DP4', 'texte' => "Je crains que ce travail ne m'endurcisse émotionnellement."],
                    ['key' => 'DP5', 'texte' => 'Je ne me soucie plus vraiment de ce qui arrive aux personnes avec qui je travaille.'],
                ],
            ],
            'ap' => [
                'label'       => 'Accomplissement personnel',
                'description' => "MBI — Personal Accomplishment (inversé)",
                'scale'       => 'mbi4',
                'inverse'     => true,
                'questions'   => [
                    ['key' => 'AP1', 'texte' => "Je parviens facilement à créer une atmosphère détendue avec les personnes avec qui je travaille."],
                    ['key' => 'AP2', 'texte' => "Le contact avec les gens dans mon travail me redonne de l'énergie."],
                    ['key' => 'AP3', 'texte' => "J'ai accompli des choses utiles et qui comptent dans mon travail."],
                    ['key' => 'AP4', 'texte' => 'Dans mon travail, je traite les problèmes avec calme.'],
                    ['key' => 'AP5', 'texte' => "Je sens que j'influence positivement la vie des autres à travers mon travail."],
                    ['key' => 'AP6', 'texte' => 'Je crée facilement une atmosphère détendue dans mon travail.'],
                    ['key' => 'AP7', 'texte' => "Je me sens plein(e) d'énergie dans mon travail."],
                    ['key' => 'AP8', 'texte' => "Dans mon travail, j'ai le sentiment de m'en sortir bien."],
                ],
            ],
        ];
    }

    public static function profiles(): array
    {
        return [
            'detendu'    => ['label' => 'Détendu',    'color' => '#16a34a', 'desc' => 'Demandes faibles + latitude élevée. Situation favorable.'],
            'actif'      => ['label' => 'Actif',      'color' => '#2563eb', 'desc' => 'Demandes élevées mais latitude élevée. Stimulant.'],
            'passif'     => ['label' => 'Passif',     'color' => '#a16207', 'desc' => 'Demandes faibles + latitude faible. Risque de désengagement.'],
            'tendu'      => ['label' => 'Job strain', 'color' => '#ea580c', 'desc' => 'Demandes élevées + latitude faible. Risque psychosocial.'],
            'iso_strain' => ['label' => 'Iso strain', 'color' => '#dc2626', 'desc' => 'Job strain + faible soutien social. Très à risque.'],
        ];
    }
}
