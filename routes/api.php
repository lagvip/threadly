<?php

use App\Http\Controllers\Api\GhnWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/ghn/webhook', [GhnWebhookController::class, 'handle'])->name('api.ghn.webhook');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
