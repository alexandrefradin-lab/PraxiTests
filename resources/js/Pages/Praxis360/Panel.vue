<script setup>
/**
 * Feedback 360° — page CANDIDAT : choisir ses évaluateurs (manager / pairs /
 * collaborateurs) et lancer les invitations. Suit la collecte et l'anonymat.
 */
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
  attempt: Object,
  panel: Object,
  relations: Object,      // { manager: 'Manager', pair: '...', collaborateur: '...' }
  invitations: Array,
  aggregate: Object,
})

const form = useForm({
  evaluators: [{ name: '', email: '', relation: 'manager' }],
})

const addRow = () => form.evaluators.push({ name: '', email: '', relation: 'pair' })
const removeRow = (i) => form.evaluators.splice(i, 1)

const submitInvites = () => {
  form.transform(d => ({ evaluators: d.evaluators.filter(e => e.email) }))
    .post(route('panel360.invite', props.panel.id), {
      preserveScroll: true,
      onSuccess: () => form.reset(),
    })
}

const sending = ref(false)
const sendAll = () => {
  sending.value = true
  router.post(route('panel360.send', props.panel.id), {}, {
    preserveScroll: true,
    onFinish: () => (sending.value = false),
  })
}

const removeInvite = (id) => {
  router.delete(route('panel360.invitation.destroy', id), { preserveScroll: true })
}

const counts = computed(() => props.aggregate?.counts ?? {})
const pendingCount = computed(() => props.invitations.filter(i => i.status === 'pending').length)

const statusLabel = (s) => ({
  pending: 'À envoyer', sent: 'Envoyée', opened: 'Ouverte',
  completed: 'Complétée', declined: 'Déclinée',
}[s] ?? s)
</script>

<template>
  <CandidateLayout>
    <Head title="Mon feedback 360°" />

    <div style="max-width:780px;margin:0 auto">

      <div class="flex items-end justify-between mb-8">
        <div>
          <h1 style="font-family:var(--font-display);font-size:2rem;font-weight:700;color:var(--text-primary);line-height:1.1">
            Mon feedback 360°
          </h1>
          <p class="mt-2" style="font-size:.9rem;color:var(--text-secondary)">
            Invitez les personnes qui vous connaissent au travail. Leurs réponses sont
            <strong>anonymes</strong> et ne s'affichent qu'à partir de {{ panel.threshold }} réponses par catégorie.
          </p>
        </div>
        <Link :href="route('results.show', attempt.id)" class="pt-btn-ghost text-sm flex-shrink-0">← Mes résultats</Link>
      </div>

      <!-- Statut de collecte -->
      <div class="pt-card" style="padding:18px 22px;margin-bottom:22px;display:flex;gap:26px;flex-wrap:wrap">
        <div><div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-secondary)">Invités</div>
          <div style="font-size:1.5rem;font-weight:700;color:var(--text-primary)">{{ counts.invited ?? invitations.length }}</div></div>
        <div><div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-secondary)">Réponses reçues</div>
          <div style="font-size:1.5rem;font-weight:700;color:var(--pt-gold)">{{ counts.total ?? 0 }}</div></div>
        <div v-for="(label, key) in relations" :key="key">
          <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-secondary)">{{ label }}</div>
          <div style="font-size:1.5rem;font-weight:700;color:var(--text-primary)">{{ counts[key] ?? 0 }}</div></div>
      </div>

      <!-- Ajouter des évaluateurs -->
      <div class="pt-card" style="padding:22px;margin-bottom:22px">
        <h2 style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin:0 0 14px">Ajouter des évaluateurs</h2>

        <div v-for="(ev, i) in form.evaluators" :key="i" class="flex gap-2 mb-3" style="align-items:center">
          <input v-model="ev.name" placeholder="Nom (optionnel)" class="pt-input" style="flex:1" />
          <input v-model="ev.email" type="email" placeholder="email@exemple.com" class="pt-input" style="flex:1.4" />
          <select v-model="ev.relation" class="pt-input" style="flex:1">
            <option v-for="(label, key) in relations" :key="key" :value="key">{{ label }}</option>
          </select>
          <button type="button" @click="removeRow(i)" v-if="form.evaluators.length > 1"
                  style="border:none;background:none;color:var(--text-secondary);cursor:pointer;font-size:1.2rem">×</button>
        </div>

        <div class="flex gap-3 mt-2" style="align-items:center">
          <button type="button" @click="addRow" class="pt-btn-ghost text-sm">+ Ajouter une ligne</button>
          <button type="button" @click="submitInvites" :disabled="form.processing" class="pt-btn text-sm">
            Enregistrer
          </button>
        </div>
      </div>

      <!-- Liste des invitations -->
      <div class="pt-card" style="padding:22px" v-if="invitations.length">
        <div class="flex items-center justify-between mb-4">
          <h2 style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin:0">Évaluateurs invités</h2>
          <button @click="sendAll" :disabled="sending || pendingCount === 0" class="pt-btn text-sm">
            Envoyer {{ pendingCount ? `(${pendingCount})` : '' }}
          </button>
        </div>

        <table style="width:100%;border-collapse:collapse;font-size:.9rem">
          <tr v-for="inv in invitations" :key="inv.id" style="border-bottom:1px solid var(--pt-cream-dark)">
            <td style="padding:9px 6px">{{ inv.name || inv.email }}</td>
            <td style="padding:9px 6px;color:var(--text-secondary)">{{ relations[inv.relation] }}</td>
            <td style="padding:9px 6px">
              <span :style="{color: inv.status==='completed' ? 'var(--pt-gold)' : 'var(--text-secondary)'}">{{ statusLabel(inv.status) }}</span>
            </td>
            <td style="padding:9px 6px;text-align:right">
              <button v-if="inv.status !== 'completed'" @click="removeInvite(inv.id)"
                      style="border:none;background:none;color:var(--text-secondary);cursor:pointer">Retirer</button>
            </td>
          </tr>
        </table>
      </div>

    </div>
  </CandidateLayout>
</template>
