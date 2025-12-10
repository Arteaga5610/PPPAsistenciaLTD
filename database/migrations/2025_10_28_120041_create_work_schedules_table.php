<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no')->index(); // mismo código que en employees
            $table->time('entry_time');             // 09:00:00
            $table->time('exit_time');              // 18:00:00
            // tolerancias (minutos)
            $table->unsignedSmallInteger('entry_minus')->default(15);
            $table->unsignedSmallInteger('entry_plus')->default(15);
            $table->unsignedSmallInteger('exit_minus')->default(10);
            $table->unsignedSmallInteger('exit_plus')->default(10);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('work_schedules');
    }
};
