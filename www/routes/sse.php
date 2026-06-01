<?php

use App\Http\Controllers\SSE\SseController;
use App\Http\Controllers\SSE\SseTestController;
use Illuminate\Support\Facades\Route;

Route::get('/sse/test', [SseTestController::class, 'stream'])
    ->name('sse.test');

Route::get('/sse/stream/{channel}', [SseController::class, 'stream'])
    ->name('sse.stream');
