<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportAttendanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $period;
    public $bodyText;
    public $pdfData;

    public function __construct($employee, $period, $bodyText, $pdfData)
    {
        $this->employee = $employee;
        $this->period = $period;
        $this->bodyText = $bodyText;
        $this->pdfData = $pdfData;
    }

    public function build()
    {
        $mail = $this->subject('Reporte de Asistencia')
            ->view('emails.report_simple')
            ->with(['employee' => $this->employee, 'bodyText' => $this->bodyText]);

        if (! empty($this->pdfData)) {
            $mail->attachData($this->pdfData, 'reporte_asistencia.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
