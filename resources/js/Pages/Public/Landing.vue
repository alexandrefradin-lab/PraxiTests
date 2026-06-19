<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const branding = computed(() => usePage().props.branding ?? { name: 'PraxiQuest' })

const pillars = [
    {
        title: 'IA experte',
        accent: 'from-indigo-500 to-violet-500',
        text: 'Synthèse personnalisée de ton profil + 15 métiers réalistes proposés à partir de tes réponses et de ton CV.',
        icon: 'ti-brain',
        iconColor: 'var(--color-primary)',
        iconBg: 'rgba(166,117,32,0.12)',
    },
    {
        title: 'Gamification subtile',
        accent: 'from-emerald-500 to-teal-500',
        text: "XP, badges, narration. Tu progresses dans un parcours qui donne envie d'aller au bout. Sans bruit, sans gadget.",
        icon: 'ti-sword',
        iconColor: 'var(--color-success)',
        iconBg: 'rgba(58,107,72,0.12)',
    },
    {
        title: 'Neuromarketing',
        accent: 'from-amber-500 to-rose-500',
        text: 'Chaque écran, chaque email est optimisé pour transformer une exploration en décision concrète.',
        icon: 'ti-sparkles',
        iconColor: 'var(--color-signal)',
        iconBg: 'rgba(10,127,160,0.12)',
    },
]
</script>

