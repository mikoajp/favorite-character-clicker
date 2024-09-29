<template>
    <div class="main-template">
        <h2>{{ title }}</h2>
        <button v-if="!gameStarted" @click="onStart">{{ buttonText }}</button>
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
    methods: {
        onStart() {
            this.$emit('start-game');
        }
    }
};
</script>
