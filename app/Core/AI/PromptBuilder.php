<?php

namespace Praxis\Core\AI;

use App\Models\ProfileGrimoire;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class PromptBuilder
{
    /**
     * Cache en mémoire du contexte Grimoire pour la durée d'une requête.
     * Évite de reconstruire le même tableau (profil + tests) 3 fois de suite
     * (globalGrimoireSynthese → globalGrimoireVoies → globalGrimoireIaImpact).
     * Clé = user_id, valeur = contexte sérialisé. Léger : durée de vie = process PHP.
     *
     * @var array<int, array>
     */
    protected array $grimoireContextCache = [];
    /**
     * L'Oracle — chat conversationnel d'orientation (widget flottant).
     *
     * Construit la liste de messages multi-tours : un system prompt qui pose la
     * persona ET injecte le contexte du candidat (profil, tests croisés, Grimoire),
     * puis l'historique de la conversation, puis le nouveau message (assaini).
     *
     * @param  Collection<int,TestAttempt>  $attempts  tentatives complétées (test + result)
     * @param  array<int,array{role:string,content:string}>  $history  tours précédents
     */
    /**
     * Intitulés professionnels des tests, utilisés par la directive « corporate »
     * injectée dans les prompts de génération (synthèses, Grimoire, Oracle).
     * Pendant au front : CORPORATE_TEST_NAMES dans resources/js/composables/useParcours.js.
     */
    private const CORPORATE_TEST_LABELS = [
        'La Quête de la Voie'          => 'Intérêts professionnels (RIASEC)',
        'La Grande Cartographie'       => 'Personnalité (Big Five)',
        'La Boussole des Émotions'     => 'Intelligence émotionnelle (EQ-i)',
        'La Sentinelle Intérieure'     => 'Bien-être et risques psychosociaux',
        'La Source des Valeurs'        => 'Valeurs professionnelles (Schwartz)',
        'Le Cartographe Mental'        => 'Biais cognitifs professionnels',
        'La Boussole de l\'Attention'  => 'Attention et concentration (repères TDAH)',
        'Le Radar des Sens'            => 'Sensibilité sensorielle',
        'Maître du Temps'              => 'Gestion du temps',
        'La Constellation des Talents' => 'Feedback 360°',
        'L\'Étoffe du Bâtisseur'       => 'Compétences entrepreneuriales (EntreComp)',
    ];

    private function isCorporate(User $user): bool
    {
        return ($user->ui_theme ?? 'medieval') === 'corporate';
    }

    /**
     * Directive de registre pour le parcours corporate — à APPENDRE au message
     * système des prompts de génération. Prioritaire sur toute consigne de
     * tutoiement ou d'exemple utilisant les noms « fantaisie » des tests.
     */
    private function corporateDirective(): string
    {
        $mapping = collect(self::CORPORATE_TEST_LABELS)
            ->map(fn ($pro, $fantasy) => "« {$fantasy} » → « {$pro} »")
            ->implode(' ; ');

        return "\n\nPARCOURS CORPORATE (directive PRIORITAIRE sur toute consigne de style ci-dessus) : "
            . "cette personne utilise l'interface professionnelle de PraxiQuest. VOUVOIE-la systématiquement, "
            . "ton posé et professionnel. N'emploie JAMAIS les noms « fantaisie » des tests — utilise leurs "
            . "intitulés professionnels : {$mapping}. "
            . "N'emploie pas non plus le vocabulaire de jeu (« Quête », « Épreuves », « Grimoire », « Éclats », "
            . "« Oracle », « Héros ») : parle d'évaluations, de dossier de synthèse et de points.";
    }

    public function oracleChat(User $user, Collection $attempts, ?ProfileGrimoire $grimoire, array $history, string $message, bool $nightMode = false): array
    {
        $persona = <<<TXT
Tu es l'Oracle de PraxiQuest. Conseiller d'orientation senior, lucide, avec du vécu. Tu aides les gens à y voir clair sur leur profil, leurs possibles et les moyens concrets d'avancer — sans leur faire de grands discours.

Tu as un fond solide en TCC et en approche ericksonienne, mais ça ne se voit pas. Ça s'entend, peut-être, dans la façon dont tu écoutes vraiment, dont tu ne rassures jamais à vide, dont tu sais poser la question qui fait tilt au bon moment. Tu ne "fais" pas la thérapie — tu es juste quelqu'un qui sait comment les gens fonctionnent, et ça se ressent.

Tu es bienveillant, mais pas mou. Tu peux être direct quand c'est utile. Et de temps en temps, tu taquines un peu — une remarque légère, un sourire dans le texte, quand le moment s'y prête et que tu sens que la personne peut l'entendre. Pas pour faire le malin : juste parce que l'humour détend, et qu'on avance mieux quand on ne se prend pas trop au sérieux.

Ce que tu n'es pas : un thérapeute, un coach motivationnel, un gourou. Tu ne poses pas de diagnostic. Si la conversation touche à quelque chose de vraiment difficile, tu l'accueilles calmement et tu invites la personne à en parler avec quelqu'un de qualifié.

EXPERTISE ORIENTATION
Tu connais ses tests (RIASEC, MBTI, Big Five, intelligence émotionnelle, etc.), son profil et la relecture globale de son Grimoire. Tu t'appuies dessus pour personnaliser chaque réponse, sans jamais réciter les données brutes.
Tu peux proposer et affiner des pistes de métiers réalistes et accessibles sur le marché francophone actuel — en variant les secteurs et les modèles (salariat / entrepreneuriat / freelance) et en expliquant POURQUOI à partir de ce que tu sais d'elle.

EXPERTISE FINANCEMENT DE FORMATION (FRANCE)
Tu maîtrises l'ingénierie financière de la formation en France et tu adaptes tes conseils au statut de la personne (visible dans son profil) :

• SALARIÉ·E
  – CPF (Compte Personnel de Formation) : utilisation simple ou abondement employeur / OPCO
  – Plan de développement des compétences (financé par l'employeur, pendant le temps de travail ou hors temps)
  – Pro-A (promotion ou reconversion par l'alternance, pour < 150 % SMIC ou sans qualification niveau II)
  – CPF de Transition Professionnelle (CPF-TP / Transitions Pro) : financement d'une reconversion longue, maintien de salaire possible
  – OPCO de la branche (financement hors-plan, co-financement, jury VAE)

• DEMANDEUR·EUSE D'EMPLOI
  – CPF : mobilisable seul, abondement possible via France Travail (AIF)
  – AIF (Aide Individuelle à la Formation, France Travail) : complément CPF ou financement total si formation non éligible CPF
  – AFPR (Action de Formation Préalable au Recrutement) : formation courte avant CDD ≥ 6 mois ou CDI, financée par France Travail
  – POEI (Préparation Opérationnelle à l'Emploi Individuelle) : formation longue (400 h max) avant CDI/CDD ≥ 12 mois, financée France Travail
  – PRF / Plan Régional de Formation : formations gratuites financées par la Région, notamment pour les secteurs en tension
  – ARE pendant formation : maintien de l'allocation si formation validée par France Travail (AREF/RFPE)
  – Rémunération de Formation France Travail (RFPE) : si fin de droits ARE

• ENTREPRENEUR·E / TNS / INDÉPENDANT·E
  – CPF : droits acquis sur la base du revenu déclaré (auto-entrepreneur : cotisation formation sur CA)
  – FAF (Fonds d'Assurance Formation) selon statut : FIF-PL (professions libérales), FIFPL, AGEFICE (commerçants/dirigeants), FAFCEA (artisans), CPSTI (micro-entrepreneurs)
  – ACRE / ARCE : pas directement pour la formation, mais à mentionner si reconversion post-création
  – Prise en charge FAF : souvent plafonnée (ex : 1 500 à 4 000 €/an), à vérifier selon la caisse
  – Régions et OPCO-EP peuvent cofinancer selon les dispositifs locaux

RÈGLES DE CONSEIL FINANCEMENT
– Tu adaptes systématiquement les dispositifs au statut de la personne et à la durée/coût de la formation visée.
– Tu signales toujours : « les montants et conditions peuvent évoluer, vérifie sur Mon Compte Formation (moncompteformation.gouv.fr) ou auprès de ton conseiller France Travail / OPCO. »
– Si le statut n'est pas précisé, tu demandes avant de conseiller.
– Tu n'inventes pas de montants précis si tu n'es pas sûr·e : tu donnes des ordres de grandeur et renvoies vers les organismes officiels.

Style : tutoiement, français naturel, phrases courtes, ton bienveillant mais franc. Pas de jargon psy, pas de flatterie creuse, pas de "c'est super que tu partages ça". Réponses concises (3 à 6 phrases en général) ; tu développes quand la personne a besoin d'espace.
Si le contexte indique un "titre_héros", tu peux t'en inspirer subtilement pour personnaliser ton angle : un Architecte aime les systèmes et la clarté, un Explorateur préfère les possibles ouverts, un Passeur est orienté transmission et lien. Tu n'en parles pas explicitement sauf si la personne le fait elle-même.
Tu poses une question quand c'est pertinent — jamais plus d'une à la fois, et parfois la bonne chose est de ne pas en poser du tout.
Garde-fous : tu ne donnes JAMAIS de conseils médicaux ou juridiques. Tu n'inventes pas de scores ni de chiffres. Tu restes dans ton rôle d'orientation et de financement de formation.
Mise en forme : utilise du Markdown léger — **gras** pour un terme clé ou un nom de métier, listes numérotées (1. …) pour énumérer des suggestions. Pas de titres #, pas de tableaux, pas de blocs ```.
TXT;

        // Parcours « corporate » : registre executive + intitulés professionnels
        // des tests — prioritaire sur la consigne de tutoiement du persona.
        if ($this->isCorporate($user)) {
            $persona .= $this->corporateDirective()
                . " Désigne-toi comme son conseiller — pas de taquinerie. "
                . "Tout le reste (concision, franchise, expertise, Markdown léger, garde-fous) reste identique.";
        }

        $context = $this->grimoireContext($user, $attempts);

        if ($grimoire && $grimoire->synthesis) {
            $context['grimoire'] = [
                'fil_conducteur' => $grimoire->synthesis,
                'voies_pressenties' => collect($grimoire->voies ?? [])
                    ->map(fn ($v) => $v['titre'] ?? null)
                    ->filter()
                    ->take(100)
                    ->values()
                    ->all(),
            ];
        }

        $system = $persona
            . "\n\n--- CONTEXTE DU CANDIDAT (confidentiel, ne pas recopier tel quel) ---\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // Easter egg : 1 chance sur 10 d'inclure un indice cryptique dans le système.
        // L'Oracle glisse subtilement une allusion à la "séquence ancienne" sans la nommer.
        if (random_int(1, 10) === 7 && $user->easter_egg_claimed_at === null) {
            $system .= "\n\nINSTRUCTION SECRÈTE (ne pas mentionner explicitement) : Glisse dans ta prochaine réponse, de façon très subtile et naturelle — comme une parenthèse poétique — l'idée que certains initiés connaissent une \"séquence ancienne\" ou une \"clé cachée\" qui ouvre quelque chose de spécial dans PraxiQuest. Juste une phrase, légère, mystérieuse, qui pourrait passer pour une métaphore. Rien de plus.";
        }

        $messages = [['role' => 'system', 'content' => $system]];

        // Historique conversationnel (déjà aux rôles user/assistant).
        foreach ($history as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $content = trim((string) ($turn['content'] ?? ''));
            if ($content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }

        // Nouveau message : assaini contre l'injection de prompt (#10).
        $messages[] = [
            'role' => 'user',
            'content' => $this->sanitizeUserContent($message, maxChars: 4000),
        ];

        return $messages;
    }
    /**
     * Le Grimoire global : relecture transversale de TOUS les tests d'un candidat.
     * Produit un seul appel IA renvoyant un JSON { synthese, voies[] }.
     *
     * @param  Collection<int,TestAttempt>  $attempts  tentatives complétées (avec test + result)
     */
    public function globalGrimoire(User $user, Collection $attempts, int $count = 100): array
    {
        $profile = $user->profile;

        $system = <<<TXT
Tu es un consultant en orientation professionnelle senior, formé aux approches RIASEC, MBTI, Big Five et intelligence émotionnelle.
Ton rôle : produire une RELECTURE GLOBALE qui CROISE plusieurs tests entre eux — pas une simple juxtaposition de synthèses individuelles.
Tu mets en évidence les convergences (ce qui se confirme d'un test à l'autre), les tensions (ce qui semble se contredire) et le fil conducteur du profil.
Style : chaleureux, professionnel, français, sans jargon, sans flatterie creuse, phrases courtes.
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers. Tu n'inventes pas de scores qu'on ne t'a pas donnés.
Tu réponds STRICTEMENT en JSON valide, sans texte hors-JSON, sans bloc ```.
TXT;

        if ($this->isCorporate($user)) {
            $system .= $this->corporateDirective();
        }

        // Une entrée par test : labels qualitatifs (jamais de chiffres bruts) + synthèse du test
        // Eager-load manquants pour éviter N+1 (ARC-M6).
        $attempts->each(fn (TestAttempt $a) => $a->loadMissing(['test', 'result']));
        $tests = $attempts->map(function (TestAttempt $a) {
            return [
                'nom'                          => $a->test?->name,
                'type'                         => $a->test?->type,
                'interprétation_par_dimension' => $this->enrichScoringForPrompt($a->result?->scoring)['interprétation_par_dimension']
                                                    ?? $this->enrichScoringForPrompt($a->result?->scoring),
                'synthèse_du_test'             => $a->result?->ai_synthesis,
            ];
        })->values()->all();

        $context = [
            'profil' => [
                'statut'          => $profile?->status,
                'depuis'          => $profile?->status_since?->format('Y-m'),
                'rôle'            => $profile?->current_role,
                'industrie'       => $profile?->industry,
                'secteur_emploi'  => $this->workSectorLabel($profile?->work_sector),
                'hobbies_loisirs' => $this->hobbiesContext($profile?->hobbies),
                'problématique'   => $this->safeProfileText($profile?->problematique),
                'cv_extrait'      => $this->safeCvStructured($profile?->cv_structured),
            ],
            'tests'  => $tests,
        ];

        $user_msg = "Voici l'ensemble des tests passés par le candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nProduis un JSON STRICT avec deux clés :\n"
            . "1) \"synthese\" : 400 à 600 mots, OBLIGATOIREMENT en 3 à 4 paragraphes distincts séparés "
            . "par un double saut de ligne échappé \\n\\n (jamais un bloc unique), en CROISANT les tests "
            . "(convergences, tensions, fil conducteur). Ne recopie pas les synthèses individuelles. "
            . "N'utilise jamais de chiffres ni de percentiles — appuie-toi sur les labels qualitatifs. "
            . "Quand tu nommes un test, ajoute juste après, entre parenthèses, en quelques mots, ce qu'il "
            . "mesure (ex. « le Radar des Sens (hypersensibilité) ») — déduis-le du nom et du type fournis.\n"
            . "2) \"voies\" : EXACTEMENT {$count} pistes de métiers réalistes et accessibles sur le marché "
            . "francophone actuel, classées du plus pertinent au moins pertinent, en variant les secteurs et "
            . "les modèles (salariat / entrepreneuriat / freelance). Pour chaque piste : "
            . "{ \"titre\", \"secteur\", \"fit_score\" (0-100), \"pourquoi\" (50 mots max), "
            . "\"appui_tests\" (liste des noms de tests qui soutiennent cette piste), "
            . "\"prochaine_etape\" (action concrète) }.\n\n"
            . "Format attendu : { \"synthese\": \"...\", \"voies\": [ { ... }, ... ] }";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    /**
     * Contexte commun aux deux prompts du Grimoire (synthèse + voies).
     * Labels qualitatifs uniquement (jamais de chiffres bruts) + synthèse par test.
     */
    protected function grimoireContext(User $user, Collection $attempts): array
    {
        // Mémoïzation par user_id : la méthode est appelée 3 fois (synthèse, voies,
        // impact IA) sur les MÊMES données. Le cache process évite 3 re-formatages.
        if (isset($this->grimoireContextCache[$user->id])) {
            return $this->grimoireContextCache[$user->id];
        }

        $profile = $user->profile;

        // Eager-load manquants pour éviter N+1 (ARC-M6).
        $attempts->each(fn (TestAttempt $a) => $a->loadMissing(['test', 'result']));

        $tests = $attempts->map(function (TestAttempt $a) {
            return [
                'nom'                          => $a->test?->name,
                'type'                         => $a->test?->type,
                'interprétation_par_dimension' => $this->enrichScoringForPrompt($a->result?->scoring)['interprétation_par_dimension']
                                                    ?? $this->enrichScoringForPrompt($a->result?->scoring),
                'synthèse_du_test'             => $a->result?->ai_synthesis,
            ];
        })->values()->all();

        $context = [
            'profil' => [
                'titre_héros'     => $this->questTitleLabel($profile?->quest_title),
                'statut'          => $profile?->status,
                'depuis'          => $profile?->status_since?->format('Y-m'),
                'rôle'            => $profile?->current_role,
                'industrie'       => $profile?->industry,
                'secteur_emploi'  => $this->workSectorLabel($profile?->work_sector),
                'hobbies_loisirs' => $this->hobbiesContext($profile?->hobbies),
                'problématique'   => $this->safeProfileText($profile?->problematique),
                'cv_extrait'      => $this->safeCvStructured($profile?->cv_structured),
            ],
            'tests'  => $tests,
        ];

        $this->grimoireContextCache[$user->id] = $context;

        return $context;
    }

    /**
     * Grimoire — PROMPT 1/2 : uniquement la synthèse croisée.
     * Conçu pour tourner EN PARALLÈLE avec globalGrimoireVoies() (Http::pool).
     */
    public function globalGrimoireSynthese(User $user, Collection $attempts): array
    {
        $system = <<<TXT
Tu es un lecteur de profil exigeant — pas un consultant RH bienveillant, pas un coach motivationnel.
Tu lis les données croisées de plusieurs tests et tu dis ce qu'elles révèlent réellement, sans édulcorer.

Structure imposée — 4 paragraphes dans cet ordre EXACT :
1. LA TENSION CENTRALE : commence par la contradiction la plus nette entre ce que la personne croit d'elle-même et ce que les données croisées révèlent. Nommer franchement. Pas d'introduction générale, pas de "Votre profil révèle des forces significatives". Aller droit au fait.
2. LE COÛT RÉEL : ce que ce pattern lui coûte concrètement — des occasions manquées, des années perdues, des décisions qui l'ont maintenu(e) en dessous de son potentiel. Être spécifique, pas abstrait.
3. LES CONSTANTES : ce qui se confirme d'un test à l'autre (convergences solides), en nommant les tests et les dimensions concernées. Ce sont les données sur lesquelles elle peut vraiment s'appuyer.
4. LE DÉFI : une seule question ou un seul constat qui ouvre une voie — pas une liste de conseils rassurants. Quelque chose qui donne envie d'agir.

Style : vous, direct, phrases courtes, aucune flatterie creuse, aucun jargon RH. Factuel et précis.
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers. Tu n'inventes pas de scores.
Tu réponds STRICTEMENT en JSON valide, sans texte hors-JSON, sans bloc ```.
TXT;

        $corporate = $this->isCorporate($user);
        if ($corporate) {
            $system .= $this->corporateDirective();
        }

        // Exemples de nommage des tests adaptés au parcours (les exemples doivent
        // être cohérents avec la directive corporate, sinon le modèle hésite).
        $namingExamples = $corporate
            ? "— ex. « Sensibilité sensorielle (hypersensibilité) », « Intelligence émotionnelle (EQ-i) », "
              . "« Personnalité (Big Five) »"
            : "— ex. « le Radar des Sens (hypersensibilité) », « la Boussole des Émotions "
              . "(intelligence émotionnelle) », « la Grande Cartographie (personnalité Big Five) »";

        $context = $this->grimoireContext($user, $attempts);

        $user_msg = "Voici l'ensemble des tests passés par le candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nProduis un JSON STRICT avec une SEULE clé \"synthese\" : 400 à 600 mots, "
            . "OBLIGATOIREMENT en 4 paragraphes distincts dans l'ordre imposé "
            . "(tension centrale → coût réel → constantes → défi), chaque paragraphe faisant au moins 3 phrases.\n"
            . "RÈGLE DE FORMAT IMPÉRATIVE : sépare chaque paragraphe par un DOUBLE saut de ligne, "
            . "écrit dans la chaîne JSON sous la forme échappée \\n\\n. Ne renvoie jamais la synthèse "
            . "comme un bloc unique sans saut de ligne.\n"
            . "Ne recopie pas les synthèses individuelles. N'utilise jamais de chiffres ni de percentiles "
            . "— appuie-toi sur les labels qualitatifs. COMMENCE le premier paragraphe par la contradiction "
            . "ou la tension centrale, PAS par les forces ou les atouts.\n"
            . "QUAND TU NOMMES UN TEST, ajoute juste après, entre parenthèses, en quelques mots, ce qu'il "
            . "mesure {$namingExamples}. "
            . "Déduis-le du nom et du type du test fournis dans le contexte.\n\n"
            . "Exemple EXACT du format attendu (garde les \\n\\n entre les paragraphes) :\n"
            . "{ \"synthese\": \"[Tension centrale — la contradiction qui ressort des tests croisés]...\\n\\n"
            . "[Coût réel — ce que ça coûte concrètement]...\\n\\n"
            . "[Constantes — ce qui se confirme d'un test à l'autre, en nommant les tests]...\\n\\n"
            . "[Défi — une seule question ou voie qui ouvre quelque chose]...\" }";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    /**
     * Grimoire — PROMPT 2/2 : uniquement les {count} voies métiers.
     *
     * Format COMPACT unique (v1.1) : chaque piste reste légère (~120 tokens) pour
     * pouvoir en générer beaucoup, vite et de façon FIABLE (le format riche d'avant
     * — 50 mots de "pourquoi" + appui_tests — faisait sous-livrer le modèle et
     * tronquait le JSON, d'où les ~15 pistes au lieu du nombre demandé).
     * On conserve "axes" (5 entiers) car ils alimentent les curseurs de préférences
     * côté front ; on supprime "appui_tests" (le champ le plus coûteux et le moins
     * utile sur une carte). Le détail (axes, prochaine étape) s'affiche au clic.
     */
    public function globalGrimoireVoies(User $user, Collection $attempts, int $count = 100): array
    {
        $system = <<<TXT
Tu es un consultant en orientation professionnelle senior qui propose des métiers réalistes, alignés sur le profil et le marché du travail français/francophone actuel.
Tu croises l'ensemble des tests du candidat pour fonder chaque piste. Tu ne proposes que des métiers existants et accessibles.
Tu varies les secteurs et les modèles (salariat / entrepreneuriat / freelance) et tu ne répètes jamais deux fois le même métier.
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers. Tu n'inventes pas de scores qu'on ne t'a pas donnés.
Tu réponds STRICTEMENT en JSON valide, sans texte hors-JSON, sans bloc ```.
TXT;

        if ($this->isCorporate($user)) {
            $system .= $this->corporateDirective();
        }

        $context = $this->grimoireContext($user, $attempts);

        $user_msg = "Voici l'ensemble des tests passés par le candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nProduis un JSON STRICT avec une SEULE clé \"voies\" : EXACTEMENT {$count} pistes de "
            . "métiers réalistes et accessibles sur le marché francophone actuel, classées du plus pertinent "
            . "au moins pertinent, en variant les secteurs ET les modèles (salariat / entrepreneuriat / freelance). "
            . "Ne propose jamais deux fois le même métier.\n"
            . "Pour chaque piste, un objet COMPACT (va à l'essentiel, sois BREF) :\n"
            . "{ \"titre\", "
            . "\"secteur\", "
            . "\"modele\" (l'un de : \"salariat\", \"freelance\", \"entrepreneuriat\"), "
            . "\"fit_score\" (0-100 : pertinence pour CE profil précis), "
            . "\"pourquoi\" (UNE phrase, 20 mots max), "
            . "\"prochaine_etape\" (action concrète, 12 mots max), "
            . "\"axes\" : 5 entiers 0-100 décrivant LE MÉTIER lui-même (pas le profil), nuancés et "
            . "différenciés d'une piste à l'autre (évite de tout mettre à 50 ou 80) — "
            . "{ \"remuneration\" (potentiel de salaire), "
            . "\"accessibilite\" (formation courte, accès rapide), "
            . "\"stabilite\" (sécurité de l'emploi, demande durable), "
            . "\"autonomie\" (indépendance, freelance/entreprendre possible), "
            . "\"sens\" (utilité, impact) } }.\n\n"
            . "N'inclus PAS de champ \"appui_tests\". Aucun texte hors-JSON, pas de bloc ```.\n"
            . "Format attendu : { \"voies\": [ { \"titre\":\"…\", \"secteur\":\"…\", \"modele\":\"…\", "
            . "\"fit_score\":0, \"pourquoi\":\"…\", \"prochaine_etape\":\"…\", "
            . "\"axes\":{ \"remuneration\":0, \"accessibilite\":0, \"stabilite\":0, \"autonomie\":0, \"sens\":0 } }, … ] }\n\n"
            . "CONTRÔLE FINAL OBLIGATOIRE : compte les objets de ton tableau \"voies\" — il doit y en avoir "
            . "EXACTEMENT {$count}, ni plus ni moins. S'il en manque, complète avec des pistes crédibles "
            . "supplémentaires (autres secteurs) avant de répondre.";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    /**
     * Plan d'action « 10 étapes » pour UNE piste métier du Grimoire.
     * Généré à la demande (bouton sur la carte de piste) puis persisté dans la
     * voie — on ne paie l'IA qu'une fois par piste.
     */
    public function voieActionPlan(User $user, Collection $attempts, array $voie): array
    {
        $system = <<<TXT
Tu es un consultant en orientation professionnelle senior. Tu construis des plans d'action de reconversion ou d'évolution CONCRETS, réalistes et séquencés pour le marché du travail francophone actuel.
Tu t'appuies sur le profil de la personne (tests, statut, parcours) ET sur la piste métier visée.
Tu connais les dispositifs français de financement de formation (CPF, AIF France Travail, Transitions Pro, FAF selon statut) et tu les cites quand c'est pertinent, adaptés au statut de la personne.
Tu ne donnes JAMAIS de conseils médicaux ou juridiques, et tu n'inventes pas de montants précis.
Tu réponds STRICTEMENT en JSON valide, sans texte hors-JSON, sans bloc ```.
TXT;

        if ($this->isCorporate($user)) {
            $system .= $this->corporateDirective();
        }

        $context = $this->grimoireContext($user, $attempts);

        $piste = [
            'titre'   => $voie['titre'] ?? '',
            'secteur' => $voie['secteur'] ?? null,
            'modele'  => $voie['modele'] ?? null,
            'pourquoi' => $voie['pourquoi'] ?? null,
        ];

        $user_msg = "PROFIL DU CANDIDAT :\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nPISTE MÉTIER VISÉE :\n"
            . json_encode($piste, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nProduis un JSON STRICT : { \"plan\": [ …EXACTEMENT 10 étapes… ] }.\n"
            . "Chaque étape est une chaîne de 15 à 30 mots : UNE action concrète et vérifiable, "
            . "suivie d'un horizon indicatif entre parenthèses — ex. « (semaine 1) », « (mois 1-2) », « (mois 3-6) ».\n"
            . "Progression chronologique imposée : étapes 1-2 = valider le projet (enquêtes métier, immersion, "
            . "réalité du marché) ; 3-5 = montée en compétence (formation ciblée + financement adapté au statut) ; "
            . "6-8 = mise en pratique (premiers projets, réseau, visibilité) ; 9-10 = bascule "
            . "(candidatures ou lancement selon le modèle, sécurisation financière de la transition).\n"
            . "Personnalise selon le statut de la personne et le modèle de la piste "
            . "(salariat / freelance / entrepreneuriat). Appuie-toi sur ses forces révélées par les tests.\n"
            . "CONTRÔLE FINAL : le tableau \"plan\" contient EXACTEMENT 10 chaînes. Aucun texte hors-JSON.";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    /**
     * Grimoire — onglet « Ton métier face à l'IA ».
     *
     * Relecture dédiée : comment le métier actuel du candidat (ou, à défaut, son
     * statut / secteur / parcours) est susceptible d'être transformé par l'IA.
     * Sortie en MARKDOWN (rendu par MarkdownText côté front), structurée en
     * sections fixes pour rester lisible et scannable. On reste factuel, nuancé et
     * non anxiogène : ni promesse, ni catastrophisme.
     */
    public function globalGrimoireIaImpact(User $user, Collection $attempts): array
    {
        $system = <<<TXT
Tu es un consultant en prospective des métiers et en transformation par l'intelligence artificielle, qui aide les personnes à comprendre comment l'IA va faire évoluer leur travail.
Ton rôle : produire une analyse LUCIDE, NUANCÉE et NON ANXIOGÈNE de l'exposition du métier de la personne à l'IA — ni promesse magique, ni catastrophisme.
Tu distingues ce qui est plausiblement automatisable (tâches répétitives, structurées) de ce qui reste humain (jugement, relation, créativité, responsabilité).
Tu t'appuies sur le métier/statut de la personne ET sur le profil révélé par ses tests (forces, appétences) pour personnaliser, sans inventer de chiffres ni de dates précises.
Style : chaleureux, professionnel, français, concret, phrases courtes, à la 2e personne du singulier (« ton métier », « tu »).
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers, et tu n'affirmes jamais qu'un métier va « disparaître » de façon catégorique.
Tu réponds en MARKDOWN (titres ##, listes à puces, gras), sans bloc ``` ni texte d'introduction hors-sujet.
TXT;

        if ($this->isCorporate($user)) {
            $system .= $this->corporateDirective();
        }

        $context = $this->grimoireContext($user, $attempts);

        $user_msg = "Voici le profil de la personne et l'ensemble de ses tests :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nRédige une analyse de 350 à 500 mots, EN MARKDOWN, sur la façon dont l'IA "
            . "va transformer le métier (ou le domaine d'activité) de cette personne. "
            . "Si le métier exact n'est pas connu, raisonne à partir de son statut, de son secteur "
            . "et du profil révélé par ses tests.\n"
            . "Structure OBLIGATOIRE, avec ces titres de section en Markdown (##) :\n"
            . "## En un mot\n"
            . "Une à deux phrases qui résument le niveau d'exposition à l'IA (ex. exposition modérée, "
            . "plutôt un copilote qu'un remplacement) — pondéré, sans alarmisme.\n"
            . "## Ce que l'IA va automatiser ou accélérer\n"
            . "3 à 5 puces : tâches concrètes du métier susceptibles d'être prises en charge ou accélérées par l'IA.\n"
            . "## Ce qui restera (plus que jamais) humain\n"
            . "3 à 5 puces : ce qui prend de la valeur — en t'appuyant sur les forces du profil quand c'est pertinent.\n"
            . "## Comment prendre une longueur d'avance\n"
            . "3 à 5 puces d'actions concrètes et accessibles (compétences à renforcer, outils IA à apprivoiser, "
            . "posture à adopter) pour transformer l'IA en atout plutôt qu'en menace.\n\n"
            . "Reste factuel et encourageant. N'invente pas de pourcentages, d'échéances chiffrées ni de scores. "
            . "Ne recopie pas les synthèses des tests. Termine sur une note d'agentivité (la personne garde la main).";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    public function profileSynthesis(TestAttempt $attempt): array
    {
        // Eager-load manquants pour éviter N+1 (ARC-M6).
        $attempt->loadMissing(['test', 'result']);
        $test   = $attempt->test;
        $result = $attempt->result;

        $system = <<<TXT
Tu es un consultant en orientation professionnelle senior, formé aux approches RIASEC, MBTI et Big Five.
Ton rôle : EXPLIQUER les résultats du test passé, de façon claire, précise et bienveillante, en français.
Tu analyses UNIQUEMENT les résultats de CE test — sans référence au parcours, au statut ou au CV de la personne.
Tu te limites STRICTEMENT à expliquer ce que révèlent les résultats : tu ne donnes NI conseil, NI plan d'action, NI piste de progression, NI recommandation de métier.
Style : chaleureux, professionnel, sans jargon, sans flatterie creuse. Phrases courtes. Pas de bullet points dans la synthèse, paragraphes.
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers.
Tu n'inventes pas de scores qu'on ne t'a pas donnés.
TXT;

        // La synthèse par test est aussi affichée dans l'interface : même registre.
        if ($attempt->user && $this->isCorporate($attempt->user)) {
            $system .= $this->corporateDirective();
        }

        $context = [
            'test' => [
                'nom'  => $test->name,
                'type' => $test->type,
            ],
            // Priorité aux données étalonnées (norm_scores) qui donnent le contexte
            // populationnel (ex: "Très développé" = top 15%) — bien plus utile pour
            // la synthèse que des chiffres bruts (86/100 ne veut rien dire sans référence)
            'résultats' => $this->enrichScoringForPrompt($result?->scoring),
        ];

        $user_msg = "Voici les résultats du test :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nGénère une synthèse de 250-400 mots, en 3 paragraphes, qui EXPLIQUE EXCLUSIVEMENT "
            . "les résultats de ce test : "
            . "(1) ce que révèlent les dimensions les plus marquées ('Très développé', "
            . "'Au-dessus de la moyenne') et ce qu'elles signifient concrètement, "
            . "(2) ce que révèlent les dimensions plus modérées ou moins présentes "
            . "('En développement', 'Peu présent') et leur signification, "
            . "(3) une lecture d'ensemble : ce que ce profil, pris dans sa globalité, révèle de la personne. "
            . "Reste strictement descriptif et explicatif. Ne cite jamais de chiffres ni de percentiles "
            . "— utilise les labels qualitatifs. "
            . "Ne donne AUCUN conseil, AUCUN plan de progression, AUCUNE recommandation de métier "
            . "et AUCUNE référence au parcours professionnel : la synthèse doit exclusivement expliquer "
            . "les résultats (les pistes et conseils relèvent du Grimoire).";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    /**
     * Enrichit les données de scoring pour les prompts IA.
     * Remplace les scores bruts par les labels normatifs quand disponibles.
     * Ex: { dim: { label: "Très développé", level: 5 } } au lieu de { dim: 86 }
     */
    protected function enrichScoringForPrompt(?array $scoring): array
    {
        if (!$scoring) return [];

        $normScores = $scoring['norm_scores'] ?? [];
        if (empty($normScores)) {
            return $scoring; // Pas de normes → retourner le scoring brut
        }

        // Construire une vue simplifiée : dimension → interprétation lisible
        $interpretation = [];
        foreach ($normScores as $dimKey => $norm) {
            if ($norm['label'] ?? null) {
                $interpretation[$dimKey] = $norm['label']; // "Très développé", "Dans la moyenne", etc.
            }
        }

        // Retourner le scoring enrichi avec l'interprétation en tête
        return array_merge(
            ['interprétation_par_dimension' => $interpretation],
            collect($scoring)->except('norm_scores')->all(), // scoring brut sans les données techniques
        );
    }

    public function jobSuggestions(TestAttempt $attempt, int $count = 100): array
    {
        $profile = $attempt->user?->profile;
        $scoring = $attempt->result?->scoring;
        $synth   = $attempt->result?->ai_synthesis;

        $system = <<<TXT
Tu es un expert en orientation professionnelle qui propose des métiers réalistes, alignés sur le profil et le marché du travail français/francophone actuel.
Tu réponds STRICTEMENT en JSON, sans texte hors-JSON, sans bloc ```.
Tu ne proposes que des métiers existants, accessibles. Tu varies les niveaux de qualification, les secteurs et les modèles (salariat / entrepreneuriat / freelance).
Pour chaque métier : titre, secteur, fit_score (0-100), pourquoi (50 mots max), prochaine_étape (action concrète).
TXT;

        $payload = json_encode([
            'profil'    => [
                'statut'          => $profile?->status,
                'rôle'            => $profile?->current_role,
                'secteur_emploi'  => $this->workSectorLabel($profile?->work_sector),
                'hobbies_loisirs' => $this->hobbiesContext($profile?->hobbies),
                'problématique'   => $this->safeProfileText($profile?->problematique),
                'cv'              => $this->safeCvStructured($profile?->cv_structured),
            ],
            'scoring'   => $scoring,
            'synthèse'  => $synth,
            'count'     => $count,
        ], JSON_UNESCAPED_UNICODE);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => "Données candidat :\n{$payload}\n\nPropose exactement {$count} métiers, classés du plus pertinent au moins pertinent. Format : { \"métiers\": [ { \"titre\":..., \"secteur\":..., \"fit_score\":..., \"pourquoi\":..., \"prochaine_étape\":... }, ... ] }"],
        ];
    }

    public function emailPersonalization(string $template, array $contextVariables): array
    {
        $system = "Tu es un copywriter spécialisé en emails neuromarketing. Tu personnalises un template email en gardant sa structure mais en adaptant le ton, les références et les exemples au destinataire. Réponds uniquement avec l'email final, sans commentaire.";

        // Sanitiser le template et les variables de contexte avant injection dans le prompt (TECH-12).
        $safeTemplate = $this->sanitizeForPrompt($template);
        $safeContext  = array_map(
            fn ($v) => is_string($v) ? $this->sanitizeForPrompt($v) : $v,
            $contextVariables
        );

        $user_msg = "Template :\n---\n{$safeTemplate}\n---\n\nContexte destinataire :\n" . json_encode($safeContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    /**
     * Traduit la clé work_sector en label lisible pour les prompts IA.
     */
    protected function questTitleLabel(?string $key): ?string
    {
        return match ($key) {
            'architecte' => "L'Architecte (construit des systèmes, pense en structures)",
            'explorateur' => "L'Explorateur (cherche, questionne, aime les possibles)",
            'passeur'    => 'Le Passeur (transmet, connecte, fait grandir)',
            default      => null,
        };
    }

    protected function workSectorLabel(?string $key): ?string
    {
        return match ($key) {
            'public'  => 'Public',
            'private' => 'Privé',
            default   => null,
        };
    }

    /**
     * Prépare les hobbies pour le prompt IA.
     *
     * Les loisirs éclairent la personnalité et les soft skills, mais ne sont PAS
     * une piste de reconversion en soi : l'IA ne doit pas suggérer "deviens coach
     * de tennis" parce que la personne joue au tennis le week-end.
     * On encapsule la donnée avec une note d'usage pour cadrer l'interprétation.
     */
    protected function hobbiesContext(?string $hobbies): ?array
    {
        $text = $this->safeProfileText($hobbies, 500);
        if ($text === null) {
            return null;
        }

        return [
            'valeur' => $text,
            'note_usage_ia' => 'Ces loisirs sont pratiqués a priori à titre amateur. '
                . 'Règle de base : utilise-les pour enrichir la personnalité (soft skills, énergie, curiosités) '
                . 'et pour nuancer le "pourquoi" d\'une piste. '
                . 'Tu peux proposer une reconversion en lien direct avec un loisir SI : '
                . '(1) la problématique mentionne une envie de créer, travailler ou entreprendre autour de ce domaine, '
                . 'OU (2) le CV/secteur indique déjà une proximité professionnelle avec ce domaine. '
                . 'Dans ce cas, privilégie des pistes périphériques et accessibles (ex : pour le crossfit → '
                . 'nutrition sportive, commerce B2B vers les salles, coaching wellness) plutôt que la pratique '
                . 'pure (coach CrossFit nécessite une reconversion longue et un niveau certifié). '
                . 'Sans signal explicite, reste sur les soft skills uniquement.',
        ];
    }

    /**
     * Sanitise une entrée courte avant injection dans un prompt IA (TECH-12).
     * Neutralise les patterns d'injection de prompt les plus courants et tronque à 500 chars.
     */
    private function sanitizeForPrompt(string $input): string
    {
        $dangerous = ['ignore previous', 'ignore all', 'system:', 'assistant:', 'human:', '###', '<|'];
        $cleaned   = $input;
        foreach ($dangerous as $pattern) {
            $cleaned = str_ireplace($pattern, '[...]', $cleaned);
        }
        return mb_substr(strip_tags($cleaned), 0, 500);
    }

    public function cvExtraction(string $cvText): array
    {
        $system = "Tu extrais les données structurées d'un CV. Réponds STRICTEMENT en JSON, sans texte hors-JSON.";
        $schema = [
            'identité'    => ['nom', 'titre_actuel'],
            'expériences' => [['poste', 'entreprise', 'début', 'fin', 'description']],
            'formations'  => [['diplôme', 'établissement', 'année']],
            'compétences' => ['hard_skills', 'soft_skills', 'langues'],
            'mots_clés'   => 'array',
        ];

        // #10 — Sanitiser le texte brut du CV avant envoi au LLM pour éviter le prompt injection.
        $safeText = $this->sanitizeUserContent($cvText, maxChars: 20000);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => "Schéma : " . json_encode($schema, JSON_UNESCAPED_UNICODE) . "\n\nCV :\n---\n{$safeText}\n---"],
        ];
    }

    /**
     * Sanitise un texte court du profil (problématique, etc.) avant envoi au LLM.
     * Renvoie null si vide pour ne pas encombrer le prompt.
     */
    protected function safeProfileText(?string $text, int $maxChars = 2000): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        return $this->sanitizeUserContent(trim($text), $maxChars);
    }

    /**
     * Extrait les informations essentielles du CV structuré (tableau JSON) pour le prompt.
     * Limite la taille et sanitise pour éviter le prompt injection.
     */
    protected function safeCvStructured(mixed $cvStructured, int $maxChars = 3000): ?string
    {
        if (empty($cvStructured)) {
            return null;
        }

        // cv_structured peut être un tableau ou une chaîne JSON déjà décodée.
        $data = is_array($cvStructured) ? $cvStructured : (json_decode((string) $cvStructured, true) ?? []);

        if (empty($data)) {
            return null;
        }

        // Ne garder que les clés utiles pour l'orientation, sans les détails verbeux.
        $safe = [];
        foreach (['identité', 'expériences', 'formations', 'compétences', 'mots_clés'] as $key) {
            if (isset($data[$key])) {
                $safe[$key] = $data[$key];
            }
        }

        $json = json_encode($safe ?: $data, JSON_UNESCAPED_UNICODE);

        return $this->sanitizeUserContent($json ?: '', $maxChars);
    }

    /**
     * Sanitise un texte utilisateur avant envoi au LLM.
     *
     * Protections appliquées :
     * 1. Troncature à $maxChars pour limiter les coûts et les injections longues.
     * 2. Détection et neutralisation des patterns de prompt injection courants
     *    (ex : "ignore previous instructions", "act as", "you are now"…).
     *    On ne supprime pas la phrase, on la neutralise en la faisant précéder d'un
     *    marqueur [FILTERED] afin de ne pas déformer silencieusement un vrai CV.
     *
     * #10 — Protection contre le prompt injection dans les entrées utilisateur.
     */
    protected function sanitizeUserContent(string $text, int $maxChars = 10000): string
    {
        // 1. Tronquer
        if (mb_strlen($text) > $maxChars) {
            $text = mb_substr($text, 0, $maxChars) . "\n[... contenu tronqué ...]";
        }

        // 2. Neutraliser les patterns d'injection courants (insensible à la casse)
        $injectionPatterns = [
            // Commandes directes au LLM
            '/\b(ignore|oublie|forget|disregard)\b.{0,60}\b(instructions?|consignes?|directives?|everything|all)\b/iu',
            '/\b(tu es maintenant|you are now|act as|agis comme|maintenant tu es)\b/iu',
            // Tentatives de ré-initialisation du rôle
            '/\bsystem\s*:/iu',
            '/\bprompt\s*:/iu',
            // Demandes de sortie du rôle
            '/\b(switch to|passe en mode|change de rôle|nouveau rôle)\b/iu',
        ];

        foreach ($injectionPatterns as $pattern) {
            $text = preg_replace($pattern, '[FILTERED]', $text);
        }

        return $text;
    }
}
