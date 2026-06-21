import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';

// Glob couvrant les pages core ET les pages de chaque plugin.
const pages = import.meta.glob([
    './Pages/**/*.vue',
    '../../plugins/*/resources/js/Pages/**/*.vue',
]);

/**
 * Résolveur Inertia compatible core + plugins.
 * - Cherche d'abord  ./Pages/{name}.vue  (pages core)
 * - Cherche ensuite  .../Pages/{name}.vue  (pages plugins, ex. PraximetResult)
 * - Lève une erreur explicite si la page est introuvable
 */
function resolvePage(name) {
    const coreKey = `./Pages/${name}.vue`;
    if (pages[coreKey]) return pages[coreKey]();

    const pluginKey = Object.keys(pages).find(k => k.endsWith(`/Pages/${name}.vue`));
    if (pluginKey) return pages[pluginKey]();

    throw new Error(`[PraxiQuest] Page introuvable : "${name}". Vérifiez que le composant Vue existe dans resources/js/Pages/ ou dans un dossier plugin/*/resources/js/Pages/.`);
}

createInertiaApp({
    title: (title) => `${title} — ${import.meta.env.VITE_APP_NAME ?? 'PraxiQuest'}`,
    resolve: resolvePage,
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('Head', Head)
            .component('Link', Link)
            .mount(el);
    },
    progress: { color: '#A67520' },
});
