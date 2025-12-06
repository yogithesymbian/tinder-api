<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PeopleController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// protected
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('people', [PeopleController::class, 'index']);
    Route::post('people/{people}/like', [PeopleController::class, 'like']);
    Route::post('people/{people}/dislike', [PeopleController::class, 'dislike']);
    Route::get('liked', [PeopleController::class, 'likedList']);
});
