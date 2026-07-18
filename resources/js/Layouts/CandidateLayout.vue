<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import OracleChat from '@/Components/OracleChat.vue'
import EasterEgg from '@/Components/EasterEgg.vue'
import { useParcours } from '@/composables/useParcours'

const { isCorporate, L, setParcours } = useParcours()

const mobileOpen = ref(false)

// ─── Easter egg — Konami Code ─────────────────────────────────────────
const KONAMI = ['ArrowUp','ArrowUp','ArrowDown','ArrowDown','ArrowLeft','ArrowRight','ArrowLeft','ArrowRight','b','a']
const konamiIdx = ref(0)
const showEasterEgg = ref(false)

function onKonami(e) {
    if (e.key === KONAMI[konamiIdx.value]) {
        konamiIdx.value++
        if (konamiIdx.value === KONAMI.length) {
            konamiIdx.value = 0
            showEasterEgg.value = true
        }
    } else {
        konamiIdx.value = e.key === KONAMI[0] ? 1 : 0
    }
}
const mobileDrawer = ref(null)
const navigating = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)

function closeUserMenu () { userMenuOpen.value = false }
function onUserMenuKeydown (e) {
    if (e.key === 'Escape') closeUserMenu()
}
function onClickOutsideUserMenu (e) {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) closeUserMenu()
}
const navTarget = ref('')
let navTimeout = null
let stopNavStart = null
let stopNavFinish = null
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

// FE-C4 focus trap
function trapFocus(e) {
    if (!mobileDrawer.value) return
    const f=mobileDrawer.value.querySelectorAll('a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])')
    if(!f.length)return
    const first=f[0],last=f[f.length-1]
    if(e.key==='Tab'){if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus()}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus()}}
    if(e.key==='Escape'){mobileOpen.value=false}
}
watch(mobileOpen,(isOpen)=>{if(isOpen){nextTick(()=>{document.addEventListener('keydown',trapFocus);mobileDrawer.value?.querySelector('a,button')?.focus()})}else{document.removeEventListener('keydown',trapFocus)}})
function onKeydown(e) {
    if (e.key === 'Escape') mobileOpen.value = false
}

onMounted(() => {
    startAiPoll()
    window.addEventListener('keydown', onKeydown)
    window.addEventListener('keydown', onKonami)
    document.addEventListener('click', onClickOutsideUserMenu)
    stopNavStart = router.on('start', (event) => {
        navTarget.value = event.detail?.visit?.url ?? ''
        navTimeout = setTimeout(() => { navigating.value = true }, 120)
    })
    stopNavFinish = router.on('finish', () => {
        clearTimeout(navTimeout)
        navTimeout = null
        navigating.value = false
    })
})
onBeforeUnmount(() => {
    document.removeEventListener('keydown',trapFocus)
    document.removeEventListener('click', onClickOutsideUserMenu)
    stopAiPoll()
    window.removeEventListener('keydown', onKeydown)
    window.removeEventListener('keydown', onKonami)
    if (stopNavStart) stopNavStart()
    if (stopNavFinish) stopNavFinish()
    // Toujours libérer le scroll du body si le layout est démonté
    // (ex: logout depuis le drawer ouvert → sinon le body reste locké)
    document.body.style.overflow = ''
    clearTimeout(navTimeout)
    clearTimeout(achievementTimeout)
    clearTimeout(levelUpTimeout)
})

// Inertia réutilise le Layout entre navigations même-composant : relancer
// le poller si ai_pending ou l'attempt change.
watch(() => [page.props.ai_pending, page.props.attempt?.id], startAiPoll)

// Fermer le drawer à chaque navigation Inertia
watch(() => page.url, () => { mobileOpen.value = false })

// Bloquer le scroll du body quand le drawer est ouvert
watch(mobileOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : ''
})

const user = computed(() => page.props.auth?.user)
const branding = computed(() => page.props.branding ?? { name: 'PraxiQuest', tagline: 'Évaluer. Orienter. Transformer.' })
const xpProgress = computed(() => page.props.gamification?.xp_progress ?? 0)
const xpTotal = computed(() => page.props.gamification?.xp_total ?? 0)
const level = computed(() => page.props.gamification?.level ?? 1)
const levelName = computed(() => page.props.gamification?.level_name ?? `Niveau ${level.value}`)

// ─── Animation « +N ✦ » quand l'XP total augmente ──────────────────────────
const xpGain = ref(0)
const showXpGain = ref(false)
let xpGainTimeout = null
let prevXp = null
watch(xpTotal, (val) => {
    if (prevXp !== null && val > prevXp) {
        xpGain.value = val - prevXp
        showXpGain.value = true
        clearTimeout(xpGainTimeout)
        xpGainTimeout = setTimeout(() => { showXpGain.value = false }, 2200)
    }
    if (val != null) prevXp = val
}, { immediate: true })

function isActive(path) {
    return page.url === path || page.url.startsWith(path + '/')
}
// Lien Salle du Trésor (route core, toujours présente — guard par sécurité).
const hasTreasure = computed(() => {
    try { return route().has('treasure.index') } catch (e) { return false }
})

// ─── Badge "nouveau trésor débloqué" ──────────────────────────────────────
// Compare le nombre de trésors débloqués (partagé via Inertia) avec la dernière
// valeur vue stockée en localStorage. Affiche un point animé sur "Le Trésor"
// jusqu'à la prochaine visite de /treasure.
const LS_KEY = 'pq_treasure_seen_count'
const hasNewTreasure = ref(false)

