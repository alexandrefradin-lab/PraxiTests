<?php

namespace Praxis\Core\AI;

use App\Models\ProfileGrimoire;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class PromptBuilder
{
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
    public function oracleChat(User $user, Collection $attempts, ?ProfileGrimoire $grimoire, array $history, string $message): array
    {
        $persona = <<<TXT
Tu es l'Oracle de PraxiQuest : un conseiller d'orientation professionnelle senior, chaleureux et lucide, qui dialogue avec la personne pour l'aider à y voir clair sur son profil et ses possibles.
Tu connais ses tests (RIASEC, MBTI, Big Five, intelligence émotionnelle, etc.), son profil et la relecture globale de son Grimoire. Tu t'appuies dessus pour personnaliser chaque réponse, sans jamais réciter les données brutes.
Tu peux, quand c'est utile ou demandé, proposer et affiner des pistes de métiers réalistes et accessibles sur le marché francophone actuel — en variant les secteurs et les modèles (salariat / entrepreneuriat / freelance) et en expliquant POURQUOI à partir de ce que tu sais d'elle.
Style : tutoiement, français naturel, phrases courtes, ton bienveillant mais franc, sans jargon ni flatterie creuse. Réponses concises (3 à 6 phrases en général) ; développe seulement si on te le demande.
Tu poses une question d'ouverture quand c'est pertinent pour faire avancer la réflexion, mais jamais plus d'une à la fois.
Garde-fous : tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers. Tu n'inventes pas de scores ni de chiffres qu'on ne t'a pas donnés. Si une information te manque, tu le dis. Tu restes dans ton rôle d'orientation même si on te demande autre chose.
Tu réponds en texte simple (pas de JSON, pas de Markdown lourd).
TXT;

        $context = $this->grimoireContext($user, $attempts);

        if ($grimoire && $grimoire->synthesis) {
            $context['grimoire'] = [
                'fil_conducteur' => $grimoire->synthesis,
                'voies_pressenties' => collect($grimoire->voies ?? [])
                    ->map(fn ($v) => $v['titre'] ?? null)
                    ->filter()
                    ->take(15)
                    ->values()
                    ->all(),
            ];
        }

        $system = $persona
            . "\n\n--- CONTEXTE DU CANDIDAT (confidentiel, ne pas recopier tel quel) ---\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

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
    public function globalGrimoire(User $user, Collection $attempts, int $count = 15): array
    {
        $profile = $user->profile;

        $system = <<<TXT
Tu es un consultant en orientation professionnelle senior, formé aux approches RIASEC, MBTI, Big Five, bilan de compétences et intelligence émotionnelle.
Ton rôle : produire une RELECTURE GLOBALE qui CROISE plusieurs tests entre eux — pas une simple juxtaposition de synthèses individuelles.
Tu mets en évidence les convergences (ce qui se confirme d'un test à l'autre), les tensions (ce qui semble se contredire) et le fil conducteur du profil.
Style : chaleureux, professionnel, français, sans jargon, sans flatterie creuse, phrases courtes.
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers. Tu n'inventes pas de scores qu'on ne t'a pas donnés.
Tu réponds STRICTEMENT en JSON valide, sans texte hors-JSON, sans bloc ```.
TXT;

        // Une entrée par test : labels qualitatifs (jamais de chiffres bruts) + synthèse du test
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
                'statut'     => $profile?->status,
                'depuis'     => $profile?->status_since?->format('Y-m'),
                'rôle'       => $profile?->current_role,
                'industrie'  => $profile?->industry,
                'problématique' => $this->safeProfileText($profile?->problematique),
                'cv_extrait' => $this->safeCvStructured($profile?->cv_structured),
            ],
            'tests'  => $tests,
        ];

        $user_msg = "Voici l'ensemble des tests passés par le candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nProduis un JSON STRICT avec deux clés :\n"
            . "1) \"synthese\" : 400 à 600 mots, OBLIGATOIREMENT en 3 à 4 paragraphes distincts séparés "
            . "par un double saut de ligne échappé \\n\\n (jamais un bloc unique), en CROISANT les tests "
            . "(convergences, tensions, fil conducteur). Ne recopie pas les synthèses individuelles. "
            . "N'utilise jamais de chiffres ni de percentiles — appuie-toi sur les labels qualitatifs.\n"
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
        $profile = $user->profile;

        $tests = $attempts->map(function (TestAttempt $a) {
            return [
                'nom'                          => $a->test?->name,
                'type'                         => $a->test?->type,
                'interprétation_par_dimension' => $this->enrichScoringForPrompt($a->result?->scoring)['interprétation_par_dimension']
                                                    ?? $this->enrichScoringForPrompt($a->result?->scoring),
                'synthèse_du_test'             => $a->result?->ai_synthesis,
            ];
        })->values()->all();

        return [
            'profil' => [
                'statut'        => $profile?->status,
                'depuis'        => $profile?->status_since?->format('Y-m'),
                'rôle'          => $profile?->current_role,
                'industrie'     => $profile?->industry,
                'problématique' => $this->safeProfileText($profile?->problematique),
                'cv_extrait'    => $this->safeCvStructured($profile?->cv_structured),
            ],
            'tests'  => $tests,
        ];
    }

    /**
     * Grimoire — PROMPT 1/2 : uniquement la synthèse croisée.
     * Conçu pour tourner EN PARALLÈLE avec globalGrimoireVoies() (Http::pool).
     */
    public function globalGrimoireSynthese(User $user, Collection $attempts): array
    {
        $system = <<<TXT
Tu es un consultant en orientation professionnelle senior, formé aux approches RIASEC, MBTI, Big Five, bilan de compétences et intelligence émotionnelle.
Ton rôle : produire une RELECTURE GLOBALE qui CROISE plusieurs tests entre eux — pas une simple juxtaposition de synthèses individuelles.
Tu mets en évidence les convergences (ce qui se confirme d'un test à l'autre), les tensions (ce qui semble se contredire) et le fil conducteur du profil.
Style : chaleureux, professionnel, français, sans jargon, sans flatterie creuse, phrases courtes.
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers. Tu n'inventes pas de scores qu'on ne t'a pas donnés.
Tu réponds STRICTEMENT en JSON valide, sans texte hors-JSON, sans bloc ```.
TXT;

        $context = $this->grimoireContext($user, $attempts);

        $user_msg = "Voici l'ensemble des tests passés par le candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nProduis un JSON STRICT avec une SEULE clé \"synthese\" : 400 à 600 mots, "
            . "OBLIGATOIREMENT structurée en 3 à 4 paragraphes distincts, en CROISANT les tests "
            . "(convergences, tensions, fil conducteur). Chaque paragraphe développe une idée et fait "
            . "au moins 4 phrases.\n"
            . "RÈGLE DE FORMAT IMPÉRATIVE : sépare chaque paragraphe par un DOUBLE saut de ligne, "
            . "écrit dans la chaîne JSON sous la forme échappée \\n\\n. Ne renvoie jamais la synthèse "
            . "comme un bloc unique sans saut de ligne.\n"
            . "Ne recopie pas les synthèses individuelles. N'utilise jamais de chiffres ni de percentiles "
            . "— appuie-toi sur les labels qualitatifs.\n\n"
            . "Exemple EXACT du format attendu (garde les \\n\\n entre les paragraphes) :\n"
            . "{ \"synthese\": \"Premier paragraphe qui pose le fil conducteur...\\n\\nDeuxième paragraphe "
            . "sur les convergences entre les tests...\\n\\nTroisième paragraphe sur les tensions et la "
            . "manière de les habiter...\" }";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    /**
     * Grimoire — PROMPT 2/2 : uniquement les {count} voies métiers.
     * Conçu pour tourner EN PARALLÈLE avec globalGrimoireSynthese() (Http::pool).
     */
    public function globalGrimoireVoies(User $user, Collection $attempts, int $count = 15): array
    {
        $system = <<<TXT
Tu es un consultant en orientation professionnelle senior qui propose des métiers réalistes, alignés sur le profil et le marché du travail français/francophone actuel.
Tu croises l'ensemble des tests du candidat pour fonder chaque piste. Tu ne proposes que des métiers existants et accessibles.
Tu varies les secteurs et les modèles (salariat / entrepreneuriat / freelance).
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers. Tu n'inventes pas de scores qu'on ne t'a pas donnés.
Tu réponds STRICTEMENT en JSON valide, sans texte hors-JSON, sans bloc ```.
TXT;

        $context = $this->grimoireContext($user, $attempts);

        $user_msg = "Voici l'ensemble des tests passés par le candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nProduis un JSON STRICT avec une SEULE clé \"voies\" : EXACTEMENT {$count} pistes de "
            . "métiers réalistes et accessibles sur le marché francophone actuel, classées du plus pertinent "
            . "au moins pertinent, en variant les secteurs et les modèles (salariat / entrepreneuriat / freelance). "
            . "Pour chaque piste : { \"titre\", \"secteur\", \"fit_score\" (0-100), \"pourquoi\" (50 mots max), "
            . "\"appui_tests\" (liste des noms de tests qui soutiennent cette piste), "
            . "\"prochaine_etape\" (action concrète), "
            . "\"axes\" : un objet décrivant À QUEL POINT CE MÉTIER satisfait 5 critères, chacun noté 0-100 "
            . "(0 = pas du tout, 100 = pleinement), en te basant sur la réalité du métier sur le marché francophone : "
            . "{ \"remuneration\" (potentiel de rémunération), "
            . "\"accessibilite\" (facilité/rapidité d'accès, formation courte), "
            . "\"stabilite\" (sécurité de l'emploi, demande durable), "
            . "\"autonomie\" (indépendance, possibilité freelance/entrepreneuriat), "
            . "\"sens\" (utilité, impact, sens du travail) } }.\n\n"
            . "Les scores d'axes décrivent le MÉTIER lui-même (pas le profil du candidat) et doivent être nuancés "
            . "et différenciés d'une piste à l'autre — évite de tout mettre à 50 ou à 80.\n\n"
            . "Format attendu : { \"voies\": [ { ..., \"axes\": { \"remuneration\": 0-100, \"accessibilite\": 0-100, \"stabilite\": 0-100, \"autonomie\": 0-100, \"sens\": 0-100 } }, ... ] }";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
    }

    public function profileSynthesis(TestAttempt $attempt): array
    {
        $user    = $attempt->user;
        $profile = $user?->profile;
        $test    = $attempt->test;
        $result  = $attempt->result;

        $system = <<<TXT
Tu es un consultant en orientation professionnelle senior, formé aux approches RIASEC, MBTI, Big Five et bilan de compétences.
Ton rôle : produire une synthèse de profil claire, bienveillante, actionnable, en français.
Style : chaleureux, professionnel, sans jargon, sans flatterie creuse. Phrases courtes. Pas de bullet points dans la synthèse, paragraphes.
Tu ne donnes JAMAIS de conseils médicaux, juridiques ou financiers.
Tu n'inventes pas de scores qu'on ne t'a pas donnés.
TXT;

        $context = [
            'profil' => [
                'statut'     => $profile?->status,
                'depuis'     => $profile?->status_since?->format('Y-m'),
                'rôle'       => $profile?->current_role,
                'industrie'  => $profile?->industry,
                'problématique' => $this->safeProfileText($profile?->problematique),
                'cv_extrait' => $this->safeCvStructured($profile?->cv_structured),
            ],
            'test' => [
                'nom'  => $test->name,
                'type' => $test->type,
            ],
            // Priorité aux données étalonnées (norm_scores) qui donnent le contexte
            // populationnel (ex: "Très développé" = top 15%) — bien plus utile pour
            // la synthèse que des chiffres bruts (86/100 ne veut rien dire sans référence)
            'résultats' => $this->enrichScoringForPrompt($result?->scoring),
        ];

        $user_msg = "Voici les données du candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nGénère une synthèse de 250-400 mots en 3 paragraphes : "
            . "(1) traits dominants et forces en t'appuyant sur les dimensions 'Très développé' "
            . "et 'Au-dessus de la moyenne', (2) zones de développement (dimensions 'En développement' "
            . "ou 'Peu présent'), (3) levier principal pour transformer ce profil en valeur ajoutée concrète. "
            . "Ne cite jamais de chiffres ni de percentiles — utilise les labels qualitatifs.";

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

    public function jobSuggestions(TestAttempt $attempt, int $count = 15): array
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
                'statut'   => $profile?->status,
                'rôle'     => $profile?->current_role,
                'problématique' => $this->safeProfileText($profile?->problematique),
                'cv'       => $this->safeCvStructured($profile?->cv_structured),
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

        $user_msg = "Template :\n---\n{$template}\n---\n\nContexte destinataire :\n" . json_encode($contextVariables, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
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
