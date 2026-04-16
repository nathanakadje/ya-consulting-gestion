<script setup>
import { ref, computed, reactive } from "vue";
import { Head, Link, router, usePage, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import StatCard from "@/Components/StatCard.vue";

// ── Props depuis ReportController::index() ───────────────
const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    byCategory: { type: Array, default: () => [] },
    evolution: { type: Array, default: () => [] },
    transactions: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    selectedMonth: { type: String, default: "" },
});

// ── Filtres locaux ────────────────────────────────────────
const localFilters = reactive({
    month: props.filters?.month ?? new Date().toISOString().slice(0, 7),
    project_id: props.filters?.projectId ?? "",
});

function applyFilters() {
    router.get(
        "/reports",
        {
            month: localFilters.month,
            project_id: localFilters.project_id || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

// ── Type de rapport sélectionné ───────────────────────────
const reportType = ref("monthly");
const reportTypes = [
    {
        value: "monthly",
        label: "Résumé mensuel",
        desc: "Vue financière globale du mois",
    },
    {
        value: "project",
        label: "Par projet",
        desc: "Suivi ressources et jalons",
    },
    {
        value: "audit",
        label: "Audit financier",
        desc: "Journal détaillé pour comptabilité",
    },
];

// ── Export ────────────────────────────────────────────────
const exporting = reactive({ pdf: false, excel: false });

function exportPdf() {
    exporting.pdf = true;
    const params = new URLSearchParams({
        month: localFilters.month,
        type: reportType.value,
        ...(localFilters.project_id && { project_id: localFilters.project_id }),
    });
    // Téléchargement direct (pas Inertia — on veut un fichier)
    window.location.href = `/reports/export-pdf?${params}`;
    setTimeout(() => (exporting.pdf = false), 3000);
}

function exportExcel() {
    exporting.excel = true;
    const params = new URLSearchParams({
        month: localFilters.month,
        ...(localFilters.project_id && { project_id: localFilters.project_id }),
    });
    window.location.href = `/reports/export-excel?${params}`;
    setTimeout(() => (exporting.excel = false), 3000);
}

// ── Graphique évolution (barres SVG manuelles) ────────────
const maxEvolution = computed(() =>
    Math.max(...props.evolution.map((m) => m.amount), 1),
);
function barH(amount) {
    return Math.max((amount / maxEvolution.value) * 100, 3);
}

// ── Catégories : pourcentage ──────────────────────────────
const totalCat = computed(() =>
    props.byCategory.reduce((s, c) => s + c.amount, 0),
);
function catPct(amount) {
    return totalCat.value > 0 ? Math.round((amount / totalCat.value) * 100) : 0;
}

// ── Formatage ─────────────────────────────────────────────
function fmt(v) {
    if (!v && v !== 0) return "—";
    return new Intl.NumberFormat("fr-FR").format(Math.round(v)) + " FCFA";
}
function fmtShort(v) {
    if (!v && v !== 0) return "—";
    if (v >= 1_000_000) return (v / 1_000_000).toFixed(1) + " M";
    if (v >= 1_000) return Math.round(v / 1_000) + " K";
    return Math.round(v) + "";
}

function statusClass(s) {
    return (
        {
            validated:
                "bg-emerald-50 dark:bg-emerald-600/10 text-emerald-700 dark:text-emerald-400",
            pending:
                "bg-amber-50 dark:bg-amber-600/10 text-amber-700 dark:text-amber-400",
            rejected:
                "bg-red-50 dark:bg-red-600/10 text-red-600 dark:text-red-400",
        }[s] ?? "bg-gray-100 text-gray-500"
    );
}
function statusLabel(s) {
    return (
        { validated: "Validée", pending: "En attente", rejected: "Rejetée" }[
            s
        ] ?? s
    );
}
</script>

<template>
    <Head title="Rapports" />

    <AppLayout title="Rapports" breadcrumb="Générateur de rapports">
        <!-- ── En-tête page ──────────────────────────────── -->
        <div class="flex items-start justify-between mb-6">
            <div>
                <!-- <h3
                    class="font-headline text-[20px] font-extrabold text-gray-900 dark:text-white tracking-tight"
                >
                    Générateur de rapports
                </h3> -->
                <p class="text-[12px] text-gray-400 dark:text-gray-500 mt-1">
                    Configurez et exportez vos données financières.
                </p>
            </div>
            <!-- Badge période active -->
            <div
                class="flex items-center gap-2 px-3.5 py-2 bg-amber-50 dark:bg-amber-600/10 border border-amber-200/60 dark:border-primary-600/20 rounded-xl"
            >
                <span
                    class="material-symbols-outlined text-amber-600 dark:text-amber400 text-[16px]"
                    style="font-variation-settings: &quot;FILL&quot; 1"
                    >calendar_month</span
                >
                <span
                    class="text-[12px] font-bold text-amber-700 dark:text-amber-300 capitalize"
                >
                    {{ selectedMonth }}
                </span>
            </div>
        </div>

        <!-- ── Layout principal 4/8 ──────────────────────── -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
            <!-- ═══ Panneau configuration (4 col) ══════════ -->
            <aside class="xl:col-span-4 flex flex-col gap-4">
                <!-- Type de rapport -->
                <div class="card p-5">
                    <p class="label-section mb-3">Type de rapport</p>
                    <div class="space-y-2">
                        <button
                            v-for="rt in reportTypes"
                            :key="rt.value"
                            @click="reportType = rt.value"
                            :class="[
                                'w-full flex items-center justify-between p-3.5 rounded-xl border-2 text-left transition-all',
                                reportType === rt.value
                                    ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-600/10'
                                    : 'border-gray-100 dark:border-gray-800 hover:border-gray-200 dark:hover:border-gray-700 bg-white dark:bg-[#111318]',
                            ]"
                        >
                            <div>
                                <p
                                    :class="[
                                        'text-[12.5px] font-bold leading-tight',
                                        reportType === rt.value
                                            ? 'text-emerald-700 dark:text-emerald-300'
                                            : 'text-gray-800 dark:text-gray-200',
                                    ]"
                                >
                                    {{ rt.label }}
                                </p>
                                <p class="text-[10.5px] text-gray-400 mt-[2px]">
                                    {{ rt.desc }}
                                </p>
                            </div>
                            <span
                                v-if="reportType === rt.value"
                                class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[18px] flex-shrink-0"
                                style="
                                    font-variation-settings: &quot;FILL&quot; 1;
                                "
                                >check_circle</span
                            >
                        </button>
                    </div>
                </div>

                <!-- Filtres période + projet -->
                <div class="card p-5 space-y-4">
                    <p class="label-section">Période & périmètre</p>

                    <div>
                        <label class="field-label">Mois</label>
                        <input
                            v-model="localFilters.month"
                            @change="applyFilters"
                            type="month"
                            class="field-input"
                        />
                    </div>

                    <div>
                        <label class="field-label">Projet (tous si vide)</label>
                        <select
                            v-model="localFilters.project_id"
                            @change="applyFilters"
                            class="field-input"
                        >
                            <option value="">— Tous les projets —</option>
                            <option
                                v-for="p in projects"
                                :key="p.id"
                                :value="p.id"
                            >
                                {{ p.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Boutons export -->
                <div class="space-y-2.5">
                    <button
                        @click="exportPdf"
                        :disabled="exporting.pdf"
                        class="w-full flex items-center justify-center gap-2.5 py-3 px-5 bg-violet-600 hover:bg-violet-700 disabled:opacity-60 text-white text-[13px] font-bold rounded-xl shadow-sm shadow-primary-600/20 transition-all active:scale-[0.98]"
                    >
                        <span
                            v-if="exporting.pdf"
                            class="material-symbols-outlined text-[18px] animate-spin"
                            >progress_activity</span
                        >
                        <span
                            v-else
                            class="material-symbols-outlined text-[18px]"
                            >picture_as_pdf</span
                        >
                        {{
                            exporting.pdf ? "Génération..." : "Télécharger PDF"
                        }}
                    </button>

                    <button
                        @click="exportExcel"
                        :disabled="exporting.excel"
                        class="w-full flex items-center justify-center gap-2.5 py-3 px-5 bg-white dark:bg-[#111318] hover:bg-gray-50 dark:hover:bg-white/[0.04] disabled:opacity-60 text-gray-700 dark:text-gray-300 text-[13px] font-bold rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all active:scale-[0.98]"
                    >
                        <span
                            v-if="exporting.excel"
                            class="material-symbols-outlined text-[18px] animate-spin"
                            >progress_activity</span
                        >
                        <span
                            v-else
                            class="material-symbols-outlined text-[18px] text-emerald-600"
                            >table_chart</span
                        >
                        {{
                            exporting.excel
                                ? "Génération..."
                                : "Exporter Excel (.xlsx)"
                        }}
                    </button>
                </div>

                <!-- Stats synthèse -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="card p-4 relative overflow-hidden">
                        <div
                            class="absolute left-0 top-0 bottom-0 w-[3px] bg-primary-600 rounded-l-2xl"
                        ></div>
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.1em] text-gray-400 mb-1.5 pl-2"
                        >
                            Projets actifs
                        </p>
                        <p
                            class="font-headline text-[22px] font-extrabold text-gray-900 dark:text-white pl-2"
                        >
                            {{ stats.active_projects ?? 0 }}
                        </p>
                    </div>
                    <div class="card p-4 relative overflow-hidden">
                        <div
                            class="absolute left-0 top-0 bottom-0 w-[3px] bg-violet-500 rounded-l-2xl"
                        ></div>
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.1em] text-gray-400 mb-1.5 pl-2"
                        >
                            Transactions
                        </p>
                        <p
                            class="font-headline text-[22px] font-extrabold text-gray-900 dark:text-white pl-2"
                        >
                            {{ stats.total_transactions ?? 0 }}
                        </p>
                    </div>
                    <div class="card p-4 relative overflow-hidden col-span-2">
                        <div
                            class="absolute left-0 top-0 bottom-0 w-[3px] bg-emerald-500 rounded-l-2xl"
                        ></div>
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.1em] text-gray-400 mb-1 pl-2"
                        >
                            Résultat mensuel
                        </p>
                        <p
                            class="font-headline text-[18px] font-extrabold pl-2"
                            :class="
                                (stats.monthly_result ?? 0) >= 0
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-red-500'
                            "
                        >
                            {{ (stats.monthly_result ?? 0) >= 0 ? "+" : ""
                            }}{{ fmt(stats.monthly_result) }}
                        </p>
                    </div>
                </div>
            </aside>

            <!-- ═══ Zone prévisualisation (8 col) ═══════════ -->
            <div class="xl:col-span-8 flex flex-col gap-5">
                <!-- Header préview -->
                <div class="card overflow-hidden">
                    <div
                        class="px-5 py-4 border-b border-gray-100 dark:border-gray-800/60 flex items-center justify-between bg-gray-50/50 dark:bg-white/[0.02]"
                    >
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-[30px] h-[30px] rounded-lg bg-primary-50 dark:bg-primary-600/10 flex items-center justify-center"
                            >
                                <span
                                    class="material-symbols-outlined text-primary-600 dark:text-primary-400 text-[16px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >preview</span
                                >
                            </div>
                            <div>
                                <h4
                                    class="font-headline font-bold text-gray-900 dark:text-white text-[13.5px]"
                                >
                                    Aperçu du rapport
                                </h4>
                                <p class="text-[10.5px] text-gray-400">
                                    {{ selectedMonth }}
                                </p>
                            </div>
                        </div>
                        <!-- Badge live -->
                        <div
                            class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-600/10 border border-emerald-200/60 dark:border-emerald-600/20 rounded-full"
                        >
                            <span
                                class="w-[6px] h-[6px] rounded-full bg-emerald-500 animate-pulse"
                            ></span>
                            <span
                                class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-[0.08em]"
                            >
                                Live
                            </span>
                        </div>
                    </div>

                    <!-- 4 KPI en ligne -->
                    <div
                        class="grid grid-cols-4 divide-x divide-gray-100 dark:divide-gray-800/60"
                    >
                        <div class="p-4">
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.1em] text-gray-400 mb-1"
                            >
                                Dépenses
                            </p>
                            <p
                                class="font-headline text-[16px] font-extrabold text-gray-900 dark:text-white"
                            >
                                {{ fmtShort(stats.monthly_expenses) }}
                                <span
                                    class="text-[10px] text-gray-400 font-normal"
                                    >FCFA</span
                                >
                            </p>
                            <p
                                class="text-[10px] mt-1 font-medium"
                                :class="
                                    (stats.evolution_pct ?? 0) <= 0
                                        ? 'text-amber-600 dark:text-amber-400'
                                        : 'text-red-500'
                                "
                            >
                                {{ (stats.evolution_pct ?? 0) > 0 ? "+" : ""
                                }}{{ stats.evolution_pct ?? 0 }}% vs mois préc.
                            </p>
                        </div>
                        <div class="p-4">
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.1em] text-gray-400 mb-1"
                            >
                                Gains
                            </p>
                            <p
                                class="font-headline text-[16px] font-extrabold text-emerald-600 dark:text-emerald-400"
                            >
                                {{ fmtShort(stats.monthly_gains) }}
                                <span
                                    class="text-[10px] font-normal text-gray-400"
                                    >FCFA</span
                                >
                            </p>
                            <p class="text-[10px] mt-1 text-gray-400">
                                {{ stats.terminated_count ?? 0 }} projet(s)
                                terminé(s)
                            </p>
                        </div>
                        <div class="p-4">
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.1em] text-gray-400 mb-1"
                            >
                                Résultat
                            </p>
                            <p
                                class="font-headline text-[16px] font-extrabold"
                                :class="
                                    (stats.monthly_result ?? 0) >= 0
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-red-500'
                                "
                            >
                                {{ (stats.monthly_result ?? 0) >= 0 ? "+" : ""
                                }}{{ fmtShort(stats.monthly_result) }}
                                <span
                                    class="text-[10px] font-normal text-gray-400"
                                    >FCFA</span
                                >
                            </p>
                        </div>
                        <div class="p-4">
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.1em] text-gray-400 mb-1"
                            >
                                Projets actifs
                            </p>
                            <p
                                class="font-headline text-[16px] font-extrabold text-gray-900 dark:text-white"
                            >
                                {{ stats.active_projects ?? 0 }}
                            </p>
                            <p class="text-[10px] mt-1 text-gray-400">
                                Ce mois-ci
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Graphique évolution 6 mois + catégories -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Évolution barres -->
                    <div class="card p-5">
                        <h5
                            class="font-headline font-bold text-gray-900 dark:text-white text-[13px] mb-4"
                        >
                            Évolution dépenses (6 mois)
                        </h5>
                        <div class="flex items-end gap-2 h-28">
                            <div
                                v-for="(m, i) in evolution"
                                :key="i"
                                class="flex-1 flex flex-col items-center gap-1.5 group"
                            >
                                <div
                                    class="w-full rounded-t-lg transition-all duration-500 relative cursor-default"
                                    :class="
                                        i === evolution.length - 1
                                            ? 'bg-primary-600 dark:bg-primary-500'
                                            : 'bg-primary-100 dark:bg-primary-900/30 group-hover:bg-primary-300 dark:group-hover:bg-primary-800'
                                    "
                                    :style="{
                                        height: barH(m.amount) + '%',
                                        minHeight: '5px',
                                    }"
                                >
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 bg-gray-900 dark:bg-gray-700 text-white text-[10px] font-bold px-2 py-1 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"
                                    >
                                        {{ fmtShort(m.amount) }} FCFA
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
                        <div
                            v-if="!evolution.length"
                            class="h-28 flex items-center justify-center"
                        >
                            <p class="text-[11.5px] text-gray-400">
                                Aucune donnée
                            </p>
                        </div>
                    </div>

                    <!-- Répartition catégories -->
                    <div class="card p-5">
                        <h5
                            class="font-headline font-bold text-gray-900 dark:text-white text-[13px] mb-4"
                        >
                            Par catégorie
                        </h5>
                        <div class="space-y-3">
                            <div
                                v-for="cat in byCategory.slice(0, 5)"
                                :key="cat.name"
                                class="space-y-1"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-[7px] h-[7px] rounded-full flex-shrink-0"
                                            :style="{
                                                backgroundColor: cat.color,
                                            }"
                                        ></div>
                                        <span
                                            class="text-[11.5px] text-gray-600 dark:text-gray-400 font-medium"
                                        >
                                            {{ cat.name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] text-gray-400"
                                            >{{ catPct(cat.amount) }}%</span
                                        >
                                        <span
                                            class="text-[11px] font-bold font-mono text-gray-900 dark:text-white"
                                        >
                                            {{ fmtShort(cat.amount) }}
                                        </span>
                                    </div>
                                </div>
                                <div
                                    class="h-[4px] bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"
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
                        <div v-if="!byCategory.length" class="py-6 text-center">
                            <p class="text-[11.5px] text-gray-400">
                                Aucune dépense ce mois
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Table transactions preview -->
                <div class="card overflow-hidden">
                    <div
                        class="px-5 py-4 border-b border-gray-100 dark:border-gray-800/60 flex items-center justify-between"
                    >
                        <h5
                            class="font-headline font-bold text-gray-900 dark:text-white text-[13.5px]"
                        >
                            Transactions du mois
                        </h5>
                        <span class="text-[11px] text-gray-400 font-medium">
                            {{ stats.total_transactions ?? 0 }} au total
                            <span class="text-gray-300 dark:text-gray-700 mx-1"
                                >·</span
                            >
                            Aperçu des 20 premières
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr
                                    class="border-b border-gray-50 dark:border-gray-800/60 text-[10.5px] font-bold uppercase tracking-[0.08em] text-gray-400"
                                >
                                    <th
                                        class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                                    >
                                        Date
                                    </th>
                                    <th
                                        class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                                    >
                                        Projet
                                    </th>
                                    <th
                                        class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                                    >
                                        Description
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
                                        class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden text-center"
                                    >
                                        Statut
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-50 dark:divide-gray-800/40 text-[12px]"
                            >
                                <tr
                                    v-for="tx in transactions"
                                    :key="tx.id"
                                    class="hover:bg-gray-50/70 dark:hover:bg-white/[0.02] transition-colors"
                                >
                                    <td
                                        class="px-5 py-3 text-gray-400 font-mono text-[11px] whitespace-nowrap"
                                    >
                                        {{ tx.date }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <Link
                                            :href="`/projects/${tx.project?.id ?? '#'}`"
                                            class="font-semibold text-gray-700 dark:text-gray-300 hover:text-primary-600 transition-colors"
                                        >
                                            {{ tx.project?.name ?? "—" }}
                                        </Link>
                                    </td>
                                    <td
                                        class="px-5 py-3 text-gray-600 dark:text-gray-400 max-w-[160px] truncate"
                                    >
                                        {{ tx.description }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-[3px] rounded-full text-[10px] font-bold"
                                            :style="{
                                                backgroundColor:
                                                    (tx.category?.color ??
                                                        '#6b7280') + '18',
                                                color:
                                                    tx.category?.color ??
                                                    '#6b7280',
                                            }"
                                        >
                                            {{ tx.category?.name ?? "—" }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-5 py-3 text-right font-bold font-mono text-gray-900 dark:text-white whitespace-nowrap"
                                    >
                                        {{ fmt(tx.amount) }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span
                                            :class="[
                                                'px-2 py-[3px] rounded-full text-[10px] font-bold uppercase',
                                                statusClass(tx.status),
                                            ]"
                                        >
                                            {{ statusLabel(tx.status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!transactions.length">
                                    <td
                                        colspan="6"
                                        class="px-5 py-14 text-center"
                                    >
                                        <span
                                            class="material-symbols-outlined text-gray-200 dark:text-gray-800 text-[40px]"
                                            >receipt_long</span
                                        >
                                        <p
                                            class="text-[12px] text-gray-400 mt-2"
                                        >
                                            Aucune transaction ce mois
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Total -->
                    <div
                        class="px-5 py-3.5 bg-gray-50/60 dark:bg-white/[0.015] border-t border-gray-100 dark:border-gray-800/60 flex items-center justify-between"
                    >
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-[0.1em] text-gray-400"
                        >
                            Total estimé
                        </span>
                        <span
                            class="font-headline font-extrabold text-[15px] text-amber-500 dark:text-amber-400"
                        >
                            {{ fmt(stats.monthly_expenses) }}
                        </span>
                    </div>
                </div>
            </div>
            <!-- fin prévisualisation -->
        </div>
    </AppLayout>
</template>

<style scoped>
/* Tokens partagés avec le layout */
.card {
    @apply bg-white dark:bg-[#111318]
           rounded-2xl border border-gray-100 dark:border-gray-800/70
           shadow-[0_1px_3px_rgba(0,0,0,0.04),0_4px_12px_rgba(0,0,0,0.03)];
}
.label-section {
    @apply text-[10.5px] font-bold uppercase tracking-[0.12em] text-gray-400 dark:text-gray-600;
}
.field-label {
    @apply block text-[10.5px] font-bold uppercase tracking-[0.1em] text-gray-500 dark:text-gray-500 mb-1.5;
}
.field-input {
    @apply w-full rounded-xl border border-gray-200 dark:border-gray-700
           bg-gray-50 dark:bg-gray-800/60 text-gray-900 dark:text-white
           px-3.5 py-2.5 text-[12.5px]
           outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/30
           transition-all appearance-none;
}
</style>
