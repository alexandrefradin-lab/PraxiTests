<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const form = useForm({ email: '' })
const submit = () => form.post(route('password.email'))
</script>

<template>
    <AuthLayout>
        <Head title="Mot de passe oublié" />

        <div class="max-w-md mx-auto">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-semibold tracking-tight">Mot de passe oublié ?</h1>
                <p class="mt-2 text-sm" style="color:var(--text-secondary)">Saisis ton adresse email. Si un compte existe, tu recevras un lien de réinitialisation.</p>
            </div>

            <div v-if="$page.props.flash?.success" class="ac-flash-success p-4 mb-6 text-sm">
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submit" class="pt-card p-8 space-y-5">
                <div>
                    <label for="forgot-email" class="block text-sm font-medium" style="color:var(--text-secondary)">Adresse email</label>
                    <input
                        id="forgot-email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        class="pt-input mt-2"
                        placeholder="ton@email.fr"
                    >
                    <p v-if="form.errors.email" class="pt-error text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full py-2.5">
                    <span v-if="form.processing">Envoi…</span>
                    <span v-else>Envoyer le lien de réinitialisation</span>
                </button>
            </form>

            <p class="text-center text-sm mt-6" style="color:var(--text-muted)">
                <Link :href="route('login')" class="ac-link-primary">← Retour à la connexion</Link>
            </p>
        </div>
    </AuthLayout>
</template>
