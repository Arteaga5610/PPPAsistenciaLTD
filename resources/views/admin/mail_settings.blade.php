@extends('layouts.app')

@section('content')
<style>
  .mail-settings-page { max-width: 1000px; margin: 30px auto; }
  .mail-card { background: white; border-radius: 12px; box-shadow: 0 6px 20px rgba(29,32,57,0.06); overflow: hidden }
  .mail-card .card-header { padding: 22px 28px; border-bottom: 1px solid #f1f3f7; display:flex; align-items:center; justify-content:space-between }
  .mail-card .card-title { font-size:20px; font-weight:700; color:#253246 }
  .mail-card .card-body { padding: 20px 28px }
  .mail-grid { display:grid; grid-template-columns: 1fr 1fr; gap:18px }
  .form-label { font-size:13px; color:#34495e; font-weight:600; margin-bottom:6px }
  .form-input { width:100%; padding:10px 12px; border-radius:8px; border:1px solid #e6edf3; background:#fbfdff }
  .small-note { font-size:12px; color:#7a8ca3; margin-top:6px }
  .actions { display:flex; gap:10px; justify-content:flex-end; padding:18px 28px; border-top:1px solid #f1f3f7 }
  .btn-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border:none; padding:10px 16px; border-radius:8px }
  .btn-outline { background:white; border:1px solid #d6dbe2; padding:10px 16px; border-radius:8px }
</style>

<div class="mail-settings-page">
  @if(session('ok')) <div class="flash">✅ {{ session('ok') }}</div>@endif

  @php $saved = !empty($settings); @endphp

  <div class="mail-card">
    <div class="card-header">
      <div class="card-title">Configuración SMTP</div>
      <div style="color:#7a8ca3">Configura la cuenta que enviará los reportes en PDF</div>
    </div>

    <form method="post" action="{{ route('admin.mailsettings.update') }}">
      @csrf
      <div class="card-body">
        <div class="mail-grid">
          <div>
            <label class="form-label">Host</label>
            <input name="host" class="form-input" value="{{ old('host',$settings->host ?? 'smtp.gmail.com') }}" {{ $saved ? 'disabled' : '' }}>
            <div class="small-note">Servidor SMTP (ej. smtp.gmail.com)</div>
          </div>

          <div>
            <label class="form-label">Puerto</label>
            <input name="port" class="form-input" value="{{ old('port',$settings->port ?? 587) }}" {{ $saved ? 'disabled' : '' }}>
            <div class="small-note">Usualmente 587 (TLS) o 465 (SSL)</div>
          </div>

          <div>
            <label class="form-label">Usuario (correo)</label>
            <input name="username" class="form-input" value="{{ old('username',$settings->username ?? '') }}" {{ $saved ? 'disabled' : '' }}>
          </div>

          <div>
            <label class="form-label">App Password</label>
            <input name="password" class="form-input" value="" autocomplete="new-password" {{ $saved ? 'disabled' : '' }}>
            <div class="small-note">Usa App Password si tu cuenta requiere 2FA. Déjalo vacío para mantener la actual.</div>
          </div>

          <div>
            <label class="form-label">Encriptación</label>
            <input name="encryption" class="form-input" value="{{ old('encryption',$settings->encryption ?? 'tls') }}" {{ $saved ? 'disabled' : '' }}>
          </div>

          <div>
            <label class="form-label">From address</label>
            <input name="from_address" class="form-input" value="{{ old('from_address',$settings->from_address ?? '') }}" {{ $saved ? 'disabled' : '' }}>
            <div class="small-note">Dirección que verá el destinatario como remitente.</div>
          </div>

          <div>
            <label class="form-label">From name</label>
            <input name="from_name" class="form-input" value="{{ old('from_name',$settings->from_name ?? '') }}" {{ $saved ? 'disabled' : '' }}>
          </div>

          <div style="display:flex;align-items:center;gap:10px">
            <label class="form-label" style="margin-bottom:0">Habilitado</label>
            <input type="checkbox" name="enabled" value="1" {{ (old('enabled', $settings->enabled ?? false) ? 'checked' : '') }} {{ $saved ? 'disabled' : '' }}>
          </div>
        </div>
      </div>

      <div class="actions">
        @if(!$saved)
          <button class="btn-primary">Guardar</button>
        @else
          <button type="button" id="btn-edit" class="btn-outline">ACTUALIZAR</button>
          <button type="submit" id="btn-save" class="btn-primary" style="display:none">Guardar cambios</button>
          <button type="button" id="btn-cancel" class="btn-outline" style="display:none">Cancelar</button>
        @endif
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function(){
    const saved = {{ $saved ? 'true' : 'false' }};
    if (!saved) return;

    const btnEdit = document.getElementById('btn-edit');
    const btnSave = document.getElementById('btn-save');
    const btnCancel = document.getElementById('btn-cancel');
    const form = document.querySelector('form');
    const inputs = Array.from(form.querySelectorAll('input'));

    btnEdit.addEventListener('click', () => {
      inputs.forEach(i => i.removeAttribute('disabled'));
      btnEdit.style.display = 'none';
      btnSave.style.display = '';
      btnCancel.style.display = '';
    });

    btnCancel.addEventListener('click', () => { window.location.reload(); });
  });
</script>

@endsection
