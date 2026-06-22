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
        'mistral' => [
            'driver' => Praxis\Core\AI\Drivers\MistralDriver::class,
            'api_key' => env('MISTRAL_API_KEY'),
            'model'   => env('MISTRAL_MODEL', 'mistral-large-latest'),
        ],
        'ollama' => [
            'driver'   => Praxis\Core\AI\Drivers\OllamaDriver::class,
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model'    => env('OLLAMA_MODEL', 'llama3.1'),
        ],
    ],

    'tasks' => [
        'profile_synthesis' => [
            'driver' => null, // null = default
            'system_prompt_view' => 'ai.prompts.synthesis-system',
        ],
        'job_suggestions' => [
            'driver' => null,
            'system_prompt_view' => 'ai.prompts.jobs-system',
            'count' => 15,
        ],
        'global_grimoire' => [
            'driver' => null,            // = défaut (anthropic)
            'prompt_version' => '1.0',
            'count' => 15,               // nombre de "Voies Possibles" consolidées
        ],
        'cv_extract' => [
            'driver' => null,
            'system_prompt_view' => 'ai.prompts.cv-extract-system',
        ],
        'email_personalization' => [
            'driver' => null,
            'system_prompt_view' => 'ai.prompts.email-system',
        ],
        'oracle_chat' => [
            'driver' => null,            // = défaut (anthropic)
            'history_limit' => 20,       // nombre de messages (user+assistant) rejoués dans le prompt
        ],
    ],
];
