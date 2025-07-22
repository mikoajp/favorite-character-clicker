<?php

namespace App\Services;

use App\Models\FavoriteCharacter;
use App\Models\User;
use Illuminate\Support\Collection;

class FavoriteCharacterService
{
    public function addToFavorites(User $user, array $character): FavoriteCharacter
    {
        return FavoriteCharacter::updateOrCreate(
            [
                'user_id' => $user->id,
                'character_id' => $character['_id']
            ],
            [
                'character_name' => $character['name'],
                'character_image' => $character['image'] ?? null
            ]
        );
    }

    public function removeFromFavorites(User $user, string $characterId): bool
    {
        return FavoriteCharacter::where('user_id', $user->id)
            ->where('character_id', $characterId)
            ->delete() > 0;
    }

    public function getUserFavorites(User $user): Collection
    {
        return $user->favoriteCharacters()->orderBy('created_at', 'desc')->get();
    }

    public function isCharacterFavorited(User $user, string $characterId): bool
    {
        return FavoriteCharacter::where('user_id', $user->id)
            ->where('character_id', $characterId)
            ->exists();
    }

    public function getMostFavoritedCharacters(int $limit = 10): Collection
    {
        return FavoriteCharacter::select('character_id', 'character_name', 'character_image')
            ->selectRaw('COUNT(*) as favorites_count')
            ->groupBy('character_id', 'character_name', 'character_image')
            ->orderBy('favorites_count', 'desc')
            ->limit($limit)
            ->get();
    }
}