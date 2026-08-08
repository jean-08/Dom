document.addEventListener('DOMContentLoaded', function () {
  // --- Sidebar mobile toggle ---
  var sidebar = document.querySelector('.da-sidebar');
  var overlay = document.querySelector('.da-overlay');
  document.querySelectorAll('[data-toggle-sidebar]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      sidebar.classList.toggle('show');
      overlay.classList.toggle('show');
    });
  });
  if (overlay) {
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });
  }

  // --- Auto-show toasts present on page load ---
  document.querySelectorAll('.toast').forEach(function (el) {
    new bootstrap.Toast(el, { delay: 4500 }).show();
  });

  // --- Confirm dialogs on dangerous links/buttons ---
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm(el.getAttribute('data-confirm') || 'Confirmer cette action ?')) {
        e.preventDefault();
      }
    });
  });

  // --- Simple client-side table search filter ---
  document.querySelectorAll('[data-table-search]').forEach(function (input) {
    var tableId = input.getAttribute('data-table-search');
    var table = document.getElementById(tableId);
    if (!table) return;
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      table.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
      });
    });
  });
});

/**
 * Petit utilitaire Fetch API pour appeler api.php depuis les vues,
 * quand une interaction ne nécessite pas de recharger la page.
 * Utilise le token stocké en session via /api.php?action=login si besoin.
 */
async function domAssistApi(action, options = {}) {
  const res = await fetch('api.php?action=' + encodeURIComponent(action), {
    method: options.method || 'GET',
    headers: Object.assign({ 'Content-Type': 'application/json' }, options.headers || {}),
    body: options.body ? JSON.stringify(options.body) : undefined,
  });
  return res.json();
}
