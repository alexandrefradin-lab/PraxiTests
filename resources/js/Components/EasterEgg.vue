<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useParcours } from '@/composables/useParcours'

const { isCorporate, L } = useParcours()

// Chaque secret a sa mise en scène, déclinée dans les deux parcours (le
// registre « quête » et le registre cabinet de conseil). Les Éclats et le
// badge, eux, viennent du serveur — rien ici n'est une source de vérité.
const EGGS = {
    konami: {
        medieval: {
            seal: '👁',
            kicker: '— Séquence ancienne reconnue —',
            title: "L'Oracle s'éveille",
            paragraphs: [
                "Depuis les premiers jours de PraxiQuest, une clé ancienne sommeille dans l'ombre. Ceux qui la connaissent accèdent à une vérité que peu voient : <em>la curiosité est déjà une forme d'intelligence.</em>",
                "Tu viens de rejoindre l'ordre des Éveillés.",
            ],
            cta: 'Continuer mon voyage',
            badge: 'Éveillé',
            againTitle: 'Déjà Éveillé',
            againText: "Tu portes déjà la marque des initiés. Le secret ne peut être révélé qu'une seule fois.",
        },
        corporate: {
            seal: '◈',
            kicker: '— Séquence non documentée —',
            title: 'Fonction non répertoriée',
            paragraphs: [
                "Une combinaison de touches héritée des premières versions de PraxiQuest ouvre cette fenêtre. Elle n'est mentionnée nulle part dans l'interface.",
                "<em>La curiosité est une compétence professionnelle.</em> Elle ne figure sur aucun référentiel, et elle explique pourtant une bonne part des trajectoires.",
            ],
            cta: 'Reprendre',
            badge: 'Perspicacité',
            againTitle: 'Déjà découverte',
            againText: "Cette fonction vous est déjà attribuée. Elle ne peut être débloquée qu'une seule fois.",
        },
    },
    faux_bouton: {
        medieval: {
            seal: '🧭',
            kicker: '— Chemin non répertorié —',
            title: 'Nulle part',
            paragraphs: [
                "Tu as suivi un lien qui annonçait ne mener à rien. C'était vrai, et tu y es allé quand même.",
                "<em>Se perdre volontairement est une compétence.</em> Elle n'apparaît sur aucun bilan, mais c'est elle qui fait bifurquer les parcours.",
            ],
            cta: 'Reprendre un chemin',
            badge: 'Égaré',
            againTitle: 'Déjà venu ici',
            againText: "Tu connais déjà ce non-lieu. On ne se perd pas deux fois au même endroit.",
        },
        corporate: {
            seal: '◇',
            kicker: '— Page non référencée —',
            title: 'Nulle part',
            paragraphs: [
                "Vous avez suivi un lien qui annonçait ne mener à rien. C'était exact, et vous y êtes allé quand même.",
                "<em>L'exploration sans objectif défini reste un mode de recherche légitime.</em> Elle produit rarement ce qu'on attendait, souvent ce dont on avait besoin.",
            ],
            cta: 'Revenir',
            badge: 'Exploration',
            againTitle: 'Déjà consultée',
            againText: "Vous connaissez déjà cette page. On ne s'égare pas deux fois au même endroit.",
        },
    },
    grimoire_inverse: {
        medieval: {
            seal: '✒',
            kicker: '— Lecture à rebours —',
            title: 'Le Scribe',
            paragraphs: [
                "Tu as remonté le Grimoire à contre-sens, sans jamais toucher la souris. C'est ainsi que travaillaient les copistes : à l'envers, pour relire ce que l'auteur croyait avoir écrit.",
                "<em>Une page cachée s'ouvre dans ton sommaire.</em>",
            ],
            cta: 'Lire les marginalia',
            badge: 'Scribe',
            againTitle: 'Déjà Scribe',
            againText: "Les marginalia te sont déjà ouvertes. Elles t'attendent dans le sommaire du Grimoire.",
        },
        corporate: {
            seal: '✎',
            kicker: '— Lecture à rebours —',
            title: 'Relecture inversée',
            paragraphs: [
                "Vous avez remonté votre dossier de synthèse section par section, en sens inverse, sans utiliser la souris. C'est la méthode des correcteurs professionnels : à l'envers, on lit ce qui est écrit plutôt que ce qu'on croit avoir écrit.",
                "<em>Une section supplémentaire s'ouvre dans votre sommaire.</em>",
            ],
            cta: 'Consulter les notes',
            badge: 'Relecture critique',
            againTitle: 'Déjà débloquée',
            againText: "Les notes de marge vous sont déjà accessibles depuis le sommaire de votre dossier.",
        },
    },
    encre_invisible: {
        medieval: {
            seal: '🔍',
            kicker: '— Encre révélée —',
            title: "L'Encre Invisible",
            paragraphs: [
                "Une phrase dormait sous la synthèse, écrite dans la couleur du parchemin. Il fallait passer la main dessus pour qu'elle apparaisse.",
                "<em>Les copistes cachaient leurs vraies pensées entre les lignes.</em> Tu viens de lire l'une d'elles.",
            ],
            cta: 'Refermer le Grimoire',
            badge: 'Déchiffreur',
            againTitle: 'Encre déjà lue',
            againText: "Tu as déjà révélé cette phrase. Une encre invisible ne se découvre qu'une fois.",
        },
        corporate: {
            seal: '◱',
            kicker: '— Note dissimulée —',
            title: 'Lecture attentive',
            paragraphs: [
                "Une phrase était écrite sous la synthèse, dans la couleur du fond. Il fallait sélectionner le texte pour la faire apparaître.",
                "<em>Ce qui compte est rarement mis en évidence.</em> Vous venez d'en faire l'expérience.",
            ],
            cta: 'Revenir au dossier',
            badge: 'Lecture attentive',
            againTitle: 'Note déjà lue',
            againText: "Vous avez déjà révélé cette note. Elle ne se découvre qu'une fois.",
        },
    },
    doute: {
        medieval: {
            seal: '⚖',
            kicker: '— Balance intérieure —',
            title: 'Le Doute',
            paragraphs: [
                "Sur une même question, tu as changé d'avis cinq fois. L'épreuve ne t'a rien dit sur le moment : t'avertir aurait faussé tes réponses.",
                "<em>Ce n'est pas de l'indécision, c'est de l'honnêteté intellectuelle.</em> Les gens qui ne reviennent jamais sur leur premier réflexe se trompent tout aussi souvent — ils le savent moins.",
            ],
            cta: 'Voir mes résultats',
            badge: 'Nuance',
            againTitle: 'Déjà reconnu',
            againText: "Cette qualité t'est déjà attribuée. Elle n'avait pas besoin d'être comptée deux fois.",
        },
        corporate: {
            seal: '◐',
            kicker: '— Révision de jugement —',
            title: 'Nuance',
            paragraphs: [
                "Sur une même question, vous avez révisé votre réponse cinq fois. L'évaluation ne l'a pas signalé sur le moment : vous en avertir aurait influencé vos réponses.",
                "<em>Réviser son jugement n'est pas de l'indécision.</em> C'est une compétence, et elle est rarement mesurée.",
            ],
            cta: 'Voir mes résultats',
            badge: 'Nuance',
            againTitle: 'Déjà reconnue',
            againText: "Cette distinction vous est déjà attribuée.",
        },
    },
}

