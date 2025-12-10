<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceEventController;
use App\Http\Controllers\FingerprintController;
use App\Http\Controllers\AttendanceFeedController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HikController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WorkScheduleController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| LOGIN / LOGOUT
|--------------------------------------------------------------------------
| Sólo existe 1 usuario (administrador) y no hay registro público.
| Estas rutas NO deben ir dentro del middleware('auth').
*/

// Sólo invitados pueden ver el formulario y enviar credenciales
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.post');
});

// Sólo usuarios autenticados pueden cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');


/*
|--------------------------------------------------------------------------
| ENDPOINTS QUE LLAMA FACEBRIDGE / HIKVISION (SIN LOGIN)
|--------------------------------------------------------------------------
| Estos endpoints los consume tu servicio local, NO un usuario en el navegador.
| Por eso no les ponemos middleware('auth').
*/

Route::post('/api/hik/notify-credential', [HikController::class, 'notifyCredential'])
    ->name('hik.notify');

Route::post('/api/hik/notify-deletion', [HikController::class, 'notifyDeletion'])
    ->name('hik.notifyDeletion');


/*
|--------------------------------------------------------------------------
| TODAS LAS RUTAS DEL PANEL (PROTEGIDAS CON LOGIN)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Home → Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Asistencias
    Route::get('/asistencias/hoy', [DashboardController::class, 'todaySchedules'])->name('asistencias.hoy');

    // ===================== EMPLEADOS =====================
    
    // CRUD de empleados
    Route::resource('employees', EmployeeController::class)->except(['destroy']);
    
    // Eliminación individual (personalizada)
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])
        ->name('employees.destroy');
    
    // Eliminación múltiple
    Route::post('employees/mass-delete', [EmployeeController::class, 'bulkDestroy'])
        ->name('employees.bulkDestroy');

    // Horario por empleado (formulario embebido)
    Route::get('/employees/{employee}/schedule', [EmployeeController::class, 'scheduleForm'])
        ->name('employees.schedule');

    Route::post('/employees/{employee}/schedule', [EmployeeController::class, 'scheduleStore'])
        ->name('employees.schedule.store');

    // Sincronizaciones desde el panel
    Route::post('/employees/sync-all', [EmployeeController::class, 'syncAll'])
        ->name('employees.syncAll');

    Route::post('/employees/{employee}/sync-credentials', [EmployeeController::class, 'syncCredentials'])
        ->name('employees.syncCredentials');


    // ===================== ASISTENCIA =====================

    // Listado crudo de eventos
    Route::get('/attendance', [AttendanceEventController::class, 'index'])
        ->name('attendance.index');

    // Atajo /asistencia → si alguien entra sin employeeNo, lo mandamos a empleados
    Route::get('/asistencia', function () {
        return redirect()->route('employees.index');
    });

    // Historial por empleado
    Route::get('/asistencia/{employeeNo}', [AttendanceController::class, 'byEmployee'])
        ->name('attendance.byEmployee');

    // Historial general (si lo usas)
    Route::get('/asistencia-all', [AttendanceController::class, 'allRecords'])
        ->name('attendance.all');

    // Feed en tiempo real
    Route::get('/attendance/feed', [AttendanceFeedController::class, 'index'])
        ->name('attendance.feed');


    // ===================== HUELLA =====================

    // Route::post('/employees/{employee}/fp/start', [FingerprintController::class, 'start'])
   //     ->name('fp.start');

  //  Route::get('/employees/{employee}/fp/status', [FingerprintController::class, 'status'])
    //    ->name('fp.status');

  //  Route::post('/employees/{employee}/fp/mark', [FingerprintController::class, 'markHasFp'])
   //     ->name('fp.mark');

   // Route::post('/employees/{employee}/fp/simple-start', [FingerprintController::class, 'simpleStart'])
   //     ->name('fp.simple');

    Route::post('/employees/{employee}/fp/sync', [EmployeeController::class, 'syncFp'])
        ->name('fp.sync');


    // ===================== HORARIOS =====================

    // Listar todos los horarios
    Route::get('/horarios', [WorkScheduleController::class, 'index'])
        ->name('horarios.index');

    // Pantalla independiente para crear horario
    Route::get('/horarios/create', [WorkScheduleController::class, 'create'])
        ->name('horarios.create');

    Route::post('/horarios', [WorkScheduleController::class, 'store'])
        ->name('horarios.store');
    
    // Editar horario
    Route::get('/horarios/{horario}/edit', [WorkScheduleController::class, 'updateForm'])
        ->name('horarios.edit');
    
    // Eliminar horario
    Route::delete('/horarios/{horario}', [WorkScheduleController::class, 'destroy'])
        ->name('horarios.destroy');

    // Crear plantillas desde formulario
    Route::post('/horarios/plantilla', [WorkScheduleController::class, 'storeTemplate'])
        ->name('horarios.storeTemplate');

    // Formulario independiente para crear plantilla
    Route::get('/horarios/plantilla/create', [WorkScheduleController::class, 'createTemplate'])
        ->name('horarios.template.create');

    // Formulario para ACTUALIZAR horario (versionado)
    Route::get('/horarios/actualizar', [WorkScheduleController::class, 'updateForm'])
        ->name('horarios.updateForm');

    Route::post('/horarios/actualizar', [WorkScheduleController::class, 'update'])
        ->name('horarios.update');


    // ===================== HIKVISION TEST =====================

    Route::get('/hik/test', function () {
        return view('hik-test');
    })->name('hik.test');

    Route::post('/hik/test', [HikController::class, 'deleteViaFaceBridge'])
        ->name('hik.test.post');
});

