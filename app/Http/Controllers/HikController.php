<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\FaceBridge; // 👈 IMPORTANTE
use App\Models\Employee;

class HikController extends Controller
{
/**
     * 1) Registrar / actualizar tarjeta directamente en el equipo Hikvision
     *    vía ISAPI (NO pasa por FaceBridge).
     */
    public function setCard(Request $req)
    {
        $data = $req->validate([
            'cardNo'     => ['required','string','max:32'],
            'employeeNo' => ['required','string','max:32'],
            'rightPlan'  => ['nullable','integer','min:0','max:15'],
        ]);

        // Datos del equipo desde .env
        $host = rtrim(env('HIK_DEVICE_HOST', 'http://192.168.22.21'), '/');
        $user = env('HIK_DEVICE_USER', 'admin');
        $pass = env('HIK_DEVICE_PASS', 'Eureca2025');

        // URL ISAPI para tarjeta (suele ser esta en terminales de control de acceso)
        $url = $host . '/ISAPI/AccessControl/CardInfo/Record?format=json';

        // Armamos un JSON "mínimo" de tarjeta
        $rightPlan = $data['rightPlan'] ?? 1;

        $payload = [
            "CardInfo" => [
                "employeeNo" => $data['employeeNo'],
                "cardNo"     => $data['cardNo'],
                "cardType"   => "normalCard",
                "name"       => $data['employeeNo'],
                "doorRight"  => "1",                // permisos para puerta 1
                "rightPlan"  => [$rightPlan, 0, 0, 0],
                "Valid" => [
                    "enable"    => true,
                    "beginTime" => "2024-01-01T00:00:00",
                    "endTime"   => "2030-12-31T23:59:59",
                ],
            ],
        ];

        try {
            $resp = Http::withDigestAuth($user, $pass)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);

            if ($resp->successful()) {
                // Si la creación de tarjeta fue exitosa, marcamos localmente
                try {
                    $emp = Employee::where('employee_no', $data['employeeNo'])->first();
                    if ($emp) {
                        $emp->has_card = true;
                        // guarda también el card_no para futuras búsquedas por card
                        $emp->card_no = $data['cardNo'];
                        $emp->save();
                    }
                } catch (\Throwable $ex) {
                    // no interrumpir la respuesta si falla el guardado local
                }
                return response()->json([
                    'ok'  => true,
                    'err' => 0,
                    'msg' => '',
                    'raw' => $resp->json(), // por si quieres ver lo que devuelve el equipo
                ]);
            }

            return response()->json([
                'ok'  => false,
                'err' => $resp->status(),
                'msg' => $resp->body(),
            ], $resp->status());

        } catch (\Throwable $e) {
            return response()->json([
                'ok'  => false,
                'err' => -500,
                'msg' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Subir rostro asociado a una tarjeta
     */
    public function setFace(Request $req)
    {
        // 1) Validar datos + foto
        $data = $req->validate([
            'cardNo'   => ['required','string','max:32'],
            'readerNo' => ['nullable','integer','min:1','max:8'],
            'foto'     => ['required','image','mimes:jpg,jpeg','max:200'], // 200KB
        ]);

        $file = $req->file('foto');

        // Contenido binario del JPG
        $contents = file_get_contents($file->getRealPath());

        // URL base de FaceBridge
        $base = config('services.facebridge.url', env('FACEBRIDGE_URL', 'https://03e57b5a7c61.ngrok-free.app/'));

        try {
            // Llamada multipart/form-data a /set-face
            $res = Http::attach(
                'faceFile',                         // nombre que espera C#
                $contents,
                $file->getClientOriginalName()
            )->post("$base/set-face", [
                'cardNo'   => $data['cardNo'],
                'readerNo' => $data['readerNo'] ?? 1,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'  => false,
                'err' => -500,
                'msg' => $e->getMessage(),
            ], 500);
        }

        if (! $res->successful()) {
            return response()->json([
                'ok'  => false,
                'err' => $res->status(),
                'msg' => $res->body(),
            ], $res->status());
        }
        $json = $res->json();

        if (is_array($json)) {
            // si FaceBridge devolvió ok, intentamos marcar localmente
            if (!empty($json['ok'])) {
                try {
                    // Primero intenta buscar por card_no (guardado cuando se llamó setCard)
                    $emp = Employee::where('card_no', $data['cardNo'])->first();
                    // Fallback: a veces cardNo puede coincidir con employee_no
                    if (! $emp) {
                        $emp = Employee::where('employee_no', $data['cardNo'])->first();
                    }
                    if ($emp) {
                        $emp->has_face = true;
                        $emp->save();
                    }
                } catch (\Throwable $ex) {
                    // silencioso
                }
            }

            return response()->json($json);
        }

        return response()->json([
            'ok'  => false,
            'err' => -501,
            'msg' => 'Respuesta inválida de FaceBridge en /set-face',
        ], 500);
    }
    public function setFinger(Request $req, FaceBridge $fb)
{
    $data = $req->validate([
        'cardNo'   => ['required','string','max:32'],
        'readerNo' => ['nullable','integer','min:1','max:8'],
        'fingerId' => ['nullable','integer','min:0','max:9'],
        'finger'   => ['required','file','max:20'], // 20KB por seguridad
    ]);

    $path = $req->file('finger')->store('fp_tmp');
    $abs  = storage_path('app/'.$path);

    $resp = $fb->setFinger(
        $data['cardNo'],
        $data['readerNo'] ?? 1,
        $data['fingerId'] ?? 1,
        $abs
    );

    @unlink($abs);

    // Si FaceBridge confirmó ok, marcamos localmente la huella para el employee relacionado (si existe)
    try {
        if (!empty($resp['ok'])) {
            // search by card_no first
            $emp = Employee::where('card_no', $data['cardNo'])->first();
            if (! $emp) {
                $emp = Employee::where('employee_no', $data['cardNo'])->first();
            }
            if ($emp) {
                $emp->has_fp = true;
                $emp->save();
            }
        }
    } catch (\Throwable $ex) {
        // silencioso
    }

    return response()->json($resp);
}
public function captureFinger(Request $req, FaceBridge $fb)
{
    $data = $req->validate([
        'cardNo'   => ['required','string','max:32'],
        'readerNo' => ['nullable','integer','min:1','max:8'],
        'fingerId' => ['nullable','integer','min:0','max:9'],
    ]);

    $resp = $fb->captureFinger(
        $data['cardNo'],
        $data['readerNo'] ?? 1,
        $data['fingerId'] ?? 1,
    );

    return response()->json($resp);
}
public function captureFingerTemplate(Request $request)
{
    $data = $request->validate([
        'readerNo' => 'required|integer|min:1',
    ]);

    $url = rtrim(env('FACEBRIDGE_URL', 'https://03e57b5a7c61.ngrok-free.app/'), '/') . '/finger/capture';

    try {
        $res = Http::timeout(20)->post($url, [
            'readerNo' => (int) $data['readerNo'],
        ]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'err' => -500, 'msg' => $e->getMessage()], 500);
    }

    $json = $res->json();
    // si FaceBridge devolvió plantilla y ok, no necesariamente hay que marcar nada aquí
    return response()->json($json, $res->status());
}

/**
 * Paso 2: Guardar la plantilla en el dispositivo/usuario.
 * POST /api/hik/finger/set
 * Body: { cardNo, readerNo, fingerId, templateBase64 }
 * Respuesta esperada: { ok, err, msg }
 */
public function setFingerFromTemplate(Request $request)
{
    $data = $request->validate([
        'cardNo'         => 'required|string|max:32',
        'readerNo'       => 'required|integer|min:1',
        'fingerId'       => 'required|integer|min:0|max:9',
        'templateBase64' => 'required|string',
    ]);

    $url = rtrim(env('FACEBRIDGE_URL', 'https://03e57b5a7c61.ngrok-free.app/'), '/') . '/finger/set';

    try {
        $res = Http::timeout(20)->post($url, [
            'cardNo'         => $data['cardNo'],
            'readerNo'       => (int) $data['readerNo'],
            'fingerId'       => (int) $data['fingerId'],
            'templateBase64' => $data['templateBase64'],
        ]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'err' => -500, 'msg' => $e->getMessage()], 500);
    }

    

    $json = $res->json();
    if (is_array($json) && !empty($json['ok'])) {
        try {
            $emp = Employee::where('card_no', $data['cardNo'])->first();
            if (! $emp) {
                $emp = Employee::where('employee_no', $data['cardNo'])->first();
            }
            if ($emp) {
                $emp->has_fp = true;
                $emp->save();
            }
        } catch (\Throwable $ex) {
            // silencioso
        }
    }

    return response()->json($json, $res->status());
}
    
    /**
     * Endpoint interno para recibir notificaciones desde FaceBridge
     * cuando éste realiza operaciones directamente en el equipo.
     * Body: { cardNo: string, type: 'face'|'finger'|'card', readerNo?: int, fingerId?: int }
     * Header (opcional): X-HIK-NOTIFY => debe coincidir con env('HIK_NOTIFY_SECRET') si está definida
     */
    public function notifyCredential(Request $request)
    {
        $secret = env('HIK_NOTIFY_SECRET');
        if ($secret) {
            $hdr = $request->header('X-HIK-NOTIFY');
            if (!hash_equals((string)$secret, (string)$hdr)) {
                return response()->json(['ok' => false, 'err' => 401, 'msg' => 'Unauthorized'], 401);
            }
        }

        $data = $request->validate([
            'cardNo' => ['required','string','max:64'],
            'type'   => ['required','string','in:face,finger,card'],
            'readerNo' => ['nullable','integer'],
            'fingerId' => ['nullable','integer'],
            'employeeNo' => ['nullable','string'],
        ]);

        try {
            $card = $data['cardNo'];
            $emp = Employee::where('card_no', $card)->first();
            if (! $emp && !empty($data['employeeNo'])) {
                $emp = Employee::where('employee_no', $data['employeeNo'])->first();
            }
            if (! $emp) {
                // fallback: try matching employee_no == cardNo
                $emp = Employee::where('employee_no', $card)->first();
            }

            if ($emp) {
                if ($data['type'] === 'card') {
                    $emp->has_card = true;
                    $emp->card_no = $card;
                } elseif ($data['type'] === 'face') {
                    $emp->has_face = true;
                } elseif ($data['type'] === 'finger') {
                    $emp->has_fp = true;
                }
                $emp->save();
            }

            return response()->json(['ok' => true, 'err' => 0, 'msg' => '', 'employee_found' => (bool)$emp]);
        } catch (\Throwable $ex) {
            return response()->json(['ok' => false, 'err' => -500, 'msg' => $ex->getMessage()], 500);
        }
    }

    /**
     * Endpoint para recibir notificaciones de borrado desde FaceBridge.
     * Body: { employeeNo: string, types?: ['card','face','finger'] }
     * Header (opcional): X-HIK-NOTIFY
     */
    public function notifyDeletion(Request $request)
    {
        $secret = env('HIK_NOTIFY_SECRET');
        if ($secret) {
            $hdr = $request->header('X-HIK-NOTIFY');
            if (!hash_equals((string)$secret, (string)$hdr)) {
                return response()->json(['ok' => false, 'err' => 401, 'msg' => 'Unauthorized'], 401);
            }
        }

        $data = $request->validate([
            'employeeNo' => ['required','string','max:64'],
            'types' => ['nullable','array'],
        ]);

        try {
            $emp = Employee::where('employee_no', $data['employeeNo'])->first();
            if ($emp) {
                $types = $data['types'] ?? null;
                if (is_array($types) && count($types) > 0) {
                    // Limpiar solo los tipos solicitados
                    if (in_array('card', $types)) {
                        $emp->has_card = false;
                        $emp->card_no  = null;
                    }
                    if (in_array('face', $types)) {
                        $emp->has_face = false;
                    }
                    if (in_array('finger', $types) || in_array('fp', $types)) {
                        $emp->has_fp = false;
                    }
                } else {
                    // si no se especifica, limpiar todo
                    $emp->has_card = false;
                    $emp->has_face = false;
                    $emp->has_fp   = false;
                    $emp->card_no  = null;
                }
                $emp->save();
            }

            return response()->json(['ok' => true, 'err' => 0, 'msg' => '', 'employee_found' => (bool)$emp]);
        } catch (\Throwable $ex) {
            return response()->json(['ok' => false, 'err' => -500, 'msg' => $ex->getMessage()], 500);
        }
    }

    /**
     * Endpoint usado por la UI/PRUEBA: solicita a FaceBridge eliminar credenciales
     * POST /hik/test { employeeNo }
     */
    public function deleteViaFaceBridge(Request $request)
    {
        $data = $request->validate([
            'employeeNo' => ['required','string','max:64'],
            'types' => ['nullable','array'],
        ]);

        $base = rtrim(env('FACEBRIDGE_URL', config('services.facebridge.url', 'https://03e57b5a7c61.ngrok-free.app/')), '/');
        try {
            $payload = ['employeeNo' => $data['employeeNo']];
            if (!empty($data['types']) && is_array($data['types'])) {
                $payload['types'] = $data['types'];
            }

            $res = Http::timeout(20)->post($base . '/delete-by-employee', $payload);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'err' => -500, 'msg' => $e->getMessage()], 500);
        }

