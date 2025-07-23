<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Jobs\GeneratePdfJob;
use App\Models\PdfGeneration;
use App\Http\Requests\GeneratePdfRequest;
use App\Jobs\PdfCleanupJob;

class PdfController extends Controller
{

    public function generate(GeneratePdfRequest $request)
    {
        $id = (string) Str::uuid();

        PdfGeneration::create([
            'id' => $id,
            'from_email' => $request->from_email,
            'to_email' => $request->to_email,
        ]);

        GeneratePdfJob::dispatch($id);

        return response()->json([
            'job_id' => $id,
            'message' => 'PDF generation started.',
        ]);
    }

    public function checkPdfStatus(string $id)
    {
        $record = PdfGeneration::findOrFail($id);

        return response()->json([
            'status' => $record->status,
            'file_path' => $record->file_path,
            'error' => $record->error,
        ]);
    }

    public function downloadPdf(string $id)
    {
        $record = PdfGeneration::findOrFail($id);

        if (
            $record->status !== 'completed' ||
            !$record->file_path ||
            !file_exists($record->file_path)
        ) {
            return response()->json(['message' => 'PDF not ready or failed.'], 400);
        }

        $filePath = $record->file_path;
        $filename = basename($filePath);

        PdfCleanupJob::dispatch($record->id)->delay(now()->addSeconds(20));

        return response()->download($filePath, $filename)->deleteFileAfterSend(true);
    }
}
