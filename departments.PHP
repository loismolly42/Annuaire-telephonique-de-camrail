<?php
$active_page = 'departments';
require_once __DIR__ . '/backend/session_guard.php';
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Départements — Camrail Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/main.css"/>
</head>
<body data-page="departments">

  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="content">
      <div class="dir-header">
        <div>
          <h2>Départements</h2>
          <p id="dept-subtitle">Chargement de la structure organisationnelle…</p>
        </div>
        <?php if ($current_user['is_admin']): ?>
        <button class="btn-primary" onclick="openAddDeptModal()">
          <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nouveau département
        </button>
        <?php endif; ?>
      </div>

      <!-- Grille des départements — remplie par JS -->
      <div class="dept-grid" id="dept-grid">
        <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="dept-card skeleton" style="height:180px"></div>
        <?php endfor; ?>
      </div>
    </main>
  </div>

  <!-- Modal : Employés du département -->
  <div class="modal-overlay" id="dept-modal" style="display:none" onclick="if(event.target===this)closeDeptModal()">
    <div class="modal modal-wide">
      <div class="modal-header">
        <h3 id="dept-modal-title">—</h3>
        <button class="modal-close" onclick="closeDeptModal()">✕</button>
      </div>
      <div id="dept-modal-body" style="padding:0 24px 24px">Chargement…</div>
    </div>
  </div>

  <!-- Modal ajouter département (admin) -->
  <?php if ($current_user['is_admin']): ?>
  <div class="modal-overlay" id="add-dept-modal" style="display:none" onclick="if(event.target===this)closeAddDeptModal()">
    <div class="modal">
      <div class="modal-header"><h3>Nouveau département</h3><button class="modal-close" onclick="closeAddDeptModal()">✕</button></div>
      <form id="add-dept-form">
        <div class="s-field" style="margin-bottom:14px"><label>Nom du département *</label><input name="nom_service" required placeholder="Ex: Contrôle de Qualité"/></div>
        <div class="s-field"><label>Description</label><textarea name="description" rows="3" placeholder="Rôle et responsabilités de ce département…" style="width:100%;resize:vertical"></textarea></div>
        <div id="add-dept-error" class="form-alert" style="display:none;margin-top:12px"></div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeAddDeptModal()">Annuler</button>
          <button type="submit" class="btn-primary" id="add-dept-submit">Créer</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <script>
  const IS_ADMIN = <?= $current_user['is_admin'] ? 'true' : 'false' ?>;

  // Palette d'icônes et couleurs tournantes
  const DEPT_COLORS = ['red','blue','green','amber','red','blue','green','amber'];
  const DEPT_ICONS  = [
    '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    '<polyline points="14.5 17.5 3 6 3 3 6 3 17.5 14.5"/><line x1="13" y1="19" x2="19" y2="13"/><line x1="16" y1="16" x2="20" y2="20"/><line x1="19" y1="21" x2="21" y2="19"/>',
    '<path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
    '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
    '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
    '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/>',
  ];

  // ── Charger les départements ──────────────────────────────────────────────
  function loadDepartments() {
    fetch('backend/api.php?action=departments')
      .then(r => r.json())
      .then(d => {
        if (!d.success) return;
        const depts = d.departments;
        const total_agents = depts.reduce((s, dep) => s + Number(dep.nb_employes), 0);

        document.getElementById('dept-subtitle').textContent =
          `${depts.length} unité${depts.length > 1 ? 's' : ''} active${depts.length > 1 ? 's' : ''} — ${total_agents.toLocaleString('fr-FR')} agents au total`;

        document.getElementById('dept-grid').innerHTML = depts.map((dep, i) => `
          <div class="dept-card" onclick="openDeptModal(${dep.id_service}, '${escHtml(dep.nom_service)}')" style="cursor:pointer">
            <div class="dept-card-top">
              <div class="dept-icon ${DEPT_COLORS[i % DEPT_COLORS.length]}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  ${DEPT_ICONS[i % DEPT_ICONS.length]}
                </svg>
              </div>
              ${IS_ADMIN ? `<button class="dept-delete-btn" onclick="event.stopPropagation();deleteDept(${dep.id_service})" title="Supprimer">
                <svg viewBox="0 0 24 24" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
              </button>` : ''}
            </div>
            <p class="dept-name">${dep.nom_service}</p>
            <p class="dept-desc">${dep.description || 'Aucune description disponible.'}</p>
            <div class="dept-footer">
              <span class="dept-count">${Number(dep.nb_employes).toLocaleString('fr-FR')} agent${dep.nb_employes > 1 ? 's' : ''}</span>
              <svg class="dept-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
          </div>`).join('');
      })
      .catch(() => {
        document.getElementById('dept-grid').innerHTML =
          '<p style="color:var(--text-faint);grid-column:1/-1;padding:32px 0;text-align:center">Erreur de chargement.</p>';
      });
  }

  // ── Modal : liste des employés du département ─────────────────────────────
  function openDeptModal(id_service, nom) {
    document.getElementById('dept-modal-title').textContent = nom;
    document.getElementById('dept-modal-body').innerHTML = '<p style="color:var(--text-faint);padding:16px 0">Chargement…</p>';
    document.getElementById('dept-modal').style.display = 'flex';

    fetch(`backend/api.php?action=employees&dept=${id_service}&limit=48`)
      .then(r => r.json())
      .then(d => {
        if (!d.success || !d.employees.length) {
          document.getElementById('dept-modal-body').innerHTML =
            '<p style="color:var(--text-faint);padding:16px 0">Aucun employé dans ce département.</p>';
          return;
        }
        document.getElementById('dept-modal-body').innerHTML = `
          <p style="color:var(--text-faint);font-size:13px;margin-bottom:16px">${d.total} agent${d.total > 1 ? 's' : ''}</p>
          <div class="dir-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
            ${d.employees.map(e => `
              <div class="emp-card" style="padding:16px">
                ${e.photo
                  ? `<img class="emp-avatar" src="${e.photo}" alt="" style="width:56px;height:56px"/>`
                  : `<div class="emp-avatar emp-avatar-init" style="width:56px;height:56px;font-size:18px">${(e.prenom||'?')[0]}${(e.nom||'?')[0]}</div>`}
                <p class="emp-name" style="font-size:14px">${e.prenom} ${e.nom}</p>
                <p class="emp-role" style="font-size:12px">${e.poste || '—'}</p>
                <div class="emp-actions" style="margin-top:10px">
                  ${e.email ? `<a href="mailto:${e.email}" class="emp-btn">Email</a>` : ''}
                  ${e.phone_principal ? `<a href="tel:${e.phone_principal}" class="emp-btn">Appel</a>` : ''}
                </div>
              </div>`).join('')}
          </div>`;
      });
  }

  function closeDeptModal() {
    document.getElementById('dept-modal').style.display = 'none';
  }

  // ── Helper XSS ────────────────────────────────────────────────────────────
  function escHtml(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
  }

  // ── Supprimer un département (admin) ──────────────────────────────────────
  <?php if ($current_user['is_admin']): ?>
  function deleteDept(id) {
    if (!confirm('Supprimer ce département ?\nLes employés associés seront détachés.')) return;
    fetch('backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=delete_department&id_service=${id}`
    }).then(r => r.json()).then(d => {
      if (d.success) loadDepartments();
      else alert(d.error || 'Erreur lors de la suppression');
    });
  }

  // ── Modal ajouter département ─────────────────────────────────────────────
  function openAddDeptModal() {
    document.getElementById('add-dept-modal').style.display = 'flex';
  }
  function closeAddDeptModal() {
    document.getElementById('add-dept-modal').style.display = 'none';
    document.getElementById('add-dept-form').reset();
    document.getElementById('add-dept-error').style.display = 'none';
  }

  document.getElementById('add-dept-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('add-dept-submit');
    btn.disabled = true; btn.textContent = 'Création…';
    const fd = new FormData(this);
    fd.append('action', 'add_department');
    fetch('backend/api.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        if (d.success) { closeAddDeptModal(); loadDepartments(); }
        else {
          const err = document.getElementById('add-dept-error');
          err.textContent = d.error || 'Erreur lors de la création';
          err.style.display = 'block';
        }
      })
      .finally(() => { btn.disabled = false; btn.textContent = 'Créer'; });
  });
  <?php endif; ?>

  // ── Init ──────────────────────────────────────────────────────────────────
  loadDepartments();
  </script>
  <script src="js/main.js"></script>
</body>
</html>