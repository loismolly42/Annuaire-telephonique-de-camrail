<?php
$active_page = 'profile';
require_once __DIR__ . '/backend/session_guard.php';
$view_id = isset($_GET['id']) && $current_user['is_admin'] ? (int)$_GET['id'] : ($current_user['id_employe'] ?? 0);
$is_own  = ($view_id === 0 || $view_id === ($current_user['id_employe'] ?? 0));
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profil — Camrail Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/main.css"/>
</head>
<body data-page="profile">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <div class="main-wrapper">
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <main class="content">
      <div class="dir-header">
        <div><h2><?= $is_own ? 'Mon Profil' : 'Fiche Employé' ?></h2><p id="profile-subtitle">Chargement…</p></div>
        <?php if ($is_own): ?>
        <a href="settings.php" class="btn-primary">
          <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Modifier le profil
        </a>
        <?php endif; ?>
      </div>
      <div class="profil-wrap" id="profile-content">
        <div class="profil-left">
          <div class="profil-card skeleton" style="height:260px"></div>
          <div class="profil-card skeleton" style="height:120px;margin-top:16px"></div>
        </div>
        <div class="profil-right">
          <div class="profil-info-card skeleton" style="height:180px"></div>
          <div class="profil-info-card skeleton" style="height:160px;margin-top:16px"></div>
        </div>
      </div>
    </main>
  </div>

  <script>
  const IS_ADMIN=<?= $current_user['is_admin']?'true':'false' ?>;
  const IS_OWN=<?= $is_own?'true':'false' ?>;
  const EMP_ID=<?= (int)$view_id ?>;
  const S={name:<?= json_encode($current_user['name']) ?>,dept:<?= json_encode($current_user['department']) ?>,
    title:<?= json_encode($current_user['job_title']) ?>,email:<?= json_encode($current_user['email']) ?>,
    avatar:<?= json_encode($current_user['avatar']) ?>};

  function renderProfile(e,nums){
    document.getElementById('profile-subtitle').textContent=(e.poste||'—')+' — '+(e.nom_service||S.dept||'—');
    document.getElementById('profile-content').innerHTML=`
      <div class="profil-left">
        <div class="profil-card">
          <div class="profil-photo">
            ${e.photo?`<img src="${e.photo}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%"/>`
              :`<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:48px;font-weight:700;color:var(--red);background:var(--red-light);border-radius:50%">${(e.prenom||'?')[0]}${(e.nom||'?')[0]}</div>`}
            <div class="profil-status"><div class="profil-status-dot"></div>En Service</div>
          </div>
          <div class="profil-info">
            <h2 class="profil-name">${e.prenom} ${e.nom}</h2>
            <p class="profil-role">${e.poste||'—'}</p>
            <div class="profil-cta">
              ${e.email?`<a href="mailto:${e.email}" class="btn-primary" style="flex:1;justify-content:center"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>Email</a>`:''}
              ${nums.length?`<a href="tel:${nums[0].numero}" class="btn-secondary" style="flex:1;justify-content:center"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.42 2 2 0 0 1 3.6 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>Appeler</a>`:''}
            </div>
          </div>
        </div>
        <div class="profil-card" style="padding:18px">
          <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-faint);margin-bottom:12px">Identifiant</p>
          <div class="profil-fields">
            <div class="profil-field"><label>Matricule</label><p style="font-family:var(--font-mono)">EMP-${String(e.id_employe||0).padStart(5,'0')}</p></div>
            <div class="profil-field"><label>Département</label><p>${e.nom_service||'—'}</p></div>
            <div class="profil-field"><label>Depuis</label><p>${e.date_creation?new Date(e.date_creation).toLocaleDateString('fr-FR',{year:'numeric',month:'long'}):'—'}</p></div>
          </div>
        </div>
      </div>
      <div class="profil-right">
        <div class="profil-info-card">
          <div class="profil-info-header">
            <h3><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Coordonnées</h3>
            ${IS_OWN?'<a href="settings.php" class="profil-edit-link">Modifier</a>':''}
          </div>
          <div class="profil-fields">
            <div class="profil-field"><label>Email</label><p>${e.email?`<a href="mailto:${e.email}" style="color:var(--red)">${e.email}</a>`:'—'}</p></div>
            ${nums.map(n=>`<div class="profil-field"><label>${n.type_numero}${n.principal?' (principal)':''}</label><p><a href="tel:${n.numero}">${n.numero}</a></p></div>`).join('')}
            ${!nums.length?'<div class="profil-field"><label>Téléphone</label><p style="color:var(--text-faint)">Non renseigné</p></div>':''}
          </div>
        </div>
        <div class="profil-info-card">
          <div class="profil-info-header"><h3><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Organisation</h3></div>
          <div class="profil-fields">
            <div class="profil-field"><label>Département</label><p>${e.nom_service||'—'}</p></div>
            <div class="profil-field"><label>Poste</label><p>${e.poste||'—'}</p></div>
          </div>
        </div>
        ${IS_OWN&&EMP_ID>0?`
        <div class="profil-info-card">
          <div class="profil-info-header"><h3>Demande de modification</h3></div>
          <form id="mod-form" style="padding-top:8px">
            <div class="s-2col">
              <div class="s-field"><label>Champ</label><select id="mod-champ"><option value="poste">Poste</option><option value="email">Email</option><option value="photo">Photo (URL)</option></select></div>
              <div class="s-field"><label>Nouvelle valeur</label><input id="mod-valeur" placeholder="Nouvelle valeur…"/></div>
            </div>
            <div id="mod-msg" class="form-alert" style="display:none;margin-top:8px"></div>
            <button type="submit" class="btn-secondary" style="margin-top:12px">Soumettre</button>
          </form>
        </div>`:''}
      </div>`;

    const mf=document.getElementById('mod-form');
    if(mf) mf.addEventListener('submit',function(ev){
      ev.preventDefault();
      const champ=document.getElementById('mod-champ').value;
      const valeur=document.getElementById('mod-valeur').value.trim();
      const msg=document.getElementById('mod-msg');
      if(!valeur){msg.textContent='Entrez une valeur.';msg.style.display='block';return;}
      fetch('backend/api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=request_modification&table_cible=Employes&id_cible=${EMP_ID}&champ=${champ}&valeur=${encodeURIComponent(valeur)}&type_validation=ADMIN`})
        .then(r=>r.json()).then(d=>{
          msg.textContent=d.success?'✓ Demande envoyée, en attente de validation.':(d.error||'Erreur');
          msg.style.display='block';
          msg.style.background=d.success?'#dcfce7':'';
          msg.style.color=d.success?'#15803d':'';
          if(d.success)document.getElementById('mod-valeur').value='';
        });
    });
  }

  if(EMP_ID>0){
    fetch(`backend/api.php?action=employee&id=${EMP_ID}`)
      .then(r=>r.json()).then(d=>{
        if(!d.success){document.getElementById('profile-content').innerHTML='<p style="color:var(--text-faint)">Profil introuvable.</p>';return;}
        renderProfile(d.employee,d.employee.numeros||[]);
      });
  } else {
    const parts=S.name.split(' ');
    renderProfile({id_employe:0,nom:parts.slice(1).join(' ')||'—',prenom:parts[0]||'—',
      poste:S.title,email:S.email,photo:S.avatar,nom_service:S.dept,date_creation:new Date().toISOString()},[]);
  }
  </script>
  <script src="js/main.js"></script>
</body>
</html>