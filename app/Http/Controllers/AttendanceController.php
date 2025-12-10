<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\WorkSchedule;


class AttendanceController extends Controller
{
    /**
     * LISTADO GENERAL POR DÍA (usa attendance_records)
     * /asistencia?date=YYYY-MM-DD
     */
    public function index(Request $request)
    {
        // Fecha seleccionada (zona horaria Lima) o hoy
        $date = $request->input('date');
        $date = $date
            ? Carbon::parse($date, 'America/Lima')->toDateString()
            : Carbon::now('America/Lima')->toDateString();

        // Leemos attendance_records
        $rows = AttendanceRecord::with(['employee:id,employee_no,name'])
            ->whereDate('date', $date)
            ->orderBy('employee_no')
            ->get([
                'id','employee_no','date',
                'entry_time','exit_time',
                'entry_window_start','entry_window_end',
                'exit_window_start','exit_window_end',
                'created_at',
            ]);

        return view('attendance.index', [
            'date' => $date,
            'rows' => $rows,
        ]);
    }

    /**
     * Analiza un día concreto con:
     * - horario (entry_time, exit_time, tolerancias)
     * - eventos crudos de la Hikvision (AttendanceEvent) para esa fecha
     *
     * Devuelve:
     * [entryMark, exitMark, obsEntrada, obsSalida, estadoEntrada, estadoSalida]
     */
private function analyzeDay(
    \Illuminate\Support\Carbon $date,
    \App\Models\WorkSchedule $schedule,
    \Illuminate\Support\Collection $events
): array {
    $now = Carbon::now('America/Lima');

    // Si es futuro → nunca marcamos nada
    if ($date->isFuture()) {
        return [null, null, null, null, null, null];
    }

    // Horas del horario
    $entryAt = Carbon::parse($date->toDateString() . ' ' . $schedule->entry_time, 'America/Lima');
    $exitAt  = Carbon::parse($date->toDateString() . ' ' . $schedule->exit_time,  'America/Lima');

    // Tolerancias
    $entryMinus = (int) $schedule->entry_minus;
    $entryPlus  = (int) $schedule->entry_plus;
    $exitMinus  = (int) $schedule->exit_minus;
    $exitPlus   = (int) $schedule->exit_plus;

    // Ventanas
    $entryWindowStart = $entryAt->copy()->subMinutes($entryMinus);
    $entryWindowEnd   = $entryAt->copy()->addMinutes($entryPlus);

    $exitWindowStart  = $exitAt->copy()->subMinutes($exitMinus);
    $exitWindowEnd    = $exitAt->copy()->addMinutes($exitPlus);

    // Ordenar eventos
    $events = $events->sortBy('event_time');

    /*
     * ENTRADA
     */
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
            // Pendiente
            $estadoEntrada = null;
            $obsEntrada    = null;
        } else {
            // Falta de entrada
            $estadoEntrada = '❌ Falta';
            $obsEntrada    = null;
        }
    } else {
        // Puntual = hasta 59 segundos después
        $onTimeLimit = $entryAt->copy()->addMinute()->subSecond();

        if ($entryMark->lte($onTimeLimit)) {
            $estadoEntrada = '✅ Asistió';
            $obsEntrada    = null;
        } elseif ($entryMark->lte($entryWindowEnd)) {
            $estadoEntrada = '⚠️ Tardanza';
        } else {
            $estadoEntrada = '❌ Falta';
            $obsEntrada    = 'Entrada marcó a las ' . $entryMark->format('H:i') . ' (fuera de tolerancia)';
        }
    }

    /*
     * SALIDA
     */
    $exitCandidates = $events->filter(function ($ev) use ($exitWindowStart, $exitWindowEnd) {
        $t = Carbon::parse($ev->event_time, 'America/Lima');
        return $t->between($exitWindowStart, $exitWindowEnd);
    });

    $exitMark = $exitCandidates->isNotEmpty()
        ? Carbon::parse($exitCandidates->last()->event_time, 'America/Lima')
        : null;

    $estadoSalida = null;
    $obsSalida    = null;

    if (!$exitMark) {
        if ($date->isSameDay($now) && $now->lt($exitWindowEnd)) {
            // Pendiente
            $estadoSalida = null;
            $obsSalida    = null;
        } else {
            // Falta de salida
            $estadoSalida = '❌ Falta';
            $obsSalida    = null;
        }
    } else {
        if ($exitMark->between($exitWindowStart, $exitWindowEnd)) {
            if ($exitMark->lt($exitAt)) {
                $estadoSalida = '⚠️ Salida anticipada';
            } else {
                $estadoSalida = '✅ Asistió';
            }
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

    /**
     * HISTORIAL POR EMPLEADO (pantalla que siempre usas)
     * /asistencia/{employee_no}
     */
public function byEmployee(string $employeeNo)
{
    // 1) Obtener empleado
    $employee = Employee::where('employee_no', $employeeNo)->firstOrFail();

    // 2) Rango de fechas (30 días atrás y 30 adelante)
    $today     = Carbon::now('America/Lima')->startOfDay();
    $baseStart = $today->copy()->subDays(30)->startOfDay();
    $end       = $today->copy()->addDays(30)->endOfDay();

    // 3) Horarios del empleado
    $schedules = WorkSchedule::where('employee_no', $employeeNo)
        ->orderBy('start_date')
        ->orderBy('created_at')
        ->get();

    if ($schedules->isEmpty()) {
        return view('attendance.by_employee', [
            'employee' => $employee,
            'rows'     => [],
        ]);
    }

    // 4) Desde cuándo mostrar
    // Mostrar SIEMPRE los últimos 30 días, independientemente de cuándo se crearon los horarios
    $start = $baseStart->copy();
    
    // 5) Eventos de asistencia
    $events = AttendanceEvent::where('employee_no', $employeeNo)
        ->whereBetween('event_time', [$start, $end])
        ->orderBy('event_time')
        ->get()
        ->groupBy(function ($e) {
            return Carbon::parse($e->event_time)->toDateString();
        });

    // 6) Generar filas día por día
    $rows   = [];
    $cursor = $start->copy();

    // Tarifa del empleado
    $hourlyRate   = (float) ($employee->hourly_rate ?? 0);     // S/ por hora
    $payPerMinute = $hourlyRate > 0 ? $hourlyRate / 60.0 : 0;  // S/ por minuto

    while ($cursor <= $end) {
        $dateStr = $cursor->toDateString();
        $dow     = $cursor->isoWeekday(); // 1=lunes ... 7=domingo

        // Horarios activos ese día
        $activeSchedules = $schedules->filter(function ($sch) use ($cursor, $dow) {
            $d = $cursor->toDateString();

            $startDate = $sch->start_date ? $sch->start_date->toDateString() : null;
            $endDate   = $sch->end_date   ? $sch->end_date->toDateString()   : null;

            if ($startDate && $d < $startDate) return false;
            if ($endDate   && $d > $endDate)   return false;

            $workDays = is_array($sch->work_days) ? $sch->work_days : [];
            if (!in_array($dow, $workDays)) return false;

            return true;
        });

        if ($activeSchedules->isEmpty()) {
            $cursor->addDay();
            continue;
        }

        $dayEvents = $events[$dateStr] ?? collect();

        // ---------- 1er PASO: solo construir los turnos ----------
        $turnos = [];

        foreach ($activeSchedules as $sch) {
            $entryScheduled = $sch->entry_time;
            $exitScheduled  = $sch->exit_time;

            $entryMark = $exitMark = $obsEntrada = $obsSalida = $estadoEntrada = $estadoSalida = null;

            if (!$cursor->isFuture()) {
                [
                    $entryMark,
                    $exitMark,
                    $obsEntrada,
                    $obsSalida,
                    $estadoEntrada,
                    $estadoSalida,
                ] = $this->analyzeDay($cursor, $sch, $dayEvents);
            }

            $turnos[] = [
                'entry_scheduled' => $entryScheduled,
                'exit_scheduled'  => $exitScheduled,
                'entry_mark'      => $entryMark,
                'exit_mark'       => $exitMark,
                'obs_entrada'     => $obsEntrada,
                'obs_salida'      => $obsSalida,
                'estado_entrada'  => $estadoEntrada,
                'estado_salida'   => $estadoSalida,
            ];
        }

        // ---------- 2º PASO: calcular pagos y descuentos ----------
        $totalBasePay      = 0.0;
        $totalDailyPay     = 0.0;
        $totalMinutesLate  = 0;
        $totalMinutesEarly = 0;
        $totalDiscount     = 0.0;

        if ($hourlyRate > 0) {
            foreach ($turnos as $t) {
                // 1) Validar que el turno tenga ambas marcas y no sea "Falta"
                if (
                    !($t['entry_mark'] instanceof \Carbon\Carbon) ||
                    !($t['exit_mark']  instanceof \Carbon\Carbon) ||
                    $t['estado_entrada'] === '❌ Falta' ||
                    $t['estado_salida']  === '❌ Falta'
                ) {
                    continue; // este turno no se paga
                }

                // 2) Horas programadas del turno
                $entryAt = Carbon::parse(
                    $cursor->toDateString() . ' ' . $t['entry_scheduled'],
                    'America/Lima'
                );
                $exitAt  = Carbon::parse(
                    $cursor->toDateString() . ' ' . $t['exit_scheduled'],
                    'America/Lima'
                );

                // Duración del turno (en minutos)
                $durationMinutes = max(0, $entryAt->diffInMinutes($exitAt));
                if ($durationMinutes <= 0) {
                    continue;
                }

                // Pago base del turno = horas * tarifa/hora
                $baseTurnPay = ($durationMinutes / 60.0) * $hourlyRate;

                // 3) Minutos de tardanza (entrada DESPUÉS de la hora programada)
                // IMPORTANTE: Solo contar minutos COMPLETOS, ignorar segundos
                $minutesLate = 0;
                if ($t['entry_mark']->gt($entryAt)) {
                    // Truncar segundos antes de calcular diferencia
                    $entryAtNoSec = $entryAt->copy()->second(0);
                    $entryMarkNoSec = $t['entry_mark']->copy()->second(0);
                    $minutesLate = $entryAtNoSec->diffInMinutes($entryMarkNoSec, false);
                    $minutesLate = max(0, $minutesLate); // solo positivos
                }

                // 4) Minutos de salida anticipada (salida ANTES de la hora programada)
                // IMPORTANTE: Solo contar minutos COMPLETOS, ignorar segundos
                $minutesEarly = 0;
                if ($t['exit_mark']->lt($exitAt)) {
                    // Truncar segundos antes de calcular diferencia
                    $exitMarkNoSec = $t['exit_mark']->copy()->second(0);
                    $exitAtNoSec = $exitAt->copy()->second(0);
                    $minutesEarly = $exitMarkNoSec->diffInMinutes($exitAtNoSec, false);
                    $minutesEarly = max(0, $minutesEarly); // solo positivos
                }

                // 5) Descuento del turno
                $discountTurn = ($minutesLate + $minutesEarly) * $payPerMinute;
                $payTurn      = max(0, $baseTurnPay - $discountTurn);

                // 6) Acumular totales del día
                $totalBasePay      += $baseTurnPay;
                $totalDailyPay     += $payTurn;
                $totalMinutesLate  += $minutesLate;
                $totalMinutesEarly += $minutesEarly;
                $totalDiscount     += $discountTurn;
            }
        }


        // ---- Resultado final del día ----
        $payAmount = null;
        $payInfo   = null;

        if ($hourlyRate > 0 && $totalBasePay > 0) {
            $totalBasePay      = round($totalBasePay, 2);
            $totalDailyPay     = round($totalDailyPay, 2);
            $totalDiscount     = round($totalDiscount, 2);
            $totalMinutesLate  = (int) $totalMinutesLate;
            $totalMinutesEarly = (int) $totalMinutesEarly;

            $payAmount = $totalDailyPay;

            if ($totalMinutesLate > 0 || $totalMinutesEarly > 0) {
                $payInfo = sprintf(
                    'Base día: S/ %.2f. Se descontó S/ %.2f por %d min de tardanza y %d min de salida anticipada.',
                    $totalBasePay,
                    $totalDiscount,
                    $totalMinutesLate,
                    $totalMinutesEarly
                );
            } else {
                $payInfo = sprintf(
                    'Base día: S/ %.2f. Sin tardanzas ni salidas anticipadas.',
                    $totalBasePay
                );
            }
        }

        // Guardar fila del día
        $rows[] = [
            'date'       => $cursor->copy(),
            'day_name'   => $cursor->locale('es')->isoFormat('dddd'),
            'turnos'     => $turnos,
            'pay_amount' => $payAmount,
            'pay_info'   => $payInfo,
        ];

        $cursor->addDay();
    }

    return view('attendance.by_employee', [
        'employee' => $employee,
        'rows'     => $rows,
    ]);
}


    /**
     * Estos métodos de markEntry/markExit los dejo tal cual los tenías,
     * por si en algún momento quieres usarlos con otra integración.
     * NO afectan la pantalla de historial por empleado (que usa AttendanceEvent).
     */

    public function markEntry(Request $request)
    {
        $employee   = Employee::findOrFail($request->employee_no);
        $entry_time = Carbon::parse($request->entry_time);
        $now        = Carbon::now('America/Lima');

        $valid_entry_time = $now->copy()->subMinutes(15);
        $valid_exit_time  = $now->copy()->addMinutes(15);

        Log::info('Guardando entrada manual', [
            'employee_no' => $employee->id,
            'entry_time'  => $entry_time->toTimeString(),
        ]);

        if ($entry_time->between($valid_entry_time, $valid_exit_time)) {
            AttendanceRecord::create([
                'employee_no' => $employee->id,
                'date'        => $now->toDateString(),
                'entry_time'  => $entry_time->toTimeString(),
                'exit_time'   => null,
            ]);

            return response()->json(['message' => 'Entrada registrada correctamente.']);
        }

        return response()->json(['message' => 'Hora de entrada fuera de rango permitido.'], 400);
    }

    public function markExit(Request $request)
    {
        $attendanceRecord = AttendanceRecord::where('employee_no', $request->employee_no)
            ->where('date', Carbon::today('America/Lima')->toDateString())
            ->whereNull('exit_time')
            ->first();

        if (!$attendanceRecord) {
            return response()->json(['message' => 'No se encontró entrada registrada.'], 400);
        }

        $exit_time = Carbon::parse($request->exit_time);
        $now       = Carbon::now('America/Lima');

        $valid_exit_time  = $now->copy()->subMinutes(10);
        $valid_entry_time = $now->copy()->addMinutes(10);

        Log::info('Guardando salida manual', [
            'employee_no' => $attendanceRecord->employee_no,
            'exit_time'   => $exit_time->toTimeString(),
        ]);

        if ($exit_time->between($valid_exit_time, $valid_entry_time)) {
            $attendanceRecord->update(['exit_time' => $exit_time->toTimeString()]);

            return response()->json(['message' => 'Salida registrada correctamente.']);
        }

        return response()->json(['message' => 'Hora de salida fuera de rango permitido.'], 400);
    }

    public function showAttendanceByEmployee($employee_no, $date)
    {
        $employee = Employee::findOrFail($employee_no);

        $attendanceRecords = AttendanceRecord::where('employee_no', $employee_no)
            ->whereDate('date', $date)
            ->get();

        // dd($attendanceRecords); // si luego quieres depurar

        return view('attendance.by_employee', [
            'employee'          => $employee,
            'attendanceRecords' => $attendanceRecords,
            'date'              => $date,
        ]);
    }

    public function allRecords(Request $request)
    {
        $records = AttendanceRecord::with(['employee:id,employee_no,name'])
            ->orderByDesc('date')
            ->paginate(50);

        return view('attendance.all-records', [
            'records' => $records,
        ]);
    }
}
