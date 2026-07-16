<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ConfirmModal from '@/Components/Admin/ConfirmModal.vue'

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
    { value: 'scale',       label: 'Échelle (1–N)' },
    { value: 'single',      label: 'Choix unique' },
    { value: 'multi',       label: 'Choix multiple' },
    { value: 'text',        label: 'Texte libre' },
    { value: 'ranking',     label: 'Classement' },
    { value: 'situational', label: 'Mise en situation' },
    { value: 'exercise',    label: 'Exercice' },
]

// Types dont les options n'ont pas (encore) d'UI dédiée : édition en JSON brut.
const ADVANCED_TYPES = ['ranking', 'situational', 'exercise']

// Message d'erreur si la chaîne n'est pas un JSON valide (null si vide ou valide).
const jsonError = (str) => {
    if (!str || !str.trim()) return null
    try { JSON.parse(str); return null } catch (e) { return 'JSON invalide — ' + e.message }
}

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
        // ranking / situational / exercise : options en JSON brut
        optionsStr: '',
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
        // Types avancés : on préserve les options existantes en JSON brut
        // (avant, elles étaient silencieusement écrasées à la sauvegarde).
        optionsStr:     ADVANCED_TYPES.includes(q.type) && q.options != null
            ? JSON.stringify(q.options, null, 2) : '',
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
    } else if (ADVANCED_TYPES.includes(q.type) && q.optionsStr.trim()) {
        options = JSON.parse(q.optionsStr)
    }

    // Un JSON invalide bloque la sauvegarde en amont (structureJsonInvalid) —
    // plus jamais d'écrasement silencieux en null.
    const scoring = q.scoringStr.trim() ? JSON.parse(q.scoringStr) : null

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

// Vrai si au moins un champ JSON (scoring ou options avancées) est invalide.
const structureJsonInvalid = computed(() =>
    sections.value.some(s => s.questions.some(q =>
        jsonError(q.scoringStr) || (ADVANCED_TYPES.includes(q.type) && jsonError(q.optionsStr))
    ))
)

const saveStructure = () => {
    if (structureJsonInvalid.value) return
    structureForm.sections = serializeSections()
    structureForm.put(route('admin.tests.structure', props.test.id), {
        onSuccess: () => { savedSnapshot.value = snapshot() },
    })
}

const totalQuestions = computed(() =>
    sections.value.reduce((n, s) => n + s.questions.length, 0)
)

// ─── Garde « modifications non enregistrées » ────────────────────────────────
// Snapshot de la structure hors clés d'UI (_key, open) : replier une section
// ne compte pas comme une modification.
const snapshot = () => JSON.stringify(
    sections.value,
    (k, v) => (k === '_key' || k === 'open') ? undefined : v,
)
const savedSnapshot = ref(snapshot())
const isDirty = computed(() => meta.isDirty || snapshot() !== savedSnapshot.value)

const onBeforeUnload = (e) => {
    if (isDirty.value) { e.preventDefault(); e.returnValue = '' }
}
window.addEventListener('beforeunload', onBeforeUnload)

// Navigation Inertia (liens internes) : on ne garde que les GET — les
// soumissions de formulaires (put/post) doivent passer.
const removeNavGuard = router.on('before', (event) => {
    if (event.detail.visit.method === 'get' && isDirty.value
        && !window.confirm('Des modifications non enregistrées seront perdues. Quitter quand même ?')) {
        event.preventDefault()
    }
})

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', onBeforeUnload)
    removeNavGuard()
})

// ─── Actions sections ─────────────────────────────────────────────────────────

const addSection = () => {
    sections.value.push(makeSection(sections.value.length))
}

// Suppression de section : ConfirmModal (thème) au lieu du confirm() natif
const confirmingSectionRemoval = ref(null)   // index de la section, ou null

const removeSection = (si) => { confirmingSectionRemoval.value = si }

