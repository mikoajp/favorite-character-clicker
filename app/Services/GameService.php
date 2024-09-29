<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class GameService
{
    protected CharacterService $characterService;

    public function __construct(CharacterService $characterService)
    {
        $this->characterService = $characterService;
    }


    /**
     * Start a new game by getting 14 random characters and caching them.
     *
     * @return array
     */
    public function startNewGame(): array
    {

        $characters = $this->characterService->getRandomCharacters(14);

        Session::put('selected_characters', []);

        return $characters;
    }

    /**
     * Select a character by ID and advance to the next round.
     */
    public function selectCharacterAndAdvanceRound(string $characterId): array
    {
        $selectedCharacter = $this->characterService->getCharacterById($characterId);
        $previouslySelectedCharacters = $this->updateSelectedCharactersInSession($selectedCharacter);

        $excludeIds = collect($previouslySelectedCharacters)->pluck('_id')->all();
        $additionalCharacters = $this->characterService->getAdditionalCharactersForNextRound($excludeIds, 1);

        return array_merge([$selectedCharacter], $additionalCharacters);
    }

    /**
     * Update the session with the newly selected character.
     */
    private function updateSelectedCharactersInSession(array $selectedCharacter): array
    {
        $previouslySelectedCharacters = Session::get('selected_characters', []);

        if (!collect($previouslySelectedCharacters)->contains('_id', $selectedCharacter['_id'])) {
            $previouslySelectedCharacters[] = $selectedCharacter;
            Session::put('selected_characters', $previouslySelectedCharacters);
        }

        return $previouslySelectedCharacters;
    }


}
