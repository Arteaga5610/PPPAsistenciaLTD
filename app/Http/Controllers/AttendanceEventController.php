<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEvent;
use Illuminate\Http\Request;

class AttendanceEventController extends Controller
{
public function index(\Illuminate\Http\Request $request)
{
    $q = $request->input('q');

    // Por defecto: mostrar TODO (people=0). Si people=1 -> solo persona
    $onlyPeople = $request->boolean('people', false);

    $events = \App\Models\AttendanceEvent::query()
        ->when($onlyPeople, fn($qb) => $qb->whereNotNull('employee_no'))
        ->when($q, fn($qb) =>
            $qb->where(function($qq) use ($q) {
                $qq->where('employee_no', 'like', "%{$q}%")
                   ->orWhere('event_type', 'like', "%{$q}%")
                   ->orWhere('method', 'like', "%{$q}%")
                   ->orWhere('result', 'like', "%{$q}%");
            }))
        ->orderByDesc('id')
        ->paginate(20)
        ->withQueryString();

    // Mapa de nombres (solo para los employee_no que están en la página)
    $empNos  = $events->pluck('employee_no')->filter()->unique();
    $nameMap = \App\Models\Employee::whereIn('employee_no', $empNos)->pluck('name','employee_no');

    return view('attendance.index', compact('events','q','onlyPeople','nameMap'));
}

}