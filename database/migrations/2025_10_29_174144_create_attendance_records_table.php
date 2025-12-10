<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Borra la tabla si existía
        Schema::dropIfExists('attendance_records');

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();

            // Relación por código alfanumérico del empleado
            $table->string('employee_no', 32)->index();

            // Fecha del día de asistencia
            $table->date('date')->index();

            // Marcaciones (hora real registrada)
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();

            // Ventanas permitidas (con fecha y hora)
            $table->dateTime('entry_window_start')->nullable();
            $table->dateTime('entry_window_end')->nullable();
            $table->dateTime('exit_window_start')->nullable();
            $table->dateTime('exit_window_end')->nullable();

            $table->timestamps();

            // Un registro por empleado y día
            $table->unique(['employee_no', 'date']);
        });

        // (Opcional pero recomendado) FK por employee_no si tu tabla employees
        // tiene una columna employee_no UNIQUE/INDEX del mismo largo/collation.
        Schema::table('attendance_records', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'employee_no')) {
                $table->foreign('employee_no')
                      ->references('employee_no')
                      ->on('employees')
                      ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
