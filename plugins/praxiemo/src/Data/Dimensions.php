<?php

namespace Praxis\Plugins\PraxiEmo\Data;

class Dimensions
{
    public static function families(): array
    {
        return [
            1 => ['label' => 'Conscience de soi',         'icon' => 'brain',     'color' => '#4F46E5'],
            2 => ['label' => 'Régulation émotionnelle',   'icon' => 'lightning', 'color' => '#10B981'],
            3 => ['label' => 'Relations & Communication', 'icon' => 'users',     'color' => '#F59E0B'],
            4 => ['label' => 'Leadership émotionnel',     'icon' => 'target',    'color' => '#EF4444'],
        ];
    }

    public static function dimensions(): array
    {
        return [
            1  => ['label' => 'Connaissance de soi',       'famille' => 1, 'questions' => [0,1,2,3,4]],
            4  => ['label' => 'Confiance en soi',          'famille' => 1, 'questions' => [5,6,7,8,9]],
            9  => ['label' => 'Expression des sentiments', 'famille' => 1, 'questions' => [10,11,12,13,14]],
            16 => ['label' => 'Contrôle des impulsions',   'famille' => 1, 'questions' => [15,16,17,18,19]],
            2  => ['label' => 'Gestion du stress',         'famille' => 2, 'questions' => [20,21,22,23,24]],
            3  => ['label' => 'Gestion de la colère',      'famille' => 2, 'questions' => [25,26,27,28,29]],
            5  => ['label' => 'Auto-motivation',           'famille' => 2, 'questions' => [30,31,32,33,34]],
            6  => ['label' => 'Optimisme',                 'famille' => 2, 'questions' => [35,36,37,38,39]],
            7  => ['label' => 'Résilience',                'famille' => 2, 'questions' => [40,41,42,43,44]],
            8  => ['label' => 'Flexibilité',               'famille' => 2, 'questions' => [45,46,47,48,49]],
            10 => ['label' => 'Assertivité',               'famille' => 3, 'questions' => [50,51,52,53,54]],
            11 => ['label' => 'Empathie',                  'famille' => 3, 'questions' => [55,56,57,58,59]],
            12 => ['label' => 'Tact',                      'famille' => 3, 'questions' => [60,61,62,63,64]],
            13 => ['label' => 'Gestion de la diversité',   'famille' => 3, 'questions' => [65,66,67,68,69]],
            14 => ['label' => 'Motiver les autres',        'famille' => 4, 'questions' => [70,71,72,73,74]],
            15 => ['label' => 'Gestion des conflits',      'famille' => 4, 'questions' => [75,76,77,78,79]],
        ];
    }

