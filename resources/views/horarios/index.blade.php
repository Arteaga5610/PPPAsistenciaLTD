@extends('layouts.app')

@section('content')
<style>
  .schedules-page {
    padding: 8px; /* tighter gutters to match global framed look */
  }

  .page-header {
    background: white;
    padding: 12px 14px; /* reduce inner padding so content sits closer to sidebar */
    border-radius: 8px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.06);
    margin-bottom: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
  }
  
  .page-title-section h1 {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .page-subtitle {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
  }
  
  .btn-new {
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
  }
  
  .btn-new:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
  }
  
  .data-table-section {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
  }
  
  .table-header {
    padding: 12px 14px;
    border-bottom: 1px solid #e9ecef;
  }
  
  .table-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
  }
  
  thead th {
    background: #f8f9fa;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #495057;
    padding: 10px 12px;
    text-align: left;
    border-bottom: 2px solid #e9ecef;
  }
  
  tbody tr {
    transition: background-color 0.15s ease;
    border-bottom: 1px solid #f1f3f5;
  }
  
  tbody tr:hover {
    background-color: #f8f9fa;
  }
  
  tbody td {
    padding: 10px 12px;
    font-size: 14px;
    color: #2c3e50;
  }
  
  tbody tr:last-child {
    border-bottom: none;
  }
  
  .employee-name {
    font-weight: 600;
    color: #2c3e50;
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
  
  .time-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f0f3ff;
    color: #667eea;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
  }
  
  .days-list {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
  }
  
  .day-chip {
    background: #e8f5e9;
    color: #2e7d32;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
  }
  
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
  }
  
  td.actions a:first-child {
    background: #fff3cd;
    color: #856404;
  }
  
  td.actions a:first-child:hover {
    background: #ffc107;
    color: #fff;
  }
  
  td.actions .btn-delete {
    background: #f8d7da;
    color: #721c24;
  }
  
  td.actions .btn-delete:hover {
    background: #dc3545;
    color: #fff;
  }
  
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

  /* Additional responsive tweaks: make page narrower gaps on small screens */
  @media (max-width: 640px) {
    .schedules-page { padding: 6px; }
    .page-header { padding: 10px 12px; }
    .page-title-section h1 { font-size: 18px; }
    .page-subtitle { font-size: 13px; }
    .btn-new { padding: 10px 14px; }
    .table-header { padding: 10px 12px; }
    thead th { font-size: 11px; padding: 8px 10px; }
    tbody td { padding: 8px 10px; font-size: 13px; }
  }
</style>

