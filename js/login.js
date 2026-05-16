/* ═══════════════════════════════════════════════════
   app.js — Camrail Login Page
   ─────────────────────────────────────────────────── */

/* ════════════════════════════════════════════════════
   1. COMPTES SIMULÉS
   ─────────────────────────────────────────────────────
   Modifie ce tableau pour ajouter/supprimer des comptes.
   En production, cette vérification se fait côté serveur.
════════════════════════════════════════════════════ */
const FAKE_ACCOUNTS = [
  { id: "CR-000001", password: "admin123",  role: "admin",  name: "Directeur Système" },
  { id: "CR-000002", password: "user1234",  role: "user",   name: "Agent Opérationnel" },
  { id: "CR-000003", password: "camrail!",  role: "user",   name: "Technicien Réseau" },
];

/* ════════════════════════════════════════════════════
   2. DÉLAI DE SIMULATION (ms)
   Simule un appel réseau — change la valeur librement.
════════════════════════════════════════════════════ */
const LOGIN_DELAY_MS = 1400;

/* ════════════════════════════════════════════════════
   3. MESSAGES (modifie les textes ici)
════════════════════════════════════════════════════ */
const MSG = {
  errorEmptyId:   "Veuillez saisir votre identifiant employé.",
  errorIdFormat:  "Format attendu : CR-XXXXXX (ex : CR-000001).",
  errorEmptyPwd:  "Veuillez saisir votre mot de passe.",
  errorPwdShort:  "Le mot de passe doit contenir au moins 6 caractères.",
  errorRoleMismatch: "Ce compte n'a pas le rôle sélectionné.",
  errorBadCreds:  "Identifiant ou mot de passe incorrect.",
  success:        (name, role) =>
    `✓ Connexion réussie. Bienvenue, ${name} (${role === "admin" ? "Administrateur" : "Utilisateur"}).`,
  forgotAlert:    "Contactez le service informatique au : it-support@camrail.cm",
};

/* ════════════════════════════════════════════════════
   4. REGEX DE VALIDATION DE L'IDENTIFIANT
   Modifie ce regex si le format change.
════════════════════════════════════════════════════ */
const ID_REGEX = /^CR-\d{6}$/;

/* ══════════════════════════════════════════════════ */

