<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  plans:     { type: Object, required: true },
  trialDays: { type: Number, default: 14 },
  contact:   { type: String, default: 'contact@praxiquest.fr' },
})

const flash = computed(() => usePage().props.flash ?? {})

// Période sélectionnée pour l'offre Indépendant (mensuel / annuel)
const period = ref('monthly')

const euros = (cents) => Math.round(cents / 100)

const chiffres = [
  { valeur: '90–260 €', label: 'une batterie complète par candidat chez les éditeurs à la passation*' },
  { valeur: '1–2 h',    label: 'de synthèse par bilan, avant PraxiQuest' },
  { valeur: '8–20 €',   label: 'le coût effectif d\'un dossier PraxiQuest, tests et synthèse IA compris' },
]

const tests = [
  'RIASEC (intérêts professionnels)', 'Big Five (30 facettes)', 'Valeurs professionnelles (Schwartz)',
  'Intelligence émotionnelle (EQ-i)', 'Stress & épuisement (Karasek + MBI)', 'Biais cognitifs',
  'Gestion du temps', 'Dépistage TDAH (ASRS-v1.1, OMS)', 'Hypersensibilité (SPS)',
  'Feedback 360° (évaluateurs invités)', 'Compétences entrepreneuriales', 'Orientation express',
]

const restitution = [
  'Synthèse rédigée + 15-30 pistes métiers',
  'Relecture transversale de tous les tests',
  'Plans d\'action par piste (10 étapes)',
  'Rapport PDF aux couleurs du cabinet',
]

const interSeance = [
  'Parcours guidés 30-60 jours (confiance, stress, prise de parole, temps…)',
  'Le bénéficiaire reste actif : le bilan ne s\'enlise plus entre deux rendez-vous',
]

const parcours = [
  { num: '01', titre: 'Invitation',   texte: 'Le consultant envoie un lien unique. Le bénéficiaire s\'inscrit, dépose son CV, l\'IA en extrait le parcours.' },
  { num: '02', titre: 'Passation',    texte: 'Tests en ligne, à son rythme, dans un univers gamifié qui tient la motivation jusqu\'au bout.' },
  { num: '03', titre: 'Restitution',  texte: 'Synthèse IA, pistes métiers et plans d\'action prêts pour la séance, exportés en PDF à votre marque.' },
  { num: '04', titre: 'Inter-séance', texte: 'Parcours guidés 30-60 jours : le bénéficiaire arrive à la séance suivante en ayant avancé.' },
]

const pilote = [
  'Comptes d\'équipe : plusieurs consultants sous une même structure',
  'Vue consolidée : activité, taux de complétion et dossiers de toute l\'équipe',
  'Personnalisation de marque renforcée pour les réseaux',
  'Tarif gelé sur la grille actuelle pour toute la durée de votre abonnement',
  'Ligne directe avec le fondateur : onboarding accompagné, réponses en direct',
  'Votre voix dans la roadmap : les fonctions équipe se conçoivent sur vos usages réels',
]
</script>

<template>
<Head title="PraxiQuest pour les structures — Bilans de compétences augmentés par IA">
  <meta name="description" content="PraxiQuest réunit les 12 tests psychométriques, la restitution IA et l'inter-séance dans un seul outil au prix fixe. Essai gratuit 14 jours pour chaque consultant, programme pilote pour les structures." />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="PraxiQuest pour les structures — La plateforme qui travaille aussi entre les séances" />
  <meta property="og:description" content="12 tests, synthèse IA, rapports à votre marque et parcours inter-séances. 39 €/mois par consultant, essai gratuit 14 jours." />