        // Si FaceBridge devolvió éxito, limpiamos las banderas locales inmediatamente
        try {
            $ok = false;
            if ($res->successful()) {
                $json = $res->json();
                if (is_array($json) && array_key_exists('ok', $json)) {
                    $ok = !empty($json['ok']);
                } else {
                    // si no tiene key ok, asumimos success por status HTTP
                    $ok = true;
                }
            }

            if ($ok) {
                $json = $res->json();
                $results = is_array($json) && array_key_exists('results', $json) ? $json['results'] : null;

                $emp = Employee::where('employee_no', $data['employeeNo'])->first();
                if ($emp && is_array($results)) {
                    // Solo limpiar los que reportaron ok=true
                    if (array_key_exists('card', $results) && !empty($results['card']) && (!empty($results['card']['ok']) || (!empty($results['card']['status']) && intval($results['card']['status']) >= 200 && intval($results['card']['status']) < 300))) {
                        $emp->has_card = false;
                        $emp->card_no  = null;
                    }
                    if (array_key_exists('face', $results) && !empty($results['face']) && (!empty($results['face']['ok']) || (!empty($results['face']['status']) && intval($results['face']['status']) >= 200 && intval($results['face']['status']) < 300))) {
                        $emp->has_face = false;
                    }
                    if (array_key_exists('finger', $results) && !empty($results['finger']) && (!empty($results['finger']['ok']) || (!empty($results['finger']['status']) && intval($results['finger']['status']) >= 200 && intval($results['finger']['status']) < 300))) {
                        $emp->has_fp = false;
                    }
                    $emp->save();
                }
            }
        } catch (\Throwable $ex) {
            // no interrumpir la respuesta en caso de fallo al actualizar la DB
        }

