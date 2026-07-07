<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { useParcours } from '@/composables/useParcours'

const props = defineProps({
    email: String,
    // Inscription via lien d'invitation : propose la case de consentement
    // RGPD au partage des résultats avec le professionnel invitant.
    viaInvitation: { type: Boolean, default: false },
})

const { theme, isCorporate, L, setParcours } = useParcours()

const form = useForm({
    name: '',
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
    terms: false,
    consent_share: false,
    quest_title: '',
    ui_theme: theme.value,
    website: '', // honeypot anti-bot — doit rester vide
})

// Choix du parcours dès l'inscription : bascule live (page + libellés) et
// valeur envoyée avec le formulaire pour être posée sur users.ui_theme.
function chooseParcours(t) {
    setParcours(t)
    form.ui_theme = t
}

const submit = () => form.post(route('register'), {
    onSuccess: () => {
        // Le parcours a été choisi ici : la WelcomeModal saute son écran de choix.
        try { localStorage.setItem('praxiquest_parcours_chosen', '1') } catch (e) { /* mode privé */ }
    },
    onFinish: () => form.reset('password', 'password_confirmation'),
})

const questOptions = computed(() => [
    {
        value: 'architecte',
        label: "L'Architecte",
        description: isCorporate.value
            ? 'Vous construisez des systèmes, vous pensez en structures.'
            : 'Tu construis des systèmes, tu penses en structures.',
    },
    {
        value: 'explorateur',
        label: "L'Explorateur",
        description: isCorporate.value
            ? 'Vous cherchez, vous questionnez, vous aimez les possibilités.'
            : 'Tu cherches, tu questionnes, tu aimes les possibilités.',
    },
    {
        value: 'passeur',
        label: 'Le Passeur',
        description: isCorporate.value
            ? 'Vous transmettez, vous connectez, vous faites grandir.'
            : 'Tu transmets, tu connectes, tu fais grandir.',
    },
])
</script>

