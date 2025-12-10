@extends('layouts.app')

@section('content')
<style>
  .create-employee-page {
    padding: 30px;
    max-width: 900px;
    margin: 0 auto;
  }
  
  .page-header-create {
    background: white;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 25px;
  }
  
  .page-header-create h1 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 5px 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .page-subtitle-create {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
  }
  
  .form-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
  }
  
  .form-section {
    padding: 30px;
  }
  
  .section-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #f1f3f5;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }
  
  .form-group-full {
    grid-column: 1 / -1;
  }
  
  .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  
  .form-label-modern {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  
  .form-input-modern {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: white;
  }
  
  .form-input-modern:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }
  
  .radio-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
  }
  
  .radio-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
  }
  
  .radio-option:hover {
    border-color: #667eea;
    background: #f8f9ff;
  }
  
  .radio-option input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #667eea;
  }
  
  .radio-option.selected {
    border-color: #667eea;
    background: #f0f3ff;
  }
  
  .radio-option label {
    margin: 0;
    cursor: pointer;
    font-weight: 500;
    color: #2c3e50;
  }
  
  .form-actions {
    padding: 20px 30px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
  }
  
  .btn-primary-modern {
    padding: 12px 28px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }
  
  .btn-secondary-modern {
    padding: 12px 28px;
    background: white;
    color: #6c757d;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  
  .btn-secondary-modern:hover {
    border-color: #6c757d;
    color: #495057;
    background: #f8f9fa;
  }
  
  .error-message {
    color: #dc3545;
    font-size: 13px;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  
  .input-icon {
    color: #7f8c8d;
    font-size: 16px;
  }
  
  @media (max-width: 768px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
    
    .create-employee-page {
      padding: 20px;
    }
    
    .form-actions {
      flex-direction: column-reverse;
    }
    
    .btn-primary-modern,
    .btn-secondary-modern {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<div class="create-employee-page">
  
  <!-- Header -->
  <div class="page-header-create">
    <h1>
      <svg width="28" height="28" fill="currentColor" viewBox="0 0 16 16" style="color: #667eea;">
        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
        <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
      </svg>
      Nuevo Empleado
    </h1>
    <p class="page-subtitle-create">Registra un nuevo empleado en el sistema</p>
  </div>

  <!-- Formulario -->
  <form id="empForm" method="post" action="{{ route('employees.store') }}">
    @csrf
    
    <div class="form-card">
      
      <!-- Sección: Información Personal -->
      <div class="form-section">
        <h2 class="section-title">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
          </svg>
          Información Personal
        </h2>
        
        <div class="form-grid">
          <!-- Nombre -->
          <div class="form-group form-group-full">
            <label class="form-label-modern">
              <span class="input-icon">👤</span>
              Nombre Completo
            </label>
            <input 
              type="text" 
              name="name" 
              class="form-input-modern" 
              value="{{ old('name') }}" 
              placeholder="Ej: Juan Pérez García"
              required
            >
            @error('name') 
              <div class="error-message">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                {{ $message }}
              </div>
            @enderror
          </div>
          
          <!-- Género -->
          <div class="form-group form-group-full">
            <label class="form-label-modern">
              <span class="input-icon">⚧</span>
              Género
            </label>
            <div class="radio-group">
              <div class="radio-option {{ old('gender','male')==='male'?'selected':'' }}">
                <input 
                  type="radio" 
                  name="gender" 
                  value="male" 
                  id="gender_male"
                  {{ old('gender','male')==='male'?'checked':'' }}
                >
                <label for="gender_male">👨 Masculino</label>
              </div>
              
              <div class="radio-option {{ old('gender')==='female'?'selected':'' }}">
                <input 
                  type="radio" 
                  name="gender" 
                  value="female" 
                  id="gender_female"
                  {{ old('gender')==='female'?'checked':'' }}
                >
                <label for="gender_female">👩 Femenino</label>
              </div>
              
              <div class="radio-option {{ old('gender')==='unknown'?'selected':'' }}">
                <input 
                  type="radio" 
                  name="gender" 
                  value="unknown" 
                  id="gender_unknown"
                  {{ old('gender')==='unknown'?'checked':'' }}
                >
                <label for="gender_unknown">❓ Otro</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Sección: Información Laboral -->
      <div class="form-section" style="border-top: 1px solid #f1f3f5;">
        <h2 class="section-title">
          <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v1.384l7.614 2.03a1.5 1.5 0 0 0 .772 0L16 5.884V4.5A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5z"/>
            <path d="M0 12.5A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5V6.85L8.129 8.947a.5.5 0 0 1-.258 0L0 6.85v5.65z"/>
          </svg>
          Información Laboral
        </h2>
        
        <div class="form-grid">
          <!-- Fecha de contratación -->
          <div class="form-group">
            <label class="form-label-modern">
              <span class="input-icon">📅</span>
              Fecha de Contratación
            </label>
            <input 
              type="date" 
              name="hire_date" 
              class="form-input-modern"
              value="{{ old('hire_date', now()->toDateString()) }}"
            >
            @error('hire_date') 
              <div class="error-message">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                {{ $message }}
              </div>
            @enderror
          </div>
          
          <!-- Pago por hora -->
          <div class="form-group">
            <label class="form-label-modern">
              <span class="input-icon">💰</span>
              Pago por Hora (S/)
            </label>
            <input
              type="number"
              step="0.01"
              min="0"
              name="hourly_rate"
              id="hourly_rate"
              class="form-input-modern"
              value="{{ old('hourly_rate', 0) }}"
              placeholder="0.00"
            >
          </div>
        </div>
      </div>
      
      <!-- Acciones -->
      <div class="form-actions">
        <a href="{{ route('employees.index') }}" class="btn-secondary-modern">
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
          </svg>
          Cancelar
        </a>
        <button type="submit" id="saveBtn" class="btn-primary-modern">
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
          </svg>
          Guardar Empleado
        </button>
      </div>
      
    </div>
  </form>
  
</div>

<script>
  // Efecto visual para los radio buttons
  document.querySelectorAll('.radio-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
      // Remover selected de todos
      document.querySelectorAll('.radio-option').forEach(opt => {
        opt.classList.remove('selected');
      });
      // Agregar selected al padre del radio seleccionado
      if(this.checked) {
        this.closest('.radio-option').classList.add('selected');
      }
    });
  });
</script>

@endsection
