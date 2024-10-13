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


    public function startNewGame(int $numberOfCharacters): array
    {

        $characters = $this->characterService->getAllCharacters();
        $randomCharacters = collect($characters)->random($numberOfCharacters)->all();

        Session::put('selected_characters', $randomCharacters);

        return $randomCharacters;
    }

    /**
     * Select a character by ID and advance to the next round.
     */
    public function selectCharacterAndAdvanceRound(): array
    {
        $characters = Session::get('selected_characters', []);

        if (empty($characters)) {
            return [];
        }

        $selectedCharacter = collect($characters)->random();
        return $this->updateSelectedCharactersInSession($selectedCharacter);
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
