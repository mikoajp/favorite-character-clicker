import { createApp } from 'vue';
import Contest  from "./components/Contest.vue";
import '../css/app.css';
import axios from 'axios';

// Configure Axios for CSRF protection
window.axios = axios;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set CSRF token for all requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found');
}

const app = createApp({});
app.component('character-list', Contest);
app.mount('#app');
