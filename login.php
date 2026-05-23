<?php
/**
 * Camrail Directory — Page de connexion
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_name('camrail_sess');
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'dashboard.php'));
    exit;
}

$errors = [
    'empty'    => 'Veuillez renseigner votre adresse email et votre mot de passe.',
    'user'     => 'Adresse email introuvable. Vérifiez votre email professionnel.',
    'password' => 'Mot de passe incorrect. Veuillez réessayer.',
    'role'     => 'Accès refusé : vous n\'avez pas les droits administrateur.',
    'locked'   => 'Compte temporairement bloqué après plusieurs tentatives. Réessayez dans 15 min.',
    'expired'  => 'Votre session a expiré. Veuillez vous reconnecter.',
    'session'  => 'Vous devez être connecté pour accéder à cette page.',
    'db'       => 'Erreur de connexion à la base de données. Contactez l\'administrateur.',
    'format'   => 'Format d\'email invalide. Exemple : prenom.nom@camrail.net',
];

$error_code  = $_GET['error'] ?? '';
$error_msg   = $errors[$error_code] ?? '';
$success_msg = (($_GET['msg'] ?? '') === 'logged_out') ? 'Vous avez été déconnecté avec succès.' : '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Camrail — Portail Interne</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --red:       #c0000c;
      --red-dark:  #930007;
      --border:    #e5e7eb;
      --text:      #111827;
      --muted:     #6b7280;
      --bg:        #ffffff;
      --input-bg:  #f9fafb;
      --font:      'DM Sans', sans-serif;
      --mono:      'DM Mono', monospace;
    }

    html, body {
      height: 100%;
      font-family: var(--font);
      color: var(--text);
      background: #0d0d0d;
    }

    /* ════════════════════════════
       LAYOUT
    ════════════════════════════ */
    .page {
      display: flex;
      height: 100vh;
      width: 100vw;
      overflow: hidden;
    }

    /* ── GAUCHE ── */
    .pane-left {
      min-height: 0;
      width: 50%;
      flex-shrink: 0;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow-y: auto;
    }

    .form-area {
      width: 100%;
      max-width: 440px;
      padding: 24px 44px;
    }

    /* Marque */
    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 18px;
    }
    .brand-icon {
      width: 42px; height: 42px;
      background: var(--red);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .brand-icon svg { width: 22px; height: 22px; stroke: #fff; fill: none; stroke-width: 2; }
    .brand-name {
      font-size: 17px; font-weight: 700;
      color: var(--red);
      letter-spacing: .08em;
      text-transform: uppercase;
    }

    .form-title    { font-size: 22px; font-weight: 700; line-height: 1.2; margin-bottom: 6px; }
    .form-subtitle { font-size: 14px; color: var(--muted); line-height: 1.55; margin-bottom: 20px; }

    /* Rôle */
    .role-tabs {
      display: grid; grid-template-columns: 1fr 1fr;
      background: #f3f4f6;
      border-radius: 10px; padding: 4px; gap: 4px;
      margin-bottom: 18px;
    }
    .role-tab {
      padding: 9px 0; text-align: center;
      font-size: 13px; font-weight: 500;
      border: none; border-radius: 7px;
      cursor: pointer; background: transparent;
      color: var(--muted); transition: all .16s;
      font-family: var(--font);
    }
    .role-tab.active {
      background: #fff; color: var(--text);
      box-shadow: 0 1px 4px rgba(0,0,0,.1);
    }

    /* Alertes */
    .alert {
      display: flex; align-items: flex-start; gap: 10px;
      padding: 12px 14px; border-radius: 8px;
      font-size: 13px; font-weight: 500;
      margin-bottom: 20px; line-height: 1.5;
    }
    .alert svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; stroke: currentColor; fill: none; stroke-width: 2; }
    .alert-error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .alert-warn    { background: #fefce8; color: #92400e; border: 1px solid #fde68a; }

    /* Champs */
    .field { margin-bottom: 14px; }
    .field-label {
      display: block; font-size: 12px; font-weight: 600;
      color: var(--muted); text-transform: uppercase;
      letter-spacing: .05em; margin-bottom: 7px;
    }
    .field-wrap { position: relative; }
    .field-icon {
      position: absolute; left: 14px; top: 50%;
      transform: translateY(-50%);
      width: 17px; height: 17px;
      stroke: #9ca3af; fill: none; stroke-width: 2;
      pointer-events: none; transition: stroke .15s;
    }
    .field-input {
      width: 100%; height: 46px;
      padding: 0 46px;
      background: var(--input-bg);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      font-family: var(--font); font-size: 14px; color: var(--text);
      outline: none;
      transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .field-input::placeholder { color: #c0c0c0; }
    .field-input:focus {
      border-color: var(--red); background: #fff;
      box-shadow: 0 0 0 3px rgba(192,0,12,.1);
    }
    .field-wrap:focus-within .field-icon { stroke: var(--red); }
    .field-input.err { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.09); }
    .field-error { font-size: 12px; color: #dc2626; margin-top: 5px; min-height: 16px; display: block; }

    .toggle-pwd {
      position: absolute; right: 12px; top: 50%;
      transform: translateY(-50%);
      background: none; border: none; cursor: pointer; padding: 4px;
      color: #9ca3af; display: flex; align-items: center;
      transition: color .15s;
    }
    .toggle-pwd:hover { color: var(--red); }
    .toggle-pwd svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; }

    /* Remember / oublié */
    .row-between {
      display: flex; align-items: center;
      justify-content: space-between; margin-bottom: 18px;
    }
    .check-label {
      display: flex; align-items: center; gap: 8px;
      font-size: 13px; color: var(--muted); cursor: pointer;
    }
    .check-label input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--red); cursor: pointer; }
    .forgot-btn {
      font-size: 13px; font-weight: 600; color: var(--red);
      background: none; border: none; cursor: pointer;
      font-family: var(--font); transition: opacity .15s;
    }
    .forgot-btn:hover { opacity: .75; text-decoration: underline; }

    /* Submit */
    .submit-btn {
      width: 100%; height: 48px;
      background: var(--red); color: #fff;
      border: none; border-radius: 10px;
      font-family: var(--font); font-size: 15px; font-weight: 600;
      cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;
      box-shadow: 0 4px 14px rgba(192,0,12,.28);
      transition: background .18s, box-shadow .18s, transform .12s;
    }
    .submit-btn:hover  { background: var(--red-dark); box-shadow: 0 6px 20px rgba(192,0,12,.38); }
    .submit-btn:active { transform: scale(.98); }
    .submit-btn:disabled { background: #d1d5db; box-shadow: none; cursor: not-allowed; }
    .submit-btn svg { width: 18px; height: 18px; stroke: #fff; fill: none; stroke-width: 2.5; }
    .spin { animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Footer */
    .form-footer {
      margin-top: 20px; padding-top: 16px;
      border-top: 1px solid var(--border);
      display: flex; align-items: center;
      justify-content: space-between; flex-wrap: wrap; gap: 8px;
    }
    .footer-copy { font-size: 12px; color: #9ca3af; }
    .footer-links { display: flex; gap: 16px; }
    .footer-links a { font-size: 12px; color: #9ca3af; text-decoration: none; transition: color .15s; }
    .footer-links a:hover { color: var(--red); }

    /* ── DROITE ── */
    .pane-right {
      flex: 1;
      position: relative;
      overflow: hidden;
      /* ===== Remplacez 'images/train.jpg' par votre image ===== */
      background: #1c1c2e url('images/train.jpg') center center / cover no-repeat;
    }
    .pane-right::before {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(
        160deg,
        rgba(10,10,20,.42) 0%,
        rgba(10,10,20,.28) 35%,
        rgba(10,10,20,.75) 100%
      );
      z-index: 1;
    }

    .pane-right-content {
      position: absolute; inset: 0; z-index: 2;
      display: flex; flex-direction: column;
      justify-content: space-between;
      padding: 28px 32px 36px;
    }

    .badge-network {
      align-self: flex-end;
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.15);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 999px; padding: 8px 18px;
      font-size: 13px; font-weight: 500; color: #fff;
    }
    .badge-network svg { width: 15px; height: 15px; stroke: #fff; fill: none; stroke-width: 2; }

    .pane-body { display: flex; flex-direction: column; gap: 18px; }

    .badge-camrail {
      display: inline-flex; align-items: center;
      background: var(--red); color: #fff;
      font-size: 11px; font-weight: 700;
      letter-spacing: .1em; text-transform: uppercase;
      padding: 5px 14px; border-radius: 999px; width: fit-content;
    }

    .pane-desc {
      font-size: clamp(14px, 1.3vw, 16px);
      color: rgba(255,255,255,.85);
      line-height: 1.7; max-width: 360px;
    }

    .stats-grid {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 14px; max-width: 400px;
    }
    .stat-card {
      background: rgba(255,255,255,.12);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 14px; padding: 20px 22px;
    }
    .stat-card-icon { margin-bottom: 10px; }
    .stat-card-icon svg { width: 24px; height: 24px; stroke: rgba(255,255,255,.75); fill: none; stroke-width: 1.5; }
    .stat-card-n { font-size: 26px; font-weight: 700; color: #fff; line-height: 1.1; margin-bottom: 4px; }
    .stat-card-l { font-size: 12px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .07em; font-family: var(--mono); }

    .pane-footer { display: flex; align-items: center; gap: 16px; }
    .pane-footer-line { flex: 1; height: 1px; background: rgba(255,255,255,.22); }
    .pane-footer-text {
      font-size: 11px; font-weight: 600;
      color: rgba(255,255,255,.4);
      letter-spacing: .18em; text-transform: uppercase;
      font-family: var(--mono); white-space: nowrap;
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      .page        { flex-direction: column; height: auto; overflow: auto; }
      .pane-left   { width: 100%; min-height: 100vh; }
      .form-area   { padding: 48px 32px; max-width: 100%; }
      .pane-right  { min-height: 360px; flex: none; }
    }
    @media (max-width: 520px) {
      .form-area  { padding: 36px 20px; }
      .pane-right { min-height: 300px; }
      .stats-grid { gap: 10px; }
      .stat-card  { padding: 16px; }
      .stat-card-n { font-size: 22px; }
    }
  </style>
</head>
<body>

<div class="page">

  <!-- ══════════════════════════
       GAUCHE — Formulaire
  ══════════════════════════ -->
  <div class="pane-left">
    <div class="form-area">

      <div class="brand">
        <div class="brand-icon">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <rect x="4" y="3" width="16" height="14" rx="3"/>
            <path d="M4 12h16M8 17l-1.5 3M16 17l1.5 3"/>
            <circle cx="8.5" cy="14" r="1" fill="#fff" stroke="none"/>
            <circle cx="15.5" cy="14" r="1" fill="#fff" stroke="none"/>
          </svg>
        </div>
        <span class="brand-name">Camrail</span>
      </div>

      <h1 class="form-title">Portail d'Accès Interne</h1>
      <p class="form-subtitle">Veuillez vous authentifier pour accéder à l'annuaire.</p>

      <form id="login-form" method="POST" action="backend/auth.php" novalidate>

        <!-- Champ role hidden — mis à jour par le JS quand on clique un bouton -->
        <input type="hidden" name="role" id="role-input"
               value="<?= $error_code === 'role' ? 'admin' : 'user' ?>"/>

        <!-- Sélecteur de rôle — DANS le form pour que l'input hidden soit soumis correctement -->
        <div class="role-tabs" role="group" aria-label="Choisir un rôle">
          <button class="role-tab <?= ($error_code !== 'role') ? 'active' : '' ?>"
                  id="btn-user" data-role="user" type="button"
                  aria-pressed="<?= ($error_code !== 'role') ? 'true' : 'false' ?>">
            Utilisateur
          </button>
          <button class="role-tab <?= ($error_code === 'role') ? 'active' : '' ?>"
                  id="btn-admin" data-role="admin" type="button"
                  aria-pressed="<?= ($error_code === 'role') ? 'true' : 'false' ?>">
            Administrateur
          </button>
        </div>

        <?php if ($success_msg): ?>
          <div class="alert alert-success" role="status">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <?= htmlspecialchars($success_msg) ?>
          </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
          <div class="alert alert-error" role="alert">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($error_msg) ?>
          </div>
        <?php endif; ?>

        <!-- Email -->
        <div class="field">
          <label for="employee-email" class="field-label">Identifiant Professionnel</label>
          <div class="field-wrap">
            <input type="email" id="employee-email" name="employee_email"
                   class="field-input <?= in_array($error_code, ['user','empty','format']) ? 'err' : '' ?>"
                   placeholder="matricule@camrail.net"
                   value="<?= htmlspecialchars($_GET['email'] ?? '') ?>"
                   autocomplete="email"
                   spellcheck="false"
                   aria-required="true"
                   aria-describedby="error-email"/>
            <svg class="field-icon" viewBox="0 0 24 24" aria-hidden="true">
              <rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 8l10 7 10-7"/>
            </svg>
          </div>
          <span class="field-error" id="error-email" role="alert" aria-live="polite"></span>
        </div>

        <!-- Mot de passe -->
        <div class="field">
          <label for="password" class="field-label">Mot de passe</label>
          <div class="field-wrap">
            <input type="password" id="password" name="password"
                   class="field-input <?= $error_code === 'password' ? 'err' : '' ?>"
                   placeholder="••••••••"
                   autocomplete="current-password"
                   aria-required="true"
                   aria-describedby="error-pwd"/>
            <svg class="field-icon" viewBox="0 0 24 24" aria-hidden="true">
              <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
            </svg>
            <button type="button" class="toggle-pwd" id="toggle-pwd" aria-label="Afficher le mot de passe">
              <svg id="icon-eye" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
              <svg id="icon-eye-off" viewBox="0 0 24 24" style="display:none">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
          <span class="field-error" id="error-pwd" role="alert" aria-live="polite"></span>
        </div>

        <div class="row-between">
          <label class="check-label">
            <input type="checkbox" id="remember" name="remember"/>
            <span>Se souvenir de moi</span>
          </label>
          <button type="button" class="forgot-btn" id="forgot-btn">Mot de passe oublié ?</button>
        </div>

        <div class="alert alert-warn" id="form-alert" role="alert" style="display:none;margin-bottom:16px">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span id="form-alert-text"></span>
        </div>

        <button type="submit" class="submit-btn" id="submit-btn">
          <span id="submit-label">Connexion</span>
          <svg id="submit-spinner" viewBox="0 0 24 24" class="spin" style="display:none" aria-hidden="true">
            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
          </svg>
          <svg id="submit-arrow" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </button>

      </form>

      <div class="form-footer">
        <span class="footer-copy">© <?= date('Y') ?> Camrail S.A. Tous droits réservés.</span>
        <div class="footer-links">
          <a href="#">Support</a>
          <a href="#">Sécurité</a>
        </div>
      </div>

    </div>
  </div>

  <!-- ══════════════════════════
       DROITE — image train
       Ajoutez votre image via
       background-image dans .pane-right
  ══════════════════════════ -->
  <div class="pane-right" aria-hidden="true">
    <div class="pane-right-content">

      <div class="badge-network">
        <svg viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        Réseau Interne
      </div>

      <div class="pane-body">
        <span class="badge-camrail">Camrail</span>
        <p class="pane-desc">
          Accédez à l'ensemble des ressources humaines et techniques de la compagnie.
          Une structure digitale agile pour une performance ferroviaire sans compromis.
        </p>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-card-icon">
              <svg viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <div class="stat-card-n">2 400+</div>
            <div class="stat-card-l">Collaborateurs</div>
          </div>
          <div class="stat-card">
            <div class="stat-card-icon">
              <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.93 4.93l2.12 2.12M16.95 16.95l2.12 2.12M4.93 19.07l2.12-2.12M16.95 7.05l2.12-2.12"/>
              </svg>
            </div>
            <div class="stat-card-n">18</div>
            <div class="stat-card-l">Départements</div>
          </div>
        </div>
      </div>

      <div class="pane-footer">
        <div class="pane-footer-line"></div>
        <span class="pane-footer-text">Digital Rail Infrastructure</span>
        <div class="pane-footer-line"></div>
      </div>

    </div>
  </div>

</div><!-- .page -->

<script>
(function () {
  'use strict';

  const form       = document.getElementById('login-form');
  const emailInput = document.getElementById('employee-email');
  const pwdInput   = document.getElementById('password');
  const errEmail   = document.getElementById('error-email');
  const errPwd     = document.getElementById('error-pwd');
  const formAlert  = document.getElementById('form-alert');
  const formAlertT = document.getElementById('form-alert-text');
  const spinner    = document.getElementById('submit-spinner');
  const arrow      = document.getElementById('submit-arrow');
  const submitBtn  = document.getElementById('submit-btn');
  const btnUser    = document.getElementById('btn-user');
  const btnAdmin   = document.getElementById('btn-admin');
  const roleInput  = document.getElementById('role-input');
  const togglePwd  = document.getElementById('toggle-pwd');
  const iconEye    = document.getElementById('icon-eye');
  const iconEyeOff = document.getElementById('icon-eye-off');
  const forgotBtn  = document.getElementById('forgot-btn');

  /* Rôle */
  function setRole(role) {
    roleInput.value = role;
    btnUser.classList.toggle('active', role === 'user');
    btnAdmin.classList.toggle('active', role === 'admin');
    btnUser.setAttribute('aria-pressed', String(role === 'user'));
    btnAdmin.setAttribute('aria-pressed', String(role === 'admin'));
  }
  btnUser.addEventListener('click',  () => setRole('user'));
  btnAdmin.addEventListener('click', () => setRole('admin'));

  /* Toggle mot de passe */
  togglePwd.addEventListener('click', () => {
    const show = pwdInput.type === 'password';
    pwdInput.type = show ? 'text' : 'password';
    iconEye.style.display    = show ? 'none' : '';
    iconEyeOff.style.display = show ? ''     : 'none';
    togglePwd.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
  });

  /* Nettoyage erreurs à la saisie */
  emailInput.addEventListener('input', () => {
    errEmail.textContent = '';
    emailInput.classList.remove('err');
  });
  pwdInput.addEventListener('input', () => {
    errPwd.textContent = '';
    pwdInput.classList.remove('err');
  });

  /* Validation email */
  function isValidEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  }

  /* Soumission */
  form.addEventListener('submit', function (e) {
    errEmail.textContent = '';
    errPwd.textContent   = '';
    emailInput.classList.remove('err');
    pwdInput.classList.remove('err');
    formAlert.style.display = 'none';

    let valid = true;
    const email = emailInput.value.trim();
    const pwd   = pwdInput.value;

    if (!email) {
      errEmail.textContent = 'Veuillez entrer votre adresse email.';
      emailInput.classList.add('err');
      emailInput.focus();
      valid = false;
    } else if (!isValidEmail(email)) {
      errEmail.textContent = 'Format invalide. Exemple : prenom.nom@camrail.net';
      emailInput.classList.add('err');
      emailInput.focus();
      valid = false;
    }

    if (!pwd) {
      errPwd.textContent = 'Veuillez entrer votre mot de passe.';
      pwdInput.classList.add('err');
      if (valid) pwdInput.focus();
      valid = false;
    }

    if (!valid) { e.preventDefault(); return; }

    submitBtn.disabled    = true;
    spinner.style.display = '';
    arrow.style.display   = 'none';
    document.getElementById('submit-label').textContent = 'Connexion…';
  });

  /* Mot de passe oublié */
  forgotBtn.addEventListener('click', () => {
    formAlertT.textContent  = 'Contactez votre administrateur pour réinitialiser votre mot de passe.Numero de poste:';
    formAlert.style.display = 'flex';
    setTimeout(() => { formAlert.style.display = 'none'; }, 6000);
  });

})();
</script>

</body>
</html>