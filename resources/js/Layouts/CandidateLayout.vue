<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const branding = computed(() => page.props.branding ?? { name: 'PraxiQuest' })
</script>

<template>
    <div class="min-h-screen flex flex-col" style="background: var(--pt-cream)">

        <!-- Header -->
        <header style="background: var(--pt-white); border-bottom: 0.5px solid var(--pt-border); position: sticky; top: 0; z-index: 10; box-shadow: var(--pt-shadow-xs)">
            <div class="mx-auto" style="max-width: 1100px; padding: 0 2rem; height: 60px; display: flex; align-items: center; justify-content: space-between">

                <!-- Logo -->
                <Link :href="route('home')" style="display: flex; align-items: center; gap: 10px; text-decoration: none">
                    <div style="width: 30px; height: 30px; border-radius: 6px; background: var(--pt-navy); display: flex; align-items: center; justify-content: center; flex-shrink: 0">
                        <span style="font-family: 'Playfair Display', serif; font-size: 14px; font-weight: 700; color: var(--pt-gold); line-height: 1">P</span>
                    </div>
                    <span style="font-size: 15px; font-weight: 600; color: var(--pt-text); letter-spacing: -0.01em">{{ branding.name }}</span>
                </Link>

                <!-- Navigation candidat -->
                <nav v-if="user" style="display: flex; align-items: center; gap: 8px">
                    <Link :href="route('tests.index')"
                        style="font-size: 13px; color: var(--pt-text-muted); text-decoration: none; padding: 6px 12px; border-radius: 6px; transition: color 0.1s, background 0.1s"
                        class="cand-nav-link">
                        Mes tests
                    </Link>
                    <Link :href="route('history')"
                        style="font-size: 13px; color: var(--pt-text-muted); text-decoration: none; padding: 6px 12px; border-radius: 6px; transition: color 0.1s, background 0.1s"
                        class="cand-nav-link">
                        Historique
                    </Link>

                    <div style="width: 0.5px; height: 18px; background: var(--pt-border); margin: 0 6px"></div>

                    <!-- User -->
                    <div style="display: flex; align-items: center; gap: 8px">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--pt-navy); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: rgba(255,255,255,.8); flex-shrink: 0">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                        <span style="font-size: 13px; color: var(--pt-text-muted)">{{ user.name }}</span>
                        <Link :href="route('logout')" method="post" as="button"
                            style="font-size: 12px; color: var(--pt-text-light); background: none; border: none; cursor: pointer; padding: 6px; border-radius: 6px; transition: color 0.1s"
                            class="cand-nav-link">
                            Déconnexion
                        </Link>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Body -->
        <main class="flex-1">
            <div class="mx-auto" style="max-width: 1100px; padding: 2.5rem 2rem">
                <!-- Flash messages -->
                <div v-if="$page.props.flash?.success" class="pt-flash-success mb-6">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash?.error" class="pt-flash-error mb-6">
                    {{ $page.props.flash.error }}
                </div>

                <slot />
            </div>
        </main>

        <!-- Footer -->
        <footer style="border-top: 0.5px solid var(--pt-border); padding: 1.5rem 2rem; text-align: center">
            <p style="font-size: 12px; color: var(--pt-text-light)">
                {{ branding.name }} &mdash;
                <span style="font-style: italic">{{ branding.tagline || 'Évaluer. Orienter. Transformer.' }}</span>
            </p>
        </footer>
    </div>
</template>

<style scoped>
.cand-nav-link:hover {
    color: var(--pt-text) !important;
    background: var(--pt-cream) !important;
}
</style>
