<script setup>
import { Head } from '@inertiajs/vue3'
defineProps({ reason: String })
</script>

<template>
  <Head title="Lien expiré" />
  <div style="min-height:100vh;background:#F0E8D4;display:flex;align-items:center;justify-content:center;padding:24px;font-family:Segoe UI,Helvetica,Arial,sans-serif">
    <div style="max-width:440px;text-align:center;background:#FFFDF7;border:1px solid #CBBE9E;border-radius:16px;padding:40px 32px">
      <div style="font-size:1.2rem;font-weight:bold;color:#1C1408;margin-bottom:6px">Praxi<span style="color:#A67520">Quest</span></div>
      <h1 style="font-size:1.3rem;color:#2A1E08;margin:18px 0 10px">Ce lien n'est plus actif</h1>
      <p style="font-size:.95rem;color:#6B5A3E;line-height:1.6;margin:0">
        {{ reason === 'expired'
          ? "L'invitation a expiré. Vous pouvez demander à la personne de vous renvoyer une invitation."
          : "Cette invitation n'est plus disponible." }}
      </p>
    </div>
  </div>
</template>
