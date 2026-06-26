<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
    token: String,
    email: String,
})

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submit = () => form.post(route('password.update'))
</script>

<template>
    <AuthLayout>
        <Head title="Réinitialiser le mot de passe" />

        <div class="max-w-md mx-auto">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-semibold tracking-tight">Nouveau mot de passe</h1>
                <p class="mt-2 text-sm" style="color:var(--text-secondary)">Choisis un mot de passe d'au moins 8 caractères.</p>
            </div>

            <form @submit.prevent="submit" class="pt-card p-8 space-y-5">
                <div>
                    <label for="reset-email" class="block text-sm font-medium" style="color:var(--text-secondary)">Adresse email</label>
                    <input
                        id="reset-email"
                        v-model="form.email"
                        type="email"
                        required
                        class="pt-input mt-2"
                    >
                    <p v-if="form.errors.email" class="pt-error text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="reset-password" class="block text-sm font-medium" style="color:var(--text-secondary)">Nouveau mot de passe</label>
                    <input
                        id="reset-password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="pt-input mt-2"
                        placeholder="Minimum 8 caractères"
                    >
                    <p v-if="form.errors.password" class="pt-error text-xs mt-1">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label for="reset-password-confirm" class="block text-sm font-medium" style="color:var(--text-secondary)">Confirmer le mot de passe</label>
                    <input
                        id="reset-password-confirm"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        class="pt-input mt-2"
                    >
                </div>

                <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full py-2.5">
                    <span v-if="form.processing">Réinitialisation…</span>
                    <span v-else>Réinitialiser le mot de passe</span>
                </button>
            </form>

            <p class="text-center text-sm mt-6" style="color:var(--text-muted)">
                <Link :href="route('login')" class="ac-link-primary">← Retour à la connexion</Link>
            </p>
        </div>
    </AuthLayout>
</template>
