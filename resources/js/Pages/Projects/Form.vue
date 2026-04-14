<script setup>
import { computed, ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    project: { type: Object, default: null },
    clients: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

const isEditing = computed(() => !!props.project?.id);

// ── Formulaire Inertia ────────────────────────────────────
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
    // IMPORTANT : format Y-m-d pour les inputs type="date"
    start_date: props.project?.start_date_raw ?? "",
    end_date_planned: props.project?.end_date_planned_raw ?? "",
    end_date_actual: props.project?.end_date_actual_raw ?? "",
});

function submit() {
    if (isEditing.value) {
        form.put(`/projects/${props.project.id}`);
    } else {
        form.post("/projects");
    }
}

// ── Calculs budget ────────────────────────────────────────
const budgetTotal = computed(() => parseFloat(form.budget) || 0);

const totalAllocated = computed(() =>
    [
        form.budget_main_oeuvre,
        form.budget_materiel,
        form.budget_transport,
        form.budget_autres,
    ].reduce((s, v) => s + (parseFloat(v) || 0), 0),
);
const budgetDiff = computed(() => totalAllocated.value - budgetTotal.value);

function pct(val) {
    if (!budgetTotal.value) return 0;
    return Math.min(((parseFloat(val) || 0) / budgetTotal.value) * 100, 100);
}

// ── Durée calculée ────────────────────────────────────────
const durationDays = computed(() => {
    if (!form.start_date || !form.end_date_planned) return null;
    const diff = new Date(form.end_date_planned) - new Date(form.start_date);
    return Math.max(Math.ceil(diff / (1000 * 60 * 60 * 24)), 0);
});

// ── Stepper ───────────────────────────────────────────────
const step = ref(0);
const steps = [
    { label: "Informations", icon: "info" },
    { label: "Planning", icon: "calendar_month" },
    { label: "Budget", icon: "account_balance_wallet" },
];

// Validation par étape avant avancer
function goNext() {
    if (step.value === 0 && !form.name) {
        form.validate("name");
        return;
    }
    if (step.value < steps.length - 1) step.value++;
}
function goPrev() {
    if (step.value > 0) step.value--;
}

// ── Helpers ───────────────────────────────────────────────
function inputCls(err) {
    return [
        "w-full px-4 py-2.5 rounded-xl border text-[13px] outline-none transition-all",
        "bg-white dark:bg-gray-800/50 text-gray-900 dark:text-white",
        "placeholder:text-gray-400 dark:placeholder:text-gray-600",
        err
            ? "border-red-400 dark:border-red-500 focus:ring-2 focus:ring-red-400/20"
            : "border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/40",
    ];
}

function fmt(v) {
    return new Intl.NumberFormat("fr-FR").format(Math.round(v ?? 0)) + " FCFA";
}

// Ligne budget dans la répartition
const budgetLines = [
    {
        key: "budget_main_oeuvre",
        label: "Main d'œuvre",
        icon: "groups",
        colorBar: "bg-blue-500",
        colorIcon: "text-blue-500",
    },
    {
        key: "budget_materiel",
        label: "Matériel",
        icon: "inventory_2",
        colorBar: "bg-violet-500",
        colorIcon: "text-violet-500",
    },
    {
        key: "budget_transport",
        label: "Transport",
        icon: "local_shipping",
        colorBar: "bg-amber-500",
        colorIcon: "text-amber-500",
    },
    {
        key: "budget_autres",
        label: "Autres coûts",
        icon: "category",
        colorBar: "bg-gray-400",
        colorIcon: "text-gray-400",
    },
];
</script>

