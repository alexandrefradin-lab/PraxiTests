<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    legal:    { type: Object, default: () => ({}) },
    contact:  { type: Object, default: () => ({}) },
    branding: { type: Object, default: () => ({ name: 'PraxiQuest' }) },
})

const version = '1.0'
const date = '26 juin 2026'
</script>

<template>
    <Head title="Mentions légales" />

    <div style="min-height:100vh; background:var(--bg-base); font-family:var(--font-body); color:var(--text-primary)">

        <header style="background:var(--color-accent); padding:1.25rem 2rem; display:flex; align-items:center; gap:12px">
            <Link href="/" style="display:flex; align-items:center; gap:10px; text-decoration:none">
                <div style="width:36px; height:36px; border-radius:8px; background:var(--color-primary); display:flex; align-items:center; justify-content:center">
                    <span style="font-family:var(--font-display); font-size:17px; font-weight:700; color:var(--bg-base)">P</span>
                </div>
                <span style="font-family:var(--font-display); font-size:16px; font-weight:600; color:var(--bg-base)">{{ branding?.name ?? 'PraxiQuest' }}</span>
            </Link>
            <span style="color:rgba(240,232,212,.4); margin-left:auto; font-family:var(--font-data); font-size:12px">v{{ version }} — {{ date }}</span>
        </header>

        <main style="max-width:860px; margin:0 auto; padding:3rem 2rem 5rem">

            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:700; letter-spacing:-0.02em; color:var(--text-primary); margin-bottom:0.5rem">
                Mentions légales
            </h1>
            <p style="font-family:var(--font-data); font-size:13px; color:var(--text-muted); margin-bottom:3rem">
                Conformément à l'article 6 de la loi n° 2004-575 du 21 juin 2004 (LCEN)
            </p>

            <section class="cgu-section">
                <h2>1. Éditeur du site</h2>
                <p>
                    <strong>{{ legal.editor_name }}</strong><br>
                    {{ legal.editor_status }}<br>
                    <template v-if="legal.editor_address">{{ legal.editor_address }}<br></template>
                    <template v-if="legal.editor_siret">SIRET : {{ legal.editor_siret }}<br></template>
                    Contact : <a :href="`mailto:${legal.editor_email}`">{{ legal.editor_email }}</a>
                </p>
            </section>

            <section class="cgu-section">
                <h2>2. Directeur de la publication</h2>
                <p>{{ legal.publisher }}</p>
            </section>

            <section class="cgu-section">
                <h2>3. Hébergeur</h2>
                <p>
                    <strong>{{ legal.host_name }}</strong><br>
                    {{ legal.host_address }}<br>
                    <template v-if="legal.host_phone">Tél. : {{ legal.host_phone }}</template>
                </p>
            </section>

            <section class="cgu-section">
                <h2>4. Propriété intellectuelle</h2>
                <p>
                    L'ensemble des contenus présents sur la Plateforme (textes, interfaces, algorithmes de scoring,
                    questions de tests, graphismes, marques et logos) est protégé par le droit de la propriété
                    intellectuelle et demeure la propriété exclusive de l'éditeur ou de ses concédants. Toute
                    reproduction ou exploitation non autorisée est interdite.
                </p>
            </section>

            <section class="cgu-section">
                <h2>5. Données personnelles</h2>
                <p>
                    Le traitement de vos données personnelles est décrit dans notre
                    <Link href="/confidentialite" style="color:var(--color-primary); font-weight:600">Politique de confidentialité</Link>.
                    Les conditions d'utilisation figurent dans nos
                    <Link href="/cgu" style="color:var(--color-primary); font-weight:600">Conditions Générales d'Utilisation</Link>.
                </p>
            </section>

            <section class="cgu-section" style="border-left:3px solid var(--color-primary); padding-left:1.25rem; border-bottom:none">
                <h2 style="margin-top:0">Contact</h2>
                <p>
                    <strong>{{ legal.editor_name }}</strong> —
                    <a :href="`mailto:${contact.email ?? legal.editor_email}`">{{ contact.email ?? legal.editor_email }}</a>
                </p>
            </section>

        </main>
    </div>
</template>

<style scoped>
.cgu-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-light);
}
.cgu-section h2 {
    font-family: var(--font-display);
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
    margin-top: 0;
}
.cgu-section p {
    font-size: 0.9375rem;
    line-height: 1.75;
    color: var(--text-secondary);
    margin-bottom: 0.85rem;
}
.cgu-section p:last-child { margin-bottom: 0; }
.cgu-section a { color: var(--color-primary); text-decoration: none; }
.cgu-section a:hover { text-decoration: underline; }
</style>