const doRemoveSection = () => {
    if (confirmingSectionRemoval.value !== null) {
        sections.value.splice(confirmingSectionRemoval.value, 1)
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
            <h1 class="text-2xl font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">{{ test?.id ? test.name : 'Nouveau test' }}</h1>
            <Link :href="route('admin.tests.index')" class="pt-btn-ghost text-sm">← Tous les tests</Link>
        </div>

        <!-- Flash success -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-3 rounded-lg text-sm" style="background:color-mix(in srgb, var(--color-success) 12%, transparent);border:1px solid color-mix(in srgb, var(--color-success) 30%, transparent);color:var(--color-success)">
            {{ $page.props.flash.success }}
        </div>

        <!-- ═══ Section 1 : Métadonnées ════════════════════════════════════════ -->
        <form @submit.prevent="saveMeta" class="grid lg:grid-cols-3 gap-6 mb-10">
            <section class="pt-card p-6 lg:col-span-2 space-y-5">
                <h2 class="font-semibold text-base" style="font-family:var(--font-display);color:var(--text-secondary)">Informations générales</h2>

                <div>
                    <label class="pt-label">Nom du test</label>
                    <input v-model="meta.name" required class="pt-input mt-2">
                    <p v-if="meta.errors.name" class="text-xs mt-1" style="color:var(--color-danger)">{{ meta.errors.name }}</p>
                </div>
                <div>
                    <label class="pt-label">Slug (URL)</label>
                    <input v-model="meta.slug" required pattern="[a-z0-9\-]+" placeholder="mon-test" class="pt-input mt-2">
                </div>
                <div>
                    <label class="pt-label">Description</label>
                    <textarea v-model="meta.description" rows="3" class="pt-input mt-2" placeholder="Visible par le candidat avant de démarrer…"></textarea>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="pt-label">Type</label>
                        <select v-model="meta.type" class="pt-input mt-2">
                            <option value="questionnaire">Questionnaire</option>
                            <option value="situational">Mises en situation</option>
                            <option value="projective">Projectif</option>
                        </select>
                    </div>
                    <div>
                        <label class="pt-label">Moteur de scoring</label>
                        <select v-if="scoring_engines?.length" v-model="meta.scoring_engine" class="pt-input mt-2">
                            <option v-for="key in scoring_engines" :key="key" :value="key">{{ key }}</option>
                        </select>
                        <input v-else v-model="meta.scoring_engine" class="pt-input mt-2" placeholder="default">
                    </div>
                    <div>
                        <label class="pt-label">Durée estimée (min)</label>
                        <input v-model.number="meta.estimated_minutes" type="number" min="1" class="pt-input mt-2">
                    </div>
                </div>
            </section>

            <aside class="pt-card p-6 space-y-4 h-fit">
                <h3 class="font-semibold text-sm" style="font-family:var(--font-display);color:var(--text-primary)">Publication</h3>
                <label class="flex items-center gap-2 text-sm" style="color:var(--text-secondary)">
                    <input type="checkbox" v-model="meta.published" class="ac-checkbox">
                    Publié (visible par les candidats)
                </label>
                <label class="flex items-center gap-2 text-sm" style="color:var(--text-secondary)">
                    <input type="checkbox" v-model="meta.public" class="ac-checkbox">
                    Accessible sans invitation
                </label>
                <div class="border-t pt-4" style="border-color:var(--border-light)">
                    <button type="submit" :disabled="meta.processing" class="pt-btn-primary w-full">
                        {{ meta.processing ? 'Enregistrement…' : test?.id ? 'Mettre à jour' : 'Créer le test' }}
                    </button>
                    <p v-if="meta.recentlySuccessful" class="text-xs text-center mt-2" style="color:var(--color-success)">Métadonnées enregistrées ✓</p>
                </div>
            </aside>
        </form>

        <!-- ═══ Section 2 : Structure (sections + questions) ═══════════════════ -->
        <div v-if="test?.id">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">Structure du test</h2>
                    <p class="text-sm mt-0.5" style="color:var(--text-muted)">{{ sections.length }} section(s) · {{ totalQuestions }} question(s)</p>
                </div>
                <div class="flex items-center gap-3">
                    <span v-if="isDirty" class="text-xs" style="color:var(--color-warning, #B8860B)">● Modifications non enregistrées</span>
                    <button type="button" @click="addSection" class="pt-btn-ghost text-sm">
                        + Ajouter une section
                    </button>
                    <button
                        v-if="sections.length"
                        type="button"
                        @click="saveStructure"
                        :disabled="structureForm.processing || structureJsonInvalid"
                        :title="structureJsonInvalid ? 'Corrige les champs JSON invalides avant de sauvegarder' : undefined"
                        class="pt-btn-primary text-sm"
                    >
                        {{ structureForm.processing ? 'Sauvegarde…' : 'Sauvegarder la structure' }}
                    </button>
                </div>
            </div>

            <!-- Aucune section -->
            <div v-if="!sections.length" class="pt-card p-10 text-center border-dashed">
                <p class="mb-4" style="color:var(--text-muted)">Aucune section. Clique sur "Ajouter une section" pour commencer.</p>
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
                        class="flex items-center gap-3 p-4 cursor-pointer border-b select-none"
                        style="background:var(--bg-elevated);border-color:var(--border-light)"
                        @click="section.open = !section.open"
                    >
                        <span class="text-xs font-mono" style="color:var(--text-muted)">{{ si + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm truncate" style="color:var(--text-primary)">{{ section.title || '(sans titre)' }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ section.questions.length }} question(s)</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click.stop="moveSectionUp(si)" class="p-1" style="color:var(--text-muted)" title="Monter" aria-label="Monter la section">↑</button>
                            <button type="button" @click.stop="moveSectionDown(si)" class="p-1" style="color:var(--text-muted)" title="Descendre" aria-label="Descendre la section">↓</button>
                            <button type="button" @click.stop="removeSection(si)" class="p-1 ml-1" style="color:var(--color-danger)" title="Supprimer" aria-label="Supprimer la section">✕</button>
                            <span class="ml-2 text-xs" style="color:var(--text-muted)">{{ section.open ? '▲' : '▼' }}</span>
                        </div>
                    </div>

                    <!-- Corps section (expandable) -->
                    <div v-if="section.open" class="p-5 space-y-4">
                        <!-- Champs section -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="pt-label mb-1">Titre de la section *</label>
                                <input v-model="section.title" required class="pt-input text-sm" placeholder="Ex : Tes terrains de jeu">
                            </div>
                            <div>
                                <label class="pt-label mb-1">Intro narrative (optionnel)</label>
                                <input v-model="section.narrative_intro" class="pt-input text-sm" placeholder="Message affiché avant les questions">
                            </div>
                        </div>

                        <!-- Questions -->
                        <div class="border-t pt-4 space-y-3" style="border-color:var(--border-light)">
                            <div
                                v-for="(q, qi) in section.questions"
                                :key="q._key"
                                class="border rounded-xl p-4 space-y-3"
                                style="border-color:var(--border-light);background:var(--bg-surface)"
                            >
                                <!-- Header question -->
                                <div class="flex items-start gap-3">
                                    <span class="text-xs font-mono mt-1 min-w-[28px]" style="color:var(--text-muted)">Q{{ qi + 1 }}</span>
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
                                                <label class="block text-xs mb-1" style="color:var(--text-muted)">Type</label>
                                                <select v-model="q.type" class="pt-input text-sm">
                                                    <option v-for="t in TYPES" :key="t.value" :value="t.value">{{ t.label }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs mb-1" style="color:var(--text-muted)">Texte d'aide (optionnel)</label>
                                                <input v-model="q.helper" class="pt-input text-sm" placeholder="Sous-titre de la question">
                                            </div>
                                        </div>

                                        <!-- Options selon le type -->

                                        <!-- Scale -->
                                        <div v-if="q.type === 'scale'" class="grid grid-cols-4 gap-2">
                                            <div>
                                                <label class="block text-xs mb-1" style="color:var(--text-muted)">Min</label>
                                                <input v-model.number="q.scaleMin" type="number" min="0" max="10" class="pt-input text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs mb-1" style="color:var(--text-muted)">Max</label>
                                                <input v-model.number="q.scaleMax" type="number" min="2" max="10" class="pt-input text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-xs mb-1" style="color:var(--text-muted)">Label min</label>
                                                <input v-model="q.scaleMinLabel" class="pt-input text-sm" placeholder="Pas du tout">
                                            </div>
                                            <div>
                                                <label class="block text-xs mb-1" style="color:var(--text-muted)">Label max</label>
                                                <input v-model="q.scaleMaxLabel" class="pt-input text-sm" placeholder="Tout à fait">
                                            </div>
                                        </div>

                                        <!-- Single / Multi -->
                                        <div v-if="q.type === 'single' || q.type === 'multi'">
                                            <label class="block text-xs mb-2" style="color:var(--text-muted)">Options de réponse</label>
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
                                                <button type="button" @click="removeChoice(q, ci)" class="text-sm px-1" style="color:var(--color-danger)">✕</button>
                                            </div>
                                            <button type="button" @click="addChoice(q)" class="ac-link-primary text-xs">+ Ajouter une option</button>
                                        </div>

                                        <!-- Ranking / Situational / Exercise : options en JSON brut -->
                                        <div v-if="ADVANCED_TYPES.includes(q.type)">
                                            <label class="block text-xs mb-1" style="color:var(--text-muted)">Options (JSON — type avancé)</label>
                                            <textarea
                                                v-model="q.optionsStr"
                                                rows="3"
                                                class="pt-input text-xs font-mono"
                                                placeholder='["Élément A", "Élément B", "Élément C"]'
                                            ></textarea>
                                            <p v-if="jsonError(q.optionsStr)" class="text-xs mt-1" style="color:var(--color-danger)">{{ jsonError(q.optionsStr) }}</p>
                                        </div>

                                        <!-- Scoring JSON -->
                                        <div>
                                            <label class="block text-xs mb-1" style="color:var(--text-muted)">Scoring (JSON, optionnel)</label>
                                            <textarea
                                                v-model="q.scoringStr"
                                                rows="2"
                                                class="pt-input text-xs font-mono"
                                                placeholder='{"dimension":"realistic","max":5}'
                                            ></textarea>
                                            <p v-if="jsonError(q.scoringStr)" class="text-xs mt-1" style="color:var(--color-danger)">{{ jsonError(q.scoringStr) }}</p>
                                        </div>
                                    </div>

                                    <!-- Actions question -->
                                    <div class="flex flex-col items-center gap-1 flex-shrink-0">
                                        <button type="button" @click="moveQuestionUp(si, qi)" class="p-1" style="color:var(--text-muted)" title="Monter" aria-label="Monter la question">↑</button>
                                        <button type="button" @click="moveQuestionDown(si, qi)" class="p-1" style="color:var(--text-muted)" title="Descendre" aria-label="Descendre la question">↓</button>
                                        <button type="button" @click="removeQuestion(si, qi)" class="p-1 mt-1" style="color:var(--color-danger)" title="Supprimer" aria-label="Supprimer la question">✕</button>
                                    </div>
                                </div>

                                <!-- Requis -->
                                <label class="flex items-center gap-2 text-xs" style="color:var(--text-muted)">
                                    <input type="checkbox" v-model="q.required" class="ac-checkbox">
                                    Question obligatoire
                                </label>
                            </div>

                            <!-- Ajouter question -->
                            <button
                                type="button"
                                @click="addQuestion(si)"
                                class="pt-add-question w-full border border-dashed rounded-xl py-3 text-sm transition"
                            >
                                + Ajouter une question
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bouton sauvegarder flottant en bas -->
            <div v-if="sections.length" class="mt-6 flex items-center justify-end gap-4">
                <p v-if="structureJsonInvalid" class="text-xs" style="color:var(--color-danger)">
                    Un champ JSON (scoring ou options) est invalide — corrige-le pour pouvoir sauvegarder.
                </p>
                <button
                    type="button"
                    @click="saveStructure"
                    :disabled="structureForm.processing || structureJsonInvalid"
                    class="pt-btn-primary px-8 py-3"
                >
                    {{ structureForm.processing ? 'Sauvegarde en cours…' : 'Sauvegarder la structure' }}
                </button>
            </div>
        </div>

        <ConfirmModal
            :show="confirmingSectionRemoval !== null"
            @update:show="confirmingSectionRemoval = null"
            title="Supprimer cette section ?"
            confirm-label="Supprimer" danger
            @confirm="doRemoveSection"
        >
            « {{ sections[confirmingSectionRemoval]?.title || 'Sans titre' }} » et ses
            {{ sections[confirmingSectionRemoval]?.questions.length ?? 0 }} question(s) seront retirées.
            La suppression ne devient définitive qu'à la sauvegarde de la structure.
        </ConfirmModal>
    </AdminLayout>
</template>

<style scoped>
.pt-add-question {
    border-color: var(--border-mid);
    color: var(--text-muted);
}
.pt-add-question:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
}
</style>
