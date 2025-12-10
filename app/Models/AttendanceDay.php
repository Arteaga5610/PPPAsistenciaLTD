<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDay extends Model
{
    protected $fillable = [
        'employee_no','date',
        'entry_scheduled','exit_scheduled',
        'entry_marked_at','entry_event_id',
        'exit_marked_at','exit_event_id',
        'status','late_minutes','left_early_minutes'
    ];

    protected $casts = [
        'entry_scheduled' => 'datetime',
        'exit_scheduled'  => 'datetime',
        'entry_marked_at' => 'datetime',
        'exit_marked_at'  => 'datetime',
        'date'            => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_no', 'employee_no');
    }
}
