<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({
    enabled:        { type: Boolean, default: false },
    secret:         { type: String,  default: '' },
    qr_url:         { type: String,  default: '' },
    otp_uri:        { type: String,  default: '' },
    recovery_codes: { type: Array,   default: () => [] },
})

const showSecret   = ref(false)
const showRecovery = ref(props.enabled && props.recovery_codes.length > 0)

const enableForm  = useForm({ code: '' })
const disableForm = useForm({ password: '' })
const regenForm   = useForm({})

const submit = () => {
    enableForm.post(route('account.two-factor.enable'), {
        onSuccess: () => { showRecovery.value = true },
        onFinish: () => enableForm.reset('code'),
    })
}

const disable = () => {
    if (!confirm('Désactiver le 2FA ? Votre compte sera moins protégé.')) return
    disableForm.post(route('account.two-factor.disable'), {
        onFinish: () => disableForm.reset('password'),
    })
}

const regen = () => {
    if (!confirm('Régénérer les codes de récupération ? Les anciens codes seront invalides.')) return
    regenForm.post(route('account.two-factor.recovery-codes'))
}

const copySecret = () => {
    navigator.clipboard?.writeText(props.secret)
}
</script>

<template>
    <AuthLayout>
        <Head title="Double authentification — PraxiQuest" />

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
                letter-spacing:-0.02em;
                color:var(--color-accent);margin:0 0 0.5rem;
            ">Double authentification (2FA)</h1>
            <p style="font-family:'Inter',sans-serif;font-size:14px;color:var(--text-secondary);margin:0">
                Protégez votre compte avec une application d'authentification (Google Authenticator, Authy, etc.).
            </p>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="pt-flash-success" style="margin-bottom:1.25rem">
            {{ $page.props.flash.success }}
        </div>

        <!-- ── 2FA déjà activé ─────────────────────────────────────────── -->
        <template v-if="enabled">
            <div style="
                padding:1rem 1.25rem;border-radius:10px;
                background:rgba(var(--color-success-rgb,34,197,94),0.1);
                border:1px solid rgba(var(--color-success-rgb,34,197,94),0.25);
                margin-bottom:1.5rem;display:flex;align-items:center;gap:0.75rem;
            ">
                <span style="font-size:1.25rem">✅</span>
                <span style="font-family:'Inter',sans-serif;font-size:14px;color:var(--text-primary);font-weight:500">
                    La double authentification est <strong>activée</strong>.
                </span>
            </div>

            <!-- Codes de récupération -->
            <div style="margin-bottom:1.5rem">
                <h2 style="
                    font-family:'Space Grotesk',sans-serif;font-size:1rem;
                    font-weight:600;margin:0 0 0.75rem;color:var(--text-primary);
                ">Codes de récupération</h2>
                <p style="font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary);margin:0 0 0.75rem;line-height:1.5">
                    Ces codes permettent d'accéder à votre compte si vous perdez votre téléphone.
                    Chaque code est à usage unique. Conservez-les dans un endroit sûr.
                </p>

                <div v-if="recovery_codes.length" style="
                    display:grid;grid-template-columns:1fr 1fr;gap:0.375rem;
                    font-family:monospace;font-size:13px;
                    background:var(--bg-card);border-radius:8px;padding:1rem;
                    border:1px solid var(--border-subtle);margin-bottom:0.75rem;
                ">
                    <span
                        v-for="c in recovery_codes" :key="c"
                        style="padding:2px 0;color:var(--text-primary);letter-spacing:0.05em"
                    >{{ c }}</span>
                </div>
                <p v-else style="font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary)">
                    Aucun code disponible — régénérez-en.
                </p>

                <form @submit.prevent="regen">
                    <button type="submit" class="pt-btn-secondary" :disabled="regenForm.processing" style="font-size:13px">
                        Régénérer les codes
                    </button>
                </form>
            </div>

            <!-- Désactivation -->
            <div style="
                padding:1rem 1.25rem;border-radius:10px;
                border:1px solid var(--border-subtle);
            ">
                <h2 style="
                    font-family:'Space Grotesk',sans-serif;font-size:1rem;
                    font-weight:600;margin:0 0 0.5rem;color:var(--text-primary);
                ">Désactiver le 2FA</h2>
                <form @submit.prevent="disable" style="display:flex;flex-direction:column;gap:0.75rem">
                    <input
                        v-model="disableForm.password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="Confirmez votre mot de passe"
                        class="pt-input"
                        :class="{ 'pt-input-error': disableForm.errors.password }"
                    />
                    <p v-if="disableForm.errors.password" class="pt-error">{{ disableForm.errors.password }}</p>
                    <button type="submit" class="pt-btn-danger" :disabled="disableForm.processing" style="font-size:13px">
                        Désactiver la double authentification
                    </button>
                </form>
            </div>
        </template>

        <!-- ── Activation du 2FA ───────────────────────────────────────── -->
        <template v-else>
            <!-- Étape 1 : Scanner le QR -->
            <div style="margin-bottom:1.5rem">
                <h2 style="
                    font-family:'Space Grotesk',sans-serif;font-size:1rem;
                    font-weight:600;margin:0 0 0.75rem;color:var(--text-primary);
                ">1. Scanner avec votre application</h2>
                <p style="font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary);margin:0 0 1rem;line-height:1.5">
                    Utilisez Google Authenticator, Authy, 1Password ou toute autre application TOTP.
                </p>

                <!-- QR code -->
                <div style="display:flex;justify-content:center;margin-bottom:1rem">
                    <img
                        :src="qr_url"
                        alt="QR code 2FA"
                        width="180" height="180"
                        style="border-radius:8px;border:4px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.15)"
                    />
                </div>

                <!-- Secret manuel -->
                <div style="text-align:center">
                    <button
                        type="button"
                        @click="showSecret = !showSecret"
                        style="
                            background:none;border:none;cursor:pointer;padding:0;
                            font-family:'Inter',sans-serif;font-size:12px;
                            color:var(--text-secondary);text-decoration:underline;
                        "
                    >
                        {{ showSecret ? 'Masquer' : 'Saisir le code manuellement' }}
                    </button>

                    <div v-if="showSecret" style="
                        margin-top:0.75rem;padding:0.75rem 1rem;
                        background:var(--bg-card);border-radius:8px;
                        border:1px solid var(--border-subtle);
                        display:flex;align-items:center;gap:0.5rem;justify-content:center;flex-wrap:wrap;
                    ">
                        <code style="
                            font-family:monospace;font-size:14px;
                            letter-spacing:0.15em;color:var(--color-accent);
                            word-break:break-all;
                        ">{{ secret }}</code>
                        <button
                            type="button"
                            @click="copySecret"
                            style="
                                background:none;border:1px solid var(--border-subtle);
                                cursor:pointer;border-radius:4px;padding:2px 8px;
                                font-size:11px;color:var(--text-secondary);
                            "
                        >Copier</button>
                    </div>
                </div>
            </div>

            <!-- Étape 2 : Confirmer avec le code -->
            <div>
                <h2 style="
                    font-family:'Space Grotesk',sans-serif;font-size:1rem;
                    font-weight:600;margin:0 0 0.75rem;color:var(--text-primary);
                ">2. Confirmer avec le code affiché</h2>

                <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:1rem">
                    <div>
                        <label class="pt-label">Code à 6 chiffres</label>
                        <input
                            v-model="enableForm.code"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            placeholder="000000"
                            class="pt-input"
                            :class="{ 'pt-input-error': enableForm.errors.code }"
                            style="letter-spacing:0.25em;font-size:1.25rem;text-align:center"
                        />
                        <p v-if="enableForm.errors.code" class="pt-error">{{ enableForm.errors.code }}</p>
                    </div>

                    <button type="submit" class="pt-btn-primary" :disabled="enableForm.processing">
                        <span v-if="enableForm.processing">Activation…</span>
                        <span v-else>Activer la double authentification</span>
                    </button>
                </form>
            </div>
        </template>
    </AuthLayout>
</template>
