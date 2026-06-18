<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)

const nav = [
    { name: 'Tableau de bord', href: '/admin',           icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Tests',           href: '/admin/tests',      icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { name: 'Campagnes',       href: '/admin/campaigns',  icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { name: 'Leads',           href: '/admin/leads',      icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { name: 'Plugins',         href: '/admin/plugins',        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { name: 'Abonnements',     href: '/admin/subscriptions',  icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
]

const isActive = (href) => {
    if (href === '/admin') return page.url === '/' || page.url === ''
    return page.url.startsWith(href.replace('/admin', ''))
}
</script>

<template>
    <div class="min-h-screen flex" style="background: var(--pt-cream)">

        <!-- Sidebar -->
        <aside class="w-64 flex-shrink-0 flex flex-col" style="background: var(--pt-navy)">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-white/10">
                <span class="text-white font-bold text-lg tracking-tight">PraxiQuest</span>
                <span class="ml-2 text-xs text-white/40 font-mono">admin</span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">
                <Link
                    v-for="item in nav"
                    :key="item.name"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors"
                    :class="isActive(item.href)
                        ? 'bg-white/15 text-white font-medium'
                        : 'text-white/60 hover:bg-white/10 hover:text-white'"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User -->
            <div class="p-4 border-t border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? 'A' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-white text-xs font-medium truncate">{{ user?.name ?? 'Admin' }}</p>
                        <p class="text-white/40 text-xs truncate">{{ user?.email ?? '' }}</p>
                    </div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="mt-3 w-full text-left text-xs text-white/40 hover:text-white/70 transition-colors"
                >
                    Se déconnecter
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar -->
            <header class="h-16 flex items-center px-6 bg-white border-b border-slate-100 flex-shrink-0">
                <slot name="header" />
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
