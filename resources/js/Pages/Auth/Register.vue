<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const props = defineProps({ email: String })

const form = useForm({
    name: '',
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
    terms: false,
    quest_title: '',
})

const submit = () => form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
})

const questOptions = [
    {
        value: 'architecte',
        label: "L'Architecte",
        description: 'Tu construis des systèmes, tu penses en structures.',
    },
    {
        value: 'explorateur',
        label: "L'Explorateur",
        description: 'Tu cherches, tu questionnes, tu aimes les possibilités.',
    },
    {
        value: 'passeur',
        label: 'Le Passeur',
        description: 'Tu transmets, tu connectes, tu fais grandir.',
    },
]
</script>

<template>
    <AuthLayout>
        <Head title="Créer mon Identité de Héros — PraxiQuest" />

        <!-- En-tête -->
        <div style="margin-bottom:1.75rem">
            <h1 style="
                font-family:'Space Grotesk',sans-serif;
                font-size:1.625rem;font-weight:700;
                letter-spacing:-0.02em;line-height:1.15;
                color:var(--color-accent);margin:0 0 0.5rem;
            ">Créer mon Identité de Héros</h1>
            <p style="
                font-family:'Inter',sans-serif;
                font-size:14px;color:var(--text-secondary);margin:0;line-height:1.5;
            ">2 minutes. La première Épreuve est offerte.</p>
        </div>

        <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:1.125rem">

            <!-- BLOC NEUROMARKETING : Ancrage identitaire précoce -->
            <div>
                <p style="
                    font-family:'Space Grotesk',sans-serif;
                    font-size:13px;font-weight:600;
                    color:var(--text-primary);
                    margin:0 0 0.75rem;letter-spacing:0.01em;
                ">Choisis ton titre de Héros</p>

                <div style="display:flex;flex-direction:column;gap:0.625rem">
                    <label
                        v-for="opt in questOptions"
                        :key="opt.value"
                        style="cursor:pointer;display:block"
                    >
                        <input
                            type="radio"
                            :value="opt.value"
                            v-model="form.quest_title"
                            style="position:absolute;opacity:0;pointer-events:none;width:0;height:0"
                        >
                        <div style="
                            padding:0.875rem 1rem;
                            border-radius:8px;
                            border:1.5px solid var(--glass-border);
                            background:var(--bg-surface);
                            transition:border-color .15s, background .15s;
                            display:flex;flex-direction:column;gap:0.2rem;
                        " :style="form.quest_title === opt.value ? {
                            borderColor: 'var(--color-primary)',
                            background: 'var(--bg-elevated)',
                            borderWidth: '2px',
                        } : {}">
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
                " for="register-name">Ton nom dans la Quête</label>
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
                " for="register-email">Adresse du Héros</label>
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
                " for="register-password">Sceau secret</label>
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
                " for="register-password-confirm">Confirmer le Sceau</label>
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

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing || !form.terms"
                class="pt-btn-primary"
                style="width:100%;padding:0.7rem 1.5rem;justify-content:center;margin-top:0.25rem"
            >
                <span v-if="form.processing">Création en cours…</span>
                <span v-else>Commencer la Quête</span>
            </button>
        </form>

        <!-- Lien connexion -->
        <p style="
            text-align:center;margin-top:1.5rem;
            font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary);
        ">
            Déjà un Héros ?
            <Link :href="route('login')" style="
                color:var(--color-primary);font-weight:600;text-decoration:none;
            " class="hover:underline">→ Entrer dans la Quête</Link>
        </p>
    </AuthLayout>
</template>
