<script setup>
/**
 * Questionnaire de questionnement sur les croyances bloquantes.
 *
 * 5 étapes progressives pour aider l'utilisateur à identifier
 * ce qui l'a empêché d'agir, et trouver un premier pas minuscule.
 *
 * Affiché depuis le lien dans l'email de relance journalière.
 */
import { ref, computed } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    plugin:      { type: String, required: true },
    day:         { type: Number, required: true },
    pluginLabel: { type: String, required: true },
    actionUrl:   { type: String, required: true },
})

// ── Étapes ─────────────────────────────────────────────────────────────────
const steps = [
    {
        id: 1,
        emoji: '🪞',
        title: 'Qu\'est-ce qui s\'est passé ?',
        hint: 'Pas de jugement ici. Décris simplement ce qui a fait que l\'action est restée en attente aujourd\'hui.',
        type: 'textarea',
        field: 'q1_obstacle',
        placeholder: 'Aujourd\'hui, je n\'ai pas agi parce que…',
    },
    {
        id: 2,
        emoji: '🔍',
        title: 'Ce qui t\'a bloqué ressemble le plus à…',
        hint: 'Choisir honnêtement, c\'est déjà à moitié résoudre.',
        type: 'choice',
        field: 'q2_category',
        choices: [
            { value: 'peur',     label: '😰 Une peur', desc: 'Peur d\'échouer, d\'être jugé, que ça ne serve à rien…' },
            { value: 'fatigue',  label: '😴 La fatigue', desc: 'Manque d\'énergie, journée chargée, épuisement.' },
            { value: 'temps',    label: '⏳ Le manque de temps', desc: 'Agenda plein, urgences, pas trouvé le créneau.' },
            { value: 'croyance', label: '💭 Une croyance sur moi', desc: '"Je ne suis pas fait pour ça", "ça ne changera rien"…' },
            { value: 'autre',    label: '🌀 Autre chose', desc: 'Une raison différente, difficile à nommer.' },
        ],
    },
    {
        id: 3,
        emoji: '📏',
        title: 'À quel point ce blocage est-il vraiment insurmontable ?',
        hint: 'Sur 10 : 0 = "je pourrais agir maintenant", 10 = "c\'est totalement impossible".',
        type: 'slider',
        field: 'q3_score',
    },
    {
        id: 4,
        emoji: '🤝',
        title: 'Et si c\'était ton meilleur ami qui te disait ça ?',
        hint: 'Il te donne exactement la même raison que tu viens de formuler. Que lui répondrais-tu — avec bienveillance et honnêteté ?',
        type: 'textarea',
        field: 'q4_friend_advice',
        placeholder: 'Je lui dirais que…',
    },
    {
        id: 5,
        emoji: '🌱',
        title: 'Quel est le pas le plus petit possible ?',
        hint: 'Quelque chose de si simple que le refuser serait ridicule. 30 secondes. Une phrase. Un geste.',
        type: 'textarea',
        field: 'q5_small_step',
        placeholder: 'Le truc minuscule que je pourrais faire là, maintenant…',
    },
]

// ── État ────────────────────────────────────────────────────────────────────
const currentStep = ref(0) // 0-based index
const step        = computed(() => steps[currentStep.value])

const form = useForm({
    plugin:           props.plugin,
    day:              props.day,
    q1_obstacle:      '',
    q2_category:      '',
    q3_score:         5,
    q4_friend_advice: '',
    q5_small_step:    '',
})

// ── Navigation ──────────────────────────────────────────────────────────────
const canNext = computed(() => {
    if (step.value.type === 'choice') {
        return !!form[step.value.field]
    }
    return true // optionnel pour les autres types
})

function next() {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++
    }
}

function prev() {
    if (currentStep.value > 0) {
        currentStep.value--
    }
}

function submit() {
    form.post(route('beliefs.store'), { preserveScroll: true })
}

// Label humain du score
const scoreLabel = computed(() => {
    const s = form.q3_score
    if (s <= 2) return 'Très surmontable'
    if (s <= 4) return 'Difficile mais possible'
    if (s <= 6) return 'Vraiment bloquant'
    if (s <= 8) return 'Très dur à dépasser'
    return 'Perçu comme insurmontable'
})

// Barre de progression
const progress = computed(() => Math.round(((currentStep.value) / steps.length) * 100))
</script>

