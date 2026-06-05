<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\ResearchDocument;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Log;
use App\Jobs\GenerateResearchDraftJob;

class ExtractDocumentJob implements ShouldQueue
{
    use Queueable;

    protected ResearchDocument $document;

    public function __construct(ResearchDocument $document)
    {
        $this->document = $document;
    }

    public function handle(): void
    {
        try {
            $path = Storage::disk('public')->path($this->document->file_path);
            
            if (!file_exists($path)) {
                throw new \Exception("File not found: {$path}");
            }

            // Using Spatie pdf-to-text (requires poppler-utils installed on the OS)
            $text = Pdf::getText($path);

            $this->document->update([
                'extracted_text' => $text
            ]);

            Log::info("Successfully extracted text for document: {$this->document->id}");

        } catch (\Exception $e) {
            Log::error("Failed to extract text for document {$this->document->id}: " . $e->getMessage());
        }
    }
}
