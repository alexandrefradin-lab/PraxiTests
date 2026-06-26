<script setup>
/**
 * Feedback 360° — page ÉVALUATEUR (invité, anonyme, sans compte).
 * Questionnaire de fréquence à la 3e personne sur le candidat + verbatims.
 * Page autonome (pas de layout authentifié).
 */
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
  token: String,
  subjectName: String,
  relationLabel: String,
  scale: {
    type: Object,
    default: () => ({ min: 1, max: 5, min_label: 'Jamais', max_label: 'Toujours' })
  },                        // { min, max, min_label, max_label }
  questions: Array,         // [{ id, section, prompt }]
  verbatims: Object,        // { strength: 'label', growth: '...', advice: '...' }
  answered: Object,         // { question_id: value }
})

const answers = ref({ ...(props.answered || {}) })
const verbatimValues = ref({ strength: '', growth: '', advice: '' })
const submitting = ref(false)

const steps = computed(() => {
  const arr = []
  for (let v = props.scale.min; v <= props.scale.max; v++) arr.push(v)
  return arr
})

const sections = computed(() => {
  const map = {}
  props.questions.forEach(q => {
    (map[q.section] ??= []).push(q)
  })
  return map
})

const answeredCount = computed(() => Object.keys(answers.value).length)
const allAnswered = computed(() => answeredCount.value >= props.questions.length)

const choose = (qid, value) => {
  answers.value[qid] = value
  router.post(route('eval360.answer', props.token),
    { question_id: qid, value },
    { preserveScroll: true, preserveState: true })
}

const submit = () => {
  submitting.value = true
  router.post(route('eval360.complete', props.token),
    { verbatims: verbatimValues.value },
    { onFinish: () => (submitting.value = false) })
}

const freqLabel = (v) => {
  if (v === props.scale.min) return props.scale.min_label
  if (v === props.scale.max) return props.scale.max_label
  return ''
}
</script>

<template>
  <Head title="Feedback 360°" />
  <div style="min-height:100vh;background:var(--bg-base);font-family:var(--font-body);color:var(--text-primary);padding:32px 16px">
    <div style="max-width:680px;margin:0 auto">

      <!-- En-tête -->
      <div style="text-align:center;margin-bottom:8px">
        <div style="font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--color-accent)">Praxi<span style="color:var(--color-primary)">Quest</span></div>
        <div style="font-family:var(--font-data);font-size:.7rem;letter-spacing:2px;text-transform:uppercase;color:var(--text-muted)">Feedback 360°</div>
      </div>

      <div style="background:var(--color-accent);border:1px solid var(--color-primary);border-radius:14px;padding:24px 26px;margin:18px 0 24px">
        <p style="color:var(--bg-base);font-size:1.05rem;line-height:1.6;margin:0 0 8px">
          Vous évaluez <strong style="color:var(--bg-base)">{{ subjectName }}</strong> en tant que
          <strong style="color:var(--color-primary)">{{ relationLabel }}</strong>.
        </p>
        <p style="color:rgba(240,232,212,0.6);font-size:.85rem;line-height:1.6;margin:0">
          Pour chaque affirmation, indiquez à quelle fréquence cette personne adopte ce comportement.
          Vos réponses sont <strong>anonymes</strong> : {{ subjectName }} ne verra que des résultats agrégés.
        </p>
      </div>

      <!-- Progression -->
      <div style="position:sticky;top:0;background:var(--bg-base);padding:8px 0 12px;z-index:5">
        <div style="height:8px;background:var(--bg-elevated);border-radius:5px;overflow:hidden">
          <div :style="{width: (answeredCount/questions.length*100)+'%', height:'100%', background:'var(--color-primary)'}"></div>
        </div>
        <div style="font-family:var(--font-data);font-size:.75rem;color:var(--text-muted);margin-top:4px;text-align:right">{{ answeredCount }} / {{ questions.length }}</div>
      </div>

      <!-- Questions par section -->
      <div v-for="(qs, section) in sections" :key="section" style="margin-bottom:22px">
        <h3 style="font-family:var(--font-data);font-size:.8rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--color-primary-dark);font-weight:700;margin:18px 0 10px">{{ section }}</h3>

        <div v-for="q in qs" :key="q.id"
             style="background:var(--bg-surface);border:1px solid var(--border-mid);border-radius:10px;padding:14px 16px;margin-bottom:10px">
          <div style="font-size:.95rem;line-height:1.5;margin-bottom:10px">{{ q.prompt }}</div>
          <div style="display:flex;gap:6px;flex-wrap:wrap">
            <button v-for="v in steps" :key="v" type="button" @click="choose(q.id, v)"
              :style="{
                flex:'1', minWidth:'54px', padding:'8px 4px', borderRadius:'8px', cursor:'pointer',
                fontFamily:'var(--font-data)',
                border: '1px solid ' + (answers[q.id]===v ? 'var(--color-primary)' : 'var(--border-mid)'),
                background: answers[q.id]===v ? 'var(--color-primary)' : 'var(--bg-base)',
                color: answers[q.id]===v ? 'var(--color-accent)' : 'var(--text-muted)',
                fontWeight: answers[q.id]===v ? '700' : '400'
              }">
              <div style="font-size:1rem">{{ v }}</div>
              <div style="font-size:.6rem;line-height:1.1;margin-top:2px">{{ freqLabel(v) }}</div>
            </button>
          </div>
        </div>
      </div>

      <!-- Verbatims -->
      <div style="background:var(--bg-surface);border:1px solid var(--border-mid);border-radius:12px;padding:20px;margin:24px 0">
        <h3 style="font-family:var(--font-data);font-size:.8rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--color-primary-dark);font-weight:700;margin:0 0 12px">En quelques mots (facultatif)</h3>
        <div v-for="(label, key) in verbatims" :key="key" style="margin-bottom:14px">
          <label :for="'verbatim-' + key" style="display:block;font-size:.9rem;margin-bottom:6px;color:var(--text-secondary)">{{ label }}</label>
          <textarea :id="'verbatim-' + key" v-model="verbatimValues[key]" rows="2"
            style="width:100%;border:1px solid var(--border-mid);border-radius:8px;padding:9px;font-family:var(--font-body);font-size:.9rem;background:var(--bg-base);color:var(--text-primary);resize:vertical"></textarea>
        </div>
      </div>

      <!-- Soumettre -->
      <div style="text-align:center;margin:8px 0 40px">
        <p v-if="!allAnswered" style="font-size:.8rem;color:var(--text-muted);margin-bottom:10px">
          Il reste {{ questions.length - answeredCount }} affirmation(s) à renseigner.
        </p>
        <button @click="submit" :disabled="!allAnswered || submitting"
          :style="{
            background: allAnswered ? 'var(--color-primary)' : 'var(--bg-elevated)', color:'var(--color-accent)', border:'none',
            fontFamily:'var(--font-display)', fontWeight:'700', fontSize:'1rem', padding:'14px 34px', borderRadius:'10px',
            cursor: allAnswered ? 'pointer' : 'not-allowed'
          }">
          Envoyer mon évaluation
        </button>
      </div>

    </div>
  </div>
</template>
