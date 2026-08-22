<script setup>
import { Head, Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

defineProps({
    paid: { type: Boolean, default: false },
})
</script>

<template>
    <CandidateLayout>
        <Head title="Parcours débloqué" />

        <div class="max-w-xl mx-auto text-center py-10">
            <template v-if="paid">
                <div
                    class="mx-auto mb-6 flex items-center justify-center rounded-full"
                    style="width:72px;height:72px;background:rgba(166,117,32,0.12);border:1px solid var(--color-primary);"
                >
                    <span style="font-size:30px;color:var(--color-primary);">✓</span>
                </div>
                <h1 class="font-bold tracking-tight mb-3" style="font-family:var(--font-display); color:var(--text-primary); font-size:2rem;">
                    Ton parcours est débloqué
                </h1>
                <p class="text-sm mb-8" style="color:var(--text-secondary); font-family:var(--font-body); line-height:1.7;">
                    Toutes les épreuves, la relecture globale et ton rapport PDF t'attendent.
                    Un reçu de paiement t'a été envoyé par email.
                </p>
                <Link :href="route('tests.index')" class="pt-btn-primary px-8 py-3 text-sm inline-block">
                    Reprendre l'aventure
                </Link>
            </template>

            <template v-else>
                <h1 class="font-bold tracking-tight mb-3" style="font-family:var(--font-display); color:var(--text-primary); font-size:1.6rem;">
                    Paiement en cours de confirmation…
                </h1>
                <p class="text-sm mb-8" style="color:var(--text-secondary); font-family:var(--font-body); line-height:1.7;">
                    Ta banque confirme le paiement. Ton accès se débloque automatiquement dans
                    quelques instants — recharge la page ou reviens un peu plus tard. Si rien ne
                    change d'ici quelques minutes,
                    <Link href="/contact" style="color:var(--color-primary); text-decoration:underline; text-underline-offset:2px;">contacte le support</Link>.
                </p>
                <Link :href="route('tests.index')" class="pt-btn-primary px-8 py-3 text-sm inline-block">
                    Retourner aux épreuves
                </Link>
            </template>
        </div>
    </CandidateLayout>
</template>
