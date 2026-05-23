<?php
$require_admin = true;
$active_page   = 'admin';
require_once __DIR__ . '/backend/session_guard.php';
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Administration — Camrail Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/main.css"/>
  <style>
    :root {
      --red:#C0392B;--red-light:#FAECE7;--red-dark:#922B21;
      --green:#16a34a;--green-bg:#dcfce7;
      --amber:#d97706;--amber-bg:#fef3c7;
      --blue:#2563eb;--blue-bg:#dbeafe;
      --purple:#7c3aed;--purple-bg:#ede9fe;
      --r-sm:6px;--r-md:10px;--r-lg:14px;--r-full:999px;
      --shadow-modal:0 20px 60px rgba(0,0,0,.18),0 4px 16px rgba(0,0,0,.08);
      --text-faint:#9ca3af;--border:#e5e7eb;
    }

    /* ── Overlay / Modal base ── */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:1000;padding:16px;backdrop-filter:blur(2px)}
    .modal-overlay.open{display:flex}
    .modal{background:#fff;border-radius:var(--r-lg);box-shadow:var(--shadow-modal);width:100%;max-width:700px;max-height:90vh;display:flex;flex-direction:column;animation:modalIn .2s ease}
    @keyframes modalIn{from{opacity:0;transform:translateY(12px) scale(.98)}to{opacity:1;transform:none}}

    /* ── Modal head ── */
    .modal-head{display:flex;align-items:center;gap:12px;padding:20px 24px 16px;border-bottom:1px solid var(--border);flex-shrink:0}
    .modal-head-icon{width:38px;height:38px;border-radius:var(--r-md);display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .modal-head-icon.red{background:var(--red-light)}
    .modal-head-icon.purple{background:var(--purple-bg)}
    .modal-head-icon svg{width:18px;height:18px;stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .modal-head-icon.red svg{stroke:var(--red)}
    .modal-head-icon.purple svg{stroke:var(--purple)}
    .modal-head h3{flex:1;font-size:16px;font-weight:700;color:#111;margin:0}
    .modal-head p{font-size:12px;color:var(--text-faint);margin:2px 0 0}
    .modal-close{width:32px;height:32px;border:none;background:none;border-radius:var(--r-full);cursor:pointer;font-size:18px;color:var(--text-faint);display:flex;align-items:center;justify-content:center;transition:background .15s,color .15s}
    .modal-close:hover{background:#f3f4f6;color:#111}

    /* ── Modal tabs ── */
    .modal-tabs{display:flex;padding:0 24px;border-bottom:1px solid var(--border);flex-shrink:0;overflow-x:auto}
    .modal-tab{padding:12px 14px;font-size:13px;font-weight:500;color:var(--text-faint);border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;display:flex;align-items:center;gap:6px;white-space:nowrap}
    .modal-tab svg{width:14px;height:14px;stroke:currentColor;stroke-width:2;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .modal-tab.active-red{color:var(--red);border-bottom-color:var(--red)}
    .modal-tab.active-purple{color:var(--purple);border-bottom-color:var(--purple)}
    .modal-tab:hover:not(.active-red):not(.active-purple){color:#374151}

    /* ── Modal body ── */
    .modal-body{padding:24px;overflow-y:auto;flex:1}
    .tab-panel{display:none}.tab-panel.active{display:block}

    /* ── Form ── */
    .form-section{margin-bottom:24px}
    .form-section-title{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-faint);margin-bottom:12px;display:flex;align-items:center;gap:8px}
    .form-section-title::after{content:'';flex:1;height:1px;background:var(--border)}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .form-grid .span2{grid-column:1/-1}
    .form-grid .span3{grid-column:1/-1}
    .f-field{display:flex;flex-direction:column;gap:5px}
    .f-field label{font-size:12px;font-weight:600;color:#374151;display:flex;align-items:center;gap:4px}
    .f-field label .req{color:var(--red)}
    .f-field input,.f-field select,.f-field textarea{border:1px solid var(--border);border-radius:var(--r-sm);padding:9px 12px;font-size:14px;font-family:inherit;color:#111;background:#fff;transition:border-color .15s,box-shadow .15s;outline:none;width:100%}
    .f-field input:focus,.f-field select:focus,.f-field textarea:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(192,57,43,.12)}
    .f-field-purple input:focus,.f-field-purple select:focus{border-color:var(--purple);box-shadow:0 0 0 3px rgba(124,58,237,.12)}
    .f-field input::placeholder,.f-field textarea::placeholder{color:#d1d5db}
    .f-field textarea{resize:vertical;min-height:80px}

    /* ── Phones ── */
    .phones-list{display:flex;flex-direction:column;gap:10px}
    .phone-row{display:flex;align-items:center;gap:8px}
    .phone-row select{width:110px;flex-shrink:0}
    .phone-row input{flex:1}
    .btn-principal{padding:0 10px;height:38px;border:1px solid var(--border);border-radius:var(--r-sm);background:#fff;font-size:11px;font-weight:600;cursor:pointer;color:var(--text-faint);white-space:nowrap;transition:all .15s}
    .btn-principal.is-principal{background:var(--green-bg);border-color:var(--green);color:var(--green)}
    .btn-del-phone{width:32px;height:32px;border:none;background:none;cursor:pointer;color:var(--text-faint);border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;transition:background .15s,color .15s;flex-shrink:0}
    .btn-del-phone:hover{background:var(--red-light);color:var(--red)}
    .btn-add-row{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;border:none;border-radius:var(--r-sm);padding:7px 12px;cursor:pointer;margin-top:4px;transition:opacity .15s}
    .btn-add-row.blue{color:var(--blue);background:var(--blue-bg)}
    .btn-add-row:hover{opacity:.85}

    /* ── Password strength ── */
    .pwd-strength{height:4px;border-radius:2px;margin-top:6px;background:#e5e7eb;overflow:hidden}
    .pwd-strength-bar{height:100%;width:0;border-radius:2px;transition:width .3s,background .3s}

    /* ── Role badge ── */
    .role-info{background:var(--purple-bg);color:var(--purple);border:1px solid rgba(124,58,237,.2);border-radius:var(--r-sm);padding:10px 14px;font-size:13px;display:flex;align-items:flex-start;gap:8px;margin-top:4px}
    .role-info svg{width:16px;height:16px;stroke:currentColor;stroke-width:2;fill:none;flex-shrink:0;margin-top:1px}

    /* ── Alert / success ── */
    .form-alert{display:none;background:var(--red-light);color:var(--red);border:1px solid rgba(192,57,43,.25);border-radius:var(--r-sm);padding:10px 14px;font-size:13px;font-weight:500;margin-top:8px}
    .form-alert.visible{display:flex;align-items:center;gap:8px}
    .form-alert svg{width:16px;height:16px;stroke:currentColor;stroke-width:2;fill:none;flex-shrink:0}
    .form-success{display:none;background:var(--green-bg);color:var(--green);border:1px solid rgba(22,163,74,.25);border-radius:var(--r-sm);padding:10px 14px;font-size:13px;font-weight:500;align-items:center;gap:8px}
    .form-success.visible{display:flex}
    .form-success svg{width:16px;height:16px;stroke:currentColor;stroke-width:2;fill:none;flex-shrink:0}

    /* ── Modal footer ── */
    .modal-foot{padding:16px 24px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-shrink:0}
    .btn-cancel{padding:9px 18px;border:1px solid var(--border);border-radius:var(--r-sm);background:#fff;font-size:14px;font-weight:500;cursor:pointer;color:#374151;transition:background .15s}
    .btn-cancel:hover{background:#f9fafb}
    .btn-submit{padding:9px 20px;border:none;border-radius:var(--r-sm);color:#fff;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;transition:background .15s,opacity .15s}
    .btn-submit.red{background:var(--red)}
    .btn-submit.red:hover:not(:disabled){background:var(--red-dark)}
    .btn-submit.purple{background:var(--purple)}
    .btn-submit.purple:hover:not(:disabled){background:#6d28d9}
    .btn-submit:disabled{opacity:.55;cursor:not-allowed}
    .btn-submit svg{width:16px;height:16px;stroke:#fff;stroke-width:2.5;fill:none;stroke-linecap:round;stroke-linejoin:round}
    .spinner{width:15px;height:15px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .edit-badge{font-size:11px;font-weight:700;border-radius:var(--r-full);padding:3px 10px}
    .edit-badge.amber{background:var(--amber-bg);color:var(--amber)}
    .edit-badge.purple{background:var(--purple-bg);color:var(--purple)}

    /* ── Employee-linked info box ── */
    .emp-linked-box{background:#f8fafc;border:1px solid var(--border);border-radius:var(--r-sm);padding:12px 14px;display:flex;align-items:center;gap:12px}
    .emp-linked-box .emp-avatar{width:36px;height:36px;border-radius:50%;background:var(--red-light);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--red);flex-shrink:0}
    .emp-linked-box .emp-info p{margin:0;font-size:13px;font-weight:600;color:#111}
    .emp-linked-box .emp-info span{font-size:12px;color:var(--text-faint)}

    @media(max-width:600px){
      .form-grid{grid-template-columns:1fr}
      .form-grid .span2,.form-grid .span3{grid-column:auto}
      .phone-row select{width:90px}
    }
  </style>
</head>
<body data-page="admin">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="content">

      <div class="dir-header">
        <div><h2>Administration</h2><p>Tableau de bord — tâches en attente et journal d'activité.</p></div>
        <div style="display:flex;gap:10px;">
          <!-- Bouton Nouvel utilisateur -->
          <button class="btn-secondary" onclick="openUserModal()" style="display:flex;align-items:center;gap:6px;">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M19 8v6M22 11h-6"/>
            </svg>
            Nouvel utilisateur
          </button>
          <!-- Bouton Nouvel employé -->
          <button class="btn-primary" onclick="openEmployeeModal()">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvel employé
          </button>
        </div>
      </div>

      <div class="admin-stats">
        <div class="stat-card"><div class="stat-card-top"><div class="stat-icon red"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div></div><p class="stat-label">Total Employés</p><p class="stat-value" id="stat-employes">—</p></div>
        <div class="stat-card"><div class="stat-card-top"><div class="stat-icon amber"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><span class="stat-trend warn" id="urgency-label">…</span></div><p class="stat-label">Demandes en attente</p><p class="stat-value" id="stat-attente">—</p></div>
        <div class="stat-card"><div class="stat-card-top"><div class="stat-icon blue"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></div></div><p class="stat-label">Départements</p><p class="stat-value" id="stat-services">—</p></div>
        <div class="stat-card"><div class="stat-card-top"><div class="stat-icon green"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div></div><p class="stat-label">Utilisateurs système</p><p class="stat-value" id="stat-users">—</p></div>
      </div>

      <div class="admin-grid">
        <div>
          <div class="admin-card">
            <div class="admin-card-header">
              <h3 class="admin-card-title">File de validation</h3>
              <span id="queue-count" style="background:var(--red-light);color:var(--red);font-size:11px;font-weight:700;padding:3px 8px;border-radius:var(--r-full)">…</span>
            </div>
            <div id="validation-queue"><p style="color:var(--text-faint);padding:12px 0">Chargement…</p></div>
          </div>
          <div class="admin-card" style="margin-top:16px">
            <div class="admin-card-header"><h3 class="admin-card-title">Journal d'activité</h3></div>
            <table class="log-table">
              <thead><tr><th>Action</th><th>Utilisateur</th><th>Cible</th><th>Date</th><th>Statut</th></tr></thead>
              <tbody id="activity-log-body"><tr><td colspan="5" style="color:var(--text-faint);text-align:center;padding:16px">Chargement…</td></tr></tbody>
            </table>
          </div>
        </div>
        <div>
          <div class="admin-card" style="padding:20px;margin-bottom:16px">
            <h3 class="admin-card-title" style="margin-bottom:16px">Outils de gestion</h3>
            <div class="tools-grid">
              <button class="tool-btn" onclick="openEmployeeModal()"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>Ajouter</button>
              <a href="backend/export.php?format=csv" class="tool-btn"><svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Export CSV</a>
              <button class="tool-btn" onclick="loadActivityLog(200)"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Audit complet</button>
              <a href="backend/export.php?format=pdf" class="tool-btn"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Rapport PDF</a>
            </div>
          </div>
          <div class="admin-card" style="padding:20px">
            <h3 class="admin-card-title" style="margin-bottom:16px">Répartition du personnel</h3>
            <ul class="dist-list" id="repartition-list"><li style="color:var(--text-faint)">Chargement…</li></ul>
            <a href="departments.php" class="btn-secondary" style="width:100%;margin-top:16px;justify-content:center;display:flex">Voir les départements</a>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- ═══════════════════════════════════════════════════════════════
       MODAL 1 — EMPLOYÉ  (Informations / Téléphones / Localisation)
  ═══════════════════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="employee-modal" onclick="if(event.target===this)closeEmployeeModal()">
    <div class="modal">
      <div class="modal-head">
        <div class="modal-head-icon red">
          <svg viewBox="0 0 24 24" id="modal-head-icon-svg">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/>
            <line x1="16" y1="11" x2="22" y2="11"/>
          </svg>
        </div>
        <div style="flex:1">
          <h3 id="modal-emp-title">Nouvel employé</h3>
          <p id="modal-emp-subtitle">Remplissez les informations de l'employé</p>
        </div>
        <span class="edit-badge amber" id="emp-edit-badge" style="display:none">Modification</span>
        <button class="modal-close" onclick="closeEmployeeModal()">✕</button>
      </div>

      <div class="modal-tabs" id="emp-tabs">
        <button class="modal-tab active-red" data-tab="infos" onclick="switchTab('emp','infos',this)">
          <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Informations
        </button>
        <button class="modal-tab" data-tab="contacts" onclick="switchTab('emp','contacts',this)">
          <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.21 15a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          Téléphones
        </button>
        <button class="modal-tab" data-tab="localisation" onclick="switchTab('emp','localisation',this)">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Localisation
        </button>
      </div>

      <div class="modal-body">
        <form id="employee-form" autocomplete="off" onsubmit="return false">
          <input type="hidden" id="f-id" name="id_employe" value="0"/>

          <!-- TAB : Informations -->
          <div class="tab-panel active" id="emp-tab-infos">
            <div class="form-section">
              <div class="form-section-title">Identité</div>
              <div class="form-grid">
                <div class="f-field">
                  <label>Prénom <span class="req">*</span></label>
                  <input id="f-prenom" name="prenom" required placeholder="Marie" autocomplete="off"/>
                </div>
                <div class="f-field">
                  <label>Nom <span class="req">*</span></label>
                  <input id="f-nom" name="nom" required placeholder="DUPONT" autocomplete="off"/>
                </div>
                <div class="f-field span2">
                  <label>Adresse email <span class="req">*</span></label>
                  <input id="f-email" name="email" type="email" required placeholder="m.dupont@camrail.cm" autocomplete="off"/>
                </div>
              </div>
            </div>
            <div class="form-section">
              <div class="form-section-title">Poste &amp; affectation</div>
              <div class="form-grid">
                <div class="f-field">
                  <label>Poste / Titre</label>
                  <input id="f-poste" name="poste" placeholder="Ingénieur Ferroviaire"/>
                </div>
                <div class="f-field">
                  <label>Département</label>
                  <select id="f-dept" name="id_service">
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
                  <input id="f-photo" name="photo" placeholder="https://… ou laisser vide"/>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB : Téléphones -->
          <div class="tab-panel" id="emp-tab-contacts">
            <div class="form-section">
              <div class="form-section-title">Numéros de téléphone</div>
              <div class="phones-list" id="phones-list"></div>
              <button type="button" class="btn-add-row blue" onclick="addPhoneRow()">
                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter un numéro
              </button>
            </div>
            <p style="font-size:12px;color:var(--text-faint);margin-top:8px">Cochez <strong>Principal</strong> sur le numéro qui apparaîtra en priorité.</p>
          </div>

          <!-- TAB : Localisation -->
          <div class="tab-panel" id="emp-tab-localisation">
            <div class="form-section">
              <div class="form-section-title">Localisation existante</div>
              <div class="form-grid">
                <div class="f-field span2">
                  <label>Associer à une localisation existante</label>
                  <select id="f-localisation" name="id_localisation">
                    <option value="">— Aucune / Choisir —</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="form-section-title">Ou créer une nouvelle localisation</div>
              <p style="font-size:12px;color:var(--text-faint);margin:-8px 0 14px">Si vous remplissez les champs ci-dessous, une nouvelle localisation sera créée et associée à cet employé.</p>
              <div class="form-grid">
                <div class="f-field">
                  <label>Nom de la localisation</label>
                  <input id="f-loc-nom" name="loc_nom" placeholder="Bureau principal DG"/>
                </div>
                <div class="f-field">
                  <label>Ville</label>
                  <input id="f-loc-ville" name="loc_ville" placeholder="Yaoundé"/>
                </div>
                <div class="f-field">
                  <label>Site / Campus</label>
                  <input id="f-loc-site" name="loc_site" placeholder="Siège social"/>
                </div>
                <div class="f-field">
                  <label>Bâtiment</label>
                  <input id="f-loc-batiment" name="loc_batiment" placeholder="Bât. A"/>
                </div>
                <div class="f-field">
                  <label>Étage</label>
                  <input id="f-loc-etage" name="loc_etage" placeholder="2ème étage"/>
                </div>
                <div class="f-field">
                  <label>Bureau / Salle</label>
                  <input id="f-loc-bureau" name="loc_bureau" placeholder="Bureau 214"/>
                </div>
                <div class="f-field span2">
                  <label>Description / Notes</label>
                  <textarea id="f-loc-desc" name="loc_description" placeholder="Informations complémentaires sur cet espace…"></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="form-alert" id="emp-form-error">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span id="emp-form-error-msg"></span>
          </div>
          <div class="form-success" id="emp-form-success">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <span id="emp-form-success-msg"></span>
          </div>
        </form>
      </div>

      <div class="modal-foot">
        <button type="button" class="btn-cancel" onclick="closeEmployeeModal()">Annuler</button>
        <button type="button" class="btn-submit red" id="emp-submit-btn" onclick="submitEmployeeForm()">
          <svg viewBox="0 0 24 24" id="emp-submit-icon"><polyline points="20 6 9 17 4 12"/></svg>
          <span id="emp-submit-label">Ajouter l'employé</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════════════
       MODAL 2 — UTILISATEUR SYSTÈME
       Un utilisateur EST un employé (relation obligatoire)
  ═══════════════════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="user-modal" onclick="if(event.target===this)closeUserModal()">
    <div class="modal">
      <div class="modal-head">
        <div class="modal-head-icon purple">
          <svg viewBox="0 0 24 24">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
            <line x1="8" y1="21" x2="16" y2="21"/>
            <line x1="12" y1="17" x2="12" y2="21"/>
          </svg>
        </div>
        <div style="flex:1">
          <h3 id="modal-user-title">Nouvel utilisateur système</h3>
          <p id="modal-user-subtitle">Créez un accès pour un employé existant</p>
        </div>
        <span class="edit-badge purple" id="user-edit-badge" style="display:none">Modification</span>
        <button class="modal-close" onclick="closeUserModal()">✕</button>
      </div>

      <div class="modal-tabs" id="user-tabs">
        <button class="modal-tab active-purple" data-tab="compte" onclick="switchTab('user','compte',this)">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          Compte
        </button>
        <button class="modal-tab" data-tab="acces" onclick="switchTab('user','acces',this)">
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Accès &amp; rôle
        </button>
      </div>

      <div class="modal-body">
        <form id="user-form" autocomplete="off" onsubmit="return false">
          <input type="hidden" id="uf-id" name="id_utilisateur" value="0"/>

          <!-- TAB : Compte -->
          <div class="tab-panel active" id="user-tab-compte">

            <!-- Lien obligatoire à un employé -->
            <div class="form-section">
              <div class="form-section-title">Employé associé <span style="color:var(--red);font-size:11px">obligatoire</span></div>

              <div class="role-info" style="margin-bottom:14px">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Un utilisateur système doit obligatoirement correspondre à un employé Camrail. Sélectionnez l'employé ci-dessous — ses coordonnées seront reprises automatiquement.</span>
              </div>

              <div class="form-grid">
                <div class="f-field span2">
                  <label>Rechercher &amp; sélectionner un employé <span class="req">*</span></label>
                  <select id="uf-employe" name="id_employe_lie" onchange="onEmployeeSelected(this)" style="font-size:14px">
                    <option value="">— Choisir un employé —</option>
                  </select>
                </div>
              </div>

              <!-- Fiche de l'employé sélectionné -->
              <div id="uf-emp-preview" style="display:none;margin-top:10px">
                <div class="emp-linked-box">
                  <div class="emp-avatar" id="uf-emp-initials">??</div>
                  <div class="emp-info">
                    <p id="uf-emp-name">—</p>
                    <span id="uf-emp-details">—</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Identifiants -->
            <div class="form-section">
              <div class="form-section-title">Identifiants de connexion</div>
              <div class="form-grid">
                <div class="f-field f-field-purple">
                  <label>Nom <span class="req">*</span></label>
                  <input id="uf-nom" name="nom" required placeholder="Dupont" autocomplete="off"/>
                </div>
                <div class="f-field f-field-purple">
                  <label>Prénom <span class="req">*</span></label>
                  <input id="uf-prenom" name="prenom" required placeholder="Marie" autocomplete="off"/>
                </div>
                <div class="f-field f-field-purple span2">
                  <label>Login (identifiant unique) <span class="req">*</span></label>
                  <input id="uf-login" name="login" required placeholder="m.dupont" autocomplete="off" oninput="sanitizeLogin(this)"/>
                </div>
                <div class="f-field f-field-purple">
                  <label>Mot de passe <span class="req" id="pwd-req-marker">*</span></label>
                  <input id="uf-pwd" name="mot_de_passe" type="password" placeholder="Minimum 8 caractères" autocomplete="new-password" oninput="checkPwdStrength(this)"/>
                  <div class="pwd-strength"><div class="pwd-strength-bar" id="pwd-bar"></div></div>
                </div>
                <div class="f-field f-field-purple">
                  <label>Confirmer le mot de passe <span class="req" id="pwd-conf-req-marker">*</span></label>
                  <input id="uf-pwd-conf" name="mot_de_passe_conf" type="password" placeholder="Répéter le mot de passe" autocomplete="new-password"/>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB : Accès & rôle -->
          <div class="tab-panel" id="user-tab-acces">
            <div class="form-section">
              <div class="form-section-title">Rôle système</div>
              <div class="form-grid">
                <div class="f-field f-field-purple span2">
                  <label>Rôle <span class="req">*</span></label>
                  <select id="uf-role" name="id_role" onchange="onRoleChanged(this)">
                    <option value="">— Choisir un rôle —</option>
                  </select>
                </div>
              </div>
              <!-- Description du rôle -->
              <div id="role-desc-box" style="display:none;margin-top:12px">
                <div class="role-info">
                  <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                  <span id="role-desc-text"></span>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="form-section-title">Récapitulatif</div>
              <div id="recap-box" style="background:#f8fafc;border:1px solid var(--border);border-radius:var(--r-sm);padding:14px;font-size:13px;color:#374151;line-height:1.8">
                <em style="color:var(--text-faint)">Sélectionnez un employé et un rôle pour voir le récapitulatif.</em>
              </div>
            </div>
          </div>

          <div class="form-alert" id="user-form-error">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span id="user-form-error-msg"></span>
          </div>
          <div class="form-success" id="user-form-success">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <span id="user-form-success-msg"></span>
          </div>
        </form>
      </div>

      <div class="modal-foot">
        <button type="button" class="btn-cancel" onclick="closeUserModal()">Annuler</button>
        <button type="button" class="btn-submit purple" id="user-submit-btn" onclick="submitUserForm()">
          <svg viewBox="0 0 24 24" id="user-submit-icon">
            <rect x="2" y="3" width="20" height="14" rx="2"/>
            <line x1="8" y1="21" x2="16" y2="21"/>
            <line x1="12" y1="17" x2="12" y2="21"/>
          </svg>
          <span id="user-submit-label">Créer l'utilisateur</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════════ SCRIPTS ════════════════════════════ -->
  <script>
  const COLORS = ['var(--red)','#3b82f6','#f59e0b','#22c55e','#8b5cf6'];

  // ── Helpers ─────────────────────────────────────────────────────────────────
  function api(url, options = {}) {
    return fetch(url, { credentials: 'include', ...options });
  }
  function escHtml(str) {
    return String(str||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  /**
   * switchTab(context, name, btn)
   * context = 'emp' | 'user'
   * Gère les tabs des deux modals indépendamment
   */
  function switchTab(ctx, name, btn) {
    const prefix = ctx === 'emp' ? 'emp-tab-' : 'user-tab-';
    // Cacher tous les panels du contexte
    document.querySelectorAll(`[id^="${prefix}"]`).forEach(p => p.classList.remove('active'));
    // Retirer active de tous les tabs du contexte
    const tabsContainer = document.getElementById(ctx === 'emp' ? 'emp-tabs' : 'user-tabs');
    tabsContainer.querySelectorAll('.modal-tab').forEach(b => {
      b.classList.remove('active-red','active-purple');
    });
    document.getElementById(prefix + name).classList.add('active');
    btn.classList.add(ctx === 'emp' ? 'active-red' : 'active-purple');
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // MODAL EMPLOYÉ
  // ═══════════════════════════════════════════════════════════════════════════
  let _empEditMode = false;
  let _phoneIndex  = 0;

  function addPhoneRow(type='mobile', numero='', principal=false) {
    const idx = _phoneIndex++;
    const row = document.createElement('div');
    row.className = 'phone-row';
    row.dataset.idx = idx;
    row.innerHTML = `
      <select name="numeros[${idx}][type]">
        <option value="mobile"   ${type==='mobile'   ?'selected':''}>Mobile</option>
        <option value="fixe"     ${type==='fixe'     ?'selected':''}>Fixe</option>
        <option value="whatsapp" ${type==='whatsapp' ?'selected':''}>WhatsApp</option>
        <option value="poste"    ${type==='poste'    ?'selected':''}>Poste</option>
      </select>
      <input type="tel" name="numeros[${idx}][numero]" value="${escHtml(numero)}" placeholder="+237 6xx xx xx xx"/>
      <button type="button" class="btn-principal ${principal?'is-principal':''}"
              name="numeros[${idx}][principal]" value="${principal?'1':'0'}"
              onclick="togglePrincipal(this)">
        ${principal ? '★ Principal' : '☆ Principal'}
      </button>
      <button type="button" class="btn-del-phone" onclick="removePhoneRow(this)" title="Supprimer">
        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>`;
    document.getElementById('phones-list').appendChild(row);
  }

  function togglePrincipal(btn) {
    document.querySelectorAll('.btn-principal').forEach(b => {
      b.classList.remove('is-principal'); b.value='0'; b.textContent='☆ Principal';
    });
    btn.classList.add('is-principal'); btn.value='1'; btn.textContent='★ Principal';
  }
  function removePhoneRow(btn) { btn.closest('.phone-row').remove(); }

  async function loadDepts(selectedId=0) {
    try {
      const r = await api('backend/api.php?action=departments');
      const d = await r.json();
      if (!d.success) return;
      const sel = document.getElementById('f-dept');
      sel.innerHTML = '<option value="">— Choisir —</option>' +
        d.departments.map(s =>
          `<option value="${s.id_service}" ${s.id_service==selectedId?'selected':''}>${escHtml(s.nom_service)}</option>`
        ).join('');
    } catch(e) {}
  }

  async function loadLocalisations(selectedId=0) {
    try {
      const r = await api('backend/api.php?action=localisations');
      const d = await r.json();
      if (!d.success) return;
      const sel = document.getElementById('f-localisation');
      sel.innerHTML = '<option value="">— Aucune / Choisir —</option>' +
        d.localisations.map(l =>
          `<option value="${l.id_localisation}" ${l.id_localisation==selectedId?'selected':''}>${escHtml(l.nom_localisation)}${l.ville?' — '+escHtml(l.ville):''}</option>`
        ).join('');
    } catch(e) {}
  }

  async function openEmployeeModal() {
    _empEditMode = false;
    resetEmpForm();
    document.getElementById('modal-emp-title').textContent    = 'Nouvel employé';
    document.getElementById('modal-emp-subtitle').textContent = "Remplissez les informations de l'employé";
    document.getElementById('emp-edit-badge').style.display   = 'none';
    document.getElementById('emp-submit-label').textContent   = "Ajouter l'employé";
    document.getElementById('emp-submit-icon').innerHTML      = '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>';
    await loadDepts();
    await loadLocalisations();
    addPhoneRow('mobile', '', true);
    document.getElementById('employee-modal').classList.add('open');
    document.getElementById('f-prenom').focus();
  }

  async function openEditModal(idEmploye) {
    _empEditMode = true;
    resetEmpForm();
    document.getElementById('modal-emp-title').textContent    = "Modifier l'employé";
    document.getElementById('modal-emp-subtitle').textContent = 'Modifiez les informations ci-dessous';
    document.getElementById('emp-edit-badge').style.display   = 'inline-flex';
    document.getElementById('emp-submit-label').textContent   = 'Enregistrer les modifications';
    document.getElementById('emp-submit-icon').innerHTML      = '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>';
    document.getElementById('employee-modal').classList.add('open');
    setEmpLoading(true);
    try {
      const r = await api(`backend/api.php?action=get_employee_full&id=${idEmploye}`);
      const d = await r.json();
      if (!d.success) { showEmpError(d.error || 'Employé introuvable'); setEmpLoading(false); return; }
      const emp = d.employee;
      document.getElementById('f-id').value     = emp.id_employe;
      document.getElementById('f-prenom').value = emp.prenom || '';
      document.getElementById('f-nom').value    = emp.nom    || '';
      document.getElementById('f-email').value  = emp.email  || '';
      document.getElementById('f-poste').value  = emp.poste  || '';
      document.getElementById('f-photo').value  = emp.photo  || '';
      await loadDepts(emp.id_service);
      await loadLocalisations(emp.id_localisation);
      // Pré-remplir localisation existante si champs connus
      if (emp.localisation) {
        document.getElementById('f-loc-nom').value      = emp.localisation.nom_localisation || '';
        document.getElementById('f-loc-ville').value    = emp.localisation.ville || '';
        document.getElementById('f-loc-site').value     = emp.localisation.site  || '';
        document.getElementById('f-loc-batiment').value = emp.localisation.batiment || '';
        document.getElementById('f-loc-etage').value    = emp.localisation.etage  || '';
        document.getElementById('f-loc-bureau').value   = emp.localisation.bureau || '';
        document.getElementById('f-loc-desc').value     = emp.localisation.description || '';
      }
      const numeros = emp.numeros || [];
      if (numeros.length === 0) addPhoneRow('mobile','',true);
      else numeros.forEach(n => addPhoneRow(n.type, n.numero, n.principal==1));
    } catch(e) { showEmpError('Erreur de chargement'); }
    finally { setEmpLoading(false); }
  }

  async function submitEmployeeForm() {
    hideEmpMessages();
    const prenom = document.getElementById('f-prenom').value.trim();
    const nom    = document.getElementById('f-nom').value.trim();
    const email  = document.getElementById('f-email').value.trim();
    if (!prenom || !nom || !email) {
      showEmpError('Les champs Prénom, Nom et Email sont obligatoires.');
      switchTab('emp','infos', document.querySelector('#emp-tabs [data-tab="infos"]')); return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showEmpError("L'adresse email n'est pas valide.");
      switchTab('emp','infos', document.querySelector('#emp-tabs [data-tab="infos"]')); return;
    }
    setEmpLoading(true);
    try {
      const action = _empEditMode ? 'edit_employee' : 'add_employee';
      const fd = new FormData(document.getElementById('employee-form'));
      fd.set('action', action);
      document.querySelectorAll('.btn-principal').forEach(btn => {
        const idx = btn.closest('.phone-row').dataset.idx;
        fd.set(`numeros[${idx}][principal]`, btn.value);
      });
      const r = await api('backend/api.php', { method: 'POST', body: fd });
      const d = await r.json();
      if (d.success) {
        const msg = _empEditMode
          ? '✓ Employé mis à jour avec succès.'
          : `✓ Employé ajouté avec succès (ID : ${d.id_employe}).`;
        showEmpSuccess(msg);
        refreshStats();
        setTimeout(() => { closeEmployeeModal(); location.reload(); }, 1400);
      } else {
        const ERRORS = {
          missing_fields:'Champs obligatoires manquants.',
          invalid_email:'Adresse email invalide.',
          email_already_used:'Cette adresse email est déjà utilisée.',
          db_error:'Erreur base de données. Réessayez.',
          forbidden:'Accès refusé.',
          invalid_id:'Identifiant employé invalide.',
        };
        showEmpError(ERRORS[d.error] || d.error || 'Une erreur est survenue.');
      }
    } catch(e) { showEmpError('Erreur réseau. Vérifiez votre connexion.'); }
    finally { setEmpLoading(false); }
  }

  function setEmpLoading(on) {
    const btn = document.getElementById('emp-submit-btn');
    btn.disabled = on;
    if (on) {
      document.getElementById('emp-submit-icon').outerHTML = '<span class="spinner" id="emp-submit-icon"></span>';
      document.getElementById('emp-submit-label').textContent = _empEditMode ? 'Enregistrement…' : 'Ajout en cours…';
    } else {
      const sp = document.getElementById('emp-submit-icon');
      if (sp && sp.classList.contains('spinner'))
        sp.outerHTML = '<svg viewBox="0 0 24 24" id="emp-submit-icon" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
      document.getElementById('emp-submit-label').textContent = _empEditMode ? 'Enregistrer les modifications' : "Ajouter l'employé";
    }
  }

  function showEmpError(msg)   { const el=document.getElementById('emp-form-error');   document.getElementById('emp-form-error-msg').textContent=msg;   el.classList.add('visible');    el.scrollIntoView({behavior:'smooth',block:'nearest'}); }
  function showEmpSuccess(msg) { const el=document.getElementById('emp-form-success'); document.getElementById('emp-form-success-msg').textContent=msg; el.classList.add('visible'); }
  function hideEmpMessages()   { document.getElementById('emp-form-error').classList.remove('visible'); document.getElementById('emp-form-success').classList.remove('visible'); }

  function resetEmpForm() {
    document.getElementById('employee-form').reset();
    document.getElementById('f-id').value = '0';
    document.getElementById('phones-list').innerHTML = '';
    _phoneIndex = 0;
    hideEmpMessages();
    switchTab('emp','infos', document.querySelector('#emp-tabs [data-tab="infos"]'));
  }
  function closeEmployeeModal() {
    document.getElementById('employee-modal').classList.remove('open');
    resetEmpForm();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // MODAL UTILISATEUR
  // ═══════════════════════════════════════════════════════════════════════════
  let _userEditMode = false;
  let _rolesCache   = [];
  let _employeesCache = [];

  async function openUserModal() {
    _userEditMode = false;
    resetUserForm();
    document.getElementById('modal-user-title').textContent    = 'Nouvel utilisateur système';
    document.getElementById('modal-user-subtitle').textContent = "Créez un accès pour un employé existant";
    document.getElementById('user-edit-badge').style.display   = 'none';
    document.getElementById('user-submit-label').textContent   = "Créer l'utilisateur";
    // Marquer mot de passe obligatoire
    document.getElementById('pwd-req-marker').style.display     = 'inline';
    document.getElementById('pwd-conf-req-marker').style.display = 'inline';
    document.getElementById('uf-pwd').required = true;
    document.getElementById('uf-pwd-conf').required = true;

    await Promise.all([loadEmployeesForSelect(), loadRoles()]);
    document.getElementById('user-modal').classList.add('open');
    document.getElementById('uf-employe').focus();
  }

  async function loadEmployeesForSelect() {
    try {
      const r = await api('backend/api.php?action=employees_without_account');
      const d = await r.json();
      if (!d.success) return;
      _employeesCache = d.employees || [];
      const sel = document.getElementById('uf-employe');
      sel.innerHTML = '<option value="">— Choisir un employé —</option>' +
        _employeesCache.map(e =>
          `<option value="${e.id_employe}" data-prenom="${escHtml(e.prenom||'')}" data-nom="${escHtml(e.nom||'')}" data-poste="${escHtml(e.poste||'')}" data-dept="${escHtml(e.nom_service||'')}">
            ${escHtml(e.prenom||'')} ${escHtml(e.nom||'')}${e.poste?' — '+escHtml(e.poste):''}
          </option>`
        ).join('');
    } catch(e) {}
  }

  async function loadRoles() {
    try {
      const r = await api('backend/api.php?action=roles');
      const d = await r.json();
      if (!d.success) return;
      _rolesCache = d.roles || [];
      const sel = document.getElementById('uf-role');
      sel.innerHTML = '<option value="">— Choisir un rôle —</option>' +
        _rolesCache.map(role =>
          `<option value="${role.id_role}">${escHtml(role.nom_role)}</option>`
        ).join('');
    } catch(e) {}
  }

  function onEmployeeSelected(sel) {
    const opt = sel.options[sel.selectedIndex];
    const preview = document.getElementById('uf-emp-preview');
    if (!sel.value) { preview.style.display='none'; updateRecap(); return; }
    const prenom = opt.dataset.prenom || '';
    const nom    = opt.dataset.nom    || '';
    const poste  = opt.dataset.poste  || '';
    const dept   = opt.dataset.dept   || '';
    document.getElementById('uf-emp-initials').textContent = ((prenom[0]||'?') + (nom[0]||'?')).toUpperCase();
    document.getElementById('uf-emp-name').textContent     = `${prenom} ${nom}`;
    document.getElementById('uf-emp-details').textContent  = [poste, dept].filter(Boolean).join(' · ');
    preview.style.display = 'block';
    // Auto-remplir nom/prénom et suggérer login
    document.getElementById('uf-prenom').value = prenom;
    document.getElementById('uf-nom').value    = nom;
    if (!document.getElementById('uf-login').value) {
      const suggested = (prenom[0]||'').toLowerCase() + '.' + (nom||'').toLowerCase().replace(/\s+/g,'_');
      document.getElementById('uf-login').value = suggested;
    }
    updateRecap();
  }

  function onRoleChanged(sel) {
    const box  = document.getElementById('role-desc-box');
    const text = document.getElementById('role-desc-text');
    const ROLE_DESCS = {
      'Administrateur' : 'Accès complet : gestion des employés, utilisateurs, validation des demandes et journaux d\'audit.',
      'Éditeur'        : 'Peut modifier les fiches employés et valider les demandes. Ne peut pas gérer les utilisateurs.',
      'Lecteur'        : 'Accès en lecture seule à l\'annuaire. Peut soumettre des demandes de modification.',
    };
    const nom = sel.options[sel.selectedIndex]?.text || '';
    if (sel.value && ROLE_DESCS[nom]) {
      text.textContent = ROLE_DESCS[nom];
      box.style.display = 'block';
    } else if (sel.value) {
      text.textContent = `Rôle sélectionné : ${nom}`;
      box.style.display = 'block';
    } else {
      box.style.display = 'none';
    }
    updateRecap();
  }

  function updateRecap() {
    const empSel  = document.getElementById('uf-employe');
    const roleSel = document.getElementById('uf-role');
    const login   = document.getElementById('uf-login').value.trim();
    const box     = document.getElementById('recap-box');
    const empOpt  = empSel.options[empSel.selectedIndex];
    const roleOpt = roleSel.options[roleSel.selectedIndex];
    if (!empSel.value || !roleSel.value) {
      box.innerHTML = '<em style="color:var(--text-faint)">Sélectionnez un employé et un rôle pour voir le récapitulatif.</em>';
      return;
    }
    const nom   = empOpt.dataset.nom   || '';
    const prenom= empOpt.dataset.prenom|| '';
    box.innerHTML = `
      <table style="font-size:13px;width:100%;border-collapse:collapse">
        <tr><td style="color:#6b7280;padding:3px 0;width:40%">Employé lié</td><td style="font-weight:600">${escHtml(prenom+' '+nom)}</td></tr>
        <tr><td style="color:#6b7280;padding:3px 0">Login</td><td style="font-family:monospace;background:#f1f5f9;padding:2px 6px;border-radius:4px">${escHtml(login||'—')}</td></tr>
        <tr><td style="color:#6b7280;padding:3px 0">Rôle attribué</td><td><span style="background:var(--purple-bg);color:var(--purple);padding:2px 8px;border-radius:var(--r-full);font-size:11px;font-weight:700">${escHtml(roleOpt.text)}</span></td></tr>
      </table>`;
  }

  function sanitizeLogin(input) {
    input.value = input.value.toLowerCase().replace(/[^a-z0-9._-]/g,'');
    updateRecap();
  }

  function checkPwdStrength(input) {
    const v = input.value;
    let score = 0;
    if (v.length >= 8)  score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const bar = document.getElementById('pwd-bar');
    const colors = ['#ef4444','#f97316','#eab308','#22c55e'];
    bar.style.width   = (score * 25) + '%';
    bar.style.background = colors[score-1] || '#e5e7eb';
  }

  async function submitUserForm() {
    hideUserMessages();
    const empId  = document.getElementById('uf-employe').value;
    const nom    = document.getElementById('uf-nom').value.trim();
    const prenom = document.getElementById('uf-prenom').value.trim();
    const login  = document.getElementById('uf-login').value.trim();
    const roleId = document.getElementById('uf-role').value;
    const pwd    = document.getElementById('uf-pwd').value;
    const pwdC   = document.getElementById('uf-pwd-conf').value;

    if (!empId)  { showUserError("Veuillez sélectionner un employé associé."); switchTab('user','compte',document.querySelector('#user-tabs [data-tab="compte"]')); return; }
    if (!nom || !prenom || !login) { showUserError("Nom, prénom et login sont obligatoires."); switchTab('user','compte',document.querySelector('#user-tabs [data-tab="compte"]')); return; }
    if (!roleId) { showUserError("Veuillez sélectionner un rôle."); switchTab('user','acces',document.querySelector('#user-tabs [data-tab="acces"]')); return; }
    if (!_userEditMode) {
      if (!pwd) { showUserError("Mot de passe obligatoire."); return; }
      if (pwd.length < 8) { showUserError("Le mot de passe doit contenir au moins 8 caractères."); return; }
      if (pwd !== pwdC)   { showUserError("Les deux mots de passe ne correspondent pas."); return; }
    } else if (pwd && pwd !== pwdC) {
      showUserError("Les deux mots de passe ne correspondent pas."); return;
    }

    setUserLoading(true);
    try {
      const fd = new FormData(document.getElementById('user-form'));
      fd.set('action', _userEditMode ? 'edit_user' : 'add_user');
      const r = await api('backend/api.php', { method: 'POST', body: fd });
      const d = await r.json();
      if (d.success) {
        showUserSuccess(_userEditMode
          ? '✓ Utilisateur mis à jour avec succès.'
          : `✓ Utilisateur créé avec succès (login : ${escHtml(login)}).`);
        refreshStats();
        setTimeout(() => { closeUserModal(); location.reload(); }, 1500);
      } else {
        const ERRORS = {
          missing_fields      : 'Champs obligatoires manquants.',
          login_already_used  : 'Ce login est déjà utilisé par un autre compte.',
          employee_has_account: 'Cet employé possède déjà un compte utilisateur.',
          invalid_employee    : 'Employé introuvable.',
          invalid_role        : 'Rôle invalide.',
          db_error            : 'Erreur base de données. Réessayez.',
          forbidden           : 'Accès refusé.',
        };
        showUserError(ERRORS[d.error] || d.error || 'Une erreur est survenue.');
      }
    } catch(e) { showUserError('Erreur réseau. Vérifiez votre connexion.'); }
    finally { setUserLoading(false); }
  }

  function setUserLoading(on) {
    const btn = document.getElementById('user-submit-btn');
    btn.disabled = on;
    if (on) {
      document.getElementById('user-submit-icon').outerHTML = '<span class="spinner" id="user-submit-icon"></span>';
      document.getElementById('user-submit-label').textContent = _userEditMode ? 'Enregistrement…' : 'Création en cours…';
    } else {
      const sp = document.getElementById('user-submit-icon');
      if (sp && sp.classList.contains('spinner'))
        sp.outerHTML = '<svg viewBox="0 0 24 24" id="user-submit-icon" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>';
      document.getElementById('user-submit-label').textContent = _userEditMode ? 'Enregistrer les modifications' : "Créer l'utilisateur";
    }
  }

  function showUserError(msg)   { const el=document.getElementById('user-form-error');   document.getElementById('user-form-error-msg').textContent=msg;   el.classList.add('visible');    el.scrollIntoView({behavior:'smooth',block:'nearest'}); }
  function showUserSuccess(msg) { const el=document.getElementById('user-form-success'); document.getElementById('user-form-success-msg').textContent=msg; el.classList.add('visible'); }
  function hideUserMessages()   { document.getElementById('user-form-error').classList.remove('visible'); document.getElementById('user-form-success').classList.remove('visible'); }

  function resetUserForm() {
    document.getElementById('user-form').reset();
    document.getElementById('uf-id').value = '0';
    document.getElementById('uf-emp-preview').style.display = 'none';
    document.getElementById('role-desc-box').style.display  = 'none';
    document.getElementById('pwd-bar').style.width = '0';
    updateRecap();
    hideUserMessages();
    switchTab('user','compte', document.querySelector('#user-tabs [data-tab="compte"]'));
  }
  function closeUserModal() {
    document.getElementById('user-modal').classList.remove('open');
    resetUserForm();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // DASHBOARD
  // ═══════════════════════════════════════════════════════════════════════════
  function refreshStats() {
    api('backend/api.php?action=stats').then(r=>r.json()).then(d=>{
      if (!d.success) return;
      document.getElementById('stat-employes').textContent = Number(d.total_employes).toLocaleString('fr-FR');
      document.getElementById('stat-attente').textContent  = d.en_attente;
      document.getElementById('stat-services').textContent = d.total_services;
      document.getElementById('stat-users').textContent    = d.total_users;
      document.getElementById('urgency-label').textContent = d.en_attente>0 ? d.en_attente+' urgent(s)' : 'À jour';
      document.getElementById('repartition-list').innerHTML = d.repartition.map((r,i)=>`
        <li class="dist-item">
          <div class="dist-dot" style="background:${COLORS[i%COLORS.length]}"></div>
          <span class="dist-name">${escHtml(r.nom_service)}</span>
          <span class="dist-val">${Number(r.total).toLocaleString('fr-FR')}</span>
        </li>`).join('');
    }).catch(()=>{});
  }

  function loadQueue() {
    api('backend/api.php?action=validation_queue').then(r=>r.json()).then(d=>{
      if (!d.success) return;
      document.getElementById('queue-count').textContent = d.queue.length+' en attente';
      document.getElementById('validation-queue').innerHTML = d.queue.length
        ? d.queue.map(item=>`
          <div class="validation-item">
            <div class="validation-person">
              ${item.emp_photo
                ?`<img class="validation-avatar" src="${item.emp_photo}" alt=""/>`
                :`<div class="validation-initials">${(item.emp_prenom||'?')[0]}${(item.emp_nom||'?')[0]}</div>`}
              <div>
                <p class="validation-name">${escHtml(item.demandeur)}</p>
                <p class="validation-sub">${escHtml(item.champ_modifie)} → <em>${escHtml(item.nouvelle_valeur)}</em></p>
              </div>
            </div>
            <div class="validation-actions">
              <button class="btn-decline" onclick="handleRequest(${item.id_demande},'reject_request')">Refuser</button>
              <button class="btn-approve" onclick="handleRequest(${item.id_demande},'approve_request')">Approuver</button>
            </div>
          </div>`).join('')
        : '<p style="color:var(--text-faint);padding:12px 0">Aucune demande en attente ✓</p>';
    }).catch(()=>{});
  }

  function handleRequest(id, action) {
    api('backend/api.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`action=${action}&id_demande=${id}`
    }).then(r=>r.json()).then(d=>{
      if (d.success) { loadQueue(); refreshStats(); }
      else alert(d.error||'Erreur');
    });
  }

  function loadActivityLog(limit=20) {
    api(`backend/api.php?action=activity_log&limit=${limit}`).then(r=>r.json()).then(d=>{
      if (!d.success) return;
      const STATUS = {
        LOGIN_SUCCESS:['ok','Connexion'],LOGIN_FAIL_USER:['err','Échec user'],
        LOGIN_FAIL_PWD:['err','Échec mdp'],LOGOUT:['ok','Déconnexion'],
        ADD_EMPLOYE:['ok','Ajout'],EDIT_EMPLOYE:['warn','Modification'],
        DELETE_EMPLOYE:['err','Suppression'],DEMANDE_VALIDE:['ok','Approuvé'],
        DEMANDE_REFUSE:['warn','Refusé'],ADD_USER:['ok','Nouv. user'],
      };
      document.getElementById('activity-log-body').innerHTML = d.logs.map(log=>{
        const[cls,label]=STATUS[log.action]||['',log.action];
        return`<tr>
          <td style="font-weight:600">${escHtml(log.action)}</td>
          <td>${escHtml(log.admin_name||'—')}</td>
          <td>${escHtml(log.table_cible)} #${log.id_cible}</td>
          <td style="color:var(--text-faint)">${new Date(log.date_heure).toLocaleString('fr-FR')}</td>
          <td><span class="log-badge ${cls}">${label}</span></td>
        </tr>`;
      }).join('')||'<tr><td colspan="5" style="color:var(--text-faint);text-align:center">Aucune entrée</td></tr>';
    }).catch(()=>{});
  }

  // Init
  refreshStats();
  loadQueue();
  loadActivityLog();
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeEmployeeModal(); closeUserModal(); }
  });
  </script>
  <script src="js/main.js"></script>
</body>
</html>