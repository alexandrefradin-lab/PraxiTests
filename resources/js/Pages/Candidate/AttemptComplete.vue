<script setup>
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  attempt: Object,
  eclatsGagnes: { type: Number, default: 0 },
  levelUp: { type: Boolean, default: false },
  newLevel: { type: Number, default: null },
  redirectUrl: String,
});

onMounted(() => {
  setTimeout(() => {
    if (props.redirectUrl) {
      router.visit(props.redirectUrl);
    } else if (props.attempt?.id) {
      router.visit(route('candidate.results.show', props.attempt.id));
    }
  }, 3000);
});
</script>

<template>
  <div class="attempt-complete-overlay">
    <div class="attempt-complete-card">
      <div class="eclats-burst">
        <span class="eclat-icon">✦</span>
        <span class="eclat-count">+{{ eclatsGagnes }}</span>
        <span class="eclat-label">Éclats gagnés</span>
      </div>

      <div v-if="levelUp" class="level-banner">
        ✧ Niveau {{ newLevel }} atteint ! ✧
      </div>

      <h1 class="title">Épreuve accomplie !</h1>
      <p class="subtitle">Ton Grimoire se révèle…</p>

      <div class="dots">
        <span /><span /><span />
      </div>
    </div>
  </div>
</template>

<style scoped>
.attempt-complete-overlay {
  position: fixed;
  inset: 0;
  background: rgba(10, 7, 2, 0.93);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.attempt-complete-card {
  text-align: center;
  color: #F0E8D4;
  padding: 3rem 4rem;
}
.eclats-burst {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  margin-bottom: 2rem;
}
.eclat-icon {
  font-size: 3.5rem;
  color: #D4A843;
  animation: pulse-gold 1.2s ease-in-out infinite;
}
.eclat-count {
  font-size: 3rem;
  font-weight: 800;
  color: #D4A843;
  font-family: 'Space Mono', monospace;
  letter-spacing: 0.05em;
}
.eclat-label {
  font-size: 0.75rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  opacity: 0.6;
}
.level-banner {
  background: linear-gradient(135deg, #A67520, #D4A843);
  color: #1a0e00;
  font-weight: 700;
  font-size: 1rem;
  padding: 0.6rem 2rem;
  border-radius: 2rem;
  display: inline-block;
  margin-bottom: 1.5rem;
}
.title {
  font-size: 2.2rem;
  font-weight: 700;
  margin: 0.5rem 0;
}
.subtitle {
  font-size: 1rem;
  opacity: 0.6;
  margin-bottom: 2rem;
}
.dots {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}
.dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #A67520;
  animation: bounce 1.2s ease-in-out infinite;
}
.dots span:nth-child(2) { animation-delay: 0.2s; }
.dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce {
  0%, 80%, 100% { transform: scale(0.5); opacity: 0.3; }
  40% { transform: scale(1.1); opacity: 1; }
}
@keyframes pulse-gold {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}
</style>
