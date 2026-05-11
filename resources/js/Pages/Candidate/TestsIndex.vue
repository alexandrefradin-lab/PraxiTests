<script setup>
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

defineProps({
    tests: Array,
    profile_complete: Boolean,
})
</script>

<template>
    <CandidateLayout>
        <Head title="Tes tests" />

        <div class="flex items-end justify-between mb-8">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Choisis ton parcours</h1>
                <p class="text-slate-600 mt-1">Chaque test est une étape de ta cartographie.</p>
            </div>
        </div>

        <div v-if="!profile_complete" class="pt-card p-6 mb-8 border-l-4 border-amber-400 bg-amber-50">
            <p class="text-sm text-amber-900">Ton profil n'est pas encore complet. <Link :href="route('onboarding.show')" class="underline font-medium">Le compléter</Link> pour débloquer les tests.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div v-for="test in tests" :key="test.id" class="pt-card p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-lg">{{ test.name }}</h3>
                <p class="text-sm text-slate-600 mt-2">{{ test.description }}</p>
                <div class="flex items-center justify-between mt-6">
                    <span class="text-xs text-slate-500">≈ {{ test.estimated_minutes }} min</span>
                    <Link :href="route('tests.show', test.slug)" class="pt-btn-primary text-xs" :class="{ 'pointer-events-none opacity-40': !profile_complete }">
                        Commencer
                    </Link>
                </div>
            </div>
        </div>
    </CandidateLayout>
</template>
