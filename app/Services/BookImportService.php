<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\DB;

class BookImportService
{
    public function import(string $fullPath): void
    {
        DB::connection()->disableQueryLog();

        SimpleExcelReader::create($fullPath)
            ->getRows()
            ->each(function (array $row) {
                $title = isset($row['Title']) ? trim($row['Title']) : null;
                if (empty($title)) return;

                DB::transaction(function () use ($row, $title) {
                    $publisherId = $this->getPublisherId($row['Publisher'] ?? null);

                    $book = Book::updateOrCreate(
                        [
                            'title' => $title,
                            'isbn'  => $row['ISBN'] ?? null
                        ],
                        [
                            'description'    => $row['Description'] ?? null,
                            'edition'        => !empty($row['Edition']) ? (int) $row['Edition'] : null,
                            'published_date' => $row['Year'] ?? null,
                            'pages'          => !empty($row['Pages']) ? (int) $row['Pages'] : null,
                            'format'         => $row['Format'] ?? null,
                            'country'        => $row['Country'] ?? null,
                            'publisher_id'   => $publisherId,
                        ]
                    );

                    $this->syncRelations($book, $row);
                });
            });
    }

    private function getPublisherId(?string $name): ?int
    {
        if (empty(trim($name))) return null;

        return Publisher::firstOrCreate(['name' => trim($name)])->id;
    }

    private function syncRelations(Book $book, array $row): void
    {
        if (!empty($row['Authors'])) {
            $authorIds = collect(explode(';', $row['Authors']))
                ->map(fn($name) => trim($name))
                ->filter()
                ->map(fn($name) => Author::firstOrCreate(['name' => $name])->id)
                ->filter();

            $book->authors()->sync($authorIds);
        }

        if (!empty($row['Genre'])) {
            $genreIds = collect(explode(';', $row['Genre']))
                ->map(fn($name) => trim($name))
                ->filter()
                ->map(fn($name) => Genre::firstOrCreate(['name' => $name])->id)
                ->filter();

            $book->genres()->sync($genreIds);
        }
    }
}
