<script setup>
import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'praxiquest_welcome_seen'

const visible = ref(false)
const step = ref(0)

const steps = [
    {
        roman: '·',
        icon: '<circle cx="19" cy="19" r="17.5" stroke="#A67520" stroke-width="1"/><circle cx="19" cy="19" r="13" stroke="#A67520" stroke-width="0.5" opacity="0.5"/><polygon points="19,6 20.4,18 19,21 17.6,18" fill="#A67520"/><polygon points="19,32 20.4,20 19,17 17.6,20" fill="#A67520" opacity="0.35"/><circle cx="19" cy="19" r="2" fill="#A67520"/><circle cx="19" cy="19" r="1" fill="#F0E8D4"/>',
        iconViewBox: '0 0 38 38',
        label: 'Voyage intérieur',
        title: 'Bienvenue dans PraxiQuest',
        text: 'Une expédition au cœur de toi-même. L\'IA cartographie ton monde intérieur et révèle les horizons qui t\'attendent.',
    },
    {
        roman: 'I',
        icon: '<path d="M9 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>',
        iconViewBox: '0 0 24 24',
        label: 'L\'ancrage',
        title: 'Forge ton Identité',
        text: 'Tu poses le contexte. Ton statut, ton parcours, ton CV. L\'IA t\'écoute avant de te parler.',
    },
    {
        roman: 'II',
        icon: '<path d="M12 3.5V21"/><path d="M12 6h7l-2 2 2 2h-7"/><path d="M12 12H5l-2 2 2 2h7"/>',
        iconViewBox: '0 0 24 24',
        label: 'L\'exploration',
        title: 'Passe les Épreuves',
        text: 'Des questions qui font réfléchir. Des révélations inattendues. Une quête intérieure engageante, à ton rythme.',
    },
    {
        roman: 'III',
        icon: '<path d="M12 2L14.5 9H22l-6 4.5 2.3 7L12 16.5 5.7 20.5 8 13.5 2 9h7.5z" fill="#A67520" stroke="#A67520"/>',
        iconViewBox: '0 0 24 24',
        label: 'La révélation',
        title: 'Lis ton Grimoire',
        text: 'L\'IA cartographie ce que tu as traversé. Une synthèse profonde, personnalisée, honnête — et des horizons que tu n\'aurais peut-être jamais imaginés.',
    },
    {
        roman: 'IV',
        icon: '<path d="M4 8l5 5 6-4 5 4"/><circle cx="4" cy="8" r="1.1" fill="#A67520" stroke="none"/><circle cx="9" cy="13" r="1.1" fill="#A67520" stroke="none"/><circle cx="15" cy="9" r="1.1" fill="#A67520" stroke="none"/><circle cx="20" cy="13" r="1.1" fill="#A67520" stroke="none"/>',
        iconViewBox: '0 0 24 24',
        label: 'L\'horizon',
        title: 'Explore la Salle du Trésor',
        text: 'En avançant, tu débloque des mini-apps de développement personnel. Pas des réponses — des chemins.',
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
                style="background:rgba(42,30,8,0.6); backdrop-filter:blur(6px);"
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
                        class="relative w-full max-w-md overflow-hidden"
                        style="background:#F0E8D4; border:1px solid rgba(166,117,32,0.35); border-radius:4px; box-shadow:0 32px 80px rgba(42,30,8,0.4);"
                    >
                        <!-- Fond grille (comme la landing) -->
                        <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.05;pointer-events:none" viewBox="0 0 400 360" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                            <defs>
                                <pattern id="wgridLg" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="#A67520" stroke-width=".6"/></pattern>
                                <pattern id="wgridSm" width="8" height="8" patternUnits="userSpaceOnUse"><path d="M 8 0 L 0 0 0 8" fill="none" stroke="#A67520" stroke-width=".2"/></pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#wgridSm)"/>
                            <rect width="100%" height="100%" fill="url(#wgridLg)"/>
                            <circle cx="200" cy="180" r="140" fill="none" stroke="#A67520" stroke-width="1"/>
                            <circle cx="200" cy="180" r="90" fill="none" stroke="#A67520" stroke-width=".6"/>
                        </svg>

                        <!-- Bouton fermer -->
                        <button
                            @click="close"
                            class="absolute top-4 right-4 z-10 flex items-center justify-center transition-opacity hover:opacity-60"
                            style="width:28px;height:28px;background:rgba(166,117,32,0.1);border:1px solid rgba(166,117,32,0.25);border-radius:2px;color:#5B4A30;"
                            aria-label="Fermer"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>

                        <!-- Contenu -->
                        <div class="relative" style="padding:2.5rem 2.5rem 2rem; z-index:1;">

                            <!-- Badge label -->
                            <div
                                style="display:inline-flex;align-items:center;gap:7px;font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.18em;color:#A67520;text-transform:uppercase;margin-bottom:1.8rem;padding:4px 12px 4px 9px;border:1px solid rgba(166,117,32,0.3);border-radius:2px;background:rgba(166,117,32,0.05);"
                            >
                                <div style="width:5px;height:5px;background:#A67520;transform:rotate(45deg);flex-shrink:0;"></div>
                                <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" mode="out-in">
                                    <span :key="step">{{ steps[step].label }}</span>
                                </Transition>
                            </div>

                            <!-- Icône + chiffre romain -->
                            <div class="flex items-center gap-4 mb-5">
                                <div style="width:56px;height:56px;flex-shrink:0;background:#E5DAC2;border:1px solid rgba(166,117,32,0.35);border-radius:2px;display:flex;align-items:center;justify-content:center;">
                                    <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" mode="out-in">
                                        <svg :key="step" :viewBox="steps[step].iconViewBox" fill="none" stroke="#A67520" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="width:28px;height:28px;" v-html="steps[step].icon" aria-hidden="true"/>
                                    </Transition>
                                </div>
                                <div>
                                    <div style="font-family:'Space Mono',monospace;font-size:10px;color:#A67520;letter-spacing:.14em;text-transform:uppercase;margin-bottom:2px;">
                                        <span v-if="steps[step].roman !== '·'">Acte {{ steps[step].roman }}</span>
                                        <span v-else>Voyage intérieur</span>
                                    </div>
                                    <Transition enter-active-class="transition-all duration-250" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" mode="out-in">
                                        <h2
                                            :key="step"
                                            id="welcome-title"
                                            style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:#2A1E08;line-height:1.2;letter-spacing:-0.02em;"
                                        >
                                            {{ steps[step].title }}
                                        </h2>
                                    </Transition>
                                </div>
                            </div>

                            <!-- Texte -->
                            <Transition enter-active-class="transition-all duration-250" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" mode="out-in">
                                <p
                                    :key="step"
                                    style="font-family:'Inter',sans-serif;font-size:13.5px;color:#6B5A3E;line-height:1.8;margin-bottom:1.8rem;min-height:56px;"
                                >
                                    {{ steps[step].text }}
                                </p>
                            </Transition>

                            <!-- Séparateur -->
                            <div style="height:1px;background:rgba(166,117,32,0.2);margin-bottom:1.4rem;"></div>

                            <!-- Dots -->
                            <div style="display:flex;justify-content:center;gap:8px;margin-bottom:1.4rem;">
                                <button
                                    v-for="(s, i) in steps"
                                    :key="i"
                                    @click="step = i"
                                    :aria-label="`Étape ${i + 1}`"
                                    class="transition-all duration-200"
                                    style="border-radius:0;"
                                    :style="{
                                        width: i === step ? '24px' : '8px',
                                        height: '4px',
                                        background: i === step ? '#A67520' : 'rgba(166,117,32,0.25)',
                                        border: 'none',
                                        cursor: 'pointer',
                                    }"
                                />
                            </div>

                            <!-- Boutons -->
                            <div style="display:flex;gap:10px;">
                                <button
                                    v-if="step > 0"
                                    @click="prev"
                                    class="flex-1 transition-opacity hover:opacity-70"
                                    style="padding:11px;border:1px solid rgba(166,117,32,0.3);background:transparent;color:#5B4A30;font-family:'Space Grotesk',sans-serif;font-size:12px;font-weight:600;letter-spacing:.02em;cursor:pointer;border-radius:2px;"
                                >
                                    ← Précédent
                                </button>
                                <button
                                    @click="next"
                                    class="flex-1 transition-opacity hover:opacity-85"
                                    style="padding:11px;border:none;background:#1C1408;color:#F0E8D4;font-family:'Space Grotesk',sans-serif;font-size:12px;font-weight:600;letter-spacing:.02em;cursor:pointer;border-radius:2px;box-shadow:0 4px 16px rgba(42,30,8,0.25);"
                                >
                                    {{ step < steps.length - 1 ? 'Continuer l\'expédition →' : 'Commencer l\'expédition' }}
                                </button>
                            </div>

                            <!-- Skip -->
                            <div v-if="step < steps.length - 1" style="text-align:center;margin-top:14px;">
                                <button
                                    @click="close"
                                    class="transition-opacity hover:opacity-60"
                                    style="background:none;border:none;font-size:11px;font-family:'Space Mono',monospace;color:#8C7A5E;letter-spacing:.08em;cursor:pointer;text-transform:uppercase;"
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