function checkNewTreasure() {
    const current = page.props.gamification?.treasure_unlocked_count ?? 0
    const seen = parseInt(localStorage.getItem(LS_KEY) ?? '0', 10)
    hasNewTreasure.value = current > seen
}

function markTreasureSeen() {
    const current = page.props.gamification?.treasure_unlocked_count ?? 0
    localStorage.setItem(LS_KEY, String(current))
    hasNewTreasure.value = false
}

// Vérifier au montage et à chaque changement de gamification (ex. après un test)
onMounted(() => { checkNewTreasure() })
watch(() => page.props.gamification?.treasure_unlocked_count, () => { checkNewTreasure() })

// Effacer le badge dès que l'utilisateur arrive sur /treasure
watch(() => page.url, (url) => {
    if (url.startsWith('/treasure')) markTreasureSeen()
})

// ─── Achievement toast ────────────────────────────────────────────────────
const showAchievement = ref(false)
const achievementData = ref(null)
let achievementTimeout = null

watch(() => page.props.flash?.achievement, (achievement) => {
    if (achievement) {
        achievementData.value = achievement
        showAchievement.value = true
        clearTimeout(achievementTimeout)
        achievementTimeout = setTimeout(() => { showAchievement.value = false }, 4500)
    }
}, { immediate: true })

// ─── Level Up overlay ─────────────────────────────────────────────────────
const showLevelUp = ref(false)
const levelUpData = ref(null)
const prevLevel = ref(null)
let levelUpTimeout = null

watch(() => page.props.gamification?.level, (newLevel) => {
    if (prevLevel.value !== null && newLevel > prevLevel.value) {
        levelUpData.value = {
            level: newLevel,
            name: page.props.gamification?.level_name ?? `Niveau ${newLevel}`,
        }
        showLevelUp.value = true
        clearTimeout(levelUpTimeout)
        levelUpTimeout = setTimeout(() => { showLevelUp.value = false }, 2800)
    }
    if (newLevel != null) prevLevel.value = newLevel
}, { immediate: true })
</script>

