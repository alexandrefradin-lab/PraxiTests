<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'

const props = defineProps({
    settings:   Object,
    drivers:    Array,
    providers:  { type: Array, default: () => [] },   // [{value,label}] pour le choix par tâche
    taskConfig: { type: Object, default: () => ({}) },// { task: {label, driver, model, default_model} }
})

// Champs par tâche (task_<task>_driver / task_<task>_model) construits dynamiquement.
const taskEntries = Object.entries(props.taskConfig || {})
const taskFields = {}
for (const [task, cfg] of taskEntries) {
    taskFields[`task_${task}_driver`] = cfg.driver ?? ''
    taskFields[`task_${task}_model`]  = cfg.model ?? ''
}

const form = useForm({
    default_driver:        props.settings?.default_driver        ?? 'anthropic',
    anthropic_api_key:     '',
    anthropic_model:       props.settings?.anthropic_model       ?? 'claude-sonnet-4-6',
    anthropic_haiku_model: props.settings?.anthropic_haiku_model ?? 'claude-haiku-4-5-20251001',
    openai_api_key:        '',
    openai_model:          props.settings?.openai_model          ?? 'gpt-4o-mini',
    deepseek_api_key:      '',
    deepseek_model:        props.settings?.deepseek_model        ?? 'deepseek-chat',
    deepseek_base_url:     props.settings?.deepseek_base_url      ?? 'https://api.deepseek.com',
    mistral_api_key:       '',
    mistral_model:         props.settings?.mistral_model         ?? 'mistral-large-latest',
    ollama_base_url:       props.settings?.ollama_base_url        ?? 'http://localhost:11434',
    ollama_model:          props.settings?.ollama_model           ?? 'llama3.1',
    ...taskFields,
})

// Indique si une clé est déjà stockée (l'API renvoie '••••••••')
const hasKey = (driver) => props.settings?.[`${driver}_api_key`] === '••••••••'

// Affichage/masquage des champs clé API
const visible = ref({ anthropic: false, openai: false, mistral: false, deepseek: false })

const DRIVERS = [
    {
        id: 'anthropic',
        name: 'Anthropic / Claude',
        logo: '🔵',
        url: 'https://console.anthropic.com/settings/keys',
        models: ['claude-sonnet-4-6', 'claude-opus-4-8', 'claude-haiku-4-5-20251001'],
        hint: 'Recommandé — qualité/coût optimale pour le rédactionnel (synthèses, relecture).',
    },
    {
        id: 'openai',
        name: 'OpenAI',
        logo: '🟢',
        url: 'https://platform.openai.com/api-keys',
        models: ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo'],
        hint: 'Alternative solide. gpt-4o-mini est le plus économique.',
    },
    {
        id: 'deepseek',
        name: 'DeepSeek',
        logo: '🟣',
        url: 'https://platform.deepseek.com/api_keys',
        models: ['deepseek-chat', 'deepseek-reasoner'],
        hint: 'Très économique. API compatible OpenAI.',
    },
    {
        id: 'mistral',
        name: 'Mistral AI',
        logo: '🟠',
        url: 'https://console.mistral.ai/api-keys/',
        models: ['mistral-large-latest', 'mistral-medium-latest', 'mistral-small-latest'],
        hint: 'Hébergé en Europe — bon choix pour la conformité RGPD.',
    },
    {
        id: 'ollama',
        name: 'Ollama (local / auto-hébergé)',
        logo: '⚙️',
        url: 'https://ollama.ai',
        models: ['llama3.1', 'llama3.2', 'mistral', 'phi3', 'gemma2'],
        hint: 'Gratuit, sans envoi de données. Nécessite un serveur Ollama accessible.',
    },
]

const submit = () => form.post(route('admin.settings.update'))

// Test de connexion : ping le fournisseur avec la clé ENREGISTRÉE (pense à
// enregistrer d'abord si tu viens de saisir une nouvelle clé).
const testing = ref(null)
const testConnection = (driverId) => {
    testing.value = driverId
    router.post(route('admin.settings.test-connection'), { driver: driverId }, {
        preserveScroll: true,
        onFinish: () => { testing.value = null },
    })
}
</script>

