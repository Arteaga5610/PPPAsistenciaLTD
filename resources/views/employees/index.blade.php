@extends('layouts.app')

@section('content')
<style>
  .employees-page {
    padding: 8px;
  }

  /* Header del panel */
  .page-header {
    background: white;
    padding: 16px 12px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
  }

  .page-title-section h1 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
  }

  .page-subtitle {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
  }

  .btn-new {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border: none;
    box-shadow: 0 2px 8px rgba(17, 153, 142, 0.3);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-new:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4);
    color: white;
  }

  /* Sección de búsqueda y filtros */
  .filters-section {
    background: white;
    padding: 20px 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 25px;
  }

  .filters-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: 15px;
    align-items: end;
  }

  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .filter-label {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .search-input-wrapper {
    position: relative;
  }

  .search-input-wrapper input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
  }

  .search-input-wrapper input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #7f8c8d;
  }

  .filter-select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .btn-search {
    padding: 12px 25px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 46px;
  }

  .btn-search:hover {
    background: #5568d3;
    transform: translateY(-1px);
  }

  .active-filters {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: #e7f5ff;
    color: #0c8599;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
  }

  .filter-tag a {
    color: #0c8599;
    text-decoration: none;
    opacity: 0.7;
    transition: opacity 0.2s;
  }

  .filter-tag a:hover {
    opacity: 1;
  }

  @media (max-width: 992px) {
    .filters-grid {
      grid-template-columns: 1fr;
    }
  }

  /* Tabla de datos */
  .data-table-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
  }

  .table-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
  }

  .table-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
  }

  /* ✅ Scroll horizontal REAL en móvil */
  .table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    min-width: 980px; /* fuerza ancho mínimo para que haya scroll y no se escondan columnas */
  }

  thead th {
    background: #f8f9fa;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #495057;
    padding: 14px 20px;
    text-align: left;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
  }

  tbody tr {
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #f1f3f5;
  }

  tbody tr:hover {
    background-color: #f8f9fa;
  }

  tbody td {
    padding: 16px 20px;
    font-size: 14px;
    color: #2c3e50;
    white-space: nowrap; /* evita que se apilen y rompan el layout en móvil */
  }

  tbody tr:last-child {
    border-bottom: none;
  }

  .badge {
    background: #e7f5ff;
    color: #0c8599;
    padding: 5px 12px;
    border-radius: 5px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    font-weight: 600;
  }

  .employee-name {
    font-weight: 600;
    color: #2c3e50;
  }

  .hire-date {
    color: #7f8c8d;
    font-size: 13px;
  }

  /* Credenciales */
  .cred-item {
    display: inline-block;
    margin-right: 15px;
    font-size: 14px;
    vertical-align: middle;
  }

  .cred-icon {
    display: inline-block;
    width: 22px;
    text-align: center;
    margin-right: 5px;
    opacity: 0.25;
    filter: grayscale(100%);
    font-size: 16px;
  }

  .cred-icon.present {
    opacity: 1;
    filter: none;
  }

  .cred-val {
    font-weight: 600;
    margin-right: 6px;
    color: #2c3e50;
  }

  /* Botones de acción */
  td.actions {
    white-space: nowrap;
  }

  td.actions a,
  td.actions button {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    margin-right: 5px;
    transition: all 0.15s ease;
    border: none;
    cursor: pointer;
    white-space: nowrap;
  }

  td.actions a:first-child {
    background: #fff3cd;
    color: #856404;
  }

  td.actions a:first-child:hover {
    background: #ffc107;
    color: #fff;
  }

  td.actions a:nth-child(2) {
    background: #d1ecf1;
    color: #0c5460;
  }

  td.actions a:nth-child(2):hover {
    background: #17a2b8;
    color: #fff;
  }

  td.actions .btn {
    background: #f8d7da;
    color: #721c24;
  }

  td.actions .btn:hover {
    background: #dc3545;
    color: #fff;
  }

  /* Paginación */
  .pagination-section {
    padding: 20px 25px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
  }

  .pagination-info {
    color: #6c757d;
    font-size: 14px;
  }

  .pagination-info strong {
    color: #2c3e50;
    font-weight: 600;
  }

  .pagination-controls {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .pagination-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-width: 44px;
    height: 44px;
    padding: 0 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    text-decoration: none;
    background: white;
    transition: all 0.2s ease;
    cursor: pointer;
    white-space: nowrap;
  }

  .pagination-btn:hover:not(.disabled) {
    border-color: #667eea;
    background: #f0f3ff;
    color: #667eea;
    transform: translateY(-2px);
  }

  .pagination-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none;
  }

  .pagination-btn svg {
    width: 16px;
    height: 16px;
  }

  .pagination-pages {
    display: flex;
    gap: 6px;
    align-items: center;
  }

  .pagination-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    padding: 0 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    text-decoration: none;
    background: white;
    transition: all 0.2s ease;
  }

  .pagination-page:hover {
    border-color: #667eea;
    background: #f0f3ff;
    color: #667eea;
    transform: translateY(-2px);
  }

  .pagination-page.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
  }

  .pagination-ellipsis {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    color: #6c757d;
    font-weight: 600;
  }

  @media (max-width: 768px) {
    .pagination-section {
      flex-direction: column;
      text-align: center;
    }

    .pagination-controls {
      flex-wrap: wrap;
      justify-content: center;
    }
  }

  /* Mensaje vacío */
  .empty-state {
    padding: 60px 20px;
    text-align: center;
  }

  .empty-state-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
  }

  .empty-state h3 {
    color: #2c3e50;
    font-size: 18px;
    margin: 0 0 10px 0;
  }

  .empty-state p {
    color: #7f8c8d;
    margin: 0;
  }

  /* Barra flotante de acciones múltiples */
  .bulk-actions-bar {
    position: fixed;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%) translateY(200%);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 16px;
    border-radius: 12px 12px 0 0;
    box-shadow: 0 -6px 18px rgba(0,0,0,0.18);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 1000;
    transition: transform 0.28s cubic-bezier(.22,.9,.33,1);
    box-sizing: border-box;
    max-width: 100%;
    transform-origin: bottom center;
  }

  .bulk-actions-bar.visible {
    transform: translateX(-50%) translateY(0);
  }

  body.has-bulk-bar { padding-bottom: 96px; }

  .bulk-info {
    flex: 1 1 auto;
    min-width: 0;
  }

  .bulk-count {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 2px;
  }

  .bulk-subtitle {
    font-size: 12px;
    opacity: 0.95;
  }

  .bulk-actions {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .btn-bulk {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.18s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-bulk-delete {
    background: #dc3545;
    color: white;
  }

  .btn-bulk-delete:hover {
    background: #c82333;
    transform: translateY(-1px);
  }

  .btn-bulk-cancel {
    background: rgba(255,255,255,0.14);
    color: white;
    border: 1px solid rgba(255,255,255,0.22);
  }

  .btn-bulk-cancel:hover {
    background: rgba(255,255,255,0.18);
  }

  @media (max-width: 768px) {
    .bulk-actions-bar {
      left: 0;
      width: 100%;
      border-radius: 12px 12px 0 0;
      min-width: unset;
      padding: 12px 12px;
      transform: translateY(200%);
    }

    .bulk-actions-bar.visible { transform: translateY(0); }

    .bulk-actions { flex-direction: column; gap: 10px; width: 100%; }
    .bulk-actions .btn-bulk { width: 100%; justify-content: center; }
    .bulk-info { margin-bottom: 8px; }
  }

  /* Checkbox personalizado */
  .employee-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #667eea;
  }

  .select-all-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #667eea;
  }

  thead th:first-child {
    width: 50px;
    text-align: center;
  }

  tbody td:first-child {
    text-align: center;
  }

  .selection-mode tbody tr {
    cursor: pointer;
  }

  .selection-mode tbody tr:hover {
    background-color: #e7f3ff !important;
  }

  .selection-mode tbody tr.selected {
    background-color: #d0e9ff !important;
  }

  .selection-mode .employee-checkbox-cell {
    display: table-cell !important;
  }

  .employee-checkbox-cell {
    display: none;
  }

  .btn-select-mode {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border: none;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
  }

  .btn-select-mode:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }

  .btn-select-mode.active {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
  }

  @media (max-width: 768px) {
    tbody td { padding: 10px 12px; font-size: 13px; }
    thead th { padding: 10px 12px; font-size: 12px; }
    .badge { padding: 3px 8px; font-size: 11px; }
  }

  /* Header actions responsive */
  .header-actions { display: flex; gap: 10px; align-items: center; }

  @media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: stretch; padding: 16px; gap: 12px; }
    .header-actions { width: 100%; justify-content: space-between; }
    .header-actions .btn-select-mode,
    .header-actions .btn-new { min-width: 0; box-sizing: border-box; }
  }

  @media (max-width: 480px) {
    .header-actions { flex-direction: column; gap: 8px; }
    .header-actions .btn-select-mode,
    .header-actions .btn-new { width: 100%; display: inline-flex; justify-content: center; }
  }
