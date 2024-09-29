<?php
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;


Route::get('/random-character', [CharacterController::class, 'getOneRandomCharacter']);
Route::get('/start-game', [GameController::class, 'startGame']);
Route::post('/select-character', [GameController::class, 'selectCharacterAndAdvanceRound']);
