<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    plugin: { type: String, required: true },
    tip: { type: Object, default: null },
    engagement: { type: Object, default: () => ({ streak: 0, longest_streak: 0, total_applied: 0, applied_today: false }) },
})

const applying = ref(false)

// Niveau de preuve → pastille + libellé.
const evidenceMeta = computed(() => ({
    solide:     { dot: '🟢', label: 'Preuve solide',     hint: 'Méta-analyses ou essais contrôlés randomisés convergents.' },
    prometteur: { dot: '🟡', label: 'Preuve prometteuse', hint: 'Résultats encourageants, à confirmer.' },
    emergent:   { dot: '🟠', label: 'Piste émergente',    hint: 'Données préliminaires ou théoriques.' },
}[props.tip?.evidence] ?? { dot: '🟡', label: 'Preuve prometteuse', hint: '' }))

const appliedToday = computed(() => props.engagement?.applied_today)
const streak = computed(() => props.engagement?.streak ?? 0)

const apply = () => {
    if (applying.value || appliedToday.value) return
    applying.value = true
    router.post(route('dailytip.apply', props.plugin), {}, {
        preserveScroll: true,
        onFinish: () => { applying.value = false },
    })
}
</script>

<template>
    <div v-if="tip" class="mb-8" style="position: relative; background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 12px); overflow: hidden;">
        <!-- Liseré thématique -->
        <div style="height: 4px; background: linear-gradient(90deg, var(--primary, #4F46E5), #8B5CF6);"></div>

        <div style="padding: 1.25rem 1.4rem 1.4rem;">
            <!-- Bandeau : label + série -->
            <div class="flex items-center justify-between" style="margin-bottom: 0.85rem;">
                <span style="font-family: var(--font-display); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: var(--primary, #4F46E5);">
                    ✦ Tip du jour
                </span>
                <span
                    v-if="streak > 0"
                    :title="`Plus longue série : ${engagement.longest_streak} j`"
                    style="font-size: 0.78rem; font-weight: 700; color: #EA580C; background: #FFF7ED; border: 1px solid #FED7AA; border-radius: 999px; padding: 0.15rem 0.6rem;"
                >
                    🔥 {{ streak }} jour{{ streak > 1 ? 's' : '' }}
                </span>
            </div>

            <!-- Niveau de preuve + thème -->
            <div class="flex items-center gap-2" style="margin-bottom: 0.5rem; flex-wrap: wrap;">
                <span :title="evidenceMeta.hint" style="font-size: 0.72rem; font-weight: 600; color: var(--text-secondary); background: var(--bg, #f9fafb); border: 1px solid var(--border, #e5e7eb); border-radius: 999px; padding: 0.12rem 0.55rem;">
                    {{ evidenceMeta.dot }} {{ evidenceMeta.label }}
                </span>
                <span v-if="tip.theme" style="font-size: 0.72rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em;">
                    {{ tip.theme }}
                </span>
                <span v-if="tip.personalized" title="Recoupe les axes de ton profil" style="font-size: 0.7rem; font-weight: 600; color: #7C3AED; background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 999px; padding: 0.12rem 0.55rem;">
                    ✨ Choisi pour toi
                </span>
            </div>

            <!-- Titre -->
            <h2 style="font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; color: var(--text-primary); line-height: 1.25; margin-bottom: 0.5rem;">
                {{ tip.title }}
            </h2>

            <!-- Insight (le fond) -->
            <p style="font-family: var(--font-body); font-size: 0.95rem; color: var(--text-primary); line-height: 1.6;">
                {{ tip.insight }}
            </p>

            <!-- Micro-action -->
            <div v-if="tip.action" style="margin-top: 0.9rem; padding: 0.8rem 1rem; background: var(--bg, #f9fafb); border-left: 3px solid var(--primary, #4F46E5); border-radius: 8px;">
                <span style="display: block; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.2rem;">
                    Ta micro-action du jour
                </span>
                <p style="font-size: 0.92rem; color: var(--text-primary); line-height: 1.5;">{{ tip.action }}</p>
            </div>

            <!-- Source -->
            <p v-if="tip.source" style="margin-top: 0.75rem; font-size: 0.72rem; font-style: italic; color: var(--text-secondary);">
                Source : {{ tip.source }}
            </p>

            <!-- Action -->
            <div class="flex items-center justify-between gap-3" style="margin-top: 1.1rem; flex-wrap: wrap;">
                <button
                    v-if="!appliedToday"
                    type="button"
                    :disabled="applying"
                    @click="apply"
                    style="font-family: var(--font-display); font-weight: 700; font-size: 0.88rem; color: #fff; background: var(--primary, #4F46E5); border: none; border-radius: 999px; padding: 0.55rem 1.3rem; cursor: pointer; transition: opacity .15s;"
                    :style="{ opacity: applying ? 0.6 : 1 }"
                >
                    {{ applying ? '…' : "Je l'applique aujourd'hui" }}
                </button>
                <span v-else style="font-size: 0.88rem; font-weight: 700; color: #10B981;">
                    ✓ Appliqué aujourd'hui · reviens demain
                </span>

                <span style="font-size: 0.72rem; color: var(--text-secondary);">
                    Tip {{ tip.day_number }}/{{ tip.library_size }}
                </span>
            </div>
        </div>
    </div>
</template>
