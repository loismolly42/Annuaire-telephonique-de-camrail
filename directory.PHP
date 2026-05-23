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
  <style>
    :root {
      --red:#C0392B;--red-light:#FAECE7;--red-dark:#922B21;
      --green:#16a34a;--green-bg:#dcfce7;
      --amber:#d97706;--amber-bg:#fef3c7;
      --blue:#2563eb;--blue-bg:#dbeafe;
      --r-sm:6px;--r-md:10px;--r-lg:14px;--r-full:999px;
      --shadow-modal:0 20px 60px rgba(0,0,0,.18),0 4px 16px rgba(0,0,0,.08);
      --text-faint:#9ca3af;--border:#e5e7eb;
    }
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:1000;padding:16px;backdrop-filter:blur(2px)}
    .modal-overlay.open{display:flex}
    .modal{background:#fff;border-radius:var(--r-lg);box-shadow:var(--shadow-modal);width:100%;max-width:700px;max-height:90vh;display:flex;flex-direction:column;animation:modalIn .2s ease}
    .modal-wide{max-width:860px}
    @keyframes modalIn{from{opacity:0;transform:translateY(12px) scale(.98)}to{opacity:1;transform:none}}
    .modal-head{display:flex;align-items:center;gap:12px;padding:20px 24px 16px;border-bottom:1px solid var(--border);flex-shrink:0}
    .modal-head-icon{width:38px;height:38px;border-radius:var(--r-md);background:var(--red-light);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .modal-head-icon svg{width:18px;height:18px;stroke:var(--red);stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .modal-head h3{flex:1;font-size:16px;font-weight:700;color:#111;margin:0}
    .modal-head p{font-size:12px;color:var(--text-faint);margin:2px 0 0}
    .modal-close{width:32px;height:32px;border:none;background:none;border-radius:var(--r-full);cursor:pointer;font-size:18px;color:var(--text-faint);display:flex;align-items:center;justify-content:center;transition:background .15s,color .15s}
    .modal-close:hover{background:#f3f4f6;color:#111}
    .modal-tabs{display:flex;padding:0 24px;border-bottom:1px solid var(--border);flex-shrink:0;overflow-x:auto}
    .modal-tab{padding:12px 14px;font-size:13px;font-weight:500;color:var(--text-faint);border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;display:flex;align-items:center;gap:6px;white-space:nowrap}
    .modal-tab svg{width:14px;height:14px;stroke:currentColor;stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .modal-tab.active-red{color:var(--red);border-bottom-color:var(--red)}
    .modal-tab:hover:not(.active-red){color:#374151}
    .modal-body{padding:24px;overflow-y:auto;flex:1}
    .tab-panel{display:none}.tab-panel.active{display:block}
    .form-section{margin-bottom:24px}
    .form-section-title{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-faint);margin-bottom:12px;display:flex;align-items:center;gap:8px}
    .form-section-title::after{content:'';flex:1;height:1px;background:var(--border)}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .form-grid .span2{grid-column:1/-1}
    .f-field{display:flex;flex-direction:column;gap:5px}
    .f-field label{font-size:12px;font-weight:600;color:#374151;display:flex;align-items:center;gap:4px}
    .f-field label .req{color:var(--red)}
    .f-field input,.f-field select,.f-field textarea{border:1px solid var(--border);border-radius:var(--r-sm);padding:9px 12px;font-size:14px;font-family:inherit;color:#111;background:#fff;transition:border-color .15s,box-shadow .15s;outline:none;width:100%}
    .f-field input:focus,.f-field select:focus,.f-field textarea:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(192,57,43,.12)}
    .f-field input::placeholder,.f-field textarea::placeholder{color:#d1d5db}
    .f-field textarea{resize:vertical;min-height:80px}
    .phones-list{display:flex;flex-direction:column;gap:10px}
    .phone-row{display:flex;align-items:center;gap:8px}
    .phone-row select{width:110px;flex-shrink:0}
    .phone-row input{flex:1}
    .btn-principal{padding:0 10px;height:38px;border:1px solid var(--border);border-radius:var(--r-sm);background:#fff;font-size:11px;font-weight:600;cursor:pointer;color:var(--text-faint);white-space:nowrap;transition:all .15s}
    .btn-principal.is-principal{background:var(--green-bg);border-color:var(--green);color:var(--green)}
    .btn-del-phone{width:32px;height:32px;border:none;background:none;cursor:pointer;color:var(--text-faint);border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;transition:background .15s,color .15s;flex-shrink:0}
    .btn-del-phone:hover{background:var(--red-light);color:var(--red)}
    .btn-add-row{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--blue);background:var(--blue-bg);border:none;border-radius:var(--r-sm);padding:7px 12px;cursor:pointer;margin-top:4px;transition:opacity .15s}
    .btn-add-row:hover{opacity:.85}
    .form-alert-modal{display:none;background:var(--red-light);color:var(--red);border:1px solid rgba(192,57,43,.25);border-radius:var(--r-sm);padding:10px 14px;font-size:13px;font-weight:500;margin-top:8px;align-items:center;gap:8px}
    .form-alert-modal.visible{display:flex}
    .form-alert-modal svg{width:16px;height:16px;stroke:currentColor;stroke-width:2;fill:none;flex-shrink:0}
    .form-success-modal{display:none;background:var(--green-bg);color:var(--green);border:1px solid rgba(22,163,74,.25);border-radius:var(--r-sm);padding:10px 14px;font-size:13px;font-weight:500;align-items:center;gap:8px}
    .form-success-modal.visible{display:flex}
    .form-success-modal svg{width:16px;height:16px;stroke:currentColor;stroke-width:2;fill:none;flex-shrink:0}
    .modal-foot{padding:16px 24px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-shrink:0}
    .btn-cancel{padding:9px 18px;border:1px solid var(--border);border-radius:var(--r-sm);background:#fff;font-size:14px;font-weight:500;cursor:pointer;color:#374151;transition:background .15s}
    .btn-cancel:hover{background:#f9fafb}
    .btn-submit-modal{padding:9px 20px;border:none;border-radius:var(--r-sm);background:var(--red);color:#fff;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;transition:background .15s}
    .btn-submit-modal:hover:not(:disabled){background:var(--red-dark)}
    .btn-submit-modal:disabled{opacity:.55;cursor:not-allowed}
    .btn-submit-modal svg{width:16px;height:16px;stroke:#fff;stroke-width:2.5;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .spinner-sm{width:15px;height:15px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    @media(max-width:600px){.form-grid{grid-template-columns:1fr}.form-grid .span2{grid-column:auto}.phone-row select{width:90px}}
  </style>
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
          <button class="btn-primary" onclick="openAddModal()">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajouter un employé
          </button>
          <?php endif; ?>
        </div>
      </div>

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

      <div class="dir-grid" id="emp-grid">
        <?php for ($i = 0; $i < 6; $i++): ?>
        <div class="emp-card skeleton" style="height:220px"></div>
        <?php endfor; ?>
      </div>

      <div class="dir-pagination" id="dir-pagination" style="display:none">
        <p id="pagination-info"></p>
        <div class="pag-btns" id="pag-btns"></div>
      </div>
    </main>
  </div>

  <!-- ── Modal détail employé ─────────────────────────────────────────────── -->
  <div class="modal-overlay" id="emp-modal" onclick="if(event.target===this)closeEmpModal()">
    <div class="modal modal-wide">
      <div class="modal-head">
        <div class="modal-head-icon">
          <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div style="flex:1">
          <h3 id="modal-emp-name">Chargement…</h3>
          <p id="modal-emp-poste" style="font-size:12px;color:var(--text-faint);margin:2px 0 0"></p>
        </div>
        <button class="modal-close" onclick="closeEmpModal()">✕</button>
      </div>
      <div id="modal-emp-body" style="padding:24px">Chargement…</div>
    </div>
  </div>

  <!-- ── Modal ajout employé (admin) — même design que admin.php ─────────── -->
  <?php if ($current_user['is_admin']): ?>
  <div class="modal-overlay" id="add-modal" onclick="if(event.target===this)closeAddModal()">
    <div class="modal">
      <div class="modal-head">
        <div class="modal-head-icon">
          <svg viewBox="0 0 24 24">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/>
            <line x1="16" y1="11" x2="22" y2="11"/>
          </svg>
        </div>
        <div style="flex:1">
          <h3>Nouvel employé</h3>
          <p>Remplissez les informations de l'employé</p>
        </div>
        <button class="modal-close" onclick="closeAddModal()">✕</button>
      </div>

      <div class="modal-tabs" id="add-tabs">
        <button class="modal-tab active-red" data-tab="infos" onclick="switchAddTab('infos',this)">
          <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Informations
        </button>
        <button class="modal-tab" data-tab="contacts" onclick="switchAddTab('contacts',this)">
          <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.21 15a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          Téléphones
        </button>
        <button class="modal-tab" data-tab="localisation" onclick="switchAddTab('localisation',this)">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Localisation
        </button>
      </div>

      <div class="modal-body">
        <form id="add-employee-form" autocomplete="off" onsubmit="return false">

          <!-- TAB Informations -->
          <div class="tab-panel active" id="add-tab-infos">
            <div class="form-section">
              <div class="form-section-title">Identité</div>
              <div class="form-grid">
                <div class="f-field">
                  <label>Prénom <span class="req">*</span></label>
                  <input id="af-prenom" name="prenom" required placeholder="Marie"/>
                </div>
                <div class="f-field">
                  <label>Nom <span class="req">*</span></label>
                  <input id="af-nom" name="nom" required placeholder="DUPONT"/>
                </div>
                <div class="f-field span2">
                  <label>Adresse email <span class="req">*</span></label>
                  <input id="af-email" name="email" type="email" required placeholder="m.dupont@camrail.cm"/>
                </div>
              </div>
            </div>
            <div class="form-section">
              <div class="form-section-title">Poste &amp; affectation</div>
              <div class="form-grid">
                <div class="f-field">
                  <label>Poste / Titre</label>
                  <input id="af-poste" name="poste" placeholder="Ingénieur Ferroviaire"/>
                </div>
                <div class="f-field">
                  <label>Département</label>
                  <select id="af-dept" name="id_service">
                    <option value="">— Choisir —</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-section">
              <div class="form-section-title">Photo de profil</div>
              <div class="form-grid">
                <div class="f-field span2">
                  <label>URL de la photo (optionnel)</label>
                  <input id="af-photo" name="photo" placeholder="https://…"/>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB Téléphones -->
          <div class="tab-panel" id="add-tab-contacts">
            <div class="form-section">
              <div class="form-section-title">Numéros de téléphone</div>
              <div class="phones-list" id="add-phones-list"></div>
              <button type="button" class="btn-add-row" onclick="addPhoneRowDir()">
                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter un numéro
              </button>
            </div>
            <p style="font-size:12px;color:var(--text-faint);margin-top:8px">Cochez <strong>Principal</strong> sur le numéro prioritaire.</p>
          </div>

          <!-- TAB Localisation -->
          <div class="tab-panel" id="add-tab-localisation">
            <div class="form-section">
              <div class="form-section-title">Localisation existante</div>
              <div class="form-grid">
                <div class="f-field span2">
                  <label>Associer à une localisation existante</label>
                  <select id="af-localisation" name="id_localisation">
                    <option value="">— Aucune / Choisir —</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-section">
              <div class="form-section-title">Ou créer une nouvelle localisation</div>
              <div class="form-grid">
                <div class="f-field">
                  <label>Nom</label>
                  <input id="af-loc-nom" name="loc_nom" placeholder="Bureau DG"/>
                </div>
                <div class="f-field">
                  <label>Ville</label>
                  <input id="af-loc-ville" name="loc_ville" placeholder="Yaoundé"/>
                </div>
                <div class="f-field">
                  <label>Site</label>
                  <input id="af-loc-site" name="loc_site" placeholder="Siège social"/>
                </div>
                <div class="f-field">
                  <label>Bâtiment</label>
                  <input id="af-loc-batiment" name="loc_batiment" placeholder="Bât. A"/>
                </div>
                <div class="f-field">
                  <label>Étage</label>
                  <input id="af-loc-etage" name="loc_etage" placeholder="2ème étage"/>
                </div>
                <div class="f-field">
                  <label>Bureau</label>
                  <input id="af-loc-bureau" name="loc_bureau" placeholder="214"/>
                </div>
                <div class="f-field span2">
                  <label>Description</label>
                  <textarea id="af-loc-desc" name="loc_description" placeholder="Notes complémentaires…"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="form-alert-modal" id="add-form-error">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span id="add-form-error-msg"></span>
          </div>
          <div class="form-success-modal" id="add-form-success">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <span id="add-form-success-msg"></span>
          </div>
        </form>
      </div>

      <div class="modal-foot">
        <button type="button" class="btn-cancel" onclick="closeAddModal()">Annuler</button>
        <button type="button" class="btn-submit-modal" id="add-submit-btn" onclick="submitAddForm()">
          <svg viewBox="0 0 24 24" id="add-submit-icon">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span id="add-submit-label">Ajouter l'employé</span>
        </button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <script>
  const IS_ADMIN = <?= $current_user['is_admin'] ? 'true' : 'false' ?>;
  let currentPage = 1;
  let searchTimer;
  let _dirPhoneIdx = 0;

  function escHtml(str) {
    return String(str||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  // ── Fetch avec credentials ────────────────────────────────────────────────
  function apiFetch(url, options={}) {
    return fetch(url, { credentials: 'include', ...options });
  }

  // ── Charger départements ──────────────────────────────────────────────────
  apiFetch('backend/api.php?action=departments')
    .then(r => r.json())
    .then(d => {
      if (!d.success) return;
      const sel  = document.getElementById('filter-dept');
      const asel = document.getElementById('af-dept');
      d.departments.forEach(s => {
        const o1 = new Option(`${escHtml(s.nom_service)} (${s.nb_employes})`, s.id_service);
        const o2 = new Option(escHtml(s.nom_service), s.id_service);
        sel.appendChild(o1);
        if (asel) asel.appendChild(o2);
      });
    });

  // Charger localisations dans le select du modal ajout
  function loadLocalisationsDir() {
    apiFetch('backend/api.php?action=localisations')
      .then(r => r.json())
      .then(d => {
        if (!d.success) return;
        const sel = document.getElementById('af-localisation');
        if (!sel) return;
        sel.innerHTML = '<option value="">— Aucune / Choisir —</option>';
        d.localisations.forEach(l => {
          sel.appendChild(new Option(
            escHtml(l.nom_localisation) + (l.ville ? ' — ' + escHtml(l.ville) : ''),
            l.id_localisation
          ));
        });
      });
  }

  // ── Charger employés ──────────────────────────────────────────────────────
  function loadEmployees(page = 1) {
    currentPage = page;
    const q    = document.getElementById('dir-search-input').value.trim();
    const dept = document.getElementById('filter-dept').value;
    const sort = document.getElementById('filter-sort').value;
    let url = `backend/api.php?action=employees&page=${page}&limit=24&sort=${sort}`;
    if (q)    url += `&q=${encodeURIComponent(q)}`;
    if (dept) url += `&dept=${dept}`;

    const grid = document.getElementById('emp-grid');
    grid.innerHTML = Array(6).fill('<div class="emp-card skeleton" style="height:220px"></div>').join('');

    apiFetch(url)
      .then(r => r.json())
      .then(data => {
        if (!data.success) {
          grid.innerHTML = '<p style="color:var(--text-faint);grid-column:1/-1;padding:32px 0;text-align:center">Erreur de chargement.</p>';
          return;
        }
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
              ? `<img class="emp-avatar" src="${escHtml(e.photo)}" alt="${escHtml(e.prenom)}"/>`
              : `<div class="emp-avatar emp-avatar-init">${(e.prenom||'?')[0]}${(e.nom||'?')[0]}</div>`}
            <p class="emp-name">${escHtml(e.prenom)} ${escHtml(e.nom)}</p>
            <p class="emp-role">${escHtml(e.poste || '—')}</p>
            <span class="emp-dept">${escHtml(e.nom_service || '—')}</span>
            <div class="emp-actions" onclick="event.stopPropagation()">
              ${e.email ? `<a href="mailto:${escHtml(e.email)}" class="emp-btn">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Email</a>` : ''}
              ${e.phone_principal ? `<a href="tel:${escHtml(e.phone_principal)}" class="emp-btn">
                <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.6 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>Appeler</a>` : ''}
              <button class="emp-btn fav-btn" onclick="toggleFav(${e.id_employe},this)" title="Favoris">
                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              </button>
              ${IS_ADMIN ? `<button class="emp-btn" onclick="deleteEmployee(${e.id_employe})" title="Supprimer" style="color:var(--red)">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
              </button>` : ''}
            </div>
          </div>`).join('');

        renderPagination(data.page, data.pages, data.total, data.limit);
      })
      .catch(() => {
        grid.innerHTML = '<p style="color:var(--text-faint);grid-column:1/-1;padding:32px 0;text-align:center">Erreur de connexion.</p>';
      });
  }

  // ── Pagination ────────────────────────────────────────────────────────────
  function renderPagination(page, pages, total, limit) {
    const pag = document.getElementById('dir-pagination');
    if (pages <= 1) { pag.style.display = 'none'; return; }
    pag.style.display = 'flex';
    const from = (page-1)*limit+1, to = Math.min(page*limit,total);
    document.getElementById('pagination-info').textContent = `Affichage ${from} à ${to} sur ${total.toLocaleString('fr-FR')}`;
    const start = Math.max(1,page-2), end = Math.min(pages,page+2);
    let html = `<button class="pag-btn" ${page===1?'disabled':''} onclick="loadEmployees(${page-1})">‹</button>`;
    if (start>1) html+=`<button class="pag-btn" onclick="loadEmployees(1)">1</button>${start>2?'<span>…</span>':''}`;
    for(let i=start;i<=end;i++) html+=`<button class="pag-btn ${i===page?'active':''}" onclick="loadEmployees(${i})">${i}</button>`;
    if(end<pages) html+=`${end<pages-1?'<span>…</span>':''}<button class="pag-btn" onclick="loadEmployees(${pages})">${pages}</button>`;
    html+=`<button class="pag-btn" ${page===pages?'disabled':''} onclick="loadEmployees(${page+1})">›</button>`;
    document.getElementById('pag-btns').innerHTML = html;
  }

  // ── Modal détail ──────────────────────────────────────────────────────────
  function openEmpModal(id) {
    const modal = document.getElementById('emp-modal');
    document.getElementById('modal-emp-name').textContent = 'Chargement…';
    document.getElementById('modal-emp-poste').textContent = '';
    document.getElementById('modal-emp-body').innerHTML = '<p style="color:var(--text-faint)">Chargement…</p>';
    modal.classList.add('open');
    apiFetch(`backend/api.php?action=employee&id=${id}`)
      .then(r => r.json())
      .then(d => {
        if (!d.success) { document.getElementById('modal-emp-body').textContent = 'Employé introuvable.'; return; }
        const e = d.employee;
        document.getElementById('modal-emp-name').textContent  = `${e.prenom} ${e.nom}`;
        document.getElementById('modal-emp-poste').textContent = [e.poste, e.nom_service].filter(Boolean).join(' · ');
        document.getElementById('modal-emp-body').innerHTML = `
          <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;margin-bottom:20px">
            ${e.photo
              ? `<img src="${escHtml(e.photo)}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;flex-shrink:0" alt=""/>`
              : `<div style="width:80px;height:80px;border-radius:50%;background:var(--red-light);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:var(--red);flex-shrink:0">${(e.prenom||'?')[0]}${(e.nom||'?')[0]}</div>`}
            <div>
              <p style="font-size:15px;font-weight:600;margin-bottom:4px">${escHtml(e.poste||'—')}</p>
              <p style="color:var(--text-faint);font-size:13px">${escHtml(e.nom_service||'—')}</p>
              ${e.email ? `<a href="mailto:${escHtml(e.email)}" style="font-size:13px;color:var(--red)">${escHtml(e.email)}</a>` : ''}
            </div>
          </div>
          <div class="profil-fields">
            <div class="profil-field"><label>Département</label><p>${escHtml(e.nom_service||'—')}</p></div>
            <div class="profil-field"><label>Membre depuis</label><p>${new Date(e.date_creation).toLocaleDateString('fr-FR')}</p></div>
          </div>
          ${e.numeros?.length ? `
          <div style="margin-top:16px">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-faint);margin-bottom:10px">Numéros</p>
            ${e.numeros.map(n=>`
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                <span style="font-size:11px;padding:2px 8px;border-radius:999px;background:#f3f4f6;color:#6b7280">${n.type_numero}</span>
                <a href="tel:${escHtml(n.numero)}" style="font-weight:500;font-size:14px">${escHtml(n.numero)}</a>
                ${n.principal=='1'?'<span style="font-size:10px;color:var(--red);font-weight:700">Principal</span>':''}
              </div>`).join('')}
          </div>` : ''}
          <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap">
            ${e.email ? `<a href="mailto:${escHtml(e.email)}" class="btn-primary">Envoyer un email</a>` : ''}
            ${e.numeros?.[0] ? `<a href="tel:${escHtml(e.numeros[0].numero)}" class="btn-secondary">Appeler</a>` : ''}
            <button class="btn-secondary" onclick="toggleFav(${e.id_employe})">♡ Favoris</button>
          </div>`;
      });
  }

  function closeEmpModal() {
    document.getElementById('emp-modal').classList.remove('open');
  }

  // ── Favoris ───────────────────────────────────────────────────────────────
  function toggleFav(id, btn) {
    apiFetch('backend/api.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: `action=add_favorite&id_employe=${id}`
    }).then(r=>r.json()).then(d=>{
      if (btn) btn.style.color = d.action==='added' ? 'var(--red)' : '';
    });
  }

  // ── Supprimer employé ─────────────────────────────────────────────────────
  function deleteEmployee(id) {
    if (!confirm('Supprimer cet employé ? Cette action est irréversible.')) return;
    apiFetch('backend/api.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`action=delete_employee&id_employe=${id}`
    }).then(r=>r.json()).then(d=>{
      if (d.success) loadEmployees(currentPage);
      else alert(d.error||'Erreur');
    });
  }

  // ── Modal ajout — tabs ────────────────────────────────────────────────────
  function switchAddTab(name, btn) {
    document.querySelectorAll('[id^="add-tab-"]').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('#add-tabs .modal-tab').forEach(b=>b.classList.remove('active-red'));
    document.getElementById('add-tab-'+name).classList.add('active');
    btn.classList.add('active-red');
  }

  // ── Modal ajout — téléphones ──────────────────────────────────────────────
  function addPhoneRowDir(type='mobile', numero='', principal=false) {
    const idx = _dirPhoneIdx++;
    const row = document.createElement('div');
    row.className='phone-row'; row.dataset.idx=idx;
    row.innerHTML=`
      <select name="numeros[${idx}][type]">
        <option value="mobile" ${type==='mobile'?'selected':''}>Mobile</option>
        <option value="fixe"   ${type==='fixe'  ?'selected':''}>Fixe</option>
        <option value="whatsapp" ${type==='whatsapp'?'selected':''}>WhatsApp</option>
        <option value="poste"  ${type==='poste' ?'selected':''}>Poste</option>
      </select>
      <input type="tel" name="numeros[${idx}][numero]" value="${escHtml(numero)}" placeholder="+237 6xx xx xx xx"/>
      <button type="button" class="btn-principal ${principal?'is-principal':''}"
              name="numeros[${idx}][principal]" value="${principal?'1':'0'}"
              onclick="togglePrincipalDir(this)">
        ${principal?'★ Principal':'☆ Principal'}
      </button>
      <button type="button" class="btn-del-phone" onclick="this.closest('.phone-row').remove()">
        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>`;
    document.getElementById('add-phones-list').appendChild(row);
  }

  function togglePrincipalDir(btn) {
    document.querySelectorAll('#add-phones-list .btn-principal').forEach(b=>{
      b.classList.remove('is-principal'); b.value='0'; b.textContent='☆ Principal';
    });
    btn.classList.add('is-principal'); btn.value='1'; btn.textContent='★ Principal';
  }

  // ── Ouvrir/fermer modal ajout ─────────────────────────────────────────────
  function openAddModal() {
    document.getElementById('add-employee-form').reset();
    document.getElementById('add-phones-list').innerHTML='';
    _dirPhoneIdx=0;
    document.getElementById('add-form-error').classList.remove('visible');
    document.getElementById('add-form-success').classList.remove('visible');
    switchAddTab('infos', document.querySelector('#add-tabs [data-tab="infos"]'));
    loadLocalisationsDir();
    addPhoneRowDir('mobile','',true);
    document.getElementById('add-modal').classList.add('open');
  }

  function closeAddModal() {
    document.getElementById('add-modal').classList.remove('open');
  }

  // ── Soumettre formulaire ajout ────────────────────────────────────────────
  function submitAddForm() {
    document.getElementById('add-form-error').classList.remove('visible');
    document.getElementById('add-form-success').classList.remove('visible');

    const prenom = document.getElementById('af-prenom').value.trim();
    const nom    = document.getElementById('af-nom').value.trim();
    const email  = document.getElementById('af-email').value.trim();
    if (!prenom||!nom||!email) {
      document.getElementById('add-form-error-msg').textContent='Prénom, Nom et Email sont obligatoires.';
      document.getElementById('add-form-error').classList.add('visible');
      switchAddTab('infos',document.querySelector('#add-tabs [data-tab="infos"]'));
      return;
    }

    const btn = document.getElementById('add-submit-btn');
    const lbl = document.getElementById('add-submit-label');
    btn.disabled=true;
    document.getElementById('add-submit-icon').outerHTML='<span class="spinner-sm" id="add-submit-icon"></span>';
    lbl.textContent='Ajout en cours…';

    const fd = new FormData(document.getElementById('add-employee-form'));
    fd.set('action','add_employee');
    document.querySelectorAll('#add-phones-list .btn-principal').forEach(b=>{
      fd.set(`numeros[${b.closest('.phone-row').dataset.idx}][principal]`, b.value);
    });

    apiFetch('backend/api.php', {method:'POST', body:fd})
      .then(r=>r.json())
      .then(d=>{
        if (d.success) {
          document.getElementById('add-form-success-msg').textContent=`✓ Employé ajouté (ID : ${d.id_employe})`;
          document.getElementById('add-form-success').classList.add('visible');
          setTimeout(()=>{ closeAddModal(); loadEmployees(1); }, 1400);
        } else {
          const ERRORS={missing_fields:'Champs manquants.',invalid_email:'Email invalide.',email_already_used:'Email déjà utilisé.',db_error:'Erreur base de données.'};
          document.getElementById('add-form-error-msg').textContent=ERRORS[d.error]||d.error||'Erreur.';
          document.getElementById('add-form-error').classList.add('visible');
        }
      })
      .catch(()=>{
        document.getElementById('add-form-error-msg').textContent='Erreur réseau.';
        document.getElementById('add-form-error').classList.add('visible');
      })
      .finally(()=>{
        btn.disabled=false;
        const sp=document.getElementById('add-submit-icon');
        if(sp&&sp.classList.contains('spinner-sm'))
          sp.outerHTML='<svg viewBox="0 0 24 24" id="add-submit-icon" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>';
        document.getElementById('add-submit-label').textContent="Ajouter l'employé";
      });
  }

  // ── Filtres / recherche ───────────────────────────────────────────────────
  document.getElementById('dir-search-input').addEventListener('input',()=>{
    clearTimeout(searchTimer);
    searchTimer=setTimeout(()=>loadEmployees(1),350);
  });
  document.getElementById('filter-dept').addEventListener('change',()=>loadEmployees(1));
  document.getElementById('filter-sort').addEventListener('change',()=>loadEmployees(1));
  document.addEventListener('keydown',e=>{ if(e.key==='Escape'){closeEmpModal();closeAddModal&&closeAddModal();} });

  // ── Init ──────────────────────────────────────────────────────────────────
  loadEmployees(1);
  </script>
  <script src="js/main.js"></script>
</body>
</html>