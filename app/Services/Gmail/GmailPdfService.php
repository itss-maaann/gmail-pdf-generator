<?php

namespace App\Services\Gmail;

use Google_Service_Gmail;
use App\Services\Pdf\PdfService;
use App\Services\Pdf\HtmlConversationBuilder;
use Illuminate\Support\Facades\Log;

class GmailPdfService
{
    public function __construct(
        protected GmailService $gmailService,
        protected HtmlConversationBuilder $htmlBuilder,
        protected PdfService $pdfService
    ) {}

    public function generateConversationPdf(string $from, string $to, string $conversationId): string
    {
        $start = microtime(true);
        Log::info("📄 PDF conversation generation initiated for: {$conversationId}");

        $client = $this->gmailService->getClientForEmail($from);
        $service = new Google_Service_Gmail($client);

        $fetchStart = microtime(true);
        $messages = $this->gmailService->getMessagesBetween($service, $from, $to);
        logExecutionStats("📩 Fetched Gmail messages (" . count($messages) . ")", $fetchStart);

        if (empty($messages)) {
            throw new \Exception("No conversation found.");
        }

        $extractStart = microtime(true);
        $emails = $this->gmailService->extractMessageBodies($messages, $service);
        logExecutionStats("✉️ Extracted email bodies (" . count($emails) . ")", $extractStart);

        usort($emails, function ($a, $b) {
            preg_match('/(Message|Reply) (\d+)/', $a['subject'], $aMatch);
            preg_match('/(Message|Reply) (\d+)/', $b['subject'], $bMatch);
            return ($aMatch[2] ?? 0) <=> ($bMatch[2] ?? 0);
        });

        $buildStart = microtime(true);
        $html = $this->htmlBuilder->build($from, $to, $emails, $conversationId);
        logExecutionStats("🛠️ HTML conversation built", $buildStart);

        $pdfStart = microtime(true);
        $finalFilename = "gmail_conversation_{$conversationId}_" . now()->format('Ymd_His') . ".pdf";
        $pdfPath = $this->pdfService->generate($html, $finalFilename);
        logExecutionStats("📄 PDF generation completed", $pdfStart);

        Log::info("✅ Full conversation PDF saved at: {$pdfPath}");

        logExecutionStats("🎯 Total PDF generation workflow for conversation ID: {$conversationId}", $start);

        return $pdfPath;
    }
}
