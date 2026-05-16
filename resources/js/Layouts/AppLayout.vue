<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Banner from "@/Components/Banner.vue";
import NavItem from "@/Components/NavItem.vue";

// ── Props ──────────────────────────────────────────────────
const props = defineProps({
    title: { type: String, default: "Dashboard" },
    breadcrumb: { type: String, default: null },
});

const page = usePage();
const pageTitle = computed(() => props.title);
const pageBreadcrumb = computed(() => props.breadcrumb);

// ── Thème ──────────────────────────────────────────────────
// const isDark = ref(false);

// onMounted(() => {
//     isDark.value = document.documentElement.classList.contains("dark");
//     document.addEventListener("click", handleOutsideClick);
// });
// onBeforeUnmount(() => {
//     document.removeEventListener("click", handleOutsideClick);
// });

// function toggleTheme() {
//     isDark.value = !isDark.value;
//     document.documentElement.classList.toggle("dark", isDark.value);
//     document.documentElement.style.colorScheme = isDark.value
//         ? "dark"
//         : "light";
//     router.patch(
//         "/gestion/user/theme",
//         { theme: isDark.value ? "dark" : "light" },
//         {
//             preserveState: true,
//             preserveScroll: true,
//             onError: () => {
//                 isDark.value = !isDark.value;
//                 document.documentElement.classList.toggle("dark", isDark.value);
//             },
//         },
//     );
// }
const isDark = ref(false);

onMounted(() => {
    // Récupérer le thème depuis l'utilisateur ou localStorage
    const userTheme = page.props.auth.user?.theme;
    if (userTheme) {
        isDark.value = userTheme === "dark";
    } else {
        isDark.value = document.documentElement.classList.contains("dark");
    }

    // Appliquer le thème
    if (isDark.value) {
        document.documentElement.classList.add("dark");
    } else {
        document.documentElement.classList.remove("dark");
    }

    document.addEventListener("click", handleOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleOutsideClick);
});

