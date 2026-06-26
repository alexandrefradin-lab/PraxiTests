<?php

return [
    'default' => env('AI_DEFAULT_DRIVER', 'anthropic'),

    'drivers' => [
        'openai' => [
            'driver' => Praxis\Core\AI\Drivers\OpenAiDriver::class,
            'api_key' => env('OPENAI_API_KEY'),
            'model'   => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'temperature' => 0.7,
            'max_tokens'  => 2000,
        ],
        'anthropic' => [
            'driver' => Praxis\Core\AI\Drivers\AnthropicDriver::class,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model'   => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
            'temperature' => 0.7,
            'max_tokens'  => 4000,
        ],

        // Même fournisseur (Anthropic), modèle ÉCONOMIQUE (Haiku ≈ 3× moins cher que
        // Sonnet : 1$/5$ vs 3$/15$ par M tokens). Utilisé pour les tâches structurées
        // ou peu rédactionnelles (extraction CV, emails, suggestions, voies du Grimoire).
        'anthropic_haiku' => [
            'driver' => Praxis\Core\AI\Drivers\AnthropicDriver::class,
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model'   => env('ANTHROPIC_HAIKU_MODEL', 'claude-haiku-4-5-20251001'),
            'temperature' => 0.6,
            'max_tokens'  => 4000,
        ],
        'mistral' => [
            'driver' => Praxis\Core\AI\Drivers\MistralDriver::class,
            'api_key' => env('MISTRAL_API_KEY'),
            'model'   => env('MISTRAL_MODEL', 'mistral-large-latest'),
        ],

        // DeepSeek : API compatible OpenAI → on réutilise OpenAiDriver avec un base_url
        // différent. Clé et modèle surchargeables en admin (Setting groupe 'ai').
        'deepseek' => [
            'driver'   => Praxis\Core\AI\Drivers\OpenAiDriver::class,
            'key'      => 'deepseek',
            'api_key'  => env('DEEPSEEK_API_KEY'),
            'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
            'model'    => env('DEEPSEEK_MODEL', 'deepseek-chat'),
            'temperature' => 0.6,
            'max_tokens'  => 4000,
        ],
        'ollama' => [
            'driver'   => Praxis\Core\AI\Drivers\OllamaDriver::class,
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model'    => env('OLLAMA_MODEL', 'llama3.1'),
        ],
    ],

    // NB : 'driver' désigne une entrée de 'drivers' ci-dessus. null = défaut (anthropic/Sonnet).
    // 'anthropic_haiku' = même fournisseur, modèle économique (Haiku). Choix par tâche
    // pour ne payer Sonnet QUE sur le rédactionnel (synthèses, relecture, Oracle) et
    // basculer les tâches structurées/extraction vers Haiku. Sera surchargeable en admin (Phase 2).
    'tasks' => [
        // Rédactionnel, qualité importante → Sonnet.
        'profile_synthesis' => [
            'driver' => null, // null = default (Sonnet)
            'system_prompt_view' => 'ai.prompts.synthesis-system',
        ],
        // Liste structurée de métiers → Haiku suffit. count 30→15 (aligné sur le produit : "15 idées de métiers").
        'job_suggestions' => [
            'driver' => 'anthropic_haiku',
            'system_prompt_view' => 'ai.prompts.jobs-system',
            'count' => 15,
        ],
        // Grimoire — SYNTHÈSE (relecture rédactionnelle) → Sonnet.
        'global_grimoire' => [
            'driver' => null,            // = défaut (Sonnet)
            'prompt_version' => '1.1',   // 1.1 = pistes compactes + génération progressive
            'count' => 30,               // nombre de "Voies Possibles" par défaut (max 50). Format compact => 30 tient en <30s.
        ],
        // Grimoire — VOIES (liste structurée compacte) → Haiku (3× moins cher).
        'global_grimoire_voies' => [
            'driver' => 'anthropic_haiku',
        ],
        // Extraction JSON pure → Haiku.
        'cv_extract' => [
            'driver' => 'anthropic_haiku',
            'system_prompt_view' => 'ai.prompts.cv-extract-system',
        ],
        // Personnalisation d'email → Haiku.
        'email_personalization' => [
            'driver' => 'anthropic_haiku',
            'system_prompt_view' => 'ai.prompts.email-system',
        ],
        // Oracle conversationnel → Sonnet (qualité), mais on réduit l'historique rejoué
        // (20→10) pour diviser par ~2 les tokens d'entrée sur les conversations longues.
        'oracle_chat' => [
            'driver' => null,            // = défaut (Sonnet)
            'history_limit' => 10,       // nombre de messages (user+assistant) rejoués dans le prompt
        ],
    ],
];
