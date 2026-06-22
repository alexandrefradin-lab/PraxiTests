<script setup>
import { ref, computed } from 'vue'
import { useForm, Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    profile: Object,
    statuses: Object,
    cv_max_size_kb: Number,
    cv_allowed_mimes: Array,
})

// Mode édition : profil déjà forgé (statut renseigné) → mise à jour, pas création.
const isEdit = computed(() => !!(props.profile && props.profile.status))

const p = props.profile || {}
const manualCv = (p.cv_structured && p.cv_structured.source === 'manual') ? p.cv_structured : null

const form = useForm({
    status: p.status || '',
    status_since: p.status_since ? String(p.status_since).slice(0, 10) : '',
    current_role: p.current_role || '',
    industry: p.industry || '',
    problematique: p.problematique || '',
    cv: null,
    // En édition, le consentement a déjà été donné lors de l'onboarding.
    consent_data: isEdit.value ? true : false,
    consent_marketing: !!p.consent_marketing,
    cv_mode: manualCv ? 'manual' : 'file',
    cv_job_title: manualCv?.job_title || '',
    cv_sector: manualCv?.sector || '',
    cv_years: manualCv?.years || '',
})

// Nom du CV déjà déposé (affiché en édition pour rappel).
const existingCvName = computed(() => p.cv_original_name || null)

const isDragging = ref(false)

// U4 — ancienneté par tranche (moins de friction qu'une date exacte).
// Chaque tranche envoie une date représentative ; le back-end continue de
// recevoir un status_since valide (date) et calcule status_months inchangé.
const tenureOptions = [
    { value: '3',   label: 'Moins de 6 mois' },
    { value: '9',   label: 'Entre 6 mois et 1 an' },
    { value: '18',  label: 'Entre 1 et 2 ans' },
    { value: '42',  label: 'Entre 2 et 5 ans' },
    { value: '90',  label: 'Entre 5 et 10 ans' },
    { value: '132', label: 'Plus de 10 ans' },
]
// Pré-sélection de la tranche d'ancienneté à partir des mois enregistrés.
const tenureFromMonths = (m) => {
    if (m == null) return ''
    if (m < 6) return '3'
    if (m < 12) return '9'
    if (m < 24) return '18'
    if (m < 60) return '42'
    if (m < 120) return '90'
    return '132'
}
const tenure = ref(isEdit.value ? tenureFromMonths(p.status_months) : '')
const onTenureChange = () => {
    const m = parseInt(tenure.value || '0', 10)
    const d = new Date()
    d.setMonth(d.getMonth() - m)
    form.status_since = d.toISOString().slice(0, 10) // YYYY-MM-DD
}

const submit = () => {
    if (isEdit.value) {
        // Spoofing PUT pour autoriser l'upload de fichier (multipart).
        form
            .transform((data) => ({ ...data, _method: 'put' }))
            .post(route('profile.update'), { forceFormData: true })
    } else {
        form.post(route('onboarding.store'), { forceFormData: true })
    }
}

const onDrop = (e) => {
    isDragging.value = false
    const file = e.dataTransfer?.files?.[0]
    if (file) form.cv = file
}

const onFileChange = (e) => {
    form.cv = e.target.files[0] ?? null
}
</script>

