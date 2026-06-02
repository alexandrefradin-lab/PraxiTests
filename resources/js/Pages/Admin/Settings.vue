<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    settings: Object,
    drivers:  Array,
})

const form = useForm({
    default_driver:    props.settings?.default_driver    ?? 'anthropic',
    anthropic_api_key: '',
    anthropic_model:   props.settings?.anthropic_model   ?? 'claude-sonnet-4-6',
    openai_api_key:    '',
    openai_model:      props.settings?.openai_model      ?? 'gpt-4o-mini',
    mistral_api_key:   '',
    mistral_model:     props.settings?.mistral_model     ?? 'mistral-large-latest',
    ollama_base_url:   props.settings?.ollama_base_url   ?? 'http://localhost:11434',
    ollama_model:      props.settings?.ollama_model      ?? 'llama3.1',
})

// Indique si une clé est déjà stockée (l'API renvoie '••••••••')
const hasKey = (driver) => props.settings?.[`${driver}_api_key`] === '••••••••'

// Affichage/masquage des champs clé API
const visible = ref({ anthropic: false, openai: false, mistral: false })

const DRIVERS = [
    {
        id: 'anthropic',
        name: 'Anthropic / Claude',
        logo: '🔵',
        url: 'https://console.anthropic.com/settings/keys',
        models: ['claude-sonnet-4-6', 'claude-opus-4-6', 'claude-haiku-4-5-20251001'],
        hint: 'Recommandé — qualité/coût optimale pour la synthèse de profil.',
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
</script>

<template>
    <AdminLayout>
        <Head title="Paramètres — IA" />

        <div class="max-w-3xl">
            <h1 class="text-2xl font-semibold mb-1">Paramètres</h1>
            <p class="text-slate-500 text-sm mb-8">Configuration des moteurs IA utilisés pour la synthèse de profil, les 15 métiers et l'extraction de CV.</p>

            <div v-if="$page.props.flash?.success" class="mb-6 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- Driver par défaut -->
                <div class="pt-card p-6">
                    <h2 class="font-semibold mb-1">Driver par défaut</h2>
                    <p class="text-xs text-slate-500 mb-4">Moteur utilisé pour toutes les tâches IA sauf configuration spécifique par tâche.</p>
                    <div class="grid sm:grid-cols-4 gap-3">
                        <label v-for="d in DRIVERS" :key="d.id"
                            class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer text-sm transition"
                            :class="form.default_driver === d.id
                                ? 'border-indigo-500 bg-indigo-50 text-indigo-900'
                                : 'border-slate-200 hover:border-indigo-300 text-slate-700'">
                            <input type="radio" :value="d.id" v-model="form.default_driver" class="text-indigo-600">
                            <span>{{ d.logo }} {{ d.name.split(' ')[0] }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.default_driver" class="text-xs text-rose-600 mt-1">{{ form.errors.default_driver }}</p>
                </div>

                <!-- Drivers API -->
                <div v-for="d in DRIVERS" :key="d.id" class="pt-card p-6">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <h2 class="font-semibold flex items-center gap-2">
                                {{ d.logo }} {{ d.name }}
                                <span v-if="form.default_driver === d.id"
                                    class="text-xs font-medium bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                                    Actif
                                </span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-1">{{ d.hint }}</p>
                        </div>
                        <a v-if="d.id !== 'ollama'" :href="d.url" target="_blank"
                            class="text-xs text-indigo-600 hover:underline flex-shrink-0 mt-1">
                            Obtenir une clé →
                        </a>
                    </div>

                    <!-- Clé API (uniquement pour les drivers cloud) -->
                    <div v-if="d.id !== 'ollama'" class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Clé API
                            <span v-if="hasKey(d.id)" class="text-emerald-600 text-xs font-normal ml-2">✓ Clé enregistrée</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form[`${d.id}_api_key`]"
                                :type="visible[d.id] ? 'text' : 'password'"
                                class="pt-input pr-16"
                                :placeholder="hasKey(d.id) ? '(inchangée — saisir pour modifier)' : 'sk-…'"
                            >
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 hover:text-slate-700"
                                @click="visible[d.id] = !visible[d.id]">
                                {{ visible[d.id] ? 'Masquer' : 'Afficher' }}
                            </button>
                        </div>
                        <p v-if="form.errors[`${d.id}_api_key`]" class="text-xs text-rose-600 mt-1">
                            {{ form.errors[`${d.id}_api_key`] }}
                        </p>
                    </div>

                    <!-- Ollama URL -->
                    <div v-if="d.id === 'ollama'" class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">URL du serveur Ollama</label>
                        <input v-model="form.ollama_base_url" type="url" class="pt-input" placeholder="http://localhost:11434">
                        <p v-if="form.errors.ollama_base_url" class="text-xs text-rose-600 mt-1">{{ form.errors.ollama_base_url }}</p>
                    </div>

                    <!-- Modèle -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Modèle</label>
                        <div class="flex gap-2">
                            <input v-model="form[`${d.id}_model`]" class="pt-input flex-1"
                                :placeholder="d.models[0]">
                            <select class="pt-input w-auto"
                                @change="(e) => form[`${d.id}_model`] = e.target.value">
                                <option value="">— Modèles courants</option>
                                <option v-for="m in d.models" :key="m" :value="m">{{ m }}</option>
                            </select>
                        </div>
                        <p v-if="form.errors[`${d.id}_model`]" class="text-xs text-rose-600 mt-1">
                            {{ form.errors[`${d.id}_model`] }}
                        </p>
                    </div>
                </div>

                <!-- Info sécurité -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-500 leading-relaxed">
                    🔒 Les clés API sont chiffrées en base de données (AES-256). Elles ne sont jamais exposées dans l'interface après enregistrement.
                    Les valeurs du <code class="bg-slate-200 px-1 rounded">.env</code> servent de fallback si aucune clé n'est configurée ici.
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
