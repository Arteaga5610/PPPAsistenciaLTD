<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEvent;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\AttendanceRecord;



class AttendanceWebhookController extends Controller
{

    public function handle(\Illuminate\Http\Request $request)
{
    // 0) Log básico
    $method  = $request->getMethod();
    $raw     = $request->getContent() ?? '';
    $headers = $request->headers->all();
    Log::info('HIK Webhook', [
        'ip'     => $request->ip(),
        'len'    => strlen($raw),
        'method' => $method,
        'ct'     => $request->header('Content-Type')
    ]);

    // 1) Aceptar también GET (algunos firmwares suben por querystring)
    if (!in_array($method, ['POST','PUT','PATCH','GET'])) {
        Log::info('HIK ping (unsupported method)', compact('method'));
        return response()->json(['ok' => true]);
    }

    // 2) Normalizar $data desde varias fuentes (JSON / XML / form-data / query)
    $data = null;

    // 2.a JSON directo en el cuerpo
    if ($raw !== '') {
        $tryJson = json_decode($raw, true);
        if (is_array($tryJson)) $data = $tryJson;
    }

    // 2.b XML en el cuerpo
    if (!$data && $raw !== '' && stripos($request->header('Content-Type', ''), 'xml') !== false) {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($raw);
        if ($xml !== false) {
            $json = json_encode($xml);
            $arr  = json_decode($json, true);
            if (is_array($arr)) $data = $arr;
        }
        libxml_clear_errors();
    }

    // 2.c form-data / x-www-form-urlencoded (puede venir un campo con JSON embebido)
    if (!$data) {
        $bag = $request->all(); // incluye form-data + query en Laravel
        if (!empty($bag)) {
            // Si hay un único campo tipo `event={...json...}`
            if (count($bag) === 1) {
                $onlyVal = reset($bag);
                if (is_string($onlyVal)) {
                    $tryInner = json_decode($onlyVal, true);
                    if (is_array($tryInner)) {
                        $data = $tryInner;
                    }
                }
            }
            // Si aún no, usar todo el arreglo tal cual (query/form)
            if (!$data) $data = $bag;
        }
    }

    // 2.d Si sigue vacío: intentar GET puro (querystring)
    if (!$data) {
        $qs = $request->query();
        if (!empty($qs)) $data = $qs;
    }

    // 2.e Si AÚN así no hay data, registrar el hit y salir (pero guardando raw/query)
    if (!$data || !is_array($data)) {
        Log::warning('ATTN empty/unknown payload format', [
            'device_ip'   => $request->ip(),
            'raw_len'     => strlen($raw),
            'query_count' => count($request->query() ?? []),
        ]);

        // Guardar evento crudo (aunque sea vacío) para auditoría
        \App\Models\AttendanceEvent::create([
            'device_ip'   => $request->ip(),
            'employee_no' => null,
            'event_type'  => 'accesscontrollerevent',
            'method'      => null,
            'event_time'  => now(),
            'raw_payload' => $raw !== '' ? $raw : http_build_query($request->query() ?? []),
        ]);

        return response()->json(['ok' => true]);
    }

    // 3) Normalizar campos clave: tipo, método, fecha
    $eventType = \Illuminate\Support\Str::lower((string) data_get($data, 'eventType', 'accesscontrollerevent'));
    if ($eventType === '') $eventType = 'accesscontrollerevent';

    $methodVal = \Illuminate\Support\Str::lower((string) data_get($data, 'AccessControllerEvent.currentVerifyMode', ''));
    if ($methodVal === '' || $methodVal === 'invalid') {
        $verifyNo = (int) data_get($data, 'AccessControllerEvent.verifyNo', 0);
        $methodVal = $verifyNo > 0 ? 'face' : null; // heurística
    }

    $when = data_get($data, 'dateTime')
        ?: data_get($data, 'AccessControllerEvent.dateTime')
        ?: data_get($data, 'AccessControllerEvent.eventTime')
        ?: data_get($data, 'AccessControllerEvent.time')
        ?: now()->toIso8601String();

    try {
        $hitAt = \Illuminate\Support\Carbon::parse($when);
    } catch (\Throwable $e) {
        $hitAt = now();
    }

    // 4) Resolver employee_no de varias rutas (y fallback en crudo/query)
    $empNo = null;
    foreach ([
        'AccessControllerEvent.employeeNoString',
        'AccessControllerEvent.employeeNo',
        'AccessControllerEvent.EmployeeNo',
        'AccessControllerEvent.employeeNoStr',
        'UserInfo.employeeNo',
        'AccessControllerEvent.UserInfo.employeeNo',
        'AccessControllerEvent.cardNo',
        'cardNo',
        'employeeNo', // por si viene plano en query
        'employeeNoString',
    ] as $path) {
        $v = data_get($data, $path);
        if ($v !== null && $v !== '') { $empNo = (string) $v; break; }
    }

    if (!$empNo) {
        // fallback regex sobre raw y sobre query codificada
        $rawScan = $raw !== '' ? $raw : http_build_query($request->query() ?? []);
        if (preg_match('/employeeNo(?:String|Str)?=([A-Za-z0-9_-]+)/i', $rawScan, $m)) {
            $empNo = $m[1];
        } elseif (preg_match('/"employeeNo(?:String|Str)?"\s*:\s*"([^"]+)"/i', $rawScan, $m)) {
            $empNo = $m[1];
        } elseif (preg_match('/cardNo=([A-Za-z0-9_-]+)/i', $rawScan, $m)) {
            $empNo = $m[1];
        }
    }

    // 5) Siempre guardar el evento crudo
    \App\Models\AttendanceEvent::create([
        'device_ip'   => $request->ip(),
        'employee_no' => $empNo,
        'event_type'  => $eventType,
        'method'      => $methodVal,
        'event_time'  => $hitAt,
        'raw_payload' => $raw !== '' ? $raw : json_encode($data, JSON_UNESCAPED_UNICODE),
    ]);

    if (!$empNo) {
        Log::warning('ATTN mirror: employee_no not found even after fallbacks', [
            'time' => $hitAt->format('Y-m-d H:i:s'),
        ]);
        return response()->json(['ok' => true]);
    }

    // 6) Buscar empleado y calcular ventanas
    $emp = \App\Models\Employee::where('employee_no', $empNo)->first();
    if (!$emp) {
        Log::warning('ATTN mirror: employee not found', ['employee_no' => $empNo]);
        return response()->json(['ok' => true]);
    }

    $date = $hitAt->toDateString();
    if (method_exists($emp, 'attendanceWindowsForDateSmart')) {
        [$entryWin, $exitWin] = $emp->attendanceWindowsForDateSmart($date);
    } else {
        [$entryWin, $exitWin] = $emp->attendanceWindowsForDate($date);
    }

    $isEntry = is_array($entryWin) && isset($entryWin[0], $entryWin[1]) && $hitAt->between($entryWin[0], $entryWin[1]);
    $isExit  = !$isEntry && is_array($exitWin)  && isset($exitWin[0], $exitWin[1])  && $hitAt->between($exitWin[0], $exitWin[1]);

    // 7) Upsert de registro diario por employee_no + date
    $rec = \App\Models\AttendanceRecord::firstOrCreate(
        ['employee_no' => $empNo, 'date' => $date],
        [
            'entry_time'         => null,
            'exit_time'          => null,
            //'entry_window_start' => (is_array($entryWin) && isset($entryWin[0])) ? $entryWin[0]->format('H:i:s') : '00:00:00',
            //'entry_window_end'   => (is_array($entryWin) && isset($entryWin[1])) ? $entryWin[1]->format('H:i:s') : '23:59:59',
            //'exit_window_start'  => (is_array($exitWin)  && isset($exitWin[0]))  ? $exitWin[0]->format('H:i:s')  : '00:00:00',
            //'exit_window_end'    => (is_array($exitWin)  && isset($exitWin[1]))  ? $exitWin[1]->format('H:i:s')  : '23:59:59',
        ]
    );

    // Rellenar entrada/salida si aplica
$dirty = false;
if ($isEntry && empty($rec->entry_time)) {
    $rec->entry_time = $hitAt->format('H:i:s');
    $dirty = true;
}
if ($isExit && empty($rec->exit_time)) {
    $rec->exit_time = $hitAt->format('H:i:s');
    $dirty = true;
}
if ($dirty) {
    $rec->save();
}

    Log::info('ATTN mirror saved', [
        'emp'    => $empNo,
        'date'   => $date,
        'entry'  => $rec->entry_time,
        'exit'   => $rec->exit_time,
    ]);

    return response()->json(['ok' => true]);
}



