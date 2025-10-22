<?php

namespace App\Http\Controllers;

use App\Services\CharacterService;
use Illuminate\Http\JsonResponse;

class CharacterController extends Controller
{
    protected CharacterService $characterService;

    public function __construct(CharacterService $characterService)
    {
        $this->characterService = $characterService;
    }

    public function showHomePage()
    {
        $characters = $this->characterService->getRandomCharacters(2);
        return view('welcome', ['characters' => $characters]);
    }

    public function getOneRandomCharacter(): JsonResponse
    {
        $characters = $this->characterService->getRandomCharacters(1);

        if (empty($characters)) {
            return response()->json(['error' => 'No character found'], 404);
        }

        // Return single character object, not array
        return response()->json($characters[0]);
    }
}