</style>

<div class="employees-page">

  <!-- Header -->
  <div class="page-header">
    <div class="page-title-section">
      <h1><i class="fas fa-users"></i> Empleados</h1>
      <p class="page-subtitle">Administra la información de tu equipo de trabajo</p>
    </div>

    <div class="header-actions">
      <button type="button" class="btn-select-mode" id="btnToggleSelectMode">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
          <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.235.235 0 0 1 .02-.022z"/>
        </svg>
        Seleccionar
      </button>

      <a class="btn-new" href="{{ route('employees.create') }}">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
        </svg>
        Nuevo Empleado
      </a>
    </div>
  </div>

  <!-- Búsqueda y Filtros -->
  <div class="filters-section">
    <form method="get" action="{{ route('employees.index') }}">
      <div class="filters-grid">
        <!-- Búsqueda -->
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-search"></i> Buscar</label>
          <div class="search-input-wrapper">
            <svg class="search-icon" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
              <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
            </svg>
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Código de empleado o nombre...">
          </div>
        </div>

        <!-- Filtro por Credenciales -->
        <div class="filter-group">
          <label class="filter-label">🔐 Credenciales</label>
          <select name="cred" class="filter-select">
            <option value="">Todas</option>
            <option value="huella" {{ ($credFilter ?? '') === 'huella' ? 'selected' : '' }}>Con Huella</option>
            <option value="rostro" {{ ($credFilter ?? '') === 'rostro' ? 'selected' : '' }}>Con Rostro</option>
            <option value="tarjeta" {{ ($credFilter ?? '') === 'tarjeta' ? 'selected' : '' }}>Con Tarjeta</option>
            <option value="ninguna" {{ ($credFilter ?? '') === 'ninguna' ? 'selected' : '' }}>❌ Sin Credenciales</option>
          </select>
        </div>

        <!-- Ordenar por -->
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-sort"></i> Ordenar por</label>
          <select name="order" class="filter-select">
            <option value="id_desc" {{ ($orderBy ?? 'id_desc') === 'id_desc' ? 'selected' : '' }}>Más recientes</option>
            <option value="name_asc" {{ ($orderBy ?? '') === 'name_asc' ? 'selected' : '' }}>Nombre (A-Z)</option>
            <option value="name_desc" {{ ($orderBy ?? '') === 'name_desc' ? 'selected' : '' }}>Nombre (Z-A)</option>
            <option value="hire_asc" {{ ($orderBy ?? '') === 'hire_asc' ? 'selected' : '' }}>Contratación (Antigua)</option>
            <option value="hire_desc" {{ ($orderBy ?? '') === 'hire_desc' ? 'selected' : '' }}>Contratación (Reciente)</option>
          </select>
        </div>
      </div>

      <div class="filters-grid" style="margin-top: 15px;">
        <div style="grid-column: span 3; display: flex; gap: 10px;">
          <button type="submit" class="btn-search" style="flex: 0 0 auto;">
            Aplicar Filtros
          </button>
          @if(($q ?? '') || ($credFilter ?? '') || (($orderBy ?? 'id_desc') !== 'id_desc'))
            <a href="{{ route('employees.index') }}" class="btn-search" style="background: #6c757d; text-decoration: none; display: inline-flex; align-items: center;">
              Limpiar Filtros
            </a>
          @endif
        </div>
      </div>

      @if(($q ?? '') || ($credFilter ?? '') || (($orderBy ?? 'id_desc') !== 'id_desc'))
        <div class="active-filters">
          <span style="font-size: 12px; color: #6c757d; font-weight: 600;">FILTROS ACTIVOS:</span>

          @if($q ?? '')
            <span class="filter-tag">
              Búsqueda: "{{ $q }}"
              <a href="{{ route('employees.index', ['cred' => $credFilter ?? '', 'order' => $orderBy ?? '']) }}">✕</a>
            </span>
          @endif

          @if($credFilter ?? '')
            <span class="filter-tag">
              @if($credFilter === 'huella') Con Huella
              @elseif($credFilter === 'rostro') Con Rostro
              @elseif($credFilter === 'tarjeta') Con Tarjeta
              @elseif($credFilter === 'ninguna') ❌ Sin Credenciales
              @endif
              <a href="{{ route('employees.index', ['q' => $q ?? '', 'order' => $orderBy ?? '']) }}">✕</a>
            </span>
          @endif

          @if(($orderBy ?? 'id_desc') !== 'id_desc')
            <span class="filter-tag">
              Orden:
              @if($orderBy === 'name_asc') Nombre A-Z
              @elseif($orderBy === 'name_desc') Nombre Z-A
              @elseif($orderBy === 'hire_asc') Contratación Antigua
              @elseif($orderBy === 'hire_desc') Contratación Reciente
              @endif
              <a href="{{ route('employees.index', ['q' => $q ?? '', 'cred' => $credFilter ?? '']) }}">✕</a>
            </span>
          @endif
        </div>
      @endif
    </form>
  </div>

  <!-- Tabla de Datos -->
  <div class="data-table-section">
    <div class="table-header">
      <h2>Lista de Empleados</h2>
    </div>

    <form id="bulkDeleteForm" method="post" action="{{ route('employees.bulkDestroy') }}">
      @csrf

      <div class="table-responsive">
        <table id="employeesTable">
          <thead>
            <tr>
              <th class="employee-checkbox-cell">
                <input type="checkbox" class="select-all-checkbox" id="selectAll" title="Seleccionar todos">
              </th>
              <th>Employee No</th>
              <th>Nombre</th>
              <th>Contratación</th>
              <th>Credenciales</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($employees as $e)
              <tr data-employee-id="{{ $e->id }}">
                <td class="employee-checkbox-cell">
                  <input type="checkbox" class="employee-checkbox" name="employee_ids[]" value="{{ $e->id }}">
                </td>
                <td><span class="badge">{{ $e->employee_no }}</span></td>
                <td><span class="employee-name">{{ $e->name }}</span></td>
                <td>
                  <span class="hire-date">{{ $e->hire_date ? $e->hire_date->format('d/m/Y') : '—' }}</span>
                </td>
                <td>
                  @php
                    $hasFp = (bool) ($e->has_fp ?? false);
                    $hasFace = (bool) ($e->has_face ?? false);
                    $hasCard = (bool) ($e->has_card ?? false);
                  @endphp

                  <span class="cred-item" title="Huella">
                    <span class="cred-icon {{ $hasFp ? 'present' : '' }}"><i class="fas fa-fingerprint"></i></span>
                    <span class="cred-val">{{ $hasFp ? '1' : '0' }}</span>
                  </span>
                  <span class="cred-item" title="Rostro">
                    <span class="cred-icon {{ $hasFace ? 'present' : '' }}"><i class="fas fa-user"></i></span>
                    <span class="cred-val">{{ $hasFace ? '1' : '0' }}</span>
                  </span>
                  <span class="cred-item" title="Tarjeta">
                    <span class="cred-icon {{ $hasCard ? 'present' : '' }}"><i class="fas fa-id-card"></i></span>
                    <span class="cred-val">{{ $hasCard ? '1' : '0' }}</span>
                  </span>
                </td>
                <td class="actions">
                  <a href="{{ route('employees.edit',$e) }}">Editar</a>
                  <a href="{{ route('attendance.byEmployee', $e->employee_no) }}">Asistencia</a>
                  <button class="btn" onclick="event.preventDefault(); if(confirm('¿Seguro que deseas eliminar a {{ $e->name }}?')) { document.getElementById('delete-form-{{ $e->id }}').submit(); }">Eliminar</button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7">
                  <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No se encontraron empleados</h3>
                    <p>Intenta con otro término de búsqueda o agrega un nuevo empleado</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>

    {{-- Formularios individuales de eliminación (fuera del formulario bulk) --}}
    @foreach($employees as $e)
      <form id="delete-form-{{ $e->id }}" method="post" action="{{ route('employees.destroy', $e) }}" style="display:none">
        @csrf @method('DELETE')
      </form>
    @endforeach

    <div class="pagination-section">
      <div class="pagination-info">
        Mostrando
        <strong>{{ $employees->firstItem() ?? 0 }}</strong>
        a
        <strong>{{ $employees->lastItem() ?? 0 }}</strong>
        de
        <strong>{{ $employees->total() }}</strong>
        empleados
      </div>

      <div class="pagination-controls">
        {{-- Botón Anterior --}}
        @if ($employees->onFirstPage())
          <span class="pagination-btn disabled">
            <svg fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
            Anterior
          </span>
        @else
          <a href="{{ $employees->previousPageUrl() }}" class="pagination-btn">
            <svg fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
            </svg>
            Anterior
          </a>
        @endif

        {{-- Números de página --}}
        <div class="pagination-pages">
          @php
            $currentPage = $employees->currentPage();
            $lastPage = $employees->lastPage();
            $start = max(1, $currentPage - 2);
            $end = min($lastPage, $currentPage + 2);
          @endphp

          @if ($start > 1)
            <a href="{{ $employees->url(1) }}" class="pagination-page">1</a>
            @if ($start > 2)
              <span class="pagination-ellipsis">...</span>
            @endif
          @endif

          @for ($page = $start; $page <= $end; $page++)
            @if ($page == $currentPage)
              <span class="pagination-page active">{{ $page }}</span>
            @else
              <a href="{{ $employees->url($page) }}" class="pagination-page">{{ $page }}</a>
            @endif
          @endfor

          @if ($end < $lastPage)
            @if ($end < $lastPage - 1)
              <span class="pagination-ellipsis">...</span>
            @endif
            <a href="{{ $employees->url($lastPage) }}" class="pagination-page">{{ $lastPage }}</a>
          @endif
        </div>

        {{-- Botón Siguiente --}}
        @if ($employees->hasMorePages())
          <a href="{{ $employees->nextPageUrl() }}" class="pagination-btn">
            Siguiente
            <svg fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
            </svg>
          </a>
        @else
          <span class="pagination-btn disabled">
            Siguiente
            <svg fill="currentColor" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
            </svg>
          </span>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Barra flotante de acciones múltiples -->
