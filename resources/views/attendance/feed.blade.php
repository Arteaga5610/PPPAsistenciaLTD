@extends('layouts.app')

@section('content')
  <h2>Feed de eventos (deduplicado {{ $bucket }}s)</h2>

  <form method="get" class="mb-3">
    <label>Desde:</label>
    <input type="text" name="since" value="{{ $since }}" placeholder="YYYY-MM-DD HH:MM:SS">
    <label style="margin-left:10px">Bucket (s):</label>
    <input type="number" name="bucket" value="{{ $bucket }}" min="1" style="width:80px">
    <button class="btn">Filtrar</button>
  </form>

  @php
    use App\Support\AttendanceLabels;
@endphp

<div class="table-responsive">
  <table>
  <thead>
    <tr>
      <th>Empleado</th>
      <th>Evento</th>
      <th>Hora del evento</th>
    </tr>
  </thead>
  <tbody>
  @foreach($rows as $r)
    <tr>
      <td>{{ $r->employee_name ?? $r->employee_no ?? '—' }}</td>
      <td>{{ AttendanceLabels::label($r->event_type ?? null, $r->method ?? null, $r->result ?? null) }}</td>
      <td>{{ \Carbon\Carbon::parse($r->event_time)->format('Y-m-d H:i:s') }}</td>
    </tr>
  @endforeach
  </tbody>
  </table>
</div>
@endsection
