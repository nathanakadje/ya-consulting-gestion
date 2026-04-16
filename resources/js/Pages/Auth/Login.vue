<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const showPassword = ref(false);

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post("/login", {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Connexion — Ya Consulting" />

    <!-- Conteneur plein écran avec fond sombre + orbe violet -->
    <div class="ya-login-root">
        <!-- ── Fond cosmique ──────────────────────────────── -->
        <div class="ya-bg">
            <!-- Gradient de base -->
            <div class="ya-bg-gradient"></div>
            <!-- Orbe violet principal (droite haute) -->
            <div class="ya-orb ya-orb-main"></div>
            <!-- Orbe violet secondaire (légèrement décalé) -->
            <div class="ya-orb ya-orb-secondary"></div>
            <!-- Halo bas gauche -->
            <div class="ya-orb ya-orb-accent"></div>
            <!-- Étoiles simulées -->
            <div class="ya-stars"></div>
        </div>

        <!-- ── Carte formulaire glassmorphique ─────────────── -->
        <main class="ya-main">
            <div class="ya-card">
                <!-- En-tête -->
                <div class="ya-card-header">
                    <!-- Logo / Marque -->
                    <div class="ya-logo">
                        <div class="ya-logo-icon">
                            <svg
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                            >
                                <path
                                    d="M10 2L2 7v6l8 5 8-5V7L10 2z"
                                    fill="white"
                                    opacity="0.9"
                                />
                                <path
                                    d="M2 7l8 5 8-5"
                                    stroke="white"
                                    stroke-width="0.5"
                                    opacity="0.4"
                                />
                                <path
                                    d="M10 12v5"
                                    stroke="white"
                                    stroke-width="0.5"
                                    opacity="0.4"
                                />
                            </svg>
                        </div>
                        <span class="ya-logo-text">Ya Consulting</span>
                    </div>

                    <h1 class="ya-title">Connexion</h1>
                    <p class="ya-subtitle">Accédez à votre espace de gestion</p>
                </div>

                <!-- Message de statut Jetstream (mot de passe réinitialisé, etc.) -->
                <div v-if="status" class="ya-status">
                    {{ status }}
                </div>

                <!-- Formulaire — logique Jetstream intacte -->
                <form @submit.prevent="submit" class="ya-form">
                    <!-- Email -->
                    <div class="ya-field">
                        <label for="email" class="ya-label"
                            >Adresse email</label
                        >
                        <div class="ya-input-wrap">
                            <span class="ya-input-icon">
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 16 16"
                                    fill="none"
                                >
                                    <path
                                        d="M14 3H2a1 1 0 00-1 1v8a1 1 0 001 1h12a1 1 0 001-1V4a1 1 0 00-1-1z"
                                        stroke="currentColor"
                                        stroke-width="1.2"
                                    />
                                    <path
                                        d="M1 4l7 5 7-5"
                                        stroke="currentColor"
                                        stroke-width="1.2"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="ya-input"
                                placeholder="vous@ya-consulting.ci"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <InputError
                            :message="form.errors.email"
                            class="ya-error"
                        />
                    </div>

                    <!-- Mot de passe -->
                    <div class="ya-field">
                        <div class="ya-label-row">
                            <label for="password" class="ya-label"
                                >Mot de passe</label
                            >
                            <!-- <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="ya-forgot"
                            >
                                Mot de passe oublié ?
                            </Link> -->
                        </div>
                        <div class="ya-input-wrap">
                            <span class="ya-input-icon">
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 16 16"
                                    fill="none"
                                >
                                    <rect
                                        x="2"
                                        y="7"
                                        width="12"
                                        height="8"
                                        rx="0"
                                        stroke="currentColor"
                                        stroke-width="1.2"
                                    />
                                    <path
                                        d="M5 7V5a3 3 0 016 0v2"
                                        stroke="currentColor"
                                        stroke-width="1.2"
                                        stroke-linecap="square"
                                    />
                                    <circle
                                        cx="8"
                                        cy="11"
                                        r="1.2"
                                        fill="currentColor"
                                    />
                                </svg>
                            </span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="ya-input ya-input-pw"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                class="ya-pw-toggle"
                                @click="showPassword = !showPassword"
                                :aria-label="
                                    showPassword ? 'Masquer' : 'Afficher'
                                "
                            >
                                {{ showPassword ? "Masquer" : "Afficher" }}
                            </button>
                        </div>
                        <InputError
                            :message="form.errors.password"
                            class="ya-error"
                        />
                    </div>

                    <!-- Se souvenir de moi -->
                    <label class="ya-remember">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="ya-checkbox"
                        />
                        <span class="ya-remember-text">Rester connecté</span>
                    </label>

                    <!-- Bouton de connexion -->
                    <button
                        type="submit"
                        class="ya-submit"
                        :class="{ 'ya-submit--loading': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="ya-spinner"></span>
                        <span v-else>Se connecter</span>
                    </button>
                </form>

                <!-- Pied de carte -->
                <p class="ya-footer-text">
                    © {{ new Date().getFullYear() }} Ya Consulting
                </p>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* ═══════════════════════════════════════
   CONTENEUR RACINE
═══════════════════════════════════════ */
.ya-login-root {
    position: fixed;
    inset: 0;
    overflow: hidden;
    font-family: "DM Sans", "Syne", system-ui, sans-serif;
}

/* ═══════════════════════════════════════
   FOND COSMIQUE
═══════════════════════════════════════ */
.ya-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.ya-bg-gradient {
    position: absolute;
    inset: 0;
    background: radial-gradient(
        ellipse at 70% 40%,
        #1a0b3e 0%,
        #0d0717 40%,
        #070410 100%
    );
}

/* Orbe violet principal — grand cercle lumineux droite */
.ya-orb {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.ya-orb-main {
    width: 680px;
    height: 680px;
    top: -120px;
    right: -160px;
    /* background: radial-gradient(
        circle at 35% 40%,
        #7c3aed,
        #701c97 50%,
        #2e1065 80%,
        transparent 100%
    ); */
    background: radial-gradient(
        circle at 70% 30%,
        rgba(168, 85, 247, 0.9) 0%,
        rgba(139, 92, 246, 0.6) 20%,
        rgba(91, 33, 182, 0.4) 40%,
        rgba(30, 27, 75, 0.2) 60%,
        transparent 75%
    );
    opacity: 0.85;
}
.ya-orb-secondary {
    width: 520px;
    height: 520px;
    top: -60px;
    right: -80px;
    background: radial-gradient(
        circle at 40% 35%,
        rgba(167, 139, 250, 0.25),
        rgba(109, 40, 217, 0.15) 50%,
        transparent 80%
    );
}
.ya-orb-accent {
    width: 360px;
    height: 360px;
    bottom: -100px;
    left: -80px;
    background: radial-gradient(
        circle,
        rgba(109, 40, 217, 0.12),
        transparent 70%
    );
}

/* Étoiles simulées (points CSS) */
/* .ya-stars {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(
            1px 1px at 15% 20%,
            rgba(255, 255, 255, 0.5) 0%,
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 28% 65%,
            rgba(255, 255, 255, 0.4) 0%,
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 42% 15%,
            rgba(255, 255, 255, 0.3) 0%,
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 55% 80%,
            rgba(255, 255, 255, 0.4) 0%,
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 8% 48%,
            rgba(255, 255, 255, 0.3) 0%,
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 70% 30%,
            rgba(255, 255, 255, 0.2) 0%,
            transparent 100%
        ),
        radial-gradient(
            1.5px 1.5px at 35% 42%,
            rgba(255, 255, 255, 0.35) 0%,
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 18% 85%,
            rgba(255, 255, 255, 0.25) 0%,
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 60% 55%,
            rgba(255, 255, 255, 0.3) 0%,
            transparent 100%
        );
} */
.ya-stars {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(
            1px 1px at 15% 20%,
            rgba(255, 255, 255, 0.8),
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 28% 65%,
            rgba(255, 255, 255, 0.6),
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 42% 15%,
            rgba(255, 255, 255, 0.5),
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 55% 80%,
            rgba(255, 255, 255, 0.6),
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 8% 48%,
            rgba(255, 255, 255, 0.4),
            transparent 100%
        ),
        radial-gradient(
            1.5px 1.5px at 70% 30%,
            rgba(255, 255, 255, 0.5),
            transparent 100%
        ),
        radial-gradient(
            2px 2px at 35% 42%,
            rgba(255, 255, 255, 0.7),
            transparent 100%
        ),
        radial-gradient(
            1px 1px at 18% 85%,
            rgba(255, 255, 255, 0.4),
            transparent 100%
        ),
        radial-gradient(
            1.5px 1.5px at 60% 55%,
            rgba(255, 255, 255, 0.5),
            transparent 100%
        );

    filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.4));
    opacity: 0.8;
    animation: twinkle 4s infinite ease-in-out;
}
.ya-stars::after {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(
            1px 1px at 10% 10%,
            rgba(255, 255, 255, 0.2),
            transparent
        ),
        radial-gradient(
            1px 1px at 90% 90%,
            rgba(255, 255, 255, 0.2),
            transparent
        );
    opacity: 0.3;
}

@keyframes ya-spin {
    to {
        transform: rotate(360deg);
    }
}
@keyframes twinkle {
    0%,
    100% {
        opacity: 0.6;
    }
    50% {
        opacity: 1;
    }
}

/* ═══════════════════════════════════════
   CENTRAGE
═══════════════════════════════════════ */
.ya-main {
    position: relative;
    z-index: 10;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 2rem 6rem;
}

@media (max-width: 768px) {
    .ya-main {
        justify-content: center;
        padding: 1.5rem;
    }
}

/* ═══════════════════════════════════════
   CARTE GLASSMORPHIQUE — ANGLES DROITS
═══════════════════════════════════════ */
.ya-card {
    width: 100%;
    max-width: 400px;
    background: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(32px);
    -webkit-backdrop-filter: blur(32px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    /* Pas de border-radius — angles droits comme dans le prototype */
    border-radius: 0;
    padding: 2.5rem;
    box-shadow:
        0 0 0 1px rgba(124, 58, 237, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

/* ═══════════════════════════════════════
   EN-TÊTE
═══════════════════════════════════════ */
.ya-card-header {
    margin-bottom: 2rem;
}

.ya-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 1.5rem;
}

.ya-logo-icon {
    width: 34px;
    height: 34px;
    background: linear-gradient(135deg, #7c3aed, #5b21b6);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0; /* angles droits */
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.ya-logo-text {
    font-size: 14px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.9);
    letter-spacing: -0.01em;
    font-family: "Syne", "DM Sans", sans-serif;
}

.ya-title {
    font-family: "Plus Jakarta Sans", "Inter", sans-serif;
    font-size: 28px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.02em;
    line-height: 1.1;
    margin-bottom: 6px;
}

.ya-subtitle {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.45);
    line-height: 1.4;
}

/* ═══════════════════════════════════════
   STATUT JETSTREAM
═══════════════════════════════════════ */
.ya-status {
    margin-bottom: 1.25rem;
    padding: 10px 14px;
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 0;
    font-size: 13px;
    color: #6ee7b7;
}

/* ═══════════════════════════════════════
   FORMULAIRE
═══════════════════════════════════════ */
.ya-form {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.ya-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ya-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ya-label {
    font-size: 11.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(255, 255, 255, 0.5);
}

.ya-forgot {
    font-size: 12px;
    color: rgba(167, 139, 250, 0.8);
    text-decoration: none;
    transition: color 0.15s;
}
.ya-forgot:hover {
    color: rgba(196, 181, 253, 1);
}

/* Wrapper input avec icône -->  */
.ya-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.ya-input-icon {
    position: absolute;
    left: 13px;
    color: rgba(255, 255, 255, 0.35);
    display: flex;
    align-items: center;
    pointer-events: none;
    z-index: 1;
}

.ya-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0; /* angles droits */
    padding: 13px 14px 13px 40px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.9);
    outline: none;
    transition:
        border-color 0.15s,
        background 0.15s;
    font-family: "Plus Jakarta Sans", sans-serif;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

.ya-input::placeholder {
    color: rgba(255, 255, 255, 0.25);
}

.ya-input:focus {
    border-color: rgba(124, 58, 237, 0.7);
    background: rgba(124, 58, 237, 0.06);
    box-shadow: 0 0 0 1px rgba(124, 58, 237, 0.3);
}

/* Espace pour le bouton "Afficher" dans le champ password */
.ya-input-pw {
    padding-right: 76px;
}

.ya-pw-toggle {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.4);
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    padding: 4px;
    transition: color 0.15s;
    font-family: inherit;
}
.ya-pw-toggle:hover {
    color: rgba(255, 255, 255, 0.75);
}

/* Erreurs Jetstream -->  */
.ya-error {
    font-size: 12px !important;
    color: rgba(252, 165, 165, 0.9) !important;
    margin-top: 2px;
}

/* ═══════════════════════════════════════
   REMEMBER ME
═══════════════════════════════════════ */
.ya-remember {
    display: flex;
    align-items: center;
    gap: 9px;
    cursor: pointer;
    margin-top: 2px;
}

.ya-checkbox {
    width: 15px;
    height: 15px;
    border-radius: 0; /* angles droits */
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.04);
    accent-color: #7c3aed;
    cursor: pointer;
    flex-shrink: 0;
}

.ya-remember-text {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    user-select: none;
}

/* ═══════════════════════════════════════
   BOUTON SOUMETTRE
═══════════════════════════════════════ */
.ya-submit {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
    border: none;
    border-radius: 0; /* angles droits */
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition:
        opacity 0.15s,
        transform 0.1s;
    font-family: inherit;
    margin-top: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 0 30px rgba(124, 58, 237, 0.35);
}

.ya-submit:hover:not(:disabled) {
    opacity: 0.9;
    box-shadow: 0 0 40px rgba(124, 58, 237, 0.5);
}

.ya-submit:active:not(:disabled) {
    transform: scale(0.99);
}

.ya-submit--loading,
.ya-submit:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

/* Spinner de chargement */
.ya-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: ya-spin 0.7s linear infinite;
    display: inline-block;
}

/* ═══════════════════════════════════════
   PIED DE CARTE
═══════════════════════════════════════ */
.ya-footer-text {
    margin-top: 1.75rem;
    text-align: center;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.2);
    letter-spacing: 0.04em;
}
</style>
