<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Asistencia – Iniciar Sesión</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🕐</text></svg>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        color: #fff;
    }

    /* Fondo con tu imagen */
    .bg-wrapper {
    position: fixed;
    inset: 0;
    background: url('../img/PORTADAUDH.jpg') center center / cover no-repeat;
    z-index: -2;
}

    /* Capa oscura */
    .bg-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        z-index: -1;
    }

    /* ---------- LOGIN CARD ---------- */
    .login-card {
        width: 100%;
        max-width: 460px;
        
        padding: 32px 40px;
        border: 0;

        background: rgba(255, 255, 255, 0.06);
        backdrop-filter: blur(22px);
        -webkit-backdrop-filter: blur(22px);

        border-radius: 24px;
        box-shadow: 0 12px 50px rgba(0, 0, 0, 0.40);

        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* LOGO */
    .logo-box {
        display: flex;
        justify-content: center;
        margin-bottom: 24px;
        margin-top: 0;
    }

    .logo-box img {
        width: 140px;
        height: auto;
        border-radius: 50%;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
    }

    /* TÍTULO */
    .title {
        text-align: center;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 32px;
    }

    /* LABEL */
    .field-label {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 8px;
        width: 100%;
    }

    .field-group {
        margin-bottom: 20px;
        width: 100%;
    }

    /* INPUTS */
    .input-control {
        width: 100%;
        height: 50px;
        padding: 0 16px;

        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(15, 23, 42, 0.55);

        color: #e5e7eb;
        font-size: 1rem;
        outline: none;
    }

    .input-control::placeholder {
        font-size: 0.95rem;
        color: rgba(200, 200, 200, 0.85);
    }

    .input-control:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.4);
    }

    /* BOTÓN */
    .btn-submit {
        width: 100%;
        height: 50px;

        margin-top: 8px;

        border-radius: 12px;
        border: none;

        cursor: pointer;
        font-weight: 600;
        font-size: 1.05rem;
        color: #f9fafb;

        background: linear-gradient(135deg, #16a34a, #0f8b44);
        box-shadow: 0 8px 24px rgba(22, 163, 74, 0.5);

        transition: 0.15s ease-in-out;
    }

    .btn-submit:hover {
        filter: brightness(1.07);
        box-shadow: 0 12px 32px rgba(22, 163, 74, 0.6);
    }

    .btn-submit:active {
        transform: scale(0.98);
    }

    /* ERRORES */
    .error-box {
        background: rgba(127, 29, 29, 0.9);
        border-radius: 12px;
        padding: 8px 12px;
        font-size: 0.9rem;
        margin-bottom: 15px;
        width: 100%;
    }

    .error-box ul {
        margin-left: 16px;
    }

    /* TEXTO PEQUEÑO */
    .small-text {
        margin-top: 20px;
        font-size: 0.85rem;
        text-align: center;
        color: rgba(226, 232, 240, 0.8);
    }

</style>

</head>
<body>

<div class="bg-wrapper"></div>
<div class="bg-overlay"></div>

<div class="login-card">
    <div class="logo-box">
        <img src="{{ asset('img/LogoLTD.svg') }}" alt="Logo">
    </div>

    <h1 class="title">Panel de Administración</h1>

    {{-- Mensajes de error --}}
    @if($errors->any())
        <div class="error-box">
            <strong>Hubo un problema:</strong>
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Mensaje de error de login (si usas session()->flash('error', ...)) --}}
    @if(session('error'))
        <div class="error-box">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="field-group">
            <label class="field-label" for="email">Correo</label>
            <input
                id="email"
                type="email"
                name="email"
                class="input-control"
                placeholder="correo@ejemplo.com"
                value="{{ old('email') }}"
                required
                autofocus
            >
        </div>

        <div class="field-group">
            <label class="field-label" for="password">Contraseña</label>
            <input
                id="password"
                type="password"
                name="password"
                class="input-control"
                placeholder="Ingresa tu contraseña"
                required
            >
        </div>

        <button type="submit" class="btn-submit">
            Ingresar
        </button>

        <div class="small-text">
            Acceso exclusivo para administradores del sistema.
        </div>
    </form>
</div>

</body>
</html>
