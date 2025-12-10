<?php

namespace App\Services;

use App\Models\Employee;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class HikvisionClient
{
    protected Client $http;

    public function __construct()
    {
        // OJO: si tu equipo responde en otro puerto, inclúyelo aquí: 192.168.22.28:80 o :8000
        $host = env('HIK_HOST', '192.168.22.28:80');

        $this->http = new Client([
            'base_uri'    => "http://{$host}",
            // Hikvision casi siempre requiere DIGEST (no Basic)
            'auth'        => [env('HIK_USER'), env('HIK_PASS'), 'digest'],
            'timeout'     => 10,
            'headers'     => [
                // algunos firmwares son estrictos con el charset
                'Content-Type' => 'application/json; charset=UTF-8',
            ],
            'http_errors' => false,
        ]);
    }

    public function pushUser(Employee $e): bool
    {
        // defaults si no hay vigencia
        $begin = $e->valid_from ? Carbon::parse($e->valid_from) : now()->startOfDay();
        $end   = $e->valid_to   ? Carbon::parse($e->valid_to)   : now()->addYears(5)->endOfDay();

        // Algunos equipos exigen el separador "T"
        $bt = $begin->format('Y-m-d\TH:i:s');
        $et = $end->format('Y-m-d\TH:i:s');

        $payload = [
            "UserInfo" => [
                "employeeNo" => (string) $e->employee_no,
                "name"       => (string) $e->name,
                "userType"   => $e->user_type ?: "normal",
                "Valid"      => [
                    "enable"    => true,
                    "beginTime" => $bt,
                    "endTime"   => $et,
                ],
            ],
        ];

        // 1) Crear
        $res  = $this->http->post('/ISAPI/AccessControl/UserInfo/Record?format=json', [
            'body' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $code = $res->getStatusCode();
        $body = (string) $res->getBody();

        Log::info('HIK Record', [
            'employee_no' => $e->employee_no,
            'status'      => $code,
            'body'        => $body,
        ]);

        // si falló, intenta Modificar
        if ($code < 200 || $code >= 300) {
            $res2 = $this->http->put('/ISAPI/AccessControl/UserInfo/Modify?format=json', [
                'body' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
            $code = $res2->getStatusCode();
            $body = (string) $res2->getBody();

            Log::info('HIK Modify', [
                'employee_no' => $e->employee_no,
                'status'      => $code,
                'body'        => $body,
            ]);
        }

        $ok = $code >= 200 && $code < 300;

        if ($ok) {
            $e->synced_at = now();
            $e->saveQuietly();
        } else {
            Log::warning('HIK pushUser failed', [
                'employee_no' => $e->employee_no,
                'status'      => $code,
                'body'        => $body,
            ]);
        }

        return $ok;
    }
}
