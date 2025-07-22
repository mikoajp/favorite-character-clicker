<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CharacterService
{
    protected int $totalPages = 90;
    protected int $limit = 10;
    protected string $cacheKeyAllCharacters = "all_characters";

    /**
     * Get characters from a specific page.
     */
    public function getCharacters(int $page): array
    {
        $cacheKey = "characters_page_{$page}_limit_{$this->limit}";

        return Cache::remember($cacheKey, 3600, function () use ($page) {
            try {
                $response = Http::timeout(10)
                    ->retry(3, 1000)
                    ->get("https://starwars-databank-server.vercel.app/api/v1/characters", [
                        'page' => $page,
                        'limit' => $this->limit,
                    ]);

                if ($response->successful()) {
                    return $response->json('data') ?? [];
                }
                
                Log::warning("Star Wars API request failed", [
                    'status' => $response->status(),
                    'page' => $page,
                    'response' => $response->body()
                ]);
                
                return $this->getFallbackCharacters($page);
            } catch (\Exception $e) {
                Log::error("Star Wars API timeout/error", [
                    'page' => $page,
                    'error' => $e->getMessage()
                ]);
                
                return $this->getFallbackCharacters($page);
            }
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
    public function getAllCharacters(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget($this->cacheKeyAllCharacters);
        }

        return Cache::remember($this->cacheKeyAllCharacters, 3600, function () {
            $allCharacters = [];
            $page = 1;
            $maxPages = min($this->totalPages, 10); // Limit initial load to 10 pages

            do {
                $pageCharacters = $this->getCharacters($page);
                if (empty($pageCharacters)) {
                    break;
                }
                $allCharacters = array_merge($allCharacters, $pageCharacters);
                $page++;
                
                // Add small delay to prevent overwhelming the API
                if ($page <= $maxPages) {
                    usleep(100000); // 0.1 second delay
                }
            } while ($page <= $maxPages && count($allCharacters) < 100);

            return $allCharacters;
        });
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

    /**
     * Get fallback characters when API is unavailable.
     */
    protected function getFallbackCharacters(int $page): array
    {
        $fallbackCharacters = [
            [
                '_id' => 'luke-skywalker',
                'name' => 'Luke Skywalker',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/luke-skywalker-main_5a38c454_461eebf5.jpeg'
            ],
            [
                '_id' => 'darth-vader',
                'name' => 'Darth Vader',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/darth-vader-main_4560aff7_68916052.jpeg'
            ],
            [
                '_id' => 'princess-leia',
                'name' => 'Princess Leia',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/leia-organa-main_5a38c454_461eebf5.jpeg'
            ],
            [
                '_id' => 'han-solo',
                'name' => 'Han Solo',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/han-solo-main_5a38c454_461eebf5.jpeg'
            ],
            [
                '_id' => 'chewbacca',
                'name' => 'Chewbacca',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/chewbacca-main_5a38c454_461eebf5.jpeg'
            ],
            [
                '_id' => 'obi-wan-kenobi',
                'name' => 'Obi-Wan Kenobi',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/obi-wan-kenobi-main_5a38c454_461eebf5.jpeg'
            ],
            [
                '_id' => 'yoda',
                'name' => 'Yoda',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/yoda-main_5a38c454_461eebf5.jpeg'
            ],
            [
                '_id' => 'r2-d2',
                'name' => 'R2-D2',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/r2-d2-main_5a38c454_461eebf5.jpeg'
            ],
            [
                '_id' => 'c-3po',
                'name' => 'C-3PO',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/c-3po-main_5a38c454_461eebf5.jpeg'
            ],
            [
                '_id' => 'boba-fett',
                'name' => 'Boba Fett',
                'image' => 'https://lumiere-a.akamaihd.net/v1/images/boba-fett-main_5a38c454_461eebf5.jpeg'
            ]
        ];

        // Return subset based on page
        $startIndex = ($page - 1) * $this->limit;
        return array_slice($fallbackCharacters, $startIndex, $this->limit);
    }
}
