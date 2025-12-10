<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class HikvisionService
{
    protected Client $http;
    protected string $base;
    protected array $auth;
    
    public function __construct()
    {
        $cfg = config('hikvision');

        $this->base = sprintf('%s://%s:%d', $cfg['protocol'], $cfg['host'], $cfg['port']);
        $this->auth = [$cfg['user'], $cfg['pass'], 'digest'];

        $this->http = new Client([
            'base_uri'        => $this->base,
            'auth'            => $this->auth, // HTTP Digest
            'http_errors'     => false,
            'timeout'         => 8.0,
            'connect_timeout' => 5.0,
            'verify'          => false,       // cert autofirmado
            'headers'         => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ]);
    }

    /**
     * Intenta SetUp; si falla con 400/405, intenta Record.
     */
    public function upsertUser(array $payload): array
    {
        $res = $this->http->request('PUT', '/ISAPI/AccessControl/UserInfo/SetUp?format=json', [
            'json' => $payload,
        ]);

        $status = $res->getStatusCode();
        $body   = (string) $res->getBody();

        return [
            'ok'      => $status >= 200 && $status < 300,
            'status'  => $status,
            'body'    => $body,
            'endpoint'=> 'SetUp',
        ];
    }
    public function deleteUser(string $employeeNo): array
{
    // INTENTO 1: DELETE por ID
    $res1 = $this->http->request('DELETE', "/ISAPI/AccessControl/UserInfo/ID/{$employeeNo}");
    $s1 = $res1->getStatusCode();
    if ($s1 >= 200 && $s1 < 300) {
        return ['ok'=>true,'status'=>$s1,'endpoint'=>'ID_DELETE','body'=>(string)$res1->getBody()];
    }

    // INTENTO 2 (forma A): PUT /Delete con array de objetos
    $payloadA = [
        'UserInfoDelCond' => [
            'EmployeeNoList' => [
                ['employeeNo' => $employeeNo]
            ]
        ]
    ];
    $res2 = $this->http->request('PUT', '/ISAPI/AccessControl/UserInfo/Delete?format=json', [
        'json' => $payloadA,
    ]);
    $s2 = $res2->getStatusCode();
    if ($s2 >= 200 && $s2 < 300) {
        return ['ok'=>true,'status'=>$s2,'endpoint'=>'Delete_PUT_A','body'=>(string)$res2->getBody()];
    }

    // INTENTO 3 (forma B): PUT /Delete con array de strings dentro de EmployeeNoList
    $payloadB = [
        'UserInfoDelCond' => [
            'EmployeeNoList' => [
                'employeeNo' => [$employeeNo]   // <- otra forma que aceptan algunos firmwares
            ]
        ]
    ];
    $res3 = $this->http->request('PUT', '/ISAPI/AccessControl/UserInfo/Delete?format=json', [
        'json' => $payloadB,
    ]);
    $s3 = $res3->getStatusCode();
    return [
        'ok'      => $s3 >= 200 && $s3 < 300,
        'status'  => $s3,
        'endpoint'=> 'Delete_PUT_B',
        'body'    => (string)$res3->getBody(),
    ];
}
public function getUserBiometrics(string $employeeNo): array
{
    // Busca por employeeNo
    $payload = [
        'UserInfoSearchCond' => [
            'searchID' => '1',
            'searchResultPosition' => 0,
            'maxResults' => 1,
            'EmployeeNoList' => [
                ['employeeNo' => $employeeNo],
            ],
        ],
    ];

    try {
        $res = $this->http->request(
            'POST',
            '/ISAPI/AccessControl/UserInfo/Search?format=json',
            ['json' => $payload]
        );

        if ($res->getStatusCode() >= 200 && $res->getStatusCode() < 300) {
            $json = json_decode((string)$res->getBody(), true);
            $list = $json['UserInfo'] ?? $json['UserInfoList'] ?? $json['UserInfo']['Item'] ?? [];
            $this->isAssoc($list).
            // Firmwares varían: prueba varios nombres comunes
            $fpNum = $info['fingerPrintNum'] 
                ?? $info['fpNum'] 
                ?? (isset($info['FPList']) ? count($info['FPList']) : null);

            return [
                'ok' => true,
                'fpNum' => (int)($fpNum ?? 0),
                'raw' => $json,
            ];
        }
    } catch (\Throwable $e) {
        // silencioso para fallback
    }

    return ['ok' => false];
}

/** helper para saber si es array asociativo */
private function isAssoc(array $arr): bool
{
    return array_keys($arr) !== range(0, count($arr) - 1);
}

// HUELLA DIGITAL FUNCION
  // =======================================================
//  HUELLA DIGITAL — Enroll / Status / Cancel (robusto)
// =======================================================

/** Mapa de dedos: [fingerType(Verbose), shortCode, idA, idB] */
private array $fingerMap = [
    'rightThumb'  => ['rightThumb','RT','1','11'],
    'rightIndex'  => ['rightIndex','RI','2','12'],
    'rightMiddle' => ['rightMiddle','RM','3','13'],
    'rightRing'   => ['rightRing','RR','4','14'],
    'rightLittle' => ['rightLittle','RL','5','15'],
    'leftThumb'   => ['leftThumb','LT','6','16'],
    'leftIndex'   => ['leftIndex','LI','7','17'],
    'leftMiddle'  => ['leftMiddle','LM','8','18'],
    'leftRing'    => ['leftRing','LR','9','19'],
    'leftLittle'  => ['leftLittle','LL','10','20'],
];

private function mapFinger(string $finger): array
{
    return $this->fingerMap[$finger] ?? $this->fingerMap['rightIndex'];
}

/** envoltorio seguro para requests (sin lanzar excepciones) */
private function tryRequest(string $method, string $uri, array $opts = []): array
{
    $opts += [
        'http_errors' => false,
        'headers'     => ['Accept' => 'application/json'],
        'timeout'     => 10,
    ];
    try {
        $res  = $this->http->request($method, $uri, $opts);
        $code = $res->getStatusCode();
        $body = (string) $res->getBody();
        $json = null;
        if (str_contains($res->getHeaderLine('Content-Type'), 'json')) {
            $json = json_decode($body, true);
        }
        return ['ok' => $code >= 200 && $code < 300, 'status' => $code, 'body' => $body, 'json' => $json, 'endpoint' => $uri];
    } catch (\Throwable $e) {
        return ['ok' => false, 'status' => 0, 'body' => $e->getMessage(), 'json' => null, 'endpoint' => $uri];
    }
}

/** Descubre cuál rama de huellas soporta el firmware */
  public function discoverFingerprintApi(): array
  {
      foreach ([
          '/ISAPI/AccessControl/Fingerprint/capabilities', // firmwares nuevos
          '/ISAPI/AccessControl/FingerPrint/capabilities', // variante mayúscula
          '/ISAPI/AccessControl/Finger/capabilities',      // modelos viejos
          '/ISAPI/AccessControl/capabilities',             // genérico
      ] as $p) {
          $r = $this->tryRequest('GET', $p);
          if ($r['ok']) {
              return ['ok' => true, 'endpoint' => $p, 'caps' => $r['json']];
          }
      }
      return ['ok' => false, 'endpoint' => null, 'caps' => null];
  }

/**
 * Inicia inscripción de huella en el terminal (muestra prompt).
 * Devuelve: ['ok'=>bool, 'endpoint'=>string, 'status'=>int, 'body'=>string]
 */
public function startFingerprintEnroll(string $employeeNo, string $finger = 'rightIndex'): array
{
    [$f1, $_, $idA, $_idB] = $this->mapFinger($finger);

    // 1) Descubre rama preferida
    $disc           = $this->discoverFingerprintApi();
    $base           = strtolower($disc['endpoint'] ?? '');
    $preferNewRama  = str_contains($base, '/fingerprint/'); // Fingerprint vs FingerPrint

    // 2) Prepara intentos (varios endpoints válidos según firmware)
    $payloadEnroll = ['FPEnrollCond' => [
        'employeeNo' => $employeeNo,
        'fingerType' => $f1,
        'enable'     => true,
    ]];

    $payloadRecordStart = ['FingerPrintInfo' => [
        'employeeNo'    => $employeeNo,
        'fingerType'    => $f1,
        'fingerPrintID' => $idA,
        'fpCtrl'        => 'start',
        'enable'        => true,
    ]];

    $tries = [
        // Enroll (PUT) – ordena según la rama descubierta
        ['PUT', '/ISAPI/AccessControl/Fingerprint/Enroll?format=json',  $payloadEnroll],
        ['PUT', '/ISAPI/AccessControl/FingerPrint/Enroll?format=json',  $payloadEnroll],

        // Record (POST) – algunos firmwares usan Record start/stop
        ['POST','/ISAPI/AccessControl/FingerPrint/Record?format=json', $payloadRecordStart],
    ];

    if (!$preferNewRama) {
        // si NO detectamos /Fingerprint/, prioriza la variante antigua
        $tries = [
            ['PUT', '/ISAPI/AccessControl/FingerPrint/Enroll?format=json', $payloadEnroll],
            ['POST','/ISAPI/AccessControl/FingerPrint/Record?format=json', $payloadRecordStart],
            ['PUT', '/ISAPI/AccessControl/Fingerprint/Enroll?format=json', $payloadEnroll],
        ];
    }

    // 3) Ejecuta intentos hasta que alguno responda 2xx
    foreach ($tries as [$method, $path, $json]) {
        $r = $this->tryRequest($method, $path, ['json' => $json]);
        if ($r['ok']) return $r;
    }

    return ['ok' => false, 'endpoint' => '<none>', 'status' => 400, 'body' => 'No enroll endpoint'];
}

/**
 * Consulta el progreso/resultado de la inscripción.
 * Devuelve: ['ok'=>bool, 'statusText'=>?, 'progress'=>?, 'endpoint'=>?]
 */
public function getFingerprintStatus(string $employeeNo): array
{
    foreach ([
        '/ISAPI/AccessControl/Fingerprint/Status?format=json',
        '/ISAPI/AccessControl/FingerPrint/Status?format=json',
        '/ISAPI/AccessControl/Finger/Status?format=json',
    ] as $p) {
        $r = $this->tryRequest('GET', $p);
        if ($r['ok']) {
            $j       = $r['json'] ?? [];
            $status  = $j['status'] ?? $j['EnrollStatus'] ?? null;
            $progress= $j['progress'] ?? $j['Progress'] ?? null;
            $statusL = strtolower((string)$status);

            // normalización “éxito”
            $ok = in_array($statusL, ['success','ok','done','completed'], true)
               || ($j['enrolled'] ?? false) === true
               || ($j['result'] ?? '') === 'success';

            return [
                'ok'         => $ok,
                'status'     => $r['status'],
                'endpoint'   => $p,
                'statusText' => $status,
                'progress'   => $progress,
                'raw'        => $j,
            ];
        }
    }

    // fallback: muchos firmwares NO tienen /Status (usa webhook)
    return ['ok' => false, 'status' => 404, 'endpoint' => '<none>', 'statusText' => null, 'progress' => null, 'raw' => null];
}

/**
 * Cancela inscripción (si el firmware lo soporta).
 * Devuelve: ['ok'=>bool, 'status'=>int, 'endpoint'=>string]
 */
public function cancelFingerprintEnroll(string $employeeNo): array
{
    // 1) Enroll disable
    $p1 = ['FPEnrollCond' => ['employeeNo' => $employeeNo, 'enable' => false]];
    foreach ([
        '/ISAPI/AccessControl/Fingerprint/Enroll?format=json',
        '/ISAPI/AccessControl/FingerPrint/Enroll?format=json',
    ] as $p) {
        $r = $this->tryRequest('PUT', $p, ['json' => $p1]);
        if ($r['ok']) return $r;
    }

    // 2) Record stop
    $p2 = ['FingerPrintInfo' => ['employeeNo' => $employeeNo, 'fpCtrl' => 'stop', 'enable' => false]];
    $r2 = $this->tryRequest('POST', '/ISAPI/AccessControl/FingerPrint/Record?format=json', ['json' => $p2]);
    if ($r2['ok']) return $r2;

    return ['ok' => false, 'status' => 400, 'endpoint' => '<none>', 'body' => 'No cancel endpoint'];
}

// App\Services\HikvisionService.php
public function countFingerprints(string $employeeNo): array
{
    $attempts = []; // guardamos todos los intentos para debug

    $try = function (string $method, string $path, ?array $json, callable $extract) use (&$attempts) {
        try {
            $opts = $json ? ['json'=>$json] : [];
            $res  = $this->http->request($method, $path, $opts);
            $code = $res->getStatusCode();
            $body = (string) $res->getBody();

            $attempts[] = ['method'=>$method,'path'=>$path,'status'=>$code];

            if ($code >= 200 && $code < 300) {
                $data = json_decode($body, true);
                $cnt  = $extract($data);
                if ($cnt !== null) {
                    return ['ok'=>true,'count'=>(int)$cnt,'status'=>$code,'endpoint'=>$path];
                }
            }
        } catch (\Throwable $e) {
            $attempts[] = ['method'=>$method,'path'=>$path,'status'=>0,'err'=>$e->getMessage()];
        }
        return null;
    };

    $extractFromUser = function($j){
        $u = $j['UserInfo'] ?? null;
        if (is_array($u) && array_is_list($u)) $u = $u[0] ?? null;
        if (!$u) return null;
        if (isset($u['fpList']) && is_array($u['fpList'])) return count($u['fpList']);
        if (isset($u['fingerPrintNum']))  return (int)$u['fingerPrintNum'];
        if (isset($u['FingerPrintNum']))  return (int)$u['FingerPrintNum'];
        return null;
    };
    $extractFromFpRecord = function($j){
        foreach (['FingerPrintRecord','fingerPrintRecord'] as $k) {
            if (isset($j[$k]) && is_array($j[$k])) return count($j[$k]);
        }
        if (isset($j['FingerPrintInfo'])) return 1;
        return null;
    };

    // 0) Capabilities -> decide rama (Fingerprint vs FingerPrint)
    $segList = ['Fingerprint','FingerPrint'];
    try {
        $cap = $this->http->request('GET','/ISAPI/AccessControl/Fingerprint/capabilities');
        if ($cap->getStatusCode() >= 200 && $cap->getStatusCode() < 300) $segList = ['Fingerprint','FingerPrint'];
        else $segList = ['FingerPrint','Fingerprint'];
    } catch (\Throwable $e) { /* seguimos con ambas */ }

    // 1) DETAIL (dos variantes de casing)
    foreach (['employeeNo','EmployeeNo'] as $param) {
        $r = $try('GET', '/ISAPI/AccessControl/UserInfo/Detail?format=json&'.$param.'='.urlencode($employeeNo), null, $extractFromUser);
        if ($r) return $r;
    }

    // 2) SEARCH / ADVANCESEARCH con varios cuerpos posibles
    $searchEndpoints = [
        '/ISAPI/AccessControl/UserInfo/Search?format=json',
        '/ISAPI/AccessControl/UserInfo/AdvanceSearch?format=json',
    ];
    $searchBodies = [
        // a) array de valores bajo employeeNo
        ['searchID'=>'1','searchResultPosition'=>0,'maxResults'=>1,'EmployeeNoList'=>['employeeNo'=>[$employeeNo]]],
        // b) array de objetos { employeeNo: ... }
        ['searchID'=>'1','searchResultPosition'=>0,'maxResults'=>1,'EmployeeNoList'=>[['employeeNo'=>$employeeNo]]],
        // c) casing alterno
        ['searchID'=>'1','searchResultPosition'=>0,'maxResults'=>1,'EmployeeNoList'=>['EmployeeNo'=>[$employeeNo]]],
        // d) array de objetos con EmployeeNo
        ['searchID'=>'1','searchResultPosition'=>0,'maxResults'=>1,'EmployeeNoList'=>[['EmployeeNo'=>$employeeNo]]],
        // e) bloque Condition (algunos firmwares)
        ['searchID'=>'1','searchResultPosition'=>0,'maxResults'=>1,'Condition'=>['employeeNo'=>$employeeNo]],
    ];
    foreach ($searchEndpoints as $ep) {
        foreach ($searchBodies as $jb) {
            $r = $try('POST', $ep, $jb, function($j) use ($extractFromUser){
                $root = $j['UserInfoSearch'] ?? $j;
                if (isset($root['UserInfo']) && is_array($root['UserInfo'])) {
                    $root['UserInfo'] = $root['UserInfo'][0] ?? $root['UserInfo'];
                }
                return $extractFromUser($root);
            });
            if ($r) return $r;
        }
    }

    // 3) RECORD GET (con y sin format, casing del parámetro)
    foreach ($segList as $seg) {
        foreach ([true,false] as $withFormat) {
            $fmt = $withFormat ? '?format=json' : '';
            foreach (['employeeNo','EmployeeNo'] as $param) {
                $r = $try('GET', "/ISAPI/AccessControl/{$seg}/Record{$fmt}&{$param}=".urlencode($employeeNo), null, $extractFromFpRecord);
                if ($r) return $r;
            }
        }
    }

    // 4) RECORD POST (varios cuerpos) y sin/ con format
    $postBodies = [
        ['FingerPrintInfo'=>['employeeNo'=>$employeeNo]],
        ['FingerPrintInfo'=>['EmployeeNo'=>$employeeNo]],
        ['EmployeeNo'=>$employeeNo], // algunos aceptan plano
    ];
    foreach ($segList as $seg) {
        foreach ([true,false] as $withFormat) {
            $fmt = $withFormat ? '?format=json' : '';
            foreach ($postBodies as $jb) {
                $r = $try('POST', "/ISAPI/AccessControl/{$seg}/Record{$fmt}", $jb, $extractFromFpRecord);
                if ($r) return $r;
            }
        }
    }

    // 5) USERINFO/RECORD (último intento)
    foreach (['employeeNo','EmployeeNo'] as $param) {
        $r = $try('GET', '/ISAPI/AccessControl/UserInfo/Record?format=json&'.$param.'='.urlencode($employeeNo), null, $extractFromUser);
        if ($r) return $r;
    }

    // devolvemos último intento + lista de intentos para flash/debug
    return [
        'ok'=>false,
        'status'=> $attempts[count($attempts)-1]['status'] ?? 0,
        'endpoint'=> $attempts[count($attempts)-1]['path'] ?? '<none>',
        'attempts'=> $attempts,
    ];
}


}