    /* ================= JSON ================= */

    private function parseJsonFlexible(array $p, array &$out, ?string &$personName): void
    {
        $find = function(array $arr, array $keys) {
            $keys = array_map('strtolower', $keys);
            $iter = function($node) use (&$iter,$keys){
                if (!is_array($node)) return null;
                foreach ($node as $k=>$v){
                    $kl = strtolower((string)$k);
                    if (in_array($kl,$keys,true)) return $v;
                    if (is_array($v)){
                        $r = $iter($v);
                        if ($r !== null) return $r;
                    }
                }
                return null;
            };
            return $iter($arr);
        

                    // --- Extra: detectar método/tiempo/etc. dentro de AccessControllerEvent ---
            $ace = $find($p, ['AccessControllerEvent','accesscontrollerevent']);
            if (is_array($ace)) {
                // método (prioridad absoluta si viene aquí)
                $mode = $find($ace, [
                    'currentVerifyMode','verifyMode','identifyType','bioType','credentialType'
                ]);
                if ($mode && empty($out['method'])) {
                    $out['method'] = $this->normalizeMethod($mode);
                }

                // persona (a veces repiten aquí)
                $emp2  = $find($ace, ['employeeNoString','employeeNo','personId','personID']);
                if (!$out['employee_no'] && $emp2) {
                    $out['employee_no'] = $this->nn($emp2);
                }

                // descripción de evento → clasificador
                $desc = $find($ace, ['subEventTypeDesc','eventDescription','name']);
                if (!$out['event_type'] && $desc) {
                    $out['event_type'] = $this->normalizeEventType(null, $desc, null) ?: 'accesscontrollerevent';
                }

                // hora específica del sub-nodo
                $when = $find($ace, ['dateTime','eventTime','occurTime']);
                if (!$out['event_time'] && $when) {
                    $out['event_time'] = \Carbon\Carbon::parse($when);
                }
            }

        };
        
        $emp   = $find($p, ['employeeNoString','employeeNo','personId','personID','employeeID']);
        $name  = $find($p, ['name','personName']);
        $evt   = $find($p, ['eventTime','time','occurTime','dateTime']);
        $maj   = $find($p, ['major','majorType']);
        $min   = $find($p, ['minor','minorType','eventType','eventTypeDesc']);
        $ver   = $find($p, ['verifyMode','identifyType','authType']);
        $sta   = $find($p, ['status','statusValue','passResult']);

        $out['employee_no'] = $this->nn($emp);
        $personName = $this->nn($name);

        if ($this->nn($evt)) $out['event_time'] = Carbon::parse($evt);

        $out['event_type'] = $this->normalizeEventType($maj, $min, null) ?: $this->nn($min, toLower:true);
        $out['method']     = $this->normalizeMethod($ver) ?: $this->guessMethodFromText($min);
        $out['result']     = $this->normalizeResult($sta, $min);
    

        
      }


