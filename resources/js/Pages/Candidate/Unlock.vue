<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    products:      { type: Array, default: () => [] },
    freeTestSlugs: { type: Array, default: () => [] },
})

const page = usePage()
const errors = computed(() => page.props.errors ?? {})

const selected = ref(props.products.find(p => p.available && p.highlighted)?.key
    ?? props.products.find(p => p.available)?.key
    ?? null)
const cgvAccepted = ref(false)
const submitting = ref(false)

const euros = (cents) => (cents / 100).toLocaleString('fr-FR', { minimumFractionDigits: 0 })

function choose(p) {
    if (p.available) selected.value = p.key
}

function pay() {
    if (!selected.value || submitting.value) return
    submitting.value = true
    router.post(route('b2c.checkout'), {
        product: selected.value,
        cgv: cgvAccepted.value,
    }, {
        onFinish: () => { submitting.value = false },
    })
}
</script>

<template>
    <CandidateLayout>
        <Head title="Débloquer mon parcours" />

        <div class="max-w-3xl mx-auto">

            <!-- En-tête -->
            <div class="text-center mb-10">
                <p class="uppercase mb-3" style="font-family:'Space Mono',monospace; font-size:10px; letter-spacing:.18em; color:var(--color-primary);">
                    La suite de ton voyage
                </p>
                <h1 class="font-bold tracking-tight" style="font-family:var(--font-display); color:var(--text-primary); font-size:2.2rem; line-height:1.15;">
                    Débloque ton Rapport complet
                </h1>
                <p class="mt-3 text-sm max-w-md mx-auto" style="color:var(--text-secondary); font-family:var(--font-body); line-height:1.7;">
                    Tu as goûté à l'épreuve de découverte. Le reste du parcours — toutes les épreuves,
                    la relecture globale par IA et ton rapport PDF — se débloque en une fois, sans abonnement.
                </p>
            </div>

            <!-- Offres -->
            <div class="grid md:grid-cols-2 gap-4 mb-8" :class="{ 'md:grid-cols-1 max-w-md mx-auto': products.length === 1 }">
                <div
                    v-for="p in products"
                    :key="p.key"
                    class="pt-card ac-card-ornate p-6 flex flex-col relative"
                    :style="{
                        cursor: p.available ? 'pointer' : 'default',
                        opacity: p.available ? 1 : 0.55,
                        borderColor: selected === p.key ? 'var(--color-primary)' : undefined,
                        boxShadow: selected === p.key ? '0 8px 28px rgba(166,117,32,0.18)' : undefined,
                    }"
                    role="radio"
                    :aria-checked="selected === p.key"
                    :tabindex="p.available ? 0 : null"
                    @click="choose(p)"
                    @keydown.enter="choose(p)"
                    @keydown.space.prevent="choose(p)"
                >
                    <div class="flex items-start justify-between mb-2 gap-3">
                        <h3 class="font-bold" style="font-family:var(--font-display); font-size:17px; color:var(--text-primary);">
                            {{ p.name }}
                        </h3>
                        <span
                            v-if="!p.available"
                            class="inline-block px-2 py-0.5 rounded text-[10px] uppercase tracking-widest"
                            style="font-family:'Space Mono',monospace; color:var(--text-secondary); background:var(--bg-elevated); white-space:nowrap;"
                        >Bientôt</span>
                        <span
                            v-else-if="selected === p.key"
                            class="inline-flex items-center justify-center rounded-full"
                            style="width:22px;height:22px;background:var(--color-primary);color:var(--bg-base);font-size:12px;flex:none;"
                        >✓</span>
                    </div>

                    <div class="mb-3">
                        <span class="font-bold" style="font-family:var(--font-display); font-size:30px; color:var(--color-primary); letter-spacing:-0.02em;">{{ euros(p.price) }} €</span>
                        <span class="text-xs ml-1" style="color:var(--text-secondary); font-family:'Space Mono',monospace;">une fois</span>
                    </div>

                    <p class="text-[13px] mb-4" style="color:var(--text-secondary); font-family:'Inter',sans-serif; line-height:1.65;">
                        {{ p.description }}
                    </p>

                    <ul class="space-y-2 mt-auto">
                        <li
                            v-for="f in p.features"
                            :key="f"
                            class="flex items-start gap-2 text-[13px]"
                            style="color:var(--text-secondary); font-family:'Inter',sans-serif; line-height:1.5;"
                        >
                            <span style="color:var(--color-primary); flex:none;">◆</span>
                            <span>{{ f }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- CGV + paiement -->
            <div class="pt-card p-6 max-w-xl mx-auto">
                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <input
                        v-model="cgvAccepted"
                        type="checkbox"
                        class="mt-1"
                        style="accent-color:var(--color-primary); width:16px; height:16px; flex:none;"
                    />
                    <span class="text-[13px]" style="color:var(--text-secondary); font-family:'Inter',sans-serif; line-height:1.6;">
                        J'accepte les
                        <Link href="/cgv" target="_blank" style="color:var(--color-primary); text-decoration:underline; text-underline-offset:2px;">Conditions Générales de Vente</Link>
                        et je demande l'exécution immédiate du service : je reconnais renoncer à mon droit de
                        rétractation dès le déblocage de mon accès (art. L221-28 du Code de la consommation).
                    </span>
                </label>
                <p v-if="errors.cgv" class="mt-2 text-[12px]" style="color:var(--color-secondary,#7B1515);">{{ errors.cgv }}</p>
                <p v-if="errors.product" class="mt-2 text-[12px]" style="color:var(--color-secondary,#7B1515);">{{ errors.product }}</p>

                <button
                    class="pt-btn-primary w-full mt-5 py-3 text-sm"
                    :disabled="!selected || !cgvAccepted || submitting"
                    :style="{ opacity: (!selected || !cgvAccepted || submitting) ? 0.5 : 1 }"
                    @click="pay"
                >
                    {{ submitting ? 'Redirection vers le paiement…' : 'Débloquer mon parcours' }}
                </button>

                <p class="mt-3 text-center text-[11px]" style="color:var(--text-muted,#8C7A5E); font-family:'Space Mono',monospace; letter-spacing:.06em;">
                    Paiement unique sécurisé par Stripe · Pas d'abonnement · Reçu par email
                </p>
            </div>

            <p class="mt-6 text-center text-[12px]" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                Tu es accompagné·e par un professionnel ? Ton accès passe par son invitation —
                <Link href="/contact" style="color:var(--color-primary); text-decoration:underline; text-underline-offset:2px;">contacte-nous</Link> si quelque chose cloche.
            </p>
        </div>
    </CandidateLayout>
</template>

<style scoped>
.pt-card {
    transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}
.pt-card[role="radio"]:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}
</style>
