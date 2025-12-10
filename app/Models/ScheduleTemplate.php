<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    protected $fillable = [
        'name',
        'entry_time', 'exit_time',
        'entry_early_min', 'entry_late_min',
        'exit_early_min', 'exit_late_min',
        'work_days',
    ];

    protected $casts = [
        'work_days' => 'array',
    ];

    public function workSchedules()
    {
        return $this->hasMany(WorkSchedule::class);
    }
}
