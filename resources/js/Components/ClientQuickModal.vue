<script setup>
/**
 * resources/js/Components/ClientQuickModal.vue
 *
 * Modal de création rapide d'un client.
 * Utilisé depuis Projects/Form.vue (et partout où on a un select client).
 *
 * Émit 'created' avec le nouveau client quand la création réussit,
 * pour que le parent l'ajoute dans sa liste et le sélectionne.
 *
 * Usage :
 *   <ClientQuickModal v-model="showClientModal" @created="onClientCreated" />
 */
import { reactive, watch } from "vue";
import axios from "axios";

const props = defineProps({
    modelValue: { type: Boolean, required: true }, // v-model = show/hide
});

const emit = defineEmits(["update:modelValue", "created"]);

// ── Formulaire local (pas useForm — on fait un appel axios direct) ──
const form = reactive({
    name: "",
    email: "",
    phone: "",
    contact_person: "",
    address: "",
    city: "",
    country: "Côte d'Ivoire",
});

const errors = reactive({});
const loading = reactive({ value: false });
const submitted = reactive({ value: false });

// Réinitialiser à chaque ouverture
watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            Object.assign(form, {
                name: "",
                email: "",
                phone: "",
                contact_person: "",
                address: "",
                city: "",
                country: "Côte d'Ivoire",
            });
            Object.keys(errors).forEach((k) => delete errors[k]);
            submitted.value = false;
            loading.value = false;
        }
    },
);

// ── Soumission ───────────────────────────────────────────────
async function submit() {
    loading.value = true;
    Object.keys(errors).forEach((k) => delete errors[k]);

    try {
        const res = await axios.post("/clients/quick-create", form);
        submitted.value = true;

        // Émettre le client créé vers le parent
        emit("created", res.data.client);

        // Fermer le modal après un court délai (feedback visuel)
        setTimeout(() => close(), 800);
    } catch (err) {
        if (err.response?.status === 422) {
            // Erreurs de validation Laravel
            const serverErrors = err.response.data.errors ?? {};
            Object.assign(errors, serverErrors);
            // Transformer 'name.0' → message string
            Object.keys(errors).forEach((k) => {
                if (Array.isArray(errors[k])) errors[k] = errors[k][0];
            });
        } else {
            errors.name = "Une erreur est survenue. Veuillez réessayer.";
        }
    } finally {
        loading.value = false;
    }
}

function close() {
    emit("update:modelValue", false);
}

