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

        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight" style="font-family:'Playfair Display',serif">Créer mon espace</h1>
            <p class="mt-1" style="font-size:14px;color:var(--pt-text-muted)">2 minutes. Gratuit. Aucune carte bancaire.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:var(--pt-text-muted)">Prénom & nom</label>
                <input v-model="form.name" required autofocus class="pt-input" placeholder="Marie Dupont">
                <p v-if="form.errors.name" class="text-xs mt-1" style="color:var(--pt-error)">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:var(--pt-text-muted)">Adresse email</label>
                <input type="email" v-model="form.email" required autocomplete="email" class="pt-input" placeholder="vous@exemple.fr">
                <p v-if="form.errors.email" class="text-xs mt-1" style="color:var(--pt-error)">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:var(--pt-text-muted)">Mot de passe</label>
                <input type="password" v-model="form.password" required minlength="8" autocomplete="new-password" class="pt-input" placeholder="Minimum 8 caractères">
                <p v-if="form.errors.password" class="text-xs mt-1" style="color:var(--pt-error)">{{ form.errors.password }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5" style="color:var(--pt-text-muted)">Confirmer le mot de passe</label>
                <input type="password" v-model="form.password_confirmation" required autocomplete="new-password" class="pt-input">
            </div>

            <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full py-2.5">
                <span v-if="form.processing">Création…</span>
                <span v-else>Créer mon espace</span>
            </button>
        </form>

        <p class="text-center mt-6" style="font-size:13px;color:var(--pt-text-muted)">
            Déjà inscrit ?
            <Link :href="route('login')" style="color:var(--pt-gold);font-weight:500;text-decoration:none" class="hover:underline">
                Se connecter
            </Link>
        </p>
    </AuthLayout>
</template>
