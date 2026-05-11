<?php

namespace Praxis\Core\AI\Services;

use App\Models\TestAttempt;
use Praxis\Core\AI\AIManager;
use Praxis\Core\AI\PromptBuilder;
use Praxis\Core\Plugins\PluginHooks;

class ProfileSynthesisService
{
    public function __construct(
        protected AIManager $ai,
        protected PromptBuilder $prompts,
    ) {}

    public function synthesize(TestAttempt $attempt): string
    {
        $messages = $this->prompts->profileSynthesis($attempt);
        $messages = PluginHooks::applyFilters('ai.synthesis.messages', $messages, $attempt);

        $driver = $this->ai->forTask('profile_synthesis');
        $text = $driver->chat($messages, ['temperature' => 0.6, 'max_tokens' => 1200]);
        $text = PluginHooks::applyFilters('ai.synthesis.output', $text, $attempt);

        $usage = $driver->lastUsage();

        $attempt->result()->update([
            'ai_synthesis' => $text,
            'ai_driver'    => $driver->key(),
            'ai_tokens_used' => ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0),
            'generated_at' => now(),
        ]);

        PluginHooks::doAction('ai.synthesis.completed', $attempt, $text);
        return $text;
    }
}
