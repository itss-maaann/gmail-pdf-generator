<?php

namespace App\Services\Gmail;

use Google_Service_Gmail;
use Illuminate\Support\Facades\Log;

class SimulatedEmailService
{
    public function __construct(
        protected GmailService $gmailService,
        protected EmailContentService $contentService
    ) {}

    public function simulateConversation(array $from, array $to): void
    {
        $fromService = new Google_Service_Gmail(
            $this->gmailService->getClient(['token' => $from['token']])
        );

        $toService = new Google_Service_Gmail(
            $this->gmailService->getClient(['token' => $to['token']])
        );

        $conversationBlocks = $this->contentService->extractAllConversations();

        foreach ($conversationBlocks as $i => [$message, $reply]) {
            if ($message) {
                $this->sendEmail($fromService, $from['email'], $to['email'], "Message {$i} from A to B", $message);
            }

            if ($reply) {
                $this->sendEmail($toService, $to['email'], $from['email'], "Reply {$i} from B to A", $reply);
            }
        }
    }

    protected function sendEmail(
        Google_Service_Gmail $service,
        string $from,
        string $to,
        string $subject,
        string $body
    ): void {
        try {
            $this->gmailService->sendEmail($service, $from, $to, $subject, $body);
            Log::info("Email sent: {$subject} | {$from} → {$to}");
        } catch (\Throwable $e) {
            Log::error("Email failed: {$subject} | Error: " . $e->getMessage());
        }

        sleep(1);
    }
}
