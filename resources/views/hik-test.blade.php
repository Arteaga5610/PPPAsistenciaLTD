@extends('layouts.app')

@section('content')
<style>
  .auth-page {
    max-width: 1200px;
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

  .alert-info {
    background: #e8f4f8;
    border-left: 4px solid #3498db;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 2rem;
  }

  .alert-info strong {
    color: #2c3e50;
    font-weight: 600;
  }

  .alert-info code {
    background: #d6eaf8;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.9rem;
    color: #1f618d;
  }

  .auth-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 2rem;
    margin-bottom: 2rem;
  }

  .card-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #ecf0f1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  @media (max-width: 480px) {
    .form-row { grid-template-columns: 1fr; }
    .auth-card { padding: 1rem; }
  }

  .form-group {
    margin-bottom: 1rem;
  }

  .form-group label {
    display: block;
    font-weight: 500;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
  }

  .form-group input[type="text"],
  .form-group input[type="number"],
  .form-group input[type="file"] {
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

  .btn-action {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 0.5rem;
  }

  .btn-primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(52, 152, 219, 0.2);
  }

  .btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #2980b9 0%, #21618c 100%);
    box-shadow: 0 6px 12px rgba(52, 152, 219, 0.3);
    transform: translateY(-2px);
  }

  .btn-success {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(39, 174, 96, 0.2);
  }

  .btn-success:hover:not(:disabled) {
    background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
    box-shadow: 0 6px 12px rgba(39, 174, 96, 0.3);
    transform: translateY(-2px);
  }

  .btn-warning {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(243, 156, 18, 0.2);
  }

  .btn-warning:hover:not(:disabled) {
    background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
    box-shadow: 0 6px 12px rgba(243, 156, 18, 0.3);
    transform: translateY(-2px);
  }

  .btn-danger {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(231, 76, 60, 0.2);
  }

  .btn-danger:hover:not(:disabled) {
    background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
    box-shadow: 0 6px 12px rgba(231, 76, 60, 0.3);
    transform: translateY(-2px);
  }

  button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
  }

  .response-container {
    margin-top: 1.5rem;
  }

  .response-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.75rem;
    font-size: 1rem;
  }

  .response-output {
    background: #0b1020;
    color: #cde3ff;
    padding: 1rem;
    border-radius: 8px;
    overflow: auto;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.85rem;
    max-height: 300px;
  }

  .checkbox-group {
    display: flex;
    gap: 1.5rem;
    margin: 1rem 0;
  }

  .checkbox-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: 500;
    color: #2c3e50;
  }

  .checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }

  .button-group {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-top: 1rem;
  }

  .hint-text {
    color: #95a5a6;
    font-size: 0.85rem;
    margin-left: 1rem;
  }
</style>

