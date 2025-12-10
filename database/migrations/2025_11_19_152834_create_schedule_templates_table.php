<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);             // Ej: "Oficina mañana L-V"

            // Horario base
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();

            // Tolerancias
            $table->unsignedSmallInteger('entry_early_min')->default(15);
            $table->unsignedSmallInteger('entry_late_min')->default(15);
            $table->unsignedSmallInteger('exit_early_min')->default(10);
            $table->unsignedSmallInteger('exit_late_min')->default(10);

            // Días laborables por defecto, ej [1,2,3,4,5]
            $table->json('work_days')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_templates');
    }
};
