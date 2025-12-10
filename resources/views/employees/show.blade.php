@extends('layouts.app')
@section('content')
  <h2>Detalle Employee</h2>
  <p><strong>Employee No:</strong> {{ $employee->employee_no }}</p>
  <p><strong>Nombre:</strong> {{ $employee->name }}</p>
  <p><strong>Válido:</strong>
    {{ $employee->valid_from ? $employee->valid_from->format('Y-m-d H:i') : '-' }} —
    {{ $employee->valid_to ? $employee->valid_to->format('Y-m-d H:i') : '-' }}
  </p>
  <p><a class="btn" href="{{ route('employees.index') }}">Volver</a></p>
@endsection

<div id="fpData"
     data-start="{{ route('fp.start',$employee) }}"
     data-status="{{ route('fp.status',$employee) }}"
     data-mark="{{ route('fp.mark',$employee) }}"
     data-id="{{ $employee->id }}"></div>

<script>
  const el = document.getElementById('fpData');
  const startUrl = el.dataset.start;
  const statusUrl= el.dataset.status;
  const markUrl  = el.dataset.mark;
  const empId    = el.dataset.id;

  enrollFp(startUrl, statusUrl, markUrl, empId);
</script>
