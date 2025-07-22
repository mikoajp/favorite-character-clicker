<?php

namespace App\Http\Controllers;

use App\Services\CharacterRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CharacterRatingController extends Controller
{
    protected CharacterRatingService $ratingService;

    public function __construct(CharacterRatingService $ratingService)
    {
        $this->middleware('auth')->except(['getCharacterRatings', 'getCharacterAverageRating', 'getTopRated']);
        $this->ratingService = $ratingService;
    }

    public function rateCharacter(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'character_id' => 'required|string',
                'character_name' => 'required|string',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:500'
            ]);

            $character = [
                '_id' => $request->input('character_id'),
                'name' => $request->input('character_name')
            ];

            $rating = $this->ratingService->rateCharacter(
                Auth::user(),
                $character,
                $request->input('rating'),
                $request->input('comment')
            );

            return response()->json([
                'message' => 'Character rated successfully',
                'rating' => $rating
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserRating(string $characterId): JsonResponse
    {
        try {
            $rating = $this->ratingService->getUserRating(Auth::user(), $characterId);

            return response()->json(['user_rating' => $rating]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCharacterRatings(string $characterId): JsonResponse
    {
        try {
            $ratings = $this->ratingService->getCharacterRatings($characterId);

            return response()->json(['ratings' => $ratings]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCharacterAverageRating(string $characterId): JsonResponse
    {
        try {
            $averageData = $this->ratingService->getCharacterAverageRating($characterId);

            return response()->json($averageData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getTopRated(): JsonResponse
    {
        try {
            $topRated = $this->ratingService->getTopRatedCharacters(20);

            return response()->json(['top_rated' => $topRated]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserRatings(): JsonResponse
    {
        try {
            $ratings = $this->ratingService->getUserRatings(Auth::user());

            return response()->json(['user_ratings' => $ratings]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function deleteRating(string $characterId): JsonResponse
    {
        try {
            $success = $this->ratingService->deleteRating(Auth::user(), $characterId);

            if (!$success) {
                return response()->json(['error' => 'Rating not found'], 404);
            }

            return response()->json(['message' => 'Rating deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}