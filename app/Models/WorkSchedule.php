<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'employee_no',
        'entry_time', 'exit_time',
        'entry_minus', 'entry_plus',
        'exit_minus', 'exit_plus',
        'schedule_template_id',
        'work_days',
        'start_date','end_date',   // 👈 IMPORTANTE
    ];

    protected $casts = [
    'work_days'  => 'array',
    'start_date' => 'date',
    'end_date'   => 'date',
];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_no', 'employee_no');
    }

    public function template()
    {
        return $this->belongsTo(ScheduleTemplate::class, 'schedule_template_id');
    }
}
