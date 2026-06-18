<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    attempt: Object,
    progress: Object,
    gamification: Object,
    narrative: Object,
})

const allQuestions = computed(() =>
    props.attempt.test.sections.flatMap(s => s.questions.map(q => ({ ...q, section_title: s.title })))
)

const startIndex = props.attempt.answers.length
const currentIndex = ref(Math.min(startIndex, Math.max(0, allQuestions.value.length - 1)))
const currentQuestion = computed(() => allQuestions.value[currentIndex.value])
const totalQuestions = computed(() => allQuestions.value.length)
const percent = computed(() => totalQuestions.value > 0 ? (currentIndex.value / totalQuestions.value) * 100 : 0)

const value = ref(null)
const startedAt = ref(Date.now())
const isSubmitting = ref(false)

const submit = () => {
    if (isSubmitting.value) return
    if (value.value === null || value.value === '' || (Array.isArray(value.value) && !value.value.length)) return
    isSubmitting.value = true
    const time = Math.round((Date.now() - startedAt.value) / 1000)
    router.post(route('attempt.answer', props.attempt.id), {
        question_id: currentQuestion.value.id,
        value: value.value,
        time_spent: time,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isSubmitting.value = false
            if (currentIndex.value + 1 >= totalQuestions.value) {
                router.post(route('attempt.complete', props.attempt.id))
            } else {
                currentIndex.value += 1
                value.value = null
                startedAt.value = Date.now()
            }
        },
        onError: () => {
            isSubmitting.value = false
        },
    })
}
</script>

<template>
    <CandidateLayout>
        <Head :title="attempt.test.name" />

        <!-- FE-01 : guard test vide -->
        <template v-if="totalQuestions > 0">
            <div class="max-w-2xl mx-auto">
                <!-- Progress + gamification strip -->
                <div class="pt-card p-5 mb-6">
                    <div class="flex items-center justify-between text-xs text-slate-600 mb-2">
                        <span>{{ Math.round(percent) }}% complété</span>
                        <span class="pt-badge">Niveau {{ gamification.level }} — {{ gamification.level_name }} · {{ gamification.xp }} XP</span>
                    </div>
                    <div class="pt-progress-track">
                        <div class="pt-progress-fill" :style="{ width: percent + '%' }"></div>
                    </div>
                    <p v-if="progress.narrative" class="text-xs text-emerald-700 mt-3">{{ progress.narrative }}</p>
                </div>

                <div class="pt-card p-8">
                    <p class="text-xs uppercase tracking-wide text-slate-400">{{ currentQuestion.section_title }}</p>
                    <h2 class="text-2xl font-semibold mt-2 leading-snug">{{ currentQuestion.prompt }}</h2>
                    <p v-if="currentQuestion.helper" class="text-sm text-slate-500 mt-2">{{ currentQuestion.helper }}</p>

                    <div class="mt-8 space-y-3">
                        <!-- Single choice -->
                        <template v-if="currentQuestion.type === 'single'">
                            <label v-for="opt in currentQuestion.options" :key="opt.value"
                                class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 transition"
                                :class="{ 'border-indigo-500 bg-indigo-50': value === opt.value }">
                                <input type="radio" :value="opt.value" v-model="value" class="text-indigo-600">
                                <span class="text-sm">{{ opt.label }}</span>
                            </label>
                        </template>

                        <!-- Scale -->
                        <template v-else-if="currentQuestion.type === 'scale'">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs text-slate-500">{{ currentQuestion.options?.min_label || 'Pas du tout' }}</span>
                                <button v-for="n in (currentQuestion.options?.max || 5)" :key="n"
                                    type="button" @click="value = n"
                                    class="h-12 w-12 rounded-full border text-sm font-medium transition"
                                    :class="value === n ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-400'">
                                    {{ n }}
                                </button>
                                <span class="text-xs text-slate-500">{{ currentQuestion.options?.max_label || 'Tout à fait' }}</span>
                            </div>
                        </template>

                        <!-- Multi -->
                        <template v-else-if="currentQuestion.type === 'multi'">
                            <label v-for="opt in currentQuestion.options" :key="opt.value"
                                class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400">
                                <input type="checkbox" :value="opt.value" v-model="value" class="text-indigo-600">
                                <span class="text-sm">{{ opt.label }}</span>
                            </label>
                        </template>

                        <!-- Texte libre -->
                        <template v-else-if="currentQuestion.type === 'text'">
                            <textarea v-model="value" rows="4" class="pt-input" placeholder="Ta réponse…"></textarea>
                        </template>
                    </div>

                    <!-- FE-02 : bouton désactivé pendant l'envoi -->
                    <div class="mt-8 flex justify-between items-center">
                        <span class="text-xs text-slate-500">Question {{ currentIndex + 1 }} sur {{ totalQuestions }}</span>
                        <button @click="submit"
                            :disabled="isSubmitting"
                            class="pt-btn-primary"
                            :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }">
                            {{ isSubmitting ? '…' : (currentIndex + 1 === totalQuestions ? 'Terminer' : 'Suivant →') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- FE-01 : état vide si 0 questions -->
        <div v-else class="max-w-2xl mx-auto">
            <div class="pt-card p-10 text-center text-slate-500">
                <p class="text-lg font-medium mb-2">Ce test ne contient aucune question.</p>
                <p class="text-sm">Contactez l'administrateur pour vérifier la configuration du test.</p>
            </div>
        </div>

    </CandidateLayout>
</template>