        return response()->json($res->json(), $res->status());
    }
        /**
     * Eliminar TODAS las credenciales (card, face, finger) de un empleado.
     * POST /api/hik/employee/{employeeNo}/delete-all
     */
    public function deleteAllCredentials(Request $request, string $employeeNo)
    {
        // Reutilizamos deleteViaFaceBridge
        $request->merge([
            'employeeNo' => $employeeNo,
            // sin 'types' => se borran todas en FaceBridge
        ]);

        return $this->deleteViaFaceBridge($request);
    }

    /**
     * Eliminar SOLO la TARJETA de un empleado.
     * POST /api/hik/employee/{employeeNo}/delete-card
     */
    public function deleteCard(Request $request, string $employeeNo)
    {
        $request->merge([
            'employeeNo' => $employeeNo,
            'types'      => ['card'],
        ]);

        return $this->deleteViaFaceBridge($request);
    }

    /**
     * Eliminar SOLO el ROSTRO de un empleado.
     * POST /api/hik/employee/{employeeNo}/delete-face
     */
    public function deleteFace(Request $request, string $employeeNo)
    {
        $request->merge([
            'employeeNo' => $employeeNo,
            'types'      => ['face'],
        ]);

        return $this->deleteViaFaceBridge($request);
    }

    /**
     * Eliminar SOLO la HUELLA de un empleado.
     * POST /api/hik/employee/{employeeNo}/delete-finger
     */
    public function deleteFinger(Request $request, string $employeeNo)
    {
        $request->merge([
            'employeeNo' => $employeeNo,
            'types'      => ['finger'], // también acepta 'fp' en FaceBridge
        ]);

        return $this->deleteViaFaceBridge($request);
    }
