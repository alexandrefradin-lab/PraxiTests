<?php

namespace Praxis\Plugins\PraxiSelf\Data;

/**
 * Parcours 60 jours PraxiSelf — Affirmation de soi.
 *
 * Fondements scientifiques :
 *   - Phillippa Lally (UCL, 2010)  : 66 jours pour ancrer une habitude
 *   - BJ Fogg (Tiny Habits, 2019)  : anchor habit — attacher à un comportement existant
 *   - James Clear (Atomic Habits)  : cue → craving → response → reward
 *   - Bandura (1997)               : auto-efficacité par petites victoires cumulées
 *
 * 4 phases :
 *   Phase 1 — Découverte   (J1–15)  : 5 min, très guidé
 *   Phase 2 — Installation (J16–30) : 6–7 min, semi-guidé
 *   Phase 3 — Renforcement (J31–45) : 8 min, autonomie croissante
 *   Phase 4 — Maîtrise     (J46–60) : 8–10 min, intégration totale
 *
 * Thèmes hebdomadaires :
 *   Sem. 1–2  (J1–14)  : Estime de soi — journal réussites, inventaire forces VIA, auto-compassion
 *   Sem. 3–4  (J15–28) : Assertivité comportementale — refus, disque rayé, formuler position
 *   Sem. 5–6  (J29–42) : Gestion du regard — défusion cognitive ACT, exposition sociale graduelle
 *   Sem. 7–8  (J43–56) : Expression des besoins — CNV, message "je", demande directe
 *   Sem. 9    (J57–60) : Intégration — scénario professionnel complet, bilan identitaire
 */
class Journey
{
    public static function all(): array
    {
        return [

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 1 — Estime de soi : premières victoires (J1–7)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 1,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Mon premier inventaire des forces',
                'exercise_ref'     => 'SE-04',
                'duration_minutes' => 5,
                'anchor'           => 'Le soir avant de vous coucher',
                'intention'        => 'Aujourd\'hui, je reconnais une chose positive en moi.',
                'micro_habit'      => 'Ouvrez l\'appli, notez une force que vous avez exprimée aujourd\'hui.',
                'reward'           => 'Cochez votre case. Chaque jour compte.',
                'weekly_theme'     => 'Découvrir ses forces',
                'tip_science'      => 'L\'auto-efficacité se construit par petites victoires (Bandura, 1997)',
            ],

            [
                'day'              => 2,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Journal des réussites — Jour 1',
                'exercise_ref'     => 'SE-01',
                'duration_minutes' => 5,
                'anchor'           => 'Le soir, après le dîner',
                'intention'        => 'Aujourd\'hui, je note trois réussites, même minimes.',
                'micro_habit'      => 'Posez votre téléphone sur la table, notez avant de vous lever.',
                'reward'           => 'Relisez vos 3 réussites à voix haute.',
                'weekly_theme'     => 'Découvrir ses forces',
                'tip_science'      => 'L\'attention aux succès renforce les circuits neuronaux positifs (Hebb, 1949)',
            ],

            [
                'day'              => 3,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Journal des réussites — Jour 2',
                'exercise_ref'     => 'SE-01',
                'duration_minutes' => 5,
                'anchor'           => 'Le soir, après le dîner',
                'intention'        => 'Aujourd\'hui, je cherche plus profondément ce qui a bien marché.',
                'micro_habit'      => 'Même heure qu\'hier. Même geste. La régularité crée l\'habitude.',
                'reward'           => 'Comparez avec hier : vous avez déjà 6 réussites en 2 jours.',
                'weekly_theme'     => 'Découvrir ses forces',
                'tip_science'      => 'La répétition à heure fixe réduit la friction cognitive (Fogg, 2019)',
            ],

            [
                'day'              => 4,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Affirmations ancrées sur preuves',
                'exercise_ref'     => 'SE-02',
                'duration_minutes' => 5,
                'anchor'           => 'Le matin, juste après votre premier café',
                'intention'        => 'Aujourd\'hui, je remplace une pensée limitante par une preuve concrète.',
                'micro_habit'      => 'Avant de regarder vos mails, faites cet exercice 5 minutes.',
                'reward'           => 'Prenez une photo de votre affirmation reformulée.',
                'weekly_theme'     => 'Découvrir ses forces',
                'tip_science'      => 'Les contre-preuves comportementales modifient les croyances (Beck, 1979)',
            ],

            [
                'day'              => 5,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Recadrage de l\'autocritique',
                'exercise_ref'     => 'SE-03',
                'duration_minutes' => 5,
                'anchor'           => 'Après une situation difficile de la journée',
                'intention'        => 'Aujourd\'hui, je traite une erreur comme une information, pas un verdict.',
                'micro_habit'      => 'Dès qu\'une autocritique surgit, posez-vous les 3 questions de recadrage.',
                'reward'           => 'Notez la version recadrée dans votre journal.',
                'weekly_theme'     => 'Découvrir ses forces',
                'tip_science'      => 'La restructuration cognitive réduit la rumination (Beck, 1979)',
            ],

            [
                'day'              => 6,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Inventaire VIA approfondi',
                'exercise_ref'     => 'SE-04',
                'duration_minutes' => 5,
                'anchor'           => 'Le soir avant de vous coucher',
                'intention'        => 'Aujourd\'hui, j\'identifie 5 forces avec leurs preuves concrètes.',
                'micro_habit'      => 'Relisez vos entrées de la semaine pour trouver des patterns.',
                'reward'           => 'Votre liste de forces sera votre boussole cette semaine.',
                'weekly_theme'     => 'Découvrir ses forces',
                'tip_science'      => 'Les forces de caractère VIA prédisent le bien-être (Seligman & Peterson, 2004)',
            ],

            [
                'day'              => 7,
                'week'             => 1,
                'phase'            => 'decouverte',
                'title'            => 'Bilan de semaine 1 — mes forces en action',
                'exercise_ref'     => 'SE-04',
                'duration_minutes' => 5,
                'anchor'           => 'Le dimanche soir, avant de préparer la semaine',
                'intention'        => 'Cette semaine, j\'ai découvert que je suis capable de ___.',
                'micro_habit'      => 'Relisez vos 7 entrées et formulez votre phrase de clôture de semaine.',
                'reward'           => 'Vous avez posé les fondations. La semaine prochaine, on construit.',
                'weekly_theme'     => 'Découvrir ses forces',
                'tip_science'      => 'La réflexion hebdomadaire consolide la mémoire à long terme (Ebbinghaus, 1885)',
            ],

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 2 — Estime de soi : auto-compassion et ancre identitaire (J8–14)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 8,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Deuil de la perfection',
                'exercise_ref'     => 'SE-19',
                'duration_minutes' => 5,
                'anchor'           => 'Le matin, juste après votre premier café',
                'intention'        => 'Aujourd\'hui, je lâche une exigence irréaliste envers moi-même.',
                'micro_habit'      => 'Nommez à voix haute votre exigence perfectionniste avant de commencer.',
                'reward'           => 'Notez ce que vous avez économisé en énergie en lâchant cette exigence.',
                'weekly_theme'     => 'Auto-compassion & identité',
                'tip_science'      => 'Le perfectionnisme érode l\'estime de soi à long terme (Brown, 2010)',
            ],

