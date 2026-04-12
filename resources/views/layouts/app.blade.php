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
              <li class="nav-item"><a class="nav-link" href="{{ route('sorties.index') }}">Sorties</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('facturations.index') }}">Facturation</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('paiements.index') }}">Paiements</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('clients.index') }}">Clients</a></li>
              <li class="nav-item"><a class="nav-link" href="{{ route('vehicules.index') }}">Véhicules</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#comptaMenu">Comptabilité</a>
                <div class="collapse" id="comptaMenu">
                  <ul class="nav flex-column ms-2">
                    <li class="nav-item"><a class="nav-link" href="{{ route('journal_comptes.index') ?? '#' }}">Journal</a></li>
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
    @stack('scripts')
  </body>
</html>
