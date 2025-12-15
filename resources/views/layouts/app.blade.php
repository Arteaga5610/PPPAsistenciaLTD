<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'ASISTENCIAP3') }}</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🕐</text></svg>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://kit.fontawesome.com/7c98d24672.js" crossorigin="anonymous"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body { 
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      background: #f4f6f9;
      color: #2c3e50;
    }
    
    /* Layout principal */
    .admin-layout {
      display: flex;
      min-height: 100vh;
    }
    
    /* Sidebar */
    .sidebar {
      width: 260px;
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
      color: white;
      position: fixed;
      height: 100vh;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      z-index: 1000;
      display: flex;
      flex-direction: column;
    }
    
    .sidebar-header {
      padding: 25px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-logo {
      font-size: 22px;
      font-weight: 700;
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .sidebar-logo-icon {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }
    
    .sidebar-nav {
      padding: 20px 0;
      flex: 1 1 auto;
      overflow-y: auto;
    }
    
    .nav-section-title {
      padding: 10px 20px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: rgba(255,255,255,0.5);
      font-weight: 600;
      margin-top: 10px;
    }
    
    .nav-item {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: rgba(255,255,255,0.8);
      text-decoration: none;
      transition: all 0.2s ease;
      border-left: 3px solid transparent;
    }

    /* Evita que el footer absoluto del sidebar cubra y desactive los enlaces
       reservando espacio inferior y asegurando el stacking apropiado. */
    .nav-item { position: relative; z-index: 2; }
    .sidebar-footer { z-index: 1; }
    
    .nav-item:hover {
      background: rgba(255,255,255,0.1);
      color: white;
      border-left-color: #667eea;
    }
    
    .nav-item.active {
      background: rgba(102, 126, 234, 0.2);
      color: white;
      border-left-color: #667eea;
    }
    
    .nav-item-icon {
      margin-right: 12px;
      font-size: 18px;
      width: 20px;
      text-align: center;
    }
    
    .nav-item-text {
      font-size: 14px;
      font-weight: 500;
    }
    
    .sidebar-footer {
      position: relative;
      width: 100%;
      padding: 20px;
      border-top: 1px solid rgba(255,255,255,0.1);
      background: rgba(0,0,0,0.06);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.02);
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 10px;
    }
    
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 16px;
    }
    
    .user-details {
      flex: 1;
    }
    
    .user-name {
      font-size: 14px;
      font-weight: 600;
      color: white;
    }
    
    .user-role {
      font-size: 12px;
      color: rgba(255,255,255,0.6);
    }
    
    .btn-logout {
      width: 100%;
      padding: 10px;
      background: rgba(220, 53, 69, 0.2);
      color: #ff6b6b;
      border: 1px solid rgba(220, 53, 69, 0.3);
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.2s ease;
    }
    
    .btn-logout:hover {
      background: #dc3545;
      color: white;
      border-color: #dc3545;
    }
    
    /* Main content */
    .main-content {
      flex: 1;
      margin-left: 260px;
      min-height: 100vh;
    }
    
    /* Topbar */
    .topbar {
      background: white;
      padding: 15px 30px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.08);
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    
    .topbar-left h2 {
      font-size: 20px;
      color: #2c3e50;
      font-weight: 600;
    }
    
    .topbar-right {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .topbar-time {
      font-size: 13px;
      color: #7f8c8d;
    }
    
    /* Content area */
    .content-wrapper {
      padding: 0;
    }
    
    /* Flash messages */
    .flash {
      margin: 20px 30px;
      padding: 15px 20px;
      border-radius: 8px;
      background: #d1ecf1;
      border: 1px solid #bee5eb;
      color: #0c5460;
      font-size: 14px;
    }
    
    .flash.error {
      background: #f8d7da;
      border-color: #f5c6cb;
      color: #721c24;
    }
    
    /* Estilos legacy para compatibilidad */
    .container { 
      max-width: none;
      margin: 0;
      background: transparent;
      padding: 0;
      box-shadow: none;
    }
    
    table { 
      width: 100%; 
      border-collapse: collapse;
    }
    
    th, td { 
      padding: 12px;
      text-align: left;
    }
    
    .actions a, .actions button { 
      margin-right: 6px;
    }
    
    label { 
      display: block;
      margin-top: 10px;
      font-weight: 500;
      color: #2c3e50;
    }
    
    input[type=text], 
    input[type=email], 
    input[type=password],
    input[type=datetime-local], 
    input[type=date],
    select, 
    textarea {
      width: 100%;
      padding: 10px 12px;
      box-sizing: border-box;
      border: 2px solid #e9ecef;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.2s ease;
    }
    
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: #667eea;
    }
    
    .row { 
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }
    
    .col { 
      flex: 1;
      min-width: 200px;
    }
    
    .btn { 
      display: inline-block;
      padding: 10px 18px;
      border: 1px solid #ddd;
      background: white;
      cursor: pointer;
      font-size: 14px;
      border-radius: 6px;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .btn:hover {
      background: #f8f9fa;
    }
    
    .btn.primary { 
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
    }
    
    .btn.primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }
    
    .badge { 
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        width: 70px;
      }
      
      .main-content {
        margin-left: 70px;
      }
      
      .nav-item-text,
      .nav-section-title,
      .sidebar-logo span,
      .user-details,
      .btn-logout {
        display: none;
      }
      
      .sidebar-footer {
        padding: 10px;
      }
      
      .user-avatar {
        margin: 0 auto;
      }
    }
  </style>
