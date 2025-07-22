<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameRound;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class GameService
{
    protected CharacterService $characterService;

    public function __construct(CharacterService $characterService)
    {
        $this->characterService = $characterService;
    }

    public function startNewGame(int $numberOfCharacters): array
    {
        // Get random characters from API
        $allCharacters = $this->characterService->getAllCharacters();
        $randomCharacters = collect($allCharacters)->random($numberOfCharacters)->values()->all();

        // Create game record in database
        $game = Game::create([
            'user_id' => Auth::id(),
            'session_id' => Auth::guest() ? Session::getId() : null,
            'total_characters' => $numberOfCharacters,
            'current_round' => 1,
            'characters' => $randomCharacters,
            'status' => 'active'
        ]);

        // Store game ID in session
        Session::put('current_game_id', $game->id);
        Session::put('game_characters', $randomCharacters);
        Session::save(); // Force session save
        
        Log::info('Game started', [
            'game_id' => $game->id,
            'session_id' => Session::getId(),
            'character_count' => count($randomCharacters)
        ]);

        return $this->getCurrentRoundCharacters($game);
    }

    public function selectCharacterAndAdvanceRound(string $selectedCharacterId): array
    {
        $gameId = Session::get('current_game_id');
        Log::info('Selecting character', [
            'character_id' => $selectedCharacterId,
            'session_game_id' => $gameId,
            'session_id' => Session::getId()
        ]);
        
        // Fallback: Try to find the most recent active game if no session
        if (!$gameId) {
            Log::warning('No active game found in session, trying fallback', [
                'session_id' => Session::getId(),
                'all_session_data' => Session::all()
            ]);
            
            // Try to find the most recent active game
            $game = Game::where('status', 'active')
                ->where(function($query) {
                    $query->where('session_id', Session::getId())
                          ->orWhere('user_id', Auth::id());
                })
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($game) {
                Log::info('Found fallback game', ['game_id' => $game->id]);
                Session::put('current_game_id', $game->id);
                Session::save();
                $gameId = $game->id;
            } else {
                Log::error('No active game found anywhere', [
                    'session_id' => Session::getId(),
                    'user_id' => Auth::id()
                ]);
                throw new \Exception('No active game found', 404);
            }
        }

        $game = Game::find($gameId);
        if (!$game || !$game->isActive()) {
            throw new \Exception('Game not found or not active', 404);
        }

        $characters = $game->characters;
        if (count($characters) < 2) {
            throw new \Exception('Not enough characters to continue', 400);
        }

        // Find current round characters (first 2 characters for consistency)
        $currentRoundChars = array_slice($characters, 0, 2);
        $character1 = $currentRoundChars[0];
        $character2 = $currentRoundChars[1];

        // Validate selected character
        $selectedCharacter = null;
        $eliminatedCharacter = null;

        if ($character1['_id'] === $selectedCharacterId) {
            $selectedCharacter = $character1;
            $eliminatedCharacter = $character2;
        } elseif ($character2['_id'] === $selectedCharacterId) {
            $selectedCharacter = $character2;
            $eliminatedCharacter = $character1;
        } else {
            throw new \Exception('Invalid character selection', 400);
        }

        // Record the round
        GameRound::create([
            'game_id' => $game->id,
            'round_number' => $game->current_round,
            'character1_id' => $character1['_id'],
            'character2_id' => $character2['_id'],
            'selected_character_id' => $selectedCharacter['_id'],
            'eliminated_character_id' => $eliminatedCharacter['_id']
        ]);

        // Remove eliminated character and add eliminated to history
        $eliminatedCharacters = $game->eliminated_characters ?? [];
        $eliminatedCharacters[] = $eliminatedCharacter;

        // Keep selected character in its original position
        $remainingCharacters = array_slice($characters, 2); // Remove first 2 characters
        
        // Determine original position of selected character
        $selectedWasFirst = ($character1['_id'] === $selectedCharacterId);
        
        if ($selectedWasFirst) {
            // Selected was on the left (position 0), keep it there
            array_unshift($remainingCharacters, $selectedCharacter);
        } else {
            // Selected was on the right (position 1), add it as second if we have characters
            if (count($remainingCharacters) > 0) {
                array_splice($remainingCharacters, 1, 0, [$selectedCharacter]);
            } else {
                $remainingCharacters[] = $selectedCharacter;
            }
        }

        // Check if game is completed (only 1 character left)
        if (count($remainingCharacters) === 1) {
            $game->update([
                'characters' => $remainingCharacters,
                'eliminated_characters' => $eliminatedCharacters,
                'winner_character_id' => $selectedCharacter['_id'],
                'status' => 'completed',
                'score' => $this->calculateFinalScore($game, count($eliminatedCharacters) + 1)
            ]);

            Session::forget(['current_game_id', 'game_characters']);
            
            return [
                'game_completed' => true,
                'winner' => $selectedCharacter,
                'final_score' => $game->score
            ];
        }

        // Add new random character if we have less than 2 characters
        if (count($remainingCharacters) < 2) {
            $usedCharacterIds = array_merge(
                array_column($remainingCharacters, '_id'),
                array_column($eliminatedCharacters, '_id')
            );
            
            $newCharacter = $this->characterService->getRandomCharacters(1, $usedCharacterIds);
            if (!empty($newCharacter)) {
                $remainingCharacters = array_merge($remainingCharacters, $newCharacter);
            }
        }

        // Update game
        $game->update([
            'characters' => $remainingCharacters,
            'eliminated_characters' => $eliminatedCharacters,
            'current_round' => $game->current_round + 1
        ]);

        Session::put('game_characters', $remainingCharacters);

        return $this->getCurrentRoundCharacters($game->fresh());
    }

    public function getCurrentGame(): ?Game
    {
        $gameId = Session::get('current_game_id');
        return $gameId ? Game::find($gameId) : null;
    }

    public function abandonCurrentGame(): bool
    {
        $game = $this->getCurrentGame();
        if ($game && $game->isActive()) {
            $game->update(['status' => 'abandoned']);
            Session::forget(['current_game_id', 'game_characters']);
            return true;
        }
        return false;
    }

    private function getCurrentRoundCharacters(Game $game): array
    {
        $characters = $game->characters;
        
        if (count($characters) < 2) {
            return [
                'characters' => $characters,
                'round' => $game->current_round,
                'total_characters' => count($characters),
                'game_completed' => count($characters) <= 1
            ];
        }

        // Always return characters in consistent order - first two characters
        $currentRoundChars = array_slice($characters, 0, 2);
        
        // Ensure consistent ordering by adding position index
        $currentRoundChars = array_values($currentRoundChars);
        for ($i = 0; $i < count($currentRoundChars); $i++) {
            $currentRoundChars[$i]['position'] = $i;
            $currentRoundChars[$i]['original_position'] = $i; // Track original position
        }
        
        return [
            'characters' => $currentRoundChars,
            'round' => $game->current_round,
            'total_characters' => count($characters),
            'game_completed' => false
        ];
    }

    private function calculateFinalScore(Game $game, int $totalRounds): int
    {
        $baseScore = $game->total_characters * 10;
        $roundBonus = $totalRounds * 5;
        $timeBonus = max(0, 100 - ($game->created_at->diffInMinutes(now()) * 2));
        
        return $baseScore + $roundBonus + $timeBonus;
    }

    public function getGameStats(?User $user = null): array
    {
        $query = Game::where('status', 'completed');
        
        if ($user) {
            $query->where('user_id', $user->id);
        }

        $games = $query->get();

        return [
            'total_games' => $games->count(),
            'average_score' => $games->avg('score') ?? 0,
            'highest_score' => $games->max('score') ?? 0,
            'total_rounds_played' => GameRound::whereIn('game_id', $games->pluck('id'))->count()
        ];
    }
}
