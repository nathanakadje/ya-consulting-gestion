<template>
    <!-- resources/js/Components/ExpenseModal.vue
         Modal de création ET modification de dépense.
         Usage:
           <ExpenseModal
             v-model="showModal"
             :project-id="project.id"
             :categories="categories"
             :expense="expenseToEdit"   ← null = création, objet = édition
           />
    -->
    <Teleport to="body">
        <Transition name="modal-backdrop">
            <div
                v-if="modelValue"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
                @click.self="close"
            >
                <!-- Fond assombri -->
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                ></div>

                <!-- Panneau modal -->
                <Transition name="modal-panel">
                    <div
                        v-if="modelValue"
                        class="relative w-full sm:max-w-lg bg-white dark:bg-gray-900 rounded-t-3xl sm:rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden"
                    >
                        <!-- En-tête modal -->
                        <div
                            class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center"
                                >
                                    <span
                                        class="material-symbols-outlined text-primary-600 dark:text-primary-400 text-[20px]"
                                    >
                                        {{ isEditing ? "edit" : "add_circle" }}
                                    </span>
                                </div>
                                <div>
                                    <h3
                                        class="font-headline font-bold text-gray-900 dark:text-white"
                                    >
                                        {{
                                            isEditing
                                                ? "Modifier la dépense"
                                                : "Nouvelle dépense"
                                        }}
                                    </h3>
                                    <p class="text-xs text-gray-400">
                                        {{ projectName }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="close"
                                class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all"
                            >
                                <span
                                    class="material-symbols-outlined text-[20px]"
                                    >close</span
                                >
                            </button>
                        </div>

                        <!-- Corps du formulaire -->
                        <form
                            @submit.prevent="submit"
                            class="p-6 space-y-5 max-h-[70vh] overflow-y-auto"
                        >
                            <!-- Description -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5"
                                >
                                    Description *
                                </label>
                                <input
                                    v-model="form.description"
                                    type="text"
                                    placeholder="ex: Achat câblage réseau"
                                    :class="inputClass(form.errors.description)"
                                />
                                <p
                                    v-if="form.errors.description"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Montant + Date (côte à côte) -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5"
                                    >
                                        Montant (FCFA) *
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="form.amount"
                                            type="number"
                                            min="1"
                                            step="100"
                                            placeholder="0"
                                            :class="[
                                                inputClass(form.errors.amount),
                                                'pr-14',
                                            ]"
                                        />
                                        <span
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400"
                                            >FCFA</span
                                        >
                                    </div>
                                    <p
                                        v-if="form.errors.amount"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ form.errors.amount }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5"
                                    >
                                        Date *
                                    </label>
                                    <input
                                        v-model="form.expense_date"
                                        type="date"
                                        :class="
                                            inputClass(form.errors.expense_date)
                                        "
                                    />
                                    <p
                                        v-if="form.errors.expense_date"
                                        class="mt-1 text-xs text-red-500"
                                    >
                                        {{ form.errors.expense_date }}
                                    </p>
                                </div>
                            </div>

                            <!-- Catégorie -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5"
                                >
                                    Catégorie *
                                </label>
                                <!-- Sélection visuelle par boutons -->
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        type="button"
                                        @click="form.category_id = cat.id"
                                        :class="[
                                            'flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs font-semibold border-2 transition-all text-left',
                                            form.category_id == cat.id
                                                ? 'border-current shadow-sm'
                                                : 'border-gray-100 dark:border-gray-800 text-gray-500 dark:text-gray-400 hover:border-gray-200 dark:hover:border-gray-700',
                                        ]"
                                        :style="
                                            form.category_id == cat.id
                                                ? {
                                                      borderColor: cat.color,
                                                      color: cat.color,
                                                      backgroundColor:
                                                          cat.color + '12',
                                                  }
                                                : {}
                                        "
                                    >
                                        <span
                                            class="material-symbols-outlined text-[16px]"
                                            >{{ cat.icon ?? "receipt" }}</span
                                        >
                                        {{ cat.name }}
                                    </button>
                                </div>
                                <p
                                    v-if="form.errors.category_id"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5"
                                >
                                    Notes
                                    <span class="normal-case font-normal"
                                        >(optionnel)</span
                                    >
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    rows="2"
                                    placeholder="Informations complémentaires..."
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/30 transition-all resize-none placeholder:text-gray-400"
                                ></textarea>
                            </div>

                            <!-- Upload justificatif -->
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1.5"
                                >
                                    Justificatif
                                    <span class="normal-case font-normal"
                                        >(PDF, JPG, PNG — max 5 Mo)</span
                                    >
                                </label>

                                <!-- Justificatif existant (mode édition) -->
                                <div
                                    v-if="
                                        isEditing &&
                                        expense?.receipt_path &&
                                        !removeReceipt
                                    "
                                    class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl mb-2"
                                >
                                    <span
                                        class="material-symbols-outlined text-emerald-600 text-[20px]"
                                        >attach_file</span
                                    >
                                    <span
                                        class="text-sm text-emerald-700 dark:text-emerald-400 flex-1 truncate font-medium"
                                    >
                                        Justificatif existant
                                    </span>
                                    <button
                                        type="button"
                                        @click="
                                            removeReceipt = true;
                                            form.remove_receipt = true;
                                        "
                                        class="text-xs text-red-400 hover:text-red-600 font-semibold transition-colors"
                                    >
                                        Supprimer
                                    </button>
                                </div>

                                <!-- Zone de dépôt de fichier -->
                                <div
                                    v-if="
                                        !isEditing ||
                                        removeReceipt ||
                                        !expense?.receipt_path
                                    "
                                    @dragover.prevent="isDragging = true"
                                    @dragleave="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    @click="$refs.fileInput.click()"
                                    :class="[
                                        'border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all',
                                        isDragging
                                            ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20'
                                            : selectedFile
                                              ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20'
                                              : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-gray-50 dark:hover:bg-gray-800/50',
                                    ]"
                                >
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept=".pdf,.jpg,.jpeg,.png,.webp"
                                        class="hidden"
                                        @change="handleFileSelect"
                                    />

                                    <!-- Fichier sélectionné -->
                                    <div
                                        v-if="selectedFile"
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <span
                                            class="material-symbols-outlined text-emerald-600 text-[20px]"
                                            >check_circle</span
                                        >
                                        <span
                                            class="text-sm font-semibold text-emerald-700 dark:text-emerald-400 truncate max-w-xs"
                                        >
                                            {{ selectedFile.name }}
                                        </span>
                                        <span class="text-xs text-gray-400"
                                            >({{
                                                formatFileSize(
                                                    selectedFile.size,
                                                )
                                            }})</span
                                        >
                                        <button
                                            type="button"
                                            @click.stop="clearFile"
                                            class="text-gray-400 hover:text-red-400 ml-1 transition-colors"
                                        >
                                            <span
                                                class="material-symbols-outlined text-[16px]"
                                                >close</span
                                            >
                                        </button>
                                    </div>

                                    <!-- Pas de fichier -->
                                    <div v-else>
                                        <span
                                            class="material-symbols-outlined text-gray-300 dark:text-gray-700 text-[32px] block mb-1"
                                            >upload_file</span
                                        >
                                        <p class="text-sm text-gray-400">
                                            <span
                                                class="text-primary-600 dark:text-primary-400 font-semibold"
                                                >Cliquez</span
                                            >
                                            ou glissez un fichier ici
                                        </p>
                                    </div>
                                </div>
                                <p
                                    v-if="form.errors.receipt"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.receipt }}
                                </p>
                            </div>
                        </form>

                        <!-- Pied de page modal -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30"
                        >
                            <button
                                type="button"
                                @click="close"
                                class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                            >
                                Annuler
                            </button>

                            <!-- Montant affiché en temps réel -->
                            <div
                                v-if="form.amount"
                                class="text-sm font-bold text-primary-600 dark:text-primary-400 font-mono"
                            >
                                {{ formatCurrency(form.amount) }}
                            </div>

                            <button
                                type="button"
                                @click="submit"
                                :disabled="form.processing"
                                class="flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-xl shadow-sm shadow-primary-600/20 transition-all active:scale-95 disabled:opacity-60"
                            >
                                <span
                                    v-if="form.processing"
                                    class="material-symbols-outlined text-[18px] animate-spin"
                                    >progress_activity</span
                                >
                                <span
                                    v-else
                                    class="material-symbols-outlined text-[18px]"
                                    >{{ isEditing ? "save" : "add" }}</span
                                >
                                {{
                                    isEditing
                                        ? "Enregistrer"
                                        : "Ajouter la dépense"
                                }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

// ── Props ────────────────────────────────────────────────────
const props = defineProps({
    modelValue: { type: Boolean, required: true }, // v-model (show/hide)
    projectId: { type: [Number, String], required: true },
    projectName: { type: String, default: "" },
    categories: { type: Array, default: () => [] },
    expense: { type: Object, default: null }, // null = création
});

const emit = defineEmits(["update:modelValue"]);

const isEditing = computed(() => !!props.expense?.id);
const fileInput = ref(null);
const isDragging = ref(false);
const selectedFile = ref(null);
const removeReceipt = ref(false);

// ── Formulaire Inertia ────────────────────────────────────────
const form = useForm({
    project_id: props.projectId,
    category_id: props.expense?.category?.id ?? "",
    description: props.expense?.description ?? "",
    amount: props.expense?.amount ?? "",
    expense_date:
        props.expense?.expense_date_raw ??
        new Date().toISOString().split("T")[0],
    notes: props.expense?.notes ?? "",
    receipt: null,
    remove_receipt: false,
    status: props.expense?.status ?? "validated",
});

// Réinitialiser quand le modal s'ouvre avec une nouvelle dépense
watch(
    () => props.expense,
    (newVal) => {
        form.reset();
        selectedFile.value = null;
        removeReceipt.value = false;

        if (newVal) {
            form.category_id = newVal.category?.id ?? "";
            form.description = newVal.description ?? "";
            form.amount = newVal.amount ?? "";
            form.expense_date = newVal.expense_date_raw ?? "";
            form.notes = newVal.notes ?? "";
            form.status = newVal.status ?? "validated";
        }
    },
);

watch(
    () => props.modelValue,
    (val) => {
        if (!val) {
            form.reset();
            selectedFile.value = null;
            removeReceipt.value = false;
        }
    },
);

// ── Submit ────────────────────────────────────────────────────
// function submit() {
//     // Attacher le fichier s'il y en a un
//     if (selectedFile.value) {
//         form.receipt = selectedFile.value;
//     }

//     if (isEditing.value) {
//         // PUT /expenses/{id}
//         form.post(`/expenses/${props.expense.id}`, {
//             method: "put",
//             forceFormData: true, // nécessaire pour l'upload fichier
//             onSuccess: () => close(),
//         });
//     } else {
//         // POST /expenses
//         form.post("/expenses", {
//             forceFormData: true,
//             onSuccess: () => close(),
//         });
//     }
// }
function submit() {
    // Attacher le fichier s'il y en a un
    if (selectedFile.value) {
        form.receipt = selectedFile.value;
    }

    if (isEditing.value) {
        // Pour une modification avec upload de fichier :
        // On utilise POST mais on injecte _method: 'put'
        form.transform((data) => ({
            ...data,
            _method: "put",
        })).post(`/expenses/${props.expense.id}`, {
            forceFormData: true,
            onSuccess: () => close(),
        });
    } else {
        // Pour une création classique
        form.post("/expenses", {
            forceFormData: true,
            onSuccess: () => close(),
        });
    }
}

// ── Gestion fichier ───────────────────────────────────────────
function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) validateAndSetFile(file);
}

