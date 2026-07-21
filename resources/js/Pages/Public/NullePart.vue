<script setup>
import { computed, onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import EasterEgg from '@/Components/EasterEgg.vue'
import { useParcours } from '@/composables/useParcours'

// Page publique : un visiteur non connecté suit sa préférence localStorage,
// gérée par le composable — pas besoin de cas particulier ici.
const { isCorporate } = useParcours()

const props = defineProps({
    // Faux pour un visiteur anonyme : la page reste lisible, mais rien
    // n'est crédité (la route de claim est derrière l'auth de toute façon).
    can_claim: { type: Boolean, default: false },
})

const showEgg = ref(false)

const COPY = {
    medieval: {
        signature: "— l'Oracle",
        back: 'Reprendre un chemin',
        paragraphs: [
            "Cette page ne mène à rien. Elle n'est reliée à aucun menu, aucun parcours, aucun tableau de bord. Tu es arrivé ici en cliquant sur un lien qui t'annonçait exactement ça.",
            "On passe beaucoup de temps, dans un bilan de compétences, à chercher <em>la</em> direction. Celle qui serait la bonne. Et pendant ce temps-là, l'expérience la plus utile est souvent celle qu'on n'avait pas prévue : le stage qui ne débouche sur rien, le métier essayé six mois, la conversation avec quelqu'un dont le travail ne t'intéressait pas.",
            "Se perdre n'est pas une panne dans le parcours. C'est une partie du parcours. La seule chose qui distingue une errance d'un détour utile, c'est ce qu'on en ramène.",
        ],
    },
    corporate: {
        signature: '— votre conseiller',
        back: 'Revenir à l\'accueil',
        paragraphs: [
            "Cette page ne mène à rien. Elle n'est reliée à aucun menu, aucun parcours, aucun tableau de bord. Vous êtes arrivé ici en cliquant sur un lien qui vous annonçait exactement cela.",
            "On passe beaucoup de temps, dans un bilan de compétences, à chercher <em>la</em> direction — celle qui serait la bonne. Pendant ce temps, l'expérience la plus instructive est souvent celle qui n'était pas prévue : la mission qui ne débouche sur rien, le poste occupé six mois, l'échange avec quelqu'un dont le métier ne vous intéressait pas.",
            "S'égarer n'est pas un incident de parcours. C'est une partie du parcours. Ce qui distingue une errance d'un détour utile, c'est uniquement ce qu'on en retire.",
        ],
    },
}
const copy = computed(() => isCorporate.value ? COPY.corporate : COPY.medieval)

// Petite latence : on laisse le texte s'installer avant que la modale
// n'arrive. Se faire récompenser trop vite tuerait le moment.
onMounted(() => {
    if (props.can_claim) setTimeout(() => { showEgg.value = true }, 2600)
})
</script>

<template>
    <Head title="Nulle part" />

    <div class="np-page">
        <div class="np-inner">
            <p class="np-kicker">Nulle part</p>

            <!-- eslint-disable-next-line vue/no-v-html -- copie statique du composant, aucune donnée utilisateur -->
            <p v-for="(para, i) in copy.paragraphs" :key="i" class="np-para" v-html="para"></p>

            <p class="np-signature">{{ copy.signature }}</p>

            <a href="/" class="np-back">{{ copy.back }}</a>
        </div>

        <EasterEgg :show="showEgg" slug="faux_bouton" @close="showEgg = false" />
    </div>
</template>

<style scoped>
.np-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-base);
    font-family: var(--font-body);
    padding: 3rem 1.5rem;
}

.np-inner {
    max-width: 34rem;
    animation: np-in 1.4s ease both;
}

@keyframes np-in {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.np-kicker {
    font-family: var(--font-display, var(--font-body));
    font-size: .7rem;
    letter-spacing: .22em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin: 0 0 2.5rem;
}

.np-para {
    font-size: 1rem;
    line-height: 1.85;
    color: var(--text-secondary);
    margin: 0 0 1.6rem;
}

.np-para em {
    color: var(--text-primary);
    font-style: italic;
}

.np-signature {
    margin: 2.5rem 0 0;
    font-size: .85rem;
    font-style: italic;
    color: var(--text-muted);
}

.np-back {
    display: inline-block;
    margin-top: 3rem;
    font-size: .85rem;
    color: var(--color-primary);
    text-decoration: none;
    border-bottom: 1px solid currentColor;
}

.np-back:hover { opacity: .75; }
</style>
