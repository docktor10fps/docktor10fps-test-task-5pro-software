<?php

namespace App\Jobs;

use App\Services\BookImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportBooksFromCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $filePath
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BookImportService $service): void
    {
        $fullPath = Storage::path($this->filePath);

        if (!Storage::exists($this->filePath)) {
            Log::error("ImportBooksFromCsv: File not found at path {$fullPath}");
            return;
        }

        try {
            $service->import($fullPath);

            Storage::delete($this->filePath);

            Log::info("ImportBooksFromCsv: The import of file {$this->filePath} was successful.");
        } catch (\Exception $e) {
            Log::error("ImportBooksFromCsv: Error during import: " . $e->getMessage());

            throw $e;
        }
    }
}