<div class="auth-page">
  <div class="page-header">
    <h1>Gestión de Autenticación</h1>
    <p class="page-subtitle">Configura los métodos de autenticación biométrica para empleados</p>
  </div>

  <div class="alert-info">
    <strong>Importante:</strong> Asegúrate de tener corriendo el servicio <strong>FaceBridge</strong> (C#) y que
    tu <code>.env</code> tenga <code>FACEBRIDGE_URL</code> correctamente configurado.
  </div>

  <div class="auth-card">
    <div class="card-title">💳 1) Registrar/Actualizar Tarjeta</div>
    <form id="formCard">
      <div class="form-row">
        <div class="form-group">
          <label>N° Tarjeta (cardNo)</label>
          <input name="cardNo" placeholder="Ej: 12345" required>
        </div>
        <div class="form-group">
          <label>ID Empleado (employeeNo)</label>
          <input name="employeeNo" placeholder="Ej: 1011" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Plan de derechos (rightPlan)</label>
          <input name="rightPlan" type="number" value="0" min="0" max="15">
        </div>
        <div style="display:flex;align-items:end;">
          <button type="submit" id="btnCard" class="btn-action btn-primary">Enviar</button>
        </div>
      </div>
      
      <div class="response-container">
        <div class="response-title">Respuesta</div>
        <pre id="outCard" class="response-output">{}</pre>
      </div>
    </form>
  </div>

  <div class="auth-card">
    <div class="card-title">😊 2) Subir Rostro para Tarjeta</div>
    <form id="formFace">
      <div class="form-row">
        <div class="form-group">
          <label>N° Tarjeta (cardNo)</label>
          <input name="cardNo" placeholder="Debe existir o la crearás" required>
        </div>
        <div class="form-group">
          <label>Lector (readerNo)</label>
          <input name="readerNo" type="number" value="1" min="1" max="8">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Foto (JPG ≤ 200KB)</label>
          <input name="foto" type="file" accept=".jpg,.jpeg" required>
        </div>
        <div style="display:flex;align-items:end;">
          <button type="submit" id="btnFace" class="btn-action btn-success">Subir Rostro</button>
        </div>
      </div>
      
      <div class="response-container">
        <div class="response-title">Respuesta</div>
        <pre id="outFace" class="response-output">{}</pre>
      </div>
    </form>
  </div>
  
  <div class="auth-card">
    <div class="card-title">👆 3) Capturar Huella en Lector (2 pasos)</div>
    <form id="formFinger">
      <div class="form-row">
        <div class="form-group">
          <label>N° Tarjeta (cardNo)</label>
          <input name="cardNo" placeholder="Debe existir" required>
        </div>
        <div class="form-group">
          <label>Lector (readerNo)</label>
          <input name="readerNo" type="number" value="1" min="1" max="8">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Finger ID (0–9)</label>
          <input name="fingerId" type="number" value="0" min="0" max="9">
        </div>
      </div>
      
      <div class="button-group">
        <button type="button" id="btnFingerCapture" class="btn-action btn-warning">1) Capturar Huella</button>
        <button type="button" id="btnFingerSet" class="btn-action btn-success" disabled>2) Guardar Huella</button>
        <span class="hint-text" id="fingerHint"></span>
      </div>
      
      <div class="response-container">
        <div class="response-title">Respuesta</div>
        <pre id="outFinger" class="response-output">{}</pre>
      </div>
    </form>
  </div>

  <div class="auth-card">
    <div class="card-title"><i class="fas fa-trash-alt"></i> 4) Eliminar Credenciales por EmployeeNo</div>
    <form id="formDelete">
      <div class="form-group">
        <label>ID Empleado (employeeNo)</label>
        <input name="employeeNo" placeholder="Ej: 1011" required>
      </div>
      
      <div class="checkbox-group">
        <label>
          <input type="checkbox" name="types[]" value="card" checked>
          Tarjeta
        </label>
        <label>
          <input type="checkbox" name="types[]" value="face" checked>
          Rostro
        </label>
        <label>
          <input type="checkbox" name="types[]" value="finger" checked>
          Huella
        </label>
      </div>
      
      <button type="submit" id="btnDelete" class="btn-action btn-danger">
        Eliminar en Equipo + Actualizar Laravel
      </button>
      
      <div class="response-container">
        <div class="response-title">Respuesta</div>
        <pre id="outDelete" class="response-output">{}</pre>
      </div>
    </form>
  </div>
</div>

<script>
let capturedTemplateB64 = null;

const outCard = document.getElementById('outCard');
const outFace = document.getElementById('outFace');
const btnCard = document.getElementById('btnCard');
const btnFace = document.getElementById('btnFace');
const outFinger = document.getElementById('outFinger');

const btnFingerCapture = document.getElementById('btnFingerCapture');
const btnFingerSet = document.getElementById('btnFingerSet');
const fingerHint = document.getElementById('fingerHint');
const formFinger = document.getElementById('formFinger');

document.getElementById('formCard').addEventListener('submit', async (e) => {
  e.preventDefault();
  btnCard.disabled = true;
  outCard.textContent = 'Enviando...';

  const fd = new FormData(e.target);
  try {
    const res = await fetch(`{{ url('/api/hik/set-card') }}`, {
      method: 'POST',
      body: fd
    });
    const text = await res.text();
    try {
      const json = JSON.parse(text);
      outCard.textContent = JSON.stringify(json, null, 2);
    } catch {
      outCard.textContent = 'Respuesta no JSON:\n' + text;
    }
  } catch (err) {
    outCard.textContent = 'Error: ' + err;
  } finally {
    btnCard.disabled = false;
  }
});

