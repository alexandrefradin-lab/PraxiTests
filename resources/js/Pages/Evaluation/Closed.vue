<script setup>
import { Head } from '@inertiajs/vue3'
defineProps({ reason: String })
</script>

<template>
  <Head title="Lien expiré" />
  <div style="min-height:100vh;background:var(--bg-base);display:flex;align-items:center;justify-content:center;padding:24px;font-family:var(--font-body)">
    <div style="max-width:440px;text-align:center;background:var(--bg-surface);border:1px solid var(--border-light);border-radius:16px;padding:40px 32px">
      <div style="font-size:1.2rem;font-weight:bold;color:var(--color-accent);margin-bottom:6px">Praxi<span style="color:var(--color-primary)">Quest</span></div>
      <h1 style="font-size:1.3rem;color:var(--text-primary);margin:18px 0 10px;font-family:var(--font-display)">Ce lien n'est plus actif</h1>
      <p style="font-size:.95rem;color:var(--text-muted);line-height:1.6;margin:0">
        {{ reason === 'expired'
          ? "L'invitation a expiré. Vous pouvez demander à la personne de vous renvoyer une invitation."
          : "Cette invitation n'est plus disponible." }}
      </p>
    </div>
  </div>
</template>
