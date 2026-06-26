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
</style>
