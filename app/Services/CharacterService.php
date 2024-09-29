<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CharacterService
{
    protected int $totalPages = 30;
    protected int $limit = 5;
    protected string $cacheKeyAllCharacters = "all_characters";

    /**
     * Get characters from a specific page.
     */
    public function getCharacters(int $page): array
    {
        $cacheKey = "characters_page_{$page}_limit_{$this->limit}";

        return Cache::remember($cacheKey, 600, function () use ($page) {
            $response = Http::get("https://starwars-databank-server.vercel.app/api/v1/characters", [
                'page' => $page,
                'limit' => $this->limit,
            ]);

            return $response->ok() ? $response->json('data') : [];
        });
    }

    /**
     * Get a specific number of random characters, excluding given IDs.
     */
    public function getRandomCharacters(int $count, array $excludeIds = []): array
    {
        $allCharacters = $this->getFilteredCharacters($excludeIds);

        if (count($allCharacters) < $count) {
            return [];
        }

        $randomKeys = array_rand($allCharacters, $count);
        return array_map(fn($key) => $allCharacters[$key], (array) $randomKeys);
    }

    /**
     * Get all characters and store them in cache.
     */
    public function getAllCharacters(): array
    {
        return Cache::remember($this->cacheKeyAllCharacters, 600, function () {
            $allCharacters = [];
            $page = 1;

            do {
                $pageCharacters = $this->getCharacters($page);
                if (empty($pageCharacters)) {
                    break;
                }
                $allCharacters = array_merge($allCharacters, $pageCharacters);
                $page++;
            } while ($page <= $this->totalPages);

            return $allCharacters;
        });
    }

    /**
     * Get additional characters for the next round, excluding given IDs.
     */
    public function getAdditionalCharactersForNextRound(array $excludeIds, int $count): array
    {
        return $this->getRandomCharacters($count, $excludeIds);
    }

    /**
     * Get a character by its ID.
     */
    public function getCharacterById(string $characterId): ?array
    {
        $allCharacters = $this->getAllCharacters();
        return collect($allCharacters)->firstWhere('_id', $characterId);
    }

    /**
     * Get filtered characters excluding specific IDs.
     */
    protected function getFilteredCharacters(array $excludeIds = []): array
    {
        return collect($this->getAllCharacters())
            ->filter(fn($character) => !in_array($character['_id'], $excludeIds))
            ->values()
            ->toArray();
    }
}
