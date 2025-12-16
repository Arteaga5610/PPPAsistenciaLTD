<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\MailSetting;
use App\Mail\ReportAttendanceMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\AttendanceController;

class ReportController extends Controller
{
    public function sendEmployeeReport(Request $request, Employee $employee)
    {
        // 1) Check email
        if (empty($employee->email)) {
            return back()->with('ok', 'El empleado no tiene un correo registrado.');
        }

        // 2) Check SMTP config
        $settings = MailSetting::first();
        if (! $settings || ! $settings->enabled) {
            return back()->with('ok', 'SMTP no configurado o deshabilitado. Configure SMTP en el panel.');
        }

        // 3) Apply dynamic mail config
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $settings->host,
            'port' => $settings->port,
            'encryption' => $settings->encryption,
            'username' => $settings->username,
            'password' => $settings->getDecryptedPassword(),
            'timeout' => null,
        ]);
        Config::set('mail.from.address', $settings->from_address);
        Config::set('mail.from.name', $settings->from_name);

        // 4) Build report data (you should adapt to your attendance logic)
        $period = now()->startOfMonth()->format('d/m/Y') . ' - ' . now()->endOfMonth()->format('d/m/Y');

        // Build attendance rows using the same logic as AttendanceController
        $data = AttendanceController::buildRows($employee->employee_no);
        $rows = $data['rows'] ?? [];

        $attendanceRecords = [];
        foreach ($rows as $r) {
            $date = $r['date']->format('d/m/Y');
            $dayName = $r['day_name'] ?? '';

            $turnos = $r['turnos'] ?? [];
            $firstRow = true;

            foreach ($turnos as $firstIndex => $t) {
                $scheduled = ($t['entry_scheduled'] ?? '') . ' – ' . ($t['exit_scheduled'] ?? '');

                $entryText = '';
                if (!empty($t['estado_entrada'])) {
                    $entryText = strip_tags($t['estado_entrada']);
                    $icons = ['✅', '❌', '⚠️', '⚠'];
                    $entryText = trim(str_replace($icons, '', $entryText));
                    if (!empty($t['entry_mark']) && $t['entry_mark'] instanceof \Illuminate\Support\Carbon) {
                        $entryText .= ' (' . $t['entry_mark']->format('H:i') . ')';
                    }
                }

                $exitText = '';
                if (!empty($t['estado_salida'])) {
                    $exitText = strip_tags($t['estado_salida']);
                    $icons = ['✅', '❌', '⚠️', '⚠'];
                    $exitText = trim(str_replace($icons, '', $exitText));
                    if (!empty($t['exit_mark']) && $t['exit_mark'] instanceof \Illuminate\Support\Carbon) {
                        $exitText .= ' (' . $t['exit_mark']->format('H:i') . ')';
                    }
                }

                $observation = trim(($t['obs_entrada'] ?? '') . ' ' . ($t['obs_salida'] ?? ''));
                if (!empty($r['pay_info'])) {
                    $observation = trim($observation . ' ' . $r['pay_info']);
                }

                $amount = null;
                if ($firstRow && isset($r['pay_amount']) && !is_null($r['pay_amount'])) {
                    $amount = number_format($r['pay_amount'], 2);
                }

                $attendanceRecords[] = [
                    'date' => $date,
                    'day'  => ucfirst($dayName),
                    'scheduled' => $scheduled,
                    'entry_mark' => $entryText,
                    'exit_mark'  => $exitText,
                    'observation'=> $observation,
                    'amount'     => $amount,
                ];

                $firstRow = false;
            }
        }

        // 5) Render PDF from Blade view
        $pdf = Pdf::loadView('reports.attendance_pdf', [
            'employee' => $employee,
            'period' => $period,
            'records' => $attendanceRecords,
        ]);

        $pdfData = $pdf->output();

        // 6) Send mail
        try {
            Mail::to($employee->email)->send(new ReportAttendanceMail($employee, $period, 'Adjunto reporte de asistencia.', $pdfData));
            return back()->with('ok', 'Reporte enviado a ' . $employee->email);
        } catch (\Throwable $ex) {
            return back()->with('ok', 'Error enviando reporte: ' . $ex->getMessage());
        }
    }
}
