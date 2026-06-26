<template>
  <div style="min-height:100vh; background:var(--bg-base); font-family:var(--font-body); color:var(--text-primary)">
    <!-- Header -->
    <header
      style="border-bottom:1px solid var(--glass-border); background:var(--glass-bg); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); padding:1rem 1.5rem; display:flex; align-items:center; justify-content:space-between"
    >
      <div style="display:flex; align-items:center; gap:10px">
        <svg width="30" height="30" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0">
          <circle cx="19" cy="19" r="17.5" stroke="var(--color-primary)" stroke-width="1"/>
          <circle cx="19" cy="19" r="13" stroke="var(--color-primary)" stroke-width="0.5" opacity="0.5"/>
          <polygon points="19,6 20.4,18 19,21 17.6,18" fill="var(--color-primary)"/>
          <polygon points="19,32 20.4,20 19,17 17.6,20" fill="var(--color-primary)" opacity="0.35"/>
          <circle cx="19" cy="19" r="2" fill="var(--color-primary)"/>
        </svg>
        <span style="font-family:var(--font-display); font-size:17px; font-weight:600; color:var(--text-primary); letter-spacing:-0.01em">PraxiQuest</span>
      </div>
      <span style="font-family:var(--font-data); font-size:11px; color:var(--text-muted); letter-spacing:0.03em">Lien valable jusqu'au {{ props.expires_at }}</span>
    </header>

    <main class="mx-auto max-w-2xl px-4 py-10" style="display:flex; flex-direction:column; gap:2rem">
      <!-- Titre -->
      <div class="text-center">
        <p style="font-family:var(--font-data); font-size:11px; font-weight:600; color:var(--color-primary); text-transform:uppercase; letter-spacing:0.18em; margin-bottom:0.5rem">Profil partagé</p>
        <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:700; letter-spacing:-0.02em; color:var(--text-primary)">
          {{ profile.name }}
        </h1>
        <p v-if="profile.status" style="margin-top:0.5rem; font-size:13px; color:var(--text-secondary)">
          {{ statusLabel }} · {{ profile.is_grimoire ? 'relecture globale du' : 'bilan complété le' }} {{ profile.completed_at }}
        </p>

        <!-- Filet décoratif or -->
        <div class="flex items-center gap-3" style="margin-top:1.25rem">
          <div class="h-px flex-1" style="background:linear-gradient(to right, var(--color-primary), transparent)"></div>
          <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 0L9.6 6.4L16 8L9.6 9.6L8 16L6.4 9.6L0 8L6.4 6.4L8 0Z" fill="var(--color-primary)" opacity="0.5"/>
          </svg>
          <div class="h-px flex-1" style="background:linear-gradient(to left, var(--color-primary), transparent)"></div>
        </div>
      </div>

      <!-- Synthèse IA -->
      <section class="pt-card" style="padding:1.5rem">
        <h2 style="font-family:var(--font-display); font-size:1rem; font-weight:600; color:var(--text-primary); margin-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem">
          <span style="font-size:1.4rem">✨</span> {{ profile.is_grimoire ? 'Le fil conducteur' : 'Synthèse de personnalité' }}
        </h2>
        <MarkdownText :source="profile.synthesis" style="color:var(--text-secondary); line-height:1.7" />
      </section>

      <!-- Scores -->
      <section v-if="profile.scores && Object.keys(profile.scores).length" class="pt-card" style="padding:1.5rem">
        <h2 style="font-family:var(--font-display); font-size:1rem; font-weight:600; color:var(--text-primary); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem">
          <span style="font-size:1.4rem">📊</span> Résultats détaillés
        </h2>
        <ul style="display:flex; flex-direction:column; gap:0.85rem">
          <li v-for="(score, key) in profile.scores" :key="key">
            <div class="flex justify-between" style="font-size:13px; color:var(--text-secondary); margin-bottom:0.3rem">
              <span>{{ key }}</span>
              <span style="font-weight:700; font-family:var(--font-data); color:var(--text-primary)">{{ score }}%</span>
            </div>
            <div style="height:6px; border-radius:99px; background:var(--bg-elevated); overflow:hidden">
              <div
                style="height:6px; border-radius:99px; background:var(--color-primary); transition:width 0.7s ease"
                :style="{ width: score + '%' }"
              />
            </div>
          </li>
        </ul>
      </section>

      <!-- 15 métiers -->
      <section class="pt-card" style="padding:1.5rem">
        <h2 style="font-family:var(--font-display); font-size:1rem; font-weight:600; color:var(--text-primary); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem">
          <span style="font-size:1.4rem">🚀</span> {{ profile.is_grimoire ? 'Voies possibles' : 'Pistes de métiers suggérées' }}
        </h2>
        <ol style="display:flex; flex-direction:column; gap:0.6rem">
          <li
            v-for="(career, i) in profile.careers"
            :key="i"
            style="display:flex; align-items:flex-start; gap:0.75rem; font-size:13px"
          >
            <span style="margin-top:1px; display:flex; height:24px; width:24px; flex-shrink:0; align-items:center; justify-content:center; border-radius:50%; background:var(--pt-gold-pale); font-family:var(--font-data); font-size:11px; font-weight:700; color:var(--color-primary)">
              {{ i + 1 }}
            </span>
            <div>
              <p style="font-weight:600; color:var(--text-primary)">{{ career.titre ?? career.title ?? career }}</p>
              <p v-if="career.pourquoi ?? career.description" style="color:var(--text-muted); margin-top:0.15rem">{{ career.pourquoi ?? career.description }}</p>
            </div>
          </li>
        </ol>
      </section>

      <!-- Footer -->
      <p class="text-center" style="font-size:12px; color:var(--text-muted)">
        Ce profil a été partagé par {{ profile.first_name }} via PraxiQuest.
        <br>Vous souhaitez aussi découvrir vos pistes d'orientation ?
        <a href="/" class="ac-link-primary">Commencer gratuitement →</a>
      </p>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
  profile: {
    type: Object,
    required: true,
  },
  expires_at: String,
})

const statusLabels = {
  employee:     'Salarié(e)',
  entrepreneur: 'Entrepreneur(e)',
  jobseeker:    'Demandeur d\'emploi',
  student:      'Étudiant(e)',
  other:        'Autre',
}

const statusLabel = computed(() => statusLabels[props.profile.status] ?? props.profile.status ?? '')
</script>
