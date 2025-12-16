<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#222">
  <p>Hola {{ $employee->name }},</p>
  <p>{!! nl2br(e($bodyText)) !!}</p>
  <p>Saludos,<br>{{ config('app.name') }}</p>
</div>
