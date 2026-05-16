<?php /** Topbar partagée — inclure après session_guard.php */ ?>
<header class="topbar">
  <div class="search-wrap" style="position:relative">
    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="search" class="search-input" id="global-search"
           placeholder="Rechercher un employé, département…" autocomplete="off"/>
    <kbd class="search-kbd">⌘K</kbd>
    <div class="search-results" id="search-results" style="display:none"></div>
  </div>

  <div class="topbar-actions">
    <?php if ($current_user['is_admin']): ?>
    <button class="topbar-btn" id="notif-btn" title="Demandes en attente">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span class="notif-badge" id="notif-count" style="display:none">0</span>
    </button>
    <?php endif; ?>

    <a href="profile.php" class="topbar-btn" title="Mon profil">
      <?php if ($current_user['avatar']): ?>
        <img src="<?= htmlspecialchars($current_user['avatar']) ?>"
             style="width:28px;height:28px;border-radius:50%;object-fit:cover" alt=""/>
      <?php else: ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
        </svg>
      <?php endif; ?>
    </a>
  </div>
</header>

<script>
// ── Recherche live ────────────────────────────────────────────────────────────
(function() {
  const input   = document.getElementById('global-search');
  const results = document.getElementById('search-results');
  if (!input) return;
  let timer;

  input.addEventListener('input', () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if (q.length < 2) { results.style.display = 'none'; return; }
    timer = setTimeout(() => {
      fetch(`backend/api.php?action=search&q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
          if (!data.success) return;
          if (!data.results.length) {
            results.innerHTML = '<p class="search-empty">Aucun résultat pour « ' + q + ' »</p>';
          } else {
            results.innerHTML = data.results.map(e => `
              <a href="employee.php?id=${e.id_employe}" class="search-result-item">
                ${e.photo
                  ? `<img src="${e.photo}" class="search-avatar" alt=""/>`
                  : `<div class="search-avatar-init">${(e.prenom||'?')[0]}${(e.nom||'?')[0]}</div>`}
                <div>
                  <strong>${e.prenom} ${e.nom}</strong>
                  <span>${e.poste || ''} — ${e.nom_service || ''}</span>
                </div>
              </a>`).join('');
          }
          results.style.display = 'block';
        }).catch(() => {});
    }, 300);
  });

  document.addEventListener('click', e => {
    if (!input.contains(e.target) && !results.contains(e.target))
      results.style.display = 'none';
  });

  // Raccourci ⌘K / Ctrl+K
  document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault(); input.focus();
    }
  });
})();

// ── Badge notifications (admin) ───────────────────────────────────────────────
<?php if ($current_user['is_admin']): ?>
fetch('backend/api.php?action=stats')
  .then(r => r.json())
  .then(d => {
    if (d.success && d.en_attente > 0) {
      const badge = document.getElementById('notif-count');
      if (badge) { badge.textContent = d.en_attente; badge.style.display = 'flex'; }
    }
  }).catch(() => {});

document.getElementById('notif-btn')?.addEventListener('click', () => {
  window.location = 'admin.php';
});
<?php endif; ?>
</script>