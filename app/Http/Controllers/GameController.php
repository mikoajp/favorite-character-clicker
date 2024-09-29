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


    /**
     * Start the game by preparing 14 random characters.
     *
     * @return JsonResponse
     */
    public function startGame(): JsonResponse
    {
        $characters = $this->characterService->getRandomCharacters(14);

        if (empty($characters)) {
            return response()->json(['error' => 'Not enough characters available for the game'], 404);
        }

        session(['selected_characters' => []]);

        return response()->json(['characters' => $characters]);
    }

    /**
     * Select a character and advance to the next round.
     */
    public function selectCharacterAndAdvanceRound(Request $request): JsonResponse
    {
        try {
            $selectedCharacterId = $request->input('character_id');
            $nextRoundCharacters = $this->gameService->selectCharacterAndAdvanceRound($selectedCharacterId);

            return response()->json([
                'characters' => $this->transformCharacters($nextRoundCharacters),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
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
