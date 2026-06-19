<script setup>
import { ref, computed } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    user: Object,
})

// ── Export ────────────────────────────────────────────────────────────────────
const exportLoading = ref(false)

const downloadExport = () => {
    exportLoading.value = true
    // Déclenche le téléchargement JSON directement via navigation
    window.location.href = route('gdpr.export')
    setTimeout(() => { exportLoading.value = false }, 2000)
}

// ── Suppression ───────────────────────────────────────────────────────────────
const showDeleteModal  = ref(false)
const deleteStep       = ref(1) // 1 = avertissements, 2 = confirmation mot de passe
const deleteCheckboxes = ref({ understood: false, irreversible: false, data: false })

const allBoxesChecked = computed(() =>
    deleteCheckboxes.value.understood &&
    deleteCheckboxes.value.irreversible &&
    deleteCheckboxes.value.data
)

const deleteForm = useForm({ password: '' })

const openDeleteModal = () => {
    deleteStep.value = 1
    deleteCheckboxes.value = { understood: false, irreversible: false, data: false }
    deleteForm.reset()
    showDeleteModal.value = true
}

const proceedToPassword = () => {
    if (allBoxesChecked.value) deleteStep.value = 2
}

const submitDelete = () => {
    deleteForm.delete(route('gdpr.destroy'), {
        onSuccess: () => { showDeleteModal.value = false },
    })
}
</script>

