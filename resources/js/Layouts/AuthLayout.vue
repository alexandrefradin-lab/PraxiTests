<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const branding = computed(() => usePage().props.branding ?? { name: 'PraxiQuest', tagline: 'Évaluer. Orienter. Transformer.' })
</script>

<template>
    <div class="min-h-screen flex" style="background: var(--bg-base)">

        <!-- Panneau gauche — Encre / identité AC -->
        <div class="hidden lg:flex flex-col justify-between" style="width: 420px; flex-shrink: 0; background: var(--color-accent); padding: 3rem 3rem 2.5rem; position: relative; overflow: hidden">

            <!-- Ligne décorative verticale droite -->
            <div style="position: absolute; top: 0; right: 0; width: 1px; height: 100%; background: linear-gradient(to bottom, transparent, rgba(166,117,32,0.3) 20%, rgba(166,117,32,0.3) 80%, transparent)"></div>

            <!-- Fond boussole décoratif -->
            <svg style="position:absolute;bottom:-60px;right:-60px;opacity:0.055;pointer-events:none;width:340px;height:340px" viewBox="0 0 220 220">
                <circle cx="110" cy="110" r="100" fill="none" stroke="#A67520" stroke-width="1"/>
                <circle cx="110" cy="110" r="70" fill="none" stroke="#A67520" stroke-width="0.6"/>
                <circle cx="110" cy="110" r="40" fill="none" stroke="#A67520" stroke-width="0.4"/>
                <line x1="110" y1="10" x2="110" y2="210" stroke="#A67520" stroke-width="0.8"/>
                <line x1="10" y1="110" x2="210" y2="110" stroke="#A67520" stroke-width="0.8"/>
                <polygon points="110,12 113,26 110,22 107,26" fill="#A67520"/>
            </svg>

            <!-- Logo -->
            <Link href="/" style="display: flex; align-items: center; gap: 14px; text-decoration: none; position: relative; z-index: 1">
                <!-- Boussole: fond or + anneau + aiguille ivoire bien visible -->
                <div style="width:44px;height:44px;border-radius:50%;background:rgba(166,117,32,0.25);border:2px solid #A67520;display:flex;align-items:center;justify-content:center;position:relative;flex-shrink:0">
                    <!-- Aiguille nord ivoire -->
                    <svg width="44" height="44" viewBox="0 0 44 44" style="position:absolute;top:0;left:0" fill="none">
                        <polygon points="22,6 24.5,20 22,24 19.5,20" fill="#F0E8D4"/>
                        <polygon points="22,38 24.5,24 22,20 19.5,24" fill="#A67520" opacity="0.6"/>
                        <circle cx="22" cy="22" r="4" fill="#1C1408" stroke="#A67520" stroke-width="1.5"/>
                        <circle cx="22" cy="22" r="1.5" fill="#A67520"/>
                    </svg>
                </div>
                <div>
                    <div style="font-family: var(--font-display); font-size: 17px; font-weight: 700; color: #F0E8D4; letter-spacing: -0.01em; line-height: 1">{{ branding.name }}</div>
                    <div style="font-family: var(--font-data); font-size: 10px; color: #A67520; text-transform: uppercase; letter-spacing: 0.14em; margin-top: 4px">Voyage intérieur</div>
                </div>
            </Link>

            <!-- Citation centrale -->
            <div style="position: relative; z-index: 1">
                <div style="display:inline-flex;align-items:center;gap:8px;font-family:var(--font-data);font-size:10px;letter-spacing:0.12em;color:#A67520;text-transform:uppercase;padding:5px 14px 5px 10px;border:1px solid rgba(166,117,32,0.45);border-radius:4px;background:rgba(166,117,32,0.12);margin-bottom:1.5rem">
                    <div style="width:6px;height:6px;background:#A67520;transform:rotate(45deg);flex-shrink:0"></div>
                    Augmenté par IA
                </div>
                <blockquote style="font-family: var(--font-display); font-size: 22px; font-weight: 400; color: #F0E8D4; line-height: 1.5; letter-spacing: -0.015em; margin-bottom: 1.25rem">
                    « Connaître ses forces, c'est choisir sa trajectoire. »
                </blockquote>
                <div style="width: 32px; height: 1px; background: #A67520; opacity: 0.5; margin-bottom: 1rem"></div>
                <p style="font-family: var(--font-body); font-size: 12px; color: rgba(240,232,212,0.5); font-style: italic; line-height: 1.6">
                    Synthèse IA · Bilan de compétences · Orientation experte
                </p>
            </div>

            <!-- Bas — Bullets qualité -->
            <div style="display: flex; flex-direction: column; gap: 14px; position: relative; z-index: 1">
                <div v-for="bullet in [
                    'Analyse IA multi-dimensions',
                    '15 pistes métiers personnalisées',
                    'Rapport PDF confidentiel',
                ]" :key="bullet" style="display: flex; align-items: center; gap: 12px">
                    <div style="width:7px;height:7px;background:#A67520;transform:rotate(45deg);flex-shrink:0"></div>
                    <span style="font-family: var(--font-body); font-size: 13px; color: rgba(240,232,212,0.75); letter-spacing: 0.01em">{{ bullet }}</span>
                </div>
            </div>
        </div>

        <!-- Panneau droit — Formulaire sur parchemin -->
        <div class="flex-1 flex flex-col items-center justify-center" style="padding: 3rem 2rem; background: var(--bg-base)">

            <!-- Logo mobile uniquement -->
            <Link href="/" class="lg:hidden mb-10 flex items-center gap-3" style="text-decoration: none">
                <div style="width:38px;height:38px;border-radius:50%;background:rgba(166,117,32,0.12);border:2px solid var(--color-primary);display:flex;align-items:center;justify-content:center;position:relative;flex-shrink:0">
                    <svg width="38" height="38" viewBox="0 0 38 38" style="position:absolute;top:0;left:0" fill="none">
                        <polygon points="19,5 21.5,17.5 19,21 16.5,17.5" fill="#A67520"/>
                        <polygon points="19,33 21.5,20.5 19,17 16.5,20.5" fill="#A67520" opacity="0.4"/>
                        <circle cx="19" cy="19" r="3.5" fill="var(--bg-base)" stroke="var(--color-primary)" stroke-width="1.5"/>
                        <circle cx="19" cy="19" r="1.5" fill="var(--color-primary)"/>
                    </svg>
                </div>
                <div>
                    <div style="font-family: var(--font-display); font-size: 15px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.01em; line-height: 1">{{ branding.name }}</div>
                    <div style="font-family: var(--font-data); font-size: 9px; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.14em; margin-top: 3px">Voyage intérieur</div>
                </div>
            </Link>

            <div style="width: 100%; max-width: 400px">
                <slot />
            </div>

            <p style="font-family: var(--font-data); font-size: 11px; color: var(--text-muted); margin-top: 2.5rem; text-align: center; letter-spacing: 0.03em">
                {{ branding.tagline }}
            </p>
        </div>
    </div>
</template>
