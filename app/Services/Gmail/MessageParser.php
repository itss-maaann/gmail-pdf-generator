<?php

namespace App\Services\Gmail;

class MessageParser
{
    public function parse(array $messages, \Google_Service_Gmail $gmailService): array
    {
        $emails = [];

        foreach ($messages as $message) {
            $payload = $message->getPayload();
            $headers = collect($payload->getHeaders());

            $from = $headers->firstWhere('name', 'From')?->getValue();
            $to = $headers->firstWhere('name', 'To')?->getValue();
            $subject = $headers->firstWhere('name', 'Subject')?->getValue() ?? '';
            $date = $message->getInternalDate();
            $body = $this->extractBody($gmailService, $message->getId(), $payload);

            if ($from && $to && $body && $date) {
                $emails[] = [
                    'from' => $from,
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $body,
                    'timestamp' => (int) ($date / 1000),
                ];
            }
        }

        usort($emails, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        return $emails;
    }

    private function extractBody(\Google_Service_Gmail $gmail, string $messageId, $payload): ?string
    {
        $body = $this->findBodyInParts($gmail, $messageId, [$payload]);

        return $body ?: null;
    }

    private function findBodyInParts(\Google_Service_Gmail $gmail, string $messageId, $parts): ?string
    {
        $htmlBody = null;
        $plainBody = null;

        foreach ($parts as $part) {
            $mimeType = $part->getMimeType();
            $body = $part->getBody();

            if (in_array($mimeType, ['text/plain', 'text/html'])) {
                $data = null;

                if ($body->getData()) {
                    $data = base64_decode(strtr($body->getData(), '-_', '+/'));
                } elseif ($body->getAttachmentId()) {
                    $attachment = $gmail->users_messages_attachments->get("me", $messageId, $body->getAttachmentId());
                    $data = base64_decode(strtr($attachment->getData(), '-_', '+/'));
                }

                if ($data) {
                    if ($mimeType === 'text/html') {
                        $htmlBody = $data;
                    } elseif ($mimeType === 'text/plain' && !$plainBody) {
                        $plainBody = $data;
                    }
                }
            }

            // Recurse deeply
            if ($part->getParts()) {
                $nested = $this->findBodyInParts($gmail, $messageId, $part->getParts());
                if ($nested) {
                    return $nested;
                }
            }
        }

        // Prefer HTML over plain text
        return $htmlBody ?? $plainBody;
    }
}

