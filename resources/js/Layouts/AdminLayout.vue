<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const isAdmin = computed(() => page.props.auth?.is_admin ?? false)

// Badges d'incident (ex : synthèses IA en échec) — prop partagée admin_alerts
const alertCounts = computed(() => page.props.admin_alerts ?? {})

// adminOnly : entrées masquées aux professionnels (routes role:admin).
// La sécurité reste portée par le middleware — ici c'est de l'affichage.
const allNav = [
    { name: 'Tableau de bord', href: '/admin',           icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Conseiller',      href: '/admin/conseiller', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
    { name: 'Invitations',     href: '/admin/invitations', icon: 'M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844a2.25 2.25 0 011.183-1.98l7.5-4.04a2.25 2.25 0 012.134 0l7.5 4.04a2.25 2.25 0 011.183 1.98V19.5z' },
    { name: 'Tests',           href: '/admin/tests',      icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', adminOnly: true },
    { name: 'Campagnes',       href: '/admin/campaigns',  icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { name: 'Leads',           href: '/admin/leads',      icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { name: 'Utilisateurs',    href: '/admin/users',      icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z', adminOnly: true },
    { name: 'Plugins',         href: '/admin/plugins',        icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', adminOnly: true },
    { name: 'Abonnements',     href: '/admin/subscriptions',  icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', adminOnly: true },
    { name: 'Synthèses IA',    href: '/admin/attempts/failed-insights', badge: 'failed_insights', icon: 'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z', adminOnly: true },
    { name: 'Journal d\'audit', href: '/admin/audit-logs',    icon: 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25', adminOnly: true },
    { name: 'Réglages',        href: '/admin/settings',       icon: 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z M15 12a3 3 0 11-6 0 3 3 0 016 0z', adminOnly: true },
]

const nav = computed(() => allNav.filter(item => !item.adminOnly || isAdmin.value))

const isActive = (href) => {
    if (href === '/admin') return page.url === '/admin' || page.url.startsWith('/admin?')
    return page.url.startsWith(href)
}

// Fil d'ariane (2 niveaux) déduit de l'entrée de nav active
const currentSection = computed(() =>
    nav.value.find(item => item.href !== '/admin' && isActive(item.href))
    ?? nav.value.find(item => isActive(item.href)),
)
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
                    <span class="flex-1">{{ item.name }}</span>
                    <span
                        v-if="item.badge && alertCounts[item.badge] > 0"
                        class="adm-nav-badge"
                        :aria-label="`${alertCounts[item.badge]} à traiter`"
                    >{{ alertCounts[item.badge] }}</span>
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
            <!-- Top bar : fil d'ariane + lien vers le site (le slot header garde la priorité) -->
            <header class="h-16 flex items-center gap-4 px-6 flex-shrink-0" style="background:var(--bg-surface); border-bottom:1px solid var(--border-light)">
                <slot name="header">
                    <nav aria-label="Fil d'ariane" class="flex items-center gap-2 text-sm" style="color:var(--text-muted)">
                        <Link href="/admin" class="hover:underline" style="color:var(--text-muted)">Administration</Link>
                        <template v-if="currentSection && currentSection.href !== '/admin'">
                            <span aria-hidden="true">›</span>
                            <span style="color:var(--text-primary);font-family:var(--font-display)">{{ currentSection.name }}</span>
                        </template>
                    </nav>
                </slot>
                <a href="/" target="_blank" rel="noopener" class="ml-auto text-sm hover:underline" style="color:var(--color-primary)">
                    Voir le site ↗
                </a>
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
.adm-nav-badge {
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--color-danger, #B3402A);
    color: #FFF;
    font-family: var(--font-data);
    font-size: 10px;
    font-weight: 700;
    flex-shrink: 0;
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
