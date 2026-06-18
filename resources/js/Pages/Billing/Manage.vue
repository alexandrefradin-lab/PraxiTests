<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    subscribed:     Boolean,
    onTrial:        Boolean,
    trialEndsAt:    String,
    endsAt:         String,
    onGracePeriod:  Boolean,
    activePlanKey:  String,
    activePlanName: String,
    activePeriod:   String,
    card:           Object,
    invoices:       Array,
})

const cancelForm = useForm({})
const resumeForm = useForm({})

function cancel() {
    if (confirm('Confirmer l\'annulation ? Ton accès reste actif jusqu\'à la fin de la période payée.')) {
        cancelForm.post(route('billing.cancel'))
    }
}

function resume() {
    resumeForm.post(route('billing.resume'))
}
</script>

<template>
    <CandidateLayout>
        <div style="max-width: 700px; margin: 0 auto; padding: 3rem 1.5rem">

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem">
                <h1 style="font-family: 'Playfair Display', serif; font-size: 1.75rem; font-weight: 700; color: var(--pt-navy)">
                    Mon abonnement
                </h1>
                <Link :href="route('billing.plans')" style="font-size: 13px; color: var(--pt-text-muted); text-decoration: none; border: 1px solid var(--pt-border); border-radius: 8px; padding: 6px 14px">
                    Voir les plans
                </Link>
            </div>

            <!-- Pas encore abonné -->
            <div
                v-if="!subscribed && !onTrial"
                style="border: 1px solid var(--pt-border); border-radius: 14px; background: var(--pt-white); padding: 2rem; text-align: center"
            >
                <p style="font-size: 15px; color: var(--pt-text-muted); margin-bottom: 1.25rem">
                    Tu n'as pas encore d'abonnement actif.
                </p>
                <Link
                    :href="route('billing.plans')"
                    style="display: inline-block; background: var(--pt-navy); color: #fff; border-radius: 10px; padding: 11px 24px; font-size: 14px; font-weight: 600; text-decoration: none"
                >
                    Choisir un plan
                </Link>
            </div>

            <!-- Abonnement actif -->
            <template v-else>

                <!-- Statut -->
                <div style="border: 1px solid var(--pt-border); border-radius: 14px; background: var(--pt-white); padding: 1.5rem; margin-bottom: 1.25rem">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap">
                        <div>
                            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--pt-text-muted); margin-bottom: 4px">
                                Plan actuel
                            </div>
                            <div style="font-size: 1.25rem; font-weight: 700; color: var(--pt-navy)">
                                {{ activePlanName ?? '—' }}
                                <span v-if="activePeriod" style="font-size: 13px; font-weight: 400; color: var(--pt-text-muted); margin-left: 6px">
                                    ({{ activePeriod === 'yearly' ? 'annuel' : 'mensuel' }})
                                </span>
                            </div>
                        </div>

                        <!-- Badge statut -->
                        <div>
                            <span
                                v-if="onGracePeriod"
                                style="background: #FEE2E2; color: #991B1B; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600"
                            >
                                Annulé — accès jusqu'au {{ endsAt }}
                            </span>
                            <span
                                v-else-if="onTrial"
                                style="background: #FEF3C7; color: #92400E; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600"
                            >
                                Essai gratuit jusqu'au {{ trialEndsAt }}
                            </span>
                            <span
                                v-else
                                style="background: #D1FAE5; color: #065F46; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600"
                            >
                                ✓ Actif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Moyen de paiement -->
                <div style="border: 1px solid var(--pt-border); border-radius: 14px; background: var(--pt-white); padding: 1.5rem; margin-bottom: 1.25rem">
                    <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--pt-text-muted); margin-bottom: 0.75rem">
                        Moyen de paiement
                    </div>
                    <div v-if="card" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem">
                        <div style="display: flex; align-items: center; gap: 10px">
                            <span style="font-size: 20px">💳</span>
                            <div>
                                <span style="font-weight: 600; text-transform: capitalize">{{ card.brand }}</span>
                                <span style="color: var(--pt-text-muted); margin-left: 8px">•••• {{ card.last4 }}</span>
                                <div style="font-size: 12px; color: var(--pt-text-muted); margin-top: 2px">Expire {{ card.exp }}</div>
                            </div>
                        </div>
                        <a
                            :href="route('billing.portal')"
                            style="font-size: 13px; color: var(--pt-navy); text-decoration: none; border: 1px solid var(--pt-border); border-radius: 8px; padding: 6px 14px; font-weight: 500"
                        >
                            Modifier
                        </a>
                    </div>
                    <div v-else style="font-size: 13px; color: var(--pt-text-muted)">
                        Aucune carte enregistrée.
                        <a :href="route('billing.portal')" style="color: var(--pt-navy); text-decoration: none; font-weight: 500; margin-left: 4px">Ajouter →</a>
                    </div>
                </div>

                <!-- Actions -->
                <div style="border: 1px solid var(--pt-border); border-radius: 14px; background: var(--pt-white); padding: 1.5rem; margin-bottom: 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap">
                    <a
                        :href="route('billing.portal')"
                        style="flex: 1; min-width: 160px; text-align: center; background: var(--pt-cream); color: var(--pt-navy); border: 1px solid var(--pt-border); border-radius: 10px; padding: 11px 20px; font-size: 13px; font-weight: 600; text-decoration: none"
                    >
                        Portail Stripe
                    </a>

                    <button
                        v-if="onGracePeriod"
                        @click="resume"
                        :disabled="resumeForm.processing"
                        style="flex: 1; min-width: 160px; background: #D1FAE5; color: #065F46; border: none; border-radius: 10px; padding: 11px 20px; font-size: 13px; font-weight: 600; cursor: pointer"
                    >
                        Réactiver l'abonnement
                    </button>

                    <button
                        v-else-if="subscribed && !onGracePeriod"
                        @click="cancel"
                        :disabled="cancelForm.processing"
                        style="flex: 1; min-width: 160px; background: #FEF2F2; color: #991B1B; border: none; border-radius: 10px; padding: 11px 20px; font-size: 13px; font-weight: 600; cursor: pointer"
                    >
                        Annuler l'abonnement
                    </button>
                </div>

                <!-- Factures -->
                <div style="border: 1px solid var(--pt-border); border-radius: 14px; background: var(--pt-white); padding: 1.5rem">
                    <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--pt-text-muted); margin-bottom: 0.75rem">
                        Historique des factures
                    </div>

                    <div v-if="invoices.length === 0" style="font-size: 13px; color: var(--pt-text-muted)">
                        Aucune facture pour l'instant.
                    </div>

                    <div v-else style="display: flex; flex-direction: column; gap: 6px">
                        <div
                            v-for="inv in invoices"
                            :key="inv.id"
                            style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--pt-border); gap: 0.5rem; flex-wrap: wrap"
                        >
                            <div>
                                <span style="font-size: 13px; color: var(--pt-text)">{{ inv.date }}</span>
                                <span
                                    style="margin-left: 10px; font-size: 11px; border-radius: 10px; padding: 2px 8px; font-weight: 600"
                                    :style="inv.status === 'paid' ? 'background:#D1FAE5;color:#065F46' : 'background:#FEE2E2;color:#991B1B'"
                                >
                                    {{ inv.status === 'paid' ? 'Payée' : inv.status }}
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px">
                                <span style="font-size: 13px; font-weight: 600; color: var(--pt-navy)">{{ inv.total }}</span>
                                <a v-if="inv.url" :href="inv.url" target="_blank" style="font-size: 12px; color: var(--pt-navy); text-decoration: none; border: 1px solid var(--pt-border); border-radius: 6px; padding: 3px 10px">
                                    PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </template>
        </div>
    </CandidateLayout>
</template>