    /* ================= XML ================= */

    private function parseXmlFlexible(string $xml, array &$out, ?string &$personName): void
    {
        if (trim($xml) === '' || !str_contains($xml, '<')) return;
        libxml_use_internal_errors(true);
        $sx = simplexml_load_string($xml);
        if (!$sx) return;

        $xp = function(string $path) use ($sx){
            $res = @$sx->xpath($path);
            return $res && isset($res[0]) ? (string)$res[0] : null;
        };

        // Employee / Name
        $emp = $xp('//*[local-name()="employeeNoString"]')
            ?? $xp('//*[local-name()="employeeNo"]')
            ?? $xp('//*[local-name()="personId"]')
            ?? $xp('//*[local-name()="personID"]')
            ?? $xp('//*[local-name()="employeeID"]');

        $name = $xp('//*[local-name()="name"]')
            ?? $xp('//*[local-name()="personName"]');

        // Fecha/hora
        $evt  = $xp('//*[local-name()="eventTime"]')
            ?? $xp('//*[local-name()="time"]')
            ?? $xp('//*[local-name()="occurTime"]')
            ?? $xp('//*[local-name()="dateTime"]');

        // Tipos / método / estado
        $maj  = $xp('//*[local-name()="major"]') ?: $xp('//*[local-name()="majorType"]');
        $min  = $xp('//*[local-name()="minor"]') ?: $xp('//*[local-name()="minorType"]')
              ?: $xp('//*[local-name()="eventType"]') ?: $xp('//*[local-name()="eventTypeDesc"]');
        $ver  = $xp('//*[local-name()="verifyMode"]') ?: $xp('//*[local-name()="identifyType"]') ?: $xp('//*[local-name()="authType"]');
        $sta  = $xp('//*[local-name()="status"]') ?: $xp('//*[local-name()="statusValue"]') ?: $xp('//*[local-name()="passResult"]');

        $out['employee_no'] = $this->nn($emp);
        $personName         = $this->nn($name);

        if ($this->nn($evt)) $out['event_time'] = Carbon::parse($evt);

        $out['event_type'] = $this->normalizeEventType($maj, $min, null) ?: $this->nn($min, toLower:true);
        $out['method']     = $this->normalizeMethod($ver) ?: $this->guessMethodFromText($min);
        $out['result']     = $this->normalizeResult($sta, $min);
    }

