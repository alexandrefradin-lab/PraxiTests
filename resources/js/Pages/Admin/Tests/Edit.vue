<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ test: Object, scoring_engines: Array })

// ─── Métadonnées du test ──────────────────────────────────────────────────────
const meta = useForm({
    slug:               props.test?.slug ?? '',
    name:               props.test?.name ?? '',
    description:        props.test?.description ?? '',
    type:               props.test?.type ?? 'questionnaire',
    scoring_engine:     props.test?.scoring_engine ?? 'default',
    estimated_minutes:  props.test?.estimated_minutes ?? 10,
    published:          props.test?.published ?? false,
    public:             props.test?.public ?? false,
    gamification:       props.test?.gamification ?? {},
    neuromarketing:     props.test?.neuromarketing ?? {},
    scoring_config:     props.test?.scoring_config ?? {},
})

const saveMeta = () => {
    if (props.test?.id) {
        meta.put(route('admin.tests.update', props.test.id))
    } else {
        meta.post(route('admin.tests.store'))
    }
}

// ─── Structure (sections + questions) ────────────────────────────────────────

const TYPES = [
    { value: 'scale',  label: 'Échelle (1–N)' },
    { value: 'single', label: 'Choix unique' },
    { value: 'multi',  label: 'Choix multiple' },
    { value: 'text',   label: 'Texte libre' },
]

function makeQuestion(order = 0) {
    return {
        _key:     Math.random().toString(36).slice(2),
        id:       null,
        type:     'scale',
        prompt:   '',
        helper:   '',
        order,
        required: true,
        // scale
        scaleMin: 1,
        scaleMax: 5,
        scaleMinLabel: '',
        scaleMaxLabel: '',
        // single / multi
        choices: [{ value: '', label: '' }],
        // scoring brut JSON
        scoringStr: '',
    }
}

function makeSection(order = 0) {
    return {
        _key:           Math.random().toString(36).slice(2),
        id:             null,
        title:          '',
        description:    '',
        narrative_intro:'',
        order,
        questions:      [],
        open:           true,
    }
}

// Initialiser depuis les données existantes
function hydrateSection(s) {
    return {
        _key:           Math.random().toString(36).slice(2),
        id:             s.id,
        title:          s.title ?? '',
        description:    s.description ?? '',
        narrative_intro:s.narrative_intro ?? '',
        order:          s.order ?? 0,
        open:           true,
        questions: (s.questions ?? []).map(hydrateQuestion),
    }
}

function hydrateQuestion(q) {
    const opts = q.options ?? {}
    const isScale = q.type === 'scale'
    const isChoice = q.type === 'single' || q.type === 'multi'
    return {
        _key:           Math.random().toString(36).slice(2),
        id:             q.id,
        type:           q.type ?? 'scale',
        prompt:         q.prompt ?? '',
        helper:         q.helper ?? '',
        order:          q.order ?? 0,
        required:       q.required ?? true,
        scaleMin:       isScale ? (opts.min ?? 1)          : 1,
        scaleMax:       isScale ? (opts.max ?? 5)          : 5,
        scaleMinLabel:  isScale ? (opts.min_label ?? '')   : '',
        scaleMaxLabel:  isScale ? (opts.max_label ?? '')   : '',
        choices:        isChoice && Array.isArray(opts)
            ? opts.map(o => ({ value: String(o.value ?? ''), label: String(o.label ?? '') }))
            : [{ value: '', label: '' }],
        scoringStr:     q.scoring ? JSON.stringify(q.scoring, null, 2) : '',
    }
}

const sections = ref(
    props.test?.sections?.length
        ? props.test.sections.map(hydrateSection)
        : []
)

// ─── Sérialisation pour l'API ─────────────────────────────────────────────────

function serializeQuestion(q, idx) {
    let options = null
    if (q.type === 'scale') {
        options = {
            min: Number(q.scaleMin),
            max: Number(q.scaleMax),
            min_label: q.scaleMinLabel || undefined,
            max_label: q.scaleMaxLabel || undefined,
        }
    } else if (q.type === 'single' || q.type === 'multi') {
        options = q.choices
            .filter(c => c.label)
            .map((c, i) => ({ value: c.value || String(i + 1), label: c.label }))
    }

    let scoring = null
    if (q.scoringStr.trim()) {
        try { scoring = JSON.parse(q.scoringStr) } catch { scoring = null }
    }

    return {
        id:       q.id,
        type:     q.type,
        prompt:   q.prompt,
        helper:   q.helper || null,
        order:    idx,
        required: q.required,
        options,
        scoring,
    }
}

