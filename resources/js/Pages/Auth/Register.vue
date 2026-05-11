<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({ email: String })

const form = useForm({
    name: '',
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
})

const submit = () => form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
})
</script>

<template>
    <AuthLayout>
        <Head title="Créer un compte" />

        <div class="text-center mb-6">
            <h1 class="text-2xl font-semibold tracking-tight">Crée ton compte</h1>
            <p class="text-sm text-slate-500 mt-1">2 minutes. Aucune carte bancaire.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Prénom & nom</label>
                <input v-model="form.name" required autofocus class="pt-input mt-2">
                <p v-if="form.errors.name" class="text-xs text-rose-600 mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" v-model="form.email" required autocomplete="email" class="pt-input mt-2">
                <p v-if="form.errors.email" class="text-xs text-rose-600 mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Mot de passe</label>
                <input type="password" v-model="form.password" required minlength="8" autocomplete="new-password" class="pt-input mt-2">
                <p v-if="form.errors.password" class="text-xs text-rose-600 mt-1">{{ form.errors.password }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Confirmation</label>
                <input type="password" v-model="form.password_confirmation" required autocomplete="new-password" class="pt-input mt-2">
            </div>

            <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full">
                <span v-if="form.processing">Création…</span>
                <span v-else>Créer mon compte</span>
            </button>
        </form>

        <p class="text-sm text-center text-slate-500 mt-6">
            Déjà inscrit ?
            <Link :href="route('login')" class="text-indigo-600 font-medium hover:underline">Se connecter</Link>
        </p>
    </AuthLayout>
</template>
