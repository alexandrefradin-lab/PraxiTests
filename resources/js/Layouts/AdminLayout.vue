<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)

const nav = [
    { name: 'Tableau de bord', href: '/admin',           icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Conseiller',      href: '/admin/conseiller', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
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
    <div class="min-h-screen flex" style="background: var(--bg-base)">

        <!-- Sidebar — panneau encre (cohérent avec AuthLayout) -->
        <aside class="adm-sidebar w-64 flex-shrink-0 flex flex-col">
            <!-- Logo -->
            <Link href="/admin" class="adm-logo">
                <svg width="30" height="30" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0">
                    <circle cx="19" cy="19" r="17.5" stroke="var(--color-primary)" stroke-width="1"/>
                    <circle cx="19" cy="19" r="13" stroke="var(--color-primary)" stroke-width="0.5" opacity="0.5"/>
                    <polygon points="19,6 20.4,18 19,21 17.6,18" fill="var(--color-primary)"/>
                    <polygon points="19,32 20.4,20 19,17 17.6,20" fill="var(--color-primary)" opacity="0.35"/>
                    <circle cx="19" cy="19" r="2" fill="var(--color-primary)"/>
                </svg>
                <div style="display:flex; flex-direction:column; gap:1px; line-height:1">
                    <span style="font-family:var(--font-display); font-size:16px; font-weight:600; color:#F0E8D4; letter-spacing:-0.01em">PraxiQuest</span>
                    <span style="font-family:var(--font-data); font-size:9px; color:var(--color-primary); letter-spacing:0.16em; text-transform:uppercase">Administration</span>
                </div>
            </Link>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto py-4 px-3" style="display:flex; flex-direction:column; gap:2px">
                <Link
                    v-for="item in nav"
                    :key="item.name"
                    :href="item.href"
                    class="adm-nav-link"
                    :class="{ 'adm-nav-link--active': isActive(item.href) }"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User -->
            <div class="p-4" style="border-top:1px solid rgba(166,117,32,0.2)">
                <div class="flex items-center gap-3">
                    <div style="width:32px; height:32px; border-radius:50%; background:rgba(166,117,32,0.18); display:flex; align-items:center; justify-content:center; color:var(--color-primary); font-family:var(--font-data); font-size:12px; font-weight:700; flex-shrink:0">
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? 'A' }}
                    </div>
                    <div class="min-w-0">
                        <p style="font-family:var(--font-display); font-size:12px; font-weight:500; color:#F0E8D4" class="truncate">{{ user?.name ?? 'Admin' }}</p>
                        <p style="font-size:11px; color:rgba(240,232,212,0.45)" class="truncate">{{ user?.email ?? '' }}</p>
                    </div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="adm-logout"
                >
                    Se déconnecter
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top bar -->
            <header class="h-16 flex items-center px-6 flex-shrink-0" style="background:var(--bg-surface); border-bottom:1px solid var(--border-light)">
                <slot name="header" />
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.adm-sidebar {
    background: var(--color-accent);
    border-right: 1px solid rgba(166,117,32,0.2);
}
.adm-logo {
    height: 64px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 1.25rem;
    text-decoration: none;
    border-bottom: 1px solid rgba(166,117,32,0.2);
}
.adm-nav-link {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 9px 12px;
    border-radius: var(--r);
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 500;
    color: rgba(240,232,212,0.6);
    text-decoration: none;
    transition: color 0.15s, background 0.15s;
}
.adm-nav-link:hover {
    color: #F0E8D4;
    background: rgba(166,117,32,0.12);
}
.adm-nav-link--active {
    color: var(--color-primary);
    font-weight: 700;
    background: rgba(166,117,32,0.16);
}
.adm-logout {
    margin-top: 0.75rem;
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
    font-size: 12px;
    color: rgba(240,232,212,0.45);
    transition: color 0.15s;
}
.adm-logout:hover {
    color: rgba(240,232,212,0.8);
}
</style>