    /* ============ Fallback regex por si todo lo demás falla ============ */

    private function parseByRegexFallback(string $raw, array &$out, ?string &$personName): void
  {
    if (!is_string($raw) || $raw === '') return;

    $lower = mb_strtolower($raw, 'UTF-8');


    // Fallback: si viene como JSON plano, detectar claves de modo
  if (empty($out['method'])) {
    if (preg_match('/"currentVerifyMode"\s*:\s*"([^"]+)"/i', $raw, $m) ||
        preg_match('/"verifyMode"\s*:\s*"([^"]+)"/i', $raw, $m) ||
        preg_match('/"bioType"\s*:\s*"([^"]+)"/i', $raw, $m) ||
        preg_match('/"credentialType"\s*:\s*"([^"]+)"/i', $raw, $m)) {
        $out['method'] = $this->normalizeMethod($m[1]);
    }
  } 

    // 1) employee / name (lo que ya tenías)
    $grab = function(array $patterns) use ($raw) {
        foreach ($patterns as $pat) {
            if (preg_match($pat, $raw, $m)) return $m[1];
        }
        return null;
    };

    $emp = $grab([
        '/<employeeNoString[^>]*>([^<]+)<\/employeeNoString>/i',
        '/<employeeNo[^>]*>([^<]+)<\/employeeNo>/i',
        '/<personID[^>]*>([^<]+)<\/personID>/i',
        '/<personId[^>]*>([^<]+)<\/personId>/i',
        '/"employeeNoString"\s*:\s*"([^"]+)"/i',
        '/"employeeNo"\s*:\s*"([^"]+)"/i',
    ]);

    $name = $grab([
        '/<name[^>]*>([^<]+)<\/name>/i',
        '/<personName[^>]*>([^<]+)<\/personName>/i',
        '/"name"\s*:\s*"([^"]+)"/i',
        '/"personName"\s*:\s*"([^"]+)"/i',
    ]);

    $evt = $grab([
        '/<eventTime[^>]*>([^<]+)<\/eventTime>/i',
        '/<time[^>]*>([^<]+)<\/time>/i',
        '/"eventTime"\s*:\s*"([^"]+)"/i',
        '/"time"\s*:\s*"([^"]+)"/i',
    ]);

    $out['employee_no'] = $out['employee_no'] ?: $this->nn($emp);
    $personName         = $personName ?: $this->nn($name);
    if (!$out['event_time'] && $this->nn($evt)) $out['event_time'] = \Carbon\Carbon::parse($evt);

    // 2) Reglas en español/inglés comunes que envía el equipo (UI ES)
    $map = [
        // Huella (añade estas si no están)
        'autenticado mediante huella digital'          => ['auth_fp_success','fp','success'],
        'autentificado mediante huella digital'        => ['auth_fp_success','fp','success'],
        'autenticado con huella digital'               => ['auth_fp_success','fp','success'],
        'autentificación de huella fallida'            => ['auth_fp_fail','fp','fail'],
        'autenticacion de huella fallida'              => ['auth_fp_fail','fp','fail'], // sin tilde


        // Rostro
        'autenticado con rostro'                   => ['auth_face_success','face','success'],
        'autenticación de rostro fallida'          => ['auth_face_fail','face','fail'],
        'falló la detección de antiengaño de rostro' => ['auth_face_fail','face','fail'],

        // Tarjeta
        'autenticado con tarjeta'                  => ['auth_card_success','card','success'],
        'autenticación de tarjeta fallida'         => ['auth_card_fail','card','fail'],

        // Puerta / control remoto
        'puerta abierta'                           => ['door_open',null,null],
        'puerta cerrada'                           => ['door_close',null,null],
        'desbloqueo remoto'                        => ['door_remote_open',null,null],
        'estado abierto restante iniciado'         => ['door_hold_open_start',null,null],
        'estado abierto restante terminado'        => ['door_hold_open_end',null,null],
        'estado bloqueado restante iniciado'       => ['door_locked',null,null],
        'estado bloqueado restante terminado'      => ['door_unlocked',null,null],

        // Mensajes de denegación
        'autenticación fallida'                    => ['access_denied',null,'fail'],
        'access denied'                            => ['access_denied',null,'fail'],
        'access approved'                          => ['access_granted',null,'success'],
    ];

    foreach ($map as $needle => [$etype, $method, $result]) {
        if (str_contains($lower, $needle)) {
            $out['event_type'] = $out['event_type'] ?: $etype;
            if ($method) $out['method'] = $out['method'] ?: $method;
            if ($result) $out['result'] = $out['result'] ?: $result;
            break;
        }
    }

        // --- Resolución de prioridad si hay señales mezcladas ---
// Buscamos claves JSON típicas además de texto libre
$hasFace   = (str_contains($lower, 'rostro') || str_contains($lower, 'face')
              || preg_match('/"currentverifymode"\s*:\s*"face"/i', $raw)
              || preg_match('/"verifymode"\s*:\s*"face/i', $raw)
              || preg_match('/"biotype"\s*:\s*"face|portrait"/i', $raw));

$hasFinger = (str_contains($lower, 'huella') || str_contains($lower, 'finger')
              || preg_match('/"currentverifymode"\s*:\s*"finger/i', $raw)
              || preg_match('/"verifymode"\s*:\s*"finger/i', $raw)
              || preg_match('/"biotype"\s*:\s*"finger/i', $raw));

$hasCard   = (str_contains($lower, 'tarjeta') || str_contains($lower, 'card')
              || preg_match('/"credentialtype"\s*:\s*"card/i', $raw));

if ($hasFace) {
    $out['method'] = 'face';
} elseif ($hasFinger && empty($out['method'])) {
    $out['method'] = 'fp';
} elseif ($hasCard && empty($out['method'])) {
    $out['method'] = 'card';
}



      // Resultado si aún falta
      if (empty($out['result'])) {
          if (str_contains($lower,'success') || str_contains($lower,'aprob')) {
              $out['result'] = 'success';
          } elseif (str_contains($lower,'fail') || str_contains($lower,'deneg') || str_contains($lower,'inválid')) {
              $out['result'] = 'fail';
          }
      }
}


