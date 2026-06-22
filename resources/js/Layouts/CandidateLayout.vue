<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import OracleChat from '@/Components/OracleChat.vue'

const mobileOpen = ref(false)
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

                <!-- Bouton burger (mobile uniquement) -->
                <button
                    v-if="user"
                    type="button"
                    class="cand-burger"
                    :aria-expanded="mobileOpen"
                    aria-label="Ouvrir le menu"
                    @click="mobileOpen = !mobileOpen"
                >
                    <span></span><span></span><span></span>
                </button>

                <!-- Navigation candidat (desktop) -->
                <nav v-if="user" class="cand-nav-desktop">
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
                        <!-- Avatar + nom : cliquable → édition du profil (statut, parcours, CV) -->
                        <Link :href="route('profile.edit')"
                            class="cand-profile-link"
                            style="display: flex; align-items: center; gap: 8px; text-decoration: none; padding: 3px 8px 3px 3px; border-radius: 999px; transition: background 0.15s"
                            title="Modifier mon profil (statut, parcours, CV)">
                            <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--color-accent); display: flex; align-items: center; justify-content: center; color: var(--color-primary); flex-shrink: 0; border: 1px solid var(--border-mid)">
                                <!-- Emblème de quête : boussole d'aventurier -->
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                    <polygon points="15.5 8.5 11 11 8.5 15.5 13 13" fill="currentColor" stroke="none" />
                                    <circle cx="12" cy="12" r="0.6" fill="currentColor" stroke="none" />
                                </svg>
                            </div>
                            <span style="font-size: 13px; color: var(--text-secondary); font-family: var(--font-body)">{{ user.name }}</span>
                        </Link>

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

            <!-- Menu mobile déroulant -->
            <nav v-if="user && mobileOpen" class="cand-mobile-menu">
                <Link :href="route('tests.index')" class="cand-mobile-link" @click="mobileOpen = false">L'Armurerie</Link>
                <Link :href="route('grimoire.show')" class="cand-mobile-link" @click="mobileOpen = false">Le Grimoire</Link>
                <Link :href="route('history')" class="cand-mobile-link" @click="mobileOpen = false">Chroniques</Link>
                <Link v-if="hasTreasure" :href="route('treasure.index')" class="cand-mobile-link" @click="mobileOpen = false">Le Trésor</Link>
                <Link :href="route('profile.edit')" class="cand-mobile-link" @click="mobileOpen = false">👤 Mon profil (statut, parcours, CV)</Link>
                <Link :href="route('gdpr.show')" class="cand-mobile-link" @click="mobileOpen = false">🔒 Mes données & RGPD</Link>
                <Link :href="route('logout')" method="post" as="button" class="cand-mobile-link cand-mobile-link--danger" @click="mobileOpen = false">Quitter la Quête</Link>
            </nav>
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

        <!-- L'Oracle — chat IA d'orientation, flottant en bas à droite -->
        <OracleChat v-if="user" />
    </div>
</template>

<style scoped>
.cand-nav-link:hover {
    color: var(--text-primary) !important;
    background: var(--bg-elevated) !important;
}

/* Avatar + nom cliquable (édition profil) */
.cand-profile-link:hover {
    background: var(--bg-elevated);
}
.cand-profile-link:hover span {
    color: var(--text-primary) !important;
}

/* Navigation desktop : flex par défaut */
.cand-nav-desktop {
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Burger masqué sur desktop */
.cand-burger {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 4px;
    width: 38px;
    height: 38px;
    padding: 8px;
    background: none;
    border: 1px solid var(--border-mid);
    border-radius: var(--r-sm);
    cursor: pointer;
}

.cand-burger span {
    display: block;
    height: 2px;
    width: 100%;
    background: var(--color-primary);
    border-radius: 2px;
}

/* Menu mobile déroulant */
.cand-mobile-menu {
    display: flex;
    flex-direction: column;
    padding: 8px 1.25rem 16px;
    gap: 2px;
    border-top: 1px solid var(--glass-border);
}

.cand-mobile-link {
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 500;
    color: var(--text-secondary);
    text-decoration: none;
    text-align: left;
    padding: 12px 8px;
    border-radius: var(--r);
    background: none;
    border: none;
    cursor: pointer;
    width: 100%;
}

.cand-mobile-link:hover {
    color: var(--text-primary);
    background: var(--bg-elevated);
}

.cand-mobile-link--danger {
    color: var(--color-danger, #B03020);
    margin-top: 4px;
}

/* Bascule responsive : burger < 768px, nav desktop cachée */
@media (max-width: 768px) {
    .cand-nav-desktop {
        display: none;
    }
    .cand-burger {
        display: flex;
    }
}
</style>
