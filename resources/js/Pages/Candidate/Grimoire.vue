<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    grimoire:   Object,   // { synthesis, voies[], tests_included[], status, generated_at, disclaimer }
    tests:      Array,    // [{ attempt_id, name }]
    ai_pending: Boolean,
    is_empty:   Boolean,
})

const voies = computed(() => props.grimoire?.voies ?? [])

// ── Polling pendant la génération ────────────────────────────────────────
let timer = null
onMounted(() => {
    if (props.ai_pending) {
        timer = setInterval(async () => {
            try {
                const r = await fetch(route('grimoire.status'), { headers: { Accept: 'application/json' } })
                const data = await r.json()
                if (data.ready || data.failed) {
                    clearInterval(timer)
                    router.reload({ only: ['grimoire', 'ai_pending'] })
                }
            } catch (e) { /* on retentera au prochain tick */ }
        }, 5000)
    }
})
onUnmounted(() => { if (timer) clearInterval(timer) })

const refreshing = ref(false)
function regenerate() {
    refreshing.value = true
    router.post(route('grimoire.refresh'), {}, {
        preserveScroll: true,
        onFinish: () => { refreshing.value = false },
    })
}

function fitClass(score) {
    if (score >= 80) return 'voie-fit-high'
    if (score >= 60) return 'voie-fit-mid'
    return 'voie-fit-low'
}
</script>

<template>
    <CandidateLayout>
        <Head title="Le Grimoire" />

        <div class="grim-shell">

            <!-- ═══ ÉTAT VIDE : aucun test passé ════════════════════════════ -->
            <div v-if="is_empty" class="grim-empty">
                <div class="grim-deco-line"></div>
                <h1 class="grim-title">Le Grimoire</h1>
                <p class="grim-empty-text">
                    Ton Grimoire se remplira au fil de tes épreuves. Passe un premier test
                    pour qu'il commence à relire ton profil.
                </p>
                <Link :href="route('tests.index')" class="ac-btn-primary">Aller à l'Armurerie</Link>
            </div>

            <!-- ═══ ÉTAT EN COURS : génération de la relecture ══════════════ -->
            <div v-else-if="ai_pending" class="grim-pending">
                <div class="grim-deco-line"></div>
                <div class="grim-pulse-dots"><span></span><span></span><span></span></div>
                <h1 class="grim-title">L'oracle relit tes épreuves…</h1>
                <p class="grim-pending-sub">
                    Croisement de tes {{ tests.length }} test{{ tests.length > 1 ? 's' : '' }} · 1 à 2 minutes
                </p>
                <div class="grim-deco-line" style="margin-top: 2rem;"></div>
            </div>

            <!-- ═══ ÉTAT PRÊT : synthèse globale + Voies ════════════════════ -->
            <div v-else class="grim-content">

                <header class="grim-header">
                    <span class="grim-badge">Relecture globale</span>
                    <h1 class="grim-title">Le Grimoire</h1>
                    <p class="grim-sub">
                        Ce que révèle le croisement de
                        <strong>{{ tests.length }}</strong> de tes épreuves.
                    </p>
                    <div class="grim-tests-chips">
                        <span v-for="t in tests" :key="t.attempt_id" class="grim-chip">{{ t.name }}</span>
                    </div>
                </header>

                <!-- Message si l'IA a échoué -->
                <div v-if="grimoire?.status === 'failed'" class="grim-alert">
                    {{ grimoire.synthesis }}
                </div>

                <!-- Synthèse transversale -->
                <section v-else class="grim-synthesis">
                    <h2 class="grim-section-title">Le fil conducteur</h2>
                    <p v-for="(para, i) in (grimoire?.synthesis || '').split('\n').filter(p => p.trim())"
                       :key="i" class="grim-para">{{ para }}</p>
                </section>

                <!-- Résumé par test passé + téléchargement PDF individuel -->
                <section v-if="tests.length" class="grim-tests">
                    <h2 class="grim-section-title">Tes épreuves relues</h2>
                    <p class="grim-voies-intro">
                        Le résumé de chacun de tes tests. Télécharge le détail complet en PDF.
                    </p>

                    <div class="grim-tests-list">
                        <article v-for="t in tests" :key="t.attempt_id" class="grim-test-card">
                            <div class="grim-test-main">
                                <h3 class="grim-test-name">{{ t.name }}</h3>
                                <p v-if="t.summary" class="grim-test-summary">{{ t.summary }}</p>
                                <p v-else class="grim-test-summary grim-test-pending">
                                    Synthèse en cours de génération…
                                </p>
                            </div>
                            <div class="grim-test-actions">
                                <Link :href="t.results_url" class="grim-test-link">Voir le détail</Link>
                                <a v-if="t.pdf_url" :href="t.pdf_url" class="grim-test-pdf">
                                    Télécharger le PDF
                                </a>
                            </div>
                        </article>
                    </div>
                </section>

                <!-- Les Voies Possibles consolidées -->
                <section v-if="voies.length" class="grim-voies">
                    <h2 class="grim-section-title">Tes Voies Possibles</h2>
                    <p class="grim-voies-intro">
                        {{ voies.length }} pistes construites en croisant l'ensemble de tes résultats.
                    </p>

                    <div class="grim-voies-grid">
                        <article v-for="(v, i) in voies" :key="i" class="grim-voie-card">
                            <div class="grim-voie-head">
                                <span class="grim-voie-rank">{{ i + 1 }}</span>
                                <span v-if="v.fit_score != null" class="grim-voie-fit" :class="fitClass(v.fit_score)">
                                    {{ v.fit_score }}%
                                </span>
                            </div>
                            <h3 class="grim-voie-titre">{{ v.titre }}</h3>
                            <p v-if="v.secteur" class="grim-voie-secteur">{{ v.secteur }}</p>
                            <p v-if="v.pourquoi" class="grim-voie-why">{{ v.pourquoi }}</p>

                            <div v-if="v.appui_tests?.length" class="grim-voie-appui">
                                <span class="grim-voie-appui-label">Appuyé par :</span>
                                <span v-for="(t, j) in v.appui_tests" :key="j" class="grim-appui-tag">{{ t }}</span>
                            </div>

                            <p v-if="v.prochaine_etape" class="grim-voie-next">
                                <span class="grim-voie-next-label">Prochaine étape</span>
                                {{ v.prochaine_etape }}
                            </p>
                        </article>
                    </div>
                </section>

                <!-- Pied : régénérer + disclaimer IA -->
                <footer class="grim-footer">
                    <div class="grim-actions">
                        <a v-if="grimoire?.status === 'ready'" :href="route('grimoire.pdf')" class="ac-btn-primary">
                            Télécharger en PDF
                        </a>
                        <button class="ac-btn-secondary" :disabled="refreshing" @click="regenerate">
                            {{ refreshing ? 'Relecture en cours…' : 'Régénérer le Grimoire' }}
                        </button>
                    </div>
                    <p v-if="grimoire?.disclaimer" class="grim-disclaimer">
                        {{ grimoire.disclaimer.disclaimer_text }}
                    </p>
                </footer>
            </div>
        </div>
    </CandidateLayout>
