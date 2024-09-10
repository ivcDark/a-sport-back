<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function () {
    return response()->json(['data' => 'Тут какая-либо инфомрация', 'status' => 'success']);
});

Route::get('countries', [\App\Http\Controllers\Api\CountryController::class, 'get']);
Route::get('leagues', [\App\Http\Controllers\Api\LeagueController::class, 'get']);
Route::get('clubs', [\App\Http\Controllers\Api\ClubController::class, 'get']);
Route::get('seasons', [\App\Http\Controllers\Api\SeasonController::class, 'get']);
Route::get('games', [\App\Http\Controllers\Api\GameController::class, 'get']);
