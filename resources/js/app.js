import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';

const pages = import.meta.glob([
    './Pages/**/*.vue',
    '../../plugins/*/resources/js/Pages/**/*.vue',
]);

createInertiaApp({
    title: (title) => `${title} — ${import.meta.env.VITE_APP_NAME ?? 'PraxiTests'}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, pages),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('Head', Head)
            .component('Link', Link)
            .mount(el);
    },
    progress: { color: '#4F46E5' },
});
