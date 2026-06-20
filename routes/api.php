<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ExtractionController;
use Illuminate\Support\Facades\Route;

Route::post('/extractions', [ExtractionController::class, 'store']);
Route::get('/extractions/{id}', [ExtractionController::class, 'show']);
