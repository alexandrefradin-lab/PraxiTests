<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'

const props = defineProps({
    tests: Array,   // [{ id, name, slug }]
})

const form = useForm({
    test_ids:   props.tests.length === 1 ? [props.tests[0].id] : [],
    email:      '',
    first_name: '',
    last_name:  '',
    expires_at: '',
})

const toggleAll = () => {
    form.test_ids = form.test_ids.length === props.tests.length ? [] : props.tests.map(t => t.id)
}

// Sépare le nom thématique du descriptif : « L'Étoffe du Bâtisseur — Compétences
// entrepreneuriales » → titre en gras + sous-titre discret. Repli : nom complet.
const splitName = (name) => {
    const i = (name || '').indexOf('—')
    if (i === -1) return { title: name, sub: null }
    return { title: name.slice(0, i).trim(), sub: name.slice(i + 1).trim() }
}

const submit = () => form.post(route('admin.invitations.store'))
</script>

<template>
    <AdminLayout>
        <Head title="Inviter un candidat" />

        <!-- En-tête -->
        <div class="flex items-center gap-3 mb-8">
            <Link href="/admin/invitations" class="text-sm" style="color:var(--text-muted)">
                ← Invitations
            </Link>
            <span style="color:var(--border-light)">/</span>
            <span class="text-sm font-medium" style="color:var(--text-primary)">Inviter un candidat</span>
        </div>

        <div class="max-w-xl">
            <h1 class="text-2xl font-semibold mb-1" style="font-family:var(--font-display);color:var(--text-primary)">
                Inviter un candidat
            </h1>
            <p class="text-sm mb-8" style="color:var(--text-muted)">
                Un email avec un lien personnalisé sera envoyé immédiatement.
            </p>

            <FlashAlert />

            <form @submit.prevent="submit" class="pt-card p-6 space-y-5">

                <!-- Épreuves (cases à cocher) -->
                <div>
                    <div class="flex items-center justify-between">
                        <label class="pt-label">Épreuves à faire passer <span style="color:var(--color-danger)">*</span></label>
                        <button type="button" @click="toggleAll" class="text-xs hover:underline" style="color:var(--color-primary-dark,#7D5510)">
                            {{ form.test_ids.length === tests.length ? 'Tout décocher' : 'Tout cocher' }}
                        </button>
                    </div>
                    <!-- État vide : aucun test publié à proposer -->
                    <div v-if="!tests.length" class="mt-2 p-4 rounded-lg text-sm" style="background:var(--bg-elevated);color:var(--text-muted)">
                        Aucune épreuve publiée pour le moment. Publiez au moins un test depuis
                        <Link href="/admin/tests" class="ac-link-primary">l'éditeur de tests</Link> avant d'inviter un candidat.
                    </div>
                    <!-- Toutes les épreuves visibles d'un coup : grille de cartes cochables -->
                    <div v-else class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label
                            v-for="t in tests"
                            :key="t.id"
                            class="inv-test-card"
                            :class="{ 'inv-test-card--checked': form.test_ids.includes(t.id) }"
                        >
                            <input
                                type="checkbox"
                                :value="t.id"
                                v-model="form.test_ids"
                                class="inv-test-check"
                            >
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold leading-snug" style="color:var(--text-primary)">
                                    {{ splitName(t.name).title }}
                                </span>
                                <span v-if="splitName(t.name).sub" class="block text-xs mt-0.5 leading-snug" style="color:var(--text-muted)">
                                    {{ splitName(t.name).sub }}
                                </span>
                            </span>
                        </label>
                    </div>
                    <p class="text-xs mt-1" style="color:var(--text-muted)">
                        {{ form.test_ids.length }} épreuve{{ form.test_ids.length > 1 ? 's' : '' }} sélectionnée{{ form.test_ids.length > 1 ? 's' : '' }} —
                        le candidat recevra un seul email listant toutes les épreuves.
                    </p>
                    <p v-if="form.errors.test_ids" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.test_ids }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="inv-email" class="pt-label">Email du candidat <span style="color:var(--color-danger)">*</span></label>
                    <input
                        id="inv-email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="off"
                        placeholder="candidat@exemple.fr"
                        class="pt-input mt-2"
                    >
                    <p v-if="form.errors.email" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.email }}</p>
                </div>

                <!-- Prénom / Nom -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="inv-firstname" class="pt-label">Prénom</label>
                        <input
                            id="inv-firstname"
                            v-model="form.first_name"
                            type="text"
                            placeholder="Marie"
                            class="pt-input mt-2"
                        >
                    </div>
                    <div>
                        <label for="inv-lastname" class="pt-label">Nom</label>
                        <input
                            id="inv-lastname"
                            v-model="form.last_name"
                            type="text"
                            placeholder="Dupont"
                            class="pt-input mt-2"
                        >
                    </div>
                </div>

                <!-- Message d'invitation : identique pour tous les candidats -->
                <div class="p-4 rounded-lg text-sm" style="background:var(--bg-elevated);color:var(--text-secondary)">
                    <strong style="color:var(--text-primary)">Message envoyé (identique pour tous) :</strong><br>
                    « Ces épreuves s'inscrivent dans le cadre de votre accompagnement. Répondez spontanément —
                    il n'y a pas de bonnes ou de mauvaises réponses. Vos résultats vous seront restitués
                    directement dans votre espace personnel. »
                </div>

                <!-- Expiration -->
                <div>
                    <label for="inv-expires" class="pt-label">Date d'expiration <span class="font-normal" style="color:var(--text-muted)">(défaut : 30 jours)</span></label>
                    <input
                        id="inv-expires"
                        v-model="form.expires_at"
                        type="date"
                        class="pt-input mt-2"
                    >
                    <p v-if="form.errors.expires_at" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.expires_at }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-4 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="ac-btn-primary"
                    >
                        {{ form.processing ? 'Envoi en cours…' : 'Envoyer l\'invitation' }}
                    </button>
                    <Link href="/admin/conseiller" class="text-sm" style="color:var(--text-muted)">Annuler</Link>
                </div>
            </form>

            <!-- Info SMTP -->
            <div class="mt-6 p-4 rounded-lg text-sm" style="background:var(--bg-elevated);color:var(--text-muted)">
                <strong style="color:var(--text-secondary)">Comment ça marche ?</strong><br>
                Dès la validation, un email est envoyé au candidat avec un lien sécurisé (token unique)
                et la liste des épreuves cochées. En cliquant, il crée son compte et accède à l'Armurerie
                pour passer ses épreuves. Vous suivez son avancement depuis le tableau de bord et sa fiche lead.
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Carte d'épreuve cochable : case alignée en haut, bord doré à la sélection */
.inv-test-card {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.7rem 0.85rem;
    border: 1px solid var(--border-light, rgba(166,117,32,0.18));
    border-radius: 10px;
    background: var(--bg-surface, #F7F0DF);
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
}
.inv-test-card:hover {
    border-color: var(--color-primary, #A67520);
}
.inv-test-card--checked {
    border-color: var(--color-primary, #A67520);
    background: rgba(166, 117, 32, 0.08);
    box-shadow: inset 0 0 0 1px var(--color-primary, #A67520);
}
.inv-test-check {
    accent-color: var(--color-primary, #A67520);
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 2px; /* aligné sur la 1re ligne du titre, pas centré sur le bloc */
    cursor: pointer;
}
</style>
