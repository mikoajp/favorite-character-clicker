<template>
    <div>
        <h2>Rozpocznij Rozgrywkę Star Wars</h2>
        <button v-if="!gameStarted" @click="startGame">Rozpocznij Rozgrywkę</button>

        <div v-if="gameStarted">
            <h2>Runda {{ currentRound }}</h2>
            <ul class="character-list">
                <li v-for="(character, index) in currentCharacters" :key="character._id">
                    <strong class="character-name">{{ character.name }}</strong>
                    <div
                        class="image-container"
                        @mouseover="hoverStates[index] = true"
                        @mouseleave="hoverStates[index] = false"
                    >
                        <img
                            :src="character.image"
                            :alt="character.name"
                            class="character-image"
                        >
                        <div v-if="hoverStates[index]" class="favorite-overlay">
                            <button @click="markAsFavorite(index)">Wybierz</button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import '../../css/CharacterList.css';

export default {
    data() {
        return {
            characters: [],
            currentRound: 1,
            hoverStates: [],
            gameStarted: false,
            charactersPerRound: 2,
            selectedCharacterIndex: null,
        };
    },
    computed: {
        currentCharacters() {
            return this.characters.slice(-this.charactersPerRound);
        }
    },
    methods: {
        startGame() {
            axios.get('/api/start-game')
                .then(response => {
                    if (response.data.characters && Array.isArray(response.data.characters)) {
                        this.characters = response.data.characters;
                        this.hoverStates = Array(this.characters.length).fill(false);
                        this.gameStarted = true;
                    } else {
                        console.error('Unexpected response format:', response.data);
                    }
                })
                .catch(error => {
                    console.error('Error starting game:', error);
                });
        },
        markAsFavorite(index) {
            const isLeft = index === 0;
            const selectedCharacter = this.currentCharacters[index];
            this.selectedCharacterIndex = this.characters.length - this.charactersPerRound + index;

            axios.post('/api/select-character', { character_id: selectedCharacter._id })
                .then(response => {
                    if (response.data.characters && Array.isArray(response.data.characters)) {
                        const newCharacter = response.data.characters.find(
                            char => char._id !== selectedCharacter._id
                        );

                        if (newCharacter) {
                            if (isLeft) {
                                this.characters[this.selectedCharacterIndex + 1] = newCharacter;
                            } else {
                                this.characters[this.selectedCharacterIndex - 1] = newCharacter;
                            }
                        }

                        this.hoverStates = Array(this.characters.length).fill(false);
                        this.currentRound++;
                    } else {
                        console.error('Unexpected response format:', response.data);
                    }
                })
                .catch(error => {
                    console.error('Error selecting character and proceeding to the next round:', error);
                });
        }
    }
};
</script>


