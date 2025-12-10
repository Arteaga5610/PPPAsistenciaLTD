<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEvent extends Model
{
    protected $fillable = [
        'device_ip','employee_no','event_type','method','result','event_time','raw_payload',
    ];

    protected $casts = [
        'event_time' => 'datetime',
    ];

public function scopeDedup($q, int $bucketSeconds = 30, string $since = null)
{
    $since = $since ?: now()->subDay()->toDateTimeString();

    return $q->where('attendance_events.event_time', '>=', $since)
        ->whereNotNull('attendance_events.employee_no')
        ->selectRaw("
            attendance_events.employee_no AS employee_no,
            attendance_events.event_type  AS event_type,
            FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(attendance_events.event_time)/?) * ?) AS bucket_time,
            MIN(attendance_events.event_time) AS event_time
        ", [$bucketSeconds, $bucketSeconds])
        ->groupBy('attendance_events.employee_no', 'attendance_events.event_type', 'bucket_time');
}


}
