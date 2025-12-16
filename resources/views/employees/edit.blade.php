@extends('layouts.app')

@section('content')
<style>
    .edit-employee-page {
        padding: 2rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .employee-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        padding: 2.5rem;
        margin-bottom: 1.5rem;
    }

    .employee-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .employee-info .icon {
        font-size: 2.5rem;
    }

    .employee-info .details {
        flex: 1;
    }

    .employee-info .emp-name {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .employee-info .emp-no {
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #34495e;
        margin-bottom: 1.25rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #ecf0f1;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: #555;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-label.required::after {
        content: " *";
        color: #e74c3c;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .form-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .radio-group {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        padding: 0.75rem 1.25rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .radio-option:hover {
        border-color: #667eea;
        background: white;
    }

    .radio-option input[type="radio"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #667eea;
    }

    .radio-option input[type="radio"]:checked + .radio-label {
        color: #667eea;
        font-weight: 600;
    }

    .radio-option.selected {
        border-color: #667eea;
        background: #f0f3ff;
    }

    .radio-label {
        font-size: 0.95rem;
        color: #555;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #ecf0f1;
    }

    .btn {
        padding: 0.875rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
    }

    .btn-secondary {
        background: white;
        color: #555;
        border: 2px solid #ddd;
    }

    .btn-secondary:hover {
        background: #f8f9fa;
        border-color: #999;
    }

    .error-message {
        background: #fee;
        border-left: 4px solid #e74c3c;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        color: #c0392b;
    }

    .success-message {
        background: #efffef;
        border-left: 4px solid #27ae60;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        color: #27ae60;
    }

    .input-icon {
        font-size: 0.9rem;
        margin-right: 0.25rem;
    }

    @media (max-width: 768px) {
        .edit-employee-page {
            padding: 1rem;
        }

        .employee-card {
            padding: 1.5rem;
        }

        .radio-group {
            flex-direction: column;
            gap: 0.75rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="edit-employee-page">
    <div class="page-header">
        <h1 class="page-title">
            <span>✏️</span>
            Editar Empleado
        </h1>
    </div>

    @if ($errors->any())
        <div class="error-message">
            <strong>⚠️ Errores de validación:</strong>
            <ul style="margin: 0.5rem 0 0 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="success-message">
            ✓ {{ session('status') }}
        </div>
    @endif

    <div class="employee-card">
        <div class="employee-info">
            <div class="icon">👤</div>
            <div class="details">
                <div class="emp-name">{{ $employee->name }}</div>
                <div class="emp-no">Empleado N° {{ $employee->employee_no }}</div>
            </div>
        </div>

        <form method="post" action="{{ route('employees.update', $employee) }}">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h3 class="section-title">💼 Información Salarial</h3>
                
                <div class="form-group">
                    <label for="hourly_rate" class="form-label">
                        <span class="input-icon">💰</span>
                        Pago por hora (S/)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="hourly_rate"
                        id="hourly_rate"
                        class="form-input"
                        value="{{ old('hourly_rate', $employee->hourly_rate) }}"
                        placeholder="0.00"
                    >
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">📋 Información Personal</h3>

                <div class="form-group">
                    <label for="name" class="form-label required">
                        <span class="input-icon">👤</span>
                        Nombre
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-input"
                        value="{{ old('name', $employee->name) }}"
                        maxlength="128"
                        required
                        placeholder="Nombre completo del empleado"
                    >
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <span class="input-icon">✉️</span>
                        Correo electrónico
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-input"
                        value="{{ old('email', $employee->email) }}"
                        maxlength="128"
                        placeholder="correo@ejemplo.com"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label required">
                        <span class="input-icon">⚧️</span>
                        Género
                    </label>
                    <div class="radio-group">
                        <label class="radio-option {{ old('gender', $employee->gender) === 'male' ? 'selected' : '' }}">
                            <input
                                type="radio"
                                name="gender"
                                value="male"
                                {{ old('gender', $employee->gender) === 'male' ? 'checked' : '' }}
                            >
                            <span class="radio-label">Masculino</span>
                        </label>
                        
                        <label class="radio-option {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}">
                            <input
                                type="radio"
                                name="gender"
                                value="female"
                                {{ old('gender', $employee->gender) === 'female' ? 'checked' : '' }}
                            >
                            <span class="radio-label">Femenino</span>
                        </label>
                        
                        <label class="radio-option {{ old('gender', $employee->gender) === 'unknown' ? 'selected' : '' }}">
                            <input
                                type="radio"
                                name="gender"
                                value="unknown"
                                {{ old('gender', $employee->gender) === 'unknown' ? 'checked' : '' }}
                            >
                            <span class="radio-label">Desconocido</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span>
                    Actualizar
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                    <span>✖️</span>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    // Actualizar estilo de radio buttons al seleccionar
    document.querySelectorAll('.radio-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Remover clase selected de todos
            this.closest('.radio-group').querySelectorAll('.radio-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            // Agregar clase al seleccionado
            if (this.checked) {
                this.closest('.radio-option').classList.add('selected');
            }
        });
    });
</script>
@endsection
