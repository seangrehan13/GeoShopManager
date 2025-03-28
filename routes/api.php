<?php

use App\Http\Controllers\StoreAreaController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreDeliveryController;
use Illuminate\Support\Facades\Route;

Route::post('/store/create', [StoreController::class, 'create']);
Route::get('/stores/near/{postcode}', [StoreAreaController::class, 'show']);
Route::get('/stores/delivering-to/{postcode}', [StoreDeliveryController::class, 'show']);
