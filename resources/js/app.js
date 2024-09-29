import { createApp } from 'vue';
import Contest  from "./components/Contest.vue";

const app = createApp({});
app.component('character-list', Contest);
app.mount('#app');