public function deleteCardIsapi(Request $request)
{
    $data = $request->validate([
        'employeeNo' => ['required','string'],
    ]);

    $employeeNo = $data['employeeNo'];

    $host = rtrim(env('HIK_DEVICE_HOST', 'http://192.168.22.21'), '/');
    $user = env('HIK_DEVICE_USER', 'admin');
    $pass = env('HIK_DEVICE_PASS', 'Eureca2025');

    $url = $host . '/ISAPI/AccessControl/CardInfo/Delete?format=json';

    $payload = [
        "CardInfoDelCond" => [
            "EmployeeNoList" => [
                [ "employeeNo" => $employeeNo ]
            ]
        ]
    ];

    try {
        $resp = Http::withDigestAuth($user, $pass)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->put($url, $payload);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'err' => -500,
            'msg' => $e->getMessage(),
        ]);
    }

    if (! $resp->successful()) {
        return response()->json([
            'ok' => false,
            'err' => $resp->status(),
            'msg' => $resp->body(),
        ]);
    }

    // Limpia la BD
    try {
        $emp = \App\Models\Employee::where('employee_no', $employeeNo)->first();
        if ($emp) {
            $emp->has_card = false;
            $emp->card_no = null;
            $emp->save();
        }
    } catch (\Throwable $e) {}

    return response()->json([
        'ok' => true,
        'msg' => 'Tarjeta(s) eliminada(s)',
        'raw' => $resp->json(),
    ]);
}
}
