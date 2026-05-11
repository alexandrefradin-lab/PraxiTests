<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ test: Object })

const form = useForm({
    slug: props.test?.slug ?? '',
    name: props.test?.name ?? '',
    description: props.test?.description ?? '',
    type: props.test?.type ?? 'questionnaire',
    scoring_engine: props.test?.scoring_engine ?? 'default',
    estimated_minutes: props.test?.estimated_minutes ?? 10,
    published: props.test?.published ?? false,
    public: props.test?.public ?? false,
    gamification: props.test?.gamification ?? {},
    neuromarketing: props.test?.neuromarketing ?? {},
    scoring_config: props.test?.scoring_config ?? {},
})

const submit = () => {
    if (props.test?.id) {
        form.put(route('admin.tests.update', props.test.id))
    } else {
        form.post(route('admin.tests.store'))
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="test?.id ? 'Éditer test' : 'Nouveau test'" />

        <h1 class="text-2xl font-semibold mb-6">{{ test?.id ? 'Éditer' : 'Nouveau test' }}</h1>

        <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-6">
            <section class="pt-card p-6 lg:col-span-2 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nom</label>
                    <input v-model="form.name" required class="pt-input mt-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Slug (URL)</label>
                    <input v-model="form.slug" required pattern="[a-z0-9\-]+" class="pt-input mt-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea v-model="form.description" rows="3" class="pt-input mt-2"></textarea>
                </div>
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Type</label>
                        <select v-model="form.type" class="pt-input mt-2">
                            <option value="questionnaire">Questionnaire</option>
                            <option value="situational">Mises en situation</option>
                            <option value="projective">Projectif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Moteur scoring</label>
                        <input v-model="form.scoring_engine" class="pt-input mt-2" placeholder="default">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Durée (min)</label>
                        <input v-model.number="form.estimated_minutes" type="number" min="1" class="pt-input mt-2">
                    </div>
                </div>
            </section>

            <aside class="pt-card p-6 space-y-4 h-fit">
                <h3 class="font-semibold">Publication</h3>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.published" class="rounded border-slate-300 text-indigo-600">
                    Publié
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.public" class="rounded border-slate-300 text-indigo-600">
                    Accessible sans invitation
                </label>

                <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full mt-4">
                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
                <p v-if="form.recentlySuccessful" class="text-emerald-600 text-xs">Enregistré ✓</p>
            </aside>
        </form>
    </AdminLayout>
</template>
