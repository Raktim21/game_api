<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'me']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user-details/{id}', [UserController::class, 'show']);
    
    Route::controller(GameController::class)->prefix('games')->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/start-new', 'startNewGames');
        Route::post('/stop/{uuid}', 'stopGame');

    });
    Route::get('/game-leader-board', [GameController::class, 'gameLeaderBoard']);
});

