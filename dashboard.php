<?php
$active_page = 'dashboard';
require_once __DIR__ . '/backend/session_guard.php';
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — Camrail Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/main.css"/>
</head>
<body data-page="dashboard">

  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-wrapper">

    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="content">

      <!-- Hero -->
      <div class="page-hero">
        <div>
          <h1 class="hero-title">Bonjour, <?= htmlspecialchars(explode(' ', $current_user['name'])[0]) ?> 👋</h1>
          <p class="hero-sub">Restez à jour avec les dernières opérations ferroviaires et la disponibilité de votre équipe.</p>
        </div>
        <div class="hero-actions">
          <button class="btn-secondary">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Planning des quarts
          </button>
          <?php if ($current_user['is_admin']): ?>
          <a href="admin.php" class="btn-primary">
            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Administration
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Cartes rapides -->
      <div class="quick-grid">
        <a href="directory.php" class="quick-card">
          <div class="quick-card-top">
            <div class="quick-icon red">
              <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <svg class="quick-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <h3 class="quick-title">Tous les contacts</h3>
          <p class="quick-desc">Accès au répertoire complet des employés Camrail à travers le Cameroun.</p>
          <div class="quick-footer">
            <span class="quick-stat red" id="stat-employes">Chargement…</span>
          </div>
        </a>

        <a href="directory.php" class="quick-card">
          <div class="quick-card-top">
            <div class="quick-icon amber">
              <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <svg class="quick-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <h3 class="quick-title">Mes favoris</h3>
          <p class="quick-desc">Accès rapide aux collègues et départements avec lesquels vous interagissez le plus.</p>
          <div class="quick-footer"><span class="quick-stat amber">Favoris</span></div>
        </a>

        <a href="departments.php" class="quick-card">
          <div class="quick-card-top">
            <div class="quick-icon blue">
              <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <svg class="quick-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <h3 class="quick-title">Départements</h3>
          <p class="quick-desc">Parcourez la structure d'entreprise des Opérations à l'Administration.</p>
          <div class="quick-footer"><span class="quick-stat blue" id="stat-depts">Chargement…</span></div>
        </a>
      </div>

      <!-- Grille principale -->
      <div class="main-grid">
        <div class="col-news">
          <div class="section-header">
            <h2 class="section-title">Mises à jour du Réseau</h2>
            <a href="#" class="section-link">Voir tout</a>
          </div>
          <div class="news-cards-grid">
            <article class="news-card">
              <div class="news-img-wrap"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCKQIXEQNEYOduP0RWMDDPif2PqrOkk05duMc78lQf-gHnBcG5yU_5FDniypKnsKg8XJJI9X7xpdbA7nwEoyNjctbtXCQNiUGl7L1vqGHj0A16v9XxRrHsVZccTkjey7Ig9uvJF4RUQj9KTf9yFz4yB3RBAC1JUqTowJC9w6WxA_50BMINImXCQd3s5xxTxIGppCizX4KVaZrX3nlil4L6tOhUQRaST0vpTzVwcoY4s7skIe-QPZc2l4MqrdE3RBZ3gAhQlQ3OM54w" alt="Train"/></div>
              <div class="news-body">
                <span class="news-tag red">Infrastructure</span>
                <h3 class="news-title">Calendrier de maintenance Douala-Yaoundé</h3>
                <p class="news-excerpt">Le département ingénierie annonce des travaux de maintenance préventive programmés sur le corridor central à partir du mois prochain…</p>
                <p class="news-time"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Il y a 2 heures</p>
              </div>
            </article>
            <article class="news-card">
              <div class="news-img-wrap"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDhmAkS9KJlEewCHX8CaGK-9l5bPtT50pBF2IlH6T81wsxKQg2rjyQMPeljJw4oiMR4iBPfo0vsgLl3f28OjR8lrOktmKOW7irHsIE1SJnvF-BjU1V8O7ou4tkKeCkPRu83tUv6i-jguiucyhVF7MPjvEwjxPO-4JXBdALCmjeX-91Z4zkXfAjLPVzmhydXVDGAOEJIuPbhhtzle19d9TMa_t_1WYcfHDXF0ly-FiscuQjnQb29009YaseFF04_X4FdPoGdDnF4Ups" alt="Logistique"/></div>
              <div class="news-body">
                <span class="news-tag green">Innovation</span>
                <h3 class="news-title">Déploiement de la plateforme logistique numérique</h3>
                <p class="news-excerpt">Les phases de test final du système de suivi du fret en temps réel sont terminées. Les sessions de formation débuteront pour les responsables de dépôt…</p>
                <p class="news-time"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Il y a 5 heures</p>
              </div>
            </article>
          </div>
          <div class="news-list">
            <div class="news-list-item">
              <div class="news-list-thumb"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuB3sbE-iXLaZFQtipW-0wfmUYGPFzKvQrU2bJ16pPVfzL2t5TbE9hPMzd34JdzUmYKaot5rJhXcOMU31n077jaDPJKozdQGzJeqUCsrbkJ6F-jspdMa-GVnVy8OVgb93IXCoGM1Zi1XGaHSKn3uj9uAfD9VwQt0Lh_LMPBPAnq9SAIVNp437jLhYP8aFWMA0TNZcp1HYdVKOFj3S00YH2LsHhNb7kPfR_fwJWi8hpef_9SCfr7N-WuMYOnSDBo36soznG-sAM_ke9U" alt=""/></div>
              <div class="news-list-text"><p class="news-list-title">Résultats de l'enquête annuelle de satisfaction des employés</p><p class="news-list-sub">La direction reconnaît les retours sur les améliorations de l'espace de travail…</p></div>
              <svg class="news-list-arrow" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
            <div class="news-list-item">
              <div class="news-list-thumb"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuChNL2BsK53yk1Et0iRMY4w1I8aje_3ZdioqLmP4iJAyi6CZQnEo-d9i_DVjmMt5xwQYcTCeQ_V6WecGrCXoBOhYApRuNwqRixrOiWq3kc8cjoC181wjGiFhtQZQm-8Wc0GIFUeAx_d-W0HtKK71oUGFp8bgkCbwlfEdRpBraphKGNJDTKxsQvfceKP_DvEwsBA9Fp-Wfoowas2VG0vR_Etr-vEHNdJIwZzcd4zl9LFMLng8u0wrmK_pTeGt0EBP95VbfBqqoLE_dQ" alt=""/></div>
              <div class="news-list-text"><p class="news-list-title">Nominations ouvertes — Prix Excellence Sécurité 2024</p><p class="news-list-sub">Nommez un collègue qui incarne notre culture de la sécurité avant tout…</p></div>
              <svg class="news-list-arrow" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
          </div>
        </div>

        <div class="col-sidebar">
          <div class="widget-card">
            <div class="widget-header"><h3 class="widget-title">Activité du Répertoire</h3></div>
            <div class="activity-list">
              <div class="activity-item">
                <div class="activity-icon-wrap"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></div>
                <div class="activity-text">
                  <p><strong>Moussa B.</strong> a mis à jour ses coordonnées</p>
                  <p class="activity-meta">Ressources Humaines · il y a 12 min</p>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-icon-wrap"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></div>
                <div class="activity-text">
                  <p><strong>Sarah E.</strong> a rejoint l'équipe Logistique</p>
                  <p class="activity-meta">Nouvelle recrute · il y a 1h</p>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-icon-wrap"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                <div class="activity-text">
                  <p><strong>Dépôt Technique 4</strong> a été déplacé</p>
                  <p class="activity-meta">Secteur Ngaoundéré · il y a 3h</p>
                </div>
              </div>
            </div>
          </div>

          <div class="widget-card support-card">
            <h3 class="support-title">Besoin d'aide ?</h3>
            <p class="support-desc">Contactez le support IT pour tout problème d'accès ou de mise à jour des données du répertoire.</p>
            <button class="support-btn">Ouvrir un ticket de support</button>
          </div>

          <!-- Info session utilisateur connecté -->
          <div class="widget-card">
            <div class="widget-header"><h3 class="widget-title">Ma session</h3></div>
            <div class="profil-fields" style="margin-top:12px">
              <div class="profil-field">
                <label>Identifiant</label>
                <p style="font-family:monospace"><?= htmlspecialchars($current_user['id']) ?></p>
              </div>
              <div class="profil-field">
                <label>Département</label>
                <p><?= htmlspecialchars($current_user['department'] ?: '—') ?></p>
              </div>
              <div class="profil-field">
                <label>Rôle</label>
                <p><?= $current_user['is_admin']
                    ? '<span style="color:var(--red);font-weight:600">Administrateur</span>'
                    : 'Utilisateur' ?></p>
              </div>
              <div class="profil-field">
                <label>Connecté depuis</label>
                <p><?= date('d/m/Y H:i', $current_user['login_time']) ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>

  <script>
  // Charger les stats depuis l'API
  fetch('backend/api.php?action=stats')
    .then(r => r.json())
    .then(d => {
      if (!d.success) return;
      const el = document.getElementById('stat-employes');
      if (el) el.textContent = Number(d.total_employes).toLocaleString('fr-FR') + ' Actifs';
      const el2 = document.getElementById('stat-depts');
      if (el2) el2.textContent = d.total_services + ' Unités';
    })
    .catch(() => {}); // silencieux si l'API n'est pas encore prête
  </script>
  <script src="js/main.js"></script>
</body>
</html>