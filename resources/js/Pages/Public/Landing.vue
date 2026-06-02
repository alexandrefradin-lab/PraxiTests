<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const branding = computed(() => usePage().props.branding ?? { name: 'PraxiQuest' })

const pillars = [
    {
        title: 'IA experte',
        accent: 'from-indigo-500 to-violet-500',
        text: 'Synthèse personnalisée de ton profil + 15 métiers réalistes proposés à partir de tes réponses et de ton CV.',
    },
    {
        title: 'Gamification subtile',
        accent: 'from-emerald-500 to-teal-500',
        text: 'XP, badges, narration. Tu progresses dans un parcours qui donne envie d\'aller au bout. Sans bruit, sans gadget.',
    },
    {
        title: 'Neuromarketing',
        accent: 'from-amber-500 to-rose-500',
        text: 'Chaque écran, chaque email est optimisé pour transformer une exploration en décision concrète.',
    },
]
</script>

<template>
    <div class="min-h-screen bg-white text-slate-900">
        <Head title="Évaluer. Orienter. Transformer." />

        <!-- Header -->
        <header class="border-b border-slate-100">
            <div class="mx-auto max-w-6xl px-6 py-5 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2 font-semibold tracking-tight">
                    <span class="inline-block h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-500"></span>
                    {{ branding.name }}
                </Link>
                <nav class="flex items-center gap-2 text-sm">
                    <Link :href="route('login')" class="px-4 py-2 text-slate-600 hover:text-slate-900">Connexion</Link>
                    <Link :href="route('register')" class="pt-btn-primary">Commencer</Link>
                </nav>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 via-white to-emerald-50 opacity-70 pointer-events-none"></div>
            <div class="relative mx-auto max-w-4xl px-6 py-24 text-center">
                <span class="pt-badge">Bilan augmenté par IA</span>
                <h1 class="mt-6 text-5xl md:text-6xl font-semibold tracking-tight leading-[1.05]">
                    Le bilan qui ne ressemble à <span class="bg-gradient-to-r from-indigo-600 to-emerald-600 bg-clip-text text-transparent">aucun autre</span>.
                </h1>
                <p class="mt-6 text-lg text-slate-600 max-w-2xl mx-auto">
                    Réponds à un parcours d'évaluation engageant. Reçois une synthèse personnalisée et 15 pistes métier sérieuses, prêtes à explorer.
                </p>
                <div class="mt-10 flex items-center justify-center gap-3">
                    <Link :href="route('register')" class="pt-btn-primary px-6 py-3 text-base">Faire le test gratuit</Link>
                    <a href="#pillars" class="pt-btn-ghost px-6 py-3 text-base">Comment ça marche</a>
                </div>
                <p class="mt-6 text-xs text-slate-400">~ 8 minutes · Aucune carte bancaire · Tes données restent confidentielles</p>
            </div>
        </section>

        <!-- 3 piliers -->
        <section id="pillars" class="mx-auto max-w-6xl px-6 py-24">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight">Trois leviers, une expérience.</h2>
                <p class="mt-3 text-slate-600 max-w-xl mx-auto">Là où les bilans classiques s'arrêtent à un PDF, on construit une expérience qui engage et oriente vraiment.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <article v-for="(p, i) in pillars" :key="i" class="pt-card p-8">
                    <div :class="['h-10 w-10 rounded-lg bg-gradient-to-br', p.accent, 'mb-5']"></div>
                    <h3 class="text-xl font-semibold">{{ p.title }}</h3>
                    <p class="text-sm text-slate-600 mt-3 leading-relaxed">{{ p.text }}</p>
                </article>
            </div>
        </section>

        <!-- Trust strip -->
        <section class="bg-slate-50 border-y border-slate-100">
            <div class="mx-auto max-w-6xl px-6 py-16 grid md:grid-cols-3 gap-10 text-center">
                <div>
                    <p class="text-4xl font-semibold tracking-tight">15</p>
                    <p class="text-sm text-slate-500 mt-2">métiers proposés à chaque profil</p>
                </div>
                <div>
                    <p class="text-4xl font-semibold tracking-tight">8 min</p>
                    <p class="text-sm text-slate-500 mt-2">de parcours engageant</p>
                </div>
                <div>
                    <p class="text-4xl font-semibold tracking-tight">100%</p>
                    <p class="text-sm text-slate-500 mt-2">RGPD · Données chiffrées</p>
                </div>
            </div>
        </section>

        <!-- CTA bottom -->
        <section class="mx-auto max-w-4xl px-6 py-24 text-center">
            <h2 class="text-3xl md:text-4xl font-semibold tracking-tight">Prêt·e à voir ton profil autrement ?</h2>
            <p class="mt-3 text-slate-600">Le premier test est offert. Tu repars avec ta synthèse et tes 15 pistes.</p>
            <Link :href="route('register')" class="pt-btn-primary px-8 py-3 mt-8 text-base inline-block">Commencer maintenant</Link>
        </section>

        <footer class="border-t border-slate-100 py-10">
            <div class="mx-auto max-w-6xl px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>© {{ new Date().getFullYear() }} {{ branding.name }} — Tous droits réservés.</p>
                <p>{{ branding.tagline }}</p>
            </div>
        </footer>
    </div>
</template>
