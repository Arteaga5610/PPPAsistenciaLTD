@extends('layouts.app')

@section('content')
<style>
  .dashboard-page {
    padding: 30px;
    height: calc(100vh - 60px);
    display: flex;
    flex-direction: column;
  }
  
  /* Tarjetas de estadísticas */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }
  
  .stat-card {
    background: white;
    padding: 30px 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    flex: 1;
  }
  
  .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
  }
  
  .stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    flex-shrink: 0;
  }
  
  .stat-icon.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .stat-icon.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
  .stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .stat-icon.purple { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
  .stat-icon.red { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); }
  
  .stat-content {
    flex: 1;
  }
  
  .stat-content h3 {
    margin: 0;
    font-size: 38px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
  }
  
  .stat-content p {
    margin: 10px 0 0 0;
    font-size: 15px;
    color: #7f8c8d;
    font-weight: 500;
  }
  
  /* Gráficas y contenido */
  .dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
  }
  
  .dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
  }
  
  .card-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .card-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
  }
  
  .card-body {
    padding: 25px;
  }
  
  /* Gráfica simple */
  .chart-container {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 200px;
    gap: 8px;
  }
  
  .chart-bar {
    flex: 1;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px 4px 0 0;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
    min-height: 20px;
    transition: all 0.3s ease;
  }
  
  .chart-bar:hover {
    opacity: 0.8;
    transform: scaleY(1.05);
  }
  
  .chart-bar-value {
    position: absolute;
    top: -25px;
    font-size: 12px;
    font-weight: 600;
    color: #2c3e50;
  }
  
  .chart-bar-label {
    margin-top: 8px;
    font-size: 11px;
    color: #7f8c8d;
    text-align: center;
  }
  
  /* Lista de eventos recientes */
  .event-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  
  .event-item {
    padding: 15px 0;
    border-bottom: 1px solid #f1f3f5;
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .event-item:last-child {
    border-bottom: none;
  }
  
  .event-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #e7f5ff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
  }
  
  .event-details {
    flex: 1;
  }
  
  .event-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
    margin: 0 0 4px 0;
  }
  
  .event-time {
    font-size: 12px;
    color: #7f8c8d;
    margin: 0;
  }
  
  .event-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #d1ecf1;
    color: #0c5460;
  }
  
  /* Lista de empleados recientes */
  .employee-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  
  .employee-item {
    padding: 12px 0;
    border-bottom: 1px solid #f1f3f5;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  
  .employee-item:last-child {
    border-bottom: none;
  }
  
  .employee-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
  }
  
  .employee-info {
    flex: 1;
  }
  
  .employee-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 13px;
    margin: 0 0 3px 0;
  }
  
  .employee-code {
    font-size: 11px;
    color: #7f8c8d;
    font-family: 'Courier New', monospace;
    margin: 0;
  }
  
  /* Mensaje vacío */
  .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #7f8c8d;
  }
  
  .empty-state-icon {
    font-size: 48px;
    margin-bottom: 10px;
    opacity: 0.5;
  }
  
  @media (max-width: 1024px) {
    .dashboard-grid {
      grid-template-columns: 1fr;
    }
  }
  
  @media (max-width: 768px) {
    .stats-grid {
      grid-template-columns: 1fr;
    }
  }
  
  /* ========== PODIO TOP 3 ========== */
  .podium-section {
    margin: 0;
    display: flex;
    flex-direction: column;
    height: 100%;
  }
  
  .podium-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-shrink: 0;
  }
  
  .podium-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
  }
  
  .podium-title i {
    color: #ffd700;
    font-size: 24px;
  }
  
  .podium-period {
    font-size: 13px;
    color: #7f8c8d;
    background: #f8f9fa;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 500;
  }
  
  .podium-container {
    background: white;
    border-radius: 16px;
    padding: 30px 20px 20px 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative;
    overflow: visible;
    flex: 1;
    display: flex;
    align-items: flex-end;
  }
  
  .podium-container::before {
    display: none;
  }
  
  .podium-wrapper {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 20px;
    flex: 1;
    position: relative;
    width: 100%;
    padding-bottom: 10px;
  }
  
  /* Base dorada compartida */
  .podium-wrapper::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 580px;
    height: 6px;
    background: linear-gradient(90deg, 
      transparent 0%,
      rgba(218,165,32,0.3) 10%,
      rgba(255,215,0,0.6) 30%,
      rgba(255,235,100,0.8) 50%,
      rgba(255,215,0,0.6) 70%,
      rgba(218,165,32,0.3) 90%,
      transparent 100%
    );
    box-shadow: 0 2px 12px rgba(255,215,0,0.4);
    border-radius: 3px;
  }
  
  .podium-place {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    padding: 20px 18px 22px 18px;
    width: 170px;
    border-radius: 8px 8px 0 0;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
      0 -8px 25px rgba(0,0,0,0.4),
      inset 0 2px 12px rgba(255,255,255,0.25),
      inset 0 -8px 15px rgba(0,0,0,0.15);
  }
  
  .podium-place:hover {
    transform: translateY(-10px);
  }
  
  /* Posición 1 - Oro (Centro, más alto) */
  .podium-place.first {
    background: 
      repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(255,215,0,0.04) 8px, rgba(255,215,0,0.04) 16px),
      linear-gradient(180deg, #fffef5 0%, #fff9e0 25%, #ffeead 60%, #ffd966 100%);
    border: 3px solid rgba(218,165,32,0.5);
    height: 240px;
    order: 2;
    box-shadow: 
      0 -12px 35px rgba(255,215,0,0.5),
      0 0 25px rgba(255,215,0,0.3),
      inset 0 3px 15px rgba(255,255,255,0.4),
      inset 0 -12px 20px rgba(218,165,32,0.15);
  }
  
  /* Posición 2 - Plata (Izquierda) */
  .podium-place.second {
    background: 
      repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(192,192,192,0.04) 8px, rgba(192,192,192,0.04) 16px),
      linear-gradient(180deg, #f8f8f8 0%, #ececec 25%, #d9d9d9 60%, #bebebe 100%);
    border: 3px solid rgba(169,169,169,0.5);
    height: 200px;
    order: 1;
    box-shadow: 
      0 -12px 35px rgba(192,192,192,0.5),
      0 0 25px rgba(192,192,192,0.3),
      inset 0 3px 15px rgba(255,255,255,0.4),
      inset 0 -12px 20px rgba(169,169,169,0.15);
  }
  
  /* Posición 3 - Bronce (Derecha) */
  .podium-place.third {
    background: 
      repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(205,127,50,0.04) 8px, rgba(205,127,50,0.04) 16px),
      linear-gradient(180deg, #faf5f0 0%, #f5e6d3 25%, #e6c8a0 60%, #cd7f32 100%);
    border: 3px solid rgba(184,115,51,0.5);
    height: 170px;
    order: 3;
    box-shadow: 
      0 -12px 35px rgba(205,127,50,0.5),
      0 0 25px rgba(205,127,50,0.3),
      inset 0 3px 15px rgba(255,255,255,0.4),
      inset 0 -12px 20px rgba(184,115,51,0.15);
  }
  
  /* Banda superior ornamental */
  .podium-place::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 12px;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    box-shadow: inset 0 1px 3px rgba(255,255,255,0.4);
  }
  
  .podium-place.first::before {
    background: linear-gradient(90deg, 
      #d4af37 0%, #f4e36b 20%, #ffd700 40%, #ffe55c 50%,
      #ffd700 60%, #f4e36b 80%, #d4af37 100%
    );
    box-shadow: 
      0 0 15px rgba(255,215,0,0.6),
      inset 0 1px 4px rgba(255,255,255,0.5);
  }
  
  .podium-place.second::before {
    background: linear-gradient(90deg, 
      #a8a8a8 0%, #d0d0d0 20%, #e8e8e8 40%, #f5f5f5 50%,
      #e8e8e8 60%, #d0d0d0 80%, #a8a8a8 100%
    );
    box-shadow: 
      0 0 15px rgba(192,192,192,0.6),
      inset 0 1px 4px rgba(255,255,255,0.5);
  }
  
  .podium-place.third::before {
    background: linear-gradient(90deg, 
      #b87333 0%, #d4976b 20%, #e69c5c 40%, #f0b878 50%,
      #e69c5c 60%, #d4976b 80%, #b87333 100%
    );
    box-shadow: 
      0 0 15px rgba(205,127,50,0.6),
      inset 0 1px 4px rgba(255,255,255,0.5);
  }
  
  /* Avatar circular */
  .podium-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    position: relative;
    z-index: 2;
    border: 3px solid rgba(255,255,255,0.3);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
  }
  
  .podium-place.first .podium-avatar {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
    width: 80px;
    height: 80px;
    border-width: 4px;
    box-shadow: 
      0 0 30px rgba(255,215,0,0.8),
      0 8px 25px rgba(255,215,0,0.5);
  }
  
  .podium-place.second .podium-avatar {
    background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 50%, #c0c0c0 100%);
    width: 74px;
    height: 74px;
    box-shadow: 
      0 0 30px rgba(192,192,192,0.8),
      0 8px 25px rgba(192,192,192,0.5);
  }
  
  .podium-place.third .podium-avatar {
    background: linear-gradient(135deg, #cd7f32 0%, #e69c5c 50%, #cd7f32 100%);
    width: 72px;
    height: 72px;
    box-shadow: 
      0 0 30px rgba(205,127,50,0.8),
      0 8px 25px rgba(205,127,50,0.5);
  }
  
  .podium-avatar i {
    font-size: 32px;
    color: #2a2d35;
  }
  
  .podium-place.first .podium-avatar i {
    font-size: 38px;
  }
  
  .podium-place.second .podium-avatar i {
    font-size: 35px;
  }
  
  .podium-place.third .podium-avatar i {
    font-size: 34px;
  }
  
  /* Corona para el primer lugar - ENCIMA del podio */
  .podium-place.first::after {
    content: '👑';
    position: absolute;
    top: -70px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 50px;
    filter: drop-shadow(0 0 20px rgba(255,215,0,1));
    animation: float 3s ease-in-out infinite;
    z-index: 10;
  }
  
  /* Medalla de plata para el segundo lugar - ENCIMA del podio */
  .podium-place.second::after {
    content: '🥈';
    position: absolute;
    top: -65px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 45px;
    filter: drop-shadow(0 0 20px rgba(192,192,192,1));
    animation: float 3s ease-in-out infinite 0.3s;
    z-index: 10;
  }
  
  /* Medalla de bronce para el tercer lugar - ENCIMA del podio */
  .podium-place.third::after {
    content: '🥉';
    position: absolute;
    top: -60px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 42px;
    filter: drop-shadow(0 0 20px rgba(205,127,50,1));
    animation: float 3s ease-in-out infinite 0.6s;
    z-index: 10;
  }
  
  @keyframes float {
    0%, 100% { transform: translateX(-50%) translateY(0px); }
    50% { transform: translateX(-50%) translateY(-5px); }
  }
  
  /* Información del empleado */
  .podium-info {
    text-align: center;
    margin-bottom: 10px;
    z-index: 2;
    position: relative;
  }
  
  .podium-name {
    font-weight: 700;
    font-size: 14px;
    color: #2a2d35;
    margin-bottom: 4px;
    text-shadow: 0 1px 2px rgba(255,255,255,0.3);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
  }
  
  .podium-place.first .podium-name {
    font-size: 16px;
    color: #8b6914;
  }
  
  .podium-place.second .podium-name {
    font-size: 15px;
    color: #5a5a5a;
  }
  
  .podium-code {
    font-size: 11px;
    color: rgba(42,45,53,0.6);
    font-family: 'Courier New', monospace;
    font-weight: 600;
  }
  
  /* Número de posición */
  .podium-number {
    display: none;
  }
  
  /* Puntos */
  .podium-points {
    font-size: 12px;
    color: rgba(42,45,53,0.7);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 2;
    position: relative;
  }
  
  .podium-points-value {
    font-size: 20px;
    font-weight: 800;
    display: block;
    margin-top: 2px;
    color: #2a2d35;
  }
  
  .podium-place.first .podium-points-value {
    color: #8b6914;
    font-size: 22px;
  }
  
  .podium-place.second .podium-points-value {
    color: #5a5a5a;
    font-size: 21px;
  }
  
  .podium-place.third .podium-points-value {
    color: #8b5a2b;
    font-size: 20px;
  }
  
  /* Mensaje cuando no hay datos */
  .podium-empty {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
  }
  
  .podium-empty i {
    font-size: 60px;
    margin-bottom: 15px;
    opacity: 0.3;
  }
  
  .podium-empty p {
    margin: 0;
    font-size: 15px;
  }
</style>

<div class="dashboard-page">
  
  <!-- Layout principal: 3 columnas (Stats izquierda | Podio centro | Stats derecha) -->
  <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 20px; flex: 1; align-items: stretch;">
    
    <!-- COLUMNA IZQUIERDA: Estadísticas principales -->
    <div style="display: flex; flex-direction: column; gap: 15px; justify-content: space-between;">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-content">
          <h3>{{ $totalEmployees }}</h3>
          <p>Total de Empleados</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-fingerprint"></i></div>
        <div class="stat-content">
          <h3>{{ $employeesWithFp }}</h3>
          <p>Con Huella Digital</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-user"></i></div>
        <div class="stat-content">
          <h3>{{ $employeesWithFace }}</h3>
          <p>Con Reconocimiento Facial</p>
        </div>
      </div>
      
      <a href="{{ route('asistencias.hoy') }}" class="stat-card" style="text-decoration: none; cursor: pointer; transition: transform 0.2s ease;">
        <div class="stat-icon blue" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"><i class="fas fa-user-check"></i></div>
        <div class="stat-content">
          <h3>{{ $scheduleCount['with_marks'] }}/{{ $scheduleCount['total'] }}</h3>
          <p>Asistieron Hoy</p>
        </div>
      </a>
    </div>

    <!-- COLUMNA CENTRO: Podio TOP 3 Empleados Más Puntuales -->
    <!-- COLUMNA CENTRO: Podio TOP 3 Empleados Más Puntuales -->
    <div class="podium-section" style="margin: 0;">
      <div class="podium-header">
        <h2 class="podium-title">
          <i class="fas fa-trophy"></i>
          TOP 3 Empleados Más Puntuales
        </h2>
        <span class="podium-period">
          <i class="far fa-calendar-alt"></i> Últimos 30 días
        </span>
      </div>

      <div class="podium-container">
        @if(count($topPunctual) > 0)
          <div class="podium-wrapper">
            @foreach($topPunctual as $index => $rank)
              @php
                $position = $index + 1;
                $positionClass = '';
                if ($position === 1) $positionClass = 'first';
                elseif ($position === 2) $positionClass = 'second';
                elseif ($position === 3) $positionClass = 'third';
              @endphp
              
              <div class="podium-place {{ $positionClass }}">
                <div class="podium-avatar">
                  <i class="fas fa-user"></i>
                </div>
                
                <div class="podium-info">
                  <div class="podium-name">{{ $rank['employee']->name }}</div>
                  <div class="podium-code">{{ $rank['employee']->employee_no }}</div>
                </div>
                
                <div class="podium-number">{{ $position }}</div>
                
                <div class="podium-points">
                  <span class="podium-points-value">{{ $rank['points'] }}</span>
                  puntos
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="podium-empty">
            <i class="fas fa-chart-bar"></i>
            <p>No hay datos suficientes para generar el ranking.</p>
            <p style="margin-top: 8px; font-size: 13px; opacity: 0.7;">Los empleados deben tener asistencias con entrada y salida marcadas.</p>
          </div>
        @endif
      </div>
    </div>

    <!-- COLUMNA DERECHA: Estadísticas de asistencia del día -->
    <div style="display: flex; flex-direction: column; gap: 15px; justify-content: space-between;">
      <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-id-card"></i></div>
        <div class="stat-content">
          <h3>{{ $employeesWithCard }}</h3>
          <p>Con Tarjeta</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-clock"></i></div>
        <div class="stat-content">
          <h3>{{ $lateArrivals }}</h3>
          <p>Llegaron Tarde</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-door-open"></i></div>
        <div class="stat-content">
          <h3>{{ $earlyExits }}</h3>
          <p>Salidas Anticipadas</p>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-content">
          <h3>{{ $punctualEmployees }}</h3>
          <p>Jornada Completa Puntual</p>
        </div>
      </div>
    </div>

  </div>
  <!-- Fin del layout de 3 columnas -->

</div>
@endsection
