<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const branding = computed(() => page.props.branding ?? { name: 'PraxiQuest', tagline: 'Évaluer. Orienter. Transformer.' })
const xpProgress = computed(() => page.props.gamification?.xp_progress ?? 0)
const xpTotal = computed(() => page.props.gamification?.xp_total ?? 0)
// Lien Salle du Trésor (route core, toujours présente — guard par sécurité).
const hasTreasure = computed(() => {
    try { return route().has('treasure.index') } catch (e) { return false }
})
</script>

<template>
    <div class="min-h-screen flex flex-col" style="background: var(--bg-base)">

        <!-- Header glassmorphism sticky -->
        <header class="ac-glass" style="position: sticky; top: 0; z-index: 50; box-shadow: var(--shadow-card)">
            <div class="mx-auto" style="max-width: 1100px; padding: 0 2rem; height: 62px; display: flex; align-items: center; justify-content: space-between">

                <!-- Logo (aligné sur la page d'accueil : boussole + nom + sous-titre) -->
                <Link :href="route('home')" style="display: flex; align-items: center; gap: 10px; text-decoration: none">
                    <svg width="34" height="34" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0">
                        <circle cx="19" cy="19" r="17.5" stroke="var(--color-primary)" stroke-width="1"/>
                        <circle cx="19" cy="19" r="13" stroke="var(--color-primary)" stroke-width="0.5" opacity="0.5"/>
                        <polygon points="19,6 20.4,18 19,21 17.6,18" fill="var(--color-primary)"/>
                        <polygon points="19,32 20.4,20 19,17 17.6,20" fill="var(--color-primary)" opacity="0.35"/>
                        <circle cx="19" cy="19" r="2" fill="var(--color-primary)"/>
                        <circle cx="19" cy="19" r="1" fill="var(--bg-base)"/>
                    </svg>
                    <div style="display: flex; flex-direction: column; gap: 1px">
                        <span style="font-family: var(--font-display); font-size: 16px; font-weight: 600; color: var(--text-primary); letter-spacing: -0.01em; line-height: 1">{{ branding.name }}</span>
                        <span style="font-family: var(--font-data); font-size: 9px; font-weight: 400; color: var(--color-primary); letter-spacing: 0.14em; text-transform: uppercase; line-height: 1">Voyage intérieur</span>
                    </div>
                </Link>

                <!-- Navigation candidat -->
                <nav v-if="user" style="display: flex; align-items: center; gap: 4px">
                    <Link :href="route('tests.index')"
                        class="cand-nav-link"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        L'Armurerie
                    </Link>
                    <Link :href="route('grimoire.show')"
                        class="cand-nav-link"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        Le Grimoire
                    </Link>
                    <Link :href="route('history')"
                        class="cand-nav-link"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        Chroniques
                    </Link>
                    <Link v-if="hasTreasure" :href="route('treasure.index')"
                        class="cand-nav-link"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        Le Trésor
                    </Link>

                    <div style="width: 1px; height: 20px; background: var(--border-mid); margin: 0 8px"></div>

                    <!-- User zone -->
                    <div style="display: flex; align-items: center; gap: 8px">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--color-accent); display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-size: 12px; font-weight: 700; color: var(--color-primary); flex-shrink: 0; border: 1px solid var(--border-mid)">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <span style="font-size: 13px; color: var(--text-secondary); font-family: var(--font-body)">{{ user.name }}</span>

                        <Link :href="route('gdpr.show')"
                            class="cand-nav-link"
                            style="font-family: var(--font-display); font-size: 12px; font-weight: 500; color: var(--text-muted); text-decoration: none; padding: 5px 10px; border-radius: var(--r-sm); transition: color 0.15s, background 0.15s"
                            title="Mes données & confidentialité">
                            🔒
                        </Link>

                        <Link :href="route('logout')" method="post" as="button"
                            class="ac-btn-danger"
                            style="font-size: 12px; padding: 5px 12px; border-radius: var(--r-sm)">
                            Quitter la Quête
                        </Link>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Barre XP -->
        <div v-if="user" class="xp-bar" style="position: relative">
            <div class="xp-bar__fill" :style="{ width: xpProgress + '%' }"></div>
            <span v-if="xpTotal > 0" class="xp-bar__label" style="line-height: 4px; top: 2px; transform: none">
                {{ xpTotal }} Éclats
            </span>
        </div>

        <!-- Body -->
        <main class="flex-1">
            <div class="mx-auto" style="max-width: 1100px; padding: 2.5rem 2rem">

                <!-- Flash messages -->
                <div v-if="$page.props.flash?.success" class="ac-flash-success mb-6 ac-fade-in">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="ac-flash-error mb-6 ac-fade-in">
                    {{ $page.props.flash.error }}
                </div>

                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer style="border-top: 1px solid var(--glass-border); padding: 1.25rem 2rem; text-align: center">
            <div style="display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap">
                <p style="font-family: var(--font-data); font-size: 11px; color: var(--text-muted); letter-spacing: 0.03em">
                    {{ branding.tagline || 'Évaluer. Orienter. Transformer.' }}
                </p>
                <span style="color: var(--border-mid)">·</span>
                <Link
                    v-if="user"
                    :href="route('gdpr.show')"
                    style="font-family: var(--font-data); font-size: 11px; color: var(--text-muted); text-decoration: none; letter-spacing: 0.03em; transition: color 0.15s"
                    onmouseenter="this.style.color='var(--text-secondary)'"
                    onmouseleave="this.style.color='var(--text-muted)'"
                >
                    🔒 Mes données & RGPD
                </Link>
                <span style="color: var(--border-mid)">·</span>
                <Link :href="route('cgu')" style="font-family: var(--font-data); font-size: 11px; color: var(--text-muted); text-decoration: none; letter-spacing: 0.03em">
                    CGU
                </Link>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.cand-nav-link:hover {
    color: var(--text-primary) !important;
    background: var(--bg-elevated) !important;
}
</style>
