<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/notes', [ApiController::class, 'index'])->name('api.index');
Route::post('/notes', [ApiController::class, 'store'])->name('api.store');
Route::get('/notes/{id}', [ApiController::class, 'show'])->name('api.show');
Route::put('/notes/{id}', [ApiController::class, 'update'])->name('api.update');
Route::delete('/notes/{id}', [ApiController::class, 'destroy'])->name('api.destroy');
