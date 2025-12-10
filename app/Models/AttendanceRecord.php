<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;


    protected $fillable = [
        'employee_no',
        'date',
        'entry_time',
        'exit_time',
        //'entry_window_start',
        //'entry_window_end',
        //'exit_window_start',
        //'exit_window_end',
    ];
    protected $casts = [
        'date'                => 'date',
        //'entry_window_start'  => 'datetime',
        //'entry_window_end'    => 'datetime',
        //'exit_window_start'   => 'datetime',
        //'exit_window_end'     => 'datetime',
    ];
    // Relación con el empleado
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_no', 'employee_no');
    }
}