<div class="bulk-actions-bar" id="bulkActionsBar">
  <div class="bulk-info">
    <div class="bulk-count"><span id="selectedCount">0</span> seleccionado(s)</div>
    <div class="bulk-subtitle">Empleados marcados para eliminar</div>
  </div>
  <div class="bulk-actions">
    <button type="button" class="btn-bulk btn-bulk-delete" id="btnBulkDelete">
      <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
      </svg>
      Eliminar Seleccionados
    </button>
    <button type="button" class="btn-bulk btn-bulk-cancel" id="btnBulkCancel">
      Cancelar
    </button>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const btnToggleSelectMode = document.getElementById('btnToggleSelectMode');
    const employeesTable = document.getElementById('employeesTable');
    const selectAllCheckbox = document.getElementById('selectAll');
    const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountSpan = document.getElementById('selectedCount');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const btnBulkCancel = document.getElementById('btnBulkCancel');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const tableRows = document.querySelectorAll('#employeesTable tbody tr[data-employee-id]');

    let selectionMode = false;

    // Alternar modo de selección
    btnToggleSelectMode.addEventListener('click', function() {
      selectionMode = !selectionMode;

      if (selectionMode) {
        employeesTable.classList.add('selection-mode');
        document.body.classList.add('selection-mode');
        btnToggleSelectMode.classList.add('active');
        btnToggleSelectMode.innerHTML = `
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
          </svg>
          Cancelar Selección
        `;
      } else {
        employeesTable.classList.remove('selection-mode');
        document.body.classList.remove('selection-mode');
        btnToggleSelectMode.classList.remove('active');
        btnToggleSelectMode.innerHTML = `
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
            <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.235.235 0 0 1 .02-.022z"/>
          </svg>
          Seleccionar
        `;

        // Limpiar selecciones
        employeeCheckboxes.forEach(checkbox => {
          checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
        tableRows.forEach(row => row.classList.remove('selected'));
        updateBulkActions();
      }
    });

    // Función para actualizar el contador y mostrar/ocultar barra
    function updateBulkActions() {
      const selectedCheckboxes = document.querySelectorAll('.employee-checkbox:checked');
      const count = selectedCheckboxes.length;

      selectedCountSpan.textContent = count;

      if (count > 0 && selectionMode) {
        bulkActionsBar.classList.add('visible');
        document.body.classList.add('has-bulk-bar');
      } else {
        bulkActionsBar.classList.remove('visible');
        document.body.classList.remove('has-bulk-bar');
      }
    }

    // Click en filas cuando está en modo selección
    function attachSelectable(element) {
      element.addEventListener('click', function(e) {
        if (!selectionMode) return;

        if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
          return;
        }

        const checkbox = this.querySelector('.employee-checkbox');
        if (!checkbox) return;
        checkbox.checked = !checkbox.checked;

        if (checkbox.checked) {
          this.classList.add('selected');
        } else {
          this.classList.remove('selected');
        }

        updateBulkActions();
        updateSelectAllState();
      });
    }

    tableRows.forEach(row => attachSelectable(row));

    // Actualizar estado del checkbox "seleccionar todos"
    function updateSelectAllState() {
      const allChecked = Array.from(employeeCheckboxes).every(cb => cb.checked);
      const someChecked = Array.from(employeeCheckboxes).some(cb => cb.checked);

      selectAllCheckbox.checked = allChecked;
      selectAllCheckbox.indeterminate = someChecked && !allChecked;
    }

    // Seleccionar/deseleccionar todos
    selectAllCheckbox.addEventListener('change', function() {
      const allCheckboxes = document.querySelectorAll('.employee-checkbox');
      allCheckboxes.forEach((checkbox) => {
        checkbox.checked = this.checked;
        const tr = checkbox.closest('tr');
        const card = checkbox.closest('.employee-card');
        if (tr) {
          if (this.checked) tr.classList.add('selected'); else tr.classList.remove('selected');
        }
        if (card) {
          if (this.checked) card.classList.add('selected'); else card.classList.remove('selected');
        }
      });
      updateBulkActions();
    });

    // Actualizar cuando se cambia checkbox directamente
    function attachCheckboxListener(checkbox) {
      checkbox.addEventListener('change', function() {
        const tr = checkbox.closest('tr');
        const card = checkbox.closest('.employee-card');
        if (tr) {
          if (checkbox.checked) tr.classList.add('selected'); else tr.classList.remove('selected');
        }
        if (card) {
          if (checkbox.checked) card.classList.add('selected'); else card.classList.remove('selected');
        }
        updateBulkActions();
        updateSelectAllState();
      });
    }

    document.querySelectorAll('.employee-checkbox').forEach(cb => attachCheckboxListener(cb));

    // Botón de eliminar
    btnBulkDelete.addEventListener('click', function() {
      const selectedCheckboxes = document.querySelectorAll('.employee-checkbox:checked');
      const count = selectedCheckboxes.length;

      if (count === 0) {
        alert('No hay empleados seleccionados');
        return;
      }

      const confirmMessage = `¿Estás seguro de que deseas eliminar ${count} empleado(s)?\n\nEsta acción eliminará los empleados tanto localmente como del dispositivo Hikvision.`;

      if (confirm(confirmMessage)) {
        bulkDeleteForm.submit();
      }
    });

    // Botón de cancelar en la barra flotante
    btnBulkCancel.addEventListener('click', function() {
      employeeCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
      });
      selectAllCheckbox.checked = false;
      selectAllCheckbox.indeterminate = false;
      tableRows.forEach(row => row.classList.remove('selected'));
      updateBulkActions();
    });
  });
