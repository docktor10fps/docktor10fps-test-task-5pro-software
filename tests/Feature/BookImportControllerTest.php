<?php

namespace Tests\Feature;

use App\Jobs\ImportBooksFromCsv;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookImportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_accepts_csv_and_dispatches_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $csv = $this->makeCsvFile();

        $response = $this->postJson('/api/books/import', [
            'file' => $csv,
        ]);

        $response->assertAccepted()
            ->assertJsonFragment(['status' => 'queued']);

        Queue::assertPushed(ImportBooksFromCsv::class);
    }

    public function test_import_requires_file(): void
    {
        $this->postJson('/api/books/import', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_import_rejects_non_csv_file(): void
    {
        $file = UploadedFile::fake()->create('books.pdf', 100, 'application/pdf');

        $this->postJson('/api/books/import', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_import_rejects_file_over_size_limit(): void
    {
        Queue::fake();
        Storage::fake('local');

        // 10241 KB > 10240 KB limit
        $file = UploadedFile::fake()->create('big.csv', 10241, 'text/csv');

        $this->postJson('/api/books/import', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeCsvFile(): UploadedFile
    {
        $content = implode("\n", [
            'Authors,Title,Genre,Description,Edition,Publisher,Year,Format,Pages,Country,ISBN',
            '"John Doe","Test Book","Fiction","A description",1,"Publisher Name",2020,"Hardcover",300,"Ukraine",9781234567890',
        ]);

        $path = tempnam(sys_get_temp_dir(), 'csv_') . '.csv';
        file_put_contents($path, $content);

        return new UploadedFile($path, 'books.csv', 'text/csv', null, true);
    }
}
