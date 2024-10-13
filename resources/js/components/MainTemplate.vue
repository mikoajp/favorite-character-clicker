<template>
    <div class="main-template">
        <h2>{{ title }}</h2>
        <div v-if="!gameStarted">
            <div class="character-count-selection">
                <h3>Select Number of Characters:</h3>
                <button @click="selectCharacterCount(10)">10 Characters</button>
                <button @click="selectCharacterCount(20)">20 Characters</button>
                <button @click="selectCharacterCount(30)">30 Characters</button>
            </div>
            <div class="start-game">
                <button :disabled="!selectedCharacterCount" @click="startGame">{{ buttonText }}</button>
            </div>
        </div>
    </div>
</template>

<script>
import '../../css/app.css';
export default {
    props: {
        title: {
            type: String,
            required: true
        },
        buttonText: {
            type: String,
            required: true
        },
        gameStarted: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            selectedCharacterCount: null,
        };
    },
    methods: {
        selectCharacterCount(characterCount) {
            this.selectedCharacterCount = characterCount;
        },
        startGame() {
            if (this.selectedCharacterCount) {
                this.$emit('start-game', this.selectedCharacterCount);
            }
        }
    }
};
</script>
