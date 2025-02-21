<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Soap\SoapServerController;
use App\Http\Controllers\Api\Soap\SapServerController;
use App\Http\Controllers\Api\KapalController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('npks', SoapServerController::class);
Route::get('sap', SapServerController::class);

Route::middleware('check.api.key')->group(function () {
    Route::resource('voyages', KapalController::class)->only([
        'index', 'show', 'store', 'update', 'destroy'
    ]);
});


