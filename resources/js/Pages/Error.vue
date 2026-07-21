<script setup>
defineProps({
    status:  { type: Number, default: 500 },
    message: { type: String, default: 'Une erreur est survenue.' },
})
</script>

<template>
    <div class="error-page">
        <div class="error-box">
            <p class="error-code">{{ status }}</p>
            <p class="error-msg">{{ message }}</p>
            <a href="/" class="error-back">← Retour à l'accueil</a>

            <!-- Easter egg « Le Faux Bouton » : uniquement sur les 404, discret
                 au point de passer pour une coquille. C'est le but. -->
            <p v-if="status === 404" class="error-nowhere">
                <a href="/nulle-part">ce chemin n'existe pas non plus</a>
            </p>
        </div>
    </div>
</template>

<style scoped>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-base);
    font-family: var(--font-body);
}
.error-box {
    text-align: center;
    padding: 3rem 2rem;
}
.error-code {
    font-size: 5rem;
    font-weight: 700;
    color: var(--color-primary);
    margin: 0 0 0.5rem;
    line-height: 1;
}
.error-msg {
    font-size: 1.1rem;
    color: var(--text-secondary);
    margin: 0 0 2rem;
}
.error-back {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
    border-bottom: 1px solid currentColor;
}
.error-back:hover { opacity: .75; }
.error-nowhere {
    margin: 3.5rem 0 0;
    font-size: .68rem;
    letter-spacing: .02em;
}
.error-nowhere a {
    color: var(--text-muted);
    text-decoration: none;
    opacity: .35;
    transition: opacity .3s ease;
}
.error-nowhere a:hover,
.error-nowhere a:focus-visible { opacity: .9; }
</style>
