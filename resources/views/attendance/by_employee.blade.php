@extends('layouts.app')

@section('content')
<style>
    .attendance-page {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .employee-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .employee-info-card .icon {
        font-size: 3rem;
    }

    .employee-info-card .details {
        flex: 1;
    }

    .employee-info-card .name {
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .employee-info-card .subtitle {
        opacity: 0.9;
        font-size: 1rem;
    }

    .attendance-table-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
    }

    .attendance-table thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .attendance-table thead th {
        padding: 1.25rem 1rem;
        text-align: left;
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }

    .attendance-table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s ease;
    }

    .attendance-table tbody tr:hover {
        background: #f8f9ff;
    }

    .attendance-table tbody td {
        padding: 1rem;
        color: #555;
        vertical-align: middle;
    }

    .date-cell {
        font-weight: 600;
        color: #2c3e50;
    }

    .day-cell {
        color: #7f8c8d;
        text-transform: capitalize;
    }

    .schedule-cell {
        font-family: 'Courier New', monospace;
        color: #34495e;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.asistio {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.falta {
        background: #f8d7da;
        color: #721c24;
    }

    .status-badge.tarde {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.temprano {
        background: #d1ecf1;
        color: #0c5460;
    }

    .time-mark {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.25rem;
        font-family: 'Courier New', monospace;
    }

    .observation-cell {
        font-size: 0.85rem;
        color: #6c757d;
        line-height: 1.4;
    }

    .observation-cell small {
        display: block;
        margin-top: 0.25rem;
        font-style: italic;
    }

    .amount-cell {
        font-weight: 600;
        color: #27ae60;
        font-size: 1rem;
    }

    .btn-toggle {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        cursor: pointer;
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        margin-top: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
    }

    .btn-toggle:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
    }

    .turnos-extra {
        display: none;
    }

    .turnos-extra td {
        background: #f8f9fa;
        padding: 2rem !important;
    }

    .turnos-extra-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .turno-card {
        background: white;
        padding: 1.25rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        border-left: 4px solid #667eea;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .turno-card:last-child {
        margin-bottom: 0;
    }

    .turno-header {
        font-weight: 600;
        color: #667eea;
        margin-bottom: 0.75rem;
        font-size: 1rem;
    }

    .turno-detail {
        display: grid;
        grid-template-columns: 100px 1fr;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .turno-detail-label {
        font-weight: 600;
        color: #555;
    }

    .turno-detail-value {
        color: #666;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: #667eea;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
        border: 2px solid #667eea;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        background: #667eea;
        color: white;
        transform: translateX(-4px);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
    }

    .empty-state .icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 1200px) {
        .attendance-table {
            font-size: 0.85rem;
        }
        
        .attendance-table thead th,
        .attendance-table tbody td {
            padding: 0.75rem 0.5rem;
        }
    }

    @media (max-width: 768px) {
        .attendance-page {
            padding: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .employee-info-card .name {
            font-size: 1.25rem;
        }

        .attendance-table-container {
            overflow-x: auto;
        }

        .attendance-table {
            min-width: 900px;
        }
    }
</style>

<div class="attendance-page">
    <div class="page-header">
        <h1 class="page-title">
            <span>📋</span>
            Historial de Asistencia
        </h1>
    </div>

    {{-- Controles: exportar y enviar reporte (solo si SMTP configurado y empleado tiene email) --}}
    <div style="margin-bottom: 1.5rem;">
        @if(!empty($employee->email) && !empty($smtpEnabled))
            <form method="post" action="{{ route('employees.send_report', $employee) }}" style="display: inline">
                @csrf
                <button class="back-link" type="submit" style="background: #27ae60; color: white; border: none;">📤 Exportar y enviar reporte (PDF)</button>
            </form>
        @elseif(empty($employee->email))
            <div class="empty-state" style="padding: 0.5rem; margin-bottom: 0.75rem;">
                <small>Este empleado no tiene un correo registrado. Agrega un email para permitir el envío de reportes.</small>
            </div>
        @else
            <div class="empty-state" style="padding: 0.5rem; margin-bottom: 0.75rem;">
                <small>SMTP no configurado. Configura las credenciales en Administración → Mail Settings.</small>
            </div>
        @endif
    </div>

    <div class="employee-info-card">
        <div class="icon">👤</div>
        <div class="details">
            <div class="name">{{ $employee->name }}</div>
            <div class="subtitle">Empleado N° {{ $employee->employee_no }}</div>
        </div>
    </div>

    <div class="attendance-table-container">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Día</th>
                    <th>Hora programada</th>
                    <th>Marca de entrada</th>
                    <th>Marca de salida</th>
                    <th>Observación</th>
                    <th>Monto del día (S/)</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                @php
                    // Cada $row ya viene con todos los turnos de ese día
                    $turnos     = $row['turnos'];
                    $first      = $turnos[0];              // primer turno del día
                    $extraCount = count($turnos) - 1;      // cuantos turnos adicionales hay
                    $dateKey    = $row['date']->format('Ymd');

                    // Monto del día y texto de descuento enviados por el controlador
                    $dayPayAmount = $row['pay_amount'] ?? null;
                    $dayPayInfo   = $row['pay_info']   ?? null;
                @endphp

                {{-- Fila principal: solo muestra el PRIMER turno --}}
                <tr>
                    <td class="date-cell">{{ $row['date']->format('d/m/Y') }}</td>
                    <td class="day-cell">{{ $row['day_name'] }}</td>

                    <td class="schedule-cell">
                        {{ $first['entry_scheduled'] }} – {{ $first['exit_scheduled'] }}

                        @if($extraCount > 0)
                            <br>
                            <button type="button"
                                    class="btn-toggle"
                                    data-target="turnos-{{ $dateKey }}">
                                + {{ $extraCount }} turno(s) más
                            </button>
                        @endif
                    </td>

                    {{-- Marca de entrada + hora --}}
                    <td>
                        @if(!empty($first['estado_entrada']))
                            {!! $first['estado_entrada'] !!}
                            @if(!empty($first['entry_mark']))
                                <span class="time-mark">({{ $first['entry_mark']->format('H:i') }})</span>
                            @endif
                        @else
                            <span style="color: #999;">—</span>
                        @endif
                    </td>

                    {{-- Marca de salida + hora --}}
                    <td>
                        @if(!empty($first['estado_salida']))
                            {!! $first['estado_salida'] !!}
                            @if(!empty($first['exit_mark']))
                                <span class="time-mark">({{ $first['exit_mark']->format('H:i') }})</span>
                            @endif
                        @else
                            <span style="color: #999;">—</span>
                        @endif
                    </td>

                    {{-- Observación: textos propios + info de descuento del día --}}
                    <td class="observation-cell">
                        {{ $first['obs_entrada'] ?? '' }}
                        {{ $first['obs_salida'] ?? '' }}

                        @if($dayPayInfo)
                            <small>{{ $dayPayInfo }}</small>
                        @endif
                    </td>

                    {{-- Solo el monto final del día --}}
                    <td class="amount-cell">
                        @if(!is_null($dayPayAmount))
                            S/ {{ number_format($dayPayAmount, 2) }}
                        @else
                            <span style="color: #999;">—</span>
                        @endif
                    </td>
                </tr>

                {{-- Fila desplegable con TODOS los turnos del día --}}
                @if($extraCount > 0)
                    <tr id="turnos-{{ $dateKey }}" class="turnos-extra">
                        <td colspan="7">
                            <div class="turnos-extra-title">
                                <span>📅</span>
                                Turnos del día {{ $row['date']->format('d/m/Y') }}
                            </div>

                            @foreach($turnos as $idx => $t)
                                <div class="turno-card">
                                    <div class="turno-header">🔹 Turno {{ $idx + 1 }}</div>
                                    
                                    <div class="turno-detail">
                                        <span class="turno-detail-label">Horario:</span>
                                        <span class="turno-detail-value">{{ $t['entry_scheduled'] }} – {{ $t['exit_scheduled'] }}</span>
                                    </div>

                                    <div class="turno-detail">
                                        <span class="turno-detail-label">Entrada:</span>
                                        <span class="turno-detail-value">
                                            {!! $t['estado_entrada'] ?? '<span style="color: #999;">—</span>' !!}
                                            @if(!empty($t['entry_mark']))
                                                <span class="time-mark" style="display: inline; margin-left: 0.5rem;">({{ $t['entry_mark']->format('H:i') }})</span>
                                            @endif
                                        </span>
                                    </div>

                                    <div class="turno-detail">
                                        <span class="turno-detail-label">Salida:</span>
                                        <span class="turno-detail-value">
                                            {!! $t['estado_salida'] ?? '<span style="color: #999;">—</span>' !!}
                                            @if(!empty($t['exit_mark']))
                                                <span class="time-mark" style="display: inline; margin-left: 0.5rem;">({{ $t['exit_mark']->format('H:i') }})</span>
                                            @endif
                                        </span>
                                    </div>

                                    @if(!empty($t['obs_entrada']) || !empty($t['obs_salida']))
                                        <div class="turno-detail">
                                            <span class="turno-detail-label">Observación:</span>
                                            <span class="turno-detail-value">
                                                {{ $t['obs_entrada'] ?? '' }}
                                                {{ $t['obs_salida'] ?? '' }}
                                            </span>
                                        </div>
                                    @endif

                                    @if(!empty($t['pay_info_turno'] ?? null))
                                        <div class="turno-detail">
                                            <span class="turno-detail-label">Info:</span>
                                            <span class="turno-detail-value">
                                                <small>{{ $t['pay_info_turno'] }}</small>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="icon">📭</div>
                            <p>No hay registros de asistencia para este empleado.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('employees.index') }}" class="back-link">
        <span>←</span>
        Volver
    </a>
</div>

{{-- Script para abrir/cerrar el desplegable --}}
<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".btn-toggle").forEach(btn => {
            btn.addEventListener("click", () => {
                const id  = btn.dataset.target;
                const row = document.getElementById(id);
                if (!row) return;

                const isHidden = (row.style.display === "" || row.style.display === "none");

                if (isHidden) {
                    row.style.display = "table-row";
                    btn.textContent = btn.textContent
                        .replace("+","−")
                        .replace("más", "ocultar");
                } else {
                    row.style.display = "none";
                    btn.textContent = btn.textContent
                        .replace("−","+")
                        .replace("ocultar","más");
                }
            });
        });
    });
</script>
@endsection
