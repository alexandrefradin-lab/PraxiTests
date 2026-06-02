<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const branding = computed(() => usePage().props.branding ?? { name: 'PraxiQuest', tagline: 'Évaluer. Orienter. Transformer.' })
</script>

<template>
    <div class="min-h-screen flex" style="background: var(--pt-cream)">

        <!-- Panneau gauche — Marine / identité -->
        <div class="hidden lg:flex flex-col justify-between" style="width: 420px; flex-shrink: 0; background: var(--pt-navy); padding: 3rem 3rem 2.5rem">

            <!-- Logo -->
            <Link href="/" style="display: flex; align-items: center; gap: 12px; text-decoration: none">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: var(--pt-gold); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 10px rgba(184,145,58,.4)">
                    <span style="font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 700; color: white; line-height: 1">P</span>
                </div>
                <div>
                    <div style="font-size: 16px; font-weight: 600; color: white; letter-spacing: -0.01em">{{ branding.name }}</div>
                    <div style="font-size: 10px; color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 1px">Plateforme d'évaluation</div>
                </div>
            </Link>

            <!-- Citation centrale -->
            <div>
                <div style="width: 32px; height: 2px; background: var(--pt-gold); margin-bottom: 1.75rem; border-radius: 2px"></div>
                <blockquote style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 400; color: white; line-height: 1.4; letter-spacing: -0.01em">
                    « Connaître ses forces, c'est choisir sa trajectoire. »
                </blockquote>
                <p style="font-size: 13px; color: rgba(255,255,255,.4); margin-top: 1.25rem; font-style: italic">
                    Synthèse IA · Bilan de compétences · Orientation experte
                </p>
            </div>

            <!-- Bas — Indicateurs qualité -->
            <div style="display: flex; flex-direction: column; gap: 12px">
                <div style="display: flex; align-items: center; gap: 10px">
                    <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--pt-gold); flex-shrink: 0"></div>
                    <span style="font-size: 12px; color: rgba(255,255,255,.4)">Analyse IA multi-dimensions</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px">
                    <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--pt-gold); flex-shrink: 0"></div>
                    <span style="font-size: 12px; color: rgba(255,255,255,.4)">15 pistes métiers personnalisées</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px">
                    <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--pt-gold); flex-shrink: 0"></div>
                    <span style="font-size: 12px; color: rgba(255,255,255,.4)">Rapport PDF confidentiel</span>
                </div>
            </div>
        </div>

        <!-- Panneau droit — Formulaire -->
        <div class="flex-1 flex flex-col items-center justify-center" style="padding: 3rem 2rem">

            <!-- Logo mobile uniquement -->
            <Link href="/" class="lg:hidden mb-10 flex items-center gap-3" style="text-decoration: none">
                <div style="width: 34px; height: 34px; border-radius: 7px; background: var(--pt-navy); display: flex; align-items: center; justify-content: center">
                    <span style="font-family: 'Playfair Display', serif; font-size: 16px; font-weight: 700; color: var(--pt-gold); line-height: 1">P</span>
                </div>
                <span style="font-size: 15px; font-weight: 600; color: var(--pt-text)">{{ branding.name }}</span>
            </Link>

            <div style="width: 100%; max-width: 420px">
                <slot />
            </div>

            <p style="font-size: 11px; color: var(--pt-text-light); margin-top: 2.5rem; text-align: center">
                {{ branding.tagline }}
            </p>
        </div>
    </div>
</template>