</script>

{{-- Auto-enrolamiento si venimos de CREATE y la sync fue OK --}}
@if(session('fpStart') && session('fpStatus') && session('fpMark') && session('fpEmpId'))
  <div id="fpSessionData"
       data-start="{{ session('fpStart') }}"
       data-status="{{ session('fpStatus') }}"
       data-mark="{{ session('fpMark') }}"
       data-id="{{ session('fpEmpId') }}"
       data-name="{{ session('fpEmpName') }}">
  </div>

  <script>
    window.addEventListener('load', async () => {
      const el = document.getElementById('fpSessionData');
      if (!el) return;

      const startUrl  = el.dataset.start;
      const statusUrl = el.dataset.status;
      const markUrl   = el.dataset.mark;
      const empName   = el.dataset.name;
      const csrf      = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      try {
        const startRes = await fetch(startUrl, {
          method: 'POST',
          headers: {'X-CSRF-TOKEN': csrf}
        });
        const start = await startRes.json();
        if (!startRes.ok || !start.ok) {
          alert('No se pudo iniciar inscripción de ' + empName + '.\n' + (start.message || ''));
          return;
        }
        alert('Inscripción iniciada para ' + empName + '.\n' + (start.endpoint || ''));

        const t0 = Date.now(), TIMEOUT = 60000, INTERVAL = 2000;
        let ok = false, last = '';
        while (Date.now() - t0 < TIMEOUT) {
          const r = await fetch(statusUrl);
          const s = await r.json();
          if (s.ok) { ok = true; break; }
          last = (s.progress ?? '') + ' ' + (s.status ?? '');
          await new Promise(res => setTimeout(res, INTERVAL));
        }

        if (ok) {
          await fetch(markUrl, {method:'POST', headers:{'X-CSRF-TOKEN': csrf}});
          alert('Huella registrada correctamente.');
          location.reload();
        } else {
          alert('No hubo confirmación automática.\n'
            + 'Verifica en la pantalla del terminal.\n'
            + (last ? ('Último estado: ' + last) : '')
          );
        }
      } catch (e) {
        console.error(e);
        alert('Error de red: ' + e.message);
      }
    });
  </script>
@endif
@endsection
