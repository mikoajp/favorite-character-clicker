<?php

namespace App\Http\Controllers;

use App\Services\CharacterService;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected GameService $gameService;
    protected CharacterService $characterService;

    public function __construct(GameService $gameService, CharacterService $characterService)
    {
        $this->gameService = $gameService;
        $this->characterService = $characterService;
    }


    public function startGame(Request $request): JsonResponse
    {
        try {
            $numberOfCharacters = $request->input('number_of_characters', 10);
            $characters = $this->gameService->startNewGame($numberOfCharacters);

            if (empty($characters)) {
                return response()->json(['error' => 'Not enough characters available for the game'], 404);
            }

            return response()->json(['characters' => $this->transformCharacters($characters)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    /**
     * Select a character and advance to the next round.
     */
    public function selectCharacterAndAdvanceRound(): JsonResponse
    {
        try {
            $nextRoundCharacters = $this->gameService->selectCharacterAndAdvanceRound();

            return response()->json([
                'characters' => $this->transformCharacters($nextRoundCharacters),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Transform characters for the response.
     */
    private function transformCharacters(array $characters): array
    {
        return array_map(function ($character) {
            return [
                '_id' => $character['_id'],
                'name' => $character['name'],
                'image' => $character['image'],
            ];
        }, $characters);
    }

}