<template>
    <div class="min-h-screen flex flex-col" style="background: var(--bg-base)">
        <a href="#main-content" class="skip-link">Aller au contenu principal</a>

        <!-- Header glassmorphism sticky -->
        <header class="ac-glass" style="position: sticky; top: 0; z-index: 50; box-shadow: var(--shadow-card)">
            <div class="mx-auto" style="max-width: 1100px; padding: 0 2rem; height: 62px; display: flex; align-items: center; justify-content: space-between">

                <!-- Logo -->
                <Link :href="route('home')" style="display: flex; align-items: center; gap: 10px; text-decoration: none">
                    <svg width="34" height="34" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0">
                        <circle cx="19" cy="19" r="17.5" stroke="var(--logo-ring)" stroke-width="1.2"/>
                        <circle cx="19" cy="19" r="13" stroke="var(--logo-ring)" stroke-width="0.5" opacity="0.5"/>
                        <polygon points="19,6 20.4,18 19,21 17.6,18" fill="var(--logo-needle)"/>
                        <polygon points="19,32 20.4,20 19,17 17.6,20" fill="var(--logo-needle)" opacity="0.35"/>
                        <circle cx="19" cy="19" r="2" fill="var(--logo-ring)"/>
                        <circle cx="19" cy="19" r="1" fill="var(--bg-base)"/>
                    </svg>
                    <div style="display: flex; flex-direction: column; gap: 1px">
                        <span style="font-family: var(--font-display); font-size: 16px; font-weight: 600; color: var(--text-primary); letter-spacing: -0.01em; line-height: 1">{{ branding.name }}</span>
                        <span style="font-family: var(--font-data); font-size: 9px; font-weight: 400; color: var(--color-primary); letter-spacing: 0.14em; text-transform: uppercase; line-height: 1">{{ L.tagline }}</span>
                    </div>
                </Link>

                <!-- Bouton burger (mobile uniquement) — anime en X quand ouvert -->
                <button
                    v-if="user"
                    type="button"
                    class="cand-burger"
                    :class="{ 'cand-burger--open': mobileOpen }"
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
                        :class="{ 'cand-nav-link--active': isActive('/tests') }"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        {{ L.navTests }}
                    </Link>
                    <Link :href="route('grimoire.show')"
                        class="cand-nav-link"
                        :class="{ 'cand-nav-link--active': isActive('/grimoire') }"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        {{ L.navGrimoire }}
                    </Link>
                    <Link :href="route('history')"
                        class="cand-nav-link"
                        :class="{ 'cand-nav-link--active': isActive('/history') }"
                        style="font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        {{ L.navHistory }}
                    </Link>
                    <Link v-if="hasTreasure" :href="route('treasure.index')"
                        class="cand-nav-link"
                        :class="{ 'cand-nav-link--active': isActive('/treasure') }"
                        style="position:relative; font-family: var(--font-display); font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; padding: 6px 13px; border-radius: var(--r); transition: color 0.15s, background 0.15s">
                        {{ L.navTreasure }}
                        <span v-if="hasNewTreasure && !isActive('/treasure')" class="tresor-dot" aria-label="Nouveau contenu débloqué"></span>
                    </Link>

                    <!-- Écu de rang du héros -->
                    <div class="cand-rank" :title="`${levelName} · Niveau ${level} · ${xpTotal} ${L.xpName}`">
                        <div class="cand-rank-shield">
                            <svg width="32" height="32" viewBox="0 0 30 30" aria-hidden="true">
                                <polygon points="15,2 26,8.5 26,21.5 15,28 4,21.5 4,8.5" fill="var(--color-accent)" stroke="var(--color-primary)" stroke-width="1"/>
                                <polygon points="15,5 23,9.5 23,20.5 15,25 7,20.5 7,9.5" fill="none" stroke="var(--color-primary)" stroke-width="0.4" opacity="0.4"/>
                            </svg>
                            <span class="cand-rank-lvl">{{ level }}</span>
                        </div>
                        <div class="cand-rank-txt">
                            <span class="cand-rank-name">{{ levelName }}</span>
                            <span class="cand-rank-sub">{{ xpTotal }} {{ L.xpUnit }}</span>
                        </div>
                    </div>

                    <div style="width: 1px; height: 20px; background: var(--border-mid); margin: 0 8px"></div>

                    <!-- User zone -->
                    <div style="display: flex; align-items: center; gap: 8px">

                        <!-- User dropdown trigger -->
                        <div ref="userMenuRef" style="position: relative;">
                            <button
                                type="button"
                                class="cand-profile-link"
                                :aria-expanded="userMenuOpen"
                                aria-haspopup="true"
                                @click.stop="userMenuOpen = !userMenuOpen"
                                @keydown="onUserMenuKeydown"
                                style="display: flex; align-items: center; gap: 8px; text-decoration: none; padding: 3px 8px 3px 3px; border-radius: 999px; transition: background 0.15s; background: none; border: none; cursor: pointer;"
                            >
                                <div style="position: relative; width: 30px; height: 30px; flex-shrink: 0;">
                                    <svg width="30" height="30" viewBox="0 0 30 30" aria-hidden="true">
                                        <polygon points="15,2 26,8.5 26,21.5 15,28 4,21.5 4,8.5" fill="var(--color-accent)" stroke="var(--color-primary)" stroke-width="1"/>
                                        <polygon points="15,5 23,9.5 23,20.5 15,25 7,20.5 7,9.5" fill="none" stroke="var(--color-primary)" stroke-width="0.4" opacity="0.4"/>
                                    </svg>
                                    <span style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-size: 11px; font-weight: 700; color: var(--color-primary); letter-spacing: 0.03em; text-transform: uppercase; line-height: 1; user-select: none;">
                                        {{ user.name ? user.name.slice(0, 2) : '?' }}
                                    </span>
                                </div>
                                <span style="font-size: 13px; color: var(--text-secondary); font-family: var(--font-body)">{{ user.name }}</span>
                                <i class="ti ti-chevron-down" style="font-size: 12px; color: var(--text-muted); transition: transform 0.2s;" :style="userMenuOpen ? 'transform: rotate(180deg)' : ''"></i>
                            </button>

                            <!-- Dropdown panel -->
                            <Transition name="pt-usermenu">
                                <div v-if="userMenuOpen" class="cand-user-menu" role="menu">
                                    <div class="cand-user-menu-header">
                                        <div style="font-size: 12px; font-weight: 600; color: var(--text-primary); font-family: var(--font-display);">{{ user.name }}</div>
                                        <div style="font-size: 10px; color: var(--color-primary); font-family: var(--font-data); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px;">Niv. {{ level }} · {{ levelName }}</div>
                                    </div>
                                    <div class="cand-user-menu-divider"></div>
                                    <Link :href="route('profile.edit')" class="cand-user-menu-item" role="menuitem" @click="closeUserMenu">
                                        <i class="ti ti-user-circle"></i>
                                        <div>
                                            <div>Mon identité</div>
                                            <div class="cand-user-menu-item-sub">Statut, parcours, CV</div>
                                        </div>
                                    </Link>
                                    <!-- L'abonnement ne concerne que les professionnels -->
                                    <Link v-if="$page.props.auth?.is_pro" :href="route('billing.manage')" class="cand-user-menu-item" role="menuitem" @click="closeUserMenu">
                                        <i class="ti ti-credit-card"></i>
                                        <div>
                                            <div>Mon abonnement</div>
                                            <div class="cand-user-menu-item-sub">Modifier ou résilier</div>
                                        </div>
                                    </Link>
                                    <Link :href="route('contact')" class="cand-user-menu-item" role="menuitem" @click="closeUserMenu">
                                        <i class="ti ti-help-circle"></i>
                                        <div>
                                            <div>Aide & contact</div>
                                            <div class="cand-user-menu-item-sub">Nous écrire</div>
                                        </div>
                                    </Link>
                                    <div class="cand-user-menu-divider"></div>
                                    <!-- Choix du parcours visuel -->
                                    <div class="cand-parcours">
                                        <div class="cand-parcours-label">Mon parcours</div>
                                        <div class="cand-parcours-toggle" role="group" aria-label="Choix du parcours">
                                            <button type="button" :class="{ 'cand-parcours-opt--active': !isCorporate }" class="cand-parcours-opt" @click="setParcours('medieval')">Médiéval</button>
                                            <button type="button" :class="{ 'cand-parcours-opt--active': isCorporate }" class="cand-parcours-opt" @click="setParcours('corporate')">Corporate</button>
                                        </div>
                                    </div>
                                    <div class="cand-user-menu-divider"></div>
                                    <Link :href="route('account.password.edit')" class="cand-user-menu-item cand-user-menu-item--muted" role="menuitem" @click="closeUserMenu">
                                        <i class="ti ti-key"></i>
                                        <div>{{ L.password }}</div>
                                    </Link>
                                    <Link :href="route('gdpr.show')" class="cand-user-menu-item cand-user-menu-item--muted" role="menuitem" @click="closeUserMenu">
                                        <i class="ti ti-shield-lock"></i>
                                        <div>Mes données & RGPD</div>
                                    </Link>
                                    <Link :href="route('logout')" method="post" as="button" class="cand-user-menu-item cand-user-menu-item--danger" role="menuitem" @click="closeUserMenu">
                                        <i class="ti ti-door-exit"></i>
                                        <div>{{ L.logout }}</div>
                                    </Link>
                                </div>
                            </Transition>
                        </div>

                    </div>
                </nav>
            </div>
        </header>

        <!-- Barre XP (fine, sans label) -->
        <div v-if="user" class="xp-bar" style="position: relative">
            <div class="xp-bar__fill" :style="{ width: xpProgress + '%' }"></div>
            <Transition name="pt-xpgain">
                <span v-if="showXpGain" class="xp-gain">+{{ xpGain }} {{ L.xpUnit }}</span>
            </Transition>
        </div>

        <!-- Body -->
        <main id="main-content" class="flex-1">
            <!-- Skeleton screens pendant la navigation Inertia -->
            <template v-if="navigating">
                <!-- Grille de cartes : tests, grimoire, trésor -->
                <div v-if="/\/(tests|grimoire|salle-du-tresor)/.test(navTarget)"
                    class="mx-auto" style="max-width:1100px;padding:2.5rem 2rem">
                    <div class="pt-skel" style="width:200px;height:34px;border-radius:8px;margin-bottom:10px"></div>
                    <div class="pt-skel" style="width:320px;height:13px;border-radius:6px;margin-bottom:2rem"></div>
                    <div style="height:1px;background:var(--glass-border);margin-bottom:1.5rem"></div>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem">
                        <div v-for="i in 6" :key="i" class="pt-skel" style="height:210px;border-radius:14px"></div>
                    </div>
                </div>
                <!-- Liste : chroniques -->
                <div v-else-if="navTarget.includes('/history')"
                    class="mx-auto" style="max-width:1100px;padding:2.5rem 2rem">
                    <div class="pt-skel" style="width:200px;height:34px;border-radius:8px;margin-bottom:10px"></div>
                    <div class="pt-skel" style="width:320px;height:13px;border-radius:6px;margin-bottom:2rem"></div>
                    <div style="display:flex;flex-direction:column;gap:0.75rem">
                        <div v-for="i in 5" :key="i" class="pt-skel" style="height:84px;border-radius:14px"></div>
                    </div>
                </div>
                <!-- Générique : profil, RGPD, billing, etc. -->
                <div v-else class="mx-auto" style="max-width:1100px;padding:2.5rem 2rem">
                    <div class="pt-skel" style="width:200px;height:34px;border-radius:8px;margin-bottom:10px"></div>
                    <div class="pt-skel" style="width:320px;height:13px;border-radius:6px;margin-bottom:2rem"></div>
                    <div class="pt-skel" style="height:420px;border-radius:14px"></div>
                </div>
            </template>
            <Transition v-else name="pt-page" appear>
                <div :key="$page.url" class="mx-auto" style="max-width: 1100px; padding: 2.5rem 2rem">
                    <slot />
                </div>
            </Transition>
        </main>

        <!-- Toasts flash (bottom-right) -->
        <Teleport to="body">
            <div role="alert" aria-live="polite" aria-atomic="true" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;align-items:flex-end;pointer-events:none;">
                <Transition name="pt-toast">
                    <div v-if="$page.props.flash?.success"
                        style="background:var(--bg-surface);border:1px solid var(--border-light);border-left:3px solid var(--color-primary);padding:0.7rem 1rem;border-radius:10px;font-size:13px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:0.5rem;pointer-events:auto;box-shadow:0 4px 20px rgba(0,0,0,0.12);max-width:320px;">
                        <i class="ti ti-circle-check" style="color:var(--color-primary);font-size:17px;flex-shrink:0;"></i>
                        {{ $page.props.flash.success }}
                    </div>
                </Transition>
                <Transition name="pt-toast">
                    <div v-if="$page.props.flash?.error"
                        style="background:#FFF5F5;border:1px solid #FCA5A5;border-left:3px solid #EF4444;padding:0.7rem 1rem;border-radius:10px;font-size:13px;font-weight:600;color:#991B1B;display:flex;align-items:center;gap:0.5rem;pointer-events:auto;box-shadow:0 4px 20px rgba(0,0,0,0.12);max-width:320px;">
                        <i class="ti ti-circle-x" style="color:#EF4444;font-size:17px;flex-shrink:0;"></i>
                        {{ $page.props.flash.error }}
                    </div>
                </Transition>
                <Transition name="pt-toast">
                    <div v-if="$page.props.flash?.info"
                        style="background:var(--bg-surface);border:1px solid var(--border-light);border-left:3px solid #6366F1;padding:0.7rem 1rem;border-radius:10px;font-size:13px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:0.5rem;pointer-events:auto;box-shadow:0 4px 20px rgba(0,0,0,0.12);max-width:320px;">
                        <i class="ti ti-info-circle" style="color:#6366F1;font-size:17px;flex-shrink:0;"></i>
                        {{ $page.props.flash.info }}
                    </div>
                </Transition>
            </div>
        </Teleport>

        <!-- Drawer mobile (Teleport → body pour stacking context propre) -->
        <Teleport to="body">
            <!-- Backdrop flouté -->
            <Transition name="pt-backdrop">
                <div
                    v-if="user && mobileOpen"
                    class="cand-drawer-backdrop"
                    @click="mobileOpen = false"
                ></div>
            </Transition>

            <!-- Panneau latéral droit -->
            <Transition name="pt-drawer">
                <nav ref="mobileDrawer" v-if="user && mobileOpen" class="cand-drawer" aria-label="Menu navigation">

                    <!-- En-tête : avatar + bouton fermer -->
                    <div class="cand-drawer-header">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="position:relative;width:38px;height:38px;flex-shrink:0;">
                                <svg width="38" height="38" viewBox="0 0 30 30" aria-hidden="true">
                                    <polygon points="15,2 26,8.5 26,21.5 15,28 4,21.5 4,8.5" fill="var(--color-accent)" stroke="var(--color-primary)" stroke-width="1"/>
                                    <polygon points="15,5 23,9.5 23,20.5 15,25 7,20.5 7,9.5" fill="none" stroke="var(--color-primary)" stroke-width="0.4" opacity="0.4"/>
                                </svg>
                                <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--color-primary);text-transform:uppercase;user-select:none;">
                                    {{ user.name ? user.name.slice(0, 2) : '?' }}
                                </span>
                            </div>
                            <div>
                                <div style="font-size:14px;font-weight:600;color:var(--text-primary);font-family:var(--font-display);line-height:1.2;">{{ user.name }}</div>
                                <div style="font-size:10px;color:var(--color-primary);font-family:var(--font-data);letter-spacing:0.1em;text-transform:uppercase;margin-top:2px;">Niv. {{ level }} · {{ levelName }}</div>
                            </div>
                        </div>
                        <button class="cand-drawer-close" @click="mobileOpen = false" aria-label="Fermer le menu">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <div class="cand-drawer-divider"></div>

                    <!-- Liens de navigation principaux -->
                    <div class="cand-drawer-section">
                        <Link :href="route('tests.index')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/tests') }">
                            <i class="ti" :class="L.iconTests"></i>
                            <span>{{ L.navTests }}</span>
                        </Link>
                        <Link :href="route('grimoire.show')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/grimoire') }">
                            <i class="ti" :class="L.iconGrimoire"></i>
                            <span>{{ L.navGrimoire }}</span>
                        </Link>
                        <Link :href="route('history')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/history') }">
                            <i class="ti" :class="L.iconHistory"></i>
                            <span>{{ L.navHistory }}</span>
                        </Link>
                        <Link v-if="hasTreasure" :href="route('treasure.index')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/treasure') }"
                            style="position:relative;">
                            <i class="ti" :class="L.iconTreasure"></i>
                            <span>{{ L.navTreasure }}</span>
                            <span v-if="hasNewTreasure && !isActive('/treasure')" class="tresor-dot tresor-dot--drawer" aria-label="Nouveau contenu débloqué"></span>
                        </Link>
                    </div>

                    <div class="cand-drawer-divider"></div>

                    <!-- Choix du parcours visuel -->
                    <div class="cand-drawer-section">
                        <div class="cand-parcours cand-parcours--drawer">
                            <div class="cand-parcours-label">Mon parcours</div>
                            <div class="cand-parcours-toggle" role="group" aria-label="Choix du parcours">
                                <button type="button" :class="{ 'cand-parcours-opt--active': !isCorporate }" class="cand-parcours-opt" @click="setParcours('medieval')">Médiéval</button>
                                <button type="button" :class="{ 'cand-parcours-opt--active': isCorporate }" class="cand-parcours-opt" @click="setParcours('corporate')">Corporate</button>
                            </div>
                        </div>
                    </div>

                    <div class="cand-drawer-divider"></div>

                    <!-- Liens compte -->
                    <div class="cand-drawer-section">
                        <Link :href="route('profile.edit')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/profile') }">
                            <i class="ti ti-user-circle"></i>
                            <span>Mon identité</span>
                        </Link>
                        <!-- L'abonnement ne concerne que les professionnels -->
                        <Link v-if="$page.props.auth?.is_pro" :href="route('billing.manage')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/billing') }">
                            <i class="ti ti-credit-card"></i>
                            <span>Mon abonnement</span>
                        </Link>
                        <Link :href="route('contact')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/contact') }">
                            <i class="ti ti-help-circle"></i>
                            <span>Aide & contact</span>
                        </Link>
                        <Link :href="route('account.password.edit')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/account/password') }">
                            <i class="ti ti-key"></i>
                            <span>{{ L.password }}</span>
                        </Link>
                        <Link :href="route('gdpr.show')"
                            class="cand-drawer-link"
                            :class="{ 'cand-drawer-link--active': isActive('/gdpr') }">
                            <i class="ti ti-shield-lock"></i>
                            <span>Mes données & RGPD</span>
                        </Link>
                    </div>

                    <!-- Pied de drawer : Quitter la Quête -->
                    <div style="margin-top:auto;padding:1rem 1.25rem 1.5rem;">
                        <Link :href="route('logout')" method="post" as="button" class="cand-drawer-logout">
                            <i class="ti ti-door-exit"></i>
                            <span>{{ L.logout }}</span>
                        </Link>
                    </div>

                </nav>
            </Transition>
        </Teleport>

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
        <EasterEgg :show="showEasterEgg" @close="showEasterEgg = false" />

        <!-- Achievement toast (style Xbox, bottom-left) -->
        <Teleport to="body">
            <Transition name="pt-achievement">
                <div
                    v-if="showAchievement && achievementData"
                    style="position:fixed;bottom:1.5rem;left:1.5rem;z-index:9998;background:var(--color-accent);border:1px solid var(--border-strong);border-radius:12px;padding:12px 14px 12px 12px;display:flex;align-items:center;gap:12px;max-width:320px;pointer-events:none;"
                >
                    <div style="position:relative;flex-shrink:0;width:42px;height:42px;">
                        <svg width="42" height="42" viewBox="0 0 30 30" fill="none" aria-hidden="true">
                            <polygon points="15,2 26,8.5 26,21.5 15,28 4,21.5 4,8.5" fill="var(--color-primary)"/>
                            <polygon points="15,5 23,9.5 23,20.5 15,25 7,20.5 7,9.5" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="0.5"/>
                        </svg>
                        <span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--bg-base);font-weight:700;line-height:1;">✓</span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:9px;font-weight:600;color:var(--color-primary-light);text-transform:uppercase;letter-spacing:0.12em;margin-bottom:3px;font-family:var(--font-data);">{{ L.achievementKicker }}</div>
                        <div style="font-size:13px;font-weight:600;color:var(--bg-base);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:var(--font-display);">{{ achievementData.name }}</div>
                    </div>
                    <div style="flex-shrink:0;text-align:right;">
                        <div style="font-size:13px;font-weight:700;color:var(--color-primary-light);font-family:var(--font-data);line-height:1;">+{{ achievementData.xp }}</div>
                        <div style="font-size:8px;color:var(--text-ghost);letter-spacing:0.1em;font-family:var(--font-data);margin-top:1px;">{{ L.xpName.toUpperCase() }}</div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Level Up overlay (plein écran, cliquable pour fermer) -->
        <Teleport to="body">
            <Transition name="pt-levelup">
                <div
                    v-if="showLevelUp && levelUpData"
                    style="position:fixed;inset:0;z-index:10000;background:var(--overlay-bg);display:flex;align-items:center;justify-content:center;cursor:pointer;"
                    @click="showLevelUp = false"
                    role="dialog"
                    aria-label="Niveau supérieur atteint"
                >
                    <div class="pt-lvl-content" style="text-align:center;padding:2rem;max-width:420px;">
                        <div style="font-size:10px;font-weight:600;color:var(--overlay-accent);opacity:0.75;text-transform:uppercase;letter-spacing:0.24em;margin-bottom:0.75rem;font-family:var(--font-data);">{{ L.tagline }}</div>
                        <div style="font-size:clamp(2.5rem,8vw,4.5rem);font-weight:700;color:var(--overlay-text);letter-spacing:-0.04em;line-height:1;font-family:var(--font-data);margin-bottom:0.35rem;">NIVEAU {{ levelUpData.level }}</div>
                        <div style="font-size:1.3rem;font-weight:600;color:var(--overlay-accent);font-family:var(--font-display);margin-bottom:1.5rem;">{{ levelUpData.name }}</div>
                        <div style="position:relative;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1.5rem;">
                            <svg width="72" height="72" viewBox="0 0 30 30" fill="none" aria-hidden="true">
                                <polygon points="15,2 26,8.5 26,21.5 15,28 4,21.5 4,8.5" fill="none" stroke="var(--overlay-accent)" stroke-width="1.2"/>
                                <polygon points="15,5 23,9.5 23,20.5 15,25 7,20.5 7,9.5" fill="none" stroke="var(--overlay-accent)" stroke-width="0.4" opacity="0.35"/>
                            </svg>
                            <span style="position:absolute;font-size:22px;font-weight:700;color:var(--overlay-accent);font-family:var(--font-data);">{{ levelUpData.level }}</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem;">
                            <div style="flex:1;height:1px;background:var(--overlay-accent);opacity:0.2;"></div>
                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 0L9.6 6.4L16 8L9.6 9.6L8 16L6.4 9.6L0 8L6.4 6.4L8 0Z" fill="var(--overlay-accent)" opacity="0.45"/></svg>
                            <div style="flex:1;height:1px;background:var(--overlay-accent);opacity:0.2;"></div>
                        </div>
                        <div style="font-size:11px;color:var(--overlay-text);opacity:0.35;font-family:var(--font-data);letter-spacing:0.08em;">Cliquer pour continuer</div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
