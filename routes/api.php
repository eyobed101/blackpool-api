<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SportController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\BetController;


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

Route::get('/games', [SportController::class, 'getGames']);

Route::get('/scores', [ScoreController::class, 'getScores']);

Route::post('/place-bet',  [BetController::class, 'placeBet']);

