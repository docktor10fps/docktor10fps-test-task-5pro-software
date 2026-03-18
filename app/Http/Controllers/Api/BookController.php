<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookStoreRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\Response;

/**
 * @OA\Info(
 *     title="Book API",
 *     version="1.0.0",
 *     description="Мікросервісне API для управління базою книг"
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="API Server"
 * )
 *
 * @OA\Schema(
 *     schema="BookList",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="The Great Gatsby"),
 *     @OA\Property(property="authors", type="array", @OA\Items(type="string"), example={"F. Scott Fitzgerald"}),
 *     @OA\Property(property="publisher", type="string", example="Scribner", nullable=true),
 *     @OA\Property(property="year", type="string", example="1925", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="BookDetail",
 *     allOf={@OA\Schema(ref="#/components/schemas/BookList")},
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="edition", type="integer", nullable=true, example=1),
 *     @OA\Property(property="pages", type="integer", nullable=true, example=180),
 *     @OA\Property(property="format", type="string", nullable=true, example="Hardcover"),
 *     @OA\Property(property="country", type="string", nullable=true, example="USA"),
 *     @OA\Property(property="isbn", type="string", nullable=true, example="9780743273565"),
 *     @OA\Property(property="genres", type="array", @OA\Items(type="string"), example={"Novel", "Fiction"})
 * )
 *
 * @OA\Schema(
 *     schema="BookStoreRequest",
 *     required={"title"},
 *     @OA\Property(property="title", type="string", example="The Great Gatsby"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="edition", type="integer", nullable=true, example=1),
 *     @OA\Property(property="isbn", type="string", nullable=true, example="9780743273565"),
 *     @OA\Property(property="published_date", type="string", format="date", nullable=true, example="1925-04-10"),
 *     @OA\Property(property="pages", type="integer", nullable=true, example=180),
 *     @OA\Property(property="format", type="string", nullable=true, example="Hardcover"),
 *     @OA\Property(property="country", type="string", nullable=true, example="USA"),
 *     @OA\Property(property="publisher_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="author_ids", type="array", @OA\Items(type="integer"), nullable=true, example={1, 2}),
 *     @OA\Property(property="genre_ids", type="array", @OA\Items(type="integer"), nullable=true, example={1, 2})
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=5),
 *     @OA\Property(property="per_page", type="integer", example=10),
 *     @OA\Property(property="total", type="integer", example=50)
 * )
 */
class BookController extends Controller
{
    public function __construct(
        private BookService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/books",
     *     summary="Список книг",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успішно",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BookList")),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     )
     * )
     */
    public function index(): BookCollection
    {
        return new BookCollection(
            $this->service->paginate()
        );
    }

    /**
     * @OA\Post(
     *     path="/books",
     *     summary="Створення книги",
     *     tags={"Books"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BookStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Книгу створено",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/BookDetail"))
     *     ),
     *     @OA\Response(response=422, description="Помилка валідації")
     * )
     */
    public function store(BookStoreRequest $request): BookResource
    {
        $book = $this->service->create(
            $request->validated(),
            $request->author_ids ?? [],
            $request->genre_ids ?? []
        );

        return new BookResource($book);
    }

    /**
     * @OA\Get(
     *     path="/books/{id}",
     *     summary="Детальна інформація про книгу",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успішно",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/BookDetail"))
     *     ),
     *     @OA\Response(response=404, description="Книгу не знайдено")
     * )
     */
    public function show(Book $book): BookResource
    {
        return new BookResource(
            $this->service->show($book)
        );
    }

    /**
     * @OA\Put(
     *     path="/books/{id}",
     *     summary="Оновлення книги",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BookStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Книгу оновлено",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/BookDetail"))
     *     ),
     *     @OA\Response(response=404, description="Книгу не знайдено"),
     *     @OA\Response(response=422, description="Помилка валідації")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/books/{id}",
     *     summary="Видалення книги",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="Книгу видалено"),
     *     @OA\Response(response=404, description="Книгу не знайдено")
     * )
     */
    public function destroy(Book $book): Response
    {
        $this->service->delete($book);

        return response()->noContent();
    }
}
