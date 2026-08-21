<?php

namespace Praxis\Plugins\PraxiBalance\Data;

/**
 * La Balance — banque de tâches à trier.
 *
 * Les cartes des séries chronométrées. Chaque tâche porte trois attributs :
 *   u  urgent         — ça réclame maintenant
 *   i  important      — ça pèse sur ce qui compte
 *   m  de mon ressort — c est bien à moi de le faire
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
            ['text' => 'Le serveur de production est tombé, les clients ne peuvent plus se connecter.', 'u' => true, 'i' => true, 'm' => true],
            ['text' => 'Un dossier à rendre ce soir, promis depuis trois semaines.', 'u' => true, 'i' => true, 'm' => true],
            ['text' => 'Ton meilleur client menace de partir et demande à te parler aujourd\'hui.', 'u' => true, 'i' => true, 'm' => true],
            ['text' => 'Une erreur de facturation part chez tous les clients dans une heure.', 'u' => true, 'i' => true, 'm' => true],
            ['text' => 'Un collègue est bloqué et attend ta réponse pour continuer son travail.', 'u' => true, 'i' => true, 'm' => true],
            ['text' => 'Le téléphone sonne, numéro inconnu.', 'u' => true, 'i' => false, 'm' => true],
            ['text' => 'Une invitation à un webinaire qui commence dans dix minutes.', 'u' => true, 'i' => false, 'm' => true],
            ['text' => 'Un message dans le fil de discussion générale, sans question pour toi.', 'u' => true, 'i' => false, 'm' => false],
            ['text' => 'Une demande de sondage interne à remplir avant ce soir.', 'u' => true, 'i' => false, 'm' => true],
            ['text' => 'Un commercial rappelle pour la troisième fois cette semaine.', 'u' => true, 'i' => false, 'm' => true],
            ['text' => 'Une notification t\'annonce que quelqu\'un a réagi à ton message d\'hier.', 'u' => true, 'i' => false, 'm' => false],
            ['text' => 'Un mail marqué « urgent » dont l\'échéance réelle est le mois prochain.', 'u' => true, 'i' => false, 'm' => true],
            ['text' => 'Préparer l\'entretien annuel de la personne que tu encadres.', 'u' => false, 'i' => true, 'm' => true],
            ['text' => 'Rappeler un partenaire avec qui tu n\'as pas échangé depuis six mois.', 'u' => false, 'i' => true, 'm' => true],
            ['text' => 'Écrire la procédure que tout le monde te redemande chaque semaine.', 'u' => false, 'i' => true, 'm' => true],
            ['text' => 'Sauvegarder les dossiers qui ne sont stockés qu\'à un seul endroit.', 'u' => false, 'i' => true, 'm' => true],
            ['text' => 'Bloquer une demi-journée pour préparer le budget du trimestre prochain.', 'u' => false, 'i' => true, 'm' => true],
            ['text' => 'Prendre le rendez-vous médical repoussé depuis quatre mois.', 'u' => false, 'i' => true, 'm' => true],
            ['text' => 'Former un collègue sur la tâche que toi seul sais faire.', 'u' => false, 'i' => true, 'm' => true],
            ['text' => 'Relire la clause du contrat que personne n\'a vraiment lue.', 'u' => false, 'i' => true, 'm' => true],
            ['text' => 'Réorganiser l\'arborescence de tes dossiers personnels.', 'u' => false, 'i' => false, 'm' => true],
            ['text' => 'Choisir un nouveau fond d\'écran pour ton poste.', 'u' => false, 'i' => false, 'm' => true],
            ['text' => 'Lire un article intéressant sans rapport avec tes sujets.', 'u' => false, 'i' => false, 'm' => true],
            ['text' => 'Comparer trois outils que tu n\'as pas prévu d\'acheter.', 'u' => false, 'i' => false, 'm' => true],
            ['text' => 'Trier les photos de la dernière soirée d\'équipe.', 'u' => false, 'i' => false, 'm' => false],
            ['text' => 'Régler à nouveau les paramètres d\'affichage de ton tableau de bord.', 'u' => false, 'i' => false, 'm' => true],
            ['text' => 'Corriger la présentation d\'un collègue à sa place, la veille du comité.', 'u' => false, 'i' => true, 'm' => false],
            ['text' => 'Reprendre le suivi client d\'une personne absente, sans que personne ne l\'ait demandé.', 'u' => true, 'i' => true, 'm' => false],
            ['text' => 'Arbitrer un désaccord entre deux services qui ne dépendent pas de toi.', 'u' => true, 'i' => true, 'm' => false],
            ['text' => 'Refaire le calcul qu\'un autre service doit fournir, parce qu\'il tarde.', 'u' => true, 'i' => true, 'm' => false],
            ['text' => 'Répondre à la place de ton responsable à une question qui lui est adressée.', 'u' => true, 'i' => true, 'm' => false],
            ['text' => 'Reprendre entièrement le travail d\'un prestataire au lieu de lui faire corriger.', 'u' => false, 'i' => true, 'm' => false],
            ['text' => 'Assister à une réunion où ta présence n\'apporte rien mais où tu es invité.', 'u' => true, 'i' => false, 'm' => false],
            ['text' => 'Relire un document par acquit de conscience, alors qu\'un relecteur est déjà désigné.', 'u' => false, 'i' => false, 'm' => false],
        ];
    }

    public static function count(): int
    {
        return count(self::all());
    }
}
