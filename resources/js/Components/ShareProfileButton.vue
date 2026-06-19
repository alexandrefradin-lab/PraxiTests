<template>
  <!-- Bouton déclencheur -->
  <button
    @click="openModal"
    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition"
  >
    <ShareIcon class="h-4 w-4" />
    Partager mon profil
  </button>

  <!-- Modal -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="close" />

        <!-- Panneau -->
        <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
          <!-- En-tête -->
          <div class="flex items-start justify-between mb-4">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">Partager mon profil</h2>
              <p class="mt-1 text-sm text-gray-500">
                Vos proches pourront voir votre synthèse et vos pistes de métiers.
              </p>
            </div>
            <button @click="close" class="text-gray-400 hover:text-gray-600 ml-4">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>

          <!-- Génération du lien -->
          <div v-if="!shareUrl" class="space-y-3">
            <p class="text-sm text-gray-600">
              Un lien sécurisé valable <strong>30 jours</strong> sera généré.
              Seules les personnes qui le reçoivent pourront y accéder.
            </p>
            <button
              @click="generateLink"
              :disabled="loading"
              class="w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50 transition"
            >
              <span v-if="loading">Génération en cours…</span>
              <span v-else>Générer le lien</span>
            </button>
            <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
          </div>

          <!-- Lien généré -->
          <div v-else class="space-y-4">
            <!-- Champ URL + copie -->
            <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
              <span class="flex-1 truncate text-sm text-gray-700 font-mono">{{ shareUrl }}</span>
              <button
                @click="copyLink"
                class="shrink-0 rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-200 transition"
              >
                {{ copied ? '✓ Copié !' : 'Copier' }}
              </button>
            </div>

            <p class="text-xs text-gray-400 text-center">
              Lien valable jusqu'au {{ expiresAt }}
            </p>

            <!-- Actions -->
            <div class="flex gap-2">
              <!-- Partage natif (mobile) si dispo -->
              <button
                v-if="canShare"
                @click="nativeShare"
                class="flex-1 rounded-xl border border-gray-200 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
              >
                Envoyer via…
              </button>
              <button
                @click="revokeLink"
                class="flex-1 rounded-xl border border-red-100 py-2 text-sm text-red-500 hover:bg-red-50 transition"
              >
                Révoquer le lien
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { ShareIcon, XMarkIcon } from '@heroicons/vue/24/outline'

// ── État ─────────────────────────────────────────────────────────────────────
const open      = ref(false)
const loading   = ref(false)
const error     = ref(null)
const shareUrl  = ref(null)
const expiresAt = ref(null)
const copied    = ref(false)

const canShare = computed(() => typeof navigator.share === 'function')

// ── Actions ──────────────────────────────────────────────────────────────────
function openModal() {
  open.value = true
}

function close() {
  open.value = false
  error.value = null
}

async function generateLink() {
  loading.value = true
  error.value   = null
  try {
    const { data } = await axios.post('/profile/share')
    shareUrl.value  = data.share_url
    expiresAt.value = data.expires_at
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Une erreur est survenue.'
  } finally {
    loading.value = false
  }
}

async function copyLink() {
  await navigator.clipboard.writeText(shareUrl.value)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

async function nativeShare() {
  try {
    await navigator.share({
      title: 'Mon profil d\'orientation',
      text:  'Découvrez mon bilan d\'orientation et mes pistes de métiers.',
      url:   shareUrl.value,
    })
  } catch (_) { /* annulé par l'utilisateur */ }
}

async function revokeLink() {
  await axios.delete('/profile/share')
  shareUrl.value  = null
  expiresAt.value = null
}
</script>
