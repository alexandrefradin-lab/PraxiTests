<script setup>
/**
 * PathPlanPanel — accordéon affichant le plan d'action IA pour une piste métier.
 *
 * Cycle de vie :
 *   1. Premier clic "Plan d'action" → vérifie si un plan existe déjà (GET)
 *   2. Si non → génère via POST (appel Haiku ~2-4s)
 *   3. Affiche : premier pas → étapes numérotées → ressources → conseil
 *
 * Props :
 *   careerPathId  {number}  ID de la CareerPath en base (pour pistes Grimoire)
 *   careerPathSlug {string} Slug de la piste (fallback si pas d'id BDD)
 *   open          {boolean} Contrôlé par le parent (PathCard)
 */
import { ref, watch } from 'vue'

const props = defineProps({
    careerPathId:   { type: Number, default: null },
    careerPathSlug: { type: String, required: true },
    open:           { type: Boolean, default: false },
})

const plan      = ref(null)   // { premier_pas, etapes[], ressources[], conseil }
const loading   = ref(false)
const error     = ref(null)
const generated = ref(false)  // true dès qu'on a lancé au moins un fetch

const typeIcons = {
    financement: '💶',
    emploi:      '🔍',
    reseau:      '🤝',
    formation:   '🎓',
    info:        'ℹ️',
}

async function fetchOrGenerate() {
    if (generated.value) return   // déjà tenté, ne pas re-appeler
    generated.value = true
    loading.value   = true
    error.value     = null

    const slug = props.careerPathSlug

    try {
        // 1. Vérifier le cache
        const checkRes  = await fetch(`/career-path/${slug}/plan`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        const checkData = await checkRes.json()

        if (checkData.plan) {
            plan.value    = checkData.plan
            loading.value = false
            return
        }

        // 2. Générer
        const genRes  = await fetch(`/career-path/${slug}/plan`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type':     'application/json',
            },
        })
        const genData = await genRes.json()

        if (!genRes.ok || genData.error) {
            throw new Error(genData.error ?? 'Erreur lors de la génération')
        }

        plan.value = genData.plan
    } catch (e) {
        error.value = e.message ?? 'Erreur inconnue'
    } finally {
        loading.value = false
    }
}

// Déclenche le fetch à la première ouverture
watch(() => props.open, (isOpen) => {
    if (isOpen && !generated.value) {
        fetchOrGenerate()
    }
}, { immediate: true })
</script>

<template>
    <div v-if="open" class="ppp">
        <!-- Loader -->
        <div v-if="loading" class="ppp__loader">
            <span class="ppp__spinner" aria-hidden="true"></span>
            <span>Génération du plan en cours…</span>
        </div>

        <!-- Erreur -->
        <p v-else-if="error" class="ppp__error">{{ error }}</p>

        <!-- Plan -->
        <template v-else-if="plan">
            <!-- Premier pas -->
            <div class="ppp__premier">
                <span class="ppp__badge">⚡ Première action</span>
                <p class="ppp__premier-text">{{ plan.premier_pas }}</p>
            </div>

            <!-- Étapes -->
            <ol class="ppp__steps" v-if="plan.etapes?.length">
                <li v-for="etape in plan.etapes" :key="etape.num" class="ppp__step">
                    <div class="ppp__step-head">
                        <span class="ppp__step-num">{{ etape.num }}</span>
                        <strong class="ppp__step-title">{{ etape.titre }}</strong>
                        <span class="ppp__step-duree">{{ etape.duree }}</span>
                    </div>
                    <p class="ppp__step-desc">{{ etape.description }}</p>
                </li>
            </ol>

            <!-- Ressources -->
            <div class="ppp__resources" v-if="plan.ressources?.length">
                <p class="ppp__section-label">Ressources utiles</p>
                <ul class="ppp__res-list">
                    <li v-for="(res, i) in plan.ressources" :key="i" class="ppp__res-item">
                        <span>{{ typeIcons[res.type] ?? '🔗' }}</span>
                        <a :href="res.url" target="_blank" rel="noopener noreferrer" class="ppp__res-link">
                            {{ res.titre }}
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Conseil personnalisé -->
            <div class="ppp__conseil" v-if="plan.conseil">
                <p class="ppp__section-label">Conseil personnalisé</p>
                <p class="ppp__conseil-text">{{ plan.conseil }}</p>
            </div>
        </template>
    </div>
</template>

<style scoped>
.ppp {
    margin-top: .75rem;
    padding-top: .75rem;
    border-top: .5px solid var(--pt-border, rgba(166,117,32,0.2));
    display: flex;
    flex-direction: column;
    gap: .9rem;
}

/* Loader */
.ppp__loader {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: 12.5px;
    color: var(--pt-text-muted, #6B5A3E);
}
.ppp__spinner {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid var(--pt-gold-border, rgba(166,117,32,0.3));
    border-top-color: var(--pt-gold-hover, #A67520);
    border-radius: 50%;
    animation: ppp-spin .8s linear infinite;
    flex-shrink: 0;
}
@keyframes ppp-spin { to { transform: rotate(360deg); } }

.ppp__error {
    font-size: 12.5px;
    color: #b94040;
    margin: 0;
}

/* Premier pas */
.ppp__premier {
    background: var(--pt-gold-pale, #F5E6C8);
    border: .5px solid var(--pt-gold-border, rgba(166,117,32,0.3));
    border-radius: 8px;
    padding: .65rem .85rem;
}
.ppp__badge {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--pt-gold-hover, #A67520);
    display: block;
    margin-bottom: .25rem;
}
.ppp__premier-text {
    font-size: 13px;
    color: var(--pt-navy, #2A1E08);
    margin: 0;
    line-height: 1.5;
}

/* Étapes */
.ppp__steps {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: .7rem;
}
.ppp__step {
    display: flex;
    flex-direction: column;
    gap: .2rem;
}
.ppp__step-head {
    display: flex;
    align-items: center;
    gap: .5rem;
}
.ppp__step-num {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background: var(--pt-navy, #2A1E08);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 50%;
    flex-shrink: 0;
    font-family: 'Space Mono', monospace;
}
.ppp__step-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--pt-navy, #2A1E08);
    flex: 1;
}
.ppp__step-duree {
    font-size: 11px;
    color: var(--pt-text-light, #8C7A5E);
    white-space: nowrap;
}
.ppp__step-desc {
    font-size: 12.5px;
    color: var(--pt-text-muted, #6B5A3E);
    margin: 0 0 0 30px;
    line-height: 1.5;
}

/* Ressources */
.ppp__section-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--pt-text-light, #8C7A5E);
    margin: 0 0 .4rem;
}
.ppp__res-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.ppp__res-item {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: 12.5px;
}
.ppp__res-link {
    color: var(--pt-gold-hover, #A67520);
    text-decoration: underline;
    text-underline-offset: 2px;
}
.ppp__res-link:hover { color: var(--pt-navy, #2A1E08); }

/* Conseil */
.ppp__conseil {}
.ppp__conseil-text {
    font-size: 12.5px;
    color: var(--pt-text, #2A1E08);
    font-style: italic;
    margin: 0;
    line-height: 1.55;
    border-left: 2px solid var(--pt-gold-border, rgba(166,117,32,0.35));
    padding-left: .65rem;
}
</style>
