<?php

namespace Praxis\Plugins\PraxiSelf\Data;

class Exercises
{
    /**
     * Retourne les 20 exercices d'affirmation de soi.
     *
     * Bases scientifiques :
     *   - Bandura (1977, 1997) — théorie de l'auto-efficacité
     *   - Rosenberg (2003) — Communication Non-Violente (CNV)
     *   - Alberti & Emmons (1974, 2008) — assertivité comportementale
     *   - Wolpe (1958) — désensibilisation systématique / exposition graduelle
     *   - Smith (1975) — technique du disque rayé
     *   - Beck (1979) — restructuration cognitive
     */
    public static function all(): array
    {
        return [

            // ─── CATÉGORIE : ESTIME DE SOI ───────────────────────────────────

            [
                'id'               => 'SE-01',
                'title'            => 'Journal des réussites quotidiennes',
                'category'         => 'confiance',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => 'Bandura (1997) — expériences de maîtrise comme source primaire d\'auto-efficacité',
                'scoring'          => ['dimension' => 'estime_de_soi', 'weight' => 1.2],
                'instructions'     => <<<EOT
Étape 1 — Pause & respiration (1 min)
Installe-toi confortablement. Prends trois respirations profondes : inspire 4 secondes, expire 6 secondes.

Étape 2 — Remontée mémorielle (2 min)
Pense à ta journée de travail ou à ta semaine. Identifie 3 moments précis où tu as fait quelque chose de bien, même minime :
  • "J'ai répondu clairement à une question difficile."
  • "J'ai tenu une deadline malgré la pression."
  • "J'ai aidé un collègue à débloquer un problème."

Étape 3 — Ancrage positif (2 min)
Pour chaque réussite, formule mentalement (ou à voix basse) : "J'ai été capable de ___, cela prouve que je suis quelqu'un qui ___."
Exemple : "J'ai géré ce dossier urgent, cela prouve que je suis quelqu'un de fiable sous pression."

Répétition recommandée : 7 jours consécutifs pour un effet durable.
EOT,
            ],

            [
                'id'               => 'SE-02',
                'title'            => 'Affirmations ancrées sur preuves',
                'category'         => 'confiance',
                'duration_minutes' => 4,
                'difficulty'       => 1,
                'scientific_basis' => 'Bandura (1997) — restructuration des croyances d\'auto-efficacité par preuves comportementales',
                'scoring'          => ['dimension' => 'estime_de_soi', 'weight' => 1.0],
                'instructions'     => <<<EOT
Étape 1 — Identifier la croyance limitante (1 min)
Nomme une croyance négative récurrente sur toi-même dans un contexte professionnel.
Exemple : "Je ne suis pas assez légitime pour prendre la parole en réunion."

Étape 2 — Chercher les contre-preuves (2 min)
Liste 3 situations réelles passées qui contredisent cette croyance.
Exemple : "J'ai présenté le projet X devant 10 personnes. J'ai proposé une idée qui a été retenue lors de la réunion Y."

Étape 3 — Reformuler l'affirmation (1 min)
Crée une affirmation nuancée et fondée sur tes preuves :
"Bien que j'aie parfois des doutes, j'ai démontré que je suis capable de ___ [preuve 1], ___ [preuve 2]."
Répète-la à voix haute, lentement, 3 fois.
EOT,
            ],

            [
                'id'               => 'SE-03',
                'title'            => 'Recadrage de l\'autocritique',
                'category'         => 'confiance',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Beck (1979) — restructuration cognitive des pensées automatiques négatives',
                'scoring'          => ['dimension' => 'estime_de_soi', 'weight' => 1.1],
                'instructions'     => <<<EOT
Étape 1 — Capturer l\'autocritique (1 min)
Note une autocritique récente que tu t'es faite suite à une erreur professionnelle.
Exemple : "J'aurais dû savoir ça, je suis incompétent."

Étape 2 — Tester la réalité (2 min)
Réponds mentalement à ces 3 questions :
  • Est-ce que je dirais cela à un collègue que j'apprécie dans la même situation ?
  • Quels éléments de contexte n\'ai-je pas pris en compte ?
  • Quelle serait la version la plus juste et équilibrée des faits ?

Étape 3 — Reformuler avec bienveillance (2 min)
Remplace l\'autocritique par un énoncé factuel et constructif :
"Dans cette situation, j'ai manqué de ___ [compétence/information]. Je vais ___ [action concrète] pour progresser."
EOT,
            ],

            [
                'id'               => 'SE-04',
                'title'            => 'Inventaire des forces personnelles',
                'category'         => 'confiance',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => 'Seligman & Peterson (2004) — psychologie positive, forces de caractère VIA',
                'scoring'          => ['dimension' => 'estime_de_soi', 'weight' => 1.0],
                'instructions'     => <<<EOT
Étape 1 — Lister tes forces (3 min)
Sans censure, note 5 qualités ou compétences que tu possèdes vraiment.
Si tu bloques, demande-toi : "Qu'est-ce que mes collègues ou proches apprécient chez moi ?"

Étape 2 — Valider par exemples (2 min)
Pour chacune des 5 qualités, associe un exemple professionnel récent où tu l'as exprimée.

Étape 3 — Ancrage
Formule : "Mes forces sont réelles. Elles se manifestent concrètement dans mon travail."
Conserve cette liste et relis-la avant une situation stressante.
EOT,
            ],

            // ─── CATÉGORIE : ASSERTIVITÉ COMPORTEMENTALE ─────────────────────

            [
                'id'               => 'SE-05',
                'title'            => 'Le refus assertif — dire non avec dignité',
                'category'         => 'assertivite',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Alberti & Emmons (2008) — droit assertif de refuser sans culpabilité',
                'scoring'          => ['dimension' => 'assertivite_comportementale', 'weight' => 1.3],
                'instructions'     => <<<EOT
Contexte de l'exercice : ton responsable te demande d'assumer une charge de travail supplémentaire alors que tu es déjà surchargé(e).

Étape 1 — Reconnaître l'émotion (1 min)
Identifie ce que tu ressens face à cette demande : inconfort, culpabilité anticipée, peur du jugement ? Nomme-le sans le fuir.

Étape 2 — Formuler le refus assertif (2 min)
Utilise la structure : Fait + Impact + Refus clair + Alternative (optionnelle).
Exemple : "Je comprends l'urgence de ce projet. En ce moment, j'ai déjà ___ dossiers prioritaires qui occupent l'essentiel de ma capacité. Je ne peux pas prendre en charge ceci en plus sans impacter la qualité. Je peux en revanche te proposer ___ [alternative]."

Étape 3 — Répétition à voix haute (2 min)
Prononce cette phrase à voix haute devant un miroir ou en te déplaçant.
Note : la voix doit être ferme, le débit régulier, sans s'excuser à l'excès.
EOT,
            ],

            [
                'id'               => 'SE-06',
                'title'            => 'Le disque rayé — tenir sa position face à l\'insistance',
                'category'         => 'assertivite',
                'duration_minutes' => 5,
                'difficulty'       => 3,
                'scientific_basis' => 'Smith (1975) — technique du disque rayé, résistance à la manipulation',
                'scoring'          => ['dimension' => 'assertivite_comportementale', 'weight' => 1.4],
                'instructions'     => <<<EOT
Contexte : un collègue insiste pour que tu modifies ton analyse après une réunion, bien que tu sois convaincu(e) de sa justesse.

Étape 1 — Comprendre la technique (1 min)
Le disque rayé consiste à répéter calmement et fermement ta position sans escalade émotionnelle, quelle que soit la pression exercée. Ce n'est pas de l'entêtement : tu restes ouvert(e) à de vrais arguments nouveaux, mais tu résistes à la simple pression sociale.

Étape 2 — Formulation de base (1 min)
Crée ta phrase pivot. Exemple : "Je comprends que tu aies une autre perspective. Mon analyse repose sur ___ [données/méthode]. Je maintiens ma conclusion."

Étape 3 — Simulation d'insistance (3 min)
Imagine 3 formes d'insistance progressives :
  • "Mais tout le monde pense comme moi." → Répète ta phrase pivot.
  • "Tu es vraiment sûr(e) de toi ?" → Même réponse, même ton calme.
  • "Je te demande juste de reconsidérer." → "J'entends ta demande. Ma position reste la même pour les raisons évoquées."
EOT,
            ],

            [
                'id'               => 'SE-07',
                'title'            => 'Formuler une critique constructive',
                'category'         => 'assertivite',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Alberti & Emmons (2008) — assertivité positive, feedback non agressif',
                'scoring'          => ['dimension' => 'assertivite_comportementale', 'weight' => 1.2],
                'instructions'     => <<<EOT
Contexte : un membre de ton équipe remet régulièrement ses livrables en retard, impactant ton propre travail.

Étape 1 — Préparer le message (2 min)
Utilise la structure DESC :
  • D (Décrire) : "Lors des 3 dernières semaines, tes livrables sont arrivés après la date convenue."
  • E (Exprimer) : "Cela crée pour moi un stress important et retarde mes propres échéances."
  • S (Spécifier) : "J'ai besoin que les prochains livrables arrivent la veille au soir de la deadline."
  • C (Conséquences positives) : "Cela nous permettrait de livrer ensemble un travail de meilleure qualité."

Étape 2 — Répétition mentale (2 min)
Visualise la conversation. Imagine la réaction possible de l'autre (défensivité, excuses, accord). Prépare une réponse calme pour chaque scénario.

Étape 3 — Ancrage du ton (1 min)
La critique assertive n'est ni agressive ni apologétique. Ton ton doit être celui d'un partenaire qui résout un problème, pas d'un juge.
EOT,
            ],

            [
                'id'               => 'SE-08',
                'title'            => 'Accepter un compliment avec grâce',
                'category'         => 'assertivite',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => 'Alberti & Emmons (2008) — assertivité réceptive, droit à la reconnaissance',
                'scoring'          => ['dimension' => 'assertivite_comportementale', 'weight' => 0.9],
                'instructions'     => <<<EOT
Étape 1 — Identifier ton réflexe habituel (1 min)
Quand quelqu'un te complimente professionnellement, que fais-tu typiquement ?
  • Minimiser : "C'était rien..."
  • Dévier : "C'est toute l'équipe en fait."
  • Fuir : changer de sujet immédiatement.

Étape 2 — Pratiquer la réponse assertive (2 min)
La réponse assertive à un compliment est simple, directe et sans excès :
"Merci, j'ai effectivement travaillé sérieusement sur ce point. Je suis content(e) que ça se voie."

Entraîne-toi à dire cette phrase à voix haute 5 fois. Remarque la résistance intérieure éventuelle et laisse-la s'atténuer à chaque répétition.
EOT,
            ],

            // ─── CATÉGORIE : GESTION DU REGARD ───────────────────────────────

            [
                'id'               => 'SE-09',
                'title'            => 'Défusion cognitive — séparer soi du regard des autres',
                'category'         => 'confiance',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Hayes (2004) — Thérapie d\'Acceptation et d\'Engagement (ACT), défusion cognitive',
                'scoring'          => ['dimension' => 'gestion_du_regard', 'weight' => 1.3],
                'instructions'     => <<<EOT
Étape 1 — Identifier la pensée liée au regard (1 min)
Rappelle-toi une situation récente où tu as modifié ton comportement par peur du jugement.
Exemple : "Je n'ai pas posé ma question en réunion de peur de paraître incompétent."

Étape 2 — Nommer la pensée comme pensée (2 min)
Au lieu de "Je suis incompétent", formule : "J'ai la pensée que je pourrais sembler incompétent."
Cette distance linguistique réduit la fusion avec la pensée.

Étape 3 — Tester la réalité du risque (2 min)
Pose-toi ces questions :
  • Quelle est la probabilité réelle que tout le monde m'ait jugé négativement ?
  • Qu'auraient pensé les 20% de personnes les plus bienveillantes dans la salle ?
  • Quelle aurait été la conséquence concrète d'un jugement négatif isolé ?

Note souvent : le risque perçu est massivement surestimé.
EOT,
            ],

            [
                'id'               => 'SE-10',
                'title'            => 'Exposition graduelle — intervention en réunion',
                'category'         => 'assertivite',
                'duration_minutes' => 5,
                'difficulty'       => 3,
                'scientific_basis' => 'Wolpe (1958) — exposition graduelle, désensibilisation systématique',
                'scoring'          => ['dimension' => 'gestion_du_regard', 'weight' => 1.4],
                'instructions'     => <<<EOT
Étape 1 — Construire l'échelle d'exposition (2 min)
Classe ces situations de la moins à la plus difficile pour toi :
  N1 : Poser une question de clarification factuelle à la fin d'une réunion à 2 personnes.
  N2 : Donner ton avis sur un point précis lors d'une réunion d'équipe à 5 personnes.
  N3 : Être en désaccord poliment avec une affirmation lors d'une réunion à 10 personnes.
  N4 : Présenter spontanément une idée nouvelle lors d'un CODIR.

Étape 2 — Engagement sur le niveau suivant (2 min)
Identifie ton niveau actuel de confort. Engage-toi mentalement (et si possible par écrit) à tenter le niveau suivant lors de ta prochaine réunion.

Étape 3 — Préparation de la phrase d'amorce (1 min)
Prépare une phrase d'entrée générique :
"J'aimerais ajouter quelque chose à ce sujet..." / "Je me demande si..." / "Mon point de vue sur cela est..."
EOT,
            ],

            [
                'id'               => 'SE-11',
                'title'            => 'Visualisation de succès assertif',
                'category'         => 'roleplay',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Bandura (1997) — expériences vicariantes et simulation mentale comme source d\'auto-efficacité',
                'scoring'          => ['dimension' => 'gestion_du_regard', 'weight' => 1.1],
                'instructions'     => <<<EOT
Étape 1 — Choisir une situation cible (1 min)
Identifie une situation professionnelle prochaine qui t'intimide (entretien, confrontation, prise de parole).

Étape 2 — Visualisation filmique (3 min)
Ferme les yeux. Imagine la scène en détail :
  • L'environnement (salle, personnes présentes, heure).
  • Toi : posture droite, voix assurée, contact visuel franc.
  • Tu prends la parole ou tu poses ta limite. Les autres t'écoutent.
  • La situation se déroule avec succès. Tu ressens de la satisfaction.

Répète la visualisation 2-3 fois, en variant légèrement les conditions pour renforcer la généralisation.

Étape 3 — Ancrage sensoriel (1 min)
À la fin de la visualisation réussie, crée un ancrage physique (main sur la poitrine, pression du pouce et de l'index). Tu pourras l'utiliser avant la situation réelle.
EOT,
            ],

            // ─── CATÉGORIE : EXPRESSION DES BESOINS ──────────────────────────

            [
                'id'               => 'SE-12',
                'title'            => 'Identifier ses besoins non exprimés',
                'category'         => 'communication',
                'duration_minutes' => 5,
                'difficulty'       => 1,
                'scientific_basis' => 'Rosenberg (2003) — CNV, niveau "besoins" du processus en 4 étapes',
                'scoring'          => ['dimension' => 'expression_des_besoins', 'weight' => 1.2],
                'instructions'     => <<<EOT
Étape 1 — Choisir une frustration récente (1 min)
Identifie une situation de travail récente qui t'a frustré ou mis mal à l'aise.
Exemple : une réunion dans laquelle ton expertise n'a pas été reconnue.

Étape 2 — Descendre jusqu'au besoin (3 min)
Pose-toi successivement :
  1. "Qu'est-ce que j'ai ressenti ?" (émotion) → Frustration, déception.
  2. "Qu'est-ce que je voulais qui ne s'est pas passé ?" (besoin non satisfait) → Être reconnu(e) pour ma contribution.
  3. "Quel besoin universel est derrière ça ?" (besoin profond) → Reconnaissance, appartenance, sens.

Étape 3 — Formuler la demande CNV (1 min)
Traduis ce besoin en demande concrète, positive, actionnable et adressée à une personne précise :
"La prochaine fois que je partage une analyse, j'aimerais que tu me fasses un retour, même bref."
EOT,
            ],

            [
                'id'               => 'SE-13',
                'title'            => 'Message "Je" — exprimer sans accuser',
                'category'         => 'communication',
                'duration_minutes' => 4,
                'difficulty'       => 2,
                'scientific_basis' => 'Rosenberg (2003) — CNV, observation factuelle sans jugement ni interprétation',
                'scoring'          => ['dimension' => 'expression_des_besoins', 'weight' => 1.3],
                'instructions'     => <<<EOT
Étape 1 — Identifier une tension relationnelle (1 min)
Pense à une situation où tu as ressenti une tension avec un collègue mais ne l'as pas exprimée (ou l'as mal exprimée).

Étape 2 — Construire le message "Je" (2 min)
Structure en 4 temps :
  • OBSERVATION (faits neutres, sans jugement) : "Quand tu interromps mes présentations avant que j'aie terminé..."
  • SENTIMENT (émotion en "je") : "...je me sens interrompu(e) et frustré(e)..."
  • BESOIN : "...parce que j'ai besoin que mes idées soient entendues en entier."
  • DEMANDE (concrète, positive) : "Est-ce que tu peux laisser chaque intervenant terminer son propos avant de réagir ?"

Étape 3 — Révision (1 min)
Vérifie que ton message ne contient aucun "tu" accusateur. S'il y en a, reformule.
EOT,
            ],

            [
                'id'               => 'SE-14',
                'title'            => 'Demander de l\'aide sans se dévaloriser',
                'category'         => 'communication',
                'duration_minutes' => 3,
                'difficulty'       => 1,
                'scientific_basis' => 'Alberti & Emmons (2008) — droit assertif à demander de l\'aide',
                'scoring'          => ['dimension' => 'expression_des_besoins', 'weight' => 1.0],
                'instructions'     => <<<EOT
Étape 1 — Identifier la demande d'aide évitée (1 min)
Pense à une situation récente où tu aurais eu besoin d'aide mais ne l'as pas demandée par crainte de paraître faible ou incompétent(e).

Étape 2 — Reformuler en mode assertif (2 min)
La demande d'aide assertive est directe, contextuelle et ne s'excuse pas d'exister.
Mauvais modèle : "Désolé de te déranger, c'est peut-être stupide mais... je comprendrais si tu n'as pas le temps..."
Bon modèle : "J'aurais besoin de ton expertise sur ce point précis. Aurais-tu 10 minutes cette semaine pour en parler ?"

Pratique la formulation à voix haute jusqu'à ce qu'elle te semble naturelle.
EOT,
            ],

            [
                'id'               => 'SE-15',
                'title'            => 'Négocier une demande déraisonnable',
                'category'         => 'communication',
                'duration_minutes' => 5,
                'difficulty'       => 3,
                'scientific_basis' => 'Fisher & Ury (1981) — négociation raisonnée, intérêts vs positions',
                'scoring'          => ['dimension' => 'expression_des_besoins', 'weight' => 1.4],
                'instructions'     => <<<EOT
Contexte : ton responsable te demande de remettre un rapport complet pour demain matin alors qu'il t'a été assigné aujourd'hui à 16h.

Étape 1 — Nommer les contraintes sans plainte (1 min)
"Je viens de recevoir la demande. Il est 16h. Pour un rapport complet de cette nature, j'estime avoir besoin de ___ heures de travail sérieux."

Étape 2 — Proposer une alternative négociée (2 min)
"Je peux te remettre une version solide demain à midi, ou une synthèse exécutive demain à 9h si l'essentiel est la décision à prendre. Quelle est la vraie contrainte de ta côté ?"

Étape 3 — Entraînement mental (2 min)
Répète cet échange dans ta tête. Remarque : tu ne refuses pas, tu négocies avec un souci de qualité partagée. Ton interlocuteur a généralement un besoin sous-jacent (décision à prendre, réunion à 10h) — adresse ce besoin réel.
EOT,
            ],

            // ─── CATÉGORIE : RÉSILIENCE IDENTITAIRE ──────────────────────────

            [
                'id'               => 'SE-16',
                'title'            => 'Répondre à une critique injuste avec calme',
                'category'         => 'roleplay',
                'duration_minutes' => 5,
                'difficulty'       => 3,
                'scientific_basis' => 'Linehan (1993) — régulation émotionnelle DBT, tolérance à la détresse',
                'scoring'          => ['dimension' => 'resilience_identitaire', 'weight' => 1.4],
                'instructions'     => <<<EOT
Contexte : lors d'une réunion, un manager te critique publiquement de façon injuste ou exagérée sur ton travail.

Étape 1 — Stabilisation immédiate (1 min)
Avant de répondre, utilise la technique STOP :
  S — Stop : ne parle pas immédiatement.
  T — Take a breath : inspire profondément.
  O — Observe : que ressens-tu ? (colère, honte, sidération ?)
  P — Proceed : choisis consciemment ta réponse.

Étape 2 — Réponse assertive non défensive (2 min)
Choisir l'une des formulations suivantes selon le contexte :
  • Demande de précision : "Peux-tu me donner un exemple précis de ce que tu décris ? Je veux comprendre correctement."
  • Désaccord calme : "Je ne partage pas cette évaluation. Voici ma lecture des faits : ___."
  • Report : "Ce point mérite une discussion approfondie. Je préfère qu'on en parle en dehors de cette réunion."

Étape 3 — Après la réunion — traitement de l'émotion (2 min)
Note pour toi-même : quelle était la part de critique fondée, quelle était la part injuste ? Que retiens-tu pour progresser sans intégrer l'attaque injuste comme vérité ?
EOT,
            ],

            [
                'id'               => 'SE-17',
                'title'            => 'Ancre identitaire — qui je suis au-delà des avis',
                'category'         => 'confiance',
                'duration_minutes' => 5,
                'difficulty'       => 2,
                'scientific_basis' => 'Harris (2009) — ACT, valeurs comme boussole identitaire',
                'scoring'          => ['dimension' => 'resilience_identitaire', 'weight' => 1.3],
                'instructions'     => <<<EOT
Étape 1 — Clarifier ses valeurs professionnelles (2 min)
Nomme 3 valeurs qui guident vraiment ton travail (pas celles que tu "devrais" avoir).
Exemples : rigueur, honnêteté, créativité, solidarité, excellence, liberté, impact.

Étape 2 — Construire l'ancre identitaire (2 min)
Formule une phrase d'identité stable, fondée sur tes valeurs et non sur l'approbation externe :
"Je suis quelqu'un qui ___ [valeur 1], ___ [valeur 2] et ___ [valeur 3], quoi qu'en pensent les autres dans ce moment."

Étape 3 — Test de robustesse (1 min)
Imagine une critique virulente à ton égard. Relis ta phrase d'ancre. Observe : est-ce que la critique remet en cause tes valeurs, ou seulement ton action dans cette situation ?
EOT,
            ],

            [
                'id'               => 'SE-18',
                'title'            => 'Tolérance au désaccord — rester soi face à la pression',
                'category'         => 'roleplay',
                'duration_minutes' => 5,
                'difficulty'       => 3,
                'scientific_basis' => 'Smith (1975) — droit assertif au désaccord ; Bowlby (1988) — sécurité intérieure',
                'scoring'          => ['dimension' => 'resilience_identitaire', 'weight' => 1.3],
                'instructions'     => <<<EOT
Contexte : l'ensemble de ton équipe est d'accord sur une décision que tu penses erronée. La pression de conformité est forte.

Étape 1 — Nommer le phénomène (1 min)
Reconnais intérieurement : "Je ressens la pression de me conformer. C'est une réaction normale. Ce n'est pas une raison de trahir mon jugement si je suis fondé(e)."

Étape 2 — Tester son propre jugement (2 min)
Pose-toi honnêtement :
  • Ai-je des informations ou une perspective que les autres n'ont pas ?
  • Mon désaccord est-il fondé sur des faits, ou sur une résistance émotionnelle ?
  Si fondé → Étape 3. Si émotionnel → reformule ta position intérieure.

Étape 3 — Exprimer le désaccord respectueusement (2 min)
"Je comprends que la majorité pense X. Mon analyse me conduit à une conclusion différente pour les raisons suivantes : ___. Je préfère qu'on en tienne compte avant de conclure."
EOT,
            ],

            [
                'id'               => 'SE-19',
                'title'            => 'Deuil de la perfection — avantageux d\'être imparfait',
                'category'         => 'confiance',
                'duration_minutes' => 4,
                'difficulty'       => 2,
                'scientific_basis' => 'Brown (2010) — vulnérabilité comme force ; Ellis (1962) — REBT, irrationalité du perfectionnisme',
                'scoring'          => ['dimension' => 'resilience_identitaire', 'weight' => 1.1],
                'instructions'     => <<<EOT
Étape 1 — Identifier une attente perfectionniste (1 min)
Note une exigence professionnelle irréaliste que tu t'imposes.
Exemple : "Je dois toujours savoir la réponse immédiatement."

Étape 2 — Explorer le coût du perfectionnisme (2 min)
Que t'a coûté concrètement cette exigence ?
  • Temps perdu à sur-préparer ?
  • Opportunités manquées par peur d'échouer ?
  • Stress chronique ?

Étape 3 — Reformuler en standard élevé mais humain (1 min)
"Je vise l'excellence, pas la perfection. Il m'est permis de ne pas savoir, de me tromper et d'apprendre. C'est ainsi que je progresse."
EOT,
            ],

            [
                'id'               => 'SE-20',
                'title'            => 'Jeu de rôle complet — situation difficile intégrée',
                'category'         => 'roleplay',
                'duration_minutes' => 5,
                'difficulty'       => 3,
                'scientific_basis' => 'Bandura (1997) — performances accomplie par répétition comportementale ; Moreno (1953) — psychodrame',
                'scoring'          => [
                    'dimension' => 'assertivite_comportementale',
                    'weight'    => 1.5,
                    'secondary' => ['resilience_identitaire', 'expression_des_besoins'],
                ],
                'instructions'     => <<<EOT
Étape 1 — Choisir la situation la plus difficile (1 min)
Parmi les situations suivantes, identifie celle qui te semble la plus difficile en ce moment :
  A. Demander une augmentation ou une évolution de poste.
  B. Signaler à ton manager que sa décision est contre-productive.
  C. Mettre fin à une relation professionnelle toxique.
  D. Te présenter comme expert(e) lors d'une réunion avec des inconnus.

Étape 2 — Scénarisation détaillée (2 min)
Pour la situation choisie, écris (ou pense) :
  • Ce que tu vas dire en ouverture (30 secondes).
  • La réaction probable de l'autre.
  • Ta réponse si la situation dérape vers la pression ou la confrontation.

Étape 3 — Répétition à voix haute (2 min)
Joue la scène à voix haute, seul(e). Si possible, enregistre-toi et réécoute.
Évalue sur 3 critères :
  ✓ Clarté du message
  ✓ Ton ferme et respectueux
  ✓ Absence d'excuses excessives
EOT,
            ],

        ];
    }

