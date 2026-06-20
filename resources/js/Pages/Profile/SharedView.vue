<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-white">
    <!-- Header -->
    <header class="border-b border-indigo-100 bg-white/70 backdrop-blur-sm px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <!-- Logo / nom du SAAS -->
        <span class="text-lg font-bold text-indigo-700">PraxiTests</span>
      </div>
      <span class="text-xs text-gray-400">Lien valable jusqu'au {{ props.expires_at }}</span>
    </header>

    <main class="mx-auto max-w-2xl px-4 py-10 space-y-8">
      <!-- Titre -->
      <div class="text-center">
        <p class="text-sm font-medium text-indigo-500 uppercase tracking-widest mb-1">Profil partagé</p>
        <h1 class="text-3xl font-bold text-gray-900">
          {{ profile.name }}
        </h1>
        <p v-if="profile.status" class="mt-2 text-sm text-gray-500">
          {{ statusLabel }} · {{ profile.is_grimoire ? 'relecture globale du' : 'bilan complété le' }} {{ profile.completed_at }}
        </p>
      </div>

      <!-- Synthèse IA -->
      <section class="rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
          <span class="text-2xl">✨</span> {{ profile.is_grimoire ? 'Le fil conducteur' : 'Synthèse de personnalité' }}
        </h2>
        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ profile.synthesis }}</p>
      </section>

      <!-- Scores -->
      <section v-if="profile.scores && Object.keys(profile.scores).length" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
          <span class="text-2xl">📊</span> Résultats détaillés
        </h2>
        <ul class="space-y-3">
          <li v-for="(score, key) in profile.scores" :key="key">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
              <span>{{ key }}</span>
              <span class="font-semibold">{{ score }}%</span>
            </div>
            <div class="h-2 rounded-full bg-indigo-100 overflow-hidden">
              <div
                class="h-2 rounded-full bg-indigo-500 transition-all duration-700"
                :style="{ width: score + '%' }"
              />
            </div>
          </li>
        </ul>
      </section>

      <!-- 15 métiers -->
      <section class="rounded-2xl border border-indigo-100 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
          <span class="text-2xl">🚀</span> {{ profile.is_grimoire ? 'Voies possibles' : 'Pistes de métiers suggérées' }}
        </h2>
        <ol class="space-y-2">
          <li
            v-for="(career, i) in profile.careers"
            :key="i"
            class="flex items-start gap-3 text-sm"
          >
            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">
              {{ i + 1 }}
            </span>
            <div>
              <p class="font-semibold text-gray-800">{{ career.titre ?? career.title ?? career }}</p>
              <p v-if="career.pourquoi ?? career.description" class="text-gray-500 mt-0.5">{{ career.pourquoi ?? career.description }}</p>
            </div>
          </li>
        </ol>
      </section>

      <!-- Footer -->
      <p class="text-center text-xs text-gray-400">
        Ce profil a été partagé par {{ profile.first_name }} via PraxiTests.
        <br>Vous souhaitez aussi découvrir vos pistes d'orientation ?
        <a href="/" class="text-indigo-500 hover:underline">Commencer gratuitement →</a>
      </p>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'

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
