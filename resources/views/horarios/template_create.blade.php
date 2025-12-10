@extends('layouts.app')

@section('content')
<style>
  .template-create-page {
    max-width: 900px;
    margin: 0 auto;
  }

  .page-header {
    margin-bottom: 2rem;
  }

  .page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
  }

  .page-subtitle {
    color: #7f8c8d;
    font-size: 0.95rem;
  }

  .form-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 2rem;
  }

  .form-section {
    margin-bottom: 2rem;
  }

  .form-section:last-child {
    margin-bottom: 0;
  }

  .section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #ecf0f1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  .form-group label {
    display: block;
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
  }

  .form-group input[type="text"],
  .form-group input[type="time"] {
    width: 100%;
    padding: 0.65rem 1rem;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
  }

  .form-group input:focus {
    outline: none;
    border-color: #3498db;
    background-color: white;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
  }

  .form-group small {
    display: block;
    margin-top: 0.4rem;
    color: #95a5a6;
    font-size: 0.85rem;
  }

  .days-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.75rem;
    margin-top: 0.75rem;
  }

  .day-checkbox {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .day-checkbox:hover {
    background: #e8f4f8;
    border-color: #3498db;
  }

  .day-checkbox input[type="checkbox"] {
    margin-right: 0.6rem;
    cursor: pointer;
    width: 18px;
    height: 18px;
  }

  .day-checkbox label {
    margin: 0;
    cursor: pointer;
    flex: 1;
    font-weight: 500;
    color: #34495e;
  }

  .form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #ecf0f1;
  }

  .btn-submit {
    flex: 1;
    padding: 0.85rem 2rem;
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(39, 174, 96, 0.2);
  }

  .btn-submit:hover {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
    box-shadow: 0 6px 12px rgba(39, 174, 96, 0.3);
    transform: translateY(-2px);
  }
</style>

<div class="template-create-page">
  <div class="page-header">
    <h1>Crear Plantilla de Horario</h1>
    <p class="page-subtitle">Define una plantilla reutilizable para asignar rápidamente a varios empleados</p>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger" style="background: #fee; border-left: 4px solid #e74c3c; padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 2rem;">
      <strong>⚠️ Errores encontrados:</strong>
      <ul style="margin: 0.5rem 0 0 1.5rem;">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if (session('status'))
    <div class="alert alert-success" style="background: #d4edda; border-left: 4px solid #27ae60; padding: 1rem 1.25rem; border-radius: 8px; margin-bottom: 2rem; color: #155724;">
      <strong>✓ {{ session('status') }}</strong>
    </div>
  @endif

  <form method="post" action="{{ route('horarios.storeTemplate') }}" class="form-card">
    @csrf

    <div class="form-section">
      <div class="section-title"><i class="fas fa-clipboard-list"></i> Información de la Plantilla</div>
      
      <div class="form-group">
        <label for="template_name">Nombre de la Plantilla</label>
        <input type="text" id="template_name" name="name"
               value="{{ old('name') }}" 
               placeholder="Ej: Horario Administrativo, Turno Mañana, etc."
               required>
      </div>
    </div>

    <div class="form-section">
      <div class="section-title">🌅 Horario de Entrada</div>
      
      <div class="form-group">
        <label for="entry_time">Hora de Entrada</label>
        <input type="time" id="entry_time" name="entry_time"
               value="{{ old('entry_time') }}" required>
        <small>Tolerancia fija: 15 min antes / 15 min después.</small>
      </div>

      <input type="hidden" name="entry_minus" value="15">
      <input type="hidden" name="entry_plus" value="15">
    </div>

    <div class="form-section">
      <div class="section-title">🌆 Horario de Salida</div>
      
      <div class="form-group">
        <label for="exit_time">Hora de Salida</label>
        <input type="time" id="exit_time" name="exit_time"
               value="{{ old('exit_time') }}" required>
        <small>Tolerancia fija: 10 min antes / 10 min después.</small>
      </div>

      <input type="hidden" name="exit_minus" value="10">
      <input type="hidden" name="exit_plus" value="10">
    </div>

    <div class="form-section">
      <div class="section-title"><i class="fas fa-calendar-check"></i> Días Laborables de la Plantilla</div>
      
      @php
        $days = old('work_days', [1,2,3,4,5]); // por defecto L–V
      @endphp

      <div class="days-grid">
        <div class="day-checkbox">
          <input type="checkbox" id="day1" name="work_days[]" value="1" {{ in_array(1,$days) ? 'checked' : '' }}>
          <label for="day1">Lunes</label>
        </div>
        <div class="day-checkbox">
          <input type="checkbox" id="day2" name="work_days[]" value="2" {{ in_array(2,$days) ? 'checked' : '' }}>
          <label for="day2">Martes</label>
        </div>
        <div class="day-checkbox">
          <input type="checkbox" id="day3" name="work_days[]" value="3" {{ in_array(3,$days) ? 'checked' : '' }}>
          <label for="day3">Miércoles</label>
        </div>
        <div class="day-checkbox">
          <input type="checkbox" id="day4" name="work_days[]" value="4" {{ in_array(4,$days) ? 'checked' : '' }}>
          <label for="day4">Jueves</label>
        </div>
        <div class="day-checkbox">
          <input type="checkbox" id="day5" name="work_days[]" value="5" {{ in_array(5,$days) ? 'checked' : '' }}>
          <label for="day5">Viernes</label>
        </div>
        <div class="day-checkbox">
          <input type="checkbox" id="day6" name="work_days[]" value="6" {{ in_array(6,$days) ? 'checked' : '' }}>
          <label for="day6">Sábado</label>
        </div>
        <div class="day-checkbox">
          <input type="checkbox" id="day7" name="work_days[]" value="7" {{ in_array(7,$days) ? 'checked' : '' }}>
          <label for="day7">Domingo</label>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-submit">Guardar Plantilla</button>
    </div>
  </form>
</div>
@endsection
