<?php
/**
 * Camrail Directory — Page de connexion
 * Si déjà connecté → redirection directe vers dashboard
 */
session_start();

// Déjà connecté ? On redirige directement
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Mapping des codes d'erreur → messages ─────────────────────────────────────
$errors = [
    'empty'    => 'Veuillez renseigner votre identifiant et votre mot de passe.',
    'user'     => 'Identifiant introuvable. Vérifiez votre matricule (ex : CR-000001).',
    'password' => 'Mot de passe incorrect. Veuillez réessayer.',
    'role'     => 'Accès refusé : vous n\'avez pas les droits administrateur.',
    'locked'   => 'Compte temporairement bloqué après plusieurs tentatives. Réessayez dans 15 minutes.',
    'expired'  => 'Votre session a expiré. Veuillez vous reconnecter.',
    'session'  => 'Vous devez être connecté pour accéder à cette page.',
    'db'       => 'Erreur de connexion à la base de données. Contactez l\'administrateur.',
    'format'   => 'Format d\'identifiant invalide. Utilisez le format CR-XXXXXX.',
];

$error_code = $_GET['error'] ?? '';
$error_msg  = $errors[$error_code] ?? '';

// Message de déconnexion réussie
$success_msg = '';
if (($_GET['msg'] ?? '') === 'logged_out') {
    $success_msg = 'Vous avez été déconnecté avec succès.';
}