/* ── Skip link (accessibilité) ── */
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #1E3A5F;
    color: white;
    padding: 8px;
    z-index: 9999;
    text-decoration: none;
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 14px;
}
.skip-link:focus {
    top: 0;
}

/* ── Nav desktop ── */
.cand-nav-link:hover {
    color: var(--text-primary) !important;
    background: var(--bg-elevated) !important;
}
.cand-nav-link--active {
    color: var(--color-primary) !important;
    font-weight: 700 !important;
    background: rgba(166, 117, 32, 0.1) !important;
}
.cand-nav-link--active:hover {
    color: var(--color-primary) !important;
    background: rgba(166, 117, 32, 0.15) !important;
}

/* Avatar + nom cliquable */
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

/* ── Écu de rang ── */
.cand-rank {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 3px 12px 3px 4px;
    border: 1px solid var(--border-mid);
    border-radius: 999px;
    background: rgba(166, 117, 32, 0.06);
    margin-left: 4px;
}
.cand-rank-shield {
    position: relative;
    width: 32px;
    height: 32px;
    flex-shrink: 0;
}
.cand-rank-lvl {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-data);
    font-size: 12px;
    font-weight: 700;
    color: var(--color-primary);
    line-height: 1;
    user-select: none;
}
.cand-rank-txt {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}
.cand-rank-name {
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
}
.cand-rank-sub {
    font-family: var(--font-data);
    font-size: 9px;
    letter-spacing: 0.06em;
    color: var(--color-primary);
}