function handleDrop(event) {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    if (file) validateAndSetFile(file);
}

function validateAndSetFile(file) {
    const maxSize = 5 * 1024 * 1024; // 5 Mo
    const allowed = [
        "application/pdf",
        "image/jpeg",
        "image/png",
        "image/webp",
    ];

    if (file.size > maxSize) {
        alert("Le fichier ne doit pas dépasser 5 Mo.");
        return;
    }
    if (!allowed.includes(file.type)) {
        alert("Format accepté : PDF, JPG, PNG, WEBP.");
        return;
    }
    selectedFile.value = file;
}

function clearFile() {
    selectedFile.value = null;
    if (fileInput.value) fileInput.value.value = "";
}

// ── Utilitaires ───────────────────────────────────────────────
function close() {
    emit("update:modelValue", false);
}

function inputClass(error) {
    return [
        "w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition-all",
        "bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400",
        error
            ? "border-red-400 focus:ring-2 focus:ring-red-400/20"
            : "border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/30",
    ];
}

function formatCurrency(amount) {
    return new Intl.NumberFormat("fr-FR").format(amount ?? 0) + " FCFA";
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + " o";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + " Ko";
    return (bytes / (1024 * 1024)).toFixed(1) + " Mo";
}
</script>

<style scoped>
/* Backdrop */
.modal-backdrop-enter-active,
.modal-backdrop-leave-active {
    transition: opacity 0.25s ease;
}
.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
    opacity: 0;
}

/* Panel (monte depuis le bas sur mobile, apparaît au centre sur desktop) */
.modal-panel-enter-active,
.modal-panel-leave-active {
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-panel-enter-from,
.modal-panel-leave-to {
    opacity: 0;
    transform: translateY(24px) scale(0.97);
}
</style>
