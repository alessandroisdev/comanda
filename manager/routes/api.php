<?php

use App\Http\Controllers\Api\LicenseApiController;
use Illuminate\Support\Facades\Route;

Route::post('/licenses/generate', [LicenseApiController::class, 'generate']);
Route::post('/licenses/renew', [LicenseApiController::class, 'renew']);
Route::post('/licenses/suspend', [LicenseApiController::class, 'suspend']);
Route::post('/licenses/cancel', [LicenseApiController::class, 'cancel']);
Route::post('/licenses/activate', [LicenseApiController::class, 'activate']);
Route::get('/licenses/{uuid}', [LicenseApiController::class, 'show']);
Route::get('/installations/{uuid}', [LicenseApiController::class, 'showInstallation']);
Route::get('/modules', [LicenseApiController::class, 'modules']);
