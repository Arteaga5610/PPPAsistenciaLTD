<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/attendance/webhook', [AttendanceWebhookController::class, 'handle']);


use App\Http\Controllers\HikController;

Route::post('/hik/set-card', [HikController::class, 'setCard']);
Route::post('/hik/set-face', [HikController::class, 'setFace']);
Route::post('/hik/set-finger', [HikController::class, 'setFinger']);
Route::post('/hik/capture-finger', [HikController::class, 'captureFinger']);
// NUEVO flujo 2-pasos (no reemplaza a los anteriores)
Route::post('/hik/finger/capture', [HikController::class, 'captureFingerTemplate']);
Route::post('/hik/finger/set',      [HikController::class, 'setFingerFromTemplate']);

// ========= ELIMINAR CREDENCIALES =============

// Eliminar TODO (card, face, finger) de un empleado
Route::post('/hik/employee/{employeeNo}/delete-all', [HikController::class, 'deleteAllCredentials']);
// Eliminar solo TARJETA
Route::post('/hik/employee/{employeeNo}/delete-card', [HikController::class, 'deleteCard']);
// Eliminar solo ROSTRO
Route::post('/hik/employee/{employeeNo}/delete-face', [HikController::class, 'deleteFace']);
// Eliminar solo HUELLA
Route::post('/hik/employee/{employeeNo}/delete-finger', [HikController::class, 'deleteFinger']);
// ISAPI para eliminar tarjeta
Route::post('/hik/delete-card', [HikController::class, 'deleteCardIsapi']);
