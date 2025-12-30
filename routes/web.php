<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'DLS Calculator API',
        'version' => '1.0.0',
        'endpoints' => [
            'POST /api/dls/calculate-target' => 'Calculate DLS target',
            'POST /api/dls/over-by-over-table' => 'Get over-by-over table',
            'POST /api/dls/ball-by-ball-table' => 'Get ball-by-ball table',
            'POST /api/dls/generate-report' => 'Generate match report',
        ]
    ]);
});


