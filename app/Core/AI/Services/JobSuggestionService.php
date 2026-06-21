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
        $driver   = $this->ai->forTask('job_suggestions');

        $raw  = $driver->chat($messages, ['temperature' => 0.7, 'max_tokens' => 3500]);
        $json = $this->parseJson($raw);

        $jobs = $json['métiers'] ?? $json['jobs'] ?? [];

        // Garantir le contrat produit : exactement {count} métiers.
        // On filtre les entrées vides, on tronque au-delà, et on alerte si l'IA
        // en a renvoyé moins que demandé (sans bloquer le rendu).
        $jobs = array_values(array_filter(
            is_array($jobs) ? $jobs : [],
            fn ($j) => !empty($j) && (is_array($j) ? !empty(array_filter($j)) : true)
        ));
        if (count($jobs) > $count) {
            $jobs = array_slice($jobs, 0, $count);
        } elseif (count($jobs) < $count) {
            logger()->warning("JobSuggestion: l'IA a renvoyé " . count($jobs) . " métiers au lieu de {$count}", [
                'attempt_id' => $attempt->id,
            ]);
        }

        $jobs = PluginHooks::applyFilters('jobs.suggested', $jobs, $attempt);

        // Collecte des métadonnées de traçabilité
        $usage    = $driver->lastUsage();
        $metadata = $this->buildMetadata($attempt, $driver, $count, $usage);

        $attempt->result()->update([
            'suggested_jobs'  => $jobs,
            'ai_driver'       => $driver->key(),
            'ai_model'        => $driver->model(),
            'ai_tokens_used'  => ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0),
            'ai_metadata'     => $metadata,
            'ai_generated_at' => now(),
        ]);

        PluginHooks::doAction('jobs.generated', $attempt, $jobs);

        return $jobs;
    }

    /**
     * Construit les métadonnées de traçabilité (sans données psycho brutes).
     * Ces données sont affichées au candidat via le disclaimer IA.
     */
    protected function buildMetadata(TestAttempt $attempt, mixed $driver, int $count, array $usage): array
    {
        // Collecte les dimensions du scoring (noms uniquement, pas les valeurs)
        $scoring    = $attempt->result?->scoring ?? [];
        $dimensions = array_keys($scoring['dimensions'] ?? $scoring);

        // Nombre de tests passés par le candidat
        $testsCount = $attempt->user
            ?->attempts()
            ?->where('status', 'completed')
            ->count() ?? 1;

        return [
            'prompt_version'  => config('ai.tasks.job_suggestions.prompt_version', '1.0'),
            'jobs_requested'  => $count,
            'tests_count'     => $testsCount,
            'dimensions'      => $dimensions,
            'input_tokens'    => $usage['input_tokens'] ?? null,
            'output_tokens'   => $usage['output_tokens'] ?? null,
            'driver_key'      => $driver->key(),
            'generated_at'    => now()->toIso8601String(),
        ];
    }

    protected function parseJson(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/```(?:json)?\s*(.+?)\s*```/s', $raw, $m)) {
            $raw = $m[1];
        }
        $first = strpos($raw, '{');
        $last  = strrpos($raw, '}');
        if ($first !== false && $last !== false) {
            $raw = substr($raw, $first, $last - $first + 1);
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('AI did not return valid JSON for job suggestions');
        }
        return $decoded;
    }
}