<template>
    <AuthLayout>
        <Head :title="`${L.authTitle} — PraxiQuest`" />

        <!-- En-tête -->
        <div class="lp-anim-badge" style="margin-bottom:1.5rem">
            <div class="lp-badge" style="display:inline-flex;align-items:center;gap:7px;font-family:'Space Mono',monospace;font-size:9px;letter-spacing:0.16em;color:var(--color-primary);text-transform:uppercase;font-weight:400;margin-bottom:1rem;padding:4px 12px 4px 9px;border:1px solid var(--border-mid);border-radius:4px;background:var(--pt-gold-pale)">
                <div style="width:5px;height:5px;background:var(--color-primary);transform:rotate(45deg);flex-shrink:0"></div>
                Gratuit · 2 minutes · RGPD
            </div>
            <h1 class="lp-h1-gradient lp-anim-h1a" style="
                font-family:var(--font-display);
                font-size:1.625rem;font-weight:700;
                letter-spacing:-0.02em;line-height:1.15;
                margin:0 0 0.5rem;
            ">{{ L.authTitle }}</h1>
            <p class="lp-anim-sub" style="
                font-family:'Inter',sans-serif;
                font-size:14px;color:var(--text-secondary);margin:0;line-height:1.5;
            ">{{ L.authSubtitle }}</p>
        </div>

        <!-- Choix du parcours visuel — dès l'inscription -->
        <div style="margin-bottom:1.5rem">
            <p style="
                font-family:'Space Mono',monospace;font-size:9px;font-weight:600;
                letter-spacing:0.12em;text-transform:uppercase;
                color:var(--text-muted);margin:0 0 0.5rem;
            ">Mon parcours</p>
            <div role="group" aria-label="Choix du parcours" style="display:flex;border:1px solid var(--border-mid);border-radius:var(--r);overflow:hidden">
                <button
                    type="button"
                    @click="chooseParcours('medieval')"
                    class="auth-parcours-opt"
                    :class="{ 'auth-parcours-opt--active': !isCorporate }"
                    :aria-pressed="!isCorporate"
                >Médiéval</button>
                <button
                    type="button"
                    @click="chooseParcours('corporate')"
                    class="auth-parcours-opt"
                    :class="{ 'auth-parcours-opt--active': isCorporate }"
                    style="border-left:1px solid var(--border-mid)"
                    :aria-pressed="isCorporate"
                >Corporate</button>
            </div>
            <p style="
                font-family:'Inter',sans-serif;font-size:11px;
                color:var(--text-muted);margin:0.4rem 0 0;line-height:1.5;
            ">{{ isCorporate
                ? "L'expérience executive : sobre et directe, pensée pour un cadre professionnel — l'essentiel, sans distraction."
                : "L'expérience immersive : la gamification — quêtes, éclats, niveaux — entretient ta motivation tout au long du bilan."
            }} Modifiable à tout moment.</p>
        </div>

        <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:1.125rem">

            <!-- Honeypot anti-bot : invisible et inaccessible au clavier/lecteurs d'écran -->
            <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden">
                <label for="register-website">Ne pas remplir</label>
                <input
                    id="register-website"
                    type="text"
                    v-model="form.website"
                    tabindex="-1"
                    autocomplete="off"
                >
            </div>

            <!-- BLOC NEUROMARKETING : Ancrage identitaire précoce -->
            <div class="lp-anim-ctas">
                <p style="
                    font-family:var(--font-display);
                    font-size:13px;font-weight:600;
                    color:var(--text-primary);
                    margin:0 0 0.75rem;letter-spacing:0.01em;
                ">{{ L.authQuestLabel }}</p>

                <div style="display:flex;flex-direction:column;gap:0.625rem">
                    <label
                        v-for="opt in questOptions"
                        :key="opt.value"
                        class="auth-quest-label"
                        style="cursor:pointer;display:block"
                    >
                        <input
                            type="radio"
                            :value="opt.value"
                            v-model="form.quest_title"
                            style="position:absolute;opacity:0;pointer-events:none;width:0;height:0"
                        >
                        <div class="auth-quest-card" :class="{ 'auth-quest-card--selected': form.quest_title === opt.value }" style="
                            padding:0.875rem 1rem;
                            border-radius:8px;
                            border:1.5px solid var(--glass-border);
                            background:var(--bg-surface);
                            display:flex;align-items:center;justify-content:space-between;gap:0.75rem;
                        ">
                            <div style="display:flex;flex-direction:column;gap:0.2rem;min-width:0">
                                <span style="
                                    font-family:'Space Grotesk',sans-serif;
                                    font-size:14px;font-weight:700;
                                    color:var(--text-primary);
                                ">{{ opt.label }}</span>
                                <span style="
                                    font-family:'Inter',sans-serif;
                                    font-size:12px;color:var(--text-secondary);line-height:1.4;
                                ">{{ opt.description }}</span>
                            </div>
                            <div class="auth-quest-check" style="flex-shrink:0;width:20px;height:20px;border-radius:50%;border:1.5px solid rgba(166,117,32,0.3);display:flex;align-items:center;justify-content:center">
                                <svg v-if="form.quest_title === opt.value" width="10" height="10" viewBox="0 0 10 10" fill="none">
                                    <polyline points="1.5,5 4,7.5 8.5,2.5" stroke="#A67520" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                    </label>
                </div>
                <p v-if="form.errors.quest_title" style="
                    font-family:'Inter',sans-serif;font-size:12px;
                    color:var(--color-secondary);margin:0.35rem 0 0;
                ">{{ form.errors.quest_title }}</p>
            </div>

            <!-- Séparateur -->
            <div style="border-top:1px solid var(--glass-border);margin:0.25rem 0"></div>

            <!-- Prénom & nom -->
            <div>
                <label style="
                    display:block;
                    font-family:'Inter',sans-serif;font-size:13px;font-weight:500;
                    color:var(--text-secondary);margin-bottom:0.4rem;
                " for="register-name">{{ L.authName }}</label>
                <input
                    id="register-name"
                    v-model="form.name"
                    required
                    autofocus
                    class="pt-input"
                    placeholder="Marie Dupont"
                >
                <p v-if="form.errors.name" style="
                    font-family:'Inter',sans-serif;font-size:12px;
                    color:var(--color-secondary);margin:0.35rem 0 0;
                ">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
                <label style="
                    display:block;
                    font-family:'Inter',sans-serif;font-size:13px;font-weight:500;
                    color:var(--text-secondary);margin-bottom:0.4rem;
                " for="register-email">{{ L.authEmail }}</label>
                <input
                    id="register-email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="email"
                    class="pt-input"
                    placeholder="vous@exemple.fr"
                >
                <p v-if="form.errors.email" style="
                    font-family:'Inter',sans-serif;font-size:12px;
                    color:var(--color-secondary);margin:0.35rem 0 0;
                ">{{ form.errors.email }}</p>
            </div>

            <!-- Mot de passe -->
            <div>
                <label style="
                    display:block;
                    font-family:'Inter',sans-serif;font-size:13px;font-weight:500;
                    color:var(--text-secondary);margin-bottom:0.4rem;
                " for="register-password">{{ L.authPassword }}</label>
                <input
                    id="register-password"
                    type="password"
                    v-model="form.password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="pt-input"
                    placeholder="Minimum 8 caractères"
                >
                <p v-if="form.errors.password" style="
                    font-family:'Inter',sans-serif;font-size:12px;
                    color:var(--color-secondary);margin:0.35rem 0 0;
                ">{{ form.errors.password }}</p>
            </div>

            <!-- Confirmation -->
            <div>
                <label style="
                    display:block;
                    font-family:'Inter',sans-serif;font-size:13px;font-weight:500;
                    color:var(--text-secondary);margin-bottom:0.4rem;
                " for="register-password-confirm">{{ L.authPasswordConfirm }}</label>
                <input
                    id="register-password-confirm"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    class="pt-input"
                    placeholder="••••••••"
                >
                <p v-if="form.errors.password_confirmation" style="
                    font-family:'Inter',sans-serif;font-size:12px;
                    color:var(--color-secondary);margin:0.35rem 0 0;
                ">{{ form.errors.password_confirmation }}</p>
            </div>

            <!-- CGU -->
            <div>
                <label style="
                    display:flex;align-items:flex-start;gap:0.625rem;cursor:pointer;
                ">
                    <input
                        type="checkbox"
                        v-model="form.terms"
                        required
                        style="
                            width:16px;height:16px;margin-top:2px;
                            border-radius:4px;flex-shrink:0;
                            accent-color:var(--color-primary);cursor:pointer;
                        "
                    >
                    <span style="
                        font-family:'Inter',sans-serif;
                        font-size:13px;color:var(--text-secondary);line-height:1.5;
                    ">
                        J'ai lu et j'accepte les
                        <a :href="route('cgu')" target="_blank" rel="noopener" style="
                            color:var(--color-primary);font-weight:500;text-decoration:none;
                        " class="hover:underline">Conditions Générales d'Utilisation</a>
                        et la politique de confidentialité.
                    </span>
                </label>
                <p v-if="form.errors.terms" style="
                    font-family:'Inter',sans-serif;font-size:12px;
                    color:var(--color-secondary);margin:0.35rem 0 0;
                ">{{ form.errors.terms }}</p>
            </div>

            <!-- Consentement RGPD : partage des résultats avec le pro invitant.
                 Facultatif — refuser n'empêche pas l'inscription (consentement libre). -->
            <div v-if="viaInvitation">
                <label style="
                    display:flex;align-items:flex-start;gap:0.625rem;cursor:pointer;
                ">
                    <input
                        type="checkbox"
                        v-model="form.consent_share"
                        style="
                            width:16px;height:16px;margin-top:2px;
                            border-radius:4px;flex-shrink:0;
                            accent-color:var(--color-primary);cursor:pointer;
                        "
                    >
                    <span style="
                        font-family:'Inter',sans-serif;
                        font-size:13px;color:var(--text-secondary);line-height:1.5;
                    ">
                        J'accepte que mes résultats et synthèses soient partagés avec le
                        professionnel qui m'a invité(e) <span style="color:var(--text-muted)">(facultatif — vous
                        gardez l'accès à vos résultats dans tous les cas)</span>.
                    </span>
                </label>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing || !form.terms"
                class="pt-btn-primary lp-btn-primary"
                style="width:100%;padding:0.7rem 1.5rem;justify-content:center;margin-top:0.25rem"
            >
                <span v-if="form.processing">Création en cours…</span>
                <span v-else>{{ L.authSubmit }}</span>
            </button>
        </form>

        <!-- Lien connexion -->
        <p class="lp-anim-trust" style="
            text-align:center;margin-top:1.5rem;
            font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary);
        ">
            {{ L.authHaveAccount }}
            <Link :href="route('login')" style="
                color:var(--color-primary);font-weight:600;text-decoration:none;
            " class="hover:underline">{{ L.authLoginLink }}</Link>
        </p>
    </AuthLayout>