<div class="schedules-page">
  
  <!-- Header -->
  <div class="page-header">
    <div class="page-title-section">
      <h1>
        <svg width="28" height="28" fill="currentColor" viewBox="0 0 16 16" style="color: #667eea;">
          <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
        </svg>
        Horarios de Trabajo
      </h1>
      <p class="page-subtitle">Gestiona los horarios asignados a cada empleado</p>
    </div>
    
    <a class="btn-new" href="{{ route('horarios.create') }}">
      <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
      </svg>
      Nuevo Horario
    </a>
  </div>

  <!-- Tabla de Datos -->
  <div class="data-table-section">
    <div class="table-header">
      <h2>Lista de Horarios Asignados</h2>
    </div>
    
    <div class="table-responsive">
      <table>
      <thead>
        <tr>
          <th>Empleado</th>
          <th>Employee No</th>
          <th>Horario</th>
          <th>Días Laborales</th>
          <th>Plantilla</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      @forelse($schedules as $schedule)
        <tr>
          <td>
            <span class="employee-name">{{ $schedule->employee->name ?? 'N/A' }}</span>
          </td>
          <td>
            <span class="badge">{{ $schedule->employee_no }}</span>
          </td>
          <td>
            <span class="time-badge">
              <i class="fas fa-sign-in-alt"></i> {{ $schedule->entry_time ?? '--:--' }}
            </span>
            <span class="time-badge">
              <i class="fas fa-sign-out-alt"></i> {{ $schedule->exit_time ?? '--:--' }}
            </span>
          </td>
          <td>
            @if($schedule->work_days)
              <div class="days-list">
                @foreach($schedule->work_days as $day)
                  <span class="day-chip">
                    @switch($day)
                      @case('monday') Lun @break
                      @case('tuesday') Mar @break
                      @case('wednesday') Mié @break
                      @case('thursday') Jue @break
                      @case('friday') Vie @break
                      @case('saturday') Sáb @break
                      @case('sunday') Dom @break
                      @case(1) Lun @break
                      @case(2) Mar @break
                      @case(3) Mié @break
                      @case(4) Jue @break
                      @case(5) Vie @break
                      @case(6) Sáb @break
                      @case(7) Dom @break
                      @default {{ $day }}
                    @endswitch
                  </span>
                @endforeach
              </div>
            @else
              <span style="color: #999;">Sin días definidos</span>
            @endif
          </td>
          <td>
            @if($schedule->template)
              <span style="color: #667eea; font-weight: 600;">
                <i class="fas fa-clipboard-list"></i> {{ $schedule->template->name }}
              </span>
            @else
              <span style="color: #999;">Personalizado</span>
            @endif
          </td>
          <td class="actions">
            <a href="{{ route('horarios.edit', $schedule) }}">Editar</a>
            <form method="post" action="{{ route('horarios.destroy', $schedule) }}" style="display:inline">
              @csrf @method('DELETE')
              <button class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este horario?')">Eliminar</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <div class="empty-state-icon"><i class="fas fa-calendar-alt"></i></div>
              <h3>No hay horarios registrados</h3>
              <p>Crea un nuevo horario para asignar a tus empleados</p>
            </div>
          </td>
        </tr>
      @endforelse
      </tbody>
      </table>
    </div>
    
    @if($schedules->hasPages())
      <div class="pagination-section">
        <div class="pagination-info">
          Mostrando 
          <strong>{{ $schedules->firstItem() ?? 0 }}</strong>
          a 
          <strong>{{ $schedules->lastItem() ?? 0 }}</strong>
          de 
          <strong>{{ $schedules->total() }}</strong>
          resultados
        </div>
        
        <div class="pagination-controls">
          {{-- Botón Anterior --}}
          @if ($schedules->onFirstPage())
            <span class="pagination-btn disabled">
              <svg fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
              </svg>
              Anterior
            </span>
          @else
            <a href="{{ $schedules->previousPageUrl() }}" class="pagination-btn">
              <svg fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
              </svg>
              Anterior
            </a>
          @endif

          {{-- Números de página --}}
          <div class="pagination-pages">
            @php
              $currentPage = $schedules->currentPage();
              $lastPage = $schedules->lastPage();
              $start = max(1, $currentPage - 2);
              $end = min($lastPage, $currentPage + 2);
            @endphp

            {{-- Primera página --}}
            @if ($start > 1)
              <a href="{{ $schedules->url(1) }}" class="pagination-page">1</a>
              @if ($start > 2)
                <span class="pagination-ellipsis">...</span>
              @endif
            @endif

            {{-- Páginas del rango --}}
            @for ($page = $start; $page <= $end; $page++)
              @if ($page == $currentPage)
                <span class="pagination-page active">{{ $page }}</span>
              @else
                <a href="{{ $schedules->url($page) }}" class="pagination-page">{{ $page }}</a>
              @endif
            @endfor

            {{-- Última página --}}
            @if ($end < $lastPage)
              @if ($end < $lastPage - 1)
                <span class="pagination-ellipsis">...</span>
              @endif
              <a href="{{ $schedules->url($lastPage) }}" class="pagination-page">{{ $lastPage }}</a>
            @endif
          </div>

          {{-- Botón Siguiente --}}
          @if ($schedules->hasMorePages())
            <a href="{{ $schedules->nextPageUrl() }}" class="pagination-btn">
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
    @endif
    
  </div>

</div>
@endsection
