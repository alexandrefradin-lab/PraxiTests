<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    contact:  { type: Object, default: () => ({ email: 'contact@decisionpro.fr' }) },
    branding: { type: Object, default: () => ({ name: 'PraxiQuest' }) },
})
</script>

<template>
    <Head title="Contact & support" />

    <div style="min-height:100vh; background:var(--bg-base); font-family:var(--font-body); color:var(--text-primary)">

        <header style="background:var(--color-accent); padding:1.25rem 2rem; display:flex; align-items:center; gap:12px">
            <Link href="/" style="display:flex; align-items:center; gap:10px; text-decoration:none">
                <div style="width:36px; height:36px; border-radius:8px; background:var(--color-primary); display:flex; align-items:center; justify-content:center">
                    <span style="font-family:var(--font-display); font-size:17px; font-weight:700; color:var(--bg-base)">P</span>
                </div>
                <span style="font-family:var(--font-display); font-size:16px; font-weight:600; color:var(--bg-base)">{{ branding?.name ?? 'PraxiQuest' }}</span>
            </Link>
        </header>

        <main style="max-width:640px; margin:0 auto; padding:4rem 2rem 5rem">

            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:700; letter-spacing:-0.02em; margin-bottom:0.75rem">
                Contact &amp; support
            </h1>
            <p style="font-size:0.9375rem; line-height:1.75; color:var(--text-secondary); margin-bottom:2.5rem">
                Une question, un souci technique ou une demande concernant votre abonnement&nbsp;?
                Notre équipe vous répond dans les meilleurs délais.
            </p>

            <a
                :href="`mailto:${contact.email}`"
                style="display:inline-flex; align-items:center; gap:0.75rem; padding:1rem 1.5rem; border-radius:10px; background:var(--color-primary); color:var(--bg-base); text-decoration:none; font-family:var(--font-display); font-weight:600; font-size:0.9375rem"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                    <path d="m22 7-10 5L2 7"/>
                </svg>
                {{ contact.email }}
            </a>

            <div style="margin-top:3rem; padding-top:2rem; border-top:1px solid var(--border-light)">
                <p style="font-size:0.875rem; color:var(--text-muted); line-height:1.7">
                    Pour exercer vos droits sur vos données personnelles, rendez-vous dans votre espace,
                    rubrique <em>Mes données (RGPD)</em>, ou consultez notre
                    <Link href="/confidentialite" style="color:var(--color-primary); font-weight:600">Politique de confidentialité</Link>.
                </p>
                <p style="font-size:0.875rem; color:var(--text-muted); line-height:1.7; margin-top:0.75rem">
                    Voir aussi&nbsp;:
                    <Link href="/cgu" style="color:var(--color-primary); font-weight:600">CGU</Link> ·
                    <Link href="/mentions-legales" style="color:var(--color-primary); font-weight:600">Mentions légales</Link>
                </p>
            </div>

        </main>
    </div>
</template>
