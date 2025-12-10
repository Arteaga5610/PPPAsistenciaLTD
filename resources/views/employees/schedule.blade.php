@extends('layouts.app')

@section('content')
    <h2>Horario de {{ $employee->name }} ({{ $employee->employee_no }})</h2>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    <form method="post" action="{{ route('employees.schedule.store', $employee) }}">
        @csrf

        <h3 class="mt-3">Entrada</h3>
        <div class="mb-2">
            <label>Hora de entrada</label><br>
            <input type="time" name="entry_time"
                   value="{{ old('entry_time', $schedule->entry_time ?? '') }}">
            @error('entry_time') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-2">
            <label>Tolerancia entrada (min antes)</label><br>
            <input type="number" name="entry_minus" min="0"
                   value="{{ old('entry_minus', $schedule->entry_minus ?? 15) }}">
            @error('entry_minus') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label>Tolerancia entrada (min después)</label><br>
            <input type="number" name="entry_plus" min="0"
                   value="{{ old('entry_plus', $schedule->entry_plus ?? 15) }}">
            @error('entry_plus') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <hr>

        <h3>Salida</h3>
        <div class="mb-2">
            <label>Hora de salida</label><br>
            <input type="time" name="exit_time"
                   value="{{ old('exit_time', $schedule->exit_time ?? '') }}">
            @error('exit_time') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-2">
            <label>Tolerancia salida (min antes)</label><br>
            <input type="number" name="exit_minus" min="0"
                   value="{{ old('exit_minus', $schedule->exit_minus ?? 10) }}">
            @error('exit_minus') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label>Tolerancia salida (min después)</label><br>
            <input type="number" name="exit_plus" min="0"
                   value="{{ old('exit_plus', $schedule->exit_plus ?? 10) }}">
            @error('exit_plus') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Guardar horario</button>
        <a href="{{ route('employees.index') }}" class="btn">Cancelar</a>
    </form>
@endsection
