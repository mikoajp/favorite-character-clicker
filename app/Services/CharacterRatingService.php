<?php

namespace App\Services;

use App\Models\CharacterRating;
use App\Models\User;
use Illuminate\Support\Collection;

class CharacterRatingService
{
    public function rateCharacter(User $user, array $character, int $rating, ?string $comment = null): CharacterRating
    {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5');
        }

        return CharacterRating::updateOrCreate(
            [
                'user_id' => $user->id,
                'character_id' => $character['_id']
            ],
            [
                'character_name' => $character['name'],
                'rating' => $rating,
                'comment' => $comment
            ]
        );
    }

    public function getUserRating(User $user, string $characterId): ?CharacterRating
    {
        return CharacterRating::where('user_id', $user->id)
            ->where('character_id', $characterId)
            ->first();
    }

    public function getCharacterRatings(string $characterId): Collection
    {
        return CharacterRating::where('character_id', $characterId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCharacterAverageRating(string $characterId): array
    {
        $ratings = CharacterRating::where('character_id', $characterId);
        
        return [
            'average_rating' => round($ratings->avg('rating'), 2),
            'total_ratings' => $ratings->count(),
            'rating_distribution' => $this->getRatingDistribution($characterId)
        ];
    }

    public function getTopRatedCharacters(int $limit = 10): Collection
    {
        return CharacterRating::select('character_id', 'character_name')
            ->selectRaw('AVG(rating) as average_rating, COUNT(*) as total_ratings')
            ->groupBy('character_id', 'character_name')
            ->having('total_ratings', '>=', 3) // Minimum 3 ratings to be considered
            ->orderBy('average_rating', 'desc')
            ->orderBy('total_ratings', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getUserRatings(User $user): Collection
    {
        return $user->characterRatings()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function getRatingDistribution(string $characterId): array
    {
        $distribution = CharacterRating::where('character_id', $characterId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Ensure all ratings 1-5 are represented
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($distribution[$i])) {
                $distribution[$i] = 0;
            }
        }

        ksort($distribution);
        return $distribution;
    }

    public function deleteRating(User $user, string $characterId): bool
    {
        return CharacterRating::where('user_id', $user->id)
            ->where('character_id', $characterId)
            ->delete() > 0;
    }
}