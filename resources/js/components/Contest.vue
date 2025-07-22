<template>
    <div>
        <MainTemplate
            :title="'Rozpocznij Rozgrywkę Star Wars'"
            :buttonText="'Rozpocznij Rozgrywkę'"
            :gameStarted="gameStarted"
            @start-game="startGame"
        />

        <div v-if="gameStarted && !gameCompleted">
            <div class="game-info">
                <h2>Runda {{ currentRound }}</h2>
                <p>Pozostało postaci: {{ totalCharacters }}</p>
                <button @click="abandonGame" class="abandon-btn">Porzuć grę</button>
            </div>
            
            <div class="vs-container">
                <h3>Wybierz swoją ulubioną postać:</h3>
                <ul class="character-list" :class="getCharacterListClass()">
                    <li v-for="(character, index) in sortedCharacters" :key="`${character._id}-pos-${character.display_position}`" class="character-card" :data-position="character.display_position" :style="{ gridColumn: character.display_position + 1 }">
                        <strong class="character-name">{{ character.name }}</strong>
                        
                        <!-- Character ratings display -->
                        <div v-if="character.average_rating" class="rating-display">
                            <span class="stars">
                                <span v-for="star in 5" :key="star" 
                                      :class="['star', { 'filled': star <= Math.round(character.average_rating) }]">★</span>
                            </span>
                            <span class="rating-text">{{ character.average_rating }}/5 ({{ character.total_ratings }} ocen)</span>
                        </div>

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
                                <button @click="selectCharacter(character)" class="select-btn">Wybierz</button>
                                <button v-if="isAuthenticated" @click="toggleFavorite(character)" 
                                        :class="['favorite-btn', { 'favorited': character.is_favorited }]">
                                    {{ character.is_favorited ? '❤️' : '🤍' }}
                                </button>
                            </div>
                        </div>

                        <!-- Rating section for authenticated users -->
                        <div v-if="isAuthenticated" class="rating-section">
                            <div class="user-rating">
                                <span>Twoja ocena: </span>
                                <span v-for="star in 5" :key="star" 
                                      @click="rateCharacter(character, star)"
                                      :class="['star', 'clickable', { 'filled': star <= (character.user_rating || 0) }]">★</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Game completed screen -->
        <div v-if="gameCompleted" class="game-completed">
            <h2>🎉 Gra zakończona! 🎉</h2>
            <div class="winner-section">
                <h3>Zwycięzca:</h3>
                <div class="winner-card">
                    <img :src="winner.image" :alt="winner.name" class="winner-image">
                    <h4>{{ winner.name }}</h4>
                    <p class="final-score">Twój wynik: {{ finalScore }} punktów</p>
                </div>
            </div>
            <div class="game-actions">
                <button @click="startNewGame" class="new-game-btn">Nowa gra</button>
                <button @click="showStats" class="stats-btn">Zobacz statystyki</button>
            </div>
        </div>

        <!-- Stats modal -->
        <div v-if="showStatsModal" class="modal-overlay" @click="closeStatsModal">
            <div class="modal-content" @click.stop>
                <h3>Twoje statystyki</h3>
                <div v-if="userStats">
                    <p>Ukończone gry: {{ userStats.total_games }}</p>
                    <p>Średni wynik: {{ Math.round(userStats.average_score) }}</p>
                    <p>Najwyższy wynik: {{ userStats.highest_score }}</p>
                    <p>Łączna liczba rund: {{ userStats.total_rounds_played }}</p>
                </div>
                <button @click="closeStatsModal" class="close-btn">Zamknij</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import '../../css/contest.css';
import MainTemplate from './MainTemplate.vue';

