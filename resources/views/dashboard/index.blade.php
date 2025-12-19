@extends('layouts.app')

@section('content')
<style>
  /* =========================
    INFO PERSONA (ESTILO IMAGEN)
    ========================= */
  .employee-info {
    background: #fff;
    border-radius: 0;
    padding: 22px 26px;
    box-shadow: none;
    margin-bottom: 18px;
  }

  .employee-info-title {
    font-weight: 800;
    color: #111;
    margin: 0 0 18px 0;
    font-size: 18px;
    font-family: Georgia, "Times New Roman", serif;
  }

  .employee-info-row {
    display: grid;
    grid-template-columns: 220px 1fr 1fr 1fr;
    align-items: center;
    gap: 0;
  }

  .info-total {
    padding-right: 18px;
    display: grid;
    place-items: center;
    text-align: center;
  }

  .info-total .label {
    font-size: 16px;
    color: #333;
    font-family: Georgia, "Times New Roman", serif;
  }

  .info-total .value {
    font-size: 54px;
    font-weight: 800;
    line-height: 1;
    margin-top: 8px;
    color: #111;
    font-family: Georgia, "Times New Roman", serif;
  }

  .info-block {
    display: grid;
    grid-template-columns: 120px 1fr;
    align-items: center;
    gap: 18px;
    padding: 0 22px;
    min-width: 0;
    position: relative;
  }

  .info-block:not(:first-child)::before{
    content:"";
    position:absolute;
    left:0;
    top:50%;
    transform:translateY(-50%);
    height:90px;
    border-left:1px solid rgba(0,0,0,0.12);
  }

  .donut-wrap{
    display:grid;
    place-items:center;
    position:relative;
    width: 96px;
    height: 96px;
  }

  .donut {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: conic-gradient(var(--c) calc(var(--p) * 1%), #e9ecef 0);
    display:grid;
    place-items:center;
  }

  .donut::after{
    content:"";
    width: 74px;
    height: 74px;
    background:#fff;
    border-radius:50%;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
  }

  .donut-icon{
    position:absolute;
    font-size: 30px;
    color:#2c3e50;
  }

  .info-meta{
    min-width:0;
    display:grid;
    gap: 8px;
  }

  .info-meta .row{
    display:flex;
    justify-content: space-between;
    align-items: baseline;
    font-size: 14px;
    color:#333;
    font-family: Georgia, "Times New Roman", serif;
  }

  .info-meta .row b{
    font-weight: 900;
    color:#000;
    font-size: 22px;
    font-family: Georgia, "Times New Roman", serif;
  }

  @media (max-width: 1200px){
    .employee-info-row{
      grid-template-columns: 1fr;
      gap: 18px;
    }
    .info-block{
      padding: 14px 0;
    }
    .info-block::before{ display:none !important; }
    .info-total{ padding-right:0; }
  }
</style>

<style>
  /* =========================
    INFORME ASISTENCIA DEL DÍA
    ========================= */
  .daily-report {
    background: #fff;
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 14px;
  }

  .daily-report-header{
    display:flex;
    align-items:center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
  }

  .daily-report-title{
    margin:0;
    font-size: 16px;
    font-weight: 900;
    color:#2c3e50;
    display:flex;
    align-items:center;
    gap:10px;
  }

  .daily-report-title i{ color:#11998e; }

  .daily-report-date{
    font-size: 12px;
    font-weight: 800;
    color:#6c757d;
    background:#f8f9fa;
    padding: 6px 10px;
    border-radius: 999px;
    white-space: nowrap;
  }

  .daily-report-grid{
    display:grid;
    grid-template-columns: 1.2fr 1.8fr;
    gap: 14px;
    align-items: stretch;
  }

  .daily-card{
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 12px;
    padding: 12px;
    min-height: 0;
  }

  .daily-card h4{
    margin: 0 0 10px 0;
    font-size: 13px;
    font-weight: 900;
    color:#2c3e50;
  }

  .canvas-wrap{
    height: 260px;
    position: relative;
  }

  @media (max-width: 1200px){
    .daily-report-grid{ grid-template-columns: 1fr; }
  }

  @media (max-width: 520px){
    .canvas-wrap{ height: 220px; }
  }
</style>

<div class="dashboard-page">
  @php
    $total = (int) ($totalEmployees ?? 0);

    $faceAdded = (int) ($employeesWithFace ?? 0);
    $cardAdded = (int) ($employeesWithCard ?? 0);
    $fpAdded   = (int) ($employeesWithFp ?? 0);

    $faceNo = max(0, $total - $faceAdded);
    $cardNo = max(0, $total - $cardAdded);
    $fpNo   = max(0, $total - $fpAdded);

    $pct = function($added) use ($total){
      if ($total <= 0) return 0;
      return (int) round(($added / $total) * 100);
    };

    $facePct = $pct($faceAdded);
    $cardPct = $pct($cardAdded);
    $fpPct   = $pct($fpAdded);
  @endphp

  <div class="employee-info">
    <h3 class="employee-info-title">Información de la persona</h3>

    <div class="employee-info-row">
      <div class="info-total">
        <div class="label">Persona</div>
        <div class="value">{{ $total }}</div>
      </div>

      <div class="info-block">
        <div class="donut-wrap">
          <div class="donut" style="--p: {{ $facePct }}; --c: #dfe3e8;"></div>
          <i class="fas fa-user-check donut-icon"></i>
        </div>
        <div class="info-meta">
          <div class="row"><span>Añadido</span> <b>{{ $faceAdded }}</b></div>
          <div class="row"><span>No añadido</span> <b>{{ $faceNo }}</b></div>
        </div>
      </div>

      <div class="info-block">
        <div class="donut-wrap">
          <div class="donut" style="--p: {{ $cardPct }}; --c: #2f63ff;"></div>
          <i class="fas fa-id-card donut-icon"></i>
        </div>
        <div class="info-meta">
          <div class="row"><span>Añadido</span> <b>{{ $cardAdded }}</b></div>
          <div class="row"><span>No añadido</span> <b>{{ $cardNo }}</b></div>
        </div>
      </div>

      <div class="info-block">
        <div class="donut-wrap">
          <div class="donut" style="--p: {{ $fpPct }}; --c: #e9ecef;"></div>
          <i class="fas fa-fingerprint donut-icon"></i>
        </div>
        <div class="info-meta">
          <div class="row"><span>Añadido</span> <b>{{ $fpAdded }}</b></div>
          <div class="row"><span>No añadido</span> <b>{{ $fpNo }}</b></div>
        </div>
      </div>
    </div>
  </div>

  @php
  // ✅ TURNOS HOY (según tu nueva regla)
  $totalHoy = (int) ($scheduleCount['total'] ?? 0);

  // ✅ "Contabilizados" = turnos cerrados (marcó salida O ya pasó su ventana de salida)
  // Nota: esto viene del controlador como 'closed'
  $marcaronHoy = (int) ($scheduleCount['closed'] ?? 0);

  // Pendientes = turnos que aún no cierran (típico: falta salida y todavía no pasó la ventana)
  $pendientesHoy = (int) ($scheduleCount['pending'] ?? max(0, $totalHoy - $marcaronHoy));

  $tardeHoy = (int) ($lateArrivals ?? 0);
  $anticipadaHoy = (int) ($earlyExits ?? 0);
  $puntualHoy = (int) ($punctualEmployees ?? 0);
@endphp

  <div class="daily-report"
     id="dashboard-today-data"
     data-total="{{ $totalHoy }}"
     data-marcaron="{{ $marcaronHoy }}"
     data-pendientes="{{ $pendientesHoy }}"
     data-tarde="{{ $tardeHoy }}"
     data-anticipada="{{ $anticipadaHoy }}"
     data-puntual="{{ $puntualHoy }}">
    
    <div class="daily-report-header">
      <h3 class="daily-report-title">
        <i class="fas fa-clipboard-check"></i>
        Informe de Asistencia del Día
      </h3>
      <span class="daily-report-date">
        <i class="far fa-calendar-alt"></i>
        Hoy
      </span>
    </div>

    <div class="daily-report-grid">
      <!-- ✅ PIE CHART (CIRCULAR): marcaron vs pendientes -->
      <div class="daily-card">
        <h4>Marcación de hoy</h4>
        <div class="canvas-wrap">
          <canvas id="chartMarcacionHoy"></canvas>
        </div>
        <div style="font-size:12px; color:#6c757d; font-weight:700; margin-top:8px;">
          Total programados: <b>{{ $totalHoy }}</b> | Marcaron: <b>{{ $marcaronHoy }}</b> | Pendientes: <b>{{ $pendientesHoy }}</b>
        </div>
      </div>

      <!-- Barras -->
      <div class="daily-card">
        <h4>Incidencias de hoy</h4>
        <div class="canvas-wrap">
          <canvas id="chartIncidenciasHoy"></canvas>
        </div>
        <div style="font-size:12px; color:#6c757d; font-weight:700; margin-top:8px;">
          * “Llegaron tarde” y “Salidas anticipadas” cuentan al empleado una sola vez por día (según tu lógica).
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    (function(){
    const root = document.getElementById('dashboard-today-data');
    const total       = Number(root?.dataset?.total ?? 0);
    const marcaron    = Number(root?.dataset?.marcaron ?? 0);
    const pendientes  = Number(root?.dataset?.pendientes ?? 0);
    const tarde       = Number(root?.dataset?.tarde ?? 0);
    const anticipada  = Number(root?.dataset?.anticipada ?? 0);
    const puntual     = Number(root?.dataset?.puntual ?? 0);


      // Plugin: dibuja % dentro de cada porción (como tu imagen)
      const piePercentLabels = {
        id: 'piePercentLabels',
        afterDatasetsDraw(chart, args, pluginOptions) {
          const { ctx } = chart;
          const meta = chart.getDatasetMeta(0);
          const data = chart.data.datasets[0].data || [];
          const sum = data.reduce((a, b) => a + (Number(b) || 0), 0) || 1;

          ctx.save();
          ctx.font = '700 12px system-ui, -apple-system, Segoe UI, Roboto, Arial';
          ctx.fillStyle = '#000';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';

          meta.data.forEach((arc, i) => {
            const v = Number(data[i] || 0);
            if (v <= 0) return;

            const p = (v / sum) * 100;
            // si el pedazo es muy pequeño, no ponemos texto para que no se ensucie
            if (p < 4) return;

            const pos = arc.tooltipPosition();
            ctx.fillText(p.toFixed(1) + ' %', pos.x, pos.y);
          });

          ctx.restore();
        }
      };

      // 1) PIE: marcaron vs pendientes
      const ctx1 = document.getElementById('chartMarcacionHoy');
      if (ctx1) {
        new Chart(ctx1, {
          type: 'pie',
          data: {
            labels: ['Marcaron hoy', 'Pendientes'],
            datasets: [{
              data: [marcaron, pendientes]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'bottom' },
              tooltip: {
                callbacks: {
                  label: (c) => {
                    const v = c.raw ?? 0;
                    const base = total || 1;
                    const p = ((v / base) * 100).toFixed(1);
                    return ` ${c.label}: ${v} (${p}%)`;
                  }
                }
              }
            }
          },
          plugins: [piePercentLabels]
        });
      }

      // 2) Barras horizontales: incidencias
      const ctx2 = document.getElementById('chartIncidenciasHoy');
      if (ctx2) {
        new Chart(ctx2, {
          type: 'bar',
          data: {
            labels: ['Llegaron tarde', 'Salidas anticipadas', 'Jornada completa puntual'],
            datasets: [{
              label: 'Empleados (hoy)',
              data: [tarde, anticipada, puntual]
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: { label: (ctx) => ` ${ctx.raw} empleados` }
              }
            },
            scales: {
              x: { beginAtZero: true, ticks: { precision: 0 } },
              y: { ticks: { font: { weight: '700' } } }
            }
          }
        });
      }
    })();
  </script>
</div>

@endsection
