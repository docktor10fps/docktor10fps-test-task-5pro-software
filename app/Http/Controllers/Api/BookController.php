<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookStoreRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\Response;

class BookController extends Controller
{
    public function __construct(
        private BookService $service
    ) {}

    public function index(): BookCollection
    {
        return new BookCollection(
            $this->service->paginate()
        );
    }

    public function store(BookStoreRequest $request): BookResource
    {
        $book = $this->service->create(
            $request->validated(),
            $request->author_ids ?? [],
            $request->genre_ids ?? []
        );

        return new BookResource($book);
    }

    public function show(Book $book): BookResource
    {
        return new BookResource(
            $this->service->show($book)
        );
    }

    public function update(BookStoreRequest $request, Book $book): BookResource
    {
        $book = $this->service->update(
            $book,
            $request->validated(),
            $request->author_ids ?? [],
            $request->genre_ids ?? []
        );

        return new BookResource($book);
    }

    public function destroy(Book $book): Response
    {
        $this->service->delete($book);

        return response()->noContent();
    }
}
