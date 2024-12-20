<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('countries', [\App\Http\Controllers\Api\CountryController::class, 'get']);
Route::get('leagues', [\App\Http\Controllers\Api\LeagueController::class, 'get']);
Route::get('clubs', [\App\Http\Controllers\Api\ClubController::class, 'get']);
Route::get('seasons', [\App\Http\Controllers\Api\SeasonController::class, 'get']);
Route::get('games', [\App\Http\Controllers\Api\GameController::class, 'get']);
Route::get('players', [\App\Http\Controllers\Api\PlayerController::class, 'get']);
Route::get('filters', [\App\Http\Controllers\Api\FilterController::class, 'get']);

Route::group(['prefix' => 'table'], function () {
    Route::get('best-clubs-season', [\App\Http\Controllers\Api\ViewTableController::class, 'bestClubSeason']);
    Route::get('best-player-season', [\App\Http\Controllers\Api\ViewTableController::class, 'bestPlayerSeason']);
    Route::get('top-game', [\App\Http\Controllers\Api\ViewTableController::class, 'topGame']);
    Route::get('goals-player-to-club', [\App\Http\Controllers\Api\ViewTableController::class, 'goalsPlayerToClub']);
    Route::get('best-player-on-position', [\App\Http\Controllers\Api\ViewTableController::class, 'betsPlayersOnPosition']);
});
