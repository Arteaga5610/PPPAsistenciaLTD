@extends('layouts.app')

@section('content')
<style>
  .turnos-page {
    padding: 30px;
  }
  
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
  }
  
  .page-title {
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .page-title h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
  }
  
  .page-title .icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
  }
  
  .back-button {
    padding: 10px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    color: #495057;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  
  .back-button:hover {
    border-color: #667eea;
    color: #667eea;
    transform: translateX(-4px);
  }
  
  .stats-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .stats-summary .main-stat {
    font-size: 48px;
    font-weight: 700;
    line-height: 1;
  }
  
  .stats-summary .stat-label {
    font-size: 16px;
    opacity: 0.95;
    margin-top: 8px;
  }
  
  .stats-summary .date-info {
    text-align: right;
    opacity: 0.9;
  }
  
  .stats-summary .date-info .day {
    font-size: 18px;
    font-weight: 600;
    text-transform: capitalize;
  }
  
  .stats-summary .date-info .full-date {
    font-size: 14px;
    margin-top: 4px;
  }
  
  .table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
  }
  
  .table-container {
    overflow-x: auto;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
  }
  
  thead tr {
    background: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
  }
  
  th {
    padding: 15px 20px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  
  th.center {
    text-align: center;
  }
  
  tbody tr {
    border-bottom: 1px solid #f1f3f5;
    transition: background-color 0.2s ease;
  }
  
  tbody tr:hover {
    background-color: #f8f9fa;
  }
  
  td {
    padding: 18px 20px;
  }
  
  .employee-cell {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .employee-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
  }
  
  .employee-info .name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
    margin-bottom: 4px;
  }
  
  .employee-info .code {
    font-size: 11px;
    color: #7f8c8d;
    font-family: 'Courier New', monospace;
  }
  
  .schedule-time {
    font-family: 'Courier New', monospace;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
  }
  
  .mark-info {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
  }
  
  .mark-info .time {
    font-size: 12px;
    color: #6c757d;
    font-family: 'Courier New', monospace;
  }
  
  .status-badge {
    padding: 6px 14px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }
  
  .status-badge.complete {
    background: #d4edda;
    color: #155724;
  }
  
  .status-badge.observations {
    background: #fff3cd;
    color: #856404;
  }
  
  .status-badge.incomplete {
    background: #fff3cd;
    color: #856404;
  }
  
  .status-badge.pending {
    background: #f8d7da;
    color: #721c24;
  }
  
  .empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #7f8c8d;
  }
  
  .empty-state i {
    font-size: 64px;
    opacity: 0.2;
    margin-bottom: 15px;
  }
  
  .empty-state p {
    margin: 0;
    font-size: 16px;
  }
</style>

<div class="turnos-page">
  
  <!-- Cabecera de la página -->
  <div class="page-header">
    <div class="page-title">
      <div class="icon">
        <i class="fas fa-calendar-check"></i>
      </div>
      <h1>Asistencias de Hoy</h1>
    </div>
    <a href="{{ route('dashboard') }}" class="back-button">
      <i class="fas fa-arrow-left"></i>
      Volver
    </a>
  </div>
  
  <!-- Resumen de estadísticas -->
  <div class="stats-summary">
    <div>
      <div class="main-stat">{{ $scheduleCount['with_marks'] }}/{{ $scheduleCount['total'] }}</div>
      <div class="stat-label">Empleados han marcado su horario el día de hoy</div>
    </div>
    <div class="date-info">
      <div class="day">{{ now('America/Lima')->locale('es')->isoFormat('dddd') }}</div>
      <div class="full-date">{{ now('America/Lima')->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</div>
    </div>
  </div>
  
  <!-- Tabla de turnos -->
  <div class="table-card">
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Horario</th>
            <th class="center">Entrada</th>
            <th class="center">Salida</th>
            <th class="center">Estado</th>
          </tr>
        </thead>
        <tbody>
          @forelse($todaySchedules as $item)
            <tr>
              <td>
                <div class="employee-cell">
                  <div class="employee-avatar">
                    {{ strtoupper(substr($item['employee']->name, 0, 1)) }}
                  </div>
                  <div class="employee-info">
                    <div class="name">{{ $item['employee']->name }}</div>
                    <div class="code">{{ $item['employee']->employee_no }}</div>
                  </div>
                </div>
              </td>
              <td>
                <div class="schedule-time">
                  {{ \Carbon\Carbon::parse($item['schedule']->entry_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item['schedule']->exit_time)->format('H:i') }}
                </div>
              </td>
              <td style="text-align: center;">
                @if($item['entry_status'])
                  <div class="mark-info">
                    <span>{!! $item['entry_status'] !!}</span>
                    @if($item['entry_time'])
                      <span class="time">({{ $item['entry_time'] }})</span>
                    @endif
                  </div>
                @else
                  <span style="color: #adb5bd;">—</span>
                @endif
              </td>
              <td style="text-align: center;">
                @if($item['exit_status'])
                  <div class="mark-info">
                    <span>{!! $item['exit_status'] !!}</span>
                    @if($item['exit_time'])
                      <span class="time">({{ $item['exit_time'] }})</span>
                    @endif
                  </div>
                @else
                  <span style="color: #adb5bd;">—</span>
                @endif
              </td>
              <td style="text-align: center;">
                @php
                  $hasEntry = $item['entry_mark'] instanceof \Carbon\Carbon;
                  $hasExit = $item['exit_mark'] instanceof \Carbon\Carbon;
                  $isComplete = $hasEntry && $hasExit;
                  $isPunctual = $item['entry_status'] === '✅ Asistió' && $item['exit_status'] === '✅ Asistió';
                @endphp
                
                @if($isComplete && $isPunctual)
                  <span class="status-badge complete">
                    <i class="fas fa-check-circle"></i> Completo
                  </span>
                @elseif($isComplete)
                  <span class="status-badge observations">
                    <i class="fas fa-exclamation-triangle"></i> Con observaciones
                  </span>
                @elseif($hasEntry || $hasExit)
                  <span class="status-badge incomplete">
                    <i class="fas fa-clock"></i> Incompleto
                  </span>
                @else
                  <span class="status-badge pending">
                    <i class="fas fa-times-circle"></i> Sin marcar
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <i class="fas fa-calendar-times"></i>
                  <p>No hay turnos programados para hoy</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
