<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php $__ent = \App\Models\Entreprise::first(); @endphp
    <title>{{ $__ent->nom ?? 'Parking ERP' }}</title>
    @if(!empty($__ent->favicon))
      <link rel="icon" type="image/png" href="{{ asset('storage/' . $__ent->favicon) }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0d6efd">
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

      /* Enhanced sidebar styles */
      .sidebar { background: linear-gradient(180deg,#ffffff,#f8f9fa); border-radius:6px; }
      .sidebar .nav-link { color: #333; padding: .5rem .75rem; border-radius:6px; }
      .sidebar .nav-link:hover { background: rgba(0,0,0,0.03); color:#000; text-decoration:none; }
      .sidebar .nav-link .bi { font-size:1rem; color:#0d6efd; }
      .sidebar .collapse .nav-link { padding-left:1.25rem; }
      .sidebar-wrapper .sidebar { margin: .75rem; }
      /* Offcanvas mobile list */
      .offcanvas .list-group-item { border:0; }
      .offcanvas .list-group-item:hover { background: #f1f3f5; }
      /* Brand tweaks */
      .navbar-brand { font-weight:700; letter-spacing:.5px; }
      /* Dashboard responsive tweaks */
      @media (max-width: 767.98px) {
        .dashboard-title { display: none !important; }
        .dashboard-actions { gap: .5rem; }
        .dashboard-actions .btn { white-space: nowrap; }
        .dashboard-actions { flex-wrap: nowrap; }
      }
      /* Ensure tables can scroll horizontally on small screens */
      .table-responsive { -webkit-overflow-scrolling: touch; overflow-x: auto; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">IDDI LOGISTIC</a>
        @auth
          <!-- Single menu toggle: opens offcanvas on mobile, toggles sidebar on desktop -->
          <button id="sidebarToggle" type="button" class="btn btn-sm btn-outline-light me-2" title="Menu" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas"><i class="bi bi-list"></i></button>
        @endauth
        @guest
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        @endguest
        <div class="collapse navbar-collapse" id="navmenu">
          <ul class="navbar-nav ms-auto">
            @auth
              <li class="nav-item"><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Deconnexion</button></form></li>
            @endauth
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        @auth
        <div id="sidebar" class="col-md-2 d-none d-md-block sidebar-wrapper">
          <aside class="bg-light vh-100 sidebar p-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Menu</h5>
              <button id="sidebarHide" class="btn btn-sm btn-outline-secondary d-none d-md-inline" title="Hide menu"><i class="bi bi-x"></i></button>
            </div>
            <ul class="nav flex-column">
              <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('entrees.index') }}"><i class="bi bi-box-arrow-in-right me-2"></i>Entrées</a></li>
              <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('facturations.index') }}"><i class="bi bi-receipt me-2"></i>Facturation</a></li>
              <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('sorties.index') }}"><i class="bi bi-box-arrow-right me-2"></i>Sorties</a></li>
              <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('stocks_physique.index') }}"><i class="bi bi-layers me-2"></i>Stocks</a></li>
              <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('caisse.index') }}"><i class="bi bi-cash-stack me-2"></i>Caisse</a></li>
              <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('clients.index') }}"><i class="bi bi-people me-2"></i>Clients</a></li>
              <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('vehicules.index') }}"><i class="bi bi-truck me-2"></i>Véhicules</a></li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-start" data-bs-toggle="collapse" href="#comptaMenu" role="button" aria-expanded="false" aria-controls="comptaMenu"><i class="bi bi-journal-text me-2"></i>Comptabilité</a>
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
                <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('users.index') }}"><i class="bi bi-person-gear me-2"></i>Utilisateurs</a></li>
                <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('categories.index') }}"><i class="bi bi-tags me-2"></i>Catégories</a></li>
                <li class="nav-item"><a class="nav-link d-flex align-items-center" href="{{ route('settings.entreprise') }}"><i class="bi bi-building me-2"></i>Entreprise</a></li>
              @endif
            </ul>
          </aside>
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
    <style>
      /* PWA install banner */
      #pwaInstallBanner { position: fixed; left: 1rem; right:1rem; bottom: 1rem; z-index: 2000; display:none; }
      #pwaInstallBanner .card { display:flex; align-items:center; gap:1rem; }
      #pwaInstallBanner .actions { margin-left:auto; }
    </style>
    <div id="pwaInstallBanner" aria-hidden="true">
      <div class="card shadow-sm p-2">
        <div class="d-flex align-items-center">
          <img src="/icons/icon-192.svg" alt="app" style="width:48px;height:48px;border-radius:8px;">
          <div class="ms-2">
            <strong>Installer l'application</strong>
            <div class="small text-muted">Installez Parking en application sur votre appareil.</div>
          </div>
          <div class="actions">
            <button id="pwaInstallBtn" class="btn btn-primary btn-sm">Installer</button>
            <button id="pwaDismissBtn" class="btn btn-link btn-sm text-muted">Plus tard</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      // Register service worker
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/service-worker.js').catch(function(err){ console.warn('SW registration failed', err); });
        });
      }
      let deferredPrompt = null;
      const banner = document.getElementById('pwaInstallBanner');
      const installBtn = document.getElementById('pwaInstallBtn');
      const dismissBtn = document.getElementById('pwaDismissBtn');

      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        // show banner
        if (banner) { banner.style.display = 'block'; banner.setAttribute('aria-hidden','false'); }
      });

      if (installBtn) installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const choice = await deferredPrompt.userChoice;
        deferredPrompt = null;
        if (banner) { banner.style.display='none'; banner.setAttribute('aria-hidden','true'); }
      });

      if (dismissBtn) dismissBtn.addEventListener('click', () => {
        if (banner) { banner.style.display='none'; banner.setAttribute('aria-hidden','true'); }
      });
    </script>
    <script>
      document.addEventListener('DOMContentLoaded', function(){
        const toggle = document.getElementById('sidebarToggle');
        const hideBtn = document.getElementById('sidebarHide');
        // DEBUG: presence
        try { console.debug('sidebar:init toggle=', !!toggle, 'hideBtn=', !!hideBtn, 'window.innerWidth=', window.innerWidth); } catch(e){}
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

        // Single toggle behaviour:
        // - on small screens: open offcanvas (lookup at click time)
        // - on md+ screens: toggle collapsed sidebar
        if(toggle){
          toggle.addEventListener('click', function(e){
            e.preventDefault();
            try{
              console.debug('sidebar:click innerWidth=', window.innerWidth);
              if(window.innerWidth < 768){
                const offcanvasEl = document.getElementById('sidebarOffcanvas');
                console.debug('sidebar:offcanvasEl=', offcanvasEl);
                if(offcanvasEl){
                  console.debug('sidebar:bootstrap=', typeof bootstrap !== 'undefined' && !!bootstrap.Offcanvas);
                  const bs = (typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) ? (bootstrap.Offcanvas.getInstance(offcanvasEl) || new bootstrap.Offcanvas(offcanvasEl)) : null;
                  if(bs){ bs.show(); return; }
                }
              }
            }catch(err){ console.error('sidebar:err', err); }
            setCollapsed(!document.body.classList.contains('collapsed-sidebar'));
          });
        }
        if(hideBtn){ hideBtn.addEventListener('click', function(e){ e.preventDefault(); setCollapsed(true); }); }
      });
    </script>
    <script>
      // Wrap all tables inside <main> with .table-responsive to enable horizontal scrolling on small screens
      document.addEventListener('DOMContentLoaded', function(){
        try{
          const main = document.querySelector('main');
          if(!main) return;
          const tables = Array.from(main.querySelectorAll('table'));
          tables.forEach(tbl => {
            if(tbl.closest('.table-responsive')) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            tbl.parentNode.insertBefore(wrapper, tbl);
            wrapper.appendChild(tbl);
          });
        }catch(e){ console.error('table-responsive wrapper error', e); }
      });
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
    <!-- Offcanvas sidebar for mobile -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body p-0">
        <nav class="list-group list-group-flush">
          <a class="list-group-item list-group-item-action" href="{{ route('entrees.index') }}"><i class="bi bi-box-arrow-in-right me-2"></i>Entrées</a>
          <a class="list-group-item list-group-item-action" href="{{ route('facturations.index') }}"><i class="bi bi-receipt me-2"></i>Facturation</a>
          <a class="list-group-item list-group-item-action" href="{{ route('sorties.index') }}"><i class="bi bi-box-arrow-right me-2"></i>Sorties</a>
          <a class="list-group-item list-group-item-action" href="{{ route('stocks_physique.index') }}"><i class="bi bi-layers me-2"></i>Stocks</a>
          <a class="list-group-item list-group-item-action" href="{{ route('caisse.index') }}"><i class="bi bi-cash-stack me-2"></i>Caisse</a>
          <a class="list-group-item list-group-item-action" href="{{ route('clients.index') }}"><i class="bi bi-people me-2"></i>Clients</a>
          <a class="list-group-item list-group-item-action" href="{{ route('vehicules.index') }}"><i class="bi bi-truck me-2"></i>Véhicules</a>
          <div class="list-group-item">
            <a class="d-flex align-items-center justify-content-start" data-bs-toggle="collapse" href="#mobileCompta" role="button" aria-expanded="false" aria-controls="mobileCompta"><i class="bi bi-journal-text me-2"></i>Comptabilité <span class="ms-auto"><i class="bi bi-chevron-down"></i></span></a>
            <div class="collapse mt-2" id="mobileCompta">
              <a class="d-block ms-3" href="{{ route('journal_comptes.index') ?? '#' }}"><i class="bi bi-journal-bookmark me-2"></i>Journal</a>
              <a class="d-block ms-3" href="{{ route('journal_comptes.grand_index') }}"><i class="bi bi-book me-2"></i>Grand Livre</a>
              <a class="d-block ms-3" href="{{ route('journal_comptes.balances') }}"><i class="bi bi-list-check me-2"></i>Balances</a>
              <a class="d-block ms-3" href="{{ route('journal_comptes.compte_resultat') }}"><i class="bi bi-graph-up me-2"></i>Compte de Résultat</a>
              <a class="d-block ms-3" href="{{ route('journal_comptes.bilan') }}"><i class="bi bi-table me-2"></i>Bilan</a>
              <a class="d-block ms-3" href="{{ route('comptes.index') }}"><i class="bi bi-bookmarks me-2"></i>Comptes</a>
              <a class="d-block ms-3" href="{{ route('classes.index') }}"><i class="bi bi-tags me-2"></i>Classes</a>
            </div>
          </div>
          @if(auth()->user() && auth()->user()->role === 'superadmin')
            <a class="list-group-item list-group-item-action" href="{{ route('users.index') }}"><i class="bi bi-person-gear me-2"></i>Utilisateurs</a>
            <a class="list-group-item list-group-item-action" href="{{ route('categories.index') }}"><i class="bi bi-tags me-2"></i>Catégories</a>
            <a class="list-group-item list-group-item-action" href="{{ route('settings.entreprise') }}"><i class="bi bi-building me-2"></i>Entreprise</a>
          @endif
          
          <div class="list-group-item mt-2">
            <form method="POST" action="{{ route('logout') }}">@csrf
              <button type="submit" class="btn btn-link p-0 text-danger">Deconnexion</button>
            </form>
          </div>
        </nav>
      </div>
    </div>

    <script>
      // Close offcanvas when a link is clicked (mobile UX)
      document.addEventListener('DOMContentLoaded', function(){
        try{
          const off = document.getElementById('sidebarOffcanvas');
          if(!off) return;
          // Close offcanvas only for real navigation links (href not starting with '#')
          const items = off.querySelectorAll('.list-group-item');
          items.forEach(el => el.addEventListener('click', function(ev){
            try {
              const anchor = ev.target.closest('a');
              if (anchor) {
                const href = anchor.getAttribute('href') || '';
                const isCollapse = anchor.dataset && (anchor.dataset.bsToggle === 'collapse' || href.startsWith('#'));
                if (isCollapse) {
                  // do not hide offcanvas when toggling collapse menus
                  return;
                }
              }
            } catch(e) {
              // ignore and fallback to hide
            }
            const bs = bootstrap.Offcanvas.getInstance(off) || new bootstrap.Offcanvas(off);
            bs.hide();
          }));
        }catch(e){console.error(e)}
      });
    </script>
    <script>
      // Prevent full-page navigation when clicking collapse toggles (desktop and mobile)
      document.addEventListener('DOMContentLoaded', function(){
        try{
          const toggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
          toggles.forEach(a => {
            a.addEventListener('click', function(ev){
              // avoid link navigation
              ev.preventDefault();
              const selector = a.getAttribute('href') || a.dataset.bsTarget;
              if(!selector) return;
              const target = document.querySelector(selector);
              if(!target) return;
              // toggle collapse via Bootstrap API
              const instance = bootstrap.Collapse.getOrCreateInstance(target);
              instance.toggle();
            });
          });
        } catch(e){ console.error('collapse-toggle-fix', e); }
      });
    </script>
    @stack('scripts')
  </body>
</html>
