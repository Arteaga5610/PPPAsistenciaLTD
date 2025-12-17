@extends('layouts.app')

@section('content')
<h1>Asistencia: {{ $emp->name }} ({{ $emp->employee_no }})</h1>

<form class="mb-3" method="get">
  Desde <input type="date" name="from" value="{{ $from }}">
  Hasta <input type="date" name="to" value="{{ $to }}">
  <button class="btn btn-primary">Filtrar</button>
</form>

<div class="table-responsive">
  <table class="table table-bordered">
  <thead>
    <tr>
      <th>Fecha</th>
      <th>Horario</th>
      <th>Entrada</th>
      <th>Salida</th>
      <th>Estado</th>
    </tr>
  </thead>
  <tbody>
  @foreach($rows as $d)
    <tr>
      <td>{{ $d->date->format('Y-m-d') }}</td>
      <td>{{ optional($d->entry_scheduled)->format('H:i') }} → {{ optional($d->exit_scheduled)->format('H:i') }}</td>
      <td>{{ $d->entry_marked_at ? '✔ '.$d->entry_marked_at->format('H:i') : '✖' }}</td>
      <td>{{ $d->exit_marked_at ? '✔ '.$d->exit_marked_at->format('H:i') : '✖' }}</td>
      <td>{{ strtoupper($d->status) }}</td>
    </tr>
  @endforeach
  </tbody>
  </table>
</div>
@endsection
