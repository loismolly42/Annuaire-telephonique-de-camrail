<?php
// topbar.php — inclus dans toutes les pages
?>
<header class="topbar">
  <div class="search-wrap">
    <svg class="search-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="search" class="search-input" placeholder="Rechercher un employé, département…" autocomplete="off" id="global-search"/>
    <kbd class="search-kbd">⌘K</kbd>
  </div>
  <div class="topbar-actions">
    <?php if (!empty($current_user['is_admin'])): ?>
    <a href="/camrail_annuaire/admin.php" class="topbar-btn" title="Administration">
      <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </a>
    <?php endif; ?>
    <a href="/camrail_annuaire/profile.php" class="topbar-btn" title="Mon profil">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
    </a>
  </div>
</header>