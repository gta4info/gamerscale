import { createApp, h } from 'vue';
import { InertiaProgress } from '@inertiajs/progress'
import { createInertiaApp, Link } from '@inertiajs/inertia-vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

// Vuetify
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

// Axios
import axios from 'axios';
window.axios = axios;

const token = document.head.querySelector('meta[name="csrf-token"]').content;

window.axios.defaults.baseURL = import.meta.env.VITE_ADMIN_APP_URL;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Moment.js
import moment from 'moment';

const momentPlugin = {
    install(app) {
        app.config.globalProperties.$moment = moment
    }
}

InertiaProgress.init()

const vuetify = createVuetify({
    components,
    directives
})

createInertiaApp({
    resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .use(momentPlugin)
            .component('inertia-link', Link)
            .mount(el)
    },
})
