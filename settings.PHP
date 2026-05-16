<?php
$active_page = 'settings';
require_once __DIR__ . '/backend/session_guard.php';
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Paramètres — Camrail Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/main.css"/>
</head>
<body data-page="settings">

  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="content">

      <div class="settings-header">
        <div><h2>Paramètres</h2><p>Gérez vos informations et préférences.</p></div>
        <div class="settings-header-actions">
          <a href="profile.php" class="btn-secondary">Annuler</a>
          <button class="btn-primary" onclick="saveAll()">Enregistrer</button>
        </div>
      </div>

      <!-- Message global -->
      <div id="global-msg" class="form-alert" style="display:none;margin-bottom:16px"></div>

      <div class="settings-grid">

        <!-- ── Colonne gauche ── -->
        <div class="settings-col">

          <!-- Informations personnelles -->
          <div class="settings-card">
            <h3 class="settings-card-title">Informations personnelles</h3>
            <div class="s-avatar-wrap">
              <div class="s-avatar-ring">
                <?php if ($current_user['avatar']): ?>
                  <img id="profile-avatar" src="<?= htmlspecialchars($current_user['avatar']) ?>" alt="Avatar"/>
                <?php else: ?>
                  <div id="profile-avatar-init" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:var(--red);background:var(--red-light);border-radius:50%">
                    <?= mb_strtoupper(mb_substr($current_user['name'], 0, 2)) ?>
                  </div>
                <?php endif; ?>
                <label class="s-cam" title="Changer la photo (URL)">
                  <svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                </label>
              </div>
              <div class="s-fields">
                <div class="s-field">
                  <label>Nom complet</label>
                  <input type="text" id="s-name" value="<?= htmlspecialchars($current_user['name']) ?>" disabled style="background:var(--surface-2);color:var(--text-faint)"/>
                  <small style="color:var(--text-faint);font-size:11px">Modifiable via une demande admin</small>
                </div>
                <div class="s-field">
                  <label>Identifiant</label>
                  <input type="text" value="<?= htmlspecialchars($current_user['id']) ?>" disabled style="font-family:var(--font-mono);background:var(--surface-2);color:var(--text-faint)"/>
                </div>
              </div>
            </div>
          </div>

          <!-- Photo URL -->
          <div class="settings-card">
            <h3 class="settings-card-title">Photo de profil</h3>
            <div class="s-field">
              <label>URL de la photo</label>
              <input type="url" id="s-photo" placeholder="https://…" value="<?= htmlspecialchars($current_user['avatar']) ?>"/>
            </div>
            <button type="button" class="btn-secondary" style="margin-top:12px" onclick="previewPhoto()">Prévisualiser</button>
          </div>

          <!-- Sécurité -->
          <div class="settings-card">
            <h3 class="settings-card-title">Sécurité</h3>
            <div class="s-quick-actions">
              <button type="button" class="s-quick-btn" onclick="togglePwdForm()">
                <span class="s-quick-left"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Changer le mot de passe</span>
                <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
              </button>
            </div>
            <div id="pwd-form" style="display:none;margin-top:14px">
              <div class="s-field" style="margin-bottom:10px"><label>Mot de passe actuel</label><input type="password" id="s-pwd-old" placeholder="••••••••"/></div>
              <div class="s-field" style="margin-bottom:10px"><label>Nouveau mot de passe</label><input type="password" id="s-pwd-new" placeholder="8 caractères minimum"/></div>
              <div class="s-field" style="margin-bottom:14px"><label>Confirmer</label><input type="password" id="s-pwd-confirm" placeholder="••••••••"/></div>
              <div id="pwd-msg" class="form-alert" style="display:none;margin-bottom:10px"></div>
              <button type="button" class="btn-primary" onclick="changePassword()">Changer le mot de passe</button>
            </div>
          </div>

        </div>

        <!-- ── Colonne droite ── -->
        <div class="settings-col">

          <!-- Informations professionnelles -->
          <div class="settings-card">
            <h3 class="settings-card-title">Informations professionnelles</h3>
            <div class="s-2col">
              <div class="s-field">
                <label>Poste actuel</label>
                <input type="text" id="s-poste" value="<?= htmlspecialchars($current_user['job_title']) ?>" placeholder="Intitulé du poste"/>
              </div>
              <div class="s-field">
                <label>Département</label>
                <input type="text" value="<?= htmlspecialchars($current_user['department']) ?>" disabled style="background:var(--surface-2);color:var(--text-faint)"/>
              </div>
              <div class="s-field" style="grid-column:1/-1">
                <label>Email professionnel</label>
                <div class="s-icon-field">
                  <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                  <input type="email" id="s-email" value="<?= htmlspecialchars($current_user['email']) ?>" placeholder="prenom.nom@camrail.cm"/>
                </div>
              </div>
            </div>
            <p style="font-size:12px;color:var(--text-faint);margin-top:10px">
              Les modifications de poste et d'email sont soumises à validation par un administrateur.
            </p>
          </div>

          <!-- Visibilité dans l'annuaire -->
          <div class="settings-card muted">
            <h3 class="settings-card-title">Visibilité dans l'annuaire</h3>
            <div class="s-toggle-row">
              <div class="s-toggle-label">
                <p>Afficher le numéro à tous les employés</p>
                <p>Si désactivé, seul votre département peut le voir.</p>
              </div>
              <label class="s-switch"><input type="checkbox" id="s-show-phone" checked/><span class="s-track"></span></label>
            </div>
            <div class="s-toggle-row">
              <div class="s-toggle-label">
                <p>Disponible pour le mentorat interne</p>
                <p>Ajoute un badge sur votre fiche dans l'annuaire.</p>
              </div>
              <label class="s-switch"><input type="checkbox" id="s-mentorship"/><span class="s-track"></span></label>
            </div>
          </div>

          <!-- Rôle et permissions (info uniquement) -->
          <div class="settings-card">
            <h3 class="settings-card-title">Rôle & Permissions</h3>
            <div class="profil-fields">
              <div class="profil-field">
                <label>Rôle système</label>
                <p>
                  <?php if ($current_user['is_admin']): ?>
                  <span style="background:var(--red-light);color:var(--red);padding:3px 10px;border-radius:var(--r-full);font-size:12px;font-weight:700">Administrateur</span>
                  <?php else: ?>
                  <span style="background:var(--surface-2);color:var(--text-muted);padding:3px 10px;border-radius:var(--r-full);font-size:12px;font-weight:700">Utilisateur</span>
                  <?php endif; ?>
                </p>
              </div>
              <div class="profil-field">
                <label>Connecté depuis</label>
                <p id="session-time">—</p>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Footer -->
      <div class="settings-footer">
        <span class="settings-footer-meta" id="last-update">—</span>
        <a href="profile.php" class="btn-secondary">Annuler</a>
        <button class="btn-primary" onclick="saveAll()">Enregistrer toutes les modifications</button>
      </div>

    </main>
  </div>

  <script>
  const EMP_ID   = <?= (int)($current_user['id_employe'] ?? 0) ?>;
  const USER_ID  = <?= (int)($current_user['id_db'] ?? 0) ?>;
  const LOGIN_TS = <?= (int)($_SESSION['login_time'] ?? time()) ?>;

  // Temps de session
  document.getElementById('session-time').textContent =
    new Date(LOGIN_TS * 1000).toLocaleString('fr-FR');

  // ── Prévisualiser la photo ────────────────────────────────────────────────
  function previewPhoto() {
    const url = document.getElementById('s-photo').value.trim();
    const img = document.getElementById('profile-avatar');
    if (img && url) { img.src = url; }
  }

  // ── Toggle formulaire mot de passe ────────────────────────────────────────
  function togglePwdForm() {
    const f = document.getElementById('pwd-form');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
  }

  // ── Changer le mot de passe ───────────────────────────────────────────────
  function changePassword() {
    const old     = document.getElementById('s-pwd-old').value;
    const newPwd  = document.getElementById('s-pwd-new').value;
    const confirm = document.getElementById('s-pwd-confirm').value;
    const msg     = document.getElementById('pwd-msg');

    if (!old || !newPwd) { showMsg(msg, 'Remplissez tous les champs.', false); return; }
    if (newPwd.length < 8) { showMsg(msg, 'Le mot de passe doit faire au moins 8 caractères.', false); return; }
    if (newPwd !== confirm) { showMsg(msg, 'Les mots de passe ne correspondent pas.', false); return; }

    fetch('backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=change_password&old_password=${encodeURIComponent(old)}&new_password=${encodeURIComponent(newPwd)}`
    }).then(r => r.json()).then(d => {
      showMsg(msg, d.success ? '✓ Mot de passe modifié avec succès.' : (d.error || 'Erreur'), d.success);
      if (d.success) {
        document.getElementById('s-pwd-old').value = '';
        document.getElementById('s-pwd-new').value = '';
        document.getElementById('s-pwd-confirm').value = '';
      }
    });
  }

  // ── Enregistrer le profil ─────────────────────────────────────────────────
  function saveAll() {
    const poste  = document.getElementById('s-poste').value.trim();
    const email  = document.getElementById('s-email').value.trim();
    const photo  = document.getElementById('s-photo').value.trim();
    const msg    = document.getElementById('global-msg');

    if (!email) { showMsg(msg, 'L\'email ne peut pas être vide.', false); return; }

    const payload = new URLSearchParams();
    payload.append('action', 'update_profile');
    if (poste) payload.append('poste', poste);
    if (email) payload.append('email', email);
    if (photo) payload.append('photo', photo);

    fetch('backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: payload.toString()
    }).then(r => r.json()).then(d => {
      showMsg(msg, d.success
        ? '✓ Profil mis à jour. Certains changements nécessitent une validation admin.'
        : (d.error || 'Erreur lors de la sauvegarde'), d.success);
      if (d.success) {
        document.getElementById('last-update').textContent =
          'Dernière mise à jour : ' + new Date().toLocaleString('fr-FR');
      }
    });
  }

  function showMsg(el, text, success) {
    el.textContent = text;
    el.style.display = 'block';
    el.style.background = success ? '#dcfce7' : '';
    el.style.color = success ? '#15803d' : '';
    el.style.border = success ? '1px solid #86efac' : '';
    setTimeout(() => { el.style.display = 'none'; }, 5000);
  }

  // Mettre à jour la date de dernière modif au chargement
  document.getElementById('last-update').textContent =
    'Session démarrée le ' + new Date(LOGIN_TS * 1000).toLocaleDateString('fr-FR');
  </script>
  <script src="js/main.js"></script>
</body>
</html>