function toggleTheme() {
    const newTheme = !isDark.value;
    isDark.value = newTheme;

    // Appliquer immédiatement dans le DOM
    if (newTheme) {
        document.documentElement.classList.add("dark");
        document.documentElement.style.colorScheme = "dark";
    } else {
        document.documentElement.classList.remove("dark");
        document.documentElement.style.colorScheme = "light";
    }

    // Sauvegarder en base de données
    router.patch(
        "/gestion/user/theme",
        { theme: newTheme ? "dark" : "light" },
        {
            preserveState: true,
            preserveScroll: true,
            onError: (error) => {
                console.error("Erreur sauvegarde thème:", error);
                // Annuler le changement si erreur
                isDark.value = !newTheme;
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

// ── Logout via Jetstream ─────────────────────────────────────
// function logout() {
//     router.post(route("logout"));
// }
const logout = () => {
    router.post("/gestion/logout");
};

// ── Infos utilisateur ─────────────────────────────────────────
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
    const map = {
        admin: "Administrateur",
        project_manager: "Chef de projet",
        staff_member: "Collaborateur",
    };
    return map[page.props.auth.user?.role] ?? "";
});

// Photo de profil Jetstream (profile_photo_url) en priorité
const avatarUrl = computed(
    () =>
        page.props.auth.user?.profile_photo_url ??
        page.props.auth.user?.avatar ??
        null,
);

// ── Flash messages ────────────────────────────────────────────
const flash = computed(() => page.props.flash ?? {});
const flashSuccessVisible = ref(true);
const flashErrorVisible = ref(true);

// Réinitialiser visibilité à chaque navigation Inertia
router.on("navigate", () => {
    flashSuccessVisible.value = true;
    flashErrorVisible.value = true;
});

// ── Navigation items ──────────────────────────────────────────
// const navItems = [
//     { label: "Dashboard", icon: "dashboard", route: "/dashboard", exact: true },
//     { label: "Projets", icon: "account_tree", route: "/projects" },
//     { label: "Dépenses", icon: "receipt_long", route: "/expenses" },
//     { label: "Rapports", icon: "bar_chart", route: "/reports" },
//     { label: "Équipe", icon: "groups", route: "/team" },
// ];

const navItems = computed(() => page.props.auth.user?.nav ?? []);
</script>

<template>
    <!-- ① Head Jetstream — titre de l'onglet dynamique -->
    <Head :title="title" />

    <!-- ② Banner Jetstream — vérification email, messages système -->
    <Banner />

    <div
        class="min-h-screen bg-[#f0f2f5] dark:bg-[#0d0f12] font-body transition-colors duration-300"
    >
        <!-- ════════════════════════════════════════
             SIDEBAR
        ═════════════════════════════════════════ -->
        <aside
            :class="[
                'fixed left-0 top-0 h-screen z-50 flex flex-col',
                'transition-[width] duration-300 ease-in-out will-change-[width]',
                sidebarOpen ? 'w-[215px]' : 'w-[58px]',
                'bg-white dark:bg-[#111318]',
                'border-r border-gray-200/70 dark:border-gray-800/70',
                'shadow-[1px_0_0_0_rgba(0,0,0,0.04),4px_0_16px_0_rgba(0,0,0,0.03)]',
            ]"
        >
            <!-- Logo -->
            <div
                :class="[
                    'flex items-center border-b border-gray-100 dark:border-gray-800/80 overflow-hidden',
                    'transition-all duration-300',
                    sidebarOpen
                        ? 'gap-3 px-[14px] py-[17px]'
                        : 'justify-center px-0 py-[17px]',
                ]"
            >
                <div
                    class="w-[30px] h-[30px] rounded-[9px] bg-primary-600 flex items-center justify-center flex-shrink-0 shadow-md shadow-primary-600/30"
                >
                    <span
                        class="material-symbols-outlined text-white text-[15px]"
                        style="
                            font-variation-settings:
                                &quot;FILL&quot; 1,
                                &quot;wght&quot; 600;
                        "
                        >trending_up</span
                    >
                </div>
                <div
                    v-show="sidebarOpen"
                    class="overflow-hidden whitespace-nowrap"
                >
                    <p
                        class="font-headline font-extrabold text-gray-900 dark:text-white text-[13.5px] leading-none tracking-tight"
                    >
                        Ya Consulting
                    </p>
                    <p
                        class="text-[9px] uppercase tracking-[0.2em] text-gray-400 dark:text-gray-600 font-bold mt-[3px]"
                    >
                        Gestion de projets
                    </p>
                </div>
            </div>

            <!-- Navigation principale -->
            <nav
                class="flex-1 py-2.5 px-2 space-y-[2px] overflow-y-auto overflow-x-hidden scrollbar-none"
            >
                <NavItem
                    v-for="item in navItems"
                    :key="item.route"
                    :item="item"
                    :collapsed="!sidebarOpen"
                />
            </nav>

            <!-- Bas de sidebar : profil résumé + toggle -->
            <div
                class="border-t border-gray-100 dark:border-gray-800/80 p-2 space-y-1"
            >
                <!-- Bloc utilisateur (visible si étendu) -->
                <Transition name="fade-quick">
                    <div
                        v-if="sidebarOpen"
                        class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl bg-gray-50 dark:bg-white/[0.04] mb-0.5"
                    >
                        <div
                            class="w-[26px] h-[26px] rounded-full overflow-hidden bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center flex-shrink-0 ring-[1.5px] ring-primary-600/20"
                        >
                            <img
                                v-if="avatarUrl"
                                :src="avatarUrl"
                                :alt="$page.props.auth.user?.name"
                                class="w-full h-full object-cover"
                            />
                            <span
                                v-else
                                class="text-primary-600 text-[9px] font-extrabold font-headline"
                                >{{ userInitials }}</span
                            >
                        </div>
                        <div class="flex-1 min-w-0">
                            <p
                                class="text-[11.5px] font-semibold text-gray-800 dark:text-gray-200 truncate leading-tight"
                            >
                                {{ $page.props.auth.user?.name }}
                            </p>
                            <p
                                class="text-[9.5px] text-gray-400 truncate leading-tight"
                            >
                                {{ roleLabel }}
                            </p>
                        </div>
                    </div>
                </Transition>

                <!-- Bouton collapse -->
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    :class="[
                        'w-full flex items-center gap-2 py-[7px] px-2.5 rounded-xl',
                        'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300',
                        'hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-all',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <span
                        class="material-symbols-outlined text-[17px] transition-transform duration-300 flex-shrink-0"
                        :class="sidebarOpen ? '' : 'rotate-180'"
                    >
                        chevron_left
                    </span>
                    <span v-show="sidebarOpen" class="text-[11px] font-semibold"
                        >Réduire</span
                    >
                </button>
            </div>
        </aside>

        <!-- ════════════════════════════════════════
             WRAPPER PRINCIPAL
        ═════════════════════════════════════════ -->
        <div
            :class="[
                'transition-[margin-left] duration-300 ease-in-out flex flex-col min-h-screen',
                sidebarOpen ? 'ml-[215px]' : 'ml-[58px]',
            ]"
        >
            <!-- ══════════════════════════════════
                 TOPBAR
            ════════════════════════════════════ -->
            <header
                class="sticky top-0 z-40 h-[54px] flex items-center bg-white/85 dark:bg-[#111318]/90 backdrop-blur-xl border-b border-gray-200/60 dark:border-gray-800/60 shadow-[0_1px_0_0_rgba(0,0,0,0.05),0_4px_12px_0_rgba(0,0,0,0.02)]"
            >
                <div class="w-full flex items-center justify-between px-5">
                    <!-- Titre + breadcrumb -->
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="min-w-0">
                            <h2
                                class="font-headline font-bold text-gray-900 dark:text-white text-[14.5px] leading-tight tracking-tight truncate"
                            >
                                {{ pageTitle }}
                            </h2>
                            <p
                                v-if="pageBreadcrumb"
                                class="text-[10.5px] text-gray-400 dark:text-gray-500 leading-none mt-[2px] truncate"
                            >
                                {{ pageBreadcrumb }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions droite -->
                    <div class="flex items-center gap-0.5 flex-shrink-0">
                        <!-- Toggle thème -->
                        <button
                            @click="toggleTheme"
                            :title="isDark ? 'Mode clair' : 'Mode sombre'"
                            class="topbar-btn"
                        >
                            <span class="material-symbols-outlined text-[19px]">
                                {{ isDark ? "light_mode" : "dark_mode" }}
                            </span>
                        </button>

                        <!-- Notifications -->
                        <!-- <button class="topbar-btn relative">
                            <span class="material-symbols-outlined text-[19px]"
                                >notifications</span
                            >
                            <span
                                class="absolute top-[7px] right-[7px] w-[5px] h-[5px] bg-red-500 rounded-full ring-[1.5px] ring-white dark:ring-[#111318]"
                            ></span>
                        </button> -->

                        <!-- Divider -->
                        <div
                            class="w-px h-[18px] bg-gray-200 dark:bg-gray-700/80 mx-2"
                        ></div>

                        <!-- Utilisateur dropdown -->
                        <div class="relative" ref="userMenuRef">
                            <button
                                @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2 pl-1.5 pr-2 py-1 rounded-xl hover:bg-gray-100 dark:hover:bg-white/[0.05] transition-all"
                            >
                                <!-- Avatar (photo Jetstream ou initiales) -->
                                <div
                                    class="w-[28px] h-[28px] rounded-full overflow-hidden bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center flex-shrink-0 ring-2 ring-primary-600/15"
                                >
                                    <img
                                        v-if="avatarUrl"
                                        :src="avatarUrl"
                                        :alt="$page.props.auth.user?.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <span
                                        v-else
                                        class="text-primary-600 text-[10px] font-extrabold font-headline"
                                        >{{ userInitials }}</span
                                    >
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p
                                        class="text-[12px] font-semibold text-gray-800 dark:text-gray-200 leading-tight max-w-[110px] truncate"
                                    >
                                        {{ $page.props.auth.user?.name }}
                                    </p>
                                    <p
                                        class="text-[10px] text-gray-400 leading-tight"
                                    >
                                        {{ roleLabel }}
                                    </p>
                                </div>
                                <span
                                    class="material-symbols-outlined text-gray-400 text-[15px] transition-transform duration-200 ml-0.5"
                                    :class="userMenuOpen ? 'rotate-180' : ''"
                                >
                                    expand_more
                                </span>
                            </button>

                            <!-- Dropdown utilisateur -->
                            <Transition name="dropdown">
                                <div
                                    v-if="userMenuOpen"
                                    class="absolute right-0 top-full mt-2 w-[210px] bg-white dark:bg-[#1a1d24] rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgba(0,0,0,0.4)] border border-gray-100 dark:border-gray-800/80 overflow-hidden z-50"
                                >
                                    <!-- Header dropdown -->
                                    <div
                                        class="px-4 py-3 bg-gray-50/80 dark:bg-white/[0.03] border-b border-gray-100 dark:border-gray-800/60"
                                    >
                                        <p
                                            class="text-[12.5px] font-bold text-gray-800 dark:text-gray-200 truncate leading-tight"
                                        >
                                            {{ $page.props.auth.user?.name }}
                                        </p>
                                        <p
                                            class="text-[11px] text-gray-400 truncate mt-[2px]"
                                        >
                                            {{ $page.props.auth.user?.email }}
                                        </p>
                                    </div>

                                    <!-- Liens -->
                                    <div class="py-1.5">
                                        <Link
                                            :href="route('profile.show')"
                                            class="dropdown-item"
                                            @click="userMenuOpen = false"
                                        >
                                            <span
                                                class="material-symbols-outlined text-[15px] text-gray-400"
                                                >account_circle</span
                                            >
                                            Mon profil
                                        </Link>

                                        <Link
                                            v-if="
                                                $page.props.jetstream
                                                    ?.hasApiFeatures
                                            "
                                            :href="route('api-tokens.index')"
                                            class="dropdown-item"
                                            @click="userMenuOpen = false"
                                        >
                                            <span
                                                class="material-symbols-outlined text-[15px] text-gray-400"
                                                >key</span
                                            >
                                            Tokens API
                                        </Link>

                                        <div
                                            class="my-1 border-t border-gray-100 dark:border-gray-800/70"
                                        ></div>

                                        <!-- ③ Logout Jetstream -->
                                        <button
                                            @click="logout"
                                            class="dropdown-item w-full text-red-500 hover:!bg-red-50 dark:hover:!bg-red-900/20"
                                        >
                                            <span
                                                class="material-symbols-outlined text-[15px]"
                                                >logout</span
                                            >
                                            Déconnexion
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ══════════════════════════════════
                 FLASH MESSAGES
            ════════════════════════════════════ -->
            <div class="px-5 pt-3.5 space-y-2">
                <Transition name="flash">
                    <div
                        v-if="flash.success && flashSuccessVisible"
                        class="flex items-center gap-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/80 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-400 px-3.5 py-2.5 rounded-xl text-[12.5px] font-medium shadow-sm"
                    >
                        <span
                            class="material-symbols-outlined text-emerald-500 text-[17px] flex-shrink-0"
                            style="font-variation-settings: &quot;FILL&quot; 1"
                            >check_circle</span
                        >
                        <span class="flex-1">{{ flash.success }}</span>
                        <button
                            @click="flashSuccessVisible = false"
                            class="text-emerald-400 hover:text-emerald-600 transition-colors flex-shrink-0"
                        >
                            <span class="material-symbols-outlined text-[15px]"
                                >close</span
                            >
                        </button>
                    </div>
                </Transition>
                <Transition name="flash">
                    <div
                        v-if="flash.error && flashErrorVisible"
                        class="flex items-center gap-3 bg-red-50 dark:bg-red-950/50 border border-red-200/80 dark:border-red-800/50 text-red-600 dark:text-red-400 px-3.5 py-2.5 rounded-xl text-[12.5px] font-medium shadow-sm"
                    >
                        <span
                            class="material-symbols-outlined text-red-500 text-[17px] flex-shrink-0"
                            style="font-variation-settings: &quot;FILL&quot; 1"
                            >error</span
                        >
                        <span class="flex-1">{{ flash.error }}</span>
                        <button
                            @click="flashErrorVisible = false"
                            class="text-red-400 hover:text-red-600 transition-colors flex-shrink-0"
                        >
                            <span class="material-symbols-outlined text-[15px]"
                                >close</span
                            >
                        </button>
                    </div>
                </Transition>
            </div>

            <!-- ══════════════════════════════════
                 CONTENU PRINCIPAL
            ════════════════════════════════════ -->
            <main class="flex-1 p-5 page-enter">
                <slot />
            </main>

            <!-- Footer -->
            <footer
                class="px-5 py-3 flex items-center justify-between border-t border-gray-200/50 dark:border-gray-800/50"
            >
                <span
                    class="text-[10.5px] text-gray-300 dark:text-gray-700 font-medium"
                >
                    © {{ new Date().getFullYear() }} Ya Consulting — MVP v1.0
                </span>
                <span class="text-[10.5px] text-gray-300 dark:text-gray-700">
                    Gestion de projets
                </span>
            </footer>
        </div>
    </div>
</template>

<style scoped>
/* ── Bouton topbar générique ───────────────────────── */
.topbar-btn {
    @apply w-[32px] h-[32px] flex items-center justify-center rounded-lg
           text-gray-400 hover:text-gray-700 dark:hover:text-gray-200
           hover:bg-gray-100 dark:hover:bg-white/[0.06]
           transition-all active:scale-95;
}

/* ── Item dropdown ────────────────────────────────── */
.dropdown-item {
    @apply flex items-center gap-2.5 px-4 py-[8px]
           text-[12.5px] font-medium text-gray-700 dark:text-gray-300
           hover:bg-gray-50 dark:hover:bg-white/[0.04]
           transition-colors cursor-pointer;
}

/* ── Transitions dropdown ─────────────────────────── */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-5px) scale(0.97);
}

/* ── Flash messages ───────────────────────────────── */
.flash-enter-active,
.flash-leave-active {
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}
.flash-enter-from,
.flash-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

/* ── Fade rapide logo texte ───────────────────────── */
.fade-quick-enter-active,
.fade-quick-leave-active {
    transition: opacity 0.15s ease;
}
.fade-quick-enter-from,
.fade-quick-leave-to {
    opacity: 0;
}

/* ── Animation entrée de page ─────────────────────── */
@keyframes pageEnter {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.page-enter {
    animation: pageEnter 0.25s ease-out;
}

/* ── Masquer scrollbar sidebar ────────────────────── */
.scrollbar-none {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
</style>
