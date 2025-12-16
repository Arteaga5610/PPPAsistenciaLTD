<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size:12px; color:#222 }
    .header { text-align:center; margin-bottom:12px }
    .header h2 { margin:0 }
    table { width:100%; border-collapse:collapse; margin-top:10px }
    th, td { border:1px solid #ddd; padding:6px; font-size:11px }
    th { background:#f5f5f5 }
    .summary { margin-top:12px }
  </style>
</head>
<body>
  <div class="header">
    <h2>{{ config('app.name') }} - Reporte de Asistencia</h2>
    <div>Empleado: {{ $employee->name }} ({{ $employee->employee_no }})</div>
    <div>Periodo: {{ $period }}</div>
  </div>

  <table>
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
      @forelse($records as $r)
        <tr>
          <td style="white-space:nowrap">{{ $r['date'] ?? '' }}</td>
          <td>{{ $r['day'] ?? '' }}</td>
          <td style="white-space:nowrap">{{ $r['scheduled'] ?? '' }}</td>
          <td>{{ $r['entry_mark'] ?? '' }}</td>
          <td>{{ $r['exit_mark'] ?? '' }}</td>
          <td>{{ $r['observation'] ?? '' }}</td>
          <td style="text-align:right">{{ isset($r['amount']) ? ('S/ ' . $r['amount']) : '' }}</td>
        </tr>
      @empty
        <tr><td colspan="7" style="text-align:center">No hay registros</td></tr>
      @endforelse
    </tbody>
  </table>

  <div class="summary">
    <strong>Resumen:</strong>
    <div>Total registros: {{ count($records) }}</div>
  </div>
</body>
</html>
