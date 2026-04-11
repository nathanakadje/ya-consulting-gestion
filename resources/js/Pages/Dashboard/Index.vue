<script setup>
import { computed } from "vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import StatCard from "@/Components/StatCard.vue";
import StatusBadge from "@/Components/StatusBadge.vue";

// ── Props depuis DashboardController ─────────────────────────
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            active_projects: 0,
            paused_projects: 0,
            total_budget: 0,
            avg_margin: 0,
            expenses_this_month: 0,
        }),
    },
    recentProjects: { type: Array, default: () => [] },
    recentExpenses: { type: Array, default: () => [] },
});

const page = usePage();

const firstName = computed(
    () => page.props.auth.user?.name?.split(" ")[0] ?? "là",
);

const today = computed(() =>
    new Date().toLocaleDateString("fr-FR", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    }),
);

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return "Bonjour";
    if (h < 18) return "Bon après-midi";
    return "Bonsoir";
});

function formatCurrency(v) {
    if (!v && v !== 0) return "—";
    return new Intl.NumberFormat("fr-FR").format(Math.round(v)) + " FCFA";
}

function formatShort(v) {
    if (!v && v !== 0) return "—";
    if (v >= 1_000_000) return (v / 1_000_000).toFixed(1) + " M FCFA";
    if (v >= 1_000) return Math.round(v / 1_000) + " K FCFA";
    return Math.round(v) + " FCFA";
}

