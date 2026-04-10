<template>
    <!-- resources/js/Pages/Dashboard/Index.vue -->
    <Head title="Dashboard" />

    <AppLayout title="Dashboard" breadcrumb="Vue d'ensemble">
        <!-- ── En-tête de bienvenue ──────────────────────────────── -->
        <div class="flex items-start justify-between mb-8">
            <div>
                <h3
                    class="font-headline text-2xl font-extrabold text-gray-900 tracking-tight"
                >
                    Bonjour, {{ firstName }}
                </h3>
                <p class="text-gray-500 mt-1 text-sm">
                    Voici un résumé de l'activité de Ya Consulting.
                </p>
            </div>
            <span
                class="text-sm text-gray-400 hidden md:block"
            >
                {{ today }}
            </span>
        </div>

        <!-- ── Cartes statistiques ───────────────────────────────── -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
            <StatCard
                icon="account_tree"
                label="Projets actifs"
                :value="stats.active_projects"
                :trend="null"
                color="primary"
            />
            <StatCard
                icon="account_balance_wallet"
                label="Budget total"
                :value="formatCurrency(stats.total_budget)"
                trend="+8% ce trimestre"
                trend-up
                color="emerald"
            />
            <StatCard
                icon="trending_up"
                label="Marge moyenne"
                :value="stats.avg_margin + '%'"
                trend="+2.1% vs mois dernier"
                trend-up
                color="violet"
            />
            <StatCard
                icon="warning"
                label="En pause"
                :value="stats.paused_projects"
                :trend="
                    stats.paused_projects > 0
                        ? stats.paused_projects + ' nécessitent attention'
                        : 'Aucun'
                "
                :trend-up="false"
                color="amber"
            />
        </div>

        <!-- ── Grille principale ──────────────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Derniers projets (2/3) -->
            <div class="xl:col-span-2">
                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                >
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-100"
                    >
                        <h4
                            class="font-headline font-bold text-gray-900"
                        >
                            Projets récents
                        </h4>
                        <Link
                            href="/projects"
                            class="text-xs text-primary-600 font-semibold hover:underline flex items-center gap-1"
                        >
                            Voir tous
                            <span class="material-symbols-outlined text-[14px]"
                                >arrow_forward</span
                            >
                        </Link>
                    </div>

                    <div class="divide-y divide-gray-50">
                        <div
                            v-for="project in recentProjects"
                            :key="project.id"
                            class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50/50 transition-colors cursor-pointer group"
                            @click="$inertia.visit('/projects/' + project.id)"
                        >
                            <!-- Icône projet -->
                            <div
                                class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0"
                            >
                                <span
                                    class="material-symbols-outlined text-primary-600 text-[20px]"
                                    >folder_open</span
                                >
                            </div>

                            <!-- Info projet -->
                            <div class="flex-1 min-w-0">
                                <p
                                    class="font-semibold text-gray-900 text-sm truncate"
                                >
                                    {{ project.name }}
                                </p>
                                <p class="text-xs text-gray-400 truncate">
                                    {{ project.client?.name }}
                                </p>
                            </div>

                            <!-- Barre de progression -->
                            <div
                                class="hidden sm:flex flex-col items-end gap-1 w-28"
                            >
                                <span class="text-xs text-gray-400"
                                    >{{ project.budget_used_percent }}%</span
                                >
                                <div
                                    class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden"
                                >
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :class="
                                            project.budget_used_percent > 90
                                                ? 'bg-red-500'
                                                : project.budget_used_percent >
                                                    70
                                                  ? 'bg-amber-500'
                                                  : 'bg-primary-600'
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

                            <!-- Badge statut -->
                            <StatusBadge :status="project.status" />

                            <!-- Flèche hover -->
                            <span
                                class="material-symbols-outlined text-gray-300 text-[18px] opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                chevron_right
                            </span>
                        </div>

                        <div
                            v-if="!recentProjects.length"
                            class="px-6 py-12 text-center"
                        >
                            <span
                                class="material-symbols-outlined text-gray-300 text-[48px]"
                                >account_tree</span
                            >
                            <p class="text-gray-400 text-sm mt-2">
                                Aucun projet pour le moment
                            </p>
                            <Link
                                href="/projects/create"
                                class="inline-flex items-center gap-1 mt-3 text-primary-600 text-sm font-semibold hover:underline"
                            >
                                <span
                                    class="material-symbols-outlined text-[16px]"
                                    >add</span
                                >
                                Créer un projet
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dépenses récentes (1/3) -->
            <div>
                <div
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
                >
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-100"
                    >
                        <h4
                            class="font-headline font-bold text-gray-900"
                        >
                            Dernières dépenses
                        </h4>
                        <Link
                            href="/expenses"
                            class="text-xs text-primary-600 font-semibold hover:underline"
                        >
                            Voir
                        </Link>
                    </div>

                    <div class="divide-y divide-gray-50">
                        <div
                            v-for="expense in recentExpenses"
                            :key="expense.id"
                            class="flex items-start gap-3 px-5 py-3.5"
                        >
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                                :style="{
                                    backgroundColor:
                                        expense.category?.color + '15',
                                    color: expense.category?.color,
                                }"
                            >
                                <span
                                    class="material-symbols-outlined text-[16px]"
                                    >{{
                                        expense.category?.icon ?? "receipt"
                                    }}</span
                                >
                            </div>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-medium text-gray-800 truncate"
                                >
                                    {{ expense.description }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ expense.project?.name }}
                                </p>
                            </div>
                            <span
                                class="text-sm font-bold font-mono text-gray-900 whitespace-nowrap"
                            >
                                {{ formatCurrencyShort(expense.amount) }}
                            </span>
                        </div>

                        <div
                            v-if="!recentExpenses.length"
                            class="px-5 py-10 text-center"
                        >
                            <p class="text-gray-400 text-xs">
                                Aucune dépense récente
                            </p>
                        </div>
                    </div>

                    <!-- Total du mois -->
                    <div
                        class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center"
                    >
                        <span
                            class="text-xs text-gray-400 font-medium uppercase tracking-wide"
                            >Ce mois</span
                        >
                        <span
                            class="font-headline font-bold text-primary-600 text-sm"
                        >
                            {{ formatCurrency(stats.expenses_this_month) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import StatCard from "@/Components/StatCard.vue";
import StatusBadge from "@/Components/StatusBadge.vue";

// ── Props envoyées par DashboardController ──────────────────
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            active_projects: 0,
            total_budget: 0,
            avg_margin: 0,
            paused_projects: 0,
            expenses_this_month: 0,
        }),
    },
    recentProjects: { type: Array, default: () => [] },
    recentExpenses: { type: Array, default: () => [] },
});

const page = usePage();

// ── Prénom de l'utilisateur ───────────────────────────────────
const firstName = computed(
    () => page.props.auth.user?.name?.split(" ")[0] ?? "là",
);

// ── Date du jour formatée ─────────────────────────────────────
const today = computed(() => {
    return new Date().toLocaleDateString("fr-FR", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
});

// ── Formatage monétaire ───────────────────────────────────────
function formatCurrency(amount) {
    return new Intl.NumberFormat("fr-CI", {
        style: "currency",
        currency: "XOF",
        maximumFractionDigits: 0,
    }).format(amount ?? 0);
}

function formatCurrencyShort(amount) {
    if (amount >= 1_000_000) return (amount / 1_000_000).toFixed(1) + " M FCFA";
    if (amount >= 1_000) return (amount / 1_000).toFixed(0) + " K FCFA";
    return amount + " FCFA";
}
</script>
