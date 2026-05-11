<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const branding = computed(() => usePage().props.branding ?? { name: 'PraxiTests' })
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-white to-indigo-50 px-6">
        <div class="w-full max-w-md">
            <Link href="/" class="flex items-center justify-center gap-2 mb-10 text-slate-900 font-semibold tracking-tight">
                <span class="inline-block h-9 w-9 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-500 shadow-md"></span>
                <span class="text-lg">{{ branding.name }}</span>
            </Link>
            <div class="pt-card p-8">
                <slot />
            </div>
            <p class="text-center text-xs text-slate-400 mt-6">{{ branding.tagline }}</p>
        </div>
    </div>
</template>
