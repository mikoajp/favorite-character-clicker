<?php
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\FavoriteCharacterController;
use App\Http\Controllers\CharacterRatingController;
use Illuminate\Support\Facades\Route;

// Character routes
Route::get('/random-character', [CharacterController::class, 'getOneRandomCharacter']);

// Game routes
Route::post('/start-game', [GameController::class, 'startGame']);
Route::post('/select-character', [GameController::class, 'selectCharacterAndAdvanceRound']);
Route::get('/current-game', [GameController::class, 'getCurrentGame']);
Route::post('/abandon-game', [GameController::class, 'abandonGame']);
Route::get('/game-stats', [GameController::class, 'getGameStats']);
Route::get('/leaderboard', [GameController::class, 'getLeaderboard']);

// Favorite characters routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::post('/favorites', [FavoriteCharacterController::class, 'addToFavorites']);
    Route::delete('/favorites', [FavoriteCharacterController::class, 'removeFromFavorites']);
    Route::get('/favorites', [FavoriteCharacterController::class, 'getUserFavorites']);
    Route::get('/favorites/check/{characterId}', [FavoriteCharacterController::class, 'checkIfFavorited']);
});

// Public favorite routes
Route::get('/favorites/most-favorited', [FavoriteCharacterController::class, 'getMostFavorited']);

// Character rating routes
Route::middleware('auth')->group(function () {
    Route::post('/ratings', [CharacterRatingController::class, 'rateCharacter']);
    Route::get('/ratings/user/{characterId}', [CharacterRatingController::class, 'getUserRating']);
    Route::get('/ratings/user', [CharacterRatingController::class, 'getUserRatings']);
    Route::delete('/ratings/{characterId}', [CharacterRatingController::class, 'deleteRating']);
});

// Public rating routes
Route::get('/ratings/{characterId}', [CharacterRatingController::class, 'getCharacterRatings']);
Route::get('/ratings/{characterId}/average', [CharacterRatingController::class, 'getCharacterAverageRating']);
Route::get('/ratings/top-rated', [CharacterRatingController::class, 'getTopRated']);
