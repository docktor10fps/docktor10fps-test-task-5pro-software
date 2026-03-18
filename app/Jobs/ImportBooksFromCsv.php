<?php

namespace App\Jobs;

use App\Services\Import\BookImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportBooksFromCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private string $path
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BookImportService $service): void
    {
        if (!file_exists($this->path)) {
            Log::error("ImportBooksFromCsv: File not found at path {$this->path}.");
            return;
        }

        try {
            Log::info("ImportBooksFromCsv: Start importing a file {$this->path}");

            $service->import($this->path);

            if (is_file($this->path)) {
                unlink($this->path);
            }

            Log::info("ImportBooksFromCsv: The import was completed successfully.");

        } catch (Throwable $e) {
            Log::error("ImportBooksFromCsv: Critical error during import. Details: " . $e->getMessage(), [
                'file_path' => $this->path,
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
