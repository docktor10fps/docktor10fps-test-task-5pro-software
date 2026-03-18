<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportBooksRequest;
use App\Jobs\ImportBooksFromCsv;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BookImportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ImportBooksRequest $request): JsonResponse
    {
        $path = $request->file('file')->store('imports');

        ImportBooksFromCsv::dispatch($path);

        return response()->json([
            'status' => 'queued',
            'path' => $path,
        ], Response::HTTP_ACCEPTED);
    }
}
