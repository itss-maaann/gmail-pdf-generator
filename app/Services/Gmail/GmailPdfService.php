<?php

namespace App\Services\Gmail;

use Google_Service_Gmail;
use App\Services\Pdf\PdfService;
use App\Services\Pdf\HtmlConversationBuilder;

class GmailPdfService
{
    public function __construct(
        protected GmailService $gmailService,
        protected HtmlConversationBuilder $htmlBuilder,
        protected PdfService $pdfService
    ) {}

    public function generateConversationPdf(string $from, string $to, string $conversationId): string
    {
        $client = $this->gmailService->getClientForEmail($from);
        $service = new Google_Service_Gmail($client);

        $messages = $this->gmailService->getMessagesBetween($service, $from, $to);

        if (empty($messages)) {
            throw new \Exception("No conversation found.");
        }

        $emails = $this->gmailService->extractMessageBodies($messages, $service);

        usort($emails, function ($a, $b) {
            preg_match('/(Message|Reply) (\d+)/', $a['subject'], $aMatch);
            preg_match('/(Message|Reply) (\d+)/', $b['subject'], $bMatch);
            return ($aMatch[2] ?? 0) <=> ($bMatch[2] ?? 0);
        });

        $html = $this->htmlBuilder->build($from, $to, $emails, $conversationId);

        $finalFilename = "gmail_conversation_{$conversationId}_" . now()->format('Ymd_His') . ".pdf";
        return $this->pdfService->generate($html, $finalFilename);
    }
}
