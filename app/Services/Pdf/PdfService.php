<?php

namespace App\Services\Pdf;

use Barryvdh\Snappy\Facades\SnappyPdf;

class PdfService
{
    public function generate(string $html, string $filename): string
    {
        $path = storage_path("app/{$filename}");

        SnappyPdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('enable-local-file-access', true)
            ->save($path);

        return $path;
    }
}