export default {
    components: {
        MainTemplate
    },
    data() {
        return {
            currentCharacters: [],
            currentRound: 1,
            totalCharacters: 0,
            hoverStates: [],
            gameStarted: false,
            gameCompleted: false,
            winner: null,
            finalScore: 0,
            isAuthenticated: false,
            showStatsModal: false,
            userStats: null,
            selectedCharacterCount: 10
        };
    },
    computed: {
        sortedCharacters() {
            // Keep characters in their exact positions without reordering
            return [...this.currentCharacters].map((character, index) => ({
                ...character,
                display_position: character.position !== undefined ? character.position : index
            })).sort((a, b) => a.display_position - b.display_position);
        }
    },
    mounted() {
        this.checkAuthStatus();
    },
    methods: {
        checkAuthStatus() {
            // Check if user is authenticated - you'll need to implement this based on your auth system
            // For now, we'll assume guest user
            this.isAuthenticated = false;
        },

        startGame(characterCount = null) {
            if (characterCount) {
                this.selectedCharacterCount = characterCount;
            }

            const data = {
                number_of_characters: this.selectedCharacterCount
            };

            console.log('Starting game with data:', data);
            axios.post('/api/start-game', data)
                .then(response => {
                    console.log('Game start response:', response.data);
                    
                    if (response.data.characters && Array.isArray(response.data.characters)) {
                        this.currentCharacters = response.data.characters;
                        this.currentRound = response.data.round;
                        this.totalCharacters = response.data.total_characters;
                        this.hoverStates = Array(this.currentCharacters.length).fill(false);
                        this.gameStarted = true;
                        this.gameCompleted = false;
                        this.winner = null;
                        this.finalScore = 0;
                        
                        console.log('Game started successfully with', this.currentCharacters.length, 'characters');
                    } else {
                        console.error('Unexpected response format:', response.data);
                        this.showError('Błąd podczas rozpoczynania gry - nieprawidłowy format odpowiedzi');
                    }
                })
                .catch(error => {
                    console.error('Error starting game:', error);
                    
                    let errorMessage = 'Nie udało się rozpocząć gry';
                    if (error.response) {
                        console.error('Start game error status:', error.response.status);
                        console.error('Start game error data:', error.response.data);
                        
                        if (error.response.status === 500) {
                            errorMessage = 'Błąd serwera podczas rozpoczynania gry. Sprawdź czy baza danych jest skonfigurowana.';
                        } else if (error.response.data && error.response.data.error) {
                            errorMessage = error.response.data.error;
                        }
                    }
                    
                    this.showError(errorMessage);
                });
        },

        selectCharacter(character) {
            console.log('Selecting character:', character.name, 'ID:', character._id);
            
            axios.post('/api/select-character', { character_id: character._id })
                .then(response => {
                    console.log('Selection response:', response.data);
                    
                    if (response.data.game_completed) {
                        // Game finished
                        this.gameCompleted = true;
                        this.winner = response.data.winner;
                        this.finalScore = response.data.final_score;
                        this.gameStarted = false;
                    } else if (response.data.characters && Array.isArray(response.data.characters)) {
                        // Continue to next round
                        this.currentCharacters = response.data.characters;
                        this.currentRound = response.data.round;
                        this.totalCharacters = response.data.total_characters;
                        this.hoverStates = Array(this.currentCharacters.length).fill(false);
                    } else {
                        console.error('Unexpected response format:', response.data);
                        this.showError('Błąd podczas wyboru postaci - nieoczekiwany format odpowiedzi');
                    }
                })
                .catch(error => {
                    console.error('Error selecting character:', error);
                    
                    let errorMessage = 'Nie udało się wybrać postaci';
                    if (error.response) {
                        // Server responded with error status
                        console.error('Response status:', error.response.status);
                        console.error('Response data:', error.response.data);
                        
                        if (error.response.status === 404) {
                            errorMessage = 'Gra nie została znaleziona. Rozpocznij nową grę.';
                            this.resetGame();
                        } else if (error.response.status === 422) {
                            errorMessage = 'Nieprawidłowe dane. Spróbuj ponownie.';
                        } else if (error.response.status === 500) {
                            errorMessage = 'Błąd serwera. Sprawdź czy baza danych jest skonfigurowana.';
                        } else if (error.response.data && error.response.data.error) {
                            errorMessage = error.response.data.error;
                        }
                    } else if (error.request) {
                        // Request was made but no response received
                        errorMessage = 'Brak odpowiedzi z serwera. Sprawdź połączenie.';
                    }
                    
                    this.showError(errorMessage);
                });
        },

        toggleFavorite(character) {
            if (!this.isAuthenticated) {
                this.showError('Musisz być zalogowany, aby dodać do ulubionych');
                return;
            }

            const action = character.is_favorited ? 'delete' : 'post';
            const url = '/api/favorites';
            const data = character.is_favorited 
                ? { character_id: character._id }
                : {
                    character_id: character._id,
                    character_name: character.name,
                    character_image: character.image
                };

            axios[action](url, data)
                .then(() => {
                    character.is_favorited = !character.is_favorited;
                })
                .catch(error => {
                    console.error('Error toggling favorite:', error);
                    this.showError('Nie udało się zaktualizować ulubionych');
                });
        },

        rateCharacter(character, rating) {
            if (!this.isAuthenticated) {
                this.showError('Musisz być zalogowany, aby ocenić postać');
                return;
            }

            axios.post('/api/ratings', {
                character_id: character._id,
                character_name: character.name,
                rating: rating
            })
            .then(() => {
                character.user_rating = rating;
                // Optionally refresh character ratings
                this.refreshCharacterRating(character);
            })
            .catch(error => {
                console.error('Error rating character:', error);
                this.showError('Nie udało się ocenić postaci');
            });
        },

        refreshCharacterRating(character) {
            axios.get(`/api/ratings/${character._id}/average`)
                .then(response => {
                    character.average_rating = response.data.average_rating;
                    character.total_ratings = response.data.total_ratings;
                })
                .catch(error => {
                    console.error('Error refreshing rating:', error);
                });
        },

        abandonGame() {
            if (confirm('Czy na pewno chcesz porzucić grę?')) {
                axios.post('/api/abandon-game')
                    .then(() => {
                        this.resetGame();
                    })
                    .catch(error => {
                        console.error('Error abandoning game:', error);
                        this.showError('Nie udało się porzucić gry');
                    });
            }
        },

        startNewGame() {
            this.resetGame();
        },

        resetGame() {
            this.gameStarted = false;
            this.gameCompleted = false;
            this.currentCharacters = [];
            this.currentRound = 1;
            this.totalCharacters = 0;
            this.winner = null;
            this.finalScore = 0;
            this.hoverStates = [];
        },

        showStats() {
            if (!this.isAuthenticated) {
                this.showError('Musisz być zalogowany, aby zobaczyć statystyki');
                return;
            }

            axios.get('/api/game-stats')
                .then(response => {
                    this.userStats = response.data;
                    this.showStatsModal = true;
                })
                .catch(error => {
                    console.error('Error fetching stats:', error);
                    this.showError('Nie udało się pobrać statystyk');
                });
        },

        closeStatsModal() {
            this.showStatsModal = false;
        },

        showError(message) {
            // Simple error display - you can replace with a proper notification system
            alert(message);
        },

        getCharacterListClass() {
            if (this.currentCharacters.length === 1) {
                return 'single-character';
            } else if (this.currentCharacters.length === 2) {
                return 'two-characters';
            } else {
                return 'multiple-characters';
            }
        }
    }
};
</script>


