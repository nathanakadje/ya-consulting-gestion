<script setup>
// resources/js/Pages/Team/Index.vue
import { reactive } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import StatCard from "@/Components/StatCard.vue";

const props = defineProps({
    members: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});

// ── Rôles ─────────────────────────────────────────────────
const roles = {
    admin: {
        label: "Administrateur",
        color: "bg-red-50 dark:bg-red-600/10 text-red-600 dark:text-red-400",
    },
    project_manager: {
        label: "Chef de projet",
        color: "bg-primary-50 dark:bg-primary-600/10 text-primary-700 dark:text-primary-400",
    },
    staff_member: {
        label: "Collaborateur",
        color: "bg-violet-100 dark:bg-gray-800 text-violet-600 dark:text-violet-400",
    },
};

// ── Changement de rôle rapide ─────────────────────────────
function updateRole(userId, newRole) {
    router.patch(
        `/team/${userId}/role`,
        { role: newRole },
        {
            preserveScroll: true,
        },
    );
}

// ── Suppression ───────────────────────────────────────────
const deleteModal = reactive({ show: false, id: null, name: "" });

function confirmDelete(member) {
    deleteModal.id = member.id;
    deleteModal.name = member.name;
    deleteModal.show = true;
}

function executeDelete() {
    router.delete(`/team/${deleteModal.id}`, {
        onSuccess: () => {
            deleteModal.show = false;
        },
    });
}

// ── Initiales avatar ──────────────────────────────────────
function initials(name) {
    return name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .slice(0, 2)
        .toUpperCase();
}
</script>