    /** Indices 80-85 : items Marlowe-Crowne (désirabilité sociale). Ne comptent pas dans le score. */
    public static function questions(): array
    {
        return [
            // Famille 1 — Conscience de soi
            ['idx' => 0,  'dim' => 1,  'f' => 1, 'text' => "J'identifie mes émotions au moment même où je les ressens."],
            ['idx' => 1,  'dim' => 1,  'f' => 1, 'text' => "Je comprends ce qui déclenche mes réactions émotionnelles."],
            ['idx' => 2,  'dim' => 1,  'f' => 1, 'text' => "Je perçois l'impact de mon humeur sur mes comportements et mes décisions."],
            ['idx' => 3,  'dim' => 1,  'f' => 1, 'text' => "J'ai une vision claire de mes forces et de mes limites personnelles."],
            ['idx' => 4,  'dim' => 1,  'f' => 1, 'text' => "Je reconnais les signaux physiques qui accompagnent mes émotions (tension, accélération cardiaque...)."],
            ['idx' => 5,  'dim' => 4,  'f' => 1, 'text' => "Je crois en ma capacité à réussir les défis que j'affronte."],
            ['idx' => 6,  'dim' => 4,  'f' => 1, 'text' => "J'exprime mes opinions même lorsqu'elles diffèrent de celles des autres."],
            ['idx' => 7,  'dim' => 4,  'f' => 1, 'text' => "Je prends mes décisions sans avoir constamment besoin de validation extérieure."],
            ['idx' => 8,  'dim' => 4,  'f' => 1, 'text' => "Je fais confiance à mon jugement dans les situations difficiles."],
            ['idx' => 9,  'dim' => 4,  'f' => 1, 'text' => "Je relève de nouveaux défis sans que l'incertitude me paralyse."],
            ['idx' => 10, 'dim' => 9,  'f' => 1, 'text' => "Je prends l'initiative de partager ce que je ressens quand c'est utile à la relation."],
            ['idx' => 11, 'dim' => 9,  'f' => 1, 'text' => "Je trouve les mots justes pour décrire ce que je ressens intérieurement."],
            ['idx' => 12, 'dim' => 9,  'f' => 1, 'text' => "J'exprime naturellement mes émotions positives (joie, gratitude, affection)."],
            ['idx' => 13, 'dim' => 9,  'f' => 1, 'text' => "Je n'accumule pas mes émotions négatives en les gardant pour moi."],
            ['idx' => 14, 'dim' => 9,  'f' => 1, 'text' => "Je partage mes ressentis sans les dramatiser ni les minimiser."],
            ['idx' => 15, 'dim' => 16, 'f' => 1, 'text' => "Je prends le temps de réfléchir avant d'agir, même sous l'effet d'une émotion forte."],
            ['idx' => 16, 'dim' => 16, 'f' => 1, 'text' => "Je diffère une satisfaction immédiate au profit d'un bénéfice futur plus important."],
            ['idx' => 17, 'dim' => 16, 'f' => 1, 'text' => "Je ne prends pas de décisions importantes quand je suis sous l'emprise d'une émotion intense."],
            ['idx' => 18, 'dim' => 16, 'f' => 1, 'text' => "Avant de réagir à chaud, je marque une pause pour choisir ma réponse."],
            ['idx' => 19, 'dim' => 16, 'f' => 1, 'text' => "Je termine ce que j'ai commencé sans me laisser distraire par des envies du moment."],

            // Famille 2 — Régulation émotionnelle
            ['idx' => 20, 'dim' => 2, 'f' => 2, 'text' => "Je reste calme et efficace même sous forte pression."],
            ['idx' => 21, 'dim' => 2, 'f' => 2, 'text' => "Je dispose de stratégies concrètes pour réduire mon niveau de stress."],
            ['idx' => 22, 'dim' => 2, 'f' => 2, 'text' => "J'arrive à prendre du recul même quand la pression est forte."],
            ['idx' => 23, 'dim' => 2, 'f' => 2, 'text' => "Je ne laisse pas le stress envahir mes pensées sur une longue durée."],
            ['idx' => 24, 'dim' => 2, 'f' => 2, 'text' => "Je gère les situations d'urgence sans me laisser déborder émotionnellement."],
            ['idx' => 25, 'dim' => 3, 'f' => 2, 'text' => "Quand je me sens en colère, je choisis consciemment comment réagir plutôt que d'exploser."],
            ['idx' => 26, 'dim' => 3, 'f' => 2, 'text' => "Je prends le temps de me calmer avant de répondre dans une situation conflictuelle."],
            ['idx' => 27, 'dim' => 3, 'f' => 2, 'text' => "Quand je suis en colère, j'exprime ce que je ressens sans agressivité."],
            ['idx' => 28, 'dim' => 3, 'f' => 2, 'text' => "Je ne laisse pas la colère guider mes décisions."],
            ['idx' => 29, 'dim' => 3, 'f' => 2, 'text' => "Après un accès de colère, je reviens rapidement à un état serein."],
            ['idx' => 30, 'dim' => 5, 'f' => 2, 'text' => "Je me fixe des objectifs ambitieux et je m'y tiens sans supervision."],
            ['idx' => 31, 'dim' => 5, 'f' => 2, 'text' => "Je me remotive rapidement après un échec ou une déception."],
            ['idx' => 32, 'dim' => 5, 'f' => 2, 'text' => "Je trouve du sens dans ce que je fais, même dans les tâches routinières."],
            ['idx' => 33, 'dim' => 5, 'f' => 2, 'text' => "Je m'investis pleinement sans avoir besoin de récompense externe."],
            ['idx' => 34, 'dim' => 5, 'f' => 2, 'text' => "Je maintiens mon énergie et mon enthousiasme sur la durée."],
            ['idx' => 35, 'dim' => 6, 'f' => 2, 'text' => "Je m'attends généralement à ce que les choses se passent bien."],
            ['idx' => 36, 'dim' => 6, 'f' => 2, 'text' => "Face à un obstacle, je cherche d'abord les solutions plutôt que les problèmes."],
            ['idx' => 37, 'dim' => 6, 'f' => 2, 'text' => "Je considère les difficultés comme des opportunités d'apprentissage."],
            ['idx' => 38, 'dim' => 6, 'f' => 2, 'text' => "Je maintiens une vision positive de l'avenir même dans les moments difficiles."],
            ['idx' => 39, 'dim' => 6, 'f' => 2, 'text' => "Je tends à voir ce qui est possible plutôt que ce qui fait obstacle."],
            ['idx' => 40, 'dim' => 7, 'f' => 2, 'text' => "Je me relève rapidement après un échec ou une épreuve difficile."],
            ['idx' => 41, 'dim' => 7, 'f' => 2, 'text' => "Les situations adverses me renforcent plutôt qu'elles ne me découragent."],
            ['idx' => 42, 'dim' => 7, 'f' => 2, 'text' => "Je garde mon équilibre émotionnel lors de changements importants dans ma vie."],
            ['idx' => 43, 'dim' => 7, 'f' => 2, 'text' => "J'accepte ce que je ne peux pas contrôler et m'adapte en conséquence."],
            ['idx' => 44, 'dim' => 7, 'f' => 2, 'text' => "Je suis capable de repartir sur de nouvelles bases après une perte significative."],
            ['idx' => 45, 'dim' => 8, 'f' => 2, 'text' => "Je m'adapte facilement aux nouvelles règles, méthodes ou environnements."],
            ['idx' => 46, 'dim' => 8, 'f' => 2, 'text' => "Je change de point de vue quand on me présente des éléments convaincants."],
            ['idx' => 47, 'dim' => 8, 'f' => 2, 'text' => "Je gère bien l'incertitude et l'ambiguïté sans anxiété excessive."],
            ['idx' => 48, 'dim' => 8, 'f' => 2, 'text' => "Je ne m'accroche pas à mes habitudes quand le changement est nécessaire."],
            ['idx' => 49, 'dim' => 8, 'f' => 2, 'text' => "J'accueille les nouvelles idées avec ouverture, même si elles remettent en question ma façon de faire."],

            // Famille 3 — Relations & Communication
            ['idx' => 50, 'dim' => 10, 'f' => 3, 'text' => "Je sais dire non sans me sentir coupable."],
            ['idx' => 51, 'dim' => 10, 'f' => 3, 'text' => "J'exprime mes besoins et mes attentes clairement et directement."],
            ['idx' => 52, 'dim' => 10, 'f' => 3, 'text' => "Je défends mes opinions et mes droits sans agressivité."],
            ['idx' => 53, 'dim' => 10, 'f' => 3, 'text' => "Je donne un feedback direct sur ce qui ne me convient pas, sans détour ni agressivité."],
            ['idx' => 54, 'dim' => 10, 'f' => 3, 'text' => "Je ne laisse pas les autres empiéter sur mes limites personnelles ou professionnelles."],
            ['idx' => 55, 'dim' => 11, 'f' => 3, 'text' => "Je perçois facilement les émotions de quelqu'un, même s'il ne les exprime pas verbalement."],
            ['idx' => 56, 'dim' => 11, 'f' => 3, 'text' => "Je m'intéresse sincèrement à ce que vivent les autres."],
            ['idx' => 57, 'dim' => 11, 'f' => 3, 'text' => "Je me mets à la place de personnes dont les valeurs sont très différentes des miennes."],
            ['idx' => 58, 'dim' => 11, 'f' => 3, 'text' => "Je remarque quand quelqu'un ne va pas, même sans qu'il me le signale."],
            ['idx' => 59, 'dim' => 11, 'f' => 3, 'text' => "Je prends en compte les émotions des autres dans mes décisions."],
            ['idx' => 60, 'dim' => 12, 'f' => 3, 'text' => "Je choisis le bon moment et la bonne manière pour aborder un sujet sensible."],
            ['idx' => 61, 'dim' => 12, 'f' => 3, 'text' => "J'adapte mon langage et mon ton à mon interlocuteur et au contexte."],
            ['idx' => 62, 'dim' => 12, 'f' => 3, 'text' => "Je suis capable de dire des vérités difficiles sans blesser inutilement."],
            ['idx' => 63, 'dim' => 12, 'f' => 3, 'text' => "Je tiens compte de l'état émotionnel de l'autre avant de lui faire un retour."],
            ['idx' => 64, 'dim' => 12, 'f' => 3, 'text' => "Je crée un climat de confiance avant d'aborder des sujets délicats."],
            ['idx' => 65, 'dim' => 13, 'f' => 3, 'text' => "Je suis à l'aise pour collaborer avec des personnes d'horizons très différents du mien."],
            ['idx' => 66, 'dim' => 13, 'f' => 3, 'text' => "Je considère la diversité des points de vue comme une richesse dans un groupe."],
            ['idx' => 67, 'dim' => 13, 'f' => 3, 'text' => "Je suis curieux(se) de comprendre des références culturelles ou sociales différentes des miennes."],
            ['idx' => 68, 'dim' => 13, 'f' => 3, 'text' => "Je traite chaque personne avec le même respect, quelle que soit son appartenance."],
            ['idx' => 69, 'dim' => 13, 'f' => 3, 'text' => "Je fais des efforts pour comprendre les valeurs de ceux qui m'entourent."],

            // Famille 4 — Leadership émotionnel
            ['idx' => 70, 'dim' => 14, 'f' => 4, 'text' => "Je prends le temps d'observer ce qui donne de l'élan à chaque personne autour de moi."],
            ['idx' => 71, 'dim' => 14, 'f' => 4, 'text' => "Je suis capable d'insuffler de l'enthousiasme à un groupe qui manque d'énergie."],
            ['idx' => 72, 'dim' => 14, 'f' => 4, 'text' => "Je valorise les efforts et les réussites des personnes autour de moi."],
            ['idx' => 73, 'dim' => 14, 'f' => 4, 'text' => "Je formule des encouragements sincères et adaptés à la situation."],
            ['idx' => 74, 'dim' => 14, 'f' => 4, 'text' => "J'aide les autres à trouver du sens dans ce qu'ils font, même dans les moments difficiles."],
            ['idx' => 75, 'dim' => 15, 'f' => 4, 'text' => "Je fais face aux conflits plutôt que de les éviter."],
            ['idx' => 76, 'dim' => 15, 'f' => 4, 'text' => "Je recherche des solutions qui satisfont les deux parties plutôt que d'imposer ma vision."],
            ['idx' => 77, 'dim' => 15, 'f' => 4, 'text' => "Je reste calme et constructif(ve) dans les situations de tension interpersonnelle."],
            ['idx' => 78, 'dim' => 15, 'f' => 4, 'text' => "Je m'en prends au problème, pas à la personne."],
            ['idx' => 79, 'dim' => 15, 'f' => 4, 'text' => "Je désamorce une situation conflictuelle avant qu'elle ne s'aggrave."],

            // Famille 5 — Désirabilité sociale (Marlowe-Crowne)
            ['idx' => 80, 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de mentir légèrement pour éviter de blesser quelqu'un."],
            ['idx' => 81, 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive d'être moins patient(e) avec les autres que je ne le voudrais."],
            ['idx' => 82, 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de ressentir de l'envie face à la réussite d'un autre."],
            ['idx' => 83, 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de procrastiner, même quand je sais que je ne le devrais pas."],
            ['idx' => 84, 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de penser du mal de quelqu'un sans oser le lui dire."],
            ['idx' => 85, 'dim' => 0, 'f' => 5, 'text' => "Il m'arrive de me sentir irrité(e) par des comportements ou des personnes, même sans raison valable."],
        ];
    }
}
