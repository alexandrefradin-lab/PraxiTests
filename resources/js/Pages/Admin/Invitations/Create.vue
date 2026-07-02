<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    tests: Array,   // [{ id, name, slug }]
})

const form = useForm({
    test_ids:   props.tests.length === 1 ? [props.tests[0].id] : [],
    email:      '',
    first_name: '',
    last_name:  '',
    message:    '',
    expires_at: '',
})

const toggleAll = () => {
    form.test_ids = form.test_ids.length === props.tests.length ? [] : props.tests.map(t => t.id)
}

const submit = () => form.post(route('admin.invitations.store'))
</script>

<template>
    <AdminLayout>
        <Head title="Inviter un candidat" />

        <!-- En-tête -->
        <div class="flex items-center gap-3 mb-8">
            <Link href="/admin/conseiller" class="text-sm" style="color:var(--text-muted)">
                ← Tableau de bord
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

            <!-- Alerte flash succès (redirigée mais au cas où) -->
            <div v-if="$page.props.flash?.success" class="mb-6 p-4 rounded-lg text-sm" style="background:#ecfdf5;color:#065f46;border:1px solid #6ee7b7">
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submit" class="pt-card p-6 space-y-5">

                <!-- Épreuves (cases à cocher) -->
                <div>
                    <div class="flex items-center justify-between">
                        <label class="pt-label">Épreuves à faire passer <span style="color:#ef4444">*</span></label>
                        <button type="button" @click="toggleAll" class="text-xs hover:underline" style="color:var(--color-primary-dark,#7D5510)">
                            {{ form.test_ids.length === tests.length ? 'Tout décocher' : 'Tout cocher' }}
                        </button>
                    </div>
                    <div class="mt-2 rounded-lg border p-1" style="border-color:var(--border-light);max-height:260px;overflow-y:auto">
                        <label
                            v-for="t in tests"
                            :key="t.id"
                            class="flex items-center gap-3 px-3 py-2 rounded cursor-pointer transition-colors"
                            :style="form.test_ids.includes(t.id) ? 'background:rgba(166,117,32,0.08)' : ''"
                        >
                            <input
                                type="checkbox"
                                :value="t.id"
                                v-model="form.test_ids"
                                style="accent-color:var(--color-primary,#A67520);width:16px;height:16px;flex-shrink:0"
                            >
                            <span class="text-sm" style="color:var(--text-primary)">{{ t.name }}</span>
                        </label>
                    </div>
                    <p class="text-xs mt-1" style="color:var(--text-muted)">
                        {{ form.test_ids.length }} épreuve{{ form.test_ids.length > 1 ? 's' : '' }} sélectionnée{{ form.test_ids.length > 1 ? 's' : '' }} —
                        le candidat recevra un seul email listant toutes les épreuves.
                    </p>
                    <p v-if="form.errors.test_ids" class="text-xs mt-1" style="color:#ef4444">{{ form.errors.test_ids }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="inv-email" class="pt-label">Email du candidat <span style="color:#ef4444">*</span></label>
                    <input
                        id="inv-email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="off"
                        placeholder="candidat@exemple.fr"
                        class="pt-input mt-2"
                    >
                    <p v-if="form.errors.email" class="text-xs mt-1" style="color:#ef4444">{{ form.errors.email }}</p>
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

                <!-- Message personnalisé -->
                <div>
                    <label for="inv-message" class="pt-label">Message personnalisé <span class="font-normal" style="color:var(--text-muted)">(optionnel)</span></label>
                    <textarea
                        id="inv-message"
                        v-model="form.message"
                        rows="3"
                        placeholder="Ex. : Ce test fait partie de votre parcours d'orientation. N'hésitez pas à me contacter si vous avez des questions."
                        class="pt-input mt-2"
                        maxlength="1000"
                    ></textarea>
                    <p class="text-xs mt-1" style="color:var(--text-muted)">Apparaît dans le corps de l'email d'invitation.</p>
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
                    <p v-if="form.errors.expires_at" class="text-xs mt-1" style="color:#ef4444">{{ form.errors.expires_at }}</p>
                </div>

                <!-- Erreur générique -->
                <p v-if="form.errors.message" class="text-xs" style="color:#ef4444">{{ form.errors.message }}</p>

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
