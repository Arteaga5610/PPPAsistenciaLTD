<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendance_days', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no')->index();
            $table->date('date')->index();

            // horario planificado (fecha+hora ya combinadas)
            $table->dateTime('entry_scheduled')->nullable();
            $table->dateTime('exit_scheduled')->nullable();

            // sellos reales
            $table->dateTime('entry_marked_at')->nullable();
            $table->unsignedBigInteger('entry_event_id')->nullable();

            $table->dateTime('exit_marked_at')->nullable();
            $table->unsignedBigInteger('exit_event_id')->nullable();

            // métricas/estado
            $table->string('status', 20)->default('pending'); // pending|complete|partial|absent
            $table->integer('late_minutes')->default(0);
            $table->integer('left_early_minutes')->default(0);

            $table->timestamps();

            $table->unique(['employee_no','date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('attendance_days');
    }
};
