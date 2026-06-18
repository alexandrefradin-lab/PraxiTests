<script setup>
import { ref, computed } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    plans:        Object,
    trialDays:    Number,
    activePlan:   String,
    activePeriod: String,
    onTrial:      Boolean,
    subscribed:   Boolean,
})

const period = ref(props.activePeriod ?? 'monthly')

const form = useForm({ plan: '', period: '' })

function subscribe(planKey) {
    form.plan   = planKey
    form.period = period.value
    form.post(route('billing.checkout'))
}

function formatPrice(cents) {
    return (cents / 100).toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

const planList = computed(() =>
    Object.entries(props.plans).map(([key, p]) => ({ key, ...p }))
)
</script>

<template>
    <CandidateLayout>
        <div style="max-width: 1050px; margin: 0 auto; padding: 3rem 1.5rem">

            <!-- Header -->
            <div style="text-align: center; margin-bottom: 3rem">
                <h1 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--pt-navy); margin-bottom: 0.75rem">
                    Choisissez votre plan
                </h1>
                <p style="font-size: 1rem; color: var(--pt-text-muted); max-width: 480px; margin: 0 auto">
                    {{ trialDays }} jours d'essai gratuit, sans carte bancaire. Annulable à tout moment.
                </p>

                <!-- Trial badge -->
                <div v-if="onTrial" style="display: inline-flex; align-items: center; gap: 6px; margin-top: 1rem; background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600; color: #92400E">
                    ⏱ Essai gratuit en cours
                </div>
            </div>

            <!-- Toggle mensuel / annuel -->
            <div style="display: flex; justify-content: center; margin-bottom: 2.5rem">
                <div style="display: inline-flex; border: 1px solid var(--pt-border); border-radius: 10px; overflow: hidden">
                    <button
                        @click="period = 'monthly'"
                        :style="{
                            padding: '8px 20px',
                            fontSize: '13px',
                            fontWeight: '600',
                            border: 'none',
                            cursor: 'pointer',
                            transition: 'all 0.15s',
                            background: period === 'monthly' ? 'var(--pt-navy)' : 'transparent',
                            color: period === 'monthly' ? '#fff' : 'var(--pt-text-muted)',
                        }"
                    >Mensuel</button>
                    <button
                        @click="period = 'yearly'"
                        :style="{
                            padding: '8px 20px',
                            fontSize: '13px',
                            fontWeight: '600',
                            border: 'none',
                            cursor: 'pointer',
                            transition: 'all 0.15s',
                            background: period === 'yearly' ? 'var(--pt-navy)' : 'transparent',
                            color: period === 'yearly' ? '#fff' : 'var(--pt-text-muted)',
                        }"
                    >
                        Annuel
                        <span style="margin-left: 6px; font-size: 11px; background: #D1FAE5; color: #065F46; border-radius: 10px; padding: 1px 7px">−17%</span>
                    </button>
                </div>
            </div>

            <!-- Grille des plans -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; align-items: start">
                <div
                    v-for="plan in planList"
                    :key="plan.key"
                    :style="{
                        border: plan.highlighted ? '2px solid var(--pt-navy)' : '1px solid var(--pt-border)',
                        borderRadius: '16px',
                        background: 'var(--pt-white)',
                        padding: '1.75rem',
                        position: 'relative',
                        boxShadow: plan.highlighted ? 'var(--pt-shadow-md)' : 'var(--pt-shadow-xs)',
                    }"
                >
                    <!-- Badge populaire -->
                    <div
                        v-if="plan.highlighted"
                        style="position: absolute; top: -13px; left: 50%; transform: translateX(-50%); background: var(--pt-navy); color: #fff; font-size: 11px; font-weight: 700; letter-spacing: 0.05em; border-radius: 20px; padding: 3px 14px; white-space: nowrap"
                    >
                        LE PLUS POPULAIRE
                    </div>

                    <!-- Couleur / nom -->
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1rem">
                        <div :style="{ width: '10px', height: '10px', borderRadius: '50%', background: plan.color, flexShrink: 0 }"></div>
                        <span style="font-size: 18px; font-weight: 700; color: var(--pt-text)">{{ plan.name }}</span>
                    </div>

                    <p style="font-size: 13px; color: var(--pt-text-muted); margin-bottom: 1.25rem; line-height: 1.5">
                        {{ plan.description }}
                    </p>

                    <!-- Prix -->
                    <div style="margin-bottom: 1.5rem">
                        <span style="font-size: 2rem; font-weight: 800; color: var(--pt-navy)">
                            {{ formatPrice(period === 'yearly' ? plan.price_yearly / 12 : plan.price_monthly) }}
                        </span>
                        <span style="font-size: 13px; color: var(--pt-text-muted)"> / mois</span>
                        <div v-if="period === 'yearly'" style="font-size: 12px; color: #065F46; font-weight: 500; margin-top: 2px">
                            Facturé {{ formatPrice(plan.price_yearly) }} / an
                        </div>
                    </div>

                    <!-- Features -->
                    <ul style="list-style: none; padding: 0; margin: 0 0 1.75rem; display: flex; flex-direction: column; gap: 8px">
                        <li
                            v-for="feature in plan.features"
                            :key="feature"
                            style="display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: var(--pt-text)"
                        >
                            <span style="color: #10B981; font-size: 15px; flex-shrink: 0; margin-top: 1px">✓</span>
                            {{ feature }}
                        </li>
                    </ul>

                    <!-- CTA -->
                    <button
                        @click="subscribe(plan.key)"
                        :disabled="form.processing || activePlan === plan.key"
                        :style="{
                            width: '100%',
                            padding: '12px',
                            borderRadius: '10px',
                            border: 'none',
                            cursor: (form.processing || activePlan === plan.key) ? 'default' : 'pointer',
                            fontWeight: '600',
                            fontSize: '14px',
                            transition: 'all 0.15s',
                            background: activePlan === plan.key
                                ? '#E5E7EB'
                                : plan.highlighted
                                    ? 'var(--pt-navy)'
                                    : 'var(--pt-cream)',
                            color: activePlan === plan.key
                                ? '#6B7280'
                                : plan.highlighted
                                    ? '#fff'
                                    : 'var(--pt-navy)',
                            border: plan.highlighted ? 'none' : '1px solid var(--pt-border)',
                        }"
                    >
                        <template v-if="activePlan === plan.key && activePeriod === period">
                            ✓ Plan actuel
                        </template>
                        <template v-else-if="form.processing && form.plan === plan.key">
                            Redirection…
                        </template>
                        <template v-else-if="subscribed">
                            Passer à ce plan
                        </template>
                        <template v-else>
                            Commencer l'essai gratuit
                        </template>
                    </button>
                </div>
            </div>

            <!-- Lien vers gestion -->
            <div v-if="subscribed" style="text-align: center; margin-top: 2rem">
                <Link :href="route('billing.manage')" style="font-size: 13px; color: var(--pt-text-muted); text-decoration: underline">
                    Gérer mon abonnement actuel →
                </Link>
            </div>

            <!-- Garanties -->
            <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 3rem; flex-wrap: wrap">
                <div v-for="g in ['Sans engagement', 'Paiement sécurisé Stripe', 'Annulation en 1 clic', 'Données hébergées en France']"
                    :key="g"
                    style="font-size: 12px; color: var(--pt-text-muted); display: flex; align-items: center; gap: 5px"
                >
                    <span style="color: #10B981">✓</span> {{ g }}
                </div>
            </div>
        </div>
    </CandidateLayout>
</template>