function progressColor(pct) {
    if (pct >= 95) return "bg-red-500";
    if (pct >= 75) return "bg-amber-500";
    return "bg-primary-600";
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout title="Dashboard">
        <!-- ── Bandeau de bienvenue ───────────────────────────── -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <p
                    class="text-[10.5px] font-bold uppercase tracking-[0.15em] text-gray-400 dark:text-gray-600 mb-[5px]"
                >
                    {{ today }}
                </p>
                <!-- <h3
                    class="font-headline text-[20px] font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight"
                >
                    {{ greeting }}, {{ firstName }}
                    <span class="inline-block animate-wave origin-[70%_70%]"
                        >👋</span
                    >
                </h3> -->
                <!-- <p class="text-[12px] text-gray-400 dark:text-gray-500 mt-1">
                    Résumé de l'activité de Ya Consulting.
                </p> -->
            </div>

            <Link
                v-if="page.props.auth.user?.can?.manage_projects"
                href="/projects/create"
                class="hidden sm:flex items-center gap-1.5 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-[12px] font-semibold rounded-xl shadow-sm shadow-primary-600/20 transition-all active:scale-95 flex-shrink-0"
            >
                <span class="material-symbols-outlined text-[16px]">add</span>
                Nouveau projet
            </Link>
        </div>

        <!-- ── 4 cartes stats ─────────────────────────────────── -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <StatCard
                icon="account_tree"
                label="Projets actifs"
                :value="stats.active_projects"
                :trend="
                    stats.paused_projects > 0
                        ? stats.paused_projects + ' en pause'
                        : 'Tous opérationnels'
                "
                :trend-up="stats.paused_projects === 0"
                color="primary"
            />
            <StatCard
                icon="account_balance_wallet"
                label="Budget total"
                :value="formatShort(stats.total_budget)"
                trend="Tous projets actifs"
                color="emerald"
            />
            <StatCard
                icon="trending_up"
                label="Marge moyenne"
                :value="(stats.avg_margin ?? 0) + '%'"
                trend="Projets terminés"
                :trend-up="(stats.avg_margin ?? 0) >= 20"
                color="violet"
            />
            <StatCard
                icon="receipt_long"
                label="Dépenses ce mois"
                :value="formatShort(stats.expenses_this_month)"
                trend="Mois en cours"
                color="amber"
            />
        </div>

        <!-- ── Corps principal 7/5 ───────────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
            <!-- ═══ Projets récents — 7 col ══════════════════ -->
            <div class="xl:col-span-7 flex flex-col">
                <div class="card flex flex-col h-full">
                    <!-- Header carte -->
                    <div class="card-header">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="card-icon bg-primary-50 dark:bg-primary-600/10"
                            >
                                <span
                                    class="material-symbols-outlined text-primary-600 dark:text-primary-400 text-[16px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >account_tree</span
                                >
                            </div>
                            <div>
                                <h4 class="card-title">Projets récents</h4>
                                <p class="card-subtitle">
                                    {{ stats.active_projects }} actif{{
                                        stats.active_projects > 1 ? "s" : ""
                                    }}
                                </p>
                            </div>
                        </div>
                        <Link href="/projects" class="see-all-link">
                            Voir tous
                            <span class="material-symbols-outlined text-[13px]"
                                >arrow_forward</span
                            >
                        </Link>
                    </div>

                    <!-- Lignes projets -->
                    <div
                        class="flex-1 divide-y divide-gray-50 dark:divide-gray-800/50"
                    >
                        <Link
                            v-for="project in recentProjects"
                            :key="project.id"
                            :href="`/projects/${project.id}`"
                            class="flex items-center gap-3.5 px-5 py-3.5 hover:bg-gray-50/70 dark:hover:bg-white/[0.02] transition-colors group"
                        >
                            <!-- Avatar projet -->
                            <div
                                class="w-[34px] h-[34px] rounded-xl bg-primary-50 dark:bg-primary-600/10 flex items-center justify-center flex-shrink-0"
                            >
                                <span
                                    class="material-symbols-outlined text-primary-600 dark:text-primary-400 text-[16px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >folder_open</span
                                >
                            </div>

                            <!-- Nom + client -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-[2px]">
                                    <p
                                        class="text-[12.5px] font-semibold text-gray-900 dark:text-white truncate leading-tight"
                                    >
                                        {{ project.name }}
                                    </p>
                                    <StatusBadge
                                        :status="project.status"
                                        class="flex-shrink-0"
                                    />
                                </div>
                                <p
                                    class="text-[11px] text-gray-400 truncate leading-tight"
                                >
                                    {{ project.client?.name }}
                                </p>
                            </div>

                            <!-- Barre progression -->
                            <div
                                class="hidden md:flex flex-col items-end gap-1 w-[100px] flex-shrink-0"
                            >
                                <div class="flex justify-between w-full">
                                    <span class="text-[10px] text-gray-400">
                                        {{ project.budget_used_percent ?? 0 }}%
                                    </span>
                                    <span
                                        class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 font-mono"
                                    >
                                        {{ formatShort(project.budget) }}
                                    </span>
                                </div>
                                <div
                                    class="w-full h-[4px] bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"
                                >
                                    <div
                                        class="h-full rounded-full transition-all duration-700"
                                        :class="
                                            progressColor(
                                                project.budget_used_percent ??
                                                    0,
                                            )
                                        "
                                        :style="{
                                            width:
                                                Math.min(
                                                    project.budget_used_percent ??
                                                        0,
                                                    100,
                                                ) + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <span
                                class="material-symbols-outlined text-[15px] text-gray-300 dark:text-gray-700 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                            >
                                chevron_right
                            </span>
                        </Link>

                        <!-- État vide -->
                        <div
                            v-if="!recentProjects.length"
                            class="py-12 flex flex-col items-center"
                        >
                            <div
                                class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3"
                            >
                                <span
                                    class="material-symbols-outlined text-gray-300 dark:text-gray-700 text-[22px]"
                                    >folder_off</span
                                >
                            </div>
                            <p
                                class="text-[12px] font-medium text-gray-400 mb-2"
                            >
                                Aucun projet
                            </p>
                            <Link
                                href="/projects/create"
                                class="text-[11.5px] text-primary-600 dark:text-primary-400 font-semibold hover:underline flex items-center gap-1"
                            >
                                <span
                                    class="material-symbols-outlined text-[13px]"
                                    >add</span
                                >Créer un projet
                            </Link>
                        </div>
                    </div>

                    <!-- Pied carte -->
                    <div class="card-footer">
                        <Link
                            href="/projects"
                            class="text-[11px] font-semibold text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors flex items-center gap-1"
                        >
                            <span class="material-symbols-outlined text-[13px]"
                                >grid_view</span
                            >
                            Portefeuille complet
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ═══ Colonne droite — 5 col ═════════════════════ -->
            <div class="xl:col-span-5 flex flex-col gap-5">
                <!-- Dépenses récentes -->
                <div class="card">
                    <div class="card-header">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="card-icon bg-amber-50 dark:bg-amber-600/10"
                            >
                                <span
                                    class="material-symbols-outlined text-amber-600 dark:text-amber-400 text-[16px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >receipt_long</span
                                >
                            </div>
                            <h4 class="card-title">Dernières dépenses</h4>
                        </div>
                        <Link href="/expenses" class="see-all-link"
                            >Voir tout</Link
                        >
                    </div>

                    <div
                        class="divide-y divide-gray-50 dark:divide-gray-800/50"
                    >
                        <div
                            v-for="expense in recentExpenses"
                            :key="expense.id"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/70 dark:hover:bg-white/[0.02] transition-colors"
                        >
                            <!-- Pastille catégorie -->
                            <div
                                class="w-[30px] h-[30px] rounded-xl flex items-center justify-center flex-shrink-0"
                                :style="{
                                    backgroundColor:
                                        (expense.category?.color ?? '#6b7280') +
                                        '18',
                                    color: expense.category?.color ?? '#6b7280',
                                }"
                            >
                                <span
                                    class="material-symbols-outlined text-[14px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                >
                                    {{ expense.category?.icon ?? "receipt" }}
                                </span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-[12px] font-semibold text-gray-800 dark:text-gray-200 truncate leading-tight"
                                >
                                    {{ expense.description }}
                                </p>
                                <p
                                    class="text-[10.5px] text-gray-400 truncate leading-tight mt-[1px]"
                                >
                                    {{ expense.project?.name }}
                                </p>
                            </div>

                            <span
                                class="text-[12px] font-bold font-mono text-gray-900 dark:text-white whitespace-nowrap flex-shrink-0"
                            >
                                {{ formatShort(expense.amount) }}
                            </span>
                        </div>

                        <div
                            v-if="!recentExpenses.length"
                            class="py-10 text-center"
                        >
                            <span
                                class="material-symbols-outlined text-gray-200 dark:text-gray-800 text-[28px]"
                                >receipt_long</span
                            >
                            <p class="text-[11.5px] text-gray-400 mt-1.5">
                                Aucune dépense récente
                            </p>
                        </div>
                    </div>

                    <div class="card-footer flex items-center justify-between">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-[0.1em] text-gray-400"
                            >Ce mois</span
                        >
                        <span
                            class="font-headline font-extrabold text-[13.5px] text-primary-600 dark:text-primary-400"
                        >
                            {{ formatCurrency(stats.expenses_this_month) }}
                        </span>
                    </div>
                </div>

                <!-- Jauge rentabilité -->
                <div class="card p-5 relative overflow-hidden">
                    <!-- Halo décoratif -->
                    <div
                        class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-primary-50 dark:bg-primary-600/5 blur-3xl pointer-events-none"
                    ></div>

                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p
                                class="text-[10.5px] font-bold uppercase tracking-[0.12em] text-gray-400 dark:text-gray-600 mb-1"
                            >
                                Marge moyenne
                            </p>
                            <p
                                class="font-headline text-[26px] font-extrabold leading-none tracking-tight"
                                :class="
                                    (stats.avg_margin ?? 0) >= 0
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-red-500'
                                "
                            >
                                {{ (stats.avg_margin ?? 0) >= 0 ? "+" : ""
                                }}{{ stats.avg_margin ?? 0 }}%
                            </p>
                        </div>
                        <div
                            class="w-[36px] h-[36px] rounded-xl bg-emerald-50 dark:bg-emerald-600/10 flex items-center justify-center"
                        >
                            <span
                                class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[18px]"
                                style="
                                    font-variation-settings: &quot;FILL&quot; 1;
                                "
                                >trending_up</span
                            >
                        </div>
                    </div>

                    <!-- Barre marge -->
                    <div class="relative mb-1.5">
                        <div
                            class="h-[5px] bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-1000"
                                :class="
                                    (stats.avg_margin ?? 0) >= 30
                                        ? 'bg-emerald-500'
                                        : (stats.avg_margin ?? 0) >= 15
                                          ? 'bg-amber-500'
                                          : 'bg-red-500'
                                "
                                :style="{
                                    width:
                                        Math.min(
                                            Math.max(stats.avg_margin ?? 0, 0),
                                            100,
                                        ) + '%',
                                }"
                            ></div>
                        </div>
                        <!-- Marqueur objectif 20% -->
                        <div
                            class="absolute top-0 h-[5px] w-px bg-gray-300/70 dark:bg-gray-600"
                            style="left: 20%"
                        ></div>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] text-gray-400">
                            Objectif
                            <span
                                class="font-semibold text-gray-500 dark:text-gray-400"
                                >20%</span
                            >
                        </p>
                        <Link
                            href="/reports"
                            class="text-[11px] font-semibold text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-0.5"
                        >
                            Rapports
                            <span class="material-symbols-outlined text-[12px]"
                                >arrow_forward</span
                            >
                        </Link>
                    </div>
                </div>

                <!-- Accès rapides 2x2 -->
                <div class="grid grid-cols-2 gap-3">
                    <Link
                        href="/projects/create"
                        class="quick-btn bg-primary-600 hover:bg-primary-700 text-white shadow-sm shadow-primary-600/20"
                    >
                        <span
                            class="material-symbols-outlined text-[20px] mb-1.5 transition-transform group-hover:scale-110"
                            style="font-variation-settings: &quot;FILL&quot; 1"
                            >add_circle</span
                        >
                        <span>Nouveau projet</span>
                    </Link>
                    <Link
                        href="/expenses"
                        class="quick-btn bg-white dark:bg-[#111318] text-gray-700 dark:text-gray-300 border border-gray-100 dark:border-gray-800/70 hover:bg-gray-50 dark:hover:bg-white/[0.04]"
                    >
                        <span
                            class="material-symbols-outlined text-[20px] mb-1.5 text-amber-500 transition-transform group-hover:scale-110"
                            style="font-variation-settings: &quot;FILL&quot; 1"
                            >receipt_long</span
                        >
                        <span>Dépenses</span>
                    </Link>
                    <Link
                        href="/reports"
                        class="quick-btn bg-white dark:bg-[#111318] text-gray-700 dark:text-gray-300 border border-gray-100 dark:border-gray-800/70 hover:bg-gray-50 dark:hover:bg-white/[0.04]"
                    >
                        <span
                            class="material-symbols-outlined text-[20px] mb-1.5 text-violet-500 transition-transform group-hover:scale-110"
                            style="font-variation-settings: &quot;FILL&quot; 1"
                            >bar_chart</span
                        >
                        <span>Rapports</span>
                    </Link>
                    <Link
                        href="/team"
                        class="quick-btn bg-white dark:bg-[#111318] text-gray-700 dark:text-gray-300 border border-gray-100 dark:border-gray-800/70 hover:bg-gray-50 dark:hover:bg-white/[0.04]"
                    >
                        <span
                            class="material-symbols-outlined text-[20px] mb-1.5 text-emerald-500 transition-transform group-hover:scale-110"
                            style="font-variation-settings: &quot;FILL&quot; 1"
                            >groups</span
                        >
                        <span>Équipe</span>
                    </Link>
                </div>
            </div>
            <!-- fin col droite -->
        </div>
    </AppLayout>