// Pré-remplir l'identifiant si disponible (après erreur de mdp)
$prefill_id = htmlspecialchars($_GET['id'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Camrail — Portail Interne</title>
  <link rel="stylesheet" href="css/login.css"/>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
</head>
<body>

  <!-- Fond décoratif -->
  <div class="bg-grid" aria-hidden="true">
    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
          <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="0.8"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#grid)"/>
    </svg>
  </div>
  <div class="rail rail-left-1"  aria-hidden="true"></div>
  <div class="rail rail-left-2"  aria-hidden="true"></div>
  <div class="rail rail-right-1" aria-hidden="true"></div>
  <div class="rail rail-right-2" aria-hidden="true"></div>

  <main class="card-wrapper" role="main">
    <div class="card" id="login-card">

      <!-- Logo & Titre -->
      <header class="card-header">
        <div class="logo-circle" aria-label="Logo Camrail">
          <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuA8ujkIfvxYa3HE9iIYnZcV87PhKCaRKy95SGq0FWCOSZbcxybbQ5csJYS3oVFJk-2Triq2HLXSxFADkkzUNnyduD0fLRqKFXwMJ1AUmaUMQsJO2tSTVHyrEoOTER0GD0dBp02lPBJcSRErsu3501IRbbty42ZjxPkdQsrKvQkMkA7wB6yrpy6z17Qg8xVcPWGvZE9CCpqT1H9hFOqw3c0Hc-ZRqYu4eCqoxDMjKP4C4zTxFjP0XQCFUrdJv8OmkeSiWcUtAHMdUBw"
               alt="Logo Camrail" class="logo-img"/>
        </div>
        <h1 class="app-title">Camrail Directory</h1>
        <p class="app-subtitle">Portail d'Accès Interne</p>
      </header>

      <!-- Sélecteur de rôle -->
      <div class="role-selector" role="group" aria-label="Choisir un rôle">
        <button
          class="role-btn <?= ($error_code !== 'role') ? 'active' : '' ?>"
          id="btn-user"
          data-role="user"
          aria-pressed="<?= ($error_code !== 'role') ? 'true' : 'false' ?>"
          type="button"
        >
          <svg class="role-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
          Utilisateur
        </button>
        <button
          class="role-btn <?= ($error_code === 'role') ? 'active' : '' ?>"
          id="btn-admin"
          data-role="admin"
          aria-pressed="<?= ($error_code === 'role') ? 'true' : 'false' ?>"
          type="button"
        >
          <svg class="role-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M12 2l2.4 4.8L20 8l-4 3.9.9 5.6L12 15l-4.9 2.5.9-5.6L4 8l5.6-.2z"/>
          </svg>
          Administrateur
        </button>
      </div>

      <!-- Message de succès (déconnexion) -->
      <?php if ($success_msg): ?>
      <div class="form-alert form-alert--success" role="status" style="display:block;background:#dcfce7;color:#15803d;border-color:#86efac">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        <?= htmlspecialchars($success_msg) ?>
      </div>
      <?php endif; ?>

      <!-- Message d'erreur serveur -->
      <?php if ($error_msg): ?>
      <div class="form-alert" role="alert" style="display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;flex-shrink:0">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= htmlspecialchars($error_msg) ?>
      </div>
      <?php endif; ?>

      <!-- Formulaire -->
      <form id="login-form" class="login-form" method="POST" action="backend/auth.php" novalidate>

        <!-- Champ rôle caché (mis à jour par JS selon le bouton actif) -->
        <input type="hidden" name="role" id="role-input" value="<?= $error_code === 'role' ? 'admin' : 'user' ?>"/>

        <!-- Champ ID Employé -->
        <div class="field-group" id="group-id">
          <label for="employee-id" class="field-label">Identifiant Employé</label>
          <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>
            </svg>
            <input
              type="text"
              id="employee-id"
              name="employee_id"
              class="field-input <?= in_array($error_code, ['user','empty','format']) ? 'field-input--error' : '' ?>"
              placeholder="CR-000000"
              value="<?= $prefill_id ?>"
              autocomplete="username"
              autocapitalize="characters"
              spellcheck="false"
              aria-required="true"
              aria-describedby="error-id"
            />
          </div>
          <span class="field-error" id="error-id" role="alert" aria-live="polite">
            <?= in_array($error_code, ['user','format']) ? htmlspecialchars($error_msg) : '' ?>
          </span>
        </div>

        <!-- Champ Mot de passe -->
        <div class="field-group" id="group-pwd">
          <div class="field-label-row">
            <label for="password" class="field-label">Mot de passe</label>
            <a href="#" class="forgot-link" id="forgot-link">Oublié ?</a>
          </div>
          <div class="field-input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
            </svg>
            <input
              type="password"
              id="password"
              name="password"
              class="field-input <?= $error_code === 'password' ? 'field-input--error' : '' ?>"
              placeholder="••••••••"
              autocomplete="current-password"
              aria-required="true"
              aria-describedby="error-pwd"
            />
            <button type="button" class="toggle-pwd" id="toggle-pwd" aria-label="Afficher le mot de passe">
              <svg id="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
              <svg id="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <span class="field-error" id="error-pwd" role="alert" aria-live="polite">
            <?= $error_code === 'password' ? htmlspecialchars($error_msg) : '' ?>
          </span>
        </div>

        <!-- Se souvenir -->
        <label class="remember-label">
          <input type="checkbox" id="remember" name="remember" class="remember-check"/>
          <span class="remember-text">Se souvenir de cet appareil</span>
        </label>

        <!-- Bouton de soumission -->
        <button type="submit" class="submit-btn" id="submit-btn">
          <span class="submit-label">Connexion</span>
          <svg class="submit-spinner" id="submit-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true" style="display:none">
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
          </svg>
          <svg class="submit-arrow" id="submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>

        <!-- Alerte JS (validation côté client) -->
        <div class="form-alert" id="form-alert" role="alert" aria-live="assertive" style="display:none"></div>

      </form>

      <!-- Pied de carte -->
      <footer class="card-footer">
        <span class="footer-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          Accès SSL sécurisé
        </span>
        <div class="footer-sep" aria-hidden="true"></div>
        <span class="footer-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.68-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
          </svg>
          Protection Employé
        </span>
      </footer>

    </div><!-- /card -->

    <p class="legal-notice">
      © <?= date('Y') ?> Camrail S.A. — Services Numériques &amp; Répertoire.<br/>
      Personnel autorisé uniquement. Tous les accès sont enregistrés.
    </p>
  </main>

  <script src="js/login.js"></script>
  <script>
  // ── Sélecteur de rôle ─────────────────────────────────────────────────────
  (function () {
    const btnUser  = document.getElementById('btn-user');
    const btnAdmin = document.getElementById('btn-admin');
    const roleInput= document.getElementById('role-input');

    function setRole(role) {
      roleInput.value = role;
      btnUser.classList.toggle('active', role === 'user');
      btnAdmin.classList.toggle('active', role === 'admin');
      btnUser.setAttribute('aria-pressed', role === 'user');
      btnAdmin.setAttribute('aria-pressed', role === 'admin');
    }

    btnUser.addEventListener('click',  () => setRole('user'));
    btnAdmin.addEventListener('click', () => setRole('admin'));
  })();

  // ── Afficher/masquer le mot de passe ──────────────────────────────────────
  (function () {
    const btn     = document.getElementById('toggle-pwd');
    const input   = document.getElementById('password');
    const iconEye    = document.getElementById('icon-eye');
    const iconEyeOff = document.getElementById('icon-eye-off');
    if (!btn) return;

    btn.addEventListener('click', () => {
      const isPassword = input.type === 'password';
      input.type       = isPassword ? 'text' : 'password';
      iconEye.style.display    = isPassword ? 'none'  : '';
      iconEyeOff.style.display = isPassword ? ''      : 'none';
      btn.setAttribute('aria-label', isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    });
  })();

  // ── Validation côté client avant envoi ────────────────────────────────────
  (function () {
    const form    = document.getElementById('login-form');
    const idInput = document.getElementById('employee-id');
    const pwdInput= document.getElementById('password');
    const errId   = document.getElementById('error-id');
    const errPwd  = document.getElementById('error-pwd');
    const alert   = document.getElementById('form-alert');
    const spinner = document.getElementById('submit-spinner');
    const arrow   = document.getElementById('submit-arrow');
    const btn     = document.getElementById('submit-btn');

    // Auto-formater l'identifiant en majuscules
    idInput.addEventListener('input', () => {
      const pos = idInput.selectionStart;
      idInput.value = idInput.value.toUpperCase();
      idInput.setSelectionRange(pos, pos);
      errId.textContent = '';
      idInput.classList.remove('field-input--error');
    });

    pwdInput.addEventListener('input', () => {
      errPwd.textContent = '';
      pwdInput.classList.remove('field-input--error');
    });

    form.addEventListener('submit', function (e) {
      let valid = true;

      // Valider identifiant
      const id = idInput.value.trim();
      if (!id) {
        errId.textContent = 'Veuillez entrer votre identifiant.';
        idInput.classList.add('field-input--error');
        idInput.focus();
        valid = false;
      } else if (!/^CR-\d{6}$/.test(id)) {
        errId.textContent = 'Format invalide. Exemple : CR-000001';
        idInput.classList.add('field-input--error');
        idInput.focus();
        valid = false;
      }

      // Valider mot de passe
      const pwd = pwdInput.value;
      if (!pwd) {
        errPwd.textContent = 'Veuillez entrer votre mot de passe.';
        pwdInput.classList.add('field-input--error');
        if (valid) pwdInput.focus();
        valid = false;
      }

      if (!valid) { e.preventDefault(); return; }

      // Spinner de chargement
      btn.disabled = true;
      spinner.style.display = '';
      arrow.style.display   = 'none';
    });

    // Lien "Mot de passe oublié"
    const forgotLink = document.getElementById('forgot-link');
    if (forgotLink) {
      forgotLink.addEventListener('click', function (e) {
        e.preventDefault();
        alert.textContent    = 'Contactez votre administrateur système pour réinitialiser votre mot de passe.';
        alert.style.display  = 'block';
        alert.style.background = '#fef3c7';
        alert.style.color    = '#92400e';
        alert.style.border   = '1px solid #fbbf24';
        setTimeout(() => { alert.style.display = 'none'; }, 6000);
      });
    }
  })();
  </script>
</body>
</html>