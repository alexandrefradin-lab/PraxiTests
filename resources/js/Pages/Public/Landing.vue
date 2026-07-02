<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'

const branding = computed(() => usePage().props.branding ?? { name: 'PraxiQuest' })

onMounted(() => {
  function countUp(el, target, suffix, duration) {
    if (!el) return
    let start = 0
    const step = target / (duration / 16)
    const t = setInterval(() => {
      start = Math.min(start + step, target)
      el.textContent = Math.round(start) + suffix
      if (start >= target) clearInterval(t)
    }, 16)
  }
  setTimeout(() => {
    countUp(document.getElementById('lp-stat-epreuves'), 15, '', 1100)
    countUp(document.getElementById('lp-stat-pistes'), 50, '', 1300)
    countUp(document.getElementById('lp-stat-modules'), 10, '', 1500)
  }, 500)
})
</script>

<template>
<!-- SEO (cf. audit MK-6) : titre, description et cartes de partage Open Graph/Twitter. -->
<Head title="PraxiQuest — Test d'orientation et de reconversion professionnelle">
  <meta name="description" content="PraxiQuest : 15 épreuves fondées sur des modèles reconnus (Big Five, RIASEC, valeurs de Schwartz, MBI…), une relecture globale par IA, l'impact de l'IA sur votre métier, jusqu'à 50 pistes de métiers réalistes et 10 modules d'entraînement offerts. Idéal pour la reconversion et l'évolution professionnelle." />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="PraxiQuest — Découvrez votre potentiel professionnel" />
  <meta property="og:description" content="Tests d'orientation augmentés par l'IA : relecture globale de votre profil, votre métier face à l'IA, jusqu'à 50 pistes de métiers réalistes et des modules d'entraînement offerts." />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="PraxiQuest — Découvrez votre potentiel professionnel" />
  <meta name="twitter:description" content="Tests d'orientation augmentés par l'IA : relecture globale, votre métier face à l'IA et jusqu'à 50 pistes de métiers réalistes." />
