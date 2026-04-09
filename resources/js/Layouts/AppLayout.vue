<template>
    <!-- resources/js/Layouts/AppLayout.vue -->
    <!-- Layout principal partagé par toutes les pages après login -->

    <div
        class="min-h-screen bg-gray-50 dark:bg-gray-950 font-body transition-colors duration-300"
    >
        <!-- ══════════════════════════════════════════
             SIDEBAR
        ═══════════════════════════════════════════ -->
        <aside
            :class="[
                'fixed left-0 top-0 h-screen z-50 flex flex-col transition-all duration-300 ease-in-out',
                sidebarOpen ? 'w-64' : 'w-16',
                'bg-white dark:bg-gray-900 border-r border-gray-100 dark:border-gray-800 shadow-sm',
            ]"
        >
            <!-- Logo -->
            <div
                class="flex items-center gap-3 px-4 py-5 border-b border-gray-100 dark:border-gray-800 overflow-hidden"
            >
                <div
                    class="w-9 h-9 rounded-xl bg-primary-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary-600/30"
                >
                    <span
                        class="material-symbols-outlined text-white text-[20px]"
                        >trending_up</span
                    >
                </div>
                <div
                    v-show="sidebarOpen"
                    class="overflow-hidden transition-all duration-200"
                >
                    <h1
                        class="font-headline font-extrabold text-gray-900 dark:text-white text-base leading-tight whitespace-nowrap"
                    >
                        Ya Consulting
                    </h1>
                    <p
                        class="text-[9px] uppercase tracking-[0.2em] text-gray-400 font-semibold"
                    >
                        Gestion de projets
                    </p>
                </div>
            </div>

            <!-- Navigation principale -->
            <nav class="flex-1 py-4 px-2 space-y-1 overflow-y-auto">
                <NavItem
                    v-for="item in navItems"
                    :key="item.route"
                    :item="item"
                    :collapsed="!sidebarOpen"
                />
            </nav>

            <!-- Séparateur + bouton collapse -->
            <div
                class="p-3 border-t border-gray-100 dark:border-gray-800 space-y-2"
            >
                <!-- Toggle sidebar -->
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all group"
                    :title="sidebarOpen ? 'Réduire' : 'Agrandir'"
                >
                    <span
                        class="material-symbols-outlined text-[20px] transition-transform duration-300"
                        :class="sidebarOpen ? '' : 'rotate-180'"
                    >
                        chevron_left
                    </span>
                    <span v-show="sidebarOpen" class="text-xs font-medium"
                        >Réduire</span
                    >
                </button>
            </div>
        </aside>

        <!-- ══════════════════════════════════════════
             MAIN WRAPPER
        ═══════════════════════════════════════════ -->
        <div
            :class="[
                'transition-all duration-300 flex flex-col min-h-screen',
                sidebarOpen ? 'ml-64' : 'ml-16',
            ]"
        >
            <!-- ══════════════════════════════════════
                 TOP NAVIGATION BAR
            ═══════════════════════════════════════ -->
            <header
                class="sticky top-0 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-100/80 dark:border-gray-800/80 shadow-sm"
            >
                <div class="flex items-center justify-between px-6 h-16">
                    <!-- Titre de page + fil d'ariane -->
                    <div class="flex items-center gap-3">
                        <div>
                            <h2
                                class="font-headline font-bold text-gray-900 dark:text-white text-lg leading-tight"
                            >
                                {{ pageTitle }}
                            </h2>
                            <p
                                v-if="pageBreadcrumb"
                                class="text-xs text-gray-400 dark:text-gray-500"
                            >
                                {{ pageBreadcrumb }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions droite -->
                    <div class="flex items-center gap-2">
                        <!-- Toggle thème clair/sombre -->
                        <button
                            @click="toggleTheme"
                            class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all active:scale-95"
                            :title="isDark ? 'Mode clair' : 'Mode sombre'"
                        >
                            <span class="material-symbols-outlined text-[22px]">
                                {{ isDark ? "light_mode" : "dark_mode" }}
                            </span>
                        </button>

                        <!-- Notifications -->
                        <button
                            class="relative p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all active:scale-95"
                        >
                            <span class="material-symbols-outlined text-[22px]"
                                >notifications</span
                            >
                            <!-- Badge -->
                            <span
                                class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"
                            ></span>
                        </button>

                        <!-- Séparateur -->
                        <div
                            class="w-px h-8 bg-gray-100 dark:bg-gray-800 mx-1"
                        ></div>

                        <!-- Avatar + menu utilisateur -->
                        <div class="relative" ref="userMenuRef">
                            <button
                                @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2.5 pl-1 pr-3 py-1.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-all group"
                            >
                                <!-- Avatar -->
                                <div
                                    class="w-8 h-8 rounded-full overflow-hidden bg-primary-100 dark:bg-primary-900 flex items-center justify-center flex-shrink-0 ring-2 ring-primary-600/20"
                                >
                                    <img
                                        v-if="$page.props.auth.user?.avatar"
                                        :src="$page.props.auth.user.avatar"
                                        :alt="$page.props.auth.user?.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <span
                                        v-else
                                        class="text-primary-600 text-sm font-bold font-headline"
                                    >
                                        {{ userInitials }}
                                    </span>
                                </div>
                                <div class="text-left hidden sm:block">
                                    <p
                                        class="text-sm font-semibold text-gray-800 dark:text-gray-200 leading-tight"
                                    >
                                        {{ $page.props.auth.user?.name }}
                                    </p>
                                    <p
                                        class="text-[10px] text-gray-400 capitalize"
                                    >
                                        {{ roleLabel }}
                                    </p>
                                </div>
                                <span
                                    class="material-symbols-outlined text-gray-400 text-[18px] transition-transform"
                                    :class="userMenuOpen ? 'rotate-180' : ''"
                                >
                                    expand_more
                                </span>
                            </button>

                            <!-- Dropdown menu utilisateur -->
                            <Transition name="dropdown">
                                <div
                                    v-if="userMenuOpen"
                                    class="absolute right-0 top-full mt-2 w-52 bg-white dark:bg-gray-900 rounded-xl shadow-xl shadow-gray-200/50 dark:shadow-gray-900/80 border border-gray-100 dark:border-gray-800 py-1.5 z-50"
                                >
                                    <div
                                        class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-800"
                                    >
                                        <p
                                            class="text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                        >
                                            Mon compte
                                        </p>
                                    </div>
                                    <a
                                        href="/user/profile"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[18px] text-gray-400"
                                            >account_circle</span
                                        >
                                        Profil
                                    </a>
                                    <a
                                        href="/user/profile"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                    >
                                        <span
                                            class="material-symbols-outlined text-[18px] text-gray-400"
                                            >settings</span
                                        >
                                        Paramètres
                                    </a>
                                    <div
                                        class="border-t border-gray-100 dark:border-gray-800 mt-1 pt-1"
                                    >
                                        <form method="POST" action="/logout">
                                            <input
                                                type="hidden"
                                                name="_token"
                                                :value="csrfToken"
                                            />
                                            <button
                                                type="submit"
                                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                            >
                                                <span
                                                    class="material-symbols-outlined text-[18px]"
                                                    >logout</span
                                                >
                                                Déconnexion
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ══════════════════════════════════════
                 FLASH MESSAGES (succès / erreur)
            ═══════════════════════════════════════ -->
            <div v-if="flash.success || flash.error" class="px-6 pt-4">
                <!-- Succès -->
                <Transition name="fade">
                    <div
                        v-if="flash.success"
                        class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm font-medium"
                    >
                        <span
                            class="material-symbols-outlined text-emerald-500 text-[20px]"
                            >check_circle</span
                        >
                        {{ flash.success }}
                        <button
                            @click="dismissFlash('success')"
                            class="ml-auto text-emerald-400 hover:text-emerald-600"
                        >
                            <span class="material-symbols-outlined text-[18px]"
                                >close</span
                            >
                        </button>
                    </div>
                </Transition>
                <!-- Erreur -->
                <Transition name="fade">
                    <div
                        v-if="flash.error"
                        class="flex items-center gap-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm font-medium"
                    >
                        <span
                            class="material-symbols-outlined text-red-500 text-[20px]"
                            >error</span
                        >
                        {{ flash.error }}
                        <button
                            @click="dismissFlash('error')"
                            class="ml-auto text-red-400 hover:text-red-600"
                        >
                            <span class="material-symbols-outlined text-[18px]"
                                >close</span
                            >
                        </button>
                    </div>
                </Transition>
            </div>

            <!-- ══════════════════════════════════════
                 CONTENU PRINCIPAL (slot)
            ═══════════════════════════════════════ -->
            <main class="flex-1 p-6 animate-slide-up">
                <slot />
            </main>

            <!-- Footer minimaliste -->
            <footer
                class="px-6 py-4 text-center text-xs text-gray-300 dark:text-gray-700 border-t border-gray-100 dark:border-gray-800"
            >
                © {{ new Date().getFullYear() }} Ya Consulting — v1.0 MVP
            </footer>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import NavItem from "@/Components/NavItem.vue";

// ── Props du layout ─────────────────────────────────────────
const props = defineProps({
    title: { type: String, default: "Dashboard" },
    breadcrumb: { type: String, default: null },
});

const page = usePage();
const pageTitle = computed(() => props.title);
const pageBreadcrumb = computed(() => props.breadcrumb);

// ── Thème ───────────────────────────────────────────────────
const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains("dark");
    document.addEventListener("click", handleOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleOutsideClick);
});

