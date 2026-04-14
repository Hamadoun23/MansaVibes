<?php

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum'])->name('api.v1.')->group(function (): void {
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('orders', OrderController::class);
});