    /**
     * Retourne les métadonnées des 5 dimensions d'assertivité évaluées.
     */
    public static function dimensionsLabels(): array
    {
        return [
            'estime_de_soi' => [
                'label' => 'Estime de soi',
                'desc'  => 'Valeur personnelle perçue, fondement de l\'action assertive.',
                'color' => 'var(--pt-navy)',
                'icon'  => '⭐',
            ],
            'assertivite_comportementale' => [
                'label' => 'Assertivité comportementale',
                'desc'  => 'Capacité à s\'affirmer dans les situations concrètes sans agressivité ni passivité.',
                'color' => 'var(--pt-gold)',
                'icon'  => '💬',
            ],
            'gestion_du_regard' => [
                'label' => 'Gestion du regard des autres',
                'desc'  => 'Rapport à l\'opinion des autres, autonomie face au jugement.',
                'color' => '#2563eb',
                'icon'  => '👁️',
            ],
            'expression_des_besoins' => [
                'label' => 'Expression des besoins',
                'desc'  => 'Capacité à identifier et formuler ses besoins de façon constructive (CNV).',
                'color' => '#16a34a',
                'icon'  => '🗣️',
            ],
            'resilience_identitaire' => [
                'label' => 'Résilience identitaire',
                'desc'  => 'Stabilité de l\'identité face aux critiques, aux pressions et aux désaccords.',
                'color' => '#7c3aed',
                'icon'  => '🛡️',
            ],
        ];
    }

    /**
     * Retourne les exercices filtrés par catégorie.
     */
    public static function byCategory(string $category): array
    {
        return array_values(
            array_filter(self::all(), fn ($e) => $e['category'] === $category)
        );
    }

    /**
     * Retourne les exercices filtrés par dimension de scoring principale.
     */
    public static function byDimension(string $dimension): array
    {
        return array_values(
            array_filter(self::all(), fn ($e) => $e['scoring']['dimension'] === $dimension)
        );
    }

    /**
     * Retourne les exercices recommandés pour une dimension faible.
     * Triés par weight décroissant, limités aux N meilleurs.
     */
    public static function recommendedFor(string $dimension, int $limit = 3): array
    {
        $exercises = self::byDimension($dimension);
        usort($exercises, fn ($a, $b) => $b['scoring']['weight'] <=> $a['scoring']['weight']);
        return array_slice($exercises, 0, $limit);
    }
}
