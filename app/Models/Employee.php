<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Employee extends Model
{
    protected $fillable = [
        'employee_no',
        'card_no',
        'name',
        'hire_date',          // <--- NUEVO
        'hourly_rate',          // 👈 NUEVO
        'gender',
        'fp_count','has_fp',
        'department',
        'user_type',
        'verify_mode',
        'valid_from',
        'valid_to',
        'door_right',
        'groups',
        'meta',
        'synced_at',
        'employee_no','card_no','name','valid_from','valid_to','has_fp','has_face','has_card',
        'entry_time','exit_time',
        'entry_early_min','entry_late_min','exit_early_min','exit_late_min'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_to'   => 'datetime',
        'hire_date'  => 'date',      // <--- NUEVO
        'hourly_rate' => 'decimal:2', // 👈 para que venga como decimal
        'groups'     => 'array',
        'meta'       => 'array',
        'synced_at'  => 'datetime',
        'fp_count'   => 'int',
        'has_fp'     => 'bool',
        'has_face'   => 'bool',
        'has_card'   => 'bool',
        // Si quisieras, puedes castear como string; no toco lo existente
        // 'entry_time' => 'string',
        // 'exit_time'  => 'string',
    ];

public function schedule()
{
    // Siempre tomar el horario MÁS RECIENTE (por si tienes varios)
    return $this->hasOne(WorkSchedule::class, 'employee_no', 'employee_no')
        ->latestOfMany();
}
    public function attendanceDays() { return $this->hasMany(AttendanceDay::class, 'employee_no', 'employee_no'); }

    /**
     * Mantengo tu método original.
     * Devuelve null si no hay entry/exit definidos a nivel de empleado.
     */
    public function attendanceWindowsForDate(string $date): array
    {
        if (!$this->entry_time || !$this->exit_time) return [null, null];

        $entry = Carbon::parse("$date {$this->entry_time}");
        $exit  = Carbon::parse("$date {$this->exit_time}");

        return [
            [$entry->copy()->subMinutes($this->entry_early_min),
             $entry->copy()->addMinutes($this->entry_late_min)],
            [$exit->copy()->subMinutes($this->exit_early_min),
             $exit->copy()->addMinutes($this->exit_late_min)],
        ];
    }

    public function attendanceRecords()
    {
        // Dejo tu relación intacta (por si la usas en otro lado con id)
        return $this->hasMany(AttendanceRecord::class);
    }

    // Relación con los eventos de asistencia
    public function attendanceEvents()
    {
        return $this->hasMany(AttendanceEvent::class, 'employee_no', 'employee_no');
    }

    /* =========================
     *  AÑADIDOS (sin quitar nada)
     * ========================= */

    /**
     * NUEVO: relación a records por employee_no (tu nueva columna).
     * No reemplaza la anterior; úsala donde corresponda.
     */
    public function attendanceRecordsByNo()
    {
        return $this->hasMany(AttendanceRecord::class, 'employee_no', 'employee_no');
    }

    /**
     * NUEVO: versión "inteligente" que intenta:
     * 1) Ventanas definidas en el propio empleado (tu método actual)
     * 2) Ventanas desde WorkSchedule (por día de semana)
     * 3) Fallback amplio (mañana = entrada, tarde = salida) para pruebas
     */
    public function attendanceWindowsForDateSmart(string $date): array
{
    $sch = $this->schedule; // WorkSchedule por employee_no

    // Si hay horario, solo aplica DESDE su fecha de creación hacia adelante
    if ($sch && $sch->created_at) {
        $effectiveFrom = $sch->created_at->toDateString();
        if (\Illuminate\Support\Carbon::parse($date)->lt(
            \Illuminate\Support\Carbon::parse($effectiveFrom)
        )) {
            // Día anterior a la vigencia del horario => sin ventanas
            return [null, null];
        }
    }

    $entryTime = $sch? $sch->entry_time : $this->entry_time;
    $exitTime  = $sch? $sch->exit_time  : $this->exit_time;

    // Si no hay ni entrada ni salida configuradas, no hay ventanas
    if (!$entryTime && !$exitTime) {
        return [null, null];
    }

    // Respetar días laborables del schedule
    if ($sch && is_array($sch->work_days) && count($sch->work_days) > 0) {
        $dayOfWeek = \Illuminate\Support\Carbon::parse($date)->isoWeekday(); // 1=Mon..7=Sun
        if (!in_array($dayOfWeek, $sch->work_days)) {
            // Día no laborable => sin ventanas
            return [null, null];
        }
    }

    // Tolerancias: prioridad work_schedules; si no, employee; si no, defaults
    $eMinus = $sch? (int) $sch->entry_minus : (int) ($this->entry_early_min ?? 15);
    $ePlus  = $sch? (int) $sch->entry_plus  : (int) ($this->entry_late_min  ?? 15);
    $xMinus = $sch? (int) $sch->exit_minus  : (int) ($this->exit_early_min  ?? 10);
    $xPlus  = $sch? (int) $sch->exit_plus   : (int) ($this->exit_late_min   ?? 10);

    $entryAt = $entryTime ? \Illuminate\Support\Carbon::parse("$date $entryTime") : null;
    $exitAt  = $exitTime  ? \Illuminate\Support\Carbon::parse("$date $exitTime")  : null;

    $entryWin = $entryAt ? [
        $entryAt->copy()->subMinutes($eMinus),
        $entryAt->copy()->addMinutes($ePlus),
    ] : null;

    $exitWin = $exitAt ? [
        $exitAt->copy()->subMinutes($xMinus),
        $exitAt->copy()->addMinutes($xPlus),
    ] : null;

    return [$entryWin, $exitWin];
}


protected static function booted()
{
    static::creating(function ($e) {
        $e->valid_from ??= now()->startOfDay();
        $e->valid_to   ??= now()->addYears(5)->endOfDay();
    });

    static::created(function ($e) {
        try {
            app(\App\Services\HikvisionClient::class)->pushUser($e);
        } catch (\Throwable $ex) {
            Log::warning('HIK sync on create failed', [
                'employee_no' => $e->employee_no,
                'msg' => $ex->getMessage(),
            ]);
        }
    });
}


}
