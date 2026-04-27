<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parking ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      /* Sidebar collapse behaviour */
      @media (min-width: 768px) {
        body.collapsed-sidebar #sidebar { display: none !important; }
        body.collapsed-sidebar main { width: 100% !important; }
      }
      .quick-card { min-height: 110px; }
      /* Fix pagination arrows/icon sizing (prevent oversized SVG/icons) */
      .pagination .page-link { padding: .25rem .5rem; }
      .pagination .page-link svg { width: 1em; height: 1em; vertical-align: -0.125em; }
      .pagination .page-link .bi { font-size: 0.95rem; vertical-align: -0.125em; }
      /* Smaller variant used on clients/vehicules index */
      .small-pagination .page-link { padding: .15rem .35rem; }
      .small-pagination .page-link svg { width: .9em; height: .9em; }
      .small-pagination .page-link .bi { font-size: 0.75rem; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Parking ERP</a>
        @auth
          <button id="sidebarToggle" class="btn btn-sm btn-outline-light me-2 d-none d-md-inline" title="Toggle menu"><i class="bi bi-list"></i></button>
        @endauth
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navmenu">
          <ul class="navbar-nav ms-auto">
            @auth
              <li class="nav-item"><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form></li>
            @endauth
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        @auth
        <div id="sidebar" class="col-md-2 bg-light vh-100 d-none d-md-block sidebar">
          <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h5 class="mb-0">Menu</h5>
              <button id="sidebarHide" class="btn btn-sm btn-outline-secondary d-none d-md-inline" title="Hide menu"><i class="bi bi-x"></i></button>
            </div>
            <ul class="nav flex-column">
              <li class="nav-item"><a class="nav-link" href="{{ route('entrees.index') }}">Entrées</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('facturations.index') }}">Facturation</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('sorties.index') }}">Sorties</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('caisse.index') }}">Caisse</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('clients.index') }}">Clients</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('vehicules.index') }}">Véhicules</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#comptaMenu">Comptabilité</a>
                <div class="collapse" id="comptaMenu">
                  <ul class="nav flex-column ms-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('journal_comptes.index') ?? '#' }}">Journal</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('journal_comptes.grand_index') }}">Grand Livre</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('journal_comptes.balances') }}">Balances</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('journal_comptes.compte_resultat') }}">Compte de Résultat</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('journal_comptes.bilan') }}">Bilan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('comptes.index') }}">Comptes</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('classes.index') }}">Classes</a></li>
                  </ul>
                </div>
              </li>
              @if(auth()->user() && auth()->user()->role === 'superadmin')
                <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Users</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Catégories</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('settings.entreprise') }}">Entreprise</a></li>
              @endif
            </ul>
          </div>
        </div>
        @endauth

        <main class="col-md-10 p-4">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @yield('content')
        </main>
      </div>
    </div>
    
    <!-- Toast container -->
    <div id="toastContainer" style="position:fixed;top:1rem;right:1rem;z-index:1080"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      (function(){
        const toggle = document.getElementById('sidebarToggle');
        const hideBtn = document.getElementById('sidebarHide');
        function setCollapsed(val){
          if(val){
            document.body.classList.add('collapsed-sidebar');
            localStorage.setItem('sidebarCollapsed','1');
          } else {
            document.body.classList.remove('collapsed-sidebar');
            localStorage.removeItem('sidebarCollapsed');
          }
        }
        // init
        if(localStorage.getItem('sidebarCollapsed') === '1'){
          document.body.classList.add('collapsed-sidebar');
        }
        if(toggle){ toggle.addEventListener('click', function(e){ e.preventDefault(); setCollapsed(!document.body.classList.contains('collapsed-sidebar')); }); }
        if(hideBtn){ hideBtn.addEventListener('click', function(e){ e.preventDefault(); setCollapsed(true); }); }
      })();
    </script>
    <script>
      // Toast helper: showBootstrapToast(message, type)
      function showToast(message, type='success', timeout=4500) {
        try {
          const container = document.getElementById('toastContainer');
          if (!container) return;
          const toastId = 't_' + Date.now() + Math.floor(Math.random()*1000);
          const wrapper = document.createElement('div');
          wrapper.innerHTML = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex">
                <div class="toast-body">${String(message).replace(/\n/g,'<br>')}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>`;
          const el = wrapper.firstElementChild;
          container.appendChild(el);
          const bs = new bootstrap.Toast(el, { delay: timeout });
          el.addEventListener('hidden.bs.toast', function(){ el.remove(); });
          bs.show();
        } catch(e){ console.error('showToast error', e); }
      }

      // Scan main for VISIBLE alerts and convert them to toasts; leave hidden alerts (e.g. builders) intact.
      function initToasts() {
        try {
          const main = document.querySelector('main');
          if (!main) return;
          const allAlerts = Array.from(main.querySelectorAll('.alert'));
          // only process alerts that are visible (not display:none)
          const alerts = allAlerts.filter(a => {
            try { return a.offsetParent !== null; } catch(e){ return false; }
          });
          alerts.forEach(a => {
            const type = a.classList.contains('alert-success') ? 'success' : (a.classList.contains('alert-danger') ? 'danger' : 'info');
            const msg = a.innerText.trim();
            if (msg) showToast(msg, type);
            a.remove();
          });
        } catch(e) { console.error('initToasts error', e); }
      }
      document.addEventListener('DOMContentLoaded', function(){ initToasts(); });
      window.initToasts = initToasts;
      window.showToast = showToast;
    </script>
    @stack('scripts')
  </body>
</html>
