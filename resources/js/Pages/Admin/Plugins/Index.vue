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
                <h1 class="text-2xl font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">Plugins</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">Étends ta plateforme. Active uniquement ce dont tu as besoin.</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <article v-for="p in plugins" :key="p.id" class="pt-card p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">{{ p.name }}</h3>
                        <p class="text-xs mt-1" style="color:var(--text-muted)">{{ p.author }} · v{{ p.version }}</p>
                    </div>
                    <span class="ac-badge-neutral">{{ typeLabel[p.type] ?? p.type }}</span>
                </div>
                <p class="text-sm line-clamp-2" style="color:var(--text-secondary)">{{ p.description ?? '—' }}</p>
                <div class="flex items-center justify-between mt-4 pt-4 border-t" style="border-color:var(--border-light)">
                    <button @click="toggle(p)" :disabled="p.core && p.enabled" :class="p.enabled ? 'ac-badge-success' : 'ac-badge-neutral'" class="font-medium">
                        {{ p.enabled ? 'Activé' : 'Désactivé' }}
                    </button>
                    <Link :href="route('admin.plugins.show', p.id)" class="ac-link-primary text-xs">Configurer →</Link>
                </div>
            </article>

            <div v-if="!plugins.length" class="md:col-span-2 lg:col-span-3 pt-card p-12 text-center" style="color:var(--text-muted)">
                Aucun plugin trouvé. Dépose un plugin dans le dossier <code>plugins/</code> et lance la commande
                <code>php artisan praxiquest:plugins:discover --sync</code>.
            </div>
        </div>
    </AdminLayout>
</template>