/* ── Gain d'XP flottant ── */
.xp-gain {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-family: var(--font-data);
    font-size: 12px;
    font-weight: 700;
    color: var(--color-primary-dark);
    text-shadow: 0 1px 2px rgba(240, 232, 212, 0.8);
    pointer-events: none;
    white-space: nowrap;
}
.pt-xpgain-enter-active {
    transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pt-xpgain-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}
.pt-xpgain-enter-from {
    opacity: 0;
    transform: translateY(2px) scale(0.85);
}
.pt-xpgain-leave-to {
    opacity: 0;
    transform: translateY(-14px);
}

/* Masquer l'écu de rang sur petits écrans étroits (avant le burger) */
@media (max-width: 960px) {
    .cand-rank-txt { display: none; }
}

/* ── Burger ── */
.cand-burger {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    width: 38px;
    height: 38px;
    padding: 8px;
    background: none;
    border: 1px solid var(--border-mid);
    border-radius: var(--r-sm);
    cursor: pointer;
    /* Supprime le délai de 300ms sur iOS Safari */
    touch-action: manipulation;
}

.cand-burger span {
    display: block;
    height: 2px;
    width: 100%;
    background: var(--color-primary);
    border-radius: 2px;
    transform-origin: center;
    transition: transform 0.25s cubic-bezier(0.32, 0.72, 0, 1), opacity 0.2s ease;
}

