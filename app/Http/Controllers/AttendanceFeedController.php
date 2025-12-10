<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceEvent;

class AttendanceFeedController extends Controller
{
    /**
     * /attendance/feed
     * Feed deduplicado por ventana de X segundos
     */
    public function index(Request $request)
{
    $bucket = (int)($request->input('bucket', 30));
    $since  = $request->input('since');

    // 1) Construye la consulta deduplicada (NO ejecutes ->get() todavía)
    $dedup = AttendanceEvent::dedup($bucket, $since);

    // 2) Envuélvela como subconsulta y haz el JOIN afuera
    $rows = DB::query()
        ->fromSub($dedup, 'd') // d = (select ... from attendance_events group by ...)
        ->leftJoin('employees', 'employees.employee_no', '=', 'd.employee_no')
        ->orderByDesc('d.event_time')
        ->limit(200)
        ->get([
            'employees.name as employee_name',
            'd.employee_no',
            'd.event_type',
            'd.event_time',
        ]);

    return view('attendance.feed', compact('rows', 'bucket', 'since'));
}
}
