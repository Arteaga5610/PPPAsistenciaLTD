<?php

namespace App\Support;

class AttendanceLabels
{
    public static function label(string $eventType = null, ?string $method = null, ?string $result = null): string
    {
        $t = strtolower((string) $eventType);
        $m = strtolower((string) $method);
        $r = strtolower((string) $result);

        $map = [
            'auth_fp_success'   => 'Autenticado mediante Huella Digital',
            'auth_fp_fail'      => 'Autenticación de Huella fallida',
            'auth_face_success' => 'Autenticado con rostro',
            'auth_face_fail'    => 'Autenticación de rostro fallida',
            'auth_card_success' => 'Autenticado con tarjeta',
            'auth_card_fail'    => 'Autenticación de tarjeta fallida',
            'door_open'         => 'Puerta abierta',
            'door_close'        => 'Puerta cerrada',
            'door_remote_open'  => 'Desbloqueo remoto',
            'door_hold_open_start' => 'Mantener abierto (iniciado)',
            'door_hold_open_end'   => 'Mantener abierto (terminado)',
            'door_locked'       => 'Puerta bloqueada',
            'door_unlocked'     => 'Puerta desbloqueada',
            'access_granted'    => 'Acceso concedido',
            'access_denied'     => 'Acceso denegado',
        ];

        if (isset($map[$t])) return $map[$t];

        // Fallback por método + resultado cuando el tipo quedó genérico
        if ($m === 'fp'   && $r === 'success') return 'Autenticado mediante Huella Digital';
        if ($m === 'fp'   && $r === 'fail')    return 'Autenticación de Huella fallida';
        if ($m === 'face' && $r === 'success') return 'Autenticado con rostro';
        if ($m === 'face' && $r === 'fail')    return 'Autenticación de rostro fallida';
        if ($m === 'card' && $r === 'success') return 'Autenticado con tarjeta';
        if ($m === 'card' && $r === 'fail')    return 'Autenticación de tarjeta fallida';

        return 'Evento de control de acceso';
    }
}
