<?php

namespace Praxis\Core\AI;

use App\Models\TestAttempt;

class PromptBuilder
{
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
                'cv_extrait' => $profile?->cv_structured,
            ],
            'test' => [
                'nom'  => $test->name,
                'type' => $test->type,
            ],
            'résultats' => $result?->scoring,
        ];

        $user_msg = "Voici les données du candidat :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nGénère une synthèse de 250-400 mots en 3 paragraphes : "
            . "(1) traits dominants et forces, (2) zones de développement et angles morts, "
            . "(3) levier principal pour transformer ce profil en valeur ajoutée concrète.";

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $user_msg],
        ];
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

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => "Schéma : " . json_encode($schema, JSON_UNESCAPED_UNICODE) . "\n\nCV :\n---\n{$cvText}\n---"],
        ];
    }
}
