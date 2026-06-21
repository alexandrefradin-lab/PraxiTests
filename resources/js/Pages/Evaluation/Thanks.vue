<script setup>
import { Head } from '@inertiajs/vue3'
defineProps({ already: Boolean })
</script>

<template>
  <Head title="Merci" />
  <div style="min-height:100vh;background:#F0E8D4;display:flex;align-items:center;justify-content:center;padding:24px;font-family:Segoe UI,Helvetica,Arial,sans-serif">
    <div style="max-width:440px;text-align:center;background:#FFFDF7;border:1px solid #CBBE9E;border-radius:16px;padding:40px 32px">
      <div style="font-size:1.2rem;font-weight:bold;color:#1C1408;margin-bottom:6px">Praxi<span style="color:#A67520">Quest</span></div>
      <div style="font-size:2.4rem;margin:14px 0">🙏</div>
      <h1 style="font-size:1.4rem;color:#2A1E08;margin:0 0 10px">
        {{ already ? 'Vous avez déjà répondu' : 'Merci pour votre contribution !' }}
      </h1>
      <p style="font-size:.95rem;color:#6B5A3E;line-height:1.6;margin:0">
        Votre regard a bien été enregistré de façon anonyme. Il aidera cette personne à mieux
        comprendre son fonctionnement. Vous pouvez fermer cette page.
      </p>
    </div>
  </div>
</template>
