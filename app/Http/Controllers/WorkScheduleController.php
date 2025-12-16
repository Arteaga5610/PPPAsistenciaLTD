<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use App\Models\ScheduleTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class WorkScheduleController extends Controller
{
    // ============== LISTAR TODOS LOS HORARIOS ==============
    
    public function index()
    {
        $schedules = WorkSchedule::with(['employee', 'template'])
            ->orderBy('employee_no')
            ->paginate(20);
        
        return view('horarios.index', compact('schedules'));
    }

    // ============== CREAR PRIMER HORARIO ==============

    public function create()
    {
        $templates = ScheduleTemplate::orderBy('name')->get();
        return view('horarios.create', compact('templates'));
    }

    public function store(Request $request)
{
    // Primero validamos solo los campos básicos
    $request->validate([
        'employee_no' => ['required','string','max:32','exists:employees,employee_no'],
        'template_id' => ['nullable','integer','exists:schedule_templates,id'],
    ]);

    // Obtenemos la plantilla si existe
    $template = $request->template_id 
        ? ScheduleTemplate::find($request->template_id)
        : null;

    // Si hay plantilla, usamos sus valores por defecto
    if ($template) {
        $entryTime = $request->filled('entry_time') ? $request->entry_time : $template->entry_time;
        $exitTime  = $request->filled('exit_time') ? $request->exit_time : $template->exit_time;
        $workDays  = $request->has('work_days') ? $request->work_days : $template->work_days;
    } else {
        // Sin plantilla, validamos que los campos estén presentes
        $request->validate([
            'entry_time' => ['required','date_format:H:i'],
            'exit_time'  => ['required','date_format:H:i'],
        ]);
        
        $entryTime = $request->entry_time;
        $exitTime  = $request->exit_time;
        $workDays  = $request->work_days ?? [1,2,3,4,5];
    }

    // Validar que tengamos datos válidos
    if (!$entryTime || !$exitTime) {
        return back()->withErrors([
            'entry_time' => 'Debe especificar horarios o seleccionar una plantilla válida.'
        ])->withInput();
    }

    $entryMinus = $request->entry_minus ?? ($template?->entry_early_min ?? 15);
    $entryPlus  = $request->entry_plus ?? ($template?->entry_late_min ?? 15);
    $exitMinus  = $request->exit_minus ?? ($template?->exit_early_min ?? 10);
    $exitPlus   = $request->exit_plus ?? ($template?->exit_late_min ?? 10);

    // Si se marca 'no_repetitive', crear ocurrencias puntuales para la semana actual
    $noRepetitive = $request->has('no_repetitive') && $request->input('no_repetitive');

    if ($noRepetitive) {
        $days = is_array($workDays) ? $workDays : ([] === $workDays ? [] : (array)$workDays);
        $weekStart = Carbon::now('America/Lima')->startOfWeek(); // lunes
        foreach ($days as $wd) {
            $d = $weekStart->copy()->addDays(((int)$wd) - 1)->toDateString();
            WorkSchedule::create([
                'employee_no'          => $request->employee_no,
                'schedule_template_id' => $template?->id,
                'entry_time'           => $entryTime,
                'exit_time'            => $exitTime,
                'entry_minus'          => $entryMinus,
                'entry_plus'           => $entryPlus,
                'exit_minus'           => $exitMinus,
                'exit_plus'            => $exitPlus,
                'work_days'            => [$wd],
                'start_date'           => $d,
                'end_date'             => $d,
            ]);
        }
    } else {
        WorkSchedule::create([
            'employee_no'          => $request->employee_no,
            'schedule_template_id' => $template?->id,
            'entry_time'           => $entryTime,
            'exit_time'            => $exitTime,
            'entry_minus'          => $entryMinus,
            'entry_plus'           => $entryPlus,
            'exit_minus'           => $exitMinus,
            'exit_plus'            => $exitPlus,
            'work_days'            => $workDays,
            'start_date'           => Carbon::today(), // Iniciar desde HOY
            'end_date'             => null,             // Sin fecha de fin por defecto
        ]);
    }

    return redirect()
        ->route('horarios.create')
        ->with('status', 'Horario guardado correctamente. Válido desde hoy (' . Carbon::today()->format('d/m/Y') . ').');
}



    // ============== ACTUALIZAR HORARIO (NUEVA VISTA) ==============

    public function updateForm()
    {
        $templates = ScheduleTemplate::orderBy('name')->get();
        return view('horarios.update', compact('templates'));
    }
    
    public function update(Request $request)
{
    // Validar campos básicos
    $request->validate([
        'employee_no' => ['required','string','max:32','exists:employees,employee_no'],
        'template_id' => ['nullable','integer','exists:schedule_templates,id'],
    ]);

    // Obtenemos la plantilla si existe
    $template = $request->template_id 
        ? ScheduleTemplate::find($request->template_id)
        : null;

    // Si hay plantilla, usamos sus valores por defecto
    if ($template) {
        $entryTime = $request->filled('entry_time') ? $request->entry_time : $template->entry_time;
        $exitTime  = $request->filled('exit_time') ? $request->exit_time : $template->exit_time;
        $workDays  = $request->has('work_days') ? $request->work_days : $template->work_days;
    } else {
        // Sin plantilla, validamos que los campos estén presentes
        $request->validate([
            'entry_time' => ['required','date_format:H:i'],
            'exit_time'  => ['required','date_format:H:i'],
        ]);
        
        $entryTime = $request->entry_time;
        $exitTime  = $request->exit_time;
        $workDays  = $request->work_days ?? [1,2,3,4,5];
    }

    // Validar que tengamos datos válidos
    if (!$entryTime || !$exitTime) {
        return back()->withErrors([
            'entry_time' => 'Debe especificar horarios o seleccionar una plantilla válida.'
        ])->withInput();
    }

    $entryMinus = $request->entry_minus ?? ($template?->entry_early_min ?? 15);
    $entryPlus  = $request->entry_plus ?? ($template?->entry_late_min ?? 15);
    $exitMinus  = $request->exit_minus ?? ($template?->exit_early_min ?? 10);
    $exitPlus   = $request->exit_plus ?? ($template?->exit_late_min ?? 10);

    $today     = \Carbon\Carbon::now('America/Lima')->toDateString();
    $yesterday = \Carbon\Carbon::now('America/Lima')->subDay()->toDateString();

    // 1) Cerrar horario vigente (el que no tiene end_date)
    $current = WorkSchedule::where('employee_no', $request->employee_no)
        ->whereNull('end_date')
        ->orderBy('start_date', 'desc')
        ->first();

    if ($current) {
        // Lo dejamos vigente hasta AYER
        $current->update([
            'end_date' => $yesterday,
        ]);
    }

    // 2) Si 'no_repetitive' está marcado -> crear ocurrencias puntuales para la semana actual
    $noRepetitive = $request->has('no_repetitive') && $request->input('no_repetitive');

    if ($noRepetitive) {
        $days = is_array($workDays) ? $workDays : ([] === $workDays ? [] : (array)$workDays);
        $weekStart = Carbon::now('America/Lima')->startOfWeek();
        foreach ($days as $wd) {
            $d = $weekStart->copy()->addDays(((int)$wd) - 1)->toDateString();
            WorkSchedule::create([
                'employee_no'          => $request->employee_no,
                'schedule_template_id' => $template?->id,
                'entry_time'           => $entryTime,
                'exit_time'            => $exitTime,
                'entry_minus'          => $entryMinus,
                'entry_plus'           => $entryPlus,
                'exit_minus'           => $exitMinus,
                'exit_plus'            => $exitPlus,
                'work_days'            => [$wd],
                'start_date'           => $d,
                'end_date'             => $d,
            ]);
        }
    } else {
        // 2) Crear nuevo horario desde HOY hacia adelante (repetitivo)
        WorkSchedule::create([
            'employee_no'          => $request->employee_no,
            'schedule_template_id' => $template?->id,
            'entry_time'           => $entryTime,
            'exit_time'            => $exitTime,
            'entry_minus'          => $entryMinus,
            'entry_plus'           => $entryPlus,
            'exit_minus'           => $exitMinus,
            'exit_plus'            => $exitPlus,
            'work_days'            => $workDays,
            'start_date'           => $today,
            'end_date'             => null,
        ]);
    }

    return redirect()
        ->route('horarios.updateForm')
        ->with('status', 'Horario actualizado correctamente. El nuevo horario rige desde hoy.');
}



    // ============== PLANTILLAS ==============

    public function storeTemplate(Request $request)
    {
        // Limpiar campos vacíos antes de validar
        $input = $request->all();
        if (empty($input['entry_time'])) {
            unset($input['entry_time']);
        }
        if (empty($input['exit_time'])) {
            unset($input['exit_time']);
        }
        $request->merge($input);

        $data = $request->validate([
            'name'        => ['required','string','max:100'],
            'entry_time'  => ['required','date_format:H:i'],
            'entry_minus' => ['nullable','integer','min:0'],
            'entry_plus'  => ['nullable','integer','min:0'],
            'exit_time'   => ['required','date_format:H:i'],
            'exit_minus'  => ['nullable','integer','min:0'],
            'exit_plus'   => ['nullable','integer','min:0'],
            'work_days'   => ['nullable','array'],
            'work_days.*' => ['integer','min:1','max:7'],
        ]);

        ScheduleTemplate::create([
            'name'            => $data['name'],
            'entry_time'      => $data['entry_time'],
            'exit_time'       => $data['exit_time'],
            'entry_early_min' => $data['entry_minus'] ?? 15,
            'entry_late_min'  => $data['entry_plus'] ?? 15,
            'exit_early_min'  => $data['exit_minus'] ?? 10,
            'exit_late_min'   => $data['exit_plus'] ?? 10,
            'work_days'       => $data['work_days'] ?? [1,2,3,4,5],
        ]);

        return redirect()->route('horarios.create')->with('status', 'Plantilla creada correctamente.');
    }

    public function createTemplate()
    {
        return view('horarios.template_create');
    }
    
    // ============== ELIMINAR HORARIO ==============
    
    public function destroy(WorkSchedule $horario)
    {
        $employeeName = $horario->employee->name ?? $horario->employee_no;
        
        $horario->delete();
        
        return redirect()->route('horarios.index')
            ->with('ok', "Horario de {$employeeName} eliminado correctamente.");
    }
}