document.getElementById('formFace').addEventListener('submit', async (e) => {
  e.preventDefault();
  btnFace.disabled = true;
  outFace.textContent = 'Subiendo...';

  const fd = new FormData(e.target);
  try {
    const res = await fetch(`{{ url('/api/hik/set-face') }}`, {
      method: 'POST',
      body: fd
    });
    const text = await res.text();
    try {
      const json = JSON.parse(text);
      outFace.textContent = JSON.stringify(json, null, 2);
    } catch {
      outFace.textContent = 'Respuesta no JSON:\n' + text;
    }
  } catch (err) {
    outFace.textContent = 'Error: ' + err;
  } finally {
    btnFace.disabled = false;
  }
});

btnFingerCapture.addEventListener('click', async () => {
  const fd = new FormData(formFinger);
  const readerNo = Number(fd.get('readerNo') || 1);

  outFinger.textContent = 'Solicitando captura... Pon el dedo 3 veces cuando el equipo lo pida.';
  btnFingerCapture.disabled = true;
  btnFingerSet.disabled = true;
  fingerHint.textContent = '';

  try {
    const res = await fetch(`{{ url('/api/hik/finger/capture') }}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ readerNo })
    });
    const j = await res.json();

    if (j.ok && j.templateBase64) {
      capturedTemplateB64 = j.templateBase64;
      btnFingerSet.disabled = false;
      fingerHint.textContent = 'Plantilla capturada. Ahora pulsa "Guardar huella".';
      outFinger.textContent = JSON.stringify({ ok: true, msg: 'Plantilla capturada' }, null, 2);
    } else {
      capturedTemplateB64 = null;
      outFinger.textContent = JSON.stringify(j, null, 2);
    }
  } catch (e) {
    capturedTemplateB64 = null;
    outFinger.textContent = 'Error: ' + e;
  } finally {
    btnFingerCapture.disabled = false;
  }
});

btnFingerSet.addEventListener('click', async () => {
  const fd = new FormData(formFinger);
  const payload = {
    cardNo: String(fd.get('cardNo') || ''),
    readerNo: Number(fd.get('readerNo') || 1),
    fingerId: Number(fd.get('fingerId') || 0),
    templateBase64: capturedTemplateB64
  };

  if (!payload.templateBase64) {
    outFinger.textContent = 'Primero captura la huella.';
    return;
  }

  btnFingerSet.disabled = true;
  outFinger.textContent = 'Guardando huella...';

  try {
    const res = await fetch(`{{ url('/api/hik/finger/set') }}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const j = await res.json();
    outFinger.textContent = JSON.stringify(j, null, 2);

    if (j.ok) {
      fingerHint.textContent = 'Huella guardada ✔';
      capturedTemplateB64 = null;
      btnFingerSet.disabled = true;
    } else if (j.err === 17) {
      fingerHint.textContent = 'Error 17: revisa fingerId (0–9), que el cardNo exista y el readerNo sea el correcto.';
    } else {
      fingerHint.textContent = '';
    }
  } catch (e) {
    outFinger.textContent = 'Error: ' + e;
  } finally {
    btnFingerSet.disabled = false;
  }
});

document.getElementById('formDelete')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('btnDelete');
  const out = document.getElementById('outDelete');
  btn.disabled = true;
  out.textContent = 'Enviando petición de eliminación...';

  const fd = new FormData(e.target);
  const employeeNo = String(fd.get('employeeNo') || '');

  const types = Array.from(document.querySelectorAll('input[name="types[]"]:checked'))
    .map(el => el.value);

  const result = { employeeNo, results: {} };

  try {
    // 1) Tarjeta: Laravel + ISAPI
    if (types.includes('card')) {
      const resCard = await fetch(`{{ url('/api/hik/delete-card') }}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ employeeNo })
      });
      const jCard = await resCard.json();
      result.results.card = jCard;
    }

    // 2) Face / finger: FaceBridge
    const otherTypes = types.filter(t => t !== 'card');
    if (otherTypes.length > 0) {
      const payloadFB = { employeeNo, types: otherTypes };

      const resFB = await fetch(`{{ url('/hik/test') }}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payloadFB)
      });

      const textFB = await resFB.text();
      try {
        const jFB = JSON.parse(textFB);
        result.results.facebridge = jFB;
      } catch {
        result.results.facebridge = { ok: false, raw: textFB };
      }
    }

    out.textContent = JSON.stringify(result, null, 2);
  } catch (err) {
    out.textContent = 'Error: ' + err;
  } finally {
    btn.disabled = false;
  }
});
</script>
@endsection
