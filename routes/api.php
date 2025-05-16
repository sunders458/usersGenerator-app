<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Public routes
Route::get('/users/generate', [UserController::class, 'generate']);
Route::post('/users/batch', [UserController::class, 'batchImport']);
Route::post('/auth', [AuthController::class, 'authenticate']);

// Protected routes
Route::middleware('jwt.auth')->group(function () {
    Route::get('/users/me', [UserController::class, 'me']);
    Route::get('/users/{username}', [UserController::class, 'show']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});
