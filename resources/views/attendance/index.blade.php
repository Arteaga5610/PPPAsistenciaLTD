@extends('layouts.app')

@section('content')
  <h2>Asistencia del {{ $date }}</h2>

<table border="1" cellpadding="6">
  <thead>
    <tr>
      <th>Empleado</th>
      <th>employee_no</th>
      <th>Entrada</th>
      <th>Salida</th>
      <th>Ventana entrada</th>
      <th>Ventana salida</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $r)
      <tr>
        <td>{{ $r->employee->name ?? '—' }}</td>
        <td>{{ $r->employee_no }}</td>
        <td>{{ $r->entry_time ?? '—' }}</td>
        <td>{{ $r->exit_time ?? '—' }}</td>
        <td>
          {{ optional($r->entry_window_start)->format('H:i') ?? '—' }}
          —
          {{ optional($r->entry_window_end)->format('H:i') ?? '—' }}
        </td>
        <td>
          {{ optional($r->exit_window_start)->format('H:i') ?? '—' }}
          —
          {{ optional($r->exit_window_end)->format('H:i') ?? '—' }}
        </td>
      </tr>
    @empty
      <tr><td colspan="6">Sin registros</td></tr>
    @endforelse
  </tbody>
</table>

@endsection
