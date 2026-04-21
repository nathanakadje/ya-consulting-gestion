<template>
    <!-- resources/js/Pages/Projects/Index.vue -->
    <Head title="Projets" />

    <AppLayout title="Portefeuille Projets" breadcrumb="Gestion des projets">
        <!-- ── Stats ─────────────────────────────────────────── -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            <StatCard
                icon="account_tree"
                label="Total projets"
                :value="stats.total"
                color="primary"
            />
            <StatCard
                icon="play_circle"
                label="En cours"
                :value="stats.en_cours"
                color="emerald"
            />
            <StatCard
                icon="check_circle"
                label="Terminés"
                :value="stats.termine"
                color="violet"
            />
            <StatCard
                icon="account_balance_wallet"
                label="Budget total"
                :value="formatCurrency(stats.total_budget)"
                color="amber"
            />
        </div>

        <!-- ── Barre d'outils ────────────────────────────────── -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <!-- Filtres statut -->
            <div
                class="flex items-center gap-1 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl p-1 shadow-sm"
            >
                <button
                    v-for="f in statusFilters"
                    :key="f.value"
                    @click="setFilter('status', f.value)"
                    :class="[
                        'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all',
                        filters.status === f.value
                            ? 'bg-emerald-600 dark:bg-emerald-900/10 text-white shadow-sm'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200',
                    ]"
                >
                    {{ f.label }}
                </button>
            </div>

            <div class="flex items-center gap-2 flex-1 max-w-xl">
                <!-- Recherche -->
                <div class="relative flex-1">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]"
                        >search</span
                    >
                    <input
                        v-model="searchQuery"
                        @input="debounceSearch"
                        type="text"
                        placeholder="Rechercher un projet ou client..."
                        class="w-full pl-9 pr-4 py-2 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/30 outline-none transition-all placeholder:text-gray-400"
                    />
                </div>

                <!-- Bouton Nouveau projet -->
                <Link
                    v-if="$page.props.auth.user?.can?.manage_projects"
                    href="/projects/create"
                    class="flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl font-semibold text-sm shadow-sm shadow-primary-600/25 transition-all active:scale-95 whitespace-nowrap"
                >
                    <span class="material-symbols-outlined text-[18px]"
                        >add</span
                    >
                    Nouveau projet
                </Link>
            </div>
        </div>

        <!-- ── Tableau ────────────────────────────────────────── -->
        <div
            class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr
                            class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-100 dark:border-gray-800"
                        >
                            <th
                                class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-widest bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Projet
                            </th>
                            <th
                                class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-widest bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Client
                            </th>
                            <th
                                class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-widest bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Statut
                            </th>
                            <th
                                class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-widest bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden text-right"
                            >
                                Budget
                            </th>
                            <th
                                class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-widest bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden text-right"
                            >
                                Dépenses
                            </th>
                            <th
                                class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-widest bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden text-right"
                            >
                                Marge
                            </th>
                            <th
                                class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-widest bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Progression
                            </th>
                            <th
                                class="px-5 py-3.5 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            ></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr
                            v-for="project in projects.data"
                            :key="project.id"
                            class="hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors group cursor-pointer"
                            @click="navigateTo(project.id)"
                        >
                            <!-- Nom projet -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-violet-50 dark:bg-violet-900/30 flex items-center justify-center flex-shrink-0"
                                    >
                                        <span
                                            class="material-symbols-outlined text-violet-600 dark:text-violet-400 text-[18px]"
                                            >folder_open</span
                                        >
                                    </div>
                                    <div>
                                        <p
                                            class="font-semibold text-gray-900 dark:text-white text-sm"
                                        >
                                            {{ project.name }}
                                        </p>
                                        <p
                                            class="text-[11px] text-gray-400 font-mono"
                                        >
                                            {{ project.reference }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Client -->
                            <td
                                class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ project.client?.name }}
                            </td>

                            <!-- Statut -->
                            <td class="px-5 py-4">
                                <StatusBadge :status="project.status" />
                            </td>

                            <!-- Budget -->
                            <td
                                class="px-5 py-4 text-right font-mono text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                {{ formatCurrency(project.budget) }}
                            </td>

                            <!-- Dépenses -->
                            <td
                                class="px-5 py-4 text-right font-mono text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ formatCurrency(project.total_expenses) }}
                            </td>

                            <!-- Marge -->
                            <td class="px-5 py-4 text-right">
                                <span
                                    class="text-sm font-bold"
                                    :class="
                                        project.margin_percent >= 0
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-red-500'
                                    "
                                >
                                    {{ project.margin_percent >= 0 ? "+" : ""
                                    }}{{ project.margin_percent }}%
                                </span>
                            </td>

                            <!-- Barre de progression -->
                            <td class="px-5 py-4 w-44">
                                <div class="space-y-1">
                                    <div
                                        class="flex justify-between text-[10px] font-semibold text-gray-400 uppercase"
                                    >
                                        <span
                                            >{{ project.budget_used_percent }}%
                                            utilisé</span
                                        >
                                    </div>
                                    <div
                                        class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all duration-500"
                                            :class="
                                                barColor(
                                                    project.budget_used_percent,
                                                )
                                            "
                                            :style="{
                                                width:
                                                    Math.min(
                                                        project.budget_used_percent,
                                                        100,
                                                    ) + '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-right" @click.stop>
                                <div
                                    class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <Link
                                        :href="`/projects/${project.id}/edit`"
                                        v-if="
                                            $page.props.auth.user?.can
                                                ?.manage_projects
                                        "
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-all"
                                        title="Modifier"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[18px]"
                                            >edit</span
                                        >
                                    </Link>
                                    <button
                                        v-if="
                                            $page.props.auth.user?.role ===
                                            'admin'
                                        "
                                        @click="confirmDelete(project)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                                        title="Supprimer"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[18px]"
                                            >delete</span
                                        >
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- État vide -->
                        <tr v-if="!projects.data.length">
                            <td colspan="8" class="px-5 py-20 text-center">
                                <span
                                    class="material-symbols-outlined text-gray-200 dark:text-gray-700 text-[56px]"
                                    >folder_off</span
                                >
                                <p class="text-gray-400 mt-3 font-medium">
                                    Aucun projet trouvé
                                </p>
                                <Link
                                    href="/projects/create"
                                    class="inline-flex items-center gap-1 mt-3 text-primary-600 text-sm font-semibold hover:underline"
                                >
                                    <span
                                        class="material-symbols-outlined text-[16px]"
                                        >add</span
                                    >
                                    Créer le premier projet
                                </Link>
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
                    Affichage
                    <span class="font-bold text-gray-600 dark:text-gray-300"
                        >{{ projects.from ?? 0 }}–{{ projects.to ?? 0 }}</span
                    >
                    sur
                    <span class="font-bold text-gray-600 dark:text-gray-300">{{
                        projects.total
                    }}</span>
                    projets
                </p>
                <div class="flex items-center gap-1">
                    <Link
                        v-for="link in projects.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-medium transition-all',
                            link.active
                                ? 'bg-primary-600 text-white shadow-sm'
                                : link.url
                                  ? 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'
                                  : 'text-gray-300 dark:text-gray-700 cursor-not-allowed',
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>

        <!-- ── Modal confirmation suppression ─────────────── -->
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
                        class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 w-full max-w-md border border-gray-100 dark:border-gray-800"
                    >
                        <div class="flex items-start gap-4 mb-5">
                            <div
                                class="w-11 h-11 rounded-xl bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0"
                            >
                                <span
                                    class="material-symbols-outlined text-red-500 text-[22px]"
                                    >delete_forever</span
                                >
                            </div>
                            <div>
                                <h3
                                    class="font-headline font-bold text-gray-900 dark:text-white"
                                >
                                    Supprimer le projet
                                </h3>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                                >
                                    Confirmez-vous la suppression de
                                    <span
                                        class="font-semibold text-gray-700 dark:text-gray-300"
                                        >« {{ deleteModal.projectName }} »</span
                                    >
                                    ? Cette action est irréversible.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="deleteModal.show = false"
                                class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                            >
                                Annuler
                            </button>
                            <button
                                @click="executeDelete"
                                class="px-4 py-2 text-sm font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-all active:scale-95 shadow-sm"
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
import { ref, reactive, computed } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import StatCard from "@/Components/StatCard.vue";
import StatusBadge from "@/Components/StatusBadge.vue";

