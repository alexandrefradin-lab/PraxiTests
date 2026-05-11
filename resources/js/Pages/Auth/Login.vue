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

        <div class="text-center mb-6">
            <h1 class="text-2xl font-semibold tracking-tight">Bon retour</h1>
            <p class="text-sm text-slate-500 mt-1">Connecte-toi pour reprendre ton parcours.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" v-model="form.email" autofocus required autocomplete="username" class="pt-input mt-2">
                <p v-if="form.errors.email" class="text-xs text-rose-600 mt-1">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Mot de passe</label>
                <input type="password" v-model="form.password" required autocomplete="current-password" class="pt-input mt-2">
                <p v-if="form.errors.password" class="text-xs text-rose-600 mt-1">{{ form.errors.password }}</p>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" v-model="form.remember" class="rounded border-slate-300 text-indigo-600">
                Rester connecté
            </label>

            <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full">
                <span v-if="form.processing">Connexion…</span>
                <span v-else>Se connecter</span>
            </button>
        </form>

        <p class="text-sm text-center text-slate-500 mt-6">
            Pas encore de compte ?
            <Link :href="route('register')" class="text-indigo-600 font-medium hover:underline">Créer un compte</Link>
        </p>
    </AuthLayout>
</template>