<template>
    <AdminLayout>
        <Head title="Paramètres — IA" />

        <div class="max-w-3xl">
            <h1 class="text-2xl font-semibold mb-1" style="font-family:var(--font-display);color:var(--text-primary)">Paramètres IA</h1>
            <p class="text-sm mb-8" style="color:var(--text-muted)">Clés des fournisseurs (Claude, OpenAI, DeepSeek…), modèles, et choix du modèle utilisé pour chaque tâche IA.</p>

            <FlashAlert />

            <form @submit.prevent="submit" class="space-y-6">

                <!-- Driver par défaut -->
                <div class="pt-card p-6">
                    <h2 class="font-semibold mb-1" style="font-family:var(--font-display);color:var(--text-primary)">Fournisseur par défaut</h2>
                    <p class="text-xs mb-4" style="color:var(--text-muted)">Moteur utilisé pour les tâches sans réglage spécifique ci-dessous.</p>
                    <div class="grid sm:grid-cols-5 gap-3">
                        <label v-for="d in DRIVERS" :key="d.id"
                            class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer text-sm transition"
                            :style="form.default_driver === d.id
                                ? 'border-color:var(--color-primary);background:var(--bg-elevated);color:var(--text-primary)'
                                : 'border-color:var(--border-mid);color:var(--text-secondary)'">
                            <input type="radio" :value="d.id" v-model="form.default_driver" class="ac-radio">
                            <span>{{ d.logo }} {{ d.name.split(' ')[0] }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.default_driver" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.default_driver }}</p>
                </div>

                <!-- Drivers API -->
                <div v-for="d in DRIVERS" :key="d.id" class="pt-card p-6">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-semibold flex items-center gap-2" style="font-family:var(--font-display);color:var(--text-primary)">
                                {{ d.logo }} {{ d.name }}
                                <span v-if="form.default_driver === d.id" class="ac-badge-signal">
                                    Défaut
                                </span>
                            </h2>
                            <p class="text-xs mt-1" style="color:var(--text-muted)">{{ d.hint }}</p>
                        </div>
                        <a v-if="d.id !== 'ollama'" :href="d.url" target="_blank"
                            class="ac-link-primary text-xs flex-shrink-0 mt-1">
                            Obtenir une clé →
                        </a>
                    </div>

                    <!-- Clé API (uniquement pour les drivers cloud) -->
                    <div v-if="d.id !== 'ollama'" class="mb-4">
                        <label :for="`key-${d.id}`" class="pt-label mb-1">
                            Clé API
                            <span v-if="hasKey(d.id)" class="text-xs font-normal ml-2" style="color:var(--color-success)">✓ Clé enregistrée</span>
                        </label>
                        <div class="relative">
                            <input
                                :id="`key-${d.id}`"
                                v-model="form[`${d.id}_api_key`]"
                                :type="visible[d.id] ? 'text' : 'password'"
                                class="pt-input pr-16"
                                :placeholder="hasKey(d.id) ? '(inchangée — saisir pour modifier)' : 'sk-…'"
                            >
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs"
                                style="color:var(--text-muted)"
                                :aria-label="visible[d.id] ? `Masquer la clé API ${d.name}` : `Afficher la clé API ${d.name}`"
                                @click="visible[d.id] = !visible[d.id]">
                                {{ visible[d.id] ? 'Masquer' : 'Afficher' }}
                            </button>
                        </div>
                        <p v-if="form.errors[`${d.id}_api_key`]" class="text-xs mt-1" style="color:var(--color-danger)">
                            {{ form.errors[`${d.id}_api_key`] }}
                        </p>
                    </div>

                    <!-- DeepSeek : base URL (API compatible OpenAI) -->
                    <div v-if="d.id === 'deepseek'" class="mb-4">
                        <label class="pt-label mb-1">URL de base de l'API</label>
                        <input v-model="form.deepseek_base_url" type="url" class="pt-input" placeholder="https://api.deepseek.com">
                        <p v-if="form.errors.deepseek_base_url" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.deepseek_base_url }}</p>
                    </div>

                    <!-- Ollama URL -->
                    <div v-if="d.id === 'ollama'" class="mb-4">
                        <label class="pt-label mb-1">URL du serveur Ollama</label>
                        <input v-model="form.ollama_base_url" type="url" class="pt-input" placeholder="http://localhost:11434">
                        <p v-if="form.errors.ollama_base_url" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.ollama_base_url }}</p>
                    </div>

                    <!-- Modèle -->
                    <div>
                        <label class="pt-label mb-1">Modèle</label>
                        <div class="flex gap-2">
                            <input v-model="form[`${d.id}_model`]" class="pt-input flex-1"
                                :placeholder="d.models[0]">
                            <select class="pt-input w-auto"
                                @change="(e) => form[`${d.id}_model`] = e.target.value">
                                <option value="">— Modèles courants</option>
                                <option v-for="m in d.models" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <p v-if="form.errors[`${d.id}_model`]" class="text-xs mt-1" style="color:var(--color-danger)">
                            {{ form.errors[`${d.id}_model`] }}
                        </p>
                    </div>

                    <!-- Anthropic : modèle économique (Haiku) -->
                    <div v-if="d.id === 'anthropic'" class="mt-4 pt-4 border-t" style="border-color:var(--border-light)">
                        <label for="anthropic-haiku-model" class="pt-label mb-1">
                            Modèle économique (Haiku)
                            <span class="text-xs font-normal" style="color:var(--text-muted)">— utilisé pour les tâches structurées</span>
                        </label>
                        <input id="anthropic-haiku-model" v-model="form.anthropic_haiku_model" class="pt-input" placeholder="claude-haiku-4-5-20251001">
                        <p v-if="form.errors.anthropic_haiku_model" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.anthropic_haiku_model }}</p>
                    </div>

                    <!-- Test de connexion : valide la clé/le modèle ENREGISTRÉS -->
                    <div class="mt-4 pt-4 border-t flex items-center justify-between gap-4" style="border-color:var(--border-light)">
                        <p class="text-xs" style="color:var(--text-muted)">
                            Vérifie la configuration enregistrée (enregistre d'abord toute nouvelle clé).
                        </p>
                        <button type="button" class="ac-btn-ghost text-xs flex-shrink-0"
                            :disabled="testing !== null"
                            @click="testConnection(d.id)">
                            {{ testing === d.id ? 'Test en cours…' : 'Tester la connexion' }}
                        </button>
                    </div>
                </div>

                <!-- ── Modèle par tâche ─────────────────────────────────────── -->
                <div v-if="taskEntries.length" class="pt-card p-6">
                    <h2 class="font-semibold mb-1" style="font-family:var(--font-display);color:var(--text-primary)">Modèle par tâche</h2>
                    <p class="text-xs mb-4" style="color:var(--text-muted)">
                        Choisis quel fournisseur (et modèle) traite chaque tâche. Laisse « (défaut config) »
                        pour conserver le réglage de base. Astuce coût : Haiku/DeepSeek pour les tâches
                        structurées, Sonnet pour le rédactionnel.
                    </p>

                    <div class="space-y-3">
                        <div v-for="[task, cfg] in taskEntries" :key="task"
                            class="grid sm:grid-cols-12 gap-2 items-center">
                            <label :for="`task-${task}-driver`" class="sm:col-span-4 text-sm font-medium" style="color:var(--text-secondary)">{{ cfg.label }}</label>
                            <div class="sm:col-span-4">
                                <select :id="`task-${task}-driver`" v-model="form[`task_${task}_driver`]" class="pt-input w-full">
                                    <option v-for="p in providers" :key="p.value" :value="p.value">{{ p.label }}</option>
                                </select>
                            </div>
                            <div class="sm:col-span-4">
                                <input v-model="form[`task_${task}_model`]" class="pt-input w-full"
                                    :aria-label="`Modèle pour ${cfg.label}`"
                                    :placeholder="cfg.default_model ? `défaut : ${cfg.default_model}` : 'modèle (optionnel)'">
                            </div>
                        </div>
                    </div>
                    <p class="text-xs mt-3" style="color:var(--text-muted)">
                        Le champ « modèle » est optionnel : vide = modèle par défaut du fournisseur choisi.
                    </p>
                </div>

                <!-- Info sécurité -->
                <div class="rounded-xl p-4 text-xs leading-relaxed" style="background:var(--bg-elevated);border:1px solid var(--border-light);color:var(--text-muted)">
                    🔒 Les clés API sont chiffrées en base de données (AES-256). Elles ne sont jamais exposées dans l'interface après enregistrement.
                    Les valeurs du <code class="px-1 rounded" style="background:var(--border-light)">.env</code> servent de fallback si aucune clé n'est configurée ici.
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="pt-btn-primary px-8 py-3">
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer la configuration' }}
                    </button>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>