document.addEventListener("DOMContentLoaded", () => {

  /* ── Références DOM ── */
  const form        = document.getElementById("login-form");
  const inputId     = document.getElementById("employee-id");
  const inputPwd    = document.getElementById("password");
  const btnSubmit   = document.getElementById("submit-btn");
  const submitLabel = btnSubmit.querySelector(".submit-label");
  const spinner     = document.getElementById("submit-spinner");
  const arrow       = document.getElementById("submit-arrow");
  const formAlert   = document.getElementById("form-alert");
  const togglePwd   = document.getElementById("toggle-pwd");
  const iconEye     = document.getElementById("icon-eye");
  const iconEyeOff  = document.getElementById("icon-eye-off");
  const forgotLink  = document.getElementById("forgot-link");
  const roleBtns    = document.querySelectorAll(".role-btn");

  /* ── État ── */
  let selectedRole = "user";  // rôle sélectionné par défaut

  /* ════════════════════════════════════════════════
     SECTION A — SÉLECTEUR DE RÔLE
  ═════════════════════════════════════════════════ */
  roleBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      /* Mise à jour de l'état */
      selectedRole = btn.dataset.role;

      /* Mise à jour visuelle des boutons */
      roleBtns.forEach(b => {
        b.classList.toggle("active", b === btn);
        b.setAttribute("aria-pressed", b === btn ? "true" : "false");
      });

      /* Efface les erreurs et alertes lors du changement de rôle */
      clearErrors();
      hideAlert();
    });
  });

  /* ════════════════════════════════════════════════
     SECTION B — AFFICHER / MASQUER LE MOT DE PASSE
  ═════════════════════════════════════════════════ */
  togglePwd.addEventListener("click", () => {
    const isPassword = inputPwd.type === "password";
    inputPwd.type = isPassword ? "text" : "password";
    iconEye.style.display    = isPassword ? "none"  : "block";
    iconEyeOff.style.display = isPassword ? "block" : "none";
    togglePwd.setAttribute("aria-label",
      isPassword ? "Masquer le mot de passe" : "Afficher le mot de passe"
    );
  });

  /* ════════════════════════════════════════════════
     SECTION C — LIEN "OUBLIÉ ?"
  ═════════════════════════════════════════════════ */
  forgotLink.addEventListener("click", (e) => {
    e.preventDefault();
    /* Remplace par une vraie redirection ou modal selon tes besoins */
    showAlert("info", MSG.forgotAlert);
  });

  /* ════════════════════════════════════════════════
     SECTION D — VALIDATION EN TEMPS RÉEL (à la saisie)
     Efface l'erreur dès que l'utilisateur recommence à taper.
  ═════════════════════════════════════════════════ */
  inputId.addEventListener("input", () => clearFieldError("group-id",  "error-id"));
  inputPwd.addEventListener("input", () => clearFieldError("group-pwd", "error-pwd"));

  /* ════════════════════════════════════════════════
     SECTION E — SOUMISSION DU FORMULAIRE
  ═════════════════════════════════════════════════ */
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    /* 1. Nettoyer les états précédents */
    clearErrors();
    hideAlert();

    /* 2. Lire les valeurs */
    const idValue  = inputId.value.trim();
    const pwdValue = inputPwd.value;

    /* 3. Valider les champs */
    let hasError = false;

    if (!idValue) {
      setFieldError("group-id", "error-id", MSG.errorEmptyId);
      hasError = true;
    } else if (!ID_REGEX.test(idValue)) {
      setFieldError("group-id", "error-id", MSG.errorIdFormat);
      hasError = true;
    }

    if (!pwdValue) {
      setFieldError("group-pwd", "error-pwd", MSG.errorEmptyPwd);
      hasError = true;
    } else if (pwdValue.length < 6) {
      setFieldError("group-pwd", "error-pwd", MSG.errorPwdShort);
      hasError = true;
    }

    if (hasError) return;

    /* 4. Passer en état "chargement" */
    setLoading(true);

    /* 5. Simuler un appel réseau */
    await fakeDelay(LOGIN_DELAY_MS);

    /* 6. Vérifier les identifiants */
    const account = FAKE_ACCOUNTS.find(
      a => a.id === idValue && a.password === pwdValue
    );

    setLoading(false);

    if (!account) {
      showAlert("error", MSG.errorBadCreds);
      return;
    }

    /* 7. Vérifier le rôle sélectionné */
    if (account.role !== selectedRole) {
      showAlert("error", MSG.errorRoleMismatch);
      return;
    }

    /* 8. Connexion réussie ! */
    showAlert("success", MSG.success(account.name, account.role));
    form.reset();

    /*
     * ── PROCHAINE ÉTAPE ──────────────────────────────
     * Remplace ce bloc par une vraie redirection, ex :
     *   window.location.href = selectedRole === "admin"
     *     ? "/admin/dashboard.html"
     *     : "/user/home.html";
     * ─────────────────────────────────────────────── */
  });

  /* ════════════════════════════════════════════════
     FONCTIONS UTILITAIRES
  ═════════════════════════════════════════════════ */

  /** Affiche une erreur sur un champ précis */
  function setFieldError(groupId, errorId, message) {
    document.getElementById(groupId).classList.add("has-error");
    document.getElementById(errorId).textContent = message;
  }

  /** Efface l'erreur d'un champ précis */
  function clearFieldError(groupId, errorId) {
    document.getElementById(groupId).classList.remove("has-error");
    document.getElementById(errorId).textContent = "";
  }

  /** Efface toutes les erreurs de champs */
  function clearErrors() {
    clearFieldError("group-id",  "error-id");
    clearFieldError("group-pwd", "error-pwd");
  }

  /** Affiche l'alerte globale (type : "error" | "success" | "info") */
  function showAlert(type, message) {
    formAlert.textContent = message;
    formAlert.className   = "form-alert";
    if (type === "error")   formAlert.classList.add("alert-error");
    if (type === "success") formAlert.classList.add("alert-success");
    if (type === "info")    formAlert.classList.add("alert-error"); /* utilise le style error pour "info" */
    formAlert.style.display = "block";
  }

  /** Masque l'alerte globale */
  function hideAlert() {
    formAlert.style.display = "none";
    formAlert.className = "form-alert";
  }

  /** Active / désactive l'état de chargement du bouton */
  function setLoading(isLoading) {
    btnSubmit.disabled       = isLoading;
    spinner.style.display    = isLoading ? "block" : "none";
    arrow.style.display      = isLoading ? "none"  : "block";
    submitLabel.textContent  = isLoading ? "Connexion en cours…" : "Connexion";
  }

  /** Simule un délai réseau */
  function fakeDelay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

});