const props = defineProps({
    show: { type: Boolean, default: false },
    slug: { type: String, default: 'konami' },
})
const emit = defineEmits(['close', 'claimed'])

const claimed = ref(false)
const loading = ref(false)
const eclats = ref(0)
const badgeName = ref('')
const alreadyClaimed = ref(false)
const failed = ref(false)
const failedStatus = ref(null)

const egg = computed(() => {
    const def = EGGS[props.slug] ?? EGGS.konami
    return isCorporate.value ? def.corporate : def.medieval
})

async function claim() {
    if (loading.value || claimed.value) return
    loading.value = true
    try {
        const res = await fetch(route('easter-egg.claim'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ slug: props.slug }),
        })
        // Une reponse d'erreur ne porte ni eclats ni badge_name : sans ce
        // garde-fou, le repli cote client affichait "+0 Eclats" et un nom de
        // badge inventé, donnant une fausse impression de succes alors que
        // rien n'etait enregistre.
        if (! res.ok) {
            console.warn('[easter-egg] claim refuse', props.slug, res.status)
            failed.value = true
            failedStatus.value = res.status
            return
        }
        const data = await res.json()
        if (data.already_claimed) {
            alreadyClaimed.value = true
        } else {
            eclats.value = data.eclats ?? 0
            // Le serveur résout déjà le libellé selon le parcours (colonnes
            // name/name_corporate) ; la copie locale n'est qu'un filet.
            badgeName.value = data.badge_name || egg.value.badge || ''
            claimed.value = true
            // Permet à la page hôte de révéler ce que le secret débloque.
            emit('claimed', props.slug)
        }
    } catch (e) {
        // Réseau ou réponse non-JSON : on ferme sans déranger, mais on trace.
        // Un échec muet ici a déjà masqué une route cassée pendant des mois.
        console.warn('[easter-egg] claim échoué', props.slug, e)
        emit('close')
    } finally {
        loading.value = false
    }
}

function close() {
    emit('close')
    // Recharger les Éclats dans le layout
    if (claimed.value) {
        router.reload({ only: ['auth'] })
    }
}

watch(() => props.show, (val) => {
    if (val) claim()
})
</script>

