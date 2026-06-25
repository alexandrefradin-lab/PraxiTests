<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
    recovery: { type: Boolean, default: false },
})

const showRecovery = ref(props.recovery)

const form = useForm({
    code: '',
    recovery_code: '',
})

const submit = () => {
    if (showRecovery.value) {
        form.post(route('two-factor.challenge'), {
            onFinish: () => form.reset('recovery_code'),
        })
    } else {
        form.post(route('two-factor.challenge'), {
            onFinish: () => form.reset('code'),
        })
    }
}
</script>

<template>
    <AuthLayout>
        <Head title="Vérification en deux étapes — PraxiQuest" />

        <!-- En-tête -->
        <div style="margin-bottom:2rem">
            <div style="
                width:48px;height:48px;border-radius:12px;
                background:var(--color-accent);
                display:flex;align-items:center;justify-content:center;
                margin-bottom:1rem;font-size:1.5rem;
            ">🔐</div>
            <h1 style="
                font-family:'Space Grotesk',sans-serif;
                font-size:1.5rem;font-weight:700;
                letter-spacing:-0.02em;line-height:1.2;
                color:var(--color-accent);margin:0 0 0.5rem;
            ">Vérification en deux étapes</h1>
            <p style="
                font-family:'Inter',sans-serif;
                font-size:14px;color:var(--text-secondary);margin:0;line-height:1.5;
            ">
                <template v-if="!showRecovery">
                    Saisissez le code à 6 chiffres affiché dans votre application d'authentification.
                </template>
                <template v-else>
                    Saisissez l'un de vos codes de récupération à usage unique.
                </template>
            </p>
        </div>

        <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:1.25rem">

            <!-- Code TOTP -->
            <div v-if="!showRecovery">
                <label class="pt-label">Code d'authentification</label>
                <input
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    placeholder="000000"
                    autofocus
                    class="pt-input"
                    :class="{ 'pt-input-error': form.errors.code }"
                    style="letter-spacing:0.25em;font-size:1.25rem;text-align:center"
                />
                <p v-if="form.errors.code" class="pt-error">{{ form.errors.code }}</p>
            </div>

            <!-- Code de récupération -->
            <div v-else>
                <label class="pt-label">Code de récupération</label>
                <input
                    v-model="form.recovery_code"
                    type="text"
                    autocomplete="off"
                    placeholder="XXXXXX-XXXXXX"
                    autofocus
                    class="pt-input"
                    :class="{ 'pt-input-error': form.errors.recovery_code }"
                    style="font-family:monospace;letter-spacing:0.1em;text-align:center"
                />
                <p v-if="form.errors.recovery_code" class="pt-error">{{ form.errors.recovery_code }}</p>
            </div>

            <!-- Bouton -->
            <button type="submit" class="pt-btn-primary" :disabled="form.processing">
                <span v-if="form.processing">Vérification…</span>
                <span v-else>Vérifier</span>
            </button>

            <!-- Bascule TOTP ↔ récupération -->
            <div style="text-align:center;margin-top:0.5rem">
                <button
                    type="button"
                    @click="showRecovery = !showRecovery; form.clearErrors(); form.reset()"
                    style="
                        background:none;border:none;cursor:pointer;padding:0;
                        font-family:'Inter',sans-serif;font-size:13px;
                        color:var(--text-secondary);text-decoration:underline;
                    "
                >
                    <template v-if="!showRecovery">Utiliser un code de récupération</template>
                    <template v-else>Utiliser l'application d'authentification</template>
                </button>
            </div>
        </form>
    </AuthLayout>
</template>
