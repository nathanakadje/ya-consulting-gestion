<template>
    <!-- resources/js/Pages/Projects/Form.vue -->
    <!-- Utilisé pour CREATE et EDIT (project prop null = création) -->
    <Head :title="isEditing ? 'Modifier le projet' : 'Nouveau projet'" />

    <AppLayout
        :title="isEditing ? 'Modifier le projet' : 'Nouveau projet'"
        :breadcrumb="isEditing ? project.name : 'Création'"
    >
        <div class="max-w-4xl mx-auto">
            <!-- En-tête formulaire -->
            <div class="flex items-center gap-4 mb-8">
                <Link
                    href="/projects"
                    class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all"
                >
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <div>
                    <h3
                        class="font-headline text-xl font-bold text-gray-900 dark:text-white"
                    >
                        {{
                            isEditing
                                ? `Modifier « ${project.name} »`
                                : "Créer un nouveau projet"
                        }}
                    </h3>
                    <p class="text-sm text-gray-400 mt-0.5">
                        {{
                            isEditing
                                ? "Modifiez les informations du projet."
                                : "Remplissez les informations pour créer le projet."
                        }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- ══ Section 1 : Informations générales ════════════ -->
                <FormSection title="Informations générales" icon="info">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nom du projet -->
                        <div class="md:col-span-2">
                            <InputLabel for="name" value="Nom du projet *" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="ex: Audit Système RTI 2024"
                                :error="form.errors.name"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Référence -->
                        <div>
                            <InputLabel
                                for="reference"
                                value="Référence (auto si vide)"
                            />
                            <TextInput
                                id="reference"
                                v-model="form.reference"
                                type="text"
                                placeholder="YA-2024-001"
                                :error="form.errors.reference"
                            />
                            <InputError :message="form.errors.reference" />
                        </div>

                        <!-- Client -->
                        <div>
                            <InputLabel for="client_id" value="Client *" />
                            <SelectInput
                                id="client_id"
                                v-model="form.client_id"
                                :error="form.errors.client_id"
                            >
                                <option value="">
                                    — Sélectionner un client —
                                </option>
                                <option
                                    v-for="c in clients"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </SelectInput>
                            <InputError :message="form.errors.client_id" />
                        </div>

                        <!-- Chef de projet -->
                        <div>
                            <InputLabel
                                for="project_lead_id"
                                value="Chef de projet"
                            />
                            <SelectInput
                                id="project_lead_id"
                                v-model="form.project_lead_id"
                            >
                                <option value="">— Aucun —</option>
                                <option
                                    v-for="u in users"
                                    :key="u.id"
                                    :value="u.id"
                                >
                                    {{ u.name }}
                                </option>
                            </SelectInput>
                        </div>

                        <!-- Statut -->
                        <div>
                            <InputLabel for="status" value="Statut *" />
                            <SelectInput
                                id="status"
                                v-model="form.status"
                                :error="form.errors.status"
                            >
                                <option value="en_cours">🟢 En cours</option>
                                <option value="en_pause">🟡 En pause</option>
                                <option value="termine">✅ Terminé</option>
                            </SelectInput>
                            <InputError :message="form.errors.status" />
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                placeholder="Décrivez les objectifs du projet..."
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/30 outline-none transition-all resize-none placeholder:text-gray-400"
                            ></textarea>
                        </div>
                    </div>
                </FormSection>

                <!-- ══ Section 2 : Dates ══════════════════════════════ -->
                <FormSection title="Planning" icon="calendar_month">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <InputLabel
                                for="start_date"
                                value="Date de début *"
                            />
                            <TextInput
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                :error="form.errors.start_date"
                            />
                            <InputError :message="form.errors.start_date" />
                        </div>
                        <div>
                            <InputLabel
                                for="end_date_planned"
                                value="Date de fin prévue *"
                            />
                            <TextInput
                                id="end_date_planned"
                                v-model="form.end_date_planned"
                                type="date"
                                :error="form.errors.end_date_planned"
                            />
                            <InputError
                                :message="form.errors.end_date_planned"
                            />
                        </div>
                        <div v-if="isEditing">
                            <InputLabel
                                for="end_date_actual"
                                value="Date de fin réelle"
                            />
                            <TextInput
                                id="end_date_actual"
                                v-model="form.end_date_actual"
                                type="date"
                            />
                        </div>
                    </div>
                </FormSection>

                <!-- ══ Section 3 : Budget ═════════════════════════════ -->
                <FormSection title="Budget" icon="account_balance_wallet">
                    <!-- Budget total (calculé automatiquement OU saisi manuellement) -->
                    <div class="mb-5">
                        <InputLabel
                            for="budget"
                            value="Budget total (FCFA) *"
                        />
                        <div class="relative">
                            <TextInput
                                id="budget"
                                v-model="form.budget"
                                type="number"
                                min="0"
                                step="1000"
                                placeholder="0"
                                :error="form.errors.budget"
                                class="pr-24"
                            />
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400"
                                >FCFA</span
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            💡 Le budget total peut être réparti ci-dessous
                            (optionnel).
                        </p>
                        <InputError :message="form.errors.budget" />
                    </div>

                    <!-- Répartition budget -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <BudgetLineInput
                            v-model="form.budget_main_oeuvre"
                            label="Main d'œuvre"
                            icon="groups"
                            color="blue"
                        />
                        <BudgetLineInput
                            v-model="form.budget_materiel"
                            label="Matériel"
                            icon="inventory_2"
                            color="violet"
                        />
                        <BudgetLineInput
                            v-model="form.budget_transport"
                            label="Transport"
                            icon="local_shipping"
                            color="amber"
                        />
                        <BudgetLineInput
                            v-model="form.budget_autres"
                            label="Autres"
                            icon="category"
                            color="gray"
                        />
                    </div>

                    <!-- Barre récap répartition -->
                    <div
                        v-if="form.budget > 0"
                        class="mt-5 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl"
                    >
                        <div
                            class="flex justify-between text-xs font-semibold text-gray-500 mb-2"
                        >
                            <span>Répartition du budget</span>
                            <span
                                :class="
                                    budgetDiff !== 0
                                        ? 'text-amber-500'
                                        : 'text-emerald-600'
                                "
                            >
                                Alloué : {{ formatCurrency(totalAllocated) }}
                                <span v-if="budgetDiff !== 0">
                                    ({{ budgetDiff > 0 ? "+" : ""
                                    }}{{ formatCurrency(budgetDiff) }})</span
                                >
                            </span>
                        </div>
                        <div
                            class="h-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden flex"
                        >
                            <div
                                class="h-full bg-blue-500 transition-all"
                                :style="{
                                    width: pct(form.budget_main_oeuvre) + '%',
                                }"
                            ></div>
                            <div
                                class="h-full bg-violet-500 transition-all"
                                :style="{
                                    width: pct(form.budget_materiel) + '%',
                                }"
                            ></div>
                            <div
                                class="h-full bg-amber-500 transition-all"
                                :style="{
                                    width: pct(form.budget_transport) + '%',
                                }"
                            ></div>
                            <div
                                class="h-full bg-gray-400 transition-all"
                                :style="{
                                    width: pct(form.budget_autres) + '%',
                                }"
                            ></div>
                        </div>
                        <div class="flex gap-4 mt-2">
                            <LegendDot color="bg-blue-500" label="M.O." />
                            <LegendDot color="bg-violet-500" label="Matériel" />
                            <LegendDot color="bg-amber-500" label="Transport" />
                            <LegendDot color="bg-gray-400" label="Autres" />
                        </div>
                    </div>
                </FormSection>

                <!-- ══ Actions ════════════════════════════════════════ -->
                <div class="flex items-center justify-between pt-2">
                    <Link
                        href="/projects"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-xl shadow-sm shadow-primary-600/25 transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span
                            v-if="form.processing"
                            class="material-symbols-outlined text-[18px] animate-spin"
                            >progress_activity</span
                        >
                        <span
                            v-else
                            class="material-symbols-outlined text-[18px]"
                            >{{ isEditing ? "save" : "add_circle" }}</span
                        >
                        {{
                            isEditing
                                ? "Enregistrer les modifications"
                                : "Créer le projet"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

// ── Sous-composants locaux (définis dans ce fichier) ─────────
import FormSection from "@/Components/FormSection.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";
import InputError from "@/Components/InputError.vue";
import BudgetLineInput from "@/Components/BudgetLineInput.vue";
import LegendDot from "@/Components/LegendDot.vue";

// ── Props depuis ProjectController::create() ou edit() ───────
const props = defineProps({
    project: { type: Object, default: null },
    clients: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

const isEditing = computed(() => !!props.project?.id);

// ── Formulaire Inertia ────────────────────────────────────────
// useForm gère : les données, les erreurs de validation, et l'état loading
const form = useForm({
    name: props.project?.name ?? "",
    reference: props.project?.reference ?? "",
    description: props.project?.description ?? "",
    client_id: props.project?.client_id ?? "",
    project_lead_id: props.project?.project_lead_id ?? "",
    status: props.project?.status ?? "en_cours",
    budget: props.project?.budget ?? "",
    budget_main_oeuvre: props.project?.budget_main_oeuvre ?? "",
    budget_materiel: props.project?.budget_materiel ?? "",
    budget_transport: props.project?.budget_transport ?? "",
    budget_autres: props.project?.budget_autres ?? "",
    start_date: props.project?.start_date_raw ?? "",
    end_date_planned: props.project?.end_date_planned_raw ?? "",
    end_date_actual: props.project?.end_date_actual_raw ?? "",
});

function submit() {
    if (isEditing.value) {
        // PUT /projects/{id}
        form.put(`/projects/${props.project.id}`);
    } else {
        // POST /projects
        form.post("/projects");
    }
}

// ── Calculs budget ────────────────────────────────────────────
const totalAllocated = computed(() =>
    [
        form.budget_main_oeuvre,
        form.budget_materiel,
        form.budget_transport,
        form.budget_autres,
    ].reduce((sum, v) => sum + (parseFloat(v) || 0), 0),
);

const budgetDiff = computed(
    () => totalAllocated.value - (parseFloat(form.budget) || 0),
);

function pct(value) {
    const budget = parseFloat(form.budget) || 0;
    if (!budget) return 0;
    return Math.min(((parseFloat(value) || 0) / budget) * 100, 100);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat("fr-FR").format(amount ?? 0) + " FCFA";
}
</script>
