<?php

namespace App\Jobs;

use App\Models\TestAttempt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Praxis\Core\AI\Services\JobSuggestionService;
use Praxis\Core\AI\Services\ProfileSynthesisService;
use Praxis\Core\Plugins\PluginHooks;

class GenerateAttemptInsights implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 240;

    public function __construct(public int $attemptId) {}

    public function handle(ProfileSynthesisService $synthesis, JobSuggestionService $jobs): void
    {
        $attempt = TestAttempt::with(['user.profile', 'test', 'result'])->findOrFail($this->attemptId);

        $synthesis->synthesize($attempt);
        $jobs->suggest($attempt);

        PluginHooks::doAction('insights.generated', $attempt->fresh('result'));
    }
}
