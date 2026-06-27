<script setup>
import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'praxiquest_welcome_seen'

const visible = ref(false)
const step = ref(0)

const steps = [
    {
        icon: '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/>',
        title: 'Bienvenue dans PraxiQuest',
        text: 'Une quête d\'exploration intérieure pour mieux te connaître et découvrir les voies qui te correspondent vraiment.',
    },
    {
        icon: '<path d="M9 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>',
        title: '1 — Forge ton Identité',
        text: 'Avant toute chose, complète ton profil : statut, expérience, CV. C\'est la fondation de toutes tes analyses.',
    },
    {
        icon: '<path d="M12 3.5V21"/><path d="M12 6h7l-2 2 2 2h-7"/><path d="M12 12H5l-2 2 2 2h7"/>',
        title: '2 — Passe les Épreuves',
        text: 'Chaque test explore une dimension de toi : personnalité, émotions, valeurs, compétences, style de management… À ton rythme.',
    },
    {
        icon: '<path d="M12 2L14.5 9H22l-6 4.5 2.3 7L12 16.5 5.7 20.5 8 13.5 2 9h7.5z" fill="currentColor" stroke="currentColor"/>',
        title: '3 — Lis ton Grimoire',
        text: 'L\'IA synthétise l\'ensemble de tes résultats en une lecture transversale unique : tes forces, tes zones de croissance, et 15 pistes métiers taillées pour toi.',
    },
    {
        icon: '<path d="M4 8l5 5 6-4 5 4"/><circle cx="4" cy="8" r="1.1" fill="currentColor" stroke="none"/><circle cx="9" cy="13" r="1.1" fill="currentColor" stroke="none"/><circle cx="15" cy="9" r="1.1" fill="currentColor" stroke="none"/><circle cx="20" cy="13" r="1.1" fill="currentColor" stroke="none"/>',
        title: '4 — Explore la Salle du Trésor',
        text: 'En avançant, tu débloque des mini-apps de développement personnel : coaching, leadership, concentration, communication et bien plus.',
    },
]

onMounted(() => {
    if (!localStorage.getItem(STORAGE_KEY)) {
        visible.value = true
    }
})

function close() {
    localStorage.setItem(STORAGE_KEY, '1')
    visible.value = false
}

function next() {
    if (step.value < steps.length - 1) {
        step.value++
    } else {
        close()
    }
}

function prev() {
    if (step.value > 0) step.value--
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="background:rgba(0,0,0,0.65); backdrop-filter:blur(4px);"
                @click.self="close"
                role="dialog"
                aria-modal="true"
                aria-labelledby="welcome-title"
            >
                <Transition
                    enter-active-class="transition-all duration-300"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    appear
                >
                    <div
                        class="relative w-full max-w-md rounded-2xl overflow-hidden"
                        style="background:var(--bg-card); border:1px solid rgba(255,255,255,0.08); box-shadow:0 24px 64px rgba(0,0,0,0.5);"
                    >
                        <!-- Bouton fermer -->
                        <button
                            @click="close"
                            class="absolute top-4 right-4 z-10 w-8 h-8 flex items-center justify-center rounded-full transition-opacity hover:opacity-70"
                            style="background:var(--bg-elevated); color:var(--text-secondary);"
                            aria-label="Fermer"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>

                        <!-- Bandeau or en haut -->
                        <div class="h-1 w-full" style="background:linear-gradient(to right, transparent, var(--color-primary), transparent);"></div>

                        <!-- Contenu des étapes -->
                        <div class="p-8 pt-7">

                            <!-- Icône centrale -->
                            <div
                                class="mx-auto mb-6 flex items-center justify-center rounded-2xl"
                                style="width:72px;height:72px;background:var(--bg-elevated);border:1px solid rgba(255,255,255,0.06);"
                            >
                                <svg
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"
                                    style="width:36px;height:36px;color:var(--color-primary);"
                                    v-html="steps[step].icon"
                                    aria-hidden="true"
                                />
                            </div>

                            <!-- Texte -->
                            <Transition
                                enter-active-class="transition-all duration-250"
                                enter-from-class="opacity-0 translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                                mode="out-in"
                            >
                                <div :key="step" class="text-center">
                                    <h2
                                        id="welcome-title"
                                        class="text-xl font-bold mb-3"
                                        style="font-family:'Space Grotesk',sans-serif;color:var(--text-primary);"
                                    >
                                        {{ steps[step].title }}
                                    </h2>
                                    <p
                                        class="text-sm leading-relaxed"
                                        style="font-family:'Inter',sans-serif;color:var(--text-secondary);"
                                    >
                                        {{ steps[step].text }}
                                    </p>
                                </div>
                            </Transition>

                            <!-- Indicateurs de progression (points) -->
                            <div class="flex justify-center gap-2 mt-6 mb-6">
                                <button
                                    v-for="(s, i) in steps"
                                    :key="i"
                                    @click="step = i"
                                    :aria-label="`Étape ${i + 1}`"
                                    class="transition-all duration-200 rounded-full"
                                    :style="{
                                        width: i === step ? '24px' : '8px',
                                        height: '8px',
                                        background: i === step ? 'var(--color-primary)' : 'var(--bg-elevated)',
                                        border: '1px solid ' + (i === step ? 'var(--color-primary)' : 'rgba(255,255,255,0.12)'),
                                    }"
                                />
                            </div>

                            <!-- Boutons de navigation -->
                            <div class="flex gap-3">
                                <button
                                    v-if="step > 0"
                                    @click="prev"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-opacity hover:opacity-70"
                                    style="background:var(--bg-elevated);color:var(--text-secondary);font-family:'Inter',sans-serif;border:1px solid rgba(255,255,255,0.08);"
                                >
                                    ← Précédent
                                </button>
                                <button
                                    @click="next"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold transition-opacity hover:opacity-80"
                                    style="background:var(--color-primary);color:#000;font-family:'Inter',sans-serif;"
                                >
                                    {{ step < steps.length - 1 ? 'Suivant →' : '🗡 Commencer la quête' }}
                                </button>
                            </div>

                            <!-- Skip discret -->
                            <div v-if="step < steps.length - 1" class="text-center mt-4">
                                <button
                                    @click="close"
                                    class="text-xs transition-opacity hover:opacity-70"
                                    style="color:var(--text-secondary);font-family:'Inter',sans-serif;"
                                >
                                    Passer l'introduction
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
