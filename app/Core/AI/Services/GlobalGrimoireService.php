<?php

namespace Praxis\Core\AI\Services;

use App\Models\ProfileGrimoire;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Collection;
use Praxis\Core\AI\AIManager;
use Praxis\Core\AI\Concerns\ParsesAiJson;
use Praxis\Core\AI\PromptBuilder;
use Praxis\Core\Plugins\PluginHooks;

/**
 * Génère le Grimoire global : relecture transversale de TOUS les tests du candidat.
 * Un seul appel IA → { synthese, voies[] }. Vient par-dessus les synthèses par test.
 */
class GlobalGrimoireService
{
    use ParsesAiJson;

    public function __construct(
        protected AIManager $ai,
        protected PromptBuilder $prompts,
    ) {}

    /**
     * (Re)génère le Grimoire global pour un utilisateur.
     * Ne fait rien s'il n'a aucun test complété avec résultat.
     */
    public function generate(User $user): ?ProfileGrimoire
    {
        $attempts = $this->completedAttempts($user);

        if ($attempts->isEmpty()) {
            return null;
        }

        $count    = (int) config('ai.tasks.global_grimoire.count', 15);
        $messages = $this->prompts->globalGrimoire($user, $attempts, $count);
        $messages = PluginHooks::applyFilters('ai.grimoire.messages', $messages, $user, $attempts);

        $driver = $this->ai->forTask('global_grimoire');
        $raw    = $driver->chat($messages, ['temperature' => 0.6, 'max_tokens' => 4000]);
        $raw    = PluginHooks::applyFilters('ai.grimoire.output', $raw, $user, $attempts);

        $json     = $this->parseJson($raw);
        $synthese = (string) ($json['synthese'] ?? $json['synthèse'] ?? '');
        $voies    = $json['voies'] ?? $json['métiers'] ?? $json['jobs'] ?? [];
        $voies    = PluginHooks::applyFilters('grimoire.voies', $voies, $user, $attempts);

        $usage = $driver->lastUsage();

        $grimoire = $user->grimoire();
        $grimoire->update([
            'synthesis'       => $synthese,
            'voies'           => $voies,
            'tests_included'  => $this->testsIncluded($attempts),
            'tests_signature' => $this->signature($attempts),
            'ai_driver'       => $driver->key(),
            'ai_model'        => $driver->model(),
            'ai_tokens_used'  => ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0),
            'ai_metadata'     => [
                'prompt_version' => config('ai.tasks.global_grimoire.prompt_version', '1.0'),
                'tests_count'    => $attempts->count(),
                'voies_count'    => is_array($voies) ? count($voies) : 0,
                'input_tokens'   => $usage['input_tokens'] ?? null,
                'output_tokens'  => $usage['output_tokens'] ?? null,
                'generated_at'   => now()->toIso8601String(),
            ],
            'status'          => 'ready',
            'generated_at'    => now(),
        ]);

        PluginHooks::doAction('ai.grimoire.completed', $user, $grimoire->fresh());

        return $grimoire->fresh();
    }

    /**
     * Empreinte des tentatives prises en compte. Si elle n'a pas changé depuis la
     * dernière génération, inutile de rappeler l'IA (protège les queues sync OVH).
     */
    public function signature(Collection $attempts): string
    {
        $parts = $attempts
            ->map(fn (TestAttempt $a) => $a->id . ':' . ($a->result?->generated_at?->timestamp ?? 0))
            ->sort()
            ->values()
            ->all();

        return md5(json_encode($parts));
    }

    /** Tentatives complétées avec résultat, les plus récentes d'abord. */
    public function completedAttempts(User $user): Collection
    {
        return $user->attempts()
            ->where('status', 'completed')
            ->whereHas('result')
            ->with(['test:id,name,slug,type', 'result'])
            ->latest('completed_at')
            ->get();
    }

    protected function testsIncluded(Collection $attempts): array
    {
        return $attempts->map(fn (TestAttempt $a) => [
            'attempt_id'   => $a->id,
            'test'         => $a->test?->name,
            'slug'         => $a->test?->slug,
            'completed_at' => $a->completed_at?->toIso8601String(),
        ])->values()->all();
    }
}
