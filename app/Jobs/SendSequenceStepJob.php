<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Praxis\Core\Mailing\Services\SequenceRunner;

class SendSequenceStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $sequenceId,
        public int $userId,
        public int $stepIndex,
        public array $context = [],
    ) {}

    public function handle(SequenceRunner $runner): void
    {
        $runner->runStep($this->sequenceId, $this->userId, $this->stepIndex, $this->context);
    }
}
