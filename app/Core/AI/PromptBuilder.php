<?php

namespace Praxis\Core\AI;

use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class PromptBuilder
{
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
                'problématique' => $profile?->problematique,
                'cv_extrait' => $profile?->cv_structured,
            ],
            'tests'  => $tests,
        ];

        $user_msg = "Voici l'ensemble des tests passés par le candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nProduis un JSON STRICT avec deux clés :\n"
            . "1) \"synthese\" : 400-600 mots, 3 à 4 paragraphes, en CROISANT les tests "
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
                'problématique' => $profile?->problematique,
                'cv_extrait' => $profile?->cv_structured,
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
                'problématique' => $profile?->problematique,
                'cv'       => $profile?->cv_structured,
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