    /* ================= Normalizadores ================= */

    private function normalizeEventType($major, $minor, $pass): ?string
      {
          $maj = strtolower((string)$major);
          $min = strtolower((string)$minor);
          $pas = strtolower((string)$pass);

          // Puerta / estados
          if (str_contains($min,'remain open start') || str_contains($min,'hold open start')) return 'door_hold_open_start';
          if (str_contains($min,'remain open end')   || str_contains($min,'hold open end'))   return 'door_hold_open_end';
          if (str_contains($min,'remoteopen')        || str_contains($min,'remote open'))     return 'door_remote_open';
          if (str_contains($min,'door open')         || str_contains($min,'puerta abierta'))  return 'door_open';
          if (str_contains($min,'door close')        || str_contains($min,'puerta cerrada'))  return 'door_close';
          if (str_contains($min,'door locked')       || str_contains($min,'bloqueado'))        return 'door_locked';
          if (str_contains($min,'door unlocked')     || str_contains($min,'desbloqueo'))       return 'door_unlocked';

          // Autenticación por método + resultado (si el "minor" ya trae éxito/fracaso)
          $isSuccess = str_contains($min,'success') || str_contains($pas,'accessapproved');
          $isFail    = str_contains($min,'fail') || str_contains($min,'deny') || str_contains($pas,'denied');

          if (str_contains($min,'finger')) {
              if ($isSuccess) return 'auth_fp_success';
              if ($isFail)    return 'auth_fp_fail';
              return 'auth_fp';
          }

          if (str_contains($min,'face')) {
              if ($isSuccess) return 'auth_face_success';
              if ($isFail)    return 'auth_face_fail';
              return 'auth_face';
          }

          if (str_contains($min,'card')) {
              if ($isSuccess) return 'auth_card_success';
              if ($isFail)    return 'auth_card_fail';
              return 'auth_card';
          }

          // Genéricos de acceso
          if ($isSuccess) return 'access_granted';
          if ($isFail)    return 'access_denied';

          // Si no logramos clasificar, dejamos el genérico del equipo
          return $maj ?: ($min ?: 'accesscontrollerevent');
      }