<template>
    <div style="min-height:100vh;background:var(--bg-base);color:var(--text-primary)">
        <Head title="Deviens le personnage que tu n'avais pas encore nommé — PraxiQuest" />

        <!-- Header glassmorphism sticky -->
        <header style="
            position:sticky;top:0;z-index:50;
            background:var(--glass-bg);
            backdrop-filter:blur(14px);
            -webkit-backdrop-filter:blur(14px);
            border-bottom:1px solid var(--glass-border);
        ">
            <div style="max-width:1152px;margin:0 auto;padding:0 1.5rem;height:68px;display:flex;align-items:center;justify-content:space-between">
                <Link href="/" style="display:flex;align-items:center;gap:0.625rem;text-decoration:none">
                    <span style="
                        display:inline-flex;align-items:center;justify-content:center;
                        width:36px;height:36px;border-radius:8px;
                        background:var(--color-primary);
                        color:var(--bg-base);
                        font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:18px;
                    ">P</span>
                    <span style="font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:16px;color:var(--text-primary);letter-spacing:-0.01em">
                        {{ branding.name }}
                    </span>
                </Link>
                <nav style="display:flex;align-items:center;gap:0.5rem">
                    <Link :href="route('login')" class="pt-btn-ghost" style="font-size:14px;padding:0.5rem 1.1rem">
                        Entrer dans la Quête
                    </Link>
                    <Link :href="route('register')" class="pt-btn-primary" style="font-size:14px;padding:0.5rem 1.25rem">
                        Commencer l'Épreuve
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Hero RPG -->
        <section style="padding:6rem 1.5rem 5rem;text-align:center;position:relative;overflow:hidden">
            <!-- Texture de fond subtile -->
            <div style="
                position:absolute;inset:0;pointer-events:none;
                background:radial-gradient(ellipse 80% 60% at 50% 0%, rgba(166,117,32,0.08) 0%, transparent 70%);
            "></div>

            <div style="position:relative;max-width:760px;margin:0 auto">
                <!-- Pill badge -->
                <div style="
                    display:inline-block;
                    font-family:'Space Mono',monospace;font-size:11px;letter-spacing:0.06em;
                    color:var(--color-primary);
                    border:1px solid var(--glass-border);
                    border-radius:999px;padding:0.35rem 1rem;
                    background:rgba(166,117,32,0.07);
                    margin-bottom:1.75rem;
                ">✦ Quête de soi · IA · Gamification</div>

                <!-- Ligne décorative -->
                <div style="width:40px;height:2px;background:var(--color-primary);margin:0 auto 1.5rem"></div>

                <!-- H1 -->
                <h1 style="
                    font-family:'Space Grotesk',sans-serif;
                    font-size:clamp(2.2rem,5vw,3.2rem);
                    font-weight:700;
                    line-height:1.1;
                    letter-spacing:-0.02em;
                    color:var(--text-primary);
                    margin:0 0 1.5rem;
                ">
                    Deviens le personnage<br>que tu n'avais pas encore
                    <span style="color:var(--color-primary)">nommé</span>.
                </h1>

                <!-- Sous-titre -->
                <p style="
                    font-family:'Inter',sans-serif;
                    font-size:1.125rem;
                    line-height:1.65;
                    color:var(--text-secondary);
                    max-width:560px;margin:0 auto 2.5rem;
                ">
                    PraxiQuest révèle qui tu es, pour que tu choisisses où tu vas.
                </p>

                <!-- CTAs -->
                <div style="display:flex;align-items:center;justify-content:center;gap:0.75rem;flex-wrap:wrap">
                    <Link :href="route('register')" class="pt-btn-primary" style="font-size:15px;padding:0.75rem 1.75rem">
                        Commencer l'Épreuve gratuite
                    </Link>
                    <a href="#pillars" class="pt-btn-ghost" style="font-size:15px;padding:0.75rem 1.5rem">
                        Voir comment ça marche
                    </a>
                </div>

                <!-- Micro-texte -->
                <p style="
                    font-family:'Space Mono',monospace;
                    font-size:11px;letter-spacing:0.03em;
                    color:var(--text-secondary);opacity:0.7;
                    margin-top:1.25rem;
                ">≈ 8 min · Aucune carte bancaire · RGPD</p>
            </div>
        </section>

        <!-- Trust strip -->
        <section style="padding:0 1.5rem 4rem">
            <div style="max-width:760px;margin:0 auto">
                <div class="pt-card" style="
                    background:var(--bg-surface);
                    display:grid;grid-template-columns:repeat(3,1fr);
                    text-align:center;
                    overflow:hidden;
                ">
                    <div style="padding:2rem 1.5rem">
                        <p style="font-family:'Space Mono',monospace;font-size:2.25rem;font-weight:700;color:var(--color-primary);line-height:1;margin:0">15</p>
                        <p style="font-family:'Inter',sans-serif;font-size:12px;color:var(--text-secondary);margin:0.5rem 0 0;line-height:1.4">Voies Possibles<br>générées</p>
                    </div>
                    <div style="padding:2rem 1.5rem;border-left:1px solid var(--glass-border);border-right:1px solid var(--glass-border)">
                        <p style="font-family:'Space Mono',monospace;font-size:2.25rem;font-weight:700;color:var(--color-primary);line-height:1;margin:0">8 min</p>
                        <p style="font-family:'Inter',sans-serif;font-size:12px;color:var(--text-secondary);margin:0.5rem 0 0;line-height:1.4">de Quête<br>engageante</p>
                    </div>
                    <div style="padding:2rem 1.5rem">
                        <p style="font-family:'Space Mono',monospace;font-size:2.25rem;font-weight:700;color:var(--color-primary);line-height:1;margin:0">IA</p>
                        <p style="font-family:'Inter',sans-serif;font-size:12px;color:var(--text-secondary);margin:0.5rem 0 0;line-height:1.4">Grimoire de Synthèse<br>inclus</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3 Piliers -->
        <section id="pillars" style="padding:4rem 1.5rem 5rem">
            <div style="max-width:1152px;margin:0 auto">
                <div style="text-align:center;margin-bottom:3.5rem">
                    <!-- Ligne décorative -->
                    <div style="width:32px;height:2px;background:var(--color-primary);margin:0 auto 1.25rem"></div>
                    <h2 style="
                        font-family:'Space Grotesk',sans-serif;
                        font-size:clamp(1.75rem,3.5vw,2.25rem);
                        font-weight:700;letter-spacing:-0.02em;
                        color:var(--text-primary);margin:0 0 0.75rem;
                    ">Trois leviers, une expérience.</h2>
                    <p style="font-family:'Inter',sans-serif;font-size:15px;color:var(--text-secondary);max-width:480px;margin:0 auto;line-height:1.65">
                        Là où les bilans classiques s'arrêtent à un PDF, PraxiQuest construit une expérience qui engage et oriente vraiment.
                    </p>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem">
                    <article
                        v-for="(p, i) in pillars"
                        :key="i"
                        class="pt-card"
                        style="background:var(--bg-surface);padding:2rem"
                    >
                        <div style="
                            width:36px;height:36px;border-radius:8px;
                            display:flex;align-items:center;justify-content:center;
                            margin-bottom:1.25rem;flex-shrink:0;
                        " :style="{ background: p.iconBg }">
                            <i :class="['ti', p.icon]" style="font-size:18px" :style="{ color: p.iconColor }"></i>
                        </div>
                        <h3 style="
                            font-family:'Space Grotesk',sans-serif;
                            font-size:15px;font-weight:700;
                            color:var(--text-primary);margin:0 0 0.625rem;
                        ">{{ p.title }}</h3>
                        <p style="
                            font-family:'Inter',sans-serif;
                            font-size:13px;color:var(--text-secondary);
                            line-height:1.65;margin:0;
                        ">{{ p.text }}</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- CTA final -->
        <section style="background:var(--bg-elevated);padding:5rem 1.5rem;text-align:center">
            <div style="max-width:600px;margin:0 auto">
                <div style="width:40px;height:2px;background:var(--color-primary);margin:0 auto 2rem"></div>

                <h2 style="
                    font-family:'Space Grotesk',sans-serif;
                    font-size:clamp(1.75rem,3.5vw,2.25rem);
                    font-weight:700;letter-spacing:-0.02em;
                    color:var(--text-primary);margin:0 0 1rem;
                ">Prêt·e à voir ton profil autrement ?</h2>

                <p style="font-family:'Inter',sans-serif;font-size:15px;color:var(--text-secondary);margin:0 0 2rem;line-height:1.6">
                    La première Épreuve est offerte. Tu repars avec ta révélation et tes 15 Voies Possibles.
                </p>

                <Link :href="route('register')" style="
                    display:inline-block;
                    font-family:'Space Grotesk',sans-serif;font-size:15px;font-weight:600;
                    background:var(--color-accent);color:var(--bg-base);
                    padding:0.85rem 2rem;border-radius:8px;
                    text-decoration:none;letter-spacing:0.01em;
                    border:none;cursor:pointer;
                ">
                    Entrer dans la Quête →
                </Link>

                <p style="
                    font-family:'Space Mono',monospace;
                    font-size:11px;letter-spacing:0.03em;
                    color:var(--text-secondary);opacity:0.6;
                    margin-top:1.25rem;
                ">RGPD · Données chiffrées · Aucune carte bancaire</p>
            </div>
        </section>

        <!-- Footer -->
        <footer style="border-top:1px solid var(--glass-border);padding:2.25rem 1.5rem">
            <div style="
                max-width:1152px;margin:0 auto;
                display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;
                font-family:'Space Mono',monospace;font-size:11px;color:var(--text-secondary);
            ">
                <p style="margin:0">© {{ new Date().getFullYear() }} {{ branding.name }} — Tous droits réservés.</p>
                <p style="margin:0;opacity:0.75">Deviens le personnage que tu n'avais pas encore nommé.</p>
            </div>
        </footer>
    </div>
</template>
