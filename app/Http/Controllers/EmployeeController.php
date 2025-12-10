<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Services\HikvisionService;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

use App\Models\AttendanceEvent;

use App\Models\WorkSchedule;

use Illuminate\Support\Str;   // <- AÑADE ESTO
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class EmployeeController extends Controller
{
    public function index(Request $request)
{
    $q = $request->input('q', '');
    $credFilter = $request->input('cred', ''); // huella, rostro, tarjeta, ninguna
    $orderBy = $request->input('order', 'id_desc'); // id_desc, name_asc, name_desc, hire_asc, hire_desc

    $employees = Employee::query()
        ->when($q, fn($qq) => $qq->where(function($s) use ($q){
            $s->where('employee_no','like',"%$q%")
              ->orWhere('name','like',"%$q%");
        }))
        ->when($credFilter === 'huella', fn($qq) => $qq->where('has_fp', true))
        ->when($credFilter === 'rostro', fn($qq) => $qq->where('has_face', true))
        ->when($credFilter === 'tarjeta', fn($qq) => $qq->where('has_card', true))
        ->when($credFilter === 'ninguna', fn($qq) => $qq->where(function($s){
            $s->where('has_fp', false)
              ->where('has_face', false)
              ->where('has_card', false);
        }));

    // Aplicar ordenamiento
    switch ($orderBy) {
        case 'name_asc':
            $employees->orderBy('name', 'asc');
            break;
        case 'name_desc':
            $employees->orderBy('name', 'desc');
            break;
        case 'hire_asc':
            $employees->orderByRaw('hire_date IS NULL, hire_date ASC');
            break;
        case 'hire_desc':
            $employees->orderByRaw('hire_date IS NULL, hire_date DESC');
            break;
        default: // id_desc
            $employees->orderBy('id', 'desc');
            break;
    }

    $employees = $employees->paginate(20)->appends([
        'q' => $q,
        'cred' => $credFilter,
        'order' => $orderBy,
    ]);

    // Mapeo employee_no => última fecha en que vimos "huella" o AccessControllerEvent
    $from = now()->subDays(365);

    $map = AttendanceEvent::query()
        ->select('employee_no', DB::raw('MAX(event_time) as last_seen'))
        ->whereIn('employee_no', $employees->pluck('employee_no')->filter())
        ->where('event_time','>=',$from)
        ->where(function($w){
            $w->whereRaw('LOWER(event_type) = ?', ['accesscontrollerevent'])
              ->orWhere('method','fp')
              ->orWhereRaw('LOWER(event_type) LIKE ?', ['%finger%']);
        })
        ->groupBy('employee_no')
        ->pluck('last_seen','employee_no'); // -> ['EZX..' => '2025-10-22 15:42:28', ...]

    // Adjunta el flag a cada item (sin tocar BD)
    foreach ($employees as $e) {
        $e->has_fp_via_events = $e->employee_no && isset($map[$e->employee_no]);
        $e->fp_last_seen      = $map[$e->employee_no] ?? null;
    }

    return view('employees.index', compact('employees','q','credFilter','orderBy'));
}

    public function create(): View
    {
        return view('employees.create');
    }

public function store(EmployeeRequest $request, HikvisionService $hik): RedirectResponse|\Illuminate\Http\JsonResponse
{
    $validFrom = \Carbon\Carbon::today();
    $validTo   = \Carbon\Carbon::create(2035,12,31,23,59,59);

    $employeeNo = 'E'.\Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(7));

    $employee = \App\Models\Employee::create([
        'employee_no' => $employeeNo,
        'name'        => $request->validated('name'),
        'gender'      => $request->validated('gender'),
        'hire_date'   => $request->validated('hire_date') ?? now()->toDateString(), // <---
        'department'  => 'Company',
        'user_type'   => 'normal',
        'door_right'  => 1,
        'valid_from'  => $validFrom,
        'valid_to'    => $validTo,
        'groups'      => [],
        'entry_time'       => $request->validated('entry_time'),
        'exit_time'        => $request->validated('exit_time'),
        'entry_early_min'  => $request->validated('entry_early_min')  ?? 15,
        'entry_late_min'   => $request->validated('entry_late_min')   ?? 15,
        'exit_early_min'   => $request->validated('exit_early_min')   ?? 10,
        'exit_late_min'    => $request->validated('exit_late_min')    ?? 10,

        // 👇 NUEVO: sueldo por hora
        'hourly_rate'      => $request->validated('hourly_rate') ?? 0,
    ]);

    // *** IMPORTANTE: tipos correctos ***
    $payload = [
        'UserInfo' => [
            'employeeNo'     => $employee->employee_no,
            'name'           => $employee->name,
            'userType'       => 'normal',
            'gender'         => $employee->gender,
            'doorRight'      => '1',           // string
            'userVerifyMode' => 'faceOrFpOrCardOrPw',
            'Valid' => [
                'enable'    => true,
                'timeType'  => 'local',
                'beginTime' => $employee->valid_from->copy()->startOfDay()->format('Y-m-d\TH:i:s'),
                'endTime'   => $employee->valid_to->format('Y-m-d\TH:i:s'),
            ],
            'RightPlan' => [[
                'doorNo'         => 1,         // entero
                'planTemplateNo' => '1',       // string (AllWeek)
            ]],
        ]
    ];

    $resp = $hik->upsertUser($payload);

    // Soporte para AJAX (lo usa tu create.blade)
    if ($request->boolean('ajax')) {
        if ($resp['ok']) {
            $employee->update(['synced_at' => now()]);

            return response()->json([
                'ok'          => true,
                'employee_id' => $employee->id,
                // si quieres, aquí puedes devolver también fpStart/fpStatus/fpMark
            ]);
        }
        return response()->json([
            'ok'      => false,
            'message' => $resp['body'] ?? 'error',
        ], 400);
    }

    if ($resp['ok']) {
        $employee->update(['synced_at' => now()]);
        return redirect()->route('employees.index')->with('ok','Empleado sincronizado (SetUp).');
    }

    return redirect()->route('employees.index')
        ->with('ok', 'Guardado local. Sync falló: HTTP '.$resp['status'].' ('.$resp['endpoint'].') Body: '.substr($resp['body'] ?? '',0,200));
}

    public function show(Employee $employee): View
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(EmployeeRequest $request, Employee $employee, HikvisionService $hik): RedirectResponse
{
    // Actualiza datos locales
    $employee->update([
        'name'       => $request->validated('name'),
        'gender'     => $request->validated('gender'),
        'hire_date'  => $request->validated('hire_date') ?? $employee->hire_date, // <---
        'valid_from' => Carbon::today(), // 00:00:00 de hoy
        // 'valid_to'   => Carbon::create(2035,12,31,23,59,59), // si quieres forzarlo también
        'entry_time'       => $request->validated('entry_time'),
        'exit_time'        => $request->validated('exit_time'),
        'entry_early_min'  => $request->validated('entry_early_min')  ?? $employee->entry_early_min,
        'entry_late_min'   => $request->validated('entry_late_min')   ?? $employee->entry_late_min,
        'exit_early_min'   => $request->validated('exit_early_min')   ?? $employee->exit_early_min,
        'exit_late_min'    => $request->validated('exit_late_min')    ?? $employee->exit_late_min,

        // 👇 NUEVO
        'hourly_rate'      => $request->validated('hourly_rate') ?? $employee->hourly_rate,
    ]);

    // --- PAYLOAD SetUp (válido para tu firmware) ---
    $begin = $employee->valid_from->copy()->startOfDay()->format('Y-m-d\TH:i:s'); // 00:00:00
    $end   = ($employee->valid_to ?? Carbon::create(2035,12,31,23,59,59))->format('Y-m-d\TH:i:s');

    $payload = [
      'UserInfo' => [
        'employeeNo'     => $employee->employee_no,
        'name'           => $employee->name,
        'userType'       => 'normal',
        'gender'         => $employee->gender,
        'doorRight'      => '1',                         // string
        'userVerifyMode' => 'faceOrFpOrCardOrPw',
        'Valid' => [
          'enable'    => true,                           // boolean
          'timeType'  => 'local',
          'beginTime' => $begin,
          'endTime'   => $end,
        ],
        // Asignar plan 24×7 por defecto (suele existir como #1)
        'RightPlan' => [[
          'doorNo'         => 1,                       // string
          'planTemplateNo' => '1'                        // string
        ]],
      ]
    ];

    $resp = $hik->upsertUser($payload);

    if ($resp['ok']) {
        $employee->update(['synced_at' => now()]);
        return redirect()->route('employees.index')
            ->with('ok', 'Empleado sincronizado (SetUp).');
    }

    return redirect()->route('employees.index')
        ->with('ok', 'Guardado local. Sync falló: HTTP '.$resp['status'].' ('.$resp['endpoint'].') Body: '.substr($resp['body'] ?? '',0,300));
}


    public function destroy(Employee $employee, HikvisionService $hik): RedirectResponse
{
    $empNo = $employee->employee_no;

    // 1) Intentar borrar en Hikvision
    $resp = $hik->deleteUser($empNo);

    // 2) Siempre borra local (tu fuente de verdad es la app)
    $employee->delete();

    if ($resp['ok']) {
        return redirect()->route('employees.index')
            ->with('ok', "Empleado {$empNo} eliminado en Hikvision ({$resp['endpoint']}) y localmente.");
    }

    // Si falló en el equipo, informa detalle pero ya se borró local
    return redirect()->route('employees.index')
        ->with('ok', "Empleado {$empNo} eliminado localmente. No se pudo borrar en el dispositivo: HTTP {$resp['status']} ({$resp['endpoint']}) Body: ".substr($resp['body'] ?? '',0,200));
}

    public function bulkDestroy(Request $request, HikvisionService $hik): RedirectResponse
    {
        // Validar que se reciban IDs
        $validated = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['required', 'integer'],
        ]);

        $employeeIds = $validated['employee_ids'];
        $employees = Employee::whereIn('id', $employeeIds)->get();

        if ($employees->isEmpty()) {
            return redirect()->route('employees.index')
                ->with('ok', 'No se encontraron empleados para eliminar.');
        }

        $deleted = 0;
        $hikSuccesses = 0;
        $hikErrors = 0;
        $errorDetails = [];

        foreach ($employees as $employee) {
            $empNo = $employee->employee_no;

            // 1) Intentar borrar en Hikvision (igual que destroy individual)
            $resp = $hik->deleteUser($empNo);

            if ($resp['ok']) {
                $hikSuccesses++;
            } else {
                $hikErrors++;
                $errorDetails[] = "{$empNo}: HTTP {$resp['status']}";
            }

            // 2) Siempre borra local (tu fuente de verdad es la app)
            $employee->delete();
            $deleted++;
        }

        // Construir mensaje de resultado
        $message = "✓ {$deleted} empleado(s) eliminado(s) localmente.";
        
        if ($hikSuccesses > 0) {
            $message .= " Hikvision: {$hikSuccesses} eliminado(s) exitosamente.";
        }
        
        if ($hikErrors > 0) {
            $message .= " {$hikErrors} error(es) en Hikvision.";
            if (!empty($errorDetails)) {
                $preview = implode(', ', array_slice($errorDetails, 0, 2));
                if (count($errorDetails) > 2) {
                    $preview .= "... y " . (count($errorDetails) - 2) . " más";
                }
                $message .= " Detalles: {$preview}";
            }
        }

        return redirect()->route('employees.index')->with('ok', $message);
    }

