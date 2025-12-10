@extends('layouts.app')

@section('content')
<h2>Todos los Registros de Asistencia</h2>

@if($records->count() > 0)
<table border="1" cellpadding="6" cellspacing="0" style="width: 100%;">
    <thead>
        <tr>
            <th>Empleado (ID)</th>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Día</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Creado</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($records as $record)
        <tr>
            <td>{{ $record->employee_no }}</td>
            <td>
                @if($record->employee)
                    {{ $record->employee->name }}
                @else
                    —
                @endif
            </td>
            <td>{{ $record->date }}</td>
            <td>{{ \Carbon\Carbon::parse($record->date)->isoFormat('dddd') }}</td>
            <td>{{ $record->entry_time ? \Carbon\Carbon::parse($record->entry_time)->format('H:i:s') : '—' }}</td>
            <td>{{ $record->exit_time ? \Carbon\Carbon::parse($record->exit_time)->format('H:i:s') : '—' }}</td>
            <td>{{ $record->created_at->format('Y-m-d H:i:s') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<br>
{{ $records->links() }}
@else
<p><strong>❌ No hay registros de asistencia guardados.</strong></p>
<p>Esto significa que los eventos del dispositivo no están llegando a Laravel.</p>
@endif

<br>
<a href="{{ route('employees.index') }}">← Volver</a>
@endsection
