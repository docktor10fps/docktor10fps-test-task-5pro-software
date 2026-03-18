<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookImportController;
use Illuminate\Support\Facades\Route;

Route::apiResource('books', BookController::class);
Route::post('books/import', BookImportController::class);
