<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ plugins: Array, types: Array })

const toggle = (plugin) => {
    if (plugin.enabled) {
        router.post(route('admin.plugins.deactivate', plugin.id))
    } else {
        router.post(route('admin.plugins.activate', plugin.id))
    }
}

const typeLabel = {
    test: 'Test', scoring: 'Scoring', ai: 'IA', mail: 'Mail',
    gamification: 'Gamification', integration: 'Intégration',
    theme: 'Thème', reporting: 'Reporting',
}
</script>

<template>
    <AdminLayout>
        <Head title="Plugins" />

        <div class="flex items-end justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold">Plugins</h1>
                <p class="text-sm text-slate-500 mt-1">Étends ta plateforme. Active uniquement ce dont tu as besoin.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <article v-for="p in plugins" :key="p.id" class="pt-card p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold">{{ p.name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ p.author }} · v{{ p.version }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ typeLabel[p.type] ?? p.type }}</span>
                </div>
                <p class="text-sm text-slate-600 line-clamp-2">{{ p.description ?? '—' }}</p>
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                    <button @click="toggle(p)" :disabled="p.core && p.enabled" :class="p.enabled ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="text-xs px-3 py-1 rounded-full font-medium">
                        {{ p.enabled ? 'Activé' : 'Désactivé' }}
                    </button>
                    <Link :href="route('admin.plugins.show', p.id)" class="text-xs text-indigo-600 hover:underline">Configurer →</Link>
                </div>
            </article>

            <div v-if="!plugins.length" class="md:col-span-2 lg:col-span-3 pt-card p-12 text-center text-slate-500">
                Aucun plugin trouvé. Dépose un plugin dans le dossier <code>plugins/</code> et lance la commande
                <code>php artisan praxitests:plugins:discover --sync</code>.
            </div>
        </div>
    </AdminLayout>
</template>
