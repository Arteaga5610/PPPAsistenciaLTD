@extends('layouts.app')

@section('content')
<div style="max-width:900px;margin:30px auto">
  <h1>Configuración SMTP</h1>
  @if(session('ok')) <div style="background:#d4edda;padding:12px;border-radius:8px">{{ session('ok') }}</div>@endif

  <form method="post" action="{{ route('admin.mailsettings.update') }}" style="margin-top:16px">
    @csrf
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <label>Host</label>
        <input name="host" value="{{ old('host',$settings->host ?? 'smtp.gmail.com') }}" class="form-control">
      </div>
      <div>
        <label>Puerto</label>
        <input name="port" value="{{ old('port',$settings->port ?? 587) }}" class="form-control">
      </div>
      <div>
        <label>Usuario (correo)</label>
        <input name="username" value="{{ old('username',$settings->username ?? '') }}" class="form-control">
      </div>
      <div>
        <label>App Password (dejar vacío para no cambiar)</label>
        <input name="password" value="" class="form-control" autocomplete="new-password">
      </div>
      <div>
        <label>Encriptación</label>
        <input name="encryption" value="{{ old('encryption',$settings->encryption ?? 'tls') }}" class="form-control">
      </div>
      <div>
        <label>From address</label>
        <input name="from_address" value="{{ old('from_address',$settings->from_address ?? '') }}" class="form-control">
      </div>
      <div>
        <label>From name</label>
        <input name="from_name" value="{{ old('from_name',$settings->from_name ?? '') }}" class="form-control">
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <label style="margin-right:8px">Habilitado</label>
        <input type="checkbox" name="enabled" value="1" {{ (old('enabled', $settings->enabled ?? false) ? 'checked' : '') }}>
      </div>
    </div>

    <div style="margin-top:12px">
      <button class="btn primary">Guardar</button>
    </div>
  </form>

  <hr>
  <h3>Enviar correo de prueba</h3>
  <form method="post" action="{{ route('admin.mailsettings.test') }}">
    @csrf
    <div style="display:flex;gap:8px;align-items:center">
      <input name="test_to" placeholder="Correo de destino (opcional)" class="form-control">
      <button class="btn">Enviar prueba</button>
    </div>
  </form>
</div>
@endsection
