<script setup>
import { Link } from '@inertiajs/vue3'

const nav = [
    { name: 'Tableau de bord', href: '/admin' },
    { name: 'Tests',            href: '/admin/tests' },
    { name: 'Campagnes',        href: '/admin/campaigns' },
    { name: 'Leads',            href: '/admin/leads' },
    { name: 'Plugins',          href: '/admin/plugins' },
]
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <aside class="w-60 bg-white border-r border-slate-100 px-4 py-6">
            <Link href="/" class="flex items-center gap-2 px-2 mb-8 text-slate-900 font-semibold">
                <span class="inline-block h-7 w-7 rounded-md bg-gradient-to-br from-indigo-500 to-emerald-500"></span>
                Admin
            </Link>
            <nav class="space-y-1">
                <Link v-for="item in nav" :key="item.href" :href="item.href" class="block px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-100">
                    {{ item.name }}
                </Link>
            </nav>
        </aside>
        <main class="flex-1 p-10">
            <slot />
        </main>
    </div>
</template>
