<script setup>
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
</script>

<template>
    <CandidateLayout>
        <div style="max-width: 480px; margin: 6rem auto; padding: 0 1.5rem; text-align: center">
            <div style="font-size: 3rem; margin-bottom: 1.5rem">🎉</div>
            <h1 style="font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; color: var(--text-primary); margin-bottom: 0.75rem">
                Bienvenue dans l'aventure !
            </h1>
            <p style="font-size: 15px; color: var(--pt-text-muted); margin-bottom: 2rem; line-height: 1.6">
                Ton abonnement est activé. Tu peux maintenant accéder à tous les tests disponibles dans ton plan.
            </p>
            <Link
                :href="route('tests.index')"
                style="display: inline-block; background: var(--pt-navy); color: var(--bg-surface); border-radius: 10px; padding: 13px 28px; font-size: 14px; font-weight: 600; text-decoration: none"
            >
                Découvrir les tests →
            </Link>
            <div style="margin-top: 1.25rem">
                <Link :href="route('billing.manage')" style="font-size: 13px; color: var(--pt-text-muted); text-decoration: underline">
                    Gérer mon abonnement
                </Link>
            </div>
        </div>
    </CandidateLayout>
</template>