</template>

<style scoped>
/* ── Composants réutilisables ─────────────────────────────── */
.card {
    @apply bg-white dark:bg-[#111318]
           rounded-2xl border border-gray-100 dark:border-gray-800/70
           shadow-[0_1px_3px_rgba(0,0,0,0.04),0_4px_12px_rgba(0,0,0,0.03)]
           overflow-hidden;
}

.card-header {
    @apply flex items-center justify-between px-5 py-4
           border-b border-gray-100 dark:border-gray-800/60;
}

.card-title {
    @apply font-headline font-bold text-gray-900 dark:text-white text-[13.5px] leading-tight;
}

.card-subtitle {
    @apply text-[10.5px] text-gray-400 leading-tight mt-[1px];
}

.card-icon {
    @apply w-[30px] h-[30px] rounded-lg flex items-center justify-center flex-shrink-0;
}

.card-footer {
    @apply px-5 py-3 bg-gray-50/60 dark:bg-white/[0.015]
           border-t border-gray-100 dark:border-gray-800/60;
}

.see-all-link {
    @apply flex items-center gap-0.5 text-[11.5px] font-semibold
           text-primary-600 dark:text-primary-400
           hover:text-primary-700 dark:hover:text-primary-300 transition-colors;
}

/* Boutons accès rapides */
.quick-btn {
    @apply flex flex-col items-center justify-center py-4 px-3
           rounded-2xl text-[11.5px] font-semibold text-center
           transition-all active:scale-95
           shadow-[0_1px_3px_rgba(0,0,0,0.04)];
}

/* Animation main */
@keyframes wave {
    0%,
    100% {
        transform: rotate(0deg);
    }
    20% {
        transform: rotate(18deg);
    }
    40% {
        transform: rotate(-10deg);
    }
    60% {
        transform: rotate(14deg);
    }
    80% {
        transform: rotate(-6deg);
    }
}
.animate-wave {
    animation: wave 1.8s ease-in-out 0.6s 1;
}
</style>