/* Animation burger → X */
.cand-burger--open span:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}
.cand-burger--open span:nth-child(2) {
    opacity: 0;
    transform: scaleX(0);
}
.cand-burger--open span:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
}

/* ── Drawer backdrop ── */
.cand-drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(30, 20, 5, 0.45);
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    z-index: 200;
}

/* ── Drawer panneau ── */
.cand-drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: min(320px, 86vw);
    background: var(--bg-base);
    border-left: 1px solid var(--glass-border);
    z-index: 201;
    display: flex;
    flex-direction: column;
    box-shadow: -12px 0 48px rgba(30, 20, 5, 0.18);
    overflow-y: auto;
}

.cand-drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.25rem;
    flex-shrink: 0;
}

.cand-drawer-close {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-elevated);
    border: 1px solid var(--border-mid);
    border-radius: var(--r-sm);
    cursor: pointer;
    color: var(--text-secondary);
    font-size: 18px;
    flex-shrink: 0;
    transition: background 0.15s, color 0.15s;
    touch-action: manipulation;
}
.cand-drawer-close:hover {
    background: var(--bg-surface);
    color: var(--text-primary);
}

.cand-drawer-divider {
    height: 1px;
    background: var(--glass-border);
    margin: 0 1.25rem;
    flex-shrink: 0;
}

