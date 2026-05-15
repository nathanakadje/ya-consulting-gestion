<template>
    <!-- resources/js/Pages/Expenses/Index.vue -->
    <Head title="Dépenses" />

    <AppLayout title="Toutes les dépenses" breadcrumb="Journal des dépenses">
        <!-- ── Stats rapides ──────────────────────────────────── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            <StatCard
                icon="receipt_long"
                label="Ce mois"
                :value="formatCurrency(stats.total_this_month)"
                color="primary"
            />
            <StatCard
                icon="account_balance"
                label="Total global"
                :value="formatCurrencyShort(stats.total_all)"
                color="emerald"
            />
            <StatCard
                icon="today"
                label="Transactions / mois"
                :value="stats.count_this_month"
                color="violet"
            />
            <StatCard
                icon="pending_actions"
                label="En attente"
                :value="stats.count_pending"
                :trend="stats.count_pending > 0 ? 'À valider' : 'Tout validé'"
                :trend-up="stats.count_pending === 0"
                color="amber"
            />
        </div>

        <!-- ── Filtres ────────────────────────────────────────── -->
        <div
            class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-4 mb-5"
        >
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end"
            >
                <!-- Recherche -->
                <div class="lg:col-span-2">
                    <label
                        class="block text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1.5"
                        >Rechercher</label
                    >
                    <div class="relative">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]"
                            >search</span
                        >
                        <input
                            v-model="localFilters.search"
                            @input="debounce(applyFilters, 350)()"
                            type="text"
                            placeholder="Description..."
                            class="w-full pl-9 pr-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-primary-600/20 outline-none transition-all"
                        />
                    </div>
                </div>

                <!-- Projet -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1.5"
                        >Projet</label
                    >
                    <select
                        v-model="localFilters.project_id"
                        @change="applyFilters"
                        class="filterSelect"
                    >
                        <option value="">Tous les projets</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </select>
                </div>

                <!-- Catégorie -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1.5"
                        >Catégorie</label
                    >
                    <select
                        v-model="localFilters.category_id"
                        @change="applyFilters"
                        class="filterSelect"
                    >
                        <option value="">Toutes</option>
                        <option
                            v-for="c in categories"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }}
                        </option>
                    </select>
                </div>

                <!-- Mois -->
                <div>
                    <label
                        class="block text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1.5"
                        >Mois</label
                    >
                    <input
                        v-model="localFilters.month"
                        @change="applyFilters"
                        type="month"
                        class="filterSelect"
                    />
                </div>
            </div>

            <!-- Tags filtres actifs -->
            <div
                v-if="hasActiveFilters"
                class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-800"
            >
                <span class="text-xs text-gray-400 font-medium self-center"
                    >Filtres actifs :</span
                >
                <button
                    v-for="(value, key) in activeFilterTags"
                    :key="key"
                    @click="removeFilter(key)"
                    class="flex items-center gap-1.5 px-2.5 py-1 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-lg text-xs font-semibold hover:bg-primary-100 transition-colors"
                >
                    {{ value }}
                    <span class="material-symbols-outlined text-[14px]"
                        >close</span
                    >
                </button>
                <button
                    @click="clearFilters"
                    class="text-xs text-gray-400 hover:text-red-500 transition-colors font-medium"
                >
                    Tout effacer
                </button>
            </div>
        </div>

        <!-- ── Table ──────────────────────────────────────────── -->
        <div
            class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden"
        >
            <!-- Bouton global Ajouter (en haut du tableau) -->
            <div
                class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800"
            >
                <p
                    class="text-sm font-semibold text-gray-500 dark:text-gray-400"
                >
                    <span class="text-gray-900 dark:text-white font-bold">{{
                        expenses.total
                    }}</span>
                    dépense{{ expenses.total > 1 ? "s" : "" }}
                </p>
                <button
                    v-if="canManage"
                    @click="openModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-violet-700 hover:bg-violet-700 text-white rounded-xl text-sm font-semibold transition-all active:scale-95 shadow-sm shadow-primary-600/20"
                >
                    <span class="material-symbols-outlined text-[18px]"
                        >add</span
                    >
                    Nouvelle dépense
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr
                            class="border-b border-gray-50 dark:border-gray-800 text-[11px] font-bold uppercase tracking-widest text-gray-400"
                        >
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Date
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Description
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Projet
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Catégorie
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden text-right"
                            >
                                Montant
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Statut
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Ajouté par
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden w-24"
                            ></th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-50 dark:divide-gray-800 text-sm"
                    >
                        <tr
                            v-for="expense in expenses.data"
                            :key="expense.id"
                            class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group"
                        >
                            <td
                                class="px-5 py-3.5 text-gray-400 text-xs font-mono whitespace-nowrap"
                            >
                                {{ expense.expense_date }}
                            </td>
                            <td class="px-5 py-3.5">
                                <p
                                    class="font-semibold text-gray-800 dark:text-gray-200 max-w-xs truncate"
                                >
                                    {{ expense.description }}
                                </p>
                                <p
                                    v-if="expense.notes"
                                    class="text-[11px] text-gray-400 mt-0.5 truncate max-w-xs"
                                >
                                    {{ expense.notes }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5">
                                <Link
                                    :href="`/gestion/projects/${expense.project?.id}`"
                                    class="text-xs font-semibold text-gray-400 dark:text-gray-400 hover:underline"
                                >
                                    {{ expense.project?.name }}
                                </Link>
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    v-if="expense.category"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold"
                                    :style="{
                                        backgroundColor:
                                            expense.category.color + '18',
                                        color: expense.category.color,
                                    }"
                                >
                                    <span
                                        class="material-symbols-outlined text-[13px]"
                                        >{{
                                            expense.category.icon ?? "receipt"
                                        }}</span
                                    >
                                    {{ expense.category.name }}
                                </span>
                            </td>
                            <td
                                class="px-5 py-3.5 text-right font-mono font-bold text-gray-900 dark:text-white whitespace-nowrap"
                            >
                                {{ formatCurrency(expense.amount) }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    :class="statusClass(expense.status)"
                                    class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase"
                                >
                                    {{ statusLabel(expense.status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-400">
                                {{ expense.created_by?.name }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div
                                    v-if="canManage"
                                    class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <a
                                        v-if="expense.receipt_path"
                                        :href="`/gestion/expenses/${expense.id}/receipt`"
                                        target="_blank"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all"
                                        title="Justificatif"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[16px]"
                                            >attach_file</span
                                        >
                                    </a>
                                    <button
                                        @click="openModal(expense)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all"
                                        title="Modifier"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[16px]"
                                            >edit</span
                                        >
                                    </button>
                                    <button
                                        @click="confirmDelete(expense)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                                        title="Supprimer"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[16px]"
                                            >delete</span
                                        >
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!expenses.data.length">
                            <td colspan="8" class="px-5 py-20 text-center">
                                <span
                                    class="material-symbols-outlined text-gray-200 dark:text-gray-700 text-[56px]"
                                    >receipt_long</span
                                >
                                <p class="text-gray-400 mt-3 font-medium">
                                    Aucune dépense trouvée
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between"
            >
                <p class="text-xs text-gray-400">
                    {{ expenses.from ?? 0 }}–{{ expenses.to ?? 0 }} sur
                    {{ expenses.total }}
                </p>
                <div class="flex items-center gap-1">
                    <Link
                        v-for="link in expenses.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-medium transition-all',
                            link.active
                                ? 'bg-primary-600 text-white'
                                : link.url
                                  ? 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                                  : 'opacity-30 cursor-not-allowed pointer-events-none',
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>

        <!-- ══ Modal ajout/édition dépense ═══════════════════════ -->
        <ExpenseModal
            v-model="modal.show"
            :project-id="modal.projectId ?? projects[0]?.id ?? ''"
            :categories="categories"
            :expense="modal.expense"
            :show-project-selector="true"
            :projects="projects"
        />

        <!-- ══ Modal suppression ══════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="deleteModal.show"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                        @click="deleteModal.show = false"
                    ></div>
                    <div
                        class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 w-full max-w-sm border border-gray-100 dark:border-gray-800"
                    >
                        <h3
                            class="font-headline font-bold text-gray-900 dark:text-white mb-2"
                        >
                            Supprimer la dépense ?
                        </h3>
                        <p class="text-sm text-gray-500 mb-5">
                            «
                            <span
                                class="font-semibold text-gray-700 dark:text-gray-300"
                                >{{ deleteModal.description }}</span
                            >
                            » sera définitivement supprimée.
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="deleteModal.show = false"
                                class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 transition-all"
                            >
                                Annuler
                            </button>
                            <button
                                @click="executeDelete"
                                class="px-4 py-2 text-sm font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-all active:scale-95"
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

<script setup>
import { reactive, computed, ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import StatCard from "@/Components/StatCard.vue";
import ExpenseModal from "@/Components/ExpenseModal.vue";

const props = defineProps({
    expenses: Object,
    stats: Object,
    projects: Array,
    categories: Array,
    filters: Object,
});

const page = usePage();
const canManage = computed(() => page.props.auth.user?.can?.manage_projects);

// ── Filtres locaux ────────────────────────────────────────────
const localFilters = reactive({
    search: props.filters?.search ?? "",
    project_id: props.filters?.project_id ?? "",
    category_id: props.filters?.category_id ?? "",
    month: props.filters?.month ?? "",
});

function applyFilters() {
    router.get(
        "/gestion/expenses",
        {
            search: localFilters.search || undefined,
            project_id: localFilters.project_id || undefined,
            category_id: localFilters.category_id || undefined,
            month: localFilters.month || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

const hasActiveFilters = computed(() =>
    Object.values(localFilters).some((v) => v !== ""),
);

const activeFilterTags = computed(() => {
    const tags = {};
    if (localFilters.project_id)
        tags.project_id =
            props.projects?.find((p) => p.id == localFilters.project_id)
                ?.name ?? "Projet";
    if (localFilters.category_id)
        tags.category_id =
            props.categories?.find((c) => c.id == localFilters.category_id)
                ?.name ?? "Catégorie";
    if (localFilters.month) tags.month = localFilters.month;
    if (localFilters.search) tags.search = `"${localFilters.search}"`;
    return tags;
});

function removeFilter(key) {
    localFilters[key] = "";
    applyFilters();
}

function clearFilters() {
    Object.keys(localFilters).forEach((k) => (localFilters[k] = ""));
    applyFilters();
}

// Debounce simple
function debounce(fn, delay) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), delay);
    };
}

// ── Modal ─────────────────────────────────────────────────────
const modal = reactive({ show: false, expense: null, projectId: null });

function openModal(expense = null) {
    modal.expense = expense;
    modal.projectId = expense?.project?.id ?? null;
    modal.show = true;
}

// ── Suppression ───────────────────────────────────────────────
const deleteModal = reactive({ show: false, id: null, description: "" });

function confirmDelete(expense) {
    deleteModal.id = expense.id;
    deleteModal.description = expense.description;
    deleteModal.show = true;
}

function executeDelete() {
    router.delete(`/gestion/expenses/${deleteModal.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deleteModal.show = false;
        },
    });
}

// ── Statut dépense ────────────────────────────────────────────
function statusLabel(s) {
    return (
        { validated: "Validée", pending: "En attente", rejected: "Rejetée" }[
            s
        ] ?? s
    );
}
function statusClass(s) {
    return (
        {
            validated:
                "bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400",
            pending:
                "bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400",
            rejected: "bg-red-50 dark:bg-red-900/30 text-red-500",
        }[s] ?? "bg-gray-100 text-gray-500"
    );
}

// ── Formatage ─────────────────────────────────────────────────
function formatCurrency(v) {
    return new Intl.NumberFormat("fr-FR").format(v ?? 0) + " FCFA";
}
function formatCurrencyShort(v) {
    if (v >= 1_000_000) return (v / 1_000_000).toFixed(1) + " M FCFA";
    if (v >= 1_000) return (v / 1_000).toFixed(0) + " K FCFA";
    return v + " FCFA";
}
</script>

<style scoped>
.filterSelect {
    @apply w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-600/20 transition-all appearance-none cursor-pointer;
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
