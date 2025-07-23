<?php

namespace App\Jobs;

use App\Models\PdfGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class PdfCleanupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $jobId) {}

    public function handle(): void
    {
        $record = PdfGeneration::find($this->jobId);
        if (!$record) return;

        if (file_exists($record->file_path)) {
            @unlink($record->file_path);
        }

        $record->delete();
        Log::info("✅ PDF file and DB record cleaned for Job ID: {$this->jobId}");
    }
}