// ── Helpers classes ──────────────────────────────────────────
function inputCls(field) {
    return [
        "w-full px-4 py-2.5 rounded-xl border text-[13px] outline-none transition-all",
        "bg-white dark:bg-gray-800/60 text-gray-900 dark:text-white",
        "placeholder:text-gray-400 dark:placeholder:text-gray-600",
        errors[field]
            ? "border-red-400 dark:border-red-500 focus:ring-2 focus:ring-red-400/20"
            : "border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-primary-600/20 focus:border-primary-600/40",
    ];
}
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-backdrop">
            <div
                v-if="modelValue"
                class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4"
                @click.self="close"
            >
                <!-- Fond -->
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                ></div>

                <!-- Panneau -->
                <Transition name="modal-panel">
                    <div
                        v-if="modelValue"
                        class="relative w-full sm:max-w-lg bg-white dark:bg-[#111318] rounded-t-3xl sm:rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800/70 overflow-hidden z-10"
                    >
                        <!-- En-tête -->
                        <div
                            class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800/60"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-600/10 flex items-center justify-center"
                                >
                                    <span
                                        class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[19px]"
                                        style="
                                            font-variation-settings: &quot;FILL&quot;
                                                1;
                                        "
                                        >add_business</span
                                    >
                                </div>
                                <div>
                                    <h3
                                        class="font-headline font-bold text-gray-900 dark:text-white text-[14px] leading-tight"
                                    >
                                        Nouveau client
                                    </h3>
                                    <p
                                        class="text-[11px] text-gray-400 leading-tight"
                                    >
                                        Création rapide depuis le formulaire
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="close"
                                class="w-8 h-8 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all"
                            >
                                <span
                                    class="material-symbols-outlined text-[19px]"
                                    >close</span
                                >
                            </button>
                        </div>

                        <!-- ── Succès ── -->
                        <Transition name="flash">
                            <div
                                v-if="submitted.value"
                                class="mx-6 mt-5 flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200/70 dark:border-emerald-800/50 rounded-xl text-emerald-700 dark:text-emerald-400 text-[12.5px] font-medium"
                            >
                                <span
                                    class="material-symbols-outlined text-[18px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >check_circle</span
                                >
                                Client créé et sélectionné automatiquement !
                            </div>
                        </Transition>

                        <!-- Corps -->
                        <form
                            @submit.prevent="submit"
                            class="px-6 py-5 space-y-4 max-h-[65vh] overflow-y-auto"
                        >
                            <!-- Nom — champ obligatoire -->
                            <div>
                                <label class="field-label">
                                    Nom / Raison sociale *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="ex : Orange Côte d'Ivoire"
                                    :class="inputCls('name')"
                                    autofocus
                                />
                                <p v-if="errors.name" class="field-error">
                                    <span
                                        class="material-symbols-outlined text-[13px]"
                                        >error</span
                                    >
                                    {{ errors.name }}
                                </p>
                            </div>

                            <!-- Contact + Email -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="field-label"
                                        >Responsable contact</label
                                    >
                                    <input
                                        v-model="form.contact_person"
                                        type="text"
                                        placeholder="Prénom Nom"
                                        :class="inputCls('contact_person')"
                                    />
                                    <p
                                        v-if="errors.contact_person"
                                        class="field-error"
                                    >
                                        {{ errors.contact_person }}
                                    </p>
                                </div>
                                <div>
                                    <label class="field-label">Email</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="contact@client.ci"
                                        :class="inputCls('email')"
                                    />
                                    <p v-if="errors.email" class="field-error">
                                        {{ errors.email }}
                                    </p>
                                </div>
                            </div>

                            <!-- Téléphone + Ville -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="field-label">Téléphone</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        placeholder="+225 07 00 00 00 00"
                                        :class="inputCls('phone')"
                                    />
                                    <p v-if="errors.phone" class="field-error">
                                        {{ errors.phone }}
                                    </p>
                                </div>
                                <div>
                                    <label class="field-label">Ville</label>
                                    <input
                                        v-model="form.city"
                                        type="text"
                                        placeholder="Abidjan"
                                        :class="inputCls('city')"
                                    />
                                    <p v-if="errors.city" class="field-error">
                                        {{ errors.city }}
                                    </p>
                                </div>
                            </div>

                            <!-- Adresse + Pays (collapsed par défaut) -->
                            <details class="group">
                                <summary
                                    class="flex items-center gap-1.5 cursor-pointer text-[11px] font-semibold text-gray-400 dark:text-gray-600 hover:text-gray-600 dark:hover:text-gray-400 list-none select-none transition-colors"
                                >
                                    <span
                                        class="material-symbols-outlined text-[15px] transition-transform group-open:rotate-90"
                                    >
                                        chevron_right
                                    </span>
                                    Informations supplémentaires (adresse, pays)
                                </summary>

                                <div class="mt-3 space-y-4 pl-5">
                                    <div>
                                        <label class="field-label"
                                            >Adresse</label
                                        >
                                        <input
                                            v-model="form.address"
                                            type="text"
                                            placeholder="Rue, Quartier..."
                                            :class="inputCls('address')"
                                        />
                                    </div>
                                    <div>
                                        <label class="field-label">Pays</label>
                                        <input
                                            v-model="form.country"
                                            type="text"
                                            placeholder="Côte d'Ivoire"
                                            :class="inputCls('country')"
                                        />
                                    </div>
                                </div>
                            </details>

                            <!-- Info sur les champs optionnels -->
                            <p
                                class="text-[10.5px] text-gray-400 flex items-start gap-1.5 pt-1"
                            >
                                <span
                                    class="material-symbols-outlined text-[13px] flex-shrink-0 mt-[1px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >info</span
                                >
                                Seul le nom est obligatoire. Vous pourrez
                                compléter le profil client plus tard.
                            </p>
                        </form>

                        <!-- Pied de page -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-t border-gray-100 dark:border-gray-800/60 bg-gray-50/50 dark:bg-white/[0.015]"
                        >
                            <button
                                type="button"
                                @click="close"
                                class="px-4 py-2 text-[12.5px] bg-blue-600 hover:bg-blue-700 font-semibold text-white rounded-lg shadow-sm shadow-blue-500/20 transition-all active:scale-95"
                            >
                                Annuler
                            </button>
                            <button
                                type="button"
                                @click="submit"
                                :disabled="loading.value || submitted.value"
                                class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[12.5px] font-bold rounded-xl shadow-sm shadow-emerald-600/20 transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span
                                    v-if="loading.value"
                                    class="material-symbols-outlined text-[17px] animate-spin"
                                    >progress_activity</span
                                >
                                <span
                                    v-else-if="submitted.value"
                                    class="material-symbols-outlined text-[17px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >check_circle</span
                                >
                                <span
                                    v-else
                                    class="material-symbols-outlined text-[17px]"
                                    style="
                                        font-variation-settings: &quot;FILL&quot;
                                            1;
                                    "
                                    >add_business</span
                                >
                                {{
                                    submitted.value
                                        ? "Client ajouté !"
                                        : loading.value
                                          ? "Création..."
                                          : "Créer le client"
                                }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.field-label {
    @apply block text-[10.5px] font-bold uppercase tracking-[0.1em]
           text-gray-500 dark:text-gray-500 mb-1.5;
}
.field-error {
    @apply mt-1.5 text-[11px] text-red-500 flex items-center gap-1;
}

/* Backdrop */
.modal-backdrop-enter-active,
.modal-backdrop-leave-active {
    transition: opacity 0.25s ease;
}
.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
    opacity: 0;
}

/* Panel (slide up) */
.modal-panel-enter-active,
.modal-panel-leave-active {
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-panel-enter-from,
.modal-panel-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.97);
}

/* Flash succès */
.flash-enter-active,
.flash-leave-active {
    transition: all 0.3s ease;
}
.flash-enter-from,
.flash-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
