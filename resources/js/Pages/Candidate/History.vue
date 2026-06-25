<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    attempts: Array,
})

const completed = computed(() => props.attempts.filter(a => a.status === 'completed'))
const inProgress = computed(() => props.attempts.filter(a => a.status === 'in_progress'))

const formatDate = (iso) => {
    if (!iso) return '—'
    return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(iso))
}
</script>

<template>
    <CandidateLayout>
        <Head title="Chroniques du Héros" />

        <div class="max-w-3xl mx-auto">

            <!-- ── En-tête ──────────────────────────────────────────── -->
            <div class="flex items-end justify-between mb-10">
                <div>
                    <h1
                        style="
                            font-family: var(--font-display);
                            font-size: 2.2rem;
                            font-weight: 700;
                            color: var(--text-primary);
                            letter-spacing: -0.02em;
                            line-height: 1.1;
                        "
                    >
                        Chroniques du Héros
                    </h1>
                    <p
                        class="mt-2"
                        style="
                            font-family: var(--font-body);
                            font-size: 0.9rem;
                            color: var(--text-secondary);
                        "
                    >
                        Toutes tes Épreuves passées et en progression.
                    </p>
                </div>

                <Link :href="route('tests.index')" class="pt-btn-ghost text-sm flex-shrink-0">
                    L'Armurerie →
                </Link>
            </div>

            <!-- ── Section "En progression" ────────────────────────── -->
            <section v-if="inProgress.length" class="mb-10">

                <!-- Titre de section -->
                <p
                    class="mb-4"
                    style="
                        font-family: var(--font-data);
                        font-size: 10px;
                        text-transform: uppercase;
                        letter-spacing: 0.12em;
                        color: var(--text-secondary);
                    "
                >
                    En progression · {{ inProgress.length }}
                </p>

                <div class="space-y-3">
                    <div
                        v-for="a in inProgress"
                        :key="a.id"
                        class="pt-card p-5 flex items-center justify-between gap-4"
                        style="
                            border-left: 3px solid var(--color-primary);
                            background: var(--bg-surface);
                        "
                    >
                        <!-- Gauche : nom + date -->
                        <div class="flex-1 min-w-0">
                            <p
                                style="
                                    font-family: var(--font-display);
                                    font-size: 14px;
                                    font-weight: 700;
                                    color: var(--text-primary);
                                "
                            >
                                {{ a.test_name }}
                            </p>
                            <p
                                v-if="a.test_description"
                                class="mt-1"
                                style="
                                    font-family: var(--font-body);
                                    font-size: 12px;
                                    color: var(--text-secondary);
                                    line-height: 1.35;
                                    display: -webkit-box;
                                    -webkit-line-clamp: 2;
                                    -webkit-box-orient: vertical;
                                    overflow: hidden;
                                "
                            >
                                {{ a.test_description }}
                            </p>
                            <p
                                class="mt-1"
                                style="
                                    font-family: var(--font-data);
                                    font-size: 11px;
                                    color: var(--text-secondary);
                                "
                            >
                                Commencé le {{ formatDate(a.started_at) }}
                            </p>
                        </div>

                        <!-- Droite : badge + bouton -->
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span
                                style="
                                    font-family: var(--font-data);
                                    font-size: 10px;
                                    text-transform: uppercase;
                                    letter-spacing: 0.08em;
                                    padding: 3px 9px;
                                    border-radius: 4px;
                                    background: rgba(10,127,160,0.10);
                                    color: var(--color-signal);
                                    border: 1px solid rgba(10,127,160,0.25);
                                "
                            >
                                En cours
                            </span>

                            <Link
                                :href="route('attempt.show', a.id)"
                                class="pt-btn-primary"
                                style="font-size: 13px; padding: 6px 14px;"
                            >
                                Reprendre l'Épreuve →
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Section "Chroniques complètes" ──────────────────── -->
            <section v-if="completed.length">

                <!-- Titre de section -->
                <p
                    class="mb-4"
                    style="
                        font-family: var(--font-data);
                        font-size: 10px;
                        text-transform: uppercase;
                        letter-spacing: 0.12em;
                        color: var(--text-secondary);
                    "
                >
                    Chroniques complètes · {{ completed.length }}
                </p>

                <div class="space-y-3">
                    <div
                        v-for="a in completed"
                        :key="a.id"
                        class="pt-card p-5 flex items-center justify-between gap-4"
                        style="transition: box-shadow 0.15s, transform 0.15s;"
                        @mouseenter="$event.currentTarget.style.boxShadow = 'var(--shadow-elevated)'; $event.currentTarget.style.transform = 'translateY(-1px)'"
                        @mouseleave="$event.currentTarget.style.boxShadow = 'var(--shadow-card)'; $event.currentTarget.style.transform = ''"
                    >
                        <!-- Gauche : nom + date + score -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p
                                    style="
                                        font-family: var(--font-display);
                                        font-size: 14px;
                                        font-weight: 700;
                                        color: var(--text-primary);
                                    "
                                >
                                    {{ a.test_name }}
                                </p>

                                <!-- Badge score Éclats -->
                                <span
                                    v-if="(a.score ?? a.score_eclat ?? a.eclat ?? null) !== null"
                                    style="
                                        font-family: var(--font-data);
                                        font-size: 11px;
                                        font-weight: 700;
                                        color: var(--color-primary);
                                        padding: 2px 8px;
                                        border-radius: 4px;
                                        background: rgba(166,117,32,0.12);
                                        border: 1px solid rgba(166,117,32,0.25);
                                    "
                                >
                                    {{ a.score ?? a.score_eclat ?? a.eclat }} Éclats ✦
                                </span>
                            </div>

                            <p
                                v-if="a.test_description"
                                class="mt-1"
                                style="
                                    font-family: var(--font-body);
                                    font-size: 12px;
                                    color: var(--text-secondary);
                                    line-height: 1.35;
                                    display: -webkit-box;
                                    -webkit-line-clamp: 2;
                                    -webkit-box-orient: vertical;
                                    overflow: hidden;
                                "
                            >
                                {{ a.test_description }}
                            </p>

                            <p
                                class="mt-1"
                                style="
                                    font-family: var(--font-data);
                                    font-size: 11px;
                                    color: var(--text-secondary);
                                "
                            >
                                Complété le {{ formatDate(a.completed_at) }}
                            </p>
                        </div>

                        <!-- Droite : boutons -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <Link
                                v-if="a.result_id"
                                :href="route('results.show', a.result_id)"
                                class="pt-btn-ghost"
                                style="font-size: 13px; padding: 6px 14px;"
                            >
                                Voir la Révélation
                            </Link>

                            <!-- Bouton PDF — <a> natif obligatoire (réponse binaire, pas Inertia) -->
                            <a
                                v-if="a.result_id"
                                :href="route('results.pdf', a.result_id)"
                                class="ac-btn-ghost"
                                style="font-size: 12px; padding: 5px 12px;"
                                target="_blank"
                                rel="noopener"
                            >
                                PDF
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── État vide ─────────────────────────────────────────── -->
            <div
                v-if="!attempts.length"
                class="pt-card p-16 text-center flex flex-col items-center gap-4"
            >
                <i
                    class="ti ti-scroll"
                    style="
                        font-size: 48px;
                        color: var(--text-secondary);
                        opacity: 0.6;
                    "
                ></i>

                <p
                    style="
                        font-family: var(--font-display);
                        font-size: 16px;
                        font-weight: 600;
                        color: var(--text-secondary);
                    "
                >
                    Aucune Chronique pour le moment.
                </p>

                <Link
                    :href="route('tests.index')"
                    class="pt-btn-primary mt-2"
                    style="font-size: 14px;"
                >
                    → Commencer ma première Épreuve
                </Link>
            </div>

        </div>
    </CandidateLayout>
</template>
