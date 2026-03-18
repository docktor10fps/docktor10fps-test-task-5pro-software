<?php

namespace App\Services\Import;

use App\DTO\BookRowDTO;
use App\Models\Book;
use App\Services\BookService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\SimpleExcel\SimpleExcelReader;

class BookImportService
{
    private const CHUNK_SIZE = 200;

    public function __construct(
        private RowNormalizer $normalizer,
        private ReferenceCacheService $cache,
        private BookService $bookService
    ) {}

    public function import(string $path): void
    {
        DB::connection()->disableQueryLog();

        $rows = [];

        SimpleExcelReader::create($path, 'csv')
            ->getRows()
            ->each(function ($row) use (&$rows) {

                $dto = $this->normalizer->normalize($row);

                if (!$dto) {
                    return;
                }

                $rows[] = $dto;

                if (count($rows) >= self::CHUNK_SIZE) {
                    $this->processChunk($rows);
                    $rows = [];
                }
            });

        if ($rows) {
            $this->processChunk($rows);
        }
    }

    private function processChunk(array $rows): void
    {
        foreach ($rows as $dto) {
            try {
                $publisherId = $this->cache->publisherId($dto->publisher);

                if ($this->bookExists($dto, $publisherId)) {
                    continue;
                }

                $authorIds = collect($dto->authors)
                    ->map(fn($name) => $this->cache->authorId($name))
                    ->toArray();

                $genreIds = collect($dto->genres)
                    ->map(fn($name) => $this->cache->genreId($name))
                    ->toArray();

                $this->bookService->create(
                    [
                        'title' => $dto->title,
                        'isbn' => $dto->isbn,
                        'description' => $dto->description,
                        'edition' => $dto->edition,
                        'published_date' => $dto->year
                            ? Carbon::createFromFormat('Y', $dto->year)->startOfYear()
                            : null,
                        'pages' => $dto->pages,
                        'format' => $dto->format,
                        'country' => $dto->country,
                        'publisher_id' => $publisherId,
                    ],
                    $authorIds,
                    $genreIds
                );

            } catch (\Throwable $e) {
                Log::error("Book import error '{$dto->title}': " . $e->getMessage());
                continue;
            }
        }
    }

    private function bookExists(BookRowDTO $dto, ?int $publisherId): bool
    {
        $query = Book::query();

        if ($dto->isbn) {
            $query->where('isbn', $dto->isbn);
        } else {
            $query->where('title', $dto->title)
                ->where('publisher_id', $publisherId);
        }

        return $query->exists();
    }
}
