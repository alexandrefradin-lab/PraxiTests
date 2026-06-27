<script setup>
import { Link, useForm, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
    email: String,
    status: String,
})

const page = usePage()

const linkSent = computed(
    () => props.status === 'verification-link-sent'
        || page.props.flash?.status === 'verification-link-sent',
)
const errorMsg = computed(() => page.props.flash?.error)

const form = useForm({})
const resend = () => form.post(route('verification.send'), { preserveScroll: true })

const logout = () => router.post(route('logout'))
</script>

<template>
    <AuthLayout>
        <Head title="Confirmez votre adresse — PraxiQuest" />

        <div class="lp-anim-badge" style="margin-bottom:1.5rem">
            <div class="lp-badge" style="display:inline-flex;align-items:center;gap:7px;font-family:'Space Mono',monospace;font-size:9px;letter-spacing:0.16em;color:var(--color-primary);text-transform:uppercase;font-weight:400;margin-bottom:1rem;padding:4px 12px 4px 9px;border:1px solid rgba(166,117,32,0.3);border-radius:4px;background:rgba(166,117,32,0.05)">
                <div style="width:5px;height:5px;background:var(--color-primary);transform:rotate(45deg);flex-shrink:0"></div>
                Dernière étape
            </div>
            <h1 class="lp-h1-gradient lp-anim-h1a" style="font-family:'Space Grotesk',sans-serif;font-size:1.625rem;font-weight:700;letter-spacing:-0.02em;line-height:1.15;margin:0 0 0.5rem">
                Confirmez votre adresse email
            </h1>
            <p class="lp-anim-sub" style="font-family:'Inter',sans-serif;font-size:14px;color:var(--text-secondary);margin:0;line-height:1.55">
                Nous avons envoyé un lien de confirmation à
                <strong style="color:var(--text-primary)">{{ email }}</strong>.
                Ouvrez votre boîte mail et cliquez sur le lien pour entrer dans la Quête.
            </p>
        </div>

        <!-- Confirmation d'envoi -->
        <div v-if="linkSent" style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.85rem 1rem;border-radius:8px;background:rgba(34,139,87,0.08);border:1px solid rgba(34,139,87,0.25);margin-bottom:1.25rem">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="flex-shrink:0;margin-top:1px">
                <path d="M13.5 4.5 6 12 2.5 8.5" stroke="#228B57" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span style="font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary);line-height:1.5">
                Un nouveau lien de confirmation vient d'être envoyé. Pensez à vérifier vos spams.
            </span>
        </div>

        <!-- Erreur d'envoi (SMTP indisponible) -->
        <div v-if="errorMsg" style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.85rem 1rem;border-radius:8px;background:rgba(123,21,21,0.07);border:1px solid rgba(123,21,21,0.25);margin-bottom:1.25rem">
            <span style="font-family:'Inter',sans-serif;font-size:13px;color:var(--color-secondary);line-height:1.5">{{ errorMsg }}</span>
        </div>

        <p style="font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary);line-height:1.55;margin:0 0 1.25rem">
            Vous n'avez rien reçu après quelques minutes ? Renvoyez le lien ci-dessous.
        </p>

        <form @submit.prevent="resend">
            <button
                type="submit"
                :disabled="form.processing"
                class="pt-btn-primary lp-btn-primary"
                style="width:100%;padding:0.7rem 1.5rem;justify-content:center"
            >
                <span v-if="form.processing">Envoi en cours…</span>
                <span v-else>Renvoyer le lien de confirmation</span>
            </button>
        </form>

        <p class="lp-anim-trust" style="text-align:center;margin-top:1.5rem;font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary)">
            Mauvaise adresse ?
            <button type="button" @click="logout" style="background:none;border:none;padding:0;cursor:pointer;color:var(--color-primary);font-weight:600;font-family:inherit;font-size:13px" class="hover:underline">
                Se déconnecter
            </button>
        </p>
    </AuthLayout>
</template>