.cand-drawer-section {
    display: flex;
    flex-direction: column;
    padding: 0.6rem 1rem;
    gap: 2px;
    flex-shrink: 0;
}

.cand-drawer-link {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 500;
    color: var(--text-secondary);
    text-decoration: none;
    padding: 11px 12px;
    border-radius: var(--r);
    background: none;
    border: none;
    cursor: pointer;
    width: 100%;
    transition: color 0.15s, background 0.15s;
    touch-action: manipulation;
}
.cand-drawer-link i {
    font-size: 19px;
    width: 22px;
    text-align: center;
    flex-shrink: 0;
    opacity: 0.65;
    transition: opacity 0.15s;
}
.cand-drawer-link:hover {
    color: var(--text-primary);
    background: var(--bg-elevated);
}
.cand-drawer-link:hover i {
    opacity: 1;
}
.cand-drawer-link--active {
    color: var(--color-primary) !important;
    font-weight: 700 !important;
    background: rgba(166, 117, 32, 0.1) !important;
}
.cand-drawer-link--active i {
    opacity: 1 !important;
}
.cand-drawer-link--active:hover {
    background: rgba(166, 117, 32, 0.15) !important;
}

.cand-drawer-logout {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 11px 14px;
    background: rgba(176, 48, 32, 0.07);
    border: 1px solid rgba(176, 48, 32, 0.18);
    border-radius: var(--r);
    font-family: var(--font-display);
    font-size: 14px;
    font-weight: 600;
    color: var(--color-danger, #B03020);
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s;
    text-align: left;
}
.cand-drawer-logout i {
    font-size: 18px;
    flex-shrink: 0;
}
.cand-drawer-logout:hover {
    background: rgba(176, 48, 32, 0.13);
}

/* ── Transitions drawer ── */
.pt-drawer-enter-active,
.pt-drawer-leave-active {
    transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
}
.pt-drawer-enter-from,
.pt-drawer-leave-to {
    transform: translateX(100%);
}

/* Backdrop fade */
.pt-backdrop-enter-active {
    transition: opacity 0.25s ease;
}
.pt-backdrop-leave-active {
    transition: opacity 0.25s ease;
    /* Rendre le backdrop non-interactif dès qu'il commence à se fermer,
       sinon l'overlay position:fixed;inset:0 bloque tous les touchs pendant 250ms */
    pointer-events: none;
}
.pt-backdrop-enter-from,
.pt-backdrop-leave-to {
    opacity: 0;
}

/* ── Page fade-in ── */
.pt-page-enter-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.pt-page-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

/* ── Toast slide-in ── */
.pt-toast-enter-active { transition: opacity 0.22s ease, transform 0.22s ease; }
.pt-toast-enter-from   { opacity: 0; transform: translateX(16px); }
.pt-toast-leave-active { transition: opacity 0.18s ease; }
.pt-toast-leave-to     { opacity: 0; }

/* ── Skeleton shimmer ── */
@keyframes pt-shimmer {
    0%   { background-position: -200% 0 }
    100% { background-position:  200% 0 }
}
.pt-skel {
    background: linear-gradient(
        90deg,
        var(--bg-elevated) 25%,
        var(--bg-surface)  50%,
        var(--bg-elevated) 75%
    );
    background-size: 200% 100%;
    animation: pt-shimmer 1.6s ease-in-out infinite;
}

/* ── User dropdown menu ── */
.cand-user-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 220px;
    background: var(--bg-surface);
    border: 1px solid var(--glass-border);
    border-radius: var(--r);
    box-shadow: 0 8px 32px rgba(30, 20, 5, 0.18), 0 2px 8px rgba(30, 20, 5, 0.08);
    z-index: 200;
    overflow: hidden;
}