<template>
    <Teleport to="body">
        <Transition name="ee-fade">
            <div v-if="show" class="ee-backdrop" @click.self="close" role="dialog" aria-modal="true" aria-label="Secret découvert">
                <div class="ee-modal">
                    <!-- Sceau animé -->
                    <div class="ee-seal" aria-hidden="true">
                        <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="60" cy="60" r="55" stroke="#c9a84c" stroke-width="2" stroke-dasharray="4 3" class="ee-seal-ring"/>
                            <circle cx="60" cy="60" r="44" stroke="#c9a84c" stroke-width="1.5" opacity="0.6"/>
                            <text x="60" y="68" text-anchor="middle" font-size="42" class="ee-seal-eye">{{ egg.seal }}</text>
                        </svg>
                    </div>

                    <!-- Contenu -->
                    <div v-if="loading" class="ee-body">
                        <p class="ee-sub">Invocation en cours…</p>
                    </div>

                    <!-- Echec : on le dit, plutot que de simuler une reussite. -->
                    <div v-else-if="failed" class="ee-body">
                        <h2 class="ee-title">Le sceau resiste</h2>
                        <p class="ee-text">
                            {{ isCorporate
                                ? 'Vous avez bien trouvé le secret, mais il n’a pas pu être enregistré. Rien ne vous a été attribué — réessayez dans un instant.'
                                : 'Tu as bien trouvé le secret, mais il n’a pas pu être scellé. Rien ne t’a été attribué — retente dans un instant.' }}
                        </p>
                        <p class="ee-badge-note">Code {{ failedStatus }}</p>
                        <button class="ee-btn" @click="close">Fermer</button>
                    </div>

                    <div v-else-if="alreadyClaimed" class="ee-body">
                        <h2 class="ee-title">{{ egg.againTitle }}</h2>
                        <p class="ee-text">{{ egg.againText }}</p>
                        <button class="ee-btn" @click="close">Fermer</button>
                    </div>

                    <div v-else class="ee-body">
                        <p class="ee-kicker">{{ egg.kicker }}</p>
                        <h2 class="ee-title">{{ egg.title }}</h2>
                        <!-- eslint-disable-next-line vue/no-v-html -- copie statique du composant, aucune donnée utilisateur -->
                        <p v-for="(para, i) in egg.paragraphs" :key="i" class="ee-text" v-html="para"></p>
                        <div class="ee-reward" aria-live="polite">
                            <span class="ee-eclats">+{{ eclats }}</span>
                            <span class="ee-eclats-label">{{ L.xpName }}</span>
                        </div>
                        <p class="ee-badge-note">
                            🏅 Badge « {{ badgeName }} » —
                            {{ isCorporate
                                ? 'visible dans vos Ressources de développement'
                                : 'à retrouver dans la Salle du Trésor' }}
                        </p>
                        <button class="ee-btn" @click="close">{{ egg.cta }}</button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.ee-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(10, 8, 5, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    backdrop-filter: blur(4px);
}

.ee-modal {
    background: linear-gradient(160deg, #1a1408 0%, #0f0b04 100%);
    border: 1px solid #c9a84c;
    border-radius: 4px;
    max-width: 480px;
    width: 100%;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
    box-shadow: 0 0 60px rgba(201, 168, 76, 0.15), 0 0 0 1px rgba(201, 168, 76, 0.08);
    animation: ee-appear 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes ee-appear {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

.ee-seal {
    width: 96px;
    height: 96px;
    margin: 0 auto 1.5rem;
}

.ee-seal svg { width: 100%; height: 100%; }

.ee-seal-ring {
    animation: ee-spin 20s linear infinite;
    transform-origin: 60px 60px;
}

@keyframes ee-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

.ee-seal-eye {
    animation: ee-pulse 3s ease-in-out infinite;
}

@keyframes ee-pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}

.ee-kicker {
    font-size: 0.7rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #c9a84c;
    opacity: 0.7;
    margin-bottom: 0.75rem;
}

.ee-title {
    font-family: 'Cinzel', 'Georgia', serif;
    font-size: 1.6rem;
    color: #e8d5a3;
    margin-bottom: 1.25rem;
    letter-spacing: 0.04em;
}

.ee-text {
    font-size: 0.92rem;
    color: #b8a882;
    line-height: 1.7;
    margin-bottom: 1rem;
}

.ee-text em {
    color: #e8d5a3;
    font-style: italic;
}

.ee-sub {
    color: #7a6a44;
    font-size: 0.88rem;
    animation: ee-pulse 1.5s ease-in-out infinite;
}

.ee-reward {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.4rem;
    margin: 1.5rem 0 0.5rem;
}

.ee-eclats {
    font-family: 'Space Mono', monospace;
    font-size: 2.2rem;
    color: #c9a84c;
    animation: ee-glow 2s ease-in-out infinite;
}

@keyframes ee-glow {
    0%, 100% { text-shadow: 0 0 8px rgba(201, 168, 76, 0.4); }
    50%       { text-shadow: 0 0 24px rgba(201, 168, 76, 0.8); }
}

.ee-eclats-label {
    font-size: 1rem;
    color: #c9a84c;
    opacity: 0.8;
}

.ee-badge-note {
    font-size: 0.8rem;
    color: #7a6a44;
    margin-bottom: 1.75rem;
}

.ee-btn {
    background: transparent;
    border: 1px solid #c9a84c;
    color: #c9a84c;
    padding: 0.6rem 1.8rem;
    border-radius: 2px;
    font-size: 0.85rem;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.ee-btn:hover {
    background: #c9a84c;
    color: #0f0b04;
}

/* Transition Fade */
.ee-fade-enter-active,
.ee-fade-leave-active { transition: opacity 0.3s ease; }
.ee-fade-enter-from,
.ee-fade-leave-to    { opacity: 0; }
</style>
