{{-- resources/views/horarios/create.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
  .create-schedule-page {
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
  .form-group input[type="time"],
  .form-group select {
    width: 100%;
    padding: 0.65rem 1rem;
    border: 2px solid #e0e6ed;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
  }

  /* Limit native select visible width on larger screens; full-width on phones */
  .form-group select { max-width: 420px; }

  @media (max-width: 640px) {
    .form-group select { max-width: 100%; font-size: 15px; padding: 0.9rem 1rem; }
  }
  
  /* Custom select replacement to avoid oversized native dropdowns */
  .custom-select-wrapper { position: relative; max-width: 420px; }
  .custom-select { display:flex; align-items:center; gap:10px; justify-content:space-between; border:2px solid #e0e6ed; background:#f8f9fa; padding:0.65rem 1rem; border-radius:8px; cursor:pointer; }
  .custom-select-display { flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#2c3e50 }
  .custom-select-caret { margin-left:8px; color:#667eea }
  .custom-select-list { position:absolute; left:0; right:0; top:calc(100% + 8px); background:white; border:1px solid #e6edf3; border-radius:8px; box-shadow:0 6px 18px rgba(18,38,63,0.06); max-height:220px; overflow:auto; z-index:2000; display:none }
  .custom-select-list.open { display:block }
  .custom-select-item { padding:10px 12px; cursor:pointer; white-space:normal; color:#34495e }
  .custom-select-item:hover { background:#f0f3ff }
  @media (max-width:640px) {
    .custom-select-wrapper { max-width: 100%; }
    .custom-select-list { max-height:260px }
  }

  .form-group input:focus,
  .form-group select:focus {
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
    background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(155, 89, 182, 0.2);
  }

  .btn-submit:hover {
    background: linear-gradient(135deg, #8e44ad 0%, #7d3c98 100%);
    box-shadow: 0 6px 12px rgba(155, 89, 182, 0.3);
    transform: translateY(-2px);
  }

  .btn-cancel {
    padding: 0.85rem 2rem;
    background: #ecf0f1;
    color: #2c3e50;
    border: 2px solid #bdc3c7;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
  }

  .btn-cancel:hover {
    background: #bdc3c7;
    border-color: #95a5a6;
  }
</style>

<div class="create-schedule-page">
  <div class="page-header">
    <h1>Registrar Horario de Empleado</h1>
    <p class="page-subtitle">Asigna un horario de trabajo a un empleado</p>
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

  <form method="post" action="{{ route('horarios.store') }}" class="form-card">
    @csrf

    <div class="form-section">
      <div class="section-title"><i class="fas fa-user"></i> Información del Empleado</div>
      
      <div class="form-group">
        <label for="employee_no">Código de Empleado</label>
        <input type="text" id="employee_no" name="employee_no"
               value="{{ old('employee_no') }}" 
               placeholder="Ej: EMP001"
               required>
      </div>

      @isset($templates)
      <div class="form-group">
        <label for="template_id">Plantilla de Horario (opcional)</label>
        <select id="template_id" name="template_id">
          <option value="">Sin plantilla / personalizado</option>
          @foreach($templates as $tpl)
            <option value="{{ $tpl->id }}"
                {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                {{ $tpl->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endisset
    </div>

    <div class="form-section">
      <div class="section-title">🌅 Horario de Entrada</div>
      
      <div class="form-group">
        <label for="entry_time">Hora de Entrada</label>
        <input type="time" id="entry_time" name="entry_time"
               value="{{ old('entry_time') }}">
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
               value="{{ old('exit_time') }}">
        <small>Tolerancia fija: 10 min antes / 10 min después.</small>
      </div>

      <input type="hidden" name="exit_minus" value="10">
      <input type="hidden" name="exit_plus" value="10">
    </div>

    <div class="form-section">
      <div class="section-title"><i class="fas fa-calendar-check"></i> Días Laborables</div>
      
      @php
        $days = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
            4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
        ];
        $oldDays = (array) old('work_days', []);
      @endphp

      <div class="days-grid">
        @foreach($days as $num => $name)
        <div class="day-checkbox">
          <input type="checkbox" id="day{{ $num }}" name="work_days[]" value="{{ $num }}"
                 {{ in_array($num, $oldDays) ? 'checked' : '' }}>
          <label for="day{{ $num }}">{{ $name }}</label>
        </div>
        @endforeach
      </div>
    </div>

    <div class="form-section">
      <div class="section-title"><i class="fas fa-exclamation-triangle"></i> Opciones</div>
      <div class="form-group">
        <label style="display:flex; gap:12px; align-items:center;">
          <input type="checkbox" name="no_repetitive" id="no_repetitive" value="1">
          <span>No repetitivo — solo generar para esta semana</span>
        </label>
        <small style="display:block; color:#6c7a89; margin-top:6px">Si marcas esto, el sistema creará ocurrencias puntuales para la semana actual según los días seleccionados. Por defecto el horario es repetitivo.</small>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-submit">Guardar Horario</button>
      <a href="{{ route('employees.index') }}" class="btn-cancel">Cancelar</a>
    </div>
  </form>
</div>

<script>
// Datos de plantillas en JSON
const templatesJson = '{!! addslashes(json_encode($templates)) !!}';
const templates = JSON.parse(templatesJson);
document.getElementById('template_id')?.addEventListener('change', function() {
  const templateId = this.value;
  
  if (!templateId) {
    // Si no hay plantilla, limpiar campos
    document.getElementById('entry_time').value = '';
    document.getElementById('exit_time').value = '';
    // Desmarcar todos los checkboxes
    document.querySelectorAll('input[name="work_days[]"]').forEach(cb => cb.checked = false);
    return;
  }
  
  // Buscar la plantilla seleccionada
  const template = templates.find(t => t.id == templateId);
  
  if (template) {
    // Autocompletar hora de entrada
    if (template.entry_time) {
      const entryInput = document.getElementById('entry_time');
      entryInput.value = template.entry_time;
      // Disparar eventos para que el navegador detecte el cambio
      entryInput.dispatchEvent(new Event('input', { bubbles: true }));
      entryInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    // Autocompletar hora de salida
    if (template.exit_time) {
      const exitInput = document.getElementById('exit_time');
      exitInput.value = template.exit_time;
      // Disparar eventos para que el navegador detecte el cambio
      exitInput.dispatchEvent(new Event('input', { bubbles: true }));
      exitInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    // Autocompletar días laborables
    if (template.work_days && Array.isArray(template.work_days)) {
      // Primero desmarcar todos
      document.querySelectorAll('input[name="work_days[]"]').forEach(cb => cb.checked = false);
      
      // Marcar los días de la plantilla
      template.work_days.forEach(day => {
        const checkbox = document.getElementById('day' + day);
        if (checkbox) {
          checkbox.checked = true;
          checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    }
  }
});

// Init custom select to avoid oversized native dropdown
(function(){
  function initCustomSelect(id){
    const sel = document.getElementById(id);
    if(!sel) return;
    // build wrapper
    const wrapper = document.createElement('div'); wrapper.className='custom-select-wrapper';
    sel.parentNode.insertBefore(wrapper, sel);
    wrapper.appendChild(sel);
    sel.style.display = 'none';

    const custom = document.createElement('div'); custom.className='custom-select';
    const disp = document.createElement('div'); disp.className='custom-select-display';
    disp.textContent = sel.options[sel.selectedIndex]?.text || sel.options[0]?.text || '';
    const caret = document.createElement('div'); caret.className='custom-select-caret'; caret.innerHTML = '<i class="fas fa-caret-down"></i>';
    custom.appendChild(disp); custom.appendChild(caret);
    wrapper.appendChild(custom);

    const list = document.createElement('div'); list.className='custom-select-list';
    Array.from(sel.options).forEach(opt => {
      const item = document.createElement('div'); item.className='custom-select-item'; item.dataset.value = opt.value; item.textContent = opt.text;
      item.addEventListener('click', function(){
        sel.value = this.dataset.value;
        sel.dispatchEvent(new Event('change',{bubbles:true}));
        disp.textContent = this.textContent;
        list.classList.remove('open');
      });
      list.appendChild(item);
    });
    wrapper.appendChild(list);

    custom.addEventListener('click', function(e){ list.classList.toggle('open'); });
    document.addEventListener('click', function(e){ if(!wrapper.contains(e.target)) list.classList.remove('open'); });
  }

  initCustomSelect('template_id');
})();
</script>
@endsection