<script setup>
import { computed, reactive, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import ExpenseModal from "@/Components/ExpenseModal.vue";

const props = defineProps({
    project: { type: Object, required: true },
    financials: { type: Object, required: true },
    expenses: { type: Array, default: () => [] },
    charts: {
        type: Object,
        default: () => ({ by_category: [], by_month: [] }),
    },
    categories: { type: Array, default: () => [] },
});

const page = usePage();
const canManage = computed(() => page.props.auth.user?.can?.manage_projects);

function changeStatus(val) {
    router.patch(
        `/projects/${props.project.id}/status`,
        { status: val },
        { preserveScroll: true },
    );
}

const expenseModal = reactive({ show: false, expense: null });
function openAdd() {
    expenseModal.expense = null;
    expenseModal.show = true;
}
function openEdit(e) {
    expenseModal.expense = e;
    expenseModal.show = true;
}

const delModal = reactive({ show: false, id: null, desc: "" });
function confirmDel(e) {
    delModal.id = e.id;
    delModal.desc = e.description;
    delModal.show = true;
}
function execDel() {
    router.delete(`/expenses/${delModal.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            delModal.show = false;
        },
    });
}

function barH(amount, months) {
    const max = Math.max(...months.map((m) => m.amount), 1);
    return Math.max((amount / max) * 100, 4);
}
function catPct(amount) {
    const t = props.financials.total_expenses;
    return t > 0 ? Math.min((amount / t) * 100, 100) : 0;
}
function fmt(v) {
    if (!v && v !== 0) return "—";
    return new Intl.NumberFormat("fr-FR").format(Math.round(v)) + " FCFA";
}
function fmtS(v) {
    if (!v && v !== 0) return "—";
    if (v >= 1_000_000) return (v / 1_000_000).toFixed(1) + " M";
    if (v >= 1_000) return Math.round(v / 1_000) + " K";
    return Math.round(v) + "";
}
</script>

<template>
    <Head :title="project.name" />
    <AppLayout
        :title="project.name"
        :breadcrumb="`Projets / ${project.reference ?? project.name}`"
    >
        <!-- Hero -->
        <div class="flex flex-wrap items-start justify-between gap-4 mb-7">
            <div class="flex items-start gap-3 min-w-0">
                <Link
                    href="/projects"
                    class="w-9 h-9 mt-0.5 flex items-center justify-center rounded-xl flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all"
                >
                    <span class="material-symbols-outlined text-[20px]"
                        >arrow_back</span
                    >
                </Link>
                <div class="min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-1.5">
                        <span
                            class="font-mono text-[11px] text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-lg"
                            >{{ project.reference }}</span
                        >
                        <StatusBadge :status="project.status" />
                    </div>
                    <h2
                        class="font-headline text-[22px] font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight"
                    >
                        {{ project.name }}
                    </h2>
                    <p
                        v-if="project.description"
                        class="text-[12.5px] text-gray-500 dark:text-gray-400 mt-1.5 max-w-2xl leading-relaxed"
                    >
                        {{ project.description }}
                    </p>
                    <div class="flex flex-wrap gap-5 mt-4">
                        <div
                            v-for="meta in [
                                {
                                    icon: 'business',
                                    label: 'Client',
                                    val: project.client?.name,
                                },
                                {
                                    icon: 'person',
                                    label: 'Chef de projet',
                                    val: project.lead?.name ?? 'Non assigné',
                                },
                                {
                                    icon: 'calendar_month',
                                    label: 'Période',
                                    val:
                                        project.start_date +
                                        ' → ' +
                                        project.end_date_planned,
                                },
                            ]"
                            :key="meta.label"
                            class="flex items-center gap-2"
                        >
                            <span
                                class="material-symbols-outlined text-gray-400 text-[15px]"
                                >{{ meta.icon }}</span
                            >
                            <div>
                                <p
                                    class="text-[9.5px] text-gray-400 uppercase tracking-[0.12em] font-bold leading-none"
                                >
                                    {{ meta.label }}
                                </p>
                                <p
                                    class="text-[12.5px] font-semibold text-gray-700 dark:text-gray-300 leading-tight mt-[2px]"
                                >
                                    {{ meta.val ?? "—" }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <div v-if="canManage" class="relative">
                    <select
                        :value="project.status"
                        @change="changeStatus($event.target.value)"
                        class="text-[12.5px] font-semibold border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 pr-8 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-primary-600/20 cursor-pointer appearance-none"
                    >
                        <option value="en_cours">🟢 En cours</option>
                        <option value="en_pause">🟡 En pause</option>
                        <option value="termine">✅ Terminé</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-[16px] pointer-events-none"
                        >expand_more</span
                    >
                </div>
                <Link
                    v-if="canManage"
                    :href="`/projects/${project.id}/edit`"
                    class="flex items-center gap-1.5 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-[12.5px] font-semibold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                >
                    <span class="material-symbols-outlined text-[17px]"
                        >edit</span
                    >
                    Modifier
                </Link>
            </div>
        </div>

        <!-- Grille -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
            <!-- Métriques -->
            <div class="xl:col-span-4 space-y-4">
                <!-- Budget -->
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h4
                            class="font-headline font-bold text-gray-900 dark:text-white text-[13px]"
                        >
                            Budget
                        </h4>
                        <span
                            class="material-symbols-outlined text-primary-600 text-[18px]"
                            style="font-variation-settings: &quot;FILL&quot; 1"
                            >account_balance_wallet</span
                        >
                    </div>
                    <div class="space-y-3.5">
                        <div>
                            <div
                                class="flex justify-between text-[11.5px] mb-1.5"
                            >
                                <span class="text-gray-400">Budget alloué</span>
                                <span
                                    class="font-bold font-mono text-gray-900 dark:text-white"
                                    >{{ fmt(project.budget) }}</span
                                >
                            </div>
                            <div
                                class="h-[5px] bg-gray-100 dark:bg-gray-800 rounded-full"
                            >
                                <div
                                    class="h-full bg-primary-200 dark:bg-primary-900/30 rounded-full w-full"
                                ></div>
                            </div>
                        </div>
                        <div>
                            <div
                                class="flex justify-between text-[11.5px] mb-1.5"
                            >
                                <span class="text-gray-400"
                                    >Dépenses réelles</span
                                >
                                <span
                                    class="font-bold font-mono text-primary-600 dark:text-primary-400"
                                    >{{ fmt(financials.total_expenses) }}</span
                                >
                            </div>
                            <div
                                class="h-[5px] bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"
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
                    <p class="text-[11px] text-gray-400 mt-2.5">
                        {{ financials.budget_used_percent }}% consommé
                    </p>
                </div>

                <!-- Rentabilité -->
                <div class="card p-5">
                    <div class="flex items-center justify-between mb-1.5">
                        <h4
                            class="font-headline font-bold text-gray-900 dark:text-white text-[13px]"
                        >
                            Rentabilité
                        </h4>
                        <span
                            class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                            :class="
                                financials.margin_percent >= 0
                                    ? 'bg-emerald-50 dark:bg-emerald-600/10 text-emerald-600 dark:text-emerald-400'
                                    : 'bg-red-50 text-red-500'
                            "
                        >
                            {{ financials.margin_percent >= 0 ? "+" : ""
                            }}{{ financials.margin_percent }}%
                        </span>
                    </div>
                    <p
                        class="font-headline text-[26px] font-extrabold tracking-tight leading-none mt-1"
                        :class="
                            financials.gross_gain >= 0
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-red-500'
                        "
                    >
                        {{ financials.gross_gain >= 0 ? "+" : ""
                        }}{{ fmt(financials.gross_gain) }}
                    </p>
                    <p class="text-[11px] text-gray-400 mt-1">
                        {{
                            financials.gross_gain >= 0
                                ? "Gain brut estimé"
                                : "Perte actuelle"
                        }}
                    </p>
                </div>

                <!-- Burn par mois -->
                <div v-if="charts.by_month?.length" class="card p-5">
                    <h4
                        class="font-headline font-bold text-gray-900 dark:text-white text-[13px] mb-4"
                    >
                        Dépenses / mois
                    </h4>
                    <div class="flex items-end gap-1.5 h-24">
                        <div
                            v-for="m in charts.by_month"
                            :key="m.month"
                            class="flex-1 flex flex-col items-center gap-1 group cursor-default"
                        >
                            <div
                                class="w-full rounded-t-lg transition-all duration-500 relative bg-primary-100 dark:bg-primary-900/30 group-hover:bg-primary-600 dark:group-hover:bg-primary-500"
                                :style="{
                                    height:
                                        barH(m.amount, charts.by_month) + '%',
                                    minHeight: '4px',
                                }"
                            >
                                <div
                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 bg-gray-900 dark:bg-gray-700 text-white text-[10px] font-bold px-2 py-1 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10"
                                >
                                    {{ fmtS(m.amount) }} FCFA
                                    <span
                                        class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"
                                    ></span>
                                </div>
                            </div>
                            <span
                                class="text-[9px] font-bold text-gray-400 uppercase"
                                >{{ m.month }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Par catégorie -->
                <div v-if="charts.by_category?.length" class="card p-5">
                    <h4
                        class="font-headline font-bold text-gray-900 dark:text-white text-[13px] mb-4"
                    >
                        Par catégorie
                    </h4>
                    <div class="space-y-3">
                        <div
                            v-for="cat in charts.by_category"
                            :key="cat.name"
                            class="space-y-1"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-[6px] h-[6px] rounded-full"
                                        :style="{ backgroundColor: cat.color }"
                                    ></div>
                                    <span
                                        class="text-[11.5px] text-gray-600 dark:text-gray-400"
                                        >{{ cat.name }}</span
                                    >
                                </div>
                                <span
                                    class="text-[11px] font-bold font-mono text-gray-900 dark:text-white"
                                    >{{ fmtS(cat.amount) }}</span
                                >
                            </div>
                            <div
                                class="h-[3px] bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"
                            >
                                <div
                                    class="h-full rounded-full transition-all duration-700"
                                    :style="{
                                        width: catPct(cat.amount) + '%',
                                        backgroundColor: cat.color,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dépenses -->
            <div class="xl:col-span-8">
                <div class="card overflow-hidden flex flex-col">
                    <div
                        class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800/60"
                    >
                        <div>
                            <h4
                                class="font-headline font-bold text-gray-900 dark:text-white text-[13.5px]"
                            >
                                Dépenses du projet
                            </h4>
                            <p class="text-[10.5px] text-gray-400 mt-[1px]">
                                {{ expenses.length }} transaction{{
                                    expenses.length > 1 ? "s" : ""
                                }}
                            </p>
                        </div>
                        <button
                            v-if="canManage"
                            @click="openAdd()"
                            class="flex items-center gap-1.5 px-3.5 py-2 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 text-[12px] font-semibold rounded-xl hover:bg-primary-100 dark:hover:bg-primary-600/20 transition-all active:scale-95"
                        >
                            <span class="material-symbols-outlined text-[17px]"
                                >add</span
                            >
                            Ajouter une dépense
                        </button>
                    </div>

                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="border-b border-gray-50 dark:border-gray-800/50 text-[10.5px] font-bold uppercase tracking-[0.08em] text-gray-400"
                                >
                                    <th class="px-5 py-3">Date</th>
                                    <th class="px-5 py-3">Description</th>
                                    <th class="px-5 py-3">Catégorie</th>
                                    <th class="px-5 py-3 text-right">
                                        Montant
                                    </th>
                                    <th class="px-5 py-3">Par</th>
                                    <th class="px-5 py-3 w-20"></th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-50 dark:divide-gray-800/40"
                            >
                                <tr
                                    v-for="expense in expenses"
                                    :key="expense.id"
                                    class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors group"
                                >
                                    <td
                                        class="px-5 py-3.5 text-[11px] text-gray-400 font-mono whitespace-nowrap"
                                    >
                                        {{ expense.expense_date }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p
                                            class="text-[12.5px] font-semibold text-gray-800 dark:text-gray-200 leading-tight"
                                        >
                                            {{ expense.description }}
                                        </p>
                                        <p
                                            v-if="expense.notes"
                                            class="text-[11px] text-gray-400 mt-[2px] truncate max-w-[200px]"
                                        >
                                            {{ expense.notes }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-[3px] rounded-lg text-[10.5px] font-bold"
                                            :style="{
                                                backgroundColor:
                                                    (expense.category?.color ??
                                                        '#6b7280') + '18',
                                                color:
                                                    expense.category?.color ??
                                                    '#6b7280',
                                            }"
                                        >
                                            <span
                                                class="material-symbols-outlined text-[12px]"
                                                style="
                                                    font-variation-settings: &quot;FILL&quot;
                                                        1;
                                                "
                                                >{{
                                                    expense.category?.icon ??
                                                    "receipt"
                                                }}</span
                                            >
                                            {{
                                                expense.category?.name ??
                                                "Divers"
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-5 py-3.5 text-right font-bold font-mono text-[12.5px] text-gray-900 dark:text-white whitespace-nowrap"
                                    >
                                        {{ fmt(expense.amount) }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-[24px] h-[24px] rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 text-[10px] flex items-center justify-center font-bold flex-shrink-0"
                                            >
                                                {{
                                                    expense.created_by?.name
                                                        ?.slice(0, 2)
                                                        .toUpperCase()
                                                }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div
                                            v-if="canManage"
                                            class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                        >
                                            <a
                                                v-if="expense.receipt_path"
                                                :href="`/expenses/${expense.id}/receipt`"
                                                target="_blank"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all"
                                                title="Justificatif"
                                            >
                                                <span
                                                    class="material-symbols-outlined text-[15px]"
                                                    >attach_file</span
                                                >
                                            </a>
                                            <button
                                                @click="openEdit(expense)"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all"
                                            >
                                                <span
                                                    class="material-symbols-outlined text-[15px]"
                                                    >edit</span
                                                >
                                            </button>
                                            <button
                                                @click="confirmDel(expense)"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                                            >
                                                <span
                                                    class="material-symbols-outlined text-[15px]"
                                                    >delete</span
                                                >
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!expenses.length">
                                    <td
                                        colspan="6"
                                        class="px-5 py-16 text-center"
                                    >
                                        <span
                                            class="material-symbols-outlined text-gray-200 dark:text-gray-800 text-[44px]"
                                            >receipt_long</span
                                        >
                                        <p
                                            class="text-[12.5px] text-gray-400 mt-2 font-medium"
                                        >
                                            Aucune dépense enregistrée
                                        </p>
                                        <button
                                            v-if="canManage"
                                            @click="openAdd()"
                                            class="inline-flex items-center gap-1 mt-3 text-primary-600 dark:text-primary-400 text-[12px] font-semibold hover:underline"
                                        >
                                            <span
                                                class="material-symbols-outlined text-[14px]"
                                                >add</span
                                            >
                                            Ajouter la première dépense
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="px-5 py-3.5 bg-gray-50/60 dark:bg-white/[0.015] border-t border-gray-100 dark:border-gray-800/60 flex items-center justify-between"
                    >
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-[0.1em] text-gray-400"
                            >Total dépenses</span
                        >
                        <span
                            class="font-headline font-extrabold text-[15px] text-primary-600 dark:text-primary-400"
                            >{{ fmt(financials.total_expenses) }}</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal dépense -->
        <ExpenseModal
            v-model="expenseModal.show"
            :project-id="project.id"
            :project-name="project.name"
            :categories="categories"
            :expense="expenseModal.expense"
        />

        <!-- Modal suppression dépense -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="delModal.show"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                        @click="delModal.show = false"
                    ></div>
                    <div
                        class="relative bg-white dark:bg-[#111318] rounded-2xl shadow-2xl p-6 w-full max-w-sm border border-gray-100 dark:border-gray-800/70"
                    >
                        <div class="flex items-start gap-4 mb-5">
                            <div
                                class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0"
                            >
                                <span
                                    class="material-symbols-outlined text-red-500 text-[20px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >delete_forever</span
                                >
                            </div>
                            <div>
                                <h3
                                    class="font-headline font-bold text-gray-900 dark:text-white"
                                >
                                    Supprimer la dépense ?
                                </h3>
                                <p
                                    class="text-[12.5px] text-gray-500 dark:text-gray-400 mt-1"
                                >
                                    «
                                    <span
                                        class="font-semibold text-gray-700 dark:text-gray-300"
                                        >{{ delModal.desc }}</span
                                    >
                                    » sera définitivement supprimée.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="delModal.show = false"
                                class="px-4 py-2 text-[12.5px] font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 transition-all"
                            >
                                Annuler
                            </button>
                            <button
                                @click="execDel()"
                                class="px-4 py-2 text-[12.5px] font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-all active:scale-95"
                            >
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.card {
    @apply bg-white dark:bg-[#111318] rounded-2xl border border-gray-100 dark:border-gray-800/70 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_4px_12px_rgba(0,0,0,0.03)];
}
.modal-enter-active,
.modal-leave-active {
    transition: all 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