<template>
    <CandidateLayout>
        <Head title="Forge ton Identité du Héros" />

        <div class="max-w-[680px] mx-auto px-4 py-8">

            <!-- ── En-tête ── -->
            <div class="text-center mb-8">
                <span
                    class="inline-block px-3 py-1 rounded-full border text-xs tracking-widest uppercase mb-4"
                    style="font-family:'Space Mono',monospace; color:var(--color-primary); border-color:var(--color-primary); background:var(--bg-surface);"
                >
                    {{ isEdit ? 'Identité du Héros — Mise à jour' : 'Identité du Héros — Étape 1/1' }}
                </span>

                <!-- Ligne décorative or -->
                <div class="flex items-center gap-3 justify-center mb-5">
                    <div class="h-px flex-1 max-w-[80px]" style="background:linear-gradient(to right, transparent, var(--color-primary));"></div>
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 0L9.6 6.4L16 8L9.6 9.6L8 16L6.4 9.6L0 8L6.4 6.4L8 0Z" fill="var(--color-primary)"/>
                    </svg>
                    <div class="h-px flex-1 max-w-[80px]" style="background:linear-gradient(to left, transparent, var(--color-primary));"></div>
                </div>

                <h1
                    class="text-3xl font-bold tracking-tight mb-2"
                    style="font-family:'Space Grotesk',sans-serif; color:var(--text-primary);"
                >
                    {{ isEdit ? 'Mets à jour ton Identité' : 'Forge ton Identité du Héros' }}
                </h1>
                <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                    {{ isEdit
                        ? 'Modifie ton statut, ton parcours ou ton CV. Tes informations actuelles sont pré-remplies.'
                        : 'Ces informations personnalisent ton Grimoire de Synthèse.' }}
                </p>
            </div>

            <!-- ── Formulaire ── -->
            <form @submit.prevent="submit" class="pt-card p-8 space-y-8">

                <!-- Bandeau d'erreur global : évite tout échec silencieux -->
                <div
                    v-if="Object.keys(form.errors).length"
                    class="rounded-lg border px-4 py-3 text-sm"
                    style="border-color:var(--color-secondary); background:rgba(166,32,32,0.06); color:var(--color-secondary); font-family:'Inter',sans-serif;"
                >
                    <p class="font-semibold mb-1">Impossible de forger ton Identité :</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        <li v-for="(message, field) in form.errors" :key="field">{{ message }}</li>
                    </ul>
                </div>

                <!-- Section 1 : Statut -->
                <div class="space-y-5">
                    <div class="flex items-center gap-3">
                        <span
                            class="text-xs font-bold uppercase tracking-widest whitespace-nowrap"
                            style="font-family:'Space Mono',monospace; color:var(--color-primary);"
                        >I — Statut</span>
                        <div class="h-px flex-1" style="background:var(--glass-border);"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                            Ton statut actuel <span style="color:var(--color-secondary);">*</span>
                        </label>
                        <select v-model="form.status" class="pt-input" required>
                            <option value="" disabled>— Choisir ton statut —</option>
                            <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.status" class="text-xs mt-1" style="color:var(--color-secondary);">{{ form.errors.status }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                            Depuis quand ? <span style="color:var(--color-secondary);">*</span>
                        </label>
                        <select v-model="tenure" @change="onTenureChange" class="pt-input" required>
                            <option value="" disabled>— Choisir une tranche —</option>
                            <option v-for="opt in tenureOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                        <p v-if="form.errors.status_since" class="text-xs mt-1" style="color:var(--color-secondary);">{{ form.errors.status_since }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1.5" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                                Poste actuel
                                <span class="font-normal text-xs" style="color:var(--text-secondary);">(optionnel)</span>
                            </label>
                            <input type="text" v-model="form.current_role" class="pt-input" placeholder="Ex : Chef de projet">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1.5" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                                Secteur
                                <span class="font-normal text-xs" style="color:var(--text-secondary);">(optionnel)</span>
                            </label>
                            <input type="text" v-model="form.industry" class="pt-input" placeholder="Ex : Industrie">
                        </div>
                    </div>
                </div>

                <!-- Section 2 : Problématique / Quête -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="text-xs font-bold uppercase tracking-widest whitespace-nowrap"
                            style="font-family:'Space Mono',monospace; color:var(--color-primary);"
                        >II — Ta Quête</span>
                        <div class="h-px flex-1" style="background:var(--glass-border);"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                            Quelle est ta problématique aujourd'hui ? <span style="color:var(--color-secondary);">*</span>
                        </label>
                        <p class="text-xs mb-2" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                            En quelques lignes : ce qui t'amène, la question ou le blocage que tu veux éclaircir. Cela oriente ta synthèse et tes pistes de métiers.
                        </p>
                        <textarea
                            v-model="form.problematique"
                            class="pt-input"
                            rows="4"
                            maxlength="1000"
                            required
                            placeholder="Ex : Je me sens à l'étroit dans mon poste actuel et j'hésite entre évoluer en interne ou me reconvertir, sans savoir vers quoi."
                        ></textarea>
                        <p v-if="form.errors.problematique" class="text-xs mt-1" style="color:var(--color-secondary);">{{ form.errors.problematique }}</p>
                    </div>
                </div>

                <!-- Section 3 : CV / Codex de Compétences -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="text-xs font-bold uppercase tracking-widest whitespace-nowrap"
                            style="font-family:'Space Mono',monospace; color:var(--color-primary);"
                        >III — Codex de Compétences</span>
                        <div class="h-px flex-1" style="background:var(--glass-border);"></div>
                    </div>

                    <!-- Mode : upload fichier -->
                    <template v-if="form.cv_mode === 'file'">
                        <label
                            class="flex flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed cursor-pointer transition-all duration-200 py-10 px-6 text-center"
                            :style="{
                                background: isDragging ? 'var(--bg-elevated)' : 'var(--bg-surface)',
                                borderColor: isDragging ? 'var(--color-primary)' : 'rgba(166,117,32,0.4)',
                            }"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="onDrop"
                        >
                            <input
                                type="file"
                                class="sr-only"
                                :accept="cv_allowed_mimes.map(m => '.' + m).join(',')"
                                @change="onFileChange"
                            >
                            <i class="ti ti-file-upload text-4xl" style="color:var(--color-primary);"></i>
                            <div>
                                <p class="text-sm font-medium" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                                    Dépose ton <span style="color:var(--color-primary);">Codex de Compétences</span> (PDF/DOCX)
                                </p>
                                <p class="text-xs mt-1" style="color:var(--text-secondary);">
                                    {{ cv_allowed_mimes.join(', ').toUpperCase() }} — max {{ Math.round(cv_max_size_kb / 1024) }} Mo
                                </p>
                                <p v-if="form.cv" class="text-xs mt-2 font-semibold" style="color:var(--color-success);">
                                    ✓ {{ form.cv.name }}
                                </p>
                                <p v-else-if="isEdit && existingCvName" class="text-xs mt-2" style="color:var(--text-secondary);">
                                    CV actuel : <span style="color:var(--color-primary);">{{ existingCvName }}</span> — dépose un fichier pour le remplacer.
                                </p>
                            </div>
                        </label>
                        <p v-if="form.errors.cv" class="text-xs" style="color:var(--color-secondary);">{{ form.errors.cv }}</p>

                        <p class="text-center">
                            <span
                                class="cursor-pointer transition-opacity hover:opacity-70"
                                style="font-size:12px; color:var(--color-primary); font-family:'Inter',sans-serif; text-decoration:underline; text-underline-offset:3px; text-decoration-color:rgba(166,117,32,0.5);"
                                @click="form.cv_mode = 'manual'"
                            >
                                Tu n'as pas ton CV sous la main ? → Saisis 3 infos
                            </span>
                        </p>
                    </template>

                    <!-- Mode : saisie manuelle -->
                    <template v-else>
                        <p class="text-xs" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                            Renseigne ces 3 informations — elles alimenteront ton Grimoire de Synthèse.
                        </p>

                        <div>
                            <label class="block text-sm font-medium mb-1.5" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                                Métier actuel ou dernier poste
                            </label>
                            <input type="text" v-model="form.cv_job_title" class="pt-input" placeholder="Ex : Développeur Full Stack">
                            <p v-if="form.errors.cv_job_title" class="text-xs mt-1" style="color:var(--color-secondary);">{{ form.errors.cv_job_title }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1.5" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                                    Secteur d'activité
                                </label>
                                <input type="text" v-model="form.cv_sector" class="pt-input" placeholder="Ex : Tech, Santé, Finance…">
                                <p v-if="form.errors.cv_sector" class="text-xs mt-1" style="color:var(--color-secondary);">{{ form.errors.cv_sector }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1.5" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                                    Durée d'expérience totale
                                </label>
                                <input type="text" v-model="form.cv_years" class="pt-input" placeholder="Ex : 5 ans">
                                <p v-if="form.errors.cv_years" class="text-xs mt-1" style="color:var(--color-secondary);">{{ form.errors.cv_years }}</p>
                            </div>
                        </div>

                        <p class="text-center">
                            <span
                                class="cursor-pointer transition-opacity hover:opacity-70"
                                style="font-size:12px; color:var(--color-primary); font-family:'Inter',sans-serif; text-decoration:underline; text-underline-offset:3px; text-decoration-color:rgba(166,117,32,0.5);"
                                @click="form.cv_mode = 'file'; form.cv = null"
                            >
                                ← Finalement, je préfère uploader mon CV
                            </span>
                        </p>
                    </template>
                </div>

                <!-- Section 4 : Consentements / Serments -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="text-xs font-bold uppercase tracking-widest whitespace-nowrap"
                            style="font-family:'Space Mono',monospace; color:var(--color-primary);"
                        >IV — Serments</span>
                        <div class="h-px flex-1" style="background:var(--glass-border);"></div>
                    </div>

                    <label v-if="!isEdit" class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.consent_data"
                            class="mt-0.5 rounded shrink-0"
                            style="accent-color:var(--color-primary);"
                            required
                        >
                        <span class="text-sm leading-relaxed" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                            J'accepte l'analyse de mon Identité par l'IA pour révéler mon Grimoire.
                            <span style="color:var(--color-secondary);">*</span>
                        </span>
                    </label>
                    <p v-if="form.errors.consent_data" class="text-xs pl-7" style="color:var(--color-secondary);">{{ form.errors.consent_data }}</p>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.consent_marketing"
                            class="mt-0.5 rounded shrink-0"
                            style="accent-color:var(--color-primary);"
                        >
                        <span class="text-sm leading-relaxed" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                            Je veux recevoir des Convocations personnalisées par email
                            <span class="text-xs">(optionnel)</span>.
                        </span>
                    </label>
                </div>

                <!-- Bouton submit -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="pt-btn-primary w-full py-3 text-base font-semibold tracking-wide"
                        style="font-family:'Space Grotesk',sans-serif;"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ isEdit ? 'Mise à jour…' : 'Forge en cours…' }}
                        </span>
                        <span v-else>{{ isEdit ? '✓ Enregistrer mes modifications' : '⚔ Forger mon Identité' }}</span>
                    </button>
                </div>

            </form>
        </div>
    </CandidateLayout>
</template>