// app/Http/Controllers/EmployeeController.php
public function syncFp(Employee $employee): RedirectResponse
    {
        // Ventana de búsqueda (ajústala a tu gusto)
        $daysWindow = 365; // 90/180/365
        $from = now()->subDays($daysWindow);

        // 1) Filtro de "huella" por eventos
        //    - event_type = 'accesscontrollerevent' (normalizado a lower)
        //    - O method = 'fp'
        //    - O event_type contenga 'finger' (por si llega variado)
        $base = AttendanceEvent::query()
            ->where('employee_no', $employee->employee_no)
            ->where('event_time', '>=', $from);

        $hasFp = (clone $base)->where(function ($q) {
                $q->whereRaw('LOWER(event_type) = ?', ['accesscontrollerevent'])
                  ->orWhere('method', 'fp')
                  ->orWhereRaw('LOWER(event_type) LIKE ?', ['%finger%']);
            })
            ->exists();

        // 2) (Opcional) estadísticas para mostrar en el flash
        $count   = 0;
        $lastEvt = null;
        if ($hasFp) {
            $agg     = (clone $base)->select(
                DB::raw('COUNT(*) as c'),
                DB::raw('MAX(event_time) as last_time')
            )->first();
            $count   = (int) ($agg->c ?? 0);
            $lastEvt = $agg->last_time ? (string) $agg->last_time : null;
        }

        // 3) Actualiza el flag del empleado
        $employee->has_fp = $hasFp;
        $employee->save();

        // 4) Mensaje
        if ($hasFp) {
            $msg = "Biometría sincronizada por eventos (se encontraron {$count} eventos; último: ".($lastEvt ?: 'N/D').").";
            return back()->with('ok', $msg);
        }

        return back()->with('ok', "No se encontró evento de huella para {$employee->employee_no} en los últimos {$daysWindow} días.");
    }

    /**
     * Sincroniza credenciales (card_no, has_fp, has_face, has_card) desde el dispositivo.
     * POST /employees/{employee}/sync-credentials
     */
    public function syncCredentials(Employee $employee, \App\Services\HikvisionService $hik)
    {
        $updated = [];

        // 1) Intentar obtener info desde ISAPI (UserInfo/Search)
        $info = $hik->getUserBiometrics($employee->employee_no);

        if (!empty($info) && ($info['ok'] ?? false)) {
            // huellas
            $fpNum = isset($info['fpNum']) ? (int)$info['fpNum'] : 0;
            $employee->has_fp = $fpNum > 0 ? true : $employee->has_fp;
            $updated['fp_count'] = $fpNum;

            // Buscar cardNo dentro del raw si existe
            $raw = $info['raw'] ?? null;
            if (is_array($raw)) {
                $rawStr = json_encode($raw);
                // heurística para cardNo
                if (preg_match('/cardNo"?\s*[:=]\s*"?([A-Za-z0-9_-]+)"?/i', $rawStr, $m)) {
                    $card = $m[1];
                    if (! $employee->card_no) {
                        $employee->card_no = $card;
                        $updated['card_no'] = $card;
                    }
                }
                // heurística para face presence
                if (preg_match('/face|faceList|faceNum|faceInfo/i', $rawStr)) {
                    $employee->has_face = true;
                    $updated['has_face'] = true;
                }
                // card existence
                if (preg_match('/card|CardInfo|CardNo/i', $rawStr)) {
                    $employee->has_card = true;
                    $updated['has_card'] = true;
                }
            }

            $employee->save();

            return response()->json([ 'ok' => true, 'from' => 'isapi', 'updated' => $updated, 'employee' => $employee->only(['employee_no','card_no','has_fp','has_face','has_card']) ]);
        }

        // 2) Fallback: intentar contar huellas con llamadas más robustas
        $cnt = $hik->countFingerprints($employee->employee_no);
        if (!empty($cnt) && ($cnt['ok'] ?? false)) {
            $c = (int)($cnt['count'] ?? 0);
            $employee->has_fp = $c > 0 ? true : $employee->has_fp;
            $employee->save();
            return response()->json([ 'ok'=>true, 'from'=>'countFingerprints', 'count'=>$c, 'employee'=>$employee->only(['employee_no','card_no','has_fp']) ]);
        }

        return response()->json([ 'ok' => false, 'msg' => 'No se obtuvo información desde el dispositivo.' ], 404);
    }

    public function syncAll(\App\Services\HikvisionClient $hik)
{
    $pending = \App\Models\Employee::whereNull('synced_at')->get();

    $ok = 0; $fail = 0;
    foreach ($pending as $e) {
        $res = false;
        try { $res = $hik->pushUser($e); } catch (\Throwable $ex) {
            Log::warning('HIK syncAll error', ['employee_no' => $e->employee_no, 'msg' => $ex->getMessage()]);
        }
        $res ? $ok++ : $fail++;
    }

    return back()->with('status', "Sincronizados: {$ok}, fallidos: {$fail}");
}
    /**
     * Formulario para crear/editar horario de un empleado
     */
    public function scheduleForm(Employee $employee)
    {
        $schedule = $employee->schedule; // hasOne(WorkSchedule::class, 'employee_no', 'employee_no');

        return view('employees.schedule', compact('employee', 'schedule'));
    }

    /**
     * Guardar horario (create/update) para un empleado
     */
    public function scheduleStore(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'entry_time'   => ['required', 'date_format:H:i'],
            'exit_time'    => ['required', 'date_format:H:i', 'after:entry_time'],
            'entry_minus'  => ['required', 'integer', 'min:0', 'max:300'],
            'entry_plus'   => ['required', 'integer', 'min:0', 'max:300'],
            'exit_minus'   => ['required', 'integer', 'min:0', 'max:300'],
            'exit_plus'    => ['required', 'integer', 'min:0', 'max:300'],
        ]);

        // Si existe horario, lo actualizamos; si no, lo creamos
        if ($employee->schedule) {
            $employee->schedule->update($data);
        } else {
            $employee->schedule()->create($data);
            // hasOne('WorkSchedule', 'employee_no', 'employee_no')
            // completará automáticamente employee_no con $employee->employee_no
        }

        return redirect()
            ->route('attendance.byEmployee', $employee->employee_no)
            ->with('ok', 'Horario guardado correctamente.');
    }
}



