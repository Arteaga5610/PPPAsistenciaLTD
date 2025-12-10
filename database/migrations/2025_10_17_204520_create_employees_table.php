<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Identificador de negocio para Hikvision (employeeNo)
            $table->string('employee_no', 32)->unique();

            $table->string('name', 128);
            $table->enum('gender', ['male','female','unknown'])->default('unknown');
            $table->string('department', 64)->default('Company');
            $table->enum('user_type', ['normal','visitor','blackList'])->default('normal');

            // verify_mode deberá ser uno de los soportados por tu equipo (lo validamos en el FormRequest)
            $table->string('verify_mode', 64)->nullable();

            // Ventana de validez (rango permitido por el equipo: 2000-01-01 .. 2037-12-31)
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();

            // Door right: este modelo maneja 1 puerta (1)
            $table->unsignedTinyInteger('door_right')->default(1);

            // Hasta 4 grupos, ids 1..32 (almacenamos como JSON)
            $table->json('groups')->nullable();

            // Metadatos extra opcionales
            $table->json('meta')->nullable();

            // Marcas de sincronización con el dispositivo (todavía no implementamos ISAPI)
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};