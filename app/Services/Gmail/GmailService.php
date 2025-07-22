<?php

namespace App\Services\Gmail;

use Google_Client;
use Google_Service_Gmail;
use App\Models\AuthenticatedEmail;

class GmailService
{
    public function __construct(
        protected TokenRefresher $tokenRefresher,
        protected MessageParser $messageParser
    ) {}

    public function getClient(array $options = []): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope([
            Google_Service_Gmail::GMAIL_READONLY,
            Google_Service_Gmail::GMAIL_SEND,
        ]);

        if (!empty($options['token'])) {
            $client->setAccessToken($options['token']);

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                if (isset($options['on_refresh']) && is_callable($options['on_refresh'])) {
                    $options['on_refresh']->call($this, $client->getAccessToken());
                }
            }
        }

        return $client;
    }

    public function getClientForEmail(string $email): Google_Client
    {
        $record = AuthenticatedEmail::where('email', $email)->firstOrFail();

        return $this->getClient([
            'token' => json_decode($record->token, true),
            'on_refresh' => $this->tokenRefresher->forEmail($email),
        ]);
    }

    public function getMessagesBetween(Google_Service_Gmail $service, string $emailA, string $emailB): array
    {
        return array_merge(
            $this->fetchMessages($service, 'me', "from:$emailA to:$emailB", 35),
            $this->fetchMessages($service, 'me', "from:$emailB to:$emailA", 35),
        );
    }

    private function fetchMessages(Google_Service_Gmail $service, string $user, string $query, int $limit): array
    {
        $messages = [];
        $pageToken = null;

        while (count($messages) < $limit) {
            $params = [
                'q' => $query,
                'maxResults' => min(100, $limit - count($messages)),
            ];

            if ($pageToken) {
                $params['pageToken'] = $pageToken;
            }

            $response = $service->users_messages->listUsersMessages($user, $params);
            if (!$response->getMessages()) break;

            foreach ($response->getMessages() as $msg) {
                $full = $service->users_messages->get($user, $msg->getId(), ['format' => 'full']);
                $messages[] = $full;

                if (count($messages) >= $limit) break 2;
            }

            $pageToken = $response->getNextPageToken();
            if (!$pageToken) break;
        }

        return $messages;
    }

    public function extractMessageBodies(array $messages, Google_Service_Gmail $service): array
    {
        return $this->messageParser->parse($messages, $service);
    }

    public function sendEmail(Google_Service_Gmail $service, string $from, string $to, string $subject, string $body): ?\Google_Service_Gmail_Message
    {
        $message = new \Google_Service_Gmail_Message();

        $rawMessage = implode("\r\n", [
            'MIME-Version: 1.0',
            "From: <$from>",
            "To: <$to>",
            "Subject: $subject",
            "Content-Type: text/plain; charset=utf-8",
            "",
            $body,
        ]);

        $encoded = rtrim(strtr(base64_encode($rawMessage), ['+' => '-', '/' => '_']), '=');
        $message->setRaw($encoded);

        return $service->users_messages->send('me', $message);
    }
}
