<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\AttendanceEvent;
use App\Models\WorkSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas generales
        $totalEmployees = Employee::count();
        $employeesWithFp = Employee::where('has_fp', 1)->count();
        $employeesWithFace = Employee::where('has_face', 1)->count();
        $employeesWithCard = Employee::where('has_card', 1)->count();
        
        // Estadísticas de asistencia del día (hoy)
        $today = Carbon::today('America/Lima');
        $now = Carbon::now('America/Lima');
        
        // Obtenemos todos los empleados activos
        $employees = Employee::all();
        
        $lateArrivals = 0;
        $earlyExits = 0;
        $punctualEmployees = 0;
        
        foreach ($employees as $employee) {
            // Obtener TODOS los horarios activos para hoy
            $schedules = WorkSchedule::where('employee_no', $employee->employee_no)
                ->whereDate('start_date', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                          ->orWhereDate('end_date', '>=', $today);
                })
                ->get();
            
            if ($schedules->isEmpty()) {
                continue;
            }
            
            // Filtrar horarios activos para hoy según work_days
            $dayOfWeek = $today->dayOfWeekIso;
            $activeSchedules = $schedules->filter(function ($sch) use ($dayOfWeek) {
                $workDays = is_array($sch->work_days) ? $sch->work_days : [];
                return in_array($dayOfWeek, $workDays);
            });
            
            if ($activeSchedules->isEmpty()) {
                continue;
            }
            
            // Obtener eventos del empleado para hoy
            $events = AttendanceEvent::where('employee_no', $employee->employee_no)
                ->whereDate('event_time', $today)
                ->orderBy('event_time')
                ->get();
            
            if ($events->isEmpty()) {
                continue;
            }
            
            $employeeHasLate = false;
            $employeeHasEarlyExit = false;
            $employeeHasCompleteTurns = false;
            $allTurnsPunctual = true;
            
            // Procesar cada turno del día
            foreach ($activeSchedules as $schedule) {
                // Usar analyzeDay para obtener el estado del turno
                [
                    $entryMark,
                    $exitMark,
                    $obsEntrada,
                    $obsSalida,
                    $estadoEntrada,
                    $estadoSalida,
                ] = $this->analyzeDay($today, $schedule, $events);
                
                // Si tiene tardanza en algún turno
                if ($estadoEntrada === '⚠️ Tardanza') {
                    $employeeHasLate = true;
                }
                
                // Si tiene salida anticipada en algún turno
                if ($estadoSalida === '⚠️ Salida anticipada') {
                    $employeeHasEarlyExit = true;
                }
                
                // Si tiene turno completo (ambas marcas)
                if (($entryMark instanceof \Carbon\Carbon) && ($exitMark instanceof \Carbon\Carbon)) {
                    $employeeHasCompleteTurns = true;
                    
                    // Si el turno NO es puntual (tiene tardanza o salida anticipada)
                    if ($estadoEntrada !== '✅ Asistió' || $estadoSalida !== '✅ Asistió') {
                        $allTurnsPunctual = false;
                    }
                }
            }
            
            // Contar empleado solo UNA VEZ en cada categoría
            if ($employeeHasLate) {
                $lateArrivals++;
            }
            
            if ($employeeHasEarlyExit) {
                $earlyExits++;
            }
            
            // Jornada completa puntual: tiene al menos un turno completo Y todos los turnos son puntuales
            if ($employeeHasCompleteTurns && $allTurnsPunctual) {
                $punctualEmployees++;
            }
        }
        
        // Calcular ranking de puntualidad (últimos 30 días)
        $topPunctual = $this->calculatePunctualityRanking();
        
        // Calcular recuento de marcaciones de hoy
        $scheduleCount = $this->getTodayScheduleCount();
        
        return view('dashboard.index', compact(
            'totalEmployees',
            'employeesWithFp',
            'employeesWithFace',
            'employeesWithCard',
            'lateArrivals',
            'earlyExits',
            'punctualEmployees',
            'topPunctual',
            'scheduleCount'
        ));
    }
    
    /**
     * Vista separada para ver todos los turnos de hoy
     */
    public function todaySchedules()
    {
        $todaySchedules = $this->getTodaySchedulesWithStatus();
        $scheduleCount = $this->getTodayScheduleCount();
        
        return view('asistencias.hoy', compact('todaySchedules', 'scheduleCount'));
    }
    
    /**
     * Calcula cuántos empleados han marcado COMPLETAMENTE su horario hoy (entrada Y salida)
     */
    private function getTodayScheduleCount()
    {
        $today = Carbon::today('America/Lima');
        $dayOfWeek = $today->dayOfWeekIso;
        $employees = Employee::all();
        
        $totalScheduledEmployees = 0;
        $employeesWithCompleteMarks = 0;
        
        foreach ($employees as $employee) {
            // Obtener horarios activos para hoy
            $schedules = WorkSchedule::where('employee_no', $employee->employee_no)
                ->whereDate('start_date', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                          ->orWhereDate('end_date', '>=', $today);
                })
                ->get();
            
            // Filtrar por día de la semana
            $activeSchedules = $schedules->filter(function ($sch) use ($dayOfWeek) {
                $workDays = is_array($sch->work_days) ? $sch->work_days : [];
                return in_array($dayOfWeek, $workDays);
            });
            
            if ($activeSchedules->isEmpty()) {
                continue;
            }
            
            // Este empleado tiene horario para hoy
            $totalScheduledEmployees++;
            
            // Obtener eventos del empleado para hoy
            $events = AttendanceEvent::where('employee_no', $employee->employee_no)
                ->whereDate('event_time', $today)
                ->orderBy('event_time')
                ->get();
            
            if ($events->isEmpty()) {
                continue;
            }
            
            // Verificar si tiene al menos un turno completo (entrada Y salida)
            $hasCompleteTurn = false;
            foreach ($activeSchedules as $schedule) {
                [
                    $entryMark,
                    $exitMark,
                    $obsEntrada,
                    $obsSalida,
                    $estadoEntrada,
                    $estadoSalida,
                ] = $this->analyzeDay($today, $schedule, $events);
                
                // Si tiene ambas marcas en este turno
                if (($entryMark instanceof \Carbon\Carbon) && ($exitMark instanceof \Carbon\Carbon)) {
                    $hasCompleteTurn = true;
                    break;
                }
            }
            
            if ($hasCompleteTurn) {
                $employeesWithCompleteMarks++;
            }
        }
        
        return [
            'total' => $totalScheduledEmployees,
            'with_marks' => $employeesWithCompleteMarks,
        ];
    }
    
    /**
     * Obtiene todos los turnos de hoy con el estado de marcación de cada empleado
     */
    private function getTodaySchedulesWithStatus()
    {
        $today = Carbon::today('America/Lima');
        $dayOfWeek = $today->dayOfWeekIso;
        $employees = Employee::all();
        $schedules = [];
        
        foreach ($employees as $employee) {
            // Obtener horarios activos para hoy
            $employeeSchedules = WorkSchedule::where('employee_no', $employee->employee_no)
                ->whereDate('start_date', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                          ->orWhereDate('end_date', '>=', $today);
                })
                ->get();
            
            // Filtrar por día de la semana
            $activeSchedules = $employeeSchedules->filter(function ($sch) use ($dayOfWeek) {
                $workDays = is_array($sch->work_days) ? $sch->work_days : [];
                return in_array($dayOfWeek, $workDays);
            });
            
            if ($activeSchedules->isEmpty()) {
                continue;
            }
            
            // Obtener eventos del empleado para hoy
            $events = AttendanceEvent::where('employee_no', $employee->employee_no)
                ->whereDate('event_time', $today)
                ->orderBy('event_time')
                ->get();
            
            // Procesar cada turno
            foreach ($activeSchedules as $schedule) {
                [
                    $entryMark,
                    $exitMark,
                    $obsEntrada,
                    $obsSalida,
                    $estadoEntrada,
                    $estadoSalida,
                ] = $this->analyzeDay($today, $schedule, $events);
                
                $schedules[] = [
                    'employee' => $employee,
                    'schedule' => $schedule,
                    'entry_mark' => $entryMark,
                    'exit_mark' => $exitMark,
                    'entry_status' => $estadoEntrada,
                    'exit_status' => $estadoSalida,
                    'entry_time' => $obsEntrada,
                    'exit_time' => $obsSalida,
                ];
            }
        }
        
        // Ordenar por hora de entrada del horario
        usort($schedules, function($a, $b) {
            return strcmp($a['schedule']->entry_time, $b['schedule']->entry_time);
        });
        
        return $schedules;
    }
    
    /**
     * Calcula el ranking de puntualidad de los empleados
     * Basado en los eventos de asistencia (AttendanceEvent) de los últimos 30 días
     * Replica la lógica de la vista de asistencia usando analyzeDay
     */
    private function calculatePunctualityRanking()
    {
        $employees = Employee::all();
        $ranking = [];
        
        // Fecha de inicio: hace 30 días
        $startDate = Carbon::today('America/Lima')->subDays(30);
        $endDate = Carbon::today('America/Lima');
        
        foreach ($employees as $employee) {
            $totalPoints = 0;
            $turnosValidados = 0;
            
            // Obtener eventos de asistencia (marcas crudas) del empleado en el rango
            $events = AttendanceEvent::where('employee_no', $employee->employee_no)
                ->whereBetween('event_time', [$startDate, $endDate->copy()->endOfDay()])
                ->orderBy('event_time')
                ->get()
                ->groupBy(function ($ev) {
                    return Carbon::parse($ev->event_time)->toDateString();
                });
            
            // Obtener horarios activos en el período
            $schedules = WorkSchedule::where('employee_no', $employee->employee_no)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        $q->whereDate('start_date', '<=', $endDate)
                          ->where(function ($q2) use ($startDate) {
                              $q2->whereNull('end_date')
                                 ->orWhereDate('end_date', '>=', $startDate);
                          });
                    });
                })
                ->get();
            
            if ($schedules->isEmpty()) {
                continue;
            }
            
            // Iterar cada día en el rango
            $cursor = $startDate->copy();
            while ($cursor->lte($endDate)) {
                $dateStr = $cursor->toDateString();
                $dow = $cursor->dayOfWeekIso;
                
                // Filtrar horarios activos para este día específico
                $activeSchedules = $schedules->filter(function ($sch) use ($cursor, $dow, $dateStr) {
                    $startDate = $sch->start_date ? $sch->start_date->toDateString() : null;
                    $endDate = $sch->end_date ? $sch->end_date->toDateString() : null;
                    
                    if ($startDate && $dateStr < $startDate) return false;
                    if ($endDate && $dateStr > $endDate) return false;
                    
                    $workDays = is_array($sch->work_days) ? $sch->work_days : [];
                    if (!in_array($dow, $workDays)) return false;
                    
                    return true;
                });
                
                if ($activeSchedules->isEmpty()) {
                    $cursor->addDay();
                    continue;
                }
                
                $dayEvents = $events[$dateStr] ?? collect();
                
                // Procesar cada turno del día
                foreach ($activeSchedules as $schedule) {
                    // Usar analyzeDay para obtener las marcas del turno
                    [
                        $entryMark,
                        $exitMark,
                        $obsEntrada,
                        $obsSalida,
                        $estadoEntrada,
                        $estadoSalida,
                    ] = $this->analyzeDay($cursor, $schedule, $dayEvents);
                    
                    // Log temporal para debug
                    if ($employee->employee_no === 'ETMUJOWR' && $dateStr === '2025-11-27') {
                        Log::info("Turno {$schedule->entry_time}-{$schedule->exit_time}: Entry={$estadoEntrada}, Exit={$estadoSalida}");
                    }
                    
                    // REGLA: Solo contar si tiene AMBAS marcas y no es falta
                    if (!($entryMark instanceof \Carbon\Carbon) || 
                        !($exitMark instanceof \Carbon\Carbon) ||
                        $estadoEntrada === '❌ Falta' ||
                        $estadoSalida === '❌ Falta') {
                        continue;
                    }
                    
                    $turnoPoints = 0;
                    $entryPlusTolerance = $schedule->entry_plus ?? 15;
                    
                    // Tiempos programados
                    $scheduledEntry = Carbon::parse($dateStr . ' ' . $schedule->entry_time, 'America/Lima');
                    $scheduledExit = Carbon::parse($dateStr . ' ' . $schedule->exit_time, 'America/Lima');
                    
                    // ========== PUNTOS DE ENTRADA ==========
                    if ($entryMark->lessThanOrEqualTo($scheduledEntry)) {
                        // Llegó ANTES o PUNTUAL: 3 puntos
                        $turnoPoints += 3;
                    } else {
                        // Llegó DESPUÉS (tarde)
                        $minutesLate = $entryMark->diffInMinutes($scheduledEntry);
                        
                        if ($minutesLate <= $entryPlusTolerance) {
                            // Dentro de tolerancia: 1 punto
                            $turnoPoints += 1;
                        }
                        // Fuera de tolerancia: 0 puntos
                    }
                    
                    // ========== PUNTOS DE SALIDA ==========
                    if ($exitMark->greaterThanOrEqualTo($scheduledExit)) {
                        // Salió DESPUÉS o PUNTUAL: 3 puntos
                        $turnoPoints += 3;
                    } else {
                        // Salió ANTES (anticipado)
                        $minutesEarly = $scheduledExit->diffInMinutes($exitMark);
                        
                        if ($minutesEarly <= 10) {
                            // Dentro de 10 min: 1 punto
                            $turnoPoints += 1;
                        }
                        // Más de 10 min antes: 0 puntos
                    }
                    
                    $totalPoints += $turnoPoints;
                    $turnosValidados++;
                }
                
                $cursor->addDay();
            }
            
            // Solo agregar al ranking si tiene turnos validados
            if ($turnosValidados > 0) {
                $ranking[] = [
                    'employee' => $employee,
                    'points' => $totalPoints,
                    'days' => $turnosValidados,
                    'average' => round($totalPoints / $turnosValidados, 2)
                ];
            }
        }
        
        // Ordenar por puntos totales (descendente) y tomar TOP 3
        usort($ranking, function($a, $b) {
            return $b['points'] <=> $a['points'];
        });
        
        return array_slice($ranking, 0, 3);
    }
    
    /**
     * Analiza un día concreto (copia del método de AttendanceController)
     */
    private function analyzeDay(
        \Illuminate\Support\Carbon $date,
        \App\Models\WorkSchedule $schedule,
        \Illuminate\Support\Collection $events
    ): array {
        $now = Carbon::now('America/Lima');
        
        if ($date->isFuture()) {
            return [null, null, null, null, null, null];
        }
        
        $entryAt = Carbon::parse($date->toDateString() . ' ' . $schedule->entry_time, 'America/Lima');
        $exitAt  = Carbon::parse($date->toDateString() . ' ' . $schedule->exit_time,  'America/Lima');
        
        $entryMinus = (int) ($schedule->entry_minus ?? 0);
        $entryPlus  = (int) ($schedule->entry_plus ?? 0);
        $exitMinus  = (int) ($schedule->exit_minus ?? 0);
        $exitPlus   = (int) ($schedule->exit_plus ?? 0);
        
        $entryWindowStart = $entryAt->copy()->subMinutes($entryMinus);
        $entryWindowEnd   = $entryAt->copy()->addMinutes($entryPlus);
        $exitWindowStart  = $exitAt->copy()->subMinutes($exitMinus);
        $exitWindowEnd    = $exitAt->copy()->addMinutes($exitPlus);
        
        $events = $events->sortBy('event_time');
        
        // ENTRADA
        $entryCandidates = $events->filter(function ($ev) use ($entryWindowStart, $entryWindowEnd) {
            $t = Carbon::parse($ev->event_time, 'America/Lima');
            return $t->between($entryWindowStart, $entryWindowEnd);
        });
        
        $entryMark = $entryCandidates->isNotEmpty()
            ? Carbon::parse($entryCandidates->first()->event_time, 'America/Lima')
            : null;
        
        $estadoEntrada = null;
        $obsEntrada    = null;
        
        if (!$entryMark) {
            if ($date->isSameDay($now) && $now->lt($entryWindowEnd)) {
                $estadoEntrada = null;
                $obsEntrada    = null;
            } else {
                $estadoEntrada = '❌ Falta';
                $obsEntrada    = null;
            }
        } else {
            $onTimeLimit = $entryAt->copy()->addMinute()->subSecond();
            
            if ($entryMark->lte($onTimeLimit)) {
                $estadoEntrada = '✅ Asistió';
                $obsEntrada    = $entryMark->format('H:i');
            } elseif ($entryMark->lte($entryWindowEnd)) {
                $estadoEntrada = '⚠️ Tardanza';
                $obsEntrada    = $entryMark->format('H:i');
            } else {
                $estadoEntrada = '❌ Falta';
                $obsEntrada    = 'Entrada marcó a las ' . $entryMark->format('H:i') . ' (fuera de tolerancia)';
            }
        }
        
        // SALIDA
        $exitCandidates = $events->filter(function ($ev) use ($exitWindowStart, $exitWindowEnd, $entryMark) {
            $t = Carbon::parse($ev->event_time, 'America/Lima');
            // No tomar eventos anteriores a la entrada
            if ($entryMark && $t->lte($entryMark)) return false;
            return $t->between($exitWindowStart, $exitWindowEnd);
        });
        
        $exitMark = $exitCandidates->isNotEmpty()
            ? Carbon::parse($exitCandidates->last()->event_time, 'America/Lima')
            : null;
        
        $estadoSalida = null;
        $obsSalida    = null;
        
        if (!$exitMark) {
            if ($date->isSameDay($now) && $now->lt($exitWindowEnd)) {
                $estadoSalida = null;
                $obsSalida    = null;
            } else {
                $estadoSalida = '❌ Falta';
                $obsSalida    = null;
            }
        } else {
            $earlyLimit = $exitAt->copy()->subMinute()->addSecond();
            
            if ($exitMark->gte($earlyLimit)) {
                $estadoSalida = '✅ Asistió';
                $obsSalida    = $exitMark->format('H:i');
            } elseif ($exitMark->gte($exitWindowStart)) {
                $estadoSalida = '⚠️ Salida anticipada';
                $obsSalida    = $exitMark->format('H:i');
            } else {
                $estadoSalida = '❌ Falta';
                $obsSalida    = 'Salida marcó a las ' . $exitMark->format('H:i') . ' (fuera de tolerancia)';
            }
        }
        
        return [
            $entryMark,
            $exitMark,
            $obsEntrada,
            $obsSalida,
            $estadoEntrada,
            $estadoSalida,
        ];
    }
}