<template>
    <CandidateLayout>
        <Head title="Comprendre ce qui me bloque" />

        <div class="max-w-xl mx-auto">

            <!-- Fil d'Ariane -->
            <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-secondary); margin-bottom: 0.4rem;">
                {{ pluginLabel }} · Jour {{ day }}
            </p>
            <h1 style="font-family: var(--font-display); font-size: 1.8rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.15; margin-bottom: 0.5rem;">
                Comprendre ce qui me bloque
            </h1>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                5 questions pour transformer un blocage en insight — et retrouver de l'élan.
            </p>

            <!-- Barre de progression étapes -->
            <div style="height: 6px; background: var(--border, #e5e7eb); border-radius: 999px; overflow: hidden; margin-bottom: 2rem;">
                <div :style="{ width: progress + '%', height: '100%', background: 'var(--primary, #A67520)', transition: 'width .4s' }"></div>
            </div>

            <!-- Carte de l'étape courante -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 14px); padding: 2rem 1.8rem; min-height: 320px; display: flex; flex-direction: column;">

                <!-- En-tête étape -->
                <div style="margin-bottom: 1.4rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">{{ step.emoji }}</div>
                    <h2 style="font-family: var(--font-display); font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.4rem;">
                        {{ step.title }}
                    </h2>
                    <p style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6; margin: 0;">
                        {{ step.hint }}
                    </p>
                </div>

                <!-- Corps dynamique -->
                <div style="flex: 1;">

                    <!-- Textarea -->
                    <template v-if="step.type === 'textarea'">
                        <textarea
                            v-model="form[step.field]"
                            :placeholder="step.placeholder"
                            rows="5"
                            style="width: 100%; box-sizing: border-box; border: 1px solid var(--border, #e5e7eb); border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.95rem; font-family: var(--font-body); color: var(--text-primary); background: var(--bg, #FFFDF7); resize: vertical; outline: none; line-height: 1.6;"
                        ></textarea>
                    </template>

                    <!-- Choix multiples -->
                    <template v-else-if="step.type === 'choice'">
                        <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                            <button
                                v-for="c in step.choices"
                                :key="c.value"
                                type="button"
                                @click="form[step.field] = c.value"
                                :style="{
                                    display: 'flex', alignItems: 'flex-start', gap: '0.8rem',
                                    textAlign: 'left', width: '100%', cursor: 'pointer',
                                    padding: '0.75rem 1rem', borderRadius: '10px',
                                    border: form[step.field] === c.value
                                        ? '2px solid var(--primary, #A67520)'
                                        : '1px solid var(--border, #e5e7eb)',
                                    background: form[step.field] === c.value
                                        ? 'rgba(166,117,32,.06)'
                                        : 'var(--surface, #fff)',
                                    transition: 'border .15s, background .15s',
                                }"
                            >
                                <span style="font-size: 1.1rem; flex-shrink: 0; margin-top: 1px;">{{ c.label.split(' ')[0] }}</span>
                                <span>
                                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">{{ c.label.split(' ').slice(1).join(' ') }}</span>
                                    <br>
                                    <span style="font-size: 0.82rem; color: var(--text-secondary);">{{ c.desc }}</span>
                                </span>
                            </button>
                        </div>
                    </template>

                    <!-- Slider -->
                    <template v-else-if="step.type === 'slider'">
                        <div style="padding: 1rem 0;">
                            <input
                                type="range" min="0" max="10"
                                v-model.number="form[step.field]"
                                style="width: 100%; accent-color: var(--primary, #A67520); height: 6px;"
                            />
                            <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--text-secondary); margin-top: 0.4rem;">
                                <span>0 — Surmontable</span>
                                <span>10 — Insurmontable</span>
                            </div>
                            <div style="text-align: center; margin-top: 1rem;">
                                <span style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 800; color: var(--primary, #A67520);">{{ form[step.field] }}</span>
                                <span style="display: block; font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">{{ scoreLabel }}</span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Navigation -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.8rem;">
                    <button
                        v-if="currentStep > 0"
                        type="button"
                        @click="prev"
                        style="background: none; border: 1px solid var(--border, #e5e7eb); border-radius: 8px; padding: 0.55rem 1rem; font-size: 0.88rem; color: var(--text-secondary); cursor: pointer;"
                    >
                        ← Retour
                    </button>
                    <span v-else></span>

                    <!-- Indicateur étapes -->
                    <div style="display: flex; gap: 6px;">
                        <span
                            v-for="(s, i) in steps"
                            :key="s.id"
                            :style="{
                                width: '8px', height: '8px', borderRadius: '50%',
                                background: i === currentStep
                                    ? 'var(--primary, #A67520)'
                                    : i < currentStep
                                        ? '#10B981'
                                        : 'var(--border, #e5e7eb)',
                                transition: 'background .2s',
                            }"
                        ></span>
                    </div>

                    <!-- Dernière étape → soumettre -->
                    <button
                        v-if="currentStep < steps.length - 1"
                        type="button"
                        @click="next"
                        :disabled="!canNext"
                        :style="{
                            background: canNext ? 'var(--primary, #A67520)' : 'var(--border, #e5e7eb)',
                            color: canNext ? '#1C1408' : 'var(--text-secondary)',
                            border: 'none', borderRadius: '8px',
                            padding: '0.55rem 1.2rem', fontWeight: 700,
                            fontSize: '0.9rem', cursor: canNext ? 'pointer' : 'default',
                            transition: 'background .15s',
                        }"
                    >
                        Continuer →
                    </button>

                    <button
                        v-else
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        style="background: #10B981; color: #fff; border: none; border-radius: 8px; padding: 0.6rem 1.4rem; font-weight: 700; font-size: 0.95rem; cursor: pointer;"
                    >
                        {{ form.processing ? 'Enregistrement…' : 'Aller à l\'action du jour 🌱' }}
                    </button>
                </div>
            </div>

            <!-- Lien direct si l'utilisateur veut sauter -->
            <p style="text-align: center; margin-top: 1.2rem;">
                <a :href="actionUrl" style="font-size: 0.82rem; color: var(--text-secondary); text-decoration: underline;">
                    Passer directement à l'action du jour →
                </a>
            </p>

        </div>
    </CandidateLayout>
</template>
