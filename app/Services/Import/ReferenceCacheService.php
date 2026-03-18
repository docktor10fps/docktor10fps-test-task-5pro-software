<?php

namespace App\Services\Import;

use App\Models\Author;
use App\Models\Genre;
use App\Models\Publisher;

class ReferenceCacheService
{
    private array $authors = [];
    private array $genres = [];
    private array $publishers = [];

    public function authorId(string $name): int
    {
        if (!isset($this->authors[$name])) {

            $this->authors[$name] =
                Author::firstOrCreate(['name' => $name])->id;

        }

        return $this->authors[$name];
    }

    public function genreId(string $name): int
    {
        if (!isset($this->genres[$name])) {

            $this->genres[$name] =
                Genre::firstOrCreate(['name' => $name])->id;

        }

        return $this->genres[$name];
    }

    public function publisherId(?string $name): ?int
    {
        if (!$name) {
            return null;
        }

        if (!isset($this->publishers[$name])) {

            $this->publishers[$name] =
                Publisher::firstOrCreate(['name' => $name])->id;
        }

        return $this->publishers[$name];
    }
}
