<?php

namespace App\DTO;

class BookRowDTO
{
    public function __construct(
        public string $title,
        public ?string $isbn,
        public ?string $description,
        public ?int $edition,
        public ?int $year,
        public ?int $pages,
        public ?string $format,
        public ?string $country,
        public ?string $publisher,
        public array $authors,
        public array $genres
    ) {}
}
