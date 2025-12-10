<?php

namespace App\Services;

use GuzzleHttp\Client;

class FaceBridge
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('facebridge.base_url'),
            'timeout'  => 20,
            'http_errors' => false,
        ]);
    }

    public function setCard(string $cardNo, string $employeeNo, int $rightPlan = 0): array
    {
        $res = $this->http->post('/set-card', [
            'form_params' => compact('cardNo','employeeNo','rightPlan')
        ]);
        return json_decode((string)$res->getBody(), true) ?? [];
    }

    public function setFace(string $cardNo, string $jpgPath, int $readerNo = 1): array
    {
        $res = $this->http->post('/set-face', [
            'multipart' => [
                ['name'=>'cardNo', 'contents'=>$cardNo],
                ['name'=>'readerNo', 'contents'=>(string)$readerNo],
                ['name'=>'faceFile', 'contents'=>fopen($jpgPath, 'r'), 'filename'=>basename($jpgPath)],
            ],
        ]);
        return json_decode((string)$res->getBody(), true) ?? [];
    }

    public function setFinger(string $cardNo, int $readerNo, int $fingerId, string $fingerPath): array
{
    $url = rtrim(config('services.facebridge.url', env('FACEBRIDGE_URL')), '/') . '/set-finger';

    $response = \Http::attach(
        'fingerFile',
        file_get_contents($fingerPath),
        basename($fingerPath)
    )->post($url, [
        'cardNo'   => $cardNo,
        'readerNo' => $readerNo,
        'fingerId' => $fingerId,
    ]);

    if (!$response->ok()) {
        return [
            'ok'  => false,
            'err' => $response->status(),
            'msg' => $response->body(),
        ];
    }

    return $response->json();
}
public function captureFinger(string $cardNo, int $readerNo, int $fingerId): array
{
    $url = rtrim(config('services.facebridge.url', env('FACEBRIDGE_URL')), '/') . '/capture-finger';

    $response = \Http::post($url, [
        'cardNo'   => $cardNo,
        'readerNo' => $readerNo,
        'fingerId' => $fingerId,
    ]);

    if (!$response->ok()) {
        return [
            'ok'  => false,
            'err' => $response->status(),
            'msg' => $response->body(),
        ];
    }

    return $response->json();
}
}
