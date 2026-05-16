<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — Camrail Directory</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/main.css"/>
</head>
<body data-page="dashboard">
  <?php

session_start();

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();

}
?>

  <!-- ═══════ SIDEBAR ═══════ -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-mark">C</div>
      <div><p class="sidebar-brand">Camrail</p><p class="sidebar-sub">Employee Directory</p></div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.html" class="nav-link" data-page="dashboard">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        <span>Dashboard</span>
      </a>
      <a href="directory.html" class="nav-link" data-page="directory">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Répertoire</span>
      </a>
      <a href="departments.html" class="nav-link" data-page="departments">
        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Départements</span>
      </a>
      <a href="admin.html" class="nav-link" data-page="admin">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span>Administration</span>
      </a>
      <a href="profile.html" class="nav-link" data-page="profile">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        <span>Profil</span>
      </a>
      <a href="settings.html" class="nav-link" data-page="settings">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <span>Paramètres</span>
      </a>
    </nav>
    <div class="sidebar-user">
      <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCukTL4w0-5HMlwHzovohG-9aYm8LiFGEPuBEImdkOYoeG48-uWefQLZldD_9FU-A35LG6S12GKmLFNprA0_zFBKPyWGqLqJBHp8CCDkfCdTp4JnZkmvxP0n0WeLYLFr_qv5d5Gp7Zgb5RIDtioocK2G1WlpcytXgIcBkuQYu00heir1GpeT2y0-wczOD1ITURxvFVtEAWxeZ6o19ccgZW60zGz0fMEl6QrGMz9Jyp4fpRrSCAAMxxxMtNiY9EU9AXPH1PR8PNuzwk" alt="Avatar" class="user-avatar"/>
      <div class="user-info"><p class="user-name">Jean-Pierre K.</p><p class="user-role">Logistics Manager</p></div>
      <a href="login.html" class="logout-btn" title="Se déconnecter">
        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </aside>

  <!-- ═══════ MAIN ═══════ -->
  <div class="main-wrapper">

    <!-- Topbar -->
    <header class="topbar">
      <div class="search-wrap">
        <svg class="search-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" class="search-input" placeholder="Rechercher un employé, département…" autocomplete="off"/>
        <kbd class="search-kbd">⌘K</kbd>
      </div>
      <div class="topbar-actions">
        <button class="topbar-btn">
          <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span class="notif-badge">3</span>
        </button>
        <button class="topbar-btn">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </button>
      </div>
    </header>

    <!-- Contenu -->
    <main class="content">

      <!-- Hero -->
      <div class="page-hero">
        <div>
          <h1 class="hero-title">Bonjour, Jean-Pierre 👋</h1>
          <p class="hero-sub">Restez à jour avec les dernières opérations ferroviaires et la disponibilité de votre équipe.</p>
        </div>
        <div class="hero-actions">
          <button class="btn-secondary">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Planning des quarts
          </button>
          <button class="btn-primary">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvelle entrée
          </button>
        </div>
      </div>

      <!-- Cartes rapides -->
      <div class="quick-grid">
        <a href="directory.html" class="quick-card">
          <div class="quick-card-top">
            <div class="quick-icon red"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            <svg class="quick-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <h3 class="quick-title">Tous les contacts</h3>
          <p class="quick-desc">Accès au répertoire complet des 2 400+ employés Camrail à travers le Cameroun.</p>
          <div class="quick-footer">
            <span class="quick-stat red">2 482 Actifs</span>
            <div class="avatar-stack">
              <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDHnvK8Nqd3DX6nmi62yNFcCSX-wXewI-s4AnncKX6K-olqLXGDTh3kt6QskgVkEPPgMSlvwgw-ABZe7dIxlfjU_QZJi3kyi-MlCZeq-KvqokvzFTeU0LGYAbnILhuSzWz-hSr7SVJaxeK3JAvgb5qtuJ65rFWR8G_CFiJHvGh-qZ5l2QwuX-fukyKZ0w3mMUcqFW2y4gklkN8deU0T13Y2WJBgn75bRgKdUk7rUNwA9ZoS1HfC_qven-GALopqsW9mRAZOxjSMECk" alt=""/>
              <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDbZ56fFsuUs6F9JdMmZks1bsa1e-mJnvXouhjYE5djcgly9b0wrVsnGrubh9URJQPYIvgYCFi3DYujrWN7kpJNeStN0QXCyg_09BU0fEIUVlH5wO_9ImK8HxOiOkaoP0dXFaJNv3DUjA-1SGQfvqRm3e03YUDB_Izzrb0alzPf1sIkTMt9JyBufunAb8E860uadpDugS01zZbYpvcFULRZ17aalK95fvS4NI7slTsBgTG3DO_iGl9-0HKpkpwE5V1SdDLCWpw-X3g" alt=""/>
              <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDovdnXN70GCIOAJVDkD-eZyU01Z_xPzVd0qayCUBvK9ncTYTV7NZQrr6s16lm_YBPedIkN9-9-1uAC_qb7J_eo_M9cy0boWaUZ6qOy01imo2hCwRBUfEewYb0UhUd-Ervop-FhT63-x2TqIGgFeHARNkRXNnpAZi_AKt7VxpEqN4g1WjWXmWdRkOzVxO4eVf3cjAFCIZh-meHGqzGLBGMA-456hxwmFA_1P40Upxu2pMvxk9USFKzMHvE5iYNGNLf0a6gc_BL_Yq8" alt=""/>
            </div>
          </div>
        </a>
        <a href="directory.html" class="quick-card">
          <div class="quick-card-top">
            <div class="quick-icon amber"><svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
            <svg class="quick-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <h3 class="quick-title">Mes favoris</h3>
          <p class="quick-desc">Accès rapide aux collègues et départements avec lesquels vous interagissez le plus.</p>
          <div class="quick-footer"><span class="quick-stat amber">12 Favoris</span></div>
        </a>
        <a href="departments.html" class="quick-card">
          <div class="quick-card-top">
            <div class="quick-icon blue"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
            <svg class="quick-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <h3 class="quick-title">Départements</h3>
          <p class="quick-desc">Parcourez la structure d'entreprise des Opérations à l'Administration.</p>
          <div class="quick-footer"><span class="quick-stat blue">24 Unités</span></div>
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
              <div class="activity-item"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCUi1Y6omgay4q31L6D7g_8ICa9rpgwIuGgDeTT_T-ZC9B8riiAAUIlM6qt315RAUxvPMOZfGH3ZgQSa-_kNV2jwMWnfzy3C2r9LTHyewoI_izucmR5_jRStC2BUUZqjYRqXc_E0ZVdDXHhZL3vMys68Jls_IT_NNxcDwUMTUXPm4ilQ-mnqZLzJtVgdFt_xjU_zgzUk6eWlNFvonauqlRkEARxabPIxu7-kxR2D2ydWJBMCZvm8uOoxyStGrM51MN1wTXOgGgSIfc" alt="" class="activity-avatar"/><div class="activity-text"><p><strong>Moussa B.</strong> a mis à jour ses coordonnées</p><p class="activity-meta">Ressources Humaines · il y a 12 min</p></div></div>
              <div class="activity-item"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuD5lFldl2wP_jfRu0iDkRlREwMxT6wMA6pasaJtOKns5RQZuq_0Xxxp_zPY4mI4S-HwzXDaynzMEJxWTIUXL3w7ww9Xy_hAay0-Px3478MBhvvM6XpUYJAkzrCEcK-FmU-hyWC_jSViPhyFxAP28ojUKGEItjcXSmUm_yYVQqFEA3rh6PrEog0oL-LNUneGjNYt3tY1MX85q6GlLv2-y_ehEJmy_T3oVaYfRumAwu-CLq1cdI-wj6nRv_gdGDZ_XS2x0aMaOm0N9aY" alt="" class="activity-avatar"/><div class="activity-text"><p><strong>Sarah E.</strong> a rejoint l'équipe Logistique</p><p class="activity-meta">Nouvelle recrute · il y a 1h</p><button class="activity-action-btn">Envoyer un message de bienvenue</button></div></div>
              <div class="activity-item"><div class="activity-icon-wrap"><svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div><div class="activity-text"><p><strong>Dépôt Technique 4</strong> a été déplacé</p><p class="activity-meta">Secteur Ngaoundéré · il y a 3h</p></div></div>
            </div>
          </div>
          <div class="widget-card support-card">
            <h3 class="support-title">Besoin d'aide ?</h3>
            <p class="support-desc">Contactez le support IT pour tout problème d'accès ou de mise à jour des données du répertoire.</p>
            <button class="support-btn">Ouvrir un ticket de support</button>
          </div>
          <div class="widget-card">
            <div class="widget-header"><h3 class="widget-title">Statut du Réseau</h3><span class="status-dot"></span></div>
            <div class="map-wrap">
              <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuB4IgHHtFd_GKKjGeCMIYkDbuH4XV-EMl8DZyR1cSa05rHsoRV5-Q1PjIaXAx70xJsoBo9UsPXMfx3e1NW9k_BREShByWgNZOHZHXtBbhsxc0Q0QSelSC3kWzG8oUeEq9F992Y3jWHRlyjypMEiWIUC-et3H08R-NLYwxRhl9HEfwQnyri8X8nbs6oP-Bk6ZO5OhDA-itpPfvuqyEpq2oznRRF9ysIcgJ4Lh_UQkdkDVPx8FNcq8rQ3xFgZyd-L6ap6iUsWdh4nDqc" alt="Carte" class="map-img"/>
              <div class="map-overlay"><button class="map-btn"><svg viewBox="0 0 24 24"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>Explorer la carte</button></div>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>

  <script src="js/main.js"></script>
</body>
</html>
