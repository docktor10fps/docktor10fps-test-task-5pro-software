<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookStoreRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Response;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): BookCollection
    {
        $books = Book::with(['authors', 'publisher'])->paginate(10);

        return new BookCollection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookStoreRequest $request): BookResource
    {
        $book = Book::create($request->validated());

        if ($request->has('author_ids')) {
            $book->authors()->sync($request->author_ids);
        }

        if ($request->has('genre_ids')) {
            $book->genres()->sync($request->genre_ids);
        }

        return new BookResource($book->load(['authors', 'publisher']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): BookResource
    {
        return new BookResource($book->load(['authors', 'publisher', 'genres']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookStoreRequest $request, Book $book): BookResource
    {
        $book->update($request->validated());

        if ($request->has('author_ids')) {
            $book->authors()->sync($request->author_ids);
        }

        if ($request->has('genre_ids')) {
            $book->genres()->sync($request->genre_ids);
        }

        return new BookResource($book->load(['authors', 'publisher']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): Response
    {
        $book->delete();

        return response()->noContent();
    }
}