</Head>
<div style="font-family:var(--font-body,Inter,sans-serif);background:var(--bg-base,#F0E8D4);min-height:100vh;color:var(--text-primary,#2A1E08);overflow-x:hidden">

  <!-- NAV -->
  <nav class="lp-nav" style="background:rgba(240,232,212,0.88);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);padding:0 2.5rem;display:flex;align-items:center;justify-content:space-between;height:64px;border-bottom:1px solid rgba(166,117,32,0.2);position:sticky;top:0;z-index:100">
    <div style="display:flex;align-items:center;gap:11px">
      <!-- Icone cercle boussole -->
      <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0">
        <circle cx="19" cy="19" r="17.5" stroke="#A67520" stroke-width="1"/>
        <circle cx="19" cy="19" r="13" stroke="#A67520" stroke-width="0.5" opacity="0.5"/>
        <polygon points="19,6 20.4,18 19,21 17.6,18" fill="#A67520"/>
        <polygon points="19,32 20.4,20 19,17 17.6,20" fill="#A67520" opacity="0.35"/>
        <circle cx="19" cy="19" r="2" fill="#A67520"/>
        <circle cx="19" cy="19" r="1" fill="#F0E8D4"/>
      </svg>
      <!-- Texte logo -->
      <div style="display:flex;flex-direction:column;gap:1px">
        <span style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:16px;font-weight:600;color:var(--text-primary,#2A1E08);letter-spacing:-0.01em;line-height:1">{{ branding.name }}</span>
        <span style="font-family:var(--font-data,'Space Mono',monospace);font-size:9px;font-weight:400;color:var(--color-primary,#A67520);letter-spacing:0.14em;text-transform:uppercase;line-height:1">Voyage interieur</span>
      </div>
    </div>
    <div class="lp-nav-actions" style="display:flex;align-items:center;gap:8px">
      <Link href="/login" class="lp-nav-login" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:500;color:var(--text-secondary,#6B5A3E);text-decoration:none;padding:6px 14px;border-radius:6px;transition:background 0.15s" onmouseenter="this.style.background='rgba(166,117,32,0.08)'" onmouseleave="this.style.background='transparent'">Connexion</Link>
      <Link href="/register" class="lp-nav-cta" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:600;letter-spacing:-0.01em;color:var(--bg-base,#F0E8D4);background:var(--color-accent,#1C1408);border:none;padding:8px 20px;border-radius:6px;text-decoration:none;display:inline-block;box-shadow:0 2px 8px rgba(42,30,8,0.2);white-space:nowrap">Partir a l'aventure</Link>
    </div>
  </nav>

  <!-- HERO -->
  <section style="position:relative;overflow:hidden;background:var(--bg-base,#F0E8D4)">
    <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.07;pointer-events:none" viewBox="0 0 800 560" preserveAspectRatio="xMidYMid slice">
      <defs>
        <pattern id="gridLg" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="#A67520" stroke-width=".6"/></pattern>
        <pattern id="gridSm" width="8" height="8" patternUnits="userSpaceOnUse"><path d="M 8 0 L 0 0 0 8" fill="none" stroke="#A67520" stroke-width=".2"/></pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#gridSm)"/>
      <rect width="100%" height="100%" fill="url(#gridLg)"/>
      <circle cx="400" cy="280" r="220" fill="none" stroke="#A67520" stroke-width="1"/>
      <circle cx="400" cy="280" r="150" fill="none" stroke="#A67520" stroke-width=".6"/>
      <circle cx="400" cy="280" r="80" fill="none" stroke="#A67520" stroke-width=".4"/>
      <line x1="400" y1="60" x2="400" y2="500" stroke="#A67520" stroke-width=".7"/>
      <line x1="180" y1="280" x2="620" y2="280" stroke="#A67520" stroke-width=".7"/>
      <line x1="244" y1="124" x2="556" y2="436" stroke="#A67520" stroke-width=".3"/>
      <line x1="556" y1="124" x2="244" y2="436" stroke="#A67520" stroke-width=".3"/>
      <polygon points="400,62 404,78 400,74 396,78" fill="#A67520"/>
      <text x="400" y="56" text-anchor="middle" font-family="serif" font-size="10" fill="#A67520">N</text>
      <text x="400" y="516" text-anchor="middle" font-family="serif" font-size="10" fill="#A67520" opacity=".5">S</text>
      <text x="630" y="284" font-family="serif" font-size="10" fill="#A67520" opacity=".5">E</text>
      <text x="170" y="284" text-anchor="end" font-family="serif" font-size="10" fill="#A67520" opacity=".5">O</text>
    </svg>
    <div style="position:relative;z-index:2;padding:5.5rem 2rem 5rem;text-align:center;max-width:680px;margin:0 auto">
      <div class="lp-badge lp-anim-badge" style="display:inline-flex;align-items:center;gap:8px;font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.18em;color:var(--color-primary,#A67520);text-transform:uppercase;font-weight:400;margin-bottom:2.5rem;padding:5px 14px 5px 10px;border:1px solid rgba(166,117,32,0.3);border-radius:4px;background:rgba(166,117,32,0.05)">
        <div style="width:6px;height:6px;background:var(--color-primary,#A67520);transform:rotate(45deg);flex-shrink:0"></div>
        Voyage interieur &middot; Augmente par IA
      </div>
      <h1 class="lp-anim-h1a" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:clamp(38px,6vw,62px);font-weight:700;color:var(--text-primary,#2A1E08);line-height:1.08;letter-spacing:-0.03em;margin-bottom:.3rem">La plus grande aventure</h1>
      <h1 class="lp-h1-gradient lp-anim-h1b" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:clamp(38px,6vw,62px);font-weight:400;line-height:1.08;letter-spacing:-0.03em;margin-bottom:2rem">commence en toi.</h1>
      <p class="lp-anim-sub" style="font-family:var(--font-body,'Inter',sans-serif);font-size:16px;color:var(--text-secondary,#6B5A3E);line-height:1.8;max-width:420px;margin:0 auto 2.8rem;font-weight:400">Une expedition au coeur de toi-meme. L'IA cartographie ton monde interieur et revele les horizons qui t'attendent.</p>
      <div class="lp-anim-ctas" style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:2rem;flex-wrap:wrap">
        <Link href="/register" class="lp-btn-primary" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;letter-spacing:-0.01em;font-weight:600;color:var(--bg-base,#F0E8D4);background:var(--color-accent,#1C1408);border:none;border-radius:8px;padding:14px 32px;cursor:pointer;text-decoration:none;display:inline-block;box-shadow:0 4px 16px rgba(42,30,8,0.25)">Commencer l'expedition</Link>
        <a href="#methode" class="lp-btn-ghost" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;letter-spacing:-0.01em;font-weight:500;color:var(--text-secondary,#6B5A3E);background:transparent;border:1px solid rgba(166,117,32,0.3);border-radius:8px;padding:14px 24px;cursor:pointer;text-decoration:none;display:inline-block">Decouvrir la methode</a>
      </div>
      <div class="lp-anim-trust" style="display:flex;align-items:center;justify-content:center;gap:10px;font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.12em;color:var(--text-muted,#8C7A5E);text-transform:uppercase">
        <div style="width:28px;height:1px;background:rgba(166,117,32,0.25)"></div>
        Gratuit &middot; Sans engagement &middot; RGPD
        <div style="width:28px;height:1px;background:rgba(166,117,32,0.25)"></div>
      </div>
    </div>
  </section>

  <!-- STATS BAND -->
  <div class="lp-stats" style="background:var(--bg-elevated,#D8CEB5);padding:1.4rem 3rem;display:flex;align-items:center;justify-content:space-around;border-top:1px solid rgba(166,117,32,0.2);border-bottom:1px solid rgba(166,117,32,0.2);flex-wrap:wrap;gap:1rem">
    <div style="display:flex;align-items:center;gap:12px">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><polygon points="12,2 14,9 12,7.5 10,9" fill="none" stroke="#A67520" stroke-width="1.2" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" stroke="#A67520" stroke-width=".8" opacity=".4"/></svg>
      <div>
        <div id="lp-stat-epreuves" class="lp-stat-num" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:22px;font-weight:700;color:var(--color-primary,#A67520);letter-spacing:-0.02em">15</div>
        <div style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;color:var(--text-secondary,#6B5A3E);letter-spacing:.06em;text-transform:uppercase">Epreuves fondees</div>
      </div>
    </div>
    <div class="lp-stats-divider" style="width:1px;height:28px;background:rgba(166,117,32,0.2)"></div>
    <div style="display:flex;align-items:center;gap:12px">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><polygon points="12,2 22,7 22,17 12,22 2,17 2,7" stroke="#A67520" stroke-width=".8" opacity=".4"/><text x="12" y="16" text-anchor="middle" font-family="monospace" font-size="7" fill="#A67520" font-weight="600">IA</text></svg>
      <div>
        <div id="lp-stat-pistes" class="lp-stat-num" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:22px;font-weight:700;color:var(--color-primary,#A67520);letter-spacing:-0.02em">50</div>
        <div style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;color:var(--text-secondary,#6B5A3E);letter-spacing:.06em;text-transform:uppercase">Pistes metiers max</div>
      </div>
    </div>
    <div class="lp-stats-divider" style="width:1px;height:28px;background:rgba(166,117,32,0.2)"></div>
    <div style="display:flex;align-items:center;gap:12px">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 9 L12 4 L20 9 L20 19 L4 19 Z" stroke="#A67520" stroke-width=".9" opacity=".5"/><path d="M9 19 L9 13 L15 13 L15 19" stroke="#A67520" stroke-width="1"/><circle cx="12" cy="9" r="1.4" fill="#A67520" opacity=".7"/></svg>
      <div>
        <div id="lp-stat-modules" class="lp-stat-num" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:22px;font-weight:700;color:var(--color-primary,#A67520);letter-spacing:-0.02em">10</div>
        <div style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;color:var(--text-secondary,#6B5A3E);letter-spacing:.06em;text-transform:uppercase">Modules offerts</div>
      </div>
    </div>
    <div class="lp-stats-divider" style="width:1px;height:28px;background:rgba(166,117,32,0.2)"></div>
    <div style="display:flex;align-items:center;gap:12px">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#A67520" stroke-width=".8" opacity=".4"/><circle cx="12" cy="12" r="4" stroke="#A67520" stroke-width=".8" opacity=".5"/><line x1="12" y1="2" x2="12" y2="7" stroke="#A67520" stroke-width="1.2"/></svg>
      <div>
        <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:22px;font-weight:700;color:var(--color-primary,#A67520);letter-spacing:-0.02em">RGPD</div>
        <div style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;color:var(--text-secondary,#6B5A3E);letter-spacing:.06em;text-transform:uppercase">Donnees chiffrees</div>
      </div>
    </div>
  </div>

  <!-- INTRO SECTION -->
  <section class="lp-section" style="background:var(--bg-base,#F0E8D4);padding:5rem 3rem">
    <div class="lp-intro-grid grid grid-cols-1 md:grid-cols-2" style="max-width:960px;margin:0 auto">
      <div>
        <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--color-primary,#A67520);margin-bottom:1rem">Connaissance de soi</p>
        <h2 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:32px;font-weight:600;color:var(--text-primary,#2A1E08);line-height:1.2;letter-spacing:-0.02em;margin-bottom:1.2rem">Tu as explore le monde.<br>Et si tu explorais <span style="color:var(--color-primary,#A67520);font-weight:400;font-style:italic">qui tu es</span> ?</h2>
        <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:14px;color:var(--text-secondary,#6B5A3E);line-height:1.85;margin-bottom:1.2rem">Certaines questions meritent plus qu'une reponse rapide. PraxiQuest te guide dans une introspection profonde.</p>
        <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:14px;color:var(--text-secondary,#6B5A3E);line-height:1.85;margin-bottom:1.8rem">L'IA synthetise ton voyage et ouvre devant toi des chemins que tu n'aurais peut-etre jamais imagines.</p>
        <a href="#methode" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:12px;letter-spacing:.04em;font-weight:500;text-transform:uppercase;color:var(--color-primary,#A67520);display:inline-flex;align-items:center;gap:8px;text-decoration:none">Voir comment ca marche</a>
      </div>
      <div class="lp-intro-compass" style="display:flex;align-items:center;justify-content:center">
        <svg class="lp-compass-svg" width="220" height="220" viewBox="0 0 220 220">
          <circle cx="110" cy="110" r="100" fill="none" stroke="rgba(166,117,32,0.15)" stroke-width="1"/>
          <circle cx="110" cy="110" r="70" fill="none" stroke="rgba(166,117,32,0.22)" stroke-width="1"/>
          <circle cx="110" cy="110" r="40" fill="none" stroke="rgba(166,117,32,0.4)" stroke-width="1"/>
          <line x1="110" y1="10" x2="110" y2="210" stroke="rgba(166,117,32,0.12)" stroke-width=".8"/>
          <line x1="10" y1="110" x2="210" y2="110" stroke="rgba(166,117,32,0.12)" stroke-width=".8"/>
          <line x1="39.6" y1="39.6" x2="180.4" y2="180.4" stroke="rgba(166,117,32,0.08)" stroke-width=".8"/>
          <line x1="180.4" y1="39.6" x2="39.6" y2="180.4" stroke="rgba(166,117,32,0.08)" stroke-width=".8"/>
          <g class="lp-compass-needle">
            <polygon points="110,12 113,26 110,22 107,26" fill="#A67520"/>
            <polygon points="110,208 113,194 110,197 107,194" fill="#A67520" opacity=".3"/>
          </g>
          <circle cx="110" cy="110" r="6" fill="#A67520" opacity=".8"/>
          <circle cx="110" cy="110" r="3" fill="#F0E5CC"/>
          <circle cx="110" cy="40" r="3" fill="none" stroke="#A67520" stroke-width="1"/>
          <circle cx="180" cy="110" r="3" fill="none" stroke="#A67520" stroke-width="1"/>
          <circle cx="110" cy="180" r="3" fill="none" stroke="#A67520" stroke-width="1"/>
          <circle cx="40" cy="110" r="3" fill="none" stroke="#A67520" stroke-width="1"/>
          <text x="110" y="36" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="8" fill="#A67520" opacity=".9" font-weight="600">Qui suis-je ?</text>
          <text x="188" y="114" font-family="Space Grotesk,sans-serif" font-size="7" fill="#8B7355" opacity=".7">Horizons</text>
          <text x="110" y="196" text-anchor="middle" font-family="Space Grotesk,sans-serif" font-size="7" fill="#8B7355" opacity=".7">Possibles</text>
          <text x="10" y="114" font-family="Space Grotesk,sans-serif" font-size="7" fill="#8B7355" opacity=".7">Forces</text>
        </svg>
      </div>
    </div>
  </section>

  <!-- JOURNEY / 4 ACTES -->
  <section id="methode" class="lp-section" style="background:var(--bg-surface,#E5DAC2);padding:5rem 3rem">
    <div style="max-width:960px;margin:0 auto">
      <div style="text-align:center;margin-bottom:3.5rem">
        <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--color-primary,#A67520);margin-bottom:.8rem">Les etapes de la quete</p>
        <h2 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:30px;font-weight:600;color:var(--text-primary,#2A1E08);line-height:1.2;letter-spacing:-0.02em">Un voyage en quatre <span style="font-weight:400;color:var(--color-primary,#A67520)">actes</span></h2>
      </div>
      <div class="lp-journey-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4" style="position:relative">
        <div class="lp-journey-connector" style="position:absolute;top:28px;left:12.5%;right:12.5%;height:1px;background:linear-gradient(90deg,rgba(166,117,32,0.1),rgba(166,117,32,0.4),rgba(166,117,32,0.1))"></div>
        <div v-for="(step, i) in [
          {roman:'I', title:'L\'ancrage', text:'Tu poses le contexte. La ou tu en es. D\'ou tu viens. L\'IA t\'ecoute avant de te parler.'},
          {roman:'II', title:'L\'exploration', text:'15 epreuves fondees sur des modeles reconnus : RIASEC, Big Five, valeurs, stress, soft skills 360...'},
          {roman:'III', title:'La revelation', text:'Le Grimoire croise toutes tes epreuves : synthese profonde, et l\'impact de l\'IA sur ton metier.'},
          {roman:'IV', title:'L\'horizon', text:'Jusqu\'a 50 pistes metiers, a reordonner selon tes priorites. Pas des verdicts. Des chemins.'},
        ]" :key="i" class="lp-journey-item" style="padding:0 1.2rem;text-align:center;position:relative">
          <div class="lp-journey-circle" style="width:56px;height:56px;margin:0 auto 1.2rem;background:var(--bg-surface,#E5DAC2);border:1px solid rgba(166,117,32,0.35);border-radius:50%;display:flex;align-items:center;justify-content:center;position:relative;z-index:1">
            <span class="lp-journey-num" style="font-family:var(--font-data,'Space Mono',monospace);font-size:14px;font-weight:400;color:var(--color-primary,#A67520)">{{ step.roman }}</span>
          </div>
          <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:14px;font-weight:600;color:var(--text-primary,#2A1E08);margin-bottom:.5rem;letter-spacing:-0.01em">{{ step.title }}</div>
          <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:12px;color:var(--text-secondary,#6B5A3E);line-height:1.7">{{ step.text }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- PILLARS -->
  <section class="lp-section" style="background:var(--bg-base,#F0E8D4);padding:5rem 3rem">
    <div style="max-width:960px;margin:0 auto">
      <div style="text-align:center;margin-bottom:3rem">
        <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--color-primary,#A67520);margin-bottom:.8rem">La methode</p>
        <h2 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:30px;font-weight:600;color:var(--text-primary,#2A1E08);line-height:1.2;letter-spacing:-0.02em">Trois forces pour <span style="font-weight:400;color:var(--color-primary,#A67520)">aller en profondeur</span></h2>
      </div>
      <div class="lp-pillars-grid grid grid-cols-1 md:grid-cols-3" style="gap:1px;background:rgba(166,117,32,0.15);border:1px solid rgba(166,117,32,0.2);border-radius:12px;overflow:hidden">
        <div class="lp-pillar-card" style="background:var(--bg-surface,#E5DAC2);padding:2.2rem 1.8rem">
          <div class="lp-pillar-icon" style="width:40px;height:40px;margin-bottom:1.2rem;display:flex;align-items:center;justify-content:center;border:1px solid rgba(166,117,32,0.3);border-radius:50%;background:rgba(166,117,32,0.06)">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><polygon points="10,1 19,6 19,14 10,19 1,14 1,6" stroke="#A67520" stroke-width="1"/><circle cx="10" cy="10" r="2.5" fill="#A67520" opacity=".7"/></svg>
          </div>
          <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:15px;font-weight:600;color:var(--text-primary,#2A1E08);margin-bottom:.6rem;letter-spacing:-0.01em">Intelligence augmentee</div>
          <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:13px;color:var(--text-secondary,#6B5A3E);line-height:1.7">L'IA lit entre les lignes. Elle croise tes reponses, ton histoire, ton contexte.</p>
        </div>
        <div class="lp-pillar-card" style="background:var(--bg-surface,#E5DAC2);padding:2.2rem 1.8rem">
          <div class="lp-pillar-icon" style="width:40px;height:40px;margin-bottom:1.2rem;display:flex;align-items:center;justify-content:center;border:1px solid rgba(166,117,32,0.3);border-radius:50%;background:rgba(166,117,32,0.06)">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="9" stroke="#A67520" stroke-width="1"/><line x1="10" y1="1" x2="10" y2="19" stroke="#A67520" stroke-width=".6" opacity=".4"/><line x1="1" y1="10" x2="19" y2="10" stroke="#A67520" stroke-width=".6" opacity=".4"/><polygon points="10,2 11,7 10,6 9,7" fill="#A67520"/></svg>
          </div>
          <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:15px;font-weight:600;color:var(--text-primary,#2A1E08);margin-bottom:.6rem;letter-spacing:-0.01em">Narration immersive</div>
          <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:13px;color:var(--text-secondary,#6B5A3E);line-height:1.7">PraxiQuest n'est pas un questionnaire. C'est un recit dont tu es le heros.</p>
        </div>
        <div class="lp-pillar-card" style="background:var(--bg-surface,#E5DAC2);padding:2.2rem 1.8rem">
          <div class="lp-pillar-icon" style="width:40px;height:40px;margin-bottom:1.2rem;display:flex;align-items:center;justify-content:center;border:1px solid rgba(166,117,32,0.3);border-radius:50%;background:rgba(166,117,32,0.06)">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2 L18 6 L18 14 L10 18 L2 14 L2 6 Z" stroke="#A67520" stroke-width="1"/><path d="M10 6 L14 8 L14 12 L10 14 L6 12 L6 8 Z" fill="#A67520" opacity=".2"/></svg>
          </div>
          <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:15px;font-weight:600;color:var(--text-primary,#2A1E08);margin-bottom:.6rem;letter-spacing:-0.01em">Psychologie de la decision</div>
          <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:13px;color:var(--text-secondary,#6B5A3E);line-height:1.7">Chaque question a ete construite pour activer une prise de conscience reelle.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- UNIVERS : les trois espaces du produit -->
  <section class="lp-section" style="background:var(--bg-surface,#E5DAC2);padding:5rem 3rem">
    <div style="max-width:960px;margin:0 auto">
      <div style="text-align:center;margin-bottom:3rem">
        <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--color-primary,#A67520);margin-bottom:.8rem">Ton univers de quete</p>
        <h2 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:30px;font-weight:600;color:var(--text-primary,#2A1E08);line-height:1.2;letter-spacing:-0.02em">Trois espaces, <span style="font-weight:400;color:var(--color-primary,#A67520)">un seul voyage</span></h2>
      </div>
      <div class="lp-univers-grid grid grid-cols-1 md:grid-cols-3" style="gap:1.1rem">
        <div class="lp-univers-card" style="background:var(--bg-base,#F0E8D4);border:1px solid rgba(166,117,32,0.25);border-top:2px solid var(--color-primary,#A67520);border-radius:12px;padding:2rem 1.7rem">
          <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--color-primary,#A67520);margin-bottom:.7rem">01 &middot; Les epreuves</p>
          <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:17px;font-weight:600;color:var(--text-primary,#2A1E08);margin-bottom:.7rem;letter-spacing:-0.01em">L'Armurerie</div>
          <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:13px;color:var(--text-secondary,#6B5A3E);line-height:1.75">15 epreuves fondees sur des modeles reconnus : RIASEC, Big Five, valeurs de Schwartz, stress au travail (Karasek + MBI), soft skills 360, competences entrepreneuriales... Chacune livre une analyse detaillee, telechargeable en PDF.</p>
        </div>
        <div class="lp-univers-card" style="background:var(--bg-base,#F0E8D4);border:1px solid rgba(166,117,32,0.25);border-top:2px solid var(--color-primary,#A67520);border-radius:12px;padding:2rem 1.7rem">
          <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--color-primary,#A67520);margin-bottom:.7rem">02 &middot; La relecture</p>
          <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:17px;font-weight:600;color:var(--text-primary,#2A1E08);margin-bottom:.7rem;letter-spacing:-0.01em">Le Grimoire</div>
          <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:13px;color:var(--text-secondary,#6B5A3E);line-height:1.75">L'oracle croise l'ensemble de tes epreuves : un fil conducteur, une analyse de l'impact de l'IA sur ton metier, et jusqu'a 50 pistes metiers que tu reordonnes en direct selon tes priorites — remuneration, sens, autonomie, stabilite.</p>
        </div>
        <div class="lp-univers-card" style="background:var(--bg-base,#F0E8D4);border:1px solid rgba(166,117,32,0.25);border-top:2px solid var(--color-primary,#A67520);border-radius:12px;padding:2rem 1.7rem">
          <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--color-primary,#A67520);margin-bottom:.7rem">03 &middot; L'entrainement</p>
          <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:17px;font-weight:600;color:var(--text-primary,#2A1E08);margin-bottom:.7rem;letter-spacing:-0.01em">La Salle du Tresor</div>
          <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:13px;color:var(--text-secondary,#6B5A3E);line-height:1.75">Tes Eclats, gagnes au fil des epreuves, revelent 10 mini-apps offertes : parcours guides de 30 a 60 jours — confiance, stress, prise de parole, leadership, gestion du temps... Recommandees selon ton profil, a toi pour toujours.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- QUOTE -->
  <div class="lp-quote" style="background:#1C1408;padding:4rem 3rem;text-align:center;border-top:1px solid rgba(166,117,32,0.15)">
    <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:64px;font-weight:700;color:var(--color-primary,#A67520);opacity:.25;line-height:.7;margin-bottom:.8rem">"</div>
    <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:19px;font-style:italic;font-weight:400;color:rgba(240,232,212,0.85);line-height:1.6;max-width:480px;margin:0 auto 1.2rem;letter-spacing:.01em">Je ne m'attendais pas a me retrouver autant dans ce parcours. Comme si quelqu'un avait enfin pose les bonnes questions.</p>
    <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.15em;color:rgba(166,117,32,0.6);text-transform:uppercase">Margaux, 34 ans &middot; Apres son voyage PraxiQuest</p>
  </div>

  <!-- FINAL CTA -->
  <section class="lp-section" style="background:var(--bg-surface,#E5DAC2);padding:5rem 3rem;text-align:center;position:relative;overflow:hidden">
    <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.06;pointer-events:none" viewBox="0 0 400 400" preserveAspectRatio="xMidYMid slice">
      <circle cx="200" cy="200" r="190" fill="none" stroke="#A67520" stroke-width="1"/>
      <circle cx="200" cy="200" r="130" fill="none" stroke="#A67520" stroke-width=".6"/>
      <circle cx="200" cy="200" r="70" fill="none" stroke="#A67520" stroke-width=".4"/>
      <line x1="200" y1="10" x2="200" y2="390" stroke="#A67520" stroke-width=".6"/>
      <line x1="10" y1="200" x2="390" y2="200" stroke="#A67520" stroke-width=".6"/>
    </svg>
    <div style="position:relative;z-index:1">
      <div style="display:flex;align-items:center;justify-content:center;gap:14px;margin-bottom:1.8rem">
        <div style="width:50px;height:1px;background:rgba(166,117,32,0.3)"></div>
        <svg width="12" height="12" viewBox="0 0 12 12"><polygon points="6,0 12,6 6,12 0,6" fill="#A67520" opacity=".8"/></svg>
        <div style="width:50px;height:1px;background:rgba(166,117,32,0.3)"></div>
      </div>
      <h2 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:clamp(28px,4vw,44px);font-weight:700;color:var(--text-primary,#2A1E08);line-height:1.15;letter-spacing:-0.03em;margin-bottom:.4rem">Ta terra incognita</h2>
      <h2 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:clamp(28px,4vw,44px);font-weight:400;color:var(--color-primary,#A67520);line-height:1.15;letter-spacing:-0.03em;margin-bottom:1.2rem">t'attend.</h2>
      <p style="font-family:var(--font-body,'Inter',sans-serif);font-size:14px;color:var(--text-secondary,#6B5A3E);margin:0 auto 2.4rem;max-width:340px;line-height:1.75">Une expedition interieure. Jusqu'a 50 pistes metiers, et des modules d'entrainement pour avancer vraiment.</p>
      <Link href="/register" class="lp-btn-primary" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:600;letter-spacing:-0.01em;color:var(--bg-base,#F0E8D4);background:var(--color-accent,#1C1408);border:none;padding:16px 44px;cursor:pointer;border-radius:8px;text-decoration:none;display:inline-block;margin-bottom:1.4rem;box-shadow:0 4px 16px rgba(42,30,8,0.2)">Commencer l'expedition</Link>
      <p style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;color:var(--text-muted,#8C7A5E);letter-spacing:.1em;text-transform:uppercase">Gratuit &middot; Sans engagement &middot; Donnees chiffrees</p>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="lp-footer" style="background:var(--bg-elevated,#D8CEB5);padding:1.25rem 2.5rem;display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(166,117,32,0.2);flex-wrap:wrap;gap:.5rem">
    <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:14px;font-weight:600;color:var(--text-secondary,#6B5A3E);letter-spacing:-0.01em">{{ branding.name }}</div>
    <div style="display:flex;gap:20px">
      <Link href="/cgu" style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted,#8C7A5E);text-decoration:none">CGU</Link>
      <Link href="/confidentialite" style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted,#8C7A5E);text-decoration:none">Confidentialite</Link>
      <Link href="/mentions-legales" style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted,#8C7A5E);text-decoration:none">Mentions legales</Link>
      <Link href="/contact" style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted,#8C7A5E);text-decoration:none">Contact</Link>
    </div>
    <div style="font-family:var(--font-data,'Space Mono',monospace);font-size:10px;color:var(--text-ghost,#B0A08A);letter-spacing:.04em">{{ new Date().getFullYear() }} Praxis Accompagnement</div>
  </footer>

</div>
</template>

<style>
/*
 * RESPONSIVE — Landing.vue
 * Tailwind gère grid-template-columns via les classes HTML (grid-cols-*).
 * Ce bloc CSS gère uniquement : gaps, spacings et visibilités
 * que Tailwind ne peut pas surcharger directement (inline styles).
 */

/* ── GAP & ALIGN pour les grilles (colonnes gérées par Tailwind) ── */
.lp-intro-grid {
  gap: 4rem;
  align-items: center;
}

.lp-journey-grid {
  gap: .5rem;
}

/*
 * Fix standard CSS Grid : min-width:0 empêche les items de déborder
 * de leur colonne (par défaut min-width:auto).
 */
.lp-journey-item {
  min-width: 0;
  box-sizing: border-box;
}

/* ── TABLETTE (< 768px) ── */
@media (max-width: 768px) {
  .lp-nav              { padding: 0 1.2rem !important; height: 56px !important; }
  .lp-nav-actions      { gap: 4px !important; }
  .lp-stats            { padding: 1rem 1.5rem !important; }
  .lp-stats-divider    { display: none !important; }
  .lp-section          { padding: 3.5rem 1.5rem !important; }
  .lp-intro-grid       { gap: 2rem !important; }
  .lp-compass-svg      { width: 160px !important; height: 160px !important; }
  .lp-journey-grid     { gap: 1.2rem !important; }
  .lp-journey-item     { padding: 0 0.6rem !important; }
  .lp-journey-connector{ display: none !important; }
  .lp-quote            { padding: 3rem 1.5rem !important; }
  .lp-quote > p:first-of-type { font-size: 16px !important; }
  .lp-footer           { padding: 1rem 1.5rem !important; }
}

/* ── MOBILE (< 480px) ── */
@media (max-width: 480px) {
  .lp-nav              { padding: 0 1rem !important; }
  /* "Connexion" masqué : logo + 2 boutons > 375px de largeur. */
  .lp-nav-login        { display: none !important; }
  /* CTA réduit pour tenir sur une ligne (white-space:nowrap déjà présent). */
  .lp-nav-cta          { padding: 6px 12px !important; font-size: 12px !important; }
  .lp-section          { padding: 2.5rem 1rem !important; }
  .lp-journey-grid     { gap: 1.5rem !important; }
  .lp-journey-item     { padding: 0 0.25rem !important; }
  .lp-intro-compass    { display: none !important; }
  .lp-quote            { padding: 2.5rem 1rem !important; }
  .lp-footer           { padding: 0.75rem 1rem !important; }
}

/* ══════════════════════════════════════════════════
   AMÉLIORATIONS GRAPHIQUES — Phase Polish
   ══════════════════════════════════════════════════ */

/* ── 1. GRADIENT TEXT — titre h1 doré ── */
.lp-h1-gradient {
  background: linear-gradient(135deg, #7D5510 0%, #C99030 45%, #A67520 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* ── 2. BADGE SHIMMER ── */
.lp-badge {
  position: relative;
  overflow: hidden;
}
.lp-badge::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent 0%, rgba(166,117,32,0.22) 50%, transparent 100%);
  background-size: 200% 100%;
  animation: lp-shimmer 3.2s ease infinite 1.8s;
}
@keyframes lp-shimmer {
  0%   { background-position: -200% 0; }
  100% { background-position:  200% 0; }
}

/* ── 3. HERO CASCADE ANIMATION ── */
@keyframes lp-fade-up {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes lp-fade-down {
  from { opacity: 0; transform: translateY(-10px); }
  to   { opacity: 1; transform: translateY(0); }
}
.lp-anim-badge { animation: lp-fade-down 0.55s ease-out both; }
.lp-anim-h1a   { animation: lp-fade-up  0.5s ease-out 0.18s both; }
.lp-anim-h1b   { animation: lp-fade-up  0.5s ease-out 0.32s both; }
.lp-anim-sub   { animation: lp-fade-up  0.5s ease-out 0.46s both; }
.lp-anim-ctas  { animation: lp-fade-up  0.5s ease-out 0.6s  both; }
.lp-anim-trust { animation: lp-fade-up  0.5s ease-out 0.74s both; }

/* ── 4. CTA BOUTONS — hover avec lift ── */
.lp-btn-primary {
  transition: transform 0.18s ease, box-shadow 0.18s ease !important;
}
.lp-btn-primary:hover {
  transform: translateY(-3px) !important;
  box-shadow: 0 10px 28px rgba(42,30,8,0.42) !important;
}
.lp-btn-primary:active {
  transform: translateY(-1px) !important;
}
.lp-btn-ghost {
  transition: background 0.15s ease, border-color 0.15s ease !important;
}
.lp-btn-ghost:hover {
  background: rgba(166,117,32,0.10) !important;
  border-color: rgba(166,117,32,0.55) !important;
}

/* NAV CTA — même lift */
.lp-nav-cta {
  transition: transform 0.18s ease, box-shadow 0.18s ease !important;
}
.lp-nav-cta:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 18px rgba(42,30,8,0.38) !important;
}

/* ── UNIVERS CARDS — hover elevation (mêmes sensations que les piliers) ── */
.lp-univers-card {
  transition: transform 0.22s cubic-bezier(0.34, 1.2, 0.64, 1),
              box-shadow 0.22s ease,
              border-color 0.22s ease;
}
.lp-univers-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 32px rgba(42,30,8,0.14);
  border-color: var(--color-primary, #A67520) !important;
}

/* ── 5. PILLAR CARDS — hover elevation ── */
.lp-pillar-card {
  transition: background 0.22s ease,
              transform 0.22s cubic-bezier(0.34, 1.2, 0.64, 1),
              box-shadow 0.22s ease;
}
.lp-pillar-card:hover {
  background: var(--bg-elevated, #D8CEB5) !important;
  transform: translateY(-5px) scale(1.01);
  box-shadow: 0 12px 32px rgba(42,30,8,0.14);
  position: relative;
  z-index: 2;
  border-radius: 4px;
}
.lp-pillar-icon {
  transition: border-color 0.2s ease, background 0.2s ease;
}
.lp-pillar-card:hover .lp-pillar-icon {
  border-color: rgba(166,117,32,0.6) !important;
  background: rgba(166,117,32,0.12) !important;
}

/* ── 6. JOURNEY CIRCLES — hover doré ── */
.lp-journey-circle {
  transition: background 0.2s ease,
              border-color 0.2s ease,
              transform 0.2s cubic-bezier(0.34, 1.2, 0.64, 1),
              box-shadow 0.2s ease;
}
.lp-journey-item:hover .lp-journey-circle {
  background: var(--color-primary, #A67520) !important;
  border-color: var(--color-primary-dark, #7D5510) !important;
  transform: scale(1.14);
  box-shadow: 0 6px 18px rgba(166,117,32,0.38);
}
.lp-journey-num {
  transition: color 0.18s ease;
}
.lp-journey-item:hover .lp-journey-num {
  color: var(--bg-base, #F0E8D4) !important;
}

/* ── 7. BOUSSOLE — aiguille oscillante ── */
@keyframes lp-needle-sway {
  0%, 100% { transform: rotate(-9deg); }
  50%       { transform: rotate(9deg); }
}
.lp-compass-needle {
  transform-origin: 110px 110px;
  animation: lp-needle-sway 3.8s ease-in-out infinite;
}

/* ── 8. STATS COUNT-UP — tabular nums ── */
.lp-stat-num {
  font-variant-numeric: tabular-nums;
}

/* ── Respect prefers-reduced-motion pour les nouvelles animations ── */
@media (prefers-reduced-motion: reduce) {
  .lp-badge::after,
  .lp-compass-needle,
  .lp-anim-badge,
  .lp-anim-h1a, .lp-anim-h1b,
  .lp-anim-sub, .lp-anim-ctas, .lp-anim-trust {
    animation: none !important;
  }
  .lp-h1-gradient {
    background: none;
    -webkit-text-fill-color: var(--color-primary, #A67520);
    color: var(--color-primary, #A67520);
  }
}
</style>