<template>
    <Head title="Équipe" />

    <AppLayout title="Équipe" breadcrumb="Gestion des utilisateurs">
        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <StatCard
                icon="group"
                label="Total membres"
                :value="stats.total"
                color="primary"
            />
            <StatCard
                icon="admin_panel_settings"
                label="Administrateurs"
                :value="stats.admins"
                color="red"
            />
            <StatCard
                icon="engineering"
                label="Chefs de projet"
                :value="stats.chefs"
                color="violet"
            />
            <StatCard
                icon="person"
                label="Collaborateurs"
                :value="stats.collaborateurs"
                color="emerald"
            />
        </div>

        <!-- Tableau membres -->
        <div class="card overflow-hidden">
            <div
                class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800/60"
            >
                <div class="flex items-center gap-2.5">
                    <div class="card-icon bg-primary-50 dark:bg-primary-600/10">
                        <span
                            class="material-symbols-outlined text-primary-600 dark:text-primary-400 text-[16px]"
                            style="font-variation-settings: &quot;FILL&quot; 1"
                            >groups</span
                        >
                    </div>
                    <h4
                        class="font-headline font-bold text-gray-900 dark:text-white text-[13.5px]"
                    >
                        Membres de l'équipe
                    </h4>
                </div>
                <Link
                    href="/team/create"
                    class="flex items-center gap-1.5 px-3.5 py-2 bg-violet-600 hover:bg-violet-700 text-white text-[12px] font-semibold rounded-xl shadow-sm shadow-violet-600/20 transition-all active:scale-95"
                >
                    <span class="material-symbols-outlined text-[16px]"
                        >person_add</span
                    >
                    Ajouter un membre
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr
                            class="border-b border-gray-50 dark:border-gray-800/50 text-[10.5px] font-bold uppercase tracking-[0.08em] text-gray-400"
                        >
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Membre
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Rôle
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden text-center"
                            >
                                Projets
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden text-center"
                            >
                                Dépenses
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Statut
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden"
                            >
                                Depuis
                            </th>
                            <th
                                class="px-5 py-3 bg-primary-50 dark:bg-primary-600/10 text-primary-600 dark:text-primary-400 relative overflow-hidden w-28"
                            ></th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-50 dark:divide-gray-800/40"
                    >
                        <tr
                            v-for="member in members"
                            :key="member.id"
                            class="hover:bg-gray-50/70 dark:hover:bg-white/[0.02] transition-colors group"
                        >
                            <!-- Avatar + nom -->
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-[34px] h-[34px] rounded-full overflow-hidden bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center flex-shrink-0 ring-2 ring-primary-600/10"
                                    >
                                        <img
                                            v-if="member.avatar"
                                            :src="member.avatar"
                                            :alt="member.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <span
                                            v-else
                                            class="text-primary-600 text-[11px] font-extrabold font-headline"
                                        >
                                            {{ initials(member.name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p
                                            class="text-[12.5px] font-semibold text-gray-900 dark:text-white leading-tight"
                                        >
                                            {{ member.name }}
                                        </p>
                                        <p class="text-[11px] text-gray-400">
                                            {{ member.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Rôle — modifiable inline -->
                            <td class="px-5 py-3.5">
                                <select
                                    :value="member.role"
                                    @change="
                                        updateRole(
                                            member.id,
                                            $event.target.value,
                                        )
                                    "
                                    :disabled="
                                        member.id === $page.props.auth.user?.id
                                    "
                                    :class="[
                                        'text-[11px] font-bold px-2.5 py-1 rounded-full border-0 outline-none cursor-pointer transition-all',
                                        'disabled:opacity-50 disabled:cursor-not-allowed',
                                        roles[member.role]?.color ??
                                            'bg-gray-100 text-gray-500',
                                    ]"
                                >
                                    <option value="admin">
                                        Administrateur
                                    </option>
                                    <option value="project_manager">
                                        Chef de projet
                                    </option>
                                    <option value="staff_member">
                                        Collaborateur
                                    </option>
                                </select>
                            </td>

                            <!-- Projets -->
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="font-headline font-bold text-[13px] text-gray-900 dark:text-white"
                                >
                                    {{ member.projects_count }}
                                </span>
                            </td>

                            <!-- Dépenses -->
                            <td class="px-5 py-3.5 text-center">
                                <span
                                    class="font-headline font-bold text-[13px] text-gray-900 dark:text-white"
                                >
                                    {{ member.expenses_count }}
                                </span>
                            </td>

                            <!-- Email vérifié -->
                            <td class="px-5 py-3.5">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 px-2 py-[3px] rounded-full text-[10px] font-bold',
                                        member.email_verified
                                            ? 'bg-emerald-50 dark:bg-emerald-600/10 text-emerald-600 dark:text-emerald-400'
                                            : 'bg-amber-50 dark:bg-amber-600/10 text-amber-600 dark:text-amber-400',
                                    ]"
                                >
                                    <span
                                        class="material-symbols-outlined text-[11px]"
                                        style="
                                            font-variation-settings: &quot;FILL&quot;
                                                1;
                                        "
                                    >
                                        {{
                                            member.email_verified
                                                ? "verified"
                                                : "schedule"
                                        }}
                                    </span>
                                    {{
                                        member.email_verified
                                            ? "Vérifié"
                                            : "En attente"
                                    }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td
                                class="px-5 py-3.5 text-[11px] text-gray-400 font-mono"
                            >
                                {{ member.created_at }}
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-3.5">
                                <div
                                    class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <Link
                                        :href="`/team/${member.id}/edit`"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-600/10 transition-all"
                                        title="Modifier"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[16px]"
                                            >edit</span
                                        >
                                    </Link>
                                    <button
                                        v-if="
                                            member.id !==
                                            $page.props.auth.user?.id
                                        "
                                        @click="confirmDelete(member)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-600/10 transition-all"
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

                        <tr v-if="!members.length">
                            <td colspan="7" class="px-5 py-16 text-center">
                                <span
                                    class="material-symbols-outlined text-gray-200 dark:text-gray-800 text-[40px]"
                                    >group_off</span
                                >
                                <p class="text-[12px] text-gray-400 mt-2">
                                    Aucun membre
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal suppression -->
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
                        class="relative bg-white dark:bg-[#111318] rounded-2xl shadow-2xl p-6 w-full max-w-sm border border-gray-100 dark:border-gray-800/70"
                    >
                        <h3
                            class="font-headline font-bold text-gray-900 dark:text-white mb-2"
                        >
                            Supprimer ce membre ?
                        </h3>
                        <p class="text-[12.5px] text-gray-500 mb-5">
                            Le compte de
                            <span
                                class="font-semibold text-gray-700 dark:text-gray-300"
                                >{{ deleteModal.name }}</span
                            >
                            sera définitivement supprimé.
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="deleteModal.show = false"
                                class="px-4 py-2 text-[12.5px] font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 transition-all"
                            >
                                Annuler
                            </button>
                            <button
                                @click="executeDelete"
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
.card-icon {
    @apply w-[30px] h-[30px] rounded-lg flex items-center justify-center flex-shrink-0;
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
