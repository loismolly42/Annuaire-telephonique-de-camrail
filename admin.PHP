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
</head>
<body data-page="admin">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="content">
      <div class="dir-header">
        <div><h2>Administration</h2><p>Tableau de bord — tâches en attente et journal d'activité.</p></div>
        <button class="btn-primary" onclick="openAddEmployeeModal()">
          <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nouvel employé
        </button>
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
              <button class="tool-btn" onclick="openAddEmployeeModal()"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/></svg>Ajouter</button>
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

  <!-- Modal ajouter employé -->
  <div class="modal-overlay" id="add-modal" style="display:none" onclick="if(event.target===this)closeModal()">
    <div class="modal">
      <div class="modal-header"><h3>Nouvel employé</h3><button class="modal-close" onclick="closeModal()">✕</button></div>
      <form id="add-employee-form">
        <div class="s-2col">
          <div class="s-field"><label>Prénom *</label><input name="prenom" required placeholder="Marie"/></div>
          <div class="s-field"><label>Nom *</label><input name="nom" required placeholder="DUPONT"/></div>
          <div class="s-field"><label>Email *</label><input name="email" type="email" required placeholder="m.dupont@camrail.cm"/></div>
          <div class="s-field"><label>Poste</label><input name="poste" placeholder="Ingénieur Ferroviaire"/></div>
          <div class="s-field"><label>Département</label><select name="id_service" id="dept-select"><option value="">— Choisir —</option></select></div>
          <div class="s-field"><label>Téléphone</label><input name="phone" type="tel" placeholder="+237 6xx xx xx xx"/></div>
        </div>
        <div id="add-error" class="form-alert" style="display:none;margin-top:12px"></div>
        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeModal()">Annuler</button>
          <button type="submit" class="btn-primary" id="add-submit">Ajouter l'employé</button>
        </div>
      </form>
    </div>
  </div>

  <script>
  const COLORS=['var(--red)','#3b82f6','#f59e0b','#22c55e','#8b5cf6'];

  fetch('backend/api.php?action=stats').then(r=>r.json()).then(d=>{
    if(!d.success)return;
    document.getElementById('stat-employes').textContent=Number(d.total_employes).toLocaleString('fr-FR');
    document.getElementById('stat-attente').textContent=d.en_attente;
    document.getElementById('stat-services').textContent=d.total_services;
    document.getElementById('stat-users').textContent=d.total_users;
    document.getElementById('urgency-label').textContent=d.en_attente>0?d.en_attente+' urgent(s)':'À jour';
    document.getElementById('repartition-list').innerHTML=d.repartition.map((r,i)=>`
      <li class="dist-item"><div class="dist-dot" style="background:${COLORS[i%COLORS.length]}"></div>
      <span class="dist-name">${r.nom_service}</span><span class="dist-val">${Number(r.total).toLocaleString('fr-FR')}</span></li>`).join('');
  });

  function loadQueue(){
    fetch('backend/api.php?action=validation_queue').then(r=>r.json()).then(d=>{
      if(!d.success)return;
      document.getElementById('queue-count').textContent=d.queue.length+' en attente';
      document.getElementById('validation-queue').innerHTML=d.queue.length
        ?d.queue.map(item=>`<div class="validation-item">
            <div class="validation-person">
              ${item.emp_photo?`<img class="validation-avatar" src="${item.emp_photo}" alt=""/>`:`<div class="validation-initials">${(item.emp_prenom||'?')[0]}${(item.emp_nom||'?')[0]}</div>`}
              <div><p class="validation-name">${item.demandeur}</p>
              <p class="validation-sub">${item.champ_modifie} → <em>${item.nouvelle_valeur}</em></p></div>
            </div>
            <div class="validation-actions">
              <button class="btn-decline" onclick="handleRequest(${item.id_demande},'reject_request')">Refuser</button>
              <button class="btn-approve" onclick="handleRequest(${item.id_demande},'approve_request')">Approuver</button>
            </div></div>`).join('')
        :'<p style="color:var(--text-faint);padding:12px 0">Aucune demande en attente ✓</p>';
    });
  }

  function handleRequest(id,action){
    fetch('backend/api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`action=${action}&id_demande=${id}`})
      .then(r=>r.json()).then(d=>{ if(d.success)loadQueue(); else alert(d.error||'Erreur'); });
  }

  function loadActivityLog(limit=20){
    fetch(`backend/api.php?action=activity_log&limit=${limit}`).then(r=>r.json()).then(d=>{
      if(!d.success)return;
      const STATUS={LOGIN_SUCCESS:['ok','Connexion'],LOGIN_FAIL_USER:['err','Échec user'],
        LOGIN_FAIL_PWD:['err','Échec mdp'],LOGOUT:['ok','Déconnexion'],
        ADD_EMPLOYE:['ok','Ajout'],DELETE_EMPLOYE:['err','Suppression'],
        DEMANDE_VALIDE:['ok','Approuvé'],DEMANDE_REFUSE:['warn','Refusé']};
      document.getElementById('activity-log-body').innerHTML=d.logs.map(log=>{
        const[cls,label]=STATUS[log.action]||['',''];
        return`<tr><td style="font-weight:600">${log.action}</td><td>${log.admin_name||'—'}</td>
          <td>${log.table_cible} #${log.id_cible}</td>
          <td style="color:var(--text-faint)">${new Date(log.date_heure).toLocaleString('fr-FR')}</td>
          <td><span class="log-badge ${cls}">${label||log.action}</span></td></tr>`;
      }).join('')||'<tr><td colspan="5" style="color:var(--text-faint);text-align:center">Aucune entrée</td></tr>';
    });
  }

  function openAddEmployeeModal(){
    fetch('backend/api.php?action=departments').then(r=>r.json()).then(d=>{
      if(!d.success)return;
      document.getElementById('dept-select').innerHTML='<option value="">— Choisir —</option>'+
        d.departments.map(s=>`<option value="${s.id_service}">${s.nom_service}</option>`).join('');
    });
    document.getElementById('add-modal').style.display='flex';
  }
  function closeModal(){
    document.getElementById('add-modal').style.display='none';
    document.getElementById('add-employee-form').reset();
    document.getElementById('add-error').style.display='none';
  }

  document.getElementById('add-employee-form').addEventListener('submit',function(e){
    e.preventDefault();
    const btn=document.getElementById('add-submit');
    btn.disabled=true;btn.textContent='Ajout…';
    const fd=new FormData(this);
    fd.append('action','add_employee');
    const phone=fd.get('phone'); fd.delete('phone');
    if(phone){fd.append('numeros[0][numero]',phone);fd.append('numeros[0][type]','mobile');fd.append('numeros[0][principal]','1');}
    fetch('backend/api.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
      if(d.success){closeModal();location.reload();}
      else{const err=document.getElementById('add-error');err.textContent=d.error||'Erreur';err.style.display='block';}
    }).finally(()=>{btn.disabled=false;btn.textContent="Ajouter l'employé";});
  });

  loadQueue();loadActivityLog();
  </script>
  <script src="js/main.js"></script>
</body>
</html>