<template>
    <CandidateLayout>
        <Head title="Mes données — RGPD" />

        <div class="max-w-[700px] mx-auto">

            <!-- ── En-tête ────────────────────────────────────────────── -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-6">
                    <Link :href="route('history')" class="ac-btn-ghost" style="font-size:12px; padding:4px 10px;">
                        ← Retour
                    </Link>
                </div>

                <div class="flex items-center gap-3 mb-2">
                    <div class="deco-line"></div>
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                        <path d="M8 0L9.6 6.4L16 8L9.6 9.6L8 16L6.4 9.6L0 8L6.4 6.4L8 0Z" fill="var(--color-primary)"/>
                    </svg>
                    <div class="deco-line"></div>
                </div>

                <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:700; color:var(--text-primary); letter-spacing:-0.02em; line-height:1.1">
                    Mes données & Confidentialité
                </h1>
                <p class="mt-2" style="font-family:var(--font-body); font-size:0.875rem; color:var(--text-secondary); line-height:1.6">
                    Conformément au Règlement Général sur la Protection des Données (RGPD),
                    vous disposez d'un droit d'accès, de portabilité et d'effacement de vos données personnelles.
                </p>
            </div>

            <!-- ── Carte : Données stockées ───────────────────────────── -->
            <section class="pt-card p-6 mb-5">
                <h2 class="section-title mb-5">
                    <span class="section-icon">📋</span>
                    Ce que nous conservons
                </h2>

                <div class="data-grid">
                    <div class="data-item">
                        <span class="data-label">Compte</span>
                        <span class="data-value">Nom, email, date d'inscription</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Profil professionnel</span>
                        <span class="data-value">Statut, ancienneté, poste, secteur, consentements</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">CV</span>
                        <span class="data-value">Fichier stocké hors accès public, utilisé uniquement pour l'extraction IA</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Résultats psychométriques</span>
                        <span class="data-value">Scores aux tests, synthèse IA, métiers suggérés</span>
                    </div>
                    <div class="data-item">
                        <span class="data-label">Données de navigation</span>
                        <span class="data-value">Logs d'activité anonymisés, sessions</span>
                    </div>
                </div>

                <div class="mt-5 p-3 rounded-lg" style="background:rgba(10,127,160,0.06); border:1px solid rgba(10,127,160,0.2)">
                    <p style="font-family:var(--font-data); font-size:11px; color:var(--color-signal); line-height:1.5">
                        🔒 Vos données psychométriques ne sont jamais vendues ni partagées avec des tiers.
                        Elles sont utilisées exclusivement pour générer vos résultats.
                    </p>
                </div>
            </section>

            <!-- ── Carte : Export (Art. 15 & 20) ─────────────────────── -->
            <section class="pt-card p-6 mb-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h2 class="section-title mb-1">
                            <span class="section-icon">📦</span>
                            Exporter mes données
                        </h2>
                        <p style="font-family:var(--font-body); font-size:13px; color:var(--text-secondary); line-height:1.55; margin-top:6px">
                            Téléchargez une copie complète de toutes vos données personnelles au format JSON
                            (Art. 15 & 20 RGPD — droit d'accès et portabilité).
                            Le fichier inclut votre compte, profil, résultats et les éléments ayant guidé les recommandations IA.
                        </p>
                        <p class="mt-3" style="font-family:var(--font-data); font-size:11px; color:var(--text-muted)">
                            Compte : {{ user.email }} · Inscrit le {{ new Date(user.created_at).toLocaleDateString('fr-FR', { day:'2-digit', month:'long', year:'numeric' }) }}
                        </p>
                    </div>

                    <button
                        @click="downloadExport"
                        :disabled="exportLoading"
                        class="ac-btn-ghost flex-shrink-0 mt-1"
                        style="white-space:nowrap"
                    >
                        <span v-if="exportLoading">Préparation…</span>
                        <span v-else>⬇ Télécharger mes données</span>
                    </button>
                </div>
            </section>

            <!-- ── Carte : Suppression (Art. 17) ──────────────────────── -->
            <section class="pt-card p-6" style="border:1px solid rgba(176,48,32,0.2)">
                <h2 class="section-title mb-1" style="color:var(--color-danger)">
                    <span class="section-icon">⚠️</span>
                    Supprimer mon compte
                </h2>
                <p style="font-family:var(--font-body); font-size:13px; color:var(--text-secondary); line-height:1.55; margin-top:6px">
                    Exercez votre droit à l'effacement (Art. 17 RGPD). La suppression est <strong style="color:var(--text-primary)">définitive et irréversible</strong> :
                    toutes vos données personnelles, résultats psychométriques, CV et historique seront effacés de nos serveurs.
                </p>

                <div class="mt-5 p-3 rounded-lg" style="background:rgba(176,48,32,0.05); border:1px solid rgba(176,48,32,0.15)">
                    <p style="font-family:var(--font-data); font-size:11px; color:var(--color-danger); line-height:1.5">
                        ⚠ Votre abonnement actif sera annulé immédiatement. Aucun remboursement au prorata ne sera effectué.
                    </p>
                </div>

                <button
                    @click="openDeleteModal"
                    class="ac-btn-danger mt-5"
                    style="font-size:13px"
                >
                    Supprimer définitivement mon compte
                </button>
            </section>

        </div>

        <!-- ═══════════════════════════════════════════════════════════════
             MODAL DE SUPPRESSION
        ═══════════════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="showDeleteModal" class="modal-backdrop" @click.self="showDeleteModal = false">
                    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modal-title">

                        <!-- En-tête modal -->
                        <div class="modal-header">
                            <div class="modal-ornament"></div>
                            <h2 id="modal-title" class="modal-title">
                                Suppression définitive du compte
                            </h2>
                            <button @click="showDeleteModal = false" class="modal-close" aria-label="Fermer">✕</button>
                        </div>

                        <!-- ── ÉTAPE 1 : Prise de conscience ────────── -->
                        <div v-if="deleteStep === 1" class="modal-body">
                            <p class="modal-intro">
                                Avant de continuer, confirmez que vous avez compris les conséquences de cette action.
                            </p>

                            <div class="checks-list">
                                <label class="check-item">
                                    <input
                                        type="checkbox"
                                        v-model="deleteCheckboxes.understood"
                                        class="check-input"
                                    />
                                    <span class="check-text">
                                        Je comprends que <strong>toutes mes données seront effacées</strong> :
                                        compte, profil, CV, résultats psychométriques et historique.
                                    </span>
                                </label>

                                <label class="check-item">
                                    <input
                                        type="checkbox"
                                        v-model="deleteCheckboxes.irreversible"
                                        class="check-input"
                                    />
                                    <span class="check-text">
                                        Je comprends que cette action est <strong>irréversible</strong>.
                                        Il ne sera pas possible de récupérer mes données après suppression.
                                    </span>
                                </label>

                                <label class="check-item">
                                    <input
                                        type="checkbox"
                                        v-model="deleteCheckboxes.data"
                                        class="check-input"
                                    />
                                    <span class="check-text">
                                        Je souhaite exporter mes données <strong>avant</strong> de les supprimer
                                        (ou j'ai déjà téléchargé mon export).
                                    </span>
                                </label>
                            </div>

                            <!-- Bouton export rapide -->
                            <div class="mt-4 flex items-center gap-3">
                                <a :href="route('gdpr.export')" class="ac-btn-ghost" style="font-size:12px">
                                    ⬇ Télécharger mes données d'abord
                                </a>
                            </div>

                            <div class="modal-footer">
                                <button @click="showDeleteModal = false" class="ac-btn-ghost">
                                    Annuler
                                </button>
                                <button
                                    @click="proceedToPassword"
                                    :disabled="!allBoxesChecked"
                                    class="ac-btn-danger"
                                    style="opacity: 1"
                                    :style="{ opacity: allBoxesChecked ? '1' : '0.4', cursor: allBoxesChecked ? 'pointer' : 'not-allowed' }"
                                >
                                    Continuer →
                                </button>
                            </div>
                        </div>

                        <!-- ── ÉTAPE 2 : Confirmation mot de passe ──── -->
                        <div v-else-if="deleteStep === 2" class="modal-body">
                            <div class="final-warning">
                                <p style="font-family:var(--font-data); font-size:12px; color:var(--color-danger); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px">
                                    ⚠ Action irréversible
                                </p>
                                <p style="font-family:var(--font-body); font-size:13px; color:var(--text-secondary); line-height:1.5">
                                    Entrez votre mot de passe pour confirmer la suppression définitive
                                    de votre compte <strong style="color:var(--text-primary)">{{ user.email }}</strong>.
                                </p>
                            </div>

                            <form @submit.prevent="submitDelete" class="mt-5">
                                <div class="field-group">
                                    <label class="field-label" for="delete-password">
                                        Mot de passe actuel
                                    </label>
                                    <input
                                        id="delete-password"
                                        type="password"
                                        v-model="deleteForm.password"
                                        class="field-input"
                                        placeholder="Votre mot de passe"
                                        autocomplete="current-password"
                                        required
                                    />
                                    <p v-if="deleteForm.errors.password" class="field-error">
                                        {{ deleteForm.errors.password }}
                                    </p>
                                </div>

                                <div class="modal-footer mt-4">
                                    <button
                                        type="button"
                                        @click="deleteStep = 1"
                                        class="ac-btn-ghost"
                                    >
                                        ← Retour
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="deleteForm.processing || !deleteForm.password"
                                        class="btn-delete-confirm"
                                    >
                                        <span v-if="deleteForm.processing">Suppression…</span>
                                        <span v-else>Supprimer définitivement</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </Transition>
        </Teleport>

    </CandidateLayout>
</template>

<style scoped>
/* ── Déco ────────────────────────────────────────────────────────────── */
.deco-line {
    height: 1px;
    width: 60px;
    background: linear-gradient(to right, transparent, var(--color-primary));
}

/* ── Sections ─────────────────────────────────────────────────────────── */
.section-title {
    font-family: var(--font-display);
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: -0.01em;
}

.section-icon {
    font-size: 16px;
}

/* ── Grille données ───────────────────────────────────────────────────── */
.data-grid {
    display: flex;
    flex-direction: column;
    gap: 0;
    border: 1px solid var(--border-light);
    border-radius: var(--r);
    overflow: hidden;
}

.data-item {
    display: grid;
    grid-template-columns: 180px 1fr;
    gap: 12px;
    padding: 10px 14px;
    border-bottom: 1px solid var(--border-light);
}

.data-item:last-child {
    border-bottom: none;
}

.data-label {
    font-family: var(--font-data);
    font-size: 11px;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding-top: 1px;
    flex-shrink: 0;
}

.data-value {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--text-primary);
    line-height: 1.4;
}

/* ── Modal ────────────────────────────────────────────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(42, 30, 8, 0.6);
    backdrop-filter: blur(4px);
    z-index: 200;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-box {
    background: var(--bg-surface);
    border: 1px solid var(--border-mid);
    border-radius: var(--r-xl);
    box-shadow: var(--shadow-elevated);
    width: 100%;
    max-width: 500px;
    overflow: hidden;
}

.modal-header {
    position: relative;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-ornament {
    width: 3px;
    height: 20px;
    background: var(--color-danger);
    border-radius: 2px;
    flex-shrink: 0;
}

.modal-title {
    font-family: var(--font-display);
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    flex: 1;
    letter-spacing: -0.01em;
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 16px;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: var(--r-sm);
    line-height: 1;
    flex-shrink: 0;
    transition: color 0.15s;
}
.modal-close:hover { color: var(--text-primary); }

.modal-body {
    padding: 20px 24px;
}

.modal-intro {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.55;
    margin-bottom: 16px;
}

/* ── Checkboxes ───────────────────────────────────────────────────────── */
.checks-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.check-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    padding: 10px 12px;
    border-radius: var(--r);
    border: 1px solid var(--border-light);
    background: var(--bg-elevated);
    transition: border-color 0.15s, background 0.15s;
}