</head>
<body>

  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <a href="/" class="sidebar-logo">
          <div class="sidebar-logo-icon"><i class="fas fa-clock"></i></div>
          <span>ASISTENCIA</span>
        </a>
      </div>
      
      <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->is('/') || request()->is('dashboard') ? 'active' : '' }}">
          <span class="nav-item-icon"><i class="fas fa-home"></i></span>
          <span class="nav-item-text">Dashboard</span>
        </a>
        
        <a href="{{ route('employees.index') }}" class="nav-item {{ request()->is('employees*') ? 'active' : '' }}">
          <span class="nav-item-icon"><i class="fas fa-users"></i></span>
          <span class="nav-item-text">Empleados</span>
        </a>
        
        <a href="{{ route('asistencias.hoy') }}" class="nav-item {{ request()->is('asistencias*') ? 'active' : '' }}">
          <span class="nav-item-icon"><i class="fas fa-calendar-check"></i></span>
          <span class="nav-item-text">Asistencias</span>
        </a>
        
        <div class="nav-section-title">Horarios</div>
        
        <a href="{{ route('horarios.index') }}" class="nav-item {{ request()->is('horarios') && !request()->is('horarios/*') ? 'active' : '' }}">
          <span class="nav-item-icon"><i class="fas fa-calendar-alt"></i></span>
          <span class="nav-item-text">Ver Horarios</span>
        </a>
        
        <a href="{{ route('horarios.create') }}" class="nav-item {{ request()->is('horarios/create') ? 'active' : '' }}">
          <span class="nav-item-icon"><i class="fas fa-plus-circle"></i></span>
          <span class="nav-item-text">Registrar Horario</span>
        </a>
        
        <a href="{{ route('horarios.updateForm') }}" class="nav-item {{ request()->is('horarios/actualizar') ? 'active' : '' }}">
          <span class="nav-item-icon"><i class="fas fa-edit"></i></span>
          <span class="nav-item-text">Actualizar Horario</span>
        </a>
        
        <a href="{{ route('horarios.template.create') }}" class="nav-item {{ request()->is('horarios/plantilla/create') ? 'active' : '' }}">
          <span class="nav-item-icon"><i class="fas fa-clipboard-list"></i></span>
          <span class="nav-item-text">Crear Plantilla</span>
        </a>
        
        <div class="nav-section-title">Configuración</div>
        
        <a href="{{ route('hik.test') }}" class="nav-item {{ request()->is('hik*') ? 'active' : '' }}">
          <span class="nav-item-icon"><i class="fas fa-fingerprint"></i></span>
          <span class="nav-item-text">Autenticación</span>
        </a>
      </nav>
      
      @auth
      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
          <div class="user-details">
            <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
            <div class="user-role">Administrador</div>
          </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
        </form>
      </div>
      @endauth
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <div class="topbar">
        <div class="topbar-left">
          <h2>{{ $pageTitle ?? 'Panel de Control' }}</h2>
        </div>
        <div class="topbar-right">
          <span class="topbar-time" id="current-time"></span>
        </div>
      </div>
      
      <div class="content-wrapper">
        <div class="container">
          @if(session('ok')) 
            <div class="flash">✅ {{ session('ok') }}</div> 
          @endif

          @if($errors->any())
            <div class="flash error">
              <strong>⚠️ Errores encontrados:</strong>
              <ul style="margin: 10px 0 0 20px;">
                @foreach($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @yield('content')
        </div>
      </div>
    </main>
  </div>

  <script>
    // Reloj en tiempo real
    function updateTime() {
      const now = new Date();
      const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      };
      document.getElementById('current-time').textContent = now.toLocaleDateString('es-PE', options);
    }
    updateTime();
    setInterval(updateTime, 1000);
  </script>

</body>
</html>
