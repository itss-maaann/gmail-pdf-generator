<?php

namespace App\Services;

use App\Jobs\SendSimulatedEmailsJob;
use App\Models\AuthenticatedEmail;

class ConversationService
{
    public function startSimulation(string $fromEmail, string $toEmail): array
    {
        $from = AuthenticatedEmail::where('email', $fromEmail)->first();
        $to = AuthenticatedEmail::where('email', $toEmail)->first();

        if (!$from || !$to) {
            return [
                'status' => 'error',
                'message' => 'Please authenticate both Gmail addresses before simulating conversation.',
            ];
        }

        if (empty($from->token) || empty($to->token)) {
            return [
                'status' => 'error',
                'message' => 'One or both email tokens are missing. Re-authentication may be required.',
            ];
        }

        SendSimulatedEmailsJob::dispatch($from->toArray(), $to->toArray());

        return [
            'status' => 'success',
            'message' => 'Conversation simulation started in the background!',
        ];
    }
}