// ── Props depuis ProjectController::index() ──────────────────
const props = defineProps({
    projects: Object, // paginator
    stats: Object,
    clients: Array,
    filters: Object,
});

// ── Filtres locaux ───────────────────────────────────────────
const filters = reactive({
    status: props.filters?.status ?? "",
    client_id: props.filters?.client_id ?? "",
    search: props.filters?.search ?? "",
});

const searchQuery = ref(filters.search);

const statusFilters = [
    { value: "", label: "Tous" },
    { value: "en_cours", label: "En cours" },
    { value: "termine", label: "Terminés" },
    { value: "en_pause", label: "En pause" },
];

function setFilter(key, value) {
    filters[key] = filters[key] === value ? "" : value;
    applyFilters();
}

let searchTimer = null;
function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        filters.search = searchQuery.value;
        applyFilters();
    }, 350);
}

function applyFilters() {
    // router.get re-charge la page avec les nouveaux query params
    // preserveState: true conserve le scroll et l'état local
    router.get(
        "/projects",
        {
            status: filters.status || undefined,
            client_id: filters.client_id || undefined,
            search: filters.search || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

// ── Navigation vers détail ────────────────────────────────────
function navigateTo(id) {
    router.visit(`/projects/${id}`);
}

// ── Couleur barre progression ─────────────────────────────────
function barColor(percent) {
    if (percent >= 95) return "bg-red-500";
    if (percent >= 75) return "bg-amber-500";
    return "bg-primary-600";
}

// ── Formatage monétaire ───────────────────────────────────────
function formatCurrency(amount) {
    if (!amount && amount !== 0) return "—";
    if (amount >= 1_000_000) return (amount / 1_000_000).toFixed(1) + " M FCFA";
    return new Intl.NumberFormat("fr-FR").format(amount) + " FCFA";
}

// ── Suppression ───────────────────────────────────────────────
const deleteModal = reactive({ show: false, projectId: null, projectName: "" });

function confirmDelete(project) {
    deleteModal.show = true;
    deleteModal.projectId = project.id;
    deleteModal.projectName = project.name;
}

function executeDelete() {
    router.delete(`/projects/${deleteModal.projectId}`, {
        onSuccess: () => {
            deleteModal.show = false;
        },
    });
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: all 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95);
}
</style>
