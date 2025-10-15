import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import './bootstrap';

import 'bootstrap/dist/js/bootstrap.bundle';
import 'admin-lte/dist/js/adminlte';

const app = createApp(App);

app.use(createPinia());
app.use(router);

app.mount('#app');

