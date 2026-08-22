<?php

namespace Praxis\Plugins\PraxiBalance\Data;

/**
 * La Balance — banque de tâches à trier.
 *
 * Les cartes des séries chronométrées. Chaque tâche porte trois attributs
 * et sa justification :
 *   u    urgent         — ça réclame maintenant
 *   i    important      — ça pèse sur ce qui compte
 *   m    de mon ressort — c est bien à moi de le faire
 *   why  la raison du classement, montrée quand le candidat se trompe
 * 
 * La combinaison « important mais pas de mon ressort » est le piège du
 * niveau 6 : ce sont ces tâches-là qui remplissent les journées.
 *
 * Fichier généré depuis le prototype : ne pas éditer à la main.
 */
class Tasks
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return [
            [
                'text' => 'Le serveur de production est tombé, les clients ne peuvent plus se connecter.',
                'u' => true, 'i' => true, 'm' => true,
                'why' => 'Chaque minute d\'arrêt coûte des clients. Urgent et important : c\'est la seule catégorie qui mérite qu\'on lâche tout.',
            ],
            [
                'text' => 'Un dossier à rendre ce soir, promis depuis trois semaines.',
                'u' => true, 'i' => true, 'm' => true,
                'why' => 'L\'échéance est là et l\'engagement est pris. Devenu urgent faute d\'avoir été traité quand il ne l\'était pas.',
            ],
            [
                'text' => 'Ton meilleur client menace de partir et demande à te parler aujourd\'hui.',
                'u' => true, 'i' => true, 'm' => true,
                'why' => 'Perdre un client majeur pèse durablement. Le rappeler aujourd\'hui n\'est pas une option.',
            ],
            [
                'text' => 'Une erreur de facturation part chez tous les clients dans une heure.',
                'u' => true, 'i' => true, 'm' => true,
                'why' => 'Après l\'envoi, il faudra corriger auprès de chaque client. Avant, c\'est une ligne à changer.',
            ],
            [
                'text' => 'Un collègue est bloqué et attend ta réponse pour continuer son travail.',
                'u' => true, 'i' => true, 'm' => true,
                'why' => 'Ton silence arrête son travail : le coût est double, et il court pendant que tu hésites.',
            ],
            [
                'text' => 'Le téléphone sonne, numéro inconnu.',
                'u' => true, 'i' => false, 'm' => true,
                'why' => 'Ça sonne, donc ça semble urgent. Mais rien ne dit que ça compte — et le répondeur existe.',
            ],
            [
                'text' => 'Une invitation à un webinaire qui commence dans dix minutes.',
                'u' => true, 'i' => false, 'm' => true,
                'why' => 'L\'horaire crée l\'urgence, pas le contenu. S\'il est vraiment utile, le replay suffira.',
            ],
            [
                'text' => 'Un message dans le fil de discussion générale, sans question pour toi.',
                'u' => true, 'i' => false, 'm' => false,
                'why' => 'Aucune question ne t\'est posée. Lire tout ce qui passe n\'est pas une tâche.',
            ],
            [
                'text' => 'Une demande de sondage interne à remplir avant ce soir.',
                'u' => true, 'i' => false, 'm' => true,
                'why' => 'Une échéance courte sur un enjeu nul. Le cas d\'école de la fausse urgence.',
            ],
            [
                'text' => 'Un commercial rappelle pour la troisième fois cette semaine.',
                'u' => true, 'i' => false, 'm' => true,
                'why' => 'Son insistance mesure son objectif de vente, pas ta priorité.',
            ],
            [
                'text' => 'Une notification t\'annonce que quelqu\'un a réagi à ton message d\'hier.',
                'u' => true, 'i' => false, 'm' => false,
                'why' => 'Conçue pour capter, sans aucune conséquence si tu l\'ignores.',
            ],
            [
                'text' => 'Un mail marqué « urgent » dont l\'échéance réelle est le mois prochain.',
                'u' => true, 'i' => false, 'm' => true,
                'why' => 'L\'étiquette « urgent » est déclarative. Vérifie l\'échéance réelle avant de la subir.',
            ],
            [
                'text' => 'Préparer l\'entretien annuel de la personne que tu encadres.',
                'u' => false, 'i' => true, 'm' => true,
                'why' => 'Rien ne le réclame aujourd\'hui, et pourtant il engage une année de travail avec cette personne.',
            ],
            [
                'text' => 'Rappeler un partenaire avec qui tu n\'as pas échangé depuis six mois.',
                'u' => false, 'i' => true, 'm' => true,
                'why' => 'Une relation ne s\'effondre pas un jour précis. Elle s\'éteint faute d\'entretien.',
            ],
            [
                'text' => 'Écrire la procédure que tout le monde te redemande chaque semaine.',
                'u' => false, 'i' => true, 'm' => true,
                'why' => 'Chaque semaine sans elle te coûte la même explication. L\'écrire une fois arrête l\'hémorragie.',
            ],
            [
                'text' => 'Sauvegarder les dossiers qui ne sont stockés qu\'à un seul endroit.',
                'u' => false, 'i' => true, 'm' => true,
                'why' => 'Aucune urgence — jusqu\'au jour où c\'est irréversible. Le risque est asymétrique.',
            ],
            [
                'text' => 'Bloquer une demi-journée pour préparer le budget du trimestre prochain.',
                'u' => false, 'i' => true, 'm' => true,
                'why' => 'Le budget arrivera de toute façon. Le préparer tôt ou dans la panique, c\'est ton choix.',
            ],
            [
                'text' => 'Prendre le rendez-vous médical repoussé depuis quatre mois.',
                'u' => false, 'i' => true, 'm' => true,
                'why' => 'Repoussé quatre mois parce que rien ne l\'exige. C\'est exactement la définition du piège.',
            ],
            [
                'text' => 'Former un collègue sur la tâche que toi seul sais faire.',
                'u' => false, 'i' => true, 'm' => true,
                'why' => 'Tant que tu es seul à savoir, tu es un point unique de défaillance — et tu ne peux pas partir.',
            ],
            [
                'text' => 'Relire la clause du contrat que personne n\'a vraiment lue.',
                'u' => false, 'i' => true, 'm' => true,
                'why' => 'Un contrat mal lu ne coûte rien pendant des mois, puis coûte tout d\'un coup.',
            ],
            [
                'text' => 'Réorganiser l\'arborescence de tes dossiers personnels.',
                'u' => false, 'i' => false, 'm' => true,
                'why' => 'Sensation d\'ordre, gain réel proche de zéro. Le refuge favori quand on évite autre chose.',
            ],
            [
                'text' => 'Choisir un nouveau fond d\'écran pour ton poste.',
                'u' => false, 'i' => false, 'm' => true,
                'why' => 'Ni conséquence ni échéance. La définition même du remplissage.',
            ],
            [
                'text' => 'Lire un article intéressant sans rapport avec tes sujets.',
                'u' => false, 'i' => false, 'm' => true,
                'why' => 'Intéressant n\'est pas important. Mets-le de côté pour un moment prévu.',
            ],
            [
                'text' => 'Comparer trois outils que tu n\'as pas prévu d\'acheter.',
                'u' => false, 'i' => false, 'm' => true,
                'why' => 'Un achat non décidé ne mérite pas de comparatif. Décide d\'abord si tu achètes.',
            ],
            [
                'text' => 'Trier les photos de la dernière soirée d\'équipe.',
                'u' => false, 'i' => false, 'm' => false,
                'why' => 'Agréable, sans effet. Et ce n\'est même pas à toi de le faire.',
            ],
            [
                'text' => 'Régler à nouveau les paramètres d\'affichage de ton tableau de bord.',
                'u' => false, 'i' => false, 'm' => true,
                'why' => 'Tu l\'as déjà fait. Le refaire est une fuite déguisée en travail.',
            ],
            [
                'text' => 'Corriger la présentation d\'un collègue à sa place, la veille du comité.',
                'u' => false, 'i' => true, 'm' => false,
                'why' => 'Vraiment important — mais tu le prives d\'apprendre, et tu absorbes son retard.',
            ],
            [
                'text' => 'Reprendre le suivi client d\'une personne absente, sans que personne ne l\'ait demandé.',
                'u' => true, 'i' => true, 'm' => false,
                'why' => 'Personne ne l\'a demandé. Signale le trou plutôt que de le combler en silence.',
            ],
            [
                'text' => 'Arbitrer un désaccord entre deux services qui ne dépendent pas de toi.',
                'u' => true, 'i' => true, 'm' => false,
                'why' => 'L\'enjeu est réel, l\'arbitrage ne t\'appartient pas. Renvoie-le à qui décide.',
            ],
            [
                'text' => 'Refaire le calcul qu\'un autre service doit fournir, parce qu\'il tarde.',
                'u' => true, 'i' => true, 'm' => false,
                'why' => 'Le refaire à leur place masque le vrai problème et l\'installe durablement.',
            ],
            [
                'text' => 'Répondre à la place de ton responsable à une question qui lui est adressée.',
                'u' => true, 'i' => true, 'm' => false,
                'why' => 'Important pour l\'organisation, mais ce n\'est ni ton rôle ni ton mandat.',
            ],
            [
                'text' => 'Reprendre entièrement le travail d\'un prestataire au lieu de lui faire corriger.',
                'u' => false, 'i' => true, 'm' => false,
                'why' => 'Tu paies deux fois : son travail et le tien. Fais-le corriger.',
            ],
            [
                'text' => 'Assister à une réunion où ta présence n\'apporte rien mais où tu es invité.',
                'u' => true, 'i' => false, 'm' => false,
                'why' => 'L\'invitation n\'est pas une obligation. Demande le compte rendu.',
            ],
            [
                'text' => 'Relire un document par acquit de conscience, alors qu\'un relecteur est déjà désigné.',
                'u' => false, 'i' => false, 'm' => false,
                'why' => 'Un relecteur est désigné. Doubler son travail n\'ajoute rien et retire du temps.',
            ],
        ];
    }

    public static function count(): int
    {
        return count(self::all());
    }
}
