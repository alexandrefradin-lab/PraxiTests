<script setup>
import { ref, computed, onMounted } from 'vue'
import { useParcours } from '@/composables/useParcours'

// v2 : ajout de l'écran de choix du parcours (Médiéval / Corporate) en ouverture.
// Clé bumpée pour que les comptes existants voient le choix une fois.
const STORAGE_KEY = 'praxiquest_welcome_seen_v2'

const { theme, isCorporate, setParcours } = useParcours()

const visible = ref(false)
const choosing = ref(true)
const step = ref(0)

// ─── Tour : contenus par parcours ───────────────────────────────────────────
const MEDIEVAL_STEPS = [
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

const CORPORATE_STEPS = [
    {
        roman: '·',
        icon: '<circle cx="19" cy="19" r="17.5" stroke="#A8853B" stroke-width="1"/><circle cx="19" cy="19" r="13" stroke="#A8853B" stroke-width="0.5" opacity="0.5"/><polygon points="19,6 20.4,18 19,21 17.6,18" fill="#A8853B"/><polygon points="19,32 20.4,20 19,17 17.6,20" fill="#A8853B" opacity="0.35"/><circle cx="19" cy="19" r="2" fill="#A8853B"/>',
        iconViewBox: '0 0 38 38',
        label: 'Programme',
        title: 'Bienvenue sur PraxiQuest',
        text: 'Un programme d\'évaluation professionnelle rigoureux. L\'IA analyse vos résultats et construit des recommandations de carrière sur mesure.',
    },
    {
        roman: 'I',
        icon: '<path d="M9 5H7a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/>',
        iconViewBox: '0 0 24 24',
        label: 'Le profil',
        title: 'Complétez votre profil',
        text: 'Votre statut, votre parcours, votre CV. Ces éléments contextualisent l\'analyse et affinent chaque recommandation.',
    },
    {
        roman: 'II',
        icon: '<path d="M12 3.5V21"/><path d="M12 6h7l-2 2 2 2h-7"/><path d="M12 12H5l-2 2 2 2h7"/>',
        iconViewBox: '0 0 24 24',
        label: 'Les évaluations',
        title: 'Passez les évaluations',
        text: 'Des instruments psychométriques reconnus — RIASEC, Big Five, EQ-i… — à compléter à votre rythme.',
    },
    {
        roman: 'III',
        icon: '<path d="M12 2L14.5 9H22l-6 4.5 2.3 7L12 16.5 5.7 20.5 8 13.5 2 9h7.5z" fill="#A8853B" stroke="#A8853B"/>',
        iconViewBox: '0 0 24 24',
        label: 'La synthèse',
        title: 'Consultez votre dossier de synthèse',
        text: 'L\'IA croise l\'ensemble de vos résultats : une synthèse personnalisée, honnête, et des pistes de carrière argumentées.',
    },
    {
        roman: 'IV',
        icon: '<path d="M4 8l5 5 6-4 5 4"/><circle cx="4" cy="8" r="1.1" fill="#A8853B" stroke="none"/><circle cx="9" cy="13" r="1.1" fill="#A8853B" stroke="none"/><circle cx="15" cy="9" r="1.1" fill="#A8853B" stroke="none"/><circle cx="20" cy="13" r="1.1" fill="#A8853B" stroke="none"/>',
        iconViewBox: '0 0 24 24',
        label: 'Les ressources',
        title: 'Explorez les ressources',
        text: 'Vos points débloquent des modules de développement professionnel complémentaires, accessibles définitivement.',
    },
]

const steps = computed(() => (isCorporate.value ? CORPORATE_STEPS : MEDIEVAL_STEPS))
const ctaNext = computed(() => (isCorporate.value ? 'Continuer →' : 'Continuer l\'expédition →'))
const ctaLast = computed(() => (isCorporate.value ? 'Commencer' : 'Commencer l\'expédition'))

onMounted(() => {
    if (!localStorage.getItem(STORAGE_KEY)) {
        visible.value = true
        // Parcours déjà choisi sur le formulaire d'inscription (même navigateur) :
        // on saute l'écran de choix et on entre directement dans le tour.
        if (localStorage.getItem('praxiquest_parcours_chosen')) {
            choosing.value = false
        }
    }
})

function close() {
    localStorage.setItem(STORAGE_KEY, '1')
    visible.value = false
}

// Choix du parcours : bascule immédiate (aperçu live — la modale suit les tokens).
function choose(t) {
    if (t !== theme.value) setParcours(t)
}

function confirmChoice() {
    choosing.value = false
}

function next() {
    if (step.value < steps.value.length - 1) {
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
                style="background:rgba(20,17,10,0.58); backdrop-filter:blur(6px);"
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
                        style="background:var(--bg-base); border:1px solid var(--border-strong); border-radius:var(--r-sm); box-shadow:0 32px 80px rgba(20,17,10,0.4);"
                    >
                        <!-- Fond grille (comme la landing) -->
                        <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.05;pointer-events:none" viewBox="0 0 400 360" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                            <defs>
                                <pattern id="wgridLg" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="var(--color-primary)" stroke-width=".6"/></pattern>
                                <pattern id="wgridSm" width="8" height="8" patternUnits="userSpaceOnUse"><path d="M 8 0 L 0 0 0 8" fill="none" stroke="var(--color-primary)" stroke-width=".2"/></pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#wgridSm)"/>
                            <rect width="100%" height="100%" fill="url(#wgridLg)"/>
                            <circle cx="200" cy="180" r="140" fill="none" stroke="var(--color-primary)" stroke-width="1"/>
                            <circle cx="200" cy="180" r="90" fill="none" stroke="var(--color-primary)" stroke-width=".6"/>
                        </svg>

                        <!-- Bouton fermer -->
                        <button
                            @click="close"
                            class="absolute top-4 right-4 z-10 flex items-center justify-center transition-opacity hover:opacity-60"
                            style="width:28px;height:28px;background:var(--pt-gold-pale);border:1px solid var(--border-mid);border-radius:2px;color:var(--text-secondary);"
                            aria-label="Fermer"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>

                        <!-- ══ Écran 0 : choix du parcours ══ -->
                        <div v-if="choosing" class="relative" style="padding:2.5rem 2.5rem 2rem; z-index:1;">
                            <div
                                style="display:inline-flex;align-items:center;gap:7px;font-family:var(--font-data);font-size:9px;letter-spacing:.18em;color:var(--color-primary);text-transform:uppercase;margin-bottom:1.6rem;padding:4px 12px 4px 9px;border:1px solid var(--border-mid);border-radius:2px;background:var(--pt-gold-pale);"
                            >
                                <div style="width:5px;height:5px;background:var(--color-primary);transform:rotate(45deg);flex-shrink:0;"></div>
                                <span>Bienvenue</span>
                            </div>

                            <h2 id="welcome-title" style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--text-primary);line-height:1.2;letter-spacing:-0.02em;">
                                Choisissez votre parcours
                            </h2>
                            <p style="font-family:var(--font-body);font-size:12.5px;color:var(--text-secondary);line-height:1.7;margin-top:6px;margin-bottom:1.4rem;">
                                Même contenu, deux ambiances. Modifiable à tout moment depuis votre menu.
                            </p>

                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <!-- Vignette Médiéval -->
                                <button
                                    type="button"
                                    @click="choose('medieval')"
                                    style="text-align:left;padding:0;cursor:pointer;border-radius:10px;overflow:hidden;background:none;"
                                    :style="{ border: !isCorporate ? '2px solid var(--color-primary)' : '1px solid var(--border-mid)' }"
                                    :aria-pressed="!isCorporate"
                                >
                                    <span style="display:block;background:#F0E8D4;padding:12px 10px 10px;">
                                        <svg width="20" height="20" viewBox="0 0 30 30" aria-hidden="true"><polygon points="15,2 26,8.5 26,21.5 15,28 4,21.5 4,8.5" fill="#1C1408" stroke="#A67520" stroke-width="1"/></svg>
                                        <span style="display:block;height:5px;width:70%;background:#A67520;border-radius:99px;margin-top:8px;"></span>
                                        <span style="display:block;height:4px;width:50%;background:rgba(166,117,32,0.35);border-radius:99px;margin-top:5px;"></span>
                                        <span style="display:flex;gap:4px;margin-top:9px;"><span style="flex:1;height:20px;background:#E5DAC2;border:1px solid rgba(166,117,32,0.3);border-radius:5px;"></span><span style="flex:1;height:20px;background:#E5DAC2;border:1px solid rgba(166,117,32,0.3);border-radius:5px;"></span></span>
                                    </span>
                                    <span style="display:block;padding:9px 10px;background:#FFFDF8;border-top:1px solid rgba(166,117,32,0.2);">
                                        <span style="display:flex;align-items:center;justify-content:space-between;">
                                            <span style="font-family:'Space Grotesk',sans-serif;font-size:12.5px;font-weight:700;color:#2A1E08;">Médiéval</span>
                                            <i v-if="!isCorporate" class="ti ti-circle-check" style="font-size:15px;color:#A67520;" aria-hidden="true"></i>
                                        </span>
                                        <span style="display:block;font-family:'Inter',sans-serif;font-size:10px;color:#6B5F4C;margin-top:2px;line-height:1.5;">L'aventure intérieure — quêtes, grimoire, éclats</span>
                                    </span>
                                </button>

                                <!-- Vignette Corporate -->
                                <button
                                    type="button"
                                    @click="choose('corporate')"
                                    style="text-align:left;padding:0;cursor:pointer;border-radius:10px;overflow:hidden;background:none;"
                                    :style="{ border: isCorporate ? '2px solid var(--color-primary)' : '1px solid var(--border-mid)' }"
                                    :aria-pressed="isCorporate"
                                >
                                    <span style="display:block;background:#F8F7F4;padding:12px 10px 10px;">
                                        <svg width="20" height="20" viewBox="0 0 30 30" aria-hidden="true"><polygon points="15,2 26,8.5 26,21.5 15,28 4,21.5 4,8.5" fill="#16140F" stroke="#A8853B" stroke-width="1"/></svg>
                                        <span style="display:block;height:5px;width:70%;background:#16140F;border-radius:99px;margin-top:8px;"></span>
                                        <span style="display:block;height:4px;width:50%;background:#A8853B;border-radius:99px;margin-top:5px;"></span>
                                        <span style="display:flex;gap:4px;margin-top:9px;"><span style="flex:1;height:20px;background:#FFFFFF;border:1px solid rgba(25,23,19,0.10);border-radius:5px;"></span><span style="flex:1;height:20px;background:#FFFFFF;border:1px solid rgba(25,23,19,0.10);border-radius:5px;"></span></span>
                                    </span>
                                    <span style="display:block;padding:9px 10px;background:#FFFFFF;border-top:1px solid rgba(25,23,19,0.10);">
                                        <span style="display:flex;align-items:center;justify-content:space-between;">
                                            <span style="font-family:'Inter',sans-serif;font-size:12.5px;font-weight:700;letter-spacing:-0.02em;color:#191713;">Corporate</span>
                                            <i v-if="isCorporate" class="ti ti-circle-check" style="font-size:15px;color:#A8853B;" aria-hidden="true"></i>
                                        </span>
                                        <span style="display:block;font-family:'Inter',sans-serif;font-size:10px;color:#5F5A4F;margin-top:2px;line-height:1.5;">Le programme executive — évaluations, synthèse, points</span>
                                    </span>
                                </button>
                            </div>

                            <div style="display:flex;margin-top:1.4rem;">
                                <button
                                    @click="confirmChoice"
                                    class="flex-1 transition-opacity hover:opacity-85"
                                    style="padding:11px;border:none;background:var(--color-accent);color:var(--bg-base);font-family:var(--font-display);font-size:12px;font-weight:600;letter-spacing:.02em;cursor:pointer;border-radius:var(--r-sm);box-shadow:0 4px 16px rgba(20,17,10,0.25);"
                                >
                                    Continuer avec {{ isCorporate ? 'Corporate' : 'Médiéval' }}
                                </button>
                            </div>
                        </div>

                        <!-- ══ Tour (5 étapes) ══ -->
                        <div v-else class="relative" style="padding:2.5rem 2.5rem 2rem; z-index:1;">

                            <!-- Badge label -->
                            <div
                                style="display:inline-flex;align-items:center;gap:7px;font-family:var(--font-data);font-size:9px;letter-spacing:.18em;color:var(--color-primary);text-transform:uppercase;margin-bottom:1.8rem;padding:4px 12px 4px 9px;border:1px solid var(--border-mid);border-radius:2px;background:var(--pt-gold-pale);"
                            >
                                <div style="width:5px;height:5px;background:var(--color-primary);transform:rotate(45deg);flex-shrink:0;"></div>
                                <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" mode="out-in">
                                    <span :key="step">{{ steps[step].label }}</span>
                                </Transition>
                            </div>

                            <!-- Icône + chiffre romain -->
                            <div class="flex items-center gap-4 mb-5">
                                <div style="width:56px;height:56px;flex-shrink:0;background:var(--bg-surface);border:1px solid var(--border-strong);border-radius:2px;display:flex;align-items:center;justify-content:center;">
                                    <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" mode="out-in">
                                        <svg :key="step" :viewBox="steps[step].iconViewBox" fill="none" stroke="var(--color-primary)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="width:28px;height:28px;" v-html="steps[step].icon" aria-hidden="true"/>
                                    </Transition>
                                </div>
                                <div>
                                    <div style="font-family:var(--font-data);font-size:10px;color:var(--color-primary);letter-spacing:.14em;text-transform:uppercase;margin-bottom:2px;">
                                        <span v-if="steps[step].roman !== '·'">{{ isCorporate ? 'Étape' : 'Acte' }} {{ steps[step].roman }}</span>
                                        <span v-else>{{ isCorporate ? 'Programme' : 'Voyage intérieur' }}</span>
                                    </div>
                                    <Transition enter-active-class="transition-all duration-250" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" mode="out-in">
                                        <h2
                                            :key="step"
                                            id="welcome-title"
                                            style="font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--text-primary);line-height:1.2;letter-spacing:-0.02em;"
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
                                    style="font-family:var(--font-body);font-size:13.5px;color:var(--text-secondary);line-height:1.8;margin-bottom:1.8rem;min-height:56px;"
                                >
                                    {{ steps[step].text }}
                                </p>
                            </Transition>

                            <!-- Séparateur -->
                            <div style="height:1px;background:var(--border-light);margin-bottom:1.4rem;"></div>

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
                                        background: i === step ? 'var(--color-primary)' : 'var(--border-mid)',
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
                                    style="padding:11px;border:1px solid var(--border-mid);background:transparent;color:var(--text-secondary);font-family:var(--font-display);font-size:12px;font-weight:600;letter-spacing:.02em;cursor:pointer;border-radius:2px;"
                                >
                                    ← Précédent
                                </button>
                                <button
                                    @click="next"
                                    class="flex-1 transition-opacity hover:opacity-85"
                                    style="padding:11px;border:none;background:var(--color-accent);color:var(--bg-base);font-family:var(--font-display);font-size:12px;font-weight:600;letter-spacing:.02em;cursor:pointer;border-radius:2px;box-shadow:0 4px 16px rgba(20,17,10,0.25);"
                                >
                                    {{ step < steps.length - 1 ? ctaNext : ctaLast }}
                                </button>
                            </div>

                            <!-- Skip -->
                            <div v-if="step < steps.length - 1" style="text-align:center;margin-top:14px;">
                                <button
                                    @click="close"
                                    class="transition-opacity hover:opacity-60"
                                    style="background:none;border:none;font-size:11px;font-family:var(--font-data);color:var(--text-muted);letter-spacing:.08em;cursor:pointer;text-transform:uppercase;"
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
