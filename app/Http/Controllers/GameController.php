<?php

namespace App\Http\Controllers;

use App\Services\CharacterService;
use App\Services\GameService;
use App\Services\FavoriteCharacterService;
use App\Services\CharacterRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    protected GameService $gameService;
    protected CharacterService $characterService;
    protected FavoriteCharacterService $favoriteService;
    protected CharacterRatingService $ratingService;

    public function __construct(
        GameService $gameService, 
        CharacterService $characterService,
        FavoriteCharacterService $favoriteService,
        CharacterRatingService $ratingService
    ) {
        $this->gameService = $gameService;
        $this->characterService = $characterService;
        $this->favoriteService = $favoriteService;
        $this->ratingService = $ratingService;
    }

    public function startGame(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'number_of_characters' => 'integer|min:4|max:50'
            ]);

            $numberOfCharacters = $request->input('number_of_characters', 10);
            $gameData = $this->gameService->startNewGame($numberOfCharacters);

            if (empty($gameData['characters'])) {
                return response()->json(['error' => 'Not enough characters available for the game'], 404);
            }

            return response()->json([
                'characters' => $this->transformCharacters($gameData['characters']),
                'round' => $gameData['round'],
                'total_characters' => $gameData['total_characters'],
                'game_completed' => $gameData['game_completed']
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function selectCharacterAndAdvanceRound(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'character_id' => 'required|string'
            ]);

            $selectedCharacterId = $request->input('character_id');
            $result = $this->gameService->selectCharacterAndAdvanceRound($selectedCharacterId);

            if (isset($result['game_completed']) && $result['game_completed']) {
                return response()->json([
                    'game_completed' => true,
                    'winner' => $this->transformCharacter($result['winner']),
                    'final_score' => $result['final_score']
                ]);
            }

            return response()->json([
                'characters' => $this->transformCharacters($result['characters']),
                'round' => $result['round'],
                'total_characters' => $result['total_characters'],
                'game_completed' => $result['game_completed']
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function getCurrentGame(): JsonResponse
    {
        try {
            $game = $this->gameService->getCurrentGame();
            
            if (!$game) {
                return response()->json(['error' => 'No active game found'], 404);
            }

            return response()->json([
                'game_id' => $game->id,
                'round' => $game->current_round,
                'total_characters' => $game->getRemainingCharactersCount(),
                'status' => $game->status,
                'score' => $game->score
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function abandonGame(): JsonResponse
    {
        try {
            $success = $this->gameService->abandonCurrentGame();
            
            if (!$success) {
                return response()->json(['error' => 'No active game to abandon'], 404);
            }

            return response()->json(['message' => 'Game abandoned successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getGameStats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $stats = $this->gameService->getGameStats($user);

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLeaderboard(): JsonResponse
    {
        try {
            $topPlayers = \App\Models\User::select('id', 'name')
                ->withCount(['games as completed_games' => function ($query) {
                    $query->where('status', 'completed');
                }])
                ->withSum(['games as total_score' => function ($query) {
                    $query->where('status', 'completed');
                }], 'score')
                ->having('completed_games', '>', 0)
                ->orderBy('total_score', 'desc')
                ->orderBy('completed_games', 'desc')
                ->limit(10)
                ->get();

            return response()->json(['leaderboard' => $topPlayers]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Transform characters for the response.
     */
    private function transformCharacters(array $characters): array
    {
        return array_map([$this, 'transformCharacter'], $characters);
    }

    /**
     * Transform single character for the response.
     */
    private function transformCharacter(array $character): array
    {
        $transformed = [
            '_id' => $character['_id'],
            'name' => $character['name'],
            'image' => $character['image'],
        ];

        // Add user-specific data if authenticated
        if (Auth::check()) {
            $user = Auth::user();
            $transformed['is_favorited'] = $this->favoriteService->isCharacterFavorited($user, $character['_id']);
            $userRating = $this->ratingService->getUserRating($user, $character['_id']);
            $transformed['user_rating'] = $userRating ? $userRating->rating : null;
        }

        // Add average rating
        $ratingData = $this->ratingService->getCharacterAverageRating($character['_id']);
        $transformed['average_rating'] = $ratingData['average_rating'];
        $transformed['total_ratings'] = $ratingData['total_ratings'];

        return $transformed;
    }
}
