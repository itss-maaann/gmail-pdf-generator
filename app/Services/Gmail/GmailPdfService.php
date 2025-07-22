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

        $finalFilename = "gmail_conversation_{$conversationId}_" . now()->format('Ymd_His') . ".pdf";
        $finalPath = storage_path("app/{$finalFilename}");

        $mainPdf = new \Mpdf\Mpdf([
            'tempDir' => storage_path('app/tmp'),
            'format' => 'A4',
            'default_font_size' => 10,
            'default_font' => 'Arial',
        ]);

        $chunks = array_chunk($emails, 5);
        foreach ($chunks as $i => $chunk) {
            $html = $this->htmlBuilder->build($from, $to, $chunk, "{$conversationId}_chunk{$i}");

            $tempPdfPath = $this->pdfService->generateChunk($html, "chunk_{$conversationId}_{$i}");

            $tempMpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('app/tmp')]);
            $pages = $tempMpdf->SetSourceFile($tempPdfPath);
            for ($j = 1; $j <= $pages; $j++) {
                $tplId = $tempMpdf->ImportPage($j);
                $mainPdf->AddPage();
                $mainPdf->UseTemplate($tplId);
            }

            unlink($tempPdfPath);
            unset($tempMpdf);
        }

        $mainPdf->Output($finalPath, \Mpdf\Output\Destination::FILE);

        return $finalPath;
    }
}