      private function normalizeMethod($verify): ?string
{
    if ($verify === null || $verify === '') return null;

    // --- 1) Si es numérico (caso Hikvision) ---
    if (is_numeric($verify)) {
        $num = (int)$verify;

        // ⚙️ Mapa completo según SDK Hikvision DS-K1T32xx (probado)
        // FACE → 1, 512, 513, 515, 768, 769
        // FINGERPRINT → 2, 128, 129, 130, 131
        // CARD → 3, 41, 53, 64, 65, 66
        // PASSWORD → 4, 5, 6
        switch ($num) {
            // facial
            case 1: case 512: case 513: case 514: case 515: case 768: case 769:
                return 'face';
            // huella
            case 2: case 128: case 129: case 130: case 131: case 160: case 161:
                return 'fp';
            // tarjeta
            case 3: case 41: case 53: case 64: case 65: case 66: case 67:
                return 'card';
            // pin / password
            case 4: case 5: case 6: case 7: case 8:
                return 'pw';
            default:
                return 'invalid';
        }
    }

    // --- 2) Si es texto ---
    $v = strtolower(trim((string)$verify));
    $v = preg_replace('/[^a-z0-9]+/', '', $v);

    if (str_contains($v, 'face') || str_contains($v, 'portrait') || str_contains($v, 'facial')) {
        return 'face';
    }
    if (str_contains($v, 'finger') || str_contains($v, 'fp') || str_contains($v, 'huella')) {
        return 'fp';
    }
    if (str_contains($v, 'card') || str_contains($v, 'rfid') || str_contains($v, 'iccard')) {
        return 'card';
    }
    if (str_contains($v, 'pw') || str_contains($v, 'pin') || str_contains($v, 'password')) {
        return 'pw';
    }

    return 'invalid';
}

    private function guessMethodFromText(?string $text): ?string
    {
        $t = strtolower((string)$text);
        if ($t === '') return null;
        if (str_contains($t,'huella') || str_contains($t,'finger')) return 'fp';
        if (str_contains($t,'rostro') || str_contains($t,'face'))   return 'face';
        if (str_contains($t,'tarjeta')|| str_contains($t,'card'))   return 'card';
        if (str_contains($t,'contraseña')|| str_contains($t,'pw') || str_contains($t,'pin')) return 'pw';
        return null;
    }

    private function normalizeResult($status, $minor): ?string
    {
        $s = strtolower((string)$status);
        $m = strtolower((string)$minor);
        if (str_contains($s,'success') || str_contains($m,'success')) return 'success';
        if (str_contains($s,'fail') || str_contains($m,'deny') || str_contains($m,'invalid')) return 'fail';
        return $s ?: null;
    }

    private function nn($v, bool $toLower=false): ?string
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '') return null;
        return $toLower ? strtolower($s) : $s;
    }
}
