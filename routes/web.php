<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/web/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

