<?php
$active_page = 'directory';
require_once __DIR__ . '/backend/session_guard.php';
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Répertoire — Camrail Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/main.css"/>
</head>
<body data-page="directory">

  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="content">
      <div class="dir-header">
        <div>
          <h2>Répertoire des Employés</h2>
          <p id="dir-subtitle">Chargement…</p>
        </div>
        <div style="display:flex;gap:10px">
          <button class="btn-secondary" id="btn-filters">
            <svg viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
            Filtres
          </button>
          <?php if ($current_user['is_admin']): ?>
          <button class="btn-primary" id="btn-add-emp">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajouter un employé
          </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Barre de filtres -->
      <div class="dir-toolbar">
        <div class="dir-search">
          <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="dir-search-input" placeholder="Rechercher par nom, poste, département…"/>
        </div>
        <select class="dir-select" id="filter-dept">
          <option value="">Tous les départements</option>
        </select>
        <select class="dir-select" id="filter-sort">
          <option value="nom">Trier par nom</option>
          <option value="poste">Trier par poste</option>
          <option value="date_creation">Plus récents d'abord</option>
        </select>
      </div>

      <!-- Grille d'employés — remplie par JS -->
      <div class="dir-grid" id="emp-grid">
        <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="emp-card skeleton" style="height:220px"></div>
        <?php endfor; ?>
      </div>

      <!-- Pagination -->
      <div class="dir-pagination" id="dir-pagination" style="display:none">
        <p id="pagination-info"></p>
        <div class="pag-btns" id="pag-btns"></div>
      </div>
    </main>
  </div>

  <!-- Modal détail employé -->
  <div class="modal-overlay" id="emp-modal" style="display:none" onclick="if(event.target===this)closeEmpModal()">
    <div class="modal modal-wide">
      <div class="modal-header">
        <h3 id="modal-emp-name">—</h3>
        <button class="modal-close" onclick="closeEmpModal()">✕</button>
      </div>
      <div id="modal-emp-body" style="padding:0 24px 24px">Chargement…</div>
    </div>
  </div>

  <!-- Modal ajouter (admin) -->
  <?php if ($current_user['is_admin']): ?>
  <div class="modal-overlay" id="add-modal" style="display:none" onclick="if(event.target===this)closeAddModal()">
    <div class="modal">
      <div class="modal-header"><h3>Nouvel employé</h3><button class="modal-close" onclick="closeAddModal()">✕</button></div>
      <form id="add-employee-form">
        <div class="s-2col">
          <div class="s-field"><label>Prénom *</label><input name="prenom" required/></div>
          <div class="s-field"><label>Nom *</label><input name="nom" required/></div>
          <div class="s-field"><label>Email *</label><input name="email" type="email" required/></div>
          <div class="s-field"><label>Poste</label><input name="poste"/></div>
          <div class="s-field"><label>Département</label>
            <select name="id_service" id="add-dept-select"><option value="">— Choisir —</option></select>
          </div>
          <div class="s-field"><label>Téléphone</label><input name="phone" type="tel"/></div>
        </div>
        <div id="add-error" class="form-alert" style="display:none;margin-top:12px"></div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeAddModal()">Annuler</button>
          <button type="submit" class="btn-primary" id="add-submit">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <script>
  const IS_ADMIN = <?= $current_user['is_admin'] ? 'true' : 'false' ?>;
  let currentPage = 1;
  let searchTimer;
  let depts = [];

  // ── Charger les départements dans les filtres ─────────────────────────────
  fetch('backend/api.php?action=departments')
    .then(r => r.json())
    .then(d => {
      if (!d.success) return;
      depts = d.departments;
      const sel = document.getElementById('filter-dept');
      depts.forEach(s => {
        const o = document.createElement('option');
        o.value = s.id_service;
        o.textContent = `${s.nom_service} (${s.nb_employes})`;
        sel.appendChild(o);
      });
      // Remplir aussi le select du modal ajout
      const addSel = document.getElementById('add-dept-select');
      if (addSel) {
        depts.forEach(s => {
          const o = document.createElement('option');
          o.value = s.id_service;
          o.textContent = s.nom_service;
          addSel.appendChild(o);
        });
      }
    });

  // ── Charger et afficher les employés ─────────────────────────────────────
  function loadEmployees(page = 1) {
    currentPage = page;
    const q    = document.getElementById('dir-search-input').value.trim();
    const dept = document.getElementById('filter-dept').value;

    let url = `backend/api.php?action=employees&page=${page}&limit=24`;
    if (q)    url += `&q=${encodeURIComponent(q)}`;
    if (dept) url += `&dept=${dept}`;

    // Squelettes
    const grid = document.getElementById('emp-grid');
    grid.innerHTML = Array(6).fill('<div class="emp-card skeleton" style="height:220px"></div>').join('');

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (!data.success) { grid.innerHTML = '<p style="color:var(--text-faint)">Erreur de chargement.</p>'; return; }

        document.getElementById('dir-subtitle').textContent =
          `${data.total.toLocaleString('fr-FR')} agent${data.total > 1 ? 's' : ''} trouvé${data.total > 1 ? 's' : ''}`;

        if (!data.employees.length) {
          grid.innerHTML = '<p style="color:var(--text-faint);grid-column:1/-1;padding:32px 0;text-align:center">Aucun employé trouvé.</p>';
          document.getElementById('dir-pagination').style.display = 'none';
          return;
        }

        grid.innerHTML = data.employees.map(e => `
          <div class="emp-card" onclick="openEmpModal(${e.id_employe})" style="cursor:pointer">
            ${e.photo
              ? `<img class="emp-avatar" src="${e.photo}" alt="${e.prenom}"/>`
              : `<div class="emp-avatar emp-avatar-init">${(e.prenom||'?')[0]}${(e.nom||'?')[0]}</div>`}
            <p class="emp-name">${e.prenom} ${e.nom}</p>
            <p class="emp-role">${e.poste || '—'}</p>
            <span class="emp-dept">${e.nom_service || '—'}</span>
            <div class="emp-actions" onclick="event.stopPropagation()">
              ${e.email ? `<a href="mailto:${e.email}" class="emp-btn">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Email</a>` : ''}
              ${e.phone_principal ? `<a href="tel:${e.phone_principal}" class="emp-btn">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.6 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>Appeler</a>` : ''}
              <button class="emp-btn fav-btn" onclick="toggleFav(${e.id_employe}, this)" title="Ajouter aux favoris">
                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              </button>
              ${IS_ADMIN ? `<button class="emp-btn" onclick="deleteEmployee(${e.id_employe})" title="Supprimer" style="color:var(--red)">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
              </button>` : ''}
            </div>
          </div>`).join('');

        renderPagination(data.page, data.pages, data.total, data.limit);
      })
      .catch(() => {
        grid.innerHTML = '<p style="color:var(--text-faint);grid-column:1/-1;padding:32px 0;text-align:center">Erreur de connexion au serveur.</p>';
      });
  }

  // ── Pagination ────────────────────────────────────────────────────────────
  function renderPagination(page, pages, total, limit) {
    const pag = document.getElementById('dir-pagination');
    const info = document.getElementById('pagination-info');
    const btns = document.getElementById('pag-btns');

    if (pages <= 1) { pag.style.display = 'none'; return; }
    pag.style.display = 'flex';

    const from = (page - 1) * limit + 1;
    const to   = Math.min(page * limit, total);
    info.textContent = `Affichage ${from} à ${to} sur ${total.toLocaleString('fr-FR')} résultats`;

    let html = `<button class="pag-btn" ${page===1?'disabled':''} onclick="loadEmployees(${page-1})">
      <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></button>`;

    const start = Math.max(1, page - 2);
    const end   = Math.min(pages, page + 2);

    if (start > 1) html += `<button class="pag-btn" onclick="loadEmployees(1)">1</button>${start > 2 ? '<span style="padding:0 4px;color:var(--text-faint)">…</span>' : ''}`;

    for (let i = start; i <= end; i++) {
      html += `<button class="pag-btn ${i === page ? 'active' : ''}" onclick="loadEmployees(${i})">${i}</button>`;
    }

    if (end < pages) {
      html += `${end < pages - 1 ? '<span style="padding:0 4px;color:var(--text-faint)">…</span>' : ''}<button class="pag-btn" onclick="loadEmployees(${pages})">${pages}</button>`;
    }

    html += `<button class="pag-btn" ${page===pages?'disabled':''} onclick="loadEmployees(${page+1})">
      <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></button>`;

    btns.innerHTML = html;
  }

  // ── Modal détail employé ──────────────────────────────────────────────────
  function openEmpModal(id) {
    const modal = document.getElementById('emp-modal');
    document.getElementById('modal-emp-name').textContent = 'Chargement…';
    document.getElementById('modal-emp-body').textContent = '';
    modal.style.display = 'flex';

    fetch(`backend/api.php?action=employee&id=${id}`)
      .then(r => r.json())
      .then(d => {
        if (!d.success) { document.getElementById('modal-emp-body').textContent = 'Employé introuvable.'; return; }
        const e = d.employee;
        document.getElementById('modal-emp-name').textContent = `${e.prenom} ${e.nom}`;
        document.getElementById('modal-emp-body').innerHTML = `
          <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;margin-bottom:20px">
            ${e.photo ? `<img src="${e.photo}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;flex-shrink:0" alt=""/>` : `<div style="width:80px;height:80px;border-radius:50%;background:var(--surface-2);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:var(--red);flex-shrink:0">${(e.prenom||'?')[0]}${(e.nom||'?')[0]}</div>`}
            <div>
              <p style="font-size:15px;font-weight:600;margin-bottom:4px">${e.poste || '—'}</p>
              <p style="color:var(--text-faint);font-size:13px">${e.nom_service || '—'}</p>
              ${e.email ? `<a href="mailto:${e.email}" style="font-size:13px;color:var(--red)">${e.email}</a>` : ''}
            </div>
          </div>
          <div class="profil-fields">
            <div class="profil-field"><label>Département</label><p>${e.nom_service || '—'}</p></div>
            <div class="profil-field"><label>Membre depuis</label><p>${new Date(e.date_creation).toLocaleDateString('fr-FR')}</p></div>
          </div>
          ${e.numeros?.length ? `
          <div style="margin-top:16px">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-faint);margin-bottom:10px">Numéros</p>
            ${e.numeros.map(n => `
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                <span style="font-size:11px;padding:2px 8px;border-radius:var(--r-full);background:var(--surface-2);color:var(--text-muted)">${n.type_numero}</span>
                <a href="tel:${n.numero}" style="font-weight:500;font-size:14px">${n.numero}</a>
                ${n.principal ? '<span style="font-size:10px;color:var(--red);font-weight:700">Principal</span>' : ''}
              </div>`).join('')}
          </div>` : ''}
          <div style="margin-top:20px;display:flex;gap:10px">
            ${e.email ? `<a href="mailto:${e.email}" class="btn-primary">Envoyer un email</a>` : ''}
            ${e.numeros?.[0] ? `<a href="tel:${e.numeros[0].numero}" class="btn-secondary">Appeler</a>` : ''}
            <button class="btn-secondary" onclick="toggleFav(${e.id_employe})">♡ Favoris</button>
          </div>`;
      });
  }

  function closeEmpModal() {
    document.getElementById('emp-modal').style.display = 'none';
  }

  // ── Favoris ───────────────────────────────────────────────────────────────
  function toggleFav(id, btn) {
    fetch('backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=add_favorite&id_employe=${id}`
    }).then(r => r.json()).then(d => {
      if (btn) btn.style.color = d.success ? 'var(--red)' : '';
    });
  }

  // ── Supprimer un employé (admin) ──────────────────────────────────────────
  function deleteEmployee(id) {
    if (!confirm('Supprimer cet employé ? Cette action est irréversible.')) return;
    fetch('backend/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=delete_employee&id_employe=${id}`
    }).then(r => r.json()).then(d => {
      if (d.success) loadEmployees(currentPage);
      else alert(d.error || 'Erreur lors de la suppression');
    });
  }

  // ── Modal ajout ───────────────────────────────────────────────────────────
  <?php if ($current_user['is_admin']): ?>
  document.getElementById('btn-add-emp').addEventListener('click', () => {
    document.getElementById('add-modal').style.display = 'flex';
  });
  function closeAddModal() {
    document.getElementById('add-modal').style.display = 'none';
    document.getElementById('add-employee-form').reset();
    document.getElementById('add-error').style.display = 'none';
  }
  document.getElementById('add-employee-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('add-submit');
    btn.disabled = true; btn.textContent = 'Ajout…';
    const fd = new FormData(this);
    fd.append('action', 'add_employee');
    const phone = fd.get('phone'); fd.delete('phone');
    if (phone) { fd.append('numeros[0][numero]', phone); fd.append('numeros[0][type]', 'mobile'); fd.append('numeros[0][principal]', '1'); }
    fetch('backend/api.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        if (d.success) { closeAddModal(); loadEmployees(1); }
        else { const err = document.getElementById('add-error'); err.textContent = d.error || 'Erreur'; err.style.display = 'block'; }
      })
      .finally(() => { btn.disabled = false; btn.textContent = 'Ajouter'; });
  });
  <?php endif; ?>

  // ── Listeners filtres / recherche ─────────────────────────────────────────
  document.getElementById('dir-search-input').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadEmployees(1), 350);
  });
  document.getElementById('filter-dept').addEventListener('change', () => loadEmployees(1));
  document.getElementById('filter-sort').addEventListener('change', () => loadEmployees(1));

  // ── Init ───────────────────────────────────────────────────────────────────
  loadEmployees(1);
  </script>
  <script src="js/main.js"></script>
</body>
</html>