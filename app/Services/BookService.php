<?php

namespace App\Services;

use App\Models\Book;
use App\Repositories\BookRepository;
use Illuminate\Support\Facades\DB;

class BookService
{
    public function __construct(
        private BookRepository $repository
    ) {}

    public function paginate(): mixed
    {
        return $this->repository->paginate();
    }

    public function show(Book $book): Book
    {
        return $this->repository->find($book);
    }

    public function create(array $data, array $authorIds = [], array $genreIds = []): Book
    {
        return DB::transaction(function () use ($data, $authorIds, $genreIds) {

            $book = $this->repository->create($data);

            if ($authorIds) {
                $this->repository->syncAuthors($book, $authorIds);
            }

            if ($genreIds) {
                $this->repository->syncGenres($book, $genreIds);
            }

            return $this->repository->find($book);
        });
    }

    public function update(Book $book, array $data, array $authorIds = [], array $genreIds = []): Book
    {
        return DB::transaction(function () use ($book, $data, $authorIds, $genreIds) {

            $book = $this->repository->update($book, $data);

            if ($authorIds) {
                $this->repository->syncAuthors($book, $authorIds);
            }

            if ($genreIds) {
                $this->repository->syncGenres($book, $genreIds);
            }

            return $this->repository->find($book);
        });
    }

    public function delete(Book $book): void
    {
        $this->repository->delete($book);
    }
}
