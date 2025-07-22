<?php

namespace App\Http\Controllers;

use App\Services\FavoriteCharacterService;
use App\Services\CharacterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FavoriteCharacterController extends Controller
{
    protected FavoriteCharacterService $favoriteService;
    protected CharacterService $characterService;

    public function __construct(FavoriteCharacterService $favoriteService, CharacterService $characterService)
    {
        $this->middleware('auth');
        $this->favoriteService = $favoriteService;
        $this->characterService = $characterService;
    }

    public function addToFavorites(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'character_id' => 'required|string',
                'character_name' => 'required|string',
                'character_image' => 'nullable|string'
            ]);

            $character = [
                '_id' => $request->input('character_id'),
                'name' => $request->input('character_name'),
                'image' => $request->input('character_image')
            ];

            $favorite = $this->favoriteService->addToFavorites(Auth::user(), $character);

            return response()->json([
                'message' => 'Character added to favorites',
                'favorite' => $favorite
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeFromFavorites(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'character_id' => 'required|string'
            ]);

            $success = $this->favoriteService->removeFromFavorites(
                Auth::user(), 
                $request->input('character_id')
            );

            if (!$success) {
                return response()->json(['error' => 'Character not found in favorites'], 404);
            }

            return response()->json(['message' => 'Character removed from favorites']);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserFavorites(): JsonResponse
    {
        try {
            $favorites = $this->favoriteService->getUserFavorites(Auth::user());

            return response()->json(['favorites' => $favorites]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMostFavorited(): JsonResponse
    {
        try {
            $mostFavorited = $this->favoriteService->getMostFavoritedCharacters(20);

            return response()->json(['most_favorited' => $mostFavorited]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkIfFavorited(string $characterId): JsonResponse
    {
        try {
            $isFavorited = $this->favoriteService->isCharacterFavorited(Auth::user(), $characterId);

            return response()->json(['is_favorited' => $isFavorited]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}