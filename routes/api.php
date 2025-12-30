<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DLSCalculatorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('dls')->group(function () {
    Route::post('/calculate-target', [DLSCalculatorController::class, 'calculateTarget']);
    Route::post('/over-by-over-table', [DLSCalculatorController::class, 'overByOverTable']);
    Route::post('/ball-by-ball-table', [DLSCalculatorController::class, 'ballByBallTable']);
    Route::post('/generate-report', [DLSCalculatorController::class, 'generateReport']);
    Route::post('/par-score-table', [DLSCalculatorController::class, 'parScoreTable']);
});

// Commented out - not needed for DLS Calculator API
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
