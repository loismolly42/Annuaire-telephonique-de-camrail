/* ═══════════════════════════════════════════════════
   main.js — Camrail Directory
   Commun à toutes les pages
════════════════════════════════════════════════════ */

(function () {

  /* ── Lien actif dans la sidebar selon la page courante ── */
  var currentPage = document.body.dataset.page;
  if (currentPage) {
    var activeLink = document.querySelector('.nav-link[data-page="' + currentPage + '"]');
    if (activeLink) activeLink.classList.add('active');
  }

  /* ── Toast notification ── */
  window.showToast = function (msg, type) {
    var bg = (type === 'error') ? '#b91c1c' : '#c0000c';
    var t  = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {
      position: 'fixed', bottom: '24px', right: '24px',
      background: bg, color: '#fff',
      padding: '12px 20px', borderRadius: '8px',
      fontSize: '13px', fontFamily: "'DM Sans',sans-serif",
      fontWeight: '600', boxShadow: '0 4px 16px rgba(0,0,0,.18)',
      zIndex: 9999, opacity: 0, transition: 'opacity .3s'
    });
    document.body.appendChild(t);
    requestAnimationFrame(function () { t.style.opacity = 1; });
    setTimeout(function () {
      t.style.opacity = 0;
      setTimeout(function () { t.remove(); }, 300);
    }, 3000);
  };

  /* ── Avatar preview (page settings) ── */
  var cam   = document.getElementById('btn-upload-avatar');
  var input = document.getElementById('input-avatar');
  var img   = document.getElementById('profile-avatar');
  if (cam && input && img) {
    cam.addEventListener('click', function () { input.click(); });
    input.addEventListener('change', function (e) {
      var file = e.target.files[0];
      if (file) img.src = URL.createObjectURL(file);
    });
  }

  /* ── Boutons Save / Cancel (page settings) ── */
  document.querySelectorAll('[data-action="save"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      showToast('Modifications enregistrées !');
    });
  });
  document.querySelectorAll('[data-action="cancel"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var form = document.getElementById('edit-profile-form');
      if (form) form.reset();
    });
  });

})();
