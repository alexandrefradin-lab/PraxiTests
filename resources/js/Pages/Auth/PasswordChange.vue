<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.put(route('account.password.change'), {
        onSuccess: () => form.reset(),
        onError: () => form.reset('current_password'),
    })
}
</script>

<template>
    <AuthLayout>
        <Head title="Changer mon sceau secret — PraxiQuest" />

        <!-- En-tête -->
        <div style="margin-bottom:2rem">
            <div style="
                width:48px;height:48px;border-radius:12px;
                background:var(--color-accent);
                display:flex;align-items:center;justify-content:center;
                margin-bottom:1rem;font-size:1.5rem;
            ">🗝️</div>
            <h1 style="
                font-family:'Space Grotesk',sans-serif;
                font-size:1.5rem;font-weight:700;
                letter-spacing:-0.02em;
                color:var(--color-accent);margin:0 0 0.5rem;
            ">Changer mon sceau secret</h1>
            <p style="font-family:'Inter',sans-serif;font-size:14px;color:var(--text-secondary);margin:0">
                Confirmez votre mot de passe actuel, puis choisissez-en un nouveau (8 caractères minimum).
            </p>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="pt-flash-success" style="margin-bottom:1.25rem">
            {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:1.1rem">
            <div>
                <label class="pt-label" for="current_password">Mot de passe actuel</label>
                <input
                    id="current_password"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    class="pt-input"
                    :class="{ 'pt-input-error': form.errors.current_password }"
                />
                <p v-if="form.errors.current_password" class="pt-error">{{ form.errors.current_password }}</p>
            </div>

            <div>
                <label class="pt-label" for="password">Nouveau mot de passe</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    class="pt-input"
                    :class="{ 'pt-input-error': form.errors.password }"
                />
                <p v-if="form.errors.password" class="pt-error">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="pt-label" for="password_confirmation">Confirmer le nouveau mot de passe</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="pt-input"
                />
            </div>

            <button type="submit" class="pt-btn-primary" :disabled="form.processing">
                <span v-if="form.processing">Mise à jour…</span>
                <span v-else>Mettre à jour mon sceau secret</span>
            </button>
        </form>

        <p style="font-family:'Inter',sans-serif;font-size:12px;color:var(--text-muted);margin:1.25rem 0 0;line-height:1.5">
            Mot de passe oublié ? Déconnectez-vous puis utilisez « Sceau oublié ? » sur la page de connexion
            pour le réinitialiser par email.
        </p>
    </AuthLayout>
</template>
