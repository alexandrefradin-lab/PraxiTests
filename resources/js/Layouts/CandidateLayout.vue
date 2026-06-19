<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const branding = computed(() => page.props.branding ?? { name: 'PraxiQuest', tagline: 'Évaluer. Orienter. Transformer.' })
const xpProgress = computed(() => page.props.gamification?.xp_progress ?? 0)
const xpTotal = computed(() => page.props.gamification?.xp_total ?? 0)
</script>

<template>
    <div class="min-h-screen flex flex-col" style="background: var(--bg-base)">

        <!-- Header glassmorphism sticky -->
        <header class="ac-glass" style="position: sticky; top: 0; z-index: 50; box-shadow: var(--shadow-card)">
            <div class="mx-auto" style="max-width: 1100px; padding: 0 2rem; height: 62px; display: flex; align-items: center; justify-content: space-between">

                <!-- Logo -->
                <Link :href="route('home')" style="display: flex; align-items: center; gap: 10px; text-decoration: none">
                    <div style="width: 32px; height: 32px; border-radius: 6px; background: var(--color-accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(42,30,8,0.3)">
                        <span style="font-family: var(--font-display); font-size: 15px; font-weight: 700; color: var(--color-primary); line-height: 1">P</span>
                    </div>
                    <span style="font-family: var(--font-display); font-size: 15px; font-weight: 600; color: var(--text-primary); letter-spacing: -0.01em">{{ branding.name }}</span>
                </Link>

                <!-- Navigation candidat -->
                <nav v-if="user" style="display: flex; align-items: center; gap: 4px">
                    <Link :href="route('tests.index')"
                        class="cand-nav-link"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        L'Armurerie
                    </Link>
                    <Link :href="route('history')"
                        class="cand-nav-link"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        Chroniques
                    </Link>

                    <div style="width: 1px; height: 20px; background: var(--border-mid); margin: 0 8px"></div>

                    <!-- User zone -->
                    <div style="display: flex; align-items: center; gap: 8px">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--color-accent); display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-size: 12px; font-weight: 700; color: var(--color-primary); flex-shrink: 0; border: 1px solid var(--border-mid)">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <span style="font-size: 13px; color: var(--text-secondary); font-family: var(--font-body)">{{ user.name }}</span>

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
            <p style="font-family: var(--font-data); font-size: 11px; color: var(--text-muted); letter-spacing: 0.03em">
                {{ branding.tagline || 'Évaluer. Orienter. Transformer.' }}
            </p>
        </footer>
    </div>
</template>

<style scoped>
.cand-nav-link:hover {
    color: var(--text-primary) !important;
    background: var(--bg-elevated) !important;
}
</style>