function serializeSections() {
    return sections.value.map((s, si) => ({
        id:              s.id,
        title:           s.title,
        description:     s.description || null,
        narrative_intro: s.narrative_intro || null,
        order:           si,
        questions:       s.questions.map((q, qi) => serializeQuestion(q, qi)),
    }))
}

const structureForm = useForm({ sections: [] })

const saveStructure = () => {
    structureForm.sections = serializeSections()
    structureForm.put(route('admin.tests.structure', props.test.id))
}

const totalQuestions = computed(() =>
    sections.value.reduce((n, s) => n + s.questions.length, 0)
)

// ─── Actions sections ─────────────────────────────────────────────────────────

const addSection = () => {
    sections.value.push(makeSection(sections.value.length))
}

const removeSection = (si) => {
    if (confirm('Supprimer cette section et toutes ses questions ?')) {
        sections.value.splice(si, 1)
    }
}

const moveSectionUp = (si) => {
    if (si === 0) return
    const tmp = sections.value[si]
    sections.value[si] = sections.value[si - 1]
    sections.value[si - 1] = tmp
}

const moveSectionDown = (si) => {
    if (si >= sections.value.length - 1) return
    const tmp = sections.value[si]
    sections.value[si] = sections.value[si + 1]
    sections.value[si + 1] = tmp
}

// ─── Actions questions ────────────────────────────────────────────────────────

const addQuestion = (si) => {
    sections.value[si].questions.push(makeQuestion(sections.value[si].questions.length))
}

const removeQuestion = (si, qi) => {
    sections.value[si].questions.splice(qi, 1)
}

const addChoice = (q) => {
    q.choices.push({ value: '', label: '' })
}

const removeChoice = (q, ci) => {
    if (q.choices.length > 1) q.choices.splice(ci, 1)
}

const moveQuestionUp = (si, qi) => {
    if (qi === 0) return
    const qs = sections.value[si].questions
    const tmp = qs[qi]; qs[qi] = qs[qi - 1]; qs[qi - 1] = tmp
}

const moveQuestionDown = (si, qi) => {
    const qs = sections.value[si].questions
    if (qi >= qs.length - 1) return
    const tmp = qs[qi]; qs[qi] = qs[qi + 1]; qs[qi + 1] = tmp
}
</script>

