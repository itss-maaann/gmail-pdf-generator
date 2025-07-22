<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Gmail\SimulatedEmailService;

class SendSimulatedEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected array $from,
        protected array $to
    ) {}

    public function handle(SimulatedEmailService $simulatedEmailService): void
    {
        $simulatedEmailService->simulateConversation($this->from, $this->to);
    }
}
