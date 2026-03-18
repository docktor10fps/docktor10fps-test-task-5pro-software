<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BookRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Book::with([
            'authors:id,name',
            'publisher:id,name',
            'genres:id,name'
        ])->paginate($perPage);
    }

    public function find(Book $book): Book
    {
        return $book->load([
            'authors:id,name',
            'publisher:id,name',
            'genres:id,name'
        ]);
    }

    public function create(array $data): Book
    {
        return Book::create($data);
    }

    public function update(Book $book, array $data): Book
    {
        $book->update($data);

        return $book;
    }

    public function delete(Book $book): void
    {
        $book->delete();
    }

    public function syncAuthors(Book $book, array $authorIds): void
    {
        $book->authors()->sync($authorIds);
    }

    public function syncGenres(Book $book, array $genreIds): void
    {
        $book->genres()->sync($genreIds);
    }
}