<template>
    <Head :title="isEditing ? 'Modifier le projet' : 'Nouveau projet'" />

    <AppLayout
        :title="isEditing ? 'Modifier' : 'Nouveau projet'"
        :breadcrumb="
            isEditing ? `Projets / ${project.name}` : 'Projets / Nouveau'
        "
    >
        <div class="max-w-3xl mx-auto">
            <!-- En-tête -->
            <div class="flex items-center gap-3 mb-8">
                <Link
                    href="/projects"
                    class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all flex-shrink-0"
                >
                    <span class="material-symbols-outlined text-[20px]"
                        >arrow_back</span
                    >
                </Link>
                <div>
                    <h3
                        class="font-headline font-extrabold text-[19px] text-gray-900 dark:text-white tracking-tight leading-tight"
                    >
                        {{
                            isEditing
                                ? `Modifier « ${project.name} »`
                                : "Créer un nouveau projet"
                        }}
                    </h3>
                    <p class="text-[11.5px] text-gray-400 mt-0.5">
                        {{
                            isEditing
                                ? "Mettez à jour les informations."
                                : "Remplissez les champs pour créer votre projet."
                        }}
                    </p>
                </div>
            </div>

            <!-- Stepper navigation -->
            <div
                class="flex items-center gap-2 mb-8 p-1.5 bg-white dark:bg-[#111318] border border-gray-100 dark:border-gray-800/70 rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
            >
                <button
                    v-for="(s, i) in steps"
                    :key="i"
                    type="button"
                    @click="step = i"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl transition-all',
                        step === i
                            ? 'bg-primary-600 text-white shadow-sm'
                            : i < step
                              ? 'bg-emerald-50 dark:bg-emerald-600/10 text-emerald-600 dark:text-emerald-400'
                              : 'text-gray-400 dark:text-gray-600 hover:text-gray-600 dark:hover:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/[0.03]',
                    ]"
                >
                    <span
                        class="material-symbols-outlined text-[17px]"
                        :style="
                            step === i || i < step
                                ? 'font-variation-settings:\'FILL\' 1'
                                : ''
                        "
                    >
                        {{ i < step ? "check_circle" : s.icon }}
                    </span>
                    <span class="text-[12px] font-bold hidden sm:block">{{
                        s.label
                    }}</span>
                    <!-- Dot erreur -->
                    <span
                        v-if="
                            (i === 0 &&
                                (form.errors.name || form.errors.client_id)) ||
                            (i === 1 &&
                                (form.errors.start_date ||
                                    form.errors.end_date_planned)) ||
                            (i === 2 && form.errors.budget)
                        "
                        class="w-1.5 h-1.5 rounded-full bg-red-400 flex-shrink-0 ml-0.5"
                    ></span>
                </button>
            </div>

            <!-- Erreurs globales (si submit depuis n'importe quelle étape) -->
            <div
                v-if="
                    Object.keys(form.errors).length &&
                    form.wasSuccessful === false
                "
                class="mb-5 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-xl"
            >
                <p
                    class="text-[12px] font-semibold text-red-600 dark:text-red-400 flex items-center gap-2"
                >
                    <span
                        class="material-symbols-outlined text-[16px]"
                        style="font-variation-settings: &quot;FILL&quot; 1"
                        >error</span
                    >
                    Veuillez corriger les erreurs avant de continuer.
                </p>
            </div>

            <form @submit.prevent="submit">
                <!-- ══════════════════════════════════
                     ÉTAPE 0 — INFORMATIONS
                ══════════════════════════════════ -->
                <div v-show="step === 0" class="space-y-5">
                    <div class="card p-6 space-y-5">
                        <p class="section-label">Informations générales</p>

                        <!-- Nom -->
                        <div>
                            <label for="name" class="field-label"
                                >Nom du projet *</label
                            >
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                autocomplete="off"
                                placeholder="ex : Audit Système RTI 2024"
                                :class="inputCls(form.errors.name)"
                            />
                            <p v-if="form.errors.name" class="field-error">
                                <span
                                    class="material-symbols-outlined text-[13px]"
                                    >error</span
                                >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Référence + Statut -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="reference" class="field-label">
                                    Référence
                                    <span
                                        class="normal-case font-normal text-gray-400 ml-1"
                                        >(auto si vide)</span
                                    >
                                </label>
                                <input
                                    id="reference"
                                    v-model="form.reference"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="YA-2024-001"
                                    :class="inputCls(form.errors.reference)"
                                />
                                <p
                                    v-if="form.errors.reference"
                                    class="field-error"
                                >
                                    <span
                                        class="material-symbols-outlined text-[13px]"
                                        >error</span
                                    >
                                    {{ form.errors.reference }}
                                </p>
                            </div>
                            <div>
                                <label for="status" class="field-label"
                                    >Statut *</label
                                >
                                <div class="relative">
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        :class="[
                                            inputCls(form.errors.status),
                                            'appearance-none pr-9 cursor-pointer',
                                        ]"
                                    >
                                        <option value="en_cours">
                                            🟢 En cours
                                        </option>
                                        <option value="en_pause">
                                            🟡 En pause
                                        </option>
                                        <option value="termine">
                                            ✅ Terminé
                                        </option>
                                    </select>
                                    <span
                                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px] pointer-events-none"
                                        >expand_more</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Client + Chef de projet -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="client_id" class="field-label"
                                    >Client *</label
                                >
                                <div class="relative">
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        :class="[
                                            inputCls(form.errors.client_id),
                                            'appearance-none pr-9 cursor-pointer',
                                        ]"
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
                                    </select>
                                    <span
                                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px] pointer-events-none"
                                        >expand_more</span
                                    >
                                </div>
                                <p
                                    v-if="form.errors.client_id"
                                    class="field-error"
                                >
                                    <span
                                        class="material-symbols-outlined text-[13px]"
                                        >error</span
                                    >
                                    {{ form.errors.client_id }}
                                </p>
                            </div>
                            <div>
                                <label for="project_lead_id" class="field-label"
                                    >Chef de projet</label
                                >
                                <div class="relative">
                                    <select
                                        id="project_lead_id"
                                        v-model="form.project_lead_id"
                                        :class="[
                                            inputCls(null),
                                            'appearance-none pr-9 cursor-pointer',
                                        ]"
                                    >
                                        <option value="">— Aucun —</option>
                                        <option
                                            v-for="u in users"
                                            :key="u.id"
                                            :value="u.id"
                                        >
                                            {{ u.name }}
                                        </option>
                                    </select>
                                    <span
                                        class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px] pointer-events-none"
                                        >expand_more</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="field-label">
                                Description
                                <span
                                    class="normal-case font-normal text-gray-400 ml-1"
                                    >(optionnel)</span
                                >
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Décrivez les objectifs, le périmètre et le contexte du projet..."
                                :class="[
                                    inputCls(null),
                                    'resize-none leading-relaxed',
                                ]"
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="button"
                            @click="goNext()"
                            class="btn-next"
                        >
                            Suivant : Planning
                            <span class="material-symbols-outlined text-[17px]"
                                >arrow_forward</span
                            >
                        </button>
                    </div>
                </div>

                <!-- ══════════════════════════════════
                     ÉTAPE 1 — PLANNING
                ══════════════════════════════════ -->
                <div v-show="step === 1" class="space-y-5">
                    <div class="card p-6 space-y-5">
                        <p class="section-label">Dates du projet</p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <label for="start_date" class="field-label"
                                    >Date de début *</label
                                >
                                <input
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="date"
                                    :class="inputCls(form.errors.start_date)"
                                />
                                <p
                                    v-if="form.errors.start_date"
                                    class="field-error"
                                >
                                    <span
                                        class="material-symbols-outlined text-[13px]"
                                        >error</span
                                    >
                                    {{ form.errors.start_date }}
                                </p>
                            </div>
                            <div>
                                <label
                                    for="end_date_planned"
                                    class="field-label"
                                    >Date de fin prévue *</label
                                >
                                <input
                                    id="end_date_planned"
                                    v-model="form.end_date_planned"
                                    type="date"
                                    :min="form.start_date || undefined"
                                    :class="
                                        inputCls(form.errors.end_date_planned)
                                    "
                                />
                                <p
                                    v-if="form.errors.end_date_planned"
                                    class="field-error"
                                >
                                    <span
                                        class="material-symbols-outlined text-[13px]"
                                        >error</span
                                    >
                                    {{ form.errors.end_date_planned }}
                                </p>
                            </div>
                            <div v-if="isEditing">
                                <label
                                    for="end_date_actual"
                                    class="field-label"
                                >
                                    Fin réelle
                                    <span
                                        class="normal-case font-normal text-gray-400 ml-1"
                                        >(si terminé)</span
                                    >
                                </label>
                                <input
                                    id="end_date_actual"
                                    v-model="form.end_date_actual"
                                    type="date"
                                    :class="inputCls(null)"
                                />
                            </div>
                        </div>

                        <!-- Durée calculée -->
                        <div
                            v-if="durationDays !== null"
                            class="flex items-center gap-3 px-4 py-3 bg-primary-50 dark:bg-primary-600/10 border border-primary-100 dark:border-primary-600/20 rounded-xl"
                        >
                            <span
                                class="material-symbols-outlined text-primary-600 dark:text-primary-400 text-[18px]"
                                style="
                                    font-variation-settings: &quot;FILL&quot; 1;
                                "
                                >schedule</span
                            >
                            <p
                                class="text-[12.5px] font-semibold text-gray-700 dark:text-gray-300"
                            >
                                Durée calculée :
                                <span
                                    class="text-primary-600 dark:text-primary-400 font-bold"
                                >
                                    {{ durationDays }} jour{{
                                        durationDays > 1 ? "s" : ""
                                    }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button
                            type="button"
                            @click="goPrev()"
                            class="btn-prev"
                        >
                            <span class="material-symbols-outlined text-[17px]"
                                >arrow_back</span
                            >
                            Retour
                        </button>
                        <button
                            type="button"
                            @click="goNext()"
                            class="btn-next"
                        >
                            Suivant : Budget
                            <span class="material-symbols-outlined text-[17px]"
                                >arrow_forward</span
                            >
                        </button>
                    </div>
                </div>

                <!-- ══════════════════════════════════
                     ÉTAPE 2 — BUDGET
                ══════════════════════════════════ -->
                <div v-show="step === 2" class="space-y-5">
                    <div class="card p-6 space-y-5">
                        <p class="section-label">Budget du projet</p>

                        <!-- Budget total -->
                        <div>
                            <label for="budget" class="field-label"
                                >Budget total (FCFA) *</label
                            >
                            <div class="relative">
                                <input
                                    id="budget"
                                    v-model="form.budget"
                                    type="number"
                                    min="0"
                                    step="10000"
                                    placeholder="0"
                                    :class="[
                                        inputCls(form.errors.budget),
                                        'pr-16 font-mono text-[14px]',
                                    ]"
                                />
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[11px] font-bold text-gray-400 pointer-events-none"
                                    >FCFA</span
                                >
                            </div>
                            <p v-if="form.errors.budget" class="field-error">
                                <span
                                    class="material-symbols-outlined text-[13px]"
                                    >error</span
                                >
                                {{ form.errors.budget }}
                            </p>
                            <p class="text-[11px] text-gray-400 mt-1.5">
                                💡 La répartition ci-dessous est optionnelle.
                            </p>
                        </div>

                        <!-- Répartition -->
                        <div>
                            <p class="section-label mb-3">
                                Répartition
                                <span
                                    class="normal-case font-normal text-gray-400 ml-1"
                                    >(optionnel)</span
                                >
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    v-for="line in budgetLines"
                                    :key="line.key"
                                >
                                    <label
                                        :for="line.key"
                                        class="field-label flex items-center gap-1.5"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[14px]"
                                            :class="line.colorIcon"
                                            style="
                                                font-variation-settings: &quot;FILL&quot;
                                                    1;
                                            "
                                            >{{ line.icon }}</span
                                        >
                                        {{ line.label }}
                                    </label>
                                    <div class="relative">
                                        <input
                                            :id="line.key"
                                            v-model="form[line.key]"
                                            type="number"
                                            min="0"
                                            step="1000"
                                            placeholder="0"
                                            :class="[
                                                inputCls(null),
                                                'pr-14 font-mono text-[13px]',
                                            ]"
                                        />
                                        <span
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-gray-400 pointer-events-none"
                                            >XOF</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barre répartition -->
                        <div
                            v-if="budgetTotal > 0"
                            class="p-4 bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-gray-800/50 rounded-xl"
                        >
                            <div
                                class="flex items-center justify-between mb-2.5"
                            >
                                <p class="section-label">Aperçu répartition</p>
                                <p
                                    class="text-[11px] font-semibold"
                                    :class="
                                        Math.abs(budgetDiff) < 1
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-amber-500'
                                    "
                                >
                                    Alloué : {{ fmt(totalAllocated) }}
                                    <span
                                        v-if="Math.abs(budgetDiff) >= 1"
                                        class="opacity-70"
                                    >
                                        ({{ budgetDiff > 0 ? "+" : ""
                                        }}{{ fmt(budgetDiff) }})
                                    </span>
                                </p>
                            </div>
                            <div
                                class="h-[6px] w-full bg-gray-200 dark:bg-gray-800 rounded-full overflow-hidden flex"
                            >
                                <div
                                    v-for="line in budgetLines"
                                    :key="line.key"
                                    :class="[
                                        'h-full transition-all duration-300',
                                        line.colorBar,
                                    ]"
                                    :style="{
                                        width: pct(form[line.key]) + '%',
                                    }"
                                ></div>
                            </div>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2.5">
                                <div
                                    v-for="line in budgetLines"
                                    :key="line.key"
                                    class="flex items-center gap-1.5"
                                >
                                    <div
                                        :class="[
                                            'w-[6px] h-[6px] rounded-full',
                                            line.colorBar,
                                        ]"
                                    ></div>
                                    <span class="text-[10.5px] text-gray-400">
                                        {{ line.label }} —
                                        {{ pct(form[line.key]).toFixed(0) }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions finales -->
                    <div class="flex items-center justify-between">
                        <button
                            type="button"
                            @click="goPrev()"
                            class="btn-prev"
                        >
                            <span class="material-symbols-outlined text-[17px]"
                                >arrow_back</span
                            >
                            Retour
                        </button>
                        <div class="flex items-center gap-3">
                            <Link href="/projects" class="btn-prev"
                                >Annuler</Link
                            >
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-[13px] font-bold rounded-xl shadow-sm shadow-primary-600/20 transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span
                                    v-if="form.processing"
                                    class="material-symbols-outlined text-[17px] animate-spin"
                                    >progress_activity</span
                                >
                                <span
                                    v-else
                                    class="material-symbols-outlined text-[17px]"
                                >
                                    {{ isEditing ? "save" : "add_circle" }}
                                </span>
                                {{
                                    isEditing
                                        ? "Enregistrer les modifications"
                                        : "Créer le projet"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.card {
    @apply bg-white dark:bg-[#111318]
           rounded-2xl border border-gray-100 dark:border-gray-800/70
           shadow-[0_1px_3px_rgba(0,0,0,0.04),0_4px_12px_rgba(0,0,0,0.03)];
}
.section-label {
    @apply text-[10.5px] font-bold uppercase tracking-[0.13em] text-gray-400 dark:text-gray-600;
}
.field-label {
    @apply block text-[10.5px] font-bold uppercase tracking-[0.1em]
           text-gray-500 dark:text-gray-500 mb-1.5;
}
.field-error {
    @apply mt-1.5 text-[11px] text-red-500 flex items-center gap-1;
}
.btn-next {
    @apply flex items-center gap-1.5 px-4 py-2.5
           bg-primary-50 dark:bg-primary-600/10
           text-primary-600 dark:text-primary-400
           text-[12.5px] font-semibold rounded-xl
           hover:bg-primary-100 dark:hover:bg-primary-600/20 transition-all;
}
.btn-prev {
    @apply flex items-center gap-1.5 px-4 py-2.5
           bg-gray-100 dark:bg-gray-800
           text-gray-500 dark:text-gray-400
           text-[12.5px] font-semibold rounded-xl
           hover:bg-gray-200 dark:hover:bg-gray-700 transition-all;
}
</style>
