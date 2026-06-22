<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    practices:  { type: Array,  default: () => [] },
    currentDay: { type: Number, default: 1 },
    totalDays:  { type: Number, default: 60 },
    completed:  { type: Number, default: 0 },
    streak:     { type: Number, default: 0 },
})

const iconFor = (name) => ({
    compass: '🧭', target: '🎯', ear: '👂', message: '💬', handshake: '🤝',
    gift: '🎁', flame: '🔥', shield: '🛡️', scale: '⚖️', users: '👥',
    clock: '⏳', book: '📖', heart: '❤️', rocket: '🚀', eye: '👁️',
    seedling: '🌱', anchor: '⚓', map: '🗺️', lightbulb: '💡', sun: '☀️',
}[name] ?? '⭐')

// Pratique du jour (débloquée la plus récente non faite, sinon le jour courant).
const todayPractice = computed(() =>
    props.practices.find(p => p.is_today) ?? null
)

const donePercent = computed(() =>
    Math.round((props.completed / props.totalDays) * 100)
)

// Regroupement par thème pour la timeline.
const blocks = computed(() => {
    const out = []
    for (const p of props.practices) {
        let b = out.find(x => x.theme === p.theme)
        if (!b) { b = { theme: p.theme, items: [] }; out.push(b) }
        b.items.push(p)
    }
    return out
})
</script>

<template>
    <CandidateLayout>
        <Head title="Le Cap — 60 jours de management" />

        <div class="max-w-3xl mx-auto">

            <!-- En-tête -->
            <div class="mb-6">
                <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-secondary);">
                    Parcours · Manager de proximité
                </p>
                <h1 style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.1;">
                    Le Cap
                </h1>
                <p class="mt-2" style="font-family: var(--font-body); font-size: 0.95rem; color: var(--text-secondary);">
                    Une bonne pratique de management par jour, pendant 60 jours.
                    Chaque pratique se débloque un jour après la précédente — et te rapporte des Éclats.
                </p>
            </div>

            <!-- Tableau de bord -->
            <div class="mb-6 grid grid-cols-3 gap-3">
                <div style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 12px); padding: 0.9rem 1rem; text-align: center;">
                    <div style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--text-primary);">{{ currentDay }}<span style="font-size: 0.9rem; color: var(--text-secondary);">/{{ totalDays }}</span></div>
                    <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-secondary);">Jour du parcours</div>
                </div>
                <div style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 12px); padding: 0.9rem 1rem; text-align: center;">
                    <div style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--text-primary);">{{ completed }}</div>
                    <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-secondary);">Pratiques faites</div>
                </div>
                <div style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 12px); padding: 0.9rem 1rem; text-align: center;">
                    <div style="font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; color: var(--text-primary);">🔥 {{ streak }}</div>
                    <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-secondary);">Jours d'affilée</div>
                </div>
            </div>

            <!-- Barre de progression globale -->
            <div class="mb-8" style="height: 8px; background: var(--border, #e5e7eb); border-radius: 999px; overflow: hidden;">
                <div :style="{ width: donePercent + '%', height: '100%', background: 'var(--primary, #A67520)', transition: 'width .4s' }"></div>
            </div>

            <!-- Carte « pratique du jour » -->
            <Link
                v-if="todayPractice && todayPractice.unlocked"
                :href="route('praxilead.show', todayPractice.day)"
                class="block mb-10"
                style="text-decoration: none;"
            >
                <div style="background: linear-gradient(135deg, var(--primary, #A67520), #8a5f17); border-radius: var(--r-md, 14px); padding: 1.5rem 1.6rem; color: #fff; box-shadow: 0 8px 24px rgba(166,117,32,.25);">
                    <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: .85;">
                        Aujourd'hui · Jour {{ todayPractice.day }} · {{ todayPractice.theme }}
                    </p>
                    <h2 class="mt-1" style="font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; line-height: 1.15;">
                        {{ iconFor(todayPractice.icon) }} {{ todayPractice.title }}
                    </h2>
                    <p class="mt-2" style="font-size: 0.95rem; opacity: .92;">{{ todayPractice.summary }}</p>
                    <span class="mt-4" style="display: inline-block; background: rgba(255,255,255,.18); padding: 0.5rem 1rem; border-radius: 999px; font-weight: 600; font-size: 0.9rem;">
                        {{ todayPractice.completed ? '✓ Pratique du jour — revoir' : "Découvrir la pratique du jour →" }}
                    </span>
                </div>
            </Link>

            <!-- Timeline par bloc thématique -->
            <div v-for="block in blocks" :key="block.theme" class="mb-8">
                <h3 style="font-family: var(--font-display); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-secondary); margin-bottom: 0.75rem;">
                    {{ block.theme }}
                </h3>

                <div class="space-y-2">
                    <component
                        :is="p.unlocked ? 'Link' : 'div'"
                        v-for="p in block.items"
                        :key="p.day"
                        :href="p.unlocked ? route('praxilead.show', p.day) : undefined"
                        class="block"
                        style="text-decoration: none;"
                    >
                        <div
                            :style="{
                                display: 'flex', gap: '0.9rem', alignItems: 'center',
                                padding: '0.8rem 1rem',
                                background: p.is_today ? 'rgba(166,117,32,.06)' : 'var(--surface, #fff)',
                                border: p.is_today ? '1px solid var(--primary, #A67520)' : '1px solid var(--border, #e5e7eb)',
                                borderRadius: 'var(--r-md, 12px)',
                                opacity: p.unlocked ? 1 : 0.55,
                                cursor: p.unlocked ? 'pointer' : 'default',
                            }"
                        >
                            <!-- Pastille jour -->
                            <div :style="{
                                flexShrink: 0, width: '38px', height: '38px', borderRadius: '50%',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                fontFamily: 'var(--font-display)', fontWeight: 700, fontSize: '0.85rem',
                                background: p.completed ? '#10B981' : (p.unlocked ? 'var(--primary, #A67520)' : 'var(--border, #e5e7eb)'),
                                color: p.completed || p.unlocked ? '#fff' : 'var(--text-secondary)',
                            }">
                                <span v-if="p.completed">✓</span>
                                <span v-else-if="!p.unlocked">🔒</span>
                                <span v-else>{{ p.day }}</span>
                            </div>

                            <div style="flex: 1; min-width: 0;">
                                <div class="flex items-center gap-2">
                                    <span style="font-size: 1.05rem;">{{ iconFor(p.icon) }}</span>
                                    <h4 style="font-family: var(--font-display); font-weight: 700; font-size: 0.98rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ p.title }}
                                    </h4>
                                </div>
                                <p v-if="p.unlocked" class="mt-0.5" style="font-size: 0.82rem; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ p.summary }}
                                </p>
                                <p v-else class="mt-0.5" style="font-size: 0.8rem; font-weight: 600; color: var(--primary, #A67520);">
                                    🔒 Se débloque dans {{ p.days_left }} jour{{ p.days_left > 1 ? 's' : '' }}
                                </p>
                            </div>

                            <span v-if="p.is_today" style="flex-shrink: 0; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; color: var(--primary, #A67520);">Aujourd'hui</span>
                        </div>
                    </component>
                </div>
            </div>

        </div>
    </CandidateLayout>
</template>