function toggleTheme() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle("dark", isDark.value);
    document.documentElement.style.colorScheme = isDark.value
        ? "dark"
        : "light";

    // Persister le choix en base de données (requête Inertia silencieuse)
    router.patch(
        "/user/theme",
        { theme: isDark.value ? "dark" : "light" },
        {
            preserveState: true,
            preserveScroll: true,
            onError: () => {
                // Annuler si erreur
                isDark.value = !isDark.value;
                document.documentElement.classList.toggle("dark", isDark.value);
            },
        },
    );
}

// ── Sidebar ─────────────────────────────────────────────────
const sidebarOpen = ref(true);

// ── Menu utilisateur ─────────────────────────────────────────
const userMenuOpen = ref(false);
const userMenuRef = ref(null);

function handleOutsideClick(e) {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        userMenuOpen.value = false;
    }
}

const userInitials = computed(() => {
    const name = page.props.auth.user?.name ?? "";
    return name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .slice(0, 2)
        .toUpperCase();
});

const roleLabel = computed(() => {
    const roleMap = {
        admin: "Administrateur",
        chef_projet: "Chef de projet",
        collaborateur: "Collaborateur",
    };
    return roleMap[page.props.auth.user?.role] ?? "";
});

// ── Flash messages ────────────────────────────────────────────
const flash = computed(() => page.props.flash ?? {});
function dismissFlash(type) {
    // Simple: recharge la page pour effacer le flash
    // (en prod on utiliserait un état local)
}

// ── CSRF Token ────────────────────────────────────────────────
const csrfToken = computed(
    () =>
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ?? "",
);

// ── Navigation items ──────────────────────────────────────────
const navItems = [
    {
        label: "Dashboard",
        icon: "dashboard",
        route: "/dashboard",
        exact: true,
    },
    {
        label: "Projets",
        icon: "account_tree",
        route: "/projects",
    },
    {
        label: "Dépenses",
        icon: "receipt_long",
        route: "/expenses",
    },
    {
        label: "Rapports",
        icon: "bar_chart",
        route: "/reports",
        // N'afficher qu'aux admins et chefs de projet
        // guard: ['admin', 'chef_projet'],
    },
    {
        label: "Équipe",
        icon: "groups",
        route: "/team",
        // guard: ['admin'],
    },
];
</script>

<style scoped>
/* Transition dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.97);
}

/* Transition fade flash */
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
