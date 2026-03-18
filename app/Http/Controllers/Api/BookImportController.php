<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportBooksRequest;
use App\Jobs\ImportBooksFromCsv;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BookImportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/books/import",
     *     summary="Імпорт книг з CSV",
     *     tags={"Books"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="CSV файл (максимум 10 МБ)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Файл прийнято, імпорт виконується асинхронно",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="queued"),
     *             @OA\Property(property="path", type="string", example="imports/books.csv")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Помилка валідації")
     * )
     */
    public function __invoke(ImportBooksRequest $request): JsonResponse
    {
        $path = $request->file('file')->store('imports');

        ImportBooksFromCsv::dispatch(Storage::path($path));

        return response()->json([
            'status' => 'queued',
            'path'   => $path,
        ], Response::HTTP_ACCEPTED);
    }
}
