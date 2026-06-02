<?php

namespace Praxis\Core\AI\Services;

use App\Models\TestAttempt;
use Praxis\Core\AI\AIManager;
use Praxis\Core\AI\PromptBuilder;
use Praxis\Core\Plugins\PluginHooks;

class JobSuggestionService
{
    public function __construct(
        protected AIManager $ai,
        protected PromptBuilder $prompts,
    ) {}

    public function suggest(TestAttempt $attempt, ?int $count = null): array
    {
        $count ??= config('ai.tasks.job_suggestions.count', config('praxiquest.results.suggested_jobs_count', 15));

        $messages = $this->prompts->jobSuggestions($attempt, $count);
        $driver = $this->ai->forTask('job_suggestions');

        $raw = $driver->chat($messages, ['temperature' => 0.7, 'max_tokens' => 3500]);
        $json = $this->parseJson($raw);

        $jobs = $json['métiers'] ?? $json['jobs'] ?? [];
        $jobs = PluginHooks::applyFilters('jobs.suggested', $jobs, $attempt);

        $attempt->result()->update(['suggested_jobs' => $jobs]);
        PluginHooks::doAction('jobs.generated', $attempt, $jobs);

        return $jobs;
    }

    protected function parseJson(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*(.+?)\s*```/s', $raw, $m)) $raw = $m[1];
        $first = strpos($raw, '{');
        $last  = strrpos($raw, '}');
        if ($first !== false && $last !== false) {
            $raw = substr($raw, $first, $last - $first + 1);
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException("AI did not return valid JSON for job suggestions");
        }
        return $decoded;
    }
}
