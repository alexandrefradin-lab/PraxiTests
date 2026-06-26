<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ plugin: Object })

const form = useForm({ config: props.plugin?.config ?? {} })

const save = () => form.put(route('admin.plugins.update', props.plugin.id))

const uninstall = () => {
    if (confirm(`Désinstaller ${props.plugin.name} ? Action irréversible.`)) {
        router.delete(route('admin.plugins.destroy', props.plugin.id))
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="plugin.name" />

        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">{{ plugin.name }}</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">{{ plugin.author }} · v{{ plugin.version }} · {{ plugin.type }}</p>
            </div>
            <button v-if="!plugin.core" @click="uninstall" class="text-sm hover:underline" style="color:var(--color-danger)">Désinstaller</button>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <section class="pt-card p-6 lg:col-span-2">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Manifest</h2>
                <pre class="text-xs rounded-lg p-4 overflow-auto max-h-96" style="background:var(--bg-elevated);color:var(--text-secondary)">{{ JSON.stringify(plugin.manifest, null, 2) }}</pre>
            </section>

            <aside class="pt-card p-6 h-fit">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Permissions</h2>
                <ul class="space-y-1 text-sm">
                    <li v-for="perm in plugin.permissions ?? []" :key="perm" class="flex items-center gap-2" style="color:var(--text-secondary)">
                        <span class="h-1.5 w-1.5 rounded-full" style="background:var(--color-success)"></span> {{ perm }}
                    </li>
                    <li v-if="!plugin.permissions?.length" class="text-xs" style="color:var(--text-muted)">Aucune permission requise.</li>
                </ul>

                <h2 class="font-semibold mt-6 mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Configuration JSON</h2>
                <textarea
                    @input="(e) => { try { form.config = JSON.parse(e.target.value || '{}') } catch {} }"
                    :value="JSON.stringify(form.config, null, 2)"
                    rows="10" class="pt-input font-mono text-xs"></textarea>
                <button @click="save" :disabled="form.processing" class="pt-btn-primary w-full mt-3">
                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
            </aside>
        </div>
    </AdminLayout>
</template>
