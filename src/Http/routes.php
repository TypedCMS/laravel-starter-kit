<?php

use Illuminate\Support\Facades\Route;
use TypedCMS\LaravelStarterKit\Http\Controllers\ClearCacheController;
use TypedCMS\LaravelStarterKit\Http\Controllers\DisplayCodeController;

Route::post('webhooks/clear-cache', ClearCacheController::class);

if (in_array(config('app.env'), ['local', 'testing'], true)) {
    Route::get('display-code', DisplayCodeController::class);
}