.check-item:has(.check-input:checked) {
    border-color: var(--border-strong);
    background: var(--bg-surface);
}

.check-input {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 1px;
    accent-color: var(--color-primary);
    cursor: pointer;
}

.check-text {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.45;
}

/* ── Pied de modal ────────────────────────────────────────────────────── */
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--border-light);
}

/* ── Étape 2 ──────────────────────────────────────────────────────────── */
.final-warning {
    padding: 12px 14px;
    border-radius: var(--r);
    background: rgba(176,48,32,0.05);
    border: 1px solid rgba(176,48,32,0.2);
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-label {
    font-family: var(--font-data);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary);
}

.field-input {
    width: 100%;
    padding: 10px 14px;
    border-radius: var(--r);
    border: 1px solid var(--border-mid);
    background: var(--bg-elevated);
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--text-primary);
    outline: none;
    transition: border-color 0.15s;
}

.field-input:focus {
    border-color: var(--color-danger);
    box-shadow: 0 0 0 3px rgba(176,48,32,0.1);
}

.field-error {
    font-family: var(--font-data);
    font-size: 11px;
    color: var(--color-danger);
    margin-top: 2px;
}

.btn-delete-confirm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 18px;
    border-radius: var(--r);
    border: none;
    background: var(--color-danger);
    color: #fff;
    font-family: var(--font-display);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, opacity 0.15s;
}
.btn-delete-confirm:hover:not(:disabled) {
    background: #8c2010;
}
.btn-delete-confirm:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

/* ── Transitions ──────────────────────────────────────────────────────── */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}
.modal-fade-enter-active .modal-box,
.modal-fade-leave-active .modal-box {
    transition: transform 0.2s ease, opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.modal-fade-enter-from .modal-box {
    transform: translateY(12px);
    opacity: 0;
}
</style>
