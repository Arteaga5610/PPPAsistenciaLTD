@extends('layouts.app')

@section('content')
    <h2>Asignar horario a empleado</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;padding-left:20px;">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul> 
        </div>
    @endif

    <form method="post" action="{{ route('schedules.store') }}">
        @csrf

        <div class="mb-3">
            <label>Employee No</label><br>
                 <input type="text" name="employee_no"
                     value="{{ old('employee_no') }}">
        </div>

        <hr>

        <h3>Entrada</h3>
        <div class="mb-2">
            <label>Hora de entrada</label><br>
            <input type="time" name="entry_time"
                   value="{{ old('entry_time') }}">
        </div>
        <div class="mb-2">
            <label>Tolerancia entrada (min antes)</label><br>
            <input type="number" name="entry_minus" min="0"
                   value="{{ old('entry_minus', 15) }}">
        </div>
        <div class="mb-3">
            <label>Tolerancia entrada (min después)</label><br>
            <input type="number" name="entry_plus" min="0"
                   value="{{ old('entry_plus', 15) }}">
        </div>

        <hr>

        <h3>Salida</h3>
        <div class="mb-2">
            <label>Hora de salida</label><br>
            <input type="time" name="exit_time"
                   value="{{ old('exit_time') }}">
        </div>
        <div class="mb-2">
            <label>Tolerancia salida (min antes)</label><br>
            <input type="number" name="exit_minus" min="0"
                   value="{{ old('exit_minus', 10) }}">
        </div>
        <div class="mb-3">
            <label>Tolerancia salida (min después)</label><br>
            <input type="number" name="exit_plus" min="0"
                   value="{{ old('exit_plus', 10) }}">
        </div>

        <button type="submit" class="btn btn-primary">Guardar horario</button>
        <a href="{{ route('employees.index') }}" class="btn">Cancelar</a>
    </form>
@endsection
