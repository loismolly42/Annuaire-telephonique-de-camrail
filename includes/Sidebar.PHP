<?php
$active_page = $active_page ?? '';
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-mark">C</div>
    <div>
      <p class="sidebar-brand">Camrail</p>
      <p class="sidebar-sub">Employee Directory</p>
    </div>
  </div>

  <nav class="sidebar-nav">
    <a href="/camrail_annuaire/dashboard.php" class="nav-link <?= $active_page === 'dashboard' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      <span>Dashboard</span>
    </a>
    <a href="/camrail_annuaire/directory.php" class="nav-link <?= $active_page === 'directory' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Répertoire</span>
    </a>
    <a href="/camrail_annuaire/departments.php" class="nav-link <?= $active_page === 'departments' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <span>Départements</span>
    </a>
    <?php if (!empty($current_user['is_admin'])): ?>
    <a href="/camrail_annuaire/admin.php" class="nav-link <?= $active_page === 'admin' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <span>Administration</span>
    </a>
    <?php endif; ?>
    <a href="/camrail_annuaire/profile.php" class="nav-link <?= $active_page === 'profile' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      <span>Profil</span>
    </a>
    <a href="/camrail_annuaire/settings.php" class="nav-link <?= $active_page === 'settings' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      <span>Paramètres</span>
    </a>
  </nav>

  <div class="sidebar-user">
    <?php if (!empty($current_user['avatar'])): ?>
      <img src="<?= htmlspecialchars($current_user['avatar']) ?>" alt="Avatar" class="user-avatar"/>
    <?php else: ?>
      <div class="user-avatar" style="display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;background:var(--red-light,#fee2e2);color:var(--red,#e8303a)">
        <?= mb_strtoupper(mb_substr($current_user['name'] ?? 'U', 0, 2)) ?>
      </div>
    <?php endif; ?>
    <div class="user-info">
      <p class="user-name"><?= htmlspecialchars($current_user['name'] ?? '') ?></p>
      <p class="user-role"><?= !empty($current_user['is_admin']) ? 'Administrateur' : htmlspecialchars($current_user['job_title'] ?? 'Utilisateur') ?></p>
    </div>
    <a href="/camrail_annuaire/backend/logout.php" class="logout-btn" title="Se déconnecter">
      <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
  </div>
</aside>