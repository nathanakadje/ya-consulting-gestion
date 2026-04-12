<script setup>
import { computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import StatusBadge from "@/Components/StatusBadge.vue";

// ── Props ────────────────────────────────────────────────────
const props = defineProps({
    project: Object,
    financials: Object,
    expenses: Array,
    charts: Object,
});

const page = usePage();
const canManage = computed(() => page.props.auth.user?.can?.manage_projects);

// ── Changement de statut rapide ───────────────────────────────
function changeStatus(newStatus) {
    router.patch(
        `/projects/${props.project.id}/status`,
        { status: newStatus },
        {
            preserveScroll: true,
        },
    );
}

// ── Formatage ─────────────────────────────────────────────────
function formatCurrency(amount) {
    if (!amount && amount !== 0) return "—";
    return new Intl.NumberFormat("fr-FR").format(amount) + " FCFA";
}
function formatCurrencyShort(amount) {
    if (amount >= 1_000_000) return (amount / 1_000_000).toFixed(1) + " M";
    if (amount >= 1_000) return (amount / 1_000).toFixed(0) + " K";
    return amount + " F";
}

const addExpenseModal = ref(false);
function editExpense(expense) {
    // TODO: Semaine 3
}
</script>

<template>
    <Head :title="project.name" />

    <AppLayout
        :title="project.name"
        :breadcrumb="`Projets / ${project.reference ?? project.name}`"
    >
        <div class="flex flex-wrap items-start justify-between gap-4 mb-7">
            <div class="flex items-start gap-4">
                <Link
                    href="/projects"
                    class="p-2 mt-1 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all flex-shrink-0"
                >
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span
                            class="font-mono text-xs text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-lg"
                        >
                            {{ project.reference }}
                        </span>
                        <StatusBadge :status="project.status" />
                    </div>
                    <h2
                        class="font-headline text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight"
                    >
                        {{ project.name }}
                    </h2>
                    <p
                        class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-2xl"
                    >
                        {{ project.description }}
                    </p>

                    <div class="flex flex-wrap gap-5 mt-4">
                        <div class="flex items-center gap-2">
                            <span
                                class="material-symbols-outlined text-gray-400 text-[16px]"
                                >business</span
                            >
                            <div>
                                <p
                                    class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold"
                                >
                                    Client
                                </p>
                                <p
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300"
                                >
                                    {{ project.client?.name ?? "—" }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="material-symbols-outlined text-gray-400 text-[16px]"
                                >person</span
                            >
                            <div>
                                <p
                                    class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold"
                                >
                                    Chef de projet
                                </p>
                                <p
                                    class="text-sm font-semibold text-gray-700 dark:text-gray-300"
                                >
                                    {{ project.lead?.name ?? "Non assigné" }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <select
                    v-if="canManage"
                    :value="project.status"
                    @change="changeStatus($event.target.value)"
                    class="text-sm font-semibold border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-primary-600/20 cursor-pointer"
                >
                    <option value="en_cours">🟢 En cours</option>
                    <option value="en_pause">🟡 En pause</option>
                    <option value="termine">✅ Terminé</option>
                </select>

                <Link
                    v-if="canManage"
                    :href="`/projects/${project.id}/edit`"
                    class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                >
                    <span class="material-symbols-outlined text-[18px]"
                        >edit</span
                    >
                    Modifier
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="space-y-5">
                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm"
                >
                    <h4
                        class="font-headline font-bold text-gray-900 dark:text-white text-sm mb-4"
                    >
                        Budget
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-400"
                                    >Dépenses réelles</span
                                >
                                <span
                                    class="font-bold text-primary-600 dark:text-primary-400 font-mono"
                                    >{{
                                        formatCurrency(
                                            financials.total_expenses,
                                        )
                                    }}</span
                                >
                            </div>
                            <div
                                class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :class="
                                        financials.budget_used_percent > 90
                                            ? 'bg-red-500'
                                            : financials.budget_used_percent >
                                                70
                                              ? 'bg-amber-500'
                                              : 'bg-primary-600'
                                    "
                                    :style="{
                                        width:
                                            Math.min(
                                                financials.budget_used_percent,
                                                100,
                                            ) + '%',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">
                        {{ financials.budget_used_percent }}% du budget consommé
                    </p>
                </div>

                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm"
                >
                    <h4
                        class="font-headline font-bold text-gray-900 dark:text-white text-sm mb-4"
                    >
                        Répartition budget
                    </h4>
                    <div class="space-y-3">
                        <div
                            v-for="(val, label) in {
                                'Main d\'œuvre': project.budget_main_oeuvre,
                                Matériel: project.budget_materiel,
                                Transport: project.budget_transport,
                            }"
                            :key="label"
                        >
                            <div class="flex justify-between text-xs mb-1">
                                <span
                                    class="text-gray-500 dark:text-gray-400"
                                    >{{ label }}</span
                                >
                                <span
                                    class="font-mono font-semibold text-gray-700 dark:text-gray-300"
                                    >{{ formatCurrencyShort(val) }}</span
                                >
                            </div>
                            <div
                                class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"
                            >
                                <div
                                    class="h-full bg-primary-500"
                                    :style="{
                                        width:
                                            (project.budget > 0
                                                ? (val / project.budget) * 100
                                                : 0) + '%',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <div
                    class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden"
                >
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800"
                    >
                        <h4
                            class="font-headline font-bold text-gray-900 dark:text-white"
                        >
                            Dépenses du projet
                        </h4>
                        <button
                            v-if="canManage"
                            @click="addExpenseModal = true"
                            class="px-4 py-2 bg-primary-50 text-primary-600 rounded-xl text-sm font-semibold"
                        >
                            Ajouter
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <tbody
                                class="divide-y divide-gray-50 dark:divide-gray-800"
                            >
                                <tr
                                    v-for="expense in expenses"
                                    :key="expense.id"
                                    class="hover:bg-gray-50/50"
                                >
                                    <td
                                        class="px-5 py-3.5 text-xs text-gray-400 font-mono"
                                    >
                                        {{ expense.expense_date }}
                                    </td>
                                    <td class="px-5 py-3.5 font-semibold">
                                        {{ expense.description }}
                                    </td>
                                    <td
                                        class="px-5 py-3.5 text-right font-bold"
                                    >
                                        {{ formatCurrency(expense.amount) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
