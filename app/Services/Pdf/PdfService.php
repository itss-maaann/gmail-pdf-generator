<?php

namespace App\Services\Pdf;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Log;

class PdfService
{
    public function generate(string $html, string $filename): string
    {
        $startTime = microtime(true);
        Log::info("📄 PDF generation started using Snappy for file: {$filename}");

        $path = storage_path("app/{$filename}");

        SnappyPdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('enable-local-file-access', true)
            ->save($path);

        logExecutionStats("✅ PDF generation completed for file: {$filename}", $startTime);
        Log::info("📁 PDF saved at: {$path}");

        return $path;
    }
}