<template>
    <AdminLayout>
        <Head :title="test?.id ? 'Éditer test' : 'Nouveau test'" />

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">{{ test?.id ? test.name : 'Nouveau test' }}</h1>
            <Link :href="route('admin.tests.index')" class="pt-btn-ghost text-sm">← Tous les tests</Link>
        </div>

        <!-- Flash success -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
            {{ $page.props.flash.success }}
        </div>

        <!-- ═══ Section 1 : Métadonnées ════════════════════════════════════════ -->
        <form @submit.prevent="saveMeta" class="grid lg:grid-cols-3 gap-6 mb-10">
            <section class="pt-card p-6 lg:col-span-2 space-y-5">
                <h2 class="font-semibold text-base text-slate-700">Informations générales</h2>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nom du test</label>
                    <input v-model="meta.name" required class="pt-input mt-2">
                    <p v-if="meta.errors.name" class="text-xs text-rose-600 mt-1">{{ meta.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Slug (URL)</label>
                    <input v-model="meta.slug" required pattern="[a-z0-9\-]+" placeholder="mon-test" class="pt-input mt-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea v-model="meta.description" rows="3" class="pt-input mt-2" placeholder="Visible par le candidat avant de démarrer…"></textarea>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Type</label>
                        <select v-model="meta.type" class="pt-input mt-2">
                            <option value="questionnaire">Questionnaire</option>
                            <option value="situational">Mises en situation</option>
                            <option value="projective">Projectif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Moteur de scoring</label>
                        <select v-if="scoring_engines?.length" v-model="meta.scoring_engine" class="pt-input mt-2">
                            <option v-for="key in scoring_engines" :key="key" :value="key">{{ key }}</option>
                        </select>
                        <input v-else v-model="meta.scoring_engine" class="pt-input mt-2" placeholder="default">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Durée estimée (min)</label>
                        <input v-model.number="meta.estimated_minutes" type="number" min="1" class="pt-input mt-2">
                    </div>
                </div>
            </section>

            <aside class="pt-card p-6 space-y-4 h-fit">
                <h3 class="font-semibold text-sm">Publication</h3>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="meta.published" class="rounded border-slate-300 text-indigo-600">
                    Publié (visible par les candidats)
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="meta.public" class="rounded border-slate-300 text-indigo-600">
                    Accessible sans invitation
                </label>
                <div class="border-t border-slate-100 pt-4">
                    <button type="submit" :disabled="meta.processing" class="pt-btn-primary w-full">
                        {{ meta.processing ? 'Enregistrement…' : test?.id ? 'Mettre à jour' : 'Créer le test' }}
                    </button>
                    <p v-if="meta.recentlySuccessful" class="text-emerald-600 text-xs text-center mt-2">Métadonnées enregistrées ✓</p>
                </div>
            </aside>
        </form>

        <!-- ═══ Section 2 : Structure (sections + questions) ═══════════════════ -->
        <div v-if="test?.id">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold">Structure du test</h2>
                    <p class="text-sm text-slate-500 mt-0.5">{{ sections.length }} section(s) · {{ totalQuestions }} question(s)</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="addSection" class="pt-btn-ghost text-sm">
                        + Ajouter une section
                    </button>
                    <button
                        v-if="sections.length"
                        type="button"
                        @click="saveStructure"
                        :disabled="structureForm.processing"
                        class="pt-btn-primary text-sm"
                    >
                        {{ structureForm.processing ? 'Sauvegarde…' : 'Sauvegarder la structure' }}
                    </button>
                </div>
            </div>

            <!-- Aucune section -->
            <div v-if="!sections.length" class="pt-card p-10 text-center border-dashed">
                <p class="text-slate-400 mb-4">Aucune section. Clique sur "Ajouter une section" pour commencer.</p>
                <button type="button" @click="addSection" class="pt-btn-primary">+ Créer la première section</button>
            </div>

            <!-- Liste des sections -->
            <div class="space-y-4">
                <div
                    v-for="(section, si) in sections"
                    :key="section._key"
                    class="pt-card overflow-hidden"
                >
                    <!-- En-tête section -->
                    <div
                        class="flex items-center gap-3 p-4 cursor-pointer bg-slate-50 border-b border-slate-100 select-none"
                        @click="section.open = !section.open"
                    >
                        <span class="text-slate-400 text-xs font-mono">{{ si + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm truncate">{{ section.title || '(sans titre)' }}</p>
                            <p class="text-xs text-slate-500">{{ section.questions.length }} question(s)</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click.stop="moveSectionUp(si)" class="p-1 text-slate-400 hover:text-slate-700" title="Monter">↑</button>
                            <button type="button" @click.stop="moveSectionDown(si)" class="p-1 text-slate-400 hover:text-slate-700" title="Descendre">↓</button>
                            <button type="button" @click.stop="removeSection(si)" class="p-1 text-rose-400 hover:text-rose-600 ml-1" title="Supprimer">✕</button>
                            <span class="ml-2 text-slate-400 text-xs">{{ section.open ? '▲' : '▼' }}</span>
                        </div>
                    </div>

                    <!-- Corps section (expandable) -->
                    <div v-if="section.open" class="p-5 space-y-4">
                        <!-- Champs section -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Titre de la section *</label>
                                <input v-model="section.title" required class="pt-input text-sm" placeholder="Ex : Tes terrains de jeu">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Intro narrative (optionnel)</label>
                                <input v-model="section.narrative_intro" class="pt-input text-sm" placeholder="Message affiché avant les questions">
                            </div>
                        </div>

                        <!-- Questions -->
                        <div class="border-t border-slate-100 pt-4 space-y-3">
                            <div
                                v-for="(q, qi) in section.questions"
                                :key="q._key"
                                class="border border-slate-200 rounded-xl p-4 bg-white space-y-3"
                            >
                                <!-- Header question -->
                                <div class="flex items-start gap-3">
                                    <span class="text-xs text-slate-400 font-mono mt-1 min-w-[28px]">Q{{ qi + 1 }}</span>
                                    <div class="flex-1 space-y-3">
                                        <!-- Prompt -->
                                        <div>
                                            <input
                                                v-model="q.prompt"
                                                required
                                                class="pt-input text-sm"
                                                placeholder="Texte de la question…"
                                            >
                                        </div>

                                        <!-- Type + helper -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs text-slate-500 mb-1">Type</label>
                                                <select v-model="q.type" class="pt-input text-sm">
                                                    <option v-for="t in TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-500 mb-1">Texte d'aide (optionnel)</label>
                                                <input v-model="q.helper" class="pt-input text-sm" placeholder="Sous-titre de la question">
                                            </div>
                                        </div>

                                        <!-- Options selon le type -->

                                        <!-- Scale -->
                                        <div v-if="q.type === 'scale'" class="grid grid-cols-4 gap-2">
                                            <div>
                                                <label class="block text-xs text-slate-500 mb-1">Min</label>
                                                <input v-model.number="q.scaleMin" type="number" min="0" max="10" class="pt-input text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-500 mb-1">Max</label>
                                                <input v-model.number="q.scaleMax" type="number" min="2" max="10" class="pt-input text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-500 mb-1">Label min</label>
                                                <input v-model="q.scaleMinLabel" class="pt-input text-sm" placeholder="Pas du tout">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-slate-500 mb-1">Label max</label>
                                                <input v-model="q.scaleMaxLabel" class="pt-input text-sm" placeholder="Tout à fait">
                                            </div>
                                        </div>

                                        <!-- Single / Multi -->
                                        <div v-if="q.type === 'single' || q.type === 'multi'">
                                            <label class="block text-xs text-slate-500 mb-2">Options de réponse</label>
                                            <div
                                                v-for="(choice, ci) in q.choices"
                                                :key="ci"
                                                class="flex items-center gap-2 mb-2"
                                            >
                                                <input
                                                    v-model="choice.label"
                                                    class="pt-input text-sm flex-1"
                                                    :placeholder="'Choix ' + (ci + 1)"
                                                >
                                                <input
                                                    v-model="choice.value"
                                                    class="pt-input text-sm w-24"
                                                    placeholder="Valeur"
                                                >
                                                <button type="button" @click="removeChoice(q, ci)" class="text-rose-400 hover:text-rose-600 text-sm px-1">✕</button>
                                            </div>
                                            <button type="button" @click="addChoice(q)" class="text-xs text-indigo-600 hover:underline">+ Ajouter une option</button>
                                        </div>

                                        <!-- Scoring JSON -->
                                        <div>
                                            <label class="block text-xs text-slate-500 mb-1">Scoring (JSON, optionnel)</label>
                                            <textarea
                                                v-model="q.scoringStr"
                                                rows="2"
                                                class="pt-input text-xs font-mono"
                                                placeholder='{"dimension":"realistic","max":5}'
                                            ></textarea>
                                        </div>
                                    </div>

                                    <!-- Actions question -->
                                    <div class="flex flex-col items-center gap-1 flex-shrink-0">
                                        <button type="button" @click="moveQuestionUp(si, qi)" class="p-1 text-slate-300 hover:text-slate-600" title="Monter">↑</button>
                                        <button type="button" @click="moveQuestionDown(si, qi)" class="p-1 text-slate-300 hover:text-slate-600" title="Descendre">↓</button>
                                        <button type="button" @click="removeQuestion(si, qi)" class="p-1 text-rose-300 hover:text-rose-600 mt-1" title="Supprimer">✕</button>
                                    </div>
                                </div>

                                <!-- Requis -->
                                <label class="flex items-center gap-2 text-xs text-slate-500">
                                    <input type="checkbox" v-model="q.required" class="rounded border-slate-300 text-indigo-600">
                                    Question obligatoire
                                </label>
                            </div>

                            <!-- Ajouter question -->
                            <button
                                type="button"
                                @click="addQuestion(si)"
                                class="w-full border border-dashed border-slate-300 rounded-xl py-3 text-sm text-slate-500 hover:border-indigo-400 hover:text-indigo-600 transition"
                            >
                                + Ajouter une question
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bouton sauvegarder flottant en bas -->
            <div v-if="sections.length" class="mt-6 flex justify-end">
                <button
                    type="button"
                    @click="saveStructure"
                    :disabled="structureForm.processing"
                    class="pt-btn-primary px-8 py-3"
                >
                    {{ structureForm.processing ? 'Sauvegarde en cours…' : 'Sauvegarder la structure' }}
                </button>
            </div>
        </div>
    </AdminLayout>
</template>
