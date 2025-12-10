@php
  $genders = ['male' => 'Masculino', 'female' => 'Femenino', 'unknown' => 'Desconocido'];
@endphp

<label>Nombre *</label>
<input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}" maxlength="128" required>

<label>Género *</label>
<div>
  @foreach($genders as $val => $label)
    <label style="margin-right:12px">
      <input type="radio" name="gender" value="{{ $val }}"
        @checked(old('gender', $employee->gender ?? 'unknown') === $val)>
      {{ $label }}
    </label>
  @endforeach
</div>

{{-- No mostramos employee_no, department, user_type, fechas, etc. Todo va por defecto en el controlador --}}