</Head>
<div style="font-family:var(--font-body,Inter,sans-serif);background:var(--bg-base,#F0E8D4);min-height:100vh;color:var(--text-primary,#2A1E08);overflow-x:hidden">

  <!-- NAV -->
  <nav style="background:rgba(240,232,212,0.88);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);padding:0 2.5rem;display:flex;align-items:center;justify-content:space-between;height:64px;border-bottom:1px solid rgba(166,117,32,0.2);position:sticky;top:0;z-index:100">
    <Link href="/" style="display:flex;align-items:center;gap:11px;text-decoration:none">
      <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0">
        <circle cx="19" cy="19" r="17.5" stroke="#A67520" stroke-width="1"/>
        <circle cx="19" cy="19" r="13" stroke="#A67520" stroke-width="0.5" opacity="0.5"/>
        <polygon points="19,6 20.4,18 19,21 17.6,18" fill="#A67520"/>
        <polygon points="19,32 20.4,20 19,17 17.6,20" fill="#A67520" opacity="0.35"/>
        <circle cx="19" cy="19" r="2" fill="#A67520"/>
        <circle cx="19" cy="19" r="1" fill="#F0E8D4"/>
      </svg>
      <div style="display:flex;flex-direction:column;gap:1px">
        <span style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:16px;font-weight:600;color:var(--text-primary,#2A1E08);letter-spacing:-0.01em;line-height:1">PraxiQuest</span>
        <span style="font-family:var(--font-data,'Space Mono',monospace);font-size:9px;font-weight:400;color:var(--color-primary,#A67520);letter-spacing:0.14em;text-transform:uppercase;line-height:1">Structures</span>
      </div>
    </Link>
    <div style="display:flex;align-items:center;gap:8px">
      <Link href="/login" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:500;color:var(--text-secondary,#6B5A3E);text-decoration:none;padding:6px 14px;border-radius:6px">Connexion</Link>
      <a href="#tarifs" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:600;letter-spacing:-0.01em;color:var(--bg-base,#F0E8D4);background:var(--color-accent,#1C1408);border:none;padding:8px 20px;border-radius:6px;text-decoration:none;display:inline-block;box-shadow:0 2px 8px rgba(42,30,8,0.2);white-space:nowrap">Essai gratuit {{ trialDays }} jours</a>
    </div>
  </nav>

  <!-- Flash -->
  <div v-if="flash.error" style="max-width:900px;margin:1rem auto 0;padding:12px 18px;background:rgba(123,21,21,0.08);border:1px solid rgba(123,21,21,0.3);border-radius:8px;color:#7B1515;font-size:14px">{{ flash.error }}</div>

  <!-- HERO -->
  <section style="position:relative;padding:5rem 2rem 3.5rem;text-align:center;max-width:760px;margin:0 auto">
    <div style="display:inline-flex;align-items:center;gap:8px;font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.18em;color:var(--color-primary,#A67520);text-transform:uppercase;margin-bottom:2.2rem;padding:5px 14px 5px 10px;border:1px solid rgba(166,117,32,0.3);border-radius:4px;background:rgba(166,117,32,0.05)">
      <div style="width:6px;height:6px;background:var(--color-primary,#A67520);transform:rotate(45deg)"></div>
      Présentation structures
    </div>
    <h1 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:clamp(34px,5.4vw,56px);font-weight:700;line-height:1.1;letter-spacing:-0.03em;margin-bottom:1.6rem">La plateforme qui travaille aussi <span class="st-gradient">entre les séances.</span></h1>
    <p style="font-size:16px;color:var(--text-secondary,#6B5A3E);line-height:1.8;max-width:600px;margin:0 auto 2.6rem">Vos consultants passent un temps précieux sur les passations, la synthèse et la relance des bénéficiaires, avec des outils facturés à la passation et des pratiques hétérogènes d'un consultant à l'autre. PraxiQuest réunit les tests, la restitution et l'inter-séance dans un seul outil, au prix fixe.</p>
    <div style="display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap">
      <a :href="`/structures/essai?plan=independant&period=monthly`" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:600;color:var(--bg-base,#F0E8D4);background:var(--color-accent,#1C1408);border-radius:8px;padding:14px 32px;text-decoration:none;display:inline-block;box-shadow:0 4px 16px rgba(42,30,8,0.25)">Commencer l'essai gratuit</a>
      <a href="#tarifs" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:500;color:var(--text-secondary,#6B5A3E);border:1px solid rgba(166,117,32,0.3);border-radius:8px;padding:14px 24px;text-decoration:none;display:inline-block">Voir la grille tarifaire</a>
    </div>
    <div style="margin-top:1.6rem;font-family:var(--font-data,'Space Mono',monospace);font-size:10px;letter-spacing:.12em;color:var(--text-muted,#8C7A5E);text-transform:uppercase">{{ trialDays }} jours d'essai · Sans engagement · Données hébergées en France</div>
  </section>

  <!-- CHIFFRES -->
  <section style="max-width:1000px;margin:0 auto;padding:1rem 2rem 3.5rem">
    <div class="st-kicker">◆ Les chiffres</div>
    <div class="st-grid3">
      <div v-for="c in chiffres" :key="c.valeur" style="background:rgba(255,252,244,0.7);border:1px solid rgba(166,117,32,0.18);border-radius:12px;padding:1.6rem">
        <div style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:32px;font-weight:700;color:var(--color-primary,#A67520);letter-spacing:-0.02em;margin-bottom:.5rem">{{ c.valeur }}</div>
        <div style="font-size:13.5px;color:var(--text-secondary,#6B5A3E);line-height:1.6">{{ c.label }}</div>
      </div>
    </div>
    <div style="margin-top:.8rem;font-size:11.5px;color:var(--text-muted,#8C7A5E)">* Grilles publiques constatées (crédits par passation, batterie de 4-5 tests), juillet 2026.</div>
  </section>

  <!-- DISPONIBLE AUJOURD'HUI -->
  <section style="background:rgba(166,117,32,0.05);border-top:1px solid rgba(166,117,32,0.15);border-bottom:1px solid rgba(166,117,32,0.15);padding:3.5rem 2rem">
    <div style="max-width:1000px;margin:0 auto">
      <div class="st-kicker">◆ Disponible aujourd'hui</div>
      <div class="st-grid3">
        <div>
          <h3 class="st-h3">12 tests psychométriques</h3>
          <p style="font-size:13px;color:var(--text-muted,#8C7A5E);margin-bottom:1rem;line-height:1.6">Fondés sur les modèles de référence ; étalonnage recalculé automatiquement sur les passations réelles.</p>
          <ul class="st-list"><li v-for="t in tests" :key="t">{{ t }}</li></ul>
        </div>
        <div>
          <h3 class="st-h3">La restitution, préparée par l'IA</h3>
          <ul class="st-list"><li v-for="r in restitution" :key="r">{{ r }}</li></ul>
        </div>
        <div>
          <h3 class="st-h3">Et entre les séances</h3>
          <ul class="st-list"><li v-for="i in interSeance" :key="i">{{ i }}</li></ul>
        </div>
      </div>
    </div>
  </section>

  <!-- PARCOURS BÉNÉFICIAIRE -->
  <section style="max-width:1000px;margin:0 auto;padding:3.5rem 2rem">
    <div class="st-kicker">◆ Le parcours d'un bénéficiaire</div>
    <div class="st-grid4">
      <div v-for="p in parcours" :key="p.num" style="border-left:2px solid rgba(166,117,32,0.35);padding-left:1.1rem">
        <div style="font-family:var(--font-data,'Space Mono',monospace);font-size:22px;color:var(--color-primary,#A67520);margin-bottom:.4rem">{{ p.num }}</div>
        <h3 class="st-h3" style="margin-bottom:.4rem">{{ p.titre }}</h3>
        <p style="font-size:13.5px;color:var(--text-secondary,#6B5A3E);line-height:1.65;margin:0">{{ p.texte }}</p>
      </div>
    </div>
  </section>

  <!-- CONSULTANT / CONFORMITÉ -->
  <section style="max-width:1000px;margin:0 auto;padding:0 2rem 3.5rem">
    <div class="st-kicker">◆ Côté consultant · Côté conformité</div>
    <div class="st-grid2">
      <div style="background:rgba(255,252,244,0.7);border:1px solid rgba(166,117,32,0.18);border-radius:12px;padding:1.5rem">
        <h3 class="st-h3">Côté consultant</h3>
        <p style="font-size:13.5px;color:var(--text-secondary,#6B5A3E);line-height:1.7;margin:0">Invitation du bénéficiaire en un lien, suivi d'avancement en temps réel, relances automatiques, exports CSV pour vos dossiers Qualiopi.</p>
      </div>
      <div style="background:rgba(255,252,244,0.7);border:1px solid rgba(166,117,32,0.18);border-radius:12px;padding:1.5rem">
        <h3 class="st-h3">Côté conformité</h3>
        <p style="font-size:13.5px;color:var(--text-secondary,#6B5A3E);line-height:1.7;margin:0">Données hébergées en France (OVH), consentement explicite du bénéficiaire pour le partage de ses résultats, export et suppression des données à la demande (RGPD art. 15 &amp; 17).</p>
      </div>
    </div>
  </section>

  <!-- GRILLE TARIFAIRE -->
  <section id="tarifs" style="background:rgba(166,117,32,0.05);border-top:1px solid rgba(166,117,32,0.15);border-bottom:1px solid rgba(166,117,32,0.15);padding:3.5rem 2rem">
    <div style="max-width:1000px;margin:0 auto">
      <div class="st-kicker">◆ La grille : publique, sans surprise</div>

      <!-- Toggle période -->
      <div style="display:flex;justify-content:center;margin-bottom:2rem">
        <div style="display:inline-flex;background:rgba(255,252,244,0.8);border:1px solid rgba(166,117,32,0.25);border-radius:9px;padding:4px">
          <button @click="period = 'monthly'" :style="{fontFamily:'var(--font-display)',fontSize:'13px',fontWeight:600,padding:'8px 20px',borderRadius:'6px',border:'none',cursor:'pointer',background: period === 'monthly' ? 'var(--color-primary,#A67520)' : 'transparent',color: period === 'monthly' ? '#FFFCF4' : 'var(--text-secondary,#6B5A3E)'}">Mensuel</button>
          <button @click="period = 'yearly'"  :style="{fontFamily:'var(--font-display)',fontSize:'13px',fontWeight:600,padding:'8px 20px',borderRadius:'6px',border:'none',cursor:'pointer',background: period === 'yearly' ? 'var(--color-primary,#A67520)' : 'transparent',color: period === 'yearly' ? '#FFFCF4' : 'var(--text-secondary,#6B5A3E)'}">Annuel <span style="font-size:11px;opacity:.85">−17%</span></button>
        </div>
      </div>

      <div class="st-grid3">
        <div v-for="(plan, key) in plans" :key="key" :style="{background:'rgba(255,252,244,0.85)',border: plan.highlighted ? '2px solid var(--color-primary,#A67520)' : '1px solid rgba(166,117,32,0.2)',borderRadius:'14px',padding:'1.8rem',position:'relative',display:'flex',flexDirection:'column'}">
          <div v-if="plan.available" style="position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:var(--color-primary,#A67520);color:#FFFCF4;font-family:var(--font-data,'Space Mono',monospace);font-size:9.5px;letter-spacing:.12em;text-transform:uppercase;padding:4px 12px;border-radius:4px;white-space:nowrap">Disponible</div>
          <div v-else style="position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:var(--bg-base,#F0E8D4);border:1px solid rgba(166,117,32,0.35);color:var(--text-secondary,#6B5A3E);font-family:var(--font-data,'Space Mono',monospace);font-size:9.5px;letter-spacing:.12em;text-transform:uppercase;padding:4px 12px;border-radius:4px;white-space:nowrap">Ouverture prochaine</div>

          <h3 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:19px;font-weight:700;margin:.4rem 0 .3rem">{{ plan.name }}</h3>
          <p style="font-size:12.5px;color:var(--text-muted,#8C7A5E);line-height:1.55;margin-bottom:1.1rem;min-height:38px">{{ plan.description }}</p>
          <div style="margin-bottom:.3rem">
            <span style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:34px;font-weight:700;letter-spacing:-0.02em">{{ period === 'yearly' ? euros(plan.price_yearly) : euros(plan.price_monthly) }}&nbsp;€</span>
            <span style="font-size:13px;color:var(--text-muted,#8C7A5E)">/ {{ period === 'yearly' ? 'an' : 'mois' }}</span>
          </div>
          <div style="font-family:var(--font-data,'Space Mono',monospace);font-size:10.5px;color:var(--color-primary,#A67520);letter-spacing:.08em;text-transform:uppercase;margin-bottom:1.2rem">{{ plan.quota_dossiers }}{{ key === 'centre' ? '+' : '' }} dossiers / mois</div>
          <ul class="st-list" style="flex:1;margin-bottom:1.4rem"><li v-for="f in plan.features.slice(0, 4)" :key="f">{{ f }}</li></ul>

          <a v-if="plan.available" :href="`/structures/essai?plan=${key}&period=${period}`" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:600;text-align:center;color:var(--bg-base,#F0E8D4);background:var(--color-accent,#1C1408);border-radius:8px;padding:13px 20px;text-decoration:none;display:block;box-shadow:0 4px 14px rgba(42,30,8,0.22)">Commencer l'essai gratuit</a>
          <a v-else :href="`mailto:${contact}?subject=PraxiQuest — offre ${plan.name}`" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:600;text-align:center;color:var(--text-secondary,#6B5A3E);background:transparent;border:1px solid rgba(166,117,32,0.35);border-radius:8px;padding:13px 20px;text-decoration:none;display:block">Être prévenu·e</a>
        </div>
      </div>
      <div style="text-align:center;margin-top:1.6rem;font-size:12.5px;color:var(--text-muted,#8C7A5E)">Essai gratuit {{ trialDays }} jours · Sans engagement · Annulation en 1 clic · Paiement sécurisé Stripe</div>
    </div>
  </section>

  <!-- PROGRAMME PILOTE -->
  <section style="max-width:820px;margin:0 auto;padding:3.5rem 2rem;text-align:center">
    <div class="st-kicker" style="text-align:center">◆ Le programme pilote : 5 structures, pas plus</div>
    <h2 style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:clamp(26px,4vw,38px);font-weight:700;letter-spacing:-0.02em;margin-bottom:1rem">Entrez tôt, <span class="st-gradient">construisez avec nous.</span></h2>
    <p style="font-size:15px;color:var(--text-secondary,#6B5A3E);line-height:1.75;max-width:620px;margin:0 auto 2rem">Chaque consultant de votre équipe peut utiliser PraxiQuest dès aujourd'hui. Les fonctions dédiées aux structures sont en cours de développement, et ce sont les structures pilotes qui en définissent les priorités.</p>
    <ul class="st-list" style="text-align:left;max-width:560px;margin:0 auto 2.4rem"><li v-for="p in pilote" :key="p">{{ p }}</li></ul>
    <a :href="`mailto:${contact}?subject=Programme pilote PraxiQuest`" style="font-family:var(--font-display,'Space Grotesk',sans-serif);font-size:13px;font-weight:600;color:var(--bg-base,#F0E8D4);background:var(--color-accent,#1C1408);border-radius:8px;padding:14px 32px;text-decoration:none;display:inline-block;box-shadow:0 4px 16px rgba(42,30,8,0.25)">Échanger 30 minutes avec le fondateur</a>
    <div style="margin-top:1rem;font-size:12.5px;color:var(--text-muted,#8C7A5E)">{{ contact }} — Alexandre Fradin, fondateur</div>
  </section>

  <!-- FOOTER -->
  <footer style="border-top:1px solid rgba(166,117,32,0.2);padding:1.8rem 2rem;text-align:center;font-family:var(--font-data,'Space Mono',monospace);font-size:10.5px;letter-spacing:.1em;color:var(--text-muted,#8C7A5E)">
    <a href="/docs/presentation-structures.pdf" target="_blank" rel="noopener" style="color:var(--color-primary,#A67520);text-decoration:none">Télécharger la présentation PDF</a>
    <span style="margin:0 10px">·</span>
    <Link href="/confidentialite" style="color:inherit;text-decoration:none">Confidentialité</Link>
    <span style="margin:0 10px">·</span>
    <Link href="/cgu" style="color:inherit;text-decoration:none">CGU</Link>
    <div style="margin-top:.6rem">PraxiQuest · Praxis Accompagnement · Document d'information non contractuel</div>
  </footer>
</div>
</template>

<style scoped>
.st-gradient {
  background: linear-gradient(120deg, #A67520, #D4A24C);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}
.st-kicker {
  font-family: var(--font-data, 'Space Mono', monospace);
  font-size: 11px;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--color-primary, #A67520);
  margin-bottom: 1.4rem;
}
.st-h3 {
  font-family: var(--font-display, 'Space Grotesk', sans-serif);
  font-size: 16px;
  font-weight: 700;
  letter-spacing: -0.01em;
  margin: 0 0 .7rem;
}
.st-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.st-list li {
  position: relative;
  padding-left: 18px;
  font-size: 13.5px;
  color: var(--text-secondary, #6B5A3E);
  line-height: 1.6;
  margin-bottom: .45rem;
}
.st-list li::before {
  content: '◆';
  position: absolute;
  left: 0;
  top: 1px;
  font-size: 9px;
  color: var(--color-primary, #A67520);
}
.st-grid2, .st-grid3, .st-grid4 { display: grid; gap: 1.2rem; }
.st-grid2 { grid-template-columns: repeat(2, 1fr); }
.st-grid3 { grid-template-columns: repeat(3, 1fr); }
.st-grid4 { grid-template-columns: repeat(4, 1fr); }
@media (max-width: 900px) {
  .st-grid3, .st-grid4 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .st-grid2, .st-grid3, .st-grid4 { grid-template-columns: 1fr; }
}
</style>
