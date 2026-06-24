<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount, watch } from 'vue'
import OracleChat from '@/Components/OracleChat.vue'

const mobileOpen = ref(false)
const page = usePage()

// ─── Polling synthèse IA ───────────────────────────────────────────────
// Les pages de résultats (core + plugins) affichent un loader tant que
// result.ai_synthesis n'est pas prête. Le job d'analyse tourne APRÈS la
// réponse HTTP : sans rechargement, le loader reste figé indéfiniment.
// Ce poller interroge results.status et recharge la page (partiellement)
// dès que l'IA a terminé. Inactif sur les pages sans ai_pending.
let aiPoll = null
function stopAiPoll () { if (aiPoll) { clearInterval(aiPoll); aiPoll = null } }
function startAiPoll () {
    stopAiPoll()
    const attemptId = page.props.attempt?.id
    if (!page.props.ai_pending || !attemptId) return
    let url
    try { url = route('results.status', attemptId) } catch (e) { return }
    aiPoll = setInterval(async () => {
        try {
            const r = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            if (!r.ok) return
            const data = await r.json()
            if (data.ai_ready) {
                stopAiPoll()
                router.reload({ only: ['result', 'ai_pending'] })
            }
        } catch (e) { /* réseau : on réessaie au prochain tick */ }
    }, 5000)
}
onMounted(startAiPoll)
onBeforeUnmount(stopAiPoll)
// Inertia réutilise le Layout entre navigations même-composant : relancer
// le poller si ai_pending ou l'attempt change.
watch(() => [page.props.ai_pending, page.props.attempt?.id], startAiPoll)
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
                            <div style="position: relative; width: 30px; height: 30px; flex-shrink: 0;">
                                <!-- Hexagone initiales -->
                                <svg width="30" height="30" viewBox="0 0 30 30" aria-hidden="true">
                                    <polygon points="15,2 26,8.5 26,21.5 15,28 4,21.5 4,8.5" fill="var(--color-accent)" stroke="var(--color-primary)" stroke-width="1"/>
                                    <polygon points="15,5 23,9.5 23,20.5 15,25 7,20.5 7,9.5" fill="none" stroke="var(--color-primary)" stroke-width="0.4" opacity="0.4"/>
                                </svg>
                                <span style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-size: 11px; font-weight: 700; color: var(--color-primary); letter-spacing: 0.03em; text-transform: uppercase; line-height: 1; user-select: none;">
                                    {{ user.name ? user.name.slice(0, 2) : '?' }}
                                </span>
                            </div>
                            <span style="font-size: 13px; color: var(--text-secondary); font-family: var(--font-body)">{{ user.name }}</span>
                        </Link>

                        <Link :href="route('gdpr.show')"
                            class="cand-nav-link"
                            style="font-family: var(--font-display); font-size: 12px; font-weight: 500; color: var(--text-muted); text-decoration: none; padding: 5px 10px; border-radius: var(--r-sm); transition: color 0.15s, background 0.15s; display: inline-flex; align-items: center;"
                            title="Mes données & confidentialité">
                            <i class="ti ti-shield-lock" style="font-size: 15px;"></i>
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
                <Link :href="route('profile.edit')" class="cand-mobile-link" @click="mobileOpen = false"><i class="ti ti-user" style="margin-right:6px"></i>Mon profil (statut, parcours, CV)</Link>
                <Link :href="route('gdpr.show')" class="cand-mobile-link" @click="mobileOpen = false"><i class="ti ti-shield-lock" style="margin-right:6px"></i>Mes données & RGPD</Link>
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
                <div v-if="$page.props.flash?.info" class="ac-flash-info mb-6 ac-fade-in">
                    {{ $page.props.flash.info }}
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
                    style="font-family: var(--font-data); font-size: 11px; color: var(--text-muted); text-decoration: none; letter-spacing: 0.03em; transition: color 0.15s; display: inline-flex; align-items: center; gap: 4px;"
                    @mouseenter="(e) => e.currentTarget.style.color = 'var(--text-secondary)'"
                    @mouseleave="(e) => e.currentTarget.style.color = 'var(--text-muted)'"
                >
                    <i class="ti ti-shield-lock" style="font-size: 12px;"></i> Mes données & RGPD
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