.cand-user-menu-header {
    padding: 12px 14px 10px;
}

.cand-user-menu-divider {
    height: 1px;
    background: var(--glass-border);
    margin: 0;
}

.cand-user-menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    text-decoration: none;
    background: none;
    border: none;
    width: 100%;
    cursor: pointer;
    text-align: left;
    transition: background 0.12s, color 0.12s;
}
.cand-user-menu-item i {
    font-size: 16px;
    width: 18px;
    text-align: center;
    flex-shrink: 0;
    opacity: 0.6;
    transition: opacity 0.12s;
}
.cand-user-menu-item:hover {
    background: var(--bg-elevated);
    color: var(--text-primary);
}
.cand-user-menu-item:hover i { opacity: 1; }

.cand-user-menu-item-sub {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 400;
    margin-top: 1px;
    font-family: var(--font-body);
}

.cand-user-menu-item--muted {
    font-size: 12px;
    color: var(--text-muted);
}

.cand-user-menu-item--danger {
    color: var(--color-danger, #B03020);
}
.cand-user-menu-item--danger i { color: var(--color-danger, #B03020); }
.cand-user-menu-item--danger:hover {
    background: rgba(176, 48, 32, 0.07);
    color: var(--color-danger, #B03020);
}

/* ── Sélecteur de parcours (Quête / Executive) ── */
.cand-parcours {
    padding: 10px 14px;
}
.cand-parcours--drawer {
    padding: 4px 12px 8px;
}
.cand-parcours-label {
    font-family: var(--font-data);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 7px;
}
.cand-parcours-toggle {
    display: flex;
    gap: 0;
    border: 1px solid var(--border-mid);
    border-radius: var(--r);
    overflow: hidden;
}
.cand-parcours-opt {
    flex: 1;
    padding: 7px 8px;
    font-family: var(--font-display);
    font-size: 12px;
    font-weight: 500;
    color: var(--text-secondary);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    touch-action: manipulation;
}
.cand-parcours-opt + .cand-parcours-opt {
    border-left: 1px solid var(--border-mid);
}
.cand-parcours-opt:hover {
    background: var(--bg-elevated);
    color: var(--text-primary);
}
.cand-parcours-opt--active {
    background: var(--color-primary);
    color: var(--pt-white, #fff);
    font-weight: 600;
}
.cand-parcours-opt--active:hover {
    background: var(--color-primary-dark);
    color: var(--pt-white, #fff);
}

/* Dropdown transition */
.pt-usermenu-enter-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.pt-usermenu-leave-active { transition: opacity 0.1s ease, transform 0.1s ease; }
.pt-usermenu-enter-from, .pt-usermenu-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.97);
}

/* ── Responsive : burger visible < 768px, nav desktop masquée ── */
@media (max-width: 768px) {
    .cand-nav-desktop {
        display: none;
    }
    .cand-burger {
        display: flex;
    }
}

/* ── Achievement toast transitions ── */
.pt-achievement-enter-active {
    transition: opacity 0.32s ease, transform 0.38s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pt-achievement-enter-from {
    opacity: 0;
    transform: translateX(-28px) scale(0.9);
}
.pt-achievement-leave-active {
    transition: opacity 0.22s ease, transform 0.22s ease;
}
.pt-achievement-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}

/* ── Level Up overlay transitions ── */
.pt-levelup-enter-active {
    transition: opacity 0.38s ease;
}
.pt-levelup-leave-active {
    transition: opacity 0.35s ease;
    /* Même correctif : l'overlay inset:0 ne doit plus bloquer les touchs
       dès que l'animation de fermeture démarre */
    pointer-events: none;
}
.pt-levelup-enter-from,
.pt-levelup-leave-to {
    opacity: 0;
}

/* ── Level Up content : animation de pulsation douce ── */
@keyframes pt-lvlpulse {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.025); }
}
.pt-lvl-content {
    animation: pt-lvlpulse 2.2s ease-in-out infinite;
}

/* ── Badge "nouveau trésor" ── */
@keyframes tresor-pulse {
    0%, 100% { transform: scale(1);   box-shadow: 0 0 0 0 rgba(166,117,32,0.6); }
    50%       { transform: scale(1.2); box-shadow: 0 0 0 5px rgba(166,117,32,0); }
}
.tresor-dot {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--color-primary);
    border: 1.5px solid var(--bg-base);
    animation: tresor-pulse 1.8s ease-in-out infinite;
    pointer-events: none;
}
/* Version drawer : positionnée à droite du texte */
.tresor-dot--drawer {
    position: absolute;
    top: 50%;
    right: 14px;
    transform: translateY(-50%);
    width: 8px;
    height: 8px;
}
</style>
