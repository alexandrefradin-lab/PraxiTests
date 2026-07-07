<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { useParcours } from '@/composables/useParcours'

const { L } = useParcours()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const showPassword = ref(false)
const submit = () => form.post(route('login'), { onFinish: () => form.reset('password') })
</script>

<template>
    <AuthLayout>
        <Head :title="`${L.authLoginTitle} — PraxiQuest`" />

        <!-- En-tête -->
        <div class="lp-anim-badge" style="margin-bottom:2rem">
            <h1 class="lp-h1-gradient lp-anim-h1a" style="
                font-family:var(--font-display);
                font-size:1.625rem;font-weight:700;
                letter-spacing:-0.02em;line-height:1.15;
                margin:0 0 0.5rem;
            ">{{ L.authLoginTitle }}</h1>
            <p class="lp-anim-sub" style="
                font-family:'Inter',sans-serif;
                font-size:14px;color:var(--text-secondary);margin:0;line-height:1.5;
            ">{{ L.authLoginSubtitle }}</p>
        </div>

        <!-- Flash success -->
        <div v-if="$page.props.flash?.success" class="pt-flash-success" style="margin-bottom:1.25rem">
            {{ $page.props.flash.success }}
        </div>

        <form @submit.prevent="submit" style="display:flex;flex-direction:column;gap:1rem">

            <!-- Email -->
            <div>
                <label for="login-email" style="
                    display:block;
                    font-family:'Inter',sans-serif;font-size:13px;font-weight:500;
                    color:var(--text-secondary);margin-bottom:0.4rem;
                ">{{ L.authEmail }}</label>
                <input
                    id="login-email"
                    type="email"
                    v-model="form.email"
                    autofocus
                    required
                    autocomplete="username"
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
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem">
                    <label for="login-password" style="
                        font-family:'Inter',sans-serif;font-size:13px;font-weight:500;
                        color:var(--text-secondary);
                    ">{{ L.authPassword }}</label>
                    <Link :href="route('password.request')" style="
                        font-family:'Inter',sans-serif;font-size:12px;
                        color:var(--color-primary);text-decoration:none;
                    " class="hover:underline">{{ L.authForgot }}</Link>
                </div>
                <div style="position:relative;">
                    <input
                        id="login-password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        class="pt-input"
                        placeholder="••••••••"
                        style="padding-right:2.5rem;"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        tabindex="-1"
                        style="position:absolute;right:0.65rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0;display:flex;align-items:center;"
                    >
                        <i :class="showPassword ? 'ti ti-eye-off' : 'ti ti-eye'" style="font-size:16px;"></i>
                    </button>
                </div>
                <p v-if="form.errors.password" style="
                    font-family:'Inter',sans-serif;font-size:12px;
                    color:var(--color-secondary);margin:0.35rem 0 0;
                ">{{ form.errors.password }}</p>
            </div>

            <!-- Remember me -->
            <label style="
                display:flex;align-items:center;gap:0.625rem;cursor:pointer;
                font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary);
            ">
                <input
                    type="checkbox"
                    v-model="form.remember"
                    style="
                        width:16px;height:16px;border-radius:4px;flex-shrink:0;
                        accent-color:var(--color-primary);cursor:pointer;
                    "
                >
                {{ L.authRemember }}
            </label>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="pt-btn-primary"
                style="width:100%;padding:0.7rem 1.5rem;justify-content:center;margin-top:0.25rem"
            >
                <span v-if="form.processing">Connexion en cours…</span>
                <span v-else>{{ L.authLoginSubmit }}</span>
            </button>
        </form>

        <!-- Lien inscription -->
        <p style="
            text-align:center;margin-top:1.5rem;
            font-family:'Inter',sans-serif;font-size:13px;color:var(--text-secondary);
        ">
            Pas encore inscrit ?
            <Link :href="route('register')" style="
                color:var(--color-primary);font-weight:600;text-decoration:none;
            " class="hover:underline">{{ L.authRegisterLink }}</Link>
        </p>
    </AuthLayout>
</template>
