<?php

namespace App\Jobs;

use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Praxis\Core\Mailing\Services\SequenceRunner;

class SendSequenceStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public int $sequenceId,
        public int $userId,
        public int $stepIndex,
        public array $context = [],
    ) {}

    public function handle(SequenceRunner $runner): void
    {
        // Idempotence : ne pas renvoyer un step déjà tracé dans email_logs (TECH-08).
        $alreadySent = EmailLog::where('user_id', $this->userId)
            ->where('sequence_id', $this->sequenceId)
            ->where('step', $this->stepIndex)
            ->exists();

        if ($alreadySent) {
            return;
        }

        $runner->runStep($this->sequenceId, $this->userId, $this->stepIndex, $this->context);
    }

    /**
     * Délai (en secondes) entre chaque tentative de retry (TECH-08).
     *
     * @return array<int>
     */
    public function backoff(): array
    {
        return [30, 60, 120];
    }
}
