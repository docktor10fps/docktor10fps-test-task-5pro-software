<?php

namespace App\Services\Import;

use App\DTO\BookRowDTO;

class RowNormalizer
{
    public function normalize(array $row): ?BookRowDTO
    {
        $title = trim($row['Title'] ?? '');

        if (!$title) {
            return null;
        }

        return new BookRowDTO(
            title: $title,
            isbn: $row['ISBN'] ?? null,
            description: $row['Description'] ?? null,
            edition: (!empty($row['Edition']) && is_numeric($row['Edition'])) ? (int)$row['Edition'] : null,
            year: (!empty($row['Year']) && is_numeric($row['Year'])) ? (int)$row['Year'] : null,
            pages: !empty($row['Pages']) ? (int)$row['Pages'] : null,
            format: $row['Format'] ?? null,
            country: $row['Country'] ?? null,
            publisher: $row['Publisher'] ?? null,
            authors: $this->split($row['Authors'] ?? null),
            genres: $this->split($row['Genre'] ?? null),
        );
    }

    private function split(?string $value): array
    {
        if (!$value) {
            return [];
        }

        return array_filter(array_map('trim', explode(';', $value)), fn($val) => $val !== '');
    }
}
