<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const branding = computed(() => page.props.branding ?? { name: 'PraxiTests' })
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <header class="bg-white border-b border-slate-100">
            <div class="mx-auto max-w-6xl px-6 py-4 flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center gap-2 text-slate-900 font-semibold tracking-tight">
                    <span class="inline-block h-7 w-7 rounded-md bg-gradient-to-br from-indigo-500 to-emerald-500"></span>
                    {{ branding.name }}
                </Link>
                <nav class="flex items-center gap-4 text-sm text-slate-600" v-if="user">
                    <Link :href="route('tests.index')" class="hover:text-slate-900">Mes tests</Link>
                    <span class="text-slate-300">|</span>
                    <span>{{ user.name }}</span>
                    <Link :href="route('logout')" method="post" as="button" class="text-slate-500 hover:text-slate-900">Déconnexion</Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10">
            <slot />
        </main>

        <footer class="text-center text-xs text-slate-400 py-10">
            {{ branding.name }} — Évaluer. Orienter. Transformer.
        </footer>
    </div>
</template>