</template>

<style scoped>
.grim-shell { max-width: 1080px; margin: 0 auto; padding: 2.5rem 1.25rem 4rem; }

.grim-deco-line { height: 1px; background: var(--border-mid, #e5e7eb); max-width: 220px; margin: 0 auto; }
.grim-title { font-family: var(--font-display, serif); font-size: 2rem; font-weight: 700; text-align: center; margin: 1rem 0 .5rem; color: var(--text-primary, #0f172a); }
.grim-sub { text-align: center; color: var(--text-secondary, #475569); margin-bottom: 1rem; }

/* Vide / pending */
.grim-empty, .grim-pending { text-align: center; padding: 5rem 1rem; }
.grim-empty-text, .grim-pending-sub { color: var(--text-secondary, #475569); max-width: 460px; margin: 1rem auto 1.5rem; }
.grim-pulse-dots { display: flex; gap: 8px; justify-content: center; margin: 1.5rem 0; }
.grim-pulse-dots span { width: 9px; height: 9px; border-radius: 50%; background: var(--color-accent, #6366f1); animation: grimPulse 1.2s infinite ease-in-out; }
.grim-pulse-dots span:nth-child(2) { animation-delay: .2s; }
.grim-pulse-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes grimPulse { 0%, 80%, 100% { opacity: .25; transform: scale(.8); } 40% { opacity: 1; transform: scale(1); } }

/* En-tête */
.grim-header { text-align: center; margin-bottom: 2.5rem; }
.grim-badge { display: inline-block; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: var(--color-accent, #6366f1); border: 1px solid var(--border-mid, #e5e7eb); border-radius: 999px; padding: 4px 12px; }
.grim-tests-chips { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-top: .75rem; }
.grim-chip { font-size: 12px; background: var(--surface-2, #f1f5f9); color: var(--text-secondary, #475569); padding: 3px 10px; border-radius: 999px; }

/* Synthèse */
.grim-section-title { font-family: var(--font-display, serif); font-size: 1.35rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-primary, #0f172a); }
.grim-synthesis { max-width: 720px; margin: 0 auto 3rem; }
.grim-para { line-height: 1.75; color: var(--text-primary, #1e293b); margin-bottom: 1rem; }
.grim-alert { max-width: 720px; margin: 0 auto 2rem; padding: 1rem 1.25rem; background: #fef3c7; border: 1px solid #fde68a; border-radius: 10px; color: #92400e; }

/* Voies */
.grim-voies-intro { color: var(--text-secondary, #475569); margin-bottom: 1.5rem; }
.grim-voies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
.grim-voie-card { border: 1px solid var(--border-mid, #e5e7eb); border-radius: 14px; padding: 1.1rem 1.2rem; background: var(--surface-1, #fff); transition: box-shadow .15s, transform .15s; }
.grim-voie-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.07); transform: translateY(-2px); }
.grim-voie-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .5rem; }
.grim-voie-rank { font-family: var(--font-display, serif); font-weight: 700; color: var(--text-muted, #94a3b8); }
.grim-voie-fit { font-size: 12px; font-weight: 700; padding: 2px 9px; border-radius: 999px; }
.voie-fit-high { background: #dcfce7; color: #166534; }
.voie-fit-mid  { background: #e0e7ff; color: #3730a3; }
.voie-fit-low  { background: #f1f5f9; color: #475569; }
.grim-voie-titre { font-size: 1.05rem; font-weight: 700; color: var(--text-primary, #0f172a); margin-bottom: .15rem; }
.grim-voie-secteur { font-size: 12px; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: .04em; margin-bottom: .6rem; }
.grim-voie-why { font-size: 14px; line-height: 1.6; color: var(--text-secondary, #475569); margin-bottom: .75rem; }
.grim-voie-appui { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; margin-bottom: .75rem; }
.grim-voie-appui-label { font-size: 11px; color: var(--text-muted, #94a3b8); }
.grim-appui-tag { font-size: 11px; background: var(--surface-2, #f1f5f9); color: var(--color-accent, #4f46e5); padding: 2px 8px; border-radius: 6px; }
.grim-voie-next { font-size: 13px; line-height: 1.5; color: var(--text-primary, #1e293b); border-top: 1px dashed var(--border-mid, #e5e7eb); padding-top: .6rem; }
.grim-voie-next-label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--color-accent, #6366f1); margin-bottom: .2rem; }

/* Résumé par test */
.grim-tests { margin-bottom: 3rem; }
.grim-tests-list { display: flex; flex-direction: column; gap: .75rem; }
.grim-test-card { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem; border: 1px solid var(--border-mid, #e5e7eb); border-radius: 14px; padding: 1.1rem 1.25rem; background: var(--surface-1, #fff); }
.grim-test-main { flex: 1 1 320px; min-width: 0; }
.grim-test-name { font-size: 1.05rem; font-weight: 700; color: var(--text-primary, #0f172a); margin-bottom: .4rem; }
.grim-test-summary { font-size: 14px; line-height: 1.6; color: var(--text-secondary, #475569); }
.grim-test-pending { font-style: italic; color: var(--text-muted, #94a3b8); }
.grim-test-actions { display: flex; flex-direction: column; gap: .5rem; flex: 0 0 auto; align-items: stretch; }
.grim-test-link { font-size: 13px; text-align: center; color: var(--color-accent, #4f46e5); text-decoration: none; padding: 6px 14px; border: 1px solid var(--border-mid, #e5e7eb); border-radius: 8px; transition: background .15s; }
.grim-test-link:hover { background: var(--surface-2, #f1f5f9); }
.grim-test-pdf { font-size: 13px; text-align: center; color: #fff; background: var(--color-accent, #4f46e5); text-decoration: none; padding: 7px 14px; border-radius: 8px; transition: filter .15s; }
.grim-test-pdf:hover { filter: brightness(1.08); }

/* Footer */
.grim-footer { text-align: center; margin-top: 3rem; }
.grim-actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; }
.grim-disclaimer { font-size: 12px; color: var(--text-muted, #94a3b8); max-width: 560px; margin: 1rem auto 0; line-height: 1.5; }
</style>