</template>

<style>
/* ── Quest cards — sélection hero ── */
.auth-quest-card {
    transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
}
.auth-quest-label:hover .auth-quest-card {
    border-color: rgba(166,117,32,0.45) !important;
    background: var(--bg-elevated) !important;
    transform: translateX(3px);
}
.auth-quest-card--selected {
    border-color: var(--color-primary) !important;
    border-width: 2px !important;
    background: var(--bg-elevated) !important;
    box-shadow: 0 2px 12px rgba(166,117,32,0.12);
}
.auth-quest-check {
    transition: border-color 0.18s ease, background 0.18s ease;
}
.auth-quest-card--selected .auth-quest-check {
    border-color: var(--color-primary) !important;
    background: rgba(166,117,32,0.08);
}

/* ── Sélecteur de parcours (inscription) ── */
.auth-parcours-opt {
    flex: 1;
    padding: 9px 8px;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    touch-action: manipulation;
}
.auth-parcours-opt:hover {
    background: var(--bg-elevated);
    color: var(--text-primary);
}
.auth-parcours-opt--active {
    background: var(--color-primary);
    color: var(--pt-white, #fff);
    font-weight: 600;
}
.auth-parcours-opt--active:hover {
    background: var(--color-primary-dark);
    color: var(--pt-white, #fff);
}
</style>
