<?php
/**
 * Sidebar partagée — inclure après session_guard.php
 * $current_user et $active_page doivent être définis.
 */
$active_page = $active_page ?? basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-mark">C</div>
    <div><p class="sidebar-brand">Camrail</p><p class="sidebar-sub">Employee Directory</p></div>
  </div>

  <nav class="sidebar-nav">
    <?php
    $links = [
      'dashboard'   => ['href'=>'dashboard.php',   'label'=>'Dashboard',      'icon'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'],
      'directory'   => ['href'=>'directory.php',   'label'=>'Répertoire',     'icon'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
      'departments' => ['href'=>'departments.php', 'label'=>'Départements',   'icon'=>'<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
      'profile'     => ['href'=>'profile.php',     'label'=>'Profil',         'icon'=>'<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>'],
      'settings'    => ['href'=>'settings.php',    'label'=>'Paramètres',     'icon'=>'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l-.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
    ];

    // Ajouter le lien admin seulement pour les admins
    if ($current_user['is_admin']) {
        $admin_link = ['href'=>'admin.php', 'label'=>'Administration', 'icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'];
        // Insérer après departments
        $new = [];
        foreach ($links as $k => $v) {
            $new[$k] = $v;
            if ($k === 'departments') $new['admin'] = $admin_link;
        }
        $links = $new;
    }

    foreach ($links as $page => $link):
        $is_active = ($active_page === $page) ? ' active' : '';
    ?>
    <a href="<?= $link['href'] ?>" class="nav-link<?= $is_active ?>" data-page="<?= $page ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <?= $link['icon'] ?>
      </svg>
      <span><?= $link['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-user">
    <?php if ($current_user['avatar']): ?>
      <img src="<?= htmlspecialchars($current_user['avatar']) ?>" alt="Avatar" class="user-avatar"/>
    <?php else: ?>
      <div class="user-avatar user-avatar-initials">
        <?= mb_strtoupper(mb_substr($current_user['name'], 0, 2)) ?>
      </div>
    <?php endif; ?>
    <div class="user-info">
      <p class="user-name"><?= htmlspecialchars($current_user['short_name']) ?></p>
      <p class="user-role"><?= htmlspecialchars($current_user['job_title']) ?></p>
    </div>
    <a href="backend/logout.php" class="logout-btn" title="Se déconnecter">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </a>
  </div>
</aside>