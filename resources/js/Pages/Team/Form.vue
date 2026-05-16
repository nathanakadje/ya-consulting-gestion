<script setup>
// resources/js/Pages/Team/Form.vue
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    member: { type: Object, default: null },
});

// Fonction utilitaire pour les classes d'input (réutilisée dans le template)
function fieldClass(error) {
    return [
        "w-full rounded-xl border px-4 py-2.5 text-[12.5px] outline-none transition-all",
        "bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400",
        error
            ? "border-red-400 focus:ring-2 focus:ring-red-400/20"
            : "border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/30",
    ];
}

const isEditing = computed(() => !!props.member?.id);

const form = useForm({
    name: props.member?.name ?? "",
    email: props.member?.email ?? "",
    role: props.member?.role ?? "collaborateur",
    password: "",
    password_confirmation: "",
});

function submit() {
    if (isEditing.value) {
        form.put(`/gestion/team/${props.member.id}`);
    } else {
        form.post("/gestion/team");
    }
}

const roles = [
    {
        value: "admin",
        label: "Administrateur",
        desc: "Accès total : projets, utilisateurs, rapports",
        icon: "admin_panel_settings",
        color: "text-red-600 dark:text-red-400",
        bg: "bg-red-50 dark:bg-red-600/10",
        border: "border-red-400 dark:border-red-500",
    },
    {
        value: "project_manager",
        label: "Chef de projet",
        desc: "Créer/modifier projets, ajouter dépenses, voir ses stats",
        icon: "engineering",
        color: "text-primary-600 dark:text-primary-400",
        bg: "bg-primary-50 dark:bg-primary-600/10",
        border: "border-primary-500",
    },
    {
        value: "staff_member",
        label: "Collaborateur",
        desc: "Consultation uniquement : dépenses et statuts",
        icon: "person",
        color: "text-amber-600 dark:text-amber-400",
        bg: "bg-amber-50 dark:bg-amber-800",
        border: "border-amber-400 dark:border-amber-500",
    },
];
</script>

<template>
    <Head :title="isEditing ? 'Modifier le membre' : 'Ajouter un membre'" />

    <AppLayout
        :title="isEditing ? 'Modifier le membre' : 'Ajouter un membre'"
        :breadcrumb="`Équipe / ${isEditing ? member.name : 'Nouveau'}`"
    >
        <div class="max-w-2xl mx-auto">
            <!-- Retour -->
            <div class="flex items-center gap-3 mb-7">
                <Link
                    href="/gestion/team"
                    class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all"
                >
                    <span class="material-symbols-outlined">arrow_back</span>
                </Link>
                <div>
                    <h3
                        class="font-headline font-bold text-[18px] text-gray-900 dark:text-white"
                    >
                        {{
                            isEditing
                                ? `Modifier « ${member.name} »`
                                : "Nouveau membre"
                        }}
                    </h3>
                    <p class="text-[12px] text-gray-400 mt-0.5">
                        {{
                            isEditing
                                ? "Modifiez les informations du compte."
                                : "Créez un nouveau compte utilisateur."
                        }}
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Infos de base -->
                <div class="card p-6 space-y-5">
                    <p class="label-section mb-1">Informations du compte</p>

                    <div>
                        <label class="field-label">Nom complet *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Prénom Nom"
                            :class="fieldClass(form.errors.name)"
                        />
                        <p v-if="form.errors.name" class="field-error">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="field-label">Adresse email *</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="prenom.nom@ya-consulting.ci"
                            :class="fieldClass(form.errors.email)"
                        />
                        <p v-if="form.errors.email" class="field-error">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">
                                {{
                                    isEditing
                                        ? "Nouveau mot de passe"
                                        : "Mot de passe *"
                                }}
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                :placeholder="
                                    isEditing
                                        ? 'Laisser vide pour ne pas changer'
                                        : 'Min. 8 caractères'
                                "
                                :class="fieldClass(form.errors.password)"
                            />
                            <p v-if="form.errors.password" class="field-error">
                                {{ form.errors.password }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label"
                                >Confirmer le mot de passe</label
                            >
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Répéter le mot de passe"
                                :class="fieldClass(null)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Rôle -->
                <div class="card p-6">
                    <p class="label-section mb-4">Rôle et permissions</p>
                    <div class="space-y-3">
                        <button
                            v-for="r in roles"
                            :key="r.value"
                            type="button"
                            @click="form.role = r.value"
                            :class="[
                                'w-full flex items-center gap-4 p-4 rounded-xl border-2 text-left transition-all',
                                form.role === r.value
                                    ? `${r.border} ${r.bg}`
                                    : 'border-gray-100 dark:border-gray-800 hover:border-gray-200 dark:hover:border-gray-700 bg-white dark:bg-[#111318]',
                            ]"
                        >
                            <div
                                :class="[
                                    'w-[38px] h-[38px] rounded-xl flex items-center justify-center flex-shrink-0',
                                    form.role === r.value
                                        ? r.bg
                                        : 'bg-gray-50 dark:bg-gray-800',
                                ]"
                            >
                                <span
                                    class="material-symbols-outlined text-[20px]"
                                    :class="
                                        form.role === r.value
                                            ? r.color
                                            : 'text-gray-400'
                                    "
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                >
                                    {{ r.icon }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <p
                                    :class="[
                                        'text-[13px] font-bold leading-tight',
                                        form.role === r.value
                                            ? r.color
                                            : 'text-gray-800 dark:text-gray-200',
                                    ]"
                                >
                                    {{ r.label }}
                                </p>
                                <p class="text-[11px] text-gray-400 mt-[2px]">
                                    {{ r.desc }}
                                </p>
                            </div>
                            <span
                                v-if="form.role === r.value"
                                class="material-symbols-outlined text-[18px] flex-shrink-0"
                                :class="r.color"
                                style="
                                    font-variation-settings: &quot;FILL&quot; 1;
                                "
                                >check_circle</span
                            >
                        </button>
                    </div>
                    <p v-if="form.errors.role" class="field-error mt-2">
                        {{ form.errors.role }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-1">
                    <Link
                        href="/gestion/team"
                        class="px-5 py-2.5 text-[12.5px] font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-[12.5px] font-semibold rounded-xl shadow-sm shadow-primary-600/20 transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
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
                            {{ isEditing ? "save" : "person_add" }}
                        </span>
                        {{ isEditing ? "Enregistrer" : "Créer le compte" }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.card {
    @apply bg-white dark:bg-[#111318] rounded-2xl border border-gray-100 dark:border-gray-800/70 shadow-[0_1px_3px_rgba(0,0,0,0.04),0_4px_12px_rgba(0,0,0,0.03)];
}
.label-section {
    @apply text-[10.5px] font-bold uppercase tracking-[0.12em] text-gray-400 dark:text-gray-600;
}
.field-label {
    @apply block text-[10.5px] font-bold uppercase tracking-[0.1em] text-gray-500 mb-1.5;
}
.field-error {
    @apply mt-1.5 text-[11px] text-red-500 flex items-center gap-1;
}
</style>
