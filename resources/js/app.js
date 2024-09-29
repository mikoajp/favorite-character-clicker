import { createApp } from 'vue';
import CharacterList from "./components/CharacterList.vue";

const app = createApp({});
app.component('character-list', CharacterList);
app.mount('#app');
