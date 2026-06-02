<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') })
</script>

<template>
    <AuthLayout>
        <Head title="Connexion" />

        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight" style="font-family:'Playfair Display',serif">Bon retour</h1>
            <p class="mt-1" style="font-size:14px;color:var(--pt-text-muted)">Connectez-vous pour reprendre votre parcours.</p>
        </div>

        <div v-if="$page.props.flash?.success" class="pt-flash-success mb-5">
            {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium" style="color:var(--pt-text-muted)">Adresse email</label>
                <input type="email" v-model="form.email" autofocus required autocomplete="username" class="pt-input mt-1.5">
                <p v-if="form.errors.email" class="text-xs mt-1" style="color:var(--pt-error)">{{ form.errors.email }}</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium" style="color:var(--pt-text-muted)">Mot de passe</label>
                    <Link :href="route('password.request')" style="font-size:12px;color:var(--pt-gold);text-decoration:none" class="hover:underline">
                        Mot de passe oublié ?
                    </Link>
                </div>
                <input type="password" v-model="form.password" required autocomplete="current-password" class="pt-input">
                <p v-if="form.errors.password" class="text-xs mt-1" style="color:var(--pt-error)">{{ form.errors.password }}</p>
            </div>

            <label class="flex items-center gap-2 text-sm" style="color:var(--pt-text-muted)">
                <input type="checkbox" v-model="form.remember" class="rounded" style="accent-color:var(--pt-navy)">
                Rester connecté
            </label>

            <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full py-2.5">
                <span v-if="form.processing">Connexion…</span>
                <span v-else>Se connecter</span>
            </button>
        </form>

        <p class="text-center mt-6" style="font-size:13px;color:var(--pt-text-muted)">
            Pas encore de compte ?
            <Link :href="route('register')" style="color:var(--pt-gold);font-weight:500;text-decoration:none" class="hover:underline">
                Créer mon espace
            </Link>
        </p>
    </AuthLayout>
</template>