            [
                'day'              => 9,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Ancre identitaire — qui je suis vraiment',
                'exercise_ref'     => 'SE-17',
                'duration_minutes' => 5,
                'anchor'           => 'Le soir avant de vous coucher',
                'intention'        => 'Aujourd\'hui, je construis ma phrase d\'identité stable.',
                'micro_habit'      => 'Écrivez votre phrase d\'ancre dans votre journal. Relisez-la 3 fois.',
                'reward'           => 'Prenez en photo votre phrase. Elle devient votre écran de verrouillage cette semaine.',
                'weekly_theme'     => 'Auto-compassion & identité',
                'tip_science'      => 'Les valeurs comme ancre identitaire protègent contre la pression sociale (Harris, 2009)',
            ],

            [
                'day'              => 10,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Journal des réussites — Jour 3',
                'exercise_ref'     => 'SE-01',
                'duration_minutes' => 5,
                'anchor'           => 'Le soir, après le dîner',
                'intention'        => 'Aujourd\'hui, je connecte mes réussites à mes valeurs identifiées.',
                'micro_habit'      => 'Pour chaque réussite, notez quelle valeur elle exprime.',
                'reward'           => 'Vous voyez votre identité en action dans vos comportements.',
                'weekly_theme'     => 'Auto-compassion & identité',
                'tip_science'      => 'Relier les succès aux valeurs renforce la cohérence identitaire (Baumeister, 1997)',
            ],

            [
                'day'              => 11,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Affirmations ancrées — version 2',
                'exercise_ref'     => 'SE-02',
                'duration_minutes' => 5,
                'anchor'           => 'Le matin, juste après votre premier café',
                'intention'        => 'Aujourd\'hui, j\'affine mon affirmation avec des preuves plus récentes.',
                'micro_habit'      => 'Comparez votre affirmation de J4 avec celle d\'aujourd\'hui. Comment a-t-elle évolué ?',
                'reward'           => 'Vous avez une bibliothèque de preuves. Elle grandit chaque semaine.',
                'weekly_theme'     => 'Auto-compassion & identité',
                'tip_science'      => 'La répétition des contre-preuves modifie progressivement les schémas (Beck, 1979)',
            ],

            [
                'day'              => 12,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Recadrage — version compassionnelle',
                'exercise_ref'     => 'SE-03',
                'duration_minutes' => 5,
                'anchor'           => 'Après une situation difficile de la journée',
                'intention'        => 'Aujourd\'hui, je me parle comme je parlerais à un ami cher.',
                'micro_habit'      => 'Avant de recadrer, posez la question : "Que dirait un ami bienveillant ?"',
                'reward'           => 'Notez la différence de ton entre votre autocritique initiale et la version compassionnelle.',
                'weekly_theme'     => 'Auto-compassion & identité',
                'tip_science'      => 'L\'auto-compassion réduit l\'anxiété de performance (Neff, 2003)',
            ],

            [
                'day'              => 13,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Accepter un compliment avec grâce',
                'exercise_ref'     => 'SE-08',
                'duration_minutes' => 5,
                'anchor'           => 'Après avoir reçu un retour positif (ou en simulation)',
                'intention'        => 'Aujourd\'hui, je reçois la reconnaissance sans la minimiser.',
                'micro_habit'      => 'Pratiquez la phrase assertive devant le miroir avant de quitter la maison.',
                'reward'           => 'Notez combien de fois vous avez déjà minimisé un compliment cette semaine.',
                'weekly_theme'     => 'Auto-compassion & identité',
                'tip_science'      => 'Recevoir la reconnaissance renforce l\'estime de soi fonctionnelle (Alberti & Emmons, 2008)',
            ],

