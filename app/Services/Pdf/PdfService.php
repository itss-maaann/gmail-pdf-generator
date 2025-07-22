<?php

namespace App\Services\Pdf;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfService
{
    public function generateChunk(string $html, string $filename): string
    {
        $path = storage_path("app/tmp/{$filename}.pdf");

        $pdf = new \Mpdf\Mpdf([
            'tempDir' => storage_path('app/tmp'),
            'format' => 'A4',
            'default_font_size' => 10,
            'default_font' => 'Arial',
        ]);

        // Break HTML into safe parts (each ~10KB, split by message block div)
        $blocks = preg_split('/(?=<div style="padding: 20px;)/', $html);

        // Write the starting header first (e.g. <h1>...)
        $header = array_shift($blocks);
        $pdf->WriteHTML($header);

        foreach ($blocks as $block) {
            $pdf->WriteHTML($block);
        }

        $pdf->Output($path, \Mpdf\Output\Destination::FILE);

        return $path;
    }
}
