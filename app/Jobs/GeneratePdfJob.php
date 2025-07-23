<?php

namespace App\Jobs;

use App\Models\PdfGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Services\Gmail\GmailPdfService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $jobId
    ) {}

    public function handle(GmailPdfService $gmailPdfService)
    {
        $record = PdfGeneration::findOrFail($this->jobId);

        try {
            $pdfPath = $gmailPdfService->generateConversationPdf($record->from_email, $record->to_email, $this->jobId);
            $record->update([
                'status' => 'completed',
                'file_path' => $pdfPath,
            ]);
        } catch (\Throwable $e) {
            $record->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
