<?php
namespace App\Services;

use App\Models\WorkSchedule;
use App\Models\AttendanceDay;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Aplica un evento con persona al consolidado diario.
     * @param string $employeeNo
     * @param \DateTimeInterface $eventTime
     * @param int|null $eventId  id del attendance_events recién guardado
     */
    public function applyEvent(string $employeeNo, \DateTimeInterface $eventTime, ?int $eventId = null): void
    {
        // 1) Buscar horario vigente
        $sched = WorkSchedule::where('employee_no', $employeeNo)->first();
        if (!$sched) return; // sin horario => ignorar

        $event = Carbon::instance(\Carbon\Carbon::parse($eventTime));
        $date  = $event->toDateString();

        // 2) Construir horario plan (combinando fecha del evento + horas del schedule)
        $entryPlan = Carbon::parse($date.' '.$sched->entry_time);
        $exitPlan  = Carbon::parse($date.' '.$sched->exit_time);

        // 3) Ventanas
        $entryStart = (clone $entryPlan)->subMinutes($sched->entry_minus);
        $entryEnd   = (clone $entryPlan)->addMinutes($sched->entry_plus);

        $exitStart  = (clone $exitPlan)->subMinutes($sched->exit_minus);
        $exitEnd    = (clone $exitPlan)->addMinutes($sched->exit_plus);

        // 4) Upsert día
        $day = AttendanceDay::firstOrCreate(
            ['employee_no' => $employeeNo, 'date' => $date],
            ['entry_scheduled' => $entryPlan, 'exit_scheduled' => $exitPlan]
        );

        $changed = false;

        // 5) Marcar entrada si cae en ventana y aún no tiene sello
        if ($event->betweenIncluded($entryStart, $entryEnd) && !$day->entry_marked_at) {
            $day->entry_marked_at = $event;
            $day->entry_event_id  = $eventId;
            // tardanza: si marcó después del plan
            $day->late_minutes = max(0, $event->diffInMinutes($entryPlan, false) * -1);
            $changed = true;
        }

        // 6) Marcar salida si cae en ventana y aún no tiene sello
        if ($event->betweenIncluded($exitStart, $exitEnd) && !$day->exit_marked_at) {
            $day->exit_marked_at = $event;
            $day->exit_event_id  = $eventId;
            // salida anticipada: si se fue antes del plan (valor positivo)
            $day->left_early_minutes = max(0, $exitPlan->diffInMinutes($event, false) * -1);
            $changed = true;
        }

        // 7) Estado
        if ($changed) {
            if ($day->entry_marked_at && $day->exit_marked_at) {
                $day->status = 'complete';
            } elseif ($day->entry_marked_at || $day->exit_marked_at) {
                $day->status = 'partial';
            } else {
                $day->status = 'pending';
            }
            $day->save();
        }
    }
}