            [
                'day'              => 14,
                'week'             => 2,
                'phase'            => 'decouverte',
                'title'            => 'Bilan de semaine 2 — mon identité prend forme',
                'exercise_ref'     => 'SE-17',
                'duration_minutes' => 5,
                'anchor'           => 'Le dimanche soir',
                'intention'        => 'En deux semaines, j\'ai construit les bases de mon identité affirmée.',
                'micro_habit'      => 'Relisez vos 14 entrées et identifiez 3 patterns récurrents.',
                'reward'           => 'Vous entrez dans la phase d\'Installation. Le travail profond commence.',
                'weekly_theme'     => 'Auto-compassion & identité',
                'tip_science'      => 'La phase d\'automatisation d\'une habitude commence vers J14 (Lally, UCL, 2010)',
            ],

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 3 — Assertivité comportementale : dire non (J15–21)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 15,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Le refus assertif — première formulation',
                'exercise_ref'     => 'SE-05',
                'duration_minutes' => 6,
                'anchor'           => 'Le soir, après avoir identifié une demande difficile de la journée',
                'intention'        => 'Aujourd\'hui, je formule un refus clair sans m\'excuser excessivement.',
                'micro_habit'      => 'Identifiez une demande à laquelle vous avez dit oui par défaut. Reformulez votre refus.',
                'reward'           => 'Votre refus formulé = une frontière posée, même rétrospectivement.',
                'weekly_theme'     => 'Dire non avec dignité',
                'tip_science'      => 'Le droit à refuser est fondamental à l\'assertivité (Alberti & Emmons, 2008)',
            ],

            [
                'day'              => 16,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Refus assertif — simulation à voix haute',
                'exercise_ref'     => 'SE-05',
                'duration_minutes' => 6,
                'anchor'           => 'Le matin, avant de prendre vos transports',
                'intention'        => 'Aujourd\'hui, je m\'entraîne à dire non à voix haute avant d\'en avoir besoin.',
                'micro_habit'      => 'Prononcez votre refus devant le miroir pendant 2 minutes.',
                'reward'           => 'Notez comment votre voix sonne. Ferme ? Hésitante ? Chaque répétition l\'affermit.',
                'weekly_theme'     => 'Dire non avec dignité',
                'tip_science'      => 'La répétition comportementale réduit l\'anxiété situationnelle (Wolpe, 1958)',
            ],

            [
                'day'              => 17,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Critique constructive — préparation',
                'exercise_ref'     => 'SE-07',
                'duration_minutes' => 6,
                'anchor'           => 'Le soir, en préparant votre journée du lendemain',
                'intention'        => 'Aujourd\'hui, je prépare un feedback que j\'ai évité de donner.',
                'micro_habit'      => 'Rédigez votre message DESC complet avant de dormir.',
                'reward'           => 'Votre message est prêt. Vous n\'avez plus à improviser demain.',
                'weekly_theme'     => 'Dire non avec dignité',
                'tip_science'      => 'La préparation réduit l\'anxiété de communication (Meichenbaum, 1977)',
            ],

            [
                'day'              => 18,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Le disque rayé — introduction',
                'exercise_ref'     => 'SE-06',
                'duration_minutes' => 6,
                'anchor'           => 'Le soir, après le dîner',
                'intention'        => 'Aujourd\'hui, je comprends et pratique la résistance calme à l\'insistance.',
                'micro_habit'      => 'Simulez mentalement 3 formes d\'insistance et votre réponse stable.',
                'reward'           => 'Vous avez une phrase pivot. Elle fonctionne dans 80% des situations.',
                'weekly_theme'     => 'Dire non avec dignité',
                'tip_science'      => 'La technique du disque rayé résiste à la manipulation sociale (Smith, 1975)',
            ],

            [
                'day'              => 19,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Refus assertif — situation réelle',
                'exercise_ref'     => 'SE-05',
                'duration_minutes' => 6,
                'anchor'           => 'Dans les 2 heures suivant une demande reçue',
                'intention'        => 'Aujourd\'hui, je tente un vrai refus, même petit.',
                'micro_habit'      => 'Après votre refus réel, notez immédiatement : qu\'avez-vous ressenti ? Comment a réagi l\'autre ?',
                'reward'           => 'Chaque vrai refus enseigne plus que 10 simulations.',
                'weekly_theme'     => 'Dire non avec dignité',
                'tip_science'      => 'L\'exposition in vivo est plus efficace que la simulation seule (Wolpe, 1958)',
            ],

            [
                'day'              => 20,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Disque rayé — simulation avancée',
                'exercise_ref'     => 'SE-06',
                'duration_minutes' => 6,
                'anchor'           => 'Le matin, avant une réunion potentiellement difficile',
                'intention'        => 'Aujourd\'hui, je reste calme face à une pression répétée.',
                'micro_habit'      => 'Répétez votre phrase pivot 5 fois à voix haute avant la réunion.',
                'reward'           => 'Le calme face à l\'insistance est une compétence acquise, pas un trait de personnalité.',
                'weekly_theme'     => 'Dire non avec dignité',
                'tip_science'      => 'La régulation émotionnelle se renforce par la pratique délibérée (Linehan, 1993)',
            ],

            [
                'day'              => 21,
                'week'             => 3,
                'phase'            => 'installation',
                'title'            => 'Bilan semaine 3 — mes frontières prennent forme',
                'exercise_ref'     => 'SE-05',
                'duration_minutes' => 6,
                'anchor'           => 'Le dimanche soir',
                'intention'        => 'Cette semaine, j\'ai posé ___ frontières. Même une seule est une victoire.',
                'micro_habit'      => 'Listez vos refus de la semaine, réels ou simulés.',
                'reward'           => 'Comparez avec la semaine 1. Le changement est mesurable.',
                'weekly_theme'     => 'Dire non avec dignité',
                'tip_science'      => 'La conscience de sa progression renforce la motivation (Deci & Ryan, 2000)',
            ],

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 4 — Assertivité comportementale : tenir sa position (J22–28)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 22,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Formuler une critique constructive — délivrance',
                'exercise_ref'     => 'SE-07',
                'duration_minutes' => 7,
                'anchor'           => 'Avant une conversation difficile planifiée',
                'intention'        => 'Aujourd\'hui, je délivre un feedback préparé en semaine 3.',
                'micro_habit'      => 'Relisez votre message DESC de J17 avant la conversation.',
                'reward'           => 'Notez la réaction réelle vs votre scénario anticipé.',
                'weekly_theme'     => 'Tenir sa position',
                'tip_science'      => 'Le feedback assertif améliore la qualité des relations (Alberti & Emmons, 2008)',
            ],

            [
                'day'              => 23,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Répondre à une critique injuste',
                'exercise_ref'     => 'SE-16',
                'duration_minutes' => 7,
                'anchor'           => 'Après une situation de critique vécue ou anticipée',
                'intention'        => 'Aujourd\'hui, j\'utilise STOP avant de répondre à une attaque.',
                'micro_habit'      => 'Mémorisez STOP (Stop, Take a breath, Observe, Proceed) comme réflexe.',
                'reward'           => 'Une seconde de pause vaut mieux que 10 minutes de regrets.',
                'weekly_theme'     => 'Tenir sa position',
                'tip_science'      => 'La régulation émotionnelle préventive réduit les réponses impulsives (Linehan, 1993)',
            ],

            [
                'day'              => 24,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Tolérance au désaccord — exercice 1',
                'exercise_ref'     => 'SE-18',
                'duration_minutes' => 7,
                'anchor'           => 'Après une réunion où vous n\'avez pas exprimé votre désaccord',
                'intention'        => 'Aujourd\'hui, j\'identifie un désaccord non exprimé et je le formule pour moi.',
                'micro_habit'      => 'Notez ce que vous auriez dit si vous n\'aviez pas eu peur du jugement.',
                'reward'           => 'Cette note est votre premier brouillon pour la prochaine fois.',
                'weekly_theme'     => 'Tenir sa position',
                'tip_science'      => 'Le désaccord respectueux est un indicateur de santé organisationnelle (Lencioni, 2002)',
            ],

            [
                'day'              => 25,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Disque rayé — face à la pression du groupe',
                'exercise_ref'     => 'SE-06',
                'duration_minutes' => 7,
                'anchor'           => 'Avant une réunion d\'équipe',
                'intention'        => 'Aujourd\'hui, je me prépare à maintenir ma position face à la majorité.',
                'micro_habit'      => 'Écrivez vos 3 arguments avant la réunion. Vous les aurez en tête.',
                'reward'           => 'Avoir des arguments écrits réduit l\'intimidation de la majorité de 40%.',
                'weekly_theme'     => 'Tenir sa position',
                'tip_science'      => 'La préparation cognitive réduit la conformité sociale (Asch, 1951)',
            ],

            [
                'day'              => 26,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Négocier une demande déraisonnable',
                'exercise_ref'     => 'SE-15',
                'duration_minutes' => 7,
                'anchor'           => 'Dans l\'heure suivant une demande perçue comme excessive',
                'intention'        => 'Aujourd\'hui, je négocie plutôt que je subis.',
                'micro_habit'      => 'Reformulez la demande en question d\'intérêts : "Quel est le vrai besoin derrière ?"',
                'reward'           => 'Une négociation réussie = un "non" transformé en solution partagée.',
                'weekly_theme'     => 'Tenir sa position',
                'tip_science'      => 'Négocier sur les intérêts, pas les positions (Fisher & Ury, 1981)',
            ],

            [
                'day'              => 27,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Tolérance au désaccord — expression réelle',
                'exercise_ref'     => 'SE-18',
                'duration_minutes' => 7,
                'anchor'           => 'Lors de la prochaine réunion avec désaccord potentiel',
                'intention'        => 'Aujourd\'hui, j\'exprime un désaccord fondé, calmement.',
                'micro_habit'      => 'Utilisez la formulation de J24 et notez la réaction réelle.',
                'reward'           => 'Exprimer un désaccord sans rupture relationnelle est une compétence rare.',
                'weekly_theme'     => 'Tenir sa position',
                'tip_science'      => 'Le désaccord assertif préserve la relation s\'il est respectueux (Smith, 1975)',
            ],

            [
                'day'              => 28,
                'week'             => 4,
                'phase'            => 'installation',
                'title'            => 'Bilan semaine 4 — j\'affirme ma position',
                'exercise_ref'     => 'SE-18',
                'duration_minutes' => 7,
                'anchor'           => 'Le dimanche soir',
                'intention'        => 'Cette semaine, j\'ai tenu ma position ___ fois. Je mesure mon progrès.',
                'micro_habit'      => 'Listez 3 situations où votre assertivité a progressé depuis J1.',
                'reward'           => 'Vous avez complété la Phase 2. Le renforcement commence.',
                'weekly_theme'     => 'Tenir sa position',
                'tip_science'      => 'À J28, la plupart des comportements assertifs deviennent semi-automatiques (Lally, 2010)',
            ],

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 5 — Gestion du regard : défusion cognitive (J29–35)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 29,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Défusion cognitive — introduction ACT',
                'exercise_ref'     => 'SE-09',
                'duration_minutes' => 8,
                'anchor'           => 'Le soir, après une situation sociale inconfortable',
                'intention'        => 'Aujourd\'hui, je sépare ce que je pense de ce que je suis.',
                'micro_habit'      => 'Chaque pensée autocritique : transformez "Je suis..." en "J\'ai la pensée que..."',
                'reward'           => 'Cette seule reformulation crée de l\'espace entre vous et vos pensées.',
                'weekly_theme'     => 'Défusion cognitive',
                'tip_science'      => 'La défusion cognitive réduit l\'impact émotionnel des pensées négatives (Hayes, 2004)',
            ],

            [
                'day'              => 30,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Tester la réalité du regard des autres',
                'exercise_ref'     => 'SE-09',
                'duration_minutes' => 8,
                'anchor'           => 'Après une situation où vous avez modifié votre comportement par peur du jugement',
                'intention'        => 'Aujourd\'hui, je quantifie la probabilité réelle d\'un jugement négatif.',
                'micro_habit'      => 'Notez votre estimation initiale, puis l\'estimation réaliste après les 3 questions.',
                'reward'           => 'Le risque perçu est presque toujours 5 à 10 fois supérieur au risque réel.',
                'weekly_theme'     => 'Défusion cognitive',
                'tip_science'      => 'Le biais d\'illusion de transparence surestime la visibilité de nos états internes (Gilovich, 1998)',
            ],

            [
                'day'              => 31,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Visualisation de succès assertif — niveau 1',
                'exercise_ref'     => 'SE-11',
                'duration_minutes' => 8,
                'anchor'           => 'Le matin, avant une situation sociale importante',
                'intention'        => 'Aujourd\'hui, je programme mon cerveau pour un succès avant qu\'il arrive.',
                'micro_habit'      => 'Visualisez la scène en détail : lieu, personnes, vos mots, la réaction positive.',
                'reward'           => 'La simulation mentale active les mêmes circuits neuronaux que l\'action réelle.',
                'weekly_theme'     => 'Défusion cognitive',
                'tip_science'      => 'La simulation mentale améliore les performances comportementales (Bandura, 1997)',
            ],

            [
                'day'              => 32,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Exposition graduelle — niveau 1 (question factuelle)',
                'exercise_ref'     => 'SE-10',
                'duration_minutes' => 8,
                'anchor'           => 'Avant votre prochaine réunion à 2-3 personnes',
                'intention'        => 'Aujourd\'hui, je pose une question de clarification — le niveau d\'exposition minimal.',
                'micro_habit'      => 'Préparez une question factuelle avant la réunion. Engagez-vous à la poser.',
                'reward'           => 'Une question posée = exposition réussie. Demain, le niveau suivant.',
                'weekly_theme'     => 'Défusion cognitive',
                'tip_science'      => 'L\'exposition graduelle réduit l\'anxiété sociale par habituation (Wolpe, 1958)',
            ],

            [
                'day'              => 33,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Défusion — les pensées comme nuages',
                'exercise_ref'     => 'SE-09',
                'duration_minutes' => 8,
                'anchor'           => 'Le soir, dans les 10 minutes avant de dormir',
                'intention'        => 'Aujourd\'hui, j\'observe mes pensées sans m\'y identifier.',
                'micro_habit'      => 'Allongé(e), imaginez chaque pensée critique comme un nuage qui passe. Ne les retenez pas.',
                'reward'           => 'Vous n\'êtes pas vos pensées. Vous êtes celui ou celle qui les observe.',
                'weekly_theme'     => 'Défusion cognitive',
                'tip_science'      => 'La pleine conscience réductive diminue la fusion cognitive (Hayes, 2004)',
            ],

            [
                'day'              => 34,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Exposition — niveau 2 (donner un avis)',
                'exercise_ref'     => 'SE-10',
                'duration_minutes' => 8,
                'anchor'           => 'Lors d\'une réunion d\'équipe de 4-6 personnes',
                'intention'        => 'Aujourd\'hui, je donne mon avis sur un point précis en réunion.',
                'micro_habit'      => 'Préparez votre phrase d\'amorce : "Mon point de vue sur cela est..."',
                'reward'           => 'Chaque prise de parole est une donnée sur la réalité du risque social.',
                'weekly_theme'     => 'Défusion cognitive',
                'tip_science'      => 'L\'exposition répétée diminue la réponse anxieuse (Foa & Kozak, 1986)',
            ],

            [
                'day'              => 35,
                'week'             => 5,
                'phase'            => 'renforcement',
                'title'            => 'Bilan semaine 5 — je regarde le regard autrement',
                'exercise_ref'     => 'SE-09',
                'duration_minutes' => 8,
                'anchor'           => 'Le dimanche soir',
                'intention'        => 'Cette semaine, j\'ai réduit mon adaptation au regard des autres dans ___ situations.',
                'micro_habit'      => 'Comparez vos estimations initiales du risque social à la réalité vécue.',
                'reward'           => 'Vous avez des données réelles. Elles parlent plus fort que vos peurs.',
                'weekly_theme'     => 'Défusion cognitive',
                'tip_science'      => 'L\'auto-observation des distorsions cognitives les réduit (Beck, 1979)',
            ],

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 6 — Gestion du regard : exposition sociale (J36–42)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 36,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Exposition — niveau 3 (désaccord poli à 10 personnes)',
                'exercise_ref'     => 'SE-10',
                'duration_minutes' => 8,
                'anchor'           => 'Avant une réunion à 8 personnes ou plus',
                'intention'        => 'Aujourd\'hui, j\'exprime poliment un désaccord devant un groupe élargi.',
                'micro_habit'      => 'Préparez une formulation de désaccord : "Je vois les choses différemment sur ce point..."',
                'reward'           => 'S\'opposer avec respect dans un grand groupe = niveau d\'assertivité avancé.',
                'weekly_theme'     => 'Exposition sociale graduelle',
                'tip_science'      => 'L\'exposition in vivo à des groupes larges est plus efficace que l\'imaginaire (Marks, 1981)',
            ],

            [
                'day'              => 37,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Visualisation avancée — scénario difficile',
                'exercise_ref'     => 'SE-11',
                'duration_minutes' => 8,
                'anchor'           => 'Le matin, avant une situation anticipée difficile',
                'intention'        => 'Aujourd\'hui, je visualise la situation la plus difficile de ma semaine et je la réussis.',
                'micro_habit'      => 'Visualisez 3 variantes de la même scène pour renforcer la généralisation.',
                'reward'           => 'La préparation mentale détaillée réduit le stress situationnel de 25-30%.',
                'weekly_theme'     => 'Exposition sociale graduelle',
                'tip_science'      => 'La simulation mentale multi-scénarios améliore la flexibilité comportementale (Bandura, 1997)',
            ],

            [
                'day'              => 38,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Ancre identitaire — test de robustesse',
                'exercise_ref'     => 'SE-17',
                'duration_minutes' => 8,
                'anchor'           => 'Après une critique ou un désaccord vécu',
                'intention'        => 'Aujourd\'hui, ma phrase d\'identité résiste à une vraie pression.',
                'micro_habit'      => 'Relisez votre phrase de J9. Tenez-vous toujours debout derrière elle ?',
                'reward'           => 'Si oui, vous avez une identité stable. Si non, affinez-la.',
                'weekly_theme'     => 'Exposition sociale graduelle',
                'tip_science'      => 'Une identité stable réduit la vulnérabilité aux critiques (Bowlby, 1988)',
            ],

            [
                'day'              => 39,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Répondre à une critique — simulation avancée',
                'exercise_ref'     => 'SE-16',
                'duration_minutes' => 8,
                'anchor'           => 'Le soir, en simulant 3 types de critiques',
                'intention'        => 'Aujourd\'hui, je prépare une réponse assertive à 3 formes de critique.',
                'micro_habit'      => 'Simulez : critique factuelle, critique injuste, critique publique. Une réponse pour chaque.',
                'reward'           => 'Vous avez un répertoire. La prochaine critique ne vous surprendra pas.',
                'weekly_theme'     => 'Exposition sociale graduelle',
                'tip_science'      => 'La préparation multi-scénarios réduit la réactivité émotionnelle (Meichenbaum, 1977)',
            ],

            [
                'day'              => 40,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Exposition — niveau 4 (présenter une idée nouvelle)',
                'exercise_ref'     => 'SE-10',
                'duration_minutes' => 8,
                'anchor'           => 'Lors d\'une réunion stratégique ou d\'un CODIR',
                'intention'        => 'Aujourd\'hui, je présente spontanément une idée nouvelle devant un groupe.',
                'micro_habit'      => 'Préparez votre idée en 3 phrases max. Cherchez la première opportunité.',
                'reward'           => 'Présenter une idée nouvelle = le niveau d\'exposition le plus élevé. Bravo.',
                'weekly_theme'     => 'Exposition sociale graduelle',
                'tip_science'      => 'La maîtrise comportementale progressive renforce l\'auto-efficacité (Bandura, 1977)',
            ],

            [
                'day'              => 41,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Défusion et exposition — intégration',
                'exercise_ref'     => 'SE-09',
                'duration_minutes' => 8,
                'anchor'           => 'Le soir, après une journée sociale intense',
                'intention'        => 'Aujourd\'hui, j\'observe comment la défusion m\'a aidé pendant mon exposition.',
                'micro_habit'      => 'Notez 2 moments où vous avez utilisé la défusion cognitive en temps réel.',
                'reward'           => 'L\'outil fonctionne quand il devient réflexe, pas technique.',
                'weekly_theme'     => 'Exposition sociale graduelle',
                'tip_science'      => 'L\'intégration ACT + exposition est plus efficace que chaque méthode seule (Hayes, 2004)',
            ],

            [
                'day'              => 42,
                'week'             => 6,
                'phase'            => 'renforcement',
                'title'            => 'Bilan semaine 6 — je vis avec les regards sans en avoir peur',
                'exercise_ref'     => 'SE-11',
                'duration_minutes' => 8,
                'anchor'           => 'Le dimanche soir',
                'intention'        => 'En 6 semaines, ma relation au regard des autres a changé. Comment ?',
                'micro_habit'      => 'Comparez votre niveau d\'exposition moyen de J1 à J42.',
                'reward'           => 'Vous entrez dans la Phase 4. L\'assertivité devient votre façon d\'être.',
                'weekly_theme'     => 'Exposition sociale graduelle',
                'tip_science'      => 'À J42, les habitudes sociales sont ancrées dans les schémas comportementaux (Lally, 2010)',
            ],

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 7 — Expression des besoins : CNV et message "je" (J43–49)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 43,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Identifier mes besoins non exprimés',
                'exercise_ref'     => 'SE-12',
                'duration_minutes' => 8,
                'anchor'           => 'Le soir, après une frustration professionnelle',
                'intention'        => 'Aujourd\'hui, je descends jusqu\'au besoin universel derrière ma frustration.',
                'micro_habit'      => 'Posez les 3 questions CNV : émotion → besoin non satisfait → besoin profond.',
                'reward'           => 'Nommer son besoin est déjà 50% du travail assertif.',
                'weekly_theme'     => 'Communication Non-Violente',
                'tip_science'      => 'Identifier ses besoins réduit les comportements passifs-agressifs (Rosenberg, 2003)',
            ],

            [
                'day'              => 44,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Message "Je" — construction',
                'exercise_ref'     => 'SE-13',
                'duration_minutes' => 9,
                'anchor'           => 'Le soir, en préparant une conversation difficile',
                'intention'        => 'Aujourd\'hui, je construis un message "je" complet en 4 temps.',
                'micro_habit'      => 'Vérifiez qu\'il n\'y a aucun "tu" accusateur dans votre message. Reformulez si besoin.',
                'reward'           => 'Un message "je" parfait = responsabilité de ses émotions sans attaque.',
                'weekly_theme'     => 'Communication Non-Violente',
                'tip_science'      => 'L\'observation factuelle sans jugement réduit la défensivité de l\'interlocuteur (Rosenberg, 2003)',
            ],

            [
                'day'              => 45,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Demander de l\'aide sans se dévaloriser',
                'exercise_ref'     => 'SE-14',
                'duration_minutes' => 8,
                'anchor'           => 'Avant de solliciter un collègue ou un supérieur',
                'intention'        => 'Aujourd\'hui, je demande de l\'aide directement, sans m\'excuser d\'en avoir besoin.',
                'micro_habit'      => 'Comparez votre formulation naturelle avec le modèle assertif. Pratiquez 3 fois.',
                'reward'           => 'Demander de l\'aide efficacement = compétence professionnelle, pas faiblesse.',
                'weekly_theme'     => 'Communication Non-Violente',
                'tip_science'      => 'La demande directe et non apologétique augmente les taux de réponse positive (Cialdini, 1984)',
            ],

            [
                'day'              => 46,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Message "Je" — délivrance réelle',
                'exercise_ref'     => 'SE-13',
                'duration_minutes' => 9,
                'anchor'           => 'Lors d\'une conversation prévue avec la personne concernée',
                'intention'        => 'Aujourd\'hui, je délivre mon message "je" en situation réelle.',
                'micro_habit'      => 'Notez la réaction de l\'autre et votre ressenti après la conversation.',
                'reward'           => 'Une conversation difficile bien menée renforce plus que 10 simulations.',
                'weekly_theme'     => 'Communication Non-Violente',
                'tip_science'      => 'La CNV améliore la qualité des relations professionnelles à long terme (Rosenberg, 2003)',
            ],

            [
                'day'              => 47,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Négociation avancée — intérêts vs positions',
                'exercise_ref'     => 'SE-15',
                'duration_minutes' => 9,
                'anchor'           => 'Face à une demande ou contrainte professionnelle',
                'intention'        => 'Aujourd\'hui, je cherche le besoin réel derrière toute demande avant de répondre.',
                'micro_habit'      => 'Avant chaque réponse à une demande : "Quel est le vrai besoin ici ?"',
                'reward'           => 'La négociation sur les intérêts crée des solutions que les deux parties souhaitent.',
                'weekly_theme'     => 'Communication Non-Violente',
                'tip_science'      => 'La négociation raisonnée produit des accords plus durables (Fisher & Ury, 1981)',
            ],

            [
                'day'              => 48,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Inventaire des besoins non exprimés de la semaine',
                'exercise_ref'     => 'SE-12',
                'duration_minutes' => 8,
                'anchor'           => 'Le vendredi soir, bilan de semaine',
                'intention'        => 'Aujourd\'hui, je recense tous les besoins non exprimés de ma semaine.',
                'micro_habit'      => 'Listez-les tous. Lesquels avez-vous exprimés ? Lesquels restent en suspens ?',
                'reward'           => 'La conscience de vos besoins non exprimés est le premier pas vers leur expression.',
                'weekly_theme'     => 'Communication Non-Violente',
                'tip_science'      => 'Les besoins non reconnus génèrent des tensions relationnelles chroniques (Rosenberg, 2003)',
            ],

            [
                'day'              => 49,
                'week'             => 7,
                'phase'            => 'maitrise',
                'title'            => 'Bilan semaine 7 — j\'exprime ce dont j\'ai besoin',
                'exercise_ref'     => 'SE-12',
                'duration_minutes' => 8,
                'anchor'           => 'Le dimanche soir',
                'intention'        => 'Cette semaine, j\'ai exprimé ___ besoins qui restaient silencieux avant.',
                'micro_habit'      => 'Comparez vos conversations de la semaine 7 avec celles de la semaine 1.',
                'reward'           => 'La CNV n\'est plus une technique — c\'est votre façon de communiquer.',
                'weekly_theme'     => 'Communication Non-Violente',
                'tip_science'      => 'L\'intégration de la CNV prend en moyenne 6-8 semaines de pratique régulière (Rosenberg, 2003)',
            ],

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 8 — Expression des besoins : demande directe (J50–56)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 50,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'La demande directe — formulation avancée',
                'exercise_ref'     => 'SE-14',
                'duration_minutes' => 9,
                'anchor'           => 'Avant de faire une demande importante à un supérieur',
                'intention'        => 'Aujourd\'hui, je formule une demande importante avec clarté et directivité.',
                'micro_habit'      => 'Ma demande est-elle concrète, positive, actionnable, et adressée à une personne précise ?',
                'reward'           => 'Une demande bien formulée multiplie les chances d\'un oui par 2 à 3.',
                'weekly_theme'     => 'Demande directe',
                'tip_science'      => 'La précision de la demande est le facteur le plus prédictif de son succès (Cialdini, 1984)',
            ],

            [
                'day'              => 51,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Jeu de rôle — demander une évolution',
                'exercise_ref'     => 'SE-20',
                'duration_minutes' => 9,
                'anchor'           => 'Le soir, en simulant une conversation avec votre manager',
                'intention'        => 'Aujourd\'hui, je simule la demande la plus difficile : demander une évolution.',
                'micro_habit'      => 'Jouez la scène à voix haute. Enregistrez-vous si possible.',
                'reward'           => 'Se pré-enregistrer réduit considérablement la charge émotionnelle de la vraie conversation.',
                'weekly_theme'     => 'Demande directe',
                'tip_science'      => 'La répétition comportementale à voix haute améliore la performance réelle (Bandura, 1997)',
            ],

            [
                'day'              => 52,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Répondre à une critique injuste — maîtrise',
                'exercise_ref'     => 'SE-16',
                'duration_minutes' => 9,
                'anchor'           => 'Après une critique reçue dans la journée',
                'intention'        => 'Aujourd\'hui, j\'applique STOP automatiquement, sans y penser.',
                'micro_habit'      => 'Évaluez : combien de secondes vous a pris votre pause avant de répondre ?',
                'reward'           => 'Moins de 3 secondes de pause = STOP intégré. Compétence acquise.',
                'weekly_theme'     => 'Demande directe',
                'tip_science'      => 'L\'automatisation des régulations émotionnelles réduit le coût cognitif (Linehan, 1993)',
            ],

            [
                'day'              => 53,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Jeu de rôle — signaler une décision contre-productive',
                'exercise_ref'     => 'SE-20',
                'duration_minutes' => 10,
                'anchor'           => 'Le soir, en préparant une vraie conversation délicate',
                'intention'        => 'Aujourd\'hui, je prépare et joue la scène la plus courageuse : remettre en question une décision hiérarchique.',
                'micro_habit'      => 'Préparez votre ouverture (30 sec), la réaction probable, votre réponse si déraillement.',
                'reward'           => 'La préparation scénarisée transforme le courage en compétence.',
                'weekly_theme'     => 'Demande directe',
                'tip_science'      => 'La répétition comportementale complexe consolide les nouvelles connexions neurales (Hebb, 1949)',
            ],

            [
                'day'              => 54,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Ancre identitaire — version finale',
                'exercise_ref'     => 'SE-17',
                'duration_minutes' => 9,
                'anchor'           => 'Le matin, avant votre journée',
                'intention'        => 'Aujourd\'hui, ma phrase d\'identité est gravée. Elle me porte naturellement.',
                'micro_habit'      => 'Relisez et récitez votre phrase de J9 puis de J38. Laquelle vous représente le mieux aujourd\'hui ?',
                'reward'           => 'Votre identité affirmée est la base de toute assertivité durable.',
                'weekly_theme'     => 'Demande directe',
                'tip_science'      => 'L\'identité stable est le prédicteur le plus fort de l\'assertivité long terme (Harris, 2009)',
            ],

            [
                'day'              => 55,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Tolérance au désaccord — maîtrise complète',
                'exercise_ref'     => 'SE-18',
                'duration_minutes' => 9,
                'anchor'           => 'Lors de la prochaine réunion avec désaccord potentiel',
                'intention'        => 'Aujourd\'hui, j\'exprime mon désaccord avec calme absolu, même si je suis seul(e).',
                'micro_habit'      => 'Après la réunion, notez : avez-vous exprimé votre désaccord ? Pourquoi oui ou non ?',
                'reward'           => 'Exprimer un désaccord solitaire face à un groupe = niveau expert atteint.',
                'weekly_theme'     => 'Demande directe',
                'tip_science'      => 'L\'indépendance de jugement face à la pression sociale est le marqueur d\'une assertivité mature (Asch, 1951)',
            ],

            [
                'day'              => 56,
                'week'             => 8,
                'phase'            => 'maitrise',
                'title'            => 'Bilan semaine 8 — je demande ce dont j\'ai besoin',
                'exercise_ref'     => 'SE-14',
                'duration_minutes' => 9,
                'anchor'           => 'Le dimanche soir',
                'intention'        => 'En 8 semaines, ma capacité à demander directement a changé. Comment ?',
                'micro_habit'      => 'Listez 3 demandes que vous avez formulées cette semaine. Résultats ?',
                'reward'           => 'Plus que 4 jours. L\'intégration finale vous attend.',
                'weekly_theme'     => 'Demande directe',
                'tip_science'      => 'À J56, le comportement assertif est intégré dans l\'identité professionnelle (Lally, 2010)',
            ],

            // ═══════════════════════════════════════════════════════════════
            // SEMAINE 9 — Intégration totale (J57–60)
            // ═══════════════════════════════════════════════════════════════

            [
                'day'              => 57,
                'week'             => 9,
                'phase'            => 'maitrise',
                'title'            => 'Scénario professionnel complet — demander une augmentation',
                'exercise_ref'     => 'SE-20',
                'duration_minutes' => 10,
                'anchor'           => 'Le soir, dans un moment de calme absolu',
                'intention'        => 'Aujourd\'hui, je joue le scénario le plus exigeant en mobilisant tout ce que j\'ai appris.',
                'micro_habit'      => 'Situation A de SE-20 : demander une augmentation. Toutes les techniques intégrées.',
                'reward'           => 'Ce n\'est plus un exercice. C\'est une répétition générale.',
                'weekly_theme'     => 'Intégration totale',
                'tip_science'      => 'La performance accomplie en situation complexe est la source la plus puissante d\'auto-efficacité (Bandura, 1997)',
            ],

            [
                'day'              => 58,
                'week'             => 9,
                'phase'            => 'maitrise',
                'title'            => 'Scénario professionnel complet — relation toxique',
                'exercise_ref'     => 'SE-20',
                'duration_minutes' => 10,
                'anchor'           => 'Le soir, dans un moment de calme absolu',
                'intention'        => 'Aujourd\'hui, je prépare comment mettre fin à une dynamique professionnelle toxique.',
                'micro_habit'      => 'Situation C de SE-20 : clarté, respect, fermeté. Sans agressivité ni culpabilité.',
                'reward'           => 'Vous avez le droit de changer une relation toxique. Et maintenant vous savez comment.',
                'weekly_theme'     => 'Intégration totale',
                'tip_science'      => 'Les relations professionnelles toxiques non adressées érodent l\'estime de soi à long terme (Leiter & Maslach, 2005)',
            ],

            [
                'day'              => 59,
                'week'             => 9,
                'phase'            => 'maitrise',
                'title'            => 'Bilan identitaire — qui suis-je devenu(e) ?',
                'exercise_ref'     => 'SE-17',
                'duration_minutes' => 10,
                'anchor'           => 'Dans un lieu calme, avec votre journal',
                'intention'        => 'Aujourd\'hui, je compare ma phrase d\'identité de J1 à celle d\'aujourd\'hui.',
                'micro_habit'      => 'Relisez vos 58 entrées. Identifiez le changement le plus significatif.',
                'reward'           => 'La distance entre J1 et J59 est votre preuve de croissance.',
                'weekly_theme'     => 'Intégration totale',
                'tip_science'      => 'La réflexivité identitaire est le marqueur de l\'assertivité intégrée vs superficielle (Bandura, 1997)',
            ],

            [
                'day'              => 60,
                'week'             => 9,
                'phase'            => 'maitrise',
                'title'            => 'Jour 60 — mon affirmation de soi est ancrée',
                'exercise_ref'     => 'SE-20',
                'duration_minutes' => 10,
                'anchor'           => 'Le moment le plus significatif de votre journée',
                'intention'        => 'Aujourd\'hui, j\'agis depuis qui je suis devenu(e), pas depuis qui j\'avais peur d\'être.',
                'micro_habit'      => 'Choisissez votre situation réelle la plus difficile. Appliquez tout. Notez le résultat.',
                'reward'           => 'Vous avez complété 60 jours. L\'habitude est ancrée. L\'affirmation de soi est maintenant la vôtre.',
                'weekly_theme'     => 'Intégration totale',
                'tip_science'      => '66 jours pour ancrer une habitude — vous êtes à 91% du chemin. La routine est désormais automatique (Lally, UCL, 2010)',
            ],

        ];
    }

    /**
     * Retourne un jour spécifique du parcours.
     */
    public static function day(int $day): ?array
    {
        return collect(self::all())->firstWhere('day', $day);
    }

    /**
     * Retourne les jours d'une phase donnée.
     * Phases : 'decouverte', 'installation', 'renforcement', 'maitrise'
     */
    public static function byPhase(string $phase): array
    {
        return array_values(
            array_filter(self::all(), fn ($d) => $d['phase'] === $phase)
        );
    }

    /**
     * Retourne les jours d'une semaine donnée (1–9).
     */
    public static function byWeek(int $week): array
    {
        return array_values(
            array_filter(self::all(), fn ($d) => $d['week'] === $week)
        );
    }

    /**
     * Libellés des phases avec couleurs et durées.
     */
    public static function phaseMeta(): array
    {
        return [
            'decouverte'    => [
                'label'     => 'Découverte',
                'days'      => '1–15',
                'color'     => 'var(--pt-navy)',
                'duration'  => '5 min',
                'desc'      => 'Très guidé — fondations de l\'estime de soi',
            ],
            'installation'  => [
                'label'     => 'Installation',
                'days'      => '16–30',
                'color'     => 'var(--pt-gold)',
                'duration'  => '6–7 min',
                'desc'      => 'Semi-guidé — assertivité comportementale',
            ],
            'renforcement'  => [
                'label'     => 'Renforcement',
                'days'      => '31–45',
                'color'     => '#2563eb',
                'duration'  => '8 min',
                'desc'      => 'Autonomie croissante — gestion du regard',
            ],
            'maitrise'      => [
                'label'     => 'Maîtrise',
                'days'      => '46–60',
                'color'     => '#7c3aed',
                'duration'  => '8–10 min',
                'desc'      => 'Intégration totale — expression & identité',
            ],
        ];
    